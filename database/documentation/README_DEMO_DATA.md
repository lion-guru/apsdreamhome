# APS Dream Home Demo Data Documentation

## Overview

This document provides information about the demo data that has been seeded into the APS Dream Home database to ensure all dashboard widgets and features display properly. The data was generated on May 17, 2025.

## Tables Populated

The following tables have been populated with realistic demo data:

### Core Tables
- **users**: Admin, agent, and customer accounts
- **properties**: Various property types (villas, apartments, houses)
- **customers**: Customer profiles with contact information
- **leads**: Inquiries with various statuses and sources
- **bookings**: Property bookings with dates and amounts
- **transactions**: Financial transactions with amounts and dates

### Supporting Tables
- **property_visits**: Scheduled and completed property visits
- **visit_reminders**: Reminders for upcoming visits
- **notifications**: System and user notifications
- **feedback**: Customer feedback and ratings
- **gallery**: Property images and captions
- **testimonials**: Customer testimonials
- **mlm_commissions**: Agent commissions and earnings

## Dashboard Widgets

All dashboard widgets have been populated with meaningful data:

- **Properties Count**: Shows total properties in the system
- **Customers Count**: Shows total customers in the system
- **Bookings Count**: Shows total bookings in the system
- **Inquiries/Leads Count**: Shows total leads in the system
- **Recent Bookings**: Displays latest property bookings
- **Recent Transactions**: Shows latest financial transactions
- **Recent Inquiries/Leads**: Lists latest customer inquiries
- **Upcoming Visit Reminders**: Shows scheduled property visits
- **Recent Notifications**: Displays system and user notifications
- **MLM Commission Overview**: Shows commission distribution and top earners
- **Leads Converted**: Displays lead conversion metrics

## Refreshing Demo Data

If you need to refresh or regenerate the demo data in the future, you can use the following scripts:

1. **Complete Database Seed**:
   ```
   php database/complete_database_seed.php
   ```
   This script examines all tables in the database and populates them with appropriate demo data.

2. **Structure-Based Seed**:
   ```
   php database/structure_based_seed.php
   ```
   This script analyzes the exact structure of each table and generates data that matches the schema.

3. **Final Dashboard Check**:
   ```
   php database/final_dashboard_check.php
   ```
   This script specifically targets dashboard widgets and ensures they all display data.

## Adding More Data

If you need to add more specialized data for specific features:

1. Identify the relevant tables for the feature
2. Check the existing schema using `DESCRIBE table_name`
3. Create an SQL script with appropriate INSERT statements
4. Run the script using the MySQL command line or phpMyAdmin

## Data Maintenance

To maintain the demo data:

1. **Backup**: Before making significant changes, back up your database
2. **Consistency**: Ensure new data maintains referential integrity
3. **Realistic Values**: Use realistic values that match your business domain
4. **Date Awareness**: Update dates periodically to keep them current

## Troubleshooting

If dashboard widgets show "No data found" or zero counts:

1. Check if the relevant table exists using `SHOW TABLES LIKE 'table_name'`
2. Verify the table has data using `SELECT COUNT(*) FROM table_name`
3. Check if the dashboard query is correctly formatted
4. Run the `final_dashboard_check.php` script to repopulate missing data

---

*This documentation was generated on May 17, 2025, as part of the APS Dream Home demo data seeding process.*
