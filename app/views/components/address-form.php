<?php
/**
 * Shared address form partial — one include for every form needing an address.
 *
 *     <?php
 *     $addressPrefix = 'lead';           // unique per form on the page
 *     $addressValues = $lead ?? [];      // existing values for edit
 *     $addressShowMap = true;            // Leaflet picker on/off
 *     $addressRequired = false;          // required markers on/off
 *     include __DIR__ . '/../../components/address-form.php';
 *     ?>
 *
 * Field names: {prefix}_state_id, {prefix}_district_id, {prefix}_city,
 * {prefix}_pincode, {prefix}_address_line, {prefix}_latitude, {prefix}_longitude.
 * Needs: /api/locations/states, /districts, /cities, /pincode/{pin} (all live).
 */
$addressPrefix = $addressPrefix ?? 'addr';
$addressValues = $addressValues ?? [];
$addressShowMap = $addressShowMap ?? true;
$addressRequired = $addressRequired ?? false;
$addressBase = defined('BASE_URL') ? BASE_URL : '';
$addrReq = $addressRequired ? 'required' : '';
$addrStar = $addressRequired ? ' <span class="text-danger">*</span>' : '';
$addrVal = function ($k) use ($addressValues) { return htmlspecialchars($addressValues[$k] ?? '', ENT_QUOTES, 'UTF-8'); };
$p = preg_replace('/[^a-zA-Z0-9_]/', '', $addressPrefix);
?>
<div class="address-form" id="<?= $p ?>-address-form">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="<?= $p ?>_state_id">State<?= $addrStar ?></label>
                <select class="form-select" id="<?= $p ?>_state_id" name="<?= $p ?>_state_id" <?= $addrReq ?> data-addr-role="state">
                    <option value="">Select State</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="<?= $p ?>_district_id">District<?= $addrStar ?></label>
                <select class="form-select" id="<?= $p ?>_district_id" name="<?= $p ?>_district_id" <?= $addrReq ?> data-addr-role="district">
                    <option value="">Select State First</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="<?= $p ?>_city">City / Town<?= $addrStar ?></label>
                <input type="text" class="form-control" id="<?= $p ?>_city" name="<?= $p ?>_city"
                       value="<?= $addrVal('city') ?>" list="<?= $p ?>_city_list" <?= $addrReq ?> autocomplete="off">
                <datalist id="<?= $p ?>_city_list"></datalist>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="<?= $p ?>_pincode">Pincode<?= $addrStar ?></label>
                <input type="text" class="form-control" id="<?= $p ?>_pincode" name="<?= $p ?>_pincode"
                       value="<?= $addrVal('pincode') ?>" maxlength="6" inputmode="numeric" <?= $addrReq ?>>
                <small class="form-text text-muted" id="<?= $p ?>_pin_hint">6 digits — city/state auto-fill honge</small>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="<?= $p ?>_address_line">Full Address<?= $addrStar ?></label>
        <textarea class="form-control" id="<?= $p ?>_address_line" name="<?= $p ?>_address_line" rows="2" <?= $addrReq ?>><?= $addrVal('address_line') ?></textarea>
    </div>
    <?php if ($addressShowMap): ?>
    <div class="mb-3">
        <label class="form-label">Location on Map <small class="text-muted">(click to set GPS)</small></label>
        <div id="<?= $p ?>_map" style="height: 260px; width: 100%;" class="border rounded"></div>
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="<?= $p ?>_latitude">Latitude</label>
                <input type="text" class="form-control" id="<?= $p ?>_latitude" name="<?= $p ?>_latitude"
                       value="<?= $addrVal('latitude') ?>" placeholder="26.7606" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="<?= $p ?>_longitude">Longitude</label>
                <input type="text" class="form-control" id="<?= $p ?>_longitude" name="<?= $p ?>_longitude"
                       value="<?= $addrVal('longitude') ?>" placeholder="83.3732" readonly>
            </div>
        </div>
    </div>
    <input type="hidden" id="<?= $p ?>_sel_state" value="<?= $addrVal('state_id') ?>">
    <input type="hidden" id="<?= $p ?>_sel_district" value="<?= $addrVal('district_id') ?>">
</div>
<script>
(function() {
    var P = <?= json_encode($p) ?>;
    var BASE = <?= json_encode($addressBase) ?>;
    var SHOW_MAP = <?= $addressShowMap ? 'true' : 'false' ?>;
    var $ = function(id) { return document.getElementById(P + '_' + id); };
    var stateSel = $('state_id'), distSel = $('district_id'), cityIn = $('city'),
        cityList = $('city_list'), pinIn = $('pincode'), pinHint = $('pin_hint'),
        latIn = $('latitude'), lngIn = $('longitude');

    function getJSON(url, cb) {
        fetch(url).then(function(r) { return r.json(); }).then(cb).catch(function(e) { console.error('address-form:', e); });
    }
    function setOptions(sel, rows, valKey, textKey, placeholder) {
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        (rows || []).forEach(function(r) {
            var o = document.createElement('option');
            o.value = r[valKey]; o.textContent = r[textKey];
            sel.appendChild(o);
        });
    }

    // States (preselect on edit)
    getJSON(BASE + '/api/locations/states', function(rows) {
        setOptions(stateSel, rows, 'id', 'name', 'Select State');
        var sv = $('sel_state').value;
        if (sv) { stateSel.value = sv; loadDistricts(sv, $('sel_district').value); }
    });

    function loadDistricts(stateId, preselect) {
        if (!stateId) { distSel.innerHTML = '<option value="">Select State First</option>'; return; }
        getJSON(BASE + '/api/locations/districts?state_id=' + stateId, function(rows) {
            setOptions(distSel, rows, 'id', 'name', 'Select District');
            if (preselect) distSel.value = preselect;
        });
    }
    stateSel.addEventListener('change', function() { loadDistricts(this.value, ''); suggestCities(); });

    // City suggestions for selected district
    var cityTimer = null;
    function suggestCities() {
        var did = distSel.value;
        if (!did) return;
        getJSON(BASE + '/api/locations/cities?district_id=' + did, function(rows) {
            cityList.innerHTML = '';
            (rows || []).slice(0, 50).forEach(function(r) {
                var o = document.createElement('option');
                o.value = r.name;
                cityList.appendChild(o);
            });
        });
    }
    distSel.addEventListener('change', suggestCities);

    // Pincode auto-fill (debounced)
    var pinTimer = null;
    pinIn.addEventListener('input', function() {
        clearTimeout(pinTimer);
        var pin = this.value.replace(/\D/g, '').slice(0, 6);
        if (pin.length !== 6) return;
        pinTimer = setTimeout(function() {
            getJSON(BASE + '/api/locations/pincode/' + pin, function(res) {
                if (!res || !res.found) {
                    if (pinHint) { pinHint.textContent = 'Pincode nahi mila — manual bharein'; pinHint.className = 'form-text text-warning'; }
                    return;
                }
                if (pinHint) { pinHint.textContent = 'Auto-filled: ' + (res.city || res.district || ''); pinHint.className = 'form-text text-success'; }
                if (res.city && !cityIn.value) cityIn.value = res.city;
                if (res.latitude && res.longitude) {
                    latIn.value = res.latitude; lngIn.value = res.longitude;
                    moveMarker(parseFloat(res.latitude), parseFloat(res.longitude), 13);
                }
            });
        }, 500);
    });

    // Leaflet picker
    var map = null, marker = null;
    function moveMarker(lat, lng, zoom) {
        if (!map || isNaN(lat) || isNaN(lng)) return;
        if (zoom) map.setView([lat, lng], zoom);
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
    }
    function initMap() {
        if (!SHOW_MAP || typeof L === 'undefined') return;
        if (!document.getElementById(P + '_map')) return;
        var lat = parseFloat(latIn.value) || 26.7606, lng = parseFloat(lngIn.value) || 83.3732;
        map = L.map(P + '_map').setView([lat, lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        if (latIn.value && lngIn.value) moveMarker(lat, lng);
        map.on('click', function(e) {
            latIn.value = e.latlng.lat.toFixed(6);
            lngIn.value = e.latlng.lng.toFixed(6);
            moveMarker(e.latlng.lat, e.latlng.lng);
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMap);
    } else { initMap(); }
})();
</script>
