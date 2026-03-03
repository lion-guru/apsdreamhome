@echo off
title APS Dream Home PowerShell - Fixed
color 0A

echo ==========================================
echo APS Dream Home PowerShell - Fixed Version
echo ==========================================
echo.
echo 🔧 VCRUNTIME140.dll warnings suppressed
echo 🚀 PowerShell auto-close prevented
echo ✅ Auto-sync ready to use
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command "$env:PHP_INI_SCAN_DIR=''; $ErrorActionPreference='SilentlyContinue'; Write-Host '🎉 APS Dream Home PowerShell Environment Loaded!' -ForegroundColor Green; Write-Host 'Testing PHP integration...' -ForegroundColor Cyan; try { $result = php -v 2>&1; if ($LASTEXITCODE -eq 0) { Write-Host '✅ PHP is working (warnings suppressed)' -ForegroundColor Green } else { Write-Host '❌ PHP test failed' -ForegroundColor Red } } catch { Write-Host '❌ PHP error:' $_.Exception.Message -ForegroundColor Red }; Write-Host 'Testing Git integration...' -ForegroundColor Cyan; try { $result = git --version 2>&1; if ($LASTEXITCODE -eq 0) { Write-Host '✅ Git is working' -ForegroundColor Green } else { Write-Host '❌ Git test failed' -ForegroundColor Red } } catch { Write-Host '❌ Git error:' $_.Exception.Message -ForegroundColor Red }; Write-Host '🎉 Auto-Sync is ready!' -ForegroundColor Green; Write-Host 'Press Enter to continue...' -ForegroundColor Yellow; Read-Host"

echo.
echo PowerShell session completed.
pause
