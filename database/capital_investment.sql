-- Capital & Investment Tables for FarmApp
-- Run this in phpMyAdmin or MySQL CLI

CREATE TABLE IF NOT EXISTS `capital_entries` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `farm_id`       BIGINT UNSIGNED NOT NULL,
    `entry_date`    DATE NOT NULL,
    `capital_type`  ENUM('owner_equity','retained_earnings','loan_capital','grant','other') NOT NULL DEFAULT 'owner_equity',
    `title`         VARCHAR(255) NOT NULL,
    `description`   TEXT NULL,
    `amount`        DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `source_name`   VARCHAR(255) NULL COMMENT 'Who provided the capital',
    `reference_no`  VARCHAR(100) NULL,
    `notes`         TEXT NULL,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `investments` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `farm_id`           BIGINT UNSIGNED NOT NULL,
    `investment_date`   DATE NOT NULL,
    `investment_type`   ENUM('equipment','infrastructure','land','livestock','technology','other') NOT NULL DEFAULT 'equipment',
    `title`             VARCHAR(255) NOT NULL,
    `description`       TEXT NULL,
    `amount`            DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `expected_return`   DECIMAL(18,2) NULL COMMENT 'Expected return or value',
    `useful_life_years` INT NULL COMMENT 'Depreciation period in years',
    `status`            ENUM('active','disposed','depreciated') NOT NULL DEFAULT 'active',
    `reference_no`      VARCHAR(100) NULL,
    `notes`             TEXT NULL,
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
