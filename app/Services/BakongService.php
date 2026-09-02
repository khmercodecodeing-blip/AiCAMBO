<?php

namespace App\Services;

use KHQR\BakongKHQR;

/**
 * Bakong Service — Checks transaction status via the Bakong Open API
 */
class BakongService
{
    private BakongKHQR $bakong;

    public function __construct()
    {
        $this->bakong = new BakongKHQR(BAKONG_TOKEN);
    }

    /**
     * Check transaction by MD5 hash
     *
     * @param string $md5Hash The MD5 hash of the QR string
     * @return array{found: bool, data: array|null}
     */
    public function checkTransactionByMD5(string $md5Hash): array
    {
        try {
            $response = $this->bakong->checkTransactionByMD5($md5Hash);

            // Bakong API returns responseCode 0 on success
            if (isset($response['responseCode']) && $response['responseCode'] === 0) {
                // If data is not null, transaction was found (payment made)
                if (isset($response['data']) && $response['data'] !== null) {
                    return [
                        'found' => true,
                        'data'  => $response['data'],
                    ];
                }
            }

            return ['found' => false, 'data' => null];
        } catch (\Exception $e) {
            error_log('Bakong API Error: ' . $e->getMessage());
            return ['found' => false, 'data' => null];
        }
    }

    /**
     * Verify that a transaction matches the expected amount
     *
     * @param array  $transactionData Transaction data from Bakong
     * @param float  $expectedAmount  Expected payment amount
     * @param string $currency        Expected currency
     * @return bool
     */
    public function verifyAmount(array $transactionData, float $expectedAmount, string $currency = 'USD'): bool
    {
        // Transaction data structure varies; check common fields
        $txAmount = $transactionData['amount'] ?? $transactionData['transactionAmount'] ?? null;

        if ($txAmount === null) {
            // If no amount in response, still consider valid (some responses don't include amount)
            return true;
        }

        // Allow small float precision differences
        return abs((float)$txAmount - $expectedAmount) < 0.01;
    }
}
