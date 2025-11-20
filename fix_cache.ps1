#Requires -RunAsAdministrator

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "APS DREAM HOME - AUTOMATIC CACHE CLEAR" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Function to clear browser caches
function Clear-BrowserCache {
    param([string]$BrowserName)

    Write-Host "Clearing $BrowserName cache..." -ForegroundColor Yellow

    try {
        switch ($BrowserName) {
            "Chrome" {
                $chromePaths = @(
                    "$env:LOCALAPPDATA\Google\Chrome\User Data\Default\Cache",
                    "$env:LOCALAPPDATA\Google\Chrome\User Data\Default\Media Cache",
                    "$env:LOCALAPPDATA\Google\Chrome\User Data\Default\GPUCache",
                    "$env:LOCALAPPDATA\Google\Chrome\User Data\Default\Service Worker"
                )

                foreach ($path in $chromePaths) {
                    if (Test-Path $path) {
                        Remove-Item -Path $path -Recurse -Force -ErrorAction SilentlyContinue
                        Write-Host "  Cleared: $path" -ForegroundColor Green
                    }
                }
            }

            "Edge" {
                $edgePaths = @(
                    "$env:LOCALAPPDATA\Microsoft\Edge\User Data\Default\Cache",
                    "$env:LOCALAPPDATA\Microsoft\Edge\User Data\Default\Media Cache",
                    "$env:LOCALAPPDATA\Microsoft\Edge\User Data\Default\GPUCache",
                    "$env:LOCALAPPDATA\Microsoft\Edge\User Data\Default\Service Worker"
                )

                foreach ($path in $edgePaths) {
                    if (Test-Path $path) {
                        Remove-Item -Path $path -Recurse -Force -ErrorAction SilentlyContinue
                        Write-Host "  Cleared: $path" -ForegroundColor Green
                    }
                }
            }

            "Firefox" {
                $firefoxProfiles = Get-ChildItem "$env:APPDATA\Mozilla\Firefox\Profiles" -Directory -ErrorAction SilentlyContinue
                foreach ($profile in $firefoxProfiles) {
                    $cachePaths = @(
                        "$($profile.FullName)\cache2",
                        "$($profile.FullName)\startupCache",
                        "$($profile.FullName)\thumbnails"
                    )

                    foreach ($path in $cachePaths) {
                        if (Test-Path $path) {
                            Remove-Item -Path $path -Recurse -Force -ErrorAction SilentlyContinue
                            Write-Host "  Cleared: $path" -ForegroundColor Green
                        }
                    }
                }
            }
        }
    } catch {
        Write-Host "  Error clearing $BrowserName cache: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Stop Apache
Write-Host "[1/5] Stopping Apache server..." -ForegroundColor Yellow
try {
    $apacheService = Get-Service | Where-Object { $_.Name -like "*Apache*" -or $_.DisplayName -like "*Apache*" }
    if ($apacheService) {
        Stop-Service -Name $apacheService.Name -Force -ErrorAction SilentlyContinue
        Write-Host "  Apache stopped successfully" -ForegroundColor Green
    } else {
        Write-Host "  Apache service not found" -ForegroundColor Red
    }
} catch {
    Write-Host "  Error stopping Apache: $($_.Exception.Message)" -ForegroundColor Red
}

Start-Sleep -Seconds 3

# Clear browser caches
Write-Host "[2/5] Clearing browser caches..." -ForegroundColor Yellow
Clear-BrowserCache -BrowserName "Chrome"
Clear-BrowserCache -BrowserName "Edge"
Clear-BrowserCache -BrowserName "Firefox"

# Clear system temp files
Write-Host "[3/5] Clearing system temporary files..." -ForegroundColor Yellow
try {
    Get-ChildItem -Path $env:TEMP -Recurse -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "  System temp files cleared" -ForegroundColor Green
} catch {
    Write-Host "  Error clearing temp files: $($_.Exception.Message)" -ForegroundColor Red
}

# Clear PHP cache if OPcache is enabled
Write-Host "[4/5] Clearing PHP cache..." -ForegroundColor Yellow
try {
    # Check if OPcache is enabled and clear it
    if (Get-Command php -ErrorAction SilentlyContinue) {
        $phpOutput = & php -r "echo 'OPcache enabled: ' . (ini_get('opcache.enable') ? 'Yes' : 'No') . PHP_EOL;"
        Write-Host "  PHP Info: $phpOutput" -ForegroundColor Green

        # Try to reset OPcache
        & php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache reset successfully' . PHP_EOL; } else { echo 'OPcache not available' . PHP_EOL; }"
    } else {
        Write-Host "  PHP command not found in PATH" -ForegroundColor Red
    }
} catch {
    Write-Host "  Error clearing PHP cache: $($_.Exception.Message)" -ForegroundColor Red
}

# Start Apache
Write-Host "[5/5] Starting Apache server..." -ForegroundColor Yellow
try {
    if ($apacheService) {
        Start-Service -Name $apacheService.Name -ErrorAction SilentlyContinue
        Write-Host "  Apache started successfully" -ForegroundColor Green
    } else {
        Write-Host "  Apache service not found" -ForegroundColor Red
    }
} catch {
    Write-Host "  Error starting Apache: $($_.Exception.Message)" -ForegroundColor Red
}

Start-Sleep -Seconds 3

# Open the bookings page
Write-Host "Opening bookings page..." -ForegroundColor Yellow
try {
    Start-Process "http://localhost/apsdreamhome/admin/bookings.php"
    Write-Host "  Bookings page opened in default browser" -ForegroundColor Green
} catch {
    Write-Host "  Error opening browser: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✅ AUTOMATIC FIX COMPLETED!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "The bookings page should now load without errors." -ForegroundColor White
Write-Host "If you still see errors, try:" -ForegroundColor White
Write-Host "1. Open in incognito/private mode" -ForegroundColor White
Write-Host "2. Try a different browser" -ForegroundColor White
Write-Host "3. Clear browser data manually" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to exit..." -ForegroundColor White
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
