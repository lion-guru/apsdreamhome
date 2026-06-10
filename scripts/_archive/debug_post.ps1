# Debug version: dump the actual POST response, headers, and see what happened
$jar = "$env:TEMP\aps_debug.txt"
if (Test-Path $jar) { Remove-Item $jar -Force }

# 1. login
$r1 = Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/admin/login?test_login=1' -UseBasicParsing -TimeoutSec 8 -SessionVariable jar -MaximumRedirection 5
Write-Host "Step 1: login status=$([int]$r1.StatusCode)"

# 2. get form
$r2 = Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/admin/finance/bank-account-form' -UseBasicParsing -TimeoutSec 8 -WebSession $jar
$content = $r2.Content
Write-Host "Step 2: form status=$([int]$r2.StatusCode) length=$($r2.Content.Length)"

# extract token
$token = ''
if ($content -match 'name="csrf_token"[^>]*value="([^"]+)"') { $token = $matches[1] }
Write-Host "Step 2: token='$token'"

# 3. post - use full URL with verbose output
$body = @{
    csrf_token = $token
    account_name = 'DebugBank_' + (Get-Random)
    account_number = 'DB' + (Get-Random)
    ifsc_code = 'DBG0001234'
    bank_name = 'DBG Bank'
    branch = 'X'
    account_type = 'current'
    opening_balance = '9999'
    active = '1'
}
Write-Host "Step 3: posting with body: $($body | ConvertTo-Json -Compress)"

try {
    $r3 = Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/admin/finance/bank-account-store' -Method POST -Body $body -UseBasicParsing -TimeoutSec 8 -WebSession $jar -MaximumRedirection 0 -ErrorAction Stop
    Write-Host "Step 3: status=$([int]$r3.StatusCode) headers=$($r3.Headers.Keys -join ',')"
} catch {
    $ex = $_.Exception
    $code = if ($ex.Response) { [int]$ex.Response.StatusCode } else { 0 }
    Write-Host "Step 3: exception status=$code msg=$($ex.Message)"
    if ($ex.Response) {
        $h = $ex.Response.Headers
        if ($h['Location']) { Write-Host "  Location: $($h['Location'])" }
    }
}
# 4. Check db
$r = & "C:\xampp\php\php.exe" -r "define('APP_ROOT', 'C:\xampp\htdocs\apsdreamhome'); require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php'; \$db = \App\Core\Database\Database::getInstance(); \$row = \$db->fetchOne('SELECT * FROM bank_accounts WHERE bank_name = ?', ['DBG Bank']); echo (\$row ? 'FOUND id=' . \$row['id'] : 'NOT FOUND') . PHP_EOL; echo 'Total: ' . \$db->fetchOne('SELECT COUNT(*) c FROM bank_accounts')['c'] . PHP_EOL;" 2>&1
Write-Host "Step 4: $r"
