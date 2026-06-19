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
    required GeoLocation location,
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
