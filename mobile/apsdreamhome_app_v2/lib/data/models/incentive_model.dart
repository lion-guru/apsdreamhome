class Incentive {
  final int id;
  final int userId;
  final int month;
  final int year;
  final String rankAtTime;
  final double targetBusiness;
  final double achievedBusiness;
  final double incentiveAmount;
  final String status;
  final String? remarks;
  final String createdAt;

  const Incentive({
    required this.id,
    required this.userId,
    required this.month,
    required this.year,
    required this.rankAtTime,
    required this.targetBusiness,
    required this.achievedBusiness,
    required this.incentiveAmount,
    required this.status,
    this.remarks,
    required this.createdAt,
  });

  factory Incentive.fromJson(Map<String, dynamic> json) {
    return Incentive(
      id: _parseInt(json['id']),
      userId: _parseInt(json['user_id']),
      month: _parseInt(json['month']),
      year: _parseInt(json['year']),
      rankAtTime: json['rank_at_time'] as String? ?? '',
      targetBusiness: _parseDouble(json['target_business']),
      achievedBusiness: _parseDouble(json['achieved_business']),
      incentiveAmount: _parseDouble(json['incentive_amount']),
      status: json['status'] as String? ?? 'pending',
      remarks: json['remarks'] as String?,
      createdAt: json['created_at'] as String? ?? '',
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'month': month,
      'year': year,
      'rank_at_time': rankAtTime,
      'target_business': targetBusiness,
      'achieved_business': achievedBusiness,
      'incentive_amount': incentiveAmount,
      'status': status,
      'remarks': remarks,
      'created_at': createdAt,
    };
  }

  double get progress => targetBusiness > 0
      ? (achievedBusiness / targetBusiness).clamp(0.0, 1.0)
      : 0.0;
  bool get isAchieved => achievedBusiness >= targetBusiness;

  String get monthName {
    const months = [
      '',
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December'
    ];
    return months[month];
  }
}
