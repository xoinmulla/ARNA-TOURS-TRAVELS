<?php

declare(strict_types=1);


/**
 * Escape HTML output.
 */
function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


/**
 * Return JSON response.
 */
function jsonResponse(
    bool $success,
    string $message,
    array $data = [],
    int $statusCode = 200
): never {

    http_response_code($statusCode);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/**
 * Store a one-time flash message for POST/Redirect/GET flows.
 */
function setFlashMessage(string $message, string $type = 'success'): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type,
    ];
}

/**
 * Retrieve and clear a one-time flash message.
 */
function getFlashMessage(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $flash = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    return is_array($flash) ? $flash : null;
}


/**
 * Generate booking number.
 */
function generateBookingNumber(): string
{
    return 'ARNA-' .
        date('Ymd') .
        '-' .
        strtoupper(
            substr(
                bin2hex(random_bytes(4)),
                0,
                6
            )
        );
}


/**
 * Clean request value.
 */
function requestValue(string $key): string
{
    return trim(
        $_POST[$key] ?? ''
    );
}

/**
 * Return the current CSRF token, creating one when needed.
 */
function csrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


/**
 * Validate a submitted CSRF token.
 */
function verifyCsrfToken(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return !empty($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}


/** Return current application date in the configured site timezone. */
function appToday(): string
{
    $tz = defined('TIMEZONE') ? TIMEZONE : 'Asia/Kolkata';
    return (new DateTime('now', new DateTimeZone($tz)))->format('Y-m-d');
}

/** Apply safe baseline response headers. */
function sendSecurityHeaders(): void
{
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
