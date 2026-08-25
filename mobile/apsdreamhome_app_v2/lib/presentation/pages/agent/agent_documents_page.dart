import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Agent Documents Page - View and manage property documents
class AgentDocumentsPage extends ConsumerWidget {
  const AgentDocumentsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final userAsync = ref.watch(currentUserDataProvider);

    return Scaffold(
      body: userAsync.when(
        data: (user) {
          if (user == null) {
            return AppWidgets.errorWidget(
              message: 'User not found',
              onRetry: () => ref.refresh(currentUserDataProvider),
            );
          }
          return _buildBody(context, ref, user);
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(currentUserDataProvider),
        ),
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref, dynamic user) {
    final userId = (user.id ?? user['id'] ?? 0) as int;
    final documentsAsync = ref.watch(_agentDocumentsProvider(userId));

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentDocumentsProvider(userId));
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'My Documents',
              subtitle: 'View and manage your property documents',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(
            child: documentsAsync.when(
              data: (documents) {
                if (documents.isEmpty) {
                  return _buildEmptyState(context);
                }
                return _buildDocumentsList(context, documents);
              },
              loading: () => const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(color: AppTheme.primaryColor),
                ),
              ),
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () => ref.invalidate(_agentDocumentsProvider(userId)),
              ),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 32)),
        ],
      ),
    );
  }

  Widget _buildAppBar(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: SafeArea(
        child: Column(
          children: [
            Row(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(
                    Icons.description_rounded,
                    color: Colors.white,
                    size: 30,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Documents',
                        style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      Text(
                        'View and manage your property documents',
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.white.withValues(alpha: 0.8),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
],
        ),
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    AppTheme.primaryColor.withValues(alpha: 0.2),
                    AppTheme.secondaryColor.withValues(alpha: 0.2),
                  ],
                ),
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(
                Icons.folder_open_rounded,
                size: 50,
                color: Colors.grey,
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'No Documents Yet',
              style: AppTheme.headlineMedium.copyWith(
                color: Colors.grey[800],
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Your property documents will appear here',
              style: TextStyle(color: Colors.grey[600], fontSize: 16),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),
            ElevatedButton.icon(
              onPressed: () => context.go('/agent/documents/upload'),
              icon: const Icon(Icons.upload_rounded),
              label: const Text('Upload Document'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentsList(BuildContext context, List<dynamic> documents) {
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: documents.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final doc = documents[index] as Map<String, dynamic>;
        return _buildDocumentCard(context, doc);
      },
    );
  }

  Widget _buildDocumentCard(BuildContext context, Map<String, dynamic> doc) {
    final title = doc['title']?.toString() ?? 'Untitled Document';
    final type = doc['document_type']?.toString() ?? 'Document';
    final status = doc['status']?.toString() ?? 'draft';
    final createdAt = doc['created_at']?.toString() ?? '';
    final fileSize = doc['file_size']?.toString() ?? '';

    IconData typeIcon;
    Color typeColor;
    switch (type.toLowerCase()) {
      case 'contract':
        typeIcon = Icons.description_rounded;
        typeColor = AppTheme.primaryColor;
        break;
      case 'deed':
        typeIcon = Icons.assignment_rounded;
        typeColor = AppTheme.successColor;
        break;
      case 'agreement':
        typeIcon = Icons.assignment_ind_rounded;
        typeColor = AppTheme.accentColor;
        break;
      case 'receipt':
        typeIcon = Icons.receipt_long_rounded;
        typeColor = AppTheme.warningColor;
        break;
      default:
        typeIcon = Icons.insert_drive_file_rounded;
        typeColor = AppTheme.infoColor;
    }

    Color statusColor;
    String statusLabel;
    switch (status.toLowerCase()) {
      case 'active':
        statusColor = AppTheme.successColor;
        statusLabel = 'Active';
        break;
      case 'pending':
        statusColor = AppTheme.warningColor;
        statusLabel = 'Pending';
        break;
      case 'expired':
        statusColor = Colors.red;
        statusLabel = 'Expired';
        break;
      case 'draft':
        statusColor = AppTheme.infoColor;
        statusLabel = 'Draft';
        break;
      default:
        statusColor = Colors.grey;
        statusLabel = status.toUpperCase();
    }

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: typeColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(typeIcon, color: typeColor, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        color: Colors.black,
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      type,
                      style: TextStyle(
                        color: typeColor,
                        fontWeight: FontWeight.w600,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  statusLabel,
                  style: TextStyle(
                    color: statusColor,
                    fontWeight: FontWeight.w600,
                    fontSize: 11,
                  ),
                ),
              ),
            ],
          ),
          if (createdAt.isNotEmpty) ...[
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(Icons.calendar_today_rounded, size: 14, color: Colors.grey[600]),
                const SizedBox(width: 6),
                Text(
                  'Created: ${_formatDate(createdAt)}',
                  style: TextStyle(color: Colors.grey[600], fontSize: 12),
                ),
              ],
            ),
          ],
          if (fileSize.isNotEmpty) ...[
            const SizedBox(height: 4),
            Row(
              children: [
                Icon(Icons.data_object_rounded, size: 14, color: Colors.grey[600]),
                const SizedBox(width: 6),
                Text(
                  'Size: $_formatFileSize(fileSize)',
                  style: TextStyle(color: Colors.grey[600], fontSize: 12),
                ),
              ],
            ),
          ],
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: () => _viewDocument(context, doc),
                    icon: const Icon(Icons.visibility_rounded, size: 16),
                    label: const Text('View'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.primaryColor,
                      side: BorderSide(color: AppTheme.primaryColor.withValues(alpha: 0.5)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: () => _downloadDocument(context, doc),
                    icon: const Icon(Icons.download_rounded, size: 16),
                    label: const Text('Download'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.primaryColor,
                      side: BorderSide(color: AppTheme.primaryColor.withValues(alpha: 0.5)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: () => _shareDocument(context, doc),
                    icon: const Icon(Icons.share_rounded, size: 16),
                    label: const Text('Share'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.infoColor,
                      side: BorderSide(color: AppTheme.infoColor.withValues(alpha: 0.5)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPlaceholder() {
    return Container(
      width: 80,
      height: 60,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTheme.primaryColor.withValues(alpha: 0.2),
            AppTheme.secondaryColor.withValues(alpha: 0.2),
          ],
        ),
        borderRadius: BorderRadius.circular(8),
      ),
      child: const Icon(Icons.description_rounded, size: 32, color: Colors.grey),
    );
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day}/${date.month}/${date.year}';
    } catch (_) {
      return dateStr;
    }
  }

  String _formatFileSize(String bytesStr) {
    final bytes = int.tryParse(bytesStr) ?? 0;
    if (bytes >= 1024 * 1024) {
      return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
    } else if (bytes >= 1024) {
      return '${(bytes / 1024).toStringAsFixed(1)} KB';
    }
    return '$bytes B';
  }

  void _viewDocument(BuildContext context, Map<String, dynamic> doc) {
    final docId = doc['id']?.toString() ?? '';
    context.push('/agent/documents/$docId');
  }

  void _downloadDocument(BuildContext context, Map<String, dynamic> doc) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Download started'),
        backgroundColor: AppTheme.successColor,
      ),
    );
  }

  void _shareDocument(BuildContext context, Map<String, dynamic> doc) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Share functionality coming soon'),
        backgroundColor: AppTheme.infoColor,
      ),
    );
  }
}

// Provider
final _agentDocumentsProvider = FutureProvider.family<List<dynamic>, int>((ref, userId) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final response = await api.get('${AppConstants.apiVersion}/agent/documents');
    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final docs = (data is List ? data : data['documents'] ?? []) as List;
      return List<Map<String, dynamic>>.from(docs);
    }
  } catch (_) {}
  // Mock data fallback
  return [
    {
      'id': 1,
      'title': 'Sale Deed - Plot A-101',
      'document_type': 'deed',
      'status': 'active',
      'created_at': '2024-01-15',
      'file_size': '2048576',
    },
    {
      'id': 2,
      'title': 'Sale Agreement - Plot B-205',
      'document_type': 'agreement',
      'status': 'pending',
      'created_at': '2024-02-20',
      'file_size': '1024000',
    },
    {
      'id': 3,
      'title': 'Payment Receipt - Booking #12345',
      'document_type': 'receipt',
      'status': 'active',
      'created_at': '2024-03-10',
      'file_size': '512000',
    },
    {
      'id': 4,
      'title': 'Construction Agreement - Plot C-303',
      'document_type': 'contract',
      'status': 'draft',
      'created_at': '2024-03-15',
      'file_size': '3072000',
    },
  ];
});

// Helper Classes
class _StatItem {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  const _StatItem(this.label, this.value, this.icon, this.color);
}

class _FunnelStage {
  final String title;
  final String count;
  final IconData icon;
  final Color color;
  final double progress;
  const _FunnelStage(this.title, this.count, this.icon, this.color, this.progress);
}

class _SourceStat {
  final String name;
  final String count;
  final String rate;
  final IconData icon;
  final Color color;
  const _SourceStat(this.name, this.count, this.rate, this.icon, this.color);
}

class _MonthData {
  final String label;
  final int leads;
  final int contacted;
  final int converted;
  const _MonthData(this.label, this.leads, this.contacted, this.converted);
}