# 2. ARCHITECTURE

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐       │
│  │ Web App  │  │ Flutter  │  │ Mobile   │  │ PWA      │       │
│  │ (Bootstrap│  │ Mobile   │  │ Browser  │  │          │       │
│  │  + jQuery)│  │ App      │  │          │  │          │       │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘       │
│       │              │              │              │             │
└───────┼──────────────┼──────────────┼──────────────┼─────────────┘
        │              │              │              │
        └──────────────┴──────────────┴──────────────┘
                              │
                    ┌─────────▼─────────┐
                    │   Apache/Nginx    │
                    │   (Reverse Proxy) │
                    └─────────┬─────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
┌───────▼───────┐   ┌─────────▼─────────┐   ┌───────▼───────┐
│  Web Routes   │   │   API Routes      │   │  WebSocket    │
│  (web.php)    │   │   (api.php)       │   │  (Ratchet)    │
└───────┬───────┘   └─────────┬─────────┘   └───────┬───────┘
        │                     │                     │
        └─────────────────────┼─────────────────────┘
                              │
                    ┌─────────▼─────────┐
                    │  CONTROLLER LAYER │
                    │  (422 controllers)│
                    └─────────┬─────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
┌───────▼───────┐   ┌─────────▼─────────┐   ┌───────▼───────┐
│  Service      │   │  Model Layer      │   │  Cache        │
│  Layer        │   │  (91 models)      │   │  (Redis)      │
│  (454 serv.)  │   │                   │   │               │
└───────┬───────┘   └─────────┬─────────┘   └───────┬───────┘
        │                     │                     │
        └─────────────────────┼─────────────────────┘
                              │
                    ┌─────────▼─────────┐
                    │  DATABASE LAYER   │
                    │  MySQL 8.0        │
                    │  (584 tables)     │
                    └───────────────────┘
```

## 7-Layer Tenant Enforcement

| Layer | Mechanism | Implementation |
|-------|-----------|----------------|
| 1. Global | `enforceTenantStatus()` | Blocks suspended tenants |
| 2. Controller | `TenantAwareTrait` | tenant_id from session |
| 3. Service | `ServiceTenantTrait` | tenant_id added to all SQL writes |
| 4. Model | `Model::$tenantScoped` | 39+ business models |
| 5. Cache | `CacheService::tenantKey()` | Prefix `t{N}_` on all cache keys |
| 6. Cron | `TenantContext::setById()` | In all cron scripts |
| 7. Auth | tenant_id filtering | On ALL login/register queries |

## Folder Structure

```
app/
├── Core/           → Framework (Database, Router, Auth, Cache)
├── Http/
│   └── Controllers/
│       ├── Admin/      → 217 admin controllers
│       ├── Api/        → 56 API controllers
│       ├── Auth/       → 13 auth controllers
│       ├── Front/      → 47 front controllers
│       ├── Employee/   → 8 employee controllers
│       └── MLM/        → MLM controllers
├── Models/         → 91 models
├── Services/       → 454 services
├── Views/          → 1,700 view templates
└── Helpers/        → Utility functions

routes/
├── web.php         → Web routes (1,500+)
└── api.php         → API routes (200+)

mobile/
└── apsdreamhome_app_v2/  → Flutter app (147 pages)

scripts/
└── cron_*          → Cron jobs

tests/
└── visual_tests/   → E2E tests (153 test cases)
```
