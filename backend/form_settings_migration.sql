-- Миграция для настройки формы заявок

-- Добавить настройки формы в таблицу projects
ALTER TABLE projects 
ADD COLUMN form_enabled BOOLEAN NOT NULL DEFAULT TRUE AFTER hide_branding,
ADD COLUMN form_title VARCHAR(200) NULL AFTER form_enabled,
ADD COLUMN form_button_text VARCHAR(100) NOT NULL DEFAULT 'Отправить заявку' AFTER form_title,
ADD COLUMN form_fields JSON NULL AFTER form_button_text;

-- form_fields будет хранить настройки полей в формате:
-- {"name": {"enabled": true, "required": false, "placeholder": "Ваше имя"}, 
--  "email": {"enabled": true, "required": true, "placeholder": "Email"},
--  "phone": {"enabled": true, "required": false, "placeholder": "Телефон"},
--  "message": {"enabled": true, "required": false, "placeholder": "Сообщение"}}

