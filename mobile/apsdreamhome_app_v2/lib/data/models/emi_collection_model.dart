import 'package:freezed_annotation/freezed_annotation.dart';
import 'geo_location.dart';

part 'emi_collection_model.freezed.dart';
part 'emi_collection_model.g.dart';

/// EMI Collection Agent Model
/// For field agents who collect EMI payments door-to-door
@freezed
abstract class EMICollectionAgent with _$EMICollectionAgent {
  const factory EMICollectionAgent({
    required String id,
    required String name,
    required String phone,
    required String email,
    String? photoUrl,
    String? aadharNumber,
    String? address,
    
    // Employment
    required String employeeId,
    required DateTime joiningDate,
    required CollectionAgentType agentType, // FullTime, PartTime, Freelance
    required CollectionArea assignedArea,
    
    // Salary Structure
    required double monthlySalary,
    double? commissionPerCollection, // Per EMI collected
    double? commissionPercentage, // % of collected amount
    double? incentivePerTarget, // Bonus for target achievement
    
    // Assigned Customers
    @Default([]) List<String> assignedCustomerIds,
    @Default([]) List<EMICustomerAssignment> customerAssignments,
    
    // Performance
    @Default([]) List<DailyCollectionReport> dailyReports,
    @Default([]) List<MonthlyCollectionPerformance> monthlyReports,
    
    // Current Month Stats
    @Default(0) int currentMonthCollections,
    @Default(0) double currentMonthAmount,
    @Default(0) double currentMonthCommission,
    @Default(0) int currentMonthTarget,
    @Default(0) double targetAchievement,
    
    // Location Tracking
    @Default([]) List<LocationTracking> locationHistory,
    bool? isCurrentlyActive,
    GeoLocation? lastLocation,
    DateTime? lastLocationUpdate,
    
    // Status
    required AgentStatus status,
    DateTime? lastActiveAt,
    
    // Documents
    @Default([]) List<String> documentUrls,
    
    required DateTime createdAt,
    required DateTime updatedAt,
  }) = _EMICollectionAgent;

  factory EMICollectionAgent.fromJson(Map<String, dynamic> json) =>
      _$EMICollectionAgentFromJson(json);
}

enum CollectionAgentType { fullTime, partTime, freelance, contractor }

enum AgentStatus { active, onLeave, suspended, terminated }

@freezed
abstract class CollectionArea with _$CollectionArea {
  const factory CollectionArea({
    required String areaName,
    required String state,
    required String district,
    required String city,
    @Default([]) List<String> colonies,
    @Default([]) List<String> pincodes,
    String? areaManagerId,
  }) = _CollectionArea;

  factory CollectionArea.fromJson(Map<String, dynamic> json) =>
      _$CollectionAreaFromJson(json);
}

@freezed
abstract class EMICustomerAssignment with _$EMICustomerAssignment {
  const factory EMICustomerAssignment({
    required String customerId,
    required String customerName,
    required String customerPhone,
    required String customerAddress,
    required String bookingId,
    required String plotNumber,
    required String colonyName,
    
    // EMI Details
    required double monthlyEMI,
    required int totalEMIs,
    required int paidEMIs,
    required int pendingEMIs,
    required double totalDue,
    
    // Due Date
    required int dueDay, // 5th, 10th, 15th of month
    DateTime? nextDueDate,
    
    // Status
    @Default(PaymentStatus.regular) PaymentStatus paymentStatus, // Regular, Irregular, Defaulter
    @Default(false) bool isHighPriority, // For overdue
    
    // Collection Info
    String? preferredCollectionTime, // Morning, Afternoon, Evening
    String? landmark,
    GeoLocation? location,
    
    // History
    @Default([]) List<PreviousVisit> visitHistory,
    String? specialInstructions,
    
    DateTime? assignedAt,
    DateTime? lastCollectedAt,
  }) = _EMICustomerAssignment;

  factory EMICustomerAssignment.fromJson(Map<String, dynamic> json) =>
      _$EMICustomerAssignmentFromJson(json);
}

enum PaymentStatus { regular, irregular, defaulter, newCustomer }

@freezed
abstract class PreviousVisit with _$PreviousVisit {
  const factory PreviousVisit({
    required DateTime visitDate,
    required VisitOutcome outcome,
    double? amountCollected,
    String? notes,
    String? customerFeedback,
  }) = _PreviousVisit;

  factory PreviousVisit.fromJson(Map<String, dynamic> json) =>
      _$PreviousVisitFromJson(json);
}

enum VisitOutcome { collected, partial, notHome, refused, willPayLater, rescheduled, notInterested }

@freezed
abstract class DailyCollectionReport with _$DailyCollectionReport {
  const factory DailyCollectionReport({
    required String id,
    required DateTime date,
    required String agentId,
    
    // Collection Summary
    @Default(0) int totalVisits,
    @Default(0) int successfulCollections,
    @Default(0) int partialCollections,
    @Default(0) int failedVisits,
    @Default(0) int rescheduled,
    @Default(0) int customersNotHome,
    
    // Financial
    @Default(0) double totalCollected,
    @Default(0) double cashCollected,
    @Default(0) double chequeCollected,
    @Default(0) double onlineCollected,
    @Default(0) double upiCollected,
    
    // Individual Collections
    @Default([]) List<CollectionRecord> collections,
    
    // Time Tracking
    DateTime? startTime,
    DateTime? endTime,
    @Default(0) int workingHours,
    
    // Location Data
    @Default([]) List<LocationTracking> routeTaken,
    @Default(0) double totalDistanceKm,
    
    // Status
    required ReportSubmissionStatus submissionStatus,
    DateTime? submittedAt,
    String? adminNotes,
  }) = _DailyCollectionReport;

  factory DailyCollectionReport.fromJson(Map<String, dynamic> json) =>
      _$DailyCollectionReportFromJson(json);
}

enum ReportSubmissionStatus { pending, submitted, verified, disputed }

@freezed
abstract class CollectionRecord with _$CollectionRecord {
  const factory CollectionRecord({
    required String customerId,
    required String customerName,
    required String bookingId,
    required DateTime collectionTime,
    required double amount,
    required PaymentMode mode,
    
    // Details
    int? emiNumber,
    double? lateFee,
    String? chequeNumber,
    String? transactionId,
    String? receiptNumber,
    
    // Location
    GeoLocation? location,
    String? addressAtCollection,
    
    // Proof
    @Default([]) List<String> photoUrls, // Payment proof photos
    String? signatureUrl,
    String? notes,
    
    // Verification
    bool? isVerified,
    DateTime? verifiedAt,
    String? verifiedBy,
    String? disputeReason,
  }) = _CollectionRecord;

  factory CollectionRecord.fromJson(Map<String, dynamic> json) =>
      _$CollectionRecordFromJson(json);
}

enum PaymentMode { cash, cheque, online, upi, card, bankTransfer }

@freezed
abstract class MonthlyCollectionPerformance with _$MonthlyCollectionPerformance {
  const factory MonthlyCollectionPerformance({
    required String id,
    required int year,
    required int month,
    required String agentId,
    
    // Collections
    @Default(0) int totalCollections,
    @Default(0) double totalAmount,
    @Default(0) int totalCustomers,
    @Default(0) int newCustomersAdded,
    
    // Performance Metrics
    @Default(0) double collectionRate, // % of target
    @Default(0) double successRate, // % of visits successful
    @Default(0) int ranking, // Among all agents
    
    // Financial
    @Default(0) double baseSalary,
    @Default(0) double commissionEarned,
    @Default(0) double incentives,
    @Default(0) double deductions,
    @Default(0) double totalEarnings,
    
    // Quality
    @Default(0) double customerSatisfaction, // 0-100
    @Default(0) int complaints,
    @Default(0) int commendations,
    
    // Daily average
    @Default(0) double avgCollectionsPerDay,
    @Default(0) double avgAmountPerDay,
    @Default(0) double avgDistancePerDay,
    
    // Target
    @Default(0) double targetAmount,
    @Default(0) double targetAchievement,
    
    required PaymentStatus paymentStatus,
    DateTime? paidAt,
  }) = _MonthlyCollectionPerformance;

  factory MonthlyCollectionPerformance.fromJson(Map<String, dynamic> json) =>
      _$MonthlyCollectionPerformanceFromJson(json);
}

@freezed
abstract class LocationTracking with _$LocationTracking {
  const factory LocationTracking({
    required DateTime timestamp,
    required GeoLocation location,
    String? activity, // traveling, visiting, collecting, break
    String? customerId, // If visiting specific customer
  }) = _LocationTracking;

  factory LocationTracking.fromJson(Map<String, dynamic> json) =>
      _$LocationTrackingFromJson(json);
}

/// EMI Due List - Generated automatically for agents
@freezed
abstract class EMIDueList with _$EMIDueList {
  const factory EMIDueList({
    required String id,
    required DateTime generatedAt,
    required String agentId,
    required DateTime forDate,
    
    // List of dues
    @Default([]) List<EMIDueItem> dues,
    
    // Summary
    @Default(0) int totalDues,
    @Default(0) double totalAmount,
    @Default(0) int highPriorityDues, // Overdue by > 15 days
    @Default(0) int mediumPriorityDues, // Overdue by 7-15 days
    @Default(0) int regularDues, // Due today or future
    
    // Status
    @Default(false) bool isCompleted,
    DateTime? completedAt,
    @Default(0) int collectionsMade,
    @Default(0) double collectedAmount,
  }) = _EMIDueList;

  factory EMIDueList.fromJson(Map<String, dynamic> json) =>
      _$EMIDueListFromJson(json);
}

@freezed
abstract class EMIDueItem with _$EMIDueItem {
  const factory EMIDueItem({
    required String customerId,
    required String customerName,
    required String phone,
    required String address,
    required String bookingId,
    required String plotNumber,
    required String colonyName,
    
    // Due Details
    required double emiAmount,
    required DateTime dueDate,
    required int daysOverdue,
    
    // Total Dues
    required double totalDue, // Including late fees
    @Default(0) double lateFee,
    
    // Status
    required DuePriority priority, // High, Medium, Low
    String? lastVisitNotes,
    DateTime? lastVisitDate,
    
    // Collection
    bool? isCollected,
    double? collectedAmount,
    DateTime? collectedAt,
    
    // Location
    GeoLocation? location,
    String? landmark,
    String? preferredTime,
  }) = _EMIDueItem;

  factory EMIDueItem.fromJson(Map<String, dynamic> json) =>
      _$EMIDueItemFromJson(json);
}

enum DuePriority { high, medium, low }

/// Automated EMI Reminder System
@freezed
abstract class EMIReminder with _$EMIReminder {
  const factory EMIReminder({
    required String id,
    required String customerId,
    required String bookingId,
    required String customerName,
    required String phone,
    required double emiAmount,
    required DateTime dueDate,
    
    // Reminder
    required ReminderType type, // SMS, WhatsApp, Call, Email
    required ReminderStatus status, // Scheduled, Sent, Delivered, Failed
    String? messageContent,
    DateTime? scheduledAt,
    DateTime? sentAt,
    DateTime? deliveredAt,
    
    // Response
    bool? isResponded,
    DateTime? respondedAt,
    String? responseType, // WillPay, NeedTime, CannotPay, Paid
    
    // Agent Assignment
    String? assignedAgentId,
    DateTime? agentAssignedAt,
  }) = _EMIReminder;

  factory EMIReminder.fromJson(Map<String, dynamic> json) =>
      _$EMIReminderFromJson(json);
}

enum ReminderType { sms, whatsapp, call, email, push }

enum ReminderStatus { scheduled, sent, delivered, read, failed, bounced }
