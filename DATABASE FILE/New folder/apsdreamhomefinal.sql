-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2025 at 07:40 AM
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
  `user_id` int(11) DEFAULT NULL,
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

--
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `admin_id`, `username`, `role`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 'admin', 'admin', 'admin_login', 'Login success: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-04-24 18:03:16'),
(2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-17 12:33:21'),
(6, 1, 'Value for username 1', 'Value for role 1', 'Value for action 1', 'Value for details 1', 'Delhi', 'Value for user_agent 1', '2025-04-30 18:30:00'),
(7, 2, 'Value for username 2', 'Value for role 2', 'Value for action 2', 'Value for details 2', 'Mumbai', 'Value for user_agent 2', '2025-05-06 18:30:00'),
(8, 3, 'Value for username 3', 'Value for role 3', 'Value for action 3', 'Value for details 3', 'Bangalore', 'Value for user_agent 3', '2025-05-12 18:30:00'),
(9, 4, 'Value for username 4', 'Value for role 4', 'Value for action 4', 'Value for details 4', 'Ahmedabad', 'Value for user_agent 4', '2025-05-18 18:30:00'),
(10, 5, 'Value for username 5', 'Value for role 5', 'Value for action 5', 'Value for details 5', 'Pune', 'Value for user_agent 5', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `ai_chatbot_interactions`
--

INSERT INTO `ai_chatbot_interactions` (`id`, `user_id`, `query`, `response`, `satisfaction_score`, `response_time`, `created_at`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, '2025-05-17 12:33:21'),
(6, 1, 'Value for query 1', 'Value for response 1', 9.9, 999.99, '2025-04-30 18:30:00'),
(7, 2, 'Value for query 2', 'Value for response 2', 9.9, 999.99, '2025-05-06 18:30:00'),
(8, 3, 'Value for query 3', 'Value for response 3', 9.9, 999.99, '2025-05-12 18:30:00'),
(9, 4, 'Value for query 4', 'Value for response 4', 9.9, 999.99, '2025-05-18 18:30:00'),
(10, 5, 'Value for query 5', 'Value for response 5', 9.9, 999.99, '2025-05-24 18:30:00');

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

--
-- Dumping data for table `ai_config`
--

INSERT INTO `ai_config` (`id`, `feature`, `enabled`, `config_json`, `updated_by`, `updated_at`) VALUES
(1, NULL, 1, NULL, NULL, '2025-05-17 12:33:21'),
(6, 'Value for feature 1', 1, 'Value for config_json 1', 1, '2025-04-30 18:30:00'),
(7, 'Value for feature 2', 2, 'Value for config_json 2', 2, '2025-05-06 18:30:00'),
(8, 'Value for feature 3', 3, 'Value for config_json 3', 3, '2025-05-12 18:30:00'),
(9, 'Value for feature 4', 4, 'Value for config_json 4', 4, '2025-05-18 18:30:00'),
(10, 'Value for feature 5', 5, 'Value for config_json 5', 5, '2025-05-24 18:30:00');

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
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `input_text` text DEFAULT NULL,
  `ai_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_logs`
--

INSERT INTO `ai_logs` (`id`, `user_id`, `action`, `input_text`, `ai_response`, `created_at`) VALUES
(1, 1, 'chat', 'What is the price of Plot 101?', 'The price is 15,00,000 INR.', '2025-04-21 20:54:15'),
(2, 2, 'chat', 'Show my bookings.', 'You have 1 booking for Plot 101.', '2025-04-21 20:54:15'),
(3, NULL, NULL, NULL, NULL, '2025-05-17 12:33:21'),
(6, 1, 'Value for action 1', 'This is a sample message for record 1.', 'Value for ai_response 1', '2025-04-30 18:30:00'),
(7, 2, 'Value for action 2', 'This is a sample message for record 2.', 'Value for ai_response 2', '2025-05-06 18:30:00'),
(8, 3, 'Value for action 3', 'This is a sample message for record 3.', 'Value for ai_response 3', '2025-05-12 18:30:00'),
(9, 4, 'Value for action 4', 'This is a sample message for record 4.', 'Value for ai_response 4', '2025-05-18 18:30:00'),
(10, 5, 'Value for action 5', 'This is a sample message for record 5.', 'Value for ai_response 5', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `user_id`, `api_key`, `name`, `permissions`, `rate_limit`, `status`, `created_at`, `updated_at`, `last_used_at`) VALUES
(1, 1, '7f4b2a1e9d8c3b6a5f0e7d9c8b3a2f1', 'Admin API Key', '[\"*\"]', 1000, 'active', '2025-05-25 10:52:15', NULL, NULL);

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

--
-- Dumping data for table `api_request_logs`
--

INSERT INTO `api_request_logs` (`id`, `api_key_id`, `endpoint`, `request_time`, `ip_address`, `user_agent`) VALUES
(1, 1, '/apsdreamhomefinal/api/v1/test.php?api_key=7f4b2a1e9d8c3b6a5f0e7d9c8b3a2f1', '2025-05-25 10:56:38', '::1', 'curl/8.9.1'),
(2, 1, '/apsdreamhomefinal/api/v1/test.php?api_key=7f4b2a1e9d8c3b6a5f0e7d9c8b3a2f1', '2025-05-25 10:57:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0');

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
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `in_time`, `out_time`, `status`) VALUES
(1, 10, '2025-04-01', '09:00:00', '18:00:00', 'present'),
(2, 11, '2025-04-01', '09:15:00', '18:10:00', 'present'),
(3, NULL, NULL, NULL, NULL, 'present'),
(6, 1, '2025-05-01', '10:01:00', '10:01:00', ''),
(7, 2, '2025-05-07', '10:02:00', '10:02:00', ''),
(8, 3, '2025-05-13', '10:03:00', '10:03:00', ''),
(9, 4, '2025-05-19', '10:04:00', '10:04:00', ''),
(10, 5, '2025-05-25', '10:05:00', '10:05:00', '');

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
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'login', 'Super Admin logged in.', '2025-04-21 20:54:15'),
(2, 2, 'booking', 'Agent One booked Plot 101 for Customer One.', '2025-04-21 20:54:15'),
(3, NULL, NULL, NULL, '2025-05-17 12:33:21'),
(6, 1, 'Value for action 1', 'Value for details 1', '2025-04-30 18:30:00'),
(7, 2, 'Value for action 2', 'Value for details 2', '2025-05-06 18:30:00'),
(8, 3, 'Value for action 3', 'Value for details 3', '2025-05-12 18:30:00'),
(9, 4, 'Value for action 4', 'Value for details 4', '2025-05-18 18:30:00'),
(10, 5, 'Value for action 5', 'Value for details 5', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `plot_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `status` enum('booked','cancelled','completed') DEFAULT 'booked',
  `amount` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `property_id`, `plot_id`, `customer_id`, `associate_id`, `booking_date`, `status`, `amount`, `created_at`) VALUES
(1, 1, 1, 99, NULL, '2025-05-26', 'cancelled', 3650048.00, '2025-05-27 11:56:44'),
(2, 1, 1, 89, NULL, '2025-05-25', 'booked', 4203638.00, '2025-05-27 11:56:44'),
(3, 1, 1, 12, NULL, '2025-05-24', 'completed', 1529121.00, '2025-05-27 11:56:44'),
(4, 1, 1, 87, NULL, '2025-05-23', 'completed', 1015891.00, '2025-05-27 11:56:44'),
(5, 1, 1, 13, NULL, '2025-05-22', 'completed', 900776.00, '2025-05-27 11:56:44'),
(6, 1, 1, 147, NULL, '2025-05-21', 'cancelled', 122438.00, '2025-05-27 11:56:44'),
(7, 1, 1, 84, NULL, '2025-05-20', 'cancelled', 1245908.00, '2025-05-27 11:56:44'),
(8, 1, 1, 27, NULL, '2025-05-19', 'completed', 2969358.00, '2025-05-27 11:56:44'),
(9, 1, 1, 94, NULL, '2025-05-18', 'completed', 1221646.00, '2025-05-27 11:56:44'),
(10, 1, 1, 167, NULL, '2025-05-17', 'completed', 2850475.00, '2025-05-27 11:56:44'),
(11, 1, 1, 84, NULL, '2025-05-16', 'booked', 775738.00, '2025-05-27 11:56:44'),
(12, 1, 1, 6, NULL, '2025-05-15', 'booked', 3547483.00, '2025-05-27 11:56:44'),
(13, 1, 1, 166, NULL, '2025-05-14', 'cancelled', 4399869.00, '2025-05-27 11:56:44'),
(14, 1, 1, 106, NULL, '2025-05-13', 'booked', 4538395.00, '2025-05-27 11:56:44'),
(15, 1, 1, 79, NULL, '2025-05-12', 'booked', 1563396.00, '2025-05-27 11:56:44'),
(16, 1, 1, 149, NULL, '2025-05-11', 'booked', 771288.00, '2025-05-27 11:56:44'),
(17, 1, 1, 2, NULL, '2025-05-10', 'completed', 4266756.00, '2025-05-27 11:56:44'),
(18, 1, 1, 144, NULL, '2025-05-09', 'cancelled', 3463354.00, '2025-05-27 11:56:44'),
(19, 1, 1, 152, NULL, '2025-05-08', 'completed', 1295552.00, '2025-05-27 11:56:44'),
(20, 1, 1, 109, NULL, '2025-05-07', 'completed', 4328833.00, '2025-05-27 11:56:44');

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

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_email`, `message`, `created_at`) VALUES
(1, NULL, NULL, '2025-05-17 18:03:21'),
(6, 'rahul@example.com', 'This is a sample message for record 1.', '2025-05-01 00:00:00'),
(7, 'priya@example.com', 'This is a sample message for record 2.', '2025-05-07 00:00:00'),
(8, 'amit@example.com', 'This is a sample message for record 3.', '2025-05-13 00:00:00'),
(9, 'neha@example.com', 'This is a sample message for record 4.', '2025-05-19 00:00:00'),
(10, 'vikram@example.com', 'This is a sample message for record 5.', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `commission_payouts`
--

CREATE TABLE `commission_payouts` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payout_date` date NOT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
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

--
-- Dumping data for table `commission_transactions`
--

INSERT INTO `commission_transactions` (`transaction_id`, `associate_id`, `booking_id`, `business_amount`, `commission_amount`, `commission_percentage`, `level_difference_amount`, `upline_id`, `transaction_date`, `status`) VALUES
(1, 1, 1, 15000000.00, 15000000.00, 99.99, 15000000.00, 1, '2025-04-30 18:30:00', ''),
(2, 2, 2, 7000000.00, 7000000.00, 99.99, 7000000.00, 2, '2025-05-06 18:30:00', 'pending'),
(3, 3, 3, 9000000.00, 9000000.00, 99.99, 9000000.00, 3, '2025-05-12 18:30:00', ''),
(4, 4, 4, 20000000.00, 20000000.00, 99.99, 20000000.00, 4, '2025-05-18 18:30:00', 'cancelled'),
(5, 5, 5, 25000000.00, 25000000.00, 99.99, 25000000.00, 5, '2025-05-24 18:30:00', '');

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

--
-- Dumping data for table `communications`
--

INSERT INTO `communications` (`id`, `lead_id`, `type`, `subject`, `notes`, `communication_date`, `user_id`, `created_at`) VALUES
(1, 1, '', 'Value for subject 1', 'This is a sample message for record 1.', '2025-05-01 00:00:00', 1, '2025-04-30 18:30:00'),
(2, 2, '', 'Value for subject 2', 'This is a sample message for record 2.', '2025-05-07 00:00:00', 2, '2025-05-06 18:30:00'),
(3, 3, '', 'Value for subject 3', 'This is a sample message for record 3.', '2025-05-13 00:00:00', 3, '2025-05-12 18:30:00'),
(4, 4, '', 'Value for subject 4', 'This is a sample message for record 4.', '2025-05-19 00:00:00', 4, '2025-05-18 18:30:00'),
(5, 5, '', 'Value for subject 5', 'This is a sample message for record 5.', '2025-05-25 00:00:00', 5, '2025-05-24 18:30:00');

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

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `address`, `gstin`, `pan`, `created_at`) VALUES
(1, 'Dream Home Pvt Ltd', '123 Main Road, City', '22AAAAA0000A1Z5', 'AAAAA0000A', '2025-04-21 20:38:10'),
(2, 'Rahul Sharma', 'Delhi', 'Value for gstin 1', 'Value for pan 1', '2025-04-30 18:30:00'),
(3, 'Priya Singh', 'Mumbai', 'Value for gstin 2', 'Value for pan 2', '2025-05-06 18:30:00'),
(4, 'Amit Kumar', 'Bangalore', 'Value for gstin 3', 'Value for pan 3', '2025-05-12 18:30:00'),
(5, 'Neha Patel', 'Ahmedabad', 'Value for gstin 4', 'Value for pan 4', '2025-05-18 18:30:00'),
(6, 'Vikram Mehta', 'Pune', 'Value for gstin 5', 'Value for pan 5', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `company_employees`
--

INSERT INTO `company_employees` (`id`, `company_id`, `user_id`, `position`, `salary`, `join_date`, `status`) VALUES
(1, 1, 10, 'Manager', 50000.00, '2024-01-15', 'active'),
(2, 1, 11, 'Accountant', 30000.00, '2024-02-01', 'active'),
(3, 1, 1, 'Value for position 1', 1000.00, '2025-05-01', 'active'),
(4, 2, 2, 'Value for position 2', 2000.00, '2025-05-07', ''),
(5, 3, 3, 'Value for position 3', 3000.00, '2025-05-13', ''),
(6, 4, 4, 'Value for position 4', 4000.00, '2025-05-19', ''),
(7, 5, 5, 'Value for position 5', 5000.00, '2025-05-25', 'active');

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

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `customer_type`, `kyc_status`, `created_at`) VALUES
(1, NULL, NULL, NULL, '2025-05-17 20:06:37'),
(2, NULL, NULL, NULL, '2025-05-25 08:28:34');

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

--
-- Dumping data for table `customer_documents`
--

INSERT INTO `customer_documents` (`id`, `customer_id`, `doc_name`, `status`, `uploaded_at`, `blockchain_hash`) VALUES
(1, 1, 'Rahul Sharma', 'active', '2025-05-01 00:00:00', 'Value for blockchain_hash 1'),
(2, 2, 'Priya Singh', 'pending', '2025-05-07 00:00:00', 'Value for blockchain_hash 2'),
(3, 3, 'Amit Kumar', 'completed', '2025-05-13 00:00:00', 'Value for blockchain_hash 3'),
(4, 4, 'Neha Patel', 'cancelled', '2025-05-19 00:00:00', 'Value for blockchain_hash 4'),
(5, 5, 'Vikram Mehta', 'active', '2025-05-25 00:00:00', 'Value for blockchain_hash 5');

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
  `user_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `uploaded_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `drive_file_id` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `user_id`, `property_id`, `type`, `url`, `uploaded_on`, `drive_file_id`) VALUES
(1, 1, 1, 'agreement', 'docs/agreement1.pdf', '2025-04-21 20:54:15', NULL),
(2, 2, 2, 'kyc', 'docs/kyc_agent1.pdf', '2025-04-21 20:54:15', NULL),
(3, 1, 1, 'villa', 'Value for url 1', '0000-00-00 00:00:00', '1'),
(4, 2, 2, 'apartment', 'Value for url 2', '0000-00-00 00:00:00', '2'),
(5, 3, 3, 'house', 'Value for url 3', '0000-00-00 00:00:00', '3'),
(6, 4, 4, 'villa', 'Value for url 4', '0000-00-00 00:00:00', '4'),
(7, 5, 5, 'penthouse', 'Value for url 5', '0000-00-00 00:00:00', '5');

-- --------------------------------------------------------

--
-- Table structure for table `emi`
--

CREATE TABLE `emi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emi`
--

INSERT INTO `emi` (`id`, `user_id`, `property_id`, `amount`, `due_date`, `paid_date`, `status`) VALUES
(1, 1, 1, 15000000.00, '2025-05-01', '2025-05-01', ''),
(2, 2, 2, 7000000.00, '2025-05-07', '2025-05-07', 'pending'),
(3, 3, 3, 9000000.00, '2025-05-13', '2025-05-13', ''),
(4, 4, 4, 20000000.00, '2025-05-19', '2025-05-19', ''),
(5, 5, 5, 25000000.00, '2025-05-25', '2025-05-25', '');

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

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `phone`, `role`, `salary`, `join_date`, `status`, `password`, `created_at`) VALUES
(1, 'Demo Employee 1', 'demo.employee1@aps.com', '9000000001', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(2, 'Demo Employee 2', 'demo.employee2@aps.com', '9000000002', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(3, 'Demo Employee 3', 'demo.employee3@aps.com', '9000000003', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(4, 'Demo Employee 4', 'demo.employee4@aps.com', '9000000004', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(5, 'Demo Employee 5', 'demo.employee5@aps.com', '9000000005', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(6, 'Demo Employee 1', 'demo.employee1@aps.com', '9000000001', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(7, 'Demo Employee 2', 'demo.employee2@aps.com', '9000000002', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(8, 'Demo Employee 3', 'demo.employee3@aps.com', '9000000003', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(9, 'Demo Employee 4', 'demo.employee4@aps.com', '9000000004', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(10, 'Demo Employee 5', 'demo.employee5@aps.com', '9000000005', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(11, 'Demo Employee 1', 'demo.employee1@aps.com', '9000000001', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13'),
(12, 'Demo Employee 2', 'demo.employee2@aps.com', '9000000002', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13'),
(13, 'Demo Employee 3', 'demo.employee3@aps.com', '9000000003', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13'),
(14, 'Demo Employee 4', 'demo.employee4@aps.com', '9000000004', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13'),
(15, 'Demo Employee 5', 'demo.employee5@aps.com', '9000000005', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13');

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

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `user_id`, `amount`, `source`, `expense_date`, `description`, `created_at`) VALUES
(1, 1, 15000000.00, 'Value for source 1', '2025-05-01', 'Beautiful luxury villa with garden and pool', '2025-04-30 18:30:00'),
(2, 2, 7000000.00, 'Value for source 2', '2025-05-07', 'Modern apartment in city center with great amenities', '2025-05-06 18:30:00'),
(3, 3, 9000000.00, 'Value for source 3', '2025-05-13', 'Spacious family home in quiet neighborhood', '2025-05-12 18:30:00'),
(4, 4, 20000000.00, 'Value for source 4', '2025-05-19', 'Beachfront luxury home with amazing views', '2025-05-18 18:30:00'),
(5, 5, 25000000.00, 'Value for source 5', '2025-05-25', 'Luxury penthouse with terrace and city views', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`id`, `user_id`, `land_area`, `location`, `kyc_doc`) VALUES
(1, 4, 2000.00, 'Village Area', 'kyc123.pdf'),
(2, 12, 3000.00, 'Village B', 'kyc_farmer1.pdf'),
(3, 1, 1000.00, 'Value for location 1', 'Value for kyc_doc 1'),
(4, 2, 2000.00, 'Value for location 2', 'Value for kyc_doc 2'),
(5, 3, 3000.00, 'Value for location 3', 'Value for kyc_doc 3'),
(6, 4, 4000.00, 'Value for location 4', 'Value for kyc_doc 4'),
(7, 5, 5000.00, 'Value for location 5', 'Value for kyc_doc 5');

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

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `message`, `status`, `created_at`) VALUES
(1, NULL, NULL, 'new', '2025-05-17 12:21:22'),
(4, 1, 'This is a sample message for record 1.', 'active', '2025-04-30 18:30:00'),
(5, 2, 'This is a sample message for record 2.', 'pending', '2025-05-06 18:30:00'),
(6, 3, 'This is a sample message for record 3.', 'completed', '2025-05-12 18:30:00'),
(7, 4, 'This is a sample message for record 4.', 'cancelled', '2025-05-18 18:30:00'),
(8, 5, 'This is a sample message for record 5.', 'active', '2025-05-24 18:30:00'),
(9, NULL, NULL, '1', '2025-05-25 12:18:12'),
(10, NULL, NULL, '1', '2025-05-25 12:18:12');

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

--
-- Dumping data for table `feedback_tickets`
--

INSERT INTO `feedback_tickets` (`id`, `user_id`, `message`, `status`, `created_at`) VALUES
(1, 1, 'This is a sample message for record 1.', '', '2025-05-01 00:00:00'),
(2, 2, 'This is a sample message for record 2.', '', '2025-05-07 00:00:00'),
(3, 3, 'This is a sample message for record 3.', '', '2025-05-13 00:00:00'),
(4, 4, 'This is a sample message for record 4.', '', '2025-05-19 00:00:00'),
(5, 5, 'This is a sample message for record 5.', '', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `image_path`, `caption`, `status`, `created_at`) VALUES
(1, '/assets/images/gallery/project1.jpg', 'Raghunath Nagri Project Site View', 'active', '2025-04-30 06:27:19'),
(2, '/assets/images/gallery/project2.jpg', 'Modern Apartment Block', 'active', '2025-04-30 06:27:19'),
(3, '/assets/images/gallery/project3.jpg', 'Clubhouse and Amenities', 'active', '2025-04-30 06:27:19'),
(4, 'Value for image_path 1', 'Value for caption 1', 'active', '2025-04-30 18:30:00'),
(5, 'Value for image_path 2', 'Value for caption 2', '', '2025-05-06 18:30:00'),
(6, 'Value for image_path 3', 'Value for caption 3', '', '2025-05-12 18:30:00'),
(7, 'Value for image_path 4', 'Value for caption 4', '', '2025-05-18 18:30:00'),
(8, 'Value for image_path 5', 'Value for caption 5', 'active', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `global_payments`
--

INSERT INTO `global_payments` (`id`, `client`, `amount`, `currency`, `purpose`, `status`, `created_at`) VALUES
(1, 'Value for client 1', 15000000.00, 'Value for ', 'Value for purpose 1', 'active', '2025-05-01 00:00:00'),
(2, 'Value for client 2', 7000000.00, 'Value for ', 'Value for purpose 2', 'pending', '2025-05-07 00:00:00'),
(3, 'Value for client 3', 9000000.00, 'Value for ', 'Value for purpose 3', 'completed', '2025-05-13 00:00:00'),
(4, 'Value for client 4', 20000000.00, 'Value for ', 'Value for purpose 4', 'cancelled', '2025-05-19 00:00:00'),
(5, 'Value for client 5', 25000000.00, 'Value for ', 'Value for purpose 5', 'active', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `inventory_log`
--

INSERT INTO `inventory_log` (`id`, `plot_id`, `action`, `user_id`, `note`, `action_date`, `created_at`) VALUES
(1, 1, 'booked', 1, 'Value for note 1', '2025-05-01 00:00:00', '2025-04-30 18:30:00'),
(2, 2, 'sold', 2, 'Value for note 2', '2025-05-07 00:00:00', '2025-05-06 18:30:00'),
(3, 3, 'transferred', 3, 'Value for note 3', '2025-05-13 00:00:00', '2025-05-12 18:30:00'),
(4, 4, 'released', 4, 'Value for note 4', '2025-05-19 00:00:00', '2025-05-18 18:30:00'),
(5, 5, 'created', 5, 'Value for note 5', '2025-05-25 00:00:00', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `iot_devices`
--

INSERT INTO `iot_devices` (`id`, `property_id`, `device_name`, `device_type`, `status`, `last_seen`, `created_at`) VALUES
(1, 1, 'Rahul Sharma', 'villa', 'active', '2025-05-01 00:00:00', '2025-05-01 00:00:00'),
(2, 2, 'Priya Singh', 'apartment', 'pending', '2025-05-02 00:00:00', '2025-05-07 00:00:00'),
(3, 3, 'Amit Kumar', 'house', 'completed', '2025-05-03 00:00:00', '2025-05-13 00:00:00'),
(4, 4, 'Neha Patel', 'villa', 'cancelled', '2025-05-04 00:00:00', '2025-05-19 00:00:00'),
(5, 5, 'Vikram Mehta', 'penthouse', 'active', '2025-05-05 00:00:00', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `iot_device_events`
--

INSERT INTO `iot_device_events` (`id`, `device_id`, `event_type`, `event_value`, `event_time`) VALUES
(1, 1, 'villa', 'Value for event_value 1', '2025-05-01 00:00:00'),
(2, 2, 'apartment', 'Value for event_value 2', '2025-05-02 00:00:00'),
(3, 3, 'house', 'Value for event_value 3', '2025-05-03 00:00:00'),
(4, 4, 'villa', 'Value for event_value 4', '2025-05-04 00:00:00'),
(5, 5, 'penthouse', 'Value for event_value 5', '2025-05-05 00:00:00');

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
  `property_id` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `registry_no` varchar(100) DEFAULT NULL,
  `agreement_doc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `land_purchases`
--

INSERT INTO `land_purchases` (`id`, `farmer_id`, `property_id`, `purchase_date`, `amount`, `registry_no`, `agreement_doc`) VALUES
(1, 1, 1, '2025-04-22', 1500000.00, 'REG123', 'agreement123.pdf'),
(2, 2, 3, '2025-04-15', 1400000.00, 'REG124', 'agreement124.pdf'),
(3, 1, 1, '2025-05-01', 15000000.00, 'Value for registry_no 1', 'Value for agreement_doc 1'),
(4, 2, 2, '2025-05-07', 7000000.00, 'Value for registry_no 2', 'Value for agreement_doc 2'),
(5, 3, 3, '2025-05-13', 9000000.00, 'Value for registry_no 3', 'Value for agreement_doc 3'),
(6, 4, 4, '2025-05-19', 20000000.00, 'Value for registry_no 4', 'Value for agreement_doc 4'),
(7, 5, 5, '2025-05-25', 25000000.00, 'Value for registry_no 5', 'Value for agreement_doc 5');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `converted_at` datetime DEFAULT NULL,
  `converted_amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `name`, `contact`, `source`, `assigned_to`, `status`, `notes`, `created_at`, `converted_at`, `converted_amount`) VALUES
(1, 'Lead 1', '+91 984199040', 'Referral', 139, 'closed_won', NULL, '2025-05-17 20:06:37', '2025-05-17 01:36:38', 15000000.00),
(2, 'Lead 2', '+91 983365109', 'Referral', 142, 'contacted', NULL, '2025-05-17 20:06:37', NULL, NULL),
(3, 'Lead 3', '+91 987686354', 'Walk-in', 105, 'closed_won', NULL, '2025-05-17 20:06:37', '2025-03-24 01:36:38', 9000000.00),
(4, 'Lead 4', '+91 981782466', 'Property Portal', 106, 'negotiation', NULL, '2025-05-17 20:06:37', NULL, NULL),
(5, 'Lead 5', '+91 987868812', 'Property Portal', 105, 'closed_won', NULL, '2025-05-17 20:06:37', '2025-04-16 01:36:38', 7000000.00),
(6, 'Lead 6', '+91 989431808', 'Walk-in', 142, 'qualified', NULL, '2025-05-17 20:06:37', NULL, NULL),
(7, 'Lead 7', '+91 986592157', 'Walk-in', 142, 'qualified', NULL, '2025-05-17 20:06:37', NULL, NULL),
(8, 'Lead 8', '+91 986077012', 'Property Portal', 105, 'closed_won', NULL, '2025-05-17 20:06:37', '2025-03-25 01:36:38', NULL),
(9, 'Lead 9', '+91 988862888', 'Website', 142, 'negotiation', NULL, '2025-05-17 20:06:37', NULL, NULL),
(10, 'Lead 10', '+91 985332951', 'Direct Call', 139, 'negotiation', NULL, '2025-05-17 20:06:37', NULL, NULL);

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

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `employee_id`, `leave_type`, `from_date`, `to_date`, `status`, `remarks`, `created_at`) VALUES
(1, 1, 'villa', '2025-05-01', '2025-05-01', '', 'Value for remarks 1', '2025-04-30 18:30:00'),
(2, 2, 'apartment', '2025-05-07', '2025-05-07', 'pending', 'Value for remarks 2', '2025-05-06 18:30:00'),
(3, 3, 'house', '2025-05-13', '2025-05-13', '', 'Value for remarks 3', '2025-05-12 18:30:00'),
(4, 4, 'villa', '2025-05-19', '2025-05-19', '', 'Value for remarks 4', '2025-05-18 18:30:00'),
(5, 5, 'penthouse', '2025-05-25', '2025-05-25', '', 'Value for remarks 5', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `legal_documents`
--

INSERT INTO `legal_documents` (`id`, `file_name`, `file_url`, `review_status`, `ai_summary`, `ai_flags`, `uploaded_at`) VALUES
(1, 'Rahul Sharma', 'Value for file_url 1', 'active', 'Value for ai_summary 1', 'Value for ai_flags 1', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for file_url 2', 'pending', 'Value for ai_summary 2', 'Value for ai_flags 2', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for file_url 3', 'completed', 'Value for ai_summary 3', 'Value for ai_flags 3', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for file_url 4', 'cancelled', 'Value for ai_summary 4', 'Value for ai_flags 4', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for file_url 5', 'active', 'Value for ai_summary 5', 'Value for ai_flags 5', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `marketing_campaigns`
--

INSERT INTO `marketing_campaigns` (`id`, `name`, `type`, `message`, `scheduled_at`, `status`, `created_at`) VALUES
(1, 'Rahul Sharma', '', 'This is a sample message for record 1.', '2025-05-01 00:00:00', 'active', '2025-05-01 00:00:00'),
(2, 'Priya Singh', '', 'This is a sample message for record 2.', '2025-05-07 00:00:00', 'pending', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', '', 'This is a sample message for record 3.', '2025-05-13 00:00:00', 'completed', '2025-05-13 00:00:00'),
(4, 'Neha Patel', '', 'This is a sample message for record 4.', '2025-05-19 00:00:00', 'cancelled', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', '', 'This is a sample message for record 5.', '2025-05-25 00:00:00', 'active', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `marketing_strategies`
--

INSERT INTO `marketing_strategies` (`id`, `title`, `description`, `image_url`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Limited Time Offer', 'Get an extra 5% discount on all bookings made this week. Hurry up!', 'assets/marketing/offer1.jpg', 1, '2025-04-29 19:48:42', '2025-04-29 19:48:42'),
(2, 'Free Site Visit', 'Book a free site visit for any property and get exclusive insights from our experts.', 'assets/marketing/offer2.jpg', 1, '2025-04-29 19:48:42', '2025-04-29 19:48:42'),
(3, 'Referral Bonus', 'Refer a friend and earn Ôé╣10,000 on their first booking.', 'assets/marketing/offer3.jpg', 1, '2025-04-29 19:48:42', '2025-04-29 19:48:42'),
(4, 'Festive Bonanza', 'Special festive deals on select properties. Limited period only!', 'assets/marketing/offer4.jpg', 1, '2025-04-29 19:48:42', '2025-04-29 19:48:42'),
(5, 'Luxury Villa', 'Beautiful luxury villa with garden and pool', 'Value for image_url 1', 1, '2025-04-30 18:30:00', '2025-04-30 18:30:00'),
(6, 'City Apartment', 'Modern apartment in city center with great amenities', 'Value for image_url 2', 2, '2025-05-06 18:30:00', '2025-05-06 18:30:00'),
(7, 'Suburban House', 'Spacious family home in quiet neighborhood', 'Value for image_url 3', 3, '2025-05-12 18:30:00', '2025-05-12 18:30:00'),
(8, 'Beach Property', 'Beachfront luxury home with amazing views', 'Value for image_url 4', 4, '2025-05-18 18:30:00', '2025-05-18 18:30:00'),
(9, 'Penthouse', 'Luxury penthouse with terrace and city views', 'Value for image_url 5', 5, '2025-05-24 18:30:00', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `marketplace_apps`
--

INSERT INTO `marketplace_apps` (`id`, `app_name`, `provider`, `app_url`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Value for provider 1', 'Value for app_url 1', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for provider 2', 'Value for app_url 2', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for provider 3', 'Value for app_url 3', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for provider 4', 'Value for app_url 4', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for provider 5', 'Value for app_url 5', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `migration_name`, `applied_at`) VALUES
(1, '1.0.1', 'adduserpreferencestable', '2025-05-25 10:48:05'),
(2, '1.0.2', 'addapiauthenticationtables', '2025-05-25 10:52:15');

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commissions`
--

CREATE TABLE `mlm_commissions` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `payout_id` int(11) DEFAULT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commission_ledger`
--

CREATE TABLE `mlm_commission_ledger` (
  `id` int(11) NOT NULL,
  `commission_id` int(11) NOT NULL,
  `action` enum('created','updated','paid','cancelled') NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `mlm_tree`
--

INSERT INTO `mlm_tree` (`id`, `user_id`, `parent_id`, `level`, `join_date`) VALUES
(1, 1, 1, 1, '2025-05-01'),
(2, 2, 2, 2, '2025-05-07'),
(3, 3, 3, 3, '2025-05-13'),
(4, 4, 4, 4, '2025-05-19'),
(5, 5, 5, 5, '2025-05-25');

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

--
-- Dumping data for table `mobile_devices`
--

INSERT INTO `mobile_devices` (`id`, `device_user`, `push_token`, `platform`, `created_at`) VALUES
(1, 'Value for device_user 1', 'Value for push_token 1', 'Value for platform 1', '2025-05-01 00:00:00'),
(2, 'Value for device_user 2', 'Value for push_token 2', 'Value for platform 2', '2025-05-07 00:00:00'),
(3, 'Value for device_user 3', 'Value for push_token 3', 'Value for platform 3', '2025-05-13 00:00:00'),
(4, 'Value for device_user 4', 'Value for push_token 4', 'Value for platform 4', '2025-05-19 00:00:00'),
(5, 'Value for device_user 5', 'Value for push_token 5', 'Value for platform 5', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `date`, `summary`, `image`, `content`, `created_at`) VALUES
(1, 'Welcome to APS Dream Homes!', '2025-04-01', 'We are excited to announce the launch of our new platform.', 'news1.jpg', 'Full content of the news article goes here.', '2025-04-23 08:19:00'),
(2, 'Market Update: April 2025', '2025-04-10', 'Latest trends and updates in the real estate market.', 'news2.jpg', 'Detailed content for market update.', '2025-04-23 08:19:00'),
(3, 'Luxury Villa', '2025-05-01', 'Value for summary 1', 'Value for image 1', 'This is a sample message for record 1.', '2025-04-30 18:30:00'),
(4, 'City Apartment', '2025-05-07', 'Value for summary 2', 'Value for image 2', 'This is a sample message for record 2.', '2025-05-06 18:30:00'),
(5, 'Suburban House', '2025-05-13', 'Value for summary 3', 'Value for image 3', 'This is a sample message for record 3.', '2025-05-12 18:30:00'),
(6, 'Beach Property', '2025-05-19', 'Value for summary 4', 'Value for image 4', 'This is a sample message for record 4.', '2025-05-18 18:30:00'),
(7, 'Penthouse', '2025-05-25', 'Value for summary 5', 'Value for image 5', 'This is a sample message for record 5.', '2025-05-24 18:30:00');

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
(1, 1, 'Welcome to APS Dream Home!', 'info', '2025-05-17 20:06:37', NULL),
(2, 2, 'Your booking is confirmed.', 'booking', '2025-05-17 20:06:37', NULL),
(3, 10, 'Salary credited for March.', 'salary', '2025-05-17 20:06:37', NULL),
(4, 1, 'This is a sample message for record 1.', 'villa', '2025-05-26 20:06:37', '2025-04-30 18:30:00'),
(5, 2, 'This is a sample message for record 2.', 'apartment', '2025-06-01 20:06:37', '2025-05-06 18:30:00'),
(6, 3, 'This is a sample message for record 3.', 'house', '2025-06-07 20:06:37', '2025-05-12 18:30:00'),
(7, 4, 'This is a sample message for record 4.', 'villa', '2025-06-13 20:06:37', '2025-05-18 18:30:00'),
(8, 5, 'This is a sample message for record 5.', 'penthouse', '2025-06-19 20:06:37', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `notification_logs`
--

INSERT INTO `notification_logs` (`id`, `notification_id`, `status`, `error_message`, `created_at`) VALUES
(1, 1, 'active', 'This is a sample message for record 1.', '2025-04-30 18:30:00'),
(2, 2, 'pending', 'This is a sample message for record 2.', '2025-05-06 18:30:00'),
(3, 3, 'completed', 'This is a sample message for record 3.', '2025-05-12 18:30:00'),
(9, 4, 'cancelled', 'This is a sample message for record 4.', '2025-05-18 18:30:00'),
(10, 5, 'active', 'This is a sample message for record 5.', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `notification_settings`
--

INSERT INTO `notification_settings` (`id`, `user_id`, `type`, `email_enabled`, `push_enabled`, `sms_enabled`, `created_at`, `updated_at`) VALUES
(1, 1, 'villa', 1, 1, 1, '2025-04-30 18:30:00', '2025-04-30 18:30:00'),
(2, 2, 'apartment', 2, 2, 2, '2025-05-06 18:30:00', '2025-05-06 18:30:00'),
(3, 3, 'house', 3, 3, 3, '2025-05-12 18:30:00', '2025-05-12 18:30:00'),
(4, 4, 'villa', 4, 4, 4, '2025-05-18 18:30:00', '2025-05-18 18:30:00'),
(5, 5, 'penthouse', 5, 5, 5, '2025-05-24 18:30:00', '2025-05-24 18:30:00');

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
(1, 'new_lead', 'New Lead: {property_title}', 'You have received a new inquiry from {name} for {property_title}. Click here to view details.', '2025-05-17 06:30:15', NULL),
(2, 'visit_scheduled', 'Visit Scheduled: {property_title}', '{name} has scheduled a visit for {property_title} on {visit_date} at {visit_time}.', '2025-05-17 06:30:15', NULL),
(3, 'visit_reminder', 'Visit Reminder: {property_title}', 'Reminder: You have a property visit scheduled with {name} for {property_title} tomorrow at {visit_time}.', '2025-05-17 06:30:15', NULL),
(4, 'lead_update', 'Lead Status Update: {property_title}', 'The status of your inquiry for {property_title} has been updated to {status}.', '2025-05-17 06:30:15', NULL),
(5, 'lead_status_change', 'Lead Status Updated: {property_title}', 'The status of your lead for {property_title} has been updated to {lead_status}.', '2025-05-17 07:07:12', NULL),
(6, 'property_status_change', 'Property Status Change: {property_title}', 'The status of {property_title} has been changed to {property_status}.', '2025-05-17 07:07:12', NULL);

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

--
-- Dumping data for table `opportunities`
--

INSERT INTO `opportunities` (`id`, `lead_id`, `stage`, `value`, `expected_close`, `status`, `created_at`) VALUES
(1, 1, 'Value for stage 1', 1000.00, '2025-05-01', '', '2025-04-30 18:30:00'),
(2, 2, 'Value for stage 2', 2000.00, '2025-05-02', '', '2025-05-06 18:30:00'),
(3, 3, 'Value for stage 3', 3000.00, '2025-05-03', '', '2025-05-12 18:30:00'),
(4, 4, 'Value for stage 4', 4000.00, '2025-05-04', '', '2025-05-18 18:30:00'),
(5, 5, 'Value for stage 5', 5000.00, '2025-05-05', '', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `partner_certification`
--

INSERT INTO `partner_certification` (`id`, `partner_name`, `app_name`, `cert_status`, `revenue_share`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Rahul Sharma', 'active', 1, '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Priya Singh', 'pending', 2, '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Amit Kumar', 'completed', 3, '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Neha Patel', 'cancelled', 4, '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Vikram Mehta', 'active', 5, '2025-05-25 00:00:00');

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

--
-- Dumping data for table `partner_rewards`
--

INSERT INTO `partner_rewards` (`id`, `partner_email`, `points`, `description`, `created_at`) VALUES
(1, 'rahul@example.com', 1, 'Beautiful luxury villa with garden and pool', '2025-05-01 00:00:00'),
(2, 'priya@example.com', 2, 'Modern apartment in city center with great amenities', '2025-05-07 00:00:00'),
(3, 'amit@example.com', 3, 'Spacious family home in quiet neighborhood', '2025-05-13 00:00:00'),
(4, 'neha@example.com', 4, 'Beachfront luxury home with amazing views', '2025-05-19 00:00:00'),
(5, 'vikram@example.com', 5, 'Luxury penthouse with terrace and city views', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount`, `payment_date`, `method`, `status`, `created_at`) VALUES
(1, 1, 15000000.00, '2025-05-01', 'Value for method 1', '', '2025-04-30 18:30:00'),
(2, 2, 7000000.00, '2025-05-07', 'Value for method 2', 'pending', '2025-05-06 18:30:00'),
(3, 3, 9000000.00, '2025-05-13', 'Value for method 3', 'completed', '2025-05-12 18:30:00'),
(4, 4, 20000000.00, '2025-05-19', 'Value for method 4', '', '2025-05-18 18:30:00'),
(5, 5, 25000000.00, '2025-05-25', 'Value for method 5', '', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `payment_gateway_config`
--

INSERT INTO `payment_gateway_config` (`id`, `provider`, `api_key`, `api_secret`, `created_at`) VALUES
(1, 'Value for provider 1', 'Value for api_key 1', 'Value for api_secret 1', '2025-05-01 00:00:00'),
(2, 'Value for provider 2', 'Value for api_key 2', 'Value for api_secret 2', '2025-05-07 00:00:00'),
(3, 'Value for provider 3', 'Value for api_key 3', 'Value for api_secret 3', '2025-05-13 00:00:00'),
(4, 'Value for provider 4', 'Value for api_key 4', 'Value for api_secret 4', '2025-05-19 00:00:00'),
(5, 'Value for provider 5', 'Value for api_key 5', 'Value for api_secret 5', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `payment_logs`
--

INSERT INTO `payment_logs` (`id`, `user_id`, `payment_method`, `amount`, `status`, `created_at`) VALUES
(1, 1, 'Value for payment_method 1', 15000000.00, 'active', '2025-04-30 18:30:00'),
(2, 2, 'Value for payment_method 2', 7000000.00, 'pending', '2025-05-06 18:30:00'),
(3, 3, 'Value for payment_method 3', 9000000.00, 'completed', '2025-05-12 18:30:00'),
(4, 4, 'Value for payment_method 4', 20000000.00, 'cancelled', '2025-05-18 18:30:00'),
(5, 5, 'Value for payment_method 5', 25000000.00, 'active', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `action` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `action`, `description`) VALUES
(1, 'Value for action 1', 'Beautiful luxury villa with garden and pool'),
(2, 'Value for action 2', 'Modern apartment in city center with great amenities'),
(3, 'Value for action 3', 'Spacious family home in quiet neighborhood'),
(4, 'Value for action 4', 'Beachfront luxury home with amazing views'),
(5, 'Value for action 5', 'Luxury penthouse with terrace and city views');

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

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `location`, `description`, `status`, `builder_id`, `project_name`, `start_date`, `end_date`, `budget`, `created_at`, `updated_at`, `brochure_path`, `youtube_url`, `brochure_drive_id`) VALUES
(1, 'Green Valley', 'Sector 10, City', 'Premium residential plots', 'active', NULL, NULL, NULL, NULL, NULL, '2025-04-21 21:09:43', '2025-04-21 21:09:43', NULL, NULL, NULL),
(2, 'Suryoday Colony ', 'jungle kaudiya to kalesar four lane', 'Affordable housing project', 'active', NULL, 'suryoday Colony ', '2022-04-24', NULL, 100000000.00, '2025-04-21 21:09:43', '2025-04-30 06:37:56', NULL, NULL, NULL),
(3, 'Raghunath Nagri', 'Gorakhpur', 'Premium residential project in Gorakhpur by APS Dream Homes. Modern amenities and prime location.', 'active', NULL, 'Raghunath Nagri', '2023-01-01', NULL, 50000000.00, '2025-04-30 06:22:23', '2025-04-30 06:22:23', NULL, NULL, NULL),
(4, 'Rahul Sharma', 'Value for location 1', 'Beautiful luxury villa with garden and pool', 'active', 1, 'Rahul Sharma', '2025-05-01', '2025-05-01', 1000.00, '2025-04-30 18:30:00', '2025-04-30 18:30:00', 'Value for brochure_path 1', 'Value for youtube_url 1', '1'),
(5, 'Priya Singh', 'Value for location 2', 'Modern apartment in city center with great amenities', '', 2, 'Priya Singh', '2025-05-07', '2025-05-07', 2000.00, '2025-05-06 18:30:00', '2025-05-06 18:30:00', 'Value for brochure_path 2', 'Value for youtube_url 2', '2'),
(6, 'Amit Kumar', 'Value for location 3', 'Spacious family home in quiet neighborhood', '', 3, 'Amit Kumar', '2025-05-13', '2025-05-13', 3000.00, '2025-05-12 18:30:00', '2025-05-12 18:30:00', 'Value for brochure_path 3', 'Value for youtube_url 3', '3'),
(7, 'Neha Patel', 'Value for location 4', 'Beachfront luxury home with amazing views', '', 4, 'Neha Patel', '2025-05-19', '2025-05-19', 4000.00, '2025-05-18 18:30:00', '2025-05-18 18:30:00', 'Value for brochure_path 4', 'Value for youtube_url 4', '4'),
(8, 'Vikram Mehta', 'Value for location 5', 'Luxury penthouse with terrace and city views', 'active', 5, 'Vikram Mehta', '2025-05-25', '2025-05-25', 5000.00, '2025-05-24 18:30:00', '2025-05-24 18:30:00', 'Value for brochure_path 5', 'Value for youtube_url 5', '5'),
(9, NULL, NULL, 'Raghunath Nagri is a vibrant residential community known for its peaceful environment and modern amenities. The colony features well-planned layouts, green spaces, and a variety of housing options, catering to families and individuals alike.\r\n\r\nKey roads, including Road 30, Road 40, and Road 50, enhance accessibility within the colony and connect residents to nearby urban centers. These roads are designed to accommodate smooth traffic flow and provide easy access to essential facilities such as parks, schools, shopping areas, and healthcare services.\r\n\r\nThe strategic location of Raghunath Nagri, with its proximity to major thoroughfares, ensures that residents enjoy both tranquility and convenience, making it an ideal place to live. Overall, Raghunath Nagri embodies a blend of comfort, community spirit, and accessibility.', 'active', 83, 'Raghunath Nagrii', '2024-04-05', '2026-07-30', 0.00, '2025-05-25 12:18:13', '2025-05-25 12:18:13', NULL, NULL, NULL),
(10, NULL, NULL, 'Raghunath Nagri is a vibrant residential community known for its peaceful environment and modern amenities. The colony features well-planned layouts, green spaces, and a variety of housing options, catering to families and individuals alike.\r\n\r\nKey roads, including Road 30, Road 40, and Road 50, enhance accessibility within the colony and connect residents to nearby urban centers. These roads are designed to accommodate smooth traffic flow and provide easy access to essential facilities such as parks, schools, shopping areas, and healthcare services.\r\n\r\nThe strategic location of Raghunath Nagri, with its proximity to major thoroughfares, ensures that residents enjoy both tranquility and convenience, making it an ideal place to live. Overall, Raghunath Nagri embodies a blend of comfort, community spirit, and accessibility.', 'active', 83, 'Raghunath Nagrii', '2024-04-05', '2026-07-30', 0.00, '2025-05-25 12:18:13', '2025-05-25 12:18:13', NULL, NULL, NULL),
(11, NULL, NULL, 'Raghunath Nagri is a vibrant residential community known for its peaceful environment and modern amenities. The colony features well-planned layouts, green spaces, and a variety of housing options, catering to families and individuals alike.\r\n\r\nKey roads, including Road 30, Road 40, and Road 50, enhance accessibility within the colony and connect residents to nearby urban centers. These roads are designed to accommodate smooth traffic flow and provide easy access to essential facilities such as parks, schools, shopping areas, and healthcare services.\r\n\r\nThe strategic location of Raghunath Nagri, with its proximity to major thoroughfares, ensures that residents enjoy both tranquility and convenience, making it an ideal place to live. Overall, Raghunath Nagri embodies a blend of comfort, community spirit, and accessibility.', 'active', 83, 'Raghunath Nagrii', '2024-04-05', '2026-07-30', 0.00, '2025-05-25 12:18:13', '2025-05-25 12:18:13', NULL, NULL, NULL),
(12, 'Sample Project', NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL, '2025-05-27 11:56:44', '2025-05-27 11:56:44', NULL, NULL, NULL);

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

--
-- Dumping data for table `project_amenities`
--

INSERT INTO `project_amenities` (`id`, `project_id`, `icon_path`, `label`) VALUES
(1, 1, 'Value for icon_path 1', 'Value for label 1'),
(2, 2, 'Value for icon_path 2', 'Value for label 2'),
(3, 3, 'Value for icon_path 3', 'Value for label 3'),
(9, 4, 'Value for icon_path 4', 'Value for label 4'),
(10, 5, 'Value for icon_path 5', 'Value for label 5');

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

--
-- Dumping data for table `project_gallery`
--

INSERT INTO `project_gallery` (`id`, `project_id`, `image_path`, `caption`, `drive_file_id`) VALUES
(1, 1, 'Value for image_path 1', 'Value for caption 1', '1'),
(2, 2, 'Value for image_path 2', 'Value for caption 2', '2'),
(3, 3, 'Value for image_path 3', 'Value for caption 3', '3'),
(9, 4, 'Value for image_path 4', 'Value for caption 4', '4'),
(10, 5, 'Value for image_path 5', 'Value for caption 5', '5');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `property_type_id` int(11) DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `area_sqft` decimal(10,2) DEFAULT NULL,
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('available','sold','reserved','under_construction') DEFAULT 'available',
  `featured` tinyint(1) DEFAULT 0,
  `hot_offer` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `slug`, `description`, `property_type_id`, `price`, `area_sqft`, `bedrooms`, `bathrooms`, `address`, `city`, `state`, `country`, `postal_code`, `latitude`, `longitude`, `status`, `featured`, `hot_offer`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Sample Property', 'sample-property', 'This is a sample property', 1, 1000000.00, 1500.00, 2, 2, NULL, 'Sample City', NULL, NULL, NULL, NULL, NULL, 'available', 0, 0, NULL, NULL, '2025-05-27 12:00:11', '2025-05-27 12:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `status` enum('available','sold','pending') DEFAULT 'available',
  `type` enum('residential','commercial','land') DEFAULT 'residential',
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `gallery_images` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0
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
-- Table structure for table `property_types`
--

CREATE TABLE `property_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_types`
--

INSERT INTO `property_types` (`id`, `name`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Apartment', NULL, 'fa-building', 'active', '2025-05-27 12:00:11', '2025-05-27 12:00:11');

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

--
-- Dumping data for table `rental_properties`
--

INSERT INTO `rental_properties` (`id`, `owner_id`, `address`, `rent_amount`, `status`) VALUES
(1, 1, 'Delhi', 15000000.00, ''),
(2, 2, 'Mumbai', 7000000.00, ''),
(3, 3, 'Bangalore', 9000000.00, ''),
(4, 4, 'Ahmedabad', 20000000.00, ''),
(5, 5, 'Pune', 25000000.00, '');

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

--
-- Dumping data for table `rent_payments`
--

INSERT INTO `rent_payments` (`id`, `rental_property_id`, `tenant_id`, `amount`, `due_date`, `paid_date`, `status`) VALUES
(1, 1, 1, 15000000.00, '2025-05-01', '2025-05-01', ''),
(2, 2, 2, 7000000.00, '2025-05-07', '2025-05-07', 'pending'),
(3, 3, 3, 9000000.00, '2025-05-13', '2025-05-13', ''),
(4, 4, 4, 20000000.00, '2025-05-19', '2025-05-19', ''),
(5, 5, 5, 25000000.00, '2025-05-25', '2025-05-25', '');

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

--
-- Dumping data for table `resale_commissions`
--

INSERT INTO `resale_commissions` (`id`, `associate_id`, `resale_property_id`, `amount`, `paid_on`) VALUES
(1, 1, 1, 15000000.00, '2025-05-01'),
(2, 2, 2, 7000000.00, '2025-05-02'),
(3, 3, 3, 9000000.00, '2025-05-03'),
(4, 4, 4, 20000000.00, '2025-05-04'),
(5, 5, 5, 25000000.00, '2025-05-05');

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

--
-- Dumping data for table `resale_properties`
--

INSERT INTO `resale_properties` (`id`, `owner_id`, `details`, `price`, `status`) VALUES
(1, 1, 'Value for details 1', 15000000.00, ''),
(2, 2, 'Value for details 2', 7000000.00, ''),
(3, 3, 'Value for details 3', 9000000.00, ''),
(4, 4, 'Value for details 4', 20000000.00, ''),
(5, 5, 'Value for details 5', 25000000.00, '');

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

--
-- Dumping data for table `reward_history`
--

INSERT INTO `reward_history` (`id`, `associate_id`, `reward_type`, `reward_value`, `reward_date`, `description`, `created_at`) VALUES
(1, 1, 'villa', 1000.00, '2025-05-01', 'Beautiful luxury villa with garden and pool', '2025-04-30 18:30:00'),
(2, 2, 'apartment', 2000.00, '2025-05-07', 'Modern apartment in city center with great amenities', '2025-05-06 18:30:00'),
(3, 3, 'house', 3000.00, '2025-05-13', 'Spacious family home in quiet neighborhood', '2025-05-12 18:30:00'),
(4, 4, 'villa', 4000.00, '2025-05-19', 'Beachfront luxury home with amazing views', '2025-05-18 18:30:00'),
(5, 5, 'penthouse', 5000.00, '2025-05-25', 'Luxury penthouse with terrace and city views', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(2, 'Admin'),
(21, 'ceo'),
(23, 'cfo'),
(25, 'cm'),
(24, 'coo'),
(22, 'cto'),
(20, 'director'),
(10, 'finance'),
(13, 'hr'),
(12, 'it_head'),
(16, 'legal'),
(19, 'manager'),
(14, 'marketing'),
(26, 'office_admin'),
(27, 'official_employee'),
(15, 'operations'),
(17, 'sales'),
(9, 'superadmin'),
(18, 'support');

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

--
-- Dumping data for table `role_change_approvals`
--

INSERT INTO `role_change_approvals` (`id`, `user_id`, `role_id`, `action`, `requested_by`, `status`, `requested_at`, `decided_by`, `decided_at`) VALUES
(1, 1, 1, 'remove', 1, '', '2025-05-01 00:00:00', 1, '2025-05-01 00:00:00'),
(2, 2, 2, 'assign', 2, 'pending', '2025-05-07 00:00:00', 2, '2025-05-07 00:00:00'),
(3, 3, 3, 'remove', 3, '', '2025-05-13 00:00:00', 3, '2025-05-13 00:00:00'),
(4, 4, 4, 'assign', 4, '', '2025-05-19 00:00:00', 4, '2025-05-19 00:00:00'),
(5, 5, 5, 'remove', 5, '', '2025-05-25 00:00:00', 5, '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4),
(5, 5, 5);

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

--
-- Dumping data for table `saas_instances`
--

INSERT INTO `saas_instances` (`id`, `client_name`, `domain`, `status`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Value for domain 1', 'active', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for domain 2', 'pending', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for domain 3', 'completed', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for domain 4', 'cancelled', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for domain 5', 'active', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `salaries`
--

INSERT INTO `salaries` (`id`, `employee_id`, `month`, `year`, `amount`, `status`, `paid_on`) VALUES
(1, 10, 3, 2025, 50000.00, 'paid', '2025-03-31'),
(2, 11, 3, 2025, 30000.00, 'paid', '2025-03-31'),
(3, 1, 1, 1, 15000000.00, '', '2025-05-01'),
(4, 2, 2, 2, 7000000.00, 'pending', '2025-05-02'),
(5, 3, 3, 3, 9000000.00, '', '2025-05-03'),
(6, 4, 4, 4, 20000000.00, '', '2025-05-04'),
(7, 5, 5, 5, 25000000.00, '', '2025-05-05');

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

--
-- Dumping data for table `salary_plan`
--

INSERT INTO `salary_plan` (`id`, `associate_id`, `level`, `salary_amount`, `payout_date`, `status`, `created_at`) VALUES
(1, 1, 1, 15000000.00, '2025-05-01', '', '2025-04-30 18:30:00'),
(2, 2, 2, 7000000.00, '2025-05-07', 'pending', '2025-05-06 18:30:00'),
(3, 3, 3, 9000000.00, '2025-05-13', '', '2025-05-12 18:30:00'),
(4, 4, 4, 20000000.00, '2025-05-19', '', '2025-05-18 18:30:00'),
(5, 5, 5, 25000000.00, '2025-05-25', '', '2025-05-24 18:30:00');

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
(1, 'footer_content', 'Your trusted partner in real estate, providing quality properties and excellent service across India.', '2025-05-06 05:43:21', '2025-05-06 05:43:21'),
(2, 'footer_links', '[{\"url\":\"/\",\"text\":\"Home\"},{\"url\":\"/properties.php\",\"text\":\"Properties\"},{\"url\":\"/about.php\",\"text\":\"About\"},{\"url\":\"/contact.php\",\"text\":\"Contact\"}]', '2025-05-06 05:43:21', '2025-05-06 05:43:21'),
(3, 'social_links', '[{\"url\":\"https://facebook.com/apsdreamhomes\",\"icon\":\"fa-facebook\"},{\"url\":\"https://twitter.com/apsdreamhomes\",\"icon\":\"fa-twitter\"},{\"url\":\"https://instagram.com/apsdreamhomes\",\"icon\":\"fa-instagram\"}]', '2025-05-06 05:43:21', '2025-05-06 05:43:21'),
(4, 'header_menu_items', '[{\"url\":\"/apsdreamhomefinal/\",\"text\":\"Home\"},{\"url\":\"/apsdreamhomefinal/project.php\",\"text\":\"Project\"},{\"url\":\"/apsdreamhomefinal/about.php\",\"text\":\"About\"},{\"url\":\"/apsdreamhomefinal/contact.php\",\"text\":\"Contact\"},{\"url\":\"/apsdreamhomefinal/login.php\",\"text\":\"Login\"}]', '2025-05-12 13:47:11', '2025-05-12 13:47:11'),
(5, 'site_logo', '/apsdreamhomefinal/assets/images/logo.png', '2025-05-12 13:47:11', '2025-05-12 13:47:11'),
(6, 'header_styles', '{\"background\":\"#ffffff\",\"text_color\":\"#333333\"}', '2025-05-12 13:47:11', '2025-05-12 13:47:11');

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

--
-- Dumping data for table `smart_contracts`
--

INSERT INTO `smart_contracts` (`id`, `agreement_name`, `parties`, `terms`, `status`, `blockchain_txn`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Value for parties 1', 'Value for terms 1', 'active', 'Value for blockchain_txn 1', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for parties 2', 'Value for terms 2', 'pending', 'Value for blockchain_txn 2', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for parties 3', 'Value for terms 3', 'completed', 'Value for blockchain_txn 3', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for parties 4', 'Value for terms 4', 'cancelled', 'Value for blockchain_txn 4', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for parties 5', 'Value for terms 5', 'active', 'Value for blockchain_txn 5', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 1, 'Value for subject 1', 'This is a sample message for record 1.', 'active', '2025-04-30 18:30:00'),
(2, 2, 'Value for subject 2', 'This is a sample message for record 2.', 'pending', '2025-05-06 18:30:00'),
(3, 3, 'Value for subject 3', 'This is a sample message for record 3.', 'completed', '2025-05-12 18:30:00'),
(4, 4, 'Value for subject 4', 'This is a sample message for record 4.', 'cancelled', '2025-05-18 18:30:00'),
(5, 5, 'Value for subject 5', 'This is a sample message for record 5.', 'active', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `assigned_by`, `due_date`, `status`, `created_at`) VALUES
(1, 'Luxury Villa', 'Beautiful luxury villa with garden and pool', 1, 1, '2025-05-01', '', '2025-04-30 18:30:00'),
(2, 'City Apartment', 'Modern apartment in city center with great amenities', 2, 2, '2025-05-07', 'pending', '2025-05-06 18:30:00'),
(3, 'Suburban House', 'Spacious family home in quiet neighborhood', 3, 3, '2025-05-13', 'completed', '2025-05-12 18:30:00'),
(4, 'Beach Property', 'Beachfront luxury home with amazing views', 4, 4, '2025-05-19', 'cancelled', '2025-05-18 18:30:00'),
(5, 'Penthouse', 'Luxury penthouse with terrace and city views', 5, 5, '2025-05-25', '', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`id`, `name`, `designation`, `bio`, `photo`, `status`, `created_at`) VALUES
(1, 'Amit Singh', 'Managing Director', 'Over 20 years experience in real estate and project management.', '/assets/images/team/amit.jpg', 'active', '2025-04-30 06:28:57'),
(2, 'Neha Verma', 'Sales Head', 'Expert in customer relations and property sales.', '/assets/images/team/neha.jpg', 'active', '2025-04-30 06:28:57'),
(3, 'Rahul Pandey', 'Project Engineer', 'Specialist in construction and site supervision.', '/assets/images/team/rahul.jpg', 'active', '2025-04-30 06:28:57'),
(4, 'Rahul Sharma', 'Value for designation 1', 'Value for bio 1', 'Value for photo 1', 'active', '2025-04-30 18:30:00'),
(5, 'Priya Singh', 'Value for designation 2', 'Value for bio 2', 'Value for photo 2', '', '2025-05-06 18:30:00'),
(6, 'Amit Kumar', 'Value for designation 3', 'Value for bio 3', 'Value for photo 3', '', '2025-05-12 18:30:00'),
(7, 'Neha Patel', 'Value for designation 4', 'Value for bio 4', 'Value for photo 4', '', '2025-05-18 18:30:00'),
(8, 'Vikram Mehta', 'Value for designation 5', 'Value for bio 5', 'Value for photo 5', 'active', '2025-05-24 18:30:00');

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

--
-- Dumping data for table `team_hierarchy`
--

INSERT INTO `team_hierarchy` (`id`, `associate_id`, `upline_id`, `level`, `created_at`) VALUES
(1, 1, 1, 1, '2025-04-30 18:30:00'),
(2, 2, 2, 2, '2025-05-06 18:30:00'),
(3, 3, 3, 3, '2025-05-12 18:30:00'),
(4, 4, 4, 4, '2025-05-18 18:30:00'),
(5, 5, 5, 5, '2025-05-24 18:30:00'),
(6, 5, 1, 2, '2025-04-02 19:32:32'),
(7, 6, 4, 1, '2025-04-02 19:34:06'),
(8, 6, 2, 2, '2025-04-02 19:34:06'),
(9, 6, 1, 3, '2025-04-02 19:34:06'),
(11, 7, 6, 1, '2025-04-02 19:35:00'),
(12, 7, 4, 2, '2025-04-02 19:35:00'),
(13, 7, 2, 3, '2025-04-02 19:35:00'),
(14, 7, 1, 4, '2025-04-02 19:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rating` tinyint(1) DEFAULT 5,
  `testimonial` text NOT NULL,
  `client_photo` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','active','inactive') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `email`, `rating`, `testimonial`, `client_photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ravi Kumar', NULL, 5, 'APS Dream Homes helped me find my dream house in Gorakhpur. Highly recommended!', '/assets/images/testimonials/ravi.jpg', 'approved', '2025-04-30 06:25:53', '2025-05-28 18:36:01'),
(2, 'Priya Sharma', NULL, 5, 'Professional team and transparent process. Very happy with my new property!', '/assets/images/testimonials/priya.jpg', 'approved', '2025-04-30 06:25:53', '2025-05-28 18:36:01'),
(3, '', NULL, 5, '', NULL, 'approved', '2025-05-17 12:21:22', '2025-05-28 18:36:01'),
(4, 'Rahul Sharma', NULL, 5, 'Value for testimonial 1', 'Value for client_photo 1', 'approved', '2025-04-30 18:30:00', '2025-05-28 18:36:01'),
(5, 'Priya Singh', NULL, 5, 'Value for testimonial 2', 'Value for client_photo 2', '', '2025-05-06 18:30:00', '2025-05-28 18:36:01'),
(6, 'Amit Kumar', NULL, 5, 'Value for testimonial 3', 'Value for client_photo 3', '', '2025-05-12 18:30:00', '2025-05-28 18:36:01'),
(7, 'Neha Patel', NULL, 5, 'Value for testimonial 4', 'Value for client_photo 4', '', '2025-05-18 18:30:00', '2025-05-28 18:36:01'),
(8, 'Vikram Mehta', NULL, 5, 'Value for testimonial 5', 'Value for client_photo 5', 'approved', '2025-05-24 18:30:00', '2025-05-28 18:36:01'),
(9, 'raju', 'raju345@gmail.com', 1, 'Turant ragistry Turant kabja milta hai', '<div class=\"avatar-circle\" style=\"background: #7e3e2d; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 24px;\">R</div>', 'pending', '2025-05-28 19:51:58', '2025-05-28 19:51:58');

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

--
-- Dumping data for table `third_party_integrations`
--

INSERT INTO `third_party_integrations` (`id`, `type`, `api_token`, `created_at`) VALUES
(1, 'villa', 'Value for api_token 1', '2025-05-01 00:00:00'),
(2, 'apartment', 'Value for api_token 2', '2025-05-07 00:00:00'),
(3, 'house', 'Value for api_token 3', '2025-05-13 00:00:00'),
(4, 'villa', 'Value for api_token 4', '2025-05-19 00:00:00'),
(5, 'penthouse', 'Value for api_token 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ref_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `date`, `description`, `ref_id`, `created_at`, `updated_at`) VALUES
(1, 4, 'Booking', 1500000.00, '2025-01-22', 'Plot booking Green Valley', 'TXN001', '2025-05-24 20:06:37', '2025-05-17 20:06:37'),
(2, 5, 'Booking', 2500000.00, '2024-12-08', 'Flat booking Green Valley', 'TXN002', '2025-05-24 20:06:37', '2025-05-17 20:06:37'),
(3, 6, 'Booking', 1500000.00, '2025-02-11', 'Plot booking Green Valley', 'TXN003', '2025-05-24 20:06:37', '2025-05-17 20:06:37'),
(4, 1, 'booking', 2500000.00, NULL, NULL, NULL, '2025-05-17 20:06:37', '2025-05-07 20:06:37'),
(5, 2, 'booking', 3200000.00, NULL, NULL, NULL, '2025-05-18 20:06:37', '2025-05-05 20:06:37'),
(6, 3, 'booking', 1800000.00, NULL, NULL, NULL, '2025-05-19 20:06:37', '2025-05-13 20:06:37'),
(7, 4, 'booking', 4000000.00, NULL, NULL, NULL, '2025-05-19 20:06:37', '2025-05-06 20:06:37'),
(8, 5, 'booking', 2700000.00, NULL, NULL, NULL, '2025-05-20 20:06:37', '2025-05-16 20:06:37'),
(9, 1, 'booking', 2500000.00, '2025-05-18', 'Booking for Property 201', 'BK20250415A', '2025-05-17 20:06:37', '2025-05-17 20:06:37'),
(10, 2, 'booking', 3200000.00, '2025-03-06', 'Booking for Property 202', 'BK20250416B', '2025-05-18 20:06:37', '2025-05-17 20:06:37'),
(11, 3, 'booking', 1800000.00, '2025-05-14', 'Booking for Property 203', 'BK20250417C', '2025-05-19 20:06:37', '2025-05-17 20:06:37'),
(12, 4, 'booking', 4000000.00, '2024-12-06', 'Booking for Property 204', 'BK20250417D', '2025-05-19 20:06:37', '2025-05-17 20:06:37'),
(13, 5, 'booking', 2700000.00, '2025-02-23', 'Booking for Property 205', 'BK20250418E', '2025-05-20 20:06:37', '2025-05-17 20:06:37');

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

--
-- Dumping data for table `upload_audit_log`
--

INSERT INTO `upload_audit_log` (`id`, `event_type`, `entity_id`, `entity_table`, `file_name`, `drive_file_id`, `uploader`, `slack_status`, `telegram_status`, `created_at`) VALUES
(1, 'villa', 1, 'Value for entity_table 1', 'Rahul Sharma', '1', 'Value for uploader 1', 'active', 'active', '2025-04-30 18:30:00'),
(2, 'apartment', 2, 'Value for entity_table 2', 'Priya Singh', '2', 'Value for uploader 2', 'pending', 'pending', '2025-05-06 18:30:00'),
(3, 'house', 3, 'Value for entity_table 3', 'Amit Kumar', '3', 'Value for uploader 3', 'completed', 'completed', '2025-05-12 18:30:00'),
(4, 'villa', 4, 'Value for entity_table 4', 'Neha Patel', '4', 'Value for uploader 4', 'cancelled', 'cancelled', '2025-05-18 18:30:00'),
(5, 'penthouse', 5, 'Value for entity_table 5', 'Vikram Mehta', '5', 'Value for uploader 5', 'active', 'active', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `api_access` tinyint(1) DEFAULT 0,
  `api_rate_limit` int(11) DEFAULT 1000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `profile_picture`, `phone`, `type`, `password`, `status`, `created_at`, `updated_at`, `api_access`, `api_rate_limit`) VALUES
(1, 'Super Admin', 'superadmin@dreamhome.com', NULL, '9999999999', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:37:15', '2025-05-16 20:57:55', 0, 1000),
(2, 'Agent One', 'agent1@dreamhome.com', NULL, '9000000001', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:37:15', '2025-05-16 20:57:55', 0, 1000),
(3, 'Builder One', 'builder1@dreamhome.com', NULL, '9000000002', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:37:15', '2025-05-16 20:57:55', 0, 1000),
(4, 'Customer One', 'customer1@dreamhome.com', NULL, '9000000003', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:37:15', '2025-05-16 20:57:55', 0, 1000),
(5, 'Customer Two', 'customer2@dreamhome.com', NULL, '9000000004', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:45:19', '2025-05-16 20:57:55', 0, 1000),
(6, 'Customer Three', 'customer3@dreamhome.com', NULL, '9000000005', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:45:19', '2025-05-16 20:57:55', 0, 1000),
(7, 'Associate One', 'associate1@dreamhome.com', NULL, '9000000006', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:45:19', '2025-05-16 20:57:55', 0, 1000),
(8, 'Michael', 'michael@mail.com', NULL, '8542221140', NULL, '6812f136d636e737248d365016f8cfd5139e387c', 'active', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 0, 1000),
(9, 'APS Dream Homes', 'apsdreamhomes44@gmail.com', NULL, '', NULL, '', 'active', '2025-03-24 21:18:04', '2025-03-25 19:14:38', 0, 1000),
(10, 'Customer Four', 'customer4@dreamhome.com', NULL, '9000000007', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:52:02', '2025-05-16 20:57:55', 0, 1000),
(11, 'Employee One', 'employee1@dreamhome.com', NULL, '9000000009', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:54:15', '2025-05-16 20:57:55', 0, 1000),
(12, 'Employee Two', 'employee2@dreamhome.com', NULL, '9000000010', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:54:15', '2025-05-16 20:57:55', 0, 1000),
(13, 'Farmer One', 'farmer1@dreamhome.com', NULL, '9000000011', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:54:15', '2025-05-16 20:57:55', 0, 1000),
(27, 'raju rajpoot', 'raju556767@gmail.com', NULL, '7897787656', NULL, '$2y$10$NZMXbAuf5N1VGgFlx0BcT.CXUhvXNfg2emBAz1dtZOB5n0tugpYLW', 'active', '2025-03-29 20:25:04', '2025-03-29 20:25:04', 0, 1000),
(32, 'raju rajpoot', 'rajut5456323767@gmail.com', NULL, '7897765556', NULL, '$2y$10$tgXpRAsQtDeF.EvnSZ7gyOj0EbtU14u/YOVmuTPyFQdtXiOTRs7Li', 'active', '2025-03-29 20:57:10', '2025-03-29 20:57:10', 0, 1000),
(33, 'raju rajpoot singh', 'rajut54563253767@gmail.com', NULL, '7897565656', NULL, '$2y$10$.yX9Xp55uVPGt0V9.9hX6eJw749hy8ZD4wt3XFbrHItM2QT0nOCm6', 'active', '2025-04-01 18:05:40', '2025-04-01 18:05:40', 0, 1000),
(34, 'ruhitwo', 'ruhi2@gmail.com', NULL, '5466776435', NULL, '$2y$10$dh.42xn.6d8AKfYsFaxjlediKrfkFPe5GuBBs3vC.AoVwcZ9aUrYm', 'active', '2025-04-02 19:30:27', '2025-04-02 19:30:27', 0, 1000),
(35, 'ruhithree', 'ruhi3@gmail.com', NULL, '8788776787', NULL, '$2y$10$o8FlNyDEXuXXVWCsxGK5L.1wlMTktOSs6oudAOA/Rd2faKl4QpIFK', 'active', '2025-04-02 19:32:32', '2025-04-02 19:32:32', 0, 1000),
(36, 'ruhifour', 'ruhi4@gmail.com', NULL, '8779787787', NULL, '$2y$10$bUo2ZhK4KZ7xToKaOWXcaO1zxgomTHAb51r357nq0FMBv3ePZ2ofy', 'active', '2025-04-02 19:34:06', '2025-04-02 19:34:06', 0, 1000),
(37, 'ruhisix', 'ruhi6@gmail.com', NULL, '6764543654', NULL, '$2y$10$ZenpMb0wtdzJ.4o1EKOpwuBCEyERDzTr4C3yRTY4LZg9OhIbe9lSC', 'active', '2025-04-02 19:35:00', '2025-04-02 19:35:00', 0, 1000),
(76, 'APS', 'test@test.com', NULL, '0000000000', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-08 20:40:23', '2025-05-16 20:57:55', 0, 1000),
(77, 'Abhay Singh', 'techguruabhay@gmail.com', NULL, '7007444842', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-15 19:07:10', '2025-05-16 20:57:55', 0, 1000),
(78, 'Pravin kumar Prabhat', 'pravin.prabhat@yahoo.com', NULL, '9026336001', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-16 01:37:20', '2025-05-16 20:57:55', 0, 1000),
(79, 'Anita Singh', 'rudra.vir007@gmail.com', NULL, '7068013668', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-16 01:39:27', '2025-05-16 20:57:55', 0, 1000),
(80, 'Anuj kumar srivastava', 'devsrivastava74@gmail.com', NULL, '8707742187', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-16 06:16:34', '2025-05-16 20:57:55', 0, 1000),
(81, 'Rachna gupta', 'rachana@gmail.com', NULL, '7007414234', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-17 03:40:25', '2025-05-16 20:57:55', 0, 1000),
(82, 'Puneet kumar sinha', 'puneetsinha123@gmail.com', NULL, '9935883444', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-20 10:03:38', '2025-05-16 20:57:55', 0, 1000),
(83, 'Rahul Verma', 'rv83810@gmail.com', NULL, '8858763451', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-20 10:25:31', '2025-05-16 20:57:55', 0, 1000),
(84, 'Neeraj Kumar Singh', 'nerajsingh235@gmail.com', NULL, '9506362690', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-20 10:37:13', '2025-05-16 20:57:55', 0, 1000),
(85, 'Pramod kumar sharma', 'Pramod.rich1989@gmail.com', NULL, '8318037728', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-20 10:48:22', '2025-05-16 20:57:55', 0, 1000),
(86, 'ashok kumar', 'ashok12@gmail.com', NULL, '8808403728', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:42:30', '2025-05-16 20:57:55', 0, 1000),
(87, 'rinku chauhan', 'rinku@gmail.com', NULL, '9219494408', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:47:51', '2025-05-16 20:57:55', 0, 1000),
(88, 'SUNIL KUMAR', 'sunil12@gamil.com', NULL, '7348127038', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:49:43', '2025-05-16 20:57:55', 0, 1000),
(89, 'irfan ahmad', 'irfan12@gmail.com', NULL, '8318431354', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:53:49', '2025-05-16 20:57:55', 0, 1000),
(90, 'priya mishra', 'priyag.9671@gmail.com', NULL, '9140640713', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:54:28', '2025-05-16 20:57:55', 0, 1000),
(91, 'Rishikesh', 'rishi12@gmail.com', NULL, '8172938998', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:11:01', '2025-05-16 20:57:55', 0, 1000),
(92, 'rajni verma', 'rajniskn@gmail.com', NULL, '9956390332', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:11:45', '2025-05-16 20:57:55', 0, 1000),
(93, 'Rahul kannujiya', 'rahul12@gmail.com', NULL, '7607952353', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:14:25', '2025-05-16 20:57:55', 0, 1000),
(94, 'anuradha rai', 'anuradharai@gmail.com', NULL, '7678767656', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:15:00', '2025-05-16 20:57:55', 0, 1000),
(95, 'avinash pandey', 'avinash12@gmail.com', NULL, '9517241234', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:17:33', '2025-05-16 20:57:55', 0, 1000),
(96, 'avinash tiwari', 'tiwari1210@gmail.com', NULL, '8318721419', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:18:30', '2025-05-16 20:57:55', 0, 1000),
(97, 'shalu bharti', 'shalubharti@gmail.com', NULL, '9792398767', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:20:24', '2025-05-16 20:57:55', 0, 1000),
(98, 'anshika upadhyay', 'itsanshika454@gmail.com', NULL, '7754831158', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:21:46', '2025-05-16 20:57:55', 0, 1000),
(99, 'priyanka yadav', 'priyakayadav@gmail.com', NULL, '7607702191', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:23:41', '2025-05-16 20:57:55', 0, 1000),
(100, 'maggi', 'maggi@gmail.com', NULL, '6765676566', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-05 06:39:42', '2025-05-16 20:57:55', 0, 1000),
(101, 'abhay', 'abhay444@gmail.com', NULL, '6576646546', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-07 22:01:30', '2025-05-16 20:57:55', 0, 1000),
(102, 'abhay', 'anjuuuuu@gmail.com', NULL, '6565654345', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-08 14:37:23', '2025-05-16 20:57:55', 0, 1000),
(103, 'builder', 'builder@gmail.com', NULL, '3764765456', 'builder', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-09 17:35:07', '2025-05-16 20:57:55', 0, 1000),
(104, 'user', 'user@gmail.com', NULL, '4553767598', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-09 17:35:33', '2025-05-16 20:57:55', 0, 1000),
(105, 'agent', 'agent@gmail.com', NULL, '5546745554', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-09 17:44:21', '2025-05-16 20:57:55', 0, 1000),
(106, 'anuj22', 'anuj7656@gmail.com', NULL, '7898786566', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-09 20:24:14', '2025-05-16 20:57:55', 0, 1000),
(107, 'Abhay kumar singh', 'techgure434hjhuabhay@gmail.com', NULL, '7004499842', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-15 10:32:39', '2025-05-16 20:57:55', 0, 1000),
(108, 'Rohit', 'rohit123@gmail.com', NULL, '1234556786', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-15 14:00:32', '2025-05-16 20:57:55', 0, 1000),
(109, 'abhayy', 'abhayy3007@gmail.com', NULL, '5656787676', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-11-08 07:33:33', '2025-05-16 20:57:55', 0, 1000),
(110, 'praveen', 'praveen@gmail.com', NULL, '676765665', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-02-27 11:14:03', '2025-05-16 20:57:55', 0, 1000),
(139, 'admin', 'abhay3007@live.com', NULL, '07007444842', 'admin', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-24 18:30:00', '2025-05-16 20:57:55', 0, 1000),
(140, 'Rahul Sharma', 'rahul@example.com', NULL, '9876543210', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(141, 'Priya Singh', 'priya@example.com', NULL, '8765432109', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(142, 'Amit Kumar', 'amit@example.com', NULL, '7654321098', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(143, 'Neha Gupta', 'neha@example.com', NULL, '6543210987', 'builder', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(144, 'Vikram Patel', 'vikram@example.com', NULL, '5432109876', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(145, 'Demo User 1', 'demo.user1@aps.com', NULL, '9000010001', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(146, 'Demo User 2', 'demo.user2@aps.com', NULL, '9000010002', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(147, 'Demo Agent 1', 'demo.agent1@aps.com', NULL, '9000020001', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(148, 'Demo Agent 2', 'demo.agent2@aps.com', NULL, '9000020002', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(149, 'Demo Builder', 'demo.builder@aps.com', NULL, '9000030001', 'builder', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(150, 'Demo Customer', 'demo.customer@aps.com', NULL, '9000040001', 'customer', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(151, 'Demo Investor', 'demo.investor@aps.com', NULL, '9000040002', 'investor', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(152, 'Demo Tenant', 'demo.tenant@aps.com', NULL, '9000040003', 'tenant', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(166, 'Customer User', 'customer@demo.com', NULL, '7000000001', 'customer', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:26:09', '2025-05-16 20:57:55', 0, 1000),
(167, 'Investor User', 'investor@demo.com', NULL, '7000000002', 'investor', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:26:09', '2025-05-16 20:57:55', 0, 1000),
(168, 'Tenant User', 'tenant@demo.com', NULL, '7000000003', 'tenant', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:26:09', '2025-05-16 20:57:55', 0, 1000);

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

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `preference_key`, `preference_value`, `created_at`, `updated_at`) VALUES
(1, 76, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(2, 139, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(3, 108, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(4, 103, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(5, 104, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(6, 144, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(7, 105, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(8, 109, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(9, 143, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(10, 102, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(11, 101, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(12, 100, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(13, 110, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(14, 166, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(15, 167, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(16, 168, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(17, 107, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(18, 81, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(19, 77, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(20, 79, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(21, 88, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(22, 99, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(23, 93, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(24, 142, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(25, 94, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(26, 98, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(27, 106, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(28, 91, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(29, 85, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(30, 89, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(31, 96, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(32, 80, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(33, 141, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(34, 86, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(35, 83, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(36, 2, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(37, 3, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(38, 4, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(39, 5, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(40, 6, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(41, 7, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(42, 10, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(43, 11, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(44, 12, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(45, 13, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(46, 145, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(47, 146, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(48, 147, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(49, 148, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(50, 149, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(51, 150, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(52, 151, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(53, 152, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(54, 78, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(55, 90, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(56, 87, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(57, 84, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(58, 95, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(59, 97, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(60, 140, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(61, 82, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(62, 92, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(63, 1, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(2, 2);

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

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `login_time`, `logout_time`, `ip_address`, `status`) VALUES
(1, 1, '2025-05-01 00:00:00', '2025-05-01 00:00:00', 'Delhi', 'active'),
(2, 2, '2025-05-02 00:00:00', '2025-05-02 00:00:00', 'Mumbai', ''),
(3, 3, '2025-05-03 00:00:00', '2025-05-03 00:00:00', 'Bangalore', ''),
(4, 4, '2025-05-04 00:00:00', '2025-05-04 00:00:00', 'Ahmedabad', ''),
(5, 5, '2025-05-05 00:00:00', '2025-05-05 00:00:00', 'Pune', 'active');

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
-- Table structure for table `voice_assistant_config`
--

CREATE TABLE `voice_assistant_config` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voice_assistant_config`
--

INSERT INTO `voice_assistant_config` (`id`, `provider`, `api_key`, `created_at`) VALUES
(1, 'Value for provider 1', 'Value for api_key 1', '2025-05-01 00:00:00'),
(2, 'Value for provider 2', 'Value for api_key 2', '2025-05-07 00:00:00'),
(3, 'Value for provider 3', 'Value for api_key 3', '2025-05-13 00:00:00'),
(4, 'Value for provider 4', 'Value for api_key 4', '2025-05-19 00:00:00'),
(5, 'Value for provider 5', 'Value for api_key 5', '2025-05-25 00:00:00');

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

--
-- Dumping data for table `whatsapp_automation_config`
--

INSERT INTO `whatsapp_automation_config` (`id`, `provider`, `api_key`, `sender_number`, `created_at`) VALUES
(1, 'Value for provider 1', 'Value for api_key 1', 'Value for sender_number 1', '2025-05-01 00:00:00'),
(2, 'Value for provider 2', 'Value for api_key 2', 'Value for sender_number 2', '2025-05-07 00:00:00'),
(3, 'Value for provider 3', 'Value for api_key 3', 'Value for sender_number 3', '2025-05-13 00:00:00'),
(4, 'Value for provider 4', 'Value for api_key 4', 'Value for sender_number 4', '2025-05-19 00:00:00'),
(5, 'Value for provider 5', 'Value for api_key 5', 'Value for sender_number 5', '2025-05-25 00:00:00');

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
-- Dumping data for table `workflow_automations`
--

INSERT INTO `workflow_automations` (`id`, `name`, `provider`, `webhook_url`, `status`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Value for provider 1', 'Value for webhook_url 1', 'active', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for provider 2', 'Value for webhook_url 2', 'pending', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for provider 3', 'Value for webhook_url 3', 'completed', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for provider 4', 'Value for webhook_url 4', 'cancelled', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for provider 5', 'Value for webhook_url 5', 'active', '2025-05-25 00:00:00');

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

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
  ADD UNIQUE KEY `email` (`email`);

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
  ADD KEY `plot_id` (`plot_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `associate_id` (`associate_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commission_payouts`
--
ALTER TABLE `commission_payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `associate_id` (`associate_id`);

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
  ADD KEY `associate_id` (`associate_id`),
  ADD KEY `payout_id` (`payout_id`);

--
-- Indexes for table `mlm_commission_ledger`
--
ALTER TABLE `mlm_commission_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commission_id` (`commission_id`);

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
  ADD KEY `property_type_id` (`property_type_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `status` (`status`),
  ADD KEY `featured` (`featured`),
  ADD KEY `hot_offer` (`hot_offer`);
ALTER TABLE `properties` ADD FULLTEXT KEY `title` (`title`,`description`,`address`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `status` (`status`),
  ADD KEY `type` (`type`),
  ADD KEY `city` (`city`),
  ADD KEY `state` (`state`);

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
-- Indexes for table `property_types`
--
ALTER TABLE `property_types`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `commission_payouts`
--
ALTER TABLE `commission_payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mlm_commission_ledger`
--
ALTER TABLE `mlm_commission_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `property_types`
--
ALTER TABLE `property_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `smart_contracts`
--
ALTER TABLE `smart_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `third_party_integrations`
--
ALTER TABLE `third_party_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `upload_audit_log`
--
ALTER TABLE `upload_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

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
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `ai_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

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
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `commission_payouts`
--
ALTER TABLE `commission_payouts`
  ADD CONSTRAINT `commission_payouts_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `emi`
--
ALTER TABLE `emi`
  ADD CONSTRAINT `emi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

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
  ADD CONSTRAINT `land_purchases_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`);

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `mlm_commissions`
--
ALTER TABLE `mlm_commissions`
  ADD CONSTRAINT `mlm_commissions_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mlm_commissions_ibfk_2` FOREIGN KEY (`payout_id`) REFERENCES `commission_payouts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mlm_commission_ledger`
--
ALTER TABLE `mlm_commission_ledger`
  ADD CONSTRAINT `mlm_commission_ledger_ibfk_1` FOREIGN KEY (`commission_id`) REFERENCES `mlm_commissions` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`property_type_id`) REFERENCES `property_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `properties_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `rental_properties`
--
ALTER TABLE `rental_properties`
  ADD CONSTRAINT `rental_properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rent_payments`
--
ALTER TABLE `rent_payments`
  ADD CONSTRAINT `rent_payments_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `resale_commissions`
--
ALTER TABLE `resale_commissions`
  ADD CONSTRAINT `resale_commissions_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
