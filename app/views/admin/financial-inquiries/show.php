<?php
$page_title = $page_title ?? 'Financial Inquiry';
$active_page = 'financial-inquiries';

$statusLabels = [
    'new' => ['label' => 'New', 'class' => 'primary'],
    'contacted' => ['label' => 'Contacted', 'class' => 'info'],
    'converted' => ['label' => 'Converted', 'class' => 'success'],
    'closed' => ['label' => 'Closed', 'class' => 'secondary'],
];
$sl = $statusLabels[$inquiry['status']] ?? ['label' => $inquiry['status'], 'class' => 'secondary'];
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-money-check-alt"></i> Inquiry #<?= $inquiry['id'] ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/financial-inquiries" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-user"></i> Inquiry Details</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <table class="table table-bordered">
                    <tr><th style="width:200px">Name</th><td><?= htmlspecialchars($inquiry['name'] ?? '') ?></td></tr>
                    <tr><th>Email</th><td><?= htmlspecialchars($inquiry['email'] ?? '-') ?></td></tr>
                    <tr><th>Phone</th><td><?= htmlspecialchars($inquiry['phone'] ?? '-') ?></td></tr>
                    <tr><th>Service Interest</th><td><span class="badge bg-light text-dark"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $inquiry['service_interest'] ?? 'General'))) ?></span></td></tr>
                    <tr><th>Message</th><td><?= nl2br(htmlspecialchars($inquiry['message'] ?? '-')) ?></td></tr>
                    <tr><th>Current Status</th><td><span class="badge bg-<?= $sl['class'] ?> fs-6"><?= $sl['label'] ?></span></td></tr>
                    <tr><th>Submitted</th><td><?= date('d M Y h:i A', strtotime($inquiry['created_at'])) ?></td></tr>
                    <?php if (!empty($inquiry['updated_at']) && $inquiry['updated_at'] !== $inquiry['created_at']): ?>
                        <tr><th>Last Updated</th><td><?= date('d M Y h:i A', strtotime($inquiry['updated_at'])) ?></td></tr>
                    <?php endif; ?>
                </table>

                <?php if (!empty($inquiry['notes'])): ?>
                    <h6 class="mt-3"><i class="fas fa-sticky-note"></i> Notes</h6>
                    <div class="bg-light p-3 rounded">
                        <pre class="mb-0" style="white-space:pre-wrap"><?= htmlspecialchars($inquiry['notes']) ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Update Status</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/financial-inquiries/<?= $inquiry['id'] ?>/status">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select class="form-select" name="status" required>
                            <?php foreach ($statusLabels as $key => $info): ?>
                                <option value="<?= $key ?>" <?= ($inquiry['status'] ?? '') === $key ? 'selected' : '' ?>><?= $info['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Add notes about this update..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
