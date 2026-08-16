<?php $pageTitle = $page_title ?? 'Smart Home Dashboard'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-home me-2"></i><?= htmlspecialchars($pageTitle ?? '') ?></h4>
        <a href="<?= ($base ?? BASE_URL) ?>iot/devices" class="btn btn-outline-primary btn-sm"><i class="fas fa-cog me-1"></i>Manage Devices</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-info mb-2"><i class="fas fa-wifi"></i></div>
                    <h5 class="mb-1"><?= count($iot_devices ?? []) ?></h5>
                    <small class="text-muted">Connected Devices</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-bolt"></i></div>
                    <h5 class="mb-1"><?= ($smart_features['energy_management'] ?? false) ? 'Active' : 'Inactive' ?></h5>
                    <small class="text-muted">Energy Management</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-danger mb-2"><i class="fas fa-shield-alt"></i></div>
                    <h5 class="mb-1"><?= ($smart_features['security_system'] ?? false) ? 'Armed' : 'Disarmed' ?></h5>
                    <small class="text-muted">Security System</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-warning mb-2"><i class="fas fa-thermometer-half"></i></div>
                    <h5 class="mb-1"><?= ($smart_features['climate_control'] ?? false) ? 'Active' : 'Inactive' ?></h5>
                    <small class="text-muted">Climate Control</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-microchip me-2"></i>IoT Devices</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Device</th><th>Type</th><th>Location</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if (!empty($iot_devices)): ?>
                                    <?php foreach ($iot_devices as $d): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($d['device_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($d['device_type_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($d['location'] ?? '-') ?></td>
                                            <td><span class="badge bg-<?= ($d['status'] ?? 'offline') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($d['status'] ?? 'offline') ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No IoT devices found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-star me-2"></i>Smart Features</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $features = $smart_features ?? []; ?>
                    <?php foreach (['energy_management' => 'Energy Management', 'security_system' => 'Security System', 'climate_control' => 'Climate Control', 'lighting_automation' => 'Lighting Automation', 'appliance_control' => 'Appliance Control', 'water_management' => 'Water Management', 'garden_automation' => 'Garden Automation'] as $key => $label): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="badge bg-<?= ($features[$key] ?? false) ? 'success' : 'secondary' ?>"><?= ($features[$key] ?? false) ? 'Enabled' : 'Disabled' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
                    <p class="mb-0 text-muted small"><?= htmlspecialchars($property['city'] ?? '') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
