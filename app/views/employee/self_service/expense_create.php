<?php
/** Employee Self-Service - Create Expense Claim */

$page_title = $page_title ?? 'Create Expense Claim';
$categories = $categories ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.item-row { border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 0.75rem; background: #fafafa; transition: all 0.2s; }
.item-row:hover { border-color: #7c2d12; background: #fefcf6; }
.item-row .drag-handle { cursor: grab; color: #7c2d12; }
.item-row.dragging { opacity: 0.5; background: #fff3e0; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-receipt me-2 text-primary"></i>Create Expense Claim</h4>
            <p class="text-muted mb-0 small">Submit a new expense reimbursement claim</p>
        </div>
        <a href="<?= BASE_URL ?>/employee/self-service/expenses" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Claims
        </a>
    </div>

    <form method="POST" id="claimForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <!-- Basic Info -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Claim Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Claim Date <span class="text-danger">*</span></label>
                        <input type="date" name="claim_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Business purpose, client meeting, etc."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Items -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-list me-2"></i>Expense Items</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                    <i class="fas fa-plus me-1"></i> Add Item
                </button>
            </div>
            <div class="card-body">
                <div id="itemsContainer">
                    <div class="item-row" data-index="0">
                        <div class="row g-3 align-items-end">
                            <div class="col-auto">
                                <div class="drag-handle p-2 text-muted" title="Drag to reorder">
                                    <i class="fas fa-grip-lines"></i>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Date <span class="text-danger">*</span></label>
                                <input type="date" name="items[0][expense_date]" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Description <span class="text-danger">*</span></label>
                                <input type="text" name="items[0][description]" class="form-control" required placeholder="e.g., Client lunch, taxi fare">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="items[0][amount]" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">GST Amount (₹)</label>
                                <input type="number" step="0.01" min="0" name="items[0][gst_amount]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small">GST %</label>
                                <input type="number" step="0.01" min="0" max="28" name="items[0][gst_rate]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small">HSN/SAC</label>
                                <input type="text" name="items[0][hsn_sac_code]" class="form-control form-control-sm" placeholder="e.g., 9983">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Receipt</label>
                                <input type="file" name="items[0][receipt_file]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                    <i class="fas fa-trash me-1"></i> Remove
                                </button>
                            </div>
                            <hr class="my-2">
                        </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/employee/self-service/expenses" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Submit Claim
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
let itemIndex = 1;

function addItem() {
    const container = document.getElementById('itemsContainer');
    const index = itemIndex++;
    const html = `
        <div class="item-row" data-index="${index}">
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <div class="drag-handle p-2 text-muted" title="Drag to reorder">
                        <i class="fas fa-grip-lines"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Date <span class="text-danger">*</span></label>
                    <input type="date" name="items[${index}][expense_date]" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Description <span class="text-danger">*</span></label>
                    <input type="text" name="items[${index}][description]" class="form-control" required placeholder="e.g., Client lunch, taxi fare">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="items[${index}][amount]" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">GST Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][gst_amount]" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <label class="form-label small">GST %</label>
                    <input type="number" step="0.01" min="0" max="28" name="items[${index}][gst_rate]" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <label class="form-label small">HSN/SAC</label>
                    <input type="text" name="items[${index}][hsn_sac_code]" class="form-control form-control-sm" placeholder="e.g., 9983">
                </div>
                <div class="col-12">
                    <label class="form-label small">Receipt</label>
                    <input type="file" name="items[${index}][receipt_file]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                        <i class="fas fa-trash me-1"></i> Remove
                    </button>
                </div>
                <hr class="my-2">
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
}

function removeItem(btn) {
    if (confirm('Remove this item?')) {
        btn.closest('.item-row').remove();
    }
}

// Form validation
document.getElementById('claimForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>