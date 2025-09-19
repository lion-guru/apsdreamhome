<?php
require_once __DIR__ . '/includes/new_header.php';

// Check if user has permission to view bookings
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Set page title
$page_title = 'Manage Bookings';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Bookings</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Bookings</li>
            </ul>
        </div>
        <div class="col-auto">
            <a href="add_booking.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Booking
            </a>
        </div>
    </div>
</div>
<!-- /Page Header -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Property</th>
                                <th>Booking Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Sample Data - Replace with dynamic data -->
                            <tr>
                                <td>1</td>
                                <td>BK-0001</td>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="customer-profile.php" class="avatar avatar-sm me-2">
                                            <img class="avatar-img rounded-circle" src="../assets/img/profiles/avatar-01.jpg" alt="User Image">
                                        </a>
                                        <a href="customer-profile.php">John Doe</a>
                                    </h2>
                                </td>
                                <td><a href="property-details.php">Luxury Villa</a></td>
                                <td>27 May 2023</td>
                                <td>$250,000</td>
                                <td><span class="badge bg-success">Confirmed</span></td>
                                <td class="text-end">
                                    <div class="actions">
                                        <a class="btn btn-sm bg-primary-light me-2" href="view_booking.php?id=1">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a class="btn btn-sm bg-success-light me-2" href="edit_booking.php?id=1">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a class="btn btn-sm bg-danger-light" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <!-- End Sample Data -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this booking?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>
<!-- /Delete Modal -->

<?php include 'includes/new_footer.php'; ?>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#bookingsTable').DataTable({
        "order": [],
        "columnDefs": [{
            "targets": 'no-sort',
            "orderable": false,
        }]
    });
});
</script>
