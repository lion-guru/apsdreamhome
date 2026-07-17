import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/services/api_service.dart';

class LegalDocument {
  final int id;
  final String title;
  final String? documentNumber;
  final String? status;
  final String? categoryName;
  final String? entityType;
  final String? entityName;
  final String? createdAt;
  final bool isKycVerified;

  const LegalDocument({
    required this.id,
    required this.title,
    this.documentNumber,
    this.status,
    this.categoryName,
    this.entityType,
    this.entityName,
    this.createdAt,
    this.isKycVerified = false,
  });

  factory LegalDocument.fromJson(Map<String, dynamic> json) {
    return LegalDocument(
      id: json['id'] is int
          ? json['id'] as int
          : int.tryParse(json['id']?.toString() ?? '') ?? 0,
      title: json['title']?.toString() ?? '',
      documentNumber: json['document_number']?.toString(),
      status: json['status']?.toString(),
      categoryName: json['category_name']?.toString(),
      entityType: json['entity_type']?.toString(),
      entityName: json['entity_name']?.toString(),
      createdAt: json['created_at']?.toString(),
      isKycVerified:
          json['is_kyc_verified'] == 1 || json['is_kyc_verified'] == true,
    );
  }
}

final legalDocumentsProvider = FutureProvider<List<LegalDocument>>((ref) async {
  final api = ApiService();
  final response = await api.get('legal/documents');
  if (response['success'] == true && response['data'] is List) {
    return (response['data'] as List)
        .map((e) => LegalDocument.fromJson(e as Map<String, dynamic>))
        .toList();
  }
  return [];
});

class LegalDocumentsPage extends ConsumerWidget {
  const LegalDocumentsPage({super.key});

  String _statusLabel(String? s) {
    switch (s) {
      case 'draft':
        return 'Draft';
      case 'final':
        return 'Final';
      case 'signed':
        return 'Signed';
      case 'expired':
        return 'Expired';
      case 'cancelled':
        return 'Cancelled';
      case 'archived':
        return 'Archived';
      default:
        return s ?? 'Unknown';
    }
  }

  Color _statusColor(String? s) {
    switch (s) {
      case 'final':
        return Colors.green;
      case 'signed':
        return Colors.blue;
      case 'draft':
        return Colors.orange;
      case 'expired':
        return Colors.red;
      case 'cancelled':
        return Colors.grey;
      case 'archived':
        return Colors.brown;
      default:
        return Colors.grey;
    }
  }

  IconData _statusIcon(String? s) {
    switch (s) {
      case 'final':
        return Icons.check_circle;
      case 'signed':
        return Icons.edit_note;
      case 'draft':
        return Icons.edit;
      case 'expired':
        return Icons.timer_off;
      case 'cancelled':
        return Icons.cancel;
      case 'archived':
        return Icons.archive;
      default:
        return Icons.description;
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final docsAsync = ref.watch(legalDocumentsProvider);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Legal Documents'),
        centerTitle: true,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF1A237E), Color(0xFF283593)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
      ),
      body: docsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.cloud_off, size: 64, color: Colors.grey[400]),
                const SizedBox(height: 16),
                Text(
                  'Could not load documents',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: 8),
                Text(
                  e.toString(),
                  style: TextStyle(color: Colors.grey[600]),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                ElevatedButton.icon(
                  onPressed: () => ref.invalidate(legalDocumentsProvider),
                  icon: const Icon(Icons.refresh),
                  label: const Text('Retry'),
                ),
              ],
            ),
          ),
        ),
        data: (docs) {
          if (docs.isEmpty) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.description_outlined,
                    size: 80,
                    color: Colors.grey[300],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'No legal documents yet',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Documents will appear here once generated',
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                ],
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(legalDocumentsProvider),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: docs.length,
              itemBuilder: (context, index) {
                final doc = docs[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  elevation: 2,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(12),
                    onTap: () => context.push('/legal-documents/${doc.id}'),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: _statusColor(
                                doc.status,
                              ).withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(
                              _statusIcon(doc.status),
                              color: _statusColor(doc.status),
                              size: 28,
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  doc.title,
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w600,
                                    fontSize: 15,
                                  ),
                                ),
                                if (doc.documentNumber != null)
                                  Padding(
                                    padding: const EdgeInsets.only(top: 4),
                                    child: Text(
                                      doc.documentNumber!,
                                      style: TextStyle(
                                        color: Colors.grey[600],
                                        fontSize: 13,
                                      ),
                                    ),
                                  ),
                                const SizedBox(height: 8),
                                Wrap(
                                  spacing: 8,
                                  children: [
                                    _buildChip(
                                      _statusLabel(doc.status),
                                      _statusColor(doc.status),
                                    ),
                                    if (doc.categoryName != null)
                                      _buildChip(
                                        doc.categoryName!,
                                        Colors.deepPurple,
                                      ),
                                    if (doc.isKycVerified)
                                      _buildChip('KYC Verified', Colors.teal),
                                  ],
                                ),
                                if (doc.createdAt != null)
                                  Padding(
                                    padding: const EdgeInsets.only(top: 6),
                                    child: Text(
                                      DateFormat(
                                        'dd MMM yyyy',
                                      ).format(DateTime.parse(doc.createdAt!)),
                                      style: TextStyle(
                                        color: Colors.grey[500],
                                        fontSize: 12,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          Icon(Icons.chevron_right, color: Colors.grey[400]),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildChip(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.w500,
        ),
      ),
    );
  }
}
