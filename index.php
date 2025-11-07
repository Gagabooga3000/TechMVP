<?php require __DIR__ . '/backend/config.php'; start_session_once(); ?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>TechMVP — Технологический стартап</title>
    <meta name="description" content="TechMVP — запускайте и проверяйте гипотезы быстрее: автоматизация, аналитика и первые пользователи за 5 минут." />
    <meta property="og:title" content="TechMVP — Технологический стартап" />
    <meta property="og:description" content="Запустите MVP за 5 минут: лендинг, лиды и аналитика без кода." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="/" />
    <meta property="og:image" content="/og-image.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <div class="bg-3d"></div>
    <a class="skip-link" href="#main">Перейти к содержанию</a>

    <header class="header" id="header">
      <div class="container header__inner">
        <a href="/index.php" class="logo" aria-label="TechMVP">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 12a8 8 0 1 1 16 0l-2.5 0A5.5 5.5 0 1 0 6.5 12H4z" fill="currentColor"/>
            <circle cx="12" cy="12" r="2.25" fill="#00D3A7"/>
          </svg>
          <span>TechMVP</span>
        </a>

        <button class="nav-toggle" aria-controls="nav" aria-expanded="false" aria-label="Открыть меню">
          <span></span><span></span><span></span>
        </button>

        <nav class="nav" id="nav">
          <a href="#hero">Главная</a>
          <a href="#about">О продукте</a>
          <a href="#benefits">Преимущества</a>
          <a href="#pricing">Тарифы</a>
          <a href="#contacts">Контакты</a>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="/profile.php">Профиль</a>
            <a href="/logout.php">Выйти</a>
          <?php else: ?>
            <a href="/login.php">Войти</a>
            <a href="/register.php">Регистрация</a>
          <?php endif; ?>
        </nav>
      </div>
    </header>

    <main id="main">
      <!-- Hero Section -->
      <section class="section hero" id="hero" aria-label="Первый экран">
        <div class="container hero__inner">
          <div class="hero__content">
            <h1 class="hero__title">Автоматизируйте запуск MVP за 5 минут</h1>
            <p class="hero__subtitle">TechMVP помогает студентам и командам быстро проверить гипотезу: сбор лидов, аналитика и интеграции — без кода.</p>
            <div class="hero__cta">
              <a href="#signup" class="button button--primary">Попробовать бесплатно</a>
              <a href="#demo" class="button button--ghost">Запросить демо-доступ</a>
            </div>
            <div style="margin-top:14px;max-width:560px">
              <video src="" controls preload="none" style="width:100%;border-radius:12px;border:1px solid var(--stroke);background:#0b0f14" poster="https://dummyimage.com/800x450/0f1720/ffffff&text=Demo+Video" loading="lazy"></video>
            </div>
          </div>
          <div class="hero__visual scene" aria-hidden="true">
            <div class="mockup" data-tilt>
              <span class="glow"></span>
              <div class="mockup__top">
                <span></span><span></span><span></span>
              </div>
              <div class="mockup__body">
                <div class="mockup__card"></div>
                <div class="mockup__card mockup__card--alt"></div>
                <div class="mockup__rows"></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Problem / Solution -->
      <section class="section section--alt" id="about" aria-label="Проблема и решение">
        <div class="container grid-2">
          <div>
            <h2>Проблема</h2>
            <p>Командам сложно быстро собрать лендинг, подключить сбор контактов и аналитику. На это уходят недели, ресурсы и фокус от главного — проверки гипотезы.</p>
          </div>
          <div>
            <h2>Решение</h2>
            <p>TechMVP — это набор готовых блоков и интеграций, который позволяет запустить лендинг, начать сбор лидов и получить первые метрики уже сегодня.</p>
          </div>
        </div>
      </section>

      <!-- How it works / Benefits -->
      <section class="section" id="benefits" aria-label="Как это работает">
        <div class="container">
          <h2>Как это работает</h2>
          <div class="cards">
            <div class="card scene" data-tilt>
              <span class="glow"></span>
              <div class="card__icon">⚡</div>
              <h3>Быстрый старт</h3>
              <p>Выберите шаблон, добавьте УТП и подключите форму — готово.</p>
            </div>
            <div class="card scene" data-tilt>
              <span class="glow"></span>
              <div class="card__icon">📈</div>
              <h3>Встроенная аналитика</h3>
              <p>Смотрите конверсии кнопок и форм, улучшайте страницы на данных.</p>
            </div>
            <div class="card scene" data-tilt>
              <span class="glow"></span>
              <div class="card__icon">🔌</div>
              <h3>Интеграции</h3>
              <p>Подключите почту, таблицы и мессенджеры — без кода.</p>
            </div>
            <div class="card scene" data-tilt>
              <span class="glow"></span>
              <div class="card__icon">🛡️</div>
              <h3>Надёжность</h3>
              <p>Адаптивный дизайн и высокая скорость загрузки из коробки.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Pricing -->
      <section class="section section--alt" id="pricing" aria-label="Тарифы">
        <div class="container">
          <h2>Тарифы</h2>
          <p class="section__lead">Выберите план, который подходит именно вам</p>
          <div class="pricing">
            <div class="plan scene" data-tilt>
              <span class="glow"></span>
              <h3>Базовый</h3>
              <div class="price">0₽ <span>/ 14 дней</span></div>
              <ul class="features">
                <li>Готовый лендинг</li>
                <li>Форма сбора e-mail</li>
                <li>Базовая аналитика</li>
              </ul>
              <a href="#signup" class="button button--secondary">Начать бесплатно</a>
            </div>
            <div class="plan plan--pro scene" data-tilt>
              <span class="glow"></span>
              <div class="badge">Рекомендуем</div>
              <h3>Профессиональный</h3>
              <div class="price">490₽ <span>/ месяц</span></div>
              <ul class="features">
                <li>Все из Базового</li>
                <li>Интеграции (Google Sheets, Telegram)</li>
                <li>Событийная аналитика</li>
              </ul>
              <a href="#signup" class="button button--primary">Выбрать тариф</a>
            </div>
          </div>
        </div>
      </section>

      <!-- Social Proof -->
      <section class="section" id="social" aria-label="Социальное доказательство">
        <div class="container">
          <h2>Нам доверяют</h2>
          <div class="partners" aria-label="Партнёры">
            <img src="https://dummyimage.com/120x34/0f1720/ffffff&text=A" alt="Партнёр A" loading="lazy" />
            <img src="https://dummyimage.com/120x34/0f1720/ffffff&text=B" alt="Партнёр B" loading="lazy" />
            <img src="https://dummyimage.com/120x34/0f1720/ffffff&text=C" alt="Партнёр C" loading="lazy" />
            <img src="https://dummyimage.com/120x34/0f1720/ffffff&text=D" alt="Партнёр D" loading="lazy" />
            <img src="https://dummyimage.com/120x34/0f1720/ffffff&text=E" alt="Партнёр E" loading="lazy" />
          </div>
          <div class="testimonials">
            <blockquote class="testimonial">
              <p>«Собрали страницу, лид-форму и аналитику за вечер. На защите уже были первые заявки.»</p>
              <cite>Анна, капитан команды</cite>
            </blockquote>
            <blockquote class="testimonial">
              <p>«Идеально для кейс-чемпионатов: быстро, понятно, с метриками. Помогло сфокусироваться на продукте.»</p>
              <cite>Илья, product</cite>
            </blockquote>
          </div>
        </div>
      </section>

      <!-- Team -->
      <section class="section section--alt" id="team" aria-label="Команда">
        <div class="container">
          <h2>Команда</h2>
          <div class="team">
            <div class="member">
              <img src="https://images.unsplash.com/photo-1544006659-f0b21884ce1d?q=80&w=600&auto=format&fit=crop" alt="Анна — Продакт" loading="lazy" />
              <h4>Анна</h4>
              <p class="section__lead">Product</p>
            </div>
            <div class="member">
              <img src="https://images.unsplash.com/photo-1547425260-76bcadfb4f2c?q=80&w=600&auto=format&fit=crop" alt="Илья — Разработчик" loading="lazy" />
              <h4>Илья</h4>
              <p class="section__lead">Developer</p>
            </div>
            <div class="member">
              <img src="https://images.unsplash.com/photo-1546525848-3ce03ca516f6?q=80&w=600&auto=format&fit=crop" alt="Марина — Дизайнер" loading="lazy" />
              <h4>Марина</h4>
              <p class="section__lead">Designer</p>
            </div>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section class="section section--alt" id="faq" aria-label="Частые вопросы">
        <div class="container">
          <h2>FAQ</h2>
          <div class="accordion" data-accordion>
            <div class="accordion__item">
              <button class="accordion__trigger">Нужны ли навыки программирования?</button>
              <div class="accordion__content"><p>Нет. Готовые блоки и формы позволяют запустить лендинг без кода.</p></div>
            </div>
            <div class="accordion__item">
              <button class="accordion__trigger">Можно ли подключить аналитику?</button>
              <div class="accordion__content"><p>Да. Событийная аналитика и конверсии форм доступны в Про-плане.</p></div>
            </div>
            <div class="accordion__item">
              <button class="accordion__trigger">Как получить демо?</button>
              <div class="accordion__content"><p>Нажмите «Запросить демо-доступ» и оставьте e-mail, мы свяжемся.</p></div>
            </div>
            <div class="accordion__item">
              <button class="accordion__trigger">Можно ли использовать Flexbe?</button>
              <div class="accordion__content"><p>Да. Структура блоков оптимизирована под быстрое воссоздание на Flexbe.</p></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Final CTA + Signup -->
      <section class="section" id="signup" aria-label="Финальный призыв к действию">
        <div class="container grid-2">
          <div>
            <h2>Готовы проверить гипотезу?</h2>
            <p>Подпишитесь, чтобы первыми получить доступ к бета-версии и шаблонам для защиты проекта.</p>
            <a href="#pricing" class="button button--ghost">Сравнить тарифы</a>
          </div>
          <form class="form" action="https://formspree.io/f/mqazgkzb" method="POST">
            <label class="sr-only" for="email">Ваш e-mail</label>
            <input id="email" name="email" type="email" placeholder="Ваш e-mail" required />
            <label style="display:flex;gap:8px;align-items:flex-start;color:var(--muted);font-size:12px;">
              <input type="checkbox" required style="margin-top:4px;" />
              <span>Я соглашаюсь с <a href="/privacy.html">политикой конфиденциальности</a> и <a href="/terms.html">условиями использования</a>.</span>
            </label>
            <button type="submit" class="button button--primary">Получить доступ</button>
            <p class="form__hint">Отправляя форму, вы соглашаетесь с <a href="/privacy.html">политикой конфиденциальности</a>.</p>
          </form>
        </div>
      </section>
    </main>

    <footer class="footer" id="contacts">
      <div class="container footer__inner">
        <div class="footer__brand">
          <a href="#hero" class="logo" aria-label="TechMVP">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M4 12a8 8 0 1 1 16 0l-2.5 0A5.5 5.5 0 1 0 6.5 12H4z" fill="currentColor"/>
              <circle cx="12" cy="12" r="2.25" fill="#00D3A7"/>
            </svg>
            <span>TechMVP</span>
          </a>
          <p>© <span id="year"></span> TechMVP. Все права защищены.</p>
        </div>
        <div class="footer__links">
          <a href="mailto:team@example.com">fantapavel16@gmail.com</a>
          <a href="https://t.me/yourteam" target="_blank" rel="noopener">Telegram</a>
          <a href="/privacy.html">Политика конфиденциальности</a>
        </div>
      </div>
    </footer>

    <!-- Floating CTA -->
    <a href="https://t.me/yourteam" class="float-cta" target="_blank" rel="noopener" aria-label="Заказать звонок">Заказать звонок</a>

    <div class="cookie-banner" id="cookieBanner" style="position:fixed;left:16px;right:16px;bottom:16px;background:#0f1720;border:1px solid var(--stroke);border-radius:12px;padding:12px 14px;display:none;z-index:200;box-shadow:0 10px 30px rgba(0,0,0,.35)">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;justify-content:space-between">
        <div style="color:var(--muted);max-width:720px">Мы используем файлы cookie и аналитику для улучшения сервиса. Подробнее в <a href="/privacy.html">политике конфиденциальности</a>.</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="button button--secondary" data-cookie-reject>Отклонить</button>
          <button class="button button--primary" data-cookie-accept>Согласиться</button>
        </div>
      </div>
    </div>

    <script src="script.js"></script>
  </body>
  </html>


