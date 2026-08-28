/// KYC Verification Result Model
class KYCVerificationResult {
  final bool success;
  final String message;
  final String? verificationId;
  final DateTime timestamp;
  final Map<String, dynamic> details;
  final Map<String, dynamic>? data;

  KYCVerificationResult({
    required this.success,
    required this.message,
    this.verificationId,
    required this.timestamp,
    required this.details,
    this.data,
  });

  factory KYCVerificationResult.fromJson(Map<String, dynamic> json) {
    return KYCVerificationResult(
      success: json['success'] as bool? ?? false,
      message: json['message'] as String? ?? 'Unknown error',
      verificationId: json['verification_id'] as String?,
      timestamp: DateTime.tryParse(json['timestamp'] as String? ?? '') ??
          DateTime.now(),
      details: json['details'] as Map<String, dynamic>? ?? {},
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'success': success,
      'message': message,
      'verification_id': verificationId,
      'timestamp': timestamp.toIso8601String(),
      'details': details,
    };
  }
}

/// KYC Status Enum
enum KYCStatus {
  pending,
  inProgress,
  verified,
  rejected,
  expired,
}

/// KYC Status Model
class KYCStatusModel {
  final KYCStatus status;
  final String? message;
  final DateTime? lastUpdated;
  final Map<String, dynamic> details;
  final String? id;
  final DateTime? createdAt;
  final DateTime? verifiedAt;
  final String? panNumber;
  final String? aadhaarNumber;
  final String? maskedAadhaar;
  final bool panVerified;
  final bool aadhaarVerified;
  final bool documentsUploaded;
  final bool faceMatched;
  final bool videoCompleted;

  KYCStatusModel({
    required this.status,
    this.message,
    this.lastUpdated,
    required this.details,
    this.id,
    this.createdAt,
    this.verifiedAt,
    this.panNumber,
    this.aadhaarNumber,
    this.maskedAadhaar,
    this.panVerified = false,
    this.aadhaarVerified = false,
    this.documentsUploaded = false,
    this.faceMatched = false,
    this.videoCompleted = false,
  });

  bool get isCompleted => status == KYCStatus.verified;

  factory KYCStatusModel.fromJson(Map<String, dynamic> json) {
    return KYCStatusModel(
      status: _parseStatus(json['status'] as String?),
      message: json['message'] as String?,
      lastUpdated: json['last_updated'] != null
          ? DateTime.tryParse(json['last_updated'] as String)
          : null,
      details: json['details'] as Map<String, dynamic>? ?? {},
    );
  }

  static KYCStatus _parseStatus(String? status) {
    switch (status?.toLowerCase()) {
      case 'pending':
        return KYCStatus.pending;
      case 'in_progress':
        return KYCStatus.inProgress;
      case 'verified':
        return KYCStatus.verified;
      case 'rejected':
        return KYCStatus.rejected;
      case 'expired':
        return KYCStatus.expired;
      default:
        return KYCStatus.pending;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'status': status.name,
      'message': message,
      'last_updated': lastUpdated?.toIso8601String(),
      'details': details,
    };
  }
}
