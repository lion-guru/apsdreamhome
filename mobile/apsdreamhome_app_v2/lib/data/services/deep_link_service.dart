import 'dart:async';
import 'package:firebase_dynamic_links/firebase_dynamic_links.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/utils/logger.dart';

/// Deep Linking Service
/// Smart Referral Links with Firebase Dynamic Links
/// Auto-maps customers to associate downline without referral code
class DeepLinkService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FirebaseDynamicLinks _dynamicLinks = FirebaseDynamicLinks.instance;

  StreamSubscription<PendingDynamicLinkData>? _linkSubscription;

  // Collection references
  CollectionReference get _referralLinks =>
      _firestore.collection('referral_links');
  CollectionReference get _linkClicks => _firestore.collection('link_clicks');
  CollectionReference get _users => _firestore.collection('users');

  /// Initialize deep link listeners
  void initDeepLinkListener(Function(Map<String, dynamic>) onLinkReceived) {
    // Listen for link clicks when app is in background
    _linkSubscription = _dynamicLinks.onLink.listen(
      (PendingDynamicLinkData dynamicLinkData) {
        _handleDynamicLink(dynamicLinkData.link, onLinkReceived);
      },
      onError: (error) {
        AppLogger.error('Deep link error', error);
      },
    );

    // Check for pending link when app is opened from terminated state
    _dynamicLinks.getInitialLink().then((dynamicLinkData) {
      if (dynamicLinkData != null) {
        _handleDynamicLink(dynamicLinkData.link, onLinkReceived);
      }
    });
  }

  /// Handle incoming dynamic link
  Future<void> _handleDynamicLink(
    Uri? deepLink,
    Function(Map<String, dynamic>) onLinkReceived,
  ) async {
    if (deepLink == null) return;

    AppLogger.info('Received deep link: ${deepLink.toString()}');

    // Parse link parameters
    final associateId = deepLink.queryParameters['ref'];
    final plotId = deepLink.queryParameters['plot'];
    final colonyId = deepLink.queryParameters['colony'];
    final linkId = deepLink.queryParameters['linkId'];

    if (associateId == null) {
      AppLogger.warning('No associate ID in deep link');
      return;
    }

    // Record the click
    if (linkId != null) {
      await _recordLinkClick(linkId, deepLink);
    }

    // Get associate details
    final associateDoc = await _users.doc(associateId).get();
    final associateData = associateDoc.data() as Map<String, dynamic>?;

    // Prepare navigation data
    final linkData = {
      'associateId': associateId,
      'associateName': associateData?['name'] ?? 'Unknown',
      'plotId': plotId,
      'colonyId': colonyId,
      'deepLink': deepLink.toString(),
      'route': deepLink.path,
    };

    // Notify the app
    onLinkReceived(linkData);

    AppLogger.info('Deep link processed: ${associateData?['name']} referral');
  }

  /// Create Smart Referral Link
  Future<Map<String, dynamic>> createReferralLink({
    required String associateId,
    String? associateName,
    String? plotId,
    String? colonyId,
    String? plotNumber,
    String? colonyName,
    String? customMessage,
  }) async {
    try {
      // Create unique link ID
      final linkId = 'REF${DateTime.now().millisecondsSinceEpoch}';

      // Build dynamic link parameters
      final dynamicLinkParams = DynamicLinkParameters(
        uriPrefix: 'https://apsdreamhome.page.link',
        link: Uri.parse(
          'https://apsdreamhome.com/referral?'
          'ref=$associateId'
          '${plotId != null ? '&plot=$plotId' : ''}'
          '${colonyId != null ? '&colony=$colonyId' : ''}'
          '&linkId=$linkId',
        ),
        androidParameters: AndroidParameters(
          packageName: 'com.apsdreamhomes.mobileapp',
          minimumVersion: 1,
          fallbackUrl: Uri.parse('https://apsdreamhome.com/install'),
        ),
        iosParameters: const IOSParameters(
          bundleId: 'com.apsdreamhomes.mobileapp',
          minimumVersion: '1.0.0',
          appStoreId: '1234567890', // Replace with actual App Store ID
        ),
        socialMetaTagParameters: SocialMetaTagParameters(
          title: plotNumber != null
              ? 'Plot $plotNumber at $colonyName - APS Dream Home'
              : 'Join APS Dream Home - Real Estate Opportunity',
          description: customMessage ??
              'Check out this amazing property opportunity with ${associateName ?? 'APS Dream Home'}!',
          imageUrl: Uri.parse(
            'https://apsdreamhome.com/assets/images/referral-banner.jpg',
          ),
        ),
        navigationInfoParameters: const NavigationInfoParameters(
          forcedRedirectEnabled: true,
        ),
      );

      // Generate short link
      final shortLink = await _dynamicLinks.buildShortLink(
        dynamicLinkParams,
        shortLinkType: ShortDynamicLinkType.unguessable,
      );

      // Save link to Firestore
      await _referralLinks.doc(linkId).set({
        'id': linkId,
        'associateId': associateId,
        'associateName': associateName,
        'plotId': plotId,
        'colonyId': colonyId,
        'plotNumber': plotNumber,
        'colonyName': colonyName,
        'shortUrl': shortLink.shortUrl.toString(),
        'previewLink': shortLink.previewLink.toString(),
        'createdAt': DateTime.now(),
        'clicks': 0,
        'installs': 0,
        'registrations': 0,
        'isActive': true,
      });

      // Update associate's referral stats
      await _users.doc(associateId).update({
        'totalReferralLinksGenerated': FieldValue.increment(1),
      });

      return {
        'success': true,
        'linkId': linkId,
        'shortUrl': shortLink.shortUrl.toString(),
        'shareMessage': _generateShareMessage(
          associateName: associateName,
          plotNumber: plotNumber,
          colonyName: colonyName,
          shortUrl: shortLink.shortUrl.toString(),
          customMessage: customMessage,
        ),
      };
    } catch (e) {
      AppLogger.error('Error creating referral link', e);
      return {'success': false, 'error': e.toString()};
    }
  }

  /// Record link click analytics
  Future<void> _recordLinkClick(String linkId, Uri deepLink) async {
    try {
      await _linkClicks.add({
        'linkId': linkId,
        'timestamp': DateTime.now(),
        'source': deepLink.queryParameters['utm_source'] ?? 'direct',
        'medium': deepLink.queryParameters['utm_medium'] ?? 'referral',
        'campaign': deepLink.queryParameters['utm_campaign'],
        'deviceInfo': await _getDeviceInfo(),
      });

      // Increment click count
      await _referralLinks.doc(linkId).update({
        'clicks': FieldValue.increment(1),
      });
    } catch (e) {
      AppLogger.error('Error recording link click', e);
    }
  }

  /// Track app install from referral
  Future<void> trackReferralInstall(String linkId, String newUserId) async {
    try {
      final linkDoc = await _referralLinks.doc(linkId).get();
      if (!linkDoc.exists) return;

      final linkData = linkDoc.data() as Map<String, dynamic>;
      final associateId = linkData['associateId'] as String?;

      // Update link stats
      await _referralLinks.doc(linkId).update({
        'installs': FieldValue.increment(1),
        'registeredUserIds': FieldValue.arrayUnion([newUserId]),
      });

      // If associate exists, add to their downline
      if (associateId != null) {
        await _addToAssociateDownline(associateId, newUserId);
      }

      AppLogger.info('Referral install tracked: $newUserId from $associateId');
    } catch (e) {
      AppLogger.error('Error tracking referral install', e);
    }
  }

  /// Add customer to associate's downline
  Future<void> _addToAssociateDownline(
      String associateId, String customerId) async {
    try {
      // Update customer's referrer
      await _users.doc(customerId).update({
        'referredBy': associateId,
        'referralSource': 'deep_link',
        'referredAt': DateTime.now(),
      });

      // Update associate's downline
      await _users.doc(associateId).update({
        'downlineIds': FieldValue.arrayUnion([customerId]),
        'totalReferrals': FieldValue.increment(1),
      });

      // Create referral record for commission tracking
      await _firestore.collection('referrals').add({
        'associateId': associateId,
        'customerId': customerId,
        'status': 'registered',
        'referredAt': DateTime.now(),
        'convertedAt': null,
        'commissionPaid': false,
      });
    } catch (e) {
      AppLogger.error('Error adding to downline', e);
    }
  }

  /// Generate WhatsApp share message
  String _generateShareMessage({
    String? associateName,
    String? plotNumber,
    String? colonyName,
    required String shortUrl,
    String? customMessage,
  }) {
    if (customMessage != null && customMessage.isNotEmpty) {
      return '$customMessage\n\n$shortUrl';
    }

    if (plotNumber != null && colonyName != null) {
      return '🏠 Amazing Property Opportunity!\n\n'
          'Plot $plotNumber at $colonyName\n'
          'Premium location with great amenities\n\n'
          'Check it out here: $shortUrl\n\n'
          'Contact ${associateName ?? 'us'} for details!';
    }

    return '🏠 Join APS Dream Home!\n\n'
        'Start your real estate journey with us.\n'
        'Great properties, great commissions!\n\n'
        'Download our app: $shortUrl';
  }

  /// Get device info for analytics
  Future<Map<String, dynamic>> _getDeviceInfo() async {
    // This would integrate with device_info_plus package
    return {
      'platform': 'mobile',
      'timestamp': DateTime.now().toIso8601String(),
    };
  }

  /// Get link analytics
  Future<Map<String, dynamic>> getLinkAnalytics(String linkId) async {
    try {
      final linkDoc = await _referralLinks.doc(linkId).get();
      if (!linkDoc.exists) {
        return {'success': false, 'error': 'Link not found'};
      }

      final linkData = linkDoc.data() as Map<String, dynamic>;

      // Get click details
      final clicksSnapshot = await _linkClicks
          .where('linkId', isEqualTo: linkId)
          .orderBy('timestamp', descending: true)
          .limit(50)
          .get();

      final clicks = (linkData['clicks'] as num?)?.toInt() ?? 0;
      final registrations = (linkData['registrations'] as num?)?.toInt() ?? 0;

      final conversionRate = clicks > 0
          ? (registrations / clicks * 100).toStringAsFixed(2)
          : '0.00';

      return {
        'success': true,
        'linkData': linkData,
        'recentClicks': clicksSnapshot.docs.map((d) => d.data()).toList(),
        'conversionRate': '$conversionRate%',
      };
    } catch (e) {
      return {'success': false, 'error': e.toString()};
    }
  }

  /// Get all links for associate
  Future<List<Map<String, dynamic>>> getAssociateLinks(
      String associateId) async {
    final snapshot = await _referralLinks
        .where('associateId', isEqualTo: associateId)
        .orderBy('createdAt', descending: true)
        .get();

    return snapshot.docs
        .map((doc) => doc.data() as Map<String, dynamic>)
        .toList();
  }

  /// Dispose
  void dispose() {
    _linkSubscription?.cancel();
  }
}

// Provider
final deepLinkServiceProvider =
    Provider<DeepLinkService>((ref) => DeepLinkService());
