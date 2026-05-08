/// PhonePe Payment SDK Stub
/// This is a stub implementation since the actual PhonePe SDK is not available
/// In production, add the real phonepe_payment_sdk package to pubspec.yaml
library;

enum Environment { production, sandbox }

class PhonePePaymentSdk {
  static Future<void> init({
    required Environment environment,
    String? appId,
    required String merchantId,
    bool enableLogging = false,
  }) async {
    // Stub implementation
    return;
  }

  static Future<Map<String, dynamic>> startTransaction({
    required String body,
    required String checksum,
    required String apiEndPoint,
    Map<String, String>? headers,
  }) async {
    // Stub implementation - returns success
    return {
      'status': 'SUCCESS',
      'transactionId': 'stub_${DateTime.now().millisecondsSinceEpoch}',
    };
  }

  static Future<bool> isPhonePeInstalled() async {
    return false;
  }

  static Future<bool> isPaytmInstalled() async {
    return false;
  }

  static Future<bool> isGpayInstalled() async {
    return false;
  }

  static Future<List<Map<String, dynamic>>> getInstalledUpiApps() async {
    return [];
  }
}
