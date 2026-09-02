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
        return $this->db->fetchAll(
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
    }

    /**
     * Get all active products of a given type ('tool' or 'course')
     */
    public function getAllByType(string $type): array
    {
        return $this->db->fetchAll(
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
    }

    /**
     * Get all courses (including inactive) for admin
     */
    public function getAllAdmin(): array
    {
        return $this->db->fetchAll(
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
    }

    /**
     * Get course by ID
     */
    public function getById(int $id): ?array
    {
        return $this->db->fetch(
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
