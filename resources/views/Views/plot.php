



<?php
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");
$db_host = 'localhost';
$db_username = 'abhay3007';
$db_password = 'abhay@128125'; // SECURITY: Sensitive information partially masked
$db_name = 'apsdreamhome';

// Connect to database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve plot categories
$query = "SELECT * FROM plot_categories";
$result = $conn->query($query);
$plot_categories = array();
while ($row = $result->fetch_assoc()) {
    $plot_categories[] = $row['category_name'];
}

// Retrieve plots
$query = "SELECT * FROM plots";
$result = $conn->query($query);
$plots = array();
while ($row = $result->fetch_assoc()) {
    $plots[] = $row;
}

// Close connection
$conn->close();

// Display plot information
?>
<table border="1" cellpadding="5" cellspacing="0">
  <tr>
    <td height="42" colspan="4">
      <span class="font_normal_violet">
        <input type="radio" name="plot_category" value="ALL" checked="checked" /> ALL
        <?php foreach ($plot_categories as $category) { ?>
          <input type="radio" name="plot_category" value="<?php echo $category;?>" /> <?php echo $category;?>
        <?php } ?>
      </span>
    </td>
  </tr>
  <tr>
    <td height="30" colspan="11" bgcolor="#CCCCCC">
      <span class="font_normal_violet">Plot ID</span>
      <span class="font_normal_violet">Status</span>
      <span class="font_normal_violet">Breadth</span>
      <span class="font_normal_violet">Length</span>
      <span class="font_normal_violet">Total Size</span>
      <span class="font_normal_violet">Description</span>
    </td>
  </tr>
  <?php foreach ($plots as $plot) { ?>
  <tr>
    <td height="30" bgcolor="<?php echo get_plot_status_class($plot['status']);?>"><?php echo $plot['plot_id'];?></td>
    <td><img src="<?php echo get_plot_status_image($plot['status']);?>" alt="<?php echo $plot['status'];?>" /></td>
    <td><?php echo $plot['breadth'];?></td>
    <td><?php echo $plot['length'];?></td>
    <td><?php echo $plot['total_size'];?></td>
    <td><?php echo $plot['description'];?></td>
  </tr>
  <?php } ?>
</table>

<?php
// Function to get plot status class
function get_plot_status_class($status) {
  switch ($status) {
    case 'available':
      return '#C6F7D0'; // green
    case 'booked':
      return '#F7DC6F'; // yellow
    case 'hold':
      return '#FFC080'; // orange
    case 'sold':
      return '#FF69B4'; // red
    default:
      return '#CCCCCC'; // gray
  }
}

// Function to get plot status image
function get_plot_status_image($status) {
  switch ($status) {
    case 'available':
      return 'available.gif';
    case 'booked':
      return 'booked.gif';
    case 'hold':
      return 'hold.gif';
    case 'sold':
      return 'sold.gif';
    default:
      return 'unknown.gif';
  }
}
?>
<?php
// Configuration
$project_name = "Suryoday Colony";
$block_code = "All";
$plot_categories = array("ALL", "Available", "Booked", "Hold", "Sold Out");

// Plots array
$plots = array(
    array("id" => "B-56", "status" => "available", "breadth" => 25, "length" => 40, "total_size" => 1000, "description" => "Intermittent Plot West Phase"),
    array("id" => "A506A", "status" => "booked", "breadth" => 0, "length" => 0, "total_size" => 1041, "description" => "Intermittent Plot North Phase"),
    array("id" => "B109", "status" => "available", "breadth" => 25, "length" => 40, "total_size" => 1000, "description" => "Intermittent Plot East Phase"),
    array("id" => "B108", "status" => "sold", "breadth" => 16, "length" => 50, "total_size" => 800, "description" => "Intermittent Plot East Phase"),
    array("id" => "B107A", "status" => "sold", "breadth" => 20, "length" => 30, "total_size" => 600, "description" => "Intermittent Plot East Phase"),
    array("id" => "B107B", "status" => "sold", "breadth" => 20, "length" => 30, "total_size" => 600, "description" => "Intermittent Plot East Phase"),
    array("id" => "B78", "status" => "sold", "breadth" => 25, "length" => 40, "total_size" => 1000, "description" => "Intermittent Plot West Phase"),
    array("id" => "B110", "status" => "booked", "breadth" => 25, "length" => 40, "total_size" => 1000, "description" => "Intermittent Plot East Phase"),
    array("id" => "A37", "status" => "sold", "breadth" => 40, "length" => 100, "total_size" => 4000, "description" => "Intermittent Plot East Phase"),
    array("id" => "A44", "status" => "available", "breadth" => 160, "length" => 50, "total_size" => 8000, "description" => "Intermittent Plot West Phase")
);

// The functions get_plot_status_class and get_plot_status_image are already defined above
// Using the existing functions for the rest of the code

// HTML
?>

<table width="970" border="0">
  <tr>
    <td colspan="4"><span class="font_normal">Project Name:</span><font color="#800080" style="font-size:14px; font-weight:bold;"><?php echo $project_name;?></font>
      <input name="pr" type="hidden" id="pr" value="<?php echo $project_name;?>" />
    </td>
    <td colspan="2"><span class="font_normal">Block Code:</span><font color="#800080" style="font-size:14px; font-weight:bold;"><?php echo $block_code;?></font></td>
    <td colspan="5"><span class="font_normal">Plot Category/Dimension:</span><font color="#800080" style="font-size:14px; font-weight:bold;"></font></td>
  </tr>
  <tr>
    <td height="21" colspan="11"><img src="./admin/<?php echo get_asset_url('back_img.jpg', 'images'); ?>" onclick="history.back(-1)"/></td>
  </tr>
   <tr>
    <td height="42" colspan="4"><span class="font_normal_violet">
      <input type="radio" name="plot_category" value="ALL" checked="checked" /> ALL
      <?php foreach ($plot_categories as $category) { ?>
        <input type="radio" name="plot_category" value="<?php echo $category;?>" /> <?php echo $category;?>
      <?php } ?>
    </span></td>
  </tr>
  <tr>
    <td height="30" colspan="11" bgcolor="#CCCCCC">
      <span class="font_normal_violet">Plot ID</span>
      <span class="font_normal_violet">Status</span>
      <span class="font_normal_violet">Breadth</span>
      <span class="font_normal_violet">Length</span>
      <span class="font_normal_violet">Total Size</span>
      <span class="font_normal_violet">Description</span>
    </td>
  </tr>
  <?php foreach ($plots as $plot) { ?>
  <tr>
    <td height="30" bgcolor="<?php echo get_plot_status_class($plot['status']);?>"><?php echo $plot['id'];?></td>
    <td><img src="<?php echo get_plot_status_image($plot['status']);?>" alt="<?php echo $plot['status'];?>" /></td>
    <td><?php echo $plot['breadth'];?></td>
    <td><?php echo $plot['length'];?></td>
    <td><?php echo $plot['total_size'];?></td>
    <td><?php echo $plot['description'];?></td>
  </tr>
  <?php } ?>
</table>
