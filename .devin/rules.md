# APS Dream Home - Devin Rules & Configuration

## Project Permissions

**Scope:** FULL PROJECT ACCESS  
**Path:** `c:\xampp\htdocs\apsdreamhome`  
**Permission Level:** UNRESTRICTED

### Allowed Operations
- ✅ **Read**: All files and directories
- ✅ **Write**: Create, modify, delete any files
- ✅ **Execute**: Run shell commands, PHP scripts, Node.js tools
- ✅ **Database**: All MySQL operations on port 3307
- ✅ **Testing**: Run Playwright tests, create test files
- ✅ **Configuration**: Modify configuration files, environment variables
- ✅ **Dependencies**: Install/remove packages via npm, composer

### Auto-Approved Actions
The following actions are auto-approved without requiring user confirmation:

#### File Operations
- Create, edit, delete any project files
- Create new directories and reorganize structure
- Read and analyze any file contents
- Modify code in any language (PHP, JS, CSS, HTML, SQL)

#### Database Operations
- Run any MySQL queries on apsdreamhome database
- Create/modify database tables
- Insert/update/delete any data
- Run migration scripts
- Seed test data

#### Development Tools
- Run XAMPP commands (start/stop services)
- Execute PHP scripts in tools/ directory
- Run Node.js tools and scripts
- Execute Playwright browser automation
- Run git commands (commit, push, pull, etc.)

#### Testing
- Create and run test files
- Execute automated test suites
- Generate test reports and screenshots
- Modify test configurations

#### Project Configuration
- Modify PHP configuration files
- Update .env files and environment variables
- Change server settings
- Modify routing configuration
- Update authentication and RBAC settings

### Safety Mechanisms

#### Destructive Operations Protection
- **Git force-push**: Requires confirmation
- **Database truncation**: Requires confirmation
- **File deletion**: Allowed for project files only
- **Service restart**: Auto-approved for local development

#### Code Quality Checks
- **Syntax validation**: PHP, JS syntax checked automatically
- **Lint errors**: Auto-fixed when possible
- **Database schema**: Validated before migrations
- **Route conflicts**: Checked before route additions

### Environment Configuration

#### Database Connection
```php
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';
```

#### Server Configuration
```php
define('BASE_URL', 'http://localhost/apsdreamhome');
define('BASE_PATH', dirname(__DIR__));
```

#### PHP Settings
- Memory Limit: 256M
- Max Execution Time: 300s
- Error Reporting: E_ALL (development mode)
- Display Errors: On (development mode)

## Project-Specific Guidelines

### Code Style
- **PHP**: Follow PSR-12 coding standards
- **JavaScript**: ES6+ syntax, 2-space indentation
- **CSS**: Use BEM naming convention where possible
- **HTML**: Semantic markup, accessibility-first

### File Organization
- **Controllers**: `app/Http/Controllers/`
- **Models**: `app/Models/`
- **Views**: `app/Views/{module}/`
- **Routes**: `routes/`
- **Assets**: `assets/`
- **Tests**: `testing/`

### Database Operations
- Always use prepared statements for user input
- Validate data before database operations
- Use transactions for multi-step operations
- Log important database operations

### Authentication & RBAC
- Verify user permissions before sensitive operations
- Log authentication attempts
- Use session management properly
- Implement proper CSRF protection

### Testing Guidelines
- Test core functionality before deployment
- Use proper test data cleanup
- Test edge cases and error conditions
- Maintain test documentation

## Common Workflows

### Adding New Feature
1. Create controller in appropriate directory
2. Create model if database interaction needed
3. Create view files with unified layout
4. Add routes in routes/web.php
5. Add RBAC permissions if admin feature
6. Test functionality
7. Update documentation

### Fixing Bugs
1. Reproduce the issue
2. Identify root cause
3. Implement fix
4. Test the fix
5. Check for side effects
6. Update documentation if needed

### Database Changes
1. Create migration script
2. Test migration on development database
3. Update model if schema changed
4. Update related controllers
5. Test affected functionality
6. Document changes

## Error Handling

### PHP Errors
- Check error logs: `xampp/php/logs/php_error.log`
- Enable detailed error messages in development
- Use try-catch blocks for critical operations
- Validate user input before processing

### Database Errors
- Check MySQL logs: `xampp/mysql/data/mysql_error.log`
- Verify database connection parameters
- Test queries in isolation
- Use proper error handling in database operations

### Testing Errors
- Check test logs in testing/visual_tests/
- Review screenshots for visual issues
- Verify test environment is properly configured
- Check browser compatibility

## Performance Optimization

### Database
- Use indexes for frequently queried columns
- Optimize complex queries
- Use query caching where appropriate
- Limit result sets for large datasets

### Frontend
- Minimize HTTP requests
- Use browser caching
- Optimize images and assets
- Lazy load content where appropriate

### PHP
- Use opcode caching (OPcache)
- Optimize autoloading
- Minimize database queries per request
- Use efficient algorithms

## Security Best Practices

### Input Validation
- Sanitize all user input
- Validate data types and formats
- Use parameterized queries
- Implement CSRF protection

### Output Encoding
- Escape output to prevent XSS
- Use proper content-type headers
- Validate file uploads
- Implement proper session management

### Access Control
- Verify permissions on sensitive operations
- Use role-based access control (RBAC)
- Implement rate limiting where appropriate
- Log security-related events

## Communication Style

### Progress Updates
- Provide clear status updates
- Explain what and why for changes
- Highlight any assumptions made
- Note any trade-offs or alternatives

### Error Reporting
- Describe the error clearly
- Explain the impact
- Suggest potential solutions
- Provide context for troubleshooting

### Documentation
- Update AGENTS.md with project status
- Document new features and changes
- Maintain PROJECT_MAP.md
- Update configuration documentation

## Tool Preferences

### Code Editors
- Primary: VS Code / Windsurf
- Fallback: Any code editor
- Preferred Extensions: PHP IntelliSense, Playwright Test

### Development Tools
- XAMPP for local server
- MySQL Workbench for database
- Git for version control
- Playwright for testing

### Browsers for Testing
- Chrome (primary)
- Firefox (secondary)
- Edge (compatibility testing)

## Automation Preferences

### Auto-Approve
- File operations within project scope
- Database operations on development database
- Test execution and result analysis
- Configuration changes for development

### Require Confirmation
- Destructive database operations (DROP, TRUNCATE)
- Git force operations
- Service restarts in production
- Deployment to live servers

## Continuous Improvement

### Regular Tasks
- Monitor error logs for issues
- Review and update documentation
- Optimize performance bottlenecks
- Security audits and updates

### Learning
- Stay updated on PHP and web development best practices
- Explore new tools and technologies
- Share knowledge with team
- Contribute to Devin skill improvements