#!/bin/bash

# APS Dream Home - Railway Deployment Script
# ==========================================
# This script helps you deploy to Railway

echo "🚀 APS Dream Home - Railway Deployment Helper"
echo "============================================="

# Check if Railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "📦 Installing Railway CLI..."
    curl -fsSL https://railway.app/install.sh | sh
fi

echo "✅ Railway CLI installed"

# Login to Railway
echo "🔐 Please login to Railway:"
echo "Run: railway login"
echo "Then press Enter to continue..."
read -p ""

# Check if project exists
echo "🔍 Checking for existing Railway project..."
railway status

echo ""
echo "📋 NEXT STEPS:"
echo "=============="
echo "1. 🌐 Go to https://railway.app"
echo "2. ➕ Create New Project"
echo "3. 🐳 Select 'Deploy from GitHub' or 'Deploy from Docker'"
echo "4. 📁 Select your repository or upload files"
echo "5. ⚙️  Configure environment variables from .env.railway"
echo "6. 🗄️  Add MySQL database service"
echo "7. 🌐 Add custom domain: apsdreamhomes.com"
echo "8. 🚀 Click Deploy!"

echo ""
echo "📋 IMPORTANT CONFIGURATION:"
echo "=========================="
echo "• Runtime: PHP 8.2"
echo "• Build Command: composer install && npm run build"
echo "• Start Command: php-fpm"
echo "• Port: 8080"

echo ""
echo "🔧 ENVIRONMENT VARIABLES TO SET:"
echo "================================"
echo "Copy from .env.railway file to Railway dashboard"

echo ""
echo "🎯 POST-DEPLOYMENT CHECKLIST:"
echo "============================"
echo "✅ Verify database connection"
echo "✅ Test all pages load correctly"
echo "✅ Configure custom domain"
echo "✅ Set up SSL certificate"
echo "✅ Test admin panel"
echo "✅ Test contact forms"

echo ""
echo "🏆 SUCCESS! Your site will be live at:"
echo "https://your-app-name.railway.app"
echo "or"
echo "https://apsdreamhomes.com (after domain setup)"

echo ""
echo "💡 Need help? Check Railway documentation:"
echo "https://docs.railway.app"
