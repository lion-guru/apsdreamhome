-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS apsdreamhomefinal;

-- Use the database
USE apsdreamhomefinal;

-- Set root password for localhost
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;