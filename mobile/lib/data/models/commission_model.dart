import 'package:json_annotation/json_annotation.dart';

part 'commission_model.g.dart';

@JsonSerializable()
class Commission {
  final String commissionId;
  final String userId;
  final String sourceId;
  final String sourceType; // Property, Lead, Membership
  final double amount;
  final double percentage;
  final String rank;
  final String status; // Pending, Approved, Paid
  final String createdAt;
  final String updatedAt;
  final String? lastSyncedAt;
  
  const Commission({
    required this.commissionId,
    required this.userId,
    required this.sourceId,
    required this.sourceType,
    required this.amount,
    required this.percentage,
    required this.rank,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
    this.lastSyncedAt,
  });
  
  factory Commission.fromJson(Map<String, dynamic> json) => _$CommissionFromJson(json);
  Map<String, dynamic> toJson() => _$CommissionToJson(this);
  
  Commission copyWith({
    String? commissionId,
    String? userId,
    String? sourceId,
    String? sourceType,
    double? amount,
    double? percentage,
    String? rank,
    String? status,
    String? createdAt,
    String? updatedAt,
    String? lastSyncedAt,
  }) {
    return Commission(
      commissionId: commissionId ?? this.commissionId,
      userId: userId ?? this.userId,
      sourceId: sourceId ?? this.sourceId,
      sourceType: sourceType ?? this.sourceType,
      amount: amount ?? this.amount,
      percentage: percentage ?? this.percentage,
      rank: rank ?? this.rank,
      status: status ?? this.status,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      lastSyncedAt: lastSyncedAt ?? this.lastSyncedAt,
    );
  }
  
  // Get status color
  String getStatusColor() {
    switch (status.toLowerCase()) {
      case 'pending':
        return '#FF9800'; // Orange
      case 'approved':
        return '#2196F3'; // Blue
      case 'paid':
        return '#4CAF50'; // Green
      default:
        return '#9E9E9E'; // Grey
    }
  }
  
  // Format amount
  String get formattedAmount {
    if (amount >= 10000000) {
      return '${(amount / 10000000).toStringAsFixed(2)} Cr';
    } else if (amount >= 100000) {
      return '${(amount / 100000).toStringAsFixed(2)} L';
    } else if (amount >= 1000) {
      return '${(amount / 1000).toStringAsFixed(2)} K';
    } else {
      return amount.toStringAsFixed(2);
    }
  }
  
  // Check if commission is pending
  bool get isPending => status.toLowerCase() == 'pending';
  
  // Check if commission is approved
  bool get isApproved => status.toLowerCase() == 'approved';
  
  // Check if commission is paid
  bool get isPaid => status.toLowerCase() == 'paid';
  
  // Get commission type description
  String get commissionType {
    switch (sourceType.toLowerCase()) {
      case 'property':
        return 'Property Sale';
      case 'lead':
        return 'Lead Conversion';
      case 'membership':
        return 'Membership Commission';
      default:
        return sourceType;
    }
  }
}
