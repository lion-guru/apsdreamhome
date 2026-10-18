# OpenCode API Key 1-Click Switcher Script
$authPath = "$env:USERPROFILE\.local\share\opencode\auth.json"

$keyTechguru = "oc_sk_e45303e6e06c_dlgfYvLfSFtkaZa5YTcOiy0upxQGrpjt"
$keyAps      = "oc_sk_922b42946876_zyXpUtTi8K4KWjFRE5XI_lDx34ZX2wRQ"

if (-not (Test-Path $authPath)) {
    Write-Host "[ERROR] auth.json not found at: $authPath" -ForegroundColor Red
    pause
    exit 1
}

$content = Get-Content -Raw -Path $authPath
$data = $content | ConvertFrom-Json

$currentKey = $data.opencode.key

Clear-Host
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "            OPENCODE API KEY 1-CLICK SWITCHER                   " -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Cyan

$mode = $args[0]

if ($mode -eq "techguru") {
    $targetKey = $keyTechguru
    $targetName = "techguruabhay@gmail.com"
} elseif ($mode -eq "aps") {
    $targetKey = $keyAps
    $targetName = "apsdreamhomes44@gmail.com"
} else {
    # Auto-toggle mode
    if ($currentKey -eq $keyTechguru) {
        $targetKey = $keyAps
        $targetName = "apsdreamhomes44@gmail.com"
    } else {
        $targetKey = $keyTechguru
        $targetName = "techguruabhay@gmail.com"
    }
}

if ($currentKey -eq $keyTechguru) {
    Write-Host " [PREVIOUS] Active Account: techguruabhay@gmail.com" -ForegroundColor Gray
} elseif ($currentKey -eq $keyAps) {
    Write-Host " [PREVIOUS] Active Account: apsdreamhomes44@gmail.com" -ForegroundColor Gray
} else {
    Write-Host " [PREVIOUS] Active Key: Custom / Unknown Key" -ForegroundColor Gray
}

$data.opencode.key = $targetKey
$newJson = $data | ConvertTo-Json -Depth 10
Set-Content -Path $authPath -Value $newJson -Encoding UTF8

Write-Host "----------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [SUCCESS]  NOW ACTIVE ACCOUNT: " -NoNewline -ForegroundColor Green
Write-Host $targetName -ForegroundColor Yellow
Write-Host " [KEY]      $($targetKey.Substring(0, 12))...$($targetKey.Substring($targetKey.Length - 8))" -ForegroundColor DarkCyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "OpenCode is now configured with: $targetName" -ForegroundColor Green
Write-Host ""
