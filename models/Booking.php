<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Booking
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $data): int
    {
        $sql = "
            INSERT INTO bookings
            (booking_number, customer_id, source_location, destination_location,
             trip_type, preferred_date, participants)
            VALUES (:booking_number, :customer_id, :source_location, :destination_location,
                    :trip_type, :preferred_date, :participants)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':booking_number' => $data['booking_number'],
            ':customer_id' => $data['customer_id'],
            ':source_location' => $data['source_location'],
            ':destination_location' => $data['destination_location'],
            ':trip_type' => $data['trip_type'],
            ':preferred_date' => $data['preferred_date'],
            ':participants' => $data['participants'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getDashboardStats(): array
    {
        $stats = [
            'total' => 0,
            'new' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'customers' => 0,
        ];

        $stats['total'] = (int) $this->db->query(
            'SELECT COUNT(*) FROM bookings'
        )->fetchColumn();

        $stmt = $this->db->query(
            "SELECT status, COUNT(*) AS total FROM bookings GROUP BY status"
        );
        foreach ($stmt->fetchAll() as $row) {
            $key = strtolower((string) $row['status']);
            if (array_key_exists($key, $stats)) {
                $stats[$key] = (int) $row['total'];
            }
        }

        $stats['customers'] = (int) $this->db->query(
            'SELECT COUNT(*) FROM customers'
        )->fetchColumn();

        return $stats;
    }

    public function getRecent(int $limit = 8): array
    {
        $limit = max(1, min($limit, 50));
        $stmt = $this->db->query(
            "SELECT b.*, c.full_name, c.mobile_number
             FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             ORDER BY b.created_at DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll();
    }

    public function getAll(string $status = '', string $search = '', int $limit = 100, int $offset = 0): array
    {
        $where = [];
        $params = [];

        $allowedStatuses = ['NEW', 'CONFIRMED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];
        if (in_array($status, $allowedStatuses, true)) {
            $where[] = 'b.status = :status';
            $params[':status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(b.booking_number LIKE :search OR c.full_name LIKE :search OR c.mobile_number LIKE :search OR b.source_location LIKE :search OR b.destination_location LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $sql = "SELECT b.*, c.full_name, c.mobile_number, c.email
                FROM bookings b
                INNER JOIN customers c ON c.id = b.customer_id";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);
        $sql .= ' ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, c.full_name, c.mobile_number, c.email, v.vehicle_name, v.registration_number, d.full_name AS driver_name, d.mobile_number AS driver_mobile
             FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             LEFT JOIN vehicles v ON v.id = b.vehicle_id
             LEFT JOIN drivers d ON d.id = b.driver_id
             WHERE b.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function assign(int $id, ?int $vehicleId, ?int $driverId): bool
    {
        $booking = $this->findById($id);
        if (!$booking) return false;

        if ($vehicleId !== null) {
            $stmt = $this->db->prepare("SELECT id, status FROM vehicles WHERE id = ? LIMIT 1");
            $stmt->execute([$vehicleId]);
            $vehicle = $stmt->fetch();
            if (!$vehicle || in_array($vehicle['status'], ['INACTIVE', 'MAINTENANCE'], true)) return false;
        }

        if ($driverId !== null) {
            $stmt = $this->db->prepare("SELECT id, status FROM drivers WHERE id = ? LIMIT 1");
            $stmt->execute([$driverId]);
            $driver = $stmt->fetch();
            if (!$driver || in_array($driver['status'], ['INACTIVE', 'OFF_DUTY'], true)) return false;
        }

        $stmt = $this->db->prepare('UPDATE bookings SET vehicle_id = ?, driver_id = ? WHERE id = ?');
        $stmt->execute([$vehicleId, $driverId, $id]);
        return true;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['NEW', 'CONFIRMED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT status FROM bookings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();
        if ($current === false) return false;
        if ($current === $status) return true;

        $transitions = [
            'NEW' => ['CONFIRMED', 'CANCELLED'],
            'CONFIRMED' => ['IN_PROGRESS', 'CANCELLED'],
            'IN_PROGRESS' => ['COMPLETED', 'CANCELLED'],
            'COMPLETED' => [],
            'CANCELLED' => [],
        ];
        if (!in_array($status, $transitions[$current] ?? [], true)) return false;

        $stmt = $this->db->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        return $stmt->rowCount() === 1;
    }
}
