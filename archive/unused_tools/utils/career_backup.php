<?php
// Careers Page with Database Integration
require_once __DIR__ . '/config/unified_config.php';
require_once __DIR__ . '/../../app/helpers.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/includes/db_config.php';

// Set page metadata
$page_title = 'Careers - APS Dream Homes';
$page_description = 'Join the APS Dream Homes team. Explore career opportunities in real estate';
$page_keywords = 'careers, jobs, APS Dream Homes, recruitment, real estate careers';

// Handle job application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $career_id = $_POST['career_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $cover_letter = trim($_POST['cover_letter'] ?? '');

    $errors = [];
    $success = false;

    // Validation
    if (empty($name)) $errors['name'] = 'Name is required';
    if (empty($email)) $errors['email'] = 'Email is required';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
    if (empty($phone)) $errors['phone'] = 'Phone number is required';
    if (empty($cover_letter)) $errors['cover_letter'] = 'Cover letter is required';

    // Handle file upload
    $resume_path = null;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['pdf', 'doc', 'docx'];
        $file_extension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            $errors['resume'] = 'Only PDF, DOC, and DOCX files are allowed';
        } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors['resume'] = 'File size must be less than 5MB';
        } else {
            $upload_dir = __DIR__ . '/uploads/resumes/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename = uniqid() . '_' . basename($_FILES['resume']['name']);
            $resume_path = $upload_dir . $filename;

            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
                $errors['resume'] = 'Failed to upload resume';
            } else {
                $resume_path = 'uploads/resumes/' . $filename; // Store relative path
            }
        }
    } else {
        $errors['resume'] = 'Resume is required';
    }

    if (empty($errors)) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if ($conn->connect_error) {
                throw new Exception("Database connection failed");
            }

            // Insert application
            $query = "INSERT INTO applications (career_id, name, email, phone, resume_path, cover_letter, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isssss", $career_id, $name, $email, $phone, $resume_path, $cover_letter);

            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors['submission'] = 'Failed to submit application. Please try again.';
            }

            $stmt->close();
            $conn->close();

        } catch (Exception $e) {
            error_log("Application submission error: " . $e->getMessage());
            $errors['submission'] = 'System error. Please try again later.';
        }
    }
}

// Fetch careers from database
$careers = [];
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    $query = "SELECT * FROM careers WHERE status = 'active' ORDER BY created_at DESC";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $careers[] = $row;
        }
    }

    $conn->close();
} catch (Exception $e) {
    error_log("Careers fetch error: " . $e->getMessage());
    // Fallback to sample data
    $careers = [
        [
            'id' => 1,
            'title' => 'Senior Real Estate Agent',
            'department' => 'Sales',
            'location' => 'Gorakhpur',
            'type' => 'full-time',
            'description' => 'We are looking for experienced real estate agents to join our team.',
            'requirements' => '3+ years experience in real estate sales',
            'salary_range' => '₹25,000 - 50,000 per month'
        ],
        [
            'id' => 2,
            'title' => 'Marketing Executive',
            'department' => 'Marketing',
            'location' => 'Gorakhpur',
            'type' => 'full-time',
            'description' => 'Marketing executive to promote our properties and manage campaigns.',
            'requirements' => '2+ years experience in marketing',
            'salary_range' => '₹20,000 - 35,000 per month'
        ]
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

        /* Jobs Section */
        .jobs-section {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .job-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            border-left: 5px solid var(--primary-color);
        }

        .job-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }

        .job-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
        }

        .job-department {
            display: inline-block;
            background: linear-gradient(135deg, var(--info-color), var(--primary-color));
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .job-type {
            background: var(--success-color);
            color: white;
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .job-location {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
        }

        .job-location i {
            color: var(--primary-color);
            margin-right: 8px;
        }

        .job-description {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .job-requirements {
            margin-bottom: 25px;
        }

        .job-requirements h4 {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 15px;
        }

        .job-requirements ul {
            list-style: none;
            padding: 0;
        }

        .job-requirements li {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #555;
        }

        .job-requirements li i {
            color: var(--success-color);
            margin-right: 10px;
            font-size: 0.9rem;
        }

        .job-action {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .job-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            color: white;
        }

        /* Culture Section */
        .culture-section {
            padding: 100px 0;
            background: white;
        }

        .culture-item {
            text-align: center;
            padding: 40px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .culture-item:hover {
            background: #f8f9fa;
            transform: translateY(-5px);
        }

        .culture-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .culture-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .culture-description {
            color: #666;
            max-width: 300px;
            margin: 0 auto;
        }

        /* Benefits Section */
        .benefits-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 100px 0;
        }

        .benefit-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
            height: 100%;
        }

        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .benefit-icon {
            font-size: 3rem;
            color: var(--success-color);
            margin-bottom: 20px;
        }

        .benefit-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .benefit-description {
            color: #666;
        }

        /* Application Form */
        .application-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 100px 0;
            color: white;
        }

        .application-form {
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .form-control, .form-select {
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 1rem;
        }

        .form-control::placeholder, .form-select {
            color: rgba(255,255,255,0.8);
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.3);
            border-color: white;
            outline: none;
            color: white;
        }

        .form-control option {
            color: var(--dark-color);
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .btn-application {
            background: white;
            color: var(--primary-color);
            padding: 15px 35px;
            border-radius: 50px;
            border: none;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-application:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 60px;
            }

            .job-card {
                padding: 30px 20px;
            }

            .job-header {
                flex-direction: column;
                gap: 15px;
            }

            .application-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include_once __DIR__ . '/includes/components/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center hero-content" data-aos="fade-up">
                    <div class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill">
                        <i class="fas fa-briefcase me-2"></i>Career Opportunities
                    </div>
                    <h1 class="display-3 fw-bold mb-4">Join Our Team</h1>
                    <p class="lead mb-4">
                        Build a rewarding career with APS Dream Homes. We're looking for passionate individuals
                        who want to grow in the dynamic real estate industry in Gorakhpur.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="#jobs" class="btn btn-light btn-lg">
                            <i class="fas fa-search me-2"></i>View Openings
                        </a>
                        <a href="#culture" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-users me-2"></i>Our Culture
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Jobs Section -->
    <section class="jobs-section" id="jobs">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Current Openings</h2>
                <p class="lead text-muted">Explore exciting career opportunities with APS Dream Homes</p>
            </div>

            <!-- Job Listings -->
            <div class="row">
                <?php foreach ($careers as $career): ?>
                    <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo array_search($career, $careers) * 100; ?>">
                        <div class="job-card">
                            <div class="job-header">
                                <div>
                                    <span class="job-department"><?php echo h($career['department']); ?></span>
                                    <h3 class="job-title"><?php echo h($career['title']); ?></h3>
                                    <div class="job-location">
                                        <i class="fas fa-map-marker-alt"></i><?php echo h($career['location']); ?>
                                    </div>
                                </div>
                                <span class="job-type"><?php echo h(ucfirst($career['type'])); ?></span>
                            </div>

                            <p class="job-description">
                                <?php echo h($career['description']); ?>
                            </p>

                            <div class="job-requirements">
                                <h4>Requirements:</h4>
                                <ul>
                                    <li><i class="fas fa-check"></i><?php echo h($career['requirements']); ?></li>
                                </ul>
                            </div>

                            <?php if (!empty($career['salary_range'])): ?>
                                <div class="job-salary">
                                    <strong>Salary:</strong> <?php echo h($career['salary_range']); ?>
                                </div>
                            <?php endif; ?>

                            <a href="#application" class="job-action" onclick="setCareerId(<?php echo $career['id']; ?>, '<?php echo h($career['title']); ?>')">
                                <i class="fas fa-paper-plane me-2"></i>Apply Now
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <span class="job-department">Marketing</span>
                                <h3 class="job-title">Digital Marketing Executive</h3>
                                <div class="job-location">
                                    <i class="fas fa-map-marker-alt"></i>Gorakhpur, Uttar Pradesh
                                </div>
                            </div>
                            <span class="job-type">Full Time</span>
                        </div>
                        <p class="job-description">
                            Join our marketing team to promote APS Dream Homes properties and services.
                            You'll manage social media, create content, and run digital marketing campaigns.
                        </p>
                        <div class="job-requirements">
                            <h4>Key Requirements:</h4>
                            <ul>
                                <li><i class="fas fa-check"></i>2+ years in digital marketing</li>
                                <li><i class="fas fa-check"></i>Social media expertise</li>
                                <li><i class="fas fa-check"></i>Content creation skills</li>
                                <li><i class="fas fa-check"></i>Marketing degree preferred</li>
                            </ul>
                        </div>
                        <a href="#application" class="job-action">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <span class="job-department">Operations</span>
                                <h3 class="job-title">Property Manager</h3>
                                <div class="job-location">
                                    <i class="fas fa-map-marker-alt"></i>Gorakhpur, Uttar Pradesh
                                </div>
                            </div>
                            <span class="job-type">Full Time</span>
                        </div>
                        <p class="job-description">
                            Manage property portfolios and ensure excellent tenant relationships.
                            You'll handle property maintenance, rent collection, and client services.
                        </p>
                        <div class="job-requirements">
                            <h4>Key Requirements:</h4>
                            <ul>
                                <li><i class="fas fa-check"></i>3+ years in property management</li>
                                <li><i class="fas fa-check"></i>Strong organizational skills</li>
                                <li><i class="fas fa-check"></i>Customer service excellence</li>
                                <li><i class="fas fa-check"></i>Real estate knowledge</li>
                            </ul>
                        </div>
                        <a href="#application" class="job-action">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <span class="job-department">Legal</span>
                                <h3 class="job-title">Legal Executive</h3>
                                <div class="job-location">
                                    <i class="fas fa-map-marker-alt"></i>Gorakhpur, Uttar Pradesh
                                </div>
                            </div>
                            <span class="job-type">Full Time</span>
                        </div>
                        <p class="job-description">
                            Handle legal documentation and compliance for property transactions.
                            You'll ensure all legal formalities are completed accurately and timely.
                        </p>
                        <div class="job-requirements">
                            <h4>Key Requirements:</h4>
                            <ul>
                                <li><i class="fas fa-check"></i>LLB degree required</li>
                                <li><i class="fas fa-check"></i>2+ years in property law</li>
                                <li><i class="fas fa-check"></i>Attention to detail</li>
                                <li><i class="fas fa-check"></i>Strong documentation skills</li>
                            </ul>
                        </div>
                        <a href="#application" class="job-action">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <span class="job-department">Admin</span>
                                <h3 class="job-title">Office Administrator</h3>
                                <div class="job-location">
                                    <i class="fas fa-map-marker-alt"></i>Gorakhpur, Uttar Pradesh
                                </div>
                            </div>
                            <span class="job-type">Full Time</span>
                        </div>
                        <p class="job-description">
                            Manage daily office operations and administrative tasks.
                            You'll coordinate with teams, handle documentation, and ensure smooth office functioning.
                        </p>
                        <div class="job-requirements">
                            <h4>Key Requirements:</h4>
                            <ul>
                                <li><i class="fas fa-check"></i>2+ years in administration</li>
                                <li><i class="fas fa-check"></i>MS Office proficiency</li>
                                <li><i class="fas fa-check"></i>Organizational skills</li>
                                <li><i class="fas fa-check"></i>Graduate preferred</li>
                            </ul>
                        </div>
                        <a href="#application" class="job-action">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <span class="job-department">Internship</span>
                                <h3 class="job-title">Real Estate Intern</h3>
                                <div class="job-location">
                                    <i class="fas fa-map-marker-alt"></i>Gorakhpur, Uttar Pradesh
                                </div>
                            </div>
                            <span class="job-type">Internship</span>
                        </div>
                        <p class="job-description">
                            Great opportunity for fresh graduates to learn real estate operations.
                            You'll assist in sales, marketing, and administrative tasks.
                        </p>
                        <div class="job-requirements">
                            <h4>Key Requirements:</h4>
                            <ul>
                                <li><i class="fas fa-check"></i>Recent graduate (any discipline)</li>
                                <li><i class="fas fa-check"></i>Eager to learn</li>
                                <li><i class="fas fa-check"></i>Good communication skills</li>
                                <li><i class="fas fa-check"></i>Basic computer knowledge</li>
                            </ul>
                        </div>
                        <a href="#application" class="job-action">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Culture Section -->
    <section class="culture-section" id="culture">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Why Work With Us</h2>
                <p class="lead text-muted">Experience a culture of growth, innovation, and collaboration</p>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="culture-item">
                        <div class="culture-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3 class="culture-title">Growth Opportunities</h3>
                        <p class="culture-description">
                            Continuous learning and career advancement in the growing real estate sector
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="culture-item">
                        <div class="culture-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="culture-title">Team Environment</h3>
                        <p class="culture-description">
                            Collaborative work culture with supportive colleagues and mentors
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="culture-item">
                        <div class="culture-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3 class="culture-title">Performance Rewards</h3>
                        <p class="culture-description">
                            Attractive incentives and recognition for outstanding performance
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="culture-item">
                        <div class="culture-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h3 class="culture-title">Work-Life Balance</h3>
                        <p class="culture-description">
                            Flexible work arrangements and focus on employee well-being
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Employee Benefits</h2>
                <p class="lead text-muted">Comprehensive benefits package for our team members</p>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3 class="benefit-title">Health Insurance</h3>
                        <p class="benefit-description">
                            Comprehensive health coverage for you and your family
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <h3 class="benefit-title">Provident Fund</h3>
                        <p class="benefit-description">
                            Secure your future with our PF contribution scheme
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3 class="benefit-title">Training Programs</h3>
                        <p class="benefit-description">
                            Regular skill development and training opportunities
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <h3 class="benefit-title">Performance Bonus</h3>
                        <p class="benefit-description">
                            Quarterly and annual performance-based bonuses
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Application Form -->
    <section class="application-section" id="application">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto" data-aos="fade-up">
                    <div class="text-center mb-5">
                        <h2 class="display-4 fw-bold mb-4">Apply Now</h2>
                        <p class="lead">
                            Ready to join our team? Fill out the form below and we'll get back to you soon.
                        </p>
                    </div>

                    <form class="application-form" method="POST" enctype="multipart/form-data">
                        <?php if ($success ?? false): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Thank you! Your application has been submitted successfully. We'll contact you soon.
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
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" name="phone" required
                                       value="<?php echo h($phone ?? ''); ?>"
                                       placeholder="+91 98765 43210">
                                <?php if (isset($errors['phone'])): ?>
                                    <small class="text-danger"><?php echo $errors['phone']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Position *</label>
                                <select class="form-select" name="career_id" required>
                                    <option value="">Select Position</option>
                                    <?php foreach ($careers as $career): ?>
                                        <option value="<?php echo $career['id']; ?>"
                                                <?php echo (isset($career_id) && $career_id == $career['id']) ? 'selected' : ''; ?>>
                                            <?php echo h($career['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Resume * (PDF, DOC, DOCX - Max 5MB)</label>
                            <input type="file" class="form-control" name="resume" required
                                   accept=".pdf,.doc,.docx">
                            <?php if (isset($errors['resume'])): ?>
                                <small class="text-danger"><?php echo $errors['resume']; ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Cover Letter *</label>
                            <textarea class="form-control" rows="5" name="cover_letter" required
                                      placeholder="Tell us why you're interested in this position..."><?php echo h($cover_letter ?? ''); ?></textarea>
                            <?php if (isset($errors['cover_letter'])): ?>
                                <small class="text-danger"><?php echo $errors['cover_letter']; ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit_application" class="btn-application">
                                <i class="fas fa-paper-plane me-2"></i>Submit Application
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-muted">Request ID: <?php echo uniqid('CR_'); ?> | IP: <?php echo h($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'); ?></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include_once __DIR__ . '/includes/components/footer.php'; ?>

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

        // Set career ID from Apply Now button
        function setCareerId(careerId, jobTitle) {
            // Scroll to application form
            document.getElementById('application').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Set the position in dropdown
            setTimeout(() => {
                const careerSelect = document.querySelector('select[name="career_id"]');
                if (careerSelect) {
                    careerSelect.value = careerId;
                }
            }, 500);
        }

        // Job card hover effect
        document.querySelectorAll('.job-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Apply button clicks
        document.querySelectorAll('.job-action').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const jobTitle = this.closest('.job-card').querySelector('.job-title').textContent;

                // Scroll to application form
                document.getElementById('application').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Set the position in dropdown
                setTimeout(() => {
                    const positionSelect = document.querySelector('select[name="position"]');
                    if (positionSelect) {
                        // Find matching option
                        for (let option of positionSelect.options) {
                            if (option.text.toLowerCase().includes(jobTitle.toLowerCase().split(' ')[0])) {
                                option.selected = true;
                                break;
                            }
                        }
                    }
                }, 500);
            });
        });
    </script>
</body>
</html>
