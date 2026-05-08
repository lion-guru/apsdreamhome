@echo off
echo ==========================================
echo Flutter SDK Installer for APS Dream Home
echo ==========================================
echo.

REM Check if already installed
where flutter >nul 2>&1
if %errorlevel% == 0 (
    echo Flutter already installed!
    flutter --version
    pause
    exit /b
)

REM Download Flutter
echo [1/4] Downloading Flutter SDK...
cd C:\
powershell -Command "Invoke-WebRequest -Uri 'https://storage.googleapis.com/flutter_infra_release/releases/stable/windows/flutter_windows_3.19.6-stable.zip' -OutFile 'flutter.zip'"

REM Extract
echo [2/4] Extracting...
powershell -Command "Expand-Archive -Path 'flutter.zip' -DestinationPath 'C:\flutter' -Force"

REM Add to PATH
echo [3/4] Adding to PATH...
setx PATH "%PATH%;C:\flutter\bin" /M

REM Cleanup
del C:\flutter.zip

echo [4/4] Installation Complete!
echo.
echo IMPORTANT: Close and reopen VS Code/CMD
echo Then run: flutter doctor
echo.
pause
