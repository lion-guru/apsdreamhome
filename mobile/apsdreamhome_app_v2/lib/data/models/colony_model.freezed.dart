// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'colony_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
  'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models',
);

ColonyModel _$ColonyModelFromJson(Map<String, dynamic> json) {
  return _ColonyModel.fromJson(json);
}

/// @nodoc
mixin _$ColonyModel {
  String get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get location => throw _privateConstructorUsedError;
  String get district => throw _privateConstructorUsedError;
  String get state => throw _privateConstructorUsedError;
  String? get description => throw _privateConstructorUsedError;
  List<String>? get images => throw _privateConstructorUsedError;
  String? get masterPlanImage => throw _privateConstructorUsedError;
  String? get videoUrl => throw _privateConstructorUsedError;
  double? get latitude => throw _privateConstructorUsedError;
  double? get longitude =>
      throw _privateConstructorUsedError; // Plot Statistics
  int get totalPlots => throw _privateConstructorUsedError;
  int get availablePlots => throw _privateConstructorUsedError;
  int get holdPlots => throw _privateConstructorUsedError;
  int get bookedPlots => throw _privateConstructorUsedError;
  int get soldPlots => throw _privateConstructorUsedError; // Pricing
  double get pricePerSqft => throw _privateConstructorUsedError;
  double? get tokenAmount => throw _privateConstructorUsedError;
  double? get bookingPercentage => throw _privateConstructorUsedError;
  Map<String, double>? get blockWisePricing =>
      throw _privateConstructorUsedError; // A, B, C blocks with different rates
  // Amenities
  List<String>? get amenities => throw _privateConstructorUsedError; // Status
  String get status =>
      throw _privateConstructorUsedError; // upcoming, launching, active, completed, sold_out
  DateTime? get launchDate => throw _privateConstructorUsedError;
  DateTime? get completionDate =>
      throw _privateConstructorUsedError; // Timestamps
  DateTime? get createdAt => throw _privateConstructorUsedError;
  DateTime? get updatedAt => throw _privateConstructorUsedError;
  String? get createdBy =>
      throw _privateConstructorUsedError; // Additional Info
  String? get reraNumber => throw _privateConstructorUsedError;
  String? get legalStatus => throw _privateConstructorUsedError;
  List<String>? get nearbyLandmarks => throw _privateConstructorUsedError;
  Map<String, dynamic>? get additionalInfo =>
      throw _privateConstructorUsedError; // New Fields for Images and Maps
  String? get layoutMap => throw _privateConstructorUsedError;
  String? get rateList => throw _privateConstructorUsedError;
  String? get handbill => throw _privateConstructorUsedError;
  String? get mapLink => throw _privateConstructorUsedError;

  /// Serializes this ColonyModel to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of ColonyModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $ColonyModelCopyWith<ColonyModel> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $ColonyModelCopyWith<$Res> {
  factory $ColonyModelCopyWith(
    ColonyModel value,
    $Res Function(ColonyModel) then,
  ) = _$ColonyModelCopyWithImpl<$Res, ColonyModel>;
  @useResult
  $Res call({
    String id,
    String name,
    String location,
    String district,
    String state,
    String? description,
    List<String>? images,
    String? masterPlanImage,
    String? videoUrl,
    double? latitude,
    double? longitude,
    int totalPlots,
    int availablePlots,
    int holdPlots,
    int bookedPlots,
    int soldPlots,
    double pricePerSqft,
    double? tokenAmount,
    double? bookingPercentage,
    Map<String, double>? blockWisePricing,
    List<String>? amenities,
    String status,
    DateTime? launchDate,
    DateTime? completionDate,
    DateTime? createdAt,
    DateTime? updatedAt,
    String? createdBy,
    String? reraNumber,
    String? legalStatus,
    List<String>? nearbyLandmarks,
    Map<String, dynamic>? additionalInfo,
    String? layoutMap,
    String? rateList,
    String? handbill,
    String? mapLink,
  });
}

/// @nodoc
class _$ColonyModelCopyWithImpl<$Res, $Val extends ColonyModel>
    implements $ColonyModelCopyWith<$Res> {
  _$ColonyModelCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of ColonyModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? location = null,
    Object? district = null,
    Object? state = null,
    Object? description = freezed,
    Object? images = freezed,
    Object? masterPlanImage = freezed,
    Object? videoUrl = freezed,
    Object? latitude = freezed,
    Object? longitude = freezed,
    Object? totalPlots = null,
    Object? availablePlots = null,
    Object? holdPlots = null,
    Object? bookedPlots = null,
    Object? soldPlots = null,
    Object? pricePerSqft = null,
    Object? tokenAmount = freezed,
    Object? bookingPercentage = freezed,
    Object? blockWisePricing = freezed,
    Object? amenities = freezed,
    Object? status = null,
    Object? launchDate = freezed,
    Object? completionDate = freezed,
    Object? createdAt = freezed,
    Object? updatedAt = freezed,
    Object? createdBy = freezed,
    Object? reraNumber = freezed,
    Object? legalStatus = freezed,
    Object? nearbyLandmarks = freezed,
    Object? additionalInfo = freezed,
    Object? layoutMap = freezed,
    Object? rateList = freezed,
    Object? handbill = freezed,
    Object? mapLink = freezed,
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
            location: null == location
                ? _value.location
                : location // ignore: cast_nullable_to_non_nullable
                      as String,
            district: null == district
                ? _value.district
                : district // ignore: cast_nullable_to_non_nullable
                      as String,
            state: null == state
                ? _value.state
                : state // ignore: cast_nullable_to_non_nullable
                      as String,
            description: freezed == description
                ? _value.description
                : description // ignore: cast_nullable_to_non_nullable
                      as String?,
            images: freezed == images
                ? _value.images
                : images // ignore: cast_nullable_to_non_nullable
                      as List<String>?,
            masterPlanImage: freezed == masterPlanImage
                ? _value.masterPlanImage
                : masterPlanImage // ignore: cast_nullable_to_non_nullable
                      as String?,
            videoUrl: freezed == videoUrl
                ? _value.videoUrl
                : videoUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            latitude: freezed == latitude
                ? _value.latitude
                : latitude // ignore: cast_nullable_to_non_nullable
                      as double?,
            longitude: freezed == longitude
                ? _value.longitude
                : longitude // ignore: cast_nullable_to_non_nullable
                      as double?,
            totalPlots: null == totalPlots
                ? _value.totalPlots
                : totalPlots // ignore: cast_nullable_to_non_nullable
                      as int,
            availablePlots: null == availablePlots
                ? _value.availablePlots
                : availablePlots // ignore: cast_nullable_to_non_nullable
                      as int,
            holdPlots: null == holdPlots
                ? _value.holdPlots
                : holdPlots // ignore: cast_nullable_to_non_nullable
                      as int,
            bookedPlots: null == bookedPlots
                ? _value.bookedPlots
                : bookedPlots // ignore: cast_nullable_to_non_nullable
                      as int,
            soldPlots: null == soldPlots
                ? _value.soldPlots
                : soldPlots // ignore: cast_nullable_to_non_nullable
                      as int,
            pricePerSqft: null == pricePerSqft
                ? _value.pricePerSqft
                : pricePerSqft // ignore: cast_nullable_to_non_nullable
                      as double,
            tokenAmount: freezed == tokenAmount
                ? _value.tokenAmount
                : tokenAmount // ignore: cast_nullable_to_non_nullable
                      as double?,
            bookingPercentage: freezed == bookingPercentage
                ? _value.bookingPercentage
                : bookingPercentage // ignore: cast_nullable_to_non_nullable
                      as double?,
            blockWisePricing: freezed == blockWisePricing
                ? _value.blockWisePricing
                : blockWisePricing // ignore: cast_nullable_to_non_nullable
                      as Map<String, double>?,
            amenities: freezed == amenities
                ? _value.amenities
                : amenities // ignore: cast_nullable_to_non_nullable
                      as List<String>?,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as String,
            launchDate: freezed == launchDate
                ? _value.launchDate
                : launchDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            completionDate: freezed == completionDate
                ? _value.completionDate
                : completionDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            updatedAt: freezed == updatedAt
                ? _value.updatedAt
                : updatedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            createdBy: freezed == createdBy
                ? _value.createdBy
                : createdBy // ignore: cast_nullable_to_non_nullable
                      as String?,
            reraNumber: freezed == reraNumber
                ? _value.reraNumber
                : reraNumber // ignore: cast_nullable_to_non_nullable
                      as String?,
            legalStatus: freezed == legalStatus
                ? _value.legalStatus
                : legalStatus // ignore: cast_nullable_to_non_nullable
                      as String?,
            nearbyLandmarks: freezed == nearbyLandmarks
                ? _value.nearbyLandmarks
                : nearbyLandmarks // ignore: cast_nullable_to_non_nullable
                      as List<String>?,
            additionalInfo: freezed == additionalInfo
                ? _value.additionalInfo
                : additionalInfo // ignore: cast_nullable_to_non_nullable
                      as Map<String, dynamic>?,
            layoutMap: freezed == layoutMap
                ? _value.layoutMap
                : layoutMap // ignore: cast_nullable_to_non_nullable
                      as String?,
            rateList: freezed == rateList
                ? _value.rateList
                : rateList // ignore: cast_nullable_to_non_nullable
                      as String?,
            handbill: freezed == handbill
                ? _value.handbill
                : handbill // ignore: cast_nullable_to_non_nullable
                      as String?,
            mapLink: freezed == mapLink
                ? _value.mapLink
                : mapLink // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$ColonyModelImplCopyWith<$Res>
    implements $ColonyModelCopyWith<$Res> {
  factory _$$ColonyModelImplCopyWith(
    _$ColonyModelImpl value,
    $Res Function(_$ColonyModelImpl) then,
  ) = __$$ColonyModelImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String location,
    String district,
    String state,
    String? description,
    List<String>? images,
    String? masterPlanImage,
    String? videoUrl,
    double? latitude,
    double? longitude,
    int totalPlots,
    int availablePlots,
    int holdPlots,
    int bookedPlots,
    int soldPlots,
    double pricePerSqft,
    double? tokenAmount,
    double? bookingPercentage,
    Map<String, double>? blockWisePricing,
    List<String>? amenities,
    String status,
    DateTime? launchDate,
    DateTime? completionDate,
    DateTime? createdAt,
    DateTime? updatedAt,
    String? createdBy,
    String? reraNumber,
    String? legalStatus,
    List<String>? nearbyLandmarks,
    Map<String, dynamic>? additionalInfo,
    String? layoutMap,
    String? rateList,
    String? handbill,
    String? mapLink,
  });
}

/// @nodoc
class __$$ColonyModelImplCopyWithImpl<$Res>
    extends _$ColonyModelCopyWithImpl<$Res, _$ColonyModelImpl>
    implements _$$ColonyModelImplCopyWith<$Res> {
  __$$ColonyModelImplCopyWithImpl(
    _$ColonyModelImpl _value,
    $Res Function(_$ColonyModelImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of ColonyModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? location = null,
    Object? district = null,
    Object? state = null,
    Object? description = freezed,
    Object? images = freezed,
    Object? masterPlanImage = freezed,
    Object? videoUrl = freezed,
    Object? latitude = freezed,
    Object? longitude = freezed,
    Object? totalPlots = null,
    Object? availablePlots = null,
    Object? holdPlots = null,
    Object? bookedPlots = null,
    Object? soldPlots = null,
    Object? pricePerSqft = null,
    Object? tokenAmount = freezed,
    Object? bookingPercentage = freezed,
    Object? blockWisePricing = freezed,
    Object? amenities = freezed,
    Object? status = null,
    Object? launchDate = freezed,
    Object? completionDate = freezed,
    Object? createdAt = freezed,
    Object? updatedAt = freezed,
    Object? createdBy = freezed,
    Object? reraNumber = freezed,
    Object? legalStatus = freezed,
    Object? nearbyLandmarks = freezed,
    Object? additionalInfo = freezed,
    Object? layoutMap = freezed,
    Object? rateList = freezed,
    Object? handbill = freezed,
    Object? mapLink = freezed,
  }) {
    return _then(
      _$ColonyModelImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _value.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        location: null == location
            ? _value.location
            : location // ignore: cast_nullable_to_non_nullable
                  as String,
        district: null == district
            ? _value.district
            : district // ignore: cast_nullable_to_non_nullable
                  as String,
        state: null == state
            ? _value.state
            : state // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _value.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        images: freezed == images
            ? _value._images
            : images // ignore: cast_nullable_to_non_nullable
                  as List<String>?,
        masterPlanImage: freezed == masterPlanImage
            ? _value.masterPlanImage
            : masterPlanImage // ignore: cast_nullable_to_non_nullable
                  as String?,
        videoUrl: freezed == videoUrl
            ? _value.videoUrl
            : videoUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        latitude: freezed == latitude
            ? _value.latitude
            : latitude // ignore: cast_nullable_to_non_nullable
                  as double?,
        longitude: freezed == longitude
            ? _value.longitude
            : longitude // ignore: cast_nullable_to_non_nullable
                  as double?,
        totalPlots: null == totalPlots
            ? _value.totalPlots
            : totalPlots // ignore: cast_nullable_to_non_nullable
                  as int,
        availablePlots: null == availablePlots
            ? _value.availablePlots
            : availablePlots // ignore: cast_nullable_to_non_nullable
                  as int,
        holdPlots: null == holdPlots
            ? _value.holdPlots
            : holdPlots // ignore: cast_nullable_to_non_nullable
                  as int,
        bookedPlots: null == bookedPlots
            ? _value.bookedPlots
            : bookedPlots // ignore: cast_nullable_to_non_nullable
                  as int,
        soldPlots: null == soldPlots
            ? _value.soldPlots
            : soldPlots // ignore: cast_nullable_to_non_nullable
                  as int,
        pricePerSqft: null == pricePerSqft
            ? _value.pricePerSqft
            : pricePerSqft // ignore: cast_nullable_to_non_nullable
                  as double,
        tokenAmount: freezed == tokenAmount
            ? _value.tokenAmount
            : tokenAmount // ignore: cast_nullable_to_non_nullable
                  as double?,
        bookingPercentage: freezed == bookingPercentage
            ? _value.bookingPercentage
            : bookingPercentage // ignore: cast_nullable_to_non_nullable
                  as double?,
        blockWisePricing: freezed == blockWisePricing
            ? _value._blockWisePricing
            : blockWisePricing // ignore: cast_nullable_to_non_nullable
                  as Map<String, double>?,
        amenities: freezed == amenities
            ? _value._amenities
            : amenities // ignore: cast_nullable_to_non_nullable
                  as List<String>?,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        launchDate: freezed == launchDate
            ? _value.launchDate
            : launchDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        completionDate: freezed == completionDate
            ? _value.completionDate
            : completionDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        updatedAt: freezed == updatedAt
            ? _value.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdBy: freezed == createdBy
            ? _value.createdBy
            : createdBy // ignore: cast_nullable_to_non_nullable
                  as String?,
        reraNumber: freezed == reraNumber
            ? _value.reraNumber
            : reraNumber // ignore: cast_nullable_to_non_nullable
                  as String?,
        legalStatus: freezed == legalStatus
            ? _value.legalStatus
            : legalStatus // ignore: cast_nullable_to_non_nullable
                  as String?,
        nearbyLandmarks: freezed == nearbyLandmarks
            ? _value._nearbyLandmarks
            : nearbyLandmarks // ignore: cast_nullable_to_non_nullable
                  as List<String>?,
        additionalInfo: freezed == additionalInfo
            ? _value._additionalInfo
            : additionalInfo // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>?,
        layoutMap: freezed == layoutMap
            ? _value.layoutMap
            : layoutMap // ignore: cast_nullable_to_non_nullable
                  as String?,
        rateList: freezed == rateList
            ? _value.rateList
            : rateList // ignore: cast_nullable_to_non_nullable
                  as String?,
        handbill: freezed == handbill
            ? _value.handbill
            : handbill // ignore: cast_nullable_to_non_nullable
                  as String?,
        mapLink: freezed == mapLink
            ? _value.mapLink
            : mapLink // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$ColonyModelImpl extends _ColonyModel {
  const _$ColonyModelImpl({
    required this.id,
    required this.name,
    required this.location,
    required this.district,
    required this.state,
    this.description,
    final List<String>? images,
    this.masterPlanImage,
    this.videoUrl,
    this.latitude,
    this.longitude,
    required this.totalPlots,
    required this.availablePlots,
    required this.holdPlots,
    required this.bookedPlots,
    required this.soldPlots,
    required this.pricePerSqft,
    this.tokenAmount,
    this.bookingPercentage,
    final Map<String, double>? blockWisePricing,
    final List<String>? amenities,
    required this.status,
    this.launchDate,
    this.completionDate,
    this.createdAt,
    this.updatedAt,
    this.createdBy,
    this.reraNumber,
    this.legalStatus,
    final List<String>? nearbyLandmarks,
    final Map<String, dynamic>? additionalInfo,
    this.layoutMap,
    this.rateList,
    this.handbill,
    this.mapLink,
  }) : _images = images,
       _blockWisePricing = blockWisePricing,
       _amenities = amenities,
       _nearbyLandmarks = nearbyLandmarks,
       _additionalInfo = additionalInfo,
       super._();

  factory _$ColonyModelImpl.fromJson(Map<String, dynamic> json) =>
      _$$ColonyModelImplFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String location;
  @override
  final String district;
  @override
  final String state;
  @override
  final String? description;
  final List<String>? _images;
  @override
  List<String>? get images {
    final value = _images;
    if (value == null) return null;
    if (_images is EqualUnmodifiableListView) return _images;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  @override
  final String? masterPlanImage;
  @override
  final String? videoUrl;
  @override
  final double? latitude;
  @override
  final double? longitude;
  // Plot Statistics
  @override
  final int totalPlots;
  @override
  final int availablePlots;
  @override
  final int holdPlots;
  @override
  final int bookedPlots;
  @override
  final int soldPlots;
  // Pricing
  @override
  final double pricePerSqft;
  @override
  final double? tokenAmount;
  @override
  final double? bookingPercentage;
  final Map<String, double>? _blockWisePricing;
  @override
  Map<String, double>? get blockWisePricing {
    final value = _blockWisePricing;
    if (value == null) return null;
    if (_blockWisePricing is EqualUnmodifiableMapView) return _blockWisePricing;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(value);
  }

  // A, B, C blocks with different rates
  // Amenities
  final List<String>? _amenities;
  // A, B, C blocks with different rates
  // Amenities
  @override
  List<String>? get amenities {
    final value = _amenities;
    if (value == null) return null;
    if (_amenities is EqualUnmodifiableListView) return _amenities;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  // Status
  @override
  final String status;
  // upcoming, launching, active, completed, sold_out
  @override
  final DateTime? launchDate;
  @override
  final DateTime? completionDate;
  // Timestamps
  @override
  final DateTime? createdAt;
  @override
  final DateTime? updatedAt;
  @override
  final String? createdBy;
  // Additional Info
  @override
  final String? reraNumber;
  @override
  final String? legalStatus;
  final List<String>? _nearbyLandmarks;
  @override
  List<String>? get nearbyLandmarks {
    final value = _nearbyLandmarks;
    if (value == null) return null;
    if (_nearbyLandmarks is EqualUnmodifiableListView) return _nearbyLandmarks;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  final Map<String, dynamic>? _additionalInfo;
  @override
  Map<String, dynamic>? get additionalInfo {
    final value = _additionalInfo;
    if (value == null) return null;
    if (_additionalInfo is EqualUnmodifiableMapView) return _additionalInfo;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(value);
  }

  // New Fields for Images and Maps
  @override
  final String? layoutMap;
  @override
  final String? rateList;
  @override
  final String? handbill;
  @override
  final String? mapLink;

  @override
  String toString() {
    return 'ColonyModel(id: $id, name: $name, location: $location, district: $district, state: $state, description: $description, images: $images, masterPlanImage: $masterPlanImage, videoUrl: $videoUrl, latitude: $latitude, longitude: $longitude, totalPlots: $totalPlots, availablePlots: $availablePlots, holdPlots: $holdPlots, bookedPlots: $bookedPlots, soldPlots: $soldPlots, pricePerSqft: $pricePerSqft, tokenAmount: $tokenAmount, bookingPercentage: $bookingPercentage, blockWisePricing: $blockWisePricing, amenities: $amenities, status: $status, launchDate: $launchDate, completionDate: $completionDate, createdAt: $createdAt, updatedAt: $updatedAt, createdBy: $createdBy, reraNumber: $reraNumber, legalStatus: $legalStatus, nearbyLandmarks: $nearbyLandmarks, additionalInfo: $additionalInfo, layoutMap: $layoutMap, rateList: $rateList, handbill: $handbill, mapLink: $mapLink)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$ColonyModelImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.location, location) ||
                other.location == location) &&
            (identical(other.district, district) ||
                other.district == district) &&
            (identical(other.state, state) || other.state == state) &&
            (identical(other.description, description) ||
                other.description == description) &&
            const DeepCollectionEquality().equals(other._images, _images) &&
            (identical(other.masterPlanImage, masterPlanImage) ||
                other.masterPlanImage == masterPlanImage) &&
            (identical(other.videoUrl, videoUrl) ||
                other.videoUrl == videoUrl) &&
            (identical(other.latitude, latitude) ||
                other.latitude == latitude) &&
            (identical(other.longitude, longitude) ||
                other.longitude == longitude) &&
            (identical(other.totalPlots, totalPlots) ||
                other.totalPlots == totalPlots) &&
            (identical(other.availablePlots, availablePlots) ||
                other.availablePlots == availablePlots) &&
            (identical(other.holdPlots, holdPlots) ||
                other.holdPlots == holdPlots) &&
            (identical(other.bookedPlots, bookedPlots) ||
                other.bookedPlots == bookedPlots) &&
            (identical(other.soldPlots, soldPlots) ||
                other.soldPlots == soldPlots) &&
            (identical(other.pricePerSqft, pricePerSqft) ||
                other.pricePerSqft == pricePerSqft) &&
            (identical(other.tokenAmount, tokenAmount) ||
                other.tokenAmount == tokenAmount) &&
            (identical(other.bookingPercentage, bookingPercentage) ||
                other.bookingPercentage == bookingPercentage) &&
            const DeepCollectionEquality().equals(
              other._blockWisePricing,
              _blockWisePricing,
            ) &&
            const DeepCollectionEquality().equals(
              other._amenities,
              _amenities,
            ) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.launchDate, launchDate) ||
                other.launchDate == launchDate) &&
            (identical(other.completionDate, completionDate) ||
                other.completionDate == completionDate) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt) &&
            (identical(other.createdBy, createdBy) ||
                other.createdBy == createdBy) &&
            (identical(other.reraNumber, reraNumber) ||
                other.reraNumber == reraNumber) &&
            (identical(other.legalStatus, legalStatus) ||
                other.legalStatus == legalStatus) &&
            const DeepCollectionEquality().equals(
              other._nearbyLandmarks,
              _nearbyLandmarks,
            ) &&
            const DeepCollectionEquality().equals(
              other._additionalInfo,
              _additionalInfo,
            ) &&
            (identical(other.layoutMap, layoutMap) ||
                other.layoutMap == layoutMap) &&
            (identical(other.rateList, rateList) ||
                other.rateList == rateList) &&
            (identical(other.handbill, handbill) ||
                other.handbill == handbill) &&
            (identical(other.mapLink, mapLink) || other.mapLink == mapLink));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    id,
    name,
    location,
    district,
    state,
    description,
    const DeepCollectionEquality().hash(_images),
    masterPlanImage,
    videoUrl,
    latitude,
    longitude,
    totalPlots,
    availablePlots,
    holdPlots,
    bookedPlots,
    soldPlots,
    pricePerSqft,
    tokenAmount,
    bookingPercentage,
    const DeepCollectionEquality().hash(_blockWisePricing),
    const DeepCollectionEquality().hash(_amenities),
    status,
    launchDate,
    completionDate,
    createdAt,
    updatedAt,
    createdBy,
    reraNumber,
    legalStatus,
    const DeepCollectionEquality().hash(_nearbyLandmarks),
    const DeepCollectionEquality().hash(_additionalInfo),
    layoutMap,
    rateList,
    handbill,
    mapLink,
  ]);

  /// Create a copy of ColonyModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$ColonyModelImplCopyWith<_$ColonyModelImpl> get copyWith =>
      __$$ColonyModelImplCopyWithImpl<_$ColonyModelImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$ColonyModelImplToJson(this);
  }
}

abstract class _ColonyModel extends ColonyModel {
  const factory _ColonyModel({
    required final String id,
    required final String name,
    required final String location,
    required final String district,
    required final String state,
    final String? description,
    final List<String>? images,
    final String? masterPlanImage,
    final String? videoUrl,
    final double? latitude,
    final double? longitude,
    required final int totalPlots,
    required final int availablePlots,
    required final int holdPlots,
    required final int bookedPlots,
    required final int soldPlots,
    required final double pricePerSqft,
    final double? tokenAmount,
    final double? bookingPercentage,
    final Map<String, double>? blockWisePricing,
    final List<String>? amenities,
    required final String status,
    final DateTime? launchDate,
    final DateTime? completionDate,
    final DateTime? createdAt,
    final DateTime? updatedAt,
    final String? createdBy,
    final String? reraNumber,
    final String? legalStatus,
    final List<String>? nearbyLandmarks,
    final Map<String, dynamic>? additionalInfo,
    final String? layoutMap,
    final String? rateList,
    final String? handbill,
    final String? mapLink,
  }) = _$ColonyModelImpl;
  const _ColonyModel._() : super._();

  factory _ColonyModel.fromJson(Map<String, dynamic> json) =
      _$ColonyModelImpl.fromJson;

  @override
  String get id;
  @override
  String get name;
  @override
  String get location;
  @override
  String get district;
  @override
  String get state;
  @override
  String? get description;
  @override
  List<String>? get images;
  @override
  String? get masterPlanImage;
  @override
  String? get videoUrl;
  @override
  double? get latitude;
  @override
  double? get longitude; // Plot Statistics
  @override
  int get totalPlots;
  @override
  int get availablePlots;
  @override
  int get holdPlots;
  @override
  int get bookedPlots;
  @override
  int get soldPlots; // Pricing
  @override
  double get pricePerSqft;
  @override
  double? get tokenAmount;
  @override
  double? get bookingPercentage;
  @override
  Map<String, double>? get blockWisePricing; // A, B, C blocks with different rates
  // Amenities
  @override
  List<String>? get amenities; // Status
  @override
  String get status; // upcoming, launching, active, completed, sold_out
  @override
  DateTime? get launchDate;
  @override
  DateTime? get completionDate; // Timestamps
  @override
  DateTime? get createdAt;
  @override
  DateTime? get updatedAt;
  @override
  String? get createdBy; // Additional Info
  @override
  String? get reraNumber;
  @override
  String? get legalStatus;
  @override
  List<String>? get nearbyLandmarks;
  @override
  Map<String, dynamic>? get additionalInfo; // New Fields for Images and Maps
  @override
  String? get layoutMap;
  @override
  String? get rateList;
  @override
  String? get handbill;
  @override
  String? get mapLink;

  /// Create a copy of ColonyModel
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$ColonyModelImplCopyWith<_$ColonyModelImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
