$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# Use test_login=1 for admin
$r = Invoke-WebRequest -Uri "$base/admin/login?test_login=1" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue

Write-Host "=== Admin KYC Pending ===" -ForegroundColor Cyan
$r = Invoke-WebRequest -Uri "$base/admin/kyc/pending" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "HTTP: $($r.StatusCode)"
$idx = $r.Content.IndexOf('Pending KYC')
if ($idx -ge 0) { Write-Host "Found 'Pending KYC' at $idx" }
$idx = $r.Content.IndexOf('No pending')
if ($idx -ge 0) { Write-Host "Found 'No pending' at $idx" }

Write-Host "`n=== Admin KYC Show (ID=1) ===" -ForegroundColor Cyan
$r = Invoke-WebRequest -Uri "$base/admin/kyc/show/1" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "HTTP: $($r.StatusCode)"
$idx = $r.Content.IndexOf('KYC Request')
if ($idx -ge 0) { Write-Host "Found 'KYC Request' at $idx" }
$idx = $r.Content.IndexOf('PAN Card')
if ($idx -ge 0) { Write-Host "Found 'PAN Card' at $idx" }
$idx = $r.Content.IndexOf('Aadhaar Front')
if ($idx -ge 0) { Write-Host "Found 'Aadhaar Front' at $idx" }
