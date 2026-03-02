<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('EMI Plans Management')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('EMI Plans')); ?></li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="/admin/emi/create" class="btn btn-primary">
                    <i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('New EMI Plan')); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <?php if ($flash_success = $this->getFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <?php echo h($mlSupport->translate($flash_success)); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($flash_error = $this->getFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <?php echo h($mlSupport->translate($flash_error)); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover table-center mb-0" id="emiPlansTable">
                            <thead>
                                <tr>
                                    <th><?php echo h($mlSupport->translate('Plan ID')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Customer')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Total Amount')); ?></th>
                                    <th><?php echo h($mlSupport->translate('EMI Amount')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Tenure')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#emiPlansTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo BASE_URL; ?>admin/emi/list",
                "type": "POST"
            },
            "columns": [{
                    "data": "id",
                    "render": function(data, type, row) {
                        return '#EMI-' + data;
                    }
                },
                {
                    "data": "customer_name",
                    "render": function(data, type, row) {
                        return '<div class="fw-bold">' + data + '</div>' +
                            '<small class="text-muted"><?php echo h($mlSupport->translate("Start Date")); ?>: ' +
                            (row.start_date ? new Date(row.start_date).toLocaleDateString('en-GB', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric'
                            }) : '-') +
                            '</small>';
                    }
                },
                {
                    "data": "total_amount",
                    "render": function(data) {
                        return '₹' + parseFloat(data).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                },
                {
                    "data": "emi_amount",
                    "render": function(data) {
                        return '₹' + parseFloat(data).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                },
                {
                    "data": "tenure_months",
                    "render": function(data) {
                        return data + ' <?php echo h($mlSupport->translate("Months")); ?>';
                    }
                },
                {
                    "data": "status",
                    "render": function(data) {
                        var badgeClass = '';
                        switch (data) {
                            case 'active':
                                badgeClass = 'bg-success';
                                break;
                            case 'completed':
                                badgeClass = 'bg-primary';
                                break;
                            case 'foreclosed':
                                badgeClass = 'bg-info';
                                break;
                            case 'defaulted':
                                badgeClass = 'bg-danger';
                                break;
                            default:
                                badgeClass = 'bg-warning';
                        }
                        return '<span class="badge ' + badgeClass + '">' +
                            data.charAt(0).toUpperCase() + data.slice(1) +
                            '</span>';
                    }
                },
                {
                    "data": "id",
                    "orderable": false,
                    "render": function(data) {
                        return '<a href="<?php echo BASE_URL; ?>admin/emi/show/' + data + '" class="btn btn-sm btn-outline-primary">' +
                            '<i class="fa fa-eye"></i> <?php echo h($mlSupport->translate("View")); ?>' +
                            '</a>';
                    }
                }
            ],
            "order": [
                [0, "desc"]
            ],
            "language": {
                "emptyTable": "<?php echo h($mlSupport->translate('No EMI plans found.')); ?>",
                "search": "<?php echo h($mlSupport->translate('Search')); ?>:",
                "paginate": {
                    "first": "<?php echo h($mlSupport->translate('First')); ?>",
                    "last": "<?php echo h($mlSupport->translate('Last')); ?>",
                    "next": "<?php echo h($mlSupport->translate('Next')); ?>",
                    "previous": "<?php echo h($mlSupport->translate('Previous')); ?>"
                }
            }
        });
    });
</script>