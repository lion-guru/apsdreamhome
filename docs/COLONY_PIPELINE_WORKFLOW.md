# Colony Development Pipeline — Complete Workflow Documentation

## Overview

The APS Dream Home Colony Development Pipeline manages the full lifecycle of turning raw land into a registered, developed, and saleable colony. It follows **7 sequential stages** with legal compliance checks at every transition.

**Business Context:** Company buys land → plans colony with phases/blocks/amenities → cuts plots legally → RERA registers → develops → prices → sells.

---

## Pipeline Stages (Sequential — no skipping)

```
LAND ACQUISITION → MASTER PLANNING → PLOT CUTTING → RERA REGISTRATION → DEVELOPMENT → PRICING → SALES READY
```

---

## Stage 1: Land Acquisition (भूमि अधिग्रहण)

### Purpose

Identify, negotiate, and legally acquire land for colony development.

### Key Activities

| #   | Activity                     | Details                                         |
| --- | ---------------------------- | ----------------------------------------------- |
| 1   | **Land Lead Identification** | Find land parcels via agents, owners, brokers   |
| 2   | **Site Inspection**          | Physical visit, measurement, soil test          |
| 3   | **Title Verification**       | Check encumbrances, previous owners, disputes   |
| 4   | **Rate Negotiation**         | Per sqft/per acre negotiation with land owner   |
| 5   | **Agreement Drafting**       | Sale agreement with terms, advance, timeline    |
| 6   | **Advance Payment**          | Token/advance payment to land owner             |
| 7   | **Registration**             | Sub-Registrar office — sale deed registration   |
| 8   | **Mutation**                 | Revenue records — transfer land to company name |

### Roles & Responsibilities

| Role                | Responsibility                           |
| ------------------- | ---------------------------------------- |
| **Land Manager**    | Finding deals, negotiations, site visits |
| **Legal Advisor**   | Title verification, agreement drafting   |
| **Finance Manager** | Payment scheduling, fund allocation      |
| **Director/CEO**    | Final approval for acquisition           |
| **Accountant**      | Payment processing, documentation        |

### Database Records

- **`colonies`** table: Create placeholder with `pipeline_stage = 'land_acquisition'`
- **`land_deals`** table: Record deal details (area, consideration, advance, balance)
- **`land_acquisitions`** (VIEW): Read-only view over land_deals

### Quality Gates Before Advancing

- [ ] Land deal status = `registered` or `mutated` or `closed`
- [ ] Title verification report positive
- [ ] Sale deed registered at Sub-Registrar
- [ ] Advance payment recorded
- [ ] No legal encumbrances found

### Permissions Required

| Action                | Who Can Do          |
| --------------------- | ------------------- |
| Create land deal      | Land Manager, Admin |
| Update deal status    | Land Manager, Admin |
| Approve acquisition   | Director/CEO        |
| Process payment       | Finance Manager     |
| Advance to next stage | Admin, Director     |

---

## Stage 2: Master Planning (मास्टर प्लानिंग)

### Purpose

Design the colony layout — phases, blocks, roads, parks, amenities, and infrastructure.

### Key Activities

| #   | Activity                   | Details                                       |
| --- | -------------------------- | --------------------------------------------- |
| 1   | **Land Survey**            | Topographical survey, boundary demarcation    |
| 2   | **Colony Phasing**         | Divide into Phase 1, Phase 2, etc.            |
| 3   | **Block Planning**         | Create blocks (A, B, C...) with plot groups   |
| 4   | **Road Network**           | Internal roads, connecting roads, entry/exit  |
| 5   | **Park Allocation**        | Green spaces, playgrounds, gardens            |
| 6   | **Amenity Planning**       | Club, temple, water tank, electricity         |
| 7   | **Infrastructure**         | Water, sewerage, drainage, electricity layout |
| 8   | **Layout Approval**        | Colony layout approval from local authority   |
| 9   | **Open Space Calculation** | Must be ≥30% (RERA requirement)               |

### Roles & Responsibilities

| Role                    | Responsibility                          |
| ----------------------- | --------------------------------------- |
| **Town Planner**        | Colony layout design, RERA compliance   |
| **Civil Engineer**      | Road widths, infrastructure planning    |
| **Landscape Architect** | Parks, green belts, amenities           |
| **Legal Team**          | Layout approval, authority coordination |
| **Admin/Director**      | Layout approval                         |

### Database Records

- **`colony_layouts`** table: Store layout with road_area_pct, common_area_pct, residential_pct
- Update **`colonies`** table with total_area_acres, total_area_sqft

### Quality Gates Before Advancing

- [ ] Master plan created and saved in DB
- [ ] Open space (road + common) ≥ 30% (RERA minimum)
- [ ] Colony type defined (residential/commercial/mixed)
- [ ] Phases and blocks defined
- [ ] Layout approved by authority (if applicable)

### RERA Compliance

- **Minimum 30% open space** — UP RERA mandatory
- **Road width ≥ 20 feet** — Internal colony roads
- **Depth ratio ≤ 3:1** — Plot depth cannot exceed 3× width

---

## Stage 3: Plot Cutting (प्लॉट कटिंग)

### Purpose

Divide the master plan into individual saleable plots with precise measurements.

### Key Activities

| #   | Activity                       | Details                                  |
| --- | ------------------------------ | ---------------------------------------- |
| 1   | **Plot Cutting Algorithm**     | Generate plots from layout parameters    |
| 2   | **Dimension Calculation**      | Width, length, area for each plot        |
| 3   | **Corner Plot Identification** | Mark corner plots (premium pricing)      |
| 4   | **Park Facing Marking**        | Mark park-facing plots (premium pricing) |
| 5   | **Road Width Tagging**         | Tag each plot with adjacent road width   |
| 6   | **Setback Compliance**         | Verify RERA setbacks from roads          |
| 7   | **Plot Numbering**             | Sequential numbering within blocks       |
| 8   | **Plot Verification**          | Cross-check total area vs land area      |

### Roles & Responsibilities

| Role               | Responsibility                           |
| ------------------ | ---------------------------------------- |
| **Town Planner**   | Plot cutting parameters, RERA compliance |
| **Civil Engineer** | Measurement verification                 |
| **Survey Team**    | Ground verification of plot boundaries   |
| **Legal Team**     | Compliance check                         |
| **Admin**          | Final approval of plot layout            |

### Database Records

- **`plots`** table: Individual plot records (colony_id, block, plot_number, area_sqft, width_ft, length_ft, facing, corner_plot, park_facing, road_width_ft)
- **`plot_categories`** table: Plot size categories

### Key Plot Dimensions

| Parameter         | Formula/Source                  |
| ----------------- | ------------------------------- |
| `area_sqft`       | Calculated by PlotCutterService |
| `width_ft`        | From layout parameters          |
| `length_ft`       | From layout parameters          |
| `dimension_label` | "30×40" format                  |
| `corner_plot`     | Boolean — corner of block       |
| `park_facing`     | Boolean — faces park            |
| `facing`          | North/South/East/West           |
| `road_width_ft`   | Width of adjacent road          |

### Quality Gates Before Advancing

- [ ] Plots generated in DB (count > 0)
- [ ] Total plot area ≤ land area (no over-allocation)
- [ ] All plots have valid dimensions
- [ ] Setback compliance verified
- [ ] Corner/park-facing plots marked

---

## Stage 4: RERA Registration (RERA पंजीकरण)

### Purpose

Register the colony project with UP RERA (Real Estate Regulatory Authority) — legally mandatory before selling.

### Key Activities

| #   | Activity                 | Details                                          |
| --- | ------------------------ | ------------------------------------------------ |
| 1   | **RERA Application**     | Prepare and submit RERA registration application |
| 2   | **Document Preparation** | Title deed, layout plan, approved drawings       |
| 3   | **Escrow Account**       | Open RERA escrow account for buyer funds         |
| 4   | **Milestone Planning**   | Define development milestones with deadlines     |
| 5   | **RERA Number**          | Obtain RERA registration number                  |
| 6   | **Website Listing**      | Publish project on RERA website                  |
| 7   | **Quarterly Compliance** | Submit quarterly progress reports                |

### Roles & Responsibilities

| Role                   | Responsibility                      |
| ---------------------- | ----------------------------------- |
| **Legal Manager**      | Application preparation, compliance |
| **Compliance Officer** | RERA filing, quarterly reports      |
| **Finance Manager**    | Escrow account management           |
| **Director**           | Application approval, signing       |
| **Marketing**          | Website listing, buyer information  |

### Database Records

- **`rera_projects`** table: RERA registration details (rera_number, status, registration_date, expiry_date)
- **`rera_milestones`** table: Development milestones with planned/actual dates
- Update **`colonies`** table: rera_project_id, rera_number

### Key RERA Milestones (Example)

| Milestone           | Planned   | Actual |
| ------------------- | --------- | ------ |
| Foundation Complete | 6 months  | -      |
| 50% Structure       | 12 months | -      |
| 75% Structure       | 18 months | -      |
| 100% Structure      | 24 months | -      |
| Possession          | 30 months | -      |

### Quality Gates Before Advancing

- [ ] RERA number obtained
- [ ] RERA status = `Registered`
- [ ] Escrow account opened
- [ ] Milestones defined in DB
- [ ] Website listing published

### Legal Requirements (UP RERA)

- **Registration mandatory** before selling any plot
- **70% buyer funds** in escrow account
- **Quarterly progress reports** to RERA
- **Interest on delays** — 2% above SBI lending rate
- **Refund on non-delivery** — Full refund + interest

---

## Stage 5: Development (विकास)

### Purpose

Execute physical construction — roads, drainage, water, electricity, compound wall, amenities.

### Key Activities

| #   | Activity               | Details                                  |
| --- | ---------------------- | ---------------------------------------- |
| 1   | **Vendor Selection**   | Tender process for development work      |
| 2   | **Road Construction**  | Internal roads, connecting roads         |
| 3   | **Drainage System**    | Storm water drainage, sewerage           |
| 4   | **Water Supply**       | Underground pipes, overhead tank         |
| 5   | **Electricity**        | Transformers, street lights, connections |
| 6   | **Compound Wall**      | Perimeter boundary wall                  |
| 7   | **Gate & Security**    | Main gate, guard room                    |
| 8   | **Landscaping**        | Parks, trees, gardens                    |
| 9   | **Progress Tracking**  | Weekly/monthly progress monitoring       |
| 10  | **Quality Inspection** | Quality checks at each milestone         |

### Roles & Responsibilities

| Role                   | Responsibility                         |
| ---------------------- | -------------------------------------- |
| **Project Manager**    | Overall development oversight          |
| **Site Engineer**      | Day-to-day construction supervision    |
| **Procurement**        | Vendor selection, material procurement |
| **Finance Manager**    | Cost tracking, payment approvals       |
| **Quality Inspector**  | Quality checks, compliance             |
| **Compliance Officer** | RERA milestone reporting               |

### Database Records

- **`colony_development_costs`** table: All costs (road, electricity, water, sewerage, etc.)
- **`colony_development_vendors`** table: Vendor details and payments
- **`rera_milestones`** table: Update actual dates as milestones complete
- Update **`colonies`** table: status, development progress

### Cost Categories

| Category           | Description                         |
| ------------------ | ----------------------------------- |
| `land_acquisition` | Purchase cost + registration        |
| `road`             | Road construction, paving           |
| `electricity`      | Transformers, wiring, street lights |
| `water`            | Supply pipes, overhead tank         |
| `sewerage`         | Sewage treatment, pipes             |
| `street_light`     | Street light poles + fixtures       |
| `drainage`         | Storm water drainage                |
| `compound_wall`    | Boundary wall construction          |
| `gate`             | Main gate, guard room               |
| `security`         | Security infrastructure             |
| `landscaping`      | Parks, gardens, trees               |
| `approval_fee`     | Authority approval fees             |
| `legal`            | Legal charges, documentation        |
| `brokerage`        | Broker commissions                  |
| `marketing`        | Marketing & advertising             |
| `office_setup`     | Site office expenses                |
| `staff`            | Staff salaries, wages               |
| `other`            | Miscellaneous expenses              |

### Quality Gates Before Advancing

- [ ] At least road + drainage + water infrastructure complete
- [ ] Development costs recorded in DB
- [ ] Vendor payments tracked
- [ ] RERA milestones updated with progress
- [ ] Quality inspection passed

---

## Stage 6: Pricing (मूल्य निर्धारण)

### Purpose

Calculate plot prices based on cost + market rates + legal minimums.

### Key Activities

| #   | Activity                               | Details                                       |
| --- | -------------------------------------- | --------------------------------------------- |
| 1   | **Cost Compilation**                   | Total land + development + overhead costs     |
| 2   | **Cost Per Sqft**                      | Calculate per-sqft cost                       |
| 3   | **Legal Minimum Rate**                 | Ensure price ≥ RERA registered rate           |
| 4   | **Market Comparison**                  | Compare with similar colonies in area         |
| 5   | **Premium Adjustments**                | Corner plot, park facing, road width premiums |
| 6   | **Slab-wise Pricing**                  | Different rates for different plot sizes      |
| 7   | **PLC (Preferential Location Charge)** | Location-based premium                        |
| 8   | **Final Price List**                   | Approved price list with all plots            |
| 9   | **GST & TDS**                          | Calculate applicable taxes                    |

### Roles & Responsibilities

| Role                | Responsibility                       |
| ------------------- | ------------------------------------ |
| **Finance Manager** | Cost compilation, margin calculation |
| **Sales Manager**   | Market rates, competitor analysis    |
| **Legal Team**      | RERA rate compliance                 |
| **Director**        | Final price approval                 |
| **Marketing**       | Price positioning strategy           |

### Database Records

- **`plots`** table: Update total_price, price_per_sqft, plc_amount for each plot
- **`colony_development_costs`** table: Read total costs
- Update **`colonies`** table: starting_price

### Pricing Formula

```
Base Price = (Land Cost + Development Cost + Overhead) / Total Plot Area
Final Price = Base Price × (1 + Premium%) + PLC
Minimum Price = RERA Registered Rate (floor)
```

### Premium Adjustments

| Factor            | Premium     |
| ----------------- | ----------- |
| Corner Plot       | +5% to +10% |
| Park Facing       | +3% to +7%  |
| Wide Road (40ft+) | +2% to +5%  |
| East/North Facing | +2% to +5%  |

### Quality Gates Before Advancing

- [ ] All plots have pricing (total_price > 0)
- [ ] Starting price set on colony
- [ ] Prices ≥ RERA minimum rate
- [ ] Price list approved by Director
- [ ] GST/TDS calculations verified

---

## Stage 7: Sales Ready (बिक्री के लिए तैयार)

### Purpose

Final readiness check — activate inventory, launch marketing, start selling.

### Key Activities

| #   | Activity                 | Details                                    |
| --- | ------------------------ | ------------------------------------------ |
| 1   | **Readiness Checklist**  | Final checklist verification               |
| 2   | **Inventory Activation** | Mark all plots as `available`              |
| 3   | **Marketing Launch**     | Website listing, ads, brochure             |
| 4   | **Sales Team Briefing**  | Train team on pricing, USPs                |
| 5   | **Booking System**       | Enable online/offline booking              |
| 6   | **Payment Plans**        | Define EMI, down payment options           |
| 7   | **Legal Documents**      | Sale agreement, allotment letter templates |
| 8   | **Possession Timeline**  | Define expected possession date            |

### Roles & Responsibilities

| Role              | Responsibility                 |
| ----------------- | ------------------------------ |
| **Sales Manager** | Sales readiness, team briefing |
| **Marketing**     | Campaign launch, collateral    |
| **Legal Team**    | Document templates             |
| **Finance**       | Payment plan setup             |
| **Admin**         | System activation              |
| **Director**      | Final go/no-go decision        |

### Database Records

- **`plots`** table: Update status to `available`
- Update **`colonies`** status to `active`, set available_plots count
- **`bookings`** table: Ready for new entries

### Quality Gates (Final)

- [ ] All previous 6 stages completed
- [ ] Pricing applied to all plots
- [ ] RERA registration active
- [ ] Development progress sufficient (roads + basic infrastructure)
- [ ] Marketing materials ready
- [ ] Sales team trained
- [ ] Legal documents prepared
- [ ] Director sign-off

---

## Department Workflow Summary

| Stage                | Lead Department          | Supporting Departments        |
| -------------------- | ------------------------ | ----------------------------- |
| 1. Land Acquisition  | **Land Department**      | Legal, Finance, Director      |
| 2. Master Planning   | **Planning/Engineering** | Legal, Admin                  |
| 3. Plot Cutting      | **Planning/Engineering** | Legal, Survey                 |
| 4. RERA Registration | **Legal/Compliance**     | Finance, Admin                |
| 5. Development       | **Project/Construction** | Procurement, Finance, Quality |
| 6. Pricing           | **Finance**              | Sales, Legal, Director        |
| 7. Sales Ready       | **Sales/Marketing**      | Legal, Finance, Admin         |

---

## Approval Matrix

| Action                  | Approver                   | Escalation     |
| ----------------------- | -------------------------- | -------------- |
| Land acquisition start  | Land Manager               | Director       |
| Land deal closure       | Director                   | -              |
| Master plan approval    | Director                   | -              |
| Plot cutting approval   | Town Planner + Director    | -              |
| RERA application        | Legal Manager              | Director       |
| Development cost > ₹10L | Finance Manager            | Director       |
| Final pricing approval  | Director                   | -              |
| Sales launch            | Director                   | -              |
| Stage advance (auto)    | System (if all gates pass) | Admin override |
| Stage advance (manual)  | Admin/Director             | -              |

---

## Technical Architecture

### Services

| Service                           | File                                                  | Lines | Purpose                         |
| --------------------------------- | ----------------------------------------------------- | ----- | ------------------------------- |
| **LegalColonyDevelopmentService** | `app/Services/Land/LegalColonyDevelopmentService.php` | 1031  | Master pipeline orchestrator    |
| **PipelineWorkflowService**       | `app/Services/Land/PipelineWorkflowService.php`       | 358   | Auto-advance + readiness checks |
| **PlotCutterService**             | `app/Services/Land/PlotCutterService.php`             | 797   | Plot generation algorithm       |
| **ColonyPricingService**          | `app/Services/Land/ColonyPricingService.php`          | 1068  | Price calculation engine        |
| **ColonyAnalyticsService**        | `app/Services/Land/ColonyAnalyticsService.php`        | 279   | Revenue/cost/profit analytics   |
| **ColonyFeasibilityService**      | `app/Services/Land/ColonyFeasibilityService.php`      | 536   | Pre-development feasibility     |
| **ColonyHealthService**           | `app/Services/Land/ColonyHealthService.php`           | 593   | Health scoring + alerts         |
| **LandAcquisitionService**        | `app/Services/Land/LandAcquisitionService.php`        | 754   | Land deal lifecycle             |

### Controllers

| Controller                        | File                                                           | Methods     |
| --------------------------------- | -------------------------------------------------------------- | ----------- |
| **LegalColonyPipelineController** | `app/Http/Controllers/Admin/LegalColonyPipelineController.php` | 30+ methods |
| **ColonyPipelineController**      | `app/Http/Controllers/Admin/ColonyPipelineController.php`      | 16 methods  |

### Views

All views at `app/views/admin/legal-colony-pipeline/`:

- `index.php` — Pipeline listing with health scores, auto-advance
- `detail.php` — Colony detail with feasibility + health badge
- `health_overview.php` — Health dashboard with alerts
- `analytics_comparison.php` — Colony comparison with charts
- `pricing_form.php` — Pricing configuration
- `development_form.php` — Development cost entry
- `acquisition_form.php` — Land acquisition form
- `master_plan_form.php` — Master planning form
- `plot_cutting_form.php` — Plot cutting configuration
- `rera_form.php` — RERA registration form
- `readiness.php` — Launch readiness checklist
- `analytics.php` — Revenue analytics
- `milestones.php` — RERA milestone tracking

### Routes

```
/admin/colony-pipeline                    — Pipeline listing
/admin/colony-pipeline/{id}               — Colony detail
/admin/colony-pipeline/{id}/health        — Health overview
/admin/colony-pipeline/{id}/analytics     — Analytics comparison
/admin/colony-pipeline/{id}/map           — Interactive Leaflet map
/admin/colony-pipeline/auto-advance       — Auto-advance all colonies
/admin/colony-pipeline/health-alerts      — Colonies below threshold
/api/colonies/{id}/health                 — Mobile API (health data)
/api/colonies/health/all                  — Mobile API (all health)
```

---

## Key Business Rules

1. **Sequential progression** — Cannot skip stages
2. **30% minimum open space** — UP RERA requirement
3. **20ft minimum road width** — Internal roads
4. **Plot depth ≤ 3× width** — RERA compliance
5. **RERA registration mandatory** — Before any sales
6. **70% funds in escrow** — RERA requirement
7. **Plan snapshots on every commission** — Immutable historical record
8. **Auto-advance when all gates pass** — But admin can override
9. **Health scoring below 50% = alert** — Auto-escalate to management
10. **All costs tracked by category** — For audit and tax purposes

---

_Document Version: 1.0 | Last Updated: 2026-07-14 | Created for: APS Dream Home Colony Development Pipeline_
