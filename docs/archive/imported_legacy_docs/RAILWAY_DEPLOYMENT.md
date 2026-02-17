# 🚀 APS Dream Home - Railway Deployment Guide

## Overview
This guide will help you deploy APS Dream Home to Railway, a modern hosting platform that supports PHP applications with automatic scaling, databases, and custom domains.

## Prerequisites
- Railway account (free signup at [railway.app](https://railway.app))
- GitHub repository (optional but recommended)
- Domain name (apsdreamhomes.com)

## Quick Deployment (5 minutes)

### Method 1: Deploy from GitHub (Recommended)
1. **Connect Repository**
   - Go to [Railway Dashboard](https://railway.app)
   - Click "New Project" → "Deploy from GitHub"
   - Connect your GitHub account
   - Select your APS Dream Home repository

2. **Configure Services**
   - Railway will automatically detect PHP
   - Add MySQL database service
   - Configure environment variables (see below)

3. **Deploy**
   - Click "Deploy"
   - Railway will build and deploy automatically

### Method 2: Deploy from Docker
1. **Upload Files**
   - Go to Railway Dashboard
   - Create new project
   - Select "Deploy from Docker"
   - Upload your project files

2. **Configure Dockerfile**
   - Railway will use the provided Dockerfile
   - Ensure all dependencies are installed

## Environment Variables

Copy these to your Railway project settings:

```env
# Application
APP_NAME="APS Dream Home"
APP_ENV="production"
APP_DEBUG="false"
APP_URL="https://apsdreamhomes.com"

# Database (Railway MySQL)
DB_CONNECTION="mysql"
DB_HOST="@{MySQL.HOST}"
DB_PORT="@{MySQL.PORT}"
DB_DATABASE="@{MySQL.DATABASE}"
DB_USERNAME="@{MySQL.USERNAME}"
DB_PASSWORD="@{MySQL.PASSWORD}"

# Security
SESSION_DRIVER="database"
CACHE_DRIVER="database"

# Email Configuration
MAIL_MAILER="smtp"
MAIL_HOST="smtp.gmail.com"
MAIL_PORT="587"
MAIL_USERNAME="info@apsdreamhomes.com"
MAIL_PASSWORD="your-app-password"
MAIL_FROM_ADDRESS="info@apsdreamhomes.com"
```

## Custom Domain Setup

1. **GoDaddy DNS Configuration**
   ```
   Type: A Record
   Name: @
   Value: [Railway IP Address]
   TTL: 600

   Type: CNAME
   Name: www
   Value: [Railway Domain]
   TTL: 600
   ```

2. **Railway Domain Settings**
   - Go to Railway Dashboard
   - Select your project
   - Go to "Settings" → "Domains"
   - Add "apsdreamhomes.com" and "www.apsdreamhomes.com"

## Database Setup

1. **Railway MySQL**
   - Add MySQL service to your project
   - Railway will provide connection details
   - Import your database schema

2. **Database Migration**
   ```bash
   # Connect to Railway MySQL
   mysql -h [DB_HOST] -u [DB_USER] -p[DB_PASS] [DB_NAME]

   # Import schema
   source database/aps_dream_home_schema.sql
   ```

## Post-Deployment Checklist

### ✅ Essential Checks
- [ ] Website loads correctly
- [ ] Database connection working
- [ ] Admin panel accessible
- [ ] All pages functional
- [ ] Contact forms working
- [ ] Images loading properly

### ✅ Domain & SSL
- [ ] Custom domain configured
- [ ] SSL certificate active
- [ ] HTTPS redirect working
- [ ] www redirect working

### ✅ Performance & Security
- [ ] Caching enabled
- [ ] Security headers set
- [ ] Error logging configured
- [ ] Backup system active

## Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check Railway logs: `railway logs`
- Verify database connection
- Check PHP error logs

**Database Connection Failed**
- Verify MySQL service is running
- Check connection credentials
- Ensure database schema is imported

**Assets Not Loading**
- Check public file permissions
- Verify build process completed
- Check CDN configuration

**Domain Not Working**
- Wait for DNS propagation (24-48 hours)
- Verify DNS records are correct
- Check Railway domain settings

## Monitoring & Maintenance

### Railway Dashboard
- **Logs**: Real-time application logs
- **Metrics**: Performance monitoring
- **Deployments**: Deployment history
- **Databases**: Database management

### Performance Monitoring
- Application response times
- Database query performance
- Error rates and logs
- Resource utilization

## Support & Resources

### Railway Documentation
- [Getting Started](https://docs.railway.app)
- [PHP Deployment](https://docs.railway.app/deploy/php)
- [Database Setup](https://docs.railway.app/databases/mysql)

### Community
- [Railway Community](https://discord.gg/railway)
- [Railway Status](https://status.railway.app)

## Cost & Pricing

### Free Tier
- 512 MB RAM
- 1 GB Storage
- 100 GB Bandwidth/month
- Perfect for getting started!

### Pro Plan ($20/month)
- 8 GB RAM
- 100 GB Storage
- Unlimited Bandwidth
- Priority Support

## Next Steps After Deployment

1. **Content Management**
   - Add property listings
   - Update company information
   - Configure team profiles

2. **Marketing Setup**
   - Google Analytics integration
   - SEO optimization
   - Social media integration

3. **Business Operations**
   - Configure payment gateways
   - Set up email notifications
   - Enable user registration

4. **Monitoring & Analytics**
   - Set up error tracking
   - Configure performance monitoring
   - Set up backup systems

---

## 🚀 You're All Set!

Your APS Dream Home platform is now deployed on Railway with:
- ✅ **Automatic scaling**
- ✅ **Global CDN**
- ✅ **SSL certificates**
- ✅ **Database included**
- ✅ **Zero configuration**

**Your site will be live at:**
- **Railway URL:** https://your-app.railway.app
- **Custom Domain:** https://apsdreamhomes.com

**Happy deploying!** 🎉
