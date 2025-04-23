<?php
include('header.php');
include('db_connection.php');

// Handle form submission for new opportunity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_opportunity'])) {
    $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : null;
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $value = floatval($_POST['value']);
    $stage = mysqli_real_escape_string($con, $_POST['stage']);
    $probability = intval($_POST['probability']);
    $expected_close_date = mysqli_real_escape_string($con, $_POST['expected_close_date']);
    $property_interest = isset($_POST['property_interest']) ? intval($_POST['property_interest']) : null;
    $notes = mysqli_real_escape_string($con, $_POST['notes']);
    $assigned_to = isset($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;

    $query = "INSERT INTO opportunities (lead_id, title, value, stage, probability, expected_close_date, property_interest, notes, assigned_to) 
              VALUES (" . ($lead_id ? $lead_id : 'NULL') . ", '$title', $value, '$stage', $probability, '$expected_close_date', " .
              ($property_interest ? $property_interest : 'NULL') . ", '$notes', " . ($assigned_to ? $assigned_to : 'NULL') . ")"; 

    if (mysqli_query($con, $query)) {
        echo "<div class='alert alert-success'>अवसर सफलतापूर्वक जोड़ा गया!</div>";
    } else {
        echo "<div class='alert alert-danger'>अवसर जोड़ने में त्रुटि: " . htmlspecialchars(mysqli_error($con)) . "</div>";
    }
}

// Handle opportunity deletion
if (isset($_GET['delete'])) {
    $opportunity_id = intval($_GET['delete']);
    $query = "DELETE FROM opportunities WHERE opportunity_id = $opportunity_id";
    if (mysqli_query($con, $query)) {
        echo "<div class='alert alert-success'>अवसर सफलतापूर्वक हटा दिया गया!</div>";
    } else {
        echo "<div class='alert alert-danger'>अवसर हटाने में त्रुटि: " . htmlspecialchars(mysqli_error($con)) . "</div>";
    }
}
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">अवसर मैनेजमेंट</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">डैशबोर्ड</a></li>
                        <li class="breadcrumb-item active">अवसर</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_opportunity"><i class="fa fa-plus"></i> नया अवसर जोड़ें</a>
                </div>
            </div>
        </div>

        <!-- Opportunities List -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table mb-0 datatable">
                        <thead>
                            <tr>
                                <th>शीर्षक</th>
                                <th>लीड</th>
                                <th>मूल्य</th>
                                <th>स्टेज</th>
                                <th>संभावना</th>
                                <th>प्रॉपर्टी</th>
                                <th>असाइन किया गया</th>
                                <th>अपेक्षित समापन तिथि</th>
                                <th class="text-right">कार्रवाई</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT o.*, l.name as lead_name, u.firstname, u.lastname, p.title as property_title 
                                      FROM opportunities o 
                                      LEFT JOIN leads l ON o.lead_id = l.lead_id 
                                      LEFT JOIN users u ON o.assigned_to = u.id 
                                      LEFT JOIN properties p ON o.property_interest = p.id
                                      ORDER BY o.expected_close_date ASC";
                            $result = mysqli_query($con, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $stage_labels = array(
                                    'prospecting' => 'प्रॉस्पेक्टिंग',
                                    'qualification' => 'क्वालिफिकेशन',
                                    'needs_analysis' => 'आवश्यकता विश्लेषण',
                                    'proposal' => 'प्रस्ताव',
                                    'negotiation' => 'बातचीत',
                                    'closed_won' => 'जीता हुआ',
                                    'closed_lost' => 'खोया हुआ'
                                );
                                
                                echo "<tr>
                                    <td>{$row['title']}</td>
                                    <td>{$row['lead_name']}</td>
                                    <td>₹" . number_format($row['value'], 2) . "</td>
                                    <td>{$stage_labels[$row['stage']]}</td>
                                    <td>{$row['probability']}%</td>
                                    <td>{$row['property_title']}</td>
                                    <td>" . ($row['firstname'] ? "{$row['firstname']} {$row['lastname']}" : 'Unassigned') . "</td>
                                    <td>" . date('Y-m-d', strtotime($row['expected_close_date'])) . "</td>
                                    <td class='text-right'>
                                        <div class='dropdown dropdown-action'>
                                            <a href='#' class='action-icon dropdown-toggle' data-toggle='dropdown' aria-expanded='false'><i class='material-icons'>more_vert</i></a>
                                            <div class='dropdown-menu dropdown-menu-right'>
                                                <a class='dropdown-item' href='#' data-toggle='modal' data-target='#edit_opportunity' data-id='{$row['opportunity_id']}'><i class='fa fa-pencil m-r-5'></i> संपादित करें</a>
                                                <a class='dropdown-item' href='#' data-toggle='modal' data-target='#delete_opportunity' data-id='{$row['opportunity_id']}'><i class='fa fa-trash-o m-r-5'></i> हटाएं</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Opportunity Modal -->
    <div id="add_opportunity" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">नया अवसर जोड़ें</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>शीर्षक <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="title" required>
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
                                            echo "<option value='{$row['lead_id']}'>{$row['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>मूल्य <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" step="0.01" name="value" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>स्टेज</label>
                                    <select class="form-control" name="stage">
                                        <option value="prospecting">प्रॉस्पेक्टिंग</option>
                                        <option value="qualification">क्वालिफिकेशन</option>
                                        <option value="needs_analysis">आवश्यकता विश्लेषण</option>
                                        <option value="proposal">प्रस्ताव</option>
                                        <option value="negotiation">बातचीत</option>
                                        <option value="closed_won">जीता हुआ</option>
                                        <option value="closed_lost">खोया हुआ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>संभावना (%)</label>
                                    <input class="form-control" type="number" min="0" max="100" name="probability">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>अपेक्षित समापन तिथि</label>
                                    <input class="form-control" type="date" name="expected_close_date">
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
                                            echo "<option value='{$row['id']}'>{$row['title']}</option>";
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
                                            echo "<option value='{$row['id']}'>{$row['firstname']} {$row['lastname']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>नोट्स</label>
                                    <textarea class="form-control" name="notes" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn" name="add_opportunity" type="submit">सबमिट करें</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Opportunity Modal -->
    <div id="edit_opportunity" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!-- Edit form will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <!-- Delete Opportunity Modal -->
    <div class="modal custom-modal fade" id="delete_opportunity" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>अवसर हटाएं</h3>
                        <p>क्या आप वाकई इस अवसर को हटाना चाहते हैं?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <div class="row">
                            <div class="col-6">
                                <a href="" class="btn btn-primary continue-btn">हटाएं</a>
                            </div>
                            <div class="col-6">
                                <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-primary cancel-btn">रद्द करें</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize datatable
    $('.datatable').DataTable();

    // Handle delete confirmation
    $('#delete_opportunity').on('show.bs.modal', function (e) {
        var opportunityId = $(e.relatedTarget).data('id');
        $('.continue-btn').attr('href', 'opportunities.php?delete=' + opportunityId);
    });

    // Handle edit modal loading
    $('#edit_opportunity').on('show.bs.modal', function (e) {
        var opportunityId = $(e.relatedTarget).data('id');
        $.ajax({
            url: 'edit_opportunity.php',
            type: 'GET',
            data: {id: opportunityId},
            success: function(response) {
                $('#edit_opportunity .modal-content').html(response);
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>