<?php
require __DIR__ . '/../backend/config.php';
start_session_once();

$pdo = db_get_pdo();
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    http_response_code(404);
    die('Проект не найден');
}

// Load project
$stmt = $pdo->prepare('SELECT * FROM projects WHERE slug = ? AND status = "published"');
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    die('Проект не найден или не опубликован');
}

// Track view (only once per session)
$viewKey = 'project_view_' . $project['id'];
if (!isset($_SESSION[$viewKey])) {
    $pdo->prepare('UPDATE projects SET analytics_views = analytics_views + 1 WHERE id = ?')->execute([$project['id']]);
    $_SESSION[$viewKey] = true;
    
    // Track UTM parameters
    $utmSource = $_GET['utm_source'] ?? null;
    $utmMedium = $_GET['utm_medium'] ?? null;
    $utmCampaign = $_GET['utm_campaign'] ?? null;
    
    if ($utmSource || $utmMedium || $utmCampaign) {
        try {
            // Update or insert UTM stats (table might not exist yet)
            $utmStmt = $pdo->prepare('INSERT INTO project_utm_stats (project_id, utm_source, utm_medium, utm_campaign, views) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE views = views + 1');
            $utmStmt->execute([$project['id'], $utmSource, $utmMedium, $utmCampaign]);
        } catch (Throwable $e) {
            // Table might not exist - ignore for now
            error_log('UTM stats error: ' . $e->getMessage());
        }
    }
}

// Load analytics pixels (table might not exist yet)
$analytics = [];
try {
    $analyticsStmt = $pdo->prepare('SELECT * FROM project_analytics WHERE project_id = ? AND is_active = 1');
    $analyticsStmt->execute([$project['id']]);
    $analytics = $analyticsStmt->fetchAll();
} catch (Throwable $e) {
    // Table might not exist - ignore for now
    error_log('Analytics pixels error: ' . $e->getMessage());
}

// Load integrations
$intStmt = $pdo->prepare('SELECT * FROM project_integrations WHERE project_id = ? AND is_active = 1');
$intStmt->execute([$project['id']]);
$integrations = $intStmt->fetchAll();
$intByType = [];
foreach ($integrations as $int) {
    $intByType[$int['integration_type']] = json_decode($int['config'], true);
}

// Handle form submission (only if form is enabled)
$formEnabled = $project['form_enabled'] ?? true;
if ($formEnabled && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_lead'])) {
    $formFieldsData = !empty($project['form_fields']) ? json_decode($project['form_fields'], true) : [
        'name' => ['enabled' => true, 'required' => false],
        'email' => ['enabled' => true, 'required' => true],
        'phone' => ['enabled' => true, 'required' => false],
        'message' => ['enabled' => true, 'required' => false]
    ];
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate required fields based on form settings
    if (($formFieldsData['email']['enabled'] ?? true) && ($formFieldsData['email']['required'] ?? true) && empty($email)) {
        $error = 'Email обязателен для заполнения';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email';
    } elseif (($formFieldsData['phone']['enabled'] ?? true) && ($formFieldsData['phone']['required'] ?? false) && empty($phone)) {
        $error = 'Телефон обязателен для заполнения';
    } elseif (($formFieldsData['name']['enabled'] ?? true) && ($formFieldsData['name']['required'] ?? false) && empty($name)) {
        $error = 'Имя обязательно для заполнения';
    } elseif (($formFieldsData['message']['enabled'] ?? true) && ($formFieldsData['message']['required'] ?? false) && empty($message)) {
        $error = 'Сообщение обязательно для заполнения';
    }
    
    if (empty($error) && ($email || $phone)) {
        try {
            // Save lead
            // Get UTM from URL or session
        $utmSource = $_GET['utm_source'] ?? $_SESSION['utm_source_' . $project['id']] ?? null;
        $utmMedium = $_GET['utm_medium'] ?? $_SESSION['utm_medium_' . $project['id']] ?? null;
        $utmCampaign = $_GET['utm_campaign'] ?? $_SESSION['utm_campaign_' . $project['id']] ?? null;
        
        // Save UTM to session for this project
        if ($utmSource) $_SESSION['utm_source_' . $project['id']] = $utmSource;
        if ($utmMedium) $_SESSION['utm_medium_' . $project['id']] = $utmMedium;
        if ($utmCampaign) $_SESSION['utm_campaign_' . $project['id']] = $utmCampaign;
        
        $leadStmt = $pdo->prepare('INSERT INTO leads (project_id, name, email, phone, message, source, utm_source, utm_medium, utm_campaign) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $leadStmt->execute([
            $project['id'],
            $name ?: null,
            $email ?: null,
            $phone ?: null,
            $message ?: null,
            $_SERVER['HTTP_REFERER'] ?? 'direct',
            $utmSource,
            $utmMedium,
            $utmCampaign
        ]);
        
        // Increment leads counter
        $pdo->prepare('UPDATE projects SET analytics_leads = analytics_leads + 1 WHERE id = ?')->execute([$project['id']]);
        
        // Update UTM stats for leads
        if ($utmSource || $utmMedium || $utmCampaign) {
            try {
                $utmLeadStmt = $pdo->prepare('INSERT INTO project_utm_stats (project_id, utm_source, utm_medium, utm_campaign, leads) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE leads = leads + 1');
                $utmLeadStmt->execute([$project['id'], $utmSource, $utmMedium, $utmCampaign]);
            } catch (Throwable $e) {
                error_log('UTM stats leads error: ' . $e->getMessage());
            }
        }
        
        // Send to integrations
        if (isset($intByType['email']) && !empty($intByType['email']['email'])) {
            $subject = "Новая заявка: " . $project['name'];
            $body = "Имя: " . ($name ?: 'Не указано') . "\n";
            $body .= "Email: " . ($email ?: 'Не указано') . "\n";
            $body .= "Телефон: " . ($phone ?: 'Не указано') . "\n";
            $body .= "Сообщение: " . ($message ?: 'Нет') . "\n";
            @mail($intByType['email']['email'], $subject, $body);
        }
        
        if (isset($intByType['telegram']) && !empty($intByType['telegram']['bot_token']) && !empty($intByType['telegram']['chat_id'])) {
            $text = "🎯 *Новая заявка*\n\n";
            $text .= "Проект: " . $project['name'] . "\n";
            $text .= "Имя: " . ($name ?: 'Не указано') . "\n";
            $text .= "Email: " . ($email ?: 'Не указано') . "\n";
            $text .= "Телефон: " . ($phone ?: 'Не указано') . "\n";
            if ($message) $text .= "Сообщение: " . $message . "\n";
            
            $url = "https://api.telegram.org/bot" . $intByType['telegram']['bot_token'] . "/sendMessage";
            $data = [
                'chat_id' => $intByType['telegram']['chat_id'],
                'text' => $text,
                'parse_mode' => 'Markdown'
            ];
            @file_get_contents($url . '?' . http_build_query($data));
        }
        
        if (isset($intByType['webhook']) && !empty($intByType['webhook']['url'])) {
            $data = [
                'project' => $project['name'],
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'timestamp' => date('c')
            ];
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($data)
                ]
            ]);
            @file_get_contents($intByType['webhook']['url'], false, $context);
        }
        
            $success = true;
        } catch (Throwable $e) {
            $error = 'Ошибка при сохранении заявки. Попробуйте позже.';
            error_log('Lead save error: ' . $e->getMessage());
        }
    } elseif (empty($error) && !($email || $phone)) {
        $error = 'Укажите email или телефон';
    }
}

$templateType = $project['template_type'] ?: 'basic';
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($project['seo_title'] ?: $project['name'] . ' — TechMVP') ?></title>
    <meta name="description" content="<?= htmlspecialchars($project['seo_description'] ?: $project['description'] ?: $project['name']) ?>" />
    <?php if ($project['favicon_url']): ?>
    <link rel="icon" href="<?= htmlspecialchars($project['favicon_url']) ?>" />
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>styles.css" />
    <?php if ($project['custom_css']): ?>
    <style><?= htmlspecialchars($project['custom_css']) ?></style>
    <?php endif; ?>
    
    <?php
    // Load and output analytics pixels
    foreach ($analytics as $analyticsItem):
        if ($analyticsItem['analytics_type'] === 'google_analytics'):
    ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($analyticsItem['tracking_id']) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= htmlspecialchars($analyticsItem['tracking_id']) ?>');
    </script>
    <?php
        elseif ($analyticsItem['analytics_type'] === 'yandex_metrika'):
    ?>
    <!-- Yandex.Metrika -->
    <script type="text/javascript">
       (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
       m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
       (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
       ym(<?= htmlspecialchars($analyticsItem['tracking_id']) ?>, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true
       });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/<?= htmlspecialchars($analyticsItem['tracking_id']) ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <?php
        elseif ($analyticsItem['analytics_type'] === 'facebook_pixel'):
    ?>
    <!-- Facebook Pixel -->
    <script>
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '<?= htmlspecialchars($analyticsItem['tracking_id']) ?>');
      fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id=<?= htmlspecialchars($analyticsItem['tracking_id']) ?>&ev=PageView&noscript=1"
    /></noscript>
    <?php
        elseif ($analyticsItem['analytics_type'] === 'custom'):
    ?>
    <!-- Custom Analytics -->
    <?= $analyticsItem['tracking_id'] ?>
    <?php
        endif;
    endforeach;
    ?>
    <style>
      .landing-hero { padding: 80px 0; text-align: center; }
      .landing-hero h1 { font-size: 48px; margin-bottom: 16px; }
      .landing-hero p { font-size: 20px; color: var(--muted); max-width: 600px; margin: 0 auto 32px; }
      .lead-form { max-width: 500px; margin: 0 auto; background: var(--card); border: 1px solid var(--stroke); border-radius: 14px; padding: 32px; }
      .lead-form input, .lead-form textarea { width: 100%; background: #0c131b; border: 1px solid var(--stroke); border-radius: 10px; color: var(--text); padding: 12px 14px; margin-bottom: 16px; }
      .lead-form textarea { min-height: 120px; }
    </style>
  </head>
  <body>
    <div class="bg-3d"></div>
    <header class="header">
      <div class="container header__inner">
        <a href="<?= BASE_PATH ?>index.php" class="logo"><?= htmlspecialchars($project['name']) ?></a>
      </div>
    </header>
    <main>
      <section class="landing-hero">
        <div class="container">
          <h1><?= htmlspecialchars($project['name']) ?></h1>
          <?php if ($project['description']): ?>
          <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
          <?php endif; ?>
          
          <?php
          // Check if form is enabled
          $formEnabled = $project['form_enabled'] ?? true;
          $formFieldsData = !empty($project['form_fields']) ? json_decode($project['form_fields'], true) : [
              'name' => ['enabled' => true, 'required' => false, 'placeholder' => 'Ваше имя'],
              'email' => ['enabled' => true, 'required' => true, 'placeholder' => 'Email'],
              'phone' => ['enabled' => true, 'required' => false, 'placeholder' => 'Телефон'],
              'message' => ['enabled' => true, 'required' => false, 'placeholder' => 'Сообщение']
          ];
          $formTitle = $project['form_title'] ?? '';
          $formButtonText = $project['form_button_text'] ?? 'Отправить заявку';
          ?>
          
          <?php if ($formEnabled): ?>
          <div class="lead-form">
            <?php if ($formTitle): ?>
            <h3 style="margin: 0 0 20px; text-align: center;"><?= htmlspecialchars($formTitle) ?></h3>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
            <div style="background: #10231f; border: 1px solid #195b4f; color: #c7fff1; padding: 16px; border-radius: 10px; margin-bottom: 24px;">
              Спасибо! Ваша заявка отправлена.
            </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
            <div style="background: #201316; border: 1px solid #5b1b1b; color: #ffb4b4; padding: 16px; border-radius: 10px; margin-bottom: 24px;">
              <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
              <input type="hidden" name="submit_lead" value="1" />
              
              <?php if ($formFieldsData['name']['enabled'] ?? true): ?>
              <input type="text" name="name" placeholder="<?= htmlspecialchars($formFieldsData['name']['placeholder'] ?? 'Ваше имя') ?>" <?= ($formFieldsData['name']['required'] ?? false) ? 'required' : '' ?> />
              <?php endif; ?>
              
              <?php if ($formFieldsData['email']['enabled'] ?? true): ?>
              <input type="email" name="email" placeholder="<?= htmlspecialchars($formFieldsData['email']['placeholder'] ?? 'Email') ?>" <?= ($formFieldsData['email']['required'] ?? true) ? 'required' : '' ?> />
              <?php endif; ?>
              
              <?php if ($formFieldsData['phone']['enabled'] ?? true): ?>
              <input type="tel" name="phone" placeholder="<?= htmlspecialchars($formFieldsData['phone']['placeholder'] ?? 'Телефон') ?>" <?= ($formFieldsData['phone']['required'] ?? false) ? 'required' : '' ?> />
              <?php endif; ?>
              
              <?php if ($formFieldsData['message']['enabled'] ?? true): ?>
              <textarea name="message" placeholder="<?= htmlspecialchars($formFieldsData['message']['placeholder'] ?? 'Сообщение') ?>" <?= ($formFieldsData['message']['required'] ?? false) ? 'required' : '' ?>></textarea>
              <?php endif; ?>
              
              <button class="button button--primary" type="submit" style="width: 100%;" onclick="trackClick(<?= $project['id'] ?>)"><?= htmlspecialchars($formButtonText) ?></button>
            </form>
          </div>
          <?php endif; ?>
        </div>
      </section>
    </main>
    <footer class="footer">
      <div class="container footer__inner">
        <?php if (empty($project['hide_branding'])): ?>
        <p style="color: var(--muted); text-align: center;">Создано с помощью TechMVP</p>
        <?php endif; ?>
      </div>
    </footer>
    <script>
      function getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        return {
          utm_source: params.get('utm_source'),
          utm_medium: params.get('utm_medium'),
          utm_campaign: params.get('utm_campaign')
        };
      }
      
      function trackClick(projectId) {
        const utm = getUrlParams();
        let url = '<?= API_PATH ?>track.php?project_id=' + projectId;
        if (utm.utm_source) url += '&utm_source=' + encodeURIComponent(utm.utm_source);
        if (utm.utm_medium) url += '&utm_medium=' + encodeURIComponent(utm.utm_medium);
        if (utm.utm_campaign) url += '&utm_campaign=' + encodeURIComponent(utm.utm_campaign);
        fetch(url).catch(() => {});
      }
      // Track CTA clicks
      document.querySelectorAll('a.button, button.button').forEach(btn => {
        btn.addEventListener('click', () => trackClick(<?= $project['id'] ?>));
      });
      <?php if ($project['custom_js']): ?>
      <?= $project['custom_js'] ?>
      <?php endif; ?>
    </script>
  </body>
</html>

