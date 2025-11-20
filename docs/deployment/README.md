# Deployment & Operations Handbook

Authoritative runbook for deploying, operating, and maintaining APS Dream Home across environments. This document consolidates the legacy `DEPLOYMENT_MAINTENANCE_GUIDE.md`, `DEPLOYMENT_OPTIONS.md`, `DEPLOYMENT_READINESS_CHECKLIST.md`, and the previous `docs/deployment/README.md`.

## Quick Navigation

- [Audience & Scope](#audience--scope)
- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Deployment Workflow](#deployment-workflow)
  - [Choose Hosting Strategy](#choose-hosting-strategy)
  - [Prepare Code & Assets](#prepare-code--assets)
  - [Database Migration](#database-migration)
  - [Configuration Updates](#configuration-updates)
  - [Web & SSL Configuration](#web--ssl-configuration)
  - [Go-Live Checklist](#go-live-checklist)
- [Post-Deployment Operations](#post-deployment-operations)
  - [Monitoring & Metrics](#monitoring--metrics)
  - [Maintenance Cadence](#maintenance-cadence)
  - [Backups](#backups)
  - [Growth & Optimization](#growth--optimization)
- [Troubleshooting Quick Reference](#troubleshooting-quick-reference)
- [Support & References](#support--references)

---

## Audience & Scope

| Role | Focus |
| ---- | ----- |
| Project owner / stakeholder | Understand deployment options, SLA expectations, cost implications. |
| DevOps / engineers | Execute deployments, automate backups, maintain uptime. |
| Support staff | Monitor health checks, troubleshoot incidents, coordinate escalation. |

> **Environments supported:** Local (XAMPP), shared hosting, VPS/cloud, and staged production environments.

---

## Pre-Deployment Checklist

### Functional readiness

- [x] Core pages (home, properties, about, contact) render without errors.
- [x] Admin panel accessible with valid accounts.
- [x] Property search, inquiry forms, and authentication flows work end-to-end.
- [x] Database schema and seed data validated (properties, users, site settings).
- [x] Assets build successfully (`npm run build`).

### Operational prerequisites

- [x] Domain purchased and DNS access confirmed.
- [x] Hosting / server provisioned with PHP 8.1+, MySQL 8.x (or MariaDB 10.3+), and SSH/FTP access.
- [x] SSL certificate planned (Let’s Encrypt or equivalent).
- [x] Backup destination chosen (cloud storage, S3, etc.).
- [x] Monitoring/analytics accounts set up (Google Analytics, Search Console, uptime monitoring).

---

## Deployment Workflow

### Choose Hosting Strategy

| Option | Description | Typical Use | Notes |
| ------ | ----------- | ---------- | ----- |
| Shared hosting | cPanel-driven providers (Hostinger, Bluehost, SiteGround). | Cost-effective production for low traffic. | Use file manager/FTP + phpMyAdmin. Ensure PHP 8.1 availability. |
| VPS / cloud | DigitalOcean droplet, AWS Lightsail, etc. | Full control, scaling, staging/prod parity. | Requires server hardening, automated provisioning beneficial. |
| Local / LAN | XAMPP / WAMP stack. | Demos, QA, offline validation. | Not internet accessible; disable when idle. |

For shared hosting or VPS, prefer **gradual replacement with testing (recommended Option B)**:

1. Upload new page templates one at a time (`index.php`, `about.php`, etc.).
2. Smoke test each replacement in browser.
3. Clean up legacy includes (`includes/templates/`, redundant footers) after confirmation.

> Use the `DEPLOYMENT_OPTIONS.md` archive if you need original migration command snippets.

### Prepare Code & Assets

```bash
git clone https://github.com/<org>/apsdreamhome.git
cd apsdreamhome

composer install --no-dev --optimize-autoloader
npm install
npm run build        # or npm run build:pwa for PWA bundle

# Set permissions (Linux/macOS)
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/framework storage/logs
```

- Use `.env.production` template if available; avoid committing secrets.
- Hash and upload static assets (`dist/`, `public/`) alongside PHP templates.

### Database Migration

#### Export from source (phpMyAdmin example)

1. Open `http://localhost/phpmyadmin/`.
2. Select the `apsdreamhome` database.
3. Click **Export → Quick → SQL** to download `apsdreamhome.sql`.

#### Import to destination

```bash
# Via MySQL CLI (VPS)
mysql -u <user> -p -h <host> <target_db> < apsdreamhome.sql

# Shared hosting phpMyAdmin
1. Create empty database matching production naming convention.
2. Use Import tab to upload the SQL dump.
3. Verify tables/data counts post-import.
```

Maintain a migration log capturing schema version, dataset snapshots, and operator/date.

### Configuration Updates

```php
// includes/db_connection.php
function getDbConnection(): PDO {
    $host     = 'localhost';     // or remote DB host
    $dbname   = 'production_db';
    $username = 'db_user';
    $password = 'db_password';
    // remainder unchanged
}

// config.php (or equivalent)
define('BASE_URL', 'https://yourdomain.com/');
define('ADMIN_EMAIL', 'support@yourdomain.com');
```

Additional tasks:

- Toggle debug / logging levels for production (`display_errors = Off`).
- Configure SMTP credentials for transactional emails.
- Update any hard-coded asset or API endpoints to match production base URL.

### Web & SSL Configuration

#### Apache virtual host

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/apsdreamhome/public

    <Directory /var/www/apsdreamhome/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/apsdreamhome-error.log
    CustomLog ${APACHE_LOG_DIR}/apsdreamhome-access.log combined
</VirtualHost>
```

#### Nginx server block

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/apsdreamhome/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Transport security

```bash
sudo certbot --apache -d your-domain.com
# or
sudo certbot --nginx -d your-domain.com
```

Schedule auto-renewals (`certbot renew`). For shared hosting, use provider tools.

#### Cron / scheduled tasks

```bash
* * * * * cd /path/to/apsdreamhome && php artisan schedule:run >> /dev/null 2>&1
```

### Go-Live Checklist

| Area | Validation |
| ---- | ---------- |
| Domain & DNS | A/AAAA records point to production host; propagation verified. |
| SSL | HTTPS enforced, no mixed-content warnings. |
| Application | Frontend pages, admin dashboard, and API endpoints function as expected. |
| Data | Production DB seeded, admin accounts confirmed. |
| Monitoring | Analytics, uptime, and logging pipelines active. |
| Backups | Automated DB/file backups configured and tested. |
| Security | File permissions hardened, security headers applied, admin credentials rotated. |
| Stakeholder sign-off | Business owner approves content and functionality. |

Retain a signed-off deployment report including change logs, responsible engineers, and rollback plan.

---

## Post-Deployment Operations

### Monitoring & Metrics

- **Uptime**: UptimeRobot, Better Uptime, or similar with 1-minute probes.
- **Performance**: Google PageSpeed Insights, Lighthouse (`npm run performance`), WebPageTest.
- **Error tracking**: Enable PHP error logging (`logs/`), integrate Sentry/New Relic if available.
- **Business KPIs**: Contact form submissions, property inquiries, lead conversion, bounce rate.

### Maintenance Cadence

| Frequency | Tasks |
| --------- | ----- |
| Daily | Confirm site availability, review contact submissions, monitor admin audit logs. |
| Weekly | Update property listings, scan for broken links, review analytics trends. |
| Monthly | Apply framework/library updates, optimize database, refresh content, verify backups. |
| Quarterly | Security review (password rotations, role audit), load testing, review roadmap alignment. |

### Backups

#### Database

```bash
# Example automated backup (cron)
mysqldump -u <user> -p<password> apsdreamhome > /backups/apsdreamhome_$(date +%F).sql
```

- Store backups offsite (S3, Google Drive, OneDrive).
- Retain daily backups for 7 days, weekly for 4 weeks, monthly for 6 months (example policy).
- Test restore procedure quarterly.

#### Files

- Sync `public/`, `uploads/`, and configuration files to cloud storage.
- Use `rsync`/`scp` for VPS; use hosting backup wizards on shared hosting.
- Maintain a backup log (date, operator, location).

### Growth & Optimization

- **SEO & marketing**: Maintain Google My Business profile, produce real-estate blogs, schedule social posts.
- **Performance**: Implement CDN, enable Brotli/gzip compression, lazy-load heavy media.
- **Feature roadmap**: Expand geographic coverage, integrate CRM automations, introduce referral programs.
- **Analytics enhancements**: Track funnel events (tour bookings, brochure downloads).

---

## Troubleshooting Quick Reference

| Symptom | Likely Cause | Resolution |
| ------- | ------------ | ---------- |
| Site returns 500 error | Permissions misconfigured, missing dependencies. | Reapply file permissions, review PHP logs, ensure `vendor/` present. |
| Database connection error | Incorrect credentials or MySQL service down. | Verify `.env`/config values, restart MySQL, confirm network access. |
| Images not loading | Incorrect file paths or uploads folder permissions. | Check relative paths, set `uploads/` to 755, re-sync assets. |
| Contact form not sending mail | SMTP credentials missing or blocked. | Configure mail settings, test with `telnet`, fall back to transactional email service. |
| Admin login loop | Session path unwritable or mismatched domain. | Confirm session save path, adjust `BASE_URL`, clear browser cookies. |
| Mixed-content warnings | HTTP asset URLs on HTTPS site. | Update templates to use `BASE_URL` or protocol-relative paths. |

> Additional issue trees, maintenance routines, and hosting-specific scripts live in `docs/archive/DEPLOYMENT_MAINTENANCE_GUIDE.md` for historical reference.

---

## Support & References

- **Operational contacts**: `devops@apsdreamhome.com`, `support@apsdreamhome.com`, escalation phone +91-9554000001.
- **Health checks**: `/database/tools/db_health_report.php`, admin analytics panels (`admin/enhanced_dashboard.php`).
- **Related documentation**:
  - `docs/frontend.md` – build pipeline details.
  - `docs/archive/README_ENHANCED.md` – legacy frontend setup.
  - `docs/archive/DEPLOYMENT_MAINTENANCE_GUIDE.md` – preserved long-form deployment playbook.

Maintain this handbook as the single source of truth. When procedures change, update this document first, then archive superseded material under `docs/archive/`.
