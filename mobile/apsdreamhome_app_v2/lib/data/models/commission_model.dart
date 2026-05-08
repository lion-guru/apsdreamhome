/// Commission Model - MLM Commission Management
class CommissionModel {
  final String id;
  final String userId;
  final String? userName;
  final String? associateId; // alias for userId
  final String? associateName; // alias for userName
  final String? associateRank;
  final String type; // direct, level, bonus
  final double amount;
  final double? commissionAmount; // alias for amount
  final double percentage;
  final String? sourceUserId;
  final String? sourceUserName;
  final String? leadId;
  final String? propertyId;
  final String? plotId;
  final String? plotNumber;
  final String? colonyName;
  final String? customerName;
  final String? bookingId;
  final double? saleAmount;
  final DateTime? saleDate;
  final String? commissionType; // alias for type
  final String? sellerId;
  final String? sellerName;
  final String? sellerRank;
  final String? relationship;
  final String status; // pending, paid, hold
  final DateTime? createdAt;
  final DateTime? paidAt;
  final String? description;
  final int? level;
  final double? maxDistributedPercentage;
  final String? calculationBreakdown;

  CommissionModel({
    required this.id,
    String? userId,
    this.userName,
    this.associateId,
    this.associateName,
    this.associateRank,
    this.type = 'direct',
    this.amount = 0.0,
    this.commissionAmount,
    required this.percentage,
    this.sourceUserId,
    this.sourceUserName,
    this.leadId,
    this.propertyId,
    this.plotId,
    this.plotNumber,
    this.colonyName,
    this.customerName,
    this.bookingId,
    this.saleAmount,
    this.saleDate,
    this.commissionType,
    this.sellerId,
    this.sellerName,
    this.sellerRank,
    this.relationship,
    required this.status,
    this.createdAt,
    this.paidAt,
    this.description,
    this.level,
    this.maxDistributedPercentage,
    this.calculationBreakdown,
  }) : userId = userId ?? associateId ?? '';

  factory CommissionModel.fromJson(Map<String, dynamic> json) {
    return CommissionModel(
      id: json['id'] as String,
      userId: json['userId'] as String? ?? json['associateId'] as String?,
      userName: json['userName'] as String? ?? json['associateName'] as String?,
      associateId: json['associateId'] as String?,
      associateName: json['associateName'] as String?,
      associateRank: json['associateRank'] as String?,
      type: json['type'] as String? ??
          json['commissionType'] as String? ??
          'direct',
      amount: (json['amount'] as num? ?? json['commissionAmount'] as num?)
              ?.toDouble() ??
          0.0,
      commissionAmount: (json['commissionAmount'] as num?)?.toDouble(),
      percentage: (json['percentage'] as num).toDouble(),
      sourceUserId: json['sourceUserId'] as String?,
      sourceUserName: json['sourceUserName'] as String?,
      leadId: json['leadId'] as String?,
      propertyId: json['propertyId'] as String?,
      plotId: json['plotId'] as String?,
      plotNumber: json['plotNumber'] as String?,
      colonyName: json['colonyName'] as String?,
      customerName: json['customerName'] as String?,
      bookingId: json['bookingId'] as String?,
      saleAmount: (json['saleAmount'] as num?)?.toDouble(),
      saleDate: json['saleDate'] != null
          ? DateTime.parse(json['saleDate'] as String)
          : null,
      commissionType: json['commissionType'] as String?,
      sellerId: json['sellerId'] as String?,
      sellerName: json['sellerName'] as String?,
      sellerRank: json['sellerRank'] as String?,
      relationship: json['relationship'] as String?,
      status: json['status'] as String,
      createdAt: json['createdAt'] != null
          ? DateTime.parse(json['createdAt'] as String)
          : null,
      paidAt: json['paidAt'] != null
          ? DateTime.parse(json['paidAt'] as String)
          : null,
      description: json['description'] as String?,
      level: json['level'] as int?,
      maxDistributedPercentage:
          (json['maxDistributedPercentage'] as num?)?.toDouble(),
      calculationBreakdown: json['calculationBreakdown'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'userId': userId,
      'userName': userName,
      'associateId': associateId,
      'associateName': associateName,
      'associateRank': associateRank,
      'type': type,
      'amount': amount,
      'commissionAmount': commissionAmount ?? amount,
      'percentage': percentage,
      'sourceUserId': sourceUserId,
      'sourceUserName': sourceUserName,
      'leadId': leadId,
      'propertyId': propertyId,
      'plotId': plotId,
      'plotNumber': plotNumber,
      'colonyName': colonyName,
      'customerName': customerName,
      'bookingId': bookingId,
      'saleAmount': saleAmount,
      'saleDate': saleDate?.toIso8601String(),
      'commissionType': commissionType ?? type,
      'sellerId': sellerId,
      'sellerName': sellerName,
      'sellerRank': sellerRank,
      'relationship': relationship,
      'status': status,
      'createdAt': createdAt?.toIso8601String(),
      'paidAt': paidAt?.toIso8601String(),
      'description': description,
      'level': level,
      'maxDistributedPercentage': maxDistributedPercentage,
      'calculationBreakdown': calculationBreakdown,
    };
  }

  CommissionModel copyWith({
    String? id,
    String? userId,
    String? userName,
    String? associateId,
    String? associateName,
    String? associateRank,
    String? type,
    double? amount,
    double? commissionAmount,
    double? percentage,
    String? sourceUserId,
    String? sourceUserName,
    String? leadId,
    String? propertyId,
    String? plotId,
    String? plotNumber,
    String? colonyName,
    String? customerName,
    String? bookingId,
    double? saleAmount,
    DateTime? saleDate,
    String? commissionType,
    String? sellerId,
    String? sellerName,
    String? sellerRank,
    String? relationship,
    String? status,
    DateTime? createdAt,
    DateTime? paidAt,
    String? description,
    int? level,
    double? maxDistributedPercentage,
    String? calculationBreakdown,
  }) {
    return CommissionModel(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      userName: userName ?? this.userName,
      associateId: associateId ?? this.associateId,
      associateName: associateName ?? this.associateName,
      associateRank: associateRank ?? this.associateRank,
      type: type ?? this.type,
      amount: amount ?? this.amount,
      commissionAmount: commissionAmount ?? this.commissionAmount,
      percentage: percentage ?? this.percentage,
      sourceUserId: sourceUserId ?? this.sourceUserId,
      sourceUserName: sourceUserName ?? this.sourceUserName,
      leadId: leadId ?? this.leadId,
      propertyId: propertyId ?? this.propertyId,
      plotId: plotId ?? this.plotId,
      plotNumber: plotNumber ?? this.plotNumber,
      colonyName: colonyName ?? this.colonyName,
      customerName: customerName ?? this.customerName,
      bookingId: bookingId ?? this.bookingId,
      saleAmount: saleAmount ?? this.saleAmount,
      saleDate: saleDate ?? this.saleDate,
      commissionType: commissionType ?? this.commissionType,
      sellerId: sellerId ?? this.sellerId,
      sellerName: sellerName ?? this.sellerName,
      sellerRank: sellerRank ?? this.sellerRank,
      relationship: relationship ?? this.relationship,
      status: status ?? this.status,
      createdAt: createdAt ?? this.createdAt,
      paidAt: paidAt ?? this.paidAt,
      description: description ?? this.description,
      level: level ?? this.level,
      maxDistributedPercentage:
          maxDistributedPercentage ?? this.maxDistributedPercentage,
      calculationBreakdown: calculationBreakdown ?? this.calculationBreakdown,
    );
  }
}

/// Commission Summary for MLM
class CommissionSummary {
  final String? associateId;
  final double totalEarned;
  final double totalPaid;
  final double totalPending;
  final double totalHold;
  final int count;
  final int totalSales;
  final int directSales;
  final int indirectSales;
  final Map<String, double> byType;
  final Map<String, double> byLevel;
  final Map<String, double> byMonth;

  CommissionSummary({
    this.associateId,
    required this.totalEarned,
    required this.totalPaid,
    required this.totalPending,
    required this.totalHold,
    required this.count,
    this.totalSales = 0,
    this.directSales = 0,
    this.indirectSales = 0,
    this.byType = const {},
    this.byLevel = const {},
    this.byMonth = const {},
  });

  factory CommissionSummary.fromJson(Map<String, dynamic> json) {
    return CommissionSummary(
      associateId: json['associateId'] as String?,
      totalEarned: (json['totalEarned'] as num).toDouble(),
      totalPaid: (json['totalPaid'] as num).toDouble(),
      totalPending: (json['totalPending'] as num).toDouble(),
      totalHold: (json['totalHold'] as num).toDouble(),
      count: json['count'] as int,
      totalSales: json['totalSales'] as int? ?? 0,
      directSales: json['directSales'] as int? ?? 0,
      indirectSales: json['indirectSales'] as int? ?? 0,
      byType:
          (json['byType'] as Map<String, dynamic>?)?.cast<String, num>().map(
                    (k, v) => MapEntry(k, v.toDouble()),
                  ) ??
              {},
      byLevel:
          (json['byLevel'] as Map<String, dynamic>?)?.cast<String, num>().map(
                    (k, v) => MapEntry(k, v.toDouble()),
                  ) ??
              {},
      byMonth:
          (json['byMonth'] as Map<String, dynamic>?)?.cast<String, num>().map(
                    (k, v) => MapEntry(k, v.toDouble()),
                  ) ??
              {},
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'associateId': associateId,
      'totalEarned': totalEarned,
      'totalPaid': totalPaid,
      'totalPending': totalPending,
      'totalHold': totalHold,
      'count': count,
      'totalSales': totalSales,
      'directSales': directSales,
      'indirectSales': indirectSales,
      'byType': byType,
      'byLevel': byLevel,
      'byMonth': byMonth,
    };
  }

  CommissionSummary copyWith({
    String? associateId,
    double? totalEarned,
    double? totalPaid,
    double? totalPending,
    double? totalHold,
    int? count,
    int? totalSales,
    int? directSales,
    int? indirectSales,
    Map<String, double>? byType,
    Map<String, double>? byLevel,
    Map<String, double>? byMonth,
  }) {
    return CommissionSummary(
      associateId: associateId ?? this.associateId,
      totalEarned: totalEarned ?? this.totalEarned,
      totalPaid: totalPaid ?? this.totalPaid,
      totalPending: totalPending ?? this.totalPending,
      totalHold: totalHold ?? this.totalHold,
      count: count ?? this.count,
      totalSales: totalSales ?? this.totalSales,
      directSales: directSales ?? this.directSales,
      indirectSales: indirectSales ?? this.indirectSales,
      byType: byType ?? this.byType,
      byLevel: byLevel ?? this.byLevel,
      byMonth: byMonth ?? this.byMonth,
    );
  }
}
