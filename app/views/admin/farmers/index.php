<?php
$page_title = $page_title ?? 'Farmers Management';
$farmers = $farmers ?? [];
$totalFarmers = $totalFarmers ?? 0;
$activeAgreements = $activeAgreements ?? 0;
$activeLoans = $activeLoans ?? 0;
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-tractor text-success me-2"></i> Farmers Management</h4>
        <div>
            <input type="text" id="farmerSearch" class="form-control form-control-sm d-inline-block w-auto me-2" placeholder="Search farmers..." onkeyup="filterFarmers()">
        </div>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-success bg-opacity-10 text-success rounded p-3"><i class="fas fa-tractor fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">Total Farmers</h6><h3 class="mb-0"><?php echo $totalFarmers; ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-primary bg-opacity-10 text-primary rounded p-3"><i class="fas fa-file-signature fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">Active Agreements</h6><h3 class="mb-0"><?php echo $activeAgreements; ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-warning bg-opacity-10 text-warning rounded p-3"><i class="fas fa-hand-holding-usd fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">Active Loans</h6><h3 class="mb-0"><?php echo $activeLoans; ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Farmers</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="farmersTable">
                    <thead class="table-light">
                        <tr>
                            <th>Farmer Name</th>
                            <th>Mobile</th>
                            <th>Land Area</th>
                            <th>Location</th>
                            <th>Total Price</th>
                            <th>Paid</th>
                            <th>Pending</th>
                            <th>Agreement Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($farmers as $f): ?>
                        <tr class="farmer-row">
                            <td><strong><?php echo htmlspecialchars($f['farmer_name'] ?? ''); ?></strong></td>
                            <td><?php echo htmlspecialchars($f['farmer_mobile'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($f['land_area'] ?? '0'); ?> sq.ft</td>
                            <td><?php echo htmlspecialchars($f['district'] ?? ($f['location'] ?? '')); ?></td>
                            <td>₹<?php echo number_format($f['total_land_price'] ?? 0); ?></td>
                            <td>₹<?php echo number_format($f['total_paid_amount'] ?? 0); ?></td>
                            <td>₹<?php echo number_format($f['amount_pending'] ?? 0); ?></td>
                            <td>
                                <?php $s = $f['agreement_status'] ?? 'N/A'; ?>
                                <?php if ($s === 'active'): ?><span class="badge bg-success">Active</span>
                                <?php elseif ($s === 'completed'): ?><span class="badge bg-info">Completed</span>
                                <?php elseif ($s === 'terminated'): ?><span class="badge bg-danger">Terminated</span>
                                <?php elseif ($s === 'draft'): ?><span class="badge bg-secondary">Draft</span>
                                <?php else: ?><span class="badge bg-light text-dark"><?php echo htmlspecialchars($s ?? ''); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <a href="<?php echo BASE_URL; ?>/admin/farmers/show/<?php echo $f['id']; ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($farmers)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No farmers found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterFarmers() {
    var input = document.getElementById('farmerSearch').value.toLowerCase();
    document.querySelectorAll('.farmer-row').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().indexOf(input) > -1 ? '' : 'none';
    });
}
</script>
