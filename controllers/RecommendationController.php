<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Destination.php';
class RecommendationController {
    public function destinations(string $search='', string $status='ACTIVE'): array { return (new Destination())->all($search,$status); }
}
