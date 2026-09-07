import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:http/http.dart' as http;
import '../../core/constants/app_constants.dart';

class GoogleAuthService {
  // Web client ID from Google Cloud Console (for server-side ID token verification)
  static const String _webClientId = '387997879764-rk0cvpfqcf3m6nm20n93cpc4gprht2o3.apps.googleusercontent.com';
  
  static final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: ['email', 'profile'],
    serverClientId: _webClientId,
  );

  static Future<Map<String, dynamic>?> signInWithGoogle() async {
    try {
      final GoogleSignInAccount? account = await _googleSignIn.signIn();
      if (account == null) return null;

      final GoogleSignInAuthentication auth = await account.authentication;

      final response = await http.post(
        Uri.parse(
          '${AppConstants.baseUrl}${AppConstants.apiVersion}${AppConstants.googleLoginEndpoint}',
        ),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'id_token': auth.idToken ?? '',
          'email': account.email,
          'name': account.displayName ?? '',
          'photo_url': account.photoUrl ?? '',
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          return data['data'] as Map<String, dynamic>;
        }
      }
      return null;
    } catch (e) {
      debugPrint('Google Sign-In error: $e');
      return null;
    }
  }

  static Future<void> signOut() async {
    await _googleSignIn.signOut();
  }
}
