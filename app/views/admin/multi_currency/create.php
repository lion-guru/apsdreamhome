<?php include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Add Exchange Rate</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">New Currency Pair</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="/admin/multi-currency/store" id="addRateForm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Base Currency <span class="text-danger">*</span></label>
                                            <select name="base_currency" class="form-control" required>
                                            <?php foreach (($currencies ?? []) as $c): ?>
                                                <option value="<?php echo htmlspecialchars($c); ?>" <?php if ($c === 'INR') echo 'selected'; ?>><?php echo htmlspecialchars($c); ?></option>
                                            <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Target Currency <span class="text-danger">*</span></label>
                                            <select name="target_currency" class="form-control" required>
                                                <option value="">-- Select --</option>
                                            <?php foreach (($currencies ?? []) as $c): ?>
                                                <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                                            <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Rate <span class="text-danger">*</span></label>
                                            <input type="number" name="rate" class="form-control" step="0.000001" min="0.000001" required placeholder="e.g. 83.50">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="hidden" name="is_active" value="1">
                                        <input type="checkbox" class="custom-control-input" id="isActive" checked>
                                        <label class="custom-control-label" for="isActive">Active</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Rate</button>
                                <a href="/admin/multi-currency" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script nonce="<?php echo htmlspecialchars($csp_nonce ?? ''); ?>">
document.getElementById('addRateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    fetch(form.action, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: new FormData(form)
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