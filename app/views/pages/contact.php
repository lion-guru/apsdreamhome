<?php
// Contact Page - APS Dream Home
?>

<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Contact Us</h1>
                <p class="lead mb-4">Get in touch with APS Dream Home for all your real estate needs. Visit our office or call us to find your dream property.</p>
            </div>
            <div class="col-lg-6">
                <div class="card bg-white bg-opacity-10">
                    <div class="card-body">
                        <h3 class="card-title">Get In Touch</h3>
                        <p>We're here to help you find your perfect property or answer any questions you may have.</p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Head Office:</strong><br>
                                1st floor singhariya chauraha, Kunraghat, deoria Road<br>
                                Gorakhpur, UP - 273008<br>
                                Phone: +91-7007444842
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Email:</strong><br>
                                <a href="mailto:info@apsdreamhomes.com">info@apsdreamhomes.com</a>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <form method="POST" action="<?php echo BASE_URL; ?>/contact" class="needs-validation">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <select id="subject" name="subject" class="form-control" required>
                                        <option value="">Select Subject</option>
                                        <option value="Property Inquiry">Property Inquiry</option>
                                        <option value="Schedule Visit">Schedule Visit</option>
                                        <option value="General Query">General Query</option>
                                        <option value="Complaint">Complaint</option>
                                        <option value="Feedback">Feedback</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4">Frequently Asked Questions</h2>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            What types of properties do you offer?
                        </h2>
                        <div class="accordion-content">
                            <p>We offer residential apartments, villas, commercial spaces, and plots in Gorakhpur, Lucknow, and across Uttar Pradesh.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            How can I schedule a property visit?
                        </h2>
                        <div class="accordion-content">
                            <p>You can call us at +91-7007444842 or fill out the contact form. Our team will get back to you to arrange a convenient time.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            Do you provide home loan assistance?
                        </h2>
                        <div class="accordion-content">
                            <p>Yes, we have partnerships with leading banks and financial institutions to help you with home loan assistance and documentation.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            Are your properties legally verified?
                        </h2>
                        <div class="accordion-content">
                            <p>Absolutely! All our properties undergo thorough legal verification to ensure they are free from disputes and have clear titles.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Office Locations</h3>
                        <div class="office-location">
                            <h4>Head Office - Gorakhpur</h4>
                            <address>
                                1st floor singhariya chauraha, Kunraghat, deoria Road<br>
                                Gorakhpur, UP - 273008<br>
                                Phone: +91-7007444842<br>
                                Email: info@apsdreamhomes.com
                            </address>
                        </div>
                        <div class="map-container">
                            <iframe 
                                src="https://maps.google.com/maps?q=Gorakhpur,+Uttar+Pradesh&output=embed" 
                                width="100%" 
                                height="200" 
                                style="border:0;" 
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
