<?php $states = $states ?? []; ?>
<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>New Colony</h4>
    <form method="POST" action="<?php echo BASE_URL; ?>/admin/colonies/store" class="row g-4">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h6 class="mb-0">Basic Info</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Colony Name *</label>
                        <input name="name" class="form-control" required onkeyup="document.getElementsByName('slug')[0].value = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'')">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug (URL)</label>
                        <input name="slug" class="form-control" placeholder="Auto-generated">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">State</label>
                        <select name="state_id" class="form-select" onchange="loadDistricts(this.value)">
                            <option value="">Select State</option>
                            <?php foreach ($states as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name'] ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">District</label>
                        <select name="district_id" class="form-select" id="district_select">
                            <option value="">Select State First</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Full description of the colony"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amenities (one per line)</label>
                    <textarea name="amenities" class="form-control" rows="3" placeholder="Park&#10;24hr Water&#10;Security&#10;Community Hall"></textarea>
                </div>
            </div></div>

            <div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><h6 class="mb-0">Content & Media</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-3">
                    <label class="form-label">Key Highlights (JSON array)</label>
                    <textarea name="key_highlights" class="form-control" rows="3" placeholder='["Prime Location","Bank Loans Available","Park Facing","Corner Plots"]'></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nearby Places (JSON array of objects)</label>
                    <textarea name="nearby_places" class="form-control" rows="3" placeholder='[{"name":"Railway Station","distance":"2km"},{"name":"Hospital","distance":"1km"}]'></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Image Path</label>
                        <input name="image_path" class="form-control" placeholder="/assets/images/colonies/xyz.jpg">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Banner Image</label>
                        <input name="banner_image" class="form-control" placeholder="/assets/images/colonies/banner.jpg">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Layout Image</label>
                        <input name="layout_image" class="form-control" placeholder="/assets/images/colonies/layout.jpg">
                        <small class="text-muted">Colony layout/plan image</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Brochure Path (PDF)</label>
                        <input name="brochure_path" class="form-control" placeholder="/assets/brochures/xyz.pdf">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">YouTube Video URL</label>
                        <input name="youtube_video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Virtual Tour URL</label>
                        <input name="virtual_tour_url" class="form-control" placeholder="https://my.matterport.com/show/?m=...">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gallery Images (JSON array of paths)</label>
                    <textarea name="gallery_images" class="form-control" rows="2" placeholder='["/assets/images/colonies/img1.jpg","/assets/images/colonies/img2.jpg"]'></textarea>
                </div>
            </div></div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h6 class="mb-0">Pricing & Plots</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-3">
                    <label class="form-label">Total Plots</label>
                    <input name="total_plots" type="number" class="form-control" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Available Plots</label>
                    <input name="available_plots" type="number" class="form-control" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Starting Price (₹)</label>
                    <input name="starting_price" type="number" step="0.01" class="form-control" value="0">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude</label>
                        <input name="latitude" type="number" step="0.00000001" class="form-control" placeholder="26.7606">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude</label>
                        <input name="longitude" type="number" step="0.00000001" class="form-control" placeholder="83.3732">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Google Maps Link</label>
                    <input name="map_link" class="form-control" id="map_link" placeholder="https://maps.google.com/?q=...">
                    <small class="text-muted">Auto-generated from lat/lng if empty on save</small>
                </div>
            </div></div>

            <div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><h6 class="mb-0">Contact & SEO</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-3">
                    <label class="form-label">Contact Phone</label>
                    <input name="contact_phone" class="form-control" placeholder="+91 92771 21112">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Email</label>
                    <input name="contact_email" type="email" class="form-control" placeholder="info@apsdreamhome.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Title (SEO)</label>
                    <input name="meta_title" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2"></textarea>
                </div>
            </div></div>

            <div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><h6 class="mb-0">Settings</h6></div>
            <div class="card-body aps-cp-card-body">
                <div class="form-check mb-2">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked value="1">
                    <label class="form-check-label" for="is_active">Active (visible on website)</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1">
                    <label class="form-check-label" for="is_featured">Featured (show on homepage)</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="show_plots_publicly" class="form-check-input" id="show_plots_publicly" value="1">
                    <label class="form-check-label" for="show_plots_publicly">Show plot availability publicly</label>
                </div>
            </div></div>

            <button type="submit" class="btn btn-primary w-100 mt-4"><i class="fas fa-save me-1"></i>Create Colony</button>
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
function autoMapLink() {
    const lat = document.querySelector('input[name="latitude"]').value;
    const lng = document.querySelector('input[name="longitude"]').value;
    const mapInput = document.getElementById('map_link');
    if (lat && lng && !mapInput.value) {
        mapInput.value = 'https://maps.google.com/?q=' + lat + ',' + lng;
    }
}
document.querySelector('input[name="latitude"]')?.addEventListener('change', autoMapLink);
document.querySelector('input[name="longitude"]')?.addEventListener('change', autoMapLink);
</script>
