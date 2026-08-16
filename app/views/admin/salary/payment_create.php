<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus-circle text-primary me-2"></i>Create Salary Payment</h1>
        <a href="<?= BASE_URL ?>/admin/salary/payments" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Payments
        </a>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card shadow mb-4 border-top-primary">
                <div class="card-header py-3 bg-white d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-invoice-dollar me-2"></i>Payment Details</h6>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="<?= BASE_URL ?>/admin/salary/payments/store">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" id="empSelect" class="form-select select2-init" required>
                                    <option value="">Search and select employee...</option>
                                    <?php foreach ($users ?? [] as $e): ?>
                                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Apply Salary Structure</label>
                                <select name="structure_id" id="structSelect" class="form-select select2-init">
                                    <option value="">Select structure to auto-fill...</option>
                                    <?php foreach ($structures ?? [] as $s): ?>
                                    <option value="<?= $s['id'] ?>" 
                                            data-basic="<?= $s['basic_salary'] ?? 0 ?>" 
                                            data-gross="<?= $s['gross_salary'] ?? 0 ?>" 
                                            data-deductions="<?= $s['total_deductions'] ?? 0 ?>">
                                        <?= htmlspecialchars($s['employee_name'] ?? '') ?> - Net: ₹<?= number_format($s['net_salary'] ?? 0, 0) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted"><i class="fas fa-magic me-1"></i> Selecting a structure will auto-fill amounts.</small>
                            </div>
                        </div>

                        <div class="card bg-light border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="text-primary mb-3"><i class="fas fa-calculator me-2"></i>Financial Breakdown</h6>
                                <div class="row align-items-end">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label text-muted small fw-bold">Basic Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number" step="0.01" name="basic_amount" id="basicAmount" class="form-control" value="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label text-success small fw-bold">Gross Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number" step="0.01" name="gross_amount" id="grossAmount" class="form-control text-success fw-bold" value="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label text-danger small fw-bold">Total Deductions</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number" step="0.01" name="deduction_amount" id="totalDeductions" class="form-control text-danger fw-bold" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end align-items-center mt-2 pt-3 border-top">
                                    <span class="text-muted me-3 text-uppercase fw-bold">Net Payable:</span>
                                    <span class="fs-3 fw-bold text-primary" id="netSalaryDisplay">₹0.00</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="cash">Cash</option>
                                    <option value="upi">UPI</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Transaction ID</label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="Optional reference">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Payment Status</label>
                                <select name="payment_status" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Remarks / Notes</label>
                                <input type="text" name="remarks" class="form-control" placeholder="Any additional notes...">
                            </div>
                        </div>
                        
                        <hr class="mt-4 mb-4">
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="fas fa-check-circle me-2"></i> Record Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Initialize Select2
    if($.fn.select2) {
        $('.select2-init').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }

    const structSelect = document.getElementById('structSelect');
    const basicInput = document.getElementById('basicAmount');
    const grossInput = document.getElementById('grossAmount');
    const dedInput = document.getElementById('totalDeductions');
    const netDisplay = document.getElementById('netSalaryDisplay');

    function formatCurrency(num) {
        return '₹' + parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function calculateNet(){
        const g = parseFloat(grossInput.value) || 0;
        const d = parseFloat(dedInput.value) || 0;
        const net = g - d;
        netDisplay.textContent = formatCurrency(net);
    }

    function autoFillFromStructure(){
        const opt = structSelect.options[structSelect.selectedIndex];
        if(opt && opt.value){
            const basic = parseFloat(opt.dataset.basic || 0);
            const gross = parseFloat(opt.dataset.gross || 0);
            const deds = parseFloat(opt.dataset.deductions || 0);
            
            basicInput.value = basic.toFixed(2);
            grossInput.value = gross.toFixed(2);
            dedInput.value = deds.toFixed(2);
        }
        calculateNet();
    }

    // Event listeners
    if (structSelect) {
        // Handle jQuery select2 change event if present, otherwise native
        if ($.fn.select2) {
            $(structSelect).on('select2:select', autoFillFromStructure);
        } else {
            structSelect.addEventListener('change', autoFillFromStructure);
        }
    }
    
    basicInput.addEventListener('input', calculateNet);
    grossInput.addEventListener('input', calculateNet);
    dedInput.addEventListener('input', calculateNet);
    
    // Initial calculation
    calculateNet();
});
</script>
