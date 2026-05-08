class Document {
  final int id;
  final int userId;
  final String title;
  final String documentType;
  final String fileUrl;
  final String status;
  final String? remarks;
  final DateTime createdAt;
  final DateTime updatedAt;

  Document({
    required this.id,
    required this.userId,
    required this.title,
    required this.documentType,
    required this.fileUrl,
    required this.status,
    this.remarks,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Document.fromJson(Map<String, dynamic> json) {
    return Document(
      id: _parseInt(json['id']),
      userId: _parseInt(json['user_id']),
      title: json['title'] as String? ?? '',
      documentType: json['document_type'] as String? ?? '',
      fileUrl: json['file_url'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      remarks: json['remarks'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'title': title,
      'document_type': documentType,
      'file_url': fileUrl,
      'status': status,
      'remarks': remarks,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}
