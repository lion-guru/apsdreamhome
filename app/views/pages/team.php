<?php

/**
 * Enhanced Team Page - APS Dream Home
 * Modern UI/UX showcasing company team members
 */

// Set page title and description for layout
$page_title = 'Our Team - APS Dream Home';
$page_description = 'Meet the experienced team behind APS Dream Home. Our real estate professionals are dedicated to helping you find your perfect property.';

?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        --info-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    }

    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #2c3e50;
        background: #f8f9fa;
    }

    /* Modern Hero Section */
    .hero-team {
        background: var(--primary-gradient);
        color: white;
        padding: 5rem 0;
        position: relative;
        overflow: hidden;
    }

    .hero-team::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><circle cx="500" cy="500" r="300" fill="rgba(255,255,255,0.05)"/></svg>');
        animation: float 20s ease-in-out infinite;
    }

    .hero-title {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 800;
        margin-bottom: 1.5rem;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .hero-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 2rem;
    }

    /* Enhanced Buttons */
    .btn-team-cta {
        border-radius: 25px;
        padding: 1rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-contact {
        background: white;
        color: #667eea;
    }

    .btn-contact:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
    }

    .btn-join {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .btn-join:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
    }

    /* Team Member Cards */
    .team-member {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .team-member::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--primary-gradient);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .team-member:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .team-member:hover::before {
        opacity: 0.05;
    }

    .member-avatar {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .member-avatar img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid #667eea;
        transition: all 0.3s ease;
    }

    .team-member:hover .member-avatar img {
        transform: scale(1.1);
    }

    .social-links {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 0.5rem;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .team-member:hover .social-links {
        opacity: 1;
    }

    .social-link {
        width: 40px;
        height: 40px;
        background: #667eea;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .social-link:hover {
        background: #764ba2;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Stats Section */
    .stats-section {
        background: white;
        padding: 4rem 0;
    }

    .stats-counter {
        font-size: 3rem;
        font-weight: 800;
        color: #667eea;
        margin-bottom: 0.5rem;
    }

    /* Values Section */
    .value-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        height: 100%;
    }

    .value-card:hover {
        transform: translateY(-5px);
    }

    .value-icon {
        width: 80px;
        height: 80px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    /* CTA Section */
    .cta-team {
        background: var(--primary-gradient);
        color: white;
        padding: 4rem 0;
        text-align: center;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .floating {
        animation: float 3s ease-in-out infinite;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-team {
            padding: 3rem 0;
        }

        .hero-title {
            font-size: 2.5rem;
        }

        .stats-counter {
            font-size: 2rem;
        }
    }
</style>
</head>

<body>

    <!-- Hero Section -->
    <section class="hero-team">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="hero-title" data-aos="fade-up">
                        <i class="fas fa-users me-3"></i>
                        Meet Our Team
                    </h1>
                    <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="100">
                        Our experienced team of real estate professionals is dedicated to helping you find your perfect property and achieve your real estate goals.
                    </p>
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="#contact" class="btn btn-team-cta btn-contact" data-aos="fade-up" data-aos-delay="200">Get In Touch</a>
                        <a href="#careers" class="btn btn-team-cta btn-join" data-aos="fade-up" data-aos-delay="300">Join Our Team</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stats-item">
                        <div class="stats-counter" id="teamMembersCount">50</div>
                        <p class="mb-0">Team Members</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-item">
                        <div class="stats-counter" id="yearsExperienceCount">75</div>
                        <p class="mb-0">Years Combined Experience</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stats-item">
                        <div class="stats-counter" id="propertiesSoldCount">2500</div>
                        <p class="mb-0">Properties Sold</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="stats-item">
                        <div class="stats-counter" id="clientSatisfactionCount">98</div>
                        <p class="mb-0">Client Satisfaction %</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Our Leadership Team</h2>
            <div class="row g-4">
                <?php if (!empty($team_members)): ?>
                    <?php foreach ($team_members as $index => $member): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                            <div class="team-member">
                                <div class="member-avatar">
                                    <img src="<?php echo htmlspecialchars($member->photo ? BASE_URL . $member->photo : 'https://via.placeholder.com/150/667eea/ffffff?text=' . substr($member->name, 0, 2)); ?>"
                                        alt="<?php echo htmlspecialchars($member->name); ?>"
                                        onerror="this.src='https://via.placeholder.com/150/667eea/ffffff?text=<?php echo substr($member->name, 0, 2); ?>'">
                                    <div class="social-links">
                                        <?php if (!empty($member->linkedin)): ?>
                                            <a href="<?php echo htmlspecialchars($member->linkedin); ?>" class="social-link" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <h4 class="member-name"><?php echo htmlspecialchars($member->name); ?></h4>
                                <p class="member-role text-primary"><?php echo htmlspecialchars($member->position); ?></p>
                                <p class="member-description">
                                    <?php echo htmlspecialchars($member->bio ?? ''); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback static content -->
                    <div class="col-12 text-center">
                        <p>Team members are currently being updated. Please check back soon.</p>
                    </div>
                <?php endif; ?>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-handshake fa-3x text-primary"></i>
                        </div>
                        <h5>Integrity</h5>
                        <p class="text-muted">
                            We conduct all our business with honesty, transparency, and ethical practices.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="value-card text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-users fa-3x text-success"></i>
                        </div>
                        <h5>Client Focus</h5>
                        <p class="text-muted">
                            Our clients' needs and satisfaction are at the center of everything we do.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="value-card text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-chart-line fa-3x text-warning"></i>
                        </div>
                        <h5>Excellence</h5>
                        <p class="text-muted">
                            We strive for excellence in every service we provide and every interaction we have.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="value-card text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-lightbulb fa-3x text-info"></i>
                        </div>
                        <h5>Innovation</h5>
                        <p class="text-muted">
                            We embrace new technologies and innovative approaches to serve our clients better.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section py-5" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h3 class="text-white mb-4">
                        <i class="fas fa-phone me-2"></i>
                        Ready to Work with Our Expert Team?
                    </h3>
                    <p class="text-white-50 mb-4">
                        Contact us today and experience the APS Dream Home difference. Our team is ready to help you achieve your real estate goals.
                    </p>
                    <div class="cta-buttons">
                        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-light btn-lg me-3">
                            <i class="fas fa-phone me-2"></i>Contact Our Team
                        </a>
                        <a href="<?php echo BASE_URL; ?>about" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-info-circle me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .team-member {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .member-avatar {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 4px solid #f8f9fa;
        }

        .social-links {
            position: absolute;
            bottom: 0;
            right: 0;
            display: flex;
            gap: 0.5rem;
        }

        .social-link {
            width: 32px;
            height: 32px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .social-link:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .social-link:nth-child(1):hover {
            background: #3b5998;
        }

        .social-link:nth-child(2):hover {
            background: #1da1f2;
        }

        .social-link:nth-child(3):hover {
            background: #0077b5;
        }

        .member-name {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .member-role {
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .member-description {
            color: #6c757d;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .value-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .value-icon {
            margin-bottom: 1rem;
        }

        .value-card h5 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .cta-buttons .btn {
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media (max-width: 768px) {
            .team-member {
                padding: 1.5rem;
            }

            .member-avatar {
                width: 100px;
                height: 100px;
            }

            .social-links {
                position: static;
                justify-content: center;
                margin-top: 1rem;
            }
        }
    </style>