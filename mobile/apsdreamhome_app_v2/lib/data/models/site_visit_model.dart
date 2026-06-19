import 'package:freezed_annotation/freezed_annotation.dart';

part 'site_visit_model.freezed.dart';
part 'site_visit_model.g.dart';

/// Site Visit Model - GPS Tracking for Site Visits
@freezed
class SiteVisitModel with _$SiteVisitModel {
  const factory SiteVisitModel({
    @Default('') String id,
    @Default('') String agentId,
    @Default('') String agentName,
    
    // Customer Info
    String? customerId,
    String? customerName,
    String? customerPhone,
    
    // Location
    @Default('') String colonyId,
    @Default('') String colonyName,
    List<String>? plotIdsShown,
    List<String>? plotNumbersShown,
    
    // GPS Coordinates
    @Default(0.0) double latitude,
    @Default(0.0) double longitude,
    String? address,
    double? accuracy,
    
    // Visit Details
    required DateTime visitStartTime,
    DateTime? visitEndTime,
    Duration? duration,
    String? purpose, // initial_visit, follow_up, document_collection, etc.
    
    // Feedback
    String? customerFeedback,
    String? agentNotes,
    String? outcome, // interested, not_interested, thinking, booking_done
    
    // Follow-up
    bool? followUpRequired,
    DateTime? followUpDate,
    String? followUpType,
    
    // Media
    List<String>? photos,
    List<String>? videos,
    String? voiceNoteUrl,
    
    // Offline Sync
    bool? isOfflineCreated,
    DateTime? syncedAt,
    
    // Timestamps
    DateTime? createdAt,
    DateTime? updatedAt,
  }) = _SiteVisitModel;

  factory SiteVisitModel.fromJson(Map<String, dynamic> json) =>
      _$SiteVisitModelFromJson(json);
}

// Live Location Sharing Model
@freezed
class LiveLocationModel with _$LiveLocationModel {
  const factory LiveLocationModel({
    @Default('') String userId,
    @Default('') String userName,
    @Default('') String userType, // agent, customer
    @Default(0.0) double latitude,
    @Default(0.0) double longitude,
    required DateTime timestamp,
    double? speed,
    double? heading,
    double? accuracy,
    bool? isSharingEnabled,
    DateTime? sharingStartedAt,
    DateTime? sharingExpiresAt,
    String? sharedWith, // booking_id, lead_id, etc.
  }) = _LiveLocationModel;

  factory LiveLocationModel.fromJson(Map<String, dynamic> json) =>
      _$LiveLocationModelFromJson(json);
}
