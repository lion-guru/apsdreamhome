import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

/// Document model for local display
class AppDocument {
  final String id;
  final String name;
  final String type;
  final String? category;
  final DateTime? uploadedAt;
  final String? status;
  final String? url;

  const AppDocument({
    required this.id,
    required this.name,
    required this.type,
    this.category,
    this.uploadedAt,
    this.status,
    this.url,
  });

  factory AppDocument.fromJson(Map<String, dynamic> json) {
    return AppDocument(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString() ?? '',
      category: json['category']?.toString(),
      uploadedAt: json['uploaded_at'] != null
          ? DateTime.tryParse(json['uploaded_at'].toString())
          : null,
      status: json['status']?.toString(),
      url: json['url']?.toString(),
    );
  }
}

/// Fetches customer documents from API
final documentsProvider = FutureProvider<List<AppDocument>>((ref) async {
  final api = ref.read(apiServiceProvider);
  final response = await api.get('user/documents');
  final data = response['data'];
  if (data is List) {
    return data
        .map((json) => AppDocument.fromJson(json as Map<String, dynamic>))
        .toList();
  }
  return [];
});

class DocumentsPage extends ConsumerWidget {
  const DocumentsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final docsAsync = ref.watch(documentsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Documents'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.upload_file),
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Upload documents via the web portal'),
                ),
              );
            },
            tooltip: 'Upload Document',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(documentsProvider),
        child: docsAsync.when(
          data: (docs) {
            if (docs.isEmpty) return _buildEmptyState();
            return _buildDocumentList(context, docs);
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (err, _) => ListView(
            children: [
              const SizedBox(height: 100),
              Center(child: Text('Error: $err')),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.folder_open, size: 64, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          Text(
            'No documents yet',
            style: TextStyle(color: Colors.grey.shade600, fontSize: 18),
          ),
          const SizedBox(height: 8),
          Text(
            'Upload your KYC and booking documents here.',
            style: TextStyle(color: Colors.grey.shade500),
          ),
        ],
      ),
    );
  }

  Widget _buildDocumentList(BuildContext context, List<AppDocument> docs) {
    // Group by category
    final grouped = <String, List<AppDocument>>{};
    for (final doc in docs) {
      final cat = doc.category ?? 'Other';
      grouped.putIfAbsent(cat, () => []).add(doc);
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: grouped.length,
      itemBuilder: (context, sectionIndex) {
        final category = grouped.keys.elementAt(sectionIndex);
        final categoryDocs = grouped[category]!;

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: EdgeInsets.only(
                bottom: 8,
                top: sectionIndex == 0 ? 0 : 16,
              ),
              child: Text(
                category,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryColor,
                ),
              ),
            ),
            ...categoryDocs.map((doc) => _buildDocumentCard(doc)),
          ],
        );
      },
    );
  }

  Widget _buildDocumentCard(AppDocument doc) {
    final iconData = _getIconForType(doc.type);
    final iconColor = _getColorForType(doc.type);

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 6),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: iconColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(iconData, color: iconColor, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  doc.name,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 2),
                if (doc.uploadedAt != null)
                  Text(
                    'Uploaded: ${doc.uploadedAt!.day}/${doc.uploadedAt!.month}/${doc.uploadedAt!.year}',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  ),
              ],
            ),
          ),
          if (doc.status != null) _buildStatusChip(doc.status!),
          const SizedBox(width: 8),
          Icon(Icons.chevron_right, color: Colors.grey.shade400, size: 20),
        ],
      ),
    );
  }

  Widget _buildStatusChip(String status) {
    Color color;
    switch (status) {
      case 'verified':
        color = AppTheme.successColor;
        break;
      case 'pending':
        color = AppTheme.warningColor;
        break;
      case 'rejected':
        color = AppTheme.errorColor;
        break;
      default:
        color = AppTheme.infoColor;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        status.toUpperCase(),
        style: TextStyle(
          fontSize: 9,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
    );
  }

  IconData _getIconForType(String type) {
    switch (type) {
      case 'identity':
        return Icons.badge_outlined;
      case 'agreement':
        return Icons.description_outlined;
      case 'receipt':
        return Icons.receipt_long_outlined;
      case 'allotment':
        return Icons.home_work_outlined;
      default:
        return Icons.insert_drive_file_outlined;
    }
  }

  Color _getColorForType(String type) {
    switch (type) {
      case 'identity':
        return AppTheme.infoColor;
      case 'agreement':
        return AppTheme.primaryColor;
      case 'receipt':
        return AppTheme.successColor;
      case 'allotment':
        return AppTheme.warningColor;
      default:
        return Colors.grey;
    }
  }
}
