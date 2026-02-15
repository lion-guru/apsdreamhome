<?php
// Customer Reviews with Database Integration
require_once __DIR__ . '/init.php';

use \App\Core\Database;

// Initialize database connection
$db = \App\Core\App::database();

// Set page metadata
$page_title = 'Customer Reviews - APS Dream Homes';
$page_description = 'Read what our customers say about APS Dream Homes properties and services';
$page_keywords = 'customer reviews, testimonials, APS Dream Homes, client feedback';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $property_id = $_POST['property_id'] ?? null;
    $rating = $_POST['rating'] ?? 0;
    $review_text = trim($_POST['review_text'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $errors = [];
    $success = false;

    // Validation
    if (empty($name)) $errors['name'] = 'Name is required';
    if (empty($email)) $errors['email'] = 'Email is required';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
    if (empty($review_text)) $errors['review_text'] = 'Review text is required';
    if ($rating < 1 || $rating > 5) $errors['rating'] = 'Valid rating is required';

    if (empty($errors)) {
        try {
            // Insert review
            $user_id = $_SESSION['user_id'] ?? null;
            $data = [
                'property_id' => $property_id,
                'user_id' => $user_id,
                'rating' => $rating,
                'review_text' => $review_text,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($db->insert('reviews', $data)) {
                $success = true;
            } else {
                $errors['submission'] = 'Failed to submit review. Please try again.';
            }

        } catch (Exception $e) {
            error_log("Review submission error: " . $e->getMessage());
            $errors['submission'] = 'System error. Please try again later.';
        }
    }
}

// Fetch average rating
try {
    $query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE status = 'approved'";
    $stats = $db->fetch($query);
} catch (Exception $e) {
    error_log("Stats fetch error: " . $e->getMessage());
    $stats = ['avg_rating' => 4.8, 'total_reviews' => 850];
}

// Fetch reviews from database
$reviews = [];
try {
    $query = "SELECT r.*, p.name as property_name, u.uname as user_name
              FROM reviews r
              JOIN properties p ON r.property_id = p.id
              JOIN user u ON r.user_id = u.uid
              WHERE r.status = 'approved'
              ORDER BY r.created_at DESC";
    $reviews = $db->fetchAll($query);

    if (empty($reviews)) {
        throw new Exception("No reviews found");
    }
} catch (Exception $e) {
    error_log("Review fetch error: " . $e->getMessage());
    // Fallback to sample data
    $reviews = [
        [
            'id' => 1,
            'rating' => 5,
            'review_text' => 'Excellent service and beautiful property. Very satisfied with APS Dream Homes.',
            'property_name' => 'APS Anant City',
            'user_name' => 'Rajesh Kumar',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
        ]
    ];
}

// Fetch properties for dropdown
$properties = [];
try {
    $query = "SELECT id, name FROM properties WHERE status = 'available' ORDER BY name";
    $properties = $db->fetchAll($query);

    if (empty($properties)) {
        throw new Exception("No properties found");
    }
} catch (Exception $e) {
    $properties = [
        ['id' => 1, 'name' => 'APS Anant City'],
        ['id' => 2, 'name' => 'APS Royal Enclave']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 120px 0 80px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff20" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,96C1248,75,1344,53,1392,42.7L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        /* Reviews Section */
        .reviews-section {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .review-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
        }

        .review-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .review-rating {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
        }

        .star {
            color: #ffd700;
            font-size: 1.2rem;
        }

        .star.empty {
            color: #ddd;
        }

        .review-text {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
            font-style: italic;
        }

        .review-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .author-info {
            flex: 1;
        }

        .author-name {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .author-details {
            color: #888;
            font-size: 0.9rem;
        }

        .review-date {
            color: #888;
            font-size: 0.85rem;
        }

        .review-verified {
            display: inline-block;
            background: var(--success-color);
            color: white;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 10px;
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 80px 0;
            color: white;
        }

        .stat-card {
            text-align: center;
            padding: 30px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #ffd700;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Add Review Section */
        .add-review-section {
            padding: 100px 0;
            background: white;
        }

        .review-form {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .rating-input {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .rating-star {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .rating-star:hover,
        .rating-star.active {
            color: #ffd700;
            transform: scale(1.1);
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
            outline: none;
        }

        .btn-submit-review {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 35px;
            border-radius: 50px;
            border: none;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit-review:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 50px;
        }

        .filter-btn {
            background: white;
            border: 2px solid #e0e0e0;
            padding: 10px 20px;
            border-radius: 50px;
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: var(--primary-color);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 60px;
            }

            .review-card {
                padding: 20px;
            }

            .review-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include_once __DIR__ . '/includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center hero-content" data-aos="fade-up">
                    <div class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill">
                        <i class="fas fa-star me-2"></i>Customer Reviews
                    </div>
                    <h1 class="display-3 fw-bold mb-4">What Our Clients Say</h1>
                    <p class="lead mb-4">
                        Read genuine reviews and testimonials from our satisfied customers.
                        Their trust and satisfaction are our biggest achievements.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="#reviews" class="btn btn-light btn-lg">
                            <i class="fas fa-comments me-2"></i>Read Reviews
                        </a>
                        <a href="#add-review" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-pen me-2"></i>Write Review
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card">
                        <div class="stat-number">4.8</div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card">
                        <div class="stat-number">950+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-card">
                        <div class="stat-number">850+</div>
                        <div class="stat-label">Reviews Posted</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-card">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews-section" id="reviews">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Customer Testimonials</h2>
                <p class="lead text-muted">Real experiences from real customers</p>
            </div>

            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up" data-aos-delay="100">
                <div class="d-flex flex-wrap justify-content-center">
                    <button class="filter-btn active" onclick="filterReviews('all')">All Reviews</button>
                    <button class="filter-btn" onclick="filterReviews('5')">5 Stars</button>
                    <button class="filter-btn" onclick="filterReviews('4')">4 Stars</button>
                    <button class="filter-btn" onclick="filterReviews('property')">Property Buyers</button>
                    <button class="filter-btn" onclick="filterReviews('investment')">Investors</button>
                    <button class="filter-btn" onclick="filterReviews('recent')">Recent</button>
                </div>
            </div>

            <!-- Customer Reviews -->
            <div class="row">
                <?php foreach ($reviews as $review): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo array_search($review, $reviews) * 100; ?>">
                        <div class="review-card">
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star star <?php echo $i <= $review['rating'] ? '' : 'empty'; ?>"></i>
                                <?php endfor; ?>
                            </div>

                            <p class="review-text">
                                "<?php echo h($review['review_text']); ?>"
                            </p>

                            <div class="review-author">
                                <div class="author-avatar">
                                    <?php echo strtoupper(substr($review['user_name'] ?? 'A', 0, 1)); ?>
                                </div>
                                <div class="author-info">
                                    <div class="author-name">
                                        <?php echo h($review['user_name'] ?? 'Anonymous'); ?>
                                        <span class="review-verified">
                                            <i class="fas fa-check-circle me-1"></i>Verified
                                        </span>
                                    </div>
                                    <div class="author-details">
                                        <?php echo h($review['property_name'] ?? 'General Review'); ?>
                                        <span class="review-date">
                                            <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Add Review Section -->
    <section class="add-review-section" id="add-review">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto" data-aos="fade-up">
                    <div class="text-center mb-5">
                        <h2 class="display-4 fw-bold mb-3">Share Your Experience</h2>
                        <p class="lead text-muted">Help others by sharing your experience with APS Dream Homes</p>
                    </div>

                    <form class="review-form" method="POST">
                        <?php if ($success ?? false): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Thank you! Your review has been submitted successfully and will be visible after approval.
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please fix the errors below.
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Your Name *</label>
                                <input type="text" class="form-control" name="name" required
                                       value="<?php echo h($name ?? ''); ?>"
                                       placeholder="Enter your full name">
                                <?php if (isset($errors['name'])): ?>
                                    <small class="text-danger"><?php echo $errors['name']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="email" required
                                       value="<?php echo h($email ?? ''); ?>"
                                       placeholder="your.email@example.com">
                                <?php if (isset($errors['email'])): ?>
                                    <small class="text-danger"><?php echo $errors['email']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Property *</label>
                                <select class="form-select" name="property_id" required>
                                    <option value="">Select Property</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?php echo $property['id']; ?>"
                                                <?php echo (isset($property_id) && $property_id == $property['id']) ? 'selected' : ''; ?>>
                                            <?php echo h($property['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Your Rating *</label>
                                <div class="rating-input">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star rating-star <?php echo (isset($rating) && $i <= $rating) ? 'active' : ''; ?>"
                                           data-rating="<?php echo $i; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingValue" value="<?php echo h($rating ?? ''); ?>" required>
                                <?php if (isset($errors['rating'])): ?>
                                    <small class="text-danger"><?php echo $errors['rating']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Your Review *</label>
                            <textarea class="form-control" rows="5" name="review_text" required
                                      placeholder="Share your experience with APS Dream Homes..."><?php echo h($review_text ?? ''); ?></textarea>
                            <?php if (isset($errors['review_text'])): ?>
                                <small class="text-danger"><?php echo $errors['review_text']; ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit_review" class="btn-submit-review">
                                <i class="fas fa-paper-plane me-2"></i>Submit Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include_once __DIR__ . '/includes/templates/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Rating System
        const ratingStars = document.querySelectorAll('.rating-star');
        const ratingValue = document.getElementById('ratingValue');

        ratingStars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                ratingValue.value = rating;
                updateRatingDisplay(rating);
            });

            star.addEventListener('mouseenter', function() {
                const hoverRating = parseInt(this.dataset.rating);
                updateRatingDisplay(hoverRating);
            });
        });

        document.querySelector('.rating-input').addEventListener('mouseleave', function() {
            const currentRating = parseInt(ratingValue.value) || 0;
            updateRatingDisplay(currentRating);
        });

        function updateRatingDisplay(rating) {
            ratingStars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        // Initialize rating display
        const initialRating = parseInt(ratingValue.value) || 0;
        updateRatingDisplay(initialRating);

        // Filter Reviews
        function filterReviews(filter) {
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Filter review cards
            document.querySelectorAll('.review-card').forEach(card => {
                const rating = card.dataset.rating;
                const category = card.dataset.category;

                let show = false;

                switch(filter) {
                    case 'all':
                        show = true;
                        break;
                    case '5':
                    case '4':
                        show = rating === filter;
                        break;
                    case 'property':
                    case 'investment':
                        show = category.includes(filter);
                        break;
                    case 'recent':
                        show = category.includes('recent');
                        break;
                }

                card.style.display = show ? 'block' : 'none';
            });
        }

        // Submit Review
        function submitReview(event) {
            event.preventDefault();

            if (selectedRating === 0) {
                alert('Please select a rating before submitting your review.');
                return;
            }

            // Get form data
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            data.rating = selectedRating;

            // Show success message
            alert('Thank you for your review! It will be published after verification.');

            // Reset form
            event.target.reset();
            selectedRating = 0;
            updateRatingDisplay();

            // Scroll to top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
