import 'package:json_annotation/json_annotation.dart';

part 'user_model.g.dart';

// Create UserModel type alias for compatibility
typedef UserModel = User;

@JsonSerializable()
class User {
  final String userId;
  final String name;
  final String email;
  final String? phone;
  final String rank;
  final String? role;
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
    this.role,
    required this.target,
    this.avatar,
    required this.createdAt,
    required this.updatedAt,
  });

  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);
  Map<String, dynamic> toJson() => _$UserToJson(this);

  User copyWith({
    String? userId,
    String? name,
    String? email,
    String? phone,
    String? role,
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
      role: role ?? this.role,
      rank: rank ?? this.rank,
      target: target ?? this.target,
      avatar: avatar ?? this.avatar,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  // Alias getters for compatibility
  String get id => userId;
  String? get profileImage => null;
  String? get referralCode => null;
  int get totalSales => 0;

  // Role helper getters — use role column (users.role) first, fallback to rank
  bool get isCustomer =>
      (role ?? rank) == 'customer' || (role ?? rank) == 'Customer';
  bool get isAgent => (role ?? rank) == 'agent' || (role ?? rank) == 'Agent';
  bool get isTelecaller =>
      (role ?? rank) == 'telecaller' ||
      (role ?? rank) == 'Telecaller' ||
      (role ?? rank) == 'telecalling' ||
      (role ?? rank) == 'Telecalling';
  bool get isEmployee =>
      (role ?? rank) == 'employee' ||
      (role ?? rank) == 'Employee' ||
      (role ?? rank) == 'hr' ||
      (role ?? rank) == 'HR' ||
      isTelecaller;
  bool get isAdmin =>
      (role ?? rank) == 'admin' ||
      (role ?? rank) == 'Admin' ||
      (role ?? rank) == 'super_admin' ||
      (role ?? rank) == 'manager' ||
      (role ?? rank) == 'Manager';
  bool get isAssociate =>
      (role ?? rank) == 'associate' ||
      (role ?? rank) == 'Associate' ||
      (role ?? rank) == 'Associate';

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
