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
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      userId: json['user_id'] is int ? json['user_id'] : int.parse(json['user_id'].toString()),
      title: json['title'],
      documentType: json['document_type'],
      fileUrl: json['file_url'],
      status: json['status'],
      remarks: json['remarks'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
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
