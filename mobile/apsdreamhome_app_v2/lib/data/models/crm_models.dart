import 'package:flutter/material.dart';

class CRMLead {
  final int id;
  final String? leadNumber;
  final String name;
  final String? email;
  final String? phone;
  final String? status;
  final String? source;
  final String? priority;
  final String? leadCategory;
  final int? leadScore;
  final double? conversionProbability;
  final String? propertyInterest;
  final String? budgetRange;
  final String? locationPreference;
  final int? assignedTo;
  final String? assignedToName;
  final int? createdBy;
  final bool isConverted;
  final String? leadType;
  final double? totalPurchaseValue;
  final DateTime? lastActivityDate;
  final DateTime? nextFollowupDate;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  const CRMLead({
    required this.id,
    this.leadNumber,
    required this.name,
    this.email,
    this.phone,
    this.status,
    this.source,
    this.priority,
    this.leadCategory,
    this.leadScore,
    this.conversionProbability,
    this.propertyInterest,
    this.budgetRange,
    this.locationPreference,
    this.assignedTo,
    this.assignedToName,
    this.createdBy,
    this.isConverted = false,
    this.leadType,
    this.totalPurchaseValue,
    this.lastActivityDate,
    this.nextFollowupDate,
    this.createdAt,
    this.updatedAt,
  });

  factory CRMLead.fromJson(Map<String, dynamic> json) {
    return CRMLead(
      id: parseInt(json['id']),
      leadNumber: json['lead_number']?.toString(),
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString(),
      phone: json['phone']?.toString(),
      status: json['status']?.toString(),
      source: json['source']?.toString(),
      priority: json['priority']?.toString(),
      leadCategory: json['lead_category']?.toString(),
      leadScore: parseInt(json['lead_score']),
      conversionProbability: parseDouble(json['conversion_probability']),
      propertyInterest: json['property_interest']?.toString(),
      budgetRange: json['budget_range']?.toString(),
      locationPreference: json['location_preference']?.toString(),
      assignedTo: parseInt(json['assigned_to']),
      assignedToName: json['assigned_to_name']?.toString(),
      createdBy: parseInt(json['created_by']),
      isConverted: json['is_converted'] == true || json['is_converted'] == 1,
      leadType: json['lead_type']?.toString(),
      totalPurchaseValue: parseDouble(json['total_purchase_value']),
      lastActivityDate: parseDate(json['last_activity_date']),
      nextFollowupDate: parseDate(json['next_followup_date']),
      createdAt: parseDate(json['created_at']),
      updatedAt: parseDate(json['updated_at']),
    );
  }

  static int parseInt(dynamic v) => v is int ? v : int.tryParse(v?.toString() ?? '') ?? 0;
  static double? parseDouble(dynamic v) => v is num ? v.toDouble() : double.tryParse(v?.toString() ?? '');
  static DateTime? parseDate(dynamic v) {
    if (v == null) return null;
    try { return DateTime.parse(v.toString()); } catch (_) { return null; }
  }

  CRMLead copyWith({String? status, int? leadScore, String? assignedToName, DateTime? nextFollowupDate}) {
    return CRMLead(
      id: id, leadNumber: leadNumber, name: name, email: email, phone: phone,
      status: status ?? this.status, source: source, priority: priority,
      leadCategory: leadCategory, leadScore: leadScore ?? this.leadScore,
      conversionProbability: conversionProbability, propertyInterest: propertyInterest,
      budgetRange: budgetRange, locationPreference: locationPreference,
      assignedTo: assignedTo, assignedToName: assignedToName ?? this.assignedToName,
      createdBy: createdBy, isConverted: isConverted, leadType: leadType,
      totalPurchaseValue: totalPurchaseValue, lastActivityDate: lastActivityDate,
      nextFollowupDate: nextFollowupDate ?? this.nextFollowupDate,
      createdAt: createdAt, updatedAt: updatedAt,
    );
  }

  Color get categoryColor {
    switch (leadCategory) {
      case 'hot': return const Color(0xFFEF4444);
      case 'warm': return const Color(0xFFF59E0B);
      case 'lukewarm': return const Color(0xFF3B82F6);
      case 'cold': return const Color(0xFF6B7280);
      default: return const Color(0xFF9CA3AF);
    }
  }

  Color get statusColor {
    switch (status) {
      case 'new': return const Color(0xFF3B82F6);
      case 'contacted': return const Color(0xFF8B5CF6);
      case 'qualified': return const Color(0xFF06B6D4);
      case 'site_visit': return const Color(0xFFF59E0B);
      case 'proposal': return const Color(0xFFF97316);
      case 'negotiation': return const Color(0xFFEC4899);
      case 'booking': return const Color(0xFF8B5CF6);
      case 'won': return const Color(0xFF10B981);
      case 'lost': return const Color(0xFFEF4444);
      case 'nurture': return const Color(0xFF6366F1);
      default: return const Color(0xFF6B7280);
    }
  }

  Color get priorityColor {
    switch (priority) {
      case 'high': return const Color(0xFFEF4444);
      case 'medium': return const Color(0xFFF59E0B);
      case 'low': return const Color(0xFF10B981);
      default: return const Color(0xFF6B7280);
    }
  }
}

class CRMInteraction {
  final int id;
  final int leadId;
  final int userId;
  final String interactionType;
  final String? direction;
  final String? subject;
  final String? body;
  final int? durationSeconds;
  final String? outcome;
  final DateTime createdAt;

  const CRMInteraction({
    required this.id,
    required this.leadId,
    required this.userId,
    required this.interactionType,
    this.direction,
    this.subject,
    this.body,
    this.durationSeconds,
    this.outcome,
    required this.createdAt,
  });

  factory CRMInteraction.fromJson(Map<String, dynamic> json) {
    return CRMInteraction(
      id: CRMLead.parseInt(json['id']),
      leadId: CRMLead.parseInt(json['lead_id']),
      userId: CRMLead.parseInt(json['user_id']),
      interactionType: json['interaction_type']?.toString() ?? 'note',
      direction: json['direction']?.toString(),
      subject: json['subject']?.toString(),
      body: json['body']?.toString(),
      durationSeconds: json['duration_seconds'] is int ? json['duration_seconds'] as int : null,
      outcome: json['outcome']?.toString(),
      createdAt: CRMLead.parseDate(json['created_at']) ?? DateTime.now(),
    );
  }

  IconData get icon {
    switch (interactionType) {
      case 'call': return Icons.call;
      case 'sms': return Icons.sms;
      case 'email': return Icons.email;
      case 'whatsapp': return Icons.chat;
      case 'site_visit': return Icons.location_on;
      case 'meeting': return Icons.groups;
      case 'note': return Icons.note;
      default: return Icons.circle;
    }
  }
}

class CRMPipelineStage {
  final String status;
  final String label;
  final int count;
  final double totalValue;
  final int sortOrder;

  const CRMPipelineStage({
    required this.status,
    required this.label,
    this.count = 0,
    this.totalValue = 0,
    this.sortOrder = 0,
  });

  factory CRMPipelineStage.fromJson(Map<String, dynamic> json) {
    return CRMPipelineStage(
      status: json['status']?.toString() ?? '',
      label: json['label']?.toString() ?? '',
      count: CRMLead.parseInt(json['count']),
      totalValue: json['total_value'] is num ? (json['total_value'] as num).toDouble() : double.tryParse(json['total_value']?.toString() ?? '') ?? 0,
      sortOrder: CRMLead.parseInt(json['sort_order']),
    );
  }
}

class CRMTask {
  final int id;
  final int leadId;
  final int? assignedTo;
  final String taskType;
  final String title;
  final String? description;
  final String priority;
  final String status;
  final DateTime? dueDate;
  final String? dueTime;
  final DateTime createdAt;

  const CRMTask({
    required this.id,
    required this.leadId,
    this.assignedTo,
    required this.taskType,
    required this.title,
    this.description,
    this.priority = 'medium',
    this.status = 'pending',
    this.dueDate,
    this.dueTime,
    required this.createdAt,
  });

  factory CRMTask.fromJson(Map<String, dynamic> json) {
    return CRMTask(
      id: CRMLead.parseInt(json['id']),
      leadId: CRMLead.parseInt(json['lead_id']),
      assignedTo: json['assigned_to'] != null ? CRMLead.parseInt(json['assigned_to']) : null,
      taskType: json['task_type']?.toString() ?? 'follow_up',
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString(),
      priority: json['priority']?.toString() ?? 'medium',
      status: json['status']?.toString() ?? 'pending',
      dueDate: CRMLead.parseDate(json['due_date']),
      dueTime: json['due_time']?.toString(),
      createdAt: CRMLead.parseDate(json['created_at']) ?? DateTime.now(),
    );
  }

  bool get isOverdue {
    if (dueDate == null) return false;
    return dueDate!.isBefore(DateTime.now()) && status != 'completed';
  }
}

class CRMDashboardStats {
  final int totalLeads;
  final int newLeads;
  final int contactedLeads;
  final int qualifiedLeads;
  final int wonLeads;
  final int lostLeads;
  final int pendingTasks;
  final int overdueTasks;
  final double conversionRate;
  final double totalValue;
  final Map<String, int> bySource;
  final Map<String, int> byPriority;

  const CRMDashboardStats({
    this.totalLeads = 0,
    this.newLeads = 0,
    this.contactedLeads = 0,
    this.qualifiedLeads = 0,
    this.wonLeads = 0,
    this.lostLeads = 0,
    this.pendingTasks = 0,
    this.overdueTasks = 0,
    this.conversionRate = 0,
    this.totalValue = 0,
    this.bySource = const {},
    this.byPriority = const {},
  });

  factory CRMDashboardStats.fromJson(Map<String, dynamic> json) {
    return CRMDashboardStats(
      totalLeads: CRMLead.parseInt(json['total_leads']),
      newLeads: CRMLead.parseInt(json['new_leads']),
      contactedLeads: CRMLead.parseInt(json['contacted_leads']),
      qualifiedLeads: CRMLead.parseInt(json['qualified_leads']),
      wonLeads: CRMLead.parseInt(json['won_leads']),
      lostLeads: CRMLead.parseInt(json['lost_leads']),
      pendingTasks: CRMLead.parseInt(json['pending_tasks']),
      overdueTasks: CRMLead.parseInt(json['overdue_tasks']),
      conversionRate: json['conversion_rate'] is num ? (json['conversion_rate'] as num).toDouble() : double.tryParse(json['conversion_rate']?.toString() ?? '') ?? 0,
      totalValue: json['total_value'] is num ? (json['total_value'] as num).toDouble() : double.tryParse(json['total_value']?.toString() ?? '') ?? 0,
      bySource: Map<String, int>.from((json['by_source'] as Map?)?.map((k, v) => MapEntry(k.toString(), v is int ? v : int.tryParse(v.toString()) ?? 0)) ?? {}),
      byPriority: Map<String, int>.from((json['by_priority'] as Map?)?.map((k, v) => MapEntry(k.toString(), v is int ? v : int.tryParse(v.toString()) ?? 0)) ?? {}),
    );
  }
}
