$jar = "$env:TEMP\aps_real_final.txt"
if (Test-Path $jar) { Remove-Item $jar -Force }
# Login via test_login
Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/admin/login?test_login=1' -UseBasicParsing -TimeoutSec 8 -SessionVariable jar -MaximumRedirection 5 | Out-Null
# Get form + CSRF
$form = Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/admin/finance/bank-account-form' -UseBasicParsing -TimeoutSec 8 -WebSession $jar
$content = $form.Content
$token = ''
if ($content -match 'name="csrf_token"[^>]*value="([^"]+)"') { $token = $matches[1] }
Write-Host "CSRF token length: $($token.Length)"
# POST
$body = @{
    csrf_token = $token
    account_name = 'RealSessionFinal_' + (Get-Random)
    account_number = 'RSF' + (Get-Random)
    ifsc_code = 'RSF0001234'
    bank_name = 'RSF Bank'
    branch = 'Test'
    account_type = 'current'
    opening_balance = '8888'
    active = '1'
}
try {
    $r = Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/admin/finance/bank-account-store' -Method POST -Body $body -UseBasicParsing -TimeoutSec 8 -WebSession $jar -MaximumRedirection 5 -ErrorAction Stop
    Write-Host "POST final: $(([int]$r.StatusCode))"
} catch {
    $code = if ($_.Exception.Response) { [int]$_.Exception.Response.StatusCode } else { 0 }
    Write-Host "POST: ${code} - $($_.Exception.Message)"
}
# Verify
& "C:\xampp\php\php.exe" -r "define('APP_ROOT', 'C:\xampp\htdocs\apsdreamhome'); require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php'; \$db = \App\Core\Database\Database::getInstance(); \$row = \$db->fetchOne('SELECT * FROM bank_accounts WHERE bank_name = ?', ['RSF Bank']); echo 'RSF Bank row: ' . (\$row ? 'YES id=' . \$row['id'] : 'NO') . PHP_EOL; echo 'Total: ' . \$db->fetchOne('SELECT COUNT(*) c FROM bank_accounts')['c'] . PHP_EOL;"
