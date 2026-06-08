$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
$body = "csrf_token=$csrf&identity=admin@apsdreamhome.com&password=Test1234"
Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null

# Test CFO with no redirect following
$r = Invoke-WebRequest -Uri "$base/admin/cfo-dashboard" -WebSession $session -Method GET -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
Write-Host "CFO StatusCode: $($r.StatusCode)"
Write-Host "CFO Headers: $($r.Headers.Location)"
Write-Host "CFO Content length: $($r.Content.Length)"

# Test CEO with no redirect following
$r = Invoke-WebRequest -Uri "$base/admin/ceo-dashboard" -WebSession $session -Method GET -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
Write-Host "CEO StatusCode: $($r.StatusCode)"
Write-Host "CEO Headers: $($r.Headers.Location)"
Write-Host "CEO Content length: $($r.Content.Length)"
