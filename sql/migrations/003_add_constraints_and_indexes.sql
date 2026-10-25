-- Contraintes d'intégrité et index manquants.

DELETE FROM `BT_Tour_Translation` WHERE `tour_id` NOT IN (SELECT `id` FROM `BT_Tour`);

ALTER TABLE `BT_Tour_Translation`
  ADD CONSTRAINT `chk_tour_translation_locale` CHECK (`locale` IN ('en', 'fr', 'es', 'it'));

ALTER TABLE `BT_Tour`
  ADD CONSTRAINT `chk_tour_group_type` CHECK (`group_type` IN ('private', 'small', 'school')),
  ADD KEY `idx_tour_active` (`is_active`);

ALTER TABLE `BT_Message`
  ADD KEY `idx_message_created` (`created_at`);
