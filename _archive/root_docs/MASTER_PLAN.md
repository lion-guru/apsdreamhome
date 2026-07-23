# APS Dream Home — Master Implementation Plan (2026-06-03 onwards)

## 🎯 Vision
Build a **complete, self-learning, production-grade real estate ERP** with:
- **Self-hosted AI** (no external API dependency for core features)
- **Multi-agent orchestration** (parallel work distribution)
- **Future-proof architecture** (extensible, modular, scalable)
- **Zero-downtime** feature additions
- **Continuous learning** from user behavior

---

## 🧠 Self-Learning AI Core (Phase 23)

**Philosophy**: Build AI that learns from OUR data, not from external APIs.

### Components

| Component | Purpose | Storage |
|-----------|---------|---------|
| **PatternLearner** | Learns user behavior patterns (what they click, search, view) | `ai_learning_data` + `user_behavior_tracking` |
| **IntentDetector** | NLP-based intent classification from Hindi/English text | `ai_intent_patterns` + `chatbot_training_data` |
| **RecommendationEngine** | Suggests properties based on user preferences | `ai_user_profiles` + `ai_recommendations` |
| **LeadScorer** | Scores leads based on engagement metrics | `ai_lead_scores` (rebuilt) |
| **AnomalyDetector** | Detects fraud, suspicious activity, price anomalies | `ai_anomalies` |
| **PricePredictor** | Predicts property prices from historical data | `ai_price_models` |
| **ChatbotBrain** | Self-learning chatbot using retrieval + patterns | `ai_conversations` + `chatbot_conversations` |
| **ForecastEngine** | Sales/revenue forecasting from time-series | `forecast_results` |

### AI Architecture (No External API)
```
User Input
   ↓
Preprocessor (tokenize, normalize Hindi/English)
   ↓
PatternLearner (checks learned patterns DB)
   ↓
ContextualMatcher (matches with user history)
   ↓
RecommendationEngine (suggests based on similar users)
   ↓
Response Generator (uses templates + learned phrases)
   ↓
Feedback Loop (rate response quality, retrain)
```

**Data flow**:
1. Every user action logged → `ai_learning_data`
2. Nightly batch trains pattern recognizer
3. Real-time inference uses learned patterns
4. User feedback improves future responses

### Why Self-Hosted?
- ✅ No API costs
- ✅ Privacy (data stays in-house)
- ✅ Customizable to domain
- ✅ Works offline
- ✅ Learns our specific user patterns
- ❌ Limited to pattern-based (vs LLM)

---

## 📋 Implementation Phases

### **Phase 23: Self-Learning AI Core** (Day 1-2)
- [ ] PatternLearner service (regex + Bayesian classifier)
- [ ] IntentDetector (Hindi/English tokenizer)
- [ ] RecommendationEngine (collaborative filtering)
- [ ] LeadScorer (rule-based + ML hybrid)
- [ ] PricePredictor (linear regression on historical data)
- [ ] ChatbotBrain (RAG-style with vector storage)
- [ ] Continuous learning pipeline (cron job)

**Tables restored**: `ai_learning_data`, `ai_user_profiles`, `ai_intent_patterns`, `ai_recommendations`, `ai_lead_scores`, `ai_anomalies`, `ai_price_models`

### **Phase 24: User-Facing Features** (Day 3)
- [ ] `incomplete_registrations` — track abandoned forms, auto-recover
- [ ] `progressive_registrations` — step-by-step registration wizard
- [ ] `customer_journeys` — lifecycle stage tracking (new→engaged→converted)
- [ ] `customer_behavior_analysis` — RFM segmentation
- [ ] `customer_favorites` (already exists) — enhance with AI recommendations
- [ ] `saved_searches` (already exists) — auto-notify on matching properties

### **Phase 25: HRM Complete** (Day 4)
- [ ] `employee_advances` — salary advance requests/approvals
- [ ] `employee_bonuses` — bonus calculation & payout
- [ ] `payroll_entries` — line items per payroll run
- [ ] `salary_contracts` — contract management
- [ ] `salary_history` — change tracking (raise, role change)
- [ ] `attendance_settings` — config per company
- [ ] `employee_tasks` — task assignment & tracking
- [ ] `department_budgets` — budget per department

### **Phase 26: Property Features** (Day 5)
- [ ] `property_valuations` — historical price tracking
- [ ] `property_ai_tags` — auto-tagging (luxury, family, investment)
- [ ] `property_analytics` — view counts, inquiry rate, conversion
- [ ] `property_maintenance` — post-sale maintenance schedule
- [ ] `property_market_data` — neighborhood market intelligence
- [ ] `resell_properties` — resale listing separate from primary
- [ ] `resell_property_images` — multiple images per resale
- [ ] `resell_commission_structure` — different rates for resales

### **Phase 27: MLM & Commission** (Day 6)
- [ ] `agent_commission_rates` — per-agent commission config
- [ ] `commission_calculation_rules` — flexible rule engine
- [ ] `hybrid_commission_records` — tier-based hybrid model
- [ ] `hybrid_commission_plans` — plan definitions
- [ ] `farmer_commissions` — farmer referral commissions
- [ ] `farmer_commission_structures` — sector-specific rates
- [ ] `mlm_rank_rates` — rank-based rate table
- [ ] `sponsor_running_no` — auto-increment sponsor counter

### **Phase 28: Notification System Complete** (Day 7)
- [ ] `notification_templates` (64 rows) — multi-channel templates
- [ ] `email_tracking` — open/click/bounce tracking
- [ ] `push_notifications` — mobile push queue
- [ ] `push_subscriptions` — device tokens
- [ ] `whatsapp_lead_shares` — share lead via WhatsApp
- [ ] `realtime_notifications` — websocket push
- [ ] `notification_settings` — per-user channel preferences
- [ ] `sms_templates` — SMS-specific templates

### **Phase 29: Document & Workflow** (Day 8)
- [ ] `document_classification` — auto-tag (KYC, Legal, Financial)
- [ ] `ocr_documents` — OCR processing queue
- [ ] `ocr_extracted_fields` — extracted data from OCR
- [ ] `ocr_templates` — field templates per doc type
- [ ] `report_executions` — track who ran what report when
- [ ] `document_reviews` — review/approval workflow
- [ ] `file_access_logs` — file access audit

### **Phase 30: Analytics & Reporting** (Day 9)
- [ ] `kpis` — company-wide KPI tracking
- [ ] `employee_kpis` — per-employee KPIs
- [ ] `daily_metrics_summary` — daily aggregated metrics
- [ ] `performance_benchmarks` — performance targets
- [ ] `forecast_results` — sales/revenue forecasts
- [ ] `market_analytics_summary` — market intelligence
- [ ] `analytics_dashboards` — saved dashboard configs
- [ ] `analytics_alerts` — threshold-based alerts

### **Phase 31: Auth & Security** (Day 10)
- [ ] `two_factor_tokens` — 2FA (TOTP/SMS)
- [ ] `password_reset_tokens` — secure reset flow
- [ ] `blocked_ips` — IP blacklist
- [ ] `failed_login_attempts` — brute force protection
- [ ] `jwt_blacklist` — JWT revocation
- [ ] `security_sessions` — active session tracking
- [ ] `two_factor_settings` — per-user 2FA config

### **Phase 32: Marketing & Finance** (Day 11)
- [ ] `campaign_deliveries` — multi-channel delivery tracking
- [ ] `budgets` + `budget_planning` — budget management
- [ ] `cash_flow_projections` — financial forecasting
- [ ] `gst_returns` — GST compliance
- [ ] `gst_settings` — GST config
- [ ] `tax_slabs` — tax bracket config
- [ ] `tax_types` — tax type master
- [ ] `budget_expenses` — budget vs actual

### **Phase 33: Multi-Agent Orchestration** (Day 12)
- [ ] `agent_tasks` — task queue for agents
- [ ] `agent_executions` — execution history
- [ ] `agent_state` — agent state persistence
- [ ] `workflow_automations` — rule-based automation
- [ ] `workflow_instances` — running workflow state
- [ ] `agent_orchestrator` — coordinates multiple agents
- [ ] Sequential workflow manager
- [ ] Real-time agent monitoring dashboard

### **Phase 34: Production Hardening** (Day 13)
- [ ] Performance optimization (caching, query optimization)
- [ ] Security audit (CSRF, XSS, SQL injection, auth)
- [ ] Backup & recovery strategy
- [ ] Monitoring & alerting (uptime, errors, performance)
- [ ] Documentation (API docs, admin guide, dev guide)
- [ ] Final E2E test (100% pass rate)
- [ ] Load testing
- [ ] Deployment script

---

## 🏗️ Multi-Agent Orchestration Strategy

**Agents** (each runs in parallel):
1. **DatabaseAgent** — schema, migrations, indexes
2. **BackendAgent** — controllers, services, APIs
3. **FrontendAgent** — views, JS, CSS
4. **AIAgent** — self-learning AI core
5. **SecurityAgent** — auth, CSRF, validation
6. **PerformanceAgent** — caching, query optimization
7. **TestAgent** — E2E, unit, integration tests
8. **DocsAgent** — documentation, API specs

**Coordination**:
- All agents share `agent_state.json`
- Each agent claims work via DB lock
- Conflicts resolved by priority queue
- Progress tracked in real-time

---

## 📊 Success Metrics

| Metric | Current | Target |
|--------|---------|--------|
| Tables | 213 | 350+ (functional completeness) |
| E2E tests | 163/164 | 250+/250+ |
| Self-learned patterns | 0 | 1000+ |
| External API calls | 0 | 0 (self-hosted) |
| Performance (avg page load) | TBD | <500ms |
| Security score | TBD | A+ |
| Documentation | Partial | Complete |
| Test coverage | ~30% | 80%+ |

---

## 🎯 Daily Milestones

| Day | Phase | Deliverable |
|-----|-------|-------------|
| 1 | 23 | Self-learning AI core working |
| 2 | 23 | Chatbot brain, lead scorer live |
| 3 | 24 | User features deployed |
| 4 | 25 | HRM complete |
| 5 | 26 | Property features complete |
| 6 | 27 | MLM complete |
| 7 | 28 | Notifications complete |
| 8 | 29 | Document/Workflow complete |
| 9 | 30 | Analytics complete |
| 10 | 31 | Security complete |
| 11 | 32 | Marketing/Finance complete |
| 12 | 33 | Multi-agent orchestration |
| 13 | 34 | Production ready |

---

## ⚠️ Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Self-hosted AI quality lower than LLM | Medium | Hybrid: pattern-based for common cases, optional LLM for complex |
| Scope creep | High | Strict phase boundaries, daily review |
| Test debt | Medium | Test-driven development, E2E after each phase |
| Performance regression | Medium | Benchmark before/after each phase |
| Data integrity | High | Always verify with real DB, restore scripts ready |

---

**This is a 2-week sprint to production-grade ERP. Let's go! 🚀**
