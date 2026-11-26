# ngrok Auto-Start Script — APS Dream Home
# Runs on Windows login via Startup folder shortcut.
# Starts ngrok tunnel for localhost:80 (XAMPP) if not already running.
# Idempotent: never starts a duplicate tunnel.

$ngrokExe = "C:\Users\abhay\AppData\Local\ngrok\ngrok.exe"
$logDir = "C:\Users\abhay\AppData\Local\Temp\opencode"
$logOut = Join-Path $logDir "ngrok_autostart.log"
$logErr = Join-Path $logDir "ngrok_autostart.err"

if (-not (Test-Path $ngrokExe)) {
    "ngrok executable not found: $ngrokExe" | Out-File $logErr -Append
    exit 1
}

if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir -Force | Out-Null
}

# Idempotency: do not start a second ngrok process
$existing = Get-Process -Name ngrok -ErrorAction SilentlyContinue
if ($existing) {
    "ngrok already running (PID $($existing.Id -join ',')). Skipping." | Out-File $logOut -Append
    exit 0
}

# Start ngrok hidden, forwarding localhost:80
$p = Start-Process -FilePath $ngrokExe `
    -ArgumentList "http","80","--log=stdout","--log-level=info" `
    -WindowStyle Hidden `
    -RedirectStandardOutput $logOut `
    -RedirectStandardError $logErr `
    -PassThru

Start-Sleep -Seconds 6
$alive = Get-Process -Id $p.Id -ErrorAction SilentlyContinue
if ($alive) {
    "ngrok started at $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') PID $($p.Id) -> http://localhost:80" | Out-File $logOut -Append
} else {
    "ngrok failed to stay running at $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" | Out-File $logErr -Append
}
