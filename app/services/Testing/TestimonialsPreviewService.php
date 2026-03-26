<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Testimonials - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .testimonial-hero {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.9), rgba(15, 52, 96, 0.9)), 
                        url('https://via.placeholder.com/1920x400/1a1a2e/ffffff?text=Happy+Customers') center/cover;
            color: white;
            padding: 120px 0 80px;
            position: relative;
        }
        
        .testimonial-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://via.placeholder.com/1920x400/667eea/ffffff?text=Client+Stories') center/cover;
            opacity: 0.1;
            z-index: 0;
        }
        
        .testimonial-hero .container {
            position: relative;
            z-index: 1;
        }
        
        .testimonial-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            height: 100%;
            background: white;
            overflow: hidden;
        }
        
        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        
        .testimonial-content {
            padding: 40px 30px;
            position: relative;
        }
        
        .testimonial-content::before {
            content: '"';
            position: absolute;
            top: 10px;
            left: 20px;
            font-size: 4rem;
            color: #667eea;
            opacity: 0.2;
            font-family: Georgia, serif;
        }
        
        .testimonial-text {
            font-style: italic;
            color: #555;
            line-height: 1.8;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }
        
        .author-info h5 {
            margin: 0;
            color: #1a1a2e;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .author-property {
            color: #667eea;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .rating {
            display: flex;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        .rating i {
            color: #ffc107;
            font-size: 1.1rem;
        }
        
        .stats-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        
        .stat-card {
            text-align: center;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }
        
        .stat-number {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .section-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #1a1a2e, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .section-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 60px;
        }
        
        .filter-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 12px 25px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-2px);
        }
        
        .featured-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .testimonial-hero {
                padding: 80px 0 60px;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .testimonial-card {
                margin-bottom: 30px;
            }
            
            .filter-buttons {
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-building me-2"></i>APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Projects</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Team</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Testimonials</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="testimonial-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">
                        <i class="fas fa-heart me-3"></i>Client Testimonials
                    </h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        What our clients say about us - Real stories from real homeowners who found their dream properties with APS Dream Home.
                    </p>
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="#contact" class="btn btn-primary btn-lg" data-aos="fade-up" data-aos-delay="200">
                            <i class="fas fa-phone me-2"></i>Share Your Story
                        </a>
                        <a href="#properties" class="btn btn-outline-light btn-lg" data-aos="fade-up" data-aos-delay="300">
                            <i class="fas fa-home me-2"></i>Find Your Home
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
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-card" data-aos="fade-up">
                        <div class="stat-number">5000+</div>
                        <div class="stat-label">Happy Clients</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="stat-number">4.8</div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="stat-number">8</div>
                        <div class="stat-label">Years of Service</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" data-aos="fade-up">Client Stories</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Real experiences from our valued customers</p>
            </div>

            <!-- Filter Buttons -->
            <div class="filter-buttons" data-aos="fade-up" data-aos-delay="200">
                <button class="filter-btn active" onclick="filterTestimonials('all')">All Testimonials</button>
                <button class="filter-btn" onclick="filterTestimonials('residential')">Residential</button>
                <button class="filter-btn" onclick="filterTestimonials('commercial')">Commercial</button>
                <button class="filter-btn" onclick="filterTestimonials('investment')">Investment</button>
            </div>

            <div class="row g-4">
                <!-- Testimonial 1 -->
                <div class="col-lg-4 col-md-6 testimonial-item" data-category="residential" data-aos="fade-up">
                    <div class="testimonial-card">
                        <span class="featured-badge">Featured</span>
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="testimonial-text">
                                APS Dream Home made our dream of owning a 3BHK apartment in Gomti Nagar a reality. Their team was professional, transparent, and guided us through every step of the process. Highly recommended!
                            </p>
                            <div class="testimonial-author">
                                <img src="https://via.placeholder.com/60x60/667eea/ffffff?text=RS" alt="Rahul Sharma" class="author-avatar">
                                <div class="author-info">
                                    <h5>Rahul Sharma</h5>
                                    <p class="author-property">3BHK Apartment in Gomti Nagar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="col-lg-4 col-md-6 testimonial-item" data-category="commercial" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="testimonial-text">
                                Excellent service for our commercial space requirements. The team understood our business needs and found us the perfect office space in Hazratganj. Great location and value for money!
                            </p>
                            <div class="testimonial-author">
                                <img src="https://via.placeholder.com/60x60/764ba2/ffffff?text=PK" alt="Priya Khanna" class="author-avatar">
                                <div class="author-info">
                                    <h5>Priya Khanna</h5>
                                    <p class="author-property">Office Space in Hazratganj</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="col-lg-4 col-md-6 testimonial-item" data-category="investment" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <p class="testimonial-text">
                                Great investment opportunity! APS Dream Home helped me identify and purchase two properties that have appreciated significantly. Their market insights are invaluable for real estate investors.
                            </p>
                            <div class="testimonial-author">
                                <img src="https://via.placeholder.com/60x60/28a745/ffffff?text=AV" alt="Amit Verma" class="author-avatar">
                                <div class="author-info">
                                    <h5>Amit Verma</h5>
                                    <p class="author-property">Investment Properties</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 4 -->
                <div class="col-lg-4 col-md-6 testimonial-item" data-category="residential" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="testimonial-text">
                                The team at APS Dream Home is exceptional! They helped us find our first home within our budget. The entire process was smooth, from property search to documentation. Thank you for making our dream come true!
                            </p>
                            <div class="testimonial-author">
                                <img src="https://via.placeholder.com/60x60/dc3545/ffffff?text=SG" alt="Sneha Gupta" class="author-avatar">
                                <div class="author-info">
                                    <h5>Sneha Gupta</h5>
                                    <p class="author-property">2BHK Apartment in Indira Nagar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 5 -->
                <div class="col-lg-4 col-md-6 testimonial-item" data-category="residential" data-aos="fade-up" data-aos-delay="400">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <p class="testimonial-text">
                                Professional and reliable service. They understood our requirements perfectly and showed us properties that matched our needs. The negotiation process was handled expertly. Very satisfied with the service.
                            </p>
                            <div class="testimonial-author">
                                <img src="https://via.placeholder.com/60x60/6f42c1/ffffff?text=RK" alt="Rajesh Kumar" class="author-avatar">
                                <div class="author-info">
                                    <h5>Rajesh Kumar</h5>
                                    <p class="author-property">Villa in Gomti Nagar Extension</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 6 -->
                <div class="col-lg-4 col-md-6 testimonial-item" data-category="commercial" data-aos="fade-up" data-aos-delay="500">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="testimonial-text">
                                Outstanding service for our retail space requirements. Found us the perfect location with great footfall. The team's knowledge of commercial real estate is impressive. Highly recommend their services!
                            </p>
                            <div class="testimonial-author">
                                <img src="https://via.placeholder.com/60x60/20c997/ffffff?text=MP" alt="Meera Patel" class="author-avatar">
                                <div class="author-info">
                                    <h5>Meera Patel</h5>
                                    <p class="author-property">Retail Space in Mahanagar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Share Your Experience</h2>
            <p class="lead mb-4">Have you worked with APS Dream Home? We'd love to hear your story!</p>
            <div class="d-flex justify-content-center gap-3">
                <button class="btn btn-light btn-lg">
                    <i class="fas fa-pen me-2"></i>Write Review
                </button>
                <button class="btn btn-outline-light btn-lg">
                    <i class="fas fa-video me-2"></i>Video Testimonial
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>APS Dream Home</h5>
                    <p>Your trusted partner in real estate since 2008</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex gap-3 justify-content-md-end">
                        <a href="#" class="text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-3">
            <div class="text-center">
                <p class="mb-0">&copy; 2026 APS Dream Home. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Filter testimonials
        function filterTestimonials(category) {
            const items = document.querySelectorAll('.testimonial-item');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Update active button
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.toLowerCase().includes(category) || 
                    (category === 'all' && btn.textContent.includes('All'))) {
                    btn.classList.add('active');
                }
            });
            
            // Show/hide testimonials
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 100);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        }
    </script>
</body>
</html>
