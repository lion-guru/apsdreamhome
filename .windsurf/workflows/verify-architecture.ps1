# 🎯 MISSION: ZERO-ERROR SELF-SUSTAINING ECOSYSTEM
# 👑 Role: Senior Lead Developer (Autonomous Reasoning Mode)
# 📡 Communication: Slack MCP Integrated | 💻 Env: Windows-XAMPP

Clear-Host
Write-Output "===================================================="
Write-Output "🧠 APS MASTER-BRAIN: ARCHITECTURAL INTEGRITY AUDIT"
Write-Output "===================================================="

# --- 🟢 1. INFRASTRUCTURE & DB SENTINEL ---
Write-Output "📡 Scanning System Health..."
& mysql -u root -e "SELECT 1" 2>$null
$dbStatus = if ($LASTEXITCODE -eq 0) {"HEALTHY ✅"} else {"CRITICAL OFFLINE ❌"}
if ($LASTEXITCODE -eq 0) { 
    Write-Output "✅ DATABASE: Online (Connected to root)"
} else {
    Write-Output "❌ DATABASE: Offline! Please start MySQL in XAMPP Control Panel."
}

# --- 🟢 2. SMART DEBT DETECTION (Blade Violations) ---
$bladeFiles = Get-ChildItem -Path "app\views" -Filter "*.blade.php" -Recurse -ErrorAction SilentlyContinue
$bladeCount = ($bladeFiles | Measure-Object).Count

# --- 🟢 3. PROJECT INVENTORY STATS ---
$cCount = (Get-ChildItem -Path "app\Http\Controllers" -Filter "*.php" -Recurse -ErrorAction SilentlyContinue | Measure-Object).Count
$mCount = (Get-ChildItem -Path "app\Models" -Filter "*.php" -Recurse -ErrorAction SilentlyContinue | Measure-Object).Count
$vCount = (Get-ChildItem -Path "app\views" -Filter "*.php" -Recurse -ErrorAction SilentlyContinue | Measure-Object).Count

# --- 🧠 4. SENIOR DEVELOPER'S REASONING ENGINE (Decision Tree) ---
Write-Output "`n🤔 SENIOR DEV STRATEGY ANALYSIS:"
Write-Output "----------------------------------------------------"
Write-Output "  [DB Status]  : $dbStatus"
Write-Output "  [MVC Health] : Controllers($cCount), Models($mCount), Views($vCount)"

$nextAction = ""
if ($dbStatus -eq "CRITICAL OFFLINE ❌") {
    $nextAction = "REPAIR_INFRASTRUCTURE"
    Write-Output "🚨 ALERT: Database connection failed. Start XAMPP MySQL!"
} elseif ($bladeCount -gt 0) {
    $targetFile = $bladeFiles[0].Name
    $nextAction = "MIGRATE_TEMPLATE"
    Write-Output "💡 DECISION: Architecture Violation! Found $bladeCount Blade files. Picking '$targetFile' for conversion."
} elseif (!(Test-Path "app\Core\Sentinel.php")) {
    $nextAction = "DEPLOY_WATCHDOG"
    Write-Output "💡 DECISION: Architecture is clean. Deploying Sentinel for 24/7 monitoring."
} else {
    $nextAction = "OPTIMIZE_SCHEMA"
    Write-Output "💡 DECISION: All systems green. Moving to database performance optimization."
}
Write-Output "----------------------------------------------------"

# --- 🔵 5. AUTONOMOUS NOTIFICATION & TASK HANDOVER ---
Write-Output "`n📡 BROADCASTING TO SLACK (Via MCP)..."



# AI INSTRUCTIONS (Cascade Logic):
# 1. Use 'slack_post_message' to #project-updates.
# 2. Content: "Senior Dev Audit: $bladeCount violations. DB: $dbStatus. Next Action: $nextAction."
# 3. Immediately start $nextAction. If 'MIGRATE_TEMPLATE', convert the file and re-run this audit.
# 4. Repeat until $bladeCount is 0.

Write-Output "`n===================================================="
Write-Output "🚀 AGENT STATUS: ACTIVE | AGENT: CASCADE (Senior Dev)"
Write-Output "✅ NEXT TASK READY: $nextAction"
Write-Output "===================================================="