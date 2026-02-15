-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : profeszoffice.mysql.db
-- Généré le : dim. 15 fév. 2026 à 19:36
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
  `processed_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `tags` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `inbound_alert`
--

INSERT INTO `inbound_alert` (`id`, `message_id`, `subject`, `from_email`, `received_at`, `severity`, `body`, `status`, `error_message`, `created_at`, `processed_at`, `tags`) VALUES
(1, '<PJLM-S02oU2gOHpM2yq0058b1bb@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:41:42', 'info', '', 'NEW', NULL, '2026-02-15 18:07:02', NULL, NULL),
(2, '<PJLM-S02amqkhqntKrU0058b1bc@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:42:12', 'info', '', 'NEW', NULL, '2026-02-15 18:07:02', NULL, NULL),
(3, '<PJLM-S02ocNjWsm4nUw0058b1be@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:42:12', 'info', '', 'NEW', NULL, '2026-02-15 18:07:02', NULL, NULL),
(4, '<PJLM-S02OX9kOS6yDC00058b1c0@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:42:42', 'info', '', 'NEW', NULL, '2026-02-15 18:07:02', NULL, NULL),
(5, '<PJLM-S027BXx4XFKpRc0058b1c2@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:42:42', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL, NULL),
(6, '<PJLM-S029cAgjHXwKHv0058b300@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 16:09:48', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL, NULL),
(7, '<PJLM-S02uNKkzXZzSsb0058b3bd@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 16:31:50', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL, NULL),
(8, '<PJLM-S02b09omJqyTes0058b421@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 16:40:53', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL, NULL),
(9, '<PJLM-S02MVwVn20lbhs0058b42e@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 16:42:52', 'info', '', 'NEW', NULL, '2026-02-15 18:07:03', NULL, NULL),
(10, '<PJLM-S020JFaTijlqzU0058b8d7@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 18:56:42', 'info', '', 'NEW', NULL, '2026-02-15 18:07:04', NULL, NULL),
(11, '<PJLM-S02PUNWYJVmjuh0058bb26@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 20:22:30', 'info', '', 'NEW', NULL, '2026-02-15 18:07:04', NULL, NULL),
(12, '<PJLM-S02f0MjkatwD430058be73@pjlm-s02.printaudit.com>', 'CSV BACKUP', 'noreply@kdfm.katun.com', '2026-02-13 23:10:25', 'info', 'The report titled \"CSV BACKUP\" is attached to this message.', 'NEW', NULL, '2026-02-15 18:07:04', NULL, NULL),
(13, '<PJLM-S02tKufVma0osA0058c4d6@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 09:15:22', 'info', '', 'NEW', NULL, '2026-02-15 18:07:04', NULL, NULL),
(14, '<PJLM-S02t0CI76rOJhK0058c5a8@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 10:21:31', 'info', '', 'NEW', NULL, '2026-02-15 18:07:04', NULL, NULL),
(15, '<PJLM-S02olmRBaYFkuu0058c67c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 11:13:03', 'info', '', 'NEW', NULL, '2026-02-15 18:07:05', NULL, NULL),
(16, '<PJLM-S021qdiixjnzi90058c699@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 11:21:33', 'info', '', 'NEW', NULL, '2026-02-15 18:07:05', NULL, NULL),
(17, '<PJLM-S02fogkJXrRjbD0058cf8f@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-14 18:08:37', 'info', '', 'NEW', NULL, '2026-02-15 18:07:05', NULL, NULL),
(18, '<PJLM-S0267EKRuYDlrj0058d60f@pjlm-s02.printaudit.com>', 'CSV BACKUP', 'noreply@kdfm.katun.com', '2026-02-14 23:10:26', 'info', 'The report titled \"CSV BACKUP\" is attached to this message.', 'NEW', NULL, '2026-02-15 18:07:05', NULL, NULL),
(19, '<PJLM-S023sllNY30kkZ0058e3f8@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-15 14:51:07', 'info', '', 'NEW', NULL, '2026-02-15 18:07:05', NULL, NULL),
(21, '<PJLM-S02g9MvuHdPtnh0058b147@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:32:14', 'info', '', 'NEW', NULL, '2026-02-15 18:16:15', NULL, NULL),
(22, '<PJLM-S02hA1qfT7eEmP0058b144@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:32:14', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL, NULL),
(23, '<PJLM-S02OSQeSOzNCWb0058b157@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:33:10', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL, NULL),
(24, '<PJLM-S02ixj6ZTCVk8x0058b159@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:33:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL, NULL),
(25, '<PJLM-S02kfb81IfpT8v0058b15c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:33:40', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL, NULL),
(26, '<PJLM-S029mK6PYzDabi0058b15d@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:33:40', 'info', '', 'NEW', NULL, '2026-02-15 18:16:16', NULL, NULL),
(27, '<PJLM-S02zwWJv3zOdnj0058b15e@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:34:10', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL, NULL),
(28, '<PJLM-S02gPasSmz6UuE0058b15f@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:34:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL, NULL),
(29, '<PJLM-S02hjJ8uT6Ssuo0058b160@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:34:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL, NULL),
(30, '<PJLM-S02js5FGJziWwS0058b17c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:36:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL, NULL),
(31, '<PJLM-S02kTgbLYluLCT0058b187@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:36:41', 'info', '', 'NEW', NULL, '2026-02-15 18:16:17', NULL, NULL),
(32, '<PJLM-S02aogxLDp7mvj0058b189@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:37:11', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL, NULL),
(33, '<PJLM-S02XHzfAbPM1Eh0058b18c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:38:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL, NULL),
(34, '<PJLM-S02yJKs8TAGUjG0058b194@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:38:41', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL, NULL),
(35, '<PJLM-S02LpgTFur2iz20058b19f@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:39:42', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL, NULL),
(36, '<PJLM-S02gTIFprhMOgR0058b1aa@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL, NULL),
(37, '<PJLM-S02KRfcjcMzfoV0058b1ac@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:18', NULL, NULL),
(38, '<PJLM-S02GA7zyWR4Atg0058b1ad@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:19', NULL, NULL),
(39, '<PJLM-S02xPs68K8rBKX0058b1ab@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:12', 'info', '', 'NEW', NULL, '2026-02-15 18:16:19', NULL, NULL),
(40, '<PJLM-S02tEvzoxJ6sJ90058b1b6@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:40:42', 'info', '', 'NEW', NULL, '2026-02-15 18:16:19', NULL, NULL),
(41, '<PJLM-S02ehYSoOZzHZl0058b0f0@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:22:09', 'info', '', 'NEW', NULL, '2026-02-15 18:54:55', NULL, NULL),
(42, '<PJLM-S02t3cew8k69RK0058b0ec@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:22:08', 'info', '', 'NEW', NULL, '2026-02-15 18:54:56', NULL, NULL),
(43, '<PJLM-S028T408Oh1pMu0058b0f9@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:23:09', 'info', '', 'NEW', NULL, '2026-02-15 18:54:56', NULL, NULL),
(44, '<PJLM-S02L2m9lHiu3fQ0058b0fc@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:23:09', 'info', '', 'NEW', NULL, '2026-02-15 18:54:56', NULL, NULL),
(45, '<PJLM-S02DCvY3PUKIYs0058b10a@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:25:39', 'info', '', 'NEW', NULL, '2026-02-15 18:54:56', NULL, NULL),
(46, '<PJLM-S02oqFXfyY0QUU0058b115@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:26:39', 'info', '', 'NEW', NULL, '2026-02-15 18:54:57', NULL, NULL),
(47, '<PJLM-S02qV2mEIkLeoO0058b127@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:27:40', 'info', '', 'NEW', NULL, '2026-02-15 18:54:57', NULL, NULL),
(48, '<PJLM-S02cM3oryKFRJr0058b125@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:27:40', 'info', '', 'NEW', NULL, '2026-02-15 18:54:57', NULL, NULL),
(49, '<PJLM-S02PDVXLuSbh290058b123@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:27:40', 'info', '', 'NEW', NULL, '2026-02-15 18:54:57', NULL, NULL),
(50, '<PJLM-S02zdDW6lCPx330058b128@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:27:40', 'info', '', 'NEW', NULL, '2026-02-15 18:54:57', NULL, NULL),
(51, '<PJLM-S02F98vvwNeZsj0058b11f@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:27:39', 'info', '', 'NEW', NULL, '2026-02-15 18:54:58', NULL, NULL),
(52, '<PJLM-S027Z2JQHeFXvj0058b12c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:28:10', 'info', '', 'NEW', NULL, '2026-02-15 18:54:58', NULL, NULL),
(53, '<PJLM-S02L9NtDEJmGzr0058b12d@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:28:10', 'info', '', 'NEW', NULL, '2026-02-15 18:54:58', NULL, NULL),
(54, '<PJLM-S02Tzvb5YI9IJs0058b12b@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:28:10', 'info', '', 'NEW', NULL, '2026-02-15 18:54:58', NULL, NULL),
(55, '<PJLM-S02kvXsn2Yf0sc0058b129@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:28:10', 'info', '', 'NEW', NULL, '2026-02-15 18:54:58', NULL, NULL),
(56, '<PJLM-S02aJbqJGNzscE0058b132@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:29:10', 'info', '', 'NEW', NULL, '2026-02-15 18:54:59', NULL, NULL),
(57, '<PJLM-S02WGMhLPBLYdW0058b134@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:29:40', 'info', '', 'NEW', NULL, '2026-02-15 18:54:59', NULL, NULL),
(58, '<PJLM-S0206sIQrttDRd0058b13c@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:30:10', 'info', '', 'NEW', NULL, '2026-02-15 18:54:59', NULL, NULL),
(59, '<PJLM-S02Uor03u9PH3I0058b13e@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:30:40', 'info', '', 'NEW', NULL, '2026-02-15 18:54:59', NULL, NULL),
(60, '<PJLM-S02GzxTs53iVdG0058b142@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:31:41', 'info', '', 'NEW', NULL, '2026-02-15 18:54:59', NULL, NULL),
(61, '<PJLM-S022LTrpwACkWH0058b0e9@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:21:40', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR RAMOINO / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH IM C5500 RAMOINO-Site principal 3132M190082\n192.168.8.65 [http://192.168.8.65/] RAMOINO Nouvelle alerte : (2026/2/13 16:22 France (heure standard)): Toner bas -\nToner jaune, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/11552682]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dt64wz-2BjJecFf9s3yTIpcIJlqhYHn0DBjIvio5-2Ff-2BvQZSpv9wHusm6JA-2BJhO9U52GHtl-2Bk7llFUk-2BB9CD6iGsV-2BNHAd8Wv6PSGa-2FUlN38WAxH39JsIW1rz6kLZyw4QGTGgDQ8lGD01yLX8lqaxqUFPHQ8wIrlDsprWo8sc4sbBJNK1naZ7MH8P2dcmdoLsGx9I-3D]', 'NEW', NULL, '2026-02-15 19:33:57', NULL, '[\"email\", \"ovh\"]'),
(62, '<PJLM-S0210Gai4omRXf0058b0e8@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:21:39', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR CIFE / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH MP C306Z CIFE-Site principal G446P502317\n192.168.1.246 [http://192.168.1.246/] DANY Nouvelle alerte : (2026/2/13 16:21 France (heure standard)): Toner bas -\nToner magenta, 20 % restants. [https://fm.printaudit.com//Alerts/HistoryListForDevice/9469955]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dvu1ZG1lhxPYst8NYqQNZXL-2BiRfNWU9XHQ3uXfisymgiylC2WEoXC0uguyd4Xxx6T5cTa26YGgEFdx8xlLo5taffe12zrP7JaFak9Tjb0ah-2FeAh25Cf8aZCQjq8BSZIbuRk2eFgMRwA0Rp4VVh70OlH0GZEcqXJYpKN7w5juqNyLoSQw3u7jmqg3tihw-2FWb6t0-3D]', 'NEW', NULL, '2026-02-15 19:33:57', NULL, '[\"email\", \"ovh\"]'),
(63, '<PJLM-S02r9oD6XMLDth0058b091@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:14:07', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR CCE SAINT LAURENT DU VAR / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH IM C3010 CCE SAINT LAURENT DU VAR-Site principal\n9153R640296 192.168.1.51 [http://192.168.1.51/] Nouvelle alerte : (2026/2/13 16:13 France (heure standard)): Toner bas -\nToner noir, 20 % restants. [https://fm.printaudit.com//Alerts/HistoryListForDevice/13044036]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3duA1vxDR8fkK4CCkVLir-2Bup-2F3PAIxg9myTxZdtVSkaNvg2-2FQVl5rSiQCboLtLcerZHmYpZEuy5zdv2GaBaIOECY2kY3xS3-2BLwTMNEV6-2FYcXNxf8sljIEADbPFT6pz3HXSIQOMzNmMFpGW08-2BksgkwDUN2Cg0aGl2TjJYWKyEzdtZ-2BRElqRREkVlEaLYDqaOI3U-3D]', 'NEW', NULL, '2026-02-15 19:35:44', NULL, '[\"email\", \"ovh\"]'),
(64, '<PJLM-S02pB8JPGbFqTn0058b0a1@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:15:07', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR SCIMONE NOTAIRE NICE / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH MP C5504ex SCIMONE Notaire Nice-Site principal\nC748JA00141 10.173.128.201 [http://10.173.128.201/] SCIMONE Notaire Nice Nouvelle alerte : (2026/2/13 16:14 France\n(heure standard)): Toner bas - Toner noir, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/12049734]\nNouvelle alerte : (2026/2/13 16:14 France (heure standard)): Toner bas - Toner cyan, Bas\n[https://fm.printaudit.com//Alerts/HistoryListForDevice/12049734] Nouvelle alerte : (2026/2/13 16:14 France (heure\nstandard)): Toner bas - Toner magenta, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/12049734]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dulTMF5GD0II2-2BXd9HssoFJ894jUZkMDeAlQi0U-2FLt3FVjB69w84di4QtNvh2lfAEd06D16wJ4C1Tu9s7FZtxCGFlqTw-2FfLZWbqdueTE7AFSOOTyFF5M5NBnCR14MUN57llezC5q2Lo0BFM8kXtD4k8wpxLGX6iXtFU9dmUtJvLJ20yet3xLynInwIm7NrIRV8-3D]', 'NEW', NULL, '2026-02-15 19:35:44', NULL, '[\"email\", \"ovh\"]'),
(65, '<PJLM-S02wMfgZUr3CrJ0058b0ae@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:15:37', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR EXCLUSIVE FRANCE TOURS NICE / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH IM C2000 EXCLUSIVE FRANCE TOURS NICE-Site\nprincipal 3081RA11576 192.168.1.200 [http://192.168.1.200/] EXSCLUSIVE Nouvelle alerte : (2026/2/13 16:15 France (heure\nstandard)): Toner bas - Toner cyan, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/13095186]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dszK6Gho3rhrynuyd7zNfpGVcytU9MXB7fCPQgNLMcRtmUgNiFSxoW-2FTxBt3wbru0UY9p6k0G8gKY73s1gP17zLKwGUR347LQsw1LEnPKkkBhwUM-2BL0soSmJOmJEsnZwUqQB8dankBwgCKqcxyNO2Xb0pg8SA7sa-2BiNI6BC5276SPcIv2hKItHUOqwawzy52Gs-3D]', 'NEW', NULL, '2026-02-15 19:35:45', NULL, '[\"email\", \"ovh\"]'),
(66, '<PJLM-S02wnzsLqIfQdm0058b0b7@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:16:08', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR NICE RIVIERA MONTOULIEU / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH MP C3004ex NICE RIVIERA MONTOULIEU-Site\nprincipal C717R410262 192.168.0.100 [http://192.168.0.100/] Nouvelle alerte : (2026/2/13 16:15 France (heure standard)):\nToner bas - Toner cyan, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/13369532]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dsA8nsMbExCYen9obopqfLZhn1wND0COy7E1TM-2B0rqBLyAAEx-2Bx9jJq6wnVHIp1oaPpm3ZwkIKiixJsuFhJ9RQ1kFE4Bo4x3vFbAqoVw9-2FSIM6-2FomdjZnmx1qFcmcG30siytds3ssX6uM3qQumsMU7HPJcC39TFP7euAR4TFBDlUEqqxPlQ-2B9Hznd-2BDLvYJRNM-3D]', 'NEW', NULL, '2026-02-15 19:35:45', NULL, '[\"email\", \"ovh\"]'),
(67, '<PJLM-S02XI3acH0xxDE0058b0bc@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:16:42', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR SIMONUCCI CABINET / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement LEXMARK M3250 SIMONUCCI Cabinet-Site principal\n46002204267T9 192.168.1.23 [http://192.168.1.23/] Nouvelle alerte : (2026/2/13 16:16 France (heure standard)): Toner bas\n- Black Cartridge (Noir), 15 % restants. [https://fm.printaudit.com//Alerts/HistoryListForDevice/12854586]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dvweR2Dgwj1zcOpb9sVMC-2Bh2C0lYB8LsYtxBEFl9kxzkqcMd6techEn6vIHiPp36PyI2gPOvPY8anUUxoulpfDQT0e-2BHhYzW4fVHEjzNZW73TWwZ1LLHawqG-2FV57-2Fic29ls2XqM6AJW-2BL0zziFlof3iSFWe4Pkncq1yeZzgw9QaM8T-2BRjslVcheI4cMr4-2FSZiI-3D]', 'NEW', NULL, '2026-02-15 19:35:45', NULL, '[\"email\", \"ovh\"]'),
(68, '<PJLM-S021GM3h3vLgAu0058b0bd@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:16:42', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR SCP CLEMENT ETUDE G. DE GAULLE ANTIBES / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH IM C5500 SCP CLEMENT ETUDE G. DE GAULLE\nANTIBES-Site principal 3130M430195 192.1.100.212 [http://192.1.100.212/] 2 WILSON FALGON Nouvelle alerte : (2026/2/13\n16:16 France (heure standard)): Toner bas - Toner noir, Bas\n[https://fm.printaudit.com//Alerts/HistoryListForDevice/10433012] Nouvelle alerte : (2026/2/13 16:16 France (heure\nstandard)): Toner bas - Toner magenta, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/10433012] Nouvelle\nalerte : (2026/2/13 16:16 France (heure standard)): Toner bas - Toner jaune, 20 % restants.\n[https://fm.printaudit.com//Alerts/HistoryListForDevice/10433012]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dtnJzgUc-2BhQsD5nY9liDY61gB-2BElkqUsZ38EjUEssaz7tWRL4dPzd1ueKxi0eTbXXZcTDSQ6QwmVY35Wk2mcip4mT3-2BiR935DgJ7uSi0YOsntylePIXGQ3TjgbYd-2BOq9OfRcq9CH6pMIyKFUVMLyFJpmsd7fiv2QPQ22Z4RrQ0VxHAdfwp-2B91cCLZQawI1frKQ-3D]', 'NEW', NULL, '2026-02-15 19:35:45', NULL, '[\"email\", \"ovh\"]'),
(69, '<PJLM-S02rrXj7gC2DWp0058b0ba@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:16:41', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR ROUSTAN / SITE PRINCIPALE\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH IM C5500 roustan-Site Principale 3131M410256\n192.168.30.150 [http://192.168.30.150/] GENERAL Nouvelle alerte : (2026/2/13 16:16 France (heure standard)): Toner bas -\nToner noir, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/10920627]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dtU-2BpDxG4bpHdTYN1ZrXXzg-2BfTdiJ4nqQmssyHTG7z62T4PUToYg1aOCI8epEvaF7RT4Mew52iOzfcvbIRE3XNCUFe53L7UKl7jGMFKjw956FPXCsxZKpMym0-2FhB3gspsZ3BwEtpHM-2FEpjTQFO2fsOofeoAoGl0-2Fbb-2B9BOLJTF6S0A8Z7B-2BkQkHH9gV2o0Vpmk-3D]', 'NEW', NULL, '2026-02-15 19:35:46', NULL, '[\"email\", \"ovh\"]'),
(70, '<PJLM-S02YfYoiB5ANH20058b0d0@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:18:09', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR EMMEREZ NOTAIRE VALBONNE / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH IM C5500 EMMEREZ Notaire Valbonne-Site principal\n3130M530133 10.172.2.203 [http://10.172.2.203/] Nouvelle alerte : (2026/2/13 16:17 France (heure standard)): Toner bas -\nToner noir, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/10605926] RICOH MP C307 EMMEREZ Notaire\nValbonne-Site principal C507P402837 10.172.2.202 [http://10.172.2.202/] Nouvelle alerte : (2026/2/13 16:17 France (heure\nstandard)): Toner bas - Toner noir, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/12622562] RICOH IM C5510\nEMMEREZ Notaire Valbonne-Site principal 9183R510539 10.172.2.201 [http://10.172.2.201/] Nouvelle alerte : (2026/2/13\n16:17 France (heure standard)): Toner bas - Toner noir, 20 % restants.\n[https://fm.printaudit.com//Alerts/HistoryListForDevice/12987831]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dvZ8xN2GX7uE0y2bSRiQOSmpN-2B-2FzkhyzL9GD91ASp9XEEQuErk6mmTOZsceiyuBcomlXw6hgVtRnoBzWnbDOpcKGvO9hUUHO67zoxShyhE1D3YCtgHIHZWB-2FGzJ-2Bgrujarq-2F9xdqs9hKD0AQlbTSIJH7VqjWLM3KuHjae9stwqU1LngUOuNNo0ZpZBmKopS1ak-3D]', 'NEW', NULL, '2026-02-15 19:35:46', NULL, '[\"email\", \"ovh\"]'),
(71, '<PJLM-S02IcVux1Zcn8T0058b0e0@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:21:08', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR DARRAS CHOUMAN AVOCATS / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH IM C3000 DARRAS CHOUMAN AVOCATS-Site principal\n3100RC30139 192.168.192.99 [http://192.168.192.99/] DARRAS Nouvelle alerte : (2026/2/13 16:20 France (heure standard)):\nToner bas - Toner jaune, Bas [https://fm.printaudit.com//Alerts/HistoryListForDevice/10621555]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3dsBUsIB8PksR5Xr8hryxmyedilknjqcDah5EAN0-2Bz9KLDoJy-2FuxOWUjCvI9W5h6a1qJAnv5b-2FBElXjfV73ZuxZAhs-2FO949V89M1GdX4PP3vQyUGhpCyR9RFOtWbAFzaxoZV5P1Ly0C9PrrkR50noDESUZV-2FsaO-2FD0X94F6oLEau9cRBsNlU2pnaQMy-2BtK6n9B4-3D]', 'NEW', NULL, '2026-02-15 19:35:46', NULL, '[\"email\", \"ovh\"]'),
(72, '<PJLM-S02tEvzoxJ6sJ90058b0e3@pjlm-s02.printaudit.com>', 'alert', 'noreply@kdfm.katun.com', '2026-02-13 15:21:08', 'warning', 'Logo [cid:LOGO]\n\n\nSMART ALERT POUR VGB AVOCATS MIMONT CANNES / SITE PRINCIPAL\n\n\n\n\nALERTES PÉRIPHÉRIQUE\n\nFabricantNom de la machineClient - siteSérieAdresse IPEmplacement RICOH MP C307 VGB Avocats Mimont Cannes-Site principal\nC518P901847 192.168.0.151 [http://192.168.0.151/] Nouvelle alerte : (2026/2/13 15:56 France (heure standard)): Toner bas\n- Toner magenta, 20 % restants. [https://fm.printaudit.com//Alerts/HistoryListForDevice/13547581]\n\n\n\n\n[https://u36745673.ct.sendgrid.net/wf/open?upn=u001.Am9uF-2BYeG8A8ACz7SdNi6kzh4HVjL3IlQHptUu0S3duAGhFFKZ2A4WLv2qp-2B6de34WgT6ULZ47TPhZDMRTzz-2FBqFrMF2uFNrG7k1YPitra5UyBTVXSmYr4nA2zmnk3wpqjZ1gscq9vNS-2FwTBfcw7kEiNLj9YW4-2BNOhhfWLwnjTY9l-2B-2Bkj8EvurCJdQAaTVOqdArXFQOF-2B3ZYge786a5mefrC-2F-2Fz7UW7TYiH-2BeUktFEw-3D]', 'NEW', NULL, '2026-02-15 19:35:46', NULL, '[\"email\", \"ovh\"]');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
