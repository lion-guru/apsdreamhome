// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'emi_automation_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$EMIAutomationConfig {

 String get id; String get companyId; String get companyName;// WhatsApp Business Configuration
 WhatsAppConfig get whatsappConfig;// Voice Call Configuration (IVR/Cloud telephony)
 VoiceCallConfig get voiceCallConfig;// SMS Gateway Configuration
 SMSConfig get smsConfig;// Email Configuration
 EmailConfig get emailConfig;// Automation Rules
 List<AutomationRule> get reminderRules; List<AutomationRule> get escalationRules; List<AutomationRule> get collectionRules;// Field Agent Settings
 FieldAgentConfig get fieldAgentConfig;// AI/ML Settings
 AIConfig get aiConfig; bool get isActive; DateTime get createdAt; DateTime get updatedAt;
/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$EMIAutomationConfigCopyWith<EMIAutomationConfig> get copyWith => _$EMIAutomationConfigCopyWithImpl<EMIAutomationConfig>(this as EMIAutomationConfig, _$identity);

  /// Serializes this EMIAutomationConfig to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is EMIAutomationConfig&&(identical(other.id, id) || other.id == id)&&(identical(other.companyId, companyId) || other.companyId == companyId)&&(identical(other.companyName, companyName) || other.companyName == companyName)&&(identical(other.whatsappConfig, whatsappConfig) || other.whatsappConfig == whatsappConfig)&&(identical(other.voiceCallConfig, voiceCallConfig) || other.voiceCallConfig == voiceCallConfig)&&(identical(other.smsConfig, smsConfig) || other.smsConfig == smsConfig)&&(identical(other.emailConfig, emailConfig) || other.emailConfig == emailConfig)&&const DeepCollectionEquality().equals(other.reminderRules, reminderRules)&&const DeepCollectionEquality().equals(other.escalationRules, escalationRules)&&const DeepCollectionEquality().equals(other.collectionRules, collectionRules)&&(identical(other.fieldAgentConfig, fieldAgentConfig) || other.fieldAgentConfig == fieldAgentConfig)&&(identical(other.aiConfig, aiConfig) || other.aiConfig == aiConfig)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,companyId,companyName,whatsappConfig,voiceCallConfig,smsConfig,emailConfig,const DeepCollectionEquality().hash(reminderRules),const DeepCollectionEquality().hash(escalationRules),const DeepCollectionEquality().hash(collectionRules),fieldAgentConfig,aiConfig,isActive,createdAt,updatedAt);

@override
String toString() {
  return 'EMIAutomationConfig(id: $id, companyId: $companyId, companyName: $companyName, whatsappConfig: $whatsappConfig, voiceCallConfig: $voiceCallConfig, smsConfig: $smsConfig, emailConfig: $emailConfig, reminderRules: $reminderRules, escalationRules: $escalationRules, collectionRules: $collectionRules, fieldAgentConfig: $fieldAgentConfig, aiConfig: $aiConfig, isActive: $isActive, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class $EMIAutomationConfigCopyWith<$Res>  {
  factory $EMIAutomationConfigCopyWith(EMIAutomationConfig value, $Res Function(EMIAutomationConfig) _then) = _$EMIAutomationConfigCopyWithImpl;
@useResult
$Res call({
 String id, String companyId, String companyName, WhatsAppConfig whatsappConfig, VoiceCallConfig voiceCallConfig, SMSConfig smsConfig, EmailConfig emailConfig, List<AutomationRule> reminderRules, List<AutomationRule> escalationRules, List<AutomationRule> collectionRules, FieldAgentConfig fieldAgentConfig, AIConfig aiConfig, bool isActive, DateTime createdAt, DateTime updatedAt
});


$WhatsAppConfigCopyWith<$Res> get whatsappConfig;$VoiceCallConfigCopyWith<$Res> get voiceCallConfig;$SMSConfigCopyWith<$Res> get smsConfig;$EmailConfigCopyWith<$Res> get emailConfig;$FieldAgentConfigCopyWith<$Res> get fieldAgentConfig;$AIConfigCopyWith<$Res> get aiConfig;

}
/// @nodoc
class _$EMIAutomationConfigCopyWithImpl<$Res>
    implements $EMIAutomationConfigCopyWith<$Res> {
  _$EMIAutomationConfigCopyWithImpl(this._self, this._then);

  final EMIAutomationConfig _self;
  final $Res Function(EMIAutomationConfig) _then;

/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? companyId = null,Object? companyName = null,Object? whatsappConfig = null,Object? voiceCallConfig = null,Object? smsConfig = null,Object? emailConfig = null,Object? reminderRules = null,Object? escalationRules = null,Object? collectionRules = null,Object? fieldAgentConfig = null,Object? aiConfig = null,Object? isActive = null,Object? createdAt = null,Object? updatedAt = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,companyId: null == companyId ? _self.companyId : companyId // ignore: cast_nullable_to_non_nullable
as String,companyName: null == companyName ? _self.companyName : companyName // ignore: cast_nullable_to_non_nullable
as String,whatsappConfig: null == whatsappConfig ? _self.whatsappConfig : whatsappConfig // ignore: cast_nullable_to_non_nullable
as WhatsAppConfig,voiceCallConfig: null == voiceCallConfig ? _self.voiceCallConfig : voiceCallConfig // ignore: cast_nullable_to_non_nullable
as VoiceCallConfig,smsConfig: null == smsConfig ? _self.smsConfig : smsConfig // ignore: cast_nullable_to_non_nullable
as SMSConfig,emailConfig: null == emailConfig ? _self.emailConfig : emailConfig // ignore: cast_nullable_to_non_nullable
as EmailConfig,reminderRules: null == reminderRules ? _self.reminderRules : reminderRules // ignore: cast_nullable_to_non_nullable
as List<AutomationRule>,escalationRules: null == escalationRules ? _self.escalationRules : escalationRules // ignore: cast_nullable_to_non_nullable
as List<AutomationRule>,collectionRules: null == collectionRules ? _self.collectionRules : collectionRules // ignore: cast_nullable_to_non_nullable
as List<AutomationRule>,fieldAgentConfig: null == fieldAgentConfig ? _self.fieldAgentConfig : fieldAgentConfig // ignore: cast_nullable_to_non_nullable
as FieldAgentConfig,aiConfig: null == aiConfig ? _self.aiConfig : aiConfig // ignore: cast_nullable_to_non_nullable
as AIConfig,isActive: null == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,updatedAt: null == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}
/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$WhatsAppConfigCopyWith<$Res> get whatsappConfig {
  
  return $WhatsAppConfigCopyWith<$Res>(_self.whatsappConfig, (value) {
    return _then(_self.copyWith(whatsappConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$VoiceCallConfigCopyWith<$Res> get voiceCallConfig {
  
  return $VoiceCallConfigCopyWith<$Res>(_self.voiceCallConfig, (value) {
    return _then(_self.copyWith(voiceCallConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$SMSConfigCopyWith<$Res> get smsConfig {
  
  return $SMSConfigCopyWith<$Res>(_self.smsConfig, (value) {
    return _then(_self.copyWith(smsConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$EmailConfigCopyWith<$Res> get emailConfig {
  
  return $EmailConfigCopyWith<$Res>(_self.emailConfig, (value) {
    return _then(_self.copyWith(emailConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$FieldAgentConfigCopyWith<$Res> get fieldAgentConfig {
  
  return $FieldAgentConfigCopyWith<$Res>(_self.fieldAgentConfig, (value) {
    return _then(_self.copyWith(fieldAgentConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AIConfigCopyWith<$Res> get aiConfig {
  
  return $AIConfigCopyWith<$Res>(_self.aiConfig, (value) {
    return _then(_self.copyWith(aiConfig: value));
  });
}
}


/// Adds pattern-matching-related methods to [EMIAutomationConfig].
extension EMIAutomationConfigPatterns on EMIAutomationConfig {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _EMIAutomationConfig value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _EMIAutomationConfig() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _EMIAutomationConfig value)  $default,){
final _that = this;
switch (_that) {
case _EMIAutomationConfig():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _EMIAutomationConfig value)?  $default,){
final _that = this;
switch (_that) {
case _EMIAutomationConfig() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String companyId,  String companyName,  WhatsAppConfig whatsappConfig,  VoiceCallConfig voiceCallConfig,  SMSConfig smsConfig,  EmailConfig emailConfig,  List<AutomationRule> reminderRules,  List<AutomationRule> escalationRules,  List<AutomationRule> collectionRules,  FieldAgentConfig fieldAgentConfig,  AIConfig aiConfig,  bool isActive,  DateTime createdAt,  DateTime updatedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _EMIAutomationConfig() when $default != null:
return $default(_that.id,_that.companyId,_that.companyName,_that.whatsappConfig,_that.voiceCallConfig,_that.smsConfig,_that.emailConfig,_that.reminderRules,_that.escalationRules,_that.collectionRules,_that.fieldAgentConfig,_that.aiConfig,_that.isActive,_that.createdAt,_that.updatedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String companyId,  String companyName,  WhatsAppConfig whatsappConfig,  VoiceCallConfig voiceCallConfig,  SMSConfig smsConfig,  EmailConfig emailConfig,  List<AutomationRule> reminderRules,  List<AutomationRule> escalationRules,  List<AutomationRule> collectionRules,  FieldAgentConfig fieldAgentConfig,  AIConfig aiConfig,  bool isActive,  DateTime createdAt,  DateTime updatedAt)  $default,) {final _that = this;
switch (_that) {
case _EMIAutomationConfig():
return $default(_that.id,_that.companyId,_that.companyName,_that.whatsappConfig,_that.voiceCallConfig,_that.smsConfig,_that.emailConfig,_that.reminderRules,_that.escalationRules,_that.collectionRules,_that.fieldAgentConfig,_that.aiConfig,_that.isActive,_that.createdAt,_that.updatedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String companyId,  String companyName,  WhatsAppConfig whatsappConfig,  VoiceCallConfig voiceCallConfig,  SMSConfig smsConfig,  EmailConfig emailConfig,  List<AutomationRule> reminderRules,  List<AutomationRule> escalationRules,  List<AutomationRule> collectionRules,  FieldAgentConfig fieldAgentConfig,  AIConfig aiConfig,  bool isActive,  DateTime createdAt,  DateTime updatedAt)?  $default,) {final _that = this;
switch (_that) {
case _EMIAutomationConfig() when $default != null:
return $default(_that.id,_that.companyId,_that.companyName,_that.whatsappConfig,_that.voiceCallConfig,_that.smsConfig,_that.emailConfig,_that.reminderRules,_that.escalationRules,_that.collectionRules,_that.fieldAgentConfig,_that.aiConfig,_that.isActive,_that.createdAt,_that.updatedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _EMIAutomationConfig implements EMIAutomationConfig {
  const _EMIAutomationConfig({required this.id, required this.companyId, required this.companyName, required this.whatsappConfig, required this.voiceCallConfig, required this.smsConfig, required this.emailConfig, final  List<AutomationRule> reminderRules = const [], final  List<AutomationRule> escalationRules = const [], final  List<AutomationRule> collectionRules = const [], required this.fieldAgentConfig, required this.aiConfig, required this.isActive, required this.createdAt, required this.updatedAt}): _reminderRules = reminderRules,_escalationRules = escalationRules,_collectionRules = collectionRules;
  factory _EMIAutomationConfig.fromJson(Map<String, dynamic> json) => _$EMIAutomationConfigFromJson(json);

@override final  String id;
@override final  String companyId;
@override final  String companyName;
// WhatsApp Business Configuration
@override final  WhatsAppConfig whatsappConfig;
// Voice Call Configuration (IVR/Cloud telephony)
@override final  VoiceCallConfig voiceCallConfig;
// SMS Gateway Configuration
@override final  SMSConfig smsConfig;
// Email Configuration
@override final  EmailConfig emailConfig;
// Automation Rules
 final  List<AutomationRule> _reminderRules;
// Automation Rules
@override@JsonKey() List<AutomationRule> get reminderRules {
  if (_reminderRules is EqualUnmodifiableListView) return _reminderRules;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_reminderRules);
}

 final  List<AutomationRule> _escalationRules;
@override@JsonKey() List<AutomationRule> get escalationRules {
  if (_escalationRules is EqualUnmodifiableListView) return _escalationRules;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_escalationRules);
}

 final  List<AutomationRule> _collectionRules;
@override@JsonKey() List<AutomationRule> get collectionRules {
  if (_collectionRules is EqualUnmodifiableListView) return _collectionRules;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_collectionRules);
}

// Field Agent Settings
@override final  FieldAgentConfig fieldAgentConfig;
// AI/ML Settings
@override final  AIConfig aiConfig;
@override final  bool isActive;
@override final  DateTime createdAt;
@override final  DateTime updatedAt;

/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$EMIAutomationConfigCopyWith<_EMIAutomationConfig> get copyWith => __$EMIAutomationConfigCopyWithImpl<_EMIAutomationConfig>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$EMIAutomationConfigToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _EMIAutomationConfig&&(identical(other.id, id) || other.id == id)&&(identical(other.companyId, companyId) || other.companyId == companyId)&&(identical(other.companyName, companyName) || other.companyName == companyName)&&(identical(other.whatsappConfig, whatsappConfig) || other.whatsappConfig == whatsappConfig)&&(identical(other.voiceCallConfig, voiceCallConfig) || other.voiceCallConfig == voiceCallConfig)&&(identical(other.smsConfig, smsConfig) || other.smsConfig == smsConfig)&&(identical(other.emailConfig, emailConfig) || other.emailConfig == emailConfig)&&const DeepCollectionEquality().equals(other._reminderRules, _reminderRules)&&const DeepCollectionEquality().equals(other._escalationRules, _escalationRules)&&const DeepCollectionEquality().equals(other._collectionRules, _collectionRules)&&(identical(other.fieldAgentConfig, fieldAgentConfig) || other.fieldAgentConfig == fieldAgentConfig)&&(identical(other.aiConfig, aiConfig) || other.aiConfig == aiConfig)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.updatedAt, updatedAt) || other.updatedAt == updatedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,companyId,companyName,whatsappConfig,voiceCallConfig,smsConfig,emailConfig,const DeepCollectionEquality().hash(_reminderRules),const DeepCollectionEquality().hash(_escalationRules),const DeepCollectionEquality().hash(_collectionRules),fieldAgentConfig,aiConfig,isActive,createdAt,updatedAt);

@override
String toString() {
  return 'EMIAutomationConfig(id: $id, companyId: $companyId, companyName: $companyName, whatsappConfig: $whatsappConfig, voiceCallConfig: $voiceCallConfig, smsConfig: $smsConfig, emailConfig: $emailConfig, reminderRules: $reminderRules, escalationRules: $escalationRules, collectionRules: $collectionRules, fieldAgentConfig: $fieldAgentConfig, aiConfig: $aiConfig, isActive: $isActive, createdAt: $createdAt, updatedAt: $updatedAt)';
}


}

/// @nodoc
abstract mixin class _$EMIAutomationConfigCopyWith<$Res> implements $EMIAutomationConfigCopyWith<$Res> {
  factory _$EMIAutomationConfigCopyWith(_EMIAutomationConfig value, $Res Function(_EMIAutomationConfig) _then) = __$EMIAutomationConfigCopyWithImpl;
@override @useResult
$Res call({
 String id, String companyId, String companyName, WhatsAppConfig whatsappConfig, VoiceCallConfig voiceCallConfig, SMSConfig smsConfig, EmailConfig emailConfig, List<AutomationRule> reminderRules, List<AutomationRule> escalationRules, List<AutomationRule> collectionRules, FieldAgentConfig fieldAgentConfig, AIConfig aiConfig, bool isActive, DateTime createdAt, DateTime updatedAt
});


@override $WhatsAppConfigCopyWith<$Res> get whatsappConfig;@override $VoiceCallConfigCopyWith<$Res> get voiceCallConfig;@override $SMSConfigCopyWith<$Res> get smsConfig;@override $EmailConfigCopyWith<$Res> get emailConfig;@override $FieldAgentConfigCopyWith<$Res> get fieldAgentConfig;@override $AIConfigCopyWith<$Res> get aiConfig;

}
/// @nodoc
class __$EMIAutomationConfigCopyWithImpl<$Res>
    implements _$EMIAutomationConfigCopyWith<$Res> {
  __$EMIAutomationConfigCopyWithImpl(this._self, this._then);

  final _EMIAutomationConfig _self;
  final $Res Function(_EMIAutomationConfig) _then;

/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? companyId = null,Object? companyName = null,Object? whatsappConfig = null,Object? voiceCallConfig = null,Object? smsConfig = null,Object? emailConfig = null,Object? reminderRules = null,Object? escalationRules = null,Object? collectionRules = null,Object? fieldAgentConfig = null,Object? aiConfig = null,Object? isActive = null,Object? createdAt = null,Object? updatedAt = null,}) {
  return _then(_EMIAutomationConfig(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,companyId: null == companyId ? _self.companyId : companyId // ignore: cast_nullable_to_non_nullable
as String,companyName: null == companyName ? _self.companyName : companyName // ignore: cast_nullable_to_non_nullable
as String,whatsappConfig: null == whatsappConfig ? _self.whatsappConfig : whatsappConfig // ignore: cast_nullable_to_non_nullable
as WhatsAppConfig,voiceCallConfig: null == voiceCallConfig ? _self.voiceCallConfig : voiceCallConfig // ignore: cast_nullable_to_non_nullable
as VoiceCallConfig,smsConfig: null == smsConfig ? _self.smsConfig : smsConfig // ignore: cast_nullable_to_non_nullable
as SMSConfig,emailConfig: null == emailConfig ? _self.emailConfig : emailConfig // ignore: cast_nullable_to_non_nullable
as EmailConfig,reminderRules: null == reminderRules ? _self._reminderRules : reminderRules // ignore: cast_nullable_to_non_nullable
as List<AutomationRule>,escalationRules: null == escalationRules ? _self._escalationRules : escalationRules // ignore: cast_nullable_to_non_nullable
as List<AutomationRule>,collectionRules: null == collectionRules ? _self._collectionRules : collectionRules // ignore: cast_nullable_to_non_nullable
as List<AutomationRule>,fieldAgentConfig: null == fieldAgentConfig ? _self.fieldAgentConfig : fieldAgentConfig // ignore: cast_nullable_to_non_nullable
as FieldAgentConfig,aiConfig: null == aiConfig ? _self.aiConfig : aiConfig // ignore: cast_nullable_to_non_nullable
as AIConfig,isActive: null == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,updatedAt: null == updatedAt ? _self.updatedAt : updatedAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$WhatsAppConfigCopyWith<$Res> get whatsappConfig {
  
  return $WhatsAppConfigCopyWith<$Res>(_self.whatsappConfig, (value) {
    return _then(_self.copyWith(whatsappConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$VoiceCallConfigCopyWith<$Res> get voiceCallConfig {
  
  return $VoiceCallConfigCopyWith<$Res>(_self.voiceCallConfig, (value) {
    return _then(_self.copyWith(voiceCallConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$SMSConfigCopyWith<$Res> get smsConfig {
  
  return $SMSConfigCopyWith<$Res>(_self.smsConfig, (value) {
    return _then(_self.copyWith(smsConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$EmailConfigCopyWith<$Res> get emailConfig {
  
  return $EmailConfigCopyWith<$Res>(_self.emailConfig, (value) {
    return _then(_self.copyWith(emailConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$FieldAgentConfigCopyWith<$Res> get fieldAgentConfig {
  
  return $FieldAgentConfigCopyWith<$Res>(_self.fieldAgentConfig, (value) {
    return _then(_self.copyWith(fieldAgentConfig: value));
  });
}/// Create a copy of EMIAutomationConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AIConfigCopyWith<$Res> get aiConfig {
  
  return $AIConfigCopyWith<$Res>(_self.aiConfig, (value) {
    return _then(_self.copyWith(aiConfig: value));
  });
}
}


/// @nodoc
mixin _$WhatsAppConfig {

 bool get isEnabled; String? get businessAccountId; String? get phoneNumberId; String? get accessToken; String? get apiVersion;// Template IDs for different scenarios
 String? get welcomeTemplateId; String? get reminderTemplateId; String? get overdueTemplateId; String? get paymentConfirmationTemplateId; String? get defaulterTemplateId;// Default message settings
 bool get sendReminders; bool get sendOverdueAlerts; bool get sendVoiceNotes;// AI generated voice messages
// Business hours
 String? get businessHoursStart;// 09:00
 String? get businessHoursEnd;// 18:00
 bool get sendOutsideBusinessHours;
/// Create a copy of WhatsAppConfig
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$WhatsAppConfigCopyWith<WhatsAppConfig> get copyWith => _$WhatsAppConfigCopyWithImpl<WhatsAppConfig>(this as WhatsAppConfig, _$identity);

  /// Serializes this WhatsAppConfig to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is WhatsAppConfig&&(identical(other.isEnabled, isEnabled) || other.isEnabled == isEnabled)&&(identical(other.businessAccountId, businessAccountId) || other.businessAccountId == businessAccountId)&&(identical(other.phoneNumberId, phoneNumberId) || other.phoneNumberId == phoneNumberId)&&(identical(other.accessToken, accessToken) || other.accessToken == accessToken)&&(identical(other.apiVersion, apiVersion) || other.apiVersion == apiVersion)&&(identical(other.welcomeTemplateId, welcomeTemplateId) || other.welcomeTemplateId == welcomeTemplateId)&&(identical(other.reminderTemplateId, reminderTemplateId) || other.reminderTemplateId == reminderTemplateId)&&(identical(other.overdueTemplateId, overdueTemplateId) || other.overdueTemplateId == overdueTemplateId)&&(identical(other.paymentConfirmationTemplateId, paymentConfirmationTemplateId) || other.paymentConfirmationTemplateId == paymentConfirmationTemplateId)&&(identical(other.defaulterTemplateId, defaulterTemplateId) || other.defaulterTemplateId == defaulterTemplateId)&&(identical(other.sendReminders, sendReminders) || other.sendReminders == sendReminders)&&(identical(other.sendOverdueAlerts, sendOverdueAlerts) || other.sendOverdueAlerts == sendOverdueAlerts)&&(identical(other.sendVoiceNotes, sendVoiceNotes) || other.sendVoiceNotes == sendVoiceNotes)&&(identical(other.businessHoursStart, businessHoursStart) || other.businessHoursStart == businessHoursStart)&&(identical(other.businessHoursEnd, businessHoursEnd) || other.businessHoursEnd == businessHoursEnd)&&(identical(other.sendOutsideBusinessHours, sendOutsideBusinessHours) || other.sendOutsideBusinessHours == sendOutsideBusinessHours));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,isEnabled,businessAccountId,phoneNumberId,accessToken,apiVersion,welcomeTemplateId,reminderTemplateId,overdueTemplateId,paymentConfirmationTemplateId,defaulterTemplateId,sendReminders,sendOverdueAlerts,sendVoiceNotes,businessHoursStart,businessHoursEnd,sendOutsideBusinessHours);

@override
String toString() {
  return 'WhatsAppConfig(isEnabled: $isEnabled, businessAccountId: $businessAccountId, phoneNumberId: $phoneNumberId, accessToken: $accessToken, apiVersion: $apiVersion, welcomeTemplateId: $welcomeTemplateId, reminderTemplateId: $reminderTemplateId, overdueTemplateId: $overdueTemplateId, paymentConfirmationTemplateId: $paymentConfirmationTemplateId, defaulterTemplateId: $defaulterTemplateId, sendReminders: $sendReminders, sendOverdueAlerts: $sendOverdueAlerts, sendVoiceNotes: $sendVoiceNotes, businessHoursStart: $businessHoursStart, businessHoursEnd: $businessHoursEnd, sendOutsideBusinessHours: $sendOutsideBusinessHours)';
}


}

/// @nodoc
abstract mixin class $WhatsAppConfigCopyWith<$Res>  {
  factory $WhatsAppConfigCopyWith(WhatsAppConfig value, $Res Function(WhatsAppConfig) _then) = _$WhatsAppConfigCopyWithImpl;
@useResult
$Res call({
 bool isEnabled, String? businessAccountId, String? phoneNumberId, String? accessToken, String? apiVersion, String? welcomeTemplateId, String? reminderTemplateId, String? overdueTemplateId, String? paymentConfirmationTemplateId, String? defaulterTemplateId, bool sendReminders, bool sendOverdueAlerts, bool sendVoiceNotes, String? businessHoursStart, String? businessHoursEnd, bool sendOutsideBusinessHours
});




}
/// @nodoc
class _$WhatsAppConfigCopyWithImpl<$Res>
    implements $WhatsAppConfigCopyWith<$Res> {
  _$WhatsAppConfigCopyWithImpl(this._self, this._then);

  final WhatsAppConfig _self;
  final $Res Function(WhatsAppConfig) _then;

/// Create a copy of WhatsAppConfig
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isEnabled = null,Object? businessAccountId = freezed,Object? phoneNumberId = freezed,Object? accessToken = freezed,Object? apiVersion = freezed,Object? welcomeTemplateId = freezed,Object? reminderTemplateId = freezed,Object? overdueTemplateId = freezed,Object? paymentConfirmationTemplateId = freezed,Object? defaulterTemplateId = freezed,Object? sendReminders = null,Object? sendOverdueAlerts = null,Object? sendVoiceNotes = null,Object? businessHoursStart = freezed,Object? businessHoursEnd = freezed,Object? sendOutsideBusinessHours = null,}) {
  return _then(_self.copyWith(
isEnabled: null == isEnabled ? _self.isEnabled : isEnabled // ignore: cast_nullable_to_non_nullable
as bool,businessAccountId: freezed == businessAccountId ? _self.businessAccountId : businessAccountId // ignore: cast_nullable_to_non_nullable
as String?,phoneNumberId: freezed == phoneNumberId ? _self.phoneNumberId : phoneNumberId // ignore: cast_nullable_to_non_nullable
as String?,accessToken: freezed == accessToken ? _self.accessToken : accessToken // ignore: cast_nullable_to_non_nullable
as String?,apiVersion: freezed == apiVersion ? _self.apiVersion : apiVersion // ignore: cast_nullable_to_non_nullable
as String?,welcomeTemplateId: freezed == welcomeTemplateId ? _self.welcomeTemplateId : welcomeTemplateId // ignore: cast_nullable_to_non_nullable
as String?,reminderTemplateId: freezed == reminderTemplateId ? _self.reminderTemplateId : reminderTemplateId // ignore: cast_nullable_to_non_nullable
as String?,overdueTemplateId: freezed == overdueTemplateId ? _self.overdueTemplateId : overdueTemplateId // ignore: cast_nullable_to_non_nullable
as String?,paymentConfirmationTemplateId: freezed == paymentConfirmationTemplateId ? _self.paymentConfirmationTemplateId : paymentConfirmationTemplateId // ignore: cast_nullable_to_non_nullable
as String?,defaulterTemplateId: freezed == defaulterTemplateId ? _self.defaulterTemplateId : defaulterTemplateId // ignore: cast_nullable_to_non_nullable
as String?,sendReminders: null == sendReminders ? _self.sendReminders : sendReminders // ignore: cast_nullable_to_non_nullable
as bool,sendOverdueAlerts: null == sendOverdueAlerts ? _self.sendOverdueAlerts : sendOverdueAlerts // ignore: cast_nullable_to_non_nullable
as bool,sendVoiceNotes: null == sendVoiceNotes ? _self.sendVoiceNotes : sendVoiceNotes // ignore: cast_nullable_to_non_nullable
as bool,businessHoursStart: freezed == businessHoursStart ? _self.businessHoursStart : businessHoursStart // ignore: cast_nullable_to_non_nullable
as String?,businessHoursEnd: freezed == businessHoursEnd ? _self.businessHoursEnd : businessHoursEnd // ignore: cast_nullable_to_non_nullable
as String?,sendOutsideBusinessHours: null == sendOutsideBusinessHours ? _self.sendOutsideBusinessHours : sendOutsideBusinessHours // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [WhatsAppConfig].
extension WhatsAppConfigPatterns on WhatsAppConfig {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _WhatsAppConfig value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _WhatsAppConfig() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _WhatsAppConfig value)  $default,){
final _that = this;
switch (_that) {
case _WhatsAppConfig():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _WhatsAppConfig value)?  $default,){
final _that = this;
switch (_that) {
case _WhatsAppConfig() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isEnabled,  String? businessAccountId,  String? phoneNumberId,  String? accessToken,  String? apiVersion,  String? welcomeTemplateId,  String? reminderTemplateId,  String? overdueTemplateId,  String? paymentConfirmationTemplateId,  String? defaulterTemplateId,  bool sendReminders,  bool sendOverdueAlerts,  bool sendVoiceNotes,  String? businessHoursStart,  String? businessHoursEnd,  bool sendOutsideBusinessHours)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _WhatsAppConfig() when $default != null:
return $default(_that.isEnabled,_that.businessAccountId,_that.phoneNumberId,_that.accessToken,_that.apiVersion,_that.welcomeTemplateId,_that.reminderTemplateId,_that.overdueTemplateId,_that.paymentConfirmationTemplateId,_that.defaulterTemplateId,_that.sendReminders,_that.sendOverdueAlerts,_that.sendVoiceNotes,_that.businessHoursStart,_that.businessHoursEnd,_that.sendOutsideBusinessHours);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isEnabled,  String? businessAccountId,  String? phoneNumberId,  String? accessToken,  String? apiVersion,  String? welcomeTemplateId,  String? reminderTemplateId,  String? overdueTemplateId,  String? paymentConfirmationTemplateId,  String? defaulterTemplateId,  bool sendReminders,  bool sendOverdueAlerts,  bool sendVoiceNotes,  String? businessHoursStart,  String? businessHoursEnd,  bool sendOutsideBusinessHours)  $default,) {final _that = this;
switch (_that) {
case _WhatsAppConfig():
return $default(_that.isEnabled,_that.businessAccountId,_that.phoneNumberId,_that.accessToken,_that.apiVersion,_that.welcomeTemplateId,_that.reminderTemplateId,_that.overdueTemplateId,_that.paymentConfirmationTemplateId,_that.defaulterTemplateId,_that.sendReminders,_that.sendOverdueAlerts,_that.sendVoiceNotes,_that.businessHoursStart,_that.businessHoursEnd,_that.sendOutsideBusinessHours);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isEnabled,  String? businessAccountId,  String? phoneNumberId,  String? accessToken,  String? apiVersion,  String? welcomeTemplateId,  String? reminderTemplateId,  String? overdueTemplateId,  String? paymentConfirmationTemplateId,  String? defaulterTemplateId,  bool sendReminders,  bool sendOverdueAlerts,  bool sendVoiceNotes,  String? businessHoursStart,  String? businessHoursEnd,  bool sendOutsideBusinessHours)?  $default,) {final _that = this;
switch (_that) {
case _WhatsAppConfig() when $default != null:
return $default(_that.isEnabled,_that.businessAccountId,_that.phoneNumberId,_that.accessToken,_that.apiVersion,_that.welcomeTemplateId,_that.reminderTemplateId,_that.overdueTemplateId,_that.paymentConfirmationTemplateId,_that.defaulterTemplateId,_that.sendReminders,_that.sendOverdueAlerts,_that.sendVoiceNotes,_that.businessHoursStart,_that.businessHoursEnd,_that.sendOutsideBusinessHours);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _WhatsAppConfig implements WhatsAppConfig {
  const _WhatsAppConfig({this.isEnabled = false, this.businessAccountId, this.phoneNumberId, this.accessToken, this.apiVersion, this.welcomeTemplateId, this.reminderTemplateId, this.overdueTemplateId, this.paymentConfirmationTemplateId, this.defaulterTemplateId, this.sendReminders = true, this.sendOverdueAlerts = true, this.sendVoiceNotes = false, this.businessHoursStart, this.businessHoursEnd, this.sendOutsideBusinessHours = false});
  factory _WhatsAppConfig.fromJson(Map<String, dynamic> json) => _$WhatsAppConfigFromJson(json);

@override@JsonKey() final  bool isEnabled;
@override final  String? businessAccountId;
@override final  String? phoneNumberId;
@override final  String? accessToken;
@override final  String? apiVersion;
// Template IDs for different scenarios
@override final  String? welcomeTemplateId;
@override final  String? reminderTemplateId;
@override final  String? overdueTemplateId;
@override final  String? paymentConfirmationTemplateId;
@override final  String? defaulterTemplateId;
// Default message settings
@override@JsonKey() final  bool sendReminders;
@override@JsonKey() final  bool sendOverdueAlerts;
@override@JsonKey() final  bool sendVoiceNotes;
// AI generated voice messages
// Business hours
@override final  String? businessHoursStart;
// 09:00
@override final  String? businessHoursEnd;
// 18:00
@override@JsonKey() final  bool sendOutsideBusinessHours;

/// Create a copy of WhatsAppConfig
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$WhatsAppConfigCopyWith<_WhatsAppConfig> get copyWith => __$WhatsAppConfigCopyWithImpl<_WhatsAppConfig>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$WhatsAppConfigToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _WhatsAppConfig&&(identical(other.isEnabled, isEnabled) || other.isEnabled == isEnabled)&&(identical(other.businessAccountId, businessAccountId) || other.businessAccountId == businessAccountId)&&(identical(other.phoneNumberId, phoneNumberId) || other.phoneNumberId == phoneNumberId)&&(identical(other.accessToken, accessToken) || other.accessToken == accessToken)&&(identical(other.apiVersion, apiVersion) || other.apiVersion == apiVersion)&&(identical(other.welcomeTemplateId, welcomeTemplateId) || other.welcomeTemplateId == welcomeTemplateId)&&(identical(other.reminderTemplateId, reminderTemplateId) || other.reminderTemplateId == reminderTemplateId)&&(identical(other.overdueTemplateId, overdueTemplateId) || other.overdueTemplateId == overdueTemplateId)&&(identical(other.paymentConfirmationTemplateId, paymentConfirmationTemplateId) || other.paymentConfirmationTemplateId == paymentConfirmationTemplateId)&&(identical(other.defaulterTemplateId, defaulterTemplateId) || other.defaulterTemplateId == defaulterTemplateId)&&(identical(other.sendReminders, sendReminders) || other.sendReminders == sendReminders)&&(identical(other.sendOverdueAlerts, sendOverdueAlerts) || other.sendOverdueAlerts == sendOverdueAlerts)&&(identical(other.sendVoiceNotes, sendVoiceNotes) || other.sendVoiceNotes == sendVoiceNotes)&&(identical(other.businessHoursStart, businessHoursStart) || other.businessHoursStart == businessHoursStart)&&(identical(other.businessHoursEnd, businessHoursEnd) || other.businessHoursEnd == businessHoursEnd)&&(identical(other.sendOutsideBusinessHours, sendOutsideBusinessHours) || other.sendOutsideBusinessHours == sendOutsideBusinessHours));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,isEnabled,businessAccountId,phoneNumberId,accessToken,apiVersion,welcomeTemplateId,reminderTemplateId,overdueTemplateId,paymentConfirmationTemplateId,defaulterTemplateId,sendReminders,sendOverdueAlerts,sendVoiceNotes,businessHoursStart,businessHoursEnd,sendOutsideBusinessHours);

@override
String toString() {
  return 'WhatsAppConfig(isEnabled: $isEnabled, businessAccountId: $businessAccountId, phoneNumberId: $phoneNumberId, accessToken: $accessToken, apiVersion: $apiVersion, welcomeTemplateId: $welcomeTemplateId, reminderTemplateId: $reminderTemplateId, overdueTemplateId: $overdueTemplateId, paymentConfirmationTemplateId: $paymentConfirmationTemplateId, defaulterTemplateId: $defaulterTemplateId, sendReminders: $sendReminders, sendOverdueAlerts: $sendOverdueAlerts, sendVoiceNotes: $sendVoiceNotes, businessHoursStart: $businessHoursStart, businessHoursEnd: $businessHoursEnd, sendOutsideBusinessHours: $sendOutsideBusinessHours)';
}


}

/// @nodoc
abstract mixin class _$WhatsAppConfigCopyWith<$Res> implements $WhatsAppConfigCopyWith<$Res> {
  factory _$WhatsAppConfigCopyWith(_WhatsAppConfig value, $Res Function(_WhatsAppConfig) _then) = __$WhatsAppConfigCopyWithImpl;
@override @useResult
$Res call({
 bool isEnabled, String? businessAccountId, String? phoneNumberId, String? accessToken, String? apiVersion, String? welcomeTemplateId, String? reminderTemplateId, String? overdueTemplateId, String? paymentConfirmationTemplateId, String? defaulterTemplateId, bool sendReminders, bool sendOverdueAlerts, bool sendVoiceNotes, String? businessHoursStart, String? businessHoursEnd, bool sendOutsideBusinessHours
});




}
/// @nodoc
class __$WhatsAppConfigCopyWithImpl<$Res>
    implements _$WhatsAppConfigCopyWith<$Res> {
  __$WhatsAppConfigCopyWithImpl(this._self, this._then);

  final _WhatsAppConfig _self;
  final $Res Function(_WhatsAppConfig) _then;

/// Create a copy of WhatsAppConfig
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isEnabled = null,Object? businessAccountId = freezed,Object? phoneNumberId = freezed,Object? accessToken = freezed,Object? apiVersion = freezed,Object? welcomeTemplateId = freezed,Object? reminderTemplateId = freezed,Object? overdueTemplateId = freezed,Object? paymentConfirmationTemplateId = freezed,Object? defaulterTemplateId = freezed,Object? sendReminders = null,Object? sendOverdueAlerts = null,Object? sendVoiceNotes = null,Object? businessHoursStart = freezed,Object? businessHoursEnd = freezed,Object? sendOutsideBusinessHours = null,}) {
  return _then(_WhatsAppConfig(
isEnabled: null == isEnabled ? _self.isEnabled : isEnabled // ignore: cast_nullable_to_non_nullable
as bool,businessAccountId: freezed == businessAccountId ? _self.businessAccountId : businessAccountId // ignore: cast_nullable_to_non_nullable
as String?,phoneNumberId: freezed == phoneNumberId ? _self.phoneNumberId : phoneNumberId // ignore: cast_nullable_to_non_nullable
as String?,accessToken: freezed == accessToken ? _self.accessToken : accessToken // ignore: cast_nullable_to_non_nullable
as String?,apiVersion: freezed == apiVersion ? _self.apiVersion : apiVersion // ignore: cast_nullable_to_non_nullable
as String?,welcomeTemplateId: freezed == welcomeTemplateId ? _self.welcomeTemplateId : welcomeTemplateId // ignore: cast_nullable_to_non_nullable
as String?,reminderTemplateId: freezed == reminderTemplateId ? _self.reminderTemplateId : reminderTemplateId // ignore: cast_nullable_to_non_nullable
as String?,overdueTemplateId: freezed == overdueTemplateId ? _self.overdueTemplateId : overdueTemplateId // ignore: cast_nullable_to_non_nullable
as String?,paymentConfirmationTemplateId: freezed == paymentConfirmationTemplateId ? _self.paymentConfirmationTemplateId : paymentConfirmationTemplateId // ignore: cast_nullable_to_non_nullable
as String?,defaulterTemplateId: freezed == defaulterTemplateId ? _self.defaulterTemplateId : defaulterTemplateId // ignore: cast_nullable_to_non_nullable
as String?,sendReminders: null == sendReminders ? _self.sendReminders : sendReminders // ignore: cast_nullable_to_non_nullable
as bool,sendOverdueAlerts: null == sendOverdueAlerts ? _self.sendOverdueAlerts : sendOverdueAlerts // ignore: cast_nullable_to_non_nullable
as bool,sendVoiceNotes: null == sendVoiceNotes ? _self.sendVoiceNotes : sendVoiceNotes // ignore: cast_nullable_to_non_nullable
as bool,businessHoursStart: freezed == businessHoursStart ? _self.businessHoursStart : businessHoursStart // ignore: cast_nullable_to_non_nullable
as String?,businessHoursEnd: freezed == businessHoursEnd ? _self.businessHoursEnd : businessHoursEnd // ignore: cast_nullable_to_non_nullable
as String?,sendOutsideBusinessHours: null == sendOutsideBusinessHours ? _self.sendOutsideBusinessHours : sendOutsideBusinessHours // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}


/// @nodoc
mixin _$VoiceCallConfig {

 bool get isEnabled; String? get provider;// Exotel, Knowlarity, Twilio, Ozonetel
 String? get apiKey; String? get apiSecret; String? get fromNumber;// IVR Settings
 bool get useIVR; String? get ivrGreetingMessage; String? get ivrMenuOptions;// "Press 1 for EMI status, 2 for payment link..."
// AI Voice Bot
 bool get useAIVoiceBot; String? get aiVoiceLanguage;// hi-IN, en-IN
 String? get aiVoiceGender;// male, female
// Call scheduling
 int get maxRetryAttempts; int get retryIntervalMinutes; List<int> get preferredCallHours;// 10 AM, 2 PM, 4 PM
// Recording
 bool get recordCalls; bool get transcribeCalls;
/// Create a copy of VoiceCallConfig
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$VoiceCallConfigCopyWith<VoiceCallConfig> get copyWith => _$VoiceCallConfigCopyWithImpl<VoiceCallConfig>(this as VoiceCallConfig, _$identity);

  /// Serializes this VoiceCallConfig to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is VoiceCallConfig&&(identical(other.isEnabled, isEnabled) || other.isEnabled == isEnabled)&&(identical(other.provider, provider) || other.provider == provider)&&(identical(other.apiKey, apiKey) || other.apiKey == apiKey)&&(identical(other.apiSecret, apiSecret) || other.apiSecret == apiSecret)&&(identical(other.fromNumber, fromNumber) || other.fromNumber == fromNumber)&&(identical(other.useIVR, useIVR) || other.useIVR == useIVR)&&(identical(other.ivrGreetingMessage, ivrGreetingMessage) || other.ivrGreetingMessage == ivrGreetingMessage)&&(identical(other.ivrMenuOptions, ivrMenuOptions) || other.ivrMenuOptions == ivrMenuOptions)&&(identical(other.useAIVoiceBot, useAIVoiceBot) || other.useAIVoiceBot == useAIVoiceBot)&&(identical(other.aiVoiceLanguage, aiVoiceLanguage) || other.aiVoiceLanguage == aiVoiceLanguage)&&(identical(other.aiVoiceGender, aiVoiceGender) || other.aiVoiceGender == aiVoiceGender)&&(identical(other.maxRetryAttempts, maxRetryAttempts) || other.maxRetryAttempts == maxRetryAttempts)&&(identical(other.retryIntervalMinutes, retryIntervalMinutes) || other.retryIntervalMinutes == retryIntervalMinutes)&&const DeepCollectionEquality().equals(other.preferredCallHours, preferredCallHours)&&(identical(other.recordCalls, recordCalls) || other.recordCalls == recordCalls)&&(identical(other.transcribeCalls, transcribeCalls) || other.transcribeCalls == transcribeCalls));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,isEnabled,provider,apiKey,apiSecret,fromNumber,useIVR,ivrGreetingMessage,ivrMenuOptions,useAIVoiceBot,aiVoiceLanguage,aiVoiceGender,maxRetryAttempts,retryIntervalMinutes,const DeepCollectionEquality().hash(preferredCallHours),recordCalls,transcribeCalls);

@override
String toString() {
  return 'VoiceCallConfig(isEnabled: $isEnabled, provider: $provider, apiKey: $apiKey, apiSecret: $apiSecret, fromNumber: $fromNumber, useIVR: $useIVR, ivrGreetingMessage: $ivrGreetingMessage, ivrMenuOptions: $ivrMenuOptions, useAIVoiceBot: $useAIVoiceBot, aiVoiceLanguage: $aiVoiceLanguage, aiVoiceGender: $aiVoiceGender, maxRetryAttempts: $maxRetryAttempts, retryIntervalMinutes: $retryIntervalMinutes, preferredCallHours: $preferredCallHours, recordCalls: $recordCalls, transcribeCalls: $transcribeCalls)';
}


}

/// @nodoc
abstract mixin class $VoiceCallConfigCopyWith<$Res>  {
  factory $VoiceCallConfigCopyWith(VoiceCallConfig value, $Res Function(VoiceCallConfig) _then) = _$VoiceCallConfigCopyWithImpl;
@useResult
$Res call({
 bool isEnabled, String? provider, String? apiKey, String? apiSecret, String? fromNumber, bool useIVR, String? ivrGreetingMessage, String? ivrMenuOptions, bool useAIVoiceBot, String? aiVoiceLanguage, String? aiVoiceGender, int maxRetryAttempts, int retryIntervalMinutes, List<int> preferredCallHours, bool recordCalls, bool transcribeCalls
});




}
/// @nodoc
class _$VoiceCallConfigCopyWithImpl<$Res>
    implements $VoiceCallConfigCopyWith<$Res> {
  _$VoiceCallConfigCopyWithImpl(this._self, this._then);

  final VoiceCallConfig _self;
  final $Res Function(VoiceCallConfig) _then;

/// Create a copy of VoiceCallConfig
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isEnabled = null,Object? provider = freezed,Object? apiKey = freezed,Object? apiSecret = freezed,Object? fromNumber = freezed,Object? useIVR = null,Object? ivrGreetingMessage = freezed,Object? ivrMenuOptions = freezed,Object? useAIVoiceBot = null,Object? aiVoiceLanguage = freezed,Object? aiVoiceGender = freezed,Object? maxRetryAttempts = null,Object? retryIntervalMinutes = null,Object? preferredCallHours = null,Object? recordCalls = null,Object? transcribeCalls = null,}) {
  return _then(_self.copyWith(
isEnabled: null == isEnabled ? _self.isEnabled : isEnabled // ignore: cast_nullable_to_non_nullable
as bool,provider: freezed == provider ? _self.provider : provider // ignore: cast_nullable_to_non_nullable
as String?,apiKey: freezed == apiKey ? _self.apiKey : apiKey // ignore: cast_nullable_to_non_nullable
as String?,apiSecret: freezed == apiSecret ? _self.apiSecret : apiSecret // ignore: cast_nullable_to_non_nullable
as String?,fromNumber: freezed == fromNumber ? _self.fromNumber : fromNumber // ignore: cast_nullable_to_non_nullable
as String?,useIVR: null == useIVR ? _self.useIVR : useIVR // ignore: cast_nullable_to_non_nullable
as bool,ivrGreetingMessage: freezed == ivrGreetingMessage ? _self.ivrGreetingMessage : ivrGreetingMessage // ignore: cast_nullable_to_non_nullable
as String?,ivrMenuOptions: freezed == ivrMenuOptions ? _self.ivrMenuOptions : ivrMenuOptions // ignore: cast_nullable_to_non_nullable
as String?,useAIVoiceBot: null == useAIVoiceBot ? _self.useAIVoiceBot : useAIVoiceBot // ignore: cast_nullable_to_non_nullable
as bool,aiVoiceLanguage: freezed == aiVoiceLanguage ? _self.aiVoiceLanguage : aiVoiceLanguage // ignore: cast_nullable_to_non_nullable
as String?,aiVoiceGender: freezed == aiVoiceGender ? _self.aiVoiceGender : aiVoiceGender // ignore: cast_nullable_to_non_nullable
as String?,maxRetryAttempts: null == maxRetryAttempts ? _self.maxRetryAttempts : maxRetryAttempts // ignore: cast_nullable_to_non_nullable
as int,retryIntervalMinutes: null == retryIntervalMinutes ? _self.retryIntervalMinutes : retryIntervalMinutes // ignore: cast_nullable_to_non_nullable
as int,preferredCallHours: null == preferredCallHours ? _self.preferredCallHours : preferredCallHours // ignore: cast_nullable_to_non_nullable
as List<int>,recordCalls: null == recordCalls ? _self.recordCalls : recordCalls // ignore: cast_nullable_to_non_nullable
as bool,transcribeCalls: null == transcribeCalls ? _self.transcribeCalls : transcribeCalls // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [VoiceCallConfig].
extension VoiceCallConfigPatterns on VoiceCallConfig {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _VoiceCallConfig value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _VoiceCallConfig() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _VoiceCallConfig value)  $default,){
final _that = this;
switch (_that) {
case _VoiceCallConfig():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _VoiceCallConfig value)?  $default,){
final _that = this;
switch (_that) {
case _VoiceCallConfig() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isEnabled,  String? provider,  String? apiKey,  String? apiSecret,  String? fromNumber,  bool useIVR,  String? ivrGreetingMessage,  String? ivrMenuOptions,  bool useAIVoiceBot,  String? aiVoiceLanguage,  String? aiVoiceGender,  int maxRetryAttempts,  int retryIntervalMinutes,  List<int> preferredCallHours,  bool recordCalls,  bool transcribeCalls)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _VoiceCallConfig() when $default != null:
return $default(_that.isEnabled,_that.provider,_that.apiKey,_that.apiSecret,_that.fromNumber,_that.useIVR,_that.ivrGreetingMessage,_that.ivrMenuOptions,_that.useAIVoiceBot,_that.aiVoiceLanguage,_that.aiVoiceGender,_that.maxRetryAttempts,_that.retryIntervalMinutes,_that.preferredCallHours,_that.recordCalls,_that.transcribeCalls);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isEnabled,  String? provider,  String? apiKey,  String? apiSecret,  String? fromNumber,  bool useIVR,  String? ivrGreetingMessage,  String? ivrMenuOptions,  bool useAIVoiceBot,  String? aiVoiceLanguage,  String? aiVoiceGender,  int maxRetryAttempts,  int retryIntervalMinutes,  List<int> preferredCallHours,  bool recordCalls,  bool transcribeCalls)  $default,) {final _that = this;
switch (_that) {
case _VoiceCallConfig():
return $default(_that.isEnabled,_that.provider,_that.apiKey,_that.apiSecret,_that.fromNumber,_that.useIVR,_that.ivrGreetingMessage,_that.ivrMenuOptions,_that.useAIVoiceBot,_that.aiVoiceLanguage,_that.aiVoiceGender,_that.maxRetryAttempts,_that.retryIntervalMinutes,_that.preferredCallHours,_that.recordCalls,_that.transcribeCalls);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isEnabled,  String? provider,  String? apiKey,  String? apiSecret,  String? fromNumber,  bool useIVR,  String? ivrGreetingMessage,  String? ivrMenuOptions,  bool useAIVoiceBot,  String? aiVoiceLanguage,  String? aiVoiceGender,  int maxRetryAttempts,  int retryIntervalMinutes,  List<int> preferredCallHours,  bool recordCalls,  bool transcribeCalls)?  $default,) {final _that = this;
switch (_that) {
case _VoiceCallConfig() when $default != null:
return $default(_that.isEnabled,_that.provider,_that.apiKey,_that.apiSecret,_that.fromNumber,_that.useIVR,_that.ivrGreetingMessage,_that.ivrMenuOptions,_that.useAIVoiceBot,_that.aiVoiceLanguage,_that.aiVoiceGender,_that.maxRetryAttempts,_that.retryIntervalMinutes,_that.preferredCallHours,_that.recordCalls,_that.transcribeCalls);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _VoiceCallConfig implements VoiceCallConfig {
  const _VoiceCallConfig({this.isEnabled = false, this.provider, this.apiKey, this.apiSecret, this.fromNumber, this.useIVR = false, this.ivrGreetingMessage, this.ivrMenuOptions, this.useAIVoiceBot = false, this.aiVoiceLanguage, this.aiVoiceGender, this.maxRetryAttempts = 3, this.retryIntervalMinutes = 30, final  List<int> preferredCallHours = const [10, 14, 16], this.recordCalls = true, this.transcribeCalls = true}): _preferredCallHours = preferredCallHours;
  factory _VoiceCallConfig.fromJson(Map<String, dynamic> json) => _$VoiceCallConfigFromJson(json);

@override@JsonKey() final  bool isEnabled;
@override final  String? provider;
// Exotel, Knowlarity, Twilio, Ozonetel
@override final  String? apiKey;
@override final  String? apiSecret;
@override final  String? fromNumber;
// IVR Settings
@override@JsonKey() final  bool useIVR;
@override final  String? ivrGreetingMessage;
@override final  String? ivrMenuOptions;
// "Press 1 for EMI status, 2 for payment link..."
// AI Voice Bot
@override@JsonKey() final  bool useAIVoiceBot;
@override final  String? aiVoiceLanguage;
// hi-IN, en-IN
@override final  String? aiVoiceGender;
// male, female
// Call scheduling
@override@JsonKey() final  int maxRetryAttempts;
@override@JsonKey() final  int retryIntervalMinutes;
 final  List<int> _preferredCallHours;
@override@JsonKey() List<int> get preferredCallHours {
  if (_preferredCallHours is EqualUnmodifiableListView) return _preferredCallHours;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_preferredCallHours);
}

// 10 AM, 2 PM, 4 PM
// Recording
@override@JsonKey() final  bool recordCalls;
@override@JsonKey() final  bool transcribeCalls;

/// Create a copy of VoiceCallConfig
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$VoiceCallConfigCopyWith<_VoiceCallConfig> get copyWith => __$VoiceCallConfigCopyWithImpl<_VoiceCallConfig>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$VoiceCallConfigToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _VoiceCallConfig&&(identical(other.isEnabled, isEnabled) || other.isEnabled == isEnabled)&&(identical(other.provider, provider) || other.provider == provider)&&(identical(other.apiKey, apiKey) || other.apiKey == apiKey)&&(identical(other.apiSecret, apiSecret) || other.apiSecret == apiSecret)&&(identical(other.fromNumber, fromNumber) || other.fromNumber == fromNumber)&&(identical(other.useIVR, useIVR) || other.useIVR == useIVR)&&(identical(other.ivrGreetingMessage, ivrGreetingMessage) || other.ivrGreetingMessage == ivrGreetingMessage)&&(identical(other.ivrMenuOptions, ivrMenuOptions) || other.ivrMenuOptions == ivrMenuOptions)&&(identical(other.useAIVoiceBot, useAIVoiceBot) || other.useAIVoiceBot == useAIVoiceBot)&&(identical(other.aiVoiceLanguage, aiVoiceLanguage) || other.aiVoiceLanguage == aiVoiceLanguage)&&(identical(other.aiVoiceGender, aiVoiceGender) || other.aiVoiceGender == aiVoiceGender)&&(identical(other.maxRetryAttempts, maxRetryAttempts) || other.maxRetryAttempts == maxRetryAttempts)&&(identical(other.retryIntervalMinutes, retryIntervalMinutes) || other.retryIntervalMinutes == retryIntervalMinutes)&&const DeepCollectionEquality().equals(other._preferredCallHours, _preferredCallHours)&&(identical(other.recordCalls, recordCalls) || other.recordCalls == recordCalls)&&(identical(other.transcribeCalls, transcribeCalls) || other.transcribeCalls == transcribeCalls));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,isEnabled,provider,apiKey,apiSecret,fromNumber,useIVR,ivrGreetingMessage,ivrMenuOptions,useAIVoiceBot,aiVoiceLanguage,aiVoiceGender,maxRetryAttempts,retryIntervalMinutes,const DeepCollectionEquality().hash(_preferredCallHours),recordCalls,transcribeCalls);

@override
String toString() {
  return 'VoiceCallConfig(isEnabled: $isEnabled, provider: $provider, apiKey: $apiKey, apiSecret: $apiSecret, fromNumber: $fromNumber, useIVR: $useIVR, ivrGreetingMessage: $ivrGreetingMessage, ivrMenuOptions: $ivrMenuOptions, useAIVoiceBot: $useAIVoiceBot, aiVoiceLanguage: $aiVoiceLanguage, aiVoiceGender: $aiVoiceGender, maxRetryAttempts: $maxRetryAttempts, retryIntervalMinutes: $retryIntervalMinutes, preferredCallHours: $preferredCallHours, recordCalls: $recordCalls, transcribeCalls: $transcribeCalls)';
}


}

/// @nodoc
abstract mixin class _$VoiceCallConfigCopyWith<$Res> implements $VoiceCallConfigCopyWith<$Res> {
  factory _$VoiceCallConfigCopyWith(_VoiceCallConfig value, $Res Function(_VoiceCallConfig) _then) = __$VoiceCallConfigCopyWithImpl;
@override @useResult
$Res call({
 bool isEnabled, String? provider, String? apiKey, String? apiSecret, String? fromNumber, bool useIVR, String? ivrGreetingMessage, String? ivrMenuOptions, bool useAIVoiceBot, String? aiVoiceLanguage, String? aiVoiceGender, int maxRetryAttempts, int retryIntervalMinutes, List<int> preferredCallHours, bool recordCalls, bool transcribeCalls
});




}
/// @nodoc
class __$VoiceCallConfigCopyWithImpl<$Res>
    implements _$VoiceCallConfigCopyWith<$Res> {
  __$VoiceCallConfigCopyWithImpl(this._self, this._then);

  final _VoiceCallConfig _self;
  final $Res Function(_VoiceCallConfig) _then;

/// Create a copy of VoiceCallConfig
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isEnabled = null,Object? provider = freezed,Object? apiKey = freezed,Object? apiSecret = freezed,Object? fromNumber = freezed,Object? useIVR = null,Object? ivrGreetingMessage = freezed,Object? ivrMenuOptions = freezed,Object? useAIVoiceBot = null,Object? aiVoiceLanguage = freezed,Object? aiVoiceGender = freezed,Object? maxRetryAttempts = null,Object? retryIntervalMinutes = null,Object? preferredCallHours = null,Object? recordCalls = null,Object? transcribeCalls = null,}) {
  return _then(_VoiceCallConfig(
isEnabled: null == isEnabled ? _self.isEnabled : isEnabled // ignore: cast_nullable_to_non_nullable
as bool,provider: freezed == provider ? _self.provider : provider // ignore: cast_nullable_to_non_nullable
as String?,apiKey: freezed == apiKey ? _self.apiKey : apiKey // ignore: cast_nullable_to_non_nullable
as String?,apiSecret: freezed == apiSecret ? _self.apiSecret : apiSecret // ignore: cast_nullable_to_non_nullable
as String?,fromNumber: freezed == fromNumber ? _self.fromNumber : fromNumber // ignore: cast_nullable_to_non_nullable
as String?,useIVR: null == useIVR ? _self.useIVR : useIVR // ignore: cast_nullable_to_non_nullable
as bool,ivrGreetingMessage: freezed == ivrGreetingMessage ? _self.ivrGreetingMessage : ivrGreetingMessage // ignore: cast_nullable_to_non_nullable
as String?,ivrMenuOptions: freezed == ivrMenuOptions ? _self.ivrMenuOptions : ivrMenuOptions // ignore: cast_nullable_to_non_nullable
as String?,useAIVoiceBot: null == useAIVoiceBot ? _self.useAIVoiceBot : useAIVoiceBot // ignore: cast_nullable_to_non_nullable
as bool,aiVoiceLanguage: freezed == aiVoiceLanguage ? _self.aiVoiceLanguage : aiVoiceLanguage // ignore: cast_nullable_to_non_nullable
as String?,aiVoiceGender: freezed == aiVoiceGender ? _self.aiVoiceGender : aiVoiceGender // ignore: cast_nullable_to_non_nullable
as String?,maxRetryAttempts: null == maxRetryAttempts ? _self.maxRetryAttempts : maxRetryAttempts // ignore: cast_nullable_to_non_nullable
as int,retryIntervalMinutes: null == retryIntervalMinutes ? _self.retryIntervalMinutes : retryIntervalMinutes // ignore: cast_nullable_to_non_nullable
as int,preferredCallHours: null == preferredCallHours ? _self._preferredCallHours : preferredCallHours // ignore: cast_nullable_to_non_nullable
as List<int>,recordCalls: null == recordCalls ? _self.recordCalls : recordCalls // ignore: cast_nullable_to_non_nullable
as bool,transcribeCalls: null == transcribeCalls ? _self.transcribeCalls : transcribeCalls // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}


/// @nodoc
mixin _$SMSConfig {

 bool get isEnabled; String? get provider;// Msg91, Twilio, ValueFirst
 String? get apiKey; String? get senderId;// APSDLRM
// DLT Template IDs (India TRAI compliance)
 String? get otpTemplateId; String? get reminderTemplateId; String? get overdueTemplateId; String? get paymentLinkTemplateId; String? get receiptTemplateId;// SMS settings
 bool get useShortURL; bool get trackClicks; List<String> get blockedHours;
/// Create a copy of SMSConfig
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SMSConfigCopyWith<SMSConfig> get copyWith => _$SMSConfigCopyWithImpl<SMSConfig>(this as SMSConfig, _$identity);

  /// Serializes this SMSConfig to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SMSConfig&&(identical(other.isEnabled, isEnabled) || other.isEnabled == isEnabled)&&(identical(other.provider, provider) || other.provider == provider)&&(identical(other.apiKey, apiKey) || other.apiKey == apiKey)&&(identical(other.senderId, senderId) || other.senderId == senderId)&&(identical(other.otpTemplateId, otpTemplateId) || other.otpTemplateId == otpTemplateId)&&(identical(other.reminderTemplateId, reminderTemplateId) || other.reminderTemplateId == reminderTemplateId)&&(identical(other.overdueTemplateId, overdueTemplateId) || other.overdueTemplateId == overdueTemplateId)&&(identical(other.paymentLinkTemplateId, paymentLinkTemplateId) || other.paymentLinkTemplateId == paymentLinkTemplateId)&&(identical(other.receiptTemplateId, receiptTemplateId) || other.receiptTemplateId == receiptTemplateId)&&(identical(other.useShortURL, useShortURL) || other.useShortURL == useShortURL)&&(identical(other.trackClicks, trackClicks) || other.trackClicks == trackClicks)&&const DeepCollectionEquality().equals(other.blockedHours, blockedHours));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,isEnabled,provider,apiKey,senderId,otpTemplateId,reminderTemplateId,overdueTemplateId,paymentLinkTemplateId,receiptTemplateId,useShortURL,trackClicks,const DeepCollectionEquality().hash(blockedHours));

@override
String toString() {
  return 'SMSConfig(isEnabled: $isEnabled, provider: $provider, apiKey: $apiKey, senderId: $senderId, otpTemplateId: $otpTemplateId, reminderTemplateId: $reminderTemplateId, overdueTemplateId: $overdueTemplateId, paymentLinkTemplateId: $paymentLinkTemplateId, receiptTemplateId: $receiptTemplateId, useShortURL: $useShortURL, trackClicks: $trackClicks, blockedHours: $blockedHours)';
}


}

/// @nodoc
abstract mixin class $SMSConfigCopyWith<$Res>  {
  factory $SMSConfigCopyWith(SMSConfig value, $Res Function(SMSConfig) _then) = _$SMSConfigCopyWithImpl;
@useResult
$Res call({
 bool isEnabled, String? provider, String? apiKey, String? senderId, String? otpTemplateId, String? reminderTemplateId, String? overdueTemplateId, String? paymentLinkTemplateId, String? receiptTemplateId, bool useShortURL, bool trackClicks, List<String> blockedHours
});




}
/// @nodoc
class _$SMSConfigCopyWithImpl<$Res>
    implements $SMSConfigCopyWith<$Res> {
  _$SMSConfigCopyWithImpl(this._self, this._then);

  final SMSConfig _self;
  final $Res Function(SMSConfig) _then;

/// Create a copy of SMSConfig
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isEnabled = null,Object? provider = freezed,Object? apiKey = freezed,Object? senderId = freezed,Object? otpTemplateId = freezed,Object? reminderTemplateId = freezed,Object? overdueTemplateId = freezed,Object? paymentLinkTemplateId = freezed,Object? receiptTemplateId = freezed,Object? useShortURL = null,Object? trackClicks = null,Object? blockedHours = null,}) {
  return _then(_self.copyWith(
isEnabled: null == isEnabled ? _self.isEnabled : isEnabled // ignore: cast_nullable_to_non_nullable
as bool,provider: freezed == provider ? _self.provider : provider // ignore: cast_nullable_to_non_nullable
as String?,apiKey: freezed == apiKey ? _self.apiKey : apiKey // ignore: cast_nullable_to_non_nullable
as String?,senderId: freezed == senderId ? _self.senderId : senderId // ignore: cast_nullable_to_non_nullable
as String?,otpTemplateId: freezed == otpTemplateId ? _self.otpTemplateId : otpTemplateId // ignore: cast_nullable_to_non_nullable
as String?,reminderTemplateId: freezed == reminderTemplateId ? _self.reminderTemplateId : reminderTemplateId // ignore: cast_nullable_to_non_nullable
as String?,overdueTemplateId: freezed == overdueTemplateId ? _self.overdueTemplateId : overdueTemplateId // ignore: cast_nullable_to_non_nullable
as String?,paymentLinkTemplateId: freezed == paymentLinkTemplateId ? _self.paymentLinkTemplateId : paymentLinkTemplateId // ignore: cast_nullable_to_non_nullable
as String?,receiptTemplateId: freezed == receiptTemplateId ? _self.receiptTemplateId : receiptTemplateId // ignore: cast_nullable_to_non_nullable
as String?,useShortURL: null == useShortURL ? _self.useShortURL : useShortURL // ignore: cast_nullable_to_non_nullable
as bool,trackClicks: null == trackClicks ? _self.trackClicks : trackClicks // ignore: cast_nullable_to_non_nullable
as bool,blockedHours: null == blockedHours ? _self.blockedHours : blockedHours // ignore: cast_nullable_to_non_nullable
as List<String>,
  ));
}

}


/// Adds pattern-matching-related methods to [SMSConfig].
extension SMSConfigPatterns on SMSConfig {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _SMSConfig value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _SMSConfig() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _SMSConfig value)  $default,){
final _that = this;
switch (_that) {
case _SMSConfig():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _SMSConfig value)?  $default,){
final _that = this;
switch (_that) {
case _SMSConfig() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isEnabled,  String? provider,  String? apiKey,  String? senderId,  String? otpTemplateId,  String? reminderTemplateId,  String? overdueTemplateId,  String? paymentLinkTemplateId,  String? receiptTemplateId,  bool useShortURL,  bool trackClicks,  List<String> blockedHours)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _SMSConfig() when $default != null:
return $default(_that.isEnabled,_that.provider,_that.apiKey,_that.senderId,_that.otpTemplateId,_that.reminderTemplateId,_that.overdueTemplateId,_that.paymentLinkTemplateId,_that.receiptTemplateId,_that.useShortURL,_that.trackClicks,_that.blockedHours);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isEnabled,  String? provider,  String? apiKey,  String? senderId,  String? otpTemplateId,  String? reminderTemplateId,  String? overdueTemplateId,  String? paymentLinkTemplateId,  String? receiptTemplateId,  bool useShortURL,  bool trackClicks,  List<String> blockedHours)  $default,) {final _that = this;
switch (_that) {
case _SMSConfig():
return $default(_that.isEnabled,_that.provider,_that.apiKey,_that.senderId,_that.otpTemplateId,_that.reminderTemplateId,_that.overdueTemplateId,_that.paymentLinkTemplateId,_that.receiptTemplateId,_that.useShortURL,_that.trackClicks,_that.blockedHours);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isEnabled,  String? provider,  String? apiKey,  String? senderId,  String? otpTemplateId,  String? reminderTemplateId,  String? overdueTemplateId,  String? paymentLinkTemplateId,  String? receiptTemplateId,  bool useShortURL,  bool trackClicks,  List<String> blockedHours)?  $default,) {final _that = this;
switch (_that) {
case _SMSConfig() when $default != null:
return $default(_that.isEnabled,_that.provider,_that.apiKey,_that.senderId,_that.otpTemplateId,_that.reminderTemplateId,_that.overdueTemplateId,_that.paymentLinkTemplateId,_that.receiptTemplateId,_that.useShortURL,_that.trackClicks,_that.blockedHours);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _SMSConfig implements SMSConfig {
  const _SMSConfig({this.isEnabled = false, this.provider, this.apiKey, this.senderId, this.otpTemplateId, this.reminderTemplateId, this.overdueTemplateId, this.paymentLinkTemplateId, this.receiptTemplateId, this.useShortURL = true, this.trackClicks = true, final  List<String> blockedHours = const []}): _blockedHours = blockedHours;
  factory _SMSConfig.fromJson(Map<String, dynamic> json) => _$SMSConfigFromJson(json);

@override@JsonKey() final  bool isEnabled;
@override final  String? provider;
// Msg91, Twilio, ValueFirst
@override final  String? apiKey;
@override final  String? senderId;
// APSDLRM
// DLT Template IDs (India TRAI compliance)
@override final  String? otpTemplateId;
@override final  String? reminderTemplateId;
@override final  String? overdueTemplateId;
@override final  String? paymentLinkTemplateId;
@override final  String? receiptTemplateId;
// SMS settings
@override@JsonKey() final  bool useShortURL;
@override@JsonKey() final  bool trackClicks;
 final  List<String> _blockedHours;
@override@JsonKey() List<String> get blockedHours {
  if (_blockedHours is EqualUnmodifiableListView) return _blockedHours;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_blockedHours);
}


/// Create a copy of SMSConfig
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$SMSConfigCopyWith<_SMSConfig> get copyWith => __$SMSConfigCopyWithImpl<_SMSConfig>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$SMSConfigToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _SMSConfig&&(identical(other.isEnabled, isEnabled) || other.isEnabled == isEnabled)&&(identical(other.provider, provider) || other.provider == provider)&&(identical(other.apiKey, apiKey) || other.apiKey == apiKey)&&(identical(other.senderId, senderId) || other.senderId == senderId)&&(identical(other.otpTemplateId, otpTemplateId) || other.otpTemplateId == otpTemplateId)&&(identical(other.reminderTemplateId, reminderTemplateId) || other.reminderTemplateId == reminderTemplateId)&&(identical(other.overdueTemplateId, overdueTemplateId) || other.overdueTemplateId == overdueTemplateId)&&(identical(other.paymentLinkTemplateId, paymentLinkTemplateId) || other.paymentLinkTemplateId == paymentLinkTemplateId)&&(identical(other.receiptTemplateId, receiptTemplateId) || other.receiptTemplateId == receiptTemplateId)&&(identical(other.useShortURL, useShortURL) || other.useShortURL == useShortURL)&&(identical(other.trackClicks, trackClicks) || other.trackClicks == trackClicks)&&const DeepCollectionEquality().equals(other._blockedHours, _blockedHours));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,isEnabled,provider,apiKey,senderId,otpTemplateId,reminderTemplateId,overdueTemplateId,paymentLinkTemplateId,receiptTemplateId,useShortURL,trackClicks,const DeepCollectionEquality().hash(_blockedHours));

@override
String toString() {
  return 'SMSConfig(isEnabled: $isEnabled, provider: $provider, apiKey: $apiKey, senderId: $senderId, otpTemplateId: $otpTemplateId, reminderTemplateId: $reminderTemplateId, overdueTemplateId: $overdueTemplateId, paymentLinkTemplateId: $paymentLinkTemplateId, receiptTemplateId: $receiptTemplateId, useShortURL: $useShortURL, trackClicks: $trackClicks, blockedHours: $blockedHours)';
}


}

/// @nodoc
abstract mixin class _$SMSConfigCopyWith<$Res> implements $SMSConfigCopyWith<$Res> {
  factory _$SMSConfigCopyWith(_SMSConfig value, $Res Function(_SMSConfig) _then) = __$SMSConfigCopyWithImpl;
@override @useResult
$Res call({
 bool isEnabled, String? provider, String? apiKey, String? senderId, String? otpTemplateId, String? reminderTemplateId, String? overdueTemplateId, String? paymentLinkTemplateId, String? receiptTemplateId, bool useShortURL, bool trackClicks, List<String> blockedHours
});




}
/// @nodoc
class __$SMSConfigCopyWithImpl<$Res>
    implements _$SMSConfigCopyWith<$Res> {
  __$SMSConfigCopyWithImpl(this._self, this._then);

  final _SMSConfig _self;
  final $Res Function(_SMSConfig) _then;

/// Create a copy of SMSConfig
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isEnabled = null,Object? provider = freezed,Object? apiKey = freezed,Object? senderId = freezed,Object? otpTemplateId = freezed,Object? reminderTemplateId = freezed,Object? overdueTemplateId = freezed,Object? paymentLinkTemplateId = freezed,Object? receiptTemplateId = freezed,Object? useShortURL = null,Object? trackClicks = null,Object? blockedHours = null,}) {
  return _then(_SMSConfig(
isEnabled: null == isEnabled ? _self.isEnabled : isEnabled // ignore: cast_nullable_to_non_nullable
as bool,provider: freezed == provider ? _self.provider : provider // ignore: cast_nullable_to_non_nullable
as String?,apiKey: freezed == apiKey ? _self.apiKey : apiKey // ignore: cast_nullable_to_non_nullable
as String?,senderId: freezed == senderId ? _self.senderId : senderId // ignore: cast_nullable_to_non_nullable
as String?,otpTemplateId: freezed == otpTemplateId ? _self.otpTemplateId : otpTemplateId // ignore: cast_nullable_to_non_nullable
as String?,reminderTemplateId: freezed == reminderTemplateId ? _self.reminderTemplateId : reminderTemplateId // ignore: cast_nullable_to_non_nullable
as String?,overdueTemplateId: freezed == overdueTemplateId ? _self.overdueTemplateId : overdueTemplateId // ignore: cast_nullable_to_non_nullable
as String?,paymentLinkTemplateId: freezed == paymentLinkTemplateId ? _self.paymentLinkTemplateId : paymentLinkTemplateId // ignore: cast_nullable_to_non_nullable
as String?,receiptTemplateId: freezed == receiptTemplateId ? _self.receiptTemplateId : receiptTemplateId // ignore: cast_nullable_to_non_nullable
as String?,useShortURL: null == useShortURL ? _self.useShortURL : useShortURL // ignore: cast_nullable_to_non_nullable
as bool,trackClicks: null == trackClicks ? _self.trackClicks : trackClicks // ignore: cast_nullable_to_non_nullable
as bool,blockedHours: null == blockedHours ? _self._blockedHours : blockedHours // ignore: cast_nullable_to_non_nullable
as List<String>,
  ));
}


}


/// @nodoc
mixin _$EmailConfig {

 bool get isEnabled; String? get provider;// SendGrid, AWS SES, Mailgun
 String? get apiKey; String? get fromEmail; String? get fromName; String? get replyToEmail;// Email templates
 String? get welcomeEmailTemplateId; String? get reminderEmailTemplateId; String? get invoiceEmailTemplateId; String? get receiptEmailTemplateId; String? get newsletterTemplateId;// Settings
 bool get sendHTML; bool get trackOpens; bool get trackClicks; List<String> get bccEmails;
/// Create a copy of EmailConfig
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$EmailConfigCopyWith<EmailConfig> get copyWith => _$EmailConfigCopyWithImpl<EmailConfig>(this as EmailConfig, _$identity);

  /// Serializes this EmailConfig to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is EmailConfig&&(identical(other.isEnabled, isEnabled) || other.isEnabled == isEnabled)&&(identical(other.provider, provider) || other.provider == provider)&&(identical(other.apiKey, apiKey) || other.apiKey == apiKey)&&(identical(other.fromEmail, fromEmail) || other.fromEmail == fromEmail)&&(identical(other.fromName, fromName) || other.fromName == fromName)&&(identical(other.replyToEmail, replyToEmail) || other.replyToEmail == replyToEmail)&&(identical(other.welcomeEmailTemplateId, welcomeEmailTemplateId) || other.welcomeEmailTemplateId == welcomeEmailTemplateId)&&(identical(other.reminderEmailTemplateId, reminderEmailTemplateId) || other.reminderEmailTemplateId == reminderEmailTemplateId)&&(identical(other.invoiceEmailTemplateId, invoiceEmailTemplateId) || other.invoiceEmailTemplateId == invoiceEmailTemplateId)&&(identical(other.receiptEmailTemplateId, receiptEmailTemplateId) || other.receiptEmailTemplateId == receiptEmailTemplateId)&&(identical(other.newsletterTemplateId, newsletterTemplateId) || other.newsletterTemplateId == newsletterTemplateId)&&(identical(other.sendHTML, sendHTML) || other.sendHTML == sendHTML)&&(identical(other.trackOpens, trackOpens) || other.trackOpens == trackOpens)&&(identical(other.trackClicks, trackClicks) || other.trackClicks == trackClicks)&&const DeepCollectionEquality().equals(other.bccEmails, bccEmails));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,isEnabled,provider,apiKey,fromEmail,fromName,replyToEmail,welcomeEmailTemplateId,reminderEmailTemplateId,invoiceEmailTemplateId,receiptEmailTemplateId,newsletterTemplateId,sendHTML,trackOpens,trackClicks,const DeepCollectionEquality().hash(bccEmails));

@override
String toString() {
  return 'EmailConfig(isEnabled: $isEnabled, provider: $provider, apiKey: $apiKey, fromEmail: $fromEmail, fromName: $fromName, replyToEmail: $replyToEmail, welcomeEmailTemplateId: $welcomeEmailTemplateId, reminderEmailTemplateId: $reminderEmailTemplateId, invoiceEmailTemplateId: $invoiceEmailTemplateId, receiptEmailTemplateId: $receiptEmailTemplateId, newsletterTemplateId: $newsletterTemplateId, sendHTML: $sendHTML, trackOpens: $trackOpens, trackClicks: $trackClicks, bccEmails: $bccEmails)';
}


}

/// @nodoc
abstract mixin class $EmailConfigCopyWith<$Res>  {
  factory $EmailConfigCopyWith(EmailConfig value, $Res Function(EmailConfig) _then) = _$EmailConfigCopyWithImpl;
@useResult
$Res call({
 bool isEnabled, String? provider, String? apiKey, String? fromEmail, String? fromName, String? replyToEmail, String? welcomeEmailTemplateId, String? reminderEmailTemplateId, String? invoiceEmailTemplateId, String? receiptEmailTemplateId, String? newsletterTemplateId, bool sendHTML, bool trackOpens, bool trackClicks, List<String> bccEmails
});




}
/// @nodoc
class _$EmailConfigCopyWithImpl<$Res>
    implements $EmailConfigCopyWith<$Res> {
  _$EmailConfigCopyWithImpl(this._self, this._then);

  final EmailConfig _self;
  final $Res Function(EmailConfig) _then;

/// Create a copy of EmailConfig
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isEnabled = null,Object? provider = freezed,Object? apiKey = freezed,Object? fromEmail = freezed,Object? fromName = freezed,Object? replyToEmail = freezed,Object? welcomeEmailTemplateId = freezed,Object? reminderEmailTemplateId = freezed,Object? invoiceEmailTemplateId = freezed,Object? receiptEmailTemplateId = freezed,Object? newsletterTemplateId = freezed,Object? sendHTML = null,Object? trackOpens = null,Object? trackClicks = null,Object? bccEmails = null,}) {
  return _then(_self.copyWith(
isEnabled: null == isEnabled ? _self.isEnabled : isEnabled // ignore: cast_nullable_to_non_nullable
as bool,provider: freezed == provider ? _self.provider : provider // ignore: cast_nullable_to_non_nullable
as String?,apiKey: freezed == apiKey ? _self.apiKey : apiKey // ignore: cast_nullable_to_non_nullable
as String?,fromEmail: freezed == fromEmail ? _self.fromEmail : fromEmail // ignore: cast_nullable_to_non_nullable
as String?,fromName: freezed == fromName ? _self.fromName : fromName // ignore: cast_nullable_to_non_nullable
as String?,replyToEmail: freezed == replyToEmail ? _self.replyToEmail : replyToEmail // ignore: cast_nullable_to_non_nullable
as String?,welcomeEmailTemplateId: freezed == welcomeEmailTemplateId ? _self.welcomeEmailTemplateId : welcomeEmailTemplateId // ignore: cast_nullable_to_non_nullable
as String?,reminderEmailTemplateId: freezed == reminderEmailTemplateId ? _self.reminderEmailTemplateId : reminderEmailTemplateId // ignore: cast_nullable_to_non_nullable
as String?,invoiceEmailTemplateId: freezed == invoiceEmailTemplateId ? _self.invoiceEmailTemplateId : invoiceEmailTemplateId // ignore: cast_nullable_to_non_nullable
as String?,receiptEmailTemplateId: freezed == receiptEmailTemplateId ? _self.receiptEmailTemplateId : receiptEmailTemplateId // ignore: cast_nullable_to_non_nullable
as String?,newsletterTemplateId: freezed == newsletterTemplateId ? _self.newsletterTemplateId : newsletterTemplateId // ignore: cast_nullable_to_non_nullable
as String?,sendHTML: null == sendHTML ? _self.sendHTML : sendHTML // ignore: cast_nullable_to_non_nullable
as bool,trackOpens: null == trackOpens ? _self.trackOpens : trackOpens // ignore: cast_nullable_to_non_nullable
as bool,trackClicks: null == trackClicks ? _self.trackClicks : trackClicks // ignore: cast_nullable_to_non_nullable
as bool,bccEmails: null == bccEmails ? _self.bccEmails : bccEmails // ignore: cast_nullable_to_non_nullable
as List<String>,
  ));
}

}


/// Adds pattern-matching-related methods to [EmailConfig].
extension EmailConfigPatterns on EmailConfig {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _EmailConfig value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _EmailConfig() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _EmailConfig value)  $default,){
final _that = this;
switch (_that) {
case _EmailConfig():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _EmailConfig value)?  $default,){
final _that = this;
switch (_that) {
case _EmailConfig() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isEnabled,  String? provider,  String? apiKey,  String? fromEmail,  String? fromName,  String? replyToEmail,  String? welcomeEmailTemplateId,  String? reminderEmailTemplateId,  String? invoiceEmailTemplateId,  String? receiptEmailTemplateId,  String? newsletterTemplateId,  bool sendHTML,  bool trackOpens,  bool trackClicks,  List<String> bccEmails)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _EmailConfig() when $default != null:
return $default(_that.isEnabled,_that.provider,_that.apiKey,_that.fromEmail,_that.fromName,_that.replyToEmail,_that.welcomeEmailTemplateId,_that.reminderEmailTemplateId,_that.invoiceEmailTemplateId,_that.receiptEmailTemplateId,_that.newsletterTemplateId,_that.sendHTML,_that.trackOpens,_that.trackClicks,_that.bccEmails);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isEnabled,  String? provider,  String? apiKey,  String? fromEmail,  String? fromName,  String? replyToEmail,  String? welcomeEmailTemplateId,  String? reminderEmailTemplateId,  String? invoiceEmailTemplateId,  String? receiptEmailTemplateId,  String? newsletterTemplateId,  bool sendHTML,  bool trackOpens,  bool trackClicks,  List<String> bccEmails)  $default,) {final _that = this;
switch (_that) {
case _EmailConfig():
return $default(_that.isEnabled,_that.provider,_that.apiKey,_that.fromEmail,_that.fromName,_that.replyToEmail,_that.welcomeEmailTemplateId,_that.reminderEmailTemplateId,_that.invoiceEmailTemplateId,_that.receiptEmailTemplateId,_that.newsletterTemplateId,_that.sendHTML,_that.trackOpens,_that.trackClicks,_that.bccEmails);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isEnabled,  String? provider,  String? apiKey,  String? fromEmail,  String? fromName,  String? replyToEmail,  String? welcomeEmailTemplateId,  String? reminderEmailTemplateId,  String? invoiceEmailTemplateId,  String? receiptEmailTemplateId,  String? newsletterTemplateId,  bool sendHTML,  bool trackOpens,  bool trackClicks,  List<String> bccEmails)?  $default,) {final _that = this;
switch (_that) {
case _EmailConfig() when $default != null:
return $default(_that.isEnabled,_that.provider,_that.apiKey,_that.fromEmail,_that.fromName,_that.replyToEmail,_that.welcomeEmailTemplateId,_that.reminderEmailTemplateId,_that.invoiceEmailTemplateId,_that.receiptEmailTemplateId,_that.newsletterTemplateId,_that.sendHTML,_that.trackOpens,_that.trackClicks,_that.bccEmails);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _EmailConfig implements EmailConfig {
  const _EmailConfig({this.isEnabled = false, this.provider, this.apiKey, this.fromEmail, this.fromName, this.replyToEmail, this.welcomeEmailTemplateId, this.reminderEmailTemplateId, this.invoiceEmailTemplateId, this.receiptEmailTemplateId, this.newsletterTemplateId, this.sendHTML = true, this.trackOpens = true, this.trackClicks = true, final  List<String> bccEmails = const []}): _bccEmails = bccEmails;
  factory _EmailConfig.fromJson(Map<String, dynamic> json) => _$EmailConfigFromJson(json);

@override@JsonKey() final  bool isEnabled;
@override final  String? provider;
// SendGrid, AWS SES, Mailgun
@override final  String? apiKey;
@override final  String? fromEmail;
@override final  String? fromName;
@override final  String? replyToEmail;
// Email templates
@override final  String? welcomeEmailTemplateId;
@override final  String? reminderEmailTemplateId;
@override final  String? invoiceEmailTemplateId;
@override final  String? receiptEmailTemplateId;
@override final  String? newsletterTemplateId;
// Settings
@override@JsonKey() final  bool sendHTML;
@override@JsonKey() final  bool trackOpens;
@override@JsonKey() final  bool trackClicks;
 final  List<String> _bccEmails;
@override@JsonKey() List<String> get bccEmails {
  if (_bccEmails is EqualUnmodifiableListView) return _bccEmails;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_bccEmails);
}


/// Create a copy of EmailConfig
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$EmailConfigCopyWith<_EmailConfig> get copyWith => __$EmailConfigCopyWithImpl<_EmailConfig>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$EmailConfigToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _EmailConfig&&(identical(other.isEnabled, isEnabled) || other.isEnabled == isEnabled)&&(identical(other.provider, provider) || other.provider == provider)&&(identical(other.apiKey, apiKey) || other.apiKey == apiKey)&&(identical(other.fromEmail, fromEmail) || other.fromEmail == fromEmail)&&(identical(other.fromName, fromName) || other.fromName == fromName)&&(identical(other.replyToEmail, replyToEmail) || other.replyToEmail == replyToEmail)&&(identical(other.welcomeEmailTemplateId, welcomeEmailTemplateId) || other.welcomeEmailTemplateId == welcomeEmailTemplateId)&&(identical(other.reminderEmailTemplateId, reminderEmailTemplateId) || other.reminderEmailTemplateId == reminderEmailTemplateId)&&(identical(other.invoiceEmailTemplateId, invoiceEmailTemplateId) || other.invoiceEmailTemplateId == invoiceEmailTemplateId)&&(identical(other.receiptEmailTemplateId, receiptEmailTemplateId) || other.receiptEmailTemplateId == receiptEmailTemplateId)&&(identical(other.newsletterTemplateId, newsletterTemplateId) || other.newsletterTemplateId == newsletterTemplateId)&&(identical(other.sendHTML, sendHTML) || other.sendHTML == sendHTML)&&(identical(other.trackOpens, trackOpens) || other.trackOpens == trackOpens)&&(identical(other.trackClicks, trackClicks) || other.trackClicks == trackClicks)&&const DeepCollectionEquality().equals(other._bccEmails, _bccEmails));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,isEnabled,provider,apiKey,fromEmail,fromName,replyToEmail,welcomeEmailTemplateId,reminderEmailTemplateId,invoiceEmailTemplateId,receiptEmailTemplateId,newsletterTemplateId,sendHTML,trackOpens,trackClicks,const DeepCollectionEquality().hash(_bccEmails));

@override
String toString() {
  return 'EmailConfig(isEnabled: $isEnabled, provider: $provider, apiKey: $apiKey, fromEmail: $fromEmail, fromName: $fromName, replyToEmail: $replyToEmail, welcomeEmailTemplateId: $welcomeEmailTemplateId, reminderEmailTemplateId: $reminderEmailTemplateId, invoiceEmailTemplateId: $invoiceEmailTemplateId, receiptEmailTemplateId: $receiptEmailTemplateId, newsletterTemplateId: $newsletterTemplateId, sendHTML: $sendHTML, trackOpens: $trackOpens, trackClicks: $trackClicks, bccEmails: $bccEmails)';
}


}

/// @nodoc
abstract mixin class _$EmailConfigCopyWith<$Res> implements $EmailConfigCopyWith<$Res> {
  factory _$EmailConfigCopyWith(_EmailConfig value, $Res Function(_EmailConfig) _then) = __$EmailConfigCopyWithImpl;
@override @useResult
$Res call({
 bool isEnabled, String? provider, String? apiKey, String? fromEmail, String? fromName, String? replyToEmail, String? welcomeEmailTemplateId, String? reminderEmailTemplateId, String? invoiceEmailTemplateId, String? receiptEmailTemplateId, String? newsletterTemplateId, bool sendHTML, bool trackOpens, bool trackClicks, List<String> bccEmails
});




}
/// @nodoc
class __$EmailConfigCopyWithImpl<$Res>
    implements _$EmailConfigCopyWith<$Res> {
  __$EmailConfigCopyWithImpl(this._self, this._then);

  final _EmailConfig _self;
  final $Res Function(_EmailConfig) _then;

/// Create a copy of EmailConfig
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isEnabled = null,Object? provider = freezed,Object? apiKey = freezed,Object? fromEmail = freezed,Object? fromName = freezed,Object? replyToEmail = freezed,Object? welcomeEmailTemplateId = freezed,Object? reminderEmailTemplateId = freezed,Object? invoiceEmailTemplateId = freezed,Object? receiptEmailTemplateId = freezed,Object? newsletterTemplateId = freezed,Object? sendHTML = null,Object? trackOpens = null,Object? trackClicks = null,Object? bccEmails = null,}) {
  return _then(_EmailConfig(
isEnabled: null == isEnabled ? _self.isEnabled : isEnabled // ignore: cast_nullable_to_non_nullable
as bool,provider: freezed == provider ? _self.provider : provider // ignore: cast_nullable_to_non_nullable
as String?,apiKey: freezed == apiKey ? _self.apiKey : apiKey // ignore: cast_nullable_to_non_nullable
as String?,fromEmail: freezed == fromEmail ? _self.fromEmail : fromEmail // ignore: cast_nullable_to_non_nullable
as String?,fromName: freezed == fromName ? _self.fromName : fromName // ignore: cast_nullable_to_non_nullable
as String?,replyToEmail: freezed == replyToEmail ? _self.replyToEmail : replyToEmail // ignore: cast_nullable_to_non_nullable
as String?,welcomeEmailTemplateId: freezed == welcomeEmailTemplateId ? _self.welcomeEmailTemplateId : welcomeEmailTemplateId // ignore: cast_nullable_to_non_nullable
as String?,reminderEmailTemplateId: freezed == reminderEmailTemplateId ? _self.reminderEmailTemplateId : reminderEmailTemplateId // ignore: cast_nullable_to_non_nullable
as String?,invoiceEmailTemplateId: freezed == invoiceEmailTemplateId ? _self.invoiceEmailTemplateId : invoiceEmailTemplateId // ignore: cast_nullable_to_non_nullable
as String?,receiptEmailTemplateId: freezed == receiptEmailTemplateId ? _self.receiptEmailTemplateId : receiptEmailTemplateId // ignore: cast_nullable_to_non_nullable
as String?,newsletterTemplateId: freezed == newsletterTemplateId ? _self.newsletterTemplateId : newsletterTemplateId // ignore: cast_nullable_to_non_nullable
as String?,sendHTML: null == sendHTML ? _self.sendHTML : sendHTML // ignore: cast_nullable_to_non_nullable
as bool,trackOpens: null == trackOpens ? _self.trackOpens : trackOpens // ignore: cast_nullable_to_non_nullable
as bool,trackClicks: null == trackClicks ? _self.trackClicks : trackClicks // ignore: cast_nullable_to_non_nullable
as bool,bccEmails: null == bccEmails ? _self._bccEmails : bccEmails // ignore: cast_nullable_to_non_nullable
as List<String>,
  ));
}


}


/// @nodoc
mixin _$FieldAgentConfig {

// Agent assignment settings
 String get assignmentMethod;// round_robin, load_based, location_based, performance_based
 int get maxLeadsPerAgent; int get maxDailyVisits;// Location tracking
 bool get trackLocation; int get locationUpdateIntervalMinutes; bool get geoFencingEnabled; int get geoFenceRadiusMeters;// Commission structure
 double get collectionCommissionPercent;// 0.5% of collected amount
 double get perCollectionFixedIncentive; double get targetAchievementBonus;// App settings
 bool get offlineModeEnabled; bool get autoSyncEnabled; int get syncIntervalMinutes;// Notifications
 bool get notifyOnNewAssignment; bool get notifyOnDueListReady; bool get notifyOnCollectionConfirmation;
/// Create a copy of FieldAgentConfig
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FieldAgentConfigCopyWith<FieldAgentConfig> get copyWith => _$FieldAgentConfigCopyWithImpl<FieldAgentConfig>(this as FieldAgentConfig, _$identity);

  /// Serializes this FieldAgentConfig to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FieldAgentConfig&&(identical(other.assignmentMethod, assignmentMethod) || other.assignmentMethod == assignmentMethod)&&(identical(other.maxLeadsPerAgent, maxLeadsPerAgent) || other.maxLeadsPerAgent == maxLeadsPerAgent)&&(identical(other.maxDailyVisits, maxDailyVisits) || other.maxDailyVisits == maxDailyVisits)&&(identical(other.trackLocation, trackLocation) || other.trackLocation == trackLocation)&&(identical(other.locationUpdateIntervalMinutes, locationUpdateIntervalMinutes) || other.locationUpdateIntervalMinutes == locationUpdateIntervalMinutes)&&(identical(other.geoFencingEnabled, geoFencingEnabled) || other.geoFencingEnabled == geoFencingEnabled)&&(identical(other.geoFenceRadiusMeters, geoFenceRadiusMeters) || other.geoFenceRadiusMeters == geoFenceRadiusMeters)&&(identical(other.collectionCommissionPercent, collectionCommissionPercent) || other.collectionCommissionPercent == collectionCommissionPercent)&&(identical(other.perCollectionFixedIncentive, perCollectionFixedIncentive) || other.perCollectionFixedIncentive == perCollectionFixedIncentive)&&(identical(other.targetAchievementBonus, targetAchievementBonus) || other.targetAchievementBonus == targetAchievementBonus)&&(identical(other.offlineModeEnabled, offlineModeEnabled) || other.offlineModeEnabled == offlineModeEnabled)&&(identical(other.autoSyncEnabled, autoSyncEnabled) || other.autoSyncEnabled == autoSyncEnabled)&&(identical(other.syncIntervalMinutes, syncIntervalMinutes) || other.syncIntervalMinutes == syncIntervalMinutes)&&(identical(other.notifyOnNewAssignment, notifyOnNewAssignment) || other.notifyOnNewAssignment == notifyOnNewAssignment)&&(identical(other.notifyOnDueListReady, notifyOnDueListReady) || other.notifyOnDueListReady == notifyOnDueListReady)&&(identical(other.notifyOnCollectionConfirmation, notifyOnCollectionConfirmation) || other.notifyOnCollectionConfirmation == notifyOnCollectionConfirmation));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,assignmentMethod,maxLeadsPerAgent,maxDailyVisits,trackLocation,locationUpdateIntervalMinutes,geoFencingEnabled,geoFenceRadiusMeters,collectionCommissionPercent,perCollectionFixedIncentive,targetAchievementBonus,offlineModeEnabled,autoSyncEnabled,syncIntervalMinutes,notifyOnNewAssignment,notifyOnDueListReady,notifyOnCollectionConfirmation);

@override
String toString() {
  return 'FieldAgentConfig(assignmentMethod: $assignmentMethod, maxLeadsPerAgent: $maxLeadsPerAgent, maxDailyVisits: $maxDailyVisits, trackLocation: $trackLocation, locationUpdateIntervalMinutes: $locationUpdateIntervalMinutes, geoFencingEnabled: $geoFencingEnabled, geoFenceRadiusMeters: $geoFenceRadiusMeters, collectionCommissionPercent: $collectionCommissionPercent, perCollectionFixedIncentive: $perCollectionFixedIncentive, targetAchievementBonus: $targetAchievementBonus, offlineModeEnabled: $offlineModeEnabled, autoSyncEnabled: $autoSyncEnabled, syncIntervalMinutes: $syncIntervalMinutes, notifyOnNewAssignment: $notifyOnNewAssignment, notifyOnDueListReady: $notifyOnDueListReady, notifyOnCollectionConfirmation: $notifyOnCollectionConfirmation)';
}


}

/// @nodoc
abstract mixin class $FieldAgentConfigCopyWith<$Res>  {
  factory $FieldAgentConfigCopyWith(FieldAgentConfig value, $Res Function(FieldAgentConfig) _then) = _$FieldAgentConfigCopyWithImpl;
@useResult
$Res call({
 String assignmentMethod, int maxLeadsPerAgent, int maxDailyVisits, bool trackLocation, int locationUpdateIntervalMinutes, bool geoFencingEnabled, int geoFenceRadiusMeters, double collectionCommissionPercent, double perCollectionFixedIncentive, double targetAchievementBonus, bool offlineModeEnabled, bool autoSyncEnabled, int syncIntervalMinutes, bool notifyOnNewAssignment, bool notifyOnDueListReady, bool notifyOnCollectionConfirmation
});




}
/// @nodoc
class _$FieldAgentConfigCopyWithImpl<$Res>
    implements $FieldAgentConfigCopyWith<$Res> {
  _$FieldAgentConfigCopyWithImpl(this._self, this._then);

  final FieldAgentConfig _self;
  final $Res Function(FieldAgentConfig) _then;

/// Create a copy of FieldAgentConfig
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? assignmentMethod = null,Object? maxLeadsPerAgent = null,Object? maxDailyVisits = null,Object? trackLocation = null,Object? locationUpdateIntervalMinutes = null,Object? geoFencingEnabled = null,Object? geoFenceRadiusMeters = null,Object? collectionCommissionPercent = null,Object? perCollectionFixedIncentive = null,Object? targetAchievementBonus = null,Object? offlineModeEnabled = null,Object? autoSyncEnabled = null,Object? syncIntervalMinutes = null,Object? notifyOnNewAssignment = null,Object? notifyOnDueListReady = null,Object? notifyOnCollectionConfirmation = null,}) {
  return _then(_self.copyWith(
assignmentMethod: null == assignmentMethod ? _self.assignmentMethod : assignmentMethod // ignore: cast_nullable_to_non_nullable
as String,maxLeadsPerAgent: null == maxLeadsPerAgent ? _self.maxLeadsPerAgent : maxLeadsPerAgent // ignore: cast_nullable_to_non_nullable
as int,maxDailyVisits: null == maxDailyVisits ? _self.maxDailyVisits : maxDailyVisits // ignore: cast_nullable_to_non_nullable
as int,trackLocation: null == trackLocation ? _self.trackLocation : trackLocation // ignore: cast_nullable_to_non_nullable
as bool,locationUpdateIntervalMinutes: null == locationUpdateIntervalMinutes ? _self.locationUpdateIntervalMinutes : locationUpdateIntervalMinutes // ignore: cast_nullable_to_non_nullable
as int,geoFencingEnabled: null == geoFencingEnabled ? _self.geoFencingEnabled : geoFencingEnabled // ignore: cast_nullable_to_non_nullable
as bool,geoFenceRadiusMeters: null == geoFenceRadiusMeters ? _self.geoFenceRadiusMeters : geoFenceRadiusMeters // ignore: cast_nullable_to_non_nullable
as int,collectionCommissionPercent: null == collectionCommissionPercent ? _self.collectionCommissionPercent : collectionCommissionPercent // ignore: cast_nullable_to_non_nullable
as double,perCollectionFixedIncentive: null == perCollectionFixedIncentive ? _self.perCollectionFixedIncentive : perCollectionFixedIncentive // ignore: cast_nullable_to_non_nullable
as double,targetAchievementBonus: null == targetAchievementBonus ? _self.targetAchievementBonus : targetAchievementBonus // ignore: cast_nullable_to_non_nullable
as double,offlineModeEnabled: null == offlineModeEnabled ? _self.offlineModeEnabled : offlineModeEnabled // ignore: cast_nullable_to_non_nullable
as bool,autoSyncEnabled: null == autoSyncEnabled ? _self.autoSyncEnabled : autoSyncEnabled // ignore: cast_nullable_to_non_nullable
as bool,syncIntervalMinutes: null == syncIntervalMinutes ? _self.syncIntervalMinutes : syncIntervalMinutes // ignore: cast_nullable_to_non_nullable
as int,notifyOnNewAssignment: null == notifyOnNewAssignment ? _self.notifyOnNewAssignment : notifyOnNewAssignment // ignore: cast_nullable_to_non_nullable
as bool,notifyOnDueListReady: null == notifyOnDueListReady ? _self.notifyOnDueListReady : notifyOnDueListReady // ignore: cast_nullable_to_non_nullable
as bool,notifyOnCollectionConfirmation: null == notifyOnCollectionConfirmation ? _self.notifyOnCollectionConfirmation : notifyOnCollectionConfirmation // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [FieldAgentConfig].
extension FieldAgentConfigPatterns on FieldAgentConfig {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _FieldAgentConfig value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _FieldAgentConfig() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _FieldAgentConfig value)  $default,){
final _that = this;
switch (_that) {
case _FieldAgentConfig():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _FieldAgentConfig value)?  $default,){
final _that = this;
switch (_that) {
case _FieldAgentConfig() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String assignmentMethod,  int maxLeadsPerAgent,  int maxDailyVisits,  bool trackLocation,  int locationUpdateIntervalMinutes,  bool geoFencingEnabled,  int geoFenceRadiusMeters,  double collectionCommissionPercent,  double perCollectionFixedIncentive,  double targetAchievementBonus,  bool offlineModeEnabled,  bool autoSyncEnabled,  int syncIntervalMinutes,  bool notifyOnNewAssignment,  bool notifyOnDueListReady,  bool notifyOnCollectionConfirmation)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _FieldAgentConfig() when $default != null:
return $default(_that.assignmentMethod,_that.maxLeadsPerAgent,_that.maxDailyVisits,_that.trackLocation,_that.locationUpdateIntervalMinutes,_that.geoFencingEnabled,_that.geoFenceRadiusMeters,_that.collectionCommissionPercent,_that.perCollectionFixedIncentive,_that.targetAchievementBonus,_that.offlineModeEnabled,_that.autoSyncEnabled,_that.syncIntervalMinutes,_that.notifyOnNewAssignment,_that.notifyOnDueListReady,_that.notifyOnCollectionConfirmation);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String assignmentMethod,  int maxLeadsPerAgent,  int maxDailyVisits,  bool trackLocation,  int locationUpdateIntervalMinutes,  bool geoFencingEnabled,  int geoFenceRadiusMeters,  double collectionCommissionPercent,  double perCollectionFixedIncentive,  double targetAchievementBonus,  bool offlineModeEnabled,  bool autoSyncEnabled,  int syncIntervalMinutes,  bool notifyOnNewAssignment,  bool notifyOnDueListReady,  bool notifyOnCollectionConfirmation)  $default,) {final _that = this;
switch (_that) {
case _FieldAgentConfig():
return $default(_that.assignmentMethod,_that.maxLeadsPerAgent,_that.maxDailyVisits,_that.trackLocation,_that.locationUpdateIntervalMinutes,_that.geoFencingEnabled,_that.geoFenceRadiusMeters,_that.collectionCommissionPercent,_that.perCollectionFixedIncentive,_that.targetAchievementBonus,_that.offlineModeEnabled,_that.autoSyncEnabled,_that.syncIntervalMinutes,_that.notifyOnNewAssignment,_that.notifyOnDueListReady,_that.notifyOnCollectionConfirmation);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String assignmentMethod,  int maxLeadsPerAgent,  int maxDailyVisits,  bool trackLocation,  int locationUpdateIntervalMinutes,  bool geoFencingEnabled,  int geoFenceRadiusMeters,  double collectionCommissionPercent,  double perCollectionFixedIncentive,  double targetAchievementBonus,  bool offlineModeEnabled,  bool autoSyncEnabled,  int syncIntervalMinutes,  bool notifyOnNewAssignment,  bool notifyOnDueListReady,  bool notifyOnCollectionConfirmation)?  $default,) {final _that = this;
switch (_that) {
case _FieldAgentConfig() when $default != null:
return $default(_that.assignmentMethod,_that.maxLeadsPerAgent,_that.maxDailyVisits,_that.trackLocation,_that.locationUpdateIntervalMinutes,_that.geoFencingEnabled,_that.geoFenceRadiusMeters,_that.collectionCommissionPercent,_that.perCollectionFixedIncentive,_that.targetAchievementBonus,_that.offlineModeEnabled,_that.autoSyncEnabled,_that.syncIntervalMinutes,_that.notifyOnNewAssignment,_that.notifyOnDueListReady,_that.notifyOnCollectionConfirmation);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _FieldAgentConfig implements FieldAgentConfig {
  const _FieldAgentConfig({this.assignmentMethod = 'round_robin', this.maxLeadsPerAgent = 20, this.maxDailyVisits = 50, this.trackLocation = true, this.locationUpdateIntervalMinutes = 5, this.geoFencingEnabled = true, this.geoFenceRadiusMeters = 500, this.collectionCommissionPercent = 0.5, this.perCollectionFixedIncentive = 50, this.targetAchievementBonus = 500, this.offlineModeEnabled = true, this.autoSyncEnabled = true, this.syncIntervalMinutes = 15, this.notifyOnNewAssignment = true, this.notifyOnDueListReady = true, this.notifyOnCollectionConfirmation = true});
  factory _FieldAgentConfig.fromJson(Map<String, dynamic> json) => _$FieldAgentConfigFromJson(json);

// Agent assignment settings
@override@JsonKey() final  String assignmentMethod;
// round_robin, load_based, location_based, performance_based
@override@JsonKey() final  int maxLeadsPerAgent;
@override@JsonKey() final  int maxDailyVisits;
// Location tracking
@override@JsonKey() final  bool trackLocation;
@override@JsonKey() final  int locationUpdateIntervalMinutes;
@override@JsonKey() final  bool geoFencingEnabled;
@override@JsonKey() final  int geoFenceRadiusMeters;
// Commission structure
@override@JsonKey() final  double collectionCommissionPercent;
// 0.5% of collected amount
@override@JsonKey() final  double perCollectionFixedIncentive;
@override@JsonKey() final  double targetAchievementBonus;
// App settings
@override@JsonKey() final  bool offlineModeEnabled;
@override@JsonKey() final  bool autoSyncEnabled;
@override@JsonKey() final  int syncIntervalMinutes;
// Notifications
@override@JsonKey() final  bool notifyOnNewAssignment;
@override@JsonKey() final  bool notifyOnDueListReady;
@override@JsonKey() final  bool notifyOnCollectionConfirmation;

/// Create a copy of FieldAgentConfig
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$FieldAgentConfigCopyWith<_FieldAgentConfig> get copyWith => __$FieldAgentConfigCopyWithImpl<_FieldAgentConfig>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$FieldAgentConfigToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _FieldAgentConfig&&(identical(other.assignmentMethod, assignmentMethod) || other.assignmentMethod == assignmentMethod)&&(identical(other.maxLeadsPerAgent, maxLeadsPerAgent) || other.maxLeadsPerAgent == maxLeadsPerAgent)&&(identical(other.maxDailyVisits, maxDailyVisits) || other.maxDailyVisits == maxDailyVisits)&&(identical(other.trackLocation, trackLocation) || other.trackLocation == trackLocation)&&(identical(other.locationUpdateIntervalMinutes, locationUpdateIntervalMinutes) || other.locationUpdateIntervalMinutes == locationUpdateIntervalMinutes)&&(identical(other.geoFencingEnabled, geoFencingEnabled) || other.geoFencingEnabled == geoFencingEnabled)&&(identical(other.geoFenceRadiusMeters, geoFenceRadiusMeters) || other.geoFenceRadiusMeters == geoFenceRadiusMeters)&&(identical(other.collectionCommissionPercent, collectionCommissionPercent) || other.collectionCommissionPercent == collectionCommissionPercent)&&(identical(other.perCollectionFixedIncentive, perCollectionFixedIncentive) || other.perCollectionFixedIncentive == perCollectionFixedIncentive)&&(identical(other.targetAchievementBonus, targetAchievementBonus) || other.targetAchievementBonus == targetAchievementBonus)&&(identical(other.offlineModeEnabled, offlineModeEnabled) || other.offlineModeEnabled == offlineModeEnabled)&&(identical(other.autoSyncEnabled, autoSyncEnabled) || other.autoSyncEnabled == autoSyncEnabled)&&(identical(other.syncIntervalMinutes, syncIntervalMinutes) || other.syncIntervalMinutes == syncIntervalMinutes)&&(identical(other.notifyOnNewAssignment, notifyOnNewAssignment) || other.notifyOnNewAssignment == notifyOnNewAssignment)&&(identical(other.notifyOnDueListReady, notifyOnDueListReady) || other.notifyOnDueListReady == notifyOnDueListReady)&&(identical(other.notifyOnCollectionConfirmation, notifyOnCollectionConfirmation) || other.notifyOnCollectionConfirmation == notifyOnCollectionConfirmation));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,assignmentMethod,maxLeadsPerAgent,maxDailyVisits,trackLocation,locationUpdateIntervalMinutes,geoFencingEnabled,geoFenceRadiusMeters,collectionCommissionPercent,perCollectionFixedIncentive,targetAchievementBonus,offlineModeEnabled,autoSyncEnabled,syncIntervalMinutes,notifyOnNewAssignment,notifyOnDueListReady,notifyOnCollectionConfirmation);

@override
String toString() {
  return 'FieldAgentConfig(assignmentMethod: $assignmentMethod, maxLeadsPerAgent: $maxLeadsPerAgent, maxDailyVisits: $maxDailyVisits, trackLocation: $trackLocation, locationUpdateIntervalMinutes: $locationUpdateIntervalMinutes, geoFencingEnabled: $geoFencingEnabled, geoFenceRadiusMeters: $geoFenceRadiusMeters, collectionCommissionPercent: $collectionCommissionPercent, perCollectionFixedIncentive: $perCollectionFixedIncentive, targetAchievementBonus: $targetAchievementBonus, offlineModeEnabled: $offlineModeEnabled, autoSyncEnabled: $autoSyncEnabled, syncIntervalMinutes: $syncIntervalMinutes, notifyOnNewAssignment: $notifyOnNewAssignment, notifyOnDueListReady: $notifyOnDueListReady, notifyOnCollectionConfirmation: $notifyOnCollectionConfirmation)';
}


}

/// @nodoc
abstract mixin class _$FieldAgentConfigCopyWith<$Res> implements $FieldAgentConfigCopyWith<$Res> {
  factory _$FieldAgentConfigCopyWith(_FieldAgentConfig value, $Res Function(_FieldAgentConfig) _then) = __$FieldAgentConfigCopyWithImpl;
@override @useResult
$Res call({
 String assignmentMethod, int maxLeadsPerAgent, int maxDailyVisits, bool trackLocation, int locationUpdateIntervalMinutes, bool geoFencingEnabled, int geoFenceRadiusMeters, double collectionCommissionPercent, double perCollectionFixedIncentive, double targetAchievementBonus, bool offlineModeEnabled, bool autoSyncEnabled, int syncIntervalMinutes, bool notifyOnNewAssignment, bool notifyOnDueListReady, bool notifyOnCollectionConfirmation
});




}
/// @nodoc
class __$FieldAgentConfigCopyWithImpl<$Res>
    implements _$FieldAgentConfigCopyWith<$Res> {
  __$FieldAgentConfigCopyWithImpl(this._self, this._then);

  final _FieldAgentConfig _self;
  final $Res Function(_FieldAgentConfig) _then;

/// Create a copy of FieldAgentConfig
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? assignmentMethod = null,Object? maxLeadsPerAgent = null,Object? maxDailyVisits = null,Object? trackLocation = null,Object? locationUpdateIntervalMinutes = null,Object? geoFencingEnabled = null,Object? geoFenceRadiusMeters = null,Object? collectionCommissionPercent = null,Object? perCollectionFixedIncentive = null,Object? targetAchievementBonus = null,Object? offlineModeEnabled = null,Object? autoSyncEnabled = null,Object? syncIntervalMinutes = null,Object? notifyOnNewAssignment = null,Object? notifyOnDueListReady = null,Object? notifyOnCollectionConfirmation = null,}) {
  return _then(_FieldAgentConfig(
assignmentMethod: null == assignmentMethod ? _self.assignmentMethod : assignmentMethod // ignore: cast_nullable_to_non_nullable
as String,maxLeadsPerAgent: null == maxLeadsPerAgent ? _self.maxLeadsPerAgent : maxLeadsPerAgent // ignore: cast_nullable_to_non_nullable
as int,maxDailyVisits: null == maxDailyVisits ? _self.maxDailyVisits : maxDailyVisits // ignore: cast_nullable_to_non_nullable
as int,trackLocation: null == trackLocation ? _self.trackLocation : trackLocation // ignore: cast_nullable_to_non_nullable
as bool,locationUpdateIntervalMinutes: null == locationUpdateIntervalMinutes ? _self.locationUpdateIntervalMinutes : locationUpdateIntervalMinutes // ignore: cast_nullable_to_non_nullable
as int,geoFencingEnabled: null == geoFencingEnabled ? _self.geoFencingEnabled : geoFencingEnabled // ignore: cast_nullable_to_non_nullable
as bool,geoFenceRadiusMeters: null == geoFenceRadiusMeters ? _self.geoFenceRadiusMeters : geoFenceRadiusMeters // ignore: cast_nullable_to_non_nullable
as int,collectionCommissionPercent: null == collectionCommissionPercent ? _self.collectionCommissionPercent : collectionCommissionPercent // ignore: cast_nullable_to_non_nullable
as double,perCollectionFixedIncentive: null == perCollectionFixedIncentive ? _self.perCollectionFixedIncentive : perCollectionFixedIncentive // ignore: cast_nullable_to_non_nullable
as double,targetAchievementBonus: null == targetAchievementBonus ? _self.targetAchievementBonus : targetAchievementBonus // ignore: cast_nullable_to_non_nullable
as double,offlineModeEnabled: null == offlineModeEnabled ? _self.offlineModeEnabled : offlineModeEnabled // ignore: cast_nullable_to_non_nullable
as bool,autoSyncEnabled: null == autoSyncEnabled ? _self.autoSyncEnabled : autoSyncEnabled // ignore: cast_nullable_to_non_nullable
as bool,syncIntervalMinutes: null == syncIntervalMinutes ? _self.syncIntervalMinutes : syncIntervalMinutes // ignore: cast_nullable_to_non_nullable
as int,notifyOnNewAssignment: null == notifyOnNewAssignment ? _self.notifyOnNewAssignment : notifyOnNewAssignment // ignore: cast_nullable_to_non_nullable
as bool,notifyOnDueListReady: null == notifyOnDueListReady ? _self.notifyOnDueListReady : notifyOnDueListReady // ignore: cast_nullable_to_non_nullable
as bool,notifyOnCollectionConfirmation: null == notifyOnCollectionConfirmation ? _self.notifyOnCollectionConfirmation : notifyOnCollectionConfirmation // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}


/// @nodoc
mixin _$AIConfig {

// AI Lead Scoring
 bool get enableLeadScoring; bool get autoAssignLeads;// AI Communication
 bool get enableAIVoiceCalls; bool get enableAIWhatsApp; bool get enableAIPersonalization;// AI Prediction
 bool get predictDefaultRisk; bool get predictBestCollectionTime; bool get predictCustomerResponse;// AI Document Processing
 bool get enableOCR; bool get enableAutoReceiptGeneration;// AI Assistant
 bool get enableFieldAgentAIAssistant; bool get enableCustomerAIChatbot;
/// Create a copy of AIConfig
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AIConfigCopyWith<AIConfig> get copyWith => _$AIConfigCopyWithImpl<AIConfig>(this as AIConfig, _$identity);

  /// Serializes this AIConfig to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AIConfig&&(identical(other.enableLeadScoring, enableLeadScoring) || other.enableLeadScoring == enableLeadScoring)&&(identical(other.autoAssignLeads, autoAssignLeads) || other.autoAssignLeads == autoAssignLeads)&&(identical(other.enableAIVoiceCalls, enableAIVoiceCalls) || other.enableAIVoiceCalls == enableAIVoiceCalls)&&(identical(other.enableAIWhatsApp, enableAIWhatsApp) || other.enableAIWhatsApp == enableAIWhatsApp)&&(identical(other.enableAIPersonalization, enableAIPersonalization) || other.enableAIPersonalization == enableAIPersonalization)&&(identical(other.predictDefaultRisk, predictDefaultRisk) || other.predictDefaultRisk == predictDefaultRisk)&&(identical(other.predictBestCollectionTime, predictBestCollectionTime) || other.predictBestCollectionTime == predictBestCollectionTime)&&(identical(other.predictCustomerResponse, predictCustomerResponse) || other.predictCustomerResponse == predictCustomerResponse)&&(identical(other.enableOCR, enableOCR) || other.enableOCR == enableOCR)&&(identical(other.enableAutoReceiptGeneration, enableAutoReceiptGeneration) || other.enableAutoReceiptGeneration == enableAutoReceiptGeneration)&&(identical(other.enableFieldAgentAIAssistant, enableFieldAgentAIAssistant) || other.enableFieldAgentAIAssistant == enableFieldAgentAIAssistant)&&(identical(other.enableCustomerAIChatbot, enableCustomerAIChatbot) || other.enableCustomerAIChatbot == enableCustomerAIChatbot));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,enableLeadScoring,autoAssignLeads,enableAIVoiceCalls,enableAIWhatsApp,enableAIPersonalization,predictDefaultRisk,predictBestCollectionTime,predictCustomerResponse,enableOCR,enableAutoReceiptGeneration,enableFieldAgentAIAssistant,enableCustomerAIChatbot);

@override
String toString() {
  return 'AIConfig(enableLeadScoring: $enableLeadScoring, autoAssignLeads: $autoAssignLeads, enableAIVoiceCalls: $enableAIVoiceCalls, enableAIWhatsApp: $enableAIWhatsApp, enableAIPersonalization: $enableAIPersonalization, predictDefaultRisk: $predictDefaultRisk, predictBestCollectionTime: $predictBestCollectionTime, predictCustomerResponse: $predictCustomerResponse, enableOCR: $enableOCR, enableAutoReceiptGeneration: $enableAutoReceiptGeneration, enableFieldAgentAIAssistant: $enableFieldAgentAIAssistant, enableCustomerAIChatbot: $enableCustomerAIChatbot)';
}


}

/// @nodoc
abstract mixin class $AIConfigCopyWith<$Res>  {
  factory $AIConfigCopyWith(AIConfig value, $Res Function(AIConfig) _then) = _$AIConfigCopyWithImpl;
@useResult
$Res call({
 bool enableLeadScoring, bool autoAssignLeads, bool enableAIVoiceCalls, bool enableAIWhatsApp, bool enableAIPersonalization, bool predictDefaultRisk, bool predictBestCollectionTime, bool predictCustomerResponse, bool enableOCR, bool enableAutoReceiptGeneration, bool enableFieldAgentAIAssistant, bool enableCustomerAIChatbot
});




}
/// @nodoc
class _$AIConfigCopyWithImpl<$Res>
    implements $AIConfigCopyWith<$Res> {
  _$AIConfigCopyWithImpl(this._self, this._then);

  final AIConfig _self;
  final $Res Function(AIConfig) _then;

/// Create a copy of AIConfig
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? enableLeadScoring = null,Object? autoAssignLeads = null,Object? enableAIVoiceCalls = null,Object? enableAIWhatsApp = null,Object? enableAIPersonalization = null,Object? predictDefaultRisk = null,Object? predictBestCollectionTime = null,Object? predictCustomerResponse = null,Object? enableOCR = null,Object? enableAutoReceiptGeneration = null,Object? enableFieldAgentAIAssistant = null,Object? enableCustomerAIChatbot = null,}) {
  return _then(_self.copyWith(
enableLeadScoring: null == enableLeadScoring ? _self.enableLeadScoring : enableLeadScoring // ignore: cast_nullable_to_non_nullable
as bool,autoAssignLeads: null == autoAssignLeads ? _self.autoAssignLeads : autoAssignLeads // ignore: cast_nullable_to_non_nullable
as bool,enableAIVoiceCalls: null == enableAIVoiceCalls ? _self.enableAIVoiceCalls : enableAIVoiceCalls // ignore: cast_nullable_to_non_nullable
as bool,enableAIWhatsApp: null == enableAIWhatsApp ? _self.enableAIWhatsApp : enableAIWhatsApp // ignore: cast_nullable_to_non_nullable
as bool,enableAIPersonalization: null == enableAIPersonalization ? _self.enableAIPersonalization : enableAIPersonalization // ignore: cast_nullable_to_non_nullable
as bool,predictDefaultRisk: null == predictDefaultRisk ? _self.predictDefaultRisk : predictDefaultRisk // ignore: cast_nullable_to_non_nullable
as bool,predictBestCollectionTime: null == predictBestCollectionTime ? _self.predictBestCollectionTime : predictBestCollectionTime // ignore: cast_nullable_to_non_nullable
as bool,predictCustomerResponse: null == predictCustomerResponse ? _self.predictCustomerResponse : predictCustomerResponse // ignore: cast_nullable_to_non_nullable
as bool,enableOCR: null == enableOCR ? _self.enableOCR : enableOCR // ignore: cast_nullable_to_non_nullable
as bool,enableAutoReceiptGeneration: null == enableAutoReceiptGeneration ? _self.enableAutoReceiptGeneration : enableAutoReceiptGeneration // ignore: cast_nullable_to_non_nullable
as bool,enableFieldAgentAIAssistant: null == enableFieldAgentAIAssistant ? _self.enableFieldAgentAIAssistant : enableFieldAgentAIAssistant // ignore: cast_nullable_to_non_nullable
as bool,enableCustomerAIChatbot: null == enableCustomerAIChatbot ? _self.enableCustomerAIChatbot : enableCustomerAIChatbot // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [AIConfig].
extension AIConfigPatterns on AIConfig {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AIConfig value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AIConfig() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AIConfig value)  $default,){
final _that = this;
switch (_that) {
case _AIConfig():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AIConfig value)?  $default,){
final _that = this;
switch (_that) {
case _AIConfig() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool enableLeadScoring,  bool autoAssignLeads,  bool enableAIVoiceCalls,  bool enableAIWhatsApp,  bool enableAIPersonalization,  bool predictDefaultRisk,  bool predictBestCollectionTime,  bool predictCustomerResponse,  bool enableOCR,  bool enableAutoReceiptGeneration,  bool enableFieldAgentAIAssistant,  bool enableCustomerAIChatbot)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AIConfig() when $default != null:
return $default(_that.enableLeadScoring,_that.autoAssignLeads,_that.enableAIVoiceCalls,_that.enableAIWhatsApp,_that.enableAIPersonalization,_that.predictDefaultRisk,_that.predictBestCollectionTime,_that.predictCustomerResponse,_that.enableOCR,_that.enableAutoReceiptGeneration,_that.enableFieldAgentAIAssistant,_that.enableCustomerAIChatbot);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool enableLeadScoring,  bool autoAssignLeads,  bool enableAIVoiceCalls,  bool enableAIWhatsApp,  bool enableAIPersonalization,  bool predictDefaultRisk,  bool predictBestCollectionTime,  bool predictCustomerResponse,  bool enableOCR,  bool enableAutoReceiptGeneration,  bool enableFieldAgentAIAssistant,  bool enableCustomerAIChatbot)  $default,) {final _that = this;
switch (_that) {
case _AIConfig():
return $default(_that.enableLeadScoring,_that.autoAssignLeads,_that.enableAIVoiceCalls,_that.enableAIWhatsApp,_that.enableAIPersonalization,_that.predictDefaultRisk,_that.predictBestCollectionTime,_that.predictCustomerResponse,_that.enableOCR,_that.enableAutoReceiptGeneration,_that.enableFieldAgentAIAssistant,_that.enableCustomerAIChatbot);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool enableLeadScoring,  bool autoAssignLeads,  bool enableAIVoiceCalls,  bool enableAIWhatsApp,  bool enableAIPersonalization,  bool predictDefaultRisk,  bool predictBestCollectionTime,  bool predictCustomerResponse,  bool enableOCR,  bool enableAutoReceiptGeneration,  bool enableFieldAgentAIAssistant,  bool enableCustomerAIChatbot)?  $default,) {final _that = this;
switch (_that) {
case _AIConfig() when $default != null:
return $default(_that.enableLeadScoring,_that.autoAssignLeads,_that.enableAIVoiceCalls,_that.enableAIWhatsApp,_that.enableAIPersonalization,_that.predictDefaultRisk,_that.predictBestCollectionTime,_that.predictCustomerResponse,_that.enableOCR,_that.enableAutoReceiptGeneration,_that.enableFieldAgentAIAssistant,_that.enableCustomerAIChatbot);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _AIConfig implements AIConfig {
  const _AIConfig({this.enableLeadScoring = true, this.autoAssignLeads = true, this.enableAIVoiceCalls = true, this.enableAIWhatsApp = true, this.enableAIPersonalization = true, this.predictDefaultRisk = true, this.predictBestCollectionTime = true, this.predictCustomerResponse = true, this.enableOCR = true, this.enableAutoReceiptGeneration = true, this.enableFieldAgentAIAssistant = true, this.enableCustomerAIChatbot = true});
  factory _AIConfig.fromJson(Map<String, dynamic> json) => _$AIConfigFromJson(json);

// AI Lead Scoring
@override@JsonKey() final  bool enableLeadScoring;
@override@JsonKey() final  bool autoAssignLeads;
// AI Communication
@override@JsonKey() final  bool enableAIVoiceCalls;
@override@JsonKey() final  bool enableAIWhatsApp;
@override@JsonKey() final  bool enableAIPersonalization;
// AI Prediction
@override@JsonKey() final  bool predictDefaultRisk;
@override@JsonKey() final  bool predictBestCollectionTime;
@override@JsonKey() final  bool predictCustomerResponse;
// AI Document Processing
@override@JsonKey() final  bool enableOCR;
@override@JsonKey() final  bool enableAutoReceiptGeneration;
// AI Assistant
@override@JsonKey() final  bool enableFieldAgentAIAssistant;
@override@JsonKey() final  bool enableCustomerAIChatbot;

/// Create a copy of AIConfig
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AIConfigCopyWith<_AIConfig> get copyWith => __$AIConfigCopyWithImpl<_AIConfig>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$AIConfigToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _AIConfig&&(identical(other.enableLeadScoring, enableLeadScoring) || other.enableLeadScoring == enableLeadScoring)&&(identical(other.autoAssignLeads, autoAssignLeads) || other.autoAssignLeads == autoAssignLeads)&&(identical(other.enableAIVoiceCalls, enableAIVoiceCalls) || other.enableAIVoiceCalls == enableAIVoiceCalls)&&(identical(other.enableAIWhatsApp, enableAIWhatsApp) || other.enableAIWhatsApp == enableAIWhatsApp)&&(identical(other.enableAIPersonalization, enableAIPersonalization) || other.enableAIPersonalization == enableAIPersonalization)&&(identical(other.predictDefaultRisk, predictDefaultRisk) || other.predictDefaultRisk == predictDefaultRisk)&&(identical(other.predictBestCollectionTime, predictBestCollectionTime) || other.predictBestCollectionTime == predictBestCollectionTime)&&(identical(other.predictCustomerResponse, predictCustomerResponse) || other.predictCustomerResponse == predictCustomerResponse)&&(identical(other.enableOCR, enableOCR) || other.enableOCR == enableOCR)&&(identical(other.enableAutoReceiptGeneration, enableAutoReceiptGeneration) || other.enableAutoReceiptGeneration == enableAutoReceiptGeneration)&&(identical(other.enableFieldAgentAIAssistant, enableFieldAgentAIAssistant) || other.enableFieldAgentAIAssistant == enableFieldAgentAIAssistant)&&(identical(other.enableCustomerAIChatbot, enableCustomerAIChatbot) || other.enableCustomerAIChatbot == enableCustomerAIChatbot));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,enableLeadScoring,autoAssignLeads,enableAIVoiceCalls,enableAIWhatsApp,enableAIPersonalization,predictDefaultRisk,predictBestCollectionTime,predictCustomerResponse,enableOCR,enableAutoReceiptGeneration,enableFieldAgentAIAssistant,enableCustomerAIChatbot);

@override
String toString() {
  return 'AIConfig(enableLeadScoring: $enableLeadScoring, autoAssignLeads: $autoAssignLeads, enableAIVoiceCalls: $enableAIVoiceCalls, enableAIWhatsApp: $enableAIWhatsApp, enableAIPersonalization: $enableAIPersonalization, predictDefaultRisk: $predictDefaultRisk, predictBestCollectionTime: $predictBestCollectionTime, predictCustomerResponse: $predictCustomerResponse, enableOCR: $enableOCR, enableAutoReceiptGeneration: $enableAutoReceiptGeneration, enableFieldAgentAIAssistant: $enableFieldAgentAIAssistant, enableCustomerAIChatbot: $enableCustomerAIChatbot)';
}


}

/// @nodoc
abstract mixin class _$AIConfigCopyWith<$Res> implements $AIConfigCopyWith<$Res> {
  factory _$AIConfigCopyWith(_AIConfig value, $Res Function(_AIConfig) _then) = __$AIConfigCopyWithImpl;
@override @useResult
$Res call({
 bool enableLeadScoring, bool autoAssignLeads, bool enableAIVoiceCalls, bool enableAIWhatsApp, bool enableAIPersonalization, bool predictDefaultRisk, bool predictBestCollectionTime, bool predictCustomerResponse, bool enableOCR, bool enableAutoReceiptGeneration, bool enableFieldAgentAIAssistant, bool enableCustomerAIChatbot
});




}
/// @nodoc
class __$AIConfigCopyWithImpl<$Res>
    implements _$AIConfigCopyWith<$Res> {
  __$AIConfigCopyWithImpl(this._self, this._then);

  final _AIConfig _self;
  final $Res Function(_AIConfig) _then;

/// Create a copy of AIConfig
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? enableLeadScoring = null,Object? autoAssignLeads = null,Object? enableAIVoiceCalls = null,Object? enableAIWhatsApp = null,Object? enableAIPersonalization = null,Object? predictDefaultRisk = null,Object? predictBestCollectionTime = null,Object? predictCustomerResponse = null,Object? enableOCR = null,Object? enableAutoReceiptGeneration = null,Object? enableFieldAgentAIAssistant = null,Object? enableCustomerAIChatbot = null,}) {
  return _then(_AIConfig(
enableLeadScoring: null == enableLeadScoring ? _self.enableLeadScoring : enableLeadScoring // ignore: cast_nullable_to_non_nullable
as bool,autoAssignLeads: null == autoAssignLeads ? _self.autoAssignLeads : autoAssignLeads // ignore: cast_nullable_to_non_nullable
as bool,enableAIVoiceCalls: null == enableAIVoiceCalls ? _self.enableAIVoiceCalls : enableAIVoiceCalls // ignore: cast_nullable_to_non_nullable
as bool,enableAIWhatsApp: null == enableAIWhatsApp ? _self.enableAIWhatsApp : enableAIWhatsApp // ignore: cast_nullable_to_non_nullable
as bool,enableAIPersonalization: null == enableAIPersonalization ? _self.enableAIPersonalization : enableAIPersonalization // ignore: cast_nullable_to_non_nullable
as bool,predictDefaultRisk: null == predictDefaultRisk ? _self.predictDefaultRisk : predictDefaultRisk // ignore: cast_nullable_to_non_nullable
as bool,predictBestCollectionTime: null == predictBestCollectionTime ? _self.predictBestCollectionTime : predictBestCollectionTime // ignore: cast_nullable_to_non_nullable
as bool,predictCustomerResponse: null == predictCustomerResponse ? _self.predictCustomerResponse : predictCustomerResponse // ignore: cast_nullable_to_non_nullable
as bool,enableOCR: null == enableOCR ? _self.enableOCR : enableOCR // ignore: cast_nullable_to_non_nullable
as bool,enableAutoReceiptGeneration: null == enableAutoReceiptGeneration ? _self.enableAutoReceiptGeneration : enableAutoReceiptGeneration // ignore: cast_nullable_to_non_nullable
as bool,enableFieldAgentAIAssistant: null == enableFieldAgentAIAssistant ? _self.enableFieldAgentAIAssistant : enableFieldAgentAIAssistant // ignore: cast_nullable_to_non_nullable
as bool,enableCustomerAIChatbot: null == enableCustomerAIChatbot ? _self.enableCustomerAIChatbot : enableCustomerAIChatbot // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}


/// @nodoc
mixin _$AutomationRule {

 String get id; String get name; String get type;// reminder, escalation, collection
 String get trigger;// days_before_due, days_after_due, amount_threshold
 int get triggerValue;// 3 (days), 5000 (amount)
// Actions to take
 List<String> get actions;// whatsapp, sms, email, call, agent_notify
// Timing
 String get scheduleTime;// 09:00
 String? get scheduleDays;// monday,tuesday,wednesday
// Priority
 int get priority;// Conditions
 String? get conditionAmount;// > 10000
 String? get conditionStatus;// regular, irregular, defaulter
// Message templates
 String? get whatsappTemplate; String? get smsTemplate; String? get emailTemplate; String? get voiceMessage;// Status
 bool get isActive; DateTime get createdAt;
/// Create a copy of AutomationRule
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AutomationRuleCopyWith<AutomationRule> get copyWith => _$AutomationRuleCopyWithImpl<AutomationRule>(this as AutomationRule, _$identity);

  /// Serializes this AutomationRule to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AutomationRule&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.type, type) || other.type == type)&&(identical(other.trigger, trigger) || other.trigger == trigger)&&(identical(other.triggerValue, triggerValue) || other.triggerValue == triggerValue)&&const DeepCollectionEquality().equals(other.actions, actions)&&(identical(other.scheduleTime, scheduleTime) || other.scheduleTime == scheduleTime)&&(identical(other.scheduleDays, scheduleDays) || other.scheduleDays == scheduleDays)&&(identical(other.priority, priority) || other.priority == priority)&&(identical(other.conditionAmount, conditionAmount) || other.conditionAmount == conditionAmount)&&(identical(other.conditionStatus, conditionStatus) || other.conditionStatus == conditionStatus)&&(identical(other.whatsappTemplate, whatsappTemplate) || other.whatsappTemplate == whatsappTemplate)&&(identical(other.smsTemplate, smsTemplate) || other.smsTemplate == smsTemplate)&&(identical(other.emailTemplate, emailTemplate) || other.emailTemplate == emailTemplate)&&(identical(other.voiceMessage, voiceMessage) || other.voiceMessage == voiceMessage)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,type,trigger,triggerValue,const DeepCollectionEquality().hash(actions),scheduleTime,scheduleDays,priority,conditionAmount,conditionStatus,whatsappTemplate,smsTemplate,emailTemplate,voiceMessage,isActive,createdAt);

@override
String toString() {
  return 'AutomationRule(id: $id, name: $name, type: $type, trigger: $trigger, triggerValue: $triggerValue, actions: $actions, scheduleTime: $scheduleTime, scheduleDays: $scheduleDays, priority: $priority, conditionAmount: $conditionAmount, conditionStatus: $conditionStatus, whatsappTemplate: $whatsappTemplate, smsTemplate: $smsTemplate, emailTemplate: $emailTemplate, voiceMessage: $voiceMessage, isActive: $isActive, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class $AutomationRuleCopyWith<$Res>  {
  factory $AutomationRuleCopyWith(AutomationRule value, $Res Function(AutomationRule) _then) = _$AutomationRuleCopyWithImpl;
@useResult
$Res call({
 String id, String name, String type, String trigger, int triggerValue, List<String> actions, String scheduleTime, String? scheduleDays, int priority, String? conditionAmount, String? conditionStatus, String? whatsappTemplate, String? smsTemplate, String? emailTemplate, String? voiceMessage, bool isActive, DateTime createdAt
});




}
/// @nodoc
class _$AutomationRuleCopyWithImpl<$Res>
    implements $AutomationRuleCopyWith<$Res> {
  _$AutomationRuleCopyWithImpl(this._self, this._then);

  final AutomationRule _self;
  final $Res Function(AutomationRule) _then;

/// Create a copy of AutomationRule
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? type = null,Object? trigger = null,Object? triggerValue = null,Object? actions = null,Object? scheduleTime = null,Object? scheduleDays = freezed,Object? priority = null,Object? conditionAmount = freezed,Object? conditionStatus = freezed,Object? whatsappTemplate = freezed,Object? smsTemplate = freezed,Object? emailTemplate = freezed,Object? voiceMessage = freezed,Object? isActive = null,Object? createdAt = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,trigger: null == trigger ? _self.trigger : trigger // ignore: cast_nullable_to_non_nullable
as String,triggerValue: null == triggerValue ? _self.triggerValue : triggerValue // ignore: cast_nullable_to_non_nullable
as int,actions: null == actions ? _self.actions : actions // ignore: cast_nullable_to_non_nullable
as List<String>,scheduleTime: null == scheduleTime ? _self.scheduleTime : scheduleTime // ignore: cast_nullable_to_non_nullable
as String,scheduleDays: freezed == scheduleDays ? _self.scheduleDays : scheduleDays // ignore: cast_nullable_to_non_nullable
as String?,priority: null == priority ? _self.priority : priority // ignore: cast_nullable_to_non_nullable
as int,conditionAmount: freezed == conditionAmount ? _self.conditionAmount : conditionAmount // ignore: cast_nullable_to_non_nullable
as String?,conditionStatus: freezed == conditionStatus ? _self.conditionStatus : conditionStatus // ignore: cast_nullable_to_non_nullable
as String?,whatsappTemplate: freezed == whatsappTemplate ? _self.whatsappTemplate : whatsappTemplate // ignore: cast_nullable_to_non_nullable
as String?,smsTemplate: freezed == smsTemplate ? _self.smsTemplate : smsTemplate // ignore: cast_nullable_to_non_nullable
as String?,emailTemplate: freezed == emailTemplate ? _self.emailTemplate : emailTemplate // ignore: cast_nullable_to_non_nullable
as String?,voiceMessage: freezed == voiceMessage ? _self.voiceMessage : voiceMessage // ignore: cast_nullable_to_non_nullable
as String?,isActive: null == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [AutomationRule].
extension AutomationRulePatterns on AutomationRule {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AutomationRule value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AutomationRule() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AutomationRule value)  $default,){
final _that = this;
switch (_that) {
case _AutomationRule():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AutomationRule value)?  $default,){
final _that = this;
switch (_that) {
case _AutomationRule() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String name,  String type,  String trigger,  int triggerValue,  List<String> actions,  String scheduleTime,  String? scheduleDays,  int priority,  String? conditionAmount,  String? conditionStatus,  String? whatsappTemplate,  String? smsTemplate,  String? emailTemplate,  String? voiceMessage,  bool isActive,  DateTime createdAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AutomationRule() when $default != null:
return $default(_that.id,_that.name,_that.type,_that.trigger,_that.triggerValue,_that.actions,_that.scheduleTime,_that.scheduleDays,_that.priority,_that.conditionAmount,_that.conditionStatus,_that.whatsappTemplate,_that.smsTemplate,_that.emailTemplate,_that.voiceMessage,_that.isActive,_that.createdAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String name,  String type,  String trigger,  int triggerValue,  List<String> actions,  String scheduleTime,  String? scheduleDays,  int priority,  String? conditionAmount,  String? conditionStatus,  String? whatsappTemplate,  String? smsTemplate,  String? emailTemplate,  String? voiceMessage,  bool isActive,  DateTime createdAt)  $default,) {final _that = this;
switch (_that) {
case _AutomationRule():
return $default(_that.id,_that.name,_that.type,_that.trigger,_that.triggerValue,_that.actions,_that.scheduleTime,_that.scheduleDays,_that.priority,_that.conditionAmount,_that.conditionStatus,_that.whatsappTemplate,_that.smsTemplate,_that.emailTemplate,_that.voiceMessage,_that.isActive,_that.createdAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String name,  String type,  String trigger,  int triggerValue,  List<String> actions,  String scheduleTime,  String? scheduleDays,  int priority,  String? conditionAmount,  String? conditionStatus,  String? whatsappTemplate,  String? smsTemplate,  String? emailTemplate,  String? voiceMessage,  bool isActive,  DateTime createdAt)?  $default,) {final _that = this;
switch (_that) {
case _AutomationRule() when $default != null:
return $default(_that.id,_that.name,_that.type,_that.trigger,_that.triggerValue,_that.actions,_that.scheduleTime,_that.scheduleDays,_that.priority,_that.conditionAmount,_that.conditionStatus,_that.whatsappTemplate,_that.smsTemplate,_that.emailTemplate,_that.voiceMessage,_that.isActive,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _AutomationRule implements AutomationRule {
  const _AutomationRule({this.id = '', this.name = '', this.type = '', this.trigger = '', this.triggerValue = 0, final  List<String> actions = const [], this.scheduleTime = '09:00', this.scheduleDays, this.priority = 1, this.conditionAmount, this.conditionStatus, this.whatsappTemplate, this.smsTemplate, this.emailTemplate, this.voiceMessage, this.isActive = true, required this.createdAt}): _actions = actions;
  factory _AutomationRule.fromJson(Map<String, dynamic> json) => _$AutomationRuleFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String name;
@override@JsonKey() final  String type;
// reminder, escalation, collection
@override@JsonKey() final  String trigger;
// days_before_due, days_after_due, amount_threshold
@override@JsonKey() final  int triggerValue;
// 3 (days), 5000 (amount)
// Actions to take
 final  List<String> _actions;
// 3 (days), 5000 (amount)
// Actions to take
@override@JsonKey() List<String> get actions {
  if (_actions is EqualUnmodifiableListView) return _actions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_actions);
}

// whatsapp, sms, email, call, agent_notify
// Timing
@override@JsonKey() final  String scheduleTime;
// 09:00
@override final  String? scheduleDays;
// monday,tuesday,wednesday
// Priority
@override@JsonKey() final  int priority;
// Conditions
@override final  String? conditionAmount;
// > 10000
@override final  String? conditionStatus;
// regular, irregular, defaulter
// Message templates
@override final  String? whatsappTemplate;
@override final  String? smsTemplate;
@override final  String? emailTemplate;
@override final  String? voiceMessage;
// Status
@override@JsonKey() final  bool isActive;
@override final  DateTime createdAt;

/// Create a copy of AutomationRule
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AutomationRuleCopyWith<_AutomationRule> get copyWith => __$AutomationRuleCopyWithImpl<_AutomationRule>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$AutomationRuleToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _AutomationRule&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.type, type) || other.type == type)&&(identical(other.trigger, trigger) || other.trigger == trigger)&&(identical(other.triggerValue, triggerValue) || other.triggerValue == triggerValue)&&const DeepCollectionEquality().equals(other._actions, _actions)&&(identical(other.scheduleTime, scheduleTime) || other.scheduleTime == scheduleTime)&&(identical(other.scheduleDays, scheduleDays) || other.scheduleDays == scheduleDays)&&(identical(other.priority, priority) || other.priority == priority)&&(identical(other.conditionAmount, conditionAmount) || other.conditionAmount == conditionAmount)&&(identical(other.conditionStatus, conditionStatus) || other.conditionStatus == conditionStatus)&&(identical(other.whatsappTemplate, whatsappTemplate) || other.whatsappTemplate == whatsappTemplate)&&(identical(other.smsTemplate, smsTemplate) || other.smsTemplate == smsTemplate)&&(identical(other.emailTemplate, emailTemplate) || other.emailTemplate == emailTemplate)&&(identical(other.voiceMessage, voiceMessage) || other.voiceMessage == voiceMessage)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,type,trigger,triggerValue,const DeepCollectionEquality().hash(_actions),scheduleTime,scheduleDays,priority,conditionAmount,conditionStatus,whatsappTemplate,smsTemplate,emailTemplate,voiceMessage,isActive,createdAt);

@override
String toString() {
  return 'AutomationRule(id: $id, name: $name, type: $type, trigger: $trigger, triggerValue: $triggerValue, actions: $actions, scheduleTime: $scheduleTime, scheduleDays: $scheduleDays, priority: $priority, conditionAmount: $conditionAmount, conditionStatus: $conditionStatus, whatsappTemplate: $whatsappTemplate, smsTemplate: $smsTemplate, emailTemplate: $emailTemplate, voiceMessage: $voiceMessage, isActive: $isActive, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$AutomationRuleCopyWith<$Res> implements $AutomationRuleCopyWith<$Res> {
  factory _$AutomationRuleCopyWith(_AutomationRule value, $Res Function(_AutomationRule) _then) = __$AutomationRuleCopyWithImpl;
@override @useResult
$Res call({
 String id, String name, String type, String trigger, int triggerValue, List<String> actions, String scheduleTime, String? scheduleDays, int priority, String? conditionAmount, String? conditionStatus, String? whatsappTemplate, String? smsTemplate, String? emailTemplate, String? voiceMessage, bool isActive, DateTime createdAt
});




}
/// @nodoc
class __$AutomationRuleCopyWithImpl<$Res>
    implements _$AutomationRuleCopyWith<$Res> {
  __$AutomationRuleCopyWithImpl(this._self, this._then);

  final _AutomationRule _self;
  final $Res Function(_AutomationRule) _then;

/// Create a copy of AutomationRule
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? type = null,Object? trigger = null,Object? triggerValue = null,Object? actions = null,Object? scheduleTime = null,Object? scheduleDays = freezed,Object? priority = null,Object? conditionAmount = freezed,Object? conditionStatus = freezed,Object? whatsappTemplate = freezed,Object? smsTemplate = freezed,Object? emailTemplate = freezed,Object? voiceMessage = freezed,Object? isActive = null,Object? createdAt = null,}) {
  return _then(_AutomationRule(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,trigger: null == trigger ? _self.trigger : trigger // ignore: cast_nullable_to_non_nullable
as String,triggerValue: null == triggerValue ? _self.triggerValue : triggerValue // ignore: cast_nullable_to_non_nullable
as int,actions: null == actions ? _self._actions : actions // ignore: cast_nullable_to_non_nullable
as List<String>,scheduleTime: null == scheduleTime ? _self.scheduleTime : scheduleTime // ignore: cast_nullable_to_non_nullable
as String,scheduleDays: freezed == scheduleDays ? _self.scheduleDays : scheduleDays // ignore: cast_nullable_to_non_nullable
as String?,priority: null == priority ? _self.priority : priority // ignore: cast_nullable_to_non_nullable
as int,conditionAmount: freezed == conditionAmount ? _self.conditionAmount : conditionAmount // ignore: cast_nullable_to_non_nullable
as String?,conditionStatus: freezed == conditionStatus ? _self.conditionStatus : conditionStatus // ignore: cast_nullable_to_non_nullable
as String?,whatsappTemplate: freezed == whatsappTemplate ? _self.whatsappTemplate : whatsappTemplate // ignore: cast_nullable_to_non_nullable
as String?,smsTemplate: freezed == smsTemplate ? _self.smsTemplate : smsTemplate // ignore: cast_nullable_to_non_nullable
as String?,emailTemplate: freezed == emailTemplate ? _self.emailTemplate : emailTemplate // ignore: cast_nullable_to_non_nullable
as String?,voiceMessage: freezed == voiceMessage ? _self.voiceMessage : voiceMessage // ignore: cast_nullable_to_non_nullable
as String?,isActive: null == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}


/// @nodoc
mixin _$AutomationExecution {

 String get id; String get ruleId; String get ruleName; String get customerId; String get bookingId; String get emiId;// Execution details
 String get channel;// whatsapp, sms, email, call, agent_app
 String get action;// reminder_sent, call_made, agent_notified
 String get status;// success, failed, pending, scheduled
// Content
 String? get messageContent; String? get templateUsed; Map<String, dynamic>? get metadata;// Timing
 DateTime get scheduledAt; DateTime? get executedAt; DateTime? get deliveredAt;// Response
 String? get customerResponse; DateTime? get responseAt; String? get responseType;// will_pay, cannot_pay, asked_time, paid
// Error
 String? get errorMessage; int? get retryCount; DateTime get createdAt;
/// Create a copy of AutomationExecution
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AutomationExecutionCopyWith<AutomationExecution> get copyWith => _$AutomationExecutionCopyWithImpl<AutomationExecution>(this as AutomationExecution, _$identity);

  /// Serializes this AutomationExecution to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AutomationExecution&&(identical(other.id, id) || other.id == id)&&(identical(other.ruleId, ruleId) || other.ruleId == ruleId)&&(identical(other.ruleName, ruleName) || other.ruleName == ruleName)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.emiId, emiId) || other.emiId == emiId)&&(identical(other.channel, channel) || other.channel == channel)&&(identical(other.action, action) || other.action == action)&&(identical(other.status, status) || other.status == status)&&(identical(other.messageContent, messageContent) || other.messageContent == messageContent)&&(identical(other.templateUsed, templateUsed) || other.templateUsed == templateUsed)&&const DeepCollectionEquality().equals(other.metadata, metadata)&&(identical(other.scheduledAt, scheduledAt) || other.scheduledAt == scheduledAt)&&(identical(other.executedAt, executedAt) || other.executedAt == executedAt)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.customerResponse, customerResponse) || other.customerResponse == customerResponse)&&(identical(other.responseAt, responseAt) || other.responseAt == responseAt)&&(identical(other.responseType, responseType) || other.responseType == responseType)&&(identical(other.errorMessage, errorMessage) || other.errorMessage == errorMessage)&&(identical(other.retryCount, retryCount) || other.retryCount == retryCount)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,ruleId,ruleName,customerId,bookingId,emiId,channel,action,status,messageContent,templateUsed,const DeepCollectionEquality().hash(metadata),scheduledAt,executedAt,deliveredAt,customerResponse,responseAt,responseType,errorMessage,retryCount,createdAt]);

@override
String toString() {
  return 'AutomationExecution(id: $id, ruleId: $ruleId, ruleName: $ruleName, customerId: $customerId, bookingId: $bookingId, emiId: $emiId, channel: $channel, action: $action, status: $status, messageContent: $messageContent, templateUsed: $templateUsed, metadata: $metadata, scheduledAt: $scheduledAt, executedAt: $executedAt, deliveredAt: $deliveredAt, customerResponse: $customerResponse, responseAt: $responseAt, responseType: $responseType, errorMessage: $errorMessage, retryCount: $retryCount, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class $AutomationExecutionCopyWith<$Res>  {
  factory $AutomationExecutionCopyWith(AutomationExecution value, $Res Function(AutomationExecution) _then) = _$AutomationExecutionCopyWithImpl;
@useResult
$Res call({
 String id, String ruleId, String ruleName, String customerId, String bookingId, String emiId, String channel, String action, String status, String? messageContent, String? templateUsed, Map<String, dynamic>? metadata, DateTime scheduledAt, DateTime? executedAt, DateTime? deliveredAt, String? customerResponse, DateTime? responseAt, String? responseType, String? errorMessage, int? retryCount, DateTime createdAt
});




}
/// @nodoc
class _$AutomationExecutionCopyWithImpl<$Res>
    implements $AutomationExecutionCopyWith<$Res> {
  _$AutomationExecutionCopyWithImpl(this._self, this._then);

  final AutomationExecution _self;
  final $Res Function(AutomationExecution) _then;

/// Create a copy of AutomationExecution
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? ruleId = null,Object? ruleName = null,Object? customerId = null,Object? bookingId = null,Object? emiId = null,Object? channel = null,Object? action = null,Object? status = null,Object? messageContent = freezed,Object? templateUsed = freezed,Object? metadata = freezed,Object? scheduledAt = null,Object? executedAt = freezed,Object? deliveredAt = freezed,Object? customerResponse = freezed,Object? responseAt = freezed,Object? responseType = freezed,Object? errorMessage = freezed,Object? retryCount = freezed,Object? createdAt = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,ruleId: null == ruleId ? _self.ruleId : ruleId // ignore: cast_nullable_to_non_nullable
as String,ruleName: null == ruleName ? _self.ruleName : ruleName // ignore: cast_nullable_to_non_nullable
as String,customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,emiId: null == emiId ? _self.emiId : emiId // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,action: null == action ? _self.action : action // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,messageContent: freezed == messageContent ? _self.messageContent : messageContent // ignore: cast_nullable_to_non_nullable
as String?,templateUsed: freezed == templateUsed ? _self.templateUsed : templateUsed // ignore: cast_nullable_to_non_nullable
as String?,metadata: freezed == metadata ? _self.metadata : metadata // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,scheduledAt: null == scheduledAt ? _self.scheduledAt : scheduledAt // ignore: cast_nullable_to_non_nullable
as DateTime,executedAt: freezed == executedAt ? _self.executedAt : executedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,customerResponse: freezed == customerResponse ? _self.customerResponse : customerResponse // ignore: cast_nullable_to_non_nullable
as String?,responseAt: freezed == responseAt ? _self.responseAt : responseAt // ignore: cast_nullable_to_non_nullable
as DateTime?,responseType: freezed == responseType ? _self.responseType : responseType // ignore: cast_nullable_to_non_nullable
as String?,errorMessage: freezed == errorMessage ? _self.errorMessage : errorMessage // ignore: cast_nullable_to_non_nullable
as String?,retryCount: freezed == retryCount ? _self.retryCount : retryCount // ignore: cast_nullable_to_non_nullable
as int?,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [AutomationExecution].
extension AutomationExecutionPatterns on AutomationExecution {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AutomationExecution value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AutomationExecution() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AutomationExecution value)  $default,){
final _that = this;
switch (_that) {
case _AutomationExecution():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AutomationExecution value)?  $default,){
final _that = this;
switch (_that) {
case _AutomationExecution() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String ruleId,  String ruleName,  String customerId,  String bookingId,  String emiId,  String channel,  String action,  String status,  String? messageContent,  String? templateUsed,  Map<String, dynamic>? metadata,  DateTime scheduledAt,  DateTime? executedAt,  DateTime? deliveredAt,  String? customerResponse,  DateTime? responseAt,  String? responseType,  String? errorMessage,  int? retryCount,  DateTime createdAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AutomationExecution() when $default != null:
return $default(_that.id,_that.ruleId,_that.ruleName,_that.customerId,_that.bookingId,_that.emiId,_that.channel,_that.action,_that.status,_that.messageContent,_that.templateUsed,_that.metadata,_that.scheduledAt,_that.executedAt,_that.deliveredAt,_that.customerResponse,_that.responseAt,_that.responseType,_that.errorMessage,_that.retryCount,_that.createdAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String ruleId,  String ruleName,  String customerId,  String bookingId,  String emiId,  String channel,  String action,  String status,  String? messageContent,  String? templateUsed,  Map<String, dynamic>? metadata,  DateTime scheduledAt,  DateTime? executedAt,  DateTime? deliveredAt,  String? customerResponse,  DateTime? responseAt,  String? responseType,  String? errorMessage,  int? retryCount,  DateTime createdAt)  $default,) {final _that = this;
switch (_that) {
case _AutomationExecution():
return $default(_that.id,_that.ruleId,_that.ruleName,_that.customerId,_that.bookingId,_that.emiId,_that.channel,_that.action,_that.status,_that.messageContent,_that.templateUsed,_that.metadata,_that.scheduledAt,_that.executedAt,_that.deliveredAt,_that.customerResponse,_that.responseAt,_that.responseType,_that.errorMessage,_that.retryCount,_that.createdAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String ruleId,  String ruleName,  String customerId,  String bookingId,  String emiId,  String channel,  String action,  String status,  String? messageContent,  String? templateUsed,  Map<String, dynamic>? metadata,  DateTime scheduledAt,  DateTime? executedAt,  DateTime? deliveredAt,  String? customerResponse,  DateTime? responseAt,  String? responseType,  String? errorMessage,  int? retryCount,  DateTime createdAt)?  $default,) {final _that = this;
switch (_that) {
case _AutomationExecution() when $default != null:
return $default(_that.id,_that.ruleId,_that.ruleName,_that.customerId,_that.bookingId,_that.emiId,_that.channel,_that.action,_that.status,_that.messageContent,_that.templateUsed,_that.metadata,_that.scheduledAt,_that.executedAt,_that.deliveredAt,_that.customerResponse,_that.responseAt,_that.responseType,_that.errorMessage,_that.retryCount,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _AutomationExecution implements AutomationExecution {
  const _AutomationExecution({this.id = '', this.ruleId = '', this.ruleName = '', this.customerId = '', this.bookingId = '', this.emiId = '', this.channel = '', this.action = '', this.status = '', this.messageContent, this.templateUsed, final  Map<String, dynamic>? metadata, required this.scheduledAt, this.executedAt, this.deliveredAt, this.customerResponse, this.responseAt, this.responseType, this.errorMessage, this.retryCount, required this.createdAt}): _metadata = metadata;
  factory _AutomationExecution.fromJson(Map<String, dynamic> json) => _$AutomationExecutionFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String ruleId;
@override@JsonKey() final  String ruleName;
@override@JsonKey() final  String customerId;
@override@JsonKey() final  String bookingId;
@override@JsonKey() final  String emiId;
// Execution details
@override@JsonKey() final  String channel;
// whatsapp, sms, email, call, agent_app
@override@JsonKey() final  String action;
// reminder_sent, call_made, agent_notified
@override@JsonKey() final  String status;
// success, failed, pending, scheduled
// Content
@override final  String? messageContent;
@override final  String? templateUsed;
 final  Map<String, dynamic>? _metadata;
@override Map<String, dynamic>? get metadata {
  final value = _metadata;
  if (value == null) return null;
  if (_metadata is EqualUnmodifiableMapView) return _metadata;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(value);
}

// Timing
@override final  DateTime scheduledAt;
@override final  DateTime? executedAt;
@override final  DateTime? deliveredAt;
// Response
@override final  String? customerResponse;
@override final  DateTime? responseAt;
@override final  String? responseType;
// will_pay, cannot_pay, asked_time, paid
// Error
@override final  String? errorMessage;
@override final  int? retryCount;
@override final  DateTime createdAt;

/// Create a copy of AutomationExecution
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AutomationExecutionCopyWith<_AutomationExecution> get copyWith => __$AutomationExecutionCopyWithImpl<_AutomationExecution>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$AutomationExecutionToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _AutomationExecution&&(identical(other.id, id) || other.id == id)&&(identical(other.ruleId, ruleId) || other.ruleId == ruleId)&&(identical(other.ruleName, ruleName) || other.ruleName == ruleName)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.emiId, emiId) || other.emiId == emiId)&&(identical(other.channel, channel) || other.channel == channel)&&(identical(other.action, action) || other.action == action)&&(identical(other.status, status) || other.status == status)&&(identical(other.messageContent, messageContent) || other.messageContent == messageContent)&&(identical(other.templateUsed, templateUsed) || other.templateUsed == templateUsed)&&const DeepCollectionEquality().equals(other._metadata, _metadata)&&(identical(other.scheduledAt, scheduledAt) || other.scheduledAt == scheduledAt)&&(identical(other.executedAt, executedAt) || other.executedAt == executedAt)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.customerResponse, customerResponse) || other.customerResponse == customerResponse)&&(identical(other.responseAt, responseAt) || other.responseAt == responseAt)&&(identical(other.responseType, responseType) || other.responseType == responseType)&&(identical(other.errorMessage, errorMessage) || other.errorMessage == errorMessage)&&(identical(other.retryCount, retryCount) || other.retryCount == retryCount)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,ruleId,ruleName,customerId,bookingId,emiId,channel,action,status,messageContent,templateUsed,const DeepCollectionEquality().hash(_metadata),scheduledAt,executedAt,deliveredAt,customerResponse,responseAt,responseType,errorMessage,retryCount,createdAt]);

@override
String toString() {
  return 'AutomationExecution(id: $id, ruleId: $ruleId, ruleName: $ruleName, customerId: $customerId, bookingId: $bookingId, emiId: $emiId, channel: $channel, action: $action, status: $status, messageContent: $messageContent, templateUsed: $templateUsed, metadata: $metadata, scheduledAt: $scheduledAt, executedAt: $executedAt, deliveredAt: $deliveredAt, customerResponse: $customerResponse, responseAt: $responseAt, responseType: $responseType, errorMessage: $errorMessage, retryCount: $retryCount, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$AutomationExecutionCopyWith<$Res> implements $AutomationExecutionCopyWith<$Res> {
  factory _$AutomationExecutionCopyWith(_AutomationExecution value, $Res Function(_AutomationExecution) _then) = __$AutomationExecutionCopyWithImpl;
@override @useResult
$Res call({
 String id, String ruleId, String ruleName, String customerId, String bookingId, String emiId, String channel, String action, String status, String? messageContent, String? templateUsed, Map<String, dynamic>? metadata, DateTime scheduledAt, DateTime? executedAt, DateTime? deliveredAt, String? customerResponse, DateTime? responseAt, String? responseType, String? errorMessage, int? retryCount, DateTime createdAt
});




}
/// @nodoc
class __$AutomationExecutionCopyWithImpl<$Res>
    implements _$AutomationExecutionCopyWith<$Res> {
  __$AutomationExecutionCopyWithImpl(this._self, this._then);

  final _AutomationExecution _self;
  final $Res Function(_AutomationExecution) _then;

/// Create a copy of AutomationExecution
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? ruleId = null,Object? ruleName = null,Object? customerId = null,Object? bookingId = null,Object? emiId = null,Object? channel = null,Object? action = null,Object? status = null,Object? messageContent = freezed,Object? templateUsed = freezed,Object? metadata = freezed,Object? scheduledAt = null,Object? executedAt = freezed,Object? deliveredAt = freezed,Object? customerResponse = freezed,Object? responseAt = freezed,Object? responseType = freezed,Object? errorMessage = freezed,Object? retryCount = freezed,Object? createdAt = null,}) {
  return _then(_AutomationExecution(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,ruleId: null == ruleId ? _self.ruleId : ruleId // ignore: cast_nullable_to_non_nullable
as String,ruleName: null == ruleName ? _self.ruleName : ruleName // ignore: cast_nullable_to_non_nullable
as String,customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,emiId: null == emiId ? _self.emiId : emiId // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,action: null == action ? _self.action : action // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,messageContent: freezed == messageContent ? _self.messageContent : messageContent // ignore: cast_nullable_to_non_nullable
as String?,templateUsed: freezed == templateUsed ? _self.templateUsed : templateUsed // ignore: cast_nullable_to_non_nullable
as String?,metadata: freezed == metadata ? _self._metadata : metadata // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>?,scheduledAt: null == scheduledAt ? _self.scheduledAt : scheduledAt // ignore: cast_nullable_to_non_nullable
as DateTime,executedAt: freezed == executedAt ? _self.executedAt : executedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,customerResponse: freezed == customerResponse ? _self.customerResponse : customerResponse // ignore: cast_nullable_to_non_nullable
as String?,responseAt: freezed == responseAt ? _self.responseAt : responseAt // ignore: cast_nullable_to_non_nullable
as DateTime?,responseType: freezed == responseType ? _self.responseType : responseType // ignore: cast_nullable_to_non_nullable
as String?,errorMessage: freezed == errorMessage ? _self.errorMessage : errorMessage // ignore: cast_nullable_to_non_nullable
as String?,retryCount: freezed == retryCount ? _self.retryCount : retryCount // ignore: cast_nullable_to_non_nullable
as int?,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}


/// @nodoc
mixin _$CustomerCommunicationLog {

 String get id; String get customerId; String get bookingId;// Communication details
 String get channel;// whatsapp, sms, email, call, agent_visit
 String get direction;// outgoing, incoming
 String get type;// reminder, follow_up, payment_confirmation, enquiry
// Content
 String? get message; String? get attachmentUrl; String? get callRecordingUrl; int? get callDurationSeconds;// Status
 String get status;// sent, delivered, read, failed
 DateTime? get sentAt; DateTime? get deliveredAt; DateTime? get readAt;// Agent info (if agent initiated)
 String? get agentId; String? get agentName;// AI/Automation info
 bool get wasAutomated; String? get automationRuleId;// Customer response
 String? get customerReply; DateTime? get repliedAt;// Notes
 String? get adminNotes; DateTime get createdAt;
/// Create a copy of CustomerCommunicationLog
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CustomerCommunicationLogCopyWith<CustomerCommunicationLog> get copyWith => _$CustomerCommunicationLogCopyWithImpl<CustomerCommunicationLog>(this as CustomerCommunicationLog, _$identity);

  /// Serializes this CustomerCommunicationLog to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CustomerCommunicationLog&&(identical(other.id, id) || other.id == id)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.channel, channel) || other.channel == channel)&&(identical(other.direction, direction) || other.direction == direction)&&(identical(other.type, type) || other.type == type)&&(identical(other.message, message) || other.message == message)&&(identical(other.attachmentUrl, attachmentUrl) || other.attachmentUrl == attachmentUrl)&&(identical(other.callRecordingUrl, callRecordingUrl) || other.callRecordingUrl == callRecordingUrl)&&(identical(other.callDurationSeconds, callDurationSeconds) || other.callDurationSeconds == callDurationSeconds)&&(identical(other.status, status) || other.status == status)&&(identical(other.sentAt, sentAt) || other.sentAt == sentAt)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.readAt, readAt) || other.readAt == readAt)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.agentName, agentName) || other.agentName == agentName)&&(identical(other.wasAutomated, wasAutomated) || other.wasAutomated == wasAutomated)&&(identical(other.automationRuleId, automationRuleId) || other.automationRuleId == automationRuleId)&&(identical(other.customerReply, customerReply) || other.customerReply == customerReply)&&(identical(other.repliedAt, repliedAt) || other.repliedAt == repliedAt)&&(identical(other.adminNotes, adminNotes) || other.adminNotes == adminNotes)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,customerId,bookingId,channel,direction,type,message,attachmentUrl,callRecordingUrl,callDurationSeconds,status,sentAt,deliveredAt,readAt,agentId,agentName,wasAutomated,automationRuleId,customerReply,repliedAt,adminNotes,createdAt]);

@override
String toString() {
  return 'CustomerCommunicationLog(id: $id, customerId: $customerId, bookingId: $bookingId, channel: $channel, direction: $direction, type: $type, message: $message, attachmentUrl: $attachmentUrl, callRecordingUrl: $callRecordingUrl, callDurationSeconds: $callDurationSeconds, status: $status, sentAt: $sentAt, deliveredAt: $deliveredAt, readAt: $readAt, agentId: $agentId, agentName: $agentName, wasAutomated: $wasAutomated, automationRuleId: $automationRuleId, customerReply: $customerReply, repliedAt: $repliedAt, adminNotes: $adminNotes, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class $CustomerCommunicationLogCopyWith<$Res>  {
  factory $CustomerCommunicationLogCopyWith(CustomerCommunicationLog value, $Res Function(CustomerCommunicationLog) _then) = _$CustomerCommunicationLogCopyWithImpl;
@useResult
$Res call({
 String id, String customerId, String bookingId, String channel, String direction, String type, String? message, String? attachmentUrl, String? callRecordingUrl, int? callDurationSeconds, String status, DateTime? sentAt, DateTime? deliveredAt, DateTime? readAt, String? agentId, String? agentName, bool wasAutomated, String? automationRuleId, String? customerReply, DateTime? repliedAt, String? adminNotes, DateTime createdAt
});




}
/// @nodoc
class _$CustomerCommunicationLogCopyWithImpl<$Res>
    implements $CustomerCommunicationLogCopyWith<$Res> {
  _$CustomerCommunicationLogCopyWithImpl(this._self, this._then);

  final CustomerCommunicationLog _self;
  final $Res Function(CustomerCommunicationLog) _then;

/// Create a copy of CustomerCommunicationLog
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? customerId = null,Object? bookingId = null,Object? channel = null,Object? direction = null,Object? type = null,Object? message = freezed,Object? attachmentUrl = freezed,Object? callRecordingUrl = freezed,Object? callDurationSeconds = freezed,Object? status = null,Object? sentAt = freezed,Object? deliveredAt = freezed,Object? readAt = freezed,Object? agentId = freezed,Object? agentName = freezed,Object? wasAutomated = null,Object? automationRuleId = freezed,Object? customerReply = freezed,Object? repliedAt = freezed,Object? adminNotes = freezed,Object? createdAt = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,direction: null == direction ? _self.direction : direction // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,message: freezed == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String?,attachmentUrl: freezed == attachmentUrl ? _self.attachmentUrl : attachmentUrl // ignore: cast_nullable_to_non_nullable
as String?,callRecordingUrl: freezed == callRecordingUrl ? _self.callRecordingUrl : callRecordingUrl // ignore: cast_nullable_to_non_nullable
as String?,callDurationSeconds: freezed == callDurationSeconds ? _self.callDurationSeconds : callDurationSeconds // ignore: cast_nullable_to_non_nullable
as int?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,sentAt: freezed == sentAt ? _self.sentAt : sentAt // ignore: cast_nullable_to_non_nullable
as DateTime?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,readAt: freezed == readAt ? _self.readAt : readAt // ignore: cast_nullable_to_non_nullable
as DateTime?,agentId: freezed == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String?,agentName: freezed == agentName ? _self.agentName : agentName // ignore: cast_nullable_to_non_nullable
as String?,wasAutomated: null == wasAutomated ? _self.wasAutomated : wasAutomated // ignore: cast_nullable_to_non_nullable
as bool,automationRuleId: freezed == automationRuleId ? _self.automationRuleId : automationRuleId // ignore: cast_nullable_to_non_nullable
as String?,customerReply: freezed == customerReply ? _self.customerReply : customerReply // ignore: cast_nullable_to_non_nullable
as String?,repliedAt: freezed == repliedAt ? _self.repliedAt : repliedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,adminNotes: freezed == adminNotes ? _self.adminNotes : adminNotes // ignore: cast_nullable_to_non_nullable
as String?,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [CustomerCommunicationLog].
extension CustomerCommunicationLogPatterns on CustomerCommunicationLog {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CustomerCommunicationLog value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CustomerCommunicationLog() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CustomerCommunicationLog value)  $default,){
final _that = this;
switch (_that) {
case _CustomerCommunicationLog():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CustomerCommunicationLog value)?  $default,){
final _that = this;
switch (_that) {
case _CustomerCommunicationLog() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String customerId,  String bookingId,  String channel,  String direction,  String type,  String? message,  String? attachmentUrl,  String? callRecordingUrl,  int? callDurationSeconds,  String status,  DateTime? sentAt,  DateTime? deliveredAt,  DateTime? readAt,  String? agentId,  String? agentName,  bool wasAutomated,  String? automationRuleId,  String? customerReply,  DateTime? repliedAt,  String? adminNotes,  DateTime createdAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CustomerCommunicationLog() when $default != null:
return $default(_that.id,_that.customerId,_that.bookingId,_that.channel,_that.direction,_that.type,_that.message,_that.attachmentUrl,_that.callRecordingUrl,_that.callDurationSeconds,_that.status,_that.sentAt,_that.deliveredAt,_that.readAt,_that.agentId,_that.agentName,_that.wasAutomated,_that.automationRuleId,_that.customerReply,_that.repliedAt,_that.adminNotes,_that.createdAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String customerId,  String bookingId,  String channel,  String direction,  String type,  String? message,  String? attachmentUrl,  String? callRecordingUrl,  int? callDurationSeconds,  String status,  DateTime? sentAt,  DateTime? deliveredAt,  DateTime? readAt,  String? agentId,  String? agentName,  bool wasAutomated,  String? automationRuleId,  String? customerReply,  DateTime? repliedAt,  String? adminNotes,  DateTime createdAt)  $default,) {final _that = this;
switch (_that) {
case _CustomerCommunicationLog():
return $default(_that.id,_that.customerId,_that.bookingId,_that.channel,_that.direction,_that.type,_that.message,_that.attachmentUrl,_that.callRecordingUrl,_that.callDurationSeconds,_that.status,_that.sentAt,_that.deliveredAt,_that.readAt,_that.agentId,_that.agentName,_that.wasAutomated,_that.automationRuleId,_that.customerReply,_that.repliedAt,_that.adminNotes,_that.createdAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String customerId,  String bookingId,  String channel,  String direction,  String type,  String? message,  String? attachmentUrl,  String? callRecordingUrl,  int? callDurationSeconds,  String status,  DateTime? sentAt,  DateTime? deliveredAt,  DateTime? readAt,  String? agentId,  String? agentName,  bool wasAutomated,  String? automationRuleId,  String? customerReply,  DateTime? repliedAt,  String? adminNotes,  DateTime createdAt)?  $default,) {final _that = this;
switch (_that) {
case _CustomerCommunicationLog() when $default != null:
return $default(_that.id,_that.customerId,_that.bookingId,_that.channel,_that.direction,_that.type,_that.message,_that.attachmentUrl,_that.callRecordingUrl,_that.callDurationSeconds,_that.status,_that.sentAt,_that.deliveredAt,_that.readAt,_that.agentId,_that.agentName,_that.wasAutomated,_that.automationRuleId,_that.customerReply,_that.repliedAt,_that.adminNotes,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CustomerCommunicationLog implements CustomerCommunicationLog {
  const _CustomerCommunicationLog({this.id = '', this.customerId = '', this.bookingId = '', this.channel = '', this.direction = '', this.type = '', this.message, this.attachmentUrl, this.callRecordingUrl, this.callDurationSeconds, this.status = '', this.sentAt, this.deliveredAt, this.readAt, this.agentId, this.agentName, this.wasAutomated = false, this.automationRuleId, this.customerReply, this.repliedAt, this.adminNotes, required this.createdAt});
  factory _CustomerCommunicationLog.fromJson(Map<String, dynamic> json) => _$CustomerCommunicationLogFromJson(json);

@override@JsonKey() final  String id;
@override@JsonKey() final  String customerId;
@override@JsonKey() final  String bookingId;
// Communication details
@override@JsonKey() final  String channel;
// whatsapp, sms, email, call, agent_visit
@override@JsonKey() final  String direction;
// outgoing, incoming
@override@JsonKey() final  String type;
// reminder, follow_up, payment_confirmation, enquiry
// Content
@override final  String? message;
@override final  String? attachmentUrl;
@override final  String? callRecordingUrl;
@override final  int? callDurationSeconds;
// Status
@override@JsonKey() final  String status;
// sent, delivered, read, failed
@override final  DateTime? sentAt;
@override final  DateTime? deliveredAt;
@override final  DateTime? readAt;
// Agent info (if agent initiated)
@override final  String? agentId;
@override final  String? agentName;
// AI/Automation info
@override@JsonKey() final  bool wasAutomated;
@override final  String? automationRuleId;
// Customer response
@override final  String? customerReply;
@override final  DateTime? repliedAt;
// Notes
@override final  String? adminNotes;
@override final  DateTime createdAt;

/// Create a copy of CustomerCommunicationLog
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CustomerCommunicationLogCopyWith<_CustomerCommunicationLog> get copyWith => __$CustomerCommunicationLogCopyWithImpl<_CustomerCommunicationLog>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CustomerCommunicationLogToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _CustomerCommunicationLog&&(identical(other.id, id) || other.id == id)&&(identical(other.customerId, customerId) || other.customerId == customerId)&&(identical(other.bookingId, bookingId) || other.bookingId == bookingId)&&(identical(other.channel, channel) || other.channel == channel)&&(identical(other.direction, direction) || other.direction == direction)&&(identical(other.type, type) || other.type == type)&&(identical(other.message, message) || other.message == message)&&(identical(other.attachmentUrl, attachmentUrl) || other.attachmentUrl == attachmentUrl)&&(identical(other.callRecordingUrl, callRecordingUrl) || other.callRecordingUrl == callRecordingUrl)&&(identical(other.callDurationSeconds, callDurationSeconds) || other.callDurationSeconds == callDurationSeconds)&&(identical(other.status, status) || other.status == status)&&(identical(other.sentAt, sentAt) || other.sentAt == sentAt)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.readAt, readAt) || other.readAt == readAt)&&(identical(other.agentId, agentId) || other.agentId == agentId)&&(identical(other.agentName, agentName) || other.agentName == agentName)&&(identical(other.wasAutomated, wasAutomated) || other.wasAutomated == wasAutomated)&&(identical(other.automationRuleId, automationRuleId) || other.automationRuleId == automationRuleId)&&(identical(other.customerReply, customerReply) || other.customerReply == customerReply)&&(identical(other.repliedAt, repliedAt) || other.repliedAt == repliedAt)&&(identical(other.adminNotes, adminNotes) || other.adminNotes == adminNotes)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hashAll([runtimeType,id,customerId,bookingId,channel,direction,type,message,attachmentUrl,callRecordingUrl,callDurationSeconds,status,sentAt,deliveredAt,readAt,agentId,agentName,wasAutomated,automationRuleId,customerReply,repliedAt,adminNotes,createdAt]);

@override
String toString() {
  return 'CustomerCommunicationLog(id: $id, customerId: $customerId, bookingId: $bookingId, channel: $channel, direction: $direction, type: $type, message: $message, attachmentUrl: $attachmentUrl, callRecordingUrl: $callRecordingUrl, callDurationSeconds: $callDurationSeconds, status: $status, sentAt: $sentAt, deliveredAt: $deliveredAt, readAt: $readAt, agentId: $agentId, agentName: $agentName, wasAutomated: $wasAutomated, automationRuleId: $automationRuleId, customerReply: $customerReply, repliedAt: $repliedAt, adminNotes: $adminNotes, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$CustomerCommunicationLogCopyWith<$Res> implements $CustomerCommunicationLogCopyWith<$Res> {
  factory _$CustomerCommunicationLogCopyWith(_CustomerCommunicationLog value, $Res Function(_CustomerCommunicationLog) _then) = __$CustomerCommunicationLogCopyWithImpl;
@override @useResult
$Res call({
 String id, String customerId, String bookingId, String channel, String direction, String type, String? message, String? attachmentUrl, String? callRecordingUrl, int? callDurationSeconds, String status, DateTime? sentAt, DateTime? deliveredAt, DateTime? readAt, String? agentId, String? agentName, bool wasAutomated, String? automationRuleId, String? customerReply, DateTime? repliedAt, String? adminNotes, DateTime createdAt
});




}
/// @nodoc
class __$CustomerCommunicationLogCopyWithImpl<$Res>
    implements _$CustomerCommunicationLogCopyWith<$Res> {
  __$CustomerCommunicationLogCopyWithImpl(this._self, this._then);

  final _CustomerCommunicationLog _self;
  final $Res Function(_CustomerCommunicationLog) _then;

/// Create a copy of CustomerCommunicationLog
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? customerId = null,Object? bookingId = null,Object? channel = null,Object? direction = null,Object? type = null,Object? message = freezed,Object? attachmentUrl = freezed,Object? callRecordingUrl = freezed,Object? callDurationSeconds = freezed,Object? status = null,Object? sentAt = freezed,Object? deliveredAt = freezed,Object? readAt = freezed,Object? agentId = freezed,Object? agentName = freezed,Object? wasAutomated = null,Object? automationRuleId = freezed,Object? customerReply = freezed,Object? repliedAt = freezed,Object? adminNotes = freezed,Object? createdAt = null,}) {
  return _then(_CustomerCommunicationLog(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,customerId: null == customerId ? _self.customerId : customerId // ignore: cast_nullable_to_non_nullable
as String,bookingId: null == bookingId ? _self.bookingId : bookingId // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,direction: null == direction ? _self.direction : direction // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,message: freezed == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String?,attachmentUrl: freezed == attachmentUrl ? _self.attachmentUrl : attachmentUrl // ignore: cast_nullable_to_non_nullable
as String?,callRecordingUrl: freezed == callRecordingUrl ? _self.callRecordingUrl : callRecordingUrl // ignore: cast_nullable_to_non_nullable
as String?,callDurationSeconds: freezed == callDurationSeconds ? _self.callDurationSeconds : callDurationSeconds // ignore: cast_nullable_to_non_nullable
as int?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,sentAt: freezed == sentAt ? _self.sentAt : sentAt // ignore: cast_nullable_to_non_nullable
as DateTime?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,readAt: freezed == readAt ? _self.readAt : readAt // ignore: cast_nullable_to_non_nullable
as DateTime?,agentId: freezed == agentId ? _self.agentId : agentId // ignore: cast_nullable_to_non_nullable
as String?,agentName: freezed == agentName ? _self.agentName : agentName // ignore: cast_nullable_to_non_nullable
as String?,wasAutomated: null == wasAutomated ? _self.wasAutomated : wasAutomated // ignore: cast_nullable_to_non_nullable
as bool,automationRuleId: freezed == automationRuleId ? _self.automationRuleId : automationRuleId // ignore: cast_nullable_to_non_nullable
as String?,customerReply: freezed == customerReply ? _self.customerReply : customerReply // ignore: cast_nullable_to_non_nullable
as String?,repliedAt: freezed == repliedAt ? _self.repliedAt : repliedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,adminNotes: freezed == adminNotes ? _self.adminNotes : adminNotes // ignore: cast_nullable_to_non_nullable
as String?,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}

// dart format on
