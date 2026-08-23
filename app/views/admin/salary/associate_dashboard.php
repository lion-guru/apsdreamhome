<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Associate Salary Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/salary">Salary</a></li>
                        <li class="breadcrumb-item active">Associate Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= (int)($total_associates ?? 0) ?></h3>
                            <p>Total Associates</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= (int)($salary_eligible ?? 0) ?></h3>
                            <p>Salary Eligible</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= (int)($target_bonus_eligible ?? 0) ?></h3>
                            <p>Target Bonus Eligible</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-gift"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>&#8377;<?= number_format((float)($total_target_bonus ?? 0), 2) ?></h3>
                            <p>Total Target Bonus</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Associates Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Associate Salary Status</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Rank</th>
                                    <th>Total Sales</th>
                                    <th>Registrations</th>
                                    <th>Required</th>
                                    <th>Status</th>
                                    <th>Salary</th>
                                    <th>Target Bonus</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($associates ?? []) as $assoc): ?>
                                <tr>
                                    <td><?= (int)$assoc['id'] ?></td>
                                    <td><?= htmlspecialchars($assoc['user_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($assoc['level'] ?? 'N/A') ?></td>
                                    <td>&#8377;<?= number_format((float)($assoc['total_sales'] ?? 0), 2) ?></td>
                                    <td><?= (int)($assoc['registration_count'] ?? 0) ?></td>
                                    <td><?= (int)($assoc['required_registrations'] ?? 0) ?></td>
                                    <td>
                                        <?php if (!empty($assoc['registration_complete'])): ?>
                                            <span class="badge badge-success">Complete</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending (<?= (int)($assoc['pending_registrations'] ?? 0) ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($assoc['salary_eligible'])): ?>
                                            <span class="badge badge-success">&#8377;<?= number_format((float)($assoc['salary_amount'] ?? 0), 2) ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Not Eligible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($assoc['target_bonus_eligible'])): ?>
                                            <span class="badge badge-warning">&#8377;<?= number_format((float)($assoc['target_bonus_amount'] ?? 0), 2) ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Not Eligible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-salary" data-id="<?= (int)$assoc['id'] ?>" data-salary="<?= (float)($assoc['salary_amount'] ?? 0) ?>" data-salary-eligible="<?= (int)($assoc['salary_eligible'] ?? 0) ?>" data-target="<?= (float)($assoc['target_bonus_amount'] ?? 0) ?>" data-target-eligible="<?= (int)($assoc['target_bonus_eligible'] ?? 0) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if (!empty($assoc['salary_eligible'])): ?>
                                        <button class="btn btn-sm btn-success process-salary" data-id="<?= (int)$assoc['id'] ?>">
                                            <i class="fas fa-money-bill"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($associates)): ?>
                                <tr><td colspan="10" class="text-center text-muted">No active associates found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Salary Modal -->
<div class="modal fade" id="editSalaryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Associate Salary</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editSalaryForm">
    <?php echo CSRFProtection::csrfField(); ?>
                    <input type="hidden" id="editAssociateId" name="associate_id">
                    <div class="form-group">
                        <label for="editSalaryAmount">Salary Amount (&#8377;)</label>
                        <input type="number" class="form-control" id="editSalaryAmount" name="salary_amount" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="editSalaryEligible">Salary Eligible</label>
                        <select class="form-control" id="editSalaryEligible" name="salary_eligible">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editTargetBonus">Target Bonus Amount (&#8377;)</label>
                        <input type="number" class="form-control" id="editTargetBonus" name="target_bonus_amount" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="editTargetEligible">Target Bonus Eligible</label>
                        <select class="form-control" id="editTargetEligible" name="target_bonus_eligible">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveSalaryBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Process Salary Modal -->
<div class="modal fade" id="processSalaryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Salary Payment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="processSalaryForm">
    <?php echo CSRFProtection::csrfField(); ?>
                    <input type="hidden" id="processAssociateId" name="associate_id">
                    <div class="form-group">
                        <label for="paymentMonth">Payment Month</label>
                        <select class="form-control" id="paymentMonth" name="payment_month">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="paymentYear">Payment Year</label>
                        <input type="number" class="form-control" id="paymentYear" name="payment_year" value="<?= date('Y') ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="processSalaryBtn">Process Payment</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit Salary Modal
    $('.edit-salary').click(function() {
        var id = $(this).data('id');
        var salary = $(this).data('salary');
        var salaryEligible = $(this).data('salary-eligible');
        var target = $(this).data('target');
        var targetEligible = $(this).data('target-eligible');

        $('#editAssociateId').val(id);
        $('#editSalaryAmount').val(salary);
        $('#editSalaryEligible').val(salaryEligible);
        $('#editTargetBonus').val(target);
        $('#editTargetEligible').val(targetEligible);

        $('#editSalaryModal').modal('show');
    });

    $('#saveSalaryBtn').click(function() {
        $.ajax({
            url: '<?= BASE_URL ?>/admin/salary/update-associate-salary',
            type: 'POST',
            data: $('#editSalaryForm').serialize(),
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'info');
                    location.reload();
                } else {
                    showToast(response.message, 'info');
                }
            },
            error: function() {
                showToast('Error updating salary', 'danger');
            }
        });
    });

    // Process Salary Modal
    $('.process-salary').click(function() {
        var id = $(this).data('id');
        $('#processAssociateId').val(id);
        $('#paymentMonth').val(new Date().getMonth() + 1);
        $('#paymentYear').val(new Date().getFullYear());
        $('#processSalaryModal').modal('show');
    });

    $('#processSalaryBtn').click(function() {
        $.ajax({
            url: '<?= BASE_URL ?>/admin/salary/process-associate-salary',
            type: 'POST',
            data: $('#processSalaryForm').serialize(),
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'info');
                    location.reload();
                } else {
                    showToast(response.message, 'info');
                }
            },
            error: function() {
                showToast('Error processing salary', 'danger');
            }
        });
    });
});
</script>
