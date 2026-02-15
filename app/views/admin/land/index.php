<?php
$page_title = "Kissan Land Records";
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Kissan Land Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Land Records</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="/admin/land/create" class="btn btn-primary add-btn"><i class="fa fa-plus"></i> Add New Record</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom-0">
                        <h4 class="card-title mb-0">List of Kissan Land Records</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($flash_success = get_flash('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo h($flash_success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($flash_error = get_flash('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo h($flash_error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-center mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Farmer Details</th>
                                        <th>Site & Gata</th>
                                        <th>Area (sqft)</th>
                                        <th>Pricing (₹)</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $cnt = 1;
                                    foreach ($land_records as $row):
                                        $total_price = number_format((float)($row['total_land_price'] ?? 0), 2);
                                        $paid_amount = number_format((float)($row['total_paid_amount'] ?? 0), 2);
                                        $pending = number_format((float)($row['amount_pending'] ?? 0), 2);
                                    ?>
                                        <tr>
                                            <td><?php echo h($cnt++); ?></td>
                                            <td>
                                                <strong><?php echo h($row['farmer_name'] ?? 'N/A'); ?></strong><br>
                                                <small class='text-muted'><i class='fa fa-phone'></i> <?php echo h($row['farmer_mobile'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td>
                                                <?php echo h($row['site_name'] ?? 'N/A'); ?><br>
                                                <small class='text-muted'>Gata: <?php echo h($row['gata_number'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td><?php echo h($row['land_area'] ?? '0'); ?></td>
                                            <td>
                                                Total: <?php echo h($total_price); ?><br>
                                                <small class='text-success'>Paid: <?php echo h($paid_amount); ?></small><br>
                                                <small class='text-danger'>Pending: <?php echo h($pending); ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = 'warning';
                                                if ($row['agreement_status'] == 'Registered' || $row['agreement_status'] == 'Done') $status_class = 'success';
                                                if ($row['agreement_status'] == 'Cancelled') $status_class = 'danger';
                                                ?>
                                                <span class='badge badge-<?php echo h($status_class); ?>'><?php echo h($row['agreement_status'] ?? 'Pending'); ?></span>
                                            </td>
                                            <td class='text-right'>
                                                <div class='dropdown dropdown-action'>
                                                    <a href='#' class='action-icon dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false'><i class='fa fa-ellipsis-v'></i></a>
                                                    <div class='dropdown-menu dropdown-menu-end'>
                                                        <a class='dropdown-item' href='/admin/land/edit/<?php echo h($row['id']); ?>'><i class='fa fa-pencil m-r-5'></i> Edit</a>
                                                        <?php if (!empty($row['land_paper'])): ?>
                                                            <a class='dropdown-item' href='/<?php echo h($row['land_paper']); ?>' target='_blank'><i class='fa fa-file-pdf-o m-r-5'></i> View Paper</a>
                                                        <?php endif; ?>
                                                        <a class='dropdown-item delete-record' href='#' data-id='<?php echo h($row['id']); ?>' data-name='<?php echo h($row['farmer_name']); ?>' data-bs-toggle='modal' data-bs-target='#delete_modal'><i class='fa fa-trash-o m-r-5'></i> Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="delete_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <form id="delete_form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" id="delete_id">
                    <img src="/assets/img/sent.png" alt="" width="50" height="46">
                    <h3>Are you sure you want to delete record for <span id="delete_farmer_name"></span>?</h3>
                    <div class="m-t-20">
                        <a href="#" class="btn btn-white" data-bs-dismiss="modal">Close</a>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.delete-record').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            $('#delete_id').val(id);
            $('#delete_farmer_name').text(name);
        });

        $('#delete_form').submit(function(e) {
            e.preventDefault();
            var id = $('#delete_id').val();
            var csrf = $('input[name="csrf_token"]').val();

            $.ajax({
                type: "POST",
                url: "/admin/land/delete/" + id,
                data: {
                    csrf_token: csrf
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert(response.message || 'Error deleting record');
                    }
                },
                error: function() {
                    alert('Error connecting to server');
                }
            });
        });
    });
</script>

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>