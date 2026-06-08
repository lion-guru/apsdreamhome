$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='apsdreamhome'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
$base = 'http://localhost/apsdreamhome'

$tests = @(
    @{ Email = 'customer1@apsdreamhome.com'; Path = '/user/dashboard' },
    @{ Email = 'agent1@apsdreamhome.com'; Path = '/agent/dashboard' },
    @{ Email = 'testassociate@example.com'; Path = '/associate/dashboard' },
    @{ Email = 'employee@apsdreamhome.com'; Path = '/employee/dashboard' }
)

foreach ($t in $tests) {
    Write-Host "=== $($t.Email) $($t.Path) ===" -ForegroundColor Cyan
    $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $form = Invoke-WebRequest -Uri "$base/login" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
    $csrf = ([regex]::Match($form.Content, 'name="csrf_token"\s+value="([^"]+)"')).Groups[1].Value
    $body = "csrf_token=$csrf&identity=$($t.Email)&password=Test1234"
    Invoke-WebRequest -Uri "$base/login" -WebSession $session -Method POST -Body $body -ContentType 'application/x-www-form-urlencoded' -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null

    $r = Invoke-WebRequest -Uri "$base$($t.Path)" -WebSession $session -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
    $hasWidget = $r.Content -match 'aps-cp-progress'
    $hasCard = $r.Content -match 'aps-cp-card'
    $hasLogin = $r.Content -match 'APS Dream Home</title>' -and $r.Content -match 'Login'
    Write-Host "  HTTP: $($r.StatusCode) widget=$hasWidget card=$hasCard loginRedirect=$hasLogin"
    if ($hasWidget) {
        $level = [regex]::Match($r.Content, '<div class="display-5[^"]*">([^<]+)</div>').Groups[1].Value
        Write-Host "  Level: $level"
    }
}
