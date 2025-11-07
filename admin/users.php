<?php
$pdo = db_get_pdo();
$userId = $_GET['user_id'] ?? null;
$action = $_POST['action'] ?? null;
$message = '';
$error = '';

// Обработка действий
if ($action && verify_csrf_token($_POST['csrf_token'] ?? null)) {
    if ($action === 'toggle_admin') {
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $isAdmin = (int)($_POST['is_admin'] ?? 0);
        $pdo->prepare('UPDATE users SET is_admin = ? WHERE id = ?')->execute([$isAdmin, $targetUserId]);
        log_activity('toggle_admin', 'user', $targetUserId, ['is_admin' => $isAdmin]);
        $message = 'Статус администратора обновлен';
    } elseif ($action === 'delete_user') {
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        if ($targetUserId !== $_SESSION['user_id']) {
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$targetUserId]);
            log_activity('delete_user', 'user', $targetUserId);
            $message = 'Пользователь удален';
        } else {
            $error = 'Нельзя удалить самого себя';
        }
    } elseif ($action === 'grant_subscription') {
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $planType = $_POST['plan_type'] ?? 'pro';
        $months = (int)($_POST['months'] ?? 1);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$months} months"));
        
        // Деактивируем старую подписку
        $pdo->prepare('UPDATE subscriptions SET status = "cancelled" WHERE user_id = ? AND status = "active"')->execute([$targetUserId]);
        
        // Создаем новую
        $pdo->prepare('INSERT INTO subscriptions (user_id, plan_type, status, expires_at) VALUES (?, ?, "active", ?)')->execute([$targetUserId, $planType, $expiresAt]);
        log_activity('grant_subscription', 'user', $targetUserId, ['plan_type' => $planType, 'months' => $months]);
        $message = 'Подписка выдана';
    }
}

// Получить пользователя для детального просмотра
$user = null;
if ($userId) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Подписки пользователя
        $userSubs = $pdo->prepare('SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC');
        $userSubs->execute([$userId]);
        $user['subscriptions'] = $userSubs->fetchAll();
        
        // Проекты пользователя
        $userProjects = $pdo->prepare('SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC');
        $userProjects->execute([$userId]);
        $user['projects'] = $userProjects->fetchAll();
    }
}

// Список всех пользователей
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($search) {
    $where = 'WHERE email LIKE ? OR name LIKE ?';
    $params = ["%{$search}%", "%{$search}%"];
}

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users {$where}")->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

$usersQuery = "SELECT u.*, 
    (SELECT COUNT(*) FROM projects WHERE user_id = u.id) as projects_count,
    (SELECT COUNT(*) FROM subscriptions WHERE user_id = u.id AND status = 'active') as active_subscriptions
    FROM users u {$where} ORDER BY u.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($usersQuery);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<?php if ($message): ?>
<div class="card" style="border-color:#195b4f;background:#10231f;color:#c7fff1;margin-bottom:16px;"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($user): ?>
<!-- Детальный просмотр пользователя -->
<div style="margin-bottom: 24px;">
  <a href="?tab=users" class="button button--ghost">← Назад к списку</a>
</div>

<h1>Пользователь: <?= htmlspecialchars($user['email']) ?></h1>

<div style="display: grid; gap: 24px; margin-bottom: 32px;">
  <div class="card" style="padding: 24px;">
    <h3>Информация</h3>
    <div style="display: grid; gap: 12px; margin-top: 16px;">
      <div><strong>ID:</strong> <?= $user['id'] ?></div>
      <div><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
      <div><strong>Имя:</strong> <?= htmlspecialchars($user['name'] ?: '-') ?></div>
      <div><strong>Университет:</strong> <?= htmlspecialchars($user['university'] ?: '-') ?></div>
      <div><strong>Telegram:</strong> <?= htmlspecialchars($user['telegram'] ?: '-') ?></div>
      <div><strong>Администратор:</strong> <?= $user['is_admin'] ? '✅ Да' : '❌ Нет' ?></div>
      <div><strong>Дата регистрации:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></div>
    </div>
    
    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--stroke);">
      <h4>Действия</h4>
      <form method="post" style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 12px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>" />
        <input type="hidden" name="action" value="toggle_admin" />
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>" />
        <input type="hidden" name="is_admin" value="<?= $user['is_admin'] ? 0 : 1 ?>" />
        <button type="submit" class="button button--<?= $user['is_admin'] ? 'secondary' : 'primary' ?>">
          <?= $user['is_admin'] ? 'Убрать права администратора' : 'Сделать администратором' ?>
        </button>
      </form>
      
      <form method="post" style="display: inline-block; margin-top: 12px;" onsubmit="return confirm('Вы уверены? Это действие нельзя отменить.');">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>" />
        <input type="hidden" name="action" value="delete_user" />
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>" />
        <button type="submit" class="button button--ghost" style="color: #ff6b6b;">Удалить пользователя</button>
      </form>
    </div>
  </div>
  
  <div class="card" style="padding: 24px;">
    <h3>Выдать подписку</h3>
    <form method="post" style="display: grid; gap: 12px; margin-top: 16px;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>" />
      <input type="hidden" name="action" value="grant_subscription" />
      <input type="hidden" name="user_id" value="<?= $user['id'] ?>" />
      <div>
        <label style="display: block; margin-bottom: 8px;">Тариф</label>
        <select name="plan_type" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
          <option value="free">Базовый</option>
          <option value="pro" selected>Профессиональный</option>
        </select>
      </div>
      <div>
        <label style="display: block; margin-bottom: 8px;">Срок (месяцев)</label>
        <input type="number" name="months" value="1" min="1" max="12" required style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
      </div>
      <button type="submit" class="button button--primary">Выдать подписку</button>
    </form>
  </div>
  
  <div class="card" style="padding: 24px;">
    <h3>Подписки (<?= count($user['subscriptions']) ?>)</h3>
    <?php if (empty($user['subscriptions'])): ?>
    <p style="color: var(--muted);">Нет подписок</p>
    <?php else: ?>
    <table class="table" style="margin-top: 16px;">
      <thead>
        <tr>
          <th>Тариф</th>
          <th>Статус</th>
          <th>Истекает</th>
          <th>Создана</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($user['subscriptions'] as $sub): ?>
        <tr>
          <td><?= $sub['plan_type'] === 'pro' ? 'Профессиональный' : 'Базовый' ?></td>
          <td><span class="badge badge-<?= $sub['status'] === 'active' ? 'success' : 'danger' ?>"><?= $sub['status'] === 'active' ? 'Активна' : ($sub['status'] === 'expired' ? 'Истекла' : 'Отменена') ?></span></td>
          <td><?= $sub['expires_at'] ? date('d.m.Y', strtotime($sub['expires_at'])) : '-' ?></td>
          <td><?= date('d.m.Y H:i', strtotime($sub['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  
  <div class="card" style="padding: 24px;">
    <h3>Проекты (<?= count($user['projects']) ?>)</h3>
    <?php if (empty($user['projects'])): ?>
    <p style="color: var(--muted);">Нет проектов</p>
    <?php else: ?>
    <table class="table" style="margin-top: 16px;">
      <thead>
        <tr>
          <th>Название</th>
          <th>Статус</th>
          <th>Создан</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($user['projects'] as $project): ?>
        <tr>
          <td><a href="<?= PROJECTS_PATH ?>edit.php?id=<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></a></td>
          <td><span class="badge badge-<?= $project['status'] === 'published' ? 'success' : 'warning' ?>"><?= $project['status'] === 'published' ? 'Опубликован' : 'Черновик' ?></span></td>
          <td><?= date('d.m.Y', strtotime($project['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>
<!-- Список пользователей -->
<h1>Пользователи</h1>

<form method="get" style="margin-bottom: 24px; display: flex; gap: 12px;">
  <input type="hidden" name="tab" value="users" />
  <input type="text" name="search" placeholder="Поиск по email или имени..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
  <button type="submit" class="button button--primary">Поиск</button>
  <?php if ($search): ?>
  <a href="?tab=users" class="button button--ghost">Сбросить</a>
  <?php endif; ?>
</form>

<p style="color: var(--muted); margin-bottom: 16px;">Всего: <?= $totalUsers ?> пользователей</p>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Email</th>
      <th>Имя</th>
      <th>Проектов</th>
      <th>Подписок</th>
      <th>Админ</th>
      <th>Дата регистрации</th>
      <th>Действия</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><a href="?tab=users&user_id=<?= $u['id'] ?>"><?= htmlspecialchars($u['email']) ?></a></td>
      <td><?= htmlspecialchars($u['name'] ?: '-') ?></td>
      <td><?= $u['projects_count'] ?></td>
      <td><?= $u['active_subscriptions'] ?></td>
      <td><?= $u['is_admin'] ? '✅' : '❌' ?></td>
      <td><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
      <td><a href="?tab=users&user_id=<?= $u['id'] ?>" class="button button--ghost" style="padding: 6px 12px; font-size: 12px;">Открыть</a></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div style="display: flex; gap: 8px; justify-content: center; margin-top: 24px;">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
  <a href="?tab=users&page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="button button--<?= $i === $page ? 'primary' : 'ghost' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

