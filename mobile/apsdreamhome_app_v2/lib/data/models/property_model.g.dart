// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'property_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Property _$PropertyFromJson(Map<String, dynamic> json) => Property(
  propertyId: json['propertyId'] as String,
  title: json['title'] as String,
  description: json['description'] as String,
  type: json['type'] as String,
  price: (json['price'] as num).toDouble(),
  size: (json['size'] as num?)?.toDouble(),
  location: json['location'] as String,
  status: json['status'] as String,
  imageUrl: json['imageUrl'] as String?,
  createdAt: json['createdAt'] as String,
  updatedAt: json['updatedAt'] as String,
  lastSyncedAt: json['lastSyncedAt'] as String?,
);

Map<String, dynamic> _$PropertyToJson(Property instance) => <String, dynamic>{
  'propertyId': instance.propertyId,
  'title': instance.title,
  'description': instance.description,
  'type': instance.type,
  'price': instance.price,
  'size': instance.size,
  'location': instance.location,
  'status': instance.status,
  'imageUrl': instance.imageUrl,
  'createdAt': instance.createdAt,
  'updatedAt': instance.updatedAt,
  'lastSyncedAt': instance.lastSyncedAt,
};
