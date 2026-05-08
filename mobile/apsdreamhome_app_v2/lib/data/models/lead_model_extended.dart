/// Extended Lead Model with additional fields for AI processing
/// This extends the base Lead class with fields needed for lead management
class LeadModel {
  final String id;
  final int? serverId;
  final String name;
  final String? email;
  final String phone;
  final String? address;
  final String? source;
  final String? priority;
  final double? budgetMin;
  final double? budgetMax;
  final String? interestedIn;
  final String? preferredLocation;
  final String? followUpNotes;
  final String? status;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  final String? assignedTo;
  final String? assignedToName;
  final Map<String, dynamic>? customFields;
  final String? voiceNoteUrl;
  final String? voiceTranscript;
  final bool? isOfflineCreated;

  const LeadModel({
    required this.id,
    this.serverId,
    required this.name,
    this.email,
    required this.phone,
    this.address,
    this.source,
    this.priority,
    this.budgetMin,
    this.budgetMax,
    this.interestedIn,
    this.preferredLocation,
    this.followUpNotes,
    this.status,
    this.createdAt,
    this.updatedAt,
    this.assignedTo,
    this.assignedToName,
    this.customFields,
    this.voiceNoteUrl,
    this.voiceTranscript,
    this.isOfflineCreated,
  });

  factory LeadModel.fromJson(Map<String, dynamic> json) {
    return LeadModel(
      id: json['id'] as String,
      serverId: (json['serverId'] as int?) ?? (json['server_id'] as int?),
      name: json['name'] as String,
      email: json['email'] as String?,
      phone: json['phone'] as String,
      address: json['address'] as String?,
      source: json['source'] as String?,
      priority: json['priority'] as String?,
      budgetMin: (json['budgetMin'] as num?)?.toDouble(),
      budgetMax: (json['budgetMax'] as num?)?.toDouble(),
      interestedIn: json['interestedIn'] as String?,
      preferredLocation: json['preferredLocation'] as String?,
      followUpNotes: json['followUpNotes'] as String?,
      status: json['status'] as String?,
      assignedTo: json['assignedTo'] as String?,
      assignedToName: json['assignedToName'] as String?,
      customFields: json['customFields'] as Map<String, dynamic>?,
      voiceNoteUrl: json['voiceNoteUrl'] as String?,
      voiceTranscript: json['voiceTranscript'] as String?,
      isOfflineCreated: json['isOfflineCreated'] as bool?,
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
      'server_id': serverId,
      'name': name,
      'email': email,
      'phone': phone,
      'address': address,
      'source': source,
      'priority': priority,
      'budgetMin': budgetMin,
      'budgetMax': budgetMax,
      'interestedIn': interestedIn,
      'preferredLocation': preferredLocation,
      'followUpNotes': followUpNotes,
      'status': status,
      'assignedTo': assignedTo,
      'assignedToName': assignedToName,
      'customFields': customFields,
      'voiceNoteUrl': voiceNoteUrl,
      'voiceTranscript': voiceTranscript,
      'isOfflineCreated': isOfflineCreated,
      'createdAt': createdAt?.toIso8601String(),
      'updatedAt': updatedAt?.toIso8601String(),
    };
  }

  LeadModel copyWith({
    String? id,
    int? serverId,
    String? name,
    String? email,
    String? phone,
    String? address,
    String? source,
    String? priority,
    double? budgetMin,
    double? budgetMax,
    String? interestedIn,
    String? preferredLocation,
    String? followUpNotes,
    String? status,
    DateTime? createdAt,
    DateTime? updatedAt,
    String? assignedTo,
    String? assignedToName,
    Map<String, dynamic>? customFields,
    String? voiceNoteUrl,
    String? voiceTranscript,
    bool? isOfflineCreated,
  }) {
    return LeadModel(
      id: id ?? this.id,
      serverId: serverId ?? this.serverId,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      address: address ?? this.address,
      source: source ?? this.source,
      priority: priority ?? this.priority,
      budgetMin: budgetMin ?? this.budgetMin,
      budgetMax: budgetMax ?? this.budgetMax,
      interestedIn: interestedIn ?? this.interestedIn,
      preferredLocation: preferredLocation ?? this.preferredLocation,
      followUpNotes: followUpNotes ?? this.followUpNotes,
      status: status ?? this.status,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      assignedTo: assignedTo ?? this.assignedTo,
      assignedToName: assignedToName ?? this.assignedToName,
      customFields: customFields ?? this.customFields,
      voiceNoteUrl: voiceNoteUrl ?? this.voiceNoteUrl,
      voiceTranscript: voiceTranscript ?? this.voiceTranscript,
      isOfflineCreated: isOfflineCreated ?? this.isOfflineCreated,
    );
  }

  // Getters for compatibility
  String? get displayBudget {
    if (budgetMin == null && budgetMax == null) return null;
    if (budgetMin != null && budgetMax != null) {
      return '₹${budgetMin!.toStringAsFixed(0)} - ₹${budgetMax!.toStringAsFixed(0)}';
    }
    return '₹${(budgetMin ?? budgetMax)!.toStringAsFixed(0)}';
  }

  bool get needsFollowUp => status?.toLowerCase() == 'new' || status?.toLowerCase() == 'followup';
}

// Type alias for backward compatibility
typedef Lead = LeadModel;
