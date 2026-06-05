# APS Dream Home – Developer Guide

This guide is for **engineers contributing to the APS Dream Home codebase**. It covers local setup, architectural conventions, common dev workflows, testing, and deployment.

> **Codebase facts:** Custom PHP 8.2 MVC framework (NOT Laravel), MySQL 8.0, Redis (optional, with file fallback), 281+ database tables, 300+ controllers, 700+ view files, 1700+ routes.

---

## Table of Contents

1. [Local Setup](#local-setup)
2. [Project Structure](#project-structure)
3. [The MVC Pattern Here](#the-mvc-pattern-here)
4. [Adding a New Route](#adding-a-new-route)
5. [Creating a Controller](#creating-a-controller)
6. [Creating a View](#creating-a-view)
7. [Database Migrations](#database-migrations)
8. [The Service Layer](#the-service-layer)
9. [Caching (Redis + File Fallback)](#caching)
10. [Authentication](#authentication)
11. [CSRF Protection](#csrf-protection)
12. [Security Best Practices](#security-best-practices)
13. [Testing](#testing)
14. [Image Optimization](#image-optimization)
15. [WebSocket Server](#websocket-server)
16. [Cron Jobs](#cron-jobs)
17. [Logging](#logging)
18. [Deployment (Docker)](#deployment-docker)
19. [Git Workflow](#git-workflow)
20. [Troubleshooting](#troubleshooting)

---

## Local Setup

### Prerequisites

| Tool      | Version    | Notes                              |
|-----------|------------|------------------------------------|
| PHP       | **8.2+**   | With `pdo_mysql, mbstring, gd, openssl, curl, zip, intl, sockets` |
| MySQL     | **8.0+**   | (Or MariaDB 10.6+)                 |
| Apache    | 2.4+       | With `mod_rewrite, mod_deflate, mod_expires, mod_headers` |
| Composer  | 2.x        | For Ratchet (WebSocket) only       |
| Node.js   | 18 LTS     | For Playwright E2E tests           |
| Redis     | 7.x        | **Optional** – file fallback works |

### Quick Start with XAMPP (Windows)

1. **Install XAMPP** with PHP 8.2 (https://www.apachefriends.org).
2. **Start Apache + MySQL** (default port 3307 for MySQL in this project).
3. **Clone the repo** to `C:\xampp\htdocs\apsdreamhome`.
4. **Import the database**:
   ```powershell
   & "C:\xampp\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3307 -u root -e "CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4"
   & "C:\xampp\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3307 -u root apsdreamhome < database/apsdreamhome_backup_nofk.sql
   ```
5. **Configure** `.env` (copy from `.env.example` if needed):
   ```
   DB_HOST=127.0.0.1
   DB_PORT=3307
   DB_DATABASE=apsdreamhome
   DB_USERNAME=root
   DB_PASSWORD=
   ```
6. **Install Composer dependencies** (for Ratchet WebSocket):
   ```powershell
   composer install
   ```
7. **Visit** `http://localhost/apsdreamhome/`.

### Verifying the Install

- Homepage loads → ✅
- `/admin/login` returns 200 → ✅
- DB connection works:
   ```powershell
   php -r "require 'public/index.php';" # should not show DB errors
   ```

---

## Project Structure

```
apsdreamhome/
├── app/
│   ├── Core/                  → Framework classes (Database, Router, Cache, Autoloader)
│   ├── Http/
│   │   ├── Controllers/        → All controllers (300+)
│   │   │   ├── Admin/            → Admin-panel controllers
│   │   │   ├── Auth/             → Login/register controllers
│   │   │   ├── Front/            → Public-facing
│   │   │   ├── Api/              → REST endpoints (/api/*)
│   │   │   └── Mobile/           → Mobile JWT API (/api/mobile/*)
│   │   └── Middleware/         → Auth, CSRF, RateLimit, etc.
│   ├── Models/                → Eloquent-style models
│   ├── Services/              → Business logic (Cache, Notification, Auth, etc.)
│   ├── Helpers/               → Utility functions
│   └── views/                 → PHP templates (.php files only, NO Blade)
├── config/                    → Configuration files (app, database, cache, mail)
├── database/                  → Schema + migrations + seeds
├── docs/                      → This documentation
├── lang/                      → Translation files (en.php, hi.php)
├── logs/                      → Application logs (php_error.log etc.)
├── public/                    → Web root (index.php, assets)
├── routes/                    → Route definitions (web.php, api.php)
├── scripts/                   → CLI scripts (seeds, migrations, cron)
├── storage/                   → Uploads, sessions, cache files
├── testing/                   → E2E tests, unit tests
├── vendor/                    → Composer dependencies
├── .env                       → Environment variables
├── .htaccess                  → Apache rewrite rules
├── composer.json              → PHP dependencies (Ratchet, JWT)
├── Dockerfile                 → Production Docker image
├── docker-compose.yml         → Local + prod stack
└── AGENTS.md                  → Project history + working notes
```

### Front-Controller Pattern

All requests hit `public/index.php` via Apache rewrite. That file:

1. Loads the **Autoloader** (PSR-4 in `app/`, plus a class map for legacy globals).
2. Bootstraps the **Database**, **Session**, **Router**.
3. Calls **Router::dispatch()** which matches against `routes/web.php` and `routes/api.php`.

---

## The MVC Pattern Here

### Controllers

All controllers extend either `BaseController` or a child like `AdminController`.

```php
namespace App\Http\Controllers;

class BaseController {
    protected $db;         // App\Core\Database
    protected $session;    // App\Core\Session
    protected $cache;      // App\Services\CacheService

    public function render($view, $data = [], $layout = 'base') { ... }
    public function json($data, $status = 200) { ... }
    public function redirect($url, $flash = []) { ... }
    public function requireLogin() { ... }     // Customer auth
    public function requireAdmin() { ... }     // Admin auth
    public function isAjax(): bool { ... }
}
```

### Models

Models are thin wrappers around `App\Core\Database` queries. Many controllers query the DB directly via `$this->db->...` – the model layer is OPTIONAL.

### Views

PHP templates in `app/views/`. **No template engine** – just `<?php echo $variable; ?>` inline.

Two render styles:
- **`$this->render('pages/foo', $data)`** – modern, uses layout.
- **`require __DIR__ . '/file.php'`** – legacy, view manages its own HTML.

**Always prefer `$this->render()` for new code.**

---

## Adding a New Route

Edit `routes/web.php` (or `routes/api.php` for API endpoints).

```php
$router->get('/my-new-page', 'Front\PageController@myMethod');
$router->post('/my-form-submit', 'Front\FormController@submit');
$router->get('/admin/widgets/{id}', 'Admin\WidgetController@show');

$router->any('/either-get-or-post', 'Front\HandlerController@handle');
```

**Parameters** are accessed via `$request['id']` or via method signature in the controller method.

### Best Practices

- **Group related routes** by section in the file.
- **Match controller method names exactly** – router resolves them with autoloader.
- **Avoid closures** – they bypass the MVC layout system. Always use `Controller@method`.

---

## Creating a Controller

Example: a new "Widgets" admin controller.

### 1. Create the file

`app/Http/Controllers/Admin/WidgetController.php`:

```php
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

class WidgetController extends AdminController {

    public function index() {
        $this->requireAdmin();   // CRITICAL — controller auth, not view auth

        $widgets = $this->db->fetchAll("SELECT * FROM widgets ORDER BY id DESC");

        return $this->render('admin/widgets/index', [
            'widgets'    => $widgets,
            'page_title' => 'Widget Management',
        ]);
    }

    public function store() {
        $this->requireAdmin();
        $this->validateCsrf();   // Inherited from BaseController

        $name = trim($_POST['name'] ?? '');
        if (strlen($name) < 3) {
            return $this->redirect('/admin/widgets', ['error' => 'Name too short']);
        }

        $this->db->insert('widgets', ['name' => $name, 'created_at' => date('Y-m-d H:i:s')]);
        return $this->redirect('/admin/widgets', ['success' => 'Widget created!']);
    }
}
```

### 2. Add Routes

In `routes/web.php`:

```php
$router->get('/admin/widgets', 'Admin\WidgetController@index');
$router->post('/admin/widgets/store', 'Admin\WidgetController@store');
```

### 3. Create the View

`app/views/admin/widgets/index.php` — see next section.

### Critical Rules (Lessons from 300+ existing controllers!)

- **Always call `parent::__construct()`** if you override `__construct()` — otherwise `$this->db`, session, CSRF are uninitialized.
- **`protected $db`, NOT `private $db`** — `private` causes "access level violation" in PHP 8.x when extending.
- **Don't shadow parent methods** — if `AdminController` has `public function getRecentActivities()`, don't add a `private` one with the same name.
- **`$request = null` for method parameters** — controllers should accept `($request = null)` so the router can call them with no args.
- **Never `session_start()` in views** — let the framework handle it. Auth goes in the controller via `requireAdmin()`.
- **Never `header('Location: ...')` in views** — use `$this->redirect()` in the controller.

---

## Creating a View

`app/views/admin/widgets/index.php`:

```php
<?php
$page_title = $page_title ?? 'Widgets';
?>
<div class="container-fluid">
    <h1><?= htmlspecialchars($page_title) ?></h1>

    <table class="table">
        <thead><tr><th>ID</th><th>Name</th></tr></thead>
        <tbody>
            <?php foreach ($widgets as $w): ?>
                <tr>
                    <td><?= (int)$w['id'] ?></td>
                    <td><?= htmlspecialchars($w['name']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

### Key Conventions

- **Use `htmlspecialchars()`** for any user-supplied content (XSS prevention).
- **Cast IDs to `(int)`** before echoing.
- **Use `(short) <?= ?>`** for output, `<?php ?>` for logic.
- **Use `?? 'default'`** for fallbacks (PHP null-coalesce).
- **Lazy load images**: `<img src="..." loading="lazy">`.
- **Never include `<html>` or `<body>`** if your controller uses `$this->render()` – the layout adds them.

---

## Database Migrations

There is **no migration framework** (unlike Laravel). Instead, write **PHP scripts** in `scripts/` and track them in a `_migrations` table:

```php
<?php
// scripts/add_widgets_table.php
require __DIR__ . '/../public/index.php';
use App\Core\Database\Database;

$db = Database::getInstance();
$db->execute("
    CREATE TABLE IF NOT EXISTS widgets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        INDEX idx_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "Widgets table created.\n";
```

Run with `php scripts/add_widgets_table.php`. Use `php scripts/track_migration.php add_widgets_table` to record it.

### Best Practices

- Always **`CREATE TABLE IF NOT EXISTS`** to make scripts idempotent.
- Always use **InnoDB** + **utf8mb4**.
- Add **indexes** on columns used in WHERE / JOIN / ORDER BY.
- For **schema changes**, use `ALTER TABLE` with try/catch:
   ```php
   try {
       $db->execute("ALTER TABLE widgets ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
   } catch (\Exception $e) {
       // Column already exists, ignore
   }
   ```

---

## The Service Layer

Services are in `app/Services/` and encapsulate **business logic**. They are usually thin wrappers around DB queries.

Common services:

| Service                    | Purpose                                |
|----------------------------|----------------------------------------|
| `CacheService`             | Redis + file caching                   |
| `NotificationService`      | Send email/SMS/WhatsApp/push           |
| `SocialLoginService`       | OAuth (Google, Facebook, LinkedIn)     |
| `AuditService`             | Audit log writes                       |
| `WebhookService`           | External integrations                  |
| `SavedSearchService`       | Saved-search + email alerts            |
| `AI/AIManager`             | Routes intents to AI agents            |
| `Payroll/PayrollService`   | HR salary calculations                 |

### Service Pattern

```php
namespace App\Services;

class WidgetService {
    private $db;
    public function __construct() {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function findActive(): array {
        return $this->db->fetchAll("SELECT * FROM widgets WHERE status='active'");
    }

    public function getStats(): array {
        return [
            'total'  => $this->db->fetchOne("SELECT COUNT(*) c FROM widgets")['c'],
            'active' => $this->db->fetchOne("SELECT COUNT(*) c FROM widgets WHERE status='active'")['c'],
        ];
    }
}
```

Inject in a controller:

```php
$service = new \App\Services\WidgetService();
$widgets = $service->findActive();
```

---

## Caching

The `CacheService` provides a unified facade over **Redis** (when available) and a **file-cache fallback**.

### Reading / Writing

```php
use App\Services\CacheService;

$cache = CacheService::getInstance();

// Get-or-set pattern (recommended)
$data = $cache->cache('my_cache_key', 600, function() {
    return expensive_query();
});

// Manual
$cache->set('foo', $value, 300);
$val = $cache->get('foo');
$cache->invalidate('foo');
$cache->invalidatePattern('user_*');
```

### Pre-Built Helpers

- `CacheService::getAdminMenu()` – cached for 1h
- `CacheService::getHeaderProjects()` – cached for 5min
- `CacheService::getUnreadCount($userId)` – cached for 30s
- `CacheService::getAdminDashboardStats()` – cached for 2min
- `CacheService::getPropertyFilters()` – cached for 1h

### Invalidation Hooks

When data changes, **invalidate** related caches:

```php
$cache->invalidateAdminDashboard();
$cache->invalidatePropertyFilters();
$cache->invalidateUnreadCount($userId);
```

### Redis vs File

If Redis isn't installed, **everything still works** – it falls back to file cache at `storage/cache/`. To install Redis on Windows: use WSL, then `sudo apt install redis-server php-redis`.

---

## Authentication

Two parallel auth systems:

### Customer Auth (Sessions)

- `$_SESSION['user_id']`, `$_SESSION['user_role']`.
- Used for customers, associates, agents, employees, farmers.
- Set by `CustomerAuthController::authenticate()`.
- Checked by `BaseController::requireLogin()`.

### Admin Auth (Sessions)

- `$_SESSION['admin_id']`, `$_SESSION['admin_role']`.
- Used for admin panel access.
- Set by `AdminAuthController::authenticate()`.
- Checked by `BaseController::requireAdmin()`.

### API / Mobile Auth (JWT)

- For `/api/mobile/*` routes, use JWT tokens.
- Generated by `MobileAuthController`, verified by `JwtMiddleware`.

### How to Protect a Route

```php
public function myProtectedMethod() {
    $this->requireAdmin();    // 302 to /admin/login if not logged in

    // your code...
}
```

---

## CSRF Protection

All **POST forms** must include a CSRF token. The framework auto-generates one in `$_SESSION['csrf_token']`.

### In a Form

```html
<form method="POST" action="/admin/widgets/store">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <input type="text" name="name">
    <button type="submit">Save</button>
</form>
```

### In a Controller

```php
public function store() {
    $this->validateCsrf();   // Throws 403 if token mismatch
    // ...
}
```

### In an AJAX Call

Read the token from the `<meta name="csrf-token">` tag (auto-included in layouts):

```js
const token = document.querySelector('meta[name="csrf-token"]').content;
fetch('/api/foo', {
    method: 'POST',
    headers: { 'X-CSRF-Token': token, 'Content-Type': 'application/json' },
    body: JSON.stringify({ ... }),
});
```

---

## Security Best Practices

- **Never trust `$_POST` / `$_GET`** – always validate and sanitize.
- **Use prepared statements** – `$this->db->execute("SELECT * FROM users WHERE id=?", [$id])`. Never concatenate SQL.
- **Always `htmlspecialchars()`** before echoing user data.
- **Cast IDs to `(int)`** – `intval($_GET['id'])`.
- **CSRF token** on every POST form.
- **Rate-limit** sensitive endpoints (login, password-reset) – use `RateLimitMiddleware`.
- **Hash passwords** with `password_hash($pwd, PASSWORD_DEFAULT)` (bcrypt).
- **Verify passwords** with `password_verify($pwd, $hash)`.
- **HTTPS only** – production has HSTS enabled.
- **Strip EXIF** from uploaded images – `ImageOptimizer` does this automatically.
- **CSP headers** set in `.htaccess` – don't add `unsafe-eval` unless absolutely needed.

---

## Testing

### E2E Testing (Playwright)

The master suite is at `testing/visual_tests/E2E_MASTER_TEST.mjs`. Run it with:

```powershell
node testing/visual_tests/E2E_MASTER_TEST.mjs
```

Expected: **164/165 pass** (1 expected GodMode 403). Failing tests indicate regressions.

### Adding a New E2E Test

Edit `E2E_MASTER_TEST.mjs` and add a check to the array:

```js
{ url: '/admin/widgets', expectStatus: 200, note: 'Widgets page' },
```

Then re-run.

### Unit Tests (PHP)

Stand-alone PHP scripts under `testing/` (e.g., `testing/test_translations.php`). Each prints `PASSED: N, FAILED: M` and exits with status 0/1.

Run with:

```powershell
php testing/test_translations.php
```

### PHP Syntax Check

Before committing:

```powershell
php -l app/Http/Controllers/MyNewController.php
php -l app/views/admin/my_view.php
```

The `pre-commit` git hook does this automatically for all staged PHP files.

---

## Image Optimization

The `ImageOptimizer` class (`app/Core/ImageOptimizer.php`) auto-resizes property photos on upload.

Behavior:

- Resizes to **max 1920px wide** (preserves aspect ratio).
- Strips **EXIF metadata** (privacy + smaller files).
- Saves a **WebP sibling** when GD `imagewebp` is available (~91% smaller).
- Quality: 85 (JPEG default).
- Never throws – returns stats array.

Usage in a controller:

```php
use App\Core\ImageOptimizer;

// Static helper (one-line)
ImageOptimizer::optimizeStatic($uploadedFilePath);

// Or instance for tuning
$opt = new ImageOptimizer();
$opt->setMaxWidth(1280)->setQuality(80)->setEmitWebp(true);
$result = $opt->optimize($uploadedFilePath);
```

---

## WebSocket Server

For real-time notifications, run the WebSocket server:

```powershell
# Foreground (dev)
php websocket_server.php

# Background (Windows)
Start-Process -FilePath "C:\xampp\php\php.exe" -ArgumentList "websocket_server.php" -WindowStyle Hidden
```

The server runs on **port 8080**. The frontend `assets/js/notification-system.js` auto-connects with exponential-backoff reconnection.

Protocol: see [API.md → WebSocket Protocol](API.md#websocket-protocol).

---

## Cron Jobs

The app expects three cron endpoints to be hit on a schedule:

| Endpoint | Frequency | Purpose |
|----------|-----------|---------|
| `/admin/cron/daily?key=<CRON_SECRET>` | Daily 5 AM | Reports, cleanups, lead scoring |
| `/admin/cron/hourly?key=<CRON_SECRET>` | Hourly | Scheduled notifications |
| `/user/saved-searches/cron-alerts?key=<CRON_SECRET>` | Daily 9 AM | Saved-search email alerts |

### Windows Task Scheduler

```powershell
$action = New-ScheduledTaskAction -Execute 'C:\xampp\php\php.exe' `
    -Argument 'C:\xampp\htdocs\apsdreamhome\scripts\daily_alerts_cron.php'
$trigger = New-ScheduledTaskTrigger -Daily -At '09:00'
Register-ScheduledTask -TaskName 'APS_Daily_Alerts' -Action $action -Trigger $trigger
```

### Linux Crontab

```cron
0 5 * * *  curl -sf "https://apsdreamhome.com/admin/cron/daily?key=$CRON_SECRET" > /dev/null
0 * * * *  curl -sf "https://apsdreamhome.com/admin/cron/hourly?key=$CRON_SECRET" > /dev/null
0 9 * * *  curl -sf "https://apsdreamhome.com/user/saved-searches/cron-alerts?key=$CRON_SECRET" > /dev/null
```

---

## Logging

- **`logs/php_error.log`** – PHP errors, warnings, notices.
- **`logs/alerts_cron.log`** – saved-search cron output.
- **`storage/logs/audit.log`** – audit events.
- **`storage/logs/webhook.log`** – webhook delivery attempts.

Use `error_log()` for app-level logging (gated behind `DEBUG_MODE` for noisy logs).

```php
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log("WidgetController::index() entered");
}
```

---

## Deployment (Docker)

A production-ready Docker stack is included:

```bash
# Build images
make build

# Start the stack (5 services: app, websocket, db, redis, nginx)
make up

# Verify
make smoke-test
```

See `README.DOCKER.md` for the **full deployment guide** including SSL setup, zero-downtime deploys, and backup configuration.

---

## Git Workflow

```powershell
# Always start with a baseline commit
git commit --allow-empty -m "Pre-feature: starting widget module"

# Work, then check status
git status
git diff

# Stage and commit
git add app/Http/Controllers/Admin/WidgetController.php app/views/admin/widgets/
git commit -m "Feature: Widget CRUD with admin permissions"

# Push (the pre-push hook validates PHP syntax)
git push
```

### Conventions

- **Pre-* commits** mark the start of a major effort.
- **Phase-N-complete tags** mark milestone completions.
- **Short, imperative commit messages**: "Fix: ...", "Feature: ...", "Perf: ...", "Refactor: ...".

---

## Troubleshooting

### "Class not found" autoloader error

- Check the namespace and filename match (PSR-4).
- Check `app/Core/Autoloader.php` class map for legacy aliases.

### "Access level must be protected" (PHP fatal)

- A child controller declared `private $db` but the parent has `protected $db`.
- Fix: change to `protected $db`.

### `/admin/foo` returns 500

1. Check `logs/php_error.log` for the actual error.
2. Check that the controller method exists.
3. Check that `parent::__construct()` is called if you override the constructor.

### "Headers already sent" warning

- A view echoed something before the controller called `redirect()` or `json()`.
- Check the view doesn't have stray whitespace before `<?php`.

### Redis "Class Redis not found"

- The `phpredis` extension isn't installed – this is OK; the file cache fallback runs.
- To install: WSL → `sudo apt install php-redis`.

### Sessions not persisting

- Check `session.save_path` is writable.
- Check `storage/sessions/` exists.
- Check the browser sends cookies (try in incognito with no extensions).

---

**Last Updated:** June 5, 2026
**Document Version:** 1.0
**See also:** [User Guide](USER_GUIDE.md) · [Admin Manual](ADMIN_MANUAL.md) · [API Reference](API.md)
