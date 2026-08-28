import 'package:dio/dio.dart';
import 'package:razorpay_flutter/razorpay_flutter.dart';
import '../../core/constants/app_constants.dart';

class PaymentService {
  late Razorpay _razorpay;

  final Function(Map<String, dynamic> order) onPaymentSuccess;
  final Function(String error) onPaymentError;
  final Function() onPaymentCancelled;

  PaymentService({
    required this.onPaymentSuccess,
    required this.onPaymentError,
    required this.onPaymentCancelled,
  }) {
    _razorpay = Razorpay();
    _razorpay.on(Razorpay.EVENT_PAYMENT_SUCCESS, _handlePaymentSuccess);
    _razorpay.on(Razorpay.EVENT_PAYMENT_ERROR, _handlePaymentError);
  }

  void _handlePaymentSuccess(PaymentSuccessResponse response) {
    onPaymentSuccess({
      'payment_id': response.paymentId ?? '',
      'order_id': response.orderId ?? '',
      'signature': response.signature ?? '',
    });
  }

  void _handlePaymentError(PaymentFailureResponse response) {
    if (response.code == Razorpay.PAYMENT_CANCELLED) {
      onPaymentCancelled();
    } else {
      onPaymentError(response.message ?? 'Payment failed');
    }
  }

  /// Create order on backend, then open Razorpay checkout
  Future<void> startPayment({
    required Dio dio,
    required String token,
    required int propertyId,
    required int packageId,
    required String packageName,
    required double amount,
    String? contact,
    String? email,
  }) async {
    try {
      final response = await dio.post(
        '${AppConstants.apiVersion}${AppConstants.listingCreateOrderEndpoint}',
        data: {
          'property_id': propertyId,
          'package_id': packageId,
        },
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true || data['order_id'] == null) {
        onPaymentError((data['message'] ?? 'Failed to create order').toString());
        return;
      }

      final razorpayKey = (data['razorpay_key_id'] ?? '').toString();
      if (razorpayKey.isEmpty) {
        onPaymentError('Payment configuration missing. Please contact support.');
        return;
      }

      final prefill = data['prefill'] as Map<String, dynamic>? ?? {};
      final options = {
        'key': razorpayKey,
        'amount': (amount * 100).toInt(),
        'name': 'APS Dream Home',
        'description': 'Listing Upgrade: $packageName',
        'order_id': data['order_id'],
        'prefill': {
          'contact': contact ?? (prefill['contact'] ?? '').toString(),
          'email': email ?? (prefill['email'] ?? '').toString(),
        },
        'theme': {
          'color': '#7c3aed',
        },
      };

      _razorpay.open(options);
    } catch (e) {
      onPaymentError('Error: ${e.toString()}');
    }
  }

  /// Verify payment on backend
  Future<bool> verifyPayment({
    required Dio dio,
    required String token,
    required String orderId,
    required String paymentId,
    required String signature,
  }) async {
    try {
      final response = await dio.post(
        '${AppConstants.apiVersion}${AppConstants.listingVerifyPaymentEndpoint}',
        data: {
          'order_id': orderId,
          'razorpay_payment_id': paymentId,
          'razorpay_signature': signature,
        },
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      return response.data['success'] == true;
    } catch (e) {
      return false;
    }
  }

  void dispose() {
    _razorpay.clear();
  }
}
