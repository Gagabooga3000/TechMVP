# Руководство по миграции

## Шаг 1: Обновление конфигурации

1. Создайте файл `.env` на основе `.env.example`
2. Заполните параметры подключения к БД
3. Убедитесь, что `backend/load_env.php` загружается в `backend/config.php`

## Шаг 2: Миграция базы данных

Выполните миграции в следующем порядке:

```sql
-- 1. Добавить поля плана пользователя
SOURCE backend/migration_user_plan.sql;

-- 2. Обновить структуру projects
SOURCE backend/migration_projects_new.sql;

-- 3. Создать таблицу аналитики
SOURCE backend/migration_analytics.sql;

-- 4. Обновить таблицу leads (если нужно)
SOURCE backend/migration_leads_new.sql;
```

**Важно:** Если при выполнении миграций возникают ошибки "column already exists", это нормально — просто пропустите эти команды.

## Шаг 3: Обновление существующих данных

```sql
-- Обновить существующих пользователей на free план
UPDATE users SET plan = 'free' WHERE plan IS NULL OR plan = '';

-- Обновить template_type на template в projects
UPDATE projects SET template = template_type WHERE template IS NULL OR template = '' AND template_type IS NOT NULL;
```

## Шаг 4: Проверка

1. Убедитесь, что роутинг работает: `/p/test-slug` должен открывать проект
2. Проверьте создание проекта: `/projects/project_new.php`
3. Проверьте аналитику: события должны записываться в `project_events`
4. Проверьте отправку лидов: форма должна работать через AJAX

## Шаг 5: Настройка подписки Pro

Для выдачи Pro подписки пользователю:

```sql
UPDATE users 
SET plan = 'pro', 
    plan_expires_at = DATE_ADD(NOW(), INTERVAL 1 MONTH)
WHERE id = ?;
```

Или используйте админ-панель: `/admin/users.php`

## Возможные проблемы

### Ошибка "Database configuration is missing"
- Проверьте, что `.env` файл существует и заполнен
- Убедитесь, что `backend/load_env.php` подключён

### Ошибка "Column 'niche' doesn't exist"
- Выполните миграцию `backend/migration_projects_new.sql`
- Или добавьте колонки вручную

### Роутинг `/p/{slug}` не работает
- Проверьте, что `mod_rewrite` включён в Apache
- Проверьте `.htaccess` файл
- Для Nginx настройте конфигурацию (см. README.md)

