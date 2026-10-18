<?php
/**
 * Create Support Ticket
 */

$page_title = $page_title ?? 'Create Support Ticket';
$categories = $categories ?? ['it', 'hr', 'finance', 'facilities', 'security', 'other'];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus me-2"></i><?= htmlspecialchars($page_title) ?></h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Submit a support ticket for IT, HR, Finance, Facilities, Security, or other issues.</p>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/helpdesk/create" id="createTicketForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- Category -->
                        <div class="row mb-3">
                            <label for="category" class="col-sm-3 col-form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select category</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="row mb-3">
                            <label for="subject" class="col-sm-3 col-form-label fw-bold">Subject <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="subject" name="subject" required placeholder="Brief summary of the issue">
                            </div>
                        </div>

                        <!-- Priority -->
                        <div class="row mb-3">
                            <label for="priority" class="col-sm-3 col-form-label fw-bold">Priority <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="low">Low - General inquiry</option>
                                    <option value="medium" selected>Medium - Standard issue</option>
                                    <option value="high">High - Affects work</option>
                                    <option value="urgent">Urgent - Critical/Blocking</option>
                                </select>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row mb-3">
                            <label for="description" class="col-sm-3 col-form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="description" name="description" rows="6" required placeholder="Describe the issue in detail..."></textarea>
                            </div>
                        </div>

                        <!-- Attachment -->
                        <div class="row mb-3">
                            <label for="attachment" class="col-sm-3 col-form-label">Attachment</label>
                            <div class="col-sm-9">
                                <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.png,.xls,.xlsx">
                                <div class="form-text">Max 5MB. PDF, DOC, JPG, PNG, XLS, XLSX.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary btn-lg me-2">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Ticket
                                </button>
                                <a href="<?= BASE_URL ?>/employee/self-service/helpdesk" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>