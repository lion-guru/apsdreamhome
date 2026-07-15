import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';

final documentDetailProvider =
    FutureProvider.family<Map<String, dynamic>?, int>((ref, id) async {
      final api = ApiService();
      final response = await api.get('legal/documents/$id');
      if (response['success'] == true) return response;
      return null;
    });

class LegalDocumentDetailPage extends ConsumerWidget {
  final int documentId;
  const LegalDocumentDetailPage({super.key, required this.documentId});

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
      default:
        return Icons.description;
    }
  }

  Widget _buildChip(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailAsync = ref.watch(documentDetailProvider(documentId));
    return Scaffold(
      appBar: AppBar(
        title: const Text('Document Details'),
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
      body: detailAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.error_outline, size: 64, color: Colors.red[300]),
                const SizedBox(height: 16),
                Text(
                  'Error loading document',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: 8),
                Text(
                  e.toString(),
                  style: TextStyle(color: Colors.grey[600]),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ),
        data: (result) {
          if (result == null) {
            return const Center(child: Text('Document not found'));
          }
          final doc = result['data'] as Map<String, dynamic>? ?? {};
          final uploads = result['uploads'] as List<dynamic>? ?? [];

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeaderCard(doc, context),
                const SizedBox(height: 16),
                _buildInfoSection(doc),
                const SizedBox(height: 16),
                _buildActionButtons(context, doc),
                if (doc['content'] != null &&
                    doc['content'].toString().isNotEmpty) ...[
                  const SizedBox(height: 16),
                  _buildContentPreview(doc, context),
                ],
                if (uploads.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  _buildUploadsSection(uploads),
                ],
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildHeaderCard(Map<String, dynamic> doc, BuildContext context) {
    return Card(
      elevation: 3,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: _statusColor(
                      doc['status']?.toString(),
                    ).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    _statusIcon(doc['status']?.toString()),
                    color: _statusColor(doc['status']?.toString()),
                    size: 32,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        doc['title']?.toString() ?? 'Untitled',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      if (doc['document_number'] != null)
                        Padding(
                          padding: const EdgeInsets.only(top: 4),
                          child: Text(
                            doc['document_number'].toString(),
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 13,
                            ),
                          ),
                        ),
                      const SizedBox(height: 8),
                      _buildChip(
                        _statusLabel(doc['status']?.toString()),
                        _statusColor(doc['status']?.toString()),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            if (doc['is_kyc_verified'] == 1 || doc['is_kyc_verified'] == true)
              Padding(
                padding: const EdgeInsets.only(top: 12),
                child: Row(
                  children: [
                    Icon(Icons.verified, color: Colors.teal, size: 18),
                    const SizedBox(width: 6),
                    Text(
                      'KYC Verified',
                      style: TextStyle(
                        color: Colors.teal[700],
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoSection(Map<String, dynamic> doc) {
    final items = <MapEntry<String, String>>[];
    if (doc['category_name'] != null)
      items.add(MapEntry('Category', doc['category_name'].toString()));
    if (doc['entity_type'] != null)
      items.add(MapEntry('Entity Type', doc['entity_type'].toString()));
    if (doc['entity_name'] != null)
      items.add(MapEntry('Entity', doc['entity_name'].toString()));
    if (doc['created_at'] != null) {
      try {
        items.add(
          MapEntry(
            'Created',
            DateFormat(
              'dd MMM yyyy',
            ).format(DateTime.parse(doc['created_at'].toString())),
          ),
        );
      } catch (_) {
        items.add(MapEntry('Created', doc['created_at'].toString()));
      }
    }
    if (items.isEmpty) return const SizedBox.shrink();
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Details',
              style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16),
            ),
            const SizedBox(height: 12),
            ...items.map(
              (item) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: 110,
                      child: Text(
                        item.key,
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    Expanded(child: Text(item.value)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionButtons(BuildContext context, Map<String, dynamic> doc) {
    return Row(
      children: [
        Expanded(
          child: OutlinedButton.icon(
            onPressed: () =>
                context.push('/legal-documents/$documentId/preview'),
            icon: const Icon(Icons.visibility),
            label: const Text('Preview Full'),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildContentPreview(Map<String, dynamic> doc, BuildContext context) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Content Preview',
              style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16),
            ),
            const SizedBox(height: 8),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.grey[50],
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.grey[200]!),
              ),
              child: Text(
                doc['content'].toString().length > 500
                    ? '${doc['content'].toString().substring(0, 500)}...'
                    : doc['content'].toString(),
                style: TextStyle(
                  color: Colors.grey[800],
                  height: 1.6,
                  fontSize: 13,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildUploadsSection(List<dynamic> uploads) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Uploads',
              style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16),
            ),
            const SizedBox(height: 12),
            ...uploads.map((u) {
              final upload = u as Map<String, dynamic>;
              return ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.blue.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.upload_file, color: Colors.blue),
                ),
                title: Text(
                  upload['file_name']?.toString() ?? 'Upload',
                  style: const TextStyle(fontWeight: FontWeight.w500),
                ),
                subtitle: Text(
                  upload['upload_type']?.toString() ?? '',
                  style: TextStyle(color: Colors.grey[600], fontSize: 12),
                ),
                trailing: upload['status']?.toString() == 'verified'
                    ? const Icon(Icons.verified, color: Colors.green, size: 20)
                    : const Icon(
                        Icons.hourglass_empty,
                        color: Colors.orange,
                        size: 20,
                      ),
              );
            }),
          ],
        ),
      ),
    );
  }
}
