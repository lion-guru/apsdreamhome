<?php
$file = 'C:\xampp\htdocs\apsdreamhome\routes\web.php';
$content = file_get_contents($file);

$replacements = [
    // PageController -> PageController (core pages - keep)
    // These remain as PageController: home, about, team, faqs, comingSoon, thankYou, gallery, sitemap, mobile-app, privacy, news, downloads, underConstruction, customerReviews, opportunity, navigation, builderRegistration, setLanguage, analytics, howItWorks
    
    // Property routes -> PropertyController
    'Front\\PageController@properties' => 'Front\\PropertyController@properties',
    'Front\\PageController@featuredProperties' => 'Front\\PropertyController@getFeaturedProperties',
    'Front\\PageController@propertyDetails' => 'Front\\PropertyController@propertyDetails',
    'Front\\PageController@buyProperty' => 'Front\\PropertyController@buyProperty',
    'Front\\PageController@sellProperty' => 'Front\\PropertyController@sellProperty',
    'Front\\PageController@rentProperty' => 'Front\\PropertyController@rentProperty',
    'Front\\PageController@investProperty' => 'Front\\PropertyController@investProperty',
    'Front\\PageController@listProperty' => 'Front\\PropertyController@listProperty',
    'Front\\PageController@handlePropertyListing' => 'Front\\PropertyController@handlePropertyListing',
    'Front\\PageController@propertySubmit' => 'Front\\PropertyController@listProperty',
    'Front\\PageController@userPropertyDetail' => 'Front\\PropertyController@userPropertyDetail',
    'Front\\PageController@propertyInterest' => 'Front\\PropertyController@propertyInterest',
    'Front\\PageController@propertyInquiry' => 'Front\\PropertyController@propertyInquiry',
    
    // Project/Colony routes -> ProjectController
    'Front\\PageController@projects' => 'Front\\ProjectController@projects',
    'Front\\PageController@projectDetails' => 'Front\\ProjectController@projectDetails',
    'Front\\PageController@colonies' => 'Front\\ProjectController@colonies',
    'Front\\PageController@colonyDetail' => 'Front\\ProjectController@colonyDetail',
    'Front\\PageController@colonyPlots' => 'Front\\ProjectController@colonyPlots',
    'Front\\PageController@projectsByLocation' => 'Front\\ProjectController@projectsByLocation',
    'Front\\PageController@location' => 'Front\\ProjectController@location',
    'Front\\PageController@plotMap' => 'Front\\ProjectController@plotMap',
    'Front\\PageController@budhaCity' => 'Front\\ProjectController@budhaCity',
    'Front\\PageController@suyodayColonyPage' => 'Front\\ProjectController@suyodayColonyPage',
    
    // Tools/Calculators -> ToolController
    'Front\\PageController@stampDutyCalculator' => 'Front\\ToolController@stampDutyCalculator',
    'Front\\PageController@plotSizeConverter' => 'Front\\ToolController@plotSizeConverter',
    'Front\\PageController@plotConverter' => 'Front\\ToolController@plotConverter',
    'Front\\PageController@valuationCalculator' => 'Front\\ToolController@valuationCalculator',
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
    'Front\\PageController@calc' => 'Front\\ToolController@calc',
    'Front\\PageController@reraLookup' => 'Front\\ToolController@reraLookup',
    
    // Legal -> LegalController
    'Front\\PageController@legalTermsConditions' => 'Front\\LegalController@terms',
    'Front\\PageController@legalTermsPage' => 'Front\\LegalController@terms',
    'Front\\PageController@legalServices' => 'Front\\LegalController@services',
    'Front\\PageController@legalDocuments' => 'Front\\LegalController@documents',
    'Front\\PageController@legal' => 'Front\\LegalController@index',
    'Front\\PageController@legalPrivacy' => 'Front\\LegalController@privacy',
    'Front\\PageController@privacy' => 'Front\\LegalController@privacy',
    'Front\\PageController@insurance' => 'Front\\LegalController@insurance',
    'Front\\PageController@nachMandate' => 'Front\\LegalController@nachMandate',
    'Front\\PageController@agreements' => 'Front\\LegalController@agreements',
    'Front\\PageController@titleProtection' => 'Front\\LegalController@titleProtection',
    'Front\\PageController@propertyVerification' => 'Front\\LegalController@propertyVerification',
    'Front\\PageController@howItWorks' => 'Front\\LegalController@howItWorks',
    'Front\\PageController@disclaimer' => 'Front\\LegalController@disclaimer',
    'Front\\PageController@refundPolicy' => 'Front\\LegalController@refundPolicy',
    'Front\\PageController@cancellationPolicy' => 'Front\\LegalController@cancellationPolicy',
    
    // Career -> CareerController
    'Front\\PageController@careers' => 'Front\\CareerController@careers',
    'Front\\PageController@careerApply' => 'Front\\CareerController@careerApply',
    'Front\\PageController@submitCareerApplication' => 'Front\\CareerController@submitCareerApplication',
    'Front\\PageController@careerJobs' => 'Front\\CareerController@careerJobs',
    'Front\\PageController@careerJobDetails' => 'Front\\CareerController@careerJobDetails',
    'Front\\PageController@opportunity' => 'Front\\CareerController@opportunity',
    
    // Financial -> FinancialController
    'Front\\PageController@financialServices' => 'Front\\FinancialController@financialServices',
    'Front\\PageController@financialContact' => 'Front\\FinancialController@financialContact',
    'Front\\PageController@bank' => 'Front\\FinancialController@bank',
    
    // Services -> ServiceController
    'Front\\PageController@services' => 'Front\\ServiceController@services',
    'Front\\PageController@interiorDesign' => 'Front\\ServiceController@interiorDesign',
    'Front\\PageController@constructionServices' => 'Front\\ServiceController@constructionServices',
    'Front\\PageController@constructionInquiry' => 'Front\\ServiceController@constructionInquiry',
    'Front\\PageController@documents' => 'Front\\ServiceController@documents',
    'Front\\PageController@resell' => 'Front\\ServiceController@resell',
    
    // AI -> AIController
    'Front\\PageController@aiChatbotPage' => 'Front\\AIController@aiChatbotPage',
    'Front\\PageController@aiValuation' => 'Front\\AIController@aiValuation',
    'Front\\PageController@userAiSuggestions' => 'Front\\AIController@userAiSuggestions',
    'Front\\PageController@whatsappChat' => 'Front\\AIController@whatsappChat',
    'Front\\PageController@virtualTour' => 'Front\\AIController@virtualTour',
    
    // Contact -> ContactController
    'Front\\PageController@contact' => 'Front\\ContactController@contact',
    'Front\\PageController@serviceInterest' => 'Front\\ContactController@serviceInterest',
    'Front\\PageController@handleQuickInquiry' => 'Front\\ContactController@handleQuickInquiry',
    'Front\\PageController@scheduleMeeting' => 'Front\\ContactController@scheduleMeeting',
    'Front\\PageController@handleScheduleMeeting' => 'Front\\ContactController@handleScheduleMeeting',
    'Front\\PageController@support' => 'Front\\ContactController@support',
    
    // User Dashboard -> UserDashboardController
    'Front\\PageController@userInvestments' => 'Front\\UserDashboardController@userInvestments',
    'Front\\PageController@userEditProfile' => 'Front\\UserDashboardController@userEditProfile',
    'Front\\PageController@userSavedSearches' => 'Front\\UserDashboardController@userSavedSearches',
    'Front\\PageController@userNotifications' => 'Front\\UserDashboardController@userNotifications',
    
    // Associate -> AssociateController
    'Front\\PageController@becomeAssociate' => 'Front\\AssociateController@becomeAssociate',
    'Front\\PageController@mlmDashboard' => 'Front\\AssociateController@mlmDashboard',
    
    // Plot/Plots Availability -> PropertyController
    'Front\\PageController@plotsAvailability' => 'Front\\PropertyController@plotsAvailability',
    'Front\\PageController@plot' => 'Front\\PropertyController@plot',
    
    // Gallery/News/Blog -> keep existing BlogController for blog, PageController for gallery
    // 'Front\\PageController@gallery' stays
    // 'Front\\PageController@news' stays
    // 'Front\\PageController@newsView' stays
    
    // Downloads -> PageController (keep)
    // 'Front\\PageController@downloads' stays
    
    // Document gallery -> PageController (keep)
    // 'Front\\PageController@documentGallery' stays
    // 'Front\\PageController@downloadDocument' stays
    
    // Review submit -> PageController (keep)
    // 'Front\\PageController@reviewSubmit' stays
    
    // Inquiry -> PageController (keep)
    // 'Front\\PageController@inquiry' stays
    
    // Testimonials -> TestimonialsController (keep)
    // 'Front\\TestimonialsController@index' stays
    
    // PageController@createMobileApp -> PageController (keep)
    // 'Front\\PageController@createMobileApp' stays
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