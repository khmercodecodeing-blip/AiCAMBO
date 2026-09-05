<?php

namespace App\Models;

/**
 * Course Model — CRUD operations for courses table
 */
class CourseModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all active courses
     */
    public function getAll(): array
    {
        $courses = $this->db->fetchAll(
            "SELECT c.*, COALESCE(i.student_count, 0) as student_count
             FROM courses c
             LEFT JOIN (
                 SELECT course_id, COUNT(*) as student_count
                 FROM invoices
                 WHERE payment_status = 'completed'
                 GROUP BY course_id
             ) i ON c.id = i.course_id
             WHERE c.is_active = 1 AND c.id NOT IN (1, 2, 3)
             ORDER BY c.created_at DESC"
        );
        $this->populateStockInfo($courses);
        return $courses;
    }

    /**
     * Get all active products of a given type ('tool' or 'course')
     */
    public function getAllByType(string $type): array
    {
        $courses = $this->db->fetchAll(
            "SELECT c.*, COALESCE(i.student_count, 0) as student_count
             FROM courses c
             LEFT JOIN (
                 SELECT course_id, COUNT(*) as student_count
                 FROM invoices
                 WHERE payment_status = 'completed'
                 GROUP BY course_id
             ) i ON c.id = i.course_id
             WHERE c.is_active = 1 AND c.id NOT IN (1, 2, 3) AND c.type = :type
             ORDER BY c.created_at DESC",
            [':type' => $type]
        );
        $this->populateStockInfo($courses);
        return $courses;
    }

    /**
     * Get all active AI Pro accounts (QuantumVault reseller products and AI tools)
     */
    public function getAllAiAccounts(): array
    {
        $courses = $this->db->fetchAll(
            "SELECT c.*, COALESCE(i.student_count, 0) as student_count
             FROM courses c
             LEFT JOIN (
                 SELECT course_id, COUNT(*) as student_count
                 FROM invoices
                 WHERE payment_status = 'completed'
                 GROUP BY course_id
             ) i ON c.id = i.course_id
             WHERE c.is_active = 1 AND c.id NOT IN (1, 2, 3)
               AND (
                   (c.qv_product_key IS NOT NULL AND c.qv_product_key <> '')
                   OR LOWER(c.title) LIKE '%ai%'
                   OR LOWER(c.title) LIKE '%gemini%'
                   OR LOWER(c.title) LIKE '%chatgpt%'
                   OR LOWER(c.title) LIKE '%gpt%'
                   OR LOWER(c.title) LIKE '%canva%'
                   OR LOWER(c.title) LIKE '%claude%'
               )
             ORDER BY c.created_at DESC"
        );
        $this->populateStockInfo($courses);
        return $courses;
    }

    /**
     * Get all courses (including inactive) for admin
     */
    public function getAllAdmin(): array
    {
        $courses = $this->db->fetchAll(
            "SELECT c.*, COALESCE(i.student_count, 0) as student_count
             FROM courses c
             LEFT JOIN (
                 SELECT course_id, COUNT(*) as student_count
                 FROM invoices
                 WHERE payment_status = 'completed'
                 GROUP BY course_id
             ) i ON c.id = i.course_id
             ORDER BY c.created_at DESC"
        );
        $this->populateStockInfo($courses);
        return $courses;
    }

    /**
     * Get course by ID
     */
    public function getById(int $id): ?array
    {
        $course = $this->db->fetch(
            "SELECT c.*, COALESCE(i.student_count, 0) as student_count
             FROM courses c
             LEFT JOIN (
                 SELECT course_id, COUNT(*) as student_count
                 FROM invoices
                 WHERE payment_status = 'completed'
                 GROUP BY course_id
             ) i ON c.id = i.course_id
             WHERE c.id = :id",
            [':id' => $id]
        );
        if ($course) {
            $list = [&$course];
            $this->populateStockInfo($list);
        }
        return $course;
    }

    /**
     * Populate real-time stock information for QuantumVault and local tools
     */
    public function populateStockInfo(array &$courses): void
    {
        if (empty($courses)) {
            return;
        }
        $qvStockMap = class_exists('\App\Services\QuantumVaultClient')
            ? \App\Services\QuantumVaultClient::getStockMap()
            : [];

        $toolIds = [];
        foreach ($courses as $c) {
            if (empty($c['qv_product_key']) && ($c['type'] ?? '') === 'tool') {
                $toolIds[] = (int) ($c['id'] ?? 0);
            }
        }
        $localStocks = [];
        if (!empty($toolIds)) {
            try {
                $inQuery = implode(',', array_filter($toolIds));
                if ($inQuery !== '') {
                    $rows = $this->db->fetchAll("SELECT course_id, COUNT(*) as cnt FROM product_stocks WHERE course_id IN ($inQuery) AND is_sold = 0 GROUP BY course_id");
                    foreach ($rows as $r) {
                        $localStocks[(int) $r['course_id']] = (int) $r['cnt'];
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        foreach ($courses as &$course) {
            if (!empty($course['qv_product_key'])) {
                $pKey = (string) $course['qv_product_key'];
                $vKey = (string) ($course['qv_variant_key'] ?? '');
                $pKeyLower = strtolower(trim($pKey));
                $vKeyLower = strtolower(trim($vKey));
                $lookup = ($vKey !== '') ? ($pKey . ':' . $vKey) : $pKey;
                $lookupLower = ($vKeyLower !== '') ? ($pKeyLower . ':' . $vKeyLower) : $pKeyLower;

                $info = $qvStockMap[$lookup] ?? ($qvStockMap[$lookupLower] ?? ($qvStockMap[$pKey] ?? ($qvStockMap[$pKeyLower] ?? null)));

                if (!$info && class_exists('\App\Services\QuantumVaultClient') && \App\Services\QuantumVaultClient::enabled()) {
                    try {
                        $client = new \App\Services\QuantumVaultClient();
                        $qRes = $client->quote($pKeyLower, $vKeyLower !== '' ? $vKeyLower : null, 999999);
                        $prod = $qRes['product'] ?? [];
                        if (!empty($prod)) {
                            $info = [
                                'stock' => $prod['stock'] ?? null,
                                'inStock' => !empty($prod['inStock']),
                                'unlimited' => !empty($prod['unlimited']),
                            ];
                            $qvStockMap[$lookup] = $info;
                            $qvStockMap[$lookupLower] = $info;
                            $qvStockMap[$pKey] = $info;
                            $qvStockMap[$pKeyLower] = $info;
                        }
                    } catch (\Throwable $e) {
                    }
                }

                $course['is_qv'] = true;
                if ($info) {
                    $course['stock_qty'] = $info['stock'];
                    $course['in_stock'] = !empty($info['inStock']);
                    $course['unlimited_stock'] = !empty($info['unlimited']);
                } else {
                    $course['stock_qty'] = null;
                    $course['in_stock'] = true;
                    $course['unlimited_stock'] = false;
                }
            } elseif (($course['type'] ?? '') === 'tool') {
                $course['is_qv'] = false;
                $cid = (int) ($course['id'] ?? 0);
                if (isset($localStocks[$cid])) {
                    $course['stock_qty'] = $localStocks[$cid];
                    $course['in_stock'] = $localStocks[$cid] > 0;
                    $course['unlimited_stock'] = false;
                }
            }
        }
        unset($course);
    }

    /**
     * Create a new course/tool
     */
    public function create(array $data): int
    {
        $this->db->query(
            "INSERT INTO courses (title, description, price, original_price, currency, type, thumbnail, video_url, telegram_group_id, download_link, is_active)
             VALUES (:title, :description, :price, :original_price, :currency, :type, :thumbnail, :video_url, :telegram_group_id, :download_link, :is_active)",
            [
                ':title'             => $data['title'],
                ':description'       => $data['description'] ?? '',
                ':price'             => $data['price'],
                ':original_price'    => $data['original_price'] ?? null,
                ':currency'          => $data['currency'] ?? 'USD',
                ':type'              => $data['type'] ?? 'course',
                ':thumbnail'         => $data['thumbnail'] ?? null,
                ':video_url'         => $data['video_url'] ?? null,
                ':telegram_group_id' => $data['telegram_group_id'] ?: null,
                ':download_link'     => $data['download_link'] ?: null,
                ':is_active'         => $data['is_active'] ?? 1,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a course/tool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowed = ['title', 'description', 'price', 'original_price', 'currency', 'type', 'thumbnail', 'video_url', 'telegram_group_id', 'download_link', 'is_active'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $val = $data[$field];
                if (($field === 'telegram_group_id' || $field === 'download_link') && $val === '') {
                    $val = null;
                }
                $params[":$field"] = $val;
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE courses SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Delete a course
     */
    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM courses WHERE id = :id", [':id' => $id]);
        return true;
    }

    /**
     * Get course count
     */
    public function getCount(): int
    {
        $row = $this->db->fetch("SELECT COUNT(*) as total FROM courses WHERE is_active = 1");
        return (int) ($row['total'] ?? 0);
    }
}
