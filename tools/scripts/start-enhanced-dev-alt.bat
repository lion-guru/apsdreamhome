@echo off
echo 🚀 APS Dream Home - Enhanced Development Server (Alternative Port)
echo =================================================================
echo.
echo If port 3000 doesn't work, trying port 3001...
echo.
echo Starting server on http://127.0.0.1:3001
echo.
echo Press Ctrl+C to stop the server
echo.
npx vite --host 127.0.0.1 --port 3001
pause
