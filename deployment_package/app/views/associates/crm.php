<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Customer Relationship Management (CRM)</h1>
        <div>
            <button class="btn btn-primary btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#addLeadModal">
                <i class="fas fa-user-plus fa-sm text-white-50"></i> Add Lead
            </button>
            <button class="btn btn-success btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#addCustomerModal">
                <i class="fas fa-user-check fa-sm text-white-50"></i> Add Customer
            </button>
            <button class="btn btn-info btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#scheduleVisitModal">
                <i class="fas fa-calendar-alt fa-sm text-white-50"></i> Schedule Visit
            </button>
            <button class="btn btn-warning btn-sm shadow-sm" data-toggle="modal" data-target="#markVisitModal">
                <i class="fas fa-map-marker-alt fa-sm text-white-50"></i> Mark Field Visit
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_success'];
            unset($_SESSION['flash_success']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_error'];
            unset($_SESSION['flash_error']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- CRM Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Leads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $leads_stats['total_leads'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Converted</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $leads_stats['closed_leads'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">New Leads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $leads_stats['new_leads'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Conversion Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($conversion['conversion_rate'] ?? 0, 1) ?>%</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-percentage fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Tabs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="crmTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="leads-tab" data-toggle="tab" href="#leads" role="tab" aria-controls="leads" aria-selected="true">Leads</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="customers-tab" data-toggle="tab" href="#customers" role="tab" aria-controls="customers" aria-selected="false">Customers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="visits-tab" data-toggle="tab" href="#visits" role="tab" aria-controls="visits" aria-selected="false">Visits</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="activities-tab" data-toggle="tab" href="#activities" role="tab" aria-controls="activities" aria-selected="false">Activities</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="expenses-tab" data-toggle="tab" href="#expenses" role="tab" aria-controls="expenses" aria-selected="false">Expenses</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="crmTabsContent">

                <!-- Leads Tab -->
                <div class="tab-pane fade show active" id="leads" role="tabpanel" aria-labelledby="leads-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="leadsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Budget</th>
                                    <th>Source</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_leads)): ?>
                                    <?php foreach ($recent_leads as $lead): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']) ?></td>
                                            <td>
                                                <i class="fas fa-phone fa-sm text-gray-400"></i> <?= htmlspecialchars($lead['phone']) ?><br>
                                                <small><?= htmlspecialchars($lead['email']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $lead['status'] == 'new' ? 'primary' : ($lead['status'] == 'closed' ? 'success' : 'secondary') ?>">
                                                    <?= ucfirst($lead['status']) ?>
                                                </span>
                                            </td>
                                            <td>₹<?= number_format($lead['budget'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars($lead['source']) ?></td>
                                            <td><?= date('d M Y', strtotime($lead['created_at'])) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info"
                                                    data-toggle="modal"
                                                    data-target="#editLeadModal"
                                                    data-id="<?= $lead['id'] ?>"
                                                    data-name="<?= htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']) ?>"
                                                    data-email="<?= htmlspecialchars($lead['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($lead['phone']) ?>"
                                                    data-budget="<?= $lead['budget'] ?>"
                                                    data-status="<?= $lead['status'] ?>"
                                                    data-notes="<?= htmlspecialchars($lead['notes']) ?>"
                                                    data-location="<?= htmlspecialchars($lead['location']) ?>"
                                                    data-property_type="<?= htmlspecialchars($lead['property_type']) ?>"
                                                    data-address="<?= htmlspecialchars($lead['address'] ?? '') ?>"
                                                    data-city="<?= htmlspecialchars($lead['city'] ?? '') ?>"
                                                    data-state="<?= htmlspecialchars($lead['state'] ?? '') ?>"
                                                    data-pincode="<?= htmlspecialchars($lead['pincode'] ?? '') ?>"
                                                    data-account_name="<?= htmlspecialchars($lead['account_name'] ?? '') ?>"
                                                    data-account_number="<?= htmlspecialchars($lead['account_number'] ?? '') ?>"
                                                    data-ifsc_code="<?= htmlspecialchars($lead['ifsc_code'] ?? '') ?>"
                                                    data-bank_name="<?= htmlspecialchars($lead['bank_name'] ?? '') ?>"
                                                    data-branch_name="<?= htmlspecialchars($lead['branch_name'] ?? '') ?>"
                                                    title="Edit Lead">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="openScheduleModal('lead', <?= $lead['id'] ?>)"
                                                    title="Schedule Visit">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success"
                                                    onclick="openCheckInModal('lead', <?= $lead['id'] ?>)"
                                                    title="Check In (Location)">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No leads found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Customers Tab -->
                <div class="tab-pane fade" id="customers" role="tabpanel" aria-labelledby="customers-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Bookings</th>
                                    <th>Total Value</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($customer['full_name']) ?></td>
                                            <td><?= htmlspecialchars($customer['phone']) ?></td>
                                            <td><?= $customer['total_bookings'] ?? 0 ?></td>
                                            <td>₹<?= number_format($customer['total_value'] ?? 0) ?></td>
                                            <td><?= date('d M Y', strtotime($customer['created_at'])) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="openScheduleModal('customer', <?= $customer['id'] ?>)"
                                                    title="Schedule Visit">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success"
                                                    onclick="openCheckInModal('customer', <?= $customer['id'] ?>)"
                                                    title="Check In (Location)">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No customers found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Visits Tab -->
                <div class="tab-pane fade" id="visits" role="tabpanel" aria-labelledby="visits-tab">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-uppercase small font-weight-bold">Total Visits</div>
                                    <div class="h3 mb-0 font-weight-bold"><?= $visit_stats['total'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-uppercase small font-weight-bold">Scheduled</div>
                                    <div class="h3 mb-0 font-weight-bold"><?= $visit_stats['scheduled'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-uppercase small font-weight-bold">Completed</div>
                                    <div class="h3 mb-0 font-weight-bold"><?= $visit_stats['completed'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-uppercase small font-weight-bold">Cancelled</div>
                                    <div class="h3 mb-0 font-weight-bold"><?= $visit_stats['cancelled'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Client</th>
                                    <th>Property</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($visits)): ?>
                                    <?php foreach ($visits as $visit): ?>
                                        <tr>
                                            <td>
                                                <?= date('d M Y', strtotime($visit['visit_date'])) ?>
                                                <br>
                                                <small class="text-muted"><?= date('h:i A', strtotime($visit['visit_time'])) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($visit['client_name']) ?></strong>
                                                <br>
                                                <span class="badge badge-info"><?= $visit['client_type'] ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($visit['property_title'] ?? 'N/A') ?></td>
                                            <td><?= ucfirst(str_replace('_', ' ', $visit['visit_type'])) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                switch ($visit['status']) {
                                                    case 'scheduled':
                                                        $statusClass = 'warning';
                                                        break;
                                                    case 'confirmed':
                                                        $statusClass = 'info';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'success';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= ucfirst($visit['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($visit['notes']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No visits scheduled.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Activities Tab -->
                <div class="tab-pane fade" id="activities" role="tabpanel" aria-labelledby="activities-tab">
                    <div class="list-group">
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $activity): ?>
                                <div class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($activity['activity_type']) ?></h6>
                                        <small><?= date('d M Y H:i', strtotime($activity['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($activity['description']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item">No recent activities.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Expenses Tab -->
                <div class="tab-pane fade" id="expenses" role="tabpanel" aria-labelledby="expenses-tab">
                    <!-- Expense Stats -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Expenses</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹<?= number_format($expense_stats['total_amount'] ?? 0, 2) ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-wallet fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $expense_stats['pending_count'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $expense_stats['approved_count'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $expense_stats['rejected_count'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addExpenseModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Add Expense
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Proof</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($expenses)): ?>
                                    <?php foreach ($expenses as $expense): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($expense['expense_date'])) ?></td>
                                            <td><?= htmlspecialchars($expense['category']) ?></td>
                                            <td>₹<?= number_format($expense['amount'], 2) ?></td>
                                            <td><?= htmlspecialchars($expense['description']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $expense['status'] == 'approved' ? 'success' : ($expense['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($expense['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($expense['proof_file'])): ?>
                                                    <a href="/uploads/expenses/<?= htmlspecialchars($expense['proof_file']) ?>" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fas fa-file-alt"></i> View
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No File</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No expenses found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1" role="dialog" aria-labelledby="addLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeadModalLabel">Add New Lead</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/associate/crm/lead/store" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="phone" required pattern="[0-9]{10}">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Budget</label>
                            <input type="number" class="form-control" name="budget" placeholder="e.g. 5000000">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Property Type</label>
                            <select class="form-control" name="property_type">
                                <option value="">Select Type</option>
                                <option value="plot">Plot</option>
                                <option value="flat">Flat</option>
                                <option value="house">House</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Location Preference</label>
                        <input type="text" class="form-control" name="location">
                    </div>
                    <div class="form-group">
                        <label>Address Details (Optional)</label>
                        <textarea class="form-control" name="address" rows="2" placeholder="Full Address"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Pincode</label>
                            <input type="text" class="form-control" name="pincode" id="lead_pincode" placeholder="Enter Pincode" maxlength="6">
                        </div>
                        <div class="form-group col-md-6">
                            <label>City</label>
                            <input type="text" class="form-control" name="city" id="lead_city" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>State</label>
                            <input type="text" class="form-control" name="state" id="lead_state" readonly>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 text-primary">Bank Details (Optional)</h6>
                    <div class="form-group">
                        <label>Account Holder Name</label>
                        <input type="text" class="form-control" name="account_name">
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" class="form-control" name="account_number">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>IFSC Code</label>
                            <input type="text" class="form-control" name="ifsc_code" id="lead_ifsc" placeholder="Enter IFSC" maxlength="11" style="text-transform:uppercase">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" id="lead_bank" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Branch</label>
                        <input type="text" class="form-control" name="branch_name" id="lead_branch" readonly>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Lead Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1" role="dialog" aria-labelledby="editLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLeadModalLabel">Edit Lead</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/associate/crm/lead/update" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="lead_id" id="edit_lead_id">
                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="phone" id="edit_phone" required pattern="[0-9]{10}">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Budget</label>
                            <input type="number" class="form-control" name="budget" id="edit_budget">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select class="form-control" name="status" id="edit_status">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="proposal">Proposal</option>
                                <option value="negotiation">Negotiation</option>
                                <option value="closed">Closed</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Property Type</label>
                        <select class="form-control" name="property_type" id="edit_property_type">
                            <option value="">Select Type</option>
                            <option value="plot">Plot</option>
                            <option value="flat">Flat</option>
                            <option value="house">House</option>
                            <option value="commercial">Commercial</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Location Preference</label>
                        <input type="text" class="form-control" name="location" id="edit_location">
                    </div>
                    <div class="form-group">
                        <label>Address Details (Optional)</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="2" placeholder="Full Address"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Pincode</label>
                            <input type="text" class="form-control" name="pincode" id="edit_pincode" placeholder="Enter Pincode" maxlength="6">
                        </div>
                        <div class="form-group col-md-6">
                            <label>City</label>
                            <input type="text" class="form-control" name="city" id="edit_city" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>State</label>
                            <input type="text" class="form-control" name="state" id="edit_state" readonly>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 text-primary">Bank Details (Optional)</h6>
                    <div class="form-group">
                        <label>Account Holder Name</label>
                        <input type="text" class="form-control" name="account_name" id="edit_account_name">
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" class="form-control" name="account_number" id="edit_account_number">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>IFSC Code</label>
                            <input type="text" class="form-control" name="ifsc_code" id="edit_ifsc_code" placeholder="Enter IFSC" maxlength="11" style="text-transform:uppercase">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" id="edit_bank_name" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Branch</label>
                        <input type="text" class="form-control" name="branch_name" id="edit_branch_name" readonly>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" id="edit_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/associate/crm/customer/store" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="phone" required pattern="[0-9]{10}">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Pincode</label>
                            <input type="text" class="form-control" name="pincode" id="customer_pincode" placeholder="Enter Pincode">
                        </div>
                        <div class="form-group col-md-6">
                            <label>City</label>
                            <input type="text" class="form-control" name="city" id="customer_city" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>State</label>
                            <input type="text" class="form-control" name="state" id="customer_state" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Pan Number</label>
                            <input type="text" class="form-control" name="pan_number" placeholder="Optional">
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 text-primary">Bank Details (Optional)</h6>
                    <div class="form-group">
                        <label>Account Holder Name</label>
                        <input type="text" class="form-control" name="account_name">
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" class="form-control" name="account_number">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>IFSC Code</label>
                            <input type="text" class="form-control" name="ifsc_code" id="customer_ifsc" placeholder="Enter IFSC" maxlength="11" style="text-transform:uppercase">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" id="customer_bank" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Branch</label>
                        <input type="text" class="form-control" name="branch_name" id="customer_branch" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addExpenseModalLabel">Add New Expense</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/associate/expenses/store" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category <span class="text-danger">*</span></label>
                        <select class="form-control" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Travel">Travel</option>
                            <option value="Food">Food</option>
                            <option value="Lodging">Lodging</option>
                            <option value="Office Supplies">Office Supplies</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="amount" step="0.01" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="expense_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Proof (Image/PDF) <span class="text-muted">(Max 5MB)</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="proofFile" name="proof_file" accept=".jpg,.jpeg,.png,.pdf">
                            <label class="custom-file-label" for="proofFile">Choose file</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Visit Modal -->
<div class="modal fade" id="scheduleVisitModal" tabindex="-1" role="dialog" aria-labelledby="scheduleVisitModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleVisitModalLabel">Schedule Client Visit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/associate/crm/visit/schedule" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Client Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="client_type_selector" onchange="toggleClientSelect()">
                            <option value="lead">Lead</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>

                    <div class="form-group" id="lead_select_group">
                        <label>Select Lead <span class="text-danger">*</span></label>
                        <select class="form-control" name="lead_id" id="schedule_lead_id">
                            <option value="">-- Select Lead --</option>
                            <?php if (!empty($all_leads)): ?>
                                <?php foreach ($all_leads as $l): ?>
                                    <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['first_name'] . ' ' . $l['last_name']) ?> (<?= $l['phone'] ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group d-none" id="customer_select_group">
                        <label>Select Customer <span class="text-danger">*</span></label>
                        <select class="form-control" name="customer_id" id="schedule_customer_id">
                            <option value="">-- Select Customer --</option>
                            <?php if (!empty($all_customers)): ?>
                                <?php foreach ($all_customers as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['full_name']) ?> (<?= $c['phone'] ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Select Property <span class="text-danger">*</span></label>
                        <select class="form-control" name="property_id" required>
                            <option value="">-- Select Property --</option>
                            <?php if (!empty($properties)): ?>
                                <?php foreach ($properties as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?> (<?= htmlspecialchars($p['project_name']) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Visit Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="visit_date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Visit Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="visit_time" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Visit Type</label>
                        <select class="form-control" name="visit_type">
                            <option value="site_visit">Site Visit</option>
                            <option value="office_meeting">Office Meeting</option>
                            <option value="virtual_meeting">Virtual Meeting</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Any specific requirements..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Schedule Visit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark Visit Modal -->
<div class="modal fade" id="markVisitModal" tabindex="-1" role="dialog" aria-labelledby="markVisitModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markVisitModalLabel">Mark Field Visit (Employee Check-in)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/associate/crm/visit/store" method="POST" id="visitForm">
                <div class="modal-body">
                    <input type="hidden" name="latitude" id="visit_lat">
                    <input type="hidden" name="longitude" id="visit_lng">
                    <input type="hidden" name="accuracy" id="visit_accuracy">

                    <div class="alert alert-info" id="locationStatus">
                        <i class="fas fa-spinner fa-spin"></i> Fetching your location...
                    </div>

                    <div class="form-group">
                        <label>Visit Purpose / Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="notes" rows="3" required placeholder="Meeting with client, site visit, etc."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Location Name (Optional)</label>
                        <input type="text" class="form-control" name="location_address" placeholder="e.g. Sector 62, Noida">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveVisitBtn" disabled>Save Visit Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Check In Client Modal -->
<div class="modal fade" id="checkInClientModal" tabindex="-1" role="dialog" aria-labelledby="checkInClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkInClientModalLabel">Client Visit Check-In</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/associate/crm/visit/client-location" method="POST" id="clientVisitForm">
                <div class="modal-body">
                    <input type="hidden" name="lead_id" id="checkin_lead_id">
                    <input type="hidden" name="customer_id" id="checkin_customer_id">
                    <input type="hidden" name="latitude" id="client_visit_lat">
                    <input type="hidden" name="longitude" id="client_visit_lng">

                    <div class="alert alert-info" id="clientLocationStatus">
                        <i class="fas fa-spinner fa-spin"></i> Fetching location...
                    </div>

                    <div class="form-group">
                        <label>Visit Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="notes" rows="3" required placeholder="Outcome of the visit..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Location Address (Auto-filled or Manual)</label>
                        <input type="text" class="form-control" name="location_address" id="client_visit_address" placeholder="Location details">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="saveClientVisitBtn" disabled>Confirm Check-In</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/public/assets/js/location-bank-helper.js"></script>
<script>
    function openScheduleModal(type, id) {
        // Reset selections
        $('#client_type_selector').val(type);
        toggleClientSelect();

        if (type === 'lead') {
            $('#schedule_lead_id').val(id);
            $('#schedule_customer_id').val('');
        } else {
            $('#schedule_customer_id').val(id);
            $('#schedule_lead_id').val('');
        }

        $('#scheduleVisitModal').modal('show');
    }

    function openCheckInModal(type, id) {
        // Reset
        $('#checkin_lead_id').val('');
        $('#checkin_customer_id').val('');
        $('#client_visit_lat').val('');
        $('#client_visit_lng').val('');
        $('#client_visit_address').val('');
        $('#clientLocationStatus').removeClass('alert-success alert-danger').addClass('alert-info').html('<i class="fas fa-spinner fa-spin"></i> Fetching location...');
        $('#saveClientVisitBtn').prop('disabled', true);

        if (type === 'lead') {
            $('#checkin_lead_id').val(id);
        } else {
            $('#checkin_customer_id').val(id);
        }

        $('#checkInClientModal').modal('show');
    }

    function toggleClientSelect() {
        var type = $('#client_type_selector').val();
        if (type === 'lead') {
            $('#lead_select_group').removeClass('d-none');
            $('#customer_select_group').addClass('d-none');
        } else {
            $('#lead_select_group').addClass('d-none');
            $('#customer_select_group').removeClass('d-none');
        }
    }

    $(document).ready(function() {
        // Edit Lead Modal population
        $('#editLeadModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var name = button.data('name');
            var email = button.data('email');
            var phone = button.data('phone');
            var budget = button.data('budget');
            var status = button.data('status');
            var notes = button.data('notes');
            var location = button.data('location');
            var property_type = button.data('property_type');
            var address = button.data('address');
            var city = button.data('city');
            var state = button.data('state');
            var pincode = button.data('pincode');
            var account_name = button.data('account_name');
            var account_number = button.data('account_number');
            var ifsc_code = button.data('ifsc_code');
            var bank_name = button.data('bank_name');
            var branch_name = button.data('branch_name');

            var modal = $(this);
            modal.find('.modal-title').text('Edit Lead: ' + name);
            modal.find('#edit_lead_id').val(id);
            modal.find('#edit_name').val(name);
            modal.find('#edit_email').val(email);
            modal.find('#edit_phone').val(phone);
            modal.find('#edit_budget').val(budget);
            modal.find('#edit_status').val(status);
            modal.find('#edit_notes').val(notes);
            modal.find('#edit_location').val(location);
            modal.find('#edit_property_type').val(property_type);
            modal.find('#edit_address').val(address);
            modal.find('#edit_city').val(city);
            modal.find('#edit_state').val(state);
            modal.find('#edit_pincode').val(pincode);
            modal.find('#edit_account_name').val(account_name);
            modal.find('#edit_account_number').val(account_number);
            modal.find('#edit_ifsc_code').val(ifsc_code);
            modal.find('#edit_bank_name').val(bank_name);
            modal.find('#edit_branch_name').val(branch_name);
        });

        // Mark Visit Modal Logic
        $('#markVisitModal').on('show.bs.modal', function(e) {
            const statusDiv = document.getElementById('locationStatus');
            const saveBtn = document.getElementById('saveVisitBtn');

            // Reset state
            statusDiv.className = 'alert alert-info';
            statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching your location...';
            saveBtn.disabled = true;

            LocationBankHelper.getCurrentLocation((pos) => {
                document.getElementById('visit_lat').value = pos.latitude;
                document.getElementById('visit_lng').value = pos.longitude;
                document.getElementById('visit_accuracy').value = pos.accuracy;

                statusDiv.className = 'alert alert-success';
                statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> Location captured successfully!';
                saveBtn.disabled = false;
            }, (err) => {
                statusDiv.className = 'alert alert-danger';
                statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + err;
                saveBtn.disabled = true;
            });
        });

        // Check In Client Modal Logic
        $('#checkInClientModal').on('shown.bs.modal', function() {
            const statusDiv = $('#clientLocationStatus');
            const saveBtn = $('#saveClientVisitBtn');

            LocationBankHelper.getCurrentLocation((pos) => {
                $('#client_visit_lat').val(pos.latitude);
                $('#client_visit_lng').val(pos.longitude);

                statusDiv.removeClass('alert-info alert-danger').addClass('alert-success');
                statusDiv.html('<i class="fas fa-check-circle"></i> Location captured! Accuracy: ' + Math.round(pos.accuracy) + 'm');
                saveBtn.prop('disabled', false);
            }, (err) => {
                statusDiv.removeClass('alert-info alert-success').addClass('alert-danger');
                statusDiv.html('<i class="fas fa-exclamation-triangle"></i> ' + err);
                saveBtn.prop('disabled', true);
            });
        });

        // Pincode lookup for Add Customer
        $('#customer_pincode').on('blur', function() {
            const pincode = $(this).val();
            if (pincode.length === 6) {
                LocationBankHelper.lookupPincode(pincode, (data) => {
                    $('#customer_city').val(data.city);
                    $('#customer_state').val(data.state);
                });
            }
        });

        // IFSC lookup for Add Lead
        $('#lead_ifsc').on('blur', function() {
            const ifsc = $(this).val();
            if (ifsc.length === 11) {
                LocationBankHelper.lookupIFSC(ifsc, (data) => {
                    $('#lead_bank').val(data.bank);
                    $('#lead_branch').val(data.branch);
                }, (err) => {
                    console.error(err);
                    alert('Invalid IFSC Code');
                    $('#lead_bank').val('');
                    $('#lead_branch').val('');
                });
            }
        });

        // IFSC lookup for Add Customer
        $('#customer_ifsc').on('blur', function() {
            const ifsc = $(this).val();
            if (ifsc.length === 11) {
                LocationBankHelper.lookupIFSC(ifsc, (data) => {
                    $('#customer_bank').val(data.bank);
                    $('#customer_branch').val(data.branch);
                }, (err) => {
                    console.error(err);
                    alert('Invalid IFSC Code');
                    $('#customer_bank').val('');
                    $('#customer_branch').val('');
                });
            }
        });

        // Pincode lookup for Add Lead
        $('#lead_pincode').on('blur', function() {
            const pincode = $(this).val();
            if (pincode.length === 6) {
                LocationBankHelper.lookupPincode(pincode, (data) => {
                    $('#lead_city').val(data.city);
                    $('#lead_state').val(data.state);
                });
            }
        });

        // Pincode lookup for Edit Lead
        $('#edit_pincode').on('blur', function() {
            const pincode = $(this).val();
            if (pincode.length === 6) {
                LocationBankHelper.lookupPincode(pincode, (data) => {
                    $('#edit_city').val(data.city);
                    $('#edit_state').val(data.state);
                });
            }
        });

        // IFSC lookup for Edit Lead
        $('#edit_ifsc_code').on('blur', function() {
            const ifsc = $(this).val();
            if (ifsc.length === 11) {
                LocationBankHelper.lookupIFSC(ifsc, (data) => {
                    $('#edit_bank_name').val(data.bank);
                    $('#edit_branch_name').val(data.branch);
                }, (err) => {
                    console.error(err);
                    alert('Invalid IFSC Code');
                    $('#edit_bank_name').val('');
                    $('#edit_branch_name').val('');
                });
            }
        });
    });
</script>

<?php
// Footer is included by the layout
?>