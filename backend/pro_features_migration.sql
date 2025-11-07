-- Миграция для Pro-функций

-- Добавить поля для SEO и кастомизации
-- Выполняйте по одному, если поле уже существует - будет ошибка (можно игнорировать)

ALTER TABLE projects ADD COLUMN seo_title VARCHAR(255) NULL AFTER domain;
ALTER TABLE projects ADD COLUMN seo_description TEXT NULL AFTER seo_title;
ALTER TABLE projects ADD COLUMN theme VARCHAR(50) NOT NULL DEFAULT 'default' AFTER seo_description;
ALTER TABLE projects ADD COLUMN favicon_url VARCHAR(255) NULL AFTER theme;
ALTER TABLE projects ADD COLUMN custom_css TEXT NULL AFTER favicon_url;
ALTER TABLE projects ADD COLUMN custom_js TEXT NULL AFTER custom_css;
ALTER TABLE projects ADD COLUMN hide_branding BOOLEAN NOT NULL DEFAULT FALSE AFTER custom_js;

-- Таблица для пикселей и счетчиков аналитики
CREATE TABLE IF NOT EXISTS project_analytics (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id INT UNSIGNED NOT NULL,
  analytics_type ENUM('google_analytics', 'yandex_metrika', 'facebook_pixel', 'custom') NOT NULL,
  tracking_id VARCHAR(255) NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Расширить аналитику для UTM
ALTER TABLE projects ADD COLUMN analytics_utm_data JSON NULL AFTER analytics_leads;

-- Таблица для сохранения UTM статистики
CREATE TABLE IF NOT EXISTS project_utm_stats (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id INT UNSIGNED NOT NULL,
  utm_source VARCHAR(100) NULL,
  utm_medium VARCHAR(100) NULL,
  utm_campaign VARCHAR(100) NULL,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  clicks INT UNSIGNED NOT NULL DEFAULT 0,
  leads INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  UNIQUE KEY unique_utm (project_id, utm_source, utm_medium, utm_campaign),
  INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

