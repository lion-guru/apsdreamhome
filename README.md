# APS Dream Home

## 🏠 Project Overview

APS Dream Home is a comprehensive real estate and MLM (Multi-Level Marketing) platform built with a custom PHP MVC architecture. The platform provides property management, agent services, customer relationship management, and a sophisticated MLM network system.

## 🏗️ Architecture

### **Custom MVC Framework**
- **Framework**: Pure PHP Custom MVC (NOT Laravel)
- **Database**: Custom PDO wrapper with prepared statements
- **Configuration**: JSON-based configuration system
- **Session**: Custom session management
- **Security**: Enterprise-grade security with Argon2ID hashing
- **Views**: Pure PHP with includes (NO Blade)

### **Directory Structure**
```
apsdreamhome/
├── app/
│   ├── Core/                    # Custom framework core
│   │   ├── Database/           # Custom database layer
│   │   ├── Config.php          # Custom config system
│   │   ├── Controller.php       # Custom base controller
│   │   ├── Session/            # Custom session system
│   │   └── Security/          # Custom security
│   ├── Http/Controllers/       # Web controllers
│   ├── Services/               # Business logic services
│   ├── Models/                # Data models
│   ├── Helpers/               # Helper functions
│   └── views/                 # View files (.php only)
├── config/
│   ├── bootstrap.php           # Custom bootstrap
│   ├── app_config.json        # JSON configuration
│   └── database.php           # Database configuration
├── database/                  # Database migrations and seeds
├── public/                    # Public web assets
├── storage/                   # File storage
├── tests/                     # Test suites
└── vendor/                     # Composer dependencies
```

## 🚀 Features

### **Real Estate Management**
- Property listing and management
- Agent dashboard and performance tracking
- Lead management and conversion tracking
- Customer relationship management
- Commission calculation and tracking

### **MLM System**
- Multi-level network management
- Binary tree structure
- Rank calculation and progression
- Commission distribution (binary, unilevel, matrix)
- Team performance analytics
- Referral system

### **Security Features**
- Modern password hashing (Argon2ID)
- CSRF token validation
- Rate limiting with configurable windows
- Input sanitization and validation
- SQL injection prevention
- XSS protection
- IP blocking and monitoring
- Security event logging

### **Communication**
- WhatsApp template management
- Email notifications
- Team messaging system
- Multi-language support (English, Hindi, Spanish, French, Arabic)

### **Admin Features**
- Comprehensive admin dashboard
- User management and permissions
- System monitoring and health checks
- Performance analytics and reporting
- Security monitoring and alerts

## 🛠️ Technical Stack

### **Backend**
- **Language**: PHP 8+
- **Database**: MySQL with custom PDO wrapper
- **Authentication**: Custom session-based authentication
- **API**: RESTful API with JSON responses
- **Caching**: File-based caching system

### **Frontend**
- **Mobile**: React Native
- **Web**: Pure PHP with JavaScript
- **Styling**: TailwindCSS
- **Icons**: Lucide icons
- **Charts**: Chart.js

### **Development Tools**
- **Package Manager**: Composer
- **Testing**: PHPUnit
- **Code Quality**: PHPStan
- **Documentation**: Inline documentation

## 📊 Project Status

### **Migration Status**
- **✅ Phase 5**: Core infrastructure migration completed
- **✅ Phase 6A**: Critical infrastructure migration completed
- **✅ Phase 6B**: Business logic migration completed
- **✅ Phase 6C**: Utility migration completed
- **✅ Phase 7**: Final cleanup and documentation completed

### **Code Quality**
- **Zero Laravel Dependencies**: Pure Custom MVC implementation
- **Security Standards**: Enterprise-grade security implemented
- **Error Handling**: Comprehensive exception handling
- **Database Security**: Prepared statements everywhere
- **Test Coverage**: Comprehensive test suites

## 🚀 Installation

### **Prerequisites**
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx)

### **Setup Instructions**
1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd apsdreamhome
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Database Setup**
   ```bash
   # Import database schema
   mysql -u root -p database_name < database/schema.sql
   ```

5. **Configure Web Server**
   - Document root: `public/`
   - Enable mod_rewrite (for Apache)
   - Set up virtual host

## 🔧 Configuration

### **Environment Variables**
```env
APP_NAME=APS Dream Home
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=apsdreamhome
DB_USERNAME=root
DB_PASSWORD=your_password
```

### **Application Configuration**
Configuration is managed through `config/app_config.json`:
- Database settings
- Security settings
- Cache configuration
- MLM parameters
- Email settings

## 🧪 Testing

### **Run Tests**
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Feature/

# Run with coverage
./vendor/bin/phpunit --coverage-html
```

### **Test Structure**
```
tests/
├── Feature/          # Feature tests
├── Unit/             # Unit tests
└── Integration/       # Integration tests
```

## 📝 Development

### **Code Standards**
- PSR-4 autoloading
- PSR-12 coding standards
- Comprehensive inline documentation
- Type hints where applicable
- Error and exception handling

### **Git Workflow**
1. Create feature branch
2. Make changes with comprehensive testing
3. Commit with descriptive messages
4. Create pull request
5. Code review and merge

### **Debugging**
- Error logging to `logs/` directory
- Debug mode via `.env` configuration
- Performance monitoring
- Security event tracking

## 🔒 Security

### **Implemented Security Measures**
- **Authentication**: Argon2ID password hashing, session security
- **Input Validation**: Comprehensive sanitization and validation
- **CSRF Protection**: Token-based CSRF protection
- **Rate Limiting**: Configurable rate limiting per IP/user
- **SQL Injection Prevention**: Prepared statements everywhere
- **XSS Protection**: Input filtering and output encoding
- **IP Blocking**: Dynamic IP blocking for suspicious activities
- **Security Monitoring**: Real-time security event logging

### **Security Best Practices**
- Regular security audits
- Dependency updates
- Environment-specific configurations
- Secure password policies
- Multi-factor authentication support

## 📈 Performance

### **Optimization Features**
- Database query optimization
- Caching layer for frequently accessed data
- Lazy loading for large datasets
- Efficient file handling
- Memory management

### **Monitoring**
- Response time tracking
- Database query performance
- Memory usage monitoring
- Error rate tracking

## 🌐 API Documentation

### **Authentication Endpoints**
- `POST /api/auth/login` - User authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `POST /api/auth/refresh` - Token refresh

### **MLM Endpoints**
- `GET /api/mlm/dashboard` - MLM dashboard data
- `GET /api/mlm/network` - Network tree data
- `POST /api/mlm/commission` - Add commission
- `GET /api/mlm/commissions` - Get commission history

### **Property Endpoints**
- `GET /api/properties` - List properties
- `POST /api/properties` - Create property
- `PUT /api/properties/{id}` - Update property
- `DELETE /api/properties/{id}` - Delete property

## 📱 Mobile Applications

### **React Native App**
- Cross-platform mobile application
- Real-time property listings
- Agent dashboard on mobile
- Push notifications
- Offline mode support

### **Features**
- Property search and filtering
- Agent communication
- Document management
- Location-based services

## 🤝 Contributing

### **Guidelines**
1. Follow PSR standards
2. Write comprehensive tests
3. Update documentation
4. Use semantic versioning
5. Follow security best practices

### **Pull Request Process**
1. Fork repository
2. Create feature branch
3. Implement changes with tests
4. Update documentation
5. Submit pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Support

### **Documentation**
- API Documentation: `/docs/api`
- Developer Guide: `/docs/developer`
- Deployment Guide: `/docs/deployment`

### **Contact**
- Technical Support: support@apsdreamhome.com
- Business Inquiries: info@apsdreamhome.com
- Website: https://apsdreamhome.com

---

**Last Updated**: March 16, 2026  
**Version**: 1.0.0  
**Status**: Production Ready ✅