<?php $leads = $leads ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Extracted Leads from Calls</h4>
    <button class="btn btn-success btn-sm" onclick="alert('Convert all verified leads')"><i class="fas fa-check-double"></i> Convert All Verified</button>
</div>
<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Interest</th>
                        <th>Source Call</th>
                        <th>Verified</th>
                        <th>Converted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No extracted leads yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($leads as $l): ?>
                            <tr>
                                <td><?= htmlspecialchars($l['name'] ?? $l['lead_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($l['phone'] ?? $l['customer_phone'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($l['email'] ?? '-') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($l['interest'] ?? $l['property_interest'] ?? 'General') ?></span></td>
                                <td><small><?= htmlspecialchars($l['call_id'] ?? $l['source_call_id'] ?? 'N/A') ?></small></td>
                                <td><?php $v = $l['is_verified'] ?? 0; ?>
                                    <span class="badge bg-<?= $v ? 'success' : 'warning' ?>"><?= $v ? 'Verified' : 'Pending' ?></span>
                                </td>
                                <td><?php $cv = $l['is_converted'] ?? 0; ?>
                                    <span class="badge bg-<?= $cv ? 'primary' : 'secondary' ?>"><?= $cv ? 'Converted' : 'Not' ?></span>
                                </td>
                                <td>
                                    <?php if (!($l['is_verified'] ?? 0)): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="alert('Verify lead')" title="Verify"><i class="fas fa-check"></i></button>
                                    <?php endif; ?>
                                    <?php if (!($l['is_converted'] ?? 0) && ($l['is_verified'] ?? 0)): ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="alert('Convert to lead')" title="Convert"><i class="fas fa-exchange-alt"></i></button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-info" onclick="alert('View details')" title="Details"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
