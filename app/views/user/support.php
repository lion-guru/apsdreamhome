<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="fw-bold text-premium mb-2">Help & Support</h1>
            <p class="text-muted">We're here to help you with any queries or issues you might have.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Contact Cards -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4 hover-premium">
                <div class="card-body">
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-phone-alt fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Call Us</h5>
                    <p class="text-muted small">Our support team is available Mon-Sat, 10am - 6pm</p>
                    <a href="tel:+91XXXXXXXXXX" class="btn btn-premium rounded-pill px-4">+91 XXXXXXXXXX</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4 hover-premium">
                <div class="card-body">
                    <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-block mb-3">
                        <i class="fab fa-whatsapp fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">WhatsApp</h5>
                    <p class="text-muted small">Chat with us for quick queries and property updates</p>
                    <a href="https://wa.me/919277121112" class="btn btn-outline-success rounded-pill px-4">Chat Now</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4 hover-premium">
                <div class="card-body">
                    <div class="bg-info-subtle text-info p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-envelope fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Email Support</h5>
                    <p class="text-muted small">Send us your detailed queries or documents</p>
                    <a href="mailto:support@apsdreamhome.com" class="btn btn-outline-info rounded-pill px-4">Email Us</a>
                </div>
            </div>
        </div>

        <!-- Support Ticket Form -->
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-ticket-alt me-2 text-premium"></i>Raise a Support Ticket</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>support" method="POST">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="payment">Payment Issue</option>
                                    <option value="booking">Booking Query</option>
                                    <option value="kyc">KYC Verification</option>
                                    <option value="technical">Technical Issue</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Subject</label>
                                <input type="text" name="subject" class="form-control" placeholder="Brief summary" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Message</label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Describe your issue in detail..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-premium px-5 py-2 rounded-pill shadow-none">
                                    Submit Ticket
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
