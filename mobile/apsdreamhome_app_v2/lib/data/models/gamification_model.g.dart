// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'gamification_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$GamificationModelImpl _$$GamificationModelImplFromJson(
  Map<String, dynamic> json,
) => _$GamificationModelImpl(
  userId: json['userId'] as String? ?? '',
  totalPoints: (json['totalPoints'] as num?)?.toInt() ?? 0,
  availablePoints: (json['availablePoints'] as num?)?.toInt() ?? 0,
  redeemedPoints: (json['redeemedPoints'] as num?)?.toInt() ?? 0,
  currentLevel: (json['currentLevel'] as num?)?.toInt() ?? 0,
  currentRank: json['currentRank'] as String? ?? '',
  pointsToNextLevel: (json['pointsToNextLevel'] as num?)?.toInt(),
  levelProgressPercentage: (json['levelProgressPercentage'] as num?)
      ?.toDouble(),
  currentStreak: (json['currentStreak'] as num?)?.toInt(),
  longestStreak: (json['longestStreak'] as num?)?.toInt(),
  lastActivityDate: json['lastActivityDate'] == null
      ? null
      : DateTime.parse(json['lastActivityDate'] as String),
  achievements: (json['achievements'] as List<dynamic>?)
      ?.map((e) => Achievement.fromJson(e as Map<String, dynamic>))
      .toList(),
  badges: (json['badges'] as List<dynamic>?)
      ?.map((e) => Badge.fromJson(e as Map<String, dynamic>))
      .toList(),
  recentTransactions: (json['recentTransactions'] as List<dynamic>?)
      ?.map((e) => PointsTransaction.fromJson(e as Map<String, dynamic>))
      .toList(),
  leaderboardRank: (json['leaderboardRank'] as num?)?.toInt(),
  totalParticipants: (json['totalParticipants'] as num?)?.toInt(),
  createdAt: json['createdAt'] == null
      ? null
      : DateTime.parse(json['createdAt'] as String),
  updatedAt: json['updatedAt'] == null
      ? null
      : DateTime.parse(json['updatedAt'] as String),
);

Map<String, dynamic> _$$GamificationModelImplToJson(
  _$GamificationModelImpl instance,
) => <String, dynamic>{
  'userId': instance.userId,
  'totalPoints': instance.totalPoints,
  'availablePoints': instance.availablePoints,
  'redeemedPoints': instance.redeemedPoints,
  'currentLevel': instance.currentLevel,
  'currentRank': instance.currentRank,
  'pointsToNextLevel': instance.pointsToNextLevel,
  'levelProgressPercentage': instance.levelProgressPercentage,
  'currentStreak': instance.currentStreak,
  'longestStreak': instance.longestStreak,
  'lastActivityDate': instance.lastActivityDate?.toIso8601String(),
  'achievements': instance.achievements,
  'badges': instance.badges,
  'recentTransactions': instance.recentTransactions,
  'leaderboardRank': instance.leaderboardRank,
  'totalParticipants': instance.totalParticipants,
  'createdAt': instance.createdAt?.toIso8601String(),
  'updatedAt': instance.updatedAt?.toIso8601String(),
};

_$PointsTransactionImpl _$$PointsTransactionImplFromJson(
  Map<String, dynamic> json,
) => _$PointsTransactionImpl(
  id: json['id'] as String? ?? '',
  userId: json['userId'] as String? ?? '',
  points: (json['points'] as num?)?.toInt() ?? 0,
  type: json['type'] as String? ?? '',
  activityType: json['activityType'] as String? ?? '',
  description: json['description'] as String?,
  metadata: json['metadata'] as String?,
  balanceBefore: (json['balanceBefore'] as num?)?.toInt(),
  balanceAfter: (json['balanceAfter'] as num?)?.toInt(),
  createdAt: json['createdAt'] == null
      ? null
      : DateTime.parse(json['createdAt'] as String),
);

Map<String, dynamic> _$$PointsTransactionImplToJson(
  _$PointsTransactionImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'userId': instance.userId,
  'points': instance.points,
  'type': instance.type,
  'activityType': instance.activityType,
  'description': instance.description,
  'metadata': instance.metadata,
  'balanceBefore': instance.balanceBefore,
  'balanceAfter': instance.balanceAfter,
  'createdAt': instance.createdAt?.toIso8601String(),
};

_$AchievementImpl _$$AchievementImplFromJson(Map<String, dynamic> json) =>
    _$AchievementImpl(
      id: json['id'] as String? ?? '',
      name: json['name'] as String? ?? '',
      description: json['description'] as String? ?? '',
      icon: json['icon'] as String? ?? '',
      pointsReward: (json['pointsReward'] as num?)?.toInt() ?? 0,
      category: json['category'] as String? ?? '',
      condition: json['condition'] as String? ?? '',
      targetValue: (json['targetValue'] as num?)?.toInt(),
      currentValue: (json['currentValue'] as num?)?.toInt(),
      progressPercentage: (json['progressPercentage'] as num?)?.toDouble(),
      isCompleted: json['isCompleted'] as bool?,
      completedAt: json['completedAt'] == null
          ? null
          : DateTime.parse(json['completedAt'] as String),
      createdAt: json['createdAt'] == null
          ? null
          : DateTime.parse(json['createdAt'] as String),
    );

Map<String, dynamic> _$$AchievementImplToJson(_$AchievementImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'description': instance.description,
      'icon': instance.icon,
      'pointsReward': instance.pointsReward,
      'category': instance.category,
      'condition': instance.condition,
      'targetValue': instance.targetValue,
      'currentValue': instance.currentValue,
      'progressPercentage': instance.progressPercentage,
      'isCompleted': instance.isCompleted,
      'completedAt': instance.completedAt?.toIso8601String(),
      'createdAt': instance.createdAt?.toIso8601String(),
    };

_$BadgeImpl _$$BadgeImplFromJson(Map<String, dynamic> json) => _$BadgeImpl(
  id: json['id'] as String? ?? '',
  name: json['name'] as String? ?? '',
  description: json['description'] as String? ?? '',
  icon: json['icon'] as String? ?? '',
  rarity: json['rarity'] as String? ?? '',
  category: json['category'] as String? ?? '',
  earnedAt: json['earnedAt'] == null
      ? null
      : DateTime.parse(json['earnedAt'] as String),
  createdAt: json['createdAt'] == null
      ? null
      : DateTime.parse(json['createdAt'] as String),
);

Map<String, dynamic> _$$BadgeImplToJson(_$BadgeImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'description': instance.description,
      'icon': instance.icon,
      'rarity': instance.rarity,
      'category': instance.category,
      'earnedAt': instance.earnedAt?.toIso8601String(),
      'createdAt': instance.createdAt?.toIso8601String(),
    };

_$RewardImpl _$$RewardImplFromJson(Map<String, dynamic> json) => _$RewardImpl(
  id: json['id'] as String? ?? '',
  name: json['name'] as String? ?? '',
  description: json['description'] as String? ?? '',
  imageUrl: json['imageUrl'] as String? ?? '',
  pointsCost: (json['pointsCost'] as num?)?.toInt() ?? 0,
  stockQuantity: (json['stockQuantity'] as num?)?.toInt() ?? 0,
  category: json['category'] as String?,
  termsAndConditions: json['termsAndConditions'] as String?,
  isActive: json['isActive'] as bool?,
  validFrom: json['validFrom'] == null
      ? null
      : DateTime.parse(json['validFrom'] as String),
  validUntil: json['validUntil'] == null
      ? null
      : DateTime.parse(json['validUntil'] as String),
  createdAt: json['createdAt'] == null
      ? null
      : DateTime.parse(json['createdAt'] as String),
);

Map<String, dynamic> _$$RewardImplToJson(_$RewardImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'description': instance.description,
      'imageUrl': instance.imageUrl,
      'pointsCost': instance.pointsCost,
      'stockQuantity': instance.stockQuantity,
      'category': instance.category,
      'termsAndConditions': instance.termsAndConditions,
      'isActive': instance.isActive,
      'validFrom': instance.validFrom?.toIso8601String(),
      'validUntil': instance.validUntil?.toIso8601String(),
      'createdAt': instance.createdAt?.toIso8601String(),
    };

_$RewardRedemptionImpl _$$RewardRedemptionImplFromJson(
  Map<String, dynamic> json,
) => _$RewardRedemptionImpl(
  id: json['id'] as String? ?? '',
  userId: json['userId'] as String? ?? '',
  rewardId: json['rewardId'] as String? ?? '',
  rewardName: json['rewardName'] as String? ?? '',
  pointsSpent: (json['pointsSpent'] as num?)?.toInt() ?? 0,
  status: json['status'] as String? ?? '',
  deliveryAddress: json['deliveryAddress'] as String?,
  trackingNumber: json['trackingNumber'] as String?,
  requestedAt: json['requestedAt'] == null
      ? null
      : DateTime.parse(json['requestedAt'] as String),
  processedAt: json['processedAt'] == null
      ? null
      : DateTime.parse(json['processedAt'] as String),
  shippedAt: json['shippedAt'] == null
      ? null
      : DateTime.parse(json['shippedAt'] as String),
  deliveredAt: json['deliveredAt'] == null
      ? null
      : DateTime.parse(json['deliveredAt'] as String),
  notes: json['notes'] as String?,
);

Map<String, dynamic> _$$RewardRedemptionImplToJson(
  _$RewardRedemptionImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'userId': instance.userId,
  'rewardId': instance.rewardId,
  'rewardName': instance.rewardName,
  'pointsSpent': instance.pointsSpent,
  'status': instance.status,
  'deliveryAddress': instance.deliveryAddress,
  'trackingNumber': instance.trackingNumber,
  'requestedAt': instance.requestedAt?.toIso8601String(),
  'processedAt': instance.processedAt?.toIso8601String(),
  'shippedAt': instance.shippedAt?.toIso8601String(),
  'deliveredAt': instance.deliveredAt?.toIso8601String(),
  'notes': instance.notes,
};

_$LeaderboardEntryImpl _$$LeaderboardEntryImplFromJson(
  Map<String, dynamic> json,
) => _$LeaderboardEntryImpl(
  userId: json['userId'] as String? ?? '',
  userName: json['userName'] as String? ?? '',
  userPhoto: json['userPhoto'] as String? ?? '',
  rank: (json['rank'] as num?)?.toInt() ?? 0,
  totalPoints: (json['totalPoints'] as num?)?.toInt() ?? 0,
  level: (json['level'] as num?)?.toInt() ?? 0,
  salesCount: (json['salesCount'] as num?)?.toInt() ?? 0,
  recruitsCount: (json['recruitsCount'] as num?)?.toInt() ?? 0,
  isCurrentUser: json['isCurrentUser'] as bool?,
);

Map<String, dynamic> _$$LeaderboardEntryImplToJson(
  _$LeaderboardEntryImpl instance,
) => <String, dynamic>{
  'userId': instance.userId,
  'userName': instance.userName,
  'userPhoto': instance.userPhoto,
  'rank': instance.rank,
  'totalPoints': instance.totalPoints,
  'level': instance.level,
  'salesCount': instance.salesCount,
  'recruitsCount': instance.recruitsCount,
  'isCurrentUser': instance.isCurrentUser,
};
