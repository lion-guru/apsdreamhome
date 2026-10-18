<?php
$data = $data ?? ['rows' => [], 'buckets' => [], 'total' => 0, 'count' => 0, 'as_of_date' => date('Y-m-d')];
$vendors = $vendors ?? [];
$asOfDate = $asOfDate ?? date('Y-m-d');
$vendorId = (int)($vendorId ?? 0);
$buckets = $data['buckets'] ?? [];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Payables Aging Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin/reports/financial">Financial Reports</a></li>
                        <li class="breadcrumb-item active">Payables Aging</li>
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
                            <label>Vendor</label>
                            <select name="vendor_id" class="form-control">
                                <option value="0">All Vendors</option>
                                <?php foreach ($vendors as $v) : ?>
                                    <option value="<?php echo (int)$v['id']; ?>" <?php if ($vendorId === (int)$v['id']) echo 'selected'; ?>><?php echo htmlspecialchars($v['vendor_name']); ?></option>
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
                <?php foreach ($buckets as $bucket) : ?>
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
                    <h3 class="card-title">Outstanding Payables</h3>
                    <span class="badge bg-danger">Total: <?php echo number_format((float)$data['total'], 2); ?></span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Vendor</th>
                                <th>Bill No</th>
                                <th>Bill Date</th>
                                <th>Age Days</th>
                                <th>Gross</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Bucket</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($data['rows'])) : ?>
                            <tr><td colspan="9" class="text-center text-muted py-3">No outstanding payables found.</td></tr>
                        <?php else : ?>
                            <?php foreach ($data['rows'] as $row) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['vendor_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['bill_number'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['bill_date']); ?></td>
                                    <td><?php echo (int)$row['age_days']; ?></td>
                                    <td><?php echo number_format((float)$row['gross_amount'], 2); ?></td>
                                    <td><?php echo number_format((float)($row['paid_amount'] ?? 0), 2); ?></td>
                                    <td><?php echo number_format((float)$row['balance'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
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
