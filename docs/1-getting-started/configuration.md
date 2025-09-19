# Configuration Guide

## Environment Configuration

The application's configuration is stored in the `.env` file. Here are the key settings:

### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aps_dream_home
DB_USERNAME=root
DB_PASSWORD=
```

### Application Settings
```env
APP_NAME="APS Dream Home"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Feature Flags

Enable/disable features using these flags in your `.env` file:

```env
FEATURE_MAINTENANCE_MODE=false
FEATURE_REGISTRATION=true
FEATURE_EMAIL_VERIFICATION=true
FEATURE_TWO_FACTOR_AUTH=false
```

## Security Settings

### Session Configuration
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
```

### Caching
```env
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

## Third-Party Integrations

### Google Maps API
```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

### Payment Gateway
```env
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

## Environment Specific Configuration

### Local Development
```env
APP_DEBUG=true
APP_ENV=local
```

### Production
```env
APP_DEBUG=false
APP_ENV=production
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## Configuration Caching

After making changes to configuration files, you may need to clear the configuration cache:

```bash
php artisan config:clear
php artisan config:cache
```
