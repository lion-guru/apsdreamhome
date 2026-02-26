# APS Dream Home - Production Deployment README
# Complete deployment guide for production environment

## 🚀 Quick Start Deployment

### Prerequisites
- Ubuntu 22.04 LTS server
- Root or sudo access
- Domain name pointed to server IP
- SSH access to server

### One-Command Deployment
```bash
# Upload project files to server
scp -r /path/to/apsdreamhome user@server:/tmp/

# Run deployment script
sudo bash /tmp/apsdreamhome/deploy-production.sh

# That's it! Your application will be live at https://yourdomain.com
```

---

## 📋 Detailed Deployment Steps

### Step 1: Server Preparation
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd php8.1-intl composer certbot python3-certbot-nginx git curl wget htop iotop sysstat

# Install Node.js (optional, for asset compilation)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Secure MySQL installation
sudo mysql_secure_installation
```

### Step 2: Domain & DNS Configuration
```bash
# Update /etc/hosts if needed
echo "127.0.0.1 yourdomain.com www.yourdomain.com" | sudo tee -a /etc/hosts

# Verify DNS resolution
nslookup yourdomain.com
dig yourdomain.com
```

### Step 3: Database Setup
```bash
# Create database and user
sudo mysql -u root -p << EOF
CREATE DATABASE apsdreamhome_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'apsdreamhome_user'@'localhost' IDENTIFIED BY 'SECURE_PASSWORD_CHANGE_THIS';
GRANT ALL PRIVILEGES ON apsdreamhome_prod.* TO 'apsdreamhome_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
EOF

# Import initial data
sudo mysql -u apsdreamhome_user -p apsdreamhome_prod < database-production.sql
```

### Step 4: Application Deployment
```bash
# Create application directory
sudo mkdir -p /var/www/apsdreamhome
sudo chown -R www-data:www-data /var/www/apsdreamhome

# Upload application files
sudo cp -r /path/to/apsdreamhome/* /var/www/apsdreamhome/

# Install PHP dependencies
cd /var/www/apsdreamhome
sudo -u www-data composer install --no-dev --optimize-autoloader

# Install Node dependencies (if applicable)
sudo -u www-data npm install
sudo -u www-data npm run build

# Set proper permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome
sudo find /var/www/apsdreamhome -type f -exec chmod 644 {} \;
sudo find /var/www/apsdreamhome -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/apsdreamhome/storage
sudo chmod -R 775 /var/www/apsdreamhome/bootstrap/cache
```

### Step 5: Environment Configuration
```bash
# Copy production environment file
sudo cp .env.production .env

# Edit environment variables
sudo nano .env
# Update the following variables:
# APP_URL=https://yourdomain.com
# DB_PASSWORD=your_secure_db_password
# MAIL_MAILER=smtp
# MAIL_HOST=your_smtp_host
# MAIL_USERNAME=your_smtp_username
# MAIL_PASSWORD=your_smtp_password

# Generate application key
sudo -u www-data php artisan key:generate

# Configure additional environment variables as needed
```

### Step 6: Web Server Configuration
```bash
# Copy Nginx configuration
sudo cp nginx-production.conf /etc/nginx/sites-available/apsdreamhome

# Edit domain name in configuration
sudo sed -i 's/yourdomain\.com/your-actual-domain.com/g' /etc/nginx/sites-available/apsdreamhome

# Enable site
sudo ln -s /etc/nginx/sites-available/apsdreamhome /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### Step 7: SSL Certificate Setup
```bash
# Run SSL setup script
sudo bash ssl-setup.sh

# Alternative manual setup
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Verify SSL
curl -I https://yourdomain.com
```

### Step 8: Database Migration & Seeding
```bash
cd /var/www/apsdreamhome

# Run migrations
sudo -u www-data php artisan migrate --force

# Seed database with initial data
sudo -u www-data php artisan db:seed --force

# Create admin user
sudo -u www-data php artisan tinker
# In tinker: User::create(['name'=>'Admin','email'=>'admin@yourdomain.com','password'=>Hash::make('secure_password')]);
```

### Step 9: Performance Optimization
```bash
cd /var/www/apsdreamhome

# Cache configuration and routes
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Optimize Composer autoloader
sudo -u www-data composer dump-autoload --optimize

# Set up cron jobs for automated tasks
sudo cp deploy/cron /etc/cron.d/apsdreamhome
sudo chmod 644 /etc/cron.d/apsdreamhome
sudo systemctl restart cron
```

### Step 10: Security Hardening
```bash
# Configure firewall
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'

# Disable root SSH login
sudo sed -i 's/#PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
sudo systemctl restart ssh

# Install fail2ban for SSH protection
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Set up log rotation
sudo cp deploy/logrotate /etc/logrotate.d/apsdreamhome
```

### Step 11: Backup Setup
```bash
# Set up automated backups
sudo mkdir -p /var/backups/apsdreamhome
sudo chown www-data:www-data /var/backups/apsdreamhome

# Copy backup script
sudo cp backup-production.sh /usr/local/bin/apsdreamhome-backup
sudo chmod +x /usr/local/bin/apsdreamhome-backup

# Set up daily backup cron job
echo "0 2 * * * www-data /usr/local/bin/apsdreamhome-backup database" | sudo tee -a /etc/cron.d/apsdreamhome-backup
echo "0 3 * * 0 www-data /usr/local/bin/apsdreamhome-backup full" | sudo tee -a /etc/cron.d/apsdreamhome-backup
```

### Step 12: Monitoring Setup
```bash
# Copy health check script
sudo cp health-check.sh /usr/local/bin/apsdreamhome-health-check
sudo chmod +x /usr/local/bin/apsdreamhome-health-check

# Set up health check cron job (every 6 hours)
echo "0 */6 * * * root /usr/local/bin/apsdreamhome-health-check full" | sudo tee -a /etc/cron.d/apsdreamhome-monitoring

# Install monitoring tools (optional)
sudo apt install -y prometheus-node-exporter
sudo systemctl enable prometheus-node-exporter
sudo systemctl start prometheus-node-exporter
```

### Step 13: Final Verification
```bash
# Run health check
sudo bash health-check.sh

# Test application access
curl -I https://yourdomain.com
curl https://yourdomain.com | head -20

# Test admin panel
curl -I https://yourdomain.com/admin/login

# Check logs
tail -f /var/log/nginx/apsdreamhome_access.log
tail -f /var/log/nginx/apsdreamhome_error.log
```

---

## 🔧 Post-Deployment Tasks

### User Account Setup
1. Create admin user account
2. Set up agent accounts
3. Configure associate accounts
4. Test user registration flow

### Content Population
1. Add property listings
2. Upload property images
3. Create project pages
4. Set up testimonials and reviews

### Integration Testing
1. Test payment gateway (Razorpay)
2. Verify email sending
3. Check WhatsApp integration
4. Test file uploads

### Performance Testing
1. Load testing with multiple users
2. Database query optimization
3. CDN setup and testing
4. Mobile responsiveness verification

---

## 🚨 Emergency Procedures

### Rollback Plan
```bash
# If deployment fails, rollback to previous version
sudo bash deploy/rollback.sh
```

### Common Issues & Solutions

#### Issue: 502 Bad Gateway
```bash
# Check PHP-FPM status
sudo systemctl status php8.1-fpm
sudo systemctl restart php8.1-fpm

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log
```

#### Issue: Database Connection Failed
```bash
# Check database credentials in .env
sudo nano /var/www/apsdreamhome/.env

# Test database connection
sudo -u www-data php artisan tinker
# DB::connection()->getPdo();
```

#### Issue: Permission Denied
```bash
# Fix storage permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome/storage
sudo chmod -R 775 /var/www/apsdreamhome/storage
```

#### Issue: SSL Certificate Issues
```bash
# Renew SSL certificate
sudo certbot renew

# Reload Nginx
sudo systemctl reload nginx
```

---

## 📊 Monitoring & Maintenance

### Daily Checks
- Monitor server resources (CPU, memory, disk)
- Check application logs for errors
- Verify backup completion
- Test critical user flows

### Weekly Maintenance
- Update system packages
- Clear application caches
- Review and optimize database queries
- Check SSL certificate expiry

### Monthly Tasks
- Full system backup verification
- Security audit and updates
- Performance optimization
- User feedback review

---

## 📞 Support & Documentation

### Support Contacts
- **Technical Support:** dev@apsdreamhome.com
- **Customer Support:** support@apsdreamhome.com
- **Emergency Contact:** +91-7007444842

### Documentation Links
- [Application User Guide](./docs/user-guide.md)
- [API Documentation](./docs/api.md)
- [Troubleshooting Guide](./docs/troubleshooting.md)
- [Security Guidelines](./docs/security.md)

---

## 🎯 Success Metrics

Monitor these KPIs for deployment success:

### Technical Metrics
- **Uptime:** > 99.9%
- **Response Time:** < 2 seconds
- **Error Rate:** < 0.1%
- **SSL Score:** A+ rating

### Business Metrics
- **User Registrations:** Track daily signups
- **Property Views:** Monitor engagement
- **Conversion Rate:** Track leads to sales
- **Customer Satisfaction:** Monitor feedback

---

## 🚀 Go-Live Checklist

- [ ] Domain DNS configured
- [ ] SSL certificate installed
- [ ] Database migrated and seeded
- [ ] Admin user created
- [ ] Email service configured
- [ ] Payment gateway tested
- [ ] Backups automated
- [ ] Monitoring active
- [ ] Health checks passing
- [ ] Load testing completed
- [ ] Security audit passed
- [ ] Documentation updated
- [ ] Support team briefed

---

**🎉 Deployment Complete! Your APS Dream Home application is now live and ready to serve customers!**
