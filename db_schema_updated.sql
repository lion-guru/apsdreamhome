-- APS Dream Home - MLM Database Schema
-- Generated: 2025-06-30 11:11:32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Disable foreign key checks temporarily
--
SET FOREIGN_KEY_CHECKS = 0;

--
-- Table structure for table `associates`
--
DROP TABLE IF EXISTS `associates`;
CREATE TABLE `associates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `commission_percent` decimal(5,2) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  KEY `level` (`level`),
  CONSTRAINT `associates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `associates_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `associates` (`id`),
  CONSTRAINT `associates_ibfk_3` FOREIGN KEY (`level`) REFERENCES `associate_levels` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `mlm_tree`
--
DROP TABLE IF EXISTS `mlm_tree`;
CREATE TABLE `mlm_tree` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `mlm_tree_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `mlm_tree_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `mlm_commissions`
--
DROP TABLE IF EXISTS `mlm_commissions`;
CREATE TABLE `mlm_commissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `property_id` int(11) NOT NULL,
  `commission_amount` decimal(12,2) DEFAULT NULL,
  `commission_type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `mlm_commission_ledger`
--
DROP TABLE IF EXISTS `mlm_commission_ledger`;
CREATE TABLE `mlm_commission_ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) DEFAULT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `commission_date` datetime DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `associate_id` (`associate_id`),
  CONSTRAINT `mlm_commission_ledger_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `commission_payouts`
--
DROP TABLE IF EXISTS `commission_payouts`;
CREATE TABLE `commission_payouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `paid_on` date DEFAULT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `associate_id` (`associate_id`),
  KEY `transaction_id` (`transaction_id`),
  CONSTRAINT `commission_payouts_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`),
  CONSTRAINT `commission_payouts_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `commission_transactions`
--
DROP TABLE IF EXISTS `commission_transactions`;
CREATE TABLE `commission_transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `business_amount` decimal(12,2) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `commission_percentage` decimal(4,2) NOT NULL,
  `level_difference_amount` decimal(10,2) DEFAULT 0.00,
  `upline_id` int(11) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `associate_levels`
--
DROP TABLE IF EXISTS `associate_levels`;
CREATE TABLE `associate_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `commission_percent` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `team_hierarchy`
--
DROP TABLE IF EXISTS `team_hierarchy`;
CREATE TABLE `team_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `upline_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Enable foreign key checks
--
SET FOREIGN_KEY_CHECKS = 1;

--
-- Commit the transaction
--
COMMIT;
