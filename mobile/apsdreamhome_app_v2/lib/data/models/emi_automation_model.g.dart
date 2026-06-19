// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'emi_automation_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$EMIAutomationConfigImpl _$$EMIAutomationConfigImplFromJson(
  Map<String, dynamic> json,
) => _$EMIAutomationConfigImpl(
  id: json['id'] as String,
  companyId: json['companyId'] as String,
  companyName: json['companyName'] as String,
  whatsappConfig: WhatsAppConfig.fromJson(
    json['whatsappConfig'] as Map<String, dynamic>,
  ),
  voiceCallConfig: VoiceCallConfig.fromJson(
    json['voiceCallConfig'] as Map<String, dynamic>,
  ),
  smsConfig: SMSConfig.fromJson(json['smsConfig'] as Map<String, dynamic>),
  emailConfig: EmailConfig.fromJson(
    json['emailConfig'] as Map<String, dynamic>,
  ),
  reminderRules:
      (json['reminderRules'] as List<dynamic>?)
          ?.map((e) => AutomationRule.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  escalationRules:
      (json['escalationRules'] as List<dynamic>?)
          ?.map((e) => AutomationRule.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  collectionRules:
      (json['collectionRules'] as List<dynamic>?)
          ?.map((e) => AutomationRule.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  fieldAgentConfig: FieldAgentConfig.fromJson(
    json['fieldAgentConfig'] as Map<String, dynamic>,
  ),
  aiConfig: AIConfig.fromJson(json['aiConfig'] as Map<String, dynamic>),
  isActive: json['isActive'] as bool,
  createdAt: DateTime.parse(json['createdAt'] as String),
  updatedAt: DateTime.parse(json['updatedAt'] as String),
);

Map<String, dynamic> _$$EMIAutomationConfigImplToJson(
  _$EMIAutomationConfigImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'companyId': instance.companyId,
  'companyName': instance.companyName,
  'whatsappConfig': instance.whatsappConfig,
  'voiceCallConfig': instance.voiceCallConfig,
  'smsConfig': instance.smsConfig,
  'emailConfig': instance.emailConfig,
  'reminderRules': instance.reminderRules,
  'escalationRules': instance.escalationRules,
  'collectionRules': instance.collectionRules,
  'fieldAgentConfig': instance.fieldAgentConfig,
  'aiConfig': instance.aiConfig,
  'isActive': instance.isActive,
  'createdAt': instance.createdAt.toIso8601String(),
  'updatedAt': instance.updatedAt.toIso8601String(),
};

_$WhatsAppConfigImpl _$$WhatsAppConfigImplFromJson(Map<String, dynamic> json) =>
    _$WhatsAppConfigImpl(
      isEnabled: json['isEnabled'] as bool,
      businessAccountId: json['businessAccountId'] as String?,
      phoneNumberId: json['phoneNumberId'] as String?,
      accessToken: json['accessToken'] as String?,
      apiVersion: json['apiVersion'] as String?,
      welcomeTemplateId: json['welcomeTemplateId'] as String?,
      reminderTemplateId: json['reminderTemplateId'] as String?,
      overdueTemplateId: json['overdueTemplateId'] as String?,
      paymentConfirmationTemplateId:
          json['paymentConfirmationTemplateId'] as String?,
      defaulterTemplateId: json['defaulterTemplateId'] as String?,
      sendReminders: json['sendReminders'] as bool? ?? true,
      sendOverdueAlerts: json['sendOverdueAlerts'] as bool? ?? true,
      sendVoiceNotes: json['sendVoiceNotes'] as bool? ?? false,
      businessHoursStart: json['businessHoursStart'] as String?,
      businessHoursEnd: json['businessHoursEnd'] as String?,
      sendOutsideBusinessHours:
          json['sendOutsideBusinessHours'] as bool? ?? false,
    );

Map<String, dynamic> _$$WhatsAppConfigImplToJson(
  _$WhatsAppConfigImpl instance,
) => <String, dynamic>{
  'isEnabled': instance.isEnabled,
  'businessAccountId': instance.businessAccountId,
  'phoneNumberId': instance.phoneNumberId,
  'accessToken': instance.accessToken,
  'apiVersion': instance.apiVersion,
  'welcomeTemplateId': instance.welcomeTemplateId,
  'reminderTemplateId': instance.reminderTemplateId,
  'overdueTemplateId': instance.overdueTemplateId,
  'paymentConfirmationTemplateId': instance.paymentConfirmationTemplateId,
  'defaulterTemplateId': instance.defaulterTemplateId,
  'sendReminders': instance.sendReminders,
  'sendOverdueAlerts': instance.sendOverdueAlerts,
  'sendVoiceNotes': instance.sendVoiceNotes,
  'businessHoursStart': instance.businessHoursStart,
  'businessHoursEnd': instance.businessHoursEnd,
  'sendOutsideBusinessHours': instance.sendOutsideBusinessHours,
};

_$VoiceCallConfigImpl _$$VoiceCallConfigImplFromJson(
  Map<String, dynamic> json,
) => _$VoiceCallConfigImpl(
  isEnabled: json['isEnabled'] as bool,
  provider: json['provider'] as String?,
  apiKey: json['apiKey'] as String?,
  apiSecret: json['apiSecret'] as String?,
  fromNumber: json['fromNumber'] as String?,
  useIVR: json['useIVR'] as bool? ?? false,
  ivrGreetingMessage: json['ivrGreetingMessage'] as String?,
  ivrMenuOptions: json['ivrMenuOptions'] as String?,
  useAIVoiceBot: json['useAIVoiceBot'] as bool? ?? false,
  aiVoiceLanguage: json['aiVoiceLanguage'] as String?,
  aiVoiceGender: json['aiVoiceGender'] as String?,
  maxRetryAttempts: (json['maxRetryAttempts'] as num?)?.toInt() ?? 3,
  retryIntervalMinutes: (json['retryIntervalMinutes'] as num?)?.toInt() ?? 30,
  preferredCallHours:
      (json['preferredCallHours'] as List<dynamic>?)
          ?.map((e) => (e as num).toInt())
          .toList() ??
      const [10, 14, 16],
  recordCalls: json['recordCalls'] as bool? ?? true,
  transcribeCalls: json['transcribeCalls'] as bool? ?? true,
);

Map<String, dynamic> _$$VoiceCallConfigImplToJson(
  _$VoiceCallConfigImpl instance,
) => <String, dynamic>{
  'isEnabled': instance.isEnabled,
  'provider': instance.provider,
  'apiKey': instance.apiKey,
  'apiSecret': instance.apiSecret,
  'fromNumber': instance.fromNumber,
  'useIVR': instance.useIVR,
  'ivrGreetingMessage': instance.ivrGreetingMessage,
  'ivrMenuOptions': instance.ivrMenuOptions,
  'useAIVoiceBot': instance.useAIVoiceBot,
  'aiVoiceLanguage': instance.aiVoiceLanguage,
  'aiVoiceGender': instance.aiVoiceGender,
  'maxRetryAttempts': instance.maxRetryAttempts,
  'retryIntervalMinutes': instance.retryIntervalMinutes,
  'preferredCallHours': instance.preferredCallHours,
  'recordCalls': instance.recordCalls,
  'transcribeCalls': instance.transcribeCalls,
};

_$SMSConfigImpl _$$SMSConfigImplFromJson(Map<String, dynamic> json) =>
    _$SMSConfigImpl(
      isEnabled: json['isEnabled'] as bool,
      provider: json['provider'] as String?,
      apiKey: json['apiKey'] as String?,
      senderId: json['senderId'] as String?,
      otpTemplateId: json['otpTemplateId'] as String?,
      reminderTemplateId: json['reminderTemplateId'] as String?,
      overdueTemplateId: json['overdueTemplateId'] as String?,
      paymentLinkTemplateId: json['paymentLinkTemplateId'] as String?,
      receiptTemplateId: json['receiptTemplateId'] as String?,
      useShortURL: json['useShortURL'] as bool? ?? true,
      trackClicks: json['trackClicks'] as bool? ?? true,
      blockedHours:
          (json['blockedHours'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
    );

Map<String, dynamic> _$$SMSConfigImplToJson(_$SMSConfigImpl instance) =>
    <String, dynamic>{
      'isEnabled': instance.isEnabled,
      'provider': instance.provider,
      'apiKey': instance.apiKey,
      'senderId': instance.senderId,
      'otpTemplateId': instance.otpTemplateId,
      'reminderTemplateId': instance.reminderTemplateId,
      'overdueTemplateId': instance.overdueTemplateId,
      'paymentLinkTemplateId': instance.paymentLinkTemplateId,
      'receiptTemplateId': instance.receiptTemplateId,
      'useShortURL': instance.useShortURL,
      'trackClicks': instance.trackClicks,
      'blockedHours': instance.blockedHours,
    };

_$EmailConfigImpl _$$EmailConfigImplFromJson(Map<String, dynamic> json) =>
    _$EmailConfigImpl(
      isEnabled: json['isEnabled'] as bool,
      provider: json['provider'] as String?,
      apiKey: json['apiKey'] as String?,
      fromEmail: json['fromEmail'] as String?,
      fromName: json['fromName'] as String?,
      replyToEmail: json['replyToEmail'] as String?,
      welcomeEmailTemplateId: json['welcomeEmailTemplateId'] as String?,
      reminderEmailTemplateId: json['reminderEmailTemplateId'] as String?,
      invoiceEmailTemplateId: json['invoiceEmailTemplateId'] as String?,
      receiptEmailTemplateId: json['receiptEmailTemplateId'] as String?,
      newsletterTemplateId: json['newsletterTemplateId'] as String?,
      sendHTML: json['sendHTML'] as bool? ?? true,
      trackOpens: json['trackOpens'] as bool? ?? true,
      trackClicks: json['trackClicks'] as bool? ?? true,
      bccEmails:
          (json['bccEmails'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
    );

Map<String, dynamic> _$$EmailConfigImplToJson(_$EmailConfigImpl instance) =>
    <String, dynamic>{
      'isEnabled': instance.isEnabled,
      'provider': instance.provider,
      'apiKey': instance.apiKey,
      'fromEmail': instance.fromEmail,
      'fromName': instance.fromName,
      'replyToEmail': instance.replyToEmail,
      'welcomeEmailTemplateId': instance.welcomeEmailTemplateId,
      'reminderEmailTemplateId': instance.reminderEmailTemplateId,
      'invoiceEmailTemplateId': instance.invoiceEmailTemplateId,
      'receiptEmailTemplateId': instance.receiptEmailTemplateId,
      'newsletterTemplateId': instance.newsletterTemplateId,
      'sendHTML': instance.sendHTML,
      'trackOpens': instance.trackOpens,
      'trackClicks': instance.trackClicks,
      'bccEmails': instance.bccEmails,
    };

_$FieldAgentConfigImpl _$$FieldAgentConfigImplFromJson(
  Map<String, dynamic> json,
) => _$FieldAgentConfigImpl(
  assignmentMethod: json['assignmentMethod'] as String? ?? 'round_robin',
  maxLeadsPerAgent: (json['maxLeadsPerAgent'] as num?)?.toInt() ?? 20,
  maxDailyVisits: (json['maxDailyVisits'] as num?)?.toInt() ?? 50,
  trackLocation: json['trackLocation'] as bool? ?? true,
  locationUpdateIntervalMinutes:
      (json['locationUpdateIntervalMinutes'] as num?)?.toInt() ?? 5,
  geoFencingEnabled: json['geoFencingEnabled'] as bool? ?? true,
  geoFenceRadiusMeters: (json['geoFenceRadiusMeters'] as num?)?.toInt() ?? 500,
  collectionCommissionPercent:
      (json['collectionCommissionPercent'] as num?)?.toDouble() ?? 0.5,
  perCollectionFixedIncentive:
      (json['perCollectionFixedIncentive'] as num?)?.toDouble() ?? 50,
  targetAchievementBonus:
      (json['targetAchievementBonus'] as num?)?.toDouble() ?? 500,
  offlineModeEnabled: json['offlineModeEnabled'] as bool? ?? true,
  autoSyncEnabled: json['autoSyncEnabled'] as bool? ?? true,
  syncIntervalMinutes: (json['syncIntervalMinutes'] as num?)?.toInt() ?? 15,
  notifyOnNewAssignment: json['notifyOnNewAssignment'] as bool? ?? true,
  notifyOnDueListReady: json['notifyOnDueListReady'] as bool? ?? true,
  notifyOnCollectionConfirmation:
      json['notifyOnCollectionConfirmation'] as bool? ?? true,
);

Map<String, dynamic> _$$FieldAgentConfigImplToJson(
  _$FieldAgentConfigImpl instance,
) => <String, dynamic>{
  'assignmentMethod': instance.assignmentMethod,
  'maxLeadsPerAgent': instance.maxLeadsPerAgent,
  'maxDailyVisits': instance.maxDailyVisits,
  'trackLocation': instance.trackLocation,
  'locationUpdateIntervalMinutes': instance.locationUpdateIntervalMinutes,
  'geoFencingEnabled': instance.geoFencingEnabled,
  'geoFenceRadiusMeters': instance.geoFenceRadiusMeters,
  'collectionCommissionPercent': instance.collectionCommissionPercent,
  'perCollectionFixedIncentive': instance.perCollectionFixedIncentive,
  'targetAchievementBonus': instance.targetAchievementBonus,
  'offlineModeEnabled': instance.offlineModeEnabled,
  'autoSyncEnabled': instance.autoSyncEnabled,
  'syncIntervalMinutes': instance.syncIntervalMinutes,
  'notifyOnNewAssignment': instance.notifyOnNewAssignment,
  'notifyOnDueListReady': instance.notifyOnDueListReady,
  'notifyOnCollectionConfirmation': instance.notifyOnCollectionConfirmation,
};

_$AIConfigImpl _$$AIConfigImplFromJson(Map<String, dynamic> json) =>
    _$AIConfigImpl(
      enableLeadScoring: json['enableLeadScoring'] as bool? ?? true,
      autoAssignLeads: json['autoAssignLeads'] as bool? ?? true,
      enableAIVoiceCalls: json['enableAIVoiceCalls'] as bool? ?? true,
      enableAIWhatsApp: json['enableAIWhatsApp'] as bool? ?? true,
      enableAIPersonalization: json['enableAIPersonalization'] as bool? ?? true,
      predictDefaultRisk: json['predictDefaultRisk'] as bool? ?? true,
      predictBestCollectionTime:
          json['predictBestCollectionTime'] as bool? ?? true,
      predictCustomerResponse: json['predictCustomerResponse'] as bool? ?? true,
      enableOCR: json['enableOCR'] as bool? ?? true,
      enableAutoReceiptGeneration:
          json['enableAutoReceiptGeneration'] as bool? ?? true,
      enableFieldAgentAIAssistant:
          json['enableFieldAgentAIAssistant'] as bool? ?? true,
      enableCustomerAIChatbot: json['enableCustomerAIChatbot'] as bool? ?? true,
    );

Map<String, dynamic> _$$AIConfigImplToJson(_$AIConfigImpl instance) =>
    <String, dynamic>{
      'enableLeadScoring': instance.enableLeadScoring,
      'autoAssignLeads': instance.autoAssignLeads,
      'enableAIVoiceCalls': instance.enableAIVoiceCalls,
      'enableAIWhatsApp': instance.enableAIWhatsApp,
      'enableAIPersonalization': instance.enableAIPersonalization,
      'predictDefaultRisk': instance.predictDefaultRisk,
      'predictBestCollectionTime': instance.predictBestCollectionTime,
      'predictCustomerResponse': instance.predictCustomerResponse,
      'enableOCR': instance.enableOCR,
      'enableAutoReceiptGeneration': instance.enableAutoReceiptGeneration,
      'enableFieldAgentAIAssistant': instance.enableFieldAgentAIAssistant,
      'enableCustomerAIChatbot': instance.enableCustomerAIChatbot,
    };

_$AutomationRuleImpl _$$AutomationRuleImplFromJson(Map<String, dynamic> json) =>
    _$AutomationRuleImpl(
      id: json['id'] as String,
      name: json['name'] as String,
      type: json['type'] as String,
      trigger: json['trigger'] as String,
      triggerValue: (json['triggerValue'] as num).toInt(),
      actions:
          (json['actions'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
      scheduleTime: json['scheduleTime'] as String,
      scheduleDays: json['scheduleDays'] as String?,
      priority: (json['priority'] as num?)?.toInt() ?? 1,
      conditionAmount: json['conditionAmount'] as String?,
      conditionStatus: json['conditionStatus'] as String?,
      whatsappTemplate: json['whatsappTemplate'] as String?,
      smsTemplate: json['smsTemplate'] as String?,
      emailTemplate: json['emailTemplate'] as String?,
      voiceMessage: json['voiceMessage'] as String?,
      isActive: json['isActive'] as bool? ?? true,
      createdAt: DateTime.parse(json['createdAt'] as String),
    );

Map<String, dynamic> _$$AutomationRuleImplToJson(
  _$AutomationRuleImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'type': instance.type,
  'trigger': instance.trigger,
  'triggerValue': instance.triggerValue,
  'actions': instance.actions,
  'scheduleTime': instance.scheduleTime,
  'scheduleDays': instance.scheduleDays,
  'priority': instance.priority,
  'conditionAmount': instance.conditionAmount,
  'conditionStatus': instance.conditionStatus,
  'whatsappTemplate': instance.whatsappTemplate,
  'smsTemplate': instance.smsTemplate,
  'emailTemplate': instance.emailTemplate,
  'voiceMessage': instance.voiceMessage,
  'isActive': instance.isActive,
  'createdAt': instance.createdAt.toIso8601String(),
};

_$AutomationExecutionImpl _$$AutomationExecutionImplFromJson(
  Map<String, dynamic> json,
) => _$AutomationExecutionImpl(
  id: json['id'] as String,
  ruleId: json['ruleId'] as String,
  ruleName: json['ruleName'] as String,
  customerId: json['customerId'] as String,
  bookingId: json['bookingId'] as String,
  emiId: json['emiId'] as String,
  channel: json['channel'] as String,
  action: json['action'] as String,
  status: json['status'] as String,
  messageContent: json['messageContent'] as String?,
  templateUsed: json['templateUsed'] as String?,
  metadata: json['metadata'] as Map<String, dynamic>?,
  scheduledAt: DateTime.parse(json['scheduledAt'] as String),
  executedAt: json['executedAt'] == null
      ? null
      : DateTime.parse(json['executedAt'] as String),
  deliveredAt: json['deliveredAt'] == null
      ? null
      : DateTime.parse(json['deliveredAt'] as String),
  customerResponse: json['customerResponse'] as String?,
  responseAt: json['responseAt'] == null
      ? null
      : DateTime.parse(json['responseAt'] as String),
  responseType: json['responseType'] as String?,
  errorMessage: json['errorMessage'] as String?,
  retryCount: (json['retryCount'] as num?)?.toInt(),
  createdAt: DateTime.parse(json['createdAt'] as String),
);

Map<String, dynamic> _$$AutomationExecutionImplToJson(
  _$AutomationExecutionImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'ruleId': instance.ruleId,
  'ruleName': instance.ruleName,
  'customerId': instance.customerId,
  'bookingId': instance.bookingId,
  'emiId': instance.emiId,
  'channel': instance.channel,
  'action': instance.action,
  'status': instance.status,
  'messageContent': instance.messageContent,
  'templateUsed': instance.templateUsed,
  'metadata': instance.metadata,
  'scheduledAt': instance.scheduledAt.toIso8601String(),
  'executedAt': instance.executedAt?.toIso8601String(),
  'deliveredAt': instance.deliveredAt?.toIso8601String(),
  'customerResponse': instance.customerResponse,
  'responseAt': instance.responseAt?.toIso8601String(),
  'responseType': instance.responseType,
  'errorMessage': instance.errorMessage,
  'retryCount': instance.retryCount,
  'createdAt': instance.createdAt.toIso8601String(),
};

_$CustomerCommunicationLogImpl _$$CustomerCommunicationLogImplFromJson(
  Map<String, dynamic> json,
) => _$CustomerCommunicationLogImpl(
  id: json['id'] as String,
  customerId: json['customerId'] as String,
  bookingId: json['bookingId'] as String,
  channel: json['channel'] as String,
  direction: json['direction'] as String,
  type: json['type'] as String,
  message: json['message'] as String?,
  attachmentUrl: json['attachmentUrl'] as String?,
  callRecordingUrl: json['callRecordingUrl'] as String?,
  callDurationSeconds: (json['callDurationSeconds'] as num?)?.toInt(),
  status: json['status'] as String,
  sentAt: json['sentAt'] == null
      ? null
      : DateTime.parse(json['sentAt'] as String),
  deliveredAt: json['deliveredAt'] == null
      ? null
      : DateTime.parse(json['deliveredAt'] as String),
  readAt: json['readAt'] == null
      ? null
      : DateTime.parse(json['readAt'] as String),
  agentId: json['agentId'] as String?,
  agentName: json['agentName'] as String?,
  wasAutomated: json['wasAutomated'] as bool? ?? false,
  automationRuleId: json['automationRuleId'] as String?,
  customerReply: json['customerReply'] as String?,
  repliedAt: json['repliedAt'] == null
      ? null
      : DateTime.parse(json['repliedAt'] as String),
  adminNotes: json['adminNotes'] as String?,
  createdAt: DateTime.parse(json['createdAt'] as String),
);

Map<String, dynamic> _$$CustomerCommunicationLogImplToJson(
  _$CustomerCommunicationLogImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'customerId': instance.customerId,
  'bookingId': instance.bookingId,
  'channel': instance.channel,
  'direction': instance.direction,
  'type': instance.type,
  'message': instance.message,
  'attachmentUrl': instance.attachmentUrl,
  'callRecordingUrl': instance.callRecordingUrl,
  'callDurationSeconds': instance.callDurationSeconds,
  'status': instance.status,
  'sentAt': instance.sentAt?.toIso8601String(),
  'deliveredAt': instance.deliveredAt?.toIso8601String(),
  'readAt': instance.readAt?.toIso8601String(),
  'agentId': instance.agentId,
  'agentName': instance.agentName,
  'wasAutomated': instance.wasAutomated,
  'automationRuleId': instance.automationRuleId,
  'customerReply': instance.customerReply,
  'repliedAt': instance.repliedAt?.toIso8601String(),
  'adminNotes': instance.adminNotes,
  'createdAt': instance.createdAt.toIso8601String(),
};
