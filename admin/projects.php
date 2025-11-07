<?php
$pdo = db_get_pdo();
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($search) {
    $where[] = '(p.name LIKE ? OR p.slug LIKE ? OR u.email LIKE ?)';
    $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
}
if ($status) {
    $where[] = 'p.status = ?';
    $params[] = $status;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalProjects = $pdo->query("SELECT COUNT(*) FROM projects p LEFT JOIN users u ON p.user_id = u.id {$whereClause}")->fetchColumn();
$totalPages = ceil($totalProjects / $perPage);

$projectsQuery = "SELECT p.*, u.email as user_email, u.name as user_name 
    FROM projects p 
    LEFT JOIN users u ON p.user_id = u.id 
    {$whereClause} 
    ORDER BY p.created_at DESC 
    LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($projectsQuery);
$stmt->execute($params);
$projects = $stmt->fetchAll();
?>

<h1>Проекты</h1>

<form method="get" style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
  <input type="hidden" name="tab" value="projects" />
  <input type="text" name="search" placeholder="Поиск..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; min-width: 200px; background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
  <select name="status" style="background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
    <option value="">Все статусы</option>
    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Черновик</option>
    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Опубликован</option>
    <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Архив</option>
  </select>
  <button type="submit" class="button button--primary">Поиск</button>
  <?php if ($search || $status): ?>
  <a href="?tab=projects" class="button button--ghost">Сбросить</a>
  <?php endif; ?>
</form>

<p style="color: var(--muted); margin-bottom: 16px;">Всего: <?= $totalProjects ?> проектов</p>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Название</th>
      <th>Slug</th>
      <th>Пользователь</th>
      <th>Просмотры</th>
      <th>Клики</th>
      <th>Заявки</th>
      <th>Статус</th>
      <th>Создан</th>
      <th>Действия</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($projects as $project): ?>
    <tr>
      <td><?= $project['id'] ?></td>
      <td><a href="<?= PROJECTS_PATH ?>edit.php?id=<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></a></td>
      <td><code style="font-size: 12px;"><?= htmlspecialchars($project['slug']) ?></code></td>
      <td><a href="?tab=users&user_id=<?= $project['user_id'] ?>"><?= htmlspecialchars($project['user_name'] ?: $project['user_email']) ?></a></td>
      <td><?= $project['analytics_views'] ?></td>
      <td><?= $project['analytics_clicks'] ?></td>
      <td><?= $project['analytics_leads'] ?></td>
      <td><span class="badge badge-<?= $project['status'] === 'published' ? 'success' : ($project['status'] === 'draft' ? 'warning' : 'danger') ?>"><?= $project['status'] === 'published' ? 'Опубликован' : ($project['status'] === 'draft' ? 'Черновик' : 'Архив') ?></span></td>
      <td><?= date('d.m.Y', strtotime($project['created_at'])) ?></td>
      <td>
        <a href="<?= PROJECTS_PATH ?>edit.php?id=<?= $project['id'] ?>" class="button button--ghost" style="padding: 6px 12px; font-size: 12px;">Открыть</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div style="display: flex; gap: 8px; justify-content: center; margin-top: 24px;">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
  <a href="?tab=projects&page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?>" class="button button--<?= $i === $page ? 'primary' : 'ghost' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

