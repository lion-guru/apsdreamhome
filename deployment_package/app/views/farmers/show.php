<?php require_once 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/farmers">किसान</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= htmlspecialchars($farmer['name']) ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Farmer Details Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="farmer-avatar-large mr-4">
                                    <?= strtoupper(substr($farmer['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h2 class="mb-1">
                                        <?= htmlspecialchars($farmer['name']) ?>
                                        <span class="badge badge-<?= $farmer['status'] === 'active' ? 'success' : 'secondary' ?> ml-2">
                                            <?= $farmer['status'] === 'active' ? 'सक्रिय' : 'निष्क्रिय' ?>
                                        </span>
                                    </h2>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-id-badge mr-2"></i>
                                        किसान ID: <?= $farmer['id'] ?>
                                    </p>
                                    <p class="text-muted">
                                        <i class="fas fa-calendar-plus mr-2"></i>
                                        जुड़ा: <?= date('d F Y', strtotime($farmer['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="/farmers/<?= $farmer['id'] ?>/edit" class="btn btn-warning">
                                <i class="fas fa-edit mr-2"></i>एडिट करें
                            </a>
                            <button type="button" class="btn btn-danger ml-2" onclick="deleteFarmer(<?= $farmer['id'] ?>)">
                                <i class="fas fa-trash mr-2"></i>डिलीट करें
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Farmer Information -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user mr-2"></i>व्यक्तिगत जानकारी
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">पूरा नाम</label>
                                <div class="font-weight-bold">
                                    <?= htmlspecialchars($farmer['name']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">मोबाइल नंबर</label>
                                <div class="font-weight-bold">
                                    <i class="fas fa-phone mr-1 text-success"></i>
                                    <a href="tel:<?= htmlspecialchars($farmer['phone']) ?>">
                                        <?= htmlspecialchars($farmer['phone']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">ईमेल पता</label>
                                <div class="font-weight-bold">
                                    <?php if ($farmer['email']): ?>
                                        <i class="fas fa-envelope mr-1 text-primary"></i>
                                        <a href="mailto:<?= htmlspecialchars($farmer['email']) ?>">
                                            <?= htmlspecialchars($farmer['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">उपलब्ध नहीं</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">पता</label>
                                <div class="font-weight-bold">
                                    <?= nl2br(htmlspecialchars($farmer['address'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">राज्य</label>
                                <div class="font-weight-bold">
                                    <i class="fas fa-map-marker-alt mr-1 text-info"></i>
                                    <?= htmlspecialchars($farmer['state_name'] ?? 'N/A') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">जिला</label>
                                <div class="font-weight-bold">
                                    <i class="fas fa-city mr-1 text-info"></i>
                                    <?= htmlspecialchars($farmer['district_name'] ?? 'N/A') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-rupee-sign mr-2"></i>वित्तीय जानकारी
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">आधार नंबर</label>
                                <div class="font-weight-bold">
                                    <?php if ($farmer['aadhar_number']): ?>
                                        <?= htmlspecialchars(substr($farmer['aadhar_number'], 0, 4) . 'XXXX' . substr($farmer['aadhar_number'], -4)) ?>
                                    <?php else: ?>
                                        <span class="text-muted">उपलब्ध नहीं</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">पैन नंबर</label>
                                <div class="font-weight-bold">
                                    <?php if ($farmer['pan_number']): ?>
                                        <?= htmlspecialchars($farmer['pan_number']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">उपलब्ध नहीं</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">बैंक अकाउंट</label>
                                <div class="font-weight-bold">
                                    <?php if ($farmer['bank_account']): ?>
                                        <?= htmlspecialchars(substr($farmer['bank_account'], 0, 4) . 'XXXX' . substr($farmer['bank_account'], -4)) ?>
                                    <?php else: ?>
                                        <span class="text-muted">उपलब्ध नहीं</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">IFSC कोड</label>
                                <div class="font-weight-bold">
                                    <?php if ($farmer['ifsc_code']): ?>
                                        <?= htmlspecialchars($farmer['ifsc_code']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">उपलब्ध नहीं</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Land Holdings -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-map mr-2"></i>जमीन होल्डिंग्स
                    </h6>
                    <a href="/land-holdings/create?farmer_id=<?= $farmer['id'] ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-plus mr-1"></i>नई होल्डिंग जोड़ें
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($land_holdings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-map fa-3x text-muted mb-3"></i>
                            <p class="text-muted">कोई जमीन होल्डिंग नहीं मिली</p>
                            <a href="/land-holdings/create?farmer_id=<?= $farmer['id'] ?>" class="btn btn-success">
                                पहली होल्डिंग जोड़ें
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>सर्वे नंबर</th>
                                        <th>क्षेत्रफल</th>
                                        <th>जमीन का प्रकार</th>
                                        <th>लोकेशन</th>
                                        <th>स्टेटस</th>
                                        <th>कार्रवाई</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($land_holdings as $holding): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($holding['survey_number']) ?></strong>
                                            </td>
                                            <td>
                                                <?= number_format($holding['area'], 2) ?>
                                                <?= htmlspecialchars($holding['area_unit']) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($holding['land_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($holding['location_address']) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $holding['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                    <?= $holding['status'] === 'active' ? 'सक्रिय' : 'निष्क्रिय' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/land-holdings/<?= $holding['id'] ?>"
                                                       class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/land-holdings/<?= $holding['id'] ?>/edit"
                                                       class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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
                            <span>कुल होल्डिंग्स</span>
                            <span class="badge badge-primary">
                                <?= count($land_holdings) ?>
                            </span>
                        </div>
                    </div>
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>कुल क्षेत्रफल</span>
                            <span class="badge badge-success">
                                <?= number_format(array_sum(array_column($land_holdings, 'area')), 2) ?> एकड़
                            </span>
                        </div>
                    </div>
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>सक्रिय होल्डिंग्स</span>
                            <span class="badge badge-info">
                                <?= count(array_filter($land_holdings, fn($h) => $h['status'] === 'active')) ?>
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
                        <a href="/land-holdings/create?farmer_id=<?= $farmer['id'] ?>"
                           class="btn btn-success">
                            <i class="fas fa-plus mr-2"></i>नई होल्डिंग जोड़ें
                        </a>
                        <a href="/land-purchases/create?farmer_id=<?= $farmer['id'] ?>"
                           class="btn btn-warning">
                            <i class="fas fa-shopping-cart mr-2"></i>जमीन खरीदें
                        </a>
                        <button type="button" class="btn btn-info" onclick="printFarmerDetails()">
                            <i class="fas fa-print mr-2"></i>डिटेल्स प्रिंट करें
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i>हाल की गतिविधि
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <small class="text-muted">
                                    किसान रजिस्टर्ड किया गया
                                </small>
                                <br>
                                <small class="font-weight-bold">
                                    <?= date('d M Y, h:i A', strtotime($farmer['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <?php if (count($land_holdings) > 0): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">
                                        <?= count($land_holdings) ?> जमीन होल्डिंग्स जोड़ी गईं
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">किसान डिलीट करें</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>क्या आप वाकई <strong><?= htmlspecialchars($farmer['name']) ?></strong> को डिलीट करना चाहते हैं?</p>
                <p class="text-danger">यह कार्रवाई वापस नहीं की जा सकती।</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">रद्द करें</button>
                <a href="/farmers/<?= $farmer['id'] ?>/delete" class="btn btn-danger">डिलीट करें</a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteFarmer(id) {
    $('#deleteModal').modal('show');
}

function printFarmerDetails() {
    window.print();
}
</script>

<style>
.farmer-avatar-large {
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

.stats-item {
    padding: 0.75rem;
    border-radius: 8px;
    background: #f8f9fc;
    border-left: 4px solid #28a745;
}

.badge {
    font-size: 0.85em;
}

@media print {
    .btn, .modal, nav {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
