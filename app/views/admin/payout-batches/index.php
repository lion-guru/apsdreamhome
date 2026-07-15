<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-8">
            <h2 style="color:#e0e0e0;"><i class="fas fa-money-check-alt me-2" style="color:#28a745;"></i> Payout Batches</h2>
            <p style="color:#aaa;">Commission payout management with approval workflow</p>
        </div>
        <div class="col-4 text-end">
            <a href="/admin/payout-batches/create" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Batch</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <?php
        $statusConfig = [
            'draft'             => ['icon' => 'fa-edit', 'color' => '#6c757d'],
            'pending_approval'  => ['icon' => 'fa-clock', 'color' => '#ffc107'],
            'approved'          => ['icon' => 'fa-check', 'color' => '#28a745'],
            'processing'        => ['icon' => 'fa-spinner', 'color' => '#17a2b8'],
            'completed'         => ['icon' => 'fa-check-double', 'color' => '#20c997'],
            'rejected'          => ['icon' => 'fa-times', 'color' => '#dc3545'],
        ];
        ?>
        <?php foreach ($statusConfig as $sKey => $sCfg): ?>
            <div class="col-md-2">
                <div class="card" style="background:rgba(<?= $sCfg['color'] ?>,0.08);border:1px solid <?= $sCfg['color'] ?>40;">
                    <div class="card-body text-center py-2">
                        <h4 style="color:<?= $sCfg['color'] ?>;margin:0;"><?= $stats[$sKey]['count'] ?? 0 ?></h4>
                        <small style="color:#888;text-transform:capitalize;"><?= str_replace('_', ' ', $sKey) ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div class="mb-3">
        <a href="/admin/payout-batches" class="btn btn-sm <?= empty($status_filter) ? 'btn-success' : 'btn-outline-secondary' ?>">All</a>
        <?php foreach ($statusConfig as $sKey => $sCfg): ?>
            <a href="/admin/payout-batches?status=<?= $sKey ?>" class="btn btn-sm <?= $status_filter === $sKey ? 'btn-success' : 'btn-outline-secondary' ?>"><?= str_replace('_', ' ', ucfirst($sKey)) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Batch List -->
    <div class="card" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x" style="color:#555;"></i>
                    <h5 style="color:#ccc;margin-top:15px;">No payout batches found</h5>
                    <a href="/admin/payout-batches/create" class="btn btn-success mt-2"><i class="fas fa-plus me-1"></i> Create First Batch</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr style="border-bottom:1px solid #444;">
                                <th style="color:#28a745;">#</th>
                                <th style="color:#28a745;">Name</th>
                                <th style="color:#28a745;">Type</th>
                                <th style="color:#28a745;">Period</th>
                                <th style="color:#28a745;">Entries</th>
                                <th style="color:#28a745;">Total (₹)</th>
                                <th style="color:#28a745;">Status</th>
                                <th style="color:#28a745;">Created By</th>
                                <th style="color:#28a745;">Date</th>
                                <th style="color:#28a745;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr style="border-bottom:1px solid #333;">
                                    <td><?= $item['id'] ?></td>
                                    <td><strong style="color:#e0e0e0;"><?= htmlspecialchars($item['batch_name']) ?></strong></td>
                                    <td><span class="badge bg-info"><?= ucfirst($item['batch_type']) ?></span></td>
                                    <td>
                                        <?php if ($item['period_from'] && $item['period_to']): ?>
                                            <small><?= date('d M', strtotime($item['period_from'])) ?> - <?= date('d M Y', strtotime($item['period_to'])) ?></small>
                                        <?php else: ?>
                                            <small style="color:#666;">No period</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= number_format($item['total_entries']) ?></strong></td>
                                    <td style="color:#28a745;font-weight:bold;">₹<?= number_format((float)$item['total_amount']) ?></td>
                                    <td>
                                        <?php
                                        $sc = $statusConfig[$item['status']] ?? ['color' => '#6c757d'];
                                        ?>
                                        <span class="badge" style="background:<?= $sc['color'] ?>20;color:<?= $sc['color'] ?>;border:1px solid <?= $sc['color'] ?>40;">
                                            <?= str_replace('_', ' ', ucfirst($item['status'])) ?>
                                        </span>
                                    </td>
                                    <td><small><?= htmlspecialchars($item['created_by_name'] ?? 'Admin') ?></small></td>
                                    <td><small><?= date('d M H:i', strtotime($item['created_at'])) ?></small></td>
                                    <td>
                                        <a href="/admin/payout-batches/<?= $item['id'] ?>" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-between align-items-center p-3" style="border-top:1px solid #444;">
                        <small style="color:#888;">Page <?= $page ?> of <?= $total_pages ?></small>
                        <div>
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="/admin/payout-batches?status=<?= $status_filter ?>&page=<?= $i ?>"
                                   class="btn btn-sm <?= $i === $page ? 'btn-success' : 'btn-outline-secondary' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
