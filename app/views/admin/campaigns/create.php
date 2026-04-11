<?php
$page_title = $page_title ?? 'Create Campaign';
$campaign_types = $campaign_types ?? ['general', 'offer', 'promotion', 'announcement'];
$target_audiences = $target_audiences ?? ['all', 'customers', 'agents', 'employees', 'admin'];
$error = $error ?? null;
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Create Campaign</h1>
            <p class="text-muted mb-0">Set up a new marketing campaign</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/campaigns" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Campaigns
        </a>
    </div>

    <!-- Error Messages -->
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Campaign Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Campaign Details</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/campaigns/store">
                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">
                            <i class="fas fa-tag"></i> Campaign Name *
                        </label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required
                            placeholder="Enter campaign name">
                    </div>
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-bold">
                            <i class="fas fa-list"></i> Campaign Type
                        </label>
                        <select class="form-select" id="type" name="type">
                            <?php foreach ($campaign_types as $type): ?>
                                <option value="<?= $type ?>" <?= (($_POST['type'] ?? '') === $type) ? 'selected' : '' ?>>
                                    <?= ucfirst($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="form-label fw-bold">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea class="form-control" id="description" name="description" rows="4"
                        placeholder="Describe your campaign goals and details"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <!-- Targeting and Schedule -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="target_audience" class="form-label fw-bold">
                            <i class="fas fa-users"></i> Target Audience
                        </label>
                        <select class="form-select" id="target_audience" name="target_audience">
                            <?php foreach ($target_audiences as $audience): ?>
                                <option value="<?= $audience ?>" <?= (($_POST['target_audience'] ?? '') === $audience) ? 'selected' : '' ?>>
                                    <?= ucfirst($audience) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label fw-bold">
                            <i class="fas fa-calendar"></i> Start Date
                        </label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="<?= $_POST['start_date'] ?? date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label fw-bold">
                            <i class="fas fa-calendar-check"></i> End Date
                        </label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="<?= $_POST['end_date'] ?? '' ?>">
                    </div>
                </div>

                <!-- Budget and Revenue -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="budget" class="form-label fw-bold">
                            <i class="fas fa-dollar-sign"></i> Budget ($)
                        </label>
                        <input type="number" class="form-control" id="budget" name="budget"
                            value="<?= htmlspecialchars($_POST['budget'] ?? '0') ?>"
                            step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="col-md-6">
                        <label for="expected_revenue" class="form-label fw-bold">
                            <i class="fas fa-chart-line"></i> Expected Revenue ($)
                        </label>
                        <input type="number" class="form-control" id="expected_revenue" name="expected_revenue"
                            value="<?= htmlspecialchars($_POST['expected_revenue'] ?? '0') ?>"
                            step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>

                <!-- Campaign Settings -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="fas fa-cog"></i> Campaign Settings
                        </h6>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="auto_launch" name="auto_launch"
                                <?= isset($_POST['auto_launch']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="auto_launch">
                                Auto-launch on start date
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="track_conversions" name="track_conversions"
                                <?= isset($_POST['track_conversions']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="track_conversions">
                                Track conversions
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_notifications" name="send_notifications"
                                <?= isset($_POST['send_notifications']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="send_notifications">
                                Send notifications
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>/admin/campaigns" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <div>
                        <button type="button" class="btn btn-outline-primary me-2" onclick="previewCampaign()">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Campaign
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Campaign Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="campaignPreview"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    function previewCampaign() {
        const name = document.getElementById('name').value;
        const description = document.getElementById('description').value;
        const type = document.getElementById('type').value;
        const targetAudience = document.getElementById('target_audience').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const budget = document.getElementById('budget').value;
        const expectedRevenue = document.getElementById('expected_revenue').value;

        const preview = `
        <div class="campaign-preview">
            <h4>${name || 'Untitled Campaign'}</h4>
            <p><strong>Type:</strong> ${type}</p>
            <p><strong>Target Audience:</strong> ${targetAudience}</p>
            <p><strong>Start Date:</strong> ${startDate || 'Not set'}</p>
            <p><strong>End Date:</strong> ${endDate || 'Ongoing'}</p>
            <p><strong>Budget:</strong> $${budget || '0'}</p>
            <p><strong>Expected Revenue:</strong> $${expectedRevenue || '0'}</p>
            <hr>
            <p><strong>Description:</strong></p>
            <p>${description || 'No description provided'}</p>
        </div>
    `;

        document.getElementById('campaignPreview').innerHTML = preview;
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();

        if (!name) {
            e.preventDefault();
            alert('Campaign name is required');
            return false;
        }

        if (name.length < 3) {
            e.preventDefault();
            alert('Campaign name must be at least 3 characters long');
            return false;
        }
    });

    // Date validation
    document.getElementById('end_date').addEventListener('change', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = this.value;

        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            alert('End date must be after start date');
            this.value = '';
        }
    });

    // Budget validation
    document.getElementById('budget').addEventListener('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });

    document.getElementById('expected_revenue').addEventListener('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });
</script>

<style>
    .campaign-preview {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .campaign-preview h4 {
        color: #667eea;
        margin-bottom: 20px;
    }

    .campaign-preview p {
        margin-bottom: 10px;
    }

    .campaign-preview strong {
        color: #333;
    }
</style>