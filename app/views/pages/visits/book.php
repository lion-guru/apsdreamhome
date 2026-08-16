<?php
$page_title = $page_title ?? __('visit_hero_title', [], 'Schedule Site Visit');
$page_heading = $page_heading ?? __('visit_hero_subtitle', [], 'Book Property Visit');
$content = $content ?? '';
$property = $property ?? null;
$slots = $slots ?? [];
$property_id = $property_id ?? 0;
$logged_in = $logged_in ?? false;
$form_data = $_SESSION['visit_form'] ?? [];
$errors = $_SESSION['visit_errors'] ?? [];
unset($_SESSION['visit_form'], $_SESSION['visit_errors']);
?>
<style>
.visit-hero { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: #fff; padding: 50px 0; }
.slot-pill { background: white; border: 2px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; cursor: pointer; transition: all 0.2s; text-align: center; }
.slot-pill:hover { border-color: #06b6d4; transform: translateY(-2px); }
.slot-pill.selected { border-color: #06b6d4; background: #06b6d4; color: white; }
.slot-pill.full { opacity: 0.5; cursor: not-allowed; }
.slot-pill.partial { border-color: #f59e0b; }
.day-tabs { display: flex; gap: 8px; overflow-x: auto; padding: 8px 0; }
.day-tab { background: white; border: 2px solid #e5e7eb; border-radius: 8px; padding: 12px 20px; cursor: pointer; min-width: 100px; text-align: center; }
.day-tab.active { border-color: #06b6d4; background: #06b6d4; color: white; }
</style>

<section class="visit-hero">
    <div class="container text-center">
        <h1 class="display-5 fw-bold mb-2"><i class="fas fa-calendar-check me-2"></i>Schedule a Site Visit</h1>
        <p class="lead mb-0 opacity-90"><?= __('visit_hero_desc', [], 'Pick a convenient time and we\'ll arrange a free site visit for you') ?></p>
    </div>
</section>

<div class="container py-4">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($property): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                        <i class="fas fa-home fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1"><?= htmlspecialchars($property['title'] ?? $property['address'] ?? 'Property') ?></h5>
                        <p class="mb-0 text-muted">
                            <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($property['address'] ?? $property['city'] ?? 'N/A') ?>
                            <span class="mx-2">Â·</span>
                            <strong class="text-success">₹<?= number_format($property['price'] ?? 0) ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/visit/store" id="visitForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="property_id" value="<?= $property_id ?>">
        <input type="hidden" name="visit_date" id="selectedDate">
        <input type="hidden" name="visit_time" id="selectedTime">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i><?= __('visit_step1_title', [], '1. Pick a Date') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (empty($slots)): ?>
                            <p class="text-muted"><?= __('visit_no_slots', [], 'No available slots in the next 14 days') ?></p>
                        <?php else: ?>
                            <?php
                            $byDate = [];
                            foreach ($slots as $s) { $byDate[$s['date']][] = $s; }
                            $dates = array_keys($byDate);
                            ?>
                            <div class="day-tabs" id="dayTabs">
                                <?php foreach (array_slice($dates, 0, 8) as $i => $date): ?>
                                    <div class="day-tab <?= $i === 0 ? 'active' : '' ?>" onclick="selectDate('<?= $date ?>', this)">
                                        <small class="d-block"><?= date('D', strtotime($date)) ?></small>
                                        <strong><?= date('M j', strtotime($date)) ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i><?= __('visit_step2_title', [], '2. Pick a Time Slot') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-2" id="timeSlots">
                            <?php if (!empty($slots)): ?>
                                <?php foreach (($byDate[$dates[0]] ?? []) as $slot):
                                    $isFull = $slot['current_bookings'] >= $slot['max_bookings'];
                                    $isPartial = $slot['current_bookings'] > 0 && !$isFull;
                                    $cls = $isFull ? 'full' : ($isPartial ? 'partial' : '');
                                ?>
                                    <div class="col-md-3 col-6">
                                        <div class="slot-pill <?= $cls ?>" data-time="<?= $slot['time_slot'] ?>"
                                             <?= $isFull ? '' : 'onclick="selectTime(this)"' ?>>
                                            <i class="far fa-clock"></i>
                                            <strong class="d-block"><?= date('h:i A', strtotime($slot['time_slot'])) ?></strong>
                                            <small><?= $slot['current_bookings'] ?>/<?= $slot['max_bookings'] ?> booked</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i><?= __('visit_step3_title', [], '3. Your Information') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label"><?= __('visit_type_label', [], 'Visit Type') ?></label>
                                <select class="form-select" name="visit_type">
                                    <option value="site_visit">ðŸ�  <?= __('visit_type_site', [], 'Site Visit (in-person)') ?></option>
                                    <option value="virtual_tour">ðŸ’» <?= __('visit_type_virtual', [], 'Virtual Tour (video)') ?></option>
                                    <option value="office_meeting">ðŸ�¢ <?= __('visit_type_office', [], 'Office Meeting') ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?= __('visit_name_label', [], 'Name *') ?></label>
                                <input type="text" class="form-control" name="name" required
                                       value="<?= htmlspecialchars($form_data['name'] ?? ($_SESSION['user_name'] ?? '')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?= __('visit_phone_label', [], 'Phone *') ?></label>
                                <input type="tel" class="form-control" name="phone" required
                                       value="<?= htmlspecialchars($form_data['phone'] ?? ($_SESSION['user_phone'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('visit_email_label', [], 'Email *') ?></label>
                                <input type="email" class="form-control" name="email" required
                                       value="<?= htmlspecialchars($form_data['email'] ?? ($_SESSION['user_email'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('visit_notes_label', [], 'Additional Notes') ?></label>
                                <input type="text" class="form-control" name="notes"
                                       value="<?= htmlspecialchars($form_data['notes'] ?? '') ?>"
                                       placeholder="<?= __('visit_notes_placeholder', [], 'Any specific requirements?') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3" class="style-86581">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i><?= __('visit_summary_title', [], 'Booking Summary') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block"><?= __('visit_summary_date', [], 'Selected Date') ?></small>
                            <strong id="summaryDate"><?= __('visit_summary_date_placeholder', [], 'Please pick a date') ?></strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block"><?= __('visit_summary_time', [], 'Selected Time') ?></small>
                            <strong id="summaryTime"><?= __('visit_summary_time_placeholder', [], 'Please pick a time') ?></strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block"><?= __('visit_summary_type', [], 'Visit Type') ?></small>
                            <strong id="summaryType"><?= __('visit_summary_type_default', [], 'Site Visit') ?></strong>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn" disabled>
                            <i class="fas fa-check-circle me-2"></i> <?= __('visit_summary_submit', [], 'Confirm Booking') ?>
                        </button>
                        <p class="small text-muted mt-2 mb-0 text-center"><?= __('visit_summary_note', [], 'Free visit Â· No obligations') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const slotsByDate = <?= json_encode($byDate ?? []) ?>;
function selectDate(date, el) {
    document.getElementById('selectedDate').value = date;
    document.querySelectorAll('.day-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    const dateObj = new Date(date);
    document.getElementById('summaryDate').textContent = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    const container = document.getElementById('timeSlots');
    container.innerHTML = '';
    (slotsByDate[date] || []).forEach(slot => {
        const isFull = slot.current_bookings >= slot.max_bookings;
        const isPartial = slot.current_bookings > 0 && !isFull;
        const time12 = new Date('2000-01-01 ' + slot.time_slot).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        const div = document.createElement('div');
        div.className = 'col-md-3 col-6';
        div.innerHTML = `<div class="slot-pill ${isFull ? 'full' : (isPartial ? 'partial' : '')}" data-time="${slot.time_slot}" ${isFull ? '' : 'onclick="selectTime(this)"'}>
            <i class="far fa-clock"></i>
            <strong class="d-block">${time12}</strong>
            <small>${slot.current_bookings}/${slot.max_bookings} booked</small>
        </div>`;
        container.appendChild(div);
    });
    validateForm();
}
function selectTime(el) {
    document.getElementById('selectedTime').value = el.dataset.time;
    document.querySelectorAll('.slot-pill').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');
    const time12 = new Date('2000-01-01 ' + el.dataset.time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    document.getElementById('summaryTime').textContent = time12;
    validateForm();
}
function validateForm() {
    const hasDate = document.getElementById('selectedDate').value !== '';
    const hasTime = document.getElementById('selectedTime').value !== '';
    document.getElementById('submitBtn').disabled = !(hasDate && hasTime);
}
document.querySelector('select[name="visit_type"]').addEventListener('change', function() {
    const labels = { site_visit: 'ðŸ�  Site Visit', virtual_tour: 'ðŸ’» Virtual Tour', office_meeting: 'ðŸ�¢ Office Meeting' };
    document.getElementById('summaryType').textContent = labels[this.value] || this.value;
});
</script>