// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'colony_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$ColonyModelImpl _$$ColonyModelImplFromJson(Map<String, dynamic> json) =>
    _$ColonyModelImpl(
      id: json['id'] as String? ?? '',
      name: json['name'] as String? ?? '',
      location: json['location'] as String? ?? '',
      district: json['district'] as String? ?? '',
      state: json['state'] as String? ?? '',
      description: json['description'] as String?,
      images: (json['images'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList(),
      masterPlanImage: json['masterPlanImage'] as String?,
      videoUrl: json['videoUrl'] as String?,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      totalPlots: (json['totalPlots'] as num?)?.toInt() ?? 0,
      availablePlots: (json['availablePlots'] as num?)?.toInt() ?? 0,
      holdPlots: (json['holdPlots'] as num?)?.toInt() ?? 0,
      bookedPlots: (json['bookedPlots'] as num?)?.toInt() ?? 0,
      soldPlots: (json['soldPlots'] as num?)?.toInt() ?? 0,
      pricePerSqft: (json['pricePerSqft'] as num?)?.toDouble() ?? 0.0,
      tokenAmount: (json['tokenAmount'] as num?)?.toDouble(),
      bookingPercentage: (json['bookingPercentage'] as num?)?.toDouble(),
      blockWisePricing: (json['blockWisePricing'] as Map<String, dynamic>?)
          ?.map((k, e) => MapEntry(k, (e as num).toDouble())),
      amenities: (json['amenities'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList(),
      status: json['status'] as String? ?? 'upcoming',
      launchDate: json['launchDate'] == null
          ? null
          : DateTime.parse(json['launchDate'] as String),
      completionDate: json['completionDate'] == null
          ? null
          : DateTime.parse(json['completionDate'] as String),
      createdAt: json['createdAt'] == null
          ? null
          : DateTime.parse(json['createdAt'] as String),
      updatedAt: json['updatedAt'] == null
          ? null
          : DateTime.parse(json['updatedAt'] as String),
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
      'location': instance.location,
      'district': instance.district,
      'state': instance.state,
      'description': instance.description,
      'images': instance.images,
      'masterPlanImage': instance.masterPlanImage,
      'videoUrl': instance.videoUrl,
      'latitude': instance.latitude,
      'longitude': instance.longitude,
      'totalPlots': instance.totalPlots,
      'availablePlots': instance.availablePlots,
      'holdPlots': instance.holdPlots,
      'bookedPlots': instance.bookedPlots,
      'soldPlots': instance.soldPlots,
      'pricePerSqft': instance.pricePerSqft,
      'tokenAmount': instance.tokenAmount,
      'bookingPercentage': instance.bookingPercentage,
      'blockWisePricing': instance.blockWisePricing,
      'amenities': instance.amenities,
      'status': instance.status,
      'launchDate': instance.launchDate?.toIso8601String(),
      'completionDate': instance.completionDate?.toIso8601String(),
      'createdAt': instance.createdAt?.toIso8601String(),
      'updatedAt': instance.updatedAt?.toIso8601String(),
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
