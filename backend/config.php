<?php
// Database configuration
// Please ensure this file is NOT publicly accessible in production (deny from web or move outside webroot)

$DB_HOST = 'localhost';
$DB_NAME = 'u3085178_default';
$DB_USER = 'u3085178_default';
$DB_PASS = '0Q8e9sD8lWYFrwg6';
$DB_CHARSET = 'utf8mb4';

function db_get_pdo(): PDO {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET;
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, $DB_USER, $DB_PASS, $options);
}

function start_session_once(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function generate_csrf_token(): string {
    start_session_once();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    start_session_once();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function require_auth(): void {
    start_session_once();
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}


