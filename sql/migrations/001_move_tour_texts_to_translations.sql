-- Les colonnes title, tagline et summary de BT_Tour doublonnent BT_Tour_Translation.
-- On sauvegarde d'abord leur contenu comme traduction anglaise quand elle manque, puis on les supprime.

INSERT IGNORE INTO `BT_Tour_Translation` (`tour_id`, `locale`, `title`, `tagline`, `summary`)
SELECT `id`, 'en', `title`, `tagline`, `summary` FROM `BT_Tour`;

ALTER TABLE `BT_Tour`
  DROP COLUMN `title`,
  DROP COLUMN `tagline`,
  DROP COLUMN `summary`;
