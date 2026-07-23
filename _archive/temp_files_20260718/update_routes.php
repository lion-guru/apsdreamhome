<?php
$content = file_get_contents('C:\xampp\htdocs\apsdreamhome\routes\web.php');

$replacements = [
    // Privacy, Terms, Legal
    'Front\\PageController@privacy' => 'Front\\LegalController@privacy',
    'Front\\PageController@legalTermsConditions' => 'Front\\LegalController@terms',
    'Front\\PageController@legalServices' => 'Front\\LegalController@legalServices',
    'Front\\PageController@legalDocuments' => 'Front\\LegalController@documents',
    'Front\\PageController@legal' => 'Front\\LegalController@terms',
    'Front\\PageController@legalPrivacy' => 'Front\\LegalController@privacy',
    'Front\\PageController@legalTermsPage' => 'Front\\LegalController@terms',
    'Front\\PageController@howItWorks' => 'Front\\LegalController@howItWorks',
    
    // News, Gallery
    'Front\\PageController@news' => 'Front\\BlogController@index',
    'Front\\PageController@gallery' => 'Front\\PageController@gallery',
    
    // Resell
    'Front\\PageController@resell' => 'Front\\ServiceController@resell',
    
    // Associate
    'Front\\PageController@becomeAssociate' => 'Front\\AssociateController@becomeAssociate',
    
    // AI
    'Front\\PageController@whatsappChat' => 'Front\\AIController@whatsappChat',
    'Front\\PageController@userAiSuggestions' => 'Front\\AIController@userAiSuggestions',
    'Front\\PageController@aiChatbotPage' => 'Front\\AIController@aiChatbotPage',
    
    // User Dashboard
    'Front\\PageController@userInvestments' => 'Front\\UserDashboardController@userInvestments',
    'Front\\PageController@userEditProfile' => 'Front\\UserDashboardController@userEditProfile',
    
    // Builder Registration
    'Front\\PageController@builderRegistration' => 'Front\\PageController@builderRegistration',
    
    // Plots
    'Front\\PageController@plotsAvailability' => 'Front\\PropertyController@plotsAvailability',
    'Front\\PageController@plotMap' => 'Front\\ProjectController@plotMap',
    
    // Tools
    'Front\\PageController@stampDutyCalculator' => 'Front\\ToolController@stampDutyCalculator',
    'Front\\PageController@plotSizeConverter' => 'Front\\ToolController@plotSizeConverter',
    'Front\\PageController@homeLoanEligibility' => 'Front\\ToolController@homeLoanEligibility',
    'Front\\PageController@propertyValuation' => 'Front\\ToolController@propertyValuation',
    'Front\\PageController@toolsHub' => 'Front\\ToolController@toolsHub',
    'Front\\PageController@partnerTools' => 'Front\\ToolController@partnerTools',
    'Front\\PageController@rentVsBuy' => 'Front\\ToolController@rentVsBuy',
    'Front\\PageController@sipVsRealestate' => 'Front\\ToolController@sipVsRealestate',
    'Front\\PageController@capitalGains' => 'Front\\ToolController@capitalGains',
    'Front\\PageController@gstCalculator' => 'Front\\ToolController@gstCalculator',
    'Front\\PageController@constructionCostEstimator' => 'Front\\ToolController@constructionCostEstimator',
    'Front\\PageController@rentalYieldCalculator' => 'Front\\ToolController@rentalYieldCalculator',
    'Front\\PageController@propertyTaxCalculator' => 'Front\\ToolController@propertyTaxCalculator',
    'Front\\PageController@reraLookup' => 'Front\\LegalController@reraLookup',
    'Front\\PageController@plotConverter' => 'Front\\ToolController@plotConverter',
    'Front\\PageController@valuationCalculator' => 'Front\\ToolController@valuationCalculator',
    
    // Careers
    'Front\\PageController@careerApply' => 'Front\\CareerController@careerApply',
    'Front\\PageController@submitCareerApplication' => 'Front\\CareerController@submitCareerApplication',
    'Front\\PageController@careerJobs' => 'Front\\CareerController@careerJobs',
    'Front\\PageController@careerJobDetails' => 'Front\\CareerController@careerJobDetails',
    
    // Properties
    'Front\\PageController@properties' => 'Front\\PropertyController@properties',
    'Front\\PageController@featuredProperties' => 'Front\\PropertyController@getFeaturedProperties',
    'Front\\PageController@propertyDetails' => 'Front\\PropertyController@propertyDetails',
    'Front\\PageController@propertyInquiry' => 'Front\\PropertyController@propertyInquiry',
    'Front\\PageController@propertyInterest' => 'Front\\PropertyController@propertyInterest',
    'Front\\PageController@listProperty' => 'Front\\PropertyController@listProperty',
    'Front\\PageController@handlePropertyListing' => 'Front\\PropertyController@handlePropertyListing',
    'Front\\PageController@propertySubmit' => 'Front\\PropertyController@listProperty',
    'Front\\PageController@handleQuickInquiry' => 'Front\\ContactController@handleQuickInquiry',
    
    // Projects
    'Front\\PageController@projects' => 'Front\\ProjectController@projects',
    'Front\\PageController@projectDetails' => 'Front\\ProjectController@projectDetails',
    'Front\\PageController@projectsByLocation' => 'Front\\ProjectController@projectsByLocation',
    'Front\\PageController@colonyDetail' => 'Front\\ProjectController@colonyDetail',
    'Front\\PageController@colonies' => 'Front\\ProjectController@colonies',
    'Front\\PageController@budhaCity' => 'Front\\ProjectController@budhaCity',
    'Front\\PageController@location' => 'Front\\ProjectController@location',
    
    // Financial
    'Front\\PageController@financialServices' => 'Front\\FinancialController@financialServices',
    'Front\\PageController@financialContact' => 'Front\\FinancialController@financialContact',
    'Front\\PageController@bank' => 'Front\\FinancialController@bank',
    
    // Services
    'Front\\PageController@interiorDesign' => 'Front\\ServiceController@interiorDesign',
    'Front\\PageController@constructionServices' => 'Front\\ServiceController@constructionServices',
    'Front\\PageController@constructionInquiry' => 'Front\\ServiceController@constructionInquiry',
    'Front\\PageController@inquiry' => 'Front\\ServiceController@inquiry',
    
    // Documents
    'Front\\PageController@documentGallery' => 'Front\\ServiceController@documents',
    'Front\\PageController@downloadDocument' => 'Front\\ServiceController@downloadDocument',
    
    // Buy/Sell/Rent/Invest
    'Front\\PageController@buyProperty' => 'Front\\PropertyController@buyProperty',
    'Front\\PageController@sellProperty' => 'Front\\PropertyController@sellProperty',
    'Front\\PageController@rentProperty' => 'Front\\PropertyController@rentProperty',
    'Front\\PageController@investProperty' => 'Front\\PropertyController@investProperty',
    
    // Schedule Meeting
    'Front\\PageController@scheduleMeeting' => 'Front\\ContactController@scheduleMeeting',
    'Front\\PageController@handleScheduleMeeting' => 'Front\\ContactController@handleScheduleMeeting',
    
    // Service Interest
    'Front\\PageController@serviceInterest' => 'Front\\ContactController@serviceInterest',
];

$count = 0;
foreach ($replacements as $old => $new) {
    $oldCount = substr_count($content, $old);
    if ($oldCount > 0) {
        $content = str_replace($old, $new, $content);
        $count += $oldCount;
        echo "Replaced $oldCount occurrences of '$old' -> '$new'\n";
    }
}

file_put_contents('C:\xampp\htdocs\apsdreamhome\routes\web.php', $content);
echo "\nTotal replacements: $count\n";
echo "Routes updated successfully!\n";