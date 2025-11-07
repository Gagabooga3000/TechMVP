-- Добавление полей для проектов (если их еще нет)

-- Проверка и добавление полей для SEO и кастомизации
ALTER TABLE projects 
ADD COLUMN IF NOT EXISTS seo_title VARCHAR(255) NULL AFTER domain,
ADD COLUMN IF NOT EXISTS seo_description TEXT NULL AFTER seo_title,
ADD COLUMN IF NOT EXISTS theme VARCHAR(50) NOT NULL DEFAULT 'default' AFTER seo_description,
ADD COLUMN IF NOT EXISTS favicon_url VARCHAR(255) NULL AFTER theme,
ADD COLUMN IF NOT EXISTS custom_css TEXT NULL AFTER favicon_url,
ADD COLUMN IF NOT EXISTS custom_js TEXT NULL AFTER custom_css,
ADD COLUMN IF NOT EXISTS hide_branding BOOLEAN NOT NULL DEFAULT FALSE AFTER custom_js;

