# Code Quality Analyzer

## Overview
A comprehensive PHP-based tool for analyzing code quality, style, and potential security issues across a project.

## Features
- Recursive file analysis
- Code style checks
- Complexity analysis
- Security vulnerability detection
- Detailed reporting
- Configurable rules

## Analysis Capabilities

### Code Style Checks
- Line length validation
- Naming convention enforcement
- Indentation consistency

### Complexity Analysis
- Cyclomatic complexity measurement
- Nesting depth tracking
- Function and method complexity

### Security Checks
- Input validation detection
- Output escaping verification
- SQL injection risk assessment
- Cross-Site Scripting (XSS) prevention

## Configuration
Customize via `config/code_quality_config.json`:
- File types to analyze
- Ignored directories
- Code style rules
- Complexity thresholds
- Security check toggles

## Usage Modes

### Command Line
```bash
php code_quality_analyzer.php
```

### Web Interface
Navigate to `code_quality_analyzer.php`

## Configuration Options
- Maximum line length
- Naming convention patterns
- Complexity thresholds
- Security check toggles

## Report Types
- JSON detailed report
- HTML human-readable report
- Comprehensive logging

## Dependencies
- PHP 7.4+
- PHP-Parser library (optional for advanced analysis)

## Best Practices
- Regular code quality scans
- Review generated reports
- Address identified issues
- Customize configuration

## Troubleshooting
- Check `logs/code_quality_analysis_*.log`
- Verify PHP-Parser installation
- Ensure proper permissions

## Limitations
- Static code analysis
- Potential false positives
- Requires manual review
- Performance overhead

## Recommended Workflow
1. Configure analysis rules
2. Run code quality scan
3. Review generated report
4. Address identified issues
5. Repeat periodically
