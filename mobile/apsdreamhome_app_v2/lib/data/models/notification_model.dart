import 'package:freezed_annotation/freezed_annotation.dart';

part 'notification_model.freezed.dart';
part 'notification_model.g.dart';

/// Notification Model - Push Notifications & In-App
@freezed
class NotificationModel with _$NotificationModel {
  const factory NotificationModel({
    @Default('') String id,
    @Default('') String userId,
    @Default('') String type, // booking, commission, payout, lead, general, promotional
    @Default('') String title,
    @Default('') String body,
    String? imageUrl,
    
    // Action
    String? actionType, // open_screen, open_url, show_dialog, none
    String? actionData, // screen_name, url, etc.
    String? actionUrl,
    
    // Related Entity
    String? relatedId,
    String? relatedType,
    
    // Status
    bool? isRead,
    DateTime? readAt,
    
    // Timestamps
    DateTime? createdAt,
    DateTime? expiresAt,
    
    // Delivery
    bool? pushDelivered,
    DateTime? pushDeliveredAt,
    String? fcmMessageId,
  }) = _NotificationModel;

  factory NotificationModel.fromJson(Map<String, dynamic> json) =>
      _$NotificationModelFromJson(json);
}

// Notification Types
class NotificationTypes {
  static const String bookingApproved = 'booking_approved';
  static const String bookingRejected = 'booking_rejected';
  static const String bookingCompleted = 'booking_completed';
  static const String commissionEarned = 'commission_earned';
  static const String commissionPaid = 'commission_paid';
  static const String payoutProcessed = 'payout_processed';
  static const String leadAssigned = 'lead_assigned';
  static const String followUpReminder = 'follow_up_reminder';
  static const String newPlotAvailable = 'new_plot_available';
  static const String priceDrop = 'price_drop';
  static const String documentVerified = 'document_verified';
  static const String rankUpgraded = 'rank_upgraded';
  static const String achievementUnlocked = 'achievement_unlocked';
  static const String general = 'general';
  static const String promotional = 'promotional';
}
