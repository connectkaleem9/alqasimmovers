-- Al Qasim Movers — lead storage (Hostinger MySQL)
-- Import once via hPanel → Databases → phpMyAdmin → Import.
-- Character set: utf8mb4 so Arabic names and messages are stored correctly.

CREATE TABLE IF NOT EXISTS `leads` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lang`          CHAR(2)      NOT NULL DEFAULT 'en',
  `source_page`   VARCHAR(191) NOT NULL DEFAULT '',
  `name`          VARCHAR(80)  NOT NULL,
  `phone`         VARCHAR(20)  NOT NULL,
  `email`         VARCHAR(120)     NULL,
  `moving_from`   VARCHAR(80)      NULL,
  `moving_to`     VARCHAR(80)      NULL,
  `property_type` VARCHAR(20)      NULL,
  `moving_date`   DATE             NULL,
  `services`      VARCHAR(255) NOT NULL DEFAULT '',
  `message`       TEXT             NULL,
  `ip_hash`       CHAR(64)     NOT NULL DEFAULT '',   -- hashed, never the raw IP
  `user_agent`    VARCHAR(255) NOT NULL DEFAULT '',
  `status`        ENUM('new','contacted','quoted','won','lost','spam') NOT NULL DEFAULT 'new',
  `notes`         TEXT             NULL,              -- for the owner's own follow-up notes
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`),
  KEY `idx_ip_recent` (`ip_hash`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: a view of this month's leads, handy in phpMyAdmin.
CREATE OR REPLACE VIEW `leads_this_month` AS
SELECT `id`, `created_at`, `lang`, `name`, `phone`, `property_type`, `moving_date`, `services`, `status`
FROM `leads`
WHERE `created_at` >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
ORDER BY `created_at` DESC;
