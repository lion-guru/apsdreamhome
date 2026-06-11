<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Lead Report</h4>
                <div>
                    <form method="GET" class="d-inline-flex align-items-center gap-2">
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($start_date); ?>">
                        <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($end_date); ?>">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">Total Leads</h6>
                    <h2 class="mb-0"><?php echo count($lead_data ?? []); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">New</h6>
                    <h2 class="mb-0"><?php echo array_reduce($lead_data ?? [], function($c, $l) { return $c + (($l['status'] ?? '') == 'new' ? 1 : 0); }, 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">Converted</h6>
                    <h2 class="mb-0"><?php echo array_reduce($lead_data ?? [], function($c, $l) { return $c + (($l['status'] ?? '') == 'closed_won' ? 1 : 0); }, 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-dark-50">Avg Score</h6>
                    <h2 class="mb-0"><?php
                        $scores = array_filter(array_column($lead_data ?? [], 'lead_score'), fn($v) => $v !== null);
                        echo count($scores) > 0 ? round(array_sum($scores) / count($scores)) : 0;
                    ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Leads — <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h5>
            <a href="<?php echo BASE_URL; ?>/admin/reports/export?type=leads&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="btn btn-sm btn-success">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Activities</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lead_data)): ?>
                            <?php foreach ($lead_data as $lead): ?>
                                <tr>
                                    <td><?php echo $lead['id']; ?></td>
                                    <td><?php echo htmlspecialchars($lead['name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($lead['email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($lead['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($lead['source'] ?? '-')); ?></td>
                                    <td>
                                        <?php
                                            $statusClass = match ($lead['status'] ?? '') {
                                                'new' => 'bg-primary',
                                                'contacted' => 'bg-info',
                                                'qualified' => 'bg-success',
                                                'proposal' => 'bg-warning text-dark',
                                                'negotiation' => 'bg-warning text-dark',
                                                'closed_won' => 'bg-success',
                                                'closed_lost' => 'bg-danger',
                                                'nurture' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $lead['status'] ?? 'new'))); ?></span>
                                    </td>
                                    <td><?php echo $lead['lead_score'] ?? 0; ?></td>
                                    <td><?php echo $lead['activity_count'] ?? 0; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($lead['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No leads found for the selected date range.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
