# Troubleshooting Guide

## 🔧 Common Issues & Solutions

### Test Execution Issues

#### Database Connection Failed

**Symptoms:**
- "Database connection failed" error
- Tests not running
- Connection timeout

**Common Causes:**
- Incorrect database credentials
- Database server down
- Network connectivity issues

**Solutions:**
1. Check includes/config/constants.php for correct DB settings
1. Verify database server is running
1. Test connection with manual MySQL client
1. Check firewall settings

---

#### PHPUnit Class Not Found

**Symptoms:**
- "Class Tests\TestCase not found"
- Autoloader issues
- Missing dependencies

**Common Causes:**
- PHPUnit not installed
- Incorrect autoloader configuration
- Missing vendor directory

**Solutions:**
1. Install PHPUnit: composer require phpunit/phpunit
1. Run composer install to install dependencies
1. Check composer.json autoloader configuration
1. Use standalone test suites instead

---

#### Permission Denied Errors

**Symptoms:**
- "Permission denied" in logs
- File write errors
- Upload failures

**Common Causes:**
- Incorrect file permissions
- Missing directories
- SELinux restrictions

**Solutions:**
1. Set proper permissions: chmod 755 for directories, 644 for files
1. Create missing upload directories
1. Check SELinux status and configure if needed
1. Verify web server user permissions

---

#### Memory Exhausted Errors

**Symptoms:**
- "Allowed memory size exhausted"
- Tests stopping unexpectedly
- Performance degradation

**Common Causes:**
- Large dataset operations
- Memory leaks in tests
- Insufficient PHP memory limit

**Solutions:**
1. Increase memory_limit in php.ini
1. Optimize test data cleanup
1. Use memory-efficient data structures
1. Break large tests into smaller chunks

---

#### Session Issues

**Symptoms:**
- Authentication failures
- Session not starting
- Login test failures

**Common Causes:**
- Session configuration problems
- Cookie path issues
- Session storage permissions

**Solutions:**
1. Check session.save_path permissions
1. Verify session.cookie_path setting
1. Clear session cookies and restart browser
1. Check session timeout settings

---

### Performance Issues

#### Slow Test Execution

**Symptoms:**
- Tests taking unusually long to complete
- Timeout errors during test execution
- High CPU usage during testing

**Causes:**
- Database query optimization needed
- Large dataset operations
- Inefficient test data cleanup
- Network latency issues

**Solutions:**
1. Optimize database queries with proper indexing
2. Use smaller test datasets where possible
3. Implement efficient test data cleanup
4. Consider running tests on local database
5. Profile slow queries and optimize accordingly

### Configuration Issues

#### Environment Variables

**Common Issues:**
- Missing or incorrect environment variables
- Path configuration problems
- URL generation issues

**Solutions:**
1. Verify all required constants in includes/config/constants.php
2. Check BASE_URL configuration
3. Ensure file paths are correct for your environment
4. Test configuration with simple script first

### Debugging Techniques

#### Enable Debug Mode

```php
// Add to test file for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

#### Log Debug Information

```php
// Add logging to test methods
error_log('Test starting: ' . __METHOD__);
error_log('Test data: ' . print_r(, true));
```

#### Step-by-Step Testing

1. Test database connection first
2. Test basic CRUD operations
3. Test complex queries
4. Test authentication flows
5. Test API endpoints

### Getting Help

#### Log Analysis

- Check Apache/Nginx error logs
- Review PHP error logs
- Examine database query logs
- Monitor system resource usage

#### Common File Locations

- **Error Logs:** /var/log/apache2/error.log or /var/log/nginx/error.log
- **PHP Logs:** /var/log/php_errors.log
- **MySQL Logs:** /var/log/mysql/error.log
- **Application Logs:** Check configured log directory

---

*Last Updated: 2025-11-28 18:46:55*
