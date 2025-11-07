-- Миграция для добавления админ-панели
-- Добавить поле is_admin в таблицу users

ALTER TABLE users ADD COLUMN is_admin BOOLEAN NOT NULL DEFAULT FALSE AFTER bio;
ALTER TABLE users ADD INDEX idx_is_admin (is_admin);

-- Создать таблицу для логов действий
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50) NULL,
  entity_id INT UNSIGNED NULL,
  details JSON NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_created (user_id, created_at),
  INDEX idx_action (action),
  INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Расширить таблицу projects для дополнительных настроек
ALTER TABLE projects ADD COLUMN seo_title VARCHAR(255) NULL AFTER description;
ALTER TABLE projects ADD COLUMN seo_description TEXT NULL AFTER seo_title;
ALTER TABLE projects ADD COLUMN custom_css TEXT NULL AFTER settings;
ALTER TABLE projects ADD COLUMN custom_js TEXT NULL AFTER custom_css;
ALTER TABLE projects ADD COLUMN theme VARCHAR(50) NULL DEFAULT 'default' AFTER template_type;
ALTER TABLE projects ADD COLUMN favicon_url VARCHAR(255) NULL AFTER subdomain;

-- Создать таблицу для шаблонов проектов
CREATE TABLE IF NOT EXISTS project_templates (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  category VARCHAR(50) NOT NULL,
  description TEXT NULL,
  preview_image VARCHAR(255) NULL,
  template_data JSON NOT NULL,
  is_pro BOOLEAN NOT NULL DEFAULT FALSE,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_category (category),
  INDEX idx_is_pro (is_pro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

