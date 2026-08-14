# APS Dream Home — Authentication System Consolidation Reference

> **Status:** Post-consolidation reference (2026-08-15)
> **Auth Goal:** Merge 12 redundant auth controllers into 4 clear controllers.

---

## 1. Controller Inventory (PRE-Consolidation)

### Active Controllers (10)

| Controller | Namespace | Methods | Primary Routes | Role Specific? |
|---|---|---|---|---|
| `LoginController` | `Auth\` | showLogin, authenticate, logout, redirectToDashboard | /login, /auth/login, /logout | Universal |
| `RegisterController` | `Auth\` | showRegister, handleRegister, redirectToDashboard | /register, /register/unified, /auth/register | Universal (customer/associate/agent) |
| `CustomerAuthController` | `Auth\` | login, authenticate, register, handleRegister, logout, rate-limiting helpers | /register/customer, /user/logout | Customer-only |
| `AdminAuthController` | `Auth\` | adminLogin, authenticateAdmin, logout | /admin/login, /admin/logout | Admin-only |
| `AgentAuthController` | `Auth\` | register, handleRegister, login, authenticate, logout | /agent/register, /agent/login, /agent/logout | Agent-only |
| `AssociateAuthController` | `Auth\` | associateRegister, handleAssociateRegister, associateLogin, authenticateAssociate, logout | /associate/register, /associate/login, /associate/logout | Associate-only |
| `FarmerAuthController` | `Auth\` | loginForm, login, logout | /farmer/login, /farmer/logout | Farmer-only |
| `OtpAuthController` | `Auth\` | showPhoneInput, sendOtp, showOtpVerification, verifyOtp, saveProfileProgress, skipProfileCompletion, resendOtp, saveRoleSelection, showAirLogin, requestAirLoginOtp, showAirLoginVerify, verifyAirLoginOtp, trackBehavior, adminDashboard, adminSessionDetail | /register/smart/*, /auth/air-login/*, /auth/smart/role, /admin/smart-registration/* | Universal (OTP-based) |
| `RegistrationWizardController` | `Auth\` | step1-4, saveStep1-4, complete, verifyOtp, resendOtp, skip | /register/step1-4, /register/verify-otp, /register/resend-otp, /register/skip, /register/complete | Universal (4-step) |
| `QuickAuthController` | `Auth\` | quickRegister, requestReferralCode, autoGenerateUser | /auth/quick-register | Universal (quick) |
| `AuthenticationController` | `Auth\` | showLogin, login, showRegister, register, logout, showForgotPassword, forgotPassword, showResetPassword, resetPassword, sendForgotOtp, verifyForgotOtp, showChangePassword, changePassword, showProfile, getAuthStatus, checkPermission, getCurrentUser | /forgot-password (POST), /reset-password | Universal |
| `AuthController` (root) | `Controllers\` | login, me, refresh, logout, forgotPassword, verifyEmail | /forgot-password (GET) | Stub/API redirect |

### Empty/Dead Controllers (3)

| Controller | Reason |
|---|---|
| `UnifiedRegisterController` | Empty class — no methods |
| `SmartRegistrationController` | Empty class — no methods |
| `CoreAuthController` | Empty class — no methods |

---

## 2. Redundancy Analysis

### `LoginController` vs `AuthenticationController` vs `CustomerAuthController`

| Feature | LoginController | AuthenticationController | CustomerAuthController |
|---|---|---|---|
| showLogin() | Uses `core_login.php` view, has test_login support | Uses `auth/login.php` view, via AuthenticationService | Redirects to `/login` — no own view |
| authenticate() | Raw SQL, test_login support, login alerts | Via AuthenticationService | Raw SQL, **rate limiting + password rehash + generic errors** |
| logout() | Simple session_destroy | Via AuthenticationService | Secure logout (clears cookies, audit log) |
| forgotPassword | **MISSING** | POST /forgot-password route | **MISSING** |
| resetPassword | **MISSING** | POST /reset-password route | **MISSING** |

**Decision:** LoginController is the active login controller (routes: /login, /auth/login, /logout). AuthenticationController only has 2 active routes (/forgot-password, /reset-password). CustomerAuthController has rate-limiting that LoginController lacks.

**Consolidation:**
1. Add `forgotPassword()`, `showResetPassword()`, `resetPassword()` to LoginController (from AuthenticationController)
2. Add rate-limiting + password rehash to LoginController (from CustomerAuthController)
3. Remove CustomerAuthController's login/authenticate (already delegated to LoginController via redirect)
4. Remove AuthenticationController's dead methods (showLogin, login, showRegister, register, logout, changePassword, showProfile)

### `RegisterController` vs `CustomerAuthController` vs `AuthenticationController`

| Feature | RegisterController | CustomerAuthController | AuthenticationController |
|---|---|---|---|
| showRegister() | core_register.php | customer_register.php | auth/register.php |
| handleRegister() | Role-based (customer/associate/agent) | Customer-only | AuthenticationService-based |
| Routes | /register, /register/unified, /auth/register | /register/customer | (no active routes) |

**Decision:** RegisterController handles /register + /register/unified + /auth/register. CustomerAuthController handles /register/customer (customer-specific form view). AuthenticationController's register is dead code.

**Consolidation:**
1. RegisterController already covers universal registration
2. CustomerAuthController's `/register/customer` route → redirect to `/register?role=customer`
3. CustomerAuthController's `/user/logout` → LoginController::logout()

### `OtpAuthController` vs `RegistrationWizardController`

OtpAuthController handles `/register/smart/*` (phone input → OTP → profile completion).
RegistrationWizardController handles `/register/step1-4` (multi-step form registration).

These are **two different flows**:
- Smart registration: phone-first OTP verification
- Wizard: form-based multi-step with role selection

**Decision:** Keep both but rename for clarity:
- OtpAuthController → keep as is (OTP is specialized)
- RegistrationWizardController → keep as is (wizard is a distinct UI pattern)

### `AuthController` (root) vs `AuthenticationController`

AuthController (root) handles GET `/forgot-password` — renders `views/auth/forgot_password.php`.
AuthenticationController handles POST `/forgot-password` and GET/POST `/reset-password`.

**Decision:** Merge AuthController's `forgotPassword()` into LoginController. Remove root AuthController's forgotPassword stub.

---

## 3. Post-Consolidation Architecture

### Final Controller Structure (5 controllers)

| Controller | Namespace | Purpose | Routes |
|---|---|---|---|
| **`AuthController`** (new, replaces LoginController + AuthenticationController + AuthController) | `Auth\` | Universal login/logout + password reset/forgot. Merged best of all 3. | /login, /auth/login, /logout, /auth/logout, /forgot-password, /reset-password, /change-password |
| **`RegisterController`** | `Auth\` | Unified registration with role selection (customer/associate/agent). | /register, /register/unified, /auth/register |
| **`OtpAuthController`** | `Auth\` | OTP-based registration + Air Login (passwordless). | /register/smart/*, /auth/air-login/*, /auth/smart/role, /admin/smart-registration/* |
| **`RegistrationWizardController`** | `Auth\` | 4-step wizard registration. | /register/step1-4, /register/verify-otp, /register/resend-otp, /register/skip |
| **`QuickAuthController`** | `Auth\` | Quick register + referral code. | /auth/quick-register |
| **`AdminAuthController`** | `Auth\` | Admin login/logout (test_login bypass). | /admin/login, /admin/logout |
| **`AgentAuthController`** | `Auth\` | Agent login/register (role-specific views + stats). | /agent/login, /agent/register, /agent/logout |
| **`AssociateAuthController`** | `Auth\` | Associate login/register (role-specific views + stats). | /associate/login, /associate/register, /associate/logout |
| **`FarmerAuthController`** | `Auth\` | Farmer login. | /farmer/login, /farmer/logout |

### Archived Controllers (4)

| Controller | Archive Location | Reason |
|---|---|---|
| `AuthenticationController` | `_archive/dead_auth/` | All features merged into AuthController |
| `LoginController` | `_archive/dead_auth/` | Replaced by AuthController |
| `CustomerAuthController` | `_archive/dead_auth/` | Login/register merged into AuthController + RegisterController |
| `AuthController` (root) | `_archive/dead_auth/` | Forgotten routes merged into AuthController |
| `UnifiedRegisterController` | `_archive/dead_auth/` | Empty — was already dead |
| `SmartRegistrationController` | `_archive/dead_auth/` | Empty — was already dead |
| `CoreAuthController` | `_archive/dead_auth/` | Empty — was already dead |

---

## 4. Route Mapping (Old → New)

### Login Routes
| Old Route | Old Controller | New Route | New Controller |
|---|---|---|---|
| GET /login | LoginController@showLogin | GET /login | **AuthController@showLogin** |
| POST /login | LoginController@authenticate | POST /login | **AuthController@authenticate** |
| GET /logout | LoginController@logout | GET /logout | **AuthController@logout** |
| GET /auth/login | LoginController@showLogin | GET /auth/login | **AuthController@showLogin** |
| POST /auth/login | LoginController@authenticate | POST /auth/login | **AuthController@authenticate** |
| GET /auth/logout | LoginController@logout | GET /auth/logout | **AuthController@logout** |
| GET /user/logout | CustomerAuthController@logout | GET /user/logout | **AuthController@logout** (alias) |

### Password Routes
| Old Route | Old Controller | New Route | New Controller |
|---|---|---|---|
| GET /forgot-password | AuthController@showForgotPassword | GET /forgot-password | **AuthController@showForgotPassword** |
| POST /forgot-password | AuthenticationController@forgotPassword | POST /forgot-password | **AuthController@forgotPassword** |
| GET /reset-password | AuthenticationController@showResetPassword | GET /reset-password | **AuthController@showResetPassword** |
| POST /reset-password | AuthenticationController@resetPassword | POST /reset-password | **AuthController@resetPassword** |
| GET /change-password | AuthenticationController@showChangePassword | GET /change-password | **AuthController@showChangePassword** |
| POST /change-password | AuthenticationController@changePassword | POST /change-password | **AuthController@changePassword** |
| GET /user/two-factor/verify | LoginController (2FA redirect) | GET /user/two-factor/verify | unchanged (2FA handled separately) |

### Registration Routes
| Old Route | Old Controller | New Route | New Controller |
|---|---|---|---|
| GET /register | RegisterController@showRegister | GET /register | **RegisterController@showRegister** (unchanged) |
| POST /register | RegisterController@handleRegister | POST /register | **RegisterController@handleRegister** (unchanged) |
| GET /register/customer | CustomerAuthController@register | GET /register?role=customer | **RegisterController@showRegister** (redirect) |
| POST /register/customer | CustomerAuthController@handleRegister | POST /register?role=customer | **RegisterController@handleRegister** (redirect) |
| GET /register/unified | RegisterController@showRegister | GET /register/unified | **RegisterController@showRegister** (alias route) |
| POST /register/unified | RegisterController@handleRegister | POST /register/unified | **RegisterController@handleRegister** (alias route) |

---

## 5. Auth Controller Method Mapping

### AuthController (NEW — merged from Login + Authentication + AuthController)

**From LoginController (kept):**
- `showLogin()` — enhanced with CustomerAuthController-style redirect check
- `authenticate()` — enhanced with rate limiting + password rehash + 2FA
- `logout()` — secure logout (clear cookies, destroy session)
- `redirectToDashboard()` — full role→dashboard map (from LoginController, enhanced)

**From AuthenticationController (kept):**
- `showForgotPassword()` — renders forgot_password form
- `forgotPassword()` — sends password reset email
- `showResetPassword()` — renders reset form with token
- `resetPassword()` — validates token + changes password
- `sendForgotOtp()` — OTP-based password reset (AJAX)
- `verifyForgotOtp()` — verifies OTP + issues reset token (AJAX)
- `showChangePassword()` — for logged-in users
- `changePassword()` — change current password with verification

**From AuthController (root — kept):**
- `forgotPassword()` (GET) — renders forgot password form → merged into showForgotPassword

**From CustomerAuthController (security features):**
- Rate limiting: `isLockedOut()`, `getLockoutRemaining()`, `getRecentAttempts()`, `logAttempt()`, `clearAttempts()`
- `getTenantSql()` helper
- Password rehash check in authenticate()
- Generic error messages (prevents email enumeration)
- `auditLog()` for login tracking

**From AuthenticationController (AJAX endpoints — kept):**
- `getAuthStatus()` — AJAX check for auth state
- `getCurrentUser()` — AJAX current user endpoint
- `showProfile()` / `profile()` — user profile page

**From AuthenticationController (archived — not routed):**
- All remaining methods that had no routes assigned

---

## 6. Key Security Patterns (from CustomerAuthController)

| Pattern | Implementation |
|---|---|
| Rate limiting | Max 5 attempts per 15min window, then lockout |
| Progressive delay | 1s, 2s, 4s, 8s, 16s between attempts |
| Password rehash | `password_needs_rehash()` for Argon2id upgrade |
| Generic errors | "Invalid email/phone or password" — no enumeration |
| CSRF skip | All auth POST endpoints skip CSRF |
| Audit logging | Login/logout logged to AuditService |
| Login notifications | Email/SMS/Push/WhatsApp via LoginNotificationService |
| Session fixation | `session_regenerate_id(true)` after login |
| Account status check | pending/rejected/deleted checked before login |
| 2FA support | Redirect to `/user/two-factor/verify` if enabled |

---

## 7. View File References (post-consolidation)

All auth views remain unchanged — the new AuthController will render the same view files:
- `views/auth/core_login.php` — main login form (used by LoginController, now AuthController)
- `views/auth/core_register.php` — main register form (used by RegisterController)
- `views/auth/forgot_password.php` — forgot password form
- `views/auth/reset_password.php` — reset password form
- `views/auth/change-password.php` — change password page
- `views/auth/customer_register.php` — customer-specific form (redirect merged)
- `views/auth/agent_login.php` — agent login (kept for AgentAuthController)
- `views/auth/agent_register.php` — agent register (kept)
- `views/auth/associate_login.php` — associate login (kept for AssociateAuthController)
- `views/auth/associate_register.php` — associate register (kept)
- `views/auth/air_login.php` — air login OTP request
- `views/auth/air_login_verify.php` — air login OTP verification

---

## 8. Implementation Checklist

- [x] Create this reference document
- [x] Fix logo fallback in NavigationHelper + site_content
- [ ] Create `Auth\AuthController.php` with merged methods
- [ ] Update `routes/web.php` — all routes point to AuthController
- [ ] Add rate limiting + password rehash to AuthController::authenticate
- [ ] Redirect `/register/customer` GET to `/register?role=customer`
- [ ] Redirect `/register/customer` POST to `/register` with role field set
- [ ] Merge `/forgot-password` GET + POST into AuthController (remove root AuthController stub)
- [ ] Archive old controllers: LoginController, AuthenticationController, CustomerAuthController, AuthController (root)
- [ ] PHP syntax check all new files
- [ ] E2E tests: 153/153
