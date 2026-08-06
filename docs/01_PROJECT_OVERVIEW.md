# 1. PROJECT OVERVIEW

## What is APS Dream Home?

APS Dream Home is a **comprehensive Real Estate ERP/CRM SaaS Platform** designed for land developers, real estate agents, and property associates. It manages the entire lifecycle from land acquisition → colony development → plot sales → customer management → commission distribution.

## Core Business Model

```
Land Acquisition → Colony Development → Plot Cutting → Pricing → Sales Ready
         ↓                                                           ↓
    Farmer Relations                                          Customer Booking
         ↓                                                        ↓
    Legal Documentation                                       EMI & Payments
         ↓                                                        ↓
    RERA Compliance                                           MLM Commission
                                                             ↓
                                                    Associate/Agent Network
```

## User Roles (8 Types)

| Role | Purpose | Dashboard |
|------|---------|-----------|
| **Super Admin** | Platform owner, manages all tenants | `/admin/erp` |
| **Admin** | Company admin, manages employees & operations | `/admin/dashboard` |
| **Manager** | Department head (Sales, Finance, HR) | `/admin/dashboard` |
| **Employee** | Backoffice operations | `/employee/dashboard` |
| **Associate** | MLM network member, earns commissions | `/associate/dashboard` |
| **Agent** | Sales agent with clients | `/agent/dashboard` |
| **Customer** | Property buyer | `/user/dashboard` |
| **Farmer** | Land seller | `/farmer/dashboard` |

## Key Business Entities

| Entity | Count | Key Tables |
|--------|-------|------------|
| Colonies | 4 active | `colonies`, `plots`, `plot_bookings` |
| Plots | 204 with dimensions | `plots`, `plot_categories`, `plot_costs` |
| Associates | 56 active | `associates`, `mlm_network_tree`, `mlm_profiles` |
| MLM Network | 15 active nodes | `mlm_network_tree`, `network_tree` |
| Commission | 311 entries, ₹1.05Cr+ | `mlm_commission_ledger` |
| Customers | 1000+ | `users`, `bookings`, `user_properties` |
| Employees | 50+ | `employees`, `employee_attendance` |
| Leads | 400+ | `leads`, `lead_activities`, `lead_deals` |
