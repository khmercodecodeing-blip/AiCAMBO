<?php

namespace App\Services;

use App\Models\QuantumVaultOrderModel;

class QuantumVaultDeliveryService
{
    private ?QuantumVaultOrderModel $orders;
    private QuantumVaultClient $client;

    public function __construct(?QuantumVaultOrderModel $orders = null, ?QuantumVaultClient $client = null)
    {
        $this->orders = $orders;
        $this->client = $client ?? new QuantumVaultClient();
    }

    public static function checkout(array $course, float $amount): array
    {
        if (empty($course['qv_product_key'])) {
            return [];
        }
        if (!QuantumVaultClient::enabled() || ($course['currency'] ?? '') !== 'USD'
            || (int) ($course['id'] ?? 0) <= 3 || ($course['type'] ?? '') !== 'tool'
            || !is_finite($amount) || $amount <= 0 || (float) $course['qv_max_cost'] > $amount) {
            throw new \RuntimeException('Supplier checkout unavailable.');
        }
        (new QuantumVaultClient())->quote($course['qv_product_key'], $course['qv_variant_key'] ?: null, (float) $course['qv_max_cost']);
        return [
            'qv_product_key' => $course['qv_product_key'],
            'qv_variant_key' => $course['qv_variant_key'] ?: null,
            'qv_max_cost' => $course['qv_max_cost'],
            'qv_status' => 'pending',
        ];
    }

    public function deliver(array $invoice): string
    {
        if (($invoice['payment_status'] ?? '') !== 'completed' || empty($invoice['qv_product_key'])) {
            return 'not_required';
        }
        $claimed = false;
        $purchaseStarted = false;
        try {
            $orders = $this->orders ??= new QuantumVaultOrderModel();
            if (!QuantumVaultClient::enabled() || !$orders->claim($invoice['invoice_no'])) {
                return $orders->get($invoice['invoice_no'])['qv_status'] ?? 'pending';
            }
            $claimed = true;
            $current = $orders->get($invoice['invoice_no']);
            if (!$current || $current['currency'] !== 'USD' || (float) $current['qv_max_cost'] > (float) $current['amount']) {
                throw new \RuntimeException('Supplier purchase cost is not approved.');
            }
            $this->client->quote($current['qv_product_key'], $current['qv_variant_key'], (float) $current['qv_max_cost']);
            $purchaseStarted = true;
            $response = $this->client->purchase($current['qv_product_key'], $current['qv_variant_key'],
                function (array $received) use ($orders, $invoice): void {
                    $orders->saveResponse($invoice['invoice_no'], $received);
                });
            $orders->saveResponse($invoice['invoice_no'], $response);
            $items = $response['order']['items'] ?? [];
            if (($response['success'] ?? false) !== true || ($response['order']['fulfilled'] ?? 0) !== 1 || count($items) !== 1) {
                throw new \RuntimeException('Supplier response requires review.');
            }
            $content = QuantumVaultClient::delivery($items[0], $current['qv_product_key'], $current['qv_variant_key']);
            $orders->complete($invoice['invoice_no'], $items[0], $content);
            return 'delivered';
        } catch (\Throwable $error) {
            if ($claimed) {
                try {
                    $this->orders->finishAttempt($invoice['invoice_no'], $purchaseStarted);
                } catch (\Throwable $ignored) {
                }
            }
            error_log('QuantumVault delivery pending for invoice ' . $invoice['invoice_no']);
            return $purchaseStarted ? 'review' : 'pending';
        }
    }

    public function recover(string $invoiceNo, string $orderId): void
    {
        $orders = $this->orders ??= new QuantumVaultOrderModel();
        $invoice = $orders->get($invoiceNo);
        if (!$invoice || $invoice['payment_status'] !== 'completed'
            || !in_array($invoice['qv_status'], ['processing', 'review'], true) || empty($invoice['qv_attempted_at'])) {
            throw new \RuntimeException('Purchase is not awaiting recovery.');
        }
        $item = $this->client->order($orderId);
        $content = QuantumVaultClient::delivery($item, $invoice['qv_product_key'], $invoice['qv_variant_key']);
        $deliveredAt = strtotime($item['deliveredAt'] ?? '');
        $attemptedAt = strtotime($invoice['qv_attempted_at']);
        if (!$deliveredAt || !$attemptedAt || $deliveredAt < $attemptedAt - 5 || $deliveredAt > time() + 300) {
            throw new \RuntimeException('Supplier order date does not match the attempt.');
        }
        $orders->complete($invoiceNo, $item, $content);
    }
}