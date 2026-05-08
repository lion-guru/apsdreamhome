// import 'package:json_annotation/json_annotation.dart'; // Temporarily removed due to build_runner issues

// part 'lead_model.g.dart'; // Temporarily removed due to build_runner issues

// @JsonSerializable() // Temporarily removed due to build_runner issues
class Lead {
  final String leadId;
  final String name;
  final String? email;
  final String phone;
  final String? propertyInterest;
  final double? budget;
  final String status; // New, Contacted, Interested, Not Interested, Converted
  final String? notes;
  final String createdAt;
  final String updatedAt;
  final String? lastSyncedAt;
  final bool isSynced;

  const Lead({
    required this.leadId,
    required this.name,
    this.email,
    required this.phone,
    this.propertyInterest,
    this.budget,
    required this.status,
    this.notes,
    required this.createdAt,
    required this.updatedAt,
    this.lastSyncedAt,
    this.isSynced = false,
  });

  factory Lead.fromJson(Map<String, dynamic> json) => Lead(
        leadId: json['lead_id'] as String? ?? '',
        name: json['name'] as String? ?? '',
        email: json['email'] as String?,
        phone: json['phone'] as String? ?? '',
        propertyInterest: json['property_interest'] as String?,
        budget: (json['budget'] as num?)?.toDouble(),
        status: json['status'] as String? ?? '',
        notes: json['notes'] as String?,
        createdAt: json['created_at'] as String? ?? '',
        updatedAt: json['updated_at'] as String? ?? '',
        lastSyncedAt: json['last_synced_at'] as String?,
        isSynced: json['is_synced'] as bool? ?? false,
      );

  Map<String, dynamic> toJson() => {
        'lead_id': leadId,
        'name': name,
        'email': email,
        'phone': phone,
        'property_interest': propertyInterest,
        'budget': budget,
        'status': status,
        'notes': notes,
        'created_at': createdAt,
        'updated_at': updatedAt,
        'last_synced_at': lastSyncedAt,
        'is_synced': isSynced,
      };

  Lead copyWith({
    String? leadId,
    String? name,
    String? email,
    String? phone,
    String? propertyInterest,
    double? budget,
    String? status,
    String? notes,
    String? createdAt,
    String? updatedAt,
    String? lastSyncedAt,
    bool? isSynced,
  }) {
    return Lead(
      leadId: leadId ?? this.leadId,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      propertyInterest: propertyInterest ?? this.propertyInterest,
      budget: budget ?? this.budget,
      status: status ?? this.status,
      notes: notes ?? this.notes,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      lastSyncedAt: lastSyncedAt ?? this.lastSyncedAt,
      isSynced: isSynced ?? this.isSynced,
    );
  }

  // Get status color
  String getStatusColor() {
    switch (status.toLowerCase()) {
      case 'new':
        return '#2196F3'; // Blue
      case 'contacted':
        return '#FF9800'; // Orange
      case 'interested':
        return '#4CAF50'; // Green
      case 'not interested':
        return '#F44336'; // Red
      case 'converted':
        return '#9C27B0'; // Purple
      default:
        return '#9E9E9E'; // Grey
    }
  }

  // Format budget
  String get formattedBudget {
    if (budget == null) return 'Not specified';
    if (budget! >= 10000000) {
      return '${(budget! / 10000000).toStringAsFixed(2)} Cr';
    } else if (budget! >= 100000) {
      return '${(budget! / 100000).toStringAsFixed(2)} L';
    } else if (budget! >= 1000) {
      return '${(budget! / 1000).toStringAsFixed(2)} K';
    } else {
      return budget!.toStringAsFixed(2);
    }
  }

  // Check if lead is new
  bool get isNew => status.toLowerCase() == 'new';

  // Check if lead is contacted
  bool get isContacted => status.toLowerCase() == 'contacted';

  // Check if lead is interested
  bool get isInterested => status.toLowerCase() == 'interested';

  // Check if lead is not interested
  bool get isNotInterested => status.toLowerCase() == 'not interested';

  // Check if lead is converted
  bool get isConverted => status.toLowerCase() == 'converted';

  // Get status priority for sorting
  int get statusPriority {
    switch (status.toLowerCase()) {
      case 'new':
        return 1;
      case 'contacted':
        return 2;
      case 'interested':
        return 3;
      case 'not interested':
        return 4;
      case 'converted':
        return 5;
      default:
        return 6;
    }
  }
}
