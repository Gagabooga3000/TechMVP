<?php
/**
 * Скрипт для проверки истечения подписок
 * Запускать через cron: php backend/check_expired_subscriptions.php
 * Рекомендуется запускать ежедневно: 0 0 * * * php /path/to/backend/check_expired_subscriptions.php
 */

require __DIR__ . '/config.php';

$pdo = db_get_pdo();

// Находим истекшие подписки
$stmt = $pdo->prepare('
    SELECT id, user_id, plan_type, expires_at 
    FROM subscriptions 
    WHERE status = "active" 
    AND expires_at IS NOT NULL 
    AND expires_at < NOW()
');

$stmt->execute();
$expired = $stmt->fetchAll();

$updated = 0;
foreach ($expired as $sub) {
    // Деактивируем подписку
    $upd = $pdo->prepare('UPDATE subscriptions SET status = "expired" WHERE id = ?');
    $upd->execute([$sub['id']]);
    $updated++;
    
    // Здесь можно добавить отправку email-уведомления
    // $userStmt = $pdo->prepare('SELECT email, name FROM users WHERE id = ?');
    // $userStmt->execute([$sub['user_id']]);
    // $user = $userStmt->fetch();
    // sendExpirationEmail($user['email'], $user['name']);
}

echo "Проверено подписок. Деактивировано: {$updated}\n";

