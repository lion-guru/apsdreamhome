# APS Dream Home - PowerShell Docker Deployment Script
# =====================================================
# This script handles Docker deployment without buffer issues

Write-Host "🚀 APS Dream Home - PowerShell Docker Deployment" -ForegroundColor Green
Write-Host "=================================================" -ForegroundColor Green
Write-Host ""

# Set variables
$DOCKER_USER = "abhaysingh3007"
$DOCKER_REPO = "aps-dream-home"
$DOMAIN = $args[0]

# Check if domain is provided
if (-not $DOMAIN) {
    Write-Host "❌ Error: Please provide your domain name" -ForegroundColor Red
    Write-Host "Usage: .\deploy_docker_windows.ps1 yourdomain.com" -ForegroundColor Yellow
    Write-Host "Example: .\deploy_docker_windows.ps1 apsdreamhome.com" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "📋 Starting Docker deployment for domain: $DOMAIN" -ForegroundColor Cyan
Write-Host ""

# Check Docker Desktop
Write-Host "🔍 Checking Docker Desktop..." -ForegroundColor Yellow
try {
    $dockerVersion = docker --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Docker Desktop is running: $dockerVersion" -ForegroundColor Green
    } else {
        throw "Docker not accessible"
    }
} catch {
    Write-Host "❌ Docker Desktop is not running or not installed." -ForegroundColor Red
    Write-Host "Please install Docker Desktop for Windows and make sure it's running." -ForegroundColor Yellow
    Write-Host "Download: https://docs.docker.com/desktop/install/windows-install/" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""

# Step 1: Login to Docker Hub
Write-Host "🔐 Step 1: Logging into Docker Hub..." -ForegroundColor Yellow
Write-Host "Please enter your Docker password when prompted:" -ForegroundColor Cyan
Write-Host ""

try {
    docker login -u $DOCKER_USER
    if ($LASTEXITCODE -ne 0) {
        throw "Login failed"
    }
    Write-Host "✅ Docker login successful" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker login failed. Please check your credentials." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""

# Step 2: Tag images
Write-Host "🏷️  Step 2: Tagging Docker images..." -ForegroundColor Yellow

Write-Host "Tagging application image..." -ForegroundColor Cyan
docker tag apsdreamhome_app "$DOCKER_USER/$DOCKER_REPO:latest"

Write-Host "Tagging MySQL image..." -ForegroundColor Cyan
docker tag apsdreamhome_mysql "$DOCKER_USER/$DOCKER_REPO:mysql"

Write-Host "Tagging Nginx image..." -ForegroundColor Cyan
docker tag apsdreamhome_nginx "$DOCKER_USER/$DOCKER_REPO:nginx"

Write-Host "✅ All images tagged successfully" -ForegroundColor Green
Write-Host ""

# Step 3: Push images to Docker Hub
Write-Host "📤 Step 3: Pushing images to Docker Hub..." -ForegroundColor Yellow
Write-Host ""

Write-Host "Pushing latest image..." -ForegroundColor Cyan
docker push "$DOCKER_USER/$DOCKER_REPO:latest"

Write-Host "Pushing mysql image..." -ForegroundColor Cyan
docker push "$DOCKER_USER/$DOCKER_REPO:mysql"

Write-Host "Pushing nginx image..." -ForegroundColor Cyan
docker push "$DOCKER_USER/$DOCKER_REPO:nginx"

Write-Host "✅ All images pushed successfully" -ForegroundColor Green
Write-Host ""

# Step 4: Deploy to production
Write-Host "🚀 Step 4: Deploying to production..." -ForegroundColor Yellow
Write-Host "Deploying for domain: $DOMAIN" -ForegroundColor Cyan
Write-Host ""

# Check if deploy_enhanced.bat exists
if (-not (Test-Path "deploy_enhanced.bat")) {
    Write-Host "❌ deploy_enhanced.bat not found." -ForegroundColor Red
    Write-Host "Please make sure deploy_enhanced.bat exists in the current directory." -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# Run the enhanced deployment script
try {
    & ".\deploy_enhanced.bat" $DOMAIN
    if ($LASTEXITCODE -ne 0) {
        throw "Deployment failed"
    }
} catch {
    Write-Host "❌ Deployment failed. Check the error messages above." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "✅ Deployment completed successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Your application should be available at: https://$DOMAIN" -ForegroundColor Cyan
Write-Host "📊 Monitoring: http://localhost:9090" -ForegroundColor Cyan
Write-Host "📈 Dashboard: http://localhost:3000" -ForegroundColor Cyan
Write-Host ""

Write-Host "📋 Next Steps:" -ForegroundColor Yellow
Write-Host "1. Update your DNS to point $DOMAIN to this server" -ForegroundColor White
Write-Host "2. Configure SSL certificate for production" -ForegroundColor White
Write-Host "3. Set up monitoring alerts" -ForegroundColor White
Write-Host "4. Test all functionality" -ForegroundColor White
Write-Host ""

Write-Host "🎉 Docker deployment process completed!" -ForegroundColor Green
Write-Host ""
Write-Host "🔧 Troubleshooting:" -ForegroundColor Yellow
Write-Host "• If you get PowerShell errors, close all terminals and open a new one" -ForegroundColor White
Write-Host "• Make sure Docker Desktop is running" -ForegroundColor White
Write-Host "• Check that all images are built correctly" -ForegroundColor White
Write-Host ""

Read-Host "Press Enter to exit"
