<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');

// Check if columns exist before adding
$cols = [];
$s = $pdo->query("SHOW COLUMNS FROM team_members");
foreach ($s as $r) {
    $cols[] = $r['Field'];
}

if (!in_array('category', $cols)) {
    $pdo->exec("ALTER TABLE team_members ADD category VARCHAR(50) DEFAULT 'department' AFTER sort_order");
}
if (!in_array('group_name', $cols)) {
    $pdo->exec("ALTER TABLE team_members ADD group_name VARCHAR(100) DEFAULT NULL AFTER category");
}
if (!in_array('facebook_url', $cols)) {
    $pdo->exec("ALTER TABLE team_members ADD facebook_url VARCHAR(500) DEFAULT NULL AFTER linkedin");
}
if (!in_array('instagram_url', $cols)) {
    $pdo->exec("ALTER TABLE team_members ADD instagram_url VARCHAR(500) DEFAULT NULL AFTER facebook_url");
}
if (!in_array('website_url', $cols)) {
    $pdo->exec("ALTER TABLE team_members ADD website_url VARCHAR(500) DEFAULT NULL AFTER instagram_url");
}

echo "Columns verified.\n";

// Delete dummy entries
$pdo->exec("DELETE FROM team_members WHERE id IN (2,3,4,5,6,7,8,9) OR name LIKE '%Test%' OR name LIKE '%Agent%' OR name LIKE '%Dummy%' OR (id > 1 AND photo IS NULL AND email IS NULL)");

// Insert real team data
$stmt = $pdo->prepare("INSERT INTO team_members (name, position, bio, photo, email, phone, expertise, experience, sort_order, category, group_name, status) VALUES (?,?,?,?,?,?,?,?,?,?,?, 'active')");

$team = [
    ['Praveen Prabhat', 'Founder & CEO', 'Visionary leader with 15+ years in real estate. Founded APS Dream Home to make property ownership accessible for everyone.', '', 'praveen@apsdreamhome.com', '+91-9277121112', 'Leadership, Strategy, Vision', '15+ years', 1, 'leadership', 'APS Warriors'],
    ['Abhay Singh', 'Managing Director', 'Driving operational excellence and technology innovation. Passionate about leveraging AI to transform real estate.', '', 'abhay@apsdreamhome.com', '+91-XXXXXXXXXX', 'Operations, Strategy, AI', '12+ years', 2, 'leadership', 'Dream Builders'],
    ['Anuj Srivastwa', 'Head of Finance', 'Managing financial operations, budgeting, and investments. Ensuring transparency and sustainable growth.', '', 'anuj@apsdreamhome.com', '+91-XXXXXXXXXX', 'Finance, Accounting, Investment', '10+ years', 3, 'department', 'Dream Builders'],
    ['Vijay Verma', 'CTO / Head of IT & AI', 'Leading technology innovation and AI product development. Building next-gen real estate platform with AI-powered tools.', '', 'vijay@apsdreamhome.com', '+91-XXXXXXXXXX', 'Technology, AI, Software, Product', '10+ years', 4, 'department', 'Tech Pioneers'],
    ['Shushant Srivastva', 'Head of Legal & Compliance', 'Expert in property law, RERA compliance, and documentation. Ensuring every deal is legally sound.', '', 'shushant@apsdreamhome.com', '+91-XXXXXXXXXX', 'Property Law, RERA, Compliance, Documentation', '8+ years', 5, 'department', 'Dream Builders'],
    ['Pramod Sharma', 'Head of Marketing & Sales', 'Driving brand growth and revenue. Expert in real estate marketing, lead generation, and sales strategy.', '', 'pramod@apsdreamhome.com', '+91-XXXXXXXXXX', 'Marketing, Sales, Branding, Lead Generation', '10+ years', 6, 'department', 'APS Warriors'],
    ['Rachna Gupta', 'Head of Customer Relations & Nari Shakti Lead', 'Empowering customers with world-class support. Leading women empowerment initiative across APS Dream Home.', '', 'rachna@apsdreamhome.com', '+91-XXXXXXXXXX', 'Customer Relations, Communication, Women Empowerment', '8+ years', 7, 'women_wing', 'Nari Shakti'],
    ['Praveen Singh', 'Senior Advisor & Strategic Consultant', 'Seasoned advisor with deep expertise in real estate markets, business strategy, and business development.', '', 'praveensingh@apsdreamhome.com', '+91-XXXXXXXXXX', 'Business Advisory, Strategy, Market Expansion', '20+ years', 8, 'tech_advisory', ''],
];

foreach ($team as $t) {
    $stmt->execute($t);
}

echo "Team inserted.\n";

// Create team_groups table
$pdo->exec("CREATE TABLE IF NOT EXISTS team_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slogan VARCHAR(255) DEFAULT NULL,
    description TEXT,
    leader_name VARCHAR(100) DEFAULT NULL,
    member_count INT DEFAULT 0,
    score INT DEFAULT 0,
    badge_color VARCHAR(20) DEFAULT '#0d9488',
    icon VARCHAR(50) DEFAULT 'fas fa-users',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed groups
$pdo->exec("DELETE FROM team_groups");
$gStmt = $pdo->prepare("INSERT INTO team_groups (name, slogan, description, leader_name, member_count, score, badge_color, icon) VALUES (?,?,?,?,?,?,?,?)");
$groups = [
    ['APS Warriors', 'Fight for the best!', 'The competitive squad — always pushing boundaries and closing deals with warrior spirit. Leading the sales and marketing charge.', 'Praveen Prabhat & Pramod Sharma', 8, 2500, '#dc2626', 'fas fa-shield-halved'],
    ['Dream Builders', 'Building dreams, one property at a time.', 'Focused on creating value through innovation, teamwork, and customer-first approach. Operations, finance, and legal backbone.', 'Abhay Singh', 6, 2100, '#2563eb', 'fas fa-building'],
    ['Nari Shakti', 'Women power — unstoppable!', 'Empowered women team driving customer relations, communication, and community growth. Breaking barriers in real estate.', 'Rachna Gupta', 5, 1800, '#d946ef', 'fas fa-fist-raised'],
    ['Tech Pioneers', 'Innovate. Automate. Elevate.', 'The technology squad building AI tools, software products, and digital solutions. Transforming real estate with code.', 'Vijay Verma', 4, 2200, '#059669', 'fas fa-microchip'],
];
foreach ($groups as $g) {
    $gStmt->execute($g);
}

echo "Groups created.\n\n=== Final Team Members ===\n";
$q = $pdo->query("SELECT id, name, position, category, group_name, sort_order FROM team_members ORDER BY sort_order");
foreach ($q as $r) {
    echo $r['id'] . ': ' . $r['name'] . ' | ' . $r['position'] . ' | ' . $r['category'] . ' | group=' . ($r['group_name'] ?? '-') . "\n";
}

echo "\n=== Groups ===\n";
$q = $pdo->query("SELECT id, name, slogan, score, icon FROM team_groups");
foreach ($q as $r) {
    echo $r['id'] . ': ' . $r['name'] . ' | ' . $r['slogan'] . ' | score=' . $r['score'] . "\n";
}
