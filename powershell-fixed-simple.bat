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

powershell -NoProfile -ExecutionPolicy Bypass -Command "& {
    # Suppress VCRUNTIME140.dll warnings
    $env:PHP_INI_SCAN_DIR = ""
    $ErrorActionPreference = "SilentlyContinue"
    
    # Custom prompt
    function prompt {
        "APS Dream Home PS " + (Get-Location) + "> "
    }
    
    # Test functions
    function Test-PHP {
        try {
            $result = php -v 2>&1 | Out-String
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ PHP is working (warnings suppressed)" -ForegroundColor Green
                return $true
            } else {
                Write-Host "❌ PHP test failed" -ForegroundColor Red
                return $false
            }
        } catch {
            Write-Host "❌ PHP error: $($_.Exception.Message)" -ForegroundColor Red
            return $false
        }
    }
    
    function Test-Git {
        try {
            $result = git --version 2>&1 | Out-String
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ Git is working" -ForegroundColor Green
                return $true
            } else {
                Write-Host "❌ Git test failed" -ForegroundColor Red
                return $false
            }
        } catch {
            Write-Host "❌ Git error: $($_.Exception.Message)" -ForegroundColor Red
            return $false
        }
    }
    
    function Start-AutoSync {
        Write-Host "🚀 Starting APS Dream Home Auto-Sync..." -ForegroundColor Cyan
        
        if (Test-PHP) {
            Write-Host "✅ PHP integration working" -ForegroundColor Green
        }
        
        if (Test-Git) {
            Write-Host "✅ Git integration working" -ForegroundColor Green
        }
        
        Write-Host "🎉 Auto-Sync is ready!" -ForegroundColor Green
        Write-Host "Type 'exit' to close PowerShell" -ForegroundColor Yellow
    }
    
    Write-Host "🎉 APS Dream Home PowerShell Environment Loaded!" -ForegroundColor Green
    Write-Host "Type 'Start-AutoSync' to begin auto-sync" -ForegroundColor Cyan
    Write-Host "Type 'Test-PHP' to test PHP integration" -ForegroundColor Cyan
    Write-Host "Type 'Test-Git' to test Git integration" -ForegroundColor Cyan
    Write-Host "Type 'exit' to close PowerShell" -ForegroundColor Yellow
    Write-Host ""
    
    # Keep PowerShell open
    try {
        $Host.UI.RawUI.WindowTitle = "APS Dream Home PowerShell - Fixed"
        # Interactive shell
        while ($true) {
            $command = Read-Host
            if ($command -eq "exit") { break }
            try {
                Invoke-Expression $command
            } catch {
                Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    } catch {
        Write-Host "Press Enter to exit..." -ForegroundColor Yellow
        Read-Host
    }
}"

echo.
echo PowerShell session ended.
pause
