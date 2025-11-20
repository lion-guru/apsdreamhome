-- 005_add_additional_foreign_keys.sql
-- Generated: 2025-11-01 21:52:28
-- Add safe additional foreign keys discovered across the schema

-- plots.customer_id -> users.id
-- SKIP INDEX: `ix_plots_customer_id` already exists
-- SKIP FK: `fk_plots_customer_id` already exists

-- plots.associate_id -> associates.id
-- SKIP INDEX: `ix_plots_associate_id` already exists
-- SKIP FK: `fk_plots_associate_id` already exists

-- property_visits.property_id -> properties.id
-- SKIP INDEX: `ix_property_visits_property_id` already exists
-- SKIP FK: `fk_property_visits_property_id` already exists

-- property_visits.customer_id -> users.id
-- SKIP INDEX: `ix_property_visits_customer_id` already exists
-- SKIP FK: `fk_property_visits_customer_id` already exists

-- property_visits.created_by -> users.id
-- SKIP INDEX: `ix_property_visits_created_by` already exists
-- SKIP FK: `fk_property_visits_created_by` already exists

-- mlm_commissions.associate_id -> associates.id
-- SKIP INDEX: `ix_mlm_commissions_associate_id` already exists
-- SKIP FK: `fk_mlm_commissions_associate_id` already exists

-- SKIP: `mlm_commissions`.`booking_id` or `bookings` PK missing
-- leads.assigned_to -> users.id
-- SKIP INDEX: `ix_leads_assigned_to` already exists
-- SKIP FK: `fk_leads_assigned_to` already exists

-- SKIP: `properties`.`type_id` or `property_types` PK missing
-- associates.user_id -> users.id
-- SKIP INDEX: `ix_associates_user_id` already exists
-- SKIP FK: `fk_associates_user_id` already exists

-- SKIP: `documents`.`owner_user_id` or `users` PK missing
-- SKIP: `projects`.`land_purchase_id` or `land_purchases` PK missing