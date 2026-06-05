# APS Dream Home - Production Deployment Guide

> **Last updated:** 2026-06-05
> **Audience:** DevOps engineers, system administrators, and developers deploying APS Dream Home to a production environment.
> **Estimated setup time:** 2-3 hours for a fresh server.

This guide walks you through deploying APS Dream Home to a production server using Docker Compose, Nginx, Let's Encrypt SSL, and zero-downtime rolling restarts. By the end, you'll have a horizontally-scalable, HTTPS-secured, fully-monitored application running on Ubuntu 22.04 (or any modern Linux distribution).

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Initial Server Setup](#2-initial-server-setup)
3. [Domain Configuration](#3-domain-configuration)
4. [Database Setup](#4-database-setup)
5. [Redis Setup](#5-redis-setup)
6. [WebSocket Setup](#6-websocket-setup)
7. [Environment Variables](#7-environment-variables)
8. [SSL with Let's Encrypt](#8-ssl-with-lets-encrypt)
9. [First Deploy](#9-first-deploy)
10. [Zero-Downtime Updates](#10-zero-downtime-updates)
11. [Backup Strategy](#11-backup-strategy)
12. [Monitoring & Health Checks](#12-monitoring--health-checks)
13. [Scaling](#13-scaling)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Prerequisites

### 1.1 Server Sizing

| Tier | vCPU | RAM | Disk | Use Case |
|------|------|-----|------|----------|
| **Minimum** | 2 | 4 GB | 40 GB SSD | Dev/staging, < 1K daily users |
| **Recommended** | 4 | 8 GB | 80 GB SSD | Production, 1K-10K daily users |
| **High traffic** | 8 | 16 GB | 200 GB NVMe | 10K+ daily users, live auctions |

### 1.2 Operating System

- **Ubuntu 22.04 LTS** (recommended) or 20.04 LTS
- **Debian 11** (Bullseye) or later
- **RHEL 9 / Rocky Linux 9 / AlmaLinux 9** (with SELinux configured)

All commands in this guide assume Ubuntu 22.04. For other distros, adapt the `apt` commands to your package manager.

### 1.3 Required Software

Install these before continuing:

```bash
# Update package index
sudo apt update && sudo apt upgrade -y

# Docker Engine + Compose plugin
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
# Log out and back in for group change to take effect

# Verify
docker --version          # Docker version 24+
docker compose version    # Docker Compose version v2.20+
git --version             # git 2.34+
curl --version            # curl 7.81+
```

### 1.4 Networking

Open the following inbound ports in your firewall / security group:

| Port | Protocol | Purpose |
|------|----------|---------|
| 22 | TCP | SSH (admin access) |
| 80 | TCP | HTTP (redirects to HTTPS) |
| 443 | TCP | HTTPS (public traffic) |
| 8080 | TCP | WebSocket (or expose via nginx on 443/ws) |

We recommend closing port 8080 publicly and routing WebSocket traffic through nginx (which is the default in our config).

### 1.5 DNS

Before deploying, point your domain's DNS A/AAAA records to the server's public IP:

```
A     apsdreamhome.com     -> 203.0.113.10
A     www.apsdreamhome.com -> 203.0.113.10
```

DNS propagation can take 24-48 hours. You can deploy without DNS in place, but Let's Encrypt requires the domain to resolve to obtain a certificate.

---

## 2. Initial Server Setup

### 2.1 Create a Deploy User

```bash
sudo adduser deploy
sudo usermod -aG docker deploy
sudo usermod -aG sudo deploy
```

### 2.2 Set Up SSH Key Authentication

From your local machine:

```bash
ssh-copy-id deploy@your-server-ip
```

Test:

```bash
ssh deploy@your-server-ip
```

### 2.3 Configure the Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

### 2.4 Install Fail2ban (Brute-force Protection)

```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 2.5 Clone the Repository

```bash
sudo mkdir -p /opt/apsdreamhome
sudo chown deploy:deploy /opt/apsdreamhome
cd /opt/apsdreamhome
git clone https://github.com/your-org/apsdreamhome.git .
git checkout production   # or main, depending on your branch
```

---

## 3. Domain Configuration

### 3.1 Update the Application

Set the `APP_URL` in `production.env`:

```env
APP_URL=https://apsdreamhome.com
```

The application uses this for:
- Generating absolute URLs in emails
- Open Graph meta tags
- CORS and CSP policies
- Stripe / payment callbacks (if used)

### 3.2 Verify DNS

```bash
dig +short apsdreamhome.com
dig +short www.apsdreamhome.com
```

Both should return your server's IP.

---

## 4. Database Setup

We use **MySQL 8.0** running inside a Docker container (defined in `docker-compose.yml`). For very high-traffic deployments, you may want to move to a managed RDS instance.

### 4.1 Configure Database Credentials

Edit `production.env`:

```env
DB_HOST=db                         # container name (internal)
DB_PORT=3306
DB_DATABASE=apsdreamhome
DB_USERNAME=apsdream
DB_PASSWORD=<32+ char random>      # generate: openssl rand -base64 32
MYSQL_ROOT_PASSWORD=<32+ char random>
```

### 4.2 Persistent Volume

The `db_data` named volume is mounted at `/var/lib/mysql` inside the container. Back this up regularly (see [Backup Strategy](#11-backup-strategy)).

### 4.3 Connection from Host (Optional)

To connect from your workstation via a MySQL client:

```bash
docker compose exec db mysql -u root -p
```

Or add a temporary port mapping in `docker-compose.override.yml` (don't ship this to production):

```yaml
services:
  db:
    ports:
      - "127.0.0.1:3306:3306"
```

### 4.4 Tuning

The default `docker/mysql/my.cnf` is sized for the 4 GB tier. For larger deployments, edit:

```ini
[mysqld]
innodb_buffer_pool_size = 4G     # 50-70% of available RAM
max_connections = 500
slow_query_log = 1
long_query_time = 2
```

---

## 5. Redis Setup

Redis is used for:
- HTTP cache (`admin_menu_*`, `header_projects_all`, `unread_count_*`)
- PHP sessions (so login survives app restarts)
- Queue backend
- WebSocket pub/sub

### 5.1 Configuration

Default settings in `docker/redis/redis.conf`:

- 256 MB max memory, allkeys-lru eviction
- AOF + RDB persistence
- 16 logical databases (we use 0 for cache, 1 for sessions)

### 5.2 Set a Password (Production)

In `production.env`:

```env
REDIS_PASSWORD=<32+ char random>
```

Then update `docker/redis/redis.conf`:

```conf
requirepass <your-password>
```

Restart: `docker compose restart redis`

### 5.3 Monitor Memory

```bash
docker compose exec redis redis-cli info memory
```

If `used_memory` consistently exceeds 80% of `maxmemory`, increase the limit in `docker-compose.production.yml`:

```yaml
redis:
  command:
    - redis-server
    - /usr/local/etc/redis/redis.conf
    - --maxmemory
    - 1gb    # was 768mb
```

---

## 6. WebSocket Setup

The WebSocket server (Ratchet, PHP) handles:
- Real-time chat
- Live notifications
- Property auction bidding
- NPS triggers
- Live visitor tracking

### 6.1 Single Instance (Default)

One WebSocket container is the default. The server uses file-based state for connected users (acceptable for single instance). If you scale to multiple instances, you need Redis pub/sub for state sharing.

### 6.2 Multiple Instances (HA)

Requires:
1. Redis pub/sub channel (set `WEBSOCKET_REDIS_CHANNEL=aps-ws`)
2. Sticky sessions on the load balancer
3. Shared `app_storage` volume (read-write from all instances)

Edit `docker-compose.production.yml`:

```yaml
websocket:
  deploy:
    replicas: 3
```

### 6.3 Behind Nginx (Recommended)

WebSocket traffic is upgraded at nginx via the `Connection: Upgrade` header. Our `app.conf` already handles `/ws/` paths. Clients connect to `wss://apsdreamhome.com/ws/` (TLS terminated at nginx).

---

## 7. Environment Variables

All environment variables live in `production.env` (NEVER commit this file):

### 7.1 Required

| Variable | Example | Notes |
|----------|---------|-------|
| `APP_ENV` | `production` | Forces production mode |
| `APP_DEBUG` | `false` | Disables verbose error pages |
| `APP_URL` | `https://apsdreamhome.com` | Used for absolute URLs |
| `APP_KEY` | `base64:...32-bytes...` | Used for encryption |
| `DB_HOST` | `db` | Container name |
| `DB_DATABASE` | `apsdreamhome` | |
| `DB_USERNAME` | `apsdream` | |
| `DB_PASSWORD` | `<strong>` | |
| `MYSQL_ROOT_PASSWORD` | `<strong>` | |
| `REDIS_PASSWORD` | `<strong>` | Set in production |

### 7.2 Recommended

| Variable | Example | Notes |
|----------|---------|-------|
| `MAIL_MAILER` | `smtp` | For transactional email |
| `MAIL_HOST` | `smtp.sendgrid.net` | |
| `MAIL_PORT` | `587` | |
| `MAIL_USERNAME` | `apikey` | |
| `MAIL_PASSWORD` | `<sendgrid-key>` | |
| `MAIL_ENCRYPTION` | `tls` | |
| `MAIL_FROM_ADDRESS` | `noreply@apsdreamhome.com` | |
| `TZ` | `Asia/Kolkata` | Server timezone |

### 7.3 Optional

| Variable | Purpose |
|----------|---------|
| `GOOGLE_OAUTH_CLIENT_ID` | Google login |
| `GOOGLE_OAUTH_CLIENT_SECRET` | |
| `TWILIO_SID`, `TWILIO_TOKEN`, `TWILIO_FROM` | WhatsApp/SMS notifications |
| `SENTRY_DSN` | Error tracking |
| `SLACK_WEBHOOK` | Deploy notifications |
| `S3_BUCKET` | Remote backup destination |
| `BACKUP_ENCRYPTION_KEY` | Encrypt local backups |

Generate strong random values with:

```bash
openssl rand -base64 32
```

---

## 8. SSL with Let's Encrypt

We use **Let's Encrypt** with the **webroot** challenge (no port 80 service downtime).

### 8.1 Install Certbot

```bash
sudo apt install -y certbot
```

### 8.2 Obtain a Certificate

```bash
cd /opt/apsdreamhome
sudo ./scripts/setup_ssl.sh \
  -d apsdreamhome.com \
  -d www.apsdreamhome.com \
  -e admin@apsdreamhome.com
```

The script will:
1. Run `certbot certonly --webroot`
2. Copy certs to `docker/ssl/`
3. Enable the nginx HTTPS server block
4. Enable HTTP→HTTPS redirect
5. Reload nginx
6. Install a daily cron job at 03:00 UTC for renewal

### 8.3 Test Auto-Renewal

```bash
sudo certbot renew --dry-run
```

### 8.4 Wildcard Certificates (Optional)

For `*.apsdreamhome.com`, you'll need DNS challenge:

```bash
sudo certbot certonly --dns-cloudflare \
  --dns-cloudflare-credentials /root/.secrets/cloudflare.ini \
  -d "*.apsdreamhome.com" -d "apsdreamhome.com"
```

### 8.5 Verify SSL Grade

```bash
# Local
openssl s_client -connect apsdreamhome.com:443 -servername apsdreamhome.com < /dev/null 2>&1 | openssl x509 -noout -subject -dates

# Remote
# Visit: https://www.ssllabs.com/ssltest/analyze.html?d=apsdreamhome.com
```

Target: **A or A+ grade**.

---

## 9. First Deploy

### 9.1 Pre-flight

```bash
cd /opt/apsdreamhome
ls production.env       # must exist
ls docker/ssl/fullchain.pem   # must exist (after SSL setup)
docker compose config --quiet   # validates compose file
```

### 9.2 Build and Start

```bash
docker compose -f docker-compose.yml -f docker-compose.production.yml build
docker compose -f docker-compose.yml -f docker-compose.production.yml up -d
```

### 9.3 Watch the Logs

```bash
docker compose logs -f
# or
docker compose logs -f app
```

### 9.4 Run Migrations

Migrations run automatically on first start (via `docker-entrypoint.sh`). To run manually:

```bash
docker compose exec app php scripts/create_migrations_table.php
docker compose exec app php scripts/track_migration.php
```

### 9.5 Verify

```bash
curl -I https://apsdreamhome.com
# Should return: HTTP/2 200

bash scripts/health_check.sh
# Should report: STATUS: HEALTHY
```

### 9.6 Create Admin User

```bash
docker compose exec app php -r "
require 'vendor/autoload.php';
\$db = new PDO('mysql:host=db;dbname=apsdreamhome', 'root', getenv('MYSQL_ROOT_PASSWORD'));
\$hash = password_hash('ChangeMe123!', PASSWORD_BCRYPT);
\$stmt = \$db->prepare('INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
\$stmt->execute(['Admin', 'admin@apsdreamhome.com', \$hash, 'admin', 'active']);
echo \"Admin created with ID: \" . \$db->lastInsertId() . PHP_EOL;
"
```

Then login at: `https://apsdreamhome.com/admin/login`

---

## 10. Zero-Downtime Updates

```bash
cd /opt/apsdreamhome
git pull origin main
./deploy-to-production.sh
```

The deploy script:
1. Backs up the database
2. Builds new images (in parallel)
3. Runs pending migrations
4. **Scales `app` to 2 instances** so there's always one running
5. Waits for the new instance to pass its health check
6. Stops the old instance
7. Scales back to 1
8. Reloads nginx (workers only, no connection drop)
9. Runs health checks
10. Rolls back on any failure

Total downtime: **< 1 second** (just the time it takes nginx to switch to the new upstream).

---

## 11. Backup Strategy

### 11.1 Database Backups

#### Automatic (via cron)

The `db-backup` sidecar container (`docker-compose.override.yml` with `with-backup` profile) runs `docker/backup/backup.sh` daily.

```bash
docker compose --profile with-backup up -d db-backup
```

#### Manual

```bash
./scripts/backup_before_deploy.sh
```

This creates `backups/pre_deploy_TIMESTAMP.sql.gz` and:
- Keeps the last 30 days locally
- Optionally uploads to S3 (`S3_BUCKET` env var)
- Optionally encrypts with `BACKUP_ENCRYPTION_KEY`

#### Restore

```bash
gunzip -c backups/pre_deploy_20260605_120000.sql.gz | docker compose exec -T db mysql -u root -p
```

### 11.2 Application Uploads

Uploads are stored in the `app_uploads` and `app_assets_uploads` named volumes. Back these up to S3:

```bash
docker run --rm \
  -v apsdreamhome_app_uploads:/data:ro \
  -v $(pwd)/backups:/backup \
  alpine tar czf /backup/uploads_$(date +%Y%m%d).tar.gz -C /data .
aws s3 cp backups/uploads_*.tar.gz s3://my-backup-bucket/uploads/
```

### 11.3 Configuration Files

Keep a copy of:
- `production.env` (in a password manager)
- `docker/ssl/` (the certs)
- Any custom nginx configs

---

## 12. Monitoring & Health Checks

### 12.1 Health Endpoint

The app exposes `GET /health` returning JSON:

```json
{
  "status": "healthy",
  "timestamp": "2026-06-05T14:30:00Z",
  "checks": {
    "database": "ok",
    "redis": "ok",
    "disk": "ok"
  }
}
```

### 12.2 Manual Check

```bash
bash scripts/health_check.sh
```

Output:

```
[OK]   http                HTTP 200 on https://apsdreamhome.com/health
[OK]   database            MySQL responsive (315 tables, 24.7 MB)
[OK]   redis               Redis responsive (memory: 8.2M)
[OK]   websocket           WebSocket port 8080 open
[OK]   disk                45% full (free: 22G)
[OK]   backup              Latest backup: 6h old (pre_deploy_*.sql.gz)
[OK]   ssl                 Valid for 78 days (expires: Aug 22)
```

### 12.3 JSON Output (for Monitoring)

```bash
bash scripts/health_check.sh --json | curl -X POST -H "Content-Type: application/json" -d @- https://monitoring.example.com/api/ingest
```

### 12.4 Integrate with Uptime Monitoring

Use the JSON output with:
- **UptimeRobot** — poll `https://apsdreamhome.com/health` every 60s
- **Pingdom** — HTTP check
- **Better Stack** — JSON status check on `/health` endpoint
- **Prometheus** — textfile exporter + node_exporter

### 12.5 Error Tracking (Sentry)

```env
SENTRY_DSN=https://...@sentry.io/...
```

The `app/Services/Monitoring/ErrorTracker.php` service auto-captures unhandled exceptions.

### 12.6 Log Aggregation

Logs are written to:
- `app_logs` volume (Apache + PHP error log)
- Stdout (captured by Docker, view with `docker compose logs`)
- Application logs: `storage/logs/*.log`

For centralization, use a sidecar like `vector` or `fluentd` to ship to Loki/Elasticsearch.

---

## 13. Scaling

### 13.1 Vertical Scaling (Bigger Server)

Edit `docker-compose.production.yml` to increase resources:

```yaml
app:
  deploy:
    resources:
      limits:
        cpus: "4.0"
        memory: 4G
db:
  deploy:
    resources:
      limits:
        cpus: "4.0"
        memory: 8G
```

Then restart: `docker compose up -d`

### 13.2 Horizontal Scaling (More App Instances)

```yaml
app:
  deploy:
    replicas: 4    # was 2
```

Nginx automatically load-balances across instances (round-robin). For sticky sessions (if needed by WebSocket), add `ip_hash` to the upstream block.

### 13.3 Database Scaling

For > 50K daily users, consider:
- **AWS RDS / DigitalOcean Managed MySQL** — automated backups, point-in-time recovery
- **Read replicas** for read-heavy workloads (admin reports, search)
- **Connection pooler** (ProxySQL) if you hit `max_connections`

### 13.4 CDN for Static Assets

Put Cloudflare or CloudFront in front of:
- `assets/images/*`
- `assets/css/*`
- `assets/js/*`
- `uploads/*`

The `.htaccess` and nginx config already set `Cache-Control: public, max-age=31536000, immutable` for these.

---

## 14. Troubleshooting

### 14.1 Container Won't Start

```bash
docker compose ps                    # see which container is failing
docker compose logs app --tail=100   # view app logs
docker compose logs db  --tail=100   # view DB logs
```

Common issues:
- `database is uninitialized` → run `./scripts/backup_before_deploy.sh` to check
- `permission denied` on `storage/` → re-run entrypoint: `docker compose up -d app`
- `port already in use` → check with `sudo lsof -i :80`

### 14.2 Health Check Failing

```bash
bash scripts/health_check.sh --json | jq
```

Drill into the failing check:
- `http: crit` → `docker compose logs nginx app`
- `database: crit` → `docker compose exec db mysqladmin ping`
- `redis: crit` → `docker compose exec redis redis-cli ping`
- `websocket: crit` → `docker compose logs websocket --tail=50`
- `disk: crit` → clean up old logs/backups

### 14.3 502 Bad Gateway

Nginx can't reach the app:

```bash
docker compose ps app
docker compose logs app --tail=50
docker compose exec app php -r "echo 'PHP works';"
```

If the app is up but nginx still 502s, check the upstream config in `docker/nginx/conf.d/app.conf` and verify `php_app` resolves:

```bash
docker compose exec nginx nslookup php_app
# or
docker compose exec nginx getent hosts php_app
```

### 14.4 WebSocket Disconnects

```bash
docker compose logs websocket --tail=100
```

Common causes:
- nginx timeout too low — verify `proxy_read_timeout 86400;` is in the `/ws/` location
- PHP max_execution_time killing the process
- Network MTU issues with Docker bridge

### 14.5 Database Connection Exhausted

```bash
docker compose exec db mysql -u root -p -e "SHOW PROCESSLIST;"
docker compose exec db mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';"
```

If many idle connections, lower PHP's `max_persistent` or use ProxySQL.

### 14.6 Rolling Back

```bash
cd /opt/apsdreamhome
git log --oneline -5    # find a known-good commit
git checkout <commit-sha>
./deploy-to-production.sh --skip-migrate
```

To restore the database:

```bash
gunzip -c backups/pre_deploy_<good-timestamp>.sql.gz | docker compose exec -T db mysql -u root -p
```

### 14.7 SSL Certificate Renewal Failed

```bash
sudo certbot renew --dry-run
sudo certbot renew --force-renewal
sudo systemctl status certbot.timer
```

Check that the webroot is accessible: `curl http://apsdreamhome.com/.well-known/acme-challenge/test`

### 14.8 Getting Help

- Check `logs/deploy_<timestamp>.log` for the most recent deploy attempt
- Run `docker compose ps` and `docker compose logs --tail=200`
- Visit the GitHub issues: https://github.com/your-org/apsdreamhome/issues

---

## Appendix A: Useful Make Targets

```bash
make help              # show all targets
make up                # docker compose up -d
make down              # docker compose down
make logs              # tail all logs
make logs-app          # tail app logs only
make health            # run health check
make smoke-test        # post-deploy verification (10 sections)
make db-backup         # run backup script
make db-restore FILE=  # restore from backup
make deploy            # full zero-downtime deploy
make ssl-init DOMAIN=  # obtain Let's Encrypt cert
```

## Appendix B: Quick Reference

```bash
# Deploy
./deploy-to-production.sh

# Backup
./scripts/backup_before_deploy.sh

# Health
./scripts/health_check.sh

# SSL
./scripts/setup_ssl.sh -d example.com -e admin@example.com

# Restart just the app
docker compose up -d --no-deps app

# View logs
docker compose logs -f --tail=200 app

# Execute a PHP command in the app container
docker compose exec app php -r "echo 'hello';"

# Open a MySQL shell
docker compose exec db mysql -u root -p

# Open a Redis shell
docker compose exec redis redis-cli

# View resource usage
docker stats
```

## Appendix C: Security Checklist

- [ ] All environment variables use strong random values (32+ chars)
- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] MySQL root password != DB user password
- [ ] Redis password set
- [ ] Firewall only exposes 22, 80, 443
- [ ] Fail2ban enabled
- [ ] SSH key-only authentication (password auth disabled)
- [ ] Let's Encrypt certificate installed and auto-renewing
- [ ] HSTS header present (verify with `curl -I https://...`)
- [ ] Backups run daily and verified
- [ ] Sentry (or equivalent) error tracking configured
- [ ] Uptime monitoring configured
- [ ] All admin accounts use 2FA
- [ ] API keys are scoped (read-only where possible)

---

## Appendix D: AWS S3 Storage Setup

The application ships with a swappable storage layer (`App\Services\Storage\StorageManager`).
By default it uses local disk; flipping `STORAGE_DRIVER=s3` routes every upload and backup
through the `S3Storage` adapter (which uses AWS Signature V4 - no SDK dependency).

### D.1 Create the S3 bucket

1. **Open the S3 console** at <https://s3.console.aws.amazon.com/s3/>.
2. Click **Create bucket** and configure:
   - **Bucket name**: `apsdreamhome-uploads` (must be globally unique - pick your own).
   - **Region**: same as your EC2/ECS region (e.g. `ap-south-1` for Mumbai, `us-east-1` for N. Virginia).
   - **Object Ownership**: ACLs disabled (recommended).
   - **Block Public Access**: ON for private buckets, OFF only if you serve via CloudFront.
   - **Bucket Versioning**: Enabled (cheap insurance against accidental deletes).
   - **Default encryption**: SSE-S3 (AES-256). Free, no performance cost.
   - **Tags**: at minimum `Project=apsdreamhome`, `Env=production`.
3. Click **Create bucket**.

### D.2 Create an IAM user with least privilege

**Never use root credentials.** Create a dedicated IAM user for the app:

1. Open <https://console.aws.amazon.com/iam/> → **Users** → **Add users**.
2. **User name**: `apsdreamhome-s3-app`.
3. **Access type**: ✅ Programmatic access. ❌ Console access (not needed).
4. **Permissions**: attach the following inline policy (replace `apsdreamhome-uploads` with your bucket name):

   ```json
   {
     "Version": "2012-10-17",
     "Statement": [
       {
         "Effect": "Allow",
         "Action": [
           "s3:PutObject",
           "s3:GetObject",
           "s3:DeleteObject",
           "s3:ListBucket",
           "s3:HeadObject",
           "s3:CopyObject",
           "s3:AbortMultipartUpload",
           "s3:ListMultipartUploadParts"
         ],
         "Resource": [
           "arn:aws:s3:::apsdreamhome-uploads",
           "arn:aws:s3:::apsdreamhome-uploads/*"
         ]
       }
     ]
   }
   ```
5. **Save** the Access Key ID and Secret Access Key shown on the confirmation page.
   The secret is shown only once.

### D.3 Configure CORS (only if browser-side PUTs to S3)

The app uploads via the PHP backend, so CORS is **not required for normal operation**.
If you later add direct browser uploads, add this CORS rule on the bucket:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration>
  <CORSRule>
    <AllowedOrigin>https://yourdomain.com</AllowedOrigin>
    <AllowedMethod>GET</AllowedMethod>
    <AllowedMethod>PUT</AllowedMethod>
    <AllowedMethod>POST</AllowedMethod>
    <AllowedHeader>*</AllowedHeader>
    <ExposeHeader>ETag</ExposeHeader>
    <MaxAgeSeconds>3000</MaxAgeSeconds>
  </CORSRule>
</CORSConfiguration>
```

### D.4 Lifecycle policy: auto-delete old backups (90 days)

1. In the S3 console, open your bucket → **Management** → **Create lifecycle rule**.
2. **Rule name**: `expire-old-backups`.
3. **Scope**: Prefix `backups/`.
4. **Lifecycle rule actions**:
   - Transition objects to **Infrequent Access** after 30 days.
   - Transition to **Glacier Instant Retrieval** after 60 days.
   - **Expire** (delete) after 90 days.
5. Save. The rule runs once per day.

### D.5 CloudFront CDN in front of S3 (optional, recommended for media)

For public read access (property images, blog thumbnails) put CloudFront in front:

1. Open <https://console.aws.amazon.com/cloudfront/> → **Create distribution**.
2. **Origin domain**: select your bucket (`apsdreamhome-uploads.s3.ap-south-1.amazonaws.com`).
3. **Origin access**: **Origin access control settings (recommended)** → **Create new OAC**.
   Copy the auto-generated bucket policy and paste it into the S3 bucket's permissions.
4. **Viewer protocol policy**: **Redirect HTTP to HTTPS**.
5. **Allowed HTTP methods**: GET, HEAD, OPTIONS.
6. **Cache policy**: `CachingOptimized` (built-in) or a custom policy with `?v=` query strings respected.
7. **Price class**: `Use all edge locations` for global, or `Use only North America and Europe` for ~30% cheaper.
8. **Alternate domain name (CNAME)**: `cdn.yourdomain.com`. Add the certificate in ACM (`us-east-1`).
9. **Default root object**: leave blank.
10. Click **Create distribution**. Provisioning takes ~5 minutes.

In your app, set the CDN URL via env (after update):

```env
AWS_CLOUDFRONT_DOMAIN=cdn.yourdomain.com
```

The `S3Storage::url()` method will return the CloudFront URL when this is set.

### D.6 Cost estimation (rough)

| Resource | Quantity (small) | Price (USD/mo, ap-south-1) |
|----------|------------------|---------------------------|
| S3 Standard storage | 100 GB | ~$2.30 |
| S3 PUT/COPY/POST | 100K | ~$0.50 |
| S3 GET | 1M | ~$0.40 |
| CloudFront egress | 100 GB | ~$8.50 |
| Glacier Instant | 50 GB (after 30d) | ~$0.80 |
| **Total** | | **~$12.50** |

For a small/medium production app, expect **$10-30/month** in storage + CDN costs.
Enable the **AWS Free Tier** for the first 12 months (5 GB S3, 15 GB CloudFront, 15M requests).

### D.7 Using S3-compatible services (MinIO, DigitalOcean Spaces, Cloudflare R2)

The `S3Storage` adapter works with any S3-compatible service. Just set:

```env
STORAGE_DRIVER=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com   # or your MinIO/R2 endpoint
AWS_S3_USE_PATH_STYLE=true                          # required for MinIO/Spaces
```

Path-style addressing is automatically enabled when `AWS_ENDPOINT` is set.

### D.8 Verifying the setup

1. Open `https://yourdomain.com/admin/storage` in your browser.
2. The **AWS S3** card should show **Configured** with bucket + region.
3. Click **Test Connection** → a 1-byte file is uploaded, fetched, and deleted.
4. Click **View Bucket (first 10)** → you should see the test object's key briefly.
5. Upload a property image via the admin UI → confirm it lands in S3.

For end-to-end verification, run:

```bash
S3_TEST_MODE=true php testing/test_s3_storage.php
```

The suite runs 53 tests when no creds are set, and 70+ tests against real S3 with creds.
Exit code 0 means all pass.

---

**Happy deploying!** Questions? Open an issue on GitHub or ping the DevOps channel.
