$urls = @(
    '/admin/finance',
    '/admin/finance/dashboard',
    '/admin/finance/bank-accounts',
    '/admin/finance/bank-account-form',
    '/admin/finance/cash-book',
    '/admin/finance/transaction-form',
    '/admin/finance/petty-cash',
    '/admin/finance/cheques',
    '/admin/finance/cheque-issue',
    '/admin/finance/reconciliation',
    '/admin/finance/tds',
    '/admin/finance/tds-record',
    '/admin/finance/tds-certificates',
    '/admin/finance/gst',
    '/admin/finance/gst-summary',
    '/admin/finance/expenses',
    '/admin/finance/expense-form',
    '/admin/finance/vendors',
    '/admin/finance/vendor-payment',
    '/admin/finance/forecast',
    '/admin/finance/templates',
    '/admin/finance/template-form',
    '/admin/finance/voucher-log'
)
$ok = 0; $fail = 0; $list = @()
foreach ($u in $urls) {
    $full = "http://localhost/apsdreamhome$u" + '?test_login=1'
    try {
        $r = Invoke-WebRequest -Uri $full -UseBasicParsing -TimeoutSec 8 -ErrorAction Stop
        $code = [int]$r.StatusCode
        if ($code -ge 200 -and $code -lt 400) { Write-Host "[OK   $code] $u"; $ok++ }
        else { Write-Host "[HTTP $code] $u"; $fail++ }
    } catch {
        $code = 0
        if ($_.Exception.Response) { $code = [int]$_.Exception.Response.StatusCode }
        if ($code -ge 200 -and $code -lt 500) { Write-Host "[OK   $code] $u"; $ok++ }
        else { Write-Host "[FAIL $code] $u"; $fail++ }
    }
}
Write-Host ""
Write-Host "===== SUMMARY ====="
Write-Host "OK:   $ok"
Write-Host "FAIL: $fail"
Write-Host "Total: $($urls.Count)"
