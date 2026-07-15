import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../providers/document_provider.dart';
import '../../../data/models/document_model.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/app_constants.dart';
import 'package:intl/intl.dart';

class DocumentLockerPage extends ConsumerWidget {
  const DocumentLockerPage({super.key});

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
        onPressed: () => _showUploadOptions(context, ref),
        backgroundColor: AppConstants.accentColor,
        child: const Icon(Icons.add_a_photo, color: Colors.black),
      ),
    );
  }

  void _showUploadOptions(BuildContext context, WidgetRef ref) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 20),
              const Text(
                'Upload Document',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              const Text(
                'Choose how to add your document',
                style: TextStyle(color: Colors.grey, fontSize: 14),
              ),
              const SizedBox(height: 20),
              ListTile(
                leading: const CircleAvatar(child: Icon(Icons.camera_alt)),
                title: const Text('Take Photo'),
                subtitle: const Text('Capture document using camera'),
                onTap: () {
                  Navigator.pop(ctx);
                  _pickAndUpload(context, ref, ImageSource.camera);
                },
              ),
              ListTile(
                leading: const CircleAvatar(child: Icon(Icons.photo_library)),
                title: const Text('Choose from Gallery'),
                subtitle: const Text('Select existing document image'),
                onTap: () {
                  Navigator.pop(ctx);
                  _pickAndUpload(context, ref, ImageSource.gallery);
                },
              ),
              ListTile(
                leading: CircleAvatar(
                  backgroundColor: Colors.grey[200],
                  child: const Icon(Icons.description, color: Colors.grey),
                ),
                title: const Text('Document Type Info'),
                subtitle: const Text(
                  'Supports: registry, payment_receipt, id_proof',
                ),
                enabled: false,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _pickAndUpload(
    BuildContext context,
    WidgetRef ref,
    ImageSource source,
  ) async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: source, maxWidth: 2048);
    if (picked == null) return;

    ScaffoldMessenger.of(
      context,
    ).showSnackBar(const SnackBar(content: Text('Uploading document...')));

    try {
      final type = await _selectDocumentType(context);
      if (type == null) return;

      final api = ApiService();
      await api.uploadDocument(picked.path, type);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Document uploaded successfully!'),
            backgroundColor: Colors.green,
          ),
        );
        ref.read(documentProvider.notifier).refresh();
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Upload failed: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<String?> _selectDocumentType(BuildContext context) {
    return showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Document Type'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              title: const Text('Registry'),
              leading: const Icon(Icons.assignment),
              onTap: () => Navigator.pop(ctx, 'registry'),
            ),
            ListTile(
              title: const Text('Payment Receipt'),
              leading: const Icon(Icons.receipt_long),
              onTap: () => Navigator.pop(ctx, 'payment_receipt'),
            ),
            ListTile(
              title: const Text('ID Proof'),
              leading: const Icon(Icons.badge),
              onTap: () => Navigator.pop(ctx, 'id_proof'),
            ),
            ListTile(
              title: const Text('Other'),
              leading: const Icon(Icons.description),
              onTap: () => Navigator.pop(ctx, 'other'),
            ),
          ],
        ),
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
            color: _getStatusColor(doc.status).withValues(alpha: 0.1),
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
                style: const TextStyle(
                  fontStyle: FontStyle.italic,
                  fontSize: 12,
                ),
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
        color: _getStatusColor(status).withValues(alpha: 0.2),
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
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Row(
          children: [
            Icon(
              _getDocumentIcon(doc.documentType),
              color: _getStatusColor(doc.status),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(doc.title, style: const TextStyle(fontSize: 18)),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _detailRow('Type', doc.documentType.toUpperCase()),
            _detailRow('Status', doc.status.toUpperCase()),
            _detailRow('Date', DateFormat('dd MMM yyyy').format(doc.createdAt)),
            _detailRow(
              'Updated',
              DateFormat('dd MMM yyyy').format(doc.updatedAt),
            ),
            if (doc.remarks != null) _detailRow('Notes', doc.remarks!),
            if (doc.fileUrl.isNotEmpty) ...[
              const Divider(height: 24),
              const Text(
                'File URL:',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
              ),
              const SizedBox(height: 4),
              Text(
                doc.fileUrl,
                style: const TextStyle(fontSize: 11, color: Colors.blue),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Close'),
          ),
          if (doc.fileUrl.isNotEmpty)
            ElevatedButton.icon(
              icon: const Icon(Icons.open_in_browser, size: 18),
              label: const Text('Open'),
              onPressed: () async {
                Navigator.pop(ctx);
                final uri = Uri.tryParse(doc.fileUrl);
                if (uri != null && await canLaunchUrl(uri)) {
                  await launchUrl(uri, mode: LaunchMode.externalApplication);
                } else {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Could not open: ${doc.fileUrl}')),
                    );
                  }
                }
              },
            ),
        ],
      ),
    );
  }

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 80,
            child: Text(
              label,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.grey,
                fontSize: 13,
              ),
            ),
          ),
          Expanded(child: Text(value, style: const TextStyle(fontSize: 13))),
        ],
      ),
    );
  }
}
