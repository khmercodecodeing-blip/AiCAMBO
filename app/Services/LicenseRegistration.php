<?php

namespace App\Services;

use PDO;

class LicenseRegistration
{
    public static function register(PDO $database, array $purchase, string $ip): array
    {
        $key = $purchase['license_key'];
        $reference = $purchase['transaction_ref'];
        $note = "Auto-registered via payment. Plan: {$purchase['plan']}, Amount: \${$purchase['amount']}, Ref: {$reference}";
        $lockName = 'license:' . substr(hash('sha256', $key), 0, 40);
        $lock = $database->prepare('SELECT GET_LOCK(?, 0)');
        $lock->execute([$lockName]);
        if ((int) $lock->fetchColumn() !== 1) {
            return ['status' => 503, 'success' => false, 'message' => 'License registration is busy. Please retry.'];
        }

        try {
            $database->beginTransaction();
            $lookup = $database->prepare('SELECT id, note, expires_at FROM licenses WHERE license_key = ? LIMIT 1 FOR UPDATE');
            $lookup->execute([$key]);
            $license = $lookup->fetch(PDO::FETCH_ASSOC);
            if ($license) {
                if ($reference === '' || !hash_equals($note, (string) $license['note'])) {
                    $database->rollBack();
                    return ['status' => 409, 'success' => false, 'message' => 'License key already exists for another registration.'];
                }
                $licenseId = $license['id'];
            } else {
                $database->prepare('INSERT INTO licenses (license_key, customer_name, expires_at, note) VALUES (?, ?, ?, ?)')
                    ->execute([$key, $purchase['customer_name'], $purchase['expires_at'], $note]);
                $licenseId = $database->lastInsertId();
            }

            if ($purchase['hardware_id'] !== '') {
                $activation = $database->prepare('SELECT hardware_id FROM activations WHERE license_id = ? LIMIT 1 FOR UPDATE');
                $activation->execute([$licenseId]);
                $existing = $activation->fetch(PDO::FETCH_ASSOC);
                if ($existing && $existing['hardware_id'] !== $purchase['hardware_id']) {
                    $database->rollBack();
                    return ['status' => 409, 'success' => false, 'message' => 'License is already bound to another device.'];
                }
                if (!$existing) {
                    $database->prepare('INSERT INTO activations (license_id, hardware_id, pc_name, ip_address) VALUES (?, ?, ?, ?)')
                        ->execute([$licenseId, $purchase['hardware_id'], $purchase['pc_name'], $ip]);
                }
            }

            $database->commit();
            return [
                'status' => 200, 'success' => true, 'message' => 'License registered successfully.',
                'data' => [
                    'license_key' => $key,
                    'expires_at' => $license ? $license['expires_at'] : $purchase['expires_at'],
                    'activated_at' => date('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Throwable $error) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }
            throw $error;
        } finally {
            $database->prepare('SELECT RELEASE_LOCK(?)')->execute([$lockName]);
        }
    }
}