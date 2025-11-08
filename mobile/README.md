# 🏠 APS Dream Home Mobile App

A comprehensive React Native mobile application for the APS Dream Home real estate platform. This app provides a modern, user-friendly interface for browsing properties, managing bookings, and accessing real estate services on mobile devices.

## 🚀 Features

### 🔐 Authentication & User Management
- **Secure Login/Register** - JWT-based authentication
- **User Profiles** - Complete profile management
- **Role-based Access** - Support for customers, agents, and admins
- **Biometric Authentication** - Fingerprint/Face ID support

### 🏡 Property Management
- **Property Listings** - Browse properties with advanced filtering
- **Property Details** - Comprehensive property information
- **Favorites System** - Save and manage favorite properties
- **Property Search** - Full-text search with filters
- **Image Galleries** - High-quality property photos
- **Virtual Tours** - 360° property viewing

### 📍 Location Services
- **Map Integration** - Interactive property maps
- **Nearby Properties** - Location-based property search
- **Geolocation** - Current location services
- **City/State Filters** - Location-based filtering

### 💼 Booking & Transactions
- **Property Booking** - Easy booking system
- **Payment Integration** - Secure payment processing
- **Booking History** - Track all bookings
- **Status Updates** - Real-time booking status

### 👥 Agent Features
- **Agent Dashboard** - Commission tracking and management
- **Lead Management** - Customer inquiry management
- **Performance Analytics** - Sales and performance metrics
- **Team Management** - Multi-level agent hierarchy

### 💰 Commission System
- **Commission Tracking** - Real-time commission calculations
- **Payout Management** - Commission withdrawal system
- **Performance Reports** - Detailed analytics
- **Multi-level Commissions** - Agent referral system

### 📱 Advanced Features
- **Push Notifications** - Real-time updates
- **Offline Support** - Offline property browsing
- **WhatsApp Integration** - Direct WhatsApp contact
- **Multi-language Support** - i18n ready
- **Dark/Light Theme** - Theme switching
- **Accessibility** - WCAG compliance

## 🛠️ Technology Stack

### Core Technologies
- **React Native 0.72** - Cross-platform mobile development
- **Redux Toolkit** - State management
- **React Navigation 6** - Navigation system
- **TypeScript** - Type safety (optional)

### UI & Styling
- **React Native Elements** - UI component library
- **React Native Paper** - Material Design components
- **React Native Vector Icons** - Icon system
- **Custom Theme System** - Theming engine

### Backend Integration
- **Axios** - HTTP client
- **AsyncStorage** - Local storage
- **React Native NetInfo** - Network state
- **Biometrics** - Secure authentication

### Development Tools
- **Metro Bundler** - React Native bundler
- **Flipper** - Debugging platform
- **React Native Debugger** - Development tools
- **ESLint/Prettier** - Code formatting

## 📁 Project Structure

```
mobile/
├── 📁 src/
│   ├── 📁 components/          # Reusable UI components
│   │   ├── PropertyCard.js     # Property display component
│   │   ├── SearchBar.js        # Search functionality
│   │   ├── FilterModal.js      # Property filters
│   │   └── ...
│   ├── 📁 screens/             # Application screens
│   │   ├── 📁 auth/            # Authentication screens
│   │   ├── 📁 main/            # Main app screens
│   │   ├── 📁 booking/         # Booking screens
│   │   ├── 📁 agent/           # Agent screens
│   │   └── 📁 admin/           # Admin screens
│   ├── 📁 services/            # API services
│   │   ├── ApiService.js       # Main API client
│   │   └── ...
│   ├── 📁 store/               # Redux store
│   │   ├── 📁 actions/         # Redux actions
│   │   ├── 📁 reducers/        # Redux reducers
│   │   └── index.js            # Store configuration
│   ├── 📁 theme/               # Theme system
│   │   └── index.js            # Theme configuration
│   ├── 📁 utils/               # Utility functions
│   ├── 📁 constants/           # App constants
│   └── 📁 types/               # TypeScript types
├── 📁 android/                 # Android native code
├── 📁 ios/                     # iOS native code
├── 📁 assets/                  # Static assets
├── App.js                      # Main app component
├── index.js                    # App entry point
├── package.json                # Dependencies
├── metro.config.js             # Metro bundler config
└── babel.config.js             # Babel configuration
```

## 🚀 Getting Started

### Prerequisites

- **Node.js** (>= 16.0.0)
- **React Native CLI** - `npm install -g @react-native-community/cli`
- **Android Studio** (for Android development)
- **Xcode** (for iOS development on macOS)
- **Java JDK** (>= 11)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd apsdreamhomefinal/mobile
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Configure environment**
   ```bash
   # Copy environment template
   cp .env.example .env

   # Edit .env with your API endpoints
   nano .env
   ```

4. **Install iOS dependencies** (macOS only)
   ```bash
   cd ios && pod install
   ```

5. **Start Metro bundler**
   ```bash
   npm start
   ```

### Running the App

#### Android
```bash
npm run android
```

#### iOS (macOS only)
```bash
npm run ios
```

#### Development
```bash
npm start
```

## 🔧 Configuration

### Environment Variables

Create a `.env` file in the mobile directory:

```env
# API Configuration
API_BASE_URL=http://localhost/apsdreamhomefinal/api
API_TIMEOUT=15000

# App Configuration
APP_ENV=development
APP_NAME=APS Dream Home
APP_VERSION=1.0.0

# Authentication
JWT_SECRET=your-secret-key

# Push Notifications
ONESIGNAL_APP_ID=your-onesignal-app-id

# Maps
GOOGLE_MAPS_API_KEY=your-google-maps-key

# Analytics
GOOGLE_ANALYTICS_ID=your-ga-id
```

### API Configuration

The app automatically detects the environment and configures API endpoints:

- **Development**: `http://localhost/apsdreamhomefinal/api`
- **Production**: `https://api.apsdreamhome.com/v1`

## 📱 App Screens

### Authentication Flow
1. **Splash Screen** - App initialization and branding
2. **Login Screen** - User authentication
3. **Register Screen** - New user registration
4. **Forgot Password** - Password recovery

### Main App
1. **Home Screen** - Featured properties and search
2. **Properties Screen** - Property listings with filters
3. **Property Detail** - Detailed property information
4. **Favorites** - Saved properties
5. **Profile** - User profile management

### Booking System
1. **Booking Screen** - Property booking form
2. **Booking History** - Past and current bookings
3. **Payment** - Payment processing

### Agent Dashboard
1. **Agent Dashboard** - Performance overview
2. **Commission Tracking** - Earnings management
3. **Lead Management** - Customer inquiries
4. **Reports** - Analytics and reports

## 🎨 Theme System

The app includes a comprehensive theme system with:

### Light Theme
- Primary: `#3498db` (Blue)
- Background: `#ffffff` (White)
- Text: `#212529` (Dark Gray)
- Cards: `#ffffff` (White)

### Dark Theme
- Primary: `#3498db` (Blue)
- Background: `#1a1a1a` (Dark)
- Text: `#ffffff` (White)
- Cards: `#2d2d2d` (Dark Gray)

### Custom Theming
```javascript
import {useTheme} from '../theme';

const MyComponent = () => {
  const {theme} = useTheme();

  return (
    <View style={{backgroundColor: theme.colors.background}}>
      <Text style={{color: theme.colors.text}}>
        Themed text
      </Text>
    </View>
  );
};
```

## 🔒 Security Features

### Authentication Security
- **JWT Tokens** - Secure token-based authentication
- **Biometric Auth** - Fingerprint/Face ID support
- **Auto-logout** - Session timeout handling
- **Secure Storage** - Encrypted local storage

### Data Security
- **HTTPS Only** - All API calls use HTTPS
- **Input Validation** - Client-side validation
- **XSS Protection** - Sanitized user inputs
- **CSRF Protection** - Request validation

### Privacy
- **Location Permissions** - Optional location access
- **Camera Permissions** - Controlled media access
- **Notification Permissions** - User consent required
- **Data Minimization** - Only necessary data collected

## 🧪 Testing

### Unit Tests
```bash
npm test
```

### Integration Tests
```bash
npm run test:integration
```

### E2E Tests
```bash
npm run test:e2e
```

### Test Coverage
```bash
npm run test:coverage
```

## 📦 Build & Deployment

### Android Build
```bash
# Development build
npm run android

# Release build
npm run build:android

# Generate APK
cd android && ./gradlew assembleRelease
```

### iOS Build
```bash
# Development build
npm run ios

# Release build
npm run build:ios

# Archive for App Store
cd ios && xcodebuild -workspace ApsDreamHome.xcworkspace -scheme ApsDreamHome -configuration Release archive
```

### Code Signing

#### Android
1. Generate keystore: `keytool -genkey -v -keystore aps-dream-home.keystore -alias aps-dream-home -keyalg RSA -keysize 2048 -validity 10000`
2. Configure in `android/app/build.gradle`

#### iOS
1. Create certificates in Apple Developer Console
2. Configure in Xcode project settings
3. Update provisioning profiles

## 📊 Performance Optimization

### Bundle Optimization
- **Code Splitting** - Lazy loading of screens
- **Tree Shaking** - Remove unused code
- **Asset Optimization** - Compressed images
- **Caching** - Efficient caching strategies

### Memory Management
- **Component Cleanup** - Proper cleanup in useEffect
- **Image Optimization** - Optimized image loading
- **List Virtualization** - FlatList optimization
- **Memory Leaks** - Prevention strategies

### Network Optimization
- **Request Caching** - API response caching
- **Image Caching** - Image cache management
- **Offline Support** - Offline data sync
- **Background Sync** - Background data updates

## 🔄 Continuous Integration

### GitHub Actions
```yaml
name: Mobile CI/CD
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-node@v2
      - run: npm install
      - run: npm test

  build-android:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-java@v2
      - run: npm install
      - run: npm run build:android
```

## 🤝 Contributing

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Make your changes**
4. **Run tests**: `npm test`
5. **Commit changes**: `git commit -m 'Add amazing feature'`
6. **Push to branch**: `git push origin feature/amazing-feature`
7. **Open a Pull Request**

## 📝 Code Style

### ESLint Configuration
```javascript
module.exports = {
  extends: [
    '@react-native',
    'prettier',
  ],
  rules: {
    'react-hooks/exhaustive-deps': 'warn',
    'no-unused-vars': 'error',
  },
};
```

### Prettier Configuration
```json
{
  "semi": true,
  "trailingComma": "es5",
  "singleQuote": true,
  "printWidth": 80,
  "tabWidth": 2
}
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- **Email**: support@apsdreamhome.com
- **Documentation**: [docs.apsdreamhome.com](https://docs.apsdreamhome.com)
- **GitHub Issues**: [github.com/apsdreamhome/mobile/issues](https://github.com/apsdreamhome/mobile/issues)

## 🗺️ Roadmap

### Version 1.1
- [ ] Push notifications implementation
- [ ] Offline property browsing
- [ ] Advanced search filters
- [ ] Property comparison feature
- [ ] Social sharing integration

### Version 1.2
- [ ] AR property viewing
- [ ] Mortgage calculator
- [ ] Multi-language support
- [ ] Advanced analytics
- [ ] Agent rating system

### Version 2.0
- [ ] Video calling with agents
- [ ] Virtual reality tours
- [ ] AI-powered recommendations
- [ ] Blockchain property records
- [ ] Cryptocurrency payments

---

**Built with ❤️ by APS Team**

*Empowering real estate professionals with modern mobile technology*
