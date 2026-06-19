// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'site_visit_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
  'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models',
);

SiteVisitModel _$SiteVisitModelFromJson(Map<String, dynamic> json) {
  return _SiteVisitModel.fromJson(json);
}

/// @nodoc
mixin _$SiteVisitModel {
  String get id => throw _privateConstructorUsedError;
  String get agentId => throw _privateConstructorUsedError;
  String get agentName => throw _privateConstructorUsedError; // Customer Info
  String? get customerId => throw _privateConstructorUsedError;
  String? get customerName => throw _privateConstructorUsedError;
  String? get customerPhone => throw _privateConstructorUsedError; // Location
  String get colonyId => throw _privateConstructorUsedError;
  String get colonyName => throw _privateConstructorUsedError;
  List<String>? get plotIdsShown => throw _privateConstructorUsedError;
  List<String>? get plotNumbersShown =>
      throw _privateConstructorUsedError; // GPS Coordinates
  double get latitude => throw _privateConstructorUsedError;
  double get longitude => throw _privateConstructorUsedError;
  String? get address => throw _privateConstructorUsedError;
  double? get accuracy => throw _privateConstructorUsedError; // Visit Details
  DateTime get visitStartTime => throw _privateConstructorUsedError;
  DateTime? get visitEndTime => throw _privateConstructorUsedError;
  Duration? get duration => throw _privateConstructorUsedError;
  String? get purpose =>
      throw _privateConstructorUsedError; // initial_visit, follow_up, document_collection, etc.
  // Feedback
  String? get customerFeedback => throw _privateConstructorUsedError;
  String? get agentNotes => throw _privateConstructorUsedError;
  String? get outcome =>
      throw _privateConstructorUsedError; // interested, not_interested, thinking, booking_done
  // Follow-up
  bool? get followUpRequired => throw _privateConstructorUsedError;
  DateTime? get followUpDate => throw _privateConstructorUsedError;
  String? get followUpType => throw _privateConstructorUsedError; // Media
  List<String>? get photos => throw _privateConstructorUsedError;
  List<String>? get videos => throw _privateConstructorUsedError;
  String? get voiceNoteUrl =>
      throw _privateConstructorUsedError; // Offline Sync
  bool? get isOfflineCreated => throw _privateConstructorUsedError;
  DateTime? get syncedAt => throw _privateConstructorUsedError; // Timestamps
  DateTime? get createdAt => throw _privateConstructorUsedError;
  DateTime? get updatedAt => throw _privateConstructorUsedError;

  /// Serializes this SiteVisitModel to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of SiteVisitModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $SiteVisitModelCopyWith<SiteVisitModel> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $SiteVisitModelCopyWith<$Res> {
  factory $SiteVisitModelCopyWith(
    SiteVisitModel value,
    $Res Function(SiteVisitModel) then,
  ) = _$SiteVisitModelCopyWithImpl<$Res, SiteVisitModel>;
  @useResult
  $Res call({
    String id,
    String agentId,
    String agentName,
    String? customerId,
    String? customerName,
    String? customerPhone,
    String colonyId,
    String colonyName,
    List<String>? plotIdsShown,
    List<String>? plotNumbersShown,
    double latitude,
    double longitude,
    String? address,
    double? accuracy,
    DateTime visitStartTime,
    DateTime? visitEndTime,
    Duration? duration,
    String? purpose,
    String? customerFeedback,
    String? agentNotes,
    String? outcome,
    bool? followUpRequired,
    DateTime? followUpDate,
    String? followUpType,
    List<String>? photos,
    List<String>? videos,
    String? voiceNoteUrl,
    bool? isOfflineCreated,
    DateTime? syncedAt,
    DateTime? createdAt,
    DateTime? updatedAt,
  });
}

/// @nodoc
class _$SiteVisitModelCopyWithImpl<$Res, $Val extends SiteVisitModel>
    implements $SiteVisitModelCopyWith<$Res> {
  _$SiteVisitModelCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of SiteVisitModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? agentId = null,
    Object? agentName = null,
    Object? customerId = freezed,
    Object? customerName = freezed,
    Object? customerPhone = freezed,
    Object? colonyId = null,
    Object? colonyName = null,
    Object? plotIdsShown = freezed,
    Object? plotNumbersShown = freezed,
    Object? latitude = null,
    Object? longitude = null,
    Object? address = freezed,
    Object? accuracy = freezed,
    Object? visitStartTime = null,
    Object? visitEndTime = freezed,
    Object? duration = freezed,
    Object? purpose = freezed,
    Object? customerFeedback = freezed,
    Object? agentNotes = freezed,
    Object? outcome = freezed,
    Object? followUpRequired = freezed,
    Object? followUpDate = freezed,
    Object? followUpType = freezed,
    Object? photos = freezed,
    Object? videos = freezed,
    Object? voiceNoteUrl = freezed,
    Object? isOfflineCreated = freezed,
    Object? syncedAt = freezed,
    Object? createdAt = freezed,
    Object? updatedAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            agentId: null == agentId
                ? _value.agentId
                : agentId // ignore: cast_nullable_to_non_nullable
                      as String,
            agentName: null == agentName
                ? _value.agentName
                : agentName // ignore: cast_nullable_to_non_nullable
                      as String,
            customerId: freezed == customerId
                ? _value.customerId
                : customerId // ignore: cast_nullable_to_non_nullable
                      as String?,
            customerName: freezed == customerName
                ? _value.customerName
                : customerName // ignore: cast_nullable_to_non_nullable
                      as String?,
            customerPhone: freezed == customerPhone
                ? _value.customerPhone
                : customerPhone // ignore: cast_nullable_to_non_nullable
                      as String?,
            colonyId: null == colonyId
                ? _value.colonyId
                : colonyId // ignore: cast_nullable_to_non_nullable
                      as String,
            colonyName: null == colonyName
                ? _value.colonyName
                : colonyName // ignore: cast_nullable_to_non_nullable
                      as String,
            plotIdsShown: freezed == plotIdsShown
                ? _value.plotIdsShown
                : plotIdsShown // ignore: cast_nullable_to_non_nullable
                      as List<String>?,
            plotNumbersShown: freezed == plotNumbersShown
                ? _value.plotNumbersShown
                : plotNumbersShown // ignore: cast_nullable_to_non_nullable
                      as List<String>?,
            latitude: null == latitude
                ? _value.latitude
                : latitude // ignore: cast_nullable_to_non_nullable
                      as double,
            longitude: null == longitude
                ? _value.longitude
                : longitude // ignore: cast_nullable_to_non_nullable
                      as double,
            address: freezed == address
                ? _value.address
                : address // ignore: cast_nullable_to_non_nullable
                      as String?,
            accuracy: freezed == accuracy
                ? _value.accuracy
                : accuracy // ignore: cast_nullable_to_non_nullable
                      as double?,
            visitStartTime: null == visitStartTime
                ? _value.visitStartTime
                : visitStartTime // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            visitEndTime: freezed == visitEndTime
                ? _value.visitEndTime
                : visitEndTime // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            duration: freezed == duration
                ? _value.duration
                : duration // ignore: cast_nullable_to_non_nullable
                      as Duration?,
            purpose: freezed == purpose
                ? _value.purpose
                : purpose // ignore: cast_nullable_to_non_nullable
                      as String?,
            customerFeedback: freezed == customerFeedback
                ? _value.customerFeedback
                : customerFeedback // ignore: cast_nullable_to_non_nullable
                      as String?,
            agentNotes: freezed == agentNotes
                ? _value.agentNotes
                : agentNotes // ignore: cast_nullable_to_non_nullable
                      as String?,
            outcome: freezed == outcome
                ? _value.outcome
                : outcome // ignore: cast_nullable_to_non_nullable
                      as String?,
            followUpRequired: freezed == followUpRequired
                ? _value.followUpRequired
                : followUpRequired // ignore: cast_nullable_to_non_nullable
                      as bool?,
            followUpDate: freezed == followUpDate
                ? _value.followUpDate
                : followUpDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            followUpType: freezed == followUpType
                ? _value.followUpType
                : followUpType // ignore: cast_nullable_to_non_nullable
                      as String?,
            photos: freezed == photos
                ? _value.photos
                : photos // ignore: cast_nullable_to_non_nullable
                      as List<String>?,
            videos: freezed == videos
                ? _value.videos
                : videos // ignore: cast_nullable_to_non_nullable
                      as List<String>?,
            voiceNoteUrl: freezed == voiceNoteUrl
                ? _value.voiceNoteUrl
                : voiceNoteUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            isOfflineCreated: freezed == isOfflineCreated
                ? _value.isOfflineCreated
                : isOfflineCreated // ignore: cast_nullable_to_non_nullable
                      as bool?,
            syncedAt: freezed == syncedAt
                ? _value.syncedAt
                : syncedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
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
abstract class _$$SiteVisitModelImplCopyWith<$Res>
    implements $SiteVisitModelCopyWith<$Res> {
  factory _$$SiteVisitModelImplCopyWith(
    _$SiteVisitModelImpl value,
    $Res Function(_$SiteVisitModelImpl) then,
  ) = __$$SiteVisitModelImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String agentId,
    String agentName,
    String? customerId,
    String? customerName,
    String? customerPhone,
    String colonyId,
    String colonyName,
    List<String>? plotIdsShown,
    List<String>? plotNumbersShown,
    double latitude,
    double longitude,
    String? address,
    double? accuracy,
    DateTime visitStartTime,
    DateTime? visitEndTime,
    Duration? duration,
    String? purpose,
    String? customerFeedback,
    String? agentNotes,
    String? outcome,
    bool? followUpRequired,
    DateTime? followUpDate,
    String? followUpType,
    List<String>? photos,
    List<String>? videos,
    String? voiceNoteUrl,
    bool? isOfflineCreated,
    DateTime? syncedAt,
    DateTime? createdAt,
    DateTime? updatedAt,
  });
}

/// @nodoc
class __$$SiteVisitModelImplCopyWithImpl<$Res>
    extends _$SiteVisitModelCopyWithImpl<$Res, _$SiteVisitModelImpl>
    implements _$$SiteVisitModelImplCopyWith<$Res> {
  __$$SiteVisitModelImplCopyWithImpl(
    _$SiteVisitModelImpl _value,
    $Res Function(_$SiteVisitModelImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of SiteVisitModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? agentId = null,
    Object? agentName = null,
    Object? customerId = freezed,
    Object? customerName = freezed,
    Object? customerPhone = freezed,
    Object? colonyId = null,
    Object? colonyName = null,
    Object? plotIdsShown = freezed,
    Object? plotNumbersShown = freezed,
    Object? latitude = null,
    Object? longitude = null,
    Object? address = freezed,
    Object? accuracy = freezed,
    Object? visitStartTime = null,
    Object? visitEndTime = freezed,
    Object? duration = freezed,
    Object? purpose = freezed,
    Object? customerFeedback = freezed,
    Object? agentNotes = freezed,
    Object? outcome = freezed,
    Object? followUpRequired = freezed,
    Object? followUpDate = freezed,
    Object? followUpType = freezed,
    Object? photos = freezed,
    Object? videos = freezed,
    Object? voiceNoteUrl = freezed,
    Object? isOfflineCreated = freezed,
    Object? syncedAt = freezed,
    Object? createdAt = freezed,
    Object? updatedAt = freezed,
  }) {
    return _then(
      _$SiteVisitModelImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        agentId: null == agentId
            ? _value.agentId
            : agentId // ignore: cast_nullable_to_non_nullable
                  as String,
        agentName: null == agentName
            ? _value.agentName
            : agentName // ignore: cast_nullable_to_non_nullable
                  as String,
        customerId: freezed == customerId
            ? _value.customerId
            : customerId // ignore: cast_nullable_to_non_nullable
                  as String?,
        customerName: freezed == customerName
            ? _value.customerName
            : customerName // ignore: cast_nullable_to_non_nullable
                  as String?,
        customerPhone: freezed == customerPhone
            ? _value.customerPhone
            : customerPhone // ignore: cast_nullable_to_non_nullable
                  as String?,
        colonyId: null == colonyId
            ? _value.colonyId
            : colonyId // ignore: cast_nullable_to_non_nullable
                  as String,
        colonyName: null == colonyName
            ? _value.colonyName
            : colonyName // ignore: cast_nullable_to_non_nullable
                  as String,
        plotIdsShown: freezed == plotIdsShown
            ? _value._plotIdsShown
            : plotIdsShown // ignore: cast_nullable_to_non_nullable
                  as List<String>?,
        plotNumbersShown: freezed == plotNumbersShown
            ? _value._plotNumbersShown
            : plotNumbersShown // ignore: cast_nullable_to_non_nullable
                  as List<String>?,
        latitude: null == latitude
            ? _value.latitude
            : latitude // ignore: cast_nullable_to_non_nullable
                  as double,
        longitude: null == longitude
            ? _value.longitude
            : longitude // ignore: cast_nullable_to_non_nullable
                  as double,
        address: freezed == address
            ? _value.address
            : address // ignore: cast_nullable_to_non_nullable
                  as String?,
        accuracy: freezed == accuracy
            ? _value.accuracy
            : accuracy // ignore: cast_nullable_to_non_nullable
                  as double?,
        visitStartTime: null == visitStartTime
            ? _value.visitStartTime
            : visitStartTime // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        visitEndTime: freezed == visitEndTime
            ? _value.visitEndTime
            : visitEndTime // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        duration: freezed == duration
            ? _value.duration
            : duration // ignore: cast_nullable_to_non_nullable
                  as Duration?,
        purpose: freezed == purpose
            ? _value.purpose
            : purpose // ignore: cast_nullable_to_non_nullable
                  as String?,
        customerFeedback: freezed == customerFeedback
            ? _value.customerFeedback
            : customerFeedback // ignore: cast_nullable_to_non_nullable
                  as String?,
        agentNotes: freezed == agentNotes
            ? _value.agentNotes
            : agentNotes // ignore: cast_nullable_to_non_nullable
                  as String?,
        outcome: freezed == outcome
            ? _value.outcome
            : outcome // ignore: cast_nullable_to_non_nullable
                  as String?,
        followUpRequired: freezed == followUpRequired
            ? _value.followUpRequired
            : followUpRequired // ignore: cast_nullable_to_non_nullable
                  as bool?,
        followUpDate: freezed == followUpDate
            ? _value.followUpDate
            : followUpDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        followUpType: freezed == followUpType
            ? _value.followUpType
            : followUpType // ignore: cast_nullable_to_non_nullable
                  as String?,
        photos: freezed == photos
            ? _value._photos
            : photos // ignore: cast_nullable_to_non_nullable
                  as List<String>?,
        videos: freezed == videos
            ? _value._videos
            : videos // ignore: cast_nullable_to_non_nullable
                  as List<String>?,
        voiceNoteUrl: freezed == voiceNoteUrl
            ? _value.voiceNoteUrl
            : voiceNoteUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        isOfflineCreated: freezed == isOfflineCreated
            ? _value.isOfflineCreated
            : isOfflineCreated // ignore: cast_nullable_to_non_nullable
                  as bool?,
        syncedAt: freezed == syncedAt
            ? _value.syncedAt
            : syncedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
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
class _$SiteVisitModelImpl implements _SiteVisitModel {
  const _$SiteVisitModelImpl({
    this.id = '',
    this.agentId = '',
    this.agentName = '',
    this.customerId,
    this.customerName,
    this.customerPhone,
    this.colonyId = '',
    this.colonyName = '',
    final List<String>? plotIdsShown,
    final List<String>? plotNumbersShown,
    this.latitude = 0.0,
    this.longitude = 0.0,
    this.address,
    this.accuracy,
    required this.visitStartTime,
    this.visitEndTime,
    this.duration,
    this.purpose,
    this.customerFeedback,
    this.agentNotes,
    this.outcome,
    this.followUpRequired,
    this.followUpDate,
    this.followUpType,
    final List<String>? photos,
    final List<String>? videos,
    this.voiceNoteUrl,
    this.isOfflineCreated,
    this.syncedAt,
    this.createdAt,
    this.updatedAt,
  }) : _plotIdsShown = plotIdsShown,
       _plotNumbersShown = plotNumbersShown,
       _photos = photos,
       _videos = videos;

  factory _$SiteVisitModelImpl.fromJson(Map<String, dynamic> json) =>
      _$$SiteVisitModelImplFromJson(json);

  @override
  @JsonKey()
  final String id;
  @override
  @JsonKey()
  final String agentId;
  @override
  @JsonKey()
  final String agentName;
  // Customer Info
  @override
  final String? customerId;
  @override
  final String? customerName;
  @override
  final String? customerPhone;
  // Location
  @override
  @JsonKey()
  final String colonyId;
  @override
  @JsonKey()
  final String colonyName;
  final List<String>? _plotIdsShown;
  @override
  List<String>? get plotIdsShown {
    final value = _plotIdsShown;
    if (value == null) return null;
    if (_plotIdsShown is EqualUnmodifiableListView) return _plotIdsShown;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  final List<String>? _plotNumbersShown;
  @override
  List<String>? get plotNumbersShown {
    final value = _plotNumbersShown;
    if (value == null) return null;
    if (_plotNumbersShown is EqualUnmodifiableListView)
      return _plotNumbersShown;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  // GPS Coordinates
  @override
  @JsonKey()
  final double latitude;
  @override
  @JsonKey()
  final double longitude;
  @override
  final String? address;
  @override
  final double? accuracy;
  // Visit Details
  @override
  final DateTime visitStartTime;
  @override
  final DateTime? visitEndTime;
  @override
  final Duration? duration;
  @override
  final String? purpose;
  // initial_visit, follow_up, document_collection, etc.
  // Feedback
  @override
  final String? customerFeedback;
  @override
  final String? agentNotes;
  @override
  final String? outcome;
  // interested, not_interested, thinking, booking_done
  // Follow-up
  @override
  final bool? followUpRequired;
  @override
  final DateTime? followUpDate;
  @override
  final String? followUpType;
  // Media
  final List<String>? _photos;
  // Media
  @override
  List<String>? get photos {
    final value = _photos;
    if (value == null) return null;
    if (_photos is EqualUnmodifiableListView) return _photos;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  final List<String>? _videos;
  @override
  List<String>? get videos {
    final value = _videos;
    if (value == null) return null;
    if (_videos is EqualUnmodifiableListView) return _videos;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  @override
  final String? voiceNoteUrl;
  // Offline Sync
  @override
  final bool? isOfflineCreated;
  @override
  final DateTime? syncedAt;
  // Timestamps
  @override
  final DateTime? createdAt;
  @override
  final DateTime? updatedAt;

  @override
  String toString() {
    return 'SiteVisitModel(id: $id, agentId: $agentId, agentName: $agentName, customerId: $customerId, customerName: $customerName, customerPhone: $customerPhone, colonyId: $colonyId, colonyName: $colonyName, plotIdsShown: $plotIdsShown, plotNumbersShown: $plotNumbersShown, latitude: $latitude, longitude: $longitude, address: $address, accuracy: $accuracy, visitStartTime: $visitStartTime, visitEndTime: $visitEndTime, duration: $duration, purpose: $purpose, customerFeedback: $customerFeedback, agentNotes: $agentNotes, outcome: $outcome, followUpRequired: $followUpRequired, followUpDate: $followUpDate, followUpType: $followUpType, photos: $photos, videos: $videos, voiceNoteUrl: $voiceNoteUrl, isOfflineCreated: $isOfflineCreated, syncedAt: $syncedAt, createdAt: $createdAt, updatedAt: $updatedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$SiteVisitModelImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.agentId, agentId) || other.agentId == agentId) &&
            (identical(other.agentName, agentName) ||
                other.agentName == agentName) &&
            (identical(other.customerId, customerId) ||
                other.customerId == customerId) &&
            (identical(other.customerName, customerName) ||
                other.customerName == customerName) &&
            (identical(other.customerPhone, customerPhone) ||
                other.customerPhone == customerPhone) &&
            (identical(other.colonyId, colonyId) ||
                other.colonyId == colonyId) &&
            (identical(other.colonyName, colonyName) ||
                other.colonyName == colonyName) &&
            const DeepCollectionEquality().equals(
              other._plotIdsShown,
              _plotIdsShown,
            ) &&
            const DeepCollectionEquality().equals(
              other._plotNumbersShown,
              _plotNumbersShown,
            ) &&
            (identical(other.latitude, latitude) ||
                other.latitude == latitude) &&
            (identical(other.longitude, longitude) ||
                other.longitude == longitude) &&
            (identical(other.address, address) || other.address == address) &&
            (identical(other.accuracy, accuracy) ||
                other.accuracy == accuracy) &&
            (identical(other.visitStartTime, visitStartTime) ||
                other.visitStartTime == visitStartTime) &&
            (identical(other.visitEndTime, visitEndTime) ||
                other.visitEndTime == visitEndTime) &&
            (identical(other.duration, duration) ||
                other.duration == duration) &&
            (identical(other.purpose, purpose) || other.purpose == purpose) &&
            (identical(other.customerFeedback, customerFeedback) ||
                other.customerFeedback == customerFeedback) &&
            (identical(other.agentNotes, agentNotes) ||
                other.agentNotes == agentNotes) &&
            (identical(other.outcome, outcome) || other.outcome == outcome) &&
            (identical(other.followUpRequired, followUpRequired) ||
                other.followUpRequired == followUpRequired) &&
            (identical(other.followUpDate, followUpDate) ||
                other.followUpDate == followUpDate) &&
            (identical(other.followUpType, followUpType) ||
                other.followUpType == followUpType) &&
            const DeepCollectionEquality().equals(other._photos, _photos) &&
            const DeepCollectionEquality().equals(other._videos, _videos) &&
            (identical(other.voiceNoteUrl, voiceNoteUrl) ||
                other.voiceNoteUrl == voiceNoteUrl) &&
            (identical(other.isOfflineCreated, isOfflineCreated) ||
                other.isOfflineCreated == isOfflineCreated) &&
            (identical(other.syncedAt, syncedAt) ||
                other.syncedAt == syncedAt) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    id,
    agentId,
    agentName,
    customerId,
    customerName,
    customerPhone,
    colonyId,
    colonyName,
    const DeepCollectionEquality().hash(_plotIdsShown),
    const DeepCollectionEquality().hash(_plotNumbersShown),
    latitude,
    longitude,
    address,
    accuracy,
    visitStartTime,
    visitEndTime,
    duration,
    purpose,
    customerFeedback,
    agentNotes,
    outcome,
    followUpRequired,
    followUpDate,
    followUpType,
    const DeepCollectionEquality().hash(_photos),
    const DeepCollectionEquality().hash(_videos),
    voiceNoteUrl,
    isOfflineCreated,
    syncedAt,
    createdAt,
    updatedAt,
  ]);

  /// Create a copy of SiteVisitModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$SiteVisitModelImplCopyWith<_$SiteVisitModelImpl> get copyWith =>
      __$$SiteVisitModelImplCopyWithImpl<_$SiteVisitModelImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$SiteVisitModelImplToJson(this);
  }
}

abstract class _SiteVisitModel implements SiteVisitModel {
  const factory _SiteVisitModel({
    final String id,
    final String agentId,
    final String agentName,
    final String? customerId,
    final String? customerName,
    final String? customerPhone,
    final String colonyId,
    final String colonyName,
    final List<String>? plotIdsShown,
    final List<String>? plotNumbersShown,
    final double latitude,
    final double longitude,
    final String? address,
    final double? accuracy,
    required final DateTime visitStartTime,
    final DateTime? visitEndTime,
    final Duration? duration,
    final String? purpose,
    final String? customerFeedback,
    final String? agentNotes,
    final String? outcome,
    final bool? followUpRequired,
    final DateTime? followUpDate,
    final String? followUpType,
    final List<String>? photos,
    final List<String>? videos,
    final String? voiceNoteUrl,
    final bool? isOfflineCreated,
    final DateTime? syncedAt,
    final DateTime? createdAt,
    final DateTime? updatedAt,
  }) = _$SiteVisitModelImpl;

  factory _SiteVisitModel.fromJson(Map<String, dynamic> json) =
      _$SiteVisitModelImpl.fromJson;

  @override
  String get id;
  @override
  String get agentId;
  @override
  String get agentName; // Customer Info
  @override
  String? get customerId;
  @override
  String? get customerName;
  @override
  String? get customerPhone; // Location
  @override
  String get colonyId;
  @override
  String get colonyName;
  @override
  List<String>? get plotIdsShown;
  @override
  List<String>? get plotNumbersShown; // GPS Coordinates
  @override
  double get latitude;
  @override
  double get longitude;
  @override
  String? get address;
  @override
  double? get accuracy; // Visit Details
  @override
  DateTime get visitStartTime;
  @override
  DateTime? get visitEndTime;
  @override
  Duration? get duration;
  @override
  String? get purpose; // initial_visit, follow_up, document_collection, etc.
  // Feedback
  @override
  String? get customerFeedback;
  @override
  String? get agentNotes;
  @override
  String? get outcome; // interested, not_interested, thinking, booking_done
  // Follow-up
  @override
  bool? get followUpRequired;
  @override
  DateTime? get followUpDate;
  @override
  String? get followUpType; // Media
  @override
  List<String>? get photos;
  @override
  List<String>? get videos;
  @override
  String? get voiceNoteUrl; // Offline Sync
  @override
  bool? get isOfflineCreated;
  @override
  DateTime? get syncedAt; // Timestamps
  @override
  DateTime? get createdAt;
  @override
  DateTime? get updatedAt;

  /// Create a copy of SiteVisitModel
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$SiteVisitModelImplCopyWith<_$SiteVisitModelImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

LiveLocationModel _$LiveLocationModelFromJson(Map<String, dynamic> json) {
  return _LiveLocationModel.fromJson(json);
}

/// @nodoc
mixin _$LiveLocationModel {
  String get userId => throw _privateConstructorUsedError;
  String get userName => throw _privateConstructorUsedError;
  String get userType => throw _privateConstructorUsedError; // agent, customer
  double get latitude => throw _privateConstructorUsedError;
  double get longitude => throw _privateConstructorUsedError;
  DateTime get timestamp => throw _privateConstructorUsedError;
  double? get speed => throw _privateConstructorUsedError;
  double? get heading => throw _privateConstructorUsedError;
  double? get accuracy => throw _privateConstructorUsedError;
  bool? get isSharingEnabled => throw _privateConstructorUsedError;
  DateTime? get sharingStartedAt => throw _privateConstructorUsedError;
  DateTime? get sharingExpiresAt => throw _privateConstructorUsedError;
  String? get sharedWith => throw _privateConstructorUsedError;

  /// Serializes this LiveLocationModel to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of LiveLocationModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $LiveLocationModelCopyWith<LiveLocationModel> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $LiveLocationModelCopyWith<$Res> {
  factory $LiveLocationModelCopyWith(
    LiveLocationModel value,
    $Res Function(LiveLocationModel) then,
  ) = _$LiveLocationModelCopyWithImpl<$Res, LiveLocationModel>;
  @useResult
  $Res call({
    String userId,
    String userName,
    String userType,
    double latitude,
    double longitude,
    DateTime timestamp,
    double? speed,
    double? heading,
    double? accuracy,
    bool? isSharingEnabled,
    DateTime? sharingStartedAt,
    DateTime? sharingExpiresAt,
    String? sharedWith,
  });
}

/// @nodoc
class _$LiveLocationModelCopyWithImpl<$Res, $Val extends LiveLocationModel>
    implements $LiveLocationModelCopyWith<$Res> {
  _$LiveLocationModelCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of LiveLocationModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? userId = null,
    Object? userName = null,
    Object? userType = null,
    Object? latitude = null,
    Object? longitude = null,
    Object? timestamp = null,
    Object? speed = freezed,
    Object? heading = freezed,
    Object? accuracy = freezed,
    Object? isSharingEnabled = freezed,
    Object? sharingStartedAt = freezed,
    Object? sharingExpiresAt = freezed,
    Object? sharedWith = freezed,
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
            userType: null == userType
                ? _value.userType
                : userType // ignore: cast_nullable_to_non_nullable
                      as String,
            latitude: null == latitude
                ? _value.latitude
                : latitude // ignore: cast_nullable_to_non_nullable
                      as double,
            longitude: null == longitude
                ? _value.longitude
                : longitude // ignore: cast_nullable_to_non_nullable
                      as double,
            timestamp: null == timestamp
                ? _value.timestamp
                : timestamp // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            speed: freezed == speed
                ? _value.speed
                : speed // ignore: cast_nullable_to_non_nullable
                      as double?,
            heading: freezed == heading
                ? _value.heading
                : heading // ignore: cast_nullable_to_non_nullable
                      as double?,
            accuracy: freezed == accuracy
                ? _value.accuracy
                : accuracy // ignore: cast_nullable_to_non_nullable
                      as double?,
            isSharingEnabled: freezed == isSharingEnabled
                ? _value.isSharingEnabled
                : isSharingEnabled // ignore: cast_nullable_to_non_nullable
                      as bool?,
            sharingStartedAt: freezed == sharingStartedAt
                ? _value.sharingStartedAt
                : sharingStartedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            sharingExpiresAt: freezed == sharingExpiresAt
                ? _value.sharingExpiresAt
                : sharingExpiresAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            sharedWith: freezed == sharedWith
                ? _value.sharedWith
                : sharedWith // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$LiveLocationModelImplCopyWith<$Res>
    implements $LiveLocationModelCopyWith<$Res> {
  factory _$$LiveLocationModelImplCopyWith(
    _$LiveLocationModelImpl value,
    $Res Function(_$LiveLocationModelImpl) then,
  ) = __$$LiveLocationModelImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String userId,
    String userName,
    String userType,
    double latitude,
    double longitude,
    DateTime timestamp,
    double? speed,
    double? heading,
    double? accuracy,
    bool? isSharingEnabled,
    DateTime? sharingStartedAt,
    DateTime? sharingExpiresAt,
    String? sharedWith,
  });
}

/// @nodoc
class __$$LiveLocationModelImplCopyWithImpl<$Res>
    extends _$LiveLocationModelCopyWithImpl<$Res, _$LiveLocationModelImpl>
    implements _$$LiveLocationModelImplCopyWith<$Res> {
  __$$LiveLocationModelImplCopyWithImpl(
    _$LiveLocationModelImpl _value,
    $Res Function(_$LiveLocationModelImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of LiveLocationModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? userId = null,
    Object? userName = null,
    Object? userType = null,
    Object? latitude = null,
    Object? longitude = null,
    Object? timestamp = null,
    Object? speed = freezed,
    Object? heading = freezed,
    Object? accuracy = freezed,
    Object? isSharingEnabled = freezed,
    Object? sharingStartedAt = freezed,
    Object? sharingExpiresAt = freezed,
    Object? sharedWith = freezed,
  }) {
    return _then(
      _$LiveLocationModelImpl(
        userId: null == userId
            ? _value.userId
            : userId // ignore: cast_nullable_to_non_nullable
                  as String,
        userName: null == userName
            ? _value.userName
            : userName // ignore: cast_nullable_to_non_nullable
                  as String,
        userType: null == userType
            ? _value.userType
            : userType // ignore: cast_nullable_to_non_nullable
                  as String,
        latitude: null == latitude
            ? _value.latitude
            : latitude // ignore: cast_nullable_to_non_nullable
                  as double,
        longitude: null == longitude
            ? _value.longitude
            : longitude // ignore: cast_nullable_to_non_nullable
                  as double,
        timestamp: null == timestamp
            ? _value.timestamp
            : timestamp // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        speed: freezed == speed
            ? _value.speed
            : speed // ignore: cast_nullable_to_non_nullable
                  as double?,
        heading: freezed == heading
            ? _value.heading
            : heading // ignore: cast_nullable_to_non_nullable
                  as double?,
        accuracy: freezed == accuracy
            ? _value.accuracy
            : accuracy // ignore: cast_nullable_to_non_nullable
                  as double?,
        isSharingEnabled: freezed == isSharingEnabled
            ? _value.isSharingEnabled
            : isSharingEnabled // ignore: cast_nullable_to_non_nullable
                  as bool?,
        sharingStartedAt: freezed == sharingStartedAt
            ? _value.sharingStartedAt
            : sharingStartedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        sharingExpiresAt: freezed == sharingExpiresAt
            ? _value.sharingExpiresAt
            : sharingExpiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        sharedWith: freezed == sharedWith
            ? _value.sharedWith
            : sharedWith // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$LiveLocationModelImpl implements _LiveLocationModel {
  const _$LiveLocationModelImpl({
    this.userId = '',
    this.userName = '',
    this.userType = '',
    this.latitude = 0.0,
    this.longitude = 0.0,
    required this.timestamp,
    this.speed,
    this.heading,
    this.accuracy,
    this.isSharingEnabled,
    this.sharingStartedAt,
    this.sharingExpiresAt,
    this.sharedWith,
  });

  factory _$LiveLocationModelImpl.fromJson(Map<String, dynamic> json) =>
      _$$LiveLocationModelImplFromJson(json);

  @override
  @JsonKey()
  final String userId;
  @override
  @JsonKey()
  final String userName;
  @override
  @JsonKey()
  final String userType;
  // agent, customer
  @override
  @JsonKey()
  final double latitude;
  @override
  @JsonKey()
  final double longitude;
  @override
  final DateTime timestamp;
  @override
  final double? speed;
  @override
  final double? heading;
  @override
  final double? accuracy;
  @override
  final bool? isSharingEnabled;
  @override
  final DateTime? sharingStartedAt;
  @override
  final DateTime? sharingExpiresAt;
  @override
  final String? sharedWith;

  @override
  String toString() {
    return 'LiveLocationModel(userId: $userId, userName: $userName, userType: $userType, latitude: $latitude, longitude: $longitude, timestamp: $timestamp, speed: $speed, heading: $heading, accuracy: $accuracy, isSharingEnabled: $isSharingEnabled, sharingStartedAt: $sharingStartedAt, sharingExpiresAt: $sharingExpiresAt, sharedWith: $sharedWith)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$LiveLocationModelImpl &&
            (identical(other.userId, userId) || other.userId == userId) &&
            (identical(other.userName, userName) ||
                other.userName == userName) &&
            (identical(other.userType, userType) ||
                other.userType == userType) &&
            (identical(other.latitude, latitude) ||
                other.latitude == latitude) &&
            (identical(other.longitude, longitude) ||
                other.longitude == longitude) &&
            (identical(other.timestamp, timestamp) ||
                other.timestamp == timestamp) &&
            (identical(other.speed, speed) || other.speed == speed) &&
            (identical(other.heading, heading) || other.heading == heading) &&
            (identical(other.accuracy, accuracy) ||
                other.accuracy == accuracy) &&
            (identical(other.isSharingEnabled, isSharingEnabled) ||
                other.isSharingEnabled == isSharingEnabled) &&
            (identical(other.sharingStartedAt, sharingStartedAt) ||
                other.sharingStartedAt == sharingStartedAt) &&
            (identical(other.sharingExpiresAt, sharingExpiresAt) ||
                other.sharingExpiresAt == sharingExpiresAt) &&
            (identical(other.sharedWith, sharedWith) ||
                other.sharedWith == sharedWith));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    userId,
    userName,
    userType,
    latitude,
    longitude,
    timestamp,
    speed,
    heading,
    accuracy,
    isSharingEnabled,
    sharingStartedAt,
    sharingExpiresAt,
    sharedWith,
  );

  /// Create a copy of LiveLocationModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$LiveLocationModelImplCopyWith<_$LiveLocationModelImpl> get copyWith =>
      __$$LiveLocationModelImplCopyWithImpl<_$LiveLocationModelImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$LiveLocationModelImplToJson(this);
  }
}

abstract class _LiveLocationModel implements LiveLocationModel {
  const factory _LiveLocationModel({
    final String userId,
    final String userName,
    final String userType,
    final double latitude,
    final double longitude,
    required final DateTime timestamp,
    final double? speed,
    final double? heading,
    final double? accuracy,
    final bool? isSharingEnabled,
    final DateTime? sharingStartedAt,
    final DateTime? sharingExpiresAt,
    final String? sharedWith,
  }) = _$LiveLocationModelImpl;

  factory _LiveLocationModel.fromJson(Map<String, dynamic> json) =
      _$LiveLocationModelImpl.fromJson;

  @override
  String get userId;
  @override
  String get userName;
  @override
  String get userType; // agent, customer
  @override
  double get latitude;
  @override
  double get longitude;
  @override
  DateTime get timestamp;
  @override
  double? get speed;
  @override
  double? get heading;
  @override
  double? get accuracy;
  @override
  bool? get isSharingEnabled;
  @override
  DateTime? get sharingStartedAt;
  @override
  DateTime? get sharingExpiresAt;
  @override
  String? get sharedWith;

  /// Create a copy of LiveLocationModel
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$LiveLocationModelImplCopyWith<_$LiveLocationModelImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
