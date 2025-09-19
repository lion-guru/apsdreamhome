# APS Dream Home - System Flow

## 1. User Authentication Flow
```mermaid
graph TD
    A[User Visits Site] --> B{Logged In?}
    B -->|No| C[Show Login/Register]
    B -->|Yes| D[Show Dashboard]
    C --> E[Login/Register Form]
    E --> F[Authentication]
    F -->|Success| D
    F -->|Failure| C
```

## 2. Property Booking Flow
```mermaid
graph TD
    A[Customer Browses Properties] --> B[Selects Property]
    B --> C[Check Availability]
    C --> D[Fill Booking Form]
    D --> E[Upload Documents]
    E --> F[Make Payment]
    F --> G[Booking Confirmation]
    G --> H[Associate Commission Calculation]
    H --> I[Update MLM Tree]
```

## 3. MLM Commission Flow
```mermaid
graph LR
    A[Sale Completed] --> B[Identify Associate]
    B --> C[Find Upline to 7 Levels]
    C --> D[Calculate Commission]
    D --> E[Update Balances]
    E --> F[Generate Payout]
    F --> G[Process Payment]
    G --> H[Update Ledger]
```

## 4. Land Acquisition Flow
```mermaid
graph TD
    A[Land Identification] --> B[Owner Verification]
    B --> C[Document Collection]
    C --> D[Payment Processing]
    D --> E[Registry Update]
    E --> F[Land Bank Update]
```

## 5. System Architecture
```
+-------------------+     +---------------------+     +-------------------+
|                   |     |                     |     |                   |
|    Frontend       |<--->|    API Layer        |<--->|    Database        |
|  (HTML/CSS/JS)    |     |  (PHP/Laravel)     |     |   (MySQL)         |
+-------------------+     +---------------------+     +-------------------+
         ^                         ^                               ^
         |                         |                               |
         v                         v                               v
+----------------+      +---------------------+           +-------------------+
|                |      |                     |           |                   |
|  Customers     |      |  Admin/Agents      |           |  External APIs    |
|  (Web/Mobile)  |      |  (Web Dashboard)   |           |  (Payment, SMS)   |
+----------------+      +---------------------+           +-------------------+
```

## 6. Data Flow
```mermaid
sequenceDiagram
    participant C as Customer
    participant F as Frontend
    participant B as Backend
    participant D as Database
    participant P as Payment Gateway
    
    C->>F: Browse Properties
    F->>B: API Request
    B->>D: Query Database
    D-->>B: Return Results
    B-->>F: JSON Response
    F-->>C: Display Properties
    
    C->>F: Initiate Booking
    F->>B: Submit Booking
    B->>P: Process Payment
    P-->>B: Payment Confirmation
    B->>D: Save Booking
    B->>D: Update MLM Commissions
    B-->>F: Booking Confirmation
    F-->>C: Show Success
```

## 7. Security Flow
```mermaid
graph LR
    A[Request] --> B{Authenticated?}
    B -->|No| C[Return 401]
    B -->|Yes| D[Check Permissions]
    D -->|Allowed| E[Process Request]
    D -->|Denied| F[Return 403]
    E --> G[Log Activity]
    G --> H[Return Response]
```

## 8. Reporting Flow
```mermaid
graph TD
    A[Generate Report] --> B[Select Parameters]
    B --> C[Fetch Data]
    C --> D[Process Data]
    D --> E[Format Output]
    E --> F[Export Options]
    F --> G[Download/Email]
```

## 9. Notification Flow
```mermaid
graph LR
    A[Event Trigger] --> B[Generate Notification]
    B --> C[Select Channel]
    C -->|Email| D[Send Email]
    C -->|SMS| E[Send SMS]
    C -->|In-App| F[Show Notification]
    D & E & F --> G[Update Status]
```

## 10. Backup & Recovery Flow
```mermaid
graph TD
    A[Schedule Backup] --> B[Export Database]
    B --> C[Compress Files]
    C --> D[Upload to Storage]
    D --> E[Verify Backup]
    E --> F[Cleanup Old Backups]
    
    G[Initiate Recovery] --> H[Select Backup]
    H --> I[Download Backup]
    I --> J[Restore Database]
    J --> K[Verify Data]
    K --> L[System Ready]
```

---
*Last Updated: 18th May 2025*
