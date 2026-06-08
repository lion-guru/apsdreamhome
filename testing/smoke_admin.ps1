$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

# Test admin dashboards
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
$body = "csrf_token=$csrf&identity=admin@apsdreamhome.com&password=Test1234"
Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null

Write-Host "=== CEO Dashboard ===" -ForegroundColor Cyan
$r = Invoke-WebRequest -Uri "$base/admin/ceo-dashboard" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "HTTP: $($r.StatusCode)"
Write-Host "Has Top Performers: $($r.Content -match 'Top Performers')"
Write-Host "Has trophy icon: $($r.Content -match 'fa-trophy')"

Write-Host "`n=== CFO Dashboard ===" -ForegroundColor Cyan
$r = Invoke-WebRequest -Uri "$base/admin/cfo-dashboard" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "HTTP: $($r.StatusCode)"
Write-Host "Has Top Performers: $($r.Content -match 'Top Performers')"
Write-Host "Has trophy icon: $($r.Content -match 'fa-trophy')"
