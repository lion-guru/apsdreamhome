<?php
$colony = $colony ?? [];
$states = $states ?? [];
$districts = $districts ?? [];
?>
<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fas fa-edit text-primary me-2"></i>Edit Colony: <?php echo htmlspecialchars($colony['name'] ?? ''); ?></h4>
    <form method="POST" action="<?php echo BASE_URL; ?>/admin/colonies/update/<?php echo $colony['id'] ?? 0; ?>" class="row g-4">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h6 class="mb-0">Basic Info</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Colony Name *</label>
                        <input name="name" class="form-control" value="<?php echo htmlspecialchars($colony['name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug (URL)</label>
                        <input name="slug" class="form-control" value="<?php echo htmlspecialchars($colony['slug'] ?? ''); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">State</label>
                        <select name="state_id" class="form-select" onchange="loadDistricts(this.value)">
                            <option value="">Select State</option>
                            <?php foreach ($states as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($colony['district_id'] ?? 0) == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">District</label>
                        <select name="district_id" class="form-select" id="district_select">
                            <?php foreach ($districts as $d): ?>
                            <option value="<?php echo $d['id']; ?>" <?php echo ($colony['district_id'] ?? 0) == $d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($colony['description'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amenities (one per line)</label>
                    <textarea name="amenities" class="form-control" rows="3"><?php echo htmlspecialchars($colony['amenities'] ?? ''); ?></textarea>
                </div>
            </div></div>

            <div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><h6 class="mb-0">Content & Media</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-3">
                    <label class="form-label">Key Highlights (JSON array)</label>
                    <textarea name="key_highlights" class="form-control" rows="3"><?php echo htmlspecialchars($colony['key_highlights'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nearby Places (JSON)</label>
                    <textarea name="nearby_places" class="form-control" rows="3"><?php echo htmlspecialchars($colony['nearby_places'] ?? ''); ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Image Path</label>
                        <input name="image_path" class="form-control" value="<?php echo htmlspecialchars($colony['image_path'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Banner Image</label>
                        <input name="banner_image" class="form-control" value="<?php echo htmlspecialchars($colony['banner_image'] ?? ''); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Brochure Path</label>
                        <input name="brochure_path" class="form-control" value="<?php echo htmlspecialchars($colony['brochure_path'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">YouTube Video URL</label>
                        <input name="youtube_video_url" class="form-control" value="<?php echo htmlspecialchars($colony['youtube_video_url'] ?? ''); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gallery Images (JSON array)</label>
                    <textarea name="gallery_images" class="form-control" rows="2"><?php echo htmlspecialchars($colony['gallery_images'] ?? ''); ?></textarea>
                </div>
            </div></div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h6 class="mb-0">Pricing & Plots</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-3"><label class="form-label">Total Plots</label><input name="total_plots" type="number" class="form-control" value="<?php echo $colony['total_plots'] ?? 0; ?>"></div>
                <div class="mb-3"><label class="form-label">Available Plots</label><input name="available_plots" type="number" class="form-control" value="<?php echo $colony['available_plots'] ?? 0; ?>"></div>
                <div class="mb-3"><label class="form-label">Starting Price (₹)</label><input name="starting_price" type="number" step="0.01" class="form-control" value="<?php echo $colony['starting_price'] ?? 0; ?>"></div>
                <div class="mb-3"><label class="form-label">Map Link</label><input name="map_link" class="form-control" value="<?php echo htmlspecialchars($colony['map_link'] ?? ''); ?>"></div>
            </div></div>

            <div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><h6 class="mb-0">Contact & SEO</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-3"><label class="form-label">Phone</label><input name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($colony['contact_phone'] ?? ''); ?>"></div>
                <div class="mb-3"><label class="form-label">Email</label><input name="contact_email" class="form-control" value="<?php echo htmlspecialchars($colony['contact_email'] ?? ''); ?>"></div>
                <div class="mb-3"><label class="form-label">Meta Title</label><input name="meta_title" class="form-control" value="<?php echo htmlspecialchars($colony['meta_title'] ?? ''); ?>"></div>
                <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2"><?php echo htmlspecialchars($colony['meta_description'] ?? ''); ?></textarea></div>
            </div></div>

            <div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><h6 class="mb-0">Settings</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="form-check mb-2">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" <?php echo ($colony['is_active'] ?? 0) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo ($colony['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_featured">Featured</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="show_plots_publicly" class="form-check-input" id="show_plots_publicly" value="1" <?php echo ($colony['show_plots_publicly'] ?? 0) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="show_plots_publicly">Show plots publicly</label>
                </div>
            </div></div>

            <button type="submit" class="btn btn-primary w-100 mt-4"><i class="fas fa-save me-1"></i>Save Changes</button>
            <a href="<?php echo BASE_URL; ?>/admin/colonies" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
        </div>
    </form>
</div>

<script>
function loadDistricts(stateId) {
    if (!stateId) return;
    fetch('<?php echo BASE_URL; ?>/api/locations/districts?state_id=' + stateId)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('district_select');
            sel.innerHTML = '<option value="">Select District</option>';
            data.forEach(d => sel.innerHTML += '<option value="' + d.id + '">' + d.name + '</option>');
        });
}
</script>
