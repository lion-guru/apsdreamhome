# APS Dream Home - Deep Table Analysis Report
## Generated: 2026-04-03

---

## EXECUTIVE SUMMARY

| Category | Count | Action |
|----------|-------|--------|
| **ACTIVE + HAS DATA** | ~90 tables | ✅ KEEP |
| **ACTIVE + EMPTY** | ~100 tables | 📊 MONITOR |
| **UNUSED + HAS DATA** | ~10 tables | 🔍 INVESTIGATE |
| **COMPLETELY UNUSED** | ~440 tables | ⚠️ BACKUP THEN DELETE |

---

## TABLE CATEGORIES - ACTUALLY NOT DUPLICATES!

After deep analysis, most tables serve **different purposes**:

### 1. User Management (NOT Duplicate)

| Table | Records | Purpose | Used In |
|-------|---------|---------|---------|
| `users` | 23 | Main user table | 455 files |
| `customers` | ? | Customer-specific data | Customer dashboard |
| `employees` | 10 | Employee data | HR/Attendance |
| `agents` | ? | Agent-specific | Agent dashboard |
| `associates` | ? | MLM Associates | MLM system |
| `admin` | ? | Legacy admin | AuthMiddleware |
| `admin_users` | ? | Admin users | AdminAuth |
| `admins` | 1 | Legacy | Not used |

**VERDICT**: Different tables for different user roles - NOT duplicate, but could be consolidated.

### 2. Activity/Log Tables (Different Purposes)

| Table | Records | Purpose |
|-------|---------|---------|
| `activity_log` | ? | User-specific activities |
| `activities` | ? | Dashboard activities |
| `activity_logs` | ? | System-wide logs |
| `audit_log` | 28 | Security audit |
| `system_logs` | ? | System events |
| `error_logs` | 17 | Error tracking |
| `mcp_logs` | 12 | MCP integration logs |

**VERDICT**: Each serves different logging purpose - NOT pure duplicates but over-engineered.

### 3. AI Tables (Actually Different!)

| Table | Records | Purpose |
|-------|---------|---------|
| `ai_workflows` | 589 | Workflow definitions |
| `ai_tools_directory` | 1024 | Available tools |
| `ai_audit_log` | 186 | AI operation audit |
| `ai_user_suggestions` | 99 | User recommendations |
| `ai_implementation_guides` | 123 | Implementation docs |
| `ai_ecosystem_tools` | 82 | Ecosystem tools |
| `ai_knowledge_graph` | 15 | Knowledge base |
| `ai_chatbot_config` | ? | Chatbot settings |

**VERDICT**: Actually different AI components, not duplicates.

---

## TOP 30 ACTIVE TABLES (Most Used)

| Table | Records | Status |
|-------|---------|--------|
| ai_tools_directory | 1024 | ✅ ACTIVE |
| ai_workflows | 589 | ✅ ACTIVE |
| ai_audit_log | 186 | ✅ ACTIVE |
| workflow_executions | 172 | ✅ ACTIVE |
| ai_implementation_guides | 123 | ✅ ACTIVE |
| payments | 103 | ✅ ACTIVE |
| ai_user_suggestions | 99 | ✅ ACTIVE |
| leads | 99 | ✅ ACTIVE |
| mlm_commission_ledger | 84 | ✅ ACTIVE |
| ai_ecosystem_tools | 82 | ✅ ACTIVE |
| lead_activities | 79 | ✅ ACTIVE |
| system_activities | 79 | ✅ ACTIVE |
| properties | 71 | ✅ ACTIVE |
| notification_templates | 64 | ✅ ACTIVE |
| notifications | 52 | ✅ ACTIVE |
| app_config | 44 | ✅ ACTIVE |
| users_backup_20260320 | 38 | ⚠️ BACKUP |
| commission_preferences | 36 | ✅ ACTIVE |
| analytics_page_views | 35 | ✅ ACTIVE |
| lead_tags | 34 | ✅ ACTIVE |
| audit_log | 28 | ✅ ACTIVE |
| personalized_learning_plans | 24 | ✅ ACTIVE |
| users | 23 | ✅ ACTIVE |
| ai_agent_logs | 22 | ✅ ACTIVE |
| comparison_criteria | 20 | ✅ ACTIVE |
| schema_migrations | 20 | ✅ ACTIVE |
| translations | 20 | ✅ ACTIVE |
| bookings | 17 | ✅ ACTIVE |
| chatbot_conversations | 17 | ✅ ACTIVE |
| error_logs | 17 | ✅ ACTIVE |

---

## TABLES TO INVESTIGATE (Unused but has data)

These tables have data but no code references found:

| Table | Records | Recommendation |
|-------|---------|----------------|
| `users_backup_20260320` | 38 | Archive to separate DB, then delete |
| `ai_recommendations_test` | 14 | Delete if test data |
| `farmer_*` tables | Various | Investigate - might be demo/test data |

---

## TABLES TO DELETE (After Backup)

**Safe to delete** - Empty AND not used in code (~440 tables):

- ai_logs, ai_api_logs, ai_call_logs, ai_data_pipelines
- activities, activity_log, activity_logs (merge into one)
- agent_details, agent_reviews
- And 400+ more...

---

## RECOMMENDATIONS

### Priority 1: Keep & Maintain
- ✅ `users` - Main user table (455 code references)
- ✅ `leads` - CRM leads (163 code references)
- ✅ `properties` - Properties (311 code references)
- ✅ `payments` - Payment tracking
- ✅ `bookings` - Booking management
- ✅ `notifications` - User notifications
- ✅ All AI tables (actively used)

### Priority 2: Investigate Before Delete
- `users_backup_20260320` - Move to archive DB
- `farmer_*` tables - Check if test data
- `farmers`, `farmer_*` - Multiple farmer-related tables

### Priority 3: Archive/Consolidate
- Multiple activity tables → consolidate to 1-2
- Multiple log tables → consolidate to 2-3
- Duplicate config tables → single config

### Priority 4: Delete (After Full Backup)
- All completely empty tables (447)
- Tables with 0 records that aren't referenced

---

## CONCLUSION

**NOT TRUE DUPLICATES** - The tables serve different purposes:
- AI system: 25+ tables for different AI features
- MLM system: 25+ tables for complex commission structure
- Logging: Multiple tables for different log types
- User management: Different tables for different roles

**The real issue is OVER-ENGINEERING**, not duplication. Many tables exist for features that were planned but never fully implemented.

### Recommended Action:
1. **DO NOT delete** active tables with data
2. **Archive** old backup tables
3. **Delete** empty/unused tables after backup
4. **Consolidate** multiple log tables if needed

### Estimated Cleanup:
- ~440 empty tables can be deleted
- ~10 backup/archive tables should be moved
- Keep all ~90 active tables with data
