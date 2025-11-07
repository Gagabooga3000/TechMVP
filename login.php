<?php
require __DIR__ . '/backend/config.php';
start_session_once();

if (!empty($_SESSION['user_id'])) {
    redirect('/profile.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Неверный CSRF токен';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        try {
            $pdo = db_get_pdo();
            $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                redirect('/profile.php');
            } else {
                $error = 'Неверный e-mail или пароль';
            }
        } catch (Throwable $e) {
            $error = 'Ошибка сервера. Попробуйте позже';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Вход — TechMVP</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <header class="header">
      <div class="container header__inner">
        <a href="/index.php" class="logo">TechMVP</a>
      </div>
    </header>
    <main class="section section--alt">
      <div class="container" style="max-width:560px;">
        <h1>Вход</h1>
        <?php if ($error): ?>
          <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-bottom:16px;">
            <?=htmlspecialchars($error)?>
          </div>
        <?php endif; ?>
        <form class="form" method="post" action="">
          <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
          <input type="email" name="email" placeholder="E-mail" required value="<?=htmlspecialchars($_POST['email'] ?? '')?>" />
          <input type="password" name="password" placeholder="Пароль" required />
          <button class="button button--primary" type="submit">Войти</button>
        </form>
        <p class="form__hint">Нет аккаунта? <a href="/register.php">Зарегистрироваться</a></p>
      </div>
    </main>
  </body>
</html>


