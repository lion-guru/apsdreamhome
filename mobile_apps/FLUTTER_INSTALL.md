# Flutter Installation Guide (Windows)

## Step 1: Download Flutter SDK

1. Go to: https://docs.flutter.dev/get-started/install/windows
2. Download: `flutter_windows_3.x.x-stable.zip`
3. Extract to: `C:\flutter`

## Step 2: Add to PATH

1. Windows Search: "Environment Variables"
2. Click: "Edit the system environment variables"
3. Click: "Environment Variables"
4. Under "User Variables", find "Path"
5. Click: "Edit"
6. Click: "New"
7. Add: `C:\flutter\bin`
8. Click: "OK" (3 times)

## Step 3: Verify Installation

Open new CMD/PowerShell:
```bash
flutter doctor
```

Expected output:
```
[✓] Flutter (Channel stable, 3.x.x)
[✓] Android toolchain
[✓] Android Studio (optional)
```

## Step 4: Accept Android Licenses

```bash
flutter doctor --android-licenses
```
Press `y` for all prompts

## Step 5: Install Android SDK (if needed)

1. Download Android Studio: https://developer.android.com/studio
2. Install with "Android SDK"
3. Set ANDROID_HOME environment variable:
   - Path: `C:\Users\<username>\AppData\Local\Android\Sdk`

## Step 6: Run App

```bash
cd c:\xampp\htdocs\apsdreamhome\mobile_apps
flutter pub get
flutter run --release
```

## Troubleshooting

### Error: "flutter not recognized"
→ PATH mein `C:\flutter\bin` add karein, then restart CMD

### Error: "Android SDK not found"
→ Android Studio install karein ya `flutter doctor` se instructions follow karein

### Error: "No devices found"
→ USB Debugging ON karein mobile mein
→ USB cable se connect karein
→ `flutter devices` check karein
