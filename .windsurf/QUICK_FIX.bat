@echo off
echo Quick Fix for Windsurf Performance Issues
echo ========================================
echo.

echo Stopping Windsurf...
taskkill /F /IM Code.exe >nul 2>&1
taskkill /F /IM Windsurf.exe >nul 2>&1

echo Clearing caches...
del /Q /F /S "%TEMP%\*" >nul 2>&1
del /Q /F /S ".vscode\.cache\*" >nul 2>&1
del /Q /F /S ".windsurf\cache\*" >nul 2>&1

echo Restarting Windsurf...
start code .

echo.
echo ✅ Quick fix completed!
echo Windsurf should be responsive now.
echo.
pause
