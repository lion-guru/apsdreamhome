/// Plot Model - Individual Plot Management (Self-contained)
class PlotModel {
  final String id;
  final String colonyId;
  final String colonyName;
  final String plotNumber;
  final double areaSqft;
  final String facing;
  final bool? isCorner;
  final bool? isParkFacing;
  final bool? isMainRoadFacing;
  final double basePrice;
  final double? cornerPremium;
  final double? parkFacingPremium;
  final double? mainRoadPremium;
  final double? finalPrice;
  final String status;
  final DateTime? holdUntil;
  final String? holdBy;
  final String? bookedBy;
  final String? bookedByName;
  final DateTime? bookedAt;
  final String? bookingId;
  final double? bookingAmount;
  final String? registeredTo;
  final DateTime? registryDate;
  final String? registryNumber;
  final double? frontWidth;
  final double? depth;
  final String? shape;
  final double? latitude;
  final double? longitude;
  final String? mapCoordinates;
  final List<String>? documents;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  PlotModel({
    required this.id,
    required this.colonyId,
    required this.colonyName,
    required this.plotNumber,
    required this.areaSqft,
    required this.facing,
    this.isCorner,
    this.isParkFacing,
    this.isMainRoadFacing,
    required this.basePrice,
    this.cornerPremium,
    this.parkFacingPremium,
    this.mainRoadPremium,
    this.finalPrice,
    required this.status,
    this.holdUntil,
    this.holdBy,
    this.bookedBy,
    this.bookedByName,
    this.bookedAt,
    this.bookingId,
    this.bookingAmount,
    this.registeredTo,
    this.registryDate,
    this.registryNumber,
    this.frontWidth,
    this.depth,
    this.shape,
    this.latitude,
    this.longitude,
    this.mapCoordinates,
    this.documents,
    this.createdAt,
    this.updatedAt,
  });

  factory PlotModel.fromJson(Map<String, dynamic> json) {
    return PlotModel(
      id: _safeId(json['id'] ?? json['plot_id']),
      colonyId: _safeId(json['colonyId'] ?? json['colony_id']),
      colonyName: (json['colonyName'] ?? json['colony_name'] ?? '') as String,
      plotNumber: (json['plotNumber'] ?? json['plot_no'] ?? json['plot_number'] ?? '') as String,
      areaSqft: _safeDouble(json['areaSqft'] ?? json['area_sqft']) ?? 0,
      facing: (json['facing'] ?? 'East') as String,
      isCorner: _safeBool(json['isCorner'] ?? json['is_corner'] ?? (json['corner_plot'] == 1 ? true : json['corner_plot'] == 0 ? false : null)),
      isParkFacing: _safeBool(json['isParkFacing'] ?? json['is_park_facing']),
      isMainRoadFacing: _safeBool(json['isMainRoadFacing'] ?? json['is_main_road_facing']),
      basePrice: _safeDouble(json['basePrice'] ?? json['base_price'] ?? json['total_price']) ?? 0,
      cornerPremium: _safeDouble(json['cornerPremium'] ?? json['corner_premium']),
      parkFacingPremium: _safeDouble(json['parkFacingPremium'] ?? json['park_facing_premium']),
      mainRoadPremium: _safeDouble(json['mainRoadPremium'] ?? json['main_road_premium']),
      finalPrice: _safeDouble(json['finalPrice'] ?? json['final_price']),
      status: (json['status'] ?? 'available') as String,
      holdUntil: _safeDate(json['holdUntil'] ?? json['hold_until']),
      holdBy: (json['holdBy'] ?? json['hold_by']) as String?,
      bookedBy: (json['bookedBy'] ?? json['booked_by']) as String?,
      bookedByName: (json['bookedByName'] ?? json['booked_by_name']) as String?,
      bookedAt: _safeDate(json['bookedAt'] ?? json['booked_at']),
      bookingId: (json['bookingId'] ?? json['booking_id']) as String?,
      bookingAmount: _safeDouble(json['bookingAmount'] ?? json['booking_amount']),
      registeredTo: (json['registeredTo'] ?? json['registered_to']) as String?,
      registryDate: _safeDate(json['registryDate'] ?? json['registry_date']),
      registryNumber: (json['registryNumber'] ?? json['registry_number']) as String?,
      frontWidth: _safeDouble(json['frontWidth'] ?? json['front_width']),
      depth: _safeDouble(json['depth']),
      shape: json['shape'] as String?,
      latitude: _safeDouble(json['latitude']),
      longitude: _safeDouble(json['longitude']),
      mapCoordinates: (json['mapCoordinates'] ?? json['map_coordinates']) as String?,
      documents: (json['documents'] as List?)?.map((e) => e as String).toList(),
      createdAt: _safeDate(json['createdAt'] ?? json['created_at']),
      updatedAt: _safeDate(json['updatedAt'] ?? json['updated_at']),
    );
  }

  static String _safeId(dynamic v) => v?.toString() ?? '';
  static DateTime? _safeDate(dynamic v) {
    if (v == null) return null;
    try { return DateTime.parse(v.toString()); } catch (_) { return null; }
  }
  static double? _safeDouble(dynamic v) => v is num ? v.toDouble() : null;
  static bool? _safeBool(dynamic v) => v is bool ? v : null;

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'colony_id': colonyId,
      'colony_name': colonyName,
      'plot_no': plotNumber,
      'area_sqft': areaSqft,
      'facing': facing,
      'is_corner': isCorner,
      'is_park_facing': isParkFacing,
      'is_main_road_facing': isMainRoadFacing,
      'base_price': basePrice,
      'corner_premium': cornerPremium,
      'park_facing_premium': parkFacingPremium,
      'main_road_premium': mainRoadPremium,
      'final_price': finalPrice,
      'status': status,
      'hold_until': holdUntil?.toIso8601String(),
      'hold_by': holdBy,
      'booked_by': bookedBy,
      'booked_by_name': bookedByName,
      'booked_at': bookedAt?.toIso8601String(),
      'booking_id': bookingId,
      'booking_amount': bookingAmount,
      'registered_to': registeredTo,
      'registry_date': registryDate?.toIso8601String(),
      'registry_number': registryNumber,
      'front_width': frontWidth,
      'depth': depth,
      'shape': shape,
      'latitude': latitude,
      'longitude': longitude,
      'map_coordinates': mapCoordinates,
      'documents': documents,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  PlotModel copyWith({
    String? id,
    String? colonyId,
    String? colonyName,
    String? plotNumber,
    double? areaSqft,
    String? facing,
    bool? isCorner,
    bool? isParkFacing,
    bool? isMainRoadFacing,
    double? basePrice,
    double? cornerPremium,
    double? parkFacingPremium,
    double? mainRoadPremium,
    double? finalPrice,
    String? status,
    DateTime? holdUntil,
    String? holdBy,
    String? bookedBy,
    String? bookedByName,
    DateTime? bookedAt,
    String? bookingId,
    double? bookingAmount,
    String? registeredTo,
    DateTime? registryDate,
    String? registryNumber,
    double? frontWidth,
    double? depth,
    String? shape,
    double? latitude,
    double? longitude,
    String? mapCoordinates,
    List<String>? documents,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return PlotModel(
      id: id ?? this.id,
      colonyId: colonyId ?? this.colonyId,
      colonyName: colonyName ?? this.colonyName,
      plotNumber: plotNumber ?? this.plotNumber,
      areaSqft: areaSqft ?? this.areaSqft,
      facing: facing ?? this.facing,
      isCorner: isCorner ?? this.isCorner,
      isParkFacing: isParkFacing ?? this.isParkFacing,
      isMainRoadFacing: isMainRoadFacing ?? this.isMainRoadFacing,
      basePrice: basePrice ?? this.basePrice,
      cornerPremium: cornerPremium ?? this.cornerPremium,
      parkFacingPremium: parkFacingPremium ?? this.parkFacingPremium,
      mainRoadPremium: mainRoadPremium ?? this.mainRoadPremium,
      finalPrice: finalPrice ?? this.finalPrice,
      status: status ?? this.status,
      holdUntil: holdUntil ?? this.holdUntil,
      holdBy: holdBy ?? this.holdBy,
      bookedBy: bookedBy ?? this.bookedBy,
      bookedByName: bookedByName ?? this.bookedByName,
      bookedAt: bookedAt ?? this.bookedAt,
      bookingId: bookingId ?? this.bookingId,
      bookingAmount: bookingAmount ?? this.bookingAmount,
      registeredTo: registeredTo ?? this.registeredTo,
      registryDate: registryDate ?? this.registryDate,
      registryNumber: registryNumber ?? this.registryNumber,
      frontWidth: frontWidth ?? this.frontWidth,
      depth: depth ?? this.depth,
      shape: shape ?? this.shape,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      mapCoordinates: mapCoordinates ?? this.mapCoordinates,
      documents: documents ?? this.documents,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  double get totalPrice {
    double price = basePrice;
    if (isCorner == true) price += (price * (cornerPremium ?? 10) / 100);
    if (isParkFacing == true) price += (price * (parkFacingPremium ?? 5) / 100);
    if (isMainRoadFacing == true) {
      price += (price * (mainRoadPremium ?? 8) / 100);
    }
    return price;
  }

  double get pricePerSqft => areaSqft > 0 ? totalPrice / areaSqft : 0;
  bool get isAvailable => status == 'available';
  bool get isHold => status == 'hold';
  bool get isBooked => status == 'booked';
  bool get isSold => status == 'sold';
  bool get hasPremiumLocation =>
      (isCorner == true) ||
      (isParkFacing == true) ||
      (isMainRoadFacing == true);

  String get statusColor {
    switch (status) {
      case 'available':
        return '#4CAF50';
      case 'hold':
        return '#FFC107';
      case 'booked':
        return '#2196F3';
      case 'sold':
        return '#E53935';
      default:
        return '#9E9E9E';
    }
  }

  // Compatibility getters
  List<String> get images => documents?.cast<String>() ?? const [];
}

/// Plot Filter Model
class PlotFilter {
  final String? colonyId;
  final List<String>? facings;
  final double? minArea;
  final double? maxArea;
  final double? minPrice;
  final double? maxPrice;
  final bool? cornerOnly;
  final bool? parkFacingOnly;
  final String? status;
  final String? sortBy;

  PlotFilter({
    this.colonyId,
    this.facings,
    this.minArea,
    this.maxArea,
    this.minPrice,
    this.maxPrice,
    this.cornerOnly,
    this.parkFacingOnly,
    this.status,
    this.sortBy,
  });

  factory PlotFilter.fromJson(Map<String, dynamic> json) {
    return PlotFilter(
      colonyId: json['colonyId'] as String?,
      facings: (json['facings'] as List<dynamic>?)?.cast<String>(),
      minArea: (json['minArea'] as num?)?.toDouble(),
      maxArea: (json['maxArea'] as num?)?.toDouble(),
      minPrice: (json['minPrice'] as num?)?.toDouble(),
      maxPrice: (json['maxPrice'] as num?)?.toDouble(),
      cornerOnly: json['cornerOnly'] as bool?,
      parkFacingOnly: json['parkFacingOnly'] as bool?,
      status: json['status'] as String?,
      sortBy: json['sortBy'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'colonyId': colonyId,
      'facings': facings,
      'minArea': minArea,
      'maxArea': maxArea,
      'minPrice': minPrice,
      'maxPrice': maxPrice,
      'cornerOnly': cornerOnly,
      'parkFacingOnly': parkFacingOnly,
      'status': status,
      'sortBy': sortBy,
    };
  }

  PlotFilter copyWith({
    String? colonyId,
    List<String>? facings,
    double? minArea,
    double? maxArea,
    double? minPrice,
    double? maxPrice,
    bool? cornerOnly,
    bool? parkFacingOnly,
    String? status,
    String? sortBy,
  }) {
    return PlotFilter(
      colonyId: colonyId ?? this.colonyId,
      facings: facings ?? this.facings,
      minArea: minArea ?? this.minArea,
      maxArea: maxArea ?? this.maxArea,
      minPrice: minPrice ?? this.minPrice,
      maxPrice: maxPrice ?? this.maxPrice,
      cornerOnly: cornerOnly ?? this.cornerOnly,
      parkFacingOnly: parkFacingOnly ?? this.parkFacingOnly,
      status: status ?? this.status,
      sortBy: sortBy ?? this.sortBy,
    );
  }
}
