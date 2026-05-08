import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'daily_caller_model.freezed.dart';
part 'daily_caller_model.g.dart';

/// GeoPoint JSON Converter for Firestore
class GeoPointJsonConverter implements JsonConverter<GeoPoint, Map<String, dynamic>> {
  const GeoPointJsonConverter();

  @override
  GeoPoint fromJson(Map<String, dynamic> json) {
    return GeoPoint(
      (json['latitude'] as num).toDouble(),
      (json['longitude'] as num).toDouble(),
    );
  }

  @override
  Map<String, dynamic> toJson(GeoPoint geoPoint) {
    return {
      'latitude': geoPoint.latitude,
      'longitude': geoPoint.longitude,
    };
  }
}

/// Daily Caller (Telesales) Model
/// For tracking calling activity, salary + commission
@freezed
class DailyCaller with _$DailyCaller {
  const DailyCaller._();

  const factory DailyCaller({
    required String id,
    required String name,
    required String phone,
    required String email,
    String? photoUrl,
    
    // Employment Details
    required String employeeId,
    required DateTime joiningDate,
    required CallerType callerType, // FullTime, PartTime, Freelance
    required SalaryType salaryType, // Fixed, CommissionOnly, FixedPlusCommission
    
    // Salary Structure
    required double monthlySalary,
    double? dailyTargetAmount, // Sales target per day
    int? dailyCallTarget, // Minimum calls per day
    int? dailyTalkTimeTarget, // Minutes per day
    
    // Commission Structure
    double? commissionPerLead, // Per valid lead
    double? commissionPerBooking, // Per booking conversion
    double? commissionPercentage, // % of booking value
    
    // Performance Tracking
    @Default([]) List<DailyCallReport> dailyReports,
    @Default([]) List<MonthlyPerformance> monthlyReports,
    
    // Current Month Stats (Auto-calculated)
    @Default(0) int currentMonthCalls,
    @Default(0) int currentMonthConnected,
    @Default(0) int currentMonthValidLeads,
    @Default(0) int currentMonthBookings,
    @Default(0) double currentMonthRevenue,
    @Default(0) double currentMonthCommission,
    @Default(0) int currentMonthTalkTimeMinutes,
    
    // Assigned Leads
    @Default([]) List<String> assignedLeadIds,
    @Default([]) List<CallerLeadAssignment> leadAssignments,
    
    // Status
    required CallerStatus status, // Active, OnLeave, Suspended, Terminated
    DateTime? lastActiveAt,
    
    // Admin Notes
    String? adminNotes,
    @Default([]) List<String> performanceWarnings,
    
    required DateTime createdAt,
    required DateTime updatedAt,
  }) = _DailyCaller;

  factory DailyCaller.fromJson(Map<String, dynamic> json) =>
      _$DailyCallerFromJson(json);

  factory DailyCaller.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return DailyCaller(
      id: doc.id,
      name: (data['name'] as String?) ?? '',
      phone: (data['phone'] as String?) ?? '',
      email: (data['email'] as String?) ?? '',
      photoUrl: data['photoUrl'] as String?,
      employeeId: (data['employeeId'] as String?) ?? '',
      joiningDate: (data['joiningDate'] as Timestamp?)?.toDate() ?? DateTime.now(),
      callerType: CallerType.values.firstWhere(
        (e) => e.name == data['callerType'],
        orElse: () => CallerType.fullTime,
      ),
      salaryType: SalaryType.values.firstWhere(
        (e) => e.name == data['salaryType'],
        orElse: () => SalaryType.fixedPlusCommission,
      ),
      monthlySalary: (data['monthlySalary'] as num? ?? 0).toDouble(),
      dailyTargetAmount: (data['dailyTargetAmount'] as num?)?.toDouble(),
      dailyCallTarget: data['dailyCallTarget'] as int?,
      dailyTalkTimeTarget: data['dailyTalkTimeTarget'] as int?,
      commissionPerLead: (data['commissionPerLead'] as num?)?.toDouble(),
      commissionPerBooking: (data['commissionPerBooking'] as num?)?.toDouble(),
      commissionPercentage: (data['commissionPercentage'] as num?)?.toDouble(),
      dailyReports: (data['dailyReports'] as List<dynamic>?)
              ?.map((e) => DailyCallReport.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      monthlyReports: (data['monthlyReports'] as List<dynamic>?)
              ?.map((e) => MonthlyPerformance.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      currentMonthCalls: data['currentMonthCalls'] as int? ?? 0,
      currentMonthConnected: data['currentMonthConnected'] as int? ?? 0,
      currentMonthValidLeads: data['currentMonthValidLeads'] as int? ?? 0,
      currentMonthBookings: data['currentMonthBookings'] as int? ?? 0,
      currentMonthRevenue: (data['currentMonthRevenue'] as num? ?? 0).toDouble(),
      currentMonthCommission: (data['currentMonthCommission'] as num? ?? 0).toDouble(),
      currentMonthTalkTimeMinutes: data['currentMonthTalkTimeMinutes'] as int? ?? 0,
      assignedLeadIds: List<String>.from((data['assignedLeadIds'] as Iterable?) ?? []),
      leadAssignments: (data['leadAssignments'] as List<dynamic>?)
              ?.map((e) => CallerLeadAssignment.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      status: CallerStatus.values.firstWhere(
        (e) => e.name == data['status'],
        orElse: () => CallerStatus.active,
      ),
      lastActiveAt: (data['lastActiveAt'] as Timestamp?)?.toDate(),
      adminNotes: data['adminNotes'] as String?,
      performanceWarnings: List<String>.from((data['performanceWarnings'] as Iterable?) ?? []),
      createdAt: (data['createdAt'] as Timestamp?)?.toDate() ?? DateTime.now(),
      updatedAt: (data['updatedAt'] as Timestamp?)?.toDate() ?? DateTime.now(),
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'name': name,
      'phone': phone,
      'email': email,
      'photoUrl': photoUrl,
      'employeeId': employeeId,
      'joiningDate': Timestamp.fromDate(joiningDate),
      'callerType': callerType.name,
      'salaryType': salaryType.name,
      'monthlySalary': monthlySalary,
      'dailyTargetAmount': dailyTargetAmount,
      'dailyCallTarget': dailyCallTarget,
      'dailyTalkTimeTarget': dailyTalkTimeTarget,
      'commissionPerLead': commissionPerLead,
      'commissionPerBooking': commissionPerBooking,
      'commissionPercentage': commissionPercentage,
      'dailyReports': dailyReports.map((e) => e.toJson()).toList(),
      'monthlyReports': monthlyReports.map((e) => e.toJson()).toList(),
      'currentMonthCalls': currentMonthCalls,
      'currentMonthConnected': currentMonthConnected,
      'currentMonthValidLeads': currentMonthValidLeads,
      'currentMonthBookings': currentMonthBookings,
      'currentMonthRevenue': currentMonthRevenue,
      'currentMonthCommission': currentMonthCommission,
      'currentMonthTalkTimeMinutes': currentMonthTalkTimeMinutes,
      'assignedLeadIds': assignedLeadIds,
      'leadAssignments': leadAssignments.map((e) => e.toJson()).toList(),
      'status': status.name,
      'lastActiveAt': lastActiveAt != null ? Timestamp.fromDate(lastActiveAt!) : null,
      'adminNotes': adminNotes,
      'performanceWarnings': performanceWarnings,
      'createdAt': Timestamp.fromDate(createdAt),
      'updatedAt': Timestamp.fromDate(updatedAt),
    };
  }
}

enum CallerType { fullTime, partTime, freelance }

enum SalaryType { fixed, commissionOnly, fixedPlusCommission }

enum CallerStatus { active, onLeave, suspended, terminated }

@freezed
class DailyCallReport with _$DailyCallReport {
  const factory DailyCallReport({
    required String id,
    required DateTime date,
    
    // Call Statistics
    @Default(0) int totalCalls,
    @Default(0) int connected,
    @Default(0) int notAnswered,
    @Default(0) int busy,
    @Default(0) int invalidNumber,
    @Default(0) int callLater,
    @Default(0) int notInterested,
    
    // Talk Time
    @Default(0) int totalTalkTimeMinutes,
    @Default(0) double avgTalkTimeMinutes,
    
    // Lead Generation
    @Default(0) int validLeadsGenerated,
    @Default(0) int interestedCustomers,
    @Default(0) int siteVisitsScheduled,
    @Default(0) int bookingsConfirmed,
    
    // Financial
    @Default(0) double revenueGenerated,
    @Default(0) double commissionEarned,
    
    // Detailed Log
    @Default([]) List<CallDetail> callDetails,
    
    // Status
    required ReportStatus status, // Pending, Submitted, Verified
    String? supervisorNotes,
    DateTime? submittedAt,
    DateTime? verifiedAt,
  }) = _DailyCallReport;

  factory DailyCallReport.fromJson(Map<String, dynamic> json) =>
      _$DailyCallReportFromJson(json);
}

enum ReportStatus { pending, submitted, verified, rejected }

@freezed
class CallDetail with _$CallDetail {
  const factory CallDetail({
    required String leadId,
    required String leadName,
    required String leadPhone,
    required DateTime callTime,
    required CallOutcome outcome,
    int? talkTimeSeconds,
    String? notes,
    String? recordingUrl, // Call recording
    @GeoPointJsonConverter() GeoPoint? location, // Location of caller when called
  }) = _CallDetail;

  factory CallDetail.fromJson(Map<String, dynamic> json) =>
      _$CallDetailFromJson(json);
}

enum CallOutcome {
  connected,
  notAnswered,
  busy,
  invalid,
  callLater,
  notInterested,
  interested,
  siteVisitScheduled,
  bookingDone,
  followUpRequired,
}

@freezed
class MonthlyPerformance with _$MonthlyPerformance {
  const factory MonthlyPerformance({
    required String id,
    required int year,
    required int month,
    
    // Call Stats
    @Default(0) int totalCalls,
    @Default(0) int connectedCalls,
    @Default(0) int totalTalkTimeMinutes,
    
    // Lead & Sales
    @Default(0) int validLeads,
    @Default(0) int siteVisits,
    @Default(0) int bookings,
    @Default(0) double totalRevenue,
    
    // Financial
    @Default(0) double baseSalary,
    @Default(0) double commissionEarned,
    @Default(0) double incentives,
    @Default(0) double deductions,
    @Default(0) double totalEarnings,
    
    // Target Achievement
    @Default(0) double targetAchievementPercentage,
    @Default(0) int ranking, // Among all callers
    
    // Daily average
    @Default(0) double avgCallsPerDay,
    @Default(0) double avgTalkTimePerDay,
    @Default(0) double avgLeadsPerDay,
    
    // Quality metrics
    @Default(0) double leadQualityScore, // 0-100
    @Default(0) double conversionRate,
    
    required PaymentStatus paymentStatus,
    DateTime? paidAt,
  }) = _MonthlyPerformance;

  factory MonthlyPerformance.fromJson(Map<String, dynamic> json) =>
      _$MonthlyPerformanceFromJson(json);
}

enum PaymentStatus { pending, calculated, approved, paid }

@freezed
class CallerLeadAssignment with _$CallerLeadAssignment {
  const factory CallerLeadAssignment({
    required String leadId,
    required String leadName,
    required String leadPhone,
    required DateTime assignedAt,
    required String assignedBy,
    AssignmentPriority? priority, // High, Medium, Low
    DateTime? dueDate,
    @Default([]) List<String> tags,
    String? notes,
    @Default(false) bool isCompleted,
    DateTime? completedAt,
    String? outcome,
  }) = _CallerLeadAssignment;

  factory CallerLeadAssignment.fromJson(Map<String, dynamic> json) =>
      _$CallerLeadAssignmentFromJson(json);
}

enum AssignmentPriority { high, medium, low }

/// Lead Distribution System for Daily Callers
@freezed
class LeadDistributionBatch with _$LeadDistributionBatch {
  const factory LeadDistributionBatch({
    required String id,
    required String batchName,
    required DateTime createdAt,
    required String createdBy,
    
    // Lead Sources
    @Default([]) List<String> leadSourceIds, // From campaigns, website, etc.
    @Default([]) List<Map<String, dynamic>> importedLeads,
    
    // Distribution
    @Default([]) List<String> assignedCallerIds,
    int? leadsPerCaller,
    @Default(DistributionMethod.equal) DistributionMethod method, // Equal, PriorityBased, Random, RoundRobin
    
    // Status
    required DistributionStatus status,
    DateTime? distributedAt,
    
    // Results
    @Default([]) List<String> distributedLeadIds,
    @Default(0) int totalLeads,
  }) = _LeadDistributionBatch;

  factory LeadDistributionBatch.fromJson(Map<String, dynamic> json) =>
      _$LeadDistributionBatchFromJson(json);
}

enum DistributionMethod { equal, priorityBased, random, roundRobin }

enum DistributionStatus { pending, inProgress, completed }
