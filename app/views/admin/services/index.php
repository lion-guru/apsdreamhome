<?php
if (!is_dir(__DIR__)) {
    mkdir(__DIR__, 0755, true);
}
?>
<!-- Services Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Service Interests</h1>
        <p class="text-muted mb-0">Track customer interest in services</p>
    </div>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error) && $error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Service Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <a href="<?php echo BASE_URL; ?>/admin/services" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center <?php echo !$serviceType ? 'bg-primary text-white' : 'bg-light'; ?>">
                <div class="card-body py-2">
                    <div class="h4 mb-0"><?php echo $total; ?></div>
                    <small>Total</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2">
        <a href="<?php echo BASE_URL; ?>/admin/services?service=home_loan" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center <?php echo $serviceType === 'home_loan' ? 'bg-success text-white' : ''; ?>">
                <div class="card-body py-2">
                    <div class="h4 mb-0"><?php echo $counts['home_loan'] ?? 0; ?></div>
                    <small>Home Loan</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2">
        <a href="<?php echo BASE_URL; ?>/admin/services?service=legal" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center <?php echo $serviceType === 'legal' ? 'bg-success text-white' : ''; ?>">
                <div class="card-body py-2">
                    <div class="h4 mb-0"><?php echo $counts['legal'] ?? 0; ?></div>
                    <small>Legal</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2">
        <a href="<?php echo BASE_URL; ?>/admin/services?service=interior" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center <?php echo $serviceType === 'interior' ? 'bg-success text-white' : ''; ?>">
                <div class="card-body py-2">
                    <div class="h4 mb-0"><?php echo $counts['interior'] ?? 0; ?></div>
                    <small>Interior</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2">
        <a href="<?php echo BASE_URL; ?>/admin/services?service=registry" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center <?php echo $serviceType === 'registry' ? 'bg-success text-white' : ''; ?>">
                <div class="card-body py-2">
                    <div class="h4 mb-0"><?php echo $counts['registry'] ?? 0; ?></div>
                    <small>Registry</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2">
        <a href="<?php echo BASE_URL; ?>/admin/services?service=mutation" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center <?php echo $serviceType === 'mutation' ? 'bg-success text-white' : ''; ?>">
                <div class="card-body py-2">
                    <div class="h4 mb-0"><?php echo $counts['mutation'] ?? 0; ?></div>
                    <small>Mutation</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name, phone..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="service" class="form-select">
                    <option value="">All Services</option>
                    <?php foreach ($serviceLabels as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $serviceType === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="new" <?php echo $status === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="contacted" <?php echo $status === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                    <option value="interested" <?php echo $status === 'interested' ? 'selected' : ''; ?>>Interested</option>
                    <option value="not_interested" <?php echo $status === 'not_interested' ? 'selected' : ''; ?>>Not Interested</option>
                    <option value="converted" <?php echo $status === 'converted' ? 'selected' : ''; ?>>Converted</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Services Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (!empty($services)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4">Customer</th>
                        <th class="border-0">Service</th>
                        <th class="border-0">Property</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Date</th>
                        <th class="border-0 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $svc): ?>
                    <tr class="<?php echo $svc['status'] === 'new' ? 'table-warning' : ''; ?>">
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($svc['name'] ?? 'Unknown'); ?></div>
                            <small class="text-muted">
                                <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($svc['phone'] ?? ''); ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                $colors = [
                                    'home_loan' => 'primary',
                                    'legal' => 'info',
                                    'registry' => 'success',
                                    'mutation' => 'success',
                                    'interior' => 'warning',
                                    'home_insurance' => 'secondary',
                                    'property_tax' => 'secondary',
                                    'rental_agreement' => 'dark',
                                    'Tenant_verification' => 'secondary'
                                ];
                                echo $colors[$svc['service_type']] ?? 'secondary';
                            ?>">
                                <?php echo $serviceLabels[$svc['service_type']] ?? $svc['service_type']; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($svc['property_name'] ?? '-'); ?>
                        </td>
                        <td>
                            <?php
                            $statusColors = [
                                'new' => 'bg-danger',
                                'contacted' => 'bg-info',
                                'interested' => 'bg-warning text-dark',
                                'not_interested' => 'bg-secondary',
                                'converted' => 'bg-success'
                            ];
                            ?>
                            <span class="badge <?php echo $statusColors[$svc['status']] ?? 'bg-secondary'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $svc['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <small><?php echo date('d M Y', strtotime($svc['created_at'])); ?></small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="<?php echo BASE_URL; ?>/admin/services/view/<?php echo $svc['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (!empty($svc['phone'])): ?>
                                <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $svc['phone']); ?>" target="_blank" class="btn btn-sm btn-success">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-handshake fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No service interests found</h5>
        </div>
        <?php endif; ?>
    </div>
</div>
