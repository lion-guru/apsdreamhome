import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class DocumentScannerPage extends ConsumerStatefulWidget {
  const DocumentScannerPage({super.key});

  @override
  ConsumerState<DocumentScannerPage> createState() =>
      _DocumentScannerPageState();
}

class _DocumentScannerPageState extends ConsumerState<DocumentScannerPage> {
  final ImagePicker _picker = ImagePicker();
  final ApiService _api = ApiService();
  final List<XFile> _scannedImages = [];
  bool _isProcessing = false;
  String _documentType = 'aadhaar';
  String? _uploadError;
  String? _uploadSuccess;

  final List<Map<String, String>> _documentTypes = [
    {'value': 'aadhaar', 'label': 'Aadhaar Card'},
    {'value': 'pan', 'label': 'PAN Card'},
    {'value': 'voter', 'label': 'Voter ID'},
    {'value': 'passport', 'label': 'Passport'},
    {'value': 'bank_statement', 'label': 'Bank Statement'},
    {'value': 'property_doc', 'label': 'Property Document'},
    {'value': 'agreement', 'label': 'Agreement'},
    {'value': 'other', 'label': 'Other'},
  ];

  @override
  void initState() {
    super.initState();
    _requestPermissions();
  }

  Future<void> _requestPermissions() async {
    final cameraStatus = await Permission.camera.request();
    final storageStatus = await Permission.storage.request();

    if (cameraStatus != PermissionStatus.granted ||
        storageStatus != PermissionStatus.granted) {
      if (mounted) _showPermissionDialog();
    }
  }

  void _showPermissionDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Permissions Required'),
        content: const Text(
          'Camera and storage permissions are required to scan documents.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              openAppSettings();
            },
            child: const Text('Open Settings'),
          ),
        ],
      ),
    );
  }

  Future<void> _captureDocument() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.camera,
        preferredCameraDevice: CameraDevice.rear,
        imageQuality: 80,
      );
      if (image != null) {
        setState(() {
          _scannedImages.add(image);
          _uploadError = null;
          _uploadSuccess = null;
        });
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  Future<void> _selectFromGallery() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 80,
      );
      if (image != null) {
        setState(() {
          _scannedImages.add(image);
          _uploadError = null;
          _uploadSuccess = null;
        });
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  Future<void> _uploadToServer() async {
    if (_scannedImages.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('No documents to upload')));
      return;
    }

    setState(() => _isProcessing = true);

    int successCount = 0;
    int failCount = 0;

    for (final image in _scannedImages) {
      try {
        await _api.uploadDocument(image.path, _documentType);
        successCount++;
      } catch (e) {
        failCount++;
      }
    }

    if (!mounted) return;
    setState(() {
      _isProcessing = false;
      if (failCount == 0) {
        _uploadSuccess = '$successCount document(s) uploaded successfully!';
        _uploadError = null;
        _scannedImages.clear();
      } else {
        _uploadError = '$successCount uploaded, $failCount failed';
        _uploadSuccess = null;
      }
    });
  }

  void _removeImage(int index) {
    setState(() {
      _scannedImages.removeAt(index);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Document Scanner'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          // Scanner Controls
          Container(
            padding: const EdgeInsets.all(AppConstants.defaultPadding),
            child: GlassCard(
              child: Column(
                children: [
                  Row(
                    children: [
                      const Icon(
                        Icons.document_scanner,
                        size: 32,
                        color: AppTheme.primaryColor,
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Document Scanner',
                              style: Theme.of(context).textTheme.titleLarge
                                  ?.copyWith(fontWeight: FontWeight.bold),
                            ),
                            Text(
                              '${_scannedImages.length} image(s) captured',
                              style: Theme.of(context).textTheme.bodyMedium
                                  ?.copyWith(color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  // Document type selector
                  DropdownButtonFormField<String>(
                    initialValue: _documentType,
                    decoration: const InputDecoration(
                      labelText: 'Document Type',
                      border: OutlineInputBorder(),
                      contentPadding: EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                    ),
                    items: _documentTypes
                        .map(
                          (t) => DropdownMenuItem(
                            value: t['value'],
                            child: Text(t['label']!),
                          ),
                        )
                        .toList(),
                    onChanged: (val) {
                      if (val != null) {
                        setState(() => _documentType = val);
                      }
                    },
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _isProcessing ? null : _captureDocument,
                          icon: const Icon(Icons.camera_alt),
                          label: const Text('Capture'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTheme.primaryColor,
                            foregroundColor: Colors.white,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: _isProcessing ? null : _selectFromGallery,
                          icon: const Icon(Icons.photo_library),
                          label: const Text('Gallery'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // Status messages
          if (_isProcessing)
            Container(
              padding: const EdgeInsets.all(AppConstants.defaultPadding),
              child: GlassCard(
                child: Row(
                  children: [
                    const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Text(
                        'Uploading documents...',
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

          if (_uploadError != null)
            Container(
              padding: const EdgeInsets.symmetric(
                horizontal: AppConstants.defaultPadding,
                vertical: 8,
              ),
              child: Text(
                _uploadError!,
                style: const TextStyle(color: Colors.red),
              ),
            ),

          if (_uploadSuccess != null)
            Container(
              padding: const EdgeInsets.symmetric(
                horizontal: AppConstants.defaultPadding,
                vertical: 8,
              ),
              child: Row(
                children: [
                  const Icon(Icons.check_circle, color: Colors.green, size: 20),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _uploadSuccess!,
                      style: const TextStyle(color: Colors.green),
                    ),
                  ),
                ],
              ),
            ),

          // Scanned Images
          Expanded(
            child: _scannedImages.isEmpty
                ? _buildEmptyState()
                : _buildScannedImagesList(),
          ),

          // Action Buttons
          if (_scannedImages.isNotEmpty)
            Container(
              padding: const EdgeInsets.all(AppConstants.defaultPadding),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _isProcessing ? null : _uploadToServer,
                  icon: const Icon(Icons.cloud_upload),
                  label: const Text('Upload All to Server'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.document_scanner_outlined,
            size: 64,
            color: Colors.grey.shade400,
          ),
          const SizedBox(height: 16),
          Text(
            'No Documents Scanned',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Capture documents using camera or select from gallery',
            style: Theme.of(
              context,
            ).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade500),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: _captureDocument,
            icon: const Icon(Icons.camera_alt),
            label: const Text('Start Scanning'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryColor,
              foregroundColor: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildScannedImagesList() {
    return Container(
      padding: const EdgeInsets.all(AppConstants.defaultPadding),
      child: GridView.builder(
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 0.75,
        ),
        itemCount: _scannedImages.length,
        itemBuilder: (context, index) {
          final image = _scannedImages[index];
          return _buildScannedImageCard(image, index);
        },
      ),
    );
  }

  Widget _buildScannedImageCard(XFile image, int index) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Container(
              width: double.infinity,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
                color: Colors.grey.shade100,
              ),
              child: Stack(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.file(
                      File(image.path),
                      width: double.infinity,
                      height: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.image,
                              size: 48,
                              color: Colors.grey.shade400,
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Document ${index + 1}',
                              style: TextStyle(
                                color: Colors.grey.shade600,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    top: 8,
                    right: 8,
                    child: GestureDetector(
                      onTap: () => _removeImage(index),
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: Colors.red.withValues(alpha: 0.8),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(
                          Icons.close,
                          size: 16,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Document ${index + 1}',
                  style: Theme.of(
                    context,
                  ).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600),
                ),
                FutureBuilder<int>(
                  future: image.length(),
                  builder: (context, snapshot) {
                    final size = snapshot.hasData
                        ? (snapshot.data! / 1024).toStringAsFixed(1)
                        : '...';
                    return Text(
                      'Size: $size KB',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey.shade600,
                      ),
                    );
                  },
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
