# MASTER PROMPT: APS Dream Home V2 (Smart Sync & Offline Build)

Build a premium, high-performance Flutter mobile application that connects to an **existing PHP/MySQL backend** and supports **Full Offline Mode** for field work.

---

### 🚀 **PROJECT VISION**
**Target Directory**: `D:\sofware devlopment\mobile`
**API Base URL**: `http://localhost/apsdreamhome` (Existing XAMPP Project)
**Architecture**: Flutter (Mobile) + Existing MySQL DB + Modern REST API Bridge.

### 🏛️ **KEY ARCHITECTURAL REQUIREMENTS**

#### **1. Offline-First Design (Mandatory)**
- Use **`sqflite`** for local data persistence.
- Cache all Property Listings, Associate Profile, and Leads locally.
- **Sync Queue**: If a user creates a Lead or updates a Property status while offline (e.g., at a remote plot site), save it in a local queue and auto-sync when network is restored using `connectivity_plus`.

#### **2. Security & Auth**
- Integrate with existing `users` table via JWT tokens.
- Save tokens securely in `flutter_secure_storage`.

#### **3. State Management**
- Use **Riverpod** for global state and sync status tracking.

---

### 📦 **PHASE 1: DEPENDENCIES & UI FOUNDATION**
- Add: `dio`, `flutter_riverpod`, `go_router`, `sqflite`, `path`, `connectivity_plus`, `flutter_secure_storage`, `intl`, `cached_network_image`.
- Design: **Royal Blue & Golden Accent** theme. Glassmorphism cards for property previews.

### 📦 **PHASE 2: THE SYNC ENGINE**
- Create a `SyncService` that:
    1. Fetches new data from the PHP API when online.
    2. Overwrites local SQLite cache.
    3. Regularly checks the `sync_queue` table for unsent local changes.

### 📦 **PHASE 3: FEATURE MODULES**
1.  **Lead CRM**: Create leads offline. Mark leads for sync. Use WhatsApp/Call intent integration.
2.  **Property Marketplace**: Fast search in local cache. Show "Last Updated" timestamp.
3.  **Associate Dashboard**: Real-time commission analytics (when online) and team hierarchy visualization.
4.  **Admin Tools**: On-site Plot Status Toggle (Available -> Booked -> Sold) with offline lock.

---

### 🛠️ **EXECUTION STEPS FOR WINDSURF AI**

**Step 1**: Initialize Flutter project and set up the `lib/` directory with clean architecture (Clean -> Data, Domain, Presentation).
**Step 2**: Implement the SQLite Database Helper and the Sync Queue logic.
**Step 3**: Build API Repository to talk to the existing PHP project's endpoints.
**Step 4**: Design the Login screen with offline-auth bypass (if user previously logged in).
**Step 5**: Create the Property and CRM screens with proper "Syncing..." indicators.

---

### 📜 **CODING PRINCIPLES**
- **Robustness**: Handle network timeouts gracefully.
- **Performance**: Use `shimmer` for loading and `Hero` animations for transitions.
- **Identity**: App Name: APS Dream Home | Support: 7007444842.

**START NOW: Build the core Sync Engine first, then proceed to the UI.**
