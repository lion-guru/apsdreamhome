<?php
/**
 * Team Page - APS Dream Homes
 * Display team members and departments
 */

require_once 'core/functions.php';
require_once 'includes/db_connection.php';

try {
    $pdo = getDbConnection();

    // Check if team_members table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'team_members'")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        // Table doesn't exist, use fallback data
        $departments = ['sales', 'marketing', 'support', 'management'];
        $members = [];
    } else {
        // Get distinct departments
        $deptQuery = "SELECT DISTINCT COALESCE(department, 'general') as department FROM team_members WHERE status = 'active' ORDER BY department";
        $deptStmt = $pdo->query($deptQuery);
        $departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

        // Get current department filter
        $currentDepartment = $_GET['department'] ?? 'all';
        $whereClause = $currentDepartment !== 'all' ? "AND COALESCE(department, 'general') = :department" : "";

        // Get team members
        $membersQuery = "SELECT * FROM team_members WHERE status = 'active' {$whereClause} ORDER BY display_order ASC, name ASC LIMIT 20";
        $membersStmt = $pdo->prepare($membersQuery);

        if ($currentDepartment !== 'all') {
            $membersStmt->bindParam(':department', $currentDepartment);
        }

        $membersStmt->execute();
        $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    error_log('Team page database error: ' . $e->getMessage());
    // Fallback to static data
    $departments = ['sales', 'marketing', 'support', 'management'];
    $members = [];
    $currentDepartment = $_GET['department'] ?? 'all';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Include site settings
    require_once 'includes/site_settings.php';
    ?>
    <title><?php echo getSiteSetting('site_title', 'APS Dream Homes'); ?> - Our Team</title>
    <meta name="description" content="Meet our dedicated team of real estate professionals at APS Dream Homes. Our experts are committed to helping you find your dream property.">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .team-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }

        .team-member {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .team-member:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .member-image {
            position: relative;
            height: 300px;
            overflow: hidden;
        }

        .member-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .team-member:hover .member-image img {
            transform: scale(1.05);
        }

        .social-links {
            position: absolute;
            bottom: 15px;
            left: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .team-member:hover .social-links {
            opacity: 1;
            transform: translateY(0);
        }

        .social-links a {
            background: rgba(255,255,255,0.9);
            color: #333;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #667eea;
            color: white;
            transform: scale(1.1);
        }

        .member-info {
            padding: 25px;
        }

        .member-info h4 {
            color: #1a237e;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .designation {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .department {
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .bio {
            color: #666;
            line-height: 1.6;
        }

        .team-filter .btn {
            margin: 5px;
            border-radius: 25px;
            padding: 10px 20px;
        }

        .breadcrumb {
            background: #f8f9fa;
            border-radius: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="team-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Meet Our Team</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Our dedicated professionals are here to help you find your perfect property and make your real estate dreams come true.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav class="bg-light border-bottom py-2" aria-label="breadcrumb">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Our Team</li>
            </ol>
        </div>
    </nav>

    <!-- Team Content -->
    <main class="py-5">
        <div class="container">
            <!-- Department Filter -->
            <?php if (!empty($departments)): ?>
            <div class="row mb-5">
                <div class="col-12">
                    <div class="team-filter text-center">
                        <a href="team.php" class="btn <?php echo $currentDepartment === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All Departments
                        </a>
                        <?php foreach ($departments as $department): ?>
                        <a href="team.php?department=<?php echo urlencode($department); ?>"
                           class="btn <?php echo $currentDepartment === $department ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $department))); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Team Grid -->
            <?php if (!empty($members)): ?>
            <div class="row g-4">
                <?php foreach ($members as $member): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="team-member">
                        <div class="member-image">
                            <img src="<?php echo htmlspecialchars($member['image_path'] ?? 'assets/images/team-placeholder.jpg'); ?>"
                                 alt="<?php echo htmlspecialchars($member['name']); ?>">
                            <div class="social-links">
                                <?php if (!empty($member['linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" target="_blank" title="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($member['email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" title="Email">
                                    <i class="fas fa-envelope"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($member['phone'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($member['phone']); ?>" title="Phone">
                                    <i class="fas fa-phone"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="member-info">
                            <h4><?php echo htmlspecialchars($member['name']); ?></h4>
                            <p class="designation"><?php echo htmlspecialchars($member['designation']); ?></p>
                            <p class="department text-muted"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $member['department']))); ?></p>
                            <p class="bio"><?php echo htmlspecialchars(substr($member['bio'] ?? 'Experienced professional dedicated to helping clients find their dream properties.', 0, 120)) . (strlen($member['bio'] ?? '') > 120 ? '...' : ''); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-users fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">No team members found</h3>
                    <p class="text-muted mb-4">
                        <?php if ($currentDepartment !== 'all'): ?>
                        No team members found in the <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $currentDepartment))); ?> department.
                        <?php else: ?>
                        No team members available at the moment.
                        <?php endif; ?>
                    </p>
                    <a href="team.php" class="btn btn-primary">View All Departments</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
</body>
</html>