# Login with test_login, then access form, extract CSRF, POST with cookies
$jar = "$env:TEMP\aps_real_session.txt"
if (Test-Path $jar) { Remove-Item $jar -Force }

# Step 1: test_login on the admin login URL (this is the only URL that honors ?test_login=1)
$login = 'http://localhost/apsdreamhome/admin/login?test_login=1'
$r1 = Invoke-WebRequest -Uri $login -UseBasicParsing -TimeoutSec 8 -SessionVariable jar -ErrorAction Stop -MaximumRedirection 5
Write-Host "test_login GET: $([int]$r1.StatusCode), final URL: $($r1.BaseResponse.RequestMessage.RequestUri)"

# Step 2: with the session cookie, GET the bank-account-form (now should be authenticated)
$form = 'http://localhost/apsdreamhome/admin/finance/bank-account-form'
$r2 = Invoke-WebRequest -Uri $form -UseBasicParsing -TimeoutSec 8 -WebSession $jar -ErrorAction Stop
Write-Host "bank-account-form GET: $([int]$r2.StatusCode), length: $($r2.Content.Length)"

# Extract CSRF token
$token = ''
if ($r2.Content -match 'name="csrf_token"[^>]*value="([^"]+)"') { $token = $matches[1] }
Write-Host "CSRF token: '$token' (length $($token.Length))"

# Step 3: POST
if ($token) {
    $post = 'http://localhost/apsdreamhome/admin/finance/bank-account-store'
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
        Write-Host "POST result: $([int]$r3.StatusCode)"
    } catch {
        $code = if ($_.Exception.Response) { [int]$_.Exception.Response.StatusCode } else { 0 }
        Write-Host "POST result: $code"
    }
}

# Step 4: Verify DB
$check = & "C:\xampp\php\php.exe" -r "define('APP_ROOT', 'C:\xampp\htdocs\apsdreamhome'); require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php'; \$db = \App\Core\Database\Database::getInstance(); \$row = \$db->fetchOne('SELECT * FROM bank_accounts WHERE bank_name = ?', ['Real Session Bank']); echo 'Real Session Bank row: ' . (\$row ? 'YES (id=' . \$row['id'] . ')' : 'NO') . PHP_EOL; echo 'Total bank_accounts: ' . \$db->fetchOne('SELECT COUNT(*) c FROM bank_accounts')['c'] . PHP_EOL;"
Write-Host $check
