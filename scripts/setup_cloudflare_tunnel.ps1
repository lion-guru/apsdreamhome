# Cloudflare Tunnel Automated Setup Script for APS Dream Home
# Run as Administrator once you have your Cloudflare Tunnel Token.

param(
    [Parameter(Mandatory=$false)]
    [string]$TunnelToken
)

$cloudflaredPath = "C:\Program Files (x86)\cloudflared\cloudflared.exe"

if (-not (Test-Path $cloudflaredPath)) {
    Write-Host "[-] cloudflared.exe not found at $cloudflaredPath" -ForegroundColor Red
    exit 1
}

Write-Host "[+] Found cloudflared: $cloudflaredPath" -ForegroundColor Green
& $cloudflaredPath --version

# Verify Apache Port 80
$apacheConn = Get-NetTCPConnection -LocalPort 80 -ErrorAction SilentlyContinue
if ($apacheConn) {
    Write-Host "[+] Local Apache Web Server is running on Port 80." -ForegroundColor Green
} else {
    Write-Host "[!] Warning: Port 80 not active. Please start Apache in XAMPP." -ForegroundColor Yellow
}

if ([string]::IsNullOrWhiteSpace($TunnelToken)) {
    Write-Host "`nUsage:" -ForegroundColor Cyan
    Write-Host ".\scripts\setup_cloudflare_tunnel.ps1 -TunnelToken '<YOUR_CLOUDFLARE_TUNNEL_TOKEN>'" -ForegroundColor Yellow
    Write-Host "`nHow to get the token:" -ForegroundColor Cyan
    Write-Host "1. Login to https://one.dash.cloudflare.com/"
    Write-Host "2. Go to Networks -> Tunnels -> Create a tunnel"
    Write-Host "3. Name it 'apsdreamhome-local' and choose Windows"
    Write-Host "4. Copy the token command shown in Cloudflare dashboard and paste the token here!"
    exit 0
}

Write-Host "`n[*] Installing and starting Cloudflare Tunnel Windows Service..." -ForegroundColor Cyan
& $cloudflaredPath service install $TunnelToken

Start-Sleep -Seconds 3
$svc = Get-Service cloudflared -ErrorAction SilentlyContinue
if ($svc -and $svc.Status -eq 'Running') {
    Write-Host "[+] SUCCESS: Cloudflare Tunnel service is RUNNING 24x7 in the background!" -ForegroundColor Green
} else {
    Write-Host "[!] Service status: $($svc.Status). Attempting to start..." -ForegroundColor Yellow
    Start-Service cloudflared -ErrorAction SilentlyContinue
}
