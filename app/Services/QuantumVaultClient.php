<?php

namespace App\Services;

class QuantumVaultClient
{
    private string $apiKey;
    private ?\Closure $transport;

    public function __construct(?string $apiKey = null, ?callable $transport = null)
    {
        $resolvedKey = $apiKey;
        if ($resolvedKey === null || $resolvedKey === '') {
            $envVal = getenv('QUANTUMVAULT_API_KEY');
            if ($envVal !== false && $envVal !== '') {
                $resolvedKey = $envVal;
            } elseif (!empty($_ENV['QUANTUMVAULT_API_KEY'])) {
                $resolvedKey = $_ENV['QUANTUMVAULT_API_KEY'];
            } elseif (defined('QUANTUMVAULT_API_KEY')) {
                $resolvedKey = QUANTUMVAULT_API_KEY;
            }
        }
        $this->apiKey = (string) $resolvedKey;
        $this->transport = $transport ? \Closure::fromCallable($transport) : null;
    }

    public static function enabled(): bool
    {
        $enabled = getenv('QUANTUMVAULT_ENABLED');
        if ($enabled === false || $enabled === null || $enabled === '') {
            $enabled = $_ENV['QUANTUMVAULT_ENABLED'] ?? (defined('QUANTUMVAULT_ENABLED') ? QUANTUMVAULT_ENABLED : '0');
        }
        return (string) $enabled === '1';
    }

    public function products(): array
    {
        return $this->request('GET', '/products')['products'] ?? [];
    }

    public function balance(): array
    {
        return $this->request('GET', '/balance');
    }

    public function orders(): array
    {
        return $this->request('GET', '/orders?limit=100')['orders'] ?? [];
    }

    public function order(string $orderId): array
    {
        return $this->request('GET', '/orders/' . rawurlencode($orderId))['order'] ?? [];
    }

    public function quote(string $productKey, ?string $variantKey, float $maxCost): array
    {
        $product = $this->request('GET', '/products/' . rawurlencode($productKey))['product'] ?? [];
        $offer = $product;
        $variants = $product['variants'] ?? [];
        if ($variants) {
            $offer = null;
            foreach ($variants as $variant) {
                if (($variant['key'] ?? null) === $variantKey) {
                    $offer = $variant;
                    break;
                }
            }
        } elseif ($variantKey !== null && $variantKey !== '') {
            throw new \RuntimeException('Unexpected supplier variant.');
        }
        if (!$offer || ($product['productKey'] ?? '') !== $productKey
            || ($product['currency'] ?? '') !== 'USD'
            || ($product['inStock'] ?? false) !== true || ($offer['inStock'] ?? false) !== true
            || !is_numeric($offer['price'] ?? null) || (float) $offer['price'] < 0
            || !is_finite($maxCost) || $maxCost <= 0 || (float) $offer['price'] > $maxCost) {
            throw new \RuntimeException('Supplier product unavailable or above the approved cost.');
        }
        return ['price' => (float) $offer['price'], 'currency' => 'USD', 'product' => $product];
    }

    public function purchase(string $productKey, ?string $variantKey, ?callable $recordResponse = null): array
    {
        $body = ['productKey' => $productKey, 'quantity' => 1];
        if ($variantKey !== null && $variantKey !== '') {
            $body['variantKey'] = $variantKey;
        }
        return $this->request('POST', '/purchase', $body, $recordResponse);
    }

    public static function delivery(array $item, string $productKey, ?string $variantKey): string
    {
        if (empty($item['orderId']) || ($item['productKey'] ?? '') !== $productKey
            || ($item['variantKey'] ?? null) !== ($variantKey ?: null)) {
            throw new \RuntimeException('Supplier delivery does not match this purchase.');
        }
        $lines = [];
        foreach (($item['fields'] ?? []) as $field) {
            if (!is_string($field['name'] ?? null) || !is_string($field['label'] ?? null)
                || !is_scalar($field['value'] ?? null)) {
                throw new \RuntimeException('Invalid supplier delivery field.');
            }
            $lines[] = $field['label'] . ': ' . (string) $field['value'];
        }
        if (!$lines) {
            throw new \RuntimeException('Supplier delivery is empty.');
        }
        return implode("\n", $lines);
    }

    private function request(string $method, string $path, ?array $body = null, ?callable $recordResponse = null): array
    {
        if ($this->apiKey === '' || preg_match('/[\r\n]/', $this->apiKey)) {
            throw new \RuntimeException('Supplier API key is not configured.');
        }
        if ($this->transport) {
            $response = ($this->transport)($method, $path, $body);
        } else {
            $handle = curl_init('https://www.quantumvault.me/api/v1' . $path);
            curl_setopt_array($handle, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => ['X-API-Key: ' . $this->apiKey, 'Accept: application/json', 'Content-Type: application/json'],
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 35,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            if ($body !== null) {
                curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR));
            }
            $response = ['body' => curl_exec($handle), 'status' => curl_getinfo($handle, CURLINFO_HTTP_CODE), 'error' => curl_errno($handle)];
            curl_close($handle);
        }
        if ($recordResponse !== null) {
            $recordResponse([
                'http_status' => (int) ($response['status'] ?? 0),
                'curl_error' => (int) ($response['error'] ?? 0),
                'body_base64' => base64_encode(is_string($response['body'] ?? null) ? $response['body'] : ''),
            ]);
        }
        if (($response['error'] ?? 0) !== 0 || ($response['status'] ?? 0) < 200 || ($response['status'] ?? 0) >= 300) {
            throw new \RuntimeException('Supplier request failed; HTTP ' . (int) ($response['status'] ?? 0) . '.');
        }
        try {
            $decoded = json_decode($response['body'], true, 64, JSON_THROW_ON_ERROR);
        } catch (\Throwable $error) {
            throw new \RuntimeException('Invalid supplier response.');
        }
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid supplier response.');
        }
        return $decoded;
    }
}