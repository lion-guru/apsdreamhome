<?php $page_title = 'Add Expense'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Add Expense</h2>
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/expenses/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="travel">Travel</option>
                                    <option value="office">Office Supplies</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="utilities">Utilities</option>
                                    <option value="salary_advance">Salary Advance</option>
                                    <option value="commission">Commission Payout</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="legal">Legal</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount (₹) *</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment Mode</label>
                                <select name="payment_mode" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="upi">UPI</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="card">Card</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter expense details..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Expense</button>
                        <a href="<?= BASE_URL ?>/admin/expenses" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
