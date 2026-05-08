// import 'package:json_annotation/json_annotation.dart'; // Temporarily removed due to build_runner issues

// part 'user_model.g.dart'; // Temporarily removed due to build_runner issues

// @JsonSerializable() // Temporarily removed due to build_runner issues
class User {
  final String userId;
  final String name;
  final String email;
  final String? phone;
  final String rank;
  final double target;
  final String? avatar;
  final String createdAt;
  final String updatedAt;

  const User({
    required this.userId,
    required this.name,
    required this.email,
    this.phone,
    required this.rank,
    required this.target,
    this.avatar,
    required this.createdAt,
    required this.updatedAt,
  });

  factory User.fromJson(Map<String, dynamic> json) => User(
        userId: json['user_id'] as String? ?? '',
        name: json['name'] as String? ?? '',
        email: json['email'] as String? ?? '',
        phone: json['phone'] as String?,
        rank: json['rank'] as String? ?? '',
        target: (json['target'] as num?)?.toDouble() ?? 0.0,
        avatar: json['avatar'] as String?,
        createdAt: json['created_at'] as String? ?? '',
        updatedAt: json['updated_at'] as String? ?? '',
      );

  Map<String, dynamic> toJson() => {
        'user_id': userId,
        'name': name,
        'email': email,
        'phone': phone,
        'rank': rank,
        'target': target,
        'avatar': avatar,
        'created_at': createdAt,
        'updated_at': updatedAt,
      };

  User copyWith({
    String? userId,
    String? name,
    String? email,
    String? phone,
    String? rank,
    double? target,
    String? avatar,
    String? createdAt,
    String? updatedAt,
  }) {
    return User(
      userId: userId ?? this.userId,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      rank: rank ?? this.rank,
      target: target ?? this.target,
      avatar: avatar ?? this.avatar,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  // Get rank commission rate
  double get commissionRate {
    switch (rank) {
      case 'Associate':
        return 6.0;
      case 'Sr. Associate':
        return 8.0;
      case 'BDM':
        return 10.0;
      case 'Sr. BDM':
        return 12.0;
      case 'Vice President':
        return 15.0;
      case 'President':
        return 18.0;
      case 'Site Manager':
        return 20.0;
      default:
        return 0.0;
    }
  }

  // Check if user is senior to another user
  bool isSeniorTo(String otherRank) {
    final rankHierarchy = [
      'Associate',
      'Sr. Associate',
      'BDM',
      'Sr. BDM',
      'Vice President',
      'President',
      'Site Manager',
    ];

    final currentIndex = rankHierarchy.indexOf(rank);
    final otherIndex = rankHierarchy.indexOf(otherRank);

    return currentIndex > otherIndex;
  }

  // Calculate differential commission
  double calculateDifferentialCommission(String juniorRank, double saleAmount) {
    if (!isSeniorTo(juniorRank)) return 0.0;

    final seniorRate = commissionRate;
    final juniorRate = getCommissionRateForRank(juniorRank);
    final differential = seniorRate - juniorRate;

    return saleAmount * (differential / 100);
  }

  static double getCommissionRateForRank(String rank) {
    switch (rank) {
      case 'Associate':
        return 6.0;
      case 'Sr. Associate':
        return 8.0;
      case 'BDM':
        return 10.0;
      case 'Sr. BDM':
        return 12.0;
      case 'Vice President':
        return 15.0;
      case 'President':
        return 18.0;
      case 'Site Manager':
        return 20.0;
      default:
        return 0.0;
    }
  }
}
