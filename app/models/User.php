<?php

require_once BASE_PATH . 'app/core/Model.php';

class User extends Model
{
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE (username = :username OR email = :email) 
            AND is_active = 1 
            LIMIT 1
        ");
        $stmt->execute([':username' => $username, ':email' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        
        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        // Update last login timestamp
        $this->updateLastLogin($user['id']);

        return $user;
    }

    private function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): bool
    {
        if (empty($data['username']) || empty($data['password'])) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO users (full_name, username, email, password_hash, is_active)
            VALUES (:full_name, :username, :email, :password_hash, :is_active)
        ");

        return $stmt->execute([
            ':full_name' => $data['full_name'] ?? '',
            ':username' => $data['username'],
            ':email' => $data['email'] ?? null,
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':is_active' => $data['is_active'] ?? 1,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if (empty($data['username'])) {
            return false;
        }

        // If password is provided, update it; otherwise, keep the old one
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET full_name = :full_name, username = :username, email = :email, 
                    password_hash = :password_hash, is_active = :is_active
                WHERE id = :id
            ");
            return $stmt->execute([
                ':id' => $id,
                ':full_name' => $data['full_name'] ?? '',
                ':username' => $data['username'],
                ':email' => $data['email'] ?? null,
                ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':is_active' => $data['is_active'] ?? 1,
            ]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET full_name = :full_name, username = :username, email = :email, is_active = :is_active
                WHERE id = :id
            ");
            return $stmt->execute([
                ':id' => $id,
                ':full_name' => $data['full_name'] ?? '',
                ':username' => $data['username'],
                ':email' => $data['email'] ?? null,
                ':is_active' => $data['is_active'] ?? 1,
            ]);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
}
