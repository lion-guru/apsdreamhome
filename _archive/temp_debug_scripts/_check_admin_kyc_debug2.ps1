$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
$body = "csrf_token=$csrf&identity=admin@apsdreamhome.com&password=Test1234"
Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null

$r = Invoke-WebRequest -Uri "$base/admin/kyc" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host ("Length: {0}" -f $r.Content.Length)
$idx = $r.Content.IndexOf('KYC')
if ($idx -ge 0) { Write-Host ("Found 'KYC' at {0}: {1}" -f $idx, $r.Content.Substring($idx, 100)) }
$idx = $r.Content.IndexOf('Fatal error')
if ($idx -ge 0) { Write-Host ("FATAL ERROR at {0}: {1}" -f $idx, $r.Content.Substring($idx, 300)) }
$idx = $r.Content.IndexOf('Parse error')
if ($idx -ge 0) { Write-Host ("PARSE ERROR at {0}: {1}" -f $idx, $r.Content.Substring($idx, 300)) }
$idx = $r.Content.IndexOf('Error')
if ($idx -ge 0) { Write-Host ("ERROR at {0}: {1}" -f $idx, $r.Content.Substring($idx, 200)) }
Write-Host "First 500 chars:"
Write-Host $r.Content.Substring(0, [Math]::Min(500, $r.Content.Length))
