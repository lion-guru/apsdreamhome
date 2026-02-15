<?php
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}
$page_title = $page_title ?? $mlSupport->translate("Booking Management");
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Bookings')); ?></li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="/admin/bookings/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i><?php echo h($mlSupport->translate('Add Booking')); ?>
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <?php if ($flash_success = get_flash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo h($flash_success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($flash_error = get_flash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo h($flash_error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="/admin/bookings" class="row g-3">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search"
                                        placeholder="<?php echo h($mlSupport->translate('Search by customer, property...')); ?>"
                                        value="<?php echo h($search ?? ''); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="all" <?php echo ($status === 'all') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('All Statuses')); ?></option>
                                    <option value="pending" <?php echo ($status === 'pending') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Pending')); ?></option>
                                    <option value="confirmed" <?php echo ($status === 'confirmed') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Confirmed')); ?></option>
                                    <option value="cancelled" <?php echo ($status === 'cancelled') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Cancelled')); ?></option>
                                    <option value="completed" <?php echo ($status === 'completed') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Completed')); ?></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <a href="/admin/bookings" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-eraser me-2"></i><?php echo h($mlSupport->translate('Clear')); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0" id="bookingsTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo h($mlSupport->translate('Customer')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Property')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Type')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Visit Date')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th class="text-end"><?php echo h($mlSupport->translate('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($bookings)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted"><?php echo h($mlSupport->translate('No Bookings Found')); ?></h5>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo h($booking['id']); ?></td>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="/admin/customers/show/<?php echo h($booking['customer_id']); ?>">
                                                            <?php echo h($booking['customer_name']); ?>
                                                            <span><?php echo h($booking['customer_phone']); ?></span>
                                                        </a>
                                                    </h2>
                                                </td>
                                                <td>
                                                    <a href="/admin/properties/show/<?php echo h($booking['property_id']); ?>">
                                                        <?php echo h($booking['property_title']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php
                                                    $typeClass = [
                                                        'site_visit' => 'badge-info',
                                                        'online_consultation' => 'badge-warning',
                                                        'direct_booking' => 'badge-success'
                                                    ];
                                                    $typeLabel = str_replace('_', ' ', ucfirst($booking['booking_type']));
                                                    ?>
                                                    <span class="badge <?php echo $typeClass[$booking['booking_type']] ?? 'badge-secondary'; ?>">
                                                        <?php echo h($mlSupport->translate($typeLabel)); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($booking['booking_date'])); ?>
                                                    <?php if (isset($booking['visit_time']) && $booking['visit_time']): ?>
                                                        <br><small class="text-muted"><?php echo date('h:i A', strtotime($booking['visit_time'])); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown action-label">
                                                        <a class="btn btn-white btn-sm btn-rounded dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-dot-circle <?php
                                                                                        echo match ($booking['status']) {
                                                                                            'pending' => 'text-warning',
                                                                                            'confirmed' => 'text-success',
                                                                                            'cancelled' => 'text-danger',
                                                                                            'completed' => 'text-info',
                                                                                            default => 'text-secondary'
                                                                                        };
                                                                                        ?> me-1"></i> <?php echo h(ucfirst($booking['status'])); ?>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <form action="/admin/bookings/status/<?php echo h($booking['id']); ?>" method="POST" class="status-form">
                                                                <?php echo csrf_field(); ?>
                                                                <button type="submit" name="status" value="pending" class="dropdown-item"><i class="fas fa-dot-circle text-warning me-1"></i> <?php echo h($mlSupport->translate('Pending')); ?></button>
                                                                <button type="submit" name="status" value="confirmed" class="dropdown-item"><i class="fas fa-dot-circle text-success me-1"></i> <?php echo h($mlSupport->translate('Confirmed')); ?></button>
                                                                <button type="submit" name="status" value="cancelled" class="dropdown-item"><i class="fas fa-dot-circle text-danger me-1"></i> <?php echo h($mlSupport->translate('Cancelled')); ?></button>
                                                                <button type="submit" name="status" value="completed" class="dropdown-item"><i class="fas fa-dot-circle text-info me-1"></i> <?php echo h($mlSupport->translate('Completed')); ?></button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a class="dropdown-item" href="/admin/bookings/edit/<?php echo h($booking['id']); ?>"><i class="fas fa-pencil-alt m-r-5"></i> <?php echo h($mlSupport->translate('Edit')); ?></a>
                                                            <form action="/admin/bookings/delete/<?php echo h($booking['id']); ?>" method="POST" onsubmit="return confirm('<?php echo h($mlSupport->translate('Are you sure you want to delete this booking?')); ?>')">
                                                                <?php echo csrf_field(); ?>
                                                                <button type="submit" class="dropdown-item"><i class="fas fa-trash-alt m-r-5"></i> <?php echo h($mlSupport->translate('Delete')); ?></button>
                                                            </form>
                                                        </div>
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
        </div>
    </div>
</div>

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>