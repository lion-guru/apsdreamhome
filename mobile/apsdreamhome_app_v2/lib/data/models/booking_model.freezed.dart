// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'booking_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$BookingModel {

 String get id; String get plotId; String get plotNumber; String get colonyId; String get colonyName;// Customer Info
 String get customerId; String get customerName; String get customerPhone; String? get customerEmail; String? get customerAddress;// Associate Info (if booked through associate)
 String? get associateId; String? get associateName; String? get associateRank; double? get associateCommission;// Pricing
 double get plotPrice; double get tokenAmount; double get totalAmount; String get paymentPlan;// full, emi, installment
// EMI Details (if applicable)
 double? get downPayment; int? get emiMonths; double? get emiAmount; double? get interestRate;// Status
 String get status;// pending, approved, rejected, completed, cancelled
 String? get statusReason; DateTime? get approvedAt; String? get approvedBy; DateTime? get completedAt; DateTime? get cancelledAt; String? get cancelledReason;// Documents
 List<BookingDocument>? get documents;// Payments
 List<PaymentModel>? get payments; double? get totalPaid; double? get remainingAmount;// Registry Info
 DateTime? get registryDate; String? get registryNumber; String? get registryOffice;// Agreement
 DateTime? get agreementDate; String? get agreementNumber; String? get agreementDocumentUrl;// Timestamps
 DateTime? get createdAt; DateTime? get updatedAt;// Notes
 String? get notes; List<BookingHistory>? get history;
/// Create a copy of BookingModel
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BookingModelCopyWith<BookingModel> get copyWith => _$BookingModelCopyWithImpl<BookingModel>(this as BookingModel, _$identity);

  /// Serializes this BookingModel to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BookingModel&&(identical(other.id, id) || other.id == id)&&(identical(other.plotId, plotId) || other.plotId == plotId)&&(identical(other.plotNumber, plotNumber) || other.plotNumber == plotNumber)&&(identical(other.colonyId, colonyId) || other.colonyId == colonyId)&&(identical(other.colonyName, colonyName) || other.colonyName == colonyName)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.customerPhone, customerPhone) || other.customerPhone == customerPhone)&&(identical(other.customerEmail, customerEmail) || other.customerEmail == customerEmail)&&(identical(other.customerAddress, customerAddress) || other.customerAddress == customerAddress)&&(identical(other.associateId, associateId) || other.associateId == associateId)&&(identical(other.associateName, associateName) || other.associateName == associateName)&&(identical(other.associateRank, associateRank) || other.associateRank == associateRank)&&(identical(other.associateCommission, associateCommission) || other.associateCommission == associateCommission)&&(identical(other.plotPrice, plotPrice) || other.plotPrice == plotPrice)&&(identical(other.tokenAmount, tokenAmount) || other.tokenAmount == tokenAmount)&&(identical(other.totalAmount, totalAmount) || other.totalAmount == totalAmount)&&(identical(other.paymentPlan, paymentPlan) || other.paymentPlan == paymentPlan)&&(identical(other.downPayment, downPayment) || other.downPayment == downPayment)&&(identical(other.emiMonths, emiMonths) || other.emiMonths == emiMonths)&&(identical(other.emiAmount, emiAmount) || other.emiAmount == emiAmount)&&(identical(other.interestRate, interestRate) || other.interestRate == interestRate)&&(identical(other.status, status) || other.status == status)&&(identical(other.statusReason, statusReason) || other.statusReason == statusReason)&&(identical(other.approvedAt, approvedAt) || other.approvedAt == approvedAt)&&(identical(other.approvedBy, approvedBy) || other.approvedBy == approvedBy)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt)&&(identical(other.cancelledAt, cancelledAt) || other.cancelledAt == cancelledAt)&&(identical(other.cancelledReason, cancelledReason) || other.cancelledReason == cancelledReason)&&const DeepCollectionEquality().equals(other.documents, documents)&&const DeepCollectionEquality().equals(other.payments, payments)&&(identical(other.totalPaid, totalPaid) || other.totalPaid == totalPaid)&&(identical(other.remainingAmount, remainingAmount) || other.remainingAmount == remainingAmount)&&(identical(other.registryDate, registryDate) || other.registryDate == registryDate)&&(identical(other.registryNumber, registryNumber) || other.registryNumber == registryNumber)&&(identical(other.registryOffice, registryOffice) || other.registryOffice == registryOffice)&&(identical(other.agreementDate, agreementDate) || other.agreementDate == agreementDate)&&(identical(other.agreementNumber, agreementNumber) || other.agreementNumber == agreementNumber)&&(identical(other.agreementDocumentUrl, agreementDocumentUrl) || other.agreementDocumentUrl == agreementDocumentUrl)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt)&&(identical(other.notes, notes) || other.notes == notes)&&const DeepCollectionEquality().equals(other.history, history));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,plotId,plotNumber,colonyId,colonyName,customerId,customerName,customerPhone,customerEmail,customerAddress,associateId,associateName,associateRank,associateCommission,plotPrice,tokenAmount,totalAmount,paymentPlan,downPayment,emiMonths,emiAmount,interestRate,status,statusReason,approvedAt,approvedBy,completedAt,cancelledAt,cancelledReason,const DeepCollectionEquality().hash(documents),const DeepCollectionEquality().hash(payments),totalPaid,remainingAmount,registryDate,registryNumber,registryOffice,agreementDate,agreementNumber,agreementDocumentUrl,createdAt,updatedAt,notes,const DeepCollectionEquality().hash(history)]);

@override
String toString() {
  return 'BookingModel(id: $id, plotId: $plotId, plotNumber: $plotNumber, colonyId: $colonyId, colonyName: $colonyName, customerId: $customerId, customerName: $customerName, customerPhone: $customerPhone, customerEmail: $customerEmail, customerAddress: $customerAddress, associateId: $associateId, associateName: $associateName, associateRank: $associateRank, associateCommission: $associateCommission, plotPrice: $plotPrice, tokenAmount: $tokenAmount, totalAmount: $totalAmount, paymentPlan: $paymentPlan, downPayment: $downPayment, emiMonths: $emiMonths, emiAmount: $emiAmount, interestRate: $interestRate, status: $status, statusReason: $statusReason, approvedAt: $approvedAt, approvedBy: $approvedBy, completedAt: $completedAt, cancelledAt: $cancelledAt, cancelledReason: $cancelledReason, documents: $documents, payments: $payments, totalPaid: $totalPaid, remainingAmount: $remainingAmount, registryDate: $registryDate, registryNumber: $registryNumber, registryOffice: $registryOffice, agreementDate: $agreementDate, agreementNumber: $agreementNumber, agreementDocumentUrl: $agreementDocumentUrl, createdAt: $createdAt, updatedAt: $updatedAt, notes: $notes, history: $history)';
}


}

/// @nodoc
abstract mixin class $BookingModelCopyWith<$Res>  {
  factory $BookingModelCopyWith(BookingModel value, $Res Function(BookingModel) _then) = _$BookingModelCopyWithImpl;
@useResult
$Res call({
 String id, String plotId, String plotNumber, String colonyId, String colonyName, String customerId, String customerName, String customerPhone, String? customerEmail, String? customerAddress, String? associateId, String? associateName, String? associateRank, double? associateCommission, double plotPrice, double tokenAmount, double totalAmount, String paymentPlan, double? downPayment, int? emiMonths, double? emiAmount, double? interestRate, String status, String? statusReason, DateTime? approvedAt, String? approvedBy, DateTime? completedAt, DateTime? cancelledAt, String? cancelledReason, List<BookingDocument>? documents, List<PaymentModel>? payments, double? totalPaid, double? remainingAmount, DateTime? registryDate, String? registryNumber, String? registryOffice, DateTime? agreementDate, String? agreementNumber, String? agreementDocumentUrl, DateTime? createdAt, DateTime? updatedAt, String? notes, List<BookingHistory>? history
});




}
/// @nodoc
class _$BookingModelCopyWithImpl<$Res>
    implements $BookingModelCopyWith<$Res> {
  _$BookingModelCopyWithImpl(this._self, this._then);

  final BookingModel _self;
  final $Res Function(BookingModel) _then;

/// Create a copy of BookingModel
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? plotId = null,Object? plotNumber = null,Object? colonyId = null,Object? colonyName = null,Object? customerId = null,Object? customerName = null,Object? customerPhone = null,Object? customerEmail = freezed,Object? customerAddress = freezed,Object? associateId = freezed,Object? associateName = freezed,Object? associateRank = freezed,Object? associateCommission = freezed,Object? plotPrice = null,Object? tokenAmount = null,Object? totalAmount = null,Object? paymentPlan = null,Object? downPayment = freezed,Object? emiMonths = freezed,Object? emiAmount = freezed,Object? interestRate = freezed,Object? status = null,Object? statusReason = freezed,Object? approvedAt = freezed,Object? approvedBy = freezed,Object? completedAt = freezed,Object? cancelledAt = freezed,Object? cancelledReason = freezed,Object? documents = freezed,Object? payments = freezed,Object? totalPaid = freezed,Object? remainingAmount = freezed,Object? registryDate = freezed,Object? registryNumber = freezed,Object? registryOffice = freezed,Object? agreementDate = freezed,Object? agreementNumber = freezed,Object? agreementDocumentUrl = freezed,Object? createdAt = freezed,Object? updatedAt = freezed,Object? notes = freezed,Object? history = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,plotId: null == plotId ? _self.plotId : plotId // ignore: cast_nullable_to_non_nullable
as String,plotNumber: null == plotNumber ? _self.plotNumber : plotNumber // ignore: cast_nullable_to_non_nullable
as String,colonyId: null == colonyId ? _self.colonyId : colonyId // ignore: cast_nullable_to_non_nullable
as String,colonyName: null == colonyName ? _self.colonyName : colonyName // ignore: cast_nullable_to_non_nullable
as String,customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,customerPhone: null == customerPhone ? _self.customerPhone : customerPhone // ignore: cast_nullable_to_non_nullable
as String,customerEmail: freezed == customerEmail ? _self.customerEmail : customerEmail // ignore: cast_nullable_to_non_nullable
as String?,customerAddress: freezed == customerAddress ? _self.customerAddress : customerAddress // ignore: cast_nullable_to_non_nullable
as String?,associateId: freezed == associateId ? _self.associateId : associateId // ignore: cast_nullable_to_non_nullable
as String?,associateName: freezed == associateName ? _self.associateName : associateName // ignore: cast_nullable_to_non_nullable
as String?,associateRank: freezed == associateRank ? _self.associateRank : associateRank // ignore: cast_nullable_to_non_nullable
as String?,associateCommission: freezed == associateCommission ? _self.associateCommission : associateCommission // ignore: cast_nullable_to_non_nullable
as double?,plotPrice: null == plotPrice ? _self.plotPrice : plotPrice // ignore: cast_nullable_to_non_nullable
as double,tokenAmount: null == tokenAmount ? _self.tokenAmount : tokenAmount // ignore: cast_nullable_to_non_nullable
as double,totalAmount: null == totalAmount ? _self.totalAmount : totalAmount // ignore: cast_nullable_to_non_nullable
as double,paymentPlan: null == paymentPlan ? _self.paymentPlan : paymentPlan // ignore: cast_nullable_to_non_nullable
as String,downPayment: freezed == downPayment ? _self.downPayment : downPayment // ignore: cast_nullable_to_non_nullable
as double?,emiMonths: freezed == emiMonths ? _self.emiMonths : emiMonths // ignore: cast_nullable_to_non_nullable
as int?,emiAmount: freezed == emiAmount ? _self.emiAmount : emiAmount // ignore: cast_nullable_to_non_nullable
as double?,interestRate: freezed == interestRate ? _self.interestRate : interestRate // ignore: cast_nullable_to_non_nullable
as double?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,statusReason: freezed == statusReason ? _self.statusReason : statusReason // ignore: cast_nullable_to_non_nullable
as String?,approvedAt: freezed == approvedAt ? _self.approvedAt : approvedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,approvedBy: freezed == approvedBy ? _self.approvedBy : approvedBy // ignore: cast_nullable_to_non_nullable
as String?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,cancelledAt: freezed == cancelledAt ? _self.cancelledAt : cancelledAt // ignore: cast_nullable_to_non_nullable
as DateTime?,cancelledReason: freezed == cancelledReason ? _self.cancelledReason : cancelledReason // ignore: cast_nullable_to_non_nullable
as String?,documents: freezed == documents ? _self.documents : documents // ignore: cast_nullable_to_non_nullable
as List<BookingDocument>?,payments: freezed == payments ? _self.payments : payments // ignore: cast_nullable_to_non_nullable
as List<PaymentModel>?,totalPaid: freezed == totalPaid ? _self.totalPaid : totalPaid // ignore: cast_nullable_to_non_nullable
as double?,remainingAmount: freezed == remainingAmount ? _self.remainingAmount : remainingAmount // ignore: cast_nullable_to_non_nullable
as double?,registryDate: freezed == registryDate ? _self.registryDate : registryDate // ignore: cast_nullable_to_non_nullable
as DateTime?,registryNumber: freezed == registryNumber ? _self.registryNumber : registryNumber // ignore: cast_nullable_to_non_nullable
as String?,registryOffice: freezed == registryOffice ? _self.registryOffice : registryOffice // ignore: cast_nullable_to_non_nullable
as String?,agreementDate: freezed == agreementDate ? _self.agreementDate : agreementDate // ignore: cast_nullable_to_non_nullable
as DateTime?,agreementNumber: freezed == agreementNumber ? _self.agreementNumber : agreementNumber // ignore: cast_nullable_to_non_nullable
as String?,agreementDocumentUrl: freezed == agreementDocumentUrl ? _self.agreementDocumentUrl : agreementDocumentUrl // ignore: cast_nullable_to_non_nullable
as String?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,updatedAt: freezed == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,history: freezed == history ? _self.history : history // ignore: cast_nullable_to_non_nullable
as List<BookingHistory>?,
  ));
}

}


/// Adds pattern-matching-related methods to [BookingModel].
extension BookingModelPatterns on BookingModel {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BookingModel value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BookingModel() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BookingModel value)  $default,){
final _that = this;
switch (_that) {
case _BookingModel():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BookingModel value)?  $default,){
final _that = this;
switch (_that) {
case _BookingModel() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String plotId,  String plotNumber,  String colonyId,  String colonyName,  String customerId,  String customerName,  String customerPhone,  String? customerEmail,  String? customerAddress,  String? associateId,  String? associateName,  String? associateRank,  double? associateCommission,  double plotPrice,  double tokenAmount,  double totalAmount,  String paymentPlan,  double? downPayment,  int? emiMonths,  double? emiAmount,  double? interestRate,  String status,  String? statusReason,  DateTime? approvedAt,  String? approvedBy,  DateTime? completedAt,  DateTime? cancelledAt,  String? cancelledReason,  List<BookingDocument>? documents,  List<PaymentModel>? payments,  double? totalPaid,  double? remainingAmount,  DateTime? registryDate,  String? registryNumber,  String? registryOffice,  DateTime? agreementDate,  String? agreementNumber,  String? agreementDocumentUrl,  DateTime? createdAt,  DateTime? updatedAt,  String? notes,  List<BookingHistory>? history)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BookingModel() when $default != null:
return $default(_that.id,_that.plotId,_that.plotNumber,_that.colonyId,_that.colonyName,_that.customerId,_that.customerName,_that.customerPhone,_that.customerEmail,_that.customerAddress,_that.associateId,_that.associateName,_that.associateRank,_that.associateCommission,_that.plotPrice,_that.tokenAmount,_that.totalAmount,_that.paymentPlan,_that.downPayment,_that.emiMonths,_that.emiAmount,_that.interestRate,_that.status,_that.statusReason,_that.approvedAt,_that.approvedBy,_that.completedAt,_that.cancelledAt,_that.cancelledReason,_that.documents,_that.payments,_that.totalPaid,_that.remainingAmount,_that.registryDate,_that.registryNumber,_that.registryOffice,_that.agreementDate,_that.agreementNumber,_that.agreementDocumentUrl,_that.createdAt,_that.updatedAt,_that.notes,_that.history);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String plotId,  String plotNumber,  String colonyId,  String colonyName,  String customerId,  String customerName,  String customerPhone,  String? customerEmail,  String? customerAddress,  String? associateId,  String? associateName,  String? associateRank,  double? associateCommission,  double plotPrice,  double tokenAmount,  double totalAmount,  String paymentPlan,  double? downPayment,  int? emiMonths,  double? emiAmount,  double? interestRate,  String status,  String? statusReason,  DateTime? approvedAt,  String? approvedBy,  DateTime? completedAt,  DateTime? cancelledAt,  String? cancelledReason,  List<BookingDocument>? documents,  List<PaymentModel>? payments,  double? totalPaid,  double? remainingAmount,  DateTime? registryDate,  String? registryNumber,  String? registryOffice,  DateTime? agreementDate,  String? agreementNumber,  String? agreementDocumentUrl,  DateTime? createdAt,  DateTime? updatedAt,  String? notes,  List<BookingHistory>? history)  $default,) {final _that = this;
switch (_that) {
case _BookingModel():
return $default(_that.id,_that.plotId,_that.plotNumber,_that.colonyId,_that.colonyName,_that.customerId,_that.customerName,_that.customerPhone,_that.customerEmail,_that.customerAddress,_that.associateId,_that.associateName,_that.associateRank,_that.associateCommission,_that.plotPrice,_that.tokenAmount,_that.totalAmount,_that.paymentPlan,_that.downPayment,_that.emiMonths,_that.emiAmount,_that.interestRate,_that.status,_that.statusReason,_that.approvedAt,_that.approvedBy,_that.completedAt,_that.cancelledAt,_that.cancelledReason,_that.documents,_that.payments,_that.totalPaid,_that.remainingAmount,_that.registryDate,_that.registryNumber,_that.registryOffice,_that.agreementDate,_that.agreementNumber,_that.agreementDocumentUrl,_that.createdAt,_that.updatedAt,_that.notes,_that.history);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String plotId,  String plotNumber,  String colonyId,  String colonyName,  String customerId,  String customerName,  String customerPhone,  String? customerEmail,  String? customerAddress,  String? associateId,  String? associateName,  String? associateRank,  double? associateCommission,  double plotPrice,  double tokenAmount,  double totalAmount,  String paymentPlan,  double? downPayment,  int? emiMonths,  double? emiAmount,  double? interestRate,  String status,  String? statusReason,  DateTime? approvedAt,  String? approvedBy,  DateTime? completedAt,  DateTime? cancelledAt,  String? cancelledReason,  List<BookingDocument>? documents,  List<PaymentModel>? payments,  double? totalPaid,  double? remainingAmount,  DateTime? registryDate,  String? registryNumber,  String? registryOffice,  DateTime? agreementDate,  String? agreementNumber,  String? agreementDocumentUrl,  DateTime? createdAt,  DateTime? updatedAt,  String? notes,  List<BookingHistory>? history)?  $default,) {final _that = this;
switch (_that) {
case _BookingModel() when $default != null:
return $default(_that.id,_that.plotId,_that.plotNumber,_that.colonyId,_that.colonyName,_that.customerId,_that.customerName,_that.customerPhone,_that.customerEmail,_that.customerAddress,_that.associateId,_that.associateName,_that.associateRank,_that.associateCommission,_that.plotPrice,_that.tokenAmount,_that.totalAmount,_that.paymentPlan,_that.downPayment,_that.emiMonths,_that.emiAmount,_that.interestRate,_that.status,_that.statusReason,_that.approvedAt,_that.approvedBy,_that.completedAt,_that.cancelledAt,_that.cancelledReason,_that.documents,_that.payments,_that.totalPaid,_that.remainingAmount,_that.registryDate,_that.registryNumber,_that.registryOffice,_that.agreementDate,_that.agreementNumber,_that.agreementDocumentUrl,_that.createdAt,_that.updatedAt,_that.notes,_that.history);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _BookingModel extends BookingModel {
  const _BookingModel({this.id = '', this.plotId = '', this.plotNumber = '', this.colonyId = '', this.colonyName = '', this.customerId = '', this.customerName = '', this.customerPhone = '', this.customerEmail, this.customerAddress, this.associateId, this.associateName, this.associateRank, this.associateCommission, this.plotPrice = 0.0, this.tokenAmount = 0.0, this.totalAmount = 0.0, this.paymentPlan = '', this.downPayment, this.emiMonths, this.emiAmount, this.interestRate, this.status = 'pending', this.statusReason, this.approvedAt, this.approvedBy, this.completedAt, this.cancelledAt, this.cancelledReason, final  List<BookingDocument>? documents, final  List<PaymentModel>? payments, this.totalPaid, this.remainingAmount, this.registryDate, this.registryNumber, this.registryOffice, this.agreementDate, this.agreementNumber, this.agreementDocumentUrl, this.createdAt, this.updatedAt, this.notes, final  List<BookingHistory>? history}): _documents = documents,_payments = payments,_history = history,super._();
  factory _BookingModel.fromJson(Map<String, dynamic> json) => _$BookingModelFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String plotId;
@override@JsonKey() final  String plotNumber;
@override@JsonKey() final  String colonyId;
@override@JsonKey() final  String colonyName;
// Customer Info
@override@JsonKey() final  String customerId;
@override@JsonKey() final  String customerName;
@override@JsonKey() final  String customerPhone;
@override final  String? customerEmail;
@override final  String? customerAddress;
// Associate Info (if booked through associate)
@override final  String? associateId;
@override final  String? associateName;
@override final  String? associateRank;
@override final  double? associateCommission;
// Pricing
@override@JsonKey() final  double plotPrice;
@override@JsonKey() final  double tokenAmount;
@override@JsonKey() final  double totalAmount;
@override@JsonKey() final  String paymentPlan;
// full, emi, installment
// EMI Details (if applicable)
@override final  double? downPayment;
@override final  int? emiMonths;
@override final  double? emiAmount;
@override final  double? interestRate;
// Status
@override@JsonKey() final  String status;
// pending, approved, rejected, completed, cancelled
@override final  String? statusReason;
@override final  DateTime? approvedAt;
@override final  String? approvedBy;
@override final  DateTime? completedAt;
@override final  DateTime? cancelledAt;
@override final  String? cancelledReason;
// Documents
 final  List<BookingDocument>? _documents;
// Documents
@override List<BookingDocument>? get documents {
  final value = _documents;
  if (value == null) return null;
  if (_documents is EqualUnmodifiableListView) return _documents;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

// Payments
 final  List<PaymentModel>? _payments;
// Payments
@override List<PaymentModel>? get payments {
  final value = _payments;
  if (value == null) return null;
  if (_payments is EqualUnmodifiableListView) return _payments;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

@override final  double? totalPaid;
@override final  double? remainingAmount;
// Registry Info
@override final  DateTime? registryDate;
@override final  String? registryNumber;
@override final  String? registryOffice;
// Agreement
@override final  DateTime? agreementDate;
@override final  String? agreementNumber;
@override final  String? agreementDocumentUrl;
// Timestamps
@override final  DateTime? createdAt;
@override final  DateTime? updatedAt;
// Notes
@override final  String? notes;
 final  List<BookingHistory>? _history;
@override List<BookingHistory>? get history {
  final value = _history;
  if (value == null) return null;
  if (_history is EqualUnmodifiableListView) return _history;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}


/// Create a copy of BookingModel
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BookingModelCopyWith<_BookingModel> get copyWith => __$BookingModelCopyWithImpl<_BookingModel>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$BookingModelToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _BookingModel&&(identical(other.id, id) || other.id == id)&&(identical(other.plotId, plotId) || other.plotId == plotId)&&(identical(other.plotNumber, plotNumber) || other.plotNumber == plotNumber)&&(identical(other.colonyId, colonyId) || other.colonyId == colonyId)&&(identical(other.colonyName, colonyName) || other.colonyName == colonyName)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.customerName, customerName) || other.customerName == customerName)&&(identical(other.customerPhone, customerPhone) || other.customerPhone == customerPhone)&&(identical(other.customerEmail, customerEmail) || other.customerEmail == customerEmail)&&(identical(other.customerAddress, customerAddress) || other.customerAddress == customerAddress)&&(identical(other.associateId, associateId) || other.associateId == associateId)&&(identical(other.associateName, associateName) || other.associateName == associateName)&&(identical(other.associateRank, associateRank) || other.associateRank == associateRank)&&(identical(other.associateCommission, associateCommission) || other.associateCommission == associateCommission)&&(identical(other.plotPrice, plotPrice) || other.plotPrice == plotPrice)&&(identical(other.tokenAmount, tokenAmount) || other.tokenAmount == tokenAmount)&&(identical(other.totalAmount, totalAmount) || other.totalAmount == totalAmount)&&(identical(other.paymentPlan, paymentPlan) || other.paymentPlan == paymentPlan)&&(identical(other.downPayment, downPayment) || other.downPayment == downPayment)&&(identical(other.emiMonths, emiMonths) || other.emiMonths == emiMonths)&&(identical(other.emiAmount, emiAmount) || other.emiAmount == emiAmount)&&(identical(other.interestRate, interestRate) || other.interestRate == interestRate)&&(identical(other.status, status) || other.status == status)&&(identical(other.statusReason, statusReason) || other.statusReason == statusReason)&&(identical(other.approvedAt, approvedAt) || other.approvedAt == approvedAt)&&(identical(other.approvedBy, approvedBy) || other.approvedBy == approvedBy)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt)&&(identical(other.cancelledAt, cancelledAt) || other.cancelledAt == cancelledAt)&&(identical(other.cancelledReason, cancelledReason) || other.cancelledReason == cancelledReason)&&const DeepCollectionEquality().equals(other._documents, _documents)&&const DeepCollectionEquality().equals(other._payments, _payments)&&(identical(other.totalPaid, totalPaid) || other.totalPaid == totalPaid)&&(identical(other.remainingAmount, remainingAmount) || other.remainingAmount == remainingAmount)&&(identical(other.registryDate, registryDate) || other.registryDate == registryDate)&&(identical(other.registryNumber, registryNumber) || other.registryNumber == registryNumber)&&(identical(other.registryOffice, registryOffice) || other.registryOffice == registryOffice)&&(identical(other.agreementDate, agreementDate) || other.agreementDate == agreementDate)&&(identical(other.agreementNumber, agreementNumber) || other.agreementNumber == agreementNumber)&&(identical(other.agreementDocumentUrl, agreementDocumentUrl) || other.agreementDocumentUrl == agreementDocumentUrl)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt)&&(identical(other.notes, notes) || other.notes == notes)&&const DeepCollectionEquality().equals(other._history, _history));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,plotId,plotNumber,colonyId,colonyName,customerId,customerName,customerPhone,customerEmail,customerAddress,associateId,associateName,associateRank,associateCommission,plotPrice,tokenAmount,totalAmount,paymentPlan,downPayment,emiMonths,emiAmount,interestRate,status,statusReason,approvedAt,approvedBy,completedAt,cancelledAt,cancelledReason,const DeepCollectionEquality().hash(_documents),const DeepCollectionEquality().hash(_payments),totalPaid,remainingAmount,registryDate,registryNumber,registryOffice,agreementDate,agreementNumber,agreementDocumentUrl,createdAt,updatedAt,notes,const DeepCollectionEquality().hash(_history)]);

@override
String toString() {
  return 'BookingModel(id: $id, plotId: $plotId, plotNumber: $plotNumber, colonyId: $colonyId, colonyName: $colonyName, customerId: $customerId, customerName: $customerName, customerPhone: $customerPhone, customerEmail: $customerEmail, customerAddress: $customerAddress, associateId: $associateId, associateName: $associateName, associateRank: $associateRank, associateCommission: $associateCommission, plotPrice: $plotPrice, tokenAmount: $tokenAmount, totalAmount: $totalAmount, paymentPlan: $paymentPlan, downPayment: $downPayment, emiMonths: $emiMonths, emiAmount: $emiAmount, interestRate: $interestRate, status: $status, statusReason: $statusReason, approvedAt: $approvedAt, approvedBy: $approvedBy, completedAt: $completedAt, cancelledAt: $cancelledAt, cancelledReason: $cancelledReason, documents: $documents, payments: $payments, totalPaid: $totalPaid, remainingAmount: $remainingAmount, registryDate: $registryDate, registryNumber: $registryNumber, registryOffice: $registryOffice, agreementDate: $agreementDate, agreementNumber: $agreementNumber, agreementDocumentUrl: $agreementDocumentUrl, createdAt: $createdAt, updatedAt: $updatedAt, notes: $notes, history: $history)';
}


}

/// @nodoc
abstract mixin class _$BookingModelCopyWith<$Res> implements $BookingModelCopyWith<$Res> {
  factory _$BookingModelCopyWith(_BookingModel value, $Res Function(_BookingModel) _then) = __$BookingModelCopyWithImpl;
@override @useResult
$Res call({
 String id, String plotId, String plotNumber, String colonyId, String colonyName, String customerId, String customerName, String customerPhone, String? customerEmail, String? customerAddress, String? associateId, String? associateName, String? associateRank, double? associateCommission, double plotPrice, double tokenAmount, double totalAmount, String paymentPlan, double? downPayment, int? emiMonths, double? emiAmount, double? interestRate, String status, String? statusReason, DateTime? approvedAt, String? approvedBy, DateTime? completedAt, DateTime? cancelledAt, String? cancelledReason, List<BookingDocument>? documents, List<PaymentModel>? payments, double? totalPaid, double? remainingAmount, DateTime? registryDate, String? registryNumber, String? registryOffice, DateTime? agreementDate, String? agreementNumber, String? agreementDocumentUrl, DateTime? createdAt, DateTime? updatedAt, String? notes, List<BookingHistory>? history
});




}
/// @nodoc
class __$BookingModelCopyWithImpl<$Res>
    implements _$BookingModelCopyWith<$Res> {
  __$BookingModelCopyWithImpl(this._self, this._then);

  final _BookingModel _self;
  final $Res Function(_BookingModel) _then;

/// Create a copy of BookingModel
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? plotId = null,Object? plotNumber = null,Object? colonyId = null,Object? colonyName = null,Object? customerId = null,Object? customerName = null,Object? customerPhone = null,Object? customerEmail = freezed,Object? customerAddress = freezed,Object? associateId = freezed,Object? associateName = freezed,Object? associateRank = freezed,Object? associateCommission = freezed,Object? plotPrice = null,Object? tokenAmount = null,Object? totalAmount = null,Object? paymentPlan = null,Object? downPayment = freezed,Object? emiMonths = freezed,Object? emiAmount = freezed,Object? interestRate = freezed,Object? status = null,Object? statusReason = freezed,Object? approvedAt = freezed,Object? approvedBy = freezed,Object? completedAt = freezed,Object? cancelledAt = freezed,Object? cancelledReason = freezed,Object? documents = freezed,Object? payments = freezed,Object? totalPaid = freezed,Object? remainingAmount = freezed,Object? registryDate = freezed,Object? registryNumber = freezed,Object? registryOffice = freezed,Object? agreementDate = freezed,Object? agreementNumber = freezed,Object? agreementDocumentUrl = freezed,Object? createdAt = freezed,Object? updatedAt = freezed,Object? notes = freezed,Object? history = freezed,}) {
  return _then(_BookingModel(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,plotId: null == plotId ? _self.plotId : plotId // ignore: cast_nullable_to_non_nullable
as String,plotNumber: null == plotNumber ? _self.plotNumber : plotNumber // ignore: cast_nullable_to_non_nullable
as String,colonyId: null == colonyId ? _self.colonyId : colonyId // ignore: cast_nullable_to_non_nullable
as String,colonyName: null == colonyName ? _self.colonyName : colonyName // ignore: cast_nullable_to_non_nullable
as String,customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,customerName: null == customerName ? _self.customerName : customerName // ignore: cast_nullable_to_non_nullable
as String,customerPhone: null == customerPhone ? _self.customerPhone : customerPhone // ignore: cast_nullable_to_non_nullable
as String,customerEmail: freezed == customerEmail ? _self.customerEmail : customerEmail // ignore: cast_nullable_to_non_nullable
as String?,customerAddress: freezed == customerAddress ? _self.customerAddress : customerAddress // ignore: cast_nullable_to_non_nullable
as String?,associateId: freezed == associateId ? _self.associateId : associateId // ignore: cast_nullable_to_non_nullable
as String?,associateName: freezed == associateName ? _self.associateName : associateName // ignore: cast_nullable_to_non_nullable
as String?,associateRank: freezed == associateRank ? _self.associateRank : associateRank // ignore: cast_nullable_to_non_nullable
as String?,associateCommission: freezed == associateCommission ? _self.associateCommission : associateCommission // ignore: cast_nullable_to_non_nullable
as double?,plotPrice: null == plotPrice ? _self.plotPrice : plotPrice // ignore: cast_nullable_to_non_nullable
as double,tokenAmount: null == tokenAmount ? _self.tokenAmount : tokenAmount // ignore: cast_nullable_to_non_nullable
as double,totalAmount: null == totalAmount ? _self.totalAmount : totalAmount // ignore: cast_nullable_to_non_nullable
as double,paymentPlan: null == paymentPlan ? _self.paymentPlan : paymentPlan // ignore: cast_nullable_to_non_nullable
as String,downPayment: freezed == downPayment ? _self.downPayment : downPayment // ignore: cast_nullable_to_non_nullable
as double?,emiMonths: freezed == emiMonths ? _self.emiMonths : emiMonths // ignore: cast_nullable_to_non_nullable
as int?,emiAmount: freezed == emiAmount ? _self.emiAmount : emiAmount // ignore: cast_nullable_to_non_nullable
as double?,interestRate: freezed == interestRate ? _self.interestRate : interestRate // ignore: cast_nullable_to_non_nullable
as double?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,statusReason: freezed == statusReason ? _self.statusReason : statusReason // ignore: cast_nullable_to_non_nullable
as String?,approvedAt: freezed == approvedAt ? _self.approvedAt : approvedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,approvedBy: freezed == approvedBy ? _self.approvedBy : approvedBy // ignore: cast_nullable_to_non_nullable
as String?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,cancelledAt: freezed == cancelledAt ? _self.cancelledAt : cancelledAt // ignore: cast_nullable_to_non_nullable
as DateTime?,cancelledReason: freezed == cancelledReason ? _self.cancelledReason : cancelledReason // ignore: cast_nullable_to_non_nullable
as String?,documents: freezed == documents ? _self._documents : documents // ignore: cast_nullable_to_non_nullable
as List<BookingDocument>?,payments: freezed == payments ? _self._payments : payments // ignore: cast_nullable_to_non_nullable
as List<PaymentModel>?,totalPaid: freezed == totalPaid ? _self.totalPaid : totalPaid // ignore: cast_nullable_to_non_nullable
as double?,remainingAmount: freezed == remainingAmount ? _self.remainingAmount : remainingAmount // ignore: cast_nullable_to_non_nullable
as double?,registryDate: freezed == registryDate ? _self.registryDate : registryDate // ignore: cast_nullable_to_non_nullable
as DateTime?,registryNumber: freezed == registryNumber ? _self.registryNumber : registryNumber // ignore: cast_nullable_to_non_nullable
as String?,registryOffice: freezed == registryOffice ? _self.registryOffice : registryOffice // ignore: cast_nullable_to_non_nullable
as String?,agreementDate: freezed == agreementDate ? _self.agreementDate : agreementDate // ignore: cast_nullable_to_non_nullable
as DateTime?,agreementNumber: freezed == agreementNumber ? _self.agreementNumber : agreementNumber // ignore: cast_nullable_to_non_nullable
as String?,agreementDocumentUrl: freezed == agreementDocumentUrl ? _self.agreementDocumentUrl : agreementDocumentUrl // ignore: cast_nullable_to_non_nullable
as String?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,updatedAt: freezed == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,history: freezed == history ? _self._history : history // ignore: cast_nullable_to_non_nullable
as List<BookingHistory>?,
  ));
}


}


/// @nodoc
mixin _$BookingDocument {

 String get id; String get type;// aadhar, pan, photo, agreement, etc.
 String get name; String get url; String? get thumbnailUrl; DateTime? get uploadedAt; String? get verifiedBy; DateTime? get verifiedAt; String? get status;// pending, verified, rejected
 String? get notes;
/// Create a copy of BookingDocument
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BookingDocumentCopyWith<BookingDocument> get copyWith => _$BookingDocumentCopyWithImpl<BookingDocument>(this as BookingDocument, _$identity);

  /// Serializes this BookingDocument to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BookingDocument&&(identical(other.id, id) || other.id == id)&&(identical(other.type, type) || other.type == type)&&(identical(other.name, name) || other.name == name)&&(identical(other.url, url) || other.url == url)&&(identical(other.thumbnailUrl, thumbnailUrl) || other.thumbnailUrl == thumbnailUrl)&&(identical(other.uploadedAt, uploadedAt) || other.uploadedAt == uploadedAt)&&(identical(other.verifiedBy, verifiedBy) || other.verifiedBy == verifiedBy)&&(identical(other.verifiedAt, verifiedAt) || other.verifiedAt == verifiedAt)&&(identical(other.status, status) || other.status == status)&&(identical(other.notes, notes) || other.notes == notes));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,type,name,url,thumbnailUrl,uploadedAt,verifiedBy,verifiedAt,status,notes);

@override
String toString() {
  return 'BookingDocument(id: $id, type: $type, name: $name, url: $url, thumbnailUrl: $thumbnailUrl, uploadedAt: $uploadedAt, verifiedBy: $verifiedBy, verifiedAt: $verifiedAt, status: $status, notes: $notes)';
}


}

/// @nodoc
abstract mixin class $BookingDocumentCopyWith<$Res>  {
  factory $BookingDocumentCopyWith(BookingDocument value, $Res Function(BookingDocument) _then) = _$BookingDocumentCopyWithImpl;
@useResult
$Res call({
 String id, String type, String name, String url, String? thumbnailUrl, DateTime? uploadedAt, String? verifiedBy, DateTime? verifiedAt, String? status, String? notes
});




}
/// @nodoc
class _$BookingDocumentCopyWithImpl<$Res>
    implements $BookingDocumentCopyWith<$Res> {
  _$BookingDocumentCopyWithImpl(this._self, this._then);

  final BookingDocument _self;
  final $Res Function(BookingDocument) _then;

/// Create a copy of BookingDocument
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? type = null,Object? name = null,Object? url = null,Object? thumbnailUrl = freezed,Object? uploadedAt = freezed,Object? verifiedBy = freezed,Object? verifiedAt = freezed,Object? status = freezed,Object? notes = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as String,thumbnailUrl: freezed == thumbnailUrl ? _self.thumbnailUrl : thumbnailUrl // ignore: cast_nullable_to_non_nullable
as String?,uploadedAt: freezed == uploadedAt ? _self.uploadedAt : uploadedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,verifiedBy: freezed == verifiedBy ? _self.verifiedBy : verifiedBy // ignore: cast_nullable_to_non_nullable
as String?,verifiedAt: freezed == verifiedAt ? _self.verifiedAt : verifiedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,status: freezed == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [BookingDocument].
extension BookingDocumentPatterns on BookingDocument {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BookingDocument value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BookingDocument() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BookingDocument value)  $default,){
final _that = this;
switch (_that) {
case _BookingDocument():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BookingDocument value)?  $default,){
final _that = this;
switch (_that) {
case _BookingDocument() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String type,  String name,  String url,  String? thumbnailUrl,  DateTime? uploadedAt,  String? verifiedBy,  DateTime? verifiedAt,  String? status,  String? notes)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BookingDocument() when $default != null:
return $default(_that.id,_that.type,_that.name,_that.url,_that.thumbnailUrl,_that.uploadedAt,_that.verifiedBy,_that.verifiedAt,_that.status,_that.notes);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String type,  String name,  String url,  String? thumbnailUrl,  DateTime? uploadedAt,  String? verifiedBy,  DateTime? verifiedAt,  String? status,  String? notes)  $default,) {final _that = this;
switch (_that) {
case _BookingDocument():
return $default(_that.id,_that.type,_that.name,_that.url,_that.thumbnailUrl,_that.uploadedAt,_that.verifiedBy,_that.verifiedAt,_that.status,_that.notes);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String type,  String name,  String url,  String? thumbnailUrl,  DateTime? uploadedAt,  String? verifiedBy,  DateTime? verifiedAt,  String? status,  String? notes)?  $default,) {final _that = this;
switch (_that) {
case _BookingDocument() when $default != null:
return $default(_that.id,_that.type,_that.name,_that.url,_that.thumbnailUrl,_that.uploadedAt,_that.verifiedBy,_that.verifiedAt,_that.status,_that.notes);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _BookingDocument implements BookingDocument {
  const _BookingDocument({this.id = '', this.type = '', this.name = '', this.url = '', this.thumbnailUrl, this.uploadedAt, this.verifiedBy, this.verifiedAt, this.status, this.notes});
  factory _BookingDocument.fromJson(Map<String, dynamic> json) => _$BookingDocumentFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String type;
// aadhar, pan, photo, agreement, etc.
@override@JsonKey() final  String name;
@override@JsonKey() final  String url;
@override final  String? thumbnailUrl;
@override final  DateTime? uploadedAt;
@override final  String? verifiedBy;
@override final  DateTime? verifiedAt;
@override final  String? status;
// pending, verified, rejected
@override final  String? notes;

/// Create a copy of BookingDocument
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BookingDocumentCopyWith<_BookingDocument> get copyWith => __$BookingDocumentCopyWithImpl<_BookingDocument>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$BookingDocumentToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _BookingDocument&&(identical(other.id, id) || other.id == id)&&(identical(other.type, type) || other.type == type)&&(identical(other.name, name) || other.name == name)&&(identical(other.url, url) || other.url == url)&&(identical(other.thumbnailUrl, thumbnailUrl) || other.thumbnailUrl == thumbnailUrl)&&(identical(other.uploadedAt, uploadedAt) || other.uploadedAt == uploadedAt)&&(identical(other.verifiedBy, verifiedBy) || other.verifiedBy == verifiedBy)&&(identical(other.verifiedAt, verifiedAt) || other.verifiedAt == verifiedAt)&&(identical(other.status, status) || other.status == status)&&(identical(other.notes, notes) || other.notes == notes));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,type,name,url,thumbnailUrl,uploadedAt,verifiedBy,verifiedAt,status,notes);

@override
String toString() {
  return 'BookingDocument(id: $id, type: $type, name: $name, url: $url, thumbnailUrl: $thumbnailUrl, uploadedAt: $uploadedAt, verifiedBy: $verifiedBy, verifiedAt: $verifiedAt, status: $status, notes: $notes)';
}


}

/// @nodoc
abstract mixin class _$BookingDocumentCopyWith<$Res> implements $BookingDocumentCopyWith<$Res> {
  factory _$BookingDocumentCopyWith(_BookingDocument value, $Res Function(_BookingDocument) _then) = __$BookingDocumentCopyWithImpl;
@override @useResult
$Res call({
 String id, String type, String name, String url, String? thumbnailUrl, DateTime? uploadedAt, String? verifiedBy, DateTime? verifiedAt, String? status, String? notes
});




}
/// @nodoc
class __$BookingDocumentCopyWithImpl<$Res>
    implements _$BookingDocumentCopyWith<$Res> {
  __$BookingDocumentCopyWithImpl(this._self, this._then);

  final _BookingDocument _self;
  final $Res Function(_BookingDocument) _then;

/// Create a copy of BookingDocument
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? type = null,Object? name = null,Object? url = null,Object? thumbnailUrl = freezed,Object? uploadedAt = freezed,Object? verifiedBy = freezed,Object? verifiedAt = freezed,Object? status = freezed,Object? notes = freezed,}) {
  return _then(_BookingDocument(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as String,thumbnailUrl: freezed == thumbnailUrl ? _self.thumbnailUrl : thumbnailUrl // ignore: cast_nullable_to_non_nullable
as String?,uploadedAt: freezed == uploadedAt ? _self.uploadedAt : uploadedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,verifiedBy: freezed == verifiedBy ? _self.verifiedBy : verifiedBy // ignore: cast_nullable_to_non_nullable
as String?,verifiedAt: freezed == verifiedAt ? _self.verifiedAt : verifiedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,status: freezed == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$PaymentModel {

 String get id; String get bookingId; double get amount; String get type;// token, down_payment, installment, registry, full
 String get method;// cash, cheque, bank_transfer, upi, razorpay
 String? get transactionId; String? get razorpayOrderId; String? get razorpayPaymentId; DateTime? get paidAt; String? get paidBy; String? get receivedBy; String? get status;// pending, completed, failed, refunded
 String? get notes; String? get receiptUrl; DateTime? get createdAt;
/// Create a copy of PaymentModel
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentModelCopyWith<PaymentModel> get copyWith => _$PaymentModelCopyWithImpl<PaymentModel>(this as PaymentModel, _$identity);

  /// Serializes this PaymentModel to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentModel&&(identical(other.id, id) || other.id == id)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.type, type) || other.type == type)&&(identical(other.method, method) || other.method == method)&&(identical(other.transactionId, transactionId) || other.transactionId == transactionId)&&(identical(other.razorpayOrderId, razorpayOrderId) || other.razorpayOrderId == razorpayOrderId)&&(identical(other.razorpayPaymentId, razorpayPaymentId) || other.razorpayPaymentId == razorpayPaymentId)&&(identical(other.paidAt, paidAt) || other.paidAt == paidAt)&&(identical(other.paidBy, paidBy) || other.paidBy == paidBy)&&(identical(other.receivedBy, receivedBy) || other.receivedBy == receivedBy)&&(identical(other.status, status) || other.status == status)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.receiptUrl, receiptUrl) || other.receiptUrl == receiptUrl)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,bookingId,amount,type,method,transactionId,razorpayOrderId,razorpayPaymentId,paidAt,paidBy,receivedBy,status,notes,receiptUrl,createdAt);

@override
String toString() {
  return 'PaymentModel(id: $id, bookingId: $bookingId, amount: $amount, type: $type, method: $method, transactionId: $transactionId, razorpayOrderId: $razorpayOrderId, razorpayPaymentId: $razorpayPaymentId, paidAt: $paidAt, paidBy: $paidBy, receivedBy: $receivedBy, status: $status, notes: $notes, receiptUrl: $receiptUrl, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class $PaymentModelCopyWith<$Res>  {
  factory $PaymentModelCopyWith(PaymentModel value, $Res Function(PaymentModel) _then) = _$PaymentModelCopyWithImpl;
@useResult
$Res call({
 String id, String bookingId, double amount, String type, String method, String? transactionId, String? razorpayOrderId, String? razorpayPaymentId, DateTime? paidAt, String? paidBy, String? receivedBy, String? status, String? notes, String? receiptUrl, DateTime? createdAt
});




}
/// @nodoc
class _$PaymentModelCopyWithImpl<$Res>
    implements $PaymentModelCopyWith<$Res> {
  _$PaymentModelCopyWithImpl(this._self, this._then);

  final PaymentModel _self;
  final $Res Function(PaymentModel) _then;

/// Create a copy of PaymentModel
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? bookingId = null,Object? amount = null,Object? type = null,Object? method = null,Object? transactionId = freezed,Object? razorpayOrderId = freezed,Object? razorpayPaymentId = freezed,Object? paidAt = freezed,Object? paidBy = freezed,Object? receivedBy = freezed,Object? status = freezed,Object? notes = freezed,Object? receiptUrl = freezed,Object? createdAt = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as double,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,method: null == method ? _self.method : method // ignore: cast_nullable_to_non_nullable
as String,transactionId: freezed == transactionId ? _self.transactionId : transactionId // ignore: cast_nullable_to_non_nullable
as String?,razorpayOrderId: freezed == razorpayOrderId ? _self.razorpayOrderId : razorpayOrderId // ignore: cast_nullable_to_non_nullable
as String?,razorpayPaymentId: freezed == razorpayPaymentId ? _self.razorpayPaymentId : razorpayPaymentId // ignore: cast_nullable_to_non_nullable
as String?,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,paidBy: freezed == paidBy ? _self.paidBy : paidBy // ignore: cast_nullable_to_non_nullable
as String?,receivedBy: freezed == receivedBy ? _self.receivedBy : receivedBy // ignore: cast_nullable_to_non_nullable
as String?,status: freezed == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,receiptUrl: freezed == receiptUrl ? _self.receiptUrl : receiptUrl // ignore: cast_nullable_to_non_nullable
as String?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [PaymentModel].
extension PaymentModelPatterns on PaymentModel {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PaymentModel value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PaymentModel() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PaymentModel value)  $default,){
final _that = this;
switch (_that) {
case _PaymentModel():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PaymentModel value)?  $default,){
final _that = this;
switch (_that) {
case _PaymentModel() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String bookingId,  double amount,  String type,  String method,  String? transactionId,  String? razorpayOrderId,  String? razorpayPaymentId,  DateTime? paidAt,  String? paidBy,  String? receivedBy,  String? status,  String? notes,  String? receiptUrl,  DateTime? createdAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PaymentModel() when $default != null:
return $default(_that.id,_that.bookingId,_that.amount,_that.type,_that.method,_that.transactionId,_that.razorpayOrderId,_that.razorpayPaymentId,_that.paidAt,_that.paidBy,_that.receivedBy,_that.status,_that.notes,_that.receiptUrl,_that.createdAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String bookingId,  double amount,  String type,  String method,  String? transactionId,  String? razorpayOrderId,  String? razorpayPaymentId,  DateTime? paidAt,  String? paidBy,  String? receivedBy,  String? status,  String? notes,  String? receiptUrl,  DateTime? createdAt)  $default,) {final _that = this;
switch (_that) {
case _PaymentModel():
return $default(_that.id,_that.bookingId,_that.amount,_that.type,_that.method,_that.transactionId,_that.razorpayOrderId,_that.razorpayPaymentId,_that.paidAt,_that.paidBy,_that.receivedBy,_that.status,_that.notes,_that.receiptUrl,_that.createdAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String bookingId,  double amount,  String type,  String method,  String? transactionId,  String? razorpayOrderId,  String? razorpayPaymentId,  DateTime? paidAt,  String? paidBy,  String? receivedBy,  String? status,  String? notes,  String? receiptUrl,  DateTime? createdAt)?  $default,) {final _that = this;
switch (_that) {
case _PaymentModel() when $default != null:
return $default(_that.id,_that.bookingId,_that.amount,_that.type,_that.method,_that.transactionId,_that.razorpayOrderId,_that.razorpayPaymentId,_that.paidAt,_that.paidBy,_that.receivedBy,_that.status,_that.notes,_that.receiptUrl,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PaymentModel implements PaymentModel {
  const _PaymentModel({this.id = '', this.bookingId = '', this.amount = 0.0, this.type = '', this.method = '', this.transactionId, this.razorpayOrderId, this.razorpayPaymentId, this.paidAt, this.paidBy, this.receivedBy, this.status, this.notes, this.receiptUrl, this.createdAt});
  factory _PaymentModel.fromJson(Map<String, dynamic> json) => _$PaymentModelFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String bookingId;
@override@JsonKey() final  double amount;
@override@JsonKey() final  String type;
// token, down_payment, installment, registry, full
@override@JsonKey() final  String method;
// cash, cheque, bank_transfer, upi, razorpay
@override final  String? transactionId;
@override final  String? razorpayOrderId;
@override final  String? razorpayPaymentId;
@override final  DateTime? paidAt;
@override final  String? paidBy;
@override final  String? receivedBy;
@override final  String? status;
// pending, completed, failed, refunded
@override final  String? notes;
@override final  String? receiptUrl;
@override final  DateTime? createdAt;

/// Create a copy of PaymentModel
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PaymentModelCopyWith<_PaymentModel> get copyWith => __$PaymentModelCopyWithImpl<_PaymentModel>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PaymentModelToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _PaymentModel&&(identical(other.id, id) || other.id == id)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.type, type) || other.type == type)&&(identical(other.method, method) || other.method == method)&&(identical(other.transactionId, transactionId) || other.transactionId == transactionId)&&(identical(other.razorpayOrderId, razorpayOrderId) || other.razorpayOrderId == razorpayOrderId)&&(identical(other.razorpayPaymentId, razorpayPaymentId) || other.razorpayPaymentId == razorpayPaymentId)&&(identical(other.paidAt, paidAt) || other.paidAt == paidAt)&&(identical(other.paidBy, paidBy) || other.paidBy == paidBy)&&(identical(other.receivedBy, receivedBy) || other.receivedBy == receivedBy)&&(identical(other.status, status) || other.status == status)&&(identical(other.notes, notes) || other.notes == notes)&&(identical(other.receiptUrl, receiptUrl) || other.receiptUrl == receiptUrl)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,bookingId,amount,type,method,transactionId,razorpayOrderId,razorpayPaymentId,paidAt,paidBy,receivedBy,status,notes,receiptUrl,createdAt);

@override
String toString() {
  return 'PaymentModel(id: $id, bookingId: $bookingId, amount: $amount, type: $type, method: $method, transactionId: $transactionId, razorpayOrderId: $razorpayOrderId, razorpayPaymentId: $razorpayPaymentId, paidAt: $paidAt, paidBy: $paidBy, receivedBy: $receivedBy, status: $status, notes: $notes, receiptUrl: $receiptUrl, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$PaymentModelCopyWith<$Res> implements $PaymentModelCopyWith<$Res> {
  factory _$PaymentModelCopyWith(_PaymentModel value, $Res Function(_PaymentModel) _then) = __$PaymentModelCopyWithImpl;
@override @useResult
$Res call({
 String id, String bookingId, double amount, String type, String method, String? transactionId, String? razorpayOrderId, String? razorpayPaymentId, DateTime? paidAt, String? paidBy, String? receivedBy, String? status, String? notes, String? receiptUrl, DateTime? createdAt
});




}
/// @nodoc
class __$PaymentModelCopyWithImpl<$Res>
    implements _$PaymentModelCopyWith<$Res> {
  __$PaymentModelCopyWithImpl(this._self, this._then);

  final _PaymentModel _self;
  final $Res Function(_PaymentModel) _then;

/// Create a copy of PaymentModel
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? bookingId = null,Object? amount = null,Object? type = null,Object? method = null,Object? transactionId = freezed,Object? razorpayOrderId = freezed,Object? razorpayPaymentId = freezed,Object? paidAt = freezed,Object? paidBy = freezed,Object? receivedBy = freezed,Object? status = freezed,Object? notes = freezed,Object? receiptUrl = freezed,Object? createdAt = freezed,}) {
  return _then(_PaymentModel(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as double,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,method: null == method ? _self.method : method // ignore: cast_nullable_to_non_nullable
as String,transactionId: freezed == transactionId ? _self.transactionId : transactionId // ignore: cast_nullable_to_non_nullable
as String?,razorpayOrderId: freezed == razorpayOrderId ? _self.razorpayOrderId : razorpayOrderId // ignore: cast_nullable_to_non_nullable
as String?,razorpayPaymentId: freezed == razorpayPaymentId ? _self.razorpayPaymentId : razorpayPaymentId // ignore: cast_nullable_to_non_nullable
as String?,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,paidBy: freezed == paidBy ? _self.paidBy : paidBy // ignore: cast_nullable_to_non_nullable
as String?,receivedBy: freezed == receivedBy ? _self.receivedBy : receivedBy // ignore: cast_nullable_to_non_nullable
as String?,status: freezed == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,receiptUrl: freezed == receiptUrl ? _self.receiptUrl : receiptUrl // ignore: cast_nullable_to_non_nullable
as String?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$BookingHistory {

 String get id; String get action; String get performedBy; DateTime get performedAt; String? get notes; Map<String, dynamic>? get oldValues; Map<String, dynamic>? get newValues;
/// Create a copy of BookingHistory
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BookingHistoryCopyWith<BookingHistory> get copyWith => _$BookingHistoryCopyWithImpl<BookingHistory>(this as BookingHistory, _$identity);

  /// Serializes this BookingHistory to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BookingHistory&&(identical(other.id, id) || other.id == id)&&(identical(other.action, action) || other.action == action)&&(identical(other.performedBy, performedBy) || other.performedBy == performedBy)&&(identical(other.performedAt, performedAt) || other.performedAt == performedAt)&&(identical(other.notes, notes) || other.notes == notes)&&const DeepCollectionEquality().equals(other.oldValues, oldValues)&&const DeepCollectionEquality().equals(other.newValues, newValues));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,action,performedBy,performedAt,notes,const DeepCollectionEquality().hash(oldValues),const DeepCollectionEquality().hash(newValues));

@override
String toString() {
  return 'BookingHistory(id: $id, action: $action, performedBy: $performedBy, performedAt: $performedAt, notes: $notes, oldValues: $oldValues, newValues: $newValues)';
}


}

/// @nodoc
abstract mixin class $BookingHistoryCopyWith<$Res>  {
  factory $BookingHistoryCopyWith(BookingHistory value, $Res Function(BookingHistory) _then) = _$BookingHistoryCopyWithImpl;
@useResult
$Res call({
 String id, String action, String performedBy, DateTime performedAt, String? notes, Map<String, dynamic>? oldValues, Map<String, dynamic>? newValues
});




}
/// @nodoc
class _$BookingHistoryCopyWithImpl<$Res>
    implements $BookingHistoryCopyWith<$Res> {
  _$BookingHistoryCopyWithImpl(this._self, this._then);

  final BookingHistory _self;
  final $Res Function(BookingHistory) _then;

/// Create a copy of BookingHistory
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? action = null,Object? performedBy = null,Object? performedAt = null,Object? notes = freezed,Object? oldValues = freezed,Object? newValues = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,action: null == action ? _self.action : action // ignore: cast_nullable_to_non_nullable
as String,performedBy: null == performedBy ? _self.performedBy : performedBy // ignore: cast_nullable_to_non_nullable
as String,performedAt: null == performedAt ? _self.performedAt : performedAt // ignore: cast_nullable_to_non_nullable
as DateTime,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,oldValues: freezed == oldValues ? _self.oldValues : oldValues // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,newValues: freezed == newValues ? _self.newValues : newValues // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,
  ));
}

}


/// Adds pattern-matching-related methods to [BookingHistory].
extension BookingHistoryPatterns on BookingHistory {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BookingHistory value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BookingHistory() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BookingHistory value)  $default,){
final _that = this;
switch (_that) {
case _BookingHistory():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BookingHistory value)?  $default,){
final _that = this;
switch (_that) {
case _BookingHistory() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String action,  String performedBy,  DateTime performedAt,  String? notes,  Map<String, dynamic>? oldValues,  Map<String, dynamic>? newValues)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BookingHistory() when $default != null:
return $default(_that.id,_that.action,_that.performedBy,_that.performedAt,_that.notes,_that.oldValues,_that.newValues);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String action,  String performedBy,  DateTime performedAt,  String? notes,  Map<String, dynamic>? oldValues,  Map<String, dynamic>? newValues)  $default,) {final _that = this;
switch (_that) {
case _BookingHistory():
return $default(_that.id,_that.action,_that.performedBy,_that.performedAt,_that.notes,_that.oldValues,_that.newValues);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String action,  String performedBy,  DateTime performedAt,  String? notes,  Map<String, dynamic>? oldValues,  Map<String, dynamic>? newValues)?  $default,) {final _that = this;
switch (_that) {
case _BookingHistory() when $default != null:
return $default(_that.id,_that.action,_that.performedBy,_that.performedAt,_that.notes,_that.oldValues,_that.newValues);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _BookingHistory implements BookingHistory {
  const _BookingHistory({this.id = '', this.action = '', this.performedBy = '', required this.performedAt, this.notes, final  Map<String, dynamic>? oldValues, final  Map<String, dynamic>? newValues}): _oldValues = oldValues,_newValues = newValues;
  factory _BookingHistory.fromJson(Map<String, dynamic> json) => _$BookingHistoryFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String action;
@override@JsonKey() final  String performedBy;
@override final  DateTime performedAt;
@override final  String? notes;
 final  Map<String, dynamic>? _oldValues;
@override Map<String, dynamic>? get oldValues {
  final value = _oldValues;
  if (value == null) return null;
  if (_oldValues is EqualUnmodifiableMapView) return _oldValues;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(value);
}

 final  Map<String, dynamic>? _newValues;
@override Map<String, dynamic>? get newValues {
  final value = _newValues;
  if (value == null) return null;
  if (_newValues is EqualUnmodifiableMapView) return _newValues;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(value);
}


/// Create a copy of BookingHistory
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BookingHistoryCopyWith<_BookingHistory> get copyWith => __$BookingHistoryCopyWithImpl<_BookingHistory>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$BookingHistoryToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _BookingHistory&&(identical(other.id, id) || other.id == id)&&(identical(other.action, action) || other.action == action)&&(identical(other.performedBy, performedBy) || other.performedBy == performedBy)&&(identical(other.performedAt, performedAt) || other.performedAt == performedAt)&&(identical(other.notes, notes) || other.notes == notes)&&const DeepCollectionEquality().equals(other._oldValues, _oldValues)&&const DeepCollectionEquality().equals(other._newValues, _newValues));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,action,performedBy,performedAt,notes,const DeepCollectionEquality().hash(_oldValues),const DeepCollectionEquality().hash(_newValues));

@override
String toString() {
  return 'BookingHistory(id: $id, action: $action, performedBy: $performedBy, performedAt: $performedAt, notes: $notes, oldValues: $oldValues, newValues: $newValues)';
}


}

/// @nodoc
abstract mixin class _$BookingHistoryCopyWith<$Res> implements $BookingHistoryCopyWith<$Res> {
  factory _$BookingHistoryCopyWith(_BookingHistory value, $Res Function(_BookingHistory) _then) = __$BookingHistoryCopyWithImpl;
@override @useResult
$Res call({
 String id, String action, String performedBy, DateTime performedAt, String? notes, Map<String, dynamic>? oldValues, Map<String, dynamic>? newValues
});




}
/// @nodoc
class __$BookingHistoryCopyWithImpl<$Res>
    implements _$BookingHistoryCopyWith<$Res> {
  __$BookingHistoryCopyWithImpl(this._self, this._then);

  final _BookingHistory _self;
  final $Res Function(_BookingHistory) _then;

/// Create a copy of BookingHistory
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? action = null,Object? performedBy = null,Object? performedAt = null,Object? notes = freezed,Object? oldValues = freezed,Object? newValues = freezed,}) {
  return _then(_BookingHistory(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,action: null == action ? _self.action : action // ignore: cast_nullable_to_non_nullable
as String,performedBy: null == performedBy ? _self.performedBy : performedBy // ignore: cast_nullable_to_non_nullable
as String,performedAt: null == performedAt ? _self.performedAt : performedAt // ignore: cast_nullable_to_non_nullable
as DateTime,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as String?,oldValues: freezed == oldValues ? _self._oldValues : oldValues // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,newValues: freezed == newValues ? _self._newValues : newValues // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,
  ));
}


}

// dart format on
