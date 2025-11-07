<?php
require __DIR__ . '/backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];

// Load profile
$stmt = $pdo->prepare('SELECT email, name, university, telegram, bio, created_at FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    redirect('/logout.php');
}

// Handle update
$saved = false;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Неверный CSRF токен';
    }
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
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Профиль — TechMVP</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <header class="header">
      <div class="container header__inner">
        <a href="/index.php" class="logo">TechMVP</a>
        <nav class="nav">
          <a href="/logout.php">Выйти</a>
          <a href="/change_password.php">Сменить пароль</a>
        </nav>
      </div>
    </header>
    <main class="section">
      <div class="container" style="max-width:840px;">
        <h1>Профиль</h1>
        <div class="grid-2">
          <div>
            <div class="card">
              <h3>Данные</h3>
              <p class="section__lead">E-mail: <?=htmlspecialchars($user['email'])?></p>
              <p class="section__lead">С нами с: <?=htmlspecialchars($user['created_at'])?></p>
            </div>
            <?php if ($saved): ?>
            <div class="card" style="border-color:#195b4f;background:#10231f;color:#c7fff1;margin-top:12px;">Сохранено</div>
            <?php endif; ?>
            <?php if ($errors): ?>
            <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-top:12px;">
              <ul style="margin:8px 0 0 18px;">
                <?php foreach ($errors as $err): ?><li><?=htmlspecialchars($err)?></li><?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>
          </div>
          <form class="form" method="post" action="">
            <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
            <input type="text" name="name" placeholder="Имя" value="<?=htmlspecialchars($user['name'] ?? '')?>" />
            <input type="text" name="university" placeholder="Учебное заведение" value="<?=htmlspecialchars($user['university'] ?? '')?>" />
            <input type="text" name="telegram" placeholder="Telegram (@username)" value="<?=htmlspecialchars($user['telegram'] ?? '')?>" />
            <textarea name="bio" placeholder="О себе" style="width:100%;min-height:140px;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;"><?=htmlspecialchars($user['bio'] ?? '')?></textarea>
            <button class="button button--primary" type="submit">Сохранить</button>
          </form>
        </div>
      </div>
    </main>
  </body>
</html>


