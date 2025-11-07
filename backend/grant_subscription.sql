-- SQL скрипт для выдачи подписки пользователю
-- Замените 'user@example.com' на email пользователя

-- Вариант 1: Выдать Pro подписку на 1 месяц
INSERT INTO subscriptions (user_id, plan_type, status, expires_at)
VALUES (
  (SELECT id FROM users WHERE email = 'user@example.com'),
  'pro',
  'active',
  DATE_ADD(NOW(), INTERVAL 1 MONTH)
);

-- Вариант 2: Выдать Pro подписку на 3 месяца
INSERT INTO subscriptions (user_id, plan_type, status, expires_at)
VALUES (
  (SELECT id FROM users WHERE email = 'user@example.com'),
  'pro',
  'active',
  DATE_ADD(NOW(), INTERVAL 3 MONTH)
);

-- Вариант 3: Выдать Pro подписку на 12 месяцев (год)
INSERT INTO subscriptions (user_id, plan_type, status, expires_at)
VALUES (
  (SELECT id FROM users WHERE email = 'user@example.com'),
  'pro',
  'active',
  DATE_ADD(NOW(), INTERVAL 12 MONTH)
);

-- Вариант 4: Выдать подписку по ID пользователя (если знаете ID)
INSERT INTO subscriptions (user_id, plan_type, status, expires_at)
VALUES (
  1,  -- замените на ID пользователя
  'pro',
  'active',
  DATE_ADD(NOW(), INTERVAL 1 MONTH)
);

-- Вариант 5: Деактивировать старую подписку и создать новую
-- Сначала деактивируем старую
UPDATE subscriptions 
SET status = 'cancelled' 
WHERE user_id = (SELECT id FROM users WHERE email = 'user@example.com') 
  AND status = 'active';

-- Затем создаем новую
INSERT INTO subscriptions (user_id, plan_type, status, expires_at)
VALUES (
  (SELECT id FROM users WHERE email = 'user@example.com'),
  'pro',
  'active',
  DATE_ADD(NOW(), INTERVAL 1 MONTH)
);

