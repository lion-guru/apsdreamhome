// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'booking_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
  'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models',
);

BookingModel _$BookingModelFromJson(Map<String, dynamic> json) {
  return _BookingModel.fromJson(json);
}

/// @nodoc
mixin _$BookingModel {
  String get id => throw _privateConstructorUsedError;
  String get plotId => throw _privateConstructorUsedError;
  String get plotNumber => throw _privateConstructorUsedError;
  String get colonyId => throw _privateConstructorUsedError;
  String get colonyName => throw _privateConstructorUsedError; // Customer Info
  String get customerId => throw _privateConstructorUsedError;
  String get customerName => throw _privateConstructorUsedError;
  String get customerPhone => throw _privateConstructorUsedError;
  String? get customerEmail => throw _privateConstructorUsedError;
  String? get customerAddress =>
      throw _privateConstructorUsedError; // Associate Info (if booked through associate)
  String? get associateId => throw _privateConstructorUsedError;
  String? get associateName => throw _privateConstructorUsedError;
  String? get associateRank => throw _privateConstructorUsedError;
  double? get associateCommission =>
      throw _privateConstructorUsedError; // Pricing
  double get plotPrice => throw _privateConstructorUsedError;
  double get tokenAmount => throw _privateConstructorUsedError;
  double get totalAmount => throw _privateConstructorUsedError;
  String get paymentPlan =>
      throw _privateConstructorUsedError; // full, emi, installment
  // EMI Details (if applicable)
  double? get downPayment => throw _privateConstructorUsedError;
  int? get emiMonths => throw _privateConstructorUsedError;
  double? get emiAmount => throw _privateConstructorUsedError;
  double? get interestRate => throw _privateConstructorUsedError; // Status
  String get status =>
      throw _privateConstructorUsedError; // pending, approved, rejected, completed, cancelled
  String? get statusReason => throw _privateConstructorUsedError;
  DateTime? get approvedAt => throw _privateConstructorUsedError;
  String? get approvedBy => throw _privateConstructorUsedError;
  DateTime? get completedAt => throw _privateConstructorUsedError;
  DateTime? get cancelledAt => throw _privateConstructorUsedError;
  String? get cancelledReason =>
      throw _privateConstructorUsedError; // Documents
  List<BookingDocument>? get documents =>
      throw _privateConstructorUsedError; // Payments
  List<PaymentModel>? get payments => throw _privateConstructorUsedError;
  double? get totalPaid => throw _privateConstructorUsedError;
  double? get remainingAmount =>
      throw _privateConstructorUsedError; // Registry Info
  DateTime? get registryDate => throw _privateConstructorUsedError;
  String? get registryNumber => throw _privateConstructorUsedError;
  String? get registryOffice => throw _privateConstructorUsedError; // Agreement
  DateTime? get agreementDate => throw _privateConstructorUsedError;
  String? get agreementNumber => throw _privateConstructorUsedError;
  String? get agreementDocumentUrl =>
      throw _privateConstructorUsedError; // Timestamps
  DateTime? get createdAt => throw _privateConstructorUsedError;
  DateTime? get updatedAt => throw _privateConstructorUsedError; // Notes
  String? get notes => throw _privateConstructorUsedError;
  List<BookingHistory>? get history => throw _privateConstructorUsedError;

  /// Serializes this BookingModel to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of BookingModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $BookingModelCopyWith<BookingModel> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $BookingModelCopyWith<$Res> {
  factory $BookingModelCopyWith(
    BookingModel value,
    $Res Function(BookingModel) then,
  ) = _$BookingModelCopyWithImpl<$Res, BookingModel>;
  @useResult
  $Res call({
    String id,
    String plotId,
    String plotNumber,
    String colonyId,
    String colonyName,
    String customerId,
    String customerName,
    String customerPhone,
    String? customerEmail,
    String? customerAddress,
    String? associateId,
    String? associateName,
    String? associateRank,
    double? associateCommission,
    double plotPrice,
    double tokenAmount,
    double totalAmount,
    String paymentPlan,
    double? downPayment,
    int? emiMonths,
    double? emiAmount,
    double? interestRate,
    String status,
    String? statusReason,
    DateTime? approvedAt,
    String? approvedBy,
    DateTime? completedAt,
    DateTime? cancelledAt,
    String? cancelledReason,
    List<BookingDocument>? documents,
    List<PaymentModel>? payments,
    double? totalPaid,
    double? remainingAmount,
    DateTime? registryDate,
    String? registryNumber,
    String? registryOffice,
    DateTime? agreementDate,
    String? agreementNumber,
    String? agreementDocumentUrl,
    DateTime? createdAt,
    DateTime? updatedAt,
    String? notes,
    List<BookingHistory>? history,
  });
}

/// @nodoc
class _$BookingModelCopyWithImpl<$Res, $Val extends BookingModel>
    implements $BookingModelCopyWith<$Res> {
  _$BookingModelCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of BookingModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? plotId = null,
    Object? plotNumber = null,
    Object? colonyId = null,
    Object? colonyName = null,
    Object? customerId = null,
    Object? customerName = null,
    Object? customerPhone = null,
    Object? customerEmail = freezed,
    Object? customerAddress = freezed,
    Object? associateId = freezed,
    Object? associateName = freezed,
    Object? associateRank = freezed,
    Object? associateCommission = freezed,
    Object? plotPrice = null,
    Object? tokenAmount = null,
    Object? totalAmount = null,
    Object? paymentPlan = null,
    Object? downPayment = freezed,
    Object? emiMonths = freezed,
    Object? emiAmount = freezed,
    Object? interestRate = freezed,
    Object? status = null,
    Object? statusReason = freezed,
    Object? approvedAt = freezed,
    Object? approvedBy = freezed,
    Object? completedAt = freezed,
    Object? cancelledAt = freezed,
    Object? cancelledReason = freezed,
    Object? documents = freezed,
    Object? payments = freezed,
    Object? totalPaid = freezed,
    Object? remainingAmount = freezed,
    Object? registryDate = freezed,
    Object? registryNumber = freezed,
    Object? registryOffice = freezed,
    Object? agreementDate = freezed,
    Object? agreementNumber = freezed,
    Object? agreementDocumentUrl = freezed,
    Object? createdAt = freezed,
    Object? updatedAt = freezed,
    Object? notes = freezed,
    Object? history = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            plotId: null == plotId
                ? _value.plotId
                : plotId // ignore: cast_nullable_to_non_nullable
                      as String,
            plotNumber: null == plotNumber
                ? _value.plotNumber
                : plotNumber // ignore: cast_nullable_to_non_nullable
                      as String,
            colonyId: null == colonyId
                ? _value.colonyId
                : colonyId // ignore: cast_nullable_to_non_nullable
                      as String,
            colonyName: null == colonyName
                ? _value.colonyName
                : colonyName // ignore: cast_nullable_to_non_nullable
                      as String,
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
            customerEmail: freezed == customerEmail
                ? _value.customerEmail
                : customerEmail // ignore: cast_nullable_to_non_nullable
                      as String?,
            customerAddress: freezed == customerAddress
                ? _value.customerAddress
                : customerAddress // ignore: cast_nullable_to_non_nullable
                      as String?,
            associateId: freezed == associateId
                ? _value.associateId
                : associateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            associateName: freezed == associateName
                ? _value.associateName
                : associateName // ignore: cast_nullable_to_non_nullable
                      as String?,
            associateRank: freezed == associateRank
                ? _value.associateRank
                : associateRank // ignore: cast_nullable_to_non_nullable
                      as String?,
            associateCommission: freezed == associateCommission
                ? _value.associateCommission
                : associateCommission // ignore: cast_nullable_to_non_nullable
                      as double?,
            plotPrice: null == plotPrice
                ? _value.plotPrice
                : plotPrice // ignore: cast_nullable_to_non_nullable
                      as double,
            tokenAmount: null == tokenAmount
                ? _value.tokenAmount
                : tokenAmount // ignore: cast_nullable_to_non_nullable
                      as double,
            totalAmount: null == totalAmount
                ? _value.totalAmount
                : totalAmount // ignore: cast_nullable_to_non_nullable
                      as double,
            paymentPlan: null == paymentPlan
                ? _value.paymentPlan
                : paymentPlan // ignore: cast_nullable_to_non_nullable
                      as String,
            downPayment: freezed == downPayment
                ? _value.downPayment
                : downPayment // ignore: cast_nullable_to_non_nullable
                      as double?,
            emiMonths: freezed == emiMonths
                ? _value.emiMonths
                : emiMonths // ignore: cast_nullable_to_non_nullable
                      as int?,
            emiAmount: freezed == emiAmount
                ? _value.emiAmount
                : emiAmount // ignore: cast_nullable_to_non_nullable
                      as double?,
            interestRate: freezed == interestRate
                ? _value.interestRate
                : interestRate // ignore: cast_nullable_to_non_nullable
                      as double?,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as String,
            statusReason: freezed == statusReason
                ? _value.statusReason
                : statusReason // ignore: cast_nullable_to_non_nullable
                      as String?,
            approvedAt: freezed == approvedAt
                ? _value.approvedAt
                : approvedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            approvedBy: freezed == approvedBy
                ? _value.approvedBy
                : approvedBy // ignore: cast_nullable_to_non_nullable
                      as String?,
            completedAt: freezed == completedAt
                ? _value.completedAt
                : completedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            cancelledAt: freezed == cancelledAt
                ? _value.cancelledAt
                : cancelledAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            cancelledReason: freezed == cancelledReason
                ? _value.cancelledReason
                : cancelledReason // ignore: cast_nullable_to_non_nullable
                      as String?,
            documents: freezed == documents
                ? _value.documents
                : documents // ignore: cast_nullable_to_non_nullable
                      as List<BookingDocument>?,
            payments: freezed == payments
                ? _value.payments
                : payments // ignore: cast_nullable_to_non_nullable
                      as List<PaymentModel>?,
            totalPaid: freezed == totalPaid
                ? _value.totalPaid
                : totalPaid // ignore: cast_nullable_to_non_nullable
                      as double?,
            remainingAmount: freezed == remainingAmount
                ? _value.remainingAmount
                : remainingAmount // ignore: cast_nullable_to_non_nullable
                      as double?,
            registryDate: freezed == registryDate
                ? _value.registryDate
                : registryDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            registryNumber: freezed == registryNumber
                ? _value.registryNumber
                : registryNumber // ignore: cast_nullable_to_non_nullable
                      as String?,
            registryOffice: freezed == registryOffice
                ? _value.registryOffice
                : registryOffice // ignore: cast_nullable_to_non_nullable
                      as String?,
            agreementDate: freezed == agreementDate
                ? _value.agreementDate
                : agreementDate // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            agreementNumber: freezed == agreementNumber
                ? _value.agreementNumber
                : agreementNumber // ignore: cast_nullable_to_non_nullable
                      as String?,
            agreementDocumentUrl: freezed == agreementDocumentUrl
                ? _value.agreementDocumentUrl
                : agreementDocumentUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            updatedAt: freezed == updatedAt
                ? _value.updatedAt
                : updatedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            notes: freezed == notes
                ? _value.notes
                : notes // ignore: cast_nullable_to_non_nullable
                      as String?,
            history: freezed == history
                ? _value.history
                : history // ignore: cast_nullable_to_non_nullable
                      as List<BookingHistory>?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$BookingModelImplCopyWith<$Res>
    implements $BookingModelCopyWith<$Res> {
  factory _$$BookingModelImplCopyWith(
    _$BookingModelImpl value,
    $Res Function(_$BookingModelImpl) then,
  ) = __$$BookingModelImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String plotId,
    String plotNumber,
    String colonyId,
    String colonyName,
    String customerId,
    String customerName,
    String customerPhone,
    String? customerEmail,
    String? customerAddress,
    String? associateId,
    String? associateName,
    String? associateRank,
    double? associateCommission,
    double plotPrice,
    double tokenAmount,
    double totalAmount,
    String paymentPlan,
    double? downPayment,
    int? emiMonths,
    double? emiAmount,
    double? interestRate,
    String status,
    String? statusReason,
    DateTime? approvedAt,
    String? approvedBy,
    DateTime? completedAt,
    DateTime? cancelledAt,
    String? cancelledReason,
    List<BookingDocument>? documents,
    List<PaymentModel>? payments,
    double? totalPaid,
    double? remainingAmount,
    DateTime? registryDate,
    String? registryNumber,
    String? registryOffice,
    DateTime? agreementDate,
    String? agreementNumber,
    String? agreementDocumentUrl,
    DateTime? createdAt,
    DateTime? updatedAt,
    String? notes,
    List<BookingHistory>? history,
  });
}

/// @nodoc
class __$$BookingModelImplCopyWithImpl<$Res>
    extends _$BookingModelCopyWithImpl<$Res, _$BookingModelImpl>
    implements _$$BookingModelImplCopyWith<$Res> {
  __$$BookingModelImplCopyWithImpl(
    _$BookingModelImpl _value,
    $Res Function(_$BookingModelImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of BookingModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? plotId = null,
    Object? plotNumber = null,
    Object? colonyId = null,
    Object? colonyName = null,
    Object? customerId = null,
    Object? customerName = null,
    Object? customerPhone = null,
    Object? customerEmail = freezed,
    Object? customerAddress = freezed,
    Object? associateId = freezed,
    Object? associateName = freezed,
    Object? associateRank = freezed,
    Object? associateCommission = freezed,
    Object? plotPrice = null,
    Object? tokenAmount = null,
    Object? totalAmount = null,
    Object? paymentPlan = null,
    Object? downPayment = freezed,
    Object? emiMonths = freezed,
    Object? emiAmount = freezed,
    Object? interestRate = freezed,
    Object? status = null,
    Object? statusReason = freezed,
    Object? approvedAt = freezed,
    Object? approvedBy = freezed,
    Object? completedAt = freezed,
    Object? cancelledAt = freezed,
    Object? cancelledReason = freezed,
    Object? documents = freezed,
    Object? payments = freezed,
    Object? totalPaid = freezed,
    Object? remainingAmount = freezed,
    Object? registryDate = freezed,
    Object? registryNumber = freezed,
    Object? registryOffice = freezed,
    Object? agreementDate = freezed,
    Object? agreementNumber = freezed,
    Object? agreementDocumentUrl = freezed,
    Object? createdAt = freezed,
    Object? updatedAt = freezed,
    Object? notes = freezed,
    Object? history = freezed,
  }) {
    return _then(
      _$BookingModelImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        plotId: null == plotId
            ? _value.plotId
            : plotId // ignore: cast_nullable_to_non_nullable
                  as String,
        plotNumber: null == plotNumber
            ? _value.plotNumber
            : plotNumber // ignore: cast_nullable_to_non_nullable
                  as String,
        colonyId: null == colonyId
            ? _value.colonyId
            : colonyId // ignore: cast_nullable_to_non_nullable
                  as String,
        colonyName: null == colonyName
            ? _value.colonyName
            : colonyName // ignore: cast_nullable_to_non_nullable
                  as String,
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
        customerEmail: freezed == customerEmail
            ? _value.customerEmail
            : customerEmail // ignore: cast_nullable_to_non_nullable
                  as String?,
        customerAddress: freezed == customerAddress
            ? _value.customerAddress
            : customerAddress // ignore: cast_nullable_to_non_nullable
                  as String?,
        associateId: freezed == associateId
            ? _value.associateId
            : associateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        associateName: freezed == associateName
            ? _value.associateName
            : associateName // ignore: cast_nullable_to_non_nullable
                  as String?,
        associateRank: freezed == associateRank
            ? _value.associateRank
            : associateRank // ignore: cast_nullable_to_non_nullable
                  as String?,
        associateCommission: freezed == associateCommission
            ? _value.associateCommission
            : associateCommission // ignore: cast_nullable_to_non_nullable
                  as double?,
        plotPrice: null == plotPrice
            ? _value.plotPrice
            : plotPrice // ignore: cast_nullable_to_non_nullable
                  as double,
        tokenAmount: null == tokenAmount
            ? _value.tokenAmount
            : tokenAmount // ignore: cast_nullable_to_non_nullable
                  as double,
        totalAmount: null == totalAmount
            ? _value.totalAmount
            : totalAmount // ignore: cast_nullable_to_non_nullable
                  as double,
        paymentPlan: null == paymentPlan
            ? _value.paymentPlan
            : paymentPlan // ignore: cast_nullable_to_non_nullable
                  as String,
        downPayment: freezed == downPayment
            ? _value.downPayment
            : downPayment // ignore: cast_nullable_to_non_nullable
                  as double?,
        emiMonths: freezed == emiMonths
            ? _value.emiMonths
            : emiMonths // ignore: cast_nullable_to_non_nullable
                  as int?,
        emiAmount: freezed == emiAmount
            ? _value.emiAmount
            : emiAmount // ignore: cast_nullable_to_non_nullable
                  as double?,
        interestRate: freezed == interestRate
            ? _value.interestRate
            : interestRate // ignore: cast_nullable_to_non_nullable
                  as double?,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        statusReason: freezed == statusReason
            ? _value.statusReason
            : statusReason // ignore: cast_nullable_to_non_nullable
                  as String?,
        approvedAt: freezed == approvedAt
            ? _value.approvedAt
            : approvedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        approvedBy: freezed == approvedBy
            ? _value.approvedBy
            : approvedBy // ignore: cast_nullable_to_non_nullable
                  as String?,
        completedAt: freezed == completedAt
            ? _value.completedAt
            : completedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        cancelledAt: freezed == cancelledAt
            ? _value.cancelledAt
            : cancelledAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        cancelledReason: freezed == cancelledReason
            ? _value.cancelledReason
            : cancelledReason // ignore: cast_nullable_to_non_nullable
                  as String?,
        documents: freezed == documents
            ? _value._documents
            : documents // ignore: cast_nullable_to_non_nullable
                  as List<BookingDocument>?,
        payments: freezed == payments
            ? _value._payments
            : payments // ignore: cast_nullable_to_non_nullable
                  as List<PaymentModel>?,
        totalPaid: freezed == totalPaid
            ? _value.totalPaid
            : totalPaid // ignore: cast_nullable_to_non_nullable
                  as double?,
        remainingAmount: freezed == remainingAmount
            ? _value.remainingAmount
            : remainingAmount // ignore: cast_nullable_to_non_nullable
                  as double?,
        registryDate: freezed == registryDate
            ? _value.registryDate
            : registryDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        registryNumber: freezed == registryNumber
            ? _value.registryNumber
            : registryNumber // ignore: cast_nullable_to_non_nullable
                  as String?,
        registryOffice: freezed == registryOffice
            ? _value.registryOffice
            : registryOffice // ignore: cast_nullable_to_non_nullable
                  as String?,
        agreementDate: freezed == agreementDate
            ? _value.agreementDate
            : agreementDate // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        agreementNumber: freezed == agreementNumber
            ? _value.agreementNumber
            : agreementNumber // ignore: cast_nullable_to_non_nullable
                  as String?,
        agreementDocumentUrl: freezed == agreementDocumentUrl
            ? _value.agreementDocumentUrl
            : agreementDocumentUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        updatedAt: freezed == updatedAt
            ? _value.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        notes: freezed == notes
            ? _value.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        history: freezed == history
            ? _value._history
            : history // ignore: cast_nullable_to_non_nullable
                  as List<BookingHistory>?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$BookingModelImpl extends _BookingModel {
  const _$BookingModelImpl({
    required this.id,
    required this.plotId,
    required this.plotNumber,
    required this.colonyId,
    required this.colonyName,
    required this.customerId,
    required this.customerName,
    required this.customerPhone,
    this.customerEmail,
    this.customerAddress,
    this.associateId,
    this.associateName,
    this.associateRank,
    this.associateCommission,
    required this.plotPrice,
    required this.tokenAmount,
    required this.totalAmount,
    required this.paymentPlan,
    this.downPayment,
    this.emiMonths,
    this.emiAmount,
    this.interestRate,
    required this.status,
    this.statusReason,
    this.approvedAt,
    this.approvedBy,
    this.completedAt,
    this.cancelledAt,
    this.cancelledReason,
    final List<BookingDocument>? documents,
    final List<PaymentModel>? payments,
    this.totalPaid,
    this.remainingAmount,
    this.registryDate,
    this.registryNumber,
    this.registryOffice,
    this.agreementDate,
    this.agreementNumber,
    this.agreementDocumentUrl,
    this.createdAt,
    this.updatedAt,
    this.notes,
    final List<BookingHistory>? history,
  }) : _documents = documents,
       _payments = payments,
       _history = history,
       super._();

  factory _$BookingModelImpl.fromJson(Map<String, dynamic> json) =>
      _$$BookingModelImplFromJson(json);

  @override
  final String id;
  @override
  final String plotId;
  @override
  final String plotNumber;
  @override
  final String colonyId;
  @override
  final String colonyName;
  // Customer Info
  @override
  final String customerId;
  @override
  final String customerName;
  @override
  final String customerPhone;
  @override
  final String? customerEmail;
  @override
  final String? customerAddress;
  // Associate Info (if booked through associate)
  @override
  final String? associateId;
  @override
  final String? associateName;
  @override
  final String? associateRank;
  @override
  final double? associateCommission;
  // Pricing
  @override
  final double plotPrice;
  @override
  final double tokenAmount;
  @override
  final double totalAmount;
  @override
  final String paymentPlan;
  // full, emi, installment
  // EMI Details (if applicable)
  @override
  final double? downPayment;
  @override
  final int? emiMonths;
  @override
  final double? emiAmount;
  @override
  final double? interestRate;
  // Status
  @override
  final String status;
  // pending, approved, rejected, completed, cancelled
  @override
  final String? statusReason;
  @override
  final DateTime? approvedAt;
  @override
  final String? approvedBy;
  @override
  final DateTime? completedAt;
  @override
  final DateTime? cancelledAt;
  @override
  final String? cancelledReason;
  // Documents
  final List<BookingDocument>? _documents;
  // Documents
  @override
  List<BookingDocument>? get documents {
    final value = _documents;
    if (value == null) return null;
    if (_documents is EqualUnmodifiableListView) return _documents;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  // Payments
  final List<PaymentModel>? _payments;
  // Payments
  @override
  List<PaymentModel>? get payments {
    final value = _payments;
    if (value == null) return null;
    if (_payments is EqualUnmodifiableListView) return _payments;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  @override
  final double? totalPaid;
  @override
  final double? remainingAmount;
  // Registry Info
  @override
  final DateTime? registryDate;
  @override
  final String? registryNumber;
  @override
  final String? registryOffice;
  // Agreement
  @override
  final DateTime? agreementDate;
  @override
  final String? agreementNumber;
  @override
  final String? agreementDocumentUrl;
  // Timestamps
  @override
  final DateTime? createdAt;
  @override
  final DateTime? updatedAt;
  // Notes
  @override
  final String? notes;
  final List<BookingHistory>? _history;
  @override
  List<BookingHistory>? get history {
    final value = _history;
    if (value == null) return null;
    if (_history is EqualUnmodifiableListView) return _history;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  @override
  String toString() {
    return 'BookingModel(id: $id, plotId: $plotId, plotNumber: $plotNumber, colonyId: $colonyId, colonyName: $colonyName, customerId: $customerId, customerName: $customerName, customerPhone: $customerPhone, customerEmail: $customerEmail, customerAddress: $customerAddress, associateId: $associateId, associateName: $associateName, associateRank: $associateRank, associateCommission: $associateCommission, plotPrice: $plotPrice, tokenAmount: $tokenAmount, totalAmount: $totalAmount, paymentPlan: $paymentPlan, downPayment: $downPayment, emiMonths: $emiMonths, emiAmount: $emiAmount, interestRate: $interestRate, status: $status, statusReason: $statusReason, approvedAt: $approvedAt, approvedBy: $approvedBy, completedAt: $completedAt, cancelledAt: $cancelledAt, cancelledReason: $cancelledReason, documents: $documents, payments: $payments, totalPaid: $totalPaid, remainingAmount: $remainingAmount, registryDate: $registryDate, registryNumber: $registryNumber, registryOffice: $registryOffice, agreementDate: $agreementDate, agreementNumber: $agreementNumber, agreementDocumentUrl: $agreementDocumentUrl, createdAt: $createdAt, updatedAt: $updatedAt, notes: $notes, history: $history)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$BookingModelImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.plotId, plotId) || other.plotId == plotId) &&
            (identical(other.plotNumber, plotNumber) ||
                other.plotNumber == plotNumber) &&
            (identical(other.colonyId, colonyId) ||
                other.colonyId == colonyId) &&
            (identical(other.colonyName, colonyName) ||
                other.colonyName == colonyName) &&
            (identical(other.customerId, customerId) ||
                other.customerId == customerId) &&
            (identical(other.customerName, customerName) ||
                other.customerName == customerName) &&
            (identical(other.customerPhone, customerPhone) ||
                other.customerPhone == customerPhone) &&
            (identical(other.customerEmail, customerEmail) ||
                other.customerEmail == customerEmail) &&
            (identical(other.customerAddress, customerAddress) ||
                other.customerAddress == customerAddress) &&
            (identical(other.associateId, associateId) ||
                other.associateId == associateId) &&
            (identical(other.associateName, associateName) ||
                other.associateName == associateName) &&
            (identical(other.associateRank, associateRank) ||
                other.associateRank == associateRank) &&
            (identical(other.associateCommission, associateCommission) ||
                other.associateCommission == associateCommission) &&
            (identical(other.plotPrice, plotPrice) ||
                other.plotPrice == plotPrice) &&
            (identical(other.tokenAmount, tokenAmount) ||
                other.tokenAmount == tokenAmount) &&
            (identical(other.totalAmount, totalAmount) ||
                other.totalAmount == totalAmount) &&
            (identical(other.paymentPlan, paymentPlan) ||
                other.paymentPlan == paymentPlan) &&
            (identical(other.downPayment, downPayment) ||
                other.downPayment == downPayment) &&
            (identical(other.emiMonths, emiMonths) ||
                other.emiMonths == emiMonths) &&
            (identical(other.emiAmount, emiAmount) ||
                other.emiAmount == emiAmount) &&
            (identical(other.interestRate, interestRate) ||
                other.interestRate == interestRate) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.statusReason, statusReason) ||
                other.statusReason == statusReason) &&
            (identical(other.approvedAt, approvedAt) ||
                other.approvedAt == approvedAt) &&
            (identical(other.approvedBy, approvedBy) ||
                other.approvedBy == approvedBy) &&
            (identical(other.completedAt, completedAt) ||
                other.completedAt == completedAt) &&
            (identical(other.cancelledAt, cancelledAt) ||
                other.cancelledAt == cancelledAt) &&
            (identical(other.cancelledReason, cancelledReason) ||
                other.cancelledReason == cancelledReason) &&
            const DeepCollectionEquality().equals(
              other._documents,
              _documents,
            ) &&
            const DeepCollectionEquality().equals(other._payments, _payments) &&
            (identical(other.totalPaid, totalPaid) ||
                other.totalPaid == totalPaid) &&
            (identical(other.remainingAmount, remainingAmount) ||
                other.remainingAmount == remainingAmount) &&
            (identical(other.registryDate, registryDate) ||
                other.registryDate == registryDate) &&
            (identical(other.registryNumber, registryNumber) ||
                other.registryNumber == registryNumber) &&
            (identical(other.registryOffice, registryOffice) ||
                other.registryOffice == registryOffice) &&
            (identical(other.agreementDate, agreementDate) ||
                other.agreementDate == agreementDate) &&
            (identical(other.agreementNumber, agreementNumber) ||
                other.agreementNumber == agreementNumber) &&
            (identical(other.agreementDocumentUrl, agreementDocumentUrl) ||
                other.agreementDocumentUrl == agreementDocumentUrl) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            const DeepCollectionEquality().equals(other._history, _history));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    id,
    plotId,
    plotNumber,
    colonyId,
    colonyName,
    customerId,
    customerName,
    customerPhone,
    customerEmail,
    customerAddress,
    associateId,
    associateName,
    associateRank,
    associateCommission,
    plotPrice,
    tokenAmount,
    totalAmount,
    paymentPlan,
    downPayment,
    emiMonths,
    emiAmount,
    interestRate,
    status,
    statusReason,
    approvedAt,
    approvedBy,
    completedAt,
    cancelledAt,
    cancelledReason,
    const DeepCollectionEquality().hash(_documents),
    const DeepCollectionEquality().hash(_payments),
    totalPaid,
    remainingAmount,
    registryDate,
    registryNumber,
    registryOffice,
    agreementDate,
    agreementNumber,
    agreementDocumentUrl,
    createdAt,
    updatedAt,
    notes,
    const DeepCollectionEquality().hash(_history),
  ]);

  /// Create a copy of BookingModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$BookingModelImplCopyWith<_$BookingModelImpl> get copyWith =>
      __$$BookingModelImplCopyWithImpl<_$BookingModelImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$BookingModelImplToJson(this);
  }
}

abstract class _BookingModel extends BookingModel {
  const factory _BookingModel({
    required final String id,
    required final String plotId,
    required final String plotNumber,
    required final String colonyId,
    required final String colonyName,
    required final String customerId,
    required final String customerName,
    required final String customerPhone,
    final String? customerEmail,
    final String? customerAddress,
    final String? associateId,
    final String? associateName,
    final String? associateRank,
    final double? associateCommission,
    required final double plotPrice,
    required final double tokenAmount,
    required final double totalAmount,
    required final String paymentPlan,
    final double? downPayment,
    final int? emiMonths,
    final double? emiAmount,
    final double? interestRate,
    required final String status,
    final String? statusReason,
    final DateTime? approvedAt,
    final String? approvedBy,
    final DateTime? completedAt,
    final DateTime? cancelledAt,
    final String? cancelledReason,
    final List<BookingDocument>? documents,
    final List<PaymentModel>? payments,
    final double? totalPaid,
    final double? remainingAmount,
    final DateTime? registryDate,
    final String? registryNumber,
    final String? registryOffice,
    final DateTime? agreementDate,
    final String? agreementNumber,
    final String? agreementDocumentUrl,
    final DateTime? createdAt,
    final DateTime? updatedAt,
    final String? notes,
    final List<BookingHistory>? history,
  }) = _$BookingModelImpl;
  const _BookingModel._() : super._();

  factory _BookingModel.fromJson(Map<String, dynamic> json) =
      _$BookingModelImpl.fromJson;

  @override
  String get id;
  @override
  String get plotId;
  @override
  String get plotNumber;
  @override
  String get colonyId;
  @override
  String get colonyName; // Customer Info
  @override
  String get customerId;
  @override
  String get customerName;
  @override
  String get customerPhone;
  @override
  String? get customerEmail;
  @override
  String? get customerAddress; // Associate Info (if booked through associate)
  @override
  String? get associateId;
  @override
  String? get associateName;
  @override
  String? get associateRank;
  @override
  double? get associateCommission; // Pricing
  @override
  double get plotPrice;
  @override
  double get tokenAmount;
  @override
  double get totalAmount;
  @override
  String get paymentPlan; // full, emi, installment
  // EMI Details (if applicable)
  @override
  double? get downPayment;
  @override
  int? get emiMonths;
  @override
  double? get emiAmount;
  @override
  double? get interestRate; // Status
  @override
  String get status; // pending, approved, rejected, completed, cancelled
  @override
  String? get statusReason;
  @override
  DateTime? get approvedAt;
  @override
  String? get approvedBy;
  @override
  DateTime? get completedAt;
  @override
  DateTime? get cancelledAt;
  @override
  String? get cancelledReason; // Documents
  @override
  List<BookingDocument>? get documents; // Payments
  @override
  List<PaymentModel>? get payments;
  @override
  double? get totalPaid;
  @override
  double? get remainingAmount; // Registry Info
  @override
  DateTime? get registryDate;
  @override
  String? get registryNumber;
  @override
  String? get registryOffice; // Agreement
  @override
  DateTime? get agreementDate;
  @override
  String? get agreementNumber;
  @override
  String? get agreementDocumentUrl; // Timestamps
  @override
  DateTime? get createdAt;
  @override
  DateTime? get updatedAt; // Notes
  @override
  String? get notes;
  @override
  List<BookingHistory>? get history;

  /// Create a copy of BookingModel
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$BookingModelImplCopyWith<_$BookingModelImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

BookingDocument _$BookingDocumentFromJson(Map<String, dynamic> json) {
  return _BookingDocument.fromJson(json);
}

/// @nodoc
mixin _$BookingDocument {
  String get id => throw _privateConstructorUsedError;
  String get type =>
      throw _privateConstructorUsedError; // aadhar, pan, photo, agreement, etc.
  String get name => throw _privateConstructorUsedError;
  String get url => throw _privateConstructorUsedError;
  String? get thumbnailUrl => throw _privateConstructorUsedError;
  DateTime? get uploadedAt => throw _privateConstructorUsedError;
  String? get verifiedBy => throw _privateConstructorUsedError;
  DateTime? get verifiedAt => throw _privateConstructorUsedError;
  String? get status =>
      throw _privateConstructorUsedError; // pending, verified, rejected
  String? get notes => throw _privateConstructorUsedError;

  /// Serializes this BookingDocument to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of BookingDocument
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $BookingDocumentCopyWith<BookingDocument> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $BookingDocumentCopyWith<$Res> {
  factory $BookingDocumentCopyWith(
    BookingDocument value,
    $Res Function(BookingDocument) then,
  ) = _$BookingDocumentCopyWithImpl<$Res, BookingDocument>;
  @useResult
  $Res call({
    String id,
    String type,
    String name,
    String url,
    String? thumbnailUrl,
    DateTime? uploadedAt,
    String? verifiedBy,
    DateTime? verifiedAt,
    String? status,
    String? notes,
  });
}

/// @nodoc
class _$BookingDocumentCopyWithImpl<$Res, $Val extends BookingDocument>
    implements $BookingDocumentCopyWith<$Res> {
  _$BookingDocumentCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of BookingDocument
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? name = null,
    Object? url = null,
    Object? thumbnailUrl = freezed,
    Object? uploadedAt = freezed,
    Object? verifiedBy = freezed,
    Object? verifiedAt = freezed,
    Object? status = freezed,
    Object? notes = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            type: null == type
                ? _value.type
                : type // ignore: cast_nullable_to_non_nullable
                      as String,
            name: null == name
                ? _value.name
                : name // ignore: cast_nullable_to_non_nullable
                      as String,
            url: null == url
                ? _value.url
                : url // ignore: cast_nullable_to_non_nullable
                      as String,
            thumbnailUrl: freezed == thumbnailUrl
                ? _value.thumbnailUrl
                : thumbnailUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            uploadedAt: freezed == uploadedAt
                ? _value.uploadedAt
                : uploadedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            verifiedBy: freezed == verifiedBy
                ? _value.verifiedBy
                : verifiedBy // ignore: cast_nullable_to_non_nullable
                      as String?,
            verifiedAt: freezed == verifiedAt
                ? _value.verifiedAt
                : verifiedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            status: freezed == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as String?,
            notes: freezed == notes
                ? _value.notes
                : notes // ignore: cast_nullable_to_non_nullable
                      as String?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$BookingDocumentImplCopyWith<$Res>
    implements $BookingDocumentCopyWith<$Res> {
  factory _$$BookingDocumentImplCopyWith(
    _$BookingDocumentImpl value,
    $Res Function(_$BookingDocumentImpl) then,
  ) = __$$BookingDocumentImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String type,
    String name,
    String url,
    String? thumbnailUrl,
    DateTime? uploadedAt,
    String? verifiedBy,
    DateTime? verifiedAt,
    String? status,
    String? notes,
  });
}

/// @nodoc
class __$$BookingDocumentImplCopyWithImpl<$Res>
    extends _$BookingDocumentCopyWithImpl<$Res, _$BookingDocumentImpl>
    implements _$$BookingDocumentImplCopyWith<$Res> {
  __$$BookingDocumentImplCopyWithImpl(
    _$BookingDocumentImpl _value,
    $Res Function(_$BookingDocumentImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of BookingDocument
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? name = null,
    Object? url = null,
    Object? thumbnailUrl = freezed,
    Object? uploadedAt = freezed,
    Object? verifiedBy = freezed,
    Object? verifiedAt = freezed,
    Object? status = freezed,
    Object? notes = freezed,
  }) {
    return _then(
      _$BookingDocumentImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _value.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _value.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        url: null == url
            ? _value.url
            : url // ignore: cast_nullable_to_non_nullable
                  as String,
        thumbnailUrl: freezed == thumbnailUrl
            ? _value.thumbnailUrl
            : thumbnailUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        uploadedAt: freezed == uploadedAt
            ? _value.uploadedAt
            : uploadedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        verifiedBy: freezed == verifiedBy
            ? _value.verifiedBy
            : verifiedBy // ignore: cast_nullable_to_non_nullable
                  as String?,
        verifiedAt: freezed == verifiedAt
            ? _value.verifiedAt
            : verifiedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        status: freezed == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String?,
        notes: freezed == notes
            ? _value.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$BookingDocumentImpl implements _BookingDocument {
  const _$BookingDocumentImpl({
    required this.id,
    required this.type,
    required this.name,
    required this.url,
    this.thumbnailUrl,
    this.uploadedAt,
    this.verifiedBy,
    this.verifiedAt,
    this.status,
    this.notes,
  });

  factory _$BookingDocumentImpl.fromJson(Map<String, dynamic> json) =>
      _$$BookingDocumentImplFromJson(json);

  @override
  final String id;
  @override
  final String type;
  // aadhar, pan, photo, agreement, etc.
  @override
  final String name;
  @override
  final String url;
  @override
  final String? thumbnailUrl;
  @override
  final DateTime? uploadedAt;
  @override
  final String? verifiedBy;
  @override
  final DateTime? verifiedAt;
  @override
  final String? status;
  // pending, verified, rejected
  @override
  final String? notes;

  @override
  String toString() {
    return 'BookingDocument(id: $id, type: $type, name: $name, url: $url, thumbnailUrl: $thumbnailUrl, uploadedAt: $uploadedAt, verifiedBy: $verifiedBy, verifiedAt: $verifiedAt, status: $status, notes: $notes)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$BookingDocumentImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.url, url) || other.url == url) &&
            (identical(other.thumbnailUrl, thumbnailUrl) ||
                other.thumbnailUrl == thumbnailUrl) &&
            (identical(other.uploadedAt, uploadedAt) ||
                other.uploadedAt == uploadedAt) &&
            (identical(other.verifiedBy, verifiedBy) ||
                other.verifiedBy == verifiedBy) &&
            (identical(other.verifiedAt, verifiedAt) ||
                other.verifiedAt == verifiedAt) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.notes, notes) || other.notes == notes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    type,
    name,
    url,
    thumbnailUrl,
    uploadedAt,
    verifiedBy,
    verifiedAt,
    status,
    notes,
  );

  /// Create a copy of BookingDocument
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$BookingDocumentImplCopyWith<_$BookingDocumentImpl> get copyWith =>
      __$$BookingDocumentImplCopyWithImpl<_$BookingDocumentImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$BookingDocumentImplToJson(this);
  }
}

abstract class _BookingDocument implements BookingDocument {
  const factory _BookingDocument({
    required final String id,
    required final String type,
    required final String name,
    required final String url,
    final String? thumbnailUrl,
    final DateTime? uploadedAt,
    final String? verifiedBy,
    final DateTime? verifiedAt,
    final String? status,
    final String? notes,
  }) = _$BookingDocumentImpl;

  factory _BookingDocument.fromJson(Map<String, dynamic> json) =
      _$BookingDocumentImpl.fromJson;

  @override
  String get id;
  @override
  String get type; // aadhar, pan, photo, agreement, etc.
  @override
  String get name;
  @override
  String get url;
  @override
  String? get thumbnailUrl;
  @override
  DateTime? get uploadedAt;
  @override
  String? get verifiedBy;
  @override
  DateTime? get verifiedAt;
  @override
  String? get status; // pending, verified, rejected
  @override
  String? get notes;

  /// Create a copy of BookingDocument
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$BookingDocumentImplCopyWith<_$BookingDocumentImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

PaymentModel _$PaymentModelFromJson(Map<String, dynamic> json) {
  return _PaymentModel.fromJson(json);
}

/// @nodoc
mixin _$PaymentModel {
  String get id => throw _privateConstructorUsedError;
  String get bookingId => throw _privateConstructorUsedError;
  double get amount => throw _privateConstructorUsedError;
  String get type =>
      throw _privateConstructorUsedError; // token, down_payment, installment, registry, full
  String get method =>
      throw _privateConstructorUsedError; // cash, cheque, bank_transfer, upi, razorpay
  String? get transactionId => throw _privateConstructorUsedError;
  String? get razorpayOrderId => throw _privateConstructorUsedError;
  String? get razorpayPaymentId => throw _privateConstructorUsedError;
  DateTime? get paidAt => throw _privateConstructorUsedError;
  String? get paidBy => throw _privateConstructorUsedError;
  String? get receivedBy => throw _privateConstructorUsedError;
  String? get status =>
      throw _privateConstructorUsedError; // pending, completed, failed, refunded
  String? get notes => throw _privateConstructorUsedError;
  String? get receiptUrl => throw _privateConstructorUsedError;
  DateTime? get createdAt => throw _privateConstructorUsedError;

  /// Serializes this PaymentModel to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of PaymentModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $PaymentModelCopyWith<PaymentModel> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $PaymentModelCopyWith<$Res> {
  factory $PaymentModelCopyWith(
    PaymentModel value,
    $Res Function(PaymentModel) then,
  ) = _$PaymentModelCopyWithImpl<$Res, PaymentModel>;
  @useResult
  $Res call({
    String id,
    String bookingId,
    double amount,
    String type,
    String method,
    String? transactionId,
    String? razorpayOrderId,
    String? razorpayPaymentId,
    DateTime? paidAt,
    String? paidBy,
    String? receivedBy,
    String? status,
    String? notes,
    String? receiptUrl,
    DateTime? createdAt,
  });
}

/// @nodoc
class _$PaymentModelCopyWithImpl<$Res, $Val extends PaymentModel>
    implements $PaymentModelCopyWith<$Res> {
  _$PaymentModelCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of PaymentModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? bookingId = null,
    Object? amount = null,
    Object? type = null,
    Object? method = null,
    Object? transactionId = freezed,
    Object? razorpayOrderId = freezed,
    Object? razorpayPaymentId = freezed,
    Object? paidAt = freezed,
    Object? paidBy = freezed,
    Object? receivedBy = freezed,
    Object? status = freezed,
    Object? notes = freezed,
    Object? receiptUrl = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            bookingId: null == bookingId
                ? _value.bookingId
                : bookingId // ignore: cast_nullable_to_non_nullable
                      as String,
            amount: null == amount
                ? _value.amount
                : amount // ignore: cast_nullable_to_non_nullable
                      as double,
            type: null == type
                ? _value.type
                : type // ignore: cast_nullable_to_non_nullable
                      as String,
            method: null == method
                ? _value.method
                : method // ignore: cast_nullable_to_non_nullable
                      as String,
            transactionId: freezed == transactionId
                ? _value.transactionId
                : transactionId // ignore: cast_nullable_to_non_nullable
                      as String?,
            razorpayOrderId: freezed == razorpayOrderId
                ? _value.razorpayOrderId
                : razorpayOrderId // ignore: cast_nullable_to_non_nullable
                      as String?,
            razorpayPaymentId: freezed == razorpayPaymentId
                ? _value.razorpayPaymentId
                : razorpayPaymentId // ignore: cast_nullable_to_non_nullable
                      as String?,
            paidAt: freezed == paidAt
                ? _value.paidAt
                : paidAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            paidBy: freezed == paidBy
                ? _value.paidBy
                : paidBy // ignore: cast_nullable_to_non_nullable
                      as String?,
            receivedBy: freezed == receivedBy
                ? _value.receivedBy
                : receivedBy // ignore: cast_nullable_to_non_nullable
                      as String?,
            status: freezed == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as String?,
            notes: freezed == notes
                ? _value.notes
                : notes // ignore: cast_nullable_to_non_nullable
                      as String?,
            receiptUrl: freezed == receiptUrl
                ? _value.receiptUrl
                : receiptUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            createdAt: freezed == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$PaymentModelImplCopyWith<$Res>
    implements $PaymentModelCopyWith<$Res> {
  factory _$$PaymentModelImplCopyWith(
    _$PaymentModelImpl value,
    $Res Function(_$PaymentModelImpl) then,
  ) = __$$PaymentModelImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String bookingId,
    double amount,
    String type,
    String method,
    String? transactionId,
    String? razorpayOrderId,
    String? razorpayPaymentId,
    DateTime? paidAt,
    String? paidBy,
    String? receivedBy,
    String? status,
    String? notes,
    String? receiptUrl,
    DateTime? createdAt,
  });
}

/// @nodoc
class __$$PaymentModelImplCopyWithImpl<$Res>
    extends _$PaymentModelCopyWithImpl<$Res, _$PaymentModelImpl>
    implements _$$PaymentModelImplCopyWith<$Res> {
  __$$PaymentModelImplCopyWithImpl(
    _$PaymentModelImpl _value,
    $Res Function(_$PaymentModelImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of PaymentModel
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? bookingId = null,
    Object? amount = null,
    Object? type = null,
    Object? method = null,
    Object? transactionId = freezed,
    Object? razorpayOrderId = freezed,
    Object? razorpayPaymentId = freezed,
    Object? paidAt = freezed,
    Object? paidBy = freezed,
    Object? receivedBy = freezed,
    Object? status = freezed,
    Object? notes = freezed,
    Object? receiptUrl = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _$PaymentModelImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        bookingId: null == bookingId
            ? _value.bookingId
            : bookingId // ignore: cast_nullable_to_non_nullable
                  as String,
        amount: null == amount
            ? _value.amount
            : amount // ignore: cast_nullable_to_non_nullable
                  as double,
        type: null == type
            ? _value.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        method: null == method
            ? _value.method
            : method // ignore: cast_nullable_to_non_nullable
                  as String,
        transactionId: freezed == transactionId
            ? _value.transactionId
            : transactionId // ignore: cast_nullable_to_non_nullable
                  as String?,
        razorpayOrderId: freezed == razorpayOrderId
            ? _value.razorpayOrderId
            : razorpayOrderId // ignore: cast_nullable_to_non_nullable
                  as String?,
        razorpayPaymentId: freezed == razorpayPaymentId
            ? _value.razorpayPaymentId
            : razorpayPaymentId // ignore: cast_nullable_to_non_nullable
                  as String?,
        paidAt: freezed == paidAt
            ? _value.paidAt
            : paidAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        paidBy: freezed == paidBy
            ? _value.paidBy
            : paidBy // ignore: cast_nullable_to_non_nullable
                  as String?,
        receivedBy: freezed == receivedBy
            ? _value.receivedBy
            : receivedBy // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: freezed == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String?,
        notes: freezed == notes
            ? _value.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        receiptUrl: freezed == receiptUrl
            ? _value.receiptUrl
            : receiptUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        createdAt: freezed == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$PaymentModelImpl implements _PaymentModel {
  const _$PaymentModelImpl({
    required this.id,
    required this.bookingId,
    required this.amount,
    required this.type,
    required this.method,
    this.transactionId,
    this.razorpayOrderId,
    this.razorpayPaymentId,
    this.paidAt,
    this.paidBy,
    this.receivedBy,
    this.status,
    this.notes,
    this.receiptUrl,
    this.createdAt,
  });

  factory _$PaymentModelImpl.fromJson(Map<String, dynamic> json) =>
      _$$PaymentModelImplFromJson(json);

  @override
  final String id;
  @override
  final String bookingId;
  @override
  final double amount;
  @override
  final String type;
  // token, down_payment, installment, registry, full
  @override
  final String method;
  // cash, cheque, bank_transfer, upi, razorpay
  @override
  final String? transactionId;
  @override
  final String? razorpayOrderId;
  @override
  final String? razorpayPaymentId;
  @override
  final DateTime? paidAt;
  @override
  final String? paidBy;
  @override
  final String? receivedBy;
  @override
  final String? status;
  // pending, completed, failed, refunded
  @override
  final String? notes;
  @override
  final String? receiptUrl;
  @override
  final DateTime? createdAt;

  @override
  String toString() {
    return 'PaymentModel(id: $id, bookingId: $bookingId, amount: $amount, type: $type, method: $method, transactionId: $transactionId, razorpayOrderId: $razorpayOrderId, razorpayPaymentId: $razorpayPaymentId, paidAt: $paidAt, paidBy: $paidBy, receivedBy: $receivedBy, status: $status, notes: $notes, receiptUrl: $receiptUrl, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$PaymentModelImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.bookingId, bookingId) ||
                other.bookingId == bookingId) &&
            (identical(other.amount, amount) || other.amount == amount) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.method, method) || other.method == method) &&
            (identical(other.transactionId, transactionId) ||
                other.transactionId == transactionId) &&
            (identical(other.razorpayOrderId, razorpayOrderId) ||
                other.razorpayOrderId == razorpayOrderId) &&
            (identical(other.razorpayPaymentId, razorpayPaymentId) ||
                other.razorpayPaymentId == razorpayPaymentId) &&
            (identical(other.paidAt, paidAt) || other.paidAt == paidAt) &&
            (identical(other.paidBy, paidBy) || other.paidBy == paidBy) &&
            (identical(other.receivedBy, receivedBy) ||
                other.receivedBy == receivedBy) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            (identical(other.receiptUrl, receiptUrl) ||
                other.receiptUrl == receiptUrl) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    bookingId,
    amount,
    type,
    method,
    transactionId,
    razorpayOrderId,
    razorpayPaymentId,
    paidAt,
    paidBy,
    receivedBy,
    status,
    notes,
    receiptUrl,
    createdAt,
  );

  /// Create a copy of PaymentModel
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$PaymentModelImplCopyWith<_$PaymentModelImpl> get copyWith =>
      __$$PaymentModelImplCopyWithImpl<_$PaymentModelImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$PaymentModelImplToJson(this);
  }
}

abstract class _PaymentModel implements PaymentModel {
  const factory _PaymentModel({
    required final String id,
    required final String bookingId,
    required final double amount,
    required final String type,
    required final String method,
    final String? transactionId,
    final String? razorpayOrderId,
    final String? razorpayPaymentId,
    final DateTime? paidAt,
    final String? paidBy,
    final String? receivedBy,
    final String? status,
    final String? notes,
    final String? receiptUrl,
    final DateTime? createdAt,
  }) = _$PaymentModelImpl;

  factory _PaymentModel.fromJson(Map<String, dynamic> json) =
      _$PaymentModelImpl.fromJson;

  @override
  String get id;
  @override
  String get bookingId;
  @override
  double get amount;
  @override
  String get type; // token, down_payment, installment, registry, full
  @override
  String get method; // cash, cheque, bank_transfer, upi, razorpay
  @override
  String? get transactionId;
  @override
  String? get razorpayOrderId;
  @override
  String? get razorpayPaymentId;
  @override
  DateTime? get paidAt;
  @override
  String? get paidBy;
  @override
  String? get receivedBy;
  @override
  String? get status; // pending, completed, failed, refunded
  @override
  String? get notes;
  @override
  String? get receiptUrl;
  @override
  DateTime? get createdAt;

  /// Create a copy of PaymentModel
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$PaymentModelImplCopyWith<_$PaymentModelImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

BookingHistory _$BookingHistoryFromJson(Map<String, dynamic> json) {
  return _BookingHistory.fromJson(json);
}

/// @nodoc
mixin _$BookingHistory {
  String get id => throw _privateConstructorUsedError;
  String get action => throw _privateConstructorUsedError;
  String get performedBy => throw _privateConstructorUsedError;
  DateTime get performedAt => throw _privateConstructorUsedError;
  String? get notes => throw _privateConstructorUsedError;
  Map<String, dynamic>? get oldValues => throw _privateConstructorUsedError;
  Map<String, dynamic>? get newValues => throw _privateConstructorUsedError;

  /// Serializes this BookingHistory to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of BookingHistory
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $BookingHistoryCopyWith<BookingHistory> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $BookingHistoryCopyWith<$Res> {
  factory $BookingHistoryCopyWith(
    BookingHistory value,
    $Res Function(BookingHistory) then,
  ) = _$BookingHistoryCopyWithImpl<$Res, BookingHistory>;
  @useResult
  $Res call({
    String id,
    String action,
    String performedBy,
    DateTime performedAt,
    String? notes,
    Map<String, dynamic>? oldValues,
    Map<String, dynamic>? newValues,
  });
}

/// @nodoc
class _$BookingHistoryCopyWithImpl<$Res, $Val extends BookingHistory>
    implements $BookingHistoryCopyWith<$Res> {
  _$BookingHistoryCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of BookingHistory
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? action = null,
    Object? performedBy = null,
    Object? performedAt = null,
    Object? notes = freezed,
    Object? oldValues = freezed,
    Object? newValues = freezed,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            action: null == action
                ? _value.action
                : action // ignore: cast_nullable_to_non_nullable
                      as String,
            performedBy: null == performedBy
                ? _value.performedBy
                : performedBy // ignore: cast_nullable_to_non_nullable
                      as String,
            performedAt: null == performedAt
                ? _value.performedAt
                : performedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            notes: freezed == notes
                ? _value.notes
                : notes // ignore: cast_nullable_to_non_nullable
                      as String?,
            oldValues: freezed == oldValues
                ? _value.oldValues
                : oldValues // ignore: cast_nullable_to_non_nullable
                      as Map<String, dynamic>?,
            newValues: freezed == newValues
                ? _value.newValues
                : newValues // ignore: cast_nullable_to_non_nullable
                      as Map<String, dynamic>?,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$BookingHistoryImplCopyWith<$Res>
    implements $BookingHistoryCopyWith<$Res> {
  factory _$$BookingHistoryImplCopyWith(
    _$BookingHistoryImpl value,
    $Res Function(_$BookingHistoryImpl) then,
  ) = __$$BookingHistoryImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String action,
    String performedBy,
    DateTime performedAt,
    String? notes,
    Map<String, dynamic>? oldValues,
    Map<String, dynamic>? newValues,
  });
}

/// @nodoc
class __$$BookingHistoryImplCopyWithImpl<$Res>
    extends _$BookingHistoryCopyWithImpl<$Res, _$BookingHistoryImpl>
    implements _$$BookingHistoryImplCopyWith<$Res> {
  __$$BookingHistoryImplCopyWithImpl(
    _$BookingHistoryImpl _value,
    $Res Function(_$BookingHistoryImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of BookingHistory
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? action = null,
    Object? performedBy = null,
    Object? performedAt = null,
    Object? notes = freezed,
    Object? oldValues = freezed,
    Object? newValues = freezed,
  }) {
    return _then(
      _$BookingHistoryImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        action: null == action
            ? _value.action
            : action // ignore: cast_nullable_to_non_nullable
                  as String,
        performedBy: null == performedBy
            ? _value.performedBy
            : performedBy // ignore: cast_nullable_to_non_nullable
                  as String,
        performedAt: null == performedAt
            ? _value.performedAt
            : performedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        notes: freezed == notes
            ? _value.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        oldValues: freezed == oldValues
            ? _value._oldValues
            : oldValues // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>?,
        newValues: freezed == newValues
            ? _value._newValues
            : newValues // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>?,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$BookingHistoryImpl implements _BookingHistory {
  const _$BookingHistoryImpl({
    required this.id,
    required this.action,
    required this.performedBy,
    required this.performedAt,
    this.notes,
    final Map<String, dynamic>? oldValues,
    final Map<String, dynamic>? newValues,
  }) : _oldValues = oldValues,
       _newValues = newValues;

  factory _$BookingHistoryImpl.fromJson(Map<String, dynamic> json) =>
      _$$BookingHistoryImplFromJson(json);

  @override
  final String id;
  @override
  final String action;
  @override
  final String performedBy;
  @override
  final DateTime performedAt;
  @override
  final String? notes;
  final Map<String, dynamic>? _oldValues;
  @override
  Map<String, dynamic>? get oldValues {
    final value = _oldValues;
    if (value == null) return null;
    if (_oldValues is EqualUnmodifiableMapView) return _oldValues;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(value);
  }

  final Map<String, dynamic>? _newValues;
  @override
  Map<String, dynamic>? get newValues {
    final value = _newValues;
    if (value == null) return null;
    if (_newValues is EqualUnmodifiableMapView) return _newValues;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(value);
  }

  @override
  String toString() {
    return 'BookingHistory(id: $id, action: $action, performedBy: $performedBy, performedAt: $performedAt, notes: $notes, oldValues: $oldValues, newValues: $newValues)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$BookingHistoryImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.action, action) || other.action == action) &&
            (identical(other.performedBy, performedBy) ||
                other.performedBy == performedBy) &&
            (identical(other.performedAt, performedAt) ||
                other.performedAt == performedAt) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            const DeepCollectionEquality().equals(
              other._oldValues,
              _oldValues,
            ) &&
            const DeepCollectionEquality().equals(
              other._newValues,
              _newValues,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    action,
    performedBy,
    performedAt,
    notes,
    const DeepCollectionEquality().hash(_oldValues),
    const DeepCollectionEquality().hash(_newValues),
  );

  /// Create a copy of BookingHistory
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$BookingHistoryImplCopyWith<_$BookingHistoryImpl> get copyWith =>
      __$$BookingHistoryImplCopyWithImpl<_$BookingHistoryImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$BookingHistoryImplToJson(this);
  }
}

abstract class _BookingHistory implements BookingHistory {
  const factory _BookingHistory({
    required final String id,
    required final String action,
    required final String performedBy,
    required final DateTime performedAt,
    final String? notes,
    final Map<String, dynamic>? oldValues,
    final Map<String, dynamic>? newValues,
  }) = _$BookingHistoryImpl;

  factory _BookingHistory.fromJson(Map<String, dynamic> json) =
      _$BookingHistoryImpl.fromJson;

  @override
  String get id;
  @override
  String get action;
  @override
  String get performedBy;
  @override
  DateTime get performedAt;
  @override
  String? get notes;
  @override
  Map<String, dynamic>? get oldValues;
  @override
  Map<String, dynamic>? get newValues;

  /// Create a copy of BookingHistory
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$BookingHistoryImplCopyWith<_$BookingHistoryImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
