-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : profeszoffice.mysql.db
-- Généré le : dim. 15 fév. 2026 à 18:17
-- Version du serveur : 8.4.7-7
-- Version de PHP : 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `profeszoffice`
--

-- --------------------------------------------------------

--
-- Structure de la table `inbound_alert`
--

CREATE TABLE `inbound_alert` (
  `id` int NOT NULL,
  `message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `received_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `severity` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_message` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `processed_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `inbound_alert`
--

INSERT INTO `inbound_alert` (`id`, `message_id`, `subject`, `from_email`, `received_at`, `severity`, `body`, `status`, `error_message`, `created_at`, `processed_at`) VALUES
(1, '<PJLM-S02oU2gOHpM2yq0058b1bb@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:41:42', 'info', '', 'NEW', NULL, '2026-02-15 18:07:02', NULL),
(2, '<PJLM-S02amqkhqntKrU0058b1bc@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:42:12', 'info', '', 'NEW', NULL, '2026-02-15 18:07:02', NULL),
(3, '<PJLM-S02ocNjWsm4nUw0058b1be@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:42:12', 'info', '', 'NEW', NULL, '2026-02-15 18:07:02', NULL),
(4, '<PJLM-S02OX9kOS6yDC00058b1c0@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:42:42', 'info', '', 'NEW', NULL, '2026-02-15 18:07:02', NULL),
(5, '<PJLM-S027BXx4XFKpRc0058b1c2@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:42:42', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL),
(6, '<PJLM-S029cAgjHXwKHv0058b300@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 16:09:48', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL),
(7, '<PJLM-S02uNKkzXZzSsb0058b3bd@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 16:31:50', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL),
(8, '<PJLM-S02b09omJqyTes0058b421@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 16:40:53', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL),
(9, '<PJLM-S02MVwVn20lbhs0058b42e@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 16:42:52', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL),
(10, '<PJLM-S020JFaTijlqzU0058b8d7@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 18:56:42', 'info', '', 'NEW', NULL, '2026-02-15 18:07:04', NULL),
(11, '<PJLM-S02PUNWYJVmjuh0058bb26@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 20:22:30', 'info', '', 'NEW', NULL, '2026-02-15 18:07:04', NULL),
(12, '<PJLM-S02f0MjkatwD430058be73@pjlm-s02.printaudit.com>', 'CSV BACKUP', 'noreply@kdfm.katun.com', '2026-02-13 23:10:25', 'info', 'The report titled \"CSV BACKUP\" is attached to this message.', 'NEW', NULL, '2026-02-15 18:07:04', NULL),
(13, '<PJLM-S02tKufVma0osA0058c4d6@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 09:15:22', 'info', '', 'NEW', NULL, '2026-02-15 18:07:04', NULL),
(14, '<PJLM-S02t0CI76rOJhK0058c5a8@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 10:21:31', 'info', '', 'NEW', NULL, '2026-02-15 18:07:04', NULL),
(15, '<PJLM-S02olmRBaYFkuu0058c67c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 11:13:03', 'info', '', 'NEW', NULL, '2026-02-15 18:07:05', NULL),
(16, '<PJLM-S021qdiixjnzi90058c699@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 11:21:33', 'info', '', 'NEW', NULL, '2026-02-15 18:07:05', NULL),
(17, '<PJLM-S02fogkJXrRjbD0058cf8f@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 18:08:37', 'info', '', 'NEW', NULL, '2026-02-15 18:07:05', NULL),
(18, '<PJLM-S0267EKRuYDlrj0058d60f@pjlm-s02.printaudit.com>', 'CSV BACKUP', 'noreply@kdfm.katun.com', '2026-02-14 23:10:26', 'info', 'The report titled \"CSV BACKUP\" is attached to this message.', 'NEW', NULL, '2026-02-15 18:07:05', NULL),
(19, '<PJLM-S023sllNY30kkZ0058e3f8@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-15 14:51:07', 'info', '', 'NEW', NULL, '2026-02-15 18:07:05', NULL),
(21, '<PJLM-S02g9MvuHdPtnh0058b147@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:32:14', 'info', '', 'NEW', NULL, '2026-02-15 18:16:15', NULL),
(22, '<PJLM-S02hA1qfT7eEmP0058b144@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:32:14', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL),
(23, '<PJLM-S02OSQeSOzNCWb0058b157@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:33:10', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL),
(24, '<PJLM-S02ixj6ZTCVk8x0058b159@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:33:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL),
(25, '<PJLM-S02kfb81IfpT8v0058b15c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:33:40', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL),
(26, '<PJLM-S029mK6PYzDabi0058b15d@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:33:40', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL),
(27, '<PJLM-S02zwWJv3zOdnj0058b15e@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:34:10', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL),
(28, '<PJLM-S02gPasSmz6UuE0058b15f@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:34:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL),
(29, '<PJLM-S02hjJ8uT6Ssuo0058b160@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:34:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL),
(30, '<PJLM-S02js5FGJziWwS0058b17c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:36:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL),
(31, '<PJLM-S02kTgbLYluLCT0058b187@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:36:41', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL),
(32, '<PJLM-S02aogxLDp7mvj0058b189@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:37:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL),
(33, '<PJLM-S02XHzfAbPM1Eh0058b18c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:38:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL),
(34, '<PJLM-S02yJKs8TAGUjG0058b194@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:38:41', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL),
(35, '<PJLM-S02LpgTFur2iz20058b19f@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:39:42', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL),
(36, '<PJLM-S02gTIFprhMOgR0058b1aa@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL),
(37, '<PJLM-S02KRfcjcMzfoV0058b1ac@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL),
(38, '<PJLM-S02GA7zyWR4Atg0058b1ad@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:19', NULL),
(39, '<PJLM-S02xPs68K8rBKX0058b1ab@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:19', NULL),
(40, '<PJLM-S02tEvzoxJ6sJ90058b1b6@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:42', 'info', '', 'NEW', NULL, '2026-02-15 18:16:19', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `inbound_alert`
--
ALTER TABLE `inbound_alert`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_inbound_alert_message_id` (`message_id`),
  ADD KEY `idx_inbound_alert_status` (`status`),
  ADD KEY `idx_inbound_alert_received_at` (`received_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `inbound_alert`
--
ALTER TABLE `inbound_alert`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
