<?php

namespace App\Models;

class QuantumVaultOrderModel
{
    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
        self::assertUniqueOrderIndex($this->pdo);
    }

    public static function assertUniqueOrderIndex(\PDO $pdo): void
    {
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            foreach ($pdo->query('PRAGMA index_list(invoices)')->fetchAll(\PDO::FETCH_ASSOC) as $index) {
                $name = str_replace("'", "''", $index['name']);
                $columns = $pdo->query("PRAGMA index_info('$name')")->fetchAll(\PDO::FETCH_ASSOC);
                if ((int) $index['unique'] === 1 && empty($index['partial']) && array_column($columns, 'name') === ['qv_order_id']) {
                    return;
                }
            }
        } else {
            $indexes = [];
            foreach ($pdo->query('SHOW INDEX FROM invoices')->fetchAll(\PDO::FETCH_ASSOC) as $index) {
                $indexes[$index['Key_name']][] = $index;
            }
            foreach ($indexes as $columns) {
                if (count($columns) === 1 && (int) $columns[0]['Non_unique'] === 0
                    && $columns[0]['Column_name'] === 'qv_order_id' && $columns[0]['Sub_part'] === null) {
                    return;
                }
            }
        }
        throw new \RuntimeException('Supplier order uniqueness is not configured.');
    }

    public function get(string $invoiceNo): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM invoices WHERE invoice_no = :invoice');
        $statement->execute([':invoice' => $invoiceNo]);
        return $statement->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function claim(string $invoiceNo): bool
    {
        $statement = $this->pdo->prepare("UPDATE invoices SET qv_status = 'processing', qv_attempted_at = :attempted
            WHERE invoice_no = :invoice AND payment_status = 'completed' AND qv_status = 'pending'
            AND qv_product_key IS NOT NULL AND delivered_stock IS NULL
            AND (qv_attempted_at IS NULL OR qv_attempted_at < :cooldown)");
        $statement->execute([':invoice' => $invoiceNo, ':attempted' => date('Y-m-d H:i:s'), ':cooldown' => date('Y-m-d H:i:s', time() - 60)]);
        return $statement->rowCount() === 1;
    }

    public function finishAttempt(string $invoiceNo, bool $purchaseStarted): void
    {
        $statement = $this->pdo->prepare("UPDATE invoices SET qv_status = :status WHERE invoice_no = :invoice AND qv_status = 'processing'");
        $statement->execute([':invoice' => $invoiceNo, ':status' => $purchaseStarted ? 'review' : 'pending']);
    }

    public function saveResponse(string $invoiceNo, array $response): void
    {
        $statement = $this->pdo->prepare("UPDATE invoices SET qv_response = :response WHERE invoice_no = :invoice AND qv_status = 'processing'");
        $statement->execute([':invoice' => $invoiceNo, ':response' => json_encode($response, JSON_THROW_ON_ERROR)]);
    }

    public function complete(string $invoiceNo, array $item, string $content): void
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare("UPDATE invoices SET qv_order_id = :order_id, delivered_stock = :content,
                qv_response = :response, qv_status = 'delivered'
                WHERE invoice_no = :invoice AND payment_status = 'completed' AND qv_status IN ('processing', 'review')
                AND delivered_stock IS NULL AND qv_product_key = :product
                AND COALESCE(qv_variant_key, '') = :variant");
            $statement->execute([
                ':invoice' => $invoiceNo, ':order_id' => $item['orderId'], ':content' => $content,
                ':response' => json_encode($item, JSON_THROW_ON_ERROR), ':product' => $item['productKey'],
                ':variant' => $item['variantKey'] ?? '',
            ]);
            if ($statement->rowCount() !== 1) {
                throw new \RuntimeException('Delivery is no longer claimable.');
            }
            $this->pdo->commit();
        } catch (\Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
}