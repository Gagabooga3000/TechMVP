<?php
require __DIR__ . '/backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Неверный CSRF токен';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $new2 = $_POST['new_password2'] ?? '';
        if (strlen($new) < 6) $errors[] = 'Новый пароль должен быть не менее 6 символов';
        if ($new !== $new2) $errors[] = 'Пароли не совпадают';
        if (!$errors) {
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($current, $row['password_hash'])) {
                $errors[] = 'Текущий пароль неверен';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $upd->execute([$hash, $userId]);
                $message = 'Пароль обновлён';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Смена пароля — TechMVP</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <header class="header">
      <div class="container header__inner">
        <a href="/index.php" class="logo">TechMVP</a>
        <nav class="nav">
          <a href="/profile.php">Профиль</a>
          <a href="/logout.php">Выйти</a>
        </nav>
      </div>
    </header>
    <main class="section section--alt">
      <div class="container" style="max-width:560px;">
        <h1>Смена пароля</h1>
        <?php if ($message): ?><div class="card" style="border-color:#195b4f;background:#10231f;color:#c7fff1;margin-bottom:16px;"><?=htmlspecialchars($message)?></div><?php endif; ?>
        <?php if ($errors): ?>
          <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-bottom:16px;">
            <ul style="margin:8px 0 0 18px;">
              <?php foreach ($errors as $err): ?><li><?=htmlspecialchars($err)?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <form class="form" method="post" action="">
          <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
          <input type="password" name="current_password" placeholder="Текущий пароль" required />
          <input type="password" name="new_password" placeholder="Новый пароль" required />
          <input type="password" name="new_password2" placeholder="Повторите новый пароль" required />
          <button class="button button--primary" type="submit">Обновить пароль</button>
        </form>
      </div>
    </main>
  </body>
</html>


