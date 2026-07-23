<?php
/**
 * Schedule Meeting View
 * Data: $page_title
 */
$page_title = $page_title ?? 'Schedule Meeting';
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-plus me-2 text-primary"></i>Schedule Meeting</h2>
    <a href="<?= BASE_URL ?>/admin/meetings" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> All Meetings</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white"><i class="fas fa-clock me-1"></i> New Meeting</div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/meetings/store">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Site visit - Plot 12, Suryoday">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Meeting Type</label>
                            <select name="meeting_type" class="form-select">
                                <option value="site_visit">Site Visit</option>
                                <option value="office_call">Office Call</option>
                                <option value="phone">Phone Call</option>
                                <option value="video">Video Call</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="Office / Colony / Video link">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lead ID</label>
                            <input type="number" name="lead_id" class="form-control" placeholder="CRM lead ID (optional)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">User ID</label>
                            <input type="number" name="user_id" class="form-control" placeholder="Associated user ID (optional)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="datetime-local" name="end_time" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description / Agenda</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Notes, agenda, or preparation needed"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Schedule Meeting</button>
                </form>
            </div>
        </div>
    </div>
</div>
