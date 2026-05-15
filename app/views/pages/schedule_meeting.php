<?php $page_title = $page_title ?? 'Schedule a Meeting - APS Dream Home'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4">Schedule a Meeting</h1>
            <p class="text-muted mb-4">Book an appointment with our property experts.</p>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/schedule-meeting">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Date</label>
                                <input type="date" class="form-control" name="meeting_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Time</label>
                                <input type="time" class="form-control" name="meeting_time">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Purpose</label>
                                <select class="form-select" name="purpose">
                                    <option value="property_visit">Property Visit</option>
                                    <option value="consultation">Consultation</option>
                                    <option value="documentation">Documentation Help</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" name="message" rows="3"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-4 px-4"><i class="fas fa-calendar-check me-2"></i>Schedule Meeting</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
