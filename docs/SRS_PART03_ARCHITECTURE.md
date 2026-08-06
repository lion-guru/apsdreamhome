# PART 3: SYSTEM ARCHITECTURE

## 8. High-Level Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              CLIENT LAYER                                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │
│  │   Web App    │  │  Flutter     │  │   Mobile     │  │    PWA       │   │
│  │  (Bootstrap  │  │  Mobile App  │  │   Browser    │  │  (Offline)   │   │
│  │   + jQuery)  │  │  (Android/iOS)│  │              │  │              │   │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘   │
│         │                  │                  │                  │           │
└─────────┼──────────────────┼──────────────────┼──────────────────┼───────────┘
          │                  │                  │                  │
          └──────────────────┴──────────────────┴──────────────────┘
                                    │
                          ┌─────────▼─────────┐
                          │   Apache/Nginx    │
                          │   (Reverse Proxy) │
                          │   SSL/TLS         │
                          └─────────┬─────────┘
                                    │
          ┌─────────────────────────┼─────────────────────────┐
          │                         │                         │
┌─────────▼─────────┐   ┌──────────▼──────────┐   ┌──────────▼──────────┐
│   Web Routes      │   │    API Routes       │   │   WebSocket         │
│   (web.php)       │   │    (api.php)        │   │   (Ratchet)         │
│   1,500+ routes   │   │    200+ routes      │   │   Real-time         │
└─────────┬─────────┘   └──────────┬──────────┘   └──────────┬──────────┘
          │                         │                         │
          └─────────────────────────┼─────────────────────────┘
                                    │
                          ┌─────────▼─────────┐
                          │  CONTROLLER LAYER │
                          │  (422 controllers)│
                          │                   │
                          │  Admin: 217       │
                          │  API: 56          │
                          │  Front: 47        │
                          │  Auth: 13         │
                          │  Employee: 8      │
                          └─────────┬─────────┘
                                    │
          ┌─────────────────────────┼─────────────────────────┐
          │                         │                         │
┌─────────▼─────────┐   ┌──────────▼──────────┐   ┌──────────▼──────────┐
│   Service Layer   │   │    Model Layer      │   │   Cache Layer       │
│   (454 services)  │   │    (91 models)      │   │   (Redis + File)    │
│                   │   │                     │   │                     │
│   Business Logic  │   │    Data Access      │   │   Session Store     │
│   MLM Engine      │   │    tenantScoped     │   │   Query Cache       │
│   Commission Calc │   │    ORM-like         │   │   Hot Path Cache    │
└─────────┬─────────┘   └──────────┬──────────┘   └──────────┬──────────┘
          │                         │                         │
          └─────────────────────────┼─────────────────────────┘
                                    │
                          ┌─────────▼─────────┐
                          │  DATABASE LAYER   │
                          │  MySQL 8.0        │
                          │  (584 tables)     │
                          │  Port 3307        │
                          │                   │
                          │  263 Foreign Keys │
                          │  InnoDB 100%      │
                          └───────────────────┘
```

## 9. Technology Stack Justification

### Backend Stack

| Component | Technology | Why This? |
|-----------|-----------|-----------|
| **Language** | PHP 8.3 | Client team familiarity, shared hosting compatible |
| **Framework** | Custom MVC | Lightweight, no overhead, full control |
| **Database** | MySQL 8.0 | ACID compliant, JSON support, widely used |
| **Cache** | Redis + File | Speed + fallback, session storage |
| **Web Server** | Apache | .htaccess support, shared hosting |
| **WebSocket** | Ratchet | Real-time notifications, chat |

### Frontend Stack (Web)

| Component | Technology | Why This? |
|-----------|-----------|-----------|
| **CSS** | Bootstrap 5 | Rapid development, responsive |
| **JS** | Vanilla + jQuery | Lightweight, no build step |
| **Icons** | Font Awesome 6.5.1 | Comprehensive icon set |
| **Charts** | Chart.js 4.4.7 | Beautiful, interactive charts |
| **Maps** | Leaflet.js | Open-source, lightweight |

### Mobile Stack

| Component | Technology | Why This? |
|-----------|-----------|-----------|
| **Framework** | Flutter 3.44+ | Single codebase, Android + iOS |
| **State** | Riverpod | Type-safe, testable |
| **Routing** | GoRouter | Declarative, deep linking |
| **HTTP** | Dio + http | Interceptors, caching |
| **Local DB** | SQLite | Offline support |

### DevOps Stack

| Component | Technology | Why This? |
|-----------|-----------|-----------|
| **Container** | Docker | Consistent environments |
| **CI/CD** | GitHub Actions | Integrated with repo |
| **SSL** | Let's Encrypt | Free, automated |
| **Storage** | AWS S3 | Scalable, reliable |
| **Payment** | Razorpay | India-focused, UPI support |

## 10. Module Interaction Diagrams

### Colony-to-Sales Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   COLONY    │────▶│    PLOT     │────▶│   BOOKING   │────▶│   PAYMENT   │
│  CREATION   │     │   CUTTING   │     │   ENTRY     │     │   RECORD    │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
       │                   │                   │                   │
       ▼                   ▼                   ▼                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  PRICING    │     │  AVAILABILITY│    │  EMI SCHED  │     │ COMMISSION  │
│  APPROVAL   │     │  STATUS     │     │  GENERATION │     │ CALCULATION │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
```

### Lead-to-Customer Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    LEAD     │────▶│  QUALIFY    │────▶│  SITE VISIT │────▶│   BOOKING   │
│  CAPTURE    │     │  SCORING    │     │  SCHEDULE   │     │   CONVERT   │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
       │                   │                   │                   │
       ▼                   ▼                   ▼                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  CAMPAIGN   │     │  ASSIGN TO  │     │  FEEDBACK   │     │  WELCOME    │
│  SOURCE     │     │  ASSOCIATE  │     │  CAPTURE    │     │  KIT        │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
```

### Commission Distribution Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   PLOT      │────▶│  PAYMENT    │────▶│ COMMISSION  │────▶│   PAYOUT    │
│   SALE      │     │  RECEIVED   │     │ CALCULATION │     │   BATCH     │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
       │                   │                   │                   │
       ▼                   ▼                   ▼                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  BOOKING    │     │  LEDGER     │     │  TRACK A/B/C│     │  TDS        │
│  RECORD     │     │  ENTRY      │     │  + BONUSES  │     │  DEDUCTION  │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
```
