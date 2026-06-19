import 'package:freezed_annotation/freezed_annotation.dart';

part 'emi_automation_model.freezed.dart';
part 'emi_automation_model.g.dart';

/// EMI Automation Configuration & System
/// Automated reminders, calls, WhatsApp messages for EMI collection
@freezed
class EMIAutomationConfig with _$EMIAutomationConfig {
  const factory EMIAutomationConfig({
    required String id,
    required String companyId,
    required String companyName,
    
    // WhatsApp Business Configuration
    required WhatsAppConfig whatsappConfig,
    
    // Voice Call Configuration (IVR/Cloud telephony)
    required VoiceCallConfig voiceCallConfig,
    
    // SMS Gateway Configuration
    required SMSConfig smsConfig,
    
    // Email Configuration
    required EmailConfig emailConfig,
    
    // Automation Rules
    @Default([]) List<AutomationRule> reminderRules,
    @Default([]) List<AutomationRule> escalationRules,
    @Default([]) List<AutomationRule> collectionRules,
    
    // Field Agent Settings
    required FieldAgentConfig fieldAgentConfig,
    
    // AI/ML Settings
    required AIConfig aiConfig,
    
    required bool isActive,
    required DateTime createdAt,
    required DateTime updatedAt,
  }) = _EMIAutomationConfig;

  factory EMIAutomationConfig.fromJson(Map<String, dynamic> json) =>
      _$EMIAutomationConfigFromJson(json);
}

@freezed
class WhatsAppConfig with _$WhatsAppConfig {
  const factory WhatsAppConfig({
    @Default(false) bool isEnabled,
    String? businessAccountId,
    String? phoneNumberId,
    String? accessToken,
    String? apiVersion,
    
    // Template IDs for different scenarios
    String? welcomeTemplateId,
    String? reminderTemplateId,
    String? overdueTemplateId,
    String? paymentConfirmationTemplateId,
    String? defaulterTemplateId,
    
    // Default message settings
    @Default(true) bool sendReminders,
    @Default(true) bool sendOverdueAlerts,
    @Default(false) bool sendVoiceNotes, // AI generated voice messages
    
    // Business hours
    String? businessHoursStart, // 09:00
    String? businessHoursEnd,   // 18:00
    @Default(false) bool sendOutsideBusinessHours,
  }) = _WhatsAppConfig;

  factory WhatsAppConfig.fromJson(Map<String, dynamic> json) =>
      _$WhatsAppConfigFromJson(json);
}

@freezed
class VoiceCallConfig with _$VoiceCallConfig {
  const factory VoiceCallConfig({
    @Default(false) bool isEnabled,
    String? provider, // Exotel, Knowlarity, Twilio, Ozonetel
    String? apiKey,
    String? apiSecret,
    String? fromNumber,
    
    // IVR Settings
    @Default(false) bool useIVR,
    String? ivrGreetingMessage,
    String? ivrMenuOptions, // "Press 1 for EMI status, 2 for payment link..."
    
    // AI Voice Bot
    @Default(false) bool useAIVoiceBot,
    String? aiVoiceLanguage, // hi-IN, en-IN
    String? aiVoiceGender,   // male, female
    
    // Call scheduling
    @Default(3) int maxRetryAttempts,
    @Default(30) int retryIntervalMinutes,
    @Default([10, 14, 16]) List<int> preferredCallHours, // 10 AM, 2 PM, 4 PM
    
    // Recording
    @Default(true) bool recordCalls,
    @Default(true) bool transcribeCalls,
  }) = _VoiceCallConfig;

  factory VoiceCallConfig.fromJson(Map<String, dynamic> json) =>
      _$VoiceCallConfigFromJson(json);
}

@freezed
class SMSConfig with _$SMSConfig {
  const factory SMSConfig({
    @Default(false) bool isEnabled,
    String? provider, // Msg91, Twilio, ValueFirst
    String? apiKey,
    String? senderId, // APSDLRM
    
    // DLT Template IDs (India TRAI compliance)
    String? otpTemplateId,
    String? reminderTemplateId,
    String? overdueTemplateId,
    String? paymentLinkTemplateId,
    String? receiptTemplateId,
    
    // SMS settings
    @Default(true) bool useShortURL,
    @Default(true) bool trackClicks,
    @Default([]) List<String> blockedHours, // Don't send SMS during 22:00-08:00
  }) = _SMSConfig;

  factory SMSConfig.fromJson(Map<String, dynamic> json) =>
      _$SMSConfigFromJson(json);
}

@freezed
class EmailConfig with _$EmailConfig {
  const factory EmailConfig({
    @Default(false) bool isEnabled,
    String? provider, // SendGrid, AWS SES, Mailgun
    String? apiKey,
    String? fromEmail,
    String? fromName,
    String? replyToEmail,
    
    // Email templates
    String? welcomeEmailTemplateId,
    String? reminderEmailTemplateId,
    String? invoiceEmailTemplateId,
    String? receiptEmailTemplateId,
    String? newsletterTemplateId,
    
    // Settings
    @Default(true) bool sendHTML,
    @Default(true) bool trackOpens,
    @Default(true) bool trackClicks,
    @Default([]) List<String> bccEmails, // Admin BCC for monitoring
  }) = _EmailConfig;

  factory EmailConfig.fromJson(Map<String, dynamic> json) =>
      _$EmailConfigFromJson(json);
}

@freezed
class FieldAgentConfig with _$FieldAgentConfig {
  const factory FieldAgentConfig({
    // Agent assignment settings
    @Default('round_robin') String assignmentMethod, // round_robin, load_based, location_based, performance_based
    @Default(20) int maxLeadsPerAgent,
    @Default(50) int maxDailyVisits,
    
    // Location tracking
    @Default(true) bool trackLocation,
    @Default(5) int locationUpdateIntervalMinutes,
    @Default(true) bool geoFencingEnabled,
    @Default(500) int geoFenceRadiusMeters,
    
    // Commission structure
    @Default(0.5) double collectionCommissionPercent, // 0.5% of collected amount
    @Default(50) double perCollectionFixedIncentive,
    @Default(500) double targetAchievementBonus,
    
    // App settings
    @Default(true) bool offlineModeEnabled,
    @Default(true) bool autoSyncEnabled,
    @Default(15) int syncIntervalMinutes,
    
    // Notifications
    @Default(true) bool notifyOnNewAssignment,
    @Default(true) bool notifyOnDueListReady,
    @Default(true) bool notifyOnCollectionConfirmation,
  }) = _FieldAgentConfig;

  factory FieldAgentConfig.fromJson(Map<String, dynamic> json) =>
      _$FieldAgentConfigFromJson(json);
}

@freezed
class AIConfig with _$AIConfig {
  const factory AIConfig({
    // AI Lead Scoring
    @Default(true) bool enableLeadScoring,
    @Default(true) bool autoAssignLeads,
    
    // AI Communication
    @Default(true) bool enableAIVoiceCalls,
    @Default(true) bool enableAIWhatsApp,
    @Default(true) bool enableAIPersonalization,
    
    // AI Prediction
    @Default(true) bool predictDefaultRisk,
    @Default(true) bool predictBestCollectionTime,
    @Default(true) bool predictCustomerResponse,
    
    // AI Document Processing
    @Default(true) bool enableOCR,
    @Default(true) bool enableAutoReceiptGeneration,
    
    // AI Assistant
    @Default(true) bool enableFieldAgentAIAssistant,
    @Default(true) bool enableCustomerAIChatbot,
  }) = _AIConfig;

  factory AIConfig.fromJson(Map<String, dynamic> json) =>
      _$AIConfigFromJson(json);
}

@freezed
class AutomationRule with _$AutomationRule {
  const factory AutomationRule({
    @Default('') String id,
    @Default('') String name,
    @Default('') String type, // reminder, escalation, collection
    @Default('') String trigger, // days_before_due, days_after_due, amount_threshold
    @Default(0) int triggerValue, // 3 (days), 5000 (amount)
    
    // Actions to take
    @Default([]) List<String> actions, // whatsapp, sms, email, call, agent_notify
    
    // Timing
    @Default('09:00') String scheduleTime, // 09:00
    String? scheduleDays, // monday,tuesday,wednesday
    
    // Priority
    @Default(1) int priority,
    
    // Conditions
    String? conditionAmount, // > 10000
    String? conditionStatus, // regular, irregular, defaulter
    
    // Message templates
    String? whatsappTemplate,
    String? smsTemplate,
    String? emailTemplate,
    String? voiceMessage,
    
    // Status
    @Default(true) bool isActive,
    required DateTime createdAt,
  }) = _AutomationRule;

  factory AutomationRule.fromJson(Map<String, dynamic> json) =>
      _$AutomationRuleFromJson(json);
}

// ==================== AUTOMATION EXECUTION LOGS ====================

@freezed
class AutomationExecution with _$AutomationExecution {
  const factory AutomationExecution({
    @Default('') String id,
    @Default('') String ruleId,
    @Default('') String ruleName,
    @Default('') String customerId,
    @Default('') String bookingId,
    @Default('') String emiId,
    
    // Execution details
    @Default('') String channel, // whatsapp, sms, email, call, agent_app
    @Default('') String action, // reminder_sent, call_made, agent_notified
    @Default('') String status, // success, failed, pending, scheduled
    
    // Content
    String? messageContent,
    String? templateUsed,
    Map<String, dynamic>? metadata,
    
    // Timing
    required DateTime scheduledAt,
    DateTime? executedAt,
    DateTime? deliveredAt,
    
    // Response
    String? customerResponse,
    DateTime? responseAt,
    String? responseType, // will_pay, cannot_pay, asked_time, paid
    
    // Error
    String? errorMessage,
    int? retryCount,
    
    required DateTime createdAt,
  }) = _AutomationExecution;

  factory AutomationExecution.fromJson(Map<String, dynamic> json) =>
      _$AutomationExecutionFromJson(json);
}

// ==================== CUSTOMER COMMUNICATION LOG ====================

@freezed
class CustomerCommunicationLog with _$CustomerCommunicationLog {
  const factory CustomerCommunicationLog({
    @Default('') String id,
    @Default('') String customerId,
    @Default('') String bookingId,
    
    // Communication details
    @Default('') String channel, // whatsapp, sms, email, call, agent_visit
    @Default('') String direction, // outgoing, incoming
    @Default('') String type, // reminder, follow_up, payment_confirmation, enquiry
    
    // Content
    String? message,
    String? attachmentUrl,
    String? callRecordingUrl,
    int? callDurationSeconds,
    
    // Status
    @Default('') String status, // sent, delivered, read, failed
    DateTime? sentAt,
    DateTime? deliveredAt,
    DateTime? readAt,
    
    // Agent info (if agent initiated)
    String? agentId,
    String? agentName,
    
    // AI/Automation info
    @Default(false) bool wasAutomated,
    String? automationRuleId,
    
    // Customer response
    String? customerReply,
    DateTime? repliedAt,
    
    // Notes
    String? adminNotes,
    
    required DateTime createdAt,
  }) = _CustomerCommunicationLog;

  factory CustomerCommunicationLog.fromJson(Map<String, dynamic> json) =>
      _$CustomerCommunicationLogFromJson(json);
}
