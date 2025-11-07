<?php
require __DIR__ . '/../backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];

// Check subscription limits
$subStmt = $pdo->prepare('SELECT plan_type FROM subscriptions WHERE user_id = ? AND status = "active" ORDER BY created_at DESC LIMIT 1');
$subStmt->execute([$userId]);
$subscription = $subStmt->fetch();
$planType = $subscription['plan_type'] ?? 'free';
$isPro = $planType === 'pro';
$maxProjects = $isPro ? 10 : 1;

$projectsStmt = $pdo->prepare('SELECT COUNT(*) as count FROM projects WHERE user_id = ?');
$projectsStmt->execute([$userId]);
$projectsCount = $projectsStmt->fetch()['count'];

if ($projectsCount >= $maxProjects) {
    redirect(DASHBOARD_PATH . 'profile.php?tab=projects');
}

$errors = [];
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Неверный CSRF токен';
    }
    
    $name = trim($_POST['name'] ?? '');
    $templateType = trim($_POST['template_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name)) {
        $errors[] = 'Название проекта обязательно';
    }
    if (strlen($name) > 200) {
        $errors[] = 'Название слишком длинное';
    }
    
    if (!$errors) {
        // Generate slug
        $slug = mb_strtolower(trim(preg_replace('/[^a-zа-я0-9]+/u', '-', $name), '-'));
        $baseSlug = $slug;
        $counter = 1;
        
        // Ensure unique slug
        while (true) {
            $checkStmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ?');
            $checkStmt->execute([$slug]);
            if (!$checkStmt->fetch()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        $settings = json_encode([
            'template' => $templateType,
            'theme' => 'default'
        ]);
        
        $stmt = $pdo->prepare('INSERT INTO projects (user_id, name, slug, template_type, description, settings, status) VALUES (?, ?, ?, ?, ?, ?, "draft")');
        $stmt->execute([$userId, $name, $slug, $templateType ?: null, $description ?: null, $settings]);
        
        $projectId = $pdo->lastInsertId();
        redirect(PROJECTS_PATH . 'edit.php?id=' . $projectId);
    }
}

// Шаблоны для Free
$freeTemplates = [
    'basic' => 'Базовый лендинг'
];

// Шаблоны для Pro
$proTemplates = [
    'saas' => 'Лендинг SaaS-сервиса',
    'ai' => 'Лендинг AI-сервиса',
    'education' => 'Образовательный продукт',
    'mobile' => 'Мобильное приложение',
    'consulting' => 'Консалтинг / Агентство',
    'marketplace' => 'Simple marketplace',
    'booking' => 'Сервис бронирований',
    'b2b' => 'B2B-сервис'
];

$templates = $isPro ? array_merge($freeTemplates, $proTemplates) : $freeTemplates;
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Создать проект — TechMVP</title>
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
        <h1>Создать проект</h1>
        
        <?php if ($errors): ?>
        <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-bottom:24px;">
          <ul style="margin:8px 0 0 18px;">
            <?php foreach ($errors as $err): ?><li><?=htmlspecialchars($err)?></li><?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        
        <form method="post" action="" style="display: flex; flex-direction: column; gap: 16px;">
          <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
          
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Название проекта *</label>
            <input type="text" name="name" placeholder="Например: Мой стартап" required style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
          </div>
          
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Шаблон</label>
            <select name="template_type" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
              <option value="">Выберите шаблон</option>
              <?php foreach ($templates as $key => $label): ?>
              <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (!$isPro): ?>
            <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">В бесплатном тарифе доступен только базовый шаблон. <a href="<?= BASE_PATH ?>index.php#pricing" style="color: var(--primary);">Перейти на Pro</a></p>
            <?php endif; ?>
          </div>
          
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Описание</label>
            <textarea name="description" placeholder="Краткое описание проекта" style="width:100%;min-height:120px;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;"></textarea>
          </div>
          
          <div style="display: flex; gap: 12px;">
            <button class="button button--primary" type="submit">Создать проект</button>
            <a href="<?= DASHBOARD_PATH ?>profile.php?tab=projects" class="button button--ghost">Отмена</a>
          </div>
        </form>
      </div>
    </main>
  </body>
</html>

