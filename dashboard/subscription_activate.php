<?php
require __DIR__ . '/../backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];

// Load user info
$userStmt = $pdo->prepare('SELECT email, name FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

if (!$user) {
    redirect(AUTH_PATH . 'logout.php');
}

// Check current subscription
$subStmt = $pdo->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND status = "active" ORDER BY created_at DESC LIMIT 1');
$subStmt->execute([$userId]);
$currentSub = $subStmt->fetch();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Неверный CSRF токен';
    } else {
        $planType = $_POST['plan_type'] ?? 'pro';
        $months = (int)($_POST['months'] ?? 1);
        
        if ($months < 1 || $months > 12) {
            $error = 'Количество месяцев должно быть от 1 до 12';
        } else {
            // Deactivate old subscription
            if ($currentSub) {
                $pdo->prepare('UPDATE subscriptions SET status = "cancelled" WHERE id = ?')->execute([$currentSub['id']]);
            }
            
            // Create new subscription
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$months} months"));
            $ins = $pdo->prepare('INSERT INTO subscriptions (user_id, plan_type, status, expires_at) VALUES (?, ?, "active", ?)');
            $ins->execute([$userId, $planType, $expiresAt]);
            
            $message = "Подписка \"{$planType}\" активирована на {$months} " . ($months === 1 ? 'месяц' : ($months < 5 ? 'месяца' : 'месяцев'));
        }
    }
}

// Reload subscription
$subStmt->execute([$userId]);
$currentSub = $subStmt->fetch();
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Активация подписки — TechMVP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>styles.css" />
  </head>
  <body>
    <header class="header">
      <div class="container header__inner">
        <a href="<?= BASE_PATH ?>index.php" class="logo">TechMVP</a>
        <nav class="nav">
          <a href="<?= DASHBOARD_PATH ?>profile.php">Профиль</a>
          <a href="<?= AUTH_PATH ?>logout.php">Выйти</a>
        </nav>
      </div>
    </header>
    <main class="section">
      <div class="container" style="max-width:720px;">
        <h1>Активация подписки</h1>
        
        <div class="card" style="margin-bottom: 24px;">
          <h3>Текущий аккаунт</h3>
          <p class="section__lead">Email: <?= htmlspecialchars($user['email']) ?></p>
          <p class="section__lead">Имя: <?= htmlspecialchars($user['name'] ?: 'Не указано') ?></p>
        </div>
        
        <?php if ($currentSub): ?>
        <div class="card" style="border-color:#195b4f;background:#10231f;color:#c7fff1;margin-bottom:24px;">
          <h3>Текущая подписка</h3>
          <p>Тариф: <strong><?= $currentSub['plan_type'] === 'pro' ? 'Профессиональный' : 'Базовый' ?></strong></p>
          <p>Статус: <strong><?= $currentSub['status'] === 'active' ? 'Активна' : 'Неактивна' ?></strong></p>
          <?php if ($currentSub['expires_at']): ?>
          <p>Действует до: <strong><?= date('d.m.Y H:i', strtotime($currentSub['expires_at'])) ?></strong></p>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
        <div class="card" style="border-color:#195b4f;background:#10231f;color:#c7fff1;margin-bottom:24px;">
          <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-bottom:24px;">
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
          <h3>Выдать подписку</h3>
          <form method="post" action="" style="display: flex; flex-direction: column; gap: 16px; margin-top: 16px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>" />
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Тариф</label>
              <select name="plan_type" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
                <option value="free">Базовый (бесплатный)</option>
                <option value="pro" selected>Профессиональный (490₽/мес)</option>
              </select>
            </div>
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Срок (месяцев)</label>
              <input type="number" name="months" value="1" min="1" max="12" required style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
            </div>
            
            <button class="button button--primary" type="submit">Активировать подписку</button>
          </form>
        </div>
        
        <div class="card" style="margin-top: 24px; background: #1a1f2e; border-color: #2d3748;">
          <h4 style="margin-top: 0;">Для администратора</h4>
          <p style="color: var(--muted); font-size: 14px; margin-bottom: 12px;">
            Чтобы выдать подписку другому пользователю через SQL, выполните:
          </p>
          <pre style="background: #0c131b; padding: 12px; border-radius: 8px; overflow-x: auto; font-size: 12px; color: var(--muted);">
INSERT INTO subscriptions (user_id, plan_type, status, expires_at)
VALUES (
  (SELECT id FROM users WHERE email = 'user@example.com'),
  'pro',
  'active',
  DATE_ADD(NOW(), INTERVAL 1 MONTH)
);</pre>
        </div>
      </div>
    </main>
  </body>
</html>

