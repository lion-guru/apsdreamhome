<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-tie mr-2"></i>
                Create New Lead
            </h1>
            <a href="/admin/leads" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>View All Leads
            </a>
        </div>
    </div>
</div>

<!-- Lead Creation Form -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-plus mr-2"></i>Enter Lead Information
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/leads" id="leadForm">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card border-left-primary">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-info-circle mr-2"></i>Basic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="name" class="form-label">
                                            Full Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                            class="form-control"
                                            id="name"
                                            name="name"
                                            required
                                            placeholder="Lead Full Name">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">
                                            Email Address
                                        </label>
                                        <input type="email"
                                            class="form-control"
                                            id="email"
                                            name="email"
                                            placeholder="Email Address">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">
                                            Phone Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="tel"
                                            class="form-control"
                                            id="phone"
                                            name="phone"
                                            required
                                            placeholder="Mobile Number"
                                            pattern="[0-9]{10}">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="company" class="form-label">
                                            Company/Organization
                                        </label>
                                        <input type="text"
                                            class="form-control"
                                            id="company"
                                            name="company"
                                            placeholder="Company Name">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lead Details -->
                        <div class="col-md-6">
                            <div class="card border-left-success">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-chart-line mr-2"></i>Lead Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="source" class="form-label">
                                            Lead Source <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="source" name="source" required>
                                            <option value="">Select Source</option>
                                            <?php foreach ($sources as $source): ?>
                                                <option value="<?= $source['id'] ?>">
                                                    <?= htmlspecialchars($source['source_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="status" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="">Select Status</option>
                                            <?php foreach ($statuses as $status): ?>
                                                <option value="<?= $status['id'] ?>">
                                                    <?= htmlspecialchars($status['status_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="priority" class="form-label">
                                            Priority
                                        </label>
                                        <select class="form-control" id="priority" name="priority">
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="assigned_to" class="form-label">
                                            Assign To
                                        </label>
                                        <select class="form-control" id="assigned_to" name="assigned_to">
                                            <option value="">Select User</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['id'] ?>">
                                                    <?= htmlspecialchars($user['name']) ?>
                                                    (<?= $user['lead_count'] ?> Leads)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial & Requirements -->
                            <div class="card border-left-warning mt-3">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-warning">
                                        <i class="fas fa-rupee-sign mr-2"></i>Financial Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="budget" class="form-label">
                                            Budget (₹)
                                        </label>
                                        <input type="number"
                                            class="form-control"
                                            id="budget"
                                            name="budget"
                                            placeholder="Expected Budget"
                                            min="0"
                                            step="10000">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="property_type" class="form-label">
                                            Property Type
                                        </label>
                                        <select class="form-control" id="property_type" name="property_type">
                                            <option value="">Select</option>
                                            <option value="residential">Residential</option>
                                            <option value="commercial">Commercial</option>
                                            <option value="industrial">Industrial</option>
                                            <option value="land">Land</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="location_preference" class="form-label">
                                            Location Preference
                                        </label>
                                        <input type="text"
                                            class="form-control"
                                            id="location_preference"
                                            name="location_preference"
                                            placeholder="Preferred Location">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="notes" class="form-label">
                                            Additional Notes
                                        </label>
                                        <textarea class="form-control"
                                            id="notes"
                                            name="notes"
                                            rows="2"
                                            placeholder="Any Additional Information"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i>Create Lead
                            </button>
                            <a href="/admin/leads" class="btn btn-secondary btn-lg ml-3">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
    }

    .card-header {
        border-radius: 10px 10px 0 0 !important;
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
    }

    .form-control {
        border-radius: 8px;
        border: 2px solid #e3e6f0;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .btn {
        border-radius: 8px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .required {
        color: #dc3545;
    }

    .priority-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>