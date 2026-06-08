$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# Use test_login=1 for admin
$r = Invoke-WebRequest -Uri "$base/admin/login?test_login=1" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "Admin login with test_login: $($r.StatusCode)"

$r = Invoke-WebRequest -Uri "$base/admin/kyc" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "Admin KYC after test_login: $($r.StatusCode)"
$idx = $r.Content.IndexOf('KYC')
if ($idx -ge 0) { Write-Host "Found 'KYC' at $idx" }
$idx = $r.Content.IndexOf('KYC Requests')
if ($idx -ge 0) { Write-Host "Found 'KYC Requests' at $idx" }
$idx = $r.Content.IndexOf('KYC Requests')
if ($idx -lt 0) {
    Write-Host "First 500 chars:"
    Write-Host $r.Content.Substring(0, 500)
}
