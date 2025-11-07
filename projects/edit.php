<?php
require __DIR__ . '/../backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];

$projectId = (int)($_GET['id'] ?? 0);
if (!$projectId) {
    redirect(DASHBOARD_PATH . 'profile.php?tab=projects');
}

// Load project
$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ? AND user_id = ?');
$stmt->execute([$projectId, $userId]);
$project = $stmt->fetch();

if (!$project) {
    redirect(DASHBOARD_PATH . 'profile.php?tab=projects');
}

// Load integrations
$intStmt = $pdo->prepare('SELECT * FROM project_integrations WHERE project_id = ?');
$intStmt->execute([$projectId]);
$integrations = $intStmt->fetchAll();
$intByType = [];
foreach ($integrations as $int) {
    $intByType[$int['integration_type']] = $int;
}

$errors = [];
$saved = isset($_GET['saved']) && $_GET['saved'] == '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Неверный CSRF токен';
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $name = trim($_POST['name'] ?? '');
        $niche = trim($_POST['niche'] ?? '');
        $targetAudience = trim($_POST['target_audience'] ?? '');
        $goal = trim($_POST['goal'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $subdomain = trim($_POST['subdomain'] ?? '');
        $domain = trim($_POST['domain'] ?? '');
        $seoTitle = trim($_POST['seo_title'] ?? '');
        $seoDescription = trim($_POST['seo_description'] ?? '');
        $theme = trim($_POST['theme'] ?? 'default');
        $faviconUrl = trim($_POST['favicon_url'] ?? '');
        $customCss = trim($_POST['custom_css'] ?? '');
        $customJs = trim($_POST['custom_js'] ?? '');
        $hideBranding = isset($_POST['hide_branding']) ? 1 : 0;
        
        // Form settings
        $formEnabled = isset($_POST['form_enabled']) ? 1 : 0;
        $formTitle = trim($_POST['form_title'] ?? '');
        $formButtonText = trim($_POST['form_button_text'] ?? 'Отправить заявку');
        
        // Form fields settings
        $formFields = [
            'name' => [
                'enabled' => isset($_POST['form_field_name_enabled']),
                'required' => isset($_POST['form_field_name_required']),
                'placeholder' => trim($_POST['form_field_name_placeholder'] ?? 'Ваше имя')
            ],
            'email' => [
                'enabled' => isset($_POST['form_field_email_enabled']),
                'required' => isset($_POST['form_field_email_required']),
                'placeholder' => trim($_POST['form_field_email_placeholder'] ?? 'Email')
            ],
            'phone' => [
                'enabled' => isset($_POST['form_field_phone_enabled']),
                'required' => isset($_POST['form_field_phone_required']),
                'placeholder' => trim($_POST['form_field_phone_placeholder'] ?? 'Телефон')
            ],
            'message' => [
                'enabled' => isset($_POST['form_field_message_enabled']),
                'required' => isset($_POST['form_field_message_required']),
                'placeholder' => trim($_POST['form_field_message_placeholder'] ?? 'Сообщение')
            ]
        ];
        
        if (empty($name)) {
            $errors[] = 'Название проекта обязательно';
        }
        
        // Валидация поддомена
        if ($subdomain && !preg_match('/^[a-z0-9-]+$/', $subdomain)) {
            $errors[] = 'Поддомен может содержать только латинские буквы, цифры и дефисы';
        }
        
        if (!$errors) {
            try {
                // Check if form settings columns exist
                $upd = $pdo->prepare('UPDATE projects SET name = ?, niche = ?, target_audience = ?, goal = ?, description = ?, status = ?, subdomain = ?, domain = ?, seo_title = ?, seo_description = ?, theme = ?, favicon_url = ?, custom_css = ?, custom_js = ?, hide_branding = ?, form_enabled = ?, form_title = ?, form_button_text = ?, form_fields = ? WHERE id = ? AND user_id = ?');
                $upd->execute([
                    $name,
                    $niche ?: null,
                    $targetAudience ?: null,
                    $goal ?: null,
                    $description ?: null, 
                    $status, 
                    $subdomain ?: null,
                    $domain ?: null,
                    $seoTitle ?: null,
                    $seoDescription ?: null,
                    $theme,
                    $faviconUrl ?: null,
                    $customCss ?: null,
                    $customJs ?: null,
                    $hideBranding,
                    $formEnabled,
                    $formTitle ?: null,
                    $formButtonText,
                    json_encode($formFields, JSON_UNESCAPED_UNICODE),
                    $projectId, 
                    $userId
                ]);
                log_activity('update_project', 'project', $projectId);
                $saved = true;
                // Reload project data
                $stmt->execute([$projectId, $userId]);
                $project = $stmt->fetch();
                // Redirect to show success message
                redirect(PROJECTS_PATH . 'edit.php?id=' . $projectId . '&saved=1');
            } catch (Throwable $e) {
                // Check which columns are missing and try to add them
                $errorMsg = $e->getMessage();
                $missingFields = [];
                
                if (strpos($errorMsg, 'seo_title') !== false) $missingFields[] = 'seo_title';
                if (strpos($errorMsg, 'seo_description') !== false) $missingFields[] = 'seo_description';
                if (strpos($errorMsg, 'theme') !== false) $missingFields[] = 'theme';
                if (strpos($errorMsg, 'favicon_url') !== false) $missingFields[] = 'favicon_url';
                if (strpos($errorMsg, 'custom_css') !== false) $missingFields[] = 'custom_css';
                if (strpos($errorMsg, 'custom_js') !== false) $missingFields[] = 'custom_js';
                if (strpos($errorMsg, 'hide_branding') !== false) $missingFields[] = 'hide_branding';
                if (strpos($errorMsg, 'form_enabled') !== false) $missingFields[] = 'form_enabled';
                if (strpos($errorMsg, 'form_title') !== false) $missingFields[] = 'form_title';
                if (strpos($errorMsg, 'form_button_text') !== false) $missingFields[] = 'form_button_text';
                if (strpos($errorMsg, 'form_fields') !== false) $missingFields[] = 'form_fields';
                
                if (!empty($missingFields)) {
                    // Try to add missing columns
                    try {
                        $alterSql = [];
                        if (in_array('seo_title', $missingFields)) {
                            $alterSql[] = "ADD COLUMN seo_title VARCHAR(255) NULL AFTER domain";
                        }
                        if (in_array('seo_description', $missingFields)) {
                            $alterSql[] = "ADD COLUMN seo_description TEXT NULL AFTER seo_title";
                        }
                        if (in_array('theme', $missingFields)) {
                            $alterSql[] = "ADD COLUMN theme VARCHAR(50) NOT NULL DEFAULT 'default' AFTER seo_description";
                        }
                        if (in_array('favicon_url', $missingFields)) {
                            $alterSql[] = "ADD COLUMN favicon_url VARCHAR(255) NULL AFTER theme";
                        }
                        if (in_array('custom_css', $missingFields)) {
                            $alterSql[] = "ADD COLUMN custom_css TEXT NULL AFTER favicon_url";
                        }
                        if (in_array('custom_js', $missingFields)) {
                            $alterSql[] = "ADD COLUMN custom_js TEXT NULL AFTER custom_css";
                        }
                        if (in_array('hide_branding', $missingFields)) {
                            $alterSql[] = "ADD COLUMN hide_branding BOOLEAN NOT NULL DEFAULT FALSE AFTER custom_js";
                        }
                        if (in_array('form_enabled', $missingFields)) {
                            $alterSql[] = "ADD COLUMN form_enabled BOOLEAN NOT NULL DEFAULT TRUE AFTER hide_branding";
                        }
                        if (in_array('form_title', $missingFields)) {
                            $alterSql[] = "ADD COLUMN form_title VARCHAR(200) NULL AFTER form_enabled";
                        }
                        if (in_array('form_button_text', $missingFields)) {
                            $alterSql[] = "ADD COLUMN form_button_text VARCHAR(100) NOT NULL DEFAULT 'Отправить заявку' AFTER form_title";
                        }
                        if (in_array('form_fields', $missingFields)) {
                            $alterSql[] = "ADD COLUMN form_fields JSON NULL AFTER form_button_text";
                        }
                        
                        if (!empty($alterSql)) {
                            $pdo->exec("ALTER TABLE projects " . implode(", ", $alterSql));
                            // Retry the update (try with form settings if columns exist)
                            try {
                                $upd = $pdo->prepare('UPDATE projects SET name = ?, description = ?, status = ?, subdomain = ?, domain = ?, seo_title = ?, seo_description = ?, theme = ?, favicon_url = ?, custom_css = ?, custom_js = ?, hide_branding = ?, form_enabled = ?, form_title = ?, form_button_text = ?, form_fields = ? WHERE id = ? AND user_id = ?');
                                $upd->execute([
                                    $name, 
                                    $description ?: null, 
                                    $status, 
                                    $subdomain ?: null,
                                    $domain ?: null,
                                    $seoTitle ?: null,
                                    $seoDescription ?: null,
                                    $theme,
                                    $faviconUrl ?: null,
                                    $customCss ?: null,
                                    $customJs ?: null,
                                    $hideBranding,
                                    $formEnabled,
                                    $formTitle ?: null,
                                    $formButtonText,
                                    json_encode($formFields, JSON_UNESCAPED_UNICODE),
                                    $projectId, 
                                    $userId
                                ]);
                            } catch (Throwable $e3) {
                                // If form settings columns don't exist, save without them
                                $upd = $pdo->prepare('UPDATE projects SET name = ?, description = ?, status = ?, subdomain = ?, domain = ?, seo_title = ?, seo_description = ?, theme = ?, favicon_url = ?, custom_css = ?, custom_js = ?, hide_branding = ? WHERE id = ? AND user_id = ?');
                                $upd->execute([
                                    $name, 
                                    $description ?: null, 
                                    $status, 
                                    $subdomain ?: null,
                                    $domain ?: null,
                                    $seoTitle ?: null,
                                    $seoDescription ?: null,
                                    $theme,
                                    $faviconUrl ?: null,
                                    $customCss ?: null,
                                    $customJs ?: null,
                                    $hideBranding,
                                    $projectId, 
                                    $userId
                                ]);
                            }
                            log_activity('update_project', 'project', $projectId);
                            $saved = true;
                            $stmt->execute([$projectId, $userId]);
                            $project = $stmt->fetch();
                            redirect(PROJECTS_PATH . 'edit.php?id=' . $projectId . '&saved=1');
                        }
                    } catch (Throwable $e2) {
                        $errors[] = 'Ошибка при сохранении. Выполните миграцию: backend/pro_features_migration.sql';
                        error_log('Project update error: ' . $e2->getMessage());
                    }
                } else {
                    $errors[] = 'Ошибка при сохранении: ' . $e->getMessage();
                    error_log('Project update error: ' . $e->getMessage());
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_project') {
        // Delete project with confirmation
        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Неверный CSRF токен';
        } else {
            $confirmName = trim($_POST['confirm_name'] ?? '');
            if ($confirmName !== $project['name']) {
                $errors[] = 'Название проекта не совпадает. Удаление отменено.';
            } else {
                try {
                    // Check if project exists and belongs to user
                    $checkStmt = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ?');
                    $checkStmt->execute([$projectId, $userId]);
                    if (!$checkStmt->fetch()) {
                        $errors[] = 'Проект не найден';
                    } else {
                        // Delete project
                        // MySQL will automatically delete related data due to FOREIGN KEY ... ON DELETE CASCADE:
                        // - project_integrations
                        // - leads
                        // - project_analytics
                        // - project_utm_stats
                        $del = $pdo->prepare('DELETE FROM projects WHERE id = ? AND user_id = ?');
                        $del->execute([$projectId, $userId]);
                        
                        // Log activity before redirect (project will be deleted)
                        try {
                            log_activity('delete_project', 'project', $projectId);
                        } catch (Throwable $logError) {
                            // Ignore log errors
                            error_log('Activity log error: ' . $logError->getMessage());
                        }
                        
                        redirect(DASHBOARD_PATH . 'profile.php?tab=projects&deleted=1');
                    }
                } catch (Throwable $e) {
                    // Check if error is due to foreign key constraint
                    $errorMsg = $e->getMessage();
                    if (strpos($errorMsg, 'foreign key constraint') !== false || strpos($errorMsg, '1451') !== false) {
                        $errors[] = 'Не удалось удалить проект: есть связанные данные. Проверьте настройки внешних ключей в БД.';
                        error_log('Foreign key constraint error: ' . $errorMsg);
                    } else {
                        $errors[] = 'Ошибка при удалении проекта: ' . $e->getMessage();
                        error_log('Project delete error: ' . $errorMsg);
                    }
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'save_integration') {
        $intType = $_POST['integration_type'] ?? '';
        $config = [];
        
        if ($intType === 'telegram') {
            $config = ['bot_token' => trim($_POST['telegram_bot_token'] ?? ''), 'chat_id' => trim($_POST['telegram_chat_id'] ?? '')];
        } elseif ($intType === 'email') {
            $config = ['email' => trim($_POST['email_address'] ?? '')];
        } elseif ($intType === 'webhook') {
            $config = ['url' => trim($_POST['webhook_url'] ?? '')];
        }
        
        if (isset($intByType[$intType])) {
            $upd = $pdo->prepare('UPDATE project_integrations SET config = ?, is_active = ? WHERE id = ?');
            $upd->execute([json_encode($config), isset($_POST['is_active']) ? 1 : 0, $intByType[$intType]['id']]);
        } else {
            $ins = $pdo->prepare('INSERT INTO project_integrations (project_id, integration_type, config, is_active) VALUES (?, ?, ?, ?)');
            $ins->execute([$projectId, $intType, json_encode($config), isset($_POST['is_active']) ? 1 : 0]);
        }
        
        $saved = true;
        // Reload integrations
        $intStmt->execute([$projectId]);
        $integrations = $intStmt->fetchAll();
        $intByType = [];
        foreach ($integrations as $int) {
            $intByType[$int['integration_type']] = $int;
        }
    }
}

$subStmt = $pdo->prepare('SELECT plan_type FROM subscriptions WHERE user_id = ? AND status = "active" ORDER BY created_at DESC LIMIT 1');
$subStmt->execute([$userId]);
$subscription = $subStmt->fetch();
$isPro = ($subscription['plan_type'] ?? 'free') === 'pro';

// Load analytics (table might not exist yet)
$projectAnalytics = [];
try {
    $analyticsStmt = $pdo->prepare('SELECT * FROM project_analytics WHERE project_id = ?');
    $analyticsStmt->execute([$projectId]);
    $projectAnalytics = $analyticsStmt->fetchAll();
} catch (Throwable $e) {
    error_log('Analytics load error: ' . $e->getMessage());
}

// Load UTM stats (table might not exist yet)
$utmStats = [];
try {
    $utmStatsStmt = $pdo->prepare('SELECT * FROM project_utm_stats WHERE project_id = ? ORDER BY views DESC LIMIT 20');
    $utmStatsStmt->execute([$projectId]);
    $utmStats = $utmStatsStmt->fetchAll();
} catch (Throwable $e) {
    error_log('UTM stats load error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Редактировать проект — TechMVP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>styles.css" />
    <style>
      .edit-tabs { display: flex; gap: 8px; border-bottom: 1px solid var(--stroke); margin-bottom: 24px; }
      .edit-tabs a { padding: 12px 16px; color: var(--muted); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; }
      .edit-tabs a.active { color: var(--primary); border-bottom-color: var(--primary); }
      .tab-content { display: none; }
      .tab-content.active { display: block; }
      .integration-card { background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 20px; margin-bottom: 16px; }
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
    <main class="section">
      <div class="container" style="max-width:920px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
          <h1 style="margin: 0;"><?= htmlspecialchars($project['name']) ?></h1>
          <div style="display: flex; gap: 12px; align-items: center;">
            <?php if ($project['status'] === 'published'): ?>
            <a href="<?= PROJECTS_PATH ?>view.php?slug=<?= htmlspecialchars($project['slug']) ?>" target="_blank" class="button button--primary">Открыть</a>
            <?php endif; ?>
            <button type="button" onclick="showDeleteModal()" class="button" style="background: #5b1b1b; color: #ffb4b4; border-color: #5b1b1b;">Удалить проект</button>
          </div>
        </div>
        
        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
          <div style="background: var(--card); border: 1px solid var(--stroke); border-radius: 14px; padding: 32px; max-width: 500px; width: 90%;">
            <h3 style="margin: 0 0 16px; color: #ffb4b4;">Удаление проекта</h3>
            <p style="color: var(--text); margin-bottom: 24px;">Это действие нельзя отменить. Все данные проекта (заявки, интеграции, аналитика) будут удалены навсегда.</p>
            <form method="post" action="" style="display: flex; flex-direction: column; gap: 16px;">
              <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
              <input type="hidden" name="action" value="delete_project" />
              <div>
                <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Для подтверждения введите название проекта:</label>
                <input type="text" name="confirm_name" placeholder="<?= htmlspecialchars($project['name']) ?>" required style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              </div>
              <div style="display: flex; gap: 12px;">
                <button type="submit" class="button" style="background: #5b1b1b; color: #ffb4b4; border-color: #5b1b1b; flex: 1;">Удалить навсегда</button>
                <button type="button" onclick="hideDeleteModal()" class="button button--ghost" style="flex: 1;">Отмена</button>
              </div>
            </form>
          </div>
        </div>
        
        <?php if ($saved): ?>
        <div class="card" style="border-color:#195b4f;background:#10231f;color:#c7fff1;margin-bottom:24px;">Сохранено</div>
        <?php endif; ?>
        
        <?php if ($errors): ?>
        <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-bottom:24px;">
          <ul style="margin:8px 0 0 18px;">
            <?php foreach ($errors as $err): ?><li><?=htmlspecialchars($err)?></li><?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        
        <div class="edit-tabs">
          <a href="#settings" class="active" onclick="showTab('settings'); return false;">Настройки</a>
          <a href="#integrations" onclick="showTab('integrations'); return false;">Интеграции</a>
          <a href="#analytics" onclick="showTab('analytics'); return false;">Аналитика</a>
          <?php if ($isPro): ?>
          <a href="#pixels" onclick="showTab('pixels'); return false;">Пиксели</a>
          <?php endif; ?>
        </div>
        
        <!-- Settings Tab -->
        <div class="tab-content active" id="tab-settings">
          <form method="post" action="" style="display: flex; flex-direction: column; gap: 16px;">
            <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
            <input type="hidden" name="action" value="update" />
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Название проекта *</label>
              <input type="text" name="name" value="<?=htmlspecialchars($project['name'])?>" required style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
            </div>
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Описание</label>
              <textarea name="description" style="width:100%;min-height:120px;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;"><?=htmlspecialchars($project['description'] ?? '')?></textarea>
            </div>
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Статус</label>
              <select name="status" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
                <option value="draft" <?= $project['status'] === 'draft' ? 'selected' : '' ?>>Черновик</option>
                <option value="published" <?= $project['status'] === 'published' ? 'selected' : '' ?>>Опубликован</option>
                <option value="archived" <?= $project['status'] === 'archived' ? 'selected' : '' ?>>Архив</option>
              </select>
            </div>
            
            <?php if ($isPro): ?>
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Поддомен</label>
              <input type="text" name="subdomain" value="<?=htmlspecialchars($project['subdomain'] ?? '')?>" placeholder="myproject" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">Будет доступен по адресу: <?= htmlspecialchars($project['subdomain'] ?? 'myproject') ?>.techmvp.site</p>
            </div>
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Кастомный домен</label>
              <input type="text" name="domain" value="<?=htmlspecialchars($project['domain'] ?? '')?>" placeholder="example.com" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">Укажите свой домен (требует настройки DNS)</p>
            </div>
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Тема оформления</label>
              <select name="theme" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
                <option value="default" <?= ($project['theme'] ?? 'default') === 'default' ? 'selected' : '' ?>>По умолчанию</option>
                <option value="minimal" <?= ($project['theme'] ?? '') === 'minimal' ? 'selected' : '' ?>>Минималистичная</option>
                <option value="modern" <?= ($project['theme'] ?? '') === 'modern' ? 'selected' : '' ?>>Современная</option>
                <option value="corporate" <?= ($project['theme'] ?? '') === 'corporate' ? 'selected' : '' ?>>Корпоративная</option>
              </select>
            </div>
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Favicon URL</label>
              <input type="url" name="favicon_url" value="<?=htmlspecialchars($project['favicon_url'] ?? '')?>" placeholder="https://example.com/favicon.ico" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--stroke);">
              <h3 style="margin: 0 0 16px;">Настройки формы заявок</h3>
              
              <div style="margin-bottom: 16px;">
                <label style="display: flex; gap: 8px; align-items: center; cursor: pointer;">
                  <input type="checkbox" name="form_enabled" value="1" <?= ($project['form_enabled'] ?? true) ? 'checked' : '' ?> />
                  <span style="font-weight: 600;">Показывать форму заявок на странице проекта</span>
                </label>
                <p style="color: var(--muted); font-size: 12px; margin-top: 6px; margin-left: 28px;">Форма используется для сбора контактов потенциальных клиентов (лидов) для проверки гипотезы MVP. Заявки сохраняются в базе и отправляются в Telegram/Email/Webhook</p>
              </div>
              
              <?php
              $formFieldsData = !empty($project['form_fields']) ? json_decode($project['form_fields'], true) : [
                  'name' => ['enabled' => true, 'required' => false, 'placeholder' => 'Ваше имя'],
                  'email' => ['enabled' => true, 'required' => true, 'placeholder' => 'Email'],
                  'phone' => ['enabled' => true, 'required' => false, 'placeholder' => 'Телефон'],
                  'message' => ['enabled' => true, 'required' => false, 'placeholder' => 'Сообщение']
              ];
              ?>
              
              <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Заголовок формы</label>
                <input type="text" name="form_title" value="<?= htmlspecialchars($project['form_title'] ?? '') ?>" placeholder="Оставьте пустым, чтобы скрыть заголовок" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              </div>
              
              <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Текст кнопки</label>
                <input type="text" name="form_button_text" value="<?= htmlspecialchars($project['form_button_text'] ?? 'Отправить заявку') ?>" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              </div>
              
              <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 12px; color: var(--text); font-weight: 600;">Настройка полей формы</label>
                <div style="display: grid; gap: 12px; background: var(--card); border: 1px solid var(--stroke); border-radius: 10px; padding: 16px;">
                  <?php foreach (['name' => 'Имя', 'email' => 'Email', 'phone' => 'Телефон', 'message' => 'Сообщение'] as $fieldKey => $fieldLabel): ?>
                  <div style="border-bottom: 1px solid var(--stroke); padding-bottom: 12px; margin-bottom: 12px;">
                    <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                      <label style="display: flex; gap: 8px; align-items: center; cursor: pointer; flex: 1;">
                        <input type="checkbox" name="form_field_<?= $fieldKey ?>_enabled" value="1" <?= ($formFieldsData[$fieldKey]['enabled'] ?? true) ? 'checked' : '' ?> />
                        <span style="font-weight: 600;"><?= $fieldLabel ?></span>
                      </label>
                      <label style="display: flex; gap: 8px; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="form_field_<?= $fieldKey ?>_required" value="1" <?= ($formFieldsData[$fieldKey]['required'] ?? ($fieldKey === 'email' ? true : false)) ? 'checked' : '' ?> />
                        <span style="font-size: 12px; color: var(--muted);">Обязательное</span>
                      </label>
                    </div>
                    <input type="text" name="form_field_<?= $fieldKey ?>_placeholder" value="<?= htmlspecialchars($formFieldsData[$fieldKey]['placeholder'] ?? '') ?>" placeholder="Текст подсказки" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:8px;color:var(--text);padding:8px 12px;font-size:13px;" />
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--stroke);">
              <h3 style="margin: 0 0 16px;">SEO настройки</h3>
              
              <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">SEO заголовок</label>
                <input type="text" name="seo_title" value="<?=htmlspecialchars($project['seo_title'] ?? '')?>" placeholder="Мета-заголовок для поисковых систем" maxlength="255" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
                <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">Рекомендуемая длина: 50-60 символов</p>
              </div>
              
              <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">SEO описание</label>
                <textarea name="seo_description" placeholder="Мета-описание для поисковых систем" maxlength="500" style="width:100%;min-height:100px;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;"><?=htmlspecialchars($project['seo_description'] ?? '')?></textarea>
                <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">Рекомендуемая длина: 150-160 символов</p>
              </div>
            </div>
            
            <?php if ($isPro): ?>
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--stroke);">
              <h3 style="margin: 0 0 16px;">Кастомизация</h3>
              
              <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Кастомный CSS</label>
                <textarea name="custom_css" placeholder="/* Ваш CSS код */" style="width:100%;min-height:150px;font-family: monospace;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;"><?=htmlspecialchars($project['custom_css'] ?? '')?></textarea>
                <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">Добавьте свой CSS для кастомизации внешнего вида</p>
              </div>
              
              <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Кастомный JavaScript</label>
                <textarea name="custom_js" placeholder="// Ваш JavaScript код" style="width:100%;min-height:150px;font-family: monospace;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;"><?=htmlspecialchars($project['custom_js'] ?? '')?></textarea>
                <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">Добавьте свой JavaScript для дополнительного функционала</p>
              </div>
            </div>
            <?php endif; ?>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
              <button class="button button--primary" type="submit">Сохранить</button>
              <a href="<?= DASHBOARD_PATH ?>profile.php?tab=projects" class="button button--ghost">Назад</a>
            </div>
          </form>
        </div>
        
        <!-- Integrations Tab -->
        <div class="tab-content" id="tab-integrations">
          <p class="section__lead" style="margin-bottom: 24px;">Настройте интеграции для получения заявок</p>
          
          <!-- Telegram -->
          <div class="integration-card">
            <h3 style="margin: 0 0 12px;">Telegram</h3>
            <form method="post" action="" style="display: flex; flex-direction: column; gap: 12px;">
              <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
              <input type="hidden" name="action" value="save_integration" />
              <input type="hidden" name="integration_type" value="telegram" />
              <?php $tgConfig = isset($intByType['telegram']) ? json_decode($intByType['telegram']['config'], true) : []; ?>
              <input type="text" name="telegram_bot_token" placeholder="Токен бота" value="<?= htmlspecialchars($tgConfig['bot_token'] ?? '') ?>" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              <input type="text" name="telegram_chat_id" placeholder="ID чата" value="<?= htmlspecialchars($tgConfig['chat_id'] ?? '') ?>" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              <label style="display: flex; gap: 8px; align-items: center;">
                <input type="checkbox" name="is_active" <?= (isset($intByType['telegram']) && $intByType['telegram']['is_active']) ? 'checked' : '' ?> />
                <span>Активна</span>
              </label>
              <button class="button button--primary" type="submit" style="align-self: flex-start;">Сохранить</button>
            </form>
          </div>
          
          <!-- Email -->
          <div class="integration-card">
            <h3 style="margin: 0 0 12px;">Email</h3>
            <form method="post" action="" style="display: flex; flex-direction: column; gap: 12px;">
              <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
              <input type="hidden" name="action" value="save_integration" />
              <input type="hidden" name="integration_type" value="email" />
              <?php $emailConfig = isset($intByType['email']) ? json_decode($intByType['email']['config'], true) : []; ?>
              <input type="email" name="email_address" placeholder="your@email.com" value="<?= htmlspecialchars($emailConfig['email'] ?? '') ?>" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              <label style="display: flex; gap: 8px; align-items: center;">
                <input type="checkbox" name="is_active" <?= (isset($intByType['email']) && $intByType['email']['is_active']) ? 'checked' : '' ?> />
                <span>Активна</span>
              </label>
              <button class="button button--primary" type="submit" style="align-self: flex-start;">Сохранить</button>
            </form>
          </div>
          
          <!-- Webhook -->
          <?php if ($isPro): ?>
          <div class="integration-card">
            <h3 style="margin: 0 0 12px;">Webhook</h3>
            <form method="post" action="" style="display: flex; flex-direction: column; gap: 12px;">
              <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
              <input type="hidden" name="action" value="save_integration" />
              <input type="hidden" name="integration_type" value="webhook" />
              <?php $whConfig = isset($intByType['webhook']) ? json_decode($intByType['webhook']['config'], true) : []; ?>
              <input type="url" name="webhook_url" placeholder="https://your-webhook-url.com" value="<?= htmlspecialchars($whConfig['url'] ?? '') ?>" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
              <label style="display: flex; gap: 8px; align-items: center;">
                <input type="checkbox" name="is_active" <?= (isset($intByType['webhook']) && $intByType['webhook']['is_active']) ? 'checked' : '' ?> />
                <span>Активна</span>
              </label>
              <button class="button button--primary" type="submit" style="align-self: flex-start;">Сохранить</button>
            </form>
          </div>
          <?php endif; ?>
        </div>
        
        <!-- Analytics Tab -->
        <div class="tab-content" id="tab-analytics">
          <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="stat-card" style="background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 20px;">
              <div style="color: var(--muted); font-size: 14px;">Просмотры</div>
              <div style="font-size: 32px; font-weight: 700; color: var(--primary); margin: 8px 0;"><?= $project['analytics_views'] ?></div>
            </div>
            <div class="stat-card" style="background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 20px;">
              <div style="color: var(--muted); font-size: 14px;">Клики</div>
              <div style="font-size: 32px; font-weight: 700; color: var(--primary); margin: 8px 0;"><?= $project['analytics_clicks'] ?></div>
            </div>
            <div class="stat-card" style="background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 20px;">
              <div style="color: var(--muted); font-size: 14px;">Заявки</div>
              <div style="font-size: 32px; font-weight: 700; color: var(--primary); margin: 8px 0;"><?= $project['analytics_leads'] ?></div>
            </div>
            <div class="stat-card" style="background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 20px;">
              <div style="color: var(--muted); font-size: 14px;">Конверсия</div>
              <div style="font-size: 32px; font-weight: 700; color: var(--primary); margin: 8px 0;">
                <?= $project['analytics_views'] > 0 ? number_format(($project['analytics_leads'] / $project['analytics_views']) * 100, 1) : 0 ?>%
              </div>
            </div>
          </div>
          
          <?php
          $leadsStmt = $pdo->prepare('SELECT * FROM leads WHERE project_id = ? ORDER BY created_at DESC LIMIT 20');
          $leadsStmt->execute([$projectId]);
          $leads = $leadsStmt->fetchAll();
          ?>
          
          <?php if (!empty($leads)): ?>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin: 0;">Последние заявки</h3>
            <a href="<?= API_PATH ?>export.php?project_id=<?= $projectId ?>" class="button button--ghost" style="padding: 8px 12px; font-size: 14px;">📥 Экспорт в CSV</a>
          </div>
          <div style="display: grid; gap: 12px;">
            <?php foreach ($leads as $lead): ?>
            <div style="background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 16px;">
              <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <strong><?= htmlspecialchars($lead['name'] ?: 'Без имени') ?></strong>
                <span style="color: var(--muted); font-size: 12px;"><?= date('d.m.Y H:i', strtotime($lead['created_at'])) ?></span>
              </div>
              <?php if ($lead['email']): ?><div style="color: var(--muted); font-size: 14px;">📧 <?= htmlspecialchars($lead['email']) ?></div><?php endif; ?>
              <?php if ($lead['phone']): ?><div style="color: var(--muted); font-size: 14px;">📱 <?= htmlspecialchars($lead['phone']) ?></div><?php endif; ?>
              <?php if ($lead['message']): ?><div style="margin-top: 8px; color: var(--text);"><?= nl2br(htmlspecialchars($lead['message'])) ?></div><?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div style="text-align: center; padding: 48px 20px; color: var(--muted);">
            <div style="font-size: 48px; margin-bottom: 16px;">📊</div>
            <p>Пока нет заявок</p>
          </div>
          <?php endif; ?>
        </div>
        
        <?php if ($isPro): ?>
        <!-- Pixels Tab -->
        <div class="tab-content" id="tab-pixels">
          <p class="section__lead" style="margin-bottom: 24px;">Подключите свои пиксели и счетчики аналитики</p>
          
          <?php if (!empty($projectAnalytics)): ?>
          <h3 style="margin: 0 0 16px;">Подключенные пиксели</h3>
          <div style="display: grid; gap: 12px; margin-bottom: 24px;">
            <?php foreach ($projectAnalytics as $anal): ?>
            <div style="background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
              <div>
                <strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $anal['analytics_type']))) ?></strong>
                <div style="color: var(--muted); font-size: 12px; margin-top: 4px;"><?= htmlspecialchars($anal['tracking_id']) ?></div>
                <span style="font-size: 12px; color: <?= $anal['is_active'] ? 'var(--primary)' : 'var(--muted)' ?>;"><?= $anal['is_active'] ? 'Активен' : 'Неактивен' ?></span>
              </div>
              <form method="post" style="display: inline-block;" onsubmit="return confirm('Удалить пиксель?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>" />
                <input type="hidden" name="action" value="delete_analytics" />
                <input type="hidden" name="analytics_id" value="<?= $anal['id'] ?>" />
                <button type="submit" class="button button--ghost" style="padding: 6px 12px; font-size: 12px; color: #ff6b6b;">Удалить</button>
              </form>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          
          <h3 style="margin: 0 0 16px;">Добавить пиксель</h3>
          <div class="integration-card">
            <form method="post" action="" style="display: flex; flex-direction: column; gap: 12px;">
              <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
              <input type="hidden" name="action" value="save_analytics" />
              
              <div>
                <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Тип аналитики</label>
                <select name="analytics_type" required style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
                  <option value="">Выберите тип</option>
                  <option value="google_analytics">Google Analytics (GA4)</option>
                  <option value="yandex_metrika">Яндекс.Метрика</option>
                  <option value="facebook_pixel">Facebook Pixel</option>
                  <option value="custom">Кастомный код</option>
                </select>
              </div>
              
              <div>
                <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">ID отслеживания / Код</label>
                <input type="text" name="tracking_id" placeholder="G-XXXXXXXXXX или код скрипта" required style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
                <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">Для кастомного кода вставьте полный HTML/JS код</p>
              </div>
              
              <label style="display: flex; gap: 8px; align-items: center;">
                <input type="checkbox" name="is_active" checked />
                <span>Активен</span>
              </label>
              
              <button class="button button--primary" type="submit" style="align-self: flex-start;">Добавить</button>
            </form>
          </div>
        </div>
        <?php endif; ?>
        
        <?php if ($isPro && !empty($utmStats)): ?>
        <!-- UTM Statistics in Analytics Tab -->
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--stroke);">
          <h3 style="margin: 0 0 16px;">Статистика по источникам (UTM)</h3>
          <table style="width: 100%; border-collapse: collapse; background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; overflow: hidden;">
            <thead>
              <tr style="background: var(--bg);">
                <th style="padding: 12px; text-align: left; font-weight: 600;">Источник</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Канал</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Кампания</th>
                <th style="padding: 12px; text-align: right; font-weight: 600;">Просмотры</th>
                <th style="padding: 12px; text-align: right; font-weight: 600;">Клики</th>
                <th style="padding: 12px; text-align: right; font-weight: 600;">Заявки</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($utmStats as $utm): ?>
              <tr style="border-top: 1px solid var(--stroke);">
                <td style="padding: 12px;"><?= htmlspecialchars($utm['utm_source'] ?: '-') ?></td>
                <td style="padding: 12px;"><?= htmlspecialchars($utm['utm_medium'] ?: '-') ?></td>
                <td style="padding: 12px;"><?= htmlspecialchars($utm['utm_campaign'] ?: '-') ?></td>
                <td style="padding: 12px; text-align: right;"><?= $utm['views'] ?></td>
                <td style="padding: 12px; text-align: right;"><?= $utm['clicks'] ?></td>
                <td style="padding: 12px; text-align: right;"><?= $utm['leads'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </main>
    <script>
      function showTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.edit-tabs a').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        event.target.classList.add('active');
      }
      
      function showDeleteModal() {
        document.getElementById('deleteModal').style.display = 'flex';
      }
      
      function hideDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
      }
      
      // Close modal on Escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          hideDeleteModal();
        }
      });
      
      // Close modal on background click
      document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
          hideDeleteModal();
        }
      });
    </script>
  </body>
</html>

