# APS DREAM HOME — COMPLETE SOFTWARE ARCHITECTURE & PROJECT PLAN

> **Document Type:** Senior Developer Workbook / SRS / Project Plan  
> **Client:** APS Dream Home (Real Estate Developer)  
> **Project:** Real Estate ERP/CRM SaaS Platform  
> **Version:** 2.0 (2026)  
> **Last Updated:** 2026-08-06  
> **Author:** Senior Software Architect  
> **Status:** Development Phase — 70% Complete  

---

## TABLE OF CONTENTS

### PART 1: COMPANY & BUSINESS ANALYSIS
1. Company Profile
2. Business Model Analysis
3. Stakeholder Analysis
4. Current Pain Points

### PART 2: SOFTWARE REQUIREMENTS
5. Functional Requirements (Complete List)
6. Non-Functional Requirements
7. User Roles & Permissions Matrix

### PART 3: SYSTEM ARCHITECTURE
8. High-Level Architecture Diagram
9. Technology Stack Justification
10. Module Interaction Diagrams

### PART 4: DATABASE DESIGN
11. Database Architecture
12. Entity-Relationship Diagram
13. Table Categories & Purposes
14. Data Flow Diagrams

### PART 5: MODULE SPECIFICATIONS
15. Module 1: Colony Development Pipeline
16. Module 2: Sales & Booking Lifecycle
17. Module 3: MLM Commission Engine
18. Module 4: Finance & Accounting
19. Module 5: CRM System
20. Module 6: HR & Employee Management
21. Module 7: Communication System
22. Module 8: AI & Automation
23. Module 9: Mobile App

### PART 6: API ARCHITECTURE
24. REST API Design
25. API Endpoint Catalog
26. Authentication & Authorization

### PART 7: UI/UX DESIGN
27. Admin Panel Design
28. Customer Portal Design
29. Mobile App Design

### PART 8: TESTING STRATEGY
30. Test Plan
31. E2E Test Cases
32. Performance Testing

### PART 9: DEPLOYMENT & DEVOPS
33. Deployment Architecture
34. CI/CD Pipeline
35. Monitoring & Logging

### PART 10: PROJECT MANAGEMENT
36. Team Structure
37. Development Timeline
38. Junior Developer Task Assignments
39. Current Status & Gap Analysis
40. Future Roadmap

---

# PART 1: COMPANY & BUSINESS ANALYSIS

## 1. Company Profile

| Attribute | Details |
|-----------|---------|
| **Company Name** | APS Dream Home |
| **Industry** | Real Estate Development |
| **Location** | Gorakhpur, Uttar Pradesh, India |
| **Business Type** | Land Development & Plot Sales |
| **Founded** | 2011 (15+ years) |
| **Team Size** | 50+ employees |
| **Active Colonies** | 4 (Suryoday, Braj Radha, Raghunath, Budh Bihar) |
| **Total Plots** | 204+ |
| **Associates** | 56 active MLM network members |

## 2. Business Model Analysis

### Value Chain

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         APS DREAM HOME VALUE CHAIN                          │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  LAND ACQUISITION → COLONY DEVELOPMENT → PLOT SALES → CUSTOMER MANAGEMENT  │
│         │                   │                  │              │             │
│         ▼                   ▼                  ▼              ▼             │
│  ┌─────────────┐   ┌──────────────┐   ┌─────────────┐   ┌─────────────┐  │
│  │ Farmer      │   │ Plot Cutting │   │ MLM Sales   │   │ EMI &       │  │
│  │ Relations   │   │ & Pricing    │   │ Commission  │   │ Payments    │  │
│  └─────────────┘   └──────────────┘   └─────────────┘   └─────────────┘  │
│         │                   │                  │              │             │
│         ▼                   ▼                  ▼              ▼             │
│  ┌─────────────┐   ┌──────────────┐   ┌─────────────┐   ┌─────────────┐  │
│  │ Legal       │   │ RERA         │   │ Associate   │   │ Registry    │  │
│  │ Documentation│  │ Compliance   │   │ Network     │   │ & NOC       │  │
│  └─────────────┘   └──────────────┘   └─────────────┘   └─────────────┘  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Revenue Streams

| Stream | Description | % of Revenue |
|--------|-------------|--------------|
| **Plot Sales** | Direct plot sales to customers | 60% |
| **MLM Commission** | Commission via associate network | 20% |
| **Investment Plans** | Investment scheme commissions | 10% |
| **Ancillary Services** | Legal, Registry, Interior Design | 10% |

### Sales Process Flow

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                           SALES PROCESS FLOW                                  │
├──────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  LEAD GENERATION → LEAD QUALIFICATION → SITE VISIT → BOOKING → PAYMENT      │
│        │                  │                │           │          │          │
│        ▼                  ▼                ▼           ▼          ▼          │
│  ┌──────────┐      ┌──────────┐     ┌──────────┐  ┌────────┐  ┌────────┐  │
│  │ Website  │      │ Scoring  │     │ Schedule │  │ Token  │  │ EMI    │  │
│  │ Campaign │      │ Hot/Warm │     │ Confirm  │  │ Amount │  │ Schedule│  │
│  │ Referral │      │ /Cold    │     │          │  │        │  │        │  │
│  └──────────┘      └──────────┘     └──────────┘  └────────┘  └────────┘  │
│        │                  │                │           │          │          │
│        └──────────────────┴────────────────┴───────────┴──────────┘          │
│                                       │                                      │
│                                       ▼                                      │
│                              ┌────────────────┐                              │
│                              │ AGREEMENT      │                              │
│                              │ REGISTRY       │                              │
│                              │ POSSESSION     │                              │
│                              └────────────────┘                              │
│                                       │                                      │
│                                       ▼                                      │
│                              ┌────────────────┐                              │
│                              │ COMMISSION     │                              │
│                              │ DISTRIBUTION   │                              │
│                              └────────────────┘                              │
│                                                                              │
└──────────────────────────────────────────────────────────────────────────────┘
```

## 3. Stakeholder Analysis

| Stakeholder | Role | System Access | Key Needs |
|-------------|------|---------------|-----------|
| **Abhaay Singh** | Founder & Director | Super Admin | Complete business oversight, all reports |
| **Praveen Prabhat** | Senior Property Advisor | Admin | Sales tracking, team management |
| **Sales Team** | 10+ members | Employee/Sales | Lead management, booking entry |
| **Finance Team** | 3-4 members | Employee/Finance | EMI tracking, accounting, TDS/GST |
| **Associates** | 56 active | Associate | Lead entry, commission tracking, network view |
| **Customers** | 1000+ | Customer | Property search, booking, EMI tracking |
| **Farmers** | 20+ | Farmer | Land sale, payment tracking |

## 4. Current Pain Points (Why Software Needed)

| Pain Point | Impact | Solution in Software |
|------------|--------|---------------------|
| Manual lead tracking | Leads lost, no follow-up | CRM with automated follow-ups |
| Manual commission calculation | Errors, disputes | HybridCommissionEngine (automated) |
| No centralized customer data | Poor customer service | Unified customer database |
| Manual EMI tracking | Missed payments, penalties | Automated EMI schedules with dunning |
| No real-time inventory | Double bookings | Live plot availability system |
| Manual accounting | Errors, compliance issues | Automated TDS/GST calculations |
| No mobile access | Field team blind | Flutter mobile app |
| Manual legal documentation | Delays, errors | AI document generation |
