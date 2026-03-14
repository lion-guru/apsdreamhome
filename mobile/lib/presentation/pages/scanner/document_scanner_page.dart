import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import '../../core/constants/app_constants.dart';
import '../../core/theme/app_theme.dart';
import '../widgets/glass_card.dart';
import '../widgets/common_widgets.dart';

class DocumentScannerPage extends ConsumerStatefulWidget {
  const DocumentScannerPage({super.key});
  
  @override
  ConsumerState<DocumentScannerPage> createState() => _DocumentScannerPageState();
}

class _DocumentScannerPageState extends ConsumerState<DocumentScannerPage> {
  final ImagePicker _picker = ImagePicker();
  List<XFile> _scannedImages = [];
  bool _isProcessing = false;
  
  @override
  void initState() {
    super.initState();
    _requestPermissions();
  }
  
  Future<void> _requestPermissions() async {
    final cameraStatus = await Permission.camera.request();
    final storageStatus = await Permission.storage.request();
    
    if (cameraStatus != PermissionStatus.granted || storageStatus != PermissionStatus.granted) {
      _showPermissionDialog();
    }
  }
  
  void _showPermissionDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Permissions Required'),
        content: const Text('Camera and storage permissions are required to scan documents.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
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
        });
        
        // Process the image (auto-enhance, crop, etc.)
        _processDocument(image);
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error capturing document: $e')),
      );
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
        });
        
        // Process the image
        _processDocument(image);
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error selecting image: $e')),
      );
    }
  }
  
  Future<void> _processDocument(XFile image) async {
    setState(() {
      _isProcessing = true;
    });
    
    try {
      // Simulate document processing
      // In a real app, you would use OCR libraries like Google ML Kit
      await Future.delayed(const Duration(seconds: 2));
      
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Document processed successfully!')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error processing document: $e')),
      );
    } finally {
      setState(() {
        _isProcessing = false;
      });
    }
  }
  
  void _removeImage(int index) {
    setState(() {
      _scannedImages.removeAt(index);
    });
  }
  
  Future<void> _createPDF() async {
    if (_scannedImages.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No documents to convert')),
      );
      return;
    }
    
    setState(() {
      _isProcessing = true;
    });
    
    try {
      // Simulate PDF creation
      // In a real app, you would use libraries like pdf
      await Future.delayed(const Duration(seconds: 3));
      
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('PDF created successfully!')),
      );
      
      // Clear the scanned images after PDF creation
      setState(() {
        _scannedImages.clear();
      });
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error creating PDF: $e')),
      );
    } finally {
      setState(() {
        _isProcessing = false;
      });
    }
  }
  
  Future<void> _uploadToServer() async {
    if (_scannedImages.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No documents to upload')),
      );
      return;
    }
    
    setState(() {
      _isProcessing = true;
    });
    
    try {
      // Simulate upload
      await Future.delayed(const Duration(seconds: 2));
      
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Documents uploaded successfully!')),
      );
      
      setState(() {
        _scannedImages.clear();
      });
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error uploading documents: $e')),
      );
    } finally {
      setState(() {
        _isProcessing = false;
      });
    }
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
                      Icon(
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
                              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              'Scan, enhance, and convert documents',
                              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                color: Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: 16),
                  
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
          
          // Processing Indicator
          if (_isProcessing)
            Container(
              padding: const EdgeInsets.all(AppConstants.defaultPadding),
              child: GlassCard(
                child: Row(
                  children: [
                    const CircularProgressIndicator(),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Text(
                        'Processing documents...',
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
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
              child: Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: _isProcessing ? null : _createPDF,
                      icon: const Icon(Icons.picture_as_pdf),
                      label: const Text('Create PDF'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: _isProcessing ? null : _uploadToServer,
                      icon: const Icon(Icons.cloud_upload),
                      label: const Text('Upload'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        foregroundColor: Colors.white,
                      ),
                    ),
                  ),
                ],
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
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              color: Colors.grey.shade500,
            ),
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
          childAspectRatio: 0.7,
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
                color: Colors.grey.shade200,
              ),
              child: Stack(
                children: [
                  // Image placeholder (in real app, display actual image)
                  Center(
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
                  
                  // Remove button
                  Positioned(
                    top: 8,
                    right: 8,
                    child: GestureDetector(
                      onTap: () => _removeImage(index),
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: Colors.red.withOpacity(0.8),
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
          
          // Document info
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Document ${index + 1}',
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  'Size: ${(image.length() / 1024).toStringAsFixed(1)} KB',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
