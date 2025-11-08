@echo off
echo 🔍 Setting up APS Dream Home Database...
echo.

REM Path to MySQL executable (adjust if needed)
set MYSQL_PATH="C:\xampp\mysql\bin\mysql.exe"
set DB_NAME=apsdreamhome
set DB_USER=root
set DB_PASS=

REM Check if MySQL is running
%MYSQL_PATH% -u%DB_USER% -e "SELECT 1" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ❌ MySQL server is not running. Please start MySQL from XAMPP Control Panel.
    pause
    exit /b 1
)

echo ✅ MySQL server is running

REM Import SQL file
echo.
echo 📥 Importing database schema and data...
%MYSQL_PATH% -u%DB_USER% %DB_NAME% < database\create_tables.sql

if %ERRORLEVEL% equ 0 (
    echo.
    echo ✅ Database setup completed successfully!
    echo.
    echo 📊 Summary:
    echo "   - Database: %DB_NAME%"
    echo "   - Tables: colonies, plots, and other required tables"
    echo "   - Sample data for 4 colonies and multiple plots"
) else (
    echo.
    echo ❌ Error occurred during database setup.
    echo Please check the error message above.
)

echo.
pause
