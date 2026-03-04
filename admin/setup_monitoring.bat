@echo off
echo ====================================================
echo 🚀 AUTO SYSTEM MONITOR SETUP 🚀
echo ====================================================
echo.

echo 📋 Step 1: Creating Windows Task Scheduler
echo ================================================
echo.

echo 🔧 Setting up automatic monitoring task...
schtasks /create /tn "APS Dream Home Monitor" /tr "php \"%~dp0auto_system_monitor.php\"" /sc minute /mo 5 /f

if %errorlevel% equ 0 (
    echo ✅ Task created successfully
) else (
    echo ❌ Failed to create task
    echo 🔧 Trying alternative method...
    schtasks /create /tn "APS Dream Home Monitor" /tr "C:\xampp\php\php.exe \"%~dp0auto_system_monitor.php\"" /sc minute /mo 5 /f
)

echo.

echo 📋 Step 2: Testing Monitor Script
echo =================================
echo.

echo 🔧 Testing single monitoring check...
php "%~dp0auto_system_monitor.php"

echo.

echo 📋 Step 3: Opening Monitor Dashboard
echo ==================================
echo.

echo 🌐 Opening system monitor dashboard...
start http://localhost/apsdreamhome/admin/system_monitor_dashboard.php

echo.

echo 📋 Step 4: Setup Complete
echo ==============================
echo.

echo ✅ Auto System Monitor Setup Complete!
echo.
echo 📊 Monitoring Features:
echo   • Automatic system checks every 5 minutes
echo   • Database connection monitoring
echo   • Page accessibility checks
echo   • Enhanced features verification
echo   • Admin system monitoring
echo   • Performance metrics tracking
echo   • Real-time dashboard access
echo   • Automated issue detection
echo   • System resource monitoring
echo.

echo 🎯 Next Steps:
echo   1. Monitor dashboard will refresh automatically
echo   2. Check logs in /logs/auto_monitor.log
echo   3. System will auto-fix common issues
echo   4. Alerts will be generated for critical issues
echo   5. Performance metrics tracked continuously
echo.

echo 🚀 SYSTEM MONITORING IS NOW ACTIVE! 🚀
echo.

pause
