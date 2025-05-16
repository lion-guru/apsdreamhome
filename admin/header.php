<?php
// Configuration and session management is now handled in config.php
require("config.php");

// Check if the session user is set
if (!isset($_SESSION['auser'])) {
    header("Location: index.php"); // Redirect to index.php if not logged in
    exit(); // Ensure no further code is executed
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Homes</title>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.css">
    <style>
 /* Hide the logo on mobile view */
@media (max-width: 767px) {
    .logo {
        display: none;
    }
}   
 
    
    
        .sidebar {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100vh;
    background-color: #337ab7; /* Change this to your preferred color */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1;
    transform: translateX(0);
    transition: transform 0.2s ease-in-out;
}

        .sidebar.active {
            transform: translateX(0);
            display: block;
        }

        .sidebar-inner {
            padding: 20px;
        }

        .sidebar-menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sidebar-menu ul li {
            margin-bottom: 10px;
        }

        .sidebar-menu ul li a {
            color: #fff; /* Change this to your preferred color */
            text-decoration: none;
        }

        .sidebar-menu ul li a:hover {
            color: #ccc; /* Change this to your preferred color */
        }
         .toggle-btn {
            margin-right: auto; /* Pushes the button to the left */
            cursor: pointer;
            color: #fff; /* Change color if needed */
        }
        
        
      
/* Add this to your CSS file */

/* Style for the sidebar and page content on large screens */
@media (min-width: 1200px) {
    .sidebar {
        width: 250px;
    }
    .page-content {
        margin-left: 250px;
    }
}

/* Style for the sidebar and page content on medium screens */
@media (min-width: 768px) and (max-width: 1199px) {
    .sidebar {
        width: 200px;
    }
    .page-content {
        margin-left: 200px;
    }
}

/* Style for the sidebar and page content on small screens */
@media (max-width: 767px) {
    .sidebar {
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #337ab7;
        z-index: 1;
        transform: translateX(0);
        transition: transform 0.2s ease-in-out;
    }
    .page-content {
        margin-left: 0;
    }
}




/* Mobile Sidebar Styles */
.sidebar.mobile-sidebar {
    width: auto; /* Full width on mobile */
    position: auto; /* Change position for mobile */
    top: auto; /* Align below the header */
    left: 230px;
    background-color: #3a4352; /* Mobile sidebar background */
    transition: all 0.2s ease; /* Smooth transition */
    z-index: 1; /* Ensure it appears above other content */
}

.sidebar.mobile-sidebar .sidebar-menu {
    padding: auto; /* Adjust padding for mobile */
}

.sidebar.mobile-sidebar li a {
    padding: auto; /* Adjust link padding for mobile */
    font-size: 16px; /* Adjust font size for better readability */
}


/* Add this to your CSS file */

/* Style for the sidebar and page content on extra large screens */
@media (min-width: 1400px) {
    .sidebar {
        width: 300px;
    }
    .page-content {
        margin-left: 300px;
    }
}

/* Style for the sidebar and page content on large screens */
@media (min-width: 1200px) and (max-width: 1399px) {
    .sidebar {
        width: 250px;
    }
    .page-content {
        margin-left: 250px;
    }
}

/* Style for the sidebar and page content on medium screens */
@media (min-width: 768px) and (max-width: 1199px) {
    .sidebar {
        width: 200px;
    }
    .page-content {
        margin-left: 200px;
    }
}

/* Style for the sidebar and page content on small screens */
@media (max-width: 767px) {
    .sidebar {
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #337ab7;
        z-index: 1;
        transform: translateX(0);
        transition: transform 0.2s ease-in-out;
    }
    .page-content {
        margin-left: 0;
    }
}

/* Style for the sidebar and page content on extra small screens */
@media (max-width: 479px) {
    .sidebar {
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #337ab7;
        z-index: 1;
        transform: translateX(0);
        transition: transform 0.2s ease-in-out;
    }
    .page-content {
        margin-left: 0;
    }
}
/* Style for the sidebar when it has the mobile-sidebar class */
.mobile-sidebar {
    width: auto;
}  /* <-- Added missing closing brace here */

/* Style for the sidebar when it has the large-screen-sidebar class */
.large-screen-sidebar {
    width: 300px;
}

/* Style for the sidebar when it has the medium-screen-sidebar class */
.medium-screen-sidebar {
    width: 200px;
}

/* Style for the sidebar when it has the small-screen-sidebar class */
.small-screen-sidebar {
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #337ab7;
    z-index: 1;
    transform: translateX(0);
    transition: transform 0.2s ease-in-out;
} 

/* Style for the overlay */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1;
}

/* Style for the overlay when the sidebar is open */
.sidebar-open .overlay {
    display: block;
}

/* Style for the overlay when the sidebar is closed */
.sidebar-closed .overlay {
    display: none;
}

/* Style for the overlay when the sidebar is open */
.sidebar-open .overlay {
    background-color: rgba(0, 0, 0, 0.2);
}

/* Style for the menu bar on mobile */
@media (max-width: 767px) {
    .menu-bar {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background-color: #337ab7;
    }
    .menu-item {
        margin-right: 20px;
    }
    .menu-item:last-child {
        margin-right: 0;
    }
}
/* Style for the menu bar on different screen sizes */
@media (max-width: 767px) {
    .menu-bar {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background-color: #337ab7;
    }
    .menu-item {
        margin-right: 20px;
    }
    .menu-item:last-child {
        margin-right: 0;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    .menu-bar {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background-color: #337ab7;
    }
    .menu-item {
        margin-right: 30px;
    }
    .menu-item:last-child {
        margin-right: 0;
    }
}

@media (min-width: 992px) {
    .menu-bar {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background-color: #337ab7;
    }
    .menu-item {
        margin-right: 40px;
    }
    .menu-item:last-child {
        margin-right: 0;
    }
}

/* Style for the sidebar on mobile */
@media (max-width: 767px) {
    .sidebar {
        display: none;
    }
    .toggle-button:checked + .sidebar {
        display: block;
    }
}
    </style>
</head>
<body>
<div class="header" style="background-color: #977C2F">
    <a href="javascript:void(0);" id="toggle_btn">
        <i class="fe fe-text-align-left"></i>
    </a>
   
   

    <a class="mobile_btn" id="mobile_btn">
        <i class="fa fa-bars"></i>
    </a>

    <ul class="nav user-menu">
        <li class="nav-item dropdown app-dropdown">
            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                <span class="user-img">
                    <img class="rounded-circle" src="assets/<?php echo get_asset_url('profiles/avatar-01.png', 'images'); ?>" width="31" alt="User Image">
                </span>
            </a>
            <div class="dropdown-menu">
                <div class="user-header">
                    <div class="avatar avatar-sm">
                        <img src="assets/<?php echo get_asset_url('profiles/avatar-01.png', 'images'); ?>" alt="User Image" class="avatar-img rounded-circle">
                    </div>
                    <div class="user-text">
                        <h6><?php echo htmlspecialchars($_SESSION['admin_logged_in']); ?></h6>
                                                <p class="text-muted mb-0">Administrator</p>
                    </div>
                </div>
                <a class="dropdown-item" href="profile.php">Profile</a>
                <a class="dropdown-item" href="logout.php">Logout</a>
            </div>
        </li>
    </ul>
</div>

<div class="sidebar active" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li><a href="dashboard.php"><i class="fe fe-home"></i> <span>Dashboard</span></a></li>
                <li class="submenu">
                    <a href="#"><i class="fe fe-user"></i> <span>All Users</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="adminlist.php">Admin</a></li>
                        <li><a href="userlist.php">Users</a></li>
                        <li><a href="useragent.php">Agent</a></li>
                        <li><a href="userbuilder.php">Builder</a></li>
                    </ul>
                </li>
                <li class="submenu">
                    <a href="#"><i class="fe fe-location"></i> <span>State & City</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="stateadd.php">State</a></li>
                        <li><a href="cityadd.php">City</a></li>
                    </ul>
                </li>

                
                <li class="submenu">
                    <a href="#"><i class="fe fe-map"></i> <span>Property</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="propertyadd.php">Add Property</a></li>
                        <li><a href="propertyview.php">View Property</a></li>
                        <li><a href="resellplot.php">Add Resell Plot</a></li>
                        <li><a href="viewresellplot.php">View Resell Plot</a></li>
                    </ul>
                </li>

                
                <li class="submenu">
                    <a href="#"><i class="fe fe-comment"></i> <span>Contact, Feedback</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="contactview.php">Contact</a></li>
                        <li><a href="feedbackview.php">Feedback</a></li>
                    </ul>
                </li>

                
                <li class="submenu">
                    <a href="#"><i class="fe fe-browser"></i> <span>About Page</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="aboutadd.php">Add About Content</a></li>
                        <li><a href="aboutview.php">View About</a></li>
                    </ul>
                </li>

               
<li class="submenu">
    <a href="#"><i class=" fe fe-picture"></i> <span>Gallery</span> <span class="menu-arrow"></span></a>
    <ul style="display: none;">
        <li><a href="addimage.php">Add Image</a></li>
        <li><a href="gallaryview.php">View Images</a></li>
    </ul>
</li><li class="submenu">  
<a href="#"><i class=" fe fe-vector"></i> 
<span>Site Management</span> 	<span class="menu-arrow"></span></a>   
<ul style="display: none;">        	
<li><a href="site_master.php">Add Site</a></li>	
<li><a href="gata_master.php">Add Gata</a></li>	
<li><a href="plot_master.php">Add Plot</a></li>	
<li><a href="update_site.php">Update Site</a></li>	
<li><a href="update_gata.php">Update Gata</a></li>	
<li><a href="update_plot.php">Update Plot</a></li>	
</ul></li>
<li class="submenu">
    <a href="#"><i class=" fe fe-building"></i> <span>Kissan Management</span> <span class="menu-arrow"></span></a>
    <ul style="display: none;">
        <li><a href="kissan_master.php">Add Kissan</a></li>
                <li><a href="view_kisaan.php">View Kissan</a></li>
    </ul>
</li>
<li class="submenu">
    <a href="#"><i class=" fe fe-building"></i> <span>Project Management</span> <span class="menu-arrow"></span></a>
    <ul style="display: none;">
        <li><a href="projects.php">Projects</a></li>
        <li><a href="property_inventory.php">Property Type and Plots Inventory</a></li>
        <li><a href="booking.php">Booking Form and Installment Management</a></li>
        <li><a href="customer_management.php">Customer Master, KYC, and Documentation Management</a></li>
        <li><a href="ledger.php">Customer Ledger and Outstanding</a></li>
        <li><a href="reminders.php">Payment Reminders and Reports</a></li>
    </ul>
</li>
<li class="submenu">
    <a href="#"><i class="fe fe-money"></i> <span>Account</span> <span class="menu-arrow"></span></a>
    <ul style="display: none;">
        <li><a href="financial_module.php">Financial Module</a></li>
        <li><a href="transactions.php">Transactions</a></li>
        <li><a href="add_transaction.php">Add Transaction</a></li>
        <li><a href="add_expenses.php">Add Expenses</a></li>
        <li><a href="add_income.php">Add Income</a></li>
        <li><a href="ledger.php">Ledger</a></li>
    </ul>
</li>

<!-- CRM Menu -->
<li class="submenu">
    <a href="#"><i class="fe fe-users"></i> <span>CRM</span> <span class="menu-arrow"></span></a>
    <ul style="display: none;">
        <li><a href="leads.php">लीड्स</a></li>
        <li><a href="opportunities.php">अवसर</a></li>
    </ul>
</li>
<li class="submenu">
    <a href="#"><i class=" fe fe-document"></i> <span>Job Applicant</span> <span class="menu-arrow"></span></a>
    <ul style="display: none;">
        <li><a href="admin_view_applicants.php">View Applicants</a></li>
        <li><a href="admin_add_job.php">Add Job</a></li>
    </ul>
</li>
<li class="submenu">
    <a href="#"><i class=" fe fe-sitemap"></i> <span>Assosiate</span> <span class="menu-arrow"></span></a>
    <ul style="display: none;">
        <li><a href="assosiate_managment.php">Add Assosiate</a></li>
        <li><a href="add_expenses.php">Add Expenses</a></li>
		<li><a href="transactions.php">transection</a></li>
    </ul>
</li>

                    </ul>
                </li>
            </ul>
			<br> <br>
        </div>
    </div>
</div>
<br> </br>

                <!-- Add other sidebar items here -->
            </ul>
        </div>
    </div>
</div>

<!-- Slider Section -->


<!-- Include your scripts here -->
<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons"></script>
<script>


// Toggle the sidebar
const sidebar = document.querySelector('.sidebar');
const toggleButton = document.querySelector('#toggle_btn');

toggleButton.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.add('active');
});


// Function to apply mobile styles if the viewport is less than 768px
function applyMobileStyles() {
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth < 768) {
        // Add mobile-specific classes or styles
        sidebar.classList.add('mobile-sidebar');
    } else {
        // Remove mobile-specific classes or styles
        sidebar.classList.remove('mobile-sidebar');
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', applyMobileStyles);

// Run on window resize
window.addEventListener('resize', applyMobileStyles);


// Function to apply large screen styles if the viewport is greater than or equal to 1200px
function applyLargeScreenStyles() {
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth >= 1200) {
        // Add large screen-specific classes or styles
        sidebar.classList.add('large-screen-sidebar');
    } else {
        // Remove large screen-specific classes or styles
        sidebar.classList.remove('large-screen-sidebar');
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', applyLargeScreenStyles);

// Run on window resize
window.addEventListener('resize', applyLargeScreenStyles);

// Function to apply medium screen styles if the viewport is greater than or equal to 768px and less than 1200px
function applyMediumScreenStyles() {
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth >= 768 && window.innerWidth < 1200) {
        // Add medium screen-specific classes or styles
        sidebar.classList.add('medium-screen-sidebar');
    } else {
        // Remove medium screen-specific classes or styles
        sidebar.classList.remove('medium-screen-sidebar');
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', applyMediumScreenStyles);

// Run on window resize
window.addEventListener('resize', applyMediumScreenStyles);

// Function to apply small screen styles if the viewport is less than 768px
function applySmallScreenStyles() {
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth < 768) {
        // Add small screen-specific classes or styles
        sidebar.classList.add('small-screen-sidebar');
    } else {
        // Remove small screen-specific classes or styles
        sidebar.classList.remove('small-screen-sidebar');
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', applySmallScreenStyles);

// Run on window resize
window.addEventListener('resize', applySmallScreenStyles);

// Get the menu bar element
const menuBar = document.querySelector('.menu-bar');

// Get the menu items
const menuItems = document.querySelectorAll('.menu-item');

// Function to update the menu bar layout
function updateMenuBarLayout() {
    // Get the screen width
    const screenWidth = window.innerWidth;

    // Update the menu bar layout based on the screen width
    if (screenWidth <= 767) {
        menuBar.style.flexDirection = 'row';
        menuItems.forEach((menuItem) => {
            menuItem.style.marginRight = '20px';
        });
    } else if (screenWidth >= 768 && screenWidth <= 991) {
        menuBar.style.flexDirection = 'row';
        menuItems.forEach((menuItem) => {
            menuItem.style.marginRight = '30px';
        });
    } else {
        menuBar.style.flexDirection = 'row';
        menuItems.forEach((menuItem) => {
            menuItem.style.marginRight = '40px';
        });
    }
}

// Update the menu bar layout on page load
updateMenuBarLayout();

// Update the menu bar layout on window resize
window.addEventListener('resize', updateMenuBarLayout);

feather.replace();
</script>
</body>
</html>
