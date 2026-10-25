-- Textes du site modifiables depuis l'administration (un texte par clé et par langue).
-- Une valeur vide ou absente fait retomber le site sur le texte par défaut de app/lang/.
CREATE TABLE IF NOT EXISTS `BT_Setting` (
  `setting_key` varchar(40) NOT NULL,
  `locale` char(2) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`setting_key`, `locale`),
  CONSTRAINT `chk_setting_locale` CHECK (`locale` IN ('en', 'fr', 'es', 'it'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
