<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
try {
    $db = Database::getConnection();
    $db->query('SELECT 1')->fetchColumn();
    jsonResponse(true, 'Arna backend health check passed.', ['database' => 'ok', 'time' => date('c')]);
} catch (Throwable $e) {
    error_log('Arna health check error: ' . $e->getMessage());
    jsonResponse(false, 'Backend health check failed.', ['database' => 'error'], 500);
}
