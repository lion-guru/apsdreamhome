import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show defaultTargetPlatform, kIsWeb, TargetPlatform;

/// Default [FirebaseOptions] for use with your Firebase apps.
///
/// Example:
/// ```dart
/// import 'firebase_options.dart';
/// // ...
/// await Firebase.initializeApp(
///   options: DefaultFirebaseOptions.currentPlatform,
/// );
/// ```
class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) {
      return web;
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return android;
      case TargetPlatform.iOS:
        return ios;
      case TargetPlatform.macOS:
        return macos;
      case TargetPlatform.windows:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for windows - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      case TargetPlatform.linux:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for linux - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      default:
        throw UnsupportedError(
          'DefaultFirebaseOptions are not supported for this platform.',
        );
    }
  }

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'SAFE_REDACTED_TOKEN',
    appId: '1:387997879764:web:8a4585e90885466af15a41',
    messagingSenderId: '387997879764',
    projectId: 'apsgroup-163d9',
    authDomain: 'apsgroup-163d9.firebaseapp.com',
    storageBucket: 'apsgroup-163d9.firebasestorage.app',
    measurementId: 'G-XXXXXXXXXX',
  );

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'SAFE_REDACTED_TOKEN',
    appId: '1:387997879764:android:8a4585e90885466af15a41',
    messagingSenderId: '387997879764',
    projectId: 'apsgroup-163d9',
    storageBucket: 'apsgroup-163d9.firebasestorage.app',
  );

  static const FirebaseOptions ios = FirebaseOptions(
    apiKey: 'SAFE_REDACTED_TOKEN',
    appId: '1:387997879764:ios:8a4585e90885466af15a41',
    messagingSenderId: '387997879764',
    projectId: 'apsgroup-163d9',
    storageBucket: 'apsgroup-163d9.firebasestorage.app',
    iosBundleId: 'com.apsdreamhomes.mobileapp',
  );

  static const FirebaseOptions macos = FirebaseOptions(
    apiKey: 'SAFE_REDACTED_TOKEN',
    appId: '1:387997879764:ios:8a4585e90885466af15a41',
    messagingSenderId: '387997879764',
    projectId: 'apsgroup-163d9',
    storageBucket: 'apsgroup-163d9.firebasestorage.app',
    iosBundleId: 'com.apsdreamhomes.mobileapp',
  );
}
