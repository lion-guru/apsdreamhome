# APS Dream Home - Production Launch Checklist
## YOUR ACTION REQUIRED - Exact Commands to Run

---

## 📋 PRE-REQUISITES (Do these FIRST)

### 1. Provision Ubuntu 22.04 VPS (2-4 GB RAM, 2 vCPU, 50+ GB SSD)
```bash
# On your local machine - create VPS on DigitalOcean / Linode / AWS / Hetzner
# Recommended: 4 GB RAM, 2 vCPU, 80 GB SSD, Ubuntu 22.04 LTS
# Note the public IP: YOUR_SERVER_IP
```

### 2. Point Domain to Server
```bash
# In your DNS provider (GoDaddy / Namecheap / Cloudflare):
# Type: A Record    Name: @        Value: YOUR_SERVER_IP
# Type: A Record    Name: www      Value: YOUR_SERVER_IP
# Wait 5-10 min for DNS propagation
```

---

## 🚀 SERVER SETUP (Run on VPS as root)

### 3. Initial Server Setup
```bash
ssh root@YOUR_SERVER_IP

# Update & install base packages
apt update && apt upgrade -y
apt install -y nginx php8.3-fpm php8.3-mysql php8.3-curl php8.3-mbstring \
  php8.3-xml php8.3-zip php8.3-gd php8.3-bcmath php8.3-redis \
  mysql-server certbot python3-certbot-nginx git unzip htop

# Secure MySQL
mysql_secure_installation
# Set root password, remove anonymous users, disable remote root login, remove test DB

# Create database & user
mysql -u root -p
CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'aps_user'@'localhost' IDENTIFIED BY 'STRONG_RANDOM_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'aps_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Save MySQL root password for scripts
cat > /root/.my.cnf << 'EOF'
[client]
user=root
password=YOUR_MYSQL_ROOT_PASSWORD
EOF
chmod 600 /root/.my.cnf
```

### 4. Configure Nginx
```bash
# Copy deploy/nginx/apsdreamhome.conf to /etc/nginx/sites-available/
cp /var/www/apsdreamhome/deploy/nginx/apsdreamhome.conf /etc/nginx/sites-available/
ln -s /etc/nginx/sites-available/apsdreamhome.conf /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default

# Test & reload
nginx -t && systemctl reload nginx
```

### 5. Deploy Code
```bash
cd /var/www
git clone https://github.com/lion-guru/apsdreamhome.git
cd apsdreamhome

# Copy and edit production env
cp deploy/scripts/.env.production .env
# EDIT .env with ALL real credentials (see step 6)

# Install dependencies
composer install --no-dev --optimize-autoloader

# Import database
mysql -u aps_user -p apsdreamhome < database/full_backup.sql
# OR if using migrations: php scripts/migrate.php

# Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 6. Fill .env with REAL Credentials
```bash
nano /var/www/apsdreamhome/.env
```
**REQUIRED fields to replace:**
- `DB_PASSWORD=STRONG_RANDOM_PASSWORD_HERE`
- `RAZORPAY_KEY_ID=rzp_live_xxx` + `RAZORPAY_KEY_SECRET=xxx` + `RAZORPAY_WEBHOOK_SECRET=whsec_xxx`
- `WHATSAPP_API_TOKEN=EAAXXX` + `WHATSAPP_PHONE_NUMBER_ID=xxx` + `WHATSAPP_WEBHOOK_VERIFY_TOKEN=random_string`
- `MSG91_AUTH_KEY=xxx` + `MSG91_TEMPLATE_ID=xxx`
- `MAIL_PASSWORD=gmail_app_password`
- `SENTRY_DSN=https://xxx@sentry.io/xxx` (optional)

### 7. SSL Certificate
```bash
# Get Let's Encrypt cert
certbot --nginx -d apsdreamhome.com -d www.apsdreamhome.com \
  --email admin@apsdreamhome.com --agree-tos --non-interactive

# Test auto-renewal
certbot renew --dry-run
```

### 7b. Diffie-Hellman Params (for SSL security)
```bash
openssl dhparam -out /etc/letsencrypt/ssl-dhparams.pem 2048
```

### 8. Install Systemd Services
```bash
# Copy service files
cp /var/www/apsdreamhome/deploy/systemd/*.service /etc/systemd/system/
cp /var/www/apsdreamhome/deploy/systemd/*.timer /etc/systemd/system/

# Reload & enable
systemctl daemon-reload
systemctl enable --now apsdreamhome-websocket
systemctl enable --now apsdreamhome-websocket-broadcast
systemctl enable --now apsdreamhome-cron.timer

# Verify
systemctl status apsdreamhome-websocket
systemctl status apsdreamhome-websocket-broadcast
systemctl list-timers apsdreamhome-cron.timer
```

### 9. Setup Automated Backups
```bash
# Install rclone
curl https://rclone.org/install.sh | bash

# Configure Google Drive remote
rclone config
# n) New remote → name: gdrive → type: drive → follow auth flow

# Test
rclone lsd gdrive:

# Install backup script
cp /var/www/apsdreamhome/deploy/scripts/backup_gdrive.sh /usr/local/bin/
chmod +x /usr/local/bin/backup_gdrive.sh

# Add to crontab (daily 2 AM)
crontab -e
# Add: 0 2 * * * /usr/local/bin/backup_gdrive.sh >> /var/log/backup_gdrive.log 2>&1
```

### 10. SSL Monitoring Cron
```bash
cp /var/www/apsdreamhome/deploy/scripts/ssl_renew_monitor.sh /usr/local/bin/
chmod +x /usr/local/bin/ssl_renew_monitor.sh

crontab -e
# Add: 0 3 * * * /usr/local/bin/ssl_renew_monitor.sh
```

### 11. GitHub Secrets (for CI/CD)
Go to: **GitHub Repo → Settings → Secrets and variables → Actions → New repository secret**

| Secret Name | Value |
|-------------|-------|
| `SERVER_HOST` | `YOUR_SERVER_IP` |
| `SERVER_USER` | `root` |
| `SSH_PRIVATE_KEY` | `cat ~/.ssh/id_rsa` (your deploy key) |
| `SLACK_WEBHOOK` | `https://hooks.slack.com/services/xxx` (optional) |
| `MYSQL_ROOT_PASSWORD` | (for workflow if needed) |

### 12. Verify Deployment
```bash
# Run deploy script manually first test
/var/www/apsdreamhome/deploy/scripts/deploy.sh main

# Check health
curl -I https://apsdreamhome.com/
curl -I https://apsdreamhome.com/api/v2/mobile/properties

# Check logs
tail -f /var/log/nginx/apsdreamhome_access.log
tail -f /var/www/apsdreamhome/logs/php_error.log
```

---

## ✅ VERIFICATION CHECKLIST

| Check | Command | Expected |
|-------|---------|----------|
| HTTPS | `curl -I https://apsdreamhome.com` | `HTTP/2 200` |
| SSL Grade | <https://www.ssllabs.com/ssltest/analyze.html?d=apsdreamhome.com> | A+ |
| API | `curl https://apsdreamhome.com/api/v2/mobile/properties` | JSON response |
| WebSocket | `curl -I https://apsdreamhome.com/websocket/` | 101 Switching Protocols |
| Backups | `rclone lsf gdrive:apsdreamhome-backups` | Files listed |
| Cron | `systemctl list-timers` | apsdreamhome-cron.timer active |
| Services | `systemctl status apsdreamhome-*` | All active (running) |

---

## 📱 MOBILE APP - PLAY STORE

```bash
# Build release AAB
cd /var/www/apsdreamhome/mobile/apsdreamhome_app_v2
flutter build appbundle --release

# Output: build/app/outputs/bundle/release/app-release.aab
# Upload to Google Play Console → Internal Testing → Rollout
```

---

## 🔐 CREDENTIALS YOU NEED TO PROVIDE

| Service | What to Get | Where |
|---------|-------------|-------|
| **Server** | Ubuntu 22.04 VPS IP, root SSH access | DigitalOcean / Linode / AWS / Hetzner |
| **Domain** | DNS A records pointing to VPS IP | GoDaddy / Namecheap / Cloudflare |
| **MySQL** | Root password, aps_user password | You create |
| **Razorpay** | Live Key ID, Secret, Webhook Secret | <https://dashboard.razorpay.com> |
| **WhatsApp** | API Token, Phone Number ID, Webhook Verify Token | <https://developers.facebook.com> |
| **SMS (MSG91)** | Auth Key, Template ID, Sender ID | <https://msg91.com> + DLT approval |
| **Email** | Gmail App Password or SendGrid API Key | Google Account / SendGrid |
| **Google Drive** | rclone config (OAuth) | `rclone config` interactive |
| **GitHub** | SSH deploy key, repo secrets | GitHub repo settings |

---

## 🎯 DONE! 
**After completing all steps above, your production system will be live at:**
- **Website:** https://apsdreamhome.com
- **API:** https://apsdreamhome.com/api/v2/mobile/
- **Admin:** https://apsdreamhome.com/admin/login
- **Mobile App:** Play Store (after review)

**Support:** Check logs at `/var/log/nginx/`, `/var/www/apsdreamhome/logs/`, `journalctl -u apsdreamhome-*`