<?php
// Session started by controller
$page_title = 'Lead Events';
$page_description = 'Monitor and analyze lead interactions and events';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Lead Events</h1>
            <p class="text-muted">Monitor and analyze lead interactions and events</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-bolt fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Events</h6>
                            <h3 class="mb-0"><?php echo $stats['total_events'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-calendar-day fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Today's Events</h6>
                            <h3 class="mb-0"><?php echo $stats['today_events'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Conversion Events</h6>
                            <h3 class="mb-0"><?php echo $stats['conversion_events'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-filter fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Event Types</h6>
                            <h3 class="mb-0"><?= (int)($stats['event_types'] ?? 8) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Filter Events</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>/admin/customer-lead/events">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Lead Name</label>
                        <input type="text" class="form-control" name="lead_search" placeholder="Search by lead name...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Event Type</label>
                        <select class="form-select" name="event_type">
                            <option value="">All Event Types</option>
                            <option value="view">View</option>
                            <option value="click">Click</option>
                            <option value="form_start">Form Start</option>
                            <option value="form_submit">Form Submit</option>
                            <option value="call">Call</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                            <option value="site_visit">Site Visit</option>
                            <option value="booking">Booking</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <input type="date" class="form-control" name="date_from">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i> Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lead Events</h5>
                <a href="<?php echo BASE_URL; ?>/admin/customer-lead/events" class="btn btn-outline-primary btn-sm">Clear Filters</a>
            </div>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Event Type</th>
                            <th>Source</th>
                            <th>Time</th>
                            <th>IP Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $event): ?>
                                <?php
                                // Event type badge colors
                                $eventTypeClass = '';
                                switch ($event['event_type']) {
                                    case 'form_submit':
                                    case 'booking':
                                    case 'site_visit':
                                        $eventTypeClass = 'bg-success';
                                        break;
                                    case 'call':
                                    case 'whatsapp':
                                    case 'email':
                                        $eventTypeClass = 'bg-info';
                                        break;
                                    case 'view':
                                    case 'click':
                                        $eventTypeClass = 'bg-primary';
                                        break;
                                    case 'form_start':
                                        $eventTypeClass = 'bg-warning';
                                        break;
                                    default:
                                        $eventTypeClass = 'bg-secondary';
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img src="<?= BASE_URL ?>/assets/images/user/default-avatar.jpg" alt="Avatar" class="rounded-circle" width="32" height="32" />
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($event['lead_name'] ?? 'Unknown'); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($event['lead_email'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $eventTypeClass; ?>">
                                            <?php echo ucfirst($event['event_type'] ?? 'Unknown'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($event['source_page'] ?? 'Direct'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y H:i', strtotime($event['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($event['ip_address'] ?? 'Unknown'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/customer-lead/events/<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-muted mb-0">No lead events found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>