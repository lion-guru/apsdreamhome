// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'daily_caller_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$DailyCaller {

 String get id; String get name; String get phone; String get email; String? get photoUrl;// Employment Details
 String get employeeId; DateTime get joiningDate; CallerType get callerType;// FullTime, PartTime, Freelance
 SalaryType get salaryType;// Fixed, CommissionOnly, FixedPlusCommission
// Salary Structure
 double get monthlySalary; double? get dailyTargetAmount;// Sales target per day
 int? get dailyCallTarget;// Minimum calls per day
 int? get dailyTalkTimeTarget;// Minutes per day
// Commission Structure
 double? get commissionPerLead;// Per valid lead
 double? get commissionPerBooking;// Per booking conversion
 double? get commissionPercentage;// % of booking value
// Performance Tracking
 List<DailyCallReport> get dailyReports; List<MonthlyPerformance> get monthlyReports;// Current Month Stats (Auto-calculated)
 int get currentMonthCalls; int get currentMonthConnected; int get currentMonthValidLeads; int get currentMonthBookings; double get currentMonthRevenue; double get currentMonthCommission; int get currentMonthTalkTimeMinutes;// Assigned Leads
 List<String> get assignedLeadIds; List<CallerLeadAssignment> get leadAssignments;// Status
 CallerStatus get status;// Active, OnLeave, Suspended, Terminated
 DateTime? get lastActiveAt;// Admin Notes
 String? get adminNotes; List<String> get performanceWarnings; DateTime get createdAt; DateTime get updatedAt;
/// Create a copy of DailyCaller
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DailyCallerCopyWith<DailyCaller> get copyWith => _$DailyCallerCopyWithImpl<DailyCaller>(this as DailyCaller, _$identity);

  /// Serializes this DailyCaller to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DailyCaller&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.email, email) || other.email == email)&&(identical(other.photoUrl, photoUrl) || other.photoUrl == photoUrl)&&(identical(other.employeeId, employeeId) || other.employeeId == employeeId)&&(identical(other.joiningDate, joiningDate) || other.joiningDate == joiningDate)&&(identical(other.callerType, callerType) || other.callerType == callerType)&&(identical(other.salaryType, salaryType) || other.salaryType == salaryType)&&(identical(other.monthlySalary, monthlySalary) || other.monthlySalary == monthlySalary)&&(identical(other.dailyTargetAmount, dailyTargetAmount) || other.dailyTargetAmount == dailyTargetAmount)&&(identical(other.dailyCallTarget, dailyCallTarget) || other.dailyCallTarget == dailyCallTarget)&&(identical(other.dailyTalkTimeTarget, dailyTalkTimeTarget) || other.dailyTalkTimeTarget == dailyTalkTimeTarget)&&(identical(other.commissionPerLead, commissionPerLead) || other.commissionPerLead == commissionPerLead)&&(identical(other.commissionPerBooking, commissionPerBooking) || other.commissionPerBooking == commissionPerBooking)&&(identical(other.commissionPercentage, commissionPercentage) || other.commissionPercentage == commissionPercentage)&&const DeepCollectionEquality().equals(other.dailyReports, dailyReports)&&const DeepCollectionEquality().equals(other.monthlyReports, monthlyReports)&&(identical(other.currentMonthCalls, currentMonthCalls) || other.currentMonthCalls == currentMonthCalls)&&(identical(other.currentMonthConnected, currentMonthConnected) || other.currentMonthConnected == currentMonthConnected)&&(identical(other.currentMonthValidLeads, currentMonthValidLeads) || other.currentMonthValidLeads == currentMonthValidLeads)&&(identical(other.currentMonthBookings, currentMonthBookings) || other.currentMonthBookings == currentMonthBookings)&&(identical(other.currentMonthRevenue, currentMonthRevenue) || other.currentMonthRevenue == currentMonthRevenue)&&(identical(other.currentMonthCommission, currentMonthCommission) || other.currentMonthCommission == currentMonthCommission)&&(identical(other.currentMonthTalkTimeMinutes, currentMonthTalkTimeMinutes) || other.currentMonthTalkTimeMinutes == currentMonthTalkTimeMinutes)&&const DeepCollectionEquality().equals(other.assignedLeadIds, assignedLeadIds)&&const DeepCollectionEquality().equals(other.leadAssignments, leadAssignments)&&(identical(other.status, status) || other.status == status)&&(identical(other.lastActiveAt, lastActiveAt) || other.lastActiveAt == lastActiveAt)&&(identical(other.adminNotes, adminNotes) || other.adminNotes == adminNotes)&&const DeepCollectionEquality().equals(other.performanceWarnings, performanceWarnings)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,name,phone,email,photoUrl,employeeId,joiningDate,callerType,salaryType,monthlySalary,dailyTargetAmount,dailyCallTarget,dailyTalkTimeTarget,commissionPerLead,commissionPerBooking,commissionPercentage,const DeepCollectionEquality().hash(dailyReports),const DeepCollectionEquality().hash(monthlyReports),currentMonthCalls,currentMonthConnected,currentMonthValidLeads,currentMonthBookings,currentMonthRevenue,currentMonthCommission,currentMonthTalkTimeMinutes,const DeepCollectionEquality().hash(assignedLeadIds),const DeepCollectionEquality().hash(leadAssignments),status,lastActiveAt,adminNotes,const DeepCollectionEquality().hash(performanceWarnings),createdAt,updatedAt]);

@override
String toString() {
  return 'DailyCaller(id: $id, name: $name, phone: $phone, email: $email, photoUrl: $photoUrl, employeeId: $employeeId, joiningDate: $joiningDate, callerType: $callerType, salaryType: $salaryType, monthlySalary: $monthlySalary, dailyTargetAmount: $dailyTargetAmount, dailyCallTarget: $dailyCallTarget, dailyTalkTimeTarget: $dailyTalkTimeTarget, commissionPerLead: $commissionPerLead, commissionPerBooking: $commissionPerBooking, commissionPercentage: $commissionPercentage, dailyReports: $dailyReports, monthlyReports: $monthlyReports, currentMonthCalls: $currentMonthCalls, currentMonthConnected: $currentMonthConnected, currentMonthValidLeads: $currentMonthValidLeads, currentMonthBookings: $currentMonthBookings, currentMonthRevenue: $currentMonthRevenue, currentMonthCommission: $currentMonthCommission, currentMonthTalkTimeMinutes: $currentMonthTalkTimeMinutes, assignedLeadIds: $assignedLeadIds, leadAssignments: $leadAssignments, status: $status, lastActiveAt: $lastActiveAt, adminNotes: $adminNotes, performanceWarnings: $performanceWarnings, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class $DailyCallerCopyWith<$Res>  {
  factory $DailyCallerCopyWith(DailyCaller value, $Res Function(DailyCaller) _then) = _$DailyCallerCopyWithImpl;
@useResult
$Res call({
 String id, String name, String phone, String email, String? photoUrl, String employeeId, DateTime joiningDate, CallerType callerType, SalaryType salaryType, double monthlySalary, double? dailyTargetAmount, int? dailyCallTarget, int? dailyTalkTimeTarget, double? commissionPerLead, double? commissionPerBooking, double? commissionPercentage, List<DailyCallReport> dailyReports, List<MonthlyPerformance> monthlyReports, int currentMonthCalls, int currentMonthConnected, int currentMonthValidLeads, int currentMonthBookings, double currentMonthRevenue, double currentMonthCommission, int currentMonthTalkTimeMinutes, List<String> assignedLeadIds, List<CallerLeadAssignment> leadAssignments, CallerStatus status, DateTime? lastActiveAt, String? adminNotes, List<String> performanceWarnings, DateTime createdAt, DateTime updatedAt
});




}
/// @nodoc
class _$DailyCallerCopyWithImpl<$Res>
    implements $DailyCallerCopyWith<$Res> {
  _$DailyCallerCopyWithImpl(this._self, this._then);

  final DailyCaller _self;
  final $Res Function(DailyCaller) _then;

/// Create a copy of DailyCaller
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? phone = null,Object? email = null,Object? photoUrl = freezed,Object? employeeId = null,Object? joiningDate = null,Object? callerType = null,Object? salaryType = null,Object? monthlySalary = null,Object? dailyTargetAmount = freezed,Object? dailyCallTarget = freezed,Object? dailyTalkTimeTarget = freezed,Object? commissionPerLead = freezed,Object? commissionPerBooking = freezed,Object? commissionPercentage = freezed,Object? dailyReports = null,Object? monthlyReports = null,Object? currentMonthCalls = null,Object? currentMonthConnected = null,Object? currentMonthValidLeads = null,Object? currentMonthBookings = null,Object? currentMonthRevenue = null,Object? currentMonthCommission = null,Object? currentMonthTalkTimeMinutes = null,Object? assignedLeadIds = null,Object? leadAssignments = null,Object? status = null,Object? lastActiveAt = freezed,Object? adminNotes = freezed,Object? performanceWarnings = null,Object? createdAt = null,Object? updatedAt = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,photoUrl: freezed == photoUrl ? _self.photoUrl : photoUrl // ignore: cast_nullable_to_non_nullable
as String?,employeeId: null == employeeId ? _self.employeeId : employeeId // ignore: cast_nullable_to_non_nullable
as String,joiningDate: null == joiningDate ? _self.joiningDate : joiningDate // ignore: cast_nullable_to_non_nullable
as DateTime,callerType: null == callerType ? _self.callerType : callerType // ignore: cast_nullable_to_non_nullable
as CallerType,salaryType: null == salaryType ? _self.salaryType : salaryType // ignore: cast_nullable_to_non_nullable
as SalaryType,monthlySalary: null == monthlySalary ? _self.monthlySalary : monthlySalary // ignore: cast_nullable_to_non_nullable
as double,dailyTargetAmount: freezed == dailyTargetAmount ? _self.dailyTargetAmount : dailyTargetAmount // ignore: cast_nullable_to_non_nullable
as double?,dailyCallTarget: freezed == dailyCallTarget ? _self.dailyCallTarget : dailyCallTarget // ignore: cast_nullable_to_non_nullable
as int?,dailyTalkTimeTarget: freezed == dailyTalkTimeTarget ? _self.dailyTalkTimeTarget : dailyTalkTimeTarget // ignore: cast_nullable_to_non_nullable
as int?,commissionPerLead: freezed == commissionPerLead ? _self.commissionPerLead : commissionPerLead // ignore: cast_nullable_to_non_nullable
as double?,commissionPerBooking: freezed == commissionPerBooking ? _self.commissionPerBooking : commissionPerBooking // ignore: cast_nullable_to_non_nullable
as double?,commissionPercentage: freezed == commissionPercentage ? _self.commissionPercentage : commissionPercentage // ignore: cast_nullable_to_non_nullable
as double?,dailyReports: null == dailyReports ? _self.dailyReports : dailyReports // ignore: cast_nullable_to_non_nullable
as List<DailyCallReport>,monthlyReports: null == monthlyReports ? _self.monthlyReports : monthlyReports // ignore: cast_nullable_to_non_nullable
as List<MonthlyPerformance>,currentMonthCalls: null == currentMonthCalls ? _self.currentMonthCalls : currentMonthCalls // ignore: cast_nullable_to_non_nullable
as int,currentMonthConnected: null == currentMonthConnected ? _self.currentMonthConnected : currentMonthConnected // ignore: cast_nullable_to_non_nullable
as int,currentMonthValidLeads: null == currentMonthValidLeads ? _self.currentMonthValidLeads : currentMonthValidLeads // ignore: cast_nullable_to_non_nullable
as int,currentMonthBookings: null == currentMonthBookings ? _self.currentMonthBookings : currentMonthBookings // ignore: cast_nullable_to_non_nullable
as int,currentMonthRevenue: null == currentMonthRevenue ? _self.currentMonthRevenue : currentMonthRevenue // ignore: cast_nullable_to_non_nullable
as double,currentMonthCommission: null == currentMonthCommission ? _self.currentMonthCommission : currentMonthCommission // ignore: cast_nullable_to_non_nullable
as double,currentMonthTalkTimeMinutes: null == currentMonthTalkTimeMinutes ? _self.currentMonthTalkTimeMinutes : currentMonthTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
as int,assignedLeadIds: null == assignedLeadIds ? _self.assignedLeadIds : assignedLeadIds // ignore: cast_nullable_to_non_nullable
as List<String>,leadAssignments: null == leadAssignments ? _self.leadAssignments : leadAssignments // ignore: cast_nullable_to_non_nullable
as List<CallerLeadAssignment>,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as CallerStatus,lastActiveAt: freezed == lastActiveAt ? _self.lastActiveAt : lastActiveAt // ignore: cast_nullable_to_non_nullable
as DateTime?,adminNotes: freezed == adminNotes ? _self.adminNotes : adminNotes // ignore: cast_nullable_to_non_nullable
as String?,performanceWarnings: null == performanceWarnings ? _self.performanceWarnings : performanceWarnings // ignore: cast_nullable_to_non_nullable
as List<String>,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,updatedAt: null == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [DailyCaller].
extension DailyCallerPatterns on DailyCaller {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DailyCaller value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DailyCaller() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DailyCaller value)  $default,){
final _that = this;
switch (_that) {
case _DailyCaller():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DailyCaller value)?  $default,){
final _that = this;
switch (_that) {
case _DailyCaller() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String name,  String phone,  String email,  String? photoUrl,  String employeeId,  DateTime joiningDate,  CallerType callerType,  SalaryType salaryType,  double monthlySalary,  double? dailyTargetAmount,  int? dailyCallTarget,  int? dailyTalkTimeTarget,  double? commissionPerLead,  double? commissionPerBooking,  double? commissionPercentage,  List<DailyCallReport> dailyReports,  List<MonthlyPerformance> monthlyReports,  int currentMonthCalls,  int currentMonthConnected,  int currentMonthValidLeads,  int currentMonthBookings,  double currentMonthRevenue,  double currentMonthCommission,  int currentMonthTalkTimeMinutes,  List<String> assignedLeadIds,  List<CallerLeadAssignment> leadAssignments,  CallerStatus status,  DateTime? lastActiveAt,  String? adminNotes,  List<String> performanceWarnings,  DateTime createdAt,  DateTime updatedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DailyCaller() when $default != null:
return $default(_that.id,_that.name,_that.phone,_that.email,_that.photoUrl,_that.employeeId,_that.joiningDate,_that.callerType,_that.salaryType,_that.monthlySalary,_that.dailyTargetAmount,_that.dailyCallTarget,_that.dailyTalkTimeTarget,_that.commissionPerLead,_that.commissionPerBooking,_that.commissionPercentage,_that.dailyReports,_that.monthlyReports,_that.currentMonthCalls,_that.currentMonthConnected,_that.currentMonthValidLeads,_that.currentMonthBookings,_that.currentMonthRevenue,_that.currentMonthCommission,_that.currentMonthTalkTimeMinutes,_that.assignedLeadIds,_that.leadAssignments,_that.status,_that.lastActiveAt,_that.adminNotes,_that.performanceWarnings,_that.createdAt,_that.updatedAt);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String name,  String phone,  String email,  String? photoUrl,  String employeeId,  DateTime joiningDate,  CallerType callerType,  SalaryType salaryType,  double monthlySalary,  double? dailyTargetAmount,  int? dailyCallTarget,  int? dailyTalkTimeTarget,  double? commissionPerLead,  double? commissionPerBooking,  double? commissionPercentage,  List<DailyCallReport> dailyReports,  List<MonthlyPerformance> monthlyReports,  int currentMonthCalls,  int currentMonthConnected,  int currentMonthValidLeads,  int currentMonthBookings,  double currentMonthRevenue,  double currentMonthCommission,  int currentMonthTalkTimeMinutes,  List<String> assignedLeadIds,  List<CallerLeadAssignment> leadAssignments,  CallerStatus status,  DateTime? lastActiveAt,  String? adminNotes,  List<String> performanceWarnings,  DateTime createdAt,  DateTime updatedAt)  $default,) {final _that = this;
switch (_that) {
case _DailyCaller():
return $default(_that.id,_that.name,_that.phone,_that.email,_that.photoUrl,_that.employeeId,_that.joiningDate,_that.callerType,_that.salaryType,_that.monthlySalary,_that.dailyTargetAmount,_that.dailyCallTarget,_that.dailyTalkTimeTarget,_that.commissionPerLead,_that.commissionPerBooking,_that.commissionPercentage,_that.dailyReports,_that.monthlyReports,_that.currentMonthCalls,_that.currentMonthConnected,_that.currentMonthValidLeads,_that.currentMonthBookings,_that.currentMonthRevenue,_that.currentMonthCommission,_that.currentMonthTalkTimeMinutes,_that.assignedLeadIds,_that.leadAssignments,_that.status,_that.lastActiveAt,_that.adminNotes,_that.performanceWarnings,_that.createdAt,_that.updatedAt);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String name,  String phone,  String email,  String? photoUrl,  String employeeId,  DateTime joiningDate,  CallerType callerType,  SalaryType salaryType,  double monthlySalary,  double? dailyTargetAmount,  int? dailyCallTarget,  int? dailyTalkTimeTarget,  double? commissionPerLead,  double? commissionPerBooking,  double? commissionPercentage,  List<DailyCallReport> dailyReports,  List<MonthlyPerformance> monthlyReports,  int currentMonthCalls,  int currentMonthConnected,  int currentMonthValidLeads,  int currentMonthBookings,  double currentMonthRevenue,  double currentMonthCommission,  int currentMonthTalkTimeMinutes,  List<String> assignedLeadIds,  List<CallerLeadAssignment> leadAssignments,  CallerStatus status,  DateTime? lastActiveAt,  String? adminNotes,  List<String> performanceWarnings,  DateTime createdAt,  DateTime updatedAt)?  $default,) {final _that = this;
switch (_that) {
case _DailyCaller() when $default != null:
return $default(_that.id,_that.name,_that.phone,_that.email,_that.photoUrl,_that.employeeId,_that.joiningDate,_that.callerType,_that.salaryType,_that.monthlySalary,_that.dailyTargetAmount,_that.dailyCallTarget,_that.dailyTalkTimeTarget,_that.commissionPerLead,_that.commissionPerBooking,_that.commissionPercentage,_that.dailyReports,_that.monthlyReports,_that.currentMonthCalls,_that.currentMonthConnected,_that.currentMonthValidLeads,_that.currentMonthBookings,_that.currentMonthRevenue,_that.currentMonthCommission,_that.currentMonthTalkTimeMinutes,_that.assignedLeadIds,_that.leadAssignments,_that.status,_that.lastActiveAt,_that.adminNotes,_that.performanceWarnings,_that.createdAt,_that.updatedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _DailyCaller extends DailyCaller {
  const _DailyCaller({this.id = '', this.name = '', this.phone = '', this.email = '', this.photoUrl, this.employeeId = '', required this.joiningDate, required this.callerType, required this.salaryType, this.monthlySalary = 0.0, this.dailyTargetAmount, this.dailyCallTarget, this.dailyTalkTimeTarget, this.commissionPerLead, this.commissionPerBooking, this.commissionPercentage, final  List<DailyCallReport> dailyReports = const [], final  List<MonthlyPerformance> monthlyReports = const [], this.currentMonthCalls = 0, this.currentMonthConnected = 0, this.currentMonthValidLeads = 0, this.currentMonthBookings = 0, this.currentMonthRevenue = 0.0, this.currentMonthCommission = 0.0, this.currentMonthTalkTimeMinutes = 0, final  List<String> assignedLeadIds = const [], final  List<CallerLeadAssignment> leadAssignments = const [], required this.status, this.lastActiveAt, this.adminNotes, final  List<String> performanceWarnings = const [], required this.createdAt, required this.updatedAt}): _dailyReports = dailyReports,_monthlyReports = monthlyReports,_assignedLeadIds = assignedLeadIds,_leadAssignments = leadAssignments,_performanceWarnings = performanceWarnings,super._();
  factory _DailyCaller.fromJson(Map<String, dynamic> json) => _$DailyCallerFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String name;
@override@JsonKey() final  String phone;
@override@JsonKey() final  String email;
@override final  String? photoUrl;
// Employment Details
@override@JsonKey() final  String employeeId;
@override final  DateTime joiningDate;
@override final  CallerType callerType;
// FullTime, PartTime, Freelance
@override final  SalaryType salaryType;
// Fixed, CommissionOnly, FixedPlusCommission
// Salary Structure
@override@JsonKey() final  double monthlySalary;
@override final  double? dailyTargetAmount;
// Sales target per day
@override final  int? dailyCallTarget;
// Minimum calls per day
@override final  int? dailyTalkTimeTarget;
// Minutes per day
// Commission Structure
@override final  double? commissionPerLead;
// Per valid lead
@override final  double? commissionPerBooking;
// Per booking conversion
@override final  double? commissionPercentage;
// % of booking value
// Performance Tracking
 final  List<DailyCallReport> _dailyReports;
// % of booking value
// Performance Tracking
@override@JsonKey() List<DailyCallReport> get dailyReports {
  if (_dailyReports is EqualUnmodifiableListView) return _dailyReports;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_dailyReports);
}

 final  List<MonthlyPerformance> _monthlyReports;
@override@JsonKey() List<MonthlyPerformance> get monthlyReports {
  if (_monthlyReports is EqualUnmodifiableListView) return _monthlyReports;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_monthlyReports);
}

// Current Month Stats (Auto-calculated)
@override@JsonKey() final  int currentMonthCalls;
@override@JsonKey() final  int currentMonthConnected;
@override@JsonKey() final  int currentMonthValidLeads;
@override@JsonKey() final  int currentMonthBookings;
@override@JsonKey() final  double currentMonthRevenue;
@override@JsonKey() final  double currentMonthCommission;
@override@JsonKey() final  int currentMonthTalkTimeMinutes;
// Assigned Leads
 final  List<String> _assignedLeadIds;
// Assigned Leads
@override@JsonKey() List<String> get assignedLeadIds {
  if (_assignedLeadIds is EqualUnmodifiableListView) return _assignedLeadIds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_assignedLeadIds);
}

 final  List<CallerLeadAssignment> _leadAssignments;
@override@JsonKey() List<CallerLeadAssignment> get leadAssignments {
  if (_leadAssignments is EqualUnmodifiableListView) return _leadAssignments;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_leadAssignments);
}

// Status
@override final  CallerStatus status;
// Active, OnLeave, Suspended, Terminated
@override final  DateTime? lastActiveAt;
// Admin Notes
@override final  String? adminNotes;
 final  List<String> _performanceWarnings;
@override@JsonKey() List<String> get performanceWarnings {
  if (_performanceWarnings is EqualUnmodifiableListView) return _performanceWarnings;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_performanceWarnings);
}

@override final  DateTime createdAt;
@override final  DateTime updatedAt;

/// Create a copy of DailyCaller
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DailyCallerCopyWith<_DailyCaller> get copyWith => __$DailyCallerCopyWithImpl<_DailyCaller>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$DailyCallerToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _DailyCaller&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.email, email) || other.email == email)&&(identical(other.photoUrl, photoUrl) || other.photoUrl == photoUrl)&&(identical(other.employeeId, employeeId) || other.employeeId == employeeId)&&(identical(other.joiningDate, joiningDate) || other.joiningDate == joiningDate)&&(identical(other.callerType, callerType) || other.callerType == callerType)&&(identical(other.salaryType, salaryType) || other.salaryType == salaryType)&&(identical(other.monthlySalary, monthlySalary) || other.monthlySalary == monthlySalary)&&(identical(other.dailyTargetAmount, dailyTargetAmount) || other.dailyTargetAmount == dailyTargetAmount)&&(identical(other.dailyCallTarget, dailyCallTarget) || other.dailyCallTarget == dailyCallTarget)&&(identical(other.dailyTalkTimeTarget, dailyTalkTimeTarget) || other.dailyTalkTimeTarget == dailyTalkTimeTarget)&&(identical(other.commissionPerLead, commissionPerLead) || other.commissionPerLead == commissionPerLead)&&(identical(other.commissionPerBooking, commissionPerBooking) || other.commissionPerBooking == commissionPerBooking)&&(identical(other.commissionPercentage, commissionPercentage) || other.commissionPercentage == commissionPercentage)&&const DeepCollectionEquality().equals(other._dailyReports, _dailyReports)&&const DeepCollectionEquality().equals(other._monthlyReports, _monthlyReports)&&(identical(other.currentMonthCalls, currentMonthCalls) || other.currentMonthCalls == currentMonthCalls)&&(identical(other.currentMonthConnected, currentMonthConnected) || other.currentMonthConnected == currentMonthConnected)&&(identical(other.currentMonthValidLeads, currentMonthValidLeads) || other.currentMonthValidLeads == currentMonthValidLeads)&&(identical(other.currentMonthBookings, currentMonthBookings) || other.currentMonthBookings == currentMonthBookings)&&(identical(other.currentMonthRevenue, currentMonthRevenue) || other.currentMonthRevenue == currentMonthRevenue)&&(identical(other.currentMonthCommission, currentMonthCommission) || other.currentMonthCommission == currentMonthCommission)&&(identical(other.currentMonthTalkTimeMinutes, currentMonthTalkTimeMinutes) || other.currentMonthTalkTimeMinutes == currentMonthTalkTimeMinutes)&&const DeepCollectionEquality().equals(other._assignedLeadIds, _assignedLeadIds)&&const DeepCollectionEquality().equals(other._leadAssignments, _leadAssignments)&&(identical(other.status, status) || other.status == status)&&(identical(other.lastActiveAt, lastActiveAt) || other.lastActiveAt == lastActiveAt)&&(identical(other.adminNotes, adminNotes) || other.adminNotes == adminNotes)&&const DeepCollectionEquality().equals(other._performanceWarnings, _performanceWarnings)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,name,phone,email,photoUrl,employeeId,joiningDate,callerType,salaryType,monthlySalary,dailyTargetAmount,dailyCallTarget,dailyTalkTimeTarget,commissionPerLead,commissionPerBooking,commissionPercentage,const DeepCollectionEquality().hash(_dailyReports),const DeepCollectionEquality().hash(_monthlyReports),currentMonthCalls,currentMonthConnected,currentMonthValidLeads,currentMonthBookings,currentMonthRevenue,currentMonthCommission,currentMonthTalkTimeMinutes,const DeepCollectionEquality().hash(_assignedLeadIds),const DeepCollectionEquality().hash(_leadAssignments),status,lastActiveAt,adminNotes,const DeepCollectionEquality().hash(_performanceWarnings),createdAt,updatedAt]);

@override
String toString() {
  return 'DailyCaller(id: $id, name: $name, phone: $phone, email: $email, photoUrl: $photoUrl, employeeId: $employeeId, joiningDate: $joiningDate, callerType: $callerType, salaryType: $salaryType, monthlySalary: $monthlySalary, dailyTargetAmount: $dailyTargetAmount, dailyCallTarget: $dailyCallTarget, dailyTalkTimeTarget: $dailyTalkTimeTarget, commissionPerLead: $commissionPerLead, commissionPerBooking: $commissionPerBooking, commissionPercentage: $commissionPercentage, dailyReports: $dailyReports, monthlyReports: $monthlyReports, currentMonthCalls: $currentMonthCalls, currentMonthConnected: $currentMonthConnected, currentMonthValidLeads: $currentMonthValidLeads, currentMonthBookings: $currentMonthBookings, currentMonthRevenue: $currentMonthRevenue, currentMonthCommission: $currentMonthCommission, currentMonthTalkTimeMinutes: $currentMonthTalkTimeMinutes, assignedLeadIds: $assignedLeadIds, leadAssignments: $leadAssignments, status: $status, lastActiveAt: $lastActiveAt, adminNotes: $adminNotes, performanceWarnings: $performanceWarnings, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class _$DailyCallerCopyWith<$Res> implements $DailyCallerCopyWith<$Res> {
  factory _$DailyCallerCopyWith(_DailyCaller value, $Res Function(_DailyCaller) _then) = __$DailyCallerCopyWithImpl;
@override @useResult
$Res call({
 String id, String name, String phone, String email, String? photoUrl, String employeeId, DateTime joiningDate, CallerType callerType, SalaryType salaryType, double monthlySalary, double? dailyTargetAmount, int? dailyCallTarget, int? dailyTalkTimeTarget, double? commissionPerLead, double? commissionPerBooking, double? commissionPercentage, List<DailyCallReport> dailyReports, List<MonthlyPerformance> monthlyReports, int currentMonthCalls, int currentMonthConnected, int currentMonthValidLeads, int currentMonthBookings, double currentMonthRevenue, double currentMonthCommission, int currentMonthTalkTimeMinutes, List<String> assignedLeadIds, List<CallerLeadAssignment> leadAssignments, CallerStatus status, DateTime? lastActiveAt, String? adminNotes, List<String> performanceWarnings, DateTime createdAt, DateTime updatedAt
});




}
/// @nodoc
class __$DailyCallerCopyWithImpl<$Res>
    implements _$DailyCallerCopyWith<$Res> {
  __$DailyCallerCopyWithImpl(this._self, this._then);

  final _DailyCaller _self;
  final $Res Function(_DailyCaller) _then;

/// Create a copy of DailyCaller
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? phone = null,Object? email = null,Object? photoUrl = freezed,Object? employeeId = null,Object? joiningDate = null,Object? callerType = null,Object? salaryType = null,Object? monthlySalary = null,Object? dailyTargetAmount = freezed,Object? dailyCallTarget = freezed,Object? dailyTalkTimeTarget = freezed,Object? commissionPerLead = freezed,Object? commissionPerBooking = freezed,Object? commissionPercentage = freezed,Object? dailyReports = null,Object? monthlyReports = null,Object? currentMonthCalls = null,Object? currentMonthConnected = null,Object? currentMonthValidLeads = null,Object? currentMonthBookings = null,Object? currentMonthRevenue = null,Object? currentMonthCommission = null,Object? currentMonthTalkTimeMinutes = null,Object? assignedLeadIds = null,Object? leadAssignments = null,Object? status = null,Object? lastActiveAt = freezed,Object? adminNotes = freezed,Object? performanceWarnings = null,Object? createdAt = null,Object? updatedAt = null,}) {
  return _then(_DailyCaller(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,photoUrl: freezed == photoUrl ? _self.photoUrl : photoUrl // ignore: cast_nullable_to_non_nullable
as String?,employeeId: null == employeeId ? _self.employeeId : employeeId // ignore: cast_nullable_to_non_nullable
as String,joiningDate: null == joiningDate ? _self.joiningDate : joiningDate // ignore: cast_nullable_to_non_nullable
as DateTime,callerType: null == callerType ? _self.callerType : callerType // ignore: cast_nullable_to_non_nullable
as CallerType,salaryType: null == salaryType ? _self.salaryType : salaryType // ignore: cast_nullable_to_non_nullable
as SalaryType,monthlySalary: null == monthlySalary ? _self.monthlySalary : monthlySalary // ignore: cast_nullable_to_non_nullable
as double,dailyTargetAmount: freezed == dailyTargetAmount ? _self.dailyTargetAmount : dailyTargetAmount // ignore: cast_nullable_to_non_nullable
as double?,dailyCallTarget: freezed == dailyCallTarget ? _self.dailyCallTarget : dailyCallTarget // ignore: cast_nullable_to_non_nullable
as int?,dailyTalkTimeTarget: freezed == dailyTalkTimeTarget ? _self.dailyTalkTimeTarget : dailyTalkTimeTarget // ignore: cast_nullable_to_non_nullable
as int?,commissionPerLead: freezed == commissionPerLead ? _self.commissionPerLead : commissionPerLead // ignore: cast_nullable_to_non_nullable
as double?,commissionPerBooking: freezed == commissionPerBooking ? _self.commissionPerBooking : commissionPerBooking // ignore: cast_nullable_to_non_nullable
as double?,commissionPercentage: freezed == commissionPercentage ? _self.commissionPercentage : commissionPercentage // ignore: cast_nullable_to_non_nullable
as double?,dailyReports: null == dailyReports ? _self._dailyReports : dailyReports // ignore: cast_nullable_to_non_nullable
as List<DailyCallReport>,monthlyReports: null == monthlyReports ? _self._monthlyReports : monthlyReports // ignore: cast_nullable_to_non_nullable
as List<MonthlyPerformance>,currentMonthCalls: null == currentMonthCalls ? _self.currentMonthCalls : currentMonthCalls // ignore: cast_nullable_to_non_nullable
as int,currentMonthConnected: null == currentMonthConnected ? _self.currentMonthConnected : currentMonthConnected // ignore: cast_nullable_to_non_nullable
as int,currentMonthValidLeads: null == currentMonthValidLeads ? _self.currentMonthValidLeads : currentMonthValidLeads // ignore: cast_nullable_to_non_nullable
as int,currentMonthBookings: null == currentMonthBookings ? _self.currentMonthBookings : currentMonthBookings // ignore: cast_nullable_to_non_nullable
as int,currentMonthRevenue: null == currentMonthRevenue ? _self.currentMonthRevenue : currentMonthRevenue // ignore: cast_nullable_to_non_nullable
as double,currentMonthCommission: null == currentMonthCommission ? _self.currentMonthCommission : currentMonthCommission // ignore: cast_nullable_to_non_nullable
as double,currentMonthTalkTimeMinutes: null == currentMonthTalkTimeMinutes ? _self.currentMonthTalkTimeMinutes : currentMonthTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
as int,assignedLeadIds: null == assignedLeadIds ? _self._assignedLeadIds : assignedLeadIds // ignore: cast_nullable_to_non_nullable
as List<String>,leadAssignments: null == leadAssignments ? _self._leadAssignments : leadAssignments // ignore: cast_nullable_to_non_nullable
as List<CallerLeadAssignment>,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as CallerStatus,lastActiveAt: freezed == lastActiveAt ? _self.lastActiveAt : lastActiveAt // ignore: cast_nullable_to_non_nullable
as DateTime?,adminNotes: freezed == adminNotes ? _self.adminNotes : adminNotes // ignore: cast_nullable_to_non_nullable
as String?,performanceWarnings: null == performanceWarnings ? _self._performanceWarnings : performanceWarnings // ignore: cast_nullable_to_non_nullable
as List<String>,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,updatedAt: null == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}


/// @nodoc
mixin _$DailyCallReport {

 String get id; DateTime get date;// Call Statistics
 int get totalCalls; int get connected; int get notAnswered; int get busy; int get invalidNumber; int get callLater; int get notInterested;// Talk Time
 int get totalTalkTimeMinutes; double get avgTalkTimeMinutes;// Lead Generation
 int get validLeadsGenerated; int get interestedCustomers; int get siteVisitsScheduled; int get bookingsConfirmed;// Financial
 double get revenueGenerated; double get commissionEarned;// Detailed Log
 List<CallDetail> get callDetails;// Status
 ReportStatus get status;// Pending, Submitted, Verified
 String? get supervisorNotes; DateTime? get submittedAt; DateTime? get verifiedAt;
/// Create a copy of DailyCallReport
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DailyCallReportCopyWith<DailyCallReport> get copyWith => _$DailyCallReportCopyWithImpl<DailyCallReport>(this as DailyCallReport, _$identity);

  /// Serializes this DailyCallReport to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DailyCallReport&&(identical(other.id, id) || other.id == id)&&(identical(other.date, date) || other.date == date)&&(identical(other.totalCalls, totalCalls) || other.totalCalls == totalCalls)&&(identical(other.connected, connected) || other.connected == connected)&&(identical(other.notAnswered, notAnswered) || other.notAnswered == notAnswered)&&(identical(other.busy, busy) || other.busy == busy)&&(identical(other.invalidNumber, invalidNumber) || other.invalidNumber == invalidNumber)&&(identical(other.callLater, callLater) || other.callLater == callLater)&&(identical(other.notInterested, notInterested) || other.notInterested == notInterested)&&(identical(other.totalTalkTimeMinutes, totalTalkTimeMinutes) || other.totalTalkTimeMinutes == totalTalkTimeMinutes)&&(identical(other.avgTalkTimeMinutes, avgTalkTimeMinutes) || other.avgTalkTimeMinutes == avgTalkTimeMinutes)&&(identical(other.validLeadsGenerated, validLeadsGenerated) || other.validLeadsGenerated == validLeadsGenerated)&&(identical(other.interestedCustomers, interestedCustomers) || other.interestedCustomers == interestedCustomers)&&(identical(other.siteVisitsScheduled, siteVisitsScheduled) || other.siteVisitsScheduled == siteVisitsScheduled)&&(identical(other.bookingsConfirmed, bookingsConfirmed) || other.bookingsConfirmed == bookingsConfirmed)&&(identical(other.revenueGenerated, revenueGenerated) || other.revenueGenerated == revenueGenerated)&&(identical(other.commissionEarned, commissionEarned) || other.commissionEarned == commissionEarned)&&const DeepCollectionEquality().equals(other.callDetails, callDetails)&&(identical(other.status, status) || other.status == status)&&(identical(other.supervisorNotes, supervisorNotes) || other.supervisorNotes == supervisorNotes)&&(identical(other.submittedAt, submittedAt) || other.submittedAt == submittedAt)&&(identical(other.verifiedAt, verifiedAt) || other.verifiedAt == verifiedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,date,totalCalls,connected,notAnswered,busy,invalidNumber,callLater,notInterested,totalTalkTimeMinutes,avgTalkTimeMinutes,validLeadsGenerated,interestedCustomers,siteVisitsScheduled,bookingsConfirmed,revenueGenerated,commissionEarned,const DeepCollectionEquality().hash(callDetails),status,supervisorNotes,submittedAt,verifiedAt]);

@override
String toString() {
  return 'DailyCallReport(id: $id, date: $date, totalCalls: $totalCalls, connected: $connected, notAnswered: $notAnswered, busy: $busy, invalidNumber: $invalidNumber, callLater: $callLater, notInterested: $notInterested, totalTalkTimeMinutes: $totalTalkTimeMinutes, avgTalkTimeMinutes: $avgTalkTimeMinutes, validLeadsGenerated: $validLeadsGenerated, interestedCustomers: $interestedCustomers, siteVisitsScheduled: $siteVisitsScheduled, bookingsConfirmed: $bookingsConfirmed, revenueGenerated: $revenueGenerated, commissionEarned: $commissionEarned, callDetails: $callDetails, status: $status, supervisorNotes: $supervisorNotes, submittedAt: $submittedAt, verifiedAt: $verifiedAt)';
}


}

/// @nodoc
abstract mixin class $DailyCallReportCopyWith<$Res>  {
  factory $DailyCallReportCopyWith(DailyCallReport value, $Res Function(DailyCallReport) _then) = _$DailyCallReportCopyWithImpl;
@useResult
$Res call({
 String id, DateTime date, int totalCalls, int connected, int notAnswered, int busy, int invalidNumber, int callLater, int notInterested, int totalTalkTimeMinutes, double avgTalkTimeMinutes, int validLeadsGenerated, int interestedCustomers, int siteVisitsScheduled, int bookingsConfirmed, double revenueGenerated, double commissionEarned, List<CallDetail> callDetails, ReportStatus status, String? supervisorNotes, DateTime? submittedAt, DateTime? verifiedAt
});




}
/// @nodoc
class _$DailyCallReportCopyWithImpl<$Res>
    implements $DailyCallReportCopyWith<$Res> {
  _$DailyCallReportCopyWithImpl(this._self, this._then);

  final DailyCallReport _self;
  final $Res Function(DailyCallReport) _then;

/// Create a copy of DailyCallReport
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? date = null,Object? totalCalls = null,Object? connected = null,Object? notAnswered = null,Object? busy = null,Object? invalidNumber = null,Object? callLater = null,Object? notInterested = null,Object? totalTalkTimeMinutes = null,Object? avgTalkTimeMinutes = null,Object? validLeadsGenerated = null,Object? interestedCustomers = null,Object? siteVisitsScheduled = null,Object? bookingsConfirmed = null,Object? revenueGenerated = null,Object? commissionEarned = null,Object? callDetails = null,Object? status = null,Object? supervisorNotes = freezed,Object? submittedAt = freezed,Object? verifiedAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,date: null == date ? _self.date : date // ignore: cast_nullable_to_non_nullable
as DateTime,totalCalls: null == totalCalls ? _self.totalCalls : totalCalls // ignore: cast_nullable_to_non_nullable
as int,connected: null == connected ? _self.connected : connected // ignore: cast_nullable_to_non_nullable
as int,notAnswered: null == notAnswered ? _self.notAnswered : notAnswered // ignore: cast_nullable_to_non_nullable
as int,busy: null == busy ? _self.busy : busy // ignore: cast_nullable_to_non_nullable
as int,invalidNumber: null == invalidNumber ? _self.invalidNumber : invalidNumber // ignore: cast_nullable_to_non_nullable
as int,callLater: null == callLater ? _self.callLater : callLater // ignore: cast_nullable_to_non_nullable
as int,notInterested: null == notInterested ? _self.notInterested : notInterested // ignore: cast_nullable_to_non_nullable
as int,totalTalkTimeMinutes: null == totalTalkTimeMinutes ? _self.totalTalkTimeMinutes : totalTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
as int,avgTalkTimeMinutes: null == avgTalkTimeMinutes ? _self.avgTalkTimeMinutes : avgTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
as double,validLeadsGenerated: null == validLeadsGenerated ? _self.validLeadsGenerated : validLeadsGenerated // ignore: cast_nullable_to_non_nullable
as int,interestedCustomers: null == interestedCustomers ? _self.interestedCustomers : interestedCustomers // ignore: cast_nullable_to_non_nullable
as int,siteVisitsScheduled: null == siteVisitsScheduled ? _self.siteVisitsScheduled : siteVisitsScheduled // ignore: cast_nullable_to_non_nullable
as int,bookingsConfirmed: null == bookingsConfirmed ? _self.bookingsConfirmed : bookingsConfirmed // ignore: cast_nullable_to_non_nullable
as int,revenueGenerated: null == revenueGenerated ? _self.revenueGenerated : revenueGenerated // ignore: cast_nullable_to_non_nullable
as double,commissionEarned: null == commissionEarned ? _self.commissionEarned : commissionEarned // ignore: cast_nullable_to_non_nullable
as double,callDetails: null == callDetails ? _self.callDetails : callDetails // ignore: cast_nullable_to_non_nullable
as List<CallDetail>,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as ReportStatus,supervisorNotes: freezed == supervisorNotes ? _self.supervisorNotes : supervisorNotes // ignore: cast_nullable_to_non_nullable
as String?,submittedAt: freezed == submittedAt ? _self.submittedAt : submittedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,verifiedAt: freezed == verifiedAt ? _self.verifiedAt : verifiedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [DailyCallReport].
extension DailyCallReportPatterns on DailyCallReport {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DailyCallReport value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DailyCallReport() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DailyCallReport value)  $default,){
final _that = this;
switch (_that) {
case _DailyCallReport():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DailyCallReport value)?  $default,){
final _that = this;
switch (_that) {
case _DailyCallReport() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  DateTime date,  int totalCalls,  int connected,  int notAnswered,  int busy,  int invalidNumber,  int callLater,  int notInterested,  int totalTalkTimeMinutes,  double avgTalkTimeMinutes,  int validLeadsGenerated,  int interestedCustomers,  int siteVisitsScheduled,  int bookingsConfirmed,  double revenueGenerated,  double commissionEarned,  List<CallDetail> callDetails,  ReportStatus status,  String? supervisorNotes,  DateTime? submittedAt,  DateTime? verifiedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DailyCallReport() when $default != null:
return $default(_that.id,_that.date,_that.totalCalls,_that.connected,_that.notAnswered,_that.busy,_that.invalidNumber,_that.callLater,_that.notInterested,_that.totalTalkTimeMinutes,_that.avgTalkTimeMinutes,_that.validLeadsGenerated,_that.interestedCustomers,_that.siteVisitsScheduled,_that.bookingsConfirmed,_that.revenueGenerated,_that.commissionEarned,_that.callDetails,_that.status,_that.supervisorNotes,_that.submittedAt,_that.verifiedAt);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  DateTime date,  int totalCalls,  int connected,  int notAnswered,  int busy,  int invalidNumber,  int callLater,  int notInterested,  int totalTalkTimeMinutes,  double avgTalkTimeMinutes,  int validLeadsGenerated,  int interestedCustomers,  int siteVisitsScheduled,  int bookingsConfirmed,  double revenueGenerated,  double commissionEarned,  List<CallDetail> callDetails,  ReportStatus status,  String? supervisorNotes,  DateTime? submittedAt,  DateTime? verifiedAt)  $default,) {final _that = this;
switch (_that) {
case _DailyCallReport():
return $default(_that.id,_that.date,_that.totalCalls,_that.connected,_that.notAnswered,_that.busy,_that.invalidNumber,_that.callLater,_that.notInterested,_that.totalTalkTimeMinutes,_that.avgTalkTimeMinutes,_that.validLeadsGenerated,_that.interestedCustomers,_that.siteVisitsScheduled,_that.bookingsConfirmed,_that.revenueGenerated,_that.commissionEarned,_that.callDetails,_that.status,_that.supervisorNotes,_that.submittedAt,_that.verifiedAt);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  DateTime date,  int totalCalls,  int connected,  int notAnswered,  int busy,  int invalidNumber,  int callLater,  int notInterested,  int totalTalkTimeMinutes,  double avgTalkTimeMinutes,  int validLeadsGenerated,  int interestedCustomers,  int siteVisitsScheduled,  int bookingsConfirmed,  double revenueGenerated,  double commissionEarned,  List<CallDetail> callDetails,  ReportStatus status,  String? supervisorNotes,  DateTime? submittedAt,  DateTime? verifiedAt)?  $default,) {final _that = this;
switch (_that) {
case _DailyCallReport() when $default != null:
return $default(_that.id,_that.date,_that.totalCalls,_that.connected,_that.notAnswered,_that.busy,_that.invalidNumber,_that.callLater,_that.notInterested,_that.totalTalkTimeMinutes,_that.avgTalkTimeMinutes,_that.validLeadsGenerated,_that.interestedCustomers,_that.siteVisitsScheduled,_that.bookingsConfirmed,_that.revenueGenerated,_that.commissionEarned,_that.callDetails,_that.status,_that.supervisorNotes,_that.submittedAt,_that.verifiedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _DailyCallReport implements DailyCallReport {
  const _DailyCallReport({this.id = '', required this.date, this.totalCalls = 0, this.connected = 0, this.notAnswered = 0, this.busy = 0, this.invalidNumber = 0, this.callLater = 0, this.notInterested = 0, this.totalTalkTimeMinutes = 0, this.avgTalkTimeMinutes = 0.0, this.validLeadsGenerated = 0, this.interestedCustomers = 0, this.siteVisitsScheduled = 0, this.bookingsConfirmed = 0, this.revenueGenerated = 0.0, this.commissionEarned = 0.0, final  List<CallDetail> callDetails = const [], required this.status, this.supervisorNotes, this.submittedAt, this.verifiedAt}): _callDetails = callDetails;
  factory _DailyCallReport.fromJson(Map<String, dynamic> json) => _$DailyCallReportFromJson(json);

@override@JsonKey() final  String id;
@override final  DateTime date;
// Call Statistics
@override@JsonKey() final  int totalCalls;
@override@JsonKey() final  int connected;
@override@JsonKey() final  int notAnswered;
@override@JsonKey() final  int busy;
@override@JsonKey() final  int invalidNumber;
@override@JsonKey() final  int callLater;
@override@JsonKey() final  int notInterested;
// Talk Time
@override@JsonKey() final  int totalTalkTimeMinutes;
@override@JsonKey() final  double avgTalkTimeMinutes;
// Lead Generation
@override@JsonKey() final  int validLeadsGenerated;
@override@JsonKey() final  int interestedCustomers;
@override@JsonKey() final  int siteVisitsScheduled;
@override@JsonKey() final  int bookingsConfirmed;
// Financial
@override@JsonKey() final  double revenueGenerated;
@override@JsonKey() final  double commissionEarned;
// Detailed Log
 final  List<CallDetail> _callDetails;
// Detailed Log
@override@JsonKey() List<CallDetail> get callDetails {
  if (_callDetails is EqualUnmodifiableListView) return _callDetails;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_callDetails);
}

// Status
@override final  ReportStatus status;
// Pending, Submitted, Verified
@override final  String? supervisorNotes;
@override final  DateTime? submittedAt;
@override final  DateTime? verifiedAt;

/// Create a copy of DailyCallReport
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DailyCallReportCopyWith<_DailyCallReport> get copyWith => __$DailyCallReportCopyWithImpl<_DailyCallReport>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$DailyCallReportToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _DailyCallReport&&(identical(other.id, id) || other.id == id)&&(identical(other.date, date) || other.date == date)&&(identical(other.totalCalls, totalCalls) || other.totalCalls == totalCalls)&&(identical(other.connected, connected) || other.connected == connected)&&(identical(other.notAnswered, notAnswered) || other.notAnswered == notAnswered)&&(identical(other.busy, busy) || other.busy == busy)&&(identical(other.invalidNumber, invalidNumber) || other.invalidNumber == invalidNumber)&&(identical(other.callLater, callLater) || other.callLater == callLater)&&(identical(other.notInterested, notInterested) || other.notInterested == notInterested)&&(identical(other.totalTalkTimeMinutes, totalTalkTimeMinutes) || other.totalTalkTimeMinutes == totalTalkTimeMinutes)&&(identical(other.avgTalkTimeMinutes, avgTalkTimeMinutes) || other.avgTalkTimeMinutes == avgTalkTimeMinutes)&&(identical(other.validLeadsGenerated, validLeadsGenerated) || other.validLeadsGenerated == validLeadsGenerated)&&(identical(other.interestedCustomers, interestedCustomers) || other.interestedCustomers == interestedCustomers)&&(identical(other.siteVisitsScheduled, siteVisitsScheduled) || other.siteVisitsScheduled == siteVisitsScheduled)&&(identical(other.bookingsConfirmed, bookingsConfirmed) || other.bookingsConfirmed == bookingsConfirmed)&&(identical(other.revenueGenerated, revenueGenerated) || other.revenueGenerated == revenueGenerated)&&(identical(other.commissionEarned, commissionEarned) || other.commissionEarned == commissionEarned)&&const DeepCollectionEquality().equals(other._callDetails, _callDetails)&&(identical(other.status, status) || other.status == status)&&(identical(other.supervisorNotes, supervisorNotes) || other.supervisorNotes == supervisorNotes)&&(identical(other.submittedAt, submittedAt) || other.submittedAt == submittedAt)&&(identical(other.verifiedAt, verifiedAt) || other.verifiedAt == verifiedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,date,totalCalls,connected,notAnswered,busy,invalidNumber,callLater,notInterested,totalTalkTimeMinutes,avgTalkTimeMinutes,validLeadsGenerated,interestedCustomers,siteVisitsScheduled,bookingsConfirmed,revenueGenerated,commissionEarned,const DeepCollectionEquality().hash(_callDetails),status,supervisorNotes,submittedAt,verifiedAt]);

@override
String toString() {
  return 'DailyCallReport(id: $id, date: $date, totalCalls: $totalCalls, connected: $connected, notAnswered: $notAnswered, busy: $busy, invalidNumber: $invalidNumber, callLater: $callLater, notInterested: $notInterested, totalTalkTimeMinutes: $totalTalkTimeMinutes, avgTalkTimeMinutes: $avgTalkTimeMinutes, validLeadsGenerated: $validLeadsGenerated, interestedCustomers: $interestedCustomers, siteVisitsScheduled: $siteVisitsScheduled, bookingsConfirmed: $bookingsConfirmed, revenueGenerated: $revenueGenerated, commissionEarned: $commissionEarned, callDetails: $callDetails, status: $status, supervisorNotes: $supervisorNotes, submittedAt: $submittedAt, verifiedAt: $verifiedAt)';
}


}

/// @nodoc
abstract mixin class _$DailyCallReportCopyWith<$Res> implements $DailyCallReportCopyWith<$Res> {
  factory _$DailyCallReportCopyWith(_DailyCallReport value, $Res Function(_DailyCallReport) _then) = __$DailyCallReportCopyWithImpl;
@override @useResult
$Res call({
 String id, DateTime date, int totalCalls, int connected, int notAnswered, int busy, int invalidNumber, int callLater, int notInterested, int totalTalkTimeMinutes, double avgTalkTimeMinutes, int validLeadsGenerated, int interestedCustomers, int siteVisitsScheduled, int bookingsConfirmed, double revenueGenerated, double commissionEarned, List<CallDetail> callDetails, ReportStatus status, String? supervisorNotes, DateTime? submittedAt, DateTime? verifiedAt
});




}
/// @nodoc
class __$DailyCallReportCopyWithImpl<$Res>
    implements _$DailyCallReportCopyWith<$Res> {
  __$DailyCallReportCopyWithImpl(this._self, this._then);

  final _DailyCallReport _self;
  final $Res Function(_DailyCallReport) _then;

/// Create a copy of DailyCallReport
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? date = null,Object? totalCalls = null,Object? connected = null,Object? notAnswered = null,Object? busy = null,Object? invalidNumber = null,Object? callLater = null,Object? notInterested = null,Object? totalTalkTimeMinutes = null,Object? avgTalkTimeMinutes = null,Object? validLeadsGenerated = null,Object? interestedCustomers = null,Object? siteVisitsScheduled = null,Object? bookingsConfirmed = null,Object? revenueGenerated = null,Object? commissionEarned = null,Object? callDetails = null,Object? status = null,Object? supervisorNotes = freezed,Object? submittedAt = freezed,Object? verifiedAt = freezed,}) {
  return _then(_DailyCallReport(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,date: null == date ? _self.date : date // ignore: cast_nullable_to_non_nullable
as DateTime,totalCalls: null == totalCalls ? _self.totalCalls : totalCalls // ignore: cast_nullable_to_non_nullable
as int,connected: null == connected ? _self.connected : connected // ignore: cast_nullable_to_non_nullable
as int,notAnswered: null == notAnswered ? _self.notAnswered : notAnswered // ignore: cast_nullable_to_non_nullable
as int,busy: null == busy ? _self.busy : busy // ignore: cast_nullable_to_non_nullable
as int,invalidNumber: null == invalidNumber ? _self.invalidNumber : invalidNumber // ignore: cast_nullable_to_non_nullable
as int,callLater: null == callLater ? _self.callLater : callLater // ignore: cast_nullable_to_non_nullable
as int,notInterested: null == notInterested ? _self.notInterested : notInterested // ignore: cast_nullable_to_non_nullable
as int,totalTalkTimeMinutes: null == totalTalkTimeMinutes ? _self.totalTalkTimeMinutes : totalTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
as int,avgTalkTimeMinutes: null == avgTalkTimeMinutes ? _self.avgTalkTimeMinutes : avgTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
as double,validLeadsGenerated: null == validLeadsGenerated ? _self.validLeadsGenerated : validLeadsGenerated // ignore: cast_nullable_to_non_nullable
as int,interestedCustomers: null == interestedCustomers ? _self.interestedCustomers : interestedCustomers // ignore: cast_nullable_to_non_nullable
as int,siteVisitsScheduled: null == siteVisitsScheduled ? _self.siteVisitsScheduled : siteVisitsScheduled // ignore: cast_nullable_to_non_nullable
as int,bookingsConfirmed: null == bookingsConfirmed ? _self.bookingsConfirmed : bookingsConfirmed // ignore: cast_nullable_to_non_nullable
as int,revenueGenerated: null == revenueGenerated ? _self.revenueGenerated : revenueGenerated // ignore: cast_nullable_to_non_nullable
as double,commissionEarned: null == commissionEarned ? _self.commissionEarned : commissionEarned // ignore: cast_nullable_to_non_nullable
as double,callDetails: null == callDetails ? _self._callDetails : callDetails // ignore: cast_nullable_to_non_nullable
as List<CallDetail>,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as ReportStatus,supervisorNotes: freezed == supervisorNotes ? _self.supervisorNotes : supervisorNotes // ignore: cast_nullable_to_non_nullable
as String?,submittedAt: freezed == submittedAt ? _self.submittedAt : submittedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,verifiedAt: freezed == verifiedAt ? _self.verifiedAt : verifiedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$CallDetail {

 String get leadId; String get leadName; String get leadPhone; DateTime get callTime; CallOutcome get outcome; int? get talkTimeSeconds; String? get notes; String? get recordingUrl;// Call recording
 GeoLocation? get location;
/// Create a copy of CallDetail
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CallDetailCopyWith<CallDetail> get copyWith => _$CallDetailCopyWithImpl<CallDetail>(this as CallDetail, _$identity);

  /// Serializes this CallDetail to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CallDetail&&(identical(other.leadId, leadId) || other.leadId == leadId)&&(identical(other.leadName, leadName) || other.leadName == leadName)&&(identical(other.leadPhone, leadPhone) || other.leadPhone == leadPhone)&&(identical(other.callTime, callTime) || other.callTime == callTime)&&(identical(other.outcome, outcome) || other.outcome == outcome)&&(identical(other.talkTimeSeconds, talkTimeSeconds) || other.talkTimeSeconds == talkTimeSeconds)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.recordingUrl, recordingUrl) || other.recordingUrl == recordingUrl)&&(identical(other.location, location) || other.location == location));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,leadId,leadName,leadPhone,callTime,outcome,talkTimeSeconds,notes,recordingUrl,location);

@override
String toString() {
  return 'CallDetail(leadId: $leadId, leadName: $leadName, leadPhone: $leadPhone, callTime: $callTime, outcome: $outcome, talkTimeSeconds: $talkTimeSeconds, notes: $notes, recordingUrl: $recordingUrl, location: $location)';
}


}

/// @nodoc
abstract mixin class $CallDetailCopyWith<$Res>  {
  factory $CallDetailCopyWith(CallDetail value, $Res Function(CallDetail) _then) = _$CallDetailCopyWithImpl;
@useResult
$Res call({
 String leadId, String leadName, String leadPhone, DateTime callTime, CallOutcome outcome, int? talkTimeSeconds, String? notes, String? recordingUrl, GeoLocation? location
});




}
/// @nodoc
class _$CallDetailCopyWithImpl<$Res>
    implements $CallDetailCopyWith<$Res> {
  _$CallDetailCopyWithImpl(this._self, this._then);

  final CallDetail _self;
  final $Res Function(CallDetail) _then;

/// Create a copy of CallDetail
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? leadId = null,Object? leadName = null,Object? leadPhone = null,Object? callTime = null,Object? outcome = null,Object? talkTimeSeconds = freezed,Object? notes = freezed,Object? recordingUrl = freezed,Object? location = freezed,}) {
  return _then(_self.copyWith(
leadId: null == leadId ? _self.leadId : leadId // ignore: cast_nullable_to_non_nullable
as String,leadName: null == leadName ? _self.leadName : leadName // ignore: cast_nullable_to_non_nullable
as String,leadPhone: null == leadPhone ? _self.leadPhone : leadPhone // ignore: cast_nullable_to_non_nullable
as String,callTime: null == callTime ? _self.callTime : callTime // ignore: cast_nullable_to_non_nullable
as DateTime,outcome: null == outcome ? _self.outcome : outcome // ignore: cast_nullable_to_non_nullable
as CallOutcome,talkTimeSeconds: freezed == talkTimeSeconds ? _self.talkTimeSeconds : talkTimeSeconds // ignore: cast_nullable_to_non_nullable
as int?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,recordingUrl: freezed == recordingUrl ? _self.recordingUrl : recordingUrl // ignore: cast_nullable_to_non_nullable
as String?,location: freezed == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation?,
  ));
}

}


/// Adds pattern-matching-related methods to [CallDetail].
extension CallDetailPatterns on CallDetail {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CallDetail value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CallDetail() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CallDetail value)  $default,){
final _that = this;
switch (_that) {
case _CallDetail():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CallDetail value)?  $default,){
final _that = this;
switch (_that) {
case _CallDetail() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String leadId,  String leadName,  String leadPhone,  DateTime callTime,  CallOutcome outcome,  int? talkTimeSeconds,  String? notes,  String? recordingUrl,  GeoLocation? location)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CallDetail() when $default != null:
return $default(_that.leadId,_that.leadName,_that.leadPhone,_that.callTime,_that.outcome,_that.talkTimeSeconds,_that.notes,_that.recordingUrl,_that.location);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String leadId,  String leadName,  String leadPhone,  DateTime callTime,  CallOutcome outcome,  int? talkTimeSeconds,  String? notes,  String? recordingUrl,  GeoLocation? location)  $default,) {final _that = this;
switch (_that) {
case _CallDetail():
return $default(_that.leadId,_that.leadName,_that.leadPhone,_that.callTime,_that.outcome,_that.talkTimeSeconds,_that.notes,_that.recordingUrl,_that.location);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String leadId,  String leadName,  String leadPhone,  DateTime callTime,  CallOutcome outcome,  int? talkTimeSeconds,  String? notes,  String? recordingUrl,  GeoLocation? location)?  $default,) {final _that = this;
switch (_that) {
case _CallDetail() when $default != null:
return $default(_that.leadId,_that.leadName,_that.leadPhone,_that.callTime,_that.outcome,_that.talkTimeSeconds,_that.notes,_that.recordingUrl,_that.location);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CallDetail implements CallDetail {
  const _CallDetail({this.leadId = '', this.leadName = '', this.leadPhone = '', required this.callTime, required this.outcome, this.talkTimeSeconds, this.notes, this.recordingUrl, this.location});
  factory _CallDetail.fromJson(Map<String, dynamic> json) => _$CallDetailFromJson(json);

@override@JsonKey() final  String leadId;
@override@JsonKey() final  String leadName;
@override@JsonKey() final  String leadPhone;
@override final  DateTime callTime;
@override final  CallOutcome outcome;
@override final  int? talkTimeSeconds;
@override final  String? notes;
@override final  String? recordingUrl;
// Call recording
@override final  GeoLocation? location;

/// Create a copy of CallDetail
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CallDetailCopyWith<_CallDetail> get copyWith => __$CallDetailCopyWithImpl<_CallDetail>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CallDetailToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _CallDetail&&(identical(other.leadId, leadId) || other.leadId == leadId)&&(identical(other.leadName, leadName) || other.leadName == leadName)&&(identical(other.leadPhone, leadPhone) || other.leadPhone == leadPhone)&&(identical(other.callTime, callTime) || other.callTime == callTime)&&(identical(other.outcome, outcome) || other.outcome == outcome)&&(identical(other.talkTimeSeconds, talkTimeSeconds) || other.talkTimeSeconds == talkTimeSeconds)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.recordingUrl, recordingUrl) || other.recordingUrl == recordingUrl)&&(identical(other.location, location) || other.location == location));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,leadId,leadName,leadPhone,callTime,outcome,talkTimeSeconds,notes,recordingUrl,location);

@override
String toString() {
  return 'CallDetail(leadId: $leadId, leadName: $leadName, leadPhone: $leadPhone, callTime: $callTime, outcome: $outcome, talkTimeSeconds: $talkTimeSeconds, notes: $notes, recordingUrl: $recordingUrl, location: $location)';
}


}

/// @nodoc
abstract mixin class _$CallDetailCopyWith<$Res> implements $CallDetailCopyWith<$Res> {
  factory _$CallDetailCopyWith(_CallDetail value, $Res Function(_CallDetail) _then) = __$CallDetailCopyWithImpl;
@override @useResult
$Res call({
 String leadId, String leadName, String leadPhone, DateTime callTime, CallOutcome outcome, int? talkTimeSeconds, String? notes, String? recordingUrl, GeoLocation? location
});




}
/// @nodoc
class __$CallDetailCopyWithImpl<$Res>
    implements _$CallDetailCopyWith<$Res> {
  __$CallDetailCopyWithImpl(this._self, this._then);

  final _CallDetail _self;
  final $Res Function(_CallDetail) _then;

/// Create a copy of CallDetail
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? leadId = null,Object? leadName = null,Object? leadPhone = null,Object? callTime = null,Object? outcome = null,Object? talkTimeSeconds = freezed,Object? notes = freezed,Object? recordingUrl = freezed,Object? location = freezed,}) {
  return _then(_CallDetail(
leadId: null == leadId ? _self.leadId : leadId // ignore: cast_nullable_to_non_nullable
as String,leadName: null == leadName ? _self.leadName : leadName // ignore: cast_nullable_to_non_nullable
as String,leadPhone: null == leadPhone ? _self.leadPhone : leadPhone // ignore: cast_nullable_to_non_nullable
as String,callTime: null == callTime ? _self.callTime : callTime // ignore: cast_nullable_to_non_nullable
as DateTime,outcome: null == outcome ? _self.outcome : outcome // ignore: cast_nullable_to_non_nullable
as CallOutcome,talkTimeSeconds: freezed == talkTimeSeconds ? _self.talkTimeSeconds : talkTimeSeconds // ignore: cast_nullable_to_non_nullable
as int?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,recordingUrl: freezed == recordingUrl ? _self.recordingUrl : recordingUrl // ignore: cast_nullable_to_non_nullable
as String?,location: freezed == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation?,
  ));
}


}


/// @nodoc
mixin _$MonthlyPerformance {

 String get id; int get year; int get month;// Call Stats
 int get totalCalls; int get connectedCalls; int get totalTalkTimeMinutes;// Lead & Sales
 int get validLeads; int get siteVisits; int get bookings; double get totalRevenue;// Financial
 double get baseSalary; double get commissionEarned; double get incentives; double get deductions; double get totalEarnings;// Target Achievement
 double get targetAchievementPercentage; int get ranking;// Among all callers
// Daily average
 double get avgCallsPerDay; double get avgTalkTimePerDay; double get avgLeadsPerDay;// Quality metrics
 double get leadQualityScore;// 0-100
 double get conversionRate; PaymentStatus get paymentStatus; DateTime? get paidAt;
/// Create a copy of MonthlyPerformance
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MonthlyPerformanceCopyWith<MonthlyPerformance> get copyWith => _$MonthlyPerformanceCopyWithImpl<MonthlyPerformance>(this as MonthlyPerformance, _$identity);

  /// Serializes this MonthlyPerformance to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MonthlyPerformance&&(identical(other.id, id) || other.id == id)&&(identical(other.year, year) || other.year == year)&&(identical(other.month, month) || other.month == month)&&(identical(other.totalCalls, totalCalls) || other.totalCalls == totalCalls)&&(identical(other.connectedCalls, connectedCalls) || other.connectedCalls == connectedCalls)&&(identical(other.totalTalkTimeMinutes, totalTalkTimeMinutes) || other.totalTalkTimeMinutes == totalTalkTimeMinutes)&&(identical(other.validLeads, validLeads) || other.validLeads == validLeads)&&(identical(other.siteVisits, siteVisits) || other.siteVisits == siteVisits)&&(identical(other.bookings, bookings) || other.bookings == bookings)&&(identical(other.totalRevenue, totalRevenue) || other.totalRevenue == totalRevenue)&&(identical(other.baseSalary, baseSalary) || other.baseSalary == baseSalary)&&(identical(other.commissionEarned, commissionEarned) || other.commissionEarned == commissionEarned)&&(identical(other.incentives, incentives) || other.incentives == incentives)&&(identical(other.deductions, deductions) || other.deductions == deductions)&&(identical(other.totalEarnings, totalEarnings) || other.totalEarnings == totalEarnings)&&(identical(other.targetAchievementPercentage, targetAchievementPercentage) || other.targetAchievementPercentage == targetAchievementPercentage)&&(identical(other.ranking, ranking) || other.ranking == ranking)&&(identical(other.avgCallsPerDay, avgCallsPerDay) || other.avgCallsPerDay == avgCallsPerDay)&&(identical(other.avgTalkTimePerDay, avgTalkTimePerDay) || other.avgTalkTimePerDay == avgTalkTimePerDay)&&(identical(other.avgLeadsPerDay, avgLeadsPerDay) || other.avgLeadsPerDay == avgLeadsPerDay)&&(identical(other.leadQualityScore, leadQualityScore) || other.leadQualityScore == leadQualityScore)&&(identical(other.conversionRate, conversionRate) || other.conversionRate == conversionRate)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.paidAt, paidAt) || other.paidAt == paidAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,year,month,totalCalls,connectedCalls,totalTalkTimeMinutes,validLeads,siteVisits,bookings,totalRevenue,baseSalary,commissionEarned,incentives,deductions,totalEarnings,targetAchievementPercentage,ranking,avgCallsPerDay,avgTalkTimePerDay,avgLeadsPerDay,leadQualityScore,conversionRate,paymentStatus,paidAt]);

@override
String toString() {
  return 'MonthlyPerformance(id: $id, year: $year, month: $month, totalCalls: $totalCalls, connectedCalls: $connectedCalls, totalTalkTimeMinutes: $totalTalkTimeMinutes, validLeads: $validLeads, siteVisits: $siteVisits, bookings: $bookings, totalRevenue: $totalRevenue, baseSalary: $baseSalary, commissionEarned: $commissionEarned, incentives: $incentives, deductions: $deductions, totalEarnings: $totalEarnings, targetAchievementPercentage: $targetAchievementPercentage, ranking: $ranking, avgCallsPerDay: $avgCallsPerDay, avgTalkTimePerDay: $avgTalkTimePerDay, avgLeadsPerDay: $avgLeadsPerDay, leadQualityScore: $leadQualityScore, conversionRate: $conversionRate, paymentStatus: $paymentStatus, paidAt: $paidAt)';
}


}

/// @nodoc
abstract mixin class $MonthlyPerformanceCopyWith<$Res>  {
  factory $MonthlyPerformanceCopyWith(MonthlyPerformance value, $Res Function(MonthlyPerformance) _then) = _$MonthlyPerformanceCopyWithImpl;
@useResult
$Res call({
 String id, int year, int month, int totalCalls, int connectedCalls, int totalTalkTimeMinutes, int validLeads, int siteVisits, int bookings, double totalRevenue, double baseSalary, double commissionEarned, double incentives, double deductions, double totalEarnings, double targetAchievementPercentage, int ranking, double avgCallsPerDay, double avgTalkTimePerDay, double avgLeadsPerDay, double leadQualityScore, double conversionRate, PaymentStatus paymentStatus, DateTime? paidAt
});




}
/// @nodoc
class _$MonthlyPerformanceCopyWithImpl<$Res>
    implements $MonthlyPerformanceCopyWith<$Res> {
  _$MonthlyPerformanceCopyWithImpl(this._self, this._then);

  final MonthlyPerformance _self;
  final $Res Function(MonthlyPerformance) _then;

/// Create a copy of MonthlyPerformance
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? year = null,Object? month = null,Object? totalCalls = null,Object? connectedCalls = null,Object? totalTalkTimeMinutes = null,Object? validLeads = null,Object? siteVisits = null,Object? bookings = null,Object? totalRevenue = null,Object? baseSalary = null,Object? commissionEarned = null,Object? incentives = null,Object? deductions = null,Object? totalEarnings = null,Object? targetAchievementPercentage = null,Object? ranking = null,Object? avgCallsPerDay = null,Object? avgTalkTimePerDay = null,Object? avgLeadsPerDay = null,Object? leadQualityScore = null,Object? conversionRate = null,Object? paymentStatus = null,Object? paidAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,year: null == year ? _self.year : year // ignore: cast_nullable_to_non_nullable
as int,month: null == month ? _self.month : month // ignore: cast_nullable_to_non_nullable
as int,totalCalls: null == totalCalls ? _self.totalCalls : totalCalls // ignore: cast_nullable_to_non_nullable
as int,connectedCalls: null == connectedCalls ? _self.connectedCalls : connectedCalls // ignore: cast_nullable_to_non_nullable
as int,totalTalkTimeMinutes: null == totalTalkTimeMinutes ? _self.totalTalkTimeMinutes : totalTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
as int,validLeads: null == validLeads ? _self.validLeads : validLeads // ignore: cast_nullable_to_non_nullable
as int,siteVisits: null == siteVisits ? _self.siteVisits : siteVisits // ignore: cast_nullable_to_non_nullable
as int,bookings: null == bookings ? _self.bookings : bookings // ignore: cast_nullable_to_non_nullable
as int,totalRevenue: null == totalRevenue ? _self.totalRevenue : totalRevenue // ignore: cast_nullable_to_non_nullable
as double,baseSalary: null == baseSalary ? _self.baseSalary : baseSalary // ignore: cast_nullable_to_non_nullable
as double,commissionEarned: null == commissionEarned ? _self.commissionEarned : commissionEarned // ignore: cast_nullable_to_non_nullable
as double,incentives: null == incentives ? _self.incentives : incentives // ignore: cast_nullable_to_non_nullable
as double,deductions: null == deductions ? _self.deductions : deductions // ignore: cast_nullable_to_non_nullable
as double,totalEarnings: null == totalEarnings ? _self.totalEarnings : totalEarnings // ignore: cast_nullable_to_non_nullable
as double,targetAchievementPercentage: null == targetAchievementPercentage ? _self.targetAchievementPercentage : targetAchievementPercentage // ignore: cast_nullable_to_non_nullable
as double,ranking: null == ranking ? _self.ranking : ranking // ignore: cast_nullable_to_non_nullable
as int,avgCallsPerDay: null == avgCallsPerDay ? _self.avgCallsPerDay : avgCallsPerDay // ignore: cast_nullable_to_non_nullable
as double,avgTalkTimePerDay: null == avgTalkTimePerDay ? _self.avgTalkTimePerDay : avgTalkTimePerDay // ignore: cast_nullable_to_non_nullable
as double,avgLeadsPerDay: null == avgLeadsPerDay ? _self.avgLeadsPerDay : avgLeadsPerDay // ignore: cast_nullable_to_non_nullable
as double,leadQualityScore: null == leadQualityScore ? _self.leadQualityScore : leadQualityScore // ignore: cast_nullable_to_non_nullable
as double,conversionRate: null == conversionRate ? _self.conversionRate : conversionRate // ignore: cast_nullable_to_non_nullable
as double,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as PaymentStatus,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [MonthlyPerformance].
extension MonthlyPerformancePatterns on MonthlyPerformance {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MonthlyPerformance value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MonthlyPerformance() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MonthlyPerformance value)  $default,){
final _that = this;
switch (_that) {
case _MonthlyPerformance():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MonthlyPerformance value)?  $default,){
final _that = this;
switch (_that) {
case _MonthlyPerformance() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  int year,  int month,  int totalCalls,  int connectedCalls,  int totalTalkTimeMinutes,  int validLeads,  int siteVisits,  int bookings,  double totalRevenue,  double baseSalary,  double commissionEarned,  double incentives,  double deductions,  double totalEarnings,  double targetAchievementPercentage,  int ranking,  double avgCallsPerDay,  double avgTalkTimePerDay,  double avgLeadsPerDay,  double leadQualityScore,  double conversionRate,  PaymentStatus paymentStatus,  DateTime? paidAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MonthlyPerformance() when $default != null:
return $default(_that.id,_that.year,_that.month,_that.totalCalls,_that.connectedCalls,_that.totalTalkTimeMinutes,_that.validLeads,_that.siteVisits,_that.bookings,_that.totalRevenue,_that.baseSalary,_that.commissionEarned,_that.incentives,_that.deductions,_that.totalEarnings,_that.targetAchievementPercentage,_that.ranking,_that.avgCallsPerDay,_that.avgTalkTimePerDay,_that.avgLeadsPerDay,_that.leadQualityScore,_that.conversionRate,_that.paymentStatus,_that.paidAt);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  int year,  int month,  int totalCalls,  int connectedCalls,  int totalTalkTimeMinutes,  int validLeads,  int siteVisits,  int bookings,  double totalRevenue,  double baseSalary,  double commissionEarned,  double incentives,  double deductions,  double totalEarnings,  double targetAchievementPercentage,  int ranking,  double avgCallsPerDay,  double avgTalkTimePerDay,  double avgLeadsPerDay,  double leadQualityScore,  double conversionRate,  PaymentStatus paymentStatus,  DateTime? paidAt)  $default,) {final _that = this;
switch (_that) {
case _MonthlyPerformance():
return $default(_that.id,_that.year,_that.month,_that.totalCalls,_that.connectedCalls,_that.totalTalkTimeMinutes,_that.validLeads,_that.siteVisits,_that.bookings,_that.totalRevenue,_that.baseSalary,_that.commissionEarned,_that.incentives,_that.deductions,_that.totalEarnings,_that.targetAchievementPercentage,_that.ranking,_that.avgCallsPerDay,_that.avgTalkTimePerDay,_that.avgLeadsPerDay,_that.leadQualityScore,_that.conversionRate,_that.paymentStatus,_that.paidAt);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  int year,  int month,  int totalCalls,  int connectedCalls,  int totalTalkTimeMinutes,  int validLeads,  int siteVisits,  int bookings,  double totalRevenue,  double baseSalary,  double commissionEarned,  double incentives,  double deductions,  double totalEarnings,  double targetAchievementPercentage,  int ranking,  double avgCallsPerDay,  double avgTalkTimePerDay,  double avgLeadsPerDay,  double leadQualityScore,  double conversionRate,  PaymentStatus paymentStatus,  DateTime? paidAt)?  $default,) {final _that = this;
switch (_that) {
case _MonthlyPerformance() when $default != null:
return $default(_that.id,_that.year,_that.month,_that.totalCalls,_that.connectedCalls,_that.totalTalkTimeMinutes,_that.validLeads,_that.siteVisits,_that.bookings,_that.totalRevenue,_that.baseSalary,_that.commissionEarned,_that.incentives,_that.deductions,_that.totalEarnings,_that.targetAchievementPercentage,_that.ranking,_that.avgCallsPerDay,_that.avgTalkTimePerDay,_that.avgLeadsPerDay,_that.leadQualityScore,_that.conversionRate,_that.paymentStatus,_that.paidAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _MonthlyPerformance implements MonthlyPerformance {
  const _MonthlyPerformance({this.id = '', this.year = 0, this.month = 0, this.totalCalls = 0, this.connectedCalls = 0, this.totalTalkTimeMinutes = 0, this.validLeads = 0, this.siteVisits = 0, this.bookings = 0, this.totalRevenue = 0.0, this.baseSalary = 0.0, this.commissionEarned = 0.0, this.incentives = 0.0, this.deductions = 0.0, this.totalEarnings = 0.0, this.targetAchievementPercentage = 0.0, this.ranking = 0, this.avgCallsPerDay = 0.0, this.avgTalkTimePerDay = 0.0, this.avgLeadsPerDay = 0.0, this.leadQualityScore = 0.0, this.conversionRate = 0.0, required this.paymentStatus, this.paidAt});
  factory _MonthlyPerformance.fromJson(Map<String, dynamic> json) => _$MonthlyPerformanceFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  int year;
@override@JsonKey() final  int month;
// Call Stats
@override@JsonKey() final  int totalCalls;
@override@JsonKey() final  int connectedCalls;
@override@JsonKey() final  int totalTalkTimeMinutes;
// Lead & Sales
@override@JsonKey() final  int validLeads;
@override@JsonKey() final  int siteVisits;
@override@JsonKey() final  int bookings;
@override@JsonKey() final  double totalRevenue;
// Financial
@override@JsonKey() final  double baseSalary;
@override@JsonKey() final  double commissionEarned;
@override@JsonKey() final  double incentives;
@override@JsonKey() final  double deductions;
@override@JsonKey() final  double totalEarnings;
// Target Achievement
@override@JsonKey() final  double targetAchievementPercentage;
@override@JsonKey() final  int ranking;
// Among all callers
// Daily average
@override@JsonKey() final  double avgCallsPerDay;
@override@JsonKey() final  double avgTalkTimePerDay;
@override@JsonKey() final  double avgLeadsPerDay;
// Quality metrics
@override@JsonKey() final  double leadQualityScore;
// 0-100
@override@JsonKey() final  double conversionRate;
@override final  PaymentStatus paymentStatus;
@override final  DateTime? paidAt;

/// Create a copy of MonthlyPerformance
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MonthlyPerformanceCopyWith<_MonthlyPerformance> get copyWith => __$MonthlyPerformanceCopyWithImpl<_MonthlyPerformance>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$MonthlyPerformanceToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _MonthlyPerformance&&(identical(other.id, id) || other.id == id)&&(identical(other.year, year) || other.year == year)&&(identical(other.month, month) || other.month == month)&&(identical(other.totalCalls, totalCalls) || other.totalCalls == totalCalls)&&(identical(other.connectedCalls, connectedCalls) || other.connectedCalls == connectedCalls)&&(identical(other.totalTalkTimeMinutes, totalTalkTimeMinutes) || other.totalTalkTimeMinutes == totalTalkTimeMinutes)&&(identical(other.validLeads, validLeads) || other.validLeads == validLeads)&&(identical(other.siteVisits, siteVisits) || other.siteVisits == siteVisits)&&(identical(other.bookings, bookings) || other.bookings == bookings)&&(identical(other.totalRevenue, totalRevenue) || other.totalRevenue == totalRevenue)&&(identical(other.baseSalary, baseSalary) || other.baseSalary == baseSalary)&&(identical(other.commissionEarned, commissionEarned) || other.commissionEarned == commissionEarned)&&(identical(other.incentives, incentives) || other.incentives == incentives)&&(identical(other.deductions, deductions) || other.deductions == deductions)&&(identical(other.totalEarnings, totalEarnings) || other.totalEarnings == totalEarnings)&&(identical(other.targetAchievementPercentage, targetAchievementPercentage) || other.targetAchievementPercentage == targetAchievementPercentage)&&(identical(other.ranking, ranking) || other.ranking == ranking)&&(identical(other.avgCallsPerDay, avgCallsPerDay) || other.avgCallsPerDay == avgCallsPerDay)&&(identical(other.avgTalkTimePerDay, avgTalkTimePerDay) || other.avgTalkTimePerDay == avgTalkTimePerDay)&&(identical(other.avgLeadsPerDay, avgLeadsPerDay) || other.avgLeadsPerDay == avgLeadsPerDay)&&(identical(other.leadQualityScore, leadQualityScore) || other.leadQualityScore == leadQualityScore)&&(identical(other.conversionRate, conversionRate) || other.conversionRate == conversionRate)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.paidAt, paidAt) || other.paidAt == paidAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,year,month,totalCalls,connectedCalls,totalTalkTimeMinutes,validLeads,siteVisits,bookings,totalRevenue,baseSalary,commissionEarned,incentives,deductions,totalEarnings,targetAchievementPercentage,ranking,avgCallsPerDay,avgTalkTimePerDay,avgLeadsPerDay,leadQualityScore,conversionRate,paymentStatus,paidAt]);

@override
String toString() {
  return 'MonthlyPerformance(id: $id, year: $year, month: $month, totalCalls: $totalCalls, connectedCalls: $connectedCalls, totalTalkTimeMinutes: $totalTalkTimeMinutes, validLeads: $validLeads, siteVisits: $siteVisits, bookings: $bookings, totalRevenue: $totalRevenue, baseSalary: $baseSalary, commissionEarned: $commissionEarned, incentives: $incentives, deductions: $deductions, totalEarnings: $totalEarnings, targetAchievementPercentage: $targetAchievementPercentage, ranking: $ranking, avgCallsPerDay: $avgCallsPerDay, avgTalkTimePerDay: $avgTalkTimePerDay, avgLeadsPerDay: $avgLeadsPerDay, leadQualityScore: $leadQualityScore, conversionRate: $conversionRate, paymentStatus: $paymentStatus, paidAt: $paidAt)';
}


}

/// @nodoc
abstract mixin class _$MonthlyPerformanceCopyWith<$Res> implements $MonthlyPerformanceCopyWith<$Res> {
  factory _$MonthlyPerformanceCopyWith(_MonthlyPerformance value, $Res Function(_MonthlyPerformance) _then) = __$MonthlyPerformanceCopyWithImpl;
@override @useResult
$Res call({
 String id, int year, int month, int totalCalls, int connectedCalls, int totalTalkTimeMinutes, int validLeads, int siteVisits, int bookings, double totalRevenue, double baseSalary, double commissionEarned, double incentives, double deductions, double totalEarnings, double targetAchievementPercentage, int ranking, double avgCallsPerDay, double avgTalkTimePerDay, double avgLeadsPerDay, double leadQualityScore, double conversionRate, PaymentStatus paymentStatus, DateTime? paidAt
});




}
/// @nodoc
class __$MonthlyPerformanceCopyWithImpl<$Res>
    implements _$MonthlyPerformanceCopyWith<$Res> {
  __$MonthlyPerformanceCopyWithImpl(this._self, this._then);

  final _MonthlyPerformance _self;
  final $Res Function(_MonthlyPerformance) _then;

/// Create a copy of MonthlyPerformance
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? year = null,Object? month = null,Object? totalCalls = null,Object? connectedCalls = null,Object? totalTalkTimeMinutes = null,Object? validLeads = null,Object? siteVisits = null,Object? bookings = null,Object? totalRevenue = null,Object? baseSalary = null,Object? commissionEarned = null,Object? incentives = null,Object? deductions = null,Object? totalEarnings = null,Object? targetAchievementPercentage = null,Object? ranking = null,Object? avgCallsPerDay = null,Object? avgTalkTimePerDay = null,Object? avgLeadsPerDay = null,Object? leadQualityScore = null,Object? conversionRate = null,Object? paymentStatus = null,Object? paidAt = freezed,}) {
  return _then(_MonthlyPerformance(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,year: null == year ? _self.year : year // ignore: cast_nullable_to_non_nullable
as int,month: null == month ? _self.month : month // ignore: cast_nullable_to_non_nullable
as int,totalCalls: null == totalCalls ? _self.totalCalls : totalCalls // ignore: cast_nullable_to_non_nullable
as int,connectedCalls: null == connectedCalls ? _self.connectedCalls : connectedCalls // ignore: cast_nullable_to_non_nullable
as int,totalTalkTimeMinutes: null == totalTalkTimeMinutes ? _self.totalTalkTimeMinutes : totalTalkTimeMinutes // ignore: cast_nullable_to_non_nullable
as int,validLeads: null == validLeads ? _self.validLeads : validLeads // ignore: cast_nullable_to_non_nullable
as int,siteVisits: null == siteVisits ? _self.siteVisits : siteVisits // ignore: cast_nullable_to_non_nullable
as int,bookings: null == bookings ? _self.bookings : bookings // ignore: cast_nullable_to_non_nullable
as int,totalRevenue: null == totalRevenue ? _self.totalRevenue : totalRevenue // ignore: cast_nullable_to_non_nullable
as double,baseSalary: null == baseSalary ? _self.baseSalary : baseSalary // ignore: cast_nullable_to_non_nullable
as double,commissionEarned: null == commissionEarned ? _self.commissionEarned : commissionEarned // ignore: cast_nullable_to_non_nullable
as double,incentives: null == incentives ? _self.incentives : incentives // ignore: cast_nullable_to_non_nullable
as double,deductions: null == deductions ? _self.deductions : deductions // ignore: cast_nullable_to_non_nullable
as double,totalEarnings: null == totalEarnings ? _self.totalEarnings : totalEarnings // ignore: cast_nullable_to_non_nullable
as double,targetAchievementPercentage: null == targetAchievementPercentage ? _self.targetAchievementPercentage : targetAchievementPercentage // ignore: cast_nullable_to_non_nullable
as double,ranking: null == ranking ? _self.ranking : ranking // ignore: cast_nullable_to_non_nullable
as int,avgCallsPerDay: null == avgCallsPerDay ? _self.avgCallsPerDay : avgCallsPerDay // ignore: cast_nullable_to_non_nullable
as double,avgTalkTimePerDay: null == avgTalkTimePerDay ? _self.avgTalkTimePerDay : avgTalkTimePerDay // ignore: cast_nullable_to_non_nullable
as double,avgLeadsPerDay: null == avgLeadsPerDay ? _self.avgLeadsPerDay : avgLeadsPerDay // ignore: cast_nullable_to_non_nullable
as double,leadQualityScore: null == leadQualityScore ? _self.leadQualityScore : leadQualityScore // ignore: cast_nullable_to_non_nullable
as double,conversionRate: null == conversionRate ? _self.conversionRate : conversionRate // ignore: cast_nullable_to_non_nullable
as double,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as PaymentStatus,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$CallerLeadAssignment {

 String get leadId; String get leadName; String get leadPhone; DateTime get assignedAt; String get assignedBy; AssignmentPriority? get priority;// High, Medium, Low
 DateTime? get dueDate; List<String> get tags; String? get notes; bool get isCompleted; DateTime? get completedAt; String? get outcome;
/// Create a copy of CallerLeadAssignment
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CallerLeadAssignmentCopyWith<CallerLeadAssignment> get copyWith => _$CallerLeadAssignmentCopyWithImpl<CallerLeadAssignment>(this as CallerLeadAssignment, _$identity);

  /// Serializes this CallerLeadAssignment to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CallerLeadAssignment&&(identical(other.leadId, leadId) || other.leadId == leadId)&&(identical(other.leadName, leadName) || other.leadName == leadName)&&(identical(other.leadPhone, leadPhone) || other.leadPhone == leadPhone)&&(identical(other.assignedAt, assignedAt) || other.assignedAt == assignedAt)&&(identical(other.assignedBy, assignedBy) || other.assignedBy == assignedBy)&&(identical(other.priority, priority) || other.priority == priority)&&(identical(other.dueDate, dueDate) || other.dueDate == dueDate)&&const DeepCollectionEquality().equals(other.tags, tags)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.isCompleted, isCompleted) || other.isCompleted == isCompleted)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt)&&(identical(other.outcome, outcome) || other.outcome == outcome));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,leadId,leadName,leadPhone,assignedAt,assignedBy,priority,dueDate,const DeepCollectionEquality().hash(tags),notes,isCompleted,completedAt,outcome);

@override
String toString() {
  return 'CallerLeadAssignment(leadId: $leadId, leadName: $leadName, leadPhone: $leadPhone, assignedAt: $assignedAt, assignedBy: $assignedBy, priority: $priority, dueDate: $dueDate, tags: $tags, notes: $notes, isCompleted: $isCompleted, completedAt: $completedAt, outcome: $outcome)';
}


}

/// @nodoc
abstract mixin class $CallerLeadAssignmentCopyWith<$Res>  {
  factory $CallerLeadAssignmentCopyWith(CallerLeadAssignment value, $Res Function(CallerLeadAssignment) _then) = _$CallerLeadAssignmentCopyWithImpl;
@useResult
$Res call({
 String leadId, String leadName, String leadPhone, DateTime assignedAt, String assignedBy, AssignmentPriority? priority, DateTime? dueDate, List<String> tags, String? notes, bool isCompleted, DateTime? completedAt, String? outcome
});




}
/// @nodoc
class _$CallerLeadAssignmentCopyWithImpl<$Res>
    implements $CallerLeadAssignmentCopyWith<$Res> {
  _$CallerLeadAssignmentCopyWithImpl(this._self, this._then);

  final CallerLeadAssignment _self;
  final $Res Function(CallerLeadAssignment) _then;

/// Create a copy of CallerLeadAssignment
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? leadId = null,Object? leadName = null,Object? leadPhone = null,Object? assignedAt = null,Object? assignedBy = null,Object? priority = freezed,Object? dueDate = freezed,Object? tags = null,Object? notes = freezed,Object? isCompleted = null,Object? completedAt = freezed,Object? outcome = freezed,}) {
  return _then(_self.copyWith(
leadId: null == leadId ? _self.leadId : leadId // ignore: cast_nullable_to_non_nullable
as String,leadName: null == leadName ? _self.leadName : leadName // ignore: cast_nullable_to_non_nullable
as String,leadPhone: null == leadPhone ? _self.leadPhone : leadPhone // ignore: cast_nullable_to_non_nullable
as String,assignedAt: null == assignedAt ? _self.assignedAt : assignedAt // ignore: cast_nullable_to_non_nullable
as DateTime,assignedBy: null == assignedBy ? _self.assignedBy : assignedBy // ignore: cast_nullable_to_non_nullable
as String,priority: freezed == priority ? _self.priority : priority // ignore: cast_nullable_to_non_nullable
as AssignmentPriority?,dueDate: freezed == dueDate ? _self.dueDate : dueDate // ignore: cast_nullable_to_non_nullable
as DateTime?,tags: null == tags ? _self.tags : tags // ignore: cast_nullable_to_non_nullable
as List<String>,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,isCompleted: null == isCompleted ? _self.isCompleted : isCompleted // ignore: cast_nullable_to_non_nullable
as bool,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,outcome: freezed == outcome ? _self.outcome : outcome // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [CallerLeadAssignment].
extension CallerLeadAssignmentPatterns on CallerLeadAssignment {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CallerLeadAssignment value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CallerLeadAssignment() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CallerLeadAssignment value)  $default,){
final _that = this;
switch (_that) {
case _CallerLeadAssignment():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CallerLeadAssignment value)?  $default,){
final _that = this;
switch (_that) {
case _CallerLeadAssignment() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String leadId,  String leadName,  String leadPhone,  DateTime assignedAt,  String assignedBy,  AssignmentPriority? priority,  DateTime? dueDate,  List<String> tags,  String? notes,  bool isCompleted,  DateTime? completedAt,  String? outcome)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CallerLeadAssignment() when $default != null:
return $default(_that.leadId,_that.leadName,_that.leadPhone,_that.assignedAt,_that.assignedBy,_that.priority,_that.dueDate,_that.tags,_that.notes,_that.isCompleted,_that.completedAt,_that.outcome);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String leadId,  String leadName,  String leadPhone,  DateTime assignedAt,  String assignedBy,  AssignmentPriority? priority,  DateTime? dueDate,  List<String> tags,  String? notes,  bool isCompleted,  DateTime? completedAt,  String? outcome)  $default,) {final _that = this;
switch (_that) {
case _CallerLeadAssignment():
return $default(_that.leadId,_that.leadName,_that.leadPhone,_that.assignedAt,_that.assignedBy,_that.priority,_that.dueDate,_that.tags,_that.notes,_that.isCompleted,_that.completedAt,_that.outcome);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String leadId,  String leadName,  String leadPhone,  DateTime assignedAt,  String assignedBy,  AssignmentPriority? priority,  DateTime? dueDate,  List<String> tags,  String? notes,  bool isCompleted,  DateTime? completedAt,  String? outcome)?  $default,) {final _that = this;
switch (_that) {
case _CallerLeadAssignment() when $default != null:
return $default(_that.leadId,_that.leadName,_that.leadPhone,_that.assignedAt,_that.assignedBy,_that.priority,_that.dueDate,_that.tags,_that.notes,_that.isCompleted,_that.completedAt,_that.outcome);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CallerLeadAssignment implements CallerLeadAssignment {
  const _CallerLeadAssignment({this.leadId = '', this.leadName = '', this.leadPhone = '', required this.assignedAt, this.assignedBy = '', this.priority, this.dueDate, final  List<String> tags = const [], this.notes, this.isCompleted = false, this.completedAt, this.outcome}): _tags = tags;
  factory _CallerLeadAssignment.fromJson(Map<String, dynamic> json) => _$CallerLeadAssignmentFromJson(json);

@override@JsonKey() final  String leadId;
@override@JsonKey() final  String leadName;
@override@JsonKey() final  String leadPhone;
@override final  DateTime assignedAt;
@override@JsonKey() final  String assignedBy;
@override final  AssignmentPriority? priority;
// High, Medium, Low
@override final  DateTime? dueDate;
 final  List<String> _tags;
@override@JsonKey() List<String> get tags {
  if (_tags is EqualUnmodifiableListView) return _tags;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_tags);
}

@override final  String? notes;
@override@JsonKey() final  bool isCompleted;
@override final  DateTime? completedAt;
@override final  String? outcome;

/// Create a copy of CallerLeadAssignment
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CallerLeadAssignmentCopyWith<_CallerLeadAssignment> get copyWith => __$CallerLeadAssignmentCopyWithImpl<_CallerLeadAssignment>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CallerLeadAssignmentToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _CallerLeadAssignment&&(identical(other.leadId, leadId) || other.leadId == leadId)&&(identical(other.leadName, leadName) || other.leadName == leadName)&&(identical(other.leadPhone, leadPhone) || other.leadPhone == leadPhone)&&(identical(other.assignedAt, assignedAt) || other.assignedAt == assignedAt)&&(identical(other.assignedBy, assignedBy) || other.assignedBy == assignedBy)&&(identical(other.priority, priority) || other.priority == priority)&&(identical(other.dueDate, dueDate) || other.dueDate == dueDate)&&const DeepCollectionEquality().equals(other._tags, _tags)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.isCompleted, isCompleted) || other.isCompleted == isCompleted)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt)&&(identical(other.outcome, outcome) || other.outcome == outcome));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,leadId,leadName,leadPhone,assignedAt,assignedBy,priority,dueDate,const DeepCollectionEquality().hash(_tags),notes,isCompleted,completedAt,outcome);

@override
String toString() {
  return 'CallerLeadAssignment(leadId: $leadId, leadName: $leadName, leadPhone: $leadPhone, assignedAt: $assignedAt, assignedBy: $assignedBy, priority: $priority, dueDate: $dueDate, tags: $tags, notes: $notes, isCompleted: $isCompleted, completedAt: $completedAt, outcome: $outcome)';
}


}

/// @nodoc
abstract mixin class _$CallerLeadAssignmentCopyWith<$Res> implements $CallerLeadAssignmentCopyWith<$Res> {
  factory _$CallerLeadAssignmentCopyWith(_CallerLeadAssignment value, $Res Function(_CallerLeadAssignment) _then) = __$CallerLeadAssignmentCopyWithImpl;
@override @useResult
$Res call({
 String leadId, String leadName, String leadPhone, DateTime assignedAt, String assignedBy, AssignmentPriority? priority, DateTime? dueDate, List<String> tags, String? notes, bool isCompleted, DateTime? completedAt, String? outcome
});




}
/// @nodoc
class __$CallerLeadAssignmentCopyWithImpl<$Res>
    implements _$CallerLeadAssignmentCopyWith<$Res> {
  __$CallerLeadAssignmentCopyWithImpl(this._self, this._then);

  final _CallerLeadAssignment _self;
  final $Res Function(_CallerLeadAssignment) _then;

/// Create a copy of CallerLeadAssignment
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? leadId = null,Object? leadName = null,Object? leadPhone = null,Object? assignedAt = null,Object? assignedBy = null,Object? priority = freezed,Object? dueDate = freezed,Object? tags = null,Object? notes = freezed,Object? isCompleted = null,Object? completedAt = freezed,Object? outcome = freezed,}) {
  return _then(_CallerLeadAssignment(
leadId: null == leadId ? _self.leadId : leadId // ignore: cast_nullable_to_non_nullable
as String,leadName: null == leadName ? _self.leadName : leadName // ignore: cast_nullable_to_non_nullable
as String,leadPhone: null == leadPhone ? _self.leadPhone : leadPhone // ignore: cast_nullable_to_non_nullable
as String,assignedAt: null == assignedAt ? _self.assignedAt : assignedAt // ignore: cast_nullable_to_non_nullable
as DateTime,assignedBy: null == assignedBy ? _self.assignedBy : assignedBy // ignore: cast_nullable_to_non_nullable
as String,priority: freezed == priority ? _self.priority : priority // ignore: cast_nullable_to_non_nullable
as AssignmentPriority?,dueDate: freezed == dueDate ? _self.dueDate : dueDate // ignore: cast_nullable_to_non_nullable
as DateTime?,tags: null == tags ? _self._tags : tags // ignore: cast_nullable_to_non_nullable
as List<String>,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,isCompleted: null == isCompleted ? _self.isCompleted : isCompleted // ignore: cast_nullable_to_non_nullable
as bool,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,outcome: freezed == outcome ? _self.outcome : outcome // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$LeadDistributionBatch {

 String get id; String get batchName; DateTime get createdAt; String get createdBy;// Lead Sources
 List<String> get leadSourceIds;// From campaigns, website, etc.
 List<Map<String, dynamic>> get importedLeads;// Distribution
 List<String> get assignedCallerIds; int? get leadsPerCaller; DistributionMethod get method;// Equal, PriorityBased, Random, RoundRobin
// Status
 DistributionStatus get status; DateTime? get distributedAt;// Results
 List<String> get distributedLeadIds; int get totalLeads;
/// Create a copy of LeadDistributionBatch
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LeadDistributionBatchCopyWith<LeadDistributionBatch> get copyWith => _$LeadDistributionBatchCopyWithImpl<LeadDistributionBatch>(this as LeadDistributionBatch, _$identity);

  /// Serializes this LeadDistributionBatch to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is LeadDistributionBatch&&(identical(other.id, id) || other.id == id)&&(identical(other.batchName, batchName) || other.batchName == batchName)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.createdBy, createdBy) || other.createdBy == createdBy)&&const DeepCollectionEquality().equals(other.leadSourceIds, leadSourceIds)&&const DeepCollectionEquality().equals(other.importedLeads, importedLeads)&&const DeepCollectionEquality().equals(other.assignedCallerIds, assignedCallerIds)&&(identical(other.leadsPerCaller, leadsPerCaller) || other.leadsPerCaller == leadsPerCaller)&&(identical(other.method, method) || other.method == method)&&(identical(other.status, status) || other.status == status)&&(identical(other.distributedAt, distributedAt) || other.distributedAt == distributedAt)&&const DeepCollectionEquality().equals(other.distributedLeadIds, distributedLeadIds)&&(identical(other.totalLeads, totalLeads) || other.totalLeads == totalLeads));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,batchName,createdAt,createdBy,const DeepCollectionEquality().hash(leadSourceIds),const DeepCollectionEquality().hash(importedLeads),const DeepCollectionEquality().hash(assignedCallerIds),leadsPerCaller,method,status,distributedAt,const DeepCollectionEquality().hash(distributedLeadIds),totalLeads);

@override
String toString() {
  return 'LeadDistributionBatch(id: $id, batchName: $batchName, createdAt: $createdAt, createdBy: $createdBy, leadSourceIds: $leadSourceIds, importedLeads: $importedLeads, assignedCallerIds: $assignedCallerIds, leadsPerCaller: $leadsPerCaller, method: $method, status: $status, distributedAt: $distributedAt, distributedLeadIds: $distributedLeadIds, totalLeads: $totalLeads)';
}


}

/// @nodoc
abstract mixin class $LeadDistributionBatchCopyWith<$Res>  {
  factory $LeadDistributionBatchCopyWith(LeadDistributionBatch value, $Res Function(LeadDistributionBatch) _then) = _$LeadDistributionBatchCopyWithImpl;
@useResult
$Res call({
 String id, String batchName, DateTime createdAt, String createdBy, List<String> leadSourceIds, List<Map<String, dynamic>> importedLeads, List<String> assignedCallerIds, int? leadsPerCaller, DistributionMethod method, DistributionStatus status, DateTime? distributedAt, List<String> distributedLeadIds, int totalLeads
});




}
/// @nodoc
class _$LeadDistributionBatchCopyWithImpl<$Res>
    implements $LeadDistributionBatchCopyWith<$Res> {
  _$LeadDistributionBatchCopyWithImpl(this._self, this._then);

  final LeadDistributionBatch _self;
  final $Res Function(LeadDistributionBatch) _then;

/// Create a copy of LeadDistributionBatch
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? batchName = null,Object? createdAt = null,Object? createdBy = null,Object? leadSourceIds = null,Object? importedLeads = null,Object? assignedCallerIds = null,Object? leadsPerCaller = freezed,Object? method = null,Object? status = null,Object? distributedAt = freezed,Object? distributedLeadIds = null,Object? totalLeads = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,batchName: null == batchName ? _self.batchName : batchName // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,createdBy: null == createdBy ? _self.createdBy : createdBy // ignore: cast_nullable_to_non_nullable
as String,leadSourceIds: null == leadSourceIds ? _self.leadSourceIds : leadSourceIds // ignore: cast_nullable_to_non_nullable
as List<String>,importedLeads: null == importedLeads ? _self.importedLeads : importedLeads // ignore: cast_nullable_to_non_nullable
as List<Map<String, dynamic>>,assignedCallerIds: null == assignedCallerIds ? _self.assignedCallerIds : assignedCallerIds // ignore: cast_nullable_to_non_nullable
as List<String>,leadsPerCaller: freezed == leadsPerCaller ? _self.leadsPerCaller : leadsPerCaller // ignore: cast_nullable_to_non_nullable
as int?,method: null == method ? _self.method : method // ignore: cast_nullable_to_non_nullable
as DistributionMethod,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as DistributionStatus,distributedAt: freezed == distributedAt ? _self.distributedAt : distributedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,distributedLeadIds: null == distributedLeadIds ? _self.distributedLeadIds : distributedLeadIds // ignore: cast_nullable_to_non_nullable
as List<String>,totalLeads: null == totalLeads ? _self.totalLeads : totalLeads // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [LeadDistributionBatch].
extension LeadDistributionBatchPatterns on LeadDistributionBatch {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _LeadDistributionBatch value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _LeadDistributionBatch() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _LeadDistributionBatch value)  $default,){
final _that = this;
switch (_that) {
case _LeadDistributionBatch():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _LeadDistributionBatch value)?  $default,){
final _that = this;
switch (_that) {
case _LeadDistributionBatch() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String batchName,  DateTime createdAt,  String createdBy,  List<String> leadSourceIds,  List<Map<String, dynamic>> importedLeads,  List<String> assignedCallerIds,  int? leadsPerCaller,  DistributionMethod method,  DistributionStatus status,  DateTime? distributedAt,  List<String> distributedLeadIds,  int totalLeads)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _LeadDistributionBatch() when $default != null:
return $default(_that.id,_that.batchName,_that.createdAt,_that.createdBy,_that.leadSourceIds,_that.importedLeads,_that.assignedCallerIds,_that.leadsPerCaller,_that.method,_that.status,_that.distributedAt,_that.distributedLeadIds,_that.totalLeads);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String batchName,  DateTime createdAt,  String createdBy,  List<String> leadSourceIds,  List<Map<String, dynamic>> importedLeads,  List<String> assignedCallerIds,  int? leadsPerCaller,  DistributionMethod method,  DistributionStatus status,  DateTime? distributedAt,  List<String> distributedLeadIds,  int totalLeads)  $default,) {final _that = this;
switch (_that) {
case _LeadDistributionBatch():
return $default(_that.id,_that.batchName,_that.createdAt,_that.createdBy,_that.leadSourceIds,_that.importedLeads,_that.assignedCallerIds,_that.leadsPerCaller,_that.method,_that.status,_that.distributedAt,_that.distributedLeadIds,_that.totalLeads);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String batchName,  DateTime createdAt,  String createdBy,  List<String> leadSourceIds,  List<Map<String, dynamic>> importedLeads,  List<String> assignedCallerIds,  int? leadsPerCaller,  DistributionMethod method,  DistributionStatus status,  DateTime? distributedAt,  List<String> distributedLeadIds,  int totalLeads)?  $default,) {final _that = this;
switch (_that) {
case _LeadDistributionBatch() when $default != null:
return $default(_that.id,_that.batchName,_that.createdAt,_that.createdBy,_that.leadSourceIds,_that.importedLeads,_that.assignedCallerIds,_that.leadsPerCaller,_that.method,_that.status,_that.distributedAt,_that.distributedLeadIds,_that.totalLeads);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _LeadDistributionBatch implements LeadDistributionBatch {
  const _LeadDistributionBatch({this.id = '', this.batchName = '', required this.createdAt, this.createdBy = '', final  List<String> leadSourceIds = const [], final  List<Map<String, dynamic>> importedLeads = const [], final  List<String> assignedCallerIds = const [], this.leadsPerCaller, this.method = DistributionMethod.equal, required this.status, this.distributedAt, final  List<String> distributedLeadIds = const [], this.totalLeads = 0}): _leadSourceIds = leadSourceIds,_importedLeads = importedLeads,_assignedCallerIds = assignedCallerIds,_distributedLeadIds = distributedLeadIds;
  factory _LeadDistributionBatch.fromJson(Map<String, dynamic> json) => _$LeadDistributionBatchFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String batchName;
@override final  DateTime createdAt;
@override@JsonKey() final  String createdBy;
// Lead Sources
 final  List<String> _leadSourceIds;
// Lead Sources
@override@JsonKey() List<String> get leadSourceIds {
  if (_leadSourceIds is EqualUnmodifiableListView) return _leadSourceIds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_leadSourceIds);
}

// From campaigns, website, etc.
 final  List<Map<String, dynamic>> _importedLeads;
// From campaigns, website, etc.
@override@JsonKey() List<Map<String, dynamic>> get importedLeads {
  if (_importedLeads is EqualUnmodifiableListView) return _importedLeads;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_importedLeads);
}

// Distribution
 final  List<String> _assignedCallerIds;
// Distribution
@override@JsonKey() List<String> get assignedCallerIds {
  if (_assignedCallerIds is EqualUnmodifiableListView) return _assignedCallerIds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_assignedCallerIds);
}

@override final  int? leadsPerCaller;
@override@JsonKey() final  DistributionMethod method;
// Equal, PriorityBased, Random, RoundRobin
// Status
@override final  DistributionStatus status;
@override final  DateTime? distributedAt;
// Results
 final  List<String> _distributedLeadIds;
// Results
@override@JsonKey() List<String> get distributedLeadIds {
  if (_distributedLeadIds is EqualUnmodifiableListView) return _distributedLeadIds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_distributedLeadIds);
}

@override@JsonKey() final  int totalLeads;

/// Create a copy of LeadDistributionBatch
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$LeadDistributionBatchCopyWith<_LeadDistributionBatch> get copyWith => __$LeadDistributionBatchCopyWithImpl<_LeadDistributionBatch>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$LeadDistributionBatchToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _LeadDistributionBatch&&(identical(other.id, id) || other.id == id)&&(identical(other.batchName, batchName) || other.batchName == batchName)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.createdBy, createdBy) || other.createdBy == createdBy)&&const DeepCollectionEquality().equals(other._leadSourceIds, _leadSourceIds)&&const DeepCollectionEquality().equals(other._importedLeads, _importedLeads)&&const DeepCollectionEquality().equals(other._assignedCallerIds, _assignedCallerIds)&&(identical(other.leadsPerCaller, leadsPerCaller) || other.leadsPerCaller == leadsPerCaller)&&(identical(other.method, method) || other.method == method)&&(identical(other.status, status) || other.status == status)&&(identical(other.distributedAt, distributedAt) || other.distributedAt == distributedAt)&&const DeepCollectionEquality().equals(other._distributedLeadIds, _distributedLeadIds)&&(identical(other.totalLeads, totalLeads) || other.totalLeads == totalLeads));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,batchName,createdAt,createdBy,const DeepCollectionEquality().hash(_leadSourceIds),const DeepCollectionEquality().hash(_importedLeads),const DeepCollectionEquality().hash(_assignedCallerIds),leadsPerCaller,method,status,distributedAt,const DeepCollectionEquality().hash(_distributedLeadIds),totalLeads);

@override
String toString() {
  return 'LeadDistributionBatch(id: $id, batchName: $batchName, createdAt: $createdAt, createdBy: $createdBy, leadSourceIds: $leadSourceIds, importedLeads: $importedLeads, assignedCallerIds: $assignedCallerIds, leadsPerCaller: $leadsPerCaller, method: $method, status: $status, distributedAt: $distributedAt, distributedLeadIds: $distributedLeadIds, totalLeads: $totalLeads)';
}


}

/// @nodoc
abstract mixin class _$LeadDistributionBatchCopyWith<$Res> implements $LeadDistributionBatchCopyWith<$Res> {
  factory _$LeadDistributionBatchCopyWith(_LeadDistributionBatch value, $Res Function(_LeadDistributionBatch) _then) = __$LeadDistributionBatchCopyWithImpl;
@override @useResult
$Res call({
 String id, String batchName, DateTime createdAt, String createdBy, List<String> leadSourceIds, List<Map<String, dynamic>> importedLeads, List<String> assignedCallerIds, int? leadsPerCaller, DistributionMethod method, DistributionStatus status, DateTime? distributedAt, List<String> distributedLeadIds, int totalLeads
});




}
/// @nodoc
class __$LeadDistributionBatchCopyWithImpl<$Res>
    implements _$LeadDistributionBatchCopyWith<$Res> {
  __$LeadDistributionBatchCopyWithImpl(this._self, this._then);

  final _LeadDistributionBatch _self;
  final $Res Function(_LeadDistributionBatch) _then;

/// Create a copy of LeadDistributionBatch
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? batchName = null,Object? createdAt = null,Object? createdBy = null,Object? leadSourceIds = null,Object? importedLeads = null,Object? assignedCallerIds = null,Object? leadsPerCaller = freezed,Object? method = null,Object? status = null,Object? distributedAt = freezed,Object? distributedLeadIds = null,Object? totalLeads = null,}) {
  return _then(_LeadDistributionBatch(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,batchName: null == batchName ? _self.batchName : batchName // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,createdBy: null == createdBy ? _self.createdBy : createdBy // ignore: cast_nullable_to_non_nullable
as String,leadSourceIds: null == leadSourceIds ? _self._leadSourceIds : leadSourceIds // ignore: cast_nullable_to_non_nullable
as List<String>,importedLeads: null == importedLeads ? _self._importedLeads : importedLeads // ignore: cast_nullable_to_non_nullable
as List<Map<String, dynamic>>,assignedCallerIds: null == assignedCallerIds ? _self._assignedCallerIds : assignedCallerIds // ignore: cast_nullable_to_non_nullable
as List<String>,leadsPerCaller: freezed == leadsPerCaller ? _self.leadsPerCaller : leadsPerCaller // ignore: cast_nullable_to_non_nullable
as int?,method: null == method ? _self.method : method // ignore: cast_nullable_to_non_nullable
as DistributionMethod,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as DistributionStatus,distributedAt: freezed == distributedAt ? _self.distributedAt : distributedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,distributedLeadIds: null == distributedLeadIds ? _self._distributedLeadIds : distributedLeadIds // ignore: cast_nullable_to_non_nullable
as List<String>,totalLeads: null == totalLeads ? _self.totalLeads : totalLeads // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

// dart format on
