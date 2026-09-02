-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 03 juin 2026 à 17:23
-- Version du serveur : 8.0.45-0ubuntu0.24.04.1
-- Version de PHP : 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `BTGUIDE`
--

-- --------------------------------------------------------

--
-- Structure de la table `BT_Message`
--

CREATE TABLE `BT_Message` (
  `id` int NOT NULL,
  `fullname` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `BT_Message`
--

INSERT INTO `BT_Message` (`id`, `fullname`, `email`, `message`, `created_at`) VALUES
(1, 'Yann Lfc', 'yann.lfhc@gmail.com', 'dezEFDzef', '2026-06-01 08:57:36'),
(2, 'Yann Lfc', 'yann.lfhc@gmail.com', 'test', '2026-06-01 18:51:47'),
(3, 'Yann Lfc', 'yann.lfhc@gmail.com', 'yhgè!h', '2026-06-01 19:46:51'),
(4, 'sfs', 'dfaz@dhuf.com', '<b>inject<b>', '2026-06-02 15:26:40');

-- --------------------------------------------------------

--
-- Structure de la table `BT_Monument`
--

CREATE TABLE `BT_Monument` (
  `id` int NOT NULL,
  `name` varchar(120) NOT NULL,
  `district` varchar(120) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `BT_Monument`
--

INSERT INTO `BT_Monument` (`id`, `name`, `district`, `description`, `image_url`) VALUES
(1, 'Sagrada Familia', 'Eixample', 'Iconic basilica designed by Antoni Gaudí, still under construction and the symbol of Barcelona. Gaudí used Nature as his master, turning stone into organic forms of extraordinary beauty. The temple blends mystical interior light with a richly detailed exterior drawn from Romanesque, Gothic, and his own organic tradition.', 'asset1.webp'),
(2, 'Park Güell', 'Gracia', 'A colorful public park with unique mosaic art, whimsical architecture, and sweeping city views. Designed by Gaudí, the park showcases his mastery of trencadís mosaics and his deep connection with natural forms.', 'asset2.webp'),
(3, 'Casa Batlló', 'Eixample', 'One of Gaudí\'s most celebrated buildings on Passeig de Gràcia, featuring an undulating façade, dragon-scale roof, and interiors that evoke an underwater world. A masterpiece of Catalan Modernisme.', 'asset3.webp'),
(4, 'Barri Gòtic', 'Ciutat Vella', 'Barcelona\'s ancient historic core with narrow medieval streets, Roman remains, and a vibrant local atmosphere. The Gothic Quarter is a layered palimpsest of two thousand years of urban history.', 'asset4.webp'),
(5, 'Casa Vicens', 'Gracia', 'Gaudí\'s very first civil building in Barcelona, tucked away in a quiet street of the Gracia neighbourhood. An eclectic gem blending Moorish tilework, Gothic structures, and early organic experimentation — a must-see introduction to Gaudí\'s evolution before his more famous Passeig de Gràcia works.', 'asset5.webp'),
(6, 'La Pedrera (Casa Milà)', 'Eixample', 'Gaudí\'s last secular work on Passeig de Gràcia, renowned for its undulating stone façade and rooftop of sculpted chimneys that resemble warriors. A UNESCO World Heritage Site and the most radical expression of Gaudí\'s organic architecture.', 'asset6.webp'),
(7, 'Museu Picasso', 'El Born – Ciutat Vella', 'One of the most important collections of Pablo Picasso\'s works in the world, housed in five adjoining medieval palaces in the El Born quarter. The museum is uniquely rich in early and transitional works, allowing visitors to trace the full arc of the artist\'s creative transformation.', 'asset7.webp'),
(8, 'Pueblo Español', 'Montjuïc', 'An open-air museum built for the 1929 International Exposition, replicating the architectural and cultural diversity of Spain across 40,000 m². Visitors can stroll through an Andalusian quarter, walk the Camino de Santiago, watch live artisans, and enjoy a gallery featuring works by Picasso, Dalí, and Miró. Spectacular panoramic views over Barcelona.', 'asset8.webp');

-- --------------------------------------------------------

--
-- Structure de la table `BT_Tour`
--

CREATE TABLE `BT_Tour` (
  `id` int NOT NULL,
  `title` varchar(120) NOT NULL,
  `duration` varchar(60) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `tagline` text NOT NULL,
  `summary` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `BT_Tour`
--

INSERT INTO `BT_Tour` (`id`, `title`, `duration`, `price`, `tagline`, `summary`, `image_url`, `is_active`) VALUES
(1, 'Sagrada Familia Tour', '1h30', 35.00, 'A masterpiece of modernist architecture', 'La Sagrada Familia is the sum of all the projects Gaudí worked on — he used his previous buildings as a testing ground for his masterwork. During this tour you will discover how he turned stone into Nature through his iconic organic architecture, explore the religious and cultural symbols hidden in every corner of the temple, and understand the techniques behind one of the most breathtaking buildings ever built. Whatever your style, Gaudí\'s art never disappoints.', 'asset17.webp', 1),
(2, 'Tour Casas Gaudí', '4h', 55.00, 'A journey through Gaudí\'s architectural genius', 'A walk through Barcelona\'s bourgeois architecture of the late 19th and early 20th century. We start in the Gracia neighbourhood with Casa Vicens — Gaudí\'s first civil building, a hidden gem far from the city bustle — before heading to the prestigious Passeig de Gràcia to discover Casa Batlló and La Pedrera. You will follow the evolution of Gaudí\'s style and understand how Nature was the driving force behind his dazzling architecture. Individual visits to one or two casas are also possible.', 'asset18.webp', 1),
(3, 'Picasso Museum Tour', '1h30', 35.00, 'Discovering the genius of Picasso', 'The Picasso Museum in Barcelona holds an exceptional collection assembled by the artist\'s closest friend and personal secretary, enriched by major donations from Picasso\'s own family. The museum\'s strength lies in its rare display of early and mature works side by side, letting you witness the astonishing transformation of Picasso\'s style. By the end of the tour you will understand his techniques, the artists who influenced him, and why his creative freedom still resonates today.', 'asset19.webp', 1),
(4, 'Picasso Museum & The Spanish Village', '3h – 3h30', 49.00, 'A cultural adventure through Catalonia', 'After visiting the Picasso Museum we head up Montjuïc hill for an immersive experience at the Spanish Village — an open-air museum replicating Spain\'s architectural and cultural diversity across 40,000 m². Stroll through an Andalusian neighbourhood full of colour and flamenco rhythm, follow the Camino de Santiago, meet Cervantes around a corner, and watch artisans at work. The tour also includes the on-site art gallery (Picasso, Dalí, Miró…) and an optional hands-on tapas workshop. The views over Barcelona are breathtaking.', 'asset20.webp', 1),
(5, 'Walking Tour Gothic Quarter', '2h', 25.00, 'Exploring Barcelona\'s medieval heart', 'An intimate walking tour through the medieval streets and hidden plazas of the Gothic Quarter — Barcelona\'s ancient heart. Coming soon.', 'asset21.webp', 0),
(6, 'Walking Tour Picasso & Museum', '3h', 45.00, 'A combined walking tour through the Gothic-Born neighbourhood followed by a guided visit to the Picasso Museum. Coming soon.', 'asset22.webp', 0);

-- --------------------------------------------------------

--
-- Structure de la table `BT_Tour_Translation`
--

CREATE TABLE `BT_Tour_Translation` (
  `tour_id` int NOT NULL,
  `locale` char(2) NOT NULL,
  `title` varchar(120) NOT NULL,
  `tagline` text NOT NULL,
  `summary` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `BT_Tour_Translation`
--


INSERT INTO `BT_Tour_Translation` (`tour_id`, `locale`, `title`, `tagline`, `summary`) VALUES
(1, 'en', 'Sagrada Familia Tour', 'The masterpiece of Antoni Gaudí', 'La Sagrada Familia is the sum of all the projects Gaudí worked on — he used his previous buildings as a testing ground for his masterwork. During this tour you will discover how he turned stone into Nature through his iconic organic architecture, explore the religious and cultural symbols hidden in every corner of the temple, and understand the techniques behind one of the most breathtaking buildings ever built. Whatever your style, Gaudí\'s art never disappoints.'),
(2, 'en', 'Tour Casas Gaudí', 'A walk through Barcelona\'s bourgeois architecture of the late 19th and early 20th century. We start in the Gracia neighbourhood with Casa Vicens — Gaudí\'s first civil building, a hidden gem far from the city bustle — before heading to the prestigious Passeig de Gràcia to discover Casa Batlló and La Pedrera. You will follow the evolution of Gaudí\'s style and understand how Nature was the driving force behind his dazzling architecture. Individual visits to one or two casas are also possible.'),
(3, 'en', 'Picasso Museum Tour', 'The world of Picasso in Barcelona', 'The Picasso Museum in Barcelona holds an exceptional collection assembled by the artist\'s closest friend and personal secretary, enriched by major donations from Picasso\'s own family. The museum\'s strength lies in its rare display of early and mature works side by side, letting you witness the astonishing transformation of Picasso\'s style. By the end of the tour you will understand his techniques, the artists who influenced him, and why his creative freedom still resonates today.'),
(4, 'en', 'Picasso Museum & The Spanish Village', 'An immersive cultural experience', 'After visiting the Picasso Museum we head up Montjuïc hill for an immersive experience at the Spanish Village — an open-air museum replicating Spain\'s architectural and cultural diversity across 40,000 m². Stroll through an Andalusian neighbourhood full of colour and flamenco rhythm, follow the Camino de Santiago, meet Cervantes around a corner, and watch artisans at work. The tour also includes the on-site art gallery (Picasso, Dalí, Miró…) and an optional hands-on tapas workshop. The views over Barcelona are breathtaking.'),
(5, 'en', 'Walking Tour Gothic Quarter', 'Exploring Barcelona\'s medieval heart', 'An intimate walking tour through the medieval streets and hidden plazas of the Gothic Quarter — Barcelona\'s ancient heart. Coming soon.'),
(6, 'en', 'Walking Tour Picasso & Museum', 'A combined walking tour through the Gothic-Born neighbourhood followed by a guided visit to the Picasso Museum. Coming soon.'),
(7, 'es', 'espagnol', 'faerfvae'),
(8, 'en', 'dfsd', 'sdfqsdfqsd'),
(8, 'es', 'dfqsd', 'fqsdfq'),
(8, 'fr', 'sdfqsdf', 'qsdfqs'),
(8, 'it', 'fqsdfqsd', 'fqsdfq'),
(9, 'en', 'gqsdfgsdf', 'gsdfgsdfsdf'),
(9, 'es', 'gsdfgsdfgsd', 'fgsdf'),
(9, 'fr', 'fgsdfgs', 'dsfgsd'),
(9, 'it', 'dfgsdf', 'sdfgsdfg'),
(10, 'en', 'hjkhjk', 'hjkhjk'),
(10, 'es', 'hjkhjk', 'hjkhjk'),
(10, 'fr', 'hjkhjk', 'hjkhjk'),
(10, 'it', 'hjkhjk', 'hjkhjk'),
(11, 'en', 'klmlk', 'klmlk'),
(11, 'es', 'klmlk', 'klmlk'),
(11, 'fr', 'klmlk', 'klmlk'),
(11, 'it', 'klmlk', 'klmlk'),
(12, 'en', 'English Title', 'English summary'),
(13, 'es', 'Spanish Title', 'Spanish summary'),
(14, 'fr', 'French Title', 'French summary'),
(15, 'es', 'fze', 'fazef'),
(15, 'fr', 'fze', 'zfe'),
(15, 'it',('rf',('efzef'),
(16,('en','efzafe','rfaref'),
(16,('es','erfzerf','zerf'),
(16,('fr','zerf','zerf'),
(16,('it','fzer','zerf'),
(17,('en','efazef','azefaze'),
(17, 'es', 'fazefa', 'zef'),
(17, 'fr', 'fazefaz', 'efazefaze'),
(17, 'it', 'fazefazef', 'azefazef'),
(18, 'en', 'jvky', 'z'),
(18, 'es', 'gy', 'uyfg'),
(18, 'fr', 'ggg', 'gjfvhgg'),
(18, 'it', 'gg', 'g');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `BT_Message`
--
ALTER TABLE `BT_Message`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `BT_Monument`
--
ALTER TABLE `BT_Monument`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `BT_Tour`
--
ALTER TABLE `BT_Tour`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `BT_Tour_Translation`
--
ALTER TABLE `BT_Tour_Translation`
  ADD PRIMARY KEY (`tour_id`,`locale`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `BT_Message`
--
ALTER TABLE `BT_Message`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `BT_Monument`
--
ALTER TABLE `BT_Monument`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `BT_Tour`
--
ALTER TABLE `BT_Tour`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `BT_Tour_Translation`
--
ALTER TABLE `BT_Tour_Translation`
  ADD CONSTRAINT `BT_Tour_Translation_ibfk_1` FOREIGN KEY (`tour_id`) REFERENCES `BT_Tour` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
