// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'notification_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
  'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models',
);

NotificationModel _$NotificationModelFromJson(Map<String, dynamic> json) {
  return _NotificationModel.fromJson(json);
}

/// @nodoc
mixin _$NotificationModel {
  String get id => throw _privateConstructorUsedError;
  String get userId => throw _privateConstructorUsedError;
  String get type =>
      throw _privateConstructorUsedError; // booking, commission, payout, lead, general, promotional
  String get title => throw _privateConstructorUsedError;
  String get body => throw _privateConstructorUsedError;
  String? get imageUrl => throw _privateConstructorUsedError; // Action
  String? get actionType =>
      throw _privateConstructorUsedError; // open_screen, open_url, show_dialog, none
  String? get actionData =>
      throw _privateConstructorUsedError; // screen_name, url, etc.
  String? get actionUrl => throw _privateConstructorUsedError; // Related Entity
  String? get relatedId => throw _privateConstructorUsedError;
  String? get relatedType => throw _privateConstructorUsedError; // Status
  bool? get isRead => throw _privateConstructorUsedError;
  DateTime? get readAt => throw _privateConstructorUsedError; // Timestamps
  DateTime? get createdAt => throw _privateConstructorUsedError;
  DateTime? get expiresAt => throw _privateConstructorUsedError; // Delivery
  bool? get pushDelivered => throw _privateConstructorUsedError;
  DateTime? get pushDeliveredAt => throw _privateConstructorUsedError;
  String? get fcmMessageId => throw _privateConstructorUsedError;

  /// Serializes this NotificationModel to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of NotificationModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $NotificationModelCopyWith<NotificationModel> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $NotificationModelCopyWith<$Res> {
  factory $NotificationModelCopyWith(
    NotificationModel value,
    $Res Function(NotificationModel) then,
  ) = _$NotificationModelCopyWithImpl<$Res, NotificationModel>;
  @useResult
  $Res call({
    String id,
    String userId,
    String type,
    String title,
    String body,
    String? imageUrl,
    String? actionType,
    String? actionData,
    String? actionUrl,
    String? relatedId,
    String? relatedType,
    bool? isRead,
    DateTime? readAt,
    DateTime? createdAt,
    DateTime? expiresAt,
    bool? pushDelivered,
    DateTime? pushDeliveredAt,
    String? fcmMessageId,
  });
}

/// @nodoc
class _$NotificationModelCopyWithImpl<$Res, $Val extends NotificationModel>
    implements $NotificationModelCopyWith<$Res> {
  _$NotificationModelCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of NotificationModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? userId = null,
    Object? type = null,
    Object? title = null,
    Object? body = null,
    Object? imageUrl = freezed,
    Object? actionType = freezed,
    Object? actionData = freezed,
    Object? actionUrl = freezed,
    Object? relatedId = freezed,
    Object? relatedType = freezed,
    Object? isRead = freezed,
    Object? readAt = freezed,
    Object? createdAt = freezed,
    Object? expiresAt = freezed,
    Object? pushDelivered = freezed,
    Object? pushDeliveredAt = freezed,
    Object? fcmMessageId = freezed,
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
            type: null == type
                ? _value.type
                : type // ignore: cast_nullable_to_non_nullable
                      as String,
            title: null == title
                ? _value.title
                : title // ignore: cast_nullable_to_non_nullable
                      as String,
            body: null == body
                ? _value.body
                : body // ignore: cast_nullable_to_non_nullable
                      as String,
            imageUrl: freezed == imageUrl
                ? _value.imageUrl
                : imageUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            actionType: freezed == actionType
                ? _value.actionType
                : actionType // ignore: cast_nullable_to_non_nullable
                      as String?,
            actionData: freezed == actionData
                ? _value.actionData
                : actionData // ignore: cast_nullable_to_non_nullable
                      as String?,
            actionUrl: freezed == actionUrl
                ? _value.actionUrl
                : actionUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            relatedId: freezed == relatedId
                ? _value.relatedId
                : relatedId // ignore: cast_nullable_to_non_nullable
                      as String?,
            relatedType: freezed == relatedType
                ? _value.relatedType
                : relatedType // ignore: cast_nullable_to_non_nullable
                      as String?,
            isRead: freezed == isRead
                ? _value.isRead
                : isRead // ignore: cast_nullable_to_non_nullable
                      as bool?,
            readAt: freezed == readAt
                ? _value.readAt
                : readAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            expiresAt: freezed == expiresAt
                ? _value.expiresAt
                : expiresAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            pushDelivered: freezed == pushDelivered
                ? _value.pushDelivered
                : pushDelivered // ignore: cast_nullable_to_non_nullable
                      as bool?,
            pushDeliveredAt: freezed == pushDeliveredAt
                ? _value.pushDeliveredAt
                : pushDeliveredAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            fcmMessageId: freezed == fcmMessageId
                ? _value.fcmMessageId
                : fcmMessageId // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$NotificationModelImplCopyWith<$Res>
    implements $NotificationModelCopyWith<$Res> {
  factory _$$NotificationModelImplCopyWith(
    _$NotificationModelImpl value,
    $Res Function(_$NotificationModelImpl) then,
  ) = __$$NotificationModelImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String userId,
    String type,
    String title,
    String body,
    String? imageUrl,
    String? actionType,
    String? actionData,
    String? actionUrl,
    String? relatedId,
    String? relatedType,
    bool? isRead,
    DateTime? readAt,
    DateTime? createdAt,
    DateTime? expiresAt,
    bool? pushDelivered,
    DateTime? pushDeliveredAt,
    String? fcmMessageId,
  });
}

/// @nodoc
class __$$NotificationModelImplCopyWithImpl<$Res>
    extends _$NotificationModelCopyWithImpl<$Res, _$NotificationModelImpl>
    implements _$$NotificationModelImplCopyWith<$Res> {
  __$$NotificationModelImplCopyWithImpl(
    _$NotificationModelImpl _value,
    $Res Function(_$NotificationModelImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of NotificationModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? userId = null,
    Object? type = null,
    Object? title = null,
    Object? body = null,
    Object? imageUrl = freezed,
    Object? actionType = freezed,
    Object? actionData = freezed,
    Object? actionUrl = freezed,
    Object? relatedId = freezed,
    Object? relatedType = freezed,
    Object? isRead = freezed,
    Object? readAt = freezed,
    Object? createdAt = freezed,
    Object? expiresAt = freezed,
    Object? pushDelivered = freezed,
    Object? pushDeliveredAt = freezed,
    Object? fcmMessageId = freezed,
  }) {
    return _then(
      _$NotificationModelImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        userId: null == userId
            ? _value.userId
            : userId // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _value.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        title: null == title
            ? _value.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String,
        body: null == body
            ? _value.body
            : body // ignore: cast_nullable_to_non_nullable
                  as String,
        imageUrl: freezed == imageUrl
            ? _value.imageUrl
            : imageUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        actionType: freezed == actionType
            ? _value.actionType
            : actionType // ignore: cast_nullable_to_non_nullable
                  as String?,
        actionData: freezed == actionData
            ? _value.actionData
            : actionData // ignore: cast_nullable_to_non_nullable
                  as String?,
        actionUrl: freezed == actionUrl
            ? _value.actionUrl
            : actionUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        relatedId: freezed == relatedId
            ? _value.relatedId
            : relatedId // ignore: cast_nullable_to_non_nullable
                  as String?,
        relatedType: freezed == relatedType
            ? _value.relatedType
            : relatedType // ignore: cast_nullable_to_non_nullable
                  as String?,
        isRead: freezed == isRead
            ? _value.isRead
            : isRead // ignore: cast_nullable_to_non_nullable
                  as bool?,
        readAt: freezed == readAt
            ? _value.readAt
            : readAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        expiresAt: freezed == expiresAt
            ? _value.expiresAt
            : expiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        pushDelivered: freezed == pushDelivered
            ? _value.pushDelivered
            : pushDelivered // ignore: cast_nullable_to_non_nullable
                  as bool?,
        pushDeliveredAt: freezed == pushDeliveredAt
            ? _value.pushDeliveredAt
            : pushDeliveredAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        fcmMessageId: freezed == fcmMessageId
            ? _value.fcmMessageId
            : fcmMessageId // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$NotificationModelImpl implements _NotificationModel {
  const _$NotificationModelImpl({
    this.id = '',
    this.userId = '',
    this.type = '',
    this.title = '',
    this.body = '',
    this.imageUrl,
    this.actionType,
    this.actionData,
    this.actionUrl,
    this.relatedId,
    this.relatedType,
    this.isRead,
    this.readAt,
    this.createdAt,
    this.expiresAt,
    this.pushDelivered,
    this.pushDeliveredAt,
    this.fcmMessageId,
  });

  factory _$NotificationModelImpl.fromJson(Map<String, dynamic> json) =>
      _$$NotificationModelImplFromJson(json);

  @override
  @JsonKey()
  final String id;
  @override
  @JsonKey()
  final String userId;
  @override
  @JsonKey()
  final String type;
  // booking, commission, payout, lead, general, promotional
  @override
  @JsonKey()
  final String title;
  @override
  @JsonKey()
  final String body;
  @override
  final String? imageUrl;
  // Action
  @override
  final String? actionType;
  // open_screen, open_url, show_dialog, none
  @override
  final String? actionData;
  // screen_name, url, etc.
  @override
  final String? actionUrl;
  // Related Entity
  @override
  final String? relatedId;
  @override
  final String? relatedType;
  // Status
  @override
  final bool? isRead;
  @override
  final DateTime? readAt;
  // Timestamps
  @override
  final DateTime? createdAt;
  @override
  final DateTime? expiresAt;
  // Delivery
  @override
  final bool? pushDelivered;
  @override
  final DateTime? pushDeliveredAt;
  @override
  final String? fcmMessageId;

  @override
  String toString() {
    return 'NotificationModel(id: $id, userId: $userId, type: $type, title: $title, body: $body, imageUrl: $imageUrl, actionType: $actionType, actionData: $actionData, actionUrl: $actionUrl, relatedId: $relatedId, relatedType: $relatedType, isRead: $isRead, readAt: $readAt, createdAt: $createdAt, expiresAt: $expiresAt, pushDelivered: $pushDelivered, pushDeliveredAt: $pushDeliveredAt, fcmMessageId: $fcmMessageId)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$NotificationModelImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.userId, userId) || other.userId == userId) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.title, title) || other.title == title) &&
            (identical(other.body, body) || other.body == body) &&
            (identical(other.imageUrl, imageUrl) ||
                other.imageUrl == imageUrl) &&
            (identical(other.actionType, actionType) ||
                other.actionType == actionType) &&
            (identical(other.actionData, actionData) ||
                other.actionData == actionData) &&
            (identical(other.actionUrl, actionUrl) ||
                other.actionUrl == actionUrl) &&
            (identical(other.relatedId, relatedId) ||
                other.relatedId == relatedId) &&
            (identical(other.relatedType, relatedType) ||
                other.relatedType == relatedType) &&
            (identical(other.isRead, isRead) || other.isRead == isRead) &&
            (identical(other.readAt, readAt) || other.readAt == readAt) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.expiresAt, expiresAt) ||
                other.expiresAt == expiresAt) &&
            (identical(other.pushDelivered, pushDelivered) ||
                other.pushDelivered == pushDelivered) &&
            (identical(other.pushDeliveredAt, pushDeliveredAt) ||
                other.pushDeliveredAt == pushDeliveredAt) &&
            (identical(other.fcmMessageId, fcmMessageId) ||
                other.fcmMessageId == fcmMessageId));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    userId,
    type,
    title,
    body,
    imageUrl,
    actionType,
    actionData,
    actionUrl,
    relatedId,
    relatedType,
    isRead,
    readAt,
    createdAt,
    expiresAt,
    pushDelivered,
    pushDeliveredAt,
    fcmMessageId,
  );

  /// Create a copy of NotificationModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$NotificationModelImplCopyWith<_$NotificationModelImpl> get copyWith =>
      __$$NotificationModelImplCopyWithImpl<_$NotificationModelImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$NotificationModelImplToJson(this);
  }
}

abstract class _NotificationModel implements NotificationModel {
  const factory _NotificationModel({
    final String id,
    final String userId,
    final String type,
    final String title,
    final String body,
    final String? imageUrl,
    final String? actionType,
    final String? actionData,
    final String? actionUrl,
    final String? relatedId,
    final String? relatedType,
    final bool? isRead,
    final DateTime? readAt,
    final DateTime? createdAt,
    final DateTime? expiresAt,
    final bool? pushDelivered,
    final DateTime? pushDeliveredAt,
    final String? fcmMessageId,
  }) = _$NotificationModelImpl;

  factory _NotificationModel.fromJson(Map<String, dynamic> json) =
      _$NotificationModelImpl.fromJson;

  @override
  String get id;
  @override
  String get userId;
  @override
  String get type; // booking, commission, payout, lead, general, promotional
  @override
  String get title;
  @override
  String get body;
  @override
  String? get imageUrl; // Action
  @override
  String? get actionType; // open_screen, open_url, show_dialog, none
  @override
  String? get actionData; // screen_name, url, etc.
  @override
  String? get actionUrl; // Related Entity
  @override
  String? get relatedId;
  @override
  String? get relatedType; // Status
  @override
  bool? get isRead;
  @override
  DateTime? get readAt; // Timestamps
  @override
  DateTime? get createdAt;
  @override
  DateTime? get expiresAt; // Delivery
  @override
  bool? get pushDelivered;
  @override
  DateTime? get pushDeliveredAt;
  @override
  String? get fcmMessageId;

  /// Create a copy of NotificationModel
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$NotificationModelImplCopyWith<_$NotificationModelImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
