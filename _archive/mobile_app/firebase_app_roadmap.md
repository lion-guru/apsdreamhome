# APS Dream Home - Firebase App Roadmap

## Overview
**Phase 2**: Build standalone Firebase version of the app
**Cost**: FREE for up to 50,000 users (Firebase Spark Plan)
**Timeline**: 7-10 days after Phase 1 completion

---

## Why Firebase?

| Feature | Benefit |
|---------|---------|
| **Authentication** | Phone OTP, Email, Google Sign-in ready |
| **Firestore** | Real-time database (instant updates) |
| **Offline** | Built-in offline persistence |
| **Storage** | Document/photo upload |
| **Hosting** | Free for web version |
| **Analytics** | User behavior tracking |
| **Notifications** | Push notifications FREE |
| **Scalability** | Auto-scales to millions |

---

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    FLUTTER APP                          │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐   │
│  │   Offline   │  │  Firebase   │  │  Local Cache    │   │
│  │  (SQLite)   │◄─┤  Firestore  │◄─┤  (Riverpod)    │   │
│  │  Fallback   │  │  (Primary)  │  │                 │   │
│  └─────────────┘  └─────────────┘  └─────────────────┘   │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │  Firebase Auth (Phone OTP / Email / Google)      │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │  Firebase Storage (Documents, Photos, Receipts)│   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## Database Schema (Firestore)

### Collections Structure

```javascript
// 1. USERS Collection
/users/{userId}
{
  id: string,
  name: string,
  phone: string,
  email: string,
  role: 'customer' | 'associate' | 'agent' | 'admin',
  rank: 'associate' | 'sr_associate' | 'bdm' | 'site_manager',
  parentId: string | null,  // For MLM genealogy
  targetAmount: number,
  bankDetails: {
    accountNumber: string,
    ifscCode: string,
    bankName: string
  },
  createdAt: timestamp,
  updatedAt: timestamp
}

// 2. COLONIES Collection
/colonies/{colonyId}
{
  id: string,
  name: string,
  location: {
    address: string,
    district: string,
    state: string,
    latitude: number,
    longitude: number
  },
  totalPlots: number,
  availablePlots: number,
  pricePerSqft: number,
  amenities: ['road', 'light', 'park', 'water', 'electricity'],
  status: 'launching' | 'selling' | 'sold_out' | 'completed',
  launchDate: timestamp,
  expectedCompletion: timestamp,
  images: string[],
  masterPlanUrl: string,
  createdAt: timestamp
}

// 3. PLOTS Collection
/plots/{plotId}
{
  id: string,
  colonyId: string,  // Reference to colony
  plotNumber: string,
  areaSqft: number,
  facing: 'north' | 'south' | 'east' | 'west',
  isCorner: boolean,
  isParkFacing: boolean,
  basePrice: number,
  premiumPrice: number,
  finalPrice: number,
  status: 'available' | 'hold' | 'booked' | 'sold',
  bookedBy: string | null,  // userId
  bookedAt: timestamp | null,
  createdAt: timestamp
}

// 4. BOOKINGS Collection
/bookings/{bookingId}
{
  id: string,
  plotId: string,
  customerId: string,
  associateId: string,
  tokenAmount: number,
  totalPrice: number,
  paymentPlan: 'full' | 'emi',
  status: 'pending' | 'approved' | 'rejected' | 'completed',
  documents: {
    aadharUrl: string,
    panUrl: string,
    photoUrl: string
  },
  createdAt: timestamp,
  approvedAt: timestamp | null,
  approvedBy: string | null
}

// 5. COMMISSIONS Collection
/commissions/{commissionId}
{
  id: string,
  associateId: string,
  bookingId: string,
  saleAmount: number,
  commissionAmount: number,
  percentage: number,
  rankAtTime: string,
  level: number,  // 1 = direct, 2-7 = indirect
  status: 'pending' | 'paid' | 'hold',
  paidAt: timestamp | null,
  transactionId: string | null,
  createdAt: timestamp
}

// 6. LEADS Collection
/leads/{leadId}
{
  id: string,
  name: string,
  phone: string,
  email: string | null,
  source: 'walk_in' | 'referral' | 'facebook' | 'google',
  status: 'new' | 'contacted' | 'interested' | 'visited' | 'converted' | 'lost',
  interestedIn: string[],  // plotIds
  budget: number | null,
  assignedTo: string,  // associateId
  notes: string,
  followUpDate: timestamp | null,
  createdAt: timestamp,
  updatedAt: timestamp
}

// 7. PAYMENTS Collection
/payments/{paymentId}
{
  id: string,
  bookingId: string,
  userId: string,
  amount: number,
  type: 'token' | 'installment' | 'registry' | 'full_payment',
  method: 'cash' | 'cheque' | 'online' | 'upi',
  status: 'pending' | 'completed' | 'failed' | 'refunded',
  razorpayOrderId: string | null,
  razorpayPaymentId: string | null,
  receiptUrl: string | null,
  createdAt: timestamp
}

// 8. SITE_VISITS Collection
/site_visits/{visitId}
{
  id: string,
  agentId: string,
  customerName: string,
  customerPhone: string,
  colonyId: string,
  plotsShown: string[],  // plotIds
  location: {
    latitude: number,
    longitude: number
  },
  visitTime: timestamp,
  notes: string,
  followUpRequired: boolean,
  createdAt: timestamp
}

// 9. DOCUMENTS Collection
/documents/{documentId}
{
  id: string,
  userId: string,
  type: 'aadhar' | 'pan' | 'registry' | 'agreement' | 'receipt' | 'booking',
  title: string,
  fileUrl: string,
  fileName: string,
  fileSize: number,
  verified: boolean,
  verifiedBy: string | null,
  verifiedAt: timestamp | null,
  createdAt: timestamp
}

// 10. NOTIFICATIONS Collection
/notifications/{notificationId}
{
  id: string,
  userId: string,
  title: string,
  body: string,
  type: 'commission' | 'booking' | 'lead' | 'payment' | 'general',
  data: map,  // Additional payload
  read: boolean,
  createdAt: timestamp
}
```

---

## Feature Roadmap

### Week 1: Foundation
- [ ] Firebase project setup
- [ ] Authentication (Phone OTP + Email)
- [ ] Firestore database design
- [ ] Security rules configuration
- [ ] Basic navigation structure

### Week 2: Core Features
- [ ] Colony listing with search/filter
- [ ] Interactive plot map
- [ ] Plot booking workflow
- [ ] Document upload to Firebase Storage
- [ ] Offline persistence

### Week 3: MLM Features
- [ ] Genealogy tree visualization
- [ ] Real-time commission calculation
- [ ] Commission withdrawal request
- [ ] Team performance dashboard
- [ ] Rank progress tracking

### Week 4: Advanced Features
- [ ] Site visit GPS tracking
- [ ] Lead management with offline
- [ ] WhatsApp integration
- [ ] Push notifications
- [ ] Payment integration (Razorpay)

---

## Security Rules

```javascript
// Firestore Security Rules
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    
    // Users can only read/write their own data
    match /users/{userId} {
      allow read, write: if request.auth != null && request.auth.uid == userId;
    }
    
    // Colonies - public read, admin write
    match /colonies/{colonyId} {
      allow read: if request.auth != null;
      allow write: if request.auth != null && 
        get(/databases/$(database)/documents/users/$(request.auth.uid)).data.role == 'admin';
    }
    
    // Plots - public read, admin/agent write
    match /plots/{plotId} {
      allow read: if request.auth != null;
      allow write: if request.auth != null && 
        get(/databases/$(database)/documents/users/$(request.auth.uid)).data.role in ['admin', 'agent'];
    }
    
    // Bookings - user sees own, admin sees all
    match /bookings/{bookingId} {
      allow read: if request.auth != null && (
        resource.data.customerId == request.auth.uid ||
        resource.data.associateId == request.auth.uid ||
        get(/databases/$(database)/documents/users/$(request.auth.uid)).data.role == 'admin'
      );
      allow create: if request.auth != null;
      allow update: if request.auth != null && 
        get(/databases/$(database)/documents/users/$(request.auth.uid)).data.role == 'admin';
    }
    
    // Commissions - associate sees own, admin sees all
    match /commissions/{commissionId} {
      allow read: if request.auth != null && (
        resource.data.associateId == request.auth.uid ||
        get(/databases/$(database)/documents/users/$(request.auth.uid)).data.role == 'admin'
      );
      allow write: if request.auth != null && 
        get(/databases/$(database)/documents/users/$(request.auth.uid)).data.role == 'admin';
    }
  }
}
```

---

## Cost Analysis

### Firebase Spark Plan (FREE)
| Service | Free Limit | Estimated Usage |
|---------|------------|-----------------|
| **Authentication** | 10k users/month | ✅ FREE |
| **Firestore** | 50k reads/day, 20k writes/day | ✅ FREE |
| **Storage** | 5 GB | ✅ FREE |
| **Hosting** | 1 GB | ✅ FREE |
| **Functions** | 125k invocations/month | ✅ FREE |
| **Notifications** | Unlimited | ✅ FREE |

### When to Upgrade (Blaze Plan - Pay as you go)
- 50,000+ monthly active users
- High traffic (1M+ reads/day)
- Heavy storage (>5GB documents)

---

## Dependencies (pubspec.yaml)

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # Firebase Core
  firebase_core: ^2.24.2
  firebase_auth: ^4.16.0
  cloud_firestore: ^4.14.0
  firebase_storage: ^11.6.0
  firebase_messaging: ^14.7.10
  firebase_analytics: ^10.8.0
  
  # State Management
  flutter_riverpod: ^2.4.9
  
  # Navigation
  go_router: ^13.0.1
  
  # UI
  cached_network_image: ^3.3.1
  google_maps_flutter: ^2.5.0
  flutter_svg: ^2.0.9
  shimmer: ^3.0.0
  
  # Utils
  image_picker: ^1.0.7
  share_plus: ^7.2.1
  url_launcher: ^6.2.2
  intl: ^0.18.1
  connectivity_plus: ^5.0.2
  
  # Payment
  razorpay_flutter: ^1.3.6
  
  # OCR/Documents
  google_mlkit_text_recognition: ^0.11.0
  pdf: ^3.10.4
  printing: ^5.11.1
  
  # WhatsApp
  whatsapp_share: ^2.0.2
  
dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^3.0.1
```

---

## Implementation Timeline

| Day | Task | Hours |
|-----|------|-------|
| 1 | Firebase setup + Auth | 4-5 |
| 2 | Firestore schema + Security rules | 4-5 |
| 3 | Colony + Plot screens | 6-7 |
| 4 | Booking workflow + Documents | 6-7 |
| 5 | MLM Commission + Genealogy | 6-7 |
| 6 | Offline sync + Cache | 5-6 |
| 7 | Payment integration | 4-5 |
| 8 | Notifications + WhatsApp | 4-5 |
| 9 | Testing + Bug fixes | 6-8 |
| 10 | Build + Release | 3-4 |
| **Total** | | **50-60 hours** |

---

## Next Steps After Phase 1

1. Create Firebase project at https://console.firebase.google.com
2. Add Android app (package: com.apsdreamhome.app)
3. Download google-services.json
4. Start with Authentication module
5. Build incrementally (one feature at a time)

---

## Notes

- **Firestore is NOT SQL** - No joins, use denormalization
- **Offline by default** - Enable persistence in main.dart
- **Real-time updates** - Use StreamBuilder for live data
- **Security first** - Always validate in security rules
- **Cost control** - Monitor reads/writes in Firebase console

---

**Ready to start after Phase 1 completion!** 🚀
