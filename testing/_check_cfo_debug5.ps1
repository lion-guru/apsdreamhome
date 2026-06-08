$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
$body = "csrf_token=$csrf&identity=admin@apsdreamhome.com&password=Test1234"
Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null

$r = Invoke-WebRequest -Uri "$base/admin/cfo-dashboard" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$content = $r.Content
Write-Host "Content length: $($content.Length)"
$idx = $content.IndexOf('DEBUG START')
if ($idx -ge 0) {
    Write-Host "Found DEBUG START at $idx"
} else {
    Write-Host "NOT FOUND: DEBUG START"
}
$idx = $content.IndexOf('Top Performers')
if ($idx -ge 0) {
    Write-Host "Found Top Performers at $idx"
} else {
    Write-Host "NOT FOUND: Top Performers"
}
$idx = $content.IndexOf('CFO Dashboard')
if ($idx -ge 0) {
    Write-Host "Found CFO Dashboard at $idx"
} else {
    Write-Host "NOT FOUND: CFO Dashboard"
}
# Show first 200 chars
Write-Host "First 200 chars:"
Write-Host $content.Substring(0, [Math]::Min(200, $content.Length))
