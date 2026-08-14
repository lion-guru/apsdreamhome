<?php
$file = 'C:\xampp\htdocs\apsdreamhome\routes\web.php';
$content = file_get_contents($file);

$replacements = [
    // Static Pages (keep in PageController)
    'Front\\\\PageController@about' => 'Front\\\\PageController@about',
    'Front\\\\PageController@team' => 'Front\\\\PageController@team',
    'Front\\\\PageController@opportunity' => 'Front\\\\PageController@opportunity',
    'Front\\\\PageController@faq' => 'Front\\\\PageController@faq',
    'Front\\\\PageController@faqs' => 'Front\\\\PageController@faqs',
    'Front\\\\PageController@sitemap' => 'Front\\\\PageController@sitemap',
    'Front\\\\PageController@createMobileApp' => 'Front\\\\PageController@createMobileApp',
    'Front\\\\PageController@comingSoon' => 'Front\\\\PageController@comingSoon',
    'Front\\\\PageController@downloads' => 'Front\\\\PageController@downloads',
    'Front\\\\PageController@underConstruction' => 'Front\\\\PageController@underConstruction',
    'Front\\\\PageController@thankYou' => 'Front\\\\PageController@thankYou',
    'Front\\\\PageController@customerReviews' => 'Front\\\\PageController@customerReviews',
    'Front\\\\PageController@navigation' => 'Front\\\\PageController@navigation',
    'Front\\\\PageController@reviewSubmit' => 'Front\\\\PageController@reviewSubmit',
    'Front\\\\PageController@analytics' => 'Front\\\\PageController@analytics',
    'Front\\\\PageController@setLanguage' => 'Front\\\\PageController@setLanguage',
    'Front\\\\PageController@home' => 'Front\\\\PageController@home',
    
    // Privacy -> LegalController
    'Front\\\\PageController@privacy' => 'Front\\\\LegalController@privacy',
    'Front\\\\PageController@legalPrivacy' => 'Front\\\\LegalController@privacy',
    'Front\\\\PageController@legalTermsPage' => 'Front\\\\LegalController@terms',
    'Front\\\\PageController@legalTermsConditions' => 'Front\\\\LegalController@terms',
    'Front\\\\PageController@legalServices' => 'Front\\\\LegalController@services',
    'Front\\\\PageController@legalDocuments' => 'Front\\\\LegalController@documents',
    'Front\\\\PageController@legal' => 'Front\\\\LegalController@index',
    'Front\\\\PageController@howItWorks' => 'Front\\\\LegalController@howItWorks',
    
    // News -> BlogController
    'Front\\\\PageController@news' => 'Front\\\\BlogController@index',
    'Front\\\\PageController@newsView' => 'Front\\\\BlogController@show',
    
    // Gallery (keep in PageController)
    'Front\\\\PageController@gallery' => 'Front\\\\PageController@gallery',
    
    // Resell -> ServiceController
    'Front\\\\PageController@resell' => 'Front\\\\ServiceController@resell',
    
    // Associate -> AssociateController
    'Front\\\\PageController@becomeAssociate' => 'Front\\\\AssociateController@becomeAssociate',
    'Front\\\\PageController@mlmDashboard' => 'Front\\\\AssociateController@mlmDashboard',
    
    // AI -> AIController
    'Front\\\\PageController@whatsappChat' => 'Front\\\\AIController@whatsappChat',
    'Front\\\\PageController@userAiSuggestions' => 'Front\\\\AIController@userAiSuggestions',
    'Front\\\\PageController@aiChatbotPage' => 'Front\\\\AIController@aiChatbotPage',
    'Front\\\\PageController@aiValuation' => 'Front\\\\AIController@aiValuation',
    'Front\\\\PageController@virtualTour' => 'Front\\\\AIController@virtualTour',
    
    // User Dashboard -> UserDashboardController
    'Front\\\\PageController@userInvestments' => 'Front\\\\UserDashboardController@userInvestments',
    'Front\\\\PageController@userEditProfile' => 'Front\\\\UserDashboardController@userEditProfile',
    'Front\\\\PageController@userSavedSearches' => 'Front\\\\UserDashboardController@userSavedSearches',
    'Front\\\\PageController@userNotifications' => 'Front\\\\UserDashboardController@userNotifications',
    
    // Builder Registration (keep)
    'Front\\\\PageController@builderRegistration' => 'Front\\\\PageController@builderRegistration',
    
    // Plots -> PropertyController
    'Front\\\\PageController@plotsAvailability' => 'Front\\\\PropertyController@plotsAvailability',
    'Front\\\\PageController@plotMap' => 'Front\\\\ProjectController@plotMap',
    'Front\\\\PageController@plot' => 'Front\\\\PropertyController@plot',
    
    // Tools -> ToolController
    'Front\\\\PageController@stampDutyCalculator' => 'Front\\\\ToolController@stampDutyCalculator',
    'Front\\\\PageController@plotSizeConverter' => 'Front\\\\ToolController@plotSizeConverter',
    'Front\\\\PageController@homeLoanEligibility' => 'Front\\\\ToolController@homeLoanEligibility',
    'Front\\\\PageController@propertyValuation' => 'Front\\\\ToolController@propertyValuation',
    'Front\\\\PageController@toolsHub' => 'Front\\\\ToolController@toolsHub',
    'Front\\\\PageController@partnerTools' => 'Front\\\\ToolController@partnerTools',
    'Front\\\\PageController@rentVsBuy' => 'Front\\\\ToolController@rentVsBuy',
    'Front\\\\PageController@sipVsRealestate' => 'Front\\\\ToolController@sipVsRealestate',
    'Front\\\\PageController@capitalGains' => 'Front\\\\ToolController@capitalGains',
    'Front\\\\PageController@gstCalculator' => 'Front\\\\ToolController@gstCalculator',
    'Front\\\\PageController@constructionCostEstimator' => 'Front\\\\ToolController@constructionCostEstimator',
    'Front\\\\PageController@rentalYieldCalculator' => 'Front\\\\ToolController@rentalYieldCalculator',
    'Front\\\\PageController@propertyTaxCalculator' => 'Front\\\\ToolController@propertyTaxCalculator',
    'Front\\\\PageController@calc' => 'Front\\\\ToolController@calc',
    'Front\\\\PageController@reraLookup' => 'Front\\\\ToolController@reraLookup',
    'Front\\\\PageController@plotConverter' => 'Front\\\\ToolController@plotConverter',
    'Front\\\\PageController@valuationCalculator' => 'Front\\\\ToolController@valuationCalculator',
    
    // Career -> CareerController
    'Front\\\\PageController@careerApply' => 'Front\\\\CareerController@careerApply',
    'Front\\\\PageController@submitCareerApplication' => 'Front\\\\CareerController@submitCareerApplication',
    'Front\\\\PageController@careerJobs' => 'Front\\\\CareerController@careerJobs',
    'Front\\\\PageController@careerJobDetails' => 'Front\\\\CareerController@careerJobDetails',
    'Front\\\\PageController@opportunity' => 'Front\\\\CareerController@opportunity',
    
    // Properties -> PropertyController
    'Front\\\\PageController@properties' => 'Front\\\\PropertyController@properties',
    'Front\\\\PageController@featuredProperties' => 'Front\\\\PropertyController@getFeaturedProperties',
    'Front\\\\PageController@propertyDetails' => 'Front\\\\PropertyController@propertyDetails',
    'Front\\\\PageController@propertyInquiry' => 'Front\\\\PropertyController@propertyInquiry',
    'Front\\\\PageController@propertyInterest' => 'Front\\\\PropertyController@propertyInterest',
    'Front\\\\PageController@listProperty' => 'Front\\\\PropertyController@listProperty',
    'Front\\\\PageController@handlePropertyListing' => 'Front\\\\PropertyController@handlePropertyListing',
    'Front\\\\PageController@propertySubmit' => 'Front\\\\PropertyController@listProperty',
    'Front\\\\PageController@handleQuickInquiry' => 'Front\\\\ContactController@handleQuickInquiry',
    
    // Projects -> ProjectController
    'Front\\\\PageController@projects' => 'Front\\\\ProjectController@projects',
    'Front\\\\PageController@projectDetails' => 'Front\\\\ProjectController@projectDetails',
    'Front\\\\PageController@projectsByLocation' => 'Front\\\\ProjectController@projectsByLocation',
    'Front\\\\PageController@colonyDetail' => 'Front\\\\ProjectController@colonyDetail',
    'Front\\\\PageController@colonies' => 'Front\\\\ProjectController@colonies',
    'Front\\\\PageController@budhaCity' => 'Front\\\\ProjectController@budhaCity',
    'Front\\\\PageController@location' => 'Front\\\\ProjectController@location',
    'Front\\\\PageController@suyodayColonyPage' => 'Front\\\\ProjectController@suyodayColonyPage',
    
    // Financial -> FinancialController
    'Front\\\\PageController@financialServices' => 'Front\\\\FinancialController@financialServices',
    'Front\\\\PageController@financialContact' => 'Front\\\\FinancialController@financialContact',
    'Front\\\\PageController@bank' => 'Front\\\\FinancialController@bank',
    
    // Services -> ServiceController
    'Front\\\\PageController@services' => 'Front\\\\ServiceController@services',
    'Front\\\\PageController@interiorDesign' => 'Front\\\\ServiceController@interiorDesign',
    'Front\\\\PageController@constructionServices' => 'Front\\\\ServiceController@constructionServices',
    'Front\\\\PageController@constructionInquiry' => 'Front\\\\ServiceController@constructionInquiry',
    'Front\\\\PageController@documents' => 'Front\\\\ServiceController@documents',
    'Front\\\\PageController@inquiry' => 'Front\\\\ServiceController@inquiry',
    
    // Contact -> ContactController
    'Front\\\\PageController@contact' => 'Front\\\\ContactController@contact',
    'Front\\\\PageController@serviceInterest' => 'Front\\\\ContactController@serviceInterest',
    'Front\\\\PageController@scheduleMeeting' => 'Front\\\\ContactController@scheduleMeeting',
    'Front\\\\PageController@handleScheduleMeeting' => 'Front\\\\ContactController@handleScheduleMeeting',
    'Front\\\\PageController@support' => 'Front\\\\ContactController@support',
    
    // Buy/Sell/Rent/Invest -> PropertyController
    'Front\\\\PageController@buyProperty' => 'Front\\\\PropertyController@buyProperty',
    'Front\\\\PageController@sellProperty' => 'Front\\\\PropertyController@sellProperty',
    'Front\\\\PageController@rentProperty' => 'Front\\\\PropertyController@rentProperty',
    'Front\\\\PageController@investProperty' => 'Front\\\\PropertyController@investProperty',
    
    // Document Gallery (keep in PageController for now)
    'Front\\\\PageController@documentGallery' => 'Front\\\\PageController@documentGallery',
    'Front\\\\PageController@downloadDocument' => 'Front\\\\PageController@downloadDocument',
];

$replaced = 0;
foreach ($replacements as $from => $to) {
    $count = substr_count($content, $from);
    if ($count > 0) {
        $content = str_replace($from, $to, $content);
        $replaced += $count;
        echo "Replaced $count: $from -> $to\n";
    }
}

echo "Total replacements: $replaced\n";
file_put_contents($file, $content);
echo "Routes file updated successfully!\n";?>