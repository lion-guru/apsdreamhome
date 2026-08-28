// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'notification_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$NotificationModel {

 String get id; String get userId; String get type;// booking, commission, payout, lead, general, promotional
 String get title; String get body; String? get imageUrl;// Action
 String? get actionType;// open_screen, open_url, show_dialog, none
 String? get actionData;// screen_name, url, etc.
 String? get actionUrl;// Related Entity
 String? get relatedId; String? get relatedType;// Status
 bool? get isRead; DateTime? get readAt;// Timestamps
 DateTime? get createdAt; DateTime? get expiresAt;// Delivery
 bool? get pushDelivered; DateTime? get pushDeliveredAt; String? get fcmMessageId;
/// Create a copy of NotificationModel
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$NotificationModelCopyWith<NotificationModel> get copyWith => _$NotificationModelCopyWithImpl<NotificationModel>(this as NotificationModel, _$identity);

  /// Serializes this NotificationModel to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is NotificationModel&&(identical(other.id, id) || other.id == id)&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.type, type) || other.type == type)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body)&&(identical(other.imageUrl, imageUrl) || other.imageUrl == imageUrl)&&(identical(other.actionType, actionType) || other.actionType == actionType)&&(identical(other.actionData, actionData) || other.actionData == actionData)&&(identical(other.actionUrl, actionUrl) || other.actionUrl == actionUrl)&&(identical(other.relatedId, relatedId) || other.relatedId == relatedId)&&(identical(other.relatedType, relatedType) || other.relatedType == relatedType)&&(identical(other.isRead, isRead) || other.isRead == isRead)&&(identical(other.readAt, readAt) || other.readAt == readAt)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.expiresAt, expiresAt) || other.expiresAt == expiresAt)&&(identical(other.pushDelivered, pushDelivered) || other.pushDelivered == pushDelivered)&&(identical(other.pushDeliveredAt, pushDeliveredAt) || other.pushDeliveredAt == pushDeliveredAt)&&(identical(other.fcmMessageId, fcmMessageId) || other.fcmMessageId == fcmMessageId));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,userId,type,title,body,imageUrl,actionType,actionData,actionUrl,relatedId,relatedType,isRead,readAt,createdAt,expiresAt,pushDelivered,pushDeliveredAt,fcmMessageId);

@override
String toString() {
  return 'NotificationModel(id: $id, userId: $userId, type: $type, title: $title, body: $body, imageUrl: $imageUrl, actionType: $actionType, actionData: $actionData, actionUrl: $actionUrl, relatedId: $relatedId, relatedType: $relatedType, isRead: $isRead, readAt: $readAt, createdAt: $createdAt, expiresAt: $expiresAt, pushDelivered: $pushDelivered, pushDeliveredAt: $pushDeliveredAt, fcmMessageId: $fcmMessageId)';
}


}

/// @nodoc
abstract mixin class $NotificationModelCopyWith<$Res>  {
  factory $NotificationModelCopyWith(NotificationModel value, $Res Function(NotificationModel) _then) = _$NotificationModelCopyWithImpl;
@useResult
$Res call({
 String id, String userId, String type, String title, String body, String? imageUrl, String? actionType, String? actionData, String? actionUrl, String? relatedId, String? relatedType, bool? isRead, DateTime? readAt, DateTime? createdAt, DateTime? expiresAt, bool? pushDelivered, DateTime? pushDeliveredAt, String? fcmMessageId
});




}
/// @nodoc
class _$NotificationModelCopyWithImpl<$Res>
    implements $NotificationModelCopyWith<$Res> {
  _$NotificationModelCopyWithImpl(this._self, this._then);

  final NotificationModel _self;
  final $Res Function(NotificationModel) _then;

/// Create a copy of NotificationModel
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? userId = null,Object? type = null,Object? title = null,Object? body = null,Object? imageUrl = freezed,Object? actionType = freezed,Object? actionData = freezed,Object? actionUrl = freezed,Object? relatedId = freezed,Object? relatedType = freezed,Object? isRead = freezed,Object? readAt = freezed,Object? createdAt = freezed,Object? expiresAt = freezed,Object? pushDelivered = freezed,Object? pushDeliveredAt = freezed,Object? fcmMessageId = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,imageUrl: freezed == imageUrl ? _self.imageUrl : imageUrl // ignore: cast_nullable_to_non_nullable
as String?,actionType: freezed == actionType ? _self.actionType : actionType // ignore: cast_nullable_to_non_nullable
as String?,actionData: freezed == actionData ? _self.actionData : actionData // ignore: cast_nullable_to_non_nullable
as String?,actionUrl: freezed == actionUrl ? _self.actionUrl : actionUrl // ignore: cast_nullable_to_non_nullable
as String?,relatedId: freezed == relatedId ? _self.relatedId : relatedId // ignore: cast_nullable_to_non_nullable
as String?,relatedType: freezed == relatedType ? _self.relatedType : relatedType // ignore: cast_nullable_to_non_nullable
as String?,isRead: freezed == isRead ? _self.isRead : isRead // ignore: cast_nullable_to_non_nullable
as bool?,readAt: freezed == readAt ? _self.readAt : readAt // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,expiresAt: freezed == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime?,pushDelivered: freezed == pushDelivered ? _self.pushDelivered : pushDelivered // ignore: cast_nullable_to_non_nullable
as bool?,pushDeliveredAt: freezed == pushDeliveredAt ? _self.pushDeliveredAt : pushDeliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,fcmMessageId: freezed == fcmMessageId ? _self.fcmMessageId : fcmMessageId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [NotificationModel].
extension NotificationModelPatterns on NotificationModel {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _NotificationModel value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _NotificationModel() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _NotificationModel value)  $default,){
final _that = this;
switch (_that) {
case _NotificationModel():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _NotificationModel value)?  $default,){
final _that = this;
switch (_that) {
case _NotificationModel() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String userId,  String type,  String title,  String body,  String? imageUrl,  String? actionType,  String? actionData,  String? actionUrl,  String? relatedId,  String? relatedType,  bool? isRead,  DateTime? readAt,  DateTime? createdAt,  DateTime? expiresAt,  bool? pushDelivered,  DateTime? pushDeliveredAt,  String? fcmMessageId)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _NotificationModel() when $default != null:
return $default(_that.id,_that.userId,_that.type,_that.title,_that.body,_that.imageUrl,_that.actionType,_that.actionData,_that.actionUrl,_that.relatedId,_that.relatedType,_that.isRead,_that.readAt,_that.createdAt,_that.expiresAt,_that.pushDelivered,_that.pushDeliveredAt,_that.fcmMessageId);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String userId,  String type,  String title,  String body,  String? imageUrl,  String? actionType,  String? actionData,  String? actionUrl,  String? relatedId,  String? relatedType,  bool? isRead,  DateTime? readAt,  DateTime? createdAt,  DateTime? expiresAt,  bool? pushDelivered,  DateTime? pushDeliveredAt,  String? fcmMessageId)  $default,) {final _that = this;
switch (_that) {
case _NotificationModel():
return $default(_that.id,_that.userId,_that.type,_that.title,_that.body,_that.imageUrl,_that.actionType,_that.actionData,_that.actionUrl,_that.relatedId,_that.relatedType,_that.isRead,_that.readAt,_that.createdAt,_that.expiresAt,_that.pushDelivered,_that.pushDeliveredAt,_that.fcmMessageId);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String userId,  String type,  String title,  String body,  String? imageUrl,  String? actionType,  String? actionData,  String? actionUrl,  String? relatedId,  String? relatedType,  bool? isRead,  DateTime? readAt,  DateTime? createdAt,  DateTime? expiresAt,  bool? pushDelivered,  DateTime? pushDeliveredAt,  String? fcmMessageId)?  $default,) {final _that = this;
switch (_that) {
case _NotificationModel() when $default != null:
return $default(_that.id,_that.userId,_that.type,_that.title,_that.body,_that.imageUrl,_that.actionType,_that.actionData,_that.actionUrl,_that.relatedId,_that.relatedType,_that.isRead,_that.readAt,_that.createdAt,_that.expiresAt,_that.pushDelivered,_that.pushDeliveredAt,_that.fcmMessageId);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _NotificationModel implements NotificationModel {
  const _NotificationModel({this.id = '', this.userId = '', this.type = '', this.title = '', this.body = '', this.imageUrl, this.actionType, this.actionData, this.actionUrl, this.relatedId, this.relatedType, this.isRead, this.readAt, this.createdAt, this.expiresAt, this.pushDelivered, this.pushDeliveredAt, this.fcmMessageId});
  factory _NotificationModel.fromJson(Map<String, dynamic> json) => _$NotificationModelFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String userId;
@override@JsonKey() final  String type;
// booking, commission, payout, lead, general, promotional
@override@JsonKey() final  String title;
@override@JsonKey() final  String body;
@override final  String? imageUrl;
// Action
@override final  String? actionType;
// open_screen, open_url, show_dialog, none
@override final  String? actionData;
// screen_name, url, etc.
@override final  String? actionUrl;
// Related Entity
@override final  String? relatedId;
@override final  String? relatedType;
// Status
@override final  bool? isRead;
@override final  DateTime? readAt;
// Timestamps
@override final  DateTime? createdAt;
@override final  DateTime? expiresAt;
// Delivery
@override final  bool? pushDelivered;
@override final  DateTime? pushDeliveredAt;
@override final  String? fcmMessageId;

/// Create a copy of NotificationModel
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$NotificationModelCopyWith<_NotificationModel> get copyWith => __$NotificationModelCopyWithImpl<_NotificationModel>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$NotificationModelToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _NotificationModel&&(identical(other.id, id) || other.id == id)&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.type, type) || other.type == type)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body)&&(identical(other.imageUrl, imageUrl) || other.imageUrl == imageUrl)&&(identical(other.actionType, actionType) || other.actionType == actionType)&&(identical(other.actionData, actionData) || other.actionData == actionData)&&(identical(other.actionUrl, actionUrl) || other.actionUrl == actionUrl)&&(identical(other.relatedId, relatedId) || other.relatedId == relatedId)&&(identical(other.relatedType, relatedType) || other.relatedType == relatedType)&&(identical(other.isRead, isRead) || other.isRead == isRead)&&(identical(other.readAt, readAt) || other.readAt == readAt)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.expiresAt, expiresAt) || other.expiresAt == expiresAt)&&(identical(other.pushDelivered, pushDelivered) || other.pushDelivered == pushDelivered)&&(identical(other.pushDeliveredAt, pushDeliveredAt) || other.pushDeliveredAt == pushDeliveredAt)&&(identical(other.fcmMessageId, fcmMessageId) || other.fcmMessageId == fcmMessageId));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,userId,type,title,body,imageUrl,actionType,actionData,actionUrl,relatedId,relatedType,isRead,readAt,createdAt,expiresAt,pushDelivered,pushDeliveredAt,fcmMessageId);

@override
String toString() {
  return 'NotificationModel(id: $id, userId: $userId, type: $type, title: $title, body: $body, imageUrl: $imageUrl, actionType: $actionType, actionData: $actionData, actionUrl: $actionUrl, relatedId: $relatedId, relatedType: $relatedType, isRead: $isRead, readAt: $readAt, createdAt: $createdAt, expiresAt: $expiresAt, pushDelivered: $pushDelivered, pushDeliveredAt: $pushDeliveredAt, fcmMessageId: $fcmMessageId)';
}


}

/// @nodoc
abstract mixin class _$NotificationModelCopyWith<$Res> implements $NotificationModelCopyWith<$Res> {
  factory _$NotificationModelCopyWith(_NotificationModel value, $Res Function(_NotificationModel) _then) = __$NotificationModelCopyWithImpl;
@override @useResult
$Res call({
 String id, String userId, String type, String title, String body, String? imageUrl, String? actionType, String? actionData, String? actionUrl, String? relatedId, String? relatedType, bool? isRead, DateTime? readAt, DateTime? createdAt, DateTime? expiresAt, bool? pushDelivered, DateTime? pushDeliveredAt, String? fcmMessageId
});




}
/// @nodoc
class __$NotificationModelCopyWithImpl<$Res>
    implements _$NotificationModelCopyWith<$Res> {
  __$NotificationModelCopyWithImpl(this._self, this._then);

  final _NotificationModel _self;
  final $Res Function(_NotificationModel) _then;

/// Create a copy of NotificationModel
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? userId = null,Object? type = null,Object? title = null,Object? body = null,Object? imageUrl = freezed,Object? actionType = freezed,Object? actionData = freezed,Object? actionUrl = freezed,Object? relatedId = freezed,Object? relatedType = freezed,Object? isRead = freezed,Object? readAt = freezed,Object? createdAt = freezed,Object? expiresAt = freezed,Object? pushDelivered = freezed,Object? pushDeliveredAt = freezed,Object? fcmMessageId = freezed,}) {
  return _then(_NotificationModel(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,imageUrl: freezed == imageUrl ? _self.imageUrl : imageUrl // ignore: cast_nullable_to_non_nullable
as String?,actionType: freezed == actionType ? _self.actionType : actionType // ignore: cast_nullable_to_non_nullable
as String?,actionData: freezed == actionData ? _self.actionData : actionData // ignore: cast_nullable_to_non_nullable
as String?,actionUrl: freezed == actionUrl ? _self.actionUrl : actionUrl // ignore: cast_nullable_to_non_nullable
as String?,relatedId: freezed == relatedId ? _self.relatedId : relatedId // ignore: cast_nullable_to_non_nullable
as String?,relatedType: freezed == relatedType ? _self.relatedType : relatedType // ignore: cast_nullable_to_non_nullable
as String?,isRead: freezed == isRead ? _self.isRead : isRead // ignore: cast_nullable_to_non_nullable
as bool?,readAt: freezed == readAt ? _self.readAt : readAt // ignore: cast_nullable_to_non_nullable
as DateTime?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,expiresAt: freezed == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime?,pushDelivered: freezed == pushDelivered ? _self.pushDelivered : pushDelivered // ignore: cast_nullable_to_non_nullable
as bool?,pushDeliveredAt: freezed == pushDeliveredAt ? _self.pushDeliveredAt : pushDeliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,fcmMessageId: freezed == fcmMessageId ? _self.fcmMessageId : fcmMessageId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

// dart format on
