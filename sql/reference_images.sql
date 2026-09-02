-- ============================================================
--  Référencer les images locales (dossier /Images du projet)
--  À exécuter dans phpMyAdmin sur la base barcelonaguide
-- ============================================================

-- 1) Colonne image pour les visites (si elle n'existe pas encore)
ALTER TABLE BT_Tour
  ADD COLUMN image_url VARCHAR(255) NULL DEFAULT NULL AFTER summary;

-- Si phpMyAdmin affiche une erreur « colonne déjà existante », ignorez la ligne ci-dessus.

-- ============================================================
--  BT_TOUR — noms de fichiers uniquement (dossier Images/)
--  Correspondance (ordre d'insertion du fichier BARCELONA_GUIDE_data.sql) :
--    id 1 → Sagrada Familia Tour
--    id 2 → Tour Casas Gaudí
--    id 3 → Picasso Museum Tour
--    id 4 → Picasso Museum & The Spanish Village
--    id 5 → Walking Tour Gothic Quarter (coming soon)
--    id 6 → Walking Tour Picasso & Museum (coming soon)
-- ============================================================

UPDATE BT_Tour SET image_url = 'asset17.jpg' WHERE id = 1;
UPDATE BT_Tour SET image_url = 'asset18.jpg' WHERE id = 2;
UPDATE BT_Tour SET image_url = 'asset19.jpg' WHERE id = 3;
UPDATE BT_Tour SET image_url = 'asset20.jpg' WHERE id = 4;
UPDATE BT_Tour SET image_url = 'asset21.jpg' WHERE id = 5;
UPDATE BT_Tour SET image_url = 'asset22.jpg' WHERE id = 6;

-- Variante par titre si vos identifiants ne sont pas 1..6 :
-- UPDATE BT_Tour SET image_url = 'asset17.jpg' WHERE title LIKE '%Sagrada Familia%';
-- UPDATE BT_Tour SET image_url = 'asset18.jpg' WHERE title LIKE '%Casas Gaud%';
-- UPDATE BT_Tour SET image_url = 'asset19.jpg' WHERE title LIKE '%Picasso Museum Tour%';
-- UPDATE BT_Tour SET image_url = 'asset20.jpg' WHERE title LIKE '%Spanish Village%';
-- UPDATE BT_Tour SET image_url = 'asset21.jpg' WHERE title LIKE '%Gothic Quarter%';
-- UPDATE BT_Tour SET image_url = 'asset22.jpg' WHERE title LIKE '%Walking Tour Picasso%';

-- ============================================================
--  BT_MONUMENT — remplacer les liens Unsplash
--    id 1 → Sagrada Familia
--    id 2 → Park Güell
--    id 3 → Casa Batlló
--    id 4 → Barri Gòtic
--    id 5 → Casa Vicens
--    id 6 → La Pedrera
--    id 7 → Museu Picasso
--    id 8 → Pueblo Español
-- ============================================================

UPDATE BT_Monument SET image_url = 'asset1.jpg'  WHERE id = 1;
UPDATE BT_Monument SET image_url = 'asset2.jpg'  WHERE id = 2;
UPDATE BT_Monument SET image_url = 'asset3.jpg'  WHERE id = 3;
UPDATE BT_Monument SET image_url = 'asset4.jpg'  WHERE id = 4;
UPDATE BT_Monument SET image_url = 'asset5.jpg'  WHERE id = 5;
UPDATE BT_Monument SET image_url = 'asset6.jpg'  WHERE id = 6;
UPDATE BT_Monument SET image_url = 'asset7.jpg'  WHERE id = 7;
UPDATE BT_Monument SET image_url = 'asset8.jpg'  WHERE id = 8;
