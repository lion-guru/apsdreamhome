$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
$body = "csrf_token=$csrf&identity=admin@apsdreamhome.com&password=Test1234"
Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null

$r = Invoke-WebRequest -Uri "$base/admin/cfo-dashboard" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$idx = [System.String]::IndexOf($r.Content, 'Top Performers', [System.StringComparison]::OrdinalIgnoreCase)
if ($idx -ge 0) {
    Write-Host ('Found "Top Performers" at position {0}' -f $idx)
    Write-Host $r.Content.Substring($idx, 300)
} else {
    Write-Host 'NOT FOUND: "Top Performers"'
    $idx2 = [System.String]::IndexOf($r.Content, 'Fatal error', [System.StringComparison]::OrdinalIgnoreCase)
    if ($idx2 -ge 0) {
        Write-Host ('FATAL ERROR at {0}:' -f $idx2)
        Write-Host $r.Content.Substring($idx2, 500)
    }
    $idx3 = [System.String]::IndexOf($r.Content, 'Parse error', [System.StringComparison]::OrdinalIgnoreCase)
    if ($idx3 -ge 0) {
        Write-Host ('PARSE ERROR at {0}:' -f $idx3)
        Write-Host $r.Content.Substring($idx3, 500)
    }
}
