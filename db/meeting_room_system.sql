-- Adminer 4.8.1 MySQL 5.7.40 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `meeting_room_system` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `meeting_room_system`;

SET NAMES utf8mb4;

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `divisi` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `meet_with` varchar(100) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `bookings` (`id`, `user_id`, `room_id`, `divisi`, `date`, `time_start`, `time_end`, `meet_with`, `description`, `status`, `created_at`) VALUES
(53,	10,	3,	'Dept Business Development',	'2025-01-06',	'10:00:00',	'13:00:00',	NULL,	NULL,	'confirmed',	'2025-01-03 07:20:19'),
(54,	10,	4,	'Dept Business Development',	'2025-01-03',	'14:00:00',	'15:30:00',	NULL,	NULL,	'confirmed',	'2025-01-03 07:05:35'),
(56,	11,	3,	'Alfabeta',	'2025-01-08',	'14:00:00',	'15:30:00',	NULL,	NULL,	'confirmed',	'2025-01-03 07:17:37'),
(58,	12,	1,	'Dept Corporate Finance',	'2025-01-07',	'11:00:00',	'12:00:00',	NULL,	NULL,	'cancelled',	'2025-01-03 07:34:59'),
(62,	13,	3,	'Dept Marcom',	'2025-01-06',	'14:00:00',	'16:00:00',	NULL,	NULL,	'confirmed',	'2025-01-03 07:30:38'),
(63,	13,	3,	'Dept Marcom',	'2025-02-03',	'10:00:00',	'12:00:00',	NULL,	NULL,	'confirmed',	'2025-01-03 07:32:17'),
(64,	12,	3,	'Dept Corporate Finance',	'2025-01-07',	'11:00:00',	'12:00:00',	NULL,	NULL,	'confirmed',	'2025-01-03 07:40:09'),
(65,	14,	3,	'Digi Media',	'2025-01-06',	'09:00:00',	'10:00:00',	NULL,	NULL,	'confirmed',	'2025-01-03 07:57:06'),
(67,	13,	3,	'Dept Marcom',	'2025-01-13',	'10:00:00',	'12:00:00',	'internal',	'Meeting Koordinasi Team Marcomm',	'confirmed',	'2025-01-06 01:45:30'),
(69,	13,	3,	'Dept Marcom',	'2025-01-20',	'10:00:00',	'12:00:00',	'internal',	'Meeting Koordinasi Team Marcomm',	'confirmed',	'2025-01-06 01:47:29'),
(70,	13,	1,	'Dept Marcom',	'2025-02-24',	'10:00:00',	'12:00:00',	'internal',	'Meeting Koordinasi Team Marcomm',	'cancelled',	'2025-01-06 01:47:29'),
(73,	13,	3,	'Dept Marcom',	'2025-02-17',	'10:00:00',	'12:00:00',	'internal',	'Meeting Koordinasi Team Marcomm',	'confirmed',	'2025-01-06 01:49:31'),
(74,	13,	3,	'Dept Marcom',	'2025-02-17',	'14:00:00',	'16:00:00',	'internal',	'Meeting Koordinasi Team Marcomm',	'cancelled',	'2025-01-06 01:48:56'),
(75,	14,	3,	'Digi Media',	'2025-01-10',	'09:00:00',	'10:00:00',	'internal',	'Weekly Meeting Internal',	'confirmed',	'2025-01-06 02:28:31'),
(76,	9,	4,	'Dept PA BOD',	'2025-01-06',	'16:00:00',	'17:00:00',	'external',	'Tamu Pak CO',	'confirmed',	'2025-01-06 04:00:13'),
(78,	14,	3,	'Digi Media',	'2025-01-09',	'10:00:00',	'12:00:00',	'external',	'Client ADA',	'cancelled',	'2025-01-06 07:34:07'),
(79,	14,	3,	'Digi Media',	'2025-01-10',	'10:00:00',	'12:00:00',	'external',	'Client ADA',	'confirmed',	'2025-01-06 07:27:21');

CREATE TABLE `division` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `division` (`id`, `name`) VALUES
(1,	'Divisi Operasional'),
(2,	'Dept IT Smartpay'),
(3,	'Dept Maintenance'),
(4,	'Dept Infrastruktur'),
(5,	'Dept Finance'),
(6,	'Dept Revenue Control'),
(7,	'Dept Payrol'),
(8,	'Dept Audit Operasional'),
(9,	'Dept Management Report'),
(10,	'Dept Business Development'),
(11,	'Dept Design'),
(12,	'Divisi Human Capital'),
(13,	'Dept Purchasing'),
(14,	'Dept Logistik'),
(15,	'Dept Legal'),
(16,	'Dept Accounting'),
(17,	'Dept Corporate Finance'),
(18,	'Dept Risk Management'),
(19,	'Dept Marcom'),
(20,	'Dept Tax'),
(21,	'Dept In/Ex Affair'),
(22,	'Alfabeta'),
(23,	'Parkee'),
(24,	'Digi Media'),
(25,	'Bluecharge'),
(26,	'Dept PA BOD');

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `pax` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rooms` (`id`, `name`, `unit`, `pax`) VALUES
(1,	'Centrepark 1',	'H',	10),
(2,	'Centrepark 2',	'Unavailable',	8),
(3,	'Alfabeta',	'N',	6),
(4,	'Parkee',	'N',	4),
(5,	'EV',	'H',	6),
(6,	'Wuzz',	'H',	14);

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','view','user') DEFAULT NULL,
  `division` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `user_name`, `email`, `password`, `role`, `division`) VALUES
(1,	'ALULCHA',	'alulcha@gmail.com',	'$2y$10$jMPARyscr6yEnNt9NnT4je/NaYyyQr/5yBymhFQK.kpLA80DiHcqO',	'admin',	'Dept IT Smartpay'),
(2,	'IYUL',	'iyul_tebet@yahoo.co.id',	'$2y$10$ehkC3rgXBNwJ35hw3/6N1OTliY8olJb/wjRPKsCr6/Q15yLKGjeoi',	'user',	NULL),
(3,	'AMI CANTIK',	'ratu.candy85@gmail.com',	'$2y$10$ehkC3rgXBNwJ35hw3/6N1OTliY8olJb/wjRPKsCr6/Q15yLKGjeoi',	'user',	NULL),
(4,	'Andy',	'andy.subrata@centreparkcorp.com',	'$2y$10$OWPkDGVXzNrAfvuXCH69teKLJ6kSQmpgkJGw1yvvj8W6CMSZgD5H.',	'view',	NULL),
(5,	'Admin',	'admin@gmail.com',	'$2y$10$8cBHwVAqTFS1ALDBTJy6g.4ThS6PyYrbYnNnEAWNHiUEPIkrePyBO',	'admin',	NULL),
(6,	'Request',	'request@gmail.com',	'$2y$10$ZEaXyPzc2Z8okqXZhKb6KuUXRbX.al4qRqXvOUNGAkFKWMk1fhjSS',	'user',	NULL),
(7,	'Treasury 1',	'treasury1@gmail.com',	'$2y$10$OLAQqJNm6gGl.hGfx1skhO0ijFGWSZIU/s4NhhlaQakzLsoYeOYOW',	'view',	NULL),
(8,	'tejoy wurianto',	'tejoy@gmail.com',	'$2y$10$68bibqGSVf4htlufxm3dtOEBUk.JVQcsH7CqwzvCn80fsZqGmAucu',	NULL,	'Dept IT Smartpay'),
(9,	'Laviana',	'laviana@centreparkcorp.com',	'$2y$10$Zb5N40x6MVsdEq4RcenAueFWcXoQuYhgGSYGIqLwz8m3n/due81sO',	'admin',	'Dept PA BOD'),
(10,	'Anisa Dwi Aprilianti',	'anisa.aprilianti@centreparkcorp.com',	'$2y$10$1rFdFLy9uKzrHtMZv40gC.miOWjUMYyDJ6Ql6rNgWJfrZrK0P89QK',	'admin',	'Dept Business Development'),
(11,	'Taufiq Winowo',	'twibowo@alfabeta.co.id',	'$2y$10$RJ1Ba1LRS8VQIzZemqyHTeQkdX9Q9IcRq7FCr56NZQHhjQVA4wqJ.',	NULL,	'Alfabeta'),
(12,	'Laurentius Claudio',	'laurentius.claudio@centreparkcorp.com',	'$2y$10$mlC3Y/t6JJ7Q0xGhBty1HOhNYQMB9bxTFp2fbEXhraKDCeTG6hcI6',	NULL,	'Dept Corporate Finance'),
(13,	'prima diani',	'prima.diani@centreparkcorp.com',	'$2y$10$kALHGMD2JDE2lTklMXlhKuPa5EhbhJv64NNXs5rnvJ/IVOcXOmS.u',	NULL,	'Dept Marcom'),
(14,	'Voesto Kevin Panapardo',	'voesto.panapardo@centreparkcorp.com',	'$2y$10$ivcuUYoJmdrrsVQFZmqnzO1rw5yeOxpR245VhsSpQLQu4S1Q31Tsm',	NULL,	'Digi Media'),
(15,	'cp.treasury2',	'cp.treasury2@gmail.com',	'$2y$10$i04ZktTphaj8JrXgZRzR2.hM921JcGOWt966xwbNrCoh1doBc/4bm',	NULL,	'Divisi Human Capital'),
(16,	'cp.treasury3',	'cp.treasury3@gmail.com',	'$2y$10$6gKxhIrrP22DagDfNfRdV.UekhkjwTYLUXRDIfh7TUl5TUnNIhFJ.',	NULL,	'Divisi Human Capital');

-- 2025-01-06 07:40:56