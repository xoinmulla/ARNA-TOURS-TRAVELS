<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Service
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(bool $activeOnly = false): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $sql = 'SELECT * FROM services';
        if ($activeOnly) {
            $sql .= " WHERE status = 'ACTIVE'";
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        return $this->db->query($sql)->fetchAll();
    }

    private function tableExists(): bool
    {
        try {
            $this->db->query('SELECT 1 FROM services LIMIT 1');
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO services (title, short_description, icon, image, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['title'], $data['short_description'], $data['icon'], $data['image'] ?: null,
            $data['sort_order'], $data['status']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE services SET title=?, short_description=?, icon=?, image=?, sort_order=?, status=? WHERE id=?');
        return $stmt->execute([
            $data['title'], $data['short_description'], $data['icon'], $data['image'] ?: null,
            $data['sort_order'], $data['status'], $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM services WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
