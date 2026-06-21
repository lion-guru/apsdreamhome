import 'dart:async';
import 'dart:developer' as developer;
// app_links removed — AGP 8.7 incompatibility; re-enable when building for release
// import 'package:app_links/app_links.dart';

/// Deep Linking Service
/// Handles app links from external sources
/// NOTE: app_links dependency removed temporarily. Re-add when AGP 8.7+ is compatible.
class DeepLinkService {
  static final DeepLinkService _instance = DeepLinkService._internal();
  factory DeepLinkService() => _instance;
  DeepLinkService._internal();

  StreamSubscription<String>? _linkSubscription;

  /// Initialize deep link handling
  Future<void> initialize({
    required Function(DeepLinkData) onLinkReceived,
  }) async {
    developer.log('Deep link service initialized (stub mode)', name: 'DeepLinkService');
  }

  /// Generate shareable link
  String generateShareLink({
    required DeepLinkType type,
    required String id,
    Map<String, String>? parameters,
  }) {
    final buffer = StringBuffer();
    buffer.write('https://apsdreamhome.com');

    switch (type) {
      case DeepLinkType.property:
        buffer.write('/property/$id');
        break;
      case DeepLinkType.colony:
        buffer.write('/colony/$id');
        break;
      case DeepLinkType.referral:
        buffer.write('/invite?code=$id');
        break;
      case DeepLinkType.payment:
        buffer.write('/payment?order_id=$id');
        break;
      case DeepLinkType.lead:
        buffer.write('/lead/$id');
        break;
      default:
        buffer.write('/');
    }

    if (parameters != null && parameters.isNotEmpty) {
      final queryString = parameters.entries
          .map((e) => '${Uri.encodeComponent(e.key)}=${Uri.encodeComponent(e.value)}')
          .join('&');
      buffer.write('?$queryString');
    }

    return buffer.toString();
  }

  void dispose() {
    _linkSubscription?.cancel();
  }
}

enum DeepLinkType { property, colony, referral, payment, lead, unknown }

class DeepLinkData {
  final DeepLinkType type;
  final String id;
  final Map<String, String> parameters;
  final String? rawUrl;

  DeepLinkData({required this.type, required this.id, required this.parameters, this.rawUrl});

  @override
  String toString() => 'DeepLinkData(type: $type, id: $id, params: $parameters)';
}
