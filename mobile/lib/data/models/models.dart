import 'package:uuid/uuid.dart';

// User model
part 'user_model.g.dart';

// Property model  
part 'property_model.g.dart';

// Lead model
part 'lead_model.g.dart';

// Commission model
part 'commission_model.g.dart';

// Sync queue model
class SyncQueueItem {
  final String id;
  final String entityType;
  final String entityId;
  final String action; // create, update, delete
  final Map<String, dynamic> data;
  final String status; // pending, syncing, synced, failed
  final int retryCount;
  final String createdAt;
  final String updatedAt;
  
  SyncQueueItem({
    required this.id,
    required this.entityType,
    required this.entityId,
    required this.action,
    required this.data,
    required this.status,
    required this.retryCount,
    required this.createdAt,
    required this.updatedAt,
  });
  
  factory SyncQueueItem.fromJson(Map<String, dynamic> json) {
    return SyncQueueItem(
      id: json['id'] as String,
      entityType: json['entity_type'] as String,
      entityId: json['entity_id'] as String,
      action: json['action'] as String,
      data: json['data'] as Map<String, dynamic>,
      status: json['status'] as String,
      retryCount: json['retry_count'] as int,
      createdAt: json['created_at'] as String,
      updatedAt: json['updated_at'] as String,
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'entity_type': entityType,
      'entity_id': entityId,
      'action': action,
      'data': data,
      'status': status,
      'retry_count': retryCount,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
  
  SyncQueueItem copyWith({
    String? id,
    String? entityType,
    String? entityId,
    String? action,
    Map<String, dynamic>? data,
    String? status,
    int? retryCount,
    String? createdAt,
    String? updatedAt,
  }) {
    return SyncQueueItem(
      id: id ?? this.id,
      entityType: entityType ?? this.entityType,
      entityId: entityId ?? this.entityId,
      action: action ?? this.action,
      data: data ?? this.data,
      status: status ?? this.status,
      retryCount: retryCount ?? this.retryCount,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}

// API Response model
class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final List<String>? errors;
  
  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    this.errors,
  });
  
  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic)? fromJsonT,
  ) {
    return ApiResponse<T>(
      success: json['success'] as bool? ?? false,
      message: json['message'] as String? ?? '',
      data: json['data'] != null && fromJsonT != null
          ? fromJsonT(json['data'])
          : json['data'],
      errors: (json['errors'] as List<dynamic>?)?.cast<String>(),
    );
  }
  
  Map<String, dynamic> toJson(dynamic Function(T)? toJsonT) {
    return {
      'success': success,
      'message': message,
      'data': data != null && toJsonT != null ? toJsonT(data as T) : data,
      'errors': errors,
    };
  }
}

// Pagination model
class PaginatedResponse<T> {
  final List<T> data;
  final int currentPage;
  final int totalPages;
  final int totalItems;
  final int itemsPerPage;
  
  PaginatedResponse({
    required this.data,
    required this.currentPage,
    required this.totalPages,
    required this.totalItems,
    required this.itemsPerPage,
  });
  
  factory PaginatedResponse.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic) fromJsonT,
  ) {
    return PaginatedResponse<T>(
      data: (json['data'] as List<dynamic>)
          .map((item) => fromJsonT(item))
          .toList(),
      currentPage: json['current_page'] as int? ?? 1,
      totalPages: json['total_pages'] as int? ?? 1,
      totalItems: json['total_items'] as int? ?? 0,
      itemsPerPage: json['items_per_page'] as int? ?? 10,
    );
  }
}

// Utility extensions
extension StringExtension on String {
  String capitalize() {
    return "${this[0].toUpperCase()}${substring(1)}";
  }
  
  String get initials {
    final parts = split(' ');
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    } else if (parts.isNotEmpty) {
      return parts[0][0].toUpperCase();
    }
    return '';
  }
}

extension DoubleExtension on double {
  String formatCurrency() {
    if (this >= 10000000) {
      return '₹${(this / 10000000).toStringAsFixed(2)} Cr';
    } else if (this >= 100000) {
      return '₹${(this / 100000).toStringAsFixed(1)} L';
    } else if (this >= 1000) {
      return '₹${(this / 1000).toStringAsFixed(0)} K';
    } else {
      return '₹${toStringAsFixed(0)}';
    }
  }
}

extension DateTimeExtension on DateTime {
  String formatRelative() {
    final now = DateTime.now();
    final difference = now.difference(this);
    
    if (difference.inDays > 0) {
      return '${difference.inDays} day${difference.inDays == 1 ? '' : 's'} ago';
    } else if (difference.inHours > 0) {
      return '${difference.inHours} hour${difference.inHours == 1 ? '' : 's'} ago';
    } else if (difference.inMinutes > 0) {
      return '${difference.inMinutes} minute${difference.inMinutes == 1 ? '' : 's'} ago';
    } else {
      return 'Just now';
    }
  }
  
  String formatDate() {
    return '$day/${month.toString().padLeft(2, '0')}/$year';
  }
  
  String formatDateTime() {
    return '$day/${month.toString().padLeft(2, '0')}/$year ${hour.toString().padLeft(2, '0')}:${minute.toString().padLeft(2, '0')}';
  }
}
