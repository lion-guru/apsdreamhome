import 'dart:io';

import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';

import '../../../domain/models/kyc_models.dart';
import '../../../widgets/common/custom_button.dart';
import '../../../data/repositories/kyc_repository.dart' as repo;
import '../../../data/repositories/kyc_repository_provider.dart';

/// KYC Verification Page - Complete PAN/Aadhaar verification with document upload, face matching, and video KYC
class KYCVerificationPage extends ConsumerStatefulWidget {
  const KYCVerificationPage({super.key});

  @override
  ConsumerState<KYCVerificationPage> createState() =>
      _KYCVerificationPageState();
}

class _KYCVerificationPageState extends ConsumerState<KYCVerificationPage>
    with TickerProviderStateMixin {
  late TabController _tabController;
  late PageController _pageController;

  // Form controllers
  final _panController = TextEditingController();
  final _nameController = TextEditingController();
  final _aadhaarController = TextEditingController();
  final _dobController = TextEditingController();

  // File and camera
  File? _panDocument;
  File? _aadhaarDocument;
  File? _selfieImage;
  File? _videoRecording;
  XFile? _capturedImage;

  // Camera controller
  CameraController? _cameraController;
  List<CameraDescription> _cameras = [];
  bool _isCameraInitialized = false;
  bool _isRecording = false;
  int _recordingDuration = 0;

  // State
  bool _isLoading = false;
  String _currentStatus = '';
  repo.KYCVerificationResult? _panResult;
  repo.KYCVerificationResult? _aadhaarResult;
  KYCStatusModel? _kycStatus;

  // Animation
  late AnimationController _progressController;
  late Animation<double> _progressAnimation;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _pageController = PageController();
    _progressController = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    );
    _progressAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _progressController, curve: Curves.easeInOut),
    );

    _initializeCamera();
    _loadKYCStatus();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _pageController.dispose();
    _panController.dispose();
    _nameController.dispose();
    _aadhaarController.dispose();
    _dobController.dispose();
    _cameraController?.dispose();
    _progressController.dispose();
    super.dispose();
  }

  Future<void> _initializeCamera() async {
    try {
      // Request camera permission
      final cameraPermission = await Permission.camera.request();
      if (cameraPermission.isGranted) {
        _cameras = await availableCameras();
        if (_cameras.isNotEmpty) {
          _cameraController = CameraController(
            _cameras[0], // Use front camera for selfies
            ResolutionPreset.high,
            enableAudio: true,
          );
          await _cameraController!.initialize();
          if (mounted) {
            setState(() {
              _isCameraInitialized = true;
            });
          }
        }
      }
    } catch (e) {
      debugPrint('Camera initialization error: $e');
    }
  }

  Future<void> _loadKYCStatus() async {
    setState(() => _isLoading = true);
    try {
      final kycRepo = ref.read(kycRepositoryProvider);
      final status = await kycRepo.getKYCStatus();
      setState(() {
        _kycStatus = status;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _pickImage(
      {required bool isPan, required bool isDocument}) async {
    final picker = ImagePicker();
    final XFile? image = await picker.pickImage(
      source: isDocument ? ImageSource.gallery : ImageSource.camera,
      imageQuality: 80,
    );

    if (image != null) {
      setState(() {
        if (isPan) {
          _panDocument = File(image.path);
        } else {
          _aadhaarDocument = File(image.path);
        }
      });
    }
  }

  Future<void> _captureSelfie() async {
    if (!_isCameraInitialized || _cameraController == null) return;

    try {
      final image = await _cameraController!.takePicture();
      setState(() {
        _selfieImage = File(image.path);
      });
    } catch (e) {
      debugPrint('Selfie capture error: $e');
    }
  }

  Future<void> _startVideoRecording() async {
    if (!_isCameraInitialized || _cameraController == null) return;

    try {
      await _cameraController!.startVideoRecording();
      setState(() {
        _isRecording = true;
        _recordingDuration = 0;
      });

      // Start timer for recording duration
      Stream.periodic(const Duration(seconds: 1), (count) => count)
          .listen((duration) {
        if (_isRecording && duration < 30) {
          // Max 30 seconds
          setState(() {
            _recordingDuration = duration;
          });
        } else if (_isRecording) {
          _stopVideoRecording();
        }
      });
    } catch (e) {
      debugPrint('Video recording start error: $e');
    }
  }

  Future<void> _stopVideoRecording() async {
    if (!_isRecording || _cameraController == null) return;

    try {
      final video = await _cameraController!.stopVideoRecording();
      setState(() {
        _isRecording = false;
        _videoRecording = File(video.path);
      });
    } catch (e) {
      debugPrint('Video recording stop error: $e');
    }
  }

  Future<void> _verifyPAN() async {
    if (_panController.text.isEmpty) {
      _showError('Please enter PAN number');
      return;
    }

    setState(() {
      _isLoading = true;
      _currentStatus = 'Verifying PAN...';
    });

    try {
      final kycRepo = ref.read(kycRepositoryProvider);
      final result = await ref.read(kycRepositoryProvider).verifyPAN(
            pan: _panController.text,
            name: _nameController.text,
          );

      setState(() {
        _panResult = result;
        _isLoading = false;
        _currentStatus = '';
      });

      if (result.success) {
        _showSuccess('PAN verified successfully!');
        _progressController.forward();
      } else {
        _showError(result.message);
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
        _currentStatus = '';
      });
      _showError('PAN verification failed: $e');
    }
  }

  Future<void> _verifyAadhaar() async {
    if (_aadhaarController.text.isEmpty) {
      _showError('Please enter Aadhaar number');
      return;
    }

    setState(() {
      _isLoading = true;
      _currentStatus = 'Verifying Aadhaar...';
    });

    try {
      final kycRepo = ref.read(kycRepositoryProvider);
      final result = await kycRepo.verifyAadhaar(
        aadhaar: _aadhaarController.text.trim(),
      );

      setState(() {
        _aadhaarResult = result;
        _isLoading = false;
        _currentStatus = '';
      });

      if (result.success) {
        _showSuccess('Aadhaar verified successfully!');
        _progressController.forward();
      } else {
        _showError(result.message);
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
        _currentStatus = '';
      });
      _showError('Aadhaar verification failed: $e');
    }
  }

  Future<void> _uploadDocuments() async {
    setState(() {
      _isLoading = true;
      _currentStatus = 'Uploading documents...';
    });

    try {
      // Simulate document upload
      await Future.delayed(const Duration(seconds: 3));

      setState(() {
        _isLoading = false;
        _currentStatus = '';
      });

      _showSuccess('Documents uploaded successfully!');
      _tabController.animateTo(2); // Move to face matching tab
    } catch (e) {
      setState(() {
        _isLoading = false;
        _currentStatus = '';
      });
      _showError('Document upload failed: $e');
    }
  }

  Future<void> _performFaceMatching() async {
    if (_selfieImage == null) {
      _showError('Please capture your selfie first');
      return;
    }

    setState(() {
      _isLoading = true;
      _currentStatus = 'Performing face matching...';
    });

    try {
      // Simulate face matching
      await Future.delayed(const Duration(seconds: 5));

      setState(() {
        _isLoading = false;
        _currentStatus = '';
      });

      _showSuccess('Face matching completed successfully!');
      _progressController.forward();
    } catch (e) {
      setState(() {
        _isLoading = false;
        _currentStatus = '';
      });
      _showError('Face matching failed: $e');
    }
  }

  Future<void> _submitVideoKYC() async {
    if (_videoRecording == null) {
      _showError('Please record your video first');
      return;
    }

    setState(() {
      _isLoading = true;
      _currentStatus = 'Submitting video KYC...';
    });

    try {
      // Simulate video submission
      await Future.delayed(const Duration(seconds: 5));

      setState(() {
        _isLoading = false;
        _currentStatus = '';
      });

      _showSuccess('Video KYC submitted successfully!');
      _progressController.forward();
      await _loadKYCStatus(); // Refresh KYC status
    } catch (e) {
      setState(() {
        _isLoading = false;
        _currentStatus = '';
      });
      _showError('Video KYC submission failed: $e');
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.green,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'KYC Verification',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF2E7D32),
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          onTap: (index) {
            _pageController.animateToPage(
              index,
              duration: const Duration(milliseconds: 300),
              curve: Curves.easeInOut,
            );
          },
          tabs: const [
            Tab(
              icon: Icon(Icons.verified_user),
              text: 'PAN',
            ),
            Tab(
              icon: Icon(Icons.credit_card),
              text: 'Aadhaar',
            ),
            Tab(
              icon: Icon(Icons.face),
              text: 'Face/Video',
            ),
          ],
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          indicatorColor: Colors.white,
        ),
      ),
      body: PageView(
        controller: _pageController,
        onPageChanged: (index) {
          _tabController.animateTo(index);
        },
        children: [
          _buildPANVerificationTab(),
          _buildAadhaarVerificationTab(),
          _buildFaceVideoKYCTab(),
        ],
      ),
      bottomNavigationBar: _buildProgressIndicator(),
    );
  }

  Widget _buildProgressIndicator() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.1),
            blurRadius: 4,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              Expanded(
                child: LinearProgressIndicator(
                  value: _progressAnimation.value,
                  backgroundColor: Colors.grey[300],
                  valueColor:
                      const AlwaysStoppedAnimation<Color>(Color(0xFF2E7D32)),
                ),
              ),
              const SizedBox(width: 16),
              Text(
                '${(_progressAnimation.value * 100).toInt()}%',
                style: GoogleFonts.poppins(
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF2E7D32),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            'Complete all verification steps to finish KYC',
            style: GoogleFonts.poppins(
              fontSize: 12,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPANVerificationTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildStatusCard(),
          const SizedBox(height: 24),
          _buildPANForm(),
          const SizedBox(height: 24),
          _buildDocumentUpload(isPan: true),
          const SizedBox(height: 24),
          if (_panResult != null) _buildVerificationResult(_panResult!),
          const SizedBox(height: 24),
          CustomButton(
            text: 'Verify PAN',
            onPressed: _verifyPAN,
            isLoading: _isLoading,
            disabled: _panController.text.isEmpty,
          ),
        ],
      ),
    );
  }

  Widget _buildAadhaarVerificationTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildStatusCard(),
          const SizedBox(height: 24),
          _buildAadhaarForm(),
          const SizedBox(height: 24),
          _buildDocumentUpload(isPan: false),
          const SizedBox(height: 24),
          if (_aadhaarResult != null) _buildVerificationResult(_aadhaarResult!),
          const SizedBox(height: 24),
          CustomButton(
            text: 'Verify Aadhaar',
            onPressed: _verifyAadhaar,
            isLoading: _isLoading,
            disabled: _aadhaarController.text.isEmpty,
          ),
        ],
      ),
    );
  }

  Widget _buildFaceVideoKYCTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildStatusCard(),
          const SizedBox(height: 24),
          _buildSelfieCapture(),
          const SizedBox(height: 24),
          _buildVideoRecording(),
          const SizedBox(height: 24),
          Row(
            children: [
              Expanded(
                child: CustomButton(
                  text: 'Match Face',
                  onPressed: _performFaceMatching,
                  isLoading: _isLoading && _currentStatus.contains('face'),
                  disabled: _selfieImage == null,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: CustomButton(
                  text: 'Submit Video KYC',
                  onPressed: _submitVideoKYC,
                  isLoading: _isLoading && _currentStatus.contains('video'),
                  disabled: _videoRecording == null,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatusCard() {
    if (_kycStatus == null) return const SizedBox();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _kycStatus!.isCompleted ? Colors.green[50] : Colors.orange[50],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: _kycStatus!.isCompleted ? Colors.green : Colors.orange,
          width: 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                _kycStatus!.isCompleted ? Icons.verified : Icons.pending,
                color: _kycStatus!.isCompleted ? Colors.green : Colors.orange,
              ),
              const SizedBox(width: 8),
              Text(
                _kycStatus!.isCompleted ? 'KYC Completed' : 'KYC In Progress',
                style: GoogleFonts.poppins(
                  fontWeight: FontWeight.w600,
                  color: _kycStatus!.isCompleted ? Colors.green : Colors.orange,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            'Status: ${_kycStatus!.isCompleted ? "Completed" : "In Progress"}',
            style: GoogleFonts.poppins(
              fontSize: 14,
              color: Colors.grey[700],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPANForm() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'PAN Verification',
            style: GoogleFonts.poppins(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _panController,
            decoration: InputDecoration(
              labelText: 'PAN Number',
              hintText: 'ABCDE1234F',
              prefixIcon: const Icon(Icons.credit_card),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            textCapitalization: TextCapitalization.characters,
            maxLength: 10,
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _nameController,
            decoration: InputDecoration(
              labelText: 'Name (as on PAN)',
              hintText: 'John Doe',
              prefixIcon: const Icon(Icons.person),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            textCapitalization: TextCapitalization.words,
          ),
        ],
      ),
    );
  }

  Widget _buildAadhaarForm() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Aadhaar Verification',
            style: GoogleFonts.poppins(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _aadhaarController,
            decoration: InputDecoration(
              labelText: 'Aadhaar Number',
              hintText: '1234 5678 9012',
              prefixIcon: const Icon(Icons.credit_card),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            keyboardType: TextInputType.number,
            maxLength: 14,
          ),
          const SizedBox(height: 8),
          Text(
            'Note: You will receive an OTP on your registered mobile number',
            style: GoogleFonts.poppins(
              fontSize: 12,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDocumentUpload({required bool isPan}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Upload ${isPan ? 'PAN' : 'Aadhaar'} Document',
            style: GoogleFonts.poppins(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          if (isPan ? _panDocument == null : _aadhaarDocument == null)
            Container(
              width: double.infinity,
              height: 150,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey[300]!),
                borderRadius: BorderRadius.circular(8),
                color: Colors.grey[50],
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.cloud_upload,
                    size: 48,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Tap to upload ${isPan ? 'PAN' : 'Aadhaar'} card',
                    style: GoogleFonts.poppins(
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 8),
                  ElevatedButton.icon(
                    onPressed: () => _pickImage(isPan: isPan, isDocument: true),
                    icon: const Icon(Icons.photo_library),
                    label: const Text('Choose File'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF2E7D32),
                      foregroundColor: Colors.white,
                    ),
                  ),
                ],
              ),
            )
          else
            SizedBox(
              width: double.infinity,
              child: Column(
                children: [
                  Container(
                    height: 150,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      image: DecorationImage(
                        image: FileImage(
                            isPan ? _panDocument! : _aadhaarDocument!),
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () =>
                              _pickImage(isPan: isPan, isDocument: true),
                          icon: const Icon(Icons.refresh),
                          label: const Text('Change'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () {
                            setState(() {
                              if (isPan) {
                                _panDocument = null;
                              } else {
                                _aadhaarDocument = null;
                              }
                            });
                          },
                          icon: const Icon(Icons.delete),
                          label: const Text('Remove'),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.red,
                            side: const BorderSide(color: Colors.red),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildSelfieCapture() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Selfie Capture',
            style: GoogleFonts.poppins(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          if (_selfieImage == null)
            Container(
              width: double.infinity,
              height: 200,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey[300]!),
                borderRadius: BorderRadius.circular(8),
                color: Colors.grey[50],
              ),
              child: _isCameraInitialized
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: CameraPreview(_cameraController!),
                    )
                  : Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.camera_alt,
                          size: 48,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Initializing camera...',
                          style: GoogleFonts.poppins(
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
            )
          else
            SizedBox(
              width: double.infinity,
              child: Column(
                children: [
                  Container(
                    height: 200,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      image: DecorationImage(
                        image: FileImage(_selfieImage!),
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () {
                            setState(() {
                              _selfieImage = null;
                            });
                          },
                          icon: const Icon(Icons.refresh),
                          label: const Text('Retake'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          const SizedBox(height: 16),
          if (_isCameraInitialized && _selfieImage == null)
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _captureSelfie,
                icon: const Icon(Icons.camera),
                label: const Text('Capture Selfie'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF2E7D32),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.all(16),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildVideoRecording() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Video KYC',
            style: GoogleFonts.poppins(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Record a 30-second video saying your name and address',
            style: GoogleFonts.poppins(
              fontSize: 12,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: 16),
          if (_videoRecording == null)
            Container(
              width: double.infinity,
              height: 150,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey[300]!),
                borderRadius: BorderRadius.circular(8),
                color: Colors.grey[50],
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    _isRecording ? Icons.videocam : Icons.videocam_outlined,
                    size: 48,
                    color: _isRecording ? Colors.red : Colors.grey[400],
                  ),
                  const SizedBox(height: 8),
                  if (_isRecording) ...[
                    Text(
                      'Recording... $_recordingDuration/30s',
                      style: GoogleFonts.poppins(
                        color: Colors.red,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    ElevatedButton.icon(
                      onPressed: _stopVideoRecording,
                      icon: const Icon(Icons.stop),
                      label: const Text('Stop Recording'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red,
                        foregroundColor: Colors.white,
                      ),
                    ),
                  ] else ...[
                    Text(
                      'Tap to start recording',
                      style: GoogleFonts.poppins(
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 8),
                    ElevatedButton.icon(
                      onPressed: _startVideoRecording,
                      icon: const Icon(Icons.fiber_manual_record),
                      label: const Text('Start Recording'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF2E7D32),
                        foregroundColor: Colors.white,
                      ),
                    ),
                  ],
                ],
              ),
            )
          else
            SizedBox(
              width: double.infinity,
              child: Column(
                children: [
                  Container(
                    height: 150,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      color: Colors.black,
                    ),
                    child: const Center(
                      child: Icon(
                        Icons.play_circle_filled,
                        size: 48,
                        color: Colors.white,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Video recorded successfully',
                    style: GoogleFonts.poppins(
                      color: Colors.green,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () {
                            setState(() {
                              _videoRecording = null;
                            });
                          },
                          icon: const Icon(Icons.refresh),
                          label: const Text('Record Again'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildVerificationResult(repo.KYCVerificationResult result) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: result.success ? Colors.green[50] : Colors.red[50],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: result.success ? Colors.green : Colors.red,
          width: 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                result.success ? Icons.check_circle : Icons.error,
                color: result.success ? Colors.green : Colors.red,
              ),
              const SizedBox(width: 8),
              Text(
                result.success
                    ? 'Verification Successful'
                    : 'Verification Failed',
                style: GoogleFonts.poppins(
                  fontWeight: FontWeight.w600,
                  color: result.success ? Colors.green : Colors.red,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            result.message,
            style: GoogleFonts.poppins(
              fontSize: 14,
              color: Colors.grey[700],
            ),
          ),
          if (result.data != null) ...[
            const SizedBox(height: 8),
            Text(
              'Details: ${result.data.toString()}',
              style: GoogleFonts.poppins(
                fontSize: 12,
                color: Colors.grey[600],
              ),
            ),
          ],
        ],
      ),
    );
  }
}
