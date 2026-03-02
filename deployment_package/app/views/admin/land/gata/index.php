<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Gata Management</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url('admin/dashboard'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('admin/kisaan/list'); ?>">Land Records</a></li>
                <li class="breadcrumb-item active">Gata Master</li>
            </ul>
        </div>
        <div class="col-auto float-right ml-auto">
            <a href="<?php echo url('admin/gata/add'); ?>" class="btn btn-primary add-btn"><i class="fa fa-plus"></i> Add New Gata</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="card-title mb-0">List of Gata Records</h4>
            </div>
            <div class="card-body">
                <?php echo \App\Helpers\SessionHelper::getFlashMessage(); ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-center mb-0 datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Site Name</th>
                                <th>Gata No</th>
                                <th>Total Area (sqft)</th>
                                <th>Available Area (sqft)</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($gata_records)): ?>
                                <?php $cnt = 1; foreach ($gata_records as $row): ?>
                                    <tr>
                                        <td><?php echo $cnt++; ?></td>
                                        <td><?php echo htmlspecialchars($row['site_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['gata_no']); ?></td>
                                        <td><?php echo htmlspecialchars($row['area']); ?></td>
                                        <td>
                                            <?php 
                                            $avail = floatval($row['available_area']);
                                            $total = floatval($row['area']);
                                            $percent = $total > 0 ? ($avail / $total) * 100 : 0;
                                            $color = $percent < 20 ? 'text-danger' : ($percent < 50 ? 'text-warning' : 'text-success');
                                            ?>
                                            <span class="<?php echo $color; ?> font-weight-bold"><?php echo $avail; ?></span>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?php echo url('admin/gata/edit/' . $row['gata_id']); ?>" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>