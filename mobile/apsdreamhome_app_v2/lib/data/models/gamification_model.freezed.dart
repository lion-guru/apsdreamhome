// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'gamification_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$GamificationModel {

 String get userId; int get totalPoints; int get availablePoints; int get redeemedPoints; int get currentLevel; String get currentRank;// Progress
 int? get pointsToNextLevel; double? get levelProgressPercentage;// Streaks
 int? get currentStreak; int? get longestStreak; DateTime? get lastActivityDate;// Achievements
 List<Achievement>? get achievements; List<Badge>? get badges;// Recent Activity
 List<PointsTransaction>? get recentTransactions;// Leaderboard
 int? get leaderboardRank; int? get totalParticipants;// Timestamps
 DateTime? get createdAt; DateTime? get updatedAt;
/// Create a copy of GamificationModel
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$GamificationModelCopyWith<GamificationModel> get copyWith => _$GamificationModelCopyWithImpl<GamificationModel>(this as GamificationModel, _$identity);

  /// Serializes this GamificationModel to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is GamificationModel&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.totalPoints, totalPoints) || other.totalPoints == totalPoints)&&(identical(other.availablePoints, availablePoints) || other.availablePoints == availablePoints)&&(identical(other.redeemedPoints, redeemedPoints) || other.redeemedPoints == redeemedPoints)&&(identical(other.currentLevel, currentLevel) || other.currentLevel == currentLevel)&&(identical(other.currentRank, currentRank) || other.currentRank == currentRank)&&(identical(other.pointsToNextLevel, pointsToNextLevel) || other.pointsToNextLevel == pointsToNextLevel)&&(identical(other.levelProgressPercentage, levelProgressPercentage) || other.levelProgressPercentage == levelProgressPercentage)&&(identical(other.currentStreak, currentStreak) || other.currentStreak == currentStreak)&&(identical(other.longestStreak, longestStreak) || other.longestStreak == longestStreak)&&(identical(other.lastActivityDate, lastActivityDate) || other.lastActivityDate == lastActivityDate)&&const DeepCollectionEquality().equals(other.achievements, achievements)&&const DeepCollectionEquality().equals(other.badges, badges)&&const DeepCollectionEquality().equals(other.recentTransactions, recentTransactions)&&(identical(other.leaderboardRank, leaderboardRank) || other.leaderboardRank == leaderboardRank)&&(identical(other.totalParticipants, totalParticipants) || other.totalParticipants == totalParticipants)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,userId,totalPoints,availablePoints,redeemedPoints,currentLevel,currentRank,pointsToNextLevel,levelProgressPercentage,currentStreak,longestStreak,lastActivityDate,const DeepCollectionEquality().hash(achievements),const DeepCollectionEquality().hash(badges),const DeepCollectionEquality().hash(recentTransactions),leaderboardRank,totalParticipants,createdAt,updatedAt);

@override
String toString() {
  return 'GamificationModel(userId: $userId, totalPoints: $totalPoints, availablePoints: $availablePoints, redeemedPoints: $redeemedPoints, currentLevel: $currentLevel, currentRank: $currentRank, pointsToNextLevel: $pointsToNextLevel, levelProgressPercentage: $levelProgressPercentage, currentStreak: $currentStreak, longestStreak: $longestStreak, lastActivityDate: $lastActivityDate, achievements: $achievements, badges: $badges, recentTransactions: $recentTransactions, leaderboardRank: $leaderboardRank, totalParticipants: $totalParticipants, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class $GamificationModelCopyWith<$Res>  {
  factory $GamificationModelCopyWith(GamificationModel value, $Res Function(GamificationModel) _then) = _$GamificationModelCopyWithImpl;
@useResult
$Res call({
 String userId, int totalPoints, int availablePoints, int redeemedPoints, int currentLevel, String currentRank, int? pointsToNextLevel, double? levelProgressPercentage, int? currentStreak, int? longestStreak, DateTime? lastActivityDate, List<Achievement>? achievements, List<Badge>? badges, List<PointsTransaction>? recentTransactions, int? leaderboardRank, int? totalParticipants, DateTime? createdAt, DateTime? updatedAt
});




}
/// @nodoc
class _$GamificationModelCopyWithImpl<$Res>
    implements $GamificationModelCopyWith<$Res> {
  _$GamificationModelCopyWithImpl(this._self, this._then);

  final GamificationModel _self;
  final $Res Function(GamificationModel) _then;

/// Create a copy of GamificationModel
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? userId = null,Object? totalPoints = null,Object? availablePoints = null,Object? redeemedPoints = null,Object? currentLevel = null,Object? currentRank = null,Object? pointsToNextLevel = freezed,Object? levelProgressPercentage = freezed,Object? currentStreak = freezed,Object? longestStreak = freezed,Object? lastActivityDate = freezed,Object? achievements = freezed,Object? badges = freezed,Object? recentTransactions = freezed,Object? leaderboardRank = freezed,Object? totalParticipants = freezed,Object? createdAt = freezed,Object? updatedAt = freezed,}) {
  return _then(_self.copyWith(
userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,totalPoints: null == totalPoints ? _self.totalPoints : totalPoints // ignore: cast_nullable_to_non_nullable
as int,availablePoints: null == availablePoints ? _self.availablePoints : availablePoints // ignore: cast_nullable_to_non_nullable
as int,redeemedPoints: null == redeemedPoints ? _self.redeemedPoints : redeemedPoints // ignore: cast_nullable_to_non_nullable
as int,currentLevel: null == currentLevel ? _self.currentLevel : currentLevel // ignore: cast_nullable_to_non_nullable
as int,currentRank: null == currentRank ? _self.currentRank : currentRank // ignore: cast_nullable_to_non_nullable
as String,pointsToNextLevel: freezed == pointsToNextLevel ? _self.pointsToNextLevel : pointsToNextLevel // ignore: cast_nullable_to_non_nullable
as int?,levelProgressPercentage: freezed == levelProgressPercentage ? _self.levelProgressPercentage : levelProgressPercentage // ignore: cast_nullable_to_non_nullable
as double?,currentStreak: freezed == currentStreak ? _self.currentStreak : currentStreak // ignore: cast_nullable_to_non_nullable
as int?,longestStreak: freezed == longestStreak ? _self.longestStreak : longestStreak // ignore: cast_nullable_to_non_nullable
as int?,lastActivityDate: freezed == lastActivityDate ? _self.lastActivityDate : lastActivityDate // ignore: cast_nullable_to_non_nullable
as DateTime?,achievements: freezed == achievements ? _self.achievements : achievements // ignore: cast_nullable_to_non_nullable
as List<Achievement>?,badges: freezed == badges ? _self.badges : badges // ignore: cast_nullable_to_non_nullable
as List<Badge>?,recentTransactions: freezed == recentTransactions ? _self.recentTransactions : recentTransactions // ignore: cast_nullable_to_non_nullable
as List<PointsTransaction>?,leaderboardRank: freezed == leaderboardRank ? _self.leaderboardRank : leaderboardRank // ignore: cast_nullable_to_non_nullable
as int?,totalParticipants: freezed == totalParticipants ? _self.totalParticipants : totalParticipants // ignore: cast_nullable_to_non_nullable
as int?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,updatedAt: freezed == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [GamificationModel].
extension GamificationModelPatterns on GamificationModel {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _GamificationModel value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _GamificationModel() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _GamificationModel value)  $default,){
final _that = this;
switch (_that) {
case _GamificationModel():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _GamificationModel value)?  $default,){
final _that = this;
switch (_that) {
case _GamificationModel() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String userId,  int totalPoints,  int availablePoints,  int redeemedPoints,  int currentLevel,  String currentRank,  int? pointsToNextLevel,  double? levelProgressPercentage,  int? currentStreak,  int? longestStreak,  DateTime? lastActivityDate,  List<Achievement>? achievements,  List<Badge>? badges,  List<PointsTransaction>? recentTransactions,  int? leaderboardRank,  int? totalParticipants,  DateTime? createdAt,  DateTime? updatedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _GamificationModel() when $default != null:
return $default(_that.userId,_that.totalPoints,_that.availablePoints,_that.redeemedPoints,_that.currentLevel,_that.currentRank,_that.pointsToNextLevel,_that.levelProgressPercentage,_that.currentStreak,_that.longestStreak,_that.lastActivityDate,_that.achievements,_that.badges,_that.recentTransactions,_that.leaderboardRank,_that.totalParticipants,_that.createdAt,_that.updatedAt);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String userId,  int totalPoints,  int availablePoints,  int redeemedPoints,  int currentLevel,  String currentRank,  int? pointsToNextLevel,  double? levelProgressPercentage,  int? currentStreak,  int? longestStreak,  DateTime? lastActivityDate,  List<Achievement>? achievements,  List<Badge>? badges,  List<PointsTransaction>? recentTransactions,  int? leaderboardRank,  int? totalParticipants,  DateTime? createdAt,  DateTime? updatedAt)  $default,) {final _that = this;
switch (_that) {
case _GamificationModel():
return $default(_that.userId,_that.totalPoints,_that.availablePoints,_that.redeemedPoints,_that.currentLevel,_that.currentRank,_that.pointsToNextLevel,_that.levelProgressPercentage,_that.currentStreak,_that.longestStreak,_that.lastActivityDate,_that.achievements,_that.badges,_that.recentTransactions,_that.leaderboardRank,_that.totalParticipants,_that.createdAt,_that.updatedAt);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String userId,  int totalPoints,  int availablePoints,  int redeemedPoints,  int currentLevel,  String currentRank,  int? pointsToNextLevel,  double? levelProgressPercentage,  int? currentStreak,  int? longestStreak,  DateTime? lastActivityDate,  List<Achievement>? achievements,  List<Badge>? badges,  List<PointsTransaction>? recentTransactions,  int? leaderboardRank,  int? totalParticipants,  DateTime? createdAt,  DateTime? updatedAt)?  $default,) {final _that = this;
switch (_that) {
case _GamificationModel() when $default != null:
return $default(_that.userId,_that.totalPoints,_that.availablePoints,_that.redeemedPoints,_that.currentLevel,_that.currentRank,_that.pointsToNextLevel,_that.levelProgressPercentage,_that.currentStreak,_that.longestStreak,_that.lastActivityDate,_that.achievements,_that.badges,_that.recentTransactions,_that.leaderboardRank,_that.totalParticipants,_that.createdAt,_that.updatedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _GamificationModel implements GamificationModel {
  const _GamificationModel({this.userId = '', this.totalPoints = 0, this.availablePoints = 0, this.redeemedPoints = 0, this.currentLevel = 0, this.currentRank = '', this.pointsToNextLevel, this.levelProgressPercentage, this.currentStreak, this.longestStreak, this.lastActivityDate, final  List<Achievement>? achievements, final  List<Badge>? badges, final  List<PointsTransaction>? recentTransactions, this.leaderboardRank, this.totalParticipants, this.createdAt, this.updatedAt}): _achievements = achievements,_badges = badges,_recentTransactions = recentTransactions;
  factory _GamificationModel.fromJson(Map<String, dynamic> json) => _$GamificationModelFromJson(json);

@override@JsonKey() final  String userId;
@override@JsonKey() final  int totalPoints;
@override@JsonKey() final  int availablePoints;
@override@JsonKey() final  int redeemedPoints;
@override@JsonKey() final  int currentLevel;
@override@JsonKey() final  String currentRank;
// Progress
@override final  int? pointsToNextLevel;
@override final  double? levelProgressPercentage;
// Streaks
@override final  int? currentStreak;
@override final  int? longestStreak;
@override final  DateTime? lastActivityDate;
// Achievements
 final  List<Achievement>? _achievements;
// Achievements
@override List<Achievement>? get achievements {
  final value = _achievements;
  if (value == null) return null;
  if (_achievements is EqualUnmodifiableListView) return _achievements;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<Badge>? _badges;
@override List<Badge>? get badges {
  final value = _badges;
  if (value == null) return null;
  if (_badges is EqualUnmodifiableListView) return _badges;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

// Recent Activity
 final  List<PointsTransaction>? _recentTransactions;
// Recent Activity
@override List<PointsTransaction>? get recentTransactions {
  final value = _recentTransactions;
  if (value == null) return null;
  if (_recentTransactions is EqualUnmodifiableListView) return _recentTransactions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

// Leaderboard
@override final  int? leaderboardRank;
@override final  int? totalParticipants;
// Timestamps
@override final  DateTime? createdAt;
@override final  DateTime? updatedAt;

/// Create a copy of GamificationModel
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$GamificationModelCopyWith<_GamificationModel> get copyWith => __$GamificationModelCopyWithImpl<_GamificationModel>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$GamificationModelToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _GamificationModel&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.totalPoints, totalPoints) || other.totalPoints == totalPoints)&&(identical(other.availablePoints, availablePoints) || other.availablePoints == availablePoints)&&(identical(other.redeemedPoints, redeemedPoints) || other.redeemedPoints == redeemedPoints)&&(identical(other.currentLevel, currentLevel) || other.currentLevel == currentLevel)&&(identical(other.currentRank, currentRank) || other.currentRank == currentRank)&&(identical(other.pointsToNextLevel, pointsToNextLevel) || other.pointsToNextLevel == pointsToNextLevel)&&(identical(other.levelProgressPercentage, levelProgressPercentage) || other.levelProgressPercentage == levelProgressPercentage)&&(identical(other.currentStreak, currentStreak) || other.currentStreak == currentStreak)&&(identical(other.longestStreak, longestStreak) || other.longestStreak == longestStreak)&&(identical(other.lastActivityDate, lastActivityDate) || other.lastActivityDate == lastActivityDate)&&const DeepCollectionEquality().equals(other._achievements, _achievements)&&const DeepCollectionEquality().equals(other._badges, _badges)&&const DeepCollectionEquality().equals(other._recentTransactions, _recentTransactions)&&(identical(other.leaderboardRank, leaderboardRank) || other.leaderboardRank == leaderboardRank)&&(identical(other.totalParticipants, totalParticipants) || other.totalParticipants == totalParticipants)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,userId,totalPoints,availablePoints,redeemedPoints,currentLevel,currentRank,pointsToNextLevel,levelProgressPercentage,currentStreak,longestStreak,lastActivityDate,const DeepCollectionEquality().hash(_achievements),const DeepCollectionEquality().hash(_badges),const DeepCollectionEquality().hash(_recentTransactions),leaderboardRank,totalParticipants,createdAt,updatedAt);

@override
String toString() {
  return 'GamificationModel(userId: $userId, totalPoints: $totalPoints, availablePoints: $availablePoints, redeemedPoints: $redeemedPoints, currentLevel: $currentLevel, currentRank: $currentRank, pointsToNextLevel: $pointsToNextLevel, levelProgressPercentage: $levelProgressPercentage, currentStreak: $currentStreak, longestStreak: $longestStreak, lastActivityDate: $lastActivityDate, achievements: $achievements, badges: $badges, recentTransactions: $recentTransactions, leaderboardRank: $leaderboardRank, totalParticipants: $totalParticipants, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class _$GamificationModelCopyWith<$Res> implements $GamificationModelCopyWith<$Res> {
  factory _$GamificationModelCopyWith(_GamificationModel value, $Res Function(_GamificationModel) _then) = __$GamificationModelCopyWithImpl;
@override @useResult
$Res call({
 String userId, int totalPoints, int availablePoints, int redeemedPoints, int currentLevel, String currentRank, int? pointsToNextLevel, double? levelProgressPercentage, int? currentStreak, int? longestStreak, DateTime? lastActivityDate, List<Achievement>? achievements, List<Badge>? badges, List<PointsTransaction>? recentTransactions, int? leaderboardRank, int? totalParticipants, DateTime? createdAt, DateTime? updatedAt
});




}
/// @nodoc
class __$GamificationModelCopyWithImpl<$Res>
    implements _$GamificationModelCopyWith<$Res> {
  __$GamificationModelCopyWithImpl(this._self, this._then);

  final _GamificationModel _self;
  final $Res Function(_GamificationModel) _then;

/// Create a copy of GamificationModel
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? userId = null,Object? totalPoints = null,Object? availablePoints = null,Object? redeemedPoints = null,Object? currentLevel = null,Object? currentRank = null,Object? pointsToNextLevel = freezed,Object? levelProgressPercentage = freezed,Object? currentStreak = freezed,Object? longestStreak = freezed,Object? lastActivityDate = freezed,Object? achievements = freezed,Object? badges = freezed,Object? recentTransactions = freezed,Object? leaderboardRank = freezed,Object? totalParticipants = freezed,Object? createdAt = freezed,Object? updatedAt = freezed,}) {
  return _then(_GamificationModel(
userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,totalPoints: null == totalPoints ? _self.totalPoints : totalPoints // ignore: cast_nullable_to_non_nullable
as int,availablePoints: null == availablePoints ? _self.availablePoints : availablePoints // ignore: cast_nullable_to_non_nullable
as int,redeemedPoints: null == redeemedPoints ? _self.redeemedPoints : redeemedPoints // ignore: cast_nullable_to_non_nullable
as int,currentLevel: null == currentLevel ? _self.currentLevel : currentLevel // ignore: cast_nullable_to_non_nullable
as int,currentRank: null == currentRank ? _self.currentRank : currentRank // ignore: cast_nullable_to_non_nullable
as String,pointsToNextLevel: freezed == pointsToNextLevel ? _self.pointsToNextLevel : pointsToNextLevel // ignore: cast_nullable_to_non_nullable
as int?,levelProgressPercentage: freezed == levelProgressPercentage ? _self.levelProgressPercentage : levelProgressPercentage // ignore: cast_nullable_to_non_nullable
as double?,currentStreak: freezed == currentStreak ? _self.currentStreak : currentStreak // ignore: cast_nullable_to_non_nullable
as int?,longestStreak: freezed == longestStreak ? _self.longestStreak : longestStreak // ignore: cast_nullable_to_non_nullable
as int?,lastActivityDate: freezed == lastActivityDate ? _self.lastActivityDate : lastActivityDate // ignore: cast_nullable_to_non_nullable
as DateTime?,achievements: freezed == achievements ? _self._achievements : achievements // ignore: cast_nullable_to_non_nullable
as List<Achievement>?,badges: freezed == badges ? _self._badges : badges // ignore: cast_nullable_to_non_nullable
as List<Badge>?,recentTransactions: freezed == recentTransactions ? _self._recentTransactions : recentTransactions // ignore: cast_nullable_to_non_nullable
as List<PointsTransaction>?,leaderboardRank: freezed == leaderboardRank ? _self.leaderboardRank : leaderboardRank // ignore: cast_nullable_to_non_nullable
as int?,totalParticipants: freezed == totalParticipants ? _self.totalParticipants : totalParticipants // ignore: cast_nullable_to_non_nullable
as int?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,updatedAt: freezed == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$PointsTransaction {

 String get id; String get userId; int get points; String get type;// earned, redeemed, adjusted
 String get activityType; String? get description; String? get metadata; int? get balanceBefore; int? get balanceAfter; DateTime? get createdAt;
/// Create a copy of PointsTransaction
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PointsTransactionCopyWith<PointsTransaction> get copyWith => _$PointsTransactionCopyWithImpl<PointsTransaction>(this as PointsTransaction, _$identity);

  /// Serializes this PointsTransaction to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PointsTransaction&&(identical(other.id, id) || other.id == id)&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.points, points) || other.points == points)&&(identical(other.type, type) || other.type == type)&&(identical(other.activityType, activityType) || other.activityType == activityType)&&(identical(other.description, description) || other.description == description)&&(identical(other.metadata, metadata) || other.metadata == metadata)&&(identical(other.balanceBefore, balanceBefore) || other.balanceBefore == balanceBefore)&&(identical(other.balanceAfter, balanceAfter) || other.balanceAfter == balanceAfter)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,userId,points,type,activityType,description,metadata,balanceBefore,balanceAfter,createdAt);

@override
String toString() {
  return 'PointsTransaction(id: $id, userId: $userId, points: $points, type: $type, activityType: $activityType, description: $description, metadata: $metadata, balanceBefore: $balanceBefore, balanceAfter: $balanceAfter, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class $PointsTransactionCopyWith<$Res>  {
  factory $PointsTransactionCopyWith(PointsTransaction value, $Res Function(PointsTransaction) _then) = _$PointsTransactionCopyWithImpl;
@useResult
$Res call({
 String id, String userId, int points, String type, String activityType, String? description, String? metadata, int? balanceBefore, int? balanceAfter, DateTime? createdAt
});




}
/// @nodoc
class _$PointsTransactionCopyWithImpl<$Res>
    implements $PointsTransactionCopyWith<$Res> {
  _$PointsTransactionCopyWithImpl(this._self, this._then);

  final PointsTransaction _self;
  final $Res Function(PointsTransaction) _then;

/// Create a copy of PointsTransaction
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? userId = null,Object? points = null,Object? type = null,Object? activityType = null,Object? description = freezed,Object? metadata = freezed,Object? balanceBefore = freezed,Object? balanceAfter = freezed,Object? createdAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,points: null == points ? _self.points : points // ignore: cast_nullable_to_non_nullable
as int,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,activityType: null == activityType ? _self.activityType : activityType // ignore: cast_nullable_to_non_nullable
as String,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,metadata: freezed == metadata ? _self.metadata : metadata // ignore: cast_nullable_to_non_nullable
as String?,balanceBefore: freezed == balanceBefore ? _self.balanceBefore : balanceBefore // ignore: cast_nullable_to_non_nullable
as int?,balanceAfter: freezed == balanceAfter ? _self.balanceAfter : balanceAfter // ignore: cast_nullable_to_non_nullable
as int?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [PointsTransaction].
extension PointsTransactionPatterns on PointsTransaction {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PointsTransaction value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PointsTransaction() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PointsTransaction value)  $default,){
final _that = this;
switch (_that) {
case _PointsTransaction():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PointsTransaction value)?  $default,){
final _that = this;
switch (_that) {
case _PointsTransaction() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String userId,  int points,  String type,  String activityType,  String? description,  String? metadata,  int? balanceBefore,  int? balanceAfter,  DateTime? createdAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PointsTransaction() when $default != null:
return $default(_that.id,_that.userId,_that.points,_that.type,_that.activityType,_that.description,_that.metadata,_that.balanceBefore,_that.balanceAfter,_that.createdAt);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String userId,  int points,  String type,  String activityType,  String? description,  String? metadata,  int? balanceBefore,  int? balanceAfter,  DateTime? createdAt)  $default,) {final _that = this;
switch (_that) {
case _PointsTransaction():
return $default(_that.id,_that.userId,_that.points,_that.type,_that.activityType,_that.description,_that.metadata,_that.balanceBefore,_that.balanceAfter,_that.createdAt);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String userId,  int points,  String type,  String activityType,  String? description,  String? metadata,  int? balanceBefore,  int? balanceAfter,  DateTime? createdAt)?  $default,) {final _that = this;
switch (_that) {
case _PointsTransaction() when $default != null:
return $default(_that.id,_that.userId,_that.points,_that.type,_that.activityType,_that.description,_that.metadata,_that.balanceBefore,_that.balanceAfter,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PointsTransaction implements PointsTransaction {
  const _PointsTransaction({this.id = '', this.userId = '', this.points = 0, this.type = '', this.activityType = '', this.description, this.metadata, this.balanceBefore, this.balanceAfter, this.createdAt});
  factory _PointsTransaction.fromJson(Map<String, dynamic> json) => _$PointsTransactionFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String userId;
@override@JsonKey() final  int points;
@override@JsonKey() final  String type;
// earned, redeemed, adjusted
@override@JsonKey() final  String activityType;
@override final  String? description;
@override final  String? metadata;
@override final  int? balanceBefore;
@override final  int? balanceAfter;
@override final  DateTime? createdAt;

/// Create a copy of PointsTransaction
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PointsTransactionCopyWith<_PointsTransaction> get copyWith => __$PointsTransactionCopyWithImpl<_PointsTransaction>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PointsTransactionToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _PointsTransaction&&(identical(other.id, id) || other.id == id)&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.points, points) || other.points == points)&&(identical(other.type, type) || other.type == type)&&(identical(other.activityType, activityType) || other.activityType == activityType)&&(identical(other.description, description) || other.description == description)&&(identical(other.metadata, metadata) || other.metadata == metadata)&&(identical(other.balanceBefore, balanceBefore) || other.balanceBefore == balanceBefore)&&(identical(other.balanceAfter, balanceAfter) || other.balanceAfter == balanceAfter)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,userId,points,type,activityType,description,metadata,balanceBefore,balanceAfter,createdAt);

@override
String toString() {
  return 'PointsTransaction(id: $id, userId: $userId, points: $points, type: $type, activityType: $activityType, description: $description, metadata: $metadata, balanceBefore: $balanceBefore, balanceAfter: $balanceAfter, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$PointsTransactionCopyWith<$Res> implements $PointsTransactionCopyWith<$Res> {
  factory _$PointsTransactionCopyWith(_PointsTransaction value, $Res Function(_PointsTransaction) _then) = __$PointsTransactionCopyWithImpl;
@override @useResult
$Res call({
 String id, String userId, int points, String type, String activityType, String? description, String? metadata, int? balanceBefore, int? balanceAfter, DateTime? createdAt
});




}
/// @nodoc
class __$PointsTransactionCopyWithImpl<$Res>
    implements _$PointsTransactionCopyWith<$Res> {
  __$PointsTransactionCopyWithImpl(this._self, this._then);

  final _PointsTransaction _self;
  final $Res Function(_PointsTransaction) _then;

/// Create a copy of PointsTransaction
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? userId = null,Object? points = null,Object? type = null,Object? activityType = null,Object? description = freezed,Object? metadata = freezed,Object? balanceBefore = freezed,Object? balanceAfter = freezed,Object? createdAt = freezed,}) {
  return _then(_PointsTransaction(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,points: null == points ? _self.points : points // ignore: cast_nullable_to_non_nullable
as int,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,activityType: null == activityType ? _self.activityType : activityType // ignore: cast_nullable_to_non_nullable
as String,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,metadata: freezed == metadata ? _self.metadata : metadata // ignore: cast_nullable_to_non_nullable
as String?,balanceBefore: freezed == balanceBefore ? _self.balanceBefore : balanceBefore // ignore: cast_nullable_to_non_nullable
as int?,balanceAfter: freezed == balanceAfter ? _self.balanceAfter : balanceAfter // ignore: cast_nullable_to_non_nullable
as int?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$Achievement {

 String get id; String get name; String get description; String get icon; int get pointsReward; String get category;// sales, recruitment, activity, training
 String get condition; int? get targetValue; int? get currentValue; double? get progressPercentage; bool? get isCompleted; DateTime? get completedAt; DateTime? get createdAt;
/// Create a copy of Achievement
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AchievementCopyWith<Achievement> get copyWith => _$AchievementCopyWithImpl<Achievement>(this as Achievement, _$identity);

  /// Serializes this Achievement to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Achievement&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.description, description) || other.description == description)&&(identical(other.icon, icon) || other.icon == icon)&&(identical(other.pointsReward, pointsReward) || other.pointsReward == pointsReward)&&(identical(other.category, category) || other.category == category)&&(identical(other.condition, condition) || other.condition == condition)&&(identical(other.targetValue, targetValue) || other.targetValue == targetValue)&&(identical(other.currentValue, currentValue) || other.currentValue == currentValue)&&(identical(other.progressPercentage, progressPercentage) || other.progressPercentage == progressPercentage)&&(identical(other.isCompleted, isCompleted) || other.isCompleted == isCompleted)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,description,icon,pointsReward,category,condition,targetValue,currentValue,progressPercentage,isCompleted,completedAt,createdAt);

@override
String toString() {
  return 'Achievement(id: $id, name: $name, description: $description, icon: $icon, pointsReward: $pointsReward, category: $category, condition: $condition, targetValue: $targetValue, currentValue: $currentValue, progressPercentage: $progressPercentage, isCompleted: $isCompleted, completedAt: $completedAt, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class $AchievementCopyWith<$Res>  {
  factory $AchievementCopyWith(Achievement value, $Res Function(Achievement) _then) = _$AchievementCopyWithImpl;
@useResult
$Res call({
 String id, String name, String description, String icon, int pointsReward, String category, String condition, int? targetValue, int? currentValue, double? progressPercentage, bool? isCompleted, DateTime? completedAt, DateTime? createdAt
});




}
/// @nodoc
class _$AchievementCopyWithImpl<$Res>
    implements $AchievementCopyWith<$Res> {
  _$AchievementCopyWithImpl(this._self, this._then);

  final Achievement _self;
  final $Res Function(Achievement) _then;

/// Create a copy of Achievement
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? description = null,Object? icon = null,Object? pointsReward = null,Object? category = null,Object? condition = null,Object? targetValue = freezed,Object? currentValue = freezed,Object? progressPercentage = freezed,Object? isCompleted = freezed,Object? completedAt = freezed,Object? createdAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,description: null == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String,icon: null == icon ? _self.icon : icon // ignore: cast_nullable_to_non_nullable
as String,pointsReward: null == pointsReward ? _self.pointsReward : pointsReward // ignore: cast_nullable_to_non_nullable
as int,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,condition: null == condition ? _self.condition : condition // ignore: cast_nullable_to_non_nullable
as String,targetValue: freezed == targetValue ? _self.targetValue : targetValue // ignore: cast_nullable_to_non_nullable
as int?,currentValue: freezed == currentValue ? _self.currentValue : currentValue // ignore: cast_nullable_to_non_nullable
as int?,progressPercentage: freezed == progressPercentage ? _self.progressPercentage : progressPercentage // ignore: cast_nullable_to_non_nullable
as double?,isCompleted: freezed == isCompleted ? _self.isCompleted : isCompleted // ignore: cast_nullable_to_non_nullable
as bool?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [Achievement].
extension AchievementPatterns on Achievement {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Achievement value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Achievement() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Achievement value)  $default,){
final _that = this;
switch (_that) {
case _Achievement():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Achievement value)?  $default,){
final _that = this;
switch (_that) {
case _Achievement() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String name,  String description,  String icon,  int pointsReward,  String category,  String condition,  int? targetValue,  int? currentValue,  double? progressPercentage,  bool? isCompleted,  DateTime? completedAt,  DateTime? createdAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _Achievement() when $default != null:
return $default(_that.id,_that.name,_that.description,_that.icon,_that.pointsReward,_that.category,_that.condition,_that.targetValue,_that.currentValue,_that.progressPercentage,_that.isCompleted,_that.completedAt,_that.createdAt);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String name,  String description,  String icon,  int pointsReward,  String category,  String condition,  int? targetValue,  int? currentValue,  double? progressPercentage,  bool? isCompleted,  DateTime? completedAt,  DateTime? createdAt)  $default,) {final _that = this;
switch (_that) {
case _Achievement():
return $default(_that.id,_that.name,_that.description,_that.icon,_that.pointsReward,_that.category,_that.condition,_that.targetValue,_that.currentValue,_that.progressPercentage,_that.isCompleted,_that.completedAt,_that.createdAt);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String name,  String description,  String icon,  int pointsReward,  String category,  String condition,  int? targetValue,  int? currentValue,  double? progressPercentage,  bool? isCompleted,  DateTime? completedAt,  DateTime? createdAt)?  $default,) {final _that = this;
switch (_that) {
case _Achievement() when $default != null:
return $default(_that.id,_that.name,_that.description,_that.icon,_that.pointsReward,_that.category,_that.condition,_that.targetValue,_that.currentValue,_that.progressPercentage,_that.isCompleted,_that.completedAt,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _Achievement implements Achievement {
  const _Achievement({this.id = '', this.name = '', this.description = '', this.icon = '', this.pointsReward = 0, this.category = '', this.condition = '', this.targetValue, this.currentValue, this.progressPercentage, this.isCompleted, this.completedAt, this.createdAt});
  factory _Achievement.fromJson(Map<String, dynamic> json) => _$AchievementFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String name;
@override@JsonKey() final  String description;
@override@JsonKey() final  String icon;
@override@JsonKey() final  int pointsReward;
@override@JsonKey() final  String category;
// sales, recruitment, activity, training
@override@JsonKey() final  String condition;
@override final  int? targetValue;
@override final  int? currentValue;
@override final  double? progressPercentage;
@override final  bool? isCompleted;
@override final  DateTime? completedAt;
@override final  DateTime? createdAt;

/// Create a copy of Achievement
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AchievementCopyWith<_Achievement> get copyWith => __$AchievementCopyWithImpl<_Achievement>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$AchievementToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _Achievement&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.description, description) || other.description == description)&&(identical(other.icon, icon) || other.icon == icon)&&(identical(other.pointsReward, pointsReward) || other.pointsReward == pointsReward)&&(identical(other.category, category) || other.category == category)&&(identical(other.condition, condition) || other.condition == condition)&&(identical(other.targetValue, targetValue) || other.targetValue == targetValue)&&(identical(other.currentValue, currentValue) || other.currentValue == currentValue)&&(identical(other.progressPercentage, progressPercentage) || other.progressPercentage == progressPercentage)&&(identical(other.isCompleted, isCompleted) || other.isCompleted == isCompleted)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,description,icon,pointsReward,category,condition,targetValue,currentValue,progressPercentage,isCompleted,completedAt,createdAt);

@override
String toString() {
  return 'Achievement(id: $id, name: $name, description: $description, icon: $icon, pointsReward: $pointsReward, category: $category, condition: $condition, targetValue: $targetValue, currentValue: $currentValue, progressPercentage: $progressPercentage, isCompleted: $isCompleted, completedAt: $completedAt, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$AchievementCopyWith<$Res> implements $AchievementCopyWith<$Res> {
  factory _$AchievementCopyWith(_Achievement value, $Res Function(_Achievement) _then) = __$AchievementCopyWithImpl;
@override @useResult
$Res call({
 String id, String name, String description, String icon, int pointsReward, String category, String condition, int? targetValue, int? currentValue, double? progressPercentage, bool? isCompleted, DateTime? completedAt, DateTime? createdAt
});




}
/// @nodoc
class __$AchievementCopyWithImpl<$Res>
    implements _$AchievementCopyWith<$Res> {
  __$AchievementCopyWithImpl(this._self, this._then);

  final _Achievement _self;
  final $Res Function(_Achievement) _then;

/// Create a copy of Achievement
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? description = null,Object? icon = null,Object? pointsReward = null,Object? category = null,Object? condition = null,Object? targetValue = freezed,Object? currentValue = freezed,Object? progressPercentage = freezed,Object? isCompleted = freezed,Object? completedAt = freezed,Object? createdAt = freezed,}) {
  return _then(_Achievement(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,description: null == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String,icon: null == icon ? _self.icon : icon // ignore: cast_nullable_to_non_nullable
as String,pointsReward: null == pointsReward ? _self.pointsReward : pointsReward // ignore: cast_nullable_to_non_nullable
as int,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,condition: null == condition ? _self.condition : condition // ignore: cast_nullable_to_non_nullable
as String,targetValue: freezed == targetValue ? _self.targetValue : targetValue // ignore: cast_nullable_to_non_nullable
as int?,currentValue: freezed == currentValue ? _self.currentValue : currentValue // ignore: cast_nullable_to_non_nullable
as int?,progressPercentage: freezed == progressPercentage ? _self.progressPercentage : progressPercentage // ignore: cast_nullable_to_non_nullable
as double?,isCompleted: freezed == isCompleted ? _self.isCompleted : isCompleted // ignore: cast_nullable_to_non_nullable
as bool?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$Badge {

 String get id; String get name; String get description; String get icon; String get rarity;// bronze, silver, gold, platinum, diamond
 String get category; DateTime? get earnedAt; DateTime? get createdAt;
/// Create a copy of Badge
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BadgeCopyWith<Badge> get copyWith => _$BadgeCopyWithImpl<Badge>(this as Badge, _$identity);

  /// Serializes this Badge to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Badge&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.description, description) || other.description == description)&&(identical(other.icon, icon) || other.icon == icon)&&(identical(other.rarity, rarity) || other.rarity == rarity)&&(identical(other.category, category) || other.category == category)&&(identical(other.earnedAt, earnedAt) || other.earnedAt == earnedAt)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,description,icon,rarity,category,earnedAt,createdAt);

@override
String toString() {
  return 'Badge(id: $id, name: $name, description: $description, icon: $icon, rarity: $rarity, category: $category, earnedAt: $earnedAt, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class $BadgeCopyWith<$Res>  {
  factory $BadgeCopyWith(Badge value, $Res Function(Badge) _then) = _$BadgeCopyWithImpl;
@useResult
$Res call({
 String id, String name, String description, String icon, String rarity, String category, DateTime? earnedAt, DateTime? createdAt
});




}
/// @nodoc
class _$BadgeCopyWithImpl<$Res>
    implements $BadgeCopyWith<$Res> {
  _$BadgeCopyWithImpl(this._self, this._then);

  final Badge _self;
  final $Res Function(Badge) _then;

/// Create a copy of Badge
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? description = null,Object? icon = null,Object? rarity = null,Object? category = null,Object? earnedAt = freezed,Object? createdAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,description: null == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String,icon: null == icon ? _self.icon : icon // ignore: cast_nullable_to_non_nullable
as String,rarity: null == rarity ? _self.rarity : rarity // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,earnedAt: freezed == earnedAt ? _self.earnedAt : earnedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [Badge].
extension BadgePatterns on Badge {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Badge value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Badge() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Badge value)  $default,){
final _that = this;
switch (_that) {
case _Badge():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Badge value)?  $default,){
final _that = this;
switch (_that) {
case _Badge() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String name,  String description,  String icon,  String rarity,  String category,  DateTime? earnedAt,  DateTime? createdAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _Badge() when $default != null:
return $default(_that.id,_that.name,_that.description,_that.icon,_that.rarity,_that.category,_that.earnedAt,_that.createdAt);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String name,  String description,  String icon,  String rarity,  String category,  DateTime? earnedAt,  DateTime? createdAt)  $default,) {final _that = this;
switch (_that) {
case _Badge():
return $default(_that.id,_that.name,_that.description,_that.icon,_that.rarity,_that.category,_that.earnedAt,_that.createdAt);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String name,  String description,  String icon,  String rarity,  String category,  DateTime? earnedAt,  DateTime? createdAt)?  $default,) {final _that = this;
switch (_that) {
case _Badge() when $default != null:
return $default(_that.id,_that.name,_that.description,_that.icon,_that.rarity,_that.category,_that.earnedAt,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _Badge implements Badge {
  const _Badge({this.id = '', this.name = '', this.description = '', this.icon = '', this.rarity = '', this.category = '', this.earnedAt, this.createdAt});
  factory _Badge.fromJson(Map<String, dynamic> json) => _$BadgeFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String name;
@override@JsonKey() final  String description;
@override@JsonKey() final  String icon;
@override@JsonKey() final  String rarity;
// bronze, silver, gold, platinum, diamond
@override@JsonKey() final  String category;
@override final  DateTime? earnedAt;
@override final  DateTime? createdAt;

/// Create a copy of Badge
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BadgeCopyWith<_Badge> get copyWith => __$BadgeCopyWithImpl<_Badge>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$BadgeToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _Badge&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.description, description) || other.description == description)&&(identical(other.icon, icon) || other.icon == icon)&&(identical(other.rarity, rarity) || other.rarity == rarity)&&(identical(other.category, category) || other.category == category)&&(identical(other.earnedAt, earnedAt) || other.earnedAt == earnedAt)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,description,icon,rarity,category,earnedAt,createdAt);

@override
String toString() {
  return 'Badge(id: $id, name: $name, description: $description, icon: $icon, rarity: $rarity, category: $category, earnedAt: $earnedAt, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$BadgeCopyWith<$Res> implements $BadgeCopyWith<$Res> {
  factory _$BadgeCopyWith(_Badge value, $Res Function(_Badge) _then) = __$BadgeCopyWithImpl;
@override @useResult
$Res call({
 String id, String name, String description, String icon, String rarity, String category, DateTime? earnedAt, DateTime? createdAt
});




}
/// @nodoc
class __$BadgeCopyWithImpl<$Res>
    implements _$BadgeCopyWith<$Res> {
  __$BadgeCopyWithImpl(this._self, this._then);

  final _Badge _self;
  final $Res Function(_Badge) _then;

/// Create a copy of Badge
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? description = null,Object? icon = null,Object? rarity = null,Object? category = null,Object? earnedAt = freezed,Object? createdAt = freezed,}) {
  return _then(_Badge(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,description: null == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String,icon: null == icon ? _self.icon : icon // ignore: cast_nullable_to_non_nullable
as String,rarity: null == rarity ? _self.rarity : rarity // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,earnedAt: freezed == earnedAt ? _self.earnedAt : earnedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$Reward {

 String get id; String get name; String get description; String get imageUrl; int get pointsCost; int get stockQuantity; String? get category;// merchandise, vouchers, cash, experience
 String? get termsAndConditions; bool? get isActive; DateTime? get validFrom; DateTime? get validUntil; DateTime? get createdAt;
/// Create a copy of Reward
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RewardCopyWith<Reward> get copyWith => _$RewardCopyWithImpl<Reward>(this as Reward, _$identity);

  /// Serializes this Reward to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Reward&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.description, description) || other.description == description)&&(identical(other.imageUrl, imageUrl) || other.imageUrl == imageUrl)&&(identical(other.pointsCost, pointsCost) || other.pointsCost == pointsCost)&&(identical(other.stockQuantity, stockQuantity) || other.stockQuantity == stockQuantity)&&(identical(other.category, category) || other.category == category)&&(identical(other.termsAndConditions, termsAndConditions) || other.termsAndConditions == termsAndConditions)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.validFrom, validFrom) || other.validFrom == validFrom)&&(identical(other.validUntil, validUntil) || other.validUntil == validUntil)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,description,imageUrl,pointsCost,stockQuantity,category,termsAndConditions,isActive,validFrom,validUntil,createdAt);

@override
String toString() {
  return 'Reward(id: $id, name: $name, description: $description, imageUrl: $imageUrl, pointsCost: $pointsCost, stockQuantity: $stockQuantity, category: $category, termsAndConditions: $termsAndConditions, isActive: $isActive, validFrom: $validFrom, validUntil: $validUntil, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class $RewardCopyWith<$Res>  {
  factory $RewardCopyWith(Reward value, $Res Function(Reward) _then) = _$RewardCopyWithImpl;
@useResult
$Res call({
 String id, String name, String description, String imageUrl, int pointsCost, int stockQuantity, String? category, String? termsAndConditions, bool? isActive, DateTime? validFrom, DateTime? validUntil, DateTime? createdAt
});




}
/// @nodoc
class _$RewardCopyWithImpl<$Res>
    implements $RewardCopyWith<$Res> {
  _$RewardCopyWithImpl(this._self, this._then);

  final Reward _self;
  final $Res Function(Reward) _then;

/// Create a copy of Reward
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? description = null,Object? imageUrl = null,Object? pointsCost = null,Object? stockQuantity = null,Object? category = freezed,Object? termsAndConditions = freezed,Object? isActive = freezed,Object? validFrom = freezed,Object? validUntil = freezed,Object? createdAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,description: null == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String,imageUrl: null == imageUrl ? _self.imageUrl : imageUrl // ignore: cast_nullable_to_non_nullable
as String,pointsCost: null == pointsCost ? _self.pointsCost : pointsCost // ignore: cast_nullable_to_non_nullable
as int,stockQuantity: null == stockQuantity ? _self.stockQuantity : stockQuantity // ignore: cast_nullable_to_non_nullable
as int,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String?,termsAndConditions: freezed == termsAndConditions ? _self.termsAndConditions : termsAndConditions // ignore: cast_nullable_to_non_nullable
as String?,isActive: freezed == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool?,validFrom: freezed == validFrom ? _self.validFrom : validFrom // ignore: cast_nullable_to_non_nullable
as DateTime?,validUntil: freezed == validUntil ? _self.validUntil : validUntil // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [Reward].
extension RewardPatterns on Reward {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Reward value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Reward() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Reward value)  $default,){
final _that = this;
switch (_that) {
case _Reward():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Reward value)?  $default,){
final _that = this;
switch (_that) {
case _Reward() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String name,  String description,  String imageUrl,  int pointsCost,  int stockQuantity,  String? category,  String? termsAndConditions,  bool? isActive,  DateTime? validFrom,  DateTime? validUntil,  DateTime? createdAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _Reward() when $default != null:
return $default(_that.id,_that.name,_that.description,_that.imageUrl,_that.pointsCost,_that.stockQuantity,_that.category,_that.termsAndConditions,_that.isActive,_that.validFrom,_that.validUntil,_that.createdAt);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String name,  String description,  String imageUrl,  int pointsCost,  int stockQuantity,  String? category,  String? termsAndConditions,  bool? isActive,  DateTime? validFrom,  DateTime? validUntil,  DateTime? createdAt)  $default,) {final _that = this;
switch (_that) {
case _Reward():
return $default(_that.id,_that.name,_that.description,_that.imageUrl,_that.pointsCost,_that.stockQuantity,_that.category,_that.termsAndConditions,_that.isActive,_that.validFrom,_that.validUntil,_that.createdAt);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String name,  String description,  String imageUrl,  int pointsCost,  int stockQuantity,  String? category,  String? termsAndConditions,  bool? isActive,  DateTime? validFrom,  DateTime? validUntil,  DateTime? createdAt)?  $default,) {final _that = this;
switch (_that) {
case _Reward() when $default != null:
return $default(_that.id,_that.name,_that.description,_that.imageUrl,_that.pointsCost,_that.stockQuantity,_that.category,_that.termsAndConditions,_that.isActive,_that.validFrom,_that.validUntil,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _Reward implements Reward {
  const _Reward({this.id = '', this.name = '', this.description = '', this.imageUrl = '', this.pointsCost = 0, this.stockQuantity = 0, this.category, this.termsAndConditions, this.isActive, this.validFrom, this.validUntil, this.createdAt});
  factory _Reward.fromJson(Map<String, dynamic> json) => _$RewardFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String name;
@override@JsonKey() final  String description;
@override@JsonKey() final  String imageUrl;
@override@JsonKey() final  int pointsCost;
@override@JsonKey() final  int stockQuantity;
@override final  String? category;
// merchandise, vouchers, cash, experience
@override final  String? termsAndConditions;
@override final  bool? isActive;
@override final  DateTime? validFrom;
@override final  DateTime? validUntil;
@override final  DateTime? createdAt;

/// Create a copy of Reward
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$RewardCopyWith<_Reward> get copyWith => __$RewardCopyWithImpl<_Reward>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$RewardToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _Reward&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.description, description) || other.description == description)&&(identical(other.imageUrl, imageUrl) || other.imageUrl == imageUrl)&&(identical(other.pointsCost, pointsCost) || other.pointsCost == pointsCost)&&(identical(other.stockQuantity, stockQuantity) || other.stockQuantity == stockQuantity)&&(identical(other.category, category) || other.category == category)&&(identical(other.termsAndConditions, termsAndConditions) || other.termsAndConditions == termsAndConditions)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.validFrom, validFrom) || other.validFrom == validFrom)&&(identical(other.validUntil, validUntil) || other.validUntil == validUntil)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,description,imageUrl,pointsCost,stockQuantity,category,termsAndConditions,isActive,validFrom,validUntil,createdAt);

@override
String toString() {
  return 'Reward(id: $id, name: $name, description: $description, imageUrl: $imageUrl, pointsCost: $pointsCost, stockQuantity: $stockQuantity, category: $category, termsAndConditions: $termsAndConditions, isActive: $isActive, validFrom: $validFrom, validUntil: $validUntil, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$RewardCopyWith<$Res> implements $RewardCopyWith<$Res> {
  factory _$RewardCopyWith(_Reward value, $Res Function(_Reward) _then) = __$RewardCopyWithImpl;
@override @useResult
$Res call({
 String id, String name, String description, String imageUrl, int pointsCost, int stockQuantity, String? category, String? termsAndConditions, bool? isActive, DateTime? validFrom, DateTime? validUntil, DateTime? createdAt
});




}
/// @nodoc
class __$RewardCopyWithImpl<$Res>
    implements _$RewardCopyWith<$Res> {
  __$RewardCopyWithImpl(this._self, this._then);

  final _Reward _self;
  final $Res Function(_Reward) _then;

/// Create a copy of Reward
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? description = null,Object? imageUrl = null,Object? pointsCost = null,Object? stockQuantity = null,Object? category = freezed,Object? termsAndConditions = freezed,Object? isActive = freezed,Object? validFrom = freezed,Object? validUntil = freezed,Object? createdAt = freezed,}) {
  return _then(_Reward(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,description: null == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String,imageUrl: null == imageUrl ? _self.imageUrl : imageUrl // ignore: cast_nullable_to_non_nullable
as String,pointsCost: null == pointsCost ? _self.pointsCost : pointsCost // ignore: cast_nullable_to_non_nullable
as int,stockQuantity: null == stockQuantity ? _self.stockQuantity : stockQuantity // ignore: cast_nullable_to_non_nullable
as int,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String?,termsAndConditions: freezed == termsAndConditions ? _self.termsAndConditions : termsAndConditions // ignore: cast_nullable_to_non_nullable
as String?,isActive: freezed == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool?,validFrom: freezed == validFrom ? _self.validFrom : validFrom // ignore: cast_nullable_to_non_nullable
as DateTime?,validUntil: freezed == validUntil ? _self.validUntil : validUntil // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$RewardRedemption {

 String get id; String get userId; String get rewardId; String get rewardName; int get pointsSpent; String get status;// pending, processing, shipped, delivered, cancelled
 String? get deliveryAddress; String? get trackingNumber; DateTime? get requestedAt; DateTime? get processedAt; DateTime? get shippedAt; DateTime? get deliveredAt; String? get notes;
/// Create a copy of RewardRedemption
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RewardRedemptionCopyWith<RewardRedemption> get copyWith => _$RewardRedemptionCopyWithImpl<RewardRedemption>(this as RewardRedemption, _$identity);

  /// Serializes this RewardRedemption to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RewardRedemption&&(identical(other.id, id) || other.id == id)&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.rewardId, rewardId) || other.rewardId == rewardId)&&(identical(other.rewardName, rewardName) || other.rewardName == rewardName)&&(identical(other.pointsSpent, pointsSpent) || other.pointsSpent == pointsSpent)&&(identical(other.status, status) || other.status == status)&&(identical(other.deliveryAddress, deliveryAddress) || other.deliveryAddress == deliveryAddress)&&(identical(other.trackingNumber, trackingNumber) || other.trackingNumber == trackingNumber)&&(identical(other.requestedAt, requestedAt) || other.requestedAt == requestedAt)&&(identical(other.processedAt, processedAt) || other.processedAt == processedAt)&&(identical(other.shippedAt, shippedAt) || other.shippedAt == shippedAt)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.notes, notes) || other.notes == notes));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,userId,rewardId,rewardName,pointsSpent,status,deliveryAddress,trackingNumber,requestedAt,processedAt,shippedAt,deliveredAt,notes);

@override
String toString() {
  return 'RewardRedemption(id: $id, userId: $userId, rewardId: $rewardId, rewardName: $rewardName, pointsSpent: $pointsSpent, status: $status, deliveryAddress: $deliveryAddress, trackingNumber: $trackingNumber, requestedAt: $requestedAt, processedAt: $processedAt, shippedAt: $shippedAt, deliveredAt: $deliveredAt, notes: $notes)';
}


}

/// @nodoc
abstract mixin class $RewardRedemptionCopyWith<$Res>  {
  factory $RewardRedemptionCopyWith(RewardRedemption value, $Res Function(RewardRedemption) _then) = _$RewardRedemptionCopyWithImpl;
@useResult
$Res call({
 String id, String userId, String rewardId, String rewardName, int pointsSpent, String status, String? deliveryAddress, String? trackingNumber, DateTime? requestedAt, DateTime? processedAt, DateTime? shippedAt, DateTime? deliveredAt, String? notes
});




}
/// @nodoc
class _$RewardRedemptionCopyWithImpl<$Res>
    implements $RewardRedemptionCopyWith<$Res> {
  _$RewardRedemptionCopyWithImpl(this._self, this._then);

  final RewardRedemption _self;
  final $Res Function(RewardRedemption) _then;

/// Create a copy of RewardRedemption
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? userId = null,Object? rewardId = null,Object? rewardName = null,Object? pointsSpent = null,Object? status = null,Object? deliveryAddress = freezed,Object? trackingNumber = freezed,Object? requestedAt = freezed,Object? processedAt = freezed,Object? shippedAt = freezed,Object? deliveredAt = freezed,Object? notes = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,rewardId: null == rewardId ? _self.rewardId : rewardId // ignore: cast_nullable_to_non_nullable
as String,rewardName: null == rewardName ? _self.rewardName : rewardName // ignore: cast_nullable_to_non_nullable
as String,pointsSpent: null == pointsSpent ? _self.pointsSpent : pointsSpent // ignore: cast_nullable_to_non_nullable
as int,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,deliveryAddress: freezed == deliveryAddress ? _self.deliveryAddress : deliveryAddress // ignore: cast_nullable_to_non_nullable
as String?,trackingNumber: freezed == trackingNumber ? _self.trackingNumber : trackingNumber // ignore: cast_nullable_to_non_nullable
as String?,requestedAt: freezed == requestedAt ? _self.requestedAt : requestedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,processedAt: freezed == processedAt ? _self.processedAt : processedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,shippedAt: freezed == shippedAt ? _self.shippedAt : shippedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [RewardRedemption].
extension RewardRedemptionPatterns on RewardRedemption {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _RewardRedemption value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _RewardRedemption() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _RewardRedemption value)  $default,){
final _that = this;
switch (_that) {
case _RewardRedemption():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _RewardRedemption value)?  $default,){
final _that = this;
switch (_that) {
case _RewardRedemption() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String userId,  String rewardId,  String rewardName,  int pointsSpent,  String status,  String? deliveryAddress,  String? trackingNumber,  DateTime? requestedAt,  DateTime? processedAt,  DateTime? shippedAt,  DateTime? deliveredAt,  String? notes)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _RewardRedemption() when $default != null:
return $default(_that.id,_that.userId,_that.rewardId,_that.rewardName,_that.pointsSpent,_that.status,_that.deliveryAddress,_that.trackingNumber,_that.requestedAt,_that.processedAt,_that.shippedAt,_that.deliveredAt,_that.notes);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String userId,  String rewardId,  String rewardName,  int pointsSpent,  String status,  String? deliveryAddress,  String? trackingNumber,  DateTime? requestedAt,  DateTime? processedAt,  DateTime? shippedAt,  DateTime? deliveredAt,  String? notes)  $default,) {final _that = this;
switch (_that) {
case _RewardRedemption():
return $default(_that.id,_that.userId,_that.rewardId,_that.rewardName,_that.pointsSpent,_that.status,_that.deliveryAddress,_that.trackingNumber,_that.requestedAt,_that.processedAt,_that.shippedAt,_that.deliveredAt,_that.notes);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String userId,  String rewardId,  String rewardName,  int pointsSpent,  String status,  String? deliveryAddress,  String? trackingNumber,  DateTime? requestedAt,  DateTime? processedAt,  DateTime? shippedAt,  DateTime? deliveredAt,  String? notes)?  $default,) {final _that = this;
switch (_that) {
case _RewardRedemption() when $default != null:
return $default(_that.id,_that.userId,_that.rewardId,_that.rewardName,_that.pointsSpent,_that.status,_that.deliveryAddress,_that.trackingNumber,_that.requestedAt,_that.processedAt,_that.shippedAt,_that.deliveredAt,_that.notes);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _RewardRedemption implements RewardRedemption {
  const _RewardRedemption({this.id = '', this.userId = '', this.rewardId = '', this.rewardName = '', this.pointsSpent = 0, this.status = '', this.deliveryAddress, this.trackingNumber, this.requestedAt, this.processedAt, this.shippedAt, this.deliveredAt, this.notes});
  factory _RewardRedemption.fromJson(Map<String, dynamic> json) => _$RewardRedemptionFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String userId;
@override@JsonKey() final  String rewardId;
@override@JsonKey() final  String rewardName;
@override@JsonKey() final  int pointsSpent;
@override@JsonKey() final  String status;
// pending, processing, shipped, delivered, cancelled
@override final  String? deliveryAddress;
@override final  String? trackingNumber;
@override final  DateTime? requestedAt;
@override final  DateTime? processedAt;
@override final  DateTime? shippedAt;
@override final  DateTime? deliveredAt;
@override final  String? notes;

/// Create a copy of RewardRedemption
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$RewardRedemptionCopyWith<_RewardRedemption> get copyWith => __$RewardRedemptionCopyWithImpl<_RewardRedemption>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$RewardRedemptionToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _RewardRedemption&&(identical(other.id, id) || other.id == id)&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.rewardId, rewardId) || other.rewardId == rewardId)&&(identical(other.rewardName, rewardName) || other.rewardName == rewardName)&&(identical(other.pointsSpent, pointsSpent) || other.pointsSpent == pointsSpent)&&(identical(other.status, status) || other.status == status)&&(identical(other.deliveryAddress, deliveryAddress) || other.deliveryAddress == deliveryAddress)&&(identical(other.trackingNumber, trackingNumber) || other.trackingNumber == trackingNumber)&&(identical(other.requestedAt, requestedAt) || other.requestedAt == requestedAt)&&(identical(other.processedAt, processedAt) || other.processedAt == processedAt)&&(identical(other.shippedAt, shippedAt) || other.shippedAt == shippedAt)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.notes, notes) || other.notes == notes));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,userId,rewardId,rewardName,pointsSpent,status,deliveryAddress,trackingNumber,requestedAt,processedAt,shippedAt,deliveredAt,notes);

@override
String toString() {
  return 'RewardRedemption(id: $id, userId: $userId, rewardId: $rewardId, rewardName: $rewardName, pointsSpent: $pointsSpent, status: $status, deliveryAddress: $deliveryAddress, trackingNumber: $trackingNumber, requestedAt: $requestedAt, processedAt: $processedAt, shippedAt: $shippedAt, deliveredAt: $deliveredAt, notes: $notes)';
}


}

/// @nodoc
abstract mixin class _$RewardRedemptionCopyWith<$Res> implements $RewardRedemptionCopyWith<$Res> {
  factory _$RewardRedemptionCopyWith(_RewardRedemption value, $Res Function(_RewardRedemption) _then) = __$RewardRedemptionCopyWithImpl;
@override @useResult
$Res call({
 String id, String userId, String rewardId, String rewardName, int pointsSpent, String status, String? deliveryAddress, String? trackingNumber, DateTime? requestedAt, DateTime? processedAt, DateTime? shippedAt, DateTime? deliveredAt, String? notes
});




}
/// @nodoc
class __$RewardRedemptionCopyWithImpl<$Res>
    implements _$RewardRedemptionCopyWith<$Res> {
  __$RewardRedemptionCopyWithImpl(this._self, this._then);

  final _RewardRedemption _self;
  final $Res Function(_RewardRedemption) _then;

/// Create a copy of RewardRedemption
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? userId = null,Object? rewardId = null,Object? rewardName = null,Object? pointsSpent = null,Object? status = null,Object? deliveryAddress = freezed,Object? trackingNumber = freezed,Object? requestedAt = freezed,Object? processedAt = freezed,Object? shippedAt = freezed,Object? deliveredAt = freezed,Object? notes = freezed,}) {
  return _then(_RewardRedemption(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,rewardId: null == rewardId ? _self.rewardId : rewardId // ignore: cast_nullable_to_non_nullable
as String,rewardName: null == rewardName ? _self.rewardName : rewardName // ignore: cast_nullable_to_non_nullable
as String,pointsSpent: null == pointsSpent ? _self.pointsSpent : pointsSpent // ignore: cast_nullable_to_non_nullable
as int,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,deliveryAddress: freezed == deliveryAddress ? _self.deliveryAddress : deliveryAddress // ignore: cast_nullable_to_non_nullable
as String?,trackingNumber: freezed == trackingNumber ? _self.trackingNumber : trackingNumber // ignore: cast_nullable_to_non_nullable
as String?,requestedAt: freezed == requestedAt ? _self.requestedAt : requestedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,processedAt: freezed == processedAt ? _self.processedAt : processedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,shippedAt: freezed == shippedAt ? _self.shippedAt : shippedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$LeaderboardEntry {

 String get userId; String get userName; String get userPhoto; int get rank; int get totalPoints; int get level; int get salesCount; int get recruitsCount; bool? get isCurrentUser;
/// Create a copy of LeaderboardEntry
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LeaderboardEntryCopyWith<LeaderboardEntry> get copyWith => _$LeaderboardEntryCopyWithImpl<LeaderboardEntry>(this as LeaderboardEntry, _$identity);

  /// Serializes this LeaderboardEntry to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is LeaderboardEntry&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.userName, userName) || other.userName == userName)&&(identical(other.userPhoto, userPhoto) || other.userPhoto == userPhoto)&&(identical(other.rank, rank) || other.rank == rank)&&(identical(other.totalPoints, totalPoints) || other.totalPoints == totalPoints)&&(identical(other.level, level) || other.level == level)&&(identical(other.salesCount, salesCount) || other.salesCount == salesCount)&&(identical(other.recruitsCount, recruitsCount) || other.recruitsCount == recruitsCount)&&(identical(other.isCurrentUser, isCurrentUser) || other.isCurrentUser == isCurrentUser));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,userId,userName,userPhoto,rank,totalPoints,level,salesCount,recruitsCount,isCurrentUser);

@override
String toString() {
  return 'LeaderboardEntry(userId: $userId, userName: $userName, userPhoto: $userPhoto, rank: $rank, totalPoints: $totalPoints, level: $level, salesCount: $salesCount, recruitsCount: $recruitsCount, isCurrentUser: $isCurrentUser)';
}


}

/// @nodoc
abstract mixin class $LeaderboardEntryCopyWith<$Res>  {
  factory $LeaderboardEntryCopyWith(LeaderboardEntry value, $Res Function(LeaderboardEntry) _then) = _$LeaderboardEntryCopyWithImpl;
@useResult
$Res call({
 String userId, String userName, String userPhoto, int rank, int totalPoints, int level, int salesCount, int recruitsCount, bool? isCurrentUser
});




}
/// @nodoc
class _$LeaderboardEntryCopyWithImpl<$Res>
    implements $LeaderboardEntryCopyWith<$Res> {
  _$LeaderboardEntryCopyWithImpl(this._self, this._then);

  final LeaderboardEntry _self;
  final $Res Function(LeaderboardEntry) _then;

/// Create a copy of LeaderboardEntry
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? userId = null,Object? userName = null,Object? userPhoto = null,Object? rank = null,Object? totalPoints = null,Object? level = null,Object? salesCount = null,Object? recruitsCount = null,Object? isCurrentUser = freezed,}) {
  return _then(_self.copyWith(
userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,userName: null == userName ? _self.userName : userName // ignore: cast_nullable_to_non_nullable
as String,userPhoto: null == userPhoto ? _self.userPhoto : userPhoto // ignore: cast_nullable_to_non_nullable
as String,rank: null == rank ? _self.rank : rank // ignore: cast_nullable_to_non_nullable
as int,totalPoints: null == totalPoints ? _self.totalPoints : totalPoints // ignore: cast_nullable_to_non_nullable
as int,level: null == level ? _self.level : level // ignore: cast_nullable_to_non_nullable
as int,salesCount: null == salesCount ? _self.salesCount : salesCount // ignore: cast_nullable_to_non_nullable
as int,recruitsCount: null == recruitsCount ? _self.recruitsCount : recruitsCount // ignore: cast_nullable_to_non_nullable
as int,isCurrentUser: freezed == isCurrentUser ? _self.isCurrentUser : isCurrentUser // ignore: cast_nullable_to_non_nullable
as bool?,
  ));
}

}


/// Adds pattern-matching-related methods to [LeaderboardEntry].
extension LeaderboardEntryPatterns on LeaderboardEntry {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _LeaderboardEntry value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _LeaderboardEntry() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _LeaderboardEntry value)  $default,){
final _that = this;
switch (_that) {
case _LeaderboardEntry():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _LeaderboardEntry value)?  $default,){
final _that = this;
switch (_that) {
case _LeaderboardEntry() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String userId,  String userName,  String userPhoto,  int rank,  int totalPoints,  int level,  int salesCount,  int recruitsCount,  bool? isCurrentUser)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _LeaderboardEntry() when $default != null:
return $default(_that.userId,_that.userName,_that.userPhoto,_that.rank,_that.totalPoints,_that.level,_that.salesCount,_that.recruitsCount,_that.isCurrentUser);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String userId,  String userName,  String userPhoto,  int rank,  int totalPoints,  int level,  int salesCount,  int recruitsCount,  bool? isCurrentUser)  $default,) {final _that = this;
switch (_that) {
case _LeaderboardEntry():
return $default(_that.userId,_that.userName,_that.userPhoto,_that.rank,_that.totalPoints,_that.level,_that.salesCount,_that.recruitsCount,_that.isCurrentUser);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String userId,  String userName,  String userPhoto,  int rank,  int totalPoints,  int level,  int salesCount,  int recruitsCount,  bool? isCurrentUser)?  $default,) {final _that = this;
switch (_that) {
case _LeaderboardEntry() when $default != null:
return $default(_that.userId,_that.userName,_that.userPhoto,_that.rank,_that.totalPoints,_that.level,_that.salesCount,_that.recruitsCount,_that.isCurrentUser);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _LeaderboardEntry implements LeaderboardEntry {
  const _LeaderboardEntry({this.userId = '', this.userName = '', this.userPhoto = '', this.rank = 0, this.totalPoints = 0, this.level = 0, this.salesCount = 0, this.recruitsCount = 0, this.isCurrentUser});
  factory _LeaderboardEntry.fromJson(Map<String, dynamic> json) => _$LeaderboardEntryFromJson(json);

@override@JsonKey() final  String userId;
@override@JsonKey() final  String userName;
@override@JsonKey() final  String userPhoto;
@override@JsonKey() final  int rank;
@override@JsonKey() final  int totalPoints;
@override@JsonKey() final  int level;
@override@JsonKey() final  int salesCount;
@override@JsonKey() final  int recruitsCount;
@override final  bool? isCurrentUser;

/// Create a copy of LeaderboardEntry
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$LeaderboardEntryCopyWith<_LeaderboardEntry> get copyWith => __$LeaderboardEntryCopyWithImpl<_LeaderboardEntry>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$LeaderboardEntryToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _LeaderboardEntry&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.userName, userName) || other.userName == userName)&&(identical(other.userPhoto, userPhoto) || other.userPhoto == userPhoto)&&(identical(other.rank, rank) || other.rank == rank)&&(identical(other.totalPoints, totalPoints) || other.totalPoints == totalPoints)&&(identical(other.level, level) || other.level == level)&&(identical(other.salesCount, salesCount) || other.salesCount == salesCount)&&(identical(other.recruitsCount, recruitsCount) || other.recruitsCount == recruitsCount)&&(identical(other.isCurrentUser, isCurrentUser) || other.isCurrentUser == isCurrentUser));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,userId,userName,userPhoto,rank,totalPoints,level,salesCount,recruitsCount,isCurrentUser);

@override
String toString() {
  return 'LeaderboardEntry(userId: $userId, userName: $userName, userPhoto: $userPhoto, rank: $rank, totalPoints: $totalPoints, level: $level, salesCount: $salesCount, recruitsCount: $recruitsCount, isCurrentUser: $isCurrentUser)';
}


}

/// @nodoc
abstract mixin class _$LeaderboardEntryCopyWith<$Res> implements $LeaderboardEntryCopyWith<$Res> {
  factory _$LeaderboardEntryCopyWith(_LeaderboardEntry value, $Res Function(_LeaderboardEntry) _then) = __$LeaderboardEntryCopyWithImpl;
@override @useResult
$Res call({
 String userId, String userName, String userPhoto, int rank, int totalPoints, int level, int salesCount, int recruitsCount, bool? isCurrentUser
});




}
/// @nodoc
class __$LeaderboardEntryCopyWithImpl<$Res>
    implements _$LeaderboardEntryCopyWith<$Res> {
  __$LeaderboardEntryCopyWithImpl(this._self, this._then);

  final _LeaderboardEntry _self;
  final $Res Function(_LeaderboardEntry) _then;

/// Create a copy of LeaderboardEntry
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? userId = null,Object? userName = null,Object? userPhoto = null,Object? rank = null,Object? totalPoints = null,Object? level = null,Object? salesCount = null,Object? recruitsCount = null,Object? isCurrentUser = freezed,}) {
  return _then(_LeaderboardEntry(
userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,userName: null == userName ? _self.userName : userName // ignore: cast_nullable_to_non_nullable
as String,userPhoto: null == userPhoto ? _self.userPhoto : userPhoto // ignore: cast_nullable_to_non_nullable
as String,rank: null == rank ? _self.rank : rank // ignore: cast_nullable_to_non_nullable
as int,totalPoints: null == totalPoints ? _self.totalPoints : totalPoints // ignore: cast_nullable_to_non_nullable
as int,level: null == level ? _self.level : level // ignore: cast_nullable_to_non_nullable
as int,salesCount: null == salesCount ? _self.salesCount : salesCount // ignore: cast_nullable_to_non_nullable
as int,recruitsCount: null == recruitsCount ? _self.recruitsCount : recruitsCount // ignore: cast_nullable_to_non_nullable
as int,isCurrentUser: freezed == isCurrentUser ? _self.isCurrentUser : isCurrentUser // ignore: cast_nullable_to_non_nullable
as bool?,
  ));
}


}

// dart format on
