<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .team-member {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .team-member:hover {
            transform: translateY(-10px);
        }
        .member-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 25px;
            border: 5px solid #667eea;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .member-social {
            margin-top: 20px;
        }
        .member-social a {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            margin: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .member-social a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        .values-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 80px 0;
        }
        .value-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            height: 100%;
        }
        .value-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 25px;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">Our Leadership Team</h1>
                    <p class="lead mb-4">
                        Meet the experienced professionals who drive our vision and ensure
                        exceptional results for every project and client.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Leadership Team -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Leadership Team</h2>
                <p class="lead text-muted">Visionary leaders with decades of combined experience</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="team-member">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                             alt="CEO" class="member-image">
                        <h5>Abhay Pratap Singh</h5>
                        <p class="text-primary mb-2">Founder & CEO</p>
                        <p class="text-muted mb-3">Visionary leader with 15+ years of experience in real estate development and business management. Founded APS Dream Homes in 2016.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="team-member">
                        <img src="https://images.unsplash.com/photo-1494790108755-2616b332c9e9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                             alt="COO" class="member-image">
                        <h5>Priya Sharma</h5>
                        <p class="text-primary mb-2">Chief Operating Officer</p>
                        <p class="text-muted mb-3">Operations expert ensuring seamless project execution and quality control across all developments. MBA in Operations Management.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="team-member">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                             alt="Head of Sales" class="member-image">
                        <h5>Rahul Kumar</h5>
                        <p class="text-primary mb-2">Head of Sales & Marketing</p>
                        <p class="text-muted mb-3">Marketing strategist driving customer acquisition and brand building initiatives across multiple channels. 10+ years in real estate marketing.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="team-member">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                             alt="CTO" class="member-image">
                        <h5>Anita Verma</h5>
                        <p class="text-primary mb-2">Chief Technical Officer</p>
                        <p class="text-muted mb-3">Technology leader overseeing digital transformation and innovation in construction processes. PhD in Civil Engineering.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="team-member">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                             alt="CFO" class="member-image">
                        <h5>Vikash Gupta</h5>
                        <p class="text-primary mb-2">Chief Financial Officer</p>
                        <p class="text-muted mb-3">Financial strategist managing company finances, investments, and funding. Chartered Accountant with 12+ years experience.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="team-member">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                             alt="HR Head" class="member-image">
                        <h5>Meera Patel</h5>
                        <p class="text-primary mb-2">Head of Human Resources</p>
                        <p class="text-muted mb-3">HR professional fostering company culture and employee development. Masters in Human Resource Management.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Company Values -->
    <section class="values-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Core Values</h2>
                <p class="lead text-muted">Principles that guide everything we do</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Integrity</h5>
                        <p class="text-muted">We conduct business with utmost honesty and transparency, building trust with every interaction and decision.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h5>Excellence</h5>
                        <p class="text-muted">We strive for excellence in every project, maintaining the highest standards of quality and professionalism.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Customer Focus</h5>
                        <p class="text-muted">Our customers are at the heart of everything we do. We listen, understand, and deliver beyond expectations.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h5>Innovation</h5>
                        <p class="text-muted">We embrace innovation and technology to improve our processes and deliver better solutions to our clients.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Teamwork</h5>
                        <p class="text-muted">We believe in the power of collaboration and support each other to achieve common goals and success.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5>Community</h5>
                        <p class="text-muted">We are committed to giving back to the community and contributing to the development of sustainable neighborhoods.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
