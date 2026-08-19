<!-- Payroll Batch History -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-history mr-2"></i> Payroll Batch History</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/salary">Salary</a></li>
                        <li class="breadcrumb-item active">Batch History</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= BASE_URL ?>/admin/salary/batch/preview" class="btn btn-primary"><i class="fas fa-calculator mr-1"></i> New Payroll Batch</a>
                <a href="<?= BASE_URL ?>/admin/salary" class="btn btn-outline-secondary ml-2"><i class="fas fa-arrow-left mr-1"></i> Back to Salary</a>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Entries</th>
                                <th>Total Gross</th>
                                <th>Total Net</th>
                                <th>Status</th>
                                <th>Generated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No payroll history found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($history as $h): ?>
                                    <tr>
                                        <td><strong><?= $h['payment_month'] ?>/<?= $h['payment_year'] ?></strong></td>
                                        <td><span class="badge badge-primary"><?= $h['entries'] ?></span></td>
                                        <td>₹<?= number_format($h['total_gross']) ?></td>
                                        <td>₹<?= number_format($h['total_net']) ?></td>
                                        <td>
                                            <?php
                                            $statusColors = ['pending' => 'warning', 'paid' => 'success', 'processed' => 'info', 'failed' => 'danger', 'cancelled' => 'secondary'];
                                            $color = $statusColors[$h['payment_status']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $color ?>"><?= ucfirst($h['payment_status']) ?></span>
                                        </td>
                                        <td><?= date('d M Y H:i', strtotime($h['generated_at'])) ?></td>
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
