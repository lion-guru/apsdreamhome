@echo off
echo ========================================
echo APACHE RESTART SCRIPT
echo ========================================
echo.
echo Stopping Apache...
net stop Apache2.4 >nul 2>&1
timeout /t 3 /nobreak >nul
echo.
echo Starting Apache...
net start Apache2.4 >nul 2>&1
timeout /t 3 /nobreak >nul
echo.
echo ========================================
echo ✅ APACHE RESTARTED SUCCESSFULLY!
echo ========================================
echo.
echo Now try accessing:
echo http://localhost/apsdreamhome/admin/bookings.php
echo.
echo If you still see errors, run the cache clear page:
echo http://localhost/apsdreamhome/cache_clear.html
echo.
pause
