-- APS Dream Home - Ultimate Complete Database
-- Generated: 2025-09-22
-- This file contains the most comprehensive database structure

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS apsdreamhome_ultimate;
USE apsdreamhome_ultimate;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user','agent') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Properties table
CREATE TABLE IF NOT EXISTS `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `location` varchar(255) NOT NULL,
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `status` enum('available','sold','rented') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Projects table
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `status` enum('active','inactive','completed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plots table
CREATE TABLE IF NOT EXISTS `plots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `plot_no` varchar(50) NOT NULL,
  `size_sqft` decimal(10,2) DEFAULT NULL,
  `status` enum('available','booked','sold','rented','resale') NOT NULL DEFAULT 'available',
  `customer_id` int(11) DEFAULT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `customer_id` (`customer_id`),
  KEY `associate_id` (`associate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Associates table
CREATE TABLE IF NOT EXISTS `associates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `level` int(11) NOT NULL DEFAULT 1,
  `join_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers table
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transactions table
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `type` enum('booking','payment','refund') NOT NULL DEFAULT 'payment',
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `property_id` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MLM Commissions table
CREATE TABLE IF NOT EXISTS `mlm_commissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `level` int(11) NOT NULL,
  `status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `associate_id` (`associate_id`),
  KEY `customer_id` (`customer_id`),
  KEY `property_id` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leads table
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `source` varchar(50) DEFAULT NULL,
  `status` enum('new','contacted','qualified','converted','lost') NOT NULL DEFAULT 'new',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@apsdreamhome.com', '$2y$10$8K3VZvCYrQ9nJXZqW7Y8KeX8vN9vZ8zQ5Y7Z9X3C7B5D1F9H7J5L3', 'admin'),
('user', 'user@example.com', '$2y$10$8K3VZvCYrQ9nJXZqW7Y8KeX8vN9vZ8zQ5Y7Z9X3C7B5D1F9H7J5L3', 'user');

INSERT INTO `projects` (`name`, `description`, `location`, `status`) VALUES
('Dream Valley', 'Premium residential project with modern amenities', 'Mumbai', 'active'),
('Green City', 'Eco-friendly housing project', 'Delhi', 'active'),
('Smart Homes', 'Technology integrated residential complex', 'Pune', 'active');

INSERT INTO `properties` (`title`, `description`, `price`, `location`, `bedrooms`, `bathrooms`) VALUES
('Luxury Villa', 'Beautiful 4BHK villa with modern amenities', 2500000.00, 'Mumbai', 4, 3),
('2BHK Apartment', 'Cozy apartment in prime location', 1200000.00, 'Delhi', 2, 2),
('3BHK Flat', 'Spacious flat with great view', 1800000.00, 'Pune', 3, 2),
('Penthouse', 'Luxurious penthouse with city view', 4500000.00, 'Mumbai', 5, 4);

INSERT INTO `customers` (`name`, `email`, `phone`) VALUES
('John Doe', 'john@example.com', '9876543210'),
('Jane Smith', 'jane@example.com', '8765432109'),
('Robert Johnson', 'robert@example.com', '7654321098'),
('Mary Davis', 'mary@example.com', '6543210987');

INSERT INTO `associates` (`name`, `email`, `phone`, `level`) VALUES
('Rajesh Kumar', 'rajesh@associate.com', '9123456789', 1),
('Priya Sharma', 'priya@associate.com', '9234567890', 2),
('Amit Patel', 'amit@associate.com', '9345678901', 1),
('Sneha Reddy', 'sneha@associate.com', '9456789012', 3);

-- Add foreign key constraints
ALTER TABLE `plots`
  ADD CONSTRAINT `plots_project_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `plots_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `plots_associate_fk` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL;

ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_property_fk` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_property_fk` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

ALTER TABLE `mlm_commissions`
  ADD CONSTRAINT `mlm_commissions_associate_fk` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mlm_commissions_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mlm_commissions_property_fk` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

COMMIT;
