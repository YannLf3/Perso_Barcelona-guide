-- Schéma complet de la base BTGUIDE (installation neuve).
-- Pour mettre à jour une base existante, appliquer les migrations de sql/migrations dans l'ordre.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `BT_Tour` (
  `id` int NOT NULL AUTO_INCREMENT,
  `duration` varchar(60) NOT NULL,
  -- Nombre de personnes (capacité du groupe), affiché avec le suffixe "pers."
  `price` decimal(8,2) NOT NULL,
  `group_type` varchar(20) NOT NULL DEFAULT 'small',
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_tour_active` (`is_active`),
  CONSTRAINT `chk_tour_group_type` CHECK (`group_type` IN ('private', 'small', 'school'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `BT_Tour_Translation` (
  `tour_id` int NOT NULL,
  `locale` char(2) NOT NULL,
  `title` varchar(120) NOT NULL,
  `tagline` text NOT NULL,
  `summary` text NOT NULL,
  PRIMARY KEY (`tour_id`, `locale`),
  CONSTRAINT `fk_tour_translation_tour` FOREIGN KEY (`tour_id`) REFERENCES `BT_Tour` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_tour_translation_locale` CHECK (`locale` IN ('en', 'fr', 'es', 'it'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `BT_Message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_message_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `BT_User` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
