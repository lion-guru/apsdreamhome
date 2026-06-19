// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'daily_caller_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$DailyCallerImpl _$$DailyCallerImplFromJson(
  Map<String, dynamic> json,
) => _$DailyCallerImpl(
  id: json['id'] as String,
  name: json['name'] as String,
  phone: json['phone'] as String,
  email: json['email'] as String,
  photoUrl: json['photoUrl'] as String?,
  employeeId: json['employeeId'] as String,
  joiningDate: DateTime.parse(json['joiningDate'] as String),
  callerType: $enumDecode(_$CallerTypeEnumMap, json['callerType']),
  salaryType: $enumDecode(_$SalaryTypeEnumMap, json['salaryType']),
  monthlySalary: (json['monthlySalary'] as num).toDouble(),
  dailyTargetAmount: (json['dailyTargetAmount'] as num?)?.toDouble(),
  dailyCallTarget: (json['dailyCallTarget'] as num?)?.toInt(),
  dailyTalkTimeTarget: (json['dailyTalkTimeTarget'] as num?)?.toInt(),
  commissionPerLead: (json['commissionPerLead'] as num?)?.toDouble(),
  commissionPerBooking: (json['commissionPerBooking'] as num?)?.toDouble(),
  commissionPercentage: (json['commissionPercentage'] as num?)?.toDouble(),
  dailyReports:
      (json['dailyReports'] as List<dynamic>?)
          ?.map((e) => DailyCallReport.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  monthlyReports:
      (json['monthlyReports'] as List<dynamic>?)
          ?.map((e) => MonthlyPerformance.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  currentMonthCalls: (json['currentMonthCalls'] as num?)?.toInt() ?? 0,
  currentMonthConnected: (json['currentMonthConnected'] as num?)?.toInt() ?? 0,
  currentMonthValidLeads:
      (json['currentMonthValidLeads'] as num?)?.toInt() ?? 0,
  currentMonthBookings: (json['currentMonthBookings'] as num?)?.toInt() ?? 0,
  currentMonthRevenue: (json['currentMonthRevenue'] as num?)?.toDouble() ?? 0,
  currentMonthCommission:
      (json['currentMonthCommission'] as num?)?.toDouble() ?? 0,
  currentMonthTalkTimeMinutes:
      (json['currentMonthTalkTimeMinutes'] as num?)?.toInt() ?? 0,
  assignedLeadIds:
      (json['assignedLeadIds'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const [],
  leadAssignments:
      (json['leadAssignments'] as List<dynamic>?)
          ?.map((e) => CallerLeadAssignment.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  status: $enumDecode(_$CallerStatusEnumMap, json['status']),
  lastActiveAt: json['lastActiveAt'] == null
      ? null
      : DateTime.parse(json['lastActiveAt'] as String),
  adminNotes: json['adminNotes'] as String?,
  performanceWarnings:
      (json['performanceWarnings'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const [],
  createdAt: DateTime.parse(json['createdAt'] as String),
  updatedAt: DateTime.parse(json['updatedAt'] as String),
);

Map<String, dynamic> _$$DailyCallerImplToJson(_$DailyCallerImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'phone': instance.phone,
      'email': instance.email,
      'photoUrl': instance.photoUrl,
      'employeeId': instance.employeeId,
      'joiningDate': instance.joiningDate.toIso8601String(),
      'callerType': _$CallerTypeEnumMap[instance.callerType]!,
      'salaryType': _$SalaryTypeEnumMap[instance.salaryType]!,
      'monthlySalary': instance.monthlySalary,
      'dailyTargetAmount': instance.dailyTargetAmount,
      'dailyCallTarget': instance.dailyCallTarget,
      'dailyTalkTimeTarget': instance.dailyTalkTimeTarget,
      'commissionPerLead': instance.commissionPerLead,
      'commissionPerBooking': instance.commissionPerBooking,
      'commissionPercentage': instance.commissionPercentage,
      'dailyReports': instance.dailyReports,
      'monthlyReports': instance.monthlyReports,
      'currentMonthCalls': instance.currentMonthCalls,
      'currentMonthConnected': instance.currentMonthConnected,
      'currentMonthValidLeads': instance.currentMonthValidLeads,
      'currentMonthBookings': instance.currentMonthBookings,
      'currentMonthRevenue': instance.currentMonthRevenue,
      'currentMonthCommission': instance.currentMonthCommission,
      'currentMonthTalkTimeMinutes': instance.currentMonthTalkTimeMinutes,
      'assignedLeadIds': instance.assignedLeadIds,
      'leadAssignments': instance.leadAssignments,
      'status': _$CallerStatusEnumMap[instance.status]!,
      'lastActiveAt': instance.lastActiveAt?.toIso8601String(),
      'adminNotes': instance.adminNotes,
      'performanceWarnings': instance.performanceWarnings,
      'createdAt': instance.createdAt.toIso8601String(),
      'updatedAt': instance.updatedAt.toIso8601String(),
    };

const _$CallerTypeEnumMap = {
  CallerType.fullTime: 'fullTime',
  CallerType.partTime: 'partTime',
  CallerType.freelance: 'freelance',
};

const _$SalaryTypeEnumMap = {
  SalaryType.fixed: 'fixed',
  SalaryType.commissionOnly: 'commissionOnly',
  SalaryType.fixedPlusCommission: 'fixedPlusCommission',
};

const _$CallerStatusEnumMap = {
  CallerStatus.active: 'active',
  CallerStatus.onLeave: 'onLeave',
  CallerStatus.suspended: 'suspended',
  CallerStatus.terminated: 'terminated',
};

_$DailyCallReportImpl _$$DailyCallReportImplFromJson(
  Map<String, dynamic> json,
) => _$DailyCallReportImpl(
  id: json['id'] as String,
  date: DateTime.parse(json['date'] as String),
  totalCalls: (json['totalCalls'] as num?)?.toInt() ?? 0,
  connected: (json['connected'] as num?)?.toInt() ?? 0,
  notAnswered: (json['notAnswered'] as num?)?.toInt() ?? 0,
  busy: (json['busy'] as num?)?.toInt() ?? 0,
  invalidNumber: (json['invalidNumber'] as num?)?.toInt() ?? 0,
  callLater: (json['callLater'] as num?)?.toInt() ?? 0,
  notInterested: (json['notInterested'] as num?)?.toInt() ?? 0,
  totalTalkTimeMinutes: (json['totalTalkTimeMinutes'] as num?)?.toInt() ?? 0,
  avgTalkTimeMinutes: (json['avgTalkTimeMinutes'] as num?)?.toDouble() ?? 0,
  validLeadsGenerated: (json['validLeadsGenerated'] as num?)?.toInt() ?? 0,
  interestedCustomers: (json['interestedCustomers'] as num?)?.toInt() ?? 0,
  siteVisitsScheduled: (json['siteVisitsScheduled'] as num?)?.toInt() ?? 0,
  bookingsConfirmed: (json['bookingsConfirmed'] as num?)?.toInt() ?? 0,
  revenueGenerated: (json['revenueGenerated'] as num?)?.toDouble() ?? 0,
  commissionEarned: (json['commissionEarned'] as num?)?.toDouble() ?? 0,
  callDetails:
      (json['callDetails'] as List<dynamic>?)
          ?.map((e) => CallDetail.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  status: $enumDecode(_$ReportStatusEnumMap, json['status']),
  supervisorNotes: json['supervisorNotes'] as String?,
  submittedAt: json['submittedAt'] == null
      ? null
      : DateTime.parse(json['submittedAt'] as String),
  verifiedAt: json['verifiedAt'] == null
      ? null
      : DateTime.parse(json['verifiedAt'] as String),
);

Map<String, dynamic> _$$DailyCallReportImplToJson(
  _$DailyCallReportImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'date': instance.date.toIso8601String(),
  'totalCalls': instance.totalCalls,
  'connected': instance.connected,
  'notAnswered': instance.notAnswered,
  'busy': instance.busy,
  'invalidNumber': instance.invalidNumber,
  'callLater': instance.callLater,
  'notInterested': instance.notInterested,
  'totalTalkTimeMinutes': instance.totalTalkTimeMinutes,
  'avgTalkTimeMinutes': instance.avgTalkTimeMinutes,
  'validLeadsGenerated': instance.validLeadsGenerated,
  'interestedCustomers': instance.interestedCustomers,
  'siteVisitsScheduled': instance.siteVisitsScheduled,
  'bookingsConfirmed': instance.bookingsConfirmed,
  'revenueGenerated': instance.revenueGenerated,
  'commissionEarned': instance.commissionEarned,
  'callDetails': instance.callDetails,
  'status': _$ReportStatusEnumMap[instance.status]!,
  'supervisorNotes': instance.supervisorNotes,
  'submittedAt': instance.submittedAt?.toIso8601String(),
  'verifiedAt': instance.verifiedAt?.toIso8601String(),
};

const _$ReportStatusEnumMap = {
  ReportStatus.pending: 'pending',
  ReportStatus.submitted: 'submitted',
  ReportStatus.verified: 'verified',
  ReportStatus.rejected: 'rejected',
};

_$CallDetailImpl _$$CallDetailImplFromJson(Map<String, dynamic> json) =>
    _$CallDetailImpl(
      leadId: json['leadId'] as String,
      leadName: json['leadName'] as String,
      leadPhone: json['leadPhone'] as String,
      callTime: DateTime.parse(json['callTime'] as String),
      outcome: $enumDecode(_$CallOutcomeEnumMap, json['outcome']),
      talkTimeSeconds: (json['talkTimeSeconds'] as num?)?.toInt(),
      notes: json['notes'] as String?,
      recordingUrl: json['recordingUrl'] as String?,
      location: json['location'] == null
          ? null
          : GeoLocation.fromJson(json['location'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$$CallDetailImplToJson(_$CallDetailImpl instance) =>
    <String, dynamic>{
      'leadId': instance.leadId,
      'leadName': instance.leadName,
      'leadPhone': instance.leadPhone,
      'callTime': instance.callTime.toIso8601String(),
      'outcome': _$CallOutcomeEnumMap[instance.outcome]!,
      'talkTimeSeconds': instance.talkTimeSeconds,
      'notes': instance.notes,
      'recordingUrl': instance.recordingUrl,
      'location': instance.location,
    };

const _$CallOutcomeEnumMap = {
  CallOutcome.connected: 'connected',
  CallOutcome.notAnswered: 'notAnswered',
  CallOutcome.busy: 'busy',
  CallOutcome.invalid: 'invalid',
  CallOutcome.callLater: 'callLater',
  CallOutcome.notInterested: 'notInterested',
  CallOutcome.interested: 'interested',
  CallOutcome.siteVisitScheduled: 'siteVisitScheduled',
  CallOutcome.bookingDone: 'bookingDone',
  CallOutcome.followUpRequired: 'followUpRequired',
};

_$MonthlyPerformanceImpl _$$MonthlyPerformanceImplFromJson(
  Map<String, dynamic> json,
) => _$MonthlyPerformanceImpl(
  id: json['id'] as String,
  year: (json['year'] as num).toInt(),
  month: (json['month'] as num).toInt(),
  totalCalls: (json['totalCalls'] as num?)?.toInt() ?? 0,
  connectedCalls: (json['connectedCalls'] as num?)?.toInt() ?? 0,
  totalTalkTimeMinutes: (json['totalTalkTimeMinutes'] as num?)?.toInt() ?? 0,
  validLeads: (json['validLeads'] as num?)?.toInt() ?? 0,
  siteVisits: (json['siteVisits'] as num?)?.toInt() ?? 0,
  bookings: (json['bookings'] as num?)?.toInt() ?? 0,
  totalRevenue: (json['totalRevenue'] as num?)?.toDouble() ?? 0,
  baseSalary: (json['baseSalary'] as num?)?.toDouble() ?? 0,
  commissionEarned: (json['commissionEarned'] as num?)?.toDouble() ?? 0,
  incentives: (json['incentives'] as num?)?.toDouble() ?? 0,
  deductions: (json['deductions'] as num?)?.toDouble() ?? 0,
  totalEarnings: (json['totalEarnings'] as num?)?.toDouble() ?? 0,
  targetAchievementPercentage:
      (json['targetAchievementPercentage'] as num?)?.toDouble() ?? 0,
  ranking: (json['ranking'] as num?)?.toInt() ?? 0,
  avgCallsPerDay: (json['avgCallsPerDay'] as num?)?.toDouble() ?? 0,
  avgTalkTimePerDay: (json['avgTalkTimePerDay'] as num?)?.toDouble() ?? 0,
  avgLeadsPerDay: (json['avgLeadsPerDay'] as num?)?.toDouble() ?? 0,
  leadQualityScore: (json['leadQualityScore'] as num?)?.toDouble() ?? 0,
  conversionRate: (json['conversionRate'] as num?)?.toDouble() ?? 0,
  paymentStatus: $enumDecode(_$PaymentStatusEnumMap, json['paymentStatus']),
  paidAt: json['paidAt'] == null
      ? null
      : DateTime.parse(json['paidAt'] as String),
);

Map<String, dynamic> _$$MonthlyPerformanceImplToJson(
  _$MonthlyPerformanceImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'year': instance.year,
  'month': instance.month,
  'totalCalls': instance.totalCalls,
  'connectedCalls': instance.connectedCalls,
  'totalTalkTimeMinutes': instance.totalTalkTimeMinutes,
  'validLeads': instance.validLeads,
  'siteVisits': instance.siteVisits,
  'bookings': instance.bookings,
  'totalRevenue': instance.totalRevenue,
  'baseSalary': instance.baseSalary,
  'commissionEarned': instance.commissionEarned,
  'incentives': instance.incentives,
  'deductions': instance.deductions,
  'totalEarnings': instance.totalEarnings,
  'targetAchievementPercentage': instance.targetAchievementPercentage,
  'ranking': instance.ranking,
  'avgCallsPerDay': instance.avgCallsPerDay,
  'avgTalkTimePerDay': instance.avgTalkTimePerDay,
  'avgLeadsPerDay': instance.avgLeadsPerDay,
  'leadQualityScore': instance.leadQualityScore,
  'conversionRate': instance.conversionRate,
  'paymentStatus': _$PaymentStatusEnumMap[instance.paymentStatus]!,
  'paidAt': instance.paidAt?.toIso8601String(),
};

const _$PaymentStatusEnumMap = {
  PaymentStatus.pending: 'pending',
  PaymentStatus.calculated: 'calculated',
  PaymentStatus.approved: 'approved',
  PaymentStatus.paid: 'paid',
};

_$CallerLeadAssignmentImpl _$$CallerLeadAssignmentImplFromJson(
  Map<String, dynamic> json,
) => _$CallerLeadAssignmentImpl(
  leadId: json['leadId'] as String,
  leadName: json['leadName'] as String,
  leadPhone: json['leadPhone'] as String,
  assignedAt: DateTime.parse(json['assignedAt'] as String),
  assignedBy: json['assignedBy'] as String,
  priority: $enumDecodeNullable(_$AssignmentPriorityEnumMap, json['priority']),
  dueDate: json['dueDate'] == null
      ? null
      : DateTime.parse(json['dueDate'] as String),
  tags:
      (json['tags'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const [],
  notes: json['notes'] as String?,
  isCompleted: json['isCompleted'] as bool? ?? false,
  completedAt: json['completedAt'] == null
      ? null
      : DateTime.parse(json['completedAt'] as String),
  outcome: json['outcome'] as String?,
);

Map<String, dynamic> _$$CallerLeadAssignmentImplToJson(
  _$CallerLeadAssignmentImpl instance,
) => <String, dynamic>{
  'leadId': instance.leadId,
  'leadName': instance.leadName,
  'leadPhone': instance.leadPhone,
  'assignedAt': instance.assignedAt.toIso8601String(),
  'assignedBy': instance.assignedBy,
  'priority': _$AssignmentPriorityEnumMap[instance.priority],
  'dueDate': instance.dueDate?.toIso8601String(),
  'tags': instance.tags,
  'notes': instance.notes,
  'isCompleted': instance.isCompleted,
  'completedAt': instance.completedAt?.toIso8601String(),
  'outcome': instance.outcome,
};

const _$AssignmentPriorityEnumMap = {
  AssignmentPriority.high: 'high',
  AssignmentPriority.medium: 'medium',
  AssignmentPriority.low: 'low',
};

_$LeadDistributionBatchImpl _$$LeadDistributionBatchImplFromJson(
  Map<String, dynamic> json,
) => _$LeadDistributionBatchImpl(
  id: json['id'] as String,
  batchName: json['batchName'] as String,
  createdAt: DateTime.parse(json['createdAt'] as String),
  createdBy: json['createdBy'] as String,
  leadSourceIds:
      (json['leadSourceIds'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const [],
  importedLeads:
      (json['importedLeads'] as List<dynamic>?)
          ?.map((e) => e as Map<String, dynamic>)
          .toList() ??
      const [],
  assignedCallerIds:
      (json['assignedCallerIds'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const [],
  leadsPerCaller: (json['leadsPerCaller'] as num?)?.toInt(),
  method:
      $enumDecodeNullable(_$DistributionMethodEnumMap, json['method']) ??
      DistributionMethod.equal,
  status: $enumDecode(_$DistributionStatusEnumMap, json['status']),
  distributedAt: json['distributedAt'] == null
      ? null
      : DateTime.parse(json['distributedAt'] as String),
  distributedLeadIds:
      (json['distributedLeadIds'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const [],
  totalLeads: (json['totalLeads'] as num?)?.toInt() ?? 0,
);

Map<String, dynamic> _$$LeadDistributionBatchImplToJson(
  _$LeadDistributionBatchImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'batchName': instance.batchName,
  'createdAt': instance.createdAt.toIso8601String(),
  'createdBy': instance.createdBy,
  'leadSourceIds': instance.leadSourceIds,
  'importedLeads': instance.importedLeads,
  'assignedCallerIds': instance.assignedCallerIds,
  'leadsPerCaller': instance.leadsPerCaller,
  'method': _$DistributionMethodEnumMap[instance.method]!,
  'status': _$DistributionStatusEnumMap[instance.status]!,
  'distributedAt': instance.distributedAt?.toIso8601String(),
  'distributedLeadIds': instance.distributedLeadIds,
  'totalLeads': instance.totalLeads,
};

const _$DistributionMethodEnumMap = {
  DistributionMethod.equal: 'equal',
  DistributionMethod.priorityBased: 'priorityBased',
  DistributionMethod.random: 'random',
  DistributionMethod.roundRobin: 'roundRobin',
};

const _$DistributionStatusEnumMap = {
  DistributionStatus.pending: 'pending',
  DistributionStatus.inProgress: 'inProgress',
  DistributionStatus.completed: 'completed',
};
