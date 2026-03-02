@echo off
echo 🚀 Creating APS Dream Home Deployment Package...
echo.

REM Create deployment package directory
if not exist deployment_package mkdir deployment_package

REM Copy application files
echo 📁 Copying application files...
xcopy app\*.* deployment_package\app\ /E /I /Y
xcopy public\*.* deployment_package\public\ /E /I /Y
xcopy config\*.* deployment_package\config\ /E /I /Y
xcopy composer.json deployment_package\ /Y
xcopy .htaccess deployment_package\ /Y

REM Export database
echo 🗄️ Exporting database...
"C:\xampp\mysql\bin\mysqldump.exe" -u root --single-transaction --routines --triggers apsdreamhome > deployment_package\apsdreamhome_database.sql

REM Copy documentation files
echo 📝 Copying documentation files...
copy CO_WORKER_SETUP_INSTRUCTIONS.md deployment_package\ /Y
copy DEPLOYMENT_FIX_GUIDE.md deployment_package\ /Y
copy NEXT_STEPS_ROADMAP.md deployment_package\ /Y
copy verify_deployment.php deployment_package\ /Y

REM Create package info
echo 📋 Creating package info...
echo APS Dream Home Deployment Package > deployment_package\PACKAGE_INFO.txt
echo Created: %date% %time% >> deployment_package\PACKAGE_INFO.txt
echo PHP Version: >> deployment_package\PACKAGE_INFO.txt
php -v >> deployment_package\PACKAGE_INFO.txt
echo. >> deployment_package\PACKAGE_INFO.txt
echo Database: apsdreamhome_database.sql >> deployment_package\PACKAGE_INFO.txt
echo Size: >> deployment_package\PACKAGE_INFO.txt
dir deployment_package /s >> deployment_package\PACKAGE_INFO.txt

echo.
echo ✅ Deployment package created successfully!
echo 📦 Package Location: deployment_package\
echo 📊 Contents: Application files, database, documentation, verification tools
echo 🚀 Ready to share with co-worker system!
echo.

REM Count files in package
set /a file_count=0
for /r %%f in (deployment_package\*) do set /a file_count+=1
echo 📊 Total files in package: !file_count!

pause
