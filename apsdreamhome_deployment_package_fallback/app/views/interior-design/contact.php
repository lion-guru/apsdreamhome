<!-- Contact Section -->
<section id="contact" class="contact-section">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2>Get in Touch</h2>
            <p>Schedule a consultation with our interior design team</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form id="interiorDesignContactForm" class="contact-form" data-aos="fade-up">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="name">Your Name</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   id="name" 
                                   required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="email">Your Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   name="email" 
                                   id="email" 
                                   required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" 
                                   class="form-control" 
                                   name="phone" 
                                   id="phone" 
                                   required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="projectType">Project Type</label>
                            <select class="form-control" 
                                    name="projectType" 
                                    id="projectType" 
                                    required>
                                <option value="">Select Project Type</option>
                                <option value="Residential">Residential</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Office">Office</option>
                                <option value="Retail">Retail</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="budget">Estimated Budget</label>
                        <select class="form-control" 
                                name="budget" 
                                id="budget" 
                                required>
                            <option value="">Select Budget Range</option>
                            <option value="Below $10,000">Below $10,000</option>
                            <option value="$10,000 - $25,000">$10,000 - $25,000</option>
                            <option value="$25,000 - $50,000">$25,000 - $50,000</option>
                            <option value="$50,000 - $100,000">$50,000 - $100,000</option>
                            <option value="Above $100,000">Above $100,000</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Project Details</label>
                        <textarea class="form-control" 
                                  name="message" 
                                  id="message" 
                                  rows="5" 
                                  required></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Schedule Consultation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>