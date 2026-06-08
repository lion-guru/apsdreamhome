# Use a real session: login, get CSRF, POST, verify
$jar = "$env:TEMP\aps_module3_real.txt"
if (Test-Path $jar) { Remove-Item $jar -Force }

# Step 1: login with test_login
$login = 'http://localhost/apsdreamhome/admin/login?test_login=1'
$r1 = Invoke-WebRequest -Uri $login -UseBasicParsing -TimeoutSec 8 -SessionVariable jar -ErrorAction Stop
Write-Host "Login: $([int]$r1.StatusCode)"

# Step 2: visit bank-account-form to get CSRF token
$form = 'http://localhost/apsdreamhome/admin/finance/bank-account-form?test_login=1'
$r2 = Invoke-WebRequest -Uri $form -UseBasicParsing -TimeoutSec 8 -WebSession $jar -ErrorAction Stop
$html = $r2.Content

# Extract CSRF token
$token = ''
if ($html -match 'name="csrf_token"[^>]*value="([^"]+)"') { $token = $matches[1] }
Write-Host "CSRF token: $($token.Length) chars"

# Step 3: POST with real token and session
$post = 'http://localhost/apsdreamhome/admin/finance/bank-account-store?test_login=1'
$body = @{
    csrf_token = $token
    account_name = 'RealSessionBank_' + (Get-Random)
    account_number = 'RSB' + (Get-Random)
    ifsc_code = 'RSB0001234'
    bank_name = 'Real Session Bank'
    branch = 'Test'
    account_type = 'current'
    opening_balance = '7777'
    status = 'active'
}
try {
    $r3 = Invoke-WebRequest -Uri $post -Method POST -Body $body -UseBasicParsing -TimeoutSec 8 -WebSession $jar -ErrorAction Stop -MaximumRedirection 0
    Write-Host "POST 200: bank-account-store"
} catch {
    $code = if ($_.Exception.Response) { [int]$_.Exception.Response.StatusCode } else { 0 }
    if ($code -eq 302) { Write-Host "POST 302: bank-account-store (redirect = success)" }
    else { Write-Host "POST ${code}: bank-account-store" }
}

# Step 4: Check the DB via PHP
$check = & "C:\xampp\php\php.exe" -r "define('APP_ROOT', 'C:\xampp\htdocs\apsdreamhome'); require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php'; \$db = \App\Core\Database\Database::getInstance(); \$row = \$db->fetchOne('SELECT * FROM bank_accounts WHERE bank_name = ?', ['Real Session Bank']); echo 'RealSessionBank row: ' . (\$row ? 'YES (id=' . \$row['id'] . ', name=' . \$row['account_name'] . ')' : 'NO') . PHP_EOL; echo 'Total bank_accounts: ' . \$db->fetchOne('SELECT COUNT(*) c FROM bank_accounts')['c'] . PHP_EOL;"
Write-Host $check
