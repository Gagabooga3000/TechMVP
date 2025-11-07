<?php
require __DIR__ . '/backend/config.php';
start_session_once();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Неверный CSRF токен';
    }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный e-mail';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Пароль должен быть не менее 6 символов';
    }
    if ($password !== $password2) {
        $errors[] = 'Пароли не совпадают';
    }

    if (!$errors) {
        try {
            $pdo = db_get_pdo();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Пользователь с таким e-mail уже существует';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare('INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)');
                $ins->execute([$email, $hash, $name ?: null]);
                $_SESSION['user_id'] = (int)$pdo->lastInsertId();
                redirect('/profile.php');
            }
        } catch (Throwable $e) {
            $errors[] = 'Ошибка сервера. Попробуйте позже';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Регистрация — TechMVP</title>
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
        <h1>Регистрация</h1>
        <?php if ($errors): ?>
          <div class="card" style="border-color:#5b1b1b;background:#201316;color:#ffb4b4;margin-bottom:16px;">
            <ul style="margin:8px 0 0 18px;">
              <?php foreach ($errors as $err): ?><li><?=htmlspecialchars($err)?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <form class="form" method="post" action="">
          <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(generate_csrf_token())?>" />
          <input type="text" name="name" placeholder="Имя (необязательно)" value="<?=htmlspecialchars($_POST['name'] ?? '')?>" />
          <input type="email" name="email" placeholder="E-mail" required value="<?=htmlspecialchars($_POST['email'] ?? '')?>" />
          <input type="password" name="password" placeholder="Пароль" required />
          <input type="password" name="password2" placeholder="Повторите пароль" required />
          <button class="button button--primary" type="submit">Зарегистрироваться</button>
        </form>
        <p class="form__hint">Уже есть аккаунт? <a href="/login.php">Войти</a></p>
      </div>
    </main>
  </body>
</html>


