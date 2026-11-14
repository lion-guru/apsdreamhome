# OpenCode API Key 1-Click Switcher Script
# Keys come from environment variables (gitignored .env) — set them there or in your shell:
#   $env:OPENCODE_ZEN_KEY_TECHGURU = "key1..."
#   $env:OPENCODE_ZEN_KEY_APS      = "key2..."
# SECURITY (2026-11-14): the live keys previously hardcoded here were exposed to disk and
# assistant session context; rotate them in the OpenCode console before re-configuring.

$authPath = "$env:USERPROFILE\.local\share\opencode\auth.json"

$keyTechguru = $env:OPENCODE_ZEN_KEY_TECHGURU
$keyAps      = $env:OPENCODE_ZEN_KEY_APS

if ([string]::IsNullOrWhiteSpace($keyTechguru) -or [string]::IsNullOrWhiteSpace($keyAps)) {
    Write-Host "[ERROR] OPENCODE_ZEN_KEY_TECHGURU / OPENCODE_ZEN_KEY_APS not set." -ForegroundColor Red
    Write-Host "Set them as environment variables (or in .env) after rotating the exposed keys." -ForegroundColor Yellow
    exit 1
}

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
Write-Host " [KEY]      (hidden - not printed)" -ForegroundColor DarkCyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "OpenCode is now configured with: $targetName" -ForegroundColor Green
Write-Host ""
