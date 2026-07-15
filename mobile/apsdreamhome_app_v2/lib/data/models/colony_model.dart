import 'dart:convert';

import 'package:freezed_annotation/freezed_annotation.dart';

part 'colony_model.freezed.dart';
part 'colony_model.g.dart';

/// Colony Model - maps directly to PHP API snake_case response
@freezed
class ColonyModel with _$ColonyModel {
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

    // Compatibility fields (computed from API data)
    @Default('') String location,
    @Default('') String state,
    List<String>? images,
    String? masterPlanImage,
    String? videoUrl,
    double? latitude,
    double? longitude,

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
    String? mapLink,
  }) = _ColonyModel;

  factory ColonyModel.fromJson(Map<String, dynamic> raw) {
    // Preprocess: API detail endpoint returns different types than list endpoint
    // - starting_price: string "17050000.00" vs number 1400
    // - amenities: stringified JSON array "[\"...\"]" vs null/actual array
    // - is_active/is_featured: int 1 vs boolean true
    final json = Map<String, dynamic>.from(raw);

    // starting_price: ensure numeric
    if (json['starting_price'] is String) {
      json['starting_price'] =
          double.tryParse(json['starting_price'] as String) ?? 0.0;
    }

    // amenities: stringified JSON array → actual list
    if (json['amenities'] is String) {
      try {
        final decoded = jsonDecode(json['amenities'] as String);
        json['amenities'] = decoded is List ? decoded : null;
      } catch (_) {
        json['amenities'] = null;
      }
    }

    // is_active / is_featured: int → bool
    if (json['is_active'] is int) {
      json['is_active'] = (json['is_active'] as int) == 1;
    }
    if (json['is_featured'] is int) {
      json['is_featured'] = (json['is_featured'] as int) == 1;
    }

    return _$ColonyModelFromJson(json);
  }

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

  /// Get display image URL (prefer image_url, fallback to imagePath)
  String? get displayImage => imageUrl ?? imagePath;

  /// Get display images list
  List<String> get displayImages {
    if (imageUrl != null && imageUrl!.isNotEmpty) return [imageUrl!];
    if (imagePath != null && imagePath!.isNotEmpty) return [imagePath!];
    return [];
  }
}
