<?php

namespace App\Models;

use PDO;
use Exception;

/**
 * ProductStockModel — Handles stock inventory for account/mail products
 */
class ProductStockModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get available (unsold) stock count for a product
     */
    public function getAvailableCount(int $courseId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM product_stocks WHERE course_id = :course_id AND is_sold = 0",
            [':course_id' => $courseId]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Get sold stock count for a product
     */
    public function getSoldCount(int $courseId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM product_stocks WHERE course_id = :course_id AND is_sold = 1",
            [':course_id' => $courseId]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Get all stock items (both sold and unsold) for admin
     */
    public function getAllStock(int $courseId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM product_stocks WHERE course_id = :course_id ORDER BY is_sold ASC, created_at DESC",
            [':course_id' => $courseId]
        );
    }

    /**
     * Add multiple stock items
     * Returns the number of successfully added items
     */
    public function addStock(int $courseId, array $lines): int
    {
        $added = 0;
        $pdo = $this->db->getConnection();

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "INSERT INTO product_stocks (course_id, stock_content, is_sold) 
                 VALUES (:course_id, :stock_content, 0)"
            );

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $stmt->execute([
                    ':course_id'     => $courseId,
                    ':stock_content' => $line
                ]);
                $added++;
            }
            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Failed to add stock: " . $e->getMessage());
            throw $e;
        }

        return $added;
    }

    /**
     * Delete an unsold stock item
     */
    public function deleteStockItem(int $id): bool
    {
        $stmt = $this->db->query(
            "DELETE FROM product_stocks WHERE id = :id AND is_sold = 0",
            [':id' => $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Atomically assign one available stock item to an invoice.
     * Prevents race conditions.
     */
    public function assignStockToInvoice(int $courseId, string $invoiceNo): ?string
    {
        $pdo = $this->db->getConnection();

        try {
            $pdo->beginTransaction();

            // 1. Fetch one available stock item using FOR UPDATE to lock the row
            $stmt = $pdo->prepare(
                "SELECT id, stock_content FROM product_stocks 
                 WHERE course_id = :course_id AND is_sold = 0 
                 LIMIT 1 FOR UPDATE"
            );
            $stmt->execute([':course_id' => $courseId]);
            $item = $stmt->fetch();

            if (!$item) {
                $pdo->commit();
                return null; // Out of stock!
            }

            // 2. Mark it as sold
            $updateStock = $pdo->prepare(
                "UPDATE product_stocks 
                 SET is_sold = 1, invoice_no = :invoice_no, sold_at = NOW() 
                 WHERE id = :id"
            );
            $updateStock->execute([
                ':invoice_no' => $invoiceNo,
                ':id'         => $item['id']
            ]);

            // 3. Save a copy inside the invoices table delivered_stock column
            $updateInvoice = $pdo->prepare(
                "UPDATE invoices SET delivered_stock = :delivered_stock 
                 WHERE invoice_no = :invoice_no"
            );
            $updateInvoice->execute([
                ':delivered_stock' => $item['stock_content'],
                ':invoice_no'      => $invoiceNo
            ]);

            $pdo->commit();
            return $item['stock_content'];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Failed to assign stock to invoice $invoiceNo: " . $e->getMessage());
            return null;
        }
    }
}
