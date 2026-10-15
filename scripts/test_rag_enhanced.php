<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\AI\RAGKnowledgeManager;

$rag = RAGKnowledgeManager::getInstance();

// Get colony data from database
$db = \App\Core\Database\Database::getInstance();
$colonies = $db->fetchAll("SELECT id, name, location, starting_price, min_price_per_sqft, total_plots, amenities, key_highlights, description, rera_number FROM colonies WHERE is_active = 1");

// Get legal documents
$legalDocs = $db->fetchAll("SELECT id, title, content, document_type FROM legal_documents WHERE status = 'active' LIMIT 10");

// Get FAQ data
$faqs = $db->fetchAll("SELECT question, answer, category FROM faqs WHERE status = 'active' LIMIT 20");

// Get pricing data from colony_price_revisions
$pricing = $db->fetchAll("SELECT colony_id, current_price, new_price FROM colony_price_revisions WHERE is_active = 1 LIMIT 20");

// Build documents from database
$docs = [];

// Company overview
$docs[] = [
    'title' => 'APS Dream Home - Company Overview',
    'content' => 'APS Dream Home is a premium real estate company based in Gorakhpur, Uttar Pradesh. We specialize in residential plots, commercial spaces, and ready-to-move properties across Gorakhpur, Lucknow, and surrounding areas. Founded in 2010, we have served over 5000+ families with transparent dealings, legal verification, and flexible EMI options. Our colonies include Suryoday Heights, Suryoday Colony, Braj Radha Nagri, Lakeview Residency, Budh Bihar Colony, Raghunath Nagri, APS Motiram Township, and more.',
    'source' => 'company_profile',
    'tags' => ['company', 'overview', 'gorakhpur', 'real estate'],
];

// Add colony documents from database
foreach ($colonies as $colony) {
    $amenities = json_decode($colony['amenities'] ?? '[]', true) ?? [];
    $highlights = json_decode($colony['key_highlights'] ?? '[]', true) ?? [];
    
    $content = "{$colony['name']} is located at {$colony['location']}. ";
    $content .= "Starting price: ₹" . number_format($colony['starting_price'], 0) . ". ";
    if ($colony['min_price_per_sqft'] > 0) {
        $content .= "Minimum price per sq ft: ₹" . number_format($colony['min_price_per_sqft'], 2) . ". ";
    }
    $content .= "Total plots: {$colony['total_plots']}. ";
    $content .= "RERA Number: {$colony['rera_number']}. ";
    
    if (!empty($amenities)) {
        $content .= "Amenities: " . implode(', ', $amenities) . ". ";
    }
    if (!empty($highlights)) {
        $content .= "Key highlights: " . implode(', ', $highlights) . ". ";
    }
    if (!empty($colony['description'])) {
        $content .= "Description: {$colony['description']}. ";
    }
    
    $docs[] = [
        'title' => $colony['name'] . ' - Colony Details',
        'content' => $content,
        'source' => 'colony_details',
        'tags' => ['colony', strtolower(str_replace(' ', '-', $colony['name'])), 'gorakhpur', 'plots'],
    ];
}

// Legal documentation
$docs[] = [
    'title' => 'Legal Documentation and RERA Compliance',
    'content' => 'All our properties are RERA registered (UPRERA). We provide complete legal documentation: Title deed, Encumbrance certificate, Khatauni, Registry papers, Mutation records, NOC from authorities. Our legal team verifies every property before listing. We assist with: Registry process, Stamp duty calculation (UP rates: Male 7%, Female 6%, Joint 6.5%), Mutation, Possession certificate.',
    'source' => 'legal',
    'tags' => ['legal', 'rera', 'documentation', 'registry', 'stamp duty'],
];

// EMI Plans
$docs[] = [
    'title' => 'EMI Plans and Payment Options',
    'content' => 'We offer flexible EMI plans for all properties. Standard EMI: 0% interest for 12-24 months. Extended EMI: Low interest (8-10%) for 36-60 months. Down payment: 10-20% of property value. Bank loan assistance available with HDFC, SBI, ICICI, Axis, PNB. Pre-closure allowed after 6 months with nominal charges. EMI calculator available on website.',
    'source' => 'finance',
    'tags' => ['emi', 'finance', 'payment', 'loan', 'bank'],
];

// PLC Pricing
$docs[] = [
    'title' => 'PLC Pricing Rules',
    'content' => 'Preferential Location Charges (PLC) apply to plots based on location advantages. Standard PLC charges: Corner plot +10%, Park facing +5%, Wide road (40ft+) +10%, Corner + Park facing +15%, Corner + Wide road +20%. Maximum PLC capped at 25% of base price. PLC is added to base price before EMI calculation. Corner plots at Suryoday Colony: +10% on base ₹13.65/sqft = ₹15.02/sqft. Park facing at Braj Radha Nagri: +5% on base price.',
    'source' => 'pricing',
    'tags' => ['plc', 'pricing', 'charges', 'corner', 'park-facing', 'wide-road'],
];

// Stamp Duty
$docs[] = [
    'title' => 'Stamp Duty and Registration Charges UP',
    'content' => 'Uttar Pradesh Stamp Duty rates (2024): Male buyer: 7% of property value. Female buyer: 6% (1% concession). Joint ownership (Male+Female): 6.5%. Joint (Female+Female): 6%. Registration fee: 1% of property value (capped at ₹30,000). Additional charges: Lawyer fees (₹5,000-15,000), Encumbrance certificate (₹200-500), Khatauni (₹50-100). Total registration cost typically 8-9% for male, 7-8% for female buyers.',
    'source' => 'legal',
    'tags' => ['stamp duty', 'registration', 'up', 'charges', 'female concession'],
];

// Property booking process
$docs[] = [
    'title' => 'Property Booking Process',
    'content' => 'Booking process: 1) Site visit (schedule via website or call), 2) Select plot, 3) Pay token amount (₹11,000), 4) Plot held for 7 days, 4) Pay 10% within 30 days, 5) Sign agreement, 6) Pay remaining via EMI or lump sum, 7) Registry at SRO, 8) Possession handover. Token amount refundable if cancelled within 7 days. Online booking available at apsdreamhome.com.',
    'source' => 'process',
    'tags' => ['booking', 'process', 'token', 'agreement', 'registry'],
];

// Pre-closure policy
$docs[] = [
    'title' => 'Pre-closure and Foreclosure Policy',
    'content' => 'Pre-closure allowed after 6 months from booking date. Nominal charges: 1% of outstanding amount (max ₹10,000). No charges after 12 months. Process: Submit written request, pay outstanding + charges, receive NOC within 7 days. Foreclosure allowed on resale/transfer with same charges. Partial pre-payment allowed (min ₹50,000) reduces EMI or tenure.',
    'source' => 'finance',
    'tags' => ['pre-closure', 'foreclosure', 'charges', 'noc', 'partial-payment'],
];

// Colony comparison
$docs[] = [
    'title' => 'Colony Comparison: Suryoday vs Braj Radha vs Raghunath',
    'content' => 'Suryoday Colony: Gorakhpur-Lucknow Highway, starting ₹9.99L, 250 plots, ₹13.65/sqft min, amenities: Club house, pool, gym, jogging track, shopping complex. Braj Radha Nagri: Braj Vihar, ₹1.5L starting, 40 plots, spiritual theme, temple view, meditation center. Raghunath Nagri: Raghunath Nagar, ₹999 starting, 262 plots, ₹539/sqft min. APS Motiram: Motiram Road, ₹1.4L starting, 91 plots, ₹17.31/sqft. Lakeview: starting ₹12L, 60 plots. Budh Bihar: Budh Vihar, ₹1250 starting, 12 plots, heritage sites nearby.',
    'source' => 'colony_comparison',
    'tags' => ['comparison', 'suryoday', 'braj-radha', 'raghunath', 'motiram'],
];

// Add FAQs from database
foreach ($faqs as $faq) {
    $docs[] = [
        'title' => 'FAQ: ' . $faq['question'],
        'content' => "Q: {$faq['question']}\n\nA: {$faq['answer']}",
        'source' => 'faq',
        'tags' => ['faq', strtolower($faq['category'] ?? 'general')],
    ];
}

// Add legal documents
foreach ($legalDocs as $legal) {
    $docs[] = [
        'title' => 'Legal: ' . $legal['title'],
        'content' => $legal['content'],
        'source' => 'legal',
        'tags' => ['legal', strtolower($legal['document_type'] ?? 'document')],
    ];
}

// Add colony pricing from colony_price_revisions
foreach ($pricing as $price) {
    $docs[] = [
        'title' => 'Pricing: Colony ' . $price['colony_id'],
        'content' => "Colony ID: {$price['colony_id']}. Current price: ₹" . number_format($price['current_price'], 0) . ". New price: ₹" . number_format($price['new_price'] ?? 0, 0) . ".",
        'source' => 'pricing',
        'tags' => ['pricing', 'colony-' . $price['colony_id']],
    ];
}

echo "Adding " . count($docs) . " documents to knowledge base...\n";

foreach ($docs as $doc) {
    $docId = $rag->addDocument($doc);
    echo "✅ Added: {$doc['title']} (ID: $docId)\n";
}

echo "\nTesting search...\n";

$queries = [
    'Suryoday Vihar plot price',
    'EMI options for buying plot',
    'Braj Radha Nagri amenities',
    'RERA registration process',
    'Stamp duty rates UP',
    'PLC charges corner plot',
    'stamp duty female buyer',
    'colony amenities suryoday',
    'plot booking process',
    'pre-closure charges',
    'colony comparison suryoday braj',
    'pre-closure policy',
    'booking token amount',
    'female stamp duty concession',
];

foreach ($queries as $query) {
    echo "\nQuery: $query\n";
    $results = $rag->search($query, 3);
    foreach ($results as $r) {
        echo "  - [{$r['score']}] {$r['doc_title']} (source: {$r['source']})\n";
        echo "    " . substr($r['text'], 0, 150) . "...\n";
    }
}

echo "\n✅ RAG Knowledge Base test completed!\n";