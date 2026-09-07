ï»¿<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 ><i class="fas fa-calculator me-2"></i> Commission Recalculations</h2>
            <p >Retroactive recalculation requests — approve/reject historical commission changes</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 ><?= ($stats['pending']['count'] ?? 0) ?></h3>
                    <small >Pending Requests</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 ><?= ($stats['applied']['count'] ?? 0) ?></h3>
                    <small >Applied</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 ><?= ($stats['rejected']['count'] ?? 0) ?></h3>
                    <small >Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 >₹<?= number_format(array_sum(array_column($stats, 'total_diff'))) ?></h3>
                    <small >Total Impact (₹)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Request Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 ><i class="fas fa-layer-group me-2"></i> Bulk Recalculation Request</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/commission/recalculations/bulk-request">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label >Commission Type</label>
                        <select name="commission_type" class="form-control" required>
                            <option value="">Select type...</option>
                            <option value="direct_sale">Direct Sale</option>
                            <option value="override">Override</option>
                            <option value="rank_bonus">Rank Bonus</option>
                            <option value="level_bonus">Level Bonus</option>
                            <option value="matching_bonus">Matching Bonus</option>
                            <option value="generation_bonus">Generation Bonus</option>
                            <option value="royalty_pool">Royalty Pool</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label >Date From</label>
                        <input type="date" name="date_from" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label >Date To</label>
                        <input type="date" name="date_to" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label >Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="Why recalculate?" required>
                    </div>
                    <div class="col-md-2">
                        <label >&nbsp;</label>
                        <button type="submit" class="btn btn-warning w-100" data-aps-confirm="Request bulk recalculation? This will create individual requests for all matching entries.">
                            <i class="fas fa-layer-group me-1"></i> Bulk Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/admin/commission/recalculations" class="btn btn-sm <?= empty($status_filter) ? 'btn-warning' : 'btn-outline-secondary' ?>">All</a>
        <a href="<?= BASE_URL ?>/admin/commission/recalculations?status=pending" class="btn btn-sm <?= $status_filter === 'pending' ? 'btn-warning' : 'btn-outline-secondary' ?>">Pending</a>
        <a href="<?= BASE_URL ?>/admin/commission/recalculations?status=applied" class="btn btn-sm <?= $status_filter === 'applied' ? 'btn-success' : 'btn-outline-secondary' ?>">Applied</a>
        <a href="<?= BASE_URL ?>/admin/commission/recalculations?status=rejected" class="btn btn-sm <?= $status_filter === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' ?>">Rejected</a>
    </div>

    <!-- Requests Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-check-circle fa-3x"></i>
                    <h5 >No recalculation requests found</h5>
                    <p >All commission entries are using current plan rates.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr >
                                <th >#</th>
                                <th >Type</th>
                                <th >Beneficiary</th>
                                <th >Source</th>
                                <th >Original (₹)</th>
                                <th >New (₹)</th>
                                <th >Diff (₹)</th>
                                <th >Status</th>
                                <th >Requested By</th>
                                <th >Date</th>
                                <th >Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr >
                                    <td><?= $item['id'] ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($item['orig_type'] ?? 'N/A') ?></span></td>
                                    <td><?= htmlspecialchars($item['beneficiary_name'] ?? 'User #' . $item['beneficiary_user_id']) ?></td>
                                    <td><?= htmlspecialchars($item['source_name'] ?? 'User #' . $item['source_user_id']) ?></td>
                                    <td>₹<?= number_format((float)($item['original_amount'] ?? 0)) ?></td>
                                    <td>₹<?= number_format((float)($item['new_amount'] ?? 0)) ?></td>
                                    <td >
                                        <?= ((float)($item['amount_diff'] ?? 0)) >= 0 ? '+' : '' ?>₹<?= number_format((float)($item['amount_diff'] ?? 0)) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = ['pending' => 'warning', 'applied' => 'success', 'rejected' => 'danger'];
                                        $statusColor = $statusColors[$item['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($item['status']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($item['requested_by_name'] ?? 'Admin #' . $item['requested_by']) ?></td>
                                    <td><small><?= date('d M Y H:i', strtotime($item['created_at'])) ?></small></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/commission/recalculations/<?= $item['id'] ?>" class="btn btn-sm btn-outline-info">
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
                        <small >Page <?= $page ?> of <?= $total_pages ?> (<?= number_format($total) ?> total)</small>
                        <div>
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="<?= BASE_URL ?>/admin/commission/recalculations?status=<?= $status_filter ?>&page=<?= $i ?>"
                                   class="btn btn-sm <?= $i === $page ? 'btn-warning' : 'btn-outline-secondary' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
