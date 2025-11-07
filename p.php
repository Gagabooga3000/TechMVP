<?php
/**
 * Public project view router
 * Routes /p/{slug} to projects/view.php
 */
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    // Try to get from URL path
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Match /p/{slug} pattern
    if (preg_match('#^/p/([^/]+)/?$#', $path, $matches)) {
        $slug = $matches[1];
    }
}

if ($slug) {
    $_GET['slug'] = $slug;
    require __DIR__ . '/projects/view.php';
} else {
    http_response_code(404);
    die('Проект не найден');
}

