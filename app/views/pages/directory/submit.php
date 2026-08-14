<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services">Services Directory</a></li>
            <li class="breadcrumb-item active">Submit Your Business</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="mb-2"><i class="fas fa-plus-circle text-primary me-2"></i>List Your Business/Service</h2>
                    <p class="text-muted mb-4">Get discovered by thousands of people looking for real estate services in your area. Free listing!</p>

                    <form method="POST">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Business/Service Name *</label>
                                <input type="text" name="business_name" class="form-control" required placeholder="e.g. Sharma Plumbing Works">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Owner Name</label>
                                <input type="text" name="owner_name" class="form-control" placeholder="Your name">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Tell people about your services, experience, area of work..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Phone *</label>
                                <input type="text" name="phone" class="form-control" required placeholder="9876543210">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp" class="form-control" placeholder="9876543210">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="your@email.com">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" placeholder="e.g. Patna">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Experience (years)</label>
                                <input type="number" name="experience_years" class="form-control" placeholder="5">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>

                        <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i>Your listing will be reviewed by our team before being published.</p>

                        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-paper-plane me-2"></i>Submit Listing</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
