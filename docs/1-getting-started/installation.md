# Installation Guide

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (for dependency management)

## Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/aps-dream-home.git
   cd aps-dream-home
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   - Copy `.env.example` to `.env`
   - Update database credentials and other settings

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Run database migrations**
   ```bash
   php artisan migrate
   ```

6. **Seed the database**
   ```bash
   php artisan db:seed
   ```

7. **Set up storage link**
   ```bash
   php artisan storage:link
   ```

8. **Set proper permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

## Post-Installation

1. Access the admin panel at `/admin`
2. Login with default credentials:
   - Email: admin@example.com
   - Password: password
3. Change the default password immediately

## Troubleshooting

- **500 Error**: Check storage and bootstrap/cache permissions
- **Database Connection Error**: Verify .env database credentials
- **Missing Dependencies**: Run `composer install`
