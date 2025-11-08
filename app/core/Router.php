<?php
/**
 * Router Class
 * Handles URL routing and controller dispatching
 */

namespace App\Core;

class Router {
    private $routes = [];
    private $app;

    public function __construct() {
        $this->app = app();
        $this->loadRoutes();
    }

    /**
     * Load route definitions
     */
    private function loadRoutes() {
        $this->routes = [
            // Public routes
            '' => ['controller' => 'HomeControllerSimple', 'action' => 'index'],
            'home' => ['controller' => 'HomeControllerSimple', 'action' => 'enhanced'],
            'homepage' => ['controller' => 'HomeControllerSimple', 'action' => 'enhanced'],
            'about' => ['controller' => 'PageController', 'action' => 'about'],
            'contact' => ['controller' => 'PageController', 'action' => 'contact'],
            // Advanced AI routes
            'ai/price-prediction' => ['controller' => 'AdvancedAIController', 'action' => 'pricePrediction'],
            'ai/automated-valuation' => ['controller' => 'AdvancedAIController', 'action' => 'automatedValuation'],
            'ai/smart-recommendations' => ['controller' => 'AdvancedAIController', 'action' => 'smartRecommendations'],
            'ai/market-analysis' => ['controller' => 'AdvancedAIController', 'action' => 'marketAnalysis'],
            'admin/ai/model-training' => ['controller' => 'AdvancedAIController', 'action' => 'modelTraining'],

            // Metaverse routes
            'metaverse/vr-showroom' => ['controller' => 'MetaverseController', 'action' => 'vrShowroom'],
            'metaverse/virtual-development' => ['controller' => 'MetaverseController', 'action' => 'virtualDevelopment'],
            'metaverse/collaborative-spaces' => ['controller' => 'MetaverseController', 'action' => 'collaborativeSpace'],
            'metaverse/create-space' => ['controller' => 'MetaverseController', 'action' => 'createCollaborativeSpace'],
            'metaverse/virtual-marketplace' => ['controller' => 'MetaverseController', 'action' => 'virtualMarketplace'],
            'metaverse/virtual-events' => ['controller' => 'MetaverseController', 'action' => 'virtualEvents'],
            'metaverse/nft-ownership' => ['controller' => 'MetaverseController', 'action' => 'nftOwnership'],
            'metaverse/vr-tours' => ['controller' => 'MetaverseController', 'action' => 'vrTours'],
            'metaverse/social-hub' => ['controller' => 'MetaverseController', 'action' => 'socialHub'],
            'metaverse/virtual-economy' => ['controller' => 'MetaverseController', 'action' => 'virtualEconomy'],
            'metaverse/academy' => ['controller' => 'MetaverseController', 'action' => 'metaverseAcademy'],
            'metaverse/investment-portfolio' => ['controller' => 'MetaverseController', 'action' => 'investmentPortfolio'],
            'admin/metaverse-analytics' => ['controller' => 'MetaverseController', 'action' => 'metaverseAnalytics'],
            'property' => ['controller' => 'PropertyController', 'action' => 'show'],
            'projects' => ['controller' => 'ProjectController', 'action' => 'index'],
            'company-projects' => ['controller' => 'CompanyProjectsController', 'action' => 'index'],
            'portfolio' => ['controller' => 'CompanyProjectsController', 'action' => 'index'],
            // Advanced Analytics routes
            'admin/analytics' => ['controller' => 'AdvancedAnalyticsController', 'action' => 'dashboard'],
            'admin/analytics/properties' => ['controller' => 'AdvancedAnalyticsController', 'action' => 'propertyAnalytics'],
            'admin/analytics/users' => ['controller' => 'AdvancedAnalyticsController', 'action' => 'userAnalytics'],
            'admin/analytics/financial' => ['controller' => 'AdvancedAnalyticsController', 'action' => 'financialAnalytics'],
            'admin/analytics/mlm' => ['controller' => 'AdvancedAnalyticsController', 'action' => 'mlmAnalytics'],
            'api/analytics/realtime' => ['controller' => 'AdvancedAnalyticsController', 'action' => 'apiGetRealtimeData'],
            // Quantum Computing routes
            'quantum/optimization' => ['controller' => 'QuantumComputingController', 'action' => 'propertyOptimization'],
            'quantum/portfolio' => ['controller' => 'QuantumComputingController', 'action' => 'portfolioOptimization'],
            'quantum/risk-assessment' => ['controller' => 'QuantumComputingController', 'action' => 'riskAssessment'],
            'quantum/ml' => ['controller' => 'QuantumComputingController', 'action' => 'quantumML'],
            'quantum/cryptography' => ['controller' => 'QuantumComputingController', 'action' => 'quantumCryptography'],
            'quantum/algorithms' => ['controller' => 'QuantumComputingController', 'action' => 'algorithmSimulator'],
            'quantum/error-correction' => ['controller' => 'QuantumComputingController', 'action' => 'errorCorrection'],
            'quantum/advantage' => ['controller' => 'QuantumComputingController', 'action' => 'quantumAdvantage'],
            'quantum/research' => ['controller' => 'QuantumComputingController', 'action' => 'research'],
            'quantum/ethics' => ['controller' => 'QuantumComputingController', 'action' => 'ethics'],
            'quantum/education' => ['controller' => 'QuantumComputingController', 'action' => 'education'],
            'quantum/partnerships' => ['controller' => 'QuantumComputingController', 'action' => 'partnerships'],
            'quantum/roi' => ['controller' => 'QuantumComputingController', 'action' => 'roiCalculator'],
            'quantum/roadmap' => ['controller' => 'QuantumComputingController', 'action' => 'roadmap'],
            'quantum/benchmarks' => ['controller' => 'QuantumComputingController', 'action' => 'benchmarks'],
            'quantum/costs' => ['controller' => 'QuantumComputingController', 'action' => 'costAnalysis'],
            'quantum/security' => ['controller' => 'QuantumComputingController', 'action' => 'securityImplications'],
            'quantum/environmental' => ['controller' => 'QuantumComputingController', 'action' => 'environmentalImpact'],
            'quantum/skills' => ['controller' => 'QuantumComputingController', 'action' => 'skillsDevelopment'],
            'admin/quantum/dashboard' => ['controller' => 'QuantumComputingController', 'action' => 'quantumDashboard'],
            // Edge Computing & 5G routes
            'edge/dashboard' => ['controller' => 'EdgeComputingController', 'action' => 'edgeDashboard'],
            'edge/5g' => ['controller' => 'EdgeComputingController', 'action' => 'fiveGIntegration'],
            'edge/ai' => ['controller' => 'EdgeComputingController', 'action' => 'edgeAI'],
            'edge/realtime' => ['controller' => 'EdgeComputingController', 'action' => 'realTimeProcessing'],
            'edge/distributed' => ['controller' => 'EdgeComputingController', 'action' => 'distributedNetwork'],
            'edge/mobile' => ['controller' => 'EdgeComputingController', 'action' => 'mobileEdge'],
            'edge/cdn' => ['controller' => 'EdgeComputingController', 'action' => 'contentDelivery'],
            'edge/costs' => ['controller' => 'EdgeComputingController', 'action' => 'costAnalysis'],
            'edge/security' => ['controller' => 'EdgeComputingController', 'action' => 'securityFeatures'],
            'edge/benchmarks' => ['controller' => 'EdgeComputingController', 'action' => 'performanceBenchmarks'],
            'edge/integration' => ['controller' => 'EdgeComputingController', 'action' => 'integrationGuide'],
            'edge/use-cases' => ['controller' => 'EdgeComputingController', 'action' => 'useCases'],
            'edge/roi' => ['controller' => 'EdgeComputingController', 'action' => 'roiCalculator'],
            'edge/roadmap' => ['controller' => 'EdgeComputingController', 'action' => 'roadmap'],
            'edge/partnerships' => ['controller' => 'EdgeComputingController', 'action' => 'partnerships'],
            'edge/education' => ['controller' => 'EdgeComputingController', 'action' => 'education'],
            'edge/impact' => ['controller' => 'EdgeComputingController', 'action' => 'industryImpact'],
            'edge/sustainability' => ['controller' => 'EdgeComputingController', 'action' => 'sustainability'],
            'edge/research' => ['controller' => 'EdgeComputingController', 'action' => 'research'],
            'edge/case-studies' => ['controller' => 'EdgeComputingController', 'action' => 'caseStudies'],
            'admin/edge/dashboard' => ['controller' => 'EdgeComputingController', 'action' => 'edgeDashboard'],
            // Sustainable Technology routes
            'sustainability/dashboard' => ['controller' => 'SustainableTechController', 'action' => 'sustainabilityDashboard'],
            'sustainability/carbon-footprint' => ['controller' => 'SustainableTechController', 'action' => 'carbonFootprint'],
            'sustainability/energy-efficiency' => ['controller' => 'SustainableTechController', 'action' => 'energyEfficiency'],
            'sustainability/green-technology' => ['controller' => 'SustainableTechController', 'action' => 'greenTechnology'],
            'sustainability/sustainable-properties' => ['controller' => 'SustainableTechController', 'action' => 'sustainableProperties'],
            'sustainability/environmental-impact' => ['controller' => 'SustainableTechController', 'action' => 'environmentalImpact'],
            'sustainability/green-finance' => ['controller' => 'SustainableTechController', 'action' => 'greenFinance'],
            'sustainability/education' => ['controller' => 'SustainableTechController', 'action' => 'sustainabilityEducation'],
            'sustainability/partnerships' => ['controller' => 'SustainableTechController', 'action' => 'sustainabilityPartnerships'],
            'sustainability/innovation-lab' => ['controller' => 'SustainableTechController', 'action' => 'innovationLab'],
            'sustainability/awards' => ['controller' => 'SustainableTechController', 'action' => 'awards'],
            'sustainability/calculator' => ['controller' => 'SustainableTechController', 'action' => 'sustainabilityCalculator'],
            'sustainability/roadmap' => ['controller' => 'SustainableTechController', 'action' => 'sustainabilityRoadmap'],
            'sustainability/case-studies' => ['controller' => 'SustainableTechController', 'action' => 'caseStudies'],
            'sustainability/community-engagement' => ['controller' => 'SustainableTechController', 'action' => 'communityEngagement'],
            'sustainability/governance' => ['controller' => 'SustainableTechController', 'action' => 'governance'],
            'sustainability/investment-opportunities' => ['controller' => 'SustainableTechController', 'action' => 'investmentOpportunities'],
            'sustainability/trends' => ['controller' => 'SustainableTechController', 'action' => 'trends'],
            'sustainability/resources' => ['controller' => 'SustainableTechController', 'action' => 'resources'],
            'sustainability/challenges' => ['controller' => 'SustainableTechController', 'action' => 'challenges'],
            'sustainability/success-stories' => ['controller' => 'SustainableTechController', 'action' => 'successStories'],
            'sustainability/future-vision' => ['controller' => 'SustainableTechController', 'action' => 'futureVision'],
            'api/sustainability/data' => ['controller' => 'SustainableTechController', 'action' => 'apiSustainabilityData'],

            // Advanced Security routes
            'security/dashboard' => ['controller' => 'AdvancedSecurityController', 'action' => 'securityDashboard'],
            'security/quantum-cryptography' => ['controller' => 'AdvancedSecurityController', 'action' => 'quantumCryptography'],
            'security/threat-detection' => ['controller' => 'AdvancedSecurityController', 'action' => 'threatDetection'],
            'security/zero-trust' => ['controller' => 'AdvancedSecurityController', 'action' => 'zeroTrust'],
            'security/blockchain-security' => ['controller' => 'AdvancedSecurityController', 'action' => 'blockchainSecurity'],
            'security/ai-security' => ['controller' => 'AdvancedSecurityController', 'action' => 'aiSecurity'],
            'security/mfa-enhancement' => ['controller' => 'AdvancedSecurityController', 'action' => 'mfaEnhancement'],
            'security/data-privacy' => ['controller' => 'AdvancedSecurityController', 'action' => 'dataPrivacy'],
            'security/training' => ['controller' => 'AdvancedSecurityController', 'action' => 'securityTraining'],
            'security/research' => ['controller' => 'AdvancedSecurityController', 'action' => 'securityResearch'],
            'security/partnerships' => ['controller' => 'AdvancedSecurityController', 'action' => 'securityPartnerships'],
            'security/compliance' => ['controller' => 'AdvancedSecurityController', 'action' => 'complianceAuditing'],
            'security/incident-response' => ['controller' => 'AdvancedSecurityController', 'action' => 'incidentResponse'],
            'security/benchmarks' => ['controller' => 'AdvancedSecurityController', 'action' => 'performanceBenchmarks'],
            'security/roi' => ['controller' => 'AdvancedSecurityController', 'action' => 'roiCalculator'],
            'security/roadmap' => ['controller' => 'AdvancedSecurityController', 'action' => 'securityRoadmap'],
            'security/case-studies' => ['controller' => 'AdvancedSecurityController', 'action' => 'caseStudies'],
            'security/resources' => ['controller' => 'AdvancedSecurityController', 'action' => 'resources'],
            'security/innovation' => ['controller' => 'AdvancedSecurityController', 'action' => 'innovation'],
            'security/awards' => ['controller' => 'AdvancedSecurityController', 'action' => 'awards'],
            'security/future-vision' => ['controller' => 'AdvancedSecurityController', 'action' => 'futureVision'],
            'api/security/status' => ['controller' => 'AdvancedSecurityController', 'action' => 'apiSecurityStatus'],
            'api/security/report-incident' => ['controller' => 'AdvancedSecurityController', 'action' => 'apiReportIncident'],
            'testimonials' => ['controller' => 'TestimonialController', 'action' => 'index'],
            // CRM routes
            'crm' => ['controller' => 'CRMController', 'action' => 'index'],
            'crm/dashboard' => ['controller' => 'CRMController', 'action' => 'index'],
            'crm/leads' => ['controller' => 'CRMController', 'action' => 'leads'],
            'crm/leads/create' => ['controller' => 'CRMController', 'action' => 'createLead'],
            'crm/leads/export' => ['controller' => 'CRMController', 'action' => 'exportLeads'],
            'crm/analytics' => ['controller' => 'CRMController', 'action' => 'analytics'],
            'crm/my-leads' => ['controller' => 'CRMController', 'action' => 'myLeads'],
            'faq' => ['controller' => 'FaqController', 'action' => 'index'],
            'news' => ['controller' => 'NewsController', 'action' => 'index'],
            'downloads' => ['controller' => 'DownloadController', 'action' => 'index'],

            // Service routes
            'services' => ['controller' => 'PageController', 'action' => 'services'],
            'team' => ['controller' => 'PageController', 'action' => 'team'],

            // Property related routes
            'property-management' => ['controller' => 'PropertyController', 'action' => 'manage'],
            'resell' => ['controller' => 'PropertyController', 'action' => 'resell'],
            'featured-properties' => ['controller' => 'PropertyController', 'action' => 'featured'],

            // Authentication routes
            'login' => ['controller' => 'AuthController', 'action' => 'login'],
            'register' => ['controller' => 'AuthController', 'action' => 'register'],
            'logout' => ['controller' => 'AuthController', 'action' => 'logout'],

            // Favorites routes
            'favorites' => ['controller' => 'PropertyFavoriteController', 'action' => 'index'],
            'favorites/toggle' => ['controller' => 'PropertyFavoriteController', 'action' => 'toggle'],
            'favorites/remove' => ['controller' => 'PropertyFavoriteController', 'action' => 'remove'],

            // Inquiry routes
            'inquiry/submit' => ['controller' => 'PropertyInquiryController', 'action' => 'submit'],

            // Dashboard routes
            'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
            'customer-dashboard' => ['controller' => 'DashboardController', 'action' => 'customer'],
            'associate' => ['controller' => 'DashboardController', 'action' => 'associate'],
            'associate-dashboard' => ['controller' => 'DashboardController', 'action' => 'associate'],
            'associate/profile' => ['controller' => 'AssociateController', 'action' => 'profile'],
            'associate/leads' => ['controller' => 'AssociateController', 'action' => 'leads'],
            'associate/customers' => ['controller' => 'AssociateController', 'action' => 'customers'],
            'associate/rank' => ['controller' => 'AssociateMLMController', 'action' => 'rank'],

            // Admin routes
            'admin' => ['controller' => 'AdminController', 'action' => 'index'],
            'admin/dashboard' => ['controller' => 'AdminController', 'action' => 'index'],
            'admin/properties' => ['controller' => 'AdminController', 'action' => 'properties'],
            'admin/properties/create' => ['controller' => 'AdminController', 'action' => 'createProperty'],
            'admin/properties/store' => ['controller' => 'AdminController', 'action' => 'storeProperty'],
            'admin/users' => ['controller' => 'AdminController', 'action' => 'users'],
            'admin/users/create' => ['controller' => 'AdminController', 'action' => 'createUser'],
            'admin/users/store' => ['controller' => 'AdminController', 'action' => 'storeUser'],
            'admin/users/edit' => ['controller' => 'AdminController', 'action' => 'editUser'],
            'admin/users/update' => ['controller' => 'AdminController', 'action' => 'updateUser'],
            'admin/users/delete' => ['controller' => 'AdminController', 'action' => 'deleteUser'],
            'admin/users/bulk-activate' => ['controller' => 'AdminController', 'action' => 'bulkActivate'],
            'admin/users/bulk-deactivate' => ['controller' => 'AdminController', 'action' => 'bulkDeactivate'],
            'admin/users/bulk-delete' => ['controller' => 'AdminController', 'action' => 'bulkDelete'],
            'admin/settings' => ['controller' => 'AdminController', 'action' => 'settings'],
            'admin/settings/save' => ['controller' => 'AdminController', 'action' => 'saveSettings'],
            'admin/inquiries' => ['controller' => 'PropertyInquiryController', 'action' => 'adminIndex'],
            'admin/inquiries/view' => ['controller' => 'PropertyInquiryController', 'action' => 'view'],
            'admin/inquiries/update-status' => ['controller' => 'PropertyInquiryController', 'action' => 'updateStatus'],
            'admin/reports' => ['controller' => 'AdminReportsController', 'action' => 'index'],
            'admin/reports/properties' => ['controller' => 'AdminReportsController', 'action' => 'properties'],
            'admin/reports/users' => ['controller' => 'AdminReportsController', 'action' => 'users'],
            'admin/reports/financial' => ['controller' => 'AdminReportsController', 'action' => 'financial'],
            'admin/reports/inquiries' => ['controller' => 'AdminReportsController', 'action' => 'inquiries'],
            'admin/reports/export' => ['controller' => 'AdminReportsController', 'action' => 'export'],

            // Mobile API routes
            'api/properties' => ['controller' => 'MobileApiController', 'action' => 'properties'],
            'api/property' => ['controller' => 'MobileApiController', 'action' => 'property'],
            'api/inquiry/submit' => ['controller' => 'MobileApiController', 'action' => 'submitInquiry'],
            'api/favorites/toggle' => ['controller' => 'MobileApiController', 'action' => 'toggleFavorite'],
            'api/favorites' => ['controller' => 'MobileApiController', 'action' => 'userFavorites'],
            'api/property-types' => ['controller' => 'MobileApiController', 'action' => 'propertyTypes'],
            'api/cities' => ['controller' => 'MobileApiController', 'action' => 'cities'],

            // Payment routes
            'payment' => ['controller' => 'PaymentController', 'action' => 'index'],
            'payment/process' => ['controller' => 'PaymentController', 'action' => 'process'],
            'payment/verify' => ['controller' => 'PaymentController', 'action' => 'verify'],
            'payment/success' => ['controller' => 'PaymentController', 'action' => 'success'],
            'payment/failed' => ['controller' => 'PaymentController', 'action' => 'failed'],

            // Support routes
            'support' => ['controller' => 'SupportController', 'action' => 'index'],

            // Utility pages
            'coming-soon' => ['controller' => 'PageController', 'action' => 'comingSoon'],
            'maintenance' => ['controller' => 'PageController', 'action' => 'maintenance'],
            'thank-you' => ['controller' => 'PageController', 'action' => 'thankYou'],
            'privacy-policy' => ['controller' => 'PageController', 'action' => 'privacyPolicy'],
            'sitemap' => ['controller' => 'PageController', 'action' => 'sitemap'],
            // MLM routes
            'associate/mlm' => ['controller' => 'AssociateMLMController', 'action' => 'dashboard'],
            'associate/genealogy' => ['controller' => 'AssociateMLMController', 'action' => 'genealogy'],
            'associate/downline' => ['controller' => 'AssociateMLMController', 'action' => 'downline'],

            // Associate Plot Selling routes
            'associate/plot-inventory' => ['controller' => 'AssociatePlotSellingController', 'action' => 'plotInventory'],
            'associate/commission-calculator' => ['controller' => 'AssociatePlotSellingController', 'action' => 'commissionCalculator'],
            'associate/sales-analytics' => ['controller' => 'AssociatePlotSellingController', 'action' => 'salesAnalytics'],
            'associate/referrals' => ['controller' => 'AssociatePlotSellingController', 'action' => 'customerReferrals'],
            'associate/plot-booking' => ['controller' => 'AssociatePlotSellingController', 'action' => 'plotBooking'],

            // Admin MLM routes
            'admin/mlm' => ['controller' => 'AssociateMLMController', 'action' => 'adminDashboard'],
            'admin/mlm/associate' => ['controller' => 'AssociateMLMController', 'action' => 'adminAssociateDetails'],

            // Chatbot routes
            'chatbot' => ['controller' => 'AIChatbotController', 'action' => 'index'],
            'api/chatbot/message' => ['controller' => 'AIChatbotController', 'action' => 'sendMessage'],
            'api/chatbot/history' => ['controller' => 'AIChatbotController', 'action' => 'getHistory'],
            'api/chatbot/stats' => ['controller' => 'AIChatbotController', 'action' => 'getStats'],

            // API Monitoring routes
            'api/monitor/status' => ['controller' => 'MonitorController', 'action' => 'status'],
            'api/monitor/health' => ['controller' => 'MonitorController', 'action' => 'health'],
            'api/monitor/performance' => ['controller' => 'MonitorController', 'action' => 'performance'],
            'api/monitor/errors' => ['controller' => 'MonitorController', 'action' => 'errors'],

            // API Backup routes
            'api/backup/list' => ['controller' => 'BackupApiController', 'action' => 'list'],
            'api/backup/create' => ['controller' => 'BackupApiController', 'action' => 'create'],
            'api/backup/delete' => ['controller' => 'BackupApiController', 'action' => 'delete'],
            'api/backup/stats' => ['controller' => 'BackupApiController', 'action' => 'stats'],

            // Monitoring dashboard (web interface)
            'monitor' => ['controller' => 'PageController', 'action' => 'monitor'],
        ];
    }

    /**
     * Dispatch route to controller and action
     */
    public function dispatch($route) {
        // Handle error routes
        if (isset($_GET['error'])) {
            $this->handleError($_GET['error']);
            return;
        }

        // Check for dynamic routes first (routes with parameters)
        $routeConfig = $this->findDynamicRoute($route);

        if (!$routeConfig) {
            // Find matching route
            $routeConfig = $this->routes[$route] ?? null;

            if (!$routeConfig) {
                // Try to find matching file
                $routeConfig = $this->findMatchingFile($route);
            }
        }

        if (!$routeConfig) {
            throw new \Exception('Route not found: ' . $route);
        }

        // Dispatch to controller
        $this->callController($routeConfig);
    }

    /**
     * Find dynamic route with parameters
     */
    private function findDynamicRoute($route) {
        $routeParts = explode('/', trim($route, '/'));
        $routeCount = count($routeParts);

        // Handle admin routes with parameters
        if ($routeCount >= 2 && $routeParts[0] === 'admin') {
            $action = $routeParts[1];

            switch ($action) {
                case 'users':
                    if ($routeCount === 2) {
                        return ['controller' => 'AdminController', 'action' => 'users'];
                    } elseif ($routeCount === 3) {
                        $subAction = $routeParts[2];
                        if ($subAction === 'create') {
                            return ['controller' => 'AdminController', 'action' => 'createUser'];
                        }
                    } elseif ($routeCount === 4) {
                        $subAction = $routeParts[2];
                        $param = $routeParts[3];

                        switch ($subAction) {
                            case 'edit':
                                return [
                                    'controller' => 'AdminController',
                                    'action' => 'editUser',
                                    'params' => ['id' => $param]
                                ];
                            case 'update':
                                return [
                                    'controller' => 'AdminController',
                                    'action' => 'updateUser',
                                    'params' => ['id' => $param]
                                ];
                            case 'delete':
                                return [
                                    'controller' => 'AdminController',
                                    'action' => 'deleteUser',
                                    'params' => ['id' => $param]
                                ];
                        }
                    }
                    break;

                case 'properties':
                    if ($routeCount === 2) {
                        return ['controller' => 'AdminController', 'action' => 'properties'];
                    } elseif ($routeCount === 3 && $routeParts[2] === 'create') {
                        return ['controller' => 'AdminController', 'action' => 'createProperty'];
                    }
                    break;
            }
        }

        return null;
    }

    /**
     * Find matching file for route
     */
    private function findMatchingFile($route) {
        $possibleFiles = [
            $route . '.php',
            strtolower($route) . '.php',
            str_replace('-', '_', $route) . '.php'
        ];

        foreach ($possibleFiles as $file) {
            $filePath = __DIR__ . '/../views/pages/' . $file;
            if (file_exists($filePath)) {
                return [
                    'controller' => 'PageController',
                    'action' => 'render',
                    'file' => $file
                ];
            }
        }

        return null;
    }

    /**
     * Call controller method
     */
    private function callController($routeConfig) {
        $controller = $routeConfig['controller'];
        $action = $routeConfig['action'];
        $params = $routeConfig['params'] ?? [];

        $controllerClass = 'App\\Controllers\\' . $controller;
        $method = $action;

        // Check if controller exists
        if (!class_exists($controllerClass)) {
            throw new \Exception('Controller not found: ' . $controllerClass);
        }

        // Instantiate controller
        $controllerInstance = new $controllerClass();

        // Check if method exists
        if (!method_exists($controllerInstance, $method)) {
            throw new \Exception('Action not found: ' . $method . ' in ' . $controllerClass);
        }

        // Call the action with parameters if needed
        if (!empty($params)) {
            call_user_func([$controllerInstance, $method], ...array_values($params));
        } elseif (isset($routeConfig['file'])) {
            call_user_func([$controllerInstance, $method], $routeConfig['file']);
        } else {
            call_user_func([$controllerInstance, $method]);
        }
    }

    /**
     * Handle error pages
     */
    private function handleError($errorCode) {
        header("HTTP/1.0 $errorCode");

        $errorView = __DIR__ . '/../views/layouts/error.php';
        if (file_exists($errorView)) {
            require_once $errorView;
        } else {
            // Default error page
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>' . $errorCode . ' - Error</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="bg-light">
                <div class="container py-5">
                    <div class="row justify-content-center">
                        <div class="col-md-8 text-center">
                            <h1 class="display-1 text-danger">' . $errorCode . '</h1>
                            <h2>Error</h2>
                            <p class="lead">Something went wrong. Please try again later.</p>
                            <a href="' . BASE_URL . '" class="btn btn-primary">Go to Homepage</a>
                        </div>
                    </div>
                </div>
            </body>
            </html>';
        }
        exit();
    }
}

?>
