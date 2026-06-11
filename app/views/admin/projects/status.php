

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-sync"></i> Update Project Status</h2>
                <div>
                    <a href="<?= BASE_URL ?>/admin/projects" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Projects
                    </a>
                </div>
            </div>

            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Changing project status will be logged in the status history.
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/admin/projects/status/<?php echo htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label for="status" class="form-label">Project Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="planning">Planning</option>
                                <option value="under_construction">Under Construction</option>
                                <option value="completed">Completed</option>
                                <option value="delayed">Delayed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/admin/projects" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

