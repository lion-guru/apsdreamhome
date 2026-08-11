// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'property_listing_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$PropertyListing {

 String get id; String get title; String get description; PropertyType get propertyType;// Plot, House, Flat, Shop, Farmhouse
 ListingPurpose get purpose;// Sell, Rent, Lease
// Owner Details
 String get ownerId; String get ownerName; String get ownerPhone; String get ownerEmail; OwnerType get ownerType;// Customer, Associate, Agent, Employee
// Location
 String get state; String get district; String get city; String get locality; String get address; GeoLocation get location; String get landmark;// Property Details
 double get areaSqft; String? get areaUnit;// sqft, acre, guntha
 double get expectedPrice; PriceNegotiable? get negotiable; String get priceType;// Fixed, Negotiable, Best Offer
// Features
 List<String> get images; List<String> get videos; List<String> get documents;// Legal papers, registry
 Map<String, dynamic>? get features;// Bedrooms, bathrooms, parking, etc.
// Status & Verification
 ListingStatus get status;// Pending, Verified, Active, Sold, Rejected
 String? get verifiedBy; DateTime? get verifiedAt; String? get rejectionReason; int? get verificationFee;// Admin charges for verification
// Statistics
 int get viewCount; int get inquiryCount; int get callCount; int get whatsappCount; DateTime? get lastInquiryAt;// Listing Plan (Monetization)
 ListingPlan? get listingPlan;// Free, Featured, Premium, Spotlight
 DateTime? get planExpiryDate; bool get isFeatured; bool get isPremium; bool get isSpotlight;// Lead Generation
 List<String> get interestedBuyers;// User IDs
 List<PropertyInquiry> get inquiries;// Admin Notes
 String? get adminNotes; List<String> get tags; DateTime get createdAt; DateTime get updatedAt;
/// Create a copy of PropertyListing
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PropertyListingCopyWith<PropertyListing> get copyWith => _$PropertyListingCopyWithImpl<PropertyListing>(this as PropertyListing, _$identity);

  /// Serializes this PropertyListing to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PropertyListing&&(identical(other.id, id) || other.id == id)&&(identical(other.title, title) || other.title == title)&&(identical(other.description, description) || other.description == description)&&(identical(other.propertyType, propertyType) || other.propertyType == propertyType)&&(identical(other.purpose, purpose) || other.purpose == purpose)&&(identical(other.ownerId, ownerId) || other.ownerId == ownerId)&&(identical(other.ownerName, ownerName) || other.ownerName == ownerName)&&(identical(other.ownerPhone, ownerPhone) || other.ownerPhone == ownerPhone)&&(identical(other.ownerEmail, ownerEmail) || other.ownerEmail == ownerEmail)&&(identical(other.ownerType, ownerType) || other.ownerType == ownerType)&&(identical(other.state, state) || other.state == state)&&(identical(other.district, district) || other.district == district)&&(identical(other.city, city) || other.city == city)&&(identical(other.locality, locality) || other.locality == locality)&&(identical(other.address, address) || other.address == address)&&(identical(other.location, location) || other.location == location)&&(identical(other.landmark, landmark) || other.landmark == landmark)&&(identical(other.areaSqft, areaSqft) || other.areaSqft == areaSqft)&&(identical(other.areaUnit, areaUnit) || other.areaUnit == areaUnit)&&(identical(other.expectedPrice, expectedPrice) || other.expectedPrice == expectedPrice)&&(identical(other.negotiable, negotiable) || other.negotiable == negotiable)&&(identical(other.priceType, priceType) || other.priceType == priceType)&&const DeepCollectionEquality().equals(other.images, images)&&const DeepCollectionEquality().equals(other.videos, videos)&&const DeepCollectionEquality().equals(other.documents, documents)&&const DeepCollectionEquality().equals(other.features, features)&&(identical(other.status, status) || other.status == status)&&(identical(other.verifiedBy, verifiedBy) || other.verifiedBy == verifiedBy)&&(identical(other.verifiedAt, verifiedAt) || other.verifiedAt == verifiedAt)&&(identical(other.rejectionReason, rejectionReason) || other.rejectionReason == rejectionReason)&&(identical(other.verificationFee, verificationFee) || other.verificationFee == verificationFee)&&(identical(other.viewCount, viewCount) || other.viewCount == viewCount)&&(identical(other.inquiryCount, inquiryCount) || other.inquiryCount == inquiryCount)&&(identical(other.callCount, callCount) || other.callCount == callCount)&&(identical(other.whatsappCount, whatsappCount) || other.whatsappCount == whatsappCount)&&(identical(other.lastInquiryAt, lastInquiryAt) || other.lastInquiryAt == lastInquiryAt)&&(identical(other.listingPlan, listingPlan) || other.listingPlan == listingPlan)&&(identical(other.planExpiryDate, planExpiryDate) || other.planExpiryDate == planExpiryDate)&&(identical(other.isFeatured, isFeatured) || other.isFeatured == isFeatured)&&(identical(other.isPremium, isPremium) || other.isPremium == isPremium)&&(identical(other.isSpotlight, isSpotlight) || other.isSpotlight == isSpotlight)&&const DeepCollectionEquality().equals(other.interestedBuyers, interestedBuyers)&&const DeepCollectionEquality().equals(other.inquiries, inquiries)&&(identical(other.adminNotes, adminNotes) || other.adminNotes == adminNotes)&&const DeepCollectionEquality().equals(other.tags, tags)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,title,description,propertyType,purpose,ownerId,ownerName,ownerPhone,ownerEmail,ownerType,state,district,city,locality,address,location,landmark,areaSqft,areaUnit,expectedPrice,negotiable,priceType,const DeepCollectionEquality().hash(images),const DeepCollectionEquality().hash(videos),const DeepCollectionEquality().hash(documents),const DeepCollectionEquality().hash(features),status,verifiedBy,verifiedAt,rejectionReason,verificationFee,viewCount,inquiryCount,callCount,whatsappCount,lastInquiryAt,listingPlan,planExpiryDate,isFeatured,isPremium,isSpotlight,const DeepCollectionEquality().hash(interestedBuyers),const DeepCollectionEquality().hash(inquiries),adminNotes,const DeepCollectionEquality().hash(tags),createdAt,updatedAt]);

@override
String toString() {
  return 'PropertyListing(id: $id, title: $title, description: $description, propertyType: $propertyType, purpose: $purpose, ownerId: $ownerId, ownerName: $ownerName, ownerPhone: $ownerPhone, ownerEmail: $ownerEmail, ownerType: $ownerType, state: $state, district: $district, city: $city, locality: $locality, address: $address, location: $location, landmark: $landmark, areaSqft: $areaSqft, areaUnit: $areaUnit, expectedPrice: $expectedPrice, negotiable: $negotiable, priceType: $priceType, images: $images, videos: $videos, documents: $documents, features: $features, status: $status, verifiedBy: $verifiedBy, verifiedAt: $verifiedAt, rejectionReason: $rejectionReason, verificationFee: $verificationFee, viewCount: $viewCount, inquiryCount: $inquiryCount, callCount: $callCount, whatsappCount: $whatsappCount, lastInquiryAt: $lastInquiryAt, listingPlan: $listingPlan, planExpiryDate: $planExpiryDate, isFeatured: $isFeatured, isPremium: $isPremium, isSpotlight: $isSpotlight, interestedBuyers: $interestedBuyers, inquiries: $inquiries, adminNotes: $adminNotes, tags: $tags, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class $PropertyListingCopyWith<$Res>  {
  factory $PropertyListingCopyWith(PropertyListing value, $Res Function(PropertyListing) _then) = _$PropertyListingCopyWithImpl;
@useResult
$Res call({
 String id, String title, String description, PropertyType propertyType, ListingPurpose purpose, String ownerId, String ownerName, String ownerPhone, String ownerEmail, OwnerType ownerType, String state, String district, String city, String locality, String address, GeoLocation location, String landmark, double areaSqft, String? areaUnit, double expectedPrice, PriceNegotiable? negotiable, String priceType, List<String> images, List<String> videos, List<String> documents, Map<String, dynamic>? features, ListingStatus status, String? verifiedBy, DateTime? verifiedAt, String? rejectionReason, int? verificationFee, int viewCount, int inquiryCount, int callCount, int whatsappCount, DateTime? lastInquiryAt, ListingPlan? listingPlan, DateTime? planExpiryDate, bool isFeatured, bool isPremium, bool isSpotlight, List<String> interestedBuyers, List<PropertyInquiry> inquiries, String? adminNotes, List<String> tags, DateTime createdAt, DateTime updatedAt
});




}
/// @nodoc
class _$PropertyListingCopyWithImpl<$Res>
    implements $PropertyListingCopyWith<$Res> {
  _$PropertyListingCopyWithImpl(this._self, this._then);

  final PropertyListing _self;
  final $Res Function(PropertyListing) _then;

/// Create a copy of PropertyListing
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? title = null,Object? description = null,Object? propertyType = null,Object? purpose = null,Object? ownerId = null,Object? ownerName = null,Object? ownerPhone = null,Object? ownerEmail = null,Object? ownerType = null,Object? state = null,Object? district = null,Object? city = null,Object? locality = null,Object? address = null,Object? location = null,Object? landmark = null,Object? areaSqft = null,Object? areaUnit = freezed,Object? expectedPrice = null,Object? negotiable = freezed,Object? priceType = null,Object? images = null,Object? videos = null,Object? documents = null,Object? features = freezed,Object? status = null,Object? verifiedBy = freezed,Object? verifiedAt = freezed,Object? rejectionReason = freezed,Object? verificationFee = freezed,Object? viewCount = null,Object? inquiryCount = null,Object? callCount = null,Object? whatsappCount = null,Object? lastInquiryAt = freezed,Object? listingPlan = freezed,Object? planExpiryDate = freezed,Object? isFeatured = null,Object? isPremium = null,Object? isSpotlight = null,Object? interestedBuyers = null,Object? inquiries = null,Object? adminNotes = freezed,Object? tags = null,Object? createdAt = null,Object? updatedAt = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,description: null == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String,propertyType: null == propertyType ? _self.propertyType : propertyType // ignore: cast_nullable_to_non_nullable
as PropertyType,purpose: null == purpose ? _self.purpose : purpose // ignore: cast_nullable_to_non_nullable
as ListingPurpose,ownerId: null == ownerId ? _self.ownerId : ownerId // ignore: cast_nullable_to_non_nullable
as String,ownerName: null == ownerName ? _self.ownerName : ownerName // ignore: cast_nullable_to_non_nullable
as String,ownerPhone: null == ownerPhone ? _self.ownerPhone : ownerPhone // ignore: cast_nullable_to_non_nullable
as String,ownerEmail: null == ownerEmail ? _self.ownerEmail : ownerEmail // ignore: cast_nullable_to_non_nullable
as String,ownerType: null == ownerType ? _self.ownerType : ownerType // ignore: cast_nullable_to_non_nullable
as OwnerType,state: null == state ? _self.state : state // ignore: cast_nullable_to_non_nullable
as String,district: null == district ? _self.district : district // ignore: cast_nullable_to_non_nullable
as String,city: null == city ? _self.city : city // ignore: cast_nullable_to_non_nullable
as String,locality: null == locality ? _self.locality : locality // ignore: cast_nullable_to_non_nullable
as String,address: null == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String,location: null == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation,landmark: null == landmark ? _self.landmark : landmark // ignore: cast_nullable_to_non_nullable
as String,areaSqft: null == areaSqft ? _self.areaSqft : areaSqft // ignore: cast_nullable_to_non_nullable
as double,areaUnit: freezed == areaUnit ? _self.areaUnit : areaUnit // ignore: cast_nullable_to_non_nullable
as String?,expectedPrice: null == expectedPrice ? _self.expectedPrice : expectedPrice // ignore: cast_nullable_to_non_nullable
as double,negotiable: freezed == negotiable ? _self.negotiable : negotiable // ignore: cast_nullable_to_non_nullable
as PriceNegotiable?,priceType: null == priceType ? _self.priceType : priceType // ignore: cast_nullable_to_non_nullable
as String,images: null == images ? _self.images : images // ignore: cast_nullable_to_non_nullable
as List<String>,videos: null == videos ? _self.videos : videos // ignore: cast_nullable_to_non_nullable
as List<String>,documents: null == documents ? _self.documents : documents // ignore: cast_nullable_to_non_nullable
as List<String>,features: freezed == features ? _self.features : features // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as ListingStatus,verifiedBy: freezed == verifiedBy ? _self.verifiedBy : verifiedBy // ignore: cast_nullable_to_non_nullable
as String?,verifiedAt: freezed == verifiedAt ? _self.verifiedAt : verifiedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,rejectionReason: freezed == rejectionReason ? _self.rejectionReason : rejectionReason // ignore: cast_nullable_to_non_nullable
as String?,verificationFee: freezed == verificationFee ? _self.verificationFee : verificationFee // ignore: cast_nullable_to_non_nullable
as int?,viewCount: null == viewCount ? _self.viewCount : viewCount // ignore: cast_nullable_to_non_nullable
as int,inquiryCount: null == inquiryCount ? _self.inquiryCount : inquiryCount // ignore: cast_nullable_to_non_nullable
as int,callCount: null == callCount ? _self.callCount : callCount // ignore: cast_nullable_to_non_nullable
as int,whatsappCount: null == whatsappCount ? _self.whatsappCount : whatsappCount // ignore: cast_nullable_to_non_nullable
as int,lastInquiryAt: freezed == lastInquiryAt ? _self.lastInquiryAt : lastInquiryAt // ignore: cast_nullable_to_non_nullable
as DateTime?,listingPlan: freezed == listingPlan ? _self.listingPlan : listingPlan // ignore: cast_nullable_to_non_nullable
as ListingPlan?,planExpiryDate: freezed == planExpiryDate ? _self.planExpiryDate : planExpiryDate // ignore: cast_nullable_to_non_nullable
as DateTime?,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,isPremium: null == isPremium ? _self.isPremium : isPremium // ignore: cast_nullable_to_non_nullable
as bool,isSpotlight: null == isSpotlight ? _self.isSpotlight : isSpotlight // ignore: cast_nullable_to_non_nullable
as bool,interestedBuyers: null == interestedBuyers ? _self.interestedBuyers : interestedBuyers // ignore: cast_nullable_to_non_nullable
as List<String>,inquiries: null == inquiries ? _self.inquiries : inquiries // ignore: cast_nullable_to_non_nullable
as List<PropertyInquiry>,adminNotes: freezed == adminNotes ? _self.adminNotes : adminNotes // ignore: cast_nullable_to_non_nullable
as String?,tags: null == tags ? _self.tags : tags // ignore: cast_nullable_to_non_nullable
as List<String>,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,updatedAt: null == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [PropertyListing].
extension PropertyListingPatterns on PropertyListing {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PropertyListing value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PropertyListing() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PropertyListing value)  $default,){
final _that = this;
switch (_that) {
case _PropertyListing():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PropertyListing value)?  $default,){
final _that = this;
switch (_that) {
case _PropertyListing() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String title,  String description,  PropertyType propertyType,  ListingPurpose purpose,  String ownerId,  String ownerName,  String ownerPhone,  String ownerEmail,  OwnerType ownerType,  String state,  String district,  String city,  String locality,  String address,  GeoLocation location,  String landmark,  double areaSqft,  String? areaUnit,  double expectedPrice,  PriceNegotiable? negotiable,  String priceType,  List<String> images,  List<String> videos,  List<String> documents,  Map<String, dynamic>? features,  ListingStatus status,  String? verifiedBy,  DateTime? verifiedAt,  String? rejectionReason,  int? verificationFee,  int viewCount,  int inquiryCount,  int callCount,  int whatsappCount,  DateTime? lastInquiryAt,  ListingPlan? listingPlan,  DateTime? planExpiryDate,  bool isFeatured,  bool isPremium,  bool isSpotlight,  List<String> interestedBuyers,  List<PropertyInquiry> inquiries,  String? adminNotes,  List<String> tags,  DateTime createdAt,  DateTime updatedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PropertyListing() when $default != null:
return $default(_that.id,_that.title,_that.description,_that.propertyType,_that.purpose,_that.ownerId,_that.ownerName,_that.ownerPhone,_that.ownerEmail,_that.ownerType,_that.state,_that.district,_that.city,_that.locality,_that.address,_that.location,_that.landmark,_that.areaSqft,_that.areaUnit,_that.expectedPrice,_that.negotiable,_that.priceType,_that.images,_that.videos,_that.documents,_that.features,_that.status,_that.verifiedBy,_that.verifiedAt,_that.rejectionReason,_that.verificationFee,_that.viewCount,_that.inquiryCount,_that.callCount,_that.whatsappCount,_that.lastInquiryAt,_that.listingPlan,_that.planExpiryDate,_that.isFeatured,_that.isPremium,_that.isSpotlight,_that.interestedBuyers,_that.inquiries,_that.adminNotes,_that.tags,_that.createdAt,_that.updatedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String title,  String description,  PropertyType propertyType,  ListingPurpose purpose,  String ownerId,  String ownerName,  String ownerPhone,  String ownerEmail,  OwnerType ownerType,  String state,  String district,  String city,  String locality,  String address,  GeoLocation location,  String landmark,  double areaSqft,  String? areaUnit,  double expectedPrice,  PriceNegotiable? negotiable,  String priceType,  List<String> images,  List<String> videos,  List<String> documents,  Map<String, dynamic>? features,  ListingStatus status,  String? verifiedBy,  DateTime? verifiedAt,  String? rejectionReason,  int? verificationFee,  int viewCount,  int inquiryCount,  int callCount,  int whatsappCount,  DateTime? lastInquiryAt,  ListingPlan? listingPlan,  DateTime? planExpiryDate,  bool isFeatured,  bool isPremium,  bool isSpotlight,  List<String> interestedBuyers,  List<PropertyInquiry> inquiries,  String? adminNotes,  List<String> tags,  DateTime createdAt,  DateTime updatedAt)  $default,) {final _that = this;
switch (_that) {
case _PropertyListing():
return $default(_that.id,_that.title,_that.description,_that.propertyType,_that.purpose,_that.ownerId,_that.ownerName,_that.ownerPhone,_that.ownerEmail,_that.ownerType,_that.state,_that.district,_that.city,_that.locality,_that.address,_that.location,_that.landmark,_that.areaSqft,_that.areaUnit,_that.expectedPrice,_that.negotiable,_that.priceType,_that.images,_that.videos,_that.documents,_that.features,_that.status,_that.verifiedBy,_that.verifiedAt,_that.rejectionReason,_that.verificationFee,_that.viewCount,_that.inquiryCount,_that.callCount,_that.whatsappCount,_that.lastInquiryAt,_that.listingPlan,_that.planExpiryDate,_that.isFeatured,_that.isPremium,_that.isSpotlight,_that.interestedBuyers,_that.inquiries,_that.adminNotes,_that.tags,_that.createdAt,_that.updatedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String title,  String description,  PropertyType propertyType,  ListingPurpose purpose,  String ownerId,  String ownerName,  String ownerPhone,  String ownerEmail,  OwnerType ownerType,  String state,  String district,  String city,  String locality,  String address,  GeoLocation location,  String landmark,  double areaSqft,  String? areaUnit,  double expectedPrice,  PriceNegotiable? negotiable,  String priceType,  List<String> images,  List<String> videos,  List<String> documents,  Map<String, dynamic>? features,  ListingStatus status,  String? verifiedBy,  DateTime? verifiedAt,  String? rejectionReason,  int? verificationFee,  int viewCount,  int inquiryCount,  int callCount,  int whatsappCount,  DateTime? lastInquiryAt,  ListingPlan? listingPlan,  DateTime? planExpiryDate,  bool isFeatured,  bool isPremium,  bool isSpotlight,  List<String> interestedBuyers,  List<PropertyInquiry> inquiries,  String? adminNotes,  List<String> tags,  DateTime createdAt,  DateTime updatedAt)?  $default,) {final _that = this;
switch (_that) {
case _PropertyListing() when $default != null:
return $default(_that.id,_that.title,_that.description,_that.propertyType,_that.purpose,_that.ownerId,_that.ownerName,_that.ownerPhone,_that.ownerEmail,_that.ownerType,_that.state,_that.district,_that.city,_that.locality,_that.address,_that.location,_that.landmark,_that.areaSqft,_that.areaUnit,_that.expectedPrice,_that.negotiable,_that.priceType,_that.images,_that.videos,_that.documents,_that.features,_that.status,_that.verifiedBy,_that.verifiedAt,_that.rejectionReason,_that.verificationFee,_that.viewCount,_that.inquiryCount,_that.callCount,_that.whatsappCount,_that.lastInquiryAt,_that.listingPlan,_that.planExpiryDate,_that.isFeatured,_that.isPremium,_that.isSpotlight,_that.interestedBuyers,_that.inquiries,_that.adminNotes,_that.tags,_that.createdAt,_that.updatedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PropertyListing extends PropertyListing {
  const _PropertyListing({this.id = '', this.title = '', this.description = '', required this.propertyType, required this.purpose, this.ownerId = '', this.ownerName = '', this.ownerPhone = '', this.ownerEmail = '', required this.ownerType, this.state = '', this.district = '', this.city = '', this.locality = '', this.address = '', required this.location, this.landmark = '', this.areaSqft = 0.0, this.areaUnit, this.expectedPrice = 0.0, this.negotiable, this.priceType = 'Fixed', final  List<String> images = const [], final  List<String> videos = const [], final  List<String> documents = const [], final  Map<String, dynamic>? features, required this.status, this.verifiedBy, this.verifiedAt, this.rejectionReason, this.verificationFee, this.viewCount = 0, this.inquiryCount = 0, this.callCount = 0, this.whatsappCount = 0, this.lastInquiryAt, this.listingPlan, this.planExpiryDate, this.isFeatured = false, this.isPremium = false, this.isSpotlight = false, final  List<String> interestedBuyers = const [], final  List<PropertyInquiry> inquiries = const [], this.adminNotes, final  List<String> tags = const [], required this.createdAt, required this.updatedAt}): _images = images,_videos = videos,_documents = documents,_features = features,_interestedBuyers = interestedBuyers,_inquiries = inquiries,_tags = tags,super._();
  factory _PropertyListing.fromJson(Map<String, dynamic> json) => _$PropertyListingFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String title;
@override@JsonKey() final  String description;
@override final  PropertyType propertyType;
// Plot, House, Flat, Shop, Farmhouse
@override final  ListingPurpose purpose;
// Sell, Rent, Lease
// Owner Details
@override@JsonKey() final  String ownerId;
@override@JsonKey() final  String ownerName;
@override@JsonKey() final  String ownerPhone;
@override@JsonKey() final  String ownerEmail;
@override final  OwnerType ownerType;
// Customer, Associate, Agent, Employee
// Location
@override@JsonKey() final  String state;
@override@JsonKey() final  String district;
@override@JsonKey() final  String city;
@override@JsonKey() final  String locality;
@override@JsonKey() final  String address;
@override final  GeoLocation location;
@override@JsonKey() final  String landmark;
// Property Details
@override@JsonKey() final  double areaSqft;
@override final  String? areaUnit;
// sqft, acre, guntha
@override@JsonKey() final  double expectedPrice;
@override final  PriceNegotiable? negotiable;
@override@JsonKey() final  String priceType;
// Fixed, Negotiable, Best Offer
// Features
 final  List<String> _images;
// Fixed, Negotiable, Best Offer
// Features
@override@JsonKey() List<String> get images {
  if (_images is EqualUnmodifiableListView) return _images;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_images);
}

 final  List<String> _videos;
@override@JsonKey() List<String> get videos {
  if (_videos is EqualUnmodifiableListView) return _videos;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_videos);
}

 final  List<String> _documents;
@override@JsonKey() List<String> get documents {
  if (_documents is EqualUnmodifiableListView) return _documents;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_documents);
}

// Legal papers, registry
 final  Map<String, dynamic>? _features;
// Legal papers, registry
@override Map<String, dynamic>? get features {
  final value = _features;
  if (value == null) return null;
  if (_features is EqualUnmodifiableMapView) return _features;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(value);
}

// Bedrooms, bathrooms, parking, etc.
// Status & Verification
@override final  ListingStatus status;
// Pending, Verified, Active, Sold, Rejected
@override final  String? verifiedBy;
@override final  DateTime? verifiedAt;
@override final  String? rejectionReason;
@override final  int? verificationFee;
// Admin charges for verification
// Statistics
@override@JsonKey() final  int viewCount;
@override@JsonKey() final  int inquiryCount;
@override@JsonKey() final  int callCount;
@override@JsonKey() final  int whatsappCount;
@override final  DateTime? lastInquiryAt;
// Listing Plan (Monetization)
@override final  ListingPlan? listingPlan;
// Free, Featured, Premium, Spotlight
@override final  DateTime? planExpiryDate;
@override@JsonKey() final  bool isFeatured;
@override@JsonKey() final  bool isPremium;
@override@JsonKey() final  bool isSpotlight;
// Lead Generation
 final  List<String> _interestedBuyers;
// Lead Generation
@override@JsonKey() List<String> get interestedBuyers {
  if (_interestedBuyers is EqualUnmodifiableListView) return _interestedBuyers;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_interestedBuyers);
}

// User IDs
 final  List<PropertyInquiry> _inquiries;
// User IDs
@override@JsonKey() List<PropertyInquiry> get inquiries {
  if (_inquiries is EqualUnmodifiableListView) return _inquiries;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_inquiries);
}

// Admin Notes
@override final  String? adminNotes;
 final  List<String> _tags;
@override@JsonKey() List<String> get tags {
  if (_tags is EqualUnmodifiableListView) return _tags;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_tags);
}

@override final  DateTime createdAt;
@override final  DateTime updatedAt;

/// Create a copy of PropertyListing
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PropertyListingCopyWith<_PropertyListing> get copyWith => __$PropertyListingCopyWithImpl<_PropertyListing>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PropertyListingToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _PropertyListing&&(identical(other.id, id) || other.id == id)&&(identical(other.title, title) || other.title == title)&&(identical(other.description, description) || other.description == description)&&(identical(other.propertyType, propertyType) || other.propertyType == propertyType)&&(identical(other.purpose, purpose) || other.purpose == purpose)&&(identical(other.ownerId, ownerId) || other.ownerId == ownerId)&&(identical(other.ownerName, ownerName) || other.ownerName == ownerName)&&(identical(other.ownerPhone, ownerPhone) || other.ownerPhone == ownerPhone)&&(identical(other.ownerEmail, ownerEmail) || other.ownerEmail == ownerEmail)&&(identical(other.ownerType, ownerType) || other.ownerType == ownerType)&&(identical(other.state, state) || other.state == state)&&(identical(other.district, district) || other.district == district)&&(identical(other.city, city) || other.city == city)&&(identical(other.locality, locality) || other.locality == locality)&&(identical(other.address, address) || other.address == address)&&(identical(other.location, location) || other.location == location)&&(identical(other.landmark, landmark) || other.landmark == landmark)&&(identical(other.areaSqft, areaSqft) || other.areaSqft == areaSqft)&&(identical(other.areaUnit, areaUnit) || other.areaUnit == areaUnit)&&(identical(other.expectedPrice, expectedPrice) || other.expectedPrice == expectedPrice)&&(identical(other.negotiable, negotiable) || other.negotiable == negotiable)&&(identical(other.priceType, priceType) || other.priceType == priceType)&&const DeepCollectionEquality().equals(other._images, _images)&&const DeepCollectionEquality().equals(other._videos, _videos)&&const DeepCollectionEquality().equals(other._documents, _documents)&&const DeepCollectionEquality().equals(other._features, _features)&&(identical(other.status, status) || other.status == status)&&(identical(other.verifiedBy, verifiedBy) || other.verifiedBy == verifiedBy)&&(identical(other.verifiedAt, verifiedAt) || other.verifiedAt == verifiedAt)&&(identical(other.rejectionReason, rejectionReason) || other.rejectionReason == rejectionReason)&&(identical(other.verificationFee, verificationFee) || other.verificationFee == verificationFee)&&(identical(other.viewCount, viewCount) || other.viewCount == viewCount)&&(identical(other.inquiryCount, inquiryCount) || other.inquiryCount == inquiryCount)&&(identical(other.callCount, callCount) || other.callCount == callCount)&&(identical(other.whatsappCount, whatsappCount) || other.whatsappCount == whatsappCount)&&(identical(other.lastInquiryAt, lastInquiryAt) || other.lastInquiryAt == lastInquiryAt)&&(identical(other.listingPlan, listingPlan) || other.listingPlan == listingPlan)&&(identical(other.planExpiryDate, planExpiryDate) || other.planExpiryDate == planExpiryDate)&&(identical(other.isFeatured, isFeatured) || other.isFeatured == isFeatured)&&(identical(other.isPremium, isPremium) || other.isPremium == isPremium)&&(identical(other.isSpotlight, isSpotlight) || other.isSpotlight == isSpotlight)&&const DeepCollectionEquality().equals(other._interestedBuyers, _interestedBuyers)&&const DeepCollectionEquality().equals(other._inquiries, _inquiries)&&(identical(other.adminNotes, adminNotes) || other.adminNotes == adminNotes)&&const DeepCollectionEquality().equals(other._tags, _tags)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,title,description,propertyType,purpose,ownerId,ownerName,ownerPhone,ownerEmail,ownerType,state,district,city,locality,address,location,landmark,areaSqft,areaUnit,expectedPrice,negotiable,priceType,const DeepCollectionEquality().hash(_images),const DeepCollectionEquality().hash(_videos),const DeepCollectionEquality().hash(_documents),const DeepCollectionEquality().hash(_features),status,verifiedBy,verifiedAt,rejectionReason,verificationFee,viewCount,inquiryCount,callCount,whatsappCount,lastInquiryAt,listingPlan,planExpiryDate,isFeatured,isPremium,isSpotlight,const DeepCollectionEquality().hash(_interestedBuyers),const DeepCollectionEquality().hash(_inquiries),adminNotes,const DeepCollectionEquality().hash(_tags),createdAt,updatedAt]);

@override
String toString() {
  return 'PropertyListing(id: $id, title: $title, description: $description, propertyType: $propertyType, purpose: $purpose, ownerId: $ownerId, ownerName: $ownerName, ownerPhone: $ownerPhone, ownerEmail: $ownerEmail, ownerType: $ownerType, state: $state, district: $district, city: $city, locality: $locality, address: $address, location: $location, landmark: $landmark, areaSqft: $areaSqft, areaUnit: $areaUnit, expectedPrice: $expectedPrice, negotiable: $negotiable, priceType: $priceType, images: $images, videos: $videos, documents: $documents, features: $features, status: $status, verifiedBy: $verifiedBy, verifiedAt: $verifiedAt, rejectionReason: $rejectionReason, verificationFee: $verificationFee, viewCount: $viewCount, inquiryCount: $inquiryCount, callCount: $callCount, whatsappCount: $whatsappCount, lastInquiryAt: $lastInquiryAt, listingPlan: $listingPlan, planExpiryDate: $planExpiryDate, isFeatured: $isFeatured, isPremium: $isPremium, isSpotlight: $isSpotlight, interestedBuyers: $interestedBuyers, inquiries: $inquiries, adminNotes: $adminNotes, tags: $tags, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class _$PropertyListingCopyWith<$Res> implements $PropertyListingCopyWith<$Res> {
  factory _$PropertyListingCopyWith(_PropertyListing value, $Res Function(_PropertyListing) _then) = __$PropertyListingCopyWithImpl;
@override @useResult
$Res call({
 String id, String title, String description, PropertyType propertyType, ListingPurpose purpose, String ownerId, String ownerName, String ownerPhone, String ownerEmail, OwnerType ownerType, String state, String district, String city, String locality, String address, GeoLocation location, String landmark, double areaSqft, String? areaUnit, double expectedPrice, PriceNegotiable? negotiable, String priceType, List<String> images, List<String> videos, List<String> documents, Map<String, dynamic>? features, ListingStatus status, String? verifiedBy, DateTime? verifiedAt, String? rejectionReason, int? verificationFee, int viewCount, int inquiryCount, int callCount, int whatsappCount, DateTime? lastInquiryAt, ListingPlan? listingPlan, DateTime? planExpiryDate, bool isFeatured, bool isPremium, bool isSpotlight, List<String> interestedBuyers, List<PropertyInquiry> inquiries, String? adminNotes, List<String> tags, DateTime createdAt, DateTime updatedAt
});




}
/// @nodoc
class __$PropertyListingCopyWithImpl<$Res>
    implements _$PropertyListingCopyWith<$Res> {
  __$PropertyListingCopyWithImpl(this._self, this._then);

  final _PropertyListing _self;
  final $Res Function(_PropertyListing) _then;

/// Create a copy of PropertyListing
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? title = null,Object? description = null,Object? propertyType = null,Object? purpose = null,Object? ownerId = null,Object? ownerName = null,Object? ownerPhone = null,Object? ownerEmail = null,Object? ownerType = null,Object? state = null,Object? district = null,Object? city = null,Object? locality = null,Object? address = null,Object? location = null,Object? landmark = null,Object? areaSqft = null,Object? areaUnit = freezed,Object? expectedPrice = null,Object? negotiable = freezed,Object? priceType = null,Object? images = null,Object? videos = null,Object? documents = null,Object? features = freezed,Object? status = null,Object? verifiedBy = freezed,Object? verifiedAt = freezed,Object? rejectionReason = freezed,Object? verificationFee = freezed,Object? viewCount = null,Object? inquiryCount = null,Object? callCount = null,Object? whatsappCount = null,Object? lastInquiryAt = freezed,Object? listingPlan = freezed,Object? planExpiryDate = freezed,Object? isFeatured = null,Object? isPremium = null,Object? isSpotlight = null,Object? interestedBuyers = null,Object? inquiries = null,Object? adminNotes = freezed,Object? tags = null,Object? createdAt = null,Object? updatedAt = null,}) {
  return _then(_PropertyListing(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,description: null == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String,propertyType: null == propertyType ? _self.propertyType : propertyType // ignore: cast_nullable_to_non_nullable
as PropertyType,purpose: null == purpose ? _self.purpose : purpose // ignore: cast_nullable_to_non_nullable
as ListingPurpose,ownerId: null == ownerId ? _self.ownerId : ownerId // ignore: cast_nullable_to_non_nullable
as String,ownerName: null == ownerName ? _self.ownerName : ownerName // ignore: cast_nullable_to_non_nullable
as String,ownerPhone: null == ownerPhone ? _self.ownerPhone : ownerPhone // ignore: cast_nullable_to_non_nullable
as String,ownerEmail: null == ownerEmail ? _self.ownerEmail : ownerEmail // ignore: cast_nullable_to_non_nullable
as String,ownerType: null == ownerType ? _self.ownerType : ownerType // ignore: cast_nullable_to_non_nullable
as OwnerType,state: null == state ? _self.state : state // ignore: cast_nullable_to_non_nullable
as String,district: null == district ? _self.district : district // ignore: cast_nullable_to_non_nullable
as String,city: null == city ? _self.city : city // ignore: cast_nullable_to_non_nullable
as String,locality: null == locality ? _self.locality : locality // ignore: cast_nullable_to_non_nullable
as String,address: null == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String,location: null == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation,landmark: null == landmark ? _self.landmark : landmark // ignore: cast_nullable_to_non_nullable
as String,areaSqft: null == areaSqft ? _self.areaSqft : areaSqft // ignore: cast_nullable_to_non_nullable
as double,areaUnit: freezed == areaUnit ? _self.areaUnit : areaUnit // ignore: cast_nullable_to_non_nullable
as String?,expectedPrice: null == expectedPrice ? _self.expectedPrice : expectedPrice // ignore: cast_nullable_to_non_nullable
as double,negotiable: freezed == negotiable ? _self.negotiable : negotiable // ignore: cast_nullable_to_non_nullable
as PriceNegotiable?,priceType: null == priceType ? _self.priceType : priceType // ignore: cast_nullable_to_non_nullable
as String,images: null == images ? _self._images : images // ignore: cast_nullable_to_non_nullable
as List<String>,videos: null == videos ? _self._videos : videos // ignore: cast_nullable_to_non_nullable
as List<String>,documents: null == documents ? _self._documents : documents // ignore: cast_nullable_to_non_nullable
as List<String>,features: freezed == features ? _self._features : features // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as ListingStatus,verifiedBy: freezed == verifiedBy ? _self.verifiedBy : verifiedBy // ignore: cast_nullable_to_non_nullable
as String?,verifiedAt: freezed == verifiedAt ? _self.verifiedAt : verifiedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,rejectionReason: freezed == rejectionReason ? _self.rejectionReason : rejectionReason // ignore: cast_nullable_to_non_nullable
as String?,verificationFee: freezed == verificationFee ? _self.verificationFee : verificationFee // ignore: cast_nullable_to_non_nullable
as int?,viewCount: null == viewCount ? _self.viewCount : viewCount // ignore: cast_nullable_to_non_nullable
as int,inquiryCount: null == inquiryCount ? _self.inquiryCount : inquiryCount // ignore: cast_nullable_to_non_nullable
as int,callCount: null == callCount ? _self.callCount : callCount // ignore: cast_nullable_to_non_nullable
as int,whatsappCount: null == whatsappCount ? _self.whatsappCount : whatsappCount // ignore: cast_nullable_to_non_nullable
as int,lastInquiryAt: freezed == lastInquiryAt ? _self.lastInquiryAt : lastInquiryAt // ignore: cast_nullable_to_non_nullable
as DateTime?,listingPlan: freezed == listingPlan ? _self.listingPlan : listingPlan // ignore: cast_nullable_to_non_nullable
as ListingPlan?,planExpiryDate: freezed == planExpiryDate ? _self.planExpiryDate : planExpiryDate // ignore: cast_nullable_to_non_nullable
as DateTime?,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,isPremium: null == isPremium ? _self.isPremium : isPremium // ignore: cast_nullable_to_non_nullable
as bool,isSpotlight: null == isSpotlight ? _self.isSpotlight : isSpotlight // ignore: cast_nullable_to_non_nullable
as bool,interestedBuyers: null == interestedBuyers ? _self._interestedBuyers : interestedBuyers // ignore: cast_nullable_to_non_nullable
as List<String>,inquiries: null == inquiries ? _self._inquiries : inquiries // ignore: cast_nullable_to_non_nullable
as List<PropertyInquiry>,adminNotes: freezed == adminNotes ? _self.adminNotes : adminNotes // ignore: cast_nullable_to_non_nullable
as String?,tags: null == tags ? _self._tags : tags // ignore: cast_nullable_to_non_nullable
as List<String>,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,updatedAt: null == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}


/// @nodoc
mixin _$PropertyInquiry {

 String get id; String get buyerId; String get buyerName; String get buyerPhone; String? get buyerEmail; InquiryType get type;// Call, WhatsApp, Email, Visit
 String? get message; DateTime? get scheduledVisitDate; InquiryStatus get status;// New, Contacted, InDiscussion, Negotiating, Closed
 DateTime? get createdAt; DateTime? get respondedAt; String? get responseNotes;
/// Create a copy of PropertyInquiry
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PropertyInquiryCopyWith<PropertyInquiry> get copyWith => _$PropertyInquiryCopyWithImpl<PropertyInquiry>(this as PropertyInquiry, _$identity);

  /// Serializes this PropertyInquiry to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PropertyInquiry&&(identical(other.id, id) || other.id == id)&&(identical(other.buyerId, buyerId) || other.buyerId == buyerId)&&(identical(other.buyerName, buyerName) || other.buyerName == buyerName)&&(identical(other.buyerPhone, buyerPhone) || other.buyerPhone == buyerPhone)&&(identical(other.buyerEmail, buyerEmail) || other.buyerEmail == buyerEmail)&&(identical(other.type, type) || other.type == type)&&(identical(other.message, message) || other.message == message)&&(identical(other.scheduledVisitDate, scheduledVisitDate) || other.scheduledVisitDate == scheduledVisitDate)&&(identical(other.status, status) || other.status == status)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.respondedAt, respondedAt) || other.respondedAt == respondedAt)&&(identical(other.responseNotes, responseNotes) || other.responseNotes == responseNotes));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,buyerId,buyerName,buyerPhone,buyerEmail,type,message,scheduledVisitDate,status,createdAt,respondedAt,responseNotes);

@override
String toString() {
  return 'PropertyInquiry(id: $id, buyerId: $buyerId, buyerName: $buyerName, buyerPhone: $buyerPhone, buyerEmail: $buyerEmail, type: $type, message: $message, scheduledVisitDate: $scheduledVisitDate, status: $status, createdAt: $createdAt, respondedAt: $respondedAt, responseNotes: $responseNotes)';
}


}

/// @nodoc
abstract mixin class $PropertyInquiryCopyWith<$Res>  {
  factory $PropertyInquiryCopyWith(PropertyInquiry value, $Res Function(PropertyInquiry) _then) = _$PropertyInquiryCopyWithImpl;
@useResult
$Res call({
 String id, String buyerId, String buyerName, String buyerPhone, String? buyerEmail, InquiryType type, String? message, DateTime? scheduledVisitDate, InquiryStatus status, DateTime? createdAt, DateTime? respondedAt, String? responseNotes
});




}
/// @nodoc
class _$PropertyInquiryCopyWithImpl<$Res>
    implements $PropertyInquiryCopyWith<$Res> {
  _$PropertyInquiryCopyWithImpl(this._self, this._then);

  final PropertyInquiry _self;
  final $Res Function(PropertyInquiry) _then;

/// Create a copy of PropertyInquiry
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? buyerId = null,Object? buyerName = null,Object? buyerPhone = null,Object? buyerEmail = freezed,Object? type = null,Object? message = freezed,Object? scheduledVisitDate = freezed,Object? status = null,Object? createdAt = freezed,Object? respondedAt = freezed,Object? responseNotes = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,buyerId: null == buyerId ? _self.buyerId : buyerId // ignore: cast_nullable_to_non_nullable
as String,buyerName: null == buyerName ? _self.buyerName : buyerName // ignore: cast_nullable_to_non_nullable
as String,buyerPhone: null == buyerPhone ? _self.buyerPhone : buyerPhone // ignore: cast_nullable_to_non_nullable
as String,buyerEmail: freezed == buyerEmail ? _self.buyerEmail : buyerEmail // ignore: cast_nullable_to_non_nullable
as String?,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as InquiryType,message: freezed == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String?,scheduledVisitDate: freezed == scheduledVisitDate ? _self.scheduledVisitDate : scheduledVisitDate // ignore: cast_nullable_to_non_nullable
as DateTime?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as InquiryStatus,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,respondedAt: freezed == respondedAt ? _self.respondedAt : respondedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,responseNotes: freezed == responseNotes ? _self.responseNotes : responseNotes // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [PropertyInquiry].
extension PropertyInquiryPatterns on PropertyInquiry {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PropertyInquiry value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PropertyInquiry() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PropertyInquiry value)  $default,){
final _that = this;
switch (_that) {
case _PropertyInquiry():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PropertyInquiry value)?  $default,){
final _that = this;
switch (_that) {
case _PropertyInquiry() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String buyerId,  String buyerName,  String buyerPhone,  String? buyerEmail,  InquiryType type,  String? message,  DateTime? scheduledVisitDate,  InquiryStatus status,  DateTime? createdAt,  DateTime? respondedAt,  String? responseNotes)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PropertyInquiry() when $default != null:
return $default(_that.id,_that.buyerId,_that.buyerName,_that.buyerPhone,_that.buyerEmail,_that.type,_that.message,_that.scheduledVisitDate,_that.status,_that.createdAt,_that.respondedAt,_that.responseNotes);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String buyerId,  String buyerName,  String buyerPhone,  String? buyerEmail,  InquiryType type,  String? message,  DateTime? scheduledVisitDate,  InquiryStatus status,  DateTime? createdAt,  DateTime? respondedAt,  String? responseNotes)  $default,) {final _that = this;
switch (_that) {
case _PropertyInquiry():
return $default(_that.id,_that.buyerId,_that.buyerName,_that.buyerPhone,_that.buyerEmail,_that.type,_that.message,_that.scheduledVisitDate,_that.status,_that.createdAt,_that.respondedAt,_that.responseNotes);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String buyerId,  String buyerName,  String buyerPhone,  String? buyerEmail,  InquiryType type,  String? message,  DateTime? scheduledVisitDate,  InquiryStatus status,  DateTime? createdAt,  DateTime? respondedAt,  String? responseNotes)?  $default,) {final _that = this;
switch (_that) {
case _PropertyInquiry() when $default != null:
return $default(_that.id,_that.buyerId,_that.buyerName,_that.buyerPhone,_that.buyerEmail,_that.type,_that.message,_that.scheduledVisitDate,_that.status,_that.createdAt,_that.respondedAt,_that.responseNotes);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PropertyInquiry implements PropertyInquiry {
  const _PropertyInquiry({this.id = '', this.buyerId = '', this.buyerName = '', this.buyerPhone = '', this.buyerEmail, required this.type, this.message, this.scheduledVisitDate, required this.status, this.createdAt, this.respondedAt, this.responseNotes});
  factory _PropertyInquiry.fromJson(Map<String, dynamic> json) => _$PropertyInquiryFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String buyerId;
@override@JsonKey() final  String buyerName;
@override@JsonKey() final  String buyerPhone;
@override final  String? buyerEmail;
@override final  InquiryType type;
// Call, WhatsApp, Email, Visit
@override final  String? message;
@override final  DateTime? scheduledVisitDate;
@override final  InquiryStatus status;
// New, Contacted, InDiscussion, Negotiating, Closed
@override final  DateTime? createdAt;
@override final  DateTime? respondedAt;
@override final  String? responseNotes;

/// Create a copy of PropertyInquiry
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PropertyInquiryCopyWith<_PropertyInquiry> get copyWith => __$PropertyInquiryCopyWithImpl<_PropertyInquiry>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PropertyInquiryToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _PropertyInquiry&&(identical(other.id, id) || other.id == id)&&(identical(other.buyerId, buyerId) || other.buyerId == buyerId)&&(identical(other.buyerName, buyerName) || other.buyerName == buyerName)&&(identical(other.buyerPhone, buyerPhone) || other.buyerPhone == buyerPhone)&&(identical(other.buyerEmail, buyerEmail) || other.buyerEmail == buyerEmail)&&(identical(other.type, type) || other.type == type)&&(identical(other.message, message) || other.message == message)&&(identical(other.scheduledVisitDate, scheduledVisitDate) || other.scheduledVisitDate == scheduledVisitDate)&&(identical(other.status, status) || other.status == status)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.respondedAt, respondedAt) || other.respondedAt == respondedAt)&&(identical(other.responseNotes, responseNotes) || other.responseNotes == responseNotes));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,buyerId,buyerName,buyerPhone,buyerEmail,type,message,scheduledVisitDate,status,createdAt,respondedAt,responseNotes);

@override
String toString() {
  return 'PropertyInquiry(id: $id, buyerId: $buyerId, buyerName: $buyerName, buyerPhone: $buyerPhone, buyerEmail: $buyerEmail, type: $type, message: $message, scheduledVisitDate: $scheduledVisitDate, status: $status, createdAt: $createdAt, respondedAt: $respondedAt, responseNotes: $responseNotes)';
}


}

/// @nodoc
abstract mixin class _$PropertyInquiryCopyWith<$Res> implements $PropertyInquiryCopyWith<$Res> {
  factory _$PropertyInquiryCopyWith(_PropertyInquiry value, $Res Function(_PropertyInquiry) _then) = __$PropertyInquiryCopyWithImpl;
@override @useResult
$Res call({
 String id, String buyerId, String buyerName, String buyerPhone, String? buyerEmail, InquiryType type, String? message, DateTime? scheduledVisitDate, InquiryStatus status, DateTime? createdAt, DateTime? respondedAt, String? responseNotes
});




}
/// @nodoc
class __$PropertyInquiryCopyWithImpl<$Res>
    implements _$PropertyInquiryCopyWith<$Res> {
  __$PropertyInquiryCopyWithImpl(this._self, this._then);

  final _PropertyInquiry _self;
  final $Res Function(_PropertyInquiry) _then;

/// Create a copy of PropertyInquiry
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? buyerId = null,Object? buyerName = null,Object? buyerPhone = null,Object? buyerEmail = freezed,Object? type = null,Object? message = freezed,Object? scheduledVisitDate = freezed,Object? status = null,Object? createdAt = freezed,Object? respondedAt = freezed,Object? responseNotes = freezed,}) {
  return _then(_PropertyInquiry(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,buyerId: null == buyerId ? _self.buyerId : buyerId // ignore: cast_nullable_to_non_nullable
as String,buyerName: null == buyerName ? _self.buyerName : buyerName // ignore: cast_nullable_to_non_nullable
as String,buyerPhone: null == buyerPhone ? _self.buyerPhone : buyerPhone // ignore: cast_nullable_to_non_nullable
as String,buyerEmail: freezed == buyerEmail ? _self.buyerEmail : buyerEmail // ignore: cast_nullable_to_non_nullable
as String?,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as InquiryType,message: freezed == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String?,scheduledVisitDate: freezed == scheduledVisitDate ? _self.scheduledVisitDate : scheduledVisitDate // ignore: cast_nullable_to_non_nullable
as DateTime?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as InquiryStatus,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,respondedAt: freezed == respondedAt ? _self.respondedAt : respondedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,responseNotes: freezed == responseNotes ? _self.responseNotes : responseNotes // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

// dart format on
