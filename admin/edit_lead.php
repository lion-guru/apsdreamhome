<?php
include('../includes/templates/dynamic_header.php');
include('src/Database/Database.php');

if (isset($_GET['id'])) {
    $lead_id = intval($_GET['id']);
    $query = "SELECT * FROM leads WHERE lead_id = $lead_id";
    $result = mysqli_query($con, $query);
    $lead = mysqli_fetch_assoc($result);

    if ($lead) {
        ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <style>
            .form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
            .form-floating input, .form-floating select, .form-floating textarea { padding-left: 2.5rem; }
        </style>
        <div class="modal-header">
            <h5 class="modal-title">लीड संपादित करें</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form method="post" action="update_lead.php" class="needs-validation" novalidate>
                <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <input class="form-control" type="text" id="name" name="name" value="<?php echo htmlspecialchars($lead['name']); ?>" required placeholder="नाम">
                            <label for="name"><i class="fa fa-user"></i> नाम <span class="text-danger">*</span></label>
                            <div class="invalid-feedback">कृपया नाम दर्ज करें।</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($lead['email']); ?>" placeholder="ईमेल">
                            <label for="email"><i class="fa fa-envelope"></i> ईमेल</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <input class="form-control" type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($lead['phone']); ?>" placeholder="फोन">
                            <label for="phone"><i class="fa fa-phone"></i> फोन</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <select class="form-select" id="source" name="source">
                                <?php
                                $sources = array('website' => 'वेबसाइट', 'referral' => 'रेफरल', 'advertisement' => 'विज्ञापन', 'social_media' => 'सोशल मीडिया', 'other' => 'अन्य');
                                foreach ($sources as $value => $label) {
                                    $selected = ($lead['source'] == $value) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($value) . "' $selected>" . htmlspecialchars($label) . "</option>";
                                }
                                ?>
                            </select>
                            <label for="source"><i class="fa fa-globe"></i> स्रोत</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <select class="form-select" id="status" name="status">
                                <?php
                                $statuses = array(
                                    'new' => 'नई',
                                    'contacted' => 'संपर्क किया गया',
                                    'qualified' => 'योग्य',
                                    'unqualified' => 'अयोग्य'
                                );
                                foreach ($statuses as $value => $label) {
                                    $selected = ($lead['status'] == $value) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($value) . "' $selected>" . htmlspecialchars($label) . "</option>";
                                }
                                ?>
                            </select>
                            <label for="status"><i class="fa fa-flag"></i> स्थिति</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">चुनें</option>
                                <?php
                                $query = "SELECT id, firstname, lastname FROM users WHERE user_type IN ('admin', 'agent') ORDER BY firstname";
                                $result = mysqli_query($con, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $selected = ($lead['assigned_to'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>{$row['firstname']} {$row['lastname']}</option>";
                                }
                                ?>
                            </select>
                            <label for="assigned_to"><i class="fa fa-user-tie"></i> असाइन करें</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating position-relative">
                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="पता" style="height: 70px"><?php echo htmlspecialchars($lead['address']); ?></textarea>
                            <label for="address"><i class="fa fa-map-marker-alt"></i> पता</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating position-relative">
                            <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="नोट्स" style="height: 100px"><?php echo htmlspecialchars($lead['notes']); ?></textarea>
                            <label for="notes"><i class="fa fa-sticky-note"></i> नोट्स</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill"><i class="fa fa-save"></i> सेव करें</button>
                </div>
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        (() => {
          'use strict';
          const forms = document.querySelectorAll('.needs-validation');
          Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
              if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
              }
              form.classList.add('was-validated');
            }, false);
          });
        })();
        </script>
        <?php
    } else {
        echo "<div class='alert alert-danger'>लीड नहीं मिली!</div>";
    }
} else {
    echo "<div class='alert alert-danger'>अमान्य अनुरोध!</div>";
}
include('../includes/templates/new_footer.php');
?>