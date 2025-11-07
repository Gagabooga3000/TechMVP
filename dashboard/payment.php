<?php
require __DIR__ . '/../backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];

// Проверяем текущую подписку
$subStmt = $pdo->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND status = "active" ORDER BY created_at DESC LIMIT 1');
$subStmt->execute([$userId]);
$currentSub = $subStmt->fetch();

$isPro = ($currentSub['plan_type'] ?? 'free') === 'pro';

if ($isPro) {
    redirect(DASHBOARD_PATH . 'profile.php?tab=subscription');
}

$months = (int)($_GET['months'] ?? 1);
$amount = 490 * $months;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Неверный CSRF токен';
    } else {
        $months = (int)($_POST['months'] ?? 1);
        $paymentMethod = $_POST['payment_method'] ?? '';
        
        // В реальном проекте здесь должна быть интеграция с платежной системой
        // Для демо просто активируем подписку
        
        if ($paymentMethod === 'demo' || $paymentMethod === 'manual') {
            // Деактивируем старую подписку
            if ($currentSub) {
                $pdo->prepare('UPDATE subscriptions SET status = "cancelled" WHERE id = ?')->execute([$currentSub['id']]);
            }
            
            // Создаем новую подписку
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$months} months"));
            $ins = $pdo->prepare('INSERT INTO subscriptions (user_id, plan_type, status, expires_at) VALUES (?, "pro", "active", ?)');
            $ins->execute([$userId, $expiresAt]);
            
            redirect(DASHBOARD_PATH . 'profile.php?tab=subscription&success=1');
        } else {
            $error = 'Выберите способ оплаты';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Оплата подписки — TechMVP</title>
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
        <h1>Оплата подписки</h1>
        
        <?php if ($error): ?>
        <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-bottom:24px;">
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <div class="card" style="padding: 24px; margin-bottom: 24px;">
          <h3>Профессиональный тариф</h3>
          <p class="section__lead">490 ₽ в месяц</p>
        </div>
        
        <form method="post" action="" style="display: flex; flex-direction: column; gap: 16px;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>" />
          
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Срок подписки</label>
            <select name="months" id="months" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" onchange="updateAmount()">
              <option value="1" <?= $months === 1 ? 'selected' : '' ?>>1 месяц — 490 ₽</option>
              <option value="3" <?= $months === 3 ? 'selected' : '' ?>>3 месяца — 1 470 ₽ (экономия 0 ₽)</option>
              <option value="6" <?= $months === 6 ? 'selected' : '' ?>>6 месяцев — 2 940 ₽</option>
              <option value="12" <?= $months === 12 ? 'selected' : '' ?>>12 месяцев — 5 880 ₽</option>
            </select>
          </div>
          
          <div class="card" style="padding: 20px; background: #10231f; border-color: #195b4f;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 18px; font-weight: 600;">К оплате:</span>
              <span id="amount" style="font-size: 24px; font-weight: 700; color: var(--primary);"><?= $amount ?> ₽</span>
            </div>
          </div>
          
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Способ оплаты</label>
            <div style="display: flex; flex-direction: column; gap: 12px;">
              <label style="display: flex; gap: 12px; padding: 16px; background: var(--card); border: 1px solid var(--stroke); border-radius: 10px; cursor: pointer;">
                <input type="radio" name="payment_method" value="demo" checked style="margin-top: 2px;" />
                <div>
                  <strong>Демо-оплата (для тестирования)</strong>
                  <p style="color: var(--muted); font-size: 12px; margin: 4px 0 0;">Подписка будет активирована без реальной оплаты</p>
                </div>
              </label>
              <label style="display: flex; gap: 12px; padding: 16px; background: var(--card); border: 1px solid var(--stroke); border-radius: 10px; cursor: pointer; opacity: 0.6;">
                <input type="radio" name="payment_method" value="yookassa" disabled style="margin-top: 2px;" />
                <div>
                  <strong>ЮKassa (в разработке)</strong>
                  <p style="color: var(--muted); font-size: 12px; margin: 4px 0 0;">Банковская карта, СБП, электронные кошельки</p>
                </div>
              </label>
            </div>
          </div>
          
          <button class="button button--primary" type="submit" style="width: 100%;">Оплатить</button>
          <a href="<?= DASHBOARD_PATH ?>profile.php" class="button button--ghost" style="width: 100%; text-align: center;">Отмена</a>
        </form>
        
        <div class="card" style="margin-top: 24px; padding: 16px; background: #1a1f2e; border-color: #2d3748;">
          <p style="color: var(--muted); font-size: 12px; margin: 0;">
            <strong>Примечание:</strong> В текущей версии доступна только демо-оплата. Для интеграции реальных платежей необходимо подключить платежную систему (ЮKassa, Stripe и т.д.).
          </p>
        </div>
      </div>
    </main>
    <script>
      function updateAmount() {
        const months = parseInt(document.getElementById('months').value);
        const amount = months * 490;
        document.getElementById('amount').textContent = amount.toLocaleString('ru-RU') + ' ₽';
      }
    </script>
  </body>
</html>

