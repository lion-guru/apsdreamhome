<?php
/**
 * Customer Raise Township Maintenance Grievance View
 */
$plots = $plots ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-3">
                <a href="/customer/township-grievances" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                <h4 class="fw-bold mb-0">Raise Township Infrastructure Maintenance Request</h4>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form method="POST" action="/customer/township-grievances/create">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="mb-3">
                            <label for="plot_select" class="form-label fw-semibold">Your Township Property / Plot <span class="text-danger">*</span></label>
                            <select name="plot_option" id="plot_select" class="form-select" required>
                                <option value="">Select your township colony &amp; plot...</option>
                                <?php foreach ($plots as $p): ?>
                                    <option value="<?= (int)$p['plot_id'] ?>" data-colony="<?= (int)$p['colony_id'] ?>">
                                        Plot #<?= htmlspecialchars($p['plot_number']) ?> &bull; <?= htmlspecialchars($p['colony_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="colony_id" id="colony_id" value="">
                            <input type="hidden" name="plot_id" id="plot_id" value="">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="category" class="form-label fw-semibold">Infrastructure Category <span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-select" required>
                                    <option value="water_supply">Water Supply, Borewell &amp; Pipeline</option>
                                    <option value="drainage_sanitation">Drainage Blockage &amp; STP</option>
                                    <option value="street_lighting">Street Lighting &amp; Electrification</option>
                                    <option value="road_civil_work">Road Tarring &amp; Paver Blocks</option>
                                    <option value="park_horticulture">Parks &amp; Tree Plantation</option>
                                    <option value="security_gate">Security Gate &amp; Access Control</option>
                                    <option value="other">Other Township Civil Issue</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label fw-semibold">Urgency Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select" required>
                                    <option value="medium">Medium (48-hour resolution SLA)</option>
                                    <option value="high">High (24-hour resolution SLA)</option>
                                    <option value="emergency">Emergency / Critical (4-hour resolution SLA)</option>
                                    <option value="low">Low (72-hour routine)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Issue Summary / Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Street lamp outside Plot 42 not glowing" required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Detailed Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Please provide specific location landmarks and nature of the defect..." required></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/customer/township-grievances" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-paper-plane me-1"></i>Submit Maintenance Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('plot_select').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    document.getElementById('plot_id').value = opt.value || '';
    document.getElementById('colony_id').value = opt.getAttribute('data-colony') || '';
});
</script>
