<?php
include("../includes/templates/dynamic_header.php");
include('db_connection.php');

if (isset($_GET['id'])) {
    $opportunity_id = intval($_GET['id']);
    $query = "SELECT * FROM opportunities WHERE opportunity_id = $opportunity_id";
    $result = mysqli_query($con, $query);
    $opportunity = mysqli_fetch_assoc($result);

    if ($opportunity) {
        ?>
        <div class="modal-header">
            <h5 class="modal-title">अवसर संपादित करें</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form method="post" action="update_opportunity.php">
                <input type="hidden" name="opportunity_id" value="<?php echo $opportunity_id; ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>शीर्षक <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="title" value="<?php echo htmlspecialchars($opportunity['title']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>लीड</label>
                            <select class="form-control" name="lead_id">
                                <option value="">चुनें</option>
                                <?php
                                $query = "SELECT lead_id, name FROM leads ORDER BY name";
                                $result = mysqli_query($con, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $selected = ($opportunity['lead_id'] == $row['lead_id']) ? 'selected' : '';
                                    echo "<option value='{$row['lead_id']}' $selected>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>मूल्य <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" step="0.01" name="value" value="<?php echo htmlspecialchars($opportunity['value']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>स्टेज</label>
                            <select class="form-control" name="stage">
                                <?php
                                $stages = array(
                                    'prospecting' => 'प्रॉस्पेक्टिंग',
                                    'qualification' => 'क्वालिफिकेशन',
                                    'needs_analysis' => 'आवश्यकता विश्लेषण',
                                    'proposal' => 'प्रस्ताव',
                                    'negotiation' => 'बातचीत',
                                    'closed_won' => 'जीता हुआ',
                                    'closed_lost' => 'खोया हुआ'
                                );
                                foreach ($stages as $value => $label) {
                                    $selected = ($opportunity['stage'] == $value) ? 'selected' : '';
                                    echo "<option value='$value' $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>संभावना (%)</label>
                            <input class="form-control" type="number" min="0" max="100" name="probability" value="<?php echo htmlspecialchars($opportunity['probability']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>अपेक्षित समापन तिथि</label>
                            <input class="form-control" type="date" name="expected_close_date" value="<?php echo htmlspecialchars($opportunity['expected_close_date']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>प्रॉपर्टी</label>
                            <select class="form-control" name="property_interest">
                                <option value="">चुनें</option>
                                <?php
                                $query = "SELECT id, title FROM properties ORDER BY title";
                                $result = mysqli_query($con, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $selected = ($opportunity['property_interest'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>{$row['title']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>असाइन करें</label>
                            <select class="form-control" name="assigned_to">
                                <option value="">चुनें</option>
                                <?php
                                $query = "SELECT id, firstname, lastname FROM users WHERE user_type IN ('admin', 'agent') ORDER BY firstname";
                                $result = mysqli_query($con, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $selected = ($opportunity['assigned_to'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>{$row['firstname']} {$row['lastname']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>नोट्स</label>
                            <textarea class="form-control" name="notes" rows="4"><?php echo htmlspecialchars($opportunity['notes']); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="submit-section">
                    <button class="btn btn-primary submit-btn" type="submit">अपडेट करें</button>
                </div>
            </form>
        </div>
        <?php
    } else {
        echo "<div class='alert alert-danger'>अवसर नहीं मिला!</div>";
    }
} else {
    echo "<div class='alert alert-danger'>अमान्य अनुरोध!</div>";
}
if ($stmt->execute()) {
    header("Location: opportunities.php?msg=".urlencode('Opportunity updated successfully.'));
    exit();
} else {
    echo "Error: " . htmlspecialchars($stmt->error);
}
?>
<?php include("../includes/templates/new_footer.php");?>