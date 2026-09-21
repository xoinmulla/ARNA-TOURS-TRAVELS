<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Vehicle.php';
class VehicleController {
    public function all(?string $search=null, ?string $status=null): array { return (new Vehicle())->all($search,$status); }
    public function find(int $id): ?array { return (new Vehicle())->find($id); }
}
