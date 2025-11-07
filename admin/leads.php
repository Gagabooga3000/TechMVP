<?php
$pdo = db_get_pdo();
$search = $_GET['search'] ?? '';
$projectId = $_GET['project_id'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($search) {
    $where[] = '(l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)';
    $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
}
if ($projectId) {
    $where[] = 'l.project_id = ?';
    $params[] = $projectId;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalLeads = $pdo->query("SELECT COUNT(*) FROM leads l {$whereClause}")->fetchColumn();
$totalPages = ceil($totalLeads / $perPage);

$leadsQuery = "SELECT l.*, p.name as project_name, p.slug as project_slug 
    FROM leads l 
    LEFT JOIN projects p ON l.project_id = p.id 
    {$whereClause} 
    ORDER BY l.created_at DESC 
    LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($leadsQuery);
$stmt->execute($params);
$leads = $stmt->fetchAll();

// Список проектов для фильтра
$projects = $pdo->query('SELECT id, name FROM projects ORDER BY name')->fetchAll();
?>

<h1>Заявки</h1>

<form method="get" style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
  <input type="hidden" name="tab" value="leads" />
  <input type="text" name="search" placeholder="Поиск по имени, email, телефону..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; min-width: 200px; background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
  <select name="project_id" style="background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
    <option value="">Все проекты</option>
    <?php foreach ($projects as $proj): ?>
    <option value="<?= $proj['id'] ?>" <?= $projectId == $proj['id'] ? 'selected' : '' ?>><?= htmlspecialchars($proj['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="button button--primary">Поиск</button>
  <?php if ($search || $projectId): ?>
  <a href="?tab=leads" class="button button--ghost">Сбросить</a>
  <?php endif; ?>
</form>

<p style="color: var(--muted); margin-bottom: 16px;">Всего: <?= $totalLeads ?> заявок</p>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Имя</th>
      <th>Email</th>
      <th>Телефон</th>
      <th>Проект</th>
      <th>Источник</th>
      <th>Дата</th>
      <th>Действия</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($leads as $lead): ?>
    <tr>
      <td><?= $lead['id'] ?></td>
      <td><?= htmlspecialchars($lead['name'] ?: '-') ?></td>
      <td><?= htmlspecialchars($lead['email'] ?: '-') ?></td>
      <td><?= htmlspecialchars($lead['phone'] ?: '-') ?></td>
      <td><a href="<?= PROJECTS_PATH ?>edit.php?id=<?= $lead['project_id'] ?>"><?= htmlspecialchars($lead['project_name']) ?></a></td>
      <td><?= htmlspecialchars($lead['source'] ?: '-') ?></td>
      <td><?= date('d.m.Y H:i', strtotime($lead['created_at'])) ?></td>
      <td>
        <?php if ($lead['message']): ?>
        <button onclick="alert('<?= htmlspecialchars(addslashes($lead['message'])) ?>')" class="button button--ghost" style="padding: 6px 12px; font-size: 12px;">Сообщение</button>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div style="display: flex; gap: 8px; justify-content: center; margin-top: 24px;">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
  <a href="?tab=leads&page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $projectId ? '&project_id=' . urlencode($projectId) : '' ?>" class="button button--<?= $i === $page ? 'primary' : 'ghost' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

