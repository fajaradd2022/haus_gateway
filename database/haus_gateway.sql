-- ============================================================
--  HAUS Gateway — Database Schema (MySQL / MariaDB)
--  Generated: 2026-05-15
--  Cara import:
--    mysql -u root -p haus_gateway < haus_gateway.sql
--  Atau lewat phpMyAdmin / TablePlus / DBeaver.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- ─── Drop tables (urutan terbalik agar FK tidak konflik) ──────────
DROP TABLE IF EXISTS `contact_phones`;
DROP TABLE IF EXISTS `contact_tag`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `knowledge_bases`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;

-- ─── users ───────────────────────────────────────────────────────
CREATE TABLE `users` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(191)    NOT NULL,
    `email`             VARCHAR(191)    NOT NULL,
    `email_verified_at` TIMESTAMP       NULL DEFAULT NULL,
    `password`          VARCHAR(191)    NOT NULL,
    `role`              VARCHAR(191)    NOT NULL DEFAULT 'agent',
    `is_online`         TINYINT(1)      NOT NULL DEFAULT 0,
    `last_login`        TIMESTAMP       NULL DEFAULT NULL,
    `remember_token`    VARCHAR(100)    NULL DEFAULT NULL,
    `created_at`        TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`        TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── password_reset_tokens ───────────────────────────────────────
CREATE TABLE `password_reset_tokens` (
    `email`      VARCHAR(191) NOT NULL,
    `token`      VARCHAR(191) NOT NULL,
    `created_at` TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── sessions ────────────────────────────────────────────────────
CREATE TABLE `sessions` (
    `id`            VARCHAR(191)    NOT NULL,
    `user_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
    `ip_address`    VARCHAR(45)     NULL DEFAULT NULL,
    `user_agent`    TEXT            NULL DEFAULT NULL,
    `payload`       LONGTEXT        NOT NULL,
    `last_activity` INT             NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index`       (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── cache ───────────────────────────────────────────────────────
CREATE TABLE `cache` (
    `key`        VARCHAR(191) NOT NULL,
    `value`      MEDIUMTEXT   NOT NULL,
    `expiration` BIGINT       NOT NULL,
    PRIMARY KEY (`key`),
    KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
    `key`        VARCHAR(191) NOT NULL,
    `owner`      VARCHAR(191) NOT NULL,
    `expiration` BIGINT       NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── jobs ────────────────────────────────────────────────────────
CREATE TABLE `jobs` (
    `id`           BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `queue`        VARCHAR(191)      NOT NULL,
    `payload`      LONGTEXT          NOT NULL,
    `attempts`     SMALLINT UNSIGNED NOT NULL,
    `reserved_at`  INT UNSIGNED      NULL DEFAULT NULL,
    `available_at` INT UNSIGNED      NOT NULL,
    `created_at`   INT UNSIGNED      NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
    `id`             VARCHAR(191) NOT NULL,
    `name`           VARCHAR(191) NOT NULL,
    `total_jobs`     INT          NOT NULL,
    `pending_jobs`   INT          NOT NULL,
    `failed_jobs`    INT          NOT NULL,
    `failed_job_ids` LONGTEXT     NOT NULL,
    `options`        MEDIUMTEXT   NULL DEFAULT NULL,
    `cancelled_at`   INT          NULL DEFAULT NULL,
    `created_at`     INT          NOT NULL,
    `finished_at`    INT          NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`       VARCHAR(191)    NOT NULL,
    `connection` TEXT            NOT NULL,
    `queue`      TEXT            NOT NULL,
    `payload`    LONGTEXT        NOT NULL,
    `exception`  LONGTEXT        NOT NULL,
    `failed_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
    KEY `failed_jobs_connection_queue_failed_at_index` (`connection`(191), `queue`(191), `failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── customers ───────────────────────────────────────────────────
CREATE TABLE `customers` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(191)    NOT NULL,
    `phone_number`    VARCHAR(20)     NOT NULL,
    `company`         VARCHAR(191)    NULL DEFAULT NULL,
    `email`           VARCHAR(191)    NULL DEFAULT NULL,
    `notes`           TEXT            NULL DEFAULT NULL,
    `avatar_url`      VARCHAR(500)    NULL DEFAULT NULL,
    `is_vip`          TINYINT(1)      NOT NULL DEFAULT 0,
    `is_blocked`      TINYINT(1)      NOT NULL DEFAULT 0,
    `last_contact_at` TIMESTAMP       NULL DEFAULT NULL,
    `created_at`      TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `customers_phone_number_unique` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── tags ────────────────────────────────────────────────────────
CREATE TABLE `tags` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(80)     NOT NULL,
    `color`      VARCHAR(7)      NOT NULL DEFAULT '#667781',
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tags_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── contacts ────────────────────────────────────────────────────
CREATE TABLE `contacts` (
    `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`                    VARCHAR(191)    NOT NULL,
    `phone_number`            VARCHAR(30)     NOT NULL,
    `email`                   VARCHAR(191)    NULL DEFAULT NULL,
    `avatar_url`              VARCHAR(500)    NULL DEFAULT NULL,
    `company`                 VARCHAR(191)    NULL DEFAULT NULL,
    `department`              VARCHAR(100)    NULL DEFAULT NULL,
    `job_title`               VARCHAR(100)    NULL DEFAULT NULL,
    `wa_id`                   VARCHAR(50)     NULL DEFAULT NULL,
    `wa_push_name`            VARCHAR(191)    NULL DEFAULT NULL,
    `source`                  VARCHAR(191)    NOT NULL DEFAULT 'whatsapp',
    `last_seen_at`            TIMESTAMP       NULL DEFAULT NULL,
    `is_wa_verified`          TINYINT(1)      NOT NULL DEFAULT 0,
    `is_vip`                  TINYINT(1)      NOT NULL DEFAULT 0,
    `is_blocked`              TINYINT(1)      NOT NULL DEFAULT 0,
    `sla_override_minutes`    INT             NULL DEFAULT NULL,
    `notes`                   TEXT            NULL DEFAULT NULL,
    `total_tickets`           INT UNSIGNED    NOT NULL DEFAULT 0,
    `open_tickets`            INT UNSIGNED    NOT NULL DEFAULT 0,
    `first_contact_at`        TIMESTAMP       NULL DEFAULT NULL,
    `last_contact_at`         TIMESTAMP       NULL DEFAULT NULL,
    `deleted_at`              TIMESTAMP       NULL DEFAULT NULL,
    `created_at`              TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`              TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `contacts_phone_number_unique` (`phone_number`),
    UNIQUE KEY `contacts_email_unique`        (`email`),
    UNIQUE KEY `contacts_wa_id_unique`        (`wa_id`),
    KEY `contacts_company_index`              (`company`),
    KEY `contacts_source_index`              (`source`),
    KEY `contacts_is_vip_is_blocked_index`   (`is_vip`, `is_blocked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── contact_tag (pivot) ─────────────────────────────────────────
CREATE TABLE `contact_tag` (
    `contact_id` BIGINT UNSIGNED NOT NULL,
    `tag_id`     BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`contact_id`, `tag_id`),
    CONSTRAINT `contact_tag_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `contact_tag_tag_id_foreign`     FOREIGN KEY (`tag_id`)     REFERENCES `tags`     (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── contact_phones ──────────────────────────────────────────────
CREATE TABLE `contact_phones` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contact_id`   BIGINT UNSIGNED NOT NULL,
    `phone_number` VARCHAR(30)     NOT NULL,
    `label`        VARCHAR(50)     NOT NULL DEFAULT 'other',
    `is_primary`   TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`   TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `contact_phones_contact_phone_unique` (`contact_id`, `phone_number`),
    CONSTRAINT `contact_phones_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── tickets ─────────────────────────────────────────────────────
CREATE TABLE `tickets` (
    `id`                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id`              BIGINT UNSIGNED NOT NULL,
    `contact_id`               BIGINT UNSIGNED NULL DEFAULT NULL,
    `assigned_agent_id`        BIGINT UNSIGNED NULL DEFAULT NULL,
    `subject`                  VARCHAR(191)    NOT NULL,
    `status`                   VARCHAR(191)    NOT NULL DEFAULT 'open',
    `priority`                 VARCHAR(191)    NOT NULL DEFAULT 'medium',
    `channel`                  VARCHAR(191)    NOT NULL DEFAULT 'whatsapp',
    `channel_ref`              VARCHAR(191)    NULL DEFAULT NULL,
    `category`                 VARCHAR(100)    NULL DEFAULT NULL,
    `sla_deadline`             TIMESTAMP       NULL DEFAULT NULL,
    `last_message_at`          TIMESTAMP       NULL DEFAULT NULL,
    `archived_at`              TIMESTAMP       NULL DEFAULT NULL,
    `resolved_at`              TIMESTAMP       NULL DEFAULT NULL,
    `first_response_at`        TIMESTAMP       NULL DEFAULT NULL,
    `response_time_seconds`    INT UNSIGNED    NULL DEFAULT NULL,
    `resolution_time_seconds`  INT UNSIGNED    NULL DEFAULT NULL,
    `created_at`               TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`               TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `tickets_status_priority_index`  (`status`, `priority`),
    KEY `tickets_contact_id_index`       (`contact_id`),
    KEY `tickets_resolved_at_index`      (`resolved_at`),
    KEY `tickets_customer_id_index`      (`customer_id`),
    KEY `tickets_assigned_agent_id_index`(`assigned_agent_id`),
    CONSTRAINT `tickets_customer_id_foreign`       FOREIGN KEY (`customer_id`)       REFERENCES `customers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `tickets_contact_id_foreign`        FOREIGN KEY (`contact_id`)        REFERENCES `contacts`  (`id`) ON DELETE SET NULL,
    CONSTRAINT `tickets_assigned_agent_id_foreign` FOREIGN KEY (`assigned_agent_id`) REFERENCES `users`     (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── messages ────────────────────────────────────────────────────
CREATE TABLE `messages` (
    `id`                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id`              BIGINT UNSIGNED NOT NULL,
    `sender_type`            VARCHAR(191)    NOT NULL,
    `content`                TEXT            NULL DEFAULT NULL,
    `agent_id`               BIGINT UNSIGNED NULL DEFAULT NULL,
    `media_url`              VARCHAR(500)    NULL DEFAULT NULL,
    `media_type`             VARCHAR(50)     NULL DEFAULT NULL,
    `sent_at`                TIMESTAMP       NOT NULL,
    `is_internal_note`       TINYINT(1)      NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `messages_ticket_id_sent_at_index` (`ticket_id`, `sent_at`),
    KEY `messages_agent_id_index`          (`agent_id`),
    CONSTRAINT `messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `messages_agent_id_foreign`  FOREIGN KEY (`agent_id`)  REFERENCES `users`   (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── knowledge_bases ─────────────────────────────────────────────
CREATE TABLE `knowledge_bases` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`          VARCHAR(191)    NOT NULL,
    `content`        LONGTEXT        NOT NULL,
    `source`         VARCHAR(191)    NULL DEFAULT NULL,
    `last_synced_at` TIMESTAMP       NULL DEFAULT NULL,
    `created_at`     TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── audit_logs ──────────────────────────────────────────────────
CREATE TABLE `audit_logs` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NULL DEFAULT NULL,
    `action`      VARCHAR(100)    NOT NULL,
    `description` TEXT            NOT NULL,
    `context`     JSON            NULL DEFAULT NULL,
    `created_at`  TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `audit_logs_action_created_at_index` (`action`, `created_at`),
    KEY `audit_logs_user_id_index`           (`user_id`),
    CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ─── Akun admin default ───────────────────────────────────────────
-- Password: Admin@1234  (bcrypt — ganti setelah login pertama!)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_online`, `created_at`, `updated_at`)
VALUES (
    'Administrator',
    'admin@hausgateway.com',
    '$2y$12$ddTl2Ave/ecPqzHVknDQButZrjQvtwm9IZwwRnUwIKW.XDS5d7H4i',
    'admin',
    0,
    NOW(),
    NOW()
);
