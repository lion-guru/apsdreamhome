<?php
/**
 * Company Announcements
 */

$page_title = $page_title ?? 'Company Announcements';
$announcements = $announcements ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-bullhorn me-2 text-warning"></i>Company Announcements</h4>
                    <p class="text-muted mb-0 small">Stay updated with company news and notices</p>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Announcements List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Announcements</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($announcements)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Announcements</h5>
                            <p class="text-muted">No company announcements at this time.</p>
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="announcementsAccordion">
                            <?php foreach ($announcements as $index => $a): ?>
                            <div class="accordion-item mb-2 <?= !$a['read'] ? 'border-warning' : '' ?>">
                                <h2 class="accordion-header" id="heading<?= $a['id'] ?? $index ?>">
                                    <button class="accordion-button <?= !$a['read'] ? 'fw-bold' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $a['id'] ?? $index ?>" aria-expanded="<?= !$a['read'] ? 'true' : 'false' ?>" aria-controls="collapse<?= $a['id'] ?? $index ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <div>
                                                <?php if (!$a['read']): ?>
                                                <span class="badge bg-warning text-dark me-2"><i class="fas fa-circle me-1"></i>New</span>
                                                <?php endif; ?>
                                                <strong><?= htmlspecialchars($a['title'] ?? 'Untitled') ?></strong>
                                                <?php if (!empty($a['priority'])): ?>
                                                <span class="badge bg-<?= $a['priority'] === 'high' ? 'danger' : ($a['priority'] === 'medium' ? 'warning text-dark' : 'info') ?> ms-2">
                                                    <?= ucfirst($a['priority']) ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?= date('d M Y H:i', strtotime($a['created_at'] ?? 'now')) ?></small>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?= $a['id'] ?? $index ?>" class="accordion-collapse collapse <?= !$a['read'] ? 'show' : '' ?>" aria-labelledby="heading<?= $a['id'] ?? $index ?>" data-bs-parent="#announcementsAccordion">
                                    <div class="accordion-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <strong>Category:</strong> 
                                                <span class="badge bg-secondary ms-1"><?= htmlspecialchars($a['category'] ?? 'General') ?></span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Department:</strong> 
                                                <span class="badge bg-info text-dark ms-1"><?= htmlspecialchars($a['department'] ?? 'All') ?></span>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="announcement-content">
                                            <?= nl2br(htmlspecialchars($a['content'] ?? '')) ?>
                                        </div>
                                        <?php if (!empty($a['attachment'])): ?>
                                        <hr>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-paperclip me-2 text-muted"></i>
                                            <a href="<?= BASE_URL ?>/uploads/announcements/<?= htmlspecialchars($a['attachment']) ?>" target="_blank" class="text-decoration-none">
                                                <i class="fas fa-file me-1"></i> <?= htmlspecialchars($a['attachment']) ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!$a['read']): ?>
                                        <hr>
                                        <form method="POST" action="<?= BASE_URL ?>/employee/self-service/announcements/<?= $a['id'] ?>/read" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check me-1"></i> Mark as Read
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Announcements</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="filter_category" class="form-label">Category</label>
                            <select class="form-select" id="filter_category" name="category">
                                <option value="">All Categories</option>
                                <option value="general">General</option>
                                <option value="policy">Policy Update</option>
                                <option value="event">Event</option>
                                <option value="holiday">Holiday</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="security">Security</option>
                                <option value="hr">HR</option>
                                <option value="it">IT</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_priority" class="form-label">Priority</label>
                            <select class="form-select" id="filter_priority" name="priority">
                                <option value="">All Priorities</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="filter_date_from" name="date_from">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>