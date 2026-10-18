<?php
$entities = $entities ?? [];
$today = $today ?? date('Y-m-d');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>New Inter-Company Transaction</h1>
        <a href="<?= BASE_URL ?>/admin/inter-company" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/inter-company/store" id="icForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Transaction Number</label>
                                    <input type="text" name="transaction_number" class="form-control" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Transaction Date</label>
                                    <input type="date" name="transaction_date" class="form-control" value="<?= htmlspecialchars($today) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">From Entity</label>
                                    <select name="from_entity_id" class="form-select" required>
                                        <option value="">Select source entity</option>
                                        <?php foreach ($entities as $e): ?><option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['entity_name']) ?> (<?= htmlspecialchars($e['entity_code']) ?>)</option><?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">To Entity</label>
                                    <select name="to_entity_id" class="form-select" required>
                                        <option value="">Select destination entity</option>
                                        <?php foreach ($entities as $e): ?><option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['entity_name']) ?> (<?= htmlspecialchars($e['entity_code']) ?>)</option><?php endforeach; ?></select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Transaction Type</label>
                                    <select name="transaction_type" class="form-select" required>
                                        <option value="sale">Sale</option>
                                        <option value="purchase">Purchase</option>
                                        <option value="service">Service</option>
                                        <option value="loan">Loan</option>
                                        <option value="expense_allocation">Expense Allocation</option>
                                        <option value="fixed_asset_transfer">Fixed Asset Transfer</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Amount</label>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Currency</label>
                                    <select name="currency" class="form-select">
                                        <option value="INR">INR</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                        <option value="AED">AED</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Exchange Rate</label>
                                    <input type="number" name="exchange_rate" class="form-control" step="0.0001" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Amount (INR)</label>
                                    <input type="number" name="amount_inr" class="form-control" step="0.01" readonly>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Reference Document</label>
                                    <input type="text" name="reference_document" class="form-control" placeholder="Invoice/PO number">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12 mt-4">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Transaction</button>
                                <a href="<?= BASE_URL ?>/admin/inter-company" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Entities</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Currency</th></tr></thead>
                        <tbody>
                        <?php foreach ($entities as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['entity_code']) ?></td>
                                <td><?= htmlspecialchars($e['entity_name']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($e['entity_type'])) ?></td>
                                <td><?= htmlspecialchars($e['currency']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('icForm').addEventListener('input', function() {
    var amount = parseFloat(document.querySelector('input[name="amount"]').value) || 0;
    var rate = parseFloat(document.querySelector('input[name="exchange_rate"]').value) || 1;
    var inr = amount * rate;
    document.querySelector('input[name="amount_inr"]').value = inr.toFixed(2);
});
</script>
