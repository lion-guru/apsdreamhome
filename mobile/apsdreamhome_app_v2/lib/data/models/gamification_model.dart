import 'package:freezed_annotation/freezed_annotation.dart';

part 'gamification_model.freezed.dart';
part 'gamification_model.g.dart';

/// Gamification Model - Points, Badges, Rewards System
@freezed
class GamificationModel with _$GamificationModel {
  const factory GamificationModel({
    required String userId,
    required int totalPoints,
    required int availablePoints,
    required int redeemedPoints,
    required int currentLevel,
    required String currentRank,
    
    // Progress
    int? pointsToNextLevel,
    double? levelProgressPercentage,
    
    // Streaks
    int? currentStreak,
    int? longestStreak,
    DateTime? lastActivityDate,
    
    // Achievements
    List<Achievement>? achievements,
    List<Badge>? badges,
    
    // Recent Activity
    List<PointsTransaction>? recentTransactions,
    
    // Leaderboard
    int? leaderboardRank,
    int? totalParticipants,
    
    // Timestamps
    DateTime? createdAt,
    DateTime? updatedAt,
  }) = _GamificationModel;

  factory GamificationModel.fromJson(Map<String, dynamic> json) =>
      _$GamificationModelFromJson(json);
}

@freezed
class PointsTransaction with _$PointsTransaction {
  const factory PointsTransaction({
    required String id,
    required String userId,
    required int points,
    required String type, // earned, redeemed, adjusted
    required String activityType,
    String? description,
    String? metadata,
    int? balanceBefore,
    int? balanceAfter,
    DateTime? createdAt,
  }) = _PointsTransaction;

  factory PointsTransaction.fromJson(Map<String, dynamic> json) =>
      _$PointsTransactionFromJson(json);
}

@freezed
class Achievement with _$Achievement {
  const factory Achievement({
    required String id,
    required String name,
    required String description,
    required String icon,
    required int pointsReward,
    required String category, // sales, recruitment, activity, training
    required String condition,
    int? targetValue,
    int? currentValue,
    double? progressPercentage,
    bool? isCompleted,
    DateTime? completedAt,
    DateTime? createdAt,
  }) = _Achievement;

  factory Achievement.fromJson(Map<String, dynamic> json) =>
      _$AchievementFromJson(json);
}

@freezed
class Badge with _$Badge {
  const factory Badge({
    required String id,
    required String name,
    required String description,
    required String icon,
    required String rarity, // bronze, silver, gold, platinum, diamond
    required String category,
    DateTime? earnedAt,
    DateTime? createdAt,
  }) = _Badge;

  factory Badge.fromJson(Map<String, dynamic> json) =>
      _$BadgeFromJson(json);
}

@freezed
class Reward with _$Reward {
  const factory Reward({
    required String id,
    required String name,
    required String description,
    required String imageUrl,
    required int pointsCost,
    required int stockQuantity,
    String? category, // merchandise, vouchers, cash, experience
    String? termsAndConditions,
    bool? isActive,
    DateTime? validFrom,
    DateTime? validUntil,
    DateTime? createdAt,
  }) = _Reward;

  factory Reward.fromJson(Map<String, dynamic> json) =>
      _$RewardFromJson(json);
}

@freezed
class RewardRedemption with _$RewardRedemption {
  const factory RewardRedemption({
    required String id,
    required String userId,
    required String rewardId,
    required String rewardName,
    required int pointsSpent,
    required String status, // pending, processing, shipped, delivered, cancelled
    String? deliveryAddress,
    String? trackingNumber,
    DateTime? requestedAt,
    DateTime? processedAt,
    DateTime? shippedAt,
    DateTime? deliveredAt,
    String? notes,
  }) = _RewardRedemption;

  factory RewardRedemption.fromJson(Map<String, dynamic> json) =>
      _$RewardRedemptionFromJson(json);
}

// Leaderboard Entry
@freezed
class LeaderboardEntry with _$LeaderboardEntry {
  const factory LeaderboardEntry({
    required String userId,
    required String userName,
    required String userPhoto,
    required int rank,
    required int totalPoints,
    required int level,
    required int salesCount,
    required int recruitsCount,
    bool? isCurrentUser,
  }) = _LeaderboardEntry;

  factory LeaderboardEntry.fromJson(Map<String, dynamic> json) =>
      _$LeaderboardEntryFromJson(json);
}

// Activity Types for Points
class ActivityPoints {
  static const Map<String, int> points = {
    'first_login': 10,
    'daily_login': 5,
    'property_view': 2,
    'lead_generated': 20,
    'sale_completed': 100,
    'recruitment': 50,
    'training_completed': 30,
    'challenge_completed': 40,
    'document_upload': 5,
    'profile_completion': 25,
    'referral_signup': 15,
    'app_share': 10,
    'review_submitted': 15,
    'site_visit': 20,
    'follow_up_completed': 10,
  };
  
  static int getPoints(String activityType) {
    return points[activityType] ?? 5;
  }
}
