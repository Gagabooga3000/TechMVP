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
            <a href="<?= DASHBOARD_PATH ?>profile.php">Профиль</a>
            <a href="<?= AUTH_PATH ?>logout.php">Выйти</a>
          <?php else: ?>
            <a href="<?= AUTH_PATH ?>login.php">Войти</a>
            <a href="<?= AUTH_PATH ?>register.php">Регистрация</a>
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
              <video src="<?= ASSETS_PATH ?>videos/Video.mp4" controls preload="none" style="width:100%;border-radius:12px;border:1px solid var(--stroke);background:#0b0f14">
                Ваш браузер не поддерживает видео.
              </video>
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
              <p>Выберите шаблон из библиотеки MVP-лендингов под популярные ниши. Соберите презентабельный прототип за 30 минут вместо недели.</p>
            </div>
            <div class="card scene" data-tilt>
              <span class="glow"></span>
              <div class="card__icon">📈</div>
              <h3>Встроенная аналитика</h3>
              <p>Просмотры, клики, заявки и конверсия. Видите результат гипотезы без установки сложных сервисов.</p>
            </div>
            <div class="card scene" data-tilt>
              <span class="glow"></span>
              <div class="card__icon">🔌</div>
              <h3>Интеграции</h3>
              <p>Все заявки мгновенно уходят в ваш Telegram или CRM. Webhook в Notion, Trello, Zapier — без кода.</p>
            </div>
            <div class="card scene" data-tilt>
              <span class="glow"></span>
              <div class="card__icon">📋</div>
              <h3>Готовые шаблоны</h3>
              <p>Чек-листы и шаблоны для питчей, писем и опросов. Не нужно выдумывать с нуля, просто адаптируйте под свой продукт.</p>
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
                <li>1 лендинг по готовому шаблону</li>
                <li>Базовая форма заявки на e-mail</li>
                <li>Ограниченная кастомизация</li>
                <li>Без аналитики</li>
                <li>Лого TechMVP обязательно</li>
              </ul>
              <a href="#signup" class="button button--secondary">Начать бесплатно</a>
            </div>
            <div class="plan plan--pro scene" data-tilt>
              <span class="glow"></span>
              <div class="badge">Рекомендуем</div>
              <h3>Профессиональный</h3>
              <p class="plan__subtitle">Для хакатонов, пет-проектов, стартапов на ранней стадии и студенческих команд</p>
              <div class="price">490₽ <span>/ месяц</span></div>
              <ul class="features">
                <li>До 10 проектов в одном аккаунте</li>
                <li>10+ готовых нишевых шаблонов (SaaS, AI, маркетплейс, бронирования и др.)</li>
                <li>Встроенная аналитика: показы, клики, заявки, конверсия</li>
                <li>Интеграции: Telegram, кастомный e-mail, Webhook</li>
                <li>Шаблоны писем, опросников и pitch deck</li>
                <li>Кастомные домены/поддомены</li>
                <li>Удаление логотипа TechMVP</li>
                <li>Доступ к гайдам и приоритетной поддержке</li>
              </ul>
              <?php if (!empty($_SESSION['user_id'])): ?>
              <a href="<?= DASHBOARD_PATH ?>payment.php" class="button button--primary">Выбрать Профессиональный</a>
              <?php else: ?>
              <a href="<?= AUTH_PATH ?>register.php" class="button button--primary">Выбрать Профессиональный</a>
              <?php endif; ?>
              <p class="plan__note">Всего 490 ₽ — дешевле одной доставки, но с шансом запустить свой продукт</p>
              <button type="button" class="button button--ghost" style="width: 100%; margin-top: 12px;" onclick="openProModal()">Подробнее о функциях →</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Why 490 is worth it -->
      <section class="section" id="why-worth" aria-label="Почему это выгодно">
        <div class="container">
          <h2>Почему 490 ₽ — выгодно?</h2>
          <p class="section__lead">490 ₽ — это не «ещё один сервис», а быстрый способ проверить гипотезу за выходные: готовые шаблоны, аналитика, интеграции и минимальный геморрой.</p>
          <div class="comparison">
            <div class="comparison__item">
              <h3>Сделать самому</h3>
              <ul class="features features--negative">
                <li>Верстальщик: 5 000–15 000 ₽</li>
                <li>Неделя на дизайн и разработку</li>
                <li>Настройка аналитики отдельно</li>
                <li>Интеграции требуют времени и знаний</li>
                <li>Риск потерять фокус на продукте</li>
              </ul>
            </div>
            <div class="comparison__item comparison__item--highlight">
              <h3>TechMVP Pro</h3>
              <ul class="features">
                <li>Всё уже собрано: лендинг, форма, аналитика, интеграции</li>
                <li>Запуск за вечер вместо недели</li>
                <li>Готовые шаблоны под популярные ниши</li>
                <li>Встроенная аналитика из коробки</li>
                <li>Фокус на проверке гипотезы, а не на технике</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- Pro Features Modal -->
      <div id="proModal" class="modal" style="display: none;">
        <div class="modal__overlay" onclick="closeProModal()"></div>
        <div class="modal__content">
          <button class="modal__close" onclick="closeProModal()" aria-label="Закрыть">×</button>
          <h2>Что входит в Профессиональный тариф за 490 ₽/мес</h2>
          <p class="section__lead">TechMVP — сервис для проверки гипотез. За 490 ₽ в месяц вы получаете полный набор инструментов для запуска MVP.</p>
          
          <div class="modal__features">
            <div class="modal__feature">
              <div class="modal__feature-icon">🚀</div>
              <div>
                <h3>1. Мультипроекты и гибкость</h3>
                <p>Можно параллельно тестировать несколько идей, выглядеть «как взрослые».</p>
                <ul class="features">
                  <li>До 10 проектов (лендингов) в одном аккаунте</li>
                  <li>Поддомены вида idea.techmvp.site</li>
                  <li>Дополнительные секции и раскладки (кейсы, FAQ, сравнение тарифов, формы)</li>
                  <li>Удаление логотипа TechMVP (white label)</li>
                </ul>
              </div>
            </div>

            <div class="modal__feature">
              <div class="modal__feature-icon">📚</div>
              <div>
                <h3>2. Библиотека готовых MVP-шаблонов</h3>
                <p>Экономит часы на структуре и формулировках, особенно новичкам. Каждый шаблон: уже продуманная структура блоков + демо-тексты, которые можно переписать.</p>
                <ul class="features">
                  <li>SaaS-сервис</li>
                  <li>AI-сервис</li>
                  <li>Образовательный продукт</li>
                  <li>Мобильное приложение</li>
                  <li>Консалтинг / агентство</li>
                  <li>Simple marketplace</li>
                  <li>Сервис бронирований</li>
                </ul>
              </div>
            </div>

            <div class="modal__feature">
              <div class="modal__feature-icon">📊</div>
              <div>
                <h3>3. Встроенная аналитика</h3>
                <p>Пользователь видит, «стреляет гипотеза или нет», без Google Analytics и плясок.</p>
                <ul class="features">
                  <li>Количество визитов</li>
                  <li>Клики по кнопкам (CTA)</li>
                  <li>Отправленные заявки</li>
                  <li>Базовая конверсия</li>
                  <li>Простое разделение источников (по UTM: social / direct / ads и т.п.)</li>
                  <li>Возможность подключить свои пиксели и счётчики (Яндекс.Метрика, GA4)</li>
                </ul>
              </div>
            </div>

            <div class="modal__feature">
              <div class="modal__feature-icon">🔌</div>
              <div>
                <h3>4. Интеграции для реальной работы</h3>
                <p>Не просто «форма», а нормальный рабочий поток заявок.</p>
                <ul class="features">
                  <li>Отправка заявок в Telegram (бот/чат)</li>
                  <li>Отправка на кастомный e-mail (команда/фаундер)</li>
                  <li>Webhook в Notion/Trello/CRM</li>
                  <li>Подключение своих пикселей и счётчиков</li>
                </ul>
              </div>
            </div>

            <div class="modal__feature">
              <div class="modal__feature-icon">📋</div>
              <div>
                <h3>5. Шаблоны и материалы</h3>
                <p>Помогает не только собрать сайт, но и правильно коммуницировать и собирать обратную связь.</p>
                <ul class="features">
                  <li>Шаблоны писем первым пользователям: «Расскажите, что думаете о нашем MVP»</li>
                  <li>Письмо потенциальному клиенту</li>
                  <li>Письмо ментору/эксперту</li>
                  <li>Шаблон мини-pitch deck (под акселератор / конкурс / инвестора)</li>
                  <li>Шаблон опроса для проверки гипотезы (Google Forms/Typeform)</li>
                </ul>
              </div>
            </div>

            <div class="modal__feature">
              <div class="modal__feature-icon">🤖</div>
              <div>
                <h3>6. AI-ассистент по текстам</h3>
                <p>Снимает страх «я не копирайтер, не знаю, как написать».</p>
                <ul class="features">
                  <li>Подсказки заголовков</li>
                  <li>Варианты УТП по нише</li>
                  <li>Автогенерация черновика текста блоков</li>
                </ul>
              </div>
            </div>

            <div class="modal__feature">
              <div class="modal__feature-icon">💬</div>
              <div>
                <h3>7. Поддержка и комьюнити</h3>
                <p>Ощущение, что человек не один, а с «навигацией».</p>
                <ul class="features">
                  <li>Приоритетные ответы (e-mail/Telegram)</li>
                  <li>Мини-гайды: «Как запустить рекламу на 300–500 ₽ и собрать первые клики»</li>
                  <li>«Как читать конверсию»</li>
                  <li>«Что делать после первых 10 лидов»</li>
                </ul>
              </div>
            </div>
          </div>

          <div style="text-align: center; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--stroke);">
            <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="<?= DASHBOARD_PATH ?>payment.php" class="button button--primary" onclick="closeProModal()">Выбрать Профессиональный</a>
            <?php else: ?>
            <a href="<?= AUTH_PATH ?>register.php" class="button button--primary" onclick="closeProModal()">Выбрать Профессиональный</a>
            <?php endif; ?>
          </div>
        </div>
      </div>

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
              <img src="<?= ASSETS_PATH ?>images/хз.PNG" alt="Анна — Продакт" loading="lazy" />
              <h4>Анна</h4>
              <p class="section__lead">Product</p>
            </div>
            <div class="member">
              <img src="<?= ASSETS_PATH ?>images/Илья.PNG" alt="Илья — Разработчик" loading="lazy" />
              <h4>Илья</h4>
              <p class="section__lead">Developer</p>
            </div>
            <div class="member">
              <img src="<?= ASSETS_PATH ?>images/Мария.PNG" alt="Марина — Дизайнер" loading="lazy" />
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
              <span>Я соглашаюсь с <a href="<?= PAGES_PATH ?>privacy.html">политикой конфиденциальности</a> и <a href="<?= PAGES_PATH ?>terms.html">условиями использования</a>.</span>
            </label>
            <button type="submit" class="button button--primary">Получить доступ</button>
            <p class="form__hint">Отправляя форму, вы соглашаетесь с <a href="<?= PAGES_PATH ?>privacy.html">политикой конфиденциальности</a>.</p>
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
          <a href="<?= PAGES_PATH ?>privacy.html">Политика конфиденциальности</a>
        </div>
      </div>
    </footer>

    <!-- Floating CTA -->
    <a href="https://t.me/yourteam" class="float-cta" target="_blank" rel="noopener" aria-label="Заказать звонок">Заказать звонок</a>

    <div class="cookie-banner" id="cookieBanner" style="position:fixed;left:16px;right:16px;bottom:16px;background:#0f1720;border:1px solid var(--stroke);border-radius:12px;padding:12px 14px;display:none;z-index:200;box-shadow:0 10px 30px rgba(0,0,0,.35)">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;justify-content:space-between">
        <div style="color:var(--muted);max-width:720px">Мы используем файлы cookie и аналитику для улучшения сервиса. Подробнее в <a href="<?= PAGES_PATH ?>privacy.html">политике конфиденциальности</a>.</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="button button--secondary" data-cookie-reject>Отклонить</button>
          <button class="button button--primary" data-cookie-accept>Согласиться</button>
        </div>
      </div>
    </div>

    <script>
      // Define functions immediately to avoid timing issues
      function openProModal() {
        const modal = document.getElementById('proModal');
        if (modal) {
          modal.style.display = 'flex';
          document.body.style.overflow = 'hidden';
        }
      }
      function closeProModal() {
        const modal = document.getElementById('proModal');
        if (modal) {
          modal.style.display = 'none';
          document.body.style.overflow = '';
        }
      }
    </script>
    <script src="script.js"></script>
  </body>
  </html>


