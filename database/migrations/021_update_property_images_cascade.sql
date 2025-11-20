-- Migration: 021_update_property_images_cascade.sql
-- Purpose: Set property_images.property_id FK to ON DELETE CASCADE

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `property_images` DROP FOREIGN KEY `fk_property_images_property_id`;
ALTER TABLE `property_images`
  ADD CONSTRAINT `fk_property_images_property_id`
  FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

