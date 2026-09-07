ï»¿<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-8">
            <h2 ><i class="fas fa-money-check-alt me-2"></i> Payout Batches</h2>
            <p >Commission payout management with approval workflow</p>
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
                <div class="card">
                    <div class="card-body text-center py-2">
                        <h4 ><?= $stats[$sKey]['count'] ?? 0 ?></h4>
                        <small ><?= str_replace('_', ' ', $sKey) ?></small>
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
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x"></i>
                    <h5 >No payout batches found</h5>
                    <a href="<?= BASE_URL ?>/admin/payout-batches/create" class="btn btn-success mt-2"><i class="fas fa-plus me-1"></i> Create First Batch</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr >
                                <th >#</th>
                                <th >Name</th>
                                <th >Type</th>
                                <th >Period</th>
                                <th >Entries</th>
                                <th >Total (₹)</th>
                                <th >Status</th>
                                <th >Created By</th>
                                <th >Date</th>
                                <th >Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr >
                                    <td><?= $item['id'] ?></td>
                                    <td><strong ><?= htmlspecialchars($item['batch_name'] ?? '') ?></strong></td>
                                    <td><span class="badge bg-info"><?= ucfirst($item['batch_type']) ?></span></td>
                                    <td>
                                        <?php if ($item['period_from'] && $item['period_to']): ?>
                                            <small><?= date('d M', strtotime($item['period_from'])) ?> - <?= date('d M Y', strtotime($item['period_to'])) ?></small>
                                        <?php else: ?>
                                            <small >No period</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= number_format($item['total_entries']) ?></strong></td>
                                    <td >₹<?= number_format((float)$item['total_amount']) ?></td>
                                    <td>
                                        <?php
                                        $sc = $statusConfig[$item['status']] ?? ['color' => '#6c757d'];
                                        ?>
                                        <span class="badge">
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
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <small >Page <?= $page ?> of <?= $total_pages ?></small>
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
