# Структура проекта TechMVP

## Организация файлов

```
/
├── index.php              # Главная страница
├── styles.css             # Основные стили
├── script.js              # JavaScript функционал
├── .htaccess              # Настройки Apache
├── robots.txt             # Правила для поисковых роботов
├── sitemap.xml            # Карта сайта
│
├── assets/                # Статические ресурсы
│   ├── images/            # Изображения
│   └── videos/            # Видео
│       └── Video.mp4
│
├── auth/                  # Аутентификация
│   ├── login.php          # Вход
│   ├── register.php       # Регистрация
│   ├── logout.php         # Выход
│   ├── forgot_password.php # Восстановление пароля
│   ├── reset_password.php  # Сброс пароля
│   └── change_password.php # Смена пароля
│
├── dashboard/             # Личный кабинет
│   ├── profile.php        # Профиль пользователя
│   ├── payment.php        # Страница оплаты
│   └── subscription_activate.php # Активация подписки
│
├── projects/              # Управление проектами
│   ├── create.php         # Создание проекта
│   ├── edit.php           # Редактирование проекта
│   └── view.php           # Публичный лендинг проекта
│
├── api/                   # API эндпоинты
│   ├── track.php          # Отслеживание кликов
│   └── export.php         # Экспорт заявок в CSV
│
├── backend/                # Backend логика
│   ├── config.php         # Конфигурация (БД, функции)
│   ├── db.sql             # Схема базы данных
│   ├── check_expired_subscriptions.php # Cron скрипт
│   └── grant_subscription.sql # SQL скрипты
│
├── pages/                  # Статические страницы
│   ├── privacy.html       # Политика конфиденциальности
│   └── terms.html         # Условия использования
│
└── docs/                   # Документация
    ├── README.md
    ├── DEPLOYMENT.md
    └── TODO_FEATURES.md
```

## Пути в коде

Все пути используют константы из `backend/config.php`:

- `BASE_PATH` - корневой путь (`/`)
- `AUTH_PATH` - путь к аутентификации (`/auth/`)
- `DASHBOARD_PATH` - путь к личному кабинету (`/dashboard/`)
- `PROJECTS_PATH` - путь к проектам (`/projects/`)
- `API_PATH` - путь к API (`/api/`)
- `PAGES_PATH` - путь к статическим страницам (`/pages/`)
- `ASSETS_PATH` - путь к ресурсам (`/assets/`)

## Примеры использования

```php
// В PHP файлах
require __DIR__ . '/../backend/config.php';
<a href="<?= AUTH_PATH ?>login.php">Войти</a>
<a href="<?= DASHBOARD_PATH ?>profile.php">Профиль</a>
<a href="<?= PROJECTS_PATH ?>create.php">Создать проект</a>
```

```html
<!-- В HTML файлах -->
<a href="auth/login.php">Войти</a>
<a href="pages/privacy.html">Политика</a>
<img src="assets/images/photo.png" alt="Photo">
```

## Обработка 404 ошибок

Файл `.htaccess` настроен на редирект всех 404 ошибок на `index.php`:

```apache
ErrorDocument 404 /index.php
```

Это гарантирует, что все несуществующие пути будут обработаны главной страницей.

