-- Données de démonstration : contenu des visites.
-- Aucune donnée de visiteur réel. Un message contenant du HTML sert de cas de non-régression pour l'échappement.

SET NAMES utf8mb4;

INSERT INTO `BT_Tour` (`id`, `duration`, `price`, `group_type`, `image_url`, `is_active`) VALUES
(1, '1h30', 35.00, 'small', 'asset17.webp', 1),
(2, '4h', 55.00, 'small', 'asset18.webp', 1),
(3, '1h30', 35.00, 'small', 'asset19.webp', 1),
(4, '3h – 3h30', 49.00, 'small', 'asset20.webp', 1),
(5, '2h', 25.00, 'small', 'asset21.webp', 0),
(6, '3h', 45.00, 'small', 'asset22.webp', 0);

INSERT INTO `BT_Tour_Translation` (`tour_id`, `locale`, `title`, `tagline`, `summary`) VALUES
(1, 'en', 'Sagrada Familia Tour', 'The masterpiece of Antoni Gaudí', 'La Sagrada Familia is the sum of all the projects Gaudí worked on — he used his previous buildings as a testing ground for his masterwork. During this tour you will discover how he turned stone into Nature through his iconic organic architecture, explore the religious and cultural symbols hidden in every corner of the temple, and understand the techniques behind one of the most breathtaking buildings ever built. Whatever your style, Gaudí\'s art never disappoints.'),
(2, 'en', 'Tour Casas Gaudí', 'A journey through Gaudí\'s architectural genius', 'A walk through Barcelona\'s bourgeois architecture of the late 19th and early 20th century. We start in the Gracia neighbourhood with Casa Vicens — Gaudí\'s first civil building, a hidden gem far from the city bustle — before heading to the prestigious Passeig de Gràcia to discover Casa Batlló and La Pedrera. You will follow the evolution of Gaudí\'s style and understand how Nature was the driving force behind his dazzling architecture. Individual visits to one or two casas are also possible.'),
(3, 'en', 'Picasso Museum Tour', 'The world of Picasso in Barcelona', 'The Picasso Museum in Barcelona holds an exceptional collection assembled by the artist\'s closest friend and personal secretary, enriched by major donations from Picasso\'s own family. The museum\'s strength lies in its rare display of early and mature works side by side, letting you witness the astonishing transformation of Picasso\'s style. By the end of the tour you will understand his techniques, the artists who influenced him, and why his creative freedom still resonates today.'),
(4, 'en', 'Picasso Museum & The Spanish Village', 'An immersive cultural experience', 'After visiting the Picasso Museum we head up Montjuïc hill for an immersive experience at the Spanish Village — an open-air museum replicating Spain\'s architectural and cultural diversity across 40,000 m². Stroll through an Andalusian neighbourhood full of colour and flamenco rhythm, follow the Camino de Santiago, meet Cervantes around a corner, and watch artisans at work. The tour also includes the on-site art gallery (Picasso, Dalí, Miró…) and an optional hands-on tapas workshop. The views over Barcelona are breathtaking.'),
(5, 'en', 'Walking Tour Gothic Quarter', 'Exploring Barcelona\'s medieval heart', 'An intimate walking tour through the medieval streets and hidden plazas of the Gothic Quarter — Barcelona\'s ancient heart. Coming soon.'),
(6, 'en', 'Walking Tour Picasso & Museum', 'Coming soon', 'A combined walking tour through the Gothic-Born neighbourhood followed by a guided visit to the Picasso Museum. Coming soon.');

INSERT INTO `BT_Message` (`fullname`, `email`, `message`) VALUES
('Test visitor', 'visitor@example.com', '<b>inject<b>');
