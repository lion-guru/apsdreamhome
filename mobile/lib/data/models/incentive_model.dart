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
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      userId: json['user_id'] is int ? json['user_id'] : int.parse(json['user_id'].toString()),
      month: json['month'] is int ? json['month'] : int.parse(json['month'].toString()),
      year: json['year'] is int ? json['year'] : int.parse(json['year'].toString()),
      rankAtTime: json['rank_at_time'] ?? '',
      targetBusiness: double.parse(json['target_business'].toString()),
      achievedBusiness: double.parse(json['achieved_business'].toString()),
      incentiveAmount: double.parse(json['incentive_amount'].toString()),
      status: json['status'] ?? 'pending',
      remarks: json['remarks'],
      createdAt: json['created_at'] ?? '',
    );
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

  double get progress => targetBusiness > 0 ? (achievedBusiness / targetBusiness).clamp(0.0, 1.0) : 0.0;
  bool get isAchieved => achievedBusiness >= targetBusiness;
  
  String get monthName {
    const months = [
      '', 'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return months[month];
  }
}
