<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-plus me-2"></i>Create GST Invoice</h1>
        <a href="<?= BASE_URL ?>/admin/gst" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/gst/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Customer</label>
                        <select name="user_id" class="form-select">
                            <option value="0">Walk-in / No Account</option>
                            <?php foreach ($users ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control" value="INV-<?= strtoupper(uniqid()) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" name="client_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Client GSTIN</label>
                        <input type="text" name="client_gstin" class="form-control" placeholder="22AAAAA0000A1Z5">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Invoice Date</label>
                        <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Amount (₹)</label>
                        <input type="number" name="total_amount" class="form-control" step="0.01" value="0" required>
                    </div>
                </div>
                <h5 class="mt-4 mb-3">GST Configuration</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">GST Type</label>
                        <select name="gst_type" class="form-select">
                            <option value="cgst_sgst">CGST + SGST (Intra-state)</option>
                            <option value="igst">IGST (Inter-state)</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">GST Rate (%)</label>
                        <select name="gst_rate" class="form-select">
                            <option value="0">0% (Nil)</option>
                            <option value="5">5%</option>
                            <option value="12">12%</option>
                            <option value="18" selected>18%</option>
                            <option value="28">28%</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">CGST (₹)</label>
                        <input type="number" name="cgst_amount" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">SGST (₹)</label>
                        <input type="number" name="sgst_amount" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">IGST (₹)</label>
                        <input type="number" name="igst_amount" class="form-control" step="0.01" value="0">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">HSN Code</label>
                        <input type="text" name="hsn_code" class="form-control" placeholder="e.g. 9987">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Place of Supply</label>
                        <input type="text" name="place_of_supply" class="form-control" placeholder="e.g. Uttar Pradesh">
                    </div>
                </div>
                <h5 class="mt-4 mb-3">E-Invoicing (Optional)</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-Invoice Number</label>
                        <input type="text" name="e_invoice_number" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-Way Bill</label>
                        <input type="text" name="e_way_bill" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save me-1"></i>Create GST Invoice</button>
            </form>
        </div>
    </div>
</div>
