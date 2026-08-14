<?php
/**
 * Seed critical business data into 15+ empty core tables
 * Run: php database/seed_business_data.php
 */
$dbHost = '127.0.0.1';
$dbPort = '3307';
$dbName = 'apsdreamhome';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== Seeding Business Data ===\n\n";

$users = $pdo->query("SELECT id, name, email, role FROM users WHERE role IN ('customer','associate','agent','employee') LIMIT 30")->fetchAll();
$customers = array_values(array_filter($users, fn($u) => $u['role'] === 'customer'));
$associates = array_values(array_filter($users, fn($u) => $u['role'] === 'associate'));
$agents = array_values(array_filter($users, fn($u) => $u['role'] === 'agent'));
$employees = array_values(array_filter($users, fn($u) => $u['role'] === 'employee'));
$allUsers = $users;

$properties = $pdo->query("SELECT id, title, price FROM properties LIMIT 10")->fetchAll();
$plots = $pdo->query("SELECT id, plot_number, area_sqft, total_price FROM plots WHERE status='available' LIMIT 20")->fetchAll();
$leads = $pdo->query("SELECT id, name, status FROM leads LIMIT 20")->fetchAll();

echo "Found: " . count($customers) . " customers, " . count($associates) . " associates, "
     . count($agents) . " agents, " . count($properties) . " properties, "
     . count($plots) . " plots, " . count($leads) . " leads\n\n";

function pick($arr) { return $arr[array_rand($arr)]; }
function randDate($daysBack) { return date('Y-m-d', strtotime("-" . rand(1, $daysBack) . " days")); }
function randTime() { return sprintf("%02d:%02d:00", rand(9, 17), rand(0, 3) * 15); }

$now = date('Y-m-d H:i:s');

// === 1. COMMISSIONS ===
echo "1. Commissions... ";
$inserted = 0;
$cStatuses = ['pending', 'paid', 'cancelled'];
$cTypes = ['direct', 'team', 'referral', 'bonus'];
if ($pdo->query("SELECT COUNT(*) FROM commissions")->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO commissions (user_id, associate_id, amount, percentage, commission_type, status, description, created_at) VALUES (?,?,?,?,?,?,?,?)");
    foreach ($associates as $a) {
        $amt = rand(5000, 50000);
        $stmt->execute([$a['id'], $a['id'], $amt, rand(2, 10), pick($cTypes), pick($cStatuses), "Commission for property sale", $randDate = randDate(90)]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 2. PAYOUTS ===
echo "2. Payouts... ";
$inserted = 0;
$pStatuses = ['pending', 'processing', 'completed', 'failed', 'cancelled'];
$pMethods = ['bank_transfer', 'upi', 'paypal', 'check'];
if ($pdo->query("SELECT COUNT(*) FROM payouts")->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO payouts (associate_id, amount, payment_method, status, reference_number, notes, created_at) VALUES (?,?,?,?,?,?,?)");
    foreach (array_slice($associates, 0, 5) as $a) {
        $stmt->execute([$a['id'], rand(15000, 75000), pick($pMethods), pick($pStatuses), 'PAY' . rand(10000, 99999), 'Payout ' . date('M'), randDate(60)]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 3. INVOICES ===
echo "3. Invoices... ";
$inserted = 0;
$iStatuses = ['draft', 'sent', 'viewed', 'paid', 'overdue', 'cancelled'];
if ($pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, invoice_date, due_date, client_name, client_email, client_phone, client_type, subtotal, tax_amount, total_amount, status, notes, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $clientNames = ['Rahul Sharma', 'Priya Singh', 'Amit Kumar', 'Sunil Verma', 'Anita Gupta', 'Vijay Pandey', 'Deepak Mishra', 'Neha Patel'];
    for ($i = 0; $i < 8; $i++) {
        $invDate = randDate(120);
        $subtotal = rand(25000, 500000);
        $tax = round($subtotal * 0.18);
        $stmt->execute([
            'INV-' . date('Ymd', strtotime($invDate)) . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
            $invDate,
            date('Y-m-d', strtotime($invDate . ' +30 days')),
            pick($clientNames),
            'client' . ($i + 1) . '@email.com',
            '9' . rand(7000000000, 9999999999),
            pick(['customer', 'associate', 'vendor']),
            $subtotal, $tax, $subtotal + $tax,
            pick($iStatuses),
            'Invoice for property services',
            $invDate
        ]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 4. EXPENSES ===
echo "4. Expenses... ";
$inserted = 0;
$expCategories = ['Office Rent', 'Utilities', 'Marketing', 'Travel', 'Salaries', 'Maintenance', 'Legal Fees', 'Software', 'Transportation', 'Advertising'];
if ($pdo->query("SELECT COUNT(*) FROM expenses")->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO expenses (associate_id, category, amount, description, expense_date, status, created_at) VALUES (?,?,?,?,?,?,?)");
    foreach ($associates as $a) {
        $stmt->execute([$a['id'], pick($expCategories), rand(2000, 50000), 'Expense: ' . pick($expCategories), randDate(90), pick(['pending', 'approved', 'approved', 'rejected']), randDate(90)]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 5. SUPPORT TICKETS ===
echo "5. Support Tickets... ";
$inserted = 0;
$tSubjects = ['Payment not processed', 'Property document issue', 'Need help with registration', 'Water connection delayed', 'Boundary wall problem', 'EMI failed'];
if ($pdo->query("SELECT COUNT(*) FROM support_tickets")->fetchColumn() == 0 && !empty($customers)) {
    $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, assigned_to, subject, category, message, priority, status, created_at) VALUES (?,?,?,?,?,?,?,?)");
    foreach (array_slice($customers, 0, 6) as $c) {
        $emp = !empty($employees) ? pick($employees)['id'] : null;
        $stmt->execute([$c['id'], $emp, pick($tSubjects), pick(['property', 'payment', 'legal', 'maintenance']), 'I am facing an issue and need assistance.', pick(['low', 'medium', 'high']), pick(['open', 'in_progress', 'resolved', 'closed']), randDate(60)]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 6. VISITS ===
echo "6. Visits... ";
$inserted = 0;
if ($pdo->query("SELECT COUNT(*) FROM visits")->fetchColumn() == 0 && !empty($customers)) {
    $stmt = $pdo->prepare("INSERT INTO visits (associate_id, customer_id, lead_id, visit_date, visit_time, location_address, notes, status, created_at) VALUES (?,?,?,?,?,?,?,?,?)");
    foreach (array_slice($customers, 0, 8) as $c) {
        $stmt->execute([
            !empty($associates) ? pick($associates)['id'] : null, $c['id'],
            !empty($leads) ? pick($leads)['id'] : null,
            randDate(30), randTime(),
            pick(['Gorakhpur', 'Lucknow', 'Varanasi', 'Kushinagar']) . ' - Site Location',
            'Site visit scheduled', pick(['completed', 'scheduled', 'cancelled']), randDate(45)
        ]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 7. DOCUMENTS ===
echo "7. Documents... ";
$inserted = 0;
if ($pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn() == 0 && !empty($allUsers)) {
    $stmt = $pdo->prepare("INSERT INTO documents (user_id, property_id, type, url, drive_file_id, uploaded_on) VALUES (?,?,?,?,?,NOW())");
    $docTypes = ['agreement', 'sale_deed', 'id_proof', 'property_tax', 'noc'];
    foreach (array_slice($allUsers, 0, 6) as $u) {
        $stmt->execute([
            $u['id'],
            !empty($properties) ? pick($properties)['id'] : null,
            pick($docTypes),
            '/uploads/documents/doc_' . rand(100, 999) . '.pdf',
            'drive_' . uniqid()
        ]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 8. REFERRALS ===
echo "8. Referrals... ";
$inserted = 0;
if ($pdo->query("SELECT COUNT(*) FROM referrals")->fetchColumn() == 0 && !empty($associates)) {
    $stmt = $pdo->prepare("INSERT INTO referrals (referrer_id, referred_email, status, referral_code, created_at) VALUES (?,?,?,?,?)");
    foreach (array_slice($associates, 0, 6) as $a) {
        $stmt->execute([$a['id'], 'ref' . rand(100, 999) . '@email.com', pick(['pending', 'converted', 'expired']), 'REF' . strtoupper(substr(md5(rand()), 0, 8)), randDate(90)]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 9. NETWORK TREE ===
echo "9. Network Tree... ";
$inserted = 0;
if ($pdo->query("SELECT COUNT(*) FROM network_tree")->fetchColumn() == 0 && !empty($associates)) {
    $stmt = $pdo->prepare("INSERT INTO network_tree (associate_id, root_id, parent_id, level, position, personal_bv, is_active, joined_at) VALUES (?,?,?,?,?,?,1,NOW())");
    $ids = array_map(fn($a) => $a['id'], $associates);
    $root = $ids[0];
    $stmt->execute([$root, $root, null, 0, null, rand(10000, 50000)]);
    $inserted++;
    $level2 = [];
    foreach (array_slice($ids, 1, 3) as $uid) {
        $pos = pick(['left', 'right']);
        $stmt->execute([$uid, $root, $root, 1, $pos, rand(5000, 20000)]);
        $level2[] = $uid;
        $inserted++;
    }
    foreach (array_slice($ids, 4) as $i => $uid) {
        if (!empty($level2)) {
            $parent = $level2[$i % count($level2)];
            $stmt->execute([$uid, $root, $parent, 2, pick(['left', 'right']), rand(2000, 10000)]);
            $inserted++;
        }
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 10. LEADS CHECK ===
echo "10. Leads check... ";
$lc = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
if ($lc < 15) {
    $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status, property_type, city, notes, created_at) VALUES (?,?,?,?,?,?,?,?,?)");
    $sources = ['website', 'referral', 'facebook', 'google', 'whatsapp', 'walk-in'];
    $statuses = ['new', 'contacted', 'qualified', 'viewing', 'negotiation', 'closed', 'lost'];
    $propTypes = ['plot', 'house', 'flat', 'shop', 'farmhouse'];
    $cities = ['Gorakhpur', 'Lucknow', 'Varanasi', 'Kushinagar', 'Delhi'];
    $extra = max(0, 15 - $lc);
    for ($i = 0; $i < $extra; $i++) {
        $stmt->execute(['Lead ' . rand(200, 500), 'lead' . rand(100, 999) . '@email.com', '9' . rand(7000000000, 9999999999), pick($sources), pick($statuses), pick($propTypes), pick($cities), 'Interested in property purchase', randDate(60)]);
    }
    echo "Added $extra (now " . ($lc + $extra) . ")\n";
} else { echo "has $lc leads\n"; }

// === 11. BLOG POSTS ===
echo "11. Blog Posts... ";
$inserted = 0;
if ($pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, author_id, status, category, featured_image, created_at) VALUES (?,?,?,?,?,?,?,?,?)");
    $adminUser = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn();
    $authorId = $adminUser ?: 1;
    $posts = [
        ['Top 10 Tips for First-Time Home Buyers', 'first-time-home-buyers', 'Buying your first home is an exciting journey...', 'Essential tips for first-time buyers', 'published', 'Buying Guide'],
        ['Why Gorakhpur is the Next Real Estate Hotspot', 'gorakhpur-real-estate', 'Gorakhpur is emerging as a prime real estate destination...', 'Real estate growth in Gorakhpur', 'published', 'Market Trends'],
        ['Understanding Property Registration Process', 'property-registration', 'Property registration involves several legal steps...', 'Complete guide to property registration', 'published', 'Legal'],
        ['5 Benefits of Investing in Plots vs Apartments', 'plots-vs-apartments', 'When it comes to real estate investment...', 'Plot vs apartment investment comparison', 'published', 'Investment'],
        ['Home Loan Guide 2025: Everything You Need', 'home-loan-guide', 'Getting a home loan can be overwhelming...', 'Complete home loan guide', 'draft', 'Finance'],
    ];
    foreach ($posts as $p) {
        $stmt->execute([$p[0], $p[1], $p[2], $p[3], $authorId, $p[4], $p[5], '/assets/images/blog/default.jpg', randDate(90)]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 12. NEWSLETTER SUBSCRIBERS ===
echo "12. Newsletter Subscribers... ";
$inserted = 0;
if ($pdo->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, name, is_active, subscribed_at) VALUES (?,?,1,?)");
    for ($i = 0; $i < 10; $i++) {
        $stmt->execute(['sub' . rand(100, 999) . '@email.com', 'Subscriber ' . rand(1, 100), randDate(60)]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 13. PROPERTY BOOKINGS ===
echo "13. Property Bookings... ";
$inserted = 0;
if ($pdo->query("SELECT COUNT(*) FROM property_bookings")->fetchColumn() == 0 && !empty($customers) && !empty($properties)) {
    $stmt = $pdo->prepare("INSERT INTO property_bookings (user_id, property_id, amount, status, notes, created_at) VALUES (?,?,?,?,?,?)");
    foreach (array_slice($customers, 0, 5) as $c) {
        $prop = pick($properties);
        $stmt->execute([$c['id'], $prop['id'], $prop['price'] ?? rand(500000, 3000000), pick(['pending', 'confirmed', 'cancelled', 'refunded']), 'Property booking', randDate(30)]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 14. API LOGS ===
echo "14. API Logs... ";
$inserted = 0;
if ($pdo->query("SELECT COUNT(*) FROM api_logs")->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO api_logs (endpoint, method, status_code, response_time_ms, ip_address, user_agent, created_at) VALUES (?,?,?,?,?,?,?)");
    $eps = ['/api/properties', '/api/locations/cities', '/api/ai/chatbot', '/api/newsletter/subscribe', '/api/referral/dashboard'];
    for ($i = 0; $i < 10; $i++) {
        $stmt->execute([pick($eps), pick(['GET', 'POST']), pick([200, 200, 200, 201, 400, 404]), rand(50, 1500), '192.168.1.' . rand(1, 255), 'Mozilla/5.0', randDate(15)]);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

// === 15. CAMPAIGNS ===
echo "15. Campaigns... ";
$inserted = 0;
$campStatuses = ['planned', 'active', 'completed', 'cancelled'];
if ($pdo->query("SELECT COUNT(*) FROM campaigns")->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO campaigns (name, type, description, target_audience, status, start_date, end_date, budget, total_sent, total_opened, total_clicked, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $camps = [
        ['Summer Property Sale', 'email', 'Summer offers on plots!', 'all', 'completed', randDate(60), randDate(15), 25000, 150, 45, 12, randDate(90)],
        ['Budh Bihar Launch', 'email', 'New colony launch announcement', 'customers', 'active', randDate(15), null, 35000, 200, 60, 18, randDate(30)],
        ['Diwali Discount Campaign', 'sms', 'Festival special discounts', 'all', pick($campStatuses), randDate(30), randDate(5), 15000, 500, 120, 35, randDate(45)],
    ];
    foreach ($camps as $c) {
        $stmt->execute($c);
        $inserted++;
    }
    echo "$inserted records\n";
} else { echo "has data\n"; }

echo "\n=== Seeding Complete! ===\n";

$pdo = null;?>