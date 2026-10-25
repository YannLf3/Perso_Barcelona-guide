-- Tous les textes du dictionnaire app/lang deviennent modifiables : la clé du réglage est la clé du dictionnaire.
-- Les deux anciennes clés sont renommées vers leur clé de dictionnaire.
ALTER TABLE `BT_Setting` MODIFY `setting_key` varchar(80) NOT NULL;
UPDATE `BT_Setting` SET `setting_key` = 'intro.text' WHERE `setting_key` = 'intro_text';
UPDATE `BT_Setting` SET `setting_key` = 'guide.desc' WHERE `setting_key` = 'guide_text';
