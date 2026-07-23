# =============================================================================
# APS Dream Home - Docker Deployment Guide
# Complete production deployment using Docker Compose
# =============================================================================

This guide covers how to deploy **APS Dream Home** (a custom PHP MVC real-estate CRM) to production using Docker.

---

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Prerequisites](#prerequisites)
3. [Quick Start (Local)](#quick-start-local)
4. [Production Deployment](#production-deployment)
5. [SSL / HTTPS Setup](#ssl--https-setup)
6. [Backup & Restore](#backup--restore)
7. [Monitoring & Health](#monitoring--health)
8. [Performance Tuning](#performance-tuning)
9. [Troubleshooting](#troubleshooting)
10. [Zero-Downtime Deploys](#zero-downtime-deploys)

---

## Architecture Overview

The stack uses **5+ containers**, all connected via the `apsdreamhome_network` bridge network:

```
                                    ┌─────────────────┐
                                    │   Let's Encrypt │
                                    │   (certbot)     │
                                    └────────┬────────┘
                                             │ certs
┌─────────┐       ┌─────────────────┐       ▼
│  Users  │──────▶│     Nginx       │  ┌────────────────┐
└─────────┘       │  (reverse proxy,│──│ Static files   │
                  │   TLS, gzip,    │  │ + WebSocket    │
                  │   rate limit)   │  │   upgrade      │
                  └────────┬────────┘  └────────────────┘
                           │
        ┌──────────────────┼──────────────────────┐
        │                  │                      │
        ▼                  ▼                      ▼
┌──────────────┐    ┌──────────────┐      ┌──────────────┐
│  app         │    │  websocket   │      │  app         │
│  (php-apache)│    │  (php-cli +  │      │  (php-apache)│
│  PHP 8.2     │    │  Ratchet)    │      │  PHP 8.2     │
│  Port 80     │    │  Port 8080   │      │  Port 80     │
└──────┬───────┘    └──────┬───────┘      └──────┬───────┘
       │                   │                     │
       └─────────┬─────────┴─────────────────────┘
                 │
       ┌─────────┴──────────┬─────────────────┐
       ▼                    ▼                 ▼
┌─────────────┐      ┌─────────────┐    ┌─────────────┐
│  db         │      │  redis      │    │  db-backup  │
│  MySQL 8.0  │      │  Redis 7    │    │  (sidecar)  │
│  Port 3306  │      │  Port 6379  │    │  Daily cron │
└─────────────┘      └─────────────┘    └─────────────┘
```

| Service       | Image                                  | Port (host)     | Purpose                          |
|---------------|----------------------------------------|-----------------|----------------------------------|
| `nginx`       | `nginx:1.27-alpine`                    | 80, 443         | Reverse proxy, TLS, static files |
| `app`         | `apsdreamhome/app` (php:8.2-apache)    | internal 80     | PHP web frontend + REST API      |
| `websocket`   | `apsdreamhome/websocket` (php:8.2-cli) | internal 8080   | Ratchet WebSocket server         |
| `db`          | `mysql:8.0`                            | internal 3306   | Primary database                 |
| `redis`       | `redis:7-alpine`                       | internal 6379   | Cache, sessions, queues          |
| `db-backup`   | `alpine:3.19 + mysqldump`              | -               | (optional) daily DB backups      |

All data is stored in **named Docker volumes** that survive container restarts:
- `db_data` – MySQL data files
- `redis_data` – Redis snapshots and AOF
- `app_storage` – `storage/` directory (logs, cache, sessions, uploads)
- `app_uploads` – `public/uploads/`
- `db_backups` – Database dump files

---

## Prerequisites

- **Docker Engine 24.0+** ([install](https://docs.docker.com/engine/install/))
- **Docker Compose V2** (`docker compose` is part of Docker CLI in v2)
- **2 vCPU + 4 GB RAM** minimum (4 vCPU + 8 GB recommended for production)
- **Domain name** pointed at your server (for SSL)
- **Open ports**: 80 (HTTP) and 443 (HTTPS)

Verify:
```bash
docker --version
docker compose version
docker run hello-world
```

---

## Quick Start (Local)

For local development on Windows / macOS / Linux:

```bash
# 1. Clone the repository
git clone <your-repo-url> apsdreamhome
cd apsdreamhome

# 2. Copy the environment file
cp production.env.example .env
# Edit .env - set DB_PASSWORD, APP_KEY (use: openssl rand -base64 32)

# 3. (Optional) Enable local dev overrides
cp docker-compose.override.yml.example docker-compose.override.yml

# 4. Build and start the stack
make build
make up

# 5. Watch the logs
make logs

# 6. Open the app
open http://localhost
```

The first boot will:
1. Start MySQL (waits for healthcheck)
2. Start Redis (waits for healthcheck)
3. Start the app container
4. The app's entrypoint runs `php scripts/create_migrations_table.php` and seed scripts
5. Nginx is ready to serve

---

## Production Deployment

### 1. Provision a server

Recommended providers (any VPS works):
- **DigitalOcean** – 4 GB droplet, $24/mo
- **Hetzner** – CX31, €15/mo
- **AWS EC2** – t3.medium
- **Google Cloud** – e2-standard-2

OS: **Ubuntu 22.04 LTS** or later.

### 2. Install Docker

```bash
# Update and install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add your user to the docker group
sudo usermod -aG docker $USER
newgrp docker

# Verify
docker --version
docker compose version
```

### 3. Configure firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 4. Clone the repo and configure

```bash
cd /opt
sudo git clone <your-repo-url> apsdreamhome
sudo chown -R $USER:$USER apsdreamhome
cd apsdreamhome

# Copy the production env template
cp production.env.example production.env
nano production.env    # set strong passwords, APP_KEY, APP_URL, etc.
```

Generate a strong APP_KEY:
```bash
openssl rand -base64 32
# paste as APP_KEY=base64:<that-value>
```

### 5. Set up the deploy script

```bash
chmod +x deploy-to-production.sh
```

### 6. First deploy

```bash
# Pull, build, migrate, restart, health check
./deploy-to-production.sh
```

This will:
- Pull the latest `production` branch
- Backup the database
- Build the new app + websocket images
- Run migrations
- Perform a rolling restart of the app and websocket
- Reload nginx
- Verify all healthchecks pass
- Send a notification (if you set `NOTIFY_WEBHOOK_URL`)

### 7. Verify

```bash
# Check all containers are healthy
make health

# Tail logs
make logs

# Visit your site
curl -I http://your-domain.com
```

---

## SSL / HTTPS Setup

There are **three** options, in order of recommendation:

### Option A – Let's Encrypt (recommended, free)

#### 1. Get a certificate using certbot

```bash
# Make sure your domain points to the server first!
# Then run:
make ssl-init DOMAIN=apsdreamhome.com EMAIL=admin@apsdreamhome.com
```

Or manually:
```bash
docker run --rm \
    -v $(pwd)/docker/ssl:/etc/letsencrypt \
    -v $(pwd)/docker/certbot/www:/var/www/certbot \
    certbot/certbot certonly --webroot \
    --webroot-path=/var/www/certbot \
    --email admin@apsdreamhome.com \
    --agree-tos --no-eff-email \
    -d apsdreamhome.com -d www.apsdreamhome.com
```

This writes:
- `docker/ssl/live/apsdreamhome.com/fullchain.pem`
- `docker/ssl/live/apsdreamhome.com/privkey.pem`

#### 2. Link them to the expected paths

```bash
ln -sf docker/ssl/live/apsdreamhome.com/fullchain.pem docker/ssl/fullchain.pem
ln -sf docker/ssl/live/apsdreamhome.com/privkey.pem docker/ssl/privkey.pem
```

#### 3. Enable SSL in nginx

Edit `docker/nginx/conf.d/ssl-redirect.conf` and **uncomment** the redirect block:
```nginx
if ($redirect_to_https = "yes-yes") {
    return 301 https://$host$request_uri;
}
```

#### 4. Restart nginx

```bash
docker compose restart nginx
```

#### 5. Set up auto-renewal

Add a cron job on the host:
```bash
0 3 * * * cd /opt/apsdreamhome && docker run --rm -v $(pwd)/docker/ssl:/etc/letsencrypt -v $(pwd)/docker/certbot/www:/var/www/certbot certbot/certbot renew --quiet && docker compose exec nginx nginx -s reload
```

### Option B – Bring your own certificate

If you have a certificate from a commercial CA:

```bash
# Copy your cert and key into the right location
cp /path/to/your/fullchain.pem docker/ssl/fullchain.pem
cp /path/to/your/privkey.pem     docker/ssl/privkey.pem
chmod 600 docker/ssl/privkey.pem
```

Then follow steps 3-5 above.

### Option C – Reverse proxy in front of nginx (Cloudflare, AWS ALB, etc.)

If you run Cloudflare or an AWS ALB in front of the stack, you can leave nginx in HTTP-only mode and let the upstream proxy handle TLS. The app will see `X-Forwarded-Proto: https` from the proxy and behave correctly.

---

## Backup & Restore

### Automated daily backups

The `db-backup` service is included but **disabled by default** (uses the `with-backup` profile).

Enable it:
```bash
docker compose --profile with-backup up -d db-backup
```

This runs `docker/backup/backup.sh` which:
1. Dumps the database with `mysqldump`
2. Compresses with gzip
3. Saves to the `db_backups` volume
4. Keeps the last 7 days (configurable via `BACKUP_RETENTION_DAYS`)

To schedule it daily, add to the host's crontab:
```bash
0 2 * * * cd /opt/apsdreamhome && docker compose --profile with-backup run --rm db-backup
```

### Manual backup

```bash
# Local
make db-backup

# Trigger the in-container backup sidecar
docker compose run --rm db-backup

# Copy backups off the host
rsync -avz /opt/apsdreamhome/backups/ backup-user@backup-server:/backups/apsdreamhome/
```

### Restore

```bash
# Find the backup file
ls -la backups/

# Restore (will prompt for confirmation)
make db-restore FILE=backups/pre_deploy_20260101_120000.sql.gz
```

Or directly:
```bash
gunzip -c backups/pre_deploy_*.sql.gz | docker compose exec -T db mysql -u root -p"$MYSQL_ROOT_PASSWORD" $DB_DATABASE
```

### File backup

Application uploads live in Docker volumes. Back them up separately:
```bash
docker run --rm \
    -v apsdreamhome_app_uploads:/source:ro \
    -v $(pwd)/backups/uploads:/dest \
    alpine:3.19 tar czf /dest/uploads_$(date +%Y%m%d).tar.gz -C /source .
```

---

## Monitoring & Health

### Built-in healthchecks

Every service has a `HEALTHCHECK` directive. View status:
```bash
make health
```

Output:
```
apsdreamhome_app:        healthy
apsdreamhome_websocket:  healthy
apsdreamhome_db:         healthy
apsdreamhome_redis:      healthy
apsdreamhome_nginx:      healthy
```

### External health endpoint

The nginx container exposes `GET /health` which returns `200 ok`:
```bash
curl http://localhost/health
```

### Logs

```bash
make logs            # all services
make logs-app        # just the app
make logs-db         # just MySQL
```

Logs are also written to host paths via the `json-file` log driver with rotation (5 files × 20 MB).

### Container stats

```bash
make stats
```

### Recommended external monitoring

For production, set up:
- **Uptime monitoring**: UptimeRobot, Pingdom, or Hetrixtools
- **Log aggregation**: Loki, Datadog, New Relic
- **Metrics**: Prometheus + Grafana with [cAdvisor](https://github.com/google/cadvisor)

---

## Performance Tuning

### MySQL

Edit `docker/mysql/my.cnf`. The defaults are tuned for a 2 GB server. For larger servers:

| Server RAM | `innodb_buffer_pool_size` | `max_connections` |
|-----------:|--------------------------:|------------------:|
| 4 GB       | 1G                        | 300               |
| 8 GB       | 4G                        | 500               |
| 16 GB      | 8G                        | 1000              |

Restart after changes:
```bash
docker compose restart db
```

### Redis

Edit `docker/redis/redis.conf`:
- `maxmemory` – increase for more cache (default 384 MB)
- `maxmemory-policy allkeys-lru` – evicts least-recently-used keys

### PHP / Apache

Edit `docker/php/Dockerfile` and rebuild:
- `memory_limit` – default 512 MB
- `opcache.memory_consumption` – default 256 MB
- `upload_max_filesize` – default 50 MB

```bash
make build-app
make restart-app
```

### Nginx

`docker/nginx/nginx.conf` settings:
- `worker_processes auto` – one per CPU
- `worker_connections 4096`
- `client_max_body_size 50M` – increase for large uploads

### App scaling

Scale the stateless services:
```bash
# Run 3 app instances behind nginx
docker compose up -d --scale app=3

# (WebSockets are sticky – keep websocket at 1 unless you have shared state)
```

---

## Troubleshooting

### "Database connection refused"

```bash
# 1. Is the db container running?
docker compose ps db

# 2. Are the credentials correct?
docker compose exec db env | grep MYSQL

# 3. Test the connection from the app container
docker compose exec app sh -c "echo > /dev/tcp/db/3306 && echo 'OK' || echo 'FAIL'"
```

### "Permission denied" on storage

```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/storage
```

### "vendor/autoload.php not found"

Composer install failed during build. Try:
```bash
make composer-install
```

### Container won't start

```bash
# View the full logs
make logs-app

# Override the entrypoint to get a shell
docker compose run --rm --entrypoint /bin/bash app
```

### WebSocket won't connect

```bash
# 1. Is the websocket container running?
docker compose ps websocket

# 2. Is the port open internally?
docker compose exec websocket nc -z localhost 8080

# 3. Can the app reach the websocket?
docker compose exec app nc -zv websocket 8080

# 4. Is nginx routing /ws correctly?
docker compose exec nginx nginx -T | grep -A5 "location /ws"
```

### SSL not working

```bash
# 1. Are the cert files there?
ls -la docker/ssl/

# 2. Is the ssl.conf include uncommented?
grep -E "^\s*include.*ssl" docker/nginx/nginx.conf

# 3. Is nginx in HTTP-only mode?
docker compose exec nginx nginx -T 2>&1 | grep "listen 443"
```

### Out of disk space

```bash
# Check Docker disk usage
docker system df

# Clean up old images and containers
docker system prune -a -f

# Clean up old volumes (CAREFUL)
docker volume prune
```

### "OOMKilled" containers

The container ran out of memory. Increase the limit in `docker-compose.production.yml`:
```yaml
deploy:
  resources:
    limits:
      memory: 2G
```

Or reduce `memory_limit` in `docker/php/Dockerfile` (currently 512 MB for the app).

### First boot is slow

The MySQL `init.sql` runs on first start (when `db_data` is empty). After that, boots are fast. To re-trigger:
```bash
docker compose down
docker volume rm apsdreamhome_db_data
docker compose up -d
```
**WARNING:** This drops all data.

---

## Zero-Downtime Deploys

The `deploy-to-production.sh` script implements a **rolling restart** strategy:

1. Build the new `app` image (old one keeps serving)
2. Scale the `app` service to 2 replicas
3. Wait for the new instance to be **healthy**
4. Stop the old instance
5. Scale back to 1
6. Restart `websocket` (single instance, brief reconnect window)
7. Reload nginx (zero-downtime `nginx -s reload`)
8. Run health checks

**Caveats:**
- Database schema changes that require a long lock should be run **separately** with a maintenance window.
- WebSocket clients will briefly disconnect during the restart (~5-10 seconds).
- If you have multiple app replicas behind a load balancer, the deploy script scales to 2 first.

To deploy:
```bash
./deploy-to-production.sh
```

Options:
- `--no-cache` – rebuild images from scratch (no layer cache)
- `--skip-migrate` – don't run database migrations
- `BACKUP_BEFORE_DEPLOY=0` – skip the pre-deploy backup

---

## Useful Commands Cheat Sheet

```bash
# Daily operations
make help              # List all targets
make build             # Build all images
make up                # Start stack
make down              # Stop stack
make restart           # Restart everything
make logs              # Follow all logs
make logs-app          # Follow app logs only
make shell             # Shell into app container
make health            # Health status
make stats             # Resource usage

# Database
make migrate           # Run migrations
make seed              # Run seeders
make db-backup         # Backup database to ./backups
make db-restore FILE=path/to/file.sql.gz   # Restore

# Cache
make clear-cache       # Clear application cache
make clear-redis       # Clear Redis cache

# Production
./deploy-to-production.sh            # Full deploy
./deploy-to-production.sh --no-cache # Rebuild from scratch
make prod-up           # Start with production overrides

# Debug
make shell-db          # MySQL CLI
make shell-redis       # Redis CLI
make shell-websocket   # Shell into websocket
```

---

## File Reference

```
apsdreamhome/
├── Dockerfile                              # Multi-stage build (php:8.2-apache)
├── docker-compose.yml                      # Main compose file (5 services)
├── docker-compose.production.yml           # Production overrides (HA, SSL)
├── docker-compose.override.yml.example     # Local dev overrides
├── production.env.example                  # Production env template
├── Makefile                                # Common operations
├── deploy-to-production.sh                 # Rolling-restart deploy script
├── docker-entrypoint.sh                    # App init (DB wait, migrations)
├── .dockerignore                           # Build context excludes
│
├── docker/
│   ├── php/Dockerfile                      # PHP-Apache image
│   ├── websocket/
│   │   ├── Dockerfile                      # WebSocket image (php-cli + Ratchet)
│   │   └── start.sh                        # WebSocket startup script
│   ├── nginx/
│   │   ├── nginx.conf                      # Main nginx config
│   │   ├── conf.d/
│   │   │   ├── app.conf                    # HTTP routing (proxy, websocket upgrade)
│   │   │   ├── ssl.conf                    # HTTPS server block
│   │   │   └── ssl-redirect.conf           # HTTP -> HTTPS redirect
│   │   └── html/50x.html                   # Error page
│   ├── mysql/
│   │   ├── my.cnf                          # MySQL server config
│   │   └── init.sql                        # First-boot DB init
│   ├── redis/
│   │   └── redis.conf                      # Redis config
│   ├── backup/
│   │   └── backup.sh                       # DB backup script
│   ├── cron/
│   │   └── schedule                        # Cron entries for the app
│   └── ssl/                                # TLS certs (drop fullchain.pem + privkey.pem)
│
└── README.DOCKER.md                        # This file
```

---

## Support

For issues:
- Check the [Troubleshooting](#troubleshooting) section above
- View logs: `make logs`
- Get a shell in the app: `make shell`
- Open a GitHub issue

---

**Happy shipping! 🚀**
