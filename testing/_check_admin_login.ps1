$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
Write-Host "CSRF: $csrf"
$body = "csrf_token=$csrf&identity=admin@apsdreamhome.com&password=Test1234"
$resp = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "Login POST StatusCode: $($resp.StatusCode)"
Write-Host "Login POST Location: $($resp.Headers.Location)"
Write-Host "Session cookies: $($session.Cookies.Count)"

# Now try CEO
$r = Invoke-WebRequest -Uri "$base/admin/ceo-dashboard" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "CEO StatusCode: $($r.StatusCode)"

# Now try CFO
$r = Invoke-WebRequest -Uri "$base/admin/cfo-dashboard" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "CFO StatusCode: $($r.StatusCode)"
