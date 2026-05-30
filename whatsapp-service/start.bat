@echo off
cd /d C:\xampp\htdocs\apsdreamhome\whatsapp-service
echo Installing dependencies (skipping Chromium download)...
set PUPPETEER_SKIP_DOWNLOAD=true
call npm install
echo.
echo Starting WhatsApp service...
node server.js
pause
