$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$form = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
$body = "csrf_token=$csrf&identity=admin@apsdreamhome.com&password=Test1234"
Invoke-WebRequest -Uri "$base/admin/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null

$r = Invoke-WebRequest -Uri "$base/admin/cfo-dashboard" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
$content = $r.Content
$idx = $content.IndexOf('Top Performers')
if ($idx -ge 0) {
    Write-Host ("Found 'Top Performers' at position {0}" -f $idx)
    Write-Host $content.Substring($idx, 300)
} else {
    Write-Host 'NOT FOUND: "Top Performers"'
    $idx2 = $content.IndexOf('Fatal error')
    if ($idx2 -ge 0) {
        Write-Host ("FATAL ERROR at {0}:" -f $idx2)
        Write-Host $content.Substring($idx2, 500)
    }
    $idx3 = $content.IndexOf('Parse error')
    if ($idx3 -ge 0) {
        Write-Host ("PARSE ERROR at {0}:" -f $idx3)
        Write-Host $content.Substring($idx3, 500)
    }
    $idx4 = $content.IndexOf('Warning')
    if ($idx4 -ge 0) {
        Write-Host ("WARNING at {0}:" -f $idx4)
        Write-Host $content.Substring($idx4, 200)
    }
}
