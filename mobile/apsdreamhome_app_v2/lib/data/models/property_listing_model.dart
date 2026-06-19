import 'package:freezed_annotation/freezed_annotation.dart';
import 'geo_location.dart';

part 'property_listing_model.freezed.dart';
part 'property_listing_model.g.dart';

/// Property Listing Model - For Buy/Sell Marketplace
/// Anyone can post: Customer, Associate, Agent, Employee
@freezed
class PropertyListing with _$PropertyListing {
  const PropertyListing._();

  const factory PropertyListing({
    @Default('') String id,
    @Default('') String title,
    @Default('') String description,
    required PropertyType propertyType, // Plot, House, Flat, Shop, Farmhouse
    required ListingPurpose purpose, // Sell, Rent, Lease
    
    // Owner Details
    @Default('') String ownerId,
    @Default('') String ownerName,
    @Default('') String ownerPhone,
    @Default('') String ownerEmail,
    required OwnerType ownerType, // Customer, Associate, Agent, Employee
    
    // Location
    @Default('') String state,
    @Default('') String district,
    @Default('') String city,
    @Default('') String locality,
    @Default('') String address,
    required GeoLocation location,
    @Default('') String landmark,
    
    // Property Details
    @Default(0.0) double areaSqft,
    String? areaUnit, // sqft, acre, guntha
    @Default(0.0) double expectedPrice,
    PriceNegotiable? negotiable,
    @Default('Fixed') String priceType, // Fixed, Negotiable, Best Offer
    
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
    @Default('') String id,
    @Default('') String buyerId,
    @Default('') String buyerName,
    @Default('') String buyerPhone,
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
