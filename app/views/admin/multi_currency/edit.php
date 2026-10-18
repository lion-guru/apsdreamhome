<?php include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Edit Rate: <?php echo htmlspecialchars($rate['base_currency']); ?> / <?php echo htmlspecialchars($rate['target_currency']); ?></h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Update Exchange Rate</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="/admin/multi-currency/update/<?php echo (int)$rate['id']; ?>" id="editRateForm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Pair</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($rate['base_currency'] . ' / ' . $rate['target_currency']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Rate <span class="text-danger">*</span></label>
                                            <input type="number" name="rate" class="form-control" step="0.000001" min="0.000001" value="<?php echo htmlspecialchars($rate['rate']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" value="1" <?php if ((int)$rate['is_active'] === 1) echo 'checked'; ?>>
                                        <label class="custom-control-label" for="isActive">Active</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                                <a href="/admin/multi-currency" class="btn btn-secondary">Back</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script nonce="<?php echo htmlspecialchars($csp_nonce ?? ''); ?>">
document.getElementById('editRateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var fd = new FormData(form);
    if (!document.getElementById('isActive').checked) fd.delete('is_active');
    fetch(form.action, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            window.location.href = d.redirect || '/admin/multi-currency';
        } else {
            alert(d.error || 'Failed');
        }
    })
    .catch(function() { form.submit(); });
});
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>