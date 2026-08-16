<?php $sponsors = $sponsors ?? []; $levels = $levels ?? ['bronze', 'silver', 'gold', 'platinum']; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Add MLM Associate</h4>
    <a href="<?= BASE_URL ?>admin/mlm/users" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="card aps-cp-card">
    <div class="card-body aps-cp-card-body">
        <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sponsor</label>
                    <select name="sponsor_id" class="form-select" id="sponsorSelect">
                        <option value="">No Sponsor (Top Level)</option>
                        <?php foreach ($sponsors as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name'] . ' (' . $s['email'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" class="form-control mt-2" placeholder="Search sponsor..." id="sponsorSearch" onkeyup="filterSponsors()">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Level</label>
                    <select name="level" class="form-select">
                        <?php foreach ($levels as $l): ?>
                            <option value="<?= $l ?>"><?= ucfirst($l) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>

                <div class="col-12"><hr><h6 class="text-muted"><i class="fas fa-user-tag"></i> Agent Track, Brokerage & Telecaller Settings</h6></div>
                <div class="col-md-4">
                    <label class="form-label">Agent Track</label>
                    <select name="agent_track" id="agentTrack" class="form-select" onchange="toggleBrokerageFields()">
                        <option value="mlm">MLM (Network Marketing)</option>
                        <option value="independent">Independent Agent (Flat Commission)</option>
                        <option value="telecaller">Telecaller / Freelancer (Incentives & Overrides)</option>
                    </select>
                    <small class="text-muted">Independent agents get flat deals; Telecallers get salary + flat incentives.</small>
                </div>
                
                <!-- Independent Broker fields -->
                <div class="col-md-4 brokerage-field" class="style-2248">
                    <label class="form-label">Brokerage Model</label>
                    <select name="brokerage_model" id="brokerageModel" class="form-select" onchange="toggleBrokerageRate()">
                        <option value="differential">Differential (MLM Default)</option>
                        <option value="flat_percentage">Flat Percentage (%)</option>
                        <option value="flat_rate_sqft">Flat Rate per SqFt (₹)</option>
                    </select>
                </div>
                <div class="col-md-4 brokerage-field" class="style-2248">
                    <label class="form-label">Brokerage Rate</label>
                    <div class="input-group">
                        <input type="number" name="brokerage_rate" id="brokerageRate" class="form-control" step="0.01" min="0" max="100" value="8.00">
                        <span class="input-group-text" id="brokerageUnit">%</span>
                    </div>
                    <small class="text-muted" id="brokerageHint">Percentage of payment amount (e.g. 8 = 8%)</small>
                </div>

                <!-- Telecaller/Freelancer fields -->
                <div class="col-md-4 telecaller-field" class="style-2248">
                    <label class="form-label">Telecaller Team Lead / Parent</label>
                    <select name="telecaller_parent_id" class="form-select">
                        <option value="">No Team Lead (Top level)</option>
                        <?php if (!empty($telecallers)): foreach ($telecallers as $tc): ?>
                            <option value="<?= $tc['id'] ?>"><?= htmlspecialchars($tc['name'] . ' (' . $tc['email'] . ')') ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="col-md-4 telecaller-field" class="style-2248">
                    <label class="form-label">Monthly Fixed Salary Target (₹)</label>
                    <input type="number" name="telecaller_salary" class="form-control" step="0.01" placeholder="e.g. 6000.00" value="0.00">
                </div>
                <div class="col-md-4 telecaller-field" class="style-2248">
                    <label class="form-label">Flat Lead Conversion Incentive (₹)</label>
                    <input type="number" name="telecaller_incentive_rate" class="form-control" step="0.01" placeholder="e.g. 1000.00" value="0.00">
                </div>
                <div class="col-md-4 telecaller-field" class="style-2248">
                    <label class="form-label">Rate per SqFt (₹)</label>
                    <input type="number" name="telecaller_sqft_rate" class="form-control" step="0.01" placeholder="e.g. 10.00" value="0.00">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Associate</button>
                <a href="<?= BASE_URL ?>admin/mlm/users" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>
function filterSponsors() {
    var input = document.getElementById('sponsorSearch').value.toLowerCase();
    var select = document.getElementById('sponsorSelect');
    for (var i = 0; i < select.options.length; i++) {
        select.options[i].style.display = select.options[i].text.toLowerCase().includes(input) ? '' : 'none';
    }
}
function toggleBrokerageFields() {
    var track = document.getElementById('agentTrack').value;
    var isIndependent = track === 'independent';
    var isTelecaller = track === 'telecaller';
    
    document.querySelectorAll('.brokerage-field').forEach(function(el) {
        el.style.display = isIndependent ? '' : 'none';
    });
    document.querySelectorAll('.telecaller-field').forEach(function(el) {
        el.style.display = isTelecaller ? '' : 'none';
    });
}
function toggleBrokerageRate() {
    var model = document.getElementById('brokerageModel').value;
    var unit = document.getElementById('brokerageUnit');
    var hint = document.getElementById('brokerageHint');
    if (model === 'flat_rate_sqft') {
        unit.textContent = '₹/sqft';
        hint.textContent = 'Fixed rupees per square foot of plot area';
    } else if (model === 'flat_percentage') {
        unit.textContent = '%';
        hint.textContent = 'Percentage of payment amount (e.g. 8 = 8%)';
    } else {
        unit.textContent = '%';
        hint.textContent = 'Differential commission — MLM upline hierarchy';
    }
}
</script>
