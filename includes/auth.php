<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
sendSecurityHeaders();

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '1' : '0');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

const ADMIN_IDLE_TIMEOUT = 1800; // 30 minutes
if (!empty($_SESSION['admin_id'])) {
    $lastActivity = (int) ($_SESSION['admin_last_activity'] ?? time());
    if (time() - $lastActivity > ADMIN_IDLE_TIMEOUT) {
        $_SESSION = [];
        session_destroy();
        session_start();
    }
    $_SESSION['admin_last_activity'] = time();
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . APP_URL . '/admin/');
        exit;
    }
}

function redirectIfAdminLoggedIn(): void
{
    if (isAdminLoggedIn()) {
        header('Location: ' . APP_URL . '/admin/dashboard.php');
        exit;
    }
}

function currentAdmin(): ?array
{
    if (!isAdminLoggedIn()) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['admin_id'],
        'name' => (string) ($_SESSION['admin_name'] ?? ''),
        'email' => (string) ($_SESSION['admin_email'] ?? ''),
    ];
}

function adminCsrfToken(): string
{
    return csrfToken();
}

function verifyAdminCsrf(): bool
{
    return verifyCsrfToken($_POST['csrf_token'] ?? null);
}
