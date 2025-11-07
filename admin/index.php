<?php
require __DIR__ . '/../backend/config.php';
require_admin();
start_session_once();

$pdo = db_get_pdo();

// Статистика
$stats = [
    'total_users' => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'active_users' => $pdo->query('SELECT COUNT(DISTINCT user_id) FROM subscriptions WHERE status = "active"')->fetchColumn(),
    'total_projects' => $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
    'published_projects' => $pdo->query('SELECT COUNT(*) FROM projects WHERE status = "published"')->fetchColumn(),
    'total_leads' => $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn(),
    'pro_subscriptions' => $pdo->query('SELECT COUNT(*) FROM subscriptions WHERE plan_type = "pro" AND status = "active"')->fetchColumn(),
];

// Последние пользователи
$recentUsers = $pdo->query('SELECT id, email, name, created_at FROM users ORDER BY created_at DESC LIMIT 10')->fetchAll();

// Последние проекты
$recentProjects = $pdo->query('SELECT p.id, p.name, p.status, p.created_at, u.email, u.name as user_name FROM projects p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 10')->fetchAll();

// Последние заявки
$recentLeads = $pdo->query('SELECT l.*, p.name as project_name FROM leads l LEFT JOIN projects p ON l.project_id = p.id ORDER BY l.created_at DESC LIMIT 10')->fetchAll();

$tab = $_GET['tab'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Админ-панель — TechMVP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>styles.css" />
    <style>
      .admin-layout { display: flex; min-height: 100vh; }
      .admin-sidebar { width: 250px; background: var(--card); border-right: 1px solid var(--stroke); padding: 24px; }
      .admin-content { flex: 1; padding: 24px; }
      .admin-nav { display: flex; flex-direction: column; gap: 8px; }
      .admin-nav a { padding: 12px 16px; color: var(--muted); text-decoration: none; border-radius: 8px; transition: .2s; }
      .admin-nav a:hover { background: var(--bg); color: var(--text); }
      .admin-nav a.active { background: var(--primary); color: #000; }
      .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
      .stat-card { background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 20px; }
      .stat-card__value { font-size: 32px; font-weight: 700; color: var(--primary); margin: 8px 0; }
      .stat-card__label { color: var(--muted); font-size: 14px; }
      .table { width: 100%; border-collapse: collapse; background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; overflow: hidden; }
      .table th, .table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--stroke); }
      .table th { background: var(--bg); font-weight: 600; color: var(--text); }
      .table tr:last-child td { border-bottom: 0; }
      .table tr:hover { background: var(--bg); }
      .badge { padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-block; }
      .badge-success { background: #10231f; color: #00d3a7; }
      .badge-warning { background: #2d1f0f; color: #ffa500; }
      .badge-danger { background: #201316; color: #ff6b6b; }
    </style>
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
    
    <div class="admin-layout">
      <aside class="admin-sidebar">
        <h2 style="margin: 0 0 24px;">Админ-панель</h2>
        <nav class="admin-nav">
          <a href="?tab=dashboard" class="<?= $tab === 'dashboard' ? 'active' : '' ?>">📊 Дашборд</a>
          <a href="?tab=users" class="<?= $tab === 'users' ? 'active' : '' ?>">👥 Пользователи</a>
          <a href="?tab=projects" class="<?= $tab === 'projects' ? 'active' : '' ?>">🚀 Проекты</a>
          <a href="?tab=subscriptions" class="<?= $tab === 'subscriptions' ? 'active' : '' ?>">💳 Подписки</a>
          <a href="?tab=leads" class="<?= $tab === 'leads' ? 'active' : '' ?>">📧 Заявки</a>
          <a href="?tab=logs" class="<?= $tab === 'logs' ? 'active' : '' ?>">📝 Логи</a>
        </nav>
      </aside>
      
      <main class="admin-content">
        <?php if ($tab === 'dashboard'): ?>
        <h1>Дашборд</h1>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-card__label">Всего пользователей</div>
            <div class="stat-card__value"><?= $stats['total_users'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-card__label">Активных пользователей</div>
            <div class="stat-card__value"><?= $stats['active_users'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-card__label">Всего проектов</div>
            <div class="stat-card__value"><?= $stats['total_projects'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-card__label">Опубликовано</div>
            <div class="stat-card__value"><?= $stats['published_projects'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-card__label">Всего заявок</div>
            <div class="stat-card__value"><?= $stats['total_leads'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-card__label">Pro подписок</div>
            <div class="stat-card__value"><?= $stats['pro_subscriptions'] ?></div>
          </div>
        </div>
        
        <h2>Последние пользователи</h2>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Имя</th>
              <th>Дата регистрации</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentUsers as $user): ?>
            <tr>
              <td><?= $user['id'] ?></td>
              <td><a href="?tab=users&user_id=<?= $user['id'] ?>"><?= htmlspecialchars($user['email']) ?></a></td>
              <td><?= htmlspecialchars($user['name'] ?: '-') ?></td>
              <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        
        <h2 style="margin-top: 32px;">Последние проекты</h2>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Название</th>
              <th>Пользователь</th>
              <th>Статус</th>
              <th>Дата создания</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentProjects as $project): ?>
            <tr>
              <td><?= $project['id'] ?></td>
              <td><a href="<?= PROJECTS_PATH ?>edit.php?id=<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></a></td>
              <td><?= htmlspecialchars($project['user_name'] ?: $project['email']) ?></td>
              <td><span class="badge badge-<?= $project['status'] === 'published' ? 'success' : ($project['status'] === 'draft' ? 'warning' : 'danger') ?>"><?= $project['status'] === 'published' ? 'Опубликован' : ($project['status'] === 'draft' ? 'Черновик' : 'Архив') ?></span></td>
              <td><?= date('d.m.Y H:i', strtotime($project['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        
        <?php elseif ($tab === 'users'): ?>
        <?php require __DIR__ . '/users.php'; ?>
        <?php elseif ($tab === 'projects'): ?>
        <?php require __DIR__ . '/projects.php'; ?>
        <?php elseif ($tab === 'subscriptions'): ?>
        <?php require __DIR__ . '/subscriptions.php'; ?>
        <?php elseif ($tab === 'leads'): ?>
        <?php require __DIR__ . '/leads.php'; ?>
        <?php elseif ($tab === 'logs'): ?>
        <?php require __DIR__ . '/logs.php'; ?>
        <?php endif; ?>
      </main>
    </div>
  </body>
</html>

