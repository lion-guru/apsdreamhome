<header id="header" class="transparent-header-modern fixed-header-bg-white w-100">
    <!--
  <div class="top-header bg-secondary">
    <div class="container">
      <div class="row">
        <div class="col-md-8">
          <ul class="top-contact list-text-white d-table">
            <li><a href="#"><i class="fas fa-phone-alt text-success mr-1"></i>7007444842</a></li>
            <li><a href="#"><i class="fas fa-envelope text-success mr-1"></i>apsdreamhomes44@gmail.com</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div> -->
  <div class="main-nav secondary-nav hover-success-nav py-2">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <nav class="navbar navbar-expand-lg navbar-light p-0">
            <a class="navbar-brand position-relative" href="index.php"><img class="nav-logo" src="images/logo/restatelg.png" alt=""></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav mr-auto">
                <li class="nav-item"> <a class="nav-link" href="index.php">Home</a> </li>
                <li class="nav-item dropdown"> 
                  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Project</a>
                  <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item dropdown-toggle" href="#" id="navbarDropdownGorakhpur" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Gorakhpur</a>
                      <ul class="dropdown-menu" aria-labelledby="navbarDropdownGorakhpur">
                        <li><a class="dropdown-item" href="gorakhpur-suryoday-colony.php">Suryoday Colony</a></li>
                        <li><a class="dropdown-item" href="gorakhpur-raghunath-nagri.php">Raghunath Nagri</a></li>
                      </ul>
                    </li>
                    <li><a class="dropdown-item dropdown-toggle" href="#" id="navbarDropdownLucknow" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Lucknow</a>
                      <ul class="dropdown-menu" aria-labelledby="navbarDropdownLucknow">
                        <li><a class="dropdown-item" href="lucknow-ram-nagri.php">Ram Nagri</a></li>
                        <li><a class="dropdown-item" href="lucknow-project.php">Nawab City</a></li>
                      </ul>
                    </li>
                    <li><a class="dropdown-item dropdown-toggle" href="#" id="navbarDropdownVaranasi" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Kusinagar</a>
                      <ul class="dropdown-menu" aria-labelledby="navbarDropdownVaranasi">
                        <li><a class="dropdown-item" href="budhacity.php">Budha City</a></li>
                       
                      </ul>
                    </li>
                  </ul>
                </li>
                <li class="nav-item"> <a class="nav-link" href="gallary.php">Gallary</a> </li>
                <li class="nav-item"> <a class="nav-link" href="legal.php">Legal</a> </li>
                <li class="nav-item"> <a class="nav-link" href="career.php">Career</a> </li>
                <li class="nav-item"> <a class="nav-link" href="about.php">About</a> </li>
                <li class="nav-item"> <a class="nav-link" href="bank.php">Bank</a> </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="resellDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Resell</a>
                  <div class="dropdown-menu" aria-labelledby="resellDropdown">
                                        <a class="dropdown-item" href="property.php">View Resell Properties</a>
                  <a class="dropdown-item" href="submitproperty.php">Add Your Properties</a>
                </div>
              </li>
              <?php if(isset($_SESSION['uemail'])) { ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Account</a>
                  <ul class="dropdown-menu">
                    <li><a class="nav-link" href="profile.php">Profile</a></li>
                   
                    <li><a class="nav-link" href="feature.php">Your Property</a></li>
                    <li><a class="nav-link" href="logout.php">Logout</a></li>
                  </ul>
                </li>
              <?php } else { ?>
                <li class="nav-item">
                  <a class="nav-link login-register login" href="login.php">Login</a>
                </li>
              <?php } ?>
            </ul>
          </div>
        </nav>
      </div>
    </div>
  </div>
</header>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script>
  $(document).ready(function() {
    // Make dropdown menu clickable on mobile devices
    $('.dropdown-toggle').on('click', function(event) {
      event.preventDefault();
      $(this).next('.dropdown-menu').toggle();
    });
  });
</script>