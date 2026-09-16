<?php
/**
 * Seed missing legal CMS pages: disclaimer, cancellation-policy, associate-rules
 *
 * Usage: php scripts/seed_missing_legal_pages.php
 */

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: 3306;
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') ?: '');
$db   = getenv('DB_DATABASE') ?: 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT DEFAULT NULL,
        meta_description VARCHAR(500) DEFAULT NULL,
        meta_keywords VARCHAR(500) DEFAULT NULL,
        status ENUM('draft','published','archived') DEFAULT 'draft',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_pages_tenant_id (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pages = [
        'disclaimer' => [
            'title' => 'Disclaimer',
            'slug'  => 'disclaimer',
            'meta_description' => 'Disclaimer for APS Dream Home - important legal notices about property information and services',
            'meta_keywords' => 'disclaimer, legal notice, property information, APS Dream Home',
            'content' => '<h4 class="fw-bold mb-3 text-primary">1. General Information</h4>
<p class="text-muted mb-4">The information provided on APS Dream Home website and mobile application is for general informational purposes only. All information on the site is provided in good faith; however, we make no representation or warranty of any kind, express or implied, regarding the accuracy, adequacy, validity, reliability, availability, or completeness of any information on the site.</p>

<h4 class="fw-bold mb-3 text-primary">2. Property Information</h4>
<p class="text-muted mb-4">All property listings, prices, dimensions, images, and specifications are provided by the respective builders/developers and are subject to change without notice. APS Dream Home acts as an intermediary and does not guarantee the accuracy of such information. Buyers are advised to verify all details independently before making any purchase decision.</p>

<h4 class="fw-bold mb-3 text-primary">3. RERA Compliance</h4>
<p class="text-muted mb-4">Real estate projects displayed on our platform may be subject to the Real Estate (Regulation and Development) Act, 2016 (RERA). Buyers should independently verify RERA registration status of any project with the respective State RERA authority before entering into any agreement.</p>

<h4 class="fw-bold mb-3 text-primary">4. Financial Calculators</h4>
<p class="text-muted mb-4">The financial calculators (stamp duty, EMI, property tax, etc.) provided on this platform offer approximate estimates for informational purposes only. Actual values may vary based on multiple factors including but not limited to location, property type, current market conditions, and applicable government regulations. Please consult a certified financial advisor for precise calculations.</p>

<h4 class="fw-bold mb-3 text-primary">5. External Links</h4>
<p class="text-muted mb-4">The site may contain links to external websites that are not provided or maintained by APS Dream Home. We do not guarantee the accuracy, relevance, timeliness, or completeness of information on these external websites.</p>

<h4 class="fw-bold mb-3 text-primary">6. Professional Advice</h4>
<p class="text-muted mb-4">The content on this platform should not be construed as professional legal, financial, or real estate advice. Users are encouraged to consult qualified professionals before making property-related decisions.</p>

<h4 class="fw-bold mb-3 text-primary">7. Limitation of Liability</h4>
<p class="text-muted mb-4">In no event shall APS Dream Home be liable for any loss or damage, including without limitation indirect or consequential loss or damage, arising from use of or reliance on the information provided on this platform.</p>

<h4 class="fw-bold mb-3 text-primary">8. Contact Us</h4>
<p class="text-muted mb-0">If you have any questions about this disclaimer, please contact us at <a href="mailto:legal@apsdreamhome.com" class="text-primary fw-bold">legal@apsdreamhome.com</a> or call us at <a href="tel:+919277121112" class="text-primary fw-bold">+91 9277121112</a>.</p>',
        ],

        'cancellation-policy' => [
            'title' => 'Cancellation Policy',
            'slug'  => 'cancellation-policy',
            'meta_description' => 'Cancellation policy for APS Dream Home property bookings and services',
            'meta_keywords' => 'cancellation, refund, property booking, booking cancellation, APS Dream Home',
            'content' => '<h4 class="fw-bold mb-3 text-primary">1. Booking Cancellation</h4>
<p class="text-muted mb-4">If you wish to cancel a property booking, you must submit a written cancellation request to <a href="mailto:cancel@apsdreamhome.com" class="text-primary">cancel@apsdreamhome.com</a> or visit our office. Cancellation requests are processed within 7-10 business days.</p>

<h4 class="fw-bold mb-3 text-primary">2. Cancellation Charges</h4>
<ul class="text-muted mb-4">
    <li><strong>Within 7 days of booking:</strong> Full refund minus administrative charges of ₹5,000</li>
    <li><strong>8-30 days of booking:</strong> 75% refund of the booking amount</li>
    <li><strong>After 30 days of booking:</strong> 50% refund of the booking amount</li>
    <li><strong>After agreement execution:</strong> As per the terms of the sale agreement</li>
</ul>

<h4 class="fw-bold mb-3 text-primary">3. Service Cancellations</h4>
<p class="text-muted mb-4">For services such as site visits, consultations, or legal verification, cancellation must be made at least 24 hours before the scheduled appointment. Late cancellations may attract a fee of ₹500.</p>

<h4 class="fw-bold mb-3 text-primary">4. How to Cancel</h4>
<p class="text-muted mb-4">To cancel your booking or service, please contact us through any of the following channels:</p>
<ul class="text-muted mb-4">
    <li>Email: <a href="mailto:cancel@apsdreamhome.com" class="text-primary">cancel@apsdreamhome.com</a></li>
    <li>Phone: <a href="tel:+919277121112" class="text-primary">+91 9277121112</a></li>
    <li>In-person: Visit any of our offices with original booking receipt</li>
</ul>

<h4 class="fw-bold mb-3 text-primary">5. Refund Processing</h4>
<p class="text-muted mb-0">Refunds will be processed within 15-30 business days from the date of approval. Refunds will be made through the same payment method used for the original transaction, unless otherwise specified.</p>',
        ],

        'associate-rules' => [
            'title' => 'Associate Rules & Code of Conduct',
            'slug'  => 'associate-rules',
            'meta_description' => 'Rules, code of conduct, and operating guidelines for APS Dream Home associates',
            'meta_keywords' => 'associate rules, code of conduct, business associate, APS Dream Home',
            'content' => '<h4 class="fw-bold mb-3 text-primary">1. Professional Standards</h4>
<p class="text-muted mb-4">All associates must maintain the highest standards of professionalism in all interactions with clients, colleagues, and the public. This includes punctuality, appropriate dress, clear communication, and respectful conduct.</p>

<h4 class="fw-bold mb-3 text-primary">2. Client Dealings</h4>
<ul class="text-muted mb-4">
    <li>Associates must provide accurate and honest information about all properties.</li>
    <li>No misleading claims, false promises, or exaggerated projections shall be made.</li>
    <li>Client interests must always be prioritized above personal commission interests.</li>
    <li>All material defects or issues with a property must be disclosed to potential buyers.</li>
</ul>

<h4 class="fw-bold mb-3 text-primary">3. Documentation & Compliance</h4>
<ul class="text-muted mb-4">
    <li>All property documentation must be verified and complete before facilitating a sale.</li>
    <li>Associates must comply with RERA (Real Estate Regulation and Development Act, 2016) requirements.</li>
    <li>KYC documents of clients must be collected and verified as per company policy.</li>
    <li>All agreements must be executed on company-approved formats only.</li>
</ul>

<h4 class="fw-bold mb-3 text-primary">4. Commission & Payments</h4>
<ul class="text-muted mb-4">
    <li>Commission structures are defined in the Associate Agreement and must not be modified independently.</li>
    <li>No associate shall accept or offer bribes, kickbacks, or unauthorized incentives.</li>
    <li>All payments must be routed through official company channels.</li>
    <li>Commission disputes must be escalated to the designated manager, not handled directly with clients.</li>
</ul>

<h4 class="fw-bold mb-3 text-primary">5. Confidentiality</h4>
<p class="text-muted mb-4">Associates must maintain strict confidentiality of client information, company business strategies, pricing data, and internal communications. Breach of confidentiality may result in immediate termination and legal action.</p>

<h4 class="fw-bold mb-3 text-primary">6. Conflict of Interest</h4>
<p class="text-muted mb-4">Associates must disclose any personal or financial interest in a property before facilitating a transaction. undisclosed conflicts of interest will be treated as a serious violation.</p>

<h4 class="fw-bold mb-3 text-primary">7. Marketing & Representations</h4>
<ul class="text-muted mb-4">
    <li>All marketing materials must use only company-approved content and branding.</li>
    <li>No unauthorized advertisements, social media posts, or public statements on behalf of the company.</li>
    <li>Property images and descriptions must not be digitally altered to misrepresent the actual property.</li>
</ul>

<h4 class="fw-bold mb-3 text-primary">8. Termination & Disciplinary Action</h4>
<p class="text-muted mb-4">Violations of these rules may result in warnings, suspension, commission withholding, or termination of the associate agreement. Serious violations (fraud, misrepresentation, criminal activity) will result in immediate termination and may lead to legal proceedings.</p>

<h4 class="fw-bold mb-3 text-primary">9. Grievance Resolution</h4>
<p class="text-muted mb-4">Any grievances or disputes must be raised through the formal grievance mechanism outlined in the Associate Agreement. Direct escalation to clients or public forums without exhausting internal channels is prohibited.</p>

<h4 class="fw-bold mb-3 text-primary">10. Amendments</h4>
<p class="text-muted mb-4">APS Dream Home reserves the right to amend these rules at any time. Associates will be notified of changes via official communication channels. Continued association after notification constitutes acceptance of amended rules.</p>

<h4 class="fw-bold mb-3 text-primary">11. Contact</h4>
<p class="text-muted mb-0">For questions about these rules, contact <a href="mailto:associates@apsdreamhome.com" class="text-primary fw-bold">associates@apsdreamhome.com</a> or call <a href="tel:+919277121112" class="text-primary fw-bold">+91 9277121112</a>.</p>',
        ],
    ];

    $insertStmt = $pdo->prepare("
        INSERT INTO pages (title, slug, content, meta_description, meta_keywords, status, tenant_id, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, 'published', 1, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            content = IF(CHAR_LENGTH(content) < 100, VALUES(content), content),
            updated_at = NOW()
    ");

    $count = 0;
    foreach ($pages as $slug => $p) {
        $insertStmt->execute([$p['title'], $p['slug'], $p['content'], $p['meta_description'], $p['meta_keywords']]);
        $affected = $insertStmt->rowCount();
        if ($affected > 0) {
            echo "[SEED] '{$slug}' — created or updated (affected: {$affected})\n";
            $count++;
        } else {
            echo "[SKIP] '{$slug}' — already has content (>= 100 chars), not overwritten\n";
        }
    }

    echo "\n[DONE] Processed " . count($pages) . " pages ({$count} seeded/updated).\n";

    // Show final state
    $stmt = $pdo->query("SELECT id, title, slug, status, CHAR_LENGTH(content) AS content_len FROM pages ORDER BY id");
    echo "\n--- pages table ---\n";
    while ($row = $stmt->fetch()) {
        $len = $row['content_len'] ?? 0;
        $mark = $len >= 100 ? 'OK' : 'EMPTY';
        echo "  [{$mark}] id={$row['id']} slug={$row['slug']} ({$len} chars) status={$row['status']}\n";
    }

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
