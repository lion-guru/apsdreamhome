<?php require_once 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/farmers">किसान</a></li>
                    <li class="breadcrumb-item"><a href="/farmers/<?= $farmer['id'] ?>">
                        <?= htmlspecialchars($farmer['name']) ?>
                    </a></li>
                    <li class="breadcrumb-item active" aria-current="page">एडिट करें</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Edit Farmer Form -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit mr-2"></i>किसान जानकारी एडिट करें
                    </h6>
                    <div>
                        <a href="/farmers/<?= $farmer['id'] ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye mr-1"></i>विवरण देखें
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="/farmers/<?= $farmer['id'] ?>/update" id="editFarmerForm">
                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <div class="card border-left-primary">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-user mr-2"></i>व्यक्तिगत जानकारी
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label for="name" class="form-label">
                                                पूरा नाम <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="name"
                                                   name="name"
                                                   value="<?= htmlspecialchars($farmer['name']) ?>"
                                                   required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="phone" class="form-label">
                                                मोबाइल नंबर <span class="text-danger">*</span>
                                            </label>
                                            <input type="tel"
                                                   class="form-control"
                                                   id="phone"
                                                   name="phone"
                                                   value="<?= htmlspecialchars($farmer['phone']) ?>"
                                                   required
                                                   pattern="[0-9]{10}">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="email" class="form-label">
                                                ईमेल पता
                                            </label>
                                            <input type="email"
                                                   class="form-control"
                                                   id="email"
                                                   name="email"
                                                   value="<?= htmlspecialchars($farmer['email']) ?>">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="address" class="form-label">
                                                पता <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control"
                                                      id="address"
                                                      name="address"
                                                      rows="3"
                                                      required><?= htmlspecialchars($farmer['address']) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Information -->
                            <div class="col-md-6">
                                <div class="card border-left-success">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold text-success">
                                            <i class="fas fa-map-marker-alt mr-2"></i>लोकेशन जानकारी
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label for="state_id" class="form-label">
                                                राज्य <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" id="state_id" name="state_id" required>
                                                <option value="">राज्य चुनें</option>
                                                <?php foreach ($states as $state): ?>
                                                    <option value="<?= $state['id'] ?>"
                                                            <?= $state['id'] == $farmer['state_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($state['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="district_id" class="form-label">
                                                जिला <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" id="district_id" name="district_id" required>
                                                <option value="">जिला चुनें</option>
                                                <?php foreach ($districts as $district): ?>
                                                    <option value="<?= $district['id'] ?>"
                                                            <?= $district['id'] == $farmer['district_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($district['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial Information -->
                                <div class="card border-left-warning mt-3">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold text-warning">
                                            <i class="fas fa-rupee-sign mr-2"></i>वित्तीय जानकारी
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label for="aadhar_number" class="form-label">
                                                आधार नंबर
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="aadhar_number"
                                                   name="aadhar_number"
                                                   value="<?= htmlspecialchars($farmer['aadhar_number']) ?>"
                                                   pattern="[0-9]{12}">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="pan_number" class="form-label">
                                                पैन नंबर
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="pan_number"
                                                   name="pan_number"
                                                   value="<?= htmlspecialchars($farmer['pan_number']) ?>"
                                                   style="text-transform: uppercase;">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="bank_account" class="form-label">
                                                बैंक अकाउंट नंबर
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="bank_account"
                                                   name="bank_account"
                                                   value="<?= htmlspecialchars($farmer['bank_account']) ?>">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="ifsc_code" class="form-label">
                                                IFSC कोड
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="ifsc_code"
                                                   name="ifsc_code"
                                                   value="<?= htmlspecialchars($farmer['ifsc_code']) ?>"
                                                   style="text-transform: uppercase;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save mr-2"></i>परिवर्तन सेव करें
                                </button>
                                <a href="/farmers/<?= $farmer['id'] ?>" class="btn btn-secondary btn-lg ml-3">
                                    <i class="fas fa-times mr-2"></i>रद्द करें
                                </a>
                                <button type="button" class="btn btn-danger btn-lg ml-3" onclick="deleteFarmer(<?= $farmer['id'] ?>)">
                                    <i class="fas fa-trash mr-2"></i>किसान डिलीट करें
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- State-District AJAX Script -->
<script>
$(document).ready(function(){
    $('#state_id').change(function(){
        var stateId = $(this).val();
        if(stateId) {
            $.ajax({
                url: '/ajax/get-districts/' + stateId,
                type: 'GET',
                success: function(data) {
                    $('#district_id').html('<option value="">जिला चुनें</option>');
                    $.each(data, function(key, value) {
                        $('#district_id').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                    });
                }
            });
        } else {
            $('#district_id').html('<option value="">पहले राज्य चुनें</option>');
        }
    });
});
</script>

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
</script>

<style>
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e3e6f0;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn {
    border-radius: 8px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    border: none;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #ee5a24 0%, #ff6b6b 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
