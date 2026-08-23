<?php
$vendor = $vendor ?? [];
$contracts = $contracts ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= BASE_URL ?>/admin/vendors" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-2"></i>Back to Vendors
            </a>
            <h1 class="h3 mt-2 mb-0"><?= htmlspecialchars($vendor['vendor_name'] ?? 'Vendor Details') ?></h1>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/vendors/edit/<?= $vendor['id'] ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?= BASE_URL ?>/admin/vendors/contracts/<?= $vendor['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-file-contract"></i> Contracts
            </a>
        </div>
    </div>



    <div class="row">
        <!-- Left Column -->
        <div class="col-md-4 mb-4">
            <!-- Vendor Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Vendor Information</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Vendor Type</small>
                        <?php
                        $typeLabels = [
                            'contractor' => ['badge' => 'primary', 'label' => 'Contractor'],
                            'supplier' => ['badge' => 'info', 'label' => 'Supplier'],
                            'service_provider' => ['badge' => 'success', 'label' => 'Service Provider'],
                            'consultant' => ['badge' => 'secondary', 'label' => 'Consultant'],
                            'transport' => ['badge' => 'warning', 'label' => 'Transport'],
                            'other' => ['badge' => 'dark', 'label' => 'Other'],
                        ];
                        $t = $typeLabels[$vendor['vendor_type'] ?? 'other'] ?? ['badge' => 'dark', 'label' => 'Other'];
                        ?>
                        <span class="badge bg-<?= $t['badge'] ?>"><?= $t['label'] ?></span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Status</small>
                        <?php if (($vendor['status'] ?? '') === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php elseif (($vendor['status'] ?? '') === 'inactive'): ?>
                            <span class="badge bg-warning">Inactive</span>
                        <?php elseif (($vendor['status'] ?? '') === 'blacklisted'): ?>
                            <span class="badge bg-danger">Blacklisted</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= ucfirst($vendor['status'] ?? 'Unknown') ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Rating</small>
                        <?php $rating = floatval($vendor['rating'] ?? 0); ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?= $i <= $rating ? '' : ' text-muted' ?> style-42188"></i>
                        <?php endfor; ?>
                        <span class="text-muted">(<?= $rating ?>)</span>
                    </div>

                    <div class="mb-0">
                        <small class="text-muted d-block">Created By</small>
                        <strong><?= htmlspecialchars($vendor['created_by_name'] ?? 'System') ?></strong>
                    </div>
                </div>
            </div>

            <!-- Contact Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Details</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Contact Person</small>
                        <strong><?= htmlspecialchars($vendor['contact_person'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <strong><?= htmlspecialchars($vendor['phone'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong><?= htmlspecialchars($vendor['email'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Address</small>
                        <strong><?= nl2br(htmlspecialchars($vendor['address'] ?? 'N/A')) ?></strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">City / State</small>
                        <strong><?= htmlspecialchars(($vendor['city'] ?? '') . ($vendor['city'] && $vendor['state'] ? ', ' : '') . ($vendor['state'] ?? 'N/A')) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <?php if (!empty($vendor['phone'])): ?>
                            <a href="tel:<?= htmlspecialchars($vendor['phone'] ?? '') ?>" class="btn btn-outline-primary">
                                <i class="fas fa-phone me-2"></i>Call
                            </a>
                            <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $vendor['phone']) ?>" target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($vendor['email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($vendor['email'] ?? '') ?>" class="btn btn-info">
                                <i class="fas fa-envelope me-2"></i>Send Email
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-8">
            <!-- Tax & Compliance -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Tax & Compliance</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">GST Number</small>
                            <strong><?= htmlspecialchars($vendor['gst_number'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">PAN Number</small>
                            <strong><?= htmlspecialchars($vendor['pan_number'] ?? 'N/A') ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-university me-2"></i>Bank Details</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Bank Name</small>
                            <strong><?= htmlspecialchars($vendor['bank_name'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Account Number</small>
                            <strong><?= htmlspecialchars($vendor['bank_account'] ?? 'N/A') ?></strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">IFSC Code</small>
                            <strong><?= htmlspecialchars($vendor['ifsc_code'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Payment Terms</small>
                            <strong><?= ucfirst(str_replace('_', ' ', $vendor['payment_terms'] ?? '30_days')) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contract Period -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Contract Period</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Contract Start</small>
                            <strong><?= !empty($vendor['contract_start']) ? date('d M Y', strtotime($vendor['contract_start'])) : 'N/A' ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Contract End</small>
                            <strong><?= !empty($vendor['contract_end']) ? date('d M Y', strtotime($vendor['contract_end'])) : 'N/A' ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <?php if (!empty($vendor['notes'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($vendor['notes'] ?? '')) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Contracts -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-file-contract me-2"></i>Recent Contracts / Purchase Orders</h6>
                    <a href="<?= BASE_URL ?>/admin/vendors/contracts/<?= $vendor['id'] ?>" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($contracts)): ?>
                        <p class="text-muted text-center mb-0">No contracts or purchase orders yet</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>PO #</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contracts as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['po_number'] ?? $c['id'] ?? '-') ?></td>
                                        <td><?= number_format(floatval($c['total_amount'] ?? $c['amount'] ?? 0), 2) ?></td>
                                        <td><span class="badge bg-<?= ($c['status'] ?? '') === 'completed' ? 'success' : (($c['status'] ?? '') === 'pending' ? 'warning' : 'info') ?>"><?= ucfirst($c['status'] ?? 'draft') ?></span></td>
                                        <td><?= isset($c['created_at']) ? date('d M Y', strtotime($c['created_at'])) : '-' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
