// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'booking_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$BookingModelImpl _$$BookingModelImplFromJson(Map<String, dynamic> json) =>
    _$BookingModelImpl(
      id: json['id'] as String,
      plotId: json['plotId'] as String,
      plotNumber: json['plotNumber'] as String,
      colonyId: json['colonyId'] as String,
      colonyName: json['colonyName'] as String,
      customerId: json['customerId'] as String,
      customerName: json['customerName'] as String,
      customerPhone: json['customerPhone'] as String,
      customerEmail: json['customerEmail'] as String?,
      customerAddress: json['customerAddress'] as String?,
      associateId: json['associateId'] as String?,
      associateName: json['associateName'] as String?,
      associateRank: json['associateRank'] as String?,
      associateCommission: (json['associateCommission'] as num?)?.toDouble(),
      plotPrice: (json['plotPrice'] as num).toDouble(),
      tokenAmount: (json['tokenAmount'] as num).toDouble(),
      totalAmount: (json['totalAmount'] as num).toDouble(),
      paymentPlan: json['paymentPlan'] as String,
      downPayment: (json['downPayment'] as num?)?.toDouble(),
      emiMonths: (json['emiMonths'] as num?)?.toInt(),
      emiAmount: (json['emiAmount'] as num?)?.toDouble(),
      interestRate: (json['interestRate'] as num?)?.toDouble(),
      status: json['status'] as String,
      statusReason: json['statusReason'] as String?,
      approvedAt: json['approvedAt'] == null
          ? null
          : DateTime.parse(json['approvedAt'] as String),
      approvedBy: json['approvedBy'] as String?,
      completedAt: json['completedAt'] == null
          ? null
          : DateTime.parse(json['completedAt'] as String),
      cancelledAt: json['cancelledAt'] == null
          ? null
          : DateTime.parse(json['cancelledAt'] as String),
      cancelledReason: json['cancelledReason'] as String?,
      documents: (json['documents'] as List<dynamic>?)
          ?.map((e) => BookingDocument.fromJson(e as Map<String, dynamic>))
          .toList(),
      payments: (json['payments'] as List<dynamic>?)
          ?.map((e) => PaymentModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      totalPaid: (json['totalPaid'] as num?)?.toDouble(),
      remainingAmount: (json['remainingAmount'] as num?)?.toDouble(),
      registryDate: json['registryDate'] == null
          ? null
          : DateTime.parse(json['registryDate'] as String),
      registryNumber: json['registryNumber'] as String?,
      registryOffice: json['registryOffice'] as String?,
      agreementDate: json['agreementDate'] == null
          ? null
          : DateTime.parse(json['agreementDate'] as String),
      agreementNumber: json['agreementNumber'] as String?,
      agreementDocumentUrl: json['agreementDocumentUrl'] as String?,
      createdAt: json['createdAt'] == null
          ? null
          : DateTime.parse(json['createdAt'] as String),
      updatedAt: json['updatedAt'] == null
          ? null
          : DateTime.parse(json['updatedAt'] as String),
      notes: json['notes'] as String?,
      history: (json['history'] as List<dynamic>?)
          ?.map((e) => BookingHistory.fromJson(e as Map<String, dynamic>))
          .toList(),
    );

Map<String, dynamic> _$$BookingModelImplToJson(_$BookingModelImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'plotId': instance.plotId,
      'plotNumber': instance.plotNumber,
      'colonyId': instance.colonyId,
      'colonyName': instance.colonyName,
      'customerId': instance.customerId,
      'customerName': instance.customerName,
      'customerPhone': instance.customerPhone,
      'customerEmail': instance.customerEmail,
      'customerAddress': instance.customerAddress,
      'associateId': instance.associateId,
      'associateName': instance.associateName,
      'associateRank': instance.associateRank,
      'associateCommission': instance.associateCommission,
      'plotPrice': instance.plotPrice,
      'tokenAmount': instance.tokenAmount,
      'totalAmount': instance.totalAmount,
      'paymentPlan': instance.paymentPlan,
      'downPayment': instance.downPayment,
      'emiMonths': instance.emiMonths,
      'emiAmount': instance.emiAmount,
      'interestRate': instance.interestRate,
      'status': instance.status,
      'statusReason': instance.statusReason,
      'approvedAt': instance.approvedAt?.toIso8601String(),
      'approvedBy': instance.approvedBy,
      'completedAt': instance.completedAt?.toIso8601String(),
      'cancelledAt': instance.cancelledAt?.toIso8601String(),
      'cancelledReason': instance.cancelledReason,
      'documents': instance.documents,
      'payments': instance.payments,
      'totalPaid': instance.totalPaid,
      'remainingAmount': instance.remainingAmount,
      'registryDate': instance.registryDate?.toIso8601String(),
      'registryNumber': instance.registryNumber,
      'registryOffice': instance.registryOffice,
      'agreementDate': instance.agreementDate?.toIso8601String(),
      'agreementNumber': instance.agreementNumber,
      'agreementDocumentUrl': instance.agreementDocumentUrl,
      'createdAt': instance.createdAt?.toIso8601String(),
      'updatedAt': instance.updatedAt?.toIso8601String(),
      'notes': instance.notes,
      'history': instance.history,
    };

_$BookingDocumentImpl _$$BookingDocumentImplFromJson(
  Map<String, dynamic> json,
) => _$BookingDocumentImpl(
  id: json['id'] as String,
  type: json['type'] as String,
  name: json['name'] as String,
  url: json['url'] as String,
  thumbnailUrl: json['thumbnailUrl'] as String?,
  uploadedAt: json['uploadedAt'] == null
      ? null
      : DateTime.parse(json['uploadedAt'] as String),
  verifiedBy: json['verifiedBy'] as String?,
  verifiedAt: json['verifiedAt'] == null
      ? null
      : DateTime.parse(json['verifiedAt'] as String),
  status: json['status'] as String?,
  notes: json['notes'] as String?,
);

Map<String, dynamic> _$$BookingDocumentImplToJson(
  _$BookingDocumentImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'type': instance.type,
  'name': instance.name,
  'url': instance.url,
  'thumbnailUrl': instance.thumbnailUrl,
  'uploadedAt': instance.uploadedAt?.toIso8601String(),
  'verifiedBy': instance.verifiedBy,
  'verifiedAt': instance.verifiedAt?.toIso8601String(),
  'status': instance.status,
  'notes': instance.notes,
};

_$PaymentModelImpl _$$PaymentModelImplFromJson(Map<String, dynamic> json) =>
    _$PaymentModelImpl(
      id: json['id'] as String,
      bookingId: json['bookingId'] as String,
      amount: (json['amount'] as num).toDouble(),
      type: json['type'] as String,
      method: json['method'] as String,
      transactionId: json['transactionId'] as String?,
      razorpayOrderId: json['razorpayOrderId'] as String?,
      razorpayPaymentId: json['razorpayPaymentId'] as String?,
      paidAt: json['paidAt'] == null
          ? null
          : DateTime.parse(json['paidAt'] as String),
      paidBy: json['paidBy'] as String?,
      receivedBy: json['receivedBy'] as String?,
      status: json['status'] as String?,
      notes: json['notes'] as String?,
      receiptUrl: json['receiptUrl'] as String?,
      createdAt: json['createdAt'] == null
          ? null
          : DateTime.parse(json['createdAt'] as String),
    );

Map<String, dynamic> _$$PaymentModelImplToJson(_$PaymentModelImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'bookingId': instance.bookingId,
      'amount': instance.amount,
      'type': instance.type,
      'method': instance.method,
      'transactionId': instance.transactionId,
      'razorpayOrderId': instance.razorpayOrderId,
      'razorpayPaymentId': instance.razorpayPaymentId,
      'paidAt': instance.paidAt?.toIso8601String(),
      'paidBy': instance.paidBy,
      'receivedBy': instance.receivedBy,
      'status': instance.status,
      'notes': instance.notes,
      'receiptUrl': instance.receiptUrl,
      'createdAt': instance.createdAt?.toIso8601String(),
    };

_$BookingHistoryImpl _$$BookingHistoryImplFromJson(Map<String, dynamic> json) =>
    _$BookingHistoryImpl(
      id: json['id'] as String,
      action: json['action'] as String,
      performedBy: json['performedBy'] as String,
      performedAt: DateTime.parse(json['performedAt'] as String),
      notes: json['notes'] as String?,
      oldValues: json['oldValues'] as Map<String, dynamic>?,
      newValues: json['newValues'] as Map<String, dynamic>?,
    );

Map<String, dynamic> _$$BookingHistoryImplToJson(
  _$BookingHistoryImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'action': instance.action,
  'performedBy': instance.performedBy,
  'performedAt': instance.performedAt.toIso8601String(),
  'notes': instance.notes,
  'oldValues': instance.oldValues,
  'newValues': instance.newValues,
};
