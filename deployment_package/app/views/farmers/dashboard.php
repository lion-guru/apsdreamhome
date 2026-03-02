<?php require_once 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-users mr-2"></i>
                    किसान मैनेजमेंट डैशबोर्ड
                </h1>
                <a href="/farmers/create" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>नया किसान जोड़ें
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                कुल किसान
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $statistics['total_farmers'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                सक्रिय स्टेट्स
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $statistics['unique_states'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                कुल जिले
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $statistics['unique_districts'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-city fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                कंप्लीट प्रोफाइल्स
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $statistics['farmers_with_state'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Quick Actions -->
    <div class="row">
        <!-- Recent Farmers -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users mr-2"></i>हाल ही के किसान
                    </h6>
                    <a href="/farmers" class="btn btn-sm btn-outline-primary">
                        सभी देखें
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($farmers)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">कोई किसान नहीं मिला</p>
                            <a href="/farmers/create" class="btn btn-primary">
                                पहला किसान जोड़ें
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>नाम</th>
                                        <th>फोन</th>
                                        <th>स्टेट/जिला</th>
                                        <th>जमीन होल्डिंग्स</th>
                                        <th>कार्रवाई</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($farmers, 0, 5) as $farmer): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="farmer-avatar mr-3">
                                                        <?= strtoupper(substr($farmer['name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($farmer['name']) ?></strong>
                                                        <?php if ($farmer['email']): ?>
                                                            <br><small class="text-muted">
                                                                <i class="fas fa-envelope mr-1"></i>
                                                                <?= htmlspecialchars($farmer['email']) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="fas fa-phone mr-1"></i>
                                                <?= htmlspecialchars($farmer['phone']) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($farmer['state_name'] ?? 'N/A') ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($farmer['district_name'] ?? 'N/A') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <?= $farmer['total_holdings'] ?? 0 ?> होल्डिंग्स
                                                </span>
                                                <?php if ($farmer['total_area']): ?>
                                                    <br><small class="text-muted">
                                                        <?= number_format($farmer['total_area'], 2) ?> एकड़
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/farmers/<?= $farmer['id'] ?>"
                                                       class="btn btn-outline-info btn-sm"
                                                       title="विवरण देखें">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/farmers/<?= $farmer['id'] ?>/edit"
                                                       class="btn btn-outline-warning btn-sm"
                                                       title="एडिट करें">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="deleteFarmer(<?= $farmer['id'] ?>, '<?= htmlspecialchars($farmer['name']) ?>')"
                                                            title="डिलीट करें">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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

        <!-- Quick Actions & Recent Purchases -->
        <div class="col-lg-4 mb-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt mr-2"></i>त्वरित कार्रवाई
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/farmers/create" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>नया किसान जोड़ें
                        </a>
                        <a href="/farmers/search" class="btn btn-secondary">
                            <i class="fas fa-search mr-2"></i>किसान खोजें
                        </a>
                        <a href="/land-holdings/create" class="btn btn-info">
                            <i class="fas fa-map mr-2"></i>जमीन होल्डिंग जोड़ें
                        </a>
                        <a href="/land-purchases/create" class="btn btn-success">
                            <i class="fas fa-shopping-cart mr-2"></i>जमीन खरीदें
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Purchases -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shopping-cart mr-2"></i>हाल की खरीद
                    </h6>
                    <a href="/land-purchases" class="btn btn-sm btn-outline-primary">
                        सभी देखें
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_purchases)): ?>
                        <div class="text-center py-3">
                            <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">कोई खरीद नहीं हुई</p>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($recent_purchases, 0, 3) as $purchase): ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="farmer-avatar-small mr-3">
                                    <?= strtoupper(substr($purchase['farmer_name'], 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold small">
                                        <?= htmlspecialchars($purchase['farmer_name']) ?>
                                    </div>
                                    <div class="text-muted small">
                                        ₹<?= number_format($purchase['price']) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= date('d M', strtotime($purchase['purchase_date'])) ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-<?= $this->getStatusBadgeClass($purchase['status']) ?>">
                                        <?= $this->getStatusText($purchase['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
                <p>क्या आप वाकई <strong id="farmerName"></strong> को डिलीट करना चाहते हैं?</p>
                <p class="text-danger">यह कार्रवाई वापस नहीं की जा सकती।</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">रद्द करें</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">डिलीट करें</a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteFarmer(id, name) {
    document.getElementById('farmerName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '/farmers/' + id + '/delete';
    $('#deleteModal').modal('show');
}
</script>

<style>
.farmer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

.farmer-avatar-small {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
