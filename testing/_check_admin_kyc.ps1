$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
$body = "csrf_token=$csrf&identity=admin@apsdreamhome.com&password=Test1234"
Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null

Write-Host "=== Admin KYC List ===" -ForegroundColor Cyan
$r = Invoke-WebRequest -Uri "$base/admin/kyc" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "HTTP: $($r.StatusCode)"
Write-Host "Has KYC Requests: $($r.Content -match 'KYC Requests')"

Write-Host "`n=== Admin KYC Pending ===" -ForegroundColor Cyan
$r = Invoke-WebRequest -Uri "$base/admin/kyc/pending" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "HTTP: $($r.StatusCode)"
Write-Host "Has Pending: $($r.Content -match 'Pending KYC')"
