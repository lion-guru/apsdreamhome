<?php
session_start();
require("config.php");

if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}
?>
<?php include("../includes/templates/dynamic_header.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Kissan Land Management | Land Records</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
</head>
<body>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Kissan Land Records</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Land Records</li>
                        </ul>
                    </div>
                    <div class="col-auto">
                        <form class="form-inline">
                            <input type="search" class="form-control" placeholder="Search...">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <a href="Kissan.php" class="btn btn-success">Add New Record</a>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">List of Kissan Land Records</h4>
                            <?php if (isset($_GET['msg'])) echo htmlspecialchars($_GET['msg']); ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Farmer Name</th>
                                            <th>Adhaar Number</th>
                                            <th>Site Name</th>
                                            <th>Total Area</th>
                                            <th>Gata A</th>
                                            <th>Gata A Area</th>																						<th>Gata B</th>                                            <th>Gata B Area</th>																						<th>Gata C</th>                                            <th>Gata C Area</th>																						<th>Gata D</th>                                            <th>Gata D Area</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM kissan_master");
                                        $cnt = 1;
                                        while ($row = mysqli_fetch_assoc($query)) {
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cnt); ?></td>
                                                <td><?php echo htmlspecialchars($row['k_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['k_adhaar']); ?></td>												<?php 														$site_id = isset($row['site_id']) ? (int)$row['site_id'] : 0;														$gata_a = isset($row['gata_a']) ? (int)$row['gata_a'] : 0;														$gata_b = isset($row['gata_b']) ? (int)$row['gata_b'] : 0;														$gata_c = isset($row['gata_c']) ? (int)$row['gata_c'] : 0;														$gata_d = isset($row['gata_d']) ? (int)$row['gata_d'] : 0;																												$site_qurey = "select * from site_master where site_id = $site_id";														$site_result=mysqli_query($con,$site_qurey);														while($row_site=mysqli_fetch_array($site_result))														{																														$site_name = $row_site['site_name'];														}																												$gata_qurey_a = "select * from gata_master where gata_id = $gata_a";														$gata_result_a=mysqli_query($con,$gata_qurey_a);														while($row_gata_a=mysqli_fetch_array($gata_result_a))														{															$gata_no_a = $row_gata_a['gata_no'];														}														$gata_qurey_b = "select * from gata_master where gata_id = $gata_b";														$gata_result_b=mysqli_query($con,$gata_qurey_b);														while($row_gata_b=mysqli_fetch_array($gata_result_b))														{															$gata_no_b = $row_gata_b['gata_no'];														}														$gata_qurey_c = "select * from gata_master where gata_id = $gata_c";														$gata_result_c=mysqli_query($con,$gata_qurey_c);														while($row_gata_c=mysqli_fetch_array($gata_result_c))														{															$gata_no_c = $row_gata_c['gata_no'];														}														$gata_qurey_d = "select * from gata_master where gata_id = $gata_d";														$gata_result_d=mysqli_query($con,$gata_qurey_d);														while($row_gata_d=mysqli_fetch_array($gata_result_d))														{															$gata_no_d = $row_gata_d['gata_no'];														}																																								?>
                                                <td><?php echo htmlspecialchars($site_name); ?></td>
                                                <td><?php echo htmlspecialchars($row['area'])." Sqft."; ?></td>
                                                <td><?php echo htmlspecialchars($gata_no_a); ?></td>
                                                <td><?php echo htmlspecialchars($row['area_gata_a'])." Sqft."; ?></td>																								<td><?php echo htmlspecialchars($gata_no_b); ?></td>                                                <td><?php echo htmlspecialchars($row['area_gata_b'])." Sqft."; ?></td>																								<td><?php echo htmlspecialchars($gata_no_c); ?></td>                                                <td><?php echo htmlspecialchars($row['area_gata_c'])." Sqft."; ?></td>																								<td><?php echo htmlspecialchars($gata_no_d); ?></td>                                                <td><?php echo htmlspecialchars($row['area_gata_d'])." Sqft."; ?></td>                                           
                                                <td>
                                                    <button class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo htmlspecialchars($row['id']); ?>)">Edit</button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteRecord(<?php echo htmlspecialchars($row['id']); ?>)">Delete</button>
                                                </td>
                                            </tr>
                                        <?php
                                            $cnt++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Land Record</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" name="id" id="recordId">
                        <div class="form-group">
                            <label for="farmer_name">Farmer Name:</label>
                            <input type="text" class="form-control" id="farmer_name" name="farmer_name" required>
                        </div>
                        <div class="form-group">
                            <label for="farmer_mobile">Farmer Mobile:</label>
                            <input type="text" class="form-control" id="farmer_mobile" name="farmer_mobile" required>
                        </div>
                        <div class="form-group">
                            <label for="bank_name">Bank Name:</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                        </div>
                        <div class="form-group">
                            <label for="account_number">Account Number:</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" required>
                        </div>
                        <div class="form-group">
                            <label for="bank_ifsc">Bank IFSC:</label>
                            <input type="text" class="form-control" id="bank_ifsc" name="bank_ifsc" required>
                        </div>
                        <div class="form-group">
                            <label for="site_name">Site Name:</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" required>
                        </div>
                        <div class="form-group">
                            <label for="land_area">Land Area (in decmil):</label>
                            <input type="number" class="form-control" id="land_area" name="land_area" required>
                        </div>
                        <div class="form-group">
                            <label for="total_land_price">Total Land Price:</label>
                            <input type="number" class="form-control" id="total_land_price" name="total_land_price" required>
                        </div>
                        <div class="form-group">
                            <label for="total_paid_amount">Total Paid Amount:</label>
                            <input type="number" class="form-control" id="total_paid_amount" name="total_paid_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="amount_pending">Amount Pending:</label>
                            <input type="number" class="form-control" id="amount_pending" name="amount_pending" required>
                        </div>
                        <div class="form-group">
                            <label for="gata_number">Gata Number:</label>
                            <input type="text" class="form-control" id="gata_number" name="gata_number" required>
                        </div>
                        <div class="form-group">
                            <label for="district">District:</label>
                            <input type="text" class="form-control" id="district" name="district" required>
                        </div>
                        <div class="form-group">
                            <label for="tehsil">Tehsil:</label>
                            <input type="text" class="form-control" id="tehsil" name="tehsil" required>
                        </div>
                        <div class="form-group">
                            <label for="city">City:</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="gram">Gram:</label>
                            <input type="text" class="form-control" id="gram" name="gram" required>
                        </div>
                        <div class="form-group">
                            <label for="land_manager_name">Land Manager Name:</label>
                            <input type="text" class="form-control" id="land_manager_name" name="land_manager_name" required>
                        </div>
                        <div class="form-group">
                            <label for="land_manager_mobile">Land Manager Mobile:</label>
                            <input type="text" class="form-control" id="land_manager_mobile" name="land_manager_mobile" required>
                        </div>
                        <div class="form-group">
                            <label for="agreement_status">Agreement Status:</label>
                            <input type="text" class="form-control" id="agreement_status" name="agreement_status" required>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="updateRecord()">Update Record</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(id) {
            $.ajax({
                url: 'get_land_record.php',
                type: 'GET',
                data: { id: id },
                success: function(response) {
                    const record = JSON.parse(response);
                    $('#recordId').val(record.id);
                    $('#farmer_name').val(record.farmer_name);
                    $('#farmer_mobile').val(record.farmer_mobile);
                    $('#bank_name').val(record.bank_name);
                    $('#account_number').val(record.account_number);
                    $('#bank_ifsc').val(record.bank_ifsc);
                    $('#site_name').val(record.site_name);
                    $('#land_area').val(record.land_area);
                    $('#total_land_price').val(record.total_land_price);
                    $('#total_paid_amount').val(record.total_paid_amount);
                    $('#amount_pending').val(record.amount_pending);
                    $('#gata_number').val(record.gata_number);
                    $('#district').val(record.district);
                    $('#tehsil').val(record.tehsil);
                    $('#city').val(record.city);
                    $('#gram').val(record.gram);
                    $('#land_manager_name').val(record.land_manager_name);
                    $('#land_manager_mobile').val(record.land_manager_mobile);
                    $('#agreement_status').val(record.agreement_status);
                    $('#editModal').modal('show');
                }
            });
        }

        function updateRecord() {
            const formData = $('#editForm').serialize();
            $.ajax({
                url: 'update_land_record.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    alert('Record updated successfully.');
                    location.reload(); // Reload the page after update
                },
                error: function() {
                    alert('Error updating record.');
                }
            });
        }

        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete this record?')) {
                $.ajax({
                    type: 'POST',
                    url: 'delete_land_record.php',
                    data: { id: id },
                    success: function(response) {
                        const result = JSON.parse(response);
                        alert(result.message);
                        if (result.status === 'success') {
                            location.reload(); // Reload the page on success
                        }
                    },
                    error: function() {
                        alert('Error deleting record.');
                    }
                });
            }
        }
    </script>
<?php include("../includes/templates/new_footer.php");?>
</body>
</html>
