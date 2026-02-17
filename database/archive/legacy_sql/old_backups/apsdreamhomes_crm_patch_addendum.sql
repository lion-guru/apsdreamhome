-- Fix plots table so id is AUTO_INCREMENT PRIMARY KEY
ALTER TABLE `plots`
  MODIFY COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (`id`);

-- Now create plot_sales table with foreign key
CREATE TABLE IF NOT EXISTS `plot_sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plot_id` INT NOT NULL,
  `buyer_id` INT NOT NULL,
  `seller_id` INT DEFAULT NULL,
  `sale_date` DATE NOT NULL,
  `amount` DECIMAL(12,2) DEFAULT NULL,
  `notes` TEXT,
  FOREIGN KEY (`plot_id`) REFERENCES `plots`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
