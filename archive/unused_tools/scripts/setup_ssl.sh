#!/bin/bash

# APS Dream Home - SSL Certificate Setup Script
# =============================================
# This script helps set up SSL certificates for production deployment

echo "🔐 APS Dream Home - SSL Certificate Setup"
echo "========================================"

# Check if OpenSSL is installed
if ! command -v openssl &> /dev/null; then
    echo "❌ OpenSSL is not installed. Please install OpenSSL first."
    exit 1
fi

# Create SSL directory if it doesn't exist
SSL_DIR="./ssl"
if [ ! -d "$SSL_DIR" ]; then
    mkdir -p "$SSL_DIR"
    echo "📁 Created SSL directory: $SSL_DIR"
fi

# Generate self-signed certificate for development/testing
echo ""
echo "🔑 Generating self-signed SSL certificate for development..."
echo "   (For production, use Let's Encrypt or purchase a certificate)"

openssl req -x509 -newkey rsa:4096 -keyout "$SSL_DIR/private.key" -out "$SSL_DIR/certificate.crt" -days 365 -nodes -subj "/C=IN/ST=Uttar Pradesh/L=Lucknow/O=APS Dream Home/CN=localhost"

if [ $? -eq 0 ]; then
    echo "✅ Self-signed certificate generated successfully!"
    echo "   Certificate: $SSL_DIR/certificate.crt"
    echo "   Private Key: $SSL_DIR/private.key"
else
    echo "❌ Failed to generate SSL certificate"
    exit 1
fi

# Set proper permissions
chmod 600 "$SSL_DIR/private.key"
chmod 644 "$SSL_DIR/certificate.crt"

echo ""
echo "🔐 SSL Certificate Setup Complete!"
echo ""
echo "📋 Next Steps:"
echo "1. For production, replace with a valid SSL certificate"
echo "2. Update .env.production with SSL paths:"
echo "   SSL_CERT_PATH=$SSL_DIR/certificate.crt"
echo "   SSL_KEY_PATH=$SSL_DIR/private.key"
echo ""
echo "3. For Let's Encrypt (recommended for production):"
echo "   certbot certonly --webroot -w /var/www/html -d yourdomain.com"
echo ""
echo "4. Configure web server (Apache/Nginx) to use SSL"
echo ""
echo "📖 See PRODUCTION_DEPLOYMENT_GUIDE.md for detailed instructions"
