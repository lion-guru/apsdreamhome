# OpenCode API Key 1-Click Switcher Script
# Toggles between techguruabhay@gmail.com and apsdreamhomes44@gmail.com
# Compatible with both OpenCode v1 (auth.json) and OpenCode v2 (opencode auth switch / opencode.db)

$authPath = "$env:USERPROFILE\.local\share\opencode\auth.json"

# 1. Resolve Techguru Key
$keyTechguru = $env:OPENCODE_ZEN_KEY_TECHGURU
if ([string]::IsNullOrWhiteSpace($keyTechguru)) {
    $keyTechguru = [System.Environment]::GetEnvironmentVariable('OPENCODE_ZEN_KEY_TECHGURU', 'User')
}
if ([string]::IsNullOrWhiteSpace($keyTechguru)) {
    $keyTechguru = "oc_sk_e907f3131ad5_9wj4vNg59md_5U6wWOlniZHMa6de7FuE"
}

# 2. Resolve APS Key
$keyAps = $env:OPENCODE_ZEN_KEY_APS
if ([string]::IsNullOrWhiteSpace($keyAps)) {
    $keyAps = [System.Environment]::GetEnvironmentVariable('OPENCODE_ZEN_KEY_APS', 'User')
}
if ([string]::IsNullOrWhiteSpace($keyAps)) {
    $keyAps = "oc_sk_922b42946876_zyXpUtTi8K4KWjFRE5XI_lDx34ZX2wRQ"
}

# 3. Read current key from auth.json if present
$currentKey = ""
if (Test-Path $authPath) {
    try {
        $content = Get-Content -Raw -Path $authPath
        $data = $content | ConvertFrom-Json
        $currentKey = $data.opencode.key
    } catch {
        $data = [PSCustomObject]@{}
    }
} else {
    $data = [PSCustomObject]@{}
}

Clear-Host
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "            OPENCODE API KEY 1-CLICK SWITCHER                   " -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Cyan

$mode = $args[0]

if ($mode -eq "techguru") {
    $targetKey = $keyTechguru
    $targetName = "techguruabhay@gmail.com"
    $targetCredId = "opencode-techguru"
} elseif ($mode -eq "aps") {
    $targetKey = $keyAps
    $targetName = "apsdreamhomes44@gmail.com"
    $targetCredId = "opencode-aps"
} else {
    # Auto-toggle mode
    if ($currentKey -eq $keyTechguru) {
        $targetKey = $keyAps
        $targetName = "apsdreamhomes44@gmail.com"
        $targetCredId = "opencode-aps"
    } else {
        $targetKey = $keyTechguru
        $targetName = "techguruabhay@gmail.com"
        $targetCredId = "opencode-techguru"
    }
}

if ($currentKey -eq $keyTechguru) {
    Write-Host " [PREVIOUS] Active Account: techguruabhay@gmail.com" -ForegroundColor Gray
} elseif ($currentKey -eq $keyAps) {
    Write-Host " [PREVIOUS] Active Account: apsdreamhomes44@gmail.com" -ForegroundColor Gray
} else {
    Write-Host " [PREVIOUS] Active Key: Custom / Unknown Key" -ForegroundColor Gray
}

# 4. Update auth.json for legacy compatibility
if ($null -eq $data.opencode) {
    $data | Add-Member -NotePropertyName "opencode" -NotePropertyValue ([PSCustomObject]@{ type = "api"; key = $targetKey })
} else {
    $data.opencode.key = $targetKey
}
$newJson = $data | ConvertTo-Json -Depth 10
Set-Content -Path $authPath -Value $newJson -Encoding UTF8

# 5. Set OPENCODE_API_KEY permanently in Windows User Environment
try {
    [System.Environment]::SetEnvironmentVariable('OPENCODE_API_KEY', $targetKey, 'User')
    $env:OPENCODE_API_KEY = $targetKey
} catch {}

# 6. Switch active credential in OpenCode v2 via CLI
try {
    $null = opencode auth switch opencode $targetCredId 2>$null
    $null = opencode service restart 2>$null
} catch {}

Write-Host "----------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [SUCCESS]  NOW ACTIVE ACCOUNT: " -NoNewline -ForegroundColor Green
Write-Host $targetName -ForegroundColor Yellow
Write-Host " [STATUS]   OPENCODE_API_KEY set & OpenCode service synchronized!" -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "OpenCode is now active with: $targetName" -ForegroundColor Green
Write-Host ""
