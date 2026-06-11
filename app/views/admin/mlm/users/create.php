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
</script>
