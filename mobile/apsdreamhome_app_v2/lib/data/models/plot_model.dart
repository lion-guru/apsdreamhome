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
      id: json['id'] as String,
      colonyId: json['colonyId'] as String,
      colonyName: json['colonyName'] as String,
      plotNumber: json['plotNumber'] as String,
      areaSqft: (json['areaSqft'] as num).toDouble(),
      facing: json['facing'] as String,
      isCorner: json['isCorner'] as bool?,
      isParkFacing: json['isParkFacing'] as bool?,
      isMainRoadFacing: json['isMainRoadFacing'] as bool?,
      basePrice: (json['basePrice'] as num).toDouble(),
      cornerPremium: (json['cornerPremium'] as num?)?.toDouble(),
      parkFacingPremium: (json['parkFacingPremium'] as num?)?.toDouble(),
      mainRoadPremium: (json['mainRoadPremium'] as num?)?.toDouble(),
      finalPrice: (json['finalPrice'] as num?)?.toDouble(),
      status: json['status'] as String,
      holdUntil: json['holdUntil'] != null
          ? DateTime.parse(json['holdUntil'] as String)
          : null,
      holdBy: json['holdBy'] as String?,
      bookedBy: json['bookedBy'] as String?,
      bookedByName: json['bookedByName'] as String?,
      bookedAt: json['bookedAt'] != null
          ? DateTime.parse(json['bookedAt'] as String)
          : null,
      bookingId: json['bookingId'] as String?,
      bookingAmount: (json['bookingAmount'] as num?)?.toDouble(),
      registeredTo: json['registeredTo'] as String?,
      registryDate: json['registryDate'] != null
          ? DateTime.parse(json['registryDate'] as String)
          : null,
      registryNumber: json['registryNumber'] as String?,
      frontWidth: (json['frontWidth'] as num?)?.toDouble(),
      depth: (json['depth'] as num?)?.toDouble(),
      shape: json['shape'] as String?,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      mapCoordinates: json['mapCoordinates'] as String?,
      documents: (json['documents'] as List?)?.map((e) => e as String).toList(),
      createdAt: json['createdAt'] != null
          ? DateTime.parse(json['createdAt'] as String)
          : null,
      updatedAt: json['updatedAt'] != null
          ? DateTime.parse(json['updatedAt'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'colonyId': colonyId,
      'colonyName': colonyName,
      'plotNumber': plotNumber,
      'areaSqft': areaSqft,
      'facing': facing,
      'isCorner': isCorner,
      'isParkFacing': isParkFacing,
      'isMainRoadFacing': isMainRoadFacing,
      'basePrice': basePrice,
      'cornerPremium': cornerPremium,
      'parkFacingPremium': parkFacingPremium,
      'mainRoadPremium': mainRoadPremium,
      'finalPrice': finalPrice,
      'status': status,
      'holdUntil': holdUntil?.toIso8601String(),
      'holdBy': holdBy,
      'bookedBy': bookedBy,
      'bookedByName': bookedByName,
      'bookedAt': bookedAt?.toIso8601String(),
      'bookingId': bookingId,
      'bookingAmount': bookingAmount,
      'registeredTo': registeredTo,
      'registryDate': registryDate?.toIso8601String(),
      'registryNumber': registryNumber,
      'frontWidth': frontWidth,
      'depth': depth,
      'shape': shape,
      'latitude': latitude,
      'longitude': longitude,
      'mapCoordinates': mapCoordinates,
      'documents': documents,
      'createdAt': createdAt?.toIso8601String(),
      'updatedAt': updatedAt?.toIso8601String(),
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
