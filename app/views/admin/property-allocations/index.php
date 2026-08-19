<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Property Allocation Management</h1>
            <p class="text-muted mb-0">Manage plot allocations and bookings</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/admin/property-allocations/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Allocation
            </a>
            <a href="<?= BASE_URL ?>/admin/property-allocations/calendar" class="btn btn-info ms-2">
                <i class="fas fa-calendar me-2"></i>Availability Calendar
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body aps-cp-card-body">
                    <h6 class="card-subtitle mb-2">Total Allocations</h6>
                    <h3 class="card-title mb-0"><?= $stats['total_allocations'] ?? 0 ?></h3>
                    <small class="text-white-75">Total Count</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body aps-cp-card-body">
                    <h6 class="card-subtitle mb-2">Confirmed</h6>
                    <h3 class="card-title mb-0"><?= $stats['confirmed_allocations'] ?? 0 ?></h3>
                    <small class="text-white-75">₹<?= number_format($stats['confirmed_amount'] ?? 0) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body aps-cp-card-body">
                    <h6 class="card-subtitle mb-2">Pending</h6>
                    <h3 class="card-title mb-0"><?= $stats['pending_allocations'] ?? 0 ?></h3>
                    <small class="text-white-75">Awaiting confirmation</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body aps-cp-card-body">
                    <h6 class="card-subtitle mb-2">Available Properties</h6>
                    <h3 class="card-title mb-0"><?= $stats['available_properties'] ?? 0 ?></h3>
                    <small class="text-white-75">Ready for allocation</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Allocations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">All Allocations</h6>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Filter by Status
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/property-allocations">All</a>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/property-allocations?status=pending">Pending</a>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/property-allocations?status=confirmed">Confirmed</a>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/property-allocations?status=cancelled">Cancelled</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Allocation #</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Booking Amount</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($allocations)): ?>
                            <?php foreach ($allocations as $allocation): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($allocation['allocation_number'] ?? '') ?></strong></td>
                                    <td>
                                        <div><?= htmlspecialchars($allocation['customer_name'] ?? 'N/A') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($allocation['customer_email'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($allocation['property_title'] ?? 'N/A') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($allocation['property_location'] ?? '') ?></small>
                                    </td>
                                    <td><strong>₹<?= number_format($allocation['booking_amount'] ?? 0) ?></strong></td>
                                    <td><strong>₹<?= number_format($allocation['total_price'] ?? 0) ?></strong></td>
                                    <td>
                                        <?php 
                                        $statusClass = [
                                            'confirmed' => 'success',
                                            'pending' => 'warning',
                                            'cancelled' => 'danger',
                                            'transferred' => 'info'
                                        ];
                                        $class = $statusClass[$allocation['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $class ?>"><?= htmlspecialchars($allocation['status_label'] ?? $allocation['status']) ?></span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($allocation['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/admin/property-allocations/<?= $allocation['id'] ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($allocation['status'] == 'pending'): ?>
                                                <a href="<?= BASE_URL ?>/admin/property-allocations/<?= $allocation['id'] ?>/confirm" class="btn btn-outline-success" title="Confirm" onclick="return confirm('Confirm this property allocation?')">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/admin/property-allocations/<?= $allocation['id'] ?>/cancel" class="btn btn-outline-danger" title="Cancel" onclick="return confirm('Cancel this property allocation?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-home fa-3x mb-3"></i>
                                        <p class="mb-0">No property allocations found. Create your first allocation.</p>
                                        <a href="<?= BASE_URL ?>/admin/property-allocations/create" class="btn btn-primary mt-2">Create Allocation</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
