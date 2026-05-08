import 'dart:convert';
import 'dart:developer' as developer;
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
// import 'package:upi_india/upi_india.dart';  // Disabled - dependency issue

// Import the common UpiApp class to avoid conflicts
import '../../presentation/providers/payment_provider.dart';

class UpiPaymentStatus {
  final String status;
  final String message;
  final String? transactionRefId;

  UpiPaymentStatus({
    required this.status,
    required this.message,
    this.transactionRefId,
  });
}

class UpiIndia {
  Future<List<UpiApp>> getAllUpiApps(
      {bool mandatoryTransactionId = true}) async {
    // Return stub UPI apps
    return [
      UpiApp(
          name: 'Google Pay',
          packageName: 'com.google.android.apps.nbu.paisa.user',
          icon: ''),
      UpiApp(name: 'PhonePe', packageName: 'com.phonepe.app', icon: ''),
      UpiApp(name: 'Paytm', packageName: 'net.one97.paytm', icon: ''),
    ];
  }

  Future<UpiPaymentStatus> startTransaction({
    required UpiApp app,
    required String receiverUpiId,
    required String receiverName,
    required double amount,
    required String transactionNote,
    String? transactionRefId,
  }) async {
    // Simulate transaction
    await Future.delayed(const Duration(seconds: 2));

    return UpiPaymentStatus(
      status: 'success',
      message: 'Transaction completed successfully',
    );
  }
}

/// UPI Payment Service for GPay, PhonePe, Paytm integration
/// Stub implementation due to dependency issues
class UpiPaymentService {
  static final UpiPaymentService _instance = UpiPaymentService._internal();
  factory UpiPaymentService() => _instance;
  UpiPaymentService._internal();

  final UpiIndia _upiIndia = UpiIndia();

  // Backend API endpoint
  final String _baseUrl = 'https://apsdreamhome.com/api/v1';

  /// Initialize UPI payment service
  Future<void> initialize() async {
    // Check for available UPI apps
    final apps = await _upiIndia.getAllUpiApps(mandatoryTransactionId: false);
    developer.log('Available UPI apps: ${apps.length}',
        name: 'UpiPaymentService');
  }

  /// Get list of installed UPI apps
  Future<List<UpiApp>> getAvailableUpiApps() async {
    try {
      return await _upiIndia.getAllUpiApps(mandatoryTransactionId: false);
    } catch (e) {
      developer.log('Error getting UPI apps: $e', name: 'UpiPaymentService');
      return [];
    }
  }

  /// Initiate UPI payment
  /// Returns transaction response
  Future<UpiPaymentStatus> initiatePayment({
    required UpiApp app,
    required String receiverUpiId,
    required String receiverName,
    required double amount,
    required String transactionNote,
    String? transactionRefId,
  }) async {
    try {
      final response = await _upiIndia.startTransaction(
        app: app,
        receiverUpiId: receiverUpiId,
        receiverName: receiverName,
        transactionRefId:
            transactionRefId ?? 'APS${DateTime.now().millisecondsSinceEpoch}',
        transactionNote: transactionNote,
        amount: amount,
      );

      developer.log(
        'UPI Payment Response: ${response.status} - ${response.transactionRefId}',
        name: 'UpiPaymentService',
      );

      return response;
    } on PlatformException catch (e) {
      developer.log('Platform Exception: ${e.message}',
          name: 'UpiPaymentService');
      rethrow;
    } catch (e) {
      developer.log('UPI Payment Error: $e', name: 'UpiPaymentService');
      rethrow;
    }
  }

  /// Create payment order on backend
  /// Returns order details including transaction ID
  Future<Map<String, dynamic>> createOrder({
    required String userId,
    required String userType,
    required double amount,
    required String entityType,
    required int entityId,
    required String description,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/payments/create-order'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'user_type': userType,
          'amount': amount,
          'currency': 'INR',
          'entity_type': entityType,
          'entity_id': entityId,
          'description': description,
          'gateway': 'upi',
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        return data;
      } else {
        throw Exception('Failed to create order: ${response.body}');
      }
    } catch (e) {
      developer.log('Create order error: $e', name: 'UpiPaymentService');
      rethrow;
    }
  }

  /// Verify payment with backend
  /// Confirms transaction status
  Future<bool> verifyPayment({
    required String orderId,
    required String transactionRefId,
    required String status,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/payments/verify'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'order_id': orderId,
          'transaction_ref_id': transactionRefId,
          'status': status,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['success'] == true;
      }
      return false;
    } catch (e) {
      developer.log('Verify payment error: $e', name: 'UpiPaymentService');
      return false;
    }
  }

  /// Get payment history for user
  Future<List<Map<String, dynamic>>> getPaymentHistory({
    required String userId,
    required String userType,
    int limit = 20,
  }) async {
    try {
      final response = await http.get(
        Uri.parse(
            '$_baseUrl/payments/history?user_id=$userId&user_type=$userType&limit=$limit'),
        headers: {
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        final payments = data['payments'] as List<dynamic>? ?? [];
        return List<Map<String, dynamic>>.from(payments);
      }
      return [];
    } catch (e) {
      developer.log('Get history error: $e', name: 'UpiPaymentService');
      return [];
    }
  }

  /// Parse UPI response status
  String getStatusMessage(UpiPaymentStatus status) {
    // Use toString() comparison for enum values
    final statusString = status.toString();
    if (statusString.contains('success') || statusString.contains('SUCCESS')) {
      return 'Payment successful';
    } else if (statusString.contains('submitted') ||
        statusString.contains('SUBMITTED')) {
      return 'Payment submitted';
    } else if (statusString.contains('cancelled') ||
        statusString.contains('CANCELLED')) {
      return 'Payment cancelled by user';
    } else if (statusString.contains('pending') ||
        statusString.contains('PENDING')) {
      return 'Payment pending';
    }
    return 'Payment failed';
  }
}

/// UPI App types for easy reference
class UpiApps {
  static const String gpay = 'com.google.android.apps.nbu.paisa.user';
  static const String phonepe = 'com.phonepe.app';
  static const String paytm = 'net.one97.paytm';
  static const String amazonPay = 'in.amazon.mShop.android.shopping';
  static const String bhim = 'in.org.npci.upiapp';

  /// Get app name from package name
  static String getAppName(String packageName) {
    switch (packageName) {
      case gpay:
        return 'Google Pay';
      case phonepe:
        return 'PhonePe';
      case paytm:
        return 'Paytm';
      case amazonPay:
        return 'Amazon Pay';
      case bhim:
        return 'BHIM';
      default:
        return 'UPI App';
    }
  }

  /// Get app icon asset (placeholder - use actual assets)
  static String getAppIcon(String packageName) {
    switch (packageName) {
      case gpay:
        return 'assets/images/gpay.png';
      case phonepe:
        return 'assets/images/phonepe.png';
      case paytm:
        return 'assets/images/paytm.png';
      default:
        return 'assets/images/upi.png';
    }
  }
}
