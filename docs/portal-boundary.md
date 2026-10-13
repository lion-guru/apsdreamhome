# Portal Boundary Rule (DECIDED — Session 217 follow-up)

## The rule
- **`/user/*`** = **account core** — anything tied to the `users` row itself:
  profile, properties, inquiries/threads, favorites, saved searches,
  wallet, referral, insurance, investments, notifications, messages, settings.
  Served by `Front\UserController` / `DashboardController`.
- **`/customer/*`** = **booking journey** — anything tied to a `plot_bookings`
  row (customer owns it via `customer_id = $_SESSION['user_id']`):
  passbook, documents, registry timeline/appointments, solar, construction
  permits, plot transfers, grievances, NRI desk, maintenance dues,
  foreclosure, journey, possession certificate.
  Served by `Front\Customer*Controller`.

## Why not consolidate (rejected options)
- **Option A (`/customer/*` canonical) REJECTED**: 14 account pages have NO
  `/customer/*` equivalent — would require rebuilding working pages.
- **Option B (`/user/*` canonical) REJECTED**: would churn yesterday's tested
  modules for zero user benefit.
- The split already follows this boundary organically; the 404s came from
  assuming one prefix, not from the split. `PortalMenuService` mixes both
  prefixes with every URL route-verified — that is the enforcement.

## For new pages (both tabs must follow)
1. Account-scoped page? → `/user/*` + `UserController` (or new method there).
2. Booking-scoped page? → `/customer/*` + new `Customer*Controller`
   with `requireCustomer()` role check (`['', 'customer', 'user']`).
3. Menu entry? → `PortalMenuService` with the URL **only if the route
   exists** (probe it first — S217 lesson 472: menu edits without route
   evidence create 404s).
4. NEVER invent a `/customer/<noun>` URL without registering the route first.

## Auth standard (both systems)
- Customer-facing endpoints: `requireCustomer()` with role check.
- `customerId()`-style login-only checks are banned on new code
  (6 controllers were fixed in S217; do not reintroduce the pattern).
