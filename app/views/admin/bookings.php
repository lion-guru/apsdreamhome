<?php
/**
 * Admin Bookings Management View
 * Handles booking administration interface
 */

if (!isset($title)) {
    $title = 'Booking Management';
}

if (!isset($bookings)) {
    $bookings = [];
}

if (!isset($filters)) {
    $filters = [];
}

if (!isset($bookingStats)) {
    $bookingStats = [];
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-calendar-check me-2"></i>Booking Management</h2>
            <p class="text-muted">Manage property bookings and reservations</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookingModal">
                <i class="fas fa-plus me-2"></i>Create Booking
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1"><?php echo number_format($bookingStats['total_bookings'] ?? 0); ?></h3>
                        <p class="mb-0">Total Bookings</p>
                    </div>
                    <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1"><?php echo number_format($bookingStats['confirmed_bookings'] ?? 0); ?></h3>
                        <p class="mb-0">Confirmed</p>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-warning text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1"><?php echo number_format($bookingStats['pending_bookings'] ?? 0); ?></h3>
                        <p class="mb-0">Pending</p>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1">₹<?php echo number_format($bookingStats['total_revenue'] ?? 0); ?></h3>
                        <p class="mb-0">Revenue</p>
                    </div>
                    <i class="fas fa-rupee-sign fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search"
                           placeholder="Search bookings..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo ($filters['status'] ?? '') === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo ($filters['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="completed" <?php echo ($filters['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="start_date"
                           value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="end_date"
                           value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                    <a href="bookings" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Guests</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bookings)): ?>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($booking['customer_name']); ?>"
                                                 alt="Customer" class="rounded-circle">
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['customer_email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($booking['property_title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['property_location']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $booking['guest_count']; ?> guests</span>
                                </td>
                                <td>₹<?php echo number_format($booking['total_amount']); ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($booking['status']) {
                                        'confirmed' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'cancelled' => 'bg-danger',
                                        'completed' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editBooking(<?php echo $booking['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $booking['id']; ?>, 'confirmed')">Confirm</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $booking['id']; ?>, 'cancelled')">Cancel</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $booking['id']; ?>, 'completed')">Complete</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteBooking(<?php echo $booking['id']; ?>)">Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <h5>No bookings found</h5>
                                    <p class="text-muted">No bookings match your current filters.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (!empty($bookings)): ?>
    <nav aria-label="Booking pagination" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled">
                <span class="page-link">Previous</span>
            </li>
            <li class="page-item active">
                <span class="page-link">1</span>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">2</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">3</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bookingForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <!-- Customer options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Property</label>
                            <select class="form-select" name="property_id" required>
                                <option value="">Select Property</option>
                                <!-- Property options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-in Date</label>
                            <input type="date" class="form-control" name="check_in_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-out Date</label>
                            <input type="date" class="form-control" name="check_out_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Number of Guests</label>
                            <input type="number" class="form-control" name="guest_count" min="1" value="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Amount</label>
                            <input type="number" class="form-control" name="total_amount" step="0.01" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Special Requests</label>
                            <textarea class="form-control" name="special_requests" rows="3" placeholder="Any special requests or notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.stat-card {
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.avatar-sm {
    width: 40px;
    height: 40px;
}

.badge {
    font-size: 0.75rem;
}
</style>

<script>
function viewBooking(id) {
    // Implement view booking functionality
    console.log('View booking:', id);
}

function editBooking(id) {
    // Implement edit booking functionality
    console.log('Edit booking:', id);
}

function updateStatus(id, status) {
    if (confirm('Are you sure you want to update this booking status?')) {
        // Implement status update
        console.log('Update booking status:', id, status);
    }
}

function deleteBooking(id) {
    if (confirm('Are you sure you want to delete this booking?')) {
        // Implement delete functionality
        console.log('Delete booking:', id);
    }
}

// Initialize modal with dynamic data
document.getElementById('bookingModal').addEventListener('show.bs.modal', function() {
    loadCustomers();
    loadProperties();
});

function loadCustomers() {
    // Load customers via AJAX
    fetch('api/customers')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('select[name="customer_id"]');
            select.innerHTML = '<option value="">Select Customer</option>';
            data.forEach(customer => {
                select.innerHTML += `<option value="${customer.id}">${customer.name} - ${customer.email}</option>`;
            });
        });
}

function loadProperties() {
    // Load properties via AJAX
    fetch('api/properties?status=available')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('select[name="property_id"]');
            select.innerHTML = '<option value="">Select Property</option>';
            data.forEach(property => {
                select.innerHTML += `<option value="${property.id}">${property.title} - ₹${property.price}</option>`;
            });
        });
}

// Form submission
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('api/bookings', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error creating booking: ' + data.message);
        }
    });
});
</script>
