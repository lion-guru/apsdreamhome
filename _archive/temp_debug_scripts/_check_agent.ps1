$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
$body = "csrf_token=$csrf&identity=agent1@apsdreamhome.com&password=Test1234"
Invoke-WebRequest -Uri "$base/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$r = Invoke-WebRequest -Uri "$base/agent/dashboard" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$i = $r.Content.IndexOf('<title>')
$j = $r.Content.IndexOf('</title>')
Write-Host "Title: $($r.Content.Substring($i+7, $j-$i-7))"
Write-Host "Length: $($r.Content.Length)"
Write-Host "Has nav sidebar: $($r.Content -match 'nav-sidebar|sidebar')"
Write-Host "Has login form: $($r.Content -match '<form[^>]*action="/agent/login"')"
