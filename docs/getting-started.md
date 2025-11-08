# Getting Started with APS Dream Home

Welcome to APS Dream Home - Your comprehensive real estate management solution. This guide will help you get started with the system.

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.3 or higher
- Apache/Nginx web server
- Composer (for dependency management)
- Git (for version control)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://your-repository-url/apsdreamhome.git
   cd apsdreamhome
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
   php artisan migrate --seed
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

## First Steps

1. **Login**
   - Default admin credentials:
     - Email: admin@example.com
     - Password: password

2. **Configure System Settings**
   - Navigate to Admin Panel > Settings
   - Update site name, logo, and other preferences

3. **Add Properties**
   - Go to Properties > Add New
   - Fill in property details
   - Upload high-quality images

4. **Set Up User Roles**
   - Go to Users > Roles
   - Define permissions for different user types

## Support

For any questions or issues, please contact our support team at support@apsdreamhome.com
