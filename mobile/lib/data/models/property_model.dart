import 'package:json_annotation/json_annotation.dart';

part 'property_model.g.dart';

@JsonSerializable()
class Property {
  final String propertyId;
  final String title;
  final String description;
  final String type; // Residential, Commercial, Plot
  final double price;
  final double? size; // in sq ft or sq meters
  final String location;
  final String status; // Available, Booked, Sold, Hold
  final String? imageUrl;
  final String createdAt;
  final String updatedAt;
  final String? lastSyncedAt;
  
  const Property({
    required this.propertyId,
    required this.title,
    required this.description,
    required this.type,
    required this.price,
    this.size,
    required this.location,
    required this.status,
    this.imageUrl,
    required this.createdAt,
    required this.updatedAt,
    this.lastSyncedAt,
  });
  
  factory Property.fromJson(Map<String, dynamic> json) => _$PropertyFromJson(json);
  Map<String, dynamic> toJson() => _$PropertyToJson(this);
  
  Property copyWith({
    String? propertyId,
    String? title,
    String? description,
    String? type,
    double? price,
    double? size,
    String? location,
    String? status,
    String? imageUrl,
    String? createdAt,
    String? updatedAt,
    String? lastSyncedAt,
  }) {
    return Property(
      propertyId: propertyId ?? this.propertyId,
      title: title ?? this.title,
      description: description ?? this.description,
      type: type ?? this.type,
      price: price ?? this.price,
      size: size ?? this.size,
      location: location ?? this.location,
      status: status ?? this.status,
      imageUrl: imageUrl ?? this.imageUrl,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      lastSyncedAt: lastSyncedAt ?? this.lastSyncedAt,
    );
  }
  
  // Get status color
  String getStatusColor() {
    switch (status.toLowerCase()) {
      case 'available':
        return '#4CAF50'; // Green
      case 'booked':
        return '#FF9800'; // Orange
      case 'sold':
        return '#F44336'; // Red
      case 'hold':
        return '#9E9E9E'; // Grey
      default:
        return '#2196F3'; // Blue
    }
  }
  
  // Format price
  String get formattedPrice {
    if (price >= 10000000) {
      return '${(price / 10000000).toStringAsFixed(2)} Cr';
    } else if (price >= 100000) {
      return '${(price / 100000).toStringAsFixed(2)} L';
    } else if (price >= 1000) {
      return '${(price / 1000).toStringAsFixed(2)} K';
    } else {
      return price.toStringAsFixed(2);
    }
  }
  
  // Format size
  String get formattedSize {
    if (size == null) return 'N/A';
    if (size! >= 10000) {
      return '${(size! / 10000).toStringAsFixed(2)} acres';
    } else {
      return '${size!.toStringAsFixed(0)} sq ft';
    }
  }
  
  // Check if property is available
  bool get isAvailable => status.toLowerCase() == 'available';
  
  // Check if property is sold
  bool get isSold => status.toLowerCase() == 'sold';
  
  // Check if property is booked
  bool get isBooked => status.toLowerCase() == 'booked';
  
  // Check if property is on hold
  bool get isOnHold => status.toLowerCase() == 'hold';
}
