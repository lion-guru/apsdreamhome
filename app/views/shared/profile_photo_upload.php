<?php
/**
 * Reusable Profile Photo Upload Component
 *
 * Usage in any profile view:
 *   $photoUrl = $user['profile_image'] ? BASE_URL . '/' . $user['profile_image'] : null;
 *   include __DIR__ . '/../../shared/profile_photo_upload.php';
 *
 * @var int $userId
 * @var string|null $photoUrl  Full URL to current photo, or null
 * @var string $userName       Used for avatar initials fallback
 * @var string $size           sm|md|lg (default: md)
 */
$size = $size ?? 'md';
$sizeMap = ['sm' => 64, 'md' => 96, 'lg' => 128];
$px = $sizeMap[$size] ?? 96;
$fontSizeMap = ['sm' => 20, 'md' => 32, 'lg' => 44];
$fontPx = $fontSizeMap[$size] ?? 32;
$userId = $userId ?? ($_SESSION['user_id'] ?? 0);
$photoUrl = $photoUrl ?? null;
$userName = $userName ?? ($_SESSION['user_name'] ?? 'U');
$initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $userName), 0, 2)) ?: 'U';
$base = BASE_URL;
?>
<div class="profile-photo-upload" data-user-id="<?= (int)$userId ?>">
    <div class="photo-preview" id="profilePhotoPreview" class="style-60299">
        <?php if ($photoUrl): ?>
            <img src="<?= htmlspecialchars($photoUrl ?? '') ?>" alt="Profile Photo" id="profilePhotoImg">
        <?php else: ?>
            <div class="photo-initials" id="profilePhotoInitials" class="style-38370"><?= htmlspecialchars($initials ?? '') ?></div>
        <?php endif; ?>
    </div>
    <div class="photo-actions">
        <label class="photo-upload-btn" for="profileImageInput">
            <i class="fas fa-camera"></i>
            <span>Change Photo</span>
        </label>
        <input type="file" id="profileImageInput" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
        <?php if ($photoUrl): ?>
            <button type="button" class="photo-delete-btn" id="profilePhotoDeleteBtn">
                <i class="fas fa-trash"></i> Remove
            </button>
        <?php endif; ?>
    </div>
    <div class="photo-hint">JPG, PNG, GIF or WebP. Max 2MB, 1024x1024px</div>
    <div class="photo-progress" id="profilePhotoProgress" class="style-2248">
        <div class="photo-progress-bar"></div>
    </div>
    <div class="photo-message" id="profilePhotoMessage"></div>
</div>

<style>
    .profile-photo-upload { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 16px; }
    .photo-preview { border-radius: 50%; overflow: hidden; border: 3px solid #334155; background: #0f172a; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .photo-preview img { width: 100%; height: 100%; object-fit: cover; }
    .photo-initials { color: #f59e0b; font-weight: 700; user-select: none; }
    .photo-actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
    .photo-upload-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; transition: transform 0.2s; }
    .photo-upload-btn:hover { transform: translateY(-1px); }
    .photo-delete-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: transparent; color: #ef4444; border: 1px solid #ef4444; border-radius: 8px; font-size: 13px; cursor: pointer; transition: all 0.2s; }
    .photo-delete-btn:hover { background: rgba(239,68,68,0.1); }
    .photo-hint { color: #64748b; font-size: 12px; text-align: center; }
    .photo-progress { width: 100%; max-width: 200px; height: 4px; background: #1e293b; border-radius: 2px; overflow: hidden; }
    .photo-progress-bar { height: 100%; width: 0%; background: linear-gradient(90deg, #f59e0b, #d97706); border-radius: 2px; transition: width 0.3s; }
    .photo-message { font-size: 13px; text-align: center; min-height: 20px; }
    .photo-message.success { color: #34d399; }
    .photo-message.error { color: #f87171; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('profileImageInput');
    const preview = document.getElementById('profilePhotoPreview');
    const img = document.getElementById('profilePhotoImg');
    const initials = document.getElementById('profilePhotoInitials');
    const progress = document.getElementById('profilePhotoProgress');
    const progressBar = document.querySelector('.photo-progress-bar');
    const message = document.getElementById('profilePhotoMessage');
    const deleteBtn = document.getElementById('profilePhotoDeleteBtn');
    const userId = document.querySelector('.profile-photo-upload')?.dataset?.userId;

    if (!input || !userId) return;

    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Client-side validation
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showMessage('Invalid file type. Allowed: JPG, PNG, GIF, WebP', 'error');
            input.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showMessage('File too large. Maximum 2MB', 'error');
            input.value = '';
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(ev) {
            if (img) { img.src = ev.target.result; img.style.display = 'block'; }
            if (initials) initials.style.display = 'none';
        };
        reader.readAsDataURL(file);

        // Upload via AJAX
        const formData = new FormData();
        formData.append('profile_image', file);

        progress.style.display = 'block';
        progressBar.style.width = '0%';
        showMessage('Uploading...', '');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= $base ?>/profile/photo/upload', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = function(pe) {
            if (pe.lengthComputable) {
                progressBar.style.width = (pe.loaded / pe.total * 100) + '%';
            }
        };

        xhr.onload = function() {
            progress.style.display = 'none';
            try {
                const resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    showMessage('Photo updated successfully', 'success');
                    if (img) img.src = resp.photo_url + '?t=' + Date.now();
                } else {
                    showMessage(resp.message || 'Upload failed', 'error');
                }
            } catch(e) {
                showMessage('Upload failed', 'error');
            }
        };

        xhr.onerror = function() {
            progress.style.display = 'none';
            showMessage('Network error', 'error');
        };

        xhr.send(formData);
    });

    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!confirm('Remove profile photo?')) return;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= $base ?>/profile/photo/delete', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp.success) {
                        showMessage('Photo removed', 'success');
                        if (img) { img.style.display = 'none'; }
                        if (initials) { initials.style.display = 'block'; }
                        deleteBtn.style.display = 'none';
                    } else {
                        showMessage(resp.message || 'Delete failed', 'error');
                    }
                } catch(e) {
                    showMessage('Delete failed', 'error');
                }
            };
            xhr.send('_method=DELETE');
        });
    }

    function showMessage(msg, type) {
        if (!message) return;
        message.textContent = msg;
        message.className = 'photo-message' + (type ? ' ' + type : '');
        if (type === 'success') {
            setTimeout(() => { message.textContent = ''; message.className = 'photo-message'; }, 3000);
        }
    }
});
</script>
