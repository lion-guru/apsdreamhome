// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'emi_collection_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$EMICollectionAgent {

 String get id; String get name; String get phone; String get email; String? get photoUrl; String? get aadharNumber; String? get address;// Employment
 String get employeeId; DateTime get joiningDate; CollectionAgentType get agentType;// FullTime, PartTime, Freelance
 CollectionArea get assignedArea;// Salary Structure
 double get monthlySalary; double? get commissionPerCollection;// Per EMI collected
 double? get commissionPercentage;// % of collected amount
 double? get incentivePerTarget;// Bonus for target achievement
// Assigned Customers
 List<String> get assignedCustomerIds; List<EMICustomerAssignment> get customerAssignments;// Performance
 List<DailyCollectionReport> get dailyReports; List<MonthlyCollectionPerformance> get monthlyReports;// Current Month Stats
 int get currentMonthCollections; double get currentMonthAmount; double get currentMonthCommission; int get currentMonthTarget; double get targetAchievement;// Location Tracking
 List<LocationTracking> get locationHistory; bool? get isCurrentlyActive; GeoLocation? get lastLocation; DateTime? get lastLocationUpdate;// Status
 AgentStatus get status; DateTime? get lastActiveAt;// Documents
 List<String> get documentUrls; DateTime get createdAt; DateTime get updatedAt;
/// Create a copy of EMICollectionAgent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$EMICollectionAgentCopyWith<EMICollectionAgent> get copyWith => _$EMICollectionAgentCopyWithImpl<EMICollectionAgent>(this as EMICollectionAgent, _$identity);

  /// Serializes this EMICollectionAgent to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is EMICollectionAgent&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.email, email) || other.email == email)&&(identical(other.photoUrl, photoUrl) || other.photoUrl == photoUrl)&&(identical(other.aadharNumber, aadharNumber) || other.aadharNumber == aadharNumber)&&(identical(other.address, address) || other.address == address)&&(identical(other.employeeId, employeeId) || other.employeeId == employeeId)&&(identical(other.joiningDate, joiningDate) || other.joiningDate == joiningDate)&&(identical(other.agentType, agentType) || other.agentType == agentType)&&(identical(other.assignedArea, assignedArea) || other.assignedArea == assignedArea)&&(identical(other.monthlySalary, monthlySalary) || other.monthlySalary == monthlySalary)&&(identical(other.commissionPerCollection, commissionPerCollection) || other.commissionPerCollection == commissionPerCollection)&&(identical(other.commissionPercentage, commissionPercentage) || other.commissionPercentage == commissionPercentage)&&(identical(other.incentivePerTarget, incentivePerTarget) || other.incentivePerTarget == incentivePerTarget)&&const DeepCollectionEquality().equals(other.assignedCustomerIds, assignedCustomerIds)&&const DeepCollectionEquality().equals(other.customerAssignments, customerAssignments)&&const DeepCollectionEquality().equals(other.dailyReports, dailyReports)&&const DeepCollectionEquality().equals(other.monthlyReports, monthlyReports)&&(identical(other.currentMonthCollections, currentMonthCollections) || other.currentMonthCollections == currentMonthCollections)&&(identical(other.currentMonthAmount, currentMonthAmount) || other.currentMonthAmount == currentMonthAmount)&&(identical(other.currentMonthCommission, currentMonthCommission) || other.currentMonthCommission == currentMonthCommission)&&(identical(other.currentMonthTarget, currentMonthTarget) || other.currentMonthTarget == currentMonthTarget)&&(identical(other.targetAchievement, targetAchievement) || other.targetAchievement == targetAchievement)&&const DeepCollectionEquality().equals(other.locationHistory, locationHistory)&&(identical(other.isCurrentlyActive, isCurrentlyActive) || other.isCurrentlyActive == isCurrentlyActive)&&(identical(other.lastLocation, lastLocation) || other.lastLocation == lastLocation)&&(identical(other.lastLocationUpdate, lastLocationUpdate) || other.lastLocationUpdate == lastLocationUpdate)&&(identical(other.status, status) || other.status == status)&&(identical(other.lastActiveAt, lastActiveAt) || other.lastActiveAt == lastActiveAt)&&const DeepCollectionEquality().equals(other.documentUrls, documentUrls)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,name,phone,email,photoUrl,aadharNumber,address,employeeId,joiningDate,agentType,assignedArea,monthlySalary,commissionPerCollection,commissionPercentage,incentivePerTarget,const DeepCollectionEquality().hash(assignedCustomerIds),const DeepCollectionEquality().hash(customerAssignments),const DeepCollectionEquality().hash(dailyReports),const DeepCollectionEquality().hash(monthlyReports),currentMonthCollections,currentMonthAmount,currentMonthCommission,currentMonthTarget,targetAchievement,const DeepCollectionEquality().hash(locationHistory),isCurrentlyActive,lastLocation,lastLocationUpdate,status,lastActiveAt,const DeepCollectionEquality().hash(documentUrls),createdAt,updatedAt]);

@override
String toString() {
  return 'EMICollectionAgent(id: $id, name: $name, phone: $phone, email: $email, photoUrl: $photoUrl, aadharNumber: $aadharNumber, address: $address, employeeId: $employeeId, joiningDate: $joiningDate, agentType: $agentType, assignedArea: $assignedArea, monthlySalary: $monthlySalary, commissionPerCollection: $commissionPerCollection, commissionPercentage: $commissionPercentage, incentivePerTarget: $incentivePerTarget, assignedCustomerIds: $assignedCustomerIds, customerAssignments: $customerAssignments, dailyReports: $dailyReports, monthlyReports: $monthlyReports, currentMonthCollections: $currentMonthCollections, currentMonthAmount: $currentMonthAmount, currentMonthCommission: $currentMonthCommission, currentMonthTarget: $currentMonthTarget, targetAchievement: $targetAchievement, locationHistory: $locationHistory, isCurrentlyActive: $isCurrentlyActive, lastLocation: $lastLocation, lastLocationUpdate: $lastLocationUpdate, status: $status, lastActiveAt: $lastActiveAt, documentUrls: $documentUrls, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class $EMICollectionAgentCopyWith<$Res>  {
  factory $EMICollectionAgentCopyWith(EMICollectionAgent value, $Res Function(EMICollectionAgent) _then) = _$EMICollectionAgentCopyWithImpl;
@useResult
$Res call({
 String id, String name, String phone, String email, String? photoUrl, String? aadharNumber, String? address, String employeeId, DateTime joiningDate, CollectionAgentType agentType, CollectionArea assignedArea, double monthlySalary, double? commissionPerCollection, double? commissionPercentage, double? incentivePerTarget, List<String> assignedCustomerIds, List<EMICustomerAssignment> customerAssignments, List<DailyCollectionReport> dailyReports, List<MonthlyCollectionPerformance> monthlyReports, int currentMonthCollections, double currentMonthAmount, double currentMonthCommission, int currentMonthTarget, double targetAchievement, List<LocationTracking> locationHistory, bool? isCurrentlyActive, GeoLocation? lastLocation, DateTime? lastLocationUpdate, AgentStatus status, DateTime? lastActiveAt, List<String> documentUrls, DateTime createdAt, DateTime updatedAt
});


$CollectionAreaCopyWith<$Res> get assignedArea;

}
/// @nodoc
class _$EMICollectionAgentCopyWithImpl<$Res>
    implements $EMICollectionAgentCopyWith<$Res> {
  _$EMICollectionAgentCopyWithImpl(this._self, this._then);

  final EMICollectionAgent _self;
  final $Res Function(EMICollectionAgent) _then;

/// Create a copy of EMICollectionAgent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? phone = null,Object? email = null,Object? photoUrl = freezed,Object? aadharNumber = freezed,Object? address = freezed,Object? employeeId = null,Object? joiningDate = null,Object? agentType = null,Object? assignedArea = null,Object? monthlySalary = null,Object? commissionPerCollection = freezed,Object? commissionPercentage = freezed,Object? incentivePerTarget = freezed,Object? assignedCustomerIds = null,Object? customerAssignments = null,Object? dailyReports = null,Object? monthlyReports = null,Object? currentMonthCollections = null,Object? currentMonthAmount = null,Object? currentMonthCommission = null,Object? currentMonthTarget = null,Object? targetAchievement = null,Object? locationHistory = null,Object? isCurrentlyActive = freezed,Object? lastLocation = freezed,Object? lastLocationUpdate = freezed,Object? status = null,Object? lastActiveAt = freezed,Object? documentUrls = null,Object? createdAt = null,Object? updatedAt = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,photoUrl: freezed == photoUrl ? _self.photoUrl : photoUrl // ignore: cast_nullable_to_non_nullable
as String?,aadharNumber: freezed == aadharNumber ? _self.aadharNumber : aadharNumber // ignore: cast_nullable_to_non_nullable
as String?,address: freezed == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String?,employeeId: null == employeeId ? _self.employeeId : employeeId // ignore: cast_nullable_to_non_nullable
as String,joiningDate: null == joiningDate ? _self.joiningDate : joiningDate // ignore: cast_nullable_to_non_nullable
as DateTime,agentType: null == agentType ? _self.agentType : agentType // ignore: cast_nullable_to_non_nullable
as CollectionAgentType,assignedArea: null == assignedArea ? _self.assignedArea : assignedArea // ignore: cast_nullable_to_non_nullable
as CollectionArea,monthlySalary: null == monthlySalary ? _self.monthlySalary : monthlySalary // ignore: cast_nullable_to_non_nullable
as double,commissionPerCollection: freezed == commissionPerCollection ? _self.commissionPerCollection : commissionPerCollection // ignore: cast_nullable_to_non_nullable
as double?,commissionPercentage: freezed == commissionPercentage ? _self.commissionPercentage : commissionPercentage // ignore: cast_nullable_to_non_nullable
as double?,incentivePerTarget: freezed == incentivePerTarget ? _self.incentivePerTarget : incentivePerTarget // ignore: cast_nullable_to_non_nullable
as double?,assignedCustomerIds: null == assignedCustomerIds ? _self.assignedCustomerIds : assignedCustomerIds // ignore: cast_nullable_to_non_nullable
as List<String>,customerAssignments: null == customerAssignments ? _self.customerAssignments : customerAssignments // ignore: cast_nullable_to_non_nullable
as List<EMICustomerAssignment>,dailyReports: null == dailyReports ? _self.dailyReports : dailyReports // ignore: cast_nullable_to_non_nullable
as List<DailyCollectionReport>,monthlyReports: null == monthlyReports ? _self.monthlyReports : monthlyReports // ignore: cast_nullable_to_non_nullable
as List<MonthlyCollectionPerformance>,currentMonthCollections: null == currentMonthCollections ? _self.currentMonthCollections : currentMonthCollections // ignore: cast_nullable_to_non_nullable
as int,currentMonthAmount: null == currentMonthAmount ? _self.currentMonthAmount : currentMonthAmount // ignore: cast_nullable_to_non_nullable
as double,currentMonthCommission: null == currentMonthCommission ? _self.currentMonthCommission : currentMonthCommission // ignore: cast_nullable_to_non_nullable
as double,currentMonthTarget: null == currentMonthTarget ? _self.currentMonthTarget : currentMonthTarget // ignore: cast_nullable_to_non_nullable
as int,targetAchievement: null == targetAchievement ? _self.targetAchievement : targetAchievement // ignore: cast_nullable_to_non_nullable
as double,locationHistory: null == locationHistory ? _self.locationHistory : locationHistory // ignore: cast_nullable_to_non_nullable
as List<LocationTracking>,isCurrentlyActive: freezed == isCurrentlyActive ? _self.isCurrentlyActive : isCurrentlyActive // ignore: cast_nullable_to_non_nullable
as bool?,lastLocation: freezed == lastLocation ? _self.lastLocation : lastLocation // ignore: cast_nullable_to_non_nullable
as GeoLocation?,lastLocationUpdate: freezed == lastLocationUpdate ? _self.lastLocationUpdate : lastLocationUpdate // ignore: cast_nullable_to_non_nullable
as DateTime?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as AgentStatus,lastActiveAt: freezed == lastActiveAt ? _self.lastActiveAt : lastActiveAt // ignore: cast_nullable_to_non_nullable
as DateTime?,documentUrls: null == documentUrls ? _self.documentUrls : documentUrls // ignore: cast_nullable_to_non_nullable
as List<String>,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,updatedAt: null == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}
/// Create a copy of EMICollectionAgent
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CollectionAreaCopyWith<$Res> get assignedArea {
  
  return $CollectionAreaCopyWith<$Res>(_self.assignedArea, (value) {
    return _then(_self.copyWith(assignedArea: value));
  });
}
}


/// Adds pattern-matching-related methods to [EMICollectionAgent].
extension EMICollectionAgentPatterns on EMICollectionAgent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _EMICollectionAgent value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _EMICollectionAgent() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _EMICollectionAgent value)  $default,){
final _that = this;
switch (_that) {
case _EMICollectionAgent():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _EMICollectionAgent value)?  $default,){
final _that = this;
switch (_that) {
case _EMICollectionAgent() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String name,  String phone,  String email,  String? photoUrl,  String? aadharNumber,  String? address,  String employeeId,  DateTime joiningDate,  CollectionAgentType agentType,  CollectionArea assignedArea,  double monthlySalary,  double? commissionPerCollection,  double? commissionPercentage,  double? incentivePerTarget,  List<String> assignedCustomerIds,  List<EMICustomerAssignment> customerAssignments,  List<DailyCollectionReport> dailyReports,  List<MonthlyCollectionPerformance> monthlyReports,  int currentMonthCollections,  double currentMonthAmount,  double currentMonthCommission,  int currentMonthTarget,  double targetAchievement,  List<LocationTracking> locationHistory,  bool? isCurrentlyActive,  GeoLocation? lastLocation,  DateTime? lastLocationUpdate,  AgentStatus status,  DateTime? lastActiveAt,  List<String> documentUrls,  DateTime createdAt,  DateTime updatedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _EMICollectionAgent() when $default != null:
return $default(_that.id,_that.name,_that.phone,_that.email,_that.photoUrl,_that.aadharNumber,_that.address,_that.employeeId,_that.joiningDate,_that.agentType,_that.assignedArea,_that.monthlySalary,_that.commissionPerCollection,_that.commissionPercentage,_that.incentivePerTarget,_that.assignedCustomerIds,_that.customerAssignments,_that.dailyReports,_that.monthlyReports,_that.currentMonthCollections,_that.currentMonthAmount,_that.currentMonthCommission,_that.currentMonthTarget,_that.targetAchievement,_that.locationHistory,_that.isCurrentlyActive,_that.lastLocation,_that.lastLocationUpdate,_that.status,_that.lastActiveAt,_that.documentUrls,_that.createdAt,_that.updatedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String name,  String phone,  String email,  String? photoUrl,  String? aadharNumber,  String? address,  String employeeId,  DateTime joiningDate,  CollectionAgentType agentType,  CollectionArea assignedArea,  double monthlySalary,  double? commissionPerCollection,  double? commissionPercentage,  double? incentivePerTarget,  List<String> assignedCustomerIds,  List<EMICustomerAssignment> customerAssignments,  List<DailyCollectionReport> dailyReports,  List<MonthlyCollectionPerformance> monthlyReports,  int currentMonthCollections,  double currentMonthAmount,  double currentMonthCommission,  int currentMonthTarget,  double targetAchievement,  List<LocationTracking> locationHistory,  bool? isCurrentlyActive,  GeoLocation? lastLocation,  DateTime? lastLocationUpdate,  AgentStatus status,  DateTime? lastActiveAt,  List<String> documentUrls,  DateTime createdAt,  DateTime updatedAt)  $default,) {final _that = this;
switch (_that) {
case _EMICollectionAgent():
return $default(_that.id,_that.name,_that.phone,_that.email,_that.photoUrl,_that.aadharNumber,_that.address,_that.employeeId,_that.joiningDate,_that.agentType,_that.assignedArea,_that.monthlySalary,_that.commissionPerCollection,_that.commissionPercentage,_that.incentivePerTarget,_that.assignedCustomerIds,_that.customerAssignments,_that.dailyReports,_that.monthlyReports,_that.currentMonthCollections,_that.currentMonthAmount,_that.currentMonthCommission,_that.currentMonthTarget,_that.targetAchievement,_that.locationHistory,_that.isCurrentlyActive,_that.lastLocation,_that.lastLocationUpdate,_that.status,_that.lastActiveAt,_that.documentUrls,_that.createdAt,_that.updatedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String name,  String phone,  String email,  String? photoUrl,  String? aadharNumber,  String? address,  String employeeId,  DateTime joiningDate,  CollectionAgentType agentType,  CollectionArea assignedArea,  double monthlySalary,  double? commissionPerCollection,  double? commissionPercentage,  double? incentivePerTarget,  List<String> assignedCustomerIds,  List<EMICustomerAssignment> customerAssignments,  List<DailyCollectionReport> dailyReports,  List<MonthlyCollectionPerformance> monthlyReports,  int currentMonthCollections,  double currentMonthAmount,  double currentMonthCommission,  int currentMonthTarget,  double targetAchievement,  List<LocationTracking> locationHistory,  bool? isCurrentlyActive,  GeoLocation? lastLocation,  DateTime? lastLocationUpdate,  AgentStatus status,  DateTime? lastActiveAt,  List<String> documentUrls,  DateTime createdAt,  DateTime updatedAt)?  $default,) {final _that = this;
switch (_that) {
case _EMICollectionAgent() when $default != null:
return $default(_that.id,_that.name,_that.phone,_that.email,_that.photoUrl,_that.aadharNumber,_that.address,_that.employeeId,_that.joiningDate,_that.agentType,_that.assignedArea,_that.monthlySalary,_that.commissionPerCollection,_that.commissionPercentage,_that.incentivePerTarget,_that.assignedCustomerIds,_that.customerAssignments,_that.dailyReports,_that.monthlyReports,_that.currentMonthCollections,_that.currentMonthAmount,_that.currentMonthCommission,_that.currentMonthTarget,_that.targetAchievement,_that.locationHistory,_that.isCurrentlyActive,_that.lastLocation,_that.lastLocationUpdate,_that.status,_that.lastActiveAt,_that.documentUrls,_that.createdAt,_that.updatedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _EMICollectionAgent implements EMICollectionAgent {
  const _EMICollectionAgent({required this.id, required this.name, required this.phone, required this.email, this.photoUrl, this.aadharNumber, this.address, required this.employeeId, required this.joiningDate, required this.agentType, required this.assignedArea, required this.monthlySalary, this.commissionPerCollection, this.commissionPercentage, this.incentivePerTarget, final  List<String> assignedCustomerIds = const [], final  List<EMICustomerAssignment> customerAssignments = const [], final  List<DailyCollectionReport> dailyReports = const [], final  List<MonthlyCollectionPerformance> monthlyReports = const [], this.currentMonthCollections = 0, this.currentMonthAmount = 0, this.currentMonthCommission = 0, this.currentMonthTarget = 0, this.targetAchievement = 0, final  List<LocationTracking> locationHistory = const [], this.isCurrentlyActive, this.lastLocation, this.lastLocationUpdate, required this.status, this.lastActiveAt, final  List<String> documentUrls = const [], required this.createdAt, required this.updatedAt}): _assignedCustomerIds = assignedCustomerIds,_customerAssignments = customerAssignments,_dailyReports = dailyReports,_monthlyReports = monthlyReports,_locationHistory = locationHistory,_documentUrls = documentUrls;
  factory _EMICollectionAgent.fromJson(Map<String, dynamic> json) => _$EMICollectionAgentFromJson(json);

@override final  String id;
@override final  String name;
@override final  String phone;
@override final  String email;
@override final  String? photoUrl;
@override final  String? aadharNumber;
@override final  String? address;
// Employment
@override final  String employeeId;
@override final  DateTime joiningDate;
@override final  CollectionAgentType agentType;
// FullTime, PartTime, Freelance
@override final  CollectionArea assignedArea;
// Salary Structure
@override final  double monthlySalary;
@override final  double? commissionPerCollection;
// Per EMI collected
@override final  double? commissionPercentage;
// % of collected amount
@override final  double? incentivePerTarget;
// Bonus for target achievement
// Assigned Customers
 final  List<String> _assignedCustomerIds;
// Bonus for target achievement
// Assigned Customers
@override@JsonKey() List<String> get assignedCustomerIds {
  if (_assignedCustomerIds is EqualUnmodifiableListView) return _assignedCustomerIds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_assignedCustomerIds);
}

 final  List<EMICustomerAssignment> _customerAssignments;
@override@JsonKey() List<EMICustomerAssignment> get customerAssignments {
  if (_customerAssignments is EqualUnmodifiableListView) return _customerAssignments;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_customerAssignments);
}

// Performance
 final  List<DailyCollectionReport> _dailyReports;
// Performance
@override@JsonKey() List<DailyCollectionReport> get dailyReports {
  if (_dailyReports is EqualUnmodifiableListView) return _dailyReports;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_dailyReports);
}

 final  List<MonthlyCollectionPerformance> _monthlyReports;
@override@JsonKey() List<MonthlyCollectionPerformance> get monthlyReports {
  if (_monthlyReports is EqualUnmodifiableListView) return _monthlyReports;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_monthlyReports);
}

// Current Month Stats
@override@JsonKey() final  int currentMonthCollections;
@override@JsonKey() final  double currentMonthAmount;
@override@JsonKey() final  double currentMonthCommission;
@override@JsonKey() final  int currentMonthTarget;
@override@JsonKey() final  double targetAchievement;
// Location Tracking
 final  List<LocationTracking> _locationHistory;
// Location Tracking
@override@JsonKey() List<LocationTracking> get locationHistory {
  if (_locationHistory is EqualUnmodifiableListView) return _locationHistory;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_locationHistory);
}

@override final  bool? isCurrentlyActive;
@override final  GeoLocation? lastLocation;
@override final  DateTime? lastLocationUpdate;
// Status
@override final  AgentStatus status;
@override final  DateTime? lastActiveAt;
// Documents
 final  List<String> _documentUrls;
// Documents
@override@JsonKey() List<String> get documentUrls {
  if (_documentUrls is EqualUnmodifiableListView) return _documentUrls;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_documentUrls);
}

@override final  DateTime createdAt;
@override final  DateTime updatedAt;

/// Create a copy of EMICollectionAgent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$EMICollectionAgentCopyWith<_EMICollectionAgent> get copyWith => __$EMICollectionAgentCopyWithImpl<_EMICollectionAgent>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$EMICollectionAgentToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _EMICollectionAgent&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.email, email) || other.email == email)&&(identical(other.photoUrl, photoUrl) || other.photoUrl == photoUrl)&&(identical(other.aadharNumber, aadharNumber) || other.aadharNumber == aadharNumber)&&(identical(other.address, address) || other.address == address)&&(identical(other.employeeId, employeeId) || other.employeeId == employeeId)&&(identical(other.joiningDate, joiningDate) || other.joiningDate == joiningDate)&&(identical(other.agentType, agentType) || other.agentType == agentType)&&(identical(other.assignedArea, assignedArea) || other.assignedArea == assignedArea)&&(identical(other.monthlySalary, monthlySalary) || other.monthlySalary == monthlySalary)&&(identical(other.commissionPerCollection, commissionPerCollection) || other.commissionPerCollection == commissionPerCollection)&&(identical(other.commissionPercentage, commissionPercentage) || other.commissionPercentage == commissionPercentage)&&(identical(other.incentivePerTarget, incentivePerTarget) || other.incentivePerTarget == incentivePerTarget)&&const DeepCollectionEquality().equals(other._assignedCustomerIds, _assignedCustomerIds)&&const DeepCollectionEquality().equals(other._customerAssignments, _customerAssignments)&&const DeepCollectionEquality().equals(other._dailyReports, _dailyReports)&&const DeepCollectionEquality().equals(other._monthlyReports, _monthlyReports)&&(identical(other.currentMonthCollections, currentMonthCollections) || other.currentMonthCollections == currentMonthCollections)&&(identical(other.currentMonthAmount, currentMonthAmount) || other.currentMonthAmount == currentMonthAmount)&&(identical(other.currentMonthCommission, currentMonthCommission) || other.currentMonthCommission == currentMonthCommission)&&(identical(other.currentMonthTarget, currentMonthTarget) || other.currentMonthTarget == currentMonthTarget)&&(identical(other.targetAchievement, targetAchievement) || other.targetAchievement == targetAchievement)&&const DeepCollectionEquality().equals(other._locationHistory, _locationHistory)&&(identical(other.isCurrentlyActive, isCurrentlyActive) || other.isCurrentlyActive == isCurrentlyActive)&&(identical(other.lastLocation, lastLocation) || other.lastLocation == lastLocation)&&(identical(other.lastLocationUpdate, lastLocationUpdate) || other.lastLocationUpdate == lastLocationUpdate)&&(identical(other.status, status) || other.status == status)&&(identical(other.lastActiveAt, lastActiveAt) || other.lastActiveAt == lastActiveAt)&&const DeepCollectionEquality().equals(other._documentUrls, _documentUrls)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,name,phone,email,photoUrl,aadharNumber,address,employeeId,joiningDate,agentType,assignedArea,monthlySalary,commissionPerCollection,commissionPercentage,incentivePerTarget,const DeepCollectionEquality().hash(_assignedCustomerIds),const DeepCollectionEquality().hash(_customerAssignments),const DeepCollectionEquality().hash(_dailyReports),const DeepCollectionEquality().hash(_monthlyReports),currentMonthCollections,currentMonthAmount,currentMonthCommission,currentMonthTarget,targetAchievement,const DeepCollectionEquality().hash(_locationHistory),isCurrentlyActive,lastLocation,lastLocationUpdate,status,lastActiveAt,const DeepCollectionEquality().hash(_documentUrls),createdAt,updatedAt]);

@override
String toString() {
  return 'EMICollectionAgent(id: $id, name: $name, phone: $phone, email: $email, photoUrl: $photoUrl, aadharNumber: $aadharNumber, address: $address, employeeId: $employeeId, joiningDate: $joiningDate, agentType: $agentType, assignedArea: $assignedArea, monthlySalary: $monthlySalary, commissionPerCollection: $commissionPerCollection, commissionPercentage: $commissionPercentage, incentivePerTarget: $incentivePerTarget, assignedCustomerIds: $assignedCustomerIds, customerAssignments: $customerAssignments, dailyReports: $dailyReports, monthlyReports: $monthlyReports, currentMonthCollections: $currentMonthCollections, currentMonthAmount: $currentMonthAmount, currentMonthCommission: $currentMonthCommission, currentMonthTarget: $currentMonthTarget, targetAchievement: $targetAchievement, locationHistory: $locationHistory, isCurrentlyActive: $isCurrentlyActive, lastLocation: $lastLocation, lastLocationUpdate: $lastLocationUpdate, status: $status, lastActiveAt: $lastActiveAt, documentUrls: $documentUrls, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class _$EMICollectionAgentCopyWith<$Res> implements $EMICollectionAgentCopyWith<$Res> {
  factory _$EMICollectionAgentCopyWith(_EMICollectionAgent value, $Res Function(_EMICollectionAgent) _then) = __$EMICollectionAgentCopyWithImpl;
@override @useResult
$Res call({
 String id, String name, String phone, String email, String? photoUrl, String? aadharNumber, String? address, String employeeId, DateTime joiningDate, CollectionAgentType agentType, CollectionArea assignedArea, double monthlySalary, double? commissionPerCollection, double? commissionPercentage, double? incentivePerTarget, List<String> assignedCustomerIds, List<EMICustomerAssignment> customerAssignments, List<DailyCollectionReport> dailyReports, List<MonthlyCollectionPerformance> monthlyReports, int currentMonthCollections, double currentMonthAmount, double currentMonthCommission, int currentMonthTarget, double targetAchievement, List<LocationTracking> locationHistory, bool? isCurrentlyActive, GeoLocation? lastLocation, DateTime? lastLocationUpdate, AgentStatus status, DateTime? lastActiveAt, List<String> documentUrls, DateTime createdAt, DateTime updatedAt
});


@override $CollectionAreaCopyWith<$Res> get assignedArea;

}
/// @nodoc
class __$EMICollectionAgentCopyWithImpl<$Res>
    implements _$EMICollectionAgentCopyWith<$Res> {
  __$EMICollectionAgentCopyWithImpl(this._self, this._then);

  final _EMICollectionAgent _self;
  final $Res Function(_EMICollectionAgent) _then;

/// Create a copy of EMICollectionAgent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? phone = null,Object? email = null,Object? photoUrl = freezed,Object? aadharNumber = freezed,Object? address = freezed,Object? employeeId = null,Object? joiningDate = null,Object? agentType = null,Object? assignedArea = null,Object? monthlySalary = null,Object? commissionPerCollection = freezed,Object? commissionPercentage = freezed,Object? incentivePerTarget = freezed,Object? assignedCustomerIds = null,Object? customerAssignments = null,Object? dailyReports = null,Object? monthlyReports = null,Object? currentMonthCollections = null,Object? currentMonthAmount = null,Object? currentMonthCommission = null,Object? currentMonthTarget = null,Object? targetAchievement = null,Object? locationHistory = null,Object? isCurrentlyActive = freezed,Object? lastLocation = freezed,Object? lastLocationUpdate = freezed,Object? status = null,Object? lastActiveAt = freezed,Object? documentUrls = null,Object? createdAt = null,Object? updatedAt = null,}) {
  return _then(_EMICollectionAgent(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,photoUrl: freezed == photoUrl ? _self.photoUrl : photoUrl // ignore: cast_nullable_to_non_nullable
as String?,aadharNumber: freezed == aadharNumber ? _self.aadharNumber : aadharNumber // ignore: cast_nullable_to_non_nullable
as String?,address: freezed == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String?,employeeId: null == employeeId ? _self.employeeId : employeeId // ignore: cast_nullable_to_non_nullable
as String,joiningDate: null == joiningDate ? _self.joiningDate : joiningDate // ignore: cast_nullable_to_non_nullable
as DateTime,agentType: null == agentType ? _self.agentType : agentType // ignore: cast_nullable_to_non_nullable
as CollectionAgentType,assignedArea: null == assignedArea ? _self.assignedArea : assignedArea // ignore: cast_nullable_to_non_nullable
as CollectionArea,monthlySalary: null == monthlySalary ? _self.monthlySalary : monthlySalary // ignore: cast_nullable_to_non_nullable
as double,commissionPerCollection: freezed == commissionPerCollection ? _self.commissionPerCollection : commissionPerCollection // ignore: cast_nullable_to_non_nullable
as double?,commissionPercentage: freezed == commissionPercentage ? _self.commissionPercentage : commissionPercentage // ignore: cast_nullable_to_non_nullable
as double?,incentivePerTarget: freezed == incentivePerTarget ? _self.incentivePerTarget : incentivePerTarget // ignore: cast_nullable_to_non_nullable
as double?,assignedCustomerIds: null == assignedCustomerIds ? _self._assignedCustomerIds : assignedCustomerIds // ignore: cast_nullable_to_non_nullable
as List<String>,customerAssignments: null == customerAssignments ? _self._customerAssignments : customerAssignments // ignore: cast_nullable_to_non_nullable
as List<EMICustomerAssignment>,dailyReports: null == dailyReports ? _self._dailyReports : dailyReports // ignore: cast_nullable_to_non_nullable
as List<DailyCollectionReport>,monthlyReports: null == monthlyReports ? _self._monthlyReports : monthlyReports // ignore: cast_nullable_to_non_nullable
as List<MonthlyCollectionPerformance>,currentMonthCollections: null == currentMonthCollections ? _self.currentMonthCollections : currentMonthCollections // ignore: cast_nullable_to_non_nullable
as int,currentMonthAmount: null == currentMonthAmount ? _self.currentMonthAmount : currentMonthAmount // ignore: cast_nullable_to_non_nullable
as double,currentMonthCommission: null == currentMonthCommission ? _self.currentMonthCommission : currentMonthCommission // ignore: cast_nullable_to_non_nullable
as double,currentMonthTarget: null == currentMonthTarget ? _self.currentMonthTarget : currentMonthTarget // ignore: cast_nullable_to_non_nullable
as int,targetAchievement: null == targetAchievement ? _self.targetAchievement : targetAchievement // ignore: cast_nullable_to_non_nullable
as double,locationHistory: null == locationHistory ? _self._locationHistory : locationHistory // ignore: cast_nullable_to_non_nullable
as List<LocationTracking>,isCurrentlyActive: freezed == isCurrentlyActive ? _self.isCurrentlyActive : isCurrentlyActive // ignore: cast_nullable_to_non_nullable
as bool?,lastLocation: freezed == lastLocation ? _self.lastLocation : lastLocation // ignore: cast_nullable_to_non_nullable
as GeoLocation?,lastLocationUpdate: freezed == lastLocationUpdate ? _self.lastLocationUpdate : lastLocationUpdate // ignore: cast_nullable_to_non_nullable
as DateTime?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as AgentStatus,lastActiveAt: freezed == lastActiveAt ? _self.lastActiveAt : lastActiveAt // ignore: cast_nullable_to_non_nullable
as DateTime?,documentUrls: null == documentUrls ? _self._documentUrls : documentUrls // ignore: cast_nullable_to_non_nullable
as List<String>,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,updatedAt: null == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

/// Create a copy of EMICollectionAgent
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CollectionAreaCopyWith<$Res> get assignedArea {
  
  return $CollectionAreaCopyWith<$Res>(_self.assignedArea, (value) {
    return _then(_self.copyWith(assignedArea: value));
  });
}
}


/// @nodoc
mixin _$CollectionArea {

 String get areaName; String get state; String get district; String get city; List<String> get colonies; List<String> get pincodes; String? get areaManagerId;
/// Create a copy of CollectionArea
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CollectionAreaCopyWith<CollectionArea> get copyWith => _$CollectionAreaCopyWithImpl<CollectionArea>(this as CollectionArea, _$identity);

  /// Serializes this CollectionArea to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CollectionArea&&(identical(other.areaName, areaName) || other.areaName == areaName)&&(identical(other.state, state) || other.state == state)&&(identical(other.district, district) || other.district == district)&&(identical(other.city, city) || other.city == city)&&const DeepCollectionEquality().equals(other.colonies, colonies)&&const DeepCollectionEquality().equals(other.pincodes, pincodes)&&(identical(other.areaManagerId, areaManagerId) || other.areaManagerId == areaManagerId));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,areaName,state,district,city,const DeepCollectionEquality().hash(colonies),const DeepCollectionEquality().hash(pincodes),areaManagerId);

@override
String toString() {
  return 'CollectionArea(areaName: $areaName, state: $state, district: $district, city: $city, colonies: $colonies, pincodes: $pincodes, areaManagerId: $areaManagerId)';
}


}

/// @nodoc
abstract mixin class $CollectionAreaCopyWith<$Res>  {
  factory $CollectionAreaCopyWith(CollectionArea value, $Res Function(CollectionArea) _then) = _$CollectionAreaCopyWithImpl;
@useResult
$Res call({
 String areaName, String state, String district, String city, List<String> colonies, List<String> pincodes, String? areaManagerId
});




}
/// @nodoc
class _$CollectionAreaCopyWithImpl<$Res>
    implements $CollectionAreaCopyWith<$Res> {
  _$CollectionAreaCopyWithImpl(this._self, this._then);

  final CollectionArea _self;
  final $Res Function(CollectionArea) _then;

/// Create a copy of CollectionArea
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? areaName = null,Object? state = null,Object? district = null,Object? city = null,Object? colonies = null,Object? pincodes = null,Object? areaManagerId = freezed,}) {
  return _then(_self.copyWith(
areaName: null == areaName ? _self.areaName : areaName // ignore: cast_nullable_to_non_nullable
as String,state: null == state ? _self.state : state // ignore: cast_nullable_to_non_nullable
as String,district: null == district ? _self.district : district // ignore: cast_nullable_to_non_nullable
as String,city: null == city ? _self.city : city // ignore: cast_nullable_to_non_nullable
as String,colonies: null == colonies ? _self.colonies : colonies // ignore: cast_nullable_to_non_nullable
as List<String>,pincodes: null == pincodes ? _self.pincodes : pincodes // ignore: cast_nullable_to_non_nullable
as List<String>,areaManagerId: freezed == areaManagerId ? _self.areaManagerId : areaManagerId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [CollectionArea].
extension CollectionAreaPatterns on CollectionArea {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CollectionArea value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CollectionArea() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CollectionArea value)  $default,){
final _that = this;
switch (_that) {
case _CollectionArea():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CollectionArea value)?  $default,){
final _that = this;
switch (_that) {
case _CollectionArea() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String areaName,  String state,  String district,  String city,  List<String> colonies,  List<String> pincodes,  String? areaManagerId)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CollectionArea() when $default != null:
return $default(_that.areaName,_that.state,_that.district,_that.city,_that.colonies,_that.pincodes,_that.areaManagerId);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String areaName,  String state,  String district,  String city,  List<String> colonies,  List<String> pincodes,  String? areaManagerId)  $default,) {final _that = this;
switch (_that) {
case _CollectionArea():
return $default(_that.areaName,_that.state,_that.district,_that.city,_that.colonies,_that.pincodes,_that.areaManagerId);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String areaName,  String state,  String district,  String city,  List<String> colonies,  List<String> pincodes,  String? areaManagerId)?  $default,) {final _that = this;
switch (_that) {
case _CollectionArea() when $default != null:
return $default(_that.areaName,_that.state,_that.district,_that.city,_that.colonies,_that.pincodes,_that.areaManagerId);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CollectionArea implements CollectionArea {
  const _CollectionArea({required this.areaName, required this.state, required this.district, required this.city, final  List<String> colonies = const [], final  List<String> pincodes = const [], this.areaManagerId}): _colonies = colonies,_pincodes = pincodes;
  factory _CollectionArea.fromJson(Map<String, dynamic> json) => _$CollectionAreaFromJson(json);

@override final  String areaName;
@override final  String state;
@override final  String district;
@override final  String city;
 final  List<String> _colonies;
@override@JsonKey() List<String> get colonies {
  if (_colonies is EqualUnmodifiableListView) return _colonies;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_colonies);
}

 final  List<String> _pincodes;
@override@JsonKey() List<String> get pincodes {
  if (_pincodes is EqualUnmodifiableListView) return _pincodes;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_pincodes);
}

@override final  String? areaManagerId;

/// Create a copy of CollectionArea
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CollectionAreaCopyWith<_CollectionArea> get copyWith => __$CollectionAreaCopyWithImpl<_CollectionArea>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CollectionAreaToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _CollectionArea&&(identical(other.areaName, areaName) || other.areaName == areaName)&&(identical(other.state, state) || other.state == state)&&(identical(other.district, district) || other.district == district)&&(identical(other.city, city) || other.city == city)&&const DeepCollectionEquality().equals(other._colonies, _colonies)&&const DeepCollectionEquality().equals(other._pincodes, _pincodes)&&(identical(other.areaManagerId, areaManagerId) || other.areaManagerId == areaManagerId));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,areaName,state,district,city,const DeepCollectionEquality().hash(_colonies),const DeepCollectionEquality().hash(_pincodes),areaManagerId);

@override
String toString() {
  return 'CollectionArea(areaName: $areaName, state: $state, district: $district, city: $city, colonies: $colonies, pincodes: $pincodes, areaManagerId: $areaManagerId)';
}


}

/// @nodoc
abstract mixin class _$CollectionAreaCopyWith<$Res> implements $CollectionAreaCopyWith<$Res> {
  factory _$CollectionAreaCopyWith(_CollectionArea value, $Res Function(_CollectionArea) _then) = __$CollectionAreaCopyWithImpl;
@override @useResult
$Res call({
 String areaName, String state, String district, String city, List<String> colonies, List<String> pincodes, String? areaManagerId
});




}
/// @nodoc
class __$CollectionAreaCopyWithImpl<$Res>
    implements _$CollectionAreaCopyWith<$Res> {
  __$CollectionAreaCopyWithImpl(this._self, this._then);

  final _CollectionArea _self;
  final $Res Function(_CollectionArea) _then;

/// Create a copy of CollectionArea
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? areaName = null,Object? state = null,Object? district = null,Object? city = null,Object? colonies = null,Object? pincodes = null,Object? areaManagerId = freezed,}) {
  return _then(_CollectionArea(
areaName: null == areaName ? _self.areaName : areaName // ignore: cast_nullable_to_non_nullable
as String,state: null == state ? _self.state : state // ignore: cast_nullable_to_non_nullable
as String,district: null == district ? _self.district : district // ignore: cast_nullable_to_non_nullable
as String,city: null == city ? _self.city : city // ignore: cast_nullable_to_non_nullable
as String,colonies: null == colonies ? _self._colonies : colonies // ignore: cast_nullable_to_non_nullable
as List<String>,pincodes: null == pincodes ? _self._pincodes : pincodes // ignore: cast_nullable_to_non_nullable
as List<String>,areaManagerId: freezed == areaManagerId ? _self.areaManagerId : areaManagerId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$EMICustomerAssignment {

 String get customerId; String get customerName; String get customerPhone; String get customerAddress; String get bookingId; String get plotNumber; String get colonyName;// EMI Details
 double get monthlyEMI; int get totalEMIs; int get paidEMIs; int get pendingEMIs; double get totalDue;// Due Date
 int get dueDay;// 5th, 10th, 15th of month
 DateTime? get nextDueDate;// Status
 PaymentStatus get paymentStatus;// Regular, Irregular, Defaulter
 bool get isHighPriority;// For overdue
// Collection Info
 String? get preferredCollectionTime;// Morning, Afternoon, Evening
 String? get landmark; GeoLocation? get location;// History
 List<PreviousVisit> get visitHistory; String? get specialInstructions; DateTime? get assignedAt; DateTime? get lastCollectedAt;
/// Create a copy of EMICustomerAssignment
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$EMICustomerAssignmentCopyWith<EMICustomerAssignment> get copyWith => _$EMICustomerAssignmentCopyWithImpl<EMICustomerAssignment>(this as EMICustomerAssignment, _$identity);

  /// Serializes this EMICustomerAssignment to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is EMICustomerAssignment&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.customerPhone, customerPhone) || other.customerPhone == customerPhone)&&(identical(other.customerAddress, customerAddress) || other.customerAddress == customerAddress)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.plotNumber, plotNumber) || other.plotNumber == plotNumber)&&(identical(other.colonyName, colonyName) || other.colonyName == colonyName)&&(identical(other.monthlyEMI, monthlyEMI) || other.monthlyEMI == monthlyEMI)&&(identical(other.totalEMIs, totalEMIs) || other.totalEMIs == totalEMIs)&&(identical(other.paidEMIs, paidEMIs) || other.paidEMIs == paidEMIs)&&(identical(other.pendingEMIs, pendingEMIs) || other.pendingEMIs == pendingEMIs)&&(identical(other.totalDue, totalDue) || other.totalDue == totalDue)&&(identical(other.dueDay, dueDay) || other.dueDay == dueDay)&&(identical(other.nextDueDate, nextDueDate) || other.nextDueDate == nextDueDate)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.isHighPriority, isHighPriority) || other.isHighPriority == isHighPriority)&&(identical(other.preferredCollectionTime, preferredCollectionTime) || other.preferredCollectionTime == preferredCollectionTime)&&(identical(other.landmark, landmark) || other.landmark == landmark)&&(identical(other.location, location) || other.location == location)&&const DeepCollectionEquality().equals(other.visitHistory, visitHistory)&&(identical(other.specialInstructions, specialInstructions) || other.specialInstructions == specialInstructions)&&(identical(other.assignedAt, assignedAt) || other.assignedAt == assignedAt)&&(identical(other.lastCollectedAt, lastCollectedAt) || other.lastCollectedAt == lastCollectedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,customerId,customerName,customerPhone,customerAddress,bookingId,plotNumber,colonyName,monthlyEMI,totalEMIs,paidEMIs,pendingEMIs,totalDue,dueDay,nextDueDate,paymentStatus,isHighPriority,preferredCollectionTime,landmark,location,const DeepCollectionEquality().hash(visitHistory),specialInstructions,assignedAt,lastCollectedAt]);

@override
String toString() {
  return 'EMICustomerAssignment(customerId: $customerId, customerName: $customerName, customerPhone: $customerPhone, customerAddress: $customerAddress, bookingId: $bookingId, plotNumber: $plotNumber, colonyName: $colonyName, monthlyEMI: $monthlyEMI, totalEMIs: $totalEMIs, paidEMIs: $paidEMIs, pendingEMIs: $pendingEMIs, totalDue: $totalDue, dueDay: $dueDay, nextDueDate: $nextDueDate, paymentStatus: $paymentStatus, isHighPriority: $isHighPriority, preferredCollectionTime: $preferredCollectionTime, landmark: $landmark, location: $location, visitHistory: $visitHistory, specialInstructions: $specialInstructions, assignedAt: $assignedAt, lastCollectedAt: $lastCollectedAt)';
}


}

/// @nodoc
abstract mixin class $EMICustomerAssignmentCopyWith<$Res>  {
  factory $EMICustomerAssignmentCopyWith(EMICustomerAssignment value, $Res Function(EMICustomerAssignment) _then) = _$EMICustomerAssignmentCopyWithImpl;
@useResult
$Res call({
 String customerId, String customerName, String customerPhone, String customerAddress, String bookingId, String plotNumber, String colonyName, double monthlyEMI, int totalEMIs, int paidEMIs, int pendingEMIs, double totalDue, int dueDay, DateTime? nextDueDate, PaymentStatus paymentStatus, bool isHighPriority, String? preferredCollectionTime, String? landmark, GeoLocation? location, List<PreviousVisit> visitHistory, String? specialInstructions, DateTime? assignedAt, DateTime? lastCollectedAt
});




}
/// @nodoc
class _$EMICustomerAssignmentCopyWithImpl<$Res>
    implements $EMICustomerAssignmentCopyWith<$Res> {
  _$EMICustomerAssignmentCopyWithImpl(this._self, this._then);

  final EMICustomerAssignment _self;
  final $Res Function(EMICustomerAssignment) _then;

/// Create a copy of EMICustomerAssignment
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? customerId = null,Object? customerName = null,Object? customerPhone = null,Object? customerAddress = null,Object? bookingId = null,Object? plotNumber = null,Object? colonyName = null,Object? monthlyEMI = null,Object? totalEMIs = null,Object? paidEMIs = null,Object? pendingEMIs = null,Object? totalDue = null,Object? dueDay = null,Object? nextDueDate = freezed,Object? paymentStatus = null,Object? isHighPriority = null,Object? preferredCollectionTime = freezed,Object? landmark = freezed,Object? location = freezed,Object? visitHistory = null,Object? specialInstructions = freezed,Object? assignedAt = freezed,Object? lastCollectedAt = freezed,}) {
  return _then(_self.copyWith(
customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,customerPhone: null == customerPhone ? _self.customerPhone : customerPhone // ignore: cast_nullable_to_non_nullable
as String,customerAddress: null == customerAddress ? _self.customerAddress : customerAddress // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,plotNumber: null == plotNumber ? _self.plotNumber : plotNumber // ignore: cast_nullable_to_non_nullable
as String,colonyName: null == colonyName ? _self.colonyName : colonyName // ignore: cast_nullable_to_non_nullable
as String,monthlyEMI: null == monthlyEMI ? _self.monthlyEMI : monthlyEMI // ignore: cast_nullable_to_non_nullable
as double,totalEMIs: null == totalEMIs ? _self.totalEMIs : totalEMIs // ignore: cast_nullable_to_non_nullable
as int,paidEMIs: null == paidEMIs ? _self.paidEMIs : paidEMIs // ignore: cast_nullable_to_non_nullable
as int,pendingEMIs: null == pendingEMIs ? _self.pendingEMIs : pendingEMIs // ignore: cast_nullable_to_non_nullable
as int,totalDue: null == totalDue ? _self.totalDue : totalDue // ignore: cast_nullable_to_non_nullable
as double,dueDay: null == dueDay ? _self.dueDay : dueDay // ignore: cast_nullable_to_non_nullable
as int,nextDueDate: freezed == nextDueDate ? _self.nextDueDate : nextDueDate // ignore: cast_nullable_to_non_nullable
as DateTime?,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as PaymentStatus,isHighPriority: null == isHighPriority ? _self.isHighPriority : isHighPriority // ignore: cast_nullable_to_non_nullable
as bool,preferredCollectionTime: freezed == preferredCollectionTime ? _self.preferredCollectionTime : preferredCollectionTime // ignore: cast_nullable_to_non_nullable
as String?,landmark: freezed == landmark ? _self.landmark : landmark // ignore: cast_nullable_to_non_nullable
as String?,location: freezed == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation?,visitHistory: null == visitHistory ? _self.visitHistory : visitHistory // ignore: cast_nullable_to_non_nullable
as List<PreviousVisit>,specialInstructions: freezed == specialInstructions ? _self.specialInstructions : specialInstructions // ignore: cast_nullable_to_non_nullable
as String?,assignedAt: freezed == assignedAt ? _self.assignedAt : assignedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,lastCollectedAt: freezed == lastCollectedAt ? _self.lastCollectedAt : lastCollectedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [EMICustomerAssignment].
extension EMICustomerAssignmentPatterns on EMICustomerAssignment {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _EMICustomerAssignment value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _EMICustomerAssignment() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _EMICustomerAssignment value)  $default,){
final _that = this;
switch (_that) {
case _EMICustomerAssignment():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _EMICustomerAssignment value)?  $default,){
final _that = this;
switch (_that) {
case _EMICustomerAssignment() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String customerId,  String customerName,  String customerPhone,  String customerAddress,  String bookingId,  String plotNumber,  String colonyName,  double monthlyEMI,  int totalEMIs,  int paidEMIs,  int pendingEMIs,  double totalDue,  int dueDay,  DateTime? nextDueDate,  PaymentStatus paymentStatus,  bool isHighPriority,  String? preferredCollectionTime,  String? landmark,  GeoLocation? location,  List<PreviousVisit> visitHistory,  String? specialInstructions,  DateTime? assignedAt,  DateTime? lastCollectedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _EMICustomerAssignment() when $default != null:
return $default(_that.customerId,_that.customerName,_that.customerPhone,_that.customerAddress,_that.bookingId,_that.plotNumber,_that.colonyName,_that.monthlyEMI,_that.totalEMIs,_that.paidEMIs,_that.pendingEMIs,_that.totalDue,_that.dueDay,_that.nextDueDate,_that.paymentStatus,_that.isHighPriority,_that.preferredCollectionTime,_that.landmark,_that.location,_that.visitHistory,_that.specialInstructions,_that.assignedAt,_that.lastCollectedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String customerId,  String customerName,  String customerPhone,  String customerAddress,  String bookingId,  String plotNumber,  String colonyName,  double monthlyEMI,  int totalEMIs,  int paidEMIs,  int pendingEMIs,  double totalDue,  int dueDay,  DateTime? nextDueDate,  PaymentStatus paymentStatus,  bool isHighPriority,  String? preferredCollectionTime,  String? landmark,  GeoLocation? location,  List<PreviousVisit> visitHistory,  String? specialInstructions,  DateTime? assignedAt,  DateTime? lastCollectedAt)  $default,) {final _that = this;
switch (_that) {
case _EMICustomerAssignment():
return $default(_that.customerId,_that.customerName,_that.customerPhone,_that.customerAddress,_that.bookingId,_that.plotNumber,_that.colonyName,_that.monthlyEMI,_that.totalEMIs,_that.paidEMIs,_that.pendingEMIs,_that.totalDue,_that.dueDay,_that.nextDueDate,_that.paymentStatus,_that.isHighPriority,_that.preferredCollectionTime,_that.landmark,_that.location,_that.visitHistory,_that.specialInstructions,_that.assignedAt,_that.lastCollectedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String customerId,  String customerName,  String customerPhone,  String customerAddress,  String bookingId,  String plotNumber,  String colonyName,  double monthlyEMI,  int totalEMIs,  int paidEMIs,  int pendingEMIs,  double totalDue,  int dueDay,  DateTime? nextDueDate,  PaymentStatus paymentStatus,  bool isHighPriority,  String? preferredCollectionTime,  String? landmark,  GeoLocation? location,  List<PreviousVisit> visitHistory,  String? specialInstructions,  DateTime? assignedAt,  DateTime? lastCollectedAt)?  $default,) {final _that = this;
switch (_that) {
case _EMICustomerAssignment() when $default != null:
return $default(_that.customerId,_that.customerName,_that.customerPhone,_that.customerAddress,_that.bookingId,_that.plotNumber,_that.colonyName,_that.monthlyEMI,_that.totalEMIs,_that.paidEMIs,_that.pendingEMIs,_that.totalDue,_that.dueDay,_that.nextDueDate,_that.paymentStatus,_that.isHighPriority,_that.preferredCollectionTime,_that.landmark,_that.location,_that.visitHistory,_that.specialInstructions,_that.assignedAt,_that.lastCollectedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _EMICustomerAssignment implements EMICustomerAssignment {
  const _EMICustomerAssignment({required this.customerId, required this.customerName, required this.customerPhone, required this.customerAddress, required this.bookingId, required this.plotNumber, required this.colonyName, required this.monthlyEMI, required this.totalEMIs, required this.paidEMIs, required this.pendingEMIs, required this.totalDue, required this.dueDay, this.nextDueDate, this.paymentStatus = PaymentStatus.regular, this.isHighPriority = false, this.preferredCollectionTime, this.landmark, this.location, final  List<PreviousVisit> visitHistory = const [], this.specialInstructions, this.assignedAt, this.lastCollectedAt}): _visitHistory = visitHistory;
  factory _EMICustomerAssignment.fromJson(Map<String, dynamic> json) => _$EMICustomerAssignmentFromJson(json);

@override final  String customerId;
@override final  String customerName;
@override final  String customerPhone;
@override final  String customerAddress;
@override final  String bookingId;
@override final  String plotNumber;
@override final  String colonyName;
// EMI Details
@override final  double monthlyEMI;
@override final  int totalEMIs;
@override final  int paidEMIs;
@override final  int pendingEMIs;
@override final  double totalDue;
// Due Date
@override final  int dueDay;
// 5th, 10th, 15th of month
@override final  DateTime? nextDueDate;
// Status
@override@JsonKey() final  PaymentStatus paymentStatus;
// Regular, Irregular, Defaulter
@override@JsonKey() final  bool isHighPriority;
// For overdue
// Collection Info
@override final  String? preferredCollectionTime;
// Morning, Afternoon, Evening
@override final  String? landmark;
@override final  GeoLocation? location;
// History
 final  List<PreviousVisit> _visitHistory;
// History
@override@JsonKey() List<PreviousVisit> get visitHistory {
  if (_visitHistory is EqualUnmodifiableListView) return _visitHistory;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_visitHistory);
}

@override final  String? specialInstructions;
@override final  DateTime? assignedAt;
@override final  DateTime? lastCollectedAt;

/// Create a copy of EMICustomerAssignment
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$EMICustomerAssignmentCopyWith<_EMICustomerAssignment> get copyWith => __$EMICustomerAssignmentCopyWithImpl<_EMICustomerAssignment>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$EMICustomerAssignmentToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _EMICustomerAssignment&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.customerPhone, customerPhone) || other.customerPhone == customerPhone)&&(identical(other.customerAddress, customerAddress) || other.customerAddress == customerAddress)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.plotNumber, plotNumber) || other.plotNumber == plotNumber)&&(identical(other.colonyName, colonyName) || other.colonyName == colonyName)&&(identical(other.monthlyEMI, monthlyEMI) || other.monthlyEMI == monthlyEMI)&&(identical(other.totalEMIs, totalEMIs) || other.totalEMIs == totalEMIs)&&(identical(other.paidEMIs, paidEMIs) || other.paidEMIs == paidEMIs)&&(identical(other.pendingEMIs, pendingEMIs) || other.pendingEMIs == pendingEMIs)&&(identical(other.totalDue, totalDue) || other.totalDue == totalDue)&&(identical(other.dueDay, dueDay) || other.dueDay == dueDay)&&(identical(other.nextDueDate, nextDueDate) || other.nextDueDate == nextDueDate)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.isHighPriority, isHighPriority) || other.isHighPriority == isHighPriority)&&(identical(other.preferredCollectionTime, preferredCollectionTime) || other.preferredCollectionTime == preferredCollectionTime)&&(identical(other.landmark, landmark) || other.landmark == landmark)&&(identical(other.location, location) || other.location == location)&&const DeepCollectionEquality().equals(other._visitHistory, _visitHistory)&&(identical(other.specialInstructions, specialInstructions) || other.specialInstructions == specialInstructions)&&(identical(other.assignedAt, assignedAt) || other.assignedAt == assignedAt)&&(identical(other.lastCollectedAt, lastCollectedAt) || other.lastCollectedAt == lastCollectedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,customerId,customerName,customerPhone,customerAddress,bookingId,plotNumber,colonyName,monthlyEMI,totalEMIs,paidEMIs,pendingEMIs,totalDue,dueDay,nextDueDate,paymentStatus,isHighPriority,preferredCollectionTime,landmark,location,const DeepCollectionEquality().hash(_visitHistory),specialInstructions,assignedAt,lastCollectedAt]);

@override
String toString() {
  return 'EMICustomerAssignment(customerId: $customerId, customerName: $customerName, customerPhone: $customerPhone, customerAddress: $customerAddress, bookingId: $bookingId, plotNumber: $plotNumber, colonyName: $colonyName, monthlyEMI: $monthlyEMI, totalEMIs: $totalEMIs, paidEMIs: $paidEMIs, pendingEMIs: $pendingEMIs, totalDue: $totalDue, dueDay: $dueDay, nextDueDate: $nextDueDate, paymentStatus: $paymentStatus, isHighPriority: $isHighPriority, preferredCollectionTime: $preferredCollectionTime, landmark: $landmark, location: $location, visitHistory: $visitHistory, specialInstructions: $specialInstructions, assignedAt: $assignedAt, lastCollectedAt: $lastCollectedAt)';
}


}

/// @nodoc
abstract mixin class _$EMICustomerAssignmentCopyWith<$Res> implements $EMICustomerAssignmentCopyWith<$Res> {
  factory _$EMICustomerAssignmentCopyWith(_EMICustomerAssignment value, $Res Function(_EMICustomerAssignment) _then) = __$EMICustomerAssignmentCopyWithImpl;
@override @useResult
$Res call({
 String customerId, String customerName, String customerPhone, String customerAddress, String bookingId, String plotNumber, String colonyName, double monthlyEMI, int totalEMIs, int paidEMIs, int pendingEMIs, double totalDue, int dueDay, DateTime? nextDueDate, PaymentStatus paymentStatus, bool isHighPriority, String? preferredCollectionTime, String? landmark, GeoLocation? location, List<PreviousVisit> visitHistory, String? specialInstructions, DateTime? assignedAt, DateTime? lastCollectedAt
});




}
/// @nodoc
class __$EMICustomerAssignmentCopyWithImpl<$Res>
    implements _$EMICustomerAssignmentCopyWith<$Res> {
  __$EMICustomerAssignmentCopyWithImpl(this._self, this._then);

  final _EMICustomerAssignment _self;
  final $Res Function(_EMICustomerAssignment) _then;

/// Create a copy of EMICustomerAssignment
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? customerId = null,Object? customerName = null,Object? customerPhone = null,Object? customerAddress = null,Object? bookingId = null,Object? plotNumber = null,Object? colonyName = null,Object? monthlyEMI = null,Object? totalEMIs = null,Object? paidEMIs = null,Object? pendingEMIs = null,Object? totalDue = null,Object? dueDay = null,Object? nextDueDate = freezed,Object? paymentStatus = null,Object? isHighPriority = null,Object? preferredCollectionTime = freezed,Object? landmark = freezed,Object? location = freezed,Object? visitHistory = null,Object? specialInstructions = freezed,Object? assignedAt = freezed,Object? lastCollectedAt = freezed,}) {
  return _then(_EMICustomerAssignment(
customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,customerPhone: null == customerPhone ? _self.customerPhone : customerPhone // ignore: cast_nullable_to_non_nullable
as String,customerAddress: null == customerAddress ? _self.customerAddress : customerAddress // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,plotNumber: null == plotNumber ? _self.plotNumber : plotNumber // ignore: cast_nullable_to_non_nullable
as String,colonyName: null == colonyName ? _self.colonyName : colonyName // ignore: cast_nullable_to_non_nullable
as String,monthlyEMI: null == monthlyEMI ? _self.monthlyEMI : monthlyEMI // ignore: cast_nullable_to_non_nullable
as double,totalEMIs: null == totalEMIs ? _self.totalEMIs : totalEMIs // ignore: cast_nullable_to_non_nullable
as int,paidEMIs: null == paidEMIs ? _self.paidEMIs : paidEMIs // ignore: cast_nullable_to_non_nullable
as int,pendingEMIs: null == pendingEMIs ? _self.pendingEMIs : pendingEMIs // ignore: cast_nullable_to_non_nullable
as int,totalDue: null == totalDue ? _self.totalDue : totalDue // ignore: cast_nullable_to_non_nullable
as double,dueDay: null == dueDay ? _self.dueDay : dueDay // ignore: cast_nullable_to_non_nullable
as int,nextDueDate: freezed == nextDueDate ? _self.nextDueDate : nextDueDate // ignore: cast_nullable_to_non_nullable
as DateTime?,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as PaymentStatus,isHighPriority: null == isHighPriority ? _self.isHighPriority : isHighPriority // ignore: cast_nullable_to_non_nullable
as bool,preferredCollectionTime: freezed == preferredCollectionTime ? _self.preferredCollectionTime : preferredCollectionTime // ignore: cast_nullable_to_non_nullable
as String?,landmark: freezed == landmark ? _self.landmark : landmark // ignore: cast_nullable_to_non_nullable
as String?,location: freezed == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation?,visitHistory: null == visitHistory ? _self._visitHistory : visitHistory // ignore: cast_nullable_to_non_nullable
as List<PreviousVisit>,specialInstructions: freezed == specialInstructions ? _self.specialInstructions : specialInstructions // ignore: cast_nullable_to_non_nullable
as String?,assignedAt: freezed == assignedAt ? _self.assignedAt : assignedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,lastCollectedAt: freezed == lastCollectedAt ? _self.lastCollectedAt : lastCollectedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$PreviousVisit {

 DateTime get visitDate; VisitOutcome get outcome; double? get amountCollected; String? get notes; String? get customerFeedback;
/// Create a copy of PreviousVisit
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PreviousVisitCopyWith<PreviousVisit> get copyWith => _$PreviousVisitCopyWithImpl<PreviousVisit>(this as PreviousVisit, _$identity);

  /// Serializes this PreviousVisit to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PreviousVisit&&(identical(other.visitDate, visitDate) || other.visitDate == visitDate)&&(identical(other.outcome, outcome) || other.outcome == outcome)&&(identical(other.amountCollected, amountCollected) || other.amountCollected == amountCollected)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.customerFeedback, customerFeedback) || other.customerFeedback == customerFeedback));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,visitDate,outcome,amountCollected,notes,customerFeedback);

@override
String toString() {
  return 'PreviousVisit(visitDate: $visitDate, outcome: $outcome, amountCollected: $amountCollected, notes: $notes, customerFeedback: $customerFeedback)';
}


}

/// @nodoc
abstract mixin class $PreviousVisitCopyWith<$Res>  {
  factory $PreviousVisitCopyWith(PreviousVisit value, $Res Function(PreviousVisit) _then) = _$PreviousVisitCopyWithImpl;
@useResult
$Res call({
 DateTime visitDate, VisitOutcome outcome, double? amountCollected, String? notes, String? customerFeedback
});




}
/// @nodoc
class _$PreviousVisitCopyWithImpl<$Res>
    implements $PreviousVisitCopyWith<$Res> {
  _$PreviousVisitCopyWithImpl(this._self, this._then);

  final PreviousVisit _self;
  final $Res Function(PreviousVisit) _then;

/// Create a copy of PreviousVisit
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? visitDate = null,Object? outcome = null,Object? amountCollected = freezed,Object? notes = freezed,Object? customerFeedback = freezed,}) {
  return _then(_self.copyWith(
visitDate: null == visitDate ? _self.visitDate : visitDate // ignore: cast_nullable_to_non_nullable
as DateTime,outcome: null == outcome ? _self.outcome : outcome // ignore: cast_nullable_to_non_nullable
as VisitOutcome,amountCollected: freezed == amountCollected ? _self.amountCollected : amountCollected // ignore: cast_nullable_to_non_nullable
as double?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,customerFeedback: freezed == customerFeedback ? _self.customerFeedback : customerFeedback // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [PreviousVisit].
extension PreviousVisitPatterns on PreviousVisit {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PreviousVisit value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PreviousVisit() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PreviousVisit value)  $default,){
final _that = this;
switch (_that) {
case _PreviousVisit():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PreviousVisit value)?  $default,){
final _that = this;
switch (_that) {
case _PreviousVisit() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( DateTime visitDate,  VisitOutcome outcome,  double? amountCollected,  String? notes,  String? customerFeedback)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PreviousVisit() when $default != null:
return $default(_that.visitDate,_that.outcome,_that.amountCollected,_that.notes,_that.customerFeedback);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( DateTime visitDate,  VisitOutcome outcome,  double? amountCollected,  String? notes,  String? customerFeedback)  $default,) {final _that = this;
switch (_that) {
case _PreviousVisit():
return $default(_that.visitDate,_that.outcome,_that.amountCollected,_that.notes,_that.customerFeedback);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( DateTime visitDate,  VisitOutcome outcome,  double? amountCollected,  String? notes,  String? customerFeedback)?  $default,) {final _that = this;
switch (_that) {
case _PreviousVisit() when $default != null:
return $default(_that.visitDate,_that.outcome,_that.amountCollected,_that.notes,_that.customerFeedback);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PreviousVisit implements PreviousVisit {
  const _PreviousVisit({required this.visitDate, required this.outcome, this.amountCollected, this.notes, this.customerFeedback});
  factory _PreviousVisit.fromJson(Map<String, dynamic> json) => _$PreviousVisitFromJson(json);

@override final  DateTime visitDate;
@override final  VisitOutcome outcome;
@override final  double? amountCollected;
@override final  String? notes;
@override final  String? customerFeedback;

/// Create a copy of PreviousVisit
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PreviousVisitCopyWith<_PreviousVisit> get copyWith => __$PreviousVisitCopyWithImpl<_PreviousVisit>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PreviousVisitToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _PreviousVisit&&(identical(other.visitDate, visitDate) || other.visitDate == visitDate)&&(identical(other.outcome, outcome) || other.outcome == outcome)&&(identical(other.amountCollected, amountCollected) || other.amountCollected == amountCollected)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.customerFeedback, customerFeedback) || other.customerFeedback == customerFeedback));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,visitDate,outcome,amountCollected,notes,customerFeedback);

@override
String toString() {
  return 'PreviousVisit(visitDate: $visitDate, outcome: $outcome, amountCollected: $amountCollected, notes: $notes, customerFeedback: $customerFeedback)';
}


}

/// @nodoc
abstract mixin class _$PreviousVisitCopyWith<$Res> implements $PreviousVisitCopyWith<$Res> {
  factory _$PreviousVisitCopyWith(_PreviousVisit value, $Res Function(_PreviousVisit) _then) = __$PreviousVisitCopyWithImpl;
@override @useResult
$Res call({
 DateTime visitDate, VisitOutcome outcome, double? amountCollected, String? notes, String? customerFeedback
});




}
/// @nodoc
class __$PreviousVisitCopyWithImpl<$Res>
    implements _$PreviousVisitCopyWith<$Res> {
  __$PreviousVisitCopyWithImpl(this._self, this._then);

  final _PreviousVisit _self;
  final $Res Function(_PreviousVisit) _then;

/// Create a copy of PreviousVisit
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? visitDate = null,Object? outcome = null,Object? amountCollected = freezed,Object? notes = freezed,Object? customerFeedback = freezed,}) {
  return _then(_PreviousVisit(
visitDate: null == visitDate ? _self.visitDate : visitDate // ignore: cast_nullable_to_non_nullable
as DateTime,outcome: null == outcome ? _self.outcome : outcome // ignore: cast_nullable_to_non_nullable
as VisitOutcome,amountCollected: freezed == amountCollected ? _self.amountCollected : amountCollected // ignore: cast_nullable_to_non_nullable
as double?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,customerFeedback: freezed == customerFeedback ? _self.customerFeedback : customerFeedback // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$DailyCollectionReport {

 String get id; DateTime get date; String get agentId;// Collection Summary
 int get totalVisits; int get successfulCollections; int get partialCollections; int get failedVisits; int get rescheduled; int get customersNotHome;// Financial
 double get totalCollected; double get cashCollected; double get chequeCollected; double get onlineCollected; double get upiCollected;// Individual Collections
 List<CollectionRecord> get collections;// Time Tracking
 DateTime? get startTime; DateTime? get endTime; int get workingHours;// Location Data
 List<LocationTracking> get routeTaken; double get totalDistanceKm;// Status
 ReportSubmissionStatus get submissionStatus; DateTime? get submittedAt; String? get adminNotes;
/// Create a copy of DailyCollectionReport
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DailyCollectionReportCopyWith<DailyCollectionReport> get copyWith => _$DailyCollectionReportCopyWithImpl<DailyCollectionReport>(this as DailyCollectionReport, _$identity);

  /// Serializes this DailyCollectionReport to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DailyCollectionReport&&(identical(other.id, id) || other.id == id)&&(identical(other.date, date) || other.date == date)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.totalVisits, totalVisits) || other.totalVisits == totalVisits)&&(identical(other.successfulCollections, successfulCollections) || other.successfulCollections == successfulCollections)&&(identical(other.partialCollections, partialCollections) || other.partialCollections == partialCollections)&&(identical(other.failedVisits, failedVisits) || other.failedVisits == failedVisits)&&(identical(other.rescheduled, rescheduled) || other.rescheduled == rescheduled)&&(identical(other.customersNotHome, customersNotHome) || other.customersNotHome == customersNotHome)&&(identical(other.totalCollected, totalCollected) || other.totalCollected == totalCollected)&&(identical(other.cashCollected, cashCollected) || other.cashCollected == cashCollected)&&(identical(other.chequeCollected, chequeCollected) || other.chequeCollected == chequeCollected)&&(identical(other.onlineCollected, onlineCollected) || other.onlineCollected == onlineCollected)&&(identical(other.upiCollected, upiCollected) || other.upiCollected == upiCollected)&&const DeepCollectionEquality().equals(other.collections, collections)&&(identical(other.startTime, startTime) || other.startTime == startTime)&&(identical(other.endTime, endTime) || other.endTime == endTime)&&(identical(other.workingHours, workingHours) || other.workingHours == workingHours)&&const DeepCollectionEquality().equals(other.routeTaken, routeTaken)&&(identical(other.totalDistanceKm, totalDistanceKm) || other.totalDistanceKm == totalDistanceKm)&&(identical(other.submissionStatus, submissionStatus) || other.submissionStatus == submissionStatus)&&(identical(other.submittedAt, submittedAt) || other.submittedAt == submittedAt)&&(identical(other.adminNotes, adminNotes) || other.adminNotes == adminNotes));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,date,agentId,totalVisits,successfulCollections,partialCollections,failedVisits,rescheduled,customersNotHome,totalCollected,cashCollected,chequeCollected,onlineCollected,upiCollected,const DeepCollectionEquality().hash(collections),startTime,endTime,workingHours,const DeepCollectionEquality().hash(routeTaken),totalDistanceKm,submissionStatus,submittedAt,adminNotes]);

@override
String toString() {
  return 'DailyCollectionReport(id: $id, date: $date, agentId: $agentId, totalVisits: $totalVisits, successfulCollections: $successfulCollections, partialCollections: $partialCollections, failedVisits: $failedVisits, rescheduled: $rescheduled, customersNotHome: $customersNotHome, totalCollected: $totalCollected, cashCollected: $cashCollected, chequeCollected: $chequeCollected, onlineCollected: $onlineCollected, upiCollected: $upiCollected, collections: $collections, startTime: $startTime, endTime: $endTime, workingHours: $workingHours, routeTaken: $routeTaken, totalDistanceKm: $totalDistanceKm, submissionStatus: $submissionStatus, submittedAt: $submittedAt, adminNotes: $adminNotes)';
}


}

/// @nodoc
abstract mixin class $DailyCollectionReportCopyWith<$Res>  {
  factory $DailyCollectionReportCopyWith(DailyCollectionReport value, $Res Function(DailyCollectionReport) _then) = _$DailyCollectionReportCopyWithImpl;
@useResult
$Res call({
 String id, DateTime date, String agentId, int totalVisits, int successfulCollections, int partialCollections, int failedVisits, int rescheduled, int customersNotHome, double totalCollected, double cashCollected, double chequeCollected, double onlineCollected, double upiCollected, List<CollectionRecord> collections, DateTime? startTime, DateTime? endTime, int workingHours, List<LocationTracking> routeTaken, double totalDistanceKm, ReportSubmissionStatus submissionStatus, DateTime? submittedAt, String? adminNotes
});




}
/// @nodoc
class _$DailyCollectionReportCopyWithImpl<$Res>
    implements $DailyCollectionReportCopyWith<$Res> {
  _$DailyCollectionReportCopyWithImpl(this._self, this._then);

  final DailyCollectionReport _self;
  final $Res Function(DailyCollectionReport) _then;

/// Create a copy of DailyCollectionReport
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? date = null,Object? agentId = null,Object? totalVisits = null,Object? successfulCollections = null,Object? partialCollections = null,Object? failedVisits = null,Object? rescheduled = null,Object? customersNotHome = null,Object? totalCollected = null,Object? cashCollected = null,Object? chequeCollected = null,Object? onlineCollected = null,Object? upiCollected = null,Object? collections = null,Object? startTime = freezed,Object? endTime = freezed,Object? workingHours = null,Object? routeTaken = null,Object? totalDistanceKm = null,Object? submissionStatus = null,Object? submittedAt = freezed,Object? adminNotes = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,date: null == date ? _self.date : date // ignore: cast_nullable_to_non_nullable
as DateTime,agentId: null == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String,totalVisits: null == totalVisits ? _self.totalVisits : totalVisits // ignore: cast_nullable_to_non_nullable
as int,successfulCollections: null == successfulCollections ? _self.successfulCollections : successfulCollections // ignore: cast_nullable_to_non_nullable
as int,partialCollections: null == partialCollections ? _self.partialCollections : partialCollections // ignore: cast_nullable_to_non_nullable
as int,failedVisits: null == failedVisits ? _self.failedVisits : failedVisits // ignore: cast_nullable_to_non_nullable
as int,rescheduled: null == rescheduled ? _self.rescheduled : rescheduled // ignore: cast_nullable_to_non_nullable
as int,customersNotHome: null == customersNotHome ? _self.customersNotHome : customersNotHome // ignore: cast_nullable_to_non_nullable
as int,totalCollected: null == totalCollected ? _self.totalCollected : totalCollected // ignore: cast_nullable_to_non_nullable
as double,cashCollected: null == cashCollected ? _self.cashCollected : cashCollected // ignore: cast_nullable_to_non_nullable
as double,chequeCollected: null == chequeCollected ? _self.chequeCollected : chequeCollected // ignore: cast_nullable_to_non_nullable
as double,onlineCollected: null == onlineCollected ? _self.onlineCollected : onlineCollected // ignore: cast_nullable_to_non_nullable
as double,upiCollected: null == upiCollected ? _self.upiCollected : upiCollected // ignore: cast_nullable_to_non_nullable
as double,collections: null == collections ? _self.collections : collections // ignore: cast_nullable_to_non_nullable
as List<CollectionRecord>,startTime: freezed == startTime ? _self.startTime : startTime // ignore: cast_nullable_to_non_nullable
as DateTime?,endTime: freezed == endTime ? _self.endTime : endTime // ignore: cast_nullable_to_non_nullable
as DateTime?,workingHours: null == workingHours ? _self.workingHours : workingHours // ignore: cast_nullable_to_non_nullable
as int,routeTaken: null == routeTaken ? _self.routeTaken : routeTaken // ignore: cast_nullable_to_non_nullable
as List<LocationTracking>,totalDistanceKm: null == totalDistanceKm ? _self.totalDistanceKm : totalDistanceKm // ignore: cast_nullable_to_non_nullable
as double,submissionStatus: null == submissionStatus ? _self.submissionStatus : submissionStatus // ignore: cast_nullable_to_non_nullable
as ReportSubmissionStatus,submittedAt: freezed == submittedAt ? _self.submittedAt : submittedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,adminNotes: freezed == adminNotes ? _self.adminNotes : adminNotes // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [DailyCollectionReport].
extension DailyCollectionReportPatterns on DailyCollectionReport {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DailyCollectionReport value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DailyCollectionReport() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DailyCollectionReport value)  $default,){
final _that = this;
switch (_that) {
case _DailyCollectionReport():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DailyCollectionReport value)?  $default,){
final _that = this;
switch (_that) {
case _DailyCollectionReport() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  DateTime date,  String agentId,  int totalVisits,  int successfulCollections,  int partialCollections,  int failedVisits,  int rescheduled,  int customersNotHome,  double totalCollected,  double cashCollected,  double chequeCollected,  double onlineCollected,  double upiCollected,  List<CollectionRecord> collections,  DateTime? startTime,  DateTime? endTime,  int workingHours,  List<LocationTracking> routeTaken,  double totalDistanceKm,  ReportSubmissionStatus submissionStatus,  DateTime? submittedAt,  String? adminNotes)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DailyCollectionReport() when $default != null:
return $default(_that.id,_that.date,_that.agentId,_that.totalVisits,_that.successfulCollections,_that.partialCollections,_that.failedVisits,_that.rescheduled,_that.customersNotHome,_that.totalCollected,_that.cashCollected,_that.chequeCollected,_that.onlineCollected,_that.upiCollected,_that.collections,_that.startTime,_that.endTime,_that.workingHours,_that.routeTaken,_that.totalDistanceKm,_that.submissionStatus,_that.submittedAt,_that.adminNotes);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  DateTime date,  String agentId,  int totalVisits,  int successfulCollections,  int partialCollections,  int failedVisits,  int rescheduled,  int customersNotHome,  double totalCollected,  double cashCollected,  double chequeCollected,  double onlineCollected,  double upiCollected,  List<CollectionRecord> collections,  DateTime? startTime,  DateTime? endTime,  int workingHours,  List<LocationTracking> routeTaken,  double totalDistanceKm,  ReportSubmissionStatus submissionStatus,  DateTime? submittedAt,  String? adminNotes)  $default,) {final _that = this;
switch (_that) {
case _DailyCollectionReport():
return $default(_that.id,_that.date,_that.agentId,_that.totalVisits,_that.successfulCollections,_that.partialCollections,_that.failedVisits,_that.rescheduled,_that.customersNotHome,_that.totalCollected,_that.cashCollected,_that.chequeCollected,_that.onlineCollected,_that.upiCollected,_that.collections,_that.startTime,_that.endTime,_that.workingHours,_that.routeTaken,_that.totalDistanceKm,_that.submissionStatus,_that.submittedAt,_that.adminNotes);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  DateTime date,  String agentId,  int totalVisits,  int successfulCollections,  int partialCollections,  int failedVisits,  int rescheduled,  int customersNotHome,  double totalCollected,  double cashCollected,  double chequeCollected,  double onlineCollected,  double upiCollected,  List<CollectionRecord> collections,  DateTime? startTime,  DateTime? endTime,  int workingHours,  List<LocationTracking> routeTaken,  double totalDistanceKm,  ReportSubmissionStatus submissionStatus,  DateTime? submittedAt,  String? adminNotes)?  $default,) {final _that = this;
switch (_that) {
case _DailyCollectionReport() when $default != null:
return $default(_that.id,_that.date,_that.agentId,_that.totalVisits,_that.successfulCollections,_that.partialCollections,_that.failedVisits,_that.rescheduled,_that.customersNotHome,_that.totalCollected,_that.cashCollected,_that.chequeCollected,_that.onlineCollected,_that.upiCollected,_that.collections,_that.startTime,_that.endTime,_that.workingHours,_that.routeTaken,_that.totalDistanceKm,_that.submissionStatus,_that.submittedAt,_that.adminNotes);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _DailyCollectionReport implements DailyCollectionReport {
  const _DailyCollectionReport({required this.id, required this.date, required this.agentId, this.totalVisits = 0, this.successfulCollections = 0, this.partialCollections = 0, this.failedVisits = 0, this.rescheduled = 0, this.customersNotHome = 0, this.totalCollected = 0, this.cashCollected = 0, this.chequeCollected = 0, this.onlineCollected = 0, this.upiCollected = 0, final  List<CollectionRecord> collections = const [], this.startTime, this.endTime, this.workingHours = 0, final  List<LocationTracking> routeTaken = const [], this.totalDistanceKm = 0, required this.submissionStatus, this.submittedAt, this.adminNotes}): _collections = collections,_routeTaken = routeTaken;
  factory _DailyCollectionReport.fromJson(Map<String, dynamic> json) => _$DailyCollectionReportFromJson(json);

@override final  String id;
@override final  DateTime date;
@override final  String agentId;
// Collection Summary
@override@JsonKey() final  int totalVisits;
@override@JsonKey() final  int successfulCollections;
@override@JsonKey() final  int partialCollections;
@override@JsonKey() final  int failedVisits;
@override@JsonKey() final  int rescheduled;
@override@JsonKey() final  int customersNotHome;
// Financial
@override@JsonKey() final  double totalCollected;
@override@JsonKey() final  double cashCollected;
@override@JsonKey() final  double chequeCollected;
@override@JsonKey() final  double onlineCollected;
@override@JsonKey() final  double upiCollected;
// Individual Collections
 final  List<CollectionRecord> _collections;
// Individual Collections
@override@JsonKey() List<CollectionRecord> get collections {
  if (_collections is EqualUnmodifiableListView) return _collections;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_collections);
}

// Time Tracking
@override final  DateTime? startTime;
@override final  DateTime? endTime;
@override@JsonKey() final  int workingHours;
// Location Data
 final  List<LocationTracking> _routeTaken;
// Location Data
@override@JsonKey() List<LocationTracking> get routeTaken {
  if (_routeTaken is EqualUnmodifiableListView) return _routeTaken;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_routeTaken);
}

@override@JsonKey() final  double totalDistanceKm;
// Status
@override final  ReportSubmissionStatus submissionStatus;
@override final  DateTime? submittedAt;
@override final  String? adminNotes;

/// Create a copy of DailyCollectionReport
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DailyCollectionReportCopyWith<_DailyCollectionReport> get copyWith => __$DailyCollectionReportCopyWithImpl<_DailyCollectionReport>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$DailyCollectionReportToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _DailyCollectionReport&&(identical(other.id, id) || other.id == id)&&(identical(other.date, date) || other.date == date)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.totalVisits, totalVisits) || other.totalVisits == totalVisits)&&(identical(other.successfulCollections, successfulCollections) || other.successfulCollections == successfulCollections)&&(identical(other.partialCollections, partialCollections) || other.partialCollections == partialCollections)&&(identical(other.failedVisits, failedVisits) || other.failedVisits == failedVisits)&&(identical(other.rescheduled, rescheduled) || other.rescheduled == rescheduled)&&(identical(other.customersNotHome, customersNotHome) || other.customersNotHome == customersNotHome)&&(identical(other.totalCollected, totalCollected) || other.totalCollected == totalCollected)&&(identical(other.cashCollected, cashCollected) || other.cashCollected == cashCollected)&&(identical(other.chequeCollected, chequeCollected) || other.chequeCollected == chequeCollected)&&(identical(other.onlineCollected, onlineCollected) || other.onlineCollected == onlineCollected)&&(identical(other.upiCollected, upiCollected) || other.upiCollected == upiCollected)&&const DeepCollectionEquality().equals(other._collections, _collections)&&(identical(other.startTime, startTime) || other.startTime == startTime)&&(identical(other.endTime, endTime) || other.endTime == endTime)&&(identical(other.workingHours, workingHours) || other.workingHours == workingHours)&&const DeepCollectionEquality().equals(other._routeTaken, _routeTaken)&&(identical(other.totalDistanceKm, totalDistanceKm) || other.totalDistanceKm == totalDistanceKm)&&(identical(other.submissionStatus, submissionStatus) || other.submissionStatus == submissionStatus)&&(identical(other.submittedAt, submittedAt) || other.submittedAt == submittedAt)&&(identical(other.adminNotes, adminNotes) || other.adminNotes == adminNotes));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,date,agentId,totalVisits,successfulCollections,partialCollections,failedVisits,rescheduled,customersNotHome,totalCollected,cashCollected,chequeCollected,onlineCollected,upiCollected,const DeepCollectionEquality().hash(_collections),startTime,endTime,workingHours,const DeepCollectionEquality().hash(_routeTaken),totalDistanceKm,submissionStatus,submittedAt,adminNotes]);

@override
String toString() {
  return 'DailyCollectionReport(id: $id, date: $date, agentId: $agentId, totalVisits: $totalVisits, successfulCollections: $successfulCollections, partialCollections: $partialCollections, failedVisits: $failedVisits, rescheduled: $rescheduled, customersNotHome: $customersNotHome, totalCollected: $totalCollected, cashCollected: $cashCollected, chequeCollected: $chequeCollected, onlineCollected: $onlineCollected, upiCollected: $upiCollected, collections: $collections, startTime: $startTime, endTime: $endTime, workingHours: $workingHours, routeTaken: $routeTaken, totalDistanceKm: $totalDistanceKm, submissionStatus: $submissionStatus, submittedAt: $submittedAt, adminNotes: $adminNotes)';
}


}

/// @nodoc
abstract mixin class _$DailyCollectionReportCopyWith<$Res> implements $DailyCollectionReportCopyWith<$Res> {
  factory _$DailyCollectionReportCopyWith(_DailyCollectionReport value, $Res Function(_DailyCollectionReport) _then) = __$DailyCollectionReportCopyWithImpl;
@override @useResult
$Res call({
 String id, DateTime date, String agentId, int totalVisits, int successfulCollections, int partialCollections, int failedVisits, int rescheduled, int customersNotHome, double totalCollected, double cashCollected, double chequeCollected, double onlineCollected, double upiCollected, List<CollectionRecord> collections, DateTime? startTime, DateTime? endTime, int workingHours, List<LocationTracking> routeTaken, double totalDistanceKm, ReportSubmissionStatus submissionStatus, DateTime? submittedAt, String? adminNotes
});




}
/// @nodoc
class __$DailyCollectionReportCopyWithImpl<$Res>
    implements _$DailyCollectionReportCopyWith<$Res> {
  __$DailyCollectionReportCopyWithImpl(this._self, this._then);

  final _DailyCollectionReport _self;
  final $Res Function(_DailyCollectionReport) _then;

/// Create a copy of DailyCollectionReport
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? date = null,Object? agentId = null,Object? totalVisits = null,Object? successfulCollections = null,Object? partialCollections = null,Object? failedVisits = null,Object? rescheduled = null,Object? customersNotHome = null,Object? totalCollected = null,Object? cashCollected = null,Object? chequeCollected = null,Object? onlineCollected = null,Object? upiCollected = null,Object? collections = null,Object? startTime = freezed,Object? endTime = freezed,Object? workingHours = null,Object? routeTaken = null,Object? totalDistanceKm = null,Object? submissionStatus = null,Object? submittedAt = freezed,Object? adminNotes = freezed,}) {
  return _then(_DailyCollectionReport(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,date: null == date ? _self.date : date // ignore: cast_nullable_to_non_nullable
as DateTime,agentId: null == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String,totalVisits: null == totalVisits ? _self.totalVisits : totalVisits // ignore: cast_nullable_to_non_nullable
as int,successfulCollections: null == successfulCollections ? _self.successfulCollections : successfulCollections // ignore: cast_nullable_to_non_nullable
as int,partialCollections: null == partialCollections ? _self.partialCollections : partialCollections // ignore: cast_nullable_to_non_nullable
as int,failedVisits: null == failedVisits ? _self.failedVisits : failedVisits // ignore: cast_nullable_to_non_nullable
as int,rescheduled: null == rescheduled ? _self.rescheduled : rescheduled // ignore: cast_nullable_to_non_nullable
as int,customersNotHome: null == customersNotHome ? _self.customersNotHome : customersNotHome // ignore: cast_nullable_to_non_nullable
as int,totalCollected: null == totalCollected ? _self.totalCollected : totalCollected // ignore: cast_nullable_to_non_nullable
as double,cashCollected: null == cashCollected ? _self.cashCollected : cashCollected // ignore: cast_nullable_to_non_nullable
as double,chequeCollected: null == chequeCollected ? _self.chequeCollected : chequeCollected // ignore: cast_nullable_to_non_nullable
as double,onlineCollected: null == onlineCollected ? _self.onlineCollected : onlineCollected // ignore: cast_nullable_to_non_nullable
as double,upiCollected: null == upiCollected ? _self.upiCollected : upiCollected // ignore: cast_nullable_to_non_nullable
as double,collections: null == collections ? _self._collections : collections // ignore: cast_nullable_to_non_nullable
as List<CollectionRecord>,startTime: freezed == startTime ? _self.startTime : startTime // ignore: cast_nullable_to_non_nullable
as DateTime?,endTime: freezed == endTime ? _self.endTime : endTime // ignore: cast_nullable_to_non_nullable
as DateTime?,workingHours: null == workingHours ? _self.workingHours : workingHours // ignore: cast_nullable_to_non_nullable
as int,routeTaken: null == routeTaken ? _self._routeTaken : routeTaken // ignore: cast_nullable_to_non_nullable
as List<LocationTracking>,totalDistanceKm: null == totalDistanceKm ? _self.totalDistanceKm : totalDistanceKm // ignore: cast_nullable_to_non_nullable
as double,submissionStatus: null == submissionStatus ? _self.submissionStatus : submissionStatus // ignore: cast_nullable_to_non_nullable
as ReportSubmissionStatus,submittedAt: freezed == submittedAt ? _self.submittedAt : submittedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,adminNotes: freezed == adminNotes ? _self.adminNotes : adminNotes // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$CollectionRecord {

 String get customerId; String get customerName; String get bookingId; DateTime get collectionTime; double get amount; PaymentMode get mode;// Details
 int? get emiNumber; double? get lateFee; String? get chequeNumber; String? get transactionId; String? get receiptNumber;// Location
 GeoLocation? get location; String? get addressAtCollection;// Proof
 List<String> get photoUrls;// Payment proof photos
 String? get signatureUrl; String? get notes;// Verification
 bool? get isVerified; DateTime? get verifiedAt; String? get verifiedBy; String? get disputeReason;
/// Create a copy of CollectionRecord
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CollectionRecordCopyWith<CollectionRecord> get copyWith => _$CollectionRecordCopyWithImpl<CollectionRecord>(this as CollectionRecord, _$identity);

  /// Serializes this CollectionRecord to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CollectionRecord&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.collectionTime, collectionTime) || other.collectionTime == collectionTime)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.mode, mode) || other.mode == mode)&&(identical(other.emiNumber, emiNumber) || other.emiNumber == emiNumber)&&(identical(other.lateFee, lateFee) || other.lateFee == lateFee)&&(identical(other.chequeNumber, chequeNumber) || other.chequeNumber == chequeNumber)&&(identical(other.transactionId, transactionId) || other.transactionId == transactionId)&&(identical(other.receiptNumber, receiptNumber) || other.receiptNumber == receiptNumber)&&(identical(other.location, location) || other.location == location)&&(identical(other.addressAtCollection, addressAtCollection) || other.addressAtCollection == addressAtCollection)&&const DeepCollectionEquality().equals(other.photoUrls, photoUrls)&&(identical(other.signatureUrl, signatureUrl) || other.signatureUrl == signatureUrl)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.isVerified, isVerified) || other.isVerified == isVerified)&&(identical(other.verifiedAt, verifiedAt) || other.verifiedAt == verifiedAt)&&(identical(other.verifiedBy, verifiedBy) || other.verifiedBy == verifiedBy)&&(identical(other.disputeReason, disputeReason) || other.disputeReason == disputeReason));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,customerId,customerName,bookingId,collectionTime,amount,mode,emiNumber,lateFee,chequeNumber,transactionId,receiptNumber,location,addressAtCollection,const DeepCollectionEquality().hash(photoUrls),signatureUrl,notes,isVerified,verifiedAt,verifiedBy,disputeReason]);

@override
String toString() {
  return 'CollectionRecord(customerId: $customerId, customerName: $customerName, bookingId: $bookingId, collectionTime: $collectionTime, amount: $amount, mode: $mode, emiNumber: $emiNumber, lateFee: $lateFee, chequeNumber: $chequeNumber, transactionId: $transactionId, receiptNumber: $receiptNumber, location: $location, addressAtCollection: $addressAtCollection, photoUrls: $photoUrls, signatureUrl: $signatureUrl, notes: $notes, isVerified: $isVerified, verifiedAt: $verifiedAt, verifiedBy: $verifiedBy, disputeReason: $disputeReason)';
}


}

/// @nodoc
abstract mixin class $CollectionRecordCopyWith<$Res>  {
  factory $CollectionRecordCopyWith(CollectionRecord value, $Res Function(CollectionRecord) _then) = _$CollectionRecordCopyWithImpl;
@useResult
$Res call({
 String customerId, String customerName, String bookingId, DateTime collectionTime, double amount, PaymentMode mode, int? emiNumber, double? lateFee, String? chequeNumber, String? transactionId, String? receiptNumber, GeoLocation? location, String? addressAtCollection, List<String> photoUrls, String? signatureUrl, String? notes, bool? isVerified, DateTime? verifiedAt, String? verifiedBy, String? disputeReason
});




}
/// @nodoc
class _$CollectionRecordCopyWithImpl<$Res>
    implements $CollectionRecordCopyWith<$Res> {
  _$CollectionRecordCopyWithImpl(this._self, this._then);

  final CollectionRecord _self;
  final $Res Function(CollectionRecord) _then;

/// Create a copy of CollectionRecord
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? customerId = null,Object? customerName = null,Object? bookingId = null,Object? collectionTime = null,Object? amount = null,Object? mode = null,Object? emiNumber = freezed,Object? lateFee = freezed,Object? chequeNumber = freezed,Object? transactionId = freezed,Object? receiptNumber = freezed,Object? location = freezed,Object? addressAtCollection = freezed,Object? photoUrls = null,Object? signatureUrl = freezed,Object? notes = freezed,Object? isVerified = freezed,Object? verifiedAt = freezed,Object? verifiedBy = freezed,Object? disputeReason = freezed,}) {
  return _then(_self.copyWith(
customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,collectionTime: null == collectionTime ? _self.collectionTime : collectionTime // ignore: cast_nullable_to_non_nullable
as DateTime,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as double,mode: null == mode ? _self.mode : mode // ignore: cast_nullable_to_non_nullable
as PaymentMode,emiNumber: freezed == emiNumber ? _self.emiNumber : emiNumber // ignore: cast_nullable_to_non_nullable
as int?,lateFee: freezed == lateFee ? _self.lateFee : lateFee // ignore: cast_nullable_to_non_nullable
as double?,chequeNumber: freezed == chequeNumber ? _self.chequeNumber : chequeNumber // ignore: cast_nullable_to_non_nullable
as String?,transactionId: freezed == transactionId ? _self.transactionId : transactionId // ignore: cast_nullable_to_non_nullable
as String?,receiptNumber: freezed == receiptNumber ? _self.receiptNumber : receiptNumber // ignore: cast_nullable_to_non_nullable
as String?,location: freezed == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation?,addressAtCollection: freezed == addressAtCollection ? _self.addressAtCollection : addressAtCollection // ignore: cast_nullable_to_non_nullable
as String?,photoUrls: null == photoUrls ? _self.photoUrls : photoUrls // ignore: cast_nullable_to_non_nullable
as List<String>,signatureUrl: freezed == signatureUrl ? _self.signatureUrl : signatureUrl // ignore: cast_nullable_to_non_nullable
as String?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,isVerified: freezed == isVerified ? _self.isVerified : isVerified // ignore: cast_nullable_to_non_nullable
as bool?,verifiedAt: freezed == verifiedAt ? _self.verifiedAt : verifiedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,verifiedBy: freezed == verifiedBy ? _self.verifiedBy : verifiedBy // ignore: cast_nullable_to_non_nullable
as String?,disputeReason: freezed == disputeReason ? _self.disputeReason : disputeReason // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [CollectionRecord].
extension CollectionRecordPatterns on CollectionRecord {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CollectionRecord value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CollectionRecord() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CollectionRecord value)  $default,){
final _that = this;
switch (_that) {
case _CollectionRecord():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CollectionRecord value)?  $default,){
final _that = this;
switch (_that) {
case _CollectionRecord() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String customerId,  String customerName,  String bookingId,  DateTime collectionTime,  double amount,  PaymentMode mode,  int? emiNumber,  double? lateFee,  String? chequeNumber,  String? transactionId,  String? receiptNumber,  GeoLocation? location,  String? addressAtCollection,  List<String> photoUrls,  String? signatureUrl,  String? notes,  bool? isVerified,  DateTime? verifiedAt,  String? verifiedBy,  String? disputeReason)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CollectionRecord() when $default != null:
return $default(_that.customerId,_that.customerName,_that.bookingId,_that.collectionTime,_that.amount,_that.mode,_that.emiNumber,_that.lateFee,_that.chequeNumber,_that.transactionId,_that.receiptNumber,_that.location,_that.addressAtCollection,_that.photoUrls,_that.signatureUrl,_that.notes,_that.isVerified,_that.verifiedAt,_that.verifiedBy,_that.disputeReason);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String customerId,  String customerName,  String bookingId,  DateTime collectionTime,  double amount,  PaymentMode mode,  int? emiNumber,  double? lateFee,  String? chequeNumber,  String? transactionId,  String? receiptNumber,  GeoLocation? location,  String? addressAtCollection,  List<String> photoUrls,  String? signatureUrl,  String? notes,  bool? isVerified,  DateTime? verifiedAt,  String? verifiedBy,  String? disputeReason)  $default,) {final _that = this;
switch (_that) {
case _CollectionRecord():
return $default(_that.customerId,_that.customerName,_that.bookingId,_that.collectionTime,_that.amount,_that.mode,_that.emiNumber,_that.lateFee,_that.chequeNumber,_that.transactionId,_that.receiptNumber,_that.location,_that.addressAtCollection,_that.photoUrls,_that.signatureUrl,_that.notes,_that.isVerified,_that.verifiedAt,_that.verifiedBy,_that.disputeReason);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String customerId,  String customerName,  String bookingId,  DateTime collectionTime,  double amount,  PaymentMode mode,  int? emiNumber,  double? lateFee,  String? chequeNumber,  String? transactionId,  String? receiptNumber,  GeoLocation? location,  String? addressAtCollection,  List<String> photoUrls,  String? signatureUrl,  String? notes,  bool? isVerified,  DateTime? verifiedAt,  String? verifiedBy,  String? disputeReason)?  $default,) {final _that = this;
switch (_that) {
case _CollectionRecord() when $default != null:
return $default(_that.customerId,_that.customerName,_that.bookingId,_that.collectionTime,_that.amount,_that.mode,_that.emiNumber,_that.lateFee,_that.chequeNumber,_that.transactionId,_that.receiptNumber,_that.location,_that.addressAtCollection,_that.photoUrls,_that.signatureUrl,_that.notes,_that.isVerified,_that.verifiedAt,_that.verifiedBy,_that.disputeReason);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CollectionRecord implements CollectionRecord {
  const _CollectionRecord({required this.customerId, required this.customerName, required this.bookingId, required this.collectionTime, required this.amount, required this.mode, this.emiNumber, this.lateFee, this.chequeNumber, this.transactionId, this.receiptNumber, this.location, this.addressAtCollection, final  List<String> photoUrls = const [], this.signatureUrl, this.notes, this.isVerified, this.verifiedAt, this.verifiedBy, this.disputeReason}): _photoUrls = photoUrls;
  factory _CollectionRecord.fromJson(Map<String, dynamic> json) => _$CollectionRecordFromJson(json);

@override final  String customerId;
@override final  String customerName;
@override final  String bookingId;
@override final  DateTime collectionTime;
@override final  double amount;
@override final  PaymentMode mode;
// Details
@override final  int? emiNumber;
@override final  double? lateFee;
@override final  String? chequeNumber;
@override final  String? transactionId;
@override final  String? receiptNumber;
// Location
@override final  GeoLocation? location;
@override final  String? addressAtCollection;
// Proof
 final  List<String> _photoUrls;
// Proof
@override@JsonKey() List<String> get photoUrls {
  if (_photoUrls is EqualUnmodifiableListView) return _photoUrls;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_photoUrls);
}

// Payment proof photos
@override final  String? signatureUrl;
@override final  String? notes;
// Verification
@override final  bool? isVerified;
@override final  DateTime? verifiedAt;
@override final  String? verifiedBy;
@override final  String? disputeReason;

/// Create a copy of CollectionRecord
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CollectionRecordCopyWith<_CollectionRecord> get copyWith => __$CollectionRecordCopyWithImpl<_CollectionRecord>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CollectionRecordToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _CollectionRecord&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.collectionTime, collectionTime) || other.collectionTime == collectionTime)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.mode, mode) || other.mode == mode)&&(identical(other.emiNumber, emiNumber) || other.emiNumber == emiNumber)&&(identical(other.lateFee, lateFee) || other.lateFee == lateFee)&&(identical(other.chequeNumber, chequeNumber) || other.chequeNumber == chequeNumber)&&(identical(other.transactionId, transactionId) || other.transactionId == transactionId)&&(identical(other.receiptNumber, receiptNumber) || other.receiptNumber == receiptNumber)&&(identical(other.location, location) || other.location == location)&&(identical(other.addressAtCollection, addressAtCollection) || other.addressAtCollection == addressAtCollection)&&const DeepCollectionEquality().equals(other._photoUrls, _photoUrls)&&(identical(other.signatureUrl, signatureUrl) || other.signatureUrl == signatureUrl)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.isVerified, isVerified) || other.isVerified == isVerified)&&(identical(other.verifiedAt, verifiedAt) || other.verifiedAt == verifiedAt)&&(identical(other.verifiedBy, verifiedBy) || other.verifiedBy == verifiedBy)&&(identical(other.disputeReason, disputeReason) || other.disputeReason == disputeReason));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,customerId,customerName,bookingId,collectionTime,amount,mode,emiNumber,lateFee,chequeNumber,transactionId,receiptNumber,location,addressAtCollection,const DeepCollectionEquality().hash(_photoUrls),signatureUrl,notes,isVerified,verifiedAt,verifiedBy,disputeReason]);

@override
String toString() {
  return 'CollectionRecord(customerId: $customerId, customerName: $customerName, bookingId: $bookingId, collectionTime: $collectionTime, amount: $amount, mode: $mode, emiNumber: $emiNumber, lateFee: $lateFee, chequeNumber: $chequeNumber, transactionId: $transactionId, receiptNumber: $receiptNumber, location: $location, addressAtCollection: $addressAtCollection, photoUrls: $photoUrls, signatureUrl: $signatureUrl, notes: $notes, isVerified: $isVerified, verifiedAt: $verifiedAt, verifiedBy: $verifiedBy, disputeReason: $disputeReason)';
}


}

/// @nodoc
abstract mixin class _$CollectionRecordCopyWith<$Res> implements $CollectionRecordCopyWith<$Res> {
  factory _$CollectionRecordCopyWith(_CollectionRecord value, $Res Function(_CollectionRecord) _then) = __$CollectionRecordCopyWithImpl;
@override @useResult
$Res call({
 String customerId, String customerName, String bookingId, DateTime collectionTime, double amount, PaymentMode mode, int? emiNumber, double? lateFee, String? chequeNumber, String? transactionId, String? receiptNumber, GeoLocation? location, String? addressAtCollection, List<String> photoUrls, String? signatureUrl, String? notes, bool? isVerified, DateTime? verifiedAt, String? verifiedBy, String? disputeReason
});




}
/// @nodoc
class __$CollectionRecordCopyWithImpl<$Res>
    implements _$CollectionRecordCopyWith<$Res> {
  __$CollectionRecordCopyWithImpl(this._self, this._then);

  final _CollectionRecord _self;
  final $Res Function(_CollectionRecord) _then;

/// Create a copy of CollectionRecord
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? customerId = null,Object? customerName = null,Object? bookingId = null,Object? collectionTime = null,Object? amount = null,Object? mode = null,Object? emiNumber = freezed,Object? lateFee = freezed,Object? chequeNumber = freezed,Object? transactionId = freezed,Object? receiptNumber = freezed,Object? location = freezed,Object? addressAtCollection = freezed,Object? photoUrls = null,Object? signatureUrl = freezed,Object? notes = freezed,Object? isVerified = freezed,Object? verifiedAt = freezed,Object? verifiedBy = freezed,Object? disputeReason = freezed,}) {
  return _then(_CollectionRecord(
customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,collectionTime: null == collectionTime ? _self.collectionTime : collectionTime // ignore: cast_nullable_to_non_nullable
as DateTime,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as double,mode: null == mode ? _self.mode : mode // ignore: cast_nullable_to_non_nullable
as PaymentMode,emiNumber: freezed == emiNumber ? _self.emiNumber : emiNumber // ignore: cast_nullable_to_non_nullable
as int?,lateFee: freezed == lateFee ? _self.lateFee : lateFee // ignore: cast_nullable_to_non_nullable
as double?,chequeNumber: freezed == chequeNumber ? _self.chequeNumber : chequeNumber // ignore: cast_nullable_to_non_nullable
as String?,transactionId: freezed == transactionId ? _self.transactionId : transactionId // ignore: cast_nullable_to_non_nullable
as String?,receiptNumber: freezed == receiptNumber ? _self.receiptNumber : receiptNumber // ignore: cast_nullable_to_non_nullable
as String?,location: freezed == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation?,addressAtCollection: freezed == addressAtCollection ? _self.addressAtCollection : addressAtCollection // ignore: cast_nullable_to_non_nullable
as String?,photoUrls: null == photoUrls ? _self._photoUrls : photoUrls // ignore: cast_nullable_to_non_nullable
as List<String>,signatureUrl: freezed == signatureUrl ? _self.signatureUrl : signatureUrl // ignore: cast_nullable_to_non_nullable
as String?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,isVerified: freezed == isVerified ? _self.isVerified : isVerified // ignore: cast_nullable_to_non_nullable
as bool?,verifiedAt: freezed == verifiedAt ? _self.verifiedAt : verifiedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,verifiedBy: freezed == verifiedBy ? _self.verifiedBy : verifiedBy // ignore: cast_nullable_to_non_nullable
as String?,disputeReason: freezed == disputeReason ? _self.disputeReason : disputeReason // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$MonthlyCollectionPerformance {

 String get id; int get year; int get month; String get agentId;// Collections
 int get totalCollections; double get totalAmount; int get totalCustomers; int get newCustomersAdded;// Performance Metrics
 double get collectionRate;// % of target
 double get successRate;// % of visits successful
 int get ranking;// Among all agents
// Financial
 double get baseSalary; double get commissionEarned; double get incentives; double get deductions; double get totalEarnings;// Quality
 double get customerSatisfaction;// 0-100
 int get complaints; int get commendations;// Daily average
 double get avgCollectionsPerDay; double get avgAmountPerDay; double get avgDistancePerDay;// Target
 double get targetAmount; double get targetAchievement; PaymentStatus get paymentStatus; DateTime? get paidAt;
/// Create a copy of MonthlyCollectionPerformance
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MonthlyCollectionPerformanceCopyWith<MonthlyCollectionPerformance> get copyWith => _$MonthlyCollectionPerformanceCopyWithImpl<MonthlyCollectionPerformance>(this as MonthlyCollectionPerformance, _$identity);

  /// Serializes this MonthlyCollectionPerformance to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MonthlyCollectionPerformance&&(identical(other.id, id) || other.id == id)&&(identical(other.year, year) || other.year == year)&&(identical(other.month, month) || other.month == month)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.totalCollections, totalCollections) || other.totalCollections == totalCollections)&&(identical(other.totalAmount, totalAmount) || other.totalAmount == totalAmount)&&(identical(other.totalCustomers, totalCustomers) || other.totalCustomers == totalCustomers)&&(identical(other.newCustomersAdded, newCustomersAdded) || other.newCustomersAdded == newCustomersAdded)&&(identical(other.collectionRate, collectionRate) || other.collectionRate == collectionRate)&&(identical(other.successRate, successRate) || other.successRate == successRate)&&(identical(other.ranking, ranking) || other.ranking == ranking)&&(identical(other.baseSalary, baseSalary) || other.baseSalary == baseSalary)&&(identical(other.commissionEarned, commissionEarned) || other.commissionEarned == commissionEarned)&&(identical(other.incentives, incentives) || other.incentives == incentives)&&(identical(other.deductions, deductions) || other.deductions == deductions)&&(identical(other.totalEarnings, totalEarnings) || other.totalEarnings == totalEarnings)&&(identical(other.customerSatisfaction, customerSatisfaction) || other.customerSatisfaction == customerSatisfaction)&&(identical(other.complaints, complaints) || other.complaints == complaints)&&(identical(other.commendations, commendations) || other.commendations == commendations)&&(identical(other.avgCollectionsPerDay, avgCollectionsPerDay) || other.avgCollectionsPerDay == avgCollectionsPerDay)&&(identical(other.avgAmountPerDay, avgAmountPerDay) || other.avgAmountPerDay == avgAmountPerDay)&&(identical(other.avgDistancePerDay, avgDistancePerDay) || other.avgDistancePerDay == avgDistancePerDay)&&(identical(other.targetAmount, targetAmount) || other.targetAmount == targetAmount)&&(identical(other.targetAchievement, targetAchievement) || other.targetAchievement == targetAchievement)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.paidAt, paidAt) || other.paidAt == paidAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,year,month,agentId,totalCollections,totalAmount,totalCustomers,newCustomersAdded,collectionRate,successRate,ranking,baseSalary,commissionEarned,incentives,deductions,totalEarnings,customerSatisfaction,complaints,commendations,avgCollectionsPerDay,avgAmountPerDay,avgDistancePerDay,targetAmount,targetAchievement,paymentStatus,paidAt]);

@override
String toString() {
  return 'MonthlyCollectionPerformance(id: $id, year: $year, month: $month, agentId: $agentId, totalCollections: $totalCollections, totalAmount: $totalAmount, totalCustomers: $totalCustomers, newCustomersAdded: $newCustomersAdded, collectionRate: $collectionRate, successRate: $successRate, ranking: $ranking, baseSalary: $baseSalary, commissionEarned: $commissionEarned, incentives: $incentives, deductions: $deductions, totalEarnings: $totalEarnings, customerSatisfaction: $customerSatisfaction, complaints: $complaints, commendations: $commendations, avgCollectionsPerDay: $avgCollectionsPerDay, avgAmountPerDay: $avgAmountPerDay, avgDistancePerDay: $avgDistancePerDay, targetAmount: $targetAmount, targetAchievement: $targetAchievement, paymentStatus: $paymentStatus, paidAt: $paidAt)';
}


}

/// @nodoc
abstract mixin class $MonthlyCollectionPerformanceCopyWith<$Res>  {
  factory $MonthlyCollectionPerformanceCopyWith(MonthlyCollectionPerformance value, $Res Function(MonthlyCollectionPerformance) _then) = _$MonthlyCollectionPerformanceCopyWithImpl;
@useResult
$Res call({
 String id, int year, int month, String agentId, int totalCollections, double totalAmount, int totalCustomers, int newCustomersAdded, double collectionRate, double successRate, int ranking, double baseSalary, double commissionEarned, double incentives, double deductions, double totalEarnings, double customerSatisfaction, int complaints, int commendations, double avgCollectionsPerDay, double avgAmountPerDay, double avgDistancePerDay, double targetAmount, double targetAchievement, PaymentStatus paymentStatus, DateTime? paidAt
});




}
/// @nodoc
class _$MonthlyCollectionPerformanceCopyWithImpl<$Res>
    implements $MonthlyCollectionPerformanceCopyWith<$Res> {
  _$MonthlyCollectionPerformanceCopyWithImpl(this._self, this._then);

  final MonthlyCollectionPerformance _self;
  final $Res Function(MonthlyCollectionPerformance) _then;

/// Create a copy of MonthlyCollectionPerformance
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? year = null,Object? month = null,Object? agentId = null,Object? totalCollections = null,Object? totalAmount = null,Object? totalCustomers = null,Object? newCustomersAdded = null,Object? collectionRate = null,Object? successRate = null,Object? ranking = null,Object? baseSalary = null,Object? commissionEarned = null,Object? incentives = null,Object? deductions = null,Object? totalEarnings = null,Object? customerSatisfaction = null,Object? complaints = null,Object? commendations = null,Object? avgCollectionsPerDay = null,Object? avgAmountPerDay = null,Object? avgDistancePerDay = null,Object? targetAmount = null,Object? targetAchievement = null,Object? paymentStatus = null,Object? paidAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,year: null == year ? _self.year : year // ignore: cast_nullable_to_non_nullable
as int,month: null == month ? _self.month : month // ignore: cast_nullable_to_non_nullable
as int,agentId: null == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String,totalCollections: null == totalCollections ? _self.totalCollections : totalCollections // ignore: cast_nullable_to_non_nullable
as int,totalAmount: null == totalAmount ? _self.totalAmount : totalAmount // ignore: cast_nullable_to_non_nullable
as double,totalCustomers: null == totalCustomers ? _self.totalCustomers : totalCustomers // ignore: cast_nullable_to_non_nullable
as int,newCustomersAdded: null == newCustomersAdded ? _self.newCustomersAdded : newCustomersAdded // ignore: cast_nullable_to_non_nullable
as int,collectionRate: null == collectionRate ? _self.collectionRate : collectionRate // ignore: cast_nullable_to_non_nullable
as double,successRate: null == successRate ? _self.successRate : successRate // ignore: cast_nullable_to_non_nullable
as double,ranking: null == ranking ? _self.ranking : ranking // ignore: cast_nullable_to_non_nullable
as int,baseSalary: null == baseSalary ? _self.baseSalary : baseSalary // ignore: cast_nullable_to_non_nullable
as double,commissionEarned: null == commissionEarned ? _self.commissionEarned : commissionEarned // ignore: cast_nullable_to_non_nullable
as double,incentives: null == incentives ? _self.incentives : incentives // ignore: cast_nullable_to_non_nullable
as double,deductions: null == deductions ? _self.deductions : deductions // ignore: cast_nullable_to_non_nullable
as double,totalEarnings: null == totalEarnings ? _self.totalEarnings : totalEarnings // ignore: cast_nullable_to_non_nullable
as double,customerSatisfaction: null == customerSatisfaction ? _self.customerSatisfaction : customerSatisfaction // ignore: cast_nullable_to_non_nullable
as double,complaints: null == complaints ? _self.complaints : complaints // ignore: cast_nullable_to_non_nullable
as int,commendations: null == commendations ? _self.commendations : commendations // ignore: cast_nullable_to_non_nullable
as int,avgCollectionsPerDay: null == avgCollectionsPerDay ? _self.avgCollectionsPerDay : avgCollectionsPerDay // ignore: cast_nullable_to_non_nullable
as double,avgAmountPerDay: null == avgAmountPerDay ? _self.avgAmountPerDay : avgAmountPerDay // ignore: cast_nullable_to_non_nullable
as double,avgDistancePerDay: null == avgDistancePerDay ? _self.avgDistancePerDay : avgDistancePerDay // ignore: cast_nullable_to_non_nullable
as double,targetAmount: null == targetAmount ? _self.targetAmount : targetAmount // ignore: cast_nullable_to_non_nullable
as double,targetAchievement: null == targetAchievement ? _self.targetAchievement : targetAchievement // ignore: cast_nullable_to_non_nullable
as double,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as PaymentStatus,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [MonthlyCollectionPerformance].
extension MonthlyCollectionPerformancePatterns on MonthlyCollectionPerformance {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MonthlyCollectionPerformance value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MonthlyCollectionPerformance() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MonthlyCollectionPerformance value)  $default,){
final _that = this;
switch (_that) {
case _MonthlyCollectionPerformance():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MonthlyCollectionPerformance value)?  $default,){
final _that = this;
switch (_that) {
case _MonthlyCollectionPerformance() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  int year,  int month,  String agentId,  int totalCollections,  double totalAmount,  int totalCustomers,  int newCustomersAdded,  double collectionRate,  double successRate,  int ranking,  double baseSalary,  double commissionEarned,  double incentives,  double deductions,  double totalEarnings,  double customerSatisfaction,  int complaints,  int commendations,  double avgCollectionsPerDay,  double avgAmountPerDay,  double avgDistancePerDay,  double targetAmount,  double targetAchievement,  PaymentStatus paymentStatus,  DateTime? paidAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MonthlyCollectionPerformance() when $default != null:
return $default(_that.id,_that.year,_that.month,_that.agentId,_that.totalCollections,_that.totalAmount,_that.totalCustomers,_that.newCustomersAdded,_that.collectionRate,_that.successRate,_that.ranking,_that.baseSalary,_that.commissionEarned,_that.incentives,_that.deductions,_that.totalEarnings,_that.customerSatisfaction,_that.complaints,_that.commendations,_that.avgCollectionsPerDay,_that.avgAmountPerDay,_that.avgDistancePerDay,_that.targetAmount,_that.targetAchievement,_that.paymentStatus,_that.paidAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  int year,  int month,  String agentId,  int totalCollections,  double totalAmount,  int totalCustomers,  int newCustomersAdded,  double collectionRate,  double successRate,  int ranking,  double baseSalary,  double commissionEarned,  double incentives,  double deductions,  double totalEarnings,  double customerSatisfaction,  int complaints,  int commendations,  double avgCollectionsPerDay,  double avgAmountPerDay,  double avgDistancePerDay,  double targetAmount,  double targetAchievement,  PaymentStatus paymentStatus,  DateTime? paidAt)  $default,) {final _that = this;
switch (_that) {
case _MonthlyCollectionPerformance():
return $default(_that.id,_that.year,_that.month,_that.agentId,_that.totalCollections,_that.totalAmount,_that.totalCustomers,_that.newCustomersAdded,_that.collectionRate,_that.successRate,_that.ranking,_that.baseSalary,_that.commissionEarned,_that.incentives,_that.deductions,_that.totalEarnings,_that.customerSatisfaction,_that.complaints,_that.commendations,_that.avgCollectionsPerDay,_that.avgAmountPerDay,_that.avgDistancePerDay,_that.targetAmount,_that.targetAchievement,_that.paymentStatus,_that.paidAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  int year,  int month,  String agentId,  int totalCollections,  double totalAmount,  int totalCustomers,  int newCustomersAdded,  double collectionRate,  double successRate,  int ranking,  double baseSalary,  double commissionEarned,  double incentives,  double deductions,  double totalEarnings,  double customerSatisfaction,  int complaints,  int commendations,  double avgCollectionsPerDay,  double avgAmountPerDay,  double avgDistancePerDay,  double targetAmount,  double targetAchievement,  PaymentStatus paymentStatus,  DateTime? paidAt)?  $default,) {final _that = this;
switch (_that) {
case _MonthlyCollectionPerformance() when $default != null:
return $default(_that.id,_that.year,_that.month,_that.agentId,_that.totalCollections,_that.totalAmount,_that.totalCustomers,_that.newCustomersAdded,_that.collectionRate,_that.successRate,_that.ranking,_that.baseSalary,_that.commissionEarned,_that.incentives,_that.deductions,_that.totalEarnings,_that.customerSatisfaction,_that.complaints,_that.commendations,_that.avgCollectionsPerDay,_that.avgAmountPerDay,_that.avgDistancePerDay,_that.targetAmount,_that.targetAchievement,_that.paymentStatus,_that.paidAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _MonthlyCollectionPerformance implements MonthlyCollectionPerformance {
  const _MonthlyCollectionPerformance({required this.id, required this.year, required this.month, required this.agentId, this.totalCollections = 0, this.totalAmount = 0, this.totalCustomers = 0, this.newCustomersAdded = 0, this.collectionRate = 0, this.successRate = 0, this.ranking = 0, this.baseSalary = 0, this.commissionEarned = 0, this.incentives = 0, this.deductions = 0, this.totalEarnings = 0, this.customerSatisfaction = 0, this.complaints = 0, this.commendations = 0, this.avgCollectionsPerDay = 0, this.avgAmountPerDay = 0, this.avgDistancePerDay = 0, this.targetAmount = 0, this.targetAchievement = 0, required this.paymentStatus, this.paidAt});
  factory _MonthlyCollectionPerformance.fromJson(Map<String, dynamic> json) => _$MonthlyCollectionPerformanceFromJson(json);

@override final  String id;
@override final  int year;
@override final  int month;
@override final  String agentId;
// Collections
@override@JsonKey() final  int totalCollections;
@override@JsonKey() final  double totalAmount;
@override@JsonKey() final  int totalCustomers;
@override@JsonKey() final  int newCustomersAdded;
// Performance Metrics
@override@JsonKey() final  double collectionRate;
// % of target
@override@JsonKey() final  double successRate;
// % of visits successful
@override@JsonKey() final  int ranking;
// Among all agents
// Financial
@override@JsonKey() final  double baseSalary;
@override@JsonKey() final  double commissionEarned;
@override@JsonKey() final  double incentives;
@override@JsonKey() final  double deductions;
@override@JsonKey() final  double totalEarnings;
// Quality
@override@JsonKey() final  double customerSatisfaction;
// 0-100
@override@JsonKey() final  int complaints;
@override@JsonKey() final  int commendations;
// Daily average
@override@JsonKey() final  double avgCollectionsPerDay;
@override@JsonKey() final  double avgAmountPerDay;
@override@JsonKey() final  double avgDistancePerDay;
// Target
@override@JsonKey() final  double targetAmount;
@override@JsonKey() final  double targetAchievement;
@override final  PaymentStatus paymentStatus;
@override final  DateTime? paidAt;

/// Create a copy of MonthlyCollectionPerformance
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MonthlyCollectionPerformanceCopyWith<_MonthlyCollectionPerformance> get copyWith => __$MonthlyCollectionPerformanceCopyWithImpl<_MonthlyCollectionPerformance>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$MonthlyCollectionPerformanceToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _MonthlyCollectionPerformance&&(identical(other.id, id) || other.id == id)&&(identical(other.year, year) || other.year == year)&&(identical(other.month, month) || other.month == month)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.totalCollections, totalCollections) || other.totalCollections == totalCollections)&&(identical(other.totalAmount, totalAmount) || other.totalAmount == totalAmount)&&(identical(other.totalCustomers, totalCustomers) || other.totalCustomers == totalCustomers)&&(identical(other.newCustomersAdded, newCustomersAdded) || other.newCustomersAdded == newCustomersAdded)&&(identical(other.collectionRate, collectionRate) || other.collectionRate == collectionRate)&&(identical(other.successRate, successRate) || other.successRate == successRate)&&(identical(other.ranking, ranking) || other.ranking == ranking)&&(identical(other.baseSalary, baseSalary) || other.baseSalary == baseSalary)&&(identical(other.commissionEarned, commissionEarned) || other.commissionEarned == commissionEarned)&&(identical(other.incentives, incentives) || other.incentives == incentives)&&(identical(other.deductions, deductions) || other.deductions == deductions)&&(identical(other.totalEarnings, totalEarnings) || other.totalEarnings == totalEarnings)&&(identical(other.customerSatisfaction, customerSatisfaction) || other.customerSatisfaction == customerSatisfaction)&&(identical(other.complaints, complaints) || other.complaints == complaints)&&(identical(other.commendations, commendations) || other.commendations == commendations)&&(identical(other.avgCollectionsPerDay, avgCollectionsPerDay) || other.avgCollectionsPerDay == avgCollectionsPerDay)&&(identical(other.avgAmountPerDay, avgAmountPerDay) || other.avgAmountPerDay == avgAmountPerDay)&&(identical(other.avgDistancePerDay, avgDistancePerDay) || other.avgDistancePerDay == avgDistancePerDay)&&(identical(other.targetAmount, targetAmount) || other.targetAmount == targetAmount)&&(identical(other.targetAchievement, targetAchievement) || other.targetAchievement == targetAchievement)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.paidAt, paidAt) || other.paidAt == paidAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,year,month,agentId,totalCollections,totalAmount,totalCustomers,newCustomersAdded,collectionRate,successRate,ranking,baseSalary,commissionEarned,incentives,deductions,totalEarnings,customerSatisfaction,complaints,commendations,avgCollectionsPerDay,avgAmountPerDay,avgDistancePerDay,targetAmount,targetAchievement,paymentStatus,paidAt]);

@override
String toString() {
  return 'MonthlyCollectionPerformance(id: $id, year: $year, month: $month, agentId: $agentId, totalCollections: $totalCollections, totalAmount: $totalAmount, totalCustomers: $totalCustomers, newCustomersAdded: $newCustomersAdded, collectionRate: $collectionRate, successRate: $successRate, ranking: $ranking, baseSalary: $baseSalary, commissionEarned: $commissionEarned, incentives: $incentives, deductions: $deductions, totalEarnings: $totalEarnings, customerSatisfaction: $customerSatisfaction, complaints: $complaints, commendations: $commendations, avgCollectionsPerDay: $avgCollectionsPerDay, avgAmountPerDay: $avgAmountPerDay, avgDistancePerDay: $avgDistancePerDay, targetAmount: $targetAmount, targetAchievement: $targetAchievement, paymentStatus: $paymentStatus, paidAt: $paidAt)';
}


}

/// @nodoc
abstract mixin class _$MonthlyCollectionPerformanceCopyWith<$Res> implements $MonthlyCollectionPerformanceCopyWith<$Res> {
  factory _$MonthlyCollectionPerformanceCopyWith(_MonthlyCollectionPerformance value, $Res Function(_MonthlyCollectionPerformance) _then) = __$MonthlyCollectionPerformanceCopyWithImpl;
@override @useResult
$Res call({
 String id, int year, int month, String agentId, int totalCollections, double totalAmount, int totalCustomers, int newCustomersAdded, double collectionRate, double successRate, int ranking, double baseSalary, double commissionEarned, double incentives, double deductions, double totalEarnings, double customerSatisfaction, int complaints, int commendations, double avgCollectionsPerDay, double avgAmountPerDay, double avgDistancePerDay, double targetAmount, double targetAchievement, PaymentStatus paymentStatus, DateTime? paidAt
});




}
/// @nodoc
class __$MonthlyCollectionPerformanceCopyWithImpl<$Res>
    implements _$MonthlyCollectionPerformanceCopyWith<$Res> {
  __$MonthlyCollectionPerformanceCopyWithImpl(this._self, this._then);

  final _MonthlyCollectionPerformance _self;
  final $Res Function(_MonthlyCollectionPerformance) _then;

/// Create a copy of MonthlyCollectionPerformance
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? year = null,Object? month = null,Object? agentId = null,Object? totalCollections = null,Object? totalAmount = null,Object? totalCustomers = null,Object? newCustomersAdded = null,Object? collectionRate = null,Object? successRate = null,Object? ranking = null,Object? baseSalary = null,Object? commissionEarned = null,Object? incentives = null,Object? deductions = null,Object? totalEarnings = null,Object? customerSatisfaction = null,Object? complaints = null,Object? commendations = null,Object? avgCollectionsPerDay = null,Object? avgAmountPerDay = null,Object? avgDistancePerDay = null,Object? targetAmount = null,Object? targetAchievement = null,Object? paymentStatus = null,Object? paidAt = freezed,}) {
  return _then(_MonthlyCollectionPerformance(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,year: null == year ? _self.year : year // ignore: cast_nullable_to_non_nullable
as int,month: null == month ? _self.month : month // ignore: cast_nullable_to_non_nullable
as int,agentId: null == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String,totalCollections: null == totalCollections ? _self.totalCollections : totalCollections // ignore: cast_nullable_to_non_nullable
as int,totalAmount: null == totalAmount ? _self.totalAmount : totalAmount // ignore: cast_nullable_to_non_nullable
as double,totalCustomers: null == totalCustomers ? _self.totalCustomers : totalCustomers // ignore: cast_nullable_to_non_nullable
as int,newCustomersAdded: null == newCustomersAdded ? _self.newCustomersAdded : newCustomersAdded // ignore: cast_nullable_to_non_nullable
as int,collectionRate: null == collectionRate ? _self.collectionRate : collectionRate // ignore: cast_nullable_to_non_nullable
as double,successRate: null == successRate ? _self.successRate : successRate // ignore: cast_nullable_to_non_nullable
as double,ranking: null == ranking ? _self.ranking : ranking // ignore: cast_nullable_to_non_nullable
as int,baseSalary: null == baseSalary ? _self.baseSalary : baseSalary // ignore: cast_nullable_to_non_nullable
as double,commissionEarned: null == commissionEarned ? _self.commissionEarned : commissionEarned // ignore: cast_nullable_to_non_nullable
as double,incentives: null == incentives ? _self.incentives : incentives // ignore: cast_nullable_to_non_nullable
as double,deductions: null == deductions ? _self.deductions : deductions // ignore: cast_nullable_to_non_nullable
as double,totalEarnings: null == totalEarnings ? _self.totalEarnings : totalEarnings // ignore: cast_nullable_to_non_nullable
as double,customerSatisfaction: null == customerSatisfaction ? _self.customerSatisfaction : customerSatisfaction // ignore: cast_nullable_to_non_nullable
as double,complaints: null == complaints ? _self.complaints : complaints // ignore: cast_nullable_to_non_nullable
as int,commendations: null == commendations ? _self.commendations : commendations // ignore: cast_nullable_to_non_nullable
as int,avgCollectionsPerDay: null == avgCollectionsPerDay ? _self.avgCollectionsPerDay : avgCollectionsPerDay // ignore: cast_nullable_to_non_nullable
as double,avgAmountPerDay: null == avgAmountPerDay ? _self.avgAmountPerDay : avgAmountPerDay // ignore: cast_nullable_to_non_nullable
as double,avgDistancePerDay: null == avgDistancePerDay ? _self.avgDistancePerDay : avgDistancePerDay // ignore: cast_nullable_to_non_nullable
as double,targetAmount: null == targetAmount ? _self.targetAmount : targetAmount // ignore: cast_nullable_to_non_nullable
as double,targetAchievement: null == targetAchievement ? _self.targetAchievement : targetAchievement // ignore: cast_nullable_to_non_nullable
as double,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as PaymentStatus,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$LocationTracking {

 DateTime get timestamp; GeoLocation get location; String? get activity;// traveling, visiting, collecting, break
 String? get customerId;
/// Create a copy of LocationTracking
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LocationTrackingCopyWith<LocationTracking> get copyWith => _$LocationTrackingCopyWithImpl<LocationTracking>(this as LocationTracking, _$identity);

  /// Serializes this LocationTracking to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is LocationTracking&&(identical(other.timestamp, timestamp) || other.timestamp == timestamp)&&(identical(other.location, location) || other.location == location)&&(identical(other.activity, activity) || other.activity == activity)&&(identical(other.customerId, customerId) || other.customerId == customerId));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,timestamp,location,activity,customerId);

@override
String toString() {
  return 'LocationTracking(timestamp: $timestamp, location: $location, activity: $activity, customerId: $customerId)';
}


}

/// @nodoc
abstract mixin class $LocationTrackingCopyWith<$Res>  {
  factory $LocationTrackingCopyWith(LocationTracking value, $Res Function(LocationTracking) _then) = _$LocationTrackingCopyWithImpl;
@useResult
$Res call({
 DateTime timestamp, GeoLocation location, String? activity, String? customerId
});




}
/// @nodoc
class _$LocationTrackingCopyWithImpl<$Res>
    implements $LocationTrackingCopyWith<$Res> {
  _$LocationTrackingCopyWithImpl(this._self, this._then);

  final LocationTracking _self;
  final $Res Function(LocationTracking) _then;

/// Create a copy of LocationTracking
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? timestamp = null,Object? location = null,Object? activity = freezed,Object? customerId = freezed,}) {
  return _then(_self.copyWith(
timestamp: null == timestamp ? _self.timestamp : timestamp // ignore: cast_nullable_to_non_nullable
as DateTime,location: null == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation,activity: freezed == activity ? _self.activity : activity // ignore: cast_nullable_to_non_nullable
as String?,customerId: freezed == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [LocationTracking].
extension LocationTrackingPatterns on LocationTracking {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _LocationTracking value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _LocationTracking() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _LocationTracking value)  $default,){
final _that = this;
switch (_that) {
case _LocationTracking():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _LocationTracking value)?  $default,){
final _that = this;
switch (_that) {
case _LocationTracking() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( DateTime timestamp,  GeoLocation location,  String? activity,  String? customerId)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _LocationTracking() when $default != null:
return $default(_that.timestamp,_that.location,_that.activity,_that.customerId);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( DateTime timestamp,  GeoLocation location,  String? activity,  String? customerId)  $default,) {final _that = this;
switch (_that) {
case _LocationTracking():
return $default(_that.timestamp,_that.location,_that.activity,_that.customerId);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( DateTime timestamp,  GeoLocation location,  String? activity,  String? customerId)?  $default,) {final _that = this;
switch (_that) {
case _LocationTracking() when $default != null:
return $default(_that.timestamp,_that.location,_that.activity,_that.customerId);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _LocationTracking implements LocationTracking {
  const _LocationTracking({required this.timestamp, required this.location, this.activity, this.customerId});
  factory _LocationTracking.fromJson(Map<String, dynamic> json) => _$LocationTrackingFromJson(json);

@override final  DateTime timestamp;
@override final  GeoLocation location;
@override final  String? activity;
// traveling, visiting, collecting, break
@override final  String? customerId;

/// Create a copy of LocationTracking
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$LocationTrackingCopyWith<_LocationTracking> get copyWith => __$LocationTrackingCopyWithImpl<_LocationTracking>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$LocationTrackingToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _LocationTracking&&(identical(other.timestamp, timestamp) || other.timestamp == timestamp)&&(identical(other.location, location) || other.location == location)&&(identical(other.activity, activity) || other.activity == activity)&&(identical(other.customerId, customerId) || other.customerId == customerId));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,timestamp,location,activity,customerId);

@override
String toString() {
  return 'LocationTracking(timestamp: $timestamp, location: $location, activity: $activity, customerId: $customerId)';
}


}

/// @nodoc
abstract mixin class _$LocationTrackingCopyWith<$Res> implements $LocationTrackingCopyWith<$Res> {
  factory _$LocationTrackingCopyWith(_LocationTracking value, $Res Function(_LocationTracking) _then) = __$LocationTrackingCopyWithImpl;
@override @useResult
$Res call({
 DateTime timestamp, GeoLocation location, String? activity, String? customerId
});




}
/// @nodoc
class __$LocationTrackingCopyWithImpl<$Res>
    implements _$LocationTrackingCopyWith<$Res> {
  __$LocationTrackingCopyWithImpl(this._self, this._then);

  final _LocationTracking _self;
  final $Res Function(_LocationTracking) _then;

/// Create a copy of LocationTracking
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? timestamp = null,Object? location = null,Object? activity = freezed,Object? customerId = freezed,}) {
  return _then(_LocationTracking(
timestamp: null == timestamp ? _self.timestamp : timestamp // ignore: cast_nullable_to_non_nullable
as DateTime,location: null == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation,activity: freezed == activity ? _self.activity : activity // ignore: cast_nullable_to_non_nullable
as String?,customerId: freezed == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$EMIDueList {

 String get id; DateTime get generatedAt; String get agentId; DateTime get forDate;// List of dues
 List<EMIDueItem> get dues;// Summary
 int get totalDues; double get totalAmount; int get highPriorityDues;// Overdue by > 15 days
 int get mediumPriorityDues;// Overdue by 7-15 days
 int get regularDues;// Due today or future
// Status
 bool get isCompleted; DateTime? get completedAt; int get collectionsMade; double get collectedAmount;
/// Create a copy of EMIDueList
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$EMIDueListCopyWith<EMIDueList> get copyWith => _$EMIDueListCopyWithImpl<EMIDueList>(this as EMIDueList, _$identity);

  /// Serializes this EMIDueList to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is EMIDueList&&(identical(other.id, id) || other.id == id)&&(identical(other.generatedAt, generatedAt) || other.generatedAt == generatedAt)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.forDate, forDate) || other.forDate == forDate)&&const DeepCollectionEquality().equals(other.dues, dues)&&(identical(other.totalDues, totalDues) || other.totalDues == totalDues)&&(identical(other.totalAmount, totalAmount) || other.totalAmount == totalAmount)&&(identical(other.highPriorityDues, highPriorityDues) || other.highPriorityDues == highPriorityDues)&&(identical(other.mediumPriorityDues, mediumPriorityDues) || other.mediumPriorityDues == mediumPriorityDues)&&(identical(other.regularDues, regularDues) || other.regularDues == regularDues)&&(identical(other.isCompleted, isCompleted) || other.isCompleted == isCompleted)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt)&&(identical(other.collectionsMade, collectionsMade) || other.collectionsMade == collectionsMade)&&(identical(other.collectedAmount, collectedAmount) || other.collectedAmount == collectedAmount));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,generatedAt,agentId,forDate,const DeepCollectionEquality().hash(dues),totalDues,totalAmount,highPriorityDues,mediumPriorityDues,regularDues,isCompleted,completedAt,collectionsMade,collectedAmount);

@override
String toString() {
  return 'EMIDueList(id: $id, generatedAt: $generatedAt, agentId: $agentId, forDate: $forDate, dues: $dues, totalDues: $totalDues, totalAmount: $totalAmount, highPriorityDues: $highPriorityDues, mediumPriorityDues: $mediumPriorityDues, regularDues: $regularDues, isCompleted: $isCompleted, completedAt: $completedAt, collectionsMade: $collectionsMade, collectedAmount: $collectedAmount)';
}


}

/// @nodoc
abstract mixin class $EMIDueListCopyWith<$Res>  {
  factory $EMIDueListCopyWith(EMIDueList value, $Res Function(EMIDueList) _then) = _$EMIDueListCopyWithImpl;
@useResult
$Res call({
 String id, DateTime generatedAt, String agentId, DateTime forDate, List<EMIDueItem> dues, int totalDues, double totalAmount, int highPriorityDues, int mediumPriorityDues, int regularDues, bool isCompleted, DateTime? completedAt, int collectionsMade, double collectedAmount
});




}
/// @nodoc
class _$EMIDueListCopyWithImpl<$Res>
    implements $EMIDueListCopyWith<$Res> {
  _$EMIDueListCopyWithImpl(this._self, this._then);

  final EMIDueList _self;
  final $Res Function(EMIDueList) _then;

/// Create a copy of EMIDueList
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? generatedAt = null,Object? agentId = null,Object? forDate = null,Object? dues = null,Object? totalDues = null,Object? totalAmount = null,Object? highPriorityDues = null,Object? mediumPriorityDues = null,Object? regularDues = null,Object? isCompleted = null,Object? completedAt = freezed,Object? collectionsMade = null,Object? collectedAmount = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,generatedAt: null == generatedAt ? _self.generatedAt : generatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,agentId: null == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String,forDate: null == forDate ? _self.forDate : forDate // ignore: cast_nullable_to_non_nullable
as DateTime,dues: null == dues ? _self.dues : dues // ignore: cast_nullable_to_non_nullable
as List<EMIDueItem>,totalDues: null == totalDues ? _self.totalDues : totalDues // ignore: cast_nullable_to_non_nullable
as int,totalAmount: null == totalAmount ? _self.totalAmount : totalAmount // ignore: cast_nullable_to_non_nullable
as double,highPriorityDues: null == highPriorityDues ? _self.highPriorityDues : highPriorityDues // ignore: cast_nullable_to_non_nullable
as int,mediumPriorityDues: null == mediumPriorityDues ? _self.mediumPriorityDues : mediumPriorityDues // ignore: cast_nullable_to_non_nullable
as int,regularDues: null == regularDues ? _self.regularDues : regularDues // ignore: cast_nullable_to_non_nullable
as int,isCompleted: null == isCompleted ? _self.isCompleted : isCompleted // ignore: cast_nullable_to_non_nullable
as bool,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,collectionsMade: null == collectionsMade ? _self.collectionsMade : collectionsMade // ignore: cast_nullable_to_non_nullable
as int,collectedAmount: null == collectedAmount ? _self.collectedAmount : collectedAmount // ignore: cast_nullable_to_non_nullable
as double,
  ));
}

}


/// Adds pattern-matching-related methods to [EMIDueList].
extension EMIDueListPatterns on EMIDueList {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _EMIDueList value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _EMIDueList() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _EMIDueList value)  $default,){
final _that = this;
switch (_that) {
case _EMIDueList():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _EMIDueList value)?  $default,){
final _that = this;
switch (_that) {
case _EMIDueList() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  DateTime generatedAt,  String agentId,  DateTime forDate,  List<EMIDueItem> dues,  int totalDues,  double totalAmount,  int highPriorityDues,  int mediumPriorityDues,  int regularDues,  bool isCompleted,  DateTime? completedAt,  int collectionsMade,  double collectedAmount)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _EMIDueList() when $default != null:
return $default(_that.id,_that.generatedAt,_that.agentId,_that.forDate,_that.dues,_that.totalDues,_that.totalAmount,_that.highPriorityDues,_that.mediumPriorityDues,_that.regularDues,_that.isCompleted,_that.completedAt,_that.collectionsMade,_that.collectedAmount);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  DateTime generatedAt,  String agentId,  DateTime forDate,  List<EMIDueItem> dues,  int totalDues,  double totalAmount,  int highPriorityDues,  int mediumPriorityDues,  int regularDues,  bool isCompleted,  DateTime? completedAt,  int collectionsMade,  double collectedAmount)  $default,) {final _that = this;
switch (_that) {
case _EMIDueList():
return $default(_that.id,_that.generatedAt,_that.agentId,_that.forDate,_that.dues,_that.totalDues,_that.totalAmount,_that.highPriorityDues,_that.mediumPriorityDues,_that.regularDues,_that.isCompleted,_that.completedAt,_that.collectionsMade,_that.collectedAmount);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  DateTime generatedAt,  String agentId,  DateTime forDate,  List<EMIDueItem> dues,  int totalDues,  double totalAmount,  int highPriorityDues,  int mediumPriorityDues,  int regularDues,  bool isCompleted,  DateTime? completedAt,  int collectionsMade,  double collectedAmount)?  $default,) {final _that = this;
switch (_that) {
case _EMIDueList() when $default != null:
return $default(_that.id,_that.generatedAt,_that.agentId,_that.forDate,_that.dues,_that.totalDues,_that.totalAmount,_that.highPriorityDues,_that.mediumPriorityDues,_that.regularDues,_that.isCompleted,_that.completedAt,_that.collectionsMade,_that.collectedAmount);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _EMIDueList implements EMIDueList {
  const _EMIDueList({required this.id, required this.generatedAt, required this.agentId, required this.forDate, final  List<EMIDueItem> dues = const [], this.totalDues = 0, this.totalAmount = 0, this.highPriorityDues = 0, this.mediumPriorityDues = 0, this.regularDues = 0, this.isCompleted = false, this.completedAt, this.collectionsMade = 0, this.collectedAmount = 0}): _dues = dues;
  factory _EMIDueList.fromJson(Map<String, dynamic> json) => _$EMIDueListFromJson(json);

@override final  String id;
@override final  DateTime generatedAt;
@override final  String agentId;
@override final  DateTime forDate;
// List of dues
 final  List<EMIDueItem> _dues;
// List of dues
@override@JsonKey() List<EMIDueItem> get dues {
  if (_dues is EqualUnmodifiableListView) return _dues;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_dues);
}

// Summary
@override@JsonKey() final  int totalDues;
@override@JsonKey() final  double totalAmount;
@override@JsonKey() final  int highPriorityDues;
// Overdue by > 15 days
@override@JsonKey() final  int mediumPriorityDues;
// Overdue by 7-15 days
@override@JsonKey() final  int regularDues;
// Due today or future
// Status
@override@JsonKey() final  bool isCompleted;
@override final  DateTime? completedAt;
@override@JsonKey() final  int collectionsMade;
@override@JsonKey() final  double collectedAmount;

/// Create a copy of EMIDueList
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$EMIDueListCopyWith<_EMIDueList> get copyWith => __$EMIDueListCopyWithImpl<_EMIDueList>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$EMIDueListToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _EMIDueList&&(identical(other.id, id) || other.id == id)&&(identical(other.generatedAt, generatedAt) || other.generatedAt == generatedAt)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.forDate, forDate) || other.forDate == forDate)&&const DeepCollectionEquality().equals(other._dues, _dues)&&(identical(other.totalDues, totalDues) || other.totalDues == totalDues)&&(identical(other.totalAmount, totalAmount) || other.totalAmount == totalAmount)&&(identical(other.highPriorityDues, highPriorityDues) || other.highPriorityDues == highPriorityDues)&&(identical(other.mediumPriorityDues, mediumPriorityDues) || other.mediumPriorityDues == mediumPriorityDues)&&(identical(other.regularDues, regularDues) || other.regularDues == regularDues)&&(identical(other.isCompleted, isCompleted) || other.isCompleted == isCompleted)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt)&&(identical(other.collectionsMade, collectionsMade) || other.collectionsMade == collectionsMade)&&(identical(other.collectedAmount, collectedAmount) || other.collectedAmount == collectedAmount));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,generatedAt,agentId,forDate,const DeepCollectionEquality().hash(_dues),totalDues,totalAmount,highPriorityDues,mediumPriorityDues,regularDues,isCompleted,completedAt,collectionsMade,collectedAmount);

@override
String toString() {
  return 'EMIDueList(id: $id, generatedAt: $generatedAt, agentId: $agentId, forDate: $forDate, dues: $dues, totalDues: $totalDues, totalAmount: $totalAmount, highPriorityDues: $highPriorityDues, mediumPriorityDues: $mediumPriorityDues, regularDues: $regularDues, isCompleted: $isCompleted, completedAt: $completedAt, collectionsMade: $collectionsMade, collectedAmount: $collectedAmount)';
}


}

/// @nodoc
abstract mixin class _$EMIDueListCopyWith<$Res> implements $EMIDueListCopyWith<$Res> {
  factory _$EMIDueListCopyWith(_EMIDueList value, $Res Function(_EMIDueList) _then) = __$EMIDueListCopyWithImpl;
@override @useResult
$Res call({
 String id, DateTime generatedAt, String agentId, DateTime forDate, List<EMIDueItem> dues, int totalDues, double totalAmount, int highPriorityDues, int mediumPriorityDues, int regularDues, bool isCompleted, DateTime? completedAt, int collectionsMade, double collectedAmount
});




}
/// @nodoc
class __$EMIDueListCopyWithImpl<$Res>
    implements _$EMIDueListCopyWith<$Res> {
  __$EMIDueListCopyWithImpl(this._self, this._then);

  final _EMIDueList _self;
  final $Res Function(_EMIDueList) _then;

/// Create a copy of EMIDueList
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? generatedAt = null,Object? agentId = null,Object? forDate = null,Object? dues = null,Object? totalDues = null,Object? totalAmount = null,Object? highPriorityDues = null,Object? mediumPriorityDues = null,Object? regularDues = null,Object? isCompleted = null,Object? completedAt = freezed,Object? collectionsMade = null,Object? collectedAmount = null,}) {
  return _then(_EMIDueList(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,generatedAt: null == generatedAt ? _self.generatedAt : generatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,agentId: null == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String,forDate: null == forDate ? _self.forDate : forDate // ignore: cast_nullable_to_non_nullable
as DateTime,dues: null == dues ? _self._dues : dues // ignore: cast_nullable_to_non_nullable
as List<EMIDueItem>,totalDues: null == totalDues ? _self.totalDues : totalDues // ignore: cast_nullable_to_non_nullable
as int,totalAmount: null == totalAmount ? _self.totalAmount : totalAmount // ignore: cast_nullable_to_non_nullable
as double,highPriorityDues: null == highPriorityDues ? _self.highPriorityDues : highPriorityDues // ignore: cast_nullable_to_non_nullable
as int,mediumPriorityDues: null == mediumPriorityDues ? _self.mediumPriorityDues : mediumPriorityDues // ignore: cast_nullable_to_non_nullable
as int,regularDues: null == regularDues ? _self.regularDues : regularDues // ignore: cast_nullable_to_non_nullable
as int,isCompleted: null == isCompleted ? _self.isCompleted : isCompleted // ignore: cast_nullable_to_non_nullable
as bool,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,collectionsMade: null == collectionsMade ? _self.collectionsMade : collectionsMade // ignore: cast_nullable_to_non_nullable
as int,collectedAmount: null == collectedAmount ? _self.collectedAmount : collectedAmount // ignore: cast_nullable_to_non_nullable
as double,
  ));
}


}


/// @nodoc
mixin _$EMIDueItem {

 String get customerId; String get customerName; String get phone; String get address; String get bookingId; String get plotNumber; String get colonyName;// Due Details
 double get emiAmount; DateTime get dueDate; int get daysOverdue;// Total Dues
 double get totalDue;// Including late fees
 double get lateFee;// Status
 DuePriority get priority;// High, Medium, Low
 String? get lastVisitNotes; DateTime? get lastVisitDate;// Collection
 bool? get isCollected; double? get collectedAmount; DateTime? get collectedAt;// Location
 GeoLocation? get location; String? get landmark; String? get preferredTime;
/// Create a copy of EMIDueItem
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$EMIDueItemCopyWith<EMIDueItem> get copyWith => _$EMIDueItemCopyWithImpl<EMIDueItem>(this as EMIDueItem, _$identity);

  /// Serializes this EMIDueItem to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is EMIDueItem&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.address, address) || other.address == address)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.plotNumber, plotNumber) || other.plotNumber == plotNumber)&&(identical(other.colonyName, colonyName) || other.colonyName == colonyName)&&(identical(other.emiAmount, emiAmount) || other.emiAmount == emiAmount)&&(identical(other.dueDate, dueDate) || other.dueDate == dueDate)&&(identical(other.daysOverdue, daysOverdue) || other.daysOverdue == daysOverdue)&&(identical(other.totalDue, totalDue) || other.totalDue == totalDue)&&(identical(other.lateFee, lateFee) || other.lateFee == lateFee)&&(identical(other.priority, priority) || other.priority == priority)&&(identical(other.lastVisitNotes, lastVisitNotes) || other.lastVisitNotes == lastVisitNotes)&&(identical(other.lastVisitDate, lastVisitDate) || other.lastVisitDate == lastVisitDate)&&(identical(other.isCollected, isCollected) || other.isCollected == isCollected)&&(identical(other.collectedAmount, collectedAmount) || other.collectedAmount == collectedAmount)&&(identical(other.collectedAt, collectedAt) || other.collectedAt == collectedAt)&&(identical(other.location, location) || other.location == location)&&(identical(other.landmark, landmark) || other.landmark == landmark)&&(identical(other.preferredTime, preferredTime) || other.preferredTime == preferredTime));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,customerId,customerName,phone,address,bookingId,plotNumber,colonyName,emiAmount,dueDate,daysOverdue,totalDue,lateFee,priority,lastVisitNotes,lastVisitDate,isCollected,collectedAmount,collectedAt,location,landmark,preferredTime]);

@override
String toString() {
  return 'EMIDueItem(customerId: $customerId, customerName: $customerName, phone: $phone, address: $address, bookingId: $bookingId, plotNumber: $plotNumber, colonyName: $colonyName, emiAmount: $emiAmount, dueDate: $dueDate, daysOverdue: $daysOverdue, totalDue: $totalDue, lateFee: $lateFee, priority: $priority, lastVisitNotes: $lastVisitNotes, lastVisitDate: $lastVisitDate, isCollected: $isCollected, collectedAmount: $collectedAmount, collectedAt: $collectedAt, location: $location, landmark: $landmark, preferredTime: $preferredTime)';
}


}

/// @nodoc
abstract mixin class $EMIDueItemCopyWith<$Res>  {
  factory $EMIDueItemCopyWith(EMIDueItem value, $Res Function(EMIDueItem) _then) = _$EMIDueItemCopyWithImpl;
@useResult
$Res call({
 String customerId, String customerName, String phone, String address, String bookingId, String plotNumber, String colonyName, double emiAmount, DateTime dueDate, int daysOverdue, double totalDue, double lateFee, DuePriority priority, String? lastVisitNotes, DateTime? lastVisitDate, bool? isCollected, double? collectedAmount, DateTime? collectedAt, GeoLocation? location, String? landmark, String? preferredTime
});




}
/// @nodoc
class _$EMIDueItemCopyWithImpl<$Res>
    implements $EMIDueItemCopyWith<$Res> {
  _$EMIDueItemCopyWithImpl(this._self, this._then);

  final EMIDueItem _self;
  final $Res Function(EMIDueItem) _then;

/// Create a copy of EMIDueItem
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? customerId = null,Object? customerName = null,Object? phone = null,Object? address = null,Object? bookingId = null,Object? plotNumber = null,Object? colonyName = null,Object? emiAmount = null,Object? dueDate = null,Object? daysOverdue = null,Object? totalDue = null,Object? lateFee = null,Object? priority = null,Object? lastVisitNotes = freezed,Object? lastVisitDate = freezed,Object? isCollected = freezed,Object? collectedAmount = freezed,Object? collectedAt = freezed,Object? location = freezed,Object? landmark = freezed,Object? preferredTime = freezed,}) {
  return _then(_self.copyWith(
customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,address: null == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,plotNumber: null == plotNumber ? _self.plotNumber : plotNumber // ignore: cast_nullable_to_non_nullable
as String,colonyName: null == colonyName ? _self.colonyName : colonyName // ignore: cast_nullable_to_non_nullable
as String,emiAmount: null == emiAmount ? _self.emiAmount : emiAmount // ignore: cast_nullable_to_non_nullable
as double,dueDate: null == dueDate ? _self.dueDate : dueDate // ignore: cast_nullable_to_non_nullable
as DateTime,daysOverdue: null == daysOverdue ? _self.daysOverdue : daysOverdue // ignore: cast_nullable_to_non_nullable
as int,totalDue: null == totalDue ? _self.totalDue : totalDue // ignore: cast_nullable_to_non_nullable
as double,lateFee: null == lateFee ? _self.lateFee : lateFee // ignore: cast_nullable_to_non_nullable
as double,priority: null == priority ? _self.priority : priority // ignore: cast_nullable_to_non_nullable
as DuePriority,lastVisitNotes: freezed == lastVisitNotes ? _self.lastVisitNotes : lastVisitNotes // ignore: cast_nullable_to_non_nullable
as String?,lastVisitDate: freezed == lastVisitDate ? _self.lastVisitDate : lastVisitDate // ignore: cast_nullable_to_non_nullable
as DateTime?,isCollected: freezed == isCollected ? _self.isCollected : isCollected // ignore: cast_nullable_to_non_nullable
as bool?,collectedAmount: freezed == collectedAmount ? _self.collectedAmount : collectedAmount // ignore: cast_nullable_to_non_nullable
as double?,collectedAt: freezed == collectedAt ? _self.collectedAt : collectedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,location: freezed == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation?,landmark: freezed == landmark ? _self.landmark : landmark // ignore: cast_nullable_to_non_nullable
as String?,preferredTime: freezed == preferredTime ? _self.preferredTime : preferredTime // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [EMIDueItem].
extension EMIDueItemPatterns on EMIDueItem {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _EMIDueItem value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _EMIDueItem() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _EMIDueItem value)  $default,){
final _that = this;
switch (_that) {
case _EMIDueItem():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _EMIDueItem value)?  $default,){
final _that = this;
switch (_that) {
case _EMIDueItem() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String customerId,  String customerName,  String phone,  String address,  String bookingId,  String plotNumber,  String colonyName,  double emiAmount,  DateTime dueDate,  int daysOverdue,  double totalDue,  double lateFee,  DuePriority priority,  String? lastVisitNotes,  DateTime? lastVisitDate,  bool? isCollected,  double? collectedAmount,  DateTime? collectedAt,  GeoLocation? location,  String? landmark,  String? preferredTime)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _EMIDueItem() when $default != null:
return $default(_that.customerId,_that.customerName,_that.phone,_that.address,_that.bookingId,_that.plotNumber,_that.colonyName,_that.emiAmount,_that.dueDate,_that.daysOverdue,_that.totalDue,_that.lateFee,_that.priority,_that.lastVisitNotes,_that.lastVisitDate,_that.isCollected,_that.collectedAmount,_that.collectedAt,_that.location,_that.landmark,_that.preferredTime);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String customerId,  String customerName,  String phone,  String address,  String bookingId,  String plotNumber,  String colonyName,  double emiAmount,  DateTime dueDate,  int daysOverdue,  double totalDue,  double lateFee,  DuePriority priority,  String? lastVisitNotes,  DateTime? lastVisitDate,  bool? isCollected,  double? collectedAmount,  DateTime? collectedAt,  GeoLocation? location,  String? landmark,  String? preferredTime)  $default,) {final _that = this;
switch (_that) {
case _EMIDueItem():
return $default(_that.customerId,_that.customerName,_that.phone,_that.address,_that.bookingId,_that.plotNumber,_that.colonyName,_that.emiAmount,_that.dueDate,_that.daysOverdue,_that.totalDue,_that.lateFee,_that.priority,_that.lastVisitNotes,_that.lastVisitDate,_that.isCollected,_that.collectedAmount,_that.collectedAt,_that.location,_that.landmark,_that.preferredTime);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String customerId,  String customerName,  String phone,  String address,  String bookingId,  String plotNumber,  String colonyName,  double emiAmount,  DateTime dueDate,  int daysOverdue,  double totalDue,  double lateFee,  DuePriority priority,  String? lastVisitNotes,  DateTime? lastVisitDate,  bool? isCollected,  double? collectedAmount,  DateTime? collectedAt,  GeoLocation? location,  String? landmark,  String? preferredTime)?  $default,) {final _that = this;
switch (_that) {
case _EMIDueItem() when $default != null:
return $default(_that.customerId,_that.customerName,_that.phone,_that.address,_that.bookingId,_that.plotNumber,_that.colonyName,_that.emiAmount,_that.dueDate,_that.daysOverdue,_that.totalDue,_that.lateFee,_that.priority,_that.lastVisitNotes,_that.lastVisitDate,_that.isCollected,_that.collectedAmount,_that.collectedAt,_that.location,_that.landmark,_that.preferredTime);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _EMIDueItem implements EMIDueItem {
  const _EMIDueItem({required this.customerId, required this.customerName, required this.phone, required this.address, required this.bookingId, required this.plotNumber, required this.colonyName, required this.emiAmount, required this.dueDate, required this.daysOverdue, required this.totalDue, this.lateFee = 0, required this.priority, this.lastVisitNotes, this.lastVisitDate, this.isCollected, this.collectedAmount, this.collectedAt, this.location, this.landmark, this.preferredTime});
  factory _EMIDueItem.fromJson(Map<String, dynamic> json) => _$EMIDueItemFromJson(json);

@override final  String customerId;
@override final  String customerName;
@override final  String phone;
@override final  String address;
@override final  String bookingId;
@override final  String plotNumber;
@override final  String colonyName;
// Due Details
@override final  double emiAmount;
@override final  DateTime dueDate;
@override final  int daysOverdue;
// Total Dues
@override final  double totalDue;
// Including late fees
@override@JsonKey() final  double lateFee;
// Status
@override final  DuePriority priority;
// High, Medium, Low
@override final  String? lastVisitNotes;
@override final  DateTime? lastVisitDate;
// Collection
@override final  bool? isCollected;
@override final  double? collectedAmount;
@override final  DateTime? collectedAt;
// Location
@override final  GeoLocation? location;
@override final  String? landmark;
@override final  String? preferredTime;

/// Create a copy of EMIDueItem
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$EMIDueItemCopyWith<_EMIDueItem> get copyWith => __$EMIDueItemCopyWithImpl<_EMIDueItem>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$EMIDueItemToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _EMIDueItem&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.address, address) || other.address == address)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.plotNumber, plotNumber) || other.plotNumber == plotNumber)&&(identical(other.colonyName, colonyName) || other.colonyName == colonyName)&&(identical(other.emiAmount, emiAmount) || other.emiAmount == emiAmount)&&(identical(other.dueDate, dueDate) || other.dueDate == dueDate)&&(identical(other.daysOverdue, daysOverdue) || other.daysOverdue == daysOverdue)&&(identical(other.totalDue, totalDue) || other.totalDue == totalDue)&&(identical(other.lateFee, lateFee) || other.lateFee == lateFee)&&(identical(other.priority, priority) || other.priority == priority)&&(identical(other.lastVisitNotes, lastVisitNotes) || other.lastVisitNotes == lastVisitNotes)&&(identical(other.lastVisitDate, lastVisitDate) || other.lastVisitDate == lastVisitDate)&&(identical(other.isCollected, isCollected) || other.isCollected == isCollected)&&(identical(other.collectedAmount, collectedAmount) || other.collectedAmount == collectedAmount)&&(identical(other.collectedAt, collectedAt) || other.collectedAt == collectedAt)&&(identical(other.location, location) || other.location == location)&&(identical(other.landmark, landmark) || other.landmark == landmark)&&(identical(other.preferredTime, preferredTime) || other.preferredTime == preferredTime));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,customerId,customerName,phone,address,bookingId,plotNumber,colonyName,emiAmount,dueDate,daysOverdue,totalDue,lateFee,priority,lastVisitNotes,lastVisitDate,isCollected,collectedAmount,collectedAt,location,landmark,preferredTime]);

@override
String toString() {
  return 'EMIDueItem(customerId: $customerId, customerName: $customerName, phone: $phone, address: $address, bookingId: $bookingId, plotNumber: $plotNumber, colonyName: $colonyName, emiAmount: $emiAmount, dueDate: $dueDate, daysOverdue: $daysOverdue, totalDue: $totalDue, lateFee: $lateFee, priority: $priority, lastVisitNotes: $lastVisitNotes, lastVisitDate: $lastVisitDate, isCollected: $isCollected, collectedAmount: $collectedAmount, collectedAt: $collectedAt, location: $location, landmark: $landmark, preferredTime: $preferredTime)';
}


}

/// @nodoc
abstract mixin class _$EMIDueItemCopyWith<$Res> implements $EMIDueItemCopyWith<$Res> {
  factory _$EMIDueItemCopyWith(_EMIDueItem value, $Res Function(_EMIDueItem) _then) = __$EMIDueItemCopyWithImpl;
@override @useResult
$Res call({
 String customerId, String customerName, String phone, String address, String bookingId, String plotNumber, String colonyName, double emiAmount, DateTime dueDate, int daysOverdue, double totalDue, double lateFee, DuePriority priority, String? lastVisitNotes, DateTime? lastVisitDate, bool? isCollected, double? collectedAmount, DateTime? collectedAt, GeoLocation? location, String? landmark, String? preferredTime
});




}
/// @nodoc
class __$EMIDueItemCopyWithImpl<$Res>
    implements _$EMIDueItemCopyWith<$Res> {
  __$EMIDueItemCopyWithImpl(this._self, this._then);

  final _EMIDueItem _self;
  final $Res Function(_EMIDueItem) _then;

/// Create a copy of EMIDueItem
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? customerId = null,Object? customerName = null,Object? phone = null,Object? address = null,Object? bookingId = null,Object? plotNumber = null,Object? colonyName = null,Object? emiAmount = null,Object? dueDate = null,Object? daysOverdue = null,Object? totalDue = null,Object? lateFee = null,Object? priority = null,Object? lastVisitNotes = freezed,Object? lastVisitDate = freezed,Object? isCollected = freezed,Object? collectedAmount = freezed,Object? collectedAt = freezed,Object? location = freezed,Object? landmark = freezed,Object? preferredTime = freezed,}) {
  return _then(_EMIDueItem(
customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,address: null == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,plotNumber: null == plotNumber ? _self.plotNumber : plotNumber // ignore: cast_nullable_to_non_nullable
as String,colonyName: null == colonyName ? _self.colonyName : colonyName // ignore: cast_nullable_to_non_nullable
as String,emiAmount: null == emiAmount ? _self.emiAmount : emiAmount // ignore: cast_nullable_to_non_nullable
as double,dueDate: null == dueDate ? _self.dueDate : dueDate // ignore: cast_nullable_to_non_nullable
as DateTime,daysOverdue: null == daysOverdue ? _self.daysOverdue : daysOverdue // ignore: cast_nullable_to_non_nullable
as int,totalDue: null == totalDue ? _self.totalDue : totalDue // ignore: cast_nullable_to_non_nullable
as double,lateFee: null == lateFee ? _self.lateFee : lateFee // ignore: cast_nullable_to_non_nullable
as double,priority: null == priority ? _self.priority : priority // ignore: cast_nullable_to_non_nullable
as DuePriority,lastVisitNotes: freezed == lastVisitNotes ? _self.lastVisitNotes : lastVisitNotes // ignore: cast_nullable_to_non_nullable
as String?,lastVisitDate: freezed == lastVisitDate ? _self.lastVisitDate : lastVisitDate // ignore: cast_nullable_to_non_nullable
as DateTime?,isCollected: freezed == isCollected ? _self.isCollected : isCollected // ignore: cast_nullable_to_non_nullable
as bool?,collectedAmount: freezed == collectedAmount ? _self.collectedAmount : collectedAmount // ignore: cast_nullable_to_non_nullable
as double?,collectedAt: freezed == collectedAt ? _self.collectedAt : collectedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,location: freezed == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as GeoLocation?,landmark: freezed == landmark ? _self.landmark : landmark // ignore: cast_nullable_to_non_nullable
as String?,preferredTime: freezed == preferredTime ? _self.preferredTime : preferredTime // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$EMIReminder {

 String get id; String get customerId; String get bookingId; String get customerName; String get phone; double get emiAmount; DateTime get dueDate;// Reminder
 ReminderType get type;// SMS, WhatsApp, Call, Email
 ReminderStatus get status;// Scheduled, Sent, Delivered, Failed
 String? get messageContent; DateTime? get scheduledAt; DateTime? get sentAt; DateTime? get deliveredAt;// Response
 bool? get isResponded; DateTime? get respondedAt; String? get responseType;// WillPay, NeedTime, CannotPay, Paid
// Agent Assignment
 String? get assignedAgentId; DateTime? get agentAssignedAt;
/// Create a copy of EMIReminder
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$EMIReminderCopyWith<EMIReminder> get copyWith => _$EMIReminderCopyWithImpl<EMIReminder>(this as EMIReminder, _$identity);

  /// Serializes this EMIReminder to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is EMIReminder&&(identical(other.id, id) || other.id == id)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.emiAmount, emiAmount) || other.emiAmount == emiAmount)&&(identical(other.dueDate, dueDate) || other.dueDate == dueDate)&&(identical(other.type, type) || other.type == type)&&(identical(other.status, status) || other.status == status)&&(identical(other.messageContent, messageContent) || other.messageContent == messageContent)&&(identical(other.scheduledAt, scheduledAt) || other.scheduledAt == scheduledAt)&&(identical(other.sentAt, sentAt) || other.sentAt == sentAt)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.isResponded, isResponded) || other.isResponded == isResponded)&&(identical(other.respondedAt, respondedAt) || other.respondedAt == respondedAt)&&(identical(other.responseType, responseType) || other.responseType == responseType)&&(identical(other.assignedAgentId, assignedAgentId) || other.assignedAgentId == assignedAgentId)&&(identical(other.agentAssignedAt, agentAssignedAt) || other.agentAssignedAt == agentAssignedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,customerId,bookingId,customerName,phone,emiAmount,dueDate,type,status,messageContent,scheduledAt,sentAt,deliveredAt,isResponded,respondedAt,responseType,assignedAgentId,agentAssignedAt);

@override
String toString() {
  return 'EMIReminder(id: $id, customerId: $customerId, bookingId: $bookingId, customerName: $customerName, phone: $phone, emiAmount: $emiAmount, dueDate: $dueDate, type: $type, status: $status, messageContent: $messageContent, scheduledAt: $scheduledAt, sentAt: $sentAt, deliveredAt: $deliveredAt, isResponded: $isResponded, respondedAt: $respondedAt, responseType: $responseType, assignedAgentId: $assignedAgentId, agentAssignedAt: $agentAssignedAt)';
}


}

/// @nodoc
abstract mixin class $EMIReminderCopyWith<$Res>  {
  factory $EMIReminderCopyWith(EMIReminder value, $Res Function(EMIReminder) _then) = _$EMIReminderCopyWithImpl;
@useResult
$Res call({
 String id, String customerId, String bookingId, String customerName, String phone, double emiAmount, DateTime dueDate, ReminderType type, ReminderStatus status, String? messageContent, DateTime? scheduledAt, DateTime? sentAt, DateTime? deliveredAt, bool? isResponded, DateTime? respondedAt, String? responseType, String? assignedAgentId, DateTime? agentAssignedAt
});




}
/// @nodoc
class _$EMIReminderCopyWithImpl<$Res>
    implements $EMIReminderCopyWith<$Res> {
  _$EMIReminderCopyWithImpl(this._self, this._then);

  final EMIReminder _self;
  final $Res Function(EMIReminder) _then;

/// Create a copy of EMIReminder
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? customerId = null,Object? bookingId = null,Object? customerName = null,Object? phone = null,Object? emiAmount = null,Object? dueDate = null,Object? type = null,Object? status = null,Object? messageContent = freezed,Object? scheduledAt = freezed,Object? sentAt = freezed,Object? deliveredAt = freezed,Object? isResponded = freezed,Object? respondedAt = freezed,Object? responseType = freezed,Object? assignedAgentId = freezed,Object? agentAssignedAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,emiAmount: null == emiAmount ? _self.emiAmount : emiAmount // ignore: cast_nullable_to_non_nullable
as double,dueDate: null == dueDate ? _self.dueDate : dueDate // ignore: cast_nullable_to_non_nullable
as DateTime,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as ReminderType,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as ReminderStatus,messageContent: freezed == messageContent ? _self.messageContent : messageContent // ignore: cast_nullable_to_non_nullable
as String?,scheduledAt: freezed == scheduledAt ? _self.scheduledAt : scheduledAt // ignore: cast_nullable_to_non_nullable
as DateTime?,sentAt: freezed == sentAt ? _self.sentAt : sentAt // ignore: cast_nullable_to_non_nullable
as DateTime?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,isResponded: freezed == isResponded ? _self.isResponded : isResponded // ignore: cast_nullable_to_non_nullable
as bool?,respondedAt: freezed == respondedAt ? _self.respondedAt : respondedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,responseType: freezed == responseType ? _self.responseType : responseType // ignore: cast_nullable_to_non_nullable
as String?,assignedAgentId: freezed == assignedAgentId ? _self.assignedAgentId : assignedAgentId // ignore: cast_nullable_to_non_nullable
as String?,agentAssignedAt: freezed == agentAssignedAt ? _self.agentAssignedAt : agentAssignedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [EMIReminder].
extension EMIReminderPatterns on EMIReminder {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _EMIReminder value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _EMIReminder() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _EMIReminder value)  $default,){
final _that = this;
switch (_that) {
case _EMIReminder():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _EMIReminder value)?  $default,){
final _that = this;
switch (_that) {
case _EMIReminder() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String customerId,  String bookingId,  String customerName,  String phone,  double emiAmount,  DateTime dueDate,  ReminderType type,  ReminderStatus status,  String? messageContent,  DateTime? scheduledAt,  DateTime? sentAt,  DateTime? deliveredAt,  bool? isResponded,  DateTime? respondedAt,  String? responseType,  String? assignedAgentId,  DateTime? agentAssignedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _EMIReminder() when $default != null:
return $default(_that.id,_that.customerId,_that.bookingId,_that.customerName,_that.phone,_that.emiAmount,_that.dueDate,_that.type,_that.status,_that.messageContent,_that.scheduledAt,_that.sentAt,_that.deliveredAt,_that.isResponded,_that.respondedAt,_that.responseType,_that.assignedAgentId,_that.agentAssignedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String customerId,  String bookingId,  String customerName,  String phone,  double emiAmount,  DateTime dueDate,  ReminderType type,  ReminderStatus status,  String? messageContent,  DateTime? scheduledAt,  DateTime? sentAt,  DateTime? deliveredAt,  bool? isResponded,  DateTime? respondedAt,  String? responseType,  String? assignedAgentId,  DateTime? agentAssignedAt)  $default,) {final _that = this;
switch (_that) {
case _EMIReminder():
return $default(_that.id,_that.customerId,_that.bookingId,_that.customerName,_that.phone,_that.emiAmount,_that.dueDate,_that.type,_that.status,_that.messageContent,_that.scheduledAt,_that.sentAt,_that.deliveredAt,_that.isResponded,_that.respondedAt,_that.responseType,_that.assignedAgentId,_that.agentAssignedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String customerId,  String bookingId,  String customerName,  String phone,  double emiAmount,  DateTime dueDate,  ReminderType type,  ReminderStatus status,  String? messageContent,  DateTime? scheduledAt,  DateTime? sentAt,  DateTime? deliveredAt,  bool? isResponded,  DateTime? respondedAt,  String? responseType,  String? assignedAgentId,  DateTime? agentAssignedAt)?  $default,) {final _that = this;
switch (_that) {
case _EMIReminder() when $default != null:
return $default(_that.id,_that.customerId,_that.bookingId,_that.customerName,_that.phone,_that.emiAmount,_that.dueDate,_that.type,_that.status,_that.messageContent,_that.scheduledAt,_that.sentAt,_that.deliveredAt,_that.isResponded,_that.respondedAt,_that.responseType,_that.assignedAgentId,_that.agentAssignedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _EMIReminder implements EMIReminder {
  const _EMIReminder({required this.id, required this.customerId, required this.bookingId, required this.customerName, required this.phone, required this.emiAmount, required this.dueDate, required this.type, required this.status, this.messageContent, this.scheduledAt, this.sentAt, this.deliveredAt, this.isResponded, this.respondedAt, this.responseType, this.assignedAgentId, this.agentAssignedAt});
  factory _EMIReminder.fromJson(Map<String, dynamic> json) => _$EMIReminderFromJson(json);

@override final  String id;
@override final  String customerId;
@override final  String bookingId;
@override final  String customerName;
@override final  String phone;
@override final  double emiAmount;
@override final  DateTime dueDate;
// Reminder
@override final  ReminderType type;
// SMS, WhatsApp, Call, Email
@override final  ReminderStatus status;
// Scheduled, Sent, Delivered, Failed
@override final  String? messageContent;
@override final  DateTime? scheduledAt;
@override final  DateTime? sentAt;
@override final  DateTime? deliveredAt;
// Response
@override final  bool? isResponded;
@override final  DateTime? respondedAt;
@override final  String? responseType;
// WillPay, NeedTime, CannotPay, Paid
// Agent Assignment
@override final  String? assignedAgentId;
@override final  DateTime? agentAssignedAt;

/// Create a copy of EMIReminder
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$EMIReminderCopyWith<_EMIReminder> get copyWith => __$EMIReminderCopyWithImpl<_EMIReminder>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$EMIReminderToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _EMIReminder&&(identical(other.id, id) || other.id == id)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.emiAmount, emiAmount) || other.emiAmount == emiAmount)&&(identical(other.dueDate, dueDate) || other.dueDate == dueDate)&&(identical(other.type, type) || other.type == type)&&(identical(other.status, status) || other.status == status)&&(identical(other.messageContent, messageContent) || other.messageContent == messageContent)&&(identical(other.scheduledAt, scheduledAt) || other.scheduledAt == scheduledAt)&&(identical(other.sentAt, sentAt) || other.sentAt == sentAt)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.isResponded, isResponded) || other.isResponded == isResponded)&&(identical(other.respondedAt, respondedAt) || other.respondedAt == respondedAt)&&(identical(other.responseType, responseType) || other.responseType == responseType)&&(identical(other.assignedAgentId, assignedAgentId) || other.assignedAgentId == assignedAgentId)&&(identical(other.agentAssignedAt, agentAssignedAt) || other.agentAssignedAt == agentAssignedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,customerId,bookingId,customerName,phone,emiAmount,dueDate,type,status,messageContent,scheduledAt,sentAt,deliveredAt,isResponded,respondedAt,responseType,assignedAgentId,agentAssignedAt);

@override
String toString() {
  return 'EMIReminder(id: $id, customerId: $customerId, bookingId: $bookingId, customerName: $customerName, phone: $phone, emiAmount: $emiAmount, dueDate: $dueDate, type: $type, status: $status, messageContent: $messageContent, scheduledAt: $scheduledAt, sentAt: $sentAt, deliveredAt: $deliveredAt, isResponded: $isResponded, respondedAt: $respondedAt, responseType: $responseType, assignedAgentId: $assignedAgentId, agentAssignedAt: $agentAssignedAt)';
}


}

/// @nodoc
abstract mixin class _$EMIReminderCopyWith<$Res> implements $EMIReminderCopyWith<$Res> {
  factory _$EMIReminderCopyWith(_EMIReminder value, $Res Function(_EMIReminder) _then) = __$EMIReminderCopyWithImpl;
@override @useResult
$Res call({
 String id, String customerId, String bookingId, String customerName, String phone, double emiAmount, DateTime dueDate, ReminderType type, ReminderStatus status, String? messageContent, DateTime? scheduledAt, DateTime? sentAt, DateTime? deliveredAt, bool? isResponded, DateTime? respondedAt, String? responseType, String? assignedAgentId, DateTime? agentAssignedAt
});




}
/// @nodoc
class __$EMIReminderCopyWithImpl<$Res>
    implements _$EMIReminderCopyWith<$Res> {
  __$EMIReminderCopyWithImpl(this._self, this._then);

  final _EMIReminder _self;
  final $Res Function(_EMIReminder) _then;

/// Create a copy of EMIReminder
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? customerId = null,Object? bookingId = null,Object? customerName = null,Object? phone = null,Object? emiAmount = null,Object? dueDate = null,Object? type = null,Object? status = null,Object? messageContent = freezed,Object? scheduledAt = freezed,Object? sentAt = freezed,Object? deliveredAt = freezed,Object? isResponded = freezed,Object? respondedAt = freezed,Object? responseType = freezed,Object? assignedAgentId = freezed,Object? agentAssignedAt = freezed,}) {
  return _then(_EMIReminder(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,emiAmount: null == emiAmount ? _self.emiAmount : emiAmount // ignore: cast_nullable_to_non_nullable
as double,dueDate: null == dueDate ? _self.dueDate : dueDate // ignore: cast_nullable_to_non_nullable
as DateTime,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as ReminderType,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as ReminderStatus,messageContent: freezed == messageContent ? _self.messageContent : messageContent // ignore: cast_nullable_to_non_nullable
as String?,scheduledAt: freezed == scheduledAt ? _self.scheduledAt : scheduledAt // ignore: cast_nullable_to_non_nullable
as DateTime?,sentAt: freezed == sentAt ? _self.sentAt : sentAt // ignore: cast_nullable_to_non_nullable
as DateTime?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,isResponded: freezed == isResponded ? _self.isResponded : isResponded // ignore: cast_nullable_to_non_nullable
as bool?,respondedAt: freezed == respondedAt ? _self.respondedAt : respondedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,responseType: freezed == responseType ? _self.responseType : responseType // ignore: cast_nullable_to_non_nullable
as String?,assignedAgentId: freezed == assignedAgentId ? _self.assignedAgentId : assignedAgentId // ignore: cast_nullable_to_non_nullable
as String?,agentAssignedAt: freezed == agentAssignedAt ? _self.agentAssignedAt : agentAssignedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}

// dart format on
