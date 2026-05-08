import 'package:freezed_annotation/freezed_annotation.dart';

part 'booking_model.freezed.dart';
part 'booking_model.g.dart';

/// Booking Model - Plot Booking Management
@freezed
class BookingModel with _$BookingModel {
  const factory BookingModel({
    required String id,
    required String plotId,
    required String plotNumber,
    required String colonyId,
    required String colonyName,
    
    // Customer Info
    required String customerId,
    required String customerName,
    required String customerPhone,
    String? customerEmail,
    String? customerAddress,
    
    // Associate Info (if booked through associate)
    String? associateId,
    String? associateName,
    String? associateRank,
    double? associateCommission,
    
    // Pricing
    required double plotPrice,
    required double tokenAmount,
    required double totalAmount,
    required String paymentPlan, // full, emi, installment
    
    // EMI Details (if applicable)
    double? downPayment,
    int? emiMonths,
    double? emiAmount,
    double? interestRate,
    
    // Status
    required String status, // pending, approved, rejected, completed, cancelled
    String? statusReason,
    DateTime? approvedAt,
    String? approvedBy,
    DateTime? completedAt,
    DateTime? cancelledAt,
    String? cancelledReason,
    
    // Documents
    List<BookingDocument>? documents,
    
    // Payments
    List<PaymentModel>? payments,
    double? totalPaid,
    double? remainingAmount,
    
    // Registry Info
    DateTime? registryDate,
    String? registryNumber,
    String? registryOffice,
    
    // Agreement
    DateTime? agreementDate,
    String? agreementNumber,
    String? agreementDocumentUrl,
    
    // Timestamps
    DateTime? createdAt,
    DateTime? updatedAt,
    
    // Notes
    String? notes,
    List<BookingHistory>? history,
  }) = _BookingModel;

  factory BookingModel.fromJson(Map<String, dynamic> json) =>
      _$BookingModelFromJson(json);

  const BookingModel._();

  double get paidPercentage => 
      totalAmount > 0 ? ((totalPaid ?? 0) / totalAmount) * 100 : 0;
      
  bool get isFullyPaid => (totalPaid ?? 0) >= totalAmount;
  bool get isPending => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isCompleted => status == 'completed';
  bool get isCancelled => status == 'cancelled';
}

@freezed
class BookingDocument with _$BookingDocument {
  const factory BookingDocument({
    required String id,
    required String type, // aadhar, pan, photo, agreement, etc.
    required String name,
    required String url,
    String? thumbnailUrl,
    DateTime? uploadedAt,
    String? verifiedBy,
    DateTime? verifiedAt,
    String? status, // pending, verified, rejected
    String? notes,
  }) = _BookingDocument;

  factory BookingDocument.fromJson(Map<String, dynamic> json) =>
      _$BookingDocumentFromJson(json);
}

@freezed
class PaymentModel with _$PaymentModel {
  const factory PaymentModel({
    required String id,
    required String bookingId,
    required double amount,
    required String type, // token, down_payment, installment, registry, full
    required String method, // cash, cheque, bank_transfer, upi, razorpay
    String? transactionId,
    String? razorpayOrderId,
    String? razorpayPaymentId,
    DateTime? paidAt,
    String? paidBy,
    String? receivedBy,
    String? status, // pending, completed, failed, refunded
    String? notes,
    String? receiptUrl,
    DateTime? createdAt,
  }) = _PaymentModel;

  factory PaymentModel.fromJson(Map<String, dynamic> json) =>
      _$PaymentModelFromJson(json);
}

@freezed
class BookingHistory with _$BookingHistory {
  const factory BookingHistory({
    required String id,
    required String action,
    required String performedBy,
    required DateTime performedAt,
    String? notes,
    Map<String, dynamic>? oldValues,
    Map<String, dynamic>? newValues,
  }) = _BookingHistory;

  factory BookingHistory.fromJson(Map<String, dynamic> json) =>
      _$BookingHistoryFromJson(json);
}
