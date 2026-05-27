-- ============================================================
--  Base de données : portfolio_contact
--  À importer dans phpMyAdmin via Importer > Choisir fichier
-- ============================================================

CREATE DATABASE IF NOT EXISTS `portfolio_contact`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `portfolio_contact`;

-- Table principale des messages de contact
CREATE TABLE IF NOT EXISTS `messages` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `nom`        VARCHAR(100) NOT NULL,
    `prenom`     VARCHAR(100) DEFAULT NULL,
    `email`      VARCHAR(255) NOT NULL,
    `sujet`      VARCHAR(100) NOT NULL,
    `message`    TEXT         NOT NULL,
    `ip`         VARCHAR(45)  DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `lu`         TINYINT(1)   NOT NULL DEFAULT 0,
    `date_envoi` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
