<?php
require_once 'core/init.php';

if (!verifyCSRFToken()) {
    die("Security validation failed");
}

if (isset($_GET['id'])) {
    $db = \App\Core\App::database();
    $lead_id = (int)$_GET['id'];
    $lead = $db->fetchOne("SELECT * FROM leads WHERE id = ?", [$lead_id]);

    if ($lead) {
        ?>
        <div class="modal-header bg-gradient-primary text-white">
            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Lead: <?php echo h($lead['name']); ?></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form method="post" action="update_lead.php" class="needs-validation" novalidate>
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input class="form-control" type="text" id="edit_name" name="name" value="<?php echo h($lead['name']); ?>" required placeholder="Name">
                            <label for="edit_name">Name *</label>
                            <div class="invalid-feedback">Please enter a name.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input class="form-control" type="email" id="edit_email" name="email" value="<?php echo h($lead['email']); ?>" placeholder="Email">
                            <label for="edit_email">Email</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input class="form-control" type="text" id="edit_phone" name="phone" value="<?php echo h($lead['phone']); ?>" placeholder="Phone">
                            <label for="edit_phone">Phone</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select" id="edit_source" name="source">
                                <?php
                                $sources = array('Website', 'Referral', 'Advertisement', 'Social Media', 'Direct', 'Other');
                                foreach ($sources as $s) {
                                    $selected = (strcasecmp($lead['source'], $s) == 0) ? 'selected' : '';
                                    echo "<option value='" . h($s) . "' $selected>" . h($s) . "</option>";
                                }
                                ?>
                            </select>
                            <label for="edit_source">Source</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select" id="edit_status" name="status">
                                <?php
                                $statuses = array('New', 'Contacted', 'Qualified', 'Lost', 'Converted');
                                foreach ($statuses as $s) {
                                    $selected = (strcasecmp($lead['status'], $s) == 0) ? 'selected' : '';
                                    echo "<option value='" . h($s) . "' $selected>" . h($s) . "</option>";
                                }
                                ?>
                            </select>
                            <label for="edit_status">Status</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select" id="edit_assigned_to" name="assigned_to">
                                <option value="">Not Assigned</option>
                                <?php
                                $admins = $db->fetchAll("SELECT id, auser as name FROM admin ORDER BY auser");
                                foreach ($admins as $row) {
                                    $selected = ($lead['assigned_to'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='" . h($row['id']) . "' $selected>" . h($row['name']) . "</option>";
                                }
                                ?>
                            </select>
                            <label for="edit_assigned_to">Assigned To</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea class="form-control" id="edit_address" name="address" style="height: 80px" placeholder="Address"><?php echo h($lead['address']); ?></textarea>
                            <label for="edit_address">Address</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea class="form-control" id="edit_notes" name="notes" style="height: 100px" placeholder="Notes"><?php echo h($lead['notes']); ?></textarea>
                            <label for="edit_notes">Notes</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-0 pb-0 pt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Update Lead</button>
                </div>
            </form>
        </div>
        <?php
    } else {
        echo "<div class='modal-body text-center py-5 text-danger'><i class='fas fa-exclamation-circle fa-3x mb-3'></i><p>Lead not found!</p></div>";
    }
} else {
    echo "<div class='modal-body text-center py-5 text-danger'><i class='fas fa-exclamation-circle fa-3x mb-3'></i><p>Invalid request!</p></div>";
}
?>

