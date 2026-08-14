@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Associate Salary Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/admin/salary') }}">Salary</a></li>
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
                            <h3>{{ $total_associates }}</h3>
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
                            <h3>{{ $salary_eligible }}</h3>
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
                            <h3>{{ $target_bonus_eligible }}</h3>
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
                            <h3>₹{{ number_format($total_target_bonus, 2) }}</h3>
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
                                @foreach($associates as $assoc)
                                <tr>
                                    <td>{{ $assoc->id }}</td>
                                    <td>{{ $assoc->user_name ?? 'N/A' }}</td>
                                    <td>{{ $assoc->level ?? 'N/A' }}</td>
                                    <td>₹{{ number_format($assoc->total_sales, 2) }}</td>
                                    <td>{{ $assoc->registration_count }}</td>
                                    <td>{{ $assoc->required_registrations }}</td>
                                    <td>
                                        @if($assoc->registration_complete)
                                            <span class="badge badge-success">Complete</span>
                                        @else
                                            <span class="badge badge-warning">Pending ({{ $assoc->pending_registrations }})</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assoc->salary_eligible)
                                            <span class="badge badge-success">₹{{ number_format($assoc->salary_amount, 2) }}</span>
                                        @else
                                            <span class="badge badge-secondary">Not Eligible</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assoc->target_bonus_eligible)
                                            <span class="badge badge-warning">₹{{ number_format($assoc->target_bonus_amount, 2) }}</span>
                                        @else
                                            <span class="badge badge-secondary">Not Eligible</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-salary" data-id="{{ $assoc->id }}" data-salary="{{ $assoc->salary_amount }}" data-salary-eligible="{{ $assoc->salary_eligible }}" data-target="{{ $assoc->target_bonus_amount }}" data-target-eligible="{{ $assoc->target_bonus_eligible }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($assoc->salary_eligible)
                                        <button class="btn btn-sm btn-success process-salary" data-id="{{ $assoc->id }}">
                                            <i class="fas fa-money-bill"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
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
                        <label for="editSalaryAmount">Salary Amount (₹)</label>
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
                        <label for="editTargetBonus">Target Bonus Amount (₹)</label>
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
                        <input type="number" class="form-control" id="paymentYear" name="payment_year" value="{{ date('Y') }}">
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
            url: '{{ url("/admin/salary/update-associate-salary") }}',
            type: 'POST',
            data: $('#editSalaryForm').serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Error updating salary');
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
            url: '{{ url("/admin/salary/process-associate-salary") }}',
            type: 'POST',
            data: $('#processSalaryForm').serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Error processing salary');
            }
        });
    });
});
</script>
@endsection
