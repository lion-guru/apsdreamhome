@echo off
echo ==============================================
echo   APS Dream Home API Test Runner
echo ==============================================
echo.

echo [1/3] Testing PHP Client...
php test_php_client.php
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ❌ PHP Client Tests Failed!
    exit /b 1
)

echo.
echo [2/3] Testing JavaScript Client...
node test_js_client.js
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ❌ JavaScript Client Tests Failed!
    exit /b 1
)

echo.
echo [3/3] Testing Python Client...
python test_python_client.py
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ❌ Python Client Tests Failed!
    exit /b 1
)

echo.
echo ==============================================
echo   All API tests completed successfully!
echo ==============================================
pause
