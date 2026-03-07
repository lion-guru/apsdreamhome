<!-- Portfolio Section -->
<section id="portfolio" class="portfolio-section">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2>Our Portfolio</h2>
            <p>Explore our latest interior design projects</p>
        </div>

        <div class="row">
            <?php foreach ($portfolio as $project): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="portfolio-item">
                    <img src="<?php echo h($project['image']); ?>" 
                         alt="<?php echo h($project['title']); ?>" 
                         class="img-fluid" 
                         loading="lazy">
                    <div class="portfolio-info">
                        <h4><?php echo h($project['title']); ?></h4>
                        <p><?php echo h($project['category']); ?></p>
                        <a href="<?php echo h($project['image']); ?>" 
                           class="portfolio-lightbox" 
                           data-gallery="portfolio-gallery" 
                           title="<?php echo h($project['description']); ?>">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="gallery" class="btn btn-outline-primary">View All Projects</a>
        </div>
    </div>
</section>