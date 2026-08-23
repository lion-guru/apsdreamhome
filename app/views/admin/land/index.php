ï»¿<?php $pageTitle = 'Land Management'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-map-marked-alt me-2"></i>Land Management</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Land</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/land/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Land</a>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Land</h6><h3 class="mb-0"><?= number_format($totalLand ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Available</h6><h3 class="mb-0"><?= number_format($availableLand ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Under Negotiation</h6><h3 class="mb-0"><?= number_format($negotiationLand ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Sold</h6><h3 class="mb-0"><?= number_format($soldLand ?? 0) ?></h3></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Land Records</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Title</th><th>Location</th><th>Area</th><th>Price</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($landRecords)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3 style-82835"></i>
                                    <h5 class="text-muted">No land records found</h5>
                                    <p class="text-muted mb-3">Add land parcels to your inventory to track acquisitions, pricing, and availability across all colonies.</p>
                                    <a href="<?= BASE_URL ?>/admin/land/create" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Add Land Parcel
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($landRecords as $i => $l): ?>
                            <tr><td class="ps-4"><?= $i+1 ?></td><td><strong><?= $l['title'] ?? 'Land #'.$l['id'] ?></strong></td><td><?= $l['location'] ?? '-' ?></td><td><?= number_format($l['area_sqft'] ?? 0) ?> sqft</td><td class="fw-bold">₹<?= number_format($l['price'] ?? 0, 2) ?></td><td><span class="badge bg-<?= ($l['status'] ?? 'available') === 'available' ? 'success' : (($l['status'] ?? 'available') === 'sold' ? 'danger' : 'warning') ?>-subtle text-<?= ($l['status'] ?? 'available') === 'available' ? 'success' : (($l['status'] ?? 'available') === 'sold' ? 'danger' : 'warning') ?> rounded-pill px-3"><?= ucfirst($l['status'] ?? 'Available') ?></span></td><td class="text-end pe-4"><a href="<?= BASE_URL ?>/admin/land/show/<?= $l['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a> <a href="<?= BASE_URL ?>/admin/land/edit/<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
