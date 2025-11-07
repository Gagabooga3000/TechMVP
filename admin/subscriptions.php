<?php
$pdo = db_get_pdo();
$planType = $_GET['plan_type'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($planType) {
    $where[] = 's.plan_type = ?';
    $params[] = $planType;
}
if ($status) {
    $where[] = 's.status = ?';
    $params[] = $status;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalSubs = $pdo->query("SELECT COUNT(*) FROM subscriptions s {$whereClause}")->fetchColumn();
$totalPages = ceil($totalSubs / $perPage);

$subsQuery = "SELECT s.*, u.email, u.name as user_name 
    FROM subscriptions s 
    LEFT JOIN users u ON s.user_id = u.id 
    {$whereClause} 
    ORDER BY s.created_at DESC 
    LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($subsQuery);
$stmt->execute($params);
$subscriptions = $stmt->fetchAll();
?>

<h1>Подписки</h1>

<form method="get" style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
  <input type="hidden" name="tab" value="subscriptions" />
  <select name="plan_type" style="background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
    <option value="">Все тарифы</option>
    <option value="free" <?= $planType === 'free' ? 'selected' : '' ?>>Базовый</option>
    <option value="pro" <?= $planType === 'pro' ? 'selected' : '' ?>>Профессиональный</option>
  </select>
  <select name="status" style="background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
    <option value="">Все статусы</option>
    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Активные</option>
    <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>>Истекшие</option>
    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Отмененные</option>
  </select>
  <button type="submit" class="button button--primary">Фильтр</button>
  <?php if ($planType || $status): ?>
  <a href="?tab=subscriptions" class="button button--ghost">Сбросить</a>
  <?php endif; ?>
</form>

<p style="color: var(--muted); margin-bottom: 16px;">Всего: <?= $totalSubs ?> подписок</p>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Пользователь</th>
      <th>Тариф</th>
      <th>Статус</th>
      <th>Начало</th>
      <th>Истекает</th>
      <th>Создана</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($subscriptions as $sub): ?>
    <tr>
      <td><?= $sub['id'] ?></td>
      <td><a href="?tab=users&user_id=<?= $sub['user_id'] ?>"><?= htmlspecialchars($sub['user_name'] ?: $sub['email']) ?></a></td>
      <td><?= $sub['plan_type'] === 'pro' ? 'Профессиональный' : 'Базовый' ?></td>
      <td><span class="badge badge-<?= $sub['status'] === 'active' ? 'success' : ($sub['status'] === 'expired' ? 'warning' : 'danger') ?>"><?= $sub['status'] === 'active' ? 'Активна' : ($sub['status'] === 'expired' ? 'Истекла' : 'Отменена') ?></span></td>
      <td><?= date('d.m.Y', strtotime($sub['started_at'])) ?></td>
      <td><?= $sub['expires_at'] ? date('d.m.Y', strtotime($sub['expires_at'])) : '-' ?></td>
      <td><?= date('d.m.Y', strtotime($sub['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div style="display: flex; gap: 8px; justify-content: center; margin-top: 24px;">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
  <a href="?tab=subscriptions&page=<?= $i ?><?= $planType ? '&plan_type=' . urlencode($planType) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?>" class="button button--<?= $i === $page ? 'primary' : 'ghost' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

