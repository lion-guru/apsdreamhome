-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS apsdreamhome;

-- Use the database
USE apsdreamhome;

-- Set root password for localhost
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;