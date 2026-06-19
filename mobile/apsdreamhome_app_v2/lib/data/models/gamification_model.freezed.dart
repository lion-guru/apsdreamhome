// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'gamification_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
  'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models',
);

GamificationModel _$GamificationModelFromJson(Map<String, dynamic> json) {
  return _GamificationModel.fromJson(json);
}

/// @nodoc
mixin _$GamificationModel {
  String get userId => throw _privateConstructorUsedError;
  int get totalPoints => throw _privateConstructorUsedError;
  int get availablePoints => throw _privateConstructorUsedError;
  int get redeemedPoints => throw _privateConstructorUsedError;
  int get currentLevel => throw _privateConstructorUsedError;
  String get currentRank => throw _privateConstructorUsedError; // Progress
  int? get pointsToNextLevel => throw _privateConstructorUsedError;
  double? get levelProgressPercentage =>
      throw _privateConstructorUsedError; // Streaks
  int? get currentStreak => throw _privateConstructorUsedError;
  int? get longestStreak => throw _privateConstructorUsedError;
  DateTime? get lastActivityDate =>
      throw _privateConstructorUsedError; // Achievements
  List<Achievement>? get achievements => throw _privateConstructorUsedError;
  List<Badge>? get badges =>
      throw _privateConstructorUsedError; // Recent Activity
  List<PointsTransaction>? get recentTransactions =>
      throw _privateConstructorUsedError; // Leaderboard
  int? get leaderboardRank => throw _privateConstructorUsedError;
  int? get totalParticipants =>
      throw _privateConstructorUsedError; // Timestamps
  DateTime? get createdAt => throw _privateConstructorUsedError;
  DateTime? get updatedAt => throw _privateConstructorUsedError;

  /// Serializes this GamificationModel to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of GamificationModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $GamificationModelCopyWith<GamificationModel> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $GamificationModelCopyWith<$Res> {
  factory $GamificationModelCopyWith(
    GamificationModel value,
    $Res Function(GamificationModel) then,
  ) = _$GamificationModelCopyWithImpl<$Res, GamificationModel>;
  @useResult
  $Res call({
    String userId,
    int totalPoints,
    int availablePoints,
    int redeemedPoints,
    int currentLevel,
    String currentRank,
    int? pointsToNextLevel,
    double? levelProgressPercentage,
    int? currentStreak,
    int? longestStreak,
    DateTime? lastActivityDate,
    List<Achievement>? achievements,
    List<Badge>? badges,
    List<PointsTransaction>? recentTransactions,
    int? leaderboardRank,
    int? totalParticipants,
    DateTime? createdAt,
    DateTime? updatedAt,
  });
}

/// @nodoc
class _$GamificationModelCopyWithImpl<$Res, $Val extends GamificationModel>
    implements $GamificationModelCopyWith<$Res> {
  _$GamificationModelCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of GamificationModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? userId = null,
    Object? totalPoints = null,
    Object? availablePoints = null,
    Object? redeemedPoints = null,
    Object? currentLevel = null,
    Object? currentRank = null,
    Object? pointsToNextLevel = freezed,
    Object? levelProgressPercentage = freezed,
    Object? currentStreak = freezed,
    Object? longestStreak = freezed,
    Object? lastActivityDate = freezed,
    Object? achievements = freezed,
    Object? badges = freezed,
    Object? recentTransactions = freezed,
    Object? leaderboardRank = freezed,
    Object? totalParticipants = freezed,
    Object? createdAt = freezed,
    Object? updatedAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            userId: null == userId
                ? _value.userId
                : userId // ignore: cast_nullable_to_non_nullable
                      as String,
            totalPoints: null == totalPoints
                ? _value.totalPoints
                : totalPoints // ignore: cast_nullable_to_non_nullable
                      as int,
            availablePoints: null == availablePoints
                ? _value.availablePoints
                : availablePoints // ignore: cast_nullable_to_non_nullable
                      as int,
            redeemedPoints: null == redeemedPoints
                ? _value.redeemedPoints
                : redeemedPoints // ignore: cast_nullable_to_non_nullable
                      as int,
            currentLevel: null == currentLevel
                ? _value.currentLevel
                : currentLevel // ignore: cast_nullable_to_non_nullable
                      as int,
            currentRank: null == currentRank
                ? _value.currentRank
                : currentRank // ignore: cast_nullable_to_non_nullable
                      as String,
            pointsToNextLevel: freezed == pointsToNextLevel
                ? _value.pointsToNextLevel
                : pointsToNextLevel // ignore: cast_nullable_to_non_nullable
                      as int?,
            levelProgressPercentage: freezed == levelProgressPercentage
                ? _value.levelProgressPercentage
                : levelProgressPercentage // ignore: cast_nullable_to_non_nullable
                      as double?,
            currentStreak: freezed == currentStreak
                ? _value.currentStreak
                : currentStreak // ignore: cast_nullable_to_non_nullable
                      as int?,
            longestStreak: freezed == longestStreak
                ? _value.longestStreak
                : longestStreak // ignore: cast_nullable_to_non_nullable
                      as int?,
            lastActivityDate: freezed == lastActivityDate
                ? _value.lastActivityDate
                : lastActivityDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            achievements: freezed == achievements
                ? _value.achievements
                : achievements // ignore: cast_nullable_to_non_nullable
                      as List<Achievement>?,
            badges: freezed == badges
                ? _value.badges
                : badges // ignore: cast_nullable_to_non_nullable
                      as List<Badge>?,
            recentTransactions: freezed == recentTransactions
                ? _value.recentTransactions
                : recentTransactions // ignore: cast_nullable_to_non_nullable
                      as List<PointsTransaction>?,
            leaderboardRank: freezed == leaderboardRank
                ? _value.leaderboardRank
                : leaderboardRank // ignore: cast_nullable_to_non_nullable
                      as int?,
            totalParticipants: freezed == totalParticipants
                ? _value.totalParticipants
                : totalParticipants // ignore: cast_nullable_to_non_nullable
                      as int?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            updatedAt: freezed == updatedAt
                ? _value.updatedAt
                : updatedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$GamificationModelImplCopyWith<$Res>
    implements $GamificationModelCopyWith<$Res> {
  factory _$$GamificationModelImplCopyWith(
    _$GamificationModelImpl value,
    $Res Function(_$GamificationModelImpl) then,
  ) = __$$GamificationModelImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String userId,
    int totalPoints,
    int availablePoints,
    int redeemedPoints,
    int currentLevel,
    String currentRank,
    int? pointsToNextLevel,
    double? levelProgressPercentage,
    int? currentStreak,
    int? longestStreak,
    DateTime? lastActivityDate,
    List<Achievement>? achievements,
    List<Badge>? badges,
    List<PointsTransaction>? recentTransactions,
    int? leaderboardRank,
    int? totalParticipants,
    DateTime? createdAt,
    DateTime? updatedAt,
  });
}

/// @nodoc
class __$$GamificationModelImplCopyWithImpl<$Res>
    extends _$GamificationModelCopyWithImpl<$Res, _$GamificationModelImpl>
    implements _$$GamificationModelImplCopyWith<$Res> {
  __$$GamificationModelImplCopyWithImpl(
    _$GamificationModelImpl _value,
    $Res Function(_$GamificationModelImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of GamificationModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? userId = null,
    Object? totalPoints = null,
    Object? availablePoints = null,
    Object? redeemedPoints = null,
    Object? currentLevel = null,
    Object? currentRank = null,
    Object? pointsToNextLevel = freezed,
    Object? levelProgressPercentage = freezed,
    Object? currentStreak = freezed,
    Object? longestStreak = freezed,
    Object? lastActivityDate = freezed,
    Object? achievements = freezed,
    Object? badges = freezed,
    Object? recentTransactions = freezed,
    Object? leaderboardRank = freezed,
    Object? totalParticipants = freezed,
    Object? createdAt = freezed,
    Object? updatedAt = freezed,
  }) {
    return _then(
      _$GamificationModelImpl(
        userId: null == userId
            ? _value.userId
            : userId // ignore: cast_nullable_to_non_nullable
                  as String,
        totalPoints: null == totalPoints
            ? _value.totalPoints
            : totalPoints // ignore: cast_nullable_to_non_nullable
                  as int,
        availablePoints: null == availablePoints
            ? _value.availablePoints
            : availablePoints // ignore: cast_nullable_to_non_nullable
                  as int,
        redeemedPoints: null == redeemedPoints
            ? _value.redeemedPoints
            : redeemedPoints // ignore: cast_nullable_to_non_nullable
                  as int,
        currentLevel: null == currentLevel
            ? _value.currentLevel
            : currentLevel // ignore: cast_nullable_to_non_nullable
                  as int,
        currentRank: null == currentRank
            ? _value.currentRank
            : currentRank // ignore: cast_nullable_to_non_nullable
                  as String,
        pointsToNextLevel: freezed == pointsToNextLevel
            ? _value.pointsToNextLevel
            : pointsToNextLevel // ignore: cast_nullable_to_non_nullable
                  as int?,
        levelProgressPercentage: freezed == levelProgressPercentage
            ? _value.levelProgressPercentage
            : levelProgressPercentage // ignore: cast_nullable_to_non_nullable
                  as double?,
        currentStreak: freezed == currentStreak
            ? _value.currentStreak
            : currentStreak // ignore: cast_nullable_to_non_nullable
                  as int?,
        longestStreak: freezed == longestStreak
            ? _value.longestStreak
            : longestStreak // ignore: cast_nullable_to_non_nullable
                  as int?,
        lastActivityDate: freezed == lastActivityDate
            ? _value.lastActivityDate
            : lastActivityDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        achievements: freezed == achievements
            ? _value._achievements
            : achievements // ignore: cast_nullable_to_non_nullable
                  as List<Achievement>?,
        badges: freezed == badges
            ? _value._badges
            : badges // ignore: cast_nullable_to_non_nullable
                  as List<Badge>?,
        recentTransactions: freezed == recentTransactions
            ? _value._recentTransactions
            : recentTransactions // ignore: cast_nullable_to_non_nullable
                  as List<PointsTransaction>?,
        leaderboardRank: freezed == leaderboardRank
            ? _value.leaderboardRank
            : leaderboardRank // ignore: cast_nullable_to_non_nullable
                  as int?,
        totalParticipants: freezed == totalParticipants
            ? _value.totalParticipants
            : totalParticipants // ignore: cast_nullable_to_non_nullable
                  as int?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        updatedAt: freezed == updatedAt
            ? _value.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$GamificationModelImpl implements _GamificationModel {
  const _$GamificationModelImpl({
    required this.userId,
    required this.totalPoints,
    required this.availablePoints,
    required this.redeemedPoints,
    required this.currentLevel,
    required this.currentRank,
    this.pointsToNextLevel,
    this.levelProgressPercentage,
    this.currentStreak,
    this.longestStreak,
    this.lastActivityDate,
    final List<Achievement>? achievements,
    final List<Badge>? badges,
    final List<PointsTransaction>? recentTransactions,
    this.leaderboardRank,
    this.totalParticipants,
    this.createdAt,
    this.updatedAt,
  }) : _achievements = achievements,
       _badges = badges,
       _recentTransactions = recentTransactions;

  factory _$GamificationModelImpl.fromJson(Map<String, dynamic> json) =>
      _$$GamificationModelImplFromJson(json);

  @override
  final String userId;
  @override
  final int totalPoints;
  @override
  final int availablePoints;
  @override
  final int redeemedPoints;
  @override
  final int currentLevel;
  @override
  final String currentRank;
  // Progress
  @override
  final int? pointsToNextLevel;
  @override
  final double? levelProgressPercentage;
  // Streaks
  @override
  final int? currentStreak;
  @override
  final int? longestStreak;
  @override
  final DateTime? lastActivityDate;
  // Achievements
  final List<Achievement>? _achievements;
  // Achievements
  @override
  List<Achievement>? get achievements {
    final value = _achievements;
    if (value == null) return null;
    if (_achievements is EqualUnmodifiableListView) return _achievements;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  final List<Badge>? _badges;
  @override
  List<Badge>? get badges {
    final value = _badges;
    if (value == null) return null;
    if (_badges is EqualUnmodifiableListView) return _badges;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  // Recent Activity
  final List<PointsTransaction>? _recentTransactions;
  // Recent Activity
  @override
  List<PointsTransaction>? get recentTransactions {
    final value = _recentTransactions;
    if (value == null) return null;
    if (_recentTransactions is EqualUnmodifiableListView)
      return _recentTransactions;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  // Leaderboard
  @override
  final int? leaderboardRank;
  @override
  final int? totalParticipants;
  // Timestamps
  @override
  final DateTime? createdAt;
  @override
  final DateTime? updatedAt;

  @override
  String toString() {
    return 'GamificationModel(userId: $userId, totalPoints: $totalPoints, availablePoints: $availablePoints, redeemedPoints: $redeemedPoints, currentLevel: $currentLevel, currentRank: $currentRank, pointsToNextLevel: $pointsToNextLevel, levelProgressPercentage: $levelProgressPercentage, currentStreak: $currentStreak, longestStreak: $longestStreak, lastActivityDate: $lastActivityDate, achievements: $achievements, badges: $badges, recentTransactions: $recentTransactions, leaderboardRank: $leaderboardRank, totalParticipants: $totalParticipants, createdAt: $createdAt, updatedAt: $updatedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$GamificationModelImpl &&
            (identical(other.userId, userId) || other.userId == userId) &&
            (identical(other.totalPoints, totalPoints) ||
                other.totalPoints == totalPoints) &&
            (identical(other.availablePoints, availablePoints) ||
                other.availablePoints == availablePoints) &&
            (identical(other.redeemedPoints, redeemedPoints) ||
                other.redeemedPoints == redeemedPoints) &&
            (identical(other.currentLevel, currentLevel) ||
                other.currentLevel == currentLevel) &&
            (identical(other.currentRank, currentRank) ||
                other.currentRank == currentRank) &&
            (identical(other.pointsToNextLevel, pointsToNextLevel) ||
                other.pointsToNextLevel == pointsToNextLevel) &&
            (identical(
                  other.levelProgressPercentage,
                  levelProgressPercentage,
                ) ||
                other.levelProgressPercentage == levelProgressPercentage) &&
            (identical(other.currentStreak, currentStreak) ||
                other.currentStreak == currentStreak) &&
            (identical(other.longestStreak, longestStreak) ||
                other.longestStreak == longestStreak) &&
            (identical(other.lastActivityDate, lastActivityDate) ||
                other.lastActivityDate == lastActivityDate) &&
            const DeepCollectionEquality().equals(
              other._achievements,
              _achievements,
            ) &&
            const DeepCollectionEquality().equals(other._badges, _badges) &&
            const DeepCollectionEquality().equals(
              other._recentTransactions,
              _recentTransactions,
            ) &&
            (identical(other.leaderboardRank, leaderboardRank) ||
                other.leaderboardRank == leaderboardRank) &&
            (identical(other.totalParticipants, totalParticipants) ||
                other.totalParticipants == totalParticipants) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    userId,
    totalPoints,
    availablePoints,
    redeemedPoints,
    currentLevel,
    currentRank,
    pointsToNextLevel,
    levelProgressPercentage,
    currentStreak,
    longestStreak,
    lastActivityDate,
    const DeepCollectionEquality().hash(_achievements),
    const DeepCollectionEquality().hash(_badges),
    const DeepCollectionEquality().hash(_recentTransactions),
    leaderboardRank,
    totalParticipants,
    createdAt,
    updatedAt,
  );

  /// Create a copy of GamificationModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$GamificationModelImplCopyWith<_$GamificationModelImpl> get copyWith =>
      __$$GamificationModelImplCopyWithImpl<_$GamificationModelImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$GamificationModelImplToJson(this);
  }
}

abstract class _GamificationModel implements GamificationModel {
  const factory _GamificationModel({
    required final String userId,
    required final int totalPoints,
    required final int availablePoints,
    required final int redeemedPoints,
    required final int currentLevel,
    required final String currentRank,
    final int? pointsToNextLevel,
    final double? levelProgressPercentage,
    final int? currentStreak,
    final int? longestStreak,
    final DateTime? lastActivityDate,
    final List<Achievement>? achievements,
    final List<Badge>? badges,
    final List<PointsTransaction>? recentTransactions,
    final int? leaderboardRank,
    final int? totalParticipants,
    final DateTime? createdAt,
    final DateTime? updatedAt,
  }) = _$GamificationModelImpl;

  factory _GamificationModel.fromJson(Map<String, dynamic> json) =
      _$GamificationModelImpl.fromJson;

  @override
  String get userId;
  @override
  int get totalPoints;
  @override
  int get availablePoints;
  @override
  int get redeemedPoints;
  @override
  int get currentLevel;
  @override
  String get currentRank; // Progress
  @override
  int? get pointsToNextLevel;
  @override
  double? get levelProgressPercentage; // Streaks
  @override
  int? get currentStreak;
  @override
  int? get longestStreak;
  @override
  DateTime? get lastActivityDate; // Achievements
  @override
  List<Achievement>? get achievements;
  @override
  List<Badge>? get badges; // Recent Activity
  @override
  List<PointsTransaction>? get recentTransactions; // Leaderboard
  @override
  int? get leaderboardRank;
  @override
  int? get totalParticipants; // Timestamps
  @override
  DateTime? get createdAt;
  @override
  DateTime? get updatedAt;

  /// Create a copy of GamificationModel
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$GamificationModelImplCopyWith<_$GamificationModelImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

PointsTransaction _$PointsTransactionFromJson(Map<String, dynamic> json) {
  return _PointsTransaction.fromJson(json);
}

/// @nodoc
mixin _$PointsTransaction {
  String get id => throw _privateConstructorUsedError;
  String get userId => throw _privateConstructorUsedError;
  int get points => throw _privateConstructorUsedError;
  String get type =>
      throw _privateConstructorUsedError; // earned, redeemed, adjusted
  String get activityType => throw _privateConstructorUsedError;
  String? get description => throw _privateConstructorUsedError;
  String? get metadata => throw _privateConstructorUsedError;
  int? get balanceBefore => throw _privateConstructorUsedError;
  int? get balanceAfter => throw _privateConstructorUsedError;
  DateTime? get createdAt => throw _privateConstructorUsedError;

  /// Serializes this PointsTransaction to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of PointsTransaction
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $PointsTransactionCopyWith<PointsTransaction> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $PointsTransactionCopyWith<$Res> {
  factory $PointsTransactionCopyWith(
    PointsTransaction value,
    $Res Function(PointsTransaction) then,
  ) = _$PointsTransactionCopyWithImpl<$Res, PointsTransaction>;
  @useResult
  $Res call({
    String id,
    String userId,
    int points,
    String type,
    String activityType,
    String? description,
    String? metadata,
    int? balanceBefore,
    int? balanceAfter,
    DateTime? createdAt,
  });
}

/// @nodoc
class _$PointsTransactionCopyWithImpl<$Res, $Val extends PointsTransaction>
    implements $PointsTransactionCopyWith<$Res> {
  _$PointsTransactionCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of PointsTransaction
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? userId = null,
    Object? points = null,
    Object? type = null,
    Object? activityType = null,
    Object? description = freezed,
    Object? metadata = freezed,
    Object? balanceBefore = freezed,
    Object? balanceAfter = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            userId: null == userId
                ? _value.userId
                : userId // ignore: cast_nullable_to_non_nullable
                      as String,
            points: null == points
                ? _value.points
                : points // ignore: cast_nullable_to_non_nullable
                      as int,
            type: null == type
                ? _value.type
                : type // ignore: cast_nullable_to_non_nullable
                      as String,
            activityType: null == activityType
                ? _value.activityType
                : activityType // ignore: cast_nullable_to_non_nullable
                      as String,
            description: freezed == description
                ? _value.description
                : description // ignore: cast_nullable_to_non_nullable
                      as String?,
            metadata: freezed == metadata
                ? _value.metadata
                : metadata // ignore: cast_nullable_to_non_nullable
                      as String?,
            balanceBefore: freezed == balanceBefore
                ? _value.balanceBefore
                : balanceBefore // ignore: cast_nullable_to_non_nullable
                      as int?,
            balanceAfter: freezed == balanceAfter
                ? _value.balanceAfter
                : balanceAfter // ignore: cast_nullable_to_non_nullable
                      as int?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$PointsTransactionImplCopyWith<$Res>
    implements $PointsTransactionCopyWith<$Res> {
  factory _$$PointsTransactionImplCopyWith(
    _$PointsTransactionImpl value,
    $Res Function(_$PointsTransactionImpl) then,
  ) = __$$PointsTransactionImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String userId,
    int points,
    String type,
    String activityType,
    String? description,
    String? metadata,
    int? balanceBefore,
    int? balanceAfter,
    DateTime? createdAt,
  });
}

/// @nodoc
class __$$PointsTransactionImplCopyWithImpl<$Res>
    extends _$PointsTransactionCopyWithImpl<$Res, _$PointsTransactionImpl>
    implements _$$PointsTransactionImplCopyWith<$Res> {
  __$$PointsTransactionImplCopyWithImpl(
    _$PointsTransactionImpl _value,
    $Res Function(_$PointsTransactionImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of PointsTransaction
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? userId = null,
    Object? points = null,
    Object? type = null,
    Object? activityType = null,
    Object? description = freezed,
    Object? metadata = freezed,
    Object? balanceBefore = freezed,
    Object? balanceAfter = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _$PointsTransactionImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        userId: null == userId
            ? _value.userId
            : userId // ignore: cast_nullable_to_non_nullable
                  as String,
        points: null == points
            ? _value.points
            : points // ignore: cast_nullable_to_non_nullable
                  as int,
        type: null == type
            ? _value.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        activityType: null == activityType
            ? _value.activityType
            : activityType // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _value.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        metadata: freezed == metadata
            ? _value.metadata
            : metadata // ignore: cast_nullable_to_non_nullable
                  as String?,
        balanceBefore: freezed == balanceBefore
            ? _value.balanceBefore
            : balanceBefore // ignore: cast_nullable_to_non_nullable
                  as int?,
        balanceAfter: freezed == balanceAfter
            ? _value.balanceAfter
            : balanceAfter // ignore: cast_nullable_to_non_nullable
                  as int?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$PointsTransactionImpl implements _PointsTransaction {
  const _$PointsTransactionImpl({
    required this.id,
    required this.userId,
    required this.points,
    required this.type,
    required this.activityType,
    this.description,
    this.metadata,
    this.balanceBefore,
    this.balanceAfter,
    this.createdAt,
  });

  factory _$PointsTransactionImpl.fromJson(Map<String, dynamic> json) =>
      _$$PointsTransactionImplFromJson(json);

  @override
  final String id;
  @override
  final String userId;
  @override
  final int points;
  @override
  final String type;
  // earned, redeemed, adjusted
  @override
  final String activityType;
  @override
  final String? description;
  @override
  final String? metadata;
  @override
  final int? balanceBefore;
  @override
  final int? balanceAfter;
  @override
  final DateTime? createdAt;

  @override
  String toString() {
    return 'PointsTransaction(id: $id, userId: $userId, points: $points, type: $type, activityType: $activityType, description: $description, metadata: $metadata, balanceBefore: $balanceBefore, balanceAfter: $balanceAfter, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$PointsTransactionImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.userId, userId) || other.userId == userId) &&
            (identical(other.points, points) || other.points == points) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.activityType, activityType) ||
                other.activityType == activityType) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.metadata, metadata) ||
                other.metadata == metadata) &&
            (identical(other.balanceBefore, balanceBefore) ||
                other.balanceBefore == balanceBefore) &&
            (identical(other.balanceAfter, balanceAfter) ||
                other.balanceAfter == balanceAfter) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    userId,
    points,
    type,
    activityType,
    description,
    metadata,
    balanceBefore,
    balanceAfter,
    createdAt,
  );

  /// Create a copy of PointsTransaction
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$PointsTransactionImplCopyWith<_$PointsTransactionImpl> get copyWith =>
      __$$PointsTransactionImplCopyWithImpl<_$PointsTransactionImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$PointsTransactionImplToJson(this);
  }
}

abstract class _PointsTransaction implements PointsTransaction {
  const factory _PointsTransaction({
    required final String id,
    required final String userId,
    required final int points,
    required final String type,
    required final String activityType,
    final String? description,
    final String? metadata,
    final int? balanceBefore,
    final int? balanceAfter,
    final DateTime? createdAt,
  }) = _$PointsTransactionImpl;

  factory _PointsTransaction.fromJson(Map<String, dynamic> json) =
      _$PointsTransactionImpl.fromJson;

  @override
  String get id;
  @override
  String get userId;
  @override
  int get points;
  @override
  String get type; // earned, redeemed, adjusted
  @override
  String get activityType;
  @override
  String? get description;
  @override
  String? get metadata;
  @override
  int? get balanceBefore;
  @override
  int? get balanceAfter;
  @override
  DateTime? get createdAt;

  /// Create a copy of PointsTransaction
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$PointsTransactionImplCopyWith<_$PointsTransactionImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

Achievement _$AchievementFromJson(Map<String, dynamic> json) {
  return _Achievement.fromJson(json);
}

/// @nodoc
mixin _$Achievement {
  String get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get description => throw _privateConstructorUsedError;
  String get icon => throw _privateConstructorUsedError;
  int get pointsReward => throw _privateConstructorUsedError;
  String get category =>
      throw _privateConstructorUsedError; // sales, recruitment, activity, training
  String get condition => throw _privateConstructorUsedError;
  int? get targetValue => throw _privateConstructorUsedError;
  int? get currentValue => throw _privateConstructorUsedError;
  double? get progressPercentage => throw _privateConstructorUsedError;
  bool? get isCompleted => throw _privateConstructorUsedError;
  DateTime? get completedAt => throw _privateConstructorUsedError;
  DateTime? get createdAt => throw _privateConstructorUsedError;

  /// Serializes this Achievement to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Achievement
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $AchievementCopyWith<Achievement> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $AchievementCopyWith<$Res> {
  factory $AchievementCopyWith(
    Achievement value,
    $Res Function(Achievement) then,
  ) = _$AchievementCopyWithImpl<$Res, Achievement>;
  @useResult
  $Res call({
    String id,
    String name,
    String description,
    String icon,
    int pointsReward,
    String category,
    String condition,
    int? targetValue,
    int? currentValue,
    double? progressPercentage,
    bool? isCompleted,
    DateTime? completedAt,
    DateTime? createdAt,
  });
}

/// @nodoc
class _$AchievementCopyWithImpl<$Res, $Val extends Achievement>
    implements $AchievementCopyWith<$Res> {
  _$AchievementCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Achievement
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = null,
    Object? icon = null,
    Object? pointsReward = null,
    Object? category = null,
    Object? condition = null,
    Object? targetValue = freezed,
    Object? currentValue = freezed,
    Object? progressPercentage = freezed,
    Object? isCompleted = freezed,
    Object? completedAt = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            name: null == name
                ? _value.name
                : name // ignore: cast_nullable_to_non_nullable
                      as String,
            description: null == description
                ? _value.description
                : description // ignore: cast_nullable_to_non_nullable
                      as String,
            icon: null == icon
                ? _value.icon
                : icon // ignore: cast_nullable_to_non_nullable
                      as String,
            pointsReward: null == pointsReward
                ? _value.pointsReward
                : pointsReward // ignore: cast_nullable_to_non_nullable
                      as int,
            category: null == category
                ? _value.category
                : category // ignore: cast_nullable_to_non_nullable
                      as String,
            condition: null == condition
                ? _value.condition
                : condition // ignore: cast_nullable_to_non_nullable
                      as String,
            targetValue: freezed == targetValue
                ? _value.targetValue
                : targetValue // ignore: cast_nullable_to_non_nullable
                      as int?,
            currentValue: freezed == currentValue
                ? _value.currentValue
                : currentValue // ignore: cast_nullable_to_non_nullable
                      as int?,
            progressPercentage: freezed == progressPercentage
                ? _value.progressPercentage
                : progressPercentage // ignore: cast_nullable_to_non_nullable
                      as double?,
            isCompleted: freezed == isCompleted
                ? _value.isCompleted
                : isCompleted // ignore: cast_nullable_to_non_nullable
                      as bool?,
            completedAt: freezed == completedAt
                ? _value.completedAt
                : completedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$AchievementImplCopyWith<$Res>
    implements $AchievementCopyWith<$Res> {
  factory _$$AchievementImplCopyWith(
    _$AchievementImpl value,
    $Res Function(_$AchievementImpl) then,
  ) = __$$AchievementImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String description,
    String icon,
    int pointsReward,
    String category,
    String condition,
    int? targetValue,
    int? currentValue,
    double? progressPercentage,
    bool? isCompleted,
    DateTime? completedAt,
    DateTime? createdAt,
  });
}

/// @nodoc
class __$$AchievementImplCopyWithImpl<$Res>
    extends _$AchievementCopyWithImpl<$Res, _$AchievementImpl>
    implements _$$AchievementImplCopyWith<$Res> {
  __$$AchievementImplCopyWithImpl(
    _$AchievementImpl _value,
    $Res Function(_$AchievementImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of Achievement
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = null,
    Object? icon = null,
    Object? pointsReward = null,
    Object? category = null,
    Object? condition = null,
    Object? targetValue = freezed,
    Object? currentValue = freezed,
    Object? progressPercentage = freezed,
    Object? isCompleted = freezed,
    Object? completedAt = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _$AchievementImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _value.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        description: null == description
            ? _value.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String,
        icon: null == icon
            ? _value.icon
            : icon // ignore: cast_nullable_to_non_nullable
                  as String,
        pointsReward: null == pointsReward
            ? _value.pointsReward
            : pointsReward // ignore: cast_nullable_to_non_nullable
                  as int,
        category: null == category
            ? _value.category
            : category // ignore: cast_nullable_to_non_nullable
                  as String,
        condition: null == condition
            ? _value.condition
            : condition // ignore: cast_nullable_to_non_nullable
                  as String,
        targetValue: freezed == targetValue
            ? _value.targetValue
            : targetValue // ignore: cast_nullable_to_non_nullable
                  as int?,
        currentValue: freezed == currentValue
            ? _value.currentValue
            : currentValue // ignore: cast_nullable_to_non_nullable
                  as int?,
        progressPercentage: freezed == progressPercentage
            ? _value.progressPercentage
            : progressPercentage // ignore: cast_nullable_to_non_nullable
                  as double?,
        isCompleted: freezed == isCompleted
            ? _value.isCompleted
            : isCompleted // ignore: cast_nullable_to_non_nullable
                  as bool?,
        completedAt: freezed == completedAt
            ? _value.completedAt
            : completedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$AchievementImpl implements _Achievement {
  const _$AchievementImpl({
    required this.id,
    required this.name,
    required this.description,
    required this.icon,
    required this.pointsReward,
    required this.category,
    required this.condition,
    this.targetValue,
    this.currentValue,
    this.progressPercentage,
    this.isCompleted,
    this.completedAt,
    this.createdAt,
  });

  factory _$AchievementImpl.fromJson(Map<String, dynamic> json) =>
      _$$AchievementImplFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String description;
  @override
  final String icon;
  @override
  final int pointsReward;
  @override
  final String category;
  // sales, recruitment, activity, training
  @override
  final String condition;
  @override
  final int? targetValue;
  @override
  final int? currentValue;
  @override
  final double? progressPercentage;
  @override
  final bool? isCompleted;
  @override
  final DateTime? completedAt;
  @override
  final DateTime? createdAt;

  @override
  String toString() {
    return 'Achievement(id: $id, name: $name, description: $description, icon: $icon, pointsReward: $pointsReward, category: $category, condition: $condition, targetValue: $targetValue, currentValue: $currentValue, progressPercentage: $progressPercentage, isCompleted: $isCompleted, completedAt: $completedAt, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$AchievementImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.icon, icon) || other.icon == icon) &&
            (identical(other.pointsReward, pointsReward) ||
                other.pointsReward == pointsReward) &&
            (identical(other.category, category) ||
                other.category == category) &&
            (identical(other.condition, condition) ||
                other.condition == condition) &&
            (identical(other.targetValue, targetValue) ||
                other.targetValue == targetValue) &&
            (identical(other.currentValue, currentValue) ||
                other.currentValue == currentValue) &&
            (identical(other.progressPercentage, progressPercentage) ||
                other.progressPercentage == progressPercentage) &&
            (identical(other.isCompleted, isCompleted) ||
                other.isCompleted == isCompleted) &&
            (identical(other.completedAt, completedAt) ||
                other.completedAt == completedAt) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    name,
    description,
    icon,
    pointsReward,
    category,
    condition,
    targetValue,
    currentValue,
    progressPercentage,
    isCompleted,
    completedAt,
    createdAt,
  );

  /// Create a copy of Achievement
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$AchievementImplCopyWith<_$AchievementImpl> get copyWith =>
      __$$AchievementImplCopyWithImpl<_$AchievementImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$AchievementImplToJson(this);
  }
}

abstract class _Achievement implements Achievement {
  const factory _Achievement({
    required final String id,
    required final String name,
    required final String description,
    required final String icon,
    required final int pointsReward,
    required final String category,
    required final String condition,
    final int? targetValue,
    final int? currentValue,
    final double? progressPercentage,
    final bool? isCompleted,
    final DateTime? completedAt,
    final DateTime? createdAt,
  }) = _$AchievementImpl;

  factory _Achievement.fromJson(Map<String, dynamic> json) =
      _$AchievementImpl.fromJson;

  @override
  String get id;
  @override
  String get name;
  @override
  String get description;
  @override
  String get icon;
  @override
  int get pointsReward;
  @override
  String get category; // sales, recruitment, activity, training
  @override
  String get condition;
  @override
  int? get targetValue;
  @override
  int? get currentValue;
  @override
  double? get progressPercentage;
  @override
  bool? get isCompleted;
  @override
  DateTime? get completedAt;
  @override
  DateTime? get createdAt;

  /// Create a copy of Achievement
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$AchievementImplCopyWith<_$AchievementImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

Badge _$BadgeFromJson(Map<String, dynamic> json) {
  return _Badge.fromJson(json);
}

/// @nodoc
mixin _$Badge {
  String get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get description => throw _privateConstructorUsedError;
  String get icon => throw _privateConstructorUsedError;
  String get rarity =>
      throw _privateConstructorUsedError; // bronze, silver, gold, platinum, diamond
  String get category => throw _privateConstructorUsedError;
  DateTime? get earnedAt => throw _privateConstructorUsedError;
  DateTime? get createdAt => throw _privateConstructorUsedError;

  /// Serializes this Badge to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Badge
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $BadgeCopyWith<Badge> get copyWith => throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $BadgeCopyWith<$Res> {
  factory $BadgeCopyWith(Badge value, $Res Function(Badge) then) =
      _$BadgeCopyWithImpl<$Res, Badge>;
  @useResult
  $Res call({
    String id,
    String name,
    String description,
    String icon,
    String rarity,
    String category,
    DateTime? earnedAt,
    DateTime? createdAt,
  });
}

/// @nodoc
class _$BadgeCopyWithImpl<$Res, $Val extends Badge>
    implements $BadgeCopyWith<$Res> {
  _$BadgeCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Badge
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = null,
    Object? icon = null,
    Object? rarity = null,
    Object? category = null,
    Object? earnedAt = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            name: null == name
                ? _value.name
                : name // ignore: cast_nullable_to_non_nullable
                      as String,
            description: null == description
                ? _value.description
                : description // ignore: cast_nullable_to_non_nullable
                      as String,
            icon: null == icon
                ? _value.icon
                : icon // ignore: cast_nullable_to_non_nullable
                      as String,
            rarity: null == rarity
                ? _value.rarity
                : rarity // ignore: cast_nullable_to_non_nullable
                      as String,
            category: null == category
                ? _value.category
                : category // ignore: cast_nullable_to_non_nullable
                      as String,
            earnedAt: freezed == earnedAt
                ? _value.earnedAt
                : earnedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$BadgeImplCopyWith<$Res> implements $BadgeCopyWith<$Res> {
  factory _$$BadgeImplCopyWith(
    _$BadgeImpl value,
    $Res Function(_$BadgeImpl) then,
  ) = __$$BadgeImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String description,
    String icon,
    String rarity,
    String category,
    DateTime? earnedAt,
    DateTime? createdAt,
  });
}

/// @nodoc
class __$$BadgeImplCopyWithImpl<$Res>
    extends _$BadgeCopyWithImpl<$Res, _$BadgeImpl>
    implements _$$BadgeImplCopyWith<$Res> {
  __$$BadgeImplCopyWithImpl(
    _$BadgeImpl _value,
    $Res Function(_$BadgeImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of Badge
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = null,
    Object? icon = null,
    Object? rarity = null,
    Object? category = null,
    Object? earnedAt = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _$BadgeImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _value.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        description: null == description
            ? _value.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String,
        icon: null == icon
            ? _value.icon
            : icon // ignore: cast_nullable_to_non_nullable
                  as String,
        rarity: null == rarity
            ? _value.rarity
            : rarity // ignore: cast_nullable_to_non_nullable
                  as String,
        category: null == category
            ? _value.category
            : category // ignore: cast_nullable_to_non_nullable
                  as String,
        earnedAt: freezed == earnedAt
            ? _value.earnedAt
            : earnedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$BadgeImpl implements _Badge {
  const _$BadgeImpl({
    required this.id,
    required this.name,
    required this.description,
    required this.icon,
    required this.rarity,
    required this.category,
    this.earnedAt,
    this.createdAt,
  });

  factory _$BadgeImpl.fromJson(Map<String, dynamic> json) =>
      _$$BadgeImplFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String description;
  @override
  final String icon;
  @override
  final String rarity;
  // bronze, silver, gold, platinum, diamond
  @override
  final String category;
  @override
  final DateTime? earnedAt;
  @override
  final DateTime? createdAt;

  @override
  String toString() {
    return 'Badge(id: $id, name: $name, description: $description, icon: $icon, rarity: $rarity, category: $category, earnedAt: $earnedAt, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$BadgeImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.icon, icon) || other.icon == icon) &&
            (identical(other.rarity, rarity) || other.rarity == rarity) &&
            (identical(other.category, category) ||
                other.category == category) &&
            (identical(other.earnedAt, earnedAt) ||
                other.earnedAt == earnedAt) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    name,
    description,
    icon,
    rarity,
    category,
    earnedAt,
    createdAt,
  );

  /// Create a copy of Badge
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$BadgeImplCopyWith<_$BadgeImpl> get copyWith =>
      __$$BadgeImplCopyWithImpl<_$BadgeImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$BadgeImplToJson(this);
  }
}

abstract class _Badge implements Badge {
  const factory _Badge({
    required final String id,
    required final String name,
    required final String description,
    required final String icon,
    required final String rarity,
    required final String category,
    final DateTime? earnedAt,
    final DateTime? createdAt,
  }) = _$BadgeImpl;

  factory _Badge.fromJson(Map<String, dynamic> json) = _$BadgeImpl.fromJson;

  @override
  String get id;
  @override
  String get name;
  @override
  String get description;
  @override
  String get icon;
  @override
  String get rarity; // bronze, silver, gold, platinum, diamond
  @override
  String get category;
  @override
  DateTime? get earnedAt;
  @override
  DateTime? get createdAt;

  /// Create a copy of Badge
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$BadgeImplCopyWith<_$BadgeImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

Reward _$RewardFromJson(Map<String, dynamic> json) {
  return _Reward.fromJson(json);
}

/// @nodoc
mixin _$Reward {
  String get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get description => throw _privateConstructorUsedError;
  String get imageUrl => throw _privateConstructorUsedError;
  int get pointsCost => throw _privateConstructorUsedError;
  int get stockQuantity => throw _privateConstructorUsedError;
  String? get category =>
      throw _privateConstructorUsedError; // merchandise, vouchers, cash, experience
  String? get termsAndConditions => throw _privateConstructorUsedError;
  bool? get isActive => throw _privateConstructorUsedError;
  DateTime? get validFrom => throw _privateConstructorUsedError;
  DateTime? get validUntil => throw _privateConstructorUsedError;
  DateTime? get createdAt => throw _privateConstructorUsedError;

  /// Serializes this Reward to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Reward
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $RewardCopyWith<Reward> get copyWith => throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $RewardCopyWith<$Res> {
  factory $RewardCopyWith(Reward value, $Res Function(Reward) then) =
      _$RewardCopyWithImpl<$Res, Reward>;
  @useResult
  $Res call({
    String id,
    String name,
    String description,
    String imageUrl,
    int pointsCost,
    int stockQuantity,
    String? category,
    String? termsAndConditions,
    bool? isActive,
    DateTime? validFrom,
    DateTime? validUntil,
    DateTime? createdAt,
  });
}

/// @nodoc
class _$RewardCopyWithImpl<$Res, $Val extends Reward>
    implements $RewardCopyWith<$Res> {
  _$RewardCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Reward
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = null,
    Object? imageUrl = null,
    Object? pointsCost = null,
    Object? stockQuantity = null,
    Object? category = freezed,
    Object? termsAndConditions = freezed,
    Object? isActive = freezed,
    Object? validFrom = freezed,
    Object? validUntil = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            name: null == name
                ? _value.name
                : name // ignore: cast_nullable_to_non_nullable
                      as String,
            description: null == description
                ? _value.description
                : description // ignore: cast_nullable_to_non_nullable
                      as String,
            imageUrl: null == imageUrl
                ? _value.imageUrl
                : imageUrl // ignore: cast_nullable_to_non_nullable
                      as String,
            pointsCost: null == pointsCost
                ? _value.pointsCost
                : pointsCost // ignore: cast_nullable_to_non_nullable
                      as int,
            stockQuantity: null == stockQuantity
                ? _value.stockQuantity
                : stockQuantity // ignore: cast_nullable_to_non_nullable
                      as int,
            category: freezed == category
                ? _value.category
                : category // ignore: cast_nullable_to_non_nullable
                      as String?,
            termsAndConditions: freezed == termsAndConditions
                ? _value.termsAndConditions
                : termsAndConditions // ignore: cast_nullable_to_non_nullable
                      as String?,
            isActive: freezed == isActive
                ? _value.isActive
                : isActive // ignore: cast_nullable_to_non_nullable
                      as bool?,
            validFrom: freezed == validFrom
                ? _value.validFrom
                : validFrom // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            validUntil: freezed == validUntil
                ? _value.validUntil
                : validUntil // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$RewardImplCopyWith<$Res> implements $RewardCopyWith<$Res> {
  factory _$$RewardImplCopyWith(
    _$RewardImpl value,
    $Res Function(_$RewardImpl) then,
  ) = __$$RewardImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String description,
    String imageUrl,
    int pointsCost,
    int stockQuantity,
    String? category,
    String? termsAndConditions,
    bool? isActive,
    DateTime? validFrom,
    DateTime? validUntil,
    DateTime? createdAt,
  });
}

/// @nodoc
class __$$RewardImplCopyWithImpl<$Res>
    extends _$RewardCopyWithImpl<$Res, _$RewardImpl>
    implements _$$RewardImplCopyWith<$Res> {
  __$$RewardImplCopyWithImpl(
    _$RewardImpl _value,
    $Res Function(_$RewardImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of Reward
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = null,
    Object? imageUrl = null,
    Object? pointsCost = null,
    Object? stockQuantity = null,
    Object? category = freezed,
    Object? termsAndConditions = freezed,
    Object? isActive = freezed,
    Object? validFrom = freezed,
    Object? validUntil = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _$RewardImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _value.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        description: null == description
            ? _value.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String,
        imageUrl: null == imageUrl
            ? _value.imageUrl
            : imageUrl // ignore: cast_nullable_to_non_nullable
                  as String,
        pointsCost: null == pointsCost
            ? _value.pointsCost
            : pointsCost // ignore: cast_nullable_to_non_nullable
                  as int,
        stockQuantity: null == stockQuantity
            ? _value.stockQuantity
            : stockQuantity // ignore: cast_nullable_to_non_nullable
                  as int,
        category: freezed == category
            ? _value.category
            : category // ignore: cast_nullable_to_non_nullable
                  as String?,
        termsAndConditions: freezed == termsAndConditions
            ? _value.termsAndConditions
            : termsAndConditions // ignore: cast_nullable_to_non_nullable
                  as String?,
        isActive: freezed == isActive
            ? _value.isActive
            : isActive // ignore: cast_nullable_to_non_nullable
                  as bool?,
        validFrom: freezed == validFrom
            ? _value.validFrom
            : validFrom // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        validUntil: freezed == validUntil
            ? _value.validUntil
            : validUntil // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$RewardImpl implements _Reward {
  const _$RewardImpl({
    required this.id,
    required this.name,
    required this.description,
    required this.imageUrl,
    required this.pointsCost,
    required this.stockQuantity,
    this.category,
    this.termsAndConditions,
    this.isActive,
    this.validFrom,
    this.validUntil,
    this.createdAt,
  });

  factory _$RewardImpl.fromJson(Map<String, dynamic> json) =>
      _$$RewardImplFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String description;
  @override
  final String imageUrl;
  @override
  final int pointsCost;
  @override
  final int stockQuantity;
  @override
  final String? category;
  // merchandise, vouchers, cash, experience
  @override
  final String? termsAndConditions;
  @override
  final bool? isActive;
  @override
  final DateTime? validFrom;
  @override
  final DateTime? validUntil;
  @override
  final DateTime? createdAt;

  @override
  String toString() {
    return 'Reward(id: $id, name: $name, description: $description, imageUrl: $imageUrl, pointsCost: $pointsCost, stockQuantity: $stockQuantity, category: $category, termsAndConditions: $termsAndConditions, isActive: $isActive, validFrom: $validFrom, validUntil: $validUntil, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$RewardImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.imageUrl, imageUrl) ||
                other.imageUrl == imageUrl) &&
            (identical(other.pointsCost, pointsCost) ||
                other.pointsCost == pointsCost) &&
            (identical(other.stockQuantity, stockQuantity) ||
                other.stockQuantity == stockQuantity) &&
            (identical(other.category, category) ||
                other.category == category) &&
            (identical(other.termsAndConditions, termsAndConditions) ||
                other.termsAndConditions == termsAndConditions) &&
            (identical(other.isActive, isActive) ||
                other.isActive == isActive) &&
            (identical(other.validFrom, validFrom) ||
                other.validFrom == validFrom) &&
            (identical(other.validUntil, validUntil) ||
                other.validUntil == validUntil) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    name,
    description,
    imageUrl,
    pointsCost,
    stockQuantity,
    category,
    termsAndConditions,
    isActive,
    validFrom,
    validUntil,
    createdAt,
  );

  /// Create a copy of Reward
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$RewardImplCopyWith<_$RewardImpl> get copyWith =>
      __$$RewardImplCopyWithImpl<_$RewardImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$RewardImplToJson(this);
  }
}

abstract class _Reward implements Reward {
  const factory _Reward({
    required final String id,
    required final String name,
    required final String description,
    required final String imageUrl,
    required final int pointsCost,
    required final int stockQuantity,
    final String? category,
    final String? termsAndConditions,
    final bool? isActive,
    final DateTime? validFrom,
    final DateTime? validUntil,
    final DateTime? createdAt,
  }) = _$RewardImpl;

  factory _Reward.fromJson(Map<String, dynamic> json) = _$RewardImpl.fromJson;

  @override
  String get id;
  @override
  String get name;
  @override
  String get description;
  @override
  String get imageUrl;
  @override
  int get pointsCost;
  @override
  int get stockQuantity;
  @override
  String? get category; // merchandise, vouchers, cash, experience
  @override
  String? get termsAndConditions;
  @override
  bool? get isActive;
  @override
  DateTime? get validFrom;
  @override
  DateTime? get validUntil;
  @override
  DateTime? get createdAt;

  /// Create a copy of Reward
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$RewardImplCopyWith<_$RewardImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

RewardRedemption _$RewardRedemptionFromJson(Map<String, dynamic> json) {
  return _RewardRedemption.fromJson(json);
}

/// @nodoc
mixin _$RewardRedemption {
  String get id => throw _privateConstructorUsedError;
  String get userId => throw _privateConstructorUsedError;
  String get rewardId => throw _privateConstructorUsedError;
  String get rewardName => throw _privateConstructorUsedError;
  int get pointsSpent => throw _privateConstructorUsedError;
  String get status =>
      throw _privateConstructorUsedError; // pending, processing, shipped, delivered, cancelled
  String? get deliveryAddress => throw _privateConstructorUsedError;
  String? get trackingNumber => throw _privateConstructorUsedError;
  DateTime? get requestedAt => throw _privateConstructorUsedError;
  DateTime? get processedAt => throw _privateConstructorUsedError;
  DateTime? get shippedAt => throw _privateConstructorUsedError;
  DateTime? get deliveredAt => throw _privateConstructorUsedError;
  String? get notes => throw _privateConstructorUsedError;

  /// Serializes this RewardRedemption to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of RewardRedemption
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $RewardRedemptionCopyWith<RewardRedemption> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $RewardRedemptionCopyWith<$Res> {
  factory $RewardRedemptionCopyWith(
    RewardRedemption value,
    $Res Function(RewardRedemption) then,
  ) = _$RewardRedemptionCopyWithImpl<$Res, RewardRedemption>;
  @useResult
  $Res call({
    String id,
    String userId,
    String rewardId,
    String rewardName,
    int pointsSpent,
    String status,
    String? deliveryAddress,
    String? trackingNumber,
    DateTime? requestedAt,
    DateTime? processedAt,
    DateTime? shippedAt,
    DateTime? deliveredAt,
    String? notes,
  });
}

/// @nodoc
class _$RewardRedemptionCopyWithImpl<$Res, $Val extends RewardRedemption>
    implements $RewardRedemptionCopyWith<$Res> {
  _$RewardRedemptionCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of RewardRedemption
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? userId = null,
    Object? rewardId = null,
    Object? rewardName = null,
    Object? pointsSpent = null,
    Object? status = null,
    Object? deliveryAddress = freezed,
    Object? trackingNumber = freezed,
    Object? requestedAt = freezed,
    Object? processedAt = freezed,
    Object? shippedAt = freezed,
    Object? deliveredAt = freezed,
    Object? notes = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            userId: null == userId
                ? _value.userId
                : userId // ignore: cast_nullable_to_non_nullable
                      as String,
            rewardId: null == rewardId
                ? _value.rewardId
                : rewardId // ignore: cast_nullable_to_non_nullable
                      as String,
            rewardName: null == rewardName
                ? _value.rewardName
                : rewardName // ignore: cast_nullable_to_non_nullable
                      as String,
            pointsSpent: null == pointsSpent
                ? _value.pointsSpent
                : pointsSpent // ignore: cast_nullable_to_non_nullable
                      as int,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as String,
            deliveryAddress: freezed == deliveryAddress
                ? _value.deliveryAddress
                : deliveryAddress // ignore: cast_nullable_to_non_nullable
                      as String?,
            trackingNumber: freezed == trackingNumber
                ? _value.trackingNumber
                : trackingNumber // ignore: cast_nullable_to_non_nullable
                      as String?,
            requestedAt: freezed == requestedAt
                ? _value.requestedAt
                : requestedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            processedAt: freezed == processedAt
                ? _value.processedAt
                : processedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            shippedAt: freezed == shippedAt
                ? _value.shippedAt
                : shippedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            deliveredAt: freezed == deliveredAt
                ? _value.deliveredAt
                : deliveredAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            notes: freezed == notes
                ? _value.notes
                : notes // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$RewardRedemptionImplCopyWith<$Res>
    implements $RewardRedemptionCopyWith<$Res> {
  factory _$$RewardRedemptionImplCopyWith(
    _$RewardRedemptionImpl value,
    $Res Function(_$RewardRedemptionImpl) then,
  ) = __$$RewardRedemptionImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String userId,
    String rewardId,
    String rewardName,
    int pointsSpent,
    String status,
    String? deliveryAddress,
    String? trackingNumber,
    DateTime? requestedAt,
    DateTime? processedAt,
    DateTime? shippedAt,
    DateTime? deliveredAt,
    String? notes,
  });
}

/// @nodoc
class __$$RewardRedemptionImplCopyWithImpl<$Res>
    extends _$RewardRedemptionCopyWithImpl<$Res, _$RewardRedemptionImpl>
    implements _$$RewardRedemptionImplCopyWith<$Res> {
  __$$RewardRedemptionImplCopyWithImpl(
    _$RewardRedemptionImpl _value,
    $Res Function(_$RewardRedemptionImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of RewardRedemption
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? userId = null,
    Object? rewardId = null,
    Object? rewardName = null,
    Object? pointsSpent = null,
    Object? status = null,
    Object? deliveryAddress = freezed,
    Object? trackingNumber = freezed,
    Object? requestedAt = freezed,
    Object? processedAt = freezed,
    Object? shippedAt = freezed,
    Object? deliveredAt = freezed,
    Object? notes = freezed,
  }) {
    return _then(
      _$RewardRedemptionImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        userId: null == userId
            ? _value.userId
            : userId // ignore: cast_nullable_to_non_nullable
                  as String,
        rewardId: null == rewardId
            ? _value.rewardId
            : rewardId // ignore: cast_nullable_to_non_nullable
                  as String,
        rewardName: null == rewardName
            ? _value.rewardName
            : rewardName // ignore: cast_nullable_to_non_nullable
                  as String,
        pointsSpent: null == pointsSpent
            ? _value.pointsSpent
            : pointsSpent // ignore: cast_nullable_to_non_nullable
                  as int,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        deliveryAddress: freezed == deliveryAddress
            ? _value.deliveryAddress
            : deliveryAddress // ignore: cast_nullable_to_non_nullable
                  as String?,
        trackingNumber: freezed == trackingNumber
            ? _value.trackingNumber
            : trackingNumber // ignore: cast_nullable_to_non_nullable
                  as String?,
        requestedAt: freezed == requestedAt
            ? _value.requestedAt
            : requestedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        processedAt: freezed == processedAt
            ? _value.processedAt
            : processedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        shippedAt: freezed == shippedAt
            ? _value.shippedAt
            : shippedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        deliveredAt: freezed == deliveredAt
            ? _value.deliveredAt
            : deliveredAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        notes: freezed == notes
            ? _value.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$RewardRedemptionImpl implements _RewardRedemption {
  const _$RewardRedemptionImpl({
    required this.id,
    required this.userId,
    required this.rewardId,
    required this.rewardName,
    required this.pointsSpent,
    required this.status,
    this.deliveryAddress,
    this.trackingNumber,
    this.requestedAt,
    this.processedAt,
    this.shippedAt,
    this.deliveredAt,
    this.notes,
  });

  factory _$RewardRedemptionImpl.fromJson(Map<String, dynamic> json) =>
      _$$RewardRedemptionImplFromJson(json);

  @override
  final String id;
  @override
  final String userId;
  @override
  final String rewardId;
  @override
  final String rewardName;
  @override
  final int pointsSpent;
  @override
  final String status;
  // pending, processing, shipped, delivered, cancelled
  @override
  final String? deliveryAddress;
  @override
  final String? trackingNumber;
  @override
  final DateTime? requestedAt;
  @override
  final DateTime? processedAt;
  @override
  final DateTime? shippedAt;
  @override
  final DateTime? deliveredAt;
  @override
  final String? notes;

  @override
  String toString() {
    return 'RewardRedemption(id: $id, userId: $userId, rewardId: $rewardId, rewardName: $rewardName, pointsSpent: $pointsSpent, status: $status, deliveryAddress: $deliveryAddress, trackingNumber: $trackingNumber, requestedAt: $requestedAt, processedAt: $processedAt, shippedAt: $shippedAt, deliveredAt: $deliveredAt, notes: $notes)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$RewardRedemptionImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.userId, userId) || other.userId == userId) &&
            (identical(other.rewardId, rewardId) ||
                other.rewardId == rewardId) &&
            (identical(other.rewardName, rewardName) ||
                other.rewardName == rewardName) &&
            (identical(other.pointsSpent, pointsSpent) ||
                other.pointsSpent == pointsSpent) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.deliveryAddress, deliveryAddress) ||
                other.deliveryAddress == deliveryAddress) &&
            (identical(other.trackingNumber, trackingNumber) ||
                other.trackingNumber == trackingNumber) &&
            (identical(other.requestedAt, requestedAt) ||
                other.requestedAt == requestedAt) &&
            (identical(other.processedAt, processedAt) ||
                other.processedAt == processedAt) &&
            (identical(other.shippedAt, shippedAt) ||
                other.shippedAt == shippedAt) &&
            (identical(other.deliveredAt, deliveredAt) ||
                other.deliveredAt == deliveredAt) &&
            (identical(other.notes, notes) || other.notes == notes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    userId,
    rewardId,
    rewardName,
    pointsSpent,
    status,
    deliveryAddress,
    trackingNumber,
    requestedAt,
    processedAt,
    shippedAt,
    deliveredAt,
    notes,
  );

  /// Create a copy of RewardRedemption
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$RewardRedemptionImplCopyWith<_$RewardRedemptionImpl> get copyWith =>
      __$$RewardRedemptionImplCopyWithImpl<_$RewardRedemptionImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$RewardRedemptionImplToJson(this);
  }
}

abstract class _RewardRedemption implements RewardRedemption {
  const factory _RewardRedemption({
    required final String id,
    required final String userId,
    required final String rewardId,
    required final String rewardName,
    required final int pointsSpent,
    required final String status,
    final String? deliveryAddress,
    final String? trackingNumber,
    final DateTime? requestedAt,
    final DateTime? processedAt,
    final DateTime? shippedAt,
    final DateTime? deliveredAt,
    final String? notes,
  }) = _$RewardRedemptionImpl;

  factory _RewardRedemption.fromJson(Map<String, dynamic> json) =
      _$RewardRedemptionImpl.fromJson;

  @override
  String get id;
  @override
  String get userId;
  @override
  String get rewardId;
  @override
  String get rewardName;
  @override
  int get pointsSpent;
  @override
  String get status; // pending, processing, shipped, delivered, cancelled
  @override
  String? get deliveryAddress;
  @override
  String? get trackingNumber;
  @override
  DateTime? get requestedAt;
  @override
  DateTime? get processedAt;
  @override
  DateTime? get shippedAt;
  @override
  DateTime? get deliveredAt;
  @override
  String? get notes;

  /// Create a copy of RewardRedemption
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$RewardRedemptionImplCopyWith<_$RewardRedemptionImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

LeaderboardEntry _$LeaderboardEntryFromJson(Map<String, dynamic> json) {
  return _LeaderboardEntry.fromJson(json);
}

/// @nodoc
mixin _$LeaderboardEntry {
  String get userId => throw _privateConstructorUsedError;
  String get userName => throw _privateConstructorUsedError;
  String get userPhoto => throw _privateConstructorUsedError;
  int get rank => throw _privateConstructorUsedError;
  int get totalPoints => throw _privateConstructorUsedError;
  int get level => throw _privateConstructorUsedError;
  int get salesCount => throw _privateConstructorUsedError;
  int get recruitsCount => throw _privateConstructorUsedError;
  bool? get isCurrentUser => throw _privateConstructorUsedError;

  /// Serializes this LeaderboardEntry to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $LeaderboardEntryCopyWith<LeaderboardEntry> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $LeaderboardEntryCopyWith<$Res> {
  factory $LeaderboardEntryCopyWith(
    LeaderboardEntry value,
    $Res Function(LeaderboardEntry) then,
  ) = _$LeaderboardEntryCopyWithImpl<$Res, LeaderboardEntry>;
  @useResult
  $Res call({
    String userId,
    String userName,
    String userPhoto,
    int rank,
    int totalPoints,
    int level,
    int salesCount,
    int recruitsCount,
    bool? isCurrentUser,
  });
}

/// @nodoc
class _$LeaderboardEntryCopyWithImpl<$Res, $Val extends LeaderboardEntry>
    implements $LeaderboardEntryCopyWith<$Res> {
  _$LeaderboardEntryCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? userId = null,
    Object? userName = null,
    Object? userPhoto = null,
    Object? rank = null,
    Object? totalPoints = null,
    Object? level = null,
    Object? salesCount = null,
    Object? recruitsCount = null,
    Object? isCurrentUser = freezed,
  }) {
    return _then(
      _value.copyWith(
            userId: null == userId
                ? _value.userId
                : userId // ignore: cast_nullable_to_non_nullable
                      as String,
            userName: null == userName
                ? _value.userName
                : userName // ignore: cast_nullable_to_non_nullable
                      as String,
            userPhoto: null == userPhoto
                ? _value.userPhoto
                : userPhoto // ignore: cast_nullable_to_non_nullable
                      as String,
            rank: null == rank
                ? _value.rank
                : rank // ignore: cast_nullable_to_non_nullable
                      as int,
            totalPoints: null == totalPoints
                ? _value.totalPoints
                : totalPoints // ignore: cast_nullable_to_non_nullable
                      as int,
            level: null == level
                ? _value.level
                : level // ignore: cast_nullable_to_non_nullable
                      as int,
            salesCount: null == salesCount
                ? _value.salesCount
                : salesCount // ignore: cast_nullable_to_non_nullable
                      as int,
            recruitsCount: null == recruitsCount
                ? _value.recruitsCount
                : recruitsCount // ignore: cast_nullable_to_non_nullable
                      as int,
            isCurrentUser: freezed == isCurrentUser
                ? _value.isCurrentUser
                : isCurrentUser // ignore: cast_nullable_to_non_nullable
                      as bool?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$LeaderboardEntryImplCopyWith<$Res>
    implements $LeaderboardEntryCopyWith<$Res> {
  factory _$$LeaderboardEntryImplCopyWith(
    _$LeaderboardEntryImpl value,
    $Res Function(_$LeaderboardEntryImpl) then,
  ) = __$$LeaderboardEntryImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String userId,
    String userName,
    String userPhoto,
    int rank,
    int totalPoints,
    int level,
    int salesCount,
    int recruitsCount,
    bool? isCurrentUser,
  });
}

/// @nodoc
class __$$LeaderboardEntryImplCopyWithImpl<$Res>
    extends _$LeaderboardEntryCopyWithImpl<$Res, _$LeaderboardEntryImpl>
    implements _$$LeaderboardEntryImplCopyWith<$Res> {
  __$$LeaderboardEntryImplCopyWithImpl(
    _$LeaderboardEntryImpl _value,
    $Res Function(_$LeaderboardEntryImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? userId = null,
    Object? userName = null,
    Object? userPhoto = null,
    Object? rank = null,
    Object? totalPoints = null,
    Object? level = null,
    Object? salesCount = null,
    Object? recruitsCount = null,
    Object? isCurrentUser = freezed,
  }) {
    return _then(
      _$LeaderboardEntryImpl(
        userId: null == userId
            ? _value.userId
            : userId // ignore: cast_nullable_to_non_nullable
                  as String,
        userName: null == userName
            ? _value.userName
            : userName // ignore: cast_nullable_to_non_nullable
                  as String,
        userPhoto: null == userPhoto
            ? _value.userPhoto
            : userPhoto // ignore: cast_nullable_to_non_nullable
                  as String,
        rank: null == rank
            ? _value.rank
            : rank // ignore: cast_nullable_to_non_nullable
                  as int,
        totalPoints: null == totalPoints
            ? _value.totalPoints
            : totalPoints // ignore: cast_nullable_to_non_nullable
                  as int,
        level: null == level
            ? _value.level
            : level // ignore: cast_nullable_to_non_nullable
                  as int,
        salesCount: null == salesCount
            ? _value.salesCount
            : salesCount // ignore: cast_nullable_to_non_nullable
                  as int,
        recruitsCount: null == recruitsCount
            ? _value.recruitsCount
            : recruitsCount // ignore: cast_nullable_to_non_nullable
                  as int,
        isCurrentUser: freezed == isCurrentUser
            ? _value.isCurrentUser
            : isCurrentUser // ignore: cast_nullable_to_non_nullable
                  as bool?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$LeaderboardEntryImpl implements _LeaderboardEntry {
  const _$LeaderboardEntryImpl({
    required this.userId,
    required this.userName,
    required this.userPhoto,
    required this.rank,
    required this.totalPoints,
    required this.level,
    required this.salesCount,
    required this.recruitsCount,
    this.isCurrentUser,
  });

  factory _$LeaderboardEntryImpl.fromJson(Map<String, dynamic> json) =>
      _$$LeaderboardEntryImplFromJson(json);

  @override
  final String userId;
  @override
  final String userName;
  @override
  final String userPhoto;
  @override
  final int rank;
  @override
  final int totalPoints;
  @override
  final int level;
  @override
  final int salesCount;
  @override
  final int recruitsCount;
  @override
  final bool? isCurrentUser;

  @override
  String toString() {
    return 'LeaderboardEntry(userId: $userId, userName: $userName, userPhoto: $userPhoto, rank: $rank, totalPoints: $totalPoints, level: $level, salesCount: $salesCount, recruitsCount: $recruitsCount, isCurrentUser: $isCurrentUser)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$LeaderboardEntryImpl &&
            (identical(other.userId, userId) || other.userId == userId) &&
            (identical(other.userName, userName) ||
                other.userName == userName) &&
            (identical(other.userPhoto, userPhoto) ||
                other.userPhoto == userPhoto) &&
            (identical(other.rank, rank) || other.rank == rank) &&
            (identical(other.totalPoints, totalPoints) ||
                other.totalPoints == totalPoints) &&
            (identical(other.level, level) || other.level == level) &&
            (identical(other.salesCount, salesCount) ||
                other.salesCount == salesCount) &&
            (identical(other.recruitsCount, recruitsCount) ||
                other.recruitsCount == recruitsCount) &&
            (identical(other.isCurrentUser, isCurrentUser) ||
                other.isCurrentUser == isCurrentUser));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    userId,
    userName,
    userPhoto,
    rank,
    totalPoints,
    level,
    salesCount,
    recruitsCount,
    isCurrentUser,
  );

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$LeaderboardEntryImplCopyWith<_$LeaderboardEntryImpl> get copyWith =>
      __$$LeaderboardEntryImplCopyWithImpl<_$LeaderboardEntryImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$LeaderboardEntryImplToJson(this);
  }
}

abstract class _LeaderboardEntry implements LeaderboardEntry {
  const factory _LeaderboardEntry({
    required final String userId,
    required final String userName,
    required final String userPhoto,
    required final int rank,
    required final int totalPoints,
    required final int level,
    required final int salesCount,
    required final int recruitsCount,
    final bool? isCurrentUser,
  }) = _$LeaderboardEntryImpl;

  factory _LeaderboardEntry.fromJson(Map<String, dynamic> json) =
      _$LeaderboardEntryImpl.fromJson;

  @override
  String get userId;
  @override
  String get userName;
  @override
  String get userPhoto;
  @override
  int get rank;
  @override
  int get totalPoints;
  @override
  int get level;
  @override
  int get salesCount;
  @override
  int get recruitsCount;
  @override
  bool? get isCurrentUser;

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$LeaderboardEntryImplCopyWith<_$LeaderboardEntryImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
