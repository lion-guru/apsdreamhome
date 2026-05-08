import 'package:freezed_annotation/freezed_annotation.dart';

part 'colony_model.freezed.dart';
part 'colony_model.g.dart';

/// Colony Model - Colony Development & Plot Sales
@freezed
class ColonyModel with _$ColonyModel {
  const factory ColonyModel({
    required String id,
    required String name,
    required String location,
    required String district,
    required String state,
    String? description,
    List<String>? images,
    String? masterPlanImage,
    String? videoUrl,
    double? latitude,
    double? longitude,
    
    // Plot Statistics
    required int totalPlots,
    required int availablePlots,
    required int holdPlots,
    required int bookedPlots,
    required int soldPlots,
    
    // Pricing
    required double pricePerSqft,
    double? tokenAmount,
    double? bookingPercentage,
    Map<String, double>? blockWisePricing, // A, B, C blocks with different rates
    
    // Amenities
    List<String>? amenities,
    
    // Status
    required String status, // upcoming, launching, active, completed, sold_out
    DateTime? launchDate,
    DateTime? completionDate,
    
    // Timestamps
    DateTime? createdAt,
    DateTime? updatedAt,
    String? createdBy,
    
    // Additional Info
    String? reraNumber,
    String? legalStatus,
    List<String>? nearbyLandmarks,
    Map<String, dynamic>? additionalInfo,
    
    // New Fields for Images and Maps
    String? layoutMap,
    String? rateList,
    String? handbill,
    String? mapLink,
  }) = _ColonyModel;

  factory ColonyModel.fromJson(Map<String, dynamic> json) =>
      _$ColonyModelFromJson(json);

  const ColonyModel._();

  double get progressPercentage => totalPlots > 0 
      ? (soldPlots / totalPlots) * 100 
      : 0;
      
  bool get isUpcoming => status == 'upcoming';
  bool get isLaunching => status == 'launching';
  bool get isActive => status == 'active';
  bool get isCompleted => status == 'completed';
  bool get isSoldOut => status == 'sold_out';
}
