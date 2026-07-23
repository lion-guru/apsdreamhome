# Mobile Responsiveness Analysis Report

Generated: 2026-05-17 16:28:15

## Executive Summary

Total issues found: 150

### Critical Issues

- **app/views/dashboard/management_dashboard.php**: Missing viewport meta tag
  - Recommendation: Add <meta name="viewport" content="width=device-width, initial-scale=1.0">

- **app/views/pages/ai_assistant.php**: Missing viewport meta tag
  - Recommendation: Add <meta name="viewport" content="width=device-width, initial-scale=1.0">

- **app/views/pages/analytics.php**: Missing viewport meta tag
  - Recommendation: Add <meta name="viewport" content="width=device-width, initial-scale=1.0">

- **app/views/pages/inquiry.php**: Missing viewport meta tag
  - Recommendation: Add <meta name="viewport" content="width=device-width, initial-scale=1.0">

- **app/views/pages/mlm-dashboard.php**: Missing viewport meta tag
  - Recommendation: Add <meta name="viewport" content="width=device-width, initial-scale=1.0">

- **app/views/pages/plots.php**: Missing viewport meta tag
  - Recommendation: Add <meta name="viewport" content="width=device-width, initial-scale=1.0">

- **app/views/pages/privacy-policy.php**: Missing viewport meta tag
  - Recommendation: Add <meta name="viewport" content="width=device-width, initial-scale=1.0">

- **app/views/pages/terms.php**: Missing viewport meta tag
  - Recommendation: Add <meta name="viewport" content="width=device-width, initial-scale=1.0">

- **app/views/pages/whatsapp-templates.php**: Missing viewport meta tag
  - Recommendation: Add <meta name="viewport" content="width=device-width, initial-scale=1.0">

- **GLOBAL**: 9 HTML files missing viewport meta tag
  - Recommendation: Ensure all HTML pages have proper viewport meta tag

### High Priority Issues

- **app/views/auth/profile.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/blockchain/certificate.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/blockchain/explorer.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/blockchain/verify_property.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/customers/bookings.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/customers/dashboard.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/customers/payments.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/dashboard/agent_dashboard.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/dashboard/analytics-dashboard.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/dashboard/ceo_dashboard.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/edge/content_delivery.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/edge/cost_analysis.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/edge/industry_impact.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/edge/performance_benchmarks.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/edge/realtime_processing.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/edge/security_features.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/edge/sustainability.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/employees/dashboard.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/employees/performance.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/employees/salary_history.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/gallery/project.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/iot/market_insights.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/logging/details.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/pages/contact.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/pages/project_detail.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/pages/project_detail.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/payment/receipt.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/projects/detail.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/properties/single.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/pwa/index.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/reports/property.php**: HTML table without responsive wrapper
  - Recommendation: Wrap tables in responsive container div

- **app/views/team/dashboard.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

- **app/views/user/network_dashboard.php**: Fixed HTML width attributes
  - Recommendation: Remove width attributes or use responsive CSS

### Medium Priority Issues

- **app/views/admin/users.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/ai/smart_recommendations.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/auth/quick-register.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/commission/commission-opportunity.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/components/mobile-header.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/customers/property_views.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/customers/reviews.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/dashboard/customer.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/dashboard/investor_dashboard.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/dashboard/tenant_dashboard.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/dashboard/user_dashboard.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/employees/profile.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/errors/maintenance.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/gallery/index.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/gallery/project.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/home/portfolio.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/layouts/base.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/layouts/header.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/locations/gorakhpur-raghunath-nagri.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/locations/gorakhpur-suryoday-colony.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/locations/kushinagar-budha-city.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/locations/lucknow-ram-nagri.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/locations/varanasi-ganga-nagri.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/metaverse/collaborative_space.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/metaverse/social_hub.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/metaverse/vr_tours.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/about.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/ai-valuation.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/bank.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/blog.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/budhacity.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/colonies.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/company_projects.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/featured_properties.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/home.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/index.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/news.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/plots-availability.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/project_detail.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/projects.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/projects_by_location.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/properties.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/property_ai_chat.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/resell.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/suyoday_colony.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/team.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/user_properties.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/pages/virtual_tour.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/projects/detail.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/properties/compare.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/properties/compare_results.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/properties/detail.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/properties/featured.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/properties/index.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/properties/property-listings.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/properties/property_detail.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/properties/single.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/team/dashboard.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/testimonials/index.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/user/favorites.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/user/profile.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/user/saved-properties.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

- **app/views/virtual_tour/index.php**: Image without responsive class or styling
  - Recommendation: Add img-fluid class or max-width: 100% CSS

### Low Priority Issues

- **app/views/admin/api_key_management.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/admin/login.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/ai/property-valuation.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/associate/add_property.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/associate/create.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/associate/edit.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/commission/commission_calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/commission/commission_plan_calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/commission/commission_plan_manager.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/customers/alerts.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/customers/emi_calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/dashboard/commission_dashboard.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/edge/roi_calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/employees/profile.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/features/investment-calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/features/settings.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/iot/roi_calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/leads/create.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/leads/edit.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/logging/config.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/marketing/settings.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/metaverse/create_space.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/metaverse/virtual_development.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/pages/ai-valuation.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/pages/builder_registration.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/pages/calc.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/pages/resell.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/payment/emi_calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/payment/initiate.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/payment/payment_form.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/payments/emi_calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/payments/initiate.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/payments/refund.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/projects/detail.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/properties/property-listings.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/security/roi_calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/sustainability/calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/templates/ai-features-demo.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/tools/ai-valuation.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/tools/calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/tools/development_cost_calculator.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/virtual_tour/ar_furniture.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/wallet/transfer_emi.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

- **app/views/wallet/withdrawal.php**: Number input type (may have poor mobile support)
  - Recommendation: Consider using tel or pattern attributes for better mobile keyboards

