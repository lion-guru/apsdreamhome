import 'package:firebase_core/firebase_core.dart';
import 'data/services/firebase_seeder.dart';
import 'firebase_options.dart';

/// Firebase Seeder Runner
/// Run this script to seed initial data including Firebase Auth users
/// Usage: dart run lib/seed_runner.dart
Future<void> main() async {
  print('🌱 Starting Firebase Seeder...');
  
  try {
    // Initialize Firebase
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
    print('✅ Firebase initialized');
    
    // Create seeder instance
    final seeder = FirebaseSeeder();
    
    // Seed all data
    await seeder.seedAllData();
    
    print('✅ Seeding completed successfully!');
    print('📝 Admin credentials:');
    print('   Email: admin@apsdreamhome.com');
    print('   Password: Aps@12345');
    print('   Company Referral Code: APSCOMP');
    
  } catch (e, stackTrace) {
    print('❌ Seeding failed: $e');
    print('Stack trace: $stackTrace');
  }
}
