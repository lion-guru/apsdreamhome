<?php
$page_title = $page_title ?? 'Create Support Ticket';
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-plus-circle me-2"></i>Create Support Ticket</h2>
            <p>Describe your issue and our team will assist you promptly.</p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/support" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Tickets
            </a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-headset me-2 text-primary"></i>New Ticket</h5>
            </div>
            <div class="aps-cp-card-body">
                <form method="post" action="<?= BASE_URL ?>/user/support/store">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" required minlength="5" maxlength="255"
                               placeholder="Brief description of your issue" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                        <small class="text-muted">Minimum 5 characters</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" class="form-select">
                                <option value="general">General</option>
                                <option value="payment">Payment</option>
                                <option value="booking">Booking</option>
                                <option value="legal">Legal</option>
                                <option value="technical">Technical</option>
                                <option value="complaint">Complaint</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="6" required minlength="10"
                                  placeholder="Please describe your issue in detail..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        <small class="text-muted">Minimum 10 characters</small>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="<?= BASE_URL ?>/user/support" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
