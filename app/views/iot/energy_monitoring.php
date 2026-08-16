<?php $pageTitle = $page_title ?? 'Energy Monitoring'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i><?= htmlspecialchars($pageTitle ?? '') ?></h4>
    </div>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
        <div class="card-body aps-cp-card-body">
            <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
            <p class="mb-0 text-muted"><?= htmlspecialchars($property['city'] ?? '') ?></p>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Energy Consumption</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light"><tr><th>Timestamp</th><th>Consumption (kWh)</th><th>Cost (₹)</th><th>Lighting</th><th>HVAC</th><th>Appliances</th><th>Other</th></tr></thead>
                    <tbody>
                        <?php if (!empty($energy_data)): ?>
                            <?php foreach ($energy_data as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['timestamp'] ?? '-') ?></td>
                                    <td><?= $e['consumption_kwh'] ?? 0 ?></td>
                                    <td>₹<?= $e['cost'] ?? 0 ?></td>
                                    <td><?= $e['appliances']['lighting'] ?? 0 ?>%</td>
                                    <td><?= $e['appliances']['hvac'] ?? 0 ?>%</td>
                                    <td><?= $e['appliances']['appliances'] ?? 0 ?>%</td>
                                    <td><?= $e['appliances']['other'] ?? 0 ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No energy data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
