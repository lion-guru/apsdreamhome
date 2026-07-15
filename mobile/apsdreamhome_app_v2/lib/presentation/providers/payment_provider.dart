import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';
// import 'package:upi_india/upi_india.dart';  // Disabled - dependency issue
import '../../domain/services/upi_payment_service.dart';
import '../../domain/services/phonepe_payment_service.dart';

// Stub UPI App class since package is disabled
class UpiApp {
  final String name;
  final String packageName;
  final String icon;

  UpiApp({
    required this.name,
    required this.packageName,
    required this.icon,
  });
}

// Stub UpiApps class since package is disabled
class UpiApps {
  static const String gpay = 'com.google.android.apps.nbu.paisa.user';
  static const String phonepe = 'com.phonepe.app';
  static const String paytm = 'net.one97.paytm';
  static const String amazonPay = 'in.amazon.mShop.android.shopping';
  static const String bhim = 'org.npci.upiapp';
}

/// Payment state
class PaymentState {
  final bool isLoading;
  final String? error;
  final PaymentMethod? selectedMethod;
  final List<UpiApp> availableUpiApps;
  final List<PaymentTransaction> transactions;
  final PaymentTransaction? currentTransaction;

  PaymentState({
    this.isLoading = false,
    this.error,
    this.selectedMethod,
    this.availableUpiApps = const [],
    this.transactions = const [],
    this.currentTransaction,
  });

  PaymentState copyWith({
    bool? isLoading,
    String? error,
    PaymentMethod? selectedMethod,
    List<UpiApp>? availableUpiApps,
    List<PaymentTransaction>? transactions,
    PaymentTransaction? currentTransaction,
    bool clearError = false,
    bool clearTransaction = false,
  }) {
    return PaymentState(
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
      selectedMethod: selectedMethod ?? this.selectedMethod,
      availableUpiApps: availableUpiApps ?? this.availableUpiApps,
      transactions: transactions ?? this.transactions,
      currentTransaction: clearTransaction
          ? null
          : (currentTransaction ?? this.currentTransaction),
    );
  }
}

/// Payment method enum
enum PaymentMethod {
  gpay,
  phonepe,
  paytm,
  amazonPay,
  bhim,
  razorpay,
  cash,
}

extension PaymentMethodExtension on PaymentMethod {
  String get displayName {
    switch (this) {
      case PaymentMethod.gpay:
        return 'Google Pay';
      case PaymentMethod.phonepe:
        return 'PhonePe';
      case PaymentMethod.paytm:
        return 'Paytm';
      case PaymentMethod.amazonPay:
        return 'Amazon Pay';
      case PaymentMethod.bhim:
        return 'BHIM UPI';
      case PaymentMethod.razorpay:
        return 'Card/Net Banking';
      case PaymentMethod.cash:
        return 'Cash Payment';
    }
  }

  String get icon {
    switch (this) {
      case PaymentMethod.gpay:
        return 'assets/images/gpay.png';
      case PaymentMethod.phonepe:
        return 'assets/images/phonepe.png';
      case PaymentMethod.paytm:
        return 'assets/images/paytm.png';
      case PaymentMethod.amazonPay:
        return 'assets/images/amazon_pay.png';
      case PaymentMethod.bhim:
        return 'assets/images/bhim.png';
      case PaymentMethod.razorpay:
        return 'assets/images/card.png';
      case PaymentMethod.cash:
        return 'assets/images/cash.png';
    }
  }
}

/// Payment transaction model
class PaymentTransaction {
  final String id;
  final String orderId;
  final double amount;
  final String currency;
  final String status;
  final PaymentMethod? method;
  final String? transactionRefId;
  final String? entityType;
  final int? entityId;
  final DateTime createdAt;
  final DateTime? completedAt;

  PaymentTransaction({
    required this.id,
    required this.orderId,
    required this.amount,
    this.currency = 'INR',
    required this.status,
    this.method,
    this.transactionRefId,
    this.entityType,
    this.entityId,
    required this.createdAt,
    this.completedAt,
  });

  factory PaymentTransaction.fromJson(Map<String, dynamic> json) {
    return PaymentTransaction(
      id: json['id'] as String? ?? '',
      orderId: json['order_id'] as String? ?? '',
      amount: ((json['amount'] as num?) ?? 0).toDouble(),
      currency: json['currency'] as String? ?? 'INR',
      status: json['status'] as String? ?? 'unknown',
      method: json['method'] != null
          ? PaymentMethod.values.firstWhere(
              (e) => e.name == json['method'],
              orElse: () => PaymentMethod.razorpay,
            )
          : null,
      transactionRefId: json['transaction_ref_id'] as String?,
      entityType: json['entity_type'] as String?,
      entityId: json['entity_id'] as int?,
      createdAt: DateTime.parse(
          json['created_at'] as String? ?? DateTime.now().toIso8601String()),
      completedAt: json['completed_at'] != null
          ? DateTime.parse(json['completed_at'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'order_id': orderId,
      'amount': amount,
      'currency': currency,
      'status': status,
      'method': method.toString().split('.').last,
      'transaction_ref_id': transactionRefId,
      'entity_type': entityType,
      'entity_id': entityId,
      'created_at': createdAt.toIso8601String(),
      'completed_at': completedAt?.toIso8601String(),
    };
  }

  PaymentTransaction copyWith({
    String? id,
    String? orderId,
    double? amount,
    String? currency,
    String? status,
    PaymentMethod? method,
    String? transactionRefId,
    String? entityType,
    int? entityId,
    DateTime? createdAt,
    DateTime? completedAt,
  }) {
    return PaymentTransaction(
      id: id ?? this.id,
      orderId: orderId ?? this.orderId,
      amount: amount ?? this.amount,
      currency: currency ?? this.currency,
      status: status ?? this.status,
      method: method ?? this.method,
      transactionRefId: transactionRefId ?? this.transactionRefId,
      entityType: entityType ?? this.entityType,
      entityId: entityId ?? this.entityId,
      createdAt: createdAt ?? this.createdAt,
      completedAt: completedAt ?? this.completedAt,
    );
  }
}

/// Payment Notifier
class PaymentNotifier extends StateNotifier<PaymentState> {
  final UpiPaymentService _upiService;
  final PhonePePaymentService _phonePeService;

  PaymentNotifier()
      : _upiService = UpiPaymentService(),
        _phonePeService = PhonePePaymentService(),
        super(PaymentState());

  /// Initialize payment services
  Future<void> initialize() async {
    state = state.copyWith(isLoading: true);

    try {
      await _upiService.initialize();

      // Load available UPI apps
      final apps = await _upiService.getAvailableUpiApps();

      state = state.copyWith(
        isLoading: false,
        availableUpiApps: apps,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'Failed to initialize payment: $e',
      );
    }
  }

  /// Initialize PhonePe
  Future<void> initializePhonePe({
    required String merchantId,
    required String saltKey,
    String? apiKey,
    bool isProduction = false,
  }) async {
    try {
      await _phonePeService.initialize(
        merchantId: merchantId,
        saltKey: saltKey,
        apiKey: apiKey,
        isProduction: isProduction,
      );
    } catch (e) {
      state = state.copyWith(
        error: 'Failed to initialize PhonePe: $e',
      );
    }
  }

  /// Select payment method
  void selectMethod(PaymentMethod method) {
    state = state.copyWith(
      selectedMethod: method,
      clearError: true,
    );
  }

  /// Initiate UPI payment
  Future<bool> initiateUpiPayment({
    required PaymentMethod method,
    required double amount,
    required String description,
    required String userId,
    required String userType,
    required String entityType,
    required int entityId,
    String receiverUpiId = 'apsdreamhome@upi',
    String receiverName = 'APS Dream Home',
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);

    try {
      // Find the UPI app
      final app = state.availableUpiApps.firstWhere(
        (app) {
          switch (method) {
            case PaymentMethod.gpay:
              return app.packageName == UpiApps.gpay;
            case PaymentMethod.phonepe:
              return app.packageName == UpiApps.phonepe;
            case PaymentMethod.paytm:
              return app.packageName == UpiApps.paytm;
            case PaymentMethod.amazonPay:
              return app.packageName == UpiApps.amazonPay;
            case PaymentMethod.bhim:
              return app.packageName == UpiApps.bhim;
            default:
              return false;
          }
        },
        orElse: () => throw Exception('${method.displayName} is not installed'),
      );

      // Create order on backend
      final orderData = await _upiService.createOrder(
        userId: userId,
        userType: userType,
        amount: amount,
        entityType: entityType,
        entityId: entityId,
        description: description,
      );

      final orderId = orderData['order_id'] as String?;
      final transactionRefId = 'APS${DateTime.now().millisecondsSinceEpoch}';

      // Create transaction record
      final transaction = PaymentTransaction(
        id: orderData['id'] as String? ?? '',
        orderId: orderId ?? '',
        amount: amount,
        status: 'initiated',
        method: method,
        transactionRefId: transactionRefId,
        entityType: entityType,
        entityId: entityId,
        createdAt: DateTime.now(),
      );

      state = state.copyWith(
        currentTransaction: transaction,
      );

      // Initiate UPI payment
      final response = await _upiService.initiatePayment(
        app: app,
        receiverUpiId: receiverUpiId,
        receiverName: receiverName,
        amount: amount,
        transactionNote: description,
        transactionRefId: transactionRefId,
      );

      // Handle response
      final status = response.status;
      final success = status.toString().toLowerCase().contains('success');

      // Update transaction status
      final updatedTransaction = transaction.copyWith(
        status: success
            ? 'completed'
            : (status.toString().toLowerCase().contains('cancel')
                ? 'cancelled'
                : 'failed'),
      );

      state = state.copyWith(
        isLoading: false,
        currentTransaction: updatedTransaction,
      );

      // Verify with backend
      if (success) {
        await _upiService.verifyPayment(
          orderId: orderId ?? '',
          transactionRefId: response.transactionRefId ?? transactionRefId,
          status: 'completed',
        );
      }

      return success;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'Payment failed: $e',
      );
      return false;
    }
  }

  /// Initiate PhonePe payment
  Future<bool> initiatePhonePePayment({
    required double amount,
    required String description,
    required String userId,
    required String userType,
    required String entityType,
    required int entityId,
    String? mobileNumber,
    String? email,
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);

    try {
      // Create payment request
      final request = await _phonePeService.createPaymentRequest(
        userId: userId,
        userType: userType,
        amount: amount,
        entityType: entityType,
        entityId: entityId,
        description: description,
        mobileNumber: mobileNumber,
        email: email,
      );

      final success = request['success'] as bool? ?? false;
      if (!success) {
        throw Exception(request['error'] ?? 'Failed to create payment request');
      }

      // Create transaction record
      final transaction = PaymentTransaction(
        id: request['order_id'] as String? ?? '',
        orderId: request['order_id'] as String? ?? '',
        amount: amount,
        status: 'initiated',
        method: PaymentMethod.phonepe,
        transactionRefId: request['merchant_transaction_id'] as String?,
        entityType: entityType,
        entityId: entityId,
        createdAt: DateTime.now(),
      );

      state = state.copyWith(
        currentTransaction: transaction,
      );

      // Start transaction
      final result = await _phonePeService.startTransaction(
        paymentRequest: request,
      );

      final paymentSuccess = (result['success'] as bool?) == true;

      // Update transaction
      final updatedTransaction = transaction.copyWith(
        status: paymentSuccess
            ? 'completed'
            : (result['status'].toString().toLowerCase().contains('cancel')
                ? 'cancelled'
                : 'failed'),
      );

      state = state.copyWith(
        isLoading: false,
        currentTransaction: updatedTransaction,
      );

      return success;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'PhonePe payment failed: $e',
      );
      return false;
    }
  }

  /// Load payment history
  Future<void> loadPaymentHistory({
    required String userId,
    required String userType,
    int limit = 20,
  }) async {
    state = state.copyWith(isLoading: true);

    try {
      final history = await _upiService.getPaymentHistory(
        userId: userId,
        userType: userType,
        limit: limit,
      );

      final transactions =
          history.map((json) => PaymentTransaction.fromJson(json)).toList();

      state = state.copyWith(
        isLoading: false,
        transactions: transactions,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'Failed to load payment history: $e',
      );
    }
  }

  /// Clear error
  void clearError() {
    state = state.copyWith(clearError: true);
  }

  /// Clear current transaction
  void clearTransaction() {
    state = state.copyWith(clearTransaction: true);
  }
}

// Providers
final paymentProvider =
    StateNotifierProvider<PaymentNotifier, PaymentState>((ref) {
  return PaymentNotifier();
});

final availableUpiAppsProvider = Provider<List<UpiApp>>((ref) {
  return ref.watch(paymentProvider).availableUpiApps;
});

final currentTransactionProvider = Provider<PaymentTransaction?>((ref) {
  return ref.watch(paymentProvider).currentTransaction;
});
