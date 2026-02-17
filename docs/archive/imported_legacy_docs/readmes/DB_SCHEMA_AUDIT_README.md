# Database Schema Audit Tool

## Overview
A comprehensive PHP-based tool for analyzing and auditing MySQL database schemas, providing insights into table structures, potential issues, and improvement recommendations.

## Features
- Detailed table structure analysis
- Integrity checks
- Potential issue identification
- Automated recommendations
- HTML report generation
- Logging mechanism

## Usage

### Command Line
```bash
php db_schema_audit.php
```

### Web Interface
Navigate to `db_schema_audit.php` in your browser

## Audit Checks
- Primary key existence
- Critical column nullability
- Indexing analysis
- Table structure overview

## Output
- Comprehensive HTML report
- Detailed log file
- Console output with key findings

## Recommendations
- Run periodically during development
- Review generated reports carefully
- Address potential issues proactively

## Configuration
- Modify `DatabaseSchemaAuditor` class for custom checks
- Adjust logging and reporting as needed

## Dependencies
- PHP 7.4+
- PDO Extension
- Database Security Upgrade Class

## Security
- Uses prepared statements
- Implements secure logging
- Validates database interactions

## Troubleshooting
- Check `logs/db_schema_audit_*.log` for detailed logs
- Verify database connection settings
- Ensure proper permissions
