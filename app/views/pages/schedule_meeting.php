<?php $page_title = $page_title ?? __('schedule_page_title', [], 'Schedule a Meeting - APS Dream Home'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4"><?= __('schedule_heading', [], 'Schedule a Meeting') ?></h1>
            <p class="text-muted mb-4"><?= __('schedule_desc', [], 'Book an appointment with our property experts.') ?></p>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/schedule-meeting">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('schedule_name_label', [], 'Full Name') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('schedule_phone_label', [], 'Phone') ?> <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('schedule_email_label', [], 'Email') ?></label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('schedule_date_label', [], 'Preferred Date') ?></label>
                                <input type="date" class="form-control" name="meeting_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('schedule_time_label', [], 'Preferred Time') ?></label>
                                <input type="time" class="form-control" name="meeting_time">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('schedule_purpose_label', [], 'Purpose') ?></label>
                                <select class="form-select" name="purpose">
                                    <option value="property_visit"><?= __('schedule_purpose_visit', [], 'Property Visit') ?></option>
                                    <option value="consultation"><?= __('schedule_purpose_consultation', [], 'Consultation') ?></option>
                                    <option value="documentation"><?= __('schedule_purpose_docs', [], 'Documentation Help') ?></option>
                                    <option value="other"><?= __('schedule_purpose_other', [], 'Other') ?></option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><?= __('schedule_message_label', [], 'Message') ?></label>
                                <textarea class="form-control" name="message" rows="3"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-4 px-4"><i class="fas fa-calendar-check me-2"></i><?= __('schedule_submit', [], 'Schedule Meeting') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
