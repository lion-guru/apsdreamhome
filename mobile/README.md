# APS Dream Home Mobile App

A premium Flutter mobile application for APS Dream Home Real Estate & MLM platform with offline-first capabilities.

## 🏗️ Architecture

This app follows Clean Architecture principles with the following structure:

```
lib/
├── core/                    # Core functionality
│   ├── constants/          # App constants and configuration
│   ├── errors/             # Custom error classes
│   ├── router/             # Navigation configuration
│   ├── services/           # Core services (API, Database, Sync)
│   ├── theme/              # App theme and styling
│   └── utils/              # Utility functions
├── data/                   # Data layer
│   ├── datasources/        # Data sources (local, remote)
│   ├── models/             # Data models
│   └── repositories/       # Repository implementations
├── domain/                 # Business logic
│   ├── entities/           # Business entities
│   ├── repositories/       # Repository interfaces
│   └── usecases/           # Use cases
└── presentation/           # UI layer
    ├── pages/              # Screens
    ├── providers/          # Riverpod state management
    └── widgets/            # Reusable UI components
```

## 🚀 Features

### Core Features
- **Offline-First Architecture**: Full functionality without internet connection
- **Smart Sync Engine**: Automatic data synchronization when online
- **JWT Authentication**: Secure user authentication with token management
- **Real-time Updates**: Live sync status indicators

### Business Modules
- **Property Marketplace**: Browse and search properties with offline support
- **Lead CRM**: Manage leads with offline capabilities and call/WhatsApp integration
- **MLM Dashboard**: Track commissions, team performance, and rank progress
- **User Profile**: Manage profile information and settings

### Advanced Features
- **Differential Commission**: (Senior Rank %) - (Junior Rank %) calculation
- **Rank Tiers**: Associate → Sr. Associate → BDM → Sr. BDM → VP → President → Site Manager
- **Gamified Dashboard**: Progress tracking and achievement badges
- **Voice-to-Lead**: Convert speech to lead entries (planned)
- **AR Plot Overlay**: Augmented reality property visualization (planned)

## 🎨 Design System

### Theme
- **Primary Color**: Deep Royal Blue (#1A237E)
- **Accent Color**: Gold (#FFD700)
- **Style**: Glassmorphism with premium feel
- **Typography**: Inter font family

### UI Components
- Glass cards with blur effects
- Smooth hero animations
- Shimmer loading effects
- Custom status badges

## 💾 Data Management

### Local Storage
- **SQLite**: Primary local database using sqflite
- **Tables**: users, properties, leads, commissions, sync_queue
- **Sync Queue**: Offline changes queued for server sync

### API Integration
- **Base URL**: http://localhost/apsdreamhome
- **Authentication**: JWT tokens with secure storage
- **Endpoints**: RESTful API for data synchronization

## 🔧 Technical Stack

### Dependencies
- **flutter_riverpod**: State management
- **go_router**: Navigation
- **dio**: HTTP client
- **sqflite**: Local database
- **flutter_secure_storage**: Secure token storage
- **connectivity_plus**: Network connectivity
- **cached_network_image**: Image caching
- **shimmer**: Loading animations

### Development Tools
- **build_runner**: Code generation
- **json_serializable**: JSON serialization
- **retrofit**: Type-safe API clients

## 📱 Business Logic

### MLM Commission Structure
```
Associate: 6%  | Target: 1M
Sr. Associate: 8%  | Target: 3.5M
BDM: 10%  | Target: 7M
Sr. BDM: 12%  | Target: 15M
Vice President: 15%  | Target: 30M
President: 18%  | Target: 50M
Site Manager: 20%  | Target: 100M
```

### Differential Commission Formula
```
Senior Commission = (Senior Rank %) - (Junior Rank %)
Example: Site Manager (20%) - Associate (6%) = 14% to Site Manager
```

## 🔐 Security

- **JWT Tokens**: Secure authentication with automatic refresh
- **Secure Storage**: Sensitive data stored in flutter_secure_storage
- **SQL Injection Protection**: Parameterized queries
- **Input Validation**: Comprehensive form validation

## 📋 Setup Instructions

### Prerequisites
- Flutter SDK >= 3.10.0
- Dart SDK >= 3.0.0
- XAMPP with PHP/MySQL backend

### Installation
1. Clone the repository
2. Run `flutter pub get` to install dependencies
3. Run `flutter packages pub run build_runner build` to generate code
4. Connect to running APS Dream Home backend
5. Run the app: `flutter run`

### Configuration
Update `lib/core/constants/app_constants.dart` with your backend URL and settings.

## 🔄 Sync Engine

The sync engine handles:
- **Offline Queue**: Changes stored locally when offline
- **Auto Sync**: Automatic sync when network available
- **Conflict Resolution**: Server data takes precedence
- **Retry Logic**: Failed sync attempts with exponential backoff
- **Status Indicators**: Real-time sync status display

## 📊 Performance

- **Lazy Loading**: Data loaded on demand
- **Image Caching**: Efficient image memory management
- **Database Indexing**: Optimized local queries
- **Background Sync**: Non-blocking synchronization

## 🧪 Testing

- **Unit Tests**: Business logic validation
- **Widget Tests**: UI component testing
- **Integration Tests**: End-to-end workflows

## 🚀 Deployment

### Android
```bash
flutter build apk --release
flutter build appbundle --release
```

### iOS
```bash
flutter build ios --release
```

## 📞 Support

- **Phone**: 7007444842
- **Email**: support@apsdreamhome.com
- **Version**: 1.0.0

## 🗺️ Roadmap

### Phase 1 (Current)
- ✅ Core architecture
- ✅ Authentication system
- ✅ Property marketplace
- ✅ Lead CRM
- ✅ MLM dashboard
- ✅ Offline sync engine

### Phase 2 (Upcoming)
- 🔄 Voice-to-lead AI
- 🔄 Document scanner OCR
- 🔄 Live plot availability map
- 🔄 Site visit tracking
- 🔄 WhatsApp CRM bridge

### Phase 3 (Future)
- 📋 AR plot overlay
- 📋 AI property valuer
- 📋 Advanced analytics
- 📋 Team collaboration tools

---

**Built with ❤️ for APS Dream Home**
