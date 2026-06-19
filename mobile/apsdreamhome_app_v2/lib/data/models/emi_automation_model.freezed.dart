// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'emi_automation_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
  'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models',
);

EMIAutomationConfig _$EMIAutomationConfigFromJson(Map<String, dynamic> json) {
  return _EMIAutomationConfig.fromJson(json);
}

/// @nodoc
mixin _$EMIAutomationConfig {
  String get id => throw _privateConstructorUsedError;
  String get companyId => throw _privateConstructorUsedError;
  String get companyName =>
      throw _privateConstructorUsedError; // WhatsApp Business Configuration
  WhatsAppConfig get whatsappConfig =>
      throw _privateConstructorUsedError; // Voice Call Configuration (IVR/Cloud telephony)
  VoiceCallConfig get voiceCallConfig =>
      throw _privateConstructorUsedError; // SMS Gateway Configuration
  SMSConfig get smsConfig =>
      throw _privateConstructorUsedError; // Email Configuration
  EmailConfig get emailConfig =>
      throw _privateConstructorUsedError; // Automation Rules
  List<AutomationRule> get reminderRules => throw _privateConstructorUsedError;
  List<AutomationRule> get escalationRules =>
      throw _privateConstructorUsedError;
  List<AutomationRule> get collectionRules =>
      throw _privateConstructorUsedError; // Field Agent Settings
  FieldAgentConfig get fieldAgentConfig =>
      throw _privateConstructorUsedError; // AI/ML Settings
  AIConfig get aiConfig => throw _privateConstructorUsedError;
  bool get isActive => throw _privateConstructorUsedError;
  DateTime get createdAt => throw _privateConstructorUsedError;
  DateTime get updatedAt => throw _privateConstructorUsedError;

  /// Serializes this EMIAutomationConfig to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $EMIAutomationConfigCopyWith<EMIAutomationConfig> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $EMIAutomationConfigCopyWith<$Res> {
  factory $EMIAutomationConfigCopyWith(
    EMIAutomationConfig value,
    $Res Function(EMIAutomationConfig) then,
  ) = _$EMIAutomationConfigCopyWithImpl<$Res, EMIAutomationConfig>;
  @useResult
  $Res call({
    String id,
    String companyId,
    String companyName,
    WhatsAppConfig whatsappConfig,
    VoiceCallConfig voiceCallConfig,
    SMSConfig smsConfig,
    EmailConfig emailConfig,
    List<AutomationRule> reminderRules,
    List<AutomationRule> escalationRules,
    List<AutomationRule> collectionRules,
    FieldAgentConfig fieldAgentConfig,
    AIConfig aiConfig,
    bool isActive,
    DateTime createdAt,
    DateTime updatedAt,
  });

  $WhatsAppConfigCopyWith<$Res> get whatsappConfig;
  $VoiceCallConfigCopyWith<$Res> get voiceCallConfig;
  $SMSConfigCopyWith<$Res> get smsConfig;
  $EmailConfigCopyWith<$Res> get emailConfig;
  $FieldAgentConfigCopyWith<$Res> get fieldAgentConfig;
  $AIConfigCopyWith<$Res> get aiConfig;
}

/// @nodoc
class _$EMIAutomationConfigCopyWithImpl<$Res, $Val extends EMIAutomationConfig>
    implements $EMIAutomationConfigCopyWith<$Res> {
  _$EMIAutomationConfigCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? companyId = null,
    Object? companyName = null,
    Object? whatsappConfig = null,
    Object? voiceCallConfig = null,
    Object? smsConfig = null,
    Object? emailConfig = null,
    Object? reminderRules = null,
    Object? escalationRules = null,
    Object? collectionRules = null,
    Object? fieldAgentConfig = null,
    Object? aiConfig = null,
    Object? isActive = null,
    Object? createdAt = null,
    Object? updatedAt = null,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            companyId: null == companyId
                ? _value.companyId
                : companyId // ignore: cast_nullable_to_non_nullable
                      as String,
            companyName: null == companyName
                ? _value.companyName
                : companyName // ignore: cast_nullable_to_non_nullable
                      as String,
            whatsappConfig: null == whatsappConfig
                ? _value.whatsappConfig
                : whatsappConfig // ignore: cast_nullable_to_non_nullable
                      as WhatsAppConfig,
            voiceCallConfig: null == voiceCallConfig
                ? _value.voiceCallConfig
                : voiceCallConfig // ignore: cast_nullable_to_non_nullable
                      as VoiceCallConfig,
            smsConfig: null == smsConfig
                ? _value.smsConfig
                : smsConfig // ignore: cast_nullable_to_non_nullable
                      as SMSConfig,
            emailConfig: null == emailConfig
                ? _value.emailConfig
                : emailConfig // ignore: cast_nullable_to_non_nullable
                      as EmailConfig,
            reminderRules: null == reminderRules
                ? _value.reminderRules
                : reminderRules // ignore: cast_nullable_to_non_nullable
                      as List<AutomationRule>,
            escalationRules: null == escalationRules
                ? _value.escalationRules
                : escalationRules // ignore: cast_nullable_to_non_nullable
                      as List<AutomationRule>,
            collectionRules: null == collectionRules
                ? _value.collectionRules
                : collectionRules // ignore: cast_nullable_to_non_nullable
                      as List<AutomationRule>,
            fieldAgentConfig: null == fieldAgentConfig
                ? _value.fieldAgentConfig
                : fieldAgentConfig // ignore: cast_nullable_to_non_nullable
                      as FieldAgentConfig,
            aiConfig: null == aiConfig
                ? _value.aiConfig
                : aiConfig // ignore: cast_nullable_to_non_nullable
                      as AIConfig,
            isActive: null == isActive
                ? _value.isActive
                : isActive // ignore: cast_nullable_to_non_nullable
                      as bool,
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

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $WhatsAppConfigCopyWith<$Res> get whatsappConfig {
    return $WhatsAppConfigCopyWith<$Res>(_value.whatsappConfig, (value) {
      return _then(_value.copyWith(whatsappConfig: value) as $Val);
    });
  }

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $VoiceCallConfigCopyWith<$Res> get voiceCallConfig {
    return $VoiceCallConfigCopyWith<$Res>(_value.voiceCallConfig, (value) {
      return _then(_value.copyWith(voiceCallConfig: value) as $Val);
    });
  }

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $SMSConfigCopyWith<$Res> get smsConfig {
    return $SMSConfigCopyWith<$Res>(_value.smsConfig, (value) {
      return _then(_value.copyWith(smsConfig: value) as $Val);
    });
  }

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $EmailConfigCopyWith<$Res> get emailConfig {
    return $EmailConfigCopyWith<$Res>(_value.emailConfig, (value) {
      return _then(_value.copyWith(emailConfig: value) as $Val);
    });
  }

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $FieldAgentConfigCopyWith<$Res> get fieldAgentConfig {
    return $FieldAgentConfigCopyWith<$Res>(_value.fieldAgentConfig, (value) {
      return _then(_value.copyWith(fieldAgentConfig: value) as $Val);
    });
  }

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $AIConfigCopyWith<$Res> get aiConfig {
    return $AIConfigCopyWith<$Res>(_value.aiConfig, (value) {
      return _then(_value.copyWith(aiConfig: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$EMIAutomationConfigImplCopyWith<$Res>
    implements $EMIAutomationConfigCopyWith<$Res> {
  factory _$$EMIAutomationConfigImplCopyWith(
    _$EMIAutomationConfigImpl value,
    $Res Function(_$EMIAutomationConfigImpl) then,
  ) = __$$EMIAutomationConfigImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String companyId,
    String companyName,
    WhatsAppConfig whatsappConfig,
    VoiceCallConfig voiceCallConfig,
    SMSConfig smsConfig,
    EmailConfig emailConfig,
    List<AutomationRule> reminderRules,
    List<AutomationRule> escalationRules,
    List<AutomationRule> collectionRules,
    FieldAgentConfig fieldAgentConfig,
    AIConfig aiConfig,
    bool isActive,
    DateTime createdAt,
    DateTime updatedAt,
  });

  @override
  $WhatsAppConfigCopyWith<$Res> get whatsappConfig;
  @override
  $VoiceCallConfigCopyWith<$Res> get voiceCallConfig;
  @override
  $SMSConfigCopyWith<$Res> get smsConfig;
  @override
  $EmailConfigCopyWith<$Res> get emailConfig;
  @override
  $FieldAgentConfigCopyWith<$Res> get fieldAgentConfig;
  @override
  $AIConfigCopyWith<$Res> get aiConfig;
}

/// @nodoc
class __$$EMIAutomationConfigImplCopyWithImpl<$Res>
    extends _$EMIAutomationConfigCopyWithImpl<$Res, _$EMIAutomationConfigImpl>
    implements _$$EMIAutomationConfigImplCopyWith<$Res> {
  __$$EMIAutomationConfigImplCopyWithImpl(
    _$EMIAutomationConfigImpl _value,
    $Res Function(_$EMIAutomationConfigImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? companyId = null,
    Object? companyName = null,
    Object? whatsappConfig = null,
    Object? voiceCallConfig = null,
    Object? smsConfig = null,
    Object? emailConfig = null,
    Object? reminderRules = null,
    Object? escalationRules = null,
    Object? collectionRules = null,
    Object? fieldAgentConfig = null,
    Object? aiConfig = null,
    Object? isActive = null,
    Object? createdAt = null,
    Object? updatedAt = null,
  }) {
    return _then(
      _$EMIAutomationConfigImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        companyId: null == companyId
            ? _value.companyId
            : companyId // ignore: cast_nullable_to_non_nullable
                  as String,
        companyName: null == companyName
            ? _value.companyName
            : companyName // ignore: cast_nullable_to_non_nullable
                  as String,
        whatsappConfig: null == whatsappConfig
            ? _value.whatsappConfig
            : whatsappConfig // ignore: cast_nullable_to_non_nullable
                  as WhatsAppConfig,
        voiceCallConfig: null == voiceCallConfig
            ? _value.voiceCallConfig
            : voiceCallConfig // ignore: cast_nullable_to_non_nullable
                  as VoiceCallConfig,
        smsConfig: null == smsConfig
            ? _value.smsConfig
            : smsConfig // ignore: cast_nullable_to_non_nullable
                  as SMSConfig,
        emailConfig: null == emailConfig
            ? _value.emailConfig
            : emailConfig // ignore: cast_nullable_to_non_nullable
                  as EmailConfig,
        reminderRules: null == reminderRules
            ? _value._reminderRules
            : reminderRules // ignore: cast_nullable_to_non_nullable
                  as List<AutomationRule>,
        escalationRules: null == escalationRules
            ? _value._escalationRules
            : escalationRules // ignore: cast_nullable_to_non_nullable
                  as List<AutomationRule>,
        collectionRules: null == collectionRules
            ? _value._collectionRules
            : collectionRules // ignore: cast_nullable_to_non_nullable
                  as List<AutomationRule>,
        fieldAgentConfig: null == fieldAgentConfig
            ? _value.fieldAgentConfig
            : fieldAgentConfig // ignore: cast_nullable_to_non_nullable
                  as FieldAgentConfig,
        aiConfig: null == aiConfig
            ? _value.aiConfig
            : aiConfig // ignore: cast_nullable_to_non_nullable
                  as AIConfig,
        isActive: null == isActive
            ? _value.isActive
            : isActive // ignore: cast_nullable_to_non_nullable
                  as bool,
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
class _$EMIAutomationConfigImpl implements _EMIAutomationConfig {
  const _$EMIAutomationConfigImpl({
    required this.id,
    required this.companyId,
    required this.companyName,
    required this.whatsappConfig,
    required this.voiceCallConfig,
    required this.smsConfig,
    required this.emailConfig,
    final List<AutomationRule> reminderRules = const [],
    final List<AutomationRule> escalationRules = const [],
    final List<AutomationRule> collectionRules = const [],
    required this.fieldAgentConfig,
    required this.aiConfig,
    required this.isActive,
    required this.createdAt,
    required this.updatedAt,
  }) : _reminderRules = reminderRules,
       _escalationRules = escalationRules,
       _collectionRules = collectionRules;

  factory _$EMIAutomationConfigImpl.fromJson(Map<String, dynamic> json) =>
      _$$EMIAutomationConfigImplFromJson(json);

  @override
  final String id;
  @override
  final String companyId;
  @override
  final String companyName;
  // WhatsApp Business Configuration
  @override
  final WhatsAppConfig whatsappConfig;
  // Voice Call Configuration (IVR/Cloud telephony)
  @override
  final VoiceCallConfig voiceCallConfig;
  // SMS Gateway Configuration
  @override
  final SMSConfig smsConfig;
  // Email Configuration
  @override
  final EmailConfig emailConfig;
  // Automation Rules
  final List<AutomationRule> _reminderRules;
  // Automation Rules
  @override
  @JsonKey()
  List<AutomationRule> get reminderRules {
    if (_reminderRules is EqualUnmodifiableListView) return _reminderRules;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_reminderRules);
  }

  final List<AutomationRule> _escalationRules;
  @override
  @JsonKey()
  List<AutomationRule> get escalationRules {
    if (_escalationRules is EqualUnmodifiableListView) return _escalationRules;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_escalationRules);
  }

  final List<AutomationRule> _collectionRules;
  @override
  @JsonKey()
  List<AutomationRule> get collectionRules {
    if (_collectionRules is EqualUnmodifiableListView) return _collectionRules;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_collectionRules);
  }

  // Field Agent Settings
  @override
  final FieldAgentConfig fieldAgentConfig;
  // AI/ML Settings
  @override
  final AIConfig aiConfig;
  @override
  final bool isActive;
  @override
  final DateTime createdAt;
  @override
  final DateTime updatedAt;

  @override
  String toString() {
    return 'EMIAutomationConfig(id: $id, companyId: $companyId, companyName: $companyName, whatsappConfig: $whatsappConfig, voiceCallConfig: $voiceCallConfig, smsConfig: $smsConfig, emailConfig: $emailConfig, reminderRules: $reminderRules, escalationRules: $escalationRules, collectionRules: $collectionRules, fieldAgentConfig: $fieldAgentConfig, aiConfig: $aiConfig, isActive: $isActive, createdAt: $createdAt, updatedAt: $updatedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$EMIAutomationConfigImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.companyId, companyId) ||
                other.companyId == companyId) &&
            (identical(other.companyName, companyName) ||
                other.companyName == companyName) &&
            (identical(other.whatsappConfig, whatsappConfig) ||
                other.whatsappConfig == whatsappConfig) &&
            (identical(other.voiceCallConfig, voiceCallConfig) ||
                other.voiceCallConfig == voiceCallConfig) &&
            (identical(other.smsConfig, smsConfig) ||
                other.smsConfig == smsConfig) &&
            (identical(other.emailConfig, emailConfig) ||
                other.emailConfig == emailConfig) &&
            const DeepCollectionEquality().equals(
              other._reminderRules,
              _reminderRules,
            ) &&
            const DeepCollectionEquality().equals(
              other._escalationRules,
              _escalationRules,
            ) &&
            const DeepCollectionEquality().equals(
              other._collectionRules,
              _collectionRules,
            ) &&
            (identical(other.fieldAgentConfig, fieldAgentConfig) ||
                other.fieldAgentConfig == fieldAgentConfig) &&
            (identical(other.aiConfig, aiConfig) ||
                other.aiConfig == aiConfig) &&
            (identical(other.isActive, isActive) ||
                other.isActive == isActive) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    companyId,
    companyName,
    whatsappConfig,
    voiceCallConfig,
    smsConfig,
    emailConfig,
    const DeepCollectionEquality().hash(_reminderRules),
    const DeepCollectionEquality().hash(_escalationRules),
    const DeepCollectionEquality().hash(_collectionRules),
    fieldAgentConfig,
    aiConfig,
    isActive,
    createdAt,
    updatedAt,
  );

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$EMIAutomationConfigImplCopyWith<_$EMIAutomationConfigImpl> get copyWith =>
      __$$EMIAutomationConfigImplCopyWithImpl<_$EMIAutomationConfigImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$EMIAutomationConfigImplToJson(this);
  }
}

abstract class _EMIAutomationConfig implements EMIAutomationConfig {
  const factory _EMIAutomationConfig({
    required final String id,
    required final String companyId,
    required final String companyName,
    required final WhatsAppConfig whatsappConfig,
    required final VoiceCallConfig voiceCallConfig,
    required final SMSConfig smsConfig,
    required final EmailConfig emailConfig,
    final List<AutomationRule> reminderRules,
    final List<AutomationRule> escalationRules,
    final List<AutomationRule> collectionRules,
    required final FieldAgentConfig fieldAgentConfig,
    required final AIConfig aiConfig,
    required final bool isActive,
    required final DateTime createdAt,
    required final DateTime updatedAt,
  }) = _$EMIAutomationConfigImpl;

  factory _EMIAutomationConfig.fromJson(Map<String, dynamic> json) =
      _$EMIAutomationConfigImpl.fromJson;

  @override
  String get id;
  @override
  String get companyId;
  @override
  String get companyName; // WhatsApp Business Configuration
  @override
  WhatsAppConfig get whatsappConfig; // Voice Call Configuration (IVR/Cloud telephony)
  @override
  VoiceCallConfig get voiceCallConfig; // SMS Gateway Configuration
  @override
  SMSConfig get smsConfig; // Email Configuration
  @override
  EmailConfig get emailConfig; // Automation Rules
  @override
  List<AutomationRule> get reminderRules;
  @override
  List<AutomationRule> get escalationRules;
  @override
  List<AutomationRule> get collectionRules; // Field Agent Settings
  @override
  FieldAgentConfig get fieldAgentConfig; // AI/ML Settings
  @override
  AIConfig get aiConfig;
  @override
  bool get isActive;
  @override
  DateTime get createdAt;
  @override
  DateTime get updatedAt;

  /// Create a copy of EMIAutomationConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$EMIAutomationConfigImplCopyWith<_$EMIAutomationConfigImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

WhatsAppConfig _$WhatsAppConfigFromJson(Map<String, dynamic> json) {
  return _WhatsAppConfig.fromJson(json);
}

/// @nodoc
mixin _$WhatsAppConfig {
  bool get isEnabled => throw _privateConstructorUsedError;
  String? get businessAccountId => throw _privateConstructorUsedError;
  String? get phoneNumberId => throw _privateConstructorUsedError;
  String? get accessToken => throw _privateConstructorUsedError;
  String? get apiVersion =>
      throw _privateConstructorUsedError; // Template IDs for different scenarios
  String? get welcomeTemplateId => throw _privateConstructorUsedError;
  String? get reminderTemplateId => throw _privateConstructorUsedError;
  String? get overdueTemplateId => throw _privateConstructorUsedError;
  String? get paymentConfirmationTemplateId =>
      throw _privateConstructorUsedError;
  String? get defaulterTemplateId =>
      throw _privateConstructorUsedError; // Default message settings
  bool get sendReminders => throw _privateConstructorUsedError;
  bool get sendOverdueAlerts => throw _privateConstructorUsedError;
  bool get sendVoiceNotes =>
      throw _privateConstructorUsedError; // AI generated voice messages
  // Business hours
  String? get businessHoursStart => throw _privateConstructorUsedError; // 09:00
  String? get businessHoursEnd => throw _privateConstructorUsedError; // 18:00
  bool get sendOutsideBusinessHours => throw _privateConstructorUsedError;

  /// Serializes this WhatsAppConfig to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of WhatsAppConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $WhatsAppConfigCopyWith<WhatsAppConfig> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $WhatsAppConfigCopyWith<$Res> {
  factory $WhatsAppConfigCopyWith(
    WhatsAppConfig value,
    $Res Function(WhatsAppConfig) then,
  ) = _$WhatsAppConfigCopyWithImpl<$Res, WhatsAppConfig>;
  @useResult
  $Res call({
    bool isEnabled,
    String? businessAccountId,
    String? phoneNumberId,
    String? accessToken,
    String? apiVersion,
    String? welcomeTemplateId,
    String? reminderTemplateId,
    String? overdueTemplateId,
    String? paymentConfirmationTemplateId,
    String? defaulterTemplateId,
    bool sendReminders,
    bool sendOverdueAlerts,
    bool sendVoiceNotes,
    String? businessHoursStart,
    String? businessHoursEnd,
    bool sendOutsideBusinessHours,
  });
}

/// @nodoc
class _$WhatsAppConfigCopyWithImpl<$Res, $Val extends WhatsAppConfig>
    implements $WhatsAppConfigCopyWith<$Res> {
  _$WhatsAppConfigCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of WhatsAppConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? isEnabled = null,
    Object? businessAccountId = freezed,
    Object? phoneNumberId = freezed,
    Object? accessToken = freezed,
    Object? apiVersion = freezed,
    Object? welcomeTemplateId = freezed,
    Object? reminderTemplateId = freezed,
    Object? overdueTemplateId = freezed,
    Object? paymentConfirmationTemplateId = freezed,
    Object? defaulterTemplateId = freezed,
    Object? sendReminders = null,
    Object? sendOverdueAlerts = null,
    Object? sendVoiceNotes = null,
    Object? businessHoursStart = freezed,
    Object? businessHoursEnd = freezed,
    Object? sendOutsideBusinessHours = null,
  }) {
    return _then(
      _value.copyWith(
            isEnabled: null == isEnabled
                ? _value.isEnabled
                : isEnabled // ignore: cast_nullable_to_non_nullable
                      as bool,
            businessAccountId: freezed == businessAccountId
                ? _value.businessAccountId
                : businessAccountId // ignore: cast_nullable_to_non_nullable
                      as String?,
            phoneNumberId: freezed == phoneNumberId
                ? _value.phoneNumberId
                : phoneNumberId // ignore: cast_nullable_to_non_nullable
                      as String?,
            accessToken: freezed == accessToken
                ? _value.accessToken
                : accessToken // ignore: cast_nullable_to_non_nullable
                      as String?,
            apiVersion: freezed == apiVersion
                ? _value.apiVersion
                : apiVersion // ignore: cast_nullable_to_non_nullable
                      as String?,
            welcomeTemplateId: freezed == welcomeTemplateId
                ? _value.welcomeTemplateId
                : welcomeTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            reminderTemplateId: freezed == reminderTemplateId
                ? _value.reminderTemplateId
                : reminderTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            overdueTemplateId: freezed == overdueTemplateId
                ? _value.overdueTemplateId
                : overdueTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            paymentConfirmationTemplateId:
                freezed == paymentConfirmationTemplateId
                ? _value.paymentConfirmationTemplateId
                : paymentConfirmationTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            defaulterTemplateId: freezed == defaulterTemplateId
                ? _value.defaulterTemplateId
                : defaulterTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            sendReminders: null == sendReminders
                ? _value.sendReminders
                : sendReminders // ignore: cast_nullable_to_non_nullable
                      as bool,
            sendOverdueAlerts: null == sendOverdueAlerts
                ? _value.sendOverdueAlerts
                : sendOverdueAlerts // ignore: cast_nullable_to_non_nullable
                      as bool,
            sendVoiceNotes: null == sendVoiceNotes
                ? _value.sendVoiceNotes
                : sendVoiceNotes // ignore: cast_nullable_to_non_nullable
                      as bool,
            businessHoursStart: freezed == businessHoursStart
                ? _value.businessHoursStart
                : businessHoursStart // ignore: cast_nullable_to_non_nullable
                      as String?,
            businessHoursEnd: freezed == businessHoursEnd
                ? _value.businessHoursEnd
                : businessHoursEnd // ignore: cast_nullable_to_non_nullable
                      as String?,
            sendOutsideBusinessHours: null == sendOutsideBusinessHours
                ? _value.sendOutsideBusinessHours
                : sendOutsideBusinessHours // ignore: cast_nullable_to_non_nullable
                      as bool,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$WhatsAppConfigImplCopyWith<$Res>
    implements $WhatsAppConfigCopyWith<$Res> {
  factory _$$WhatsAppConfigImplCopyWith(
    _$WhatsAppConfigImpl value,
    $Res Function(_$WhatsAppConfigImpl) then,
  ) = __$$WhatsAppConfigImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    bool isEnabled,
    String? businessAccountId,
    String? phoneNumberId,
    String? accessToken,
    String? apiVersion,
    String? welcomeTemplateId,
    String? reminderTemplateId,
    String? overdueTemplateId,
    String? paymentConfirmationTemplateId,
    String? defaulterTemplateId,
    bool sendReminders,
    bool sendOverdueAlerts,
    bool sendVoiceNotes,
    String? businessHoursStart,
    String? businessHoursEnd,
    bool sendOutsideBusinessHours,
  });
}

/// @nodoc
class __$$WhatsAppConfigImplCopyWithImpl<$Res>
    extends _$WhatsAppConfigCopyWithImpl<$Res, _$WhatsAppConfigImpl>
    implements _$$WhatsAppConfigImplCopyWith<$Res> {
  __$$WhatsAppConfigImplCopyWithImpl(
    _$WhatsAppConfigImpl _value,
    $Res Function(_$WhatsAppConfigImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of WhatsAppConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? isEnabled = null,
    Object? businessAccountId = freezed,
    Object? phoneNumberId = freezed,
    Object? accessToken = freezed,
    Object? apiVersion = freezed,
    Object? welcomeTemplateId = freezed,
    Object? reminderTemplateId = freezed,
    Object? overdueTemplateId = freezed,
    Object? paymentConfirmationTemplateId = freezed,
    Object? defaulterTemplateId = freezed,
    Object? sendReminders = null,
    Object? sendOverdueAlerts = null,
    Object? sendVoiceNotes = null,
    Object? businessHoursStart = freezed,
    Object? businessHoursEnd = freezed,
    Object? sendOutsideBusinessHours = null,
  }) {
    return _then(
      _$WhatsAppConfigImpl(
        isEnabled: null == isEnabled
            ? _value.isEnabled
            : isEnabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        businessAccountId: freezed == businessAccountId
            ? _value.businessAccountId
            : businessAccountId // ignore: cast_nullable_to_non_nullable
                  as String?,
        phoneNumberId: freezed == phoneNumberId
            ? _value.phoneNumberId
            : phoneNumberId // ignore: cast_nullable_to_non_nullable
                  as String?,
        accessToken: freezed == accessToken
            ? _value.accessToken
            : accessToken // ignore: cast_nullable_to_non_nullable
                  as String?,
        apiVersion: freezed == apiVersion
            ? _value.apiVersion
            : apiVersion // ignore: cast_nullable_to_non_nullable
                  as String?,
        welcomeTemplateId: freezed == welcomeTemplateId
            ? _value.welcomeTemplateId
            : welcomeTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        reminderTemplateId: freezed == reminderTemplateId
            ? _value.reminderTemplateId
            : reminderTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        overdueTemplateId: freezed == overdueTemplateId
            ? _value.overdueTemplateId
            : overdueTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        paymentConfirmationTemplateId: freezed == paymentConfirmationTemplateId
            ? _value.paymentConfirmationTemplateId
            : paymentConfirmationTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        defaulterTemplateId: freezed == defaulterTemplateId
            ? _value.defaulterTemplateId
            : defaulterTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        sendReminders: null == sendReminders
            ? _value.sendReminders
            : sendReminders // ignore: cast_nullable_to_non_nullable
                  as bool,
        sendOverdueAlerts: null == sendOverdueAlerts
            ? _value.sendOverdueAlerts
            : sendOverdueAlerts // ignore: cast_nullable_to_non_nullable
                  as bool,
        sendVoiceNotes: null == sendVoiceNotes
            ? _value.sendVoiceNotes
            : sendVoiceNotes // ignore: cast_nullable_to_non_nullable
                  as bool,
        businessHoursStart: freezed == businessHoursStart
            ? _value.businessHoursStart
            : businessHoursStart // ignore: cast_nullable_to_non_nullable
                  as String?,
        businessHoursEnd: freezed == businessHoursEnd
            ? _value.businessHoursEnd
            : businessHoursEnd // ignore: cast_nullable_to_non_nullable
                  as String?,
        sendOutsideBusinessHours: null == sendOutsideBusinessHours
            ? _value.sendOutsideBusinessHours
            : sendOutsideBusinessHours // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$WhatsAppConfigImpl implements _WhatsAppConfig {
  const _$WhatsAppConfigImpl({
    required this.isEnabled,
    this.businessAccountId,
    this.phoneNumberId,
    this.accessToken,
    this.apiVersion,
    this.welcomeTemplateId,
    this.reminderTemplateId,
    this.overdueTemplateId,
    this.paymentConfirmationTemplateId,
    this.defaulterTemplateId,
    this.sendReminders = true,
    this.sendOverdueAlerts = true,
    this.sendVoiceNotes = false,
    this.businessHoursStart,
    this.businessHoursEnd,
    this.sendOutsideBusinessHours = false,
  });

  factory _$WhatsAppConfigImpl.fromJson(Map<String, dynamic> json) =>
      _$$WhatsAppConfigImplFromJson(json);

  @override
  final bool isEnabled;
  @override
  final String? businessAccountId;
  @override
  final String? phoneNumberId;
  @override
  final String? accessToken;
  @override
  final String? apiVersion;
  // Template IDs for different scenarios
  @override
  final String? welcomeTemplateId;
  @override
  final String? reminderTemplateId;
  @override
  final String? overdueTemplateId;
  @override
  final String? paymentConfirmationTemplateId;
  @override
  final String? defaulterTemplateId;
  // Default message settings
  @override
  @JsonKey()
  final bool sendReminders;
  @override
  @JsonKey()
  final bool sendOverdueAlerts;
  @override
  @JsonKey()
  final bool sendVoiceNotes;
  // AI generated voice messages
  // Business hours
  @override
  final String? businessHoursStart;
  // 09:00
  @override
  final String? businessHoursEnd;
  // 18:00
  @override
  @JsonKey()
  final bool sendOutsideBusinessHours;

  @override
  String toString() {
    return 'WhatsAppConfig(isEnabled: $isEnabled, businessAccountId: $businessAccountId, phoneNumberId: $phoneNumberId, accessToken: $accessToken, apiVersion: $apiVersion, welcomeTemplateId: $welcomeTemplateId, reminderTemplateId: $reminderTemplateId, overdueTemplateId: $overdueTemplateId, paymentConfirmationTemplateId: $paymentConfirmationTemplateId, defaulterTemplateId: $defaulterTemplateId, sendReminders: $sendReminders, sendOverdueAlerts: $sendOverdueAlerts, sendVoiceNotes: $sendVoiceNotes, businessHoursStart: $businessHoursStart, businessHoursEnd: $businessHoursEnd, sendOutsideBusinessHours: $sendOutsideBusinessHours)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$WhatsAppConfigImpl &&
            (identical(other.isEnabled, isEnabled) ||
                other.isEnabled == isEnabled) &&
            (identical(other.businessAccountId, businessAccountId) ||
                other.businessAccountId == businessAccountId) &&
            (identical(other.phoneNumberId, phoneNumberId) ||
                other.phoneNumberId == phoneNumberId) &&
            (identical(other.accessToken, accessToken) ||
                other.accessToken == accessToken) &&
            (identical(other.apiVersion, apiVersion) ||
                other.apiVersion == apiVersion) &&
            (identical(other.welcomeTemplateId, welcomeTemplateId) ||
                other.welcomeTemplateId == welcomeTemplateId) &&
            (identical(other.reminderTemplateId, reminderTemplateId) ||
                other.reminderTemplateId == reminderTemplateId) &&
            (identical(other.overdueTemplateId, overdueTemplateId) ||
                other.overdueTemplateId == overdueTemplateId) &&
            (identical(
                  other.paymentConfirmationTemplateId,
                  paymentConfirmationTemplateId,
                ) ||
                other.paymentConfirmationTemplateId ==
                    paymentConfirmationTemplateId) &&
            (identical(other.defaulterTemplateId, defaulterTemplateId) ||
                other.defaulterTemplateId == defaulterTemplateId) &&
            (identical(other.sendReminders, sendReminders) ||
                other.sendReminders == sendReminders) &&
            (identical(other.sendOverdueAlerts, sendOverdueAlerts) ||
                other.sendOverdueAlerts == sendOverdueAlerts) &&
            (identical(other.sendVoiceNotes, sendVoiceNotes) ||
                other.sendVoiceNotes == sendVoiceNotes) &&
            (identical(other.businessHoursStart, businessHoursStart) ||
                other.businessHoursStart == businessHoursStart) &&
            (identical(other.businessHoursEnd, businessHoursEnd) ||
                other.businessHoursEnd == businessHoursEnd) &&
            (identical(
                  other.sendOutsideBusinessHours,
                  sendOutsideBusinessHours,
                ) ||
                other.sendOutsideBusinessHours == sendOutsideBusinessHours));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    isEnabled,
    businessAccountId,
    phoneNumberId,
    accessToken,
    apiVersion,
    welcomeTemplateId,
    reminderTemplateId,
    overdueTemplateId,
    paymentConfirmationTemplateId,
    defaulterTemplateId,
    sendReminders,
    sendOverdueAlerts,
    sendVoiceNotes,
    businessHoursStart,
    businessHoursEnd,
    sendOutsideBusinessHours,
  );

  /// Create a copy of WhatsAppConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$WhatsAppConfigImplCopyWith<_$WhatsAppConfigImpl> get copyWith =>
      __$$WhatsAppConfigImplCopyWithImpl<_$WhatsAppConfigImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$WhatsAppConfigImplToJson(this);
  }
}

abstract class _WhatsAppConfig implements WhatsAppConfig {
  const factory _WhatsAppConfig({
    required final bool isEnabled,
    final String? businessAccountId,
    final String? phoneNumberId,
    final String? accessToken,
    final String? apiVersion,
    final String? welcomeTemplateId,
    final String? reminderTemplateId,
    final String? overdueTemplateId,
    final String? paymentConfirmationTemplateId,
    final String? defaulterTemplateId,
    final bool sendReminders,
    final bool sendOverdueAlerts,
    final bool sendVoiceNotes,
    final String? businessHoursStart,
    final String? businessHoursEnd,
    final bool sendOutsideBusinessHours,
  }) = _$WhatsAppConfigImpl;

  factory _WhatsAppConfig.fromJson(Map<String, dynamic> json) =
      _$WhatsAppConfigImpl.fromJson;

  @override
  bool get isEnabled;
  @override
  String? get businessAccountId;
  @override
  String? get phoneNumberId;
  @override
  String? get accessToken;
  @override
  String? get apiVersion; // Template IDs for different scenarios
  @override
  String? get welcomeTemplateId;
  @override
  String? get reminderTemplateId;
  @override
  String? get overdueTemplateId;
  @override
  String? get paymentConfirmationTemplateId;
  @override
  String? get defaulterTemplateId; // Default message settings
  @override
  bool get sendReminders;
  @override
  bool get sendOverdueAlerts;
  @override
  bool get sendVoiceNotes; // AI generated voice messages
  // Business hours
  @override
  String? get businessHoursStart; // 09:00
  @override
  String? get businessHoursEnd; // 18:00
  @override
  bool get sendOutsideBusinessHours;

  /// Create a copy of WhatsAppConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$WhatsAppConfigImplCopyWith<_$WhatsAppConfigImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

VoiceCallConfig _$VoiceCallConfigFromJson(Map<String, dynamic> json) {
  return _VoiceCallConfig.fromJson(json);
}

/// @nodoc
mixin _$VoiceCallConfig {
  bool get isEnabled => throw _privateConstructorUsedError;
  String? get provider =>
      throw _privateConstructorUsedError; // Exotel, Knowlarity, Twilio, Ozonetel
  String? get apiKey => throw _privateConstructorUsedError;
  String? get apiSecret => throw _privateConstructorUsedError;
  String? get fromNumber => throw _privateConstructorUsedError; // IVR Settings
  bool get useIVR => throw _privateConstructorUsedError;
  String? get ivrGreetingMessage => throw _privateConstructorUsedError;
  String? get ivrMenuOptions =>
      throw _privateConstructorUsedError; // "Press 1 for EMI status, 2 for payment link..."
  // AI Voice Bot
  bool get useAIVoiceBot => throw _privateConstructorUsedError;
  String? get aiVoiceLanguage =>
      throw _privateConstructorUsedError; // hi-IN, en-IN
  String? get aiVoiceGender =>
      throw _privateConstructorUsedError; // male, female
  // Call scheduling
  int get maxRetryAttempts => throw _privateConstructorUsedError;
  int get retryIntervalMinutes => throw _privateConstructorUsedError;
  List<int> get preferredCallHours =>
      throw _privateConstructorUsedError; // 10 AM, 2 PM, 4 PM
  // Recording
  bool get recordCalls => throw _privateConstructorUsedError;
  bool get transcribeCalls => throw _privateConstructorUsedError;

  /// Serializes this VoiceCallConfig to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of VoiceCallConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $VoiceCallConfigCopyWith<VoiceCallConfig> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $VoiceCallConfigCopyWith<$Res> {
  factory $VoiceCallConfigCopyWith(
    VoiceCallConfig value,
    $Res Function(VoiceCallConfig) then,
  ) = _$VoiceCallConfigCopyWithImpl<$Res, VoiceCallConfig>;
  @useResult
  $Res call({
    bool isEnabled,
    String? provider,
    String? apiKey,
    String? apiSecret,
    String? fromNumber,
    bool useIVR,
    String? ivrGreetingMessage,
    String? ivrMenuOptions,
    bool useAIVoiceBot,
    String? aiVoiceLanguage,
    String? aiVoiceGender,
    int maxRetryAttempts,
    int retryIntervalMinutes,
    List<int> preferredCallHours,
    bool recordCalls,
    bool transcribeCalls,
  });
}

/// @nodoc
class _$VoiceCallConfigCopyWithImpl<$Res, $Val extends VoiceCallConfig>
    implements $VoiceCallConfigCopyWith<$Res> {
  _$VoiceCallConfigCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of VoiceCallConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? isEnabled = null,
    Object? provider = freezed,
    Object? apiKey = freezed,
    Object? apiSecret = freezed,
    Object? fromNumber = freezed,
    Object? useIVR = null,
    Object? ivrGreetingMessage = freezed,
    Object? ivrMenuOptions = freezed,
    Object? useAIVoiceBot = null,
    Object? aiVoiceLanguage = freezed,
    Object? aiVoiceGender = freezed,
    Object? maxRetryAttempts = null,
    Object? retryIntervalMinutes = null,
    Object? preferredCallHours = null,
    Object? recordCalls = null,
    Object? transcribeCalls = null,
  }) {
    return _then(
      _value.copyWith(
            isEnabled: null == isEnabled
                ? _value.isEnabled
                : isEnabled // ignore: cast_nullable_to_non_nullable
                      as bool,
            provider: freezed == provider
                ? _value.provider
                : provider // ignore: cast_nullable_to_non_nullable
                      as String?,
            apiKey: freezed == apiKey
                ? _value.apiKey
                : apiKey // ignore: cast_nullable_to_non_nullable
                      as String?,
            apiSecret: freezed == apiSecret
                ? _value.apiSecret
                : apiSecret // ignore: cast_nullable_to_non_nullable
                      as String?,
            fromNumber: freezed == fromNumber
                ? _value.fromNumber
                : fromNumber // ignore: cast_nullable_to_non_nullable
                      as String?,
            useIVR: null == useIVR
                ? _value.useIVR
                : useIVR // ignore: cast_nullable_to_non_nullable
                      as bool,
            ivrGreetingMessage: freezed == ivrGreetingMessage
                ? _value.ivrGreetingMessage
                : ivrGreetingMessage // ignore: cast_nullable_to_non_nullable
                      as String?,
            ivrMenuOptions: freezed == ivrMenuOptions
                ? _value.ivrMenuOptions
                : ivrMenuOptions // ignore: cast_nullable_to_non_nullable
                      as String?,
            useAIVoiceBot: null == useAIVoiceBot
                ? _value.useAIVoiceBot
                : useAIVoiceBot // ignore: cast_nullable_to_non_nullable
                      as bool,
            aiVoiceLanguage: freezed == aiVoiceLanguage
                ? _value.aiVoiceLanguage
                : aiVoiceLanguage // ignore: cast_nullable_to_non_nullable
                      as String?,
            aiVoiceGender: freezed == aiVoiceGender
                ? _value.aiVoiceGender
                : aiVoiceGender // ignore: cast_nullable_to_non_nullable
                      as String?,
            maxRetryAttempts: null == maxRetryAttempts
                ? _value.maxRetryAttempts
                : maxRetryAttempts // ignore: cast_nullable_to_non_nullable
                      as int,
            retryIntervalMinutes: null == retryIntervalMinutes
                ? _value.retryIntervalMinutes
                : retryIntervalMinutes // ignore: cast_nullable_to_non_nullable
                      as int,
            preferredCallHours: null == preferredCallHours
                ? _value.preferredCallHours
                : preferredCallHours // ignore: cast_nullable_to_non_nullable
                      as List<int>,
            recordCalls: null == recordCalls
                ? _value.recordCalls
                : recordCalls // ignore: cast_nullable_to_non_nullable
                      as bool,
            transcribeCalls: null == transcribeCalls
                ? _value.transcribeCalls
                : transcribeCalls // ignore: cast_nullable_to_non_nullable
                      as bool,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$VoiceCallConfigImplCopyWith<$Res>
    implements $VoiceCallConfigCopyWith<$Res> {
  factory _$$VoiceCallConfigImplCopyWith(
    _$VoiceCallConfigImpl value,
    $Res Function(_$VoiceCallConfigImpl) then,
  ) = __$$VoiceCallConfigImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    bool isEnabled,
    String? provider,
    String? apiKey,
    String? apiSecret,
    String? fromNumber,
    bool useIVR,
    String? ivrGreetingMessage,
    String? ivrMenuOptions,
    bool useAIVoiceBot,
    String? aiVoiceLanguage,
    String? aiVoiceGender,
    int maxRetryAttempts,
    int retryIntervalMinutes,
    List<int> preferredCallHours,
    bool recordCalls,
    bool transcribeCalls,
  });
}

/// @nodoc
class __$$VoiceCallConfigImplCopyWithImpl<$Res>
    extends _$VoiceCallConfigCopyWithImpl<$Res, _$VoiceCallConfigImpl>
    implements _$$VoiceCallConfigImplCopyWith<$Res> {
  __$$VoiceCallConfigImplCopyWithImpl(
    _$VoiceCallConfigImpl _value,
    $Res Function(_$VoiceCallConfigImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of VoiceCallConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? isEnabled = null,
    Object? provider = freezed,
    Object? apiKey = freezed,
    Object? apiSecret = freezed,
    Object? fromNumber = freezed,
    Object? useIVR = null,
    Object? ivrGreetingMessage = freezed,
    Object? ivrMenuOptions = freezed,
    Object? useAIVoiceBot = null,
    Object? aiVoiceLanguage = freezed,
    Object? aiVoiceGender = freezed,
    Object? maxRetryAttempts = null,
    Object? retryIntervalMinutes = null,
    Object? preferredCallHours = null,
    Object? recordCalls = null,
    Object? transcribeCalls = null,
  }) {
    return _then(
      _$VoiceCallConfigImpl(
        isEnabled: null == isEnabled
            ? _value.isEnabled
            : isEnabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        provider: freezed == provider
            ? _value.provider
            : provider // ignore: cast_nullable_to_non_nullable
                  as String?,
        apiKey: freezed == apiKey
            ? _value.apiKey
            : apiKey // ignore: cast_nullable_to_non_nullable
                  as String?,
        apiSecret: freezed == apiSecret
            ? _value.apiSecret
            : apiSecret // ignore: cast_nullable_to_non_nullable
                  as String?,
        fromNumber: freezed == fromNumber
            ? _value.fromNumber
            : fromNumber // ignore: cast_nullable_to_non_nullable
                  as String?,
        useIVR: null == useIVR
            ? _value.useIVR
            : useIVR // ignore: cast_nullable_to_non_nullable
                  as bool,
        ivrGreetingMessage: freezed == ivrGreetingMessage
            ? _value.ivrGreetingMessage
            : ivrGreetingMessage // ignore: cast_nullable_to_non_nullable
                  as String?,
        ivrMenuOptions: freezed == ivrMenuOptions
            ? _value.ivrMenuOptions
            : ivrMenuOptions // ignore: cast_nullable_to_non_nullable
                  as String?,
        useAIVoiceBot: null == useAIVoiceBot
            ? _value.useAIVoiceBot
            : useAIVoiceBot // ignore: cast_nullable_to_non_nullable
                  as bool,
        aiVoiceLanguage: freezed == aiVoiceLanguage
            ? _value.aiVoiceLanguage
            : aiVoiceLanguage // ignore: cast_nullable_to_non_nullable
                  as String?,
        aiVoiceGender: freezed == aiVoiceGender
            ? _value.aiVoiceGender
            : aiVoiceGender // ignore: cast_nullable_to_non_nullable
                  as String?,
        maxRetryAttempts: null == maxRetryAttempts
            ? _value.maxRetryAttempts
            : maxRetryAttempts // ignore: cast_nullable_to_non_nullable
                  as int,
        retryIntervalMinutes: null == retryIntervalMinutes
            ? _value.retryIntervalMinutes
            : retryIntervalMinutes // ignore: cast_nullable_to_non_nullable
                  as int,
        preferredCallHours: null == preferredCallHours
            ? _value._preferredCallHours
            : preferredCallHours // ignore: cast_nullable_to_non_nullable
                  as List<int>,
        recordCalls: null == recordCalls
            ? _value.recordCalls
            : recordCalls // ignore: cast_nullable_to_non_nullable
                  as bool,
        transcribeCalls: null == transcribeCalls
            ? _value.transcribeCalls
            : transcribeCalls // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$VoiceCallConfigImpl implements _VoiceCallConfig {
  const _$VoiceCallConfigImpl({
    required this.isEnabled,
    this.provider,
    this.apiKey,
    this.apiSecret,
    this.fromNumber,
    this.useIVR = false,
    this.ivrGreetingMessage,
    this.ivrMenuOptions,
    this.useAIVoiceBot = false,
    this.aiVoiceLanguage,
    this.aiVoiceGender,
    this.maxRetryAttempts = 3,
    this.retryIntervalMinutes = 30,
    final List<int> preferredCallHours = const [10, 14, 16],
    this.recordCalls = true,
    this.transcribeCalls = true,
  }) : _preferredCallHours = preferredCallHours;

  factory _$VoiceCallConfigImpl.fromJson(Map<String, dynamic> json) =>
      _$$VoiceCallConfigImplFromJson(json);

  @override
  final bool isEnabled;
  @override
  final String? provider;
  // Exotel, Knowlarity, Twilio, Ozonetel
  @override
  final String? apiKey;
  @override
  final String? apiSecret;
  @override
  final String? fromNumber;
  // IVR Settings
  @override
  @JsonKey()
  final bool useIVR;
  @override
  final String? ivrGreetingMessage;
  @override
  final String? ivrMenuOptions;
  // "Press 1 for EMI status, 2 for payment link..."
  // AI Voice Bot
  @override
  @JsonKey()
  final bool useAIVoiceBot;
  @override
  final String? aiVoiceLanguage;
  // hi-IN, en-IN
  @override
  final String? aiVoiceGender;
  // male, female
  // Call scheduling
  @override
  @JsonKey()
  final int maxRetryAttempts;
  @override
  @JsonKey()
  final int retryIntervalMinutes;
  final List<int> _preferredCallHours;
  @override
  @JsonKey()
  List<int> get preferredCallHours {
    if (_preferredCallHours is EqualUnmodifiableListView)
      return _preferredCallHours;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_preferredCallHours);
  }

  // 10 AM, 2 PM, 4 PM
  // Recording
  @override
  @JsonKey()
  final bool recordCalls;
  @override
  @JsonKey()
  final bool transcribeCalls;

  @override
  String toString() {
    return 'VoiceCallConfig(isEnabled: $isEnabled, provider: $provider, apiKey: $apiKey, apiSecret: $apiSecret, fromNumber: $fromNumber, useIVR: $useIVR, ivrGreetingMessage: $ivrGreetingMessage, ivrMenuOptions: $ivrMenuOptions, useAIVoiceBot: $useAIVoiceBot, aiVoiceLanguage: $aiVoiceLanguage, aiVoiceGender: $aiVoiceGender, maxRetryAttempts: $maxRetryAttempts, retryIntervalMinutes: $retryIntervalMinutes, preferredCallHours: $preferredCallHours, recordCalls: $recordCalls, transcribeCalls: $transcribeCalls)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$VoiceCallConfigImpl &&
            (identical(other.isEnabled, isEnabled) ||
                other.isEnabled == isEnabled) &&
            (identical(other.provider, provider) ||
                other.provider == provider) &&
            (identical(other.apiKey, apiKey) || other.apiKey == apiKey) &&
            (identical(other.apiSecret, apiSecret) ||
                other.apiSecret == apiSecret) &&
            (identical(other.fromNumber, fromNumber) ||
                other.fromNumber == fromNumber) &&
            (identical(other.useIVR, useIVR) || other.useIVR == useIVR) &&
            (identical(other.ivrGreetingMessage, ivrGreetingMessage) ||
                other.ivrGreetingMessage == ivrGreetingMessage) &&
            (identical(other.ivrMenuOptions, ivrMenuOptions) ||
                other.ivrMenuOptions == ivrMenuOptions) &&
            (identical(other.useAIVoiceBot, useAIVoiceBot) ||
                other.useAIVoiceBot == useAIVoiceBot) &&
            (identical(other.aiVoiceLanguage, aiVoiceLanguage) ||
                other.aiVoiceLanguage == aiVoiceLanguage) &&
            (identical(other.aiVoiceGender, aiVoiceGender) ||
                other.aiVoiceGender == aiVoiceGender) &&
            (identical(other.maxRetryAttempts, maxRetryAttempts) ||
                other.maxRetryAttempts == maxRetryAttempts) &&
            (identical(other.retryIntervalMinutes, retryIntervalMinutes) ||
                other.retryIntervalMinutes == retryIntervalMinutes) &&
            const DeepCollectionEquality().equals(
              other._preferredCallHours,
              _preferredCallHours,
            ) &&
            (identical(other.recordCalls, recordCalls) ||
                other.recordCalls == recordCalls) &&
            (identical(other.transcribeCalls, transcribeCalls) ||
                other.transcribeCalls == transcribeCalls));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    isEnabled,
    provider,
    apiKey,
    apiSecret,
    fromNumber,
    useIVR,
    ivrGreetingMessage,
    ivrMenuOptions,
    useAIVoiceBot,
    aiVoiceLanguage,
    aiVoiceGender,
    maxRetryAttempts,
    retryIntervalMinutes,
    const DeepCollectionEquality().hash(_preferredCallHours),
    recordCalls,
    transcribeCalls,
  );

  /// Create a copy of VoiceCallConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$VoiceCallConfigImplCopyWith<_$VoiceCallConfigImpl> get copyWith =>
      __$$VoiceCallConfigImplCopyWithImpl<_$VoiceCallConfigImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$VoiceCallConfigImplToJson(this);
  }
}

abstract class _VoiceCallConfig implements VoiceCallConfig {
  const factory _VoiceCallConfig({
    required final bool isEnabled,
    final String? provider,
    final String? apiKey,
    final String? apiSecret,
    final String? fromNumber,
    final bool useIVR,
    final String? ivrGreetingMessage,
    final String? ivrMenuOptions,
    final bool useAIVoiceBot,
    final String? aiVoiceLanguage,
    final String? aiVoiceGender,
    final int maxRetryAttempts,
    final int retryIntervalMinutes,
    final List<int> preferredCallHours,
    final bool recordCalls,
    final bool transcribeCalls,
  }) = _$VoiceCallConfigImpl;

  factory _VoiceCallConfig.fromJson(Map<String, dynamic> json) =
      _$VoiceCallConfigImpl.fromJson;

  @override
  bool get isEnabled;
  @override
  String? get provider; // Exotel, Knowlarity, Twilio, Ozonetel
  @override
  String? get apiKey;
  @override
  String? get apiSecret;
  @override
  String? get fromNumber; // IVR Settings
  @override
  bool get useIVR;
  @override
  String? get ivrGreetingMessage;
  @override
  String? get ivrMenuOptions; // "Press 1 for EMI status, 2 for payment link..."
  // AI Voice Bot
  @override
  bool get useAIVoiceBot;
  @override
  String? get aiVoiceLanguage; // hi-IN, en-IN
  @override
  String? get aiVoiceGender; // male, female
  // Call scheduling
  @override
  int get maxRetryAttempts;
  @override
  int get retryIntervalMinutes;
  @override
  List<int> get preferredCallHours; // 10 AM, 2 PM, 4 PM
  // Recording
  @override
  bool get recordCalls;
  @override
  bool get transcribeCalls;

  /// Create a copy of VoiceCallConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$VoiceCallConfigImplCopyWith<_$VoiceCallConfigImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

SMSConfig _$SMSConfigFromJson(Map<String, dynamic> json) {
  return _SMSConfig.fromJson(json);
}

/// @nodoc
mixin _$SMSConfig {
  bool get isEnabled => throw _privateConstructorUsedError;
  String? get provider =>
      throw _privateConstructorUsedError; // Msg91, Twilio, ValueFirst
  String? get apiKey => throw _privateConstructorUsedError;
  String? get senderId => throw _privateConstructorUsedError; // APSDLRM
  // DLT Template IDs (India TRAI compliance)
  String? get otpTemplateId => throw _privateConstructorUsedError;
  String? get reminderTemplateId => throw _privateConstructorUsedError;
  String? get overdueTemplateId => throw _privateConstructorUsedError;
  String? get paymentLinkTemplateId => throw _privateConstructorUsedError;
  String? get receiptTemplateId =>
      throw _privateConstructorUsedError; // SMS settings
  bool get useShortURL => throw _privateConstructorUsedError;
  bool get trackClicks => throw _privateConstructorUsedError;
  List<String> get blockedHours => throw _privateConstructorUsedError;

  /// Serializes this SMSConfig to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of SMSConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $SMSConfigCopyWith<SMSConfig> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $SMSConfigCopyWith<$Res> {
  factory $SMSConfigCopyWith(SMSConfig value, $Res Function(SMSConfig) then) =
      _$SMSConfigCopyWithImpl<$Res, SMSConfig>;
  @useResult
  $Res call({
    bool isEnabled,
    String? provider,
    String? apiKey,
    String? senderId,
    String? otpTemplateId,
    String? reminderTemplateId,
    String? overdueTemplateId,
    String? paymentLinkTemplateId,
    String? receiptTemplateId,
    bool useShortURL,
    bool trackClicks,
    List<String> blockedHours,
  });
}

/// @nodoc
class _$SMSConfigCopyWithImpl<$Res, $Val extends SMSConfig>
    implements $SMSConfigCopyWith<$Res> {
  _$SMSConfigCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of SMSConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? isEnabled = null,
    Object? provider = freezed,
    Object? apiKey = freezed,
    Object? senderId = freezed,
    Object? otpTemplateId = freezed,
    Object? reminderTemplateId = freezed,
    Object? overdueTemplateId = freezed,
    Object? paymentLinkTemplateId = freezed,
    Object? receiptTemplateId = freezed,
    Object? useShortURL = null,
    Object? trackClicks = null,
    Object? blockedHours = null,
  }) {
    return _then(
      _value.copyWith(
            isEnabled: null == isEnabled
                ? _value.isEnabled
                : isEnabled // ignore: cast_nullable_to_non_nullable
                      as bool,
            provider: freezed == provider
                ? _value.provider
                : provider // ignore: cast_nullable_to_non_nullable
                      as String?,
            apiKey: freezed == apiKey
                ? _value.apiKey
                : apiKey // ignore: cast_nullable_to_non_nullable
                      as String?,
            senderId: freezed == senderId
                ? _value.senderId
                : senderId // ignore: cast_nullable_to_non_nullable
                      as String?,
            otpTemplateId: freezed == otpTemplateId
                ? _value.otpTemplateId
                : otpTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            reminderTemplateId: freezed == reminderTemplateId
                ? _value.reminderTemplateId
                : reminderTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            overdueTemplateId: freezed == overdueTemplateId
                ? _value.overdueTemplateId
                : overdueTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            paymentLinkTemplateId: freezed == paymentLinkTemplateId
                ? _value.paymentLinkTemplateId
                : paymentLinkTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            receiptTemplateId: freezed == receiptTemplateId
                ? _value.receiptTemplateId
                : receiptTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            useShortURL: null == useShortURL
                ? _value.useShortURL
                : useShortURL // ignore: cast_nullable_to_non_nullable
                      as bool,
            trackClicks: null == trackClicks
                ? _value.trackClicks
                : trackClicks // ignore: cast_nullable_to_non_nullable
                      as bool,
            blockedHours: null == blockedHours
                ? _value.blockedHours
                : blockedHours // ignore: cast_nullable_to_non_nullable
                      as List<String>,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$SMSConfigImplCopyWith<$Res>
    implements $SMSConfigCopyWith<$Res> {
  factory _$$SMSConfigImplCopyWith(
    _$SMSConfigImpl value,
    $Res Function(_$SMSConfigImpl) then,
  ) = __$$SMSConfigImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    bool isEnabled,
    String? provider,
    String? apiKey,
    String? senderId,
    String? otpTemplateId,
    String? reminderTemplateId,
    String? overdueTemplateId,
    String? paymentLinkTemplateId,
    String? receiptTemplateId,
    bool useShortURL,
    bool trackClicks,
    List<String> blockedHours,
  });
}

/// @nodoc
class __$$SMSConfigImplCopyWithImpl<$Res>
    extends _$SMSConfigCopyWithImpl<$Res, _$SMSConfigImpl>
    implements _$$SMSConfigImplCopyWith<$Res> {
  __$$SMSConfigImplCopyWithImpl(
    _$SMSConfigImpl _value,
    $Res Function(_$SMSConfigImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of SMSConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? isEnabled = null,
    Object? provider = freezed,
    Object? apiKey = freezed,
    Object? senderId = freezed,
    Object? otpTemplateId = freezed,
    Object? reminderTemplateId = freezed,
    Object? overdueTemplateId = freezed,
    Object? paymentLinkTemplateId = freezed,
    Object? receiptTemplateId = freezed,
    Object? useShortURL = null,
    Object? trackClicks = null,
    Object? blockedHours = null,
  }) {
    return _then(
      _$SMSConfigImpl(
        isEnabled: null == isEnabled
            ? _value.isEnabled
            : isEnabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        provider: freezed == provider
            ? _value.provider
            : provider // ignore: cast_nullable_to_non_nullable
                  as String?,
        apiKey: freezed == apiKey
            ? _value.apiKey
            : apiKey // ignore: cast_nullable_to_non_nullable
                  as String?,
        senderId: freezed == senderId
            ? _value.senderId
            : senderId // ignore: cast_nullable_to_non_nullable
                  as String?,
        otpTemplateId: freezed == otpTemplateId
            ? _value.otpTemplateId
            : otpTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        reminderTemplateId: freezed == reminderTemplateId
            ? _value.reminderTemplateId
            : reminderTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        overdueTemplateId: freezed == overdueTemplateId
            ? _value.overdueTemplateId
            : overdueTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        paymentLinkTemplateId: freezed == paymentLinkTemplateId
            ? _value.paymentLinkTemplateId
            : paymentLinkTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        receiptTemplateId: freezed == receiptTemplateId
            ? _value.receiptTemplateId
            : receiptTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        useShortURL: null == useShortURL
            ? _value.useShortURL
            : useShortURL // ignore: cast_nullable_to_non_nullable
                  as bool,
        trackClicks: null == trackClicks
            ? _value.trackClicks
            : trackClicks // ignore: cast_nullable_to_non_nullable
                  as bool,
        blockedHours: null == blockedHours
            ? _value._blockedHours
            : blockedHours // ignore: cast_nullable_to_non_nullable
                  as List<String>,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$SMSConfigImpl implements _SMSConfig {
  const _$SMSConfigImpl({
    required this.isEnabled,
    this.provider,
    this.apiKey,
    this.senderId,
    this.otpTemplateId,
    this.reminderTemplateId,
    this.overdueTemplateId,
    this.paymentLinkTemplateId,
    this.receiptTemplateId,
    this.useShortURL = true,
    this.trackClicks = true,
    final List<String> blockedHours = const [],
  }) : _blockedHours = blockedHours;

  factory _$SMSConfigImpl.fromJson(Map<String, dynamic> json) =>
      _$$SMSConfigImplFromJson(json);

  @override
  final bool isEnabled;
  @override
  final String? provider;
  // Msg91, Twilio, ValueFirst
  @override
  final String? apiKey;
  @override
  final String? senderId;
  // APSDLRM
  // DLT Template IDs (India TRAI compliance)
  @override
  final String? otpTemplateId;
  @override
  final String? reminderTemplateId;
  @override
  final String? overdueTemplateId;
  @override
  final String? paymentLinkTemplateId;
  @override
  final String? receiptTemplateId;
  // SMS settings
  @override
  @JsonKey()
  final bool useShortURL;
  @override
  @JsonKey()
  final bool trackClicks;
  final List<String> _blockedHours;
  @override
  @JsonKey()
  List<String> get blockedHours {
    if (_blockedHours is EqualUnmodifiableListView) return _blockedHours;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_blockedHours);
  }

  @override
  String toString() {
    return 'SMSConfig(isEnabled: $isEnabled, provider: $provider, apiKey: $apiKey, senderId: $senderId, otpTemplateId: $otpTemplateId, reminderTemplateId: $reminderTemplateId, overdueTemplateId: $overdueTemplateId, paymentLinkTemplateId: $paymentLinkTemplateId, receiptTemplateId: $receiptTemplateId, useShortURL: $useShortURL, trackClicks: $trackClicks, blockedHours: $blockedHours)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$SMSConfigImpl &&
            (identical(other.isEnabled, isEnabled) ||
                other.isEnabled == isEnabled) &&
            (identical(other.provider, provider) ||
                other.provider == provider) &&
            (identical(other.apiKey, apiKey) || other.apiKey == apiKey) &&
            (identical(other.senderId, senderId) ||
                other.senderId == senderId) &&
            (identical(other.otpTemplateId, otpTemplateId) ||
                other.otpTemplateId == otpTemplateId) &&
            (identical(other.reminderTemplateId, reminderTemplateId) ||
                other.reminderTemplateId == reminderTemplateId) &&
            (identical(other.overdueTemplateId, overdueTemplateId) ||
                other.overdueTemplateId == overdueTemplateId) &&
            (identical(other.paymentLinkTemplateId, paymentLinkTemplateId) ||
                other.paymentLinkTemplateId == paymentLinkTemplateId) &&
            (identical(other.receiptTemplateId, receiptTemplateId) ||
                other.receiptTemplateId == receiptTemplateId) &&
            (identical(other.useShortURL, useShortURL) ||
                other.useShortURL == useShortURL) &&
            (identical(other.trackClicks, trackClicks) ||
                other.trackClicks == trackClicks) &&
            const DeepCollectionEquality().equals(
              other._blockedHours,
              _blockedHours,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    isEnabled,
    provider,
    apiKey,
    senderId,
    otpTemplateId,
    reminderTemplateId,
    overdueTemplateId,
    paymentLinkTemplateId,
    receiptTemplateId,
    useShortURL,
    trackClicks,
    const DeepCollectionEquality().hash(_blockedHours),
  );

  /// Create a copy of SMSConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$SMSConfigImplCopyWith<_$SMSConfigImpl> get copyWith =>
      __$$SMSConfigImplCopyWithImpl<_$SMSConfigImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$SMSConfigImplToJson(this);
  }
}

abstract class _SMSConfig implements SMSConfig {
  const factory _SMSConfig({
    required final bool isEnabled,
    final String? provider,
    final String? apiKey,
    final String? senderId,
    final String? otpTemplateId,
    final String? reminderTemplateId,
    final String? overdueTemplateId,
    final String? paymentLinkTemplateId,
    final String? receiptTemplateId,
    final bool useShortURL,
    final bool trackClicks,
    final List<String> blockedHours,
  }) = _$SMSConfigImpl;

  factory _SMSConfig.fromJson(Map<String, dynamic> json) =
      _$SMSConfigImpl.fromJson;

  @override
  bool get isEnabled;
  @override
  String? get provider; // Msg91, Twilio, ValueFirst
  @override
  String? get apiKey;
  @override
  String? get senderId; // APSDLRM
  // DLT Template IDs (India TRAI compliance)
  @override
  String? get otpTemplateId;
  @override
  String? get reminderTemplateId;
  @override
  String? get overdueTemplateId;
  @override
  String? get paymentLinkTemplateId;
  @override
  String? get receiptTemplateId; // SMS settings
  @override
  bool get useShortURL;
  @override
  bool get trackClicks;
  @override
  List<String> get blockedHours;

  /// Create a copy of SMSConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$SMSConfigImplCopyWith<_$SMSConfigImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

EmailConfig _$EmailConfigFromJson(Map<String, dynamic> json) {
  return _EmailConfig.fromJson(json);
}

/// @nodoc
mixin _$EmailConfig {
  bool get isEnabled => throw _privateConstructorUsedError;
  String? get provider =>
      throw _privateConstructorUsedError; // SendGrid, AWS SES, Mailgun
  String? get apiKey => throw _privateConstructorUsedError;
  String? get fromEmail => throw _privateConstructorUsedError;
  String? get fromName => throw _privateConstructorUsedError;
  String? get replyToEmail =>
      throw _privateConstructorUsedError; // Email templates
  String? get welcomeEmailTemplateId => throw _privateConstructorUsedError;
  String? get reminderEmailTemplateId => throw _privateConstructorUsedError;
  String? get invoiceEmailTemplateId => throw _privateConstructorUsedError;
  String? get receiptEmailTemplateId => throw _privateConstructorUsedError;
  String? get newsletterTemplateId =>
      throw _privateConstructorUsedError; // Settings
  bool get sendHTML => throw _privateConstructorUsedError;
  bool get trackOpens => throw _privateConstructorUsedError;
  bool get trackClicks => throw _privateConstructorUsedError;
  List<String> get bccEmails => throw _privateConstructorUsedError;

  /// Serializes this EmailConfig to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of EmailConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $EmailConfigCopyWith<EmailConfig> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $EmailConfigCopyWith<$Res> {
  factory $EmailConfigCopyWith(
    EmailConfig value,
    $Res Function(EmailConfig) then,
  ) = _$EmailConfigCopyWithImpl<$Res, EmailConfig>;
  @useResult
  $Res call({
    bool isEnabled,
    String? provider,
    String? apiKey,
    String? fromEmail,
    String? fromName,
    String? replyToEmail,
    String? welcomeEmailTemplateId,
    String? reminderEmailTemplateId,
    String? invoiceEmailTemplateId,
    String? receiptEmailTemplateId,
    String? newsletterTemplateId,
    bool sendHTML,
    bool trackOpens,
    bool trackClicks,
    List<String> bccEmails,
  });
}

/// @nodoc
class _$EmailConfigCopyWithImpl<$Res, $Val extends EmailConfig>
    implements $EmailConfigCopyWith<$Res> {
  _$EmailConfigCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of EmailConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? isEnabled = null,
    Object? provider = freezed,
    Object? apiKey = freezed,
    Object? fromEmail = freezed,
    Object? fromName = freezed,
    Object? replyToEmail = freezed,
    Object? welcomeEmailTemplateId = freezed,
    Object? reminderEmailTemplateId = freezed,
    Object? invoiceEmailTemplateId = freezed,
    Object? receiptEmailTemplateId = freezed,
    Object? newsletterTemplateId = freezed,
    Object? sendHTML = null,
    Object? trackOpens = null,
    Object? trackClicks = null,
    Object? bccEmails = null,
  }) {
    return _then(
      _value.copyWith(
            isEnabled: null == isEnabled
                ? _value.isEnabled
                : isEnabled // ignore: cast_nullable_to_non_nullable
                      as bool,
            provider: freezed == provider
                ? _value.provider
                : provider // ignore: cast_nullable_to_non_nullable
                      as String?,
            apiKey: freezed == apiKey
                ? _value.apiKey
                : apiKey // ignore: cast_nullable_to_non_nullable
                      as String?,
            fromEmail: freezed == fromEmail
                ? _value.fromEmail
                : fromEmail // ignore: cast_nullable_to_non_nullable
                      as String?,
            fromName: freezed == fromName
                ? _value.fromName
                : fromName // ignore: cast_nullable_to_non_nullable
                      as String?,
            replyToEmail: freezed == replyToEmail
                ? _value.replyToEmail
                : replyToEmail // ignore: cast_nullable_to_non_nullable
                      as String?,
            welcomeEmailTemplateId: freezed == welcomeEmailTemplateId
                ? _value.welcomeEmailTemplateId
                : welcomeEmailTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            reminderEmailTemplateId: freezed == reminderEmailTemplateId
                ? _value.reminderEmailTemplateId
                : reminderEmailTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            invoiceEmailTemplateId: freezed == invoiceEmailTemplateId
                ? _value.invoiceEmailTemplateId
                : invoiceEmailTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            receiptEmailTemplateId: freezed == receiptEmailTemplateId
                ? _value.receiptEmailTemplateId
                : receiptEmailTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            newsletterTemplateId: freezed == newsletterTemplateId
                ? _value.newsletterTemplateId
                : newsletterTemplateId // ignore: cast_nullable_to_non_nullable
                      as String?,
            sendHTML: null == sendHTML
                ? _value.sendHTML
                : sendHTML // ignore: cast_nullable_to_non_nullable
                      as bool,
            trackOpens: null == trackOpens
                ? _value.trackOpens
                : trackOpens // ignore: cast_nullable_to_non_nullable
                      as bool,
            trackClicks: null == trackClicks
                ? _value.trackClicks
                : trackClicks // ignore: cast_nullable_to_non_nullable
                      as bool,
            bccEmails: null == bccEmails
                ? _value.bccEmails
                : bccEmails // ignore: cast_nullable_to_non_nullable
                      as List<String>,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$EmailConfigImplCopyWith<$Res>
    implements $EmailConfigCopyWith<$Res> {
  factory _$$EmailConfigImplCopyWith(
    _$EmailConfigImpl value,
    $Res Function(_$EmailConfigImpl) then,
  ) = __$$EmailConfigImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    bool isEnabled,
    String? provider,
    String? apiKey,
    String? fromEmail,
    String? fromName,
    String? replyToEmail,
    String? welcomeEmailTemplateId,
    String? reminderEmailTemplateId,
    String? invoiceEmailTemplateId,
    String? receiptEmailTemplateId,
    String? newsletterTemplateId,
    bool sendHTML,
    bool trackOpens,
    bool trackClicks,
    List<String> bccEmails,
  });
}

/// @nodoc
class __$$EmailConfigImplCopyWithImpl<$Res>
    extends _$EmailConfigCopyWithImpl<$Res, _$EmailConfigImpl>
    implements _$$EmailConfigImplCopyWith<$Res> {
  __$$EmailConfigImplCopyWithImpl(
    _$EmailConfigImpl _value,
    $Res Function(_$EmailConfigImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of EmailConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? isEnabled = null,
    Object? provider = freezed,
    Object? apiKey = freezed,
    Object? fromEmail = freezed,
    Object? fromName = freezed,
    Object? replyToEmail = freezed,
    Object? welcomeEmailTemplateId = freezed,
    Object? reminderEmailTemplateId = freezed,
    Object? invoiceEmailTemplateId = freezed,
    Object? receiptEmailTemplateId = freezed,
    Object? newsletterTemplateId = freezed,
    Object? sendHTML = null,
    Object? trackOpens = null,
    Object? trackClicks = null,
    Object? bccEmails = null,
  }) {
    return _then(
      _$EmailConfigImpl(
        isEnabled: null == isEnabled
            ? _value.isEnabled
            : isEnabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        provider: freezed == provider
            ? _value.provider
            : provider // ignore: cast_nullable_to_non_nullable
                  as String?,
        apiKey: freezed == apiKey
            ? _value.apiKey
            : apiKey // ignore: cast_nullable_to_non_nullable
                  as String?,
        fromEmail: freezed == fromEmail
            ? _value.fromEmail
            : fromEmail // ignore: cast_nullable_to_non_nullable
                  as String?,
        fromName: freezed == fromName
            ? _value.fromName
            : fromName // ignore: cast_nullable_to_non_nullable
                  as String?,
        replyToEmail: freezed == replyToEmail
            ? _value.replyToEmail
            : replyToEmail // ignore: cast_nullable_to_non_nullable
                  as String?,
        welcomeEmailTemplateId: freezed == welcomeEmailTemplateId
            ? _value.welcomeEmailTemplateId
            : welcomeEmailTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        reminderEmailTemplateId: freezed == reminderEmailTemplateId
            ? _value.reminderEmailTemplateId
            : reminderEmailTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        invoiceEmailTemplateId: freezed == invoiceEmailTemplateId
            ? _value.invoiceEmailTemplateId
            : invoiceEmailTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        receiptEmailTemplateId: freezed == receiptEmailTemplateId
            ? _value.receiptEmailTemplateId
            : receiptEmailTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        newsletterTemplateId: freezed == newsletterTemplateId
            ? _value.newsletterTemplateId
            : newsletterTemplateId // ignore: cast_nullable_to_non_nullable
                  as String?,
        sendHTML: null == sendHTML
            ? _value.sendHTML
            : sendHTML // ignore: cast_nullable_to_non_nullable
                  as bool,
        trackOpens: null == trackOpens
            ? _value.trackOpens
            : trackOpens // ignore: cast_nullable_to_non_nullable
                  as bool,
        trackClicks: null == trackClicks
            ? _value.trackClicks
            : trackClicks // ignore: cast_nullable_to_non_nullable
                  as bool,
        bccEmails: null == bccEmails
            ? _value._bccEmails
            : bccEmails // ignore: cast_nullable_to_non_nullable
                  as List<String>,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$EmailConfigImpl implements _EmailConfig {
  const _$EmailConfigImpl({
    required this.isEnabled,
    this.provider,
    this.apiKey,
    this.fromEmail,
    this.fromName,
    this.replyToEmail,
    this.welcomeEmailTemplateId,
    this.reminderEmailTemplateId,
    this.invoiceEmailTemplateId,
    this.receiptEmailTemplateId,
    this.newsletterTemplateId,
    this.sendHTML = true,
    this.trackOpens = true,
    this.trackClicks = true,
    final List<String> bccEmails = const [],
  }) : _bccEmails = bccEmails;

  factory _$EmailConfigImpl.fromJson(Map<String, dynamic> json) =>
      _$$EmailConfigImplFromJson(json);

  @override
  final bool isEnabled;
  @override
  final String? provider;
  // SendGrid, AWS SES, Mailgun
  @override
  final String? apiKey;
  @override
  final String? fromEmail;
  @override
  final String? fromName;
  @override
  final String? replyToEmail;
  // Email templates
  @override
  final String? welcomeEmailTemplateId;
  @override
  final String? reminderEmailTemplateId;
  @override
  final String? invoiceEmailTemplateId;
  @override
  final String? receiptEmailTemplateId;
  @override
  final String? newsletterTemplateId;
  // Settings
  @override
  @JsonKey()
  final bool sendHTML;
  @override
  @JsonKey()
  final bool trackOpens;
  @override
  @JsonKey()
  final bool trackClicks;
  final List<String> _bccEmails;
  @override
  @JsonKey()
  List<String> get bccEmails {
    if (_bccEmails is EqualUnmodifiableListView) return _bccEmails;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_bccEmails);
  }

  @override
  String toString() {
    return 'EmailConfig(isEnabled: $isEnabled, provider: $provider, apiKey: $apiKey, fromEmail: $fromEmail, fromName: $fromName, replyToEmail: $replyToEmail, welcomeEmailTemplateId: $welcomeEmailTemplateId, reminderEmailTemplateId: $reminderEmailTemplateId, invoiceEmailTemplateId: $invoiceEmailTemplateId, receiptEmailTemplateId: $receiptEmailTemplateId, newsletterTemplateId: $newsletterTemplateId, sendHTML: $sendHTML, trackOpens: $trackOpens, trackClicks: $trackClicks, bccEmails: $bccEmails)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$EmailConfigImpl &&
            (identical(other.isEnabled, isEnabled) ||
                other.isEnabled == isEnabled) &&
            (identical(other.provider, provider) ||
                other.provider == provider) &&
            (identical(other.apiKey, apiKey) || other.apiKey == apiKey) &&
            (identical(other.fromEmail, fromEmail) ||
                other.fromEmail == fromEmail) &&
            (identical(other.fromName, fromName) ||
                other.fromName == fromName) &&
            (identical(other.replyToEmail, replyToEmail) ||
                other.replyToEmail == replyToEmail) &&
            (identical(other.welcomeEmailTemplateId, welcomeEmailTemplateId) ||
                other.welcomeEmailTemplateId == welcomeEmailTemplateId) &&
            (identical(
                  other.reminderEmailTemplateId,
                  reminderEmailTemplateId,
                ) ||
                other.reminderEmailTemplateId == reminderEmailTemplateId) &&
            (identical(other.invoiceEmailTemplateId, invoiceEmailTemplateId) ||
                other.invoiceEmailTemplateId == invoiceEmailTemplateId) &&
            (identical(other.receiptEmailTemplateId, receiptEmailTemplateId) ||
                other.receiptEmailTemplateId == receiptEmailTemplateId) &&
            (identical(other.newsletterTemplateId, newsletterTemplateId) ||
                other.newsletterTemplateId == newsletterTemplateId) &&
            (identical(other.sendHTML, sendHTML) ||
                other.sendHTML == sendHTML) &&
            (identical(other.trackOpens, trackOpens) ||
                other.trackOpens == trackOpens) &&
            (identical(other.trackClicks, trackClicks) ||
                other.trackClicks == trackClicks) &&
            const DeepCollectionEquality().equals(
              other._bccEmails,
              _bccEmails,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    isEnabled,
    provider,
    apiKey,
    fromEmail,
    fromName,
    replyToEmail,
    welcomeEmailTemplateId,
    reminderEmailTemplateId,
    invoiceEmailTemplateId,
    receiptEmailTemplateId,
    newsletterTemplateId,
    sendHTML,
    trackOpens,
    trackClicks,
    const DeepCollectionEquality().hash(_bccEmails),
  );

  /// Create a copy of EmailConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$EmailConfigImplCopyWith<_$EmailConfigImpl> get copyWith =>
      __$$EmailConfigImplCopyWithImpl<_$EmailConfigImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$EmailConfigImplToJson(this);
  }
}

abstract class _EmailConfig implements EmailConfig {
  const factory _EmailConfig({
    required final bool isEnabled,
    final String? provider,
    final String? apiKey,
    final String? fromEmail,
    final String? fromName,
    final String? replyToEmail,
    final String? welcomeEmailTemplateId,
    final String? reminderEmailTemplateId,
    final String? invoiceEmailTemplateId,
    final String? receiptEmailTemplateId,
    final String? newsletterTemplateId,
    final bool sendHTML,
    final bool trackOpens,
    final bool trackClicks,
    final List<String> bccEmails,
  }) = _$EmailConfigImpl;

  factory _EmailConfig.fromJson(Map<String, dynamic> json) =
      _$EmailConfigImpl.fromJson;

  @override
  bool get isEnabled;
  @override
  String? get provider; // SendGrid, AWS SES, Mailgun
  @override
  String? get apiKey;
  @override
  String? get fromEmail;
  @override
  String? get fromName;
  @override
  String? get replyToEmail; // Email templates
  @override
  String? get welcomeEmailTemplateId;
  @override
  String? get reminderEmailTemplateId;
  @override
  String? get invoiceEmailTemplateId;
  @override
  String? get receiptEmailTemplateId;
  @override
  String? get newsletterTemplateId; // Settings
  @override
  bool get sendHTML;
  @override
  bool get trackOpens;
  @override
  bool get trackClicks;
  @override
  List<String> get bccEmails;

  /// Create a copy of EmailConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$EmailConfigImplCopyWith<_$EmailConfigImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

FieldAgentConfig _$FieldAgentConfigFromJson(Map<String, dynamic> json) {
  return _FieldAgentConfig.fromJson(json);
}

/// @nodoc
mixin _$FieldAgentConfig {
  // Agent assignment settings
  String get assignmentMethod =>
      throw _privateConstructorUsedError; // round_robin, load_based, location_based, performance_based
  int get maxLeadsPerAgent => throw _privateConstructorUsedError;
  int get maxDailyVisits =>
      throw _privateConstructorUsedError; // Location tracking
  bool get trackLocation => throw _privateConstructorUsedError;
  int get locationUpdateIntervalMinutes => throw _privateConstructorUsedError;
  bool get geoFencingEnabled => throw _privateConstructorUsedError;
  int get geoFenceRadiusMeters =>
      throw _privateConstructorUsedError; // Commission structure
  double get collectionCommissionPercent =>
      throw _privateConstructorUsedError; // 0.5% of collected amount
  double get perCollectionFixedIncentive => throw _privateConstructorUsedError;
  double get targetAchievementBonus =>
      throw _privateConstructorUsedError; // App settings
  bool get offlineModeEnabled => throw _privateConstructorUsedError;
  bool get autoSyncEnabled => throw _privateConstructorUsedError;
  int get syncIntervalMinutes =>
      throw _privateConstructorUsedError; // Notifications
  bool get notifyOnNewAssignment => throw _privateConstructorUsedError;
  bool get notifyOnDueListReady => throw _privateConstructorUsedError;
  bool get notifyOnCollectionConfirmation => throw _privateConstructorUsedError;

  /// Serializes this FieldAgentConfig to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of FieldAgentConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $FieldAgentConfigCopyWith<FieldAgentConfig> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $FieldAgentConfigCopyWith<$Res> {
  factory $FieldAgentConfigCopyWith(
    FieldAgentConfig value,
    $Res Function(FieldAgentConfig) then,
  ) = _$FieldAgentConfigCopyWithImpl<$Res, FieldAgentConfig>;
  @useResult
  $Res call({
    String assignmentMethod,
    int maxLeadsPerAgent,
    int maxDailyVisits,
    bool trackLocation,
    int locationUpdateIntervalMinutes,
    bool geoFencingEnabled,
    int geoFenceRadiusMeters,
    double collectionCommissionPercent,
    double perCollectionFixedIncentive,
    double targetAchievementBonus,
    bool offlineModeEnabled,
    bool autoSyncEnabled,
    int syncIntervalMinutes,
    bool notifyOnNewAssignment,
    bool notifyOnDueListReady,
    bool notifyOnCollectionConfirmation,
  });
}

/// @nodoc
class _$FieldAgentConfigCopyWithImpl<$Res, $Val extends FieldAgentConfig>
    implements $FieldAgentConfigCopyWith<$Res> {
  _$FieldAgentConfigCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of FieldAgentConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? assignmentMethod = null,
    Object? maxLeadsPerAgent = null,
    Object? maxDailyVisits = null,
    Object? trackLocation = null,
    Object? locationUpdateIntervalMinutes = null,
    Object? geoFencingEnabled = null,
    Object? geoFenceRadiusMeters = null,
    Object? collectionCommissionPercent = null,
    Object? perCollectionFixedIncentive = null,
    Object? targetAchievementBonus = null,
    Object? offlineModeEnabled = null,
    Object? autoSyncEnabled = null,
    Object? syncIntervalMinutes = null,
    Object? notifyOnNewAssignment = null,
    Object? notifyOnDueListReady = null,
    Object? notifyOnCollectionConfirmation = null,
  }) {
    return _then(
      _value.copyWith(
            assignmentMethod: null == assignmentMethod
                ? _value.assignmentMethod
                : assignmentMethod // ignore: cast_nullable_to_non_nullable
                      as String,
            maxLeadsPerAgent: null == maxLeadsPerAgent
                ? _value.maxLeadsPerAgent
                : maxLeadsPerAgent // ignore: cast_nullable_to_non_nullable
                      as int,
            maxDailyVisits: null == maxDailyVisits
                ? _value.maxDailyVisits
                : maxDailyVisits // ignore: cast_nullable_to_non_nullable
                      as int,
            trackLocation: null == trackLocation
                ? _value.trackLocation
                : trackLocation // ignore: cast_nullable_to_non_nullable
                      as bool,
            locationUpdateIntervalMinutes: null == locationUpdateIntervalMinutes
                ? _value.locationUpdateIntervalMinutes
                : locationUpdateIntervalMinutes // ignore: cast_nullable_to_non_nullable
                      as int,
            geoFencingEnabled: null == geoFencingEnabled
                ? _value.geoFencingEnabled
                : geoFencingEnabled // ignore: cast_nullable_to_non_nullable
                      as bool,
            geoFenceRadiusMeters: null == geoFenceRadiusMeters
                ? _value.geoFenceRadiusMeters
                : geoFenceRadiusMeters // ignore: cast_nullable_to_non_nullable
                      as int,
            collectionCommissionPercent: null == collectionCommissionPercent
                ? _value.collectionCommissionPercent
                : collectionCommissionPercent // ignore: cast_nullable_to_non_nullable
                      as double,
            perCollectionFixedIncentive: null == perCollectionFixedIncentive
                ? _value.perCollectionFixedIncentive
                : perCollectionFixedIncentive // ignore: cast_nullable_to_non_nullable
                      as double,
            targetAchievementBonus: null == targetAchievementBonus
                ? _value.targetAchievementBonus
                : targetAchievementBonus // ignore: cast_nullable_to_non_nullable
                      as double,
            offlineModeEnabled: null == offlineModeEnabled
                ? _value.offlineModeEnabled
                : offlineModeEnabled // ignore: cast_nullable_to_non_nullable
                      as bool,
            autoSyncEnabled: null == autoSyncEnabled
                ? _value.autoSyncEnabled
                : autoSyncEnabled // ignore: cast_nullable_to_non_nullable
                      as bool,
            syncIntervalMinutes: null == syncIntervalMinutes
                ? _value.syncIntervalMinutes
                : syncIntervalMinutes // ignore: cast_nullable_to_non_nullable
                      as int,
            notifyOnNewAssignment: null == notifyOnNewAssignment
                ? _value.notifyOnNewAssignment
                : notifyOnNewAssignment // ignore: cast_nullable_to_non_nullable
                      as bool,
            notifyOnDueListReady: null == notifyOnDueListReady
                ? _value.notifyOnDueListReady
                : notifyOnDueListReady // ignore: cast_nullable_to_non_nullable
                      as bool,
            notifyOnCollectionConfirmation:
                null == notifyOnCollectionConfirmation
                ? _value.notifyOnCollectionConfirmation
                : notifyOnCollectionConfirmation // ignore: cast_nullable_to_non_nullable
                      as bool,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$FieldAgentConfigImplCopyWith<$Res>
    implements $FieldAgentConfigCopyWith<$Res> {
  factory _$$FieldAgentConfigImplCopyWith(
    _$FieldAgentConfigImpl value,
    $Res Function(_$FieldAgentConfigImpl) then,
  ) = __$$FieldAgentConfigImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String assignmentMethod,
    int maxLeadsPerAgent,
    int maxDailyVisits,
    bool trackLocation,
    int locationUpdateIntervalMinutes,
    bool geoFencingEnabled,
    int geoFenceRadiusMeters,
    double collectionCommissionPercent,
    double perCollectionFixedIncentive,
    double targetAchievementBonus,
    bool offlineModeEnabled,
    bool autoSyncEnabled,
    int syncIntervalMinutes,
    bool notifyOnNewAssignment,
    bool notifyOnDueListReady,
    bool notifyOnCollectionConfirmation,
  });
}

/// @nodoc
class __$$FieldAgentConfigImplCopyWithImpl<$Res>
    extends _$FieldAgentConfigCopyWithImpl<$Res, _$FieldAgentConfigImpl>
    implements _$$FieldAgentConfigImplCopyWith<$Res> {
  __$$FieldAgentConfigImplCopyWithImpl(
    _$FieldAgentConfigImpl _value,
    $Res Function(_$FieldAgentConfigImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of FieldAgentConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? assignmentMethod = null,
    Object? maxLeadsPerAgent = null,
    Object? maxDailyVisits = null,
    Object? trackLocation = null,
    Object? locationUpdateIntervalMinutes = null,
    Object? geoFencingEnabled = null,
    Object? geoFenceRadiusMeters = null,
    Object? collectionCommissionPercent = null,
    Object? perCollectionFixedIncentive = null,
    Object? targetAchievementBonus = null,
    Object? offlineModeEnabled = null,
    Object? autoSyncEnabled = null,
    Object? syncIntervalMinutes = null,
    Object? notifyOnNewAssignment = null,
    Object? notifyOnDueListReady = null,
    Object? notifyOnCollectionConfirmation = null,
  }) {
    return _then(
      _$FieldAgentConfigImpl(
        assignmentMethod: null == assignmentMethod
            ? _value.assignmentMethod
            : assignmentMethod // ignore: cast_nullable_to_non_nullable
                  as String,
        maxLeadsPerAgent: null == maxLeadsPerAgent
            ? _value.maxLeadsPerAgent
            : maxLeadsPerAgent // ignore: cast_nullable_to_non_nullable
                  as int,
        maxDailyVisits: null == maxDailyVisits
            ? _value.maxDailyVisits
            : maxDailyVisits // ignore: cast_nullable_to_non_nullable
                  as int,
        trackLocation: null == trackLocation
            ? _value.trackLocation
            : trackLocation // ignore: cast_nullable_to_non_nullable
                  as bool,
        locationUpdateIntervalMinutes: null == locationUpdateIntervalMinutes
            ? _value.locationUpdateIntervalMinutes
            : locationUpdateIntervalMinutes // ignore: cast_nullable_to_non_nullable
                  as int,
        geoFencingEnabled: null == geoFencingEnabled
            ? _value.geoFencingEnabled
            : geoFencingEnabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        geoFenceRadiusMeters: null == geoFenceRadiusMeters
            ? _value.geoFenceRadiusMeters
            : geoFenceRadiusMeters // ignore: cast_nullable_to_non_nullable
                  as int,
        collectionCommissionPercent: null == collectionCommissionPercent
            ? _value.collectionCommissionPercent
            : collectionCommissionPercent // ignore: cast_nullable_to_non_nullable
                  as double,
        perCollectionFixedIncentive: null == perCollectionFixedIncentive
            ? _value.perCollectionFixedIncentive
            : perCollectionFixedIncentive // ignore: cast_nullable_to_non_nullable
                  as double,
        targetAchievementBonus: null == targetAchievementBonus
            ? _value.targetAchievementBonus
            : targetAchievementBonus // ignore: cast_nullable_to_non_nullable
                  as double,
        offlineModeEnabled: null == offlineModeEnabled
            ? _value.offlineModeEnabled
            : offlineModeEnabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        autoSyncEnabled: null == autoSyncEnabled
            ? _value.autoSyncEnabled
            : autoSyncEnabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        syncIntervalMinutes: null == syncIntervalMinutes
            ? _value.syncIntervalMinutes
            : syncIntervalMinutes // ignore: cast_nullable_to_non_nullable
                  as int,
        notifyOnNewAssignment: null == notifyOnNewAssignment
            ? _value.notifyOnNewAssignment
            : notifyOnNewAssignment // ignore: cast_nullable_to_non_nullable
                  as bool,
        notifyOnDueListReady: null == notifyOnDueListReady
            ? _value.notifyOnDueListReady
            : notifyOnDueListReady // ignore: cast_nullable_to_non_nullable
                  as bool,
        notifyOnCollectionConfirmation: null == notifyOnCollectionConfirmation
            ? _value.notifyOnCollectionConfirmation
            : notifyOnCollectionConfirmation // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$FieldAgentConfigImpl implements _FieldAgentConfig {
  const _$FieldAgentConfigImpl({
    this.assignmentMethod = 'round_robin',
    this.maxLeadsPerAgent = 20,
    this.maxDailyVisits = 50,
    this.trackLocation = true,
    this.locationUpdateIntervalMinutes = 5,
    this.geoFencingEnabled = true,
    this.geoFenceRadiusMeters = 500,
    this.collectionCommissionPercent = 0.5,
    this.perCollectionFixedIncentive = 50,
    this.targetAchievementBonus = 500,
    this.offlineModeEnabled = true,
    this.autoSyncEnabled = true,
    this.syncIntervalMinutes = 15,
    this.notifyOnNewAssignment = true,
    this.notifyOnDueListReady = true,
    this.notifyOnCollectionConfirmation = true,
  });

  factory _$FieldAgentConfigImpl.fromJson(Map<String, dynamic> json) =>
      _$$FieldAgentConfigImplFromJson(json);

  // Agent assignment settings
  @override
  @JsonKey()
  final String assignmentMethod;
  // round_robin, load_based, location_based, performance_based
  @override
  @JsonKey()
  final int maxLeadsPerAgent;
  @override
  @JsonKey()
  final int maxDailyVisits;
  // Location tracking
  @override
  @JsonKey()
  final bool trackLocation;
  @override
  @JsonKey()
  final int locationUpdateIntervalMinutes;
  @override
  @JsonKey()
  final bool geoFencingEnabled;
  @override
  @JsonKey()
  final int geoFenceRadiusMeters;
  // Commission structure
  @override
  @JsonKey()
  final double collectionCommissionPercent;
  // 0.5% of collected amount
  @override
  @JsonKey()
  final double perCollectionFixedIncentive;
  @override
  @JsonKey()
  final double targetAchievementBonus;
  // App settings
  @override
  @JsonKey()
  final bool offlineModeEnabled;
  @override
  @JsonKey()
  final bool autoSyncEnabled;
  @override
  @JsonKey()
  final int syncIntervalMinutes;
  // Notifications
  @override
  @JsonKey()
  final bool notifyOnNewAssignment;
  @override
  @JsonKey()
  final bool notifyOnDueListReady;
  @override
  @JsonKey()
  final bool notifyOnCollectionConfirmation;

  @override
  String toString() {
    return 'FieldAgentConfig(assignmentMethod: $assignmentMethod, maxLeadsPerAgent: $maxLeadsPerAgent, maxDailyVisits: $maxDailyVisits, trackLocation: $trackLocation, locationUpdateIntervalMinutes: $locationUpdateIntervalMinutes, geoFencingEnabled: $geoFencingEnabled, geoFenceRadiusMeters: $geoFenceRadiusMeters, collectionCommissionPercent: $collectionCommissionPercent, perCollectionFixedIncentive: $perCollectionFixedIncentive, targetAchievementBonus: $targetAchievementBonus, offlineModeEnabled: $offlineModeEnabled, autoSyncEnabled: $autoSyncEnabled, syncIntervalMinutes: $syncIntervalMinutes, notifyOnNewAssignment: $notifyOnNewAssignment, notifyOnDueListReady: $notifyOnDueListReady, notifyOnCollectionConfirmation: $notifyOnCollectionConfirmation)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$FieldAgentConfigImpl &&
            (identical(other.assignmentMethod, assignmentMethod) ||
                other.assignmentMethod == assignmentMethod) &&
            (identical(other.maxLeadsPerAgent, maxLeadsPerAgent) ||
                other.maxLeadsPerAgent == maxLeadsPerAgent) &&
            (identical(other.maxDailyVisits, maxDailyVisits) ||
                other.maxDailyVisits == maxDailyVisits) &&
            (identical(other.trackLocation, trackLocation) ||
                other.trackLocation == trackLocation) &&
            (identical(
                  other.locationUpdateIntervalMinutes,
                  locationUpdateIntervalMinutes,
                ) ||
                other.locationUpdateIntervalMinutes ==
                    locationUpdateIntervalMinutes) &&
            (identical(other.geoFencingEnabled, geoFencingEnabled) ||
                other.geoFencingEnabled == geoFencingEnabled) &&
            (identical(other.geoFenceRadiusMeters, geoFenceRadiusMeters) ||
                other.geoFenceRadiusMeters == geoFenceRadiusMeters) &&
            (identical(
                  other.collectionCommissionPercent,
                  collectionCommissionPercent,
                ) ||
                other.collectionCommissionPercent ==
                    collectionCommissionPercent) &&
            (identical(
                  other.perCollectionFixedIncentive,
                  perCollectionFixedIncentive,
                ) ||
                other.perCollectionFixedIncentive ==
                    perCollectionFixedIncentive) &&
            (identical(other.targetAchievementBonus, targetAchievementBonus) ||
                other.targetAchievementBonus == targetAchievementBonus) &&
            (identical(other.offlineModeEnabled, offlineModeEnabled) ||
                other.offlineModeEnabled == offlineModeEnabled) &&
            (identical(other.autoSyncEnabled, autoSyncEnabled) ||
                other.autoSyncEnabled == autoSyncEnabled) &&
            (identical(other.syncIntervalMinutes, syncIntervalMinutes) ||
                other.syncIntervalMinutes == syncIntervalMinutes) &&
            (identical(other.notifyOnNewAssignment, notifyOnNewAssignment) ||
                other.notifyOnNewAssignment == notifyOnNewAssignment) &&
            (identical(other.notifyOnDueListReady, notifyOnDueListReady) ||
                other.notifyOnDueListReady == notifyOnDueListReady) &&
            (identical(
                  other.notifyOnCollectionConfirmation,
                  notifyOnCollectionConfirmation,
                ) ||
                other.notifyOnCollectionConfirmation ==
                    notifyOnCollectionConfirmation));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    assignmentMethod,
    maxLeadsPerAgent,
    maxDailyVisits,
    trackLocation,
    locationUpdateIntervalMinutes,
    geoFencingEnabled,
    geoFenceRadiusMeters,
    collectionCommissionPercent,
    perCollectionFixedIncentive,
    targetAchievementBonus,
    offlineModeEnabled,
    autoSyncEnabled,
    syncIntervalMinutes,
    notifyOnNewAssignment,
    notifyOnDueListReady,
    notifyOnCollectionConfirmation,
  );

  /// Create a copy of FieldAgentConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$FieldAgentConfigImplCopyWith<_$FieldAgentConfigImpl> get copyWith =>
      __$$FieldAgentConfigImplCopyWithImpl<_$FieldAgentConfigImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$FieldAgentConfigImplToJson(this);
  }
}

abstract class _FieldAgentConfig implements FieldAgentConfig {
  const factory _FieldAgentConfig({
    final String assignmentMethod,
    final int maxLeadsPerAgent,
    final int maxDailyVisits,
    final bool trackLocation,
    final int locationUpdateIntervalMinutes,
    final bool geoFencingEnabled,
    final int geoFenceRadiusMeters,
    final double collectionCommissionPercent,
    final double perCollectionFixedIncentive,
    final double targetAchievementBonus,
    final bool offlineModeEnabled,
    final bool autoSyncEnabled,
    final int syncIntervalMinutes,
    final bool notifyOnNewAssignment,
    final bool notifyOnDueListReady,
    final bool notifyOnCollectionConfirmation,
  }) = _$FieldAgentConfigImpl;

  factory _FieldAgentConfig.fromJson(Map<String, dynamic> json) =
      _$FieldAgentConfigImpl.fromJson;

  // Agent assignment settings
  @override
  String get assignmentMethod; // round_robin, load_based, location_based, performance_based
  @override
  int get maxLeadsPerAgent;
  @override
  int get maxDailyVisits; // Location tracking
  @override
  bool get trackLocation;
  @override
  int get locationUpdateIntervalMinutes;
  @override
  bool get geoFencingEnabled;
  @override
  int get geoFenceRadiusMeters; // Commission structure
  @override
  double get collectionCommissionPercent; // 0.5% of collected amount
  @override
  double get perCollectionFixedIncentive;
  @override
  double get targetAchievementBonus; // App settings
  @override
  bool get offlineModeEnabled;
  @override
  bool get autoSyncEnabled;
  @override
  int get syncIntervalMinutes; // Notifications
  @override
  bool get notifyOnNewAssignment;
  @override
  bool get notifyOnDueListReady;
  @override
  bool get notifyOnCollectionConfirmation;

  /// Create a copy of FieldAgentConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$FieldAgentConfigImplCopyWith<_$FieldAgentConfigImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

AIConfig _$AIConfigFromJson(Map<String, dynamic> json) {
  return _AIConfig.fromJson(json);
}

/// @nodoc
mixin _$AIConfig {
  // AI Lead Scoring
  bool get enableLeadScoring => throw _privateConstructorUsedError;
  bool get autoAssignLeads =>
      throw _privateConstructorUsedError; // AI Communication
  bool get enableAIVoiceCalls => throw _privateConstructorUsedError;
  bool get enableAIWhatsApp => throw _privateConstructorUsedError;
  bool get enableAIPersonalization =>
      throw _privateConstructorUsedError; // AI Prediction
  bool get predictDefaultRisk => throw _privateConstructorUsedError;
  bool get predictBestCollectionTime => throw _privateConstructorUsedError;
  bool get predictCustomerResponse =>
      throw _privateConstructorUsedError; // AI Document Processing
  bool get enableOCR => throw _privateConstructorUsedError;
  bool get enableAutoReceiptGeneration =>
      throw _privateConstructorUsedError; // AI Assistant
  bool get enableFieldAgentAIAssistant => throw _privateConstructorUsedError;
  bool get enableCustomerAIChatbot => throw _privateConstructorUsedError;

  /// Serializes this AIConfig to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of AIConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $AIConfigCopyWith<AIConfig> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $AIConfigCopyWith<$Res> {
  factory $AIConfigCopyWith(AIConfig value, $Res Function(AIConfig) then) =
      _$AIConfigCopyWithImpl<$Res, AIConfig>;
  @useResult
  $Res call({
    bool enableLeadScoring,
    bool autoAssignLeads,
    bool enableAIVoiceCalls,
    bool enableAIWhatsApp,
    bool enableAIPersonalization,
    bool predictDefaultRisk,
    bool predictBestCollectionTime,
    bool predictCustomerResponse,
    bool enableOCR,
    bool enableAutoReceiptGeneration,
    bool enableFieldAgentAIAssistant,
    bool enableCustomerAIChatbot,
  });
}

/// @nodoc
class _$AIConfigCopyWithImpl<$Res, $Val extends AIConfig>
    implements $AIConfigCopyWith<$Res> {
  _$AIConfigCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of AIConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? enableLeadScoring = null,
    Object? autoAssignLeads = null,
    Object? enableAIVoiceCalls = null,
    Object? enableAIWhatsApp = null,
    Object? enableAIPersonalization = null,
    Object? predictDefaultRisk = null,
    Object? predictBestCollectionTime = null,
    Object? predictCustomerResponse = null,
    Object? enableOCR = null,
    Object? enableAutoReceiptGeneration = null,
    Object? enableFieldAgentAIAssistant = null,
    Object? enableCustomerAIChatbot = null,
  }) {
    return _then(
      _value.copyWith(
            enableLeadScoring: null == enableLeadScoring
                ? _value.enableLeadScoring
                : enableLeadScoring // ignore: cast_nullable_to_non_nullable
                      as bool,
            autoAssignLeads: null == autoAssignLeads
                ? _value.autoAssignLeads
                : autoAssignLeads // ignore: cast_nullable_to_non_nullable
                      as bool,
            enableAIVoiceCalls: null == enableAIVoiceCalls
                ? _value.enableAIVoiceCalls
                : enableAIVoiceCalls // ignore: cast_nullable_to_non_nullable
                      as bool,
            enableAIWhatsApp: null == enableAIWhatsApp
                ? _value.enableAIWhatsApp
                : enableAIWhatsApp // ignore: cast_nullable_to_non_nullable
                      as bool,
            enableAIPersonalization: null == enableAIPersonalization
                ? _value.enableAIPersonalization
                : enableAIPersonalization // ignore: cast_nullable_to_non_nullable
                      as bool,
            predictDefaultRisk: null == predictDefaultRisk
                ? _value.predictDefaultRisk
                : predictDefaultRisk // ignore: cast_nullable_to_non_nullable
                      as bool,
            predictBestCollectionTime: null == predictBestCollectionTime
                ? _value.predictBestCollectionTime
                : predictBestCollectionTime // ignore: cast_nullable_to_non_nullable
                      as bool,
            predictCustomerResponse: null == predictCustomerResponse
                ? _value.predictCustomerResponse
                : predictCustomerResponse // ignore: cast_nullable_to_non_nullable
                      as bool,
            enableOCR: null == enableOCR
                ? _value.enableOCR
                : enableOCR // ignore: cast_nullable_to_non_nullable
                      as bool,
            enableAutoReceiptGeneration: null == enableAutoReceiptGeneration
                ? _value.enableAutoReceiptGeneration
                : enableAutoReceiptGeneration // ignore: cast_nullable_to_non_nullable
                      as bool,
            enableFieldAgentAIAssistant: null == enableFieldAgentAIAssistant
                ? _value.enableFieldAgentAIAssistant
                : enableFieldAgentAIAssistant // ignore: cast_nullable_to_non_nullable
                      as bool,
            enableCustomerAIChatbot: null == enableCustomerAIChatbot
                ? _value.enableCustomerAIChatbot
                : enableCustomerAIChatbot // ignore: cast_nullable_to_non_nullable
                      as bool,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$AIConfigImplCopyWith<$Res>
    implements $AIConfigCopyWith<$Res> {
  factory _$$AIConfigImplCopyWith(
    _$AIConfigImpl value,
    $Res Function(_$AIConfigImpl) then,
  ) = __$$AIConfigImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    bool enableLeadScoring,
    bool autoAssignLeads,
    bool enableAIVoiceCalls,
    bool enableAIWhatsApp,
    bool enableAIPersonalization,
    bool predictDefaultRisk,
    bool predictBestCollectionTime,
    bool predictCustomerResponse,
    bool enableOCR,
    bool enableAutoReceiptGeneration,
    bool enableFieldAgentAIAssistant,
    bool enableCustomerAIChatbot,
  });
}

/// @nodoc
class __$$AIConfigImplCopyWithImpl<$Res>
    extends _$AIConfigCopyWithImpl<$Res, _$AIConfigImpl>
    implements _$$AIConfigImplCopyWith<$Res> {
  __$$AIConfigImplCopyWithImpl(
    _$AIConfigImpl _value,
    $Res Function(_$AIConfigImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of AIConfig
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? enableLeadScoring = null,
    Object? autoAssignLeads = null,
    Object? enableAIVoiceCalls = null,
    Object? enableAIWhatsApp = null,
    Object? enableAIPersonalization = null,
    Object? predictDefaultRisk = null,
    Object? predictBestCollectionTime = null,
    Object? predictCustomerResponse = null,
    Object? enableOCR = null,
    Object? enableAutoReceiptGeneration = null,
    Object? enableFieldAgentAIAssistant = null,
    Object? enableCustomerAIChatbot = null,
  }) {
    return _then(
      _$AIConfigImpl(
        enableLeadScoring: null == enableLeadScoring
            ? _value.enableLeadScoring
            : enableLeadScoring // ignore: cast_nullable_to_non_nullable
                  as bool,
        autoAssignLeads: null == autoAssignLeads
            ? _value.autoAssignLeads
            : autoAssignLeads // ignore: cast_nullable_to_non_nullable
                  as bool,
        enableAIVoiceCalls: null == enableAIVoiceCalls
            ? _value.enableAIVoiceCalls
            : enableAIVoiceCalls // ignore: cast_nullable_to_non_nullable
                  as bool,
        enableAIWhatsApp: null == enableAIWhatsApp
            ? _value.enableAIWhatsApp
            : enableAIWhatsApp // ignore: cast_nullable_to_non_nullable
                  as bool,
        enableAIPersonalization: null == enableAIPersonalization
            ? _value.enableAIPersonalization
            : enableAIPersonalization // ignore: cast_nullable_to_non_nullable
                  as bool,
        predictDefaultRisk: null == predictDefaultRisk
            ? _value.predictDefaultRisk
            : predictDefaultRisk // ignore: cast_nullable_to_non_nullable
                  as bool,
        predictBestCollectionTime: null == predictBestCollectionTime
            ? _value.predictBestCollectionTime
            : predictBestCollectionTime // ignore: cast_nullable_to_non_nullable
                  as bool,
        predictCustomerResponse: null == predictCustomerResponse
            ? _value.predictCustomerResponse
            : predictCustomerResponse // ignore: cast_nullable_to_non_nullable
                  as bool,
        enableOCR: null == enableOCR
            ? _value.enableOCR
            : enableOCR // ignore: cast_nullable_to_non_nullable
                  as bool,
        enableAutoReceiptGeneration: null == enableAutoReceiptGeneration
            ? _value.enableAutoReceiptGeneration
            : enableAutoReceiptGeneration // ignore: cast_nullable_to_non_nullable
                  as bool,
        enableFieldAgentAIAssistant: null == enableFieldAgentAIAssistant
            ? _value.enableFieldAgentAIAssistant
            : enableFieldAgentAIAssistant // ignore: cast_nullable_to_non_nullable
                  as bool,
        enableCustomerAIChatbot: null == enableCustomerAIChatbot
            ? _value.enableCustomerAIChatbot
            : enableCustomerAIChatbot // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$AIConfigImpl implements _AIConfig {
  const _$AIConfigImpl({
    this.enableLeadScoring = true,
    this.autoAssignLeads = true,
    this.enableAIVoiceCalls = true,
    this.enableAIWhatsApp = true,
    this.enableAIPersonalization = true,
    this.predictDefaultRisk = true,
    this.predictBestCollectionTime = true,
    this.predictCustomerResponse = true,
    this.enableOCR = true,
    this.enableAutoReceiptGeneration = true,
    this.enableFieldAgentAIAssistant = true,
    this.enableCustomerAIChatbot = true,
  });

  factory _$AIConfigImpl.fromJson(Map<String, dynamic> json) =>
      _$$AIConfigImplFromJson(json);

  // AI Lead Scoring
  @override
  @JsonKey()
  final bool enableLeadScoring;
  @override
  @JsonKey()
  final bool autoAssignLeads;
  // AI Communication
  @override
  @JsonKey()
  final bool enableAIVoiceCalls;
  @override
  @JsonKey()
  final bool enableAIWhatsApp;
  @override
  @JsonKey()
  final bool enableAIPersonalization;
  // AI Prediction
  @override
  @JsonKey()
  final bool predictDefaultRisk;
  @override
  @JsonKey()
  final bool predictBestCollectionTime;
  @override
  @JsonKey()
  final bool predictCustomerResponse;
  // AI Document Processing
  @override
  @JsonKey()
  final bool enableOCR;
  @override
  @JsonKey()
  final bool enableAutoReceiptGeneration;
  // AI Assistant
  @override
  @JsonKey()
  final bool enableFieldAgentAIAssistant;
  @override
  @JsonKey()
  final bool enableCustomerAIChatbot;

  @override
  String toString() {
    return 'AIConfig(enableLeadScoring: $enableLeadScoring, autoAssignLeads: $autoAssignLeads, enableAIVoiceCalls: $enableAIVoiceCalls, enableAIWhatsApp: $enableAIWhatsApp, enableAIPersonalization: $enableAIPersonalization, predictDefaultRisk: $predictDefaultRisk, predictBestCollectionTime: $predictBestCollectionTime, predictCustomerResponse: $predictCustomerResponse, enableOCR: $enableOCR, enableAutoReceiptGeneration: $enableAutoReceiptGeneration, enableFieldAgentAIAssistant: $enableFieldAgentAIAssistant, enableCustomerAIChatbot: $enableCustomerAIChatbot)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$AIConfigImpl &&
            (identical(other.enableLeadScoring, enableLeadScoring) ||
                other.enableLeadScoring == enableLeadScoring) &&
            (identical(other.autoAssignLeads, autoAssignLeads) ||
                other.autoAssignLeads == autoAssignLeads) &&
            (identical(other.enableAIVoiceCalls, enableAIVoiceCalls) ||
                other.enableAIVoiceCalls == enableAIVoiceCalls) &&
            (identical(other.enableAIWhatsApp, enableAIWhatsApp) ||
                other.enableAIWhatsApp == enableAIWhatsApp) &&
            (identical(
                  other.enableAIPersonalization,
                  enableAIPersonalization,
                ) ||
                other.enableAIPersonalization == enableAIPersonalization) &&
            (identical(other.predictDefaultRisk, predictDefaultRisk) ||
                other.predictDefaultRisk == predictDefaultRisk) &&
            (identical(
                  other.predictBestCollectionTime,
                  predictBestCollectionTime,
                ) ||
                other.predictBestCollectionTime == predictBestCollectionTime) &&
            (identical(
                  other.predictCustomerResponse,
                  predictCustomerResponse,
                ) ||
                other.predictCustomerResponse == predictCustomerResponse) &&
            (identical(other.enableOCR, enableOCR) ||
                other.enableOCR == enableOCR) &&
            (identical(
                  other.enableAutoReceiptGeneration,
                  enableAutoReceiptGeneration,
                ) ||
                other.enableAutoReceiptGeneration ==
                    enableAutoReceiptGeneration) &&
            (identical(
                  other.enableFieldAgentAIAssistant,
                  enableFieldAgentAIAssistant,
                ) ||
                other.enableFieldAgentAIAssistant ==
                    enableFieldAgentAIAssistant) &&
            (identical(
                  other.enableCustomerAIChatbot,
                  enableCustomerAIChatbot,
                ) ||
                other.enableCustomerAIChatbot == enableCustomerAIChatbot));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    enableLeadScoring,
    autoAssignLeads,
    enableAIVoiceCalls,
    enableAIWhatsApp,
    enableAIPersonalization,
    predictDefaultRisk,
    predictBestCollectionTime,
    predictCustomerResponse,
    enableOCR,
    enableAutoReceiptGeneration,
    enableFieldAgentAIAssistant,
    enableCustomerAIChatbot,
  );

  /// Create a copy of AIConfig
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$AIConfigImplCopyWith<_$AIConfigImpl> get copyWith =>
      __$$AIConfigImplCopyWithImpl<_$AIConfigImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$AIConfigImplToJson(this);
  }
}

abstract class _AIConfig implements AIConfig {
  const factory _AIConfig({
    final bool enableLeadScoring,
    final bool autoAssignLeads,
    final bool enableAIVoiceCalls,
    final bool enableAIWhatsApp,
    final bool enableAIPersonalization,
    final bool predictDefaultRisk,
    final bool predictBestCollectionTime,
    final bool predictCustomerResponse,
    final bool enableOCR,
    final bool enableAutoReceiptGeneration,
    final bool enableFieldAgentAIAssistant,
    final bool enableCustomerAIChatbot,
  }) = _$AIConfigImpl;

  factory _AIConfig.fromJson(Map<String, dynamic> json) =
      _$AIConfigImpl.fromJson;

  // AI Lead Scoring
  @override
  bool get enableLeadScoring;
  @override
  bool get autoAssignLeads; // AI Communication
  @override
  bool get enableAIVoiceCalls;
  @override
  bool get enableAIWhatsApp;
  @override
  bool get enableAIPersonalization; // AI Prediction
  @override
  bool get predictDefaultRisk;
  @override
  bool get predictBestCollectionTime;
  @override
  bool get predictCustomerResponse; // AI Document Processing
  @override
  bool get enableOCR;
  @override
  bool get enableAutoReceiptGeneration; // AI Assistant
  @override
  bool get enableFieldAgentAIAssistant;
  @override
  bool get enableCustomerAIChatbot;

  /// Create a copy of AIConfig
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$AIConfigImplCopyWith<_$AIConfigImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

AutomationRule _$AutomationRuleFromJson(Map<String, dynamic> json) {
  return _AutomationRule.fromJson(json);
}

/// @nodoc
mixin _$AutomationRule {
  String get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get type =>
      throw _privateConstructorUsedError; // reminder, escalation, collection
  String get trigger =>
      throw _privateConstructorUsedError; // days_before_due, days_after_due, amount_threshold
  int get triggerValue =>
      throw _privateConstructorUsedError; // 3 (days), 5000 (amount)
  // Actions to take
  List<String> get actions =>
      throw _privateConstructorUsedError; // whatsapp, sms, email, call, agent_notify
  // Timing
  String get scheduleTime => throw _privateConstructorUsedError; // 09:00
  String? get scheduleDays =>
      throw _privateConstructorUsedError; // monday,tuesday,wednesday
  // Priority
  int get priority => throw _privateConstructorUsedError; // Conditions
  String? get conditionAmount => throw _privateConstructorUsedError; // > 10000
  String? get conditionStatus =>
      throw _privateConstructorUsedError; // regular, irregular, defaulter
  // Message templates
  String? get whatsappTemplate => throw _privateConstructorUsedError;
  String? get smsTemplate => throw _privateConstructorUsedError;
  String? get emailTemplate => throw _privateConstructorUsedError;
  String? get voiceMessage => throw _privateConstructorUsedError; // Status
  bool get isActive => throw _privateConstructorUsedError;
  DateTime get createdAt => throw _privateConstructorUsedError;

  /// Serializes this AutomationRule to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of AutomationRule
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $AutomationRuleCopyWith<AutomationRule> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $AutomationRuleCopyWith<$Res> {
  factory $AutomationRuleCopyWith(
    AutomationRule value,
    $Res Function(AutomationRule) then,
  ) = _$AutomationRuleCopyWithImpl<$Res, AutomationRule>;
  @useResult
  $Res call({
    String id,
    String name,
    String type,
    String trigger,
    int triggerValue,
    List<String> actions,
    String scheduleTime,
    String? scheduleDays,
    int priority,
    String? conditionAmount,
    String? conditionStatus,
    String? whatsappTemplate,
    String? smsTemplate,
    String? emailTemplate,
    String? voiceMessage,
    bool isActive,
    DateTime createdAt,
  });
}

/// @nodoc
class _$AutomationRuleCopyWithImpl<$Res, $Val extends AutomationRule>
    implements $AutomationRuleCopyWith<$Res> {
  _$AutomationRuleCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of AutomationRule
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? type = null,
    Object? trigger = null,
    Object? triggerValue = null,
    Object? actions = null,
    Object? scheduleTime = null,
    Object? scheduleDays = freezed,
    Object? priority = null,
    Object? conditionAmount = freezed,
    Object? conditionStatus = freezed,
    Object? whatsappTemplate = freezed,
    Object? smsTemplate = freezed,
    Object? emailTemplate = freezed,
    Object? voiceMessage = freezed,
    Object? isActive = null,
    Object? createdAt = null,
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
            type: null == type
                ? _value.type
                : type // ignore: cast_nullable_to_non_nullable
                      as String,
            trigger: null == trigger
                ? _value.trigger
                : trigger // ignore: cast_nullable_to_non_nullable
                      as String,
            triggerValue: null == triggerValue
                ? _value.triggerValue
                : triggerValue // ignore: cast_nullable_to_non_nullable
                      as int,
            actions: null == actions
                ? _value.actions
                : actions // ignore: cast_nullable_to_non_nullable
                      as List<String>,
            scheduleTime: null == scheduleTime
                ? _value.scheduleTime
                : scheduleTime // ignore: cast_nullable_to_non_nullable
                      as String,
            scheduleDays: freezed == scheduleDays
                ? _value.scheduleDays
                : scheduleDays // ignore: cast_nullable_to_non_nullable
                      as String?,
            priority: null == priority
                ? _value.priority
                : priority // ignore: cast_nullable_to_non_nullable
                      as int,
            conditionAmount: freezed == conditionAmount
                ? _value.conditionAmount
                : conditionAmount // ignore: cast_nullable_to_non_nullable
                      as String?,
            conditionStatus: freezed == conditionStatus
                ? _value.conditionStatus
                : conditionStatus // ignore: cast_nullable_to_non_nullable
                      as String?,
            whatsappTemplate: freezed == whatsappTemplate
                ? _value.whatsappTemplate
                : whatsappTemplate // ignore: cast_nullable_to_non_nullable
                      as String?,
            smsTemplate: freezed == smsTemplate
                ? _value.smsTemplate
                : smsTemplate // ignore: cast_nullable_to_non_nullable
                      as String?,
            emailTemplate: freezed == emailTemplate
                ? _value.emailTemplate
                : emailTemplate // ignore: cast_nullable_to_non_nullable
                      as String?,
            voiceMessage: freezed == voiceMessage
                ? _value.voiceMessage
                : voiceMessage // ignore: cast_nullable_to_non_nullable
                      as String?,
            isActive: null == isActive
                ? _value.isActive
                : isActive // ignore: cast_nullable_to_non_nullable
                      as bool,
            createdAt: null == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$AutomationRuleImplCopyWith<$Res>
    implements $AutomationRuleCopyWith<$Res> {
  factory _$$AutomationRuleImplCopyWith(
    _$AutomationRuleImpl value,
    $Res Function(_$AutomationRuleImpl) then,
  ) = __$$AutomationRuleImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String type,
    String trigger,
    int triggerValue,
    List<String> actions,
    String scheduleTime,
    String? scheduleDays,
    int priority,
    String? conditionAmount,
    String? conditionStatus,
    String? whatsappTemplate,
    String? smsTemplate,
    String? emailTemplate,
    String? voiceMessage,
    bool isActive,
    DateTime createdAt,
  });
}

/// @nodoc
class __$$AutomationRuleImplCopyWithImpl<$Res>
    extends _$AutomationRuleCopyWithImpl<$Res, _$AutomationRuleImpl>
    implements _$$AutomationRuleImplCopyWith<$Res> {
  __$$AutomationRuleImplCopyWithImpl(
    _$AutomationRuleImpl _value,
    $Res Function(_$AutomationRuleImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of AutomationRule
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? type = null,
    Object? trigger = null,
    Object? triggerValue = null,
    Object? actions = null,
    Object? scheduleTime = null,
    Object? scheduleDays = freezed,
    Object? priority = null,
    Object? conditionAmount = freezed,
    Object? conditionStatus = freezed,
    Object? whatsappTemplate = freezed,
    Object? smsTemplate = freezed,
    Object? emailTemplate = freezed,
    Object? voiceMessage = freezed,
    Object? isActive = null,
    Object? createdAt = null,
  }) {
    return _then(
      _$AutomationRuleImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _value.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _value.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        trigger: null == trigger
            ? _value.trigger
            : trigger // ignore: cast_nullable_to_non_nullable
                  as String,
        triggerValue: null == triggerValue
            ? _value.triggerValue
            : triggerValue // ignore: cast_nullable_to_non_nullable
                  as int,
        actions: null == actions
            ? _value._actions
            : actions // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        scheduleTime: null == scheduleTime
            ? _value.scheduleTime
            : scheduleTime // ignore: cast_nullable_to_non_nullable
                  as String,
        scheduleDays: freezed == scheduleDays
            ? _value.scheduleDays
            : scheduleDays // ignore: cast_nullable_to_non_nullable
                  as String?,
        priority: null == priority
            ? _value.priority
            : priority // ignore: cast_nullable_to_non_nullable
                  as int,
        conditionAmount: freezed == conditionAmount
            ? _value.conditionAmount
            : conditionAmount // ignore: cast_nullable_to_non_nullable
                  as String?,
        conditionStatus: freezed == conditionStatus
            ? _value.conditionStatus
            : conditionStatus // ignore: cast_nullable_to_non_nullable
                  as String?,
        whatsappTemplate: freezed == whatsappTemplate
            ? _value.whatsappTemplate
            : whatsappTemplate // ignore: cast_nullable_to_non_nullable
                  as String?,
        smsTemplate: freezed == smsTemplate
            ? _value.smsTemplate
            : smsTemplate // ignore: cast_nullable_to_non_nullable
                  as String?,
        emailTemplate: freezed == emailTemplate
            ? _value.emailTemplate
            : emailTemplate // ignore: cast_nullable_to_non_nullable
                  as String?,
        voiceMessage: freezed == voiceMessage
            ? _value.voiceMessage
            : voiceMessage // ignore: cast_nullable_to_non_nullable
                  as String?,
        isActive: null == isActive
            ? _value.isActive
            : isActive // ignore: cast_nullable_to_non_nullable
                  as bool,
        createdAt: null == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$AutomationRuleImpl implements _AutomationRule {
  const _$AutomationRuleImpl({
    required this.id,
    required this.name,
    required this.type,
    required this.trigger,
    required this.triggerValue,
    final List<String> actions = const [],
    required this.scheduleTime,
    this.scheduleDays,
    this.priority = 1,
    this.conditionAmount,
    this.conditionStatus,
    this.whatsappTemplate,
    this.smsTemplate,
    this.emailTemplate,
    this.voiceMessage,
    this.isActive = true,
    required this.createdAt,
  }) : _actions = actions;

  factory _$AutomationRuleImpl.fromJson(Map<String, dynamic> json) =>
      _$$AutomationRuleImplFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String type;
  // reminder, escalation, collection
  @override
  final String trigger;
  // days_before_due, days_after_due, amount_threshold
  @override
  final int triggerValue;
  // 3 (days), 5000 (amount)
  // Actions to take
  final List<String> _actions;
  // 3 (days), 5000 (amount)
  // Actions to take
  @override
  @JsonKey()
  List<String> get actions {
    if (_actions is EqualUnmodifiableListView) return _actions;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_actions);
  }

  // whatsapp, sms, email, call, agent_notify
  // Timing
  @override
  final String scheduleTime;
  // 09:00
  @override
  final String? scheduleDays;
  // monday,tuesday,wednesday
  // Priority
  @override
  @JsonKey()
  final int priority;
  // Conditions
  @override
  final String? conditionAmount;
  // > 10000
  @override
  final String? conditionStatus;
  // regular, irregular, defaulter
  // Message templates
  @override
  final String? whatsappTemplate;
  @override
  final String? smsTemplate;
  @override
  final String? emailTemplate;
  @override
  final String? voiceMessage;
  // Status
  @override
  @JsonKey()
  final bool isActive;
  @override
  final DateTime createdAt;

  @override
  String toString() {
    return 'AutomationRule(id: $id, name: $name, type: $type, trigger: $trigger, triggerValue: $triggerValue, actions: $actions, scheduleTime: $scheduleTime, scheduleDays: $scheduleDays, priority: $priority, conditionAmount: $conditionAmount, conditionStatus: $conditionStatus, whatsappTemplate: $whatsappTemplate, smsTemplate: $smsTemplate, emailTemplate: $emailTemplate, voiceMessage: $voiceMessage, isActive: $isActive, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$AutomationRuleImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.trigger, trigger) || other.trigger == trigger) &&
            (identical(other.triggerValue, triggerValue) ||
                other.triggerValue == triggerValue) &&
            const DeepCollectionEquality().equals(other._actions, _actions) &&
            (identical(other.scheduleTime, scheduleTime) ||
                other.scheduleTime == scheduleTime) &&
            (identical(other.scheduleDays, scheduleDays) ||
                other.scheduleDays == scheduleDays) &&
            (identical(other.priority, priority) ||
                other.priority == priority) &&
            (identical(other.conditionAmount, conditionAmount) ||
                other.conditionAmount == conditionAmount) &&
            (identical(other.conditionStatus, conditionStatus) ||
                other.conditionStatus == conditionStatus) &&
            (identical(other.whatsappTemplate, whatsappTemplate) ||
                other.whatsappTemplate == whatsappTemplate) &&
            (identical(other.smsTemplate, smsTemplate) ||
                other.smsTemplate == smsTemplate) &&
            (identical(other.emailTemplate, emailTemplate) ||
                other.emailTemplate == emailTemplate) &&
            (identical(other.voiceMessage, voiceMessage) ||
                other.voiceMessage == voiceMessage) &&
            (identical(other.isActive, isActive) ||
                other.isActive == isActive) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
    runtimeType,
    id,
    name,
    type,
    trigger,
    triggerValue,
    const DeepCollectionEquality().hash(_actions),
    scheduleTime,
    scheduleDays,
    priority,
    conditionAmount,
    conditionStatus,
    whatsappTemplate,
    smsTemplate,
    emailTemplate,
    voiceMessage,
    isActive,
    createdAt,
  );

  /// Create a copy of AutomationRule
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$AutomationRuleImplCopyWith<_$AutomationRuleImpl> get copyWith =>
      __$$AutomationRuleImplCopyWithImpl<_$AutomationRuleImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$AutomationRuleImplToJson(this);
  }
}

abstract class _AutomationRule implements AutomationRule {
  const factory _AutomationRule({
    required final String id,
    required final String name,
    required final String type,
    required final String trigger,
    required final int triggerValue,
    final List<String> actions,
    required final String scheduleTime,
    final String? scheduleDays,
    final int priority,
    final String? conditionAmount,
    final String? conditionStatus,
    final String? whatsappTemplate,
    final String? smsTemplate,
    final String? emailTemplate,
    final String? voiceMessage,
    final bool isActive,
    required final DateTime createdAt,
  }) = _$AutomationRuleImpl;

  factory _AutomationRule.fromJson(Map<String, dynamic> json) =
      _$AutomationRuleImpl.fromJson;

  @override
  String get id;
  @override
  String get name;
  @override
  String get type; // reminder, escalation, collection
  @override
  String get trigger; // days_before_due, days_after_due, amount_threshold
  @override
  int get triggerValue; // 3 (days), 5000 (amount)
  // Actions to take
  @override
  List<String> get actions; // whatsapp, sms, email, call, agent_notify
  // Timing
  @override
  String get scheduleTime; // 09:00
  @override
  String? get scheduleDays; // monday,tuesday,wednesday
  // Priority
  @override
  int get priority; // Conditions
  @override
  String? get conditionAmount; // > 10000
  @override
  String? get conditionStatus; // regular, irregular, defaulter
  // Message templates
  @override
  String? get whatsappTemplate;
  @override
  String? get smsTemplate;
  @override
  String? get emailTemplate;
  @override
  String? get voiceMessage; // Status
  @override
  bool get isActive;
  @override
  DateTime get createdAt;

  /// Create a copy of AutomationRule
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$AutomationRuleImplCopyWith<_$AutomationRuleImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

AutomationExecution _$AutomationExecutionFromJson(Map<String, dynamic> json) {
  return _AutomationExecution.fromJson(json);
}

/// @nodoc
mixin _$AutomationExecution {
  String get id => throw _privateConstructorUsedError;
  String get ruleId => throw _privateConstructorUsedError;
  String get ruleName => throw _privateConstructorUsedError;
  String get customerId => throw _privateConstructorUsedError;
  String get bookingId => throw _privateConstructorUsedError;
  String get emiId => throw _privateConstructorUsedError; // Execution details
  String get channel =>
      throw _privateConstructorUsedError; // whatsapp, sms, email, call, agent_app
  String get action =>
      throw _privateConstructorUsedError; // reminder_sent, call_made, agent_notified
  String get status =>
      throw _privateConstructorUsedError; // success, failed, pending, scheduled
  // Content
  String? get messageContent => throw _privateConstructorUsedError;
  String? get templateUsed => throw _privateConstructorUsedError;
  Map<String, dynamic>? get metadata =>
      throw _privateConstructorUsedError; // Timing
  DateTime get scheduledAt => throw _privateConstructorUsedError;
  DateTime? get executedAt => throw _privateConstructorUsedError;
  DateTime? get deliveredAt => throw _privateConstructorUsedError; // Response
  String? get customerResponse => throw _privateConstructorUsedError;
  DateTime? get responseAt => throw _privateConstructorUsedError;
  String? get responseType =>
      throw _privateConstructorUsedError; // will_pay, cannot_pay, asked_time, paid
  // Error
  String? get errorMessage => throw _privateConstructorUsedError;
  int? get retryCount => throw _privateConstructorUsedError;
  DateTime get createdAt => throw _privateConstructorUsedError;

  /// Serializes this AutomationExecution to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of AutomationExecution
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $AutomationExecutionCopyWith<AutomationExecution> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $AutomationExecutionCopyWith<$Res> {
  factory $AutomationExecutionCopyWith(
    AutomationExecution value,
    $Res Function(AutomationExecution) then,
  ) = _$AutomationExecutionCopyWithImpl<$Res, AutomationExecution>;
  @useResult
  $Res call({
    String id,
    String ruleId,
    String ruleName,
    String customerId,
    String bookingId,
    String emiId,
    String channel,
    String action,
    String status,
    String? messageContent,
    String? templateUsed,
    Map<String, dynamic>? metadata,
    DateTime scheduledAt,
    DateTime? executedAt,
    DateTime? deliveredAt,
    String? customerResponse,
    DateTime? responseAt,
    String? responseType,
    String? errorMessage,
    int? retryCount,
    DateTime createdAt,
  });
}

/// @nodoc
class _$AutomationExecutionCopyWithImpl<$Res, $Val extends AutomationExecution>
    implements $AutomationExecutionCopyWith<$Res> {
  _$AutomationExecutionCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of AutomationExecution
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? ruleId = null,
    Object? ruleName = null,
    Object? customerId = null,
    Object? bookingId = null,
    Object? emiId = null,
    Object? channel = null,
    Object? action = null,
    Object? status = null,
    Object? messageContent = freezed,
    Object? templateUsed = freezed,
    Object? metadata = freezed,
    Object? scheduledAt = null,
    Object? executedAt = freezed,
    Object? deliveredAt = freezed,
    Object? customerResponse = freezed,
    Object? responseAt = freezed,
    Object? responseType = freezed,
    Object? errorMessage = freezed,
    Object? retryCount = freezed,
    Object? createdAt = null,
  }) {
    return _then(
      _value.copyWith(
            id: null == id
                ? _value.id
                : id // ignore: cast_nullable_to_non_nullable
                      as String,
            ruleId: null == ruleId
                ? _value.ruleId
                : ruleId // ignore: cast_nullable_to_non_nullable
                      as String,
            ruleName: null == ruleName
                ? _value.ruleName
                : ruleName // ignore: cast_nullable_to_non_nullable
                      as String,
            customerId: null == customerId
                ? _value.customerId
                : customerId // ignore: cast_nullable_to_non_nullable
                      as String,
            bookingId: null == bookingId
                ? _value.bookingId
                : bookingId // ignore: cast_nullable_to_non_nullable
                      as String,
            emiId: null == emiId
                ? _value.emiId
                : emiId // ignore: cast_nullable_to_non_nullable
                      as String,
            channel: null == channel
                ? _value.channel
                : channel // ignore: cast_nullable_to_non_nullable
                      as String,
            action: null == action
                ? _value.action
                : action // ignore: cast_nullable_to_non_nullable
                      as String,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as String,
            messageContent: freezed == messageContent
                ? _value.messageContent
                : messageContent // ignore: cast_nullable_to_non_nullable
                      as String?,
            templateUsed: freezed == templateUsed
                ? _value.templateUsed
                : templateUsed // ignore: cast_nullable_to_non_nullable
                      as String?,
            metadata: freezed == metadata
                ? _value.metadata
                : metadata // ignore: cast_nullable_to_non_nullable
                      as Map<String, dynamic>?,
            scheduledAt: null == scheduledAt
                ? _value.scheduledAt
                : scheduledAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
            executedAt: freezed == executedAt
                ? _value.executedAt
                : executedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            deliveredAt: freezed == deliveredAt
                ? _value.deliveredAt
                : deliveredAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            customerResponse: freezed == customerResponse
                ? _value.customerResponse
                : customerResponse // ignore: cast_nullable_to_non_nullable
                      as String?,
            responseAt: freezed == responseAt
                ? _value.responseAt
                : responseAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            responseType: freezed == responseType
                ? _value.responseType
                : responseType // ignore: cast_nullable_to_non_nullable
                      as String?,
            errorMessage: freezed == errorMessage
                ? _value.errorMessage
                : errorMessage // ignore: cast_nullable_to_non_nullable
                      as String?,
            retryCount: freezed == retryCount
                ? _value.retryCount
                : retryCount // ignore: cast_nullable_to_non_nullable
                      as int?,
            createdAt: null == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$AutomationExecutionImplCopyWith<$Res>
    implements $AutomationExecutionCopyWith<$Res> {
  factory _$$AutomationExecutionImplCopyWith(
    _$AutomationExecutionImpl value,
    $Res Function(_$AutomationExecutionImpl) then,
  ) = __$$AutomationExecutionImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String ruleId,
    String ruleName,
    String customerId,
    String bookingId,
    String emiId,
    String channel,
    String action,
    String status,
    String? messageContent,
    String? templateUsed,
    Map<String, dynamic>? metadata,
    DateTime scheduledAt,
    DateTime? executedAt,
    DateTime? deliveredAt,
    String? customerResponse,
    DateTime? responseAt,
    String? responseType,
    String? errorMessage,
    int? retryCount,
    DateTime createdAt,
  });
}

/// @nodoc
class __$$AutomationExecutionImplCopyWithImpl<$Res>
    extends _$AutomationExecutionCopyWithImpl<$Res, _$AutomationExecutionImpl>
    implements _$$AutomationExecutionImplCopyWith<$Res> {
  __$$AutomationExecutionImplCopyWithImpl(
    _$AutomationExecutionImpl _value,
    $Res Function(_$AutomationExecutionImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of AutomationExecution
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? ruleId = null,
    Object? ruleName = null,
    Object? customerId = null,
    Object? bookingId = null,
    Object? emiId = null,
    Object? channel = null,
    Object? action = null,
    Object? status = null,
    Object? messageContent = freezed,
    Object? templateUsed = freezed,
    Object? metadata = freezed,
    Object? scheduledAt = null,
    Object? executedAt = freezed,
    Object? deliveredAt = freezed,
    Object? customerResponse = freezed,
    Object? responseAt = freezed,
    Object? responseType = freezed,
    Object? errorMessage = freezed,
    Object? retryCount = freezed,
    Object? createdAt = null,
  }) {
    return _then(
      _$AutomationExecutionImpl(
        id: null == id
            ? _value.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        ruleId: null == ruleId
            ? _value.ruleId
            : ruleId // ignore: cast_nullable_to_non_nullable
                  as String,
        ruleName: null == ruleName
            ? _value.ruleName
            : ruleName // ignore: cast_nullable_to_non_nullable
                  as String,
        customerId: null == customerId
            ? _value.customerId
            : customerId // ignore: cast_nullable_to_non_nullable
                  as String,
        bookingId: null == bookingId
            ? _value.bookingId
            : bookingId // ignore: cast_nullable_to_non_nullable
                  as String,
        emiId: null == emiId
            ? _value.emiId
            : emiId // ignore: cast_nullable_to_non_nullable
                  as String,
        channel: null == channel
            ? _value.channel
            : channel // ignore: cast_nullable_to_non_nullable
                  as String,
        action: null == action
            ? _value.action
            : action // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        messageContent: freezed == messageContent
            ? _value.messageContent
            : messageContent // ignore: cast_nullable_to_non_nullable
                  as String?,
        templateUsed: freezed == templateUsed
            ? _value.templateUsed
            : templateUsed // ignore: cast_nullable_to_non_nullable
                  as String?,
        metadata: freezed == metadata
            ? _value._metadata
            : metadata // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>?,
        scheduledAt: null == scheduledAt
            ? _value.scheduledAt
            : scheduledAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        executedAt: freezed == executedAt
            ? _value.executedAt
            : executedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        deliveredAt: freezed == deliveredAt
            ? _value.deliveredAt
            : deliveredAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        customerResponse: freezed == customerResponse
            ? _value.customerResponse
            : customerResponse // ignore: cast_nullable_to_non_nullable
                  as String?,
        responseAt: freezed == responseAt
            ? _value.responseAt
            : responseAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        responseType: freezed == responseType
            ? _value.responseType
            : responseType // ignore: cast_nullable_to_non_nullable
                  as String?,
        errorMessage: freezed == errorMessage
            ? _value.errorMessage
            : errorMessage // ignore: cast_nullable_to_non_nullable
                  as String?,
        retryCount: freezed == retryCount
            ? _value.retryCount
            : retryCount // ignore: cast_nullable_to_non_nullable
                  as int?,
        createdAt: null == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$AutomationExecutionImpl implements _AutomationExecution {
  const _$AutomationExecutionImpl({
    required this.id,
    required this.ruleId,
    required this.ruleName,
    required this.customerId,
    required this.bookingId,
    required this.emiId,
    required this.channel,
    required this.action,
    required this.status,
    this.messageContent,
    this.templateUsed,
    final Map<String, dynamic>? metadata,
    required this.scheduledAt,
    this.executedAt,
    this.deliveredAt,
    this.customerResponse,
    this.responseAt,
    this.responseType,
    this.errorMessage,
    this.retryCount,
    required this.createdAt,
  }) : _metadata = metadata;

  factory _$AutomationExecutionImpl.fromJson(Map<String, dynamic> json) =>
      _$$AutomationExecutionImplFromJson(json);

  @override
  final String id;
  @override
  final String ruleId;
  @override
  final String ruleName;
  @override
  final String customerId;
  @override
  final String bookingId;
  @override
  final String emiId;
  // Execution details
  @override
  final String channel;
  // whatsapp, sms, email, call, agent_app
  @override
  final String action;
  // reminder_sent, call_made, agent_notified
  @override
  final String status;
  // success, failed, pending, scheduled
  // Content
  @override
  final String? messageContent;
  @override
  final String? templateUsed;
  final Map<String, dynamic>? _metadata;
  @override
  Map<String, dynamic>? get metadata {
    final value = _metadata;
    if (value == null) return null;
    if (_metadata is EqualUnmodifiableMapView) return _metadata;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(value);
  }

  // Timing
  @override
  final DateTime scheduledAt;
  @override
  final DateTime? executedAt;
  @override
  final DateTime? deliveredAt;
  // Response
  @override
  final String? customerResponse;
  @override
  final DateTime? responseAt;
  @override
  final String? responseType;
  // will_pay, cannot_pay, asked_time, paid
  // Error
  @override
  final String? errorMessage;
  @override
  final int? retryCount;
  @override
  final DateTime createdAt;

  @override
  String toString() {
    return 'AutomationExecution(id: $id, ruleId: $ruleId, ruleName: $ruleName, customerId: $customerId, bookingId: $bookingId, emiId: $emiId, channel: $channel, action: $action, status: $status, messageContent: $messageContent, templateUsed: $templateUsed, metadata: $metadata, scheduledAt: $scheduledAt, executedAt: $executedAt, deliveredAt: $deliveredAt, customerResponse: $customerResponse, responseAt: $responseAt, responseType: $responseType, errorMessage: $errorMessage, retryCount: $retryCount, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$AutomationExecutionImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.ruleId, ruleId) || other.ruleId == ruleId) &&
            (identical(other.ruleName, ruleName) ||
                other.ruleName == ruleName) &&
            (identical(other.customerId, customerId) ||
                other.customerId == customerId) &&
            (identical(other.bookingId, bookingId) ||
                other.bookingId == bookingId) &&
            (identical(other.emiId, emiId) || other.emiId == emiId) &&
            (identical(other.channel, channel) || other.channel == channel) &&
            (identical(other.action, action) || other.action == action) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.messageContent, messageContent) ||
                other.messageContent == messageContent) &&
            (identical(other.templateUsed, templateUsed) ||
                other.templateUsed == templateUsed) &&
            const DeepCollectionEquality().equals(other._metadata, _metadata) &&
            (identical(other.scheduledAt, scheduledAt) ||
                other.scheduledAt == scheduledAt) &&
            (identical(other.executedAt, executedAt) ||
                other.executedAt == executedAt) &&
            (identical(other.deliveredAt, deliveredAt) ||
                other.deliveredAt == deliveredAt) &&
            (identical(other.customerResponse, customerResponse) ||
                other.customerResponse == customerResponse) &&
            (identical(other.responseAt, responseAt) ||
                other.responseAt == responseAt) &&
            (identical(other.responseType, responseType) ||
                other.responseType == responseType) &&
            (identical(other.errorMessage, errorMessage) ||
                other.errorMessage == errorMessage) &&
            (identical(other.retryCount, retryCount) ||
                other.retryCount == retryCount) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    id,
    ruleId,
    ruleName,
    customerId,
    bookingId,
    emiId,
    channel,
    action,
    status,
    messageContent,
    templateUsed,
    const DeepCollectionEquality().hash(_metadata),
    scheduledAt,
    executedAt,
    deliveredAt,
    customerResponse,
    responseAt,
    responseType,
    errorMessage,
    retryCount,
    createdAt,
  ]);

  /// Create a copy of AutomationExecution
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$AutomationExecutionImplCopyWith<_$AutomationExecutionImpl> get copyWith =>
      __$$AutomationExecutionImplCopyWithImpl<_$AutomationExecutionImpl>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$$AutomationExecutionImplToJson(this);
  }
}

abstract class _AutomationExecution implements AutomationExecution {
  const factory _AutomationExecution({
    required final String id,
    required final String ruleId,
    required final String ruleName,
    required final String customerId,
    required final String bookingId,
    required final String emiId,
    required final String channel,
    required final String action,
    required final String status,
    final String? messageContent,
    final String? templateUsed,
    final Map<String, dynamic>? metadata,
    required final DateTime scheduledAt,
    final DateTime? executedAt,
    final DateTime? deliveredAt,
    final String? customerResponse,
    final DateTime? responseAt,
    final String? responseType,
    final String? errorMessage,
    final int? retryCount,
    required final DateTime createdAt,
  }) = _$AutomationExecutionImpl;

  factory _AutomationExecution.fromJson(Map<String, dynamic> json) =
      _$AutomationExecutionImpl.fromJson;

  @override
  String get id;
  @override
  String get ruleId;
  @override
  String get ruleName;
  @override
  String get customerId;
  @override
  String get bookingId;
  @override
  String get emiId; // Execution details
  @override
  String get channel; // whatsapp, sms, email, call, agent_app
  @override
  String get action; // reminder_sent, call_made, agent_notified
  @override
  String get status; // success, failed, pending, scheduled
  // Content
  @override
  String? get messageContent;
  @override
  String? get templateUsed;
  @override
  Map<String, dynamic>? get metadata; // Timing
  @override
  DateTime get scheduledAt;
  @override
  DateTime? get executedAt;
  @override
  DateTime? get deliveredAt; // Response
  @override
  String? get customerResponse;
  @override
  DateTime? get responseAt;
  @override
  String? get responseType; // will_pay, cannot_pay, asked_time, paid
  // Error
  @override
  String? get errorMessage;
  @override
  int? get retryCount;
  @override
  DateTime get createdAt;

  /// Create a copy of AutomationExecution
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$AutomationExecutionImplCopyWith<_$AutomationExecutionImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

CustomerCommunicationLog _$CustomerCommunicationLogFromJson(
  Map<String, dynamic> json,
) {
  return _CustomerCommunicationLog.fromJson(json);
}

/// @nodoc
mixin _$CustomerCommunicationLog {
  String get id => throw _privateConstructorUsedError;
  String get customerId => throw _privateConstructorUsedError;
  String get bookingId =>
      throw _privateConstructorUsedError; // Communication details
  String get channel =>
      throw _privateConstructorUsedError; // whatsapp, sms, email, call, agent_visit
  String get direction =>
      throw _privateConstructorUsedError; // outgoing, incoming
  String get type =>
      throw _privateConstructorUsedError; // reminder, follow_up, payment_confirmation, enquiry
  // Content
  String? get message => throw _privateConstructorUsedError;
  String? get attachmentUrl => throw _privateConstructorUsedError;
  String? get callRecordingUrl => throw _privateConstructorUsedError;
  int? get callDurationSeconds => throw _privateConstructorUsedError; // Status
  String get status =>
      throw _privateConstructorUsedError; // sent, delivered, read, failed
  DateTime? get sentAt => throw _privateConstructorUsedError;
  DateTime? get deliveredAt => throw _privateConstructorUsedError;
  DateTime? get readAt =>
      throw _privateConstructorUsedError; // Agent info (if agent initiated)
  String? get agentId => throw _privateConstructorUsedError;
  String? get agentName =>
      throw _privateConstructorUsedError; // AI/Automation info
  bool get wasAutomated => throw _privateConstructorUsedError;
  String? get automationRuleId =>
      throw _privateConstructorUsedError; // Customer response
  String? get customerReply => throw _privateConstructorUsedError;
  DateTime? get repliedAt => throw _privateConstructorUsedError; // Notes
  String? get adminNotes => throw _privateConstructorUsedError;
  DateTime get createdAt => throw _privateConstructorUsedError;

  /// Serializes this CustomerCommunicationLog to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of CustomerCommunicationLog
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $CustomerCommunicationLogCopyWith<CustomerCommunicationLog> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $CustomerCommunicationLogCopyWith<$Res> {
  factory $CustomerCommunicationLogCopyWith(
    CustomerCommunicationLog value,
    $Res Function(CustomerCommunicationLog) then,
  ) = _$CustomerCommunicationLogCopyWithImpl<$Res, CustomerCommunicationLog>;
  @useResult
  $Res call({
    String id,
    String customerId,
    String bookingId,
    String channel,
    String direction,
    String type,
    String? message,
    String? attachmentUrl,
    String? callRecordingUrl,
    int? callDurationSeconds,
    String status,
    DateTime? sentAt,
    DateTime? deliveredAt,
    DateTime? readAt,
    String? agentId,
    String? agentName,
    bool wasAutomated,
    String? automationRuleId,
    String? customerReply,
    DateTime? repliedAt,
    String? adminNotes,
    DateTime createdAt,
  });
}

/// @nodoc
class _$CustomerCommunicationLogCopyWithImpl<
  $Res,
  $Val extends CustomerCommunicationLog
>
    implements $CustomerCommunicationLogCopyWith<$Res> {
  _$CustomerCommunicationLogCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of CustomerCommunicationLog
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? customerId = null,
    Object? bookingId = null,
    Object? channel = null,
    Object? direction = null,
    Object? type = null,
    Object? message = freezed,
    Object? attachmentUrl = freezed,
    Object? callRecordingUrl = freezed,
    Object? callDurationSeconds = freezed,
    Object? status = null,
    Object? sentAt = freezed,
    Object? deliveredAt = freezed,
    Object? readAt = freezed,
    Object? agentId = freezed,
    Object? agentName = freezed,
    Object? wasAutomated = null,
    Object? automationRuleId = freezed,
    Object? customerReply = freezed,
    Object? repliedAt = freezed,
    Object? adminNotes = freezed,
    Object? createdAt = null,
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
            channel: null == channel
                ? _value.channel
                : channel // ignore: cast_nullable_to_non_nullable
                      as String,
            direction: null == direction
                ? _value.direction
                : direction // ignore: cast_nullable_to_non_nullable
                      as String,
            type: null == type
                ? _value.type
                : type // ignore: cast_nullable_to_non_nullable
                      as String,
            message: freezed == message
                ? _value.message
                : message // ignore: cast_nullable_to_non_nullable
                      as String?,
            attachmentUrl: freezed == attachmentUrl
                ? _value.attachmentUrl
                : attachmentUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            callRecordingUrl: freezed == callRecordingUrl
                ? _value.callRecordingUrl
                : callRecordingUrl // ignore: cast_nullable_to_non_nullable
                      as String?,
            callDurationSeconds: freezed == callDurationSeconds
                ? _value.callDurationSeconds
                : callDurationSeconds // ignore: cast_nullable_to_non_nullable
                      as int?,
            status: null == status
                ? _value.status
                : status // ignore: cast_nullable_to_non_nullable
                      as String,
            sentAt: freezed == sentAt
                ? _value.sentAt
                : sentAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            deliveredAt: freezed == deliveredAt
                ? _value.deliveredAt
                : deliveredAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            readAt: freezed == readAt
                ? _value.readAt
                : readAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            agentId: freezed == agentId
                ? _value.agentId
                : agentId // ignore: cast_nullable_to_non_nullable
                      as String?,
            agentName: freezed == agentName
                ? _value.agentName
                : agentName // ignore: cast_nullable_to_non_nullable
                      as String?,
            wasAutomated: null == wasAutomated
                ? _value.wasAutomated
                : wasAutomated // ignore: cast_nullable_to_non_nullable
                      as bool,
            automationRuleId: freezed == automationRuleId
                ? _value.automationRuleId
                : automationRuleId // ignore: cast_nullable_to_non_nullable
                      as String?,
            customerReply: freezed == customerReply
                ? _value.customerReply
                : customerReply // ignore: cast_nullable_to_non_nullable
                      as String?,
            repliedAt: freezed == repliedAt
                ? _value.repliedAt
                : repliedAt // ignore: cast_nullable_to_non_nullable
                      as DateTime?,
            adminNotes: freezed == adminNotes
                ? _value.adminNotes
                : adminNotes // ignore: cast_nullable_to_non_nullable
                      as String?,
            createdAt: null == createdAt
                ? _value.createdAt
                : createdAt // ignore: cast_nullable_to_non_nullable
                      as DateTime,
          )
          as $Val,
    );
  }
}

/// @nodoc
abstract class _$$CustomerCommunicationLogImplCopyWith<$Res>
    implements $CustomerCommunicationLogCopyWith<$Res> {
  factory _$$CustomerCommunicationLogImplCopyWith(
    _$CustomerCommunicationLogImpl value,
    $Res Function(_$CustomerCommunicationLogImpl) then,
  ) = __$$CustomerCommunicationLogImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({
    String id,
    String customerId,
    String bookingId,
    String channel,
    String direction,
    String type,
    String? message,
    String? attachmentUrl,
    String? callRecordingUrl,
    int? callDurationSeconds,
    String status,
    DateTime? sentAt,
    DateTime? deliveredAt,
    DateTime? readAt,
    String? agentId,
    String? agentName,
    bool wasAutomated,
    String? automationRuleId,
    String? customerReply,
    DateTime? repliedAt,
    String? adminNotes,
    DateTime createdAt,
  });
}

/// @nodoc
class __$$CustomerCommunicationLogImplCopyWithImpl<$Res>
    extends
        _$CustomerCommunicationLogCopyWithImpl<
          $Res,
          _$CustomerCommunicationLogImpl
        >
    implements _$$CustomerCommunicationLogImplCopyWith<$Res> {
  __$$CustomerCommunicationLogImplCopyWithImpl(
    _$CustomerCommunicationLogImpl _value,
    $Res Function(_$CustomerCommunicationLogImpl) _then,
  ) : super(_value, _then);

  /// Create a copy of CustomerCommunicationLog
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? customerId = null,
    Object? bookingId = null,
    Object? channel = null,
    Object? direction = null,
    Object? type = null,
    Object? message = freezed,
    Object? attachmentUrl = freezed,
    Object? callRecordingUrl = freezed,
    Object? callDurationSeconds = freezed,
    Object? status = null,
    Object? sentAt = freezed,
    Object? deliveredAt = freezed,
    Object? readAt = freezed,
    Object? agentId = freezed,
    Object? agentName = freezed,
    Object? wasAutomated = null,
    Object? automationRuleId = freezed,
    Object? customerReply = freezed,
    Object? repliedAt = freezed,
    Object? adminNotes = freezed,
    Object? createdAt = null,
  }) {
    return _then(
      _$CustomerCommunicationLogImpl(
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
        channel: null == channel
            ? _value.channel
            : channel // ignore: cast_nullable_to_non_nullable
                  as String,
        direction: null == direction
            ? _value.direction
            : direction // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _value.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        message: freezed == message
            ? _value.message
            : message // ignore: cast_nullable_to_non_nullable
                  as String?,
        attachmentUrl: freezed == attachmentUrl
            ? _value.attachmentUrl
            : attachmentUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        callRecordingUrl: freezed == callRecordingUrl
            ? _value.callRecordingUrl
            : callRecordingUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        callDurationSeconds: freezed == callDurationSeconds
            ? _value.callDurationSeconds
            : callDurationSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        status: null == status
            ? _value.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        sentAt: freezed == sentAt
            ? _value.sentAt
            : sentAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        deliveredAt: freezed == deliveredAt
            ? _value.deliveredAt
            : deliveredAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        readAt: freezed == readAt
            ? _value.readAt
            : readAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        agentId: freezed == agentId
            ? _value.agentId
            : agentId // ignore: cast_nullable_to_non_nullable
                  as String?,
        agentName: freezed == agentName
            ? _value.agentName
            : agentName // ignore: cast_nullable_to_non_nullable
                  as String?,
        wasAutomated: null == wasAutomated
            ? _value.wasAutomated
            : wasAutomated // ignore: cast_nullable_to_non_nullable
                  as bool,
        automationRuleId: freezed == automationRuleId
            ? _value.automationRuleId
            : automationRuleId // ignore: cast_nullable_to_non_nullable
                  as String?,
        customerReply: freezed == customerReply
            ? _value.customerReply
            : customerReply // ignore: cast_nullable_to_non_nullable
                  as String?,
        repliedAt: freezed == repliedAt
            ? _value.repliedAt
            : repliedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        adminNotes: freezed == adminNotes
            ? _value.adminNotes
            : adminNotes // ignore: cast_nullable_to_non_nullable
                  as String?,
        createdAt: null == createdAt
            ? _value.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// @nodoc
@JsonSerializable()
class _$CustomerCommunicationLogImpl implements _CustomerCommunicationLog {
  const _$CustomerCommunicationLogImpl({
    required this.id,
    required this.customerId,
    required this.bookingId,
    required this.channel,
    required this.direction,
    required this.type,
    this.message,
    this.attachmentUrl,
    this.callRecordingUrl,
    this.callDurationSeconds,
    required this.status,
    this.sentAt,
    this.deliveredAt,
    this.readAt,
    this.agentId,
    this.agentName,
    this.wasAutomated = false,
    this.automationRuleId,
    this.customerReply,
    this.repliedAt,
    this.adminNotes,
    required this.createdAt,
  });

  factory _$CustomerCommunicationLogImpl.fromJson(Map<String, dynamic> json) =>
      _$$CustomerCommunicationLogImplFromJson(json);

  @override
  final String id;
  @override
  final String customerId;
  @override
  final String bookingId;
  // Communication details
  @override
  final String channel;
  // whatsapp, sms, email, call, agent_visit
  @override
  final String direction;
  // outgoing, incoming
  @override
  final String type;
  // reminder, follow_up, payment_confirmation, enquiry
  // Content
  @override
  final String? message;
  @override
  final String? attachmentUrl;
  @override
  final String? callRecordingUrl;
  @override
  final int? callDurationSeconds;
  // Status
  @override
  final String status;
  // sent, delivered, read, failed
  @override
  final DateTime? sentAt;
  @override
  final DateTime? deliveredAt;
  @override
  final DateTime? readAt;
  // Agent info (if agent initiated)
  @override
  final String? agentId;
  @override
  final String? agentName;
  // AI/Automation info
  @override
  @JsonKey()
  final bool wasAutomated;
  @override
  final String? automationRuleId;
  // Customer response
  @override
  final String? customerReply;
  @override
  final DateTime? repliedAt;
  // Notes
  @override
  final String? adminNotes;
  @override
  final DateTime createdAt;

  @override
  String toString() {
    return 'CustomerCommunicationLog(id: $id, customerId: $customerId, bookingId: $bookingId, channel: $channel, direction: $direction, type: $type, message: $message, attachmentUrl: $attachmentUrl, callRecordingUrl: $callRecordingUrl, callDurationSeconds: $callDurationSeconds, status: $status, sentAt: $sentAt, deliveredAt: $deliveredAt, readAt: $readAt, agentId: $agentId, agentName: $agentName, wasAutomated: $wasAutomated, automationRuleId: $automationRuleId, customerReply: $customerReply, repliedAt: $repliedAt, adminNotes: $adminNotes, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$CustomerCommunicationLogImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.customerId, customerId) ||
                other.customerId == customerId) &&
            (identical(other.bookingId, bookingId) ||
                other.bookingId == bookingId) &&
            (identical(other.channel, channel) || other.channel == channel) &&
            (identical(other.direction, direction) ||
                other.direction == direction) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.message, message) || other.message == message) &&
            (identical(other.attachmentUrl, attachmentUrl) ||
                other.attachmentUrl == attachmentUrl) &&
            (identical(other.callRecordingUrl, callRecordingUrl) ||
                other.callRecordingUrl == callRecordingUrl) &&
            (identical(other.callDurationSeconds, callDurationSeconds) ||
                other.callDurationSeconds == callDurationSeconds) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.sentAt, sentAt) || other.sentAt == sentAt) &&
            (identical(other.deliveredAt, deliveredAt) ||
                other.deliveredAt == deliveredAt) &&
            (identical(other.readAt, readAt) || other.readAt == readAt) &&
            (identical(other.agentId, agentId) || other.agentId == agentId) &&
            (identical(other.agentName, agentName) ||
                other.agentName == agentName) &&
            (identical(other.wasAutomated, wasAutomated) ||
                other.wasAutomated == wasAutomated) &&
            (identical(other.automationRuleId, automationRuleId) ||
                other.automationRuleId == automationRuleId) &&
            (identical(other.customerReply, customerReply) ||
                other.customerReply == customerReply) &&
            (identical(other.repliedAt, repliedAt) ||
                other.repliedAt == repliedAt) &&
            (identical(other.adminNotes, adminNotes) ||
                other.adminNotes == adminNotes) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
    runtimeType,
    id,
    customerId,
    bookingId,
    channel,
    direction,
    type,
    message,
    attachmentUrl,
    callRecordingUrl,
    callDurationSeconds,
    status,
    sentAt,
    deliveredAt,
    readAt,
    agentId,
    agentName,
    wasAutomated,
    automationRuleId,
    customerReply,
    repliedAt,
    adminNotes,
    createdAt,
  ]);

  /// Create a copy of CustomerCommunicationLog
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$CustomerCommunicationLogImplCopyWith<_$CustomerCommunicationLogImpl>
  get copyWith =>
      __$$CustomerCommunicationLogImplCopyWithImpl<
        _$CustomerCommunicationLogImpl
      >(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$CustomerCommunicationLogImplToJson(this);
  }
}

abstract class _CustomerCommunicationLog implements CustomerCommunicationLog {
  const factory _CustomerCommunicationLog({
    required final String id,
    required final String customerId,
    required final String bookingId,
    required final String channel,
    required final String direction,
    required final String type,
    final String? message,
    final String? attachmentUrl,
    final String? callRecordingUrl,
    final int? callDurationSeconds,
    required final String status,
    final DateTime? sentAt,
    final DateTime? deliveredAt,
    final DateTime? readAt,
    final String? agentId,
    final String? agentName,
    final bool wasAutomated,
    final String? automationRuleId,
    final String? customerReply,
    final DateTime? repliedAt,
    final String? adminNotes,
    required final DateTime createdAt,
  }) = _$CustomerCommunicationLogImpl;

  factory _CustomerCommunicationLog.fromJson(Map<String, dynamic> json) =
      _$CustomerCommunicationLogImpl.fromJson;

  @override
  String get id;
  @override
  String get customerId;
  @override
  String get bookingId; // Communication details
  @override
  String get channel; // whatsapp, sms, email, call, agent_visit
  @override
  String get direction; // outgoing, incoming
  @override
  String get type; // reminder, follow_up, payment_confirmation, enquiry
  // Content
  @override
  String? get message;
  @override
  String? get attachmentUrl;
  @override
  String? get callRecordingUrl;
  @override
  int? get callDurationSeconds; // Status
  @override
  String get status; // sent, delivered, read, failed
  @override
  DateTime? get sentAt;
  @override
  DateTime? get deliveredAt;
  @override
  DateTime? get readAt; // Agent info (if agent initiated)
  @override
  String? get agentId;
  @override
  String? get agentName; // AI/Automation info
  @override
  bool get wasAutomated;
  @override
  String? get automationRuleId; // Customer response
  @override
  String? get customerReply;
  @override
  DateTime? get repliedAt; // Notes
  @override
  String? get adminNotes;
  @override
  DateTime get createdAt;

  /// Create a copy of CustomerCommunicationLog
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$CustomerCommunicationLogImplCopyWith<_$CustomerCommunicationLogImpl>
  get copyWith => throw _privateConstructorUsedError;
}
