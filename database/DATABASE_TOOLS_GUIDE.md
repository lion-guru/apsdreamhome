# APS Dream Home Database Tools Guide

This guide provides detailed information about the database management tools available in the APS Dream Home system, with special focus on the recently added tools for fixing common issues and ensuring all dashboard widgets display properly.

## Database Migration Tools

### 1. Run Migrations
**File:** `run_migrations.php`

This tool manages database schema migrations:
- Applies pending database migrations in version order
- Tracks applied migrations in a dedicated table
- Supports rollback on failure
- Provides detailed output of migration progress

**When to use:** 
- After deploying new code that includes database changes
- When you need to apply schema updates
- As part of your deployment process

### 2. Verify User Preferences
**File:** `verify_user_preferences.php`

Validates and fixes the user preferences system:
- Checks table structure and indexes
- Verifies all users have required preferences
- Fixes missing or invalid preference values
- Provides statistics on user preferences

**When to use:**
- After running database migrations
- When user preferences are not working as expected
- As part of regular database maintenance

## Recently Added Tools

### 1. Fix MLM Commission Tables
**File:** `fix_mlm_commission_tables.php`

This tool addresses inconsistencies in the MLM commission system:
- Creates and populates the `associates` table if missing
- Ensures both `mlm_commission_ledger` and `mlm_commissions` tables exist
- Synchronizes data between the two tables for consistency
- Fixes associates with missing names (prevents htmlspecialchars() warnings)
- Ensures all commission data is properly structured for dashboard display

**When to use:** Run this tool if you see htmlspecialchars() warnings in the MLM Commission widget or if commission data appears incomplete.

### 2. Fix Leads Data
**File:** `fix_leads_data.php`

Fixes missing or incomplete data in the leads table:
- Adds proper names to leads with missing names
- Adds contact information to leads with missing contact details
- Adds source information to leads with missing sources
- Adds status information to leads with missing statuses
- Assigns leads to appropriate agents

**When to use:** Run this tool if lead-related widgets show incomplete information or if you see errors related to null values in the leads table.

### 3. Check Leads Table
**File:** `check_leads_table.php`

Diagnostic tool that analyzes the leads table:
- Shows the complete structure of the leads table
- Displays information about converted leads
- Identifies leads with missing data
- Helps diagnose issues with lead-related dashboard widgets

**When to use:** Run this tool as a diagnostic step before using the Fix Leads Data tool.

### 4. Check Tables
**File:** `check_tables.php`

General diagnostic tool for examining database tables:
- Shows MLM-related tables and their structure
- Displays record counts for each table
- Identifies tables with missing or incomplete data
- Helps diagnose issues with various dashboard widgets

**When to use:** Run this tool to get a comprehensive overview of your database structure and identify potential issues.

## Using the Migration System

### Running Migrations
1. Place new migration files in the `database/migrations` directory
2. Name files in the format: `V{version}__{description}.sql` (e.g., `V1.0.1__Add_User_Preferences_Table.sql`)
3. Run `php database/run_migrations.php` from the project root
4. Review the output for any issues

### Creating New Migrations
1. Determine the next version number (increment from the latest migration)
2. Create a new SQL file in the migrations directory
3. Include all necessary SQL statements with proper error handling
4. Test the migration in a development environment first

## Common Issues and Solutions

### 1. "Headers already sent" Error
This error occurs when PHP tries to modify HTTP headers after content has already been sent to the browser.

**Solution:**
- Ensure all session management code is at the top of PHP files
- Check for whitespace or output before session_start() or header() calls
- Use output buffering if necessary

### 2. htmlspecialchars() Warnings
These warnings occur when null values are passed to the htmlspecialchars() function.

**Solution:**
- Run the Fix MLM Commission Tables tool
- Add null coalescing operators (??) to htmlspecialchars() calls
- Ensure database queries don't return null values for text fields

### 3. Missing Table Columns
Errors occur when SQL queries reference columns that don't exist in the tables.

**Solution:**
- Run the Final Dashboard Check tool to add missing columns
- Use DESCRIBE queries to check table structures before updates
- Add ALTER TABLE statements to add missing columns

### 4. Data Quality Issues
Dashboard widgets may show "No data found" or incorrect information due to missing or invalid data.

**Solution:**
- Run the Fix Leads Data tool to repair lead information
- Use the Dashboard Data Manager to refresh specific tables
- Run the Final Dashboard Check for comprehensive data verification

## Maintenance Schedule

For optimal performance of your APS Dream Home system, follow this maintenance schedule:

1. **Daily:**
   - Check the Database Status section in the Database Management Hub

2. **Weekly:**
   - Run the Dashboard Verification Report
   - Fix any identified issues with the appropriate tools

3. **Monthly:**
   - Run the Date Refresher to keep demo data current
   - Run the Database Optimizer to maintain performance
   - Create a backup using the Backup & Restore Tool

4. **After System Updates:**
   - Run the Final Dashboard Check
   - Verify all dashboard widgets display properly
   - Fix any issues with the specialized tools

## Integration with Existing Tools

The new tools complement the existing database management suite:

1. **Dashboard Verification → Fix Leads Data / Fix MLM Commission Tables**
   - Use the verification report to identify issues
   - Use the fix tools to address specific problems

2. **Final Dashboard Check → Check Tables / Check Leads Table**
   - Run the final check to ensure all widgets have data
   - Use the diagnostic tools to investigate any remaining issues

3. **Database Optimizer → Fix Tools**
   - Optimize the database structure first
   - Then use the fix tools to address data quality issues

---

## Best Practices

1. **Backup First**
   - Always backup your database before running migrations
   - Use the Backup & Restore Tool before making structural changes

2. **Test Migrations**
   - Test all migrations in a development environment first
   - Verify data integrity after migration

3. **Document Changes**
   - Update this guide when adding new tools or features
   - Include migration notes in your version control commit messages

## Troubleshooting

### Migration Fails
1. Check for syntax errors in your SQL
2. Verify all referenced tables and columns exist
3. Look for foreign key constraints that might prevent changes
4. Check the database error log for details

### Missing Preferences
1. Run the Verify User Preferences tool to identify issues
2. Check that all users have the required preference keys
3. Verify that preference values are valid JSON

*This guide was last updated on May 25, 2025, as part of the ongoing improvements to the APS Dream Home database management system.*
