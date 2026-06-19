// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'daily_caller_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
  'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models',
);

DailyCaller _$DailyCallerFromJson(Map<String, dynamic> json) {
  return _DailyCaller.fromJson(json);
}

/// @nodoc
mixin _$DailyCaller {
  String get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get phone => throw _privateConstructorUsedError;
  String get email => throw _privateConstructorUsedError;
  String? get photoUrl =>
      throw _privateConstructorUsedError; // Employment Details
  String get employeeId => throw _privateConstructorUsedError;
  DateTime get joiningDate => throw _privateConstructorUsedError;
  CallerType get callerType =>
      throw _privateConstructorUsedError; // FullTime, PartTime, Freelance
  SalaryType get salaryType =>
      throw _privateConstructorUsedError; // Fixed, CommissionOnly, FixedPlusCommission
  // Salary Structure
  double get monthlySalary => throw _privateConstructorUsedError;
  double? get dailyTargetAmount =>
      throw _privateConstructorUsedError; // Sales target per day
  int? get dailyCallTarget =>
      throw _privateConstructorUsedError; // Minimum calls per day
  int? get dailyTalkTimeTarget =>
      throw _privateConstructorUsedError; // Minutes per day
  // Commission Structure
  double? get commissionPerLead =>
      throw _privateConstructorUsedError; // Per valid lead
  double? get commissionPerBooking =>
      throw _privateConstructorUsedError; // Per booking conversion
  double? get commissionPercentage =>
      throw _privateConstructorUsedError; // % of booking value
  // Performance Tracking
  List<DailyCallReport> get dailyReports => throw _privateConstructorUsedError;
  List<MonthlyPerformance> get monthlyReports =>
      throw _privateConstructorUsedError; // Current Month Stats (Auto-calculated)
  int get currentMonthCalls => throw _privateConstructorUsedError;
  int get currentMonthConnected => throw _privateConstructorUsedError;
  int get currentMonthValidLeads => throw _privateConstructorUsedError;
  int get currentMonthBookings => throw _privateConstructorUsedError;
  double get currentMonthRevenue => throw _privateConstructorUsedError;
  double get currentMonthCommission => throw _privateConstructorUsedError;
  int get currentMonthTalkTimeMinutes =>
      throw _privateConstructorUsedError; // Assigned Leads
  List<String> get assignedLeadIds => throw _privateConstructorUsedError;
  List<CallerLeadAssignment> get leadAssignments =>
      throw _privateConstructorUsedError; // Status
  CallerStatus get status =>
      throw _privateConstructorUsedError; // Active, OnLeave, Suspended, Terminated
  DateTime? get lastActiveAt =>
      throw _privateConstructorUsedError; // Admin Notes
  String? get adminNotes => throw _privateConstructorUsedError;
  List<String> get performanceWarnings => throw _privateConstructorUsedError;
  DateTime get createdAt => throw _privateConstructorUsedError;
  DateTime get updatedAt => throw _privateConstructorUsedError;

  /// Serializes this DailyCaller to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of DailyCaller
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $DailyCallerCopyWith<DailyCaller> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $DailyCallerCopyWith<$Res> {
  factory $DailyCallerCopyWith(
    DailyCaller value,
    $Res Function(DailyCaller) then,
  ) = _$DailyCallerCopyWithImpl<$Res, DailyCaller>;
  @useResult
  $Res call({
    String id,
    String name,
    String phone,
    String email,
    String? photoUrl,
    String employeeId,
    DateTime joiningDate,
    CallerType callerType,
    SalaryType salaryType,
    double monthlySalary,
    double? dailyTargetAmount,
    int? dailyCallTarget,
    int? dailyTalkTimeTarget,
    double? commissionPerLead,
    double? commissionPerBooking,
    double? commissionPercentage,
    List<DailyCallReport> dailyReports,
    List<MonthlyPerformance> monthlyReports,
    int currentMonthCalls,
    int currentMonthConnected,
    int currentMonthValidLeads,
    int currentMonthBookings,
    double currentMonthRevenue,
    double currentMonthCommission,
    int currentMonthTalkTimeMinutes,
    List<String> assignedLeadIds,
    List<CallerLeadAssignment> leadAssignments,
    CallerStatus status,
    DateTime? lastActiveAt,
    String? adminNotes,
    List<String> performanceWarnings,
    DateTime createdAt,
    DateTime updatedAt,
  });
}

/// @nodoc
class _$DailyCallerCopyWithImpl<$Res, $Val extends DailyCaller>
    implements $DailyCallerCopyWith<$Res> {
  _$DailyCallerCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of DailyCaller
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? phone = null,
    Object? email = null,
    Object? photoUrl = freezed,
    Object? employeeId = null,
    Object? joiningDate = null,
    Object? callerType = null,
    Object? salaryType = null,
    Object? monthlySalary = null,
    Object? dailyTargetAmount = freezed,
    Object? dailyCallTarget = freezed,
    Object? dailyTalkTimeTarget = freezed,
    Object? commissionPerLead = freezed,
    Object? commissionPerBooking = freezed,
    Object? commissionPercentage = freezed,
    Object? dailyReports = null,
    Object? monthlyReports = null,
    Object? currentMonthCalls = null,
    Object? currentMonthConnected = null,
    Object? currentMonthValidLeads = null,
    Object? currentMonthBookings = null,
    Object? currentMonthRevenue = null,
    Object? currentMonthCommission = null,
    Object? currentMonthTalkTimeMinutes = null,
    Object? assignedLeadIds = null,
    Object? leadAssignments = null,
    Object? status = null,
    Object? lastActiveAt = freezed,
    Object? adminNotes = freezed,
    Object? performanceWarnings = null,
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
            employeeId: null == employeeId
                ? _value.employeeId
                : employeeId // ignore: cast_nullable_to_non_nullable
                      as String,
            joiningDate: null == joiningDate
                ? _value.joiningDate
                : joiningDate // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            callerType: null == callerType
                ? _value.callerType
                : callerType // ignore: cast_nullable_to_non_nullable
                      as CallerType,
            salaryType: null == salaryType
                ? _value.salaryType
                : salaryType // ignore: cast_nullable_to_non_nullable
                      as SalaryType,
            monthlySalary: null == monthlySalary
                ? _value.monthlySalary
                : monthlySalary // ignore: cast_nullable_to_non_nullable
                      as double,
            dailyTargetAmount: freezed == dailyTargetAmount
                ? _value.dailyTargetAmount
                : dailyTargetAmount // ignore: cast_nullable_to_non_nullable
                      as double?,
            dailyCallTarget: freezed == dailyCallTarget
                ? _value.dailyCallTarget
                : dailyCallTarget // ignore: cast_nullable_to_non_nullable
                      as int?,
            dailyTalkTimeTarget: freezed == dailyTalkTimeTarget
                ? _value.dailyTalkTimeTarget
                : dailyTalkTimeTarget // ignore: cast_nullable_to_non_nullable
                      as int?,
            commissionPerLead: freezed == commissionPerLead
                ? _value.commissionPerLead
                : commissionPerLead // ignore: cast_nullable_to_non_nullable
                      as double?,
            commissionPerBooking: freezed == commissionPerBooking
                ? _value.commissionPerBooking
                : commissionPerBooking // ignore: cast_nullable_to_non_nullable
                      as double?,
            commissionPercentage: freezed == commissionPercentage
                ? _value.commissionPercentage
                : commissionPercentage // ignore: cast_nullable_to_non_nullable
                      as double?,
            dailyReports: null == dailyReports
                ? _value.dailyReports
                : dailyReports // ignore: cast_nullable_to_non_nullable
                      as List<DailyCallReport>,
            monthlyReports: null == monthlyReports
                ? _value.monthlyReports
                : monthlyReports // ignore: cast_nullable_to_non_nullable
                      as List<MonthlyPerformance>,
            currentMonthCalls: null == currentMonthCalls
                ? _value.currentMonthCalls
                : currentMonthCalls // ignore: cast_nullable_to_non_nullable
                      as int,
            currentMonthConnected: null == currentMonthConnected
                ? _value.currentMonthConnected
                : currentMonthConnected // ignore: cast_nullable_to_non_nullable
                      as int,
            currentMonthValidLeads: null == currentMonthValidLeads
                ? _value.currentMonthValidLeads
                : currentMonthValidLeads // ignore: cast_nullable_to_non_nullable
                      as int,
            currentMonthBookings: null == currentMonthBookings
                ? _value.currentMonthBookings
                : currentMonthBookings // ignore: cast_nullable_to_non_nullable
                      as int,
            currentMonthRevenue: null == currentMonthRevenue
                ? _value.currentMonthRevenue
                : currentMonthRevenue // ignore: cast_nullable_to_non_nullable
                      as double,
            currentMonthCommission: null == currentMonthCommission
                ? _value.currentMonthCommission
                : currentMonthCommission // ignore: cast_nullable_to_non_nullable
                      as double,
            currentMonthTalkTimeMinutes: null == currentMonthTalkTimeMinutes
                ? _value.currentMonthTalkTimeMinutes
                : currentMonthTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
                      as int,
            assignedLeadIds: null == assignedLeadIds
                ? _value.assignedLeadIds
                : assignedLeadIds // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            leadAssignments: null == leadAssignments
                ? _value.leadAssignments
                : leadAssignments // ignore: cast_nullable_to_non_nullable
                      as List<CallerLeadAssignment>,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as CallerStatus,
            lastActiveAt: freezed == lastActiveAt
                ? _value.lastActiveAt
                : lastActiveAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            adminNotes: freezed == adminNotes
                ? _value.adminNotes
                : adminNotes // ignore: cast_nullable_to_non_nullable
                      as String?,
            performanceWarnings: null == performanceWarnings
                ? _value.performanceWarnings
                : performanceWarnings // ignore: cast_nullable_to_non_nullable
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
}

/// @nodoc
abstract class _$$DailyCallerImplCopyWith<$Res>
    implements $DailyCallerCopyWith<$Res> {
  factory _$$DailyCallerImplCopyWith(
    _$DailyCallerImpl value,
    $Res Function(_$DailyCallerImpl) then,
  ) = __$$DailyCallerImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String phone,
    String email,
    String? photoUrl,
    String employeeId,
    DateTime joiningDate,
    CallerType callerType,
    SalaryType salaryType,
    double monthlySalary,
    double? dailyTargetAmount,
    int? dailyCallTarget,
    int? dailyTalkTimeTarget,
    double? commissionPerLead,
    double? commissionPerBooking,
    double? commissionPercentage,
    List<DailyCallReport> dailyReports,
    List<MonthlyPerformance> monthlyReports,
    int currentMonthCalls,
    int currentMonthConnected,
    int currentMonthValidLeads,
    int currentMonthBookings,
    double currentMonthRevenue,
    double currentMonthCommission,
    int currentMonthTalkTimeMinutes,
    List<String> assignedLeadIds,
    List<CallerLeadAssignment> leadAssignments,
    CallerStatus status,
    DateTime? lastActiveAt,
    String? adminNotes,
    List<String> performanceWarnings,
    DateTime createdAt,
    DateTime updatedAt,
  });
}

/// @nodoc
class __$$DailyCallerImplCopyWithImpl<$Res>
    extends _$DailyCallerCopyWithImpl<$Res, _$DailyCallerImpl>
    implements _$$DailyCallerImplCopyWith<$Res> {
  __$$DailyCallerImplCopyWithImpl(
    _$DailyCallerImpl _value,
    $Res Function(_$DailyCallerImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of DailyCaller
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? phone = null,
    Object? email = null,
    Object? photoUrl = freezed,
    Object? employeeId = null,
    Object? joiningDate = null,
    Object? callerType = null,
    Object? salaryType = null,
    Object? monthlySalary = null,
    Object? dailyTargetAmount = freezed,
    Object? dailyCallTarget = freezed,
    Object? dailyTalkTimeTarget = freezed,
    Object? commissionPerLead = freezed,
    Object? commissionPerBooking = freezed,
    Object? commissionPercentage = freezed,
    Object? dailyReports = null,
    Object? monthlyReports = null,
    Object? currentMonthCalls = null,
    Object? currentMonthConnected = null,
    Object? currentMonthValidLeads = null,
    Object? currentMonthBookings = null,
    Object? currentMonthRevenue = null,
    Object? currentMonthCommission = null,
    Object? currentMonthTalkTimeMinutes = null,
    Object? assignedLeadIds = null,
    Object? leadAssignments = null,
    Object? status = null,
    Object? lastActiveAt = freezed,
    Object? adminNotes = freezed,
    Object? performanceWarnings = null,
    Object? createdAt = null,
    Object? updatedAt = null,
  }) {
    return _then(
      _$DailyCallerImpl(
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
        employeeId: null == employeeId
            ? _value.employeeId
            : employeeId // ignore: cast_nullable_to_non_nullable
                  as String,
        joiningDate: null == joiningDate
            ? _value.joiningDate
            : joiningDate // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        callerType: null == callerType
            ? _value.callerType
            : callerType // ignore: cast_nullable_to_non_nullable
                  as CallerType,
        salaryType: null == salaryType
            ? _value.salaryType
            : salaryType // ignore: cast_nullable_to_non_nullable
                  as SalaryType,
        monthlySalary: null == monthlySalary
            ? _value.monthlySalary
            : monthlySalary // ignore: cast_nullable_to_non_nullable
                  as double,
        dailyTargetAmount: freezed == dailyTargetAmount
            ? _value.dailyTargetAmount
            : dailyTargetAmount // ignore: cast_nullable_to_non_nullable
                  as double?,
        dailyCallTarget: freezed == dailyCallTarget
            ? _value.dailyCallTarget
            : dailyCallTarget // ignore: cast_nullable_to_non_nullable
                  as int?,
        dailyTalkTimeTarget: freezed == dailyTalkTimeTarget
            ? _value.dailyTalkTimeTarget
            : dailyTalkTimeTarget // ignore: cast_nullable_to_non_nullable
                  as int?,
        commissionPerLead: freezed == commissionPerLead
            ? _value.commissionPerLead
            : commissionPerLead // ignore: cast_nullable_to_non_nullable
                  as double?,
        commissionPerBooking: freezed == commissionPerBooking
            ? _value.commissionPerBooking
            : commissionPerBooking // ignore: cast_nullable_to_non_nullable
                  as double?,
        commissionPercentage: freezed == commissionPercentage
            ? _value.commissionPercentage
            : commissionPercentage // ignore: cast_nullable_to_non_nullable
                  as double?,
        dailyReports: null == dailyReports
            ? _value._dailyReports
            : dailyReports // ignore: cast_nullable_to_non_nullable
                  as List<DailyCallReport>,
        monthlyReports: null == monthlyReports
            ? _value._monthlyReports
            : monthlyReports // ignore: cast_nullable_to_non_nullable
                  as List<MonthlyPerformance>,
        currentMonthCalls: null == currentMonthCalls
            ? _value.currentMonthCalls
            : currentMonthCalls // ignore: cast_nullable_to_non_nullable
                  as int,
        currentMonthConnected: null == currentMonthConnected
            ? _value.currentMonthConnected
            : currentMonthConnected // ignore: cast_nullable_to_non_nullable
                  as int,
        currentMonthValidLeads: null == currentMonthValidLeads
            ? _value.currentMonthValidLeads
            : currentMonthValidLeads // ignore: cast_nullable_to_non_nullable
                  as int,
        currentMonthBookings: null == currentMonthBookings
            ? _value.currentMonthBookings
            : currentMonthBookings // ignore: cast_nullable_to_non_nullable
                  as int,
        currentMonthRevenue: null == currentMonthRevenue
            ? _value.currentMonthRevenue
            : currentMonthRevenue // ignore: cast_nullable_to_non_nullable
                  as double,
        currentMonthCommission: null == currentMonthCommission
            ? _value.currentMonthCommission
            : currentMonthCommission // ignore: cast_nullable_to_non_nullable
                  as double,
        currentMonthTalkTimeMinutes: null == currentMonthTalkTimeMinutes
            ? _value.currentMonthTalkTimeMinutes
            : currentMonthTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
                  as int,
        assignedLeadIds: null == assignedLeadIds
            ? _value._assignedLeadIds
            : assignedLeadIds // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        leadAssignments: null == leadAssignments
            ? _value._leadAssignments
            : leadAssignments // ignore: cast_nullable_to_non_nullable
                  as List<CallerLeadAssignment>,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as CallerStatus,
        lastActiveAt: freezed == lastActiveAt
            ? _value.lastActiveAt
            : lastActiveAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        adminNotes: freezed == adminNotes
            ? _value.adminNotes
            : adminNotes // ignore: cast_nullable_to_non_nullable
                  as String?,
        performanceWarnings: null == performanceWarnings
            ? _value._performanceWarnings
            : performanceWarnings // ignore: cast_nullable_to_non_nullable
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
class _$DailyCallerImpl extends _DailyCaller {
  const _$DailyCallerImpl({
    required this.id,
    required this.name,
    required this.phone,
    required this.email,
    this.photoUrl,
    required this.employeeId,
    required this.joiningDate,
    required this.callerType,
    required this.salaryType,
    required this.monthlySalary,
    this.dailyTargetAmount,
    this.dailyCallTarget,
    this.dailyTalkTimeTarget,
    this.commissionPerLead,
    this.commissionPerBooking,
    this.commissionPercentage,
    final List<DailyCallReport> dailyReports = const [],
    final List<MonthlyPerformance> monthlyReports = const [],
    this.currentMonthCalls = 0,
    this.currentMonthConnected = 0,
    this.currentMonthValidLeads = 0,
    this.currentMonthBookings = 0,
    this.currentMonthRevenue = 0,
    this.currentMonthCommission = 0,
    this.currentMonthTalkTimeMinutes = 0,
    final List<String> assignedLeadIds = const [],
    final List<CallerLeadAssignment> leadAssignments = const [],
    required this.status,
    this.lastActiveAt,
    this.adminNotes,
    final List<String> performanceWarnings = const [],
    required this.createdAt,
    required this.updatedAt,
  }) : _dailyReports = dailyReports,
       _monthlyReports = monthlyReports,
       _assignedLeadIds = assignedLeadIds,
       _leadAssignments = leadAssignments,
       _performanceWarnings = performanceWarnings,
       super._();

  factory _$DailyCallerImpl.fromJson(Map<String, dynamic> json) =>
      _$$DailyCallerImplFromJson(json);

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
  // Employment Details
  @override
  final String employeeId;
  @override
  final DateTime joiningDate;
  @override
  final CallerType callerType;
  // FullTime, PartTime, Freelance
  @override
  final SalaryType salaryType;
  // Fixed, CommissionOnly, FixedPlusCommission
  // Salary Structure
  @override
  final double monthlySalary;
  @override
  final double? dailyTargetAmount;
  // Sales target per day
  @override
  final int? dailyCallTarget;
  // Minimum calls per day
  @override
  final int? dailyTalkTimeTarget;
  // Minutes per day
  // Commission Structure
  @override
  final double? commissionPerLead;
  // Per valid lead
  @override
  final double? commissionPerBooking;
  // Per booking conversion
  @override
  final double? commissionPercentage;
  // % of booking value
  // Performance Tracking
  final List<DailyCallReport> _dailyReports;
  // % of booking value
  // Performance Tracking
  @override
  @JsonKey()
  List<DailyCallReport> get dailyReports {
    if (_dailyReports is EqualUnmodifiableListView) return _dailyReports;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_dailyReports);
  }

  final List<MonthlyPerformance> _monthlyReports;
  @override
  @JsonKey()
  List<MonthlyPerformance> get monthlyReports {
    if (_monthlyReports is EqualUnmodifiableListView) return _monthlyReports;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_monthlyReports);
  }

  // Current Month Stats (Auto-calculated)
  @override
  @JsonKey()
  final int currentMonthCalls;
  @override
  @JsonKey()
  final int currentMonthConnected;
  @override
  @JsonKey()
  final int currentMonthValidLeads;
  @override
  @JsonKey()
  final int currentMonthBookings;
  @override
  @JsonKey()
  final double currentMonthRevenue;
  @override
  @JsonKey()
  final double currentMonthCommission;
  @override
  @JsonKey()
  final int currentMonthTalkTimeMinutes;
  // Assigned Leads
  final List<String> _assignedLeadIds;
  // Assigned Leads
  @override
  @JsonKey()
  List<String> get assignedLeadIds {
    if (_assignedLeadIds is EqualUnmodifiableListView) return _assignedLeadIds;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_assignedLeadIds);
  }

  final List<CallerLeadAssignment> _leadAssignments;
  @override
  @JsonKey()
  List<CallerLeadAssignment> get leadAssignments {
    if (_leadAssignments is EqualUnmodifiableListView) return _leadAssignments;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_leadAssignments);
  }

  // Status
  @override
  final CallerStatus status;
  // Active, OnLeave, Suspended, Terminated
  @override
  final DateTime? lastActiveAt;
  // Admin Notes
  @override
  final String? adminNotes;
  final List<String> _performanceWarnings;
  @override
  @JsonKey()
  List<String> get performanceWarnings {
    if (_performanceWarnings is EqualUnmodifiableListView)
      return _performanceWarnings;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_performanceWarnings);
  }

  @override
  final DateTime createdAt;
  @override
  final DateTime updatedAt;

  @override
  String toString() {
    return 'DailyCaller(id: $id, name: $name, phone: $phone, email: $email, photoUrl: $photoUrl, employeeId: $employeeId, joiningDate: $joiningDate, callerType: $callerType, salaryType: $salaryType, monthlySalary: $monthlySalary, dailyTargetAmount: $dailyTargetAmount, dailyCallTarget: $dailyCallTarget, dailyTalkTimeTarget: $dailyTalkTimeTarget, commissionPerLead: $commissionPerLead, commissionPerBooking: $commissionPerBooking, commissionPercentage: $commissionPercentage, dailyReports: $dailyReports, monthlyReports: $monthlyReports, currentMonthCalls: $currentMonthCalls, currentMonthConnected: $currentMonthConnected, currentMonthValidLeads: $currentMonthValidLeads, currentMonthBookings: $currentMonthBookings, currentMonthRevenue: $currentMonthRevenue, currentMonthCommission: $currentMonthCommission, currentMonthTalkTimeMinutes: $currentMonthTalkTimeMinutes, assignedLeadIds: $assignedLeadIds, leadAssignments: $leadAssignments, status: $status, lastActiveAt: $lastActiveAt, adminNotes: $adminNotes, performanceWarnings: $performanceWarnings, createdAt: $createdAt, updatedAt: $updatedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$DailyCallerImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.phone, phone) || other.phone == phone) &&
            (identical(other.email, email) || other.email == email) &&
            (identical(other.photoUrl, photoUrl) ||
                other.photoUrl == photoUrl) &&
            (identical(other.employeeId, employeeId) ||
                other.employeeId == employeeId) &&
            (identical(other.joiningDate, joiningDate) ||
                other.joiningDate == joiningDate) &&
            (identical(other.callerType, callerType) ||
                other.callerType == callerType) &&
            (identical(other.salaryType, salaryType) ||
                other.salaryType == salaryType) &&
            (identical(other.monthlySalary, monthlySalary) ||
                other.monthlySalary == monthlySalary) &&
            (identical(other.dailyTargetAmount, dailyTargetAmount) ||
                other.dailyTargetAmount == dailyTargetAmount) &&
            (identical(other.dailyCallTarget, dailyCallTarget) ||
                other.dailyCallTarget == dailyCallTarget) &&
            (identical(other.dailyTalkTimeTarget, dailyTalkTimeTarget) ||
                other.dailyTalkTimeTarget == dailyTalkTimeTarget) &&
            (identical(other.commissionPerLead, commissionPerLead) ||
                other.commissionPerLead == commissionPerLead) &&
            (identical(other.commissionPerBooking, commissionPerBooking) ||
                other.commissionPerBooking == commissionPerBooking) &&
            (identical(other.commissionPercentage, commissionPercentage) ||
                other.commissionPercentage == commissionPercentage) &&
            const DeepCollectionEquality().equals(
              other._dailyReports,
              _dailyReports,
            ) &&
            const DeepCollectionEquality().equals(
              other._monthlyReports,
              _monthlyReports,
            ) &&
            (identical(other.currentMonthCalls, currentMonthCalls) ||
                other.currentMonthCalls == currentMonthCalls) &&
            (identical(other.currentMonthConnected, currentMonthConnected) ||
                other.currentMonthConnected == currentMonthConnected) &&
            (identical(other.currentMonthValidLeads, currentMonthValidLeads) ||
                other.currentMonthValidLeads == currentMonthValidLeads) &&
            (identical(other.currentMonthBookings, currentMonthBookings) ||
                other.currentMonthBookings == currentMonthBookings) &&
            (identical(other.currentMonthRevenue, currentMonthRevenue) ||
                other.currentMonthRevenue == currentMonthRevenue) &&
            (identical(other.currentMonthCommission, currentMonthCommission) ||
                other.currentMonthCommission == currentMonthCommission) &&
            (identical(
                  other.currentMonthTalkTimeMinutes,
                  currentMonthTalkTimeMinutes,
                ) ||
                other.currentMonthTalkTimeMinutes ==
                    currentMonthTalkTimeMinutes) &&
            const DeepCollectionEquality().equals(
              other._assignedLeadIds,
              _assignedLeadIds,
            ) &&
            const DeepCollectionEquality().equals(
              other._leadAssignments,
              _leadAssignments,
            ) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.lastActiveAt, lastActiveAt) ||
                other.lastActiveAt == lastActiveAt) &&
            (identical(other.adminNotes, adminNotes) ||
                other.adminNotes == adminNotes) &&
            const DeepCollectionEquality().equals(
              other._performanceWarnings,
              _performanceWarnings,
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
    employeeId,
    joiningDate,
    callerType,
    salaryType,
    monthlySalary,
    dailyTargetAmount,
    dailyCallTarget,
    dailyTalkTimeTarget,
    commissionPerLead,
    commissionPerBooking,
    commissionPercentage,
    const DeepCollectionEquality().hash(_dailyReports),
    const DeepCollectionEquality().hash(_monthlyReports),
    currentMonthCalls,
    currentMonthConnected,
    currentMonthValidLeads,
    currentMonthBookings,
    currentMonthRevenue,
    currentMonthCommission,
    currentMonthTalkTimeMinutes,
    const DeepCollectionEquality().hash(_assignedLeadIds),
    const DeepCollectionEquality().hash(_leadAssignments),
    status,
    lastActiveAt,
    adminNotes,
    const DeepCollectionEquality().hash(_performanceWarnings),
    createdAt,
    updatedAt,
  ]);

  /// Create a copy of DailyCaller
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$DailyCallerImplCopyWith<_$DailyCallerImpl> get copyWith =>
      __$$DailyCallerImplCopyWithImpl<_$DailyCallerImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$DailyCallerImplToJson(this);
  }
}

abstract class _DailyCaller extends DailyCaller {
  const factory _DailyCaller({
    required final String id,
    required final String name,
    required final String phone,
    required final String email,
    final String? photoUrl,
    required final String employeeId,
    required final DateTime joiningDate,
    required final CallerType callerType,
    required final SalaryType salaryType,
    required final double monthlySalary,
    final double? dailyTargetAmount,
    final int? dailyCallTarget,
    final int? dailyTalkTimeTarget,
    final double? commissionPerLead,
    final double? commissionPerBooking,
    final double? commissionPercentage,
    final List<DailyCallReport> dailyReports,
    final List<MonthlyPerformance> monthlyReports,
    final int currentMonthCalls,
    final int currentMonthConnected,
    final int currentMonthValidLeads,
    final int currentMonthBookings,
    final double currentMonthRevenue,
    final double currentMonthCommission,
    final int currentMonthTalkTimeMinutes,
    final List<String> assignedLeadIds,
    final List<CallerLeadAssignment> leadAssignments,
    required final CallerStatus status,
    final DateTime? lastActiveAt,
    final String? adminNotes,
    final List<String> performanceWarnings,
    required final DateTime createdAt,
    required final DateTime updatedAt,
  }) = _$DailyCallerImpl;
  const _DailyCaller._() : super._();

  factory _DailyCaller.fromJson(Map<String, dynamic> json) =
      _$DailyCallerImpl.fromJson;

  @override
  String get id;
  @override
  String get name;
  @override
  String get phone;
  @override
  String get email;
  @override
  String? get photoUrl; // Employment Details
  @override
  String get employeeId;
  @override
  DateTime get joiningDate;
  @override
  CallerType get callerType; // FullTime, PartTime, Freelance
  @override
  SalaryType get salaryType; // Fixed, CommissionOnly, FixedPlusCommission
  // Salary Structure
  @override
  double get monthlySalary;
  @override
  double? get dailyTargetAmount; // Sales target per day
  @override
  int? get dailyCallTarget; // Minimum calls per day
  @override
  int? get dailyTalkTimeTarget; // Minutes per day
  // Commission Structure
  @override
  double? get commissionPerLead; // Per valid lead
  @override
  double? get commissionPerBooking; // Per booking conversion
  @override
  double? get commissionPercentage; // % of booking value
  // Performance Tracking
  @override
  List<DailyCallReport> get dailyReports;
  @override
  List<MonthlyPerformance> get monthlyReports; // Current Month Stats (Auto-calculated)
  @override
  int get currentMonthCalls;
  @override
  int get currentMonthConnected;
  @override
  int get currentMonthValidLeads;
  @override
  int get currentMonthBookings;
  @override
  double get currentMonthRevenue;
  @override
  double get currentMonthCommission;
  @override
  int get currentMonthTalkTimeMinutes; // Assigned Leads
  @override
  List<String> get assignedLeadIds;
  @override
  List<CallerLeadAssignment> get leadAssignments; // Status
  @override
  CallerStatus get status; // Active, OnLeave, Suspended, Terminated
  @override
  DateTime? get lastActiveAt; // Admin Notes
  @override
  String? get adminNotes;
  @override
  List<String> get performanceWarnings;
  @override
  DateTime get createdAt;
  @override
  DateTime get updatedAt;

  /// Create a copy of DailyCaller
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$DailyCallerImplCopyWith<_$DailyCallerImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

DailyCallReport _$DailyCallReportFromJson(Map<String, dynamic> json) {
  return _DailyCallReport.fromJson(json);
}

/// @nodoc
mixin _$DailyCallReport {
  String get id => throw _privateConstructorUsedError;
  DateTime get date => throw _privateConstructorUsedError; // Call Statistics
  int get totalCalls => throw _privateConstructorUsedError;
  int get connected => throw _privateConstructorUsedError;
  int get notAnswered => throw _privateConstructorUsedError;
  int get busy => throw _privateConstructorUsedError;
  int get invalidNumber => throw _privateConstructorUsedError;
  int get callLater => throw _privateConstructorUsedError;
  int get notInterested => throw _privateConstructorUsedError; // Talk Time
  int get totalTalkTimeMinutes => throw _privateConstructorUsedError;
  double get avgTalkTimeMinutes =>
      throw _privateConstructorUsedError; // Lead Generation
  int get validLeadsGenerated => throw _privateConstructorUsedError;
  int get interestedCustomers => throw _privateConstructorUsedError;
  int get siteVisitsScheduled => throw _privateConstructorUsedError;
  int get bookingsConfirmed => throw _privateConstructorUsedError; // Financial
  double get revenueGenerated => throw _privateConstructorUsedError;
  double get commissionEarned =>
      throw _privateConstructorUsedError; // Detailed Log
  List<CallDetail> get callDetails =>
      throw _privateConstructorUsedError; // Status
  ReportStatus get status =>
      throw _privateConstructorUsedError; // Pending, Submitted, Verified
  String? get supervisorNotes => throw _privateConstructorUsedError;
  DateTime? get submittedAt => throw _privateConstructorUsedError;
  DateTime? get verifiedAt => throw _privateConstructorUsedError;

  /// Serializes this DailyCallReport to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of DailyCallReport
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $DailyCallReportCopyWith<DailyCallReport> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $DailyCallReportCopyWith<$Res> {
  factory $DailyCallReportCopyWith(
    DailyCallReport value,
    $Res Function(DailyCallReport) then,
  ) = _$DailyCallReportCopyWithImpl<$Res, DailyCallReport>;
  @useResult
  $Res call({
    String id,
    DateTime date,
    int totalCalls,
    int connected,
    int notAnswered,
    int busy,
    int invalidNumber,
    int callLater,
    int notInterested,
    int totalTalkTimeMinutes,
    double avgTalkTimeMinutes,
    int validLeadsGenerated,
    int interestedCustomers,
    int siteVisitsScheduled,
    int bookingsConfirmed,
    double revenueGenerated,
    double commissionEarned,
    List<CallDetail> callDetails,
    ReportStatus status,
    String? supervisorNotes,
    DateTime? submittedAt,
    DateTime? verifiedAt,
  });
}

/// @nodoc
class _$DailyCallReportCopyWithImpl<$Res, $Val extends DailyCallReport>
    implements $DailyCallReportCopyWith<$Res> {
  _$DailyCallReportCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of DailyCallReport
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? date = null,
    Object? totalCalls = null,
    Object? connected = null,
    Object? notAnswered = null,
    Object? busy = null,
    Object? invalidNumber = null,
    Object? callLater = null,
    Object? notInterested = null,
    Object? totalTalkTimeMinutes = null,
    Object? avgTalkTimeMinutes = null,
    Object? validLeadsGenerated = null,
    Object? interestedCustomers = null,
    Object? siteVisitsScheduled = null,
    Object? bookingsConfirmed = null,
    Object? revenueGenerated = null,
    Object? commissionEarned = null,
    Object? callDetails = null,
    Object? status = null,
    Object? supervisorNotes = freezed,
    Object? submittedAt = freezed,
    Object? verifiedAt = freezed,
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
            totalCalls: null == totalCalls
                ? _value.totalCalls
                : totalCalls // ignore: cast_nullable_to_non_nullable
                      as int,
            connected: null == connected
                ? _value.connected
                : connected // ignore: cast_nullable_to_non_nullable
                      as int,
            notAnswered: null == notAnswered
                ? _value.notAnswered
                : notAnswered // ignore: cast_nullable_to_non_nullable
                      as int,
            busy: null == busy
                ? _value.busy
                : busy // ignore: cast_nullable_to_non_nullable
                      as int,
            invalidNumber: null == invalidNumber
                ? _value.invalidNumber
                : invalidNumber // ignore: cast_nullable_to_non_nullable
                      as int,
            callLater: null == callLater
                ? _value.callLater
                : callLater // ignore: cast_nullable_to_non_nullable
                      as int,
            notInterested: null == notInterested
                ? _value.notInterested
                : notInterested // ignore: cast_nullable_to_non_nullable
                      as int,
            totalTalkTimeMinutes: null == totalTalkTimeMinutes
                ? _value.totalTalkTimeMinutes
                : totalTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
                      as int,
            avgTalkTimeMinutes: null == avgTalkTimeMinutes
                ? _value.avgTalkTimeMinutes
                : avgTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
                      as double,
            validLeadsGenerated: null == validLeadsGenerated
                ? _value.validLeadsGenerated
                : validLeadsGenerated // ignore: cast_nullable_to_non_nullable
                      as int,
            interestedCustomers: null == interestedCustomers
                ? _value.interestedCustomers
                : interestedCustomers // ignore: cast_nullable_to_non_nullable
                      as int,
            siteVisitsScheduled: null == siteVisitsScheduled
                ? _value.siteVisitsScheduled
                : siteVisitsScheduled // ignore: cast_nullable_to_non_nullable
                      as int,
            bookingsConfirmed: null == bookingsConfirmed
                ? _value.bookingsConfirmed
                : bookingsConfirmed // ignore: cast_nullable_to_non_nullable
                      as int,
            revenueGenerated: null == revenueGenerated
                ? _value.revenueGenerated
                : revenueGenerated // ignore: cast_nullable_to_non_nullable
                      as double,
            commissionEarned: null == commissionEarned
                ? _value.commissionEarned
                : commissionEarned // ignore: cast_nullable_to_non_nullable
                      as double,
            callDetails: null == callDetails
                ? _value.callDetails
                : callDetails // ignore: cast_nullable_to_non_nullable
                      as List<CallDetail>,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as ReportStatus,
            supervisorNotes: freezed == supervisorNotes
                ? _value.supervisorNotes
                : supervisorNotes // ignore: cast_nullable_to_non_nullable
                      as String?,
            submittedAt: freezed == submittedAt
                ? _value.submittedAt
                : submittedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            verifiedAt: freezed == verifiedAt
                ? _value.verifiedAt
                : verifiedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$DailyCallReportImplCopyWith<$Res>
    implements $DailyCallReportCopyWith<$Res> {
  factory _$$DailyCallReportImplCopyWith(
    _$DailyCallReportImpl value,
    $Res Function(_$DailyCallReportImpl) then,
  ) = __$$DailyCallReportImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    DateTime date,
    int totalCalls,
    int connected,
    int notAnswered,
    int busy,
    int invalidNumber,
    int callLater,
    int notInterested,
    int totalTalkTimeMinutes,
    double avgTalkTimeMinutes,
    int validLeadsGenerated,
    int interestedCustomers,
    int siteVisitsScheduled,
    int bookingsConfirmed,
    double revenueGenerated,
    double commissionEarned,
    List<CallDetail> callDetails,
    ReportStatus status,
    String? supervisorNotes,
    DateTime? submittedAt,
    DateTime? verifiedAt,
  });
}

/// @nodoc
class __$$DailyCallReportImplCopyWithImpl<$Res>
    extends _$DailyCallReportCopyWithImpl<$Res, _$DailyCallReportImpl>
    implements _$$DailyCallReportImplCopyWith<$Res> {
  __$$DailyCallReportImplCopyWithImpl(
    _$DailyCallReportImpl _value,
    $Res Function(_$DailyCallReportImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of DailyCallReport
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? date = null,
    Object? totalCalls = null,
    Object? connected = null,
    Object? notAnswered = null,
    Object? busy = null,
    Object? invalidNumber = null,
    Object? callLater = null,
    Object? notInterested = null,
    Object? totalTalkTimeMinutes = null,
    Object? avgTalkTimeMinutes = null,
    Object? validLeadsGenerated = null,
    Object? interestedCustomers = null,
    Object? siteVisitsScheduled = null,
    Object? bookingsConfirmed = null,
    Object? revenueGenerated = null,
    Object? commissionEarned = null,
    Object? callDetails = null,
    Object? status = null,
    Object? supervisorNotes = freezed,
    Object? submittedAt = freezed,
    Object? verifiedAt = freezed,
  }) {
    return _then(
      _$DailyCallReportImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        date: null == date
            ? _value.date
            : date // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        totalCalls: null == totalCalls
            ? _value.totalCalls
            : totalCalls // ignore: cast_nullable_to_non_nullable
                  as int,
        connected: null == connected
            ? _value.connected
            : connected // ignore: cast_nullable_to_non_nullable
                  as int,
        notAnswered: null == notAnswered
            ? _value.notAnswered
            : notAnswered // ignore: cast_nullable_to_non_nullable
                  as int,
        busy: null == busy
            ? _value.busy
            : busy // ignore: cast_nullable_to_non_nullable
                  as int,
        invalidNumber: null == invalidNumber
            ? _value.invalidNumber
            : invalidNumber // ignore: cast_nullable_to_non_nullable
                  as int,
        callLater: null == callLater
            ? _value.callLater
            : callLater // ignore: cast_nullable_to_non_nullable
                  as int,
        notInterested: null == notInterested
            ? _value.notInterested
            : notInterested // ignore: cast_nullable_to_non_nullable
                  as int,
        totalTalkTimeMinutes: null == totalTalkTimeMinutes
            ? _value.totalTalkTimeMinutes
            : totalTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
                  as int,
        avgTalkTimeMinutes: null == avgTalkTimeMinutes
            ? _value.avgTalkTimeMinutes
            : avgTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
                  as double,
        validLeadsGenerated: null == validLeadsGenerated
            ? _value.validLeadsGenerated
            : validLeadsGenerated // ignore: cast_nullable_to_non_nullable
                  as int,
        interestedCustomers: null == interestedCustomers
            ? _value.interestedCustomers
            : interestedCustomers // ignore: cast_nullable_to_non_nullable
                  as int,
        siteVisitsScheduled: null == siteVisitsScheduled
            ? _value.siteVisitsScheduled
            : siteVisitsScheduled // ignore: cast_nullable_to_non_nullable
                  as int,
        bookingsConfirmed: null == bookingsConfirmed
            ? _value.bookingsConfirmed
            : bookingsConfirmed // ignore: cast_nullable_to_non_nullable
                  as int,
        revenueGenerated: null == revenueGenerated
            ? _value.revenueGenerated
            : revenueGenerated // ignore: cast_nullable_to_non_nullable
                  as double,
        commissionEarned: null == commissionEarned
            ? _value.commissionEarned
            : commissionEarned // ignore: cast_nullable_to_non_nullable
                  as double,
        callDetails: null == callDetails
            ? _value._callDetails
            : callDetails // ignore: cast_nullable_to_non_nullable
                  as List<CallDetail>,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as ReportStatus,
        supervisorNotes: freezed == supervisorNotes
            ? _value.supervisorNotes
            : supervisorNotes // ignore: cast_nullable_to_non_nullable
                  as String?,
        submittedAt: freezed == submittedAt
            ? _value.submittedAt
            : submittedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        verifiedAt: freezed == verifiedAt
            ? _value.verifiedAt
            : verifiedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$DailyCallReportImpl implements _DailyCallReport {
  const _$DailyCallReportImpl({
    required this.id,
    required this.date,
    this.totalCalls = 0,
    this.connected = 0,
    this.notAnswered = 0,
    this.busy = 0,
    this.invalidNumber = 0,
    this.callLater = 0,
    this.notInterested = 0,
    this.totalTalkTimeMinutes = 0,
    this.avgTalkTimeMinutes = 0,
    this.validLeadsGenerated = 0,
    this.interestedCustomers = 0,
    this.siteVisitsScheduled = 0,
    this.bookingsConfirmed = 0,
    this.revenueGenerated = 0,
    this.commissionEarned = 0,
    final List<CallDetail> callDetails = const [],
    required this.status,
    this.supervisorNotes,
    this.submittedAt,
    this.verifiedAt,
  }) : _callDetails = callDetails;

  factory _$DailyCallReportImpl.fromJson(Map<String, dynamic> json) =>
      _$$DailyCallReportImplFromJson(json);

  @override
  final String id;
  @override
  final DateTime date;
  // Call Statistics
  @override
  @JsonKey()
  final int totalCalls;
  @override
  @JsonKey()
  final int connected;
  @override
  @JsonKey()
  final int notAnswered;
  @override
  @JsonKey()
  final int busy;
  @override
  @JsonKey()
  final int invalidNumber;
  @override
  @JsonKey()
  final int callLater;
  @override
  @JsonKey()
  final int notInterested;
  // Talk Time
  @override
  @JsonKey()
  final int totalTalkTimeMinutes;
  @override
  @JsonKey()
  final double avgTalkTimeMinutes;
  // Lead Generation
  @override
  @JsonKey()
  final int validLeadsGenerated;
  @override
  @JsonKey()
  final int interestedCustomers;
  @override
  @JsonKey()
  final int siteVisitsScheduled;
  @override
  @JsonKey()
  final int bookingsConfirmed;
  // Financial
  @override
  @JsonKey()
  final double revenueGenerated;
  @override
  @JsonKey()
  final double commissionEarned;
  // Detailed Log
  final List<CallDetail> _callDetails;
  // Detailed Log
  @override
  @JsonKey()
  List<CallDetail> get callDetails {
    if (_callDetails is EqualUnmodifiableListView) return _callDetails;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_callDetails);
  }

  // Status
  @override
  final ReportStatus status;
  // Pending, Submitted, Verified
  @override
  final String? supervisorNotes;
  @override
  final DateTime? submittedAt;
  @override
  final DateTime? verifiedAt;

  @override
  String toString() {
    return 'DailyCallReport(id: $id, date: $date, totalCalls: $totalCalls, connected: $connected, notAnswered: $notAnswered, busy: $busy, invalidNumber: $invalidNumber, callLater: $callLater, notInterested: $notInterested, totalTalkTimeMinutes: $totalTalkTimeMinutes, avgTalkTimeMinutes: $avgTalkTimeMinutes, validLeadsGenerated: $validLeadsGenerated, interestedCustomers: $interestedCustomers, siteVisitsScheduled: $siteVisitsScheduled, bookingsConfirmed: $bookingsConfirmed, revenueGenerated: $revenueGenerated, commissionEarned: $commissionEarned, callDetails: $callDetails, status: $status, supervisorNotes: $supervisorNotes, submittedAt: $submittedAt, verifiedAt: $verifiedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$DailyCallReportImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.date, date) || other.date == date) &&
            (identical(other.totalCalls, totalCalls) ||
                other.totalCalls == totalCalls) &&
            (identical(other.connected, connected) ||
                other.connected == connected) &&
            (identical(other.notAnswered, notAnswered) ||
                other.notAnswered == notAnswered) &&
            (identical(other.busy, busy) || other.busy == busy) &&
            (identical(other.invalidNumber, invalidNumber) ||
                other.invalidNumber == invalidNumber) &&
            (identical(other.callLater, callLater) ||
                other.callLater == callLater) &&
            (identical(other.notInterested, notInterested) ||
                other.notInterested == notInterested) &&
            (identical(other.totalTalkTimeMinutes, totalTalkTimeMinutes) ||
                other.totalTalkTimeMinutes == totalTalkTimeMinutes) &&
            (identical(other.avgTalkTimeMinutes, avgTalkTimeMinutes) ||
                other.avgTalkTimeMinutes == avgTalkTimeMinutes) &&
            (identical(other.validLeadsGenerated, validLeadsGenerated) ||
                other.validLeadsGenerated == validLeadsGenerated) &&
            (identical(other.interestedCustomers, interestedCustomers) ||
                other.interestedCustomers == interestedCustomers) &&
            (identical(other.siteVisitsScheduled, siteVisitsScheduled) ||
                other.siteVisitsScheduled == siteVisitsScheduled) &&
            (identical(other.bookingsConfirmed, bookingsConfirmed) ||
                other.bookingsConfirmed == bookingsConfirmed) &&
            (identical(other.revenueGenerated, revenueGenerated) ||
                other.revenueGenerated == revenueGenerated) &&
            (identical(other.commissionEarned, commissionEarned) ||
                other.commissionEarned == commissionEarned) &&
            const DeepCollectionEquality().equals(
              other._callDetails,
              _callDetails,
            ) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.supervisorNotes, supervisorNotes) ||
                other.supervisorNotes == supervisorNotes) &&
            (identical(other.submittedAt, submittedAt) ||
                other.submittedAt == submittedAt) &&
            (identical(other.verifiedAt, verifiedAt) ||
                other.verifiedAt == verifiedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    id,
    date,
    totalCalls,
    connected,
    notAnswered,
    busy,
    invalidNumber,
    callLater,
    notInterested,
    totalTalkTimeMinutes,
    avgTalkTimeMinutes,
    validLeadsGenerated,
    interestedCustomers,
    siteVisitsScheduled,
    bookingsConfirmed,
    revenueGenerated,
    commissionEarned,
    const DeepCollectionEquality().hash(_callDetails),
    status,
    supervisorNotes,
    submittedAt,
    verifiedAt,
  ]);

  /// Create a copy of DailyCallReport
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$DailyCallReportImplCopyWith<_$DailyCallReportImpl> get copyWith =>
      __$$DailyCallReportImplCopyWithImpl<_$DailyCallReportImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$DailyCallReportImplToJson(this);
  }
}

abstract class _DailyCallReport implements DailyCallReport {
  const factory _DailyCallReport({
    required final String id,
    required final DateTime date,
    final int totalCalls,
    final int connected,
    final int notAnswered,
    final int busy,
    final int invalidNumber,
    final int callLater,
    final int notInterested,
    final int totalTalkTimeMinutes,
    final double avgTalkTimeMinutes,
    final int validLeadsGenerated,
    final int interestedCustomers,
    final int siteVisitsScheduled,
    final int bookingsConfirmed,
    final double revenueGenerated,
    final double commissionEarned,
    final List<CallDetail> callDetails,
    required final ReportStatus status,
    final String? supervisorNotes,
    final DateTime? submittedAt,
    final DateTime? verifiedAt,
  }) = _$DailyCallReportImpl;

  factory _DailyCallReport.fromJson(Map<String, dynamic> json) =
      _$DailyCallReportImpl.fromJson;

  @override
  String get id;
  @override
  DateTime get date; // Call Statistics
  @override
  int get totalCalls;
  @override
  int get connected;
  @override
  int get notAnswered;
  @override
  int get busy;
  @override
  int get invalidNumber;
  @override
  int get callLater;
  @override
  int get notInterested; // Talk Time
  @override
  int get totalTalkTimeMinutes;
  @override
  double get avgTalkTimeMinutes; // Lead Generation
  @override
  int get validLeadsGenerated;
  @override
  int get interestedCustomers;
  @override
  int get siteVisitsScheduled;
  @override
  int get bookingsConfirmed; // Financial
  @override
  double get revenueGenerated;
  @override
  double get commissionEarned; // Detailed Log
  @override
  List<CallDetail> get callDetails; // Status
  @override
  ReportStatus get status; // Pending, Submitted, Verified
  @override
  String? get supervisorNotes;
  @override
  DateTime? get submittedAt;
  @override
  DateTime? get verifiedAt;

  /// Create a copy of DailyCallReport
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$DailyCallReportImplCopyWith<_$DailyCallReportImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

CallDetail _$CallDetailFromJson(Map<String, dynamic> json) {
  return _CallDetail.fromJson(json);
}

/// @nodoc
mixin _$CallDetail {
  String get leadId => throw _privateConstructorUsedError;
  String get leadName => throw _privateConstructorUsedError;
  String get leadPhone => throw _privateConstructorUsedError;
  DateTime get callTime => throw _privateConstructorUsedError;
  CallOutcome get outcome => throw _privateConstructorUsedError;
  int? get talkTimeSeconds => throw _privateConstructorUsedError;
  String? get notes => throw _privateConstructorUsedError;
  String? get recordingUrl =>
      throw _privateConstructorUsedError; // Call recording
  GeoLocation? get location => throw _privateConstructorUsedError;

  /// Serializes this CallDetail to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of CallDetail
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $CallDetailCopyWith<CallDetail> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $CallDetailCopyWith<$Res> {
  factory $CallDetailCopyWith(
    CallDetail value,
    $Res Function(CallDetail) then,
  ) = _$CallDetailCopyWithImpl<$Res, CallDetail>;
  @useResult
  $Res call({
    String leadId,
    String leadName,
    String leadPhone,
    DateTime callTime,
    CallOutcome outcome,
    int? talkTimeSeconds,
    String? notes,
    String? recordingUrl,
    GeoLocation? location,
  });
}

/// @nodoc
class _$CallDetailCopyWithImpl<$Res, $Val extends CallDetail>
    implements $CallDetailCopyWith<$Res> {
  _$CallDetailCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of CallDetail
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? leadId = null,
    Object? leadName = null,
    Object? leadPhone = null,
    Object? callTime = null,
    Object? outcome = null,
    Object? talkTimeSeconds = freezed,
    Object? notes = freezed,
    Object? recordingUrl = freezed,
    Object? location = freezed,
  }) {
    return _then(
      _value.copyWith(
            leadId: null == leadId
                ? _value.leadId
                : leadId // ignore: cast_nullable_to_non_nullable
                      as String,
            leadName: null == leadName
                ? _value.leadName
                : leadName // ignore: cast_nullable_to_non_nullable
                      as String,
            leadPhone: null == leadPhone
                ? _value.leadPhone
                : leadPhone // ignore: cast_nullable_to_non_nullable
                      as String,
            callTime: null == callTime
                ? _value.callTime
                : callTime // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            outcome: null == outcome
                ? _value.outcome
                : outcome // ignore: cast_nullable_to_non_nullable
                      as CallOutcome,
            talkTimeSeconds: freezed == talkTimeSeconds
                ? _value.talkTimeSeconds
                : talkTimeSeconds // ignore: cast_nullable_to_non_nullable
                      as int?,
            notes: freezed == notes
                ? _value.notes
                : notes // ignore: cast_nullable_to_non_nullable
                      as String?,
            recordingUrl: freezed == recordingUrl
                ? _value.recordingUrl
                : recordingUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            location: freezed == location
                ? _value.location
                : location // ignore: cast_nullable_to_non_nullable
                      as GeoLocation?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$CallDetailImplCopyWith<$Res>
    implements $CallDetailCopyWith<$Res> {
  factory _$$CallDetailImplCopyWith(
    _$CallDetailImpl value,
    $Res Function(_$CallDetailImpl) then,
  ) = __$$CallDetailImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String leadId,
    String leadName,
    String leadPhone,
    DateTime callTime,
    CallOutcome outcome,
    int? talkTimeSeconds,
    String? notes,
    String? recordingUrl,
    GeoLocation? location,
  });
}

/// @nodoc
class __$$CallDetailImplCopyWithImpl<$Res>
    extends _$CallDetailCopyWithImpl<$Res, _$CallDetailImpl>
    implements _$$CallDetailImplCopyWith<$Res> {
  __$$CallDetailImplCopyWithImpl(
    _$CallDetailImpl _value,
    $Res Function(_$CallDetailImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of CallDetail
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? leadId = null,
    Object? leadName = null,
    Object? leadPhone = null,
    Object? callTime = null,
    Object? outcome = null,
    Object? talkTimeSeconds = freezed,
    Object? notes = freezed,
    Object? recordingUrl = freezed,
    Object? location = freezed,
  }) {
    return _then(
      _$CallDetailImpl(
        leadId: null == leadId
            ? _value.leadId
            : leadId // ignore: cast_nullable_to_non_nullable
                  as String,
        leadName: null == leadName
            ? _value.leadName
            : leadName // ignore: cast_nullable_to_non_nullable
                  as String,
        leadPhone: null == leadPhone
            ? _value.leadPhone
            : leadPhone // ignore: cast_nullable_to_non_nullable
                  as String,
        callTime: null == callTime
            ? _value.callTime
            : callTime // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        outcome: null == outcome
            ? _value.outcome
            : outcome // ignore: cast_nullable_to_non_nullable
                  as CallOutcome,
        talkTimeSeconds: freezed == talkTimeSeconds
            ? _value.talkTimeSeconds
            : talkTimeSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        notes: freezed == notes
            ? _value.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        recordingUrl: freezed == recordingUrl
            ? _value.recordingUrl
            : recordingUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        location: freezed == location
            ? _value.location
            : location // ignore: cast_nullable_to_non_nullable
                  as GeoLocation?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$CallDetailImpl implements _CallDetail {
  const _$CallDetailImpl({
    required this.leadId,
    required this.leadName,
    required this.leadPhone,
    required this.callTime,
    required this.outcome,
    this.talkTimeSeconds,
    this.notes,
    this.recordingUrl,
    this.location,
  });

  factory _$CallDetailImpl.fromJson(Map<String, dynamic> json) =>
      _$$CallDetailImplFromJson(json);

  @override
  final String leadId;
  @override
  final String leadName;
  @override
  final String leadPhone;
  @override
  final DateTime callTime;
  @override
  final CallOutcome outcome;
  @override
  final int? talkTimeSeconds;
  @override
  final String? notes;
  @override
  final String? recordingUrl;
  // Call recording
  @override
  final GeoLocation? location;

  @override
  String toString() {
    return 'CallDetail(leadId: $leadId, leadName: $leadName, leadPhone: $leadPhone, callTime: $callTime, outcome: $outcome, talkTimeSeconds: $talkTimeSeconds, notes: $notes, recordingUrl: $recordingUrl, location: $location)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$CallDetailImpl &&
            (identical(other.leadId, leadId) || other.leadId == leadId) &&
            (identical(other.leadName, leadName) ||
                other.leadName == leadName) &&
            (identical(other.leadPhone, leadPhone) ||
                other.leadPhone == leadPhone) &&
            (identical(other.callTime, callTime) ||
                other.callTime == callTime) &&
            (identical(other.outcome, outcome) || other.outcome == outcome) &&
            (identical(other.talkTimeSeconds, talkTimeSeconds) ||
                other.talkTimeSeconds == talkTimeSeconds) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            (identical(other.recordingUrl, recordingUrl) ||
                other.recordingUrl == recordingUrl) &&
            (identical(other.location, location) ||
                other.location == location));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    leadId,
    leadName,
    leadPhone,
    callTime,
    outcome,
    talkTimeSeconds,
    notes,
    recordingUrl,
    location,
  );

  /// Create a copy of CallDetail
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$CallDetailImplCopyWith<_$CallDetailImpl> get copyWith =>
      __$$CallDetailImplCopyWithImpl<_$CallDetailImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$CallDetailImplToJson(this);
  }
}

abstract class _CallDetail implements CallDetail {
  const factory _CallDetail({
    required final String leadId,
    required final String leadName,
    required final String leadPhone,
    required final DateTime callTime,
    required final CallOutcome outcome,
    final int? talkTimeSeconds,
    final String? notes,
    final String? recordingUrl,
    final GeoLocation? location,
  }) = _$CallDetailImpl;

  factory _CallDetail.fromJson(Map<String, dynamic> json) =
      _$CallDetailImpl.fromJson;

  @override
  String get leadId;
  @override
  String get leadName;
  @override
  String get leadPhone;
  @override
  DateTime get callTime;
  @override
  CallOutcome get outcome;
  @override
  int? get talkTimeSeconds;
  @override
  String? get notes;
  @override
  String? get recordingUrl; // Call recording
  @override
  GeoLocation? get location;

  /// Create a copy of CallDetail
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$CallDetailImplCopyWith<_$CallDetailImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

MonthlyPerformance _$MonthlyPerformanceFromJson(Map<String, dynamic> json) {
  return _MonthlyPerformance.fromJson(json);
}

/// @nodoc
mixin _$MonthlyPerformance {
  String get id => throw _privateConstructorUsedError;
  int get year => throw _privateConstructorUsedError;
  int get month => throw _privateConstructorUsedError; // Call Stats
  int get totalCalls => throw _privateConstructorUsedError;
  int get connectedCalls => throw _privateConstructorUsedError;
  int get totalTalkTimeMinutes =>
      throw _privateConstructorUsedError; // Lead & Sales
  int get validLeads => throw _privateConstructorUsedError;
  int get siteVisits => throw _privateConstructorUsedError;
  int get bookings => throw _privateConstructorUsedError;
  double get totalRevenue => throw _privateConstructorUsedError; // Financial
  double get baseSalary => throw _privateConstructorUsedError;
  double get commissionEarned => throw _privateConstructorUsedError;
  double get incentives => throw _privateConstructorUsedError;
  double get deductions => throw _privateConstructorUsedError;
  double get totalEarnings =>
      throw _privateConstructorUsedError; // Target Achievement
  double get targetAchievementPercentage => throw _privateConstructorUsedError;
  int get ranking => throw _privateConstructorUsedError; // Among all callers
  // Daily average
  double get avgCallsPerDay => throw _privateConstructorUsedError;
  double get avgTalkTimePerDay => throw _privateConstructorUsedError;
  double get avgLeadsPerDay =>
      throw _privateConstructorUsedError; // Quality metrics
  double get leadQualityScore => throw _privateConstructorUsedError; // 0-100
  double get conversionRate => throw _privateConstructorUsedError;
  PaymentStatus get paymentStatus => throw _privateConstructorUsedError;
  DateTime? get paidAt => throw _privateConstructorUsedError;

  /// Serializes this MonthlyPerformance to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of MonthlyPerformance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $MonthlyPerformanceCopyWith<MonthlyPerformance> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $MonthlyPerformanceCopyWith<$Res> {
  factory $MonthlyPerformanceCopyWith(
    MonthlyPerformance value,
    $Res Function(MonthlyPerformance) then,
  ) = _$MonthlyPerformanceCopyWithImpl<$Res, MonthlyPerformance>;
  @useResult
  $Res call({
    String id,
    int year,
    int month,
    int totalCalls,
    int connectedCalls,
    int totalTalkTimeMinutes,
    int validLeads,
    int siteVisits,
    int bookings,
    double totalRevenue,
    double baseSalary,
    double commissionEarned,
    double incentives,
    double deductions,
    double totalEarnings,
    double targetAchievementPercentage,
    int ranking,
    double avgCallsPerDay,
    double avgTalkTimePerDay,
    double avgLeadsPerDay,
    double leadQualityScore,
    double conversionRate,
    PaymentStatus paymentStatus,
    DateTime? paidAt,
  });
}

/// @nodoc
class _$MonthlyPerformanceCopyWithImpl<$Res, $Val extends MonthlyPerformance>
    implements $MonthlyPerformanceCopyWith<$Res> {
  _$MonthlyPerformanceCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of MonthlyPerformance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? year = null,
    Object? month = null,
    Object? totalCalls = null,
    Object? connectedCalls = null,
    Object? totalTalkTimeMinutes = null,
    Object? validLeads = null,
    Object? siteVisits = null,
    Object? bookings = null,
    Object? totalRevenue = null,
    Object? baseSalary = null,
    Object? commissionEarned = null,
    Object? incentives = null,
    Object? deductions = null,
    Object? totalEarnings = null,
    Object? targetAchievementPercentage = null,
    Object? ranking = null,
    Object? avgCallsPerDay = null,
    Object? avgTalkTimePerDay = null,
    Object? avgLeadsPerDay = null,
    Object? leadQualityScore = null,
    Object? conversionRate = null,
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
            totalCalls: null == totalCalls
                ? _value.totalCalls
                : totalCalls // ignore: cast_nullable_to_non_nullable
                      as int,
            connectedCalls: null == connectedCalls
                ? _value.connectedCalls
                : connectedCalls // ignore: cast_nullable_to_non_nullable
                      as int,
            totalTalkTimeMinutes: null == totalTalkTimeMinutes
                ? _value.totalTalkTimeMinutes
                : totalTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
                      as int,
            validLeads: null == validLeads
                ? _value.validLeads
                : validLeads // ignore: cast_nullable_to_non_nullable
                      as int,
            siteVisits: null == siteVisits
                ? _value.siteVisits
                : siteVisits // ignore: cast_nullable_to_non_nullable
                      as int,
            bookings: null == bookings
                ? _value.bookings
                : bookings // ignore: cast_nullable_to_non_nullable
                      as int,
            totalRevenue: null == totalRevenue
                ? _value.totalRevenue
                : totalRevenue // ignore: cast_nullable_to_non_nullable
                      as double,
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
            targetAchievementPercentage: null == targetAchievementPercentage
                ? _value.targetAchievementPercentage
                : targetAchievementPercentage // ignore: cast_nullable_to_non_nullable
                      as double,
            ranking: null == ranking
                ? _value.ranking
                : ranking // ignore: cast_nullable_to_non_nullable
                      as int,
            avgCallsPerDay: null == avgCallsPerDay
                ? _value.avgCallsPerDay
                : avgCallsPerDay // ignore: cast_nullable_to_non_nullable
                      as double,
            avgTalkTimePerDay: null == avgTalkTimePerDay
                ? _value.avgTalkTimePerDay
                : avgTalkTimePerDay // ignore: cast_nullable_to_non_nullable
                      as double,
            avgLeadsPerDay: null == avgLeadsPerDay
                ? _value.avgLeadsPerDay
                : avgLeadsPerDay // ignore: cast_nullable_to_non_nullable
                      as double,
            leadQualityScore: null == leadQualityScore
                ? _value.leadQualityScore
                : leadQualityScore // ignore: cast_nullable_to_non_nullable
                      as double,
            conversionRate: null == conversionRate
                ? _value.conversionRate
                : conversionRate // ignore: cast_nullable_to_non_nullable
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
abstract class _$$MonthlyPerformanceImplCopyWith<$Res>
    implements $MonthlyPerformanceCopyWith<$Res> {
  factory _$$MonthlyPerformanceImplCopyWith(
    _$MonthlyPerformanceImpl value,
    $Res Function(_$MonthlyPerformanceImpl) then,
  ) = __$$MonthlyPerformanceImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    int year,
    int month,
    int totalCalls,
    int connectedCalls,
    int totalTalkTimeMinutes,
    int validLeads,
    int siteVisits,
    int bookings,
    double totalRevenue,
    double baseSalary,
    double commissionEarned,
    double incentives,
    double deductions,
    double totalEarnings,
    double targetAchievementPercentage,
    int ranking,
    double avgCallsPerDay,
    double avgTalkTimePerDay,
    double avgLeadsPerDay,
    double leadQualityScore,
    double conversionRate,
    PaymentStatus paymentStatus,
    DateTime? paidAt,
  });
}

/// @nodoc
class __$$MonthlyPerformanceImplCopyWithImpl<$Res>
    extends _$MonthlyPerformanceCopyWithImpl<$Res, _$MonthlyPerformanceImpl>
    implements _$$MonthlyPerformanceImplCopyWith<$Res> {
  __$$MonthlyPerformanceImplCopyWithImpl(
    _$MonthlyPerformanceImpl _value,
    $Res Function(_$MonthlyPerformanceImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of MonthlyPerformance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? year = null,
    Object? month = null,
    Object? totalCalls = null,
    Object? connectedCalls = null,
    Object? totalTalkTimeMinutes = null,
    Object? validLeads = null,
    Object? siteVisits = null,
    Object? bookings = null,
    Object? totalRevenue = null,
    Object? baseSalary = null,
    Object? commissionEarned = null,
    Object? incentives = null,
    Object? deductions = null,
    Object? totalEarnings = null,
    Object? targetAchievementPercentage = null,
    Object? ranking = null,
    Object? avgCallsPerDay = null,
    Object? avgTalkTimePerDay = null,
    Object? avgLeadsPerDay = null,
    Object? leadQualityScore = null,
    Object? conversionRate = null,
    Object? paymentStatus = null,
    Object? paidAt = freezed,
  }) {
    return _then(
      _$MonthlyPerformanceImpl(
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
        totalCalls: null == totalCalls
            ? _value.totalCalls
            : totalCalls // ignore: cast_nullable_to_non_nullable
                  as int,
        connectedCalls: null == connectedCalls
            ? _value.connectedCalls
            : connectedCalls // ignore: cast_nullable_to_non_nullable
                  as int,
        totalTalkTimeMinutes: null == totalTalkTimeMinutes
            ? _value.totalTalkTimeMinutes
            : totalTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
                  as int,
        validLeads: null == validLeads
            ? _value.validLeads
            : validLeads // ignore: cast_nullable_to_non_nullable
                  as int,
        siteVisits: null == siteVisits
            ? _value.siteVisits
            : siteVisits // ignore: cast_nullable_to_non_nullable
                  as int,
        bookings: null == bookings
            ? _value.bookings
            : bookings // ignore: cast_nullable_to_non_nullable
                  as int,
        totalRevenue: null == totalRevenue
            ? _value.totalRevenue
            : totalRevenue // ignore: cast_nullable_to_non_nullable
                  as double,
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
        targetAchievementPercentage: null == targetAchievementPercentage
            ? _value.targetAchievementPercentage
            : targetAchievementPercentage // ignore: cast_nullable_to_non_nullable
                  as double,
        ranking: null == ranking
            ? _value.ranking
            : ranking // ignore: cast_nullable_to_non_nullable
                  as int,
        avgCallsPerDay: null == avgCallsPerDay
            ? _value.avgCallsPerDay
            : avgCallsPerDay // ignore: cast_nullable_to_non_nullable
                  as double,
        avgTalkTimePerDay: null == avgTalkTimePerDay
            ? _value.avgTalkTimePerDay
            : avgTalkTimePerDay // ignore: cast_nullable_to_non_nullable
                  as double,
        avgLeadsPerDay: null == avgLeadsPerDay
            ? _value.avgLeadsPerDay
            : avgLeadsPerDay // ignore: cast_nullable_to_non_nullable
                  as double,
        leadQualityScore: null == leadQualityScore
            ? _value.leadQualityScore
            : leadQualityScore // ignore: cast_nullable_to_non_nullable
                  as double,
        conversionRate: null == conversionRate
            ? _value.conversionRate
            : conversionRate // ignore: cast_nullable_to_non_nullable
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
class _$MonthlyPerformanceImpl implements _MonthlyPerformance {
  const _$MonthlyPerformanceImpl({
    required this.id,
    required this.year,
    required this.month,
    this.totalCalls = 0,
    this.connectedCalls = 0,
    this.totalTalkTimeMinutes = 0,
    this.validLeads = 0,
    this.siteVisits = 0,
    this.bookings = 0,
    this.totalRevenue = 0,
    this.baseSalary = 0,
    this.commissionEarned = 0,
    this.incentives = 0,
    this.deductions = 0,
    this.totalEarnings = 0,
    this.targetAchievementPercentage = 0,
    this.ranking = 0,
    this.avgCallsPerDay = 0,
    this.avgTalkTimePerDay = 0,
    this.avgLeadsPerDay = 0,
    this.leadQualityScore = 0,
    this.conversionRate = 0,
    required this.paymentStatus,
    this.paidAt,
  });

  factory _$MonthlyPerformanceImpl.fromJson(Map<String, dynamic> json) =>
      _$$MonthlyPerformanceImplFromJson(json);

  @override
  final String id;
  @override
  final int year;
  @override
  final int month;
  // Call Stats
  @override
  @JsonKey()
  final int totalCalls;
  @override
  @JsonKey()
  final int connectedCalls;
  @override
  @JsonKey()
  final int totalTalkTimeMinutes;
  // Lead & Sales
  @override
  @JsonKey()
  final int validLeads;
  @override
  @JsonKey()
  final int siteVisits;
  @override
  @JsonKey()
  final int bookings;
  @override
  @JsonKey()
  final double totalRevenue;
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
  // Target Achievement
  @override
  @JsonKey()
  final double targetAchievementPercentage;
  @override
  @JsonKey()
  final int ranking;
  // Among all callers
  // Daily average
  @override
  @JsonKey()
  final double avgCallsPerDay;
  @override
  @JsonKey()
  final double avgTalkTimePerDay;
  @override
  @JsonKey()
  final double avgLeadsPerDay;
  // Quality metrics
  @override
  @JsonKey()
  final double leadQualityScore;
  // 0-100
  @override
  @JsonKey()
  final double conversionRate;
  @override
  final PaymentStatus paymentStatus;
  @override
  final DateTime? paidAt;

  @override
  String toString() {
    return 'MonthlyPerformance(id: $id, year: $year, month: $month, totalCalls: $totalCalls, connectedCalls: $connectedCalls, totalTalkTimeMinutes: $totalTalkTimeMinutes, validLeads: $validLeads, siteVisits: $siteVisits, bookings: $bookings, totalRevenue: $totalRevenue, baseSalary: $baseSalary, commissionEarned: $commissionEarned, incentives: $incentives, deductions: $deductions, totalEarnings: $totalEarnings, targetAchievementPercentage: $targetAchievementPercentage, ranking: $ranking, avgCallsPerDay: $avgCallsPerDay, avgTalkTimePerDay: $avgTalkTimePerDay, avgLeadsPerDay: $avgLeadsPerDay, leadQualityScore: $leadQualityScore, conversionRate: $conversionRate, paymentStatus: $paymentStatus, paidAt: $paidAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$MonthlyPerformanceImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.year, year) || other.year == year) &&
            (identical(other.month, month) || other.month == month) &&
            (identical(other.totalCalls, totalCalls) ||
                other.totalCalls == totalCalls) &&
            (identical(other.connectedCalls, connectedCalls) ||
                other.connectedCalls == connectedCalls) &&
            (identical(other.totalTalkTimeMinutes, totalTalkTimeMinutes) ||
                other.totalTalkTimeMinutes == totalTalkTimeMinutes) &&
            (identical(other.validLeads, validLeads) ||
                other.validLeads == validLeads) &&
            (identical(other.siteVisits, siteVisits) ||
                other.siteVisits == siteVisits) &&
            (identical(other.bookings, bookings) ||
                other.bookings == bookings) &&
            (identical(other.totalRevenue, totalRevenue) ||
                other.totalRevenue == totalRevenue) &&
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
            (identical(
                  other.targetAchievementPercentage,
                  targetAchievementPercentage,
                ) ||
                other.targetAchievementPercentage ==
                    targetAchievementPercentage) &&
            (identical(other.ranking, ranking) || other.ranking == ranking) &&
            (identical(other.avgCallsPerDay, avgCallsPerDay) ||
                other.avgCallsPerDay == avgCallsPerDay) &&
            (identical(other.avgTalkTimePerDay, avgTalkTimePerDay) ||
                other.avgTalkTimePerDay == avgTalkTimePerDay) &&
            (identical(other.avgLeadsPerDay, avgLeadsPerDay) ||
                other.avgLeadsPerDay == avgLeadsPerDay) &&
            (identical(other.leadQualityScore, leadQualityScore) ||
                other.leadQualityScore == leadQualityScore) &&
            (identical(other.conversionRate, conversionRate) ||
                other.conversionRate == conversionRate) &&
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
    totalCalls,
    connectedCalls,
    totalTalkTimeMinutes,
    validLeads,
    siteVisits,
    bookings,
    totalRevenue,
    baseSalary,
    commissionEarned,
    incentives,
    deductions,
    totalEarnings,
    targetAchievementPercentage,
    ranking,
    avgCallsPerDay,
    avgTalkTimePerDay,
    avgLeadsPerDay,
    leadQualityScore,
    conversionRate,
    paymentStatus,
    paidAt,
  ]);

  /// Create a copy of MonthlyPerformance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$MonthlyPerformanceImplCopyWith<_$MonthlyPerformanceImpl> get copyWith =>
      __$$MonthlyPerformanceImplCopyWithImpl<_$MonthlyPerformanceImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$MonthlyPerformanceImplToJson(this);
  }
}

abstract class _MonthlyPerformance implements MonthlyPerformance {
  const factory _MonthlyPerformance({
    required final String id,
    required final int year,
    required final int month,
    final int totalCalls,
    final int connectedCalls,
    final int totalTalkTimeMinutes,
    final int validLeads,
    final int siteVisits,
    final int bookings,
    final double totalRevenue,
    final double baseSalary,
    final double commissionEarned,
    final double incentives,
    final double deductions,
    final double totalEarnings,
    final double targetAchievementPercentage,
    final int ranking,
    final double avgCallsPerDay,
    final double avgTalkTimePerDay,
    final double avgLeadsPerDay,
    final double leadQualityScore,
    final double conversionRate,
    required final PaymentStatus paymentStatus,
    final DateTime? paidAt,
  }) = _$MonthlyPerformanceImpl;

  factory _MonthlyPerformance.fromJson(Map<String, dynamic> json) =
      _$MonthlyPerformanceImpl.fromJson;

  @override
  String get id;
  @override
  int get year;
  @override
  int get month; // Call Stats
  @override
  int get totalCalls;
  @override
  int get connectedCalls;
  @override
  int get totalTalkTimeMinutes; // Lead & Sales
  @override
  int get validLeads;
  @override
  int get siteVisits;
  @override
  int get bookings;
  @override
  double get totalRevenue; // Financial
  @override
  double get baseSalary;
  @override
  double get commissionEarned;
  @override
  double get incentives;
  @override
  double get deductions;
  @override
  double get totalEarnings; // Target Achievement
  @override
  double get targetAchievementPercentage;
  @override
  int get ranking; // Among all callers
  // Daily average
  @override
  double get avgCallsPerDay;
  @override
  double get avgTalkTimePerDay;
  @override
  double get avgLeadsPerDay; // Quality metrics
  @override
  double get leadQualityScore; // 0-100
  @override
  double get conversionRate;
  @override
  PaymentStatus get paymentStatus;
  @override
  DateTime? get paidAt;

  /// Create a copy of MonthlyPerformance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$MonthlyPerformanceImplCopyWith<_$MonthlyPerformanceImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

CallerLeadAssignment _$CallerLeadAssignmentFromJson(Map<String, dynamic> json) {
  return _CallerLeadAssignment.fromJson(json);
}

/// @nodoc
mixin _$CallerLeadAssignment {
  String get leadId => throw _privateConstructorUsedError;
  String get leadName => throw _privateConstructorUsedError;
  String get leadPhone => throw _privateConstructorUsedError;
  DateTime get assignedAt => throw _privateConstructorUsedError;
  String get assignedBy => throw _privateConstructorUsedError;
  AssignmentPriority? get priority =>
      throw _privateConstructorUsedError; // High, Medium, Low
  DateTime? get dueDate => throw _privateConstructorUsedError;
  List<String> get tags => throw _privateConstructorUsedError;
  String? get notes => throw _privateConstructorUsedError;
  bool get isCompleted => throw _privateConstructorUsedError;
  DateTime? get completedAt => throw _privateConstructorUsedError;
  String? get outcome => throw _privateConstructorUsedError;

  /// Serializes this CallerLeadAssignment to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of CallerLeadAssignment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $CallerLeadAssignmentCopyWith<CallerLeadAssignment> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $CallerLeadAssignmentCopyWith<$Res> {
  factory $CallerLeadAssignmentCopyWith(
    CallerLeadAssignment value,
    $Res Function(CallerLeadAssignment) then,
  ) = _$CallerLeadAssignmentCopyWithImpl<$Res, CallerLeadAssignment>;
  @useResult
  $Res call({
    String leadId,
    String leadName,
    String leadPhone,
    DateTime assignedAt,
    String assignedBy,
    AssignmentPriority? priority,
    DateTime? dueDate,
    List<String> tags,
    String? notes,
    bool isCompleted,
    DateTime? completedAt,
    String? outcome,
  });
}

/// @nodoc
class _$CallerLeadAssignmentCopyWithImpl<
  $Res,
  $Val extends CallerLeadAssignment
>
    implements $CallerLeadAssignmentCopyWith<$Res> {
  _$CallerLeadAssignmentCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of CallerLeadAssignment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? leadId = null,
    Object? leadName = null,
    Object? leadPhone = null,
    Object? assignedAt = null,
    Object? assignedBy = null,
    Object? priority = freezed,
    Object? dueDate = freezed,
    Object? tags = null,
    Object? notes = freezed,
    Object? isCompleted = null,
    Object? completedAt = freezed,
    Object? outcome = freezed,
  }) {
    return _then(
      _value.copyWith(
            leadId: null == leadId
                ? _value.leadId
                : leadId // ignore: cast_nullable_to_non_nullable
                      as String,
            leadName: null == leadName
                ? _value.leadName
                : leadName // ignore: cast_nullable_to_non_nullable
                      as String,
            leadPhone: null == leadPhone
                ? _value.leadPhone
                : leadPhone // ignore: cast_nullable_to_non_nullable
                      as String,
            assignedAt: null == assignedAt
                ? _value.assignedAt
                : assignedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            assignedBy: null == assignedBy
                ? _value.assignedBy
                : assignedBy // ignore: cast_nullable_to_non_nullable
                      as String,
            priority: freezed == priority
                ? _value.priority
                : priority // ignore: cast_nullable_to_non_nullable
                      as AssignmentPriority?,
            dueDate: freezed == dueDate
                ? _value.dueDate
                : dueDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            tags: null == tags
                ? _value.tags
                : tags // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            notes: freezed == notes
                ? _value.notes
                : notes // ignore: cast_nullable_to_non_nullable
                      as String?,
            isCompleted: null == isCompleted
                ? _value.isCompleted
                : isCompleted // ignore: cast_nullable_to_non_nullable
                      as bool,
            completedAt: freezed == completedAt
                ? _value.completedAt
                : completedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            outcome: freezed == outcome
                ? _value.outcome
                : outcome // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$CallerLeadAssignmentImplCopyWith<$Res>
    implements $CallerLeadAssignmentCopyWith<$Res> {
  factory _$$CallerLeadAssignmentImplCopyWith(
    _$CallerLeadAssignmentImpl value,
    $Res Function(_$CallerLeadAssignmentImpl) then,
  ) = __$$CallerLeadAssignmentImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String leadId,
    String leadName,
    String leadPhone,
    DateTime assignedAt,
    String assignedBy,
    AssignmentPriority? priority,
    DateTime? dueDate,
    List<String> tags,
    String? notes,
    bool isCompleted,
    DateTime? completedAt,
    String? outcome,
  });
}

/// @nodoc
class __$$CallerLeadAssignmentImplCopyWithImpl<$Res>
    extends _$CallerLeadAssignmentCopyWithImpl<$Res, _$CallerLeadAssignmentImpl>
    implements _$$CallerLeadAssignmentImplCopyWith<$Res> {
  __$$CallerLeadAssignmentImplCopyWithImpl(
    _$CallerLeadAssignmentImpl _value,
    $Res Function(_$CallerLeadAssignmentImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of CallerLeadAssignment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? leadId = null,
    Object? leadName = null,
    Object? leadPhone = null,
    Object? assignedAt = null,
    Object? assignedBy = null,
    Object? priority = freezed,
    Object? dueDate = freezed,
    Object? tags = null,
    Object? notes = freezed,
    Object? isCompleted = null,
    Object? completedAt = freezed,
    Object? outcome = freezed,
  }) {
    return _then(
      _$CallerLeadAssignmentImpl(
        leadId: null == leadId
            ? _value.leadId
            : leadId // ignore: cast_nullable_to_non_nullable
                  as String,
        leadName: null == leadName
            ? _value.leadName
            : leadName // ignore: cast_nullable_to_non_nullable
                  as String,
        leadPhone: null == leadPhone
            ? _value.leadPhone
            : leadPhone // ignore: cast_nullable_to_non_nullable
                  as String,
        assignedAt: null == assignedAt
            ? _value.assignedAt
            : assignedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        assignedBy: null == assignedBy
            ? _value.assignedBy
            : assignedBy // ignore: cast_nullable_to_non_nullable
                  as String,
        priority: freezed == priority
            ? _value.priority
            : priority // ignore: cast_nullable_to_non_nullable
                  as AssignmentPriority?,
        dueDate: freezed == dueDate
            ? _value.dueDate
            : dueDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        tags: null == tags
            ? _value._tags
            : tags // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        notes: freezed == notes
            ? _value.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        isCompleted: null == isCompleted
            ? _value.isCompleted
            : isCompleted // ignore: cast_nullable_to_non_nullable
                  as bool,
        completedAt: freezed == completedAt
            ? _value.completedAt
            : completedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        outcome: freezed == outcome
            ? _value.outcome
            : outcome // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$CallerLeadAssignmentImpl implements _CallerLeadAssignment {
  const _$CallerLeadAssignmentImpl({
    required this.leadId,
    required this.leadName,
    required this.leadPhone,
    required this.assignedAt,
    required this.assignedBy,
    this.priority,
    this.dueDate,
    final List<String> tags = const [],
    this.notes,
    this.isCompleted = false,
    this.completedAt,
    this.outcome,
  }) : _tags = tags;

  factory _$CallerLeadAssignmentImpl.fromJson(Map<String, dynamic> json) =>
      _$$CallerLeadAssignmentImplFromJson(json);

  @override
  final String leadId;
  @override
  final String leadName;
  @override
  final String leadPhone;
  @override
  final DateTime assignedAt;
  @override
  final String assignedBy;
  @override
  final AssignmentPriority? priority;
  // High, Medium, Low
  @override
  final DateTime? dueDate;
  final List<String> _tags;
  @override
  @JsonKey()
  List<String> get tags {
    if (_tags is EqualUnmodifiableListView) return _tags;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_tags);
  }

  @override
  final String? notes;
  @override
  @JsonKey()
  final bool isCompleted;
  @override
  final DateTime? completedAt;
  @override
  final String? outcome;

  @override
  String toString() {
    return 'CallerLeadAssignment(leadId: $leadId, leadName: $leadName, leadPhone: $leadPhone, assignedAt: $assignedAt, assignedBy: $assignedBy, priority: $priority, dueDate: $dueDate, tags: $tags, notes: $notes, isCompleted: $isCompleted, completedAt: $completedAt, outcome: $outcome)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$CallerLeadAssignmentImpl &&
            (identical(other.leadId, leadId) || other.leadId == leadId) &&
            (identical(other.leadName, leadName) ||
                other.leadName == leadName) &&
            (identical(other.leadPhone, leadPhone) ||
                other.leadPhone == leadPhone) &&
            (identical(other.assignedAt, assignedAt) ||
                other.assignedAt == assignedAt) &&
            (identical(other.assignedBy, assignedBy) ||
                other.assignedBy == assignedBy) &&
            (identical(other.priority, priority) ||
                other.priority == priority) &&
            (identical(other.dueDate, dueDate) || other.dueDate == dueDate) &&
            const DeepCollectionEquality().equals(other._tags, _tags) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            (identical(other.isCompleted, isCompleted) ||
                other.isCompleted == isCompleted) &&
            (identical(other.completedAt, completedAt) ||
                other.completedAt == completedAt) &&
            (identical(other.outcome, outcome) || other.outcome == outcome));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    leadId,
    leadName,
    leadPhone,
    assignedAt,
    assignedBy,
    priority,
    dueDate,
    const DeepCollectionEquality().hash(_tags),
    notes,
    isCompleted,
    completedAt,
    outcome,
  );

  /// Create a copy of CallerLeadAssignment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$CallerLeadAssignmentImplCopyWith<_$CallerLeadAssignmentImpl>
  get copyWith =>
      __$$CallerLeadAssignmentImplCopyWithImpl<_$CallerLeadAssignmentImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$CallerLeadAssignmentImplToJson(this);
  }
}

abstract class _CallerLeadAssignment implements CallerLeadAssignment {
  const factory _CallerLeadAssignment({
    required final String leadId,
    required final String leadName,
    required final String leadPhone,
    required final DateTime assignedAt,
    required final String assignedBy,
    final AssignmentPriority? priority,
    final DateTime? dueDate,
    final List<String> tags,
    final String? notes,
    final bool isCompleted,
    final DateTime? completedAt,
    final String? outcome,
  }) = _$CallerLeadAssignmentImpl;

  factory _CallerLeadAssignment.fromJson(Map<String, dynamic> json) =
      _$CallerLeadAssignmentImpl.fromJson;

  @override
  String get leadId;
  @override
  String get leadName;
  @override
  String get leadPhone;
  @override
  DateTime get assignedAt;
  @override
  String get assignedBy;
  @override
  AssignmentPriority? get priority; // High, Medium, Low
  @override
  DateTime? get dueDate;
  @override
  List<String> get tags;
  @override
  String? get notes;
  @override
  bool get isCompleted;
  @override
  DateTime? get completedAt;
  @override
  String? get outcome;

  /// Create a copy of CallerLeadAssignment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$CallerLeadAssignmentImplCopyWith<_$CallerLeadAssignmentImpl>
  get copyWith => throw _privateConstructorUsedError;
}

LeadDistributionBatch _$LeadDistributionBatchFromJson(
  Map<String, dynamic> json,
) {
  return _LeadDistributionBatch.fromJson(json);
}

/// @nodoc
mixin _$LeadDistributionBatch {
  String get id => throw _privateConstructorUsedError;
  String get batchName => throw _privateConstructorUsedError;
  DateTime get createdAt => throw _privateConstructorUsedError;
  String get createdBy => throw _privateConstructorUsedError; // Lead Sources
  List<String> get leadSourceIds =>
      throw _privateConstructorUsedError; // From campaigns, website, etc.
  List<Map<String, dynamic>> get importedLeads =>
      throw _privateConstructorUsedError; // Distribution
  List<String> get assignedCallerIds => throw _privateConstructorUsedError;
  int? get leadsPerCaller => throw _privateConstructorUsedError;
  DistributionMethod get method =>
      throw _privateConstructorUsedError; // Equal, PriorityBased, Random, RoundRobin
  // Status
  DistributionStatus get status => throw _privateConstructorUsedError;
  DateTime? get distributedAt => throw _privateConstructorUsedError; // Results
  List<String> get distributedLeadIds => throw _privateConstructorUsedError;
  int get totalLeads => throw _privateConstructorUsedError;

  /// Serializes this LeadDistributionBatch to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of LeadDistributionBatch
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $LeadDistributionBatchCopyWith<LeadDistributionBatch> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $LeadDistributionBatchCopyWith<$Res> {
  factory $LeadDistributionBatchCopyWith(
    LeadDistributionBatch value,
    $Res Function(LeadDistributionBatch) then,
  ) = _$LeadDistributionBatchCopyWithImpl<$Res, LeadDistributionBatch>;
  @useResult
  $Res call({
    String id,
    String batchName,
    DateTime createdAt,
    String createdBy,
    List<String> leadSourceIds,
    List<Map<String, dynamic>> importedLeads,
    List<String> assignedCallerIds,
    int? leadsPerCaller,
    DistributionMethod method,
    DistributionStatus status,
    DateTime? distributedAt,
    List<String> distributedLeadIds,
    int totalLeads,
  });
}

/// @nodoc
class _$LeadDistributionBatchCopyWithImpl<
  $Res,
  $Val extends LeadDistributionBatch
>
    implements $LeadDistributionBatchCopyWith<$Res> {
  _$LeadDistributionBatchCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of LeadDistributionBatch
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? batchName = null,
    Object? createdAt = null,
    Object? createdBy = null,
    Object? leadSourceIds = null,
    Object? importedLeads = null,
    Object? assignedCallerIds = null,
    Object? leadsPerCaller = freezed,
    Object? method = null,
    Object? status = null,
    Object? distributedAt = freezed,
    Object? distributedLeadIds = null,
    Object? totalLeads = null,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            batchName: null == batchName
                ? _value.batchName
                : batchName // ignore: cast_nullable_to_non_nullable
                      as String,
            createdAt: null == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            createdBy: null == createdBy
                ? _value.createdBy
                : createdBy // ignore: cast_nullable_to_non_nullable
                      as String,
            leadSourceIds: null == leadSourceIds
                ? _value.leadSourceIds
                : leadSourceIds // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            importedLeads: null == importedLeads
                ? _value.importedLeads
                : importedLeads // ignore: cast_nullable_to_non_nullable
                      as List<Map<String, dynamic>>,
            assignedCallerIds: null == assignedCallerIds
                ? _value.assignedCallerIds
                : assignedCallerIds // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            leadsPerCaller: freezed == leadsPerCaller
                ? _value.leadsPerCaller
                : leadsPerCaller // ignore: cast_nullable_to_non_nullable
                      as int?,
            method: null == method
                ? _value.method
                : method // ignore: cast_nullable_to_non_nullable
                      as DistributionMethod,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as DistributionStatus,
            distributedAt: freezed == distributedAt
                ? _value.distributedAt
                : distributedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            distributedLeadIds: null == distributedLeadIds
                ? _value.distributedLeadIds
                : distributedLeadIds // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            totalLeads: null == totalLeads
                ? _value.totalLeads
                : totalLeads // ignore: cast_nullable_to_non_nullable
                      as int,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$LeadDistributionBatchImplCopyWith<$Res>
    implements $LeadDistributionBatchCopyWith<$Res> {
  factory _$$LeadDistributionBatchImplCopyWith(
    _$LeadDistributionBatchImpl value,
    $Res Function(_$LeadDistributionBatchImpl) then,
  ) = __$$LeadDistributionBatchImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String batchName,
    DateTime createdAt,
    String createdBy,
    List<String> leadSourceIds,
    List<Map<String, dynamic>> importedLeads,
    List<String> assignedCallerIds,
    int? leadsPerCaller,
    DistributionMethod method,
    DistributionStatus status,
    DateTime? distributedAt,
    List<String> distributedLeadIds,
    int totalLeads,
  });
}

/// @nodoc
class __$$LeadDistributionBatchImplCopyWithImpl<$Res>
    extends
        _$LeadDistributionBatchCopyWithImpl<$Res, _$LeadDistributionBatchImpl>
    implements _$$LeadDistributionBatchImplCopyWith<$Res> {
  __$$LeadDistributionBatchImplCopyWithImpl(
    _$LeadDistributionBatchImpl _value,
    $Res Function(_$LeadDistributionBatchImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of LeadDistributionBatch
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? batchName = null,
    Object? createdAt = null,
    Object? createdBy = null,
    Object? leadSourceIds = null,
    Object? importedLeads = null,
    Object? assignedCallerIds = null,
    Object? leadsPerCaller = freezed,
    Object? method = null,
    Object? status = null,
    Object? distributedAt = freezed,
    Object? distributedLeadIds = null,
    Object? totalLeads = null,
  }) {
    return _then(
      _$LeadDistributionBatchImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        batchName: null == batchName
            ? _value.batchName
            : batchName // ignore: cast_nullable_to_non_nullable
                  as String,
        createdAt: null == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        createdBy: null == createdBy
            ? _value.createdBy
            : createdBy // ignore: cast_nullable_to_non_nullable
                  as String,
        leadSourceIds: null == leadSourceIds
            ? _value._leadSourceIds
            : leadSourceIds // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        importedLeads: null == importedLeads
            ? _value._importedLeads
            : importedLeads // ignore: cast_nullable_to_non_nullable
                  as List<Map<String, dynamic>>,
        assignedCallerIds: null == assignedCallerIds
            ? _value._assignedCallerIds
            : assignedCallerIds // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        leadsPerCaller: freezed == leadsPerCaller
            ? _value.leadsPerCaller
            : leadsPerCaller // ignore: cast_nullable_to_non_nullable
                  as int?,
        method: null == method
            ? _value.method
            : method // ignore: cast_nullable_to_non_nullable
                  as DistributionMethod,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as DistributionStatus,
        distributedAt: freezed == distributedAt
            ? _value.distributedAt
            : distributedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        distributedLeadIds: null == distributedLeadIds
            ? _value._distributedLeadIds
            : distributedLeadIds // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        totalLeads: null == totalLeads
            ? _value.totalLeads
            : totalLeads // ignore: cast_nullable_to_non_nullable
                  as int,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$LeadDistributionBatchImpl implements _LeadDistributionBatch {
  const _$LeadDistributionBatchImpl({
    required this.id,
    required this.batchName,
    required this.createdAt,
    required this.createdBy,
    final List<String> leadSourceIds = const [],
    final List<Map<String, dynamic>> importedLeads = const [],
    final List<String> assignedCallerIds = const [],
    this.leadsPerCaller,
    this.method = DistributionMethod.equal,
    required this.status,
    this.distributedAt,
    final List<String> distributedLeadIds = const [],
    this.totalLeads = 0,
  }) : _leadSourceIds = leadSourceIds,
       _importedLeads = importedLeads,
       _assignedCallerIds = assignedCallerIds,
       _distributedLeadIds = distributedLeadIds;

  factory _$LeadDistributionBatchImpl.fromJson(Map<String, dynamic> json) =>
      _$$LeadDistributionBatchImplFromJson(json);

  @override
  final String id;
  @override
  final String batchName;
  @override
  final DateTime createdAt;
  @override
  final String createdBy;
  // Lead Sources
  final List<String> _leadSourceIds;
  // Lead Sources
  @override
  @JsonKey()
  List<String> get leadSourceIds {
    if (_leadSourceIds is EqualUnmodifiableListView) return _leadSourceIds;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_leadSourceIds);
  }

  // From campaigns, website, etc.
  final List<Map<String, dynamic>> _importedLeads;
  // From campaigns, website, etc.
  @override
  @JsonKey()
  List<Map<String, dynamic>> get importedLeads {
    if (_importedLeads is EqualUnmodifiableListView) return _importedLeads;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_importedLeads);
  }

  // Distribution
  final List<String> _assignedCallerIds;
  // Distribution
  @override
  @JsonKey()
  List<String> get assignedCallerIds {
    if (_assignedCallerIds is EqualUnmodifiableListView)
      return _assignedCallerIds;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_assignedCallerIds);
  }

  @override
  final int? leadsPerCaller;
  @override
  @JsonKey()
  final DistributionMethod method;
  // Equal, PriorityBased, Random, RoundRobin
  // Status
  @override
  final DistributionStatus status;
  @override
  final DateTime? distributedAt;
  // Results
  final List<String> _distributedLeadIds;
  // Results
  @override
  @JsonKey()
  List<String> get distributedLeadIds {
    if (_distributedLeadIds is EqualUnmodifiableListView)
      return _distributedLeadIds;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_distributedLeadIds);
  }

  @override
  @JsonKey()
  final int totalLeads;

  @override
  String toString() {
    return 'LeadDistributionBatch(id: $id, batchName: $batchName, createdAt: $createdAt, createdBy: $createdBy, leadSourceIds: $leadSourceIds, importedLeads: $importedLeads, assignedCallerIds: $assignedCallerIds, leadsPerCaller: $leadsPerCaller, method: $method, status: $status, distributedAt: $distributedAt, distributedLeadIds: $distributedLeadIds, totalLeads: $totalLeads)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$LeadDistributionBatchImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.batchName, batchName) ||
                other.batchName == batchName) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.createdBy, createdBy) ||
                other.createdBy == createdBy) &&
            const DeepCollectionEquality().equals(
              other._leadSourceIds,
              _leadSourceIds,
            ) &&
            const DeepCollectionEquality().equals(
              other._importedLeads,
              _importedLeads,
            ) &&
            const DeepCollectionEquality().equals(
              other._assignedCallerIds,
              _assignedCallerIds,
            ) &&
            (identical(other.leadsPerCaller, leadsPerCaller) ||
                other.leadsPerCaller == leadsPerCaller) &&
            (identical(other.method, method) || other.method == method) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.distributedAt, distributedAt) ||
                other.distributedAt == distributedAt) &&
            const DeepCollectionEquality().equals(
              other._distributedLeadIds,
              _distributedLeadIds,
            ) &&
            (identical(other.totalLeads, totalLeads) ||
                other.totalLeads == totalLeads));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    batchName,
    createdAt,
    createdBy,
    const DeepCollectionEquality().hash(_leadSourceIds),
    const DeepCollectionEquality().hash(_importedLeads),
    const DeepCollectionEquality().hash(_assignedCallerIds),
    leadsPerCaller,
    method,
    status,
    distributedAt,
    const DeepCollectionEquality().hash(_distributedLeadIds),
    totalLeads,
  );

  /// Create a copy of LeadDistributionBatch
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$LeadDistributionBatchImplCopyWith<_$LeadDistributionBatchImpl>
  get copyWith =>
      __$$LeadDistributionBatchImplCopyWithImpl<_$LeadDistributionBatchImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$LeadDistributionBatchImplToJson(this);
  }
}

abstract class _LeadDistributionBatch implements LeadDistributionBatch {
  const factory _LeadDistributionBatch({
    required final String id,
    required final String batchName,
    required final DateTime createdAt,
    required final String createdBy,
    final List<String> leadSourceIds,
    final List<Map<String, dynamic>> importedLeads,
    final List<String> assignedCallerIds,
    final int? leadsPerCaller,
    final DistributionMethod method,
    required final DistributionStatus status,
    final DateTime? distributedAt,
    final List<String> distributedLeadIds,
    final int totalLeads,
  }) = _$LeadDistributionBatchImpl;

  factory _LeadDistributionBatch.fromJson(Map<String, dynamic> json) =
      _$LeadDistributionBatchImpl.fromJson;

  @override
  String get id;
  @override
  String get batchName;
  @override
  DateTime get createdAt;
  @override
  String get createdBy; // Lead Sources
  @override
  List<String> get leadSourceIds; // From campaigns, website, etc.
  @override
  List<Map<String, dynamic>> get importedLeads; // Distribution
  @override
  List<String> get assignedCallerIds;
  @override
  int? get leadsPerCaller;
  @override
  DistributionMethod get method; // Equal, PriorityBased, Random, RoundRobin
  // Status
  @override
  DistributionStatus get status;
  @override
  DateTime? get distributedAt; // Results
  @override
  List<String> get distributedLeadIds;
  @override
  int get totalLeads;

  /// Create a copy of LeadDistributionBatch
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$LeadDistributionBatchImplCopyWith<_$LeadDistributionBatchImpl>
  get copyWith => throw _privateConstructorUsedError;
}
