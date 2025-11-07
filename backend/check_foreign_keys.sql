-- Проверка внешних ключей для таблицы projects
-- Этот скрипт проверяет, что все связи настроены правильно

-- Таблицы, которые ссылаются на projects:
-- 1. project_integrations - ON DELETE CASCADE ✓
-- 2. leads - ON DELETE CASCADE ✓
-- 3. project_analytics - ON DELETE CASCADE ✓
-- 4. project_utm_stats - ON DELETE CASCADE ✓

-- Проверка существования внешних ключей
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    DELETE_RULE
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE 
    REFERENCED_TABLE_NAME = 'projects'
    AND TABLE_SCHEMA = DATABASE();

-- Если нужно пересоздать внешние ключи с CASCADE:
-- ALTER TABLE project_integrations DROP FOREIGN KEY project_integrations_ibfk_1;
-- ALTER TABLE project_integrations ADD CONSTRAINT project_integrations_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE;

-- ALTER TABLE leads DROP FOREIGN KEY leads_ibfk_1;
-- ALTER TABLE leads ADD CONSTRAINT leads_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE;

-- ALTER TABLE project_analytics DROP FOREIGN KEY project_analytics_ibfk_1;
-- ALTER TABLE project_analytics ADD CONSTRAINT project_analytics_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE;

-- ALTER TABLE project_utm_stats DROP FOREIGN KEY project_utm_stats_ibfk_1;
-- ALTER TABLE project_utm_stats ADD CONSTRAINT project_utm_stats_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE;

