# APS Dream Home - Production Deployment Guide

This guide explains how to deploy APS Dream Home using Docker containers in a production environment.

## Prerequisites

- Docker Engine (version 20.10+)
- Docker Compose (version 2.0+)
- Git (to clone the repository)
- A domain name pointing to your server
- SSL certificates (for HTTPS)

## Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/apsdreamhome.git
cd apsdreamhome
```

## Step 2: Configure Environment Variables

Copy the example environment file and adjust the values for your production environment:

```bash
cp .env.example .env
```

Edit the `.env` file with your specific settings:

- `APP_KEY`: Generate a secure key (you can use `php artisan key:generate --show` if Laravel is available, or generate a 32-character random string and base64 encode it)
- `APP_URL`: Your domain URL (e.g., https://apsdreamhome.com)
- Database credentials:
  - `DB_DATABASE`: Database name
  - `DB_USERNAME`: Database user
  - `DB_PASSWORD`: Database password
  - `MYSQL_ROOT_PASSWORD`: Root password for MySQL (in docker-compose.yml)
- Optionally adjust Redis settings if needed

## Step 3: Prepare SSL Certificates

For HTTPS, you need SSL certificates. You have two options:

### Option A: Use Let's Encrypt (Recommended)

1. Install certbot on your host machine
2. Obtain certificates for your domain
3. Place the certificates in the `./ssl` directory:
   - `./ssl/apsdreamhome.crt`
   - `./ssl/apsdreamhome.key`

### Option B: Use Self-Signed Certificates (For Testing Only)

```bash
mkdir -p ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout ssl/apsdreamhome.key \
  -out ssl/apsdreamhome.crt \
  -subj "/C=US/ST=State/L=City/O=Organization/CN=yourdomain.com"
```

## Step 4: Build and Start the Containers

```bash
docker-compose up -d --build
```

This command will:
1. Build the Docker images for the application
2. Start all services (app, web/nginx, db/mysql, redis)
3. Run the entrypoint script which waits for the database and runs migrations

## Step 5: Verify the Deployment

Check that all containers are running:

```bash
docker-compose ps
```

You should see:
- `apsdreamhome_app` (PHP-FPM)
- `apsdreamhome_web` (Nginx)
- `apsdreamhome_db` (MySQL)
- `apsdreamhome_redis` (Redis)

Test the application by visiting your domain in a browser: https://yourdomain.com

## Step 6: Set Up Automatic Renewal for SSL Certificates (If using Let's Encrypt)

If you used certbot, set up a cron job to renew certificates automatically:

```bash
# Edit crontab
crontab -e

# Add line to renew certificates twice daily and reload nginx
0 0,12 * * * certbot renew --quiet && docker-compose exec web nginx -s reload
```

## Step 7: Maintenance and Updates

### Viewing Logs

```bash
# View application logs
docker-compose logs app

# View nginx logs
docker-compose logs web

# View database logs
docker-compose logs db

# Follow logs in real-time
docker-compose logs -f
```

### Rebuilding After Code Changes

If you modify the application code:

```bash
docker-compose up -d --build --no-deps app
```

### Database Backups

```bash
# Backup the database
docker-compose exec db mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > backup.sql

# Restore the database
cat backup.sql | docker-compose exec -i db mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
```

### Updating Docker Images

To pull the latest base images and rebuild:

```bash
docker-compose pull
docker-compose up -d --build
```

## Troubleshooting

### 1. Application Not Connecting to Database

Check that the database container is healthy:
```bash
docker-compose ps db
```

Check the application logs for connection errors:
```bash
docker-compose logs app
```

### 2. Permission Issues

Ensure the storage and bootstrap/cache directories have correct permissions:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/bootstrap/cache
```

### 3. SSL Certificate Problems

Check nginx error logs:
```bash
docker-compose logs web
```

Verify certificates are in the correct location and have correct permissions.

## Security Recommendations

1. Regularly update your Docker images and base images
2. Use a firewall to restrict access to necessary ports (80, 443, and optionally 22 for SSH)
3. Consider setting up a web application firewall (WAF) like ModSecurity
4. Regularly backup your database and application data
5. Monitor logs for suspicious activity
6. Keep your server OS and dependencies up to date

## Directory Structure

```
.
├── Dockerfile                 # Application Dockerfile
├── docker-compose.yml         # Docker Compose configuration
├── nginx/                     # Nginx configuration
│   ├── nginx.conf             # Main nginx config
│   └── conf.d/                # Site-specific configs
├── php/                       # PHP configuration
│   ├── php.ini                # PHP settings
│   └── php-fpm.d/             # PHP-FPM pool configs
├── scripts/                   # Helper scripts
│   └── entrypoint.sh          # Container entrypoint
├── supervisord.conf           # Supervisor configuration
├── .env.example               # Example environment variables
└── DEPLOYMENT.md              # This file
```

## Contact

For issues or questions regarding deployment, please refer to the project documentation or contact the development team.
