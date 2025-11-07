<?php
$pdo = db_get_pdo();
$action = $_GET['action'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($action) {
    $where[] = 'action = ?';
    $params[] = $action;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalLogs = $pdo->query("SELECT COUNT(*) FROM activity_logs {$whereClause}")->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);

$logsQuery = "SELECT l.*, u.email as user_email 
    FROM activity_logs l 
    LEFT JOIN users u ON l.user_id = u.id 
    {$whereClause} 
    ORDER BY l.created_at DESC 
    LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($logsQuery);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Уникальные действия для фильтра
$actions = $pdo->query('SELECT DISTINCT action FROM activity_logs ORDER BY action')->fetchAll(PDO::FETCH_COLUMN);
?>

<h1>Логи действий</h1>

<form method="get" style="margin-bottom: 24px; display: flex; gap: 12px;">
  <input type="hidden" name="tab" value="logs" />
  <select name="action" style="background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
    <option value="">Все действия</option>
    <?php foreach ($actions as $act): ?>
    <option value="<?= htmlspecialchars($act) ?>" <?= $action === $act ? 'selected' : '' ?>><?= htmlspecialchars($act) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="button button--primary">Фильтр</button>
  <?php if ($action): ?>
  <a href="?tab=logs" class="button button--ghost">Сбросить</a>
  <?php endif; ?>
</form>

<p style="color: var(--muted); margin-bottom: 16px;">Всего: <?= $totalLogs ?> записей</p>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Пользователь</th>
      <th>Действие</th>
      <th>Тип</th>
      <th>ID сущности</th>
      <th>IP</th>
      <th>Дата</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($logs as $log): ?>
    <tr>
      <td><?= $log['id'] ?></td>
      <td><?= $log['user_email'] ? htmlspecialchars($log['user_email']) : 'Система' ?></td>
      <td><code style="font-size: 12px;"><?= htmlspecialchars($log['action']) ?></code></td>
      <td><?= htmlspecialchars($log['entity_type'] ?: '-') ?></td>
      <td><?= $log['entity_id'] ?: '-' ?></td>
      <td><code style="font-size: 11px;"><?= htmlspecialchars($log['ip_address'] ?: '-') ?></code></td>
      <td><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div style="display: flex; gap: 8px; justify-content: center; margin-top: 24px;">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
  <a href="?tab=logs&page=<?= $i ?><?= $action ? '&action=' . urlencode($action) : '' ?>" class="button button--<?= $i === $page ? 'primary' : 'ghost' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

