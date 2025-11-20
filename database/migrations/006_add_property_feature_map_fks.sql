-- 006_add_property_feature_map_fks.sql
-- Generated: 2025-11-01 20:34:28
-- Add foreign keys for property_feature_map join table

ALTER TABLE `property_feature_map` ADD CONSTRAINT `fk_property_feature_map_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `property_feature_map` MODIFY `feature_id` int(11) NOT NULL;
ALTER TABLE `property_feature_map` ADD CONSTRAINT `fk_property_feature_map_feature` FOREIGN KEY (`feature_id`) REFERENCES `property_features`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;