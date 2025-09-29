@echo off
echo Stopping MySQL service...
net stop MySQL80 >nul 2>&1

echo Backing up MySQL data directory...
if not exist "C:\xampp\mysql\data_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%" (
    mkdir "C:\xampp\mysql\data_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%"
)

xcopy /E /I /Y "C:\xampp\mysql\data\*.*" "C:\xampp\mysql\data_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%"

echo Removing Aria log files...
del /Q "C:\xampp\mysql\data\aria_log.*" 2>nul

echo Running Aria recovery...
cd /d "C:\xampp\mysql\bin"
aria_chk --check --force --silent */*.MAI */*.MAD
aria_chk --check --force --silent */*/*.MAI */*/*.MAD

echo Starting MySQL service...
net start MySQL80 >nul 2>&1

echo Done! Check if MySQL is running in XAMPP Control Panel.
pause
