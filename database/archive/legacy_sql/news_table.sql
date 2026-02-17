-- No code changes required for SQL logics. If you want to log newsletter/registration submissions, please specify the PHP file handling those forms.

CREATE TABLE IF NOT EXISTS `news` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `date` DATE NOT NULL,
  `summary` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `content` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
