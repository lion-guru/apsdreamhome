import 'dart:developer' as developer;
import 'package:flutter/services.dart';

import '../router/app_router.dart';

/// Deep Linking Service
/// Receives `apsdreamhome://...` links from the Android launch intent or a
/// warm (onNewIntent) tap and routes them into the GoRouter.
///
/// Native side lives in MainActivity.kt on the channel `apsdreamhome/deeplinks`:
///   - `getInitialLink` -> String? (the URL that cold-started the app)
///   - `onLink`         -> method channel event with String arguments
///
/// Links are published by the APS Dream Home ecosystem, e.g.:
///   apsdreamhome://property/123      -> /property-detail/123
///   apsdreamhome://colony/3          -> /colony-detail/3
///   apsdreamhome://colony/3/plots    -> /colony-plots/3
///   apsdreamhome://booking/55        -> /booking/55
///   apsdreamhome://offers            -> /offers
///   apsdreamhome://offers?promo=CODE -> /booking/... not linked to a plot -> /offers
class DeepLinkService {
  static final DeepLinkService _instance = DeepLinkService._internal();
  factory DeepLinkService() => _instance;
  DeepLinkService._internal();

  static const MethodChannel _channel = MethodChannel('apsdreamhome/deeplinks');

  bool _initialized = false;

  /// Initialize deep link handling. Safe to call exactly once at app start.
  Future<void> initialize() async {
    if (_initialized) return;
    _initialized = true;
    try {
      _channel.setMethodCallHandler(_handleNativeCall);
      final initial = await _channel.invokeMethod<String>('getInitialLink');
      if (initial != null && initial.isNotEmpty) {
        await _route(initial);
      }
    } catch (e) {
      developer.log('Deep link init failed: $e', name: 'DeepLinkService');
    }
  }

  Future<dynamic> _handleNativeCall(MethodCall call) async {
    if (call.method == 'onLink' && call.arguments is String) {
      await _route(call.arguments as String);
    }
    return null;
  }

  Future<void> _route(String link) async {
    final route = parseRoute(link);
    if (route == null) {
      developer.log('Unmapped deep link: $link', name: 'DeepLinkService');
      return;
    }
    developer.log('Deep link $link -> $route', name: 'DeepLinkService');
    try {
      getRouter().go(route);
    } catch (e) {
      developer.log('Deep link navigation failed: $e', name: 'DeepLinkService');
    }
  }

  // Supported link forms:
  //   apsdreamhome://property/123          -> /property-detail/123
  //   https://<host>/verify/{type}/{ref}   -> /public-verify/{type}/{ref}
  //
  // Manifest tunnels both schemes into this parser (sibling added the
  // android:autoVerify="true" https filter on <activity>).
  static String? parseRoute(String link) {
    final uri = Uri.tryParse(link);
    if (uri == null) return null;

    final isAppScheme = uri.scheme == 'apsdreamhome';
    final isHttps = uri.scheme == 'https';
    if (!isAppScheme && !isHttps) return null;

    // For HTTPS links, only accept links hosted by an APS Dream Home domain.
    if (isHttps) {
      final host = uri.host.toLowerCase();
      final trusted = host == 'apsdreamhome.com'
          || host == 'www.apsdreamhome.com'
          || host.endsWith('.apsdreamhome.com');
      if (!trusted) return null;
      final segs = uri.pathSegments.where((s) => s.isNotEmpty).toList();
      if (segs.length >= 3 && segs[0] == 'verify') {
        return '/public-verify/${segs[1]}/${segs[2]}';
      }
      if (segs.length >= 2 && segs[0] == 'colony-plots') {
        final digits = RegExp(r'\d+').stringMatch(segs[1]) ?? segs[1];
        return '/colony-plots/$digits';
      }
      if (segs.length >= 2 && segs[0] == 'property') {
        final digits = RegExp(r'\d+').stringMatch(segs[1]) ?? segs[1];
        return '/property-detail/$digits';
      }
      return null;
    }

    final host = uri.host.toLowerCase();
    final segs = [
      if (host.isNotEmpty) host,
      ...uri.pathSegments.where((s) => s.isNotEmpty),
    ];
    if (segs.isEmpty) return null;

    String digits(String s) => RegExp(r'\d+').stringMatch(s) ?? s;

    switch (segs[0]) {
      case 'property':
      case 'property-detail':
        if (segs.length >= 2 && segs[1].trim().isNotEmpty) {
          return '/property-detail/${digits(segs[1])}';
        }
        return null;
      case 'colony':
      case 'colony-detail':
        if (segs.length >= 3 && segs[2] == 'plots') {
          return '/colony-plots/${digits(segs[1])}';
        }
        if (segs.length >= 2 && segs[1].trim().isNotEmpty) {
          return '/colony-detail/${digits(segs[1])}';
        }
        return null;
      case 'colony-plots':
        if (segs.length >= 2 && segs[1].trim().isNotEmpty) {
          return '/colony-plots/${digits(segs[1])}';
        }
        return null;
      case 'booking':
        if (segs.length >= 2 && segs[1].trim().isNotEmpty) {
          return '/booking/${segs[1]}';
        }
        return null;
      case 'offers':
        return '/offers';
      case 'my-bookings':
        return '/my-bookings';
      case 'home':
        return '/home';
      default:
        return null;
    }
  }
}
