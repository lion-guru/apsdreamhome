<?php $pageTitle = 'Customer Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-user me-2"></i>Customer Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/users">users</a></li>
                    <li class="breadcrumb-item active"><?= $customer['name'] ?? 'Customer' ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/users/<?= $customer['id'] ?? 0 ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($customer)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-user-slash fa-4x d-block mb-3"></i><h5>Customer not found</h5></div>
    <?php else: ?>
    <!-- Customer 360° Tabs -->
    <div class="row g-4">
        <!-- Nav tabs -->
        <div class="col-12">
            <ul class="nav nav-tabs nav-tabs-custom" id="customer360Tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                        <i class="fas fa-user-tie me-2"></i> Overview & KYC
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab" aria-controls="bookings" aria-selected="false">
                        <i class="fas fa-folder me-2"></i> Bookings & Plots
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="emi-tab" data-bs-toggle="tab" data-bs-target="#emi" type="button" role="tab" aria-controls="emi" aria-selected="false">
                        <i class="fas fa-wallet me-2"></i> EMI & Passbook
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="receipts-tab" data-bs-toggle="tab" data-bs-target="#receipts" type="button" role="tab" aria-controls="receipts" aria-selected="false">
                        <i class="fas fa-receipt me-2"></i> Receipts & Invoices
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="legal-tab" data-bs-toggle="tab" data-bs-target="#legal" type="button" role="tab" aria-controls="legal" aria-selected="false">
                        <i class="fas fa-file-meh me-2"></i> Legal Documents
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="associate-tab" data-bs-toggle="tab" data-bs-target="#associate" type="button" role="tab" aria-controls="associate" aria-selected="false">
                        <i class="fas fa-user-friends me-2"></i> Associate Link
                    </button>
                </li>
            </ul>
        </div>
        <!-- Tab contents -->
        <div class="col-12">
            <div class="tab-content" id="customer360TabContent">
                <!-- Tab 1: Overview & KYC -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 text-center">
                                <div class="card-body py-4">
                                    <div class="avatar-lg mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"><?= strtoupper(substr($customer['name'], 0, 1)) ?></div>
                                    <h5 class="mb-1"><?= $customer['name'] ?></h5>
                                    <p class="text-muted mb-2"><?= $customer['email'] ?? 'No email' ?></p>
                                    <span class="badge bg-<?= ($customer['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= ($customer['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> rounded-pill px-3"><?= ucfirst($customer['status'] ?? 'Active') ?></span>
                                    <hr class="my-3">
                                    <h6><i class="fas fa-id-card me-2"></i> KYC Details</h6>
                                    <p class="text-muted small"><strong>Aadhaar:</strong> <?= mask_aadhaar($customer['aadhaar'] ?? '') ?></p>
                                    <p class="text-muted small"><strong>PAN:</strong> <?= mask_pan($customer['pan'] ?? '') ?></p>
                                    <p class="text-muted small"><strong>Phone:</strong> <?= $customer['phone'] ?? '-' ?></p>
                                    <p class="text-muted small"><strong>Alt. Phone:</strong> <?= $customer['alt_phone'] ?? '-' ?></p>
                                    <p class="text-muted small"><strong>City/State:</strong> <?= ($customer['city'] ?? '') . ', ' . ($customer['state'] ?? '') ?></p>
                                    <p class="text-muted small"><strong>Pincode:</strong> <?= $customer['pincode'] ?? '-' ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contact Information</h5></div>
                                <div class="card-body aps-cp-card-body">
                                    <div class="row mb-3"><div class="col-sm-4 text-muted">Phone</div><div class="col-sm-8"><strong><?= $customer['phone'] ?? '-' ?></strong></div></div>
                                    <div class="row mb-3"><div class="col-sm-4 text-muted">Alt. Phone</div><div class="col-sm-8"><?= $customer['alt_phone'] ?? '-' ?></div></div>
                                    <div class="row mb-3"><div class="col-sm-4 text-muted">Email</div><div class="col-sm-8"><?= $customer['email'] ?? '-' ?></div></div>
                                    <div class="row mb-3"><div class="col-sm-4 text-muted">Address</div><div class="col-sm-8"><?= $customer['address'] ?? '-' ?></div></div>
                                    <div class="row mb-3"><div class="col-sm-4 text-muted">City / State</div><div class="col-sm-8"><?= ($customer['city'] ?? '') . ', ' . ($customer['state'] ?? '') ?></div></div>
                                    <div class="row"><div class="col-sm-4 text-muted">Pincode</div><div class="col-sm-8"><?= $customer['pincode'] ?? '-' ?></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tab 2: Bookings & Plots -->
                <div class="tab-pane fade" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
                    <div class="row g-4">
                        <div class="col-12">
                            <h5><i class="fas fa-folder me-2"></i> Plot Bookings</h5>
                        </div>
                        <?php if (empty($customer_bookings)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No plot bookings found for this customer.
                            <a href="<?= BASE_URL ?>/admin/plots/create" class="btn btn-link text-primary mt-2">Add First Plot</a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Plot #</th>
                                        <th>Colony</th>
                                        <th>Date</th>
                                        <th>Token Paid</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customer_bookings as $bk): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= BASE_URL ?>/admin/plots/<?= $bk['plot_id'] ?? 0 ?>" class="text-primary">
                                                    <?= htmlspecialchars($bk['plot_number'] ?? 'N/A') ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($bk['colony_name'] ?? 'N/A') ?></td>
                                            <td><?= date('d M Y', strtotime($bk['booking_date'] ?? '')) ?></td>
                                            <td>₹<?= number_format(intval($bk['token_paid'] ?? 0)) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $bk['status'] === 'available' ? 'success' : ($bk['status'] === 'booked' ? 'warning' : ($bk['status'] === 'registered' ? 'info' : 'danger')) ?> fs-6">
                                                    <?= ucfirst($bk['status'] ?? '') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/admin/bookings/<?= $bk['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-book me-1"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Plot Summary -->
                        <div class="mt-4">
                            <h6><i class="fas fa-chart-bar me-2"></i> Plot Summary</h6>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Total Bookings:</strong> <?= count($customer_bookings) ?></p>
                                </div>
                                <div class="col-6 text-end">
                                    <p><strong>Total Token Amount:</strong> ₹<?= number_format(array_sum(array_map(fn($b) => $b['token_paid'] ?? 0, $customer_bookings)), 2) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Tab 3: EMI & Passbook -->
                <div class="tab-pane fade" id="emi" role="tabpanel" aria-labelledby="emi-tab">
                    <div class="row g-4">
                        <div class="col-12">
                            <h5><i class="fas fa-wallet me-2"></i> EMI & Passbook</h5>
                        </div>
                        <?php if (empty($emi_schedules)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No EMI schedules found for this customer's bookings.
                            <a href="<?= BASE_URL ?>/admin/bookings" class="btn btn-link text-primary mt-2">View All Bookings</a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Booking #</th>
                                        <th>Plot</th>
                                        <th>Due Date</th>
                                        <th>EMI Amount</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($emi_schedules as $emi): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/bookings/<?= $emi['booking_id'] ?? 0 ?>" class="text-primary">
                                                #<?= $emi['booking_id'] ?? 'N/A' ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($emi['plot_number'] ?? 'N/A') ?></td>
                                        <td><?= date('d M Y', strtotime($emi['due_date'] ?? '')) ?></td>
                                        <td>₹<?= number_format(floatval($emi['emi_amount'] ?? 0), 2) ?></td>
                                        <td>₹<?= number_format(floatval($emi['paid_amount'] ?? 0), 2) ?></td>
                                        <td>₹<?= number_format(floatval($emi['balance'] ?? 0), 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $emi['status'] === 'paid' ? 'success' : ($emi['status'] === 'overdue' ? 'danger' : ($emi['status'] === 'pending' ? 'warning' : 'secondary')) ?> fs-6">
                                                <?= ucfirst($emi['status'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/bookings/<?= $emi['booking_id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary small">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                            <?php if ($emi['status'] !== 'paid'): ?>
                                                <button class="btn btn-sm btn-outline-warning ms-1" onclick="collectEMI(<?= $emi['id'] ?>)">
                                                    <i class="fas fa-hand-holding-usd me-1"></i> Collect
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- EMI Summary -->
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Total EMIs:</strong> <?= count($emi_schedules) ?></p>
                                    <p><strong>Total Due:</strong> ₹<?= number_format(array_sum(array_map(fn($e) => $e['balance'] ?? 0, $emi_schedules)), 2) ?></p>
                                </div>
                                <div class="col-6 text-end">
                                    <p><strong>Total Paid:</strong> ₹<?= number_format(array_sum(array_map(fn($e) => $e['paid_amount'] ?? 0, $emi_schedules)), 2) ?></p>
                                    <p><strong>Overdue Count:</strong> <?= count(array_filter($emi_schedules, fn($e) => $e['status'] === 'overdue')) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Tab 4: Receipts & Invoices -->
                <div class="tab-pane fade" id="receipts" role="tabpanel" aria-labelledby="receipts-tab">
                    <div class="row g-4">
                        <div class="col-12">
                            <h5><i class="fas fa-receipt me-2"></i> Payment Receipts & Invoices</h5>
                        </div>
                        <?php if (empty($payment_receipts)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No payment receipts found for this customer.
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Receipt #</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Amount</th>
                                        <th>Transaction ID</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payment_receipts as $pr): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pr['receipt_number'] ?? 'N/A') ?></td>
                                        <td><?= date('d M Y h:i A', strtotime($pr['created_at'] ?? '')) ?></td>
                                        <td><?= ucfirst(htmlspecialchars($pr['payment_method'] ?? '')) ?></td>
                                        <td>₹<?= number_format(floatval($pr['amount'] ?? 0), 2) ?></td>
                                        <td><?= htmlspecialchars($pr['transaction_id'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $pr['status'] === 'completed' ? 'success' : 'warning' ?> fs-6">
                                                <?= ucfirst($pr['status'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary" onclick="printReceipt('<?= $pr['receipt_number'] ?>')">
                                                <i class="fas fa-print"></i> Print
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Tab 5: Legal Documents -->
                <div class="tab-pane fade" id="legal" role="tabpanel" aria-labelledby="legal-tab">
                    <div class="row g-4">
                        <div class="col-12">
                            <h5><i class="fas fa-file-meh me-2"></i> Legal Documents</h5>
                        </div>
                        <div class="row g-3">
                            <?php if (empty($legal_docs)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> No legal documents found for this customer.
                            </div>
                            <?php else: ?>
                            <?php foreach ($legal_docs as $doc): ?>
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-white py-3">
                                        <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($doc['document_type'] ?? 'Document') ?></h6>
                                    </div>
                                    <div class="card-body aps-cp-card-body">
                                        <p class="text-muted small"><strong>Document No:</strong> <?= htmlspecialchars($doc['document_number'] ?? 'N/A') ?></p>
                                        <p class="text-muted small"><strong>Date:</strong> <?= date('d M Y', strtotime($doc['created_at'] ?? '')) ?></p>
                                        <p class="text-muted small"><strong>Status:</strong> <span class="badge bg-<?= $doc['status'] === 'approved' ? 'success' : ($doc['status'] === 'pending' ? 'warning' : 'secondary') ?>"><?= ucfirst($doc['status'] ?? '') ?></span></p>
                                        <a href="<?= $doc['download_url'] ?? BASE_URL ?>/admin/documents/download/<?= $doc['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                        <?php if (!empty($doc['verification_code'])): ?>
                                            <div class="mt-2 text-muted small">
                                                <strong>Verification Code:</strong> <?= htmlspecialchars($doc['verification_code']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Tab 6: Associate Link -->
                <div class="tab-pane fade" id="associate" role="tabpanel" aria-labelledby="associate-tab">
                    <div class="row g-4">
                        <div class="col-12">
                            <h5><i class="fas fa-user-friends me-2"></i> Associate Link</h5>
                        </div>
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Referral / Sponsor Information</h2>
                            </div>
                            <div class="card-body aps-cp-card-body">
                                <?php if (!empty($customer['referral_code'])): ?>
                                <div class="mb-3">
                                    <strong>Referral Code:</strong> <span class="text-primary fw-bold"><?= htmlspecialchars($customer['referral_code']) ?></span>
                                </div>
                                <?php else: ?>
                                <div class="mb-3">
                                    <span class="text-muted">No referral code on record</span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($customer['sponsor_user_id'])): ?>
                                <div class="mb-3">
                                    <strong>Sponsored By:</strong> 
                                    <a href="<?= BASE_URL ?>/admin/associates/<?= $customer['sponsor_user_id'] ?>" class="text-primary">
                                        <?= htmlspecialchars($customer['sponsor_name'] ?? 'Unknown Sponsor') ?>
                                    </a>
                                    <span class="badge bg-info text-white ms-2">Referral</span>
                                </div>
                                <?php else: ?>
                                <div class="mb-3">
                                    <span class="text-muted">No sponsor found</span>
                                </div>
                                <?php endif; ?>
                                <hr class="my-3">
                                <h6><i class="fas fa-handshake me-2"></i>Associate Activity</h6>
                                <p class="text-muted small">
                                    <strong>Referral Status:</strong> 
                                    <?= $customer['referred_by'] === 'system' ? 'System Generated' : (($customer['referred_by'] ?? '') ? 'Active Referral' : 'No Referral') ?>
                                </p>
                                <div class="mt-3">
                                    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-users me-1"></i> View All Associates
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    // Initialize tabs
    document.addEventListener('DOMContentLoaded', function() {
        var customerTabs = new bootstrap.Tab(document.querySelector('#customer360Tabs .nav-link.active'));
        customerTabs.show();
    });

    // EMI Collect function
    function collectEMI(emiId) {
        if (confirm('Collect this EMI payment?')) {
            // AJAX call to collect EMI
            showLoader();
            fetch('<?= BASE_URL ?>/admin/bookings/collect-emi/' + emiId, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(r => r.json())
            .then(d => {
                showToast(d.message || d.error, d.success ? 'success' : 'danger');
                if (d.success) location.reload();
            })
            .catch(() => showToast('Error', 'danger'))
            .finally(() => hideLoader());
        }
    }

    // Print receipt function
    function printReceipt(receiptNumber) {
        window.open('<?= BASE_URL ?>/admin/payments/receipt/' + receiptNumber, '_blank');
    }
</script>