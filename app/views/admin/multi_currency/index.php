<?php include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Multi-Currency Exchange Rates</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin/accounting">Accounting</a></li>
                        <li class="breadcrumb-item active">Multi-Currency</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-md-2 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">Total Pairs</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo (int)($stats['total_pairs'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">Active</div>
                            <div class="h5 mb-0 font-weight-bold text-success"><?php echo (int)($stats['active'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">Inactive</div>
                            <div class="h5 mb-0 font-weight-bold text-secondary"><?php echo (int)($stats['inactive'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">API Sourced</div>
                            <div class="h5 mb-0 font-weight-bold text-info"><?php echo (int)($stats['api_sourced'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">Manual</div>
                            <div class="h5 mb-0 font-weight-bold text-warning"><?php echo (int)($stats['manual_sourced'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Exchange Rates</h3>
                            <div>
                                <form method="post" action="/admin/multi-currency/refresh" id="refreshForm" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                    <button type="submit" class="btn btn-sm btn-info"><i class="fas fa-sync-alt"></i> Refresh All</button>
                                </form>
                                <a href="/admin/multi-currency/create" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add Rate</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Pair</th>
                                        <th>Rate</th>
                                        <th>Source</th>
                                        <th>Fetched</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach (($rates ?? []) as $r): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($r['base_currency']); ?> / <?php echo htmlspecialchars($r['target_currency']); ?></strong></td>
                                        <td><?php echo number_format((float)$r['rate'], 6); ?></td>
                                        <td>
                                            <?php if ($r['source'] === 'api'): ?>
                                                <span class="badge bg-info">API</span>
                                            <?php elseif ($r['source'] === 'manual'): ?>
                                                <span class="badge bg-warning">Manual</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Test</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($r['fetched_at']); ?></small></td>
                                        <td>
                                            <?php if ((int)$r['is_active'] === 1): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <a href="/admin/multi-currency/edit/<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteRate(<?php echo (int)$r['id']; ?>)"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($rates ?? [])): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">No exchange rates configured.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Currency Converter</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" class="form-control" id="convAmount" value="1" step="0.01" min="0.01">
                            </div>
                            <div class="form-group">
                                <label>From</label>
                                <select class="form-control" id="convFrom">
                                <?php foreach (($currencies ?? []) as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>" <?php if ($c === 'INR') echo 'selected'; ?>><?php echo htmlspecialchars($c); ?></option>
                                <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>To</label>
                                <select class="form-control" id="convTo">
                                <?php foreach (($currencies ?? []) as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>" <?php if ($c === 'USD') echo 'selected'; ?>><?php echo htmlspecialchars($c); ?></option>
                                <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary btn-block" onclick="convertCurrency()"><i class="fas fa-exchange-alt"></i> Convert</button>
                            <div id="convResult" class="mt-3" style="display:none;">
                                <div class="alert alert-success mb-0">
                                    <div class="font-weight-bold" id="convValue"></div>
                                    <small class="text-muted" id="convMeta"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script nonce="<?php echo htmlspecialchars($csp_nonce ?? ''); ?>">
function convertCurrency() {
    var amount = document.getElementById('convAmount').value;
    var from = document.getElementById('convFrom').value;
    var to = document.getElementById('convTo').value;
    if (!amount || amount <= 0) { alert('Enter valid amount'); return; }
    fetch('/admin/multi-currency/convert', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=<?php echo urlencode($_SESSION['csrf_token'] ?? ''); ?>&amount=' + encodeURIComponent(amount) + '&from_currency=' + encodeURIComponent(from) + '&to_currency=' + encodeURIComponent(to)
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            document.getElementById('convValue').textContent = amount + ' ' + from + ' = ' + d.converted_amount.toLocaleString('en-IN') + ' ' + d.to;
            document.getElementById('convMeta').textContent = 'Rate: 1 ' + d.from + ' = ' + d.rate + ' ' + d.to + ' | ' + d.fetched_at;
            document.getElementById('convResult').style.display = 'block';
        } else {
            alert(d.error || 'Conversion failed');
        }
    })
    .catch(function() { alert('Request failed'); });
}

document.getElementById('refreshForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    fetch(form.action, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: new FormData(form)
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        alert(d.message || 'Refreshed');
        if (d.success) location.reload();
    })
    .catch(function() { alert('Refresh failed'); });
});

function deleteRate(id) {
    if (!confirm('Delete this rate pair?')) return;
    fetch('/admin/multi-currency/delete/' + id, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=<?php echo urlencode($_SESSION['csrf_token'] ?? ''); ?>'
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) location.reload();
        else alert(d.error || 'Delete failed');
    });
}
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>