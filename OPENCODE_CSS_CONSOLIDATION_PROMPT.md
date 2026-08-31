# OPENCODE IDE MASTER PROMPT: CSS Architecture Consolidation (Phases 2-4)

> **Context:** Phase 1 (Layout De-duplication) is **DONE**. `base.php` and `header.php` now load only individual CSS files (no consolidated bundles). Proceed with Phases 2-4.

---

## 🛑 CRITICAL CONSTRAINTS (NON-NEGOTIABLE)
1. **Bootstrap 5 stays** — Do NOT replace with Tailwind or any other framework.
2. **Preserve class names** — `.ps-card`, `.erp-module-card`, `.mobile-drawer`, `.hero`, `.admin-sidebar`, `.btn-primary`, `.table`, etc. MUST work exactly as before.
3. **Keep JS widget CSS** — `live-chat-widget.css`, `notification-system.css`, `voice-widget.css`, `notification-widget.css`, `image-gallery.css`, `dark-mode.css` — untouched.
4. **E2E Tests MUST PASS** — `node testing/visual_tests/E2E_MASTER_TEST.mjs` → 153/153 after every change.

---

## 🎯 PHASE 2: Token Consolidation — Unify Design Tokens in `:root`

### Target Files
- `public/assets/css/style.css` — Primary token file (make this the **Single Source of Truth**)
- `public/assets/css/premium-theme.css` — Map legacy aliases to new canonical tokens

### Task
1. **Audit `style.css`** — Ensure `:root` defines **canonical CSS custom properties** exactly as below. If missing, add them. If different values exist, update to these canonical values:

```css
:root {
  /* Brand Colors */
  --color-primary: #0a192f;          /* Navy */
  --color-primary-hover: #172a45;
  --color-secondary: #d4af37;        /* Gold */
  --color-secondary-hover: #b5952f;
  --color-accent: #0d9488;           /* Teal */
  --color-accent-hover: #0f766e;

  /* Surface & Background */
  --color-surface: #f8fafc;          /* Page background */
  --color-surface-elevated: #ffffff; /* Card background */
  --color-overlay: rgba(15, 23, 42, 0.6);

  /* Text */
  --color-text-primary: #1e293b;
  --color-text-secondary: #475569;
  --color-text-muted: #94a3b8;
  --color-text-inverse: #ffffff;

  /* Borders */
  --color-border: #e2e8f0;
  --color-border-strong: #cbd5e1;

  /* Radius */
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  --radius-full: 9999px;

  /* Shadows */
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.12);

  /* Transitions */
  --transition-fast: 150ms ease;
  --transition-base: 250ms ease;
  --transition-slow: 350ms ease;

  /* Z-Index Scale */
  --z-dropdown: 1000;
  --z-sticky: 1020;
  --z-fixed: 1030;
  --z-modal-backdrop: 1040;
  --z-modal: 1050;
  --z-popover: 1060;
  --z-tooltip: 1070;
  --z-toast: 1080;
}
```

2. **Map legacy aliases** in BOTH `style.css` and `premium-theme.css` to the canonical tokens:
```css
/* Legacy → Canonical Mapping (add if missing) */
--primary-color: var(--color-primary);
--secondary-color: var(--color-secondary);
--accent-color: var(--color-accent);
--primary-hover: var(--color-primary-hover);
--secondary-hover: var(--color-secondary-hover);
--text-main: var(--color-text-primary);
--text-muted: var(--color-text-muted);
--bg-surface: var(--color-surface);
--card-bg: var(--color-surface-elevated);
--border-color: var(--color-border);
--radius-md: var(--radius-md);
--shadow-sm: var(--shadow-sm);
--shadow-md: var(--shadow-md);
--shadow-lg: var(--shadow-lg);
```

3. **Replace ALL hardcoded colors** in `style.css` and `premium-theme.css` with `var(--color-*)` tokens. Search for: `#0a192f`, `#d4af37`, `#0d9488`, `#1e293b`, `#64748b`, `#e2e8f0`, `rgba(0,0,0,`, etc.

---

## 🎯 PHASE 3: Admin & ERP Isolation — Prevent Cross-Portal Styling Leaks

### Target Files
- `app/views/layouts/admin.php` — Admin layout
- `public/assets/admin/css/admin.css` — Admin-specific styles
- `public/assets/admin/css/responsive-fixes.css` — Admin responsive overrides

### Task
1. **Verify Admin Layout loads ONLY Admin CSS** — In `admin.php`, ensure these are the ONLY CSS files (besides Bootstrap/FontAwesome):
   - `assets/admin/css/admin.css`
   - `assets/admin/css/responsive-fixes.css`
   - `assets/css/notification-system.css` (shared component)
   - `assets/css/dark-mode.css` (shared component)
   - `assets/css/uiux-fixes.css` (shared component)

2. **REMOVE any public theme CSS** from Admin layout if present:
   - ❌ `frontend.css`
   - ❌ `homepage.css`
   - ❌ `premium-theme.css`
   - ❌ `header.css` (admin has its own nav)
   - ❌ `mobile-responsive.css` (admin uses responsive-fixes.css)

3. **Add scoping class to Admin `<body>`** — In `admin.php`, ensure `<body>` has:
   ```html
   <body class="aps-admin-body">
   ```
   This enables clean overrides like:
   ```css
   .aps-admin-body .card { /* admin-specific card styles */ }
   .aps-admin-body .table { /* admin-specific table styles */ }
   .aps-admin-body .btn-primary { /* admin-specific button styles */ }
   ```

4. **Move admin-specific overrides** from `uiux-fixes.css` → `assets/admin/css/admin.css` where they belong.

---

## 🎯 PHASE 4: `!important` Cleanup — Remove Unnecessary Specificity Hacks

### Target Files (Audit Order)
1. `public/assets/css/uiux-fixes.css` — Primary offender
2. `public/assets/css/frontend.css`
3. `public/assets/css/style.css`

### Task
For each file, **search for `!important`** and classify each occurrence:

| Classification | Action |
|----------------|--------|
| **Font floor rules** — `font-size: max(0.75em, 11px) !important` | **KEEP** — Required for accessibility |
| **Tap targets** — `min-height: 44px !important`, `min-width: 44px !important` | **KEEP** — Required for mobile usability |
| **Bootstrap utility overrides** — `d-flex !important`, `text-center !important`, `justify-content-* !important`, `p-* !important`, `m-* !important` | **REMOVE** — Use proper specificity instead |
| **Color/background overrides** — `color: #fff !important`, `background: #xxx !important` | **REMOVE** — Use CSS variables + proper cascade |
| **Layout fixes for specific components** | **MOVE** to component-specific CSS or use higher-specificity selector without `!important` |

### Method
1. For each `!important` to remove:
   - Identify the selector
   - Increase specificity naturally (e.g., `.header .btn` → `header .navbar .btn`)
   - Or use `:where()` for lower specificity where needed
   - Test visually after each removal

---

## 🎯 PHASE 5: Verification Checklist (Run After Each Phase)

```bash
# 1. PHP Syntax Check (all modified PHP layout files)
php -l app/views/layouts/base.php
php -l app/views/layouts/header.php
php -l app/views/layouts/admin.php

# 2. Full E2E Test Suite (MUST PASS 153/153)
node testing/visual_tests/E2E_MASTER_TEST.mjs

# 3. AI Smoke Tests
php testing/smoke_all_ai.php

# 4. Workflow Probe
php testing/workflow_probe.php

# 5. Health Check
php scripts/health_check.php
```

### Visual Verification Pages (Manual Spot-Check)
| Portal | URLs to Verify |
|--------|----------------|
| **Public** | `/`, `/properties`, `/property-detail/*`, `/colonies`, `/about`, `/contact` |
| **Admin** | `/admin/erp`, `/admin/bookings`, `/admin/mlm`, `/admin/finance`, `/admin/colony-pipeline` |
| **Customer** | `/user/dashboard`, `/user/bookings`, `/user/profile` |
| **Associate** | `/associate/dashboard`, `/associate/leads`, `/associate/commissions` |
| **Mobile** | Test at 375px, 428px, 768px viewports |

---

## 📋 EXECUTION ORDER FOR OPENCODE

```
1. PHASE 2 → Edit style.css :root + Map aliases in premium-theme.css
2. PHASE 3 → Edit admin.php (verify CSS links) + Add aps-admin-body class
3. PHASE 4 → Audit uiux-fixes.css → Remove non-essential !important
4. PHASE 4 → Audit frontend.css → Remove non-essential !important
5. PHASE 4 → Audit style.css → Remove non-essential !important
6. PHASE 5 → Run ALL verification commands
```

---

## 🚫 DO NOT TOUCH
- `public/assets/css/live-chat-widget.css`
- `public/assets/css/notification-system.css`
- `public/assets/css/voice-widget.css`
- `public/assets/css/notification-widget.css`
- `public/assets/css/image-gallery.css`
- `public/assets/css/dark-mode.css`
- `public/assets/css/bootstrap.min.css`
- Any JS files

---

## 💡 TIPS FOR OPENCODE
- **Work in small batches** — One file at a time, verify after each
- **Use grep** to find all `!important` occurrences: `rg "!important" public/assets/css/`
- **Test mobile viewport** — Use Playwright's device emulation in E2E tests
- **If E2E fails** — Revert the last change immediately, analyze which selector broke
- **Commit after each phase** — So you can bisect if something breaks later