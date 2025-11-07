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

$tab = $_GET['tab'] ?? 'emails';
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Шаблоны и материалы — TechMVP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>styles.css" />
    <style>
      .templates-nav { display: flex; gap: 8px; border-bottom: 1px solid var(--stroke); margin-bottom: 24px; flex-wrap: wrap; }
      .templates-nav a { padding: 12px 16px; color: var(--muted); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: .2s; }
      .templates-nav a:hover { color: var(--text); }
      .templates-nav a.active { color: var(--primary); border-bottom-color: var(--primary); }
      .template-card { background: var(--card); border: 1px solid var(--stroke); border-radius: 12px; padding: 24px; margin-bottom: 16px; }
      .template-card h3 { margin: 0 0 12px; }
      .template-card pre { background: #0c131b; border: 1px solid var(--stroke); border-radius: 8px; padding: 16px; overflow-x: auto; font-size: 13px; line-height: 1.6; }
      .copy-btn { margin-top: 12px; }
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
        <h1>Шаблоны и материалы</h1>
        <p class="section__lead">Готовые шаблоны для коммуникации и проверки гипотез</p>
        
        <nav class="templates-nav">
          <a href="?tab=emails" class="<?= $tab === 'emails' ? 'active' : '' ?>">📧 Письма</a>
          <a href="?tab=pitch" class="<?= $tab === 'pitch' ? 'active' : '' ?>">📊 Pitch Deck</a>
          <a href="?tab=surveys" class="<?= $tab === 'surveys' ? 'active' : '' ?>">📋 Опросы</a>
          <a href="?tab=ai" class="<?= $tab === 'ai' ? 'active' : '' ?>">🤖 AI-ассистент</a>
        </nav>
        
        <?php if ($tab === 'emails'): ?>
        <!-- Email Templates -->
        <div class="template-card">
          <h3>Письмо первым пользователям</h3>
          <p style="color: var(--muted); margin-bottom: 16px;">Шаблон для сбора обратной связи от первых пользователей MVP</p>
          <pre id="email1">Тема: Расскажите, что думаете о нашем MVP

Привет, [Имя]!

Спасибо, что проявили интерес к [Название продукта]! 

Мы только запустились и очень ценим ваше мнение. Помогите нам стать лучше:

1. Что вам понравилось?
2. Что можно улучшить?
3. Готовы ли вы платить за это? Сколько?

Ваш ответ займет 2 минуты, но поможет нам сделать продукт, который действительно нужен.

[Ссылка на опрос или форма обратной связи]

С уважением,
[Ваше имя]
Команда [Название продукта]</pre>
          <button class="button button--primary copy-btn" onclick="copyToClipboard('email1')">📋 Копировать</button>
        </div>
        
        <div class="template-card">
          <h3>Письмо потенциальному клиенту</h3>
          <p style="color: var(--muted); margin-bottom: 16px;">Шаблон для привлечения первых клиентов</p>
          <pre id="email2">Тема: [Название продукта] — решение для [проблема]

Привет, [Имя]!

Я заметил, что вы [контекст/проблема]. 

Мы создали [Название продукта] — [краткое описание решения].

[Ключевое преимущество в 1-2 предложениях]

Хотите попробовать бесплатно? [Ссылка на лендинг]

Если у вас есть вопросы, отвечу в течение часа.

С уважением,
[Ваше имя]
[Название продукта]</pre>
          <button class="button button--primary copy-btn" onclick="copyToClipboard('email2')">📋 Копировать</button>
        </div>
        
        <div class="template-card">
          <h3>Письмо ментору/эксперту</h3>
          <p style="color: var(--muted); margin-bottom: 16px;">Шаблон для запроса обратной связи от эксперта</p>
          <pre id="email3">Тема: Запрос обратной связи по MVP [Название продукта]

Здравствуйте, [Имя ментора]!

Я [ваша роль], работаю над [Название продукта] — [краткое описание].

Мы на ранней стадии и очень ценим вашу экспертизу в [область]. 

Могли бы вы уделить 15 минут на обратную связь? Нас интересует:
- Правильно ли мы понимаем проблему?
- Есть ли спрос на такое решение?
- Что критично для первых пользователей?

[Ссылка на лендинг/демо]

Буду благодарен за любые замечания!

С уважением,
[Ваше имя]
[Контакты]</pre>
          <button class="button button--primary copy-btn" onclick="copyToClipboard('email3')">📋 Копировать</button>
        </div>
        
        <?php elseif ($tab === 'pitch'): ?>
        <!-- Pitch Deck Template -->
        <div class="template-card">
          <h3>Мини Pitch Deck — структура</h3>
          <p style="color: var(--muted); margin-bottom: 16px;">Структура презентации для акселератора / конкурса / инвестора</p>
          
          <div style="display: grid; gap: 16px;">
            <div>
              <h4 style="margin: 0 0 8px;">Слайд 1: Проблема</h4>
              <pre style="margin: 0;">[Описание проблемы, которую решаете]
- Кто страдает от этой проблемы?
- Насколько она критична?
- Почему сейчас?</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Слайд 2: Решение</h4>
              <pre style="margin: 0;">[Название продукта] — [одно предложение]
- Как это работает (3 пункта)
- Ключевое преимущество</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Слайд 3: Рынок</h4>
              <pre style="margin: 0;">- Размер рынка (TAM/SAM/SOM)
- Целевая аудитория
- Примеры конкурентов</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Слайд 4: Продукт</h4>
              <pre style="margin: 0;">- Скриншоты/демо
- Ключевые функции
- Roadmap (первые 3 месяца)</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Слайд 5: Трафик и метрики</h4>
              <pre style="margin: 0;">- Первые пользователи: [число]
- Конверсия: [%]
- Обратная связь: [ключевые инсайты]</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Слайд 6: Команда</h4>
              <pre style="margin: 0;">- [Имя] — [роль], опыт в [область]
- [Имя] — [роль], опыт в [область]
- Почему мы?</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Слайд 7: Финансы (если нужно)</h4>
              <pre style="margin: 0;">- Модель монетизации
- План на 6-12 месяцев
- Запрос (если есть)</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Слайд 8: Следующие шаги</h4>
              <pre style="margin: 0;">- Что планируем сделать
- Что нужно для роста
- Контакты</pre>
            </div>
          </div>
        </div>
        
        <?php elseif ($tab === 'surveys'): ?>
        <!-- Survey Templates -->
        <div class="template-card">
          <h3>Опрос для проверки гипотезы</h3>
          <p style="color: var(--muted); margin-bottom: 16px;">Вопросы для Google Forms / Typeform</p>
          
          <div style="display: grid; gap: 16px;">
            <div>
              <h4 style="margin: 0 0 8px;">Блок 1: Контекст</h4>
              <pre style="margin: 0;">1. Какую проблему вы решаете сейчас в [область]?
   [Текстовое поле]

2. Сколько времени тратите на это?
   [Выбор: Меньше часа / 1-3 часа / 3-5 часов / Больше 5 часов]</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Блок 2: Решение</h4>
              <pre style="margin: 0;">3. Если бы существовал инструмент, который [решение], вы бы им пользовались?
   [Выбор: Да / Нет / Возможно]

4. Как часто вы бы использовали такой инструмент?
   [Выбор: Ежедневно / Несколько раз в неделю / Раз в неделю / Реже]</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Блок 3: Готовность платить</h4>
              <pre style="margin: 0;">5. Сколько вы готовы платить за такое решение?
   [Выбор: 0-500₽ / 500-1000₽ / 1000-3000₽ / Больше 3000₽]

6. Что критично для вас в таком решении?
   [Множественный выбор: Скорость / Простота / Функционал / Цена]</pre>
            </div>
            
            <div>
              <h4 style="margin: 0 0 8px;">Блок 4: Контакты</h4>
              <pre style="margin: 0;">7. Оставьте email, если хотите первыми попробовать
   [Email поле]</pre>
            </div>
          </div>
        </div>
        
        <?php elseif ($tab === 'ai'): ?>
        <!-- AI Assistant -->
        <div class="template-card">
          <h3>AI-ассистент по текстам</h3>
          <p style="color: var(--muted); margin-bottom: 16px;">Получите варианты заголовков, УТП и текстов для вашего лендинга</p>
          
          <form id="aiForm" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Ниша / Отрасль</label>
              <input type="text" id="niche" placeholder="Например: SaaS для маркетологов, Образовательная платформа" required style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
            </div>
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Целевая аудитория</label>
              <input type="text" id="audience" placeholder="Например: Студенты, Стартапы, Малый бизнес" required style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;" />
            </div>
            
            <div>
              <label style="display: block; margin-bottom: 8px; color: var(--text); font-weight: 600;">Что генерировать</label>
              <select id="generateType" style="width:100%;background:#0c131b;border:1px solid var(--stroke);border-radius:10px;color:var(--text);padding:12px 14px;">
                <option value="headlines">Заголовки</option>
                <option value="utp">УТП (уникальное торговое предложение)</option>
                <option value="benefits">Преимущества</option>
                <option value="cta">Текст кнопки (CTA)</option>
                <option value="full">Полный текст блока</option>
              </select>
            </div>
            
            <button type="submit" class="button button--primary">Сгенерировать</button>
          </form>
          
          <div id="aiResult" style="margin-top: 24px; display: none;">
            <h4 style="margin: 0 0 12px;">Результат:</h4>
            <div id="aiOutput" style="background: #0c131b; border: 1px solid var(--stroke); border-radius: 8px; padding: 16px; white-space: pre-wrap; line-height: 1.6;"></div>
            <button class="button button--primary copy-btn" onclick="copyToClipboard('aiOutput')">📋 Копировать</button>
          </div>
        </div>
        
        <script>
          document.getElementById('aiForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const niche = document.getElementById('niche').value;
            const audience = document.getElementById('audience').value;
            const type = document.getElementById('generateType').value;
            
            // Простая генерация на основе шаблонов (в реальности можно подключить API)
            const templates = {
              headlines: [
                `Автоматизируйте ${niche} за 5 минут`,
                `${audience} выбирают ${niche} без сложностей`,
                `Запустите ${niche} за выходные`,
                `Проверьте гипотезу ${niche} за 1 день`
              ],
              utp: [
                `Единственный ${niche}, который работает из коробки для ${audience}`,
                `${niche} без настройки — запуск за 5 минут`,
                `Для ${audience}, которые хотят ${niche} без технических знаний`
              ],
              benefits: [
                `Экономия времени: вместо недель — ${niche} за день`,
                `Простота: ${audience} могут использовать без обучения`,
                `Результат: первые пользователи уже через ${niche}`
              ],
              cta: [
                `Попробовать ${niche} бесплатно`,
                `Запустить ${niche} сейчас`,
                `Проверить гипотезу ${niche}`
              ],
              full: `Заголовок: Автоматизируйте ${niche} за 5 минут

Подзаголовок: Для ${audience}, которые хотят результат без сложностей

Преимущества:
• Экономия времени — вместо недель настройки
• Простота использования — без технических знаний  
• Первые результаты уже через день

УТП: Единственный ${niche}, который работает из коробки

CTA: Попробовать бесплатно`
            };
            
            const result = templates[type] || templates.headlines;
            const output = Array.isArray(result) ? result.join('\n\n') : result;
            
            document.getElementById('aiOutput').textContent = output;
            document.getElementById('aiResult').style.display = 'block';
          });
        </script>
        <?php endif; ?>
      </div>
    </main>
    <script>
      function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        const text = element.textContent || element.innerText;
        navigator.clipboard.writeText(text).then(() => {
          alert('Скопировано в буфер обмена!');
        });
      }
    </script>
  </body>
</html>

