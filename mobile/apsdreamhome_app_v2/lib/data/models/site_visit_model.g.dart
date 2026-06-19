// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'site_visit_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$SiteVisitModelImpl _$$SiteVisitModelImplFromJson(
  Map<String, dynamic> json,
) => _$SiteVisitModelImpl(
  id: json['id'] as String,
  agentId: json['agentId'] as String,
  agentName: json['agentName'] as String,
  customerId: json['customerId'] as String?,
  customerName: json['customerName'] as String?,
  customerPhone: json['customerPhone'] as String?,
  colonyId: json['colonyId'] as String,
  colonyName: json['colonyName'] as String,
  plotIdsShown: (json['plotIdsShown'] as List<dynamic>?)
      ?.map((e) => e as String)
      .toList(),
  plotNumbersShown: (json['plotNumbersShown'] as List<dynamic>?)
      ?.map((e) => e as String)
      .toList(),
  latitude: (json['latitude'] as num).toDouble(),
  longitude: (json['longitude'] as num).toDouble(),
  address: json['address'] as String?,
  accuracy: (json['accuracy'] as num?)?.toDouble(),
  visitStartTime: DateTime.parse(json['visitStartTime'] as String),
  visitEndTime: json['visitEndTime'] == null
      ? null
      : DateTime.parse(json['visitEndTime'] as String),
  duration: json['duration'] == null
      ? null
      : Duration(microseconds: (json['duration'] as num).toInt()),
  purpose: json['purpose'] as String?,
  customerFeedback: json['customerFeedback'] as String?,
  agentNotes: json['agentNotes'] as String?,
  outcome: json['outcome'] as String?,
  followUpRequired: json['followUpRequired'] as bool?,
  followUpDate: json['followUpDate'] == null
      ? null
      : DateTime.parse(json['followUpDate'] as String),
  followUpType: json['followUpType'] as String?,
  photos: (json['photos'] as List<dynamic>?)?.map((e) => e as String).toList(),
  videos: (json['videos'] as List<dynamic>?)?.map((e) => e as String).toList(),
  voiceNoteUrl: json['voiceNoteUrl'] as String?,
  isOfflineCreated: json['isOfflineCreated'] as bool?,
  syncedAt: json['syncedAt'] == null
      ? null
      : DateTime.parse(json['syncedAt'] as String),
  createdAt: json['createdAt'] == null
      ? null
      : DateTime.parse(json['createdAt'] as String),
  updatedAt: json['updatedAt'] == null
      ? null
      : DateTime.parse(json['updatedAt'] as String),
);

Map<String, dynamic> _$$SiteVisitModelImplToJson(
  _$SiteVisitModelImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'agentId': instance.agentId,
  'agentName': instance.agentName,
  'customerId': instance.customerId,
  'customerName': instance.customerName,
  'customerPhone': instance.customerPhone,
  'colonyId': instance.colonyId,
  'colonyName': instance.colonyName,
  'plotIdsShown': instance.plotIdsShown,
  'plotNumbersShown': instance.plotNumbersShown,
  'latitude': instance.latitude,
  'longitude': instance.longitude,
  'address': instance.address,
  'accuracy': instance.accuracy,
  'visitStartTime': instance.visitStartTime.toIso8601String(),
  'visitEndTime': instance.visitEndTime?.toIso8601String(),
  'duration': instance.duration?.inMicroseconds,
  'purpose': instance.purpose,
  'customerFeedback': instance.customerFeedback,
  'agentNotes': instance.agentNotes,
  'outcome': instance.outcome,
  'followUpRequired': instance.followUpRequired,
  'followUpDate': instance.followUpDate?.toIso8601String(),
  'followUpType': instance.followUpType,
  'photos': instance.photos,
  'videos': instance.videos,
  'voiceNoteUrl': instance.voiceNoteUrl,
  'isOfflineCreated': instance.isOfflineCreated,
  'syncedAt': instance.syncedAt?.toIso8601String(),
  'createdAt': instance.createdAt?.toIso8601String(),
  'updatedAt': instance.updatedAt?.toIso8601String(),
};

_$LiveLocationModelImpl _$$LiveLocationModelImplFromJson(
  Map<String, dynamic> json,
) => _$LiveLocationModelImpl(
  userId: json['userId'] as String,
  userName: json['userName'] as String,
  userType: json['userType'] as String,
  latitude: (json['latitude'] as num).toDouble(),
  longitude: (json['longitude'] as num).toDouble(),
  timestamp: DateTime.parse(json['timestamp'] as String),
  speed: (json['speed'] as num?)?.toDouble(),
  heading: (json['heading'] as num?)?.toDouble(),
  accuracy: (json['accuracy'] as num?)?.toDouble(),
  isSharingEnabled: json['isSharingEnabled'] as bool?,
  sharingStartedAt: json['sharingStartedAt'] == null
      ? null
      : DateTime.parse(json['sharingStartedAt'] as String),
  sharingExpiresAt: json['sharingExpiresAt'] == null
      ? null
      : DateTime.parse(json['sharingExpiresAt'] as String),
  sharedWith: json['sharedWith'] as String?,
);

Map<String, dynamic> _$$LiveLocationModelImplToJson(
  _$LiveLocationModelImpl instance,
) => <String, dynamic>{
  'userId': instance.userId,
  'userName': instance.userName,
  'userType': instance.userType,
  'latitude': instance.latitude,
  'longitude': instance.longitude,
  'timestamp': instance.timestamp.toIso8601String(),
  'speed': instance.speed,
  'heading': instance.heading,
  'accuracy': instance.accuracy,
  'isSharingEnabled': instance.isSharingEnabled,
  'sharingStartedAt': instance.sharingStartedAt?.toIso8601String(),
  'sharingExpiresAt': instance.sharingExpiresAt?.toIso8601String(),
  'sharedWith': instance.sharedWith,
};
