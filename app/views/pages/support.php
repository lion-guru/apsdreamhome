<?php
$priority = $_POST['priority'] ?? 'medium';
$subjectVal = $_POST['subject'] ?? '';
$messageVal = $_POST['message'] ?? '';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center font-weight-light my-4">Customer Support</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php elseif (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>support" method="post">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="subject" name="subject" required
                                value="<?php echo isset($subjectVal) ? htmlspecialchars($subjectVal) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select form-select-lg" id="priority" name="priority">
                                <option value="low" <?php echo ($priority === 'low') ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo ($priority === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo ($priority === 'high') ? 'selected' : ''; ?>>High</option>
                                <option value="urgent" <?php echo ($priority === 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?php echo isset($messageVal) ? htmlspecialchars($messageVal) : ''; ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small">
                        Need immediate assistance? Call us at <a href="tel:+919277121112">+91 92771 21112</a> or
                        <a href="mailto:info@apsdreamhome.com">email us</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
