<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Customer
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Find customer by mobile number.
     */
    public function findByMobile(string $mobileNumber): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM customers
            WHERE mobile_number = ?
            LIMIT 1
        ");

        $stmt->execute([
            $mobileNumber
        ]);

        $customer = $stmt->fetch();

        return $customer ?: null;
    }

    /**
     * Create customer.
     */
    public function create(
        string $fullName,
        string $mobileNumber
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO customers
            (
                full_name,
                mobile_number
            )
            VALUES
            (
                ?,
                ?
            )
        ");

        $stmt->execute([
            $fullName,
            $mobileNumber
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update customer name.
     */
    public function updateName(
        int $customerId,
        string $fullName
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE customers
            SET full_name = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $fullName,
            $customerId
        ]);
    }
}