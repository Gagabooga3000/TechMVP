# Changelog — Превращение в SaaS

## Выполненные изменения

### 1. Конфиг и безопасность ✅
- ✅ Вынесены параметры БД в `.env` файл
- ✅ Создан `backend/load_env.php` для загрузки переменных окружения
- ✅ Обновлён `backend/config.php` с поддержкой `.env`
- ✅ Настроены безопасные сессии (httponly, secure, samesite)
- ✅ Добавлена проверка наличия конфигурации

### 2. Модель пользователя и тарифы ✅
- ✅ Добавлены поля `plan` и `plan_expires_at` в таблицу `users`
- ✅ Созданы константы `PLAN_FREE`, `PLAN_PRO`
- ✅ Реализованы функции:
  - `get_user_plan()` — получение текущего плана пользователя
  - `user_can_create_project()` — проверка лимита проектов
  - `get_user_project_count()` — подсчёт проектов
- ✅ Автоматическая проверка истечения плана

### 3. Сущность Project ✅
- ✅ Обновлена таблица `projects` с полями:
  - `niche` — ниша проекта
  - `target_audience` — целевая аудитория
  - `goal` — цель проекта
  - `template` — шаблон (обновлён с `template_type`)
- ✅ Создан `dashboard/dashboard.php` — главная страница после входа
- ✅ Создан `projects/project_new.php` — форма создания проекта
- ✅ Обновлён `projects/project_edit.php` — поддержка новых полей

### 4. Публичные лендинги ✅
- ✅ Обновлён `projects/view.php` — публичный просмотр проектов
- ✅ Создан `p.php` — роутер для `/p/{slug}`
- ✅ Настроен `.htaccess` для роутинга
- ✅ Параметризуемый шаблон лендинга

### 5. Аналитика ✅
- ✅ Создана таблица `project_events` для событий
- ✅ Создан `backend/track.php` — endpoint для трекинга
- ✅ Добавлен JavaScript трекинг в `projects/view.php`:
  - Автоматический трекинг просмотров при загрузке
  - Трекинг кликов по CTA кнопкам
  - Трекинг лидов при отправке формы

### 6. Лиды и формы ✅
- ✅ Обновлена таблица `leads` (поле `source`)
- ✅ Создан `backend/lead_submit.php` — обработка заявок
- ✅ Интеграции:
  - Email уведомления
  - Telegram Bot API
  - Webhook (Notion, Trello, CRM)
- ✅ AJAX отправка формы в `projects/view.php`
- ✅ Настройки формы в `projects/edit.php`

### 7. Тарифы и ограничения ✅
- ✅ Логика Free/Pro реализована:
  - Free: 1 проект, базовая аналитика
  - Pro: 10 проектов, полная аналитика, интеграции, AI
- ✅ Проверки лимитов при создании проекта
- ✅ Создан `dashboard/upgrade.php` — страница апгрейда
- ✅ Call-to-action на апгрейд при достижении лимита

### 8. AI-помощник ✅
- ✅ Создан `backend/ai_helper.php` — модуль генерации текстов
- ✅ Создан `backend/ai_suggest.php` — AJAX endpoint
- ✅ Реализованы типы генерации:
  - `headline` — заголовки
  - `usp` — уникальные торговые предложения
  - `description` — описания
  - `cta` — призывы к действию
- ✅ Поддержка внешних API (структура готова)
- ✅ Доступ только для Pro пользователей

### 9. Маркетинговый лендинг ✅
- ✅ `index.php` уже содержит описание возможностей
- ✅ Блоки тарифов с сравнением Free vs Pro
- ✅ CTA на регистрацию и апгрейд

### 10. Чистка и качество ✅
- ✅ Все формы имеют CSRF-токены
- ✅ Все данные экранируются через `htmlspecialchars`
- ✅ Подготовленные SQL запросы (PDO)
- ✅ Обработка ошибок
- ✅ Логирование действий

## Новые файлы

### Backend
- `backend/load_env.php` — загрузка переменных окружения
- `backend/track.php` — трекинг аналитики
- `backend/lead_submit.php` — обработка лидов
- `backend/ai_helper.php` — AI модуль
- `backend/ai_suggest.php` — AI AJAX endpoint

### Dashboard
- `dashboard/dashboard.php` — главная страница дашборда
- `dashboard/upgrade.php` — страница апгрейда

### Projects
- `projects/project_new.php` — создание проекта

### Routing
- `p.php` — роутер для публичных проектов

### Migrations
- `backend/migration_user_plan.sql`
- `backend/migration_projects_new.sql`
- `backend/migration_analytics.sql`
- `backend/migration_leads_new.sql`

### Documentation
- `README.md` — обновлённая документация
- `MIGRATION_GUIDE.md` — руководство по миграции
- `CHANGELOG.md` — этот файл

## Обновлённые файлы

- `backend/config.php` — поддержка `.env`, новые функции
- `projects/edit.php` — поддержка новых полей
- `projects/view.php` — новый трекинг, AJAX форма
- `.htaccess` — роутинг для `/p/{slug}`

## Следующие шаги

1. Выполнить миграции БД (см. `MIGRATION_GUIDE.md`)
2. Настроить `.env` файл
3. Протестировать создание проекта
4. Протестировать публичный просмотр
5. Протестировать аналитику и лиды
6. (Опционально) Настроить AI API в `.env`

## Примечания

- Все миграции безопасны (можно выполнять несколько раз)
- Структура кода позволяет легко расширять функционал
- AI модуль готов к интеграции с внешними API
- Роутинг работает через `.htaccess` (Apache) или конфиг Nginx

