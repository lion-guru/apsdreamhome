<?php $pageTitle = 'Create Sale'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-plus-circle me-2"></i>Create Sale</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/sales">Sales</a></li>
                    <li class="breadcrumb-item active">Create Sale</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="/admin/sales/bookings/store">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Customer <span class="text-danger">*</span></label><select name="customer_id" class="form-select" required><option value="">Select Customer</option><?php foreach ($users as $c): ?><option value="<?= $c['id'] ?>"><?= $c['name'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Property <span class="text-danger">*</span></label><select name="property_id" class="form-select" required><option value="">Select Property</option><?php foreach ($properties as $p): ?><option value="<?= $p['id'] ?>"><?= $p['title'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Sale Date</label><input type="date" name="sale_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Sale Price <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Payment Mode</label><select name="payment_mode" class="form-select"><option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="cheque">Cheque</option><option value="loan">Loan</option><option value="mixed">Mixed</option></select></div>
                    <div class="col-md-4"><label class="form-label">Associate</label><select name="associate_id" class="form-select"><option value="">None</option><?php foreach ($users as $a): ?><option value="<?= $a['id'] ?>"><?= $a['name'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Sale</button> <a href="/admin/sales" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
