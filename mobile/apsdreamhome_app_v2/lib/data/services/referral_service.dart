import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:share_plus/share_plus.dart';
import '../../core/services/api_service.dart';
import '../repositories/kyc_repository_provider.dart';

class ReferralService {
  final ApiService _api;

  ReferralService(this._api);

  /// Generate a referral link for a property
  String generateReferralLink({
    required String referralCode,
    int? propertyId,
  }) {
    const base = 'https://apsdreamhome.com/property';
    final params = StringBuffer('?ref=$referralCode');
    if (propertyId != null) params.write('&id=$propertyId');
    return '$base$params';
  }

  /// Generate share message for WhatsApp
  String generateShareMessage({
    required String propertyName,
    required String referralCode,
    int? propertyId,
    String? price,
    String? location,
  }) {
    final link = generateReferralLink(
        referralCode: referralCode, propertyId: propertyId);
    final buffer = StringBuffer();
    buffer.writeln('Check out this amazing property at APS Dream Home!');
    buffer.writeln('');
    buffer.writeln('Property: $propertyName');
    if (price != null) buffer.writeln('Price: $price');
    if (location != null) buffer.writeln('Location: $location');
    buffer.writeln('');
    buffer.writeln('Use my referral link for exclusive benefits:');
    buffer.writeln(link);
    buffer.writeln('');
    buffer.writeln('APS Dream Home - Your Dream Property Awaits!');
    return buffer.toString();
  }

  /// Share via WhatsApp
  Future<bool> shareViaWhatsApp({
    required String propertyName,
    required String referralCode,
    int? propertyId,
    String? price,
    String? location,
  }) async {
    try {
      final message = generateShareMessage(
        propertyName: propertyName,
        referralCode: referralCode,
        propertyId: propertyId,
        price: price,
        location: location,
      );

      await Share.share(message);

      // Track referral on backend
      await _trackReferral(referralCode, propertyId);
      return true;
    } catch (e) {
      print('Error sharing via WhatsApp: $e');
      return false;
    }
  }

  /// Track referral on backend
  Future<void> _trackReferral(String referralCode, int? propertyId) async {
    try {
      await _api.post('/referral/track', data: {
        'referral_code': referralCode,
        'property_id': propertyId,
        'source': 'whatsapp',
      });
    } catch (e) {
      print('Error tracking referral: $e');
    }
  }

  /// Get user's referral code from profile
  Future<String?> getReferralCode() async {
    try {
      final profile = await _api.get('/user/profile');
      return (profile['data'] as Map<String, dynamic>?)?['referral_code'] as String?;
    } catch (e) {
      print('Error getting referral code: $e');
      return null;
    }
  }
}

final referralServiceProvider = Provider<ReferralService>((ref) {
  return ReferralService(ref.watch(apiServiceProvider));
});
