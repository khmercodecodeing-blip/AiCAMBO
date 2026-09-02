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
    public function generatePaymentQR(float $amount, string $invoiceNo): array
    {
        $currency = BAKONG_CURRENCY === 'KHR'
            ? KHQRData::CURRENCY_KHR
            : KHQRData::CURRENCY_USD;

        // If KHR, amount must be integer
        if ($currency === KHQRData::CURRENCY_KHR) {
            $amount = (int) round($amount);
        }

        // Sanitize merchant name and store label to conform to EMVCo alphanumeric standards
        $merchantName = preg_replace('/[^A-Za-z0-9 ]/', '', BAKONG_MERCHANT_NAME);
        $storeLabel = preg_replace('/[^A-Za-z0-9 ]/', '', APP_NAME);

        $individualInfo = new IndividualInfo(
            bakongAccountID: BAKONG_ACCOUNT_ID,
            merchantName: $merchantName,
            merchantCity: BAKONG_MERCHANT_CITY,
            currency: $currency,
            amount: $amount
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
