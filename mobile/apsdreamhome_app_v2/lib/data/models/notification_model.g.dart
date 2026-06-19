// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'notification_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$NotificationModelImpl _$$NotificationModelImplFromJson(
  Map<String, dynamic> json,
) => _$NotificationModelImpl(
  id: json['id'] as String? ?? '',
  userId: json['userId'] as String? ?? '',
  type: json['type'] as String? ?? '',
  title: json['title'] as String? ?? '',
  body: json['body'] as String? ?? '',
  imageUrl: json['imageUrl'] as String?,
  actionType: json['actionType'] as String?,
  actionData: json['actionData'] as String?,
  actionUrl: json['actionUrl'] as String?,
  relatedId: json['relatedId'] as String?,
  relatedType: json['relatedType'] as String?,
  isRead: json['isRead'] as bool?,
  readAt: json['readAt'] == null
      ? null
      : DateTime.parse(json['readAt'] as String),
  createdAt: json['createdAt'] == null
      ? null
      : DateTime.parse(json['createdAt'] as String),
  expiresAt: json['expiresAt'] == null
      ? null
      : DateTime.parse(json['expiresAt'] as String),
  pushDelivered: json['pushDelivered'] as bool?,
  pushDeliveredAt: json['pushDeliveredAt'] == null
      ? null
      : DateTime.parse(json['pushDeliveredAt'] as String),
  fcmMessageId: json['fcmMessageId'] as String?,
);

Map<String, dynamic> _$$NotificationModelImplToJson(
  _$NotificationModelImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'userId': instance.userId,
  'type': instance.type,
  'title': instance.title,
  'body': instance.body,
  'imageUrl': instance.imageUrl,
  'actionType': instance.actionType,
  'actionData': instance.actionData,
  'actionUrl': instance.actionUrl,
  'relatedId': instance.relatedId,
  'relatedType': instance.relatedType,
  'isRead': instance.isRead,
  'readAt': instance.readAt?.toIso8601String(),
  'createdAt': instance.createdAt?.toIso8601String(),
  'expiresAt': instance.expiresAt?.toIso8601String(),
  'pushDelivered': instance.pushDelivered,
  'pushDeliveredAt': instance.pushDeliveredAt?.toIso8601String(),
  'fcmMessageId': instance.fcmMessageId,
};
