# APS Dream Home - User Flow Diagrams & Journey Mapping

This document outlines the primary user journeys and interaction patterns for the APS Dream Home platform, ensuring a seamless experience across all roles.

## 1. Customer Journey Flow

The customer journey focuses on property discovery, inquiry, and management.

```mermaid
graph TD
    Start((Start)) --> Home[Homepage]
    Home --> Search[Property Search]
    Home --> AI_Chat[AI Assistant/Chatbot]
    
    Search --> Results[Search Results]
    Results --> Detail[Property Detail Page]
    
    Detail --> Inquiry[Submit Inquiry]
    Detail --> VirtualTour[Virtual Tour]
    Detail --> MortgageCalc[Mortgage Calculator]
    
    Inquiry --> RegLogin{Registered?}
    RegLogin -- No --> Register[Registration/Login]
    RegLogin -- Yes --> Dashboard[Customer Dashboard]
    
    Dashboard --> SavedProps[Saved Properties]
    Dashboard --> MyInquiries[Inquiry Status]
    Dashboard --> Profile[Profile Management]
```

## 2. Agent (Associate) Journey Flow

The agent flow focuses on lead management, commission tracking, and business growth.

```mermaid
graph TD
    Login[Agent Login] --> Dashboard[Agent Dashboard]
    
    Dashboard --> LeadMgmt[Lead Management]
    Dashboard --> MLM[MLM Structure/Network]
    Dashboard --> Earnings[Commissions & Earnings]
    Dashboard --> Marketing[Marketing Tools]
    
    LeadMgmt --> AddLead[Add New Lead]
    LeadMgmt --> FollowUp[Follow-up Scheduler]
    
    MLM --> Downline[View Downline Tree]
    MLM --> TeamSales[Team Sales Performance]
    
    Earnings --> PayoutRequest[Request Payout]
    Earnings --> History[Transaction History]
```

## 3. Admin Journey Flow

The admin journey focuses on system-wide management, property moderation, and financial oversight.

```mermaid
graph TD
    AdminLogin[Admin Login] --> AdminDashboard[Admin Overview]
    
    AdminDashboard --> UserMgmt[User & Agent Management]
    AdminDashboard --> PropMgmt[Property Listings Management]
    AdminDashboard --> Financials[Financial & MLM Reports]
    AdminDashboard --> Settings[System Configuration]
    
    UserMgmt --> ApproveAgent[Approve Agent Registrations]
    UserMgmt --> RoleMgmt[Role & Permission Control]
    
    PropMgmt --> ReviewListing[Review/Approve Listings]
    PropMgmt --> CategoryMgmt[Manage Categories/Locations]
    
    Financials --> PayoutApproval[Approve Payouts]
    Financials --> MLM_Audit[MLM Commission Audit]
```

## 4. Interaction Patterns & State Transitions

### Navigation Hierarchy
- **Top Bar**: User profile, notifications, search.
- **Side Bar**: Module-specific navigation (Dashboard, MLM, Properties).
- **Breadcrumbs**: Context-aware path tracking.

### State Transitions
- **Loading**: Pulse animation on cards, skeleton screens for dashboards.
- **Empty States**: Helpful illustrations and "Add New" calls-to-action.
- **Success/Error**: Toast notifications with color-coded feedback (Success: Green, Error: Red).
- **Modals**: Smooth fade-in/slide-down transitions for forms and detail views.

## 5. Data Flow Mapping
1. **Property Submission**: Agent/Admin -> Validation -> Database -> CDN (Images) -> Live Site.
2. **Inquiry Flow**: Customer -> Lead Management System -> Agent Notification -> CRM Log.
3. **MLM Calculation**: Transaction -> Business Volume Update -> Commission Trigger -> Wallet Update.
