-- Compte d'administration. Le mot de passe est haché par PHP : créer le compte avec bin/create-admin.php.

CREATE TABLE IF NOT EXISTS `BT_User` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
