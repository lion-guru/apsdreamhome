<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-th me-2"></i>Plot Inventory</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/mlm-realestate/plots?block=" class="btn btn-sm <?= empty($_GET['block']) ? 'btn-dark' : 'btn-outline-secondary' ?>">All</a>
            <?php foreach ($blocks as $b): ?>
            <a href="<?= BASE_URL ?>/admin/mlm-realestate/plots?block=<?= urlencode($b) ?>" class="btn btn-sm <?= ($_GET['block'] ?? '') === $b ? 'btn-dark' : 'btn-outline-secondary' ?>">Block <?= htmlspecialchars($b) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="row mb-4">
        <?php $statusColors = ['Available'=>'success','Hold'=>'warning','Tokenized_25%'=>'info','Registered'=>'primary']; ?>
        <?php foreach ($status_summary as $s): ?>
        <div class="col-md-3 mb-2">
            <div class="card bg-<?= $statusColors[$s['status']] ?? 'secondary' ?> text-white shadow-sm">
                <div class="card-body text-center py-2">
                    <h6><?= htmlspecialchars($s['status']) ?></h6>
                    <h3 class="mb-0 fw-bold"><?= $s['cnt'] ?></h3>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive" class="style-13405">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light sticky-top"><tr><th>Block</th><th>Plot No</th><th>Size</th><th>Dimension</th><th>Basic Price</th><th>PLC</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($plots as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['block_name']) ?></strong></td>
                            <td><?= htmlspecialchars($p['plot_no']) ?></td>
                            <td><?= number_format((float)$p['size_sqft']) ?> sqft</td>
                            <td><?= htmlspecialchars($p['dimension'] ?? '-') ?></td>
                            <td>₹<?= number_format((float)$p['basic_price'], 2) ?></td>
                            <td>₹<?= number_format((float)$p['plc_charges'], 2) ?></td>
                            <td>₹<?= number_format((float)$p['basic_price'] + (float)$p['plc_charges'], 2) ?></td>
                            <td><span class="badge bg-<?= $statusColors[$p['status']] ?? 'secondary' ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>