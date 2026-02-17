<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Kissan Land Management')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Land Records')); ?></li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="/admin/land/create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> <?php echo h($mlSupport->translate('Add New Record')); ?>
                </a>
            </div>
        </div>
    </div>

    <?php if ($flash_success = get_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?php echo h($mlSupport->translate($flash_success)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash_error = get_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <?php echo h($mlSupport->translate($flash_error)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><?php echo h($mlSupport->translate('List of Kissan Land Records')); ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th><?php echo h($mlSupport->translate('Farmer Details')); ?></th>
                            <th><?php echo h($mlSupport->translate('Site & Gata')); ?></th>
                            <th><?php echo h($mlSupport->translate('Area (sqft)')); ?></th>
                            <th><?php echo h($mlSupport->translate('Pricing')); ?> (<?php echo h($currency_symbol ?? '₹'); ?>)</th>
                            <th><?php echo h($mlSupport->translate('Status')); ?></th>
                            <th class="text-end pe-4"><?php echo h($mlSupport->translate('Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($land_records)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted"><?php echo h($mlSupport->translate('No land records found')); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $cnt = 1;
                            foreach ($land_records as $row):
                                $total_price = number_format((float)($row['total_land_price'] ?? 0), 2);
                                $paid_amount = number_format((float)($row['total_paid_amount'] ?? 0), 2);
                                $pending = number_format((float)($row['amount_pending'] ?? 0), 2);
                            ?>
                                <tr>
                                    <td class="ps-4"><?php echo h($cnt++); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo h($row['farmer_name'] ?? 'N/A'); ?></div>
                                        <div class="small text-muted"><i class="fas fa-phone me-1"></i> <?php echo h($row['farmer_mobile'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo h($row['site_name'] ?? 'N/A'); ?></div>
                                        <div class="small text-muted"><?php echo h($mlSupport->translate('Gata')); ?>: <?php echo h($row['gata_number'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td><?php echo h($row['land_area'] ?? '0'); ?></td>
                                    <td>
                                        <div><?php echo h($mlSupport->translate('Total')); ?>: <?php echo h($total_price); ?></div>
                                        <div class="small text-success"><?php echo h($mlSupport->translate('Paid')); ?>: <?php echo h($paid_amount); ?></div>
                                        <div class="small text-danger"><?php echo h($mlSupport->translate('Pending')); ?>: <?php echo h($pending); ?></div>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = 'warning';
                                        if ($row['agreement_status'] == 'Registered' || $row['agreement_status'] == 'Done') $status_class = 'success';
                                        if ($row['agreement_status'] == 'Cancelled') $status_class = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo h($status_class); ?>-subtle text-<?php echo h($status_class); ?> rounded-pill px-3"><?php echo h($row['agreement_status'] ?? 'Pending'); ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li><a class="dropdown-item" href="/admin/land/edit/<?php echo h($row['id']); ?>"><i class="fas fa-pencil-alt me-2 text-muted"></i> <?php echo h($mlSupport->translate('Edit')); ?></a></li>
                                                <?php if (!empty($row['land_paper'])): ?>
                                                    <li><a class="dropdown-item" href="/<?php echo h($row['land_paper']); ?>" target="_blank"><i class="fas fa-file-pdf me-2 text-muted"></i> <?php echo h($mlSupport->translate('View Paper')); ?></a></li>
                                                <?php endif; ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger delete-record" href="#" data-id="<?php echo h($row['id']); ?>" data-name="<?php echo h($row['farmer_name']); ?>" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="fas fa-trash-alt me-2"></i> <?php echo h($mlSupport->translate('Delete')); ?></a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="delete_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="mb-3 text-danger">
                    <i class="fas fa-exclamation-circle fa-3x"></i>
                </div>
                <h4 class="mb-3"><?php echo h($mlSupport->translate('Are you sure?')); ?></h4>
                <p class="mb-4"><?php echo h($mlSupport->translate('Do you really want to delete the record for')); ?> <span id="delete_farmer_name" class="fw-bold"></span>? <?php echo h($mlSupport->translate('This process cannot be undone.')); ?></p>
                <form id="delete_form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" id="delete_id">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo h($mlSupport->translate('Cancel')); ?></button>
                        <button type="submit" class="btn btn-danger"><?php echo h($mlSupport->translate('Delete')); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var deleteModal = document.getElementById('delete_modal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_farmer_name').textContent = name;
        });

        document.getElementById('delete_form').addEventListener('submit', function(e) {
            e.preventDefault();
            var id = document.getElementById('delete_id').value;
            var csrf = document.querySelector('input[name="csrf_token"]').value;

            fetch('/admin/land/delete/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'csrf_token=' + encodeURIComponent(csrf)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting record');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error connecting to server');
            });
        });
    });
</script>
