// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'site_visit_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$SiteVisitModel {

 String get id; String get agentId; String get agentName;// Customer Info
 String? get customerId; String? get customerName; String? get customerPhone;// Location
 String get colonyId; String get colonyName; List<String>? get plotIdsShown; List<String>? get plotNumbersShown;// GPS Coordinates
 double get latitude; double get longitude; String? get address; double? get accuracy;// Visit Details
 DateTime get visitStartTime; DateTime? get visitEndTime; Duration? get duration; String? get purpose;// initial_visit, follow_up, document_collection, etc.
// Feedback
 String? get customerFeedback; String? get agentNotes; String? get outcome;// interested, not_interested, thinking, booking_done
// Follow-up
 bool? get followUpRequired; DateTime? get followUpDate; String? get followUpType;// Media
 List<String>? get photos; List<String>? get videos; String? get voiceNoteUrl;// Offline Sync
 bool? get isOfflineCreated; DateTime? get syncedAt;// Timestamps
 DateTime? get createdAt; DateTime? get updatedAt;
/// Create a copy of SiteVisitModel
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SiteVisitModelCopyWith<SiteVisitModel> get copyWith => _$SiteVisitModelCopyWithImpl<SiteVisitModel>(this as SiteVisitModel, _$identity);

  /// Serializes this SiteVisitModel to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SiteVisitModel&&(identical(other.id, id) || other.id == id)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.agentName, agentName) || other.agentName == agentName)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.customerPhone, customerPhone) || other.customerPhone == customerPhone)&&(identical(other.colonyId, colonyId) || other.colonyId == colonyId)&&(identical(other.colonyName, colonyName) || other.colonyName == colonyName)&&const DeepCollectionEquality().equals(other.plotIdsShown, plotIdsShown)&&const DeepCollectionEquality().equals(other.plotNumbersShown, plotNumbersShown)&&(identical(other.latitude, latitude) || other.latitude == latitude)&&(identical(other.longitude, longitude) || other.longitude == longitude)&&(identical(other.address, address) || other.address == address)&&(identical(other.accuracy, accuracy) || other.accuracy == accuracy)&&(identical(other.visitStartTime, visitStartTime) || other.visitStartTime == visitStartTime)&&(identical(other.visitEndTime, visitEndTime) || other.visitEndTime == visitEndTime)&&(identical(other.duration, duration) || other.duration == duration)&&(identical(other.purpose, purpose) || other.purpose == purpose)&&(identical(other.customerFeedback, customerFeedback) || other.customerFeedback == customerFeedback)&&(identical(other.agentNotes, agentNotes) || other.agentNotes == agentNotes)&&(identical(other.outcome, outcome) || other.outcome == outcome)&&(identical(other.followUpRequired, followUpRequired) || other.followUpRequired == followUpRequired)&&(identical(other.followUpDate, followUpDate) || other.followUpDate == followUpDate)&&(identical(other.followUpType, followUpType) || other.followUpType == followUpType)&&const DeepCollectionEquality().equals(other.photos, photos)&&const DeepCollectionEquality().equals(other.videos, videos)&&(identical(other.voiceNoteUrl, voiceNoteUrl) || other.voiceNoteUrl == voiceNoteUrl)&&(identical(other.isOfflineCreated, isOfflineCreated) || other.isOfflineCreated == isOfflineCreated)&&(identical(other.syncedAt, syncedAt) || other.syncedAt == syncedAt)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,agentId,agentName,customerId,customerName,customerPhone,colonyId,colonyName,const DeepCollectionEquality().hash(plotIdsShown),const DeepCollectionEquality().hash(plotNumbersShown),latitude,longitude,address,accuracy,visitStartTime,visitEndTime,duration,purpose,customerFeedback,agentNotes,outcome,followUpRequired,followUpDate,followUpType,const DeepCollectionEquality().hash(photos),const DeepCollectionEquality().hash(videos),voiceNoteUrl,isOfflineCreated,syncedAt,createdAt,updatedAt]);

@override
String toString() {
  return 'SiteVisitModel(id: $id, agentId: $agentId, agentName: $agentName, customerId: $customerId, customerName: $customerName, customerPhone: $customerPhone, colonyId: $colonyId, colonyName: $colonyName, plotIdsShown: $plotIdsShown, plotNumbersShown: $plotNumbersShown, latitude: $latitude, longitude: $longitude, address: $address, accuracy: $accuracy, visitStartTime: $visitStartTime, visitEndTime: $visitEndTime, duration: $duration, purpose: $purpose, customerFeedback: $customerFeedback, agentNotes: $agentNotes, outcome: $outcome, followUpRequired: $followUpRequired, followUpDate: $followUpDate, followUpType: $followUpType, photos: $photos, videos: $videos, voiceNoteUrl: $voiceNoteUrl, isOfflineCreated: $isOfflineCreated, syncedAt: $syncedAt, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class $SiteVisitModelCopyWith<$Res>  {
  factory $SiteVisitModelCopyWith(SiteVisitModel value, $Res Function(SiteVisitModel) _then) = _$SiteVisitModelCopyWithImpl;
@useResult
$Res call({
 String id, String agentId, String agentName, String? customerId, String? customerName, String? customerPhone, String colonyId, String colonyName, List<String>? plotIdsShown, List<String>? plotNumbersShown, double latitude, double longitude, String? address, double? accuracy, DateTime visitStartTime, DateTime? visitEndTime, Duration? duration, String? purpose, String? customerFeedback, String? agentNotes, String? outcome, bool? followUpRequired, DateTime? followUpDate, String? followUpType, List<String>? photos, List<String>? videos, String? voiceNoteUrl, bool? isOfflineCreated, DateTime? syncedAt, DateTime? createdAt, DateTime? updatedAt
});




}
/// @nodoc
class _$SiteVisitModelCopyWithImpl<$Res>
    implements $SiteVisitModelCopyWith<$Res> {
  _$SiteVisitModelCopyWithImpl(this._self, this._then);

  final SiteVisitModel _self;
  final $Res Function(SiteVisitModel) _then;

/// Create a copy of SiteVisitModel
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? agentId = null,Object? agentName = null,Object? customerId = freezed,Object? customerName = freezed,Object? customerPhone = freezed,Object? colonyId = null,Object? colonyName = null,Object? plotIdsShown = freezed,Object? plotNumbersShown = freezed,Object? latitude = null,Object? longitude = null,Object? address = freezed,Object? accuracy = freezed,Object? visitStartTime = null,Object? visitEndTime = freezed,Object? duration = freezed,Object? purpose = freezed,Object? customerFeedback = freezed,Object? agentNotes = freezed,Object? outcome = freezed,Object? followUpRequired = freezed,Object? followUpDate = freezed,Object? followUpType = freezed,Object? photos = freezed,Object? videos = freezed,Object? voiceNoteUrl = freezed,Object? isOfflineCreated = freezed,Object? syncedAt = freezed,Object? createdAt = freezed,Object? updatedAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,agentId: null == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String,agentName: null == agentName ? _self.agentName : agentName // ignore: cast_nullable_to_non_nullable
as String,customerId: freezed == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String?,customerName: freezed == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String?,customerPhone: freezed == customerPhone ? _self.customerPhone : customerPhone // ignore: cast_nullable_to_non_nullable
as String?,colonyId: null == colonyId ? _self.colonyId : colonyId // ignore: cast_nullable_to_non_nullable
as String,colonyName: null == colonyName ? _self.colonyName : colonyName // ignore: cast_nullable_to_non_nullable
as String,plotIdsShown: freezed == plotIdsShown ? _self.plotIdsShown : plotIdsShown // ignore: cast_nullable_to_non_nullable
as List<String>?,plotNumbersShown: freezed == plotNumbersShown ? _self.plotNumbersShown : plotNumbersShown // ignore: cast_nullable_to_non_nullable
as List<String>?,latitude: null == latitude ? _self.latitude : latitude // ignore: cast_nullable_to_non_nullable
as double,longitude: null == longitude ? _self.longitude : longitude // ignore: cast_nullable_to_non_nullable
as double,address: freezed == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String?,accuracy: freezed == accuracy ? _self.accuracy : accuracy // ignore: cast_nullable_to_non_nullable
as double?,visitStartTime: null == visitStartTime ? _self.visitStartTime : visitStartTime // ignore: cast_nullable_to_non_nullable
as DateTime,visitEndTime: freezed == visitEndTime ? _self.visitEndTime : visitEndTime // ignore: cast_nullable_to_non_nullable
as DateTime?,duration: freezed == duration ? _self.duration : duration // ignore: cast_nullable_to_non_nullable
as Duration?,purpose: freezed == purpose ? _self.purpose : purpose // ignore: cast_nullable_to_non_nullable
as String?,customerFeedback: freezed == customerFeedback ? _self.customerFeedback : customerFeedback // ignore: cast_nullable_to_non_nullable
as String?,agentNotes: freezed == agentNotes ? _self.agentNotes : agentNotes // ignore: cast_nullable_to_non_nullable
as String?,outcome: freezed == outcome ? _self.outcome : outcome // ignore: cast_nullable_to_non_nullable
as String?,followUpRequired: freezed == followUpRequired ? _self.followUpRequired : followUpRequired // ignore: cast_nullable_to_non_nullable
as bool?,followUpDate: freezed == followUpDate ? _self.followUpDate : followUpDate // ignore: cast_nullable_to_non_nullable
as DateTime?,followUpType: freezed == followUpType ? _self.followUpType : followUpType // ignore: cast_nullable_to_non_nullable
as String?,photos: freezed == photos ? _self.photos : photos // ignore: cast_nullable_to_non_nullable
as List<String>?,videos: freezed == videos ? _self.videos : videos // ignore: cast_nullable_to_non_nullable
as List<String>?,voiceNoteUrl: freezed == voiceNoteUrl ? _self.voiceNoteUrl : voiceNoteUrl // ignore: cast_nullable_to_non_nullable
as String?,isOfflineCreated: freezed == isOfflineCreated ? _self.isOfflineCreated : isOfflineCreated // ignore: cast_nullable_to_non_nullable
as bool?,syncedAt: freezed == syncedAt ? _self.syncedAt : syncedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,updatedAt: freezed == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [SiteVisitModel].
extension SiteVisitModelPatterns on SiteVisitModel {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _SiteVisitModel value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _SiteVisitModel() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _SiteVisitModel value)  $default,){
final _that = this;
switch (_that) {
case _SiteVisitModel():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _SiteVisitModel value)?  $default,){
final _that = this;
switch (_that) {
case _SiteVisitModel() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String agentId,  String agentName,  String? customerId,  String? customerName,  String? customerPhone,  String colonyId,  String colonyName,  List<String>? plotIdsShown,  List<String>? plotNumbersShown,  double latitude,  double longitude,  String? address,  double? accuracy,  DateTime visitStartTime,  DateTime? visitEndTime,  Duration? duration,  String? purpose,  String? customerFeedback,  String? agentNotes,  String? outcome,  bool? followUpRequired,  DateTime? followUpDate,  String? followUpType,  List<String>? photos,  List<String>? videos,  String? voiceNoteUrl,  bool? isOfflineCreated,  DateTime? syncedAt,  DateTime? createdAt,  DateTime? updatedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _SiteVisitModel() when $default != null:
return $default(_that.id,_that.agentId,_that.agentName,_that.customerId,_that.customerName,_that.customerPhone,_that.colonyId,_that.colonyName,_that.plotIdsShown,_that.plotNumbersShown,_that.latitude,_that.longitude,_that.address,_that.accuracy,_that.visitStartTime,_that.visitEndTime,_that.duration,_that.purpose,_that.customerFeedback,_that.agentNotes,_that.outcome,_that.followUpRequired,_that.followUpDate,_that.followUpType,_that.photos,_that.videos,_that.voiceNoteUrl,_that.isOfflineCreated,_that.syncedAt,_that.createdAt,_that.updatedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String agentId,  String agentName,  String? customerId,  String? customerName,  String? customerPhone,  String colonyId,  String colonyName,  List<String>? plotIdsShown,  List<String>? plotNumbersShown,  double latitude,  double longitude,  String? address,  double? accuracy,  DateTime visitStartTime,  DateTime? visitEndTime,  Duration? duration,  String? purpose,  String? customerFeedback,  String? agentNotes,  String? outcome,  bool? followUpRequired,  DateTime? followUpDate,  String? followUpType,  List<String>? photos,  List<String>? videos,  String? voiceNoteUrl,  bool? isOfflineCreated,  DateTime? syncedAt,  DateTime? createdAt,  DateTime? updatedAt)  $default,) {final _that = this;
switch (_that) {
case _SiteVisitModel():
return $default(_that.id,_that.agentId,_that.agentName,_that.customerId,_that.customerName,_that.customerPhone,_that.colonyId,_that.colonyName,_that.plotIdsShown,_that.plotNumbersShown,_that.latitude,_that.longitude,_that.address,_that.accuracy,_that.visitStartTime,_that.visitEndTime,_that.duration,_that.purpose,_that.customerFeedback,_that.agentNotes,_that.outcome,_that.followUpRequired,_that.followUpDate,_that.followUpType,_that.photos,_that.videos,_that.voiceNoteUrl,_that.isOfflineCreated,_that.syncedAt,_that.createdAt,_that.updatedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String agentId,  String agentName,  String? customerId,  String? customerName,  String? customerPhone,  String colonyId,  String colonyName,  List<String>? plotIdsShown,  List<String>? plotNumbersShown,  double latitude,  double longitude,  String? address,  double? accuracy,  DateTime visitStartTime,  DateTime? visitEndTime,  Duration? duration,  String? purpose,  String? customerFeedback,  String? agentNotes,  String? outcome,  bool? followUpRequired,  DateTime? followUpDate,  String? followUpType,  List<String>? photos,  List<String>? videos,  String? voiceNoteUrl,  bool? isOfflineCreated,  DateTime? syncedAt,  DateTime? createdAt,  DateTime? updatedAt)?  $default,) {final _that = this;
switch (_that) {
case _SiteVisitModel() when $default != null:
return $default(_that.id,_that.agentId,_that.agentName,_that.customerId,_that.customerName,_that.customerPhone,_that.colonyId,_that.colonyName,_that.plotIdsShown,_that.plotNumbersShown,_that.latitude,_that.longitude,_that.address,_that.accuracy,_that.visitStartTime,_that.visitEndTime,_that.duration,_that.purpose,_that.customerFeedback,_that.agentNotes,_that.outcome,_that.followUpRequired,_that.followUpDate,_that.followUpType,_that.photos,_that.videos,_that.voiceNoteUrl,_that.isOfflineCreated,_that.syncedAt,_that.createdAt,_that.updatedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _SiteVisitModel implements SiteVisitModel {
  const _SiteVisitModel({this.id = '', this.agentId = '', this.agentName = '', this.customerId, this.customerName, this.customerPhone, this.colonyId = '', this.colonyName = '', final  List<String>? plotIdsShown, final  List<String>? plotNumbersShown, this.latitude = 0.0, this.longitude = 0.0, this.address, this.accuracy, required this.visitStartTime, this.visitEndTime, this.duration, this.purpose, this.customerFeedback, this.agentNotes, this.outcome, this.followUpRequired, this.followUpDate, this.followUpType, final  List<String>? photos, final  List<String>? videos, this.voiceNoteUrl, this.isOfflineCreated, this.syncedAt, this.createdAt, this.updatedAt}): _plotIdsShown = plotIdsShown,_plotNumbersShown = plotNumbersShown,_photos = photos,_videos = videos;
  factory _SiteVisitModel.fromJson(Map<String, dynamic> json) => _$SiteVisitModelFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String agentId;
@override@JsonKey() final  String agentName;
// Customer Info
@override final  String? customerId;
@override final  String? customerName;
@override final  String? customerPhone;
// Location
@override@JsonKey() final  String colonyId;
@override@JsonKey() final  String colonyName;
 final  List<String>? _plotIdsShown;
@override List<String>? get plotIdsShown {
  final value = _plotIdsShown;
  if (value == null) return null;
  if (_plotIdsShown is EqualUnmodifiableListView) return _plotIdsShown;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<String>? _plotNumbersShown;
@override List<String>? get plotNumbersShown {
  final value = _plotNumbersShown;
  if (value == null) return null;
  if (_plotNumbersShown is EqualUnmodifiableListView) return _plotNumbersShown;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

// GPS Coordinates
@override@JsonKey() final  double latitude;
@override@JsonKey() final  double longitude;
@override final  String? address;
@override final  double? accuracy;
// Visit Details
@override final  DateTime visitStartTime;
@override final  DateTime? visitEndTime;
@override final  Duration? duration;
@override final  String? purpose;
// initial_visit, follow_up, document_collection, etc.
// Feedback
@override final  String? customerFeedback;
@override final  String? agentNotes;
@override final  String? outcome;
// interested, not_interested, thinking, booking_done
// Follow-up
@override final  bool? followUpRequired;
@override final  DateTime? followUpDate;
@override final  String? followUpType;
// Media
 final  List<String>? _photos;
// Media
@override List<String>? get photos {
  final value = _photos;
  if (value == null) return null;
  if (_photos is EqualUnmodifiableListView) return _photos;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<String>? _videos;
@override List<String>? get videos {
  final value = _videos;
  if (value == null) return null;
  if (_videos is EqualUnmodifiableListView) return _videos;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

@override final  String? voiceNoteUrl;
// Offline Sync
@override final  bool? isOfflineCreated;
@override final  DateTime? syncedAt;
// Timestamps
@override final  DateTime? createdAt;
@override final  DateTime? updatedAt;

/// Create a copy of SiteVisitModel
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$SiteVisitModelCopyWith<_SiteVisitModel> get copyWith => __$SiteVisitModelCopyWithImpl<_SiteVisitModel>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$SiteVisitModelToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _SiteVisitModel&&(identical(other.id, id) || other.id == id)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.agentName, agentName) || other.agentName == agentName)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.customerPhone, customerPhone) || other.customerPhone == customerPhone)&&(identical(other.colonyId, colonyId) || other.colonyId == colonyId)&&(identical(other.colonyName, colonyName) || other.colonyName == colonyName)&&const DeepCollectionEquality().equals(other._plotIdsShown, _plotIdsShown)&&const DeepCollectionEquality().equals(other._plotNumbersShown, _plotNumbersShown)&&(identical(other.latitude, latitude) || other.latitude == latitude)&&(identical(other.longitude, longitude) || other.longitude == longitude)&&(identical(other.address, address) || other.address == address)&&(identical(other.accuracy, accuracy) || other.accuracy == accuracy)&&(identical(other.visitStartTime, visitStartTime) || other.visitStartTime == visitStartTime)&&(identical(other.visitEndTime, visitEndTime) || other.visitEndTime == visitEndTime)&&(identical(other.duration, duration) || other.duration == duration)&&(identical(other.purpose, purpose) || other.purpose == purpose)&&(identical(other.customerFeedback, customerFeedback) || other.customerFeedback == customerFeedback)&&(identical(other.agentNotes, agentNotes) || other.agentNotes == agentNotes)&&(identical(other.outcome, outcome) || other.outcome == outcome)&&(identical(other.followUpRequired, followUpRequired) || other.followUpRequired == followUpRequired)&&(identical(other.followUpDate, followUpDate) || other.followUpDate == followUpDate)&&(identical(other.followUpType, followUpType) || other.followUpType == followUpType)&&const DeepCollectionEquality().equals(other._photos, _photos)&&const DeepCollectionEquality().equals(other._videos, _videos)&&(identical(other.voiceNoteUrl, voiceNoteUrl) || other.voiceNoteUrl == voiceNoteUrl)&&(identical(other.isOfflineCreated, isOfflineCreated) || other.isOfflineCreated == isOfflineCreated)&&(identical(other.syncedAt, syncedAt) || other.syncedAt == syncedAt)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,agentId,agentName,customerId,customerName,customerPhone,colonyId,colonyName,const DeepCollectionEquality().hash(_plotIdsShown),const DeepCollectionEquality().hash(_plotNumbersShown),latitude,longitude,address,accuracy,visitStartTime,visitEndTime,duration,purpose,customerFeedback,agentNotes,outcome,followUpRequired,followUpDate,followUpType,const DeepCollectionEquality().hash(_photos),const DeepCollectionEquality().hash(_videos),voiceNoteUrl,isOfflineCreated,syncedAt,createdAt,updatedAt]);

@override
String toString() {
  return 'SiteVisitModel(id: $id, agentId: $agentId, agentName: $agentName, customerId: $customerId, customerName: $customerName, customerPhone: $customerPhone, colonyId: $colonyId, colonyName: $colonyName, plotIdsShown: $plotIdsShown, plotNumbersShown: $plotNumbersShown, latitude: $latitude, longitude: $longitude, address: $address, accuracy: $accuracy, visitStartTime: $visitStartTime, visitEndTime: $visitEndTime, duration: $duration, purpose: $purpose, customerFeedback: $customerFeedback, agentNotes: $agentNotes, outcome: $outcome, followUpRequired: $followUpRequired, followUpDate: $followUpDate, followUpType: $followUpType, photos: $photos, videos: $videos, voiceNoteUrl: $voiceNoteUrl, isOfflineCreated: $isOfflineCreated, syncedAt: $syncedAt, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class _$SiteVisitModelCopyWith<$Res> implements $SiteVisitModelCopyWith<$Res> {
  factory _$SiteVisitModelCopyWith(_SiteVisitModel value, $Res Function(_SiteVisitModel) _then) = __$SiteVisitModelCopyWithImpl;
@override @useResult
$Res call({
 String id, String agentId, String agentName, String? customerId, String? customerName, String? customerPhone, String colonyId, String colonyName, List<String>? plotIdsShown, List<String>? plotNumbersShown, double latitude, double longitude, String? address, double? accuracy, DateTime visitStartTime, DateTime? visitEndTime, Duration? duration, String? purpose, String? customerFeedback, String? agentNotes, String? outcome, bool? followUpRequired, DateTime? followUpDate, String? followUpType, List<String>? photos, List<String>? videos, String? voiceNoteUrl, bool? isOfflineCreated, DateTime? syncedAt, DateTime? createdAt, DateTime? updatedAt
});




}
/// @nodoc
class __$SiteVisitModelCopyWithImpl<$Res>
    implements _$SiteVisitModelCopyWith<$Res> {
  __$SiteVisitModelCopyWithImpl(this._self, this._then);

  final _SiteVisitModel _self;
  final $Res Function(_SiteVisitModel) _then;

/// Create a copy of SiteVisitModel
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? agentId = null,Object? agentName = null,Object? customerId = freezed,Object? customerName = freezed,Object? customerPhone = freezed,Object? colonyId = null,Object? colonyName = null,Object? plotIdsShown = freezed,Object? plotNumbersShown = freezed,Object? latitude = null,Object? longitude = null,Object? address = freezed,Object? accuracy = freezed,Object? visitStartTime = null,Object? visitEndTime = freezed,Object? duration = freezed,Object? purpose = freezed,Object? customerFeedback = freezed,Object? agentNotes = freezed,Object? outcome = freezed,Object? followUpRequired = freezed,Object? followUpDate = freezed,Object? followUpType = freezed,Object? photos = freezed,Object? videos = freezed,Object? voiceNoteUrl = freezed,Object? isOfflineCreated = freezed,Object? syncedAt = freezed,Object? createdAt = freezed,Object? updatedAt = freezed,}) {
  return _then(_SiteVisitModel(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,agentId: null == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String,agentName: null == agentName ? _self.agentName : agentName // ignore: cast_nullable_to_non_nullable
as String,customerId: freezed == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String?,customerName: freezed == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String?,customerPhone: freezed == customerPhone ? _self.customerPhone : customerPhone // ignore: cast_nullable_to_non_nullable
as String?,colonyId: null == colonyId ? _self.colonyId : colonyId // ignore: cast_nullable_to_non_nullable
as String,colonyName: null == colonyName ? _self.colonyName : colonyName // ignore: cast_nullable_to_non_nullable
as String,plotIdsShown: freezed == plotIdsShown ? _self._plotIdsShown : plotIdsShown // ignore: cast_nullable_to_non_nullable
as List<String>?,plotNumbersShown: freezed == plotNumbersShown ? _self._plotNumbersShown : plotNumbersShown // ignore: cast_nullable_to_non_nullable
as List<String>?,latitude: null == latitude ? _self.latitude : latitude // ignore: cast_nullable_to_non_nullable
as double,longitude: null == longitude ? _self.longitude : longitude // ignore: cast_nullable_to_non_nullable
as double,address: freezed == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String?,accuracy: freezed == accuracy ? _self.accuracy : accuracy // ignore: cast_nullable_to_non_nullable
as double?,visitStartTime: null == visitStartTime ? _self.visitStartTime : visitStartTime // ignore: cast_nullable_to_non_nullable
as DateTime,visitEndTime: freezed == visitEndTime ? _self.visitEndTime : visitEndTime // ignore: cast_nullable_to_non_nullable
as DateTime?,duration: freezed == duration ? _self.duration : duration // ignore: cast_nullable_to_non_nullable
as Duration?,purpose: freezed == purpose ? _self.purpose : purpose // ignore: cast_nullable_to_non_nullable
as String?,customerFeedback: freezed == customerFeedback ? _self.customerFeedback : customerFeedback // ignore: cast_nullable_to_non_nullable
as String?,agentNotes: freezed == agentNotes ? _self.agentNotes : agentNotes // ignore: cast_nullable_to_non_nullable
as String?,outcome: freezed == outcome ? _self.outcome : outcome // ignore: cast_nullable_to_non_nullable
as String?,followUpRequired: freezed == followUpRequired ? _self.followUpRequired : followUpRequired // ignore: cast_nullable_to_non_nullable
as bool?,followUpDate: freezed == followUpDate ? _self.followUpDate : followUpDate // ignore: cast_nullable_to_non_nullable
as DateTime?,followUpType: freezed == followUpType ? _self.followUpType : followUpType // ignore: cast_nullable_to_non_nullable
as String?,photos: freezed == photos ? _self._photos : photos // ignore: cast_nullable_to_non_nullable
as List<String>?,videos: freezed == videos ? _self._videos : videos // ignore: cast_nullable_to_non_nullable
as List<String>?,voiceNoteUrl: freezed == voiceNoteUrl ? _self.voiceNoteUrl : voiceNoteUrl // ignore: cast_nullable_to_non_nullable
as String?,isOfflineCreated: freezed == isOfflineCreated ? _self.isOfflineCreated : isOfflineCreated // ignore: cast_nullable_to_non_nullable
as bool?,syncedAt: freezed == syncedAt ? _self.syncedAt : syncedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,updatedAt: freezed == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$LiveLocationModel {

 String get userId; String get userName; String get userType;// agent, customer
 double get latitude; double get longitude; DateTime get timestamp; double? get speed; double? get heading; double? get accuracy; bool? get isSharingEnabled; DateTime? get sharingStartedAt; DateTime? get sharingExpiresAt; String? get sharedWith;
/// Create a copy of LiveLocationModel
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LiveLocationModelCopyWith<LiveLocationModel> get copyWith => _$LiveLocationModelCopyWithImpl<LiveLocationModel>(this as LiveLocationModel, _$identity);

  /// Serializes this LiveLocationModel to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is LiveLocationModel&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.userName, userName) || other.userName == userName)&&(identical(other.userType, userType) || other.userType == userType)&&(identical(other.latitude, latitude) || other.latitude == latitude)&&(identical(other.longitude, longitude) || other.longitude == longitude)&&(identical(other.timestamp, timestamp) || other.timestamp == timestamp)&&(identical(other.speed, speed) || other.speed == speed)&&(identical(other.heading, heading) || other.heading == heading)&&(identical(other.accuracy, accuracy) || other.accuracy == accuracy)&&(identical(other.isSharingEnabled, isSharingEnabled) || other.isSharingEnabled == isSharingEnabled)&&(identical(other.sharingStartedAt, sharingStartedAt) || other.sharingStartedAt == sharingStartedAt)&&(identical(other.sharingExpiresAt, sharingExpiresAt) || other.sharingExpiresAt == sharingExpiresAt)&&(identical(other.sharedWith, sharedWith) || other.sharedWith == sharedWith));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,userId,userName,userType,latitude,longitude,timestamp,speed,heading,accuracy,isSharingEnabled,sharingStartedAt,sharingExpiresAt,sharedWith);

@override
String toString() {
  return 'LiveLocationModel(userId: $userId, userName: $userName, userType: $userType, latitude: $latitude, longitude: $longitude, timestamp: $timestamp, speed: $speed, heading: $heading, accuracy: $accuracy, isSharingEnabled: $isSharingEnabled, sharingStartedAt: $sharingStartedAt, sharingExpiresAt: $sharingExpiresAt, sharedWith: $sharedWith)';
}


}

/// @nodoc
abstract mixin class $LiveLocationModelCopyWith<$Res>  {
  factory $LiveLocationModelCopyWith(LiveLocationModel value, $Res Function(LiveLocationModel) _then) = _$LiveLocationModelCopyWithImpl;
@useResult
$Res call({
 String userId, String userName, String userType, double latitude, double longitude, DateTime timestamp, double? speed, double? heading, double? accuracy, bool? isSharingEnabled, DateTime? sharingStartedAt, DateTime? sharingExpiresAt, String? sharedWith
});




}
/// @nodoc
class _$LiveLocationModelCopyWithImpl<$Res>
    implements $LiveLocationModelCopyWith<$Res> {
  _$LiveLocationModelCopyWithImpl(this._self, this._then);

  final LiveLocationModel _self;
  final $Res Function(LiveLocationModel) _then;

/// Create a copy of LiveLocationModel
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? userId = null,Object? userName = null,Object? userType = null,Object? latitude = null,Object? longitude = null,Object? timestamp = null,Object? speed = freezed,Object? heading = freezed,Object? accuracy = freezed,Object? isSharingEnabled = freezed,Object? sharingStartedAt = freezed,Object? sharingExpiresAt = freezed,Object? sharedWith = freezed,}) {
  return _then(_self.copyWith(
userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,userName: null == userName ? _self.userName : userName // ignore: cast_nullable_to_non_nullable
as String,userType: null == userType ? _self.userType : userType // ignore: cast_nullable_to_non_nullable
as String,latitude: null == latitude ? _self.latitude : latitude // ignore: cast_nullable_to_non_nullable
as double,longitude: null == longitude ? _self.longitude : longitude // ignore: cast_nullable_to_non_nullable
as double,timestamp: null == timestamp ? _self.timestamp : timestamp // ignore: cast_nullable_to_non_nullable
as DateTime,speed: freezed == speed ? _self.speed : speed // ignore: cast_nullable_to_non_nullable
as double?,heading: freezed == heading ? _self.heading : heading // ignore: cast_nullable_to_non_nullable
as double?,accuracy: freezed == accuracy ? _self.accuracy : accuracy // ignore: cast_nullable_to_non_nullable
as double?,isSharingEnabled: freezed == isSharingEnabled ? _self.isSharingEnabled : isSharingEnabled // ignore: cast_nullable_to_non_nullable
as bool?,sharingStartedAt: freezed == sharingStartedAt ? _self.sharingStartedAt : sharingStartedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,sharingExpiresAt: freezed == sharingExpiresAt ? _self.sharingExpiresAt : sharingExpiresAt // ignore: cast_nullable_to_non_nullable
as DateTime?,sharedWith: freezed == sharedWith ? _self.sharedWith : sharedWith // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [LiveLocationModel].
extension LiveLocationModelPatterns on LiveLocationModel {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _LiveLocationModel value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _LiveLocationModel() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _LiveLocationModel value)  $default,){
final _that = this;
switch (_that) {
case _LiveLocationModel():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _LiveLocationModel value)?  $default,){
final _that = this;
switch (_that) {
case _LiveLocationModel() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String userId,  String userName,  String userType,  double latitude,  double longitude,  DateTime timestamp,  double? speed,  double? heading,  double? accuracy,  bool? isSharingEnabled,  DateTime? sharingStartedAt,  DateTime? sharingExpiresAt,  String? sharedWith)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _LiveLocationModel() when $default != null:
return $default(_that.userId,_that.userName,_that.userType,_that.latitude,_that.longitude,_that.timestamp,_that.speed,_that.heading,_that.accuracy,_that.isSharingEnabled,_that.sharingStartedAt,_that.sharingExpiresAt,_that.sharedWith);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String userId,  String userName,  String userType,  double latitude,  double longitude,  DateTime timestamp,  double? speed,  double? heading,  double? accuracy,  bool? isSharingEnabled,  DateTime? sharingStartedAt,  DateTime? sharingExpiresAt,  String? sharedWith)  $default,) {final _that = this;
switch (_that) {
case _LiveLocationModel():
return $default(_that.userId,_that.userName,_that.userType,_that.latitude,_that.longitude,_that.timestamp,_that.speed,_that.heading,_that.accuracy,_that.isSharingEnabled,_that.sharingStartedAt,_that.sharingExpiresAt,_that.sharedWith);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String userId,  String userName,  String userType,  double latitude,  double longitude,  DateTime timestamp,  double? speed,  double? heading,  double? accuracy,  bool? isSharingEnabled,  DateTime? sharingStartedAt,  DateTime? sharingExpiresAt,  String? sharedWith)?  $default,) {final _that = this;
switch (_that) {
case _LiveLocationModel() when $default != null:
return $default(_that.userId,_that.userName,_that.userType,_that.latitude,_that.longitude,_that.timestamp,_that.speed,_that.heading,_that.accuracy,_that.isSharingEnabled,_that.sharingStartedAt,_that.sharingExpiresAt,_that.sharedWith);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _LiveLocationModel implements LiveLocationModel {
  const _LiveLocationModel({this.userId = '', this.userName = '', this.userType = '', this.latitude = 0.0, this.longitude = 0.0, required this.timestamp, this.speed, this.heading, this.accuracy, this.isSharingEnabled, this.sharingStartedAt, this.sharingExpiresAt, this.sharedWith});
  factory _LiveLocationModel.fromJson(Map<String, dynamic> json) => _$LiveLocationModelFromJson(json);

@override@JsonKey() final  String userId;
@override@JsonKey() final  String userName;
@override@JsonKey() final  String userType;
// agent, customer
@override@JsonKey() final  double latitude;
@override@JsonKey() final  double longitude;
@override final  DateTime timestamp;
@override final  double? speed;
@override final  double? heading;
@override final  double? accuracy;
@override final  bool? isSharingEnabled;
@override final  DateTime? sharingStartedAt;
@override final  DateTime? sharingExpiresAt;
@override final  String? sharedWith;

/// Create a copy of LiveLocationModel
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$LiveLocationModelCopyWith<_LiveLocationModel> get copyWith => __$LiveLocationModelCopyWithImpl<_LiveLocationModel>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$LiveLocationModelToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _LiveLocationModel&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.userName, userName) || other.userName == userName)&&(identical(other.userType, userType) || other.userType == userType)&&(identical(other.latitude, latitude) || other.latitude == latitude)&&(identical(other.longitude, longitude) || other.longitude == longitude)&&(identical(other.timestamp, timestamp) || other.timestamp == timestamp)&&(identical(other.speed, speed) || other.speed == speed)&&(identical(other.heading, heading) || other.heading == heading)&&(identical(other.accuracy, accuracy) || other.accuracy == accuracy)&&(identical(other.isSharingEnabled, isSharingEnabled) || other.isSharingEnabled == isSharingEnabled)&&(identical(other.sharingStartedAt, sharingStartedAt) || other.sharingStartedAt == sharingStartedAt)&&(identical(other.sharingExpiresAt, sharingExpiresAt) || other.sharingExpiresAt == sharingExpiresAt)&&(identical(other.sharedWith, sharedWith) || other.sharedWith == sharedWith));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,userId,userName,userType,latitude,longitude,timestamp,speed,heading,accuracy,isSharingEnabled,sharingStartedAt,sharingExpiresAt,sharedWith);

@override
String toString() {
  return 'LiveLocationModel(userId: $userId, userName: $userName, userType: $userType, latitude: $latitude, longitude: $longitude, timestamp: $timestamp, speed: $speed, heading: $heading, accuracy: $accuracy, isSharingEnabled: $isSharingEnabled, sharingStartedAt: $sharingStartedAt, sharingExpiresAt: $sharingExpiresAt, sharedWith: $sharedWith)';
}


}

/// @nodoc
abstract mixin class _$LiveLocationModelCopyWith<$Res> implements $LiveLocationModelCopyWith<$Res> {
  factory _$LiveLocationModelCopyWith(_LiveLocationModel value, $Res Function(_LiveLocationModel) _then) = __$LiveLocationModelCopyWithImpl;
@override @useResult
$Res call({
 String userId, String userName, String userType, double latitude, double longitude, DateTime timestamp, double? speed, double? heading, double? accuracy, bool? isSharingEnabled, DateTime? sharingStartedAt, DateTime? sharingExpiresAt, String? sharedWith
});




}
/// @nodoc
class __$LiveLocationModelCopyWithImpl<$Res>
    implements _$LiveLocationModelCopyWith<$Res> {
  __$LiveLocationModelCopyWithImpl(this._self, this._then);

  final _LiveLocationModel _self;
  final $Res Function(_LiveLocationModel) _then;

/// Create a copy of LiveLocationModel
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? userId = null,Object? userName = null,Object? userType = null,Object? latitude = null,Object? longitude = null,Object? timestamp = null,Object? speed = freezed,Object? heading = freezed,Object? accuracy = freezed,Object? isSharingEnabled = freezed,Object? sharingStartedAt = freezed,Object? sharingExpiresAt = freezed,Object? sharedWith = freezed,}) {
  return _then(_LiveLocationModel(
userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,userName: null == userName ? _self.userName : userName // ignore: cast_nullable_to_non_nullable
as String,userType: null == userType ? _self.userType : userType // ignore: cast_nullable_to_non_nullable
as String,latitude: null == latitude ? _self.latitude : latitude // ignore: cast_nullable_to_non_nullable
as double,longitude: null == longitude ? _self.longitude : longitude // ignore: cast_nullable_to_non_nullable
as double,timestamp: null == timestamp ? _self.timestamp : timestamp // ignore: cast_nullable_to_non_nullable
as DateTime,speed: freezed == speed ? _self.speed : speed // ignore: cast_nullable_to_non_nullable
as double?,heading: freezed == heading ? _self.heading : heading // ignore: cast_nullable_to_non_nullable
as double?,accuracy: freezed == accuracy ? _self.accuracy : accuracy // ignore: cast_nullable_to_non_nullable
as double?,isSharingEnabled: freezed == isSharingEnabled ? _self.isSharingEnabled : isSharingEnabled // ignore: cast_nullable_to_non_nullable
as bool?,sharingStartedAt: freezed == sharingStartedAt ? _self.sharingStartedAt : sharingStartedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,sharingExpiresAt: freezed == sharingExpiresAt ? _self.sharingExpiresAt : sharingExpiresAt // ignore: cast_nullable_to_non_nullable
as DateTime?,sharedWith: freezed == sharedWith ? _self.sharedWith : sharedWith // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

// dart format on
