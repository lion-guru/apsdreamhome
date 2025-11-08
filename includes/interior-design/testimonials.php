<!-- Testimonials Section -->
<section id="testimonials" class="testimonials-section">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2>Client Testimonials</h2>
            <p>What our clients say about our interior design services</p>
        </div>

        <div class="row">
            <?php foreach ($testimonials as $testimonial): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-card">
                    <div class="rating">
                        <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                        <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        <?php for ($i = $testimonial['rating']; $i < 5; $i++): ?>
                        <i class="far fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="testimonial-content">
                        <p><?php echo htmlspecialchars($testimonial['content']); ?></p>
                    </div>
                    <div class="testimonial-author">
                        <img src="<?php echo htmlspecialchars($testimonial['author_image']); ?>" 
                             alt="<?php echo htmlspecialchars($testimonial['author_name']); ?>" 
                             class="img-fluid rounded-circle" 
                             loading="lazy">
                        <div class="author-info">
                            <h4><?php echo htmlspecialchars($testimonial['author_name']); ?></h4>
                            <span><?php echo htmlspecialchars($testimonial['project_type']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="testimonials" class="btn btn-outline-primary">View All Testimonials</a>
        </div>
    </div>
</section>