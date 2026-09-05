<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\Database;
use App\Services\QuantumVaultClient;
use App\Services\QuantumVaultDeliveryService;

class QuantumVaultController
{
    public function index(): void
    {
        $this->protect();
        $configured = $this->configured();
        $schemaReady = false;
        $catalog = [];
        $providerOrders = [];
        $invoices = [];
        $notices = [];
        $balanceLabel = 'Unavailable';
        $refreshed = ($_GET['refresh'] ?? '') === '1';

        if (!$configured) {
            $notices[] = QuantumVaultClient::enabled()
                ? 'QuantumVault API key is missing.' : 'QuantumVault integration is disabled.';
        } else {
            try {
                $database = Database::getInstance();
                $this->checkSchema($database);
                $schemaReady = true;
                $invoices = $database->fetchAll(
                    "SELECT invoice_no, course_id, qv_status, qv_order_id, qv_product_key,
                            qv_variant_key, paid_at, qv_attempted_at
                     FROM invoices WHERE payment_status = 'completed' AND course_id > 3
                       AND qv_product_key IS NOT NULL AND qv_product_key <> ''
                       AND qv_status IN ('pending', 'processing', 'review')
                     ORDER BY id DESC LIMIT 50"
                );
            } catch (\Throwable $error) {
                $schemaReady = false;
                $notices[] = 'QuantumVault storage is unavailable. Check database connectivity and apply the required migration before importing or recovering orders.';
            }

            if ($refreshed) {
                $client = new QuantumVaultClient();
                try {
                    $catalog = $this->catalogRows($client->products());
                } catch (\Throwable $error) {
                    $notices[] = 'Supplier catalog is temporarily unavailable.';
                }
                try {
                    $providerOrders = $this->orderRows($client->orders());
                } catch (\Throwable $error) {
                    $notices[] = 'Supplier order metadata is temporarily unavailable.';
                }
                try {
                    $balance = $client->balance();
                    $amount = $balance['balance'] ?? $balance['availableBalance'] ?? null;
                    $currency = $balance['currency'] ?? null;
                    if (is_numeric($amount) && is_finite((float) $amount)
                        && is_string($currency) && preg_match('/^[A-Z]{3}$/D', $currency)) {
                        $balanceLabel = number_format((float) $amount, 4) . ' ' . $currency;
                    }
                } catch (\Throwable $error) {
                    $notices[] = 'Supplier balance is temporarily unavailable.';
                }
            }
        }

        $pageTitle = 'QuantumVault - Admin';
        require APP_ROOT . '/app/views/admin/quantumvault.php';
    }

    public function import(): void
    {
        if (!$this->protect(true)) {
            return;
        }
        $connection = null;
        try {
            $this->requireConfigured();
            $mapping = json_decode($this->input('mapping'), true, 8, JSON_THROW_ON_ERROR);
            if (!is_array($mapping)) {
                throw new \RuntimeException('Invalid mapping.');
            }
            $productKey = $this->identifier($mapping['product'] ?? null);
            $variantKey = ($mapping['variant'] ?? null) === null
                ? null : $this->identifier($mapping['variant']);
            $retail = $this->money($this->input('price'), 2);
            $maxCost = $this->money($this->input('max_cost'), 4);
            if ((float) $maxCost > (float) $retail) {
                throw new \RuntimeException('Invalid cost limit.');
            }
            $database = Database::getInstance();
            $this->checkSchema($database);
            $quote = (new QuantumVaultClient())->quote($productKey, $variantKey, (float) $maxCost);
            $product = $quote['product'] ?? [];
            $title = $product['name'] ?? $product['title'] ?? null;
            $description = $product['description'] ?? '';
            foreach (($product['variants'] ?? []) as $variant) {
                if (($variant['key'] ?? null) === $variantKey && is_string($variant['name'] ?? null)) {
                    $title .= ' - ' . $variant['name'];
                    break;
                }
            }
            if (!is_string($title) || trim($title) === '' || strlen($title) > 255 || !is_string($description)) {
                throw new \RuntimeException('Invalid product metadata.');
            }

            $connection = $database->getConnection();
            $connection->beginTransaction();
            $duplicate = $database->fetch(
                "SELECT id FROM courses WHERE qv_product_key = :product
                 AND COALESCE(qv_variant_key, '') = :variant LIMIT 1 FOR UPDATE",
                [':product' => $productKey, ':variant' => $variantKey ?? '']
            );
            if ($duplicate) {
                throw new \RuntimeException('Mapping already exists.');
            }
            $courseId = (new CourseModel())->create([
                'title' => trim($title), 'description' => $description,
                'price' => $retail, 'original_price' => null, 'currency' => 'USD',
                'type' => 'tool', 'thumbnail' => null, 'video_url' => '',
                'telegram_group_id' => '', 'download_link' => '', 'is_active' => 0,
            ]);
            if ($courseId <= 3) {
                throw new \RuntimeException('Reserved product ID.');
            }
            $database->query(
                'UPDATE courses SET qv_product_key = :product, qv_variant_key = :variant,
                 qv_max_cost = :cost WHERE id = :id',
                [':product' => $productKey, ':variant' => $variantKey, ':cost' => $maxCost, ':id' => $courseId]
            );
            $connection->commit();
            flash('success', 'Supplier product imported as an inactive tool.');
        } catch (\Throwable $error) {
            if ($connection !== null && $connection->inTransaction()) {
                $connection->rollBack();
            }
            flash('error', 'Import failed. Check the selected product, stock, USD prices, existing mappings and database setup.');
        }
        $this->back();
    }

    public function recover(): void
    {
        if (!$this->protect(true)) {
            return;
        }
        try {
            $this->requireConfigured();
            $invoiceNo = $this->identifier($this->input('invoice_no'));
            $orderId = $this->identifier($this->input('order_id'));
            $this->checkSchema(Database::getInstance());
            (new QuantumVaultDeliveryService())->recover($invoiceNo, $orderId);
            flash('success', 'Order recovery completed.');
        } catch (\Throwable $error) {
            flash('error', 'Recovery could not be completed. Verify the invoice and supplier order reference.');
        }
        $this->back();
    }

    public function retry(): void
    {
        if (!$this->protect(true)) {
            return;
        }
        try {
            $this->requireConfigured();
            $invoiceNo = $this->identifier($this->input('invoice_no'));
            $database = Database::getInstance();
            $this->checkSchema($database);
            $invoice = $database->fetch('SELECT * FROM invoices WHERE invoice_no = :invoice', [':invoice' => $invoiceNo]);
            if (!$invoice || ($invoice['payment_status'] ?? '') !== 'completed'
                || ($invoice['qv_status'] ?? '') !== 'pending' || (int) ($invoice['course_id'] ?? 0) <= 3
                || empty($invoice['qv_product_key']) || !empty($invoice['qv_order_id'])) {
                throw new \RuntimeException('Invoice is not retryable.');
            }
            $status = (new QuantumVaultDeliveryService())->deliver($invoice);
            flash($status === 'delivered' ? 'success' : 'error', $status === 'delivered'
                ? 'Delivery completed.' : 'Delivery is not complete. Check the invoice status before taking further action.');
        } catch (\Throwable $error) {
            flash('error', 'Retry could not be completed. Only paid, pending supplier invoices without an order reference can be retried.');
        }
        $this->back();
    }

    private function protect(bool $post = false): bool
    {
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        require_admin();
        if ($post && (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
            || !verify_csrf($this->input('csrf_token')))) {
            http_response_code(403);
            flash('error', 'Invalid request.');
            $this->back();
            return false;
        }
        return true;
    }

    private function configured(): bool
    {
        return QuantumVaultClient::enabled() && trim((string) getenv('QUANTUMVAULT_API_KEY')) !== '';
    }

    private function requireConfigured(): void
    {
        if (!$this->configured()) {
            throw new \RuntimeException('Integration unavailable.');
        }
    }

    private function checkSchema(Database $database): void
    {
        $database->query('SELECT qv_product_key, qv_variant_key, qv_max_cost FROM courses WHERE 1 = 0');
        $database->query('SELECT qv_product_key, qv_variant_key, qv_max_cost, qv_status,
            qv_order_id, qv_response, qv_attempted_at, delivered_stock FROM invoices WHERE 1 = 0');
        \App\Models\QuantumVaultOrderModel::assertUniqueOrderIndex($database->getConnection());
    }

    private function input(string $name): string
    {
        return is_string($_POST[$name] ?? null) ? trim($_POST[$name]) : '';
    }

    private function identifier($value): string
    {
        if (!is_string($value) || $value === '' || strlen($value) > 191 || preg_match('/[\x00-\x1f\x7f]/', $value)) {
            throw new \RuntimeException('Invalid reference.');
        }
        return $value;
    }

    private function money(string $value, int $scale): string
    {
        if (!preg_match('/^\d{1,8}(?:\.\d{1,' . $scale . '})?$/D', $value) || (float) $value <= 0) {
            throw new \RuntimeException('Invalid price.');
        }
        return number_format((float) $value, $scale, '.', '');
    }

    private function catalogRows(array $products): array
    {
        $rows = [];
        foreach ($products as $product) {
            if (!is_array($product) || !is_string($product['productKey'] ?? null)) {
                continue;
            }
            $variants = $product['variants'] ?? [];
            if (!is_array($variants)) {
                continue;
            }
            foreach ($variants ?: [null] as $variant) {
                if ($variant !== null && (!is_array($variant) || !is_string($variant['key'] ?? null))) {
                    continue;
                }
                $offer = $variant ?? $product;
                $rows[] = [
                    'product' => $product['productKey'], 'variant' => $variant['key'] ?? null,
                    'name' => $this->text($product['name'] ?? $product['title'] ?? ''),
                    'variant_name' => $this->text($variant['name'] ?? $variant['label'] ?? $variant['key'] ?? ''),
                    'price' => is_numeric($offer['price'] ?? null) && is_finite((float) $offer['price'])
                        ? number_format((float) $offer['price'], 4) : 'Unavailable',
                    'currency' => $this->text($product['currency'] ?? ''),
                    'stock' => ($product['inStock'] ?? false) === true && ($offer['inStock'] ?? false) === true,
                ];
            }
        }
        return $rows;
    }

    private function orderRows(array $orders): array
    {
        $rows = [];
        foreach (array_slice($orders, 0, 100) as $order) {
            if (!is_array($order)) {
                continue;
            }
            $row = [];
            foreach (['orderId', 'productKey', 'variantKey', 'status', 'createdAt'] as $field) {
                $row[$field] = $this->text($field === 'createdAt' ? ($order['date'] ?? $order['createdAt'] ?? '') : ($order[$field] ?? ''));
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private function text($value): string
    {
        return is_string($value) || is_numeric($value) ? (string) $value : '';
    }

    private function back(): void
    {
        redirect('/' . ADMIN_PREFIX . '/quantumvault');
    }
}