<?php
/**
 * Simple AI Knowledge Base Seeder
 * Uses PDO directly for MySQL connection
 */

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to MySQL\n\n";
    
    // Knowledge base data
    $knowledgeData = [
        ['general', 'hello|hi|namaste', 'Namaste! Main APS Dream Home ka AI assistant hoon. Aapki kya madad kar sakta hoon? 🏠'],
        ['general', 'aps dream home kya hai', '🏢 APS Dream Home - Premium Real Estate Developer\n✅ 10+ Years Excellence\n✅ 15+ Projects\n✅ 5000+ Happy Customers\n📞 +91 92771 21112'],
        ['property', 'suryoday heights', '🏗️ Suryoday Heights - Gorakhpur\n📐 1000-5000 sq ft plots\n💰 ₹5.5 Lakh se\n✅ Ready Possession\n📞 +91 92771 21112'],
        ['property', 'raghunath city center', '🏗️ Raghunath City Center - Gorakhpur\n🏢 Shops: ₹15 Lakh se\n🏠 Flats: ₹25 Lakh se\n✅ Prime Location\n📞 +91 92771 21112'],
        ['property', 'braj radha enclave', '🏗️ Braj Radha Enclave - Lucknow\n📐 1500-4000 sq ft\n💰 ₹8 Lakh se\n✅ Near Airport\n📞 +91 92771 21112'],
        ['property', 'plot kaha kaha hai', '📍 Available Locations:\n• Gorakhpur - Suryoday Heights\n• Lucknow - Braj Radha Enclave\n• Kushinagar - Buddh Bihar\n• Varanasi - Ganga Nagri\n📞 +91 92771 21112'],
        ['pricing', 'plot ka price', '💰 Price List:\n• 1000 sq ft - ₹5.5 Lakh\n• 2000 sq ft - ₹10.5 Lakh\n• 3000 sq ft - ₹15 Lakh\n• EMI starts at ₹12,400/month\n📞 +91 92771 21112'],
        ['pricing', 'emi kitni banegi', '💳 EMI Calculation:\n₹10 Lakh plot - ₹7 Lakh loan\n• 10 years: ₹12,400/month\n• 20 years: ₹8,700/month\n🏦 All major banks available\n📞 +91 92771 21112'],
        ['pricing', 'discount offer', '🎉 Current Offers:\n• 5% Cash Discount\n• Free Registration\n• ₹10,000 Referral Bonus\n⏰ Limited Time!\n📞 +91 92771 21112'],
        ['location', 'suryoday kaha hai', '📍 Suryoday Heights:\nRambagh, Gorakhpur\nNear Railway Station\n🗺️ apsdreamhome.com/locations\n📞 +91 92771 21112'],
        ['location', 'nearby facilities', '🏙️ Nearby:\n🚉 Railway - 2 km\n🏥 Hospital - 3 km\n🏫 School - 1.5 km\n🛒 Market - 1 km\n🙏 Gorakhnath Mandir - 5 km'],
        ['location', 'site visit', '🚗 Free Site Visit!\n• Mon-Sat: 10 AM - 6 PM\n• Pickup available\n• Expert Guide\n• Refreshments\n📞 Book: +91 92771 21112'],
        ['booking', 'book kaise karein', '📝 Booking Process:\n1. Site Visit\n2. Form Fill\n3. Token ₹11,000\n4. Agreement\n5. Payment\n📋 Aadhar, PAN required\n📞 +91 92771 21112'],
        ['booking', 'token money', '💰 Token Money: ₹11,000\n✅ Plot Blocking\n✅ Processing\n✅ 7 Days Validity\n✅ Adjustable in Price\n📞 +91 92771 21112'],
        ['loan', 'home loan', '🏦 Home Loan Available:\n• 80% Finance\n• 8.5%-11% Interest\n• 5-20 Years\n• HDFC, SBI, ICICI\n📞 Apply: +91 92771 21112'],
        ['loan', 'loan eligibility', '✅ Eligibility:\n• Salary: ₹25,000+/month\n• Business: ₹3L+/year\n• CIBIL: 650+\n• Age: 21-60\n📞 Check: +91 92771 21112'],
        ['legal', 'registry process', '⚖️ Registry:\n• Stamp Duty: 7%\n• Reg Fee: 1%\n• Same Day Process\n• Legal Help Included\n📞 +91 92771 21112'],
        ['legal', 'title clear hai', '✅ 100% Clear Title:\n• 30-Year Title Search\n• Bank Approved\n• Legal Certificate\n• Transparency\n📞 Report: +91 92771 21112'],
        ['contact', 'phone number', '📞 Contact:\nHelpline: +91 92771 21112\nSales: +91 92771 21113\nEmail: info@apsdreamhome.com\nOffice: Gorakhpur, UP'],
        ['services', 'interior design', '🎨 Interior Services:\n• 2D/3D Design\n• Modular Kitchen\n• False Ceiling\n• Packages: ₹500-2000/sq ft\n📞 +91 92771 21112'],
        ['services', 'rental agreement', '📄 Rental Agreement:\n• Drafting: ₹2,000\n• Registration: ₹1,000\n• Police Verification\n• Same Day\n📞 +91 92771 21112'],
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO ai_knowledge_base (category, question_pattern, answer) VALUES (?, ?, ?)");
    $inserted = 0;
    
    foreach ($knowledgeData as $k) {
        try {
            $stmt->execute($k);
            if ($stmt->rowCount() > 0) {
                echo "✅ Added: {$k[1]}\n";
                $inserted++;
            } else {
                echo "⚠️  Exists: {$k[1]}\n";
            }
        } catch (Exception $e) {
            echo "❌ Error: {$k[1]} - {$e->getMessage()}\n";
        }
    }
    
    echo "\n🎉 Done! Inserted: $inserted / " . count($knowledgeData) . "\n";
    
    // Show total count
    $count = $pdo->query("SELECT COUNT(*) FROM ai_knowledge_base")->fetchColumn();
    echo "📊 Total Q&A in database: $count\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
