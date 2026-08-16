<?php $page_title = $page_title ?? 'Record TDS'; $page_heading = $page_heading ?? 'Record New TDS Deduction'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Record TDS Deduction</h2>
        <a href="<?= BASE_URL ?>/admin/finance/tds" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/tds-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">TDS Date <span class="text-danger">*</span></label><input type="date" name="tds_date" required class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    <div class="col-md-3"><label class="form-label">Section <span class="text-danger">*</span></label>
                        <select name="section_code" required class="form-select">
                            <option value="">— Select —</option>
                            <option value="194IA">194IA (Property Transfer 1%)</option>
                            <option value="194IB">194IB (Rent 5%)</option>
                            <option value="194C">194C (Contractor)</option>
                            <option value="194H">194H (Commission 5%)</option>
                            <option value="194I">194I (Rent 10%)</option>
                            <option value="194J">194J (Professional 10%)</option>
                            <option value="194M">194M (>20L)</option>
                            <option value="194N">194N (Cash >1Cr)</option>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Deductee Type</label>
                        <select name="deductee_type" class="form-select">
                            <option value="individual">Individual</option>
                            <option value="company">Company</option>
                            <option value="huf">HUF</option>
                            <option value="firm">Firm</option>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Deductee User ID</label><input type="number" name="deductee_user_id" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Deductee Name</label><input type="text" name="deductee_name" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">PAN</label><input type="text" name="deductee_pan" class="form-control text-uppercase" maxlength="10"></div>
                    <div class="col-md-3"><label class="form-label">Gross Amount (₹) <span class="text-danger">*</span></label><input type="number" name="gross_amount" step="0.01" min="1" required class="form-control" id="gross" oninput="calcTds()"></div>
                    <div class="col-md-3"><label class="form-label">TDS Rate (%)</label><input type="number" name="tds_rate" step="0.01" class="form-control" id="rate" oninput="calcTds()"></div>
                    <div class="col-md-3"><label class="form-label">TDS Amount (₹) <span class="text-danger">*</span></label><input type="number" name="tds_amount" step="0.01" required class="form-control" id="tds"></div>
                    <div class="col-md-3"><label class="form-label">Financial Year</label>
                        <select name="financial_year" class="form-select">
                            <?php $cur = (int)date('n') < 4 ? (int)date('Y') - 1 : (int)date('Y'); ?>
                            <?php for ($i = 0; $i < 3; $i++): $y = $cur - $i; ?>
                                <option value="<?= $y ?>-<?= substr((string)($y+1), -2) ?>"><?= $y ?>-<?= substr((string)($y+1), -2) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Quarter</label>
                        <select name="quarter" class="form-select">
                            <option>Q1</option><option>Q2</option><option>Q3</option><option selected>Q4</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Deposit Bank</label>
                        <select name="deposited_in_bank" class="form-select">
                            <option value="">— Pending —</option>
                            <?php foreach (($banks ?? []) as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Challan / BSR</label><input type="text" name="challan_number" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Record TDS</button>
                    <a href="<?= BASE_URL ?>/admin/finance/tds" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function calcTds(){
    const g = parseFloat(document.getElementById('gross').value)||0;
    const r = parseFloat(document.getElementById('rate').value)||0;
    document.getElementById('tds').value = (g * r / 100).toFixed(2);
}
</script>
