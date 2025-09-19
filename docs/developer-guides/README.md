# Developer Guides

Welcome to the APS Dream Home developer documentation. These guides will help you understand, extend, and customize the system.

## Table of Contents

1. [Development Environment Setup](setup.md)
2. [System Architecture](architecture.md)
3. [Database Schema](database-schema.md)
4. [API Development](api-development.md)
5. [Themes and Templates](themes.md)
6. [Module Development](module-development.md)
7. [Testing](testing.md)
8. [Deployment](deployment.md)

## Development Workflow

1. **Setup Development Environment**
   - Install required software
   - Set up local development environment
   - Configure database

2. **Code Standards**
   - Follow PSR-12 coding standards
   - Write unit tests for new features
   - Document your code

3. **Version Control**
   - Create feature branches
   - Write meaningful commit messages
   - Create pull requests for code review

4. **Testing**
   - Run unit tests
   - Test in development environment
   - Verify in staging before production

## API Development

### Endpoint Structure
```
/api/v1/resource[/{id}]
```

### Response Format
```json
{
  "success": true,
  "data": {},
  "message": "Operation completed successfully"
}
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Support

For developer support, contact:
- Email: dev-support@apsdreamhome.com
- Slack: #developers channel
