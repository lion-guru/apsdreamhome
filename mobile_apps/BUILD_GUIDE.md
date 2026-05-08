# APS Dream Home - Mobile App Build Guide

## Quick Start

### 1. Start XAMPP Server
```
Start Apache (Port 80)
Start MySQL (Port 3307)
```

### 2. Run Flutter App

```bash
# Navigate to project
cd c:\xampp\htdocs\apsdreamhome\mobile_apps

# Get dependencies
flutter pub get

# Run in debug mode
flutter run

# Or build APK
flutter build apk --release
```

### 3. Test Credentials

| Role | Username | Password |
|------|----------|----------|
| Customer | user@apsdreamhome.com | user123 |
| Associate | associate@apsdreamhome.com | associate123 |
| Admin | admin@apsdreamhome.com | admin123 |

## API Configuration

Already set for **Android Emulator** (`10.0.2.2`)

### For Physical Device:
```dart
// Edit: lib/core/constants/app_constants.dart
static const String baseUrl = 'http://YOUR_IP/apsdreamhome';
```

Get your IP:
```cmd
ipconfig
```

## Features to Test

### Customer Flow:
1. Login → Property List → View Details → Save Favorite

### Associate Flow:
1. Login → MLM Dashboard → View Genealogy → Check Commission

### Agent Flow:
1. Login → Lead CRM → Add Lead → Schedule Visit

## Troubleshooting

### API Not Connecting:
```
1. Check XAMPP is running
2. Check baseUrl in app_constants.dart
3. Check firewall (allow port 80)
4. Try: http://10.0.2.2/apsdreamhome in browser
```

### Build Errors:
```bash
flutter clean
flutter pub get
flutter run
```

## Build Release APK

```bash
cd c:\xampp\htdocs\apsdreamhome\mobile_apps
flutter build apk --release
```

**Output:** `build/app/outputs/flutter-apk/app-release.apk`

## Next Steps After Testing

1. ✅ All features working → Build release APK
2. ❌ Issues found → Fix and retest
3. 🚀 Ready for Phase 2 → Firebase version
