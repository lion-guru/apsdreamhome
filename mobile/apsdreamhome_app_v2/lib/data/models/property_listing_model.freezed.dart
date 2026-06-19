// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'property_listing_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
  'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models',
);

PropertyListing _$PropertyListingFromJson(Map<String, dynamic> json) {
  return _PropertyListing.fromJson(json);
}

/// @nodoc
mixin _$PropertyListing {
  String get id => throw _privateConstructorUsedError;
  String get title => throw _privateConstructorUsedError;
  String get description => throw _privateConstructorUsedError;
  PropertyType get propertyType =>
      throw _privateConstructorUsedError; // Plot, House, Flat, Shop, Farmhouse
  ListingPurpose get purpose =>
      throw _privateConstructorUsedError; // Sell, Rent, Lease
  // Owner Details
  String get ownerId => throw _privateConstructorUsedError;
  String get ownerName => throw _privateConstructorUsedError;
  String get ownerPhone => throw _privateConstructorUsedError;
  String get ownerEmail => throw _privateConstructorUsedError;
  OwnerType get ownerType =>
      throw _privateConstructorUsedError; // Customer, Associate, Agent, Employee
  // Location
  String get state => throw _privateConstructorUsedError;
  String get district => throw _privateConstructorUsedError;
  String get city => throw _privateConstructorUsedError;
  String get locality => throw _privateConstructorUsedError;
  String get address => throw _privateConstructorUsedError;
  GeoLocation get location => throw _privateConstructorUsedError;
  String get landmark => throw _privateConstructorUsedError; // Property Details
  double get areaSqft => throw _privateConstructorUsedError;
  String? get areaUnit =>
      throw _privateConstructorUsedError; // sqft, acre, guntha
  double get expectedPrice => throw _privateConstructorUsedError;
  PriceNegotiable? get negotiable => throw _privateConstructorUsedError;
  String get priceType =>
      throw _privateConstructorUsedError; // Fixed, Negotiable, Best Offer
  // Features
  List<String> get images => throw _privateConstructorUsedError;
  List<String> get videos => throw _privateConstructorUsedError;
  List<String> get documents =>
      throw _privateConstructorUsedError; // Legal papers, registry
  Map<String, dynamic>? get features =>
      throw _privateConstructorUsedError; // Bedrooms, bathrooms, parking, etc.
  // Status & Verification
  ListingStatus get status =>
      throw _privateConstructorUsedError; // Pending, Verified, Active, Sold, Rejected
  String? get verifiedBy => throw _privateConstructorUsedError;
  DateTime? get verifiedAt => throw _privateConstructorUsedError;
  String? get rejectionReason => throw _privateConstructorUsedError;
  int? get verificationFee =>
      throw _privateConstructorUsedError; // Admin charges for verification
  // Statistics
  int get viewCount => throw _privateConstructorUsedError;
  int get inquiryCount => throw _privateConstructorUsedError;
  int get callCount => throw _privateConstructorUsedError;
  int get whatsappCount => throw _privateConstructorUsedError;
  DateTime? get lastInquiryAt =>
      throw _privateConstructorUsedError; // Listing Plan (Monetization)
  ListingPlan? get listingPlan =>
      throw _privateConstructorUsedError; // Free, Featured, Premium, Spotlight
  DateTime? get planExpiryDate => throw _privateConstructorUsedError;
  bool get isFeatured => throw _privateConstructorUsedError;
  bool get isPremium => throw _privateConstructorUsedError;
  bool get isSpotlight => throw _privateConstructorUsedError; // Lead Generation
  List<String> get interestedBuyers =>
      throw _privateConstructorUsedError; // User IDs
  List<PropertyInquiry> get inquiries =>
      throw _privateConstructorUsedError; // Admin Notes
  String? get adminNotes => throw _privateConstructorUsedError;
  List<String> get tags => throw _privateConstructorUsedError;
  DateTime get createdAt => throw _privateConstructorUsedError;
  DateTime get updatedAt => throw _privateConstructorUsedError;

  /// Serializes this PropertyListing to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of PropertyListing
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $PropertyListingCopyWith<PropertyListing> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $PropertyListingCopyWith<$Res> {
  factory $PropertyListingCopyWith(
    PropertyListing value,
    $Res Function(PropertyListing) then,
  ) = _$PropertyListingCopyWithImpl<$Res, PropertyListing>;
  @useResult
  $Res call({
    String id,
    String title,
    String description,
    PropertyType propertyType,
    ListingPurpose purpose,
    String ownerId,
    String ownerName,
    String ownerPhone,
    String ownerEmail,
    OwnerType ownerType,
    String state,
    String district,
    String city,
    String locality,
    String address,
    GeoLocation location,
    String landmark,
    double areaSqft,
    String? areaUnit,
    double expectedPrice,
    PriceNegotiable? negotiable,
    String priceType,
    List<String> images,
    List<String> videos,
    List<String> documents,
    Map<String, dynamic>? features,
    ListingStatus status,
    String? verifiedBy,
    DateTime? verifiedAt,
    String? rejectionReason,
    int? verificationFee,
    int viewCount,
    int inquiryCount,
    int callCount,
    int whatsappCount,
    DateTime? lastInquiryAt,
    ListingPlan? listingPlan,
    DateTime? planExpiryDate,
    bool isFeatured,
    bool isPremium,
    bool isSpotlight,
    List<String> interestedBuyers,
    List<PropertyInquiry> inquiries,
    String? adminNotes,
    List<String> tags,
    DateTime createdAt,
    DateTime updatedAt,
  });
}

/// @nodoc
class _$PropertyListingCopyWithImpl<$Res, $Val extends PropertyListing>
    implements $PropertyListingCopyWith<$Res> {
  _$PropertyListingCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of PropertyListing
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? title = null,
    Object? description = null,
    Object? propertyType = null,
    Object? purpose = null,
    Object? ownerId = null,
    Object? ownerName = null,
    Object? ownerPhone = null,
    Object? ownerEmail = null,
    Object? ownerType = null,
    Object? state = null,
    Object? district = null,
    Object? city = null,
    Object? locality = null,
    Object? address = null,
    Object? location = null,
    Object? landmark = null,
    Object? areaSqft = null,
    Object? areaUnit = freezed,
    Object? expectedPrice = null,
    Object? negotiable = freezed,
    Object? priceType = null,
    Object? images = null,
    Object? videos = null,
    Object? documents = null,
    Object? features = freezed,
    Object? status = null,
    Object? verifiedBy = freezed,
    Object? verifiedAt = freezed,
    Object? rejectionReason = freezed,
    Object? verificationFee = freezed,
    Object? viewCount = null,
    Object? inquiryCount = null,
    Object? callCount = null,
    Object? whatsappCount = null,
    Object? lastInquiryAt = freezed,
    Object? listingPlan = freezed,
    Object? planExpiryDate = freezed,
    Object? isFeatured = null,
    Object? isPremium = null,
    Object? isSpotlight = null,
    Object? interestedBuyers = null,
    Object? inquiries = null,
    Object? adminNotes = freezed,
    Object? tags = null,
    Object? createdAt = null,
    Object? updatedAt = null,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            title: null == title
                ? _value.title
                : title // ignore: cast_nullable_to_non_nullable
                      as String,
            description: null == description
                ? _value.description
                : description // ignore: cast_nullable_to_non_nullable
                      as String,
            propertyType: null == propertyType
                ? _value.propertyType
                : propertyType // ignore: cast_nullable_to_non_nullable
                      as PropertyType,
            purpose: null == purpose
                ? _value.purpose
                : purpose // ignore: cast_nullable_to_non_nullable
                      as ListingPurpose,
            ownerId: null == ownerId
                ? _value.ownerId
                : ownerId // ignore: cast_nullable_to_non_nullable
                      as String,
            ownerName: null == ownerName
                ? _value.ownerName
                : ownerName // ignore: cast_nullable_to_non_nullable
                      as String,
            ownerPhone: null == ownerPhone
                ? _value.ownerPhone
                : ownerPhone // ignore: cast_nullable_to_non_nullable
                      as String,
            ownerEmail: null == ownerEmail
                ? _value.ownerEmail
                : ownerEmail // ignore: cast_nullable_to_non_nullable
                      as String,
            ownerType: null == ownerType
                ? _value.ownerType
                : ownerType // ignore: cast_nullable_to_non_nullable
                      as OwnerType,
            state: null == state
                ? _value.state
                : state // ignore: cast_nullable_to_non_nullable
                      as String,
            district: null == district
                ? _value.district
                : district // ignore: cast_nullable_to_non_nullable
                      as String,
            city: null == city
                ? _value.city
                : city // ignore: cast_nullable_to_non_nullable
                      as String,
            locality: null == locality
                ? _value.locality
                : locality // ignore: cast_nullable_to_non_nullable
                      as String,
            address: null == address
                ? _value.address
                : address // ignore: cast_nullable_to_non_nullable
                      as String,
            location: null == location
                ? _value.location
                : location // ignore: cast_nullable_to_non_nullable
                      as GeoLocation,
            landmark: null == landmark
                ? _value.landmark
                : landmark // ignore: cast_nullable_to_non_nullable
                      as String,
            areaSqft: null == areaSqft
                ? _value.areaSqft
                : areaSqft // ignore: cast_nullable_to_non_nullable
                      as double,
            areaUnit: freezed == areaUnit
                ? _value.areaUnit
                : areaUnit // ignore: cast_nullable_to_non_nullable
                      as String?,
            expectedPrice: null == expectedPrice
                ? _value.expectedPrice
                : expectedPrice // ignore: cast_nullable_to_non_nullable
                      as double,
            negotiable: freezed == negotiable
                ? _value.negotiable
                : negotiable // ignore: cast_nullable_to_non_nullable
                      as PriceNegotiable?,
            priceType: null == priceType
                ? _value.priceType
                : priceType // ignore: cast_nullable_to_non_nullable
                      as String,
            images: null == images
                ? _value.images
                : images // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            videos: null == videos
                ? _value.videos
                : videos // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            documents: null == documents
                ? _value.documents
                : documents // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            features: freezed == features
                ? _value.features
                : features // ignore: cast_nullable_to_non_nullable
                      as Map<String, dynamic>?,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as ListingStatus,
            verifiedBy: freezed == verifiedBy
                ? _value.verifiedBy
                : verifiedBy // ignore: cast_nullable_to_non_nullable
                      as String?,
            verifiedAt: freezed == verifiedAt
                ? _value.verifiedAt
                : verifiedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            rejectionReason: freezed == rejectionReason
                ? _value.rejectionReason
                : rejectionReason // ignore: cast_nullable_to_non_nullable
                      as String?,
            verificationFee: freezed == verificationFee
                ? _value.verificationFee
                : verificationFee // ignore: cast_nullable_to_non_nullable
                      as int?,
            viewCount: null == viewCount
                ? _value.viewCount
                : viewCount // ignore: cast_nullable_to_non_nullable
                      as int,
            inquiryCount: null == inquiryCount
                ? _value.inquiryCount
                : inquiryCount // ignore: cast_nullable_to_non_nullable
                      as int,
            callCount: null == callCount
                ? _value.callCount
                : callCount // ignore: cast_nullable_to_non_nullable
                      as int,
            whatsappCount: null == whatsappCount
                ? _value.whatsappCount
                : whatsappCount // ignore: cast_nullable_to_non_nullable
                      as int,
            lastInquiryAt: freezed == lastInquiryAt
                ? _value.lastInquiryAt
                : lastInquiryAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            listingPlan: freezed == listingPlan
                ? _value.listingPlan
                : listingPlan // ignore: cast_nullable_to_non_nullable
                      as ListingPlan?,
            planExpiryDate: freezed == planExpiryDate
                ? _value.planExpiryDate
                : planExpiryDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            isFeatured: null == isFeatured
                ? _value.isFeatured
                : isFeatured // ignore: cast_nullable_to_non_nullable
                      as bool,
            isPremium: null == isPremium
                ? _value.isPremium
                : isPremium // ignore: cast_nullable_to_non_nullable
                      as bool,
            isSpotlight: null == isSpotlight
                ? _value.isSpotlight
                : isSpotlight // ignore: cast_nullable_to_non_nullable
                      as bool,
            interestedBuyers: null == interestedBuyers
                ? _value.interestedBuyers
                : interestedBuyers // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            inquiries: null == inquiries
                ? _value.inquiries
                : inquiries // ignore: cast_nullable_to_non_nullable
                      as List<PropertyInquiry>,
            adminNotes: freezed == adminNotes
                ? _value.adminNotes
                : adminNotes // ignore: cast_nullable_to_non_nullable
                      as String?,
            tags: null == tags
                ? _value.tags
                : tags // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            createdAt: null == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            updatedAt: null == updatedAt
                ? _value.updatedAt
                : updatedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$PropertyListingImplCopyWith<$Res>
    implements $PropertyListingCopyWith<$Res> {
  factory _$$PropertyListingImplCopyWith(
    _$PropertyListingImpl value,
    $Res Function(_$PropertyListingImpl) then,
  ) = __$$PropertyListingImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String title,
    String description,
    PropertyType propertyType,
    ListingPurpose purpose,
    String ownerId,
    String ownerName,
    String ownerPhone,
    String ownerEmail,
    OwnerType ownerType,
    String state,
    String district,
    String city,
    String locality,
    String address,
    GeoLocation location,
    String landmark,
    double areaSqft,
    String? areaUnit,
    double expectedPrice,
    PriceNegotiable? negotiable,
    String priceType,
    List<String> images,
    List<String> videos,
    List<String> documents,
    Map<String, dynamic>? features,
    ListingStatus status,
    String? verifiedBy,
    DateTime? verifiedAt,
    String? rejectionReason,
    int? verificationFee,
    int viewCount,
    int inquiryCount,
    int callCount,
    int whatsappCount,
    DateTime? lastInquiryAt,
    ListingPlan? listingPlan,
    DateTime? planExpiryDate,
    bool isFeatured,
    bool isPremium,
    bool isSpotlight,
    List<String> interestedBuyers,
    List<PropertyInquiry> inquiries,
    String? adminNotes,
    List<String> tags,
    DateTime createdAt,
    DateTime updatedAt,
  });
}

/// @nodoc
class __$$PropertyListingImplCopyWithImpl<$Res>
    extends _$PropertyListingCopyWithImpl<$Res, _$PropertyListingImpl>
    implements _$$PropertyListingImplCopyWith<$Res> {
  __$$PropertyListingImplCopyWithImpl(
    _$PropertyListingImpl _value,
    $Res Function(_$PropertyListingImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of PropertyListing
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? title = null,
    Object? description = null,
    Object? propertyType = null,
    Object? purpose = null,
    Object? ownerId = null,
    Object? ownerName = null,
    Object? ownerPhone = null,
    Object? ownerEmail = null,
    Object? ownerType = null,
    Object? state = null,
    Object? district = null,
    Object? city = null,
    Object? locality = null,
    Object? address = null,
    Object? location = null,
    Object? landmark = null,
    Object? areaSqft = null,
    Object? areaUnit = freezed,
    Object? expectedPrice = null,
    Object? negotiable = freezed,
    Object? priceType = null,
    Object? images = null,
    Object? videos = null,
    Object? documents = null,
    Object? features = freezed,
    Object? status = null,
    Object? verifiedBy = freezed,
    Object? verifiedAt = freezed,
    Object? rejectionReason = freezed,
    Object? verificationFee = freezed,
    Object? viewCount = null,
    Object? inquiryCount = null,
    Object? callCount = null,
    Object? whatsappCount = null,
    Object? lastInquiryAt = freezed,
    Object? listingPlan = freezed,
    Object? planExpiryDate = freezed,
    Object? isFeatured = null,
    Object? isPremium = null,
    Object? isSpotlight = null,
    Object? interestedBuyers = null,
    Object? inquiries = null,
    Object? adminNotes = freezed,
    Object? tags = null,
    Object? createdAt = null,
    Object? updatedAt = null,
  }) {
    return _then(
      _$PropertyListingImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        title: null == title
            ? _value.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String,
        description: null == description
            ? _value.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String,
        propertyType: null == propertyType
            ? _value.propertyType
            : propertyType // ignore: cast_nullable_to_non_nullable
                  as PropertyType,
        purpose: null == purpose
            ? _value.purpose
            : purpose // ignore: cast_nullable_to_non_nullable
                  as ListingPurpose,
        ownerId: null == ownerId
            ? _value.ownerId
            : ownerId // ignore: cast_nullable_to_non_nullable
                  as String,
        ownerName: null == ownerName
            ? _value.ownerName
            : ownerName // ignore: cast_nullable_to_non_nullable
                  as String,
        ownerPhone: null == ownerPhone
            ? _value.ownerPhone
            : ownerPhone // ignore: cast_nullable_to_non_nullable
                  as String,
        ownerEmail: null == ownerEmail
            ? _value.ownerEmail
            : ownerEmail // ignore: cast_nullable_to_non_nullable
                  as String,
        ownerType: null == ownerType
            ? _value.ownerType
            : ownerType // ignore: cast_nullable_to_non_nullable
                  as OwnerType,
        state: null == state
            ? _value.state
            : state // ignore: cast_nullable_to_non_nullable
                  as String,
        district: null == district
            ? _value.district
            : district // ignore: cast_nullable_to_non_nullable
                  as String,
        city: null == city
            ? _value.city
            : city // ignore: cast_nullable_to_non_nullable
                  as String,
        locality: null == locality
            ? _value.locality
            : locality // ignore: cast_nullable_to_non_nullable
                  as String,
        address: null == address
            ? _value.address
            : address // ignore: cast_nullable_to_non_nullable
                  as String,
        location: null == location
            ? _value.location
            : location // ignore: cast_nullable_to_non_nullable
                  as GeoLocation,
        landmark: null == landmark
            ? _value.landmark
            : landmark // ignore: cast_nullable_to_non_nullable
                  as String,
        areaSqft: null == areaSqft
            ? _value.areaSqft
            : areaSqft // ignore: cast_nullable_to_non_nullable
                  as double,
        areaUnit: freezed == areaUnit
            ? _value.areaUnit
            : areaUnit // ignore: cast_nullable_to_non_nullable
                  as String?,
        expectedPrice: null == expectedPrice
            ? _value.expectedPrice
            : expectedPrice // ignore: cast_nullable_to_non_nullable
                  as double,
        negotiable: freezed == negotiable
            ? _value.negotiable
            : negotiable // ignore: cast_nullable_to_non_nullable
                  as PriceNegotiable?,
        priceType: null == priceType
            ? _value.priceType
            : priceType // ignore: cast_nullable_to_non_nullable
                  as String,
        images: null == images
            ? _value._images
            : images // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        videos: null == videos
            ? _value._videos
            : videos // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        documents: null == documents
            ? _value._documents
            : documents // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        features: freezed == features
            ? _value._features
            : features // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>?,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as ListingStatus,
        verifiedBy: freezed == verifiedBy
            ? _value.verifiedBy
            : verifiedBy // ignore: cast_nullable_to_non_nullable
                  as String?,
        verifiedAt: freezed == verifiedAt
            ? _value.verifiedAt
            : verifiedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        rejectionReason: freezed == rejectionReason
            ? _value.rejectionReason
            : rejectionReason // ignore: cast_nullable_to_non_nullable
                  as String?,
        verificationFee: freezed == verificationFee
            ? _value.verificationFee
            : verificationFee // ignore: cast_nullable_to_non_nullable
                  as int?,
        viewCount: null == viewCount
            ? _value.viewCount
            : viewCount // ignore: cast_nullable_to_non_nullable
                  as int,
        inquiryCount: null == inquiryCount
            ? _value.inquiryCount
            : inquiryCount // ignore: cast_nullable_to_non_nullable
                  as int,
        callCount: null == callCount
            ? _value.callCount
            : callCount // ignore: cast_nullable_to_non_nullable
                  as int,
        whatsappCount: null == whatsappCount
            ? _value.whatsappCount
            : whatsappCount // ignore: cast_nullable_to_non_nullable
                  as int,
        lastInquiryAt: freezed == lastInquiryAt
            ? _value.lastInquiryAt
            : lastInquiryAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        listingPlan: freezed == listingPlan
            ? _value.listingPlan
            : listingPlan // ignore: cast_nullable_to_non_nullable
                  as ListingPlan?,
        planExpiryDate: freezed == planExpiryDate
            ? _value.planExpiryDate
            : planExpiryDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        isFeatured: null == isFeatured
            ? _value.isFeatured
            : isFeatured // ignore: cast_nullable_to_non_nullable
                  as bool,
        isPremium: null == isPremium
            ? _value.isPremium
            : isPremium // ignore: cast_nullable_to_non_nullable
                  as bool,
        isSpotlight: null == isSpotlight
            ? _value.isSpotlight
            : isSpotlight // ignore: cast_nullable_to_non_nullable
                  as bool,
        interestedBuyers: null == interestedBuyers
            ? _value._interestedBuyers
            : interestedBuyers // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        inquiries: null == inquiries
            ? _value._inquiries
            : inquiries // ignore: cast_nullable_to_non_nullable
                  as List<PropertyInquiry>,
        adminNotes: freezed == adminNotes
            ? _value.adminNotes
            : adminNotes // ignore: cast_nullable_to_non_nullable
                  as String?,
        tags: null == tags
            ? _value._tags
            : tags // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        createdAt: null == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        updatedAt: null == updatedAt
            ? _value.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$PropertyListingImpl extends _PropertyListing {
  const _$PropertyListingImpl({
    required this.id,
    required this.title,
    required this.description,
    required this.propertyType,
    required this.purpose,
    required this.ownerId,
    required this.ownerName,
    required this.ownerPhone,
    required this.ownerEmail,
    required this.ownerType,
    required this.state,
    required this.district,
    required this.city,
    required this.locality,
    required this.address,
    required this.location,
    required this.landmark,
    required this.areaSqft,
    this.areaUnit,
    required this.expectedPrice,
    this.negotiable,
    required this.priceType,
    final List<String> images = const [],
    final List<String> videos = const [],
    final List<String> documents = const [],
    final Map<String, dynamic>? features,
    required this.status,
    this.verifiedBy,
    this.verifiedAt,
    this.rejectionReason,
    this.verificationFee,
    this.viewCount = 0,
    this.inquiryCount = 0,
    this.callCount = 0,
    this.whatsappCount = 0,
    this.lastInquiryAt,
    this.listingPlan,
    this.planExpiryDate,
    this.isFeatured = false,
    this.isPremium = false,
    this.isSpotlight = false,
    final List<String> interestedBuyers = const [],
    final List<PropertyInquiry> inquiries = const [],
    this.adminNotes,
    final List<String> tags = const [],
    required this.createdAt,
    required this.updatedAt,
  }) : _images = images,
       _videos = videos,
       _documents = documents,
       _features = features,
       _interestedBuyers = interestedBuyers,
       _inquiries = inquiries,
       _tags = tags,
       super._();

  factory _$PropertyListingImpl.fromJson(Map<String, dynamic> json) =>
      _$$PropertyListingImplFromJson(json);

  @override
  final String id;
  @override
  final String title;
  @override
  final String description;
  @override
  final PropertyType propertyType;
  // Plot, House, Flat, Shop, Farmhouse
  @override
  final ListingPurpose purpose;
  // Sell, Rent, Lease
  // Owner Details
  @override
  final String ownerId;
  @override
  final String ownerName;
  @override
  final String ownerPhone;
  @override
  final String ownerEmail;
  @override
  final OwnerType ownerType;
  // Customer, Associate, Agent, Employee
  // Location
  @override
  final String state;
  @override
  final String district;
  @override
  final String city;
  @override
  final String locality;
  @override
  final String address;
  @override
  final GeoLocation location;
  @override
  final String landmark;
  // Property Details
  @override
  final double areaSqft;
  @override
  final String? areaUnit;
  // sqft, acre, guntha
  @override
  final double expectedPrice;
  @override
  final PriceNegotiable? negotiable;
  @override
  final String priceType;
  // Fixed, Negotiable, Best Offer
  // Features
  final List<String> _images;
  // Fixed, Negotiable, Best Offer
  // Features
  @override
  @JsonKey()
  List<String> get images {
    if (_images is EqualUnmodifiableListView) return _images;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_images);
  }

  final List<String> _videos;
  @override
  @JsonKey()
  List<String> get videos {
    if (_videos is EqualUnmodifiableListView) return _videos;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_videos);
  }

  final List<String> _documents;
  @override
  @JsonKey()
  List<String> get documents {
    if (_documents is EqualUnmodifiableListView) return _documents;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_documents);
  }

  // Legal papers, registry
  final Map<String, dynamic>? _features;
  // Legal papers, registry
  @override
  Map<String, dynamic>? get features {
    final value = _features;
    if (value == null) return null;
    if (_features is EqualUnmodifiableMapView) return _features;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(value);
  }

  // Bedrooms, bathrooms, parking, etc.
  // Status & Verification
  @override
  final ListingStatus status;
  // Pending, Verified, Active, Sold, Rejected
  @override
  final String? verifiedBy;
  @override
  final DateTime? verifiedAt;
  @override
  final String? rejectionReason;
  @override
  final int? verificationFee;
  // Admin charges for verification
  // Statistics
  @override
  @JsonKey()
  final int viewCount;
  @override
  @JsonKey()
  final int inquiryCount;
  @override
  @JsonKey()
  final int callCount;
  @override
  @JsonKey()
  final int whatsappCount;
  @override
  final DateTime? lastInquiryAt;
  // Listing Plan (Monetization)
  @override
  final ListingPlan? listingPlan;
  // Free, Featured, Premium, Spotlight
  @override
  final DateTime? planExpiryDate;
  @override
  @JsonKey()
  final bool isFeatured;
  @override
  @JsonKey()
  final bool isPremium;
  @override
  @JsonKey()
  final bool isSpotlight;
  // Lead Generation
  final List<String> _interestedBuyers;
  // Lead Generation
  @override
  @JsonKey()
  List<String> get interestedBuyers {
    if (_interestedBuyers is EqualUnmodifiableListView)
      return _interestedBuyers;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_interestedBuyers);
  }

  // User IDs
  final List<PropertyInquiry> _inquiries;
  // User IDs
  @override
  @JsonKey()
  List<PropertyInquiry> get inquiries {
    if (_inquiries is EqualUnmodifiableListView) return _inquiries;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_inquiries);
  }

  // Admin Notes
  @override
  final String? adminNotes;
  final List<String> _tags;
  @override
  @JsonKey()
  List<String> get tags {
    if (_tags is EqualUnmodifiableListView) return _tags;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_tags);
  }

  @override
  final DateTime createdAt;
  @override
  final DateTime updatedAt;

  @override
  String toString() {
    return 'PropertyListing(id: $id, title: $title, description: $description, propertyType: $propertyType, purpose: $purpose, ownerId: $ownerId, ownerName: $ownerName, ownerPhone: $ownerPhone, ownerEmail: $ownerEmail, ownerType: $ownerType, state: $state, district: $district, city: $city, locality: $locality, address: $address, location: $location, landmark: $landmark, areaSqft: $areaSqft, areaUnit: $areaUnit, expectedPrice: $expectedPrice, negotiable: $negotiable, priceType: $priceType, images: $images, videos: $videos, documents: $documents, features: $features, status: $status, verifiedBy: $verifiedBy, verifiedAt: $verifiedAt, rejectionReason: $rejectionReason, verificationFee: $verificationFee, viewCount: $viewCount, inquiryCount: $inquiryCount, callCount: $callCount, whatsappCount: $whatsappCount, lastInquiryAt: $lastInquiryAt, listingPlan: $listingPlan, planExpiryDate: $planExpiryDate, isFeatured: $isFeatured, isPremium: $isPremium, isSpotlight: $isSpotlight, interestedBuyers: $interestedBuyers, inquiries: $inquiries, adminNotes: $adminNotes, tags: $tags, createdAt: $createdAt, updatedAt: $updatedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$PropertyListingImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.title, title) || other.title == title) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.propertyType, propertyType) ||
                other.propertyType == propertyType) &&
            (identical(other.purpose, purpose) || other.purpose == purpose) &&
            (identical(other.ownerId, ownerId) || other.ownerId == ownerId) &&
            (identical(other.ownerName, ownerName) ||
                other.ownerName == ownerName) &&
            (identical(other.ownerPhone, ownerPhone) ||
                other.ownerPhone == ownerPhone) &&
            (identical(other.ownerEmail, ownerEmail) ||
                other.ownerEmail == ownerEmail) &&
            (identical(other.ownerType, ownerType) ||
                other.ownerType == ownerType) &&
            (identical(other.state, state) || other.state == state) &&
            (identical(other.district, district) ||
                other.district == district) &&
            (identical(other.city, city) || other.city == city) &&
            (identical(other.locality, locality) ||
                other.locality == locality) &&
            (identical(other.address, address) || other.address == address) &&
            (identical(other.location, location) ||
                other.location == location) &&
            (identical(other.landmark, landmark) ||
                other.landmark == landmark) &&
            (identical(other.areaSqft, areaSqft) ||
                other.areaSqft == areaSqft) &&
            (identical(other.areaUnit, areaUnit) ||
                other.areaUnit == areaUnit) &&
            (identical(other.expectedPrice, expectedPrice) ||
                other.expectedPrice == expectedPrice) &&
            (identical(other.negotiable, negotiable) ||
                other.negotiable == negotiable) &&
            (identical(other.priceType, priceType) ||
                other.priceType == priceType) &&
            const DeepCollectionEquality().equals(other._images, _images) &&
            const DeepCollectionEquality().equals(other._videos, _videos) &&
            const DeepCollectionEquality().equals(
              other._documents,
              _documents,
            ) &&
            const DeepCollectionEquality().equals(other._features, _features) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.verifiedBy, verifiedBy) ||
                other.verifiedBy == verifiedBy) &&
            (identical(other.verifiedAt, verifiedAt) ||
                other.verifiedAt == verifiedAt) &&
            (identical(other.rejectionReason, rejectionReason) ||
                other.rejectionReason == rejectionReason) &&
            (identical(other.verificationFee, verificationFee) ||
                other.verificationFee == verificationFee) &&
            (identical(other.viewCount, viewCount) ||
                other.viewCount == viewCount) &&
            (identical(other.inquiryCount, inquiryCount) ||
                other.inquiryCount == inquiryCount) &&
            (identical(other.callCount, callCount) ||
                other.callCount == callCount) &&
            (identical(other.whatsappCount, whatsappCount) ||
                other.whatsappCount == whatsappCount) &&
            (identical(other.lastInquiryAt, lastInquiryAt) ||
                other.lastInquiryAt == lastInquiryAt) &&
            (identical(other.listingPlan, listingPlan) ||
                other.listingPlan == listingPlan) &&
            (identical(other.planExpiryDate, planExpiryDate) ||
                other.planExpiryDate == planExpiryDate) &&
            (identical(other.isFeatured, isFeatured) ||
                other.isFeatured == isFeatured) &&
            (identical(other.isPremium, isPremium) ||
                other.isPremium == isPremium) &&
            (identical(other.isSpotlight, isSpotlight) ||
                other.isSpotlight == isSpotlight) &&
            const DeepCollectionEquality().equals(
              other._interestedBuyers,
              _interestedBuyers,
            ) &&
            const DeepCollectionEquality().equals(
              other._inquiries,
              _inquiries,
            ) &&
            (identical(other.adminNotes, adminNotes) ||
                other.adminNotes == adminNotes) &&
            const DeepCollectionEquality().equals(other._tags, _tags) &&
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
    title,
    description,
    propertyType,
    purpose,
    ownerId,
    ownerName,
    ownerPhone,
    ownerEmail,
    ownerType,
    state,
    district,
    city,
    locality,
    address,
    location,
    landmark,
    areaSqft,
    areaUnit,
    expectedPrice,
    negotiable,
    priceType,
    const DeepCollectionEquality().hash(_images),
    const DeepCollectionEquality().hash(_videos),
    const DeepCollectionEquality().hash(_documents),
    const DeepCollectionEquality().hash(_features),
    status,
    verifiedBy,
    verifiedAt,
    rejectionReason,
    verificationFee,
    viewCount,
    inquiryCount,
    callCount,
    whatsappCount,
    lastInquiryAt,
    listingPlan,
    planExpiryDate,
    isFeatured,
    isPremium,
    isSpotlight,
    const DeepCollectionEquality().hash(_interestedBuyers),
    const DeepCollectionEquality().hash(_inquiries),
    adminNotes,
    const DeepCollectionEquality().hash(_tags),
    createdAt,
    updatedAt,
  ]);

  /// Create a copy of PropertyListing
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$PropertyListingImplCopyWith<_$PropertyListingImpl> get copyWith =>
      __$$PropertyListingImplCopyWithImpl<_$PropertyListingImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$PropertyListingImplToJson(this);
  }
}

abstract class _PropertyListing extends PropertyListing {
  const factory _PropertyListing({
    required final String id,
    required final String title,
    required final String description,
    required final PropertyType propertyType,
    required final ListingPurpose purpose,
    required final String ownerId,
    required final String ownerName,
    required final String ownerPhone,
    required final String ownerEmail,
    required final OwnerType ownerType,
    required final String state,
    required final String district,
    required final String city,
    required final String locality,
    required final String address,
    required final GeoLocation location,
    required final String landmark,
    required final double areaSqft,
    final String? areaUnit,
    required final double expectedPrice,
    final PriceNegotiable? negotiable,
    required final String priceType,
    final List<String> images,
    final List<String> videos,
    final List<String> documents,
    final Map<String, dynamic>? features,
    required final ListingStatus status,
    final String? verifiedBy,
    final DateTime? verifiedAt,
    final String? rejectionReason,
    final int? verificationFee,
    final int viewCount,
    final int inquiryCount,
    final int callCount,
    final int whatsappCount,
    final DateTime? lastInquiryAt,
    final ListingPlan? listingPlan,
    final DateTime? planExpiryDate,
    final bool isFeatured,
    final bool isPremium,
    final bool isSpotlight,
    final List<String> interestedBuyers,
    final List<PropertyInquiry> inquiries,
    final String? adminNotes,
    final List<String> tags,
    required final DateTime createdAt,
    required final DateTime updatedAt,
  }) = _$PropertyListingImpl;
  const _PropertyListing._() : super._();

  factory _PropertyListing.fromJson(Map<String, dynamic> json) =
      _$PropertyListingImpl.fromJson;

  @override
  String get id;
  @override
  String get title;
  @override
  String get description;
  @override
  PropertyType get propertyType; // Plot, House, Flat, Shop, Farmhouse
  @override
  ListingPurpose get purpose; // Sell, Rent, Lease
  // Owner Details
  @override
  String get ownerId;
  @override
  String get ownerName;
  @override
  String get ownerPhone;
  @override
  String get ownerEmail;
  @override
  OwnerType get ownerType; // Customer, Associate, Agent, Employee
  // Location
  @override
  String get state;
  @override
  String get district;
  @override
  String get city;
  @override
  String get locality;
  @override
  String get address;
  @override
  GeoLocation get location;
  @override
  String get landmark; // Property Details
  @override
  double get areaSqft;
  @override
  String? get areaUnit; // sqft, acre, guntha
  @override
  double get expectedPrice;
  @override
  PriceNegotiable? get negotiable;
  @override
  String get priceType; // Fixed, Negotiable, Best Offer
  // Features
  @override
  List<String> get images;
  @override
  List<String> get videos;
  @override
  List<String> get documents; // Legal papers, registry
  @override
  Map<String, dynamic>? get features; // Bedrooms, bathrooms, parking, etc.
  // Status & Verification
  @override
  ListingStatus get status; // Pending, Verified, Active, Sold, Rejected
  @override
  String? get verifiedBy;
  @override
  DateTime? get verifiedAt;
  @override
  String? get rejectionReason;
  @override
  int? get verificationFee; // Admin charges for verification
  // Statistics
  @override
  int get viewCount;
  @override
  int get inquiryCount;
  @override
  int get callCount;
  @override
  int get whatsappCount;
  @override
  DateTime? get lastInquiryAt; // Listing Plan (Monetization)
  @override
  ListingPlan? get listingPlan; // Free, Featured, Premium, Spotlight
  @override
  DateTime? get planExpiryDate;
  @override
  bool get isFeatured;
  @override
  bool get isPremium;
  @override
  bool get isSpotlight; // Lead Generation
  @override
  List<String> get interestedBuyers; // User IDs
  @override
  List<PropertyInquiry> get inquiries; // Admin Notes
  @override
  String? get adminNotes;
  @override
  List<String> get tags;
  @override
  DateTime get createdAt;
  @override
  DateTime get updatedAt;

  /// Create a copy of PropertyListing
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$PropertyListingImplCopyWith<_$PropertyListingImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

PropertyInquiry _$PropertyInquiryFromJson(Map<String, dynamic> json) {
  return _PropertyInquiry.fromJson(json);
}

/// @nodoc
mixin _$PropertyInquiry {
  String get id => throw _privateConstructorUsedError;
  String get buyerId => throw _privateConstructorUsedError;
  String get buyerName => throw _privateConstructorUsedError;
  String get buyerPhone => throw _privateConstructorUsedError;
  String? get buyerEmail => throw _privateConstructorUsedError;
  InquiryType get type =>
      throw _privateConstructorUsedError; // Call, WhatsApp, Email, Visit
  String? get message => throw _privateConstructorUsedError;
  DateTime? get scheduledVisitDate => throw _privateConstructorUsedError;
  InquiryStatus get status =>
      throw _privateConstructorUsedError; // New, Contacted, InDiscussion, Negotiating, Closed
  DateTime? get createdAt => throw _privateConstructorUsedError;
  DateTime? get respondedAt => throw _privateConstructorUsedError;
  String? get responseNotes => throw _privateConstructorUsedError;

  /// Serializes this PropertyInquiry to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of PropertyInquiry
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $PropertyInquiryCopyWith<PropertyInquiry> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $PropertyInquiryCopyWith<$Res> {
  factory $PropertyInquiryCopyWith(
    PropertyInquiry value,
    $Res Function(PropertyInquiry) then,
  ) = _$PropertyInquiryCopyWithImpl<$Res, PropertyInquiry>;
  @useResult
  $Res call({
    String id,
    String buyerId,
    String buyerName,
    String buyerPhone,
    String? buyerEmail,
    InquiryType type,
    String? message,
    DateTime? scheduledVisitDate,
    InquiryStatus status,
    DateTime? createdAt,
    DateTime? respondedAt,
    String? responseNotes,
  });
}

/// @nodoc
class _$PropertyInquiryCopyWithImpl<$Res, $Val extends PropertyInquiry>
    implements $PropertyInquiryCopyWith<$Res> {
  _$PropertyInquiryCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of PropertyInquiry
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? buyerId = null,
    Object? buyerName = null,
    Object? buyerPhone = null,
    Object? buyerEmail = freezed,
    Object? type = null,
    Object? message = freezed,
    Object? scheduledVisitDate = freezed,
    Object? status = null,
    Object? createdAt = freezed,
    Object? respondedAt = freezed,
    Object? responseNotes = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            buyerId: null == buyerId
                ? _value.buyerId
                : buyerId // ignore: cast_nullable_to_non_nullable
                      as String,
            buyerName: null == buyerName
                ? _value.buyerName
                : buyerName // ignore: cast_nullable_to_non_nullable
                      as String,
            buyerPhone: null == buyerPhone
                ? _value.buyerPhone
                : buyerPhone // ignore: cast_nullable_to_non_nullable
                      as String,
            buyerEmail: freezed == buyerEmail
                ? _value.buyerEmail
                : buyerEmail // ignore: cast_nullable_to_non_nullable
                      as String?,
            type: null == type
                ? _value.type
                : type // ignore: cast_nullable_to_non_nullable
                      as InquiryType,
            message: freezed == message
                ? _value.message
                : message // ignore: cast_nullable_to_non_nullable
                      as String?,
            scheduledVisitDate: freezed == scheduledVisitDate
                ? _value.scheduledVisitDate
                : scheduledVisitDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as InquiryStatus,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            respondedAt: freezed == respondedAt
                ? _value.respondedAt
                : respondedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            responseNotes: freezed == responseNotes
                ? _value.responseNotes
                : responseNotes // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$PropertyInquiryImplCopyWith<$Res>
    implements $PropertyInquiryCopyWith<$Res> {
  factory _$$PropertyInquiryImplCopyWith(
    _$PropertyInquiryImpl value,
    $Res Function(_$PropertyInquiryImpl) then,
  ) = __$$PropertyInquiryImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String buyerId,
    String buyerName,
    String buyerPhone,
    String? buyerEmail,
    InquiryType type,
    String? message,
    DateTime? scheduledVisitDate,
    InquiryStatus status,
    DateTime? createdAt,
    DateTime? respondedAt,
    String? responseNotes,
  });
}

/// @nodoc
class __$$PropertyInquiryImplCopyWithImpl<$Res>
    extends _$PropertyInquiryCopyWithImpl<$Res, _$PropertyInquiryImpl>
    implements _$$PropertyInquiryImplCopyWith<$Res> {
  __$$PropertyInquiryImplCopyWithImpl(
    _$PropertyInquiryImpl _value,
    $Res Function(_$PropertyInquiryImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of PropertyInquiry
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? buyerId = null,
    Object? buyerName = null,
    Object? buyerPhone = null,
    Object? buyerEmail = freezed,
    Object? type = null,
    Object? message = freezed,
    Object? scheduledVisitDate = freezed,
    Object? status = null,
    Object? createdAt = freezed,
    Object? respondedAt = freezed,
    Object? responseNotes = freezed,
  }) {
    return _then(
      _$PropertyInquiryImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        buyerId: null == buyerId
            ? _value.buyerId
            : buyerId // ignore: cast_nullable_to_non_nullable
                  as String,
        buyerName: null == buyerName
            ? _value.buyerName
            : buyerName // ignore: cast_nullable_to_non_nullable
                  as String,
        buyerPhone: null == buyerPhone
            ? _value.buyerPhone
            : buyerPhone // ignore: cast_nullable_to_non_nullable
                  as String,
        buyerEmail: freezed == buyerEmail
            ? _value.buyerEmail
            : buyerEmail // ignore: cast_nullable_to_non_nullable
                  as String?,
        type: null == type
            ? _value.type
            : type // ignore: cast_nullable_to_non_nullable
                  as InquiryType,
        message: freezed == message
            ? _value.message
            : message // ignore: cast_nullable_to_non_nullable
                  as String?,
        scheduledVisitDate: freezed == scheduledVisitDate
            ? _value.scheduledVisitDate
            : scheduledVisitDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as InquiryStatus,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        respondedAt: freezed == respondedAt
            ? _value.respondedAt
            : respondedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        responseNotes: freezed == responseNotes
            ? _value.responseNotes
            : responseNotes // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$PropertyInquiryImpl implements _PropertyInquiry {
  const _$PropertyInquiryImpl({
    required this.id,
    required this.buyerId,
    required this.buyerName,
    required this.buyerPhone,
    this.buyerEmail,
    required this.type,
    this.message,
    this.scheduledVisitDate,
    required this.status,
    this.createdAt,
    this.respondedAt,
    this.responseNotes,
  });

  factory _$PropertyInquiryImpl.fromJson(Map<String, dynamic> json) =>
      _$$PropertyInquiryImplFromJson(json);

  @override
  final String id;
  @override
  final String buyerId;
  @override
  final String buyerName;
  @override
  final String buyerPhone;
  @override
  final String? buyerEmail;
  @override
  final InquiryType type;
  // Call, WhatsApp, Email, Visit
  @override
  final String? message;
  @override
  final DateTime? scheduledVisitDate;
  @override
  final InquiryStatus status;
  // New, Contacted, InDiscussion, Negotiating, Closed
  @override
  final DateTime? createdAt;
  @override
  final DateTime? respondedAt;
  @override
  final String? responseNotes;

  @override
  String toString() {
    return 'PropertyInquiry(id: $id, buyerId: $buyerId, buyerName: $buyerName, buyerPhone: $buyerPhone, buyerEmail: $buyerEmail, type: $type, message: $message, scheduledVisitDate: $scheduledVisitDate, status: $status, createdAt: $createdAt, respondedAt: $respondedAt, responseNotes: $responseNotes)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$PropertyInquiryImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.buyerId, buyerId) || other.buyerId == buyerId) &&
            (identical(other.buyerName, buyerName) ||
                other.buyerName == buyerName) &&
            (identical(other.buyerPhone, buyerPhone) ||
                other.buyerPhone == buyerPhone) &&
            (identical(other.buyerEmail, buyerEmail) ||
                other.buyerEmail == buyerEmail) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.message, message) || other.message == message) &&
            (identical(other.scheduledVisitDate, scheduledVisitDate) ||
                other.scheduledVisitDate == scheduledVisitDate) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.respondedAt, respondedAt) ||
                other.respondedAt == respondedAt) &&
            (identical(other.responseNotes, responseNotes) ||
                other.responseNotes == responseNotes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    buyerId,
    buyerName,
    buyerPhone,
    buyerEmail,
    type,
    message,
    scheduledVisitDate,
    status,
    createdAt,
    respondedAt,
    responseNotes,
  );

  /// Create a copy of PropertyInquiry
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$PropertyInquiryImplCopyWith<_$PropertyInquiryImpl> get copyWith =>
      __$$PropertyInquiryImplCopyWithImpl<_$PropertyInquiryImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$PropertyInquiryImplToJson(this);
  }
}

abstract class _PropertyInquiry implements PropertyInquiry {
  const factory _PropertyInquiry({
    required final String id,
    required final String buyerId,
    required final String buyerName,
    required final String buyerPhone,
    final String? buyerEmail,
    required final InquiryType type,
    final String? message,
    final DateTime? scheduledVisitDate,
    required final InquiryStatus status,
    final DateTime? createdAt,
    final DateTime? respondedAt,
    final String? responseNotes,
  }) = _$PropertyInquiryImpl;

  factory _PropertyInquiry.fromJson(Map<String, dynamic> json) =
      _$PropertyInquiryImpl.fromJson;

  @override
  String get id;
  @override
  String get buyerId;
  @override
  String get buyerName;
  @override
  String get buyerPhone;
  @override
  String? get buyerEmail;
  @override
  InquiryType get type; // Call, WhatsApp, Email, Visit
  @override
  String? get message;
  @override
  DateTime? get scheduledVisitDate;
  @override
  InquiryStatus get status; // New, Contacted, InDiscussion, Negotiating, Closed
  @override
  DateTime? get createdAt;
  @override
  DateTime? get respondedAt;
  @override
  String? get responseNotes;

  /// Create a copy of PropertyInquiry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$PropertyInquiryImplCopyWith<_$PropertyInquiryImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
