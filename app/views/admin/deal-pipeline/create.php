<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Create New Deal</h1>
        <a href="<?= BASE_URL ?>/admin/deal-pipeline" class="btn btn-secondary">Back to Pipeline</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div>
    <?php endif; ?>

    <div class="card aps-cp-card">
        <div class="card-body aps-cp-card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/deal-pipeline/store">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($users as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?> (<?= htmlspecialchars($c['phone'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Property</label>
                        <select name="property_id" class="form-select">
                            <option value="">Select Property</option>
                            <?php foreach ($properties as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title'] ?? '') ?> - ₹<?= number_format($p['price']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Deal Value <span class="text-danger">*</span></label>
                        <input type="number" name="deal_value" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Select User</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Expected Close Date</label>
                        <input type="date" name="expected_close_date" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Probability (%)</label>
                        <input type="number" name="probability" class="form-control" value="50" min="0" max="100">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stage</label>
                        <select name="stage" class="form-select">
                            <option value="lead">Lead</option>
                            <option value="qualified">Qualified</option>
                            <option value="site_visit">Site Visit</option>
                            <option value="negotiation">Negotiation</option>
                            <option value="booking">Booking</option>
                            <option value="agreement">Agreement</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Source</label>
                        <select name="source" class="form-select">
                            <option value="manual">Manual</option>
                            <option value="website">Website</option>
                            <option value="referral">Referral</option>
                            <option value="phone">Phone Inquiry</option>
                            <option value="walkin">Walk-in</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Create Deal</button>
            </form>
        </div>
    </div>
</div>
