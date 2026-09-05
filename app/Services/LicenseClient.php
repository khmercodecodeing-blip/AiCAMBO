<?php

namespace App\Services;

class LicenseClient
{
    public static function keyForPlan(int $courseId, $requested = null): ?string
    {
        $code = [1 => 'A1', 2 => 'B3', 3 => 'CY'][$courseId] ?? null;
        if ($code === null) {
            return null;
        }
        if ($requested !== null && $requested !== '') {
            if (!is_string($requested)) {
                throw new \InvalidArgumentException('Invalid license key.');
            }
            $raw = strtoupper(str_replace('-', '', trim($requested)));
            $body = substr($raw, 0, 12);
            $signature = strtoupper(substr(hash_hmac('sha256', $body, LICENSE_SIGNING_SECRET), 0, 4));
            if (!preg_match('/^[A-Z0-9]{16}$/', $raw) || substr($body, 0, 2) !== $code
                || !hash_equals($signature, substr($raw, 12))) {
                throw new \InvalidArgumentException('License key does not match this plan.');
            }
        } else {
            $body = $code . strtoupper(bin2hex(random_bytes(5)));
            $raw = $body . strtoupper(substr(hash_hmac('sha256', $body, LICENSE_SIGNING_SECRET), 0, 4));
        }
        return implode('-', str_split($raw, 4));
    }

    public static function payload(array $invoice): array
    {
        $days = [1 => 30, 2 => 90, 3 => 365][(int) $invoice['course_id']] ?? null;
        $paidAt = strtotime($invoice['paid_at'] ?? '');
        if ($days === null || !$paidAt || empty($invoice['license_key'])) {
            throw new \InvalidArgumentException('License purchase is incomplete.');
        }

        return [
            'api_key' => LICENSE_API_KEY,
            'license_key' => $invoice['license_key'],
            'hardware_id' => $invoice['hardware_id'] ?? '',
            'pc_name' => 'Web Purchased',
            'customer_name' => $invoice['buyer_name'],
            'plan' => $days . ' Days',
            'amount' => $invoice['amount'],
            'expires_at' => date('Y-m-d', strtotime('+' . $days . ' days', $paidAt)),
            'transaction_ref' => $invoice['invoice_no'],
        ];
    }

    public static function register(array $invoice): bool
    {
        $request = curl_init(APP_URL . '/key/api/register.php');
        curl_setopt_array($request, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(self::payload($invoice), JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'User-Agent: WebCheckout'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        $transportError = curl_errno($request);
        curl_close($request);
        $body = is_string($response) ? json_decode($response, true) : null;

        $confirmed = $status >= 200 && $status < 300 && is_array($body) && ($body['success'] ?? false) === true;
        if (!$confirmed) {
            error_log('License registration not confirmed: http=' . $status . ' curl=' . $transportError);
        }
        return $confirmed;
    }
}