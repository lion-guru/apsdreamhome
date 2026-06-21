<?php $page_title = $page_title ?? __('vpay_record_payment'); $page_heading = $page_heading ?? __('vpay_record_payment');
 $currencies = $currencies ?? ['INR' => ['symbol' => '₹', 'name' => 'Indian Rupee', 'rate' => 1.0]];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i><?= __('vpay_record_payment') ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/vendors" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i><?= __('vpay_back') ?></a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/vendor-payment-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label"><?= __('vpay_payment_date') ?> <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" required class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= __('vpay_vendor_type') ?> <span class="text-danger">*</span></label>
                        <select name="vendor_type" required class="form-select">
                            <option value="contractor"><?= __('vpay_contractor') ?></option>
                            <option value="broker"><?= __('vpay_broker') ?></option>
                            <option value="consultant"><?= __('vpay_consultant') ?></option>
                            <option value="supplier"><?= __('vpay_supplier') ?></option>
                            <option value="employee"><?= __('vpay_employee') ?></option>
                            <option value="land_owner"><?= __('vpay_land_owner') ?></option>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label"><?= __('vpay_vendor_id') ?></label><input type="number" name="vendor_id" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label"><?= __('vpay_vendor_name') ?> <span class="text-danger">*</span></label><input type="text" name="vendor_name" required class="form-control"></div>
                    <div class="col-md-3"><label class="form-label"><?= __('vpay_vendor_pan') ?></label><input type="text" name="vendor_pan" class="form-control text-uppercase" maxlength="10"></div>
                    <div class="col-md-3"><label class="form-label"><?= __('vpay_bill_invoice') ?></label><input type="text" name="bill_number" class="form-control"></div>

                    <!-- Currency selector -->
                    <div class="col-md-3">
                        <label class="form-label"><?= __('vpay_currency') ?> <span class="text-danger">*</span></label>
                        <select name="currency" id="vCurrency" class="form-select" onchange="vUpdateFx()">
                            <?php foreach ($currencies as $code => $c): ?>
                                <option value="<?= $code ?>" data-rate="<?= $c['rate'] ?>" data-symbol="<?= $c['symbol'] ?>">
                                    <?= $code ?> — <?= $c['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Exchange rate (auto-filled, editable) -->
                    <div class="col-md-3">
                        <label class="form-label"><?= __('vpay_exchange_rate') ?></label>
                        <div class="input-group">
                            <input type="number" name="exchange_rate" id="vFxRate" class="form-control" step="0.0001" min="0" value="1.0000" oninput="vCalc()">
                            <button type="button" class="btn btn-outline-primary" id="vFetchFx" title="<?= __('vpay_live_rate_tooltip') ?>" onclick="vFetchLiveRate()">
                                <i class="fas fa-sync-alt" id="vFxIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted"><?= __('vpay_exchange_hint') ?> <span id="vFxStatus" class="badge bg-secondary ms-1" style="display:none"></span></small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" id="vAmtLabel"><?= __('vpay_amount') ?> <span class="text-danger">*</span></label>
                        <input type="number" name="gross_amount" step="0.01" min="1" required class="form-control" id="vAmt" oninput="vCalc()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= __('vpay_tds_deducted') ?></label>
                        <input type="number" name="tds_amount" step="0.01" class="form-control" id="vTds" oninput="vCalc()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= __('vpay_gst_amount') ?></label>
                        <input type="number" name="gst_amount" step="0.01" class="form-control" id="vGst" oninput="vCalc()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= __('vpay_net_payable') ?></label>
                        <input type="number" step="0.01" class="form-control" id="vNet" readonly>
                    </div>

                    <!-- Amount in INR (auto-calculated, hidden if INR) -->
                    <div class="col-md-3" id="vInrWrap">
                        <label class="form-label"><?= __('vpay_amount_inr') ?></label>
                        <input type="number" name="amount_inr" step="0.01" class="form-control bg-light" id="vInr" readonly>
                        <small class="text-muted"><?= __('vpay_auto_calculated') ?></small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label"><?= __('vpay_payment_mode') ?></label>
                        <select name="payment_mode" class="form-select">
                            <option value="bank"><?= __('vpay_bank_transfer') ?></option>
                            <option value="cheque"><?= __('vpay_cheque') ?></option>
                            <option value="cash"><?= __('vpay_cash') ?></option>
                            <option value="upi"><?= __('vpay_upi') ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= __('vpay_bank_account') ?></label>
                        <select name="bank_account_id" class="form-select">
                            <option value=""><?= __('vpay_select') ?></option>
                            <?php foreach (($banks ?? []) as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label"><?= __('vpay_narration') ?></label><textarea name="narration" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?= __('vpay_record_btn') ?></button>
                    <a href="<?= BASE_URL ?>/admin/finance/vendors" class="btn btn-outline-secondary"><?= __('vpay_cancel') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const FX_RATES = <?= json_encode($currencies, JSON_HEX_TAG) ?>;

function vUpdateFx() {
    const sel  = document.getElementById('vCurrency');
    const code = sel.value;
    const opt  = sel.options[sel.selectedIndex];
    const rate = parseFloat(opt.getAttribute('data-rate')) || 1;
    document.getElementById('vFxRate').value = rate.toFixed(4);
    document.getElementById('vAmtLabel').innerHTML = '<?= __("vpay_amount") ?> (' + (FX_RATES[code]?.symbol || '₹') + ') <span class="text-danger">*</span>';
    // Show/hide INR conversion row
    document.getElementById('vInrWrap').style.display = code === 'INR' ? 'none' : '';
    vCalc();
}

function vCalc() {
    const amt  = parseFloat(document.getElementById('vAmt').value)  || 0;
    const tds  = parseFloat(document.getElementById('vTds').value)  || 0;
    const gst  = parseFloat(document.getElementById('vGst').value)  || 0;
    const rate = parseFloat(document.getElementById('vFxRate').value) || 1;
    const code = document.getElementById('vCurrency').value;
    const net  = amt - tds;
    document.getElementById('vNet').value = net.toFixed(2);
    // INR equivalent
    const inr = amt * rate;
    document.getElementById('vInr').value = inr.toFixed(2);
}

// init on load
document.addEventListener('DOMContentLoaded', function () {
    vUpdateFx();
});

function vFetchLiveRate() {
    const code = document.getElementById('vCurrency').value;
    if (code === 'INR') {
        document.getElementById('vFxRate').value = '1.0000';
        vCalc();
        return;
    }
    const btn = document.getElementById('vFetchFx');
    const icon = document.getElementById('vFxIcon');
    const status = document.getElementById('vFxStatus');
    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';
    status.style.display = 'inline';
    status.className = 'badge bg-warning ms-1';
    status.textContent = '<?= __('vpay_fetching') ?>';

    fetch('<?= BASE_URL ?>/admin/finance/exchange-rate?from=' + encodeURIComponent(code))
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            icon.className = 'fas fa-sync-alt';
            if (data.success && data.rate) {
                document.getElementById('vFxRate').value = parseFloat(data.rate).toFixed(4);
                status.className = 'badge bg-success ms-1';
                status.textContent = (data.cached ? '<?= __('vpay_cached') ?>' : '<?= __('vpay_live') ?>') + ' — ' + data.fetched_at;
                vCalc();
            } else {
                status.className = 'badge bg-danger ms-1';
                status.textContent = data.error || 'Failed';
            }
        })
        .catch(() => {
            btn.disabled = false;
            icon.className = 'fas fa-sync-alt';
            status.className = 'badge bg-danger ms-1';
            status.textContent = '<?= __('vpay_network_error') ?>';
        });
}
</script>
