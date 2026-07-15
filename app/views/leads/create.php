<?php

// TODO: Add proper error handling with try-catch blocks

require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user-tie mr-2"></i>
                    Create New Lead
                </h1>
                <a href="/leads" class="btn btn-secondary">
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
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="/leads/store" id="leadForm">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="card border-left-primary">
                                    <div class="card-header aps-cp-card-header">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-info-circle mr-2"></i>Basic Information
                                        </h6>
                                    </div>
                                    <div class="card-body aps-cp-card-body">
                                        <div class="form-group mb-3">
                                            <label for="name" class="form-label">
                                                Full Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="name"
                                                   name="name"
                                                   required
                                                   placeholder="Lead's full name">
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
                                                   placeholder="Mobile number"
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
                                                   placeholder="Company name">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lead Details -->
                            <div class="col-md-6">
                                <div class="card border-left-success">
                                    <div class="card-header aps-cp-card-header">
                                        <h6 class="m-0 font-weight-bold text-success">
                                            <i class="fas fa-chart-line mr-2"></i>Lead Details
                                        </h6>
                                    </div>
                                    <div class="card-body aps-cp-card-body">
                                        <div class="form-group mb-3">
                                            <label for="source" class="form-label">
                                                Lead Source <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" id="source" name="source" required>
                                                <option value="">Select Source</option>
                                                <?php foreach ($sources as $source): ?>
                                                    <option value="<?= $source['id'] ?>">
                                                        <?= h($source['source_name']) ?>
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
                                                        <?= h($status['status_name']) ?>
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
                                                        <?= h($user['name']) ?>
                                                        (<?= $user['lead_count'] ?> leads)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial & Requirements -->
                                <div class="card border-left-warning mt-3">
                                    <div class="card-header aps-cp-card-header">
                                        <h6 class="m-0 font-weight-bold text-warning">
                                            <i class="fas fa-rupee-sign mr-2"></i>Financial Information
                                        </h6>
                                    </div>
                                    <div class="card-body aps-cp-card-body">
                                        <div class="form-group mb-3">
                                            <label for="budget" class="form-label">
                                                Budget (₹)
                                            </label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="budget"
                                                   name="budget"
                                                   placeholder="Expected budget"
                                                   min="0"
                                                   step="10000">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="requirements" class="form-label">
                                                Requirements
                                            </label>
                                            <textarea class="form-control"
                                                      id="requirements"
                                                      name="requirements"
                                                      rows="3"
                                                      placeholder="Describe lead requirements"></textarea>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="notes" class="form-label">
                                                Additional Notes
                                            </label>
                                            <textarea class="form-control"
                                                      id="notes"
                                                      name="notes"
                                                      rows="2"
                                                      placeholder="Any additional information"></textarea>
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
                                <a href="/leads" class="btn btn-secondary btn-lg ml-3">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e3e6f0;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #0d9488;
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
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>