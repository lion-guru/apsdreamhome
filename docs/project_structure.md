# Project Structure Organization Plan

## Core Directories

### /src
- Core application logic
- Configuration files
- Database connections
- Utility functions

### /public
- Entry point files (index.php)
- Static assets (CSS, JS, images)
- Public-facing files

### /app
- Application-specific code
- Controllers
- Models
- Views/templates

### /auth
- Authentication related files
- Login/registration handlers
- Password reset
- Session management

### /admin
- Administrative interface files
- Dashboard components
- User management
- Settings

### /api
- API endpoints
- API documentation
- API utilities

### /config
- Configuration files
- Environment variables
- Database settings

### /includes
- Reusable components
- Helper functions
- Common utilities

### /uploads
- User uploaded files
- Property images
- Documents

### /tests
- Unit tests
- Integration tests
- Test utilities

### /docs
- Project documentation
- API documentation
- Setup guides

## File Organization Rules

1. Use consistent naming conventions
   - All lowercase for directories
   - Underscore for spaces
   - Descriptive names

2. Separate concerns
   - Keep related functionality together
   - Minimize dependencies between modules

3. Follow MVC pattern where applicable
   - Models in /app/models
   - Views in /app/views
   - Controllers in /app/controllers

4. Asset organization
   - CSS in /public/css
   - JavaScript in /public/js
   - Images in /public/images

5. Configuration management
   - Environment-specific configs in /config
   - Sensitive data in .env file

6. Security considerations
   - Keep sensitive files outside public directory
   - Use .htaccess for access control
   - Implement proper file permissions

## Implementation Steps

1. Create new directory structure
2. Move files to appropriate locations
3. Update file references and includes
4. Test functionality after reorganization
5. Update documentation with new structure
6. Implement version control if not already present