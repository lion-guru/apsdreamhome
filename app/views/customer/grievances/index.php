<?php
/**
 * Customer Self-Service Township Infrastructure Grievances View
 */
$grievances = $grievances ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-headset text-primary me-2"></i>My Township Infrastructure Requests</h3>
            <p class="text-muted small mb-0">Track civil repairs, water supply, drainage &amp; street lighting maintenance</p>
        </div>
        <div>
            <a href="/customer/township-grievances/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Raise Maintenance Request
            </a>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Ticket Token</th>
                            <th>Township &amp; Plot</th>
                            <th>Category</th>
                            <th>Issue Summary</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($grievances)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-check fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    You have not submitted any maintenance requests yet.
                                    <div class="mt-3">
                                        <a href="/customer/township-grievances/create" class="btn btn-sm btn-primary">
                                            Raise New Maintenance Request
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($grievances as $row): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold font-monospace text-dark"><?= htmlspecialchars($row['ticket_token']) ?></div>
                                        <div class="small text-muted"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['colony_name'] ?? 'APS Township') ?></div>
                                        <?php if (!empty($row['plot_number'])): ?>
                                            <div class="small text-muted">Plot #<?= htmlspecialchars($row['plot_number']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-capitalize">
                                        <?= str_replace('_', ' ', htmlspecialchars($row['category'])) ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['title']) ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;"><?= htmlspecialchars($row['description']) ?></div>
                                        <?php if (!empty($row['resolution_notes'])): ?>
                                            <div class="small text-success mt-1"><i class="fas fa-check me-1"></i>Resolution: <?= htmlspecialchars($row['resolution_notes']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['priority'] === 'emergency'): ?>
                                            <span class="badge bg-danger">Emergency</span>
                                        <?php elseif ($row['priority'] === 'high'): ?>
                                            <span class="badge bg-warning text-dark">High</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Medium</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'closed'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Closed</span>
                                            <?php if (!empty($row['resident_rating'])): ?>
                                                <div class="small text-warning mt-1"><?= str_repeat('★', (int)$row['resident_rating']) ?></div>
                                            <?php endif; ?>
                                        <?php elseif ($row['status'] === 'resolved'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-clipboard-check me-1"></i>Resolved</span>
                                        <?php elseif ($row['status'] === 'in_progress'): ?>
                                            <span class="badge bg-primary"><i class="fas fa-wrench me-1"></i>In Progress</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Open</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if ($row['status'] === 'resolved'): ?>
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#ratingModal<?= $row['id'] ?>">
                                                <i class="fas fa-star me-1"></i>Rate &amp; Close
                                            </button>

                                            <!-- Rating Modal -->
                                            <div class="modal fade" id="ratingModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="/customer/township-grievances/close/<?= $row['id'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                                        <div class="modal-content text-start">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">Rate Resolution — <?= htmlspecialchars($row['ticket_token']) ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="text-muted small mb-3">Our civil estate team marked this request as resolved. Please rate the service quality.</p>
                                                                <div class="mb-3">
                                                                    <label for="rating_<?= $row['id'] ?>" class="form-label fw-semibold">Satisfaction Rating</label>
                                                                    <select name="resident_rating" id="rating_<?= $row['id'] ?>" class="form-select">
                                                                        <option value="5">★★★★★ 5 Stars — Excellent &amp; Timely</option>
                                                                        <option value="4">★★★★☆ 4 Stars — Satisfied</option>
                                                                        <option value="3">★★★☆☆ 3 Stars — Average</option>
                                                                        <option value="2">★★☆☆☆ 2 Stars — Delayed</option>
                                                                        <option value="1">★☆☆☆☆ 1 Star — Unsatisfactory</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="fb_<?= $row['id'] ?>" class="form-label">Feedback / Remarks (Optional)</label>
                                                                    <textarea class="form-control" id="fb_<?= $row['id'] ?>" name="resident_feedback" rows="2" placeholder="Great job by the technician."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-success">Submit Rating &amp; Close Ticket</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="small text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
