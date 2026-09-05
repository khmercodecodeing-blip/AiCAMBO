<?php

namespace App\Services;

class GoogleIdentity
{
    public static function validClaims(array $payload, string $clientId, int $now): bool
    {
        return $clientId !== ''
            && ($payload['aud'] ?? null) === $clientId
            && in_array($payload['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true)
            && isset($payload['exp']) && is_numeric($payload['exp']) && (float) $payload['exp'] > $now
            && in_array($payload['email_verified'] ?? null, [true, 'true'], true)
            && is_string($payload['email'] ?? null)
            && filter_var($payload['email'], FILTER_VALIDATE_EMAIL) !== false
            && is_string($payload['sub'] ?? null) && $payload['sub'] !== '';
    }

    public static function validCsrf($cookie, $posted): bool
    {
        return is_string($cookie) && $cookie !== '' && is_string($posted) && hash_equals($cookie, $posted);
    }
}