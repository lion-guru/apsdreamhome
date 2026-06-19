// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'property_listing_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$PropertyListingImpl _$$PropertyListingImplFromJson(
  Map<String, dynamic> json,
) => _$PropertyListingImpl(
  id: json['id'] as String? ?? '',
  title: json['title'] as String? ?? '',
  description: json['description'] as String? ?? '',
  propertyType: $enumDecode(_$PropertyTypeEnumMap, json['propertyType']),
  purpose: $enumDecode(_$ListingPurposeEnumMap, json['purpose']),
  ownerId: json['ownerId'] as String? ?? '',
  ownerName: json['ownerName'] as String? ?? '',
  ownerPhone: json['ownerPhone'] as String? ?? '',
  ownerEmail: json['ownerEmail'] as String? ?? '',
  ownerType: $enumDecode(_$OwnerTypeEnumMap, json['ownerType']),
  state: json['state'] as String? ?? '',
  district: json['district'] as String? ?? '',
  city: json['city'] as String? ?? '',
  locality: json['locality'] as String? ?? '',
  address: json['address'] as String? ?? '',
  location: GeoLocation.fromJson(json['location'] as Map<String, dynamic>),
  landmark: json['landmark'] as String? ?? '',
  areaSqft: (json['areaSqft'] as num?)?.toDouble() ?? 0.0,
  areaUnit: json['areaUnit'] as String?,
  expectedPrice: (json['expectedPrice'] as num?)?.toDouble() ?? 0.0,
  negotiable: $enumDecodeNullable(_$PriceNegotiableEnumMap, json['negotiable']),
  priceType: json['priceType'] as String? ?? 'Fixed',
  images:
      (json['images'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const [],
  videos:
      (json['videos'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const [],
  documents:
      (json['documents'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const [],
  features: json['features'] as Map<String, dynamic>?,
  status: $enumDecode(_$ListingStatusEnumMap, json['status']),
  verifiedBy: json['verifiedBy'] as String?,
  verifiedAt: json['verifiedAt'] == null
      ? null
      : DateTime.parse(json['verifiedAt'] as String),
  rejectionReason: json['rejectionReason'] as String?,
  verificationFee: (json['verificationFee'] as num?)?.toInt(),
  viewCount: (json['viewCount'] as num?)?.toInt() ?? 0,
  inquiryCount: (json['inquiryCount'] as num?)?.toInt() ?? 0,
  callCount: (json['callCount'] as num?)?.toInt() ?? 0,
  whatsappCount: (json['whatsappCount'] as num?)?.toInt() ?? 0,
  lastInquiryAt: json['lastInquiryAt'] == null
      ? null
      : DateTime.parse(json['lastInquiryAt'] as String),
  listingPlan: $enumDecodeNullable(_$ListingPlanEnumMap, json['listingPlan']),
  planExpiryDate: json['planExpiryDate'] == null
      ? null
      : DateTime.parse(json['planExpiryDate'] as String),
  isFeatured: json['isFeatured'] as bool? ?? false,
  isPremium: json['isPremium'] as bool? ?? false,
  isSpotlight: json['isSpotlight'] as bool? ?? false,
  interestedBuyers:
      (json['interestedBuyers'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const [],
  inquiries:
      (json['inquiries'] as List<dynamic>?)
          ?.map((e) => PropertyInquiry.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const [],
  adminNotes: json['adminNotes'] as String?,
  tags:
      (json['tags'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const [],
  createdAt: DateTime.parse(json['createdAt'] as String),
  updatedAt: DateTime.parse(json['updatedAt'] as String),
);

Map<String, dynamic> _$$PropertyListingImplToJson(
  _$PropertyListingImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'title': instance.title,
  'description': instance.description,
  'propertyType': _$PropertyTypeEnumMap[instance.propertyType]!,
  'purpose': _$ListingPurposeEnumMap[instance.purpose]!,
  'ownerId': instance.ownerId,
  'ownerName': instance.ownerName,
  'ownerPhone': instance.ownerPhone,
  'ownerEmail': instance.ownerEmail,
  'ownerType': _$OwnerTypeEnumMap[instance.ownerType]!,
  'state': instance.state,
  'district': instance.district,
  'city': instance.city,
  'locality': instance.locality,
  'address': instance.address,
  'location': instance.location,
  'landmark': instance.landmark,
  'areaSqft': instance.areaSqft,
  'areaUnit': instance.areaUnit,
  'expectedPrice': instance.expectedPrice,
  'negotiable': _$PriceNegotiableEnumMap[instance.negotiable],
  'priceType': instance.priceType,
  'images': instance.images,
  'videos': instance.videos,
  'documents': instance.documents,
  'features': instance.features,
  'status': _$ListingStatusEnumMap[instance.status]!,
  'verifiedBy': instance.verifiedBy,
  'verifiedAt': instance.verifiedAt?.toIso8601String(),
  'rejectionReason': instance.rejectionReason,
  'verificationFee': instance.verificationFee,
  'viewCount': instance.viewCount,
  'inquiryCount': instance.inquiryCount,
  'callCount': instance.callCount,
  'whatsappCount': instance.whatsappCount,
  'lastInquiryAt': instance.lastInquiryAt?.toIso8601String(),
  'listingPlan': _$ListingPlanEnumMap[instance.listingPlan],
  'planExpiryDate': instance.planExpiryDate?.toIso8601String(),
  'isFeatured': instance.isFeatured,
  'isPremium': instance.isPremium,
  'isSpotlight': instance.isSpotlight,
  'interestedBuyers': instance.interestedBuyers,
  'inquiries': instance.inquiries,
  'adminNotes': instance.adminNotes,
  'tags': instance.tags,
  'createdAt': instance.createdAt.toIso8601String(),
  'updatedAt': instance.updatedAt.toIso8601String(),
};

const _$PropertyTypeEnumMap = {
  PropertyType.plot: 'plot',
  PropertyType.house: 'house',
  PropertyType.flat: 'flat',
  PropertyType.shop: 'shop',
  PropertyType.farmhouse: 'farmhouse',
  PropertyType.land: 'land',
  PropertyType.commercial: 'commercial',
  PropertyType.industrial: 'industrial',
};

const _$ListingPurposeEnumMap = {
  ListingPurpose.sell: 'sell',
  ListingPurpose.rent: 'rent',
  ListingPurpose.lease: 'lease',
};

const _$OwnerTypeEnumMap = {
  OwnerType.customer: 'customer',
  OwnerType.associate: 'associate',
  OwnerType.agent: 'agent',
  OwnerType.employee: 'employee',
  OwnerType.admin: 'admin',
};

const _$PriceNegotiableEnumMap = {
  PriceNegotiable.fixed: 'fixed',
  PriceNegotiable.negotiable: 'negotiable',
  PriceNegotiable.bestOffer: 'bestOffer',
};

const _$ListingStatusEnumMap = {
  ListingStatus.pending: 'pending',
  ListingStatus.underReview: 'underReview',
  ListingStatus.verified: 'verified',
  ListingStatus.active: 'active',
  ListingStatus.sold: 'sold',
  ListingStatus.rejected: 'rejected',
  ListingStatus.expired: 'expired',
};

const _$ListingPlanEnumMap = {
  ListingPlan.free: 'free',
  ListingPlan.featured: 'featured',
  ListingPlan.premium: 'premium',
  ListingPlan.spotlight: 'spotlight',
};

_$PropertyInquiryImpl _$$PropertyInquiryImplFromJson(
  Map<String, dynamic> json,
) => _$PropertyInquiryImpl(
  id: json['id'] as String? ?? '',
  buyerId: json['buyerId'] as String? ?? '',
  buyerName: json['buyerName'] as String? ?? '',
  buyerPhone: json['buyerPhone'] as String? ?? '',
  buyerEmail: json['buyerEmail'] as String?,
  type: $enumDecode(_$InquiryTypeEnumMap, json['type']),
  message: json['message'] as String?,
  scheduledVisitDate: json['scheduledVisitDate'] == null
      ? null
      : DateTime.parse(json['scheduledVisitDate'] as String),
  status: $enumDecode(_$InquiryStatusEnumMap, json['status']),
  createdAt: json['createdAt'] == null
      ? null
      : DateTime.parse(json['createdAt'] as String),
  respondedAt: json['respondedAt'] == null
      ? null
      : DateTime.parse(json['respondedAt'] as String),
  responseNotes: json['responseNotes'] as String?,
);

Map<String, dynamic> _$$PropertyInquiryImplToJson(
  _$PropertyInquiryImpl instance,
) => <String, dynamic>{
  'id': instance.id,
  'buyerId': instance.buyerId,
  'buyerName': instance.buyerName,
  'buyerPhone': instance.buyerPhone,
  'buyerEmail': instance.buyerEmail,
  'type': _$InquiryTypeEnumMap[instance.type]!,
  'message': instance.message,
  'scheduledVisitDate': instance.scheduledVisitDate?.toIso8601String(),
  'status': _$InquiryStatusEnumMap[instance.status]!,
  'createdAt': instance.createdAt?.toIso8601String(),
  'respondedAt': instance.respondedAt?.toIso8601String(),
  'responseNotes': instance.responseNotes,
};

const _$InquiryTypeEnumMap = {
  InquiryType.call: 'call',
  InquiryType.whatsapp: 'whatsapp',
  InquiryType.email: 'email',
  InquiryType.visit: 'visit',
  InquiryType.message: 'message',
};

const _$InquiryStatusEnumMap = {
  InquiryStatus.new_inquiry: 'new_inquiry',
  InquiryStatus.contacted: 'contacted',
  InquiryStatus.inDiscussion: 'inDiscussion',
  InquiryStatus.negotiating: 'negotiating',
  InquiryStatus.closed: 'closed',
  InquiryStatus.converted: 'converted',
};
