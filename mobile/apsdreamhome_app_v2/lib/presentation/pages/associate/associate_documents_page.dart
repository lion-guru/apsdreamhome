import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:apsdreamhome_app_v2/core/services/api_service.dart';

final associateDocumentsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  final res = await api.get('/api/v2/mobile/associate/documents');
  return res;
});

class AssociateDocumentsPage extends ConsumerWidget {
  const AssociateDocumentsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final docsAsync = ref.watch(associateDocumentsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Official Document Vault', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(associateDocumentsProvider),
          ),
        ],
      ),
      body: docsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: Color(0xFFD4AF37))),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (data) {
          final docs = (data['documents'] as List<dynamic>?) ?? [];

          return RefreshIndicator(
            color: const Color(0xFFD4AF37),
            onRefresh: () async => ref.invalidate(associateDocumentsProvider),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFD4AF37).withValues(alpha: 0.2),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.folder_special, color: Color(0xFFD4AF37), size: 28),
                      ),
                      const SizedBox(width: 14),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Authorized Associate Dossier', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                            SizedBox(height: 4),
                            Text('Encrypted digital copies of your empanelment and legal accreditation certificates.', style: TextStyle(color: Colors.white70, fontSize: 12)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                const Text('Verified Credentials', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                const SizedBox(height: 12),

                ...docs.map((d) {
                  final item = d as Map<String, dynamic>;
                  final isVerified = item['verification_status']?.toString() == 'verified';

                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFAF7F0),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.picture_as_pdf, color: Colors.redAccent, size: 26),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item['document_name']?.toString() ?? 'Document',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF0B194C)),
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    Icon(
                                      isVerified ? Icons.verified : Icons.hourglass_top,
                                      size: 13,
                                      color: isVerified ? Colors.green : Colors.orange,
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      (item['verification_status']?.toString() ?? 'Pending').toUpperCase(),
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w700,
                                        color: isVerified ? Colors.green : Colors.orange,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.download, color: Color(0xFF0B194C)),
                            onPressed: () {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text('Downloading ${item['document_name']}...')),
                              );
                            },
                          ),
                        ],
                      ),
                    ),
                  );
                }),
              ],
            ),
          );
        },
      ),
    );
  }
}
