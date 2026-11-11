import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import '../../../core/services/api_service.dart';

class DocumentScannerPage extends ConsumerStatefulWidget {
  const DocumentScannerPage({super.key});

  @override
  ConsumerState<DocumentScannerPage> createState() =>
      _DocumentScannerPageState();
}

class _DocumentScannerPageState extends ConsumerState<DocumentScannerPage> {
  static const Color primaryColor = Color(0xFF0B194C);
  static const Color accentColor = Color(0xFFF58220);

  String? _imagePath;
  bool _isScanning = false;
  Map<String, dynamic>? _result;
  String _selectedType = 'general';

  final List<String> _docTypes = [
    'general',
    'property_deed',
    'kyc_document',
    'invoice',
    'agreement',
    'registry_document',
  ];

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final image = await picker.pickImage(
      source: ImageSource.camera,
      maxWidth: 1024,
      maxHeight: 1024,
      imageQuality: 80,
    );
    if (image != null) {
      setState(() {
        _imagePath = image.path;
        _result = null;
      });
    }
  }

  Future<void> _pickFromGallery() async {
    final picker = ImagePicker();
    final image = await picker.pickImage(
      source: ImageSource.gallery,
      maxWidth: 1024,
      maxHeight: 1024,
      imageQuality: 80,
    );
    if (image != null) {
      setState(() {
        _imagePath = image.path;
        _result = null;
      });
    }
  }

  Future<void> _scanDocument() async {
    if (_imagePath == null) return;

    setState(() => _isScanning = true);
    try {
      final bytes = await File(_imagePath!).readAsBytes();
      final base64Image = base64Encode(bytes);

      final api = ApiService();
      final response = await api.post('/api/v1/ocr/extract', data: {
        'image_base64': base64Image,
        'file_name': 'doc_${DateTime.now().millisecondsSinceEpoch}.jpg',
        'document_type': _selectedType,
      });

      if (mounted && response['success'] == true) {
        final data = response['fields'] ?? response['data'];
        if (data is Map) {
          setState(() {
            _result = Map<String, dynamic>.from(data);
          });
        }
      } else if (mounted) {
        final err = response['error'] as String?;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(err ?? 'Document scan failed'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isScanning = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Document Scanner'),
        foregroundColor: primaryColor,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.pop(),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'AI Document Scanner',
              style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: primaryColor),
            ),
            const SizedBox(height: 8),
            const Text(
              'Scan documents with AI to extract key information',
              style: TextStyle(fontSize: 14, color: Colors.grey),
            ),
            const SizedBox(height: 24),
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16)),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    if (_imagePath != null)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.file(
                          File(_imagePath!),
                          height: 200,
                          width: double.infinity,
                          fit: BoxFit.cover,
                        ),
                      )
                    else
                      Container(
                        height: 200,
                        width: double.infinity,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade100,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.grey.shade300),
                        ),
                        child: const Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.document_scanner,
                                size: 64, color: Colors.grey),
                            SizedBox(height: 12),
                            Text('No document selected',
                                style: TextStyle(color: Colors.grey)),
                          ],
                        ),
                      ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _pickImage,
                            icon: const Icon(Icons.camera_alt,
                                color: accentColor),
                            label: const Text('Take Photo',
                                style: TextStyle(color: accentColor)),
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: accentColor),
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _pickFromGallery,
                            icon: const Icon(Icons.photo_library,
                                color: primaryColor),
                            label: const Text('Gallery',
                                style: TextStyle(color: primaryColor)),
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: primaryColor),
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    DropdownButtonFormField<String>(
                      initialValue: _selectedType,
                      decoration: const InputDecoration(
                        labelText: 'Document Type',
                        border: OutlineInputBorder(),
                      ),
                      items: _docTypes
                          .map((t) => DropdownMenuItem(
                                value: t,
                                child: Text(
                                    t.replaceAll('_', ' ').toUpperCase()),
                              ))
                          .toList(),
                      onChanged: (v) => setState(() => _selectedType = v ?? 'general'),
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton(
                        onPressed: (_isScanning || _imagePath == null)
                            ? null
                            : _scanDocument,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryColor,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                        child: _isScanning
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                    strokeWidth: 2),
                              )
                            : const Text('Scan with AI',
                                style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            if (_result != null) ...[
              const SizedBox(height: 24),
              _buildResultCard(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildResultCard() {
    final extractedRaw = _result!['extracted_data'];
    final extractedText = extractedRaw is Map
        ? jsonEncode(extractedRaw)
        : (extractedRaw?.toString() ?? '');
    final confidence = _result!['confidence'] as num? ?? 0;
    final summary = (_result!['summary'] ?? '').toString();

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Scan Result',
              style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: primaryColor),
            ),
            const SizedBox(height: 16),
            if (summary.isNotEmpty) ...[
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.blue.shade200),
                ),
                child: Text(summary,
                    style: const TextStyle(
                        fontSize: 14, color: Colors.black87)),
              ),
              const SizedBox(height: 16),
            ],
            const Text(
              'Extracted Data',
              style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: primaryColor),
            ),
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.grey.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Text(
                extractedText.isEmpty ? 'No data extracted' : extractedText,
                style: const TextStyle(fontSize: 13, fontFamily: 'monospace'),
              ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.green, size: 20),
                const SizedBox(width: 8),
                Text(
                  'Confidence: ${(confidence * 100).toStringAsFixed(0)}%',
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
