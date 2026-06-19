// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'emi_collection_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
  'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models',
);

EMICollectionAgent _$EMICollectionAgentFromJson(Map<String, dynamic> json) {
  return _EMICollectionAgent.fromJson(json);
}

/// @nodoc
mixin _$EMICollectionAgent {
  String get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get phone => throw _privateConstructorUsedError;
  String get email => throw _privateConstructorUsedError;
  String? get photoUrl => throw _privateConstructorUsedError;
  String? get aadharNumber => throw _privateConstructorUsedError;
  String? get address => throw _privateConstructorUsedError; // Employment
  String get employeeId => throw _privateConstructorUsedError;
  DateTime get joiningDate => throw _privateConstructorUsedError;
  CollectionAgentType get agentType =>
      throw _privateConstructorUsedError; // FullTime, PartTime, Freelance
  CollectionArea get assignedArea =>
      throw _privateConstructorUsedError; // Salary Structure
  double get monthlySalary => throw _privateConstructorUsedError;
  double? get commissionPerCollection =>
      throw _privateConstructorUsedError; // Per EMI collected
  double? get commissionPercentage =>
      throw _privateConstructorUsedError; // % of collected amount
  double? get incentivePerTarget =>
      throw _privateConstructorUsedError; // Bonus for target achievement
  // Assigned Customers
  List<String> get assignedCustomerIds => throw _privateConstructorUsedError;
  List<EMICustomerAssignment> get customerAssignments =>
      throw _privateConstructorUsedError; // Performance
  List<DailyCollectionReport> get dailyReports =>
      throw _privateConstructorUsedError;
  List<MonthlyCollectionPerformance> get monthlyReports =>
      throw _privateConstructorUsedError; // Current Month Stats
  int get currentMonthCollections => throw _privateConstructorUsedError;
  double get currentMonthAmount => throw _privateConstructorUsedError;
  double get currentMonthCommission => throw _privateConstructorUsedError;
  int get currentMonthTarget => throw _privateConstructorUsedError;
  double get targetAchievement =>
      throw _privateConstructorUsedError; // Location Tracking
  List<LocationTracking> get locationHistory =>
      throw _privateConstructorUsedError;
  bool? get isCurrentlyActive => throw _privateConstructorUsedError;
  GeoLocation? get lastLocation => throw _privateConstructorUsedError;
  DateTime? get lastLocationUpdate =>
      throw _privateConstructorUsedError; // Status
  AgentStatus get status => throw _privateConstructorUsedError;
  DateTime? get lastActiveAt => throw _privateConstructorUsedError; // Documents
  List<String> get documentUrls => throw _privateConstructorUsedError;
  DateTime get createdAt => throw _privateConstructorUsedError;
  DateTime get updatedAt => throw _privateConstructorUsedError;

  /// Serializes this EMICollectionAgent to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of EMICollectionAgent
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $EMICollectionAgentCopyWith<EMICollectionAgent> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $EMICollectionAgentCopyWith<$Res> {
  factory $EMICollectionAgentCopyWith(
    EMICollectionAgent value,
    $Res Function(EMICollectionAgent) then,
  ) = _$EMICollectionAgentCopyWithImpl<$Res, EMICollectionAgent>;
  @useResult
  $Res call({
    String id,
    String name,
    String phone,
    String email,
    String? photoUrl,
    String? aadharNumber,
    String? address,
    String employeeId,
    DateTime joiningDate,
    CollectionAgentType agentType,
    CollectionArea assignedArea,
    double monthlySalary,
    double? commissionPerCollection,
    double? commissionPercentage,
    double? incentivePerTarget,
    List<String> assignedCustomerIds,
    List<EMICustomerAssignment> customerAssignments,
    List<DailyCollectionReport> dailyReports,
    List<MonthlyCollectionPerformance> monthlyReports,
    int currentMonthCollections,
    double currentMonthAmount,
    double currentMonthCommission,
    int currentMonthTarget,
    double targetAchievement,
    List<LocationTracking> locationHistory,
    bool? isCurrentlyActive,
    GeoLocation? lastLocation,
    DateTime? lastLocationUpdate,
    AgentStatus status,
    DateTime? lastActiveAt,
    List<String> documentUrls,
    DateTime createdAt,
    DateTime updatedAt,
  });

  $CollectionAreaCopyWith<$Res> get assignedArea;
}

/// @nodoc
class _$EMICollectionAgentCopyWithImpl<$Res, $Val extends EMICollectionAgent>
    implements $EMICollectionAgentCopyWith<$Res> {
  _$EMICollectionAgentCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of EMICollectionAgent
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? phone = null,
    Object? email = null,
    Object? photoUrl = freezed,
    Object? aadharNumber = freezed,
    Object? address = freezed,
    Object? employeeId = null,
    Object? joiningDate = null,
    Object? agentType = null,
    Object? assignedArea = null,
    Object? monthlySalary = null,
    Object? commissionPerCollection = freezed,
    Object? commissionPercentage = freezed,
    Object? incentivePerTarget = freezed,
    Object? assignedCustomerIds = null,
    Object? customerAssignments = null,
    Object? dailyReports = null,
    Object? monthlyReports = null,
    Object? currentMonthCollections = null,
    Object? currentMonthAmount = null,
    Object? currentMonthCommission = null,
    Object? currentMonthTarget = null,
    Object? targetAchievement = null,
    Object? locationHistory = null,
    Object? isCurrentlyActive = freezed,
    Object? lastLocation = freezed,
    Object? lastLocationUpdate = freezed,
    Object? status = null,
    Object? lastActiveAt = freezed,
    Object? documentUrls = null,
    Object? createdAt = null,
    Object? updatedAt = null,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            name: null == name
                ? _value.name
                : name // ignore: cast_nullable_to_non_nullable
                      as String,
            phone: null == phone
                ? _value.phone
                : phone // ignore: cast_nullable_to_non_nullable
                      as String,
            email: null == email
                ? _value.email
                : email // ignore: cast_nullable_to_non_nullable
                      as String,
            photoUrl: freezed == photoUrl
                ? _value.photoUrl
                : photoUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            aadharNumber: freezed == aadharNumber
                ? _value.aadharNumber
                : aadharNumber // ignore: cast_nullable_to_non_nullable
                      as String?,
            address: freezed == address
                ? _value.address
                : address // ignore: cast_nullable_to_non_nullable
                      as String?,
            employeeId: null == employeeId
                ? _value.employeeId
                : employeeId // ignore: cast_nullable_to_non_nullable
                      as String,
            joiningDate: null == joiningDate
                ? _value.joiningDate
                : joiningDate // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            agentType: null == agentType
                ? _value.agentType
                : agentType // ignore: cast_nullable_to_non_nullable
                      as CollectionAgentType,
            assignedArea: null == assignedArea
                ? _value.assignedArea
                : assignedArea // ignore: cast_nullable_to_non_nullable
                      as CollectionArea,
            monthlySalary: null == monthlySalary
                ? _value.monthlySalary
                : monthlySalary // ignore: cast_nullable_to_non_nullable
                      as double,
            commissionPerCollection: freezed == commissionPerCollection
                ? _value.commissionPerCollection
                : commissionPerCollection // ignore: cast_nullable_to_non_nullable
                      as double?,
            commissionPercentage: freezed == commissionPercentage
                ? _value.commissionPercentage
                : commissionPercentage // ignore: cast_nullable_to_non_nullable
                      as double?,
            incentivePerTarget: freezed == incentivePerTarget
                ? _value.incentivePerTarget
                : incentivePerTarget // ignore: cast_nullable_to_non_nullable
                      as double?,
            assignedCustomerIds: null == assignedCustomerIds
                ? _value.assignedCustomerIds
                : assignedCustomerIds // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            customerAssignments: null == customerAssignments
                ? _value.customerAssignments
                : customerAssignments // ignore: cast_nullable_to_non_nullable
                      as List<EMICustomerAssignment>,
            dailyReports: null == dailyReports
                ? _value.dailyReports
                : dailyReports // ignore: cast_nullable_to_non_nullable
                      as List<DailyCollectionReport>,
            monthlyReports: null == monthlyReports
                ? _value.monthlyReports
                : monthlyReports // ignore: cast_nullable_to_non_nullable
                      as List<MonthlyCollectionPerformance>,
            currentMonthCollections: null == currentMonthCollections
                ? _value.currentMonthCollections
                : currentMonthCollections // ignore: cast_nullable_to_non_nullable
                      as int,
            currentMonthAmount: null == currentMonthAmount
                ? _value.currentMonthAmount
                : currentMonthAmount // ignore: cast_nullable_to_non_nullable
                      as double,
            currentMonthCommission: null == currentMonthCommission
                ? _value.currentMonthCommission
                : currentMonthCommission // ignore: cast_nullable_to_non_nullable
                      as double,
            currentMonthTarget: null == currentMonthTarget
                ? _value.currentMonthTarget
                : currentMonthTarget // ignore: cast_nullable_to_non_nullable
                      as int,
            targetAchievement: null == targetAchievement
                ? _value.targetAchievement
                : targetAchievement // ignore: cast_nullable_to_non_nullable
                      as double,
            locationHistory: null == locationHistory
                ? _value.locationHistory
                : locationHistory // ignore: cast_nullable_to_non_nullable
                      as List<LocationTracking>,
            isCurrentlyActive: freezed == isCurrentlyActive
                ? _value.isCurrentlyActive
                : isCurrentlyActive // ignore: cast_nullable_to_non_nullable
                      as bool?,
            lastLocation: freezed == lastLocation
                ? _value.lastLocation
                : lastLocation // ignore: cast_nullable_to_non_nullable
                      as GeoLocation?,
            lastLocationUpdate: freezed == lastLocationUpdate
                ? _value.lastLocationUpdate
                : lastLocationUpdate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as AgentStatus,
            lastActiveAt: freezed == lastActiveAt
                ? _value.lastActiveAt
                : lastActiveAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            documentUrls: null == documentUrls
                ? _value.documentUrls
                : documentUrls // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            createdAt: null == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            updatedAt: null == updatedAt
                ? _value.updatedAt
                : updatedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
          )
          as $Val,
    );
  }

  /// Create a copy of EMICollectionAgent
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CollectionAreaCopyWith<$Res> get assignedArea {
    return $CollectionAreaCopyWith<$Res>(_value.assignedArea, (value) {
      return _then(_value.copyWith(assignedArea: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$EMICollectionAgentImplCopyWith<$Res>
    implements $EMICollectionAgentCopyWith<$Res> {
  factory _$$EMICollectionAgentImplCopyWith(
    _$EMICollectionAgentImpl value,
    $Res Function(_$EMICollectionAgentImpl) then,
  ) = __$$EMICollectionAgentImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String phone,
    String email,
    String? photoUrl,
    String? aadharNumber,
    String? address,
    String employeeId,
    DateTime joiningDate,
    CollectionAgentType agentType,
    CollectionArea assignedArea,
    double monthlySalary,
    double? commissionPerCollection,
    double? commissionPercentage,
    double? incentivePerTarget,
    List<String> assignedCustomerIds,
    List<EMICustomerAssignment> customerAssignments,
    List<DailyCollectionReport> dailyReports,
    List<MonthlyCollectionPerformance> monthlyReports,
    int currentMonthCollections,
    double currentMonthAmount,
    double currentMonthCommission,
    int currentMonthTarget,
    double targetAchievement,
    List<LocationTracking> locationHistory,
    bool? isCurrentlyActive,
    GeoLocation? lastLocation,
    DateTime? lastLocationUpdate,
    AgentStatus status,
    DateTime? lastActiveAt,
    List<String> documentUrls,
    DateTime createdAt,
    DateTime updatedAt,
  });

  @override
  $CollectionAreaCopyWith<$Res> get assignedArea;
}

/// @nodoc
class __$$EMICollectionAgentImplCopyWithImpl<$Res>
    extends _$EMICollectionAgentCopyWithImpl<$Res, _$EMICollectionAgentImpl>
    implements _$$EMICollectionAgentImplCopyWith<$Res> {
  __$$EMICollectionAgentImplCopyWithImpl(
    _$EMICollectionAgentImpl _value,
    $Res Function(_$EMICollectionAgentImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of EMICollectionAgent
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? phone = null,
    Object? email = null,
    Object? photoUrl = freezed,
    Object? aadharNumber = freezed,
    Object? address = freezed,
    Object? employeeId = null,
    Object? joiningDate = null,
    Object? agentType = null,
    Object? assignedArea = null,
    Object? monthlySalary = null,
    Object? commissionPerCollection = freezed,
    Object? commissionPercentage = freezed,
    Object? incentivePerTarget = freezed,
    Object? assignedCustomerIds = null,
    Object? customerAssignments = null,
    Object? dailyReports = null,
    Object? monthlyReports = null,
    Object? currentMonthCollections = null,
    Object? currentMonthAmount = null,
    Object? currentMonthCommission = null,
    Object? currentMonthTarget = null,
    Object? targetAchievement = null,
    Object? locationHistory = null,
    Object? isCurrentlyActive = freezed,
    Object? lastLocation = freezed,
    Object? lastLocationUpdate = freezed,
    Object? status = null,
    Object? lastActiveAt = freezed,
    Object? documentUrls = null,
    Object? createdAt = null,
    Object? updatedAt = null,
  }) {
    return _then(
      _$EMICollectionAgentImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _value.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        phone: null == phone
            ? _value.phone
            : phone // ignore: cast_nullable_to_non_nullable
                  as String,
        email: null == email
            ? _value.email
            : email // ignore: cast_nullable_to_non_nullable
                  as String,
        photoUrl: freezed == photoUrl
            ? _value.photoUrl
            : photoUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        aadharNumber: freezed == aadharNumber
            ? _value.aadharNumber
            : aadharNumber // ignore: cast_nullable_to_non_nullable
                  as String?,
        address: freezed == address
            ? _value.address
            : address // ignore: cast_nullable_to_non_nullable
                  as String?,
        employeeId: null == employeeId
            ? _value.employeeId
            : employeeId // ignore: cast_nullable_to_non_nullable
                  as String,
        joiningDate: null == joiningDate
            ? _value.joiningDate
            : joiningDate // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        agentType: null == agentType
            ? _value.agentType
            : agentType // ignore: cast_nullable_to_non_nullable
                  as CollectionAgentType,
        assignedArea: null == assignedArea
            ? _value.assignedArea
            : assignedArea // ignore: cast_nullable_to_non_nullable
                  as CollectionArea,
        monthlySalary: null == monthlySalary
            ? _value.monthlySalary
            : monthlySalary // ignore: cast_nullable_to_non_nullable
                  as double,
        commissionPerCollection: freezed == commissionPerCollection
            ? _value.commissionPerCollection
            : commissionPerCollection // ignore: cast_nullable_to_non_nullable
                  as double?,
        commissionPercentage: freezed == commissionPercentage
            ? _value.commissionPercentage
            : commissionPercentage // ignore: cast_nullable_to_non_nullable
                  as double?,
        incentivePerTarget: freezed == incentivePerTarget
            ? _value.incentivePerTarget
            : incentivePerTarget // ignore: cast_nullable_to_non_nullable
                  as double?,
        assignedCustomerIds: null == assignedCustomerIds
            ? _value._assignedCustomerIds
            : assignedCustomerIds // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        customerAssignments: null == customerAssignments
            ? _value._customerAssignments
            : customerAssignments // ignore: cast_nullable_to_non_nullable
                  as List<EMICustomerAssignment>,
        dailyReports: null == dailyReports
            ? _value._dailyReports
            : dailyReports // ignore: cast_nullable_to_non_nullable
                  as List<DailyCollectionReport>,
        monthlyReports: null == monthlyReports
            ? _value._monthlyReports
            : monthlyReports // ignore: cast_nullable_to_non_nullable
                  as List<MonthlyCollectionPerformance>,
        currentMonthCollections: null == currentMonthCollections
            ? _value.currentMonthCollections
            : currentMonthCollections // ignore: cast_nullable_to_non_nullable
                  as int,
        currentMonthAmount: null == currentMonthAmount
            ? _value.currentMonthAmount
            : currentMonthAmount // ignore: cast_nullable_to_non_nullable
                  as double,
        currentMonthCommission: null == currentMonthCommission
            ? _value.currentMonthCommission
            : currentMonthCommission // ignore: cast_nullable_to_non_nullable
                  as double,
        currentMonthTarget: null == currentMonthTarget
            ? _value.currentMonthTarget
            : currentMonthTarget // ignore: cast_nullable_to_non_nullable
                  as int,
        targetAchievement: null == targetAchievement
            ? _value.targetAchievement
            : targetAchievement // ignore: cast_nullable_to_non_nullable
                  as double,
        locationHistory: null == locationHistory
            ? _value._locationHistory
            : locationHistory // ignore: cast_nullable_to_non_nullable
                  as List<LocationTracking>,
        isCurrentlyActive: freezed == isCurrentlyActive
            ? _value.isCurrentlyActive
            : isCurrentlyActive // ignore: cast_nullable_to_non_nullable
                  as bool?,
        lastLocation: freezed == lastLocation
            ? _value.lastLocation
            : lastLocation // ignore: cast_nullable_to_non_nullable
                  as GeoLocation?,
        lastLocationUpdate: freezed == lastLocationUpdate
            ? _value.lastLocationUpdate
            : lastLocationUpdate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as AgentStatus,
        lastActiveAt: freezed == lastActiveAt
            ? _value.lastActiveAt
            : lastActiveAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        documentUrls: null == documentUrls
            ? _value._documentUrls
            : documentUrls // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        createdAt: null == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        updatedAt: null == updatedAt
            ? _value.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$EMICollectionAgentImpl implements _EMICollectionAgent {
  const _$EMICollectionAgentImpl({
    required this.id,
    required this.name,
    required this.phone,
    required this.email,
    this.photoUrl,
    this.aadharNumber,
    this.address,
    required this.employeeId,
    required this.joiningDate,
    required this.agentType,
    required this.assignedArea,
    required this.monthlySalary,
    this.commissionPerCollection,
    this.commissionPercentage,
    this.incentivePerTarget,
    final List<String> assignedCustomerIds = const [],
    final List<EMICustomerAssignment> customerAssignments = const [],
    final List<DailyCollectionReport> dailyReports = const [],
    final List<MonthlyCollectionPerformance> monthlyReports = const [],
    this.currentMonthCollections = 0,
    this.currentMonthAmount = 0,
    this.currentMonthCommission = 0,
    this.currentMonthTarget = 0,
    this.targetAchievement = 0,
    final List<LocationTracking> locationHistory = const [],
    this.isCurrentlyActive,
    this.lastLocation,
    this.lastLocationUpdate,
    required this.status,
    this.lastActiveAt,
    final List<String> documentUrls = const [],
    required this.createdAt,
    required this.updatedAt,
  }) : _assignedCustomerIds = assignedCustomerIds,
       _customerAssignments = customerAssignments,
       _dailyReports = dailyReports,
       _monthlyReports = monthlyReports,
       _locationHistory = locationHistory,
       _documentUrls = documentUrls;

  factory _$EMICollectionAgentImpl.fromJson(Map<String, dynamic> json) =>
      _$$EMICollectionAgentImplFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String phone;
  @override
  final String email;
  @override
  final String? photoUrl;
  @override
  final String? aadharNumber;
  @override
  final String? address;
  // Employment
  @override
  final String employeeId;
  @override
  final DateTime joiningDate;
  @override
  final CollectionAgentType agentType;
  // FullTime, PartTime, Freelance
  @override
  final CollectionArea assignedArea;
  // Salary Structure
  @override
  final double monthlySalary;
  @override
  final double? commissionPerCollection;
  // Per EMI collected
  @override
  final double? commissionPercentage;
  // % of collected amount
  @override
  final double? incentivePerTarget;
  // Bonus for target achievement
  // Assigned Customers
  final List<String> _assignedCustomerIds;
  // Bonus for target achievement
  // Assigned Customers
  @override
  @JsonKey()
  List<String> get assignedCustomerIds {
    if (_assignedCustomerIds is EqualUnmodifiableListView)
      return _assignedCustomerIds;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_assignedCustomerIds);
  }

  final List<EMICustomerAssignment> _customerAssignments;
  @override
  @JsonKey()
  List<EMICustomerAssignment> get customerAssignments {
    if (_customerAssignments is EqualUnmodifiableListView)
      return _customerAssignments;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_customerAssignments);
  }

  // Performance
  final List<DailyCollectionReport> _dailyReports;
  // Performance
  @override
  @JsonKey()
  List<DailyCollectionReport> get dailyReports {
    if (_dailyReports is EqualUnmodifiableListView) return _dailyReports;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_dailyReports);
  }

  final List<MonthlyCollectionPerformance> _monthlyReports;
  @override
  @JsonKey()
  List<MonthlyCollectionPerformance> get monthlyReports {
    if (_monthlyReports is EqualUnmodifiableListView) return _monthlyReports;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_monthlyReports);
  }

  // Current Month Stats
  @override
  @JsonKey()
  final int currentMonthCollections;
  @override
  @JsonKey()
  final double currentMonthAmount;
  @override
  @JsonKey()
  final double currentMonthCommission;
  @override
  @JsonKey()
  final int currentMonthTarget;
  @override
  @JsonKey()
  final double targetAchievement;
  // Location Tracking
  final List<LocationTracking> _locationHistory;
  // Location Tracking
  @override
  @JsonKey()
  List<LocationTracking> get locationHistory {
    if (_locationHistory is EqualUnmodifiableListView) return _locationHistory;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_locationHistory);
  }

  @override
  final bool? isCurrentlyActive;
  @override
  final GeoLocation? lastLocation;
  @override
  final DateTime? lastLocationUpdate;
  // Status
  @override
  final AgentStatus status;
  @override
  final DateTime? lastActiveAt;
  // Documents
  final List<String> _documentUrls;
  // Documents
  @override
  @JsonKey()
  List<String> get documentUrls {
    if (_documentUrls is EqualUnmodifiableListView) return _documentUrls;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_documentUrls);
  }

  @override
  final DateTime createdAt;
  @override
  final DateTime updatedAt;

  @override
  String toString() {
    return 'EMICollectionAgent(id: $id, name: $name, phone: $phone, email: $email, photoUrl: $photoUrl, aadharNumber: $aadharNumber, address: $address, employeeId: $employeeId, joiningDate: $joiningDate, agentType: $agentType, assignedArea: $assignedArea, monthlySalary: $monthlySalary, commissionPerCollection: $commissionPerCollection, commissionPercentage: $commissionPercentage, incentivePerTarget: $incentivePerTarget, assignedCustomerIds: $assignedCustomerIds, customerAssignments: $customerAssignments, dailyReports: $dailyReports, monthlyReports: $monthlyReports, currentMonthCollections: $currentMonthCollections, currentMonthAmount: $currentMonthAmount, currentMonthCommission: $currentMonthCommission, currentMonthTarget: $currentMonthTarget, targetAchievement: $targetAchievement, locationHistory: $locationHistory, isCurrentlyActive: $isCurrentlyActive, lastLocation: $lastLocation, lastLocationUpdate: $lastLocationUpdate, status: $status, lastActiveAt: $lastActiveAt, documentUrls: $documentUrls, createdAt: $createdAt, updatedAt: $updatedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$EMICollectionAgentImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.phone, phone) || other.phone == phone) &&
            (identical(other.email, email) || other.email == email) &&
            (identical(other.photoUrl, photoUrl) ||
                other.photoUrl == photoUrl) &&
            (identical(other.aadharNumber, aadharNumber) ||
                other.aadharNumber == aadharNumber) &&
            (identical(other.address, address) || other.address == address) &&
            (identical(other.employeeId, employeeId) ||
                other.employeeId == employeeId) &&
            (identical(other.joiningDate, joiningDate) ||
                other.joiningDate == joiningDate) &&
            (identical(other.agentType, agentType) ||
                other.agentType == agentType) &&
            (identical(other.assignedArea, assignedArea) ||
                other.assignedArea == assignedArea) &&
            (identical(other.monthlySalary, monthlySalary) ||
                other.monthlySalary == monthlySalary) &&
            (identical(
                  other.commissionPerCollection,
                  commissionPerCollection,
                ) ||
                other.commissionPerCollection == commissionPerCollection) &&
            (identical(other.commissionPercentage, commissionPercentage) ||
                other.commissionPercentage == commissionPercentage) &&
            (identical(other.incentivePerTarget, incentivePerTarget) ||
                other.incentivePerTarget == incentivePerTarget) &&
            const DeepCollectionEquality().equals(
              other._assignedCustomerIds,
              _assignedCustomerIds,
            ) &&
            const DeepCollectionEquality().equals(
              other._customerAssignments,
              _customerAssignments,
            ) &&
            const DeepCollectionEquality().equals(
              other._dailyReports,
              _dailyReports,
            ) &&
            const DeepCollectionEquality().equals(
              other._monthlyReports,
              _monthlyReports,
            ) &&
            (identical(
                  other.currentMonthCollections,
                  currentMonthCollections,
                ) ||
                other.currentMonthCollections == currentMonthCollections) &&
            (identical(other.currentMonthAmount, currentMonthAmount) ||
                other.currentMonthAmount == currentMonthAmount) &&
            (identical(other.currentMonthCommission, currentMonthCommission) ||
                other.currentMonthCommission == currentMonthCommission) &&
            (identical(other.currentMonthTarget, currentMonthTarget) ||
                other.currentMonthTarget == currentMonthTarget) &&
            (identical(other.targetAchievement, targetAchievement) ||
                other.targetAchievement == targetAchievement) &&
            const DeepCollectionEquality().equals(
              other._locationHistory,
              _locationHistory,
            ) &&
            (identical(other.isCurrentlyActive, isCurrentlyActive) ||
                other.isCurrentlyActive == isCurrentlyActive) &&
            (identical(other.lastLocation, lastLocation) ||
                other.lastLocation == lastLocation) &&
            (identical(other.lastLocationUpdate, lastLocationUpdate) ||
                other.lastLocationUpdate == lastLocationUpdate) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.lastActiveAt, lastActiveAt) ||
                other.lastActiveAt == lastActiveAt) &&
            const DeepCollectionEquality().equals(
              other._documentUrls,
              _documentUrls,
            ) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    id,
    name,
    phone,
    email,
    photoUrl,
    aadharNumber,
    address,
    employeeId,
    joiningDate,
    agentType,
    assignedArea,
    monthlySalary,
    commissionPerCollection,
    commissionPercentage,
    incentivePerTarget,
    const DeepCollectionEquality().hash(_assignedCustomerIds),
    const DeepCollectionEquality().hash(_customerAssignments),
    const DeepCollectionEquality().hash(_dailyReports),
    const DeepCollectionEquality().hash(_monthlyReports),
    currentMonthCollections,
    currentMonthAmount,
    currentMonthCommission,
    currentMonthTarget,
    targetAchievement,
    const DeepCollectionEquality().hash(_locationHistory),
    isCurrentlyActive,
    lastLocation,
    lastLocationUpdate,
    status,
    lastActiveAt,
    const DeepCollectionEquality().hash(_documentUrls),
    createdAt,
    updatedAt,
  ]);

  /// Create a copy of EMICollectionAgent
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$EMICollectionAgentImplCopyWith<_$EMICollectionAgentImpl> get copyWith =>
      __$$EMICollectionAgentImplCopyWithImpl<_$EMICollectionAgentImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$EMICollectionAgentImplToJson(this);
  }
}

abstract class _EMICollectionAgent implements EMICollectionAgent {
  const factory _EMICollectionAgent({
    required final String id,
    required final String name,
    required final String phone,
    required final String email,
    final String? photoUrl,
    final String? aadharNumber,
    final String? address,
    required final String employeeId,
    required final DateTime joiningDate,
    required final CollectionAgentType agentType,
    required final CollectionArea assignedArea,
    required final double monthlySalary,
    final double? commissionPerCollection,
    final double? commissionPercentage,
    final double? incentivePerTarget,
    final List<String> assignedCustomerIds,
    final List<EMICustomerAssignment> customerAssignments,
    final List<DailyCollectionReport> dailyReports,
    final List<MonthlyCollectionPerformance> monthlyReports,
    final int currentMonthCollections,
    final double currentMonthAmount,
    final double currentMonthCommission,
    final int currentMonthTarget,
    final double targetAchievement,
    final List<LocationTracking> locationHistory,
    final bool? isCurrentlyActive,
    final GeoLocation? lastLocation,
    final DateTime? lastLocationUpdate,
    required final AgentStatus status,
    final DateTime? lastActiveAt,
    final List<String> documentUrls,
    required final DateTime createdAt,
    required final DateTime updatedAt,
  }) = _$EMICollectionAgentImpl;

  factory _EMICollectionAgent.fromJson(Map<String, dynamic> json) =
      _$EMICollectionAgentImpl.fromJson;

  @override
  String get id;
  @override
  String get name;
  @override
  String get phone;
  @override
  String get email;
  @override
  String? get photoUrl;
  @override
  String? get aadharNumber;
  @override
  String? get address; // Employment
  @override
  String get employeeId;
  @override
  DateTime get joiningDate;
  @override
  CollectionAgentType get agentType; // FullTime, PartTime, Freelance
  @override
  CollectionArea get assignedArea; // Salary Structure
  @override
  double get monthlySalary;
  @override
  double? get commissionPerCollection; // Per EMI collected
  @override
  double? get commissionPercentage; // % of collected amount
  @override
  double? get incentivePerTarget; // Bonus for target achievement
  // Assigned Customers
  @override
  List<String> get assignedCustomerIds;
  @override
  List<EMICustomerAssignment> get customerAssignments; // Performance
  @override
  List<DailyCollectionReport> get dailyReports;
  @override
  List<MonthlyCollectionPerformance> get monthlyReports; // Current Month Stats
  @override
  int get currentMonthCollections;
  @override
  double get currentMonthAmount;
  @override
  double get currentMonthCommission;
  @override
  int get currentMonthTarget;
  @override
  double get targetAchievement; // Location Tracking
  @override
  List<LocationTracking> get locationHistory;
  @override
  bool? get isCurrentlyActive;
  @override
  GeoLocation? get lastLocation;
  @override
  DateTime? get lastLocationUpdate; // Status
  @override
  AgentStatus get status;
  @override
  DateTime? get lastActiveAt; // Documents
  @override
  List<String> get documentUrls;
  @override
  DateTime get createdAt;
  @override
  DateTime get updatedAt;

  /// Create a copy of EMICollectionAgent
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$EMICollectionAgentImplCopyWith<_$EMICollectionAgentImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

CollectionArea _$CollectionAreaFromJson(Map<String, dynamic> json) {
  return _CollectionArea.fromJson(json);
}

/// @nodoc
mixin _$CollectionArea {
  String get areaName => throw _privateConstructorUsedError;
  String get state => throw _privateConstructorUsedError;
  String get district => throw _privateConstructorUsedError;
  String get city => throw _privateConstructorUsedError;
  List<String> get colonies => throw _privateConstructorUsedError;
  List<String> get pincodes => throw _privateConstructorUsedError;
  String? get areaManagerId => throw _privateConstructorUsedError;

  /// Serializes this CollectionArea to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of CollectionArea
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $CollectionAreaCopyWith<CollectionArea> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $CollectionAreaCopyWith<$Res> {
  factory $CollectionAreaCopyWith(
    CollectionArea value,
    $Res Function(CollectionArea) then,
  ) = _$CollectionAreaCopyWithImpl<$Res, CollectionArea>;
  @useResult
  $Res call({
    String areaName,
    String state,
    String district,
    String city,
    List<String> colonies,
    List<String> pincodes,
    String? areaManagerId,
  });
}

/// @nodoc
class _$CollectionAreaCopyWithImpl<$Res, $Val extends CollectionArea>
    implements $CollectionAreaCopyWith<$Res> {
  _$CollectionAreaCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of CollectionArea
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? areaName = null,
    Object? state = null,
    Object? district = null,
    Object? city = null,
    Object? colonies = null,
    Object? pincodes = null,
    Object? areaManagerId = freezed,
  }) {
    return _then(
      _value.copyWith(
            areaName: null == areaName
                ? _value.areaName
                : areaName // ignore: cast_nullable_to_non_nullable
                      as String,
            state: null == state
                ? _value.state
                : state // ignore: cast_nullable_to_non_nullable
                      as String,
            district: null == district
                ? _value.district
                : district // ignore: cast_nullable_to_non_nullable
                      as String,
            city: null == city
                ? _value.city
                : city // ignore: cast_nullable_to_non_nullable
                      as String,
            colonies: null == colonies
                ? _value.colonies
                : colonies // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            pincodes: null == pincodes
                ? _value.pincodes
                : pincodes // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            areaManagerId: freezed == areaManagerId
                ? _value.areaManagerId
                : areaManagerId // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$CollectionAreaImplCopyWith<$Res>
    implements $CollectionAreaCopyWith<$Res> {
  factory _$$CollectionAreaImplCopyWith(
    _$CollectionAreaImpl value,
    $Res Function(_$CollectionAreaImpl) then,
  ) = __$$CollectionAreaImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String areaName,
    String state,
    String district,
    String city,
    List<String> colonies,
    List<String> pincodes,
    String? areaManagerId,
  });
}

/// @nodoc
class __$$CollectionAreaImplCopyWithImpl<$Res>
    extends _$CollectionAreaCopyWithImpl<$Res, _$CollectionAreaImpl>
    implements _$$CollectionAreaImplCopyWith<$Res> {
  __$$CollectionAreaImplCopyWithImpl(
    _$CollectionAreaImpl _value,
    $Res Function(_$CollectionAreaImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of CollectionArea
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? areaName = null,
    Object? state = null,
    Object? district = null,
    Object? city = null,
    Object? colonies = null,
    Object? pincodes = null,
    Object? areaManagerId = freezed,
  }) {
    return _then(
      _$CollectionAreaImpl(
        areaName: null == areaName
            ? _value.areaName
            : areaName // ignore: cast_nullable_to_non_nullable
                  as String,
        state: null == state
            ? _value.state
            : state // ignore: cast_nullable_to_non_nullable
                  as String,
        district: null == district
            ? _value.district
            : district // ignore: cast_nullable_to_non_nullable
                  as String,
        city: null == city
            ? _value.city
            : city // ignore: cast_nullable_to_non_nullable
                  as String,
        colonies: null == colonies
            ? _value._colonies
            : colonies // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        pincodes: null == pincodes
            ? _value._pincodes
            : pincodes // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        areaManagerId: freezed == areaManagerId
            ? _value.areaManagerId
            : areaManagerId // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$CollectionAreaImpl implements _CollectionArea {
  const _$CollectionAreaImpl({
    required this.areaName,
    required this.state,
    required this.district,
    required this.city,
    final List<String> colonies = const [],
    final List<String> pincodes = const [],
    this.areaManagerId,
  }) : _colonies = colonies,
       _pincodes = pincodes;

  factory _$CollectionAreaImpl.fromJson(Map<String, dynamic> json) =>
      _$$CollectionAreaImplFromJson(json);

  @override
  final String areaName;
  @override
  final String state;
  @override
  final String district;
  @override
  final String city;
  final List<String> _colonies;
  @override
  @JsonKey()
  List<String> get colonies {
    if (_colonies is EqualUnmodifiableListView) return _colonies;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_colonies);
  }

  final List<String> _pincodes;
  @override
  @JsonKey()
  List<String> get pincodes {
    if (_pincodes is EqualUnmodifiableListView) return _pincodes;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_pincodes);
  }

  @override
  final String? areaManagerId;

  @override
  String toString() {
    return 'CollectionArea(areaName: $areaName, state: $state, district: $district, city: $city, colonies: $colonies, pincodes: $pincodes, areaManagerId: $areaManagerId)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$CollectionAreaImpl &&
            (identical(other.areaName, areaName) ||
                other.areaName == areaName) &&
            (identical(other.state, state) || other.state == state) &&
            (identical(other.district, district) ||
                other.district == district) &&
            (identical(other.city, city) || other.city == city) &&
            const DeepCollectionEquality().equals(other._colonies, _colonies) &&
            const DeepCollectionEquality().equals(other._pincodes, _pincodes) &&
            (identical(other.areaManagerId, areaManagerId) ||
                other.areaManagerId == areaManagerId));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    areaName,
    state,
    district,
    city,
    const DeepCollectionEquality().hash(_colonies),
    const DeepCollectionEquality().hash(_pincodes),
    areaManagerId,
  );

  /// Create a copy of CollectionArea
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$CollectionAreaImplCopyWith<_$CollectionAreaImpl> get copyWith =>
      __$$CollectionAreaImplCopyWithImpl<_$CollectionAreaImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$CollectionAreaImplToJson(this);
  }
}

abstract class _CollectionArea implements CollectionArea {
  const factory _CollectionArea({
    required final String areaName,
    required final String state,
    required final String district,
    required final String city,
    final List<String> colonies,
    final List<String> pincodes,
    final String? areaManagerId,
  }) = _$CollectionAreaImpl;

  factory _CollectionArea.fromJson(Map<String, dynamic> json) =
      _$CollectionAreaImpl.fromJson;

  @override
  String get areaName;
  @override
  String get state;
  @override
  String get district;
  @override
  String get city;
  @override
  List<String> get colonies;
  @override
  List<String> get pincodes;
  @override
  String? get areaManagerId;

  /// Create a copy of CollectionArea
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$CollectionAreaImplCopyWith<_$CollectionAreaImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

EMICustomerAssignment _$EMICustomerAssignmentFromJson(
  Map<String, dynamic> json,
) {
  return _EMICustomerAssignment.fromJson(json);
}

/// @nodoc
mixin _$EMICustomerAssignment {
  String get customerId => throw _privateConstructorUsedError;
  String get customerName => throw _privateConstructorUsedError;
  String get customerPhone => throw _privateConstructorUsedError;
  String get customerAddress => throw _privateConstructorUsedError;
  String get bookingId => throw _privateConstructorUsedError;
  String get plotNumber => throw _privateConstructorUsedError;
  String get colonyName => throw _privateConstructorUsedError; // EMI Details
  double get monthlyEMI => throw _privateConstructorUsedError;
  int get totalEMIs => throw _privateConstructorUsedError;
  int get paidEMIs => throw _privateConstructorUsedError;
  int get pendingEMIs => throw _privateConstructorUsedError;
  double get totalDue => throw _privateConstructorUsedError; // Due Date
  int get dueDay =>
      throw _privateConstructorUsedError; // 5th, 10th, 15th of month
  DateTime? get nextDueDate => throw _privateConstructorUsedError; // Status
  PaymentStatus get paymentStatus =>
      throw _privateConstructorUsedError; // Regular, Irregular, Defaulter
  bool get isHighPriority => throw _privateConstructorUsedError; // For overdue
  // Collection Info
  String? get preferredCollectionTime =>
      throw _privateConstructorUsedError; // Morning, Afternoon, Evening
  String? get landmark => throw _privateConstructorUsedError;
  GeoLocation? get location => throw _privateConstructorUsedError; // History
  List<PreviousVisit> get visitHistory => throw _privateConstructorUsedError;
  String? get specialInstructions => throw _privateConstructorUsedError;
  DateTime? get assignedAt => throw _privateConstructorUsedError;
  DateTime? get lastCollectedAt => throw _privateConstructorUsedError;

  /// Serializes this EMICustomerAssignment to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of EMICustomerAssignment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $EMICustomerAssignmentCopyWith<EMICustomerAssignment> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $EMICustomerAssignmentCopyWith<$Res> {
  factory $EMICustomerAssignmentCopyWith(
    EMICustomerAssignment value,
    $Res Function(EMICustomerAssignment) then,
  ) = _$EMICustomerAssignmentCopyWithImpl<$Res, EMICustomerAssignment>;
  @useResult
  $Res call({
    String customerId,
    String customerName,
    String customerPhone,
    String customerAddress,
    String bookingId,
    String plotNumber,
    String colonyName,
    double monthlyEMI,
    int totalEMIs,
    int paidEMIs,
    int pendingEMIs,
    double totalDue,
    int dueDay,
    DateTime? nextDueDate,
    PaymentStatus paymentStatus,
    bool isHighPriority,
    String? preferredCollectionTime,
    String? landmark,
    GeoLocation? location,
    List<PreviousVisit> visitHistory,
    String? specialInstructions,
    DateTime? assignedAt,
    DateTime? lastCollectedAt,
  });
}

/// @nodoc
class _$EMICustomerAssignmentCopyWithImpl<
  $Res,
  $Val extends EMICustomerAssignment
>
    implements $EMICustomerAssignmentCopyWith<$Res> {
  _$EMICustomerAssignmentCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of EMICustomerAssignment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? customerId = null,
    Object? customerName = null,
    Object? customerPhone = null,
    Object? customerAddress = null,
    Object? bookingId = null,
    Object? plotNumber = null,
    Object? colonyName = null,
    Object? monthlyEMI = null,
    Object? totalEMIs = null,
    Object? paidEMIs = null,
    Object? pendingEMIs = null,
    Object? totalDue = null,
    Object? dueDay = null,
    Object? nextDueDate = freezed,
    Object? paymentStatus = null,
    Object? isHighPriority = null,
    Object? preferredCollectionTime = freezed,
    Object? landmark = freezed,
    Object? location = freezed,
    Object? visitHistory = null,
    Object? specialInstructions = freezed,
    Object? assignedAt = freezed,
    Object? lastCollectedAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            customerId: null == customerId
                ? _value.customerId
                : customerId // ignore: cast_nullable_to_non_nullable
                      as String,
            customerName: null == customerName
                ? _value.customerName
                : customerName // ignore: cast_nullable_to_non_nullable
                      as String,
            customerPhone: null == customerPhone
                ? _value.customerPhone
                : customerPhone // ignore: cast_nullable_to_non_nullable
                      as String,
            customerAddress: null == customerAddress
                ? _value.customerAddress
                : customerAddress // ignore: cast_nullable_to_non_nullable
                      as String,
            bookingId: null == bookingId
                ? _value.bookingId
                : bookingId // ignore: cast_nullable_to_non_nullable
                      as String,
            plotNumber: null == plotNumber
                ? _value.plotNumber
                : plotNumber // ignore: cast_nullable_to_non_nullable
                      as String,
            colonyName: null == colonyName
                ? _value.colonyName
                : colonyName // ignore: cast_nullable_to_non_nullable
                      as String,
            monthlyEMI: null == monthlyEMI
                ? _value.monthlyEMI
                : monthlyEMI // ignore: cast_nullable_to_non_nullable
                      as double,
            totalEMIs: null == totalEMIs
                ? _value.totalEMIs
                : totalEMIs // ignore: cast_nullable_to_non_nullable
                      as int,
            paidEMIs: null == paidEMIs
                ? _value.paidEMIs
                : paidEMIs // ignore: cast_nullable_to_non_nullable
                      as int,
            pendingEMIs: null == pendingEMIs
                ? _value.pendingEMIs
                : pendingEMIs // ignore: cast_nullable_to_non_nullable
                      as int,
            totalDue: null == totalDue
                ? _value.totalDue
                : totalDue // ignore: cast_nullable_to_non_nullable
                      as double,
            dueDay: null == dueDay
                ? _value.dueDay
                : dueDay // ignore: cast_nullable_to_non_nullable
                      as int,
            nextDueDate: freezed == nextDueDate
                ? _value.nextDueDate
                : nextDueDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            paymentStatus: null == paymentStatus
                ? _value.paymentStatus
                : paymentStatus // ignore: cast_nullable_to_non_nullable
                      as PaymentStatus,
            isHighPriority: null == isHighPriority
                ? _value.isHighPriority
                : isHighPriority // ignore: cast_nullable_to_non_nullable
                      as bool,
            preferredCollectionTime: freezed == preferredCollectionTime
                ? _value.preferredCollectionTime
                : preferredCollectionTime // ignore: cast_nullable_to_non_nullable
                      as String?,
            landmark: freezed == landmark
                ? _value.landmark
                : landmark // ignore: cast_nullable_to_non_nullable
                      as String?,
            location: freezed == location
                ? _value.location
                : location // ignore: cast_nullable_to_non_nullable
                      as GeoLocation?,
            visitHistory: null == visitHistory
                ? _value.visitHistory
                : visitHistory // ignore: cast_nullable_to_non_nullable
                      as List<PreviousVisit>,
            specialInstructions: freezed == specialInstructions
                ? _value.specialInstructions
                : specialInstructions // ignore: cast_nullable_to_non_nullable
                      as String?,
            assignedAt: freezed == assignedAt
                ? _value.assignedAt
                : assignedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            lastCollectedAt: freezed == lastCollectedAt
                ? _value.lastCollectedAt
                : lastCollectedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$EMICustomerAssignmentImplCopyWith<$Res>
    implements $EMICustomerAssignmentCopyWith<$Res> {
  factory _$$EMICustomerAssignmentImplCopyWith(
    _$EMICustomerAssignmentImpl value,
    $Res Function(_$EMICustomerAssignmentImpl) then,
  ) = __$$EMICustomerAssignmentImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String customerId,
    String customerName,
    String customerPhone,
    String customerAddress,
    String bookingId,
    String plotNumber,
    String colonyName,
    double monthlyEMI,
    int totalEMIs,
    int paidEMIs,
    int pendingEMIs,
    double totalDue,
    int dueDay,
    DateTime? nextDueDate,
    PaymentStatus paymentStatus,
    bool isHighPriority,
    String? preferredCollectionTime,
    String? landmark,
    GeoLocation? location,
    List<PreviousVisit> visitHistory,
    String? specialInstructions,
    DateTime? assignedAt,
    DateTime? lastCollectedAt,
  });
}

/// @nodoc
class __$$EMICustomerAssignmentImplCopyWithImpl<$Res>
    extends
        _$EMICustomerAssignmentCopyWithImpl<$Res, _$EMICustomerAssignmentImpl>
    implements _$$EMICustomerAssignmentImplCopyWith<$Res> {
  __$$EMICustomerAssignmentImplCopyWithImpl(
    _$EMICustomerAssignmentImpl _value,
    $Res Function(_$EMICustomerAssignmentImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of EMICustomerAssignment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? customerId = null,
    Object? customerName = null,
    Object? customerPhone = null,
    Object? customerAddress = null,
    Object? bookingId = null,
    Object? plotNumber = null,
    Object? colonyName = null,
    Object? monthlyEMI = null,
    Object? totalEMIs = null,
    Object? paidEMIs = null,
    Object? pendingEMIs = null,
    Object? totalDue = null,
    Object? dueDay = null,
    Object? nextDueDate = freezed,
    Object? paymentStatus = null,
    Object? isHighPriority = null,
    Object? preferredCollectionTime = freezed,
    Object? landmark = freezed,
    Object? location = freezed,
    Object? visitHistory = null,
    Object? specialInstructions = freezed,
    Object? assignedAt = freezed,
    Object? lastCollectedAt = freezed,
  }) {
    return _then(
      _$EMICustomerAssignmentImpl(
        customerId: null == customerId
            ? _value.customerId
            : customerId // ignore: cast_nullable_to_non_nullable
                  as String,
        customerName: null == customerName
            ? _value.customerName
            : customerName // ignore: cast_nullable_to_non_nullable
                  as String,
        customerPhone: null == customerPhone
            ? _value.customerPhone
            : customerPhone // ignore: cast_nullable_to_non_nullable
                  as String,
        customerAddress: null == customerAddress
            ? _value.customerAddress
            : customerAddress // ignore: cast_nullable_to_non_nullable
                  as String,
        bookingId: null == bookingId
            ? _value.bookingId
            : bookingId // ignore: cast_nullable_to_non_nullable
                  as String,
        plotNumber: null == plotNumber
            ? _value.plotNumber
            : plotNumber // ignore: cast_nullable_to_non_nullable
                  as String,
        colonyName: null == colonyName
            ? _value.colonyName
            : colonyName // ignore: cast_nullable_to_non_nullable
                  as String,
        monthlyEMI: null == monthlyEMI
            ? _value.monthlyEMI
            : monthlyEMI // ignore: cast_nullable_to_non_nullable
                  as double,
        totalEMIs: null == totalEMIs
            ? _value.totalEMIs
            : totalEMIs // ignore: cast_nullable_to_non_nullable
                  as int,
        paidEMIs: null == paidEMIs
            ? _value.paidEMIs
            : paidEMIs // ignore: cast_nullable_to_non_nullable
                  as int,
        pendingEMIs: null == pendingEMIs
            ? _value.pendingEMIs
            : pendingEMIs // ignore: cast_nullable_to_non_nullable
                  as int,
        totalDue: null == totalDue
            ? _value.totalDue
            : totalDue // ignore: cast_nullable_to_non_nullable
                  as double,
        dueDay: null == dueDay
            ? _value.dueDay
            : dueDay // ignore: cast_nullable_to_non_nullable
                  as int,
        nextDueDate: freezed == nextDueDate
            ? _value.nextDueDate
            : nextDueDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        paymentStatus: null == paymentStatus
            ? _value.paymentStatus
            : paymentStatus // ignore: cast_nullable_to_non_nullable
                  as PaymentStatus,
        isHighPriority: null == isHighPriority
            ? _value.isHighPriority
            : isHighPriority // ignore: cast_nullable_to_non_nullable
                  as bool,
        preferredCollectionTime: freezed == preferredCollectionTime
            ? _value.preferredCollectionTime
            : preferredCollectionTime // ignore: cast_nullable_to_non_nullable
                  as String?,
        landmark: freezed == landmark
            ? _value.landmark
            : landmark // ignore: cast_nullable_to_non_nullable
                  as String?,
        location: freezed == location
            ? _value.location
            : location // ignore: cast_nullable_to_non_nullable
                  as GeoLocation?,
        visitHistory: null == visitHistory
            ? _value._visitHistory
            : visitHistory // ignore: cast_nullable_to_non_nullable
                  as List<PreviousVisit>,
        specialInstructions: freezed == specialInstructions
            ? _value.specialInstructions
            : specialInstructions // ignore: cast_nullable_to_non_nullable
                  as String?,
        assignedAt: freezed == assignedAt
            ? _value.assignedAt
            : assignedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        lastCollectedAt: freezed == lastCollectedAt
            ? _value.lastCollectedAt
            : lastCollectedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$EMICustomerAssignmentImpl implements _EMICustomerAssignment {
  const _$EMICustomerAssignmentImpl({
    required this.customerId,
    required this.customerName,
    required this.customerPhone,
    required this.customerAddress,
    required this.bookingId,
    required this.plotNumber,
    required this.colonyName,
    required this.monthlyEMI,
    required this.totalEMIs,
    required this.paidEMIs,
    required this.pendingEMIs,
    required this.totalDue,
    required this.dueDay,
    this.nextDueDate,
    this.paymentStatus = PaymentStatus.regular,
    this.isHighPriority = false,
    this.preferredCollectionTime,
    this.landmark,
    this.location,
    final List<PreviousVisit> visitHistory = const [],
    this.specialInstructions,
    this.assignedAt,
    this.lastCollectedAt,
  }) : _visitHistory = visitHistory;

  factory _$EMICustomerAssignmentImpl.fromJson(Map<String, dynamic> json) =>
      _$$EMICustomerAssignmentImplFromJson(json);

  @override
  final String customerId;
  @override
  final String customerName;
  @override
  final String customerPhone;
  @override
  final String customerAddress;
  @override
  final String bookingId;
  @override
  final String plotNumber;
  @override
  final String colonyName;
  // EMI Details
  @override
  final double monthlyEMI;
  @override
  final int totalEMIs;
  @override
  final int paidEMIs;
  @override
  final int pendingEMIs;
  @override
  final double totalDue;
  // Due Date
  @override
  final int dueDay;
  // 5th, 10th, 15th of month
  @override
  final DateTime? nextDueDate;
  // Status
  @override
  @JsonKey()
  final PaymentStatus paymentStatus;
  // Regular, Irregular, Defaulter
  @override
  @JsonKey()
  final bool isHighPriority;
  // For overdue
  // Collection Info
  @override
  final String? preferredCollectionTime;
  // Morning, Afternoon, Evening
  @override
  final String? landmark;
  @override
  final GeoLocation? location;
  // History
  final List<PreviousVisit> _visitHistory;
  // History
  @override
  @JsonKey()
  List<PreviousVisit> get visitHistory {
    if (_visitHistory is EqualUnmodifiableListView) return _visitHistory;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_visitHistory);
  }

  @override
  final String? specialInstructions;
  @override
  final DateTime? assignedAt;
  @override
  final DateTime? lastCollectedAt;

  @override
  String toString() {
    return 'EMICustomerAssignment(customerId: $customerId, customerName: $customerName, customerPhone: $customerPhone, customerAddress: $customerAddress, bookingId: $bookingId, plotNumber: $plotNumber, colonyName: $colonyName, monthlyEMI: $monthlyEMI, totalEMIs: $totalEMIs, paidEMIs: $paidEMIs, pendingEMIs: $pendingEMIs, totalDue: $totalDue, dueDay: $dueDay, nextDueDate: $nextDueDate, paymentStatus: $paymentStatus, isHighPriority: $isHighPriority, preferredCollectionTime: $preferredCollectionTime, landmark: $landmark, location: $location, visitHistory: $visitHistory, specialInstructions: $specialInstructions, assignedAt: $assignedAt, lastCollectedAt: $lastCollectedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$EMICustomerAssignmentImpl &&
            (identical(other.customerId, customerId) ||
                other.customerId == customerId) &&
            (identical(other.customerName, customerName) ||
                other.customerName == customerName) &&
            (identical(other.customerPhone, customerPhone) ||
                other.customerPhone == customerPhone) &&
            (identical(other.customerAddress, customerAddress) ||
                other.customerAddress == customerAddress) &&
            (identical(other.bookingId, bookingId) ||
                other.bookingId == bookingId) &&
            (identical(other.plotNumber, plotNumber) ||
                other.plotNumber == plotNumber) &&
            (identical(other.colonyName, colonyName) ||
                other.colonyName == colonyName) &&
            (identical(other.monthlyEMI, monthlyEMI) ||
                other.monthlyEMI == monthlyEMI) &&
            (identical(other.totalEMIs, totalEMIs) ||
                other.totalEMIs == totalEMIs) &&
            (identical(other.paidEMIs, paidEMIs) ||
                other.paidEMIs == paidEMIs) &&
            (identical(other.pendingEMIs, pendingEMIs) ||
                other.pendingEMIs == pendingEMIs) &&
            (identical(other.totalDue, totalDue) ||
                other.totalDue == totalDue) &&
            (identical(other.dueDay, dueDay) || other.dueDay == dueDay) &&
            (identical(other.nextDueDate, nextDueDate) ||
                other.nextDueDate == nextDueDate) &&
            (identical(other.paymentStatus, paymentStatus) ||
                other.paymentStatus == paymentStatus) &&
            (identical(other.isHighPriority, isHighPriority) ||
                other.isHighPriority == isHighPriority) &&
            (identical(
                  other.preferredCollectionTime,
                  preferredCollectionTime,
                ) ||
                other.preferredCollectionTime == preferredCollectionTime) &&
            (identical(other.landmark, landmark) ||
                other.landmark == landmark) &&
            (identical(other.location, location) ||
                other.location == location) &&
            const DeepCollectionEquality().equals(
              other._visitHistory,
              _visitHistory,
            ) &&
            (identical(other.specialInstructions, specialInstructions) ||
                other.specialInstructions == specialInstructions) &&
            (identical(other.assignedAt, assignedAt) ||
                other.assignedAt == assignedAt) &&
            (identical(other.lastCollectedAt, lastCollectedAt) ||
                other.lastCollectedAt == lastCollectedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    customerId,
    customerName,
    customerPhone,
    customerAddress,
    bookingId,
    plotNumber,
    colonyName,
    monthlyEMI,
    totalEMIs,
    paidEMIs,
    pendingEMIs,
    totalDue,
    dueDay,
    nextDueDate,
    paymentStatus,
    isHighPriority,
    preferredCollectionTime,
    landmark,
    location,
    const DeepCollectionEquality().hash(_visitHistory),
    specialInstructions,
    assignedAt,
    lastCollectedAt,
  ]);

  /// Create a copy of EMICustomerAssignment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$EMICustomerAssignmentImplCopyWith<_$EMICustomerAssignmentImpl>
  get copyWith =>
      __$$EMICustomerAssignmentImplCopyWithImpl<_$EMICustomerAssignmentImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$EMICustomerAssignmentImplToJson(this);
  }
}

abstract class _EMICustomerAssignment implements EMICustomerAssignment {
  const factory _EMICustomerAssignment({
    required final String customerId,
    required final String customerName,
    required final String customerPhone,
    required final String customerAddress,
    required final String bookingId,
    required final String plotNumber,
    required final String colonyName,
    required final double monthlyEMI,
    required final int totalEMIs,
    required final int paidEMIs,
    required final int pendingEMIs,
    required final double totalDue,
    required final int dueDay,
    final DateTime? nextDueDate,
    final PaymentStatus paymentStatus,
    final bool isHighPriority,
    final String? preferredCollectionTime,
    final String? landmark,
    final GeoLocation? location,
    final List<PreviousVisit> visitHistory,
    final String? specialInstructions,
    final DateTime? assignedAt,
    final DateTime? lastCollectedAt,
  }) = _$EMICustomerAssignmentImpl;

  factory _EMICustomerAssignment.fromJson(Map<String, dynamic> json) =
      _$EMICustomerAssignmentImpl.fromJson;

  @override
  String get customerId;
  @override
  String get customerName;
  @override
  String get customerPhone;
  @override
  String get customerAddress;
  @override
  String get bookingId;
  @override
  String get plotNumber;
  @override
  String get colonyName; // EMI Details
  @override
  double get monthlyEMI;
  @override
  int get totalEMIs;
  @override
  int get paidEMIs;
  @override
  int get pendingEMIs;
  @override
  double get totalDue; // Due Date
  @override
  int get dueDay; // 5th, 10th, 15th of month
  @override
  DateTime? get nextDueDate; // Status
  @override
  PaymentStatus get paymentStatus; // Regular, Irregular, Defaulter
  @override
  bool get isHighPriority; // For overdue
  // Collection Info
  @override
  String? get preferredCollectionTime; // Morning, Afternoon, Evening
  @override
  String? get landmark;
  @override
  GeoLocation? get location; // History
  @override
  List<PreviousVisit> get visitHistory;
  @override
  String? get specialInstructions;
  @override
  DateTime? get assignedAt;
  @override
  DateTime? get lastCollectedAt;

  /// Create a copy of EMICustomerAssignment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$EMICustomerAssignmentImplCopyWith<_$EMICustomerAssignmentImpl>
  get copyWith => throw _privateConstructorUsedError;
}

PreviousVisit _$PreviousVisitFromJson(Map<String, dynamic> json) {
  return _PreviousVisit.fromJson(json);
}

/// @nodoc
mixin _$PreviousVisit {
  DateTime get visitDate => throw _privateConstructorUsedError;
  VisitOutcome get outcome => throw _privateConstructorUsedError;
  double? get amountCollected => throw _privateConstructorUsedError;
  String? get notes => throw _privateConstructorUsedError;
  String? get customerFeedback => throw _privateConstructorUsedError;

  /// Serializes this PreviousVisit to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of PreviousVisit
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $PreviousVisitCopyWith<PreviousVisit> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $PreviousVisitCopyWith<$Res> {
  factory $PreviousVisitCopyWith(
    PreviousVisit value,
    $Res Function(PreviousVisit) then,
  ) = _$PreviousVisitCopyWithImpl<$Res, PreviousVisit>;
  @useResult
  $Res call({
    DateTime visitDate,
    VisitOutcome outcome,
    double? amountCollected,
    String? notes,
    String? customerFeedback,
  });
}

/// @nodoc
class _$PreviousVisitCopyWithImpl<$Res, $Val extends PreviousVisit>
    implements $PreviousVisitCopyWith<$Res> {
  _$PreviousVisitCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of PreviousVisit
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? visitDate = null,
    Object? outcome = null,
    Object? amountCollected = freezed,
    Object? notes = freezed,
    Object? customerFeedback = freezed,
  }) {
    return _then(
      _value.copyWith(
            visitDate: null == visitDate
                ? _value.visitDate
                : visitDate // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            outcome: null == outcome
                ? _value.outcome
                : outcome // ignore: cast_nullable_to_non_nullable
                      as VisitOutcome,
            amountCollected: freezed == amountCollected
                ? _value.amountCollected
                : amountCollected // ignore: cast_nullable_to_non_nullable
                      as double?,
            notes: freezed == notes
                ? _value.notes
                : notes // ignore: cast_nullable_to_non_nullable
                      as String?,
            customerFeedback: freezed == customerFeedback
                ? _value.customerFeedback
                : customerFeedback // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$PreviousVisitImplCopyWith<$Res>
    implements $PreviousVisitCopyWith<$Res> {
  factory _$$PreviousVisitImplCopyWith(
    _$PreviousVisitImpl value,
    $Res Function(_$PreviousVisitImpl) then,
  ) = __$$PreviousVisitImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    DateTime visitDate,
    VisitOutcome outcome,
    double? amountCollected,
    String? notes,
    String? customerFeedback,
  });
}

/// @nodoc
class __$$PreviousVisitImplCopyWithImpl<$Res>
    extends _$PreviousVisitCopyWithImpl<$Res, _$PreviousVisitImpl>
    implements _$$PreviousVisitImplCopyWith<$Res> {
  __$$PreviousVisitImplCopyWithImpl(
    _$PreviousVisitImpl _value,
    $Res Function(_$PreviousVisitImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of PreviousVisit
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? visitDate = null,
    Object? outcome = null,
    Object? amountCollected = freezed,
    Object? notes = freezed,
    Object? customerFeedback = freezed,
  }) {
    return _then(
      _$PreviousVisitImpl(
        visitDate: null == visitDate
            ? _value.visitDate
            : visitDate // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        outcome: null == outcome
            ? _value.outcome
            : outcome // ignore: cast_nullable_to_non_nullable
                  as VisitOutcome,
        amountCollected: freezed == amountCollected
            ? _value.amountCollected
            : amountCollected // ignore: cast_nullable_to_non_nullable
                  as double?,
        notes: freezed == notes
            ? _value.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        customerFeedback: freezed == customerFeedback
            ? _value.customerFeedback
            : customerFeedback // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$PreviousVisitImpl implements _PreviousVisit {
  const _$PreviousVisitImpl({
    required this.visitDate,
    required this.outcome,
    this.amountCollected,
    this.notes,
    this.customerFeedback,
  });

  factory _$PreviousVisitImpl.fromJson(Map<String, dynamic> json) =>
      _$$PreviousVisitImplFromJson(json);

  @override
  final DateTime visitDate;
  @override
  final VisitOutcome outcome;
  @override
  final double? amountCollected;
  @override
  final String? notes;
  @override
  final String? customerFeedback;

  @override
  String toString() {
    return 'PreviousVisit(visitDate: $visitDate, outcome: $outcome, amountCollected: $amountCollected, notes: $notes, customerFeedback: $customerFeedback)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$PreviousVisitImpl &&
            (identical(other.visitDate, visitDate) ||
                other.visitDate == visitDate) &&
            (identical(other.outcome, outcome) || other.outcome == outcome) &&
            (identical(other.amountCollected, amountCollected) ||
                other.amountCollected == amountCollected) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            (identical(other.customerFeedback, customerFeedback) ||
                other.customerFeedback == customerFeedback));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    visitDate,
    outcome,
    amountCollected,
    notes,
    customerFeedback,
  );

  /// Create a copy of PreviousVisit
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$PreviousVisitImplCopyWith<_$PreviousVisitImpl> get copyWith =>
      __$$PreviousVisitImplCopyWithImpl<_$PreviousVisitImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$PreviousVisitImplToJson(this);
  }
}

abstract class _PreviousVisit implements PreviousVisit {
  const factory _PreviousVisit({
    required final DateTime visitDate,
    required final VisitOutcome outcome,
    final double? amountCollected,
    final String? notes,
    final String? customerFeedback,
  }) = _$PreviousVisitImpl;

  factory _PreviousVisit.fromJson(Map<String, dynamic> json) =
      _$PreviousVisitImpl.fromJson;

  @override
  DateTime get visitDate;
  @override
  VisitOutcome get outcome;
  @override
  double? get amountCollected;
  @override
  String? get notes;
  @override
  String? get customerFeedback;

  /// Create a copy of PreviousVisit
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$PreviousVisitImplCopyWith<_$PreviousVisitImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

DailyCollectionReport _$DailyCollectionReportFromJson(
  Map<String, dynamic> json,
) {
  return _DailyCollectionReport.fromJson(json);
}

/// @nodoc
mixin _$DailyCollectionReport {
  String get id => throw _privateConstructorUsedError;
  DateTime get date => throw _privateConstructorUsedError;
  String get agentId =>
      throw _privateConstructorUsedError; // Collection Summary
  int get totalVisits => throw _privateConstructorUsedError;
  int get successfulCollections => throw _privateConstructorUsedError;
  int get partialCollections => throw _privateConstructorUsedError;
  int get failedVisits => throw _privateConstructorUsedError;
  int get rescheduled => throw _privateConstructorUsedError;
  int get customersNotHome => throw _privateConstructorUsedError; // Financial
  double get totalCollected => throw _privateConstructorUsedError;
  double get cashCollected => throw _privateConstructorUsedError;
  double get chequeCollected => throw _privateConstructorUsedError;
  double get onlineCollected => throw _privateConstructorUsedError;
  double get upiCollected =>
      throw _privateConstructorUsedError; // Individual Collections
  List<CollectionRecord> get collections =>
      throw _privateConstructorUsedError; // Time Tracking
  DateTime? get startTime => throw _privateConstructorUsedError;
  DateTime? get endTime => throw _privateConstructorUsedError;
  int get workingHours => throw _privateConstructorUsedError; // Location Data
  List<LocationTracking> get routeTaken => throw _privateConstructorUsedError;
  double get totalDistanceKm => throw _privateConstructorUsedError; // Status
  ReportSubmissionStatus get submissionStatus =>
      throw _privateConstructorUsedError;
  DateTime? get submittedAt => throw _privateConstructorUsedError;
  String? get adminNotes => throw _privateConstructorUsedError;

  /// Serializes this DailyCollectionReport to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of DailyCollectionReport
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $DailyCollectionReportCopyWith<DailyCollectionReport> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $DailyCollectionReportCopyWith<$Res> {
  factory $DailyCollectionReportCopyWith(
    DailyCollectionReport value,
    $Res Function(DailyCollectionReport) then,
  ) = _$DailyCollectionReportCopyWithImpl<$Res, DailyCollectionReport>;
  @useResult
  $Res call({
    String id,
    DateTime date,
    String agentId,
    int totalVisits,
    int successfulCollections,
    int partialCollections,
    int failedVisits,
    int rescheduled,
    int customersNotHome,
    double totalCollected,
    double cashCollected,
    double chequeCollected,
    double onlineCollected,
    double upiCollected,
    List<CollectionRecord> collections,
    DateTime? startTime,
    DateTime? endTime,
    int workingHours,
    List<LocationTracking> routeTaken,
    double totalDistanceKm,
    ReportSubmissionStatus submissionStatus,
    DateTime? submittedAt,
    String? adminNotes,
  });
}

/// @nodoc
class _$DailyCollectionReportCopyWithImpl<
  $Res,
  $Val extends DailyCollectionReport
>
    implements $DailyCollectionReportCopyWith<$Res> {
  _$DailyCollectionReportCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of DailyCollectionReport
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? date = null,
    Object? agentId = null,
    Object? totalVisits = null,
    Object? successfulCollections = null,
    Object? partialCollections = null,
    Object? failedVisits = null,
    Object? rescheduled = null,
    Object? customersNotHome = null,
    Object? totalCollected = null,
    Object? cashCollected = null,
    Object? chequeCollected = null,
    Object? onlineCollected = null,
    Object? upiCollected = null,
    Object? collections = null,
    Object? startTime = freezed,
    Object? endTime = freezed,
    Object? workingHours = null,
    Object? routeTaken = null,
    Object? totalDistanceKm = null,
    Object? submissionStatus = null,
    Object? submittedAt = freezed,
    Object? adminNotes = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            date: null == date
                ? _value.date
                : date // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            agentId: null == agentId
                ? _value.agentId
                : agentId // ignore: cast_nullable_to_non_nullable
                      as String,
            totalVisits: null == totalVisits
                ? _value.totalVisits
                : totalVisits // ignore: cast_nullable_to_non_nullable
                      as int,
            successfulCollections: null == successfulCollections
                ? _value.successfulCollections
                : successfulCollections // ignore: cast_nullable_to_non_nullable
                      as int,
            partialCollections: null == partialCollections
                ? _value.partialCollections
                : partialCollections // ignore: cast_nullable_to_non_nullable
                      as int,
            failedVisits: null == failedVisits
                ? _value.failedVisits
                : failedVisits // ignore: cast_nullable_to_non_nullable
                      as int,
            rescheduled: null == rescheduled
                ? _value.rescheduled
                : rescheduled // ignore: cast_nullable_to_non_nullable
                      as int,
            customersNotHome: null == customersNotHome
                ? _value.customersNotHome
                : customersNotHome // ignore: cast_nullable_to_non_nullable
                      as int,
            totalCollected: null == totalCollected
                ? _value.totalCollected
                : totalCollected // ignore: cast_nullable_to_non_nullable
                      as double,
            cashCollected: null == cashCollected
                ? _value.cashCollected
                : cashCollected // ignore: cast_nullable_to_non_nullable
                      as double,
            chequeCollected: null == chequeCollected
                ? _value.chequeCollected
                : chequeCollected // ignore: cast_nullable_to_non_nullable
                      as double,
            onlineCollected: null == onlineCollected
                ? _value.onlineCollected
                : onlineCollected // ignore: cast_nullable_to_non_nullable
                      as double,
            upiCollected: null == upiCollected
                ? _value.upiCollected
                : upiCollected // ignore: cast_nullable_to_non_nullable
                      as double,
            collections: null == collections
                ? _value.collections
                : collections // ignore: cast_nullable_to_non_nullable
                      as List<CollectionRecord>,
            startTime: freezed == startTime
                ? _value.startTime
                : startTime // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            endTime: freezed == endTime
                ? _value.endTime
                : endTime // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            workingHours: null == workingHours
                ? _value.workingHours
                : workingHours // ignore: cast_nullable_to_non_nullable
                      as int,
            routeTaken: null == routeTaken
                ? _value.routeTaken
                : routeTaken // ignore: cast_nullable_to_non_nullable
                      as List<LocationTracking>,
            totalDistanceKm: null == totalDistanceKm
                ? _value.totalDistanceKm
                : totalDistanceKm // ignore: cast_nullable_to_non_nullable
                      as double,
            submissionStatus: null == submissionStatus
                ? _value.submissionStatus
                : submissionStatus // ignore: cast_nullable_to_non_nullable
                      as ReportSubmissionStatus,
            submittedAt: freezed == submittedAt
                ? _value.submittedAt
                : submittedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            adminNotes: freezed == adminNotes
                ? _value.adminNotes
                : adminNotes // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$DailyCollectionReportImplCopyWith<$Res>
    implements $DailyCollectionReportCopyWith<$Res> {
  factory _$$DailyCollectionReportImplCopyWith(
    _$DailyCollectionReportImpl value,
    $Res Function(_$DailyCollectionReportImpl) then,
  ) = __$$DailyCollectionReportImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    DateTime date,
    String agentId,
    int totalVisits,
    int successfulCollections,
    int partialCollections,
    int failedVisits,
    int rescheduled,
    int customersNotHome,
    double totalCollected,
    double cashCollected,
    double chequeCollected,
    double onlineCollected,
    double upiCollected,
    List<CollectionRecord> collections,
    DateTime? startTime,
    DateTime? endTime,
    int workingHours,
    List<LocationTracking> routeTaken,
    double totalDistanceKm,
    ReportSubmissionStatus submissionStatus,
    DateTime? submittedAt,
    String? adminNotes,
  });
}

/// @nodoc
class __$$DailyCollectionReportImplCopyWithImpl<$Res>
    extends
        _$DailyCollectionReportCopyWithImpl<$Res, _$DailyCollectionReportImpl>
    implements _$$DailyCollectionReportImplCopyWith<$Res> {
  __$$DailyCollectionReportImplCopyWithImpl(
    _$DailyCollectionReportImpl _value,
    $Res Function(_$DailyCollectionReportImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of DailyCollectionReport
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? date = null,
    Object? agentId = null,
    Object? totalVisits = null,
    Object? successfulCollections = null,
    Object? partialCollections = null,
    Object? failedVisits = null,
    Object? rescheduled = null,
    Object? customersNotHome = null,
    Object? totalCollected = null,
    Object? cashCollected = null,
    Object? chequeCollected = null,
    Object? onlineCollected = null,
    Object? upiCollected = null,
    Object? collections = null,
    Object? startTime = freezed,
    Object? endTime = freezed,
    Object? workingHours = null,
    Object? routeTaken = null,
    Object? totalDistanceKm = null,
    Object? submissionStatus = null,
    Object? submittedAt = freezed,
    Object? adminNotes = freezed,
  }) {
    return _then(
      _$DailyCollectionReportImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        date: null == date
            ? _value.date
            : date // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        agentId: null == agentId
            ? _value.agentId
            : agentId // ignore: cast_nullable_to_non_nullable
                  as String,
        totalVisits: null == totalVisits
            ? _value.totalVisits
            : totalVisits // ignore: cast_nullable_to_non_nullable
                  as int,
        successfulCollections: null == successfulCollections
            ? _value.successfulCollections
            : successfulCollections // ignore: cast_nullable_to_non_nullable
                  as int,
        partialCollections: null == partialCollections
            ? _value.partialCollections
            : partialCollections // ignore: cast_nullable_to_non_nullable
                  as int,
        failedVisits: null == failedVisits
            ? _value.failedVisits
            : failedVisits // ignore: cast_nullable_to_non_nullable
                  as int,
        rescheduled: null == rescheduled
            ? _value.rescheduled
            : rescheduled // ignore: cast_nullable_to_non_nullable
                  as int,
        customersNotHome: null == customersNotHome
            ? _value.customersNotHome
            : customersNotHome // ignore: cast_nullable_to_non_nullable
                  as int,
        totalCollected: null == totalCollected
            ? _value.totalCollected
            : totalCollected // ignore: cast_nullable_to_non_nullable
                  as double,
        cashCollected: null == cashCollected
            ? _value.cashCollected
            : cashCollected // ignore: cast_nullable_to_non_nullable
                  as double,
        chequeCollected: null == chequeCollected
            ? _value.chequeCollected
            : chequeCollected // ignore: cast_nullable_to_non_nullable
                  as double,
        onlineCollected: null == onlineCollected
            ? _value.onlineCollected
            : onlineCollected // ignore: cast_nullable_to_non_nullable
                  as double,
        upiCollected: null == upiCollected
            ? _value.upiCollected
            : upiCollected // ignore: cast_nullable_to_non_nullable
                  as double,
        collections: null == collections
            ? _value._collections
            : collections // ignore: cast_nullable_to_non_nullable
                  as List<CollectionRecord>,
        startTime: freezed == startTime
            ? _value.startTime
            : startTime // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        endTime: freezed == endTime
            ? _value.endTime
            : endTime // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        workingHours: null == workingHours
            ? _value.workingHours
            : workingHours // ignore: cast_nullable_to_non_nullable
                  as int,
        routeTaken: null == routeTaken
            ? _value._routeTaken
            : routeTaken // ignore: cast_nullable_to_non_nullable
                  as List<LocationTracking>,
        totalDistanceKm: null == totalDistanceKm
            ? _value.totalDistanceKm
            : totalDistanceKm // ignore: cast_nullable_to_non_nullable
                  as double,
        submissionStatus: null == submissionStatus
            ? _value.submissionStatus
            : submissionStatus // ignore: cast_nullable_to_non_nullable
                  as ReportSubmissionStatus,
        submittedAt: freezed == submittedAt
            ? _value.submittedAt
            : submittedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        adminNotes: freezed == adminNotes
            ? _value.adminNotes
            : adminNotes // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$DailyCollectionReportImpl implements _DailyCollectionReport {
  const _$DailyCollectionReportImpl({
    required this.id,
    required this.date,
    required this.agentId,
    this.totalVisits = 0,
    this.successfulCollections = 0,
    this.partialCollections = 0,
    this.failedVisits = 0,
    this.rescheduled = 0,
    this.customersNotHome = 0,
    this.totalCollected = 0,
    this.cashCollected = 0,
    this.chequeCollected = 0,
    this.onlineCollected = 0,
    this.upiCollected = 0,
    final List<CollectionRecord> collections = const [],
    this.startTime,
    this.endTime,
    this.workingHours = 0,
    final List<LocationTracking> routeTaken = const [],
    this.totalDistanceKm = 0,
    required this.submissionStatus,
    this.submittedAt,
    this.adminNotes,
  }) : _collections = collections,
       _routeTaken = routeTaken;

  factory _$DailyCollectionReportImpl.fromJson(Map<String, dynamic> json) =>
      _$$DailyCollectionReportImplFromJson(json);

  @override
  final String id;
  @override
  final DateTime date;
  @override
  final String agentId;
  // Collection Summary
  @override
  @JsonKey()
  final int totalVisits;
  @override
  @JsonKey()
  final int successfulCollections;
  @override
  @JsonKey()
  final int partialCollections;
  @override
  @JsonKey()
  final int failedVisits;
  @override
  @JsonKey()
  final int rescheduled;
  @override
  @JsonKey()
  final int customersNotHome;
  // Financial
  @override
  @JsonKey()
  final double totalCollected;
  @override
  @JsonKey()
  final double cashCollected;
  @override
  @JsonKey()
  final double chequeCollected;
  @override
  @JsonKey()
  final double onlineCollected;
  @override
  @JsonKey()
  final double upiCollected;
  // Individual Collections
  final List<CollectionRecord> _collections;
  // Individual Collections
  @override
  @JsonKey()
  List<CollectionRecord> get collections {
    if (_collections is EqualUnmodifiableListView) return _collections;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_collections);
  }

  // Time Tracking
  @override
  final DateTime? startTime;
  @override
  final DateTime? endTime;
  @override
  @JsonKey()
  final int workingHours;
  // Location Data
  final List<LocationTracking> _routeTaken;
  // Location Data
  @override
  @JsonKey()
  List<LocationTracking> get routeTaken {
    if (_routeTaken is EqualUnmodifiableListView) return _routeTaken;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_routeTaken);
  }

  @override
  @JsonKey()
  final double totalDistanceKm;
  // Status
  @override
  final ReportSubmissionStatus submissionStatus;
  @override
  final DateTime? submittedAt;
  @override
  final String? adminNotes;

  @override
  String toString() {
    return 'DailyCollectionReport(id: $id, date: $date, agentId: $agentId, totalVisits: $totalVisits, successfulCollections: $successfulCollections, partialCollections: $partialCollections, failedVisits: $failedVisits, rescheduled: $rescheduled, customersNotHome: $customersNotHome, totalCollected: $totalCollected, cashCollected: $cashCollected, chequeCollected: $chequeCollected, onlineCollected: $onlineCollected, upiCollected: $upiCollected, collections: $collections, startTime: $startTime, endTime: $endTime, workingHours: $workingHours, routeTaken: $routeTaken, totalDistanceKm: $totalDistanceKm, submissionStatus: $submissionStatus, submittedAt: $submittedAt, adminNotes: $adminNotes)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$DailyCollectionReportImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.date, date) || other.date == date) &&
            (identical(other.agentId, agentId) || other.agentId == agentId) &&
            (identical(other.totalVisits, totalVisits) ||
                other.totalVisits == totalVisits) &&
            (identical(other.successfulCollections, successfulCollections) ||
                other.successfulCollections == successfulCollections) &&
            (identical(other.partialCollections, partialCollections) ||
                other.partialCollections == partialCollections) &&
            (identical(other.failedVisits, failedVisits) ||
                other.failedVisits == failedVisits) &&
            (identical(other.rescheduled, rescheduled) ||
                other.rescheduled == rescheduled) &&
            (identical(other.customersNotHome, customersNotHome) ||
                other.customersNotHome == customersNotHome) &&
            (identical(other.totalCollected, totalCollected) ||
                other.totalCollected == totalCollected) &&
            (identical(other.cashCollected, cashCollected) ||
                other.cashCollected == cashCollected) &&
            (identical(other.chequeCollected, chequeCollected) ||
                other.chequeCollected == chequeCollected) &&
            (identical(other.onlineCollected, onlineCollected) ||
                other.onlineCollected == onlineCollected) &&
            (identical(other.upiCollected, upiCollected) ||
                other.upiCollected == upiCollected) &&
            const DeepCollectionEquality().equals(
              other._collections,
              _collections,
            ) &&
            (identical(other.startTime, startTime) ||
                other.startTime == startTime) &&
            (identical(other.endTime, endTime) || other.endTime == endTime) &&
            (identical(other.workingHours, workingHours) ||
                other.workingHours == workingHours) &&
            const DeepCollectionEquality().equals(
              other._routeTaken,
              _routeTaken,
            ) &&
            (identical(other.totalDistanceKm, totalDistanceKm) ||
                other.totalDistanceKm == totalDistanceKm) &&
            (identical(other.submissionStatus, submissionStatus) ||
                other.submissionStatus == submissionStatus) &&
            (identical(other.submittedAt, submittedAt) ||
                other.submittedAt == submittedAt) &&
            (identical(other.adminNotes, adminNotes) ||
                other.adminNotes == adminNotes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    id,
    date,
    agentId,
    totalVisits,
    successfulCollections,
    partialCollections,
    failedVisits,
    rescheduled,
    customersNotHome,
    totalCollected,
    cashCollected,
    chequeCollected,
    onlineCollected,
    upiCollected,
    const DeepCollectionEquality().hash(_collections),
    startTime,
    endTime,
    workingHours,
    const DeepCollectionEquality().hash(_routeTaken),
    totalDistanceKm,
    submissionStatus,
    submittedAt,
    adminNotes,
  ]);

  /// Create a copy of DailyCollectionReport
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$DailyCollectionReportImplCopyWith<_$DailyCollectionReportImpl>
  get copyWith =>
      __$$DailyCollectionReportImplCopyWithImpl<_$DailyCollectionReportImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$DailyCollectionReportImplToJson(this);
  }
}

abstract class _DailyCollectionReport implements DailyCollectionReport {
  const factory _DailyCollectionReport({
    required final String id,
    required final DateTime date,
    required final String agentId,
    final int totalVisits,
    final int successfulCollections,
    final int partialCollections,
    final int failedVisits,
    final int rescheduled,
    final int customersNotHome,
    final double totalCollected,
    final double cashCollected,
    final double chequeCollected,
    final double onlineCollected,
    final double upiCollected,
    final List<CollectionRecord> collections,
    final DateTime? startTime,
    final DateTime? endTime,
    final int workingHours,
    final List<LocationTracking> routeTaken,
    final double totalDistanceKm,
    required final ReportSubmissionStatus submissionStatus,
    final DateTime? submittedAt,
    final String? adminNotes,
  }) = _$DailyCollectionReportImpl;

  factory _DailyCollectionReport.fromJson(Map<String, dynamic> json) =
      _$DailyCollectionReportImpl.fromJson;

  @override
  String get id;
  @override
  DateTime get date;
  @override
  String get agentId; // Collection Summary
  @override
  int get totalVisits;
  @override
  int get successfulCollections;
  @override
  int get partialCollections;
  @override
  int get failedVisits;
  @override
  int get rescheduled;
  @override
  int get customersNotHome; // Financial
  @override
  double get totalCollected;
  @override
  double get cashCollected;
  @override
  double get chequeCollected;
  @override
  double get onlineCollected;
  @override
  double get upiCollected; // Individual Collections
  @override
  List<CollectionRecord> get collections; // Time Tracking
  @override
  DateTime? get startTime;
  @override
  DateTime? get endTime;
  @override
  int get workingHours; // Location Data
  @override
  List<LocationTracking> get routeTaken;
  @override
  double get totalDistanceKm; // Status
  @override
  ReportSubmissionStatus get submissionStatus;
  @override
  DateTime? get submittedAt;
  @override
  String? get adminNotes;

  /// Create a copy of DailyCollectionReport
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$DailyCollectionReportImplCopyWith<_$DailyCollectionReportImpl>
  get copyWith => throw _privateConstructorUsedError;
}

CollectionRecord _$CollectionRecordFromJson(Map<String, dynamic> json) {
  return _CollectionRecord.fromJson(json);
}

/// @nodoc
mixin _$CollectionRecord {
  String get customerId => throw _privateConstructorUsedError;
  String get customerName => throw _privateConstructorUsedError;
  String get bookingId => throw _privateConstructorUsedError;
  DateTime get collectionTime => throw _privateConstructorUsedError;
  double get amount => throw _privateConstructorUsedError;
  PaymentMode get mode => throw _privateConstructorUsedError; // Details
  int? get emiNumber => throw _privateConstructorUsedError;
  double? get lateFee => throw _privateConstructorUsedError;
  String? get chequeNumber => throw _privateConstructorUsedError;
  String? get transactionId => throw _privateConstructorUsedError;
  String? get receiptNumber => throw _privateConstructorUsedError; // Location
  GeoLocation? get location => throw _privateConstructorUsedError;
  String? get addressAtCollection =>
      throw _privateConstructorUsedError; // Proof
  List<String> get photoUrls =>
      throw _privateConstructorUsedError; // Payment proof photos
  String? get signatureUrl => throw _privateConstructorUsedError;
  String? get notes => throw _privateConstructorUsedError; // Verification
  bool? get isVerified => throw _privateConstructorUsedError;
  DateTime? get verifiedAt => throw _privateConstructorUsedError;
  String? get verifiedBy => throw _privateConstructorUsedError;
  String? get disputeReason => throw _privateConstructorUsedError;

  /// Serializes this CollectionRecord to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of CollectionRecord
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $CollectionRecordCopyWith<CollectionRecord> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $CollectionRecordCopyWith<$Res> {
  factory $CollectionRecordCopyWith(
    CollectionRecord value,
    $Res Function(CollectionRecord) then,
  ) = _$CollectionRecordCopyWithImpl<$Res, CollectionRecord>;
  @useResult
  $Res call({
    String customerId,
    String customerName,
    String bookingId,
    DateTime collectionTime,
    double amount,
    PaymentMode mode,
    int? emiNumber,
    double? lateFee,
    String? chequeNumber,
    String? transactionId,
    String? receiptNumber,
    GeoLocation? location,
    String? addressAtCollection,
    List<String> photoUrls,
    String? signatureUrl,
    String? notes,
    bool? isVerified,
    DateTime? verifiedAt,
    String? verifiedBy,
    String? disputeReason,
  });
}

/// @nodoc
class _$CollectionRecordCopyWithImpl<$Res, $Val extends CollectionRecord>
    implements $CollectionRecordCopyWith<$Res> {
  _$CollectionRecordCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of CollectionRecord
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? customerId = null,
    Object? customerName = null,
    Object? bookingId = null,
    Object? collectionTime = null,
    Object? amount = null,
    Object? mode = null,
    Object? emiNumber = freezed,
    Object? lateFee = freezed,
    Object? chequeNumber = freezed,
    Object? transactionId = freezed,
    Object? receiptNumber = freezed,
    Object? location = freezed,
    Object? addressAtCollection = freezed,
    Object? photoUrls = null,
    Object? signatureUrl = freezed,
    Object? notes = freezed,
    Object? isVerified = freezed,
    Object? verifiedAt = freezed,
    Object? verifiedBy = freezed,
    Object? disputeReason = freezed,
  }) {
    return _then(
      _value.copyWith(
            customerId: null == customerId
                ? _value.customerId
                : customerId // ignore: cast_nullable_to_non_nullable
                      as String,
            customerName: null == customerName
                ? _value.customerName
                : customerName // ignore: cast_nullable_to_non_nullable
                      as String,
            bookingId: null == bookingId
                ? _value.bookingId
                : bookingId // ignore: cast_nullable_to_non_nullable
                      as String,
            collectionTime: null == collectionTime
                ? _value.collectionTime
                : collectionTime // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            amount: null == amount
                ? _value.amount
                : amount // ignore: cast_nullable_to_non_nullable
                      as double,
            mode: null == mode
                ? _value.mode
                : mode // ignore: cast_nullable_to_non_nullable
                      as PaymentMode,
            emiNumber: freezed == emiNumber
                ? _value.emiNumber
                : emiNumber // ignore: cast_nullable_to_non_nullable
                      as int?,
            lateFee: freezed == lateFee
                ? _value.lateFee
                : lateFee // ignore: cast_nullable_to_non_nullable
                      as double?,
            chequeNumber: freezed == chequeNumber
                ? _value.chequeNumber
                : chequeNumber // ignore: cast_nullable_to_non_nullable
                      as String?,
            transactionId: freezed == transactionId
                ? _value.transactionId
                : transactionId // ignore: cast_nullable_to_non_nullable
                      as String?,
            receiptNumber: freezed == receiptNumber
                ? _value.receiptNumber
                : receiptNumber // ignore: cast_nullable_to_non_nullable
                      as String?,
            location: freezed == location
                ? _value.location
                : location // ignore: cast_nullable_to_non_nullable
                      as GeoLocation?,
            addressAtCollection: freezed == addressAtCollection
                ? _value.addressAtCollection
                : addressAtCollection // ignore: cast_nullable_to_non_nullable
                      as String?,
            photoUrls: null == photoUrls
                ? _value.photoUrls
                : photoUrls // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            signatureUrl: freezed == signatureUrl
                ? _value.signatureUrl
                : signatureUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            notes: freezed == notes
                ? _value.notes
                : notes // ignore: cast_nullable_to_non_nullable
                      as String?,
            isVerified: freezed == isVerified
                ? _value.isVerified
                : isVerified // ignore: cast_nullable_to_non_nullable
                      as bool?,
            verifiedAt: freezed == verifiedAt
                ? _value.verifiedAt
                : verifiedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            verifiedBy: freezed == verifiedBy
                ? _value.verifiedBy
                : verifiedBy // ignore: cast_nullable_to_non_nullable
                      as String?,
            disputeReason: freezed == disputeReason
                ? _value.disputeReason
                : disputeReason // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$CollectionRecordImplCopyWith<$Res>
    implements $CollectionRecordCopyWith<$Res> {
  factory _$$CollectionRecordImplCopyWith(
    _$CollectionRecordImpl value,
    $Res Function(_$CollectionRecordImpl) then,
  ) = __$$CollectionRecordImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String customerId,
    String customerName,
    String bookingId,
    DateTime collectionTime,
    double amount,
    PaymentMode mode,
    int? emiNumber,
    double? lateFee,
    String? chequeNumber,
    String? transactionId,
    String? receiptNumber,
    GeoLocation? location,
    String? addressAtCollection,
    List<String> photoUrls,
    String? signatureUrl,
    String? notes,
    bool? isVerified,
    DateTime? verifiedAt,
    String? verifiedBy,
    String? disputeReason,
  });
}

/// @nodoc
class __$$CollectionRecordImplCopyWithImpl<$Res>
    extends _$CollectionRecordCopyWithImpl<$Res, _$CollectionRecordImpl>
    implements _$$CollectionRecordImplCopyWith<$Res> {
  __$$CollectionRecordImplCopyWithImpl(
    _$CollectionRecordImpl _value,
    $Res Function(_$CollectionRecordImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of CollectionRecord
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? customerId = null,
    Object? customerName = null,
    Object? bookingId = null,
    Object? collectionTime = null,
    Object? amount = null,
    Object? mode = null,
    Object? emiNumber = freezed,
    Object? lateFee = freezed,
    Object? chequeNumber = freezed,
    Object? transactionId = freezed,
    Object? receiptNumber = freezed,
    Object? location = freezed,
    Object? addressAtCollection = freezed,
    Object? photoUrls = null,
    Object? signatureUrl = freezed,
    Object? notes = freezed,
    Object? isVerified = freezed,
    Object? verifiedAt = freezed,
    Object? verifiedBy = freezed,
    Object? disputeReason = freezed,
  }) {
    return _then(
      _$CollectionRecordImpl(
        customerId: null == customerId
            ? _value.customerId
            : customerId // ignore: cast_nullable_to_non_nullable
                  as String,
        customerName: null == customerName
            ? _value.customerName
            : customerName // ignore: cast_nullable_to_non_nullable
                  as String,
        bookingId: null == bookingId
            ? _value.bookingId
            : bookingId // ignore: cast_nullable_to_non_nullable
                  as String,
        collectionTime: null == collectionTime
            ? _value.collectionTime
            : collectionTime // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        amount: null == amount
            ? _value.amount
            : amount // ignore: cast_nullable_to_non_nullable
                  as double,
        mode: null == mode
            ? _value.mode
            : mode // ignore: cast_nullable_to_non_nullable
                  as PaymentMode,
        emiNumber: freezed == emiNumber
            ? _value.emiNumber
            : emiNumber // ignore: cast_nullable_to_non_nullable
                  as int?,
        lateFee: freezed == lateFee
            ? _value.lateFee
            : lateFee // ignore: cast_nullable_to_non_nullable
                  as double?,
        chequeNumber: freezed == chequeNumber
            ? _value.chequeNumber
            : chequeNumber // ignore: cast_nullable_to_non_nullable
                  as String?,
        transactionId: freezed == transactionId
            ? _value.transactionId
            : transactionId // ignore: cast_nullable_to_non_nullable
                  as String?,
        receiptNumber: freezed == receiptNumber
            ? _value.receiptNumber
            : receiptNumber // ignore: cast_nullable_to_non_nullable
                  as String?,
        location: freezed == location
            ? _value.location
            : location // ignore: cast_nullable_to_non_nullable
                  as GeoLocation?,
        addressAtCollection: freezed == addressAtCollection
            ? _value.addressAtCollection
            : addressAtCollection // ignore: cast_nullable_to_non_nullable
                  as String?,
        photoUrls: null == photoUrls
            ? _value._photoUrls
            : photoUrls // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        signatureUrl: freezed == signatureUrl
            ? _value.signatureUrl
            : signatureUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        notes: freezed == notes
            ? _value.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        isVerified: freezed == isVerified
            ? _value.isVerified
            : isVerified // ignore: cast_nullable_to_non_nullable
                  as bool?,
        verifiedAt: freezed == verifiedAt
            ? _value.verifiedAt
            : verifiedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        verifiedBy: freezed == verifiedBy
            ? _value.verifiedBy
            : verifiedBy // ignore: cast_nullable_to_non_nullable
                  as String?,
        disputeReason: freezed == disputeReason
            ? _value.disputeReason
            : disputeReason // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$CollectionRecordImpl implements _CollectionRecord {
  const _$CollectionRecordImpl({
    required this.customerId,
    required this.customerName,
    required this.bookingId,
    required this.collectionTime,
    required this.amount,
    required this.mode,
    this.emiNumber,
    this.lateFee,
    this.chequeNumber,
    this.transactionId,
    this.receiptNumber,
    this.location,
    this.addressAtCollection,
    final List<String> photoUrls = const [],
    this.signatureUrl,
    this.notes,
    this.isVerified,
    this.verifiedAt,
    this.verifiedBy,
    this.disputeReason,
  }) : _photoUrls = photoUrls;

  factory _$CollectionRecordImpl.fromJson(Map<String, dynamic> json) =>
      _$$CollectionRecordImplFromJson(json);

  @override
  final String customerId;
  @override
  final String customerName;
  @override
  final String bookingId;
  @override
  final DateTime collectionTime;
  @override
  final double amount;
  @override
  final PaymentMode mode;
  // Details
  @override
  final int? emiNumber;
  @override
  final double? lateFee;
  @override
  final String? chequeNumber;
  @override
  final String? transactionId;
  @override
  final String? receiptNumber;
  // Location
  @override
  final GeoLocation? location;
  @override
  final String? addressAtCollection;
  // Proof
  final List<String> _photoUrls;
  // Proof
  @override
  @JsonKey()
  List<String> get photoUrls {
    if (_photoUrls is EqualUnmodifiableListView) return _photoUrls;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_photoUrls);
  }

  // Payment proof photos
  @override
  final String? signatureUrl;
  @override
  final String? notes;
  // Verification
  @override
  final bool? isVerified;
  @override
  final DateTime? verifiedAt;
  @override
  final String? verifiedBy;
  @override
  final String? disputeReason;

  @override
  String toString() {
    return 'CollectionRecord(customerId: $customerId, customerName: $customerName, bookingId: $bookingId, collectionTime: $collectionTime, amount: $amount, mode: $mode, emiNumber: $emiNumber, lateFee: $lateFee, chequeNumber: $chequeNumber, transactionId: $transactionId, receiptNumber: $receiptNumber, location: $location, addressAtCollection: $addressAtCollection, photoUrls: $photoUrls, signatureUrl: $signatureUrl, notes: $notes, isVerified: $isVerified, verifiedAt: $verifiedAt, verifiedBy: $verifiedBy, disputeReason: $disputeReason)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$CollectionRecordImpl &&
            (identical(other.customerId, customerId) ||
                other.customerId == customerId) &&
            (identical(other.customerName, customerName) ||
                other.customerName == customerName) &&
            (identical(other.bookingId, bookingId) ||
                other.bookingId == bookingId) &&
            (identical(other.collectionTime, collectionTime) ||
                other.collectionTime == collectionTime) &&
            (identical(other.amount, amount) || other.amount == amount) &&
            (identical(other.mode, mode) || other.mode == mode) &&
            (identical(other.emiNumber, emiNumber) ||
                other.emiNumber == emiNumber) &&
            (identical(other.lateFee, lateFee) || other.lateFee == lateFee) &&
            (identical(other.chequeNumber, chequeNumber) ||
                other.chequeNumber == chequeNumber) &&
            (identical(other.transactionId, transactionId) ||
                other.transactionId == transactionId) &&
            (identical(other.receiptNumber, receiptNumber) ||
                other.receiptNumber == receiptNumber) &&
            (identical(other.location, location) ||
                other.location == location) &&
            (identical(other.addressAtCollection, addressAtCollection) ||
                other.addressAtCollection == addressAtCollection) &&
            const DeepCollectionEquality().equals(
              other._photoUrls,
              _photoUrls,
            ) &&
            (identical(other.signatureUrl, signatureUrl) ||
                other.signatureUrl == signatureUrl) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            (identical(other.isVerified, isVerified) ||
                other.isVerified == isVerified) &&
            (identical(other.verifiedAt, verifiedAt) ||
                other.verifiedAt == verifiedAt) &&
            (identical(other.verifiedBy, verifiedBy) ||
                other.verifiedBy == verifiedBy) &&
            (identical(other.disputeReason, disputeReason) ||
                other.disputeReason == disputeReason));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    customerId,
    customerName,
    bookingId,
    collectionTime,
    amount,
    mode,
    emiNumber,
    lateFee,
    chequeNumber,
    transactionId,
    receiptNumber,
    location,
    addressAtCollection,
    const DeepCollectionEquality().hash(_photoUrls),
    signatureUrl,
    notes,
    isVerified,
    verifiedAt,
    verifiedBy,
    disputeReason,
  ]);

  /// Create a copy of CollectionRecord
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$CollectionRecordImplCopyWith<_$CollectionRecordImpl> get copyWith =>
      __$$CollectionRecordImplCopyWithImpl<_$CollectionRecordImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$CollectionRecordImplToJson(this);
  }
}

abstract class _CollectionRecord implements CollectionRecord {
  const factory _CollectionRecord({
    required final String customerId,
    required final String customerName,
    required final String bookingId,
    required final DateTime collectionTime,
    required final double amount,
    required final PaymentMode mode,
    final int? emiNumber,
    final double? lateFee,
    final String? chequeNumber,
    final String? transactionId,
    final String? receiptNumber,
    final GeoLocation? location,
    final String? addressAtCollection,
    final List<String> photoUrls,
    final String? signatureUrl,
    final String? notes,
    final bool? isVerified,
    final DateTime? verifiedAt,
    final String? verifiedBy,
    final String? disputeReason,
  }) = _$CollectionRecordImpl;

  factory _CollectionRecord.fromJson(Map<String, dynamic> json) =
      _$CollectionRecordImpl.fromJson;

  @override
  String get customerId;
  @override
  String get customerName;
  @override
  String get bookingId;
  @override
  DateTime get collectionTime;
  @override
  double get amount;
  @override
  PaymentMode get mode; // Details
  @override
  int? get emiNumber;
  @override
  double? get lateFee;
  @override
  String? get chequeNumber;
  @override
  String? get transactionId;
  @override
  String? get receiptNumber; // Location
  @override
  GeoLocation? get location;
  @override
  String? get addressAtCollection; // Proof
  @override
  List<String> get photoUrls; // Payment proof photos
  @override
  String? get signatureUrl;
  @override
  String? get notes; // Verification
  @override
  bool? get isVerified;
  @override
  DateTime? get verifiedAt;
  @override
  String? get verifiedBy;
  @override
  String? get disputeReason;

  /// Create a copy of CollectionRecord
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$CollectionRecordImplCopyWith<_$CollectionRecordImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

MonthlyCollectionPerformance _$MonthlyCollectionPerformanceFromJson(
  Map<String, dynamic> json,
) {
  return _MonthlyCollectionPerformance.fromJson(json);
}

/// @nodoc
mixin _$MonthlyCollectionPerformance {
  String get id => throw _privateConstructorUsedError;
  int get year => throw _privateConstructorUsedError;
  int get month => throw _privateConstructorUsedError;
  String get agentId => throw _privateConstructorUsedError; // Collections
  int get totalCollections => throw _privateConstructorUsedError;
  double get totalAmount => throw _privateConstructorUsedError;
  int get totalCustomers => throw _privateConstructorUsedError;
  int get newCustomersAdded =>
      throw _privateConstructorUsedError; // Performance Metrics
  double get collectionRate =>
      throw _privateConstructorUsedError; // % of target
  double get successRate =>
      throw _privateConstructorUsedError; // % of visits successful
  int get ranking => throw _privateConstructorUsedError; // Among all agents
  // Financial
  double get baseSalary => throw _privateConstructorUsedError;
  double get commissionEarned => throw _privateConstructorUsedError;
  double get incentives => throw _privateConstructorUsedError;
  double get deductions => throw _privateConstructorUsedError;
  double get totalEarnings => throw _privateConstructorUsedError; // Quality
  double get customerSatisfaction =>
      throw _privateConstructorUsedError; // 0-100
  int get complaints => throw _privateConstructorUsedError;
  int get commendations => throw _privateConstructorUsedError; // Daily average
  double get avgCollectionsPerDay => throw _privateConstructorUsedError;
  double get avgAmountPerDay => throw _privateConstructorUsedError;
  double get avgDistancePerDay => throw _privateConstructorUsedError; // Target
  double get targetAmount => throw _privateConstructorUsedError;
  double get targetAchievement => throw _privateConstructorUsedError;
  PaymentStatus get paymentStatus => throw _privateConstructorUsedError;
  DateTime? get paidAt => throw _privateConstructorUsedError;

  /// Serializes this MonthlyCollectionPerformance to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of MonthlyCollectionPerformance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $MonthlyCollectionPerformanceCopyWith<MonthlyCollectionPerformance>
  get copyWith => throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $MonthlyCollectionPerformanceCopyWith<$Res> {
  factory $MonthlyCollectionPerformanceCopyWith(
    MonthlyCollectionPerformance value,
    $Res Function(MonthlyCollectionPerformance) then,
  ) =
      _$MonthlyCollectionPerformanceCopyWithImpl<
        $Res,
        MonthlyCollectionPerformance
      >;
  @useResult
  $Res call({
    String id,
    int year,
    int month,
    String agentId,
    int totalCollections,
    double totalAmount,
    int totalCustomers,
    int newCustomersAdded,
    double collectionRate,
    double successRate,
    int ranking,
    double baseSalary,
    double commissionEarned,
    double incentives,
    double deductions,
    double totalEarnings,
    double customerSatisfaction,
    int complaints,
    int commendations,
    double avgCollectionsPerDay,
    double avgAmountPerDay,
    double avgDistancePerDay,
    double targetAmount,
    double targetAchievement,
    PaymentStatus paymentStatus,
    DateTime? paidAt,
  });
}

/// @nodoc
class _$MonthlyCollectionPerformanceCopyWithImpl<
  $Res,
  $Val extends MonthlyCollectionPerformance
>
    implements $MonthlyCollectionPerformanceCopyWith<$Res> {
  _$MonthlyCollectionPerformanceCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of MonthlyCollectionPerformance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? year = null,
    Object? month = null,
    Object? agentId = null,
    Object? totalCollections = null,
    Object? totalAmount = null,
    Object? totalCustomers = null,
    Object? newCustomersAdded = null,
    Object? collectionRate = null,
    Object? successRate = null,
    Object? ranking = null,
    Object? baseSalary = null,
    Object? commissionEarned = null,
    Object? incentives = null,
    Object? deductions = null,
    Object? totalEarnings = null,
    Object? customerSatisfaction = null,
    Object? complaints = null,
    Object? commendations = null,
    Object? avgCollectionsPerDay = null,
    Object? avgAmountPerDay = null,
    Object? avgDistancePerDay = null,
    Object? targetAmount = null,
    Object? targetAchievement = null,
    Object? paymentStatus = null,
    Object? paidAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            year: null == year
                ? _value.year
                : year // ignore: cast_nullable_to_non_nullable
                      as int,
            month: null == month
                ? _value.month
                : month // ignore: cast_nullable_to_non_nullable
                      as int,
            agentId: null == agentId
                ? _value.agentId
                : agentId // ignore: cast_nullable_to_non_nullable
                      as String,
            totalCollections: null == totalCollections
                ? _value.totalCollections
                : totalCollections // ignore: cast_nullable_to_non_nullable
                      as int,
            totalAmount: null == totalAmount
                ? _value.totalAmount
                : totalAmount // ignore: cast_nullable_to_non_nullable
                      as double,
            totalCustomers: null == totalCustomers
                ? _value.totalCustomers
                : totalCustomers // ignore: cast_nullable_to_non_nullable
                      as int,
            newCustomersAdded: null == newCustomersAdded
                ? _value.newCustomersAdded
                : newCustomersAdded // ignore: cast_nullable_to_non_nullable
                      as int,
            collectionRate: null == collectionRate
                ? _value.collectionRate
                : collectionRate // ignore: cast_nullable_to_non_nullable
                      as double,
            successRate: null == successRate
                ? _value.successRate
                : successRate // ignore: cast_nullable_to_non_nullable
                      as double,
            ranking: null == ranking
                ? _value.ranking
                : ranking // ignore: cast_nullable_to_non_nullable
                      as int,
            baseSalary: null == baseSalary
                ? _value.baseSalary
                : baseSalary // ignore: cast_nullable_to_non_nullable
                      as double,
            commissionEarned: null == commissionEarned
                ? _value.commissionEarned
                : commissionEarned // ignore: cast_nullable_to_non_nullable
                      as double,
            incentives: null == incentives
                ? _value.incentives
                : incentives // ignore: cast_nullable_to_non_nullable
                      as double,
            deductions: null == deductions
                ? _value.deductions
                : deductions // ignore: cast_nullable_to_non_nullable
                      as double,
            totalEarnings: null == totalEarnings
                ? _value.totalEarnings
                : totalEarnings // ignore: cast_nullable_to_non_nullable
                      as double,
            customerSatisfaction: null == customerSatisfaction
                ? _value.customerSatisfaction
                : customerSatisfaction // ignore: cast_nullable_to_non_nullable
                      as double,
            complaints: null == complaints
                ? _value.complaints
                : complaints // ignore: cast_nullable_to_non_nullable
                      as int,
            commendations: null == commendations
                ? _value.commendations
                : commendations // ignore: cast_nullable_to_non_nullable
                      as int,
            avgCollectionsPerDay: null == avgCollectionsPerDay
                ? _value.avgCollectionsPerDay
                : avgCollectionsPerDay // ignore: cast_nullable_to_non_nullable
                      as double,
            avgAmountPerDay: null == avgAmountPerDay
                ? _value.avgAmountPerDay
                : avgAmountPerDay // ignore: cast_nullable_to_non_nullable
                      as double,
            avgDistancePerDay: null == avgDistancePerDay
                ? _value.avgDistancePerDay
                : avgDistancePerDay // ignore: cast_nullable_to_non_nullable
                      as double,
            targetAmount: null == targetAmount
                ? _value.targetAmount
                : targetAmount // ignore: cast_nullable_to_non_nullable
                      as double,
            targetAchievement: null == targetAchievement
                ? _value.targetAchievement
                : targetAchievement // ignore: cast_nullable_to_non_nullable
                      as double,
            paymentStatus: null == paymentStatus
                ? _value.paymentStatus
                : paymentStatus // ignore: cast_nullable_to_non_nullable
                      as PaymentStatus,
            paidAt: freezed == paidAt
                ? _value.paidAt
                : paidAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$MonthlyCollectionPerformanceImplCopyWith<$Res>
    implements $MonthlyCollectionPerformanceCopyWith<$Res> {
  factory _$$MonthlyCollectionPerformanceImplCopyWith(
    _$MonthlyCollectionPerformanceImpl value,
    $Res Function(_$MonthlyCollectionPerformanceImpl) then,
  ) = __$$MonthlyCollectionPerformanceImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    int year,
    int month,
    String agentId,
    int totalCollections,
    double totalAmount,
    int totalCustomers,
    int newCustomersAdded,
    double collectionRate,
    double successRate,
    int ranking,
    double baseSalary,
    double commissionEarned,
    double incentives,
    double deductions,
    double totalEarnings,
    double customerSatisfaction,
    int complaints,
    int commendations,
    double avgCollectionsPerDay,
    double avgAmountPerDay,
    double avgDistancePerDay,
    double targetAmount,
    double targetAchievement,
    PaymentStatus paymentStatus,
    DateTime? paidAt,
  });
}

/// @nodoc
class __$$MonthlyCollectionPerformanceImplCopyWithImpl<$Res>
    extends
        _$MonthlyCollectionPerformanceCopyWithImpl<
          $Res,
          _$MonthlyCollectionPerformanceImpl
        >
    implements _$$MonthlyCollectionPerformanceImplCopyWith<$Res> {
  __$$MonthlyCollectionPerformanceImplCopyWithImpl(
    _$MonthlyCollectionPerformanceImpl _value,
    $Res Function(_$MonthlyCollectionPerformanceImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of MonthlyCollectionPerformance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? year = null,
    Object? month = null,
    Object? agentId = null,
    Object? totalCollections = null,
    Object? totalAmount = null,
    Object? totalCustomers = null,
    Object? newCustomersAdded = null,
    Object? collectionRate = null,
    Object? successRate = null,
    Object? ranking = null,
    Object? baseSalary = null,
    Object? commissionEarned = null,
    Object? incentives = null,
    Object? deductions = null,
    Object? totalEarnings = null,
    Object? customerSatisfaction = null,
    Object? complaints = null,
    Object? commendations = null,
    Object? avgCollectionsPerDay = null,
    Object? avgAmountPerDay = null,
    Object? avgDistancePerDay = null,
    Object? targetAmount = null,
    Object? targetAchievement = null,
    Object? paymentStatus = null,
    Object? paidAt = freezed,
  }) {
    return _then(
      _$MonthlyCollectionPerformanceImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        year: null == year
            ? _value.year
            : year // ignore: cast_nullable_to_non_nullable
                  as int,
        month: null == month
            ? _value.month
            : month // ignore: cast_nullable_to_non_nullable
                  as int,
        agentId: null == agentId
            ? _value.agentId
            : agentId // ignore: cast_nullable_to_non_nullable
                  as String,
        totalCollections: null == totalCollections
            ? _value.totalCollections
            : totalCollections // ignore: cast_nullable_to_non_nullable
                  as int,
        totalAmount: null == totalAmount
            ? _value.totalAmount
            : totalAmount // ignore: cast_nullable_to_non_nullable
                  as double,
        totalCustomers: null == totalCustomers
            ? _value.totalCustomers
            : totalCustomers // ignore: cast_nullable_to_non_nullable
                  as int,
        newCustomersAdded: null == newCustomersAdded
            ? _value.newCustomersAdded
            : newCustomersAdded // ignore: cast_nullable_to_non_nullable
                  as int,
        collectionRate: null == collectionRate
            ? _value.collectionRate
            : collectionRate // ignore: cast_nullable_to_non_nullable
                  as double,
        successRate: null == successRate
            ? _value.successRate
            : successRate // ignore: cast_nullable_to_non_nullable
                  as double,
        ranking: null == ranking
            ? _value.ranking
            : ranking // ignore: cast_nullable_to_non_nullable
                  as int,
        baseSalary: null == baseSalary
            ? _value.baseSalary
            : baseSalary // ignore: cast_nullable_to_non_nullable
                  as double,
        commissionEarned: null == commissionEarned
            ? _value.commissionEarned
            : commissionEarned // ignore: cast_nullable_to_non_nullable
                  as double,
        incentives: null == incentives
            ? _value.incentives
            : incentives // ignore: cast_nullable_to_non_nullable
                  as double,
        deductions: null == deductions
            ? _value.deductions
            : deductions // ignore: cast_nullable_to_non_nullable
                  as double,
        totalEarnings: null == totalEarnings
            ? _value.totalEarnings
            : totalEarnings // ignore: cast_nullable_to_non_nullable
                  as double,
        customerSatisfaction: null == customerSatisfaction
            ? _value.customerSatisfaction
            : customerSatisfaction // ignore: cast_nullable_to_non_nullable
                  as double,
        complaints: null == complaints
            ? _value.complaints
            : complaints // ignore: cast_nullable_to_non_nullable
                  as int,
        commendations: null == commendations
            ? _value.commendations
            : commendations // ignore: cast_nullable_to_non_nullable
                  as int,
        avgCollectionsPerDay: null == avgCollectionsPerDay
            ? _value.avgCollectionsPerDay
            : avgCollectionsPerDay // ignore: cast_nullable_to_non_nullable
                  as double,
        avgAmountPerDay: null == avgAmountPerDay
            ? _value.avgAmountPerDay
            : avgAmountPerDay // ignore: cast_nullable_to_non_nullable
                  as double,
        avgDistancePerDay: null == avgDistancePerDay
            ? _value.avgDistancePerDay
            : avgDistancePerDay // ignore: cast_nullable_to_non_nullable
                  as double,
        targetAmount: null == targetAmount
            ? _value.targetAmount
            : targetAmount // ignore: cast_nullable_to_non_nullable
                  as double,
        targetAchievement: null == targetAchievement
            ? _value.targetAchievement
            : targetAchievement // ignore: cast_nullable_to_non_nullable
                  as double,
        paymentStatus: null == paymentStatus
            ? _value.paymentStatus
            : paymentStatus // ignore: cast_nullable_to_non_nullable
                  as PaymentStatus,
        paidAt: freezed == paidAt
            ? _value.paidAt
            : paidAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$MonthlyCollectionPerformanceImpl
    implements _MonthlyCollectionPerformance {
  const _$MonthlyCollectionPerformanceImpl({
    required this.id,
    required this.year,
    required this.month,
    required this.agentId,
    this.totalCollections = 0,
    this.totalAmount = 0,
    this.totalCustomers = 0,
    this.newCustomersAdded = 0,
    this.collectionRate = 0,
    this.successRate = 0,
    this.ranking = 0,
    this.baseSalary = 0,
    this.commissionEarned = 0,
    this.incentives = 0,
    this.deductions = 0,
    this.totalEarnings = 0,
    this.customerSatisfaction = 0,
    this.complaints = 0,
    this.commendations = 0,
    this.avgCollectionsPerDay = 0,
    this.avgAmountPerDay = 0,
    this.avgDistancePerDay = 0,
    this.targetAmount = 0,
    this.targetAchievement = 0,
    required this.paymentStatus,
    this.paidAt,
  });

  factory _$MonthlyCollectionPerformanceImpl.fromJson(
    Map<String, dynamic> json,
  ) => _$$MonthlyCollectionPerformanceImplFromJson(json);

  @override
  final String id;
  @override
  final int year;
  @override
  final int month;
  @override
  final String agentId;
  // Collections
  @override
  @JsonKey()
  final int totalCollections;
  @override
  @JsonKey()
  final double totalAmount;
  @override
  @JsonKey()
  final int totalCustomers;
  @override
  @JsonKey()
  final int newCustomersAdded;
  // Performance Metrics
  @override
  @JsonKey()
  final double collectionRate;
  // % of target
  @override
  @JsonKey()
  final double successRate;
  // % of visits successful
  @override
  @JsonKey()
  final int ranking;
  // Among all agents
  // Financial
  @override
  @JsonKey()
  final double baseSalary;
  @override
  @JsonKey()
  final double commissionEarned;
  @override
  @JsonKey()
  final double incentives;
  @override
  @JsonKey()
  final double deductions;
  @override
  @JsonKey()
  final double totalEarnings;
  // Quality
  @override
  @JsonKey()
  final double customerSatisfaction;
  // 0-100
  @override
  @JsonKey()
  final int complaints;
  @override
  @JsonKey()
  final int commendations;
  // Daily average
  @override
  @JsonKey()
  final double avgCollectionsPerDay;
  @override
  @JsonKey()
  final double avgAmountPerDay;
  @override
  @JsonKey()
  final double avgDistancePerDay;
  // Target
  @override
  @JsonKey()
  final double targetAmount;
  @override
  @JsonKey()
  final double targetAchievement;
  @override
  final PaymentStatus paymentStatus;
  @override
  final DateTime? paidAt;

  @override
  String toString() {
    return 'MonthlyCollectionPerformance(id: $id, year: $year, month: $month, agentId: $agentId, totalCollections: $totalCollections, totalAmount: $totalAmount, totalCustomers: $totalCustomers, newCustomersAdded: $newCustomersAdded, collectionRate: $collectionRate, successRate: $successRate, ranking: $ranking, baseSalary: $baseSalary, commissionEarned: $commissionEarned, incentives: $incentives, deductions: $deductions, totalEarnings: $totalEarnings, customerSatisfaction: $customerSatisfaction, complaints: $complaints, commendations: $commendations, avgCollectionsPerDay: $avgCollectionsPerDay, avgAmountPerDay: $avgAmountPerDay, avgDistancePerDay: $avgDistancePerDay, targetAmount: $targetAmount, targetAchievement: $targetAchievement, paymentStatus: $paymentStatus, paidAt: $paidAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$MonthlyCollectionPerformanceImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.year, year) || other.year == year) &&
            (identical(other.month, month) || other.month == month) &&
            (identical(other.agentId, agentId) || other.agentId == agentId) &&
            (identical(other.totalCollections, totalCollections) ||
                other.totalCollections == totalCollections) &&
            (identical(other.totalAmount, totalAmount) ||
                other.totalAmount == totalAmount) &&
            (identical(other.totalCustomers, totalCustomers) ||
                other.totalCustomers == totalCustomers) &&
            (identical(other.newCustomersAdded, newCustomersAdded) ||
                other.newCustomersAdded == newCustomersAdded) &&
            (identical(other.collectionRate, collectionRate) ||
                other.collectionRate == collectionRate) &&
            (identical(other.successRate, successRate) ||
                other.successRate == successRate) &&
            (identical(other.ranking, ranking) || other.ranking == ranking) &&
            (identical(other.baseSalary, baseSalary) ||
                other.baseSalary == baseSalary) &&
            (identical(other.commissionEarned, commissionEarned) ||
                other.commissionEarned == commissionEarned) &&
            (identical(other.incentives, incentives) ||
                other.incentives == incentives) &&
            (identical(other.deductions, deductions) ||
                other.deductions == deductions) &&
            (identical(other.totalEarnings, totalEarnings) ||
                other.totalEarnings == totalEarnings) &&
            (identical(other.customerSatisfaction, customerSatisfaction) ||
                other.customerSatisfaction == customerSatisfaction) &&
            (identical(other.complaints, complaints) ||
                other.complaints == complaints) &&
            (identical(other.commendations, commendations) ||
                other.commendations == commendations) &&
            (identical(other.avgCollectionsPerDay, avgCollectionsPerDay) ||
                other.avgCollectionsPerDay == avgCollectionsPerDay) &&
            (identical(other.avgAmountPerDay, avgAmountPerDay) ||
                other.avgAmountPerDay == avgAmountPerDay) &&
            (identical(other.avgDistancePerDay, avgDistancePerDay) ||
                other.avgDistancePerDay == avgDistancePerDay) &&
            (identical(other.targetAmount, targetAmount) ||
                other.targetAmount == targetAmount) &&
            (identical(other.targetAchievement, targetAchievement) ||
                other.targetAchievement == targetAchievement) &&
            (identical(other.paymentStatus, paymentStatus) ||
                other.paymentStatus == paymentStatus) &&
            (identical(other.paidAt, paidAt) || other.paidAt == paidAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    id,
    year,
    month,
    agentId,
    totalCollections,
    totalAmount,
    totalCustomers,
    newCustomersAdded,
    collectionRate,
    successRate,
    ranking,
    baseSalary,
    commissionEarned,
    incentives,
    deductions,
    totalEarnings,
    customerSatisfaction,
    complaints,
    commendations,
    avgCollectionsPerDay,
    avgAmountPerDay,
    avgDistancePerDay,
    targetAmount,
    targetAchievement,
    paymentStatus,
    paidAt,
  ]);

  /// Create a copy of MonthlyCollectionPerformance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$MonthlyCollectionPerformanceImplCopyWith<
    _$MonthlyCollectionPerformanceImpl
  >
  get copyWith =>
      __$$MonthlyCollectionPerformanceImplCopyWithImpl<
        _$MonthlyCollectionPerformanceImpl
      >(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$MonthlyCollectionPerformanceImplToJson(this);
  }
}

abstract class _MonthlyCollectionPerformance
    implements MonthlyCollectionPerformance {
  const factory _MonthlyCollectionPerformance({
    required final String id,
    required final int year,
    required final int month,
    required final String agentId,
    final int totalCollections,
    final double totalAmount,
    final int totalCustomers,
    final int newCustomersAdded,
    final double collectionRate,
    final double successRate,
    final int ranking,
    final double baseSalary,
    final double commissionEarned,
    final double incentives,
    final double deductions,
    final double totalEarnings,
    final double customerSatisfaction,
    final int complaints,
    final int commendations,
    final double avgCollectionsPerDay,
    final double avgAmountPerDay,
    final double avgDistancePerDay,
    final double targetAmount,
    final double targetAchievement,
    required final PaymentStatus paymentStatus,
    final DateTime? paidAt,
  }) = _$MonthlyCollectionPerformanceImpl;

  factory _MonthlyCollectionPerformance.fromJson(Map<String, dynamic> json) =
      _$MonthlyCollectionPerformanceImpl.fromJson;

  @override
  String get id;
  @override
  int get year;
  @override
  int get month;
  @override
  String get agentId; // Collections
  @override
  int get totalCollections;
  @override
  double get totalAmount;
  @override
  int get totalCustomers;
  @override
  int get newCustomersAdded; // Performance Metrics
  @override
  double get collectionRate; // % of target
  @override
  double get successRate; // % of visits successful
  @override
  int get ranking; // Among all agents
  // Financial
  @override
  double get baseSalary;
  @override
  double get commissionEarned;
  @override
  double get incentives;
  @override
  double get deductions;
  @override
  double get totalEarnings; // Quality
  @override
  double get customerSatisfaction; // 0-100
  @override
  int get complaints;
  @override
  int get commendations; // Daily average
  @override
  double get avgCollectionsPerDay;
  @override
  double get avgAmountPerDay;
  @override
  double get avgDistancePerDay; // Target
  @override
  double get targetAmount;
  @override
  double get targetAchievement;
  @override
  PaymentStatus get paymentStatus;
  @override
  DateTime? get paidAt;

  /// Create a copy of MonthlyCollectionPerformance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$MonthlyCollectionPerformanceImplCopyWith<
    _$MonthlyCollectionPerformanceImpl
  >
  get copyWith => throw _privateConstructorUsedError;
}

LocationTracking _$LocationTrackingFromJson(Map<String, dynamic> json) {
  return _LocationTracking.fromJson(json);
}

/// @nodoc
mixin _$LocationTracking {
  DateTime get timestamp => throw _privateConstructorUsedError;
  GeoLocation get location => throw _privateConstructorUsedError;
  String? get activity =>
      throw _privateConstructorUsedError; // traveling, visiting, collecting, break
  String? get customerId => throw _privateConstructorUsedError;

  /// Serializes this LocationTracking to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of LocationTracking
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $LocationTrackingCopyWith<LocationTracking> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $LocationTrackingCopyWith<$Res> {
  factory $LocationTrackingCopyWith(
    LocationTracking value,
    $Res Function(LocationTracking) then,
  ) = _$LocationTrackingCopyWithImpl<$Res, LocationTracking>;
  @useResult
  $Res call({
    DateTime timestamp,
    GeoLocation location,
    String? activity,
    String? customerId,
  });
}

/// @nodoc
class _$LocationTrackingCopyWithImpl<$Res, $Val extends LocationTracking>
    implements $LocationTrackingCopyWith<$Res> {
  _$LocationTrackingCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of LocationTracking
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? timestamp = null,
    Object? location = null,
    Object? activity = freezed,
    Object? customerId = freezed,
  }) {
    return _then(
      _value.copyWith(
            timestamp: null == timestamp
                ? _value.timestamp
                : timestamp // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            location: null == location
                ? _value.location
                : location // ignore: cast_nullable_to_non_nullable
                      as GeoLocation,
            activity: freezed == activity
                ? _value.activity
                : activity // ignore: cast_nullable_to_non_nullable
                      as String?,
            customerId: freezed == customerId
                ? _value.customerId
                : customerId // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$LocationTrackingImplCopyWith<$Res>
    implements $LocationTrackingCopyWith<$Res> {
  factory _$$LocationTrackingImplCopyWith(
    _$LocationTrackingImpl value,
    $Res Function(_$LocationTrackingImpl) then,
  ) = __$$LocationTrackingImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    DateTime timestamp,
    GeoLocation location,
    String? activity,
    String? customerId,
  });
}

/// @nodoc
class __$$LocationTrackingImplCopyWithImpl<$Res>
    extends _$LocationTrackingCopyWithImpl<$Res, _$LocationTrackingImpl>
    implements _$$LocationTrackingImplCopyWith<$Res> {
  __$$LocationTrackingImplCopyWithImpl(
    _$LocationTrackingImpl _value,
    $Res Function(_$LocationTrackingImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of LocationTracking
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? timestamp = null,
    Object? location = null,
    Object? activity = freezed,
    Object? customerId = freezed,
  }) {
    return _then(
      _$LocationTrackingImpl(
        timestamp: null == timestamp
            ? _value.timestamp
            : timestamp // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        location: null == location
            ? _value.location
            : location // ignore: cast_nullable_to_non_nullable
                  as GeoLocation,
        activity: freezed == activity
            ? _value.activity
            : activity // ignore: cast_nullable_to_non_nullable
                  as String?,
        customerId: freezed == customerId
            ? _value.customerId
            : customerId // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$LocationTrackingImpl implements _LocationTracking {
  const _$LocationTrackingImpl({
    required this.timestamp,
    required this.location,
    this.activity,
    this.customerId,
  });

  factory _$LocationTrackingImpl.fromJson(Map<String, dynamic> json) =>
      _$$LocationTrackingImplFromJson(json);

  @override
  final DateTime timestamp;
  @override
  final GeoLocation location;
  @override
  final String? activity;
  // traveling, visiting, collecting, break
  @override
  final String? customerId;

  @override
  String toString() {
    return 'LocationTracking(timestamp: $timestamp, location: $location, activity: $activity, customerId: $customerId)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$LocationTrackingImpl &&
            (identical(other.timestamp, timestamp) ||
                other.timestamp == timestamp) &&
            (identical(other.location, location) ||
                other.location == location) &&
            (identical(other.activity, activity) ||
                other.activity == activity) &&
            (identical(other.customerId, customerId) ||
                other.customerId == customerId));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode =>
      Object.hash(runtimeType, timestamp, location, activity, customerId);

  /// Create a copy of LocationTracking
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$LocationTrackingImplCopyWith<_$LocationTrackingImpl> get copyWith =>
      __$$LocationTrackingImplCopyWithImpl<_$LocationTrackingImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$LocationTrackingImplToJson(this);
  }
}

abstract class _LocationTracking implements LocationTracking {
  const factory _LocationTracking({
    required final DateTime timestamp,
    required final GeoLocation location,
    final String? activity,
    final String? customerId,
  }) = _$LocationTrackingImpl;

  factory _LocationTracking.fromJson(Map<String, dynamic> json) =
      _$LocationTrackingImpl.fromJson;

  @override
  DateTime get timestamp;
  @override
  GeoLocation get location;
  @override
  String? get activity; // traveling, visiting, collecting, break
  @override
  String? get customerId;

  /// Create a copy of LocationTracking
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$LocationTrackingImplCopyWith<_$LocationTrackingImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

EMIDueList _$EMIDueListFromJson(Map<String, dynamic> json) {
  return _EMIDueList.fromJson(json);
}

/// @nodoc
mixin _$EMIDueList {
  String get id => throw _privateConstructorUsedError;
  DateTime get generatedAt => throw _privateConstructorUsedError;
  String get agentId => throw _privateConstructorUsedError;
  DateTime get forDate => throw _privateConstructorUsedError; // List of dues
  List<EMIDueItem> get dues => throw _privateConstructorUsedError; // Summary
  int get totalDues => throw _privateConstructorUsedError;
  double get totalAmount => throw _privateConstructorUsedError;
  int get highPriorityDues =>
      throw _privateConstructorUsedError; // Overdue by > 15 days
  int get mediumPriorityDues =>
      throw _privateConstructorUsedError; // Overdue by 7-15 days
  int get regularDues =>
      throw _privateConstructorUsedError; // Due today or future
  // Status
  bool get isCompleted => throw _privateConstructorUsedError;
  DateTime? get completedAt => throw _privateConstructorUsedError;
  int get collectionsMade => throw _privateConstructorUsedError;
  double get collectedAmount => throw _privateConstructorUsedError;

  /// Serializes this EMIDueList to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of EMIDueList
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $EMIDueListCopyWith<EMIDueList> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $EMIDueListCopyWith<$Res> {
  factory $EMIDueListCopyWith(
    EMIDueList value,
    $Res Function(EMIDueList) then,
  ) = _$EMIDueListCopyWithImpl<$Res, EMIDueList>;
  @useResult
  $Res call({
    String id,
    DateTime generatedAt,
    String agentId,
    DateTime forDate,
    List<EMIDueItem> dues,
    int totalDues,
    double totalAmount,
    int highPriorityDues,
    int mediumPriorityDues,
    int regularDues,
    bool isCompleted,
    DateTime? completedAt,
    int collectionsMade,
    double collectedAmount,
  });
}

/// @nodoc
class _$EMIDueListCopyWithImpl<$Res, $Val extends EMIDueList>
    implements $EMIDueListCopyWith<$Res> {
  _$EMIDueListCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of EMIDueList
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? generatedAt = null,
    Object? agentId = null,
    Object? forDate = null,
    Object? dues = null,
    Object? totalDues = null,
    Object? totalAmount = null,
    Object? highPriorityDues = null,
    Object? mediumPriorityDues = null,
    Object? regularDues = null,
    Object? isCompleted = null,
    Object? completedAt = freezed,
    Object? collectionsMade = null,
    Object? collectedAmount = null,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            generatedAt: null == generatedAt
                ? _value.generatedAt
                : generatedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            agentId: null == agentId
                ? _value.agentId
                : agentId // ignore: cast_nullable_to_non_nullable
                      as String,
            forDate: null == forDate
                ? _value.forDate
                : forDate // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            dues: null == dues
                ? _value.dues
                : dues // ignore: cast_nullable_to_non_nullable
                      as List<EMIDueItem>,
            totalDues: null == totalDues
                ? _value.totalDues
                : totalDues // ignore: cast_nullable_to_non_nullable
                      as int,
            totalAmount: null == totalAmount
                ? _value.totalAmount
                : totalAmount // ignore: cast_nullable_to_non_nullable
                      as double,
            highPriorityDues: null == highPriorityDues
                ? _value.highPriorityDues
                : highPriorityDues // ignore: cast_nullable_to_non_nullable
                      as int,
            mediumPriorityDues: null == mediumPriorityDues
                ? _value.mediumPriorityDues
                : mediumPriorityDues // ignore: cast_nullable_to_non_nullable
                      as int,
            regularDues: null == regularDues
                ? _value.regularDues
                : regularDues // ignore: cast_nullable_to_non_nullable
                      as int,
            isCompleted: null == isCompleted
                ? _value.isCompleted
                : isCompleted // ignore: cast_nullable_to_non_nullable
                      as bool,
            completedAt: freezed == completedAt
                ? _value.completedAt
                : completedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            collectionsMade: null == collectionsMade
                ? _value.collectionsMade
                : collectionsMade // ignore: cast_nullable_to_non_nullable
                      as int,
            collectedAmount: null == collectedAmount
                ? _value.collectedAmount
                : collectedAmount // ignore: cast_nullable_to_non_nullable
                      as double,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$EMIDueListImplCopyWith<$Res>
    implements $EMIDueListCopyWith<$Res> {
  factory _$$EMIDueListImplCopyWith(
    _$EMIDueListImpl value,
    $Res Function(_$EMIDueListImpl) then,
  ) = __$$EMIDueListImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    DateTime generatedAt,
    String agentId,
    DateTime forDate,
    List<EMIDueItem> dues,
    int totalDues,
    double totalAmount,
    int highPriorityDues,
    int mediumPriorityDues,
    int regularDues,
    bool isCompleted,
    DateTime? completedAt,
    int collectionsMade,
    double collectedAmount,
  });
}

/// @nodoc
class __$$EMIDueListImplCopyWithImpl<$Res>
    extends _$EMIDueListCopyWithImpl<$Res, _$EMIDueListImpl>
    implements _$$EMIDueListImplCopyWith<$Res> {
  __$$EMIDueListImplCopyWithImpl(
    _$EMIDueListImpl _value,
    $Res Function(_$EMIDueListImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of EMIDueList
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? generatedAt = null,
    Object? agentId = null,
    Object? forDate = null,
    Object? dues = null,
    Object? totalDues = null,
    Object? totalAmount = null,
    Object? highPriorityDues = null,
    Object? mediumPriorityDues = null,
    Object? regularDues = null,
    Object? isCompleted = null,
    Object? completedAt = freezed,
    Object? collectionsMade = null,
    Object? collectedAmount = null,
  }) {
    return _then(
      _$EMIDueListImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        generatedAt: null == generatedAt
            ? _value.generatedAt
            : generatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        agentId: null == agentId
            ? _value.agentId
            : agentId // ignore: cast_nullable_to_non_nullable
                  as String,
        forDate: null == forDate
            ? _value.forDate
            : forDate // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        dues: null == dues
            ? _value._dues
            : dues // ignore: cast_nullable_to_non_nullable
                  as List<EMIDueItem>,
        totalDues: null == totalDues
            ? _value.totalDues
            : totalDues // ignore: cast_nullable_to_non_nullable
                  as int,
        totalAmount: null == totalAmount
            ? _value.totalAmount
            : totalAmount // ignore: cast_nullable_to_non_nullable
                  as double,
        highPriorityDues: null == highPriorityDues
            ? _value.highPriorityDues
            : highPriorityDues // ignore: cast_nullable_to_non_nullable
                  as int,
        mediumPriorityDues: null == mediumPriorityDues
            ? _value.mediumPriorityDues
            : mediumPriorityDues // ignore: cast_nullable_to_non_nullable
                  as int,
        regularDues: null == regularDues
            ? _value.regularDues
            : regularDues // ignore: cast_nullable_to_non_nullable
                  as int,
        isCompleted: null == isCompleted
            ? _value.isCompleted
            : isCompleted // ignore: cast_nullable_to_non_nullable
                  as bool,
        completedAt: freezed == completedAt
            ? _value.completedAt
            : completedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        collectionsMade: null == collectionsMade
            ? _value.collectionsMade
            : collectionsMade // ignore: cast_nullable_to_non_nullable
                  as int,
        collectedAmount: null == collectedAmount
            ? _value.collectedAmount
            : collectedAmount // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$EMIDueListImpl implements _EMIDueList {
  const _$EMIDueListImpl({
    required this.id,
    required this.generatedAt,
    required this.agentId,
    required this.forDate,
    final List<EMIDueItem> dues = const [],
    this.totalDues = 0,
    this.totalAmount = 0,
    this.highPriorityDues = 0,
    this.mediumPriorityDues = 0,
    this.regularDues = 0,
    this.isCompleted = false,
    this.completedAt,
    this.collectionsMade = 0,
    this.collectedAmount = 0,
  }) : _dues = dues;

  factory _$EMIDueListImpl.fromJson(Map<String, dynamic> json) =>
      _$$EMIDueListImplFromJson(json);

  @override
  final String id;
  @override
  final DateTime generatedAt;
  @override
  final String agentId;
  @override
  final DateTime forDate;
  // List of dues
  final List<EMIDueItem> _dues;
  // List of dues
  @override
  @JsonKey()
  List<EMIDueItem> get dues {
    if (_dues is EqualUnmodifiableListView) return _dues;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_dues);
  }

  // Summary
  @override
  @JsonKey()
  final int totalDues;
  @override
  @JsonKey()
  final double totalAmount;
  @override
  @JsonKey()
  final int highPriorityDues;
  // Overdue by > 15 days
  @override
  @JsonKey()
  final int mediumPriorityDues;
  // Overdue by 7-15 days
  @override
  @JsonKey()
  final int regularDues;
  // Due today or future
  // Status
  @override
  @JsonKey()
  final bool isCompleted;
  @override
  final DateTime? completedAt;
  @override
  @JsonKey()
  final int collectionsMade;
  @override
  @JsonKey()
  final double collectedAmount;

  @override
  String toString() {
    return 'EMIDueList(id: $id, generatedAt: $generatedAt, agentId: $agentId, forDate: $forDate, dues: $dues, totalDues: $totalDues, totalAmount: $totalAmount, highPriorityDues: $highPriorityDues, mediumPriorityDues: $mediumPriorityDues, regularDues: $regularDues, isCompleted: $isCompleted, completedAt: $completedAt, collectionsMade: $collectionsMade, collectedAmount: $collectedAmount)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$EMIDueListImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.generatedAt, generatedAt) ||
                other.generatedAt == generatedAt) &&
            (identical(other.agentId, agentId) || other.agentId == agentId) &&
            (identical(other.forDate, forDate) || other.forDate == forDate) &&
            const DeepCollectionEquality().equals(other._dues, _dues) &&
            (identical(other.totalDues, totalDues) ||
                other.totalDues == totalDues) &&
            (identical(other.totalAmount, totalAmount) ||
                other.totalAmount == totalAmount) &&
            (identical(other.highPriorityDues, highPriorityDues) ||
                other.highPriorityDues == highPriorityDues) &&
            (identical(other.mediumPriorityDues, mediumPriorityDues) ||
                other.mediumPriorityDues == mediumPriorityDues) &&
            (identical(other.regularDues, regularDues) ||
                other.regularDues == regularDues) &&
            (identical(other.isCompleted, isCompleted) ||
                other.isCompleted == isCompleted) &&
            (identical(other.completedAt, completedAt) ||
                other.completedAt == completedAt) &&
            (identical(other.collectionsMade, collectionsMade) ||
                other.collectionsMade == collectionsMade) &&
            (identical(other.collectedAmount, collectedAmount) ||
                other.collectedAmount == collectedAmount));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    generatedAt,
    agentId,
    forDate,
    const DeepCollectionEquality().hash(_dues),
    totalDues,
    totalAmount,
    highPriorityDues,
    mediumPriorityDues,
    regularDues,
    isCompleted,
    completedAt,
    collectionsMade,
    collectedAmount,
  );

  /// Create a copy of EMIDueList
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$EMIDueListImplCopyWith<_$EMIDueListImpl> get copyWith =>
      __$$EMIDueListImplCopyWithImpl<_$EMIDueListImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$EMIDueListImplToJson(this);
  }
}

abstract class _EMIDueList implements EMIDueList {
  const factory _EMIDueList({
    required final String id,
    required final DateTime generatedAt,
    required final String agentId,
    required final DateTime forDate,
    final List<EMIDueItem> dues,
    final int totalDues,
    final double totalAmount,
    final int highPriorityDues,
    final int mediumPriorityDues,
    final int regularDues,
    final bool isCompleted,
    final DateTime? completedAt,
    final int collectionsMade,
    final double collectedAmount,
  }) = _$EMIDueListImpl;

  factory _EMIDueList.fromJson(Map<String, dynamic> json) =
      _$EMIDueListImpl.fromJson;

  @override
  String get id;
  @override
  DateTime get generatedAt;
  @override
  String get agentId;
  @override
  DateTime get forDate; // List of dues
  @override
  List<EMIDueItem> get dues; // Summary
  @override
  int get totalDues;
  @override
  double get totalAmount;
  @override
  int get highPriorityDues; // Overdue by > 15 days
  @override
  int get mediumPriorityDues; // Overdue by 7-15 days
  @override
  int get regularDues; // Due today or future
  // Status
  @override
  bool get isCompleted;
  @override
  DateTime? get completedAt;
  @override
  int get collectionsMade;
  @override
  double get collectedAmount;

  /// Create a copy of EMIDueList
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$EMIDueListImplCopyWith<_$EMIDueListImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

EMIDueItem _$EMIDueItemFromJson(Map<String, dynamic> json) {
  return _EMIDueItem.fromJson(json);
}

/// @nodoc
mixin _$EMIDueItem {
  String get customerId => throw _privateConstructorUsedError;
  String get customerName => throw _privateConstructorUsedError;
  String get phone => throw _privateConstructorUsedError;
  String get address => throw _privateConstructorUsedError;
  String get bookingId => throw _privateConstructorUsedError;
  String get plotNumber => throw _privateConstructorUsedError;
  String get colonyName => throw _privateConstructorUsedError; // Due Details
  double get emiAmount => throw _privateConstructorUsedError;
  DateTime get dueDate => throw _privateConstructorUsedError;
  int get daysOverdue => throw _privateConstructorUsedError; // Total Dues
  double get totalDue =>
      throw _privateConstructorUsedError; // Including late fees
  double get lateFee => throw _privateConstructorUsedError; // Status
  DuePriority get priority =>
      throw _privateConstructorUsedError; // High, Medium, Low
  String? get lastVisitNotes => throw _privateConstructorUsedError;
  DateTime? get lastVisitDate =>
      throw _privateConstructorUsedError; // Collection
  bool? get isCollected => throw _privateConstructorUsedError;
  double? get collectedAmount => throw _privateConstructorUsedError;
  DateTime? get collectedAt => throw _privateConstructorUsedError; // Location
  GeoLocation? get location => throw _privateConstructorUsedError;
  String? get landmark => throw _privateConstructorUsedError;
  String? get preferredTime => throw _privateConstructorUsedError;

  /// Serializes this EMIDueItem to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of EMIDueItem
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $EMIDueItemCopyWith<EMIDueItem> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $EMIDueItemCopyWith<$Res> {
  factory $EMIDueItemCopyWith(
    EMIDueItem value,
    $Res Function(EMIDueItem) then,
  ) = _$EMIDueItemCopyWithImpl<$Res, EMIDueItem>;
  @useResult
  $Res call({
    String customerId,
    String customerName,
    String phone,
    String address,
    String bookingId,
    String plotNumber,
    String colonyName,
    double emiAmount,
    DateTime dueDate,
    int daysOverdue,
    double totalDue,
    double lateFee,
    DuePriority priority,
    String? lastVisitNotes,
    DateTime? lastVisitDate,
    bool? isCollected,
    double? collectedAmount,
    DateTime? collectedAt,
    GeoLocation? location,
    String? landmark,
    String? preferredTime,
  });
}

/// @nodoc
class _$EMIDueItemCopyWithImpl<$Res, $Val extends EMIDueItem>
    implements $EMIDueItemCopyWith<$Res> {
  _$EMIDueItemCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of EMIDueItem
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? customerId = null,
    Object? customerName = null,
    Object? phone = null,
    Object? address = null,
    Object? bookingId = null,
    Object? plotNumber = null,
    Object? colonyName = null,
    Object? emiAmount = null,
    Object? dueDate = null,
    Object? daysOverdue = null,
    Object? totalDue = null,
    Object? lateFee = null,
    Object? priority = null,
    Object? lastVisitNotes = freezed,
    Object? lastVisitDate = freezed,
    Object? isCollected = freezed,
    Object? collectedAmount = freezed,
    Object? collectedAt = freezed,
    Object? location = freezed,
    Object? landmark = freezed,
    Object? preferredTime = freezed,
  }) {
    return _then(
      _value.copyWith(
            customerId: null == customerId
                ? _value.customerId
                : customerId // ignore: cast_nullable_to_non_nullable
                      as String,
            customerName: null == customerName
                ? _value.customerName
                : customerName // ignore: cast_nullable_to_non_nullable
                      as String,
            phone: null == phone
                ? _value.phone
                : phone // ignore: cast_nullable_to_non_nullable
                      as String,
            address: null == address
                ? _value.address
                : address // ignore: cast_nullable_to_non_nullable
                      as String,
            bookingId: null == bookingId
                ? _value.bookingId
                : bookingId // ignore: cast_nullable_to_non_nullable
                      as String,
            plotNumber: null == plotNumber
                ? _value.plotNumber
                : plotNumber // ignore: cast_nullable_to_non_nullable
                      as String,
            colonyName: null == colonyName
                ? _value.colonyName
                : colonyName // ignore: cast_nullable_to_non_nullable
                      as String,
            emiAmount: null == emiAmount
                ? _value.emiAmount
                : emiAmount // ignore: cast_nullable_to_non_nullable
                      as double,
            dueDate: null == dueDate
                ? _value.dueDate
                : dueDate // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            daysOverdue: null == daysOverdue
                ? _value.daysOverdue
                : daysOverdue // ignore: cast_nullable_to_non_nullable
                      as int,
            totalDue: null == totalDue
                ? _value.totalDue
                : totalDue // ignore: cast_nullable_to_non_nullable
                      as double,
            lateFee: null == lateFee
                ? _value.lateFee
                : lateFee // ignore: cast_nullable_to_non_nullable
                      as double,
            priority: null == priority
                ? _value.priority
                : priority // ignore: cast_nullable_to_non_nullable
                      as DuePriority,
            lastVisitNotes: freezed == lastVisitNotes
                ? _value.lastVisitNotes
                : lastVisitNotes // ignore: cast_nullable_to_non_nullable
                      as String?,
            lastVisitDate: freezed == lastVisitDate
                ? _value.lastVisitDate
                : lastVisitDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            isCollected: freezed == isCollected
                ? _value.isCollected
                : isCollected // ignore: cast_nullable_to_non_nullable
                      as bool?,
            collectedAmount: freezed == collectedAmount
                ? _value.collectedAmount
                : collectedAmount // ignore: cast_nullable_to_non_nullable
                      as double?,
            collectedAt: freezed == collectedAt
                ? _value.collectedAt
                : collectedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            location: freezed == location
                ? _value.location
                : location // ignore: cast_nullable_to_non_nullable
                      as GeoLocation?,
            landmark: freezed == landmark
                ? _value.landmark
                : landmark // ignore: cast_nullable_to_non_nullable
                      as String?,
            preferredTime: freezed == preferredTime
                ? _value.preferredTime
                : preferredTime // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$EMIDueItemImplCopyWith<$Res>
    implements $EMIDueItemCopyWith<$Res> {
  factory _$$EMIDueItemImplCopyWith(
    _$EMIDueItemImpl value,
    $Res Function(_$EMIDueItemImpl) then,
  ) = __$$EMIDueItemImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String customerId,
    String customerName,
    String phone,
    String address,
    String bookingId,
    String plotNumber,
    String colonyName,
    double emiAmount,
    DateTime dueDate,
    int daysOverdue,
    double totalDue,
    double lateFee,
    DuePriority priority,
    String? lastVisitNotes,
    DateTime? lastVisitDate,
    bool? isCollected,
    double? collectedAmount,
    DateTime? collectedAt,
    GeoLocation? location,
    String? landmark,
    String? preferredTime,
  });
}

/// @nodoc
class __$$EMIDueItemImplCopyWithImpl<$Res>
    extends _$EMIDueItemCopyWithImpl<$Res, _$EMIDueItemImpl>
    implements _$$EMIDueItemImplCopyWith<$Res> {
  __$$EMIDueItemImplCopyWithImpl(
    _$EMIDueItemImpl _value,
    $Res Function(_$EMIDueItemImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of EMIDueItem
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? customerId = null,
    Object? customerName = null,
    Object? phone = null,
    Object? address = null,
    Object? bookingId = null,
    Object? plotNumber = null,
    Object? colonyName = null,
    Object? emiAmount = null,
    Object? dueDate = null,
    Object? daysOverdue = null,
    Object? totalDue = null,
    Object? lateFee = null,
    Object? priority = null,
    Object? lastVisitNotes = freezed,
    Object? lastVisitDate = freezed,
    Object? isCollected = freezed,
    Object? collectedAmount = freezed,
    Object? collectedAt = freezed,
    Object? location = freezed,
    Object? landmark = freezed,
    Object? preferredTime = freezed,
  }) {
    return _then(
      _$EMIDueItemImpl(
        customerId: null == customerId
            ? _value.customerId
            : customerId // ignore: cast_nullable_to_non_nullable
                  as String,
        customerName: null == customerName
            ? _value.customerName
            : customerName // ignore: cast_nullable_to_non_nullable
                  as String,
        phone: null == phone
            ? _value.phone
            : phone // ignore: cast_nullable_to_non_nullable
                  as String,
        address: null == address
            ? _value.address
            : address // ignore: cast_nullable_to_non_nullable
                  as String,
        bookingId: null == bookingId
            ? _value.bookingId
            : bookingId // ignore: cast_nullable_to_non_nullable
                  as String,
        plotNumber: null == plotNumber
            ? _value.plotNumber
            : plotNumber // ignore: cast_nullable_to_non_nullable
                  as String,
        colonyName: null == colonyName
            ? _value.colonyName
            : colonyName // ignore: cast_nullable_to_non_nullable
                  as String,
        emiAmount: null == emiAmount
            ? _value.emiAmount
            : emiAmount // ignore: cast_nullable_to_non_nullable
                  as double,
        dueDate: null == dueDate
            ? _value.dueDate
            : dueDate // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        daysOverdue: null == daysOverdue
            ? _value.daysOverdue
            : daysOverdue // ignore: cast_nullable_to_non_nullable
                  as int,
        totalDue: null == totalDue
            ? _value.totalDue
            : totalDue // ignore: cast_nullable_to_non_nullable
                  as double,
        lateFee: null == lateFee
            ? _value.lateFee
            : lateFee // ignore: cast_nullable_to_non_nullable
                  as double,
        priority: null == priority
            ? _value.priority
            : priority // ignore: cast_nullable_to_non_nullable
                  as DuePriority,
        lastVisitNotes: freezed == lastVisitNotes
            ? _value.lastVisitNotes
            : lastVisitNotes // ignore: cast_nullable_to_non_nullable
                  as String?,
        lastVisitDate: freezed == lastVisitDate
            ? _value.lastVisitDate
            : lastVisitDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        isCollected: freezed == isCollected
            ? _value.isCollected
            : isCollected // ignore: cast_nullable_to_non_nullable
                  as bool?,
        collectedAmount: freezed == collectedAmount
            ? _value.collectedAmount
            : collectedAmount // ignore: cast_nullable_to_non_nullable
                  as double?,
        collectedAt: freezed == collectedAt
            ? _value.collectedAt
            : collectedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        location: freezed == location
            ? _value.location
            : location // ignore: cast_nullable_to_non_nullable
                  as GeoLocation?,
        landmark: freezed == landmark
            ? _value.landmark
            : landmark // ignore: cast_nullable_to_non_nullable
                  as String?,
        preferredTime: freezed == preferredTime
            ? _value.preferredTime
            : preferredTime // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$EMIDueItemImpl implements _EMIDueItem {
  const _$EMIDueItemImpl({
    required this.customerId,
    required this.customerName,
    required this.phone,
    required this.address,
    required this.bookingId,
    required this.plotNumber,
    required this.colonyName,
    required this.emiAmount,
    required this.dueDate,
    required this.daysOverdue,
    required this.totalDue,
    this.lateFee = 0,
    required this.priority,
    this.lastVisitNotes,
    this.lastVisitDate,
    this.isCollected,
    this.collectedAmount,
    this.collectedAt,
    this.location,
    this.landmark,
    this.preferredTime,
  });

  factory _$EMIDueItemImpl.fromJson(Map<String, dynamic> json) =>
      _$$EMIDueItemImplFromJson(json);

  @override
  final String customerId;
  @override
  final String customerName;
  @override
  final String phone;
  @override
  final String address;
  @override
  final String bookingId;
  @override
  final String plotNumber;
  @override
  final String colonyName;
  // Due Details
  @override
  final double emiAmount;
  @override
  final DateTime dueDate;
  @override
  final int daysOverdue;
  // Total Dues
  @override
  final double totalDue;
  // Including late fees
  @override
  @JsonKey()
  final double lateFee;
  // Status
  @override
  final DuePriority priority;
  // High, Medium, Low
  @override
  final String? lastVisitNotes;
  @override
  final DateTime? lastVisitDate;
  // Collection
  @override
  final bool? isCollected;
  @override
  final double? collectedAmount;
  @override
  final DateTime? collectedAt;
  // Location
  @override
  final GeoLocation? location;
  @override
  final String? landmark;
  @override
  final String? preferredTime;

  @override
  String toString() {
    return 'EMIDueItem(customerId: $customerId, customerName: $customerName, phone: $phone, address: $address, bookingId: $bookingId, plotNumber: $plotNumber, colonyName: $colonyName, emiAmount: $emiAmount, dueDate: $dueDate, daysOverdue: $daysOverdue, totalDue: $totalDue, lateFee: $lateFee, priority: $priority, lastVisitNotes: $lastVisitNotes, lastVisitDate: $lastVisitDate, isCollected: $isCollected, collectedAmount: $collectedAmount, collectedAt: $collectedAt, location: $location, landmark: $landmark, preferredTime: $preferredTime)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$EMIDueItemImpl &&
            (identical(other.customerId, customerId) ||
                other.customerId == customerId) &&
            (identical(other.customerName, customerName) ||
                other.customerName == customerName) &&
            (identical(other.phone, phone) || other.phone == phone) &&
            (identical(other.address, address) || other.address == address) &&
            (identical(other.bookingId, bookingId) ||
                other.bookingId == bookingId) &&
            (identical(other.plotNumber, plotNumber) ||
                other.plotNumber == plotNumber) &&
            (identical(other.colonyName, colonyName) ||
                other.colonyName == colonyName) &&
            (identical(other.emiAmount, emiAmount) ||
                other.emiAmount == emiAmount) &&
            (identical(other.dueDate, dueDate) || other.dueDate == dueDate) &&
            (identical(other.daysOverdue, daysOverdue) ||
                other.daysOverdue == daysOverdue) &&
            (identical(other.totalDue, totalDue) ||
                other.totalDue == totalDue) &&
            (identical(other.lateFee, lateFee) || other.lateFee == lateFee) &&
            (identical(other.priority, priority) ||
                other.priority == priority) &&
            (identical(other.lastVisitNotes, lastVisitNotes) ||
                other.lastVisitNotes == lastVisitNotes) &&
            (identical(other.lastVisitDate, lastVisitDate) ||
                other.lastVisitDate == lastVisitDate) &&
            (identical(other.isCollected, isCollected) ||
                other.isCollected == isCollected) &&
            (identical(other.collectedAmount, collectedAmount) ||
                other.collectedAmount == collectedAmount) &&
            (identical(other.collectedAt, collectedAt) ||
                other.collectedAt == collectedAt) &&
            (identical(other.location, location) ||
                other.location == location) &&
            (identical(other.landmark, landmark) ||
                other.landmark == landmark) &&
            (identical(other.preferredTime, preferredTime) ||
                other.preferredTime == preferredTime));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    customerId,
    customerName,
    phone,
    address,
    bookingId,
    plotNumber,
    colonyName,
    emiAmount,
    dueDate,
    daysOverdue,
    totalDue,
    lateFee,
    priority,
    lastVisitNotes,
    lastVisitDate,
    isCollected,
    collectedAmount,
    collectedAt,
    location,
    landmark,
    preferredTime,
  ]);

  /// Create a copy of EMIDueItem
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$EMIDueItemImplCopyWith<_$EMIDueItemImpl> get copyWith =>
      __$$EMIDueItemImplCopyWithImpl<_$EMIDueItemImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$EMIDueItemImplToJson(this);
  }
}

abstract class _EMIDueItem implements EMIDueItem {
  const factory _EMIDueItem({
    required final String customerId,
    required final String customerName,
    required final String phone,
    required final String address,
    required final String bookingId,
    required final String plotNumber,
    required final String colonyName,
    required final double emiAmount,
    required final DateTime dueDate,
    required final int daysOverdue,
    required final double totalDue,
    final double lateFee,
    required final DuePriority priority,
    final String? lastVisitNotes,
    final DateTime? lastVisitDate,
    final bool? isCollected,
    final double? collectedAmount,
    final DateTime? collectedAt,
    final GeoLocation? location,
    final String? landmark,
    final String? preferredTime,
  }) = _$EMIDueItemImpl;

  factory _EMIDueItem.fromJson(Map<String, dynamic> json) =
      _$EMIDueItemImpl.fromJson;

  @override
  String get customerId;
  @override
  String get customerName;
  @override
  String get phone;
  @override
  String get address;
  @override
  String get bookingId;
  @override
  String get plotNumber;
  @override
  String get colonyName; // Due Details
  @override
  double get emiAmount;
  @override
  DateTime get dueDate;
  @override
  int get daysOverdue; // Total Dues
  @override
  double get totalDue; // Including late fees
  @override
  double get lateFee; // Status
  @override
  DuePriority get priority; // High, Medium, Low
  @override
  String? get lastVisitNotes;
  @override
  DateTime? get lastVisitDate; // Collection
  @override
  bool? get isCollected;
  @override
  double? get collectedAmount;
  @override
  DateTime? get collectedAt; // Location
  @override
  GeoLocation? get location;
  @override
  String? get landmark;
  @override
  String? get preferredTime;

  /// Create a copy of EMIDueItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$EMIDueItemImplCopyWith<_$EMIDueItemImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

EMIReminder _$EMIReminderFromJson(Map<String, dynamic> json) {
  return _EMIReminder.fromJson(json);
}

/// @nodoc
mixin _$EMIReminder {
  String get id => throw _privateConstructorUsedError;
  String get customerId => throw _privateConstructorUsedError;
  String get bookingId => throw _privateConstructorUsedError;
  String get customerName => throw _privateConstructorUsedError;
  String get phone => throw _privateConstructorUsedError;
  double get emiAmount => throw _privateConstructorUsedError;
  DateTime get dueDate => throw _privateConstructorUsedError; // Reminder
  ReminderType get type =>
      throw _privateConstructorUsedError; // SMS, WhatsApp, Call, Email
  ReminderStatus get status =>
      throw _privateConstructorUsedError; // Scheduled, Sent, Delivered, Failed
  String? get messageContent => throw _privateConstructorUsedError;
  DateTime? get scheduledAt => throw _privateConstructorUsedError;
  DateTime? get sentAt => throw _privateConstructorUsedError;
  DateTime? get deliveredAt => throw _privateConstructorUsedError; // Response
  bool? get isResponded => throw _privateConstructorUsedError;
  DateTime? get respondedAt => throw _privateConstructorUsedError;
  String? get responseType =>
      throw _privateConstructorUsedError; // WillPay, NeedTime, CannotPay, Paid
  // Agent Assignment
  String? get assignedAgentId => throw _privateConstructorUsedError;
  DateTime? get agentAssignedAt => throw _privateConstructorUsedError;

  /// Serializes this EMIReminder to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of EMIReminder
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $EMIReminderCopyWith<EMIReminder> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $EMIReminderCopyWith<$Res> {
  factory $EMIReminderCopyWith(
    EMIReminder value,
    $Res Function(EMIReminder) then,
  ) = _$EMIReminderCopyWithImpl<$Res, EMIReminder>;
  @useResult
  $Res call({
    String id,
    String customerId,
    String bookingId,
    String customerName,
    String phone,
    double emiAmount,
    DateTime dueDate,
    ReminderType type,
    ReminderStatus status,
    String? messageContent,
    DateTime? scheduledAt,
    DateTime? sentAt,
    DateTime? deliveredAt,
    bool? isResponded,
    DateTime? respondedAt,
    String? responseType,
    String? assignedAgentId,
    DateTime? agentAssignedAt,
  });
}

/// @nodoc
class _$EMIReminderCopyWithImpl<$Res, $Val extends EMIReminder>
    implements $EMIReminderCopyWith<$Res> {
  _$EMIReminderCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of EMIReminder
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? customerId = null,
    Object? bookingId = null,
    Object? customerName = null,
    Object? phone = null,
    Object? emiAmount = null,
    Object? dueDate = null,
    Object? type = null,
    Object? status = null,
    Object? messageContent = freezed,
    Object? scheduledAt = freezed,
    Object? sentAt = freezed,
    Object? deliveredAt = freezed,
    Object? isResponded = freezed,
    Object? respondedAt = freezed,
    Object? responseType = freezed,
    Object? assignedAgentId = freezed,
    Object? agentAssignedAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            customerId: null == customerId
                ? _value.customerId
                : customerId // ignore: cast_nullable_to_non_nullable
                      as String,
            bookingId: null == bookingId
                ? _value.bookingId
                : bookingId // ignore: cast_nullable_to_non_nullable
                      as String,
            customerName: null == customerName
                ? _value.customerName
                : customerName // ignore: cast_nullable_to_non_nullable
                      as String,
            phone: null == phone
                ? _value.phone
                : phone // ignore: cast_nullable_to_non_nullable
                      as String,
            emiAmount: null == emiAmount
                ? _value.emiAmount
                : emiAmount // ignore: cast_nullable_to_non_nullable
                      as double,
            dueDate: null == dueDate
                ? _value.dueDate
                : dueDate // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            type: null == type
                ? _value.type
                : type // ignore: cast_nullable_to_non_nullable
                      as ReminderType,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as ReminderStatus,
            messageContent: freezed == messageContent
                ? _value.messageContent
                : messageContent // ignore: cast_nullable_to_non_nullable
                      as String?,
            scheduledAt: freezed == scheduledAt
                ? _value.scheduledAt
                : scheduledAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            sentAt: freezed == sentAt
                ? _value.sentAt
                : sentAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            deliveredAt: freezed == deliveredAt
                ? _value.deliveredAt
                : deliveredAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            isResponded: freezed == isResponded
                ? _value.isResponded
                : isResponded // ignore: cast_nullable_to_non_nullable
                      as bool?,
            respondedAt: freezed == respondedAt
                ? _value.respondedAt
                : respondedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            responseType: freezed == responseType
                ? _value.responseType
                : responseType // ignore: cast_nullable_to_non_nullable
                      as String?,
            assignedAgentId: freezed == assignedAgentId
                ? _value.assignedAgentId
                : assignedAgentId // ignore: cast_nullable_to_non_nullable
                      as String?,
            agentAssignedAt: freezed == agentAssignedAt
                ? _value.agentAssignedAt
                : agentAssignedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$EMIReminderImplCopyWith<$Res>
    implements $EMIReminderCopyWith<$Res> {
  factory _$$EMIReminderImplCopyWith(
    _$EMIReminderImpl value,
    $Res Function(_$EMIReminderImpl) then,
  ) = __$$EMIReminderImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String customerId,
    String bookingId,
    String customerName,
    String phone,
    double emiAmount,
    DateTime dueDate,
    ReminderType type,
    ReminderStatus status,
    String? messageContent,
    DateTime? scheduledAt,
    DateTime? sentAt,
    DateTime? deliveredAt,
    bool? isResponded,
    DateTime? respondedAt,
    String? responseType,
    String? assignedAgentId,
    DateTime? agentAssignedAt,
  });
}

/// @nodoc
class __$$EMIReminderImplCopyWithImpl<$Res>
    extends _$EMIReminderCopyWithImpl<$Res, _$EMIReminderImpl>
    implements _$$EMIReminderImplCopyWith<$Res> {
  __$$EMIReminderImplCopyWithImpl(
    _$EMIReminderImpl _value,
    $Res Function(_$EMIReminderImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of EMIReminder
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? customerId = null,
    Object? bookingId = null,
    Object? customerName = null,
    Object? phone = null,
    Object? emiAmount = null,
    Object? dueDate = null,
    Object? type = null,
    Object? status = null,
    Object? messageContent = freezed,
    Object? scheduledAt = freezed,
    Object? sentAt = freezed,
    Object? deliveredAt = freezed,
    Object? isResponded = freezed,
    Object? respondedAt = freezed,
    Object? responseType = freezed,
    Object? assignedAgentId = freezed,
    Object? agentAssignedAt = freezed,
  }) {
    return _then(
      _$EMIReminderImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        customerId: null == customerId
            ? _value.customerId
            : customerId // ignore: cast_nullable_to_non_nullable
                  as String,
        bookingId: null == bookingId
            ? _value.bookingId
            : bookingId // ignore: cast_nullable_to_non_nullable
                  as String,
        customerName: null == customerName
            ? _value.customerName
            : customerName // ignore: cast_nullable_to_non_nullable
                  as String,
        phone: null == phone
            ? _value.phone
            : phone // ignore: cast_nullable_to_non_nullable
                  as String,
        emiAmount: null == emiAmount
            ? _value.emiAmount
            : emiAmount // ignore: cast_nullable_to_non_nullable
                  as double,
        dueDate: null == dueDate
            ? _value.dueDate
            : dueDate // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        type: null == type
            ? _value.type
            : type // ignore: cast_nullable_to_non_nullable
                  as ReminderType,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as ReminderStatus,
        messageContent: freezed == messageContent
            ? _value.messageContent
            : messageContent // ignore: cast_nullable_to_non_nullable
                  as String?,
        scheduledAt: freezed == scheduledAt
            ? _value.scheduledAt
            : scheduledAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        sentAt: freezed == sentAt
            ? _value.sentAt
            : sentAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        deliveredAt: freezed == deliveredAt
            ? _value.deliveredAt
            : deliveredAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        isResponded: freezed == isResponded
            ? _value.isResponded
            : isResponded // ignore: cast_nullable_to_non_nullable
                  as bool?,
        respondedAt: freezed == respondedAt
            ? _value.respondedAt
            : respondedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        responseType: freezed == responseType
            ? _value.responseType
            : responseType // ignore: cast_nullable_to_non_nullable
                  as String?,
        assignedAgentId: freezed == assignedAgentId
            ? _value.assignedAgentId
            : assignedAgentId // ignore: cast_nullable_to_non_nullable
                  as String?,
        agentAssignedAt: freezed == agentAssignedAt
            ? _value.agentAssignedAt
            : agentAssignedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$EMIReminderImpl implements _EMIReminder {
  const _$EMIReminderImpl({
    required this.id,
    required this.customerId,
    required this.bookingId,
    required this.customerName,
    required this.phone,
    required this.emiAmount,
    required this.dueDate,
    required this.type,
    required this.status,
    this.messageContent,
    this.scheduledAt,
    this.sentAt,
    this.deliveredAt,
    this.isResponded,
    this.respondedAt,
    this.responseType,
    this.assignedAgentId,
    this.agentAssignedAt,
  });

  factory _$EMIReminderImpl.fromJson(Map<String, dynamic> json) =>
      _$$EMIReminderImplFromJson(json);

  @override
  final String id;
  @override
  final String customerId;
  @override
  final String bookingId;
  @override
  final String customerName;
  @override
  final String phone;
  @override
  final double emiAmount;
  @override
  final DateTime dueDate;
  // Reminder
  @override
  final ReminderType type;
  // SMS, WhatsApp, Call, Email
  @override
  final ReminderStatus status;
  // Scheduled, Sent, Delivered, Failed
  @override
  final String? messageContent;
  @override
  final DateTime? scheduledAt;
  @override
  final DateTime? sentAt;
  @override
  final DateTime? deliveredAt;
  // Response
  @override
  final bool? isResponded;
  @override
  final DateTime? respondedAt;
  @override
  final String? responseType;
  // WillPay, NeedTime, CannotPay, Paid
  // Agent Assignment
  @override
  final String? assignedAgentId;
  @override
  final DateTime? agentAssignedAt;

  @override
  String toString() {
    return 'EMIReminder(id: $id, customerId: $customerId, bookingId: $bookingId, customerName: $customerName, phone: $phone, emiAmount: $emiAmount, dueDate: $dueDate, type: $type, status: $status, messageContent: $messageContent, scheduledAt: $scheduledAt, sentAt: $sentAt, deliveredAt: $deliveredAt, isResponded: $isResponded, respondedAt: $respondedAt, responseType: $responseType, assignedAgentId: $assignedAgentId, agentAssignedAt: $agentAssignedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$EMIReminderImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.customerId, customerId) ||
                other.customerId == customerId) &&
            (identical(other.bookingId, bookingId) ||
                other.bookingId == bookingId) &&
            (identical(other.customerName, customerName) ||
                other.customerName == customerName) &&
            (identical(other.phone, phone) || other.phone == phone) &&
            (identical(other.emiAmount, emiAmount) ||
                other.emiAmount == emiAmount) &&
            (identical(other.dueDate, dueDate) || other.dueDate == dueDate) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.messageContent, messageContent) ||
                other.messageContent == messageContent) &&
            (identical(other.scheduledAt, scheduledAt) ||
                other.scheduledAt == scheduledAt) &&
            (identical(other.sentAt, sentAt) || other.sentAt == sentAt) &&
            (identical(other.deliveredAt, deliveredAt) ||
                other.deliveredAt == deliveredAt) &&
            (identical(other.isResponded, isResponded) ||
                other.isResponded == isResponded) &&
            (identical(other.respondedAt, respondedAt) ||
                other.respondedAt == respondedAt) &&
            (identical(other.responseType, responseType) ||
                other.responseType == responseType) &&
            (identical(other.assignedAgentId, assignedAgentId) ||
                other.assignedAgentId == assignedAgentId) &&
            (identical(other.agentAssignedAt, agentAssignedAt) ||
                other.agentAssignedAt == agentAssignedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    customerId,
    bookingId,
    customerName,
    phone,
    emiAmount,
    dueDate,
    type,
    status,
    messageContent,
    scheduledAt,
    sentAt,
    deliveredAt,
    isResponded,
    respondedAt,
    responseType,
    assignedAgentId,
    agentAssignedAt,
  );

  /// Create a copy of EMIReminder
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$EMIReminderImplCopyWith<_$EMIReminderImpl> get copyWith =>
      __$$EMIReminderImplCopyWithImpl<_$EMIReminderImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$EMIReminderImplToJson(this);
  }
}

abstract class _EMIReminder implements EMIReminder {
  const factory _EMIReminder({
    required final String id,
    required final String customerId,
    required final String bookingId,
    required final String customerName,
    required final String phone,
    required final double emiAmount,
    required final DateTime dueDate,
    required final ReminderType type,
    required final ReminderStatus status,
    final String? messageContent,
    final DateTime? scheduledAt,
    final DateTime? sentAt,
    final DateTime? deliveredAt,
    final bool? isResponded,
    final DateTime? respondedAt,
    final String? responseType,
    final String? assignedAgentId,
    final DateTime? agentAssignedAt,
  }) = _$EMIReminderImpl;

  factory _EMIReminder.fromJson(Map<String, dynamic> json) =
      _$EMIReminderImpl.fromJson;

  @override
  String get id;
  @override
  String get customerId;
  @override
  String get bookingId;
  @override
  String get customerName;
  @override
  String get phone;
  @override
  double get emiAmount;
  @override
  DateTime get dueDate; // Reminder
  @override
  ReminderType get type; // SMS, WhatsApp, Call, Email
  @override
  ReminderStatus get status; // Scheduled, Sent, Delivered, Failed
  @override
  String? get messageContent;
  @override
  DateTime? get scheduledAt;
  @override
  DateTime? get sentAt;
  @override
  DateTime? get deliveredAt; // Response
  @override
  bool? get isResponded;
  @override
  DateTime? get respondedAt;
  @override
  String? get responseType; // WillPay, NeedTime, CannotPay, Paid
  // Agent Assignment
  @override
  String? get assignedAgentId;
  @override
  DateTime? get agentAssignedAt;

  /// Create a copy of EMIReminder
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$EMIReminderImplCopyWith<_$EMIReminderImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
