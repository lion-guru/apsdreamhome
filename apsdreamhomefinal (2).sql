-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2025 at 05:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apsdreamhomefinal`
--

-- --------------------------------------------------------

--
-- Table structure for table `about`
--

CREATE TABLE `about` (
  `id` int(10) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about`
--

INSERT INTO `about` (`id`, `title`, `content`, `image`) VALUES
(10, 'About Us', '...your existing content...', 'condos-pool.png');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `auser` varchar(100) NOT NULL,
  `apass` varchar(255) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `auser`, `apass`, `role`, `status`, `email`, `phone`) VALUES
(1, 'superadmin', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'superadmin', 'active', 'superadmin@demo.com', '9000000001'),
(29, 'admin', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'admin', 'active', 'admin1@aps.com', NULL),
(30, 'ceo', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'ceo', 'active', NULL, NULL),
(31, 'cfo', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'cfo', 'active', NULL, NULL),
(32, 'cm', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'cm', 'active', NULL, NULL),
(33, 'coo', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'coo', 'active', NULL, NULL),
(34, 'cto', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'cto', 'active', NULL, NULL),
(35, 'director', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'director', 'active', NULL, NULL),
(36, 'finance', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'finance', 'active', NULL, NULL),
(37, 'hr', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'hr', 'active', NULL, NULL),
(38, 'it_head', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'it_head', 'active', NULL, NULL),
(39, 'legal', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'legal', 'active', NULL, NULL),
(40, 'manager', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'manager', 'active', NULL, NULL),
(41, 'marketing', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'marketing', 'active', NULL, NULL),
(42, 'office_admin', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'office_admin', 'active', NULL, NULL),
(43, 'official_employee', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'official_employee', 'active', NULL, NULL),
(44, 'operations', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'operations', 'active', NULL, NULL),
(45, 'sales', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'sales', 'active', NULL, NULL),
(46, 'super_admin', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'super_admin', 'active', NULL, NULL),
(47, 'support', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'support', 'active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sales` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_chatbot_config`
--

CREATE TABLE `ai_chatbot_config` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_chatbot_config`
--

INSERT INTO `ai_chatbot_config` (`id`, `provider`, `api_key`, `webhook_url`, `created_at`) VALUES
(1, '', NULL, NULL, '2025-05-17 18:03:21'),
(6, 'Value for provider 1', 'Value for api_key 1', 'Value for webhook_url 1', '2025-05-01 00:00:00'),
(7, 'Value for provider 2', 'Value for api_key 2', 'Value for webhook_url 2', '2025-05-07 00:00:00'),
(8, 'Value for provider 3', 'Value for api_key 3', 'Value for webhook_url 3', '2025-05-13 00:00:00'),
(9, 'Value for provider 4', 'Value for api_key 4', 'Value for webhook_url 4', '2025-05-19 00:00:00'),
(10, 'Value for provider 5', 'Value for api_key 5', 'Value for webhook_url 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ai_chatbot_interactions`
--

CREATE TABLE `ai_chatbot_interactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `query` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `satisfaction_score` decimal(2,1) DEFAULT NULL,
  `response_time` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_config`
--

CREATE TABLE `ai_config` (
  `id` int(11) NOT NULL,
  `feature` varchar(100) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `config_json` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_lead_scores`
--

CREATE TABLE `ai_lead_scores` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `scored_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_lead_scores`
--

INSERT INTO `ai_lead_scores` (`id`, `lead_id`, `score`, `scored_at`) VALUES
(1, 0, 0, '2025-05-17 18:03:21'),
(6, 1, 1, '2025-05-01 00:00:00'),
(7, 2, 2, '2025-05-07 00:00:00'),
(8, 3, 3, '2025-05-13 00:00:00'),
(9, 4, 4, '2025-05-19 00:00:00'),
(10, 5, 5, '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ai_logs`
--

CREATE TABLE `ai_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) DEFAULT NULL,
  `input_text` text DEFAULT NULL,
  `ai_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_developers`
--

CREATE TABLE `api_developers` (
  `id` int(11) NOT NULL,
  `dev_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_developers`
--

INSERT INTO `api_developers` (`id`, `dev_name`, `email`, `api_key`, `status`, `created_at`) VALUES
(1, '', '', '', 'active', '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'rahul@example.com', 'Value for api_key 1', 'active', '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'priya@example.com', 'Value for api_key 2', 'pending', '2025-05-07 00:00:00'),
(8, 'Amit Kumar', 'amit@example.com', 'Value for api_key 3', 'completed', '2025-05-13 00:00:00'),
(9, 'Neha Patel', 'neha@example.com', 'Value for api_key 4', 'cancelled', '2025-05-19 00:00:00'),
(10, 'Vikram Mehta', 'vikram@example.com', 'Value for api_key 5', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `api_integrations`
--

CREATE TABLE `api_integrations` (
  `id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `api_url` varchar(255) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_integrations`
--

INSERT INTO `api_integrations` (`id`, `service_name`, `api_url`, `api_key`, `status`, `created_at`) VALUES
(1, '', '', NULL, 'active', '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'Value for api_url 1', 'Value for api_key 1', 'active', '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'Value for api_url 2', 'Value for api_key 2', 'pending', '2025-05-07 00:00:00'),
(8, 'Amit Kumar', 'Value for api_url 3', 'Value for api_key 3', 'completed', '2025-05-13 00:00:00'),
(9, 'Neha Patel', 'Value for api_url 4', 'Value for api_key 4', 'cancelled', '2025-05-19 00:00:00'),
(10, 'Vikram Mehta', 'Value for api_url 5', 'Value for api_key 5', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `permissions` text DEFAULT NULL,
  `rate_limit` int(11) DEFAULT 100,
  `status` enum('active','revoked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `last_used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_rate_limits`
--

CREATE TABLE `api_rate_limits` (
  `id` int(11) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_request_logs`
--

CREATE TABLE `api_request_logs` (
  `id` int(11) NOT NULL,
  `api_key_id` int(11) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_sandbox`
--

CREATE TABLE `api_sandbox` (
  `id` int(11) NOT NULL,
  `dev_name` varchar(255) DEFAULT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_sandbox`
--

INSERT INTO `api_sandbox` (`id`, `dev_name`, `endpoint`, `payload`, `status`, `created_at`) VALUES
(1, NULL, NULL, NULL, 'pending', '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'Value for endpoint 1', 'Value for payload 1', 'active', '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'Value for endpoint 2', 'Value for payload 2', 'pending', '2025-05-07 00:00:00'),
(8, 'Amit Kumar', 'Value for endpoint 3', 'Value for payload 3', 'completed', '2025-05-13 00:00:00'),
(9, 'Neha Patel', 'Value for endpoint 4', 'Value for payload 4', 'cancelled', '2025-05-19 00:00:00'),
(10, 'Vikram Mehta', 'Value for endpoint 5', 'Value for payload 5', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `api_usage`
--

CREATE TABLE `api_usage` (
  `id` int(11) NOT NULL,
  `dev_name` varchar(255) DEFAULT NULL,
  `api_key` varchar(64) DEFAULT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `usage_count` int(11) DEFAULT 1,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_usage`
--

INSERT INTO `api_usage` (`id`, `dev_name`, `api_key`, `endpoint`, `usage_count`, `timestamp`) VALUES
(1, NULL, NULL, NULL, 1, '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'Value for api_key 1', 'Value for endpoint 1', 1, '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'Value for api_key 2', 'Value for endpoint 2', 2, '2025-05-02 00:00:00'),
(8, 'Amit Kumar', 'Value for api_key 3', 'Value for endpoint 3', 3, '2025-05-03 00:00:00'),
(9, 'Neha Patel', 'Value for api_key 4', 'Value for endpoint 4', 4, '2025-05-04 00:00:00'),
(10, 'Vikram Mehta', 'Value for api_key 5', 'Value for endpoint 5', 5, '2025-05-05 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `app_store`
--

CREATE TABLE `app_store` (
  `id` int(11) NOT NULL,
  `app_name` varchar(255) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `app_url` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_store`
--

INSERT INTO `app_store` (`id`, `app_name`, `provider`, `app_url`, `price`, `created_at`) VALUES
(1, '', NULL, NULL, 0.00, '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'Value for provider 1', 'Value for app_url 1', 15000000.00, '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'Value for provider 2', 'Value for app_url 2', 7000000.00, '2025-05-07 00:00:00'),
(8, 'Amit Kumar', 'Value for provider 3', 'Value for app_url 3', 9000000.00, '2025-05-13 00:00:00'),
(9, 'Neha Patel', 'Value for provider 4', 'Value for app_url 4', 20000000.00, '2025-05-19 00:00:00'),
(10, 'Vikram Mehta', 'Value for provider 5', 'Value for app_url 5', 25000000.00, '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ar_vr_tours`
--

CREATE TABLE `ar_vr_tours` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `asset_url` varchar(255) NOT NULL,
  `asset_type` varchar(50) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ar_vr_tours`
--

INSERT INTO `ar_vr_tours` (`id`, `property_id`, `asset_url`, `asset_type`, `uploaded_at`) VALUES
(1, 0, '', NULL, '2025-05-17 18:03:21'),
(6, 1, 'Value for asset_url 1', 'villa', '2025-05-01 00:00:00'),
(7, 2, 'Value for asset_url 2', 'apartment', '2025-05-07 00:00:00'),
(8, 3, 'Value for asset_url 3', 'house', '2025-05-13 00:00:00'),
(9, 4, 'Value for asset_url 4', 'villa', '2025-05-19 00:00:00'),
(10, 5, 'Value for asset_url 5', 'penthouse', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `associates`
--

CREATE TABLE `associates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `commission_percent` decimal(5,2) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `commission_plan_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `current_level` int(11) DEFAULT 1,
  `total_business` decimal(15,2) DEFAULT 0.00,
  `direct_business` decimal(15,2) DEFAULT 0.00,
  `team_business` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `associates`
--

INSERT INTO `associates` (`id`, `name`, `email`, `phone`, `user_id`, `level`, `parent_id`, `commission_percent`, `join_date`, `status`, `commission_plan_id`, `created_at`, `current_level`, `total_business`, `direct_business`, `team_business`) VALUES
(17, NULL, NULL, NULL, 189, NULL, NULL, NULL, NULL, 'active', 1, '2025-06-30 09:42:05', 1, 500000.00, 100000.00, 300000.00),
(18, NULL, NULL, NULL, 190, NULL, 17, NULL, NULL, 'active', 1, '2025-06-30 09:42:05', 2, 450000.00, 350000.00, 0.00),
(19, NULL, NULL, NULL, 191, NULL, 18, NULL, NULL, 'active', 1, '2025-06-30 09:42:05', 3, 25000.00, 25000.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `associate_levels`
--

CREATE TABLE `associate_levels` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `commission_percent` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `associate_levels`
--

INSERT INTO `associate_levels` (`id`, `name`, `commission_percent`) VALUES
(1, 'Level 1', 5.00),
(2, 'Level 2', 2.50),
(3, NULL, NULL),
(6, 'Rahul Sharma', 999.99),
(7, 'Priya Singh', 999.99),
(8, 'Amit Kumar', 999.99),
(9, 'Neha Patel', 999.99),
(10, 'Vikram Mehta', 999.99);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `in_time` time DEFAULT NULL,
  `out_time` time DEFAULT NULL,
  `status` enum('present','absent','leave') DEFAULT 'present'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_access_log`
--

CREATE TABLE `audit_access_log` (
  `id` int(11) NOT NULL,
  `accessed_at` datetime DEFAULT current_timestamp(),
  `action` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_access_log`
--

INSERT INTO `audit_access_log` (`id`, `accessed_at`, `action`, `user_id`, `details`) VALUES
(1, '2025-05-17 18:03:21', NULL, NULL, NULL),
(6, '2025-05-01 00:00:00', 'Value for action 1', 1, 'Value for details 1'),
(7, '2025-05-07 00:00:00', 'Value for action 2', 2, 'Value for details 2'),
(8, '2025-05-13 00:00:00', 'Value for action 3', 3, 'Value for details 3'),
(9, '2025-05-19 00:00:00', 'Value for action 4', 4, 'Value for details 4'),
(10, '2025-05-25 00:00:00', 'Value for action 5', 5, 'Value for details 5');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `property_type` int(11) NOT NULL,
  `installment_plan` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `city`
--

CREATE TABLE `city` (
  `cid` int(11) NOT NULL,
  `cname` varchar(100) NOT NULL,
  `sid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_payouts`
--

CREATE TABLE `commission_payouts` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `paid_on` date DEFAULT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_transactions`
--

CREATE TABLE `commission_transactions` (
  `transaction_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `business_amount` decimal(12,2) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `commission_percentage` decimal(4,2) NOT NULL,
  `level_difference_amount` decimal(10,2) DEFAULT 0.00,
  `upline_id` int(11) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `communications`
--

CREATE TABLE `communications` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `type` enum('call','email','meeting','whatsapp','sms') DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `communication_date` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `pan` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_employees`
--

CREATE TABLE `company_employees` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_type` varchar(50) DEFAULT NULL,
  `kyc_status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_documents`
--

CREATE TABLE `customer_documents` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `doc_name` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'uploaded',
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `blockchain_hash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_journeys`
--

CREATE TABLE `customer_journeys` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `journey` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`journey`)),
  `started_at` datetime DEFAULT current_timestamp(),
  `last_touch_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_stream_events`
--

CREATE TABLE `data_stream_events` (
  `id` int(11) NOT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `streamed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `url` varchar(255) NOT NULL,
  `uploaded_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `drive_file_id` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emi`
--

CREATE TABLE `emi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `property_id` int(11) NOT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emi_installments`
--

CREATE TABLE `emi_installments` (
  `id` int(11) NOT NULL,
  `emi_plan_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `principal_amount` decimal(12,2) NOT NULL,
  `interest_amount` decimal(12,2) NOT NULL,
  `due_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_status` enum('pending','paid','overdue') NOT NULL DEFAULT 'pending',
  `payment_id` int(11) DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `last_reminder_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emi_plans`
--

CREATE TABLE `emi_plans` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `tenure_months` int(11) NOT NULL,
  `emi_amount` decimal(12,2) NOT NULL,
  `down_payment` decimal(12,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','completed','defaulted','cancelled') NOT NULL DEFAULT 'active',
  `foreclosure_date` date DEFAULT NULL,
  `foreclosure_amount` decimal(12,2) DEFAULT NULL,
  `foreclosure_payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `expense_date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `land_area` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `kyc_doc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_tickets`
--

CREATE TABLE `feedback_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foreclosure_logs`
--

CREATE TABLE `foreclosure_logs` (
  `id` int(11) NOT NULL,
  `emi_plan_id` int(11) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `message` text DEFAULT NULL,
  `attempted_by` int(11) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `global_payments`
--

CREATE TABLE `global_payments` (
  `id` int(11) NOT NULL,
  `client` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'INR',
  `purpose` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `id` int(11) NOT NULL,
  `plot_id` int(11) DEFAULT NULL,
  `action` enum('created','booked','sold','transferred','released') DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `iot_devices`
--

CREATE TABLE `iot_devices` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `device_type` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `last_seen` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `iot_device_events`
--

CREATE TABLE `iot_device_events` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `event_value` varchar(255) DEFAULT NULL,
  `event_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jwt_blacklist`
--

CREATE TABLE `jwt_blacklist` (
  `id` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `land_purchases`
--

CREATE TABLE `land_purchases` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `property_id` int(11) NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `registry_no` varchar(100) DEFAULT NULL,
  `agreement_doc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `source` varchar(100) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` text NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `converted_at` datetime DEFAULT NULL,
  `converted_amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legal_documents`
--

CREATE TABLE `legal_documents` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `review_status` varchar(50) DEFAULT 'pending',
  `ai_summary` text DEFAULT NULL,
  `ai_flags` text DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketing_campaigns`
--

CREATE TABLE `marketing_campaigns` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('email','sms') NOT NULL,
  `message` text NOT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'scheduled',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketing_strategies`
--

CREATE TABLE `marketing_strategies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketplace_apps`
--

CREATE TABLE `marketplace_apps` (
  `id` int(11) NOT NULL,
  `app_name` varchar(255) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `app_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `version` varchar(20) NOT NULL,
  `migration_name` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commissions`
--

CREATE TABLE `mlm_commissions` (
  `id` int(11) NOT NULL,
  `commission_plan_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `property_id` int(11) NOT NULL,
  `commission_amount` decimal(12,2) DEFAULT NULL,
  `commission_type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `level` int(11) NOT NULL DEFAULT 1,
  `direct_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `difference_percentage` decimal(5,2) DEFAULT 0.00,
  `upline_id` int(11) DEFAULT NULL,
  `is_direct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mlm_commissions`
--

INSERT INTO `mlm_commissions` (`id`, `commission_plan_id`, `user_id`, `user_name`, `transaction_id`, `property_id`, `commission_amount`, `commission_type`, `status`, `created_at`, `level`, `direct_percentage`, `difference_percentage`, `upline_id`, `is_direct`) VALUES
(6, 1, 190, NULL, 22, 0, 5000.00, 'direct_commission', 'pending', '2025-06-30 09:44:56', 1, 5.00, 0.00, NULL, 1),
(7, 1, 190, NULL, 23, 0, 5000.00, 'direct_commission', 'pending', '2025-06-30 09:50:03', 1, 5.00, 0.00, NULL, 1),
(8, 1, 190, NULL, 24, 0, 5000.00, 'direct_commission', 'pending', '2025-06-30 09:52:35', 1, 5.00, 0.00, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commission_ledger`
--

CREATE TABLE `mlm_commission_ledger` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `commission_date` datetime DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commission_levels`
--

CREATE TABLE `mlm_commission_levels` (
  `id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `direct_percentage` decimal(5,2) NOT NULL,
  `min_business` decimal(15,2) DEFAULT 0.00,
  `max_business` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commission_levels_backup`
--

CREATE TABLE `mlm_commission_levels_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `plan_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `direct_percentage` decimal(5,2) NOT NULL,
  `min_business` decimal(15,2) DEFAULT 0.00,
  `max_business` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mlm_commission_levels_backup`
--

INSERT INTO `mlm_commission_levels_backup` (`id`, `plan_id`, `level`, `direct_percentage`, `min_business`, `max_business`, `created_at`) VALUES
(40, 1, 1, 5.00, 0.00, 500000.00, '2025-06-30 09:57:06'),
(41, 1, 2, 6.00, 500001.00, 1000000.00, '2025-06-30 09:57:06'),
(42, 1, 3, 7.00, 1000001.00, 2000000.00, '2025-06-30 09:57:06'),
(43, 1, 4, 8.00, 2000001.00, 3000000.00, '2025-06-30 09:57:06'),
(44, 1, 5, 9.00, 3000001.00, 4000000.00, '2025-06-30 09:57:06'),
(45, 1, 6, 10.00, 4000001.00, 5000000.00, '2025-06-30 09:57:06'),
(46, 1, 7, 11.00, 5000001.00, 7500000.00, '2025-06-30 09:57:06'),
(47, 1, 8, 12.00, 7500001.00, 10000000.00, '2025-06-30 09:57:06'),
(48, 1, 9, 13.00, 10000001.00, 15000000.00, '2025-06-30 09:57:06'),
(49, 1, 10, 14.00, 15000001.00, 20000000.00, '2025-06-30 09:57:06'),
(50, 1, 11, 15.00, 20000001.00, 30000000.00, '2025-06-30 09:57:06'),
(51, 1, 12, 16.00, 30000001.00, 50000000.00, '2025-06-30 09:57:06'),
(52, 1, 13, 17.00, 50000001.00, NULL, '2025-06-30 09:57:06');

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commission_plans`
--

CREATE TABLE `mlm_commission_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mlm_commission_plans`
--

INSERT INTO `mlm_commission_plans` (`id`, `name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Standard Plan', 'Standard MLM commission structure with 13 levels', 1, '2025-06-30 09:31:13');

-- --------------------------------------------------------

--
-- Table structure for table `mlm_tree`
--

CREATE TABLE `mlm_tree` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `join_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_devices`
--

CREATE TABLE `mobile_devices` (
  `id` int(11) NOT NULL,
  `device_user` varchar(255) NOT NULL,
  `push_token` varchar(255) DEFAULT NULL,
  `platform` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `summary` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `created_at`, `read_at`) VALUES
(11, 189, 'Test Notification: This is a test notification 2025-08-05 21:26:12', 'test', '2025-08-05 19:26:12', NULL),
(12, 189, 'Test Notification: This is a test notification 2025-08-05 21:26:31', 'test', '2025-08-05 19:26:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `email_enabled` tinyint(1) DEFAULT 1,
  `push_enabled` tinyint(1) DEFAULT 1,
  `sms_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title_template` text NOT NULL,
  `message_template` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `type`, `title_template`, `message_template`, `created_at`, `updated_at`) VALUES
(7, 'user_registration', 'Welcome to APS Dream Home!', 'Hello {user_name}, thank you for registering with APS Dream Home. Your account has been created successfully.', '2025-08-05 18:29:58', NULL),
(8, 'email_verification', 'Verify Your Email Address', 'Hello {user_name}, please click the link below to verify your email address: {verification_link}', '2025-08-05 18:29:58', NULL),
(9, 'password_reset', 'Password Reset Request', 'Hello {user_name}, we received a request to reset your password. Click the link below to proceed: {reset_link}', '2025-08-05 18:29:58', NULL),
(10, 'property_added', 'New Property Added', 'Hello {user_name}, your property \"{property_title}\" has been added successfully and is now live on our platform.', '2025-08-05 18:29:58', NULL),
(11, 'appointment_scheduled', 'Appointment Scheduled', 'Hello {user_name}, your appointment for {property_title} has been scheduled for {appointment_date} at {appointment_time}.', '2025-08-05 18:29:58', NULL),
(12, 'payment_received', 'Payment Received', 'Thank you for your payment of {amount} for {service}. Transaction ID: {transaction_id}', '2025-08-05 18:29:58', NULL),
(13, 'admin_alert', 'Admin Alert: {alert_type}', '{alert_message}', '2025-08-05 18:29:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `stage` varchar(50) DEFAULT NULL,
  `value` decimal(12,2) DEFAULT NULL,
  `expected_close` date DEFAULT NULL,
  `status` enum('open','won','lost') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partner_certification`
--

CREATE TABLE `partner_certification` (
  `id` int(11) NOT NULL,
  `partner_name` varchar(255) DEFAULT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `cert_status` varchar(50) DEFAULT 'pending',
  `revenue_share` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partner_rewards`
--

CREATE TABLE `partner_rewards` (
  `id` int(11) NOT NULL,
  `partner_email` varchar(255) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateway_config`
--

CREATE TABLE `payment_gateway_config` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `action` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plots`
--

CREATE TABLE `plots` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `plot_no` varchar(50) NOT NULL,
  `size_sqft` decimal(10,2) DEFAULT NULL,
  `status` enum('available','booked','sold','rented','resale') DEFAULT 'available',
  `customer_id` int(11) DEFAULT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plots`
--

INSERT INTO `plots` (`id`, `project_id`, `plot_no`, `size_sqft`, `status`, `customer_id`, `associate_id`, `sale_id`, `created_at`) VALUES
(1, 12, 'A-101', 2000.00, 'available', NULL, NULL, NULL, '2025-05-27 11:56:44');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `builder_id` int(11) DEFAULT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `brochure_path` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `brochure_drive_id` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_amenities`
--

CREATE TABLE `project_amenities` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `icon_path` varchar(255) NOT NULL,
  `label` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_categories`
--

CREATE TABLE `project_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_category_relations`
--

CREATE TABLE `project_category_relations` (
  `project_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_gallery`
--

CREATE TABLE `project_gallery` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `drive_file_id` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `area` decimal(10,2) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `pcontent` text DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `bhk` varchar(20) DEFAULT NULL,
  `stype` varchar(20) DEFAULT NULL,
  `bedroom` int(11) DEFAULT NULL,
  `bathroom` int(11) DEFAULT NULL,
  `balcony` int(11) DEFAULT NULL,
  `kitchen` int(11) DEFAULT NULL,
  `hall` int(11) DEFAULT NULL,
  `floor` varchar(20) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `feature` text DEFAULT NULL,
  `pimage` varchar(255) DEFAULT NULL,
  `pimage1` varchar(255) DEFAULT NULL,
  `pimage2` varchar(255) DEFAULT NULL,
  `pimage3` varchar(255) DEFAULT NULL,
  `pimage4` varchar(255) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `mapimage` varchar(255) DEFAULT NULL,
  `topmapimage` varchar(255) DEFAULT NULL,
  `groundmapimage` varchar(255) DEFAULT NULL,
  `totalfloor` varchar(20) DEFAULT NULL,
  `isFeatured` tinyint(1) DEFAULT NULL,
  `pimage_drive_id` varchar(128) DEFAULT NULL,
  `pimage1_drive_id` varchar(128) DEFAULT NULL,
  `pimage2_drive_id` varchar(128) DEFAULT NULL,
  `pimage3_drive_id` varchar(128) DEFAULT NULL,
  `pimage4_drive_id` varchar(128) DEFAULT NULL,
  `mapimage_drive_id` varchar(128) DEFAULT NULL,
  `topmapimage_drive_id` varchar(128) DEFAULT NULL,
  `groundmapimage_drive_id` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_features`
--

CREATE TABLE `property_features` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_feature_mappings`
--

CREATE TABLE `property_feature_mappings` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_ownership`
--

CREATE TABLE `property_ownership` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `share` decimal(5,2) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_types`
--

CREATE TABLE `property_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_visits`
--

CREATE TABLE `property_visits` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `visit_time` time DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rental_properties`
--

CREATE TABLE `rental_properties` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `rent_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('available','rented','inactive') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rent_payments`
--

CREATE TABLE `rent_payments` (
  `id` int(11) NOT NULL,
  `rental_property_id` int(11) DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `generated_for_month` int(11) NOT NULL,
  `generated_for_year` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resale_commissions`
--

CREATE TABLE `resale_commissions` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `resale_property_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `paid_on` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resale_properties`
--

CREATE TABLE `resale_properties` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `status` enum('available','sold','inactive') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reward_history`
--

CREATE TABLE `reward_history` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `reward_type` varchar(50) DEFAULT NULL,
  `reward_value` decimal(12,2) DEFAULT NULL,
  `reward_date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_change_approvals`
--

CREATE TABLE `role_change_approvals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `action` enum('assign','remove') NOT NULL,
  `requested_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saas_instances`
--

CREATE TABLE `saas_instances` (
  `id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salaries`
--

CREATE TABLE `salaries` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `paid_on` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_plan`
--

CREATE TABLE `salary_plan` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `salary_amount` decimal(12,2) DEFAULT NULL,
  `payout_date` date DEFAULT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_searches`
--

CREATE TABLE `saved_searches` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `search_params` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_name`, `value`, `created_at`, `updated_at`) VALUES
(7, 'header_menu_items', '[{\"url\":\"/apsdreamhomefinal/\",\"text\":\"Home\"},{\"url\":\"/apsdreamhomefinal/project.php\",\"text\":\"Project\"},{\"url\":\"/apsdreamhomefinal/about.php\",\"text\":\"About\"},{\"url\":\"/apsdreamhomefinal/contact.php\",\"text\":\"Contact\"},{\"url\":\"/apsdreamhomefinal/login.php\",\"text\":\"Login\"}]', '2025-06-28 06:50:17', '2025-06-28 06:50:17'),
(8, 'site_logo', '/apsdreamhomefinal/assets/images/logo.png', '2025-06-28 06:50:17', '2025-06-28 06:50:17'),
(9, 'header_styles', '{\"background\":\"#ffffff\",\"text_color\":\"#333333\"}', '2025-06-28 06:50:17', '2025-06-28 06:50:17');

-- --------------------------------------------------------

--
-- Table structure for table `smart_contracts`
--

CREATE TABLE `smart_contracts` (
  `id` int(11) NOT NULL,
  `agreement_name` varchar(255) NOT NULL,
  `parties` varchar(255) DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `blockchain_txn` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE `state` (
  `sid` int(11) NOT NULL,
  `sname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_hierarchy`
--

CREATE TABLE `team_hierarchy` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `upline_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `testimonial` text NOT NULL,
  `client_photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `third_party_integrations`
--

CREATE TABLE `third_party_integrations` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `api_token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `description` text DEFAULT NULL,
  `ref_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `date`, `description`, `ref_id`, `created_at`, `updated_at`) VALUES
(22, 190, 'sale', 100000.00, '2025-06-30', 'Test commission calculation', NULL, '2025-06-30 09:44:56', '2025-06-30 09:44:56'),
(23, 190, 'sale', 100000.00, '2025-06-30', 'Test commission calculation', NULL, '2025-06-30 09:50:03', '2025-06-30 09:50:03'),
(24, 190, 'sale', 100000.00, '2025-06-30', 'Test commission calculation', NULL, '2025-06-30 09:52:35', '2025-06-30 09:52:35');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_associates`
--

CREATE TABLE `transaction_associates` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_associates`
--

INSERT INTO `transaction_associates` (`id`, `transaction_id`, `associate_id`, `created_at`) VALUES
(1, 22, 18, '2025-06-30 09:44:56'),
(2, 23, 18, '2025-06-30 09:50:03'),
(3, 24, 18, '2025-06-30 09:52:35'),
(4, 24, 18, '2025-06-30 09:52:35');

-- --------------------------------------------------------

--
-- Table structure for table `upload_audit_log`
--

CREATE TABLE `upload_audit_log` (
  `id` int(11) NOT NULL,
  `event_type` varchar(64) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `entity_table` varchar(64) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `drive_file_id` varchar(128) DEFAULT NULL,
  `uploader` varchar(128) NOT NULL,
  `slack_status` varchar(32) DEFAULT NULL,
  `telegram_status` varchar(32) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `type` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `api_access` tinyint(1) DEFAULT 0,
  `api_rate_limit` int(11) DEFAULT 1000,
  `google2fa_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `profile_picture`, `phone`, `type`, `password`, `status`, `created_at`, `updated_at`, `api_access`, `api_rate_limit`, `google2fa_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`) VALUES
(189, 'Test Upline', 'upline@test.com', NULL, '9000000001', 'associate', '$2y$10$ffZiJHW5d4yvw8x5RSOMwOx5ex41/BC5Rd.Q8Khcv8UpMv8Kpw74K', 'active', '2025-06-30 09:42:05', '2025-06-30 09:42:05', 0, 1000, NULL, NULL, NULL),
(190, 'Test Associate 1', 'associate1@test.com', NULL, '9000000002', 'associate', '$2y$10$wdyzAfvuCtXi2HFdNLEP3uY8xXZR5YCbdUkGTJCPriY/pkYo2MZ6a', 'active', '2025-06-30 09:42:05', '2025-06-30 09:42:05', 0, 1000, NULL, NULL, NULL),
(191, 'Test Associate 2', 'associate2@test.com', NULL, '9000000003', 'associate', '$2y$10$XsawfFmBpgqaHk5yYVonrO3UQUOs6HS3ZUP/oLxWGf6qvuraaBt5K', 'active', '2025-06-30 09:42:05', '2025-06-30 09:42:05', 0, 1000, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('active','ended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_social_accounts`
--

CREATE TABLE `user_social_accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `provider_id` varchar(255) NOT NULL,
  `token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visit_availability`
--

CREATE TABLE `visit_availability` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL CHECK (`day_of_week` between 0 and 6),
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_visits_per_slot` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visit_reminders`
--

CREATE TABLE `visit_reminders` (
  `id` int(11) NOT NULL,
  `visit_id` int(11) NOT NULL,
  `reminder_type` enum('24h_before','1h_before','feedback_request') NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voice_assistant_config`
--

CREATE TABLE `voice_assistant_config` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_automation_config`
--

CREATE TABLE `whatsapp_automation_config` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `sender_number` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflows`
--

CREATE TABLE `workflows` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `definition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`definition`)),
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_automations`
--

CREATE TABLE `workflow_automations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_activity_user` (`user_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `auser` (`auser`);

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ai_chatbot_config`
--
ALTER TABLE `ai_chatbot_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ai_chatbot_interactions`
--
ALTER TABLE `ai_chatbot_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ai_config`
--
ALTER TABLE `ai_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `ai_lead_scores`
--
ALTER TABLE `ai_lead_scores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ai_logs`
--
ALTER TABLE `ai_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `api_developers`
--
ALTER TABLE `api_developers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_integrations`
--
ALTER TABLE `api_integrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `api_rate_limits`
--
ALTER TABLE `api_rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `api_key` (`api_key`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `api_key_timestamp` (`api_key`,`timestamp`);

--
-- Indexes for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `api_key_id` (`api_key_id`),
  ADD KEY `request_time` (`request_time`);

--
-- Indexes for table `api_sandbox`
--
ALTER TABLE `api_sandbox`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_usage`
--
ALTER TABLE `api_usage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_store`
--
ALTER TABLE `app_store`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ar_vr_tours`
--
ALTER TABLE `ar_vr_tours`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `associates`
--
ALTER TABLE `associates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `level` (`level`),
  ADD KEY `fk_associate_plan` (`commission_plan_id`);

--
-- Indexes for table `associate_levels`
--
ALTER TABLE `associate_levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `audit_access_log`
--
ALTER TABLE `audit_access_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `bookings_ibfk_3` (`customer_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`cid`),
  ADD KEY `sid` (`sid`);

--
-- Indexes for table `commission_payouts`
--
ALTER TABLE `commission_payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `associate_id` (`associate_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `commission_transactions`
--
ALTER TABLE `commission_transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `communications`
--
ALTER TABLE `communications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_employees`
--
ALTER TABLE `company_employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `customer_documents`
--
ALTER TABLE `customer_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_journeys`
--
ALTER TABLE `customer_journeys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_stream_events`
--
ALTER TABLE `data_stream_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `emi`
--
ALTER TABLE `emi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `emi_installments`
--
ALTER TABLE `emi_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_emi_plan` (`emi_plan_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `emi_plans`
--
ALTER TABLE `emi_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_property` (`property_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback_tickets`
--
ALTER TABLE `feedback_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `foreclosure_logs`
--
ALTER TABLE `foreclosure_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attempted_by` (`attempted_by`),
  ADD KEY `idx_emi_plan` (`emi_plan_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_attempted_at` (`attempted_at`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `global_payments`
--
ALTER TABLE `global_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iot_devices`
--
ALTER TABLE `iot_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iot_device_events`
--
ALTER TABLE `iot_device_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jwt_blacklist`
--
ALTER TABLE `jwt_blacklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`(255)),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `land_purchases`
--
ALTER TABLE `land_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `legal_documents`
--
ALTER TABLE `legal_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketing_strategies`
--
ALTER TABLE `marketing_strategies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketplace_apps`
--
ALTER TABLE `marketplace_apps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_migration` (`version`,`migration_name`);

--
-- Indexes for table `mlm_commissions`
--
ALTER TABLE `mlm_commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_commission_plan` (`commission_plan_id`),
  ADD KEY `fk_commission_upline` (`upline_id`);

--
-- Indexes for table `mlm_commission_ledger`
--
ALTER TABLE `mlm_commission_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `associate_id` (`associate_id`);

--
-- Indexes for table `mlm_commission_levels`
--
ALTER TABLE `mlm_commission_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_level` (`plan_id`,`level`);

--
-- Indexes for table `mlm_commission_plans`
--
ALTER TABLE `mlm_commission_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mlm_tree`
--
ALTER TABLE `mlm_tree`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `mobile_devices`
--
ALTER TABLE `mobile_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_id` (`notification_id`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_type` (`user_id`,`type`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type` (`type`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partner_certification`
--
ALTER TABLE `partner_certification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partner_rewards`
--
ALTER TABLE `partner_rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_gateway_config`
--
ALTER TABLE `payment_gateway_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plots`
--
ALTER TABLE `plots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `associate_id` (`associate_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_amenities`
--
ALTER TABLE `project_amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `project_category_relations`
--
ALTER TABLE `project_category_relations`
  ADD PRIMARY KEY (`project_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `project_gallery`
--
ALTER TABLE `project_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property_features`
--
ALTER TABLE `property_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property_feature_mappings`
--
ALTER TABLE `property_feature_mappings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `property_feature` (`property_id`,`feature_id`),
  ADD KEY `feature_id` (`feature_id`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`,`is_primary`,`sort_order`);

--
-- Indexes for table `property_ownership`
--
ALTER TABLE `property_ownership`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `property_types`
--
ALTER TABLE `property_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `property_visits`
--
ALTER TABLE `property_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `lead_id` (`lead_id`);

--
-- Indexes for table `rental_properties`
--
ALTER TABLE `rental_properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `rent_payments`
--
ALTER TABLE `rent_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_property_id` (`rental_property_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resale_commissions`
--
ALTER TABLE `resale_commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `associate_id` (`associate_id`),
  ADD KEY `resale_property_id` (`resale_property_id`);

--
-- Indexes for table `resale_properties`
--
ALTER TABLE `resale_properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `reward_history`
--
ALTER TABLE `reward_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_change_approvals`
--
ALTER TABLE `role_change_approvals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saas_instances`
--
ALTER TABLE `saas_instances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `salary_plan`
--
ALTER TABLE `salary_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saved_searches`
--
ALTER TABLE `saved_searches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `smart_contracts`
--
ALTER TABLE `smart_contracts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`sid`),
  ADD UNIQUE KEY `sname` (`sname`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_hierarchy`
--
ALTER TABLE `team_hierarchy`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `third_party_integrations`
--
ALTER TABLE `third_party_integrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transaction_associates`
--
ALTER TABLE `transaction_associates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `associate_id` (`associate_id`);

--
-- Indexes for table `upload_audit_log`
--
ALTER TABLE `upload_audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_profile_picture` (`profile_picture`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_preference` (`user_id`,`preference_key`),
  ADD KEY `idx_user_preferences_key` (`preference_key`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_social_accounts`
--
ALTER TABLE `user_social_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_provider` (`provider`,`provider_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `visit_availability`
--
ALTER TABLE `visit_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_availability` (`property_id`,`day_of_week`,`start_time`);

--
-- Indexes for table `visit_reminders`
--
ALTER TABLE `visit_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visit_id` (`visit_id`),
  ADD KEY `idx_reminder_status` (`status`,`scheduled_at`);

--
-- Indexes for table `voice_assistant_config`
--
ALTER TABLE `voice_assistant_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `whatsapp_automation_config`
--
ALTER TABLE `whatsapp_automation_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workflows`
--
ALTER TABLE `workflows`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workflow_automations`
--
ALTER TABLE `workflow_automations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_chatbot_config`
--
ALTER TABLE `ai_chatbot_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_chatbot_interactions`
--
ALTER TABLE `ai_chatbot_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_config`
--
ALTER TABLE `ai_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_lead_scores`
--
ALTER TABLE `ai_lead_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_logs`
--
ALTER TABLE `ai_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `api_developers`
--
ALTER TABLE `api_developers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `api_integrations`
--
ALTER TABLE `api_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `api_rate_limits`
--
ALTER TABLE `api_rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `api_sandbox`
--
ALTER TABLE `api_sandbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `api_usage`
--
ALTER TABLE `api_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `app_store`
--
ALTER TABLE `app_store`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ar_vr_tours`
--
ALTER TABLE `ar_vr_tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `associates`
--
ALTER TABLE `associates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `associate_levels`
--
ALTER TABLE `associate_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `audit_access_log`
--
ALTER TABLE `audit_access_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `city`
--
ALTER TABLE `city`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `commission_payouts`
--
ALTER TABLE `commission_payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `commission_transactions`
--
ALTER TABLE `commission_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `communications`
--
ALTER TABLE `communications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `company_employees`
--
ALTER TABLE `company_employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customer_documents`
--
ALTER TABLE `customer_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customer_journeys`
--
ALTER TABLE `customer_journeys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_stream_events`
--
ALTER TABLE `data_stream_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `emi`
--
ALTER TABLE `emi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `emi_installments`
--
ALTER TABLE `emi_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emi_plans`
--
ALTER TABLE `emi_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `feedback_tickets`
--
ALTER TABLE `feedback_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `foreclosure_logs`
--
ALTER TABLE `foreclosure_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `global_payments`
--
ALTER TABLE `global_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `iot_devices`
--
ALTER TABLE `iot_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `iot_device_events`
--
ALTER TABLE `iot_device_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jwt_blacklist`
--
ALTER TABLE `jwt_blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `land_purchases`
--
ALTER TABLE `land_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `legal_documents`
--
ALTER TABLE `legal_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `marketing_strategies`
--
ALTER TABLE `marketing_strategies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `marketplace_apps`
--
ALTER TABLE `marketplace_apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mlm_commissions`
--
ALTER TABLE `mlm_commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mlm_commission_ledger`
--
ALTER TABLE `mlm_commission_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mlm_commission_levels`
--
ALTER TABLE `mlm_commission_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mlm_commission_plans`
--
ALTER TABLE `mlm_commission_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mlm_tree`
--
ALTER TABLE `mlm_tree`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mobile_devices`
--
ALTER TABLE `mobile_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `partner_certification`
--
ALTER TABLE `partner_certification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `partner_rewards`
--
ALTER TABLE `partner_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_gateway_config`
--
ALTER TABLE `payment_gateway_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `plots`
--
ALTER TABLE `plots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `project_amenities`
--
ALTER TABLE `project_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_gallery`
--
ALTER TABLE `project_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `property_features`
--
ALTER TABLE `property_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_feature_mappings`
--
ALTER TABLE `property_feature_mappings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_ownership`
--
ALTER TABLE `property_ownership`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `property_types`
--
ALTER TABLE `property_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `property_visits`
--
ALTER TABLE `property_visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rental_properties`
--
ALTER TABLE `rental_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rent_payments`
--
ALTER TABLE `rent_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resale_commissions`
--
ALTER TABLE `resale_commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `resale_properties`
--
ALTER TABLE `resale_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reward_history`
--
ALTER TABLE `reward_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `role_change_approvals`
--
ALTER TABLE `role_change_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `saas_instances`
--
ALTER TABLE `saas_instances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `salary_plan`
--
ALTER TABLE `salary_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `saved_searches`
--
ALTER TABLE `saved_searches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `smart_contracts`
--
ALTER TABLE `smart_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `state`
--
ALTER TABLE `state`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `team_hierarchy`
--
ALTER TABLE `team_hierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `third_party_integrations`
--
ALTER TABLE `third_party_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `transaction_associates`
--
ALTER TABLE `transaction_associates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `upload_audit_log`
--
ALTER TABLE `upload_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=192;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_social_accounts`
--
ALTER TABLE `user_social_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visit_availability`
--
ALTER TABLE `visit_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

--
-- AUTO_INCREMENT for table `visit_reminders`
--
ALTER TABLE `visit_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `voice_assistant_config`
--
ALTER TABLE `voice_assistant_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `whatsapp_automation_config`
--
ALTER TABLE `whatsapp_automation_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `workflows`
--
ALTER TABLE `workflows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workflow_automations`
--
ALTER TABLE `workflow_automations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ai_chatbot_interactions`
--
ALTER TABLE `ai_chatbot_interactions`
  ADD CONSTRAINT `ai_chatbot_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ai_config`
--
ALTER TABLE `ai_config`
  ADD CONSTRAINT `ai_config_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `ai_logs`
--
ALTER TABLE `ai_logs`
  ADD CONSTRAINT `fk_ai_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  ADD CONSTRAINT `api_request_logs_ibfk_1` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `associates`
--
ALTER TABLE `associates`
  ADD CONSTRAINT `associates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `associates_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `associates` (`id`),
  ADD CONSTRAINT `associates_ibfk_3` FOREIGN KEY (`level`) REFERENCES `associate_levels` (`id`),
  ADD CONSTRAINT `fk_associate_plan` FOREIGN KEY (`commission_plan_id`) REFERENCES `mlm_commission_plans` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `city`
--
ALTER TABLE `city`
  ADD CONSTRAINT `city_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `state` (`sid`);

--
-- Constraints for table `commission_payouts`
--
ALTER TABLE `commission_payouts`
  ADD CONSTRAINT `commission_payouts_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`),
  ADD CONSTRAINT `commission_payouts_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`);

--
-- Constraints for table `company_employees`
--
ALTER TABLE `company_employees`
  ADD CONSTRAINT `company_employees_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `company_employees_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `emi`
--
ALTER TABLE `emi`
  ADD CONSTRAINT `emi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `emi_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Constraints for table `farmers`
--
ALTER TABLE `farmers`
  ADD CONSTRAINT `farmers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `foreclosure_logs`
--
ALTER TABLE `foreclosure_logs`
  ADD CONSTRAINT `foreclosure_logs_ibfk_1` FOREIGN KEY (`emi_plan_id`) REFERENCES `emi_plans` (`id`),
  ADD CONSTRAINT `foreclosure_logs_ibfk_2` FOREIGN KEY (`attempted_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `land_purchases`
--
ALTER TABLE `land_purchases`
  ADD CONSTRAINT `land_purchases_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`),
  ADD CONSTRAINT `land_purchases_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mlm_commissions`
--
ALTER TABLE `mlm_commissions`
  ADD CONSTRAINT `fk_commission_plan` FOREIGN KEY (`commission_plan_id`) REFERENCES `mlm_commission_plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_commission_upline` FOREIGN KEY (`upline_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mlm_commission_ledger`
--
ALTER TABLE `mlm_commission_ledger`
  ADD CONSTRAINT `mlm_commission_ledger_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`);

--
-- Constraints for table `mlm_commission_levels`
--
ALTER TABLE `mlm_commission_levels`
  ADD CONSTRAINT `fk_plan_levels` FOREIGN KEY (`plan_id`) REFERENCES `mlm_commission_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mlm_tree`
--
ALTER TABLE `mlm_tree`
  ADD CONSTRAINT `mlm_tree_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `mlm_tree_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD CONSTRAINT `notification_logs_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD CONSTRAINT `payment_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `plots`
--
ALTER TABLE `plots`
  ADD CONSTRAINT `plots_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plots_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `plots_ibfk_3` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_amenities`
--
ALTER TABLE `project_amenities`
  ADD CONSTRAINT `project_amenities_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD CONSTRAINT `project_categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_category_relations`
--
ALTER TABLE `project_category_relations`
  ADD CONSTRAINT `project_category_relations_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_category_relations_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `project_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_gallery`
--
ALTER TABLE `project_gallery`
  ADD CONSTRAINT `project_gallery_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `property_types` (`id`),
  ADD CONSTRAINT `properties_ibfk_3` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `property_feature_mappings`
--
ALTER TABLE `property_feature_mappings`
  ADD CONSTRAINT `property_feature_mappings_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_feature_mappings_ibfk_2` FOREIGN KEY (`feature_id`) REFERENCES `property_features` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_ownership`
--
ALTER TABLE `property_ownership`
  ADD CONSTRAINT `property_ownership_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `property_ownership_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `property_ownership_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `property_visits`
--
ALTER TABLE `property_visits`
  ADD CONSTRAINT `property_visits_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `property_visits_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `property_visits_ibfk_3` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`);

--
-- Constraints for table `rental_properties`
--
ALTER TABLE `rental_properties`
  ADD CONSTRAINT `rental_properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rent_payments`
--
ALTER TABLE `rent_payments`
  ADD CONSTRAINT `rent_payments_ibfk_1` FOREIGN KEY (`rental_property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `rent_payments_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `resale_commissions`
--
ALTER TABLE `resale_commissions`
  ADD CONSTRAINT `resale_commissions_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`),
  ADD CONSTRAINT `resale_commissions_ibfk_2` FOREIGN KEY (`resale_property_id`) REFERENCES `properties` (`id`);

--
-- Constraints for table `resale_properties`
--
ALTER TABLE `resale_properties`
  ADD CONSTRAINT `resale_properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `salaries`
--
ALTER TABLE `salaries`
  ADD CONSTRAINT `salaries_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `saved_searches`
--
ALTER TABLE `saved_searches`
  ADD CONSTRAINT `saved_searches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transaction_associates`
--
ALTER TABLE `transaction_associates`
  ADD CONSTRAINT `transaction_associates_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_associates_ibfk_2` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_social_accounts`
--
ALTER TABLE `user_social_accounts`
  ADD CONSTRAINT `user_social_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visit_availability`
--
ALTER TABLE `visit_availability`
  ADD CONSTRAINT `visit_availability_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visit_reminders`
--
ALTER TABLE `visit_reminders`
  ADD CONSTRAINT `visit_reminders_ibfk_1` FOREIGN KEY (`visit_id`) REFERENCES `property_visits` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
