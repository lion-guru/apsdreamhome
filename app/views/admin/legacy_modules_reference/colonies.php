<?php
/**
 * APS Dream Home - Colony Showcase System
 * Display APS Dream Homes colonies and projects beautifully
 */

require_once 'admin_header.php';

// Sample colony data (replace with database queries)
$colonies = [
    [
        'id' => 1,
        'name' => 'APS Dream City Gorakhpur',
        'location' => 'Gorakhpur, Uttar Pradesh',
        'total_area' => '50 Acres',
        'developed_area' => '35 Acres',
        'total_plots' => 450,
        'available_plots' => 120,
        'starting_price' => '₹15,00,000',
        'completion_status' => 'Phase 2 Ongoing',
        'amenities' => ['Club House', 'Swimming Pool', 'Gym', 'Children Play Area', '24/7 Security', 'Power Backup'],
        'image' => '/assets/images/colonies/dream-city-gorakhpur.jpg',
        'description' => 'Premium residential colony with modern amenities and excellent connectivity.',
        'highlights' => ['Prime Location', 'Modern Infrastructure', 'Investment Opportunity']
    ],
    [
        'id' => 2,
        'name' => 'APS Royal Residency',
        'location' => 'Lucknow, Uttar Pradesh',
        'total_area' => '25 Acres',
        'developed_area' => '20 Acres',
        'total_plots' => 200,
        'available_plots' => 45,
        'starting_price' => '₹25,00,000',
        'completion_status' => 'Phase 1 Complete',
        'amenities' => ['Community Hall', 'Jogging Track', 'Landscaped Gardens', 'Security', 'Water Supply'],
        'image' => '/assets/images/colonies/royal-residency.jpg',
        'description' => 'Luxury residential project in the heart of Lucknow with world-class facilities.',
        'highlights' => ['Premium Location', 'High Appreciation', 'Modern Design']
    ],
    [
        'id' => 3,
        'name' => 'APS Green Valley',
        'location' => 'Kunraghat, Gorakhpur',
        'total_area' => '30 Acres',
        'developed_area' => '15 Acres',
        'total_plots' => 300,
        'available_plots' => 80,
        'starting_price' => '₹12,00,000',
        'completion_status' => 'Development Started',
        'amenities' => ['Green Spaces', 'Community Center', 'Playground', 'Security', 'Basic Infrastructure'],
        'image' => '/assets/images/colonies/green-valley.jpg',
        'description' => 'Eco-friendly residential colony with abundant green spaces and natural surroundings.',
        'highlights' => ['Eco-Friendly', 'Affordable Luxury', 'Natural Environment']
    ]
];

// Colony statistics for display
$colony_stats = [
    'total_colonies' => count($colonies),
    'total_area' => '105 Acres',
    'total_plots' => array_sum(array_column($colonies, 'total_plots')),
    'plots_sold' => array_sum(array_column($colonies, 'total_plots')) - array_sum(array_column($colonies, 'available_plots')),
    'cities_covered' => 3
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_description; ?>">
    <meta property="og:image" content="<?php echo $base_url ?? ''; ?>/assets/images/og-colonies.jpg">
    <meta property="og:type" content="website">

    <style>
        .hero-colonies {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .colony-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .colony-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .colony-image {
            height: 250px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            position: relative;
            overflow: hidden;
        }

        .colony-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .colony-overlay {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }

        .status-badge {
            background: rgba(255,255,255,0.9);
            color: #667eea;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .colony-content {
            padding: 25px;
        }

        .colony-title {
            color: #1a237e;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .colony-location {
            color: #667eea;
            font-size: 1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .colony-location i {
            margin-right: 8px;
        }

        .colony-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .colony-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .highlight-tag {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .colony-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .spec-item {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .spec-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
            display: block;
        }

        .spec-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        .colony-amenities {
            margin-top: 15px;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .amenity-item i {
            color: #28a745;
            margin-right: 10px;
            width: 16px;
        }

        .colony-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-view-plots {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-view-plots:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .stats-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 60px 0;
        }

        .stat-counter {
            text-align: center;
            padding: 30px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #667eea;
            display: block;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #333;
            font-weight: 600;
        }

        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .filter-buttons {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .filter-btn {
            border: 2px solid #667eea;
            background: transparent;
            color: #667eea;
            padding: 8px 20px;
            border-radius: 25px;
            margin: 5px;
            transition: all 0.3s ease;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: #667eea;
            color: white;
        }

        @media (max-width: 768px) {
            .colony-card {
                margin-bottom: 20px;
            }

            .colony-specs {
                grid-template-columns: repeat(2, 1fr);
            }

            .colony-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-colonies">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">
                        Our Premium <span class="text-warning">Colonies</span>
                    </h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Discover APS Dream Homes' exceptional real estate developments across Uttar Pradesh.
                        From luxury residential colonies to thriving commercial spaces, we create communities that inspire.
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="#colonies" class="btn btn-light btn-lg px-4 py-3">
                            <i class="fas fa-building me-2"></i>Explore Colonies
                        </a>
                        <a href="#contact" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-phone me-2"></i>Get Quote
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-counter" data-aos="fade-up">
                        <span class="stat-number" data-target="<?php echo $colony_stats['total_colonies']; ?>"><?php echo $colony_stats['total_colonies']; ?></span>
                        <span class="stat-label">Active Colonies</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-counter" data-aos="fade-up" data-aos-delay="100">
                        <span class="stat-number" data-target="<?php echo str_replace([' Acres', ','], ['', ''], $colony_stats['total_area']); ?>"><?php echo $colony_stats['total_area']; ?></span>
                        <span class="stat-label">Total Area</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-counter" data-aos="fade-up" data-aos-delay="200">
                        <span class="stat-number" data-target="<?php echo $colony_stats['total_plots']; ?>"><?php echo number_format($colony_stats['total_plots']); ?></span>
                        <span class="stat-label">Total Plots</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-counter" data-aos="fade-up" data-aos-delay="300">
                        <span class="stat-number" data-target="<?php echo $colony_stats['cities_covered']; ?>"><?php echo $colony_stats['cities_covered']; ?></span>
                        <span class="stat-label">Cities Covered</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="py-5">
        <div class="container">
            <div class="filter-buttons" data-aos="fade-up">
                <button class="filter-btn active" data-filter="all">All Colonies</button>
                <button class="filter-btn" data-filter="gorakhpur">Gorakhpur</button>
                <button class="filter-btn" data-filter="lucknow">Lucknow</button>
                <button class="filter-btn" data-filter="residential">Residential</button>
                <button class="filter-btn" data-filter="commercial">Commercial</button>
            </div>

            <!-- Colonies Grid -->
            <div class="row" id="colonies-container">
                <?php foreach ($colonies as $index => $colony): ?>
                    <div class="col-lg-4 col-md-6 colony-item" data-location="<?php echo strtolower(explode(',', $colony['location'])[0]); ?>" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100); ?>">
                        <div class="colony-card">
                            <div class="colony-image">
                                <img src="<?php echo $colony['image']; ?>" alt="<?php echo $colony['name']; ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none;">
                                    <i class="fas fa-city fa-3x"></i>
                                    <p><?php echo $colony['name']; ?></p>
                                </div>
                                <div class="colony-overlay">
                                    <span class="status-badge"><?php echo $colony['completion_status']; ?></span>
                                </div>
                            </div>

                            <div class="colony-content">
                                <h3 class="colony-title"><?php echo $colony['name']; ?></h3>

                                <div class="colony-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo $colony['location']; ?>
                                </div>

                                <p class="colony-description"><?php echo $colony['description']; ?></p>

                                <div class="colony-highlights">
                                    <?php foreach ($colony['highlights'] as $highlight): ?>
                                        <span class="highlight-tag"><?php echo $highlight; ?></span>
                                    <?php endforeach; ?>
                                </div>

                                <div class="colony-specs">
                                    <div class="spec-item">
                                        <span class="spec-value"><?php echo $colony['total_area']; ?></span>
                                        <span class="spec-label">Total Area</span>
                                    </div>
                                    <div class="spec-item">
                                        <span class="spec-value"><?php echo $colony['available_plots']; ?></span>
                                        <span class="spec-label">Available</span>
                                    </div>
                                    <div class="spec-item">
                                        <span class="spec-value"><?php echo $colony['starting_price']; ?></span>
                                        <span class="spec-label">Starting Price</span>
                                    </div>
                                </div>

                                <div class="colony-amenities">
                                    <h6><i class="fas fa-star me-2"></i>Amenities</h6>
                                    <?php foreach (array_slice($colony['amenities'], 0, 4) as $amenity): ?>
                                        <div class="amenity-item">
                                            <i class="fas fa-check"></i>
                                            <?php echo $amenity; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($colony['amenities']) > 4): ?>
                                        <small class="text-muted">+<?php echo count($colony['amenities']) - 4; ?> more amenities</small>
                                    <?php endif; ?>
                                </div>

                                <div class="colony-actions">
                                    <button class="btn btn-view-plots flex-fill">
                                        <i class="fas fa-eye me-2"></i>View Plots
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="showInterest('<?php echo $colony['id']; ?>')">
                                        <i class="fas fa-heart me-2"></i>I'm Interested
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="mb-4">Ready to Invest in Your Future?</h2>
                    <p class="lead mb-4">
                        Join thousands of happy customers who have found their dream properties with APS Dream Homes.
                        Our colonies offer the perfect blend of modern living and investment opportunities.
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="#contact" class="btn btn-warning btn-lg px-5 py-3">
                            <i class="fas fa-calendar me-2"></i>Schedule Visit
                        </a>
                        <a href="associate_registration.php" class="btn btn-outline-light btn-lg px-5 py-3">
                            <i class="fas fa-handshake me-2"></i>Become Associate
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Interest Modal -->
    <div class="modal fade" id="interestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Express Interest</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="interestForm">
                        <input type="hidden" id="colony_id" name="colony_id">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="3" placeholder="I'm interested in this colony. Please contact me with more details."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>Submit Interest
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                // Update active button
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Filter colonies
                const colonies = document.querySelectorAll('.colony-item');
                colonies.forEach(colony => {
                    if (filter === 'all') {
                        colony.style.display = 'block';
                    } else {
                        const location = colony.getAttribute('data-location');
                        if (location === filter || colony.classList.contains(filter)) {
                            colony.style.display = 'block';
                        } else {
                            colony.style.display = 'none';
                        }
                    }
                });
            });
        });

        // Interest modal
        function showInterest(colonyId) {
            document.getElementById('colony_id').value = colonyId;
            new bootstrap.Modal(document.getElementById('interestModal')).show();
        }

        // Form submission
        document.getElementById('interestForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Here you would send the data to your server
            alert('Thank you for your interest! We will contact you soon.');

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('interestModal')).hide();

            // Reset form
            this.reset();
        });

        // Counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');

            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target')) || parseInt(counter.textContent);
                const duration = 2000; // 2 seconds
                const increment = target / (duration / 16); // 60fps
                let current = 0;

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 16);
            });
        }

        // Initialize AOS
        AOS.init();

        // Animate counters when page loads
        window.addEventListener('load', animateCounters);
    </script>
</body>
</html>
<?php require_once 'admin_footer.php'; ?>
