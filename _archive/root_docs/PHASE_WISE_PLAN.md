# APS Dream Home - Phase-Wise Rebuild Plan

## Project Overview
**Status**: Complete Rebuild (Delete Old → Build Fresh)
**Approach**: Phase-wise deep implementation
**Goal**: Production-ready app with ALL advanced features

---

## PHASE 1: Deep Analysis & Architecture (Day 1)
**Status**: 🔄 IN PROGRESS

### Tasks:
1. ✅ Analyze PHP website MLM logic
2. ✅ Read all requirement documents
3. ✅ Study database structure (597 tables)
4. ✅ Define proper commission structure
5. ✅ Create project architecture
6. ✅ Setup Firebase project properly
7. ✅ Define all user roles & permissions

### Deliverables:
- [ ] Complete system architecture document
- [ ] Firebase project setup with proper collections
- [ ] User role matrix
- [ ] Commission calculation logic (matching PHP)
- [ ] Database schema for Firestore

---

## PHASE 2: Core Infrastructure (Day 2)
**Status**: ⏳ PENDING

### Tasks:
1. Setup Flutter project structure
2. Configure Firebase (Auth, Firestore, Storage)
3. Implement offline-first architecture (Hive)
4. Setup state management (Riverpod)
5. Create base theme & components
6. Setup routing (Go Router)
7. Implement error handling & logging

### Deliverables:
- [ ] Working Flutter app skeleton
- [ ] Firebase integration
- [ ] Offline sync mechanism
- [ ] Base UI components
- [ ] Navigation system

---

## PHASE 3: Authentication & User Management (Day 3)
**Status**: ⏳ PENDING

### Tasks:
1. Multi-role authentication (Customer/Associate/Agent/Admin)
2. Phone OTP verification
3. Email authentication
4. User profile management
5. Bank details for payouts
6. KYC document upload
7. Parent-Child relationship for MLM

### Deliverables:
- [ ] Login/Register screens
- [ ] OTP verification
- [ ] Profile management
- [ ] User onboarding flow
- [ ] KYC upload

---

## PHASE 4: Colony & Plot Management (Day 4-5)
**Status**: ⏳ PENDING

### Tasks:
1. Colony list with filters
2. Colony detail with master plan
3. Interactive plot map (Grid view)
4. Plot status management (Available/Hold/Booked/Sold)
5. Plot details (Area, Facing, Price)
6. Corner/Park facing premium pricing
7. Booking workflow
8. Document upload for booking

### Deliverables:
- [ ] Colony showcase
- [ ] Plot visualization
- [ ] Booking system
- [ ] Document locker

---

## PHASE 5: MLM System - Core (Day 6-7)
**Status**: ⏳ PENDING

### Tasks:
1. 7-Level genealogy tree
2. Differential commission calculation
3. Rank-based percentages:
   - Associate: 5%
   - Sr. Associate: 7%
   - BDM: 10%
   - Sr. BDM: 12%
   - Vice President: 15%
   - President: 18%
   - Site Manager: 20%
4. Indirect commission (Level 2, 3)
5. Real-time commission tracking
6. Commission history
7. Payout requests

### Deliverables:
- [ ] Genealogy tree visualization
- [ ] Commission dashboard
- [ ] MLM calculations (matching PHP)
- [ ] Payout system

---

## PHASE 6: Lead CRM System (Day 8)
**Status**: ⏳ PENDING

### Tasks:
1. Lead capture (Online & Offline)
2. Lead status tracking
3. Follow-up scheduling
4. Today's follow-ups
5. Lead source tracking
6. Notes & comments
7. Voice-to-Lead AI

### Deliverables:
- [ ] Lead management
- [ ] Follow-up system
- [ ] Offline lead capture
- [ ] Voice input

---

## PHASE 7: Advanced Features (Day 9-10)
**Status**: ⏳ PENDING

### Tasks:
1. Document OCR scanner
2. WhatsApp CRM bridge
3. Live location tracking
4. Site visit tracking
5. AI property valuer
6. Gamified dashboard
7. Push notifications

### Deliverables:
- [ ] Document scanner
- [ ] WhatsApp integration
- [ ] Location tracking
- [ ] AI valuation

---

## PHASE 8: Admin & Backoffice (Day 11)
**Status**: ⏳ PENDING

### Tasks:
1. Admin dashboard
2. Colony management
3. Plot status management
4. Booking approvals
5. Commission approvals
6. Payout processing
7. User management
8. Reports & analytics

### Deliverables:
- [ ] Admin panel
- [ ] Management screens
- [ ] Reports

---

## PHASE 9: Testing & Polish (Day 12)
**Status**: ⏳ PENDING

### Tasks:
1. Unit testing
2. Widget testing
3. Integration testing
4. Performance optimization
5. UI/UX polish
6. Bug fixes

### Deliverables:
- [ ] Test coverage
- [ ] Performance optimized app
- [ ] Production ready build

---

## COMMISSION STRUCTURE (From PHP Backend)

| Rank | Direct % | Target (Lakhs) |
|------|----------|----------------|
| Associate | 5% | 1 Lakh |
| Sr. Associate | 7% | 3.5 Lakhs |
| BDM | 10% | 7 Lakhs |
| Sr. BDM | 12% | 12 Lakhs |
| Vice President | 15% | 20 Lakhs |
| President | 18% | 35 Lakhs |
| Site Manager | 20% | 50 Lakhs |

### Differential Commission Logic:
- Level 1 (Direct): Full percentage
- Level 2: 0.5% indirect
- Level 3: 0.3% indirect
- Higher ranks get differential when downline sells

---

## TECH STACK

- **Frontend**: Flutter 3.x
- **Backend**: Firebase (Firestore, Auth, Storage)
- **Offline**: Hive
- **State Management**: Riverpod
- **Navigation**: Go Router
- **Maps**: Google Maps Flutter
- **Payments**: Razorpay
- **Notifications**: Firebase Cloud Messaging
- **OCR**: Google ML Kit
- **Voice**: Speech-to-Text
- **WhatsApp**: WhatsApp Business API

---

## CURRENT STATUS

**Phase**: 1/9
**Progress**: 10%
**Next Action**: Complete Phase 1 - Deep Analysis

---

*Last Updated: April 12, 2026*
