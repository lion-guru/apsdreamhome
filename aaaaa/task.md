# Project Task List: APS Dream Home (Smart Sync)

## 🎯 High-Level Objective
Build a modern, **Offline-First** Flutter application that syncs with the existing `apsdreamhome` PHP/MySQL website. Ensure business continuity while providing mobile power for on-site field work.

---

## 📅 Roadmap & Progress

### Phase 1: Planning & Architecture (Completed)
- [x] Analyze Existing Website & DB (Scan complete)
- [x] Design "Offline-First" Strategy (Sync Queue)
- [x] Finalize MLM Plan logic templates
- [x] Design API Bridge requirements (JWT, REST)

### Phase 2: API Bridge Development (Completed)
- [x] Add JWT Auth to existing PHP project
- [x] Implement REST endpoints for Properties & Leads in `app/Http/Controllers/Api/`
- [x] Optimize `Model.php` for JSON performance
- [x] Test API responses via Postman/CURL
- [x] Implement V2 Smart Sync via `SyncService`

### Phase 3: Offline Flutter App Build (Completed)
- [x] Initialize Flutter project at `mobile/`
- [x] Implement **SQLite Cache** using `sqflite`
- [x] Build **Sync Manager** (Queue system for offline data)
- [x] Create Property Marketplace UI (Offline Search)
- [x] Build Lead Management UI (Save offline, Sync later)
- [x] Integrate MLM Dashboard & Genealogy Tree

### Phase 4: Integration & Testing (Completed)
- [x] Test Syncing data from App to Web
- [x] Verify MLM calculations on fresh sync
- [x] Performance testing on low-network sites
- [x] Generate Android .apk for field trial

### Phase 5: Advanced AI Integration (Next Up)
- [ ] Implement NLP for Voice-to-Lead (Advanced keyword extraction)
- [ ] Implement OCR for Document Scanner (Auto-fill lead details from ID proofs)
- [ ] Live Plot Availability Map (Interactive SVG/Leaflet integration)
- [ ] WhatsApp CRM Bridge (Auto-send property brochures)

---

## 🛠️ Tools & Docs
- **Implementation Plan**: [implementation_plan.md](file:///c:/Users/abhay/brain/68e4ead6-94e4-459a-835a-a19a879aa6a4/implementation_plan.md)
- **Master Prompt**: [windsurf_flutter_prompt.md](file:///c:/Users/abhay/brain/68e4ead6-94e4-459a-835a-a19a879aa6a4/windsurf_flutter_prompt.md)
- **Project Scan**: [project_scan.md](file:///c:/Users/abhay/brain/68e4ead6-94e4-459a-835a-a19a879aa6a4/comprehensive_project_scan.md)
