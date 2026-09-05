<?php

namespace App\Models;

/**
 * Invoice Model — handles invoice CRUD and payment status management
 */
class InvoiceModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate a unique invoice number
     */
    public function generateInvoiceNo(): string
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return $prefix . $random;
    }

    /**
     * Create a new invoice
     */
    public function create(array $data): string
    {
        $invoiceNo = $data['invoice_no'] ?? $this->generateInvoiceNo();
        $supplierColumns = '';
        $supplierValues = '';
        $supplierParams = [];
        if (!empty($data['qv_product_key'])) {
            $supplierColumns = ', qv_product_key, qv_variant_key, qv_max_cost, qv_status';
            $supplierValues = ', :qv_product_key, :qv_variant_key, :qv_max_cost, :qv_status';
            $supplierParams = [
                ':qv_product_key' => $data['qv_product_key'],
                ':qv_variant_key' => $data['qv_variant_key'] ?? null,
                ':qv_max_cost' => $data['qv_max_cost'],
                ':qv_status' => 'pending',
            ];
        }

        $this->db->query(
            "INSERT INTO invoices (invoice_no, course_id, buyer_name, buyer_phone, buyer_email, amount, promo_code, discount_amount, currency, payment_status, qr_string, md5_hash, license_key, hardware_id$supplierColumns)
             VALUES (:invoice_no, :course_id, :buyer_name, :buyer_phone, :buyer_email, :amount, :promo_code, :discount_amount, :currency, 'pending', :qr_string, :md5_hash, :license_key, :hardware_id$supplierValues)",
            [
                ':invoice_no'      => $invoiceNo,
                ':course_id'       => $data['course_id'],
                ':buyer_name'      => $data['buyer_name'],
                ':buyer_phone'     => $data['buyer_phone'] ?? null,
                ':buyer_email'     => $data['buyer_email'] ?? null,
                ':amount'          => $data['amount'],
                ':promo_code'      => $data['promo_code'] ?? null,
                ':discount_amount' => $data['discount_amount'] ?? 0.00,
                ':currency'        => $data['currency'] ?? 'USD',
                ':qr_string'       => $data['qr_string'] ?? null,
                ':md5_hash'        => $data['md5_hash'] ?? null,
                ':license_key'     => $data['license_key'] ?? null,
                ':hardware_id'     => $data['hardware_id'] ?? null,
            ] + $supplierParams
        );

        \App\Services\PurchaseAccess::remember($invoiceNo);
        return $invoiceNo;
    }

    /**
     * Get invoice by invoice number
     */
    public function getByInvoiceNo(string $invoiceNo): ?array
    {
        return $this->db->fetch(
            "SELECT i.*, c.title as course_title, c.telegram_group_id, c.thumbnail as course_thumbnail, c.type as product_type, c.download_link
             FROM invoices i
             JOIN courses c ON i.course_id = c.id
             WHERE i.invoice_no = :invoice_no",
            [':invoice_no' => $invoiceNo]
        );
    }

    /**
     * Get invoice by MD5 hash
     */
    public function getByMd5Hash(string $md5Hash): ?array
    {
        return $this->db->fetch(
            "SELECT i.*, c.type as product_type, c.telegram_group_id
             FROM invoices i JOIN courses c ON i.course_id = c.id WHERE i.md5_hash = :md5_hash",
            [':md5_hash' => $md5Hash]
        );
    }

    /**
     * Update payment status with duplicate prevention
     * Returns true only if the status was actually changed
     */
    public function updateStatus(string $invoiceNo, string $status): bool
    {
        $stmt = $this->db->query(
            "UPDATE invoices SET payment_status = :status, paid_at = IF(:status2 = 'completed', NOW(), paid_at)
             WHERE invoice_no = :invoice_no AND payment_status != 'completed'",
            [
                ':status'     => $status,
                ':status2'    => $status,
                ':invoice_no' => $invoiceNo,
            ]
        );
        return $stmt->rowCount() > 0;
    }

    public function claimLicenseDelivery(string $invoiceNo): bool
    {
        return $this->db->query(
            "UPDATE invoices SET license_delivery_status = 'processing', license_delivery_attempted_at = NOW()
             WHERE invoice_no = :invoice_no AND payment_status = 'completed' AND license_key IS NOT NULL
             AND license_delivery_status IN ('pending', 'processing')
             AND (license_delivery_attempted_at IS NULL OR license_delivery_attempted_at < DATE_SUB(NOW(), INTERVAL 60 SECOND))",
            [':invoice_no' => $invoiceNo]
        )->rowCount() === 1;
    }

    public function finishLicenseDelivery(string $invoiceNo, bool $delivered): void
    {
        $this->db->query(
            "UPDATE invoices SET license_delivery_status = :status
             WHERE invoice_no = :invoice_no AND license_delivery_status = 'processing'",
            [':invoice_no' => $invoiceNo, ':status' => $delivered ? 'delivered' : 'pending']
        );
    }

    /**
     * Save telegram invite link
     */
    public function updateTelegramLink(string $invoiceNo, string $link, string $expiresAt): void
    {
        $this->db->query(
            "UPDATE invoices SET telegram_link = :link, telegram_link_expires_at = :expires
             WHERE invoice_no = :invoice_no",
            [
                ':link'       => $link,
                ':expires'    => $expiresAt,
                ':invoice_no' => $invoiceNo,
            ]
        );
    }

    /**
     * Check if invoice is already completed (duplicate prevention)
     */
    public function isCompleted(string $invoiceNo): bool
    {
        $row = $this->db->fetch(
            "SELECT payment_status FROM invoices WHERE invoice_no = :invoice_no",
            [':invoice_no' => $invoiceNo]
        );
        return $row && $row['payment_status'] === 'completed';
    }

    /**
     * Expire old pending invoices
     */
    public function expireOldInvoices(int $minutesOld = 15): int
    {
        $stmt = $this->db->query(
            "UPDATE invoices SET payment_status = 'expired'
             WHERE payment_status = 'pending'
             AND created_at < DATE_SUB(NOW(), INTERVAL :minutes MINUTE)",
            [':minutes' => $minutesOld]
        );
        return $stmt->rowCount();
    }

    /**
     * Get all invoices for admin
     */
    public function getAll(string $status = null, int $limit = 100): array
    {
        $sql = "SELECT i.*, c.title as course_title
                FROM invoices i
                JOIN courses c ON i.course_id = c.id";
        $params = [];

        if ($status) {
            $sql .= " WHERE i.payment_status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY i.created_at DESC LIMIT :limit";
        $params[':limit'] = $limit;

        // Can't bind LIMIT with named params in emulated mode, use direct query
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare(str_replace(':limit', (int)$limit, $sql));
        unset($params[':limit']);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get dashboard statistics
     */
    public function getStats(): array
    {
        $total = $this->db->fetch("SELECT COUNT(*) as cnt FROM invoices");
        $completed = $this->db->fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as revenue FROM invoices WHERE payment_status = 'completed'");
        $pending = $this->db->fetch("SELECT COUNT(*) as cnt FROM invoices WHERE payment_status = 'pending'");
        $today = $this->db->fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as revenue FROM invoices WHERE payment_status = 'completed' AND DATE(paid_at) = CURDATE()");

        return [
            'total_invoices'    => (int)($total['cnt'] ?? 0),
            'completed_count'   => (int)($completed['cnt'] ?? 0),
            'total_revenue'     => (float)($completed['revenue'] ?? 0),
            'pending_count'     => (int)($pending['cnt'] ?? 0),
            'today_sales'       => (int)($today['cnt'] ?? 0),
            'today_revenue'     => (float)($today['revenue'] ?? 0),
        ];
    }

    /**
     * Get students (completed invoices with buyer info)
     */
    public function getStudents(): array
    {
        return $this->db->fetchAll(
            "SELECT i.buyer_name, i.buyer_phone, i.buyer_email, i.invoice_no, i.paid_at,
                    c.title as course_title, i.telegram_link
             FROM invoices i
             JOIN courses c ON i.course_id = c.id
             WHERE i.payment_status = 'completed'
             ORDER BY i.paid_at DESC"
        );
    }
}
