// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'emi_collection_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_EMICollectionAgent _$EMICollectionAgentFromJson(Map<String, dynamic> json) =>
    _EMICollectionAgent(
      id: json['id'] as String,
      name: json['name'] as String,
      phone: json['phone'] as String,
      email: json['email'] as String,
      photoUrl: json['photoUrl'] as String?,
      aadharNumber: json['aadharNumber'] as String?,
      address: json['address'] as String?,
      employeeId: json['employeeId'] as String,
      joiningDate: DateTime.parse(json['joiningDate'] as String),
      agentType: $enumDecode(_$CollectionAgentTypeEnumMap, json['agentType']),
      assignedArea: CollectionArea.fromJson(
        json['assignedArea'] as Map<String, dynamic>,
      ),
      monthlySalary: (json['monthlySalary'] as num).toDouble(),
      commissionPerCollection: (json['commissionPerCollection'] as num?)
          ?.toDouble(),
      commissionPercentage: (json['commissionPercentage'] as num?)?.toDouble(),
      incentivePerTarget: (json['incentivePerTarget'] as num?)?.toDouble(),
      assignedCustomerIds:
          (json['assignedCustomerIds'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
      customerAssignments:
          (json['customerAssignments'] as List<dynamic>?)
              ?.map(
                (e) =>
                    EMICustomerAssignment.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          const [],
      dailyReports:
          (json['dailyReports'] as List<dynamic>?)
              ?.map(
                (e) =>
                    DailyCollectionReport.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          const [],
      monthlyReports:
          (json['monthlyReports'] as List<dynamic>?)
              ?.map(
                (e) => MonthlyCollectionPerformance.fromJson(
                  e as Map<String, dynamic>,
                ),
              )
              .toList() ??
          const [],
      currentMonthCollections:
          (json['currentMonthCollections'] as num?)?.toInt() ?? 0,
      currentMonthAmount: (json['currentMonthAmount'] as num?)?.toDouble() ?? 0,
      currentMonthCommission:
          (json['currentMonthCommission'] as num?)?.toDouble() ?? 0,
      currentMonthTarget: (json['currentMonthTarget'] as num?)?.toInt() ?? 0,
      targetAchievement: (json['targetAchievement'] as num?)?.toDouble() ?? 0,
      locationHistory:
          (json['locationHistory'] as List<dynamic>?)
              ?.map((e) => LocationTracking.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const [],
      isCurrentlyActive: json['isCurrentlyActive'] as bool?,
      lastLocation: json['lastLocation'] == null
          ? null
          : GeoLocation.fromJson(json['lastLocation'] as Map<String, dynamic>),
      lastLocationUpdate: json['lastLocationUpdate'] == null
          ? null
          : DateTime.parse(json['lastLocationUpdate'] as String),
      status: $enumDecode(_$AgentStatusEnumMap, json['status']),
      lastActiveAt: json['lastActiveAt'] == null
          ? null
          : DateTime.parse(json['lastActiveAt'] as String),
      documentUrls:
          (json['documentUrls'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
      createdAt: DateTime.parse(json['createdAt'] as String),
      updatedAt: DateTime.parse(json['updatedAt'] as String),
    );

Map<String, dynamic> _$EMICollectionAgentToJson(_EMICollectionAgent instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'phone': instance.phone,
      'email': instance.email,
      'photoUrl': instance.photoUrl,
      'aadharNumber': instance.aadharNumber,
      'address': instance.address,
      'employeeId': instance.employeeId,
      'joiningDate': instance.joiningDate.toIso8601String(),
      'agentType': _$CollectionAgentTypeEnumMap[instance.agentType]!,
      'assignedArea': instance.assignedArea,
      'monthlySalary': instance.monthlySalary,
      'commissionPerCollection': instance.commissionPerCollection,
      'commissionPercentage': instance.commissionPercentage,
      'incentivePerTarget': instance.incentivePerTarget,
      'assignedCustomerIds': instance.assignedCustomerIds,
      'customerAssignments': instance.customerAssignments,
      'dailyReports': instance.dailyReports,
      'monthlyReports': instance.monthlyReports,
      'currentMonthCollections': instance.currentMonthCollections,
      'currentMonthAmount': instance.currentMonthAmount,
      'currentMonthCommission': instance.currentMonthCommission,
      'currentMonthTarget': instance.currentMonthTarget,
      'targetAchievement': instance.targetAchievement,
      'locationHistory': instance.locationHistory,
      'isCurrentlyActive': instance.isCurrentlyActive,
      'lastLocation': instance.lastLocation,
      'lastLocationUpdate': instance.lastLocationUpdate?.toIso8601String(),
      'status': _$AgentStatusEnumMap[instance.status]!,
      'lastActiveAt': instance.lastActiveAt?.toIso8601String(),
      'documentUrls': instance.documentUrls,
      'createdAt': instance.createdAt.toIso8601String(),
      'updatedAt': instance.updatedAt.toIso8601String(),
    };

const _$CollectionAgentTypeEnumMap = {
  CollectionAgentType.fullTime: 'fullTime',
  CollectionAgentType.partTime: 'partTime',
  CollectionAgentType.freelance: 'freelance',
  CollectionAgentType.contractor: 'contractor',
};

const _$AgentStatusEnumMap = {
  AgentStatus.active: 'active',
  AgentStatus.onLeave: 'onLeave',
  AgentStatus.suspended: 'suspended',
  AgentStatus.terminated: 'terminated',
};

_CollectionArea _$CollectionAreaFromJson(
  Map<String, dynamic> json,
) => _CollectionArea(
  areaName: json['areaName'] as String,
  state: json['state'] as String,
  district: json['district'] as String,
  city: json['city'] as String,
  colonies:
      (json['colonies'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const [],
  pincodes:
      (json['pincodes'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const [],
  areaManagerId: json['areaManagerId'] as String?,
);

Map<String, dynamic> _$CollectionAreaToJson(_CollectionArea instance) =>
    <String, dynamic>{
      'areaName': instance.areaName,
      'state': instance.state,
      'district': instance.district,
      'city': instance.city,
      'colonies': instance.colonies,
      'pincodes': instance.pincodes,
      'areaManagerId': instance.areaManagerId,
    };

_EMICustomerAssignment _$EMICustomerAssignmentFromJson(
  Map<String, dynamic> json,
) => _EMICustomerAssignment(
  customerId: json['customerId'] as String,
  customerName: json['customerName'] as String,
  customerPhone: json['customerPhone'] as String,
  customerAddress: json['customerAddress'] as String,
  bookingId: json['bookingId'] as String,
  plotNumber: json['plotNumber'] as String,
  colonyName: json['colonyName'] as String,
  monthlyEMI: (json['monthlyEMI'] as num).toDouble(),
  totalEMIs: (json['totalEMIs'] as num).toInt(),
  paidEMIs: (json['paidEMIs'] as num).toInt(),
  pendingEMIs: (json['pendingEMIs'] as num).toInt(),
  totalDue: (json['totalDue'] as num).toDouble(),
  dueDay: (json['dueDay'] as num).toInt(),
  nextDueDate: json['nextDueDate'] == null
      ? null
      : DateTime.parse(json['nextDueDate'] as String),
  paymentStatus:
      $enumDecodeNullable(_$PaymentStatusEnumMap, json['paymentStatus']) ??
      PaymentStatus.regular,
  isHighPriority: json['isHighPriority'] as bool? ?? false,
  preferredCollectionTime: json['preferredCollectionTime'] as String?,
  landmark: json['landmark'] as String?,
  location: json['location'] == null
      ? null
      : GeoLocation.fromJson(json['location'] as Map<String, dynamic>),
  visitHistory:
      (json['visitHistory'] as List<dynamic>?)
          ?.map((e) => PreviousVisit.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  specialInstructions: json['specialInstructions'] as String?,
  assignedAt: json['assignedAt'] == null
      ? null
      : DateTime.parse(json['assignedAt'] as String),
  lastCollectedAt: json['lastCollectedAt'] == null
      ? null
      : DateTime.parse(json['lastCollectedAt'] as String),
);

Map<String, dynamic> _$EMICustomerAssignmentToJson(
  _EMICustomerAssignment instance,
) => <String, dynamic>{
  'customerId': instance.customerId,
  'customerName': instance.customerName,
  'customerPhone': instance.customerPhone,
  'customerAddress': instance.customerAddress,
  'bookingId': instance.bookingId,
  'plotNumber': instance.plotNumber,
  'colonyName': instance.colonyName,
  'monthlyEMI': instance.monthlyEMI,
  'totalEMIs': instance.totalEMIs,
  'paidEMIs': instance.paidEMIs,
  'pendingEMIs': instance.pendingEMIs,
  'totalDue': instance.totalDue,
  'dueDay': instance.dueDay,
  'nextDueDate': instance.nextDueDate?.toIso8601String(),
  'paymentStatus': _$PaymentStatusEnumMap[instance.paymentStatus]!,
  'isHighPriority': instance.isHighPriority,
  'preferredCollectionTime': instance.preferredCollectionTime,
  'landmark': instance.landmark,
  'location': instance.location,
  'visitHistory': instance.visitHistory,
  'specialInstructions': instance.specialInstructions,
  'assignedAt': instance.assignedAt?.toIso8601String(),
  'lastCollectedAt': instance.lastCollectedAt?.toIso8601String(),
};

const _$PaymentStatusEnumMap = {
  PaymentStatus.regular: 'regular',
  PaymentStatus.irregular: 'irregular',
  PaymentStatus.defaulter: 'defaulter',
  PaymentStatus.newCustomer: 'newCustomer',
};

_PreviousVisit _$PreviousVisitFromJson(Map<String, dynamic> json) =>
    _PreviousVisit(
      visitDate: DateTime.parse(json['visitDate'] as String),
      outcome: $enumDecode(_$VisitOutcomeEnumMap, json['outcome']),
      amountCollected: (json['amountCollected'] as num?)?.toDouble(),
      notes: json['notes'] as String?,
      customerFeedback: json['customerFeedback'] as String?,
    );

Map<String, dynamic> _$PreviousVisitToJson(_PreviousVisit instance) =>
    <String, dynamic>{
      'visitDate': instance.visitDate.toIso8601String(),
      'outcome': _$VisitOutcomeEnumMap[instance.outcome]!,
      'amountCollected': instance.amountCollected,
      'notes': instance.notes,
      'customerFeedback': instance.customerFeedback,
    };

const _$VisitOutcomeEnumMap = {
  VisitOutcome.collected: 'collected',
  VisitOutcome.partial: 'partial',
  VisitOutcome.notHome: 'notHome',
  VisitOutcome.refused: 'refused',
  VisitOutcome.willPayLater: 'willPayLater',
  VisitOutcome.rescheduled: 'rescheduled',
  VisitOutcome.notInterested: 'notInterested',
};

_DailyCollectionReport _$DailyCollectionReportFromJson(
  Map<String, dynamic> json,
) => _DailyCollectionReport(
  id: json['id'] as String,
  date: DateTime.parse(json['date'] as String),
  agentId: json['agentId'] as String,
  totalVisits: (json['totalVisits'] as num?)?.toInt() ?? 0,
  successfulCollections: (json['successfulCollections'] as num?)?.toInt() ?? 0,
  partialCollections: (json['partialCollections'] as num?)?.toInt() ?? 0,
  failedVisits: (json['failedVisits'] as num?)?.toInt() ?? 0,
  rescheduled: (json['rescheduled'] as num?)?.toInt() ?? 0,
  customersNotHome: (json['customersNotHome'] as num?)?.toInt() ?? 0,
  totalCollected: (json['totalCollected'] as num?)?.toDouble() ?? 0,
  cashCollected: (json['cashCollected'] as num?)?.toDouble() ?? 0,
  chequeCollected: (json['chequeCollected'] as num?)?.toDouble() ?? 0,
  onlineCollected: (json['onlineCollected'] as num?)?.toDouble() ?? 0,
  upiCollected: (json['upiCollected'] as num?)?.toDouble() ?? 0,
  collections:
      (json['collections'] as List<dynamic>?)
          ?.map((e) => CollectionRecord.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  startTime: json['startTime'] == null
      ? null
      : DateTime.parse(json['startTime'] as String),
  endTime: json['endTime'] == null
      ? null
      : DateTime.parse(json['endTime'] as String),
  workingHours: (json['workingHours'] as num?)?.toInt() ?? 0,
  routeTaken:
      (json['routeTaken'] as List<dynamic>?)
          ?.map((e) => LocationTracking.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  totalDistanceKm: (json['totalDistanceKm'] as num?)?.toDouble() ?? 0,
  submissionStatus: $enumDecode(
    _$ReportSubmissionStatusEnumMap,
    json['submissionStatus'],
  ),
  submittedAt: json['submittedAt'] == null
      ? null
      : DateTime.parse(json['submittedAt'] as String),
  adminNotes: json['adminNotes'] as String?,
);

Map<String, dynamic> _$DailyCollectionReportToJson(
  _DailyCollectionReport instance,
) => <String, dynamic>{
  'id': instance.id,
  'date': instance.date.toIso8601String(),
  'agentId': instance.agentId,
  'totalVisits': instance.totalVisits,
  'successfulCollections': instance.successfulCollections,
  'partialCollections': instance.partialCollections,
  'failedVisits': instance.failedVisits,
  'rescheduled': instance.rescheduled,
  'customersNotHome': instance.customersNotHome,
  'totalCollected': instance.totalCollected,
  'cashCollected': instance.cashCollected,
  'chequeCollected': instance.chequeCollected,
  'onlineCollected': instance.onlineCollected,
  'upiCollected': instance.upiCollected,
  'collections': instance.collections,
  'startTime': instance.startTime?.toIso8601String(),
  'endTime': instance.endTime?.toIso8601String(),
  'workingHours': instance.workingHours,
  'routeTaken': instance.routeTaken,
  'totalDistanceKm': instance.totalDistanceKm,
  'submissionStatus':
      _$ReportSubmissionStatusEnumMap[instance.submissionStatus]!,
  'submittedAt': instance.submittedAt?.toIso8601String(),
  'adminNotes': instance.adminNotes,
};

const _$ReportSubmissionStatusEnumMap = {
  ReportSubmissionStatus.pending: 'pending',
  ReportSubmissionStatus.submitted: 'submitted',
  ReportSubmissionStatus.verified: 'verified',
  ReportSubmissionStatus.disputed: 'disputed',
};

_CollectionRecord _$CollectionRecordFromJson(Map<String, dynamic> json) =>
    _CollectionRecord(
      customerId: json['customerId'] as String,
      customerName: json['customerName'] as String,
      bookingId: json['bookingId'] as String,
      collectionTime: DateTime.parse(json['collectionTime'] as String),
      amount: (json['amount'] as num).toDouble(),
      mode: $enumDecode(_$PaymentModeEnumMap, json['mode']),
      emiNumber: (json['emiNumber'] as num?)?.toInt(),
      lateFee: (json['lateFee'] as num?)?.toDouble(),
      chequeNumber: json['chequeNumber'] as String?,
      transactionId: json['transactionId'] as String?,
      receiptNumber: json['receiptNumber'] as String?,
      location: json['location'] == null
          ? null
          : GeoLocation.fromJson(json['location'] as Map<String, dynamic>),
      addressAtCollection: json['addressAtCollection'] as String?,
      photoUrls:
          (json['photoUrls'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
      signatureUrl: json['signatureUrl'] as String?,
      notes: json['notes'] as String?,
      isVerified: json['isVerified'] as bool?,
      verifiedAt: json['verifiedAt'] == null
          ? null
          : DateTime.parse(json['verifiedAt'] as String),
      verifiedBy: json['verifiedBy'] as String?,
      disputeReason: json['disputeReason'] as String?,
    );

Map<String, dynamic> _$CollectionRecordToJson(_CollectionRecord instance) =>
    <String, dynamic>{
      'customerId': instance.customerId,
      'customerName': instance.customerName,
      'bookingId': instance.bookingId,
      'collectionTime': instance.collectionTime.toIso8601String(),
      'amount': instance.amount,
      'mode': _$PaymentModeEnumMap[instance.mode]!,
      'emiNumber': instance.emiNumber,
      'lateFee': instance.lateFee,
      'chequeNumber': instance.chequeNumber,
      'transactionId': instance.transactionId,
      'receiptNumber': instance.receiptNumber,
      'location': instance.location,
      'addressAtCollection': instance.addressAtCollection,
      'photoUrls': instance.photoUrls,
      'signatureUrl': instance.signatureUrl,
      'notes': instance.notes,
      'isVerified': instance.isVerified,
      'verifiedAt': instance.verifiedAt?.toIso8601String(),
      'verifiedBy': instance.verifiedBy,
      'disputeReason': instance.disputeReason,
    };

const _$PaymentModeEnumMap = {
  PaymentMode.cash: 'cash',
  PaymentMode.cheque: 'cheque',
  PaymentMode.online: 'online',
  PaymentMode.upi: 'upi',
  PaymentMode.card: 'card',
  PaymentMode.bankTransfer: 'bankTransfer',
};

_MonthlyCollectionPerformance _$MonthlyCollectionPerformanceFromJson(
  Map<String, dynamic> json,
) => _MonthlyCollectionPerformance(
  id: json['id'] as String,
  year: (json['year'] as num).toInt(),
  month: (json['month'] as num).toInt(),
  agentId: json['agentId'] as String,
  totalCollections: (json['totalCollections'] as num?)?.toInt() ?? 0,
  totalAmount: (json['totalAmount'] as num?)?.toDouble() ?? 0,
  totalCustomers: (json['totalCustomers'] as num?)?.toInt() ?? 0,
  newCustomersAdded: (json['newCustomersAdded'] as num?)?.toInt() ?? 0,
  collectionRate: (json['collectionRate'] as num?)?.toDouble() ?? 0,
  successRate: (json['successRate'] as num?)?.toDouble() ?? 0,
  ranking: (json['ranking'] as num?)?.toInt() ?? 0,
  baseSalary: (json['baseSalary'] as num?)?.toDouble() ?? 0,
  commissionEarned: (json['commissionEarned'] as num?)?.toDouble() ?? 0,
  incentives: (json['incentives'] as num?)?.toDouble() ?? 0,
  deductions: (json['deductions'] as num?)?.toDouble() ?? 0,
  totalEarnings: (json['totalEarnings'] as num?)?.toDouble() ?? 0,
  customerSatisfaction: (json['customerSatisfaction'] as num?)?.toDouble() ?? 0,
  complaints: (json['complaints'] as num?)?.toInt() ?? 0,
  commendations: (json['commendations'] as num?)?.toInt() ?? 0,
  avgCollectionsPerDay: (json['avgCollectionsPerDay'] as num?)?.toDouble() ?? 0,
  avgAmountPerDay: (json['avgAmountPerDay'] as num?)?.toDouble() ?? 0,
  avgDistancePerDay: (json['avgDistancePerDay'] as num?)?.toDouble() ?? 0,
  targetAmount: (json['targetAmount'] as num?)?.toDouble() ?? 0,
  targetAchievement: (json['targetAchievement'] as num?)?.toDouble() ?? 0,
  paymentStatus: $enumDecode(_$PaymentStatusEnumMap, json['paymentStatus']),
  paidAt: json['paidAt'] == null
      ? null
      : DateTime.parse(json['paidAt'] as String),
);

Map<String, dynamic> _$MonthlyCollectionPerformanceToJson(
  _MonthlyCollectionPerformance instance,
) => <String, dynamic>{
  'id': instance.id,
  'year': instance.year,
  'month': instance.month,
  'agentId': instance.agentId,
  'totalCollections': instance.totalCollections,
  'totalAmount': instance.totalAmount,
  'totalCustomers': instance.totalCustomers,
  'newCustomersAdded': instance.newCustomersAdded,
  'collectionRate': instance.collectionRate,
  'successRate': instance.successRate,
  'ranking': instance.ranking,
  'baseSalary': instance.baseSalary,
  'commissionEarned': instance.commissionEarned,
  'incentives': instance.incentives,
  'deductions': instance.deductions,
  'totalEarnings': instance.totalEarnings,
  'customerSatisfaction': instance.customerSatisfaction,
  'complaints': instance.complaints,
  'commendations': instance.commendations,
  'avgCollectionsPerDay': instance.avgCollectionsPerDay,
  'avgAmountPerDay': instance.avgAmountPerDay,
  'avgDistancePerDay': instance.avgDistancePerDay,
  'targetAmount': instance.targetAmount,
  'targetAchievement': instance.targetAchievement,
  'paymentStatus': _$PaymentStatusEnumMap[instance.paymentStatus]!,
  'paidAt': instance.paidAt?.toIso8601String(),
};

_LocationTracking _$LocationTrackingFromJson(Map<String, dynamic> json) =>
    _LocationTracking(
      timestamp: DateTime.parse(json['timestamp'] as String),
      location: GeoLocation.fromJson(json['location'] as Map<String, dynamic>),
      activity: json['activity'] as String?,
      customerId: json['customerId'] as String?,
    );

Map<String, dynamic> _$LocationTrackingToJson(_LocationTracking instance) =>
    <String, dynamic>{
      'timestamp': instance.timestamp.toIso8601String(),
      'location': instance.location,
      'activity': instance.activity,
      'customerId': instance.customerId,
    };

_EMIDueList _$EMIDueListFromJson(Map<String, dynamic> json) => _EMIDueList(
  id: json['id'] as String,
  generatedAt: DateTime.parse(json['generatedAt'] as String),
  agentId: json['agentId'] as String,
  forDate: DateTime.parse(json['forDate'] as String),
  dues:
      (json['dues'] as List<dynamic>?)
          ?.map((e) => EMIDueItem.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  totalDues: (json['totalDues'] as num?)?.toInt() ?? 0,
  totalAmount: (json['totalAmount'] as num?)?.toDouble() ?? 0,
  highPriorityDues: (json['highPriorityDues'] as num?)?.toInt() ?? 0,
  mediumPriorityDues: (json['mediumPriorityDues'] as num?)?.toInt() ?? 0,
  regularDues: (json['regularDues'] as num?)?.toInt() ?? 0,
  isCompleted: json['isCompleted'] as bool? ?? false,
  completedAt: json['completedAt'] == null
      ? null
      : DateTime.parse(json['completedAt'] as String),
  collectionsMade: (json['collectionsMade'] as num?)?.toInt() ?? 0,
  collectedAmount: (json['collectedAmount'] as num?)?.toDouble() ?? 0,
);

Map<String, dynamic> _$EMIDueListToJson(_EMIDueList instance) =>
    <String, dynamic>{
      'id': instance.id,
      'generatedAt': instance.generatedAt.toIso8601String(),
      'agentId': instance.agentId,
      'forDate': instance.forDate.toIso8601String(),
      'dues': instance.dues,
      'totalDues': instance.totalDues,
      'totalAmount': instance.totalAmount,
      'highPriorityDues': instance.highPriorityDues,
      'mediumPriorityDues': instance.mediumPriorityDues,
      'regularDues': instance.regularDues,
      'isCompleted': instance.isCompleted,
      'completedAt': instance.completedAt?.toIso8601String(),
      'collectionsMade': instance.collectionsMade,
      'collectedAmount': instance.collectedAmount,
    };

_EMIDueItem _$EMIDueItemFromJson(Map<String, dynamic> json) => _EMIDueItem(
  customerId: json['customerId'] as String,
  customerName: json['customerName'] as String,
  phone: json['phone'] as String,
  address: json['address'] as String,
  bookingId: json['bookingId'] as String,
  plotNumber: json['plotNumber'] as String,
  colonyName: json['colonyName'] as String,
  emiAmount: (json['emiAmount'] as num).toDouble(),
  dueDate: DateTime.parse(json['dueDate'] as String),
  daysOverdue: (json['daysOverdue'] as num).toInt(),
  totalDue: (json['totalDue'] as num).toDouble(),
  lateFee: (json['lateFee'] as num?)?.toDouble() ?? 0,
  priority: $enumDecode(_$DuePriorityEnumMap, json['priority']),
  lastVisitNotes: json['lastVisitNotes'] as String?,
  lastVisitDate: json['lastVisitDate'] == null
      ? null
      : DateTime.parse(json['lastVisitDate'] as String),
  isCollected: json['isCollected'] as bool?,
  collectedAmount: (json['collectedAmount'] as num?)?.toDouble(),
  collectedAt: json['collectedAt'] == null
      ? null
      : DateTime.parse(json['collectedAt'] as String),
  location: json['location'] == null
      ? null
      : GeoLocation.fromJson(json['location'] as Map<String, dynamic>),
  landmark: json['landmark'] as String?,
  preferredTime: json['preferredTime'] as String?,
);

Map<String, dynamic> _$EMIDueItemToJson(_EMIDueItem instance) =>
    <String, dynamic>{
      'customerId': instance.customerId,
      'customerName': instance.customerName,
      'phone': instance.phone,
      'address': instance.address,
      'bookingId': instance.bookingId,
      'plotNumber': instance.plotNumber,
      'colonyName': instance.colonyName,
      'emiAmount': instance.emiAmount,
      'dueDate': instance.dueDate.toIso8601String(),
      'daysOverdue': instance.daysOverdue,
      'totalDue': instance.totalDue,
      'lateFee': instance.lateFee,
      'priority': _$DuePriorityEnumMap[instance.priority]!,
      'lastVisitNotes': instance.lastVisitNotes,
      'lastVisitDate': instance.lastVisitDate?.toIso8601String(),
      'isCollected': instance.isCollected,
      'collectedAmount': instance.collectedAmount,
      'collectedAt': instance.collectedAt?.toIso8601String(),
      'location': instance.location,
      'landmark': instance.landmark,
      'preferredTime': instance.preferredTime,
    };

const _$DuePriorityEnumMap = {
  DuePriority.high: 'high',
  DuePriority.medium: 'medium',
  DuePriority.low: 'low',
};

_EMIReminder _$EMIReminderFromJson(Map<String, dynamic> json) => _EMIReminder(
  id: json['id'] as String,
  customerId: json['customerId'] as String,
  bookingId: json['bookingId'] as String,
  customerName: json['customerName'] as String,
  phone: json['phone'] as String,
  emiAmount: (json['emiAmount'] as num).toDouble(),
  dueDate: DateTime.parse(json['dueDate'] as String),
  type: $enumDecode(_$ReminderTypeEnumMap, json['type']),
  status: $enumDecode(_$ReminderStatusEnumMap, json['status']),
  messageContent: json['messageContent'] as String?,
  scheduledAt: json['scheduledAt'] == null
      ? null
      : DateTime.parse(json['scheduledAt'] as String),
  sentAt: json['sentAt'] == null
      ? null
      : DateTime.parse(json['sentAt'] as String),
  deliveredAt: json['deliveredAt'] == null
      ? null
      : DateTime.parse(json['deliveredAt'] as String),
  isResponded: json['isResponded'] as bool?,
  respondedAt: json['respondedAt'] == null
      ? null
      : DateTime.parse(json['respondedAt'] as String),
  responseType: json['responseType'] as String?,
  assignedAgentId: json['assignedAgentId'] as String?,
  agentAssignedAt: json['agentAssignedAt'] == null
      ? null
      : DateTime.parse(json['agentAssignedAt'] as String),
);

Map<String, dynamic> _$EMIReminderToJson(_EMIReminder instance) =>
    <String, dynamic>{
      'id': instance.id,
      'customerId': instance.customerId,
      'bookingId': instance.bookingId,
      'customerName': instance.customerName,
      'phone': instance.phone,
      'emiAmount': instance.emiAmount,
      'dueDate': instance.dueDate.toIso8601String(),
      'type': _$ReminderTypeEnumMap[instance.type]!,
      'status': _$ReminderStatusEnumMap[instance.status]!,
      'messageContent': instance.messageContent,
      'scheduledAt': instance.scheduledAt?.toIso8601String(),
      'sentAt': instance.sentAt?.toIso8601String(),
      'deliveredAt': instance.deliveredAt?.toIso8601String(),
      'isResponded': instance.isResponded,
      'respondedAt': instance.respondedAt?.toIso8601String(),
      'responseType': instance.responseType,
      'assignedAgentId': instance.assignedAgentId,
      'agentAssignedAt': instance.agentAssignedAt?.toIso8601String(),
    };

const _$ReminderTypeEnumMap = {
  ReminderType.sms: 'sms',
  ReminderType.whatsapp: 'whatsapp',
  ReminderType.call: 'call',
  ReminderType.email: 'email',
  ReminderType.push: 'push',
};

const _$ReminderStatusEnumMap = {
  ReminderStatus.scheduled: 'scheduled',
  ReminderStatus.sent: 'sent',
  ReminderStatus.delivered: 'delivered',
  ReminderStatus.read: 'read',
  ReminderStatus.failed: 'failed',
  ReminderStatus.bounced: 'bounced',
};
