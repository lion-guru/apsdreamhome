# APS Dream Home - Production Dockerfile
# ======================================
# Multi-stage build for optimal production image

# Stage 1: Build PHP dependencies
FROM php:8.2-fpm-alpine AS builder

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    pcre-dev \
    sqlite-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Stage 2: Production image
FROM php:8.2-fpm-alpine AS production

# Install runtime dependencies
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    sqlite \
    nginx \
    supervisor \
    redis \
    certbot

# Create app user
RUN addgroup -g 1000 appgroup && \
    adduser -u 1000 -G appgroup -s /bin/sh -D appuser

# Install PHP extensions for production
RUN docker-php-ext-install pdo_mysql mbstring bcmath gd

# Copy application code
WORKDIR /var/www
COPY --chown=appuser:appgroup . .

# Copy vendor directory from builder stage
COPY --from=builder --chown=appuser:appgroup /var/www/vendor ./vendor

# Set up file permissions
RUN chown -R appuser:appgroup /var/www && \
    chmod -R 755 /var/www/storage && \
    chmod -R 755 /var/www/bootstrap/cache

# Create startup script
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh && \
    echo 'php artisan config:cache' >> /usr/local/bin/start.sh && \
    echo 'php artisan route:cache' >> /usr/local/bin/start.sh && \
    echo 'php artisan view:cache' >> /usr/local/bin/start.sh && \
    echo 'php-fpm' >> /usr/local/bin/start.sh && \
    chmod +x /usr/local/bin/start.sh

# Copy Nginx configuration
COPY --chown=appuser:appgroup docker/nginx.conf /etc/nginx/nginx.conf
COPY --chown=appuser:appgroup docker/default.conf /etc/nginx/conf.d/default.conf

# Copy Supervisor configuration
COPY --chown=appuser:appgroup docker/supervisord.conf /etc/supervisord.conf

# Create SSL certificate directory
RUN mkdir -p /etc/nginx/ssl

# Switch to app user
USER appuser

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Start application
CMD ["/usr/local/bin/start.sh"]
