-- MASTER DEMO ENVIRONMENT SETUP SCRIPT
-- This script creates all required tables and seeds demo data for a fresh environment.

-- 1. Create state table
CREATE TABLE IF NOT EXISTS `state` (
  `sid` INT AUTO_INCREMENT PRIMARY KEY,
  `sname` VARCHAR(100) NOT NULL UNIQUE
);

-- 2. Create city table
CREATE TABLE IF NOT EXISTS `city` (
  `cid` INT AUTO_INCREMENT PRIMARY KEY,
  `cname` VARCHAR(100) NOT NULL,
  `sid` INT,
  FOREIGN KEY (`sid`) REFERENCES `state`(`sid`)
);

-- 3. Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20),
  `utype` ENUM('user','agent','builder','customer','investor','tenant') DEFAULT 'user',
  `status` VARCHAR(20) DEFAULT 'active',
  `address` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Create employees table
CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20),
  `role` VARCHAR(50) DEFAULT 'employee',
  `status` VARCHAR(20) DEFAULT 'active',
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Create admin table
CREATE TABLE IF NOT EXISTS `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `auser` VARCHAR(100) NOT NULL UNIQUE,
  `apass` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `status` VARCHAR(20) DEFAULT 'active',
  `email` VARCHAR(255),
  `phone` VARCHAR(20)
);

-- 6. Create associates table
CREATE TABLE IF NOT EXISTS `associates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20),
  `commission_percent` DECIMAL(5,2),
  `level` INT DEFAULT 1,
  `status` VARCHAR(20) DEFAULT 'active'
);

-- 7. Create property table (minimal for demo)
CREATE TABLE IF NOT EXISTS `property` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `pcontent` TEXT,
  `type` VARCHAR(100),
  `bhk` VARCHAR(20),
  `stype` VARCHAR(20),
  `bedroom` INT,
  `bathroom` INT,
  `balcony` INT,
  `kitchen` INT,
  `hall` INT,
  `floor` VARCHAR(20),
  `size` INT,
  `price` DECIMAL(15,2),
  `location` VARCHAR(255),
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `feature` TEXT,
  `pimage` VARCHAR(255),
  `pimage1` VARCHAR(255),
  `pimage2` VARCHAR(255),
  `pimage3` VARCHAR(255),
  `pimage4` VARCHAR(255),
  `uid` INT,
  `status` VARCHAR(20),
  `mapimage` VARCHAR(255),
  `topmapimage` VARCHAR(255),
  `groundmapimage` VARCHAR(255),
  `totalfloor` VARCHAR(20),
  `isFeatured` TINYINT(1)
);

-- 8. Seed all demo/sample data
SOURCE insert_sample_data.sql;

-- Done. All demo tables and data are ready for use.
