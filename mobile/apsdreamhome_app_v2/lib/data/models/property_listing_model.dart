import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'property_listing_model.freezed.dart';
part 'property_listing_model.g.dart';

/// GeoPoint JSON Converter for Firestore
class GeoPointJsonConverter implements JsonConverter<GeoPoint, Map<String, dynamic>> {
  const GeoPointJsonConverter();

  @override
  GeoPoint fromJson(Map<String, dynamic> json) {
    return GeoPoint(
      (json['latitude'] as num).toDouble(),
      (json['longitude'] as num).toDouble(),
    );
  }

  @override
  Map<String, dynamic> toJson(GeoPoint geoPoint) {
    return {
      'latitude': geoPoint.latitude,
      'longitude': geoPoint.longitude,
    };
  }
}

/// Property Listing Model - For Buy/Sell Marketplace
/// Anyone can post: Customer, Associate, Agent, Employee
@freezed
class PropertyListing with _$PropertyListing {
  const PropertyListing._();

  const factory PropertyListing({
    required String id,
    required String title,
    required String description,
    required PropertyType propertyType, // Plot, House, Flat, Shop, Farmhouse
    required ListingPurpose purpose, // Sell, Rent, Lease
    
    // Owner Details
    required String ownerId,
    required String ownerName,
    required String ownerPhone,
    required String ownerEmail,
    required OwnerType ownerType, // Customer, Associate, Agent, Employee
    
    // Location
    required String state,
    required String district,
    required String city,
    required String locality,
    required String address,
    @GeoPointJsonConverter() required GeoPoint location,
    required String landmark,
    
    // Property Details
    required double areaSqft,
    String? areaUnit, // sqft, acre, guntha
    required double expectedPrice,
    PriceNegotiable? negotiable,
    required String priceType, // Fixed, Negotiable, Best Offer
    
    // Features
    @Default([]) List<String> images,
    @Default([]) List<String> videos,
    @Default([]) List<String> documents, // Legal papers, registry
    Map<String, dynamic>? features, // Bedrooms, bathrooms, parking, etc.
    
    // Status & Verification
    required ListingStatus status, // Pending, Verified, Active, Sold, Rejected
    String? verifiedBy,
    DateTime? verifiedAt,
    String? rejectionReason,
    int? verificationFee, // Admin charges for verification
    
    // Statistics
    @Default(0) int viewCount,
    @Default(0) int inquiryCount,
    @Default(0) int callCount,
    @Default(0) int whatsappCount,
    DateTime? lastInquiryAt,
    
    // Listing Plan (Monetization)
    ListingPlan? listingPlan, // Free, Featured, Premium, Spotlight
    DateTime? planExpiryDate,
    @Default(false) bool isFeatured,
    @Default(false) bool isPremium,
    @Default(false) bool isSpotlight,
    
    // Lead Generation
    @Default([]) List<String> interestedBuyers, // User IDs
    @Default([]) List<PropertyInquiry> inquiries,
    
    // Admin Notes
    String? adminNotes,
    @Default([]) List<String> tags,
    
    required DateTime createdAt,
    required DateTime updatedAt,
  }) = _PropertyListing;

  factory PropertyListing.fromJson(Map<String, dynamic> json) =>
      _$PropertyListingFromJson(json);

  factory PropertyListing.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return PropertyListing(
      id: doc.id,
      title: (data['title'] as String?) ?? '',
      description: (data['description'] as String?) ?? '',
      propertyType: PropertyType.values.firstWhere(
        (e) => e.name == data['propertyType'],
        orElse: () => PropertyType.plot,
      ),
      purpose: ListingPurpose.values.firstWhere(
        (e) => e.name == data['purpose'],
        orElse: () => ListingPurpose.sell,
      ),
      ownerId: (data['ownerId'] as String?) ?? '',
      ownerName: (data['ownerName'] as String?) ?? '',
      ownerPhone: (data['ownerPhone'] as String?) ?? '',
      ownerEmail: (data['ownerEmail'] as String?) ?? '',
      ownerType: OwnerType.values.firstWhere(
        (e) => e.name == data['ownerType'],
        orElse: () => OwnerType.customer,
      ),
      state: (data['state'] as String?) ?? '',
      district: (data['district'] as String?) ?? '',
      city: (data['city'] as String?) ?? '',
      locality: (data['locality'] as String?) ?? '',
      address: (data['address'] as String?) ?? '',
      location: data['location'] as GeoPoint,
      landmark: (data['landmark'] as String?) ?? '',
      areaSqft: (data['areaSqft'] as num? ?? 0).toDouble(),
      areaUnit: data['areaUnit'] as String?,
      expectedPrice: (data['expectedPrice'] as num? ?? 0).toDouble(),
      negotiable: data['negotiable'] != null
          ? PriceNegotiable.values.firstWhere(
              (e) => e.name == data['negotiable'],
              orElse: () => PriceNegotiable.fixed,
            )
          : null,
      priceType: (data['priceType'] as String?) ?? 'Fixed',
      images: List<String>.from(data['images'] as List? ?? []),
      videos: List<String>.from(data['videos'] as List? ?? []),
      documents: List<String>.from(data['documents'] as List? ?? []),
      features: data['features'] as Map<String, dynamic>?,
      status: ListingStatus.values.firstWhere(
        (e) => e.name == data['status'],
        orElse: () => ListingStatus.pending,
      ),
      verifiedBy: data['verifiedBy'] as String?,
      verifiedAt: (data['verifiedAt'] as Timestamp?)?.toDate(),
      rejectionReason: data['rejectionReason'] as String?,
      verificationFee: (data['verificationFee'] as num?)?.toInt(),
      viewCount: (data['viewCount'] as num? ?? 0).toInt(),
      inquiryCount: (data['inquiryCount'] as num? ?? 0).toInt(),
      callCount: (data['callCount'] as num? ?? 0).toInt(),
      whatsappCount: (data['whatsappCount'] as num? ?? 0).toInt(),
      lastInquiryAt: (data['lastInquiryAt'] as Timestamp?)?.toDate(),
      listingPlan: data['listingPlan'] != null
          ? ListingPlan.values.firstWhere(
              (e) => e.name == data['listingPlan'],
              orElse: () => ListingPlan.free,
            )
          : null,
      planExpiryDate: (data['planExpiryDate'] as Timestamp?)?.toDate(),
      isFeatured: (data['isFeatured'] as bool?) ?? false,
      isPremium: (data['isPremium'] as bool?) ?? false,
      isSpotlight: (data['isSpotlight'] as bool?) ?? false,
      interestedBuyers: List<String>.from(data['interestedBuyers'] as List? ?? []),
      inquiries: (data['inquiries'] as List<dynamic>?)
              ?.map((e) => PropertyInquiry.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      adminNotes: data['adminNotes'] as String?,
      tags: List<String>.from(data['tags'] as List? ?? []),
      createdAt: (data['createdAt'] as Timestamp?)?.toDate() ?? DateTime.now(),
      updatedAt: (data['updatedAt'] as Timestamp?)?.toDate() ?? DateTime.now(),
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'title': title,
      'description': description,
      'propertyType': propertyType.name,
      'purpose': purpose.name,
      'ownerId': ownerId,
      'ownerName': ownerName,
      'ownerPhone': ownerPhone,
      'ownerEmail': ownerEmail,
      'ownerType': ownerType.name,
      'state': state,
      'district': district,
      'city': city,
      'locality': locality,
      'address': address,
      'location': location,
      'landmark': landmark,
      'areaSqft': areaSqft,
      'areaUnit': areaUnit,
      'expectedPrice': expectedPrice,
      'negotiable': negotiable?.name,
      'priceType': priceType,
      'images': images,
      'videos': videos,
      'documents': documents,
      'features': features,
      'status': status.name,
      'verifiedBy': verifiedBy,
      'verifiedAt': verifiedAt != null ? Timestamp.fromDate(verifiedAt!) : null,
      'rejectionReason': rejectionReason,
      'verificationFee': verificationFee,
      'viewCount': viewCount,
      'inquiryCount': inquiryCount,
      'callCount': callCount,
      'whatsappCount': whatsappCount,
      'lastInquiryAt': lastInquiryAt != null ? Timestamp.fromDate(lastInquiryAt!) : null,
      'listingPlan': listingPlan?.name,
      'planExpiryDate': planExpiryDate != null ? Timestamp.fromDate(planExpiryDate!) : null,
      'isFeatured': isFeatured,
      'isPremium': isPremium,
      'isSpotlight': isSpotlight,
      'interestedBuyers': interestedBuyers,
      'inquiries': inquiries.map((e) => e.toJson()).toList(),
      'adminNotes': adminNotes,
      'tags': tags,
      'createdAt': Timestamp.fromDate(createdAt),
      'updatedAt': Timestamp.fromDate(updatedAt),
    };
  }
}

enum PropertyType { plot, house, flat, shop, farmhouse, land, commercial, industrial }

enum ListingPurpose { sell, rent, lease }

enum OwnerType { customer, associate, agent, employee, admin }

enum PriceNegotiable { fixed, negotiable, bestOffer }

enum ListingStatus { pending, underReview, verified, active, sold, rejected, expired }

enum ListingPlan { free, featured, premium, spotlight }

@freezed
class PropertyInquiry with _$PropertyInquiry {
  const factory PropertyInquiry({
    required String id,
    required String buyerId,
    required String buyerName,
    required String buyerPhone,
    String? buyerEmail,
    required InquiryType type, // Call, WhatsApp, Email, Visit
    String? message,
    DateTime? scheduledVisitDate,
    required InquiryStatus status, // New, Contacted, InDiscussion, Negotiating, Closed
    DateTime? createdAt,
    DateTime? respondedAt,
    String? responseNotes,
  }) = _PropertyInquiry;

  factory PropertyInquiry.fromJson(Map<String, dynamic> json) =>
      _$PropertyInquiryFromJson(json);
}

enum InquiryType { call, whatsapp, email, visit, message }

enum InquiryStatus { new_inquiry, contacted, inDiscussion, negotiating, closed, converted }
