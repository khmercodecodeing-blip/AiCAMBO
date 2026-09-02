<?php

namespace App\Models;

/**
 * User Model — handles database interactions for students
 */
class UserModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get user by email address
     */
    public function getByEmail(string $email): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = :email",
            [':email' => $email]
        );
    }

    /**
     * Create or update user upon Google sign-in
     * Returns the user's database ID
     */
    public function createOrUpdate(array $data): int
    {
        $existing = $this->getByEmail($data['email']);

        if ($existing) {
            $this->db->query(
                "UPDATE users 
                 SET name = :name, picture = :picture, google_id = :google_id 
                 WHERE email = :email",
                [
                    ':name'      => $data['name'],
                    ':picture'   => $data['picture'] ?? null,
                    ':google_id' => $data['google_id'] ?? null,
                    ':email'     => $data['email'],
                ]
            );
            return (int) $existing['id'];
        } else {
            $this->db->query(
                "INSERT INTO users (email, name, picture, google_id) 
                 VALUES (:email, :name, :picture, :google_id)",
                [
                    ':email'     => $data['email'],
                    ':name'      => $data['name'],
                    ':picture'   => $data['picture'] ?? null,
                    ':google_id' => $data['google_id'] ?? null,
                ]
            );
            return (int) $this->db->lastInsertId();
        }
    }
}
