# =============================================================================
# APS Dream Home - Production Dockerfile (php:8.2-apache)
# Custom PHP MVC framework, not Laravel
# =============================================================================

# ---- Stage 1: Composer dependencies ----
FROM composer:2.7 AS vendor

WORKDIR /app

# Copy composer files first for better layer caching
COPY composer.json composer.lock* ./

# Install production dependencies (no dev) with autoload optimization
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --optimize-autoloader \
        --prefer-dist \
        --no-progress \
    || composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --optimize-autoloader \
        --prefer-dist \
        --no-progress \
        --ignore-platform-reqs

# ---- Stage 2: Production runtime (php:8.2-apache) ----
FROM php:8.2-apache AS production

# Set working directory
WORKDIR /var/www/html

# Avoid interactive prompts during package install
ENV DEBIAN_FRONTEND=noninteractive

# Install system packages and PHP extensions in one layer
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        # Build tools (transient, removed after)
        gcc g++ make autoconf pkg-config \
        # Runtime libraries
        libzip-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libxml2-dev libicu-dev libssl-dev libcurl4-openssl-dev \
        # Useful utilities
        git unzip curl ca-certificates nano htop \
        # Cron for scheduled tasks
        cron \
    ; \
    \
    # Install PHP extensions
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        opcache \
        zip \
        gd \
        bcmath \
        intl \
        sockets \
    ; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    \
    # Enable Apache modules
    a2enmod rewrite headers expires deflate ssl remoteip; \
    \
    # Clean up build dependencies to slim image
    apt-get purge -y --auto-remove gcc g++ make autoconf pkg-config; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Configure Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN set -eux; \
    # Update Apache config to use public/ as document root
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf; \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf; \
    # Allow .htaccess overrides
    echo '<Directory ${APACHE_DOCUMENT_ROOT}>' > /etc/apache2/conf-available/app-overrides.conf; \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/conf-available/app-overrides.conf; \
    echo '    AllowOverride All' >> /etc/apache2/conf-available/app-overrides.conf; \
    echo '    Require all granted' >> /etc/apache2/conf-available/app-overrides.conf; \
    echo '</Directory>' >> /etc/apache2/conf-available/app-overrides.conf; \
    a2enconf app-overrides; \
    # Server name to suppress warning
    echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf; \
    a2enconf servername

# Production PHP configuration
RUN set -eux; \
    { \
        echo 'memory_limit = 512M'; \
        echo 'upload_max_filesize = 50M'; \
        echo 'post_max_size = 50M'; \
        echo 'max_execution_time = 300'; \
        echo 'max_input_time = 300'; \
        echo 'max_input_vars = 5000'; \
        echo 'date.timezone = Asia/Kolkata'; \
        echo 'expose_php = Off'; \
        echo 'display_errors = Off'; \
        echo 'display_startup_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'error_log = /var/log/apache2/php_error.log'; \
        echo 'opcache.enable = 1'; \
        echo 'opcache.memory_consumption = 256'; \
        echo 'opcache.interned_strings_buffer = 16'; \
        echo 'opcache.max_accelerated_files = 20000'; \
        echo 'opcache.revalidate_freq = 60'; \
        echo 'opcache.validate_timestamps = 1'; \
        echo 'opcache.fast_shutdown = 1'; \
        echo 'session.cookie_httponly = 1'; \
        echo 'session.use_strict_mode = 1'; \
        echo 'session.cookie_samesite = "Lax"'; \
    } > /usr/local/etc/php/conf.d/zz-app.ini

# Copy application code (exclude vendor via .dockerignore; we'll add composer deps)
COPY --chown=www-data:www-data . /var/www/html

# Copy vendor from builder stage
COPY --from=vendor --chown=www-data:www-data /app/vendor /var/www/html/vendor

# Copy entrypoint and make executable
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create writable directories and set permissions
RUN set -eux; \
    mkdir -p \
        /var/www/html/storage/logs \
        /var/www/html/storage/cache \
        /var/www/html/storage/uploads \
        /var/www/html/storage/sessions \
        /var/www/html/public/uploads \
        /var/www/html/public/assets/uploads \
        /var/log/apache2 \
    ; \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads /var/www/html/public/assets/uploads; \
    chmod -R 775 /var/www/html/storage /var/www/html/public/uploads /var/www/html/public/assets/uploads; \
    # Apache needs write access to logs
    chown -R www-data:www-data /var/log/apache2; \
    # Cron directory
    mkdir -p /var/spool/cron/crontabs; \
    touch /var/spool/cron/crontabs/www-data; \
    chown -R www-data:www-data /var/spool/cron

# Expose HTTP and HTTPS ports
EXPOSE 80 443

# Healthcheck - simple HTTP probe
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -fsS http://localhost/ -o /dev/null || exit 1

# Use custom entrypoint that handles DB wait + migrations + Apache start
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
