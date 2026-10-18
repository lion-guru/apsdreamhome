<?php
/**
 * Helpdesk & Support Tickets
 */

$page_title = $page_title ?? 'Helpdesk & Support';
$tickets = $tickets ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-headset me-2 text-primary"></i>Helpdesk & Support</h4>
                    <p class="text-muted mb-0 small">Create and track support tickets</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                    <i class="fas fa-plus me-1"></i> Create Ticket
                </button>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Quick Stats -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-primary"><?= count($tickets) ?></div>
                            <div class="small text-muted">Total Tickets</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-warning"><?= count(array_filter($tickets, fn($t) => in_array($t['status'], ['open', 'in_progress']))) ?></div>
                            <div class="small text-muted">Open</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-success"><?= count(array_filter($tickets, fn($t) => $t['status'] === 'resolved')) ?></div>
                            <div class="small text-muted">Resolved</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-danger"><?= count(array_filter($tickets, fn($t) => $t['status'] === 'closed')) ?></div>
                            <div class="small text-muted">Closed</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>My Tickets</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-headset fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Tickets Yet</h5>
                            <p class="text-muted">Create your first support ticket.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                                <i class="fas fa-plus me-1"></i> Create Ticket
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Category</th>
                                        <th>Subject</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $t): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($t['ticket_number'] ?? 'N/A') ?></code></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= ucfirst($t['category'] ?? 'general') ?></span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($t['subject'] ?? 'No Subject') ?></strong>
                                            <?php if (!empty($t['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($t['description'], 0, 80)) ?><?= strlen($t['description']) > 80 ? '...' : '' ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $t['priority'] === 'urgent' ? 'danger' : ($t['priority'] === 'high' ? 'warning text-dark' : ($t['priority'] === 'medium' ? 'info' : 'secondary')) ?>">
                                                <?= ucfirst($t['priority'] ?? 'medium') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $t['status'] === 'open' ? 'primary' : ($t['status'] === 'in_progress' ? 'info' : ($t['status'] === 'resolved' ? 'success' : 'secondary')) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $t['status'] ?? 'open')) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($t['created_at'] ?? 'now')) ?></td>
                                        <td><?= $t['updated_at'] ? date('d M Y', strtotime($t['updated_at'])) : '-' ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/employee/self-service/helpdesk/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SLA Info -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Response Time SLAs</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fw-bold fs-3 text-danger">4 hrs</div>
                                <div class="small">Urgent</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fw-bold fs-3 text-warning">24 hrs</div>
                                <div class="small">High</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fw-bold fs-3 text-info">48 hrs</div>
                                <div class="small">Medium</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fw-bold fs-3 text-secondary">72 hrs</div>
                                <div class="small">Low</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Ticket Modal -->
<div class="modal fade" id="createTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/employee/self-service/helpdesk/create" id="createTicketForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Create Support Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label for="category" class="col-sm-3 col-form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select category</option>
                                <option value="it">IT Support</option>
                                <option value="hr">HR</option>
                                <option value="finance">Finance/Payroll</option>
                                <option value="facilities">Facilities</option>
                                <option value="security">Security</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="subject" class="col-sm-3 col-form-label fw-bold">Subject <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="subject" name="subject" required placeholder="Brief summary of the issue">
                        </div>
                    </div>
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
                    <div class="row mb-3">
                        <label for="description" class="col-sm-3 col-form-label fw-bold">Description <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="description" name="description" rows="5" required placeholder="Describe the issue in detail..."></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="attachment" class="col-sm-3 col-form-label">Attachment</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.png,.xls,.xlsx">
                            <div class="form-text">Max 5MB. PDF, DOC, JPG, PNG, XLS.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>