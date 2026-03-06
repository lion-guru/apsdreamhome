# APS Dream Home - Autonomous Architecture Verification
# Automated architecture verification for APS Dream Home project

# Check if running in correct directory
if (-not (Test-Path "app\Http\Controllers")) {
    Write-Host "❌ Error: Not in APS Dream Home directory" -ForegroundColor Red
    exit 1
}

Write-Host "🏗️ APS DREAM HOME - AUTONOMOUS ARCHITECTURE VERIFICATION" -ForegroundColor Green
Write-Host "===================================================" -ForegroundColor Green

# Initialize counters
$architectureScore = 0
$totalChecks = 0
$issues = @()

# Check MVC Structure
Write-Host "🔍 CHECKING MVC STRUCTURE..." -ForegroundColor Yellow

# Controllers
$totalChecks++
if (Test-Path "app\Http\Controllers") {
    $controllerFiles = Get-ChildItem "app\Http\Controllers\*.php" -ErrorAction SilentlyContinue
    if ($controllerFiles.Count -gt 0) {
        Write-Host "   ✅ Controllers: Directory exists with $($controllerFiles.Count) files" -ForegroundColor Green
        $architectureScore++
    } else {
        Write-Host "   ❌ Controllers: Directory exists but no PHP files" -ForegroundColor Red
        $issues += "Controllers directory empty"
    }
} else {
    Write-Host "   ❌ Controllers: Directory missing" -ForegroundColor Red
    $issues += "Controllers directory missing"
}

# Models
$totalChecks++
if (Test-Path "app\Models") {
    $modelFiles = Get-ChildItem "app\Models\*.php" -ErrorAction SilentlyContinue
    if ($modelFiles.Count -gt 0) {
        Write-Host "   ✅ Models: Directory exists with $($modelFiles.Count) files" -ForegroundColor Green
        $architectureScore++
    } else {
        Write-Host "   ❌ Models: Directory exists but no PHP files" -ForegroundColor Red
        $issues += "Models directory empty"
    }
} else {
    Write-Host "   ❌ Models: Directory missing" -ForegroundColor Red
    $issues += "Models directory missing"
}

# Views
$totalChecks++
if (Test-Path "app\views") {
    $viewFiles = Get-ChildItem "app\views\*.php" -Recurse -ErrorAction SilentlyContinue
    if ($viewFiles.Count -gt 0) {
        Write-Host "   ✅ Views: Directory exists with $($viewFiles.Count) files" -ForegroundColor Green
        $architectureScore++
    } else {
        Write-Host "   ❌ Views: Directory exists but no PHP files" -ForegroundColor Red
        $issues += "Views directory empty"
    }
} else {
    Write-Host "   ❌ Views: Directory missing" -ForegroundColor Red
    $issues += "Views directory missing"
}

# Check Security Architecture
Write-Host "🔒 CHECKING SECURITY ARCHITECTURE..." -ForegroundColor Yellow

$securityFiles = @(
    "app\Helpers\SecurityHelper.php",
    "app\Core\Middleware\AuthMiddleware.php",
    "app\Core\Middleware\CsrfMiddleware.php"
)

foreach ($file in $securityFiles) {
    $totalChecks++
    if (Test-Path $file) {
        Write-Host "   ✅ Security: $(Split-Path $file -Leaf)" -ForegroundColor Green
        $architectureScore++
    } else {
        Write-Host "   ❌ Security: $(Split-Path $file -Leaf) missing" -ForegroundColor Red
        $issues += "Security file missing: $(Split-Path $file -Leaf)"
    }
}

# Check Database Configuration
Write-Host "🗄️ CHECKING DATABASE CONFIGURATION..." -ForegroundColor Yellow

$totalChecks++
if (Test-Path "config\database.php") {
    Write-Host "   ✅ Database: Configuration file exists" -ForegroundColor Green
    $architectureScore++
} else {
    Write-Host "   ❌ Database: Configuration file missing" -ForegroundColor Red
    $issues += "Database configuration missing"
}

# Check Documentation
Write-Host "📚 CHECKING DOCUMENTATION..." -ForegroundColor Yellow

$totalChecks++
if (Test-Path "docs\PROJECT_COMPLETE_DOCUMENTATION.md") {
    Write-Host "   ✅ Documentation: Complete documentation exists" -ForegroundColor Green
    $architectureScore++
} else {
    Write-Host "   ❌ Documentation: Complete documentation missing" -ForegroundColor Red
    $issues += "Complete documentation missing"
}

# Calculate Score
$scorePercentage = if ($totalChecks -gt 0) { [math]::Round(($architectureScore / $totalChecks) * 100, 2) } else { 0 }

# Display Results
Write-Host "`n📊 ARCHITECTURE VERIFICATION RESULTS:" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Total Checks: $totalChecks" -ForegroundColor White
Write-Host "Passed Checks: $architectureScore" -ForegroundColor White
Write-Host "Failed Checks: $($totalChecks - $architectureScore)" -ForegroundColor White
Write-Host "Architecture Score: $scorePercentage%" -ForegroundColor $(if ($scorePercentage -ge 80) { "Green" } elseif ($scorePercentage -ge 60) { "Yellow" } else { "Red" })

if ($issues.Count -gt 0) {
    Write-Host "`n⚠️ ISSUES FOUND:" -ForegroundColor Yellow
    foreach ($issue in $issues) {
        Write-Host "   - $issue" -ForegroundColor Red
    }
}

# Generate Report
$report = @{
    timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    total_checks = $totalChecks
    passed_checks = $architectureScore
    failed_checks = $totalChecks - $architectureScore
    architecture_score = $scorePercentage
    issues = $issues
}

$report | ConvertTo-Json -Depth 3 | Out-File -FilePath "architecture_verification_report.json" -Encoding utf8

Write-Host "`n✅ AUTONOMOUS ARCHITECTURE VERIFICATION COMPLETE" -ForegroundColor Green
Write-Host "📄 Report saved to: architecture_verification_report.json" -ForegroundColor Green

# Determine Status
if ($scorePercentage -ge 90) {
    Write-Host "`n🎉 ARCHITECTURE STATUS: EXCELLENT" -ForegroundColor Green
} elseif ($scorePercentage -ge 80) {
    Write-Host "`n👍 ARCHITECTURE STATUS: GOOD" -ForegroundColor Yellow
} elseif ($scorePercentage -ge 60) {
    Write-Host "`n⚠️ ARCHITECTURE STATUS: NEEDS IMPROVEMENT" -ForegroundColor Red
} else {
    Write-Host "`n❌ ARCHITECTURE STATUS: CRITICAL" -ForegroundColor Red
}
