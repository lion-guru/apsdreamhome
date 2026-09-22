<?php $layout = "admin/layouts/admin"; $active_page = "index"; ?>
<?php $csrf = $_SESSION['csrf_token'] ?? ''; ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Admin Notes: <?= htmlspecialchars($user['name'] ?? 'User') ?></h1>
        <p class="text-muted mb-0">
            <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Back to User
            </a> &middot;
            <strong><?= htmlspecialchars($user['email'] ?? '') ?></strong> (<?= ucfirst($user['role'] ?? 'user') ?>)
        </p>
    </div>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
            <i class="fas fa-plus me-2"></i>Add Note
        </button>
    </div>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo e($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Notes List -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (!empty($notes)): ?>
        <div class="list-group list-group-flush">
            <?php foreach ($notes as $note): ?>
            <div class="list-group-item border-0 px-4 py-3 note-item" data-note-id="<?= $note['id'] ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <?php if (!empty($note['is_important'])): ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Important</span>
                            <?php endif; ?>
                            <?php if (!empty($note['tags'])): ?>
                            <?php $tags = json_decode($note['tags'], true); ?>
                            <?php if (is_array($tags)): ?>
                                <?php foreach ($tags as $tag): ?>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="note-content"><?= nl2br(htmlspecialchars($note['note'])) ?></div>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($note['admin_name'] ?? 'Admin') ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-clock me-1"></i><?= date('M d, Y H:i', strtotime($note['created_at'])) ?>
                            <?php if ($note['created_at'] !== $note['updated_at']): ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-edit me-1"></i>Updated <?= date('M d, Y H:i', strtotime($note['updated_at'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ms-3">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary edit-note" title="Edit" data-note='<?= json_encode($note, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger delete-note" title="Delete" data-note-id="<?= $note['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-sticky-note fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">No notes yet</h5>
            <p class="text-muted mb-3">Add your first admin note for this user.</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="fas fa-plus me-1"></i>Add Note
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Admin Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea name="note" class="form-control" rows="4" placeholder="Enter your note..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tags (comma-separated)</label>
                        <input type="text" name="tags" class="form-control" placeholder="e.g. followup, complaint, vip, callback" data-role="tagsinput">
                        <div class="form-text">Press Enter after each tag</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_important" class="form-check-input" id="isImportant">
                        <label class="form-check-label" for="isImportant"><i class="fas fa-star text-warning me-1"></i>Mark as Important</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editNoteForm">
                <div class="modal-body">
                    <input type="hidden" name="note_id" id="editNoteId">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea name="note" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tags (comma-separated)</label>
                        <input type="text" name="tags" class="form-control" placeholder="e.g. followup, complaint, vip, callback" data-role="tagsinput">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_important" class="form-check-input" id="editIsImportant">
                        <label class="form-check-label" for="editIsImportant"><i class="fas fa-star text-warning me-1"></i>Mark as Important</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF = '<?= $csrf ?>';
const USER_ID = <?= $user['id'] ?>;

// Add Note
document.getElementById('addNoteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    
    fetch(BASE_URL + '/admin/users/' + USER_ID + '/notes', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: formData
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('Note added', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
            this.reset();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(d.message || 'Failed', 'danger');
        }
    }).catch(() => showToast('Network error', 'danger')).finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Note';
    });
});

// Edit Note
document.querySelectorAll('.edit-note').forEach(btn => {
    btn.addEventListener('click', function() {
        const note = JSON.parse(this.dataset.note);
        document.getElementById('editNoteId').value = note.id;
        document.getElementById('editNoteForm').querySelector('textarea[name="note"]').value = note.note;
        document.getElementById('editNoteForm').querySelector('input[name="tags"]').value = note.tags ? JSON.parse(note.tags).join(', ') : '';
        document.getElementById('editIsImportant').checked = note.is_important == 1;
        
        new bootstrap.Modal(document.getElementById('editNoteModal')).show();
    });
});

document.getElementById('editNoteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const noteId = document.getElementById('editNoteId').value;
    const btn = this.querySelector('button[type="submit"]');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
    
    fetch(BASE_URL + '/admin/users/notes/' + noteId + '/update', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: formData
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('Note updated', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editNoteModal')).hide();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(d.message || 'Failed', 'danger');
        }
    }).catch(() => showToast('Network error', 'danger')).finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Update Note';
    });
});

// Delete Note
document.querySelectorAll('.delete-note').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Delete this note?')) return;
        
        const noteId = this.dataset.noteId;
        const item = this.closest('.note-item');
        
        showLoader();
        fetch(BASE_URL + '/admin/users/notes/' + noteId + '/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: 'csrf_token=' + encodeURIComponent(CSRF)
        }).then(r => r.json()).then(d => {
            if (d.success) {
                showToast('Note deleted', 'success');
                item.remove();
            } else {
                showToast(d.message || 'Failed', 'danger');
            }
        }).catch(() => showToast('Network error', 'danger')).finally(() => hideLoader());
    });
});
</script>