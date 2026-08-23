ï»¿<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-8">
            <h2 class="style-48283"><i class="fas fa-money-check-alt me-2 style-56943"></i> Payout Batches</h2>
            <p class="style-43180">Commission payout management with approval workflow</p>
        </div>
        <div class="col-4 text-end">
            <a href="<?= BASE_URL ?>/admin/payout-batches/create" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Batch</a>
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
                <div class="card style-11438">
                    <div class="card-body text-center py-2">
                        <h4 class="style-22740"><?= $stats[$sKey]['count'] ?? 0 ?></h4>
                        <small class="style-45096"><?= str_replace('_', ' ', $sKey) ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/admin/payout-batches" class="btn btn-sm <?= empty($status_filter) ? 'btn-success' : 'btn-outline-secondary' ?>">All</a>
        <?php foreach ($statusConfig as $sKey => $sCfg): ?>
            <a href="<?= BASE_URL ?>/admin/payout-batches?status=<?= $sKey ?>" class="btn btn-sm <?= $status_filter === $sKey ? 'btn-success' : 'btn-outline-secondary' ?>"><?= str_replace('_', ' ', ucfirst($sKey)) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Batch List -->
    <div class="card style-62867">
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x style-2349"></i>
                    <h5 class="style-39334">No payout batches found</h5>
                    <a href="<?= BASE_URL ?>/admin/payout-batches/create" class="btn btn-success mt-2"><i class="fas fa-plus me-1"></i> Create First Batch</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr class="style-1328">
                                <th class="style-56943">#</th>
                                <th class="style-56943">Name</th>
                                <th class="style-56943">Type</th>
                                <th class="style-56943">Period</th>
                                <th class="style-56943">Entries</th>
                                <th class="style-56943">Total (₹)</th>
                                <th class="style-56943">Status</th>
                                <th class="style-56943">Created By</th>
                                <th class="style-56943">Date</th>
                                <th class="style-56943">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr class="style-23517">
                                    <td><?= $item['id'] ?></td>
                                    <td><strong class="style-48283"><?= htmlspecialchars($item['batch_name'] ?? '') ?></strong></td>
                                    <td><span class="badge bg-info"><?= ucfirst($item['batch_type']) ?></span></td>
                                    <td>
                                        <?php if ($item['period_from'] && $item['period_to']): ?>
                                            <small><?= date('d M', strtotime($item['period_from'])) ?> - <?= date('d M Y', strtotime($item['period_to'])) ?></small>
                                        <?php else: ?>
                                            <small class="style-78225">No period</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= number_format($item['total_entries']) ?></strong></td>
                                    <td class="style-63408">₹<?= number_format((float)$item['total_amount']) ?></td>
                                    <td>
                                        <?php
                                        $sc = $statusConfig[$item['status']] ?? ['color' => '#6c757d'];
                                        ?>
                                        <span class="badge style-18884">
                                            <?= str_replace('_', ' ', ucfirst($item['status'])) ?>
                                        </span>
                                    </td>
                                    <td><small><?= htmlspecialchars($item['created_by_name'] ?? 'Admin') ?></small></td>
                                    <td><small><?= date('d M H:i', strtotime($item['created_at'])) ?></small></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/payout-batches/<?= $item['id'] ?>" class="btn btn-sm btn-outline-info">
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
                    <div class="d-flex justify-content-between align-items-center p-3 style-8524">
                        <small class="style-77712">Page <?= $page ?> of <?= $total_pages ?></small>
                        <div>
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="<?= BASE_URL ?>/admin/payout-batches?status=<?= $status_filter ?>&page=<?= $i ?>"
                                   class="btn btn-sm <?= $i === $page ? 'btn-success' : 'btn-outline-secondary' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
