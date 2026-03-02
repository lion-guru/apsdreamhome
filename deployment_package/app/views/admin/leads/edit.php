<!-- Breadcrumb -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/leads">Leads</a></li>
                <li class="breadcrumb-item"><a href="/admin/leads/<?= $lead['id'] ?>">
                        <?= htmlspecialchars($lead['name']) ?>
                    </a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Edit Lead Form -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-edit mr-2"></i>Edit Lead Information
                </h6>
                <div>
                    <a href="/admin/leads/<?= $lead['id'] ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-eye mr-1"></i>View Details
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/leads/<?= $lead['id'] ?>" id="editLeadForm">
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
                                            value="<?= htmlspecialchars($lead['name']) ?>"
                                            required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">
                                            Email Address
                                        </label>
                                        <input type="email"
                                            class="form-control"
                                            id="email"
                                            name="email"
                                            value="<?= htmlspecialchars($lead['email']) ?>">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">
                                            Phone Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="tel"
                                            class="form-control"
                                            id="phone"
                                            name="phone"
                                            value="<?= htmlspecialchars($lead['phone']) ?>"
                                            required
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
                                            value="<?= htmlspecialchars($lead['company'] ?? '') ?>">
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
                                                <option value="<?= $source['id'] ?>"
                                                    <?= ($source['id'] == $lead['source'] || strtolower($source['source_name']) == strtolower($lead['source'])) ? 'selected' : '' ?>>
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
                                                <option value="<?= $status['id'] ?>"
                                                    <?= ($status['id'] == $lead['status'] || strtolower($status['status_name']) == strtolower($lead['status'])) ? 'selected' : '' ?>>
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
                                            <option value="low" <?= $lead['priority'] == 'low' ? 'selected' : '' ?>>Low</option>
                                            <option value="medium" <?= $lead['priority'] == 'medium' ? 'selected' : '' ?>>Medium</option>
                                            <option value="high" <?= $lead['priority'] == 'high' ? 'selected' : '' ?>>High</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="assigned_to" class="form-label">
                                            Assign To
                                        </label>
                                        <select class="form-control" id="assigned_to" name="assigned_to">
                                            <option value="">Select User</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['id'] ?>"
                                                    <?= $user['id'] == $lead['assigned_to'] ? 'selected' : '' ?>>
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
                                            value="<?= htmlspecialchars($lead['budget'] ?? '') ?>"
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
                                            <option value="residential" <?= ($lead['property_type'] ?? '') == 'residential' ? 'selected' : '' ?>>Residential</option>
                                            <option value="commercial" <?= ($lead['property_type'] ?? '') == 'commercial' ? 'selected' : '' ?>>Commercial</option>
                                            <option value="industrial" <?= ($lead['property_type'] ?? '') == 'industrial' ? 'selected' : '' ?>>Industrial</option>
                                            <option value="land" <?= ($lead['property_type'] ?? '') == 'land' ? 'selected' : '' ?>>Land</option>
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
                                            value="<?= htmlspecialchars($lead['location_preference'] ?? '') ?>"
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
                                            placeholder="Any Additional Information"><?= htmlspecialchars($lead['notes'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                            <a href="/admin/leads/<?= $lead['id'] ?>" class="btn btn-secondary btn-lg ml-3">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <button type="button" class="btn btn-danger btn-lg ml-3" onclick="deleteLead()">
                                <i class="fas fa-trash mr-2"></i>Delete Lead
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Lead</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong><?= htmlspecialchars($lead['name']) ?></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    function deleteLead() {
        $('#deleteModal').modal('show');
    }

    function confirmDelete() {
        fetch('/admin/leads/delete/<?= $lead['id'] ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/admin/leads';
                } else {
                    alert('Failed to delete lead: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete lead. Please try again.');
            });
    }
</script>

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

    .btn-danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        border: none;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #ee5a24 0%, #ff6b6b 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .priority-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>