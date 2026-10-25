-- Apparence du site public modifiable depuis l'administration.
-- Chaque publication ajoute une ligne : la plus récente est l'apparence en ligne, les autres forment l'historique.
-- Restaurer une ancienne version ajoute une copie, aucune ligne n'est jamais modifiée ni supprimée.
CREATE TABLE IF NOT EXISTS `BT_Design_Revision` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(120) NOT NULL,
  `data` json NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
