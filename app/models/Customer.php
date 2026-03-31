<?php

require_once BASE_PATH . 'app/core/Model.php';

class Customer extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT c.*
            FROM customers c
            ORDER BY c.customer_name ASC
        ");

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM customers
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO customers (
                customer_name,
                contact_person,
                phone,
                email,
                address
            ) VALUES (
                :customer_name,
                :contact_person,
                :phone,
                :email,
                :address
            )
        ");

        return $stmt->execute([
            ':customer_name' => trim($data['customer_name'] ?? ''),
            ':contact_person' => !empty($data['contact_person']) ? trim($data['contact_person']) : null,
            ':phone' => !empty($data['phone']) ? trim($data['phone']) : null,
            ':email' => !empty($data['email']) ? trim($data['email']) : null,
            ':address' => !empty($data['address']) ? trim($data['address']) : null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE customers SET
                customer_name = :customer_name,
                contact_person = :contact_person,
                phone = :phone,
                email = :email,
                address = :address
            WHERE id = :id
        ");

        return $stmt->execute([
            ':customer_name' => trim($data['customer_name'] ?? ''),
            ':contact_person' => !empty($data['contact_person']) ? trim($data['contact_person']) : null,
            ':phone' => !empty($data['phone']) ? trim($data['phone']) : null,
            ':email' => !empty($data['email']) ? trim($data['email']) : null,
            ':address' => !empty($data['address']) ? trim($data['address']) : null,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM customers WHERE id = ?");
        return $stmt->execute([$id]);
    }
}