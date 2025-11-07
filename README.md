# TechMVP — Платформа для быстрого запуска MVP

SaaS-сервис для создания MVP-лендингов и проверки гипотез. PHP 8+, MySQL, без тяжёлых фреймворков.

## Возможности

- 🚀 Создание MVP-лендингов за минуты
- 📊 Встроенная аналитика (просмотры, клики, лиды)
- 📝 Сбор заявок с интеграциями (Telegram, Email, Webhook)
- 🤖 AI-помощник по текстам (для Pro)
- 📋 Готовые шаблоны под разные ниши
- 💼 Управление несколькими проектами

## Установка

### 1. Клонирование и настройка

```bash
git clone <repository>
cd techmvp
```

### 2. Настройка базы данных

Создайте базу данных MySQL и выполните миграции:

```bash
mysql -u your_user -p your_database < backend/db.sql
mysql -u your_user -p your_database < backend/migration_user_plan.sql
mysql -u your_user -p your_database < backend/migration_projects_new.sql
mysql -u your_user -p your_database < backend/migration_analytics.sql
mysql -u your_user -p your_database < backend/migration_leads_new.sql
```

### 3. Настройка окружения

Скопируйте `.env.example` в `.env` и заполните:

```bash
cp .env.example .env
```

Отредактируйте `.env`:

```env
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
DB_CHARSET=utf8mb4

APP_ENV=production
APP_DEBUG=false
BASE_URL=/

SESSION_LIFETIME=86400
SESSION_SECURE=false
SESSION_HTTPONLY=true
```

### 4. Настройка веб-сервера

#### Apache

Убедитесь, что включён `mod_rewrite`. `.htaccess` уже настроен.

#### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location /p/ {
    rewrite ^/p/(.+)$ /p.php?slug=$1 last;
}
```

### 5. Права доступа

```bash
chmod 755 backend/
chmod 644 .env
```

## Структура проекта

```
.
├── auth/              # Авторизация (login, register, etc.)
├── dashboard/         # Личный кабинет
├── projects/          # Управление проектами
├── backend/           # Backend логика и конфигурация
├── api/               # API endpoints
├── assets/            # Статические файлы
└── index.php          # Главная страница
```

## Тарифы

### Free
- 1 проект
- Базовый шаблон
- Базовая аналитика (только лиды)

### Pro (490 ₽/мес)
- До 10 проектов
- 10+ нишевых шаблонов
- Полная аналитика
- Интеграции (Telegram, Email, Webhook)
- AI-помощник
- White label

## API Endpoints

### Аналитика
- `POST /backend/track.php` — трекинг событий (view, click, lead)

### Лиды
- `POST /backend/lead_submit.php` — отправка заявки

### AI
- `POST /backend/ai_suggest.php` — генерация текстов (только Pro)

## Безопасность

- ✅ CSRF защита на всех формах
- ✅ Подготовленные SQL запросы (PDO)
- ✅ Экранирование вывода (htmlspecialchars)
- ✅ Хеширование паролей (password_hash)
- ✅ Безопасные сессии (httponly, secure)
- ✅ Конфигурация через .env (не в webroot)

## Разработка

### Добавление нового шаблона

1. Создайте шаблон в `projects/view.php` (по типу `template`)
2. Добавьте в список шаблонов в `projects/project_new.php`

### Интеграция AI API

Настройте в `.env`:
```env
AI_API_KEY=your_api_key
AI_API_URL=https://api.openai.com/v1/chat/completions
```

И обновите `backend/ai_helper.php` для работы с вашим API.

## Лицензия

Proprietary

## Поддержка

Для вопросов и предложений создавайте issues в репозитории.

