<?php
$crm_menu = '<li class="submenu">
    <a href="#"><i class="fe fe-users"></i> <span> CRM</span> <span class="menu-arrow"></span></a>
    <ul style="display: none;">
        <li><a class="<?php echo basename($_SERVER[\'PHP_SELF\']) == \'customer_management.php\' ? \'active\' : \'\'; ?>" href="customer_management.php">Customers</a></li>
        <li><a class="<?php echo basename($_SERVER[\'PHP_SELF\']) == \'booking.php\' ? \'active\' : \'\'; ?>" href="booking.php">Bookings</a></li>
        <li><a class="<?php echo basename($_SERVER[\'PHP_SELF\']) == \'aps_custom_report.php\' ? \'active\' : \'\'; ?>" href="aps_custom_report.php">Reports</a></li>
    </ul>
</li>';
?>
