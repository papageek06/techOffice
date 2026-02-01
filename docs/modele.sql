-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 27 jan. 2026 à 12:29
-- Version du serveur : 8.0.40
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `techoffice`
--

-- --------------------------------------------------------

--
-- Structure de la table `modele`
--

DROP TABLE IF EXISTS `modele`;
CREATE TABLE IF NOT EXISTS `modele` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reference_modele` varchar(150) NOT NULL,
  `couleur` tinyint NOT NULL DEFAULT '0',
  `fabricant_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_modele_fabricant_ref` (`fabricant_id`,`reference_modele`),
  KEY `IDX_10028558CBAAAAB3` (`fabricant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `modele`
--

INSERT INTO `modele` (`id`, `reference_modele`, `couleur`, `fabricant_id`) VALUES
(3, 'MP C2003', 1, 5),
(4, 'IM C3000', 1, 5),
(5, 'MP C3004', 1, 5),
(6, 'MP C4503', 1, 5),
(7, 'MP C407', 1, 5),
(8, 'MP C5504', 1, 5),
(9, 'IM C300', 1, 5),
(10, 'MP C307', 1, 5),
(11, 'MP C3003', 1, 5),
(12, 'MP C3504', 1, 5),
(13, 'MP C2503', 1, 5),
(14, 'MP C3503', 1, 5),
(15, 'IM C2000', 1, 5),
(16, 'MP C2004', 1, 5),
(17, 'IM C4500', 1, 5),
(18, 'IM 7000', 0, 5),
(19, 'IM C6000', 1, 5),
(20, 'M3250', 0, 6),
(21, 'IM C2010', 1, 5),
(22, 'DesignJet T930', 1, 7),
(23, 'LaserJet M506', 0, 7),
(24, 'M5255', 0, 6),
(25, 'IM C5510', 1, 5),
(26, 'MP C5504ex', 1, 5),
(27, 'MP C6004', 1, 5),
(28, 'MP C4504', 1, 5),
(29, 'Color Device', 1, 6),
(30, 'IM C3010', 1, 5),
(31, 'SP 4510', 0, 5),
(32, 'Monochrome Device', 0, 6),
(33, 'XM1246', 0, 6),
(34, 'MP C5503', 1, 5),
(35, 'SP 3600DN', 0, 5),
(36, 'MP C2504', 1, 5),
(37, 'IM C2500', 1, 5),
(38, 'IM C5500', 1, 5),
(39, 'MP C306', 1, 5),
(40, 'C2240', 1, 6),
(41, 'Color Device', 1, 5),
(42, 'XC4240', 1, 6),
(43, 'MP C2504ex', 1, 5),
(44, 'IM C4510', 1, 5),
(45, 'MP 9003', 0, 5),
(46, 'Aficio SP 5200S', 0, 5),
(47, 'P 501', 0, 5),
(48, 'MP 402SPF', 0, 5),
(49, 'M1246', 0, 6),
(50, 'MP C305', 1, 5),
(51, 'XM1242', 0, 6),
(52, 'IM C3500', 1, 5),
(53, 'MP C6003', 1, 5),
(54, 'IM 350', 0, 5),
(55, 'XM3142', 0, 6),
(56, 'Aficio MP C5502', 1, 5),
(57, 'IM 2702', 0, 5),
(58, 'IM 430', 0, 5),
(59, 'IM 9000', 0, 5),
(60, 'MP 6503', 0, 5);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `modele`
--
ALTER TABLE `modele`
  ADD CONSTRAINT `FK_10028558CBAAAAB3` FOREIGN KEY (`fabricant_id`) REFERENCES `fabricant` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
