import 'dart:convert';

import 'package:freezed_annotation/freezed_annotation.dart';

part 'colony_model.freezed.dart';
part 'colony_model.g.dart';

/// Colony Model - maps directly to PHP API snake_case response
@freezed
abstract class ColonyModel with _$ColonyModel {
  const factory ColonyModel({
    @Default(0) int id,
    @Default('') String name,
    String? slug,
    String? description,

    // Plot statistics (API: total_plots, available_plots)
    @JsonKey(name: 'total_plots') @Default(0) int totalPlots,
    @JsonKey(name: 'available_plots') @Default(0) int availablePlots,

    // Pricing (API: starting_price)
    @JsonKey(name: 'starting_price') @Default(0.0) double pricePerSqft,

    // Location (API: district_name, district_id)
    @JsonKey(name: 'district_name') @Default('') String district,
    @JsonKey(name: 'district_id') @Default(0) int districtId,

    // Images (API: image_path, image_url)
    @JsonKey(name: 'image_path') String? imagePath,
    @JsonKey(name: 'image_url') String? imageUrl,

    // Status (API: is_active, is_featured)
    @JsonKey(name: 'is_active') @Default(true) bool isActive,
    @JsonKey(name: 'is_featured') @Default(false) bool isFeatured,

    // Layout & Gallery (API: layout_image, gallery_images_data)
    @JsonKey(name: 'layout_image') String? layoutImage,
    @JsonKey(name: 'layout_image_url') String? layoutImageUrl,
    @JsonKey(name: 'gallery_images_data') List<String>? galleryImagesData,

    // Video & Virtual Tour (API: youtube_video_url, virtual_tour_url)
    @JsonKey(name: 'youtube_video_url') String? youtubeVideoUrl,
    @JsonKey(name: 'virtual_tour_url') String? virtualTourUrl,

    // Documents (API: brochure_path, colony_documents)
    @JsonKey(name: 'brochure_path') String? brochurePath,
    @JsonKey(name: 'colony_documents') List<String>? colonyDocuments,

    // Map & Location (API: map_link, latitude, longitude)
    @JsonKey(name: 'map_link') String? mapLinkApi,
    double? latitude,
    double? longitude,

    // Nearby Places (API: nearby_places)
    @JsonKey(name: 'nearby_places') String? nearbyPlacesRaw,

    // Compatibility fields (computed from API data)
    @Default('') String location,
    @Default('') String state,
    List<String>? images,
    String? masterPlanImage,
    String? videoUrl,

    // Extended plot stats
    @Default(0) int holdPlots,
    @Default(0) int bookedPlots,
    @Default(0) int soldPlots,

    // Extended pricing
    double? tokenAmount,
    double? bookingPercentage,
    Map<String, double>? blockWisePricing,

    // Amenities
    List<String>? amenities,

    // Dates
    String? launchDate,
    String? completionDate,
    String? createdAt,
    String? updatedAt,

    // Additional
    String? createdBy,
    String? reraNumber,
    String? legalStatus,
    List<String>? nearbyLandmarks,
    Map<String, dynamic>? additionalInfo,
    String? layoutMap,
    String? rateList,
    String? handbill,
  }) = _ColonyModel;

  factory ColonyModel.fromJson(Map<String, dynamic> raw) =>
      _$ColonyModelFromJson(_preprocessColonyJson(raw));

  const ColonyModel._();

  /// Computed status string from isActive flag
  String get status => isActive ? 'active' : 'upcoming';

  double get progressPercentage =>
      totalPlots > 0 ? (soldPlots / totalPlots) * 100 : 0;

  bool get isUpcoming => status == 'upcoming';
  bool get isLaunching => status == 'launching';
  bool get isActiveStatus => status == 'active';
  bool get isCompleted => status == 'completed';
  bool get isSoldOut => status == 'sold_out';

  /// Curated high-definition luxury architectural image for this colony
  String get luxuryHeroImage {
    const images = {
      1: 'https://images.unsplash.com/photo-1613977257363-707ba9348227?auto=format&fit=crop&w=1200&q=80', // Suryoday - Luxury Villa & Gated Community
      2: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80', // Braj Radha Nagari - Modern Estate Boulevard
      3: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80', // Raghunath City - Green Gated Township
      4: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80', // Budh Bihar Ph-1 - Boulevard & Palm Trees
      5: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80', // Awadh Vihar - Grand Residential Enclave
      6: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80', // Chandra Green - Eco Park Township
      7: 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1200&q=80', // Shivpuri Enclave - Luxury Living
    };
    return images[id] ?? 'https://images.unsplash.com/photo-1613977257363-707ba9348227?auto=format&fit=crop&w=1200&q=80';
  }

  /// Get display image URL (prefer valid remote image_url, fallback to curated luxury image)
  String get displayImage {
    if (imageUrl != null &&
        imageUrl!.startsWith('http') &&
        !imageUrl!.contains('127.0.0.1') &&
        !imageUrl!.contains('localhost')) {
      return imageUrl!;
    }
    return luxuryHeroImage;
  }

  /// Get display images list (prefers valid API images, with curated high-res gallery)
  List<String> get displayImages {
    final list = <String>[];
    if (galleryImagesData != null && galleryImagesData!.isNotEmpty) {
      for (final img in galleryImagesData!) {
        if (img.startsWith('http') &&
            !img.contains('127.0.0.1') &&
            !img.contains('localhost')) {
          list.add(img);
        }
      }
    }
    if (imageUrl != null &&
        imageUrl!.startsWith('http') &&
        !imageUrl!.contains('127.0.0.1') &&
        !imageUrl!.contains('localhost')) {
      if (!list.contains(imageUrl)) list.add(imageUrl!);
    }
    if (list.isEmpty) {
      list.add(luxuryHeroImage);
      // Complementary luxury architectural gallery
      const galleries = {
        1: [
          'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1000&q=80',
          'https://images.unsplash.com/photo-1584738766473-61c083514bf4?auto=format&fit=crop&w=1000&q=80',
          'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?auto=format&fit=crop&w=1000&q=80',
        ],
        2: [
          'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=1000&q=80',
          'https://images.unsplash.com/photo-1600585152220-90363fe7e115?auto=format&fit=crop&w=1000&q=80',
          'https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?auto=format&fit=crop&w=1000&q=80',
        ],
        3: [
          'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1000&q=80',
          'https://images.unsplash.com/photo-1600566752355-35792bedcfea?auto=format&fit=crop&w=1000&q=80',
        ],
        4: [
          'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1000&q=80',
          'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?auto=format&fit=crop&w=1000&q=80',
        ],
        5: [
          'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
          'https://images.unsplash.com/photo-1512915922686-57c11dde9b6b?auto=format&fit=crop&w=1000&q=80',
        ],
        6: [
          'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1000&q=80',
          'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&w=1000&q=80',
        ],
        7: [
          'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&w=1000&q=80',
          'https://images.unsplash.com/photo-1600585152220-90363fe7e115?auto=format&fit=crop&w=1000&q=80',
        ],
      };
      list.addAll(galleries[id] ?? [
        'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1000&q=80',
        'https://images.unsplash.com/photo-1584738766473-61c083514bf4?auto=format&fit=crop&w=1000&q=80',
      ]);
    }
    return list;
  }

  /// Get layout/master plan image URL
  String? get displayLayoutImage => layoutImageUrl ?? layoutImage;

  /// Get Google Maps directions URL
  String? get directionsUrl {
    final lat = latitude;
    final lng = longitude;
    if (lat != null && lng != null) {
      return 'https://www.google.com/maps/dir/?api=1&destination=$lat,$lng';
    }
    if (mapLinkApi != null && mapLinkApi!.isNotEmpty) return mapLinkApi;
    return null;
  }

  /// Get nearby places as list
  List<String> get nearbyPlacesList {
    if (nearbyLandmarks != null && nearbyLandmarks!.isNotEmpty) {
      return nearbyLandmarks!;
    }
    if (nearbyPlacesRaw != null && nearbyPlacesRaw!.isNotEmpty) {
      try {
        final decoded = jsonDecode(nearbyPlacesRaw!);
        if (decoded is List) return decoded.cast<String>();
      } catch (_) {}
    }
    return [];
  }
}

/// Preprocess raw API JSON before [ColonyModel] deserialization.
/// API detail endpoint returns different types than list endpoint:
/// - starting_price: string "17050000.00" vs number 1400
/// - amenities/gallery_images/colony_documents: stringified JSON arrays vs null/actual arrays
/// - is_active/is_featured: int 1 vs boolean true
Map<String, dynamic> _preprocessColonyJson(Map<String, dynamic> raw) {
  final json = Map<String, dynamic>.from(raw);

  // starting_price: ensure numeric
  if (json['starting_price'] is String) {
    json['starting_price'] =
        double.tryParse(json['starting_price'] as String) ?? 0.0;
  }

  // amenities: stringified JSON array -> actual list
  if (json['amenities'] is String) {
    try {
      final decoded = jsonDecode(json['amenities'] as String);
      json['amenities'] = decoded is List ? decoded : null;
    } catch (_) {
      json['amenities'] = null;
    }
  }

  // gallery_images_data: ensure list of strings
  if (json['gallery_images_data'] is String) {
    try {
      final decoded = jsonDecode(json['gallery_images_data'] as String);
      json['gallery_images_data'] =
          decoded is List ? decoded.cast<String>() : null;
    } catch (_) {
      json['gallery_images_data'] = null;
    }
  }

  // gallery_images: JSON array from DB -> list of strings (fallback)
  if (json['gallery_images'] is String && json['gallery_images_data'] == null) {
    try {
      final decoded = jsonDecode(json['gallery_images'] as String);
      if (decoded is List) {
        json['gallery_images_data'] = decoded.cast<String>();
      }
    } catch (_) {}
  }

  // colony_documents: stringified JSON array -> list
  if (json['colony_documents'] is String) {
    try {
      final decoded = jsonDecode(json['colony_documents'] as String);
      json['colony_documents'] =
          decoded is List ? decoded.cast<String>() : null;
    } catch (_) {
      json['colony_documents'] = null;
    }
  }

  // is_active / is_featured: int -> bool
  if (json['is_active'] is int) {
    json['is_active'] = (json['is_active'] as int) == 1;
  }
  if (json['is_featured'] is int) {
    json['is_featured'] = (json['is_featured'] as int) == 1;
  }

  return json;
}
