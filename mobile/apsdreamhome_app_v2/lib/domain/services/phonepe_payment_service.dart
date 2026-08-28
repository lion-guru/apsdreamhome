import 'dart:convert';
import 'dart:developer' as developer;
import 'package:crypto/crypto.dart';
import 'package:http/http.dart' as http;
import 'phonepe_payment_sdk_stub.dart';

/// PhonePe Payment Service
/// Handles PhonePe SDK integration for secure payments
class PhonePePaymentService {
  static final PhonePePaymentService _instance =
      PhonePePaymentService._internal();
  factory PhonePePaymentService() => _instance;
  PhonePePaymentService._internal();

  bool _isInitialized = false;

  // PhonePe Configuration
  late final String _merchantId;
  late final String _saltKey;

  // Backend URL
  final String _baseUrl = 'https://apsdreamhome.com/api/v1';

  /// Initialize PhonePe SDK
  Future<void> initialize({
    required String merchantId,
    required String saltKey,
    String? apiKey,
    bool isProduction = false,
  }) async {
    if (_isInitialized) return;

    _merchantId = merchantId;
    _saltKey = saltKey;

    try {
      // Initialize PhonePe SDK
      PhonePePaymentSdk.init(
        environment:
            isProduction ? Environment.production : Environment.sandbox,
        appId: apiKey,
        merchantId: merchantId,
        enableLogging: !isProduction,
      );

      _isInitialized = true;
      developer.log('PhonePe SDK initialized', name: 'PhonePePaymentService');
    } catch (e) {
      developer.log('PhonePe init error: $e', name: 'PhonePePaymentService');
      rethrow;
    }
  }

  /// Check if SDK is initialized
  bool get isInitialized => _isInitialized;

  /// Create PhonePe payment request
  /// Returns checkout URL or intent
  Future<Map<String, dynamic>> createPaymentRequest({
    required String userId,
    required String userType,
    required double amount,
    required String entityType,
    required int entityId,
    required String description,
    String? mobileNumber,
    String? email,
  }) async {
    if (!_isInitialized) {
      throw Exception('PhonePe SDK not initialized');
    }

    try {
      // Create order on backend first
      final orderResponse = await _createBackendOrder(
        userId: userId,
        userType: userType,
        amount: amount,
        entityType: entityType,
        entityId: entityId,
        description: description,
      );

      final orderId = orderResponse['order_id'] as String;
      final merchantTransactionId =
          'APS_PHONEPE_${DateTime.now().millisecondsSinceEpoch}';

      // Build PhonePe request
      final request = _buildPaymentRequest(
        merchantTransactionId: merchantTransactionId,
        amount: amount,
        orderId: orderId,
        mobileNumber: mobileNumber,
        email: email,
      );

      return {
        'success': true,
        'order_id': orderId,
        'merchant_transaction_id': merchantTransactionId,
        'request': request,
        'checksum': request['checksum'],
      };
    } catch (e) {
      developer.log('Create payment error: $e', name: 'PhonePePaymentService');
      return {
        'success': false,
        'error': e.toString(),
      };
    }
  }

  /// Start PhonePe transaction
  /// Opens PhonePe app or web checkout
  Future<Map<String, dynamic>> startTransaction({
    required Map<String, dynamic> paymentRequest,
  }) async {
    try {
      // For SDK integration
      final body = jsonEncode(paymentRequest['request']);
      final checksum = paymentRequest['checksum'];

      // Start transaction using SDK
      final result = await PhonePePaymentSdk.startTransaction(
        body: body,
        checksum: checksum as String,
        apiEndPoint: '/pg/v1/pay',
        headers: {},
      );

      developer.log('Transaction result: $result',
          name: 'PhonePePaymentService');

      // Parse result
      if (result.isNotEmpty) {
        final status = result['status'];
        final transactionId = result['transactionId'];

        return {
          'success': status == 'SUCCESS',
          'status': status,
          'transaction_id': transactionId,
          'response': result,
        };
      }

      return {
        'success': false,
        'error': 'Transaction cancelled or failed',
      };
    } catch (e) {
      developer.log('Transaction error: $e', name: 'PhonePePaymentService');
      return {
        'success': false,
        'error': e.toString(),
      };
    }
  }

  /// Build PhonePe payment request body
  Map<String, dynamic> _buildPaymentRequest({
    required String merchantTransactionId,
    required double amount,
    required String orderId,
    String? mobileNumber,
    String? email,
  }) {
    // Convert amount to paise
    final amountInPaise = (amount * 100).toInt();

    final payload = {
      'merchantId': _merchantId,
      'merchantTransactionId': merchantTransactionId,
      'merchantUserId': 'APS_USER_${DateTime.now().millisecondsSinceEpoch}',
      'amount': amountInPaise,
      'callbackUrl': '$_baseUrl/payments/phonepe/callback',
      'mobileNumber': mobileNumber ?? '',
      'deviceContext': {
        'deviceOS': 'ANDROID',
      },
      'paymentInstrument': {
        'type': 'PAY_PAGE',
      },
    };

    // Convert to base64
    final jsonPayload = jsonEncode(payload);
    final base64Payload = base64Encode(utf8.encode(jsonPayload));

    // Generate checksum
    final checksum = _generateChecksum(base64Payload, '/pg/v1/pay');

    return {
      'request': base64Payload,
      'checksum': checksum,
      'payload': payload,
    };
  }

  /// Generate PhonePe checksum
  String _generateChecksum(String base64Payload, String endpoint) {
    final stringToHash = base64Payload + endpoint + _saltKey;
    final bytes = utf8.encode(stringToHash);
    final digest = sha256.convert(bytes);
    return '${digest.toString()}###1';
  }

  /// Create order on backend
  Future<Map<String, dynamic>> _createBackendOrder({
    required String userId,
    required String userType,
    required double amount,
    required String entityType,
    required int entityId,
    required String description,
  }) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/payments/create-order'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'user_id': userId,
        'user_type': userType,
        'amount': amount,
        'currency': 'INR',
        'entity_type': entityType,
        'entity_id': entityId,
        'description': description,
        'gateway': 'phonepe',
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body) as Map<String, dynamic>;
    }
    throw Exception('Failed to create order: ${response.body}');
  }

  /// Verify PhonePe payment
  Future<bool> verifyPayment(String merchantTransactionId) async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/payments/phonepe/status/$merchantTransactionId'),
        headers: {'Content-Type': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['success'] == true && data['status'] == 'SUCCESS';
      }
      return false;
    } catch (e) {
      developer.log('Verify error: $e', name: 'PhonePePaymentService');
      return false;
    }
  }

  /// Check PhonePe app availability
  Future<bool> isPhonePeInstalled() async {
    try {
      // Check if PhonePe app is installed
      final apps = await PhonePePaymentSdk.getInstalledUpiApps();
      return apps.isNotEmpty;
    } catch (e) {
      developer.log('Check installed error: $e', name: 'PhonePePaymentService');
      return false;
    }
  }

  /// Get transaction status from PhonePe
  Future<Map<String, dynamic>> getTransactionStatus(
      String merchantTransactionId) async {
    try {
      final response = await http.get(
        Uri.parse(
            '$_baseUrl/payments/phonepe/transaction/$merchantTransactionId'),
        headers: {'Content-Type': 'application/json'},
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }
      return {'success': false, 'error': 'Failed to get status'};
    } catch (e) {
      return {'error': e.toString()};
    }
  }
}

/// PhonePe Payment Status
enum PhonePeStatus {
  success,
  pending,
  failed,
  cancelled,
  unknown,
}

extension PhonePeStatusExtension on String? {
  PhonePeStatus toPhonePeStatus() {
    switch (this?.toUpperCase()) {
      case 'SUCCESS':
        return PhonePeStatus.success;
      case 'PENDING':
      case 'INITIATED':
        return PhonePeStatus.pending;
      case 'FAILED':
        return PhonePeStatus.failed;
      case 'CANCELLED':
        return PhonePeStatus.cancelled;
      default:
        return PhonePeStatus.unknown;
    }
  }
}
