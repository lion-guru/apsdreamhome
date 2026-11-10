import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';

class DocumentEsignDetailPage extends StatefulWidget {
  final int documentId;
  final String? initialTitle;

  const DocumentEsignDetailPage({
    required this.documentId,
    this.initialTitle,
    super.key,
  });

  @override
  State<DocumentEsignDetailPage> createState() => _DocumentEsignDetailPageState();
}

class _DocumentEsignDetailPageState extends State<DocumentEsignDetailPage> {
  final ApiService _apiService = ApiService();
  final TextEditingController _signatureController = TextEditingController();
  bool _isLoading = true;
  bool _isSigning = false;
  Map<String, dynamic>? _document;
  String _title = '';
  String _content = '';
  String _signatureData = '';
  bool _isSigned = false;
  bool _isDownloading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _title = widget.initialTitle ?? 'Document';
    _loadDocument();
  }

  @override
  void dispose() {
    _signatureController.dispose();
    super.dispose();
  }

  Future<void> _loadDocument() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.request(
        method: 'GET',
        endpoint: 'document-esign/${widget.documentId}',
      );

      final dynamic data = response['data'];
      setState(() {
        _document = data is Map<String, dynamic>
            ? data
            : (data != null ? Map<String, dynamic>.from(data as Map) : {});
        _title = data['title']?.toString() ?? widget.initialTitle ?? 'Document';
        _content = data['content']?.toString() ?? '';
        _signatureData = data['signature_data']?.toString() ?? '';
        final dynamic statusVal = data['status'] ?? '';
        _isSigned = statusVal is String && statusVal == 'signed';
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Failed to load document details';
        _isLoading = false;
        _title = widget.initialTitle ?? 'Document ${widget.documentId}';
        _content = 'Unable to load document content. Please try again later.';
        _isSigned = false;
      });
    }
  }

  Future<void> _signDocument() async {
    final signatureText = _signatureController.text.trim();
    if (signatureText.isEmpty && _signatureData.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter your name to sign')),
      );
      return;
    }

    setState(() => _isSigning = true);
    try {
      await _apiService.request(
        method: 'POST',
        endpoint: 'document-esign/sign/${widget.documentId}',
        data: {'signature_data': signatureText.isNotEmpty ? signatureText : _signatureData},
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Document signed successfully')),
        );
        GoRouter.of(context).pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isSigning = false);
    }
  }

  /// Build a signed PDF of the e-sign document and hand it to the platform
  /// print/share sheet. Real PDF (via `pdf` package) — no stub.
  Future<void> _downloadSignedDocument() async {
    setState(() => _isDownloading = true);
    try {
      final doc = _document ?? const <String, dynamic>{};
      final title = _title.trim().isEmpty ? 'Document' : _title.trim();
      final content = _content;
      final signature = _signatureData.trim();
      final signedAt = (doc['signed_at'] ?? '').toString();
      final verificationCode = (doc['verification_code'] ?? '').toString();

      final docPdf = pw.Document();
      docPdf.addPage(
        pw.MultiPage(
          pageFormat: PdfPageFormat.a4,
          margin: const pw.EdgeInsets.all(32),
          build: (context) => [
            pw.Header(
              level: 0,
              child: pw.Text(
                'APS Dream Home',
                style: pw.TextStyle(
                  fontSize: 22,
                  fontWeight: pw.FontWeight.bold,
                  color: PdfColors.blueGrey900,
                ),
              ),
            ),
            pw.SizedBox(height: 4),
            pw.Divider(color: PdfColors.blueGrey200),
            pw.SizedBox(height: 12),
            pw.Text(
              title,
              style: pw.TextStyle(
                fontSize: 16,
                fontWeight: pw.FontWeight.bold,
              ),
            ),
            pw.SizedBox(height: 10),
            pw.Text(
              content.isEmpty ? '(No content recorded)' : content,
              style: const pw.TextStyle(fontSize: 11, lineSpacing: 3),
            ),
            pw.SizedBox(height: 24),
            pw.Divider(color: PdfColors.blueGrey200),
            pw.SizedBox(height: 10),
            _metaRow('Document Type',
                (doc['document_type'] ?? 'transaction').toString()),
            _metaRow('Status', 'SIGNED'),
            if (signedAt.isNotEmpty) _metaRow('Signed At', signedAt),
            if (verificationCode.isNotEmpty)
              _metaRow('Verification Code', verificationCode),
            pw.SizedBox(height: 18),
            pw.Text(
              'Authorised Signature',
              style: pw.TextStyle(
                fontSize: 10,
                fontWeight: pw.FontWeight.bold,
                color: PdfColors.blueGrey600,
              ),
            ),
            pw.SizedBox(height: 4),
            pw.Text(
              signature.isEmpty ? '(No signature recorded)' : signature,
              style: pw.TextStyle(
                fontSize: 18,
                fontStyle: pw.FontStyle.italic,
                color: PdfColors.blueGrey900,
              ),
            ),
            pw.SizedBox(height: 24),
            pw.Text(
              'This is a computer-generated document produced by APS Dream Home. '
              'The verification code above can be used to validate this record.',
              style: const pw.TextStyle(
                fontSize: 8,
                color: PdfColors.blueGrey500,
              ),
            ),
          ],
        ),
      );

      final bytes = await docPdf.save();
      await Printing.layoutPdf(
        onLayout: (_) async => bytes,
        name: '${title.replaceAll(RegExp(r'[^\w\- ]'), '')}.pdf',
      );
    } catch (e) {
      AppLogger.error('Error generating signed PDF', e);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Could not prepare the PDF: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isDownloading = false);
    }
  }

  pw.Widget _metaRow(String label, String value) {
    return pw.Padding(
      padding: const pw.EdgeInsets.symmetric(vertical: 2),
      child: pw.Row(
        crossAxisAlignment: pw.CrossAxisAlignment.start,
        children: [
          pw.SizedBox(
            width: 120,
            child: pw.Text(
              label,
              style: pw.TextStyle(
                fontSize: 10,
                fontWeight: pw.FontWeight.bold,
                color: PdfColors.blueGrey600,
              ),
            ),
          ),
          pw.Expanded(
            child: pw.Text(value, style: const pw.TextStyle(fontSize: 10)),
          ),
        ],
      ),
    );
  }

  Widget _buildDocumentDetail() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_document == null && _errorMessage != null) {
      return _buildErrorState();
    }

    final status = _document?['status'] ?? (_isSigned ? 'signed' : 'pending');
    final bool signed = status is String && status == 'signed';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: signed ? Colors.green.shade50 : Colors.orange.shade50,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: signed ? Colors.green.shade200 : Colors.orange.shade200,
              ),
            ),
            child: Row(
              children: [
                Icon(
                  signed ? Icons.check_circle : Icons.remove_circle,
                  color: signed ? Colors.green : Colors.orange,
                  size: 32,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _title,
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Document ID: ${widget.documentId}',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade600,
                        ),
                      ),
                      Text(
                        'Status: ${signed ? 'Signed' : 'Pending'}',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: signed ? Colors.green : Colors.orange,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            'Document Content',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.primaryColor,
            ),
          ),
          const SizedBox(height: 8),
          SelectableText(
            _content,
            style: const TextStyle(fontSize: 13, height: 1.5),
            textAlign: TextAlign.left,
          ),
          const SizedBox(height: 24),
          if (!signed) ...[
            const Divider(height: 32),
            const Text(
              'Signature Capture',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: AppTheme.primaryColor,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Use your finger or stylus to sign the document above.',
              style: TextStyle(fontSize: 13, color: Colors.black87),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.grey.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Enter Your Signature',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    controller: _signatureController,
                    decoration: InputDecoration(
                      hintText: 'Type your name as your signature',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    style: const TextStyle(
                      fontSize: 18,
                      fontStyle: FontStyle.italic,
                      color: Colors.black87,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _isSigning ? null : _signDocument,
                icon: _isSigning
                    ? const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.send),
                label: Text(_isSigning ? 'Signing...' : 'Sign Document'),
              ),
            ),
            const SizedBox(height: 8),
            if (_signatureData.isNotEmpty) ...[
              Text(
                'Previous signature: $_signatureData',
                style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
              ),
              const SizedBox(height: 8),
            ],
            SizedBox(
              width: double.infinity,
              child: TextButton.icon(
                onPressed: () {
                  _signatureController.clear();
                  setState(() {});
                },
                icon: const Icon(Icons.clear),
                label: const Text('Clear'),
              ),
            ),
          ],
          const SizedBox(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              if (signed)
                ElevatedButton.icon(
                  onPressed: _isDownloading ? null : _downloadSignedDocument,
                  icon: const Icon(Icons.download),
                  label: Text(_isDownloading ? 'Preparing...' : 'Download'),
                ),
              TextButton(
                onPressed: () => GoRouter.of(context).pop(),
                child: const Text('Back'),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.error_outline,
            size: 64,
            color: Colors.red,
          ),
          const SizedBox(height: 16),
          Text(
            _errorMessage ?? 'An error occurred',
            style: const TextStyle(fontSize: 16),
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loadDocument,
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_title),
      ),
      body: _buildDocumentDetail(),
    );
  }
}