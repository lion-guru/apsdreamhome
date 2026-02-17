-- Create property_type table that's missing in the database
CREATE TABLE IF NOT EXISTS `property_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `description` text,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default property types
INSERT INTO `property_type` (`type`, `description`, `status`) VALUES
('Residential Plot', 'Land for residential building construction', 1),
('Commercial Plot', 'Land for commercial building construction', 1),
('Villa', 'Independent luxury house with garden', 1),
('Apartment', 'Unit in multi-dwelling building', 1),
('Shop', 'Commercial retail space', 1),
('Office Space', 'Commercial office space', 1);