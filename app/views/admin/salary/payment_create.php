<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2"></i>Create Salary Payment</h1>
        <a href="<?= BASE_URL ?>/admin/salary/payments" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/payments/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" id="empSelect" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users ?? [] as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salary Structure</label>
                        <select name="structure_id" id="structSelect" class="form-select">
                            <option value="">Select Structure (auto-calculate)</option>
                            <?php foreach ($structures ?? [] as $s): ?>
                            <option value="<?= $s['id'] ?>" data-basic="<?= $s['basic_salary'] ?? 0 ?>" data-hra="<?= $s['hra'] ?? 0 ?>" data-da="<?= $s['da'] ?? 0 ?>" data-ta="<?= $s['travel_allowance'] ?? 0 ?>" data-medical="<?= $s['medical_allowance'] ?? 0 ?>" data-special="<?= $s['special_allowance'] ?? 0 ?>" data-pf="<?= $s['pf_percent'] ?? 0 ?>" data-tax="<?= $s['tax_deduction'] ?? 0 ?>"><?= htmlspecialchars($s['employee_name'] ?? '') ?> - ₹<?= number_format($s['basic_salary'] ?? 0, 0) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Payment Date</label><input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Gross Salary</label><input type="number" step="0.01" name="gross_salary" id="grossSalary" class="form-control" value="0" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Total Deductions</label><input type="number" step="0.01" name="total_deductions" id="totalDeductions" class="form-control" value="0"></div>
                    <div class="col-md-4 mb-3"><label class="form-label"><strong>Net Salary</strong></label>
                        <input type="text" class="form-control-plaintext fw-bold fs-5 text-success" id="netSalaryDisplay" value="₹0.00" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Transaction ID</label><input type="text" name="transaction_id" class="form-control" placeholder="Optional"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Payment</button>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var structSelect = document.getElementById('structSelect');
    var grossInput = document.getElementById('grossSalary');
    var dedInput = document.getElementById('totalDeductions');
    var netDisplay = document.getElementById('netSalaryDisplay');
    function calculate(){
        var g = parseFloat(grossInput.value) || 0;
        var d = parseFloat(dedInput.value) || 0;
        netDisplay.value = '₹' + (g - d).toFixed(2);
    }
    function autoFill(){
        var opt = structSelect.options[structSelect.selectedIndex];
        if(opt && opt.value){
            var basic = parseFloat(opt.dataset.basic || 0);
            var hra = parseFloat(opt.dataset.hra || 0);
            var da = parseFloat(opt.dataset.da || 0);
            var ta = parseFloat(opt.dataset.ta || 0);
            var medical = parseFloat(opt.dataset.medical || 0);
            var special = parseFloat(opt.dataset.special || 0);
            var pfPct = parseFloat(opt.dataset.pf || 0);
            var tax = parseFloat(opt.dataset.tax || 0);
            var gross = basic + hra + da + ta + medical + special;
            var pfAmt = gross * (pfPct / 100);
            var ded = pfAmt + tax;
            grossInput.value = gross.toFixed(2);
            dedInput.value = ded.toFixed(2);
        }
        calculate();
    }
    structSelect.addEventListener('change', autoFill);
    grossInput.addEventListener('input', calculate);
    dedInput.addEventListener('input', calculate);
});
</script>
