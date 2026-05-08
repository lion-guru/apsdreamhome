# 🚀 Flutter App Build Steps

## Step 1: Terminal Open Karein
```bash
cd c:\xampp\htdocs\apsdreamhome\apsdreamhome_app_v2
```

## Step 2: Clean Build
```bash
flutter clean
```

## Step 3: Dependencies Download Karein
```bash
flutter pub get
```

## Step 4: Android Gradle Sync (First Time)
```bash
cd android
./gradlew clean build
```

## Step 5: Build Debug APK (Test)
```bash
cd ..
flutter build apk --debug
```

## Step 6: Build Release APK (Final)
```bash
flutter build apk --release
```

## Output Location:
`build/app/outputs/flutter-apk/app-release.apk`

---

## 🔧 Common Issues:

### 1. Gradle Sync Fail
**Solution:**
```bash
cd android
./gradlew clean
./gradlew build
```

### 2. Firebase Not Connected
**Check:** `android/app/google-services.json` exist karti hai

### 3. Min SDK Error
**Already Fixed:** `minSdkVersion 21` in `build.gradle`

---

## ✅ Ready to Build!
