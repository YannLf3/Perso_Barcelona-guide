-- Rôle des comptes d'administration : 'admin' (accès complet) ou 'editor' (contenu uniquement : visites, photos, messages, textes de contenu).
-- Les comptes existants deviennent 'admin' (valeur par défaut).

ALTER TABLE `BT_User`
  ADD COLUMN `role` varchar(20) NOT NULL DEFAULT 'admin' AFTER `password_hash`,
  ADD CONSTRAINT `chk_user_role` CHECK (`role` IN ('admin', 'editor'));
