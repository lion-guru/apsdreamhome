#!/bin/bash
# APS Dream Homes Pvt Ltd - Deployment Script
# This script helps deploy the website to a live server

echo "🚀 APS Dream Homes Pvt Ltd - Deployment Script"
echo "=============================================="

# Configuration
PROJECT_NAME="apsdreamhomefinal"
LOCAL_PATH="c:/xampp/htdocs/apsdreamhomefinal"
REMOTE_USER="your_username"
REMOTE_HOST="your_host.com"
REMOTE_PATH="/home/your_username/public_html"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Step 1: Check if we're on Windows
if [[ "$OSTYPE" == "cygwin" ]] || [[ "$OSTYPE" == "msys" ]]; then
    print_warning "Windows detected. Using Windows-compatible commands."
    WINDOWS=true
else
    WINDOWS=false
fi

# Step 2: Pre-deployment checks
print_status "Step 1: Pre-deployment checks..."

# Check if local files exist
if [ ! -d "$LOCAL_PATH" ]; then
    print_error "Local project directory not found: $LOCAL_PATH"
    exit 1
fi

print_success "Local project directory found"

# Check if required files exist
required_files=(
    "index.php"
    "properties_template.php"
    "about_template.php"
    "contact_template.php"
    "admin_panel.php"
    "includes/db_connection.php"
    "includes/universal_template.php"
)

for file in "${required_files[@]}"; do
    if [ -f "$LOCAL_PATH/$file" ]; then
        print_success "✓ $file exists"
    else
        print_error "✗ $file missing"
        exit 1
    fi
done

# Step 3: Database export
print_status "Step 2: Database export..."

if [ "$WINDOWS" = true ]; then
    print_warning "Please export database manually using phpMyAdmin"
    print_status "Go to: http://localhost/phpmyadmin/"
    print_status "Export database: apsdreamhomefinal"
    read -p "Press Enter when database is exported..."
else
    # Linux/Mac database export
    mysqldump -u root -p apsdreamhomefinal > database_backup.sql
    if [ $? -eq 0 ]; then
        print_success "Database exported successfully"
    else
        print_error "Database export failed"
        exit 1
    fi
fi

# Step 4: Create deployment package
print_status "Step 3: Creating deployment package..."

DEPLOYMENT_FILE="${PROJECT_NAME}_deployment_$(date +%Y%m%d_%H%M%S).zip"

if [ "$WINDOWS" = true ]; then
    # Windows deployment
    cd "$LOCAL_PATH"
    powershell -Command "Compress-Archive -Path * -DestinationPath '../$DEPLOYMENT_FILE'"
else
    # Linux/Mac deployment
    cd "$LOCAL_PATH"
    zip -r "../$DEPLOYMENT_FILE" .
fi

if [ -f "../$DEPLOYMENT_FILE" ]; then
    print_success "Deployment package created: $DEPLOYMENT_FILE"
else
    print_error "Failed to create deployment package"
    exit 1
fi

# Step 5: Upload to server
print_status "Step 4: Uploading to server..."

if [ "$WINDOWS" = true ]; then
    print_warning "Please upload the file manually using FTP client"
    print_status "Upload: ../$DEPLOYMENT_FILE"
    print_status "To: $REMOTE_HOST (via FTP)"
    read -p "Press Enter when upload is complete..."
else
    # Linux/Mac upload
    scp "../$DEPLOYMENT_FILE" "$REMOTE_USER@$REMOTE_HOST:~/"
    if [ $? -eq 0 ]; then
        print_success "File uploaded successfully"
    else
        print_error "Upload failed"
        exit 1
    fi
fi

# Step 6: Remote server setup
print_status "Step 5: Remote server setup..."

if [ "$WINDOWS" = true ]; then
    print_warning "Please complete these steps manually on your server:"
else
    # SSH commands for remote setup
    ssh "$REMOTE_USER@$REMOTE_HOST" << EOF
        cd "$REMOTE_PATH"
        unzip -o "~/$DEPLOYMENT_FILE"
        rm "~/$DEPLOYMENT_FILE"

        # Set permissions
        find . -type d -exec chmod 755 {} \;
        find . -type f -exec chmod 644 {} \;
        chmod 755 *.php
        chmod 755 includes/
        chmod 755 uploads/ 2>/dev/null || mkdir -p uploads && chmod 755 uploads

        # Create database (if needed)
        echo "Please create database: $PROJECT_NAME"
        echo "And import the SQL file manually"
EOF
fi

# Step 7: Configuration update
print_status "Step 6: Configuration updates..."

if [ "$WINDOWS" = false ]; then
    ssh "$REMOTE_USER@$REMOTE_HOST" << EOF
        cd "$REMOTE_PATH"
        cp config_production.php config.php 2>/dev/null || echo "Production config already set"
EOF
fi

# Step 8: Final checks
print_status "Step 7: Final deployment checks..."

if [ "$WINDOWS" = false ]; then
    ssh "$REMOTE_USER@$REMOTE_HOST" << EOF
        cd "$REMOTE_PATH"
        echo "=== Deployment Verification ==="
        echo "Files in directory: \$(ls -la | wc -l)"
        echo "PHP files: \$(find . -name '*.php' | wc -l)"
        echo "Database connection test: "
        php -r "
            try {
                \$conn = new PDO('mysql:host=localhost;dbname=$PROJECT_NAME', '$REMOTE_USER', 'REMOTE_PASSWORD');
                echo '✓ Database connection successful';
            } catch(Exception \$e) {
                echo '✗ Database connection failed: ' . \$e->getMessage();
            }
        "
        echo "=== End Verification ==="
EOF
fi

# Step 9: Deployment complete
print_status "Step 8: Deployment complete!"

echo ""
print_success "🎉 APS Dream Homes Pvt Ltd - Deployment Complete!"
echo ""
print_status "📋 Next Steps:"
echo "1. Update database credentials in includes/db_connection.php"
echo "2. Import database backup to your hosting"
echo "3. Update BASE_URL in configuration files"
echo "4. Test all pages on live server"
echo "5. Set up SSL certificate"
echo "6. Configure email settings"
echo ""

print_status "🌐 Your website will be available at:"
if [ "$WINDOWS" = false ]; then
    echo "https://$REMOTE_HOST/"
else
    echo "https://yourdomain.com/"
fi

print_status "📞 Contact Information:"
echo "Phone: +91-9554000001"
echo "Email: info@apsdreamhomes.com"
echo ""

print_success "🚀 Your APS Dream Homes Pvt Ltd website is now live!"
echo ""
print_status "Don't forget to:"
echo "- Set up Google Analytics"
echo "- Submit to Google Search Console"
echo "- Create social media pages"
echo "- Start marketing your properties"
echo ""

# End of script
echo "=============================================="
print_success "Deployment script completed successfully!"
echo "=============================================="
