// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'user_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

User _$UserFromJson(Map<String, dynamic> json) => User(
  userId: json['userId'] as String,
  name: json['name'] as String,
  email: json['email'] as String,
  phone: json['phone'] as String?,
  rank: json['rank'] as String,
  role: json['role'] as String?,
  target: (json['target'] as num).toDouble(),
  avatar: json['avatar'] as String?,
  createdAt: json['createdAt'] as String,
  updatedAt: json['updatedAt'] as String,
);

Map<String, dynamic> _$UserToJson(User instance) => <String, dynamic>{
  'userId': instance.userId,
  'name': instance.name,
  'email': instance.email,
  'phone': instance.phone,
  'rank': instance.rank,
  'role': instance.role,
  'target': instance.target,
  'avatar': instance.avatar,
  'createdAt': instance.createdAt,
  'updatedAt': instance.updatedAt,
};
