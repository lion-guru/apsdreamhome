// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'colony_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$ColonyModelImpl _$$ColonyModelImplFromJson(Map<String, dynamic> json) =>
    _$ColonyModelImpl(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String?,
      description: json['description'] as String?,
      totalPlots: (json['total_plots'] as num?)?.toInt() ?? 0,
      availablePlots: (json['available_plots'] as num?)?.toInt() ?? 0,
      pricePerSqft: (json['starting_price'] as num?)?.toDouble() ?? 0.0,
      district: json['district_name'] as String? ?? '',
      districtId: (json['district_id'] as num?)?.toInt() ?? 0,
      imagePath: json['image_path'] as String?,
      imageUrl: json['image_url'] as String?,
      isActive: json['is_active'] as bool? ?? true,
      isFeatured: json['is_featured'] as bool? ?? false,
      location: json['location'] as String? ?? '',
      state: json['state'] as String? ?? '',
      images: (json['images'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList(),
      masterPlanImage: json['masterPlanImage'] as String?,
      videoUrl: json['videoUrl'] as String?,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      holdPlots: (json['holdPlots'] as num?)?.toInt() ?? 0,
      bookedPlots: (json['bookedPlots'] as num?)?.toInt() ?? 0,
      soldPlots: (json['soldPlots'] as num?)?.toInt() ?? 0,
      tokenAmount: (json['tokenAmount'] as num?)?.toDouble(),
      bookingPercentage: (json['bookingPercentage'] as num?)?.toDouble(),
      blockWisePricing: (json['blockWisePricing'] as Map<String, dynamic>?)
          ?.map((k, e) => MapEntry(k, (e as num).toDouble())),
      amenities: (json['amenities'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList(),
      launchDate: json['launchDate'] as String?,
      completionDate: json['completionDate'] as String?,
      createdAt: json['createdAt'] as String?,
      updatedAt: json['updatedAt'] as String?,
      createdBy: json['createdBy'] as String?,
      reraNumber: json['reraNumber'] as String?,
      legalStatus: json['legalStatus'] as String?,
      nearbyLandmarks: (json['nearbyLandmarks'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList(),
      additionalInfo: json['additionalInfo'] as Map<String, dynamic>?,
      layoutMap: json['layoutMap'] as String?,
      rateList: json['rateList'] as String?,
      handbill: json['handbill'] as String?,
      mapLink: json['mapLink'] as String?,
    );

Map<String, dynamic> _$$ColonyModelImplToJson(_$ColonyModelImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'slug': instance.slug,
      'description': instance.description,
      'total_plots': instance.totalPlots,
      'available_plots': instance.availablePlots,
      'starting_price': instance.pricePerSqft,
      'district_name': instance.district,
      'district_id': instance.districtId,
      'image_path': instance.imagePath,
      'image_url': instance.imageUrl,
      'is_active': instance.isActive,
      'is_featured': instance.isFeatured,
      'location': instance.location,
      'state': instance.state,
      'images': instance.images,
      'masterPlanImage': instance.masterPlanImage,
      'videoUrl': instance.videoUrl,
      'latitude': instance.latitude,
      'longitude': instance.longitude,
      'holdPlots': instance.holdPlots,
      'bookedPlots': instance.bookedPlots,
      'soldPlots': instance.soldPlots,
      'tokenAmount': instance.tokenAmount,
      'bookingPercentage': instance.bookingPercentage,
      'blockWisePricing': instance.blockWisePricing,
      'amenities': instance.amenities,
      'launchDate': instance.launchDate,
      'completionDate': instance.completionDate,
      'createdAt': instance.createdAt,
      'updatedAt': instance.updatedAt,
      'createdBy': instance.createdBy,
      'reraNumber': instance.reraNumber,
      'legalStatus': instance.legalStatus,
      'nearbyLandmarks': instance.nearbyLandmarks,
      'additionalInfo': instance.additionalInfo,
      'layoutMap': instance.layoutMap,
      'rateList': instance.rateList,
      'handbill': instance.handbill,
      'mapLink': instance.mapLink,
    };
