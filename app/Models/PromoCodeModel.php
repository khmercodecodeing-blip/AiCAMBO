<?php

namespace App\Models;

/**
 * PromoCode Model — handles promo code database queries and validation checks
 */
class PromoCodeModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all promo codes
     */
    public function getAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM promo_codes ORDER BY created_at DESC");
    }

    /**
     * Get promo code by ID
     */
    public function getById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM promo_codes WHERE id = :id",
            [':id' => $id]
        );
    }

    /**
     * Get promo code by code string (case-insensitive check)
     */
    public function getByCode(string $code): ?array
    {
        $code = strtoupper(trim($code));
        return $this->db->fetch(
            "SELECT * FROM promo_codes WHERE UPPER(code) = :code",
            [':code' => $code]
        );
    }

    /**
     * Create a new promo code
     */
    public function create(array $data): bool
    {
        $this->db->query(
            "INSERT INTO promo_codes (code, discount_type, discount_value, max_uses, expires_at, is_active)
             VALUES (:code, :discount_type, :discount_value, :max_uses, :expires_at, :is_active)",
            [
                ':code'           => strtoupper(trim($data['code'])),
                ':discount_type'  => $data['discount_type'],
                ':discount_value' => (float)$data['discount_value'],
                ':max_uses'       => !empty($data['max_uses']) ? (int)$data['max_uses'] : null,
                ':expires_at'     => !empty($data['expires_at']) ? $data['expires_at'] : null,
                ':is_active'      => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            ]
        );
        return true;
    }

    /**
     * Delete a promo code
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->query(
            "DELETE FROM promo_codes WHERE id = :id",
            [':id' => $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Increment use count of a code safely
     */
    public function incrementUses(string $code): bool
    {
        $code = strtoupper(trim($code));
        $stmt = $this->db->query(
            "UPDATE promo_codes SET uses_count = uses_count + 1 WHERE UPPER(code) = :code",
            [':code' => $code]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Validate a promo code and calculate discount amount
     */
    public function validateCode(string $code, float $originalPrice): array
    {
        $promo = $this->getByCode($code);

        if (!$promo) {
            return ['valid' => false, 'message' => 'កូដប្រូម៉ូសិនមិនត្រឹមត្រូវទេ (Invalid promo code)'];
        }

        if ((int)$promo['is_active'] !== 1) {
            return ['valid' => false, 'message' => 'កូដប្រូម៉ូសិននេះលែងដំណើរការហើយ (Promo code is inactive)'];
        }

        // Check expiry date
        if ($promo['expires_at'] && strtotime($promo['expires_at']) < time()) {
            return ['valid' => false, 'message' => 'កូដប្រូម៉ូសិននេះបានហួសកំណត់ហើយ (Promo code has expired)'];
        }

        // Check max uses limit
        if ($promo['max_uses'] !== null && (int)$promo['uses_count'] >= (int)$promo['max_uses']) {
            return ['valid' => false, 'message' => 'កូដប្រូម៉ូសិននេះត្រូវបានប្រើអស់កំណត់ហើយ (Promo code has reached usage limit)'];
        }

        // Calculate discount
        $discountAmount = 0.00;
        if ($promo['discount_type'] === 'percentage') {
            $discountAmount = $originalPrice * ((float)$promo['discount_value'] / 100);
        } else {
            $discountAmount = (float)$promo['discount_value'];
        }

        // Ensure discount is not greater than original price
        if ($discountAmount > $originalPrice) {
            $discountAmount = $originalPrice;
        }

        $finalPrice = max(0.00, $originalPrice - $discountAmount);

        return [
            'valid'           => true,
            'code'            => $promo['code'],
            'discount_type'   => $promo['discount_type'],
            'discount_value'  => (float)$promo['discount_value'],
            'discount_amount' => $discountAmount,
            'final_price'     => $finalPrice,
            'message'         => 'កូដត្រូវបានអនុវត្តដោយជោគជ័យ (Code applied successfully)'
        ];
    }
}
