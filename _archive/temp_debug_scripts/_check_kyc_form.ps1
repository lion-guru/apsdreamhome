$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
$body = "csrf_token=$csrf&identity=customer1@apsdreamhome.com&password=Test1234"
Invoke-WebRequest -Uri "$base/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null

$r = Invoke-WebRequest -Uri "$base/user/kyc" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
Write-Host "HTTP: $($r.StatusCode)"
$checks = @('PAN Card Document', 'Aadhaar Front', 'Aadhaar Back', 'Date of Birth', 'pan_document', 'aadhaar_front_document', 'aadhaar_back_document')
foreach ($c in $checks) {
    $found = $r.Content -match $c
    Write-Host ("  {0}: {1}" -f $c, $found)
}
