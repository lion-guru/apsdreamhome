# APS Dream Home

> **North India's complete real-estate platform** — properties, plots, MLM network, AI agents, finance & ops, all in one custom-built PHP MVC stack.

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Redis](https://img.shields.io/badge/Redis-7.x-DC382D?logo=redis&logoColor=white)](https://redis.io/)
[![Docker](https://img.shields.io/badge/Docker-Production_Ready-2496ED?logo=docker&logoColor=white)](README.DOCKER.md)
[![License](https://img.shields.io/badge/License-Proprietary-lightgrey)](#license)

---

## What Is APS Dream Home

APS Dream Home is a **production-grade enterprise ERP** built specifically for real-estate and colony-development companies. It combines a customer-facing marketplace, an admin control panel, an MLM associate network, AI-powered agents, finance & HR modules, marketing automation, and a real-time WebSocket notification system.

- **300+ controllers**, **700+ views**, **1,700+ routes**, **281+ database tables**.
- **165 E2E checks** pass on every commit (Playwright master suite).
- **Custom PHP 8.2 MVC framework** — no Laravel/Symfony, full control.
- **Self-learning AI** (pattern + Bayesian + linear regression) — no OpenAI dependency.
- **Multi-language UI** (English + Hindi) with 815 translation keys.

---

## Key Features

### Customer Marketplace
- Property browse with **8 advanced filters** (price, BHK, area, furnishing, etc.).
- **Saved searches** with daily email-alert digest.
- **Favorites** + **comparison tool** (up to 4 properties side-by-side).
- **Property auctions** (English, Sealed, Dutch, Reserve types).
- **Visit booking** with FOR-UPDATE locking (no double-booking).
- **Reviews + testimonials** with admin moderation.
- **AI chatbot** (Hindi + English, 102 trained intent patterns).
- **Live chat** with human-agent handoff.
- **Property submission** with image upload, auto-optimization, EXIF stripping.

### Admin Panel
- Role-based access for 8 user types (super-admin, admin, manager, employee, agent, associate, customer, farmer).
- **Lead kanban** drag-and-drop pipeline with auto-scoring.
- **Booking workflow** with payment tracking, refunds, installments.
- **Finance** — invoices, GST returns, cash-flow projection, bank reconciliation.
- **HRM** — attendance, payroll, leaves, recruitment, performance reviews.
- **MLM management** — network tree, ranks, commission engine, payouts.
- **Marketing** — multi-channel campaigns (email/SMS/WhatsApp/push), drip sequences.
- **Reports** — sales funnel, agent performance, conversion, custom builder.
- **Real-time analytics** dashboard refreshing every 60 seconds.
- **Audit log** — tamper-proof trail of every admin action.
- **System health** monitoring (PHP, DB, disk, memory, cache).

### Platform
- **Redis + file-cache fallback** (transparent, works without Redis installed).
- **WebSocket server** (Ratchet) on port 8080 for real-time notifications.
- **Mobile JWT API** under `/api/mobile/*`.
- **REST API** with API keys, scopes, and rate-limiting.
- **Webhooks** (HMAC-SHA256 signed) for external integrations.
- **Cron automation** (daily reports, alerts, AI retraining).
- **Bulk import/export** CSV (UTF-8 BOM for Excel).
- **2FA / TOTP** (pure PHP, no library) with QR codes + 8 backup codes.
- **Image optimization** — auto-resize to 1920px max, WebP sibling, EXIF stripped.
- **GZIP + browser caching + ETag** (60-80% smaller responses).
- **Lazy loading** on all images across 102 view files.
- **HSTS + CSP + clickjacking protection** baked into `.htaccess`.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Language** | PHP 8.2+ (strict types, typed properties) |
| **Web Server** | Apache 2.4+ (mod_rewrite, mod_deflate, mod_expires) |
| **Database** | MySQL 8.0 (InnoDB, utf8mb4) |
| **Cache** | Redis 7.x with file fallback (`storage/cache/`) |
| **WebSocket** | Ratchet (PHP), port 8080 |
| **JS Front-end** | Vanilla JS, Bootstrap 5.3.3, Font Awesome 6.5.1, Chart.js |
| **Email** | PHP `mail()` or SMTP (configurable) |
| **SMS / WhatsApp** | Twilio, Vapi, MSG91 (pluggable) |
| **Payments** | Razorpay, Paytm, UPI direct |
| **Testing** | Playwright (E2E), bare PHP scripts (unit) |
| **Deployment** | Docker Compose (5 services + 2 sidecars) |
| **CI / Hooks** | Git pre-commit (PHP syntax), pre-push (PHP syntax + tests) |

---

## Quick Start

### Prerequisites

- PHP **8.2+** with `pdo_mysql, mbstring, gd, openssl, curl, zip, intl, sockets`.
- MySQL **8.0+** (or MariaDB 10.6+).
- Apache 2.4+ with `mod_rewrite`.
- (Optional) Redis 7.x.
- (Optional) Composer for the Ratchet WebSocket server.

### Local Install (Windows / XAMPP)

```powershell
# 1. Clone
git clone <repo> C:\xampp\htdocs\apsdreamhome
cd C:\xampp\htdocs\apsdreamhome

# 2. Configure DB
copy .env.example .env
# Edit .env: DB_HOST=127.0.0.1, DB_PORT=3307, DB_DATABASE=apsdreamhome

# 3. Create database
& "C:\xampp\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3307 -u root -e "CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4"

# 4. Import schema + data
& "C:\xampp\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3307 -u root apsdreamhome < database/apsdreamhome_backup_nofk.sql

# 5. (Optional) Composer for WebSocket
composer install

# 6. Start
# - Start Apache + MySQL from XAMPP Control Panel
# - Visit http://localhost/apsdreamhome/
```

### Local Install (Linux / Ubuntu)

```bash
sudo apt install php8.2 php8.2-{mysql,mbstring,gd,curl,zip,intl,sockets} \
                 mysql-server apache2 redis-server
sudo a2enmod rewrite deflate expires headers
git clone <repo> /var/www/html/apsdreamhome
cd /var/www/html/apsdreamhome
cp .env.example .env  # edit DB credentials
mysql -u root -p < database/apsdreamhome_backup_nofk.sql
composer install
sudo systemctl restart apache2
```

### Production with Docker

```bash
cp production.env.example production.env  # edit secrets
./deploy-to-production.sh
make smoke-test
```

Full Docker guide: **[README.DOCKER.md](README.DOCKER.md)**.

---

## Architecture (Text Diagram)

```
                    ┌──────────────────────────────────────┐
                    │           USER (Browser / App)        │
                    └──────────────────┬───────────────────┘
                                       │ HTTPS
        ┌──────────────────────────────┴──────────────────────────────┐
        │                       Nginx (TLS 1.3)                       │
        │   • Rate-limiting (api: 10r/s, login: 5r/min, general: 30)  │
        │   • GZIP + browser caching + HSTS + CSP                     │
        └──────────────────────────────┬──────────────────────────────┘
                                       │
              ┌────────────────────────┼─────────────────────────┐
              │                        │                         │
              ▼                        ▼                         ▼
   ┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
   │  PHP-Apache App  │    │  WebSocket (php) │    │   Static Assets   │
   │  • Front Controller│   │  • Ratchet :8080  │    │  • /uploads      │
   │  • MVC Router    │    │  • Real-time push │    │  • /assets       │
   │  • 300+ Ctrls    │    └──────────────────┘    │  • Long cache    │
   │  • Image Optimizer│                            └──────────────────┘
   └────────┬─────────┘
            │
   ┌────────┴─────────┐
   │   Services Layer  │  Auth · Cache · Notification · AI · Email · Webhook ·
   │   (30+ services)  │  Audit · Bulk Ops · 2FA · Image · Saved Search · …
   └────────┬─────────┘
            │
            ▼
   ┌──────────────────┐         ┌──────────────────┐
   │  MySQL 8 (InnoDB)│ ◄────► │ Redis 7 (Cache)  │  ←─ falls back to
   │  281+ tables     │         │  Pub-sub events  │     file cache
   └──────────────────┘         └──────────────────┘
```

### Front-Controller Flow

1. Apache rewrites all requests to `public/index.php` (except static files).
2. `index.php` boots the **Autoloader** (PSR-4 in `app/`, plus legacy class map).
3. Starts **Session**, connects **Database**, loads **Router**.
4. **Router** matches the URL against `routes/web.php` then `routes/api.php`.
5. The matched **Controller@method** is instantiated and called.
6. Controller uses **Services** for business logic, **Models** for data, **render()** for views.
7. **Layout** (`base.php` / `admin.php` / `customer.php`) wraps the content.
8. Response sent → Nginx → User.

---

## Documentation

| Doc | Audience | Length |
|-----|----------|--------|
| **[User Guide](docs/USER_GUIDE.md)** | End customers | 2,000+ words |
| **[Admin Manual](docs/ADMIN_MANUAL.md)** | Admins / managers / operations | 3,000+ words |
| **[Developer Guide](docs/DEVELOPER_GUIDE.md)** | Engineers / new contributors | 1,500+ words |
| **[API Reference](docs/API.md)** | API consumers / integrators | Full route list + protocols |
| **[Docker Guide](README.DOCKER.md)** | DevOps / SREs | Production deployment |
| **[AGENTS.md](AGENTS.md)** | Past contributors / changelog | Session-by-session history |

---

## Project Stats (as of June 2026)

| Metric | Value |
|--------|-------|
| Database tables | **281+** |
| Routes | **1,700+** (web.php + api.php) |
| Controllers | **300+** |
| Models | **146+** |
| View files | **700+** |
| Services | **30+** |
| Translation keys | **815** (en + hi parity) |
| E2E tests | **164/165 pass** (1 expected GodMode 403) |
| Languages | English, Hindi |
| Performance | **84.7% smaller** properties page after GZIP (142KB → 21.6KB) |
| Image optimization | **79% smaller** JPEG, **91% smaller** WebP |

---

## Common Commands

```powershell
# Start XAMPP Apache + MySQL (Windows GUI)

# Start WebSocket server (background)
Start-Process -FilePath "C:\xampp\php\php.exe" `
              -ArgumentList "websocket_server.php" `
              -WorkingDirectory "C:\xampp\htdocs\apsdreamhome" -WindowStyle Hidden

# Run E2E master test (164 checks)
node testing/visual_tests/E2E_MASTER_TEST.mjs

# PHP syntax check on all modified files
git diff --name-only --diff-filter=AM HEAD | findstr ".php$" | ForEach-Object { php -l $_ }

# Apply a new migration
php scripts/<your-migration-script>.php

# Flush all caches
curl -X POST http://localhost/apsdreamhome/admin/cache/flush

# Test the cron alert system
php scripts/daily_alerts_cron.php
```

---

## Contributing

1. **Fork & branch**: `git checkout -b feature/your-feature`.
2. **Read `AGENTS.md`** for project conventions and recent decisions.
3. **Follow the patterns** in **[docs/DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md)**:
   - `protected $db`, never `private`.
   - Always call `parent::__construct()`.
   - Auth in the controller, not the view.
   - CSRF token on every POST.
4. **PHP syntax must pass** (`php -l`) on every file.
5. **E2E tests must pass** (164/165) before opening a PR.
6. **Write a short, imperative commit message**: "Feature: ...", "Fix: ...", "Perf: ...".

### Code Style

- PSR-12 PHP coding standard.
- 4-space indent (no tabs).
- Use prepared statements; never concatenate SQL.
- Always `htmlspecialchars()` user input in views.
- Lazy-load images: `<img src="..." loading="lazy">`.
- No emojis in committed code unless explicitly asked.

---

## Support

- **Bug reports** → Open an issue on the internal tracker.
- **Customer support** → support@apsdreamhome.com / +91 7007444842.
- **Security disclosures** → security@apsdreamhome.com (PGP key on request).

---

## License

Proprietary. © APS Group, 2025-2026. All rights reserved.
Unauthorized copying, modification, or distribution is strictly prohibited.

---

**Built with care for the Indian real-estate industry.**
**Last Updated:** June 5, 2026
