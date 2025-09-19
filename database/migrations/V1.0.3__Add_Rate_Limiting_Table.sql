-- Create rate limiting table
CREATE TABLE IF NOT EXISTS `api_rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `api_key` (`api_key`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add index for better performance
ALTER TABLE `api_rate_limits` ADD INDEX `api_key_timestamp` (`api_key`, `timestamp`);

-- Add rate limit column to users table if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = "users";
SET @columnname = "api_rate_limit";
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (table_schema = @dbname)
            AND (table_name = @tablename)
            AND (column_name = @columnname)
    ) > 0,
    "SELECT 1",
    CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " INT NOT NULL DEFAULT 1000 COMMENT 'API rate limit (requests per minute)' AFTER `status`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing users with default rate limit if not set
UPDATE `users` SET `api_rate_limit` = 1000 WHERE `api_rate_limit` = 0 OR `api_rate_limit` IS NULL;
