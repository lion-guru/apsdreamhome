<?php
/**
 * Seed 30 realistic CRM leads with interactions, tasks, and assignments
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// Agent/Associate user IDs for assignment
$assignableUsers = [36, 53, 77, 78, 88]; // Agent Smith, Test Associate, etc.
$createdBy = 1; // Admin

// Indian names + phones
$leads = [
    // NEW leads (10)
    ['name' => 'Amit Sharma', 'phone' => '9876543210', 'email' => 'amit.sharma@gmail.com', 'source' => 'website', 'status' => 'new', 'priority' => 'high', 'property_interest' => 'Plot', 'budget' => 2500000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 25, 'lead_category' => 'warm', 'notes' => 'Looking for 1200 sqft plot near Gorakhpur station', 'company' => 'Sharma Traders'],
    ['name' => 'Priya Patel', 'phone' => '9876543211', 'email' => 'priya.patel@outlook.com', 'source' => 'facebook', 'status' => 'new', 'priority' => 'medium', 'property_interest' => 'House', 'budget' => 5000000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Lucknow', 'lead_score' => 30, 'lead_category' => 'warm', 'notes' => 'Interested in 3BHK independent house, saw Facebook ad'],
    ['name' => 'Rahul Verma', 'phone' => '9876543212', 'email' => 'rahul.v@yahoo.com', 'source' => 'google_ads', 'status' => 'new', 'priority' => 'medium', 'property_interest' => 'Flat', 'budget' => 3500000, 'budget_range' => '25-50L', 'location_preference' => 'Varanasi', 'lead_score' => 20, 'lead_category' => 'lukewarm', 'notes' => 'Google Ads click, interested in 2BHK flat', 'company' => 'Verma Enterprises'],
    ['name' => 'Sneha Gupta', 'phone' => '9876543213', 'email' => 'sneha.g@gmail.com', 'source' => 'whatsapp', 'status' => 'new', 'priority' => 'low', 'property_interest' => 'Plot', 'budget' => 1500000, 'budget_range' => '10-25L', 'location_preference' => 'Kushinagar', 'lead_score' => 15, 'lead_category' => 'cold', 'notes' => 'WhatsApp inquiry about plots in Budh Bihar'],
    ['name' => 'Vikram Singh', 'phone' => '9876543214', 'email' => 'vikram.singh@hotmail.com', 'source' => 'walk_in', 'status' => 'new', 'priority' => 'high', 'property_interest' => 'House', 'budget' => 8000000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Gorakhpur', 'lead_score' => 35, 'lead_category' => 'hot', 'notes' => 'Walked in today, very interested, wants to see Suryoday plots', 'company' => 'Singh Construction'],
    ['name' => 'Ananya Reddy', 'phone' => '9876543215', 'email' => 'ananya.r@gmail.com', 'source' => 'instagram', 'status' => 'new', 'priority' => 'medium', 'property_interest' => 'Flat', 'budget' => 4000000, 'budget_range' => '25-50L', 'location_preference' => 'Lucknow', 'lead_score' => 28, 'lead_category' => 'warm', 'notes' => 'Instagram DM about 2BHK in Braj Radha'],
    ['name' => 'Deepak Mishra', 'phone' => '9876543216', 'email' => 'deepak.m@gmail.com', 'source' => 'referral', 'status' => 'new', 'priority' => 'medium', 'property_interest' => 'Plot', 'budget' => 3000000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 32, 'lead_category' => 'warm', 'notes' => 'Referred by Amit Sharma, interested in corner plot', 'referral_source' => 'Amit Sharma'],
    ['name' => 'Neha Agarwal', 'phone' => '9876543217', 'email' => 'neha.a@yahoo.com', 'source' => 'website', 'status' => 'new', 'priority' => 'low', 'property_interest' => 'Shop', 'budget' => 2000000, 'budget_range' => '10-25L', 'location_preference' => 'Gorakhpur', 'lead_score' => 12, 'lead_category' => 'cold', 'notes' => 'Website form for commercial shop near market area'],
    ['name' => 'Suresh Tiwari', 'phone' => '9876543218', 'email' => 'suresh.t@rediffmail.com', 'source' => 'facebook', 'status' => 'new', 'priority' => 'medium', 'property_interest' => 'House', 'budget' => 6000000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Varanasi', 'lead_score' => 22, 'lead_category' => 'warm', 'notes' => 'Facebook lead form, looking for Ganga Nagri area'],
    ['name' => 'Meena Kumari', 'phone' => '9876543219', 'email' => 'meena.k@gmail.com', 'source' => 'whatsapp', 'status' => 'new', 'priority' => 'low', 'property_interest' => 'Farmhouse', 'budget' => 10000000, 'budget_range' => '1Cr+', 'location_preference' => 'Kushinagar', 'lead_score' => 18, 'lead_category' => 'lukewarm', 'notes' => 'WhatsApp from rural area, interested in farmhouse land'],

    // CONTACTED leads (5)
    ['name' => 'Ravi Kumar', 'phone' => '9876543220', 'email' => 'ravi.k@outlook.com', 'source' => 'website', 'status' => 'contacted', 'priority' => 'high', 'property_interest' => 'Plot', 'budget' => 3500000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 55, 'lead_category' => 'hot', 'notes' => 'Called twice, very interested in Suryoday Phase 2', 'assigned_to' => 36],
    ['name' => 'Geeta Devi', 'phone' => '9876543221', 'email' => 'geeta.d@gmail.com', 'source' => 'walk_in', 'status' => 'contacted', 'priority' => 'medium', 'property_interest' => 'Flat', 'budget' => 2800000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 40, 'lead_category' => 'warm', 'notes' => 'Walk-in with husband, want 2BHK near school', 'assigned_to' => 53],
    ['name' => 'Manoj Pandey', 'phone' => '9876543222', 'email' => 'manoj.p@yahoo.com', 'source' => 'referral', 'status' => 'contacted', 'priority' => 'high', 'property_interest' => 'Plot', 'budget' => 4500000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 60, 'lead_category' => 'hot', 'notes' => 'Referred by existing customer, wants 3 corner plots', 'assigned_to' => 77, 'referral_source' => 'Vikram Singh'],
    ['name' => 'Kavita Joshi', 'phone' => '9876543223', 'email' => 'kavita.j@gmail.com', 'source' => 'google_ads', 'status' => 'contacted', 'priority' => 'medium', 'property_interest' => 'House', 'budget' => 7000000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Lucknow', 'lead_score' => 45, 'lead_category' => 'warm', 'notes' => 'Google Ads lead, visited Braj Radha, wants to negotiate', 'assigned_to' => 78],
    ['name' => 'Arun Nair', 'phone' => '9876543224', 'email' => 'arun.nair@outlook.com', 'source' => 'website', 'status' => 'contacted', 'priority' => 'low', 'property_interest' => 'Plot', 'budget' => 1800000, 'budget_range' => '10-25L', 'location_preference' => 'Kushinagar', 'lead_score' => 28, 'lead_category' => 'lukewarm', 'notes' => 'Website inquiry, called once, no response second time', 'assigned_to' => 88],

    // QUALIFIED leads (5)
    ['name' => 'Sanjay Malhotra', 'phone' => '9876543225', 'email' => 'sanjay.m@gmail.com', 'source' => 'walk_in', 'status' => 'qualified', 'priority' => 'high', 'property_interest' => 'Plot', 'budget' => 5000000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Gorakhpur', 'lead_score' => 72, 'lead_category' => 'hot', 'notes' => 'Visited site twice, confirmed budget, wants to book MT-A-005', 'assigned_to' => 36, 'conversion_probability' => 75.00],
    ['name' => 'Pooja Singhania', 'phone' => '9876543226', 'email' => 'pooja.s@gmail.com', 'source' => 'referral', 'status' => 'qualified', 'priority' => 'high', 'property_interest' => 'House', 'budget' => 9000000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Varanasi', 'lead_score' => 78, 'lead_category' => 'hot', 'notes' => 'Premium buyer, referred by director, wants Ganga Nagri villa', 'assigned_to' => 36, 'conversion_probability' => 80.00],
    ['name' => 'Rajesh Gupta', 'phone' => '9876543227', 'email' => 'rajesh.g@outlook.com', 'source' => 'whatsapp', 'status' => 'qualified', 'priority' => 'medium', 'property_interest' => 'Flat', 'budget' => 3800000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 58, 'lead_category' => 'warm', 'notes' => 'WhatsApp follow-up done, wants 2BHK with parking', 'assigned_to' => 53, 'conversion_probability' => 60.00],
    ['name' => 'Shalini Dubey', 'phone' => '9876543228', 'email' => 'shalini.d@yahoo.com', 'source' => 'facebook', 'status' => 'qualified', 'priority' => 'medium', 'property_interest' => 'Plot', 'budget' => 2200000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 52, 'lead_category' => 'warm', 'notes' => 'Facebook ad, visited Raghunath City Center, comparing prices', 'assigned_to' => 77, 'conversion_probability' => 55.00],
    ['name' => 'Vinay Chauhan', 'phone' => '9876543229', 'email' => 'vinay.c@gmail.com', 'source' => 'google_ads', 'status' => 'qualified', 'priority' => 'high', 'property_interest' => 'Plot', 'budget' => 6500000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Gorakhpur', 'lead_score' => 68, 'lead_category' => 'hot', 'notes' => 'High budget buyer, wants large plot with road facing', 'assigned_to' => 78, 'conversion_probability' => 70.00],

    // SITE_VISIT / PROPOSAL leads (5)
    ['name' => 'Ashok Pandey', 'phone' => '9876543230', 'email' => 'ashok.p@outlook.com', 'source' => 'walk_in', 'status' => 'proposal', 'priority' => 'high', 'property_interest' => 'Plot', 'budget' => 4000000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 82, 'lead_category' => 'hot', 'notes' => 'Site visit done on 15-Jun, proposal sent for MT-A-007, waiting response', 'assigned_to' => 36, 'conversion_probability' => 85.00],
    ['name' => 'Rekha Devi', 'phone' => '9876543231', 'email' => 'rekha.d@gmail.com', 'source' => 'referral', 'status' => 'proposal', 'priority' => 'high', 'property_interest' => 'House', 'budget' => 12000000, 'budget_range' => '1Cr+', 'location_preference' => 'Varanasi', 'lead_score' => 88, 'lead_category' => 'hot', 'notes' => 'Premium buyer, wants ready-to-move villa, proposal sent with discount', 'assigned_to' => 36, 'conversion_probability' => 90.00],
    ['name' => 'Mohit Bajpai', 'phone' => '9876543232', 'email' => 'mohit.b@gmail.com', 'source' => 'website', 'status' => 'proposal', 'priority' => 'medium', 'property_interest' => 'Flat', 'budget' => 3200000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 65, 'lead_category' => 'warm', 'notes' => 'Site visit done, email proposal sent with EMI options', 'assigned_to' => 53, 'conversion_probability' => 65.00],
    ['name' => 'Archana Saxena', 'phone' => '9876543233', 'email' => 'archana.s@outlook.com', 'source' => 'instagram', 'status' => 'proposal', 'priority' => 'medium', 'property_interest' => 'Plot', 'budget' => 2800000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 58, 'lead_category' => 'warm', 'notes' => 'Instagram inquiry, visited Suryoday, comparing with other projects', 'assigned_to' => 77, 'conversion_probability' => 55.00],
    ['name' => 'Pradeep Yadav', 'phone' => '9876543234', 'email' => 'pradeep.y@gmail.com', 'source' => 'walk_in', 'status' => 'proposal', 'priority' => 'high', 'property_interest' => 'Plot', 'budget' => 5500000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Gorakhpur', 'lead_score' => 75, 'lead_category' => 'hot', 'notes' => 'Walk-in with family, loved corner plot MT-A-009, proposal ready', 'assigned_to' => 78, 'conversion_probability' => 78.00],

    // NEGOTIATION leads (3)
    ['name' => 'Dinesh Chandra', 'phone' => '9876543235', 'email' => 'dinesh.c@gmail.com', 'source' => 'referral', 'status' => 'negotiation', 'priority' => 'high', 'property_interest' => 'Plot', 'budget' => 4200000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 85, 'lead_category' => 'hot', 'notes' => 'Negotiating 5% discount, wants loan assistance, very close to booking', 'assigned_to' => 36, 'conversion_probability' => 88.00],
    ['name' => 'Usha Rani', 'phone' => '9876543236', 'email' => 'usha.r@outlook.com', 'source' => 'website', 'status' => 'negotiation', 'priority' => 'high', 'property_interest' => 'House', 'budget' => 7500000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Lucknow', 'lead_score' => 80, 'lead_category' => 'hot', 'notes' => 'Wants Braj Radha villa, negotiating payment schedule, 3 meetings done', 'assigned_to' => 53, 'conversion_probability' => 82.00],
    ['name' => 'Ajay Rai', 'phone' => '9876543237', 'email' => 'ajay.r@gmail.com', 'source' => 'google_ads', 'status' => 'negotiation', 'priority' => 'medium', 'property_interest' => 'Plot', 'budget' => 3000000, 'budget_range' => '25-50L', 'location_preference' => 'Gorakhpur', 'lead_score' => 72, 'lead_category' => 'hot', 'notes' => 'Price negotiation ongoing, wants EMI flexibility, close to closing', 'assigned_to' => 77, 'conversion_probability' => 75.00],

    // CLOSED_WON leads (2)
    ['name' => 'Harish Agrawal', 'phone' => '9876543238', 'email' => 'harish.a@gmail.com', 'source' => 'walk_in', 'status' => 'closed_won', 'priority' => 'high', 'property_interest' => 'Plot', 'budget' => 5000000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Gorakhpur', 'lead_score' => 95, 'lead_category' => 'hot', 'notes' => 'BOOKED MT-A-010 on 10-Jun. Token paid â‚¹2,00,000. Agreement pending.', 'assigned_to' => 36, 'conversion_probability' => 100.00, 'is_converted' => 1, 'total_purchase_value' => 5200000],
    ['name' => 'Sunita Verma', 'phone' => '9876543239', 'email' => 'sunita.v@gmail.com', 'source' => 'referral', 'status' => 'closed_won', 'priority' => 'high', 'property_interest' => 'House', 'budget' => 8500000, 'budget_range' => '50L-1Cr', 'location_preference' => 'Varanasi', 'lead_score' => 98, 'lead_category' => 'hot', 'notes' => 'BOOKED Ganga Nagri Villa on 18-Jun. Full payment done.', 'assigned_to' => 36, 'conversion_probability' => 100.00, 'is_converted' => 1, 'total_purchase_value' => 8500000],
];

// Interaction types for different stages
$interactionTemplates = [
    'new' => [
        ['type' => 'system', 'direction' => 'inbound', 'subject' => 'Lead created', 'body' => 'Lead captured from {source}', 'outcome' => null],
    ],
    'contacted' => [
        ['type' => 'system', 'direction' => 'inbound', 'subject' => 'Lead created', 'body' => 'Lead captured from {source}', 'outcome' => null],
        ['type' => 'call', 'direction' => 'outbound', 'subject' => 'Initial call', 'body' => 'Called to introduce APS Dream Home properties. Customer showed interest.', 'outcome' => 'interested', 'duration' => 180],
    ],
    'qualified' => [
        ['type' => 'system', 'direction' => 'inbound', 'subject' => 'Lead created', 'body' => 'Lead captured from {source}', 'outcome' => null],
        ['type' => 'call', 'direction' => 'outbound', 'subject' => 'Initial call', 'body' => 'Called to introduce APS Dream Home properties.', 'outcome' => 'connected', 'duration' => 240],
        ['type' => 'whatsapp', 'direction' => 'outbound', 'subject' => 'Property details shared', 'body' => 'Shared property brochure and price list via WhatsApp.', 'outcome' => 'interested'],
        ['type' => 'call', 'direction' => 'outbound', 'subject' => 'Follow-up call', 'body' => 'Discussed budget and location preferences in detail.', 'outcome' => 'interested', 'duration' => 360],
    ],
    'proposal' => [
        ['type' => 'system', 'direction' => 'inbound', 'subject' => 'Lead created', 'body' => 'Lead captured from {source}', 'outcome' => null],
        ['type' => 'call', 'direction' => 'outbound', 'subject' => 'Initial call', 'body' => 'Called to introduce APS Dream Home properties.', 'outcome' => 'connected', 'duration' => 240],
        ['type' => 'visit', 'direction' => 'outbound', 'subject' => 'Site visit', 'body' => 'Customer visited the site with family. Showed multiple options.', 'outcome' => 'site_visit_booked', 'duration' => 3600],
        ['type' => 'email', 'direction' => 'outbound', 'subject' => 'Proposal sent', 'body' => 'Sent detailed proposal with pricing, payment schedule, and brochure.', 'outcome' => 'proposal_sent'],
    ],
    'negotiation' => [
        ['type' => 'system', 'direction' => 'inbound', 'subject' => 'Lead created', 'body' => 'Lead captured from {source}', 'outcome' => null],
        ['type' => 'call', 'direction' => 'outbound', 'subject' => 'Initial call', 'body' => 'Called to introduce APS Dream Home properties.', 'outcome' => 'connected', 'duration' => 240],
        ['type' => 'visit', 'direction' => 'outbound', 'subject' => 'Site visit', 'body' => 'Customer visited the site. Very impressed with the project.', 'outcome' => 'site_visit_booked', 'duration' => 5400],
        ['type' => 'email', 'direction' => 'outbound', 'subject' => 'Proposal sent', 'body' => 'Sent proposal with special discount offer.', 'outcome' => 'proposal_sent'],
        ['type' => 'call', 'direction' => 'outbound', 'subject' => 'Price negotiation', 'body' => 'Customer negotiating on price and payment terms. Discussed EMI options.', 'outcome' => 'connected', 'duration' => 600],
        ['type' => 'meeting', 'direction' => 'outbound', 'subject' => 'In-person meeting', 'body' => 'Met at office to discuss final terms. Very close to booking.', 'outcome' => 'interested', 'duration' => 2700],
    ],
    'closed_won' => [
        ['type' => 'system', 'direction' => 'inbound', 'subject' => 'Lead created', 'body' => 'Lead captured from {source}', 'outcome' => null],
        ['type' => 'call', 'direction' => 'outbound', 'subject' => 'Initial call', 'body' => 'Called to introduce APS Dream Home properties.', 'outcome' => 'connected', 'duration' => 240],
        ['type' => 'visit', 'direction' => 'outbound', 'subject' => 'Site visit', 'body' => 'Customer visited the site and selected plot/house.', 'outcome' => 'site_visit_booked', 'duration' => 5400],
        ['type' => 'email', 'direction' => 'outbound', 'subject' => 'Proposal sent', 'body' => 'Sent final proposal.', 'outcome' => 'proposal_sent'],
        ['type' => 'meeting', 'direction' => 'outbound', 'subject' => 'Booking meeting', 'body' => 'Final meeting at office. Agreement signed, token paid.', 'outcome' => 'deal_closed', 'duration' => 3600],
    ],
];

$count = 0;
$now = date('Y-m-d H:i:s');

foreach ($leads as $i => $lead) {
    $leadNumber = 'CRM-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    $assignedTo = $lead['assigned_to'] ?? $assignableUsers[array_rand($assignableUsers)];
    $createdAt = date('Y-m-d H:i:s', strtotime("-" . rand(1, 30) . " days -" . rand(0, 23) . " hours"));
    $updatedAt = date('Y-m-d H:i:s', strtotime($createdAt . " + " . rand(1, 5) . " days"));
    $lastActivity = $lead['status'] !== 'new' ? $updatedAt : null;

    // Calculate next_followup_date based on status
    $nextFollowup = null;
    if (in_array($lead['status'], ['contacted', 'qualified'])) {
        $nextFollowup = date('Y-m-d', strtotime('+2 days'));
    } elseif (in_array($lead['status'], ['proposal', 'negotiation'])) {
        $nextFollowup = date('Y-m-d', strtotime('+1 day'));
    }

    $scoreFactors = json_encode([
        'phone' => !empty($lead['phone']) ? 10 : 0,
        'email' => !empty($lead['email']) ? 5 : 0,
        'company' => !empty($lead['company']) ? 5 : 0,
        'budget' => $lead['budget'] > 0 ? 15 : 0,
        'high_budget' => $lead['budget'] > 5000000 ? 10 : 0,
        'location' => !empty($lead['location_preference']) ? 5 : 0,
        'property_interest' => !empty($lead['property_interest']) ? 5 : 0,
        'source_quality' => in_array($lead['source'], ['walk_in', 'referral']) ? 20 : 10,
        'status_progression' => $lead['score_factors'] ?? 0,
    ]);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO leads (
                lead_number, name, email, phone, company, source, status, priority,
                property_interest, budget, budget_range, location_preference,
                assigned_to, created_by, lead_score, lead_category, notes,
                score_factors, last_activity_date, next_activity_date,
                conversion_probability, is_converted, total_purchase_value,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $leadNumber,
            $lead['name'],
            $lead['email'],
            $lead['phone'],
            $lead['company'] ?? null,
            $lead['source'],
            $lead['status'],
            $lead['priority'],
            $lead['property_interest'],
            $lead['budget'],
            $lead['budget_range'],
            $lead['location_preference'],
            $assignedTo,
            $createdBy,
            $lead['lead_score'],
            $lead['lead_category'],
            $lead['notes'],
            $scoreFactors,
            $lastActivity,
            $nextFollowup,
            $lead['conversion_probability'] ?? 0,
            $lead['is_converted'] ?? 0,
            $lead['total_purchase_value'] ?? 0,
            $createdAt,
            $updatedAt
        ]);

        $leadId = $pdo->lastInsertId();
        $count++;

        // Insert interactions
        $interactions = $interactionTemplates[$lead['status']] ?? $interactionTemplates['new'];
        foreach ($interactions as $j => $interaction) {
            $interactionDate = date('Y-m-d H:i:s', strtotime($createdAt . " + {$j} days"));
            $body = str_replace('{source}', $lead['source'], $interaction['body']);

            $interactionStmt = $pdo->prepare("
                INSERT INTO crm_interactions (
                    lead_id, user_id, interaction_type, direction, subject, body,
                    duration_seconds, outcome, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $interactionStmt->execute([
                $leadId,
                $assignedTo,
                $interaction['type'],
                $interaction['direction'],
                $interaction['subject'],
                $body,
                $interaction['duration'] ?? null,
                $interaction['outcome'],
                $interactionDate,
            ]);
        }

        // Insert follow-up tasks for active leads
        if (in_array($lead['status'], ['contacted', 'qualified', 'proposal', 'negotiation'])) {
            $taskDate = date('Y-m-d H:i:s', strtotime('+1 day 10:00:00'));
            $taskStmt = $pdo->prepare("
                INSERT INTO crm_tasks (
                    lead_id, assigned_to, title, description, priority,
                    due_date, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $taskStmt->execute([
                $leadId,
                $assignedTo,
                "Follow up with {$lead['name']}",
                "Check progress on {$lead['property_interest']} inquiry. Budget: â‚¹" . number_format($lead['budget']),
                $lead['priority'],
                $taskDate,
                $now,
            ]);
        }

        echo "  âœ“ {$lead['name']} ({$leadNumber}) â€” {$lead['status']}, score={$lead['lead_score']}, assigned to user {$assignedTo}\n";

    } catch (PDOException $e) {
        echo "  âœ— {$lead['name']}: " . $e->getMessage() . "\n";
    }
}

// Also create pipeline activity entries for active leads
$activeLeads = $pdo->query("
    SELECT l.id, l.status, l.assigned_to, l.created_at
    FROM leads l
    WHERE l.lead_number LIKE 'CRM-%'
    AND l.status NOT IN ('new')
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($activeLeads as $al) {
    try {
        $actStmt = $pdo->prepare("
            INSERT INTO crm_interactions (
                lead_id, user_id, interaction_type, direction, subject, body, outcome, created_at
            ) VALUES (?, ?, 'system', 'outbound', 'Lead assigned', 'Lead auto-assigned to team member', 'connected', ?)
        ");
        $actStmt->execute([$al['assigned_to'], $al['assigned_to'], $al['created_at']]);
    } catch (PDOException $e) {
        // Silently skip duplicate/system interactions
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=== CRM Lead Seeding Complete ===\n";
echo "Leads created: {$count}\n";
echo "Interactions: " . ($count * 3) . " (approx)\n";
echo "Tasks: ~20 (for active leads)\n";
echo "\nPipeline distribution:\n";
echo "  NEW: 10\n";
echo "  CONTACTED: 5\n";
echo "  QUALIFIED: 5\n";
echo "  PROPOSAL: 5\n";
echo "  NEGOTIATION: 3\n";
echo "  CLOSED_WON: 2\n";?>