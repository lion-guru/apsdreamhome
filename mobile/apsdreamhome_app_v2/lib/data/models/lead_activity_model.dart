/// Lead Activity Model for tracking lead interactions
class LeadActivity {
  final String id;
  final String leadId;
  final String type;
  final String? description;
  final String? performedBy;
  final DateTime? createdAt;
  final DateTime? performedAt;
  final String? notes;
  final String? outcome;
  final String? recordingUrl;
  final List<String>? photos;

  const LeadActivity({
    required this.id,
    required this.leadId,
    required this.type,
    this.description,
    this.performedBy,
    this.createdAt,
    this.performedAt,
    this.notes,
    this.outcome,
    this.recordingUrl,
    this.photos,
  });

  factory LeadActivity.fromJson(Map<String, dynamic> json) {
    return LeadActivity(
      id: json['id'] as String,
      leadId: json['leadId'] as String,
      type: json['type'] as String,
      description: json['description'] as String?,
      performedBy: json['performedBy'] as String?,
      createdAt: json['createdAt'] != null
          ? DateTime.parse(json['createdAt'] as String)
          : null,
      performedAt: json['performedAt'] != null
          ? DateTime.parse(json['performedAt'] as String)
          : null,
      notes: json['notes'] as String?,
      outcome: json['outcome'] as String?,
      recordingUrl: json['recordingUrl'] as String?,
      photos: (json['photos'] as List<dynamic>?)?.cast<String>(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'leadId': leadId,
      'type': type,
      'description': description,
      'performedBy': performedBy,
      'createdAt': createdAt?.toIso8601String(),
      'performedAt': performedAt?.toIso8601String(),
      'notes': notes,
      'outcome': outcome,
      'recordingUrl': recordingUrl,
      'photos': photos,
    };
  }
}

/// Lead Statistics Model for analytics
class LeadStatistics {
  final int totalLeads;
  final int newLeads;
  final int contactedLeads;
  final int qualifiedLeads;
  final int convertedLeads;
  final int lostLeads;
  final double? conversionRate;
  final int? interestedLeads;
  final int? visitedLeads;
  final Map<String, int>? bySource;
  final Map<String, int>? byStatus;
  final Map<String, int>? byMonth;
  final double? averageResponseTime;
  final int? followUpsDueToday;
  final int? followUpsOverdue;

  const LeadStatistics({
    required this.totalLeads,
    required this.newLeads,
    required this.contactedLeads,
    required this.qualifiedLeads,
    required this.convertedLeads,
    required this.lostLeads,
    this.conversionRate,
    this.interestedLeads,
    this.visitedLeads,
    this.bySource,
    this.byStatus,
    this.byMonth,
    this.averageResponseTime,
    this.followUpsDueToday,
    this.followUpsOverdue,
  });

  factory LeadStatistics.fromJson(Map<String, dynamic> json) {
    return LeadStatistics(
      totalLeads: json['totalLeads'] as int,
      newLeads: json['newLeads'] as int,
      contactedLeads: json['contactedLeads'] as int,
      qualifiedLeads: json['qualifiedLeads'] as int,
      convertedLeads: json['convertedLeads'] as int,
      lostLeads: json['lostLeads'] as int,
      conversionRate: (json['conversionRate'] as num?)?.toDouble(),
      interestedLeads: json['interestedLeads'] as int?,
      visitedLeads: json['visitedLeads'] as int?,
      bySource:
          (json['bySource'] as Map<String, dynamic>?)?.cast<String, int>(),
      byStatus:
          (json['byStatus'] as Map<String, dynamic>?)?.cast<String, int>(),
      byMonth: (json['byMonth'] as Map<String, dynamic>?)?.cast<String, int>(),
      averageResponseTime: (json['averageResponseTime'] as num?)?.toDouble(),
      followUpsDueToday: json['followUpsDueToday'] as int?,
      followUpsOverdue: json['followUpsOverdue'] as int?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'totalLeads': totalLeads,
      'newLeads': newLeads,
      'contactedLeads': contactedLeads,
      'qualifiedLeads': qualifiedLeads,
      'convertedLeads': convertedLeads,
      'lostLeads': lostLeads,
      'conversionRate': conversionRate,
      'interestedLeads': interestedLeads,
      'visitedLeads': visitedLeads,
      'bySource': bySource,
      'byStatus': byStatus,
      'byMonth': byMonth,
      'averageResponseTime': averageResponseTime,
      'followUpsDueToday': followUpsDueToday,
      'followUpsOverdue': followUpsOverdue,
    };
  }
}
