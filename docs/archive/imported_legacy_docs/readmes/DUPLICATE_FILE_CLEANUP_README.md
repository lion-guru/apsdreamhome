# Automated Duplicate File Cleanup

## Overview
A comprehensive PHP script for detecting and removing duplicate files across a project, with advanced configuration and logging capabilities.

## Features
- Recursive file search
- Configurable file type detection
- Intelligent duplicate identification
- Selective directory exclusion
- Detailed logging
- Dry run mode
- JSON report generation

## Configuration
Customize via `config/duplicate_file_cleanup_config.json`:
- Ignored directories
- File types to check
- Minimum file size
- Hash method
- Dry run mode

## Usage Modes

### Command Line
```bash
php duplicate_file_cleanup.php
```

### Dry Run
Set `dry_run: true` in configuration to preview duplicates without deletion

## Configuration Options
- `ignore_directories`: Exclude specific directories
- `file_types_to_check`: Select file extensions
- `hash_method`: Choose hash algorithm
- `min_file_size_bytes`: Set minimum file size
- `dry_run`: Enable safe preview mode

## Duplicate Detection
- Content-based comparison
- Whitespace and comment removal
- Configurable hash method

## Logging
- Detailed log files
- JSON report generation
- Timestamp tracking
- Error logging

## Security Considerations
- Configurable file type restrictions
- Selective file removal
- Comprehensive logging
- Dry run safety mode

## Dependencies
- PHP 7.4+
- RecursiveIterator support

## Best Practices
- Backup project before running
- Review log files
- Use dry run mode first
- Customize configuration

## Troubleshooting
- Check `logs/duplicate_file_cleanup_*.log`
- Verify configuration
- Ensure proper permissions

## Limitations
- Performance overhead for large projects
- Potential false positives
- Requires careful configuration
