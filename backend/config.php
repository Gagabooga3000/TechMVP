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

// Base paths
define('BASE_PATH', '/');
define('AUTH_PATH', BASE_PATH . 'auth/');
define('DASHBOARD_PATH', BASE_PATH . 'dashboard/');
define('PROJECTS_PATH', BASE_PATH . 'projects/');
define('API_PATH', BASE_PATH . 'api/');
define('PAGES_PATH', BASE_PATH . 'pages/');
define('ASSETS_PATH', BASE_PATH . 'assets/');
define('ADMIN_PATH', BASE_PATH . 'admin/');

function require_auth(): void {
    start_session_once();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . AUTH_PATH . 'login.php');
        exit;
    }
}

function require_admin(): void {
    require_auth();
    start_session_once();
    $pdo = db_get_pdo();
    $stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (empty($user['is_admin'])) {
        header('Location: ' . DASHBOARD_PATH . 'profile.php');
        exit;
    }
}

function log_activity(string $action, ?string $entityType = null, ?int $entityId = null, ?array $details = null): void {
    try {
        $pdo = db_get_pdo();
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            $ip,
            $userAgent
        ]);
    } catch (Throwable $e) {
        // Логирование не должно ломать основной функционал
        error_log('Activity log error: ' . $e->getMessage());
    }
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}


