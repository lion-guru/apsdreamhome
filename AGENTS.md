# APS Dream Home - AI Agent Guidelines

## Project Overview
- **Name:** APS Dream Home
- **Type:** Real Estate CRM (Laravel PHP + Vanilla JS)
- **PHP Version:** 8.2+
- **Database:** MySQL (apsdreamhome)
- **Build Tool:** Vite

## Code Style

### PHP (Laravel)
- PSR-4 autoloading
- Controller-Service-Repository pattern
- Use Laravel's Eloquent ORM
- Form Request validation
- API Resource classes for JSON responses

### JavaScript
- Vanilla JS (no framework)
- ES6+ syntax
- Vite for bundling
- Asset files in `assets/js/` and `assets/css/`

### CSS
- Tailwind CSS for utility classes
- SCSS for custom styles
- Live Sass compiler for development

## File Structure
```
app/
├── Http/
│   └── Controllers/
│       ├── Admin/
│       ├── Front/
│       ├── Api/
│       └── MLM/
├── Models/
├── Services/
└── Repositories/

resources/
├── views/
│   ├── admin/
│   ├── front/
│   └── layouts/
└── assets/
    ├── js/
    └── css/

config/
database/
routes/
storage/
```

## Database Conventions
- Table names: snake_case, plural (e.g., `property_listings`)
- Primary key: `id` (auto-increment)
- Foreign keys: `*_id` suffix
- Timestamps: `created_at`, `updated_at`
- Soft deletes: `deleted_at`

## Important Paths
- Controllers: `app/Http/Controllers/`
- Views: `resources/views/`
- Routes: `routes/web.php`, `routes/api.php`
- Config: `config/`
- Assets: `assets/`, `public/`
- Storage: `storage/app/`

## Common Commands
```bash
# Laravel
php artisan serve
php artisan migrate
php artisan route:list
php artisan make:controller

# Vite
npm run dev
npm run build

# Database
mysql -u root -p apsdreamhome
```

## AI Features
- Property valuation engine
- Market analysis
- Price recommendations
- Location-based suggestions

## Conventions
1. Always check existing code before writing new
2. Use existing naming patterns
3. Keep controllers thin, services thick
4. Use dependency injection
5. Write meaningful comments for complex logic
6. Test changes locally before committing

## Ignore Patterns
- `vendor/` - Composer dependencies
- `node_modules/` - NPM packages
- `.env` - Environment secrets
- `storage/logs/*` - Log files
- `*.sql` - Raw SQL files
- `storage/backup/*` - Backup files
