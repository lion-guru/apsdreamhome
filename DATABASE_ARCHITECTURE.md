# APS Dream Home - Database Architecture

## Overview
Complete Real Estate ERP + CRM + MLM + AI Platform

## Database Statistics
- **Total Tables**: 657
- **Active Tables** (with data): ~200
- **Modules**: 15 categories

---

## Module Categories

### 1. USER_MGMT (User Management)
| Table | Purpose |
|-------|---------|
| users | **MAIN TABLE** - All users (customer, associate, agent, employee, admin) |
| user_addresses | Multiple addresses per user |
| user_bank_accounts | Bank details for payments |
| user_documents | KYC documents (Aadhar, PAN, etc) |
| user_roles | Role definitions |
| user_permissions | Permission management |

**User Types** (stored in `user_type` column):
- `customer` - Property buyer/renter
- `associate` - MLM member / referral partner  
- `agent` - Property agent
- `employee` - Company staff
- `admin` - Admin panel access
- `super_admin` - Full access

### 2. CRM_LEADS (Lead Management)
| Table | Purpose |
|-------|---------|
| leads | **MAIN TABLE** - Lead information |
| lead_activities | Activity log (calls, emails, meetings) |
| lead_assignment_history | Assignment tracking |
| lead_deals | Deals from leads |
| lead_engagement_metrics | Website behavior tracking |
| lead_files | Documents uploaded |
| lead_notes | Internal notes |
| lead_pipeline | Sales pipeline stages |
| lead_scores | AI-calculated scores |
| lead_scoring_history | Score change history |
| lead_scoring_models | ML models |
| lead_scoring_rules | Scoring criteria |
| lead_sources | Lead sources |
| lead_statuses | Status definitions |
| lead_tags | Tag definitions |
| lead_tag_mapping | Many-to-many tags |
| lead_visits | Property visits |

### 3. PROPERTY (Property Listings)
| Table | Purpose |
|-------|---------|
| properties | **MAIN TABLE** - General listings |
| property_images | Property photos |
| property_features | Feature list |
| property_types | Type definitions |
| property_categories | Category list |

### 4. PLOT_LAND (Colony Development)
| Table | Purpose |
|-------|---------|
| plots | **MAIN TABLE** - Colony plots |
| plot_master | Land records (Gata numbers) |
| colonies | Colony/project info |
| plot_categories | Plot types |
| sites | Site information |

**Plot Development Cost Tracking:**
- Land cost (Gata based)
- Development cost (per sqft)
- Amenities cost
- Total calculation

### 5. MLM_NETWORK (Multi-Level Marketing)
| Table | Purpose |
|-------|---------|
| mlm_tree | Network hierarchy |
| mlm_commissions | Commission calculations |
| mlm_commission_levels | Commission rates |
| mlm_commission_plans | Plan definitions |
| mlm_plans | MLM plans |
| mlm_profiles | MLM profile data |
| mlm_ranks | Rank definitions |
| mlm_rank_advancements | Rank progress |
| mlm_rank_rates | Rank-based rates |
| mlm_payouts | Payout records |
| mlm_performance | Performance metrics |
| mlm_rewards_recognition | Rewards system |
| mlm_salary_plans | Salary plans |
| mlm_special_bonuses | Bonus tracking |
| mlm_training_progress | Training progress |

### 6. PAYMENT (Financial)
| Table | Purpose |
|-------|---------|
| payments | Payment records |
| payment_plans | EMI plans |
| payment_settings | Payment config |
| payment_transactions | Transaction log |
| payment_notifications | Payment alerts |
| salary_contracts | Salary agreements |
| salary_history | Salary records |
| salary_payouts | Salary payments |
| salary_plans | Salary structures |
| salary_structures | Structure definitions |

### 7. AI_ML (Artificial Intelligence)
| Table | Purpose |
|-------|---------|
| ai_agents | Virtual assistants |
| ai_agent_logs | Agent activity |
| ai_agent_personality | Agent traits |
| ai_audit_log | AI action log |
| ai_bot_performance | Performance metrics |
| ai_bot_policies | Bot rules |
| ai_chat_history | Chat records |
| ai_chatbot_config | Chatbot settings |
| ai_config | Global AI config |
| ai_configuration | Detailed settings |
| ai_conversation_states | Conversation state |
| ai_ecosystem_tools | Available tools |
| ai_implementation_guides | How-to guides |
| ai_interaction_logs | Interaction log |
| ai_jobs | Background jobs |
| ai_knowledge_base | Knowledge base |
| ai_knowledge_graph | Knowledge relations |
| ai_recommendations | AI suggestions |
| ai_settings | AI settings |
| ai_tools_directory | Tool list |
| ai_user_suggestions | User-specific AI |
| ai_workflows | Workflow definitions |

### 8. ERP (Enterprise Resource Planning)
| Table | Purpose |
|-------|---------|
| projects | Project management |
| tasks | Task management |
| employees | Employee records |
| departments | Department list |
| design | Designations |
| attendance | Attendance tracking |
| invoices | Invoice management |
| documents | Document storage |

### 9. MARKETING
| Table | Purpose |
|-------|---------|
| campaigns | Marketing campaigns |
| notifications | Notification system |
| notification_templates | Message templates |
| notification_settings | Notif preferences |
| newsletter_subscribers | Email list |
| popups | Popup campaigns |

### 10. SETTINGS & CONFIG
| Table | Purpose |
|-------|---------|
| settings | Global settings |
| system_settings | System config |
| site_settings | Site config |
| site_content | CMS content |
| site_content | Page content |
| pages | Static pages |
| translations | Language strings |
| multi_language_translations | i18n data |

---

## Key Features Implemented

### Lead Scoring System
AI-powered lead qualification based on:
- Demographics (budget, location)
- Engagement (website visits, form fills)
- Behavior (time on site)
- AI Analysis (chat, property interest)

Auto-actions:
- Score > 80: Hot lead, auto-assign
- Score 50-80: Warm lead, nurture
- Score < 50: Cold lead, follow-up

### MLM System
Binary MLM with:
- Multi-level commissions
- Rank advancements
- Team building bonuses
- Payout management

### Plot Development
Full lifecycle:
1. Land purchase (Gata records)
2. Development planning
3. Plot booking
4. Payment tracking
5. Registry
6. Possession

---

## Database Design Principles

1. **Soft Deletes**: All main tables support soft delete
2. **Timestamps**: Standard created_at, updated_at
3. **Indexes**: Foreign keys and common queries indexed
4. **JSON**: Flexible metadata column where needed
5. **Unified Users**: Single users table with role-based access

---

## API Integration Ready

Database designed for:
- 99acres/Housing.com API integration
- WhatsApp Business API
- Payment gateways
- SMS/Email services
- Google OAuth

---

## Backup & Security

- `.env` contains secrets (gitignored)
- API keys stored in `api_keys` table
- KYC documents encrypted
- Audit logging enabled
