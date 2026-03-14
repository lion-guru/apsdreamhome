import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/document_provider.dart';
import '../../../data/models/document_model.dart';
import '../../../core/constants/app_constants.dart';
import 'package:intl/intl.dart';

class DocumentLockerPage extends ConsumerWidget {
  const DocumentLockerPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final documentsAsync = ref.watch(documentProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Digital Registry Locker'),
        backgroundColor: AppConstants.primaryColor,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.read(documentProvider.notifier).refresh(),
          ),
        ],
      ),
      body: documentsAsync.when(
        data: (documents) => documents.isEmpty
            ? _buildEmptyState()
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: documents.length,
                itemBuilder: (context, index) {
                  final doc = documents[index];
                  return _buildDocumentCard(context, doc);
                },
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text('Error: $err')),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // TODO: Implement document upload/scan flow
        },
        backgroundColor: AppConstants.accentColor,
        child: const Icon(Icons.add_a_photo, color: Colors.black),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.folder_open, size: 80, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            'No documents in locker',
            style: TextStyle(fontSize: 18, color: Colors.grey[600]),
          ),
          const SizedBox(height: 8),
          const Text('Upload your registry or receipts to keep them safe.'),
        ],
      ),
    );
  }

  Widget _buildDocumentCard(BuildContext context, Document doc) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        contentPadding: const EdgeInsets.all(12),
        leading: Container(
          width: 50,
          height: 50,
          decoration: BoxDecoration(
            color: _getStatusColor(doc.status).withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            _getDocumentIcon(doc.documentType),
            color: _getStatusColor(doc.status),
          ),
        ),
        title: Text(
          doc.title,
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text('Type: ${doc.documentType.toUpperCase()}'),
            Text('Date: ${DateFormat('dd MMM yyyy').format(doc.createdAt)}'),
            if (doc.remarks != null) ...[
              const SizedBox(height: 4),
              Text(
                'Note: ${doc.remarks}',
                style: const TextStyle(fontStyle: FontStyle.italic, fontSize: 12),
              ),
            ],
          ],
        ),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            _buildStatusChip(doc.status),
            const SizedBox(height: 4),
            const Icon(Icons.arrow_forward_ios, size: 14),
          ],
        ),
        onTap: () => _viewDocument(context, doc),
      ),
    );
  }

  Widget _buildStatusChip(String status) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: _getStatusColor(status).withOpacity(0.2),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _getStatusColor(status)),
      ),
      child: Text(
        status.toUpperCase(),
        style: TextStyle(
          color: _getStatusColor(status),
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'verified':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'rejected':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  IconData _getDocumentIcon(String type) {
    switch (type) {
      case 'registry':
        return Icons.assignment;
      case 'payment_receipt':
        return Icons.receipt_long;
      case 'id_proof':
        return Icons.badge;
      default:
        return Icons.description;
    }
  }

  void _viewDocument(BuildContext context, Document doc) {
    // Implement full document viewer or browser launch
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Opening ${doc.title}...')),
    );
  }
}
