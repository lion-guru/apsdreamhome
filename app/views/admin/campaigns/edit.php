<?php
/**
 * Edit Campaign View
 */
$campaign = $campaign ?? [];
$campaign_types = $campaign_types ?? ['general', 'offer', 'promotion', 'announcement'];
$target_audiences = $target_audiences ?? ['all', 'customers', 'agents', 'employees', 'admin'];
$page_title = $page_title ?? 'Edit Campaign';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Edit Campaign</h2>
                <p class="text-muted mb-0">Update campaign details</p>
            </div>
            <a href="<?php echo $base; ?>/admin/campaigns" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Campaigns
            </a>
        </div>
        
        <?php if (!empty($campaign)): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form id="editCampaignForm" action="<?php echo $base; ?>/admin/campaigns/<?php echo $campaign['campaign_id'] ?? ''; ?>/update" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Campaign Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($campaign['name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($campaign['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label">Campaign Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <?php foreach ($campaign_types as $type): ?>
                                            <option value="<?php echo $type; ?>" <?php echo ($campaign['type'] ?? '') === $type ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($type); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="target_audience" class="form-label">Target Audience</label>
                                    <select class="form-select" id="target_audience" name="target_audience">
                                        <?php foreach ($target_audiences as $audience): ?>
                                            <option value="<?php echo $audience; ?>" <?php echo ($campaign['target_audience'] ?? '') === $audience ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($audience); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $campaign['start_date'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $campaign['end_date'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="budget" class="form-label">Budget (₹)</label>
                                    <input type="number" class="form-control" id="budget" name="budget" value="<?php echo $campaign['budget'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="expected_revenue" class="form-label">Expected Revenue (₹)</label>
                                    <input type="number" class="form-control" id="expected_revenue" name="expected_revenue" value="<?php echo $campaign['expected_revenue'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="planned" <?php echo ($campaign['status'] ?? '') === 'planned' ? 'selected' : ''; ?>>Planned</option>
                                    <option value="active" <?php echo ($campaign['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="paused" <?php echo ($campaign['status'] ?? '') === 'paused' ? 'selected' : ''; ?>>Paused</option>
                                    <option value="completed" <?php echo ($campaign['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo $base; ?>/admin/campaigns" class="btn btn-outline-secondary">Cancel</a>
                                <div>
                                    <button type="button" onclick="deleteCampaign(<?php echo $campaign['campaign_id'] ?? ''; ?>)" class="btn btn-danger me-2">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Campaign not found.
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteCampaign(campaignId) {
            if (confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
                window.location.href = '<?php echo $base; ?>/admin/campaigns/' + campaignId + '/delete';
            }
        }
        
        document.getElementById('editCampaignForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert('Campaign updated successfully!');
                window.location.href = '<?php echo $base; ?>/admin/campaigns';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating campaign');
            });
        });
    </script>
</body>
</html>
