<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold"><i class="fas fa-calculator text-primary me-2"></i> Lekha-Jhokha (Expense Tracker)</h2>
            <p class="text-muted">Manage your business expenses, labor payments, and material costs in one place.</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Expense
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold mb-3">Total Expenses (Monthly)</h6>
                    <h2 class="fw-bold mb-0 text-danger">₹<?php echo number_format(45280, 2); ?></h2>
                    <div class="mt-3">
                        <span class="text-success small"><i class="fas fa-arrow-down me-1"></i> 12% lower than last month</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold mb-3">Upcoming Payments</h6>
                    <h2 class="fw-bold mb-0 text-warning">₹<?php echo number_format(12500, 2); ?></h2>
                    <div class="mt-3">
                        <span class="text-muted small">3 payments due this week</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Expenses</h5>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Filter by Category
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Material</a></li>
                    <li><a class="dropdown-item" href="#">Labor</a></li>
                    <li><a class="dropdown-item" href="#">Marketing</a></li>
                    <li><a class="dropdown-item" href="#">Others</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3">10 Jan, 2026</td>
                            <td>Brick Supply (5000 units)</td>
                            <td><span class="badge bg-light text-dark">Material</span></td>
                            <td class="fw-bold text-danger">₹25,000.00</td>
                            <td><span class="badge bg-success">Paid</span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-link text-decoration-none">View Receipt</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3">08 Jan, 2026</td>
                            <td>Weekly Labor Wages</td>
                            <td><span class="badge bg-light text-dark">Labor</span></td>
                            <td class="fw-bold text-danger">₹12,500.00</td>
                            <td><span class="badge bg-warning">Pending</span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-link text-decoration-none">View Details</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
