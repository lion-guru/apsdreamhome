import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/services/api_service.dart';

final documentPreviewProvider =
    FutureProvider.family<Map<String, dynamic>?, int>((ref, id) async {
      final api = ApiService();
      final response = await api.get('legal/documents/$id/preview');
      if (response['success'] == true) return response;
      return null;
    });

class LegalDocumentPreviewPage extends ConsumerWidget {
  final int documentId;
  const LegalDocumentPreviewPage({super.key, required this.documentId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final previewAsync = ref.watch(documentPreviewProvider(documentId));
    String title = 'Document Preview';
    String content = '';

    void share() {
      if (content.isNotEmpty) {
        Share.share('$title\n\n$content', subject: title);
      }
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(title),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.share),
            onPressed: content.isNotEmpty ? share : null,
            tooltip: 'Share',
          ),
        ],
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
      body: previewAsync.when(
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
                  'Preview unavailable',
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
          title = result['title']?.toString() ?? 'Document';
          content = result['content']?.toString() ?? '';
          final docNumber = result['document_number']?.toString();

          return Column(
            children: [
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.grey[50],
                  border: Border(bottom: BorderSide(color: Colors.grey[200]!)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    if (docNumber != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: Text(
                          docNumber,
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 13,
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: SelectableText(
                    content,
                    style: TextStyle(
                      fontSize: 14,
                      height: 1.8,
                      color: Colors.grey[900],
                      fontFamily: 'serif',
                    ),
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
