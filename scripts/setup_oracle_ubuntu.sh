#!/usr/bin/env bash
# ==============================================================================
# APS DREAM HOME - Oracle Cloud Ubuntu Staging / Production Provisioning Script
# Compatible with: Ubuntu 22.04 / 24.04 LTS (ARM64 Ampere A1 & x86_64 AMD)
# ==============================================================================

set -e

echo ">>> [1/7] Updating system packages..."
sudo apt update && sudo apt upgrade -y

echo ">>> [2/7] Installing prerequisite tools..."
sudo apt install -y software-properties-common curl git unzip zip certbot python3-certbot-apache ufw

echo ">>> [3/7] Adding PHP PPA and installing PHP 8.2 stack..."
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y apache2 libapache2-mod-php8.2 \
    php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd \
    php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl \
    php8.2-soap php8.2-opcache

echo ">>> [4/7] Installing MariaDB Server..."
sudo apt install -y mariadb-server

# Start and enable services
sudo systemctl enable apache2
sudo systemctl start apache2
sudo systemctl enable mariadb
sudo systemctl start mariadb

echo ">>> [5/7] Installing Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
fi

echo ">>> [6/7] Configuring Apache VirtualHost..."
sudo a2enmod rewrite headers expires ssl

# Create VirtualHost configuration
VHOST_CONF="/etc/apache2/sites-available/apsdreamhome.conf"
sudo bash -c "cat > $VHOST_CONF" << 'EOF'
<VirtualHost *:80>
    ServerName apsdreamhome.local
    DocumentRoot /var/www/apsdreamhome/public

    <Directory /var/www/apsdreamhome/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/apsdreamhome_error.log
    CustomLog ${APACHE_LOG_DIR}/apsdreamhome_access.log combined
</VirtualHost>
EOF

sudo a2dissite 000-default.conf || true
sudo a2ensite apsdreamhome.conf
sudo systemctl restart apache2

echo ">>> [7/7] Setting up Database and User..."
DB_PASS=$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)
sudo mysql -e "CREATE DATABASE IF NOT EXISTS apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'aps_user'@'localhost' IDENTIFIED BY '$DB_PASS';"
sudo mysql -e "GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'aps_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Configure firewall (UFW)
sudo ufw allow OpenSSH
sudo ufw allow 'Apache Full'
echo "y" | sudo ufw enable || true

echo "=============================================================================="
echo "🎉 APS DREAM HOME SERVER SETUP COMPLETE!"
echo "=============================================================================="
echo "Database Name : apsdreamhome"
echo "Database User : aps_user"
echo "Database Pass : $DB_PASS"
echo "Web Root      : /var/www/apsdreamhome/public"
echo "Save this database password securely!"
echo "=============================================================================="
