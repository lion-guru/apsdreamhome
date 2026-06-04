# =============================================================================
# APS Dream Home - WebSocket Container
# Runs Ratchet WebSocket server (chat, notifications, auctions, live tracking)
# =============================================================================

FROM php:8.2-cli-alpine

LABEL maintainer="APS Dream Home <dev@apsdreamhome.com>"
LABEL description="APS Dream Home WebSocket Server (Ratchet)"

# Install system dependencies
RUN apk add --no-cache \
        libzip-dev \
        oniguruma-dev \
        openssl-dev \
        curl-dev \
        icu-dev \
        bash \
        netcat-openbsd \
        curl \
    ; \
    \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        opcache \
        zip \
        bcmath \
        intl \
        sockets \
    ; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    \
    rm -rf /tmp/* /var/cache/apk/*

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Workdir
WORKDIR /var/www/html

# Production PHP settings (lighter than web tier)
RUN { \
        echo 'memory_limit = 256M'; \
        echo 'max_execution_time = 0'; \
        echo 'date.timezone = Asia/Kolkata'; \
        echo 'expose_php = Off'; \
        echo 'display_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'error_log = /var/log/websocket_error.log'; \
    } > /usr/local/etc/php/conf.d/zz-websocket.ini

# Copy application code
COPY . /var/www/html

# Install composer dependencies (production only)
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --optimize-autoloader \
        --prefer-dist \
        --no-progress \
        --ignore-platform-reqs \
    || true

# Copy and make startup script executable
COPY docker/websocket/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Healthcheck - WebSocket port should be listening
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD nc -z 127.0.0.1 8080 || exit 1

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/start.sh"]
CMD ["websocket"]
