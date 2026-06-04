# =============================================================================
# APS Dream Home - PHP Application Container
# php:8.2-apache with required extensions for the custom MVC framework
# =============================================================================

# Use the multi-stage build from the root Dockerfile
# This file is referenced by docker-compose.yml
# Build context is the project root, so we reference the root Dockerfile
FROM php:8.2-apache

# Maintainer
LABEL maintainer="APS Dream Home <dev@apsdreamhome.com>"
LABEL description="APS Dream Home - Real Estate CRM Platform"
LABEL version="1.0"

# Avoid interactive prompts
ENV DEBIAN_FRONTEND=noninteractive

# Install system packages
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        gcc g++ make autoconf pkg-config \
        libzip-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libxml2-dev libicu-dev libssl-dev libcurl4-openssl-dev \
        git unzip curl ca-certificates nano htop \
        cron rsync \
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
    # Clean up
    apt-get purge -y --auto-remove gcc g++ make autoconf pkg-config; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Document root is the public/ subdirectory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN set -eux; \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf; \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf; \
    \
    # Allow .htaccess in the document root
    { \
        echo '<VirtualHost *:80>'; \
        echo '    ServerAdmin webmaster@localhost'; \
        echo '    DocumentRoot ${APACHE_DOCUMENT_ROOT}'; \
        echo '    <Directory ${APACHE_DOCUMENT_ROOT}>'; \
        echo '        Options Indexes FollowSymLinks'; \
        echo '        AllowOverride All'; \
        echo '        Require all granted'; \
        echo '    </Directory>'; \
        echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
        echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
        echo '</VirtualHost>'; \
    } > /etc/apache2/sites-available/000-default.conf; \
    \
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

WORKDIR /var/www/html

# Create writable directories
RUN set -eux; \
    mkdir -p \
        /var/www/html/storage/logs \
        /var/www/html/storage/cache \
        /var/www/html/storage/uploads \
        /var/www/html/storage/sessions \
        /var/www/html/public/uploads \
        /var/www/html/public/assets/uploads \
    ; \
    chown -R www-data:www-data /var/www/html; \
    find /var/www/html -type d -exec chmod 755 {} +; \
    find /var/www/html -type f -exec chmod 644 {} +; \
    chmod -R 775 /var/www/html/storage /var/www/html/public/uploads /var/www/html/public/assets/uploads

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -fsS http://localhost/ -o /dev/null || exit 1

EXPOSE 80
