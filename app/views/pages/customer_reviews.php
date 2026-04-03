<?php
// Customer Reviews Page - APS Dream Homes
$page_title = 'Customer Reviews - APS Dream Homes';

// Sample reviews data
$reviews = [
    ['name' => 'Rajesh Kumar', 'rating' => 5, 'text' => 'Excellent service and beautiful property. Very satisfied with APS Dream Homes.', 'property' => 'APS Anant City', 'date' => '2026-03-15'],
    ['name' => 'Priya Sharma', 'rating' => 5, 'text' => 'Professional team, transparent dealings. Highly recommend for property investment.', 'property' => 'Suyoday Colony', 'date' => '2026-03-10'],
    ['name' => 'Amit Verma', 'rating' => 4, 'text' => 'Great experience overall. Property delivered on time with all promised amenities.', 'property' => 'Raghunath Nagri', 'date' => '2026-02-28'],
    ['name' => 'Sunita Gupta', 'rating' => 5, 'text' => 'Best real estate company in Gorakhpur. Very trustworthy and professional.', 'property' => 'Braj Radha Nagri', 'date' => '2026-02-20'],
    ['name' => 'Vikram Singh', 'rating' => 5, 'text' => 'Smooth process from booking to possession. Excellent customer support.', 'property' => 'Awadhpuri', 'date' => '2026-02-15'],
    ['name' => 'Meera Pandey', 'rating' => 4, 'text' => 'Good investment opportunity. Property value has increased significantly.', 'property' => 'Budh Bihari Colony', 'date' => '2026-02-10'],
];
?>

<section class="py-5 bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Customer Reviews</h1>
        <p class="lead">What our clients say about us</p>
        <div class="d-flex justify-content-center gap-4 mt-4">
            <div class="text-center">
                <div class="h2 fw-bold">4.8</div>
                <small>Average Rating</small>
            </div>
            <div class="text-center">
                <div class="h2 fw-bold">950+</div>
                <small>Happy Customers</small>
            </div>
            <div class="text-center">
                <div class="h2 fw-bold">98%</div>
                <small>Satisfaction</small>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <?php foreach ($reviews as $review): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-muted fst-italic">"<?php echo htmlspecialchars($review['text']); ?>"</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:45px;height:45px;font-weight:bold;">
                                <?= strtoupper(substr($review['name'], 0, 1)) ?>
                            </div>
                            <div class="ms-3">
                                <strong><?= htmlspecialchars($review['name']) ?></strong>
                                <div class="small text-muted"><?= htmlspecialchars($review['property']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-4">Share Your Experience</h3>
                        <form method="POST" action="/apsdreamhome/contact">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Property</label>
                                <select class="form-select" name="property">
                                    <option value="">Select Property</option>
                                    <option>APS Anant City</option>
                                    <option>Suyoday Colony</option>
                                    <option>Raghunath Nagri</option>
                                    <option>Braj Radha Nagri</option>
                                    <option>Awadhpuri</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star fs-4 text-muted rating-star" data-rating="<?= $i ?>" style="cursor:pointer"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your Review</label>
                                <textarea class="form-control" rows="4" name="review" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Submit Review</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.rating-star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.querySelectorAll('.rating-star').forEach((s, i) => {
            s.classList.toggle('text-warning', i < rating);
            s.classList.toggle('text-muted', i >= rating);
        });
    });
});
</script>
