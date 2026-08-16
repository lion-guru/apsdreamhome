<?php $page_title = $page_title ?? 'New Transaction'; $page_heading = $page_heading ?? 'Record Cash Transaction'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-money-bill me-2 text-primary"></i>Record Cash Transaction</h2>
        <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/transaction-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="transaction_date" required class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="transaction_type" required class="form-select">
                            <option value="receipt">Receipt (In)</option>
                            <option value="payment">Payment (Out)</option>
                            <option value="contra">Contra (Transfer)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" required class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                            <option value="dd">Demand Draft</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Party Name</label>
                        <input type="text" name="party_name" class="form-control" placeholder="Customer / Vendor name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank Account</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">Cash (no bank)</option>
                            <?php foreach (($banks ?? []) as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name'] . ' — ' . $b['bank_name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference Type</label>
                        <select name="reference_type" class="form-select">
                            <option value="">—</option>
                            <option value="booking">Booking</option>
                            <option value="installment">Installment</option>
                            <option value="expense">Expense</option>
                            <option value="vendor">Vendor Bill</option>
                            <option value="tds">TDS Deposit</option>
                            <option value="gst">GST Payment</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference ID</label>
                        <input type="number" name="reference_id" class="form-control" placeholder="Auto-link to source record">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Narration</label>
                        <textarea name="narration" class="form-control" rows="2" placeholder="Description / notes"></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Record Transaction</button>
                    <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
