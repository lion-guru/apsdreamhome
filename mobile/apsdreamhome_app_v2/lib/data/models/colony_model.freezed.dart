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
  int get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String? get slug => throw _privateConstructorUsedError;
  String? get description =>
      throw _privateConstructorUsedError; // Plot statistics (API: total_plots, available_plots)
  @JsonKey(name: 'total_plots')
  int get totalPlots => throw _privateConstructorUsedError;
  @JsonKey(name: 'available_plots')
  int get availablePlots => throw _privateConstructorUsedError; // Pricing (API: starting_price)
  @JsonKey(name: 'starting_price')
  double get pricePerSqft => throw _privateConstructorUsedError; // Location (API: district_name, district_id)
  @JsonKey(name: 'district_name')
  String get district => throw _privateConstructorUsedError;
  @JsonKey(name: 'district_id')
  int get districtId => throw _privateConstructorUsedError; // Images (API: image_path, image_url)
  @JsonKey(name: 'image_path')
  String? get imagePath => throw _privateConstructorUsedError;
  @JsonKey(name: 'image_url')
  String? get imageUrl => throw _privateConstructorUsedError; // Status (API: is_active, is_featured)
  @JsonKey(name: 'is_active')
  bool get isActive => throw _privateConstructorUsedError;
  @JsonKey(name: 'is_featured')
  bool get isFeatured => throw _privateConstructorUsedError; // Compatibility fields (computed from API data)
  String get location => throw _privateConstructorUsedError;
  String get state => throw _privateConstructorUsedError;
  List<String>? get images => throw _privateConstructorUsedError;
  String? get masterPlanImage => throw _privateConstructorUsedError;
  String? get videoUrl => throw _privateConstructorUsedError;
  double? get latitude => throw _privateConstructorUsedError;
  double? get longitude =>
      throw _privateConstructorUsedError; // Extended plot stats
  int get holdPlots => throw _privateConstructorUsedError;
  int get bookedPlots => throw _privateConstructorUsedError;
  int get soldPlots => throw _privateConstructorUsedError; // Extended pricing
  double? get tokenAmount => throw _privateConstructorUsedError;
  double? get bookingPercentage => throw _privateConstructorUsedError;
  Map<String, double>? get blockWisePricing =>
      throw _privateConstructorUsedError; // Amenities
  List<String>? get amenities => throw _privateConstructorUsedError; // Dates
  String? get launchDate => throw _privateConstructorUsedError;
  String? get completionDate => throw _privateConstructorUsedError;
  String? get createdAt => throw _privateConstructorUsedError;
  String? get updatedAt => throw _privateConstructorUsedError; // Additional
  String? get createdBy => throw _privateConstructorUsedError;
  String? get reraNumber => throw _privateConstructorUsedError;
  String? get legalStatus => throw _privateConstructorUsedError;
  List<String>? get nearbyLandmarks => throw _privateConstructorUsedError;
  Map<String, dynamic>? get additionalInfo =>
      throw _privateConstructorUsedError;
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
    int id,
    String name,
    String? slug,
    String? description,
    @JsonKey(name: 'total_plots') int totalPlots,
    @JsonKey(name: 'available_plots') int availablePlots,
    @JsonKey(name: 'starting_price') double pricePerSqft,
    @JsonKey(name: 'district_name') String district,
    @JsonKey(name: 'district_id') int districtId,
    @JsonKey(name: 'image_path') String? imagePath,
    @JsonKey(name: 'image_url') String? imageUrl,
    @JsonKey(name: 'is_active') bool isActive,
    @JsonKey(name: 'is_featured') bool isFeatured,
    String location,
    String state,
    List<String>? images,
    String? masterPlanImage,
    String? videoUrl,
    double? latitude,
    double? longitude,
    int holdPlots,
    int bookedPlots,
    int soldPlots,
    double? tokenAmount,
    double? bookingPercentage,
    Map<String, double>? blockWisePricing,
    List<String>? amenities,
    String? launchDate,
    String? completionDate,
    String? createdAt,
    String? updatedAt,
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
    Object? slug = freezed,
    Object? description = freezed,
    Object? totalPlots = null,
    Object? availablePlots = null,
    Object? pricePerSqft = null,
    Object? district = null,
    Object? districtId = null,
    Object? imagePath = freezed,
    Object? imageUrl = freezed,
    Object? isActive = null,
    Object? isFeatured = null,
    Object? location = null,
    Object? state = null,
    Object? images = freezed,
    Object? masterPlanImage = freezed,
    Object? videoUrl = freezed,
    Object? latitude = freezed,
    Object? longitude = freezed,
    Object? holdPlots = null,
    Object? bookedPlots = null,
    Object? soldPlots = null,
    Object? tokenAmount = freezed,
    Object? bookingPercentage = freezed,
    Object? blockWisePricing = freezed,
    Object? amenities = freezed,
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
                      as int,
            name: null == name
                ? _value.name
                : name // ignore: cast_nullable_to_non_nullable
                      as String,
            slug: freezed == slug
                ? _value.slug
                : slug // ignore: cast_nullable_to_non_nullable
                      as String?,
            description: freezed == description
                ? _value.description
                : description // ignore: cast_nullable_to_non_nullable
                      as String?,
            totalPlots: null == totalPlots
                ? _value.totalPlots
                : totalPlots // ignore: cast_nullable_to_non_nullable
                      as int,
            availablePlots: null == availablePlots
                ? _value.availablePlots
                : availablePlots // ignore: cast_nullable_to_non_nullable
                      as int,
            pricePerSqft: null == pricePerSqft
                ? _value.pricePerSqft
                : pricePerSqft // ignore: cast_nullable_to_non_nullable
                      as double,
            district: null == district
                ? _value.district
                : district // ignore: cast_nullable_to_non_nullable
                      as String,
            districtId: null == districtId
                ? _value.districtId
                : districtId // ignore: cast_nullable_to_non_nullable
                      as int,
            imagePath: freezed == imagePath
                ? _value.imagePath
                : imagePath // ignore: cast_nullable_to_non_nullable
                      as String?,
            imageUrl: freezed == imageUrl
                ? _value.imageUrl
                : imageUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            isActive: null == isActive
                ? _value.isActive
                : isActive // ignore: cast_nullable_to_non_nullable
                      as bool,
            isFeatured: null == isFeatured
                ? _value.isFeatured
                : isFeatured // ignore: cast_nullable_to_non_nullable
                      as bool,
            location: null == location
                ? _value.location
                : location // ignore: cast_nullable_to_non_nullable
                      as String,
            state: null == state
                ? _value.state
                : state // ignore: cast_nullable_to_non_nullable
                      as String,
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
            launchDate: freezed == launchDate
                ? _value.launchDate
                : launchDate // ignore: cast_nullable_to_non_nullable
                      as String?,
            completionDate: freezed == completionDate
                ? _value.completionDate
                : completionDate // ignore: cast_nullable_to_non_nullable
                      as String?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as String?,
            updatedAt: freezed == updatedAt
                ? _value.updatedAt
                : updatedAt // ignore: cast_nullable_to_non_nullable
                      as String?,
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
    int id,
    String name,
    String? slug,
    String? description,
    @JsonKey(name: 'total_plots') int totalPlots,
    @JsonKey(name: 'available_plots') int availablePlots,
    @JsonKey(name: 'starting_price') double pricePerSqft,
    @JsonKey(name: 'district_name') String district,
    @JsonKey(name: 'district_id') int districtId,
    @JsonKey(name: 'image_path') String? imagePath,
    @JsonKey(name: 'image_url') String? imageUrl,
    @JsonKey(name: 'is_active') bool isActive,
    @JsonKey(name: 'is_featured') bool isFeatured,
    String location,
    String state,
    List<String>? images,
    String? masterPlanImage,
    String? videoUrl,
    double? latitude,
    double? longitude,
    int holdPlots,
    int bookedPlots,
    int soldPlots,
    double? tokenAmount,
    double? bookingPercentage,
    Map<String, double>? blockWisePricing,
    List<String>? amenities,
    String? launchDate,
    String? completionDate,
    String? createdAt,
    String? updatedAt,
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
    Object? slug = freezed,
    Object? description = freezed,
    Object? totalPlots = null,
    Object? availablePlots = null,
    Object? pricePerSqft = null,
    Object? district = null,
    Object? districtId = null,
    Object? imagePath = freezed,
    Object? imageUrl = freezed,
    Object? isActive = null,
    Object? isFeatured = null,
    Object? location = null,
    Object? state = null,
    Object? images = freezed,
    Object? masterPlanImage = freezed,
    Object? videoUrl = freezed,
    Object? latitude = freezed,
    Object? longitude = freezed,
    Object? holdPlots = null,
    Object? bookedPlots = null,
    Object? soldPlots = null,
    Object? tokenAmount = freezed,
    Object? bookingPercentage = freezed,
    Object? blockWisePricing = freezed,
    Object? amenities = freezed,
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
                  as int,
        name: null == name
            ? _value.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        slug: freezed == slug
            ? _value.slug
            : slug // ignore: cast_nullable_to_non_nullable
                  as String?,
        description: freezed == description
            ? _value.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        totalPlots: null == totalPlots
            ? _value.totalPlots
            : totalPlots // ignore: cast_nullable_to_non_nullable
                  as int,
        availablePlots: null == availablePlots
            ? _value.availablePlots
            : availablePlots // ignore: cast_nullable_to_non_nullable
                  as int,
        pricePerSqft: null == pricePerSqft
            ? _value.pricePerSqft
            : pricePerSqft // ignore: cast_nullable_to_non_nullable
                  as double,
        district: null == district
            ? _value.district
            : district // ignore: cast_nullable_to_non_nullable
                  as String,
        districtId: null == districtId
            ? _value.districtId
            : districtId // ignore: cast_nullable_to_non_nullable
                  as int,
        imagePath: freezed == imagePath
            ? _value.imagePath
            : imagePath // ignore: cast_nullable_to_non_nullable
                  as String?,
        imageUrl: freezed == imageUrl
            ? _value.imageUrl
            : imageUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        isActive: null == isActive
            ? _value.isActive
            : isActive // ignore: cast_nullable_to_non_nullable
                  as bool,
        isFeatured: null == isFeatured
            ? _value.isFeatured
            : isFeatured // ignore: cast_nullable_to_non_nullable
                  as bool,
        location: null == location
            ? _value.location
            : location // ignore: cast_nullable_to_non_nullable
                  as String,
        state: null == state
            ? _value.state
            : state // ignore: cast_nullable_to_non_nullable
                  as String,
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
        launchDate: freezed == launchDate
            ? _value.launchDate
            : launchDate // ignore: cast_nullable_to_non_nullable
                  as String?,
        completionDate: freezed == completionDate
            ? _value.completionDate
            : completionDate // ignore: cast_nullable_to_non_nullable
                  as String?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as String?,
        updatedAt: freezed == updatedAt
            ? _value.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as String?,
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
    this.id = 0,
    this.name = '',
    this.slug,
    this.description,
    @JsonKey(name: 'total_plots') this.totalPlots = 0,
    @JsonKey(name: 'available_plots') this.availablePlots = 0,
    @JsonKey(name: 'starting_price') this.pricePerSqft = 0.0,
    @JsonKey(name: 'district_name') this.district = '',
    @JsonKey(name: 'district_id') this.districtId = 0,
    @JsonKey(name: 'image_path') this.imagePath,
    @JsonKey(name: 'image_url') this.imageUrl,
    @JsonKey(name: 'is_active') this.isActive = true,
    @JsonKey(name: 'is_featured') this.isFeatured = false,
    this.location = '',
    this.state = '',
    final List<String>? images,
    this.masterPlanImage,
    this.videoUrl,
    this.latitude,
    this.longitude,
    this.holdPlots = 0,
    this.bookedPlots = 0,
    this.soldPlots = 0,
    this.tokenAmount,
    this.bookingPercentage,
    final Map<String, double>? blockWisePricing,
    final List<String>? amenities,
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
  @JsonKey()
  final int id;
  @override
  @JsonKey()
  final String name;
  @override
  final String? slug;
  @override
  final String? description;
  // Plot statistics (API: total_plots, available_plots)
  @override
  @JsonKey(name: 'total_plots')
  final int totalPlots;
  @override
  @JsonKey(name: 'available_plots')
  final int availablePlots;
  // Pricing (API: starting_price)
  @override
  @JsonKey(name: 'starting_price')
  final double pricePerSqft;
  // Location (API: district_name, district_id)
  @override
  @JsonKey(name: 'district_name')
  final String district;
  @override
  @JsonKey(name: 'district_id')
  final int districtId;
  // Images (API: image_path, image_url)
  @override
  @JsonKey(name: 'image_path')
  final String? imagePath;
  @override
  @JsonKey(name: 'image_url')
  final String? imageUrl;
  // Status (API: is_active, is_featured)
  @override
  @JsonKey(name: 'is_active')
  final bool isActive;
  @override
  @JsonKey(name: 'is_featured')
  final bool isFeatured;
  // Compatibility fields (computed from API data)
  @override
  @JsonKey()
  final String location;
  @override
  @JsonKey()
  final String state;
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
  // Extended plot stats
  @override
  @JsonKey()
  final int holdPlots;
  @override
  @JsonKey()
  final int bookedPlots;
  @override
  @JsonKey()
  final int soldPlots;
  // Extended pricing
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

  // Amenities
  final List<String>? _amenities;
  // Amenities
  @override
  List<String>? get amenities {
    final value = _amenities;
    if (value == null) return null;
    if (_amenities is EqualUnmodifiableListView) return _amenities;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  // Dates
  @override
  final String? launchDate;
  @override
  final String? completionDate;
  @override
  final String? createdAt;
  @override
  final String? updatedAt;
  // Additional
  @override
  final String? createdBy;
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
    return 'ColonyModel(id: $id, name: $name, slug: $slug, description: $description, totalPlots: $totalPlots, availablePlots: $availablePlots, pricePerSqft: $pricePerSqft, district: $district, districtId: $districtId, imagePath: $imagePath, imageUrl: $imageUrl, isActive: $isActive, isFeatured: $isFeatured, location: $location, state: $state, images: $images, masterPlanImage: $masterPlanImage, videoUrl: $videoUrl, latitude: $latitude, longitude: $longitude, holdPlots: $holdPlots, bookedPlots: $bookedPlots, soldPlots: $soldPlots, tokenAmount: $tokenAmount, bookingPercentage: $bookingPercentage, blockWisePricing: $blockWisePricing, amenities: $amenities, launchDate: $launchDate, completionDate: $completionDate, createdAt: $createdAt, updatedAt: $updatedAt, createdBy: $createdBy, reraNumber: $reraNumber, legalStatus: $legalStatus, nearbyLandmarks: $nearbyLandmarks, additionalInfo: $additionalInfo, layoutMap: $layoutMap, rateList: $rateList, handbill: $handbill, mapLink: $mapLink)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$ColonyModelImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.slug, slug) || other.slug == slug) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.totalPlots, totalPlots) ||
                other.totalPlots == totalPlots) &&
            (identical(other.availablePlots, availablePlots) ||
                other.availablePlots == availablePlots) &&
            (identical(other.pricePerSqft, pricePerSqft) ||
                other.pricePerSqft == pricePerSqft) &&
            (identical(other.district, district) ||
                other.district == district) &&
            (identical(other.districtId, districtId) ||
                other.districtId == districtId) &&
            (identical(other.imagePath, imagePath) ||
                other.imagePath == imagePath) &&
            (identical(other.imageUrl, imageUrl) ||
                other.imageUrl == imageUrl) &&
            (identical(other.isActive, isActive) ||
                other.isActive == isActive) &&
            (identical(other.isFeatured, isFeatured) ||
                other.isFeatured == isFeatured) &&
            (identical(other.location, location) ||
                other.location == location) &&
            (identical(other.state, state) || other.state == state) &&
            const DeepCollectionEquality().equals(other._images, _images) &&
            (identical(other.masterPlanImage, masterPlanImage) ||
                other.masterPlanImage == masterPlanImage) &&
            (identical(other.videoUrl, videoUrl) ||
                other.videoUrl == videoUrl) &&
            (identical(other.latitude, latitude) ||
                other.latitude == latitude) &&
            (identical(other.longitude, longitude) ||
                other.longitude == longitude) &&
            (identical(other.holdPlots, holdPlots) ||
                other.holdPlots == holdPlots) &&
            (identical(other.bookedPlots, bookedPlots) ||
                other.bookedPlots == bookedPlots) &&
            (identical(other.soldPlots, soldPlots) ||
                other.soldPlots == soldPlots) &&
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
    slug,
    description,
    totalPlots,
    availablePlots,
    pricePerSqft,
    district,
    districtId,
    imagePath,
    imageUrl,
    isActive,
    isFeatured,
    location,
    state,
    const DeepCollectionEquality().hash(_images),
    masterPlanImage,
    videoUrl,
    latitude,
    longitude,
    holdPlots,
    bookedPlots,
    soldPlots,
    tokenAmount,
    bookingPercentage,
    const DeepCollectionEquality().hash(_blockWisePricing),
    const DeepCollectionEquality().hash(_amenities),
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
    final int id,
    final String name,
    final String? slug,
    final String? description,
    @JsonKey(name: 'total_plots') final int totalPlots,
    @JsonKey(name: 'available_plots') final int availablePlots,
    @JsonKey(name: 'starting_price') final double pricePerSqft,
    @JsonKey(name: 'district_name') final String district,
    @JsonKey(name: 'district_id') final int districtId,
    @JsonKey(name: 'image_path') final String? imagePath,
    @JsonKey(name: 'image_url') final String? imageUrl,
    @JsonKey(name: 'is_active') final bool isActive,
    @JsonKey(name: 'is_featured') final bool isFeatured,
    final String location,
    final String state,
    final List<String>? images,
    final String? masterPlanImage,
    final String? videoUrl,
    final double? latitude,
    final double? longitude,
    final int holdPlots,
    final int bookedPlots,
    final int soldPlots,
    final double? tokenAmount,
    final double? bookingPercentage,
    final Map<String, double>? blockWisePricing,
    final List<String>? amenities,
    final String? launchDate,
    final String? completionDate,
    final String? createdAt,
    final String? updatedAt,
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
  int get id;
  @override
  String get name;
  @override
  String? get slug;
  @override
  String? get description; // Plot statistics (API: total_plots, available_plots)
  @override
  @JsonKey(name: 'total_plots')
  int get totalPlots;
  @override
  @JsonKey(name: 'available_plots')
  int get availablePlots; // Pricing (API: starting_price)
  @override
  @JsonKey(name: 'starting_price')
  double get pricePerSqft; // Location (API: district_name, district_id)
  @override
  @JsonKey(name: 'district_name')
  String get district;
  @override
  @JsonKey(name: 'district_id')
  int get districtId; // Images (API: image_path, image_url)
  @override
  @JsonKey(name: 'image_path')
  String? get imagePath;
  @override
  @JsonKey(name: 'image_url')
  String? get imageUrl; // Status (API: is_active, is_featured)
  @override
  @JsonKey(name: 'is_active')
  bool get isActive;
  @override
  @JsonKey(name: 'is_featured')
  bool get isFeatured; // Compatibility fields (computed from API data)
  @override
  String get location;
  @override
  String get state;
  @override
  List<String>? get images;
  @override
  String? get masterPlanImage;
  @override
  String? get videoUrl;
  @override
  double? get latitude;
  @override
  double? get longitude; // Extended plot stats
  @override
  int get holdPlots;
  @override
  int get bookedPlots;
  @override
  int get soldPlots; // Extended pricing
  @override
  double? get tokenAmount;
  @override
  double? get bookingPercentage;
  @override
  Map<String, double>? get blockWisePricing; // Amenities
  @override
  List<String>? get amenities; // Dates
  @override
  String? get launchDate;
  @override
  String? get completionDate;
  @override
  String? get createdAt;
  @override
  String? get updatedAt; // Additional
  @override
  String? get createdBy;
  @override
  String? get reraNumber;
  @override
  String? get legalStatus;
  @override
  List<String>? get nearbyLandmarks;
  @override
  Map<String, dynamic>? get additionalInfo;
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
