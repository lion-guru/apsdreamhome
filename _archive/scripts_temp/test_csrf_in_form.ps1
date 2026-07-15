$jar = "$env:TEMP\aps_real_session3.txt"
if (Test-Path $jar) { Remove-Item $jar -Force }
Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/admin/login?test_login=1' -UseBasicParsing -TimeoutSec 8 -SessionVariable jar -MaximumRedirection 5 | Out-Null
$content = (Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/admin/finance/bank-account-form' -UseBasicParsing -TimeoutSec 8 -WebSession $jar).Content
$content | Select-String -Pattern 'csrf_token' -Context 0,1 | ForEach-Object { Write-Host $_.Line }
