# 🚀 APS DREAM HOME - ON_SAVE HOOK (Auto-Pilot System)
# 🔧 Automatically runs on file save to maintain MVC compliance
# 🛡️ Auto-converts Blade files, fixes security issues, validates architecture

param(
    [Parameter(Mandatory=$true)]
    [string]$FilePath,
    
    [string]$EventType = "save"
)

# --- 🟢 AUTO-PILOT VERIFICATION ---
Write-Host "🔄 Auto-Pilot: File $EventType detected - $FilePath" -ForegroundColor Cyan

# --- 🟢 1. BLADE FILE AUTO-CONVERSION ---
if ($FilePath -match '\.blade\.php$') {
    Write-Host "🔥 Blade file detected! Auto-converting to PHP..." -ForegroundColor Yellow
    
    $phpFile = $FilePath -replace '\.blade\.php$', '.php'
    
    if (!(Test-Path $phpFile)) {
        try {
            # Read Blade content
            $bladeContent = Get-Content $FilePath -Raw
            
            # Convert Blade to PHP
            $phpContent = $bladeContent -replace '@extends\(''([^'']+)''\)', '<?php include "$1.php"; ?>'
            $phpContent = $phpContent -replace '@section\(''([^'']+)''\)', '<?php // Section: $1 ?>'
            $phpContent = $phpContent -replace '@endsection', '<?php // End Section ?>'
            $phpContent = $phpContent -replace '\{\{\s*\$([^}]+)\s*\}\}', '<?php echo htmlspecialchars($$1); ?>'
            $phpContent = $phpContent -replace '@if\(([^)]+)\)', '<?php if($1): ?>'
            $phpContent = $phpContent -replace '@else', '<?php else: ?>'
            $phpContent = $phpContent -replace '@endif', '<?php endif; ?>'
            $phpContent = $phpContent -replace '@foreach\(([^)]+)\)', '<?php foreach($1): ?>'
            $phpContent = $phpContent -replace '@endforeach', '<?php endforeach; ?>'
            $phpContent = $phpContent -replace '@php', '<?php'
            $phpContent = $phpContent -replace 'endphp', '?>'
            
            # Write PHP file
            Set-Content -Path $phpFile -Value $phpContent
            
            # Move Blade to deprecated
            $deprecatedPath = "app\views\_DEPRECATED\$((Split-Path $FilePath -Leaf)).bak"
            if (!(Test-Path "app\views\_DEPRECATED")) {
                New-Item -ItemType Directory -Path "app\views\_DEPRECATED" -Force | Out-Null
            }
            Move-Item $FilePath $deprecatedPath -Force
            
            Write-Host "✅ Auto-converted: $FilePath → $phpFile" -ForegroundColor Green
            Write-Host "📦 Original moved to: $deprecatedPath" -ForegroundColor Gray
            
            # Log the conversion
            $logEntry = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Auto-converted: $FilePath → $phpFile`n"
            Add-Content -Path "logs\autopilot.log" -Value $logEntry -ErrorAction SilentlyContinue
            
        } catch {
            Write-Host "❌ Auto-conversion failed: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

# --- 🟢 2. SECURITY AUTO-FIX ---
if ($FilePath -match 'Controllers.*\.php$') {
    Write-Host "🔒 Scanning for security vulnerabilities..." -ForegroundColor Cyan
    
    try {
        $content = Get-Content $FilePath -Raw
        $fixesApplied = $false
        
        # Fix direct $_POST usage
        if ($content -match '\$_POST\[') {
            $content = $content -replace '\$_POST\[\''([^'']+)''\]', 'Security::sanitize($_POST[''$1''])'
            $content = $content -replace '\$_POST\["([^"]+)"\]', 'Security::sanitize($_POST["$1"])'
            $fixesApplied = $true
        }
        
        # Fix direct $_GET usage
        if ($content -match '\$_GET\[') {
            $content = $content -replace '\$_GET\[\''([^'']+)''\]', 'Security::sanitize($_GET[''$1''])'
            $content = $content -replace '\$_GET\["([^"]+)"\]', 'Security::sanitize($_GET["$1"])'
            $fixesApplied = $true
        }
        
        # Fix direct $_REQUEST usage
        if ($content -match '\$_REQUEST\[') {
            $content = $content -replace '\$_REQUEST\[\''([^'']+)''\]', 'Security::sanitize($_REQUEST[''$1''])'
            $content = $content -replace '\$_REQUEST\["([^"]+)"\]', 'Security::sanitize($_REQUEST["$1"])'
            $fixesApplied = $true
        }
        
        # Add missing security include if needed
        if ($fixesApplied -and $content -notmatch "use.*Security|Security::") {
            $content = "use App\Core\Security;`n`n" + $content
        }
        
        if ($fixesApplied) {
            Set-Content $FilePath -Value $content
            Write-Host "🔧 Security fixes applied to: $FilePath" -ForegroundColor Green
            
            # Log the security fix
            $logEntry = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Security fix: $FilePath`n"
            Add-Content -Path "logs\autopilot.log" -Value $logEntry -ErrorAction SilentlyContinue
        } else {
            Write-Host "✅ No security issues found" -ForegroundColor Green
        }
        
    } catch {
        Write-Host "❌ Security scan failed: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# --- 🟢 3. ARCHITECTURE VALIDATION ---
if ($FilePath -match 'app\\.*\.php$') {
    Write-Host "🏗️ Validating MVC architecture..." -ForegroundColor Cyan
    
    # Check for proper namespace
    $content = Get-Content $FilePath -Raw
    $namespaceIssues = 0
    
    # Controllers should have App\Http\Controllers namespace
    if ($FilePath -match 'Controllers\\.*\.php$' -and $content -notmatch 'namespace App\\Http\\Controllers') {
        Write-Host "⚠️ Missing proper controller namespace" -ForegroundColor Yellow
        $namespaceIssues++
    }
    
    # Models should have App\Models namespace
    if ($FilePath -match 'Models\\.*\.php$' -and $content -notmatch 'namespace App\\Models') {
        Write-Host "⚠️ Missing proper model namespace" -ForegroundColor Yellow
        $namespaceIssues++
    }
    
    # Check for proper class naming
    if ($FilePath -match 'Controllers\\.*\.php$') {
        $expectedClass = "class " + (Split-Path $FilePath -LeafBase) + "Controller"
        if ($content -notmatch [regex]::Escape($expectedClass)) {
            Write-Host "⚠️ Class name doesn't match file name" -ForegroundColor Yellow
            $namespaceIssues++
        }
    }
    
    if ($namespaceIssues -eq 0) {
        Write-Host "✅ MVC architecture validation passed" -ForegroundColor Green
    } else {
        Write-Host "⚠️ Found $namespaceIssues architecture issues" -ForegroundColor Yellow
    }
}

# --- 🟢 4. DATABASE QUERY VALIDATION ---
if ($FilePath -match 'Models\\.*\.php$') {
    Write-Host "🗄️ Validating database queries..." -ForegroundColor Cyan
    
    try {
        $content = Get-Content $FilePath -Raw
        $queryIssues = 0
        
        # Check for direct SQL without prepared statements
        if ($content -match 'mysql_query|mysqli_query' -and $content -notmatch 'prepare|bind') {
            Write-Host "⚠️ Direct SQL queries detected - consider using prepared statements" -ForegroundColor Yellow
            $queryIssues++
        }
        
        # Check for missing PDO error handling
        if ($content -match 'new PDO' -and $content -notmatch 'ATTR_ERRMODE.*ERRMODE_EXCEPTION') {
            Write-Host "⚠️ PDO error mode not set to exception" -ForegroundColor Yellow
            $queryIssues++
        }
        
        if ($queryIssues -eq 0) {
            Write-Host "✅ Database query validation passed" -ForegroundColor Green
        } else {
            Write-Host "⚠️ Found $queryIssues query issues" -ForegroundColor Yellow
        }
        
    } catch {
        Write-Host "❌ Database validation failed: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# --- 🟢 5. PERFORMANCE OPTIMIZATION ---
if ($FilePath -match 'Controllers\\.*\.php$') {
    Write-Host "⚡ Checking performance optimizations..." -ForegroundColor Cyan
    
    try {
        $content = Get-Content $FilePath -Raw
        $performanceIssues = 0
        
        # Check for N+1 query problems
        if ($content -match 'foreach.*get.*\(') {
            Write-Host "⚠️ Possible N+1 query pattern detected" -ForegroundColor Yellow
            $performanceIssues++
        }
        
        # Check for missing caching
        if ($content -match 'SELECT.*FROM.*WHERE' -and $content -notmatch 'cache|Cache') {
            Write-Host "💡 Consider adding caching for database queries" -ForegroundColor Gray
        }
        
        if ($performanceIssues -eq 0) {
            Write-Host "✅ Performance check passed" -ForegroundColor Green
        } else {
            Write-Host "⚠️ Found $performanceIssues performance issues" -ForegroundColor Yellow
        }
        
    } catch {
        Write-Host "❌ Performance check failed: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# --- 🟢 6. AUTO-COMMIT SUGGESTION ---
if ($FilePath -match '\.php$') {
    Write-Host "📝 Suggesting auto-commit..." -ForegroundColor Cyan
    
    # Get git status
    try {
        $gitStatus = git status --porcelain $FilePath 2>$null
        if ($gitStatus) {
            Write-Host "🔄 Changes detected - Ready for commit" -ForegroundColor Green
            
            # Auto-commit suggestion
            $fileName = Split-Path $FilePath -Leaf
            $commitMessage = "[Auto-Fix] $fileName : Autonomous fixes applied"
            
            Write-Host "💡 Suggested commit: $commitMessage" -ForegroundColor Gray
        }
    } catch {
        Write-Host "📝 Git status check skipped" -ForegroundColor Gray
    }
}

# --- 🟢 7. FINAL STATUS ---
Write-Host "🎉 Auto-Pilot cycle completed for: $FilePath" -ForegroundColor Green

# Update monitoring dashboard
try {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $statusData = @{
        timestamp = $timestamp
        file = $FilePath
        event = $EventType
        status = "completed"
    }
    
    if (!(Test-Path "logs")) {
        New-Item -ItemType Directory -Path "logs" -Force | Out-Null
    }
    
    $statusJson = $statusData | ConvertTo-Json -Compress
    Add-Content -Path "logs\autopilot_status.json" -Value $statusJson -ErrorAction SilentlyContinue
    
} catch {
    Write-Host "📊 Status update skipped" -ForegroundColor Gray
}

Write-Host "🤖 APS Dream Home - Auto-Pilot System - Standing by..." -ForegroundColor Cyan
