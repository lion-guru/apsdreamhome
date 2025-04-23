<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions/common-functions.php';

// Set page specific variables
$page_title = "APS Dream Homes - Premium Real Estate Solutions";
$meta_description = "APS Dream Homes provides premium real estate services in Gorakhpur, Lucknow, and across Uttar Pradesh. Explore our residential and commercial properties.";

// Additional CSS for this page
$additional_css = '
<link rel="preload" as="style" href="' . get_asset_url('swiper-bundle.min.css?v=1.0', 'vendor') . '">
<link rel="stylesheet" href="' . get_asset_url('swiper-bundle.min.css?v=1.0', 'vendor') . '">
<link rel="preload" as="style" href="' . get_asset_url('aos.css?v=1.0', 'vendor') . '">
<link rel="stylesheet" href="' . get_asset_url('aos.css?v=1.0', 'vendor') . '">
<style>
    /* Page-specific overrides only. Move all main section CSS to /assets/css/home.css */
</style>
';

// Additional JS for this page
$additional_js = '
<script src="' . get_asset_url('swiper-bundle.min.js?v=1.0', 'vendor') . '" defer></script>
<script src="' . get_asset_url('aos.js?v=1.0', 'vendor') . '" defer></script>
<script>
    // Initialize Swiper
    document.addEventListener("DOMContentLoaded", function() {
        const heroSwiper = new Swiper(".hero-slider", {
            loop: true,
            effect: "fade",
            fadeEffect: { crossFade: true },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });

        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    });
</script>
';

require_once __DIR__ . '/includes/templates/dynamic_header.php';
?>

<!-- Video Banner Section -->
<section class="video-banner-section position-relative" style="background: linear-gradient(120deg, #2a5298 80%, #e74c3c 100%); min-height: 340px; display: flex; align-items: center;">
    <video autoplay muted loop playsinline poster="/assets/images/banner/ban1.jpg" style="position:absolute; left:0; top:0; width:100%; height:100%; object-fit:cover; opacity:0.45; z-index:1;"><source src="/assets/videos/hero-banner.mp4" type="video/mp4">Your browser does not support the video tag.</video>
    <div class="container position-relative" style="z-index:2;">
        <div class="row justify-content-center align-items-center text-center">
            <div class="col-lg-10 mx-auto">
                <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-up">Discover Your Dream Home with APS Dream Homes</h1>
                <p class="lead text-white-50 mb-4" data-aos="fade-up" data-aos-delay="100">Premium properties, expert guidance, and trusted service in Gorakhpur, Lucknow & beyond.</p>
                <a href="<?php echo BASE_URL; ?>/properties.php" class="btn btn-warning btn-lg rounded-pill px-5 me-2" aria-label="Browse Properties" data-aos="fade-up" data-aos-delay="200">Browse Properties</a>
                <a href="<?php echo BASE_URL; ?>/contact.php" class="btn btn-outline-light btn-lg rounded-pill px-5" aria-label="Contact Us" data-aos="fade-up" data-aos-delay="300">Contact Us</a>
            </div>
        </div>
    </div>
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; bottom:-1px; left:0; z-index:3;"><path d="M0,0 C480,80 960,0 1440,80 L1440,80 L0,80 Z" fill="#fff"/></svg>
</section>

<!-- Hero Section -->
<section class="hero-section">
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <div class="swiper-slide" style="background-image: url(/assets/images/banner/ban1.jpg)">
                <div class="hero-content">
                    <h1 data-aos="fade-up">Find Your Dream Home</h1>
                    <p data-aos="fade-up" data-aos-delay="200">Premium Properties | Trusted Service | Best Locations</p>
                    <div class="hero-buttons" data-aos="fade-up" data-aos-delay="400">
                        <a href="<?php echo BASE_URL; ?>/properties.php" class="btn btn-warning" aria-label="Explore Properties">Explore Properties</a>
                        <a href="<?php echo BASE_URL; ?>/contact.php" class="btn btn-outline-light" aria-label="Contact Us">Contact Us</a>
                    </div>
                </div>
            </div>
            <div class="swiper-slide" style="background-image: url(/assets/images/banner/ban2.jpg)">
                <div class="hero-content">
                    <h1 data-aos="fade-up">Luxury Living Awaits</h1>
                    <p data-aos="fade-up" data-aos-delay="200">Modern Designs | Affordable Prices</p>
                </div>
            </div>
            <div class="swiper-slide" style="background-image: url(/assets/images/site_photo/gorakhpur/suryoday.jpg)">
                <div class="hero-content">
                    <h1 data-aos="fade-up">Your Trusted Real Estate Partner</h1>
                    <p data-aos="fade-up" data-aos-delay="200">Serving Gorakhpur, Lucknow & Beyond</p>
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</section>

<!-- Animated Counters/Stats Section -->
<section class="counters-section py-5 bg-primary bg-gradient position-relative text-white">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#fff"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-4 mb-md-0">
                <div class="display-4 fw-bold counter" data-target="100">0</div>
                <div class="fs-5">Properties Sold</div>
            </div>
            <div class="col-6 col-md-3 mb-4 mb-md-0">
                <div class="display-4 fw-bold counter" data-target="10">0</div>
                <div class="fs-5">Years Experience</div>
            </div>
            <div class="col-6 col-md-3 mb-4 mb-md-0">
                <div class="display-4 fw-bold counter" data-target="500">0</div>
                <div class="fs-5">Happy Clients</div>
            </div>
            <div class="col-6 col-md-3 mb-4 mb-md-0">
                <div class="display-4 fw-bold counter" data-target="30">0</div>
                <div class="fs-5">Awards Won</div>
            </div>
        </div>
    </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = Math.ceil(target / 60);
            if (count < target) {
                counter.innerText = count + increment > target ? target : count + increment;
                setTimeout(updateCount, 18);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });
});
</script>

<!-- Quick Search Section -->
<section class="quick-search-section py-4 position-relative">
    <div class="quick-search-bg position-absolute top-0 start-0 w-100 h-100" style="background: url('/assets/images/banner/diwaliban.jpg') center center/cover no-repeat; opacity: 0.18; z-index: 1;"></div>
    <div class="container position-relative" style="z-index: 2;">
        <form action="<?php echo BASE_URL; ?>/properties.php" method="get" class="quick-search-form shadow-lg p-4 rounded-3 bg-white bg-opacity-75">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary">Property Type</label>
                    <select name="type" class="form-select">
                        <option value="">Any</option>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="plot">Plot</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary">Location</label>
                    <select name="location" class="form-select">
                        <option value="">Any</option>
                        <option value="gorakhpur">Gorakhpur</option>
                        <option value="lucknow">Lucknow</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary">Budget</label>
                    <select name="budget" class="form-select">
                        <option value="">Any</option>
                        <option value="0-2500000">Under 25 Lac</option>
                        <option value="2500000-5000000">25-50 Lac</option>
                        <option value="5000000-10000000">50 Lac - 1 Cr</option>
                        <option value="10000000+">Above 1 Cr</option>
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill" aria-label="Search Properties">Search Properties</button>
                </div>
            </div>
        </form>
    </div>
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; bottom:-1px; left:0; z-index:3;"><path d="M0,0 C480,80 960,0 1440,80 L1440,80 L0,80 Z" fill="#fff"/></svg>
</section>

<!-- Featured Properties Section -->
<section class="featured-properties py-5 position-relative">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#e0ecff"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 data-aos="fade-up">Featured Properties</h2>
            <p data-aos="fade-up" data-aos-delay="200">Explore our hand-picked premium properties</p>
        </div>
        <div class="row g-4">
            <?php
            // Get featured properties
            $featured_properties = get_featured_properties();
            $demo_images = [
                '/assets/images/property/property-banner.jpg',
                '/assets/images/site_photo/gorakhpur/aps.jpg',
                '/assets/images/site_photo/gorakhpur/suryoday.jpg',
                '/assets/images/banner/ban3.jpg',
                '/assets/images/banner/ban4jpg',
                '/assets/images/banner/ban8.jpg',
            ];
            $i = 0;
            foreach ($featured_properties as $property):
            ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="property-card position-relative overflow-hidden">
                    <div class="property-image position-relative">
                        <img src="<?php echo isset($property['image']) && $property['image'] ? get_asset_url($property['image'], 'images') : $demo_images[$i % count($demo_images)]; ?>" alt="<?php echo htmlspecialchars($property['title'] ?? 'Featured property by APS Dream Homes'); ?>" class="img-fluid rounded-top-3" style="height:220px; object-fit:cover; width:100%;" loading="lazy">
                        <div class="property-tag position-absolute top-0 start-0 m-2 px-3 py-1 bg-primary text-white rounded-pill shadow-sm"><?php echo $property['type']; ?></div>
                        <div class="property-price position-absolute bottom-0 end-0 m-2 px-3 py-1 bg-danger text-white rounded-pill shadow-sm">₹<?php echo format_price($property['price']); ?></div>
                    </div>
                    <div class="property-content p-3">
                        <h3 class="fw-bold text-primary"><?php echo $property['title']; ?></h3>
                        <p class="location mb-1"><i class="fas fa-map-marker-alt text-danger"></i> <?php echo $property['location']; ?></p>
                        <div class="property-features mb-2">
                            <span class="me-2"><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                            <span class="me-2"><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                            <span><i class="fas fa-vector-square"></i> <?php echo $property['area']; ?> sq.ft</span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/property.php?id=<?php echo $property['id']; ?>" class="btn btn-outline-primary w-100 mt-2 rounded-pill" aria-label="View Details">View Details</a>
                    </div>
                </div>
            </div>
            <?php $i++; endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>/properties.php" class="btn btn-primary btn-lg rounded-pill px-5" aria-label="View All Properties">View All Properties</a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-choose-us py-5 bg-light position-relative">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#f6f9fc"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 data-aos="fade-up">Why Choose APS Dream Homes?</h2>
            <p data-aos="fade-up" data-aos-delay="200">Experience excellence in real estate services</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up">
                <div class="feature-card text-center p-4 bg-white rounded-4 shadow-sm border-0">
                    <div class="icon-circle bg-primary bg-gradient text-white mb-3 d-flex align-items-center justify-content-center mx-auto" style="width:70px; height:70px; border-radius:50%; font-size:2.5rem;"><i class="fas fa-home"></i></div>
                    <h3 class="fw-bold">Premium Properties</h3>
                    <p>Exclusive collection of high-quality properties in prime locations</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card text-center p-4 bg-white rounded-4 shadow-sm border-0">
                    <div class="icon-circle bg-warning bg-gradient text-white mb-3 d-flex align-items-center justify-content-center mx-auto" style="width:70px; height:70px; border-radius:50%; font-size:2.5rem;"><i class="fas fa-handshake"></i></div>
                    <h3 class="fw-bold">Expert Guidance</h3>
                    <p>Professional assistance throughout your property journey</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card text-center p-4 bg-white rounded-4 shadow-sm border-0">
                    <div class="icon-circle bg-danger bg-gradient text-white mb-3 d-flex align-items-center justify-content-center mx-auto" style="width:70px; height:70px; border-radius:50%; font-size:2.5rem;"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="fw-bold">Trusted Partner</h3>
                    <p>Reliable and transparent real estate solutions</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials py-5 position-relative">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#2a5298"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 data-aos="fade-up">What Our Clients Say</h2>
            <p class="lead text-secondary" data-aos="fade-up" data-aos-delay="100">Read testimonials from our satisfied customers</p>
        </div>
        <div class="swiper testimonial-slider">
            <div class="swiper-wrapper">
                <?php
                // Get testimonials
                $testimonials = get_testimonials();
                foreach ($testimonials as $testimonial):
                    $img_path = !empty($testimonial['image']) && file_exists(__DIR__ . '/assets/images/' . $testimonial['image'])
                        ? get_asset_url($testimonial['image'], 'images')
                        : get_asset_url('testimonials/avatar-default.png', 'images');
                ?>
                <div class="swiper-slide">
                    <div class="testimonial-card text-center p-4 rounded-4 shadow-lg border-0 bg-white bg-opacity-75">
                        <div class="client-image mb-3">
                            <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="rounded-circle border border-3" style="width:70px;height:70px;object-fit:cover;" loading="lazy">
                        </div>
                        <div class="testimonial-content">
                            <p class="testimonial-text text-dark">"<?php echo $testimonial['message']; ?>"</p>
                            <h4 class="client-name mb-0 text-warning"><?php echo $testimonial['name']; ?></h4>
                            <p class="client-location small text-secondary mb-0"><?php echo $testimonial['location']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section py-5 bg-gradient position-relative" style="background: linear-gradient(120deg, #2a5298 80%, #e74c3c 100%);">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#fff"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="row align-items-center">
            <div class="col-md-8" data-aos="fade-right">
                <h2 class="fw-bold text-white">Ready to Find Your Dream Home?</h2>
                <p class="text-white-50">Contact us today and let our experts help you find the perfect property</p>
            </div>
            <div class="col-md-4 text-md-end" data-aos="fade-left">
                <a href="<?php echo BASE_URL; ?>/contact.php" class="btn btn-light btn-lg rounded-pill px-5" aria-label="Contact Us">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<!-- Partners & Awards Section -->
<section class="partners-awards-section py-5 bg-white position-relative">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#f6f9fc"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 data-aos="fade-up">Our Trusted Partners & Awards</h2>
            <p data-aos="fade-up" data-aos-delay="200">Recognized by industry leaders and trusted by top partners</p>
        </div>
        <div class="row justify-content-center align-items-center g-4">
            <div class="col-6 col-sm-4 col-md-2 text-center">
                <img src="/assets/images/banner/ban3.jpg" alt="Luxury Homes Ltd. partner logo" class="img-fluid rounded shadow-sm mb-2" style="max-height:70px; object-fit:contain;" loading="lazy">
                <div class="small text-secondary">Luxury Homes Ltd.</div>
            </div>
            <div class="col-6 col-sm-4 col-md-2 text-center">
                <img src="/assets/images/banner/ban5.jpg" alt="Urban Realty partner logo" class="img-fluid rounded shadow-sm mb-2" style="max-height:70px; object-fit:contain;" loading="lazy">
                <div class="small text-secondary">Urban Realty</div>
            </div>
            <div class="col-6 col-sm-4 col-md-2 text-center">
                <img src="/assets/images/banner/ban6.jpg" alt="Excellence in Service award logo" class="img-fluid rounded shadow-sm mb-2" style="max-height:70px; object-fit:contain;" loading="lazy">
                <div class="small text-secondary">Excellence in Service</div>
            </div>
            <div class="col-6 col-sm-4 col-md-2 text-center">
                <img src="/assets/images/banner/ban8.jpg" alt="Top Performer 2025 award logo" class="img-fluid rounded shadow-sm mb-2" style="max-height:70px; object-fit:contain;" loading="lazy">
                <div class="small text-secondary">Top Performer 2025</div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5 bg-light position-relative">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#e0ecff"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 data-aos="fade-up">Frequently Asked Questions</h2>
            <p data-aos="fade-up" data-aos-delay="200">Find answers to common queries about buying, selling, and investing in properties</p>
        </div>
        <div class="accordion accordion-flush" id="faqAccordion">
            <div class="accordion-item mb-3">
                <h3 class="accordion-header" id="faqHeadingOne">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne" aria-expanded="false" aria-controls="faqCollapseOne">
                        How do I schedule a property visit?
                    </button>
                </h3>
                <div id="faqCollapseOne" class="accordion-collapse collapse" aria-labelledby="faqHeadingOne" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        You can schedule a visit by contacting us directly through the <a href="<?php echo BASE_URL; ?>/contact.php">Contact Us</a> page or by calling our office. Our team will arrange a convenient time for your tour.
                    </div>
                </div>
            </div>
            <div class="accordion-item mb-3">
                <h3 class="accordion-header" id="faqHeadingTwo">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
                        What documents are needed to buy a property?
                    </button>
                </h3>
                <div id="faqCollapseTwo" class="accordion-collapse collapse" aria-labelledby="faqHeadingTwo" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Generally, you will need an identity proof, address proof, PAN card, and proof of income. Our team will guide you through the exact requirements for your chosen property.
                    </div>
                </div>
            </div>
            <div class="accordion-item mb-3">
                <h3 class="accordion-header" id="faqHeadingThree">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
                        Can I get a home loan for my purchase?
                    </button>
                </h3>
                <div id="faqCollapseThree" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, we assist our clients in getting home loans from reputed banks. We can help you with the process and documentation.
                    </div>
                </div>
            </div>
            <div class="accordion-item mb-3">
                <h3 class="accordion-header" id="faqHeadingFour">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseFour" aria-expanded="false" aria-controls="faqCollapseFour">
                        Do you offer after-sales support?
                    </button>
                </h3>
                <div id="faqCollapseFour" class="accordion-collapse collapse" aria-labelledby="faqHeadingFour" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Absolutely! We are committed to supporting our clients even after the sale is complete. You can reach out anytime for assistance.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="contact-form-section py-5 bg-white position-relative">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#f6f9fc"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 data-aos="fade-up">Contact Our Experts</h2>
            <p data-aos="fade-up" data-aos-delay="200">Have a question or want to schedule a visit? Get in touch!</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="post" action="<?php echo BASE_URL; ?>/contact_submit.php" class="p-4 rounded-4 shadow bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-bold">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-bold">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label for="subject" class="form-label fw-bold">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject">
                        </div>
                        <div class="col-12">
                            <label for="message" class="form-label fw-bold">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5" aria-label="Send Message">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Signup Section -->
<section class="newsletter-section py-5 bg-light position-relative">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#e0ecff"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-6 text-center mb-4 mb-lg-0">
                <h2 class="fw-bold mb-3" data-aos="fade-up">Stay Updated!</h2>
                <p class="mb-4" data-aos="fade-up" data-aos-delay="100">Subscribe to our newsletter for the latest property updates, offers, and news.</p>
                <form class="row g-2 justify-content-center" method="post" action="<?php echo BASE_URL; ?>/newsletter_signup.php">
                    <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
                    <div class="col-8">
                        <input type="email" name="newsletter_email" class="form-control form-control-lg rounded-pill" placeholder="Enter your email" required aria-label="Your email">
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100" aria-label="Subscribe to Newsletter">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Visit Our Office Section -->
<section class="visit-office-section py-5 position-relative">
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 class="fw-bold" data-aos="fade-up">Visit Our Office</h2>
            <p class="lead text-secondary" data-aos="fade-up" data-aos-delay="100">We welcome you to our office for any queries or assistance</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="office-address bg-white rounded-4 shadow-lg p-4 mb-4">
                    <h5 class="mb-2">APS Dream Homes</h5>
                    <p class="mb-1">1st Floor, Plot No. 6, Sector 12A,</p>
                    <p class="mb-1">Dwarka, New Delhi, Delhi 110078</p>
                    <a href="https://maps.app.goo.gl/8k3JvW4QX8yQ9v6s8" target="_blank" class="btn btn-outline-primary btn-sm mt-2" aria-label="View on Google Maps">View on Google Maps</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Google Map Section -->
<section class="map-section py-5 bg-white position-relative">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#e0ecff"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 class="fw-bold" data-aos="fade-up">Visit Our Office</h2>
            <p class="mb-4" data-aos="fade-up" data-aos-delay="100">Find us at our Gorakhpur office or explore our service areas on the map below.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="ratio ratio-16x9 shadow rounded-4 overflow-hidden">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3606.942794383632!2d83.37912111501296!3d26.76055488320547!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3991440a8f7c2b2f%3A0x6b3b1b6b7f8b8d8c!2sGorakhpur%2C%20Uttar%20Pradesh!5e0!3m2!1sen!2sin!4v1681681681681!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog/News Preview Section -->
<section class="blog-preview-section py-5 bg-light position-relative">
    <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:60px; position:absolute; top:-1px; left:0; z-index:2;"><path d="M0,80 C480,0 960,80 1440,0 L1440,0 L0,0 Z" fill="#e0ecff"/></svg>
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 class="fw-bold" data-aos="fade-up">Latest News & Insights</h2>
            <p class="mb-4" data-aos="fade-up" data-aos-delay="100">Stay informed with the latest updates, tips, and trends in real estate.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            require_once __DIR__ . '/includes/functions.php';
            $news_items = get_latest_news(3);
            foreach ($news_items as $news):
            ?>
            <div class="col-md-4">
                <div class="news-card bg-light rounded-4 shadow-sm p-4 h-100">
                    <div class="mb-3">
                        <img src="<?php echo htmlspecialchars($news['image']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="img-fluid rounded-3 w-100" style="max-height:180px;object-fit:cover;" loading="lazy">
                    </div>
                    <h5><a href="<?php echo htmlspecialchars($news['url']); ?>" class="text-decoration-none" aria-label="Read more about <?php echo htmlspecialchars($news['title']); ?>"><?php echo htmlspecialchars($news['title']); ?></a></h5>
                    <p class="small text-secondary mb-2"><?php echo date('F j, Y', strtotime($news['date'])); ?></p>
                    <p class="mb-0"><?php echo htmlspecialchars($news['summary']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- User Feedback Widget -->
<section class="feedback-section py-5 bg-white">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="card shadow rounded-4 p-4">
          <h3 class="fw-bold mb-3 text-center"><i class="fas fa-star text-warning me-2"></i>We Value Your Feedback</h3>
          <form id="feedbackForm" method="post" action="/submit_feedback.php" autocomplete="off">
            <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
            <div class="mb-3 text-center">
              <label class="form-label mb-1">Your Rating</label><br>
              <div class="star-rating" style="font-size:2rem;">
                <span class="star" data-value="1" tabindex="0" aria-label="1 star">☆</span>
                <span class="star" data-value="2" tabindex="0" aria-label="2 stars">☆</span>
                <span class="star" data-value="3" tabindex="0" aria-label="3 stars">☆</span>
                <span class="star" data-value="4" tabindex="0" aria-label="4 stars">☆</span>
                <span class="star" data-value="5" tabindex="0" aria-label="5 stars">☆</span>
              </div>
              <input type="hidden" name="rating" id="ratingInput" value="">
            </div>
            <div class="mb-3">
              <label for="feedbackMessage" class="form-label">Your Feedback</label>
              <textarea class="form-control" id="feedbackMessage" name="message" rows="3" maxlength="300" required aria-required="true" aria-label="Your feedback"></textarea>
            </div>
            <div class="mb-3">
              <input type="text" class="form-control" name="name" placeholder="Your Name (optional)" maxlength="50" aria-label="Your name">
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary rounded-pill" aria-label="Submit Feedback">Submit Feedback</button>
            </div>
            <div id="feedbackStatus" class="text-center mt-3"></div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
// Star rating widget logic
const stars = document.querySelectorAll('.star-rating .star');
const ratingInput = document.getElementById('ratingInput');
stars.forEach(star => {
  star.addEventListener('click', function() {
    const rating = this.getAttribute('data-value');
    ratingInput.value = rating;
    stars.forEach(s => s.textContent = s.getAttribute('data-value') <= rating ? '★' : '☆');
  });
  star.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      this.click();
    }
  });
});

// AJAX submit for feedback form
const feedbackForm = document.getElementById('feedbackForm');
const feedbackStatus = document.getElementById('feedbackStatus');
feedbackForm.addEventListener('submit', function(e) {
  e.preventDefault();
  feedbackStatus.textContent = '';
  const formData = new FormData(feedbackForm);
  fetch('/submit_feedback.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    feedbackStatus.textContent = data.message;
    feedbackStatus.className = data.success ? 'text-success' : 'text-danger';
    if (data.success) feedbackForm.reset();
  })
  .catch(() => {
    feedbackStatus.textContent = 'Something went wrong. Please try again.';
    feedbackStatus.className = 'text-danger';
  });
});
</script>

<?php require_once(__DIR__ . '/includes/templates/new_footer.php'); ?>
</body>
</html>