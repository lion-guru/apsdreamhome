/// KYC Models - Complete KYC verification data structures
library;

/// KYC Verification Result - API response wrapper
class KYCVerificationResult {
  final bool success;
  final String message;
  final Map<String, dynamic>? data;
  final String? errorCode;
  final DateTime? timestamp;

  KYCVerificationResult({
    required this.success,
    required this.message,
    this.data,
    this.errorCode,
    this.timestamp,
  });

  factory KYCVerificationResult.fromJson(Map<String, dynamic> json) {
    return KYCVerificationResult(
      success: json['success'] as bool? ?? false,
      message: json['message'] as String? ?? '',
      data: json['data'] as Map<String, dynamic>?,
      errorCode: json['error_code'] as String?,
      timestamp: json['timestamp'] != null
          ? DateTime.tryParse(json['timestamp'] as String)
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'success': success,
      'message': message,
      'data': data,
      'error_code': errorCode,
      'timestamp': timestamp?.toIso8601String(),
    };
  }
}

/// KYC Status - Complete KYC verification status
class KYCStatus {
  final String id;
  final String status;
  final DateTime createdAt;
  final DateTime? verifiedAt;
  final String? panNumber;
  final String? aadhaarNumber;
  final String? maskedAadhaar;
  final bool panVerified;
  final bool aadhaarVerified;
  final bool documentsUploaded;
  final bool faceMatched;
  final bool videoCompleted;
  final bool isCompleted;
  final Map<String, dynamic>? metadata;

  KYCStatus({
    required this.id,
    required this.status,
    required this.createdAt,
    this.verifiedAt,
    this.panNumber,
    this.aadhaarNumber,
    this.maskedAadhaar,
    this.panVerified = false,
    this.aadhaarVerified = false,
    this.documentsUploaded = false,
    this.faceMatched = false,
    this.videoCompleted = false,
    this.metadata,
  }) : isCompleted = panVerified && 
            aadhaarVerified && 
            documentsUploaded && 
            faceMatched && 
            videoCompleted;

  factory KYCStatus.fromJson(Map<String, dynamic> json) {
    return KYCStatus(
      id: json['id'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : DateTime.now(),
      verifiedAt: json['verified_at'] != null
          ? DateTime.tryParse(json['verified_at'] as String)
          : null,
      panNumber: json['pan_number'] as String?,
      aadhaarNumber: json['aadhaar_number'] as String?,
      maskedAadhaar: json['masked_aadhaar'] as String?,
      panVerified: json['pan_verified'] as bool? ?? false,
      aadhaarVerified: json['aadhaar_verified'] as bool? ?? false,
      documentsUploaded: json['documents_uploaded'] as bool? ?? false,
      faceMatched: json['face_matched'] as bool? ?? false,
      videoCompleted: json['video_completed'] as bool? ?? false,
      metadata: json['metadata'] as Map<String, dynamic>?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'status': status,
      'created_at': createdAt.toIso8601String(),
      'verified_at': verifiedAt?.toIso8601String(),
      'pan_number': panNumber,
      'aadhaar_number': aadhaarNumber,
      'masked_aadhaar': maskedAadhaar,
      'pan_verified': panVerified,
      'aadhaar_verified': aadhaarVerified,
      'documents_uploaded': documentsUploaded,
      'face_matched': faceMatched,
      'video_completed': videoCompleted,
      'metadata': metadata,
    };
  }

  /// Get completion percentage
  double get completionPercentage {
    int completedSteps = 0;
    if (panVerified) completedSteps++;
    if (aadhaarVerified) completedSteps++;
    if (documentsUploaded) completedSteps++;
    if (faceMatched) completedSteps++;
    if (videoCompleted) completedSteps++;
    
    return completedSteps / 5.0;
  }

  /// Get next pending step
  String? get nextPendingStep {
    if (!panVerified) return 'PAN Verification';
    if (!aadhaarVerified) return 'Aadhaar Verification';
    if (!documentsUploaded) return 'Document Upload';
    if (!faceMatched) return 'Face Matching';
    if (!videoCompleted) return 'Video KYC';
    return null; // All completed
  }
}

/// KYC Document Type
enum KYCDocumentType {
  panCard,
  aadhaarCard,
  selfie,
  video,
  addressProof,
}

/// KYC Document - Uploaded document info
class KYCDocument {
  final String id;
  final KYCDocumentType type;
  final String fileName;
  final String filePath;
  final int fileSize;
  final String mimeType;
  final DateTime uploadedAt;
  final bool isVerified;
  final String? verificationStatus;
  final Map<String, dynamic>? metadata;

  KYCDocument({
    required this.id,
    required this.type,
    required this.fileName,
    required this.filePath,
    required this.fileSize,
    required this.mimeType,
    required this.uploadedAt,
    this.isVerified = false,
    this.verificationStatus,
    this.metadata,
  });

  factory KYCDocument.fromJson(Map<String, dynamic> json) {
    return KYCDocument(
      id: json['id'] as String? ?? '',
      type: _parseDocumentType(json['type'] as String? ?? ''),
      fileName: json['file_name'] as String? ?? '',
      filePath: json['file_path'] as String? ?? '',
      fileSize: json['file_size'] as int? ?? 0,
      mimeType: json['mime_type'] as String? ?? '',
      uploadedAt: json['uploaded_at'] != null
          ? DateTime.parse(json['uploaded_at'] as String)
          : DateTime.now(),
      isVerified: json['is_verified'] as bool? ?? false,
      verificationStatus: json['verification_status'] as String?,
      metadata: json['metadata'] as Map<String, dynamic>?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type.name,
      'file_name': fileName,
      'file_path': filePath,
      'file_size': fileSize,
      'mime_type': mimeType,
      'uploaded_at': uploadedAt.toIso8601String(),
      'is_verified': isVerified,
      'verification_status': verificationStatus,
      'metadata': metadata,
    };
  }

  static KYCDocumentType _parseDocumentType(String type) {
    switch (type.toLowerCase()) {
      case 'pan_card':
        return KYCDocumentType.panCard;
      case 'aadhaar_card':
        return KYCDocumentType.aadhaarCard;
      case 'selfie':
        return KYCDocumentType.selfie;
      case 'video':
        return KYCDocumentType.video;
      case 'address_proof':
        return KYCDocumentType.addressProof;
      default:
        return KYCDocumentType.panCard;
    }
  }

  /// Get formatted file size
  String get formattedFileSize {
    if (fileSize < 1024) return '$fileSize B';
    if (fileSize < 1024 * 1024) return '${(fileSize / 1024).toStringAsFixed(1)} KB';
    return '${(fileSize / (1024 * 1024)).toStringAsFixed(1)} MB';
  }
}

/// KYC Verification Request - API request payload
class KYCVerificationRequest {
  final String type; // 'pan' or 'aadhaar'
  final String identifier; // PAN number or Aadhaar number
  final String? name; // Name for PAN verification
  final Map<String, dynamic>? documents;

  KYCVerificationRequest({
    required this.type,
    required this.identifier,
    this.name,
    this.documents,
  });

  Map<String, dynamic> toJson() {
    return {
      'type': type,
      'identifier': identifier,
      'name': name,
      'documents': documents,
    };
  }
}

/// KYC Timeline Event
class KYCTimelineEvent {
  final String id;
  final String title;
  final String description;
  final DateTime timestamp;
  final String type; // 'info', 'success', 'error', 'warning'
  final Map<String, dynamic>? metadata;

  KYCTimelineEvent({
    required this.id,
    required this.title,
    required this.description,
    required this.timestamp,
    required this.type,
    this.metadata,
  });

  factory KYCTimelineEvent.fromJson(Map<String, dynamic> json) {
    return KYCTimelineEvent(
      id: json['id'] as String? ?? '',
      title: json['title'] as String? ?? '',
      description: json['description'] as String? ?? '',
      timestamp: json['timestamp'] != null
          ? DateTime.parse(json['timestamp'] as String)
          : DateTime.now(),
      type: json['type'] as String? ?? 'info',
      metadata: json['metadata'] as Map<String, dynamic>?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'timestamp': timestamp.toIso8601String(),
      'type': type,
      'metadata': metadata,
    };
  }
}
