<?php include __DIR__ . '/../../../layouts/admin_header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit"></i> Edit State</h2>
                <a href="/admin/locations/states" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="/admin/locations/states/edit/<?php echo $state['id']; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">State Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($state['name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">State Code *</label>
                                    <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($state['code']); ?>" maxlength="10" required style="text-transform: uppercase;">
                                    <small class="form-text text-muted">e.g., UP, MP, RJ</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $state['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/admin/locations/states" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update State
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php include __DIR__ . '/../../../layouts/admin_footer.php'; ?>
