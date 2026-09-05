<?php

namespace App\Services;

use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

/**
 * KHQR Service — Generates payment QR codes using the Bakong KHQR library
 */
class KHQRService
{
    /**
     * Generate a KHQR payment QR code
     *
     * @param float  $amount    Payment amount
     * @param string $invoiceNo Invoice number (used as bill number for tracking)
     * @return array{qr: string, md5: string}
     */
    public function generatePaymentQR(float $amount, string $invoiceNo, ?string $paymentCurrency = null): array
    {
        $paymentCurrency = $paymentCurrency ?? BAKONG_CURRENCY;
        if (!in_array($paymentCurrency, ['USD', 'KHR'], true) || !is_finite($amount) || $amount <= 0) {
            throw new \InvalidArgumentException('Unsupported currency or invalid payment amount.');
        }
        $currency = $paymentCurrency === 'KHR'
            ? KHQRData::CURRENCY_KHR
            : KHQRData::CURRENCY_USD;

        $scale = $paymentCurrency === 'KHR' ? 1 : 100;
        if (abs($amount * $scale - round($amount * $scale)) > 0.000001) {
            throw new \InvalidArgumentException('Invalid payment precision.');
        }

        // Sanitize merchant name and store label to conform to EMVCo alphanumeric standards
        $merchantName = preg_replace('/[^A-Za-z0-9 ]/', '', BAKONG_MERCHANT_NAME);
        $storeLabel = preg_replace('/[^A-Za-z0-9 ]/', '', APP_NAME);

        $individualInfo = new IndividualInfo(
            bakongAccountID: BAKONG_ACCOUNT_ID,
            merchantName: $merchantName,
            merchantCity: BAKONG_MERCHANT_CITY,
            currency: $currency,
            amount: $amount,
            billNumber: $invoiceNo,
            storeLabel: $storeLabel
        );

        $result = BakongKHQR::generateIndividual($individualInfo);

        return [
            'qr'  => $result->data['qr'],
            'md5' => $result->data['md5'],
        ];
    }

    /**
     * Verify a QR string is valid
     */
    public function verifyQR(string $qrString): bool
    {
        $result = BakongKHQR::verify($qrString);
        return $result->isValid;
    }
}
