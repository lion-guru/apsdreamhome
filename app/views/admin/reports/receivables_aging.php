<?php
$data = $data ?? ['rows' => [], 'buckets' => [], 'total' => 0, 'count' => 0, 'as_of_date' => date('Y-m-d')];
$colonies = $colonies ?? [];
$asOfDate = $asOfDate ?? date('Y-m-d');
$colonyId = (int)($colonyId ?? 0);
$buckets = $data['buckets'] ?? [];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Receivables Aging Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin/reports/financial">Financial Reports</a></li>
                        <li class="breadcrumb-item active">Receivables Aging</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="get" class="row">
                        <div class="col-md-3">
                            <label>As Of Date</label>
                            <input type="date" name="as_of_date" class="form-control" value="<?php echo htmlspecialchars($asOfDate); ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Colony</label>
                            <select name="colony_id" class="form-control">
                                <option value="0">All Colonies</option>
                                <?php foreach ($colonies as $c) : ?>
                                    <option value="<?php echo (int)$c['id']; ?>" <?php if ($colonyId === (int)$c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <?php foreach ($buckets as $key => $bucket) : ?>
                    <div class="col-md-3 col-6">
                        <div class="card text-center">
                            <div class="card-body py-2">
                                <div class="text-muted small"><?php echo htmlspecialchars($bucket['label']); ?> Days</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo (int)$bucket['count']; ?></div>
                                <div class="small text-muted"><?php echo number_format((float)$bucket['amount'], 2); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Overdue Receivables</h3>
                    <span class="badge bg-danger">Total: <?php echo number_format((float)$data['total'], 2); ?></span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Colony</th>
                                <th>Installment</th>
                                <th>Due Date</th>
                                <th>Age Days</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Outstanding</th>
                                <th>Bucket</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($data['rows'])) : ?>
                            <tr><td colspan="10" class="text-center text-muted py-3">No overdue receivables found.</td></tr>
                        <?php else : ?>
                            <?php foreach ($data['rows'] as $row) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['booking_number'] ?? ('#' . $row['booking_id'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['colony_name'] ?? ''); ?></td>
                                    <td><?php echo (int)$row['installment_no']; ?></td>
                                    <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                                    <td><?php echo (int)$row['age_days']; ?></td>
                                    <td><?php echo number_format((float)$row['amount'], 2); ?></td>
                                    <td><?php echo number_format((float)($row['paid_amount'] ?? 0), 2); ?></td>
                                    <td><?php echo number_format((float)$row['outstanding'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($buckets[$row['bucket']]['label'] ?? $row['bucket']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
