<?php
// Track clicks for analytics
require __DIR__ . '/../backend/config.php';
start_session_once();

header('Content-Type: application/json');

$projectId = (int)($_GET['project_id'] ?? 0);
$utmSource = $_GET['utm_source'] ?? null;
$utmMedium = $_GET['utm_medium'] ?? null;
$utmCampaign = $_GET['utm_campaign'] ?? null;
if (!$projectId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid project_id']);
    exit;
}

try {
    $pdo = db_get_pdo();
    $pdo->prepare('UPDATE projects SET analytics_clicks = analytics_clicks + 1 WHERE id = ?')->execute([$projectId]);

    // Track UTM for clicks
    if ($utmSource || $utmMedium || $utmCampaign) {
        try {
            $utmStmt = $pdo->prepare('INSERT INTO project_utm_stats (project_id, utm_source, utm_medium, utm_campaign, clicks) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE clicks = clicks + 1');
            $utmStmt->execute([$projectId, $utmSource, $utmMedium, $utmCampaign]);
        } catch (Throwable $e) {
            // Table might not exist - ignore
            error_log('UTM track error: ' . $e->getMessage());
        }
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    error_log('Track error: ' . $e->getMessage());
}

