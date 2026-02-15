<?php require_once 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/leads">लीड्स</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= h($lead['name']) ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Lead Details Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="lead-avatar-large mr-4">
                                    <?= strtoupper(substr($lead['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h2 class="mb-1">
                                        <?= h($lead['name']) ?>
                                        <?php if ($lead['company']): ?>
                                            <small class="text-muted">
                                                - <?= h($lead['company']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </h2>
                                    <div class="lead-badges mb-2">
                                        <span class="badge badge-<?= $this->getStatusBadgeClass($lead['status']) ?> mr-2">
                                            <i class="fas fa-tag mr-1"></i>
                                            <?= h($lead['status_name']) ?>
                                        </span>
                                        <span class="badge badge-<?= $this->getPriorityBadgeClass($lead['priority']) ?>">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            <?= h($lead['priority_name']) ?>
                                        </span>
                                    </div>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-clock mr-2"></i>
                                        बनाया गया: <?= date('d M Y, h:i A', strtotime($lead['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="btn-group-vertical">
                                <button type="button" class="btn btn-primary" onclick="addActivity()">
                                    <i class="fas fa-plus mr-2"></i>गतिविधि जोड़ें
                                </button>
                                <button type="button" class="btn btn-info" onclick="addNote()">
                                    <i class="fas fa-sticky-note mr-2"></i>नोट जोड़ें
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                                        <i class="fas fa-cog mr-2"></i>एक्शन
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="/leads/<?= $lead['id'] ?>/edit">
                                            <i class="fas fa-edit mr-2"></i>एडिट करें
                                        </a>
                                        <a class="dropdown-item" href="mailto:<?= h($lead['email']) ?>">
                                            <i class="fas fa-envelope mr-2"></i>ईमेल भेजें
                                        </a>
                                        <a class="dropdown-item" href="tel:<?= h($lead['phone']) ?>">
                                            <i class="fas fa-phone mr-2"></i>कॉल करें
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item text-danger" onclick="deleteLead()">
                                            <i class="fas fa-trash mr-2"></i>डिलीट करें
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lead Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>लीड जानकारी
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">पूरा नाम</label>
                                <div class="font-weight-bold">
                                    <?= h($lead['name']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">ईमेल पता</label>
                                <div class="font-weight-bold">
                                    <?php if ($lead['email']): ?>
                                        <a href="mailto:<?= h($lead['email']) ?>">
                                            <?= h($lead['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">उपलब्ध नहीं</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">फोन नंबर</label>
                                <div class="font-weight-bold">
                                    <a href="tel:<?= h($lead['phone']) ?>">
                                        <i class="fas fa-phone mr-1"></i>
                                        <?= h($lead['phone']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">कंपनी</label>
                                <div class="font-weight-bold">
                                    <?php if ($lead['company']): ?>
                                        <?= h($lead['company']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">उपलब्ध नहीं</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">लीड स्रोत</label>
                                <div class="font-weight-bold">
                                    <i class="fas fa-bullhorn mr-1 text-info"></i>
                                    <?= h($lead['source_name']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">असाइन किया गया</label>
                                <div class="font-weight-bold">
                                    <?php if ($lead['assigned_user_name']): ?>
                                        <i class="fas fa-user mr-1 text-success"></i>
                                        <?= h($lead['assigned_user_name']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">कोई असाइन नहीं</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <?php if ($lead['budget'] || $lead['requirements']): ?>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="text-muted small">बजट</label>
                                    <div class="font-weight-bold">
                                        <?php if ($lead['budget']): ?>
                                            ₹<?= number_format($lead['budget']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">उपलब्ध नहीं</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="text-muted small">आवश्यकताएं</label>
                                    <div class="font-weight-bold">
                                        <?php if ($lead['requirements']): ?>
                                            <?= nl2br(h($lead['requirements'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">उपलब्ध नहीं</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activities & Notes Tabs -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#activities" data-toggle="tab">
                                <i class="fas fa-list mr-1"></i>गतिविधियां (<?= count($activities) ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#notes" data-toggle="tab">
                                <i class="fas fa-sticky-note mr-1"></i>नोट्स (<?= count($notes) ?>)
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Activities Tab -->
                        <div class="tab-pane fade show active" id="activities">
                            <?php if (empty($activities)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-list fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">कोई गतिविधि नहीं मिली</p>
                                    <button class="btn btn-primary" onclick="addActivity()">
                                        पहली गतिविधि जोड़ें
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-info"></div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong><?= h($activity['activity_name']) ?></strong>
                                                        <?php if ($activity['notes']): ?>
                                                            <br><small><?= h($activity['notes']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= date('d M Y, h:i A', strtotime($activity['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        द्वारा: <?= h($activity['user_name']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Notes Tab -->
                        <div class="tab-pane fade" id="notes">
                            <?php if (empty($notes)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">कोई नोट नहीं मिला</p>
                                    <button class="btn btn-primary" onclick="addNote()">
                                        पहला नोट जोड़ें
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="notes-list">
                                    <?php foreach ($notes as $note): ?>
                                        <div class="note-item">
                                            <div class="note-header">
                                                <strong><?= h($note['user_name']) ?></strong>
                                                <small class="text-muted">
                                                    <?= date('d M Y, h:i A', strtotime($note['created_at'])) ?>
                                                </small>
                                            </div>
                                            <div class="note-content">
                                                <?= nl2br(h($note['note'])) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-2"></i>सांख्यिकी
                    </h6>
                </div>
                <div class="card-body">
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>कुल गतिविधियां</span>
                            <span class="badge badge-primary">
                                <?= count($activities) ?>
                            </span>
                        </div>
                    </div>
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>कुल नोट्स</span>
                            <span class="badge badge-info">
                                <?= count($notes) ?>
                            </span>
                        </div>
                    </div>
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>अंतिम अपडेट</span>
                            <span class="badge badge-success">
                                <?= date('d M Y', strtotime($lead['updated_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt mr-2"></i>त्वरित कार्रवाई
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="addActivity()">
                            <i class="fas fa-plus mr-2"></i>गतिविधि जोड़ें
                        </button>
                        <button class="btn btn-info" onclick="addNote()">
                            <i class="fas fa-sticky-note mr-2"></i>नोट जोड़ें
                        </button>
                        <a href="mailto:<?= h($lead['email']) ?>" class="btn btn-success">
                            <i class="fas fa-envelope mr-2"></i>ईमेल भेजें
                        </a>
                        <a href="tel:<?= h($lead['phone']) ?>" class="btn btn-warning">
                            <i class="fas fa-phone mr-2"></i>कॉल करें
                        </a>
                    </div>
                </div>
            </div>

            <!-- Lead Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i>लीड टाइमलाइन
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <small class="text-muted">लीड बनाया गया</small>
                                <br>
                                <small class="font-weight-bold">
                                    <?= date('d M Y, h:i A', strtotime($lead['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <?php if ($lead['assigned_user_name']): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">
                                        असाइन किया गया: <?= h($lead['assigned_user_name']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($activities)): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">
                                        अंतिम गतिविधि: <?= h($activities[0]['activity_name']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Modal -->
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/leads/<?= $lead['id'] ?>/activity">
                <div class="modal-header">
                    <h5 class="modal-title">गतिविधि जोड़ें</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="activity_type">गतिविधि का प्रकार</label>
                        <select class="form-control" id="activity_type" name="activity_id" required>
                            <option value="">चुनें</option>
                            <option value="1">कॉल</option>
                            <option value="2">ईमेल</option>
                            <option value="3">मीटिंग</option>
                            <option value="4">फॉलो-अप</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="activity_notes">नोट्स</label>
                        <textarea class="form-control" id="activity_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">रद्द करें</button>
                    <button type="submit" class="btn btn-primary">जोड़ें</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/leads/<?= $lead['id'] ?>/note">
                <div class="modal-header">
                    <h5 class="modal-title">नोट जोड़ें</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="note_content">नोट सामग्री</label>
                        <textarea class="form-control" id="note_content" name="note" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">रद्द करें</button>
                    <button type="submit" class="btn btn-primary">जोड़ें</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addActivity() {
    $('#activityModal').modal('show');
}

function addNote() {
    $('#noteModal').modal('show');
}

function deleteLead() {
    if (confirm('क्या आप वाकई इस लीड को डिलीट करना चाहते हैं?')) {
        window.location.href = '/leads/<?= $lead['id'] ?>/delete';
    }
}
</script>

<style>
.lead-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 32px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.info-item {
    padding: 0.75rem;
    border-radius: 8px;
    background: #f8f9fc;
    border-left: 4px solid #667eea;
}

.card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #667eea;
}

.timeline-content {
    background: #f8f9fc;
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.notes-list {
    max-height: 400px;
    overflow-y: auto;
}

.note-item {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 4px solid #28a745;
}

.note-header {
    display: flex;
    justify-content: between;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.note-content {
    color: #495057;
    line-height: 1.5;
}

.stats-item {
    padding: 0.75rem;
    border-radius: 8px;
    background: #f8f9fc;
    border-left: 4px solid #28a745;
}

.badge {
    font-size: 0.85em;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    background: #007bff;
    color: white;
    border-radius: 8px 8px 0 0;
}

.dropdown-toggle::after {
    margin-left: 0.5em;
}

@media print {
    .btn, .modal, nav {
        display: none !important;
    }
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
