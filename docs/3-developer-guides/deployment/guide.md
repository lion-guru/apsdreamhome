# Deployment Guide

<!-- PHP Configuration Reference Links -->
[error_log]: https://www.php.net/manual/en/errorfunc.configuration.php#ini.error-log
[memory_limit]: https://www.php.net/manual/en/ini.core.php#ini.memory-limit
[max_execution_time]: https://www.php.net/manual/en/info.configuration.php#ini.max-execution-time
[max_input_time]: https://www.php.net/manual/en/info.configuration.php#ini.max-input-time
[max_input_vars]: https://www.php.net/manual/en/info.configuration.php#ini.max-input-vars
[post_max_size]: https://www.php.net/manual/en/ini.core.php#ini.post-max-size
[upload_max_filesize]: https://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize
[max_file_uploads]: https://www.php.net/manual/en/ini.core.php#ini.max-file-uploads
[session.save_handler]: https://www.php.net/manual/en/session.configuration.php#ini.session.save-handler
[session.save_path]: https://www.php.net/manual/en/session.configuration.php#ini.session.save-path
[session.gc_maxlifetime]: https://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime
[session.cookie_lifetime]: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime
[session.cookie_secure]: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-secure
[session.cookie_httponly]: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-httponly
[session.cookie_samesite]: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-samesite
[disable_functions]: https://www.php.net/manual/en/ini.core.php#ini.disable-functions
[expose_php]: https://www.php.net/manual/en/ini.core.php#ini.expose-php
[allow_url_fopen]: https://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen
[allow_url_include]: https://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-include
[error_reporting]: https://www.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting
[display_errors]: https://www.php.net/manual/en/errorfunc.configuration.php#ini.display-errors
[display_startup_errors]: https://www.php.net/manual/en/errorfunc.configuration.php#ini.display-startup-errors
[log_errors]: https://www.php.net/manual/en/errorfunc.configuration.php#ini.log-errors
[ignore_repeated_errors]: https://www.php.net/manual/en/errorfunc.configuration.php#ini.ignore-repeated-errors
[ignore_repeated_source]: https://www.php.net/manual/en/errorfunc.configuration.php#ini.ignore-repeated-source
[report_memleaks]: https://www.php.net/manual/en/errorfunc.configuration.php#ini.report-memleaks
[track_errors]: https://www.php.net/manual/en/errorfunc.configuration.php#ini.track-errors
[syslog.facility]: https://www.php.net/manual/en/network.configuration.php#ini.syslog.facility
[syslog.filter]: https://www.php.net/manual/en/network.configuration.php#ini.syslog.filter
[realpath_cache_size]: https://www.php.net/manual/en/ini.core.php#ini.realpath-cache-size
[realpath_cache_ttl]: https://www.php.net/manual/en/ini.core.php#ini.realpath-cache-ttl
[opcache.enable]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.enable
[opcache.memory_consumption]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.memory-consumption
[opcache.interned_strings_buffer]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.interned-strings-buffer
[opcache.max_accelerated_files]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.max-accelerated-files
[opcache.validate_timestamps]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.validate-timestamps
[opcache.save_comments]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.save-comments
[opcache.fast_shutdown]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.fast-shutdown
[opcache.enable_cli]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.enable-cli
[opcache.jit_buffer_size]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.jit-buffer-size
[opcache.jit]: https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.jit
[upload_tmp_dir]: https://www.php.net/manual/en/ini.core.php#ini.upload-tmp-dir
[sys_temp_dir]: https://www.php.net/manual/en/ini.core.php#ini.sys-temp-dir

# Deployment Guide

## Overview

This guide provides comprehensive instructions for deploying the APS Dream Home application across different environments.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Environment Setup](#environment-setup)
3. [Deployment Types](#deployment-types)
4. [Configuration Management](#configuration-management)
5. [Database Setup](#database-setup)
6. [Web Server Configuration](#web-server-configuration)
7. [SSL/TLS Setup](#ssltls-setup)
8. [CI/CD Pipeline](#cicd-pipeline)
9. [Monitoring and Logging](#monitoring-and-logging)
10. [Scaling](#scaling)
11. [Backup and Recovery](#backup-and-recovery)
12. [Security Hardening](#security-hardening)
13. [Troubleshooting](#troubleshooting)
14. [Backup and Recovery](#backup-and-recovery)
15. [Security Hardening](#security-hardening)

## Prerequisites

### Development Environment
- Git
- PHP 8.0+
- Composer 2.0+
- Node.js 16.x+
- NPM 8.x+
- MySQL 5.7+ or MariaDB 10.3+
- Redis (for caching/queues)

### Production Server
- Linux server (Ubuntu 20.04/22.04 recommended)
- Web server (Nginx/Apache)
- PHP 8.0+ with required extensions
- MySQL/MariaDB
- Redis
- Supervisor (for queue workers)
- Certbot (for SSL certificates)

## Environment Setup

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/aps-dreamhome.git
cd aps-dreamhome
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install --optimize-autoloader --no-dev

# Frontend dependencies
npm install
npm run production
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Set File Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Deployment Types

### 1. Shared Hosting (cPanel)

1. Upload files via FTP/SFTP
2. Set document root to `public`
3. Create database and user
4. Update `.env` with database credentials
5. Run migrations: `php artisan migrate --force`
6. Set up cron job: `* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1`

### 2. VPS/Cloud (Manual)

1. Set up server (Ubuntu 20.04/22.04)
2. Install LEMP stack
3. Configure web server (Nginx/Apache)
4. Set up database
5. Deploy code
6. Configure SSL
7. Set up queues and scheduling

### 3. Docker

```bash
# Build and start containers
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate --seed

# Install NPM dependencies
docker-compose exec app npm install
docker-compose exec app npm run production
```

### 4. Platform as a Service (PaaS)

#### Heroku

```bash
# Set up Heroku CLI
heroku login

# Create new app
heroku create aps-dreamhome

# Set buildpacks
heroku buildpacks:add heroku/php
heroku buildpacks:add heroku/nodejs

# Set environment variables
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_KEY=$(php artisan --no-ansi key:generate --show)

# Add add-ons
heroku addons:create heroku-postgresql:hobby-dev
heroku addons:create heroku-redis:hobby-dev

# Deploy
git push heroku main

# Run migrations
heroku run php artisan migrate --force
```

## Configuration Management

### Environment Variables

Required `.env` variables:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aps_dreamhome
DB_USERNAME=dbuser
DB_PASSWORD=dbpassword

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Configuration Caching

```bash
# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Database Setup

### 1. Create Database

```sql
CREATE DATABASE aps_dreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dbuser'@'localhost' IDENTIFIED BY 'dbpassword';
GRANT ALL PRIVILEGES ON aps_dreamhome.* TO 'dbuser'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Run Migrations

```bash
php artisan migrate --force
```

### 3. Seed Initial Data

```bash
php artisan db:seed --class=DatabaseSeeder
```

## Database Maintenance & Optimization

### 1. Regular Maintenance Tasks

#### 1.1 Database Optimization Script

Create a maintenance script at `/usr/local/bin/mysql-maintenance.sh`:

```bash
#!/bin/bash

# Configuration
DB_USER="dbuser"
DB_PASS="your_secure_password"
DB_NAME="aps_dreamhome"
BACKUP_DIR="/var/backups/mysql"
LOG_FILE="/var/log/mysql-maintenance.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "Starting database maintenance..."

# Optimize all tables
log "Optimizing tables..."
mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME; SHOW TABLES" | grep -v "^Tables_in_" | while read -r table; do
    log "Optimizing table: $table"
    mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME; OPTIMIZE TABLE \`$table\`"
done

# Analyze tables for better query optimization
log "Analyzing tables..."
mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME; ANALYZE TABLE *"

# Check and repair tables if needed
log "Checking and repairing tables..."
mysqlcheck -u "$DB_USER" -p"$DB_PASS" --auto-repair --check "$DB_NAME"

# Clean up old backups (keep last 30 days)
log "Cleaning up old backups..."
find "$BACKUP_DIR" -type f -name "*.sql.gz" -mtime +30 -delete

log "Database maintenance completed successfully"
```

Make it executable:
```bash
chmod +x /usr/local/bin/mysql-maintenance.sh
```

#### 1.2 Scheduled Maintenance with Cron

Add to crontab (`crontab -e`):

```
# Run database maintenance weekly on Sunday at 2 AM
0 2 * * 0 /usr/local/bin/mysql-maintenance.sh
```

### 2. Performance Optimization

#### 2.1 MySQL Configuration Tuning

Edit `/etc/mysql/my.cnf` or `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
# Buffer pool size (50-70% of available RAM)
innodb_buffer_pool_size = 4G

# Log file size (25% of buffer pool size)
innodb_log_file_size = 1G
innodb_log_buffer_size = 64M

# I/O capacity settings
innodb_io_capacity = 2000
innodb_io_capacity_max = 4000

# Concurrency settings
innodb_thread_concurrency = 0
innodb_read_io_threads = 8
innodb_write_io_threads = 8

# Flush method
innodb_flush_method = O_DIRECT

# Buffer pool instances
innodb_buffer_pool_instances = 8

# Query cache (disable for MySQL 8.0+)
# query_cache_type = 0
# query_cache_size = 0

# Table open cache
table_open_cache = 4000
table_definition_cache = 2000

# Connection settings
max_connections = 200
wait_timeout = 300
interactive_timeout = 300

# Slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/mysql-slow.log
long_query_time = 2
log_queries_not_using_indexes = 1
```

#### 2.2 Index Optimization

Create a script at `/usr/local/bin/optimize-indexes.sh`:

```bash
#!/bin/bash

DB_USER="dbuser"
DB_PASS="your_secure_password"
DB_NAME="aps_dreamhome"
LOG_FILE="/var/log/mysql-optimize-indexes.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "Starting index optimization..."

# Find tables that might need indexes
mysql -u "$DB_USER" -p"$DB_PASS" -e "
    SELECT CONCAT('ALTER TABLE `', table_schema, '`.`', table_name, '` 
    ADD INDEX `idx_', column_name, '` (`', column_name, '`);') AS query
    FROM information_schema.columns
    WHERE table_schema = '$DB_NAME'
    AND column_name IN ('status', 'created_at', 'updated_at', 'user_id', 'property_id')
    AND table_name NOT IN (
        SELECT table_name
        FROM information_schema.statistics
        WHERE table_schema = '$DB_NAME'
        AND column_name IN ('status', 'created_at', 'updated_at', 'user_id', 'property_id')
        GROUP BY table_name, index_name
        HAVING COUNT(*) = 1
    )
    GROUP BY table_schema, table_name, column_name;
" | grep -v "query" > /tmp/add_indexes.sql

# Execute the generated SQL
if [ -s /tmp/add_indexes.sql ]; then
    log "Adding recommended indexes..."
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /tmp/add_indexes.sql
    rm /tmp/add_indexes.sql
else
    log "No new indexes needed."
fi

log "Index optimization completed"
```

### 3. Backup Strategy

#### 3.1 Automated Backups

Create `/usr/local/bin/mysql-backup.sh`:

```bash
#!/bin/bash

# Configuration
DB_USER="dbuser"
DB_PASS="your_secure_password"
DB_NAME="aps_dreamhome"
BACKUP_DIR="/var/backups/mysql"
DATE=$(date +"%Y%m%d_%H%M%S")
KEEP_DAYS=30

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Create backup
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_${DATE}.sql.gz"
mysqldump -u"$DB_USER" -p"$DB_PASS" --single-transaction --routines --triggers "$DB_NAME" | gzip > "$BACKUP_FILE"

# Verify backup
if [ ${PIPESTATUS[0]} -eq 0 ]; then
    echo "Backup created: $BACKUP_FILE"
    
    # Clean up old backups
    find "$BACKUP_DIR" -name "${DB_NAME}_*.sql.gz" -type f -mtime +$KEEP_DAYS -delete
else
    echo "Backup failed!" >&2
    exit 1
fi
```

#### 3.2 Backup Rotation with Logrotate

Create `/etc/logrotate.d/mysql-backups`:

```
/var/www/aps-dreamhome/storage/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
    create 640 root root
}
```

### 4. Monitoring and Alerts

#### 4.1 Database Health Check

Create `/usr/local/bin/check-db-health.sh`:

```bash
#!/bin/bash

# Configuration
DB_USER="dbuser"
DB_PASS="your_secure_password"
DB_NAME="aps_dreamhome"
ALERT_EMAIL="admin@example.com"
LOG_FILE="/var/log/mysql-health.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "Starting database health check..."

# Check MySQL service status
if ! systemctl is-active --quiet mysql; then
    log "ERROR: MySQL service is not running!"
    echo "MySQL service is not running" | mail -s "MySQL Service Down" "$ALERT_EMAIL"
    exit 1
fi

# Check for long-running queries
LONG_QUERIES=$(mysql -u "$DB_USER" -p"$DB_PASS" -e "
    SELECT * FROM information_schema.processlist 
    WHERE TIME > 300 
    AND USER NOT IN ('root', 'system user')
    AND COMMAND NOT IN ('Sleep')" 2>&1)

if [ -n "$LONG_QUERIES" ]; then
    log "WARNING: Long running queries detected:"
    echo "$LONG_QUERIES" | tee -a "$LOG_FILE"
    echo "$LONG_QUERIES" | mail -s "Long Running Queries Detected" "$ALERT_EMAIL"
fi

# Check table status
ERROR_TABLES=$(mysql -u "$DB_USER" -p"$DB_PASS" -e "
    SELECT table_name, engine, row_format, table_rows, 
           data_length, index_length, 
           round(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
    FROM information_schema.tables 
    WHERE table_schema = '$DB_NAME' 
    AND engine != 'InnoDB'" 2>&1)

if [ -n "$ERROR_TABLES" ]; then
    log "WARNING: Non-InnoDB tables found:"
    echo "$ERROR_TABLES" | tee -a "$LOG_FILE"
fi

log "Database health check completed"
```

#### 4.2 Set Up Monitoring with Prometheus

1. Install and configure MySQL Exporter:

```bash
# Download and install
wget https://github.com/prometheus/mysqld_exporter/releases/download/v0.14.0/mysqld_exporter-0.14.0.linux-amd64.tar.gz
tar xvfz mysqld_exporter-0.14.0.linux-amd64.tar.gz
sudo cp mysqld_exporter-0.14.0.linux-amd64/mysqld_exporter /usr/local/bin/

# Create systemd service
sudo nano /etc/systemd/system/mysqld_exporter.service
```

Add to `/etc/systemd/system/mysqld_exporter.service`:

```ini
[Unit]
Description=Prometheus MySQL Exporter
After=network.target

[Service]
User=mysql
group=mysql
Environment="DATA_SOURCE_NAME=dbuser:your_secure_password@(localhost:3306)/"
ExecStart=/usr/local/bin/mysqld_exporter \
    --collect.auto_increment_columns \
    --collect.binlog_size \
    --collect.global_status \
    --collect.global_variables \
    --collect.info_schema.processlist \
    --collect.info_schema.innodb_metrics \
    --collect.info_schema.tablestats \
    --collect.info_schema.tables \
    --collect.info_schema.userstats
Restart=always

[Install]
WantedBy=multi-user.target
```

#### 4.3 Add to Prometheus config (`/etc/prometheus/prometheus.yml`):

```yaml
scrape_configs:
  - job_name: 'mysql'
    static_configs:
      - targets: ['localhost:9104']
```

## Monitoring, Logging, and Alerting

### 1. Application Performance Monitoring (APM)

#### 1.1 Install and Configure New Relic

```bash
# Add New Relic repository
curl -s https://packagecloud.io/install/repositories/varnishcache/varnish72/script.deb.sh | sudo bash

# Install PHP agent
sudo apt-get install newrelic-php5

# Run configuration
sudo newrelic-install install

# Configure New Relic
sudo nano /etc/php/8.1/fpm/conf.d/newrelic.ini
```

Update New Relic configuration:

```ini
newrelic.license = "YOUR_LICENSE_KEY"
newrelic.appname = "APS Dream Home (Production)"
newrelic.daemon.address = "/tmp/.newrelic.sock"
newrelic.daemon.port = "/tmp"
newrelic.daemon.dont_launch = 3
newrelic.capture_params = true
newrelic.error_collector.enabled = true
newrelic.browser_monitoring.auto_instrument = true
newrelic.transaction_tracer.detail = 1
newrelic.transaction_tracer.slow_sql = 1
newrelic.transaction_tracer.explain_threshold = 0.5
newrelic.transaction_tracer.record_sql = "obfuscated"
newrelic.framework = "laravel"
```

#### 1.2 Configure Laravel for New Relic

Add to `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\App;

public function boot()
{
    if (App::environment('production')) {
        // Set transaction name for better tracking
        if (extension_loaded('newrelic')) {
            \newrelic_name_transaction(
                request()->route() ? request()->route()->getName() : request()->path()
            );
        }
    }
}
```

### 2. Centralized Logging with ELK Stack

#### 2.1 Install Elasticsearch, Logstash, and Kibana

```bash
# Add Elastic repository
wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee -a /etc/apt/sources.list.d/elastic-7.x.list
sudo apt update

# Install Elasticsearch
sudo apt install elasticsearch
sudo systemctl enable elasticsearch
sudo systemctl start elasticsearch

# Install Logstash
sudo apt install logstash
sudo systemctl enable logstash

# Install Kibana
sudo apt install kibana
sudo systemctl enable kibana
sudo systemctl start kibana
```

#### 2.2 Configure Logstash

Create `/etc/logstash/conf.d/01-laravel.conf`:

```ruby
input {
  file {
    path => "/var/www/aps-dreamhome/storage/logs/*.log"
    type => "laravel"
    codec => json {}
    start_position => "beginning"
    sincedb_path => "/dev/null"
  }
}

filter {
  if [type] == "laravel" {
    # Parse timestamp
    date {
      match => [ "timestamp" , "yyyy-MM-dd HH:mm:ss" ]
      target => "@timestamp"
    }
    
    # Add geoip for IP addresses
    if [context] and [context][ip] {
      geoip {
        source => "[context][ip]"
        target => "geoip"
      }
    }
    
    # Clean up fields
    mutate {
      remove_field => ["@version", "host"]
    }
  }
}

output {
  elasticsearch {
    hosts => ["localhost:9200"]
    index => "laravel-logs-%{+YYYY.MM.dd}"
  }
}
```

#### 2.3 Configure Laravel Logging

Update `config/logging.php`:

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => 'debug',
    'days' => 14,
    'formatter' => \Monolog\Formatter\JsonFormatter::class,
    'formatter_with' => [
        'includeStacktraces' => true,
    ],
],

'slack' => [
    'driver' => 'slack',
    'url' => env('LOG_SLACK_WEBHOOK_URL'),
    'username' => 'Laravel Log',
    'emoji' => ':boom:',
    'level' => 'critical',
],
```

### 3. Real-time Application Monitoring with Prometheus and Grafana

#### 3.1 Install Prometheus

```bash
# Create system user
sudo useradd --no-create-home --shell /bin/false prometheus

# Create directories
sudo mkdir /etc/prometheus
sudo mkdir /var/lib/prometheus

# Download and install
wget https://github.com/prometheus/prometheus/releases/download/v2.30.3/prometheus-2.30.3.linux-amd64.tar.gz
tar -xvf prometheus-2.30.3.linux-amd64.tar.gz

# Move binaries
sudo cp prometheus-2.30.3.linux-amd64/prometheus /usr/local/bin/
sudo cp prometheus-2.30.3.linux-amd64/promtool /usr/local/bin/

# Set ownership
sudo chown prometheus:prometheus /usr/local/bin/prometheus
sudo chown prometheus:prometheus /usr/local/bin/promtool

# Create systemd service
sudo nano /etc/systemd/system/prometheus.service
```

Add to `/etc/systemd/system/prometheus.service`:

```ini
[Unit]
Description=Prometheus
Wants=network-online.target
After=network-online.target

[Service]
User=prometheus
Group=prometheus
Type=simple
ExecStart=/usr/local/bin/prometheus \
    --config.file /etc/prometheus/prometheus.yml \
    --storage.tsdb.path /var/lib/prometheus/ \
    --web.console.templates=/etc/prometheus/consoles \
    --web.console.libraries=/etc/prometheus/console_libraries

[Install]
WantedBy=multi-user.target
```

#### 3.2 Install Node Exporter

```bash
# Download and install
wget https://github.com/prometheus/node_exporter/releases/download/v1.2.2/node_exporter-1.2.2.linux-amd64.tar.gz
tar -xvf node_exporter-1.2.2.linux-amd64.tar.gz
sudo cp node_exporter-1.2.2.linux-amd64/node_exporter /usr/local/bin/

# Create systemd service
sudo nano /etc/systemd/system/node_exporter.service
```

Add to `/etc/systemd/system/node_exporter.service`:

```ini
[Unit]
Description=Node Exporter
After=network.target

[Service]
User=prometheus
ExecStart=/usr/local/bin/node_exporter

[Install]
WantedBy=default.target
```

#### 3.3 Install Grafana

```bash
# Add repository
wget -q https://packagecloud.io/install/repositories/varnishcache/varnish72/script.deb.sh | sudo bash

# Install Grafana
sudo apt install grafana

# Enable and start
sudo systemctl enable grafana-server
sudo systemctl start grafana-server
```

### 4. Error Tracking with Sentry

#### 4.1 Install and Configure Sentry

```bash
# Install Sentry CLI
curl -sL https://sentry.io/get-cli/ | bash

# Create release
SENTRY_AUTH_TOKEN=your_auth_token \
SENTRY_ORG=your_org \
sentry-cli releases new -p your_project_name v1.0.0
```

#### 4.2 Configure Laravel for Sentry

Install the SDK:

```bash
composer require sentry/sentry-laravel
```

Publish configuration:

```bash
php artisan sentry:publish --dsn=https://yourkey@o0.ingest.sentry.io/0
```

Update `.env`:

```ini
SENTRY_LARAVEL_DSN=https://yourkey@o0.ingest.sentry.io/0
SENTRY_TRACES_SAMPLE_RATE=0.5
```

### 5. Uptime Monitoring with Uptime Kuma

#### 5.1 Install Docker

```bash
# Install Docker
sudo apt update
sudo apt install docker.io docker-compose
sudo systemctl enable --now docker

# Add user to docker group
sudo usermod -aG docker $USER
```

#### 5.2 Run Uptime Kuma

```bash
# Create volume
mkdir -p ~/uptime-kuma

# Run container
docker run -d --restart=always -p 3001:3001 -v ~/uptime-kuma:/app/data --name uptime-kuma louislam/uptime-kuma:1
```

Access at `http://your-server-ip:3001` and set up monitoring for:
- HTTP endpoints
- SSL certificate expiration
- Database connections
- API endpoints
- Response time monitoring

### 6. Security Monitoring with Wazuh

#### 6.1 Install Wazuh Manager

```bash
# Install prerequisites
sudo apt install -y curl apt-transport-https lsb-release gnupg2

# Add repository
curl -s https://packages.wazuh.com/key/GPG-KEY-WAZUH | sudo apt-key add -
echo "deb https://packages.wazuh.com/4.x/apt/ stable main" | sudo tee /etc/apt/sources.list.d/wazuh.list

# Install Wazuh
sudo apt update
sudo apt install wazuh-manager

# Start service
sudo systemctl daemon-reload
sudo systemctl enable wazuh-manager
sudo systemctl start wazuh-manager
```

#### 6.2 Install Wazuh Agent

```bash
# Install agent
sudo apt install wazuh-agent

# Configure agent
sudo nano /var/ossec/etc/ossec.conf

# Set manager IP and register agent
sudo /var/ossec/bin/agent-auth -m <MANAGER_IP> -A `hostname`

# Start agent
sudo systemctl daemon-reload
sudo systemctl enable wazuh-agent
sudo systemctl start wazuh-agent
```

## Web Server Configuration

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/aps-dreamhome/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/aps-dreamhome/public

    <Directory /var/www/aps-dreamhome/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## SSL/TLS Setup

### Using Let's Encrypt with Certbot

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

## CI/CD Pipeline

### GitHub Actions

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, xml, ctype, json, tokenizer, pdo_mysql, fileinfo, gd, imagick
    
    - name: Install Dependencies
      run: |
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
        npm ci
        npm run production
    
    - name: Upload to Server
      uses: appleboy/scp-action@master
      with:
        host: ${{ secrets.SSH_HOST }}
        username: ${{ secrets.SSH_USERNAME }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        source: "*"
        target: /var/www/aps-dreamhome
    
    - name: Run Migrations
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SSH_HOST }}
        username: ${{ secrets.SSH_USERNAME }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        script: |
          cd /var/www/aps-dreamhome
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
```

## Monitoring and Logging

### Laravel Telescope

1. Install Telescope:
   ```bash
   composer require laravel/telescope
   php artisan telescope:install
   php artisan migrate
   ```

2. Configure in `.env`:
   ```
   TELESCOPE_ENABLED=true
   ```

### Log Management

1. Configure log rotation in `/etc/logrotate.d/aps-dreamhome`:
   ```
   /var/www/aps-dreamhome/storage/logs/*.log {
       daily
       missingok
       rotate 14
       compress
       delaycompress
       notifempty
       create 0640 www-data www-data
       sharedscripts
       postrotate
           kill -USR1 `cat /run/php/php8.1-fpm.pid 2>/dev/null` 2>/dev/null || true
       endscript
   }
   ```

## Scaling

### Database Replication

1. Set up master-slave replication
2. Configure in `.env`:
   ```
   DB_READ_HOST=slave-db-host
   DB_WRITE_HOST=master-db-host
   ```

### Redis Clustering

1. Set up Redis cluster
2. Configure in `.env`:
   ```
   REDIS_CLIENT=phpredis
   REDIS_CLUSTER=redis
   REDIS_PREFIX=aps_dreamhome_
   
   REDIS_HOST=redis1,redis2,redis3
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

### Queue Workers

1. Configure Supervisor:
   ```ini
   [program:laravel-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/aps-dreamhome/artisan queue:work --sleep=3 --tries=3 --max-time=3600
   autostart=true
   autorestart=true
   stopasgroup=true
   killasgroup=true
   user=www-data
   numprocs=8
   redirect_stderr=true
   stdout_logfile=/var/log/worker.log

### 1. Server Hardening

#### 1.1 Operating System Security

```bash
# Update and upgrade all packages
sudo apt update && sudo apt upgrade -y

# Install essential security packages
sudo apt install -y fail2ban ufw unattended-upgrades apt-listchanges

# Configure automatic security updates
sudo dpkg-reconfigure -plow unattended-upgrades

# Enable and configure UFW (Uncomplicated Firewall)
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw enable

# Harden SSH configuration
sudo sed -i 's/#PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
sudo sed -i 's/PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
sudo sed -i 's/X11Forwarding yes/X11Forwarding no/' /etc/ssh/sshd_config
sudo systemctl restart sshd
```

#### 1.2 PHP Security

Edit `/etc/php/8.1/fpm/php.ini`:

```ini
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
allow_url_fopen = Off
allow_url_include = Off
post_max_size = 20M
upload_max_filesize = 20M
max_execution_time = 300
max_input_time = 60
memory_limit = 256M
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = "Lax"
```

### 2. Web Server Security

#### 2.1 Nginx Security Headers

Add to your Nginx server block:

```nginx
# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' https:; frame-ancestors 'self'; form-action 'self';" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

# Prevent MIME type sniffing
add_header X-Content-Type-Options nosniff;

# Prevent clickjacking
add_header X-Frame-Options "SAMEORIGIN";

# Enable XSS protection
add_header X-XSS-Protection "1; mode=block";

# Disable server tokens
server_tokens off;

# Disable directory listing
autoindex off;

# Disable access to hidden files
location ~ /\.(?!well-known) {
    deny all;
}

# Restrict HTTP methods
if ($request_method !~ ^(GET|HEAD|POST|PUT|DELETE|PATCH)$) {
    return 405;
}
```

#### 2.2 Rate Limiting

Add to Nginx configuration:

```nginx
# Rate limiting
limit_req_zone $binary_remote_addr zone=one:10m rate=10r/s;
limit_conn_zone $binary_remote_addr zone=addr:10m;

server {
    # ... other server config ...

    # Apply rate limiting
    limit_req zone=one burst=20 nodelay;
    limit_conn addr 10;
    
    # Login page rate limiting
    location = /login {
        limit_req zone=one burst=5;
        # ... other location config ...
    }
}
```

### 3. Application Security

#### 3.1 Laravel Security Features

1. **Enable Security Middleware**

In `app/Http/Kernel.php`:

```php
protected $middleware = [
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \App\Http\Middleware\HttpsProtocol::class, // Force HTTPS
    \App\Http\Middleware\SecurityHeaders::class, // Custom security headers
];

protected $middlewareGroups = [
    'web' => [
        // ...
        \App\Http\Middleware\XSSProtection::class,
        \App\Http\Middleware\FrameOptions::class,
    ],
    'api' => [
        'throttle:60,1',
        'bindings',
    ],
];
```

2. **Create Custom Security Middleware**

Create `app/Http/Middleware/SecurityHeaders.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), midi=(), sync-xhr=(), microphone=(), camera=(), magnetometer=(), gyroscope=()' always;
        
        // Only add CSP header if not already set
        if (!$response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https:;");
        }

        return $response;
    }
}
```

#### 3.2 Database Security

1. **Use Prepared Statements**

Always use Laravel's query builder or Eloquent ORM to prevent SQL injection:

```php
// Secure way
$users = DB::table('users')
    ->where('active', 1)
    ->get();

// Insecure way (vulnerable to SQL injection)
$users = DB::select(DB::raw("SELECT * FROM users WHERE active = " . $request->input('active')));
```

2. **Database User Privileges**

Create a dedicated database user with least privileges:

```sql
-- Create a new user
CREATE USER 'aps_dreamhome'@'%' IDENTIFIED BY 'strong-password-here';

-- Grant minimum required privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON aps_dreamhome.* TO 'aps_dreamhome'@'%';

-- Revoke unnecessary privileges
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'aps_dreamhome'@'%';

-- Apply changes
FLUSH PRIVILEGES;
```

### 4. Authentication & Authorization

#### 4.1 Password Policies

1. **Enforce Strong Passwords**

In `app/Http/Controllers/Auth/RegisterController.php`:

```php
protected function validator(array $data)
{
    return Validator::make($data, [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => [
            'required',
            'string',
            'min:12',
            'confirmed',
            'regex:/[a-z]/',      // must contain at least one lowercase letter
            'regex:/[A-Z]/',      // must contain at least one uppercase letter
            'regex:/[0-9]/',      // must contain at least one digit
            'regex:/[@$!%*#?&]/', // must contain a special character
        ],
    ]);
}
```

2. **Enable Two-Factor Authentication**

Install Laravel 2FA package:
```bash
composer require pragmarx/google2fa-laravel
```

Add to `config/auth.php`:
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

### 5. API Security

#### 5.1 API Rate Limiting

In `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    // ...
    'api' => [
        'throttle:60,1',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

For specific endpoints in `routes/api.php`:

```php
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', 'Auth\LoginController@login');
    Route::post('/register', 'Auth\RegisterController@register');
    Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'Auth\ResetPasswordController@reset');
});
```

#### 5.2 API Authentication

1. **Laravel Sanctum**

Install and configure Laravel Sanctum:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Update `.env` file:

```ini
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Optional: Use different Redis databases for different purposes
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3
```

#### 5.3 API Security Headers

Add to `app/Http/Middleware/SecurityHeaders.php`:

```php
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-XSS-Protection', '1; mode=block');
$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' https:;");
```

### 6. Security Monitoring & Logging

#### 6.1 Laravel Security Packages

1. **Laravel Security**

```bash
composer require beyondcode/laravel-security-checker
```

2. **Laravel Security Headers**

```bash
composer require bezhansalleh/laravel-security-headers
```

3. **Laravel Security Check**

```bash
composer require enshrined/svg-sanitize
```

#### 6.2 Logging Configuration

In `config/logging.php`:

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => 'debug',
    'days' => 14,
    'permission' => 0664,
],

'slack' => [
    'driver' => 'slack',
    'url' => env('LOG_SLACK_WEBHOOK_URL'),
    'username' => 'Laravel Log',
    'emoji' => ':boom:',
    'level' => 'critical',
],
```

### 7. Regular Security Audits

#### 7.1 Dependency Scanning

1. **PHP Security Checker**

```bash
composer require enlightn/security-checker
php artisan security:check
```

2. **Laravel Security Check**

```bash
composer require enlightn/security-checker
php artisan security:check
```

#### 7.2 Automated Security Testing

1. **OWASP ZAP Integration**

```bash
docker run -v $(pwd):/zap/wrk/:rw -t owasp/zap2docker-stable zap-baseline.py \
    -t https://your-staging-site.com \
    -r testreport.html \
    -x report_xml.xml
```

2. **Laravel Dusk Tests**

Create security tests in `tests/Browser/SecurityTest.php`:

```php
public function test_xss_protection()
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/search?q=<script>alert(1)</script>')
                ->assertDontSee('<script>');
    });
}
```

### 8. Incident Response Plan

1. **Security Incident Response Team (SIRT)**
   - Define team members and contact information
   - Establish communication channels
   - Document escalation procedures

2. **Incident Classification**
   - Low: Minor security issues with minimal impact
   - Medium: Issues that could lead to data exposure
   - High: Active exploitation or data breach

3. **Response Procedures**
   - Immediate containment
   - Evidence preservation
   - Root cause analysis
   - Remediation
   - Post-incident review

### 9. Compliance & Best Practices

#### 9.1 OWASP Top 10

Ensure protection against:
1. Injection
2. Broken Authentication
3. Sensitive Data Exposure
4. XML External Entities (XXE)
5. Broken Access Control
6. Security Misconfiguration
7. Cross-Site Scripting (XSS)
8. Insecure Deserialization
9. Using Components with Known Vulnerabilities
10. Insufficient Logging & Monitoring

#### 9.2 GDPR Compliance

1. **Data Protection**
   - Encrypt personal data at rest and in transit
   - Implement data retention policies
   - Provide data export/delete functionality

2. **Privacy Policy**
   - Document data collection practices
   - Obtain explicit consent
   - Provide opt-out mechanisms

## File Permissions

```bash
# Set proper permissions
find /var/www/aps-dreamhome -type d -exec chmod 755 {} \;
find /var/www/aps-dreamhome -type f -exec chmod 644 {} \;

# Set ownership
chown -R www-data:www-data /var/www/aps-dreamhome
chmod -R 775 /var/www/aps-dreamhome/storage
chmod -R 775 /var/www/aps-dreamhome/bootstrap/cache
```

### Security Headers

Add to Nginx config:

```nginx
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' https:; frame-ancestors 'self'; form-action 'self';" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
```

### Rate Limiting

In `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \App\Http\Middleware\RateLimit::class,
    ],
    'api' => [
        'throttle:60,1',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

## Backup and Recovery

### Database Backups

#### Automated MySQL Backup Script

```bash
#!/bin/bash

# Configuration
DB_USER="your_db_user"
DB_PASS="your_db_password"
DB_NAME="your_database_name"
BACKUP_DIR="/path/to/backups"
DATE=$(date +%Y%m%d_%H%M%S)
KEEP_DAYS=30

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Create backup
mysqldump -u"$DB_USER" -p"$DB_PASS" --single-transaction --routines --triggers "$DB_NAME" | gzip > "${BACKUP_DIR}/${DB_NAME}_${DATE}.sql.gz"

# Clean up old backups
find "$BACKUP_DIR" -type f -name "${DB_NAME}_*.sql.gz" -mtime +${KEEP_DAYS} -delete
```

### File System Backups

#### Backup Script for Application Files

```bash
#!/bin/bash

# Configuration
APP_DIR="/var/www/aps-dreamhome"
BACKUP_DIR="/path/to/backups/app"
DATE=$(date +%Y%m%d_%H%M%S)
KEEP_DAYS=30

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Create backup
tar -czf "${BACKUP_DIR}/app_${DATE}.tar.gz" -C "$(dirname "$APP_DIR")" "$(basename "$APP_DIR")"

# Clean up old backups
find "$BACKUP_DIR" -type f -name "app_*.tar.gz" -mtime +${KEEP_DAYS} -delete
```

### Disaster Recovery

1. **Database Recovery**:
   ```bash
   gunzip < backup_file.sql.gz | mysql -u username -p database_name
   ```

2. **Application Recovery**:
   ```bash
   tar -xzf app_backup.tar.gz -C /path/to/restore
   ```

## Security Hardening

### Server Hardening

1. **Firewall Configuration** (UFW example):
   ```bash
   sudo ufw allow ssh
   sudo ufw allow http
   sudo ufw allow https
   sudo ufw enable
   ```

2. **SSH Hardening**:
   ```/etc/ssh/sshd_config
   Port 2222
   PermitRootLogin no
   PasswordAuthentication no
   X11Forwarding no
   AllowUsers deploy
   ```

### Application Security

1. **Laravel Security Settings**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=your_secure_key
   
   SESSION_DRIVER=file
   SESSION_SECURE_COOKIE=true
   
   # Force HTTPS
   APP_URL=https://yourdomain.com
   FORCE_HTTPS=true
   ```

2. **Content Security Policy (CSP)**:
   ```php
   // In a middleware
   public function handle($request, $next)
   {
       $response = $next($request);
       
       $response->headers->set('Content-Security-Policy', \
           "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; " .
           "style-src 'self' 'unsafe-inline' https:; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' data: https:; " .
           "connect-src 'self' https:; " .
           "frame-ancestors 'self';"
       );
       
       return $response;
   }
   ```

### Database Security

1. **MySQL/MariaDB Hardening**:
   ```sql
   -- Remove anonymous users
   DELETE FROM mysql.user WHERE User='';
   
   -- Remove test database
   DROP DATABASE IF EXISTS test;
   DELETE FROM mysql.db WHERE Db='test' OR Db='test\_%';
   
   -- Reload privileges
   FLUSH PRIVILEGES;
   ```

2. **Application Database User**:
   ```sql
   CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'strong_password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON app_database.* TO 'app_user'@'localhost';
   ```

## Troubleshooting

### Common Issues

#### 500 Server Error
1. Check storage permissions:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

2. Clear caches:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

#### Database Connection Issues
1. Verify `.env` database credentials
2. Check if MySQL is running
3. Verify user permissions
4. Check firewall settings

#### Queue Workers Not Processing
1. Check Supervisor status:
   ```bash
   sudo supervisorctl status
   ```
2. Restart workers:
   ```bash
   sudo supervisorctl restart all
   ```
3. Check logs:
   ```bash
   tail -f /var/log/worker.log
   ```

#### File Upload Issues
1. Check PHP upload limits in `php.ini`:
   ```
   upload_max_filesize = 20M
   post_max_size = 20M
   ```
2. Check storage permissions
3. Verify `public/storage` symlink exists

## Maintenance Mode

### Enable Maintenance Mode
```bash
php artisan down --secret="maintenance-secret-key"
```

### Access During Maintenance
```
https://your-domain.com/maintenance-secret-key
```

### Disable Maintenance Mode
```bash
php artisan up
```

## Performance Optimization

### 1. PHP-FPM Optimization

#### 1.1 PHP-FPM Pool Configuration

Edit `/etc/php/8.1/fpm/pool.d/www.conf` to optimize PHP-FPM for your server's resources. The following settings are based on a server with 8GB RAM. Adjust according to your server specifications.

```ini
; Process manager settings - Choose 'static' for dedicated servers, 'ondemand' for shared hosting
; static = fixed number of child processes (pm.max_children)
; dynamic = number of child processes is set dynamically based on the following directives:
;   pm.max_children, pm.start_servers, pm.min_spare_servers, pm.max_spare_servers
; ondemand = processes spawn when requested (better for low traffic or shared hosting)
pm = dynamic

; Max number of child processes
; Formula: (Total RAM - (500MB for OS + MySQL + other services)) / (Average PHP process size in MB)
; Example: (8GB - 1.5GB) / 50MB = ~130 processes
pm.max_children = 50

; Number of child processes created on startup
; Set to 25% of pm.max_children
pm.start_servers = 13

; Minimum number of idle server processes
; Set to 10% of pm.max_children
pm.min_spare_servers = 5

; Maximum number of idle server processes
; Set to 35% of pm.max_children
pm.max_spare_servers = 18

; Number of requests each child process should execute before respawning
; Helps prevent memory leaks
pm.max_requests = 500

; Process settings
; Timeout for processing a single request before killing the process
request_terminate_timeout = 300s

; Timeout for serving a single request
request_slowlog_timeout = 60s

; Log file for slow requests
slowlog = /var/log/php-fpm/slow.log

; User and group
user = www-data
group = www-data

; Listen options
listen = /run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

; Process priority
; Lower values mean higher priority (-20 to 20)
; Default is 0, set to -19 for high priority
process.priority = -19

; Process manager status page
pm.status_path = /status

; Ping path for health checks
ping.path = /ping

; Access control for status and ping
; Allow from localhost and internal network
; Example: allow = 127.0.0.1
;          allow = 192.168.1.0/24
;          deny = all

; Process control
; Maximum time a process can be running before it's terminated
request_terminate_timeout = 300s

; Maximum time a process can be idle before it's terminated
process_idle_timeout = 10s

; Emergency restart threshold and interval
; Restart child processes if this many have been killed
; due to reaching pm.max_requests
emergency_restart_threshold = 10
emergency_restart_interval = 1m

; Performance tuning
pm.max_requests = 500
pm.process_idle_timeout = 10s
pm.status_path = /status
```

#### 1.2 PHP OPcache Configuration

Edit `/etc/php/8.1/fpm/conf.d/10-opcache.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=32
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
opcache.enable_cli=1
opcache.jit_buffer_size=100M
opcache.jit=1235
```

### 2. Nginx Optimization

#### 2.1 Main Nginx Configuration

Edit `/etc/nginx/nginx.conf`:

```nginx
user www-data;
worker_processes auto;
worker_rlimit_nofile 100000;
pid /run/nginx.pid;
include /etc/nginx/modules-enabled/*.conf;

events {
    worker_connections 4000;
    use epoll;
    multi_accept on;
}

http {
    # Basic Settings
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_tokens off;
    
    # Buffer size for POST submissions
    client_body_buffer_size 10K;
    client_header_buffer_size 1k;
    client_max_body_size 20m;
    large_client_header_buffers 4 8k;
    
    # Timeouts
    client_body_timeout 12;
    client_header_timeout 12;
    send_timeout 10;
    
    # FastCGI
    fastcgi_connect_timeout 300;
    fastcgi_send_timeout 180s;
    fastcgi_read_timeout 180s;
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
    fastcgi_busy_buffers_size 256k;
    fastcgi_temp_file_write_size 256k;
    
    # Gzip Settings
    gzip on;
    gzip_disable "msie6";
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_buffers 16 8k;
    gzip_http_version 1.1;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
    
    # Cache settings
    open_file_cache max=200000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;
    
    # Include other configs
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
```

### 3. Database Optimization

#### 3.1 MySQL/MariaDB Configuration

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
# General
skip-name-resolve
max_connections = 200
max_user_connections = 180
wait_timeout = 300
interactive_timeout = 300

# MyISAM
key_buffer_size = 256M
myisam_sort_buffer_size = 256M

# InnoDB
innodb_buffer_pool_size = 4G
innodb_log_file_size = 256M
innodb_log_buffer_size = 8M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
innodb_file_format = Barracuda
innodb_large_prefix = 1
innodb_lock_wait_timeout = 120

# Logging
log_error = /var/log/mysql/error.log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/mysql-slow.log
long_query_time = 1
log_queries_not_using_indexes = 1

# Performance Schema
performance_schema = ON
performance_schema_max_table_instances = 50000
```

#### 3.2 Database Indexing

1. **Add Indexes for Common Queries**

```sql
-- For properties table
ALTER TABLE properties 
ADD INDEX idx_property_status (status),
ADD INDEX idx_property_type (type),
ADD INDEX idx_property_price (price),
ADD INDEX idx_property_location (city, state);

-- For users table
ALTER TABLE users 
ADD INDEX idx_user_email (email),
ADD INDEX idx_user_role (role);

-- For property_visits table
ALTER TABLE property_visits
ADD INDEX idx_visit_dates (visit_date, visit_time),
ADD INDEX idx_visit_status (status);
```

2. **Regular Maintenance**

Create a maintenance script at `/usr/local/bin/mysql-maintenance.sh`:

```bash
#!/bin/bash

# Optimize tables weekly
mysqlcheck -u root -p --auto-repair --optimize --all-databases

# Analyze tables for better query optimization
mysqlcheck -u root -p --analyze --all-databases

# Check and repair tables if needed
mysqlcheck -u root -p --check --all-databases
```

Make it executable:
```bash
chmod +x /usr/local/bin/mysql-maintenance.sh
```

#### 3.3 Scheduled Database Maintenance

Add a cron job to run the optimization script weekly:

```bash
# Edit crontab
crontab -e

# Add this line to run every Sunday at 2 AM
0 2 * * 0 /usr/local/bin/mysql-maintenance.sh
```

### 4. Laravel Optimization

#### 4.1 Application Optimization

1. **Optimize Autoloader**

```bash
composer install --optimize-autoloader --no-dev
```

2. **Cache Configuration**

```bash
# Cache routes
php artisan route:cache

# Cache config
php artisan config:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

3. **Queue Workers**

Configure supervisor to manage queue workers in `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/aps-dreamhome/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
directory=/var/www/aps-dreamhome
redirect_stderr=true
stdout_logfile=/var/log/worker.log
stopwaitsecs=3600
```

#### 4.2 Caching Strategy

1. **Redis Caching**

Configure `.env` for Redis:

```ini
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
```

2. **Model Caching**

Use the `remember` method for frequently accessed queries:

```php
// Cache for 60 minutes
$properties = Cache::remember('featured_properties', 60, function () {
    return Property::where('is_featured', true)
        ->with('images')
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
});
```

3. **View Caching**

Cache expensive view fragments:

```php
// In your controller
$featuredProperties = Cache::remember('featured_properties_view', 60, function () {
    return Property::featured()->get();
});

return view('welcome', compact('featuredProperties'));

// In your blade template
@cache('featured-properties-section', null, $featuredProperties)
    <!-- Featured properties HTML here -->
@endcache
```

### 5. Frontend Optimization

#### 5.1 Asset Compilation

1. **Laravel Mix Configuration**

Update `webpack.mix.js`:

```javascript
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
       require('postcss-import'),
       require('tailwindcss'),
   ])
   .version()
   .sourceMaps()
   .browserSync('your-domain.com');

if (mix.inProduction()) {
    mix.version();
}
```

2. **Image Optimization**

Add image optimization to your deployment process:

```bash
# Install required packages
npm install --save-dev imagemin imagemin-webp imagemin-mozjpeg imagemin-pngquant

# Add to package.json scripts
"scripts": {
    "optimize-images": "imagemin resources/images/* --plugin=mozjpeg --out-dir=public/optimized-images"
}
```

### 6. Monitoring and Maintenance

#### 6.1 Performance Monitoring

1. **Laravel Telescope**

Install and configure Laravel Telescope for debugging and monitoring:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

2. **Laravel Horizon**

For queue monitoring and management:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

#### 6.2 Logging and Error Tracking

Configure `.env` for better logging:

```ini
LOG_CHANNEL=stack
LOG_LEVEL=debug

# For production
LOG_CHANNEL=daily
LOG_LEVEL=error

# Error tracking (e.g., Sentry)
SENTRY_LARAVEL_DSN=your-sentry-dsn
```

#### 6.3 Regular Maintenance Tasks

Create a maintenance command:

```bash
php artisan make:command MaintenanceCommand
```

Edit `app/Console/Commands/MaintenanceCommand.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MaintenanceCommand extends Command
{
    protected $signature = 'app:maintenance';
    protected $description = 'Run scheduled maintenance tasks';

    public function handle()
    {
        $this->info('Starting maintenance tasks...');
        
        // Clear application cache
        Artisan::call('cache:clear');
        $this->info('✓ Cleared application cache');
        
        // Clear view cache
        Artisan::call('view:clear');
        $this->info('✓ Cleared view cache');
        
        // Clear route cache
        Artisan::call('route:clear');
        $this->info('✓ Cleared route cache');
        
        // Clear config cache
        Artisan::call('config:clear');
        $this->info('✓ Cleared config cache');
        
        // Optimize the application
        Artisan::call('optimize');
        $this->info('✓ Optimized the application');
        
        // Clear expired password reset tokens
        Artisan::call('auth:clear-resets');
        $this->info('✓ Cleared expired password reset tokens');
        
        // Clear expired sessions
        Artisan::call('session:gc');
        $this->info('✓ Garbage collected expired sessions');
        
        $this->info('\nMaintenance completed successfully!');
        return 0;
    }
}
```

Schedule the command in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('app:maintenance')->dailyAt('3:00');
}
```

### 6. Database Optimization

#### 6.1 MySQL/MariaDB Configuration

Edit `/etc/mysql/my.cnf` or `/etc/mysql/mariadb.conf.d/50-server.cnf`:

```ini
[mysqld]
# Connection Settings
max_connections = 200
max_connect_errors = 1000000
connect_timeout = 60
wait_timeout = 600
max_allowed_packet = 256M
thread_cache_size = 128
thread_stack = 256K

# Buffer Settings
key_buffer_size = 256M
query_cache_limit = 8M
query_cache_size = 128M
query_cache_type = 1
join_buffer_size = 4M
sort_buffer_size = 4M
read_buffer_size = 2M
read_rnd_buffer_size = 4M
table_definition_cache = 4000
table_open_cache = 4000
open_files_limit = 10000

# InnoDB Settings
innodb_buffer_pool_size = 4G
innodb_buffer_pool_instances = 4
innodb_log_file_size = 1G
innodb_log_buffer_size = 16M
innodb_flush_log_at_trx_commit = 1
innodb_flush_method = O_DIRECT
innodb_thread_concurrency = 16
innodb_read_io_threads = 16
innodb_write_io_threads = 16
innodb_io_capacity = 2000
innodb_io_capacity_max = 4000
innodb_autoextend_increment = 64M
innodb_file_per_table = ON
innodb_file_format = Barracuda
innodb_large_prefix = 1
innodb_lock_wait_timeout = 120

# Logging
log_error = /var/log/mysql/error.log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/mysql-slow.log
long_query_time = 1
log_queries_not_using_indexes = 1

# Performance Schema
performance_schema = ON
performance_schema_max_table_instances = 50000
```

#### 6.2 Database Maintenance Script

Create a maintenance script at `/usr/local/bin/mysql-optimize.sh`:

```bash
#!/bin/bash

DB_USER="dbuser"
DB_PASS="your_secure_password"
DB_NAME="aps_dreamhome"
LOG_FILE="/var/log/mysql-optimize.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "Starting database optimization..."

# Optimize all databases
for db in $(mysql -u "$DB_USER" -p"$DB_PASS" -e "SHOW DATABASES;" | grep -Ev "(Database|information_schema|performance_schema|mysql|sys)"); do
    log "Optimizing database: $db"
    
    # Get list of tables
    for table in $(mysql -u "$DB_USER" -p"$DB_PASS" -e "SHOW TABLES FROM $db;" | tail -n +2); do
        log "  - Optimizing table: $table"
        mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $db; ANALYZE TABLE $table;"
        mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $db; OPTIMIZE TABLE $table;"
    done
    
    # Optimize the database
    mysqlcheck -u "$DB_USER" -p"$DB_PASS" --optimize --auto-repair "$db"
done

# Flush privileges and logs
mysql -u "$DB_USER" -p"$DB_PASS" -e "FLUSH PRIVILEGES; FLUSH TABLES; FLUSH QUERY CACHE;"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Database optimization completed"
```

Make the script executable:
```bash
chmod +x /usr/local/bin/mysql-optimize.sh
```

#### 6.3 Scheduled Database Maintenance

Add a cron job to run the optimization script weekly:

```bash
# Edit crontab
crontab -e

# Add this line to run every Sunday at 2 AM
0 2 * * 0 /usr/local/bin/mysql-optimize.sh
```

### 7. Redis Caching and Queue Optimization

#### 7.1 Redis Installation and Configuration

Install Redis server:

```bash
# For Ubuntu/Debian
sudo apt update
sudo apt install -y redis-server

# For CentOS/RHEL
sudo yum install -y epel-release
sudo yum install -y redis

# Enable and start Redis
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

#### 7.2 Redis Configuration

Edit `/etc/redis/redis.conf`:

```ini
# Basic settings
daemonize yes
pidfile /var/run/redis/redis-server.pid
port 6379
bind 127.0.0.1
timeout 0
tcp-keepalive 300

# General
loglevel notice
logfile /var/log/redis/redis-server.log
databases 16

# Snapshotting
save 900 1
save 300 10
save 60 10000
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes
dbfilename dump.rdb
dir /var/lib/redis

# Replication
# Uncomment and configure if using Redis replication
# replicaof <masterip> <masterport>
# masterauth <master-password>
replica-serve-stale-data yes
replica-read-only yes
repl-diskless-sync no
repl-diskless-sync-delay 5
repl-disable-tcp-nodelay no
replica-priority 100

# Security
# Require clients to issue AUTH <PASSWORD> before processing any other commands
# requirepass your_secure_password

# Clients
maxclients 10000
maxmemory 2gb
maxmemory-policy allkeys-lru
maxmemory-samples 5

# Append Only Mode
appendonly yes
appendfilename "appendonly.aof"
appendfsync everysec
no-appendfsync-on-rewrite no
auto-aof-rewrite-percentage 100
auto-aof-rewrite-min-size 64mb
aof-load-truncated yes
aof-rewrite-incremental-fsync yes

# Lua scripting
lua-time-limit 5000

# Slow log
slowlog-log-slower-than 10000
slowlog-max-len 128

# Event notification
notify-keyspace-events ""

# Advanced config
hash-max-ziplist-entries 512
hash-max-ziplist-value 64
list-max-ziplist-size -2
list-compress-depth 0
set-max-intset-entries 512
zset-max-ziplist-entries 128
zset-max-ziplist-value 64
hll-sparse-max-bytes 3000
activerehashing yes
client-output-buffer-limit normal 0 0 0
client-output-buffer-limit replica 256mb 64mb 60
client-output-buffer-limit pubsub 32mb 8mb 60
hz 10
aof-rewrite-incremental-fsync yes
```

#### 7.3 Laravel Redis Configuration

Update `.env` file:

```ini
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Optional: Use different Redis databases for different purposes
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3
```

#### 7.4 Redis Sentinel (Optional, for High Availability)

```ini
REDIS_CLUSTER=redis-sentinel
REDIS_HOST='tcp://127.0.0.1:26379'
REDIS_SENTINEL_SERVICE=my-redis-master
```

#### 7.5 Queue Worker Configuration

Create a supervisor configuration file at `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/aps-dreamhome/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/var/log/worker.log
stopwaitsecs=3600
```

#### 7.6 Cache Prewarming Script

Create a cache prewarming script at `/usr/local/bin/warmup-cache.sh`:

```bash
#!/bin/bash

# Warm up application cache
php /var/www/aps-dreamhome/artisan config:cache
php /var/www/aps-dreamhome/artisan route:cache
php /var/www/aps-dreamhome/artisan view:cache

# Warm up model cache (if using spatie/laravel-model-caching)
php /var/www/aps-dreamhome/artisan modelCache:clear
php /var/www/aps-dreamhome/artisan modelCache:create

# Warm up route cache
php /var/www/aps-dreamhome/artisan route:cache

# Warm up view cache
php /var/www/aps-dreamhome/artisan view:cache

# Warm up Redis cache
php /var/www/aps-dreamhome/artisan cache:clear
php /var/www/aps-dreamhome/artisan responsecache:clear

# Preload frequently accessed data into cache
php /var/www/aps-dreamhome/artisan cache:forever app_settings \
    '$(php /var/www/aps-dreamhome/artisan tinker --execute="echo json_encode(\App\Models\Setting::all()->pluck('value', 'key')->toArray())")'

# Schedule the next warmup
(crontab -l 2>/dev/null; echo "0 3 * * * /usr/local/bin/warmup-cache.sh") | crontab -
```

Make it executable:
```bash
chmod +x /usr/local/bin/warmup-cache.sh
```

#### 7.7 Monitoring Redis

Install Redis CLI tools:
```bash
sudo apt install -y redis-tools
```

Useful Redis CLI commands:
```bash
# Check Redis info
redis-cli info

# Monitor Redis in real-time
redis-cli monitor

# Check memory usage
redis-cli info memory

# Check connected clients
redis-cli client list

# Check slow queries
redis-cli slowlog get

# Check memory fragmentation
redis-cli info memory | grep fragmentation
```

### 8. CDN and Asset Optimization

#### 8.1 CDN Configuration

1. **Set up a CDN provider** (e.g., Cloudflare, AWS CloudFront, or Cloudinary)

2. **Update Laravel environment** (`.env`):

```ini
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=your_region
AWS_BUCKET=your_bucket_name
AWS_URL=https://your-cdn-url.com
AWS_ENDPOINT=https://s3.your-region.amazonaws.com
```

#### 8.2 Asset Versioning

In your blade templates, use the `mix()` or `asset()` helper with versioning:

```php
<!-- In your blade template -->
<link href="{{ mix('css/app.css') }}" rel="stylesheet">
<script src="{{ mix('js/app.js') }}"></script>
```

## Upgrading

1. Backup database and files
2. Pull latest changes:
   ```bash
   git pull origin main
   ```
3. Update dependencies:
   ```bash
   composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
   npm install
   npm run production
   ```
4. Run migrations:
   ```bash
   php artisan migrate --force
   ```
5. Clear caches:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Support

For deployment support, contact:
- Email: devops@apsdreamhome.com
- Documentation: [https://docs.apsdreamhome.com](https://docs.apsdreamhome.com)
- Status Page: [https://status.apsdreamhome.com](https://status.apsdreamhome.com)

## Automation & Scripting

### Automated Deployment Script
```bash
#!/bin/bash
# Full-stack deployment automation
DEPLOY_DIR="/var/www/aps-dreamhome"
BRANCH="main"

echo "[$(date)] Starting deployment"

# Frontend deployment
cd "${DEPLOY_DIR}/frontend"
git fetch origin
git checkout ${BRANCH}
git pull origin ${BRANCH}
npm install
npm run build

# Backend deployment
cd "${DEPLOY_DIR}"
git fetch origin
git checkout ${BRANCH}
git pull origin ${BRANCH}
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php-fpm
sudo systemctl restart nginx

echo "[$(date)] Deployment completed successfully"
```

### Automated Log Rotation
```bash
# /etc/logrotate.d/aps-dreamhome
/var/www/aps-dreamhome/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        sudo systemctl reload php-fpm > /dev/null
    endscript
}
```

### Automated Security Updates
```bash
#!/bin/bash
# Security patching script
apt-get update && apt-get upgrade -y
apt-get autoremove -y
apt-get clean

# Renew SSL certificates
certbot renew --quiet --post-hook "systemctl reload nginx"
```

## CI/CD Pipeline

### GitHub Actions Automation
```yaml
name: Production Deployment

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Checkout code
      uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, ctype, json, tokenizer, pdo_mysql, fileinfo, gd, imagick
    
    - name: Install dependencies
      run: |
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
        npm ci
        npm run production
    
    - name: Upload to Server
      uses: appleboy/scp-action@master
      with:
        host: ${{ secrets.PROD_HOST }}
        username: ${{ secrets.PROD_USER }}
        key: ${{ secrets.PROD_SSH_KEY }}
        source: "*"
        target: /var/www/aps-dreamhome
    
    - name: Run Migrations
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.PROD_HOST }}
        username: ${{ secrets.PROD_USER }}
        key: ${{ secrets.PROD_SSH_KEY }}
        script: |
          cd /var/www/aps-dreamhome
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
```

## Monitoring and Logging

### Automated Health Checks
```bash
#!/bin/bash
# Server health monitoring
ALERT_EMAIL="admin@example.com"
THRESHOLD=80

# CPU check
CPU_LOAD=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')
if (( $(echo "${CPU_LOAD} > ${THRESHOLD}" | bc -l) )); then
  echo "High CPU usage detected: ${CPU_LOAD}%" | mail -s "CPU Alert" ${ALERT_EMAIL}
fi

# Memory check
MEM_USAGE=$(free | grep Mem | awk '{print $3/$2 * 100.0}')
if (( $(echo "${MEM_USAGE} > ${THRESHOLD}" | bc -l) )); then
  echo "High memory usage detected: ${MEM_USAGE}%" | mail -s "Memory Alert" ${ALERT_EMAIL}
fi

# Disk check
DISK_USAGE=$(df / | awk 'END{print $5}' | sed 's/%//')
if [ ${DISK_USAGE} -gt ${THRESHOLD} ]; then
  echo "High disk usage detected: ${DISK_USAGE}%" | mail -s "Disk Alert" ${ALERT_EMAIL}
fi
```

### Log Monitoring Script
```bash
#!/bin/bash
# Real-time error monitoring
LOG_FILE="/var/www/aps-dreamhome/storage/logs/laravel.log"
ALERT_EMAIL="admin@example.com"

tail -n0 -F ${LOG_FILE} | while read LINE
do
  if [[ "${LINE}" == *"ERROR"* ]]; then
    echo "Critical error detected: ${LINE}" | \
    mail -s "Application Error Alert" ${ALERT_EMAIL}
  fi

done
```
