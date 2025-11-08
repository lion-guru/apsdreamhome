<?php require_once 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-users mr-2"></i>
                    सभी किसान
                </h1>
                <div class="d-flex">
                    <!-- Search Form -->
                    <form class="form-inline mr-3" method="GET" action="/farmers/search">
                        <div class="input-group">
                            <input type="text"
                                   name="q"
                                   class="form-control"
                                   placeholder="किसान खोजें..."
                                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <a href="/farmers/create" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>नया किसान जोड़ें
                    </a>
                </div>
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
                                स्टेट्स कवर किए गए
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
                                जिले कवर किए गए
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

    <!-- Farmers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table mr-2"></i>किसान सूची
                    </h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-filter mr-1"></i>फिल्टर
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="?filter=all">सभी किसान</a>
                            <a class="dropdown-item" href="?filter=active">सक्रिय किसान</a>
                            <a class="dropdown-item" href="?filter=inactive">निष्क्रिय किसान</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($farmers)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">कोई किसान नहीं मिला</h4>
                            <p class="text-muted mb-4">अभी तक कोई किसान रजिस्टर्ड नहीं है।</p>
                            <a href="/farmers/create" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus mr-2"></i>पहला किसान जोड़ें
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="farmersTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>किसान</th>
                                        <th>संपर्क जानकारी</th>
                                        <th>लोकेशन</th>
                                        <th>जमीन होल्डिंग्स</th>
                                        <th>स्टेटस</th>
                                        <th>जुड़ा</th>
                                        <th>कार्रवाई</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($farmers as $farmer): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="farmer-avatar mr-3">
                                                        <?= strtoupper(substr($farmer['name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($farmer['name']) ?></strong>
                                                        <?php if ($farmer['aadhar_number']): ?>
                                                            <br><small class="text-muted">
                                                                आधार: <?= htmlspecialchars(substr($farmer['aadhar_number'], -4)) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="fas fa-phone mr-1 text-success"></i>
                                                    <a href="tel:<?= htmlspecialchars($farmer['phone']) ?>">
                                                        <?= htmlspecialchars($farmer['phone']) ?>
                                                    </a>
                                                </div>
                                                <?php if ($farmer['email']): ?>
                                                    <div class="mt-1">
                                                        <i class="fas fa-envelope mr-1 text-primary"></i>
                                                        <a href="mailto:<?= htmlspecialchars($farmer['email']) ?>">
                                                            <?= htmlspecialchars($farmer['email']) ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        <?= htmlspecialchars($farmer['state_name'] ?? 'N/A') ?>
                                                    </span>
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <i class="fas fa-city mr-1"></i>
                                                        <?= htmlspecialchars($farmer['district_name'] ?? 'N/A') ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <span class="badge badge-success badge-lg">
                                                        <i class="fas fa-map mr-1"></i>
                                                        <?= $farmer['total_holdings'] ?? 0 ?>
                                                    </span>
                                                    <?php if ($farmer['total_area']): ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            कुल <?= number_format($farmer['total_area'], 2) ?> एकड़
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $farmer['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                    <?= $farmer['status'] === 'active' ? 'सक्रिय' : 'निष्क्रिय' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d M Y', strtotime($farmer['created_at'])) ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    ID: <?= $farmer['id'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical btn-group-sm">
                                                    <a href="/farmers/<?= $farmer['id'] ?>"
                                                       class="btn btn-outline-info btn-sm"
                                                       title="विवरण देखें">
                                                        <i class="fas fa-eye"></i> देखें
                                                    </a>
                                                    <a href="/farmers/<?= $farmer['id'] ?>/edit"
                                                       class="btn btn-outline-warning btn-sm"
                                                       title="एडिट करें">
                                                        <i class="fas fa-edit"></i> एडिट
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="deleteFarmer(<?= $farmer['id'] ?>, '<?= htmlspecialchars($farmer['name']) ?>')"
                                                            title="डिलीट करें">
                                                        <i class="fas fa-trash"></i> डिलीट
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Farmers pagination">
                                <ul class="pagination">
                                    <li class="page-item disabled">
                                        <span class="page-link">पिछला</span>
                                    </li>
                                    <li class="page-item active">
                                        <span class="page-link">1</span>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link">अगला</span>
                                    </li>
                                </ul>
                            </nav>
                        </div>
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

// Table search functionality
$(document).ready(function(){
    $("#farmersTable_filter input").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#farmersTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

<style>
.farmer-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge-lg {
    font-size: 1.1em;
    padding: 0.5em 0.8em;
}

.table td {
    vertical-align: middle;
}

.dropdown-toggle::after {
    margin-left: 0.5em;
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
