<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Customer.php';
class CustomerController {
    public function findByMobile(string $mobile): ?array { return (new Customer())->findByMobile($mobile); }
    public function create(string $name, string $mobile): int { return (new Customer())->create($name, $mobile); }
}
