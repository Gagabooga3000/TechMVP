<?php
/**
 * Экспорт заявок в CSV
 * Доступ: /api/export.php?project_id=1
 */
require __DIR__ . '/../backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];
$projectId = (int)($_GET['project_id'] ?? 0);

if (!$projectId) {
    die('Укажите project_id');
}

// Проверяем, что проект принадлежит пользователю
$stmt = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ?');
$stmt->execute([$projectId, $userId]);
if (!$stmt->fetch()) {
    die('Проект не найден');
}

// Получаем заявки
$leadsStmt = $pdo->prepare('SELECT * FROM leads WHERE project_id = ? ORDER BY created_at DESC');
$leadsStmt->execute([$projectId]);
$leads = $leadsStmt->fetchAll();

// Устанавливаем заголовки для CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="leads_' . $projectId . '_' . date('Y-m-d') . '.csv"');

// Открываем поток вывода
$output = fopen('php://output', 'w');

// BOM для корректного отображения кириллицы в Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Заголовки CSV
fputcsv($output, ['ID', 'Имя', 'Email', 'Телефон', 'Сообщение', 'Источник', 'UTM Source', 'UTM Medium', 'UTM Campaign', 'Дата'], ';');

// Данные
foreach ($leads as $lead) {
    fputcsv($output, [
        $lead['id'],
        $lead['name'] ?: '',
        $lead['email'] ?: '',
        $lead['phone'] ?: '',
        $lead['message'] ?: '',
        $lead['source'] ?: '',
        $lead['utm_source'] ?: '',
        $lead['utm_medium'] ?: '',
        $lead['utm_campaign'] ?: '',
        $lead['created_at']
    ], ';');
}

fclose($output);
exit;

