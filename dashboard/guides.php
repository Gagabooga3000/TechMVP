<?php
require __DIR__ . '/../backend/config.php';
require_auth();
start_session_once();

$pdo = db_get_pdo();
$userId = (int)$_SESSION['user_id'];

// Check subscription
$subStmt = $pdo->prepare('SELECT plan_type FROM subscriptions WHERE user_id = ? AND status = "active" ORDER BY created_at DESC LIMIT 1');
$subStmt->execute([$userId]);
$subscription = $subStmt->fetch();
$isPro = ($subscription['plan_type'] ?? 'free') === 'pro';

if (!$isPro) {
    redirect(DASHBOARD_PATH . 'profile.php?tab=subscription');
}

$guide = $_GET['guide'] ?? null;
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Гайды и поддержка — TechMVP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>styles.css" />
    <style>
      .guide-card { background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 24px; margin-bottom: 16px; cursor: pointer; transition: .2s; }
      .guide-card:hover { border-color: var(--primary); }
      .guide-content { display: none; margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--stroke); }
      .guide-content.active { display: block; }
      .guide-content h3 { margin-top: 24px; }
      .guide-content h3:first-child { margin-top: 0; }
      .guide-content ul { margin: 12px 0; padding-left: 24px; }
      .guide-content li { margin: 8px 0; }
    </style>
  </head>
  <body>
    <header class="header">
      <div class="container header__inner">
        <a href="<?= BASE_PATH ?>index.php" class="logo">TechMVP</a>
        <nav class="nav">
          <a href="<?= DASHBOARD_PATH ?>profile.php">Профиль</a>
          <a href="<?= DASHBOARD_PATH ?>templates.php">Шаблоны</a>
          <a href="<?= AUTH_PATH ?>logout.php">Выйти</a>
        </nav>
      </div>
    </header>
    <main class="section">
      <div class="container" style="max-width:920px;">
        <h1>Гайды и поддержка</h1>
        <p class="section__lead">Пошаговые инструкции для запуска MVP</p>
        
        <div class="guide-card" onclick="toggleGuide('guide1')">
          <h3 style="margin: 0 0 8px;">📢 Как запустить рекламу на 300–500 ₽ и собрать первые клики</h3>
          <p style="color: var(--muted); margin: 0;">Бюджетная реклама для проверки гипотезы</p>
        </div>
        <div id="guide1" class="guide-content">
          <h3>1. Выберите платформу</h3>
          <ul>
            <li><strong>ВКонтакте:</strong> Минимальный бюджет 100₽, таргетинг по интересам</li>
            <li><strong>Яндекс.Директ:</strong> От 300₽, хорош для B2B</li>
            <li><strong>Telegram-каналы:</strong> Реклама в тематических каналах от 500₽</li>
          </ul>
          
          <h3>2. Настройте таргетинг</h3>
          <ul>
            <li>Возраст: 25-45 лет (или ваша ЦА)</li>
            <li>Интересы: связанные с вашей нишей</li>
            <li>География: ваш город или регион</li>
          </ul>
          
          <h3>3. Создайте объявление</h3>
          <ul>
            <li>Заголовок: проблема или решение (5-7 слов)</li>
            <li>Текст: что получит пользователь (2-3 предложения)</li>
            <li>Изображение: яркое, понятное, без текста</li>
            <li>Кнопка: "Узнать больше" или "Попробовать"</li>
          </ul>
          
          <h3>4. Запустите тест</h3>
          <ul>
            <li>Бюджет: 300-500₽ на 3-5 дней</li>
            <li>Цель: переходы на сайт (не заявки сразу)</li>
            <li>Отслеживайте: клики, просмотры, заявки в аналитике</li>
          </ul>
          
          <h3>5. Анализируйте результаты</h3>
          <ul>
            <li>CPC (цена клика): должна быть < 10₽</li>
            <li>Конверсия: если > 2% — хорошо</li>
            <li>Если не работает: меняйте заголовок/изображение</li>
          </ul>
        </div>
        
        <div class="guide-card" onclick="toggleGuide('guide2')">
          <h3 style="margin: 0 0 8px;">📊 Как читать конверсию</h3>
          <p style="color: var(--muted); margin: 0;">Понимание метрик и что с ними делать</p>
        </div>
        <div id="guide2" class="guide-content">
          <h3>Ключевые метрики</h3>
          <ul>
            <li><strong>Просмотры:</strong> Сколько людей зашло на лендинг</li>
            <li><strong>Клики:</strong> Сколько нажали на кнопку/ссылку</li>
            <li><strong>Заявки:</strong> Сколько оставили контакты</li>
            <li><strong>Конверсия:</strong> Заявки / Просмотры × 100%</li>
          </ul>
          
          <h3>Что считается хорошим</h3>
          <ul>
            <li>Конверсия 2-5% — отлично для MVP</li>
            <li>Конверсия 1-2% — нормально, можно улучшить</li>
            <li>Конверсия < 1% — нужно менять оффер/тексты</li>
          </ul>
          
          <h3>Как улучшить конверсию</h3>
          <ul>
            <li>Упростите форму (меньше полей)</li>
            <li>Добавьте социальное доказательство</li>
            <li>Сделайте УТП понятнее</li>
            <li>Используйте срочность ("Ограниченное предложение")</li>
          </ul>
          
          <h3>UTM-метки</h3>
          <ul>
            <li>Используйте: ?utm_source=vk&utm_medium=ads&utm_campaign=test1</li>
            <li>Смотрите в аналитике, откуда приходят заявки</li>
            <li>Увеличивайте бюджет на рабочие каналы</li>
          </ul>
        </div>
        
        <div class="guide-card" onclick="toggleGuide('guide3')">
          <h3 style="margin: 0 0 8px;">🎯 Что делать после первых 10 лидов</h3>
          <p style="color: var(--muted); margin: 0;">Следующие шаги после получения заявок</p>
        </div>
        <div id="guide3" class="guide-content">
          <h3>1. Свяжитесь с каждым (в течение 24 часов)</h3>
          <ul>
            <li>Личное сообщение лучше массовой рассылки</li>
            <li>Поблагодарите за интерес</li>
            <li>Задайте 2-3 вопроса о проблеме</li>
          </ul>
          
          <h3>2. Соберите обратную связь</h3>
          <ul>
            <li>Что понравилось?</li>
            <li>Что не хватает?</li>
            <li>Готовы ли платить? Сколько?</li>
            <li>Когда бы начали использовать?</li>
          </ul>
          
          <h3>3. Найдите паттерны</h3>
          <ul>
            <li>Какие проблемы повторяются?</li>
            <li>Что критично для пользователей?</li>
            <li>Какие функции не нужны?</li>
          </ul>
          
          <h3>4. Приоритизируйте доработки</h3>
          <ul>
            <li>Что нужно добавить в первую очередь?</li>
            <li>Что можно отложить?</li>
            <li>Что убрать?</li>
          </ul>
          
          <h3>5. Планируйте следующий шаг</h3>
          <ul>
            <li>Если конверсия хорошая — масштабируйте рекламу</li>
            <li>Если плохая — меняйте оффер/тексты</li>
            <li>Если есть спрос — дорабатывайте продукт</li>
          </ul>
        </div>
        
        <div class="guide-card" style="background: #10231f; border-color: #195b4f;">
          <h3 style="margin: 0 0 8px;">💬 Приоритетная поддержка</h3>
          <p style="color: var(--muted); margin: 0 0 16px;">Для Pro-подписки доступна приоритетная поддержка</p>
          <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="mailto:fantapavel16@gmail.com" class="button button--primary">Написать на email</a>
            <a href="https://t.me/pb_bunny" target="_blank" class="button button--ghost">Telegram</a>
          </div>
          <p style="color: var(--muted); font-size: 12px; margin-top: 12px;">Время ответа: до 24 часов для Pro-подписки</p>
        </div>
      </div>
    </main>
    <script>
      function toggleGuide(id) {
        const content = document.getElementById(id);
        const isActive = content.classList.contains('active');
        
        // Закрыть все гайды
        document.querySelectorAll('.guide-content').forEach(el => {
          el.classList.remove('active');
        });
        
        // Открыть выбранный, если он был закрыт
        if (!isActive) {
          content.classList.add('active');
        }
      }
    </script>
  </body>
</html>

