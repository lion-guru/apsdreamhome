# Dockerfile - Development Environment (PHP 8.2 + Apache + MySQL client)
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    default-mysql-client \
    libmagickwand-dev \
    libwebp-dev \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    gd \
    mbstring \
    intl \
    zip \
    pcntl \
    soap \
    sockets \
    bcmath \
    && pecl install apcu redis \
    && docker-php-ext-enable apcu redis \
    && rm -rf /tmp/pear /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers expires ssl

# Set document root (Apache serves from public/)
RUN rm -rf /var/www/html && ln -s /workspace/public /var/www/html

# Set working directory
WORKDIR /workspace

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# If you have composer.json, install dependencies
# COPY composer.json composer.lock ./
# RUN composer install --no-interaction --prefer-dist 2>/dev/null || true

EXPOSE 80
