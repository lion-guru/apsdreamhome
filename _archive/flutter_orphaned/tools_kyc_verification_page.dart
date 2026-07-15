import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
// import 'package:image_picker/image_picker.dart';

import '../../../core/utils/logger.dart';

/// KYC Verification Page
/// Upload and verify Aadhar, PAN, and other documents
class KYCVerificationPage extends StatefulWidget {
  const KYCVerificationPage({super.key});

  @override
  State<KYCVerificationPage> createState() => _KYCVerificationPageState();
}

class _KYCVerificationPageState extends State<KYCVerificationPage> {
  // Form controllers
  final _aadharController = TextEditingController();
  final _panController = TextEditingController();
  final _fullNameController = TextEditingController();
  final _dobController = TextEditingController();
  final _addressController = TextEditingController();

  // State
  File? _aadharFrontImage;
  File? _aadharBackImage;
  File? _panImage;
  File? _selfieImage;

  bool _isUploading = false;
  double _uploadProgress = 0;
  String _kycStatus = 'pending'; // pending, verified, rejected

  // final ImagePicker _picker = ImagePicker();

  @override
  void dispose() {
    _aadharController.dispose();
    _panController.dispose();
    _fullNameController.dispose();
    _dobController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(String type) async {
    AppLogger.info('Image picking disabled to support SDK 34');
    _showError('Camera/Gallery access is temporarily disabled for this build.');
    /*
    try {
      final XFile? picked = await _picker.pickImage(
        source: ImageSource.camera,
        imageQuality: 85,
        maxWidth: 1024,
      );
      
      if (picked != null) {
        setState(() {
          switch (type) {
            case 'aadhar_front':
              _aadharFrontImage = File(picked.path);
              break;
            case 'aadhar_back':
              _aadharBackImage = File(picked.path);
              break;
            case 'pan':
              _panImage = File(picked.path);
              break;
            case 'selfie':
              _selfieImage = File(picked.path);
              break;
          }
        });
        
        // Simulate OCR extraction
        _simulateOCR(type);
      }
    } catch (e) {
      AppLogger.error('Image picker error', e);
      _showError('Failed to capture image: $e');
    }
    */
  }

  Future<void> _submitKYC() async {
    if (!_validateForm()) return;

    setState(() {
      _isUploading = true;
    });

    // Simulate upload progress
    for (int i = 0; i <= 100; i += 10) {
      await Future.delayed(const Duration(milliseconds: 200));
      setState(() {
        _uploadProgress = i / 100;
      });
    }

    setState(() {
      _isUploading = false;
      _uploadProgress = 0;
      _kycStatus = 'under_review';
    });

    _showSuccess('KYC documents submitted for verification!');
  }

  bool _validateForm() {
    if (_aadharController.text.length != 14) {
      _showError('Please enter valid 12-digit Aadhar number');
      return false;
    }
    if (_panController.text.length != 10) {
      _showError('Please enter valid 10-character PAN number');
      return false;
    }
    if (_fullNameController.text.isEmpty) {
      _showError('Please enter your full name');
      return false;
    }
    if (_aadharFrontImage == null || _aadharBackImage == null) {
      _showError('Please upload both sides of Aadhar card');
      return false;
    }
    if (_panImage == null) {
      _showError('Please upload PAN card');
      return false;
    }
    return true;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('KYC Verification'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
        actions: [
          if (_kycStatus == 'verified')
            Container(
              margin: const EdgeInsets.only(right: 16),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.green,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.verified, size: 16, color: Colors.white),
                  SizedBox(width: 4),
                  Text(
                    'Verified',
                    style: TextStyle(color: Colors.white, fontSize: 12),
                  ),
                ],
              ),
            ),
        ],
      ),
      body: _isUploading
          ? _buildUploadProgress()
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Status Card
                  _buildStatusCard(),
                  const SizedBox(height: 24),

                  // Document Upload Section
                  _buildDocumentUploadSection(),
                  const SizedBox(height: 24),

                  // Personal Details Form
                  _buildPersonalDetailsForm(),
                  const SizedBox(height: 32),

                  // Submit Button
                  _buildSubmitButton(),
                  const SizedBox(height: 24),

                  // Help Section
                  _buildHelpSection(),
                ],
              ),
            ),
    );
  }

  Widget _buildUploadProgress() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          SizedBox(
            width: 150,
            height: 150,
            child: CircularProgressIndicator(
              value: _uploadProgress,
              strokeWidth: 8,
              backgroundColor: Colors.grey.shade200,
              valueColor: AlwaysStoppedAnimation<Color>(Colors.blue.shade700),
            ),
          ),
          const SizedBox(height: 24),
          Text(
            '${(_uploadProgress * 100).toInt()}%',
            style: const TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Uploading documents...',
            style: TextStyle(color: Colors.grey),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () {
              setState(() {
                _isUploading = false;
              });
            },
            child: const Text('Cancel'),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusCard() {
    Color statusColor;
    IconData statusIcon;
    String statusText;
    String statusMessage;

    switch (_kycStatus) {
      case 'verified':
        statusColor = Colors.green;
        statusIcon = Icons.verified_user;
        statusText = 'Verified';
        statusMessage = 'Your KYC is verified. You can now book plots!';
        break;
      case 'under_review':
        statusColor = Colors.orange;
        statusIcon = Icons.pending;
        statusText = 'Under Review';
        statusMessage =
            'Your documents are being verified. This may take 24-48 hours.';
        break;
      case 'rejected':
        statusColor = Colors.red;
        statusIcon = Icons.error;
        statusText = 'Rejected';
        statusMessage = 'Your KYC was rejected. Please upload clear documents.';
        break;
      default:
        statusColor = Colors.blue;
        statusIcon = Icons.info;
        statusText = 'Pending';
        statusMessage = 'Complete your KYC to book plots and avail loans.';
    }

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: statusColor.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: statusColor.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: statusColor.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(statusIcon, color: statusColor, size: 32),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'KYC Status: $statusText',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  statusMessage,
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.grey.shade700,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDocumentUploadSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Upload Documents',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),

            // Aadhar Card
            _buildDocumentUploader(
              'Aadhar Card (Front)',
              'Upload front side of your Aadhar',
              _aadharFrontImage,
              () => _pickImage('aadhar_front'),
              isRequired: true,
            ),
            const SizedBox(height: 12),
            _buildDocumentUploader(
              'Aadhar Card (Back)',
              'Upload back side of your Aadhar',
              _aadharBackImage,
              () => _pickImage('aadhar_back'),
              isRequired: true,
            ),
            const SizedBox(height: 12),

            // PAN Card
            _buildDocumentUploader(
              'PAN Card',
              'Upload front side of your PAN card',
              _panImage,
              () => _pickImage('pan'),
              isRequired: true,
            ),
            const SizedBox(height: 12),

            // Selfie
            _buildDocumentUploader(
              'Selfie with Document',
              'Take a selfie holding Aadhar card',
              _selfieImage,
              () => _pickImage('selfie'),
              isRequired: true,
              icon: Icons.camera_alt,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentUploader(
    String title,
    String subtitle,
    File? image,
    VoidCallback onTap, {
    bool isRequired = false,
    IconData icon = Icons.upload_file,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: image != null ? Colors.green.shade50 : Colors.grey.shade50,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: image != null ? Colors.green.shade300 : Colors.grey.shade300,
          ),
        ),
        child: Row(
          children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: image != null
                    ? Colors.green.shade100
                    : Colors.blue.shade100,
                borderRadius: BorderRadius.circular(8),
              ),
              child: image != null
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.file(image, fit: BoxFit.cover),
                    )
                  : Icon(icon, color: Colors.blue.shade700, size: 28),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(
                        title,
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      if (isRequired)
                        const Text(
                          ' *',
                          style: TextStyle(
                            color: Colors.red,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    image != null ? 'Uploaded ✓' : subtitle,
                    style: TextStyle(
                      fontSize: 12,
                      color:
                          image != null ? Colors.green : Colors.grey.shade600,
                    ),
                  ),
                ],
              ),
            ),
            Icon(
              image != null ? Icons.check_circle : Icons.camera_alt,
              color: image != null ? Colors.green : Colors.grey,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPersonalDetailsForm() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Personal Details',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),

            // Full Name
            TextField(
              controller: _fullNameController,
              decoration: InputDecoration(
                labelText: 'Full Name (as per Aadhar) *',
                prefixIcon: const Icon(Icons.person),
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 16),

            // Aadhar Number
            TextField(
              controller: _aadharController,
              keyboardType: TextInputType.number,
              inputFormatters: [
                FilteringTextInputFormatter.digitsOnly,
                LengthLimitingTextInputFormatter(14),
                _AadharFormatter(),
              ],
              decoration: InputDecoration(
                labelText: 'Aadhar Number *',
                prefixIcon: const Icon(Icons.credit_card),
                hintText: 'XXXX XXXX XXXX',
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 16),

            // PAN Number
            TextField(
              controller: _panController,
              textCapitalization: TextCapitalization.characters,
              inputFormatters: [
                FilteringTextInputFormatter.allow(RegExp(r'[A-Z0-9]')),
                LengthLimitingTextInputFormatter(10),
              ],
              decoration: InputDecoration(
                labelText: 'PAN Number *',
                prefixIcon: const Icon(Icons.account_balance_wallet),
                hintText: 'ABCDE1234F',
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 16),

            // Date of Birth
            TextField(
              controller: _dobController,
              decoration: InputDecoration(
                labelText: 'Date of Birth *',
                prefixIcon: const Icon(Icons.calendar_today),
                hintText: 'DD/MM/YYYY',
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.date_range),
                  onPressed: () => _selectDate(),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Address
            TextField(
              controller: _addressController,
              maxLines: 3,
              decoration: InputDecoration(
                labelText: 'Address (as per Aadhar)',
                prefixIcon: const Icon(Icons.home),
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSubmitButton() {
    return ElevatedButton.icon(
      onPressed: _submitKYC,
      icon: const Icon(Icons.verified_user),
      label: const Text('Submit for Verification'),
      style: ElevatedButton.styleFrom(
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
        minimumSize: const Size(double.infinity, 54),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
    );
  }

  Widget _buildHelpSection() {
    return Card(
      color: Colors.blue.shade50,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.help_outline, color: Colors.blue),
                SizedBox(width: 8),
                Text(
                  'Why KYC is required?',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 12),
            _buildHelpItem('• Book plots online'),
            _buildHelpItem('• Apply for home loans'),
            _buildHelpItem('• Receive commission payouts'),
            _buildHelpItem('• Legal compliance (RBI norms)'),
            const SizedBox(height: 12),
            const Text(
              'Your data is secure and encrypted. We never share it with third parties.',
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHelpItem(String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 13,
          color: Colors.grey.shade700,
        ),
      ),
    );
  }

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime(1990),
      firstDate: DateTime(1950),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        _dobController.text = '${picked.day.toString().padLeft(2, '0')}/'
            '${picked.month.toString().padLeft(2, '0')}/'
            '${picked.year}';
      });
    }
  }

  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Colors.white),
            const SizedBox(width: 8),
            Text(message),
          ],
        ),
        backgroundColor: Colors.green,
      ),
    );
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.error, color: Colors.white),
            const SizedBox(width: 8),
            Text(message),
          ],
        ),
        backgroundColor: Colors.red,
      ),
    );
  }
}

/// Aadhar number formatter (adds spaces)
class _AadharFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    String text = newValue.text.replaceAll(' ', '');
    if (text.length > 12) text = text.substring(0, 12);

    final StringBuffer buffer = StringBuffer();
    for (int i = 0; i < text.length; i++) {
      if (i > 0 && i % 4 == 0) buffer.write(' ');
      buffer.write(text[i]);
    }

    return TextEditingValue(
      text: buffer.toString(),
      selection: TextSelection.collapsed(offset: buffer.length),
    );
  }
}
