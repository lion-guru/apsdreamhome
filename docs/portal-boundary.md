# Portal Boundary Rule (DECIDED — Option B implemented)

## The decision (final)
**`/user/*` is canonical for everything customer-facing.**
Every `/customer/*` URL 301-redirects to its `/user/*` equivalent
(see routes/web.php "Customer Portal Redirects" blocks). There are NO
controller routes under `/customer/*` anymore — only redirect closures.

History: Option C (keep both) was decided first, but the parallel tab
had already implemented Option B (redirect architecture + migrated all
Antigravity modules to `/user/*` handlers). Re-litigating would churn
tested code — Option B stands.

## Why this is consistent
- `users` table remains the single identity root (registration, sessions,
  `users.role` — unchanged by this decision).
- The Customer* controllers did NOT move — they now serve `/user/*` URLs
  (e.g. `/user/nri-desk` → `CustomerNriController@index`).
- `PortalMenuService::customerItems()` uses `/user/*` URLs only.

## For new pages (both tabs must follow)
1. Account-scoped page? → `/user/*` + `UserController`/`DashboardController`
   (or a new method there).
2. Booking-scoped page? → `/user/<feature>` + `Front\Customer*Controller`
   with `requireCustomer()` role check (`['', 'customer', 'user']`).
3. Menu entry? → `PortalMenuService` with a `/user/*` URL that has a
   registered route (the menu-links probe enforces this in CI).
4. NEVER register a `/customer/*` controller route — add a redirect closure
   only if a legacy external link needs it.
5. NEVER add a redirect whose target has no route — every redirect target
   must resolve (audit with: follow each `/customer/*` 301 to a non-404).

## Auth standard (unchanged)
- Customer-facing endpoints: `requireCustomer()` with role check.
- `customerId()`-style login-only checks are banned on new code.
