@echo off
echo Restoring APS Dream Home Database...
echo.

REM Navigate to MySQL directory
cd C:\xampp\mysql\bin

REM Import the complete database
mysql -u root apsdreamhomefinal < C:\xampp\htdocs\apsdreamhomefinal\database\apsdreamhomes.sql

REM Verify the restoration
echo.
echo Verifying restoration...
mysql -u root apsdreamhomefinal -e "SELECT COUNT(*) as tables FROM information_schema.tables WHERE table_schema = 'apsdreamhomefinal';"

echo.
echo Database restoration complete!
pause
