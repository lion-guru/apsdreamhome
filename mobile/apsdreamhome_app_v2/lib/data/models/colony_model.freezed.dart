// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'colony_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$ColonyModel {

 int get id; String get name; String? get slug; String? get description;// Plot statistics (API: total_plots, available_plots)
@JsonKey(name: 'total_plots') int get totalPlots;@JsonKey(name: 'available_plots') int get availablePlots;// Pricing (API: starting_price)
@JsonKey(name: 'starting_price') double get pricePerSqft;// Location (API: district_name, district_id)
@JsonKey(name: 'district_name') String get district;@JsonKey(name: 'district_id') int get districtId;// Images (API: image_path, image_url)
@JsonKey(name: 'image_path') String? get imagePath;@JsonKey(name: 'image_url') String? get imageUrl;// Status (API: is_active, is_featured)
@JsonKey(name: 'is_active') bool get isActive;@JsonKey(name: 'is_featured') bool get isFeatured;// Layout & Gallery (API: layout_image, gallery_images_data)
@JsonKey(name: 'layout_image') String? get layoutImage;@JsonKey(name: 'layout_image_url') String? get layoutImageUrl;@JsonKey(name: 'gallery_images_data') List<String>? get galleryImagesData;// Video & Virtual Tour (API: youtube_video_url, virtual_tour_url)
@JsonKey(name: 'youtube_video_url') String? get youtubeVideoUrl;@JsonKey(name: 'virtual_tour_url') String? get virtualTourUrl;// Documents (API: brochure_path, colony_documents)
@JsonKey(name: 'brochure_path') String? get brochurePath;@JsonKey(name: 'colony_documents') List<String>? get colonyDocuments;// Map & Location (API: map_link, latitude, longitude)
@JsonKey(name: 'map_link') String? get mapLinkApi; double? get latitude; double? get longitude;// Nearby Places (API: nearby_places)
@JsonKey(name: 'nearby_places') String? get nearbyPlacesRaw;// Compatibility fields (computed from API data)
 String get location; String get state; List<String>? get images; String? get masterPlanImage; String? get videoUrl;// Extended plot stats
 int get holdPlots; int get bookedPlots; int get soldPlots;// Extended pricing
 double? get tokenAmount; double? get bookingPercentage; Map<String, double>? get blockWisePricing;// Amenities
 List<String>? get amenities;// Dates
 String? get launchDate; String? get completionDate; String? get createdAt; String? get updatedAt;// Additional
 String? get createdBy; String? get reraNumber; String? get legalStatus; List<String>? get nearbyLandmarks; Map<String, dynamic>? get additionalInfo; String? get layoutMap; String? get rateList; String? get handbill;
/// Create a copy of ColonyModel
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ColonyModelCopyWith<ColonyModel> get copyWith => _$ColonyModelCopyWithImpl<ColonyModel>(this as ColonyModel, _$identity);

  /// Serializes this ColonyModel to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ColonyModel&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.description, description) || other.description == description)&&(identical(other.totalPlots, totalPlots) || other.totalPlots == totalPlots)&&(identical(other.availablePlots, availablePlots) || other.availablePlots == availablePlots)&&(identical(other.pricePerSqft, pricePerSqft) || other.pricePerSqft == pricePerSqft)&&(identical(other.district, district) || other.district == district)&&(identical(other.districtId, districtId) || other.districtId == districtId)&&(identical(other.imagePath, imagePath) || other.imagePath == imagePath)&&(identical(other.imageUrl, imageUrl) || other.imageUrl == imageUrl)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.isFeatured, isFeatured) || other.isFeatured == isFeatured)&&(identical(other.layoutImage, layoutImage) || other.layoutImage == layoutImage)&&(identical(other.layoutImageUrl, layoutImageUrl) || other.layoutImageUrl == layoutImageUrl)&&const DeepCollectionEquality().equals(other.galleryImagesData, galleryImagesData)&&(identical(other.youtubeVideoUrl, youtubeVideoUrl) || other.youtubeVideoUrl == youtubeVideoUrl)&&(identical(other.virtualTourUrl, virtualTourUrl) || other.virtualTourUrl == virtualTourUrl)&&(identical(other.brochurePath, brochurePath) || other.brochurePath == brochurePath)&&const DeepCollectionEquality().equals(other.colonyDocuments, colonyDocuments)&&(identical(other.mapLinkApi, mapLinkApi) || other.mapLinkApi == mapLinkApi)&&(identical(other.latitude, latitude) || other.latitude == latitude)&&(identical(other.longitude, longitude) || other.longitude == longitude)&&(identical(other.nearbyPlacesRaw, nearbyPlacesRaw) || other.nearbyPlacesRaw == nearbyPlacesRaw)&&(identical(other.location, location) || other.location == location)&&(identical(other.state, state) || other.state == state)&&const DeepCollectionEquality().equals(other.images, images)&&(identical(other.masterPlanImage, masterPlanImage) || other.masterPlanImage == masterPlanImage)&&(identical(other.videoUrl, videoUrl) || other.videoUrl == videoUrl)&&(identical(other.holdPlots, holdPlots) || other.holdPlots == holdPlots)&&(identical(other.bookedPlots, bookedPlots) || other.bookedPlots == bookedPlots)&&(identical(other.soldPlots, soldPlots) || other.soldPlots == soldPlots)&&(identical(other.tokenAmount, tokenAmount) || other.tokenAmount == tokenAmount)&&(identical(other.bookingPercentage, bookingPercentage) || other.bookingPercentage == bookingPercentage)&&const DeepCollectionEquality().equals(other.blockWisePricing, blockWisePricing)&&const DeepCollectionEquality().equals(other.amenities, amenities)&&(identical(other.launchDate, launchDate) || other.launchDate == launchDate)&&(identical(other.completionDate, completionDate) || other.completionDate == completionDate)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt)&&(identical(other.createdBy, createdBy) || other.createdBy == createdBy)&&(identical(other.reraNumber, reraNumber) || other.reraNumber == reraNumber)&&(identical(other.legalStatus, legalStatus) || other.legalStatus == legalStatus)&&const DeepCollectionEquality().equals(other.nearbyLandmarks, nearbyLandmarks)&&const DeepCollectionEquality().equals(other.additionalInfo, additionalInfo)&&(identical(other.layoutMap, layoutMap) || other.layoutMap == layoutMap)&&(identical(other.rateList, rateList) || other.rateList == rateList)&&(identical(other.handbill, handbill) || other.handbill == handbill));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,name,slug,description,totalPlots,availablePlots,pricePerSqft,district,districtId,imagePath,imageUrl,isActive,isFeatured,layoutImage,layoutImageUrl,const DeepCollectionEquality().hash(galleryImagesData),youtubeVideoUrl,virtualTourUrl,brochurePath,const DeepCollectionEquality().hash(colonyDocuments),mapLinkApi,latitude,longitude,nearbyPlacesRaw,location,state,const DeepCollectionEquality().hash(images),masterPlanImage,videoUrl,holdPlots,bookedPlots,soldPlots,tokenAmount,bookingPercentage,const DeepCollectionEquality().hash(blockWisePricing),const DeepCollectionEquality().hash(amenities),launchDate,completionDate,createdAt,updatedAt,createdBy,reraNumber,legalStatus,const DeepCollectionEquality().hash(nearbyLandmarks),const DeepCollectionEquality().hash(additionalInfo),layoutMap,rateList,handbill]);

@override
String toString() {
  return 'ColonyModel(id: $id, name: $name, slug: $slug, description: $description, totalPlots: $totalPlots, availablePlots: $availablePlots, pricePerSqft: $pricePerSqft, district: $district, districtId: $districtId, imagePath: $imagePath, imageUrl: $imageUrl, isActive: $isActive, isFeatured: $isFeatured, layoutImage: $layoutImage, layoutImageUrl: $layoutImageUrl, galleryImagesData: $galleryImagesData, youtubeVideoUrl: $youtubeVideoUrl, virtualTourUrl: $virtualTourUrl, brochurePath: $brochurePath, colonyDocuments: $colonyDocuments, mapLinkApi: $mapLinkApi, latitude: $latitude, longitude: $longitude, nearbyPlacesRaw: $nearbyPlacesRaw, location: $location, state: $state, images: $images, masterPlanImage: $masterPlanImage, videoUrl: $videoUrl, holdPlots: $holdPlots, bookedPlots: $bookedPlots, soldPlots: $soldPlots, tokenAmount: $tokenAmount, bookingPercentage: $bookingPercentage, blockWisePricing: $blockWisePricing, amenities: $amenities, launchDate: $launchDate, completionDate: $completionDate, createdAt: $createdAt, updatedAt: $updatedAt, createdBy: $createdBy, reraNumber: $reraNumber, legalStatus: $legalStatus, nearbyLandmarks: $nearbyLandmarks, additionalInfo: $additionalInfo, layoutMap: $layoutMap, rateList: $rateList, handbill: $handbill)';
}


}

/// @nodoc
abstract mixin class $ColonyModelCopyWith<$Res>  {
  factory $ColonyModelCopyWith(ColonyModel value, $Res Function(ColonyModel) _then) = _$ColonyModelCopyWithImpl;
@useResult
$Res call({
 int id, String name, String? slug, String? description,@JsonKey(name: 'total_plots') int totalPlots,@JsonKey(name: 'available_plots') int availablePlots,@JsonKey(name: 'starting_price') double pricePerSqft,@JsonKey(name: 'district_name') String district,@JsonKey(name: 'district_id') int districtId,@JsonKey(name: 'image_path') String? imagePath,@JsonKey(name: 'image_url') String? imageUrl,@JsonKey(name: 'is_active') bool isActive,@JsonKey(name: 'is_featured') bool isFeatured,@JsonKey(name: 'layout_image') String? layoutImage,@JsonKey(name: 'layout_image_url') String? layoutImageUrl,@JsonKey(name: 'gallery_images_data') List<String>? galleryImagesData,@JsonKey(name: 'youtube_video_url') String? youtubeVideoUrl,@JsonKey(name: 'virtual_tour_url') String? virtualTourUrl,@JsonKey(name: 'brochure_path') String? brochurePath,@JsonKey(name: 'colony_documents') List<String>? colonyDocuments,@JsonKey(name: 'map_link') String? mapLinkApi, double? latitude, double? longitude,@JsonKey(name: 'nearby_places') String? nearbyPlacesRaw, String location, String state, List<String>? images, String? masterPlanImage, String? videoUrl, int holdPlots, int bookedPlots, int soldPlots, double? tokenAmount, double? bookingPercentage, Map<String, double>? blockWisePricing, List<String>? amenities, String? launchDate, String? completionDate, String? createdAt, String? updatedAt, String? createdBy, String? reraNumber, String? legalStatus, List<String>? nearbyLandmarks, Map<String, dynamic>? additionalInfo, String? layoutMap, String? rateList, String? handbill
});




}
/// @nodoc
class _$ColonyModelCopyWithImpl<$Res>
    implements $ColonyModelCopyWith<$Res> {
  _$ColonyModelCopyWithImpl(this._self, this._then);

  final ColonyModel _self;
  final $Res Function(ColonyModel) _then;

/// Create a copy of ColonyModel
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? slug = freezed,Object? description = freezed,Object? totalPlots = null,Object? availablePlots = null,Object? pricePerSqft = null,Object? district = null,Object? districtId = null,Object? imagePath = freezed,Object? imageUrl = freezed,Object? isActive = null,Object? isFeatured = null,Object? layoutImage = freezed,Object? layoutImageUrl = freezed,Object? galleryImagesData = freezed,Object? youtubeVideoUrl = freezed,Object? virtualTourUrl = freezed,Object? brochurePath = freezed,Object? colonyDocuments = freezed,Object? mapLinkApi = freezed,Object? latitude = freezed,Object? longitude = freezed,Object? nearbyPlacesRaw = freezed,Object? location = null,Object? state = null,Object? images = freezed,Object? masterPlanImage = freezed,Object? videoUrl = freezed,Object? holdPlots = null,Object? bookedPlots = null,Object? soldPlots = null,Object? tokenAmount = freezed,Object? bookingPercentage = freezed,Object? blockWisePricing = freezed,Object? amenities = freezed,Object? launchDate = freezed,Object? completionDate = freezed,Object? createdAt = freezed,Object? updatedAt = freezed,Object? createdBy = freezed,Object? reraNumber = freezed,Object? legalStatus = freezed,Object? nearbyLandmarks = freezed,Object? additionalInfo = freezed,Object? layoutMap = freezed,Object? rateList = freezed,Object? handbill = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: freezed == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,totalPlots: null == totalPlots ? _self.totalPlots : totalPlots // ignore: cast_nullable_to_non_nullable
as int,availablePlots: null == availablePlots ? _self.availablePlots : availablePlots // ignore: cast_nullable_to_non_nullable
as int,pricePerSqft: null == pricePerSqft ? _self.pricePerSqft : pricePerSqft // ignore: cast_nullable_to_non_nullable
as double,district: null == district ? _self.district : district // ignore: cast_nullable_to_non_nullable
as String,districtId: null == districtId ? _self.districtId : districtId // ignore: cast_nullable_to_non_nullable
as int,imagePath: freezed == imagePath ? _self.imagePath : imagePath // ignore: cast_nullable_to_non_nullable
as String?,imageUrl: freezed == imageUrl ? _self.imageUrl : imageUrl // ignore: cast_nullable_to_non_nullable
as String?,isActive: null == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,layoutImage: freezed == layoutImage ? _self.layoutImage : layoutImage // ignore: cast_nullable_to_non_nullable
as String?,layoutImageUrl: freezed == layoutImageUrl ? _self.layoutImageUrl : layoutImageUrl // ignore: cast_nullable_to_non_nullable
as String?,galleryImagesData: freezed == galleryImagesData ? _self.galleryImagesData : galleryImagesData // ignore: cast_nullable_to_non_nullable
as List<String>?,youtubeVideoUrl: freezed == youtubeVideoUrl ? _self.youtubeVideoUrl : youtubeVideoUrl // ignore: cast_nullable_to_non_nullable
as String?,virtualTourUrl: freezed == virtualTourUrl ? _self.virtualTourUrl : virtualTourUrl // ignore: cast_nullable_to_non_nullable
as String?,brochurePath: freezed == brochurePath ? _self.brochurePath : brochurePath // ignore: cast_nullable_to_non_nullable
as String?,colonyDocuments: freezed == colonyDocuments ? _self.colonyDocuments : colonyDocuments // ignore: cast_nullable_to_non_nullable
as List<String>?,mapLinkApi: freezed == mapLinkApi ? _self.mapLinkApi : mapLinkApi // ignore: cast_nullable_to_non_nullable
as String?,latitude: freezed == latitude ? _self.latitude : latitude // ignore: cast_nullable_to_non_nullable
as double?,longitude: freezed == longitude ? _self.longitude : longitude // ignore: cast_nullable_to_non_nullable
as double?,nearbyPlacesRaw: freezed == nearbyPlacesRaw ? _self.nearbyPlacesRaw : nearbyPlacesRaw // ignore: cast_nullable_to_non_nullable
as String?,location: null == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as String,state: null == state ? _self.state : state // ignore: cast_nullable_to_non_nullable
as String,images: freezed == images ? _self.images : images // ignore: cast_nullable_to_non_nullable
as List<String>?,masterPlanImage: freezed == masterPlanImage ? _self.masterPlanImage : masterPlanImage // ignore: cast_nullable_to_non_nullable
as String?,videoUrl: freezed == videoUrl ? _self.videoUrl : videoUrl // ignore: cast_nullable_to_non_nullable
as String?,holdPlots: null == holdPlots ? _self.holdPlots : holdPlots // ignore: cast_nullable_to_non_nullable
as int,bookedPlots: null == bookedPlots ? _self.bookedPlots : bookedPlots // ignore: cast_nullable_to_non_nullable
as int,soldPlots: null == soldPlots ? _self.soldPlots : soldPlots // ignore: cast_nullable_to_non_nullable
as int,tokenAmount: freezed == tokenAmount ? _self.tokenAmount : tokenAmount // ignore: cast_nullable_to_non_nullable
as double?,bookingPercentage: freezed == bookingPercentage ? _self.bookingPercentage : bookingPercentage // ignore: cast_nullable_to_non_nullable
as double?,blockWisePricing: freezed == blockWisePricing ? _self.blockWisePricing : blockWisePricing // ignore: cast_nullable_to_non_nullable
as Map<String, double>?,amenities: freezed == amenities ? _self.amenities : amenities // ignore: cast_nullable_to_non_nullable
as List<String>?,launchDate: freezed == launchDate ? _self.launchDate : launchDate // ignore: cast_nullable_to_non_nullable
as String?,completionDate: freezed == completionDate ? _self.completionDate : completionDate // ignore: cast_nullable_to_non_nullable
as String?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as String?,updatedAt: freezed == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as String?,createdBy: freezed == createdBy ? _self.createdBy : createdBy // ignore: cast_nullable_to_non_nullable
as String?,reraNumber: freezed == reraNumber ? _self.reraNumber : reraNumber // ignore: cast_nullable_to_non_nullable
as String?,legalStatus: freezed == legalStatus ? _self.legalStatus : legalStatus // ignore: cast_nullable_to_non_nullable
as String?,nearbyLandmarks: freezed == nearbyLandmarks ? _self.nearbyLandmarks : nearbyLandmarks // ignore: cast_nullable_to_non_nullable
as List<String>?,additionalInfo: freezed == additionalInfo ? _self.additionalInfo : additionalInfo // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,layoutMap: freezed == layoutMap ? _self.layoutMap : layoutMap // ignore: cast_nullable_to_non_nullable
as String?,rateList: freezed == rateList ? _self.rateList : rateList // ignore: cast_nullable_to_non_nullable
as String?,handbill: freezed == handbill ? _self.handbill : handbill // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [ColonyModel].
extension ColonyModelPatterns on ColonyModel {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ColonyModel value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ColonyModel() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ColonyModel value)  $default,){
final _that = this;
switch (_that) {
case _ColonyModel():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ColonyModel value)?  $default,){
final _that = this;
switch (_that) {
case _ColonyModel() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String name,  String? slug,  String? description, @JsonKey(name: 'total_plots')  int totalPlots, @JsonKey(name: 'available_plots')  int availablePlots, @JsonKey(name: 'starting_price')  double pricePerSqft, @JsonKey(name: 'district_name')  String district, @JsonKey(name: 'district_id')  int districtId, @JsonKey(name: 'image_path')  String? imagePath, @JsonKey(name: 'image_url')  String? imageUrl, @JsonKey(name: 'is_active')  bool isActive, @JsonKey(name: 'is_featured')  bool isFeatured, @JsonKey(name: 'layout_image')  String? layoutImage, @JsonKey(name: 'layout_image_url')  String? layoutImageUrl, @JsonKey(name: 'gallery_images_data')  List<String>? galleryImagesData, @JsonKey(name: 'youtube_video_url')  String? youtubeVideoUrl, @JsonKey(name: 'virtual_tour_url')  String? virtualTourUrl, @JsonKey(name: 'brochure_path')  String? brochurePath, @JsonKey(name: 'colony_documents')  List<String>? colonyDocuments, @JsonKey(name: 'map_link')  String? mapLinkApi,  double? latitude,  double? longitude, @JsonKey(name: 'nearby_places')  String? nearbyPlacesRaw,  String location,  String state,  List<String>? images,  String? masterPlanImage,  String? videoUrl,  int holdPlots,  int bookedPlots,  int soldPlots,  double? tokenAmount,  double? bookingPercentage,  Map<String, double>? blockWisePricing,  List<String>? amenities,  String? launchDate,  String? completionDate,  String? createdAt,  String? updatedAt,  String? createdBy,  String? reraNumber,  String? legalStatus,  List<String>? nearbyLandmarks,  Map<String, dynamic>? additionalInfo,  String? layoutMap,  String? rateList,  String? handbill)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ColonyModel() when $default != null:
return $default(_that.id,_that.name,_that.slug,_that.description,_that.totalPlots,_that.availablePlots,_that.pricePerSqft,_that.district,_that.districtId,_that.imagePath,_that.imageUrl,_that.isActive,_that.isFeatured,_that.layoutImage,_that.layoutImageUrl,_that.galleryImagesData,_that.youtubeVideoUrl,_that.virtualTourUrl,_that.brochurePath,_that.colonyDocuments,_that.mapLinkApi,_that.latitude,_that.longitude,_that.nearbyPlacesRaw,_that.location,_that.state,_that.images,_that.masterPlanImage,_that.videoUrl,_that.holdPlots,_that.bookedPlots,_that.soldPlots,_that.tokenAmount,_that.bookingPercentage,_that.blockWisePricing,_that.amenities,_that.launchDate,_that.completionDate,_that.createdAt,_that.updatedAt,_that.createdBy,_that.reraNumber,_that.legalStatus,_that.nearbyLandmarks,_that.additionalInfo,_that.layoutMap,_that.rateList,_that.handbill);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String name,  String? slug,  String? description, @JsonKey(name: 'total_plots')  int totalPlots, @JsonKey(name: 'available_plots')  int availablePlots, @JsonKey(name: 'starting_price')  double pricePerSqft, @JsonKey(name: 'district_name')  String district, @JsonKey(name: 'district_id')  int districtId, @JsonKey(name: 'image_path')  String? imagePath, @JsonKey(name: 'image_url')  String? imageUrl, @JsonKey(name: 'is_active')  bool isActive, @JsonKey(name: 'is_featured')  bool isFeatured, @JsonKey(name: 'layout_image')  String? layoutImage, @JsonKey(name: 'layout_image_url')  String? layoutImageUrl, @JsonKey(name: 'gallery_images_data')  List<String>? galleryImagesData, @JsonKey(name: 'youtube_video_url')  String? youtubeVideoUrl, @JsonKey(name: 'virtual_tour_url')  String? virtualTourUrl, @JsonKey(name: 'brochure_path')  String? brochurePath, @JsonKey(name: 'colony_documents')  List<String>? colonyDocuments, @JsonKey(name: 'map_link')  String? mapLinkApi,  double? latitude,  double? longitude, @JsonKey(name: 'nearby_places')  String? nearbyPlacesRaw,  String location,  String state,  List<String>? images,  String? masterPlanImage,  String? videoUrl,  int holdPlots,  int bookedPlots,  int soldPlots,  double? tokenAmount,  double? bookingPercentage,  Map<String, double>? blockWisePricing,  List<String>? amenities,  String? launchDate,  String? completionDate,  String? createdAt,  String? updatedAt,  String? createdBy,  String? reraNumber,  String? legalStatus,  List<String>? nearbyLandmarks,  Map<String, dynamic>? additionalInfo,  String? layoutMap,  String? rateList,  String? handbill)  $default,) {final _that = this;
switch (_that) {
case _ColonyModel():
return $default(_that.id,_that.name,_that.slug,_that.description,_that.totalPlots,_that.availablePlots,_that.pricePerSqft,_that.district,_that.districtId,_that.imagePath,_that.imageUrl,_that.isActive,_that.isFeatured,_that.layoutImage,_that.layoutImageUrl,_that.galleryImagesData,_that.youtubeVideoUrl,_that.virtualTourUrl,_that.brochurePath,_that.colonyDocuments,_that.mapLinkApi,_that.latitude,_that.longitude,_that.nearbyPlacesRaw,_that.location,_that.state,_that.images,_that.masterPlanImage,_that.videoUrl,_that.holdPlots,_that.bookedPlots,_that.soldPlots,_that.tokenAmount,_that.bookingPercentage,_that.blockWisePricing,_that.amenities,_that.launchDate,_that.completionDate,_that.createdAt,_that.updatedAt,_that.createdBy,_that.reraNumber,_that.legalStatus,_that.nearbyLandmarks,_that.additionalInfo,_that.layoutMap,_that.rateList,_that.handbill);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String name,  String? slug,  String? description, @JsonKey(name: 'total_plots')  int totalPlots, @JsonKey(name: 'available_plots')  int availablePlots, @JsonKey(name: 'starting_price')  double pricePerSqft, @JsonKey(name: 'district_name')  String district, @JsonKey(name: 'district_id')  int districtId, @JsonKey(name: 'image_path')  String? imagePath, @JsonKey(name: 'image_url')  String? imageUrl, @JsonKey(name: 'is_active')  bool isActive, @JsonKey(name: 'is_featured')  bool isFeatured, @JsonKey(name: 'layout_image')  String? layoutImage, @JsonKey(name: 'layout_image_url')  String? layoutImageUrl, @JsonKey(name: 'gallery_images_data')  List<String>? galleryImagesData, @JsonKey(name: 'youtube_video_url')  String? youtubeVideoUrl, @JsonKey(name: 'virtual_tour_url')  String? virtualTourUrl, @JsonKey(name: 'brochure_path')  String? brochurePath, @JsonKey(name: 'colony_documents')  List<String>? colonyDocuments, @JsonKey(name: 'map_link')  String? mapLinkApi,  double? latitude,  double? longitude, @JsonKey(name: 'nearby_places')  String? nearbyPlacesRaw,  String location,  String state,  List<String>? images,  String? masterPlanImage,  String? videoUrl,  int holdPlots,  int bookedPlots,  int soldPlots,  double? tokenAmount,  double? bookingPercentage,  Map<String, double>? blockWisePricing,  List<String>? amenities,  String? launchDate,  String? completionDate,  String? createdAt,  String? updatedAt,  String? createdBy,  String? reraNumber,  String? legalStatus,  List<String>? nearbyLandmarks,  Map<String, dynamic>? additionalInfo,  String? layoutMap,  String? rateList,  String? handbill)?  $default,) {final _that = this;
switch (_that) {
case _ColonyModel() when $default != null:
return $default(_that.id,_that.name,_that.slug,_that.description,_that.totalPlots,_that.availablePlots,_that.pricePerSqft,_that.district,_that.districtId,_that.imagePath,_that.imageUrl,_that.isActive,_that.isFeatured,_that.layoutImage,_that.layoutImageUrl,_that.galleryImagesData,_that.youtubeVideoUrl,_that.virtualTourUrl,_that.brochurePath,_that.colonyDocuments,_that.mapLinkApi,_that.latitude,_that.longitude,_that.nearbyPlacesRaw,_that.location,_that.state,_that.images,_that.masterPlanImage,_that.videoUrl,_that.holdPlots,_that.bookedPlots,_that.soldPlots,_that.tokenAmount,_that.bookingPercentage,_that.blockWisePricing,_that.amenities,_that.launchDate,_that.completionDate,_that.createdAt,_that.updatedAt,_that.createdBy,_that.reraNumber,_that.legalStatus,_that.nearbyLandmarks,_that.additionalInfo,_that.layoutMap,_that.rateList,_that.handbill);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _ColonyModel extends ColonyModel {
  const _ColonyModel({this.id = 0, this.name = '', this.slug, this.description, @JsonKey(name: 'total_plots') this.totalPlots = 0, @JsonKey(name: 'available_plots') this.availablePlots = 0, @JsonKey(name: 'starting_price') this.pricePerSqft = 0.0, @JsonKey(name: 'district_name') this.district = '', @JsonKey(name: 'district_id') this.districtId = 0, @JsonKey(name: 'image_path') this.imagePath, @JsonKey(name: 'image_url') this.imageUrl, @JsonKey(name: 'is_active') this.isActive = true, @JsonKey(name: 'is_featured') this.isFeatured = false, @JsonKey(name: 'layout_image') this.layoutImage, @JsonKey(name: 'layout_image_url') this.layoutImageUrl, @JsonKey(name: 'gallery_images_data') final  List<String>? galleryImagesData, @JsonKey(name: 'youtube_video_url') this.youtubeVideoUrl, @JsonKey(name: 'virtual_tour_url') this.virtualTourUrl, @JsonKey(name: 'brochure_path') this.brochurePath, @JsonKey(name: 'colony_documents') final  List<String>? colonyDocuments, @JsonKey(name: 'map_link') this.mapLinkApi, this.latitude, this.longitude, @JsonKey(name: 'nearby_places') this.nearbyPlacesRaw, this.location = '', this.state = '', final  List<String>? images, this.masterPlanImage, this.videoUrl, this.holdPlots = 0, this.bookedPlots = 0, this.soldPlots = 0, this.tokenAmount, this.bookingPercentage, final  Map<String, double>? blockWisePricing, final  List<String>? amenities, this.launchDate, this.completionDate, this.createdAt, this.updatedAt, this.createdBy, this.reraNumber, this.legalStatus, final  List<String>? nearbyLandmarks, final  Map<String, dynamic>? additionalInfo, this.layoutMap, this.rateList, this.handbill}): _galleryImagesData = galleryImagesData,_colonyDocuments = colonyDocuments,_images = images,_blockWisePricing = blockWisePricing,_amenities = amenities,_nearbyLandmarks = nearbyLandmarks,_additionalInfo = additionalInfo,super._();
  factory _ColonyModel.fromJson(Map<String, dynamic> json) => _$ColonyModelFromJson(json);

@override@JsonKey() final  int id;
@override@JsonKey() final  String name;
@override final  String? slug;
@override final  String? description;
// Plot statistics (API: total_plots, available_plots)
@override@JsonKey(name: 'total_plots') final  int totalPlots;
@override@JsonKey(name: 'available_plots') final  int availablePlots;
// Pricing (API: starting_price)
@override@JsonKey(name: 'starting_price') final  double pricePerSqft;
// Location (API: district_name, district_id)
@override@JsonKey(name: 'district_name') final  String district;
@override@JsonKey(name: 'district_id') final  int districtId;
// Images (API: image_path, image_url)
@override@JsonKey(name: 'image_path') final  String? imagePath;
@override@JsonKey(name: 'image_url') final  String? imageUrl;
// Status (API: is_active, is_featured)
@override@JsonKey(name: 'is_active') final  bool isActive;
@override@JsonKey(name: 'is_featured') final  bool isFeatured;
// Layout & Gallery (API: layout_image, gallery_images_data)
@override@JsonKey(name: 'layout_image') final  String? layoutImage;
@override@JsonKey(name: 'layout_image_url') final  String? layoutImageUrl;
 final  List<String>? _galleryImagesData;
@override@JsonKey(name: 'gallery_images_data') List<String>? get galleryImagesData {
  final value = _galleryImagesData;
  if (value == null) return null;
  if (_galleryImagesData is EqualUnmodifiableListView) return _galleryImagesData;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

// Video & Virtual Tour (API: youtube_video_url, virtual_tour_url)
@override@JsonKey(name: 'youtube_video_url') final  String? youtubeVideoUrl;
@override@JsonKey(name: 'virtual_tour_url') final  String? virtualTourUrl;
// Documents (API: brochure_path, colony_documents)
@override@JsonKey(name: 'brochure_path') final  String? brochurePath;
 final  List<String>? _colonyDocuments;
@override@JsonKey(name: 'colony_documents') List<String>? get colonyDocuments {
  final value = _colonyDocuments;
  if (value == null) return null;
  if (_colonyDocuments is EqualUnmodifiableListView) return _colonyDocuments;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

// Map & Location (API: map_link, latitude, longitude)
@override@JsonKey(name: 'map_link') final  String? mapLinkApi;
@override final  double? latitude;
@override final  double? longitude;
// Nearby Places (API: nearby_places)
@override@JsonKey(name: 'nearby_places') final  String? nearbyPlacesRaw;
// Compatibility fields (computed from API data)
@override@JsonKey() final  String location;
@override@JsonKey() final  String state;
 final  List<String>? _images;
@override List<String>? get images {
  final value = _images;
  if (value == null) return null;
  if (_images is EqualUnmodifiableListView) return _images;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

@override final  String? masterPlanImage;
@override final  String? videoUrl;
// Extended plot stats
@override@JsonKey() final  int holdPlots;
@override@JsonKey() final  int bookedPlots;
@override@JsonKey() final  int soldPlots;
// Extended pricing
@override final  double? tokenAmount;
@override final  double? bookingPercentage;
 final  Map<String, double>? _blockWisePricing;
@override Map<String, double>? get blockWisePricing {
  final value = _blockWisePricing;
  if (value == null) return null;
  if (_blockWisePricing is EqualUnmodifiableMapView) return _blockWisePricing;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(value);
}

// Amenities
 final  List<String>? _amenities;
// Amenities
@override List<String>? get amenities {
  final value = _amenities;
  if (value == null) return null;
  if (_amenities is EqualUnmodifiableListView) return _amenities;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

// Dates
@override final  String? launchDate;
@override final  String? completionDate;
@override final  String? createdAt;
@override final  String? updatedAt;
// Additional
@override final  String? createdBy;
@override final  String? reraNumber;
@override final  String? legalStatus;
 final  List<String>? _nearbyLandmarks;
@override List<String>? get nearbyLandmarks {
  final value = _nearbyLandmarks;
  if (value == null) return null;
  if (_nearbyLandmarks is EqualUnmodifiableListView) return _nearbyLandmarks;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  Map<String, dynamic>? _additionalInfo;
@override Map<String, dynamic>? get additionalInfo {
  final value = _additionalInfo;
  if (value == null) return null;
  if (_additionalInfo is EqualUnmodifiableMapView) return _additionalInfo;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(value);
}

@override final  String? layoutMap;
@override final  String? rateList;
@override final  String? handbill;

/// Create a copy of ColonyModel
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ColonyModelCopyWith<_ColonyModel> get copyWith => __$ColonyModelCopyWithImpl<_ColonyModel>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$ColonyModelToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _ColonyModel&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.description, description) || other.description == description)&&(identical(other.totalPlots, totalPlots) || other.totalPlots == totalPlots)&&(identical(other.availablePlots, availablePlots) || other.availablePlots == availablePlots)&&(identical(other.pricePerSqft, pricePerSqft) || other.pricePerSqft == pricePerSqft)&&(identical(other.district, district) || other.district == district)&&(identical(other.districtId, districtId) || other.districtId == districtId)&&(identical(other.imagePath, imagePath) || other.imagePath == imagePath)&&(identical(other.imageUrl, imageUrl) || other.imageUrl == imageUrl)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.isFeatured, isFeatured) || other.isFeatured == isFeatured)&&(identical(other.layoutImage, layoutImage) || other.layoutImage == layoutImage)&&(identical(other.layoutImageUrl, layoutImageUrl) || other.layoutImageUrl == layoutImageUrl)&&const DeepCollectionEquality().equals(other._galleryImagesData, _galleryImagesData)&&(identical(other.youtubeVideoUrl, youtubeVideoUrl) || other.youtubeVideoUrl == youtubeVideoUrl)&&(identical(other.virtualTourUrl, virtualTourUrl) || other.virtualTourUrl == virtualTourUrl)&&(identical(other.brochurePath, brochurePath) || other.brochurePath == brochurePath)&&const DeepCollectionEquality().equals(other._colonyDocuments, _colonyDocuments)&&(identical(other.mapLinkApi, mapLinkApi) || other.mapLinkApi == mapLinkApi)&&(identical(other.latitude, latitude) || other.latitude == latitude)&&(identical(other.longitude, longitude) || other.longitude == longitude)&&(identical(other.nearbyPlacesRaw, nearbyPlacesRaw) || other.nearbyPlacesRaw == nearbyPlacesRaw)&&(identical(other.location, location) || other.location == location)&&(identical(other.state, state) || other.state == state)&&const DeepCollectionEquality().equals(other._images, _images)&&(identical(other.masterPlanImage, masterPlanImage) || other.masterPlanImage == masterPlanImage)&&(identical(other.videoUrl, videoUrl) || other.videoUrl == videoUrl)&&(identical(other.holdPlots, holdPlots) || other.holdPlots == holdPlots)&&(identical(other.bookedPlots, bookedPlots) || other.bookedPlots == bookedPlots)&&(identical(other.soldPlots, soldPlots) || other.soldPlots == soldPlots)&&(identical(other.tokenAmount, tokenAmount) || other.tokenAmount == tokenAmount)&&(identical(other.bookingPercentage, bookingPercentage) || other.bookingPercentage == bookingPercentage)&&const DeepCollectionEquality().equals(other._blockWisePricing, _blockWisePricing)&&const DeepCollectionEquality().equals(other._amenities, _amenities)&&(identical(other.launchDate, launchDate) || other.launchDate == launchDate)&&(identical(other.completionDate, completionDate) || other.completionDate == completionDate)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt)&&(identical(other.createdBy, createdBy) || other.createdBy == createdBy)&&(identical(other.reraNumber, reraNumber) || other.reraNumber == reraNumber)&&(identical(other.legalStatus, legalStatus) || other.legalStatus == legalStatus)&&const DeepCollectionEquality().equals(other._nearbyLandmarks, _nearbyLandmarks)&&const DeepCollectionEquality().equals(other._additionalInfo, _additionalInfo)&&(identical(other.layoutMap, layoutMap) || other.layoutMap == layoutMap)&&(identical(other.rateList, rateList) || other.rateList == rateList)&&(identical(other.handbill, handbill) || other.handbill == handbill));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,name,slug,description,totalPlots,availablePlots,pricePerSqft,district,districtId,imagePath,imageUrl,isActive,isFeatured,layoutImage,layoutImageUrl,const DeepCollectionEquality().hash(_galleryImagesData),youtubeVideoUrl,virtualTourUrl,brochurePath,const DeepCollectionEquality().hash(_colonyDocuments),mapLinkApi,latitude,longitude,nearbyPlacesRaw,location,state,const DeepCollectionEquality().hash(_images),masterPlanImage,videoUrl,holdPlots,bookedPlots,soldPlots,tokenAmount,bookingPercentage,const DeepCollectionEquality().hash(_blockWisePricing),const DeepCollectionEquality().hash(_amenities),launchDate,completionDate,createdAt,updatedAt,createdBy,reraNumber,legalStatus,const DeepCollectionEquality().hash(_nearbyLandmarks),const DeepCollectionEquality().hash(_additionalInfo),layoutMap,rateList,handbill]);

@override
String toString() {
  return 'ColonyModel(id: $id, name: $name, slug: $slug, description: $description, totalPlots: $totalPlots, availablePlots: $availablePlots, pricePerSqft: $pricePerSqft, district: $district, districtId: $districtId, imagePath: $imagePath, imageUrl: $imageUrl, isActive: $isActive, isFeatured: $isFeatured, layoutImage: $layoutImage, layoutImageUrl: $layoutImageUrl, galleryImagesData: $galleryImagesData, youtubeVideoUrl: $youtubeVideoUrl, virtualTourUrl: $virtualTourUrl, brochurePath: $brochurePath, colonyDocuments: $colonyDocuments, mapLinkApi: $mapLinkApi, latitude: $latitude, longitude: $longitude, nearbyPlacesRaw: $nearbyPlacesRaw, location: $location, state: $state, images: $images, masterPlanImage: $masterPlanImage, videoUrl: $videoUrl, holdPlots: $holdPlots, bookedPlots: $bookedPlots, soldPlots: $soldPlots, tokenAmount: $tokenAmount, bookingPercentage: $bookingPercentage, blockWisePricing: $blockWisePricing, amenities: $amenities, launchDate: $launchDate, completionDate: $completionDate, createdAt: $createdAt, updatedAt: $updatedAt, createdBy: $createdBy, reraNumber: $reraNumber, legalStatus: $legalStatus, nearbyLandmarks: $nearbyLandmarks, additionalInfo: $additionalInfo, layoutMap: $layoutMap, rateList: $rateList, handbill: $handbill)';
}


}

/// @nodoc
abstract mixin class _$ColonyModelCopyWith<$Res> implements $ColonyModelCopyWith<$Res> {
  factory _$ColonyModelCopyWith(_ColonyModel value, $Res Function(_ColonyModel) _then) = __$ColonyModelCopyWithImpl;
@override @useResult
$Res call({
 int id, String name, String? slug, String? description,@JsonKey(name: 'total_plots') int totalPlots,@JsonKey(name: 'available_plots') int availablePlots,@JsonKey(name: 'starting_price') double pricePerSqft,@JsonKey(name: 'district_name') String district,@JsonKey(name: 'district_id') int districtId,@JsonKey(name: 'image_path') String? imagePath,@JsonKey(name: 'image_url') String? imageUrl,@JsonKey(name: 'is_active') bool isActive,@JsonKey(name: 'is_featured') bool isFeatured,@JsonKey(name: 'layout_image') String? layoutImage,@JsonKey(name: 'layout_image_url') String? layoutImageUrl,@JsonKey(name: 'gallery_images_data') List<String>? galleryImagesData,@JsonKey(name: 'youtube_video_url') String? youtubeVideoUrl,@JsonKey(name: 'virtual_tour_url') String? virtualTourUrl,@JsonKey(name: 'brochure_path') String? brochurePath,@JsonKey(name: 'colony_documents') List<String>? colonyDocuments,@JsonKey(name: 'map_link') String? mapLinkApi, double? latitude, double? longitude,@JsonKey(name: 'nearby_places') String? nearbyPlacesRaw, String location, String state, List<String>? images, String? masterPlanImage, String? videoUrl, int holdPlots, int bookedPlots, int soldPlots, double? tokenAmount, double? bookingPercentage, Map<String, double>? blockWisePricing, List<String>? amenities, String? launchDate, String? completionDate, String? createdAt, String? updatedAt, String? createdBy, String? reraNumber, String? legalStatus, List<String>? nearbyLandmarks, Map<String, dynamic>? additionalInfo, String? layoutMap, String? rateList, String? handbill
});




}
/// @nodoc
class __$ColonyModelCopyWithImpl<$Res>
    implements _$ColonyModelCopyWith<$Res> {
  __$ColonyModelCopyWithImpl(this._self, this._then);

  final _ColonyModel _self;
  final $Res Function(_ColonyModel) _then;

/// Create a copy of ColonyModel
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? slug = freezed,Object? description = freezed,Object? totalPlots = null,Object? availablePlots = null,Object? pricePerSqft = null,Object? district = null,Object? districtId = null,Object? imagePath = freezed,Object? imageUrl = freezed,Object? isActive = null,Object? isFeatured = null,Object? layoutImage = freezed,Object? layoutImageUrl = freezed,Object? galleryImagesData = freezed,Object? youtubeVideoUrl = freezed,Object? virtualTourUrl = freezed,Object? brochurePath = freezed,Object? colonyDocuments = freezed,Object? mapLinkApi = freezed,Object? latitude = freezed,Object? longitude = freezed,Object? nearbyPlacesRaw = freezed,Object? location = null,Object? state = null,Object? images = freezed,Object? masterPlanImage = freezed,Object? videoUrl = freezed,Object? holdPlots = null,Object? bookedPlots = null,Object? soldPlots = null,Object? tokenAmount = freezed,Object? bookingPercentage = freezed,Object? blockWisePricing = freezed,Object? amenities = freezed,Object? launchDate = freezed,Object? completionDate = freezed,Object? createdAt = freezed,Object? updatedAt = freezed,Object? createdBy = freezed,Object? reraNumber = freezed,Object? legalStatus = freezed,Object? nearbyLandmarks = freezed,Object? additionalInfo = freezed,Object? layoutMap = freezed,Object? rateList = freezed,Object? handbill = freezed,}) {
  return _then(_ColonyModel(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: freezed == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,totalPlots: null == totalPlots ? _self.totalPlots : totalPlots // ignore: cast_nullable_to_non_nullable
as int,availablePlots: null == availablePlots ? _self.availablePlots : availablePlots // ignore: cast_nullable_to_non_nullable
as int,pricePerSqft: null == pricePerSqft ? _self.pricePerSqft : pricePerSqft // ignore: cast_nullable_to_non_nullable
as double,district: null == district ? _self.district : district // ignore: cast_nullable_to_non_nullable
as String,districtId: null == districtId ? _self.districtId : districtId // ignore: cast_nullable_to_non_nullable
as int,imagePath: freezed == imagePath ? _self.imagePath : imagePath // ignore: cast_nullable_to_non_nullable
as String?,imageUrl: freezed == imageUrl ? _self.imageUrl : imageUrl // ignore: cast_nullable_to_non_nullable
as String?,isActive: null == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,layoutImage: freezed == layoutImage ? _self.layoutImage : layoutImage // ignore: cast_nullable_to_non_nullable
as String?,layoutImageUrl: freezed == layoutImageUrl ? _self.layoutImageUrl : layoutImageUrl // ignore: cast_nullable_to_non_nullable
as String?,galleryImagesData: freezed == galleryImagesData ? _self._galleryImagesData : galleryImagesData // ignore: cast_nullable_to_non_nullable
as List<String>?,youtubeVideoUrl: freezed == youtubeVideoUrl ? _self.youtubeVideoUrl : youtubeVideoUrl // ignore: cast_nullable_to_non_nullable
as String?,virtualTourUrl: freezed == virtualTourUrl ? _self.virtualTourUrl : virtualTourUrl // ignore: cast_nullable_to_non_nullable
as String?,brochurePath: freezed == brochurePath ? _self.brochurePath : brochurePath // ignore: cast_nullable_to_non_nullable
as String?,colonyDocuments: freezed == colonyDocuments ? _self._colonyDocuments : colonyDocuments // ignore: cast_nullable_to_non_nullable
as List<String>?,mapLinkApi: freezed == mapLinkApi ? _self.mapLinkApi : mapLinkApi // ignore: cast_nullable_to_non_nullable
as String?,latitude: freezed == latitude ? _self.latitude : latitude // ignore: cast_nullable_to_non_nullable
as double?,longitude: freezed == longitude ? _self.longitude : longitude // ignore: cast_nullable_to_non_nullable
as double?,nearbyPlacesRaw: freezed == nearbyPlacesRaw ? _self.nearbyPlacesRaw : nearbyPlacesRaw // ignore: cast_nullable_to_non_nullable
as String?,location: null == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as String,state: null == state ? _self.state : state // ignore: cast_nullable_to_non_nullable
as String,images: freezed == images ? _self._images : images // ignore: cast_nullable_to_non_nullable
as List<String>?,masterPlanImage: freezed == masterPlanImage ? _self.masterPlanImage : masterPlanImage // ignore: cast_nullable_to_non_nullable
as String?,videoUrl: freezed == videoUrl ? _self.videoUrl : videoUrl // ignore: cast_nullable_to_non_nullable
as String?,holdPlots: null == holdPlots ? _self.holdPlots : holdPlots // ignore: cast_nullable_to_non_nullable
as int,bookedPlots: null == bookedPlots ? _self.bookedPlots : bookedPlots // ignore: cast_nullable_to_non_nullable
as int,soldPlots: null == soldPlots ? _self.soldPlots : soldPlots // ignore: cast_nullable_to_non_nullable
as int,tokenAmount: freezed == tokenAmount ? _self.tokenAmount : tokenAmount // ignore: cast_nullable_to_non_nullable
as double?,bookingPercentage: freezed == bookingPercentage ? _self.bookingPercentage : bookingPercentage // ignore: cast_nullable_to_non_nullable
as double?,blockWisePricing: freezed == blockWisePricing ? _self._blockWisePricing : blockWisePricing // ignore: cast_nullable_to_non_nullable
as Map<String, double>?,amenities: freezed == amenities ? _self._amenities : amenities // ignore: cast_nullable_to_non_nullable
as List<String>?,launchDate: freezed == launchDate ? _self.launchDate : launchDate // ignore: cast_nullable_to_non_nullable
as String?,completionDate: freezed == completionDate ? _self.completionDate : completionDate // ignore: cast_nullable_to_non_nullable
as String?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as String?,updatedAt: freezed == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as String?,createdBy: freezed == createdBy ? _self.createdBy : createdBy // ignore: cast_nullable_to_non_nullable
as String?,reraNumber: freezed == reraNumber ? _self.reraNumber : reraNumber // ignore: cast_nullable_to_non_nullable
as String?,legalStatus: freezed == legalStatus ? _self.legalStatus : legalStatus // ignore: cast_nullable_to_non_nullable
as String?,nearbyLandmarks: freezed == nearbyLandmarks ? _self._nearbyLandmarks : nearbyLandmarks // ignore: cast_nullable_to_non_nullable
as List<String>?,additionalInfo: freezed == additionalInfo ? _self._additionalInfo : additionalInfo // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,layoutMap: freezed == layoutMap ? _self.layoutMap : layoutMap // ignore: cast_nullable_to_non_nullable
as String?,rateList: freezed == rateList ? _self.rateList : rateList // ignore: cast_nullable_to_non_nullable
as String?,handbill: freezed == handbill ? _self.handbill : handbill // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

// dart format on
