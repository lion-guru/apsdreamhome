<?php
$layout = 'layouts/customer';
$page_title = $page_title ?? __('user_address_page_title', null, 'My Addresses - APS Dream Home');
$current_page = 'address';

$addresses = $addresses ?? [];
$typeIcons = ['home' => 'fa-home', 'office' => 'fa-briefcase', 'billing' => 'fa-file-invoice', 'shipping' => 'fa-truck', 'other' => 'fa-map-marker-alt'];
$typeColors = ['home' => 'blue', 'office' => 'purple', 'billing' => 'orange', 'shipping' => 'green', 'other' => 'secondary'];

ob_start();
?>
<div class="aps-cp-page-header aps-cp-flex-between">
    <div>
        <h2><i class="fas fa-map-marker-alt"></i> <?= __('user_address_heading', null, 'My Addresses') ?></h2>
        <p><?= __('user_address_subtitle', null, 'Save home, office, and other addresses for quick booking and deliveries.') ?></p>
    </div>
    <button class="aps-cp-btn aps-cp-btn-primary" onclick="openAddressModal()"><i class="fas fa-plus"></i> <?= __('user_address_add_btn', null, 'Add Address') ?></button>
</div>

<?php if (empty($addresses)): ?>
<div class="aps-cp-card">
    <div class="aps-cp-card-body">
        <div class="aps-cp-empty">
            <i class="fas fa-map-marker-alt aps-cp-empty-icon"></i>
            <h3><?= __('user_address_empty_heading', null, 'No addresses saved yet') ?></h3>
            <p><?= __('user_address_empty_desc', null, 'Add your first address to speed up bookings, deliveries, and KYC.') ?></p>
            <button class="aps-cp-btn aps-cp-btn-primary" onclick="openAddressModal()"><i class="fas fa-plus"></i> <?= __('user_address_add_btn', null, 'Add Address') ?></button>
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
                <?php if ((int)$a['is_primary'] === 1): ?><span class="aps-cp-badge aps-cp-badge-<?= $color ?>"><?= __('user_address_primary_badge', null, 'Primary') ?></span><?php endif; ?>
            </h4>
            <span class="aps-cp-badge aps-cp-badge-<?= $color ?>"><?= htmlspecialchars(ucfirst($type)) ?></span>
        </div>
        <address class="aps-cp-info-card-meta" class="style-81916">
            <?= htmlspecialchars($a['address_line1']) ?>
            <?php if (!empty($a['address_line2'])): ?>, <?= htmlspecialchars($a['address_line2']) ?><?php endif; ?><br>
            <?= htmlspecialchars($a['city']) ?>, <?= htmlspecialchars($a['state']) ?> - <?= htmlspecialchars($a['pincode']) ?><br>
            <?= htmlspecialchars($a['country'] ?? 'India') ?>
            <?php if (!empty($a['phone'])): ?><br><?= __('user_address_phone_label', null, 'Phone:') ?> <?= htmlspecialchars($a['phone']) ?><?php endif; ?>
        </address>
        <div class="aps-cp-info-card-foot">
            <?php if ((int)$a['is_primary'] !== 1): ?>
            <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-secondary" onclick="setPrimaryAddress(<?= (int)$a['id'] ?>)"><i class="fas fa-star"></i> <?= __('user_address_set_primary', null, 'Set Primary') ?></button>
            <?php endif; ?>
            <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-secondary" onclick='openAddressModal(<?= json_encode($a, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i> <?= __('user_address_edit_btn', null, 'Edit') ?></button>
            <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-danger" onclick="deleteAddress(<?= (int)$a['id'] ?>)"><i class="fas fa-trash"></i></button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div id="addressModal" class="aps-cp-modal" class="style-2248">
    <div class="aps-cp-modal-overlay" onclick="closeAddressModal()"></div>
    <div class="aps-cp-modal-dialog">
        <div class="aps-cp-modal-head">
            <h3 id="addressModalTitle"><?= __('user_address_modal_add', null, 'Add Address') ?></h3>
            <button class="aps-cp-modal-close" onclick="closeAddressModal()">&times;</button>
        </div>
        <form id="addressForm" class="aps-cp-form" data-ajax="true" action="<?= BASE_URL ?>/user/address/store" method="POST">
            <input type="hidden" name="csrf_token" value="">
            <input type="hidden" name="id" id="addressId" value="">
            <div class="aps-cp-modal-body">
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="label"><?= __('user_address_label', null, 'Label') ?> *</label>
                    <input type="text" name="label" id="label" class="aps-cp-input" placeholder="<?= __('user_address_label_placeholder', null, 'e.g. Home, Office') ?>" required>
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="address_type"><?= __('user_address_type', null, 'Type') ?></label>
                    <select name="address_type" id="address_type" class="aps-cp-input">
                        <option value="home"><?= __('user_address_type_home', null, 'Home') ?></option>
                        <option value="office"><?= __('user_address_type_office', null, 'Office') ?></option>
                        <option value="billing"><?= __('user_address_type_billing', null, 'Billing') ?></option>
                        <option value="shipping"><?= __('user_address_type_shipping', null, 'Shipping') ?></option>
                        <option value="other"><?= __('user_address_type_other', null, 'Other') ?></option>
                    </select>
                </div>
<div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="address_line1"><?= __('user_address_line1', null, 'Address Line 1') ?> *</label>
                        <div class="input-group">
                            <input type="text" name="address_line1" id="address_line1" class="aps-cp-input" placeholder="House/Flat No., Building, Street" required data-autofill="address">
                            <button type="button" class="btn btn-outline-secondary" data-action="map-picker" data-target="address_line1" title="Pick on Map">
                                <i class="fas fa-map-marker-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="address_line2"><?= __('user_address_line2', null, 'Address Line 2') ?></label>
                        <input type="text" name="address_line2" id="address_line2" class="aps-cp-input" placeholder="Area, Sector, Landmark">
                    </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="address_line2"><?= __('user_address_line2', null, 'Address Line 2') ?></label>
                    <input type="text" name="address_line2" id="address_line2" class="aps-cp-input" placeholder="<?= __('user_address_line2_placeholder', null, 'Landmark, Area') ?>">
                </div>
                <div class="aps-cp-form-row">
                    <div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="pincode"><?= __('user_address_pincode', null, 'Pincode') ?> *</label>
                        <div class="input-group">
                            <input type="text" name="pincode" id="pincode" class="aps-cp-input" pattern="[0-9]{4,10}" placeholder="273001" required data-autofill="pincode" maxlength="6">
                            <button type="button" class="btn btn-outline-secondary" data-action="gps" title="Use My Location">
                                <i class="fas fa-location-crosshairs"></i>
                            </button>
                        </div>
                        <small id="pincodeStatus" class="aps-cp-muted"></small>
                    </div>
                    <div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="city"><?= __('user_address_city', null, 'City') ?> *</label>
                        <input type="text" name="city" id="city" class="aps-cp-input" required data-autofill="city">
                    </div>
                </div>
                <div class="aps-cp-form-row">
                    <div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="state"><?= __('user_address_state', null, 'State') ?> *</label>
                        <input type="text" name="state" id="state" class="aps-cp-input" required data-autofill="state">
                    </div>
                    <div class="aps-cp-form-section">
                        <label class="aps-cp-label" for="district"><?= __('user_address_district', null, 'District') ?></label>
                        <input type="text" name="district" id="district" class="aps-cp-input" data-autofill="district">
                    </div>
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="phone"><?= __('user_address_phone', null, 'Phone') ?></label>
                    <input type="tel" name="phone" id="phone" class="aps-cp-input" pattern="[0-9+\-\s]{7,15}" placeholder="+91 98765 43210">
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-form-check">
                        <input type="checkbox" name="is_primary" value="1" id="is_primary"> <span><?= __('user_address_set_primary_address', null, 'Set as primary address') ?></span>
                    </label>
                </div>
            </div>
            <div class="aps-cp-modal-foot">
                <button type="button" class="aps-cp-btn aps-cp-btn-secondary" onclick="closeAddressModal()"><?= __('user_address_cancel', null, 'Cancel') ?></button>
                <button type="submit" class="aps-cp-btn aps-cp-btn-primary" id="addressSubmitBtn"><?= __('user_address_save_btn', null, 'Save Address') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
var ADDR_STRINGS = {
    addTitle: <?= json_encode(__('user_address_modal_add', null, 'Add Address')) ?>,
    editTitle: <?= json_encode(__('user_address_modal_edit', null, 'Edit Address')) ?>,
    saved: <?= json_encode(__('user_address_toast_saved', null, 'Address saved')) ?>,
    updated: <?= json_encode(__('user_address_toast_updated', null, 'Address updated')) ?>,
    deleted: <?= json_encode(__('user_address_toast_deleted', null, 'Address deleted')) ?>,
    primarySet: <?= json_encode(__('user_address_toast_primary', null, 'Primary address set')) ?>,
    saveFailed: <?= json_encode(__('user_address_save_failed', null, 'Save failed')) ?>,
    deleteFailed: <?= json_encode(__('user_address_delete_failed', null, 'Delete failed')) ?>,
    networkError: <?= json_encode(__('user_address_network_error', null, 'Network error')) ?>,
    deleteConfirm: <?= json_encode(__('user_address_delete_confirm', null, 'Delete this address?')) ?>,
    pincodeLooking: <?= json_encode(__('user_address_pincode_looking', null, 'Looking up...')) ?>,
    pincodeFilled: <?= json_encode(__('user_address_pincode_filled', null, 'Filled from existing address')) ?>,
    pincodeNoMatch: <?= json_encode(__('user_address_pincode_no_match', null, 'No match. Enter manually.')) ?>,
    saving: <?= json_encode(__('user_address_saving', null, 'Saving...')) ?>,
    failed: <?= json_encode(__('user_address_failed', null, 'Failed')) ?>
};

let addressFormMode = 'create';

function openAddressModal(addr = null) {
    addressFormMode = addr ? 'edit' : 'create';
    const form = document.getElementById('addressForm');
    const title = document.getElementById('addressModalTitle');
    form.reset();
    document.getElementById('pincodeStatus').textContent = '';
    if (addr) {
        title.textContent = ADDR_STRINGS.editTitle;
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
        title.textContent = ADDR_STRINGS.addTitle;
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
    status.textContent = ADDR_STRINGS.pincodeLooking;
    pincodeTimer = setTimeout(async () => {
        try {
            const r = await fetch('<?= BASE_URL ?>/api/address/pincode?pincode=' + v);
            const data = await r.json();
            if (data.found && data.data) {
                document.getElementById('city').value = data.data.city || '';
                document.getElementById('state').value = data.data.state || '';
                status.innerHTML = '<i class="fas fa-check-circle aps-cp-text-success"></i> ' + ADDR_STRINGS.pincodeFilled;
            } else {
                status.textContent = ADDR_STRINGS.pincodeNoMatch;
            }
        } catch (e) { status.textContent = ''; }
    }, 500);
});

document.getElementById('addressForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('addressSubmitBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + ADDR_STRINGS.saving;
    try {
        const r = await fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await r.json();
        if (data.success) {
            APS.toast(addressFormMode === 'edit' ? ADDR_STRINGS.updated : ADDR_STRINGS.saved, 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            APS.toast(data.error || ADDR_STRINGS.saveFailed, 'error');
            btn.disabled = false; btn.innerHTML = '<?= __("user_address_save_btn", null, "Save Address") ?>';
        }
    } catch (err) {
        APS.toast(ADDR_STRINGS.networkError + ': ' + err.message, 'error');
        btn.disabled = false; btn.innerHTML = '<?= __("user_address_save_btn", null, "Save Address") ?>';
    }
});

async function deleteAddress(id) {
    if (!confirm(ADDR_STRINGS.deleteConfirm)) return;
    try {
        const fd = new FormData();
        fd.append('csrf_token', (window.CustomerPages && CustomerPages.fetchCsrf) ? CustomerPages.fetchCsrf() : '');
        fd.append('id', id);
        const r = await fetch('<?= BASE_URL ?>/user/address/delete', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await r.json();
        if (data.success) { APS.toast(ADDR_STRINGS.deleted, 'success'); setTimeout(() => location.reload(), 800); }
        else APS.toast(data.error || ADDR_STRINGS.deleteFailed, 'error');
    } catch (e) { APS.toast(ADDR_STRINGS.networkError, 'error'); }
}

async function setPrimaryAddress(id) {
    try {
        const fd = new FormData();
        fd.append('csrf_token', (window.CustomerPages && CustomerPages.fetchCsrf) ? CustomerPages.fetchCsrf() : '');
        fd.append('id', id);
        const r = await fetch('<?= BASE_URL ?>/user/address/primary', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await r.json();
        if (data.success) { APS.toast(ADDR_STRINGS.primarySet, 'success'); setTimeout(() => location.reload(), 800); }
        else APS.toast(data.error || ADDR_STRINGS.failed, 'error');
    } catch (e) { APS.toast(ADDR_STRINGS.networkError, 'error'); }
}
</script>

<?php
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/customer.php';
