<?php

/**
 * Schedule Site Visit Form
 */

$page_title = 'Schedule Site Visit - APS Dream Home';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-calendar-plus me-2"></i>Schedule Site Visit</h1>
            <p class="text-muted">Schedule a new property site visit for a lead</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/visits" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Visits
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-car me-2"></i>Visit Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/visits/store">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Lead</label>
                                <select class="form-select" name="lead_id" required>
                                    <option value="">Choose a lead...</option>
                                    <?php foreach ($leads as $lead): ?>
                                        <option value="<?= $lead['id'] ?>">
                                            <?= htmlspecialchars($lead['name'] ?? '') ?> - <?= htmlspecialchars($lead['phone'] ?? '') ?> (<?= ucfirst($lead['status']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Property</label>
                                <select class="form-select" name="property_id" required>
                                    <option value="">Choose a property...</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?>">
                                            <?= htmlspecialchars($property['title'] ?? '') ?> - <?= htmlspecialchars($property['location'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Visit Date</label>
                                <input type="date" class="form-control" name="visit_date" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Visit Time</label>
                                <input type="time" class="form-control" name="visit_time" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Assign Agent</label>
                                <select class="form-select" name="agent_id">
                                    <option value="">Select Agent</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?= $agent['id'] ?>"><?= htmlspecialchars($agent['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Any special instructions or notes for the visit..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Schedule Visit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Select the lead from the dropdown</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Choose the property they want to visit</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Set a convenient date and time</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Assign an agent to handle the visit</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Add any special notes or requirements</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-whatsapp me-2"></i>WhatsApp Integration</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">A WhatsApp confirmation message will be sent to the lead after scheduling.</p>
                    <a href="#" class="btn btn-outline-success btn-sm w-100">
                        <i class="fab fa-whatsapp me-2"></i>Test WhatsApp Connection
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>