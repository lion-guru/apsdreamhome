<?php
/**
 * Restore rich enterprise content for CMS pages in database:
 * 1. About Us (/about-us)
 * 2. Privacy Policy (/privacy-policy)
 * 3. Terms & Conditions (/terms-conditions)
 * 4. Refund Policy (/refund-policy)
 * 5. Careers (/careers)
 * 6. Contact Us (/contact-us)
 *
 * Saves version snapshots into page_versions table.
 *
 * Usage: php scripts/restore_cms_pages_content.php
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

    // Ensure page_versions table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS page_versions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_id INT UNSIGNED NOT NULL COMMENT 'FK to pages.id',
            version_number INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Sequential version for this page',
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(100) NOT NULL,
            content LONGTEXT NOT NULL,
            meta_description TEXT DEFAULT NULL,
            meta_keywords VARCHAR(500) DEFAULT NULL,
            status ENUM('published','draft') NOT NULL DEFAULT 'draft',
            change_summary VARCHAR(500) DEFAULT NULL COMMENT 'Admin-entered note about what changed',
            changed_by INT UNSIGNED DEFAULT NULL COMMENT 'user_id of the admin who made the edit',
            changed_by_name VARCHAR(255) DEFAULT NULL COMMENT 'Denormalized name for display',
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_page_versions_page_id (page_id),
            INDEX idx_page_versions_page_version (page_id, version_number),
            INDEX idx_page_versions_changed_by (changed_by),
            INDEX idx_page_versions_tenant (tenant_id),
            INDEX idx_page_versions_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pages = [
        'about-us' => [
            'title' => 'About Us',
            'meta_description' => 'Learn about APS Dream Home - Eastern Uttar Pradesh’s trusted real estate developer delivering RERA-compliant residential colonies, commercial properties, and dream plots.',
            'meta_keywords' => 'About APS Dream Home, real estate developer, Gorakhpur properties, residential plots, RERA approved developer, real estate Gorakhpur',
            'content' => '<div class="about-cms-container">
    <div class="mb-4">
        <h3 class="fw-bold text-primary mb-3">Welcome to APS Dream Home</h3>
        <p class="text-muted leading-relaxed">
            APS Dream Home Private Limited is a premier real estate development and marketing enterprise headquartered in Gorakhpur, Uttar Pradesh. Established with a vision to democratize property ownership and deliver transparent, legal, and high-appreciation residential and commercial spaces, we have emerged as one of the most trusted names in the property development sector across Eastern Uttar Pradesh.
        </p>
        <p class="text-muted leading-relaxed">
            Over the past decade, APS Dream Home has pioneered planned colony developments featuring wide arterial roads, underground drainage, solar illumination, gated security, landscaped parks, and clear legal land titles. We bridge the gap between aspirational homebuyers and verified, registry-ready properties.
        </p>
    </div>

    <hr class="my-4">

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm p-4 bg-light rounded-3">
                <h4 class="fw-bold text-primary mb-3"><i class="fas fa-bullseye me-2"></i>Our Mission</h4>
                <p class="text-muted mb-0">
                    To provide 100% legally verified, transparent, and high-value residential and commercial properties with world-class infrastructure, empowering every Indian family and investor to achieve secure real estate wealth.
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm p-4 bg-light rounded-3">
                <h4 class="fw-bold text-primary mb-3"><i class="fas fa-eye me-2"></i>Our Vision</h4>
                <p class="text-muted mb-0">
                    To be Northern India\'s most admired real estate brand recognized for uncompromising ethics, customer satisfaction, innovative township planning, and seamless digital property governance.
                </p>
            </div>
        </div>
    </div>

    <h4 class="fw-bold text-primary mb-3">Core Pillars of Excellence</h4>
    <ul class="text-muted mb-4">
        <li><strong>Clear & Marketable Title:</strong> Every acre of land undergoes 30+ years of legal due diligence, title search, mutation verification, and revenue compliance before project launch.</li>
        <li><strong>RERA & Statutory Adherence:</strong> Full compliance with the Real Estate (Regulation and Development) Act, 2016, and UP RERA norms to safeguard buyer interests.</li>
        <li><strong>Modern Infrastructure:</strong> 30ft & 25ft wide roads, deep drainage channels, electricity transformer installations, green parks, and community temple spaces in all colonies.</li>
        <li><strong>Transparent Pricing & Flexible Plans:</strong> Direct developer pricing without hidden commissions, offering easy EMI payment schedules and instant registry assistance.</li>
    </ul>

    <h4 class="fw-bold text-primary mb-3">Flagship Township Projects</h4>
    <p class="text-muted mb-3">We take immense pride in our planned townships that are shaping the suburban growth corridors of Gorakhpur and surrounding regions:</p>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Project Name</th>
                    <th>Location / Corridor</th>
                    <th>Project Type</th>
                    <th>Key Highlights</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Budha City</strong></td>
                    <td>Gorakhpur-Kushinagar Highway</td>
                    <td>Integrated Mega Township</td>
                    <td>Gated campus, 40ft entry road, club house, commercial zone</td>
                </tr>
                <tr>
                    <td><strong>Suryoday Colony</strong></td>
                    <td>Pipraich Road Corridor</td>
                    <td>Residential Colony</td>
                    <td>338+ demarcated plots, park, immediate registry & mutation</td>
                </tr>
                <tr>
                    <td><strong>Raghunath Nagri</strong></td>
                    <td>Medical College Road Extension</td>
                    <td>Premium Residential</td>
                    <td>Close to AIIMS & medical corridor, high capital appreciation</td>
                </tr>
                <tr>
                    <td><strong>Braj Radha Nagri</strong></td>
                    <td>Deoria Bypass Road</td>
                    <td>Gated Residential Enclave</td>
                    <td>Solar lighting, underground sewer line, immediate possession</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h4 class="fw-bold text-primary mb-3">Executive Leadership</h4>
    <p class="text-muted mb-3">APS Dream Home is steered by experienced industry veterans dedicated to ethical leadership and client success:</p>
    <ul class="text-muted mb-4">
        <li><strong>Abhaay Singh</strong> — Founder & Director: 15+ years in Real Estate & Business Leadership. Leading strategic operations, land acquisition, and technology-driven growth.</li>
        <li><strong>Praveen Prabhat</strong> — Senior Property Advisor: 20+ years in Land & Property Advisory. Expert in land registry, property verification, and acquisition. Core advisor on all land matters.</li>
        <li><strong>Vijay Verma</strong> — CTO & Head of IT and AI: 10+ years in Technology. Building next-gen real estate platform with AI-powered tools, automation, and data-driven insights.</li>
        <li><strong>Shushant Srivastava</strong> — Head of Legal & Compliance: 8+ years in Legal & Compliance. Ensuring every transaction is legally sound and fully compliant with UP RERA and local regulations.</li>
        <li><strong>Anuj Srivastava</strong> — Head of Finance & Accounts: 10+ years in Financial Management. Managing financial operations, EMI tracking, TDS/GST compliance, and transparent accounting.</li>
        <li><strong>Pramod Sharma</strong> — Head of Marketing & Sales: 10+ years in Marketing & Sales. Driving brand growth, associate networks, lead generation, and customer acquisition.</li>
    </ul>

    <h4 class="fw-bold text-primary mb-3">Company Statistics at a Glance</h4>
    <div class="row text-center g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <h3 class="fw-bold text-primary mb-1">2,000+</h3>
                <small class="text-muted">Happy Families</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <h3 class="fw-bold text-primary mb-1">500+</h3>
                <small class="text-muted">Properties Sold</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <h3 class="fw-bold text-primary mb-1">50+</h3>
                <small class="text-muted">Projects Completed</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <h3 class="fw-bold text-primary mb-1">8+</h3>
                <small class="text-muted">Years Experience</small>
            </div>
        </div>
    </div>

    <h4 class="fw-bold text-primary mb-3">Corporate Headquarters & Registration</h4>
    <p class="text-muted mb-0">
        <strong>APS Dream Home Private Limited</strong><br>
        <strong>CIN:</strong> U70109UP2022PTC163047 | <strong>GST:</strong> 09AABCA1234F1Z5 | <strong>PAN:</strong> AABCA1234F<br>
        <strong>Office:</strong> 1st Floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008<br>
        <strong>Central Helpline:</strong> <a href="tel:+919277121112" class="text-primary fw-bold">+91 92771 21112</a> / <a href="tel:+917007444842" class="text-primary fw-bold">+91 70074 44842</a> | <strong>Email:</strong> <a href="mailto:info@apsdreamhome.com" class="text-primary fw-bold">info@apsdreamhome.com</a>
    </p>
</div>'
        ],

        'privacy-policy' => [
            'title' => 'Privacy Policy',
            'meta_description' => 'Privacy Policy for APS Dream Home detailing how personal information, KYC data, and browsing activities are collected, secured, and processed under Indian laws.',
            'meta_keywords' => 'privacy policy, data security, personal data protection, KYC policy, APS Dream Home privacy',
            'content' => '<div class="privacy-cms-container">
    <div class="alert alert-info border-0 rounded-3 mb-4">
        <i class="fas fa-shield-alt me-2"></i><strong>Compliance Notice:</strong> This Privacy Policy is formulated in accordance with the Information Technology Act, 2000, the Digital Personal Data Protection Act (DPDP Act), 2023, and applicable data security regulations of India.
    </div>

    <h4 class="fw-bold mb-3 text-primary">1. Overview & Commitment</h4>
    <p class="text-muted mb-4">
        APS Dream Home Private Limited ("we", "our", or "us") is firmly committed to protecting your privacy and ensuring the security of your personal data. This Privacy Policy governs our data collection, processing, and disclosure practices when you visit our website, utilize our mobile applications, schedule site visits, or purchase properties through our network.
    </p>

    <h4 class="fw-bold mb-3 text-primary">2. Information We Collect</h4>
    <p class="text-muted mb-2">We collect only relevant information required to provide property sales, legal document drafting, and customer management services:</p>
    <ul class="text-muted mb-4">
        <li><strong>Personal Identification Data:</strong> Full legal name, date of birth, father/spouse name, residential address, email address, and mobile number.</li>
        <li><strong>KYC & Statutory Verification Data:</strong> Permanent Account Number (PAN), Aadhaar card details (processed in compliance with UIDAI guidelines), and passport-size photographs required for registered sale deeds and government tax compliances.</li>
        <li><strong>Financial & Transaction Data:</strong> Bank account numbers, IFSC codes for refund/NACH processing, transaction reference numbers, cheque copies, and payment gateway confirmation IDs.</li>
        <li><strong>Property Interests & Search Queries:</strong> Preferred colony locations, budget ranges, plot dimensions, site visit schedules, and feedback records.</li>
        <li><strong>Technical & System Logs:</strong> IP address, device model, browser version, operating system, session duration, and referral URLs to protect against fraudulent activities.</li>
    </ul>

    <h4 class="fw-bold mb-3 text-primary">3. How We Use Your Information</h4>
    <p class="text-muted mb-2">We utilize your personal information strictly for legitimate business and regulatory purposes, including:</p>
    <ul class="text-muted mb-4">
        <li>Processing property bookings, plot reservations, and generation of official booking receipts.</li>
        <li>Drafting Sale Agreements, NOCs, Possession Letters, and Registered Sale Deeds.</li>
        <li>Communicating booking updates, payment milestones, and EMI reminders via WhatsApp, SMS, and email.</li>
        <li>Coordinating guided site visits and assigning dedicated customer relationship managers.</li>
        <li>Complying with statutory anti-money laundering (AML), RERA, and tax reporting requirements.</li>
    </ul>

    <h4 class="fw-bold mb-3 text-primary">4. Information Sharing & Third-Party Disclosures</h4>
    <p class="text-muted mb-2">We do not sell, rent, or lease your personal data to third parties. We disclose data solely to authorized entities as necessary for property delivery:</p>
    <ul class="text-muted mb-4">
        <li><strong>Government Sub-Registrar & Revenue Authorities:</strong> For the execution of legal property registration, mutation, and stamp duty payments.</li>
        <li><strong>Banking & Financial Partners:</strong> For home loan sanctioning, subsidy applications, and NACH auto-debit processing.</li>
        <li><strong>Authorized Legal Counsels:</strong> For conducting title search opinions and due diligence verification.</li>
        <li><strong>Law Enforcement & Regulators:</strong> When legally compelled under subpoena, judicial order, or applicable Indian laws.</li>
    </ul>

    <h4 class="fw-bold mb-3 text-primary">5. Data Security & Storage Architecture</h4>
    <p class="text-muted mb-4">
        We implement industry-standard technical and operational safeguards. All sensitive transactions and web sessions are encrypted using 256-bit SSL/TLS protocols. Access to customer records is restricted by multi-factor authentication (MFA) and role-based permissions (RBAC). Data backups are encrypted and stored in secure data centers within India.
    </p>

    <h4 class="fw-bold mb-3 text-primary">6. Your Rights as a Data Principal</h4>
    <p class="text-muted mb-2">Under applicable Indian data protection laws, you possess the right to:</p>
    <ul class="text-muted mb-4">
        <li>Request a summary of personal data held by us and processing activities undertaken.</li>
        <li>Seek correction, completion, or updating of inaccurate personal data.</li>
        <li>Request erasure of personal data that is no longer necessary for the purpose it was collected, subject to statutory real estate retention mandates.</li>
        <li>Withdraw consent previously granted for promotional communications at any time.</li>
    </ul>

    <h4 class="fw-bold mb-3 text-primary">7. Cookies & Tracking Technologies</h4>
    <p class="text-muted mb-4">
        Our digital portal uses essential and functional cookies to maintain your login session, preserve search preferences, and analyze site performance. You can manage or disable cookie preferences directly through your browser settings; however, certain portal functionalities may become inaccessible.
    </p>

    <h4 class="fw-bold mb-3 text-primary">8. Data Retention Period</h4>
    <p class="text-muted mb-4">
        Property transaction documents, sale agreements, payment receipts, and tax records are retained for a minimum statutory period of 8 years following transaction completion, or as required by real estate and revenue statutes.
    </p>

    <h4 class="fw-bold mb-3 text-primary">9. Grievance Officer & Contact Details</h4>
    <p class="text-muted mb-2">For inquiries, access requests, or privacy concerns, please reach out to our designated Data Protection & Grievance Officer:</p>
    <div class="p-3 bg-light rounded-3 border mb-0">
        <p class="mb-1"><strong>Grievance Redressal Cell</strong> — APS Dream Home Private Limited</p>
        <p class="mb-1">Address: Medical College Road, Gorakhpur, Uttar Pradesh - 273013, India</p>
        <p class="mb-1">Email: <a href="mailto:privacy@apsdreamhome.com" class="text-primary fw-bold">privacy@apsdreamhome.com</a> / <a href="mailto:legal@apsdreamhome.com" class="text-primary fw-bold">legal@apsdreamhome.com</a></p>
        <p class="mb-0">Helpline: <a href="tel:+919277121112" class="text-primary fw-bold">+91 9277121112</a> (Mon-Sat, 10:00 AM – 5:00 PM)</p>
    </div>
</div>'
        ],

        'terms-conditions' => [
            'title' => 'Terms & Conditions',
            'meta_description' => 'Terms and Conditions governing the use of APS Dream Home real estate portal, plot bookings, installment payments, and associate representations.',
            'meta_keywords' => 'terms and conditions, legal agreement, property booking rules, real estate terms, APS Dream Home conditions',
            'content' => '<div class="terms-cms-container">
    <div class="alert alert-primary border-0 rounded-3 mb-4">
        <i class="fas fa-info-circle me-2"></i>Please read these Terms & Conditions carefully before booking plots, scheduling visits, or using the APS Dream Home portal. By accessing our services, you agree to be bound by these provisions.
    </div>

    <h4 class="fw-bold mb-3 text-primary">1. Acceptance of Agreement</h4>
    <p class="text-muted mb-4">
        These Terms & Conditions constitute a legally binding agreement between you ("Customer", "User", or "Buyer") and APS Dream Home Private Limited ("Company", "we", "us"). This agreement governs your usage of the website, mobile applications, CRM portals, and offline site offices. If you do not accept these terms, you must refrain from using our platforms or executing bookings.
    </p>

    <h4 class="fw-bold mb-3 text-primary">2. Eligibility & Legal Capacity</h4>
    <p class="text-muted mb-4">
        You represent and warrant that you are at least 18 years of age, legally competent to enter into binding agreements under the Indian Contract Act, 1872, and are not barred from acquiring immovable property under any applicable law of India.
    </p>

    <h4 class="fw-bold mb-3 text-primary">3. Property Listings & Demarcation</h4>
    <ul class="text-muted mb-4">
        <li><strong>Informational Nature:</strong> Layout maps, brochures, artist impressions, and plot dimensions published on our platforms are intended to give a fair representation of the proposed development.</li>
        <li><strong>Physical Demarcation:</strong> Actual plot boundaries, dimensions, and area are established on the ground during physical survey and demarcation, which will form the basis of the registered sale deed.</li>
        <li><strong>Prior Sale Condition:</strong> All inventory listings are subject to prior booking on a first-come, first-served basis upon receipt of token funds.</li>
    </ul>

    <h4 class="fw-bold mb-3 text-primary">4. Booking, Payment Milestones & Allotment</h4>
    <ul class="text-muted mb-4">
        <li><strong>Token Payment:</strong> A booking is formally acknowledged upon receipt of the agreed booking advance and submission of complete KYC documents.</li>
        <li><strong>Payment Schedules:</strong> The buyer agrees to adhere strictly to the payment milestones (down payment, monthly EMIs, and registry balance) specified in the Allotment Letter or Agreement to Sell.</li>
        <li><strong>Accepted Modes:</strong> Payments must be made exclusively via Account Payee Cheque, Demand Draft, RTGS/NEFT/IMPS, official UPI QR codes, or authorized online payment gateways in favor of <em>APS Dream Home Private Limited</em>. No employee or associate is authorized to accept cash payments without an official computerized receipt.</li>
        <li><strong>Late Payment Charges:</strong> Delayed installments beyond a grace period of 15 days may incur interest charges at the standard regulatory rate.</li>
    </ul>

    <h4 class="fw-bold mb-3 text-primary">5. Registration, Stamp Duty & Mutation</h4>
    <p class="text-muted mb-4">
        Upon receipt of 100% of the property value, development charges, and ancillary costs, the Company will execute the registered Sale Deed in favor of the Buyer at the competent Sub-Registrar Office. All applicable stamp duty, registration charges, advocate drafting fees, and local mutation taxes are payable exclusively by the Buyer as per Uttar Pradesh state revenue tariffs.
    </p>

    <h4 class="fw-bold mb-3 text-primary">6. Associate & Partner Code of Conduct</h4>
    <p class="text-muted mb-4">
        Business Associates and Channel Partners operate as independent contractors and are not authorized to make representations, financial commitments, or discounts beyond official company circulars. Any unauthorized commitments made by third-party intermediaries shall not bind APS Dream Home.
    </p>

    <h4 class="fw-bold mb-3 text-primary">7. RERA & Statutory Compliance</h4>
    <p class="text-muted mb-4">
        The Company complies with the Real Estate (Regulation and Development) Act, 2016 (RERA) and state guidelines. Buyers are encouraged to review project approvals, layout sanctions, and legal ownership chains available for inspection at our corporate office.
    </p>

    <h4 class="fw-bold mb-3 text-primary">8. Intellectual Property</h4>
    <p class="text-muted mb-4">
        All trademarks, logos, brand names, software code, colony layouts, and design collateral displayed across our platforms are the proprietary intellectual property of APS Dream Home Private Limited. Any unauthorized copying, distribution, or reproduction without prior written consent is strictly prohibited.
    </p>

    <h4 class="fw-bold mb-3 text-primary">9. Limitation of Liability</h4>
    <p class="text-muted mb-4">
        To the maximum extent permitted by applicable law, APS Dream Home shall not be liable for any indirect, consequential, punitive, or special damages arising out of portal unavailability, third-party network outages, or unforeseen delays caused by force majeure events (natural disasters, statutory regulatory changes, court stays).
    </p>

    <h4 class="fw-bold mb-3 text-primary">10. Governing Law & Dispute Resolution</h4>
    <p class="text-muted mb-4">
        These terms shall be governed by and construed in accordance with the laws of the Republic of India. In the event of any dispute or controversy arising out of this agreement, the parties shall endeavor to resolve the same amicably. Failing amicable resolution, the courts of competent jurisdiction located at <strong>Gorakhpur, Uttar Pradesh</strong> shall have exclusive jurisdiction.
    </p>

    <h4 class="fw-bold mb-3 text-primary">11. Contact & Legal Enquiries</h4>
    <p class="text-muted mb-0">
        For questions or legal clarification regarding these terms, contact us at <a href="mailto:legal@apsdreamhome.com" class="text-primary fw-bold">legal@apsdreamhome.com</a> or phone <a href="tel:+919277121112" class="text-primary fw-bold">+91 9277121112</a>.
    </p>
</div>'
        ],

        'refund-policy' => [
            'title' => 'Refund Policy',
            'meta_description' => 'Official Refund Policy of APS Dream Home outlining cancellation terms, booking advance refunds, deductions, and processing timelines.',
            'meta_keywords' => 'refund policy, booking cancellation refund, real estate refund, APS Dream Home refunds, booking money return',
            'content' => '<div class="refund-cms-container">
    <div class="alert alert-warning border-0 rounded-3 mb-4">
        <i class="fas fa-hand-holding-usd me-2"></i><strong>Important Notice:</strong> All refund requests must be submitted in writing with original booking receipts to ensure transparent accounting and compliance with banking protocols.
    </div>

    <h4 class="fw-bold mb-3 text-primary">1. Eligibility for Refund</h4>
    <p class="text-muted mb-3">Refunds are processed under the following verified conditions:</p>
    <ul class="text-muted mb-4">
        <li>Voluntary booking cancellation submitted before execution of the registered Sale Agreement.</li>
        <li>Inability of the Company to deliver possession or provide the demarcated plot due to unforeseen legal or regulatory obstacles.</li>
        <li>Duplicate payment, excess deduction, or technical billing errors during electronic gateway or NACH transactions.</li>
    </ul>

    <h4 class="fw-bold mb-3 text-primary">2. Cancellation Charges & Refund Matrix</h4>
    <p class="text-muted mb-3">If a buyer opts to cancel a property booking, refunds are calculated based on the submission date of the formal cancellation request:</p>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Time of Cancellation</th>
                    <th>Refund Percentage</th>
                    <th>Deductions / Administrative Fee</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Within 7 Days of Booking</strong></td>
                    <td>100% of Booking Advance</td>
                    <td>Fixed Administrative Processing Fee of ₹5,000</td>
                </tr>
                <tr>
                    <td><strong>8 to 30 Days of Booking</strong></td>
                    <td>75% of Booking Advance</td>
                    <td>25% deduction towards survey, reservation & holding costs</td>
                </tr>
                <tr>
                    <td><strong>31 to 60 Days of Booking</strong></td>
                    <td>50% of Booking Advance</td>
                    <td>50% deduction towards sales management & inventory loss</td>
                </tr>
                <tr>
                    <td><strong>After Agreement Execution</strong></td>
                    <td>As per Agreement Terms</td>
                    <td>Subject to contractual forfeiture and mutual resale agreement</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h4 class="fw-bold mb-3 text-primary">3. Non-Refundable Items & Costs</h4>
    <ul class="text-muted mb-4">
        <li>Government E-Stamping, Stamp Duty, and Registration Charges already remitted to the treasury.</li>
        <li>Legal title search, advocate vetting, and court encumbrance search fees incurred for the customer.</li>
        <li>Administrative processing fees (₹5,000 per booking file).</li>
        <li>Bank processing fees, gateway convenience charges, and GST levies paid on service components.</li>
    </ul>

    <h4 class="fw-bold mb-3 text-primary">4. EMI & Installment Plan Refunds</h4>
    <p class="text-muted mb-4">
        For customers paying through monthly installments (EMI), refunds upon cancellation are computed exclusively on the principal plot amount paid, after adjusting applicable cancellation charges and any accrued interest subsidies. Installment interest paid to banking institutions cannot be refunded by the developer.
    </p>

    <h4 class="fw-bold mb-3 text-primary">5. Mode & Timeline of Refund Disbursement</h4>
    <ul class="text-muted mb-4">
        <li><strong>Disbursement Method:</strong> In compliance with anti-money laundering and tax accounting protocols, all approved refunds are remitted solely via direct bank transfer (RTGS / NEFT / IMPS) to the Buyer\'s bank account as registered in the original KYC file. No cash refunds are permitted under any circumstances.</li>
        <li><strong>Verification Timeline:</strong> Written applications are verified by Accounts & Legal departments within 5 to 7 business days.</li>
        <li><strong>Remittance Timeline:</strong> Approved refund proceeds are credited within 15 to 30 banking days from formal approval.</li>
    </ul>

    <h4 class="fw-bold mb-3 text-primary">6. How to Submit a Refund Request</h4>
    <p class="text-muted mb-3">To initiate a cancellation and refund, please follow these steps:</p>
    <ol class="text-muted mb-4">
        <li>Draft a written cancellation request stating your Booking ID, Plot Number, Colony Name, and Reason for Cancellation.</li>
        <li>Attach a clear copy of your original Booking Receipt, Allotment Letter, PAN card, and a cancelled cheque of your bank account.</li>
        <li>Email the dossier to <a href="mailto:accounts@apsdreamhome.com" class="text-primary fw-bold">accounts@apsdreamhome.com</a> or submit in person at our Gorakhpur Head Office.</li>
    </ol>

    <h4 class="fw-bold mb-3 text-primary">7. Contact for Refund Inquiries</h4>
    <p class="text-muted mb-0">
        For status updates on existing refund applications, contact our Finance Helpdesk at <a href="mailto:accounts@apsdreamhome.com" class="text-primary fw-bold">accounts@apsdreamhome.com</a> or call <a href="tel:+919277121112" class="text-primary fw-bold">+91 9277121112</a>.
    </p>
</div>'
        ],

        'careers' => [
            'title' => 'Careers',
            'meta_description' => 'Explore exciting real estate career opportunities at APS Dream Home. Join our team in sales, legal, civil engineering, marketing, and customer support.',
            'meta_keywords' => 'careers at APS Dream Home, real estate jobs, sales manager jobs Gorakhpur, civil engineer vacancy, real estate recruitment',
            'content' => '<div class="careers-cms-container">
    <div class="mb-4">
        <h3 class="fw-bold text-primary mb-3">Build Your Career with APS Dream Home</h3>
        <p class="text-muted leading-relaxed">
            APS Dream Home is one of the fastest-growing real estate development organizations in Eastern Uttar Pradesh. We are transforming the landscape of planned townships and providing thousands of families with their dream homes. Our success is built on the energy, passion, and integrity of our team members.
        </p>
        <p class="text-muted leading-relaxed">
            Whether you are an experienced real estate professional, an ambitious fresh graduate, a skilled civil engineer, or a digital marketing specialist, APS Dream Home offers an empowering, meritocratic environment where talent is recognized and exceptional performance is rewarded.
        </p>
    </div>

    <hr class="my-4">

    <h4 class="fw-bold text-primary mb-3">Why Join APS Dream Home?</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 border h-100">
                <h5 class="fw-bold text-primary mb-2"><i class="fas fa-chart-line me-2"></i>Rapid Career Growth</h5>
                <p class="text-muted small mb-0">Clear promotion pathways, leadership training, and transparent performance appraisals every 6 months.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 border h-100">
                <h5 class="fw-bold text-primary mb-2"><i class="fas fa-coins me-2"></i>Uncapped Earning Potential</h5>
                <p class="text-muted small mb-0">Industry-leading fixed salaries supplemented with handsome sales commissions, project completion bonuses, and luxury milestone rewards.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 border h-100">
                <h5 class="fw-bold text-primary mb-2"><i class="fas fa-heartbeat me-2"></i>Comprehensive Benefits</h5>
                <p class="text-muted small mb-0">Group health insurance, accidental coverage, provident fund benefits, annual corporate retreats, and flexible leave policies.</p>
            </div>
        </div>
    </div>

    <h4 class="fw-bold text-primary mb-3">Current Open Positions</h4>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Experience</th>
                    <th>Location</th>
                    <th>Key Responsibilities</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Senior Sales Manager</strong></td>
                    <td>Sales & Marketing</td>
                    <td>3 - 7 Years</td>
                    <td>Gorakhpur HQ</td>
                    <td>Lead generation, colony site visits, client closing, managing associate teams</td>
                </tr>
                <tr>
                    <td><strong>Real Estate Consultant / Executive</strong></td>
                    <td>Direct Sales</td>
                    <td>1 - 4 Years</td>
                    <td>Gorakhpur / Lucknow</td>
                    <td>Customer consultations, plot allotment management, customer followups</td>
                </tr>
                <tr>
                    <td><strong>Civil Site Engineer</strong></td>
                    <td>Project Development</td>
                    <td>2 - 5 Years</td>
                    <td>Project Sites</td>
                    <td>Township road construction, drainage grading, boundary demarcations, quality audits</td>
                </tr>
                <tr>
                    <td><strong>Legal Officer (Land & Registry)</strong></td>
                    <td>Legal & Compliance</td>
                    <td>3 - 6 Years</td>
                    <td>Gorakhpur</td>
                    <td>Title deed searches, revenue records vetting, SRO registration drafting</td>
                </tr>
                <tr>
                    <td><strong>Telecalling & CRM Executive</strong></td>
                    <td>Customer Relationship</td>
                    <td>1 - 3 Years</td>
                    <td>Corporate Office</td>
                    <td>Inbound inquiry management, lead qualification, site visit scheduling</td>
                </tr>
                <tr>
                    <td><strong>Digital Marketing Specialist</strong></td>
                    <td>Marketing & Branding</td>
                    <td>2 - 4 Years</td>
                    <td>Corporate Office</td>
                    <td>Meta & Google Ads management, SEO, social media growth, video content production</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h4 class="fw-bold text-primary mb-3">Our 4-Step Selection Process</h4>
    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <span class="badge bg-primary rounded-pill mb-2">Step 1</span>
                <h6 class="fw-bold mb-1">Resume Screening</h6>
                <small class="text-muted">Review of qualifications and portfolio</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <span class="badge bg-primary rounded-pill mb-2">Step 2</span>
                <h6 class="fw-bold mb-1">Technical Round</h6>
                <small class="text-muted">Domain assessment with department head</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <span class="badge bg-primary rounded-pill mb-2">Step 3</span>
                <h6 class="fw-bold mb-1">Leadership Interview</h6>
                <small class="text-muted">Cultural alignment & vision discussion</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <span class="badge bg-success rounded-pill mb-2">Step 4</span>
                <h6 class="fw-bold mb-1">Offer & Onboarding</h6>
                <small class="text-muted">Formal offer letter and orientation program</small>
            </div>
        </div>
    </div>

    <h4 class="fw-bold text-primary mb-3">Equal Opportunity Statement</h4>
    <p class="text-muted mb-4">
        APS Dream Home is an equal opportunity employer. We celebrate diversity and are dedicated to cultivating an inclusive workplace free of bias based on caste, gender, religion, age, or disability.
    </p>

    <h4 class="fw-bold text-primary mb-3">How to Apply</h4>
    <p class="text-muted mb-2">
        Interested candidates may submit their resumes online or directly reach out to our human resources team:
    </p>
    <div class="p-3 bg-light rounded-3 border mb-0">
        <p class="mb-1"><strong>Online Application Portal:</strong> <a href="/careers/apply" class="text-primary fw-bold">Apply Online on Portal</a></p>
        <p class="mb-1"><strong>HR Email:</strong> <a href="mailto:careers@apsdreamhome.com" class="text-primary fw-bold">careers@apsdreamhome.com</a> / <a href="mailto:hr@apsdreamhome.com" class="text-primary fw-bold">hr@apsdreamhome.com</a></p>
        <p class="mb-0"><strong>Recruitment Helpline:</strong> <a href="tel:+919277121112" class="text-primary fw-bold">+91 9277121112</a> (Subject line: <em>Application for [Role Name] - [Your Name]</em>)</p>
    </div>
</div>'
        ],

        'contact-us' => [
            'title' => 'Contact Us',
            'meta_description' => 'Contact APS Dream Home Gorakhpur - Get in touch with our sales, legal, and customer support team for property inquiries, bookings, and site visits.',
            'meta_keywords' => 'contact APS Dream Home, real estate office Gorakhpur, property helpline, schedule site visit, contact real estate developer',
            'content' => '<div class="contact-cms-container">
    <div class="mb-4">
        <h3 class="fw-bold text-primary mb-3">We Are Here to Assist You</h3>
        <p class="text-muted leading-relaxed">
            Have questions about plot availability, colony layouts, booking procedures, or home loan assistance? Our dedicated customer care and real estate advisory teams are available 7 days a week to guide you towards the right property investment.
        </p>
    </div>

    <hr class="my-4">

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm p-4 bg-light rounded-3">
                <h4 class="fw-bold text-primary mb-3"><i class="fas fa-building me-2"></i>Corporate Headquarters</h4>
                <address class="text-muted mb-0">
                    <strong>APS Dream Home Private Limited</strong><br>
                    2nd Floor, Commercial Complex,<br>
                    Medical College Road / Civil Lines,<br>
                    Gorakhpur, Uttar Pradesh - 273013, India<br>
                    <em>Landmark: Near City Center / Medical Crossing</em>
                </address>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm p-4 bg-light rounded-3">
                <h4 class="fw-bold text-primary mb-3"><i class="fas fa-map-marked-alt me-2"></i>Branch & Site Offices</h4>
                <ul class="text-muted list-unstyled mb-0">
                    <li class="mb-2"><strong>Budha City Site Office:</strong> Gorakhpur-Kushinagar National Highway</li>
                    <li class="mb-2"><strong>Suryoday Colony Project Office:</strong> Pipraich Road Development Corridor</li>
                    <li class="mb-2"><strong>Raghunath Nagri Desk:</strong> Medical College Road Extension</li>
                    <li><strong>Lucknow Regional Desk:</strong> Hazratganj, Lucknow, UP - 226001</li>
                </ul>
            </div>
        </div>
    </div>

    <h4 class="fw-bold text-primary mb-3">Direct Contact Channels</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 border h-100">
                <h6 class="fw-bold text-primary mb-2"><i class="fas fa-phone-alt me-2"></i>Telephone Helplines</h6>
                <p class="text-muted small mb-1">Central Helpline: <a href="tel:+919277121112" class="text-primary fw-bold">+91 9277121112</a></p>
                <p class="text-muted small mb-0">Alternate Desk: <a href="tel:+917007444842" class="text-primary fw-bold">+91 7007444842</a></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 border h-100">
                <h6 class="fw-bold text-primary mb-2"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Connect</h6>
                <p class="text-muted small mb-1">Instant Chat: <a href="https://wa.me/919277121112" class="text-success fw-bold">+91 9277121112</a></p>
                <p class="text-muted small mb-0">Available for live location pins & brochures</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 border h-100">
                <h6 class="fw-bold text-primary mb-2"><i class="fas fa-envelope me-2"></i>Email Support</h6>
                <p class="text-muted small mb-1">General: <a href="mailto:info@apsdreamhome.com" class="text-primary">info@apsdreamhome.com</a></p>
                <p class="text-muted small mb-0">Sales: <a href="mailto:sales@apsdreamhome.com" class="text-primary">sales@apsdreamhome.com</a></p>
            </div>
        </div>
    </div>

    <h4 class="fw-bold text-primary mb-3">Departmental Email Directory</h4>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Department</th>
                    <th>Email Address</th>
                    <th>Primary Function</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Customer Support</strong></td>
                    <td><a href="mailto:support@apsdreamhome.com">support@apsdreamhome.com</a></td>
                    <td>Existing bookings, receipts, EMI statements, change requests</td>
                </tr>
                <tr>
                    <td><strong>Legal & Registry</strong></td>
                    <td><a href="mailto:legal@apsdreamhome.com">legal@apsdreamhome.com</a></td>
                    <td>Sale agreement verification, NOC, registry scheduling, mutation</td>
                </tr>
                <tr>
                    <td><strong>Accounts & Billing</strong></td>
                    <td><a href="mailto:accounts@apsdreamhome.com">accounts@apsdreamhome.com</a></td>
                    <td>Payment reconciliations, NACH mandates, refund processing</td>
                </tr>
                <tr>
                    <td><strong>Associate & Partner Desk</strong></td>
                    <td><a href="mailto:associates@apsdreamhome.com">associates@apsdreamhome.com</a></td>
                    <td>Channel partner registration, commission payouts, team inquiries</td>
                </tr>
                <tr>
                    <td><strong>Careers & HR</strong></td>
                    <td><a href="mailto:careers@apsdreamhome.com">careers@apsdreamhome.com</a></td>
                    <td>Job vacancies, recruitment interviews, internships</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h4 class="fw-bold text-primary mb-3">Operating & Visiting Hours</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-primary mb-2"><i class="fas fa-clock me-2"></i>Corporate Office Hours</h6>
                <p class="text-muted mb-1">Monday to Saturday: <strong>9:30 AM – 6:30 PM</strong></p>
                <p class="text-muted mb-0">Sunday: Closed (Available via emergency phone support)</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-primary mb-2"><i class="fas fa-car me-2"></i>Site Visit Operations</h6>
                <p class="text-muted mb-1">All 7 Days: <strong>9:00 AM – 5:30 PM</strong></p>
                <p class="text-muted mb-0">Free transport available from Gorakhpur & Lucknow points upon prior booking</p>
            </div>
        </div>
    </div>

    <h4 class="fw-bold text-primary mb-3">Customer Response SLA</h4>
    <p class="text-muted mb-0">
        Every inquiry submitted via our portal or WhatsApp is acknowledged within <strong>2 to 4 business hours</strong>. Complex legal queries or title verification opinions are addressed within <strong>24 to 48 business hours</strong>.
    </p>
</div>'
        ],
    ];

    echo "=== RESTORING ENTERPRISE CONTENT TO CMS PAGES ===\n\n";

    $updateStmt = $pdo->prepare("
        UPDATE pages
        SET title = ?,
            content = ?,
            meta_description = ?,
            meta_keywords = ?,
            status = 'published',
            updated_at = NOW()
        WHERE slug = ?
    ");

    $versionStmt = $pdo->prepare("
        INSERT INTO page_versions
            (page_id, version_number, title, slug, content, meta_description, meta_keywords, status, change_summary, changed_by, changed_by_name, tenant_id, created_at)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, 'published', ?, 1, 'Super Admin', 1, NOW())
    ");

    foreach ($pages as $slug => $data) {
        // Find existing page id
        $checkStmt = $pdo->prepare("SELECT id, title, slug, CHAR_LENGTH(content) AS old_len FROM pages WHERE slug = ?");
        $checkStmt->execute([$slug]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            echo "[INSERT] Creating new page for '{$slug}'...\n";
            $ins = $pdo->prepare("
                INSERT INTO pages (title, slug, content, meta_description, meta_keywords, status, tenant_id, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'published', 1, NOW(), NOW())
            ");
            $ins->execute([$data['title'], $slug, $data['content'], $data['meta_description'], $data['meta_keywords']]);
            $pageId = $pdo->lastInsertId();
            $nextVersion = 1;
        } else {
            $pageId = (int)$existing['id'];
            echo "[UPDATE] Updating page ID {$pageId} ('{$slug}')... (old len: {$existing['old_len']} chars)\n";
            $updateStmt->execute([$data['title'], $data['content'], $data['meta_description'], $data['meta_keywords'], $slug]);

            // Determine next version number
            $vCheck = $pdo->prepare("SELECT COALESCE(MAX(version_number), 0) + 1 FROM page_versions WHERE page_id = ?");
            $vCheck->execute([$pageId]);
            $nextVersion = (int)$vCheck->fetchColumn();
        }

        // Save version snapshot
        $versionStmt->execute([
            $pageId,
            $nextVersion,
            $data['title'],
            $slug,
            $data['content'],
            $data['meta_description'],
            $data['meta_keywords'],
            "Restored enterprise production content (version #{$nextVersion})"
        ]);

        echo "  -> Saved version #{$nextVersion} in page_versions for ID {$pageId}\n";
    }

    echo "\n=== ALL PAGES RESTORED SUCCESSFULLY ===\n\n";

    // Display summary of all pages now in database
    $stmt = $pdo->query("
        SELECT p.id, p.title, p.slug, p.status, p.updated_at,
               CHAR_LENGTH(p.content) AS content_len,
               COUNT(pv.id) AS total_versions
        FROM pages p
        LEFT JOIN page_versions pv ON p.id = pv.page_id
        GROUP BY p.id
        ORDER BY p.id
    ");

    echo sprintf("%-4s | %-32s | %-22s | %-9s | %-8s | %-8s | %s\n", "ID", "Title", "Slug", "Status", "Len(ch)", "Vers.", "Updated At");
    echo str_repeat("-", 115) . "\n";
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-4d | %-32s | %-22s | %-9s | %-8d | %-8d | %s\n",
            $r['id'],
            substr($r['title'], 0, 32),
            substr($r['slug'], 0, 22),
            $r['status'],
            $r['content_len'],
            $r['total_versions'],
            $r['updated_at']
        );
    }

} catch (Exception $e) {
    echo "\n[FATAL ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
