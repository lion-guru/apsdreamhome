@echo off
echo Starting XAMPP...
cd /d C:\xampp
start xampp-control.exe
timeout /t 3 /nobreak > nul
echo.
echo Please click START for both Apache and MySQL in XAMPP Control Panel
echo.
echo Press any key when done...
pause > nul
