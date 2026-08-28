/// Payout Model - MLM Payout Management
class PayoutModel {
  final String id;
  final String userId;
  final String? userName;
  final double amount;
  final String status; // requested, processing, completed, rejected
  final String? paymentMethod; // bank, upi, cash
  final String? transactionId;
  final DateTime? requestedAt;
  final DateTime? processedAt;
  final DateTime? completedAt;
  final String? remarks;
  final List<String>? commissionIds; // associated commissions
  
  // Additional fields for MLM
  final String? requestedVia;
  final String? bankAccountNumber;
  final String? bankIfscCode;
  final String? bankName;
  final String? upiId;

  PayoutModel({
    required this.id,
    required this.userId,
    this.userName,
    required this.amount,
    required this.status,
    this.paymentMethod,
    this.transactionId,
    this.requestedAt,
    this.processedAt,
    this.completedAt,
    this.remarks,
    this.commissionIds,
    this.requestedVia,
    this.bankAccountNumber,
    this.bankIfscCode,
    this.bankName,
    this.upiId,
  });

  factory PayoutModel.fromJson(Map<String, dynamic> json) {
    return PayoutModel(
      id: json['id'] as String,
      userId: json['userId'] as String? ?? json['associateId'] as String? ?? '',
      userName: json['userName'] as String? ?? json['associateName'] as String?,
      amount: (json['amount'] as num).toDouble(),
      status: json['status'] as String,
      paymentMethod: json['paymentMethod'] as String?,
      transactionId: json['transactionId'] as String?,
      requestedAt: json['requestedAt'] != null
          ? DateTime.parse(json['requestedAt'] as String)
          : json['createdAt'] != null
              ? DateTime.parse(json['createdAt'] as String)
              : null,
      processedAt: json['processedAt'] != null
          ? DateTime.parse(json['processedAt'] as String)
          : null,
      completedAt: json['completedAt'] != null
          ? DateTime.parse(json['completedAt'] as String)
          : null,
      remarks: json['remarks'] as String?,
      commissionIds: (json['commissionIds'] as List<dynamic>?)?.cast<String>(),
      requestedVia: json['requestedVia'] as String?,
      bankAccountNumber: json['bankAccountNumber'] as String?,
      bankIfscCode: json['bankIfscCode'] as String?,
      bankName: json['bankName'] as String?,
      upiId: json['upiId'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'userId': userId,
      'userName': userName,
      'amount': amount,
      'status': status,
      'paymentMethod': paymentMethod,
      'transactionId': transactionId,
      'requestedAt': requestedAt?.toIso8601String(),
      'processedAt': processedAt?.toIso8601String(),
      'completedAt': completedAt?.toIso8601String(),
      'remarks': remarks,
      'commissionIds': commissionIds,
      'requestedVia': requestedVia,
      'bankAccountNumber': bankAccountNumber,
      'bankIfscCode': bankIfscCode,
      'bankName': bankName,
      'upiId': upiId,
    };
  }

  PayoutModel copyWith({
    String? id,
    String? userId,
    String? userName,
    double? amount,
    String? status,
    String? paymentMethod,
    String? transactionId,
    DateTime? requestedAt,
    DateTime? processedAt,
    DateTime? completedAt,
    String? remarks,
    List<String>? commissionIds,
    String? requestedVia,
    String? bankAccountNumber,
    String? bankIfscCode,
    String? bankName,
    String? upiId,
  }) {
    return PayoutModel(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      userName: userName ?? this.userName,
      amount: amount ?? this.amount,
      status: status ?? this.status,
      paymentMethod: paymentMethod ?? this.paymentMethod,
      transactionId: transactionId ?? this.transactionId,
      requestedAt: requestedAt ?? this.requestedAt,
      processedAt: processedAt ?? this.processedAt,
      completedAt: completedAt ?? this.completedAt,
      remarks: remarks ?? this.remarks,
      commissionIds: commissionIds ?? this.commissionIds,
      requestedVia: requestedVia ?? this.requestedVia,
      bankAccountNumber: bankAccountNumber ?? this.bankAccountNumber,
      bankIfscCode: bankIfscCode ?? this.bankIfscCode,
      bankName: bankName ?? this.bankName,
      upiId: upiId ?? this.upiId,
    );
  }
}
