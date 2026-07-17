import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/app_constants.dart';
import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';

/// Property Listing Service — fetches user-posted properties (buy/sell/rent)
/// from the `/properties` endpoint.
class PropertyListingService {
  final ApiService _api = ApiService();

  /// Get all properties with optional filters
  Future<List<PropertyListing>> getProperties({
    String? type, // plot, house, flat, shop, farmhouse
    String? purpose, // buy, rent, sell
    String? location,
    double? minPrice,
    double? maxPrice,
    int page = 1,
    int limit = 20,
  }) async {
    try {
      final params = <String, dynamic>{'page': page, 'limit': limit};
      if (type != null && type != 'all') params['type'] = type;
      if (purpose != null && purpose != 'all') params['purpose'] = purpose;
      if (location != null && location != 'all') params['location'] = location;
      if (minPrice != null && minPrice > 0) params['min_price'] = minPrice;
      if (maxPrice != null && maxPrice < 100000000) {
        params['max_price'] = maxPrice;
      }

      final response = await _api.get(
        '/properties/browse',
        queryParameters: params,
      );

      final data = response['data'] ?? [];
      return (data as List).map((json) {
        return PropertyListing.fromJson(json as Map<String, dynamic>);
      }).toList();
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching properties', e, stackTrace);
      return [];
    }
  }

  /// Get property by ID
  Future<PropertyListing?> getPropertyById(String id) async {
    try {
      final response = await _api.get('${AppConstants.propertiesEndpoint}/$id');
      final data = response['data'];
      if (data != null) {
        return PropertyListing.fromJson(data as Map<String, dynamic>);
      }
      return null;
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching property: $id', e, stackTrace);
      return null;
    }
  }

  /// Search properties by query
  Future<List<PropertyListing>> searchProperties(String query) async {
    try {
      final response = await _api.get(
        '${AppConstants.propertiesEndpoint}/search',
        queryParameters: {'q': query},
      );
      final data = response['data'] ?? [];
      return (data as List).map((json) {
        return PropertyListing.fromJson(json as Map<String, dynamic>);
      }).toList();
    } catch (e, stackTrace) {
      AppLogger.error('Error searching properties', e, stackTrace);
      return [];
    }
  }
}

/// Lightweight property listing model for marketplace display
class PropertyListing {
  final int id;
  final String title;
  final String description;
  final String type; // plot, house, flat, shop, farmhouse
  final String purpose; // buy, rent, sell
  final double price;
  final double? area;
  final String location;
  final String? city;
  final String? state;
  final String status; // available, sold, pending
  final String? imageUrl;
  final List<String> images; // multiple images from API
  final String ownerName;
  final String ownerType; // customer, associate, agent
  final bool isVerified;
  final int views;
  final int inquiries;
  final String createdAt;
  final bool isPremium;
  final bool isFeatured;
  final bool isUrgent;

  const PropertyListing({
    required this.id,
    required this.title,
    required this.description,
    required this.type,
    required this.purpose,
    required this.price,
    this.area,
    required this.location,
    this.city,
    this.state,
    required this.status,
    this.imageUrl,
    this.images = const [],
    required this.ownerName,
    required this.ownerType,
    required this.isVerified,
    required this.views,
    required this.inquiries,
    required this.createdAt,
    this.isPremium = false,
    this.isFeatured = false,
    this.isUrgent = false,
  });

  List<Map<String, dynamic>> get badges {
    final badges = <Map<String, dynamic>>[];
    if (isPremium) {
      badges.add({'label': 'Premium', 'color': '#FFD700', 'icon': 'star'});
    }
    if (isFeatured) {
      badges.add({
        'label': 'Featured',
        'color': '#4CAF50',
        'icon': 'trending_up',
      });
    }
    if (isUrgent) {
      badges.add({
        'label': 'Urgent',
        'color': '#FF5722',
        'icon': 'priority_high',
      });
    }
    return badges;
  }

  bool get isHighlighted => isPremium || isFeatured || isUrgent;

  factory PropertyListing.fromJson(Map<String, dynamic> json) {
    return PropertyListing(
      id: _parseInt(json['id']),
      title: _parseString(json['title']),
      description: _parseString(json['description']),
      type: _parseString(json['type'], fallback: 'plot'),
      purpose: _parseString(
        json['purpose'] ?? json['listing_type'],
        fallback: 'sell',
      ),
      price: _parseDouble(json['price']),
      area: json['area'] != null ? _parseDouble(json['area']) : null,
      location: _parseString(json['location'] ?? json['address']),
      city: json['city']?.toString(),
      state: json['state']?.toString(),
      status: _parseString(json['status'], fallback: 'available'),
      imageUrl: json['image']?.toString() ?? json['image_url']?.toString(),
      images:
          (json['images'] as List<dynamic>?)
              ?.map(
                (e) => (e is Map<String, dynamic>)
                    ? (e['image_path']?.toString() ?? '')
                    : e.toString(),
              )
              .where((s) => s.isNotEmpty)
              .toList() ??
          [],
      ownerName: _parseString(
        json['owner_name'] ?? json['posted_by'],
        fallback: 'Owner',
      ),
      ownerType: _parseString(json['owner_type'], fallback: 'customer'),
      isVerified: json['is_verified'] == true || json['is_verified'] == 1,
      views: _parseInt(json['views']),
      inquiries: _parseInt(json['inquiries']),
      createdAt: _parseString(json['created_at']),
      isPremium: json['is_premium'] == true || json['is_premium'] == 1,
      isFeatured: json['is_featured'] == true || json['is_featured'] == 1,
      isUrgent: json['is_urgent'] == true || json['is_urgent'] == 1,
    );
  }

  static int _parseInt(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is String) return int.tryParse(v) ?? 0;
    if (v is num) return v.toInt();
    return 0;
  }

  static double _parseDouble(dynamic v) {
    if (v == null) return 0;
    if (v is double) return v;
    if (v is int) return v.toDouble();
    if (v is String) return double.tryParse(v) ?? 0;
    if (v is num) return v.toDouble();
    return 0;
  }

  static String _parseString(dynamic v, {String fallback = ''}) {
    if (v == null) return fallback;
    return v.toString();
  }

  String get formattedPrice {
    if (price >= 10000000) {
      return '${(price / 10000000).toStringAsFixed(2)} Cr';
    } else if (price >= 100000) {
      return '${(price / 100000).toStringAsFixed(2)} L';
    } else if (price >= 1000) {
      return '${(price / 1000).toStringAsFixed(0)} K';
    }
    return price.toStringAsFixed(0);
  }

  String get formattedArea {
    if (area == null) return 'N/A';
    if (area! >= 10000) {
      return '${(area! / 10000).toStringAsFixed(2)} acres';
    }
    return '${area!.toStringAsFixed(0)} sq ft';
  }

  String get purposeLabel {
    switch (purpose.toLowerCase()) {
      case 'rent':
        return 'Rent';
      case 'sell':
      case 'buy':
        return 'Sell';
      default:
        return purpose.toUpperCase();
    }
  }

  bool get isAvailable => status.toLowerCase() == 'available';
}

// Provider
final propertyListingServiceProvider = Provider<PropertyListingService>((ref) {
  return PropertyListingService();
});

final propertyListingsProvider = FutureProvider<List<PropertyListing>>((
  ref,
) async {
  final service = ref.watch(propertyListingServiceProvider);
  return service.getProperties();
});
