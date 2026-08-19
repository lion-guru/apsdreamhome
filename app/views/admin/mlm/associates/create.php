

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users"></i> Create</h2>
                <div>
                    <a href="<?= BASE_URL ?>/admin/mlm" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
            
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Create Management - Complete MLM Associate System with 7 Levels
                    </div>
                    <form method="POST" action="<?= BASE_URL ?>/admin/mlm/users/create">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Associate Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="level_id" class="form-label">Level *</label>
                                    <select class="form-select" id="level_id" name="level_id" required>
                                        <option value="">Select Level</option>
                                        <option value="1">Associate (10%)</option>
                                        <option value="2">Senior Associate (12%)</option>
                                        <option value="3">Team Leader (15%)</option>
                                        <option value="4">Senior Team Leader (18%)</option>
                                        <option value="5">Manager (22%)</option>
                                        <option value="6">Senior Manager (25%)</option>
                                        <option value="7">Director (30%)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sponsor_id" class="form-label">Sponsor</label>
                                    <select class="form-select" id="sponsor_id" name="sponsor_id">
                                        <option value="">No Sponsor</option>
                                        <?php if (!empty($sponsors)): ?>
                                            <?php foreach ($sponsors as $sponsor): ?>
                                                <option value="<?= (int)$sponsor['id'] ?>"><?= htmlspecialchars($sponsor['full_name'] ?? $sponsor['name'] ?? '') ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="joining_date" class="form-label">Joining Date *</label>
                                    <input type="date" class="form-control" id="joining_date" name="joining_date" required>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/admin/mlm/users" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Associate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

