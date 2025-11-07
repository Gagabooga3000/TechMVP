<?php
require __DIR__ . '/../backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];

// Load profile
$stmt = $pdo->prepare('SELECT email, name, university, telegram, bio, created_at FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    redirect(AUTH_PATH . 'logout.php');
}

// Get user subscription
$subStmt = $pdo->prepare('SELECT plan_type, status, expires_at FROM subscriptions WHERE user_id = ? AND status = "active" ORDER BY created_at DESC LIMIT 1');
$subStmt->execute([$userId]);
$subscription = $subStmt->fetch();
$planType = $subscription['plan_type'] ?? 'free';
$isPro = $planType === 'pro';

// Get projects count
$projectsStmt = $pdo->prepare('SELECT COUNT(*) as count FROM projects WHERE user_id = ?');
$projectsStmt->execute([$userId]);
$projectsCount = $projectsStmt->fetch()['count'];

// Get max projects limit
$maxProjects = $isPro ? 10 : 1;

// Get projects list
$projectsListStmt = $pdo->prepare('SELECT id, name, slug, status, template_type, analytics_views, analytics_clicks, analytics_leads, created_at FROM projects WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
$projectsListStmt->execute([$userId]);
$projects = $projectsListStmt->fetchAll();

// Get total leads
$leadsStmt = $pdo->prepare('SELECT COUNT(*) as count FROM leads l INNER JOIN projects p ON l.project_id = p.id WHERE p.user_id = ?');
$leadsStmt->execute([$userId]);
$totalLeads = $leadsStmt->fetch()['count'];

// Handle update
$saved = false;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Неверный CSRF токен';
    }
    
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $university = trim($_POST['university'] ?? '');
        $telegram = trim($_POST['telegram'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        if (strlen($name) > 120) $errors[] = 'Имя слишком длинное';
        if (strlen($university) > 160) $errors[] = 'Поле «Учебное заведение» слишком длинное';
        if (strlen($telegram) > 120) $errors[] = 'Телеграм слишком длинный';

        if (!$errors) {
            $upd = $pdo->prepare('UPDATE users SET name = ?, university = ?, telegram = ?, bio = ? WHERE id = ?');
            $upd->execute([$name ?: null, $university ?: null, $telegram ?: null, $bio ?: null, $userId]);
            $saved = true;
            // reload
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
    }
}

$tab = $_GET['tab'] ?? 'profile';
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Профиль — TechMVP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>styles.css" />
    <style>
      .profile-tabs { display: flex; gap: 8px; border-bottom: 1px solid var(--stroke); margin-bottom: 24px; }
      .profile-tabs a { padding: 12px 16px; color: var(--muted); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: .2s; }
      .profile-tabs a:hover { color: var(--text); }
      .profile-tabs a.active { color: var(--primary); border-bottom-color: var(--primary); }
      .tab-content { display: none; }
      .tab-content.active { display: block; }
      .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
      .stat-card { background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 20px; }
      .stat-card__value { font-size: 32px; font-weight: 700; color: var(--primary); margin: 8px 0; }
      .stat-card__label { color: var(--muted); font-size: 14px; }
      .projects-list { display: grid; gap: 12px; }
      .project-item { background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 16px; display: flex; justify-content: space-between; align-items: center; }
      .project-item__info h4 { margin: 0 0 6px; }
      .project-item__info p { margin: 0; color: var(--muted); font-size: 14px; }
      .project-item__meta { display: flex; gap: 16px; align-items: center; }
      .project-item__stats { display: flex; gap: 12px; font-size: 12px; color: var(--muted); }
      .project-item__actions { display: flex; gap: 8px; }
      .badge-status { padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; }
      .badge-status.draft { background: #1a1f2e; color: #94a3b8; }
      .badge-status.published { background: #113c35; color: #a7ffeb; }
      .badge-status.archived { background: #2a1a1a; color: #ffb4b4; }
      .plan-badge { display: inline-block; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; }
      .plan-badge.free { background: #1a1f2e; color: #94a3b8; }
      .plan-badge.pro { background: #113c35; color: #a7ffeb; border: 1px solid #195b4f; }
      .empty-state { text-align: center; padding: 48px 20px; color: var(--muted); }
      .empty-state__icon { font-size: 48px; margin-bottom: 16px; }
    </style>
  </head>
  <body>
    <header class="header">
      <div class="container header__inner">
        <a href="<?= BASE_PATH ?>index.php" class="logo">TechMVP</a>
        <nav class="nav">
          <a href="<?= BASE_PATH ?>index.php">Главная</a>
          <?php
          // Проверка на администратора
          $adminStmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
          $adminStmt->execute([$userId]);
          $isAdmin = $adminStmt->fetch()['is_admin'] ?? false;
          if ($isAdmin):
          ?>
          <a href="<?= ADMIN_PATH ?>">Админ-панель</a>
          <?php endif; ?>
          <a href="<?= AUTH_PATH ?>logout.php">Выйти</a>
        </nav>
      </div>
    </header>
    <main class="section">
      <div class="container" style="max-width:1120px;">
        <h1>Профиль</h1>
        
        <div class="profile-tabs">
          <a href="?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>">Профиль</a>
          <a href="?tab=projects" class="<?= $tab === 'projects' ? 'active' : '' ?>">Проекты (<?= $projectsCount ?>/<?= $maxProjects ?>)</a>
          <a href="?tab=analytics" class="<?= $tab === 'analytics' ? 'active' : '' ?>">Аналитика</a>
          <?php if ($isPro): ?>
          <a href="<?= DASHBOARD_PATH ?>templates.php">📋 Шаблоны</a>
          <a href="<?= DASHBOARD_PATH ?>guides.php">📚 Гайды</a>
          <?php endif; ?>
          <a href="?tab=subscription" class="<?= $tab === 'subscription' ? 'active' : '' ?>">Подписка</a>
        </div>

        <!-- Profile Tab -->
        <div class="tab-content <?= $tab === 'profile' ? 'active' : '' ?>">
          <div class="grid-2">
            <div>
              <div class="card">
                <h3>Данные</h3>
                <p class="section__lead">E-mail: <?=htmlspecialchars($user['email'])?></p>
                <p class="section__lead">С нами с: <?=htmlspecialchars($user['created_at'])?></p>
                <p class="section__lead" style="margin-top: 12px;">
                  Тариф: <span class="plan-badge <?= $planType ?>"><?= $isPro ? 'Профессиональный' : 'Базовый' ?></span>
                </p>
              </div>
        <?php if ($saved): ?>
        <div class="card" style="border-color:#195b4f;background:#10231f;color:#c7fff1;margin-top:12px;">Сохранено</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
        <div class="card" style="border-color:#195b4f;background:#10231f;color:#c7fff1;margin-top:12px;">Проект успешно удален</div>
        <?php endif; ?>
              <?php if ($errors): ?>
              <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-top:12px;">
                <ul style="margin:8px 0 0 18px;">
                  <?php foreach ($errors as $err): ?><li><?=htmlspecialchars($err)?></li><?php endforeach; ?>
                </ul>
              </div>
              <?php endif; ?>
            </div>
            <form class="form" method="post" action="?tab=profile" style="display: flex; flex-direction: column; gap: 12px;">
              <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
              <input type="hidden" name="action" value="update_profile" />
              <input type="text" name="name" placeholder="Имя" value="<?=htmlspecialchars($user['name'] ?? '')?>" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              <input type="text" name="university" placeholder="Учебное заведение" value="<?=htmlspecialchars($user['university'] ?? '')?>" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              <input type="text" name="telegram" placeholder="Telegram (@username)" value="<?=htmlspecialchars($user['telegram'] ?? '')?>" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              <textarea name="bio" placeholder="О себе" style="width:100%;min-height:140px;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;"><?=htmlspecialchars($user['bio'] ?? '')?></textarea>
              <button class="button button--primary" type="submit">Сохранить</button>
            </form>
          </div>
        </div>

        <!-- Projects Tab -->
        <div class="tab-content <?= $tab === 'projects' ? 'active' : '' ?>">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2>Мои проекты</h2>
            <?php if ($projectsCount < $maxProjects): ?>
            <a href="<?= PROJECTS_PATH ?>create.php" class="button button--primary">+ Создать проект</a>
            <?php else: ?>
            <div style="color: var(--muted); font-size: 14px;">
              Лимит проектов достигнут. <?php if (!$isPro): ?><a href="?tab=subscription" style="color: var(--primary);">Перейти на Pro</a><?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
          
          <?php if (empty($projects)): ?>
          <div class="empty-state">
            <div class="empty-state__icon">🚀</div>
            <h3>У вас пока нет проектов</h3>
            <p>Создайте свой первый лендинг для проверки гипотезы</p>
            <?php if ($projectsCount < $maxProjects): ?>
            <a href="<?= PROJECTS_PATH ?>create.php" class="button button--primary" style="margin-top: 16px;">Создать проект</a>
            <?php endif; ?>
          </div>
          <?php else: ?>
          <div class="projects-list">
            <?php foreach ($projects as $project): ?>
            <div class="project-item">
              <div class="project-item__info">
                <h4><?= htmlspecialchars($project['name']) ?></h4>
                <p>
                  <?php if ($project['template_type']): ?>
                    Шаблон: <?= htmlspecialchars($project['template_type']) ?> • 
                  <?php endif; ?>
                  Создан: <?= date('d.m.Y', strtotime($project['created_at'])) ?>
                </p>
              </div>
              <div class="project-item__meta">
                <div class="project-item__stats">
                  <span>👁️ <?= $project['analytics_views'] ?></span>
                  <span>👆 <?= $project['analytics_clicks'] ?></span>
                  <span>📧 <?= $project['analytics_leads'] ?></span>
                </div>
                <span class="badge-status <?= $project['status'] ?>"><?= $project['status'] === 'published' ? 'Опубликован' : ($project['status'] === 'draft' ? 'Черновик' : 'Архив') ?></span>
                <div class="project-item__actions">
                  <a href="<?= PROJECTS_PATH ?>edit.php?id=<?= $project['id'] ?>" class="button button--ghost" style="padding: 8px 12px; font-size: 14px;">Редактировать</a>
                  <?php if ($project['status'] === 'published'): ?>
                  <a href="<?= PROJECTS_PATH ?>view.php?slug=<?= htmlspecialchars($project['slug']) ?>" target="_blank" class="button button--primary" style="padding: 8px 12px; font-size: 14px;">Открыть</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Analytics Tab -->
        <div class="tab-content <?= $tab === 'analytics' ? 'active' : '' ?>">
          <h2>Общая статистика</h2>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-card__label">Всего проектов</div>
              <div class="stat-card__value"><?= $projectsCount ?></div>
            </div>
            <div class="stat-card">
              <div class="stat-card__label">Всего заявок</div>
              <div class="stat-card__value"><?= $totalLeads ?></div>
            </div>
            <?php
            $totalViews = array_sum(array_column($projects, 'analytics_views'));
            $totalClicks = array_sum(array_column($projects, 'analytics_clicks'));
            ?>
            <div class="stat-card">
              <div class="stat-card__label">Всего просмотров</div>
              <div class="stat-card__value"><?= $totalViews ?></div>
            </div>
            <div class="stat-card">
              <div class="stat-card__label">Всего кликов</div>
              <div class="stat-card__value"><?= $totalClicks ?></div>
            </div>
          </div>
          
          <?php if (!empty($projects)): ?>
          <h3 style="margin-top: 32px;">По проектам</h3>
          <div class="projects-list">
            <?php foreach ($projects as $project): ?>
            <div class="project-item">
              <div class="project-item__info">
                <h4><?= htmlspecialchars($project['name']) ?></h4>
                <p>Конверсия: <?= $project['analytics_views'] > 0 ? number_format(($project['analytics_leads'] / $project['analytics_views']) * 100, 1) : 0 ?>%</p>
              </div>
              <div class="project-item__meta">
                <div class="project-item__stats">
                  <span>👁️ <?= $project['analytics_views'] ?></span>
                  <span>👆 <?= $project['analytics_clicks'] ?></span>
                  <span>📧 <?= $project['analytics_leads'] ?></span>
                </div>
                <a href="<?= PROJECTS_PATH ?>edit.php?id=<?= $project['id'] ?>#analytics" class="button button--ghost" style="padding: 8px 12px; font-size: 14px;">Детали</a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Subscription Tab -->
        <div class="tab-content <?= $tab === 'subscription' ? 'active' : '' ?>">
          <h2>Подписка</h2>
          <div class="card" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <div>
                <h3 style="margin: 0 0 8px;">Текущий тариф</h3>
                <span class="plan-badge <?= $planType ?>"><?= $isPro ? 'Профессиональный' : 'Базовый' ?></span>
              </div>
              <?php if (!$isPro): ?>
              <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="<?= DASHBOARD_PATH ?>payment.php" class="button button--primary">Оплатить Pro</a>
                <a href="<?= BASE_PATH ?>index.php#pricing" class="button button--ghost">Подробнее о тарифах</a>
                <a href="<?= DASHBOARD_PATH ?>subscription_activate.php" class="button button--ghost" style="font-size: 12px;">Активировать вручную</a>
              </div>
              <?php endif; ?>
            </div>
            
            <?php if ($isPro && $subscription['expires_at']): ?>
            <p class="section__lead">Действует до: <?= date('d.m.Y', strtotime($subscription['expires_at'])) ?></p>
            <?php elseif (!$isPro): ?>
            <p class="section__lead">Бесплатный тариф: 1 проект, 14 дней</p>
            <?php endif; ?>
            
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--stroke);">
              <h4 style="margin-bottom: 16px;">Что включено:</h4>
              <ul class="features">
                <?php if ($isPro): ?>
                <li>До 10 проектов в одном аккаунте</li>
                <li>10+ готовых нишевых шаблонов</li>
                <li>Встроенная аналитика</li>
                <li>Интеграции: Telegram, Webhook</li>
                <li>Кастомные домены/поддомены</li>
                <li>Удаление логотипа TechMVP</li>
                <li>Приоритетная поддержка</li>
                <?php else: ?>
                <li>1 проект</li>
                <li>1 базовый шаблон</li>
                <li>Базовая форма заявки</li>
                <li>14 дней бесплатно</li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </main>
  </body>
</html>
