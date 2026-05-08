import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';

import '../../core/utils/logger.dart';
import '../models/user_model.dart' as user_model;
import '../../core/utils/demo_data_generator.dart';

/// Firebase Database Seeder
/// Seeds initial data for testing and demo purposes
class FirebaseSeeder {
  final FirebaseFirestore _firestore;

  FirebaseSeeder({FirebaseFirestore? firestore})
      : _firestore = firestore ?? FirebaseFirestore.instance;

  // Collection references
  CollectionReference get _colonies => _firestore.collection('colonies');
  CollectionReference get _plots => _firestore.collection('plots');
  CollectionReference get _users => _firestore.collection('users');
  CollectionReference get _leads => _firestore.collection('leads');
  CollectionReference get _propertyListings =>
      _firestore.collection('property_listings');
  CollectionReference get _emiAgents => _firestore.collection('emi_agents');
  CollectionReference get _dailyCallers =>
      _firestore.collection('daily_callers');
  CollectionReference get _emiRules => _firestore.collection('emi_rules');

  /// Seed all demo data
  Future<void> seedAllData() async {
    AppLogger.info('🌱 Starting Firebase data seeding...');

    try {
      await seedCompanyReferralCode();
      await seedAdminUser();
      await seedColonies();
      await seedUsers();
      await seedLeads();
      await seedPropertyListings();
      await seedEMIAgents();
      await seedDailyCallers();
      await seedEMIRules();

      AppLogger.info('✅ Firebase data seeding completed!');
    } catch (e, stackTrace) {
      AppLogger.error('❌ Data seeding failed', e, stackTrace);
      rethrow;
    }
  }

  /// Seed colonies with plots
  Future<void> seedColonies() async {
    AppLogger.info('🌱 Seeding colonies...');

    final colonies = DemoDataGenerator.generateDemoColonies();

    for (final colony in colonies) {
      // Check if colony already exists
      final existing = await _colonies.doc(colony.id).get();
      if (existing.exists) {
        AppLogger.info('   Colony ${colony.name} already exists, skipping...');
        continue;
      }

      // Add colony
      await _colonies.doc(colony.id).set(colony.toJson());
      AppLogger.info('   ✅ Added colony: ${colony.name}');

      // Add plots for this colony
      final plots = DemoDataGenerator.generateDemoPlots(colony.id);
      for (final plot in plots) {
        await _plots.doc(plot.id).set(plot.toJson());
      }
      AppLogger.info('   ✅ Added ${plots.length} plots for ${colony.name}');
    }
  }

  /// Seed users
  Future<void> seedUsers() async {
    AppLogger.info('🌱 Seeding users...');

    final users = DemoDataGenerator.generateDemoUsers();

    for (final user in users) {
      final existing = await _users.doc(user.userId).get();
      if (existing.exists) {
        AppLogger.info('   User ${user.email} already exists, skipping...');
        continue;
      }

      await _users.doc(user.userId).set(user.toJson());
      AppLogger.info('   ✅ Added user: ${user.name} (${user.rank})');
    }
  }

  /// Seed leads
  Future<void> seedLeads() async {
    AppLogger.info('🌱 Seeding leads...');

    final leads = DemoDataGenerator.generateDemoLeads();

    for (final lead in leads) {
      final existing = await _leads.doc(lead.id).get();
      if (existing.exists) {
        AppLogger.info('   Lead ${lead.name} already exists, skipping...');
        continue;
      }

      await _leads.doc(lead.id).set(lead.toJson());
      AppLogger.info('   ✅ Added lead: ${lead.name} (${lead.status})');
    }
  }

  /// Seed property listings (marketplace)
  Future<void> seedPropertyListings() async {
    AppLogger.info('🌱 Seeding property listings...');

    final listings = DemoDataGenerator.generateDemoPropertyListings();

    for (final listing in listings) {
      final existing = await _propertyListings.doc(listing.id).get();
      if (existing.exists) {
        AppLogger.info(
            '   Listing ${listing.title} already exists, skipping...');
        continue;
      }

      await _propertyListings.doc(listing.id).set(listing.toJson());
      AppLogger.info('   ✅ Added listing: ${listing.title}');
    }
  }

  /// Seed EMI collection agents
  Future<void> seedEMIAgents() async {
    AppLogger.info('🌱 Seeding EMI collection agents...');

    final agents = DemoDataGenerator.generateDemoEMIAgents();

    for (final agent in agents) {
      final existing = await _emiAgents.doc(agent.id).get();
      if (existing.exists) {
        AppLogger.info('   Agent ${agent.name} already exists, skipping...');
        continue;
      }

      await _emiAgents.doc(agent.id).set(agent.toJson());
      AppLogger.info('   ✅ Added EMI agent: ${agent.name}');
    }
  }

  /// Seed daily callers (telecallers)
  Future<void> seedDailyCallers() async {
    AppLogger.info('🌱 Seeding daily callers...');

    final callers = DemoDataGenerator.generateDemoDailyCallers();

    for (final caller in callers) {
      final existing = await _dailyCallers.doc(caller.id).get();
      if (existing.exists) {
        AppLogger.info('   Caller ${caller.name} already exists, skipping...');
        continue;
      }

      await _dailyCallers.doc(caller.id).set(caller.toJson());
      AppLogger.info('   ✅ Added caller: ${caller.name}');
    }
  }

  /// Seed EMI automation rules
  Future<void> seedEMIRules() async {
    AppLogger.info('🌱 Seeding EMI reminder rules...');

    final rules = DemoDataGenerator.generateDemoEMIRules();

    for (final rule in rules) {
      final existing = await _emiRules.doc(rule.id).get();
      if (existing.exists) {
        AppLogger.info('   Rule ${rule.name} already exists, skipping...');
        continue;
      }

      await _emiRules.doc(rule.id).set(rule.toJson());
      AppLogger.info('   ✅ Added EMI rule: ${rule.name}');
    }
  }

  /// Clear all data (use with caution!)
  Future<void> clearAllData() async {
    AppLogger.warning('⚠️ Clearing all demo data...');

    final collections = [
      _colonies,
      _plots,
      _users,
      _leads,
      _propertyListings,
      _emiAgents,
      _dailyCallers,
      _emiRules,
    ];

    for (final collection in collections) {
      final snapshots = await collection.get();
      for (final doc in snapshots.docs) {
        await doc.reference.delete();
      }
    }

    AppLogger.info('✅ All demo data cleared!');
  }

  /// Seed specific data only
  Future<void> seedSpecific({
    bool colonies = false,
    bool users = false,
    bool leads = false,
    bool propertyListings = false,
    bool emiAgents = false,
    bool dailyCallers = false,
    bool emiRules = false,
  }) async {
    AppLogger.info('🌱 Seeding specific data...');

    if (colonies) await seedColonies();
    if (users) await seedUsers();
    if (leads) await seedLeads();
    if (propertyListings) await seedPropertyListings();
    if (emiAgents) await seedEMIAgents();
    if (dailyCallers) await seedDailyCallers();
    if (emiRules) await seedEMIRules();

    AppLogger.info('✅ Specific data seeding completed!');
  }

  /// Seed company referral code
  Future<void> seedCompanyReferralCode() async {
    AppLogger.info('🌱 Seeding company referral code...');

    try {
      final companyReferralDoc = await _users.doc('COMPANY_REFERRAL').get();

      if (!companyReferralDoc.exists) {
        await _users.doc('COMPANY_REFERRAL').set({
          'referralCode': 'APSCOMP',
          'companyName': 'APS Dream Home',
          'isCompanyCode': true,
          'createdAt': DateTime.now().toIso8601String(),
        });
        AppLogger.info('✅ Company referral code created: APSCOMP');
      } else {
        AppLogger.info('ℹ️ Company referral code already exists');
      }
    } catch (e, stackTrace) {
      AppLogger.error('❌ Failed to seed company referral code', e, stackTrace);
    }
  }

  /// Seed admin user
  Future<void> seedAdminUser() async {
    AppLogger.info('🌱 Seeding admin user...');

    try {
      // Import Firebase Auth
      final auth = FirebaseAuth.instance;

      // Check if Firebase Auth user exists
      try {
        await auth.signInWithEmailAndPassword(
          email: 'admin@apsdreamhome.com',
          password: 'Aps@12345',
        );
        await auth.signOut();
        AppLogger.info('ℹ️ Admin Firebase Auth user already exists');
      } catch (e) {
        // Create Firebase Auth user
        await auth.createUserWithEmailAndPassword(
          email: 'admin@apsdreamhome.com',
          password: 'Aps@12345',
        );
        AppLogger.info(
            '✅ Admin Firebase Auth user created with password: Aps@12345');
      }

      // Check Firestore document
      final adminDoc = await _users
          .where('email', isEqualTo: 'admin@apsdreamhome.com')
          .limit(1)
          .get();

      if (adminDoc.docs.isEmpty) {
        // Get Firebase Auth user UID
        final firebaseUser = auth.currentUser;
        if (firebaseUser == null) {
          throw Exception('Firebase Auth user not found');
        }

        final adminUser = user_model.User(
          userId: firebaseUser.uid,
          name: 'APS Admin',
          email: 'admin@apsdreamhome.com',
          phone: '9999999999',
          rank: 'admin',
          target: 0.0,
          createdAt: DateTime.now().toIso8601String(),
          updatedAt: DateTime.now().toIso8601String(),
        );

        final adminData = adminUser.toJson();
        adminData['referralCode'] = 'APSCOMP';
        adminData['role'] = 'admin';
        adminData['status'] = 'active';
        adminData['isEmailVerified'] = true;
        adminData['isPhoneVerified'] = true;
        adminData['lastLoginAt'] = DateTime.now().toIso8601String();
        adminData['preferences'] = {
          'canCreateUsers': true,
          'canCreateRoles': true,
          'canViewAllData': true,
        };

        await _users.doc(firebaseUser.uid).set(adminData);
        AppLogger.info(
            '✅ Admin Firestore document created: admin@apsdreamhome.com');
      } else {
        AppLogger.info('ℹ️ Admin Firestore document already exists');
      }

      AppLogger.info(
          '✅ Admin user seeding completed. Login with: admin@apsdreamhome.com / Aps@12345');
    } catch (e, stackTrace) {
      AppLogger.error('❌ Failed to seed admin user', e, stackTrace);
    }
  }
}
