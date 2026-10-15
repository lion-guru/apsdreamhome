<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\AI\RAGKnowledgeManager;

$rag = RAGKnowledgeManager::getInstance();

// Add sample documents
$docs = [
    [
        'title' => 'APS Dream Home - Company Overview',
        'content' => 'APS Dream Home is a premium real estate company based in Gorakhpur, Uttar Pradesh. We specialize in residential plots, commercial spaces, and ready-to-move properties across Gorakhpur, Lucknow, and surrounding areas. Founded in 2010, we have served over 5000+ families with transparent dealings, legal verification, and flexible EMI options. Our colonies include Suryoday Vihar, Braj Radha Nagri, Raghunath Puram, and more.',
        'source' => 'company_profile',
        'tags' => ['company', 'overview', 'gorakhpur', 'real estate'],
    ],
    [
        'title' => 'Suryoday Vihar Colony - Details',
        'content' => 'Suryoday Vihar is our flagship colony in Gorakhpur. It offers residential plots ranging from 1000 sq ft to 3000 sq ft. Amenities include: 24/7 security, underground electricity, wide roads (40-60 ft), water supply, drainage, park, community hall, and temple. All plots are RERA registered. Price starts from ₹3,500 per sq ft. PLC charges: Corner +10%, Park facing +5%, Wide road +10%.',
        'source' => 'colony_details',
        'tags' => ['suryoday vihar', 'colony', 'gorakhpur', 'plots', 'pricing'],
    ],
    [
        'title' => 'Braj Radha Nagri Colony - Details',
        'content' => 'Braj Radha Nagri is a premium colony near Lucknow bypass. Plot sizes: 1200-5000 sq ft. Features: Gated community, club house, swimming pool, gym, jogging track, children play area, rainwater harvesting. Price: ₹4,500-6,000 per sq ft. Special offers for early birds. Legal: RERA UP, clear title, bank loan approved.',
        'source' => 'colony_details',
        'tags' => ['braj radha nagri', 'colony', 'lucknow', 'plots', 'premium'],
    ],
    [
        'title' => 'EMI Plans and Payment Options',
        'content' => 'We offer flexible EMI plans for all properties. Standard EMI: 0% interest for 12-24 months. Extended EMI: Low interest (8-10%) for 36-60 months. Down payment: 10-20% of property value. Bank loan assistance available with HDFC, SBI, ICICI, Axis, PNB. Pre-closure allowed after 6 months with nominal charges. EMI calculator available on website.',
        'source' => 'finance',
        'tags' => ['emi', 'finance', 'payment', 'loan', 'bank'],
    ],
    [
        'title' => 'Legal Documentation and RERA Compliance',
        'content' => 'All our properties are RERA registered (UPRERA). We provide complete legal documentation: Title deed, Encumbrance certificate, Khatauni, Registry papers, Mutation records, NOC from authorities. Our legal team verifies every property before listing. We assist with: Registry process, Stamp duty calculation (UP rates: Male 7%, Female 6%, Joint 6.5%), Mutation, Possession certificate.',
        'source' => 'legal',
        'tags' => ['legal', 'rera', 'documentation', 'registry', 'stamp duty'],
    ],
];

echo "Adding sample documents to knowledge base...\n";

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