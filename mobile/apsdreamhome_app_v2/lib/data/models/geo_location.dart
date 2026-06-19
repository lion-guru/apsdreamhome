/// Shared GeoLocation class replacing cloud_firestore's GeoPoint.
/// Used by EMI collection, property listing, and daily caller models.
class GeoLocation {
  final double latitude;
  final double longitude;

  const GeoLocation({required this.latitude, required this.longitude});

  factory GeoLocation.fromJson(Map<String, dynamic> json) => GeoLocation(
        latitude: (json['latitude'] as num).toDouble(),
        longitude: (json['longitude'] as num).toDouble(),
      );

  Map<String, dynamic> toJson() => {
        'latitude': latitude,
        'longitude': longitude,
      };

  /// Convenience named constructor matching Firestore's GeoPoint interface
  GeoLocation.fromLatLng(double lat, double lng)
      : latitude = lat,
        longitude = lng;

  @override
  String toString() => 'GeoLocation($latitude, $longitude)';

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is GeoLocation &&
          latitude == other.latitude &&
          longitude == other.longitude;

  @override
  int get hashCode => latitude.hashCode ^ longitude.hashCode;
}
