# Proper end-to-end POST test: GET a CSRF token first, then POST with it
$jar = "$env:TEMP\aps_module3_jar.txt"
if (Test-Path $jar) { Remove-Item $jar -Force }

# 1. Get a session + CSRF token from the bank account form page
$formUrl = 'http://localhost/apsdreamhome/admin/finance/bank-account-form?test_login=1'
$resp = Invoke-WebRequest -Uri $formUrl -UseBasicParsing -TimeoutSec 8 -SessionVariable jar -ErrorAction Stop
$html = $resp.Content

# Extract CSRF token from form
$token = ''
if ($html -match 'name="csrf_token"\s+value="([^"]+)"') { $token = $matches[1] }
elseif ($html -match 'value="([^"]+)"\s+name="csrf_token"') { $token = $matches[1] }
elseif ($html -match 'name="csrf_token"[^>]*value="([^"]+)"') { $token = $matches[1] }
Write-Host "CSRF token found: $($token.Length) chars"

# 2. POST with the real token
$post = 'http://localhost/apsdreamhome/admin/finance/bank-account-store?test_login=1'
$body = @{
    csrf_token = $token
    account_name = 'Module3 Test A/c'
    account_number = 'TESTACC12345'
    ifsc_code = 'TEST0001234'
    bank_name = 'Test Bank'
    opening_balance = '9999'
    branch = 'Test Branch'
    account_type = 'savings'
    status = 'active'
}
try {
    $r = Invoke-WebRequest -Uri $post -Method POST -Body $body -UseBasicParsing -TimeoutSec 8 -WebSession $jar -ErrorAction Stop -MaximumRedirection 0
    Write-Host "[POST $(([int]$r.StatusCode))] bank-account-store"
} catch {
    $code = if ($_.Exception.Response) { [int]$_.Exception.Response.StatusCode } else { 0 }
    if ($code -eq 302) {
        Write-Host "[OK  302 (redirect - success)] bank-account-store"
    } else {
        Write-Host "[POST $code] bank-account-store: $($_.Exception.Message)"
    }
}

# 3. Verify a new row was added
$verify = 'http://localhost/apsdreamhome/admin/finance/bank-accounts?test_login=1'
$v = Invoke-WebRequest -Uri $verify -UseBasicParsing -TimeoutSec 8 -WebSession $jar -ErrorAction Stop
if ($v.Content -match 'Module3 Test A/c') {
    Write-Host "[VERIFY] bank_accounts page shows new account - PASS"
} else {
    Write-Host "[VERIFY] bank_accounts page - new account NOT visible"
}

# 4. Check the actual DB row count via a separate PHP call
& "C:\xampp\php\php.exe" -r "require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php'; define('APP_ROOT', 'C:\xampp\htdocs\apsdreamhome'); \$db = \App\Core\Database\Database::getInstance(); echo 'bank_accounts count: ' . \$db->fetchOne('SELECT COUNT(*) c FROM bank_accounts')['c'] . PHP_EOL; \$r = \$db->fetchOne('SELECT * FROM bank_accounts WHERE account_name=?', ['Module3 Test A/c']); echo 'New row found: ' . (\$r ? 'YES (id=' . \$r['id'] . ')' : 'NO') . PHP_EOL;"
