<?php $pageTitle = 'Edit Sale'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Sale</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/sales">Sales</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/sales/bookings/<?= $sale['id'] ?? 0 ?>">#<?= $sale['id'] ?? 0 ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/sales/bookings/<?= $sale['id'] ?? 0 ?>/update">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Customer</label><select name="customer_id" class="form-select"><option value="">Select Customer</option><?php foreach ($users as $c): ?><option value="<?= $c['id'] ?>" <?= ($sale['customer_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= $c['name'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Property</label><select name="property_id" class="form-select"><option value="">Select Property</option><?php foreach ($properties as $p): ?><option value="<?= $p['id'] ?>" <?= ($sale['property_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= $p['title'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Sale Date</label><input type="date" name="sale_date" class="form-control" value="<?= $sale['sale_date'] ?? date('Y-m-d') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Sale Price</label><input type="number" name="amount" class="form-control" value="<?= $sale['amount'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Payment Mode</label><select name="payment_mode" class="form-select"><option value="cash" <?= ($sale['payment_mode'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option><option value="bank_transfer" <?= ($sale['payment_mode'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option><option value="cheque" <?= ($sale['payment_mode'] ?? '') === 'cheque' ? 'selected' : '' ?>>Cheque</option></select></div>
                    <div class="col-md-4"><label class="form-label">Associate</label><select name="associate_id" class="form-select"><option value="">None</option><?php foreach ($users as $a): ?><option value="<?= $a['id'] ?>" <?= ($sale['associate_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= $a['name'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= $sale['notes'] ?? '' ?></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Sale</button> <a href="<?= BASE_URL ?>/admin/sales" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
