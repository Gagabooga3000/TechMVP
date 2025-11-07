<?php
/**
 * Create new project page
 */
require __DIR__ . '/../backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];

// Check if user can create project
if (!user_can_create_project($userId)) {
    $userPlan = get_user_plan($userId);
    $projectCount = get_user_project_count($userId);
    $maxProjects = $userPlan === PLAN_PRO ? PLAN_PRO_MAX_PROJECTS : PLAN_FREE_MAX_PROJECTS;
    
    redirect(DASHBOARD_PATH . 'dashboard.php?error=limit&count=' . $projectCount . '&max=' . $maxProjects);
}

$errors = [];
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Неверный CSRF токен';
    }
    
    $name = trim($_POST['name'] ?? '');
    $niche = trim($_POST['niche'] ?? '');
    $targetAudience = trim($_POST['target_audience'] ?? '');
    $goal = trim($_POST['goal'] ?? '');
    $template = trim($_POST['template'] ?? 'saas');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name)) {
        $errors[] = 'Название проекта обязательно';
    }
    if (strlen($name) > 190) {
        $errors[] = 'Название слишком длинное (максимум 190 символов)';
    }
    if (strlen($niche) > 190) {
        $errors[] = 'Ниша слишком длинная';
    }
    if (strlen($targetAudience) > 190) {
        $errors[] = 'Целевая аудитория слишком длинная';
    }
    if (strlen($goal) > 190) {
        $errors[] = 'Цель слишком длинная';
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
            'template' => $template,
            'theme' => 'default'
        ]);
        
        try {
            $stmt = $pdo->prepare('INSERT INTO projects (user_id, name, niche, target_audience, goal, slug, template, description, settings, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "draft")');
            $stmt->execute([
                $userId, 
                $name, 
                $niche ?: null, 
                $targetAudience ?: null, 
                $goal ?: null, 
                $slug, 
                $template, 
                $description ?: null, 
                $settings
            ]);
            
            $projectId = $pdo->lastInsertId();
            log_activity('create_project', 'project', $projectId);
            redirect(PROJECTS_PATH . 'project_edit.php?id=' . $projectId);
        } catch (Throwable $e) {
            $errors[] = 'Ошибка при создании проекта: ' . $e->getMessage();
            error_log('Project create error: ' . $e->getMessage());
        }
    }
}

$userPlan = get_user_plan($userId);
$isPro = $userPlan === PLAN_PRO;

// Templates
$freeTemplates = [
    'basic' => 'Базовый лендинг'
];

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
    <style>
        .form-section { background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        .form-section h3 { margin: 0 0 16px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--text); }
        .form-group input, .form-group textarea, .form-group select { width: 100%; }
        .form-hint { font-size: 13px; color: var(--muted); margin-top: 4px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header__inner">
            <a href="<?= BASE_PATH ?>index.php" class="logo">TechMVP</a>
            <nav class="nav">
                <a href="<?= DASHBOARD_PATH ?>dashboard.php">Дашборд</a>
                <a href="<?= DASHBOARD_PATH ?>profile.php">Профиль</a>
                <a href="<?= AUTH_PATH ?>logout.php">Выйти</a>
            </nav>
        </div>
    </header>
    <main class="section">
        <div class="container" style="max-width:800px;">
            <h1>Создать новый проект</h1>
            <p class="section__lead">Заполните основную информацию о вашем MVP</p>
            
            <?php if (!empty($errors)): ?>
            <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-bottom:24px;">
                <ul style="margin:0;padding-left:20px;">
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>" />
                
                <div class="form-section">
                    <h3>Основная информация</h3>
                    
                    <div class="form-group">
                        <label for="name">Название проекта *</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required maxlength="190" />
                        <div class="form-hint">Как называется ваш MVP или продукт?</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="niche">Ниша</label>
                        <input type="text" id="niche" name="niche" value="<?= htmlspecialchars($_POST['niche'] ?? '') ?>" maxlength="190" placeholder="Например: EdTech, FinTech, HealthTech" />
                        <div class="form-hint">В какой нише работает ваш продукт?</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="target_audience">Целевая аудитория</label>
                        <input type="text" id="target_audience" name="target_audience" value="<?= htmlspecialchars($_POST['target_audience'] ?? '') ?>" maxlength="190" placeholder="Например: Студенты, Стартапы, Малый бизнес" />
                        <div class="form-hint">Кто ваша целевая аудитория?</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="goal">Цель проекта</label>
                        <input type="text" id="goal" name="goal" value="<?= htmlspecialchars($_POST['goal'] ?? '') ?>" maxlength="190" placeholder="Например: Проверить спрос, Собрать предзаказы, Найти инвестора" />
                        <div class="form-hint">Какую гипотезу вы проверяете?</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="template">Шаблон *</label>
                        <select id="template" name="template" required>
                            <?php foreach ($templates as $key => $label): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= ($_POST['template'] ?? 'saas') === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-hint">Выберите подходящий шаблон для вашего MVP</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Описание</label>
                        <textarea id="description" name="description" rows="4" placeholder="Краткое описание проекта..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="button button--primary">Создать проект</button>
                    <a href="<?= DASHBOARD_PATH ?>dashboard.php" class="button button--secondary">Отмена</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

