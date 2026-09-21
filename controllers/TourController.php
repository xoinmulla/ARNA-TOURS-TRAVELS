<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/TourPackage.php';
class TourController {
    public function all(string $search='', string $status=''): array { return (new TourPackage())->all($search,$status); }
    public function find(int $id): ?array { return (new TourPackage())->find($id); }
}
