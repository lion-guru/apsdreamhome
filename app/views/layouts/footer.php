<footer class="mt-5 bg-dark text-white py-5">
    <div class="container">
        <!-- Newsletter Signup -->
        <div class="row mb-5 pb-4 border-bottom border-secondary">
            <div class="col-lg-6">
                <h5 class="text-white mb-2"><i class="fas fa-envelope me-2"></i>Subscribe to Our Newsletter</h5>
                <p class="text-light mb-0">Get latest property updates and exclusive offers delivered to your inbox</p>
            </div>
            <div class="col-lg-6">
                <form action="<?php echo BASE_URL; ?>/subscribe" method="POST" class="d-flex gap-2" id="newsletterForm">
                    <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Subscribe
                    </button>
                </form>
                <div id="newsletterMessage" class="mt-2" style="display: none;"></div>
            </div>
        </div>
        
        <div class="row">
            <!-- Company Info -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-3 text-white">APS Dream Homes Pvt Ltd</h5>
                <p class="text-light">With over 15 years of excellence in real estate, we help families and businesses find their perfect properties across Gorakhpur, Lucknow, and Uttar Pradesh.</p>
                <div class="social-links mt-3">
                    <a href="https://www.facebook.com/apsdreamhomes/" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/apsdreamhomes/" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/@apsdreamhomes" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-youtube"></i></a>
                    <a href="https://www.linkedin.com/company/apsdreamhomes" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://wa.me/919277121112" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.justdial.com/Gorakhpur/Aps-Dream-Homes-Pvt-Ltd" target="_blank" class="text-white social-icon"><i class="fas fa-phone"></i></a>
                </div>
                <style>
                    .social-icon {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        background: rgba(255,255,255,0.1);
                        transition: all 0.3s ease;
                    }
                    .social-icon:hover {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        transform: translateY(-3px);
                    }
                </style>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="text-uppercase mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/" class="text-light text-decoration-none">Home</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/properties" class="text-light text-decoration-none">Properties</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/company/projects" class="text-light text-decoration-none">Projects</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/about" class="text-light text-decoration-none">About Us</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/contact" class="text-light text-decoration-none">Contact</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/admin/login" class="text-light text-decoration-none">Admin</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase mb-3">Our Services</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/services" class="text-light text-decoration-none">Property Sales</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/financial-services" class="text-light text-decoration-none">Home Loan Assistance</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/interior-design" class="text-light text-decoration-none">Interior Design</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/legal-services" class="text-light text-decoration-none">Legal Services</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/resell" class="text-light text-decoration-none">Resell Properties</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase mb-3">Contact Info</h6>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                        <span class="text-light">1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008</span>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <a href="tel:+919277121112" class="text-light text-decoration-none">+91 92771 21112</a>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <a href="tel:+917007444842" class="text-light text-decoration-none">+91 70074 44842</a>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-envelope me-2 text-primary"></i>
                        <a href="mailto:info@apsdreamhome.com" class="text-light text-decoration-none">info@apsdreamhome.com</a>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        <span class="text-light">Mon-Sat: 9:00 AM - 7:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-top border-secondary pt-4 mt-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-light">&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="<?php echo BASE_URL; ?>/privacy" class="text-light me-3 text-decoration-none">Privacy Policy</a>
                    <a href="<?php echo BASE_URL; ?>/legal/terms-conditions" class="text-light text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
// Newsletter Form Handler
document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const messageDiv = document.getElementById('newsletterMessage');
    const emailInput = form.querySelector('input[name="email"]');
    
    fetch('<?php echo BASE_URL; ?>/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(emailInput.value)
    })
    .then(response => response.json())
    .then(data => {
        messageDiv.style.display = 'block';
        if (data.success) {
            messageDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
            emailInput.value = '';
        } else {
            messageDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</span>';
        }
        setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
    })
    .catch(error => {
        messageDiv.style.display = 'block';
        messageDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Something went wrong. Please try again.</span>';
    });
});
</script>