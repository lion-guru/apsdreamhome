<?php
// Modern, Indian Real Estate Homepage (APS Dream Homes)
// एरर रिपोर्टिंग को डेवलपमेंट के लिए सक्षम करें
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// प्रोडक्शन में, एरर लॉगिंग का उपयोग करें और स्क्रीन पर प्रदर्शन को अक्षम करें
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', 'c:/xampp/htdocs/apsdreamhome/logs/error.log');
session_start();
require_once __DIR__ . '/includes/db_config.php';
$page_title = "APS Dream Homes - Find Plots, Buy, Sell, Rent";
$meta_description = "Plots, properties, rentals, and more in Gorakhpur, Lucknow, and Uttar Pradesh. Find, buy, sell, or rent with APS Dream Homes.";
$additional_css = '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,600&display=swap">' . "\n" .
                  '<link rel="stylesheet" href="/assets/css/homepage-modern.css?v=' . time() . '">' . "\n" .
                  '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">' . "\n" .
                  '<link rel="stylesheet" href="/assets/vendor/js/swiper-bundle.min.css?v=1.0">' . "\n" .
                  '<link rel="stylesheet" href="/assets/vendor/js/aos.css?v=1.0">';
$additional_js = '<script src="/assets/vendor/js/swiper-bundle.min.js?v=1.0" defer></script>' . "\n" .
                 '<script src="/assets/vendor/js/aos.js?v=1.0" defer></script>' . "\n" .
                 '<script>document.addEventListener("DOMContentLoaded", function() {' . "\n" .
                 'const heroSwiper = new Swiper(".hero-slider", {loop:true,effect:"fade",fadeEffect:{crossFade:true},autoplay:{delay:5000,disableOnInteraction:false},pagination:{el:".swiper-pagination",clickable:true},navigation:{nextEl:".swiper-button-next",prevEl:".swiper-button-prev"}});' . "\n" .
                 'AOS.init({duration:1000,once:true,offset:100});' . "\n" .
                 '});</script>';
require_once __DIR__ . '/includes/templates/dynamic_header.php';

// Fetch dynamic sections from DB for homepage
$conn = getMysqliConnection();

// Site settings
$settings = [];
$res = $conn->query("SELECT setting_name, value FROM site_settings");
while ($row = $res->fetch_assoc()) $settings[$row['setting_name']] = $row['value'];

// Properties
$latest_properties = [];
$res = $conn->query("SELECT * FROM properties WHERE status='active' ORDER BY id DESC LIMIT 8");
while ($row = $res->fetch_assoc()) $latest_properties[] = $row;

// Projects
$projects = [];
$res = $conn->query("SELECT * FROM projects WHERE status='active' ORDER BY id DESC LIMIT 6");
while ($row = $res->fetch_assoc()) $projects[] = $row;

// Team
$team = [];
$res = $conn->query("SELECT * FROM team WHERE status='active' ORDER BY id DESC LIMIT 4");
while ($row = $res->fetch_assoc()) $team[] = $row;

// Gallery
$gallery = [];
$res = $conn->query("SELECT * FROM gallery WHERE status='active' ORDER BY id DESC LIMIT 8");
while ($row = $res->fetch_assoc()) $gallery[] = $row;

// Testimonials
$testimonials = [];
$res = $conn->query("SELECT * FROM testimonials WHERE status='active' ORDER BY id DESC LIMIT 5");
while ($row = $res->fetch_assoc()) $testimonials[] = $row;

// Top Agents
$top_agents = [];
$res = $conn->query("SELECT * FROM agents ORDER BY sales DESC, id DESC LIMIT 4");
while ($row = $res->fetch_assoc()) $top_agents[] = $row;

// AI Recommendations (if available)
$ai_recommendations = [];
if ($conn->query("SHOW TABLES LIKE 'ai_recommendations'")->num_rows > 0) {
  $res = $conn->query("SELECT * FROM ai_recommendations WHERE status='active' ORDER BY id DESC LIMIT 6");
  while ($row = $res->fetch_assoc()) $ai_recommendations[] = $row;
}
?>

<!-- HERO SECTION -->
<section class="hero-section d-flex align-items-center min-vh-80" style="background: url('assets/images/hero-bg.jpg') center/cover no-repeat; position:relative;">
  <div class="hero-overlay" style="position:absolute;inset:0;background:rgba(34,49,63,0.55);"></div>
  <div class="container text-center position-relative" style="z-index:2;">
    <h1 class="display-4 fw-bold mb-3 text-white" style="letter-spacing:-2px;">Find Plots, Buy, Sell or Rent<br><span class="text-warning">All in One Place</span></h1>
    <p class="lead mb-4 text-light">Plots, Properties, Rentals & Commercial Spaces in Gorakhpur, Lucknow & Uttar Pradesh</p>
    <form class="search-bar mx-auto d-flex flex-wrap justify-content-center gap-2 bg-white rounded shadow-lg p-2" style="max-width:700px;">
      <select class="form-select" style="max-width:140px;">
        <option>Buy</option>
        <option>Rent</option>
        <option>Plot</option>
        <option>Commercial</option>
      </select>
      <input type="text" class="form-control flex-grow-1" placeholder="Search city, area, or project...">
      <button class="btn btn-primary px-4" type="submit"><i class="fas fa-search me-2"></i>Search</button>
    </form>
    <div class="cta-buttons mt-4 d-flex justify-content-center gap-3">
      <a href="/post-property.php" class="btn btn-warning shadow">Post Property</a>
      <a href="/contact.php" class="btn btn-outline-light">Enquire Now</a>
    </div>
  </div>
  <a href="https://wa.me/919999999999" target="_blank" class="whatsapp-float" style="position:fixed;bottom:32px;right:32px;z-index:99;background:#25D366;color:#fff;border-radius:50%;width:56px;height:56px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 12px rgba(0,0,0,0.2);font-size:2rem;"><i class="fab fa-whatsapp"></i></a>
</section>

<!-- SERVICES SECTION -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-md-2 col-6">
        <div class="service-card p-3 bg-white rounded shadow-sm h-100">
          <i class="fas fa-map-marked-alt fa-2x text-primary mb-2"></i>
          <h6 class="fw-bold">Plotting</h6>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="service-card p-3 bg-white rounded shadow-sm h-100">
          <i class="fas fa-home fa-2x text-success mb-2"></i>
          <h6 class="fw-bold">Buy/Sell</h6>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="service-card p-3 bg-white rounded shadow-sm h-100">
          <i class="fas fa-key fa-2x text-warning mb-2"></i>
          <h6 class="fw-bold">Rentals</h6>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="service-card p-3 bg-white rounded shadow-sm h-100">
          <i class="fas fa-building fa-2x text-info mb-2"></i>
          <h6 class="fw-bold">Commercial</h6>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="service-card p-3 bg-white rounded shadow-sm h-100">
          <i class="fas fa-tools fa-2x text-danger mb-2"></i>
          <h6 class="fw-bold">Construction</h6>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="service-card p-3 bg-white rounded shadow-sm h-100">
          <i class="fas fa-balance-scale fa-2x text-secondary mb-2"></i>
          <h6 class="fw-bold">Legal Help</h6>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURED PROPERTIES/PLOTS SECTION -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="section-title mb-4 text-center">
      <h2 class="fw-bold">Featured Properties & Plots</h2>
      <p class="text-muted">Handpicked for you — buy, sell, rent, or invest</p>
    </div>
    <div class="row g-4">
      <?php foreach ($latest_properties as $prop): ?>
      <div class="col-md-4 col-lg-3">
        <div class="card h-100 shadow property-card">
          <img src="assets/images/properties/<?php echo htmlspecialchars($prop['pimage']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prop['title']); ?>">
          <div class="card-body">
            <h5 class="card-title mb-1"><?php echo htmlspecialchars($prop['title']); ?></h5>
            <div class="fw-bold text-primary mb-2">₹<?php echo htmlspecialchars($prop['price']); ?></div>
            <div class="small text-muted mb-2">
              <span><i class="fas fa-bed"></i> <?php echo htmlspecialchars($prop['bhk']); ?> BHK</span>
              <span class="ms-3"><i class="fas fa-vector-square"></i> <?php echo htmlspecialchars($prop['size']); ?> sq.ft</span>
            </div>
            <span class="badge bg-<?php echo ($prop['type'] == 'Plot') ? 'warning' : (($prop['type'] == 'Commercial') ? 'info' : 'success'); ?> mb-2"><?php echo htmlspecialchars($prop['type']); ?></span>
            <a href="#" class="btn btn-outline-primary btn-modern w-100">View Details</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PROJECTS SECTION -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="section-title mb-4 text-center">
      <h2 class="fw-bold">Our Projects</h2>
      <p class="text-muted">Featured projects by APS Dream Homes</p>
    </div>
    <div class="row g-4">
      <?php foreach($projects as $p): ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title"><?=htmlspecialchars($p['name'])?></h5>
            <p class="card-text"><?=htmlspecialchars($p['description'])?></p>
            <div class="text-muted mb-2"><i class="fas fa-map-marker-alt"></i> <?=htmlspecialchars($p['location'])?></div>
            <div class="small">Start: <?=htmlspecialchars($p['start_date'])?> | Budget: ₹<?=number_format($p['budget'])?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TEAM SECTION -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="section-title mb-4 text-center">
      <h2 class="fw-bold">Meet Our Team</h2>
      <p class="text-muted">Our experienced professionals</p>
    </div>
    <div class="row g-4">
      <?php foreach($team as $m): ?>
      <div class="col-md-3">
        <div class="card h-100 text-center shadow-sm">
          <img src="<?=htmlspecialchars($m['photo'])?>" class="card-img-top" alt="<?=htmlspecialchars($m['name'])?>" style="height:180px;object-fit:cover;">
          <div class="card-body">
            <h5 class="card-title mb-0"><?=htmlspecialchars($m['name'])?></h5>
            <div class="small text-muted mb-2"><?=htmlspecialchars($m['designation'])?></div>
            <p class="card-text small"><?=htmlspecialchars($m['bio'])?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- GALLERY SECTION -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="section-title mb-4 text-center">
      <h2 class="fw-bold">Gallery</h2>
      <p class="text-muted">Our projects and milestones</p>
    </div>
    <div class="row g-3">
      <?php foreach($gallery as $img): ?>
      <div class="col-md-3 col-6">
        <img src="<?=htmlspecialchars($img['image_path'])?>" alt="<?=htmlspecialchars($img['caption'])?>" class="img-fluid rounded shadow-sm mb-2">
        <div class="text-center small text-muted"><?=htmlspecialchars($img['caption'])?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TESTIMONIALS SECTION -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="section-title mb-4 text-center">
      <h2 class="fw-bold">Testimonials</h2>
      <p class="text-muted">What our clients say</p>
    </div>
    <div class="row g-4 justify-content-center">
      <?php foreach($testimonials as $t): ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <img src="<?=htmlspecialchars($t['client_photo'])?>" alt="<?=htmlspecialchars($t['client_name'])?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;margin-right:12px;">
              <div>
                <div class="fw-bold mb-0"><?=htmlspecialchars($t['client_name'])?></div>
                <div class="small text-muted">Client</div>
              </div>
            </div>
            <p class="card-text small">&ldquo;<?=htmlspecialchars($t['testimonial'])?>&rdquo;</p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US / STATS SECTION -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3 mb-3">
        <div class="stat-card p-4 bg-white rounded shadow-sm">
          <div class="fw-bold display-5 text-primary">1500+</div>
          <div class="text-muted">Plots Sold</div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="stat-card p-4 bg-white rounded shadow-sm">
          <div class="fw-bold display-5 text-success">500+</div>
          <div class="text-muted">Properties Listed</div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="stat-card p-4 bg-white rounded shadow-sm">
          <div class="fw-bold display-5 text-warning">1000+</div>
          <div class="text-muted">Happy Clients</div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="stat-card p-4 bg-white rounded shadow-sm">
          <div class="fw-bold display-5 text-danger">10+</div>
          <div class="text-muted">Years Experience</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TOP AGENTS SECTION -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="section-title mb-4 text-center">
      <h2 class="fw-bold">Top Agents</h2>
      <p class="text-muted">Meet our expert team</p>
    </div>
    <div class="row g-4 justify-content-center">
      <?php foreach ($top_agents as $agent): ?>
      <div class="col-md-3 col-6">
        <div class="card agent-card h-100 text-center shadow border-0" style="background: linear-gradient(135deg, #f8fafc 60%, #e0e7ef 100%);">
          <div class="mx-auto mt-4 mb-2" style="width:100px;height:100px;">
            <img src="assets/images/agents/default.jpg" class="card-img-top rounded-circle border border-2 border-primary shadow-sm" style="width:100px;height:100px;object-fit:cover;background:#fff;" alt="<?php echo htmlspecialchars($agent['name']); ?>">
          </div>
          <div class="card-body p-2">
            <h6 class="fw-bold mb-1 text-primary"><?php echo htmlspecialchars($agent['name']); ?></h6>
            <span class="badge bg-success mb-2" style="font-size:1rem;">Sales: <?php echo htmlspecialchars($agent['sales']); ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- AI/SMART RECOMMENDATIONS SECTION -->
<?php if (!empty($ai_recommendations)): ?>
<section class="py-5 bg-light">
  <div class="container">
    <div class="section-title mb-4 text-center">
      <h2 class="fw-bold">Recommended for You</h2>
      <p class="text-muted">Smart picks based on your needs</p>
    </div>
    <div class="row g-4 justify-content-center">
      <?php foreach ($ai_recommendations as $rec): ?>
      <div class="col-md-4 col-lg-3">
        <div class="card h-100 shadow property-card">
          <img src="assets/images/properties/<?php echo htmlspecialchars($rec['pimage']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($rec['title']); ?>">
          <div class="card-body">
            <h5 class="card-title mb-1"><?php echo htmlspecialchars($rec['title']); ?></h5>
            <div class="fw-bold text-danger mb-2">₹<?php echo htmlspecialchars($rec['price']); ?></div>
            <div class="small text-muted mb-2">
              <span><i class="fas fa-bed"></i> <?php echo htmlspecialchars($rec['bhk']); ?> BHK</span>
              <span class="ms-3"><i class="fas fa-vector-square"></i> <?php echo htmlspecialchars($rec['size']); ?> sq.ft</span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/templates/dynamic_footer.php'; ?>