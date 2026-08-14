<?php
require_once __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

$current = $db->fetchOne("SELECT COUNT(*) as c FROM blog_posts");
echo "Current blog posts: " . $current['c'] . "\n";

$newPosts = [
    [
        'title' => 'Why Gorakhpur is Becoming a Real Estate Hub in 2026',
        'slug' => 'gorakhpur-real-estate-hub-2026',
        'content' => '<h2>Gorakhpur: The Rising Star of Eastern UP Real Estate</h2><p>Gorakhpur has emerged as one of the fastest-growing real estate markets in Eastern Uttar Pradesh. With infrastructure development, improved connectivity, and growing industrial presence, the city offers tremendous potential for property investors.</p><h3>Key Growth Drivers</h3><ul><li><strong>Infrastructure Development:</strong> New flyovers, road widening, and metro rail proposals are transforming the cityscape.</li><li><strong>Industrial Growth:</strong> Proximity to UP Industrial Corridor attracting businesses.</li><li><strong>Educational Hub:</strong> Home to major institutions driving rental demand.</li><li><strong>Connectivity:</strong> Improved rail and air connectivity.</li></ul><h3>Why Invest Now?</h3><p>With property prices still affordable and multiple development projects in the pipeline, 2026 is the ideal time to invest in Gorakhpur real estate.</p>',
        'category' => 'Market Trends',
        'featured_image' => 'assets/images/projects/gorakhpur/suryoday.jpg',
        'author' => 'Abhaay Singh',
    ],
    [
        'title' => 'Understanding Plot Registration Process in Uttar Pradesh',
        'slug' => 'plot-registration-process-up',
        'content' => '<h2>Complete Guide to Plot Registration in UP</h2><p>Buying a plot in Uttar Pradesh involves a specific legal process that every buyer must follow.</p><h3>Documents Required</h3><ul><li>Sale deed / Agreement to Sell</li><li>Identity proof (Aadhaar, PAN)</li><li>Property documents (ownership chain)</li><li>No Objection Certificate (if applicable)</li><li>Stamp duty payment receipt</li></ul><h3>Registration Process</h3><ol><li>Stamp Duty Payment (currently 5% in UP)</li><li>Sub-Registrar Office Visit with all documents</li><li>Document Verification by sub-registrar</li><li>Biometric Verification</li><li>Registration and number assignment</li><li>Collection of registered deed (2-3 working days)</li></ol><p>At APS Dream Home, we handle the entire registration process for our plot buyers.</p>',
        'category' => 'Legal',
        'featured_image' => 'assets/images/hero/about.jpg',
        'author' => 'Shushant Srivastava',
    ],
    [
        'title' => 'Top 10 Things to Check Before Buying a Plot',
        'slug' => 'top-10-things-check-buying-plot',
        'content' => '<h2>Essential Checklist for Plot Buyers</h2><p>Buying a plot is a significant investment. Here are the 10 most important things to verify.</p><ol><li><strong>Title Verification:</strong> Check 15-20 years of ownership history.</li><li><strong>Land Use Classification:</strong> Ensure residential/commercial as per your use.</li><li><strong>Encumbrance Certificate:</strong> No outstanding loans or disputes.</li><li><strong>Approvals and Permits:</strong> Colony approved by development authority.</li><li><strong>Road Access:</strong> Proper road access is mandatory.</li><li><strong>Utility Connections:</strong> Water, electricity, sewerage availability.</li><li><strong>Neighborhood Analysis:</strong> Visit at different times of day.</li><li><strong>Future Development Plans:</strong> Upcoming infrastructure projects.</li><li><strong>Developer Reputation:</strong> Check track record and past projects.</li><li><strong>Legal Opinion:</strong> Always get a lawyer to review before finalizing.</li></ol><p><strong>APS Tip:</strong> Our team provides complete documentation and title verification for all our plots.</p>',
        'category' => 'Buyer Guide',
        'featured_image' => 'assets/images/colony-dev-1.jpg',
        'author' => 'Vijay Verma',
    ],
    [
        'title' => 'How to Calculate Stamp Duty and Registration Charges',
        'slug' => 'calculate-stamp-duty-registration-charges',
        'content' => '<h2>Understanding Stamp Duty in Uttar Pradesh</h2><p>Stamp duty is a mandatory tax paid to the government when buying property.</p><h3>Current Rates (2026)</h3><ul><li><strong>Residential Plot:</strong> 5% of circle rate or agreement value</li><li><strong>Commercial Plot:</strong> 7% of circle rate or agreement value</li><li><strong>Agricultural Land:</strong> 3% of circle rate</li></ul><h3>Additional Charges</h3><ul><li><strong>Registration Fee:</strong> 1% (minimum 500 INR)</li><li><strong>Transfer Fee:</strong> 500 INR for plots up to 5 lakh, 1000 INR higher</li></ul><h3>Example Calculation</h3><p>For a plot worth 20 lakh: Stamp Duty (5%) = 1,00,000 + Registration (1%) = 20,000. Total = 1,20,000.</p><p>UP government offers e-stamping facility through SHCIL for online payment.</p>',
        'category' => 'Legal',
        'featured_image' => 'assets/images/hero/about.jpg',
        'author' => 'Shushant Srivastava',
    ],
    [
        'title' => 'Benefits of Investing in Colony Plots vs Individual Land',
        'slug' => 'colony-plots-vs-individual-land',
        'content' => '<h2>Why Colony Plots Offer Better Value</h2><p>When investing in real estate, buyers face the choice between colony plots and individual land parcels.</p><h3>Advantages of Colony Plots</h3><ul><li><strong>Clear Title:</strong> Reputed developers provide complete documentation.</li><li><strong>Ready Infrastructure:</strong> Roads, drainage, water supply, electricity.</li><li><strong>Higher Appreciation:</strong> 15-25% faster appreciation than raw land.</li><li><strong>Community Living:</strong> Security, parks, community halls.</li><li><strong>Easy Financing:</strong> Banks prefer colony plots with clear docs.</li><li><strong>Lower Risk:</strong> Less risk of legal disputes or encroachment.</li></ul><p>APS Dream Home colonies offer DTCP/RERA approved layouts, wide roads, underground drainage, 24/7 water supply, parks, and compound walls.</p>',
        'category' => 'Investment Tips',
        'featured_image' => 'assets/images/colony-dev-2.jpg',
        'author' => 'Pramod Sharma',
    ],
];

$added = 0;
foreach ($newPosts as $post) {
    $existing = $db->fetchOne("SELECT id FROM blog_posts WHERE slug = ?", [$post['slug']]);
    if ($existing) {
        echo "Skipping (exists): " . $post['title'] . "\n";
        continue;
    }
    
    try {
        $db->execute(
            "INSERT INTO blog_posts (title, slug, content, category, featured_image, status, author_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'published', 1, NOW(), NOW())",
            [$post['title'], $post['slug'], $post['content'], $post['category'], $post['featured_image']]
        );
        echo "Added: " . $post['title'] . "\n";
        $added++;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\nAdded: $added\n";
$final = $db->fetchOne("SELECT COUNT(*) as c FROM blog_posts");
echo "Total blog posts: " . $final['c'] . "\n";?>