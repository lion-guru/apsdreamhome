# APS Dream Home - Project Setup Guide

## 🚀 Quick Setup

### 1. Database Setup
```bash
# Run database setup
php setup-database.php
```

### 2. Configuration
```bash
# Copy environment file
cp .env.example .env

# Edit database settings
DB_HOST=localhost
DB_DATABASE=apsdreamhome
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Start Application
```bash
# Start XAMPP
# Open browser: http://localhost/apsdreamhome
```

## 📁 Project Structure

```
apsdreamhome/
├── app/                    # Core application
│   ├── Models/            # Database models
│   ├── Services/          # Business logic
│   ├── Controllers/       # HTTP controllers
│   └── Core/             # Core classes
├── public/                # Public assets
├── config/               # Configuration files
├── routes/               # Route definitions
├── storage/              # File storage
├── vendor/               # Dependencies
└── views/                # View templates
```

## 🔧 Essential Scripts

### Database Management
- `setup-database.php` - Create database tables
- `backup-database.php` - Backup database

### Default Login
- **Email:** admin@apsdreamhome.com
- **Password:** admin123

## 📋 Features

### ✅ Working Features
- User authentication
- Password reset
- Profile management
- File uploads
- Email services
- Property management
- Lead management

### 🛠️ Services
- **UserService** - User operations
- **EmailService** - Email sending
- **GeminiService** - AI integration
- **ConfigurationManager** - Settings

## 🚨 Important Notes

### Database Recovery
If database gets corrupted:
1. Run `setup-database.php` to recreate
2. Use `backup-database.php` to restore

### File Locations
- **Uploads:** `public/uploads/`
- **Logs:** `storage/logs/`
- **Cache:** `storage/cache/`

### Security
- Change default admin password
- Update environment variables
- Set proper file permissions

## 📞 Support

For issues:
1. Check logs in `storage/logs/`
2. Verify database connection
3. Check file permissions

---
**Project Status:** Production Ready ✅
