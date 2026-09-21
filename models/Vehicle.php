<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Vehicle
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(?string $search = null, ?string $status = null): array
    {
        $sql = 'SELECT * FROM vehicles WHERE 1=1';
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' AND (vehicle_name LIKE :search OR vehicle_type LIKE :search OR registration_number LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if ($status !== null && $status !== '' && in_array($status, ['AVAILABLE', 'BOOKED', 'MAINTENANCE', 'INACTIVE'], true)) {
            $sql .= ' AND status = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY status = \'INACTIVE\', vehicle_name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM vehicles WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(''
            . 'INSERT INTO vehicles '
            . '(vehicle_name, vehicle_type, registration_number, seating_capacity, rate_per_km, included_km, price_per_day, image, description, status) '
            . 'VALUES (:vehicle_name, :vehicle_type, :registration_number, :seating_capacity, :rate_per_km, :included_km, :price_per_day, :image, :description, :status)'
        );

        $stmt->execute([
            ':vehicle_name' => $data['vehicle_name'],
            ':vehicle_type' => $data['vehicle_type'],
            ':registration_number' => $data['registration_number'] !== '' ? $data['registration_number'] : null,
            ':seating_capacity' => $data['seating_capacity'],
            ':rate_per_km' => $data['rate_per_km'],
            ':included_km' => $data['included_km'],
            ':price_per_day' => $data['price_per_day'],
            ':image' => $data['image'] !== '' ? $data['image'] : null,
            ':description' => $data['description'] !== '' ? $data['description'] : null,
            ':status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(''
            . 'UPDATE vehicles SET '
            . 'vehicle_name = :vehicle_name, '
            . 'vehicle_type = :vehicle_type, '
            . 'registration_number = :registration_number, '
            . 'seating_capacity = :seating_capacity, '
            . 'rate_per_km = :rate_per_km, '
            . 'included_km = :included_km, '
            . 'price_per_day = :price_per_day, '
            . 'image = :image, '
            . 'description = :description, '
            . 'status = :status '
            . 'WHERE id = :id'
        );

        return $stmt->execute([
            ':vehicle_name' => $data['vehicle_name'],
            ':vehicle_type' => $data['vehicle_type'],
            ':registration_number' => $data['registration_number'] !== '' ? $data['registration_number'] : null,
            ':seating_capacity' => $data['seating_capacity'],
            ':rate_per_km' => $data['rate_per_km'],
            ':included_km' => $data['included_km'],
            ':price_per_day' => $data['price_per_day'],
            ':image' => $data['image'] !== '' ? $data['image'] : null,
            ':description' => $data['description'] !== '' ? $data['description'] : null,
            ':status' => $data['status'],
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM vehicles WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
