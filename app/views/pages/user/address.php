<?php
$layout = 'layouts/customer';
$page_title = $page_title ?? 'My Addresses - APS Dream Home';
$current_page = 'address';

$addresses = $addresses ?? [];
$typeIcons = ['home' => 'fa-home', 'office' => 'fa-briefcase', 'billing' => 'fa-file-invoice', 'shipping' => 'fa-truck', 'other' => 'fa-map-marker-alt'];
$typeColors = ['home' => 'blue', 'office' => 'purple', 'billing' => 'orange', 'shipping' => 'green', 'other' => 'secondary'];

ob_start();
?>
<div class="aps-cp-page-header aps-cp-flex-between">
    <div>
        <h2><i class="fas fa-map-marker-alt"></i> My Addresses</h2>
        <p>Save home, office, and other addresses for quick booking and deliveries.</p>
    </div>
    <button class="aps-cp-btn aps-cp-btn-primary" onclick="openAddressModal()"><i class="fas fa-plus"></i> Add Address</button>
</div>

<?php if (empty($addresses)): ?>
<div class="aps-cp-card">
    <div class="aps-cp-card-body">
        <div class="aps-cp-empty">
            <i class="fas fa-map-marker-alt aps-cp-empty-icon"></i>
            <h3>No addresses saved yet</h3>
            <p>Add your first address to speed up bookings, deliveries, and KYC.</p>
            <button class="aps-cp-btn aps-cp-btn-primary" onclick="openAddressModal()"><i class="fas fa-plus"></i> Add Address</button>
        </div>
    </div>
</div>
<?php else: ?>
<div class="aps-cp-info-grid">
    <?php foreach ($addresses as $a):
        $type = $a['address_type'] ?? 'home';
        $icon = $typeIcons[$type] ?? 'fa-map-marker-alt';
        $color = $typeColors[$type] ?? 'primary';
    ?>
    <div class="aps-cp-info-card" data-address-id="<?= (int)$a['id'] ?>">
        <div class="aps-cp-info-card-head">
            <h4><i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($a['label']) ?>
                <?php if ((int)$a['is_primary'] === 1): ?><span class="aps-cp-badge aps-cp-badge-<?= $color ?>">Primary</span><?php endif; ?>
            </h4>
            <span class="aps-cp-badge aps-cp-badge-<?= $color ?>"><?= htmlspecialchars(ucfirst($type)) ?></span>
        </div>
        <address class="aps-cp-info-card-meta" style="font-style:normal;">
            <?= htmlspecialchars($a['address_line1']) ?>
            <?php if (!empty($a['address_line2'])): ?>, <?= htmlspecialchars($a['address_line2']) ?><?php endif; ?><br>
            <?= htmlspecialchars($a['city']) ?>, <?= htmlspecialchars($a['state']) ?> - <?= htmlspecialchars($a['pincode']) ?><br>
            <?= htmlspecialchars($a['country'] ?? 'India') ?>
            <?php if (!empty($a['phone'])): ?><br>Phone: <?= htmlspecialchars($a['phone']) ?><?php endif; ?>
        </address>
        <div class="aps-cp-info-card-foot">
            <?php if ((int)$a['is_primary'] !== 1): ?>
            <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-secondary" onclick="setPrimaryAddress(<?= (int)$a['id'] ?>)"><i class="fas fa-star"></i> Set Primary</button>
            <?php endif; ?>
            <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-secondary" onclick='openAddressModal(<?= json_encode($a, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i> Edit</button>
            <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-danger" onclick="deleteAddress(<?= (int)$a['id'] ?>)"><i class="fas fa-trash"></i></button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div id="addressModal" class="aps-cp-modal" style="display:none;">
    <div class="aps-cp-modal-overlay" onclick="closeAddressModal()"></div>
    <div class="aps-cp-modal-dialog">
        <div class="aps-cp-modal-head">
            <h3 id="addressModalTitle">Add Address</h3>
            <button class="aps-cp-modal-close" onclick="closeAddressModal()">&times;</button>
        </div>
        <form id="addressForm" class="aps-cp-form" data-ajax="true" action="<?= BASE_URL ?>/user/address/store" method="POST">
            <input type="hidden" name="csrf_token" value="">
            <input type="hidden" name="id" id="addressId" value="">
            <div class="aps-cp-modal-body">
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="label">Label *</label>
                    <input type="text" name="label" id="label" class="aps-cp-input" placeholder="e.g. Home, Office" required>
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="address_type">Type</label>
                    <select name="address_type" id="address_type" class="aps-cp-input">
                        <option value="home">Home</option>
                        <option value="office">Office</option>
                        <option value="billing">Billing</option>
                        <option value="shipping">Shipping</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="address_line1">Address Line 1 *</label>
                    <input type="text" name="address_line1" id="address_line1" class="aps-cp-input" placeholder="House/Flat, Street" required>
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="address_line2">Address Line 2</label>
                    <input type="text" name="address_line2" id="address_line2" class="aps-cp-input" placeholder="Landmark, Area">
                </div>
                <div class="aps-cp-form-row">
                    <div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="pincode">Pincode *</label>
                        <input type="text" name="pincode" id="pincode" class="aps-cp-input" pattern="[0-9]{4,10}" placeholder="273001" required>
                        <small id="pincodeStatus" class="aps-cp-muted"></small>
                    </div>
                    <div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="city">City *</label>
                        <input type="text" name="city" id="city" class="aps-cp-input" required>
                    </div>
                </div>
                <div class="aps-cp-form-row">
                    <div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="state">State *</label>
                        <input type="text" name="state" id="state" class="aps-cp-input" required>
                    </div>
                    <div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="country">Country</label>
                        <input type="text" name="country" id="country" class="aps-cp-input" value="India">
                    </div>
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="phone">Phone</label>
                    <input type="tel" name="phone" id="phone" class="aps-cp-input" pattern="[0-9+\-\s]{7,15}" placeholder="+91 98765 43210">
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-form-check">
                        <input type="checkbox" name="is_primary" value="1" id="is_primary"> <span>Set as primary address</span>
                    </label>
                </div>
            </div>
            <div class="aps-cp-modal-foot">
                <button type="button" class="aps-cp-btn aps-cp-btn-secondary" onclick="closeAddressModal()">Cancel</button>
                <button type="submit" class="aps-cp-btn aps-cp-btn-primary" id="addressSubmitBtn">Save Address</button>
            </div>
        </form>
    </div>
</div>

<script>
let addressFormMode = 'create';

function openAddressModal(addr = null) {
    addressFormMode = addr ? 'edit' : 'create';
    const form = document.getElementById('addressForm');
    const title = document.getElementById('addressModalTitle');
    form.reset();
    document.getElementById('pincodeStatus').textContent = '';
    if (addr) {
        title.textContent = 'Edit Address';
        form.action = '<?= BASE_URL ?>/user/address/update';
        document.getElementById('addressId').value = addr.id;
        document.getElementById('label').value = addr.label || '';
        document.getElementById('address_type').value = addr.address_type || 'home';
        document.getElementById('address_line1').value = addr.address_line1 || '';
        document.getElementById('address_line2').value = addr.address_line2 || '';
        document.getElementById('pincode').value = addr.pincode || '';
        document.getElementById('city').value = addr.city || '';
        document.getElementById('state').value = addr.state || '';
        document.getElementById('country').value = addr.country || 'India';
        document.getElementById('phone').value = addr.phone || '';
        document.getElementById('is_primary').checked = addr.is_primary == 1;
    } else {
        title.textContent = 'Add Address';
        form.action = '<?= BASE_URL ?>/user/address/store';
        document.getElementById('addressId').value = '';
    }
    document.getElementById('addressModal').style.display = 'flex';
}

function closeAddressModal() { document.getElementById('addressModal').style.display = 'none'; }

let pincodeTimer;
document.getElementById('pincode')?.addEventListener('input', function() {
    clearTimeout(pincodeTimer);
    const v = this.value.replace(/\D/g, '');
    const status = document.getElementById('pincodeStatus');
    if (v.length < 4) { status.textContent = ''; return; }
    status.textContent = 'Looking up...';
    pincodeTimer = setTimeout(async () => {
        try {
            const r = await fetch('<?= BASE_URL ?>/api/address/pincode?pincode=' + v);
            const data = await r.json();
            if (data.found && data.data) {
                document.getElementById('city').value = data.data.city || '';
                document.getElementById('state').value = data.data.state || '';
                status.innerHTML = '<i class="fas fa-check-circle aps-cp-text-success"></i> Filled from existing address';
            } else {
                status.textContent = 'No match. Enter manually.';
            }
        } catch (e) { status.textContent = ''; }
    }, 500);
});

document.getElementById('addressForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('addressSubmitBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    try {
        const r = await fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await r.json();
        if (data.success) {
            APS.toast(addressFormMode === 'edit' ? 'Address updated' : 'Address added', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            APS.toast(data.error || 'Save failed', 'error');
            btn.disabled = false; btn.innerHTML = 'Save Address';
        }
    } catch (err) {
        APS.toast('Network error: ' + err.message, 'error');
        btn.disabled = false; btn.innerHTML = 'Save Address';
    }
});

async function deleteAddress(id) {
    if (!confirm('Delete this address?')) return;
    try {
        const fd = new FormData();
        fd.append('csrf_token', (window.CustomerPages && CustomerPages.fetchCsrf) ? CustomerPages.fetchCsrf() : '');
        fd.append('id', id);
        const r = await fetch('<?= BASE_URL ?>/user/address/delete', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await r.json();
        if (data.success) { APS.toast('Address deleted', 'success'); setTimeout(() => location.reload(), 800); }
        else APS.toast(data.error || 'Delete failed', 'error');
    } catch (e) { APS.toast('Network error', 'error'); }
}

async function setPrimaryAddress(id) {
    try {
        const fd = new FormData();
        fd.append('csrf_token', (window.CustomerPages && CustomerPages.fetchCsrf) ? CustomerPages.fetchCsrf() : '');
        fd.append('id', id);
        const r = await fetch('<?= BASE_URL ?>/user/address/primary', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await r.json();
        if (data.success) { APS.toast('Primary address set', 'success'); setTimeout(() => location.reload(), 800); }
        else APS.toast(data.error || 'Failed', 'error');
    } catch (e) { APS.toast('Network error', 'error'); }
}
</script>

<?php
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/customer.php';
