import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;

class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    return android;
  }

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'SAFE_REDACTED_TOKEN',
    appId: '1:387997879764:android:8a4585e90885466af15a41',
    messagingSenderId: '387997879764',
    projectId: 'apsgroup-163d9',
    storageBucket: 'apsgroup-163d9.firebasestorage.app',
  );
}
