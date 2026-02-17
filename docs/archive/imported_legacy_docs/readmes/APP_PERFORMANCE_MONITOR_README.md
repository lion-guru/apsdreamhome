# Application Performance and Security Monitor

## Overview
A comprehensive PHP-based tool for monitoring application performance, security, and potential vulnerabilities.

## Features
- Real-time performance metrics
- Security vulnerability checks
- Detailed logging
- HTML report generation

## Performance Monitoring
- Memory usage tracking
- Execution time measurement
- Threshold-based alerts

## Security Checks
- Session hijacking detection
- CSRF protection
- XSS prevention
- SQL injection detection

## Usage

### Command Line
```bash
php app_performance_monitor.php
```

### Web Interface
Navigate to `app_performance_monitor.php` in your browser

## Outputs
- Detailed HTML report
- Performance log files
- Security log files

## Configuration
- Customize monitoring thresholds
- Enable/disable specific security checks
- Adjust logging parameters

## Security Checks Details
- Session Hijacking: Validates user IP consistency
- CSRF Protection: Token-based validation
- XSS Prevention: Input sanitization checks
- SQL Injection: Pattern-based detection

## Dependencies
- PHP 7.4+
- Session support
- Web server with PHP

## Best Practices
- Run periodically during development
- Review generated reports
- Address security and performance issues promptly

## Troubleshooting
- Check `logs/performance_*.log`
- Check `logs/security_*.log`
- Verify PHP configuration
- Ensure proper session management

## Limitations
- Requires active session
- Performance overhead
- Not a substitute for comprehensive security audits
