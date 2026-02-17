<?php
require_once __DIR__ . '/core/init.php';
include("../includes/templates/dynamic_header.php");

$db = \App\Core\App::database();

if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $opportunity_id = intval($_GET['id']);
    $opportunity = $db->fetchOne("SELECT * FROM opportunities WHERE opportunity_id = :opportunity_id", ['opportunity_id' => $opportunity_id]);

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
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="opportunity_id" value="<?php echo $opportunity_id; ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>शीर्षक <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="title" value="<?php echo h($opportunity['title']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>लीड</label>
                            <select class="form-control" name="lead_id">
                                <option value="">चुनें</option>
                                <?php
                                $query = "SELECT id, name FROM leads ORDER BY name";
                                $leads = $db->fetchAll($query);
                                foreach ($leads as $row) {
                                    $selected = ($opportunity['lead_id'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='".(int)$row['id']."' $selected>".h($row['name'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>मूल्य <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" step="0.01" name="value" value="<?php echo h($opportunity['value']); ?>" required>
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
                                    echo "<option value='".h($value)."' $selected>".h($label)."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>संभावना (%)</label>
                            <input class="form-control" type="number" min="0" max="100" name="probability" value="<?php echo h($opportunity['probability']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>अपेक्षित समापन तिथि</label>
                            <input class="form-control" type="date" name="expected_close_date" value="<?php echo h($opportunity['expected_close_date']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>प्रॉपर्टी</label>
                            <select class="form-control" name="property_interest">
                                <option value="">चुनें</option>
                                <?php
                                $query = "SELECT id, title FROM properties ORDER BY title";
                                $properties = $db->fetchAll($query);
                                foreach ($properties as $row) {
                                    $selected = ($opportunity['property_interest'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='".(int)$row['id']."' $selected>".h($row['title'])."</option>";
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
                                $query = "SELECT id, auser as name FROM admin ORDER BY auser";
                                $admins = $db->fetchAll($query);
                                foreach ($admins as $row) {
                                    $selected = ($opportunity['assigned_to'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='".(int)$row['id']."' $selected>".h($row['name'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>नोट्स</label>
                            <textarea class="form-control" name="notes" rows="4"><?php echo h($opportunity['notes']); ?></textarea>
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
?>
<?php include("../includes/templates/new_footer.php");?>

