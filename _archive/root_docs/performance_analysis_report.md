# APS Dream Home - Performance Analysis Report

Generated: 2026-05-17 15:23:36

## Executive Summary

- Total Controllers Analyzed: 179
- Database Issues Found: 99
- Controller Performance Issues: 34
- Complex Views: 225

## Database Query Issues

- **app/Http/Controllers/AI/AIWebController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/AI/AssistantController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/AI/ChatbotAPIController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/AISettingsController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/AccountingController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/AdminDashboardController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/AdminLoyaltyController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/AdminProfileController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/AdminSchedulerController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/AgentDashboardController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/AiController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/ApiKeyController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/BookingController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/BuilderDashboardController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/CEODashboardController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/CFODashboardController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/CampaignController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/CareerController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/CommissionController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/CustomerController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/DealController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/EMIController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/EmailSettingsController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/EngagementController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/GalleryController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/GodModeController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/InquiryController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/JobsAdminController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/LandController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/LeadController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/LeadScoringController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/LocationAdminController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/MediaController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/NetworkController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/NewsController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/NewsletterAdminController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/PaymentController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/PayoutController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/PlotController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/PlotCostController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/PlotManagementController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/PlotsAdminController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/ProjectController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/ProjectsAdminController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/PropertyController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/PropertyImageController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/PropertyManagementController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/SalesController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/ServiceController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/SiteController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/SiteSettingsController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/SupportTicketController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/TaskController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/UserController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/UserPropertyController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Admin/VisitController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Agent/MainController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Api/BankingController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Api/BaseApiController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Api/GeminiChatbotController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Api/KYCController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Api/MobileApiController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Api/PaymentGatewayController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Api/ReferralController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Auth/AdminAuthController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Auth/AgentAuthController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Auth/AssociateAuthController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Auth/AuthenticationController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Auth/CustomerAuthController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Auth/GoogleAuthController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Auth/QuickAuthController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Auth/UnifiedAuthController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Backup/BackupIntegrityController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Employee/CAController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Employee/EmployeeAuthController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Employee/EmployeeController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Employee/EmployeeDashboardController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Employee/HRManagerController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Employee/LandManagerController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Employee/LegalAdvisorController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Employee/TelecallingController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Front/PageController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Front/PageController.php**: ORDER BY RAND()
  Warning: Avoid RAND() for large tables, use alternative approaches

- **app/Http/Controllers/Front/UserController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/MLM/MLMDashboardController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Property/CompareController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Property/PropertyWorkflowController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Tech/BlockchainController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Tech/IoTController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Tech/MetaverseController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Tech/PWAController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Tech/SocialMediaController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Tech/VirtualTourController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/User/FarmerController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/User/feedback_tickets.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Utility/AIChatbotController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Utility/AdvancedAIController.php**: SELECT *
  Warning: Avoid SELECT *, specify columns instead

- **app/Http/Controllers/Utility/LanguageController.php**: Potential N+1 Query
  Warning: Review loops that might contain database queries

- **app/Http/Controllers/Utility/SystemDiagnosticController.php**: Potential N+1 Query
  Warning: Review loops that might contain database queries


## Controller Performance Issues

- **app/Http/Controllers/AI/AssistantController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/AI/ChatbotAPIController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Admin/AISettingsController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Admin/AccountingController.php**: Complex method: index() (67 lines) [low]
  Recommendation: Consider refactoring into smaller methods

- **app/Http/Controllers/Admin/AgentDashboardController.php**: Complex method: index() (68 lines) [low]
  Recommendation: Consider refactoring into smaller methods

- **app/Http/Controllers/Admin/CFODashboardController.php**: Complex method: index() (67 lines) [low]
  Recommendation: Consider refactoring into smaller methods

- **app/Http/Controllers/Admin/EngagementController.php**: Complex method: index() (64 lines) [low]
  Recommendation: Consider refactoring into smaller methods

- **app/Http/Controllers/Agent/AgentDashboardController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Api/ApiEnquiryController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Api/ApiLeadController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Api/CommunicationController.php**: Potential large dataset loading without pagination [medium]
  Recommendation: Add pagination or use chunk()

- **app/Http/Controllers/Api/GeminiApiController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Api/GeminiChatbotController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Api/MobileApiController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Api/MonitorApiController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Api/NotificationController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Api/PaymentGatewayController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Api/TestApiController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Async/AsyncController.php**: Blocking sleep calls in controller [high]
  Recommendation: Remove sleep calls or move to background jobs

- **app/Http/Controllers/Employee/WorkDistributionController.php**: Complex method: rebalanceWorkloads() (67 lines) [low]
  Recommendation: Consider refactoring into smaller methods

- **app/Http/Controllers/Front/AIBotController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Front/PageController.php**: Complex method: testimonials() (55 lines) [low]
  Recommendation: Consider refactoring into smaller methods

- **app/Http/Controllers/Payroll/SalaryController.php**: Potential large dataset loading without pagination [medium]
  Recommendation: Add pagination or use chunk()

- **app/Http/Controllers/Tech/AdvancedSecurityController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Tech/BlockchainController.php**: Blocking sleep calls in controller [high]
  Recommendation: Remove sleep calls or move to background jobs

- **app/Http/Controllers/Tech/EdgeComputingController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Tech/IoTController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Tech/MetaverseController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Tech/PWAController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Tech/PWAController.php**: Complex method: manifest() (117 lines) [low]
  Recommendation: Consider refactoring into smaller methods

- **app/Http/Controllers/Tech/SustainableTechController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Tech/VirtualTourController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Utility/AIChatbotController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching

- **app/Http/Controllers/Utility/AdvancedAIController.php**: Synchronous file operations in controller [high]
  Recommendation: Use asynchronous operations or caching


## View Complexity Issues

- **app/views/admin/ai-training.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/ai-training.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/admin/api_key_management.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/dashboard.php**: Multiple inline styles found
  Count: 10
  Recommendation: Move styles to CSS files

- **app/views/admin/dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/admin/dashboard_content.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/dashboard_content.php**: Multiple inline styles found
  Count: 12
  Recommendation: Move styles to CSS files

- **app/views/admin/dashboard_standalone.php**: Multiple inline styles found
  Count: 10
  Recommendation: Move styles to CSS files

- **app/views/admin/layout_manager.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/meta_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/meta_dashboard.php**: Multiple inline styles found
  Count: 8
  Recommendation: Move styles to CSS files

- **app/views/admin/meta_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/admin/properties.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/reports.php**: Multiple inline styles found
  Count: 6
  Recommendation: Move styles to CSS files

- **app/views/admin/super_meta_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/super_meta_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/admin/users.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/users.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/admin/whatsapp_integration.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/admin/whatsapp_integration.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/ai/property-valuation.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/associate/dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/admin_login.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/agent_login.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/agent_register.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/associate_login.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/associate_register.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/customer_login.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/customer_login.php**: Multiple inline styles found
  Count: 6
  Recommendation: Move styles to CSS files

- **app/views/auth/forgot_password.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/google_role_selection.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/quick-register.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/quick-register.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/auth/reset_password.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/auth/universal_login.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/commission/commission-opportunity.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/commission/commission_calculator.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/commission/commission_plan_calculator.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/commission/commission_plan_calculator.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/commission/commission_plan_manager.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/commission/commission_plan_manager.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/components/chatbot_widget.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/components/mobile-header.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/components/mobile-table.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/components/mobile-table.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/components/quick_register_modal.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/components/smart_chatbot.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/customers/dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/customers/dashboard.php**: Multiple inline styles found
  Count: 16
  Recommendation: Move styles to CSS files

- **app/views/customers/dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/customers/emi_calculator.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/achievements.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/agent_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/ai-dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/analytics-dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/associate.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/associate_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/builder_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/builder_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/dashboard/ceo_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/cm_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/cm_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/dashboard/commission_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/commission_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/dashboard/cron.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/dashboard/customer.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/hybrid_commission_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/hybrid_commission_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/dashboard/index.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/index.php**: Multiple inline styles found
  Count: 7
  Recommendation: Move styles to CSS files

- **app/views/dashboard/index.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/dashboard/investor_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/investor_dashboard.php**: Multiple inline styles found
  Count: 9
  Recommendation: Move styles to CSS files

- **app/views/dashboard/investor_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/dashboard/management_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/management_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/dashboard/mlm-dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/tenant_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/tenant_dashboard.php**: Multiple inline styles found
  Count: 9
  Recommendation: Move styles to CSS files

- **app/views/dashboard/tenant_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/dashboard/user_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/dashboard/user_dashboard.php**: Multiple inline styles found
  Count: 10
  Recommendation: Move styles to CSS files

- **app/views/dashboard/user_dashboard.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/employees/activities.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/activities.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/employees/attendance.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/attendance.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/employees/dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/documents.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/documents.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/employees/leaves.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/login.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/performance.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/performance.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/employees/profile.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/profile.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/employees/reporting_structure.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/reporting_structure.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/employees/salary_history.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/tasks.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/employees/tasks.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/farmers/list.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/farmers/search.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/gallery/project.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/gallery/project.php**: Multiple inline styles found
  Count: 6
  Recommendation: Move styles to CSS files

- **app/views/gallery/project.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/home/faq.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/layouts/admin.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/layouts/admin_footer.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/layouts/associate.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/layouts/base.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/layouts/chat_widget.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/layouts/customer.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/layouts/header.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/layouts/header.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/leads/create.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/leads/edit.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/leads/show.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/leads/show.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/locations/gorakhpur-bohisawagar.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/locations/gorakhpur-bohisawagar.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/locations/kushinagar-budha-city.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/mlm/genealogy.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/ai-valuation.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/ai_chat_enhanced.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/ai_chat_enhanced.php**: Multiple inline styles found
  Count: 6
  Recommendation: Move styles to CSS files

- **app/views/pages/associate_list_property.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/bank.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/become_associate.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/blog.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/careers.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/careers.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/colonies.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/coming_soon.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/company_projects.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/create_mobile_app.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/create_mobile_app.php**: Multiple inline styles found
  Count: 15
  Recommendation: Move styles to CSS files

- **app/views/pages/create_mobile_app.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/downloads.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/email_system.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/email_system.php**: Multiple inline styles found
  Count: 16
  Recommendation: Move styles to CSS files

- **app/views/pages/email_system.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/error.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/faqs.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/financial_services.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/gallery.php**: Multiple inline styles found
  Count: 7
  Recommendation: Move styles to CSS files

- **app/views/pages/home.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/home.php**: Multiple inline styles found
  Count: 14
  Recommendation: Move styles to CSS files

- **app/views/pages/index.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/index.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/interior_design.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/list_property.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/list_property.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/mlm_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/mlm_dashboard.php**: Multiple inline styles found
  Count: 34
  Recommendation: Move styles to CSS files

- **app/views/pages/navigation.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/news.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/plot.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/plot.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/project_detail.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/project_detail.php**: Multiple inline styles found
  Count: 8
  Recommendation: Move styles to CSS files

- **app/views/pages/project_detail.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/projects.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/projects_by_location.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/properties.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/resell.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/senior-developer-full-dashboard-v2.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/senior-developer-full-dashboard-v2.php**: Multiple inline styles found
  Count: 8
  Recommendation: Move styles to CSS files

- **app/views/pages/senior-developer-unified.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/senior-developer-unified.php**: Multiple inline styles found
  Count: 7
  Recommendation: Move styles to CSS files

- **app/views/pages/senior-developer-unified.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/services.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/services.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/suyoday_colony.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/team-management.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/team.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/testimonials.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/thank_you.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/under_construction.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/user_bank_details.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/user_bank_details.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/pages/user_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/user_network.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/pages/user_network.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/partials/ai_chat_widget.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/partials/ai_chat_widget.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/payment/failed.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/payment/success.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/projects/detail.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/properties/add_ai_aggregator_columns.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/properties/compare.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/properties/compare_results.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/properties/detail.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/properties/featured.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/properties/property-listings.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/properties/property-listings.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/properties/property_detail.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/properties/property_detail.php**: Multiple inline styles found
  Count: 9
  Recommendation: Move styles to CSS files

- **app/views/properties/single.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/properties/single.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/team/dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/team/genealogy.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/templates/ai-features-demo.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/tools/ai-assistant.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/tools/ai-valuation.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/tools/development_cost_calculator.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/tools/development_cost_calculator.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/user/favorites.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/user/network_dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/user/network_dashboard.php**: Multiple inline styles found
  Count: 6
  Recommendation: Move styles to CSS files

- **app/views/user/saved-properties.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/wallet/analytics.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/wallet/analytics.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/wallet/bank_accounts.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/wallet/bank_accounts.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/wallet/dashboard.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/wallet/referral_network.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/wallet/referral_network.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/wallet/transactions.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/wallet/transactions.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/wallet/transfer_emi.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/wallet/transfer_emi.php**: Database queries detected in view
  Recommendation: Move database queries to controllers

- **app/views/wallet/withdrawal.php**: Large view file
  Recommendation: Consider breaking into partials

- **app/views/wallet/withdrawal.php**: Database queries detected in view
  Recommendation: Move database queries to controllers


## Prioritized Recommendations

### [high] Database: Optimize database queries
**Details:** Address 99 query issues found

### [high] Backend: Fix high-severity performance issues
**Details:** 25 critical issues in controllers

### [medium] Frontend: Optimize view complexity
**Details:** Simplify 225 complex views

### [medium] Caching: Implement caching layer
**Details:** Add caching for frequently accessed data

