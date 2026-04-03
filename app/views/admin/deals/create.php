<?php
/**
 * Create Deal Form
 */

$page_title = 'Create Deal - APS Dream Home';
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-plus-circle me-2"></i>Create Deal</h1>
            <p class="text-muted">Create a new deal from a qualified lead</p>
        </div>
        <a href="/admin/deals" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Deals
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Deal Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/deals/store">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Lead</label>
                                <select class="form-select" name="lead_id" required>
                                    <option value="">Choose a lead...</option>
                                    <?php foreach ($leads as $lead): ?>
                                    <option value="<?= $lead['id'] ?>" <?= ($selected_lead['id'] ?? '') == $lead['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($lead['name']) ?> - <?= htmlspecialchars($lead['phone']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Initial Stage</label>
                                <select class="form-select" name="stage" required>
                                    <option value="lead">Lead</option>
                                    <option value="qualified" selected>Qualified</option>
                                    <option value="proposal">Proposal</option>
                                    <option value="negotiation">Negotiation</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Deal Value (₹ Lakhs)</label>
                                <input type="number" step="0.01" class="form-control" name="deal_value" required placeholder="e.g. 50.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expected Close Date</label>
                                <input type="date" class="form-control" name="expected_close_date" required min="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Property (Optional)</label>
                                <select class="form-select" name="property_id">
                                    <option value="">Select Property</option>
                                    <?php
                                    // Get properties from database
                                    $sql = "SELECT id, title, location FROM properties WHERE status IN ('available', 'under_construction') ORDER BY title";
                                    $stmt = $this->db->prepare($sql);
                                    $stmt->execute();
                                    $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                                    foreach ($properties as $property):
                                    ?>
                                    <option value="<?= $property['id'] ?>"><?= htmlspecialchars($property['title']) ?> - <?= htmlspecialchars($property['location']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign To</label>
                                <select class="form-select" name="assigned_to">
                                    <option value="">Select Agent</option>
                                    <?php foreach ($agents as $agent): ?>
                                    <option value="<?= $agent['id'] ?>"><?= htmlspecialchars($agent['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Deal details, requirements, special conditions..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Create Deal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Pipeline Stages</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="mb-3">
                            <span class="badge bg-secondary">Lead</span>
                            <p class="small text-muted mb-0">Initial inquiry or contact</p>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-info">Qualified</span>
                            <p class="small text-muted mb-0">Verified interest and budget</p>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-primary">Proposal</span>
                            <p class="small text-muted mb-0">Submitted property proposal</p>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-warning text-dark">Negotiation</span>
                            <p class="small text-muted mb-0">Discussing terms and pricing</p>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-success">Won</span>
                            <p class="small text-muted mb-0">Deal closed successfully</p>
                        </div>
                        <div>
                            <span class="badge bg-danger">Lost</span>
                            <p class="small text-muted mb-0">Deal not closed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Quick Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Select a qualified lead</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Set realistic deal value</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Assign to experienced agent</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Set achievable close date</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
