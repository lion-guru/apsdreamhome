<?php
/**
 * Testimonials Index View
 */
$page_title = $page_title ?? 'Testimonials - APS Dream Home';
$page_description = $page_description ?? 'Read what our satisfied customers have to say about APS Dream Home';
$testimonials = $testimonials ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .testimonial-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
        }
        .testimonial-card:hover {
            transform: translateY(-5px);
        }
        .stars {
            color: #ffc107;
            font-size: 1.1rem;
        }
        .testimonial-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        .quote-icon {
            font-size: 2rem;
            color: #0d6efd;
            opacity: 0.3;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $base; ?>">
                <i class="fas fa-home me-2"></i>APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/properties">Properties</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?php echo $base; ?>/testimonials">Testimonials</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-3">What Our Customers Say</h1>
            <p class="lead">Real stories from real customers who found their dream home with us</p>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php if (!empty($testimonials)): ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="testimonial-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <i class="fas fa-quote-left quote-icon"></i>
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i > ($testimonial['rating'] ?? 5) ? '-half-alt' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-muted mb-4" style="font-style: italic;">"<?php echo htmlspecialchars($testimonial['content'] ?? ''); ?>"</p>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $testimonial['image'] ?? '/assets/images/default-avatar.jpg'; ?>" alt="<?php echo htmlspecialchars($testimonial['name'] ?? ''); ?>" class="testimonial-img me-3">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($testimonial['name'] ?? ''); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($testimonial['property'] ?? ''); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h4>No testimonials yet</h4>
                        <p class="text-muted">Be the first to share your experience!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submit Testimonial CTA -->
            <div class="text-center mt-5">
                <div class="card border-0 shadow-sm p-4">
                    <h4 class="mb-3">Share Your Experience</h4>
                    <p class="text-muted mb-3">Have you purchased a property with us? We'd love to hear your story!</p>
                    <a href="<?php echo $base; ?>/contact" class="btn btn-primary btn-lg">
                        <i class="fas fa-pen me-2"></i>Submit Your Testimonial
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
