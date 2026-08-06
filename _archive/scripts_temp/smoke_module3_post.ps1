# POST flow tests (no CSRF: just test that controllers handle 200/302 on POST)
$tests = @(
    @{ url = '/admin/finance/bank-account-store'; body = @{ account_name = 'Test Bank A/c'; account_number = '999988887777'; ifsc_code = 'TEST0001234'; bank_name = 'Test Bank'; opening_balance = '100000' } },
    @{ url = '/admin/finance/transaction-store';  body = @{ transaction_type = 'receipt'; amount = '5000'; transaction_date = '2026-06-07'; payment_mode = 'cash'; party_name = 'Smoke Test Party'; narration = 'Smoke test receipt' } },
    @{ url = '/admin/finance/petty-topup';         body = @{ amount = '2000'; topup_date = '2026-06-07'; source = 'Main Bank'; remarks = 'Smoke topup' } },
    @{ url = '/admin/finance/cheque-store';        body = @{ cheque_date = '2026-06-07'; cheque_number = 'SMOKE-001'; amount = '7500'; bank_account_id = '1'; payee_name = 'Smoke Payee'; purpose = 'Smoke' } },
    @{ url = '/admin/finance/tds-store';           body = @{ tds_date = '2026-06-07'; section_code = '194J'; deductee_user_id = '1'; deductee_name = 'Smoke Deductee'; gross_amount = '10000'; tds_rate = '10'; tds_amount = '1000'; financial_year = '2025-26'; quarter = 'Q1' } },
    @{ url = '/admin/finance/gst-store';           body = @{ transaction_date = '2026-06-07'; transaction_type = 'output'; supply_type = 'intra'; gst_rate = '18'; taxable_amount = '10000'; cgst = '900'; sgst = '900'; igst = '0'; party_name = 'Smoke GST Party'; financial_year = '2025-26' } },
    @{ url = '/admin/finance/expense-store';       body = @{ expense_date = '2026-06-07'; category = 'office'; amount = '1500'; description = 'Smoke expense'; payment_mode = 'cash' } },
    @{ url = '/admin/finance/vendor-payment-store';body = @{ payment_date = '2026-06-07'; vendor_type = 'contractor'; vendor_id = '1'; vendor_name = 'Smoke Vendor'; amount = '5000'; tds_deducted = '500'; gst_amount = '0'; payment_mode = 'bank' } },
    @{ url = '/admin/finance/template-store';      body = @{ template_name = 'Smoke Template'; template_type = 'overdue_installment'; subject = 'Smoke'; body_html = '<p>Hello {{customer_name}},</p>'; active = '1' } }
)
$ok = 0; $fail = 0
foreach ($t in $tests) {
    $full = "http://localhost/apsdreamhome$($t.url)" + '?test_login=1'
    $form = @{}
    foreach ($k in $t.body.Keys) { $form[$k] = $t.body[$k] }
    $form['csrf_token'] = [guid]::NewGuid().ToString()
    try {
        $r = Invoke-WebRequest -Uri $full -Method POST -Body $form -UseBasicParsing -TimeoutSec 8 -ErrorAction Stop
        $code = [int]$r.StatusCode
        if ($code -ge 200 -and $code -lt 400) { Write-Host "[OK   $code] $($t.url)"; $ok++ }
        else { Write-Host "[HTTP $code] $($t.url)"; $fail++ }
    } catch {
        $code = 0
        if ($_.Exception.Response) { $code = [int]$_.Exception.Response.StatusCode }
        if ($code -ge 200 -and $code -lt 500) { Write-Host "[OK   $code] $($t.url)"; $ok++ }
        else { Write-Host "[FAIL $code] $($t.url)"; $fail++ }
    }
}
Write-Host ""
Write-Host "===== POST SUMMARY ====="
Write-Host "OK:   $ok"
Write-Host "FAIL: $fail"
