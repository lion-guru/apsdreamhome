<?php
/**
 * Seed comprehensive site settings into site_content table
 * Run: php scripts/seed_site_settings.php
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$defaults = [
    // ── Company Info ──
    ['settings', 'company_name', 'APS Dream Home', 'text', 'company', 1],
    ['settings', 'company_tagline', 'Building Dreams, Delivering Trust', 'text', 'company', 2],
    ['settings', 'company_logo', 'assets/images/logo.png', 'image', 'company', 3],
    ['settings', 'company_favicon', 'assets/img/favicon.png', 'image', 'company', 4],
    ['settings', 'company_reg_number', 'U70109UP2022PTC163047', 'text', 'company', 5],
    ['settings', 'company_cin', 'U70109UP2022PTC163047', 'text', 'company', 6],
    ['settings', 'company_gst', '09AABCA1234F1Z5', 'text', 'company', 7],
    ['settings', 'company_pan', 'AABCA1234F', 'text', 'company', 8],

    // ── Contact Info ──
    ['settings', 'contact_phone', '+91 92771 21112', 'text', 'contact', 10],
    ['settings', 'contact_phone_2', '+91 92771 21113', 'text', 'contact', 11],
    ['settings', 'contact_email', 'info@apsdreamhome.com', 'text', 'contact', 12],
    ['settings', 'contact_email_2', 'sales@apsdreamhome.com', 'text', 'contact', 13],
    ['settings', 'contact_whatsapp', '+919277121112', 'text', 'contact', 14],
    ['settings', 'contact_address', 'Gorakhpur, Uttar Pradesh, India', 'textarea', 'contact', 15],
    ['settings', 'contact_address_full', 'Near Railway Station, Gorakhpur, Uttar Pradesh - 273001, India', 'textarea', 'contact', 16],
    ['settings', 'contact_map_lat', '26.7606', 'text', 'contact', 17],
    ['settings', 'contact_map_lng', '83.3730', 'text', 'contact', 18],
    ['settings', 'contact_working_hours', 'Mon - Sat: 9:00 AM - 7:00 PM', 'text', 'contact', 19],

    // ── Social Media ──
    ['settings', 'social_facebook', 'https://facebook.com/apsdreamhome', 'text', 'social', 20],
    ['settings', 'social_instagram', 'https://instagram.com/apsdreamhome', 'text', 'social', 21],
    ['settings', 'social_twitter', 'https://twitter.com/apsdreamhome', 'text', 'social', 22],
    ['settings', 'social_youtube', 'https://youtube.com/@apsdreamhome', 'text', 'social', 23],
    ['settings', 'social_linkedin', 'https://linkedin.com/company/apsdreamhome', 'text', 'social', 24],
    ['settings', 'social_telegram', 'https://t.me/apsdreamhome', 'text', 'social', 25],

    // ── SEO Defaults ──
    ['settings', 'seo_title', 'APS Dream Home - Premium Real Estate in Uttar Pradesh', 'text', 'seo', 30],
    ['settings', 'seo_description', 'Find your dream home with APS Dream Home. Premium plots and properties in Gorakhpur, Lucknow, Kushinagar & across Uttar Pradesh.', 'textarea', 'seo', 31],
    ['settings', 'seo_keywords', 'real estate, plots, homes, Gorakhpur, Lucknow, Kushinagar, Uttar Pradesh, property, buy plot, sell property', 'textarea', 'seo', 32],
    ['settings', 'seo_og_image', 'assets/images/og-default.jpg', 'image', 'seo', 33],

    // ── Footer Content ──
    ['settings', 'footer_about', 'APS Dream Home is a trusted real estate company based in Gorakhpur, UP. We help families find their dream homes with transparent dealings and quality construction.', 'textarea', 'footer', 40],
    ['settings', 'footer_copyright', '© 2026 APS Dream Home. All rights reserved.', 'text', 'footer', 41],
    ['settings', 'footer_developer', 'Developed by APS Tech Team', 'text', 'footer', 42],

    // ── Homepage Hero ──
    ['settings', 'hero_title', 'Find Your Dream Home in Uttar Pradesh', 'text', 'hero', 50],
    ['settings', 'hero_subtitle', 'Trusted real estate partner for 2000+ families. Premium plots and homes in Gorakhpur, Lucknow, Kushinagar & beyond.', 'textarea', 'hero', 51],
    ['settings', 'hero_cta_text', 'Explore Properties', 'text', 'hero', 52],
    ['settings', 'hero_cta_url', '/properties', 'text', 'hero', 53],
    ['settings', 'hero_badge', 'Trusted by 2000+ Families', 'text', 'hero', 54],

    // ── Homepage Stats ──
    ['settings', 'stat_properties_label', 'Properties Sold', 'text', 'home_stats', 60],
    ['settings', 'stat_properties_value', '500+', 'text', 'home_stats', 61],
    ['settings', 'stat_families_label', 'Happy Families', 'text', 'home_stats', 62],
    ['settings', 'stat_families_value', '2000+', 'text', 'home_stats', 63],
    ['settings', 'stat_projects_label', 'Projects Completed', 'text', 'home_stats', 64],
    ['settings', 'stat_projects_value', '50+', 'text', 'home_stats', 65],
    ['settings', 'stat_experience_label', 'Years Experience', 'text', 'home_stats', 66],
    ['settings', 'stat_experience_value', '8+', 'text', 'home_stats', 67],

    // ── WhatsApp Widget ──
    ['settings', 'whatsapp_enabled', '1', 'text', 'widget', 70],
    ['settings', 'whatsapp_message', 'Hi! I am interested in your properties.', 'textarea', 'widget', 71],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO site_content (section, content_key, content_value, content_type, content_group, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
$count = 0;
foreach ($defaults as $row) {
    $stmt->execute($row);
    if ($stmt->rowCount() > 0) $count++;
}
echo "✓ {$count} settings seeded\n";
echo "Done!\n";
