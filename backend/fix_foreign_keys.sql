-- Исправление внешних ключей для безопасного удаления проектов
-- Выполните этот скрипт, если при удалении проекта возникают ошибки

-- Удалить старые внешние ключи (если они есть)
SET FOREIGN_KEY_CHECKS = 0;

-- Пересоздать внешние ключи с ON DELETE CASCADE

-- 1. project_integrations
ALTER TABLE project_integrations 
DROP FOREIGN KEY IF EXISTS project_integrations_ibfk_1;

ALTER TABLE project_integrations 
ADD CONSTRAINT project_integrations_ibfk_1 
FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE;

-- 2. leads
ALTER TABLE leads 
DROP FOREIGN KEY IF EXISTS leads_ibfk_1;

ALTER TABLE leads 
ADD CONSTRAINT leads_ibfk_1 
FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE;

-- 3. project_analytics (если таблица существует)
ALTER TABLE project_analytics 
DROP FOREIGN KEY IF EXISTS project_analytics_ibfk_1;

ALTER TABLE project_analytics 
ADD CONSTRAINT project_analytics_ibfk_1 
FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE;

-- 4. project_utm_stats (если таблица существует)
ALTER TABLE project_utm_stats 
DROP FOREIGN KEY IF EXISTS project_utm_stats_ibfk_1;

ALTER TABLE project_utm_stats 
ADD CONSTRAINT project_utm_stats_ibfk_1 
FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

