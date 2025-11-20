# FK Cascade Policy Proposal
Generated: 2025-11-01T22:25:33+01:00

## addresses -> properties (fk_addresses_property_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: SET NULL

- No change suggested.

## addresses -> users (fk_addresses_user_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: SET NULL

- No change suggested.

## associates -> users (fk_associates_user_id)
- Current: DELETE RESTRICT, UPDATE CASCADE
- Child rows: 5
- Suggested DELETE rule: RESTRICT

- No change suggested.

## associates -> users (fk_associate_user)
- Current: DELETE CASCADE, UPDATE RESTRICT
- Child rows: 5
- Suggested DELETE rule: RESTRICT

```sql
ALTER TABLE `associates` DROP FOREIGN KEY `fk_associate_user`;
ALTER TABLE `associates` ADD CONSTRAINT `fk_associate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
```

## bookings -> properties (bookings_ibfk_1)
- Current: DELETE CASCADE, UPDATE RESTRICT
- Child rows: 16
- Suggested DELETE rule: CASCADE

- No change suggested.

## bookings -> users (bookings_ibfk_2)
- Current: DELETE CASCADE, UPDATE RESTRICT
- Child rows: 16
- Suggested DELETE rule: SET NULL

```sql
ALTER TABLE `bookings` DROP FOREIGN KEY `bookings_ibfk_2`;
ALTER TABLE `bookings` ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT;
```

## bookings -> users (fk_bookings_customer_id)
- Current: DELETE RESTRICT, UPDATE CASCADE
- Child rows: 16
- Suggested DELETE rule: SET NULL

```sql
ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_customer_id`;
ALTER TABLE `bookings` ADD CONSTRAINT `fk_bookings_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
```

## bookings -> properties (fk_bookings_property_id)
- Current: DELETE RESTRICT, UPDATE CASCADE
- Child rows: 16
- Suggested DELETE rule: CASCADE

```sql
ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_property_id`;
ALTER TABLE `bookings` ADD CONSTRAINT `fk_bookings_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
```

## customers -> users (customers_ibfk_1)
- Current: DELETE RESTRICT, UPDATE RESTRICT
- Child rows: 1
- Suggested DELETE rule: RESTRICT

- No change suggested.

## documents -> properties (fk_documents_property_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: SET NULL

- No change suggested.

## favorites -> properties (fk_favorites_property_id)
- Current: DELETE CASCADE, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: RESTRICT

```sql
ALTER TABLE `favorites` DROP FOREIGN KEY `fk_favorites_property_id`;
ALTER TABLE `favorites` ADD CONSTRAINT `fk_favorites_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
```

## favorites -> users (fk_favorites_user_id)
- Current: DELETE CASCADE, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: RESTRICT

```sql
ALTER TABLE `favorites` DROP FOREIGN KEY `fk_favorites_user_id`;
ALTER TABLE `favorites` ADD CONSTRAINT `fk_favorites_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
```

## leads -> users (fk_leads_assigned_to)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 80
- Suggested DELETE rule: SET NULL

- No change suggested.

## mlm_commissions -> associates (fk_mlm_commissions_associate_id)
- Current: DELETE RESTRICT, UPDATE CASCADE
- Child rows: 42
- Suggested DELETE rule: RESTRICT

- No change suggested.

## notifications -> users (fk_notifications_user_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: SET NULL

- No change suggested.

## payments -> bookings (fk_payments_booking_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 100
- Suggested DELETE rule: SET NULL

- No change suggested.

## payments -> customers (fk_payments_customer_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 100
- Suggested DELETE rule: SET NULL

- No change suggested.

## plots -> associates (fk_plots_associate_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: SET NULL

- No change suggested.

## plots -> users (fk_plots_customer_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: SET NULL

- No change suggested.

## plots -> projects (fk_plots_project_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: SET NULL

- No change suggested.

## properties -> users (properties_ibfk_2)
- Current: DELETE SET NULL, UPDATE RESTRICT
- Child rows: 50
- Suggested DELETE rule: SET NULL

- No change suggested.

## properties -> users (properties_ibfk_3)
- Current: DELETE SET NULL, UPDATE RESTRICT
- Child rows: 50
- Suggested DELETE rule: SET NULL

- No change suggested.

## property_feature_map -> property_features (fk_property_feature_map_feature)
- Current: DELETE CASCADE, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: RESTRICT

```sql
ALTER TABLE `property_feature_map` DROP FOREIGN KEY `fk_property_feature_map_feature`;
ALTER TABLE `property_feature_map` ADD CONSTRAINT `fk_property_feature_map_feature` FOREIGN KEY (`feature_id`) REFERENCES `property_features` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
```

## property_feature_map -> properties (fk_property_feature_map_property)
- Current: DELETE CASCADE, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: RESTRICT

```sql
ALTER TABLE `property_feature_map` DROP FOREIGN KEY `fk_property_feature_map_property`;
ALTER TABLE `property_feature_map` ADD CONSTRAINT `fk_property_feature_map_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
```

## property_images -> properties (fk_property_images_property_id)
- Current: DELETE CASCADE, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: CASCADE

- No change suggested.

## property_visits -> users (fk_property_visits_created_by)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: SET NULL

- No change suggested.

## property_visits -> users (fk_property_visits_customer_id)
- Current: DELETE CASCADE, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: CASCADE

- No change suggested.

## property_visits -> properties (fk_property_visits_property_id)
- Current: DELETE CASCADE, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: CASCADE

- No change suggested.

## saved_searches -> users (fk_saved_searches_user_id)
- Current: DELETE RESTRICT, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: RESTRICT

- No change suggested.

## sessions -> users (fk_sessions_user_id)
- Current: DELETE RESTRICT, UPDATE CASCADE
- Child rows: 0
- Suggested DELETE rule: RESTRICT

- No change suggested.

## transactions -> customers (fk_transactions_customer_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 2
- Suggested DELETE rule: SET NULL

- No change suggested.

## transactions -> properties (fk_transactions_property_id)
- Current: DELETE SET NULL, UPDATE CASCADE
- Child rows: 2
- Suggested DELETE rule: SET NULL

- No change suggested.
