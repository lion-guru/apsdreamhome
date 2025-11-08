# APS Dream Home Database Management Suite

This directory contains a comprehensive set of tools for managing the APS Dream Home database, particularly focused on maintaining demo data for effective system demonstrations and testing.

## Database Management Hub

The central interface for accessing all database management tools is available at:
```
http://localhost/apsdreamhome/database/
```

From this hub, you can access all the tools described below and get a quick overview of your database status.

## Available Tools

### 1. Dashboard Data Manager
**File:** `dashboard_data_manager.php`

This tool allows you to:
- Check the current status of all dashboard widgets
- View record counts across all tables
- Refresh demo data with a single click
- Identify missing or empty tables

### 2. Dashboard Verification Report
**File:** `dashboard_verification_report.php`

Generates a comprehensive report that:
- Analyzes all dashboard widgets and their data sources
- Checks for data quality issues like orphaned records
- Provides visual charts of data distribution
- Makes specific recommendations for any missing data

### 3. Database Optimizer
**File:** `optimize_database.php`

Optimizes your database by:
- Removing duplicate records
- Fixing orphaned records (missing foreign key references)
- Optimizing table structures
- Cleaning up temporary data

### 4. Database Migration Tools
**File:** `run_migrations.php`

Manages database schema migrations:
- Applies pending database migrations in version order
- Tracks applied migrations in a dedicated table
- Supports rollback on failure
- Provides detailed output of migration progress

### 5. User Preferences Verifier
**File:** `verify_user_preferences.php`

Validates and fixes the user preferences system:
- Checks table structure and indexes
- Verifies all users have required preferences
- Fixes missing or invalid preference values
- Provides statistics on user preferences

### 6. Backup & Restore Tool
**File:** `backup_demo_data.php`

Helps you manage database backups:
- Create SQL dumps of your entire database
- Download backups for safekeeping
- Restore from previous backups
- Delete old backups

### 7. Date Refresher
**File:** `refresh_demo_dates.php`

Keeps your demo data current by:
- Updating all date-based fields to reflect current dates
- Shifting transaction dates, visit dates, and other time-sensitive data
- Ensuring your dashboard always shows relevant recent and upcoming data

### 8. Structure-Based Seeder
**File:** `structure_based_seed.php`

Intelligently populates your database:
- Analyzes your exact table structures
- Creates data that matches your specific schema
- Ensures proper relationships between tables
- Uses realistic values for real estate data

### 9. Final Dashboard Check
**File:** `final_dashboard_check.php`

Ensures all dashboard widgets display properly:
- Verifies all required tables exist
- Checks that each table has sufficient data
- Creates missing tables if needed
- Adds demo data where it's missing

### 10. Migration and Verification Tools
**Files:** `migration_tool.php`, `verification_tool.php`

These tools help manage database schema changes and verify data integrity:
- The migration tool applies schema changes in a controlled manner
- The verification tool checks for data consistency and reports any issues

## Demo Data Documentation

For detailed information about the demo data structure, refer to:
**File:** `README_DEMO_DATA.md`

This document provides:
- Overview of all populated tables
- Description of dashboard widgets and their data sources
- Instructions for refreshing demo data
- Guidance on data maintenance

## Quick Start Guide

1. **Check Database Status:**
   - Open the Database Management Hub
   - Review the status of core tables

2. **Verify Dashboard Data:**
   - Run the Dashboard Verification Report
   - Address any recommendations

3. **Refresh Demo Data if Needed:**
   - Use the Dashboard Data Manager to refresh specific tables
   - Or run the Structure-Based Seeder for a complete refresh

4. **Create a Backup:**
   - Use the Backup & Restore Tool to create a backup
   - Download the backup file for safekeeping

5. **Update Date-Based Data:**
   - Run the Date Refresher to update all date fields
   - Ensure all widgets show current and relevant data

## Maintenance Recommendations

1. **Regular Backups:**
   - Create backups before making significant changes
   - Keep at least one recent backup downloaded

2. **Date Refreshing:**
   - Run the Date Refresher monthly to keep demo data current
   - This ensures dashboards and reports show relevant time-based data

3. **Database Optimization:**
   - Run the Database Optimizer quarterly
   - This improves performance and fixes data integrity issues

4. **Data Verification:**
   - Run the Dashboard Verification Report after any system updates
   - This ensures all widgets continue to display properly

## Troubleshooting

If dashboard widgets show "No data found" or zero counts:

1. Run the Dashboard Verification Report to identify the issue
2. Use the Dashboard Data Manager to refresh specific tables
3. If problems persist, run the Final Dashboard Check
4. For comprehensive refresh, run the Structure-Based Seeder

---

*This documentation was created on May 17, 2025, as part of the APS Dream Home demo data management system.*
