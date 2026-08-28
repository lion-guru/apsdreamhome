import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../data/repositories/kyc_repository_provider.dart';
import '../../../domain/models/kyc_models.dart';

/// KYC Status Tracking Page - Shows complete KYC verification status and history
class KYCStatusPage extends ConsumerStatefulWidget {
  const KYCStatusPage({super.key});

  @override
  ConsumerState<KYCStatusPage> createState() => _KYCStatusPageState();
}

class _KYCStatusPageState extends ConsumerState<KYCStatusPage>
    with TickerProviderStateMixin {
  KYCStatusModel? _kycStatus;
  bool _isLoading = true;
  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      duration: const Duration(seconds: 2),
      vsync: this,
    );
    _pulseAnimation = Tween<double>(begin: 0.8, end: 1.0).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
    _pulseController.repeat(reverse: true);
    _loadKYCStatus();
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
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

  Future<void> _refreshStatus() async {
    await _loadKYCStatus();
  }

  String _formatDate(DateTime? date) {
    if (date == null) return 'N/A';
    return '${date.day}/${date.month}/${date.year}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'KYC Status',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF2E7D32),
        elevation: 0,
        actions: [
          IconButton(
            onPressed: _refreshStatus,
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh Status',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _kycStatus == null
              ? _buildEmptyState()
              : RefreshIndicator(
                  onRefresh: _refreshStatus,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildOverallStatusCard(),
                        const SizedBox(height: 24),
                        _buildVerificationSteps(),
                        const SizedBox(height: 24),
                        _buildKYCDetails(),
                        const SizedBox(height: 24),
                        _buildTimeline(),
                        const SizedBox(height: 24),
                        _buildActionButtons(),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SvgPicture.asset(
              'assets/icons/kyc_empty.svg',
              height: 120,
              colorFilter: ColorFilter.mode(
                Colors.grey[400]!,
                BlendMode.srcIn,
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'No KYC Data Found',
              style: GoogleFonts.poppins(
                fontSize: 20,
                fontWeight: FontWeight.w600,
                color: Colors.grey[700],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Start your KYC verification process',
              style: GoogleFonts.poppins(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () {
                Navigator.pushReplacementNamed(context, '/kyc-verification');
              },
              icon: const Icon(Icons.verified_user),
              label: const Text('Start KYC'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF2E7D32),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOverallStatusCard() {
    final isCompleted = _kycStatus!.isCompleted;
    final isPending = _kycStatus!.status.name.toLowerCase().contains('pending');
    final isRejected = _kycStatus!.status.name.toLowerCase().contains('reject');

    final Color cardColor = isCompleted
        ? Colors.green[50]!
        : isRejected
            ? Colors.red[50]!
            : isPending
                ? Colors.orange[50]!
                : Colors.blue[50]!;

    final Color iconColor = isCompleted
        ? Colors.green
        : isRejected
            ? Colors.red
            : isPending
                ? Colors.orange
                : Colors.blue;

    final IconData statusIcon = isCompleted
        ? Icons.verified
        : isRejected
            ? Icons.cancel
            : isPending
                ? Icons.pending
                : Icons.hourglass_empty;

    final String statusText = isCompleted
        ? 'KYC Completed'
        : isRejected
            ? 'KYC Rejected'
            : isPending
                ? 'KYC In Progress'
                : 'KYC Pending';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: iconColor, width: 2),
        boxShadow: [
          BoxShadow(
            color: iconColor.withValues(alpha: 0.1),
            blurRadius: 20,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          AnimatedBuilder(
            animation: _pulseAnimation,
            builder: (context, child) {
              return Transform.scale(
                scale: isPending ? _pulseAnimation.value : 1.0,
                child: Icon(
                  statusIcon,
                  size: 64,
                  color: iconColor,
                ),
              );
            },
          ),
          const SizedBox(height: 16),
          Text(
            statusText,
            style: GoogleFonts.poppins(
              fontSize: 24,
              fontWeight: FontWeight.w700,
              color: iconColor,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Status: ${_kycStatus!.status.name}',
            style: GoogleFonts.poppins(
              fontSize: 16,
              color: Colors.grey[700],
            ),
          ),
          if (_kycStatus!.verifiedAt != null) ...[
            const SizedBox(height: 8),
            Text(
              'Verified: ${_formatDate(_kycStatus!.verifiedAt)}',
              style: GoogleFonts.poppins(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildVerificationSteps() {
    final steps = [
      {'name': 'PAN Verification', 'completed': _kycStatus!.panVerified},
      {
        'name': 'Aadhaar Verification',
        'completed': _kycStatus!.aadhaarVerified
      },
      {'name': 'Document Upload', 'completed': _kycStatus!.documentsUploaded},
      {'name': 'Face Matching', 'completed': _kycStatus!.faceMatched},
      {'name': 'Video KYC', 'completed': _kycStatus!.videoCompleted},
    ];

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
            'Verification Steps',
            style: GoogleFonts.poppins(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          ...steps.map((step) => _buildStepItem(
                name: step['name'] as String,
                completed: step['completed'] as bool,
                isLast: step == steps.last,
              )),
        ],
      ),
    );
  }

  Widget _buildStepItem({
    required String name,
    required bool completed,
    required bool isLast,
  }) {
    return Column(
      children: [
        Row(
          children: [
            Container(
              width: 24,
              height: 24,
              decoration: BoxDecoration(
                color: completed ? Colors.green : Colors.grey[300],
                shape: BoxShape.circle,
              ),
              child: completed
                  ? const Icon(
                      Icons.check,
                      size: 16,
                      color: Colors.white,
                    )
                  : null,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                name,
                style: GoogleFonts.poppins(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: completed ? Colors.black87 : Colors.grey[600],
                ),
              ),
            ),
            if (completed)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.green[100],
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'Done',
                  style: GoogleFonts.poppins(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Colors.green,
                  ),
                ),
              ),
          ],
        ),
        if (!isLast) ...[
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.only(left: 12),
            child: Container(
              width: 2,
              height: 20,
              color: completed ? Colors.green : Colors.grey[300],
            ),
          ),
          const SizedBox(height: 8),
        ],
      ],
    );
  }

  Widget _buildKYCDetails() {
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
            'KYC Details',
            style: GoogleFonts.poppins(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          _buildDetailRow('KYC ID', _kycStatus!.id ?? ''),
          _buildDetailRow('Status', _kycStatus!.status.name),
          _buildDetailRow('Created At', _formatDate(_kycStatus!.createdAt)),
          if (_kycStatus!.verifiedAt != null)
            _buildDetailRow('Verified At', _formatDate(_kycStatus!.verifiedAt)),
          if (_kycStatus!.panNumber != null)
            _buildDetailRow('PAN Number', _kycStatus!.panNumber!),
          if (_kycStatus!.aadhaarNumber != null)
            _buildDetailRow('Aadhaar Number', _kycStatus!.maskedAadhaar!),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: GoogleFonts.poppins(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: Colors.grey[600],
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: GoogleFonts.poppins(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: Colors.black87,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTimeline() {
    // Mock timeline data - in real app, this would come from API
    final timelineEvents = [
      {
        'title': 'KYC Initiated',
        'description': 'KYC verification process started',
        'time': _formatDate(_kycStatus!.createdAt),
        'type': 'info',
      },
      if (_kycStatus!.panVerified)
        {
          'title': 'PAN Verified',
          'description': 'PAN card verified successfully',
          'time': '2 hours ago',
          'type': 'success',
        },
      if (_kycStatus!.aadhaarVerified)
        {
          'title': 'Aadhaar Verified',
          'description': 'Aadhaar verified with OTP',
          'time': '1 hour ago',
          'type': 'success',
        },
      if (_kycStatus!.documentsUploaded)
        {
          'title': 'Documents Uploaded',
          'description': 'All documents uploaded successfully',
          'time': '45 minutes ago',
          'type': 'success',
        },
      if (_kycStatus!.isCompleted)
        {
          'title': 'KYC Completed',
          'description': 'All verification steps completed',
          'time': _formatDate(_kycStatus!.verifiedAt),
          'type': 'success',
        },
    ];

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
            'Activity Timeline',
            style: GoogleFonts.poppins(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          ...timelineEvents.map((event) => _buildTimelineItem(
                title: event['title'] as String,
                description: event['description'] as String,
                time: event['time'] as String,
                type: event['type'] as String,
                isLast: event == timelineEvents.last,
              )),
        ],
      ),
    );
  }

  Widget _buildTimelineItem({
    required String title,
    required String description,
    required String time,
    required String type,
    required bool isLast,
  }) {
    final Color eventColor = type == 'success'
        ? Colors.green
        : type == 'error'
            ? Colors.red
            : Colors.blue;

    return Column(
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 12,
              height: 12,
              decoration: BoxDecoration(
                color: eventColor,
                shape: BoxShape.circle,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: GoogleFonts.poppins(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    description,
                    style: GoogleFonts.poppins(
                      fontSize: 12,
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    time,
                    style: GoogleFonts.poppins(
                      fontSize: 11,
                      color: Colors.grey[500],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        if (!isLast) ...[
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.only(left: 6),
            child: Container(
              width: 2,
              height: 40,
              color: Colors.grey[300],
            ),
          ),
          const SizedBox(height: 16),
        ],
      ],
    );
  }

  Widget _buildActionButtons() {
    return Column(
      children: [
        if (!_kycStatus!.isCompleted)
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () {
                Navigator.pushReplacementNamed(context, '/kyc-verification');
              },
              icon: const Icon(Icons.edit),
              label: const Text('Continue KYC'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF2E7D32),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.all(16),
              ),
            ),
          ),
        if (_kycStatus!.isCompleted) ...[
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () {
                // Download KYC certificate
              },
              icon: const Icon(Icons.download),
              label: const Text('Download KYC Certificate'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF2E7D32),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.all(16),
              ),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () {
                // Share KYC status
              },
              icon: const Icon(Icons.share),
              label: const Text('Share KYC Status'),
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFF2E7D32),
                side: const BorderSide(color: Color(0xFF2E7D32)),
                padding: const EdgeInsets.all(16),
              ),
            ),
          ),
        ],
      ],
    );
  }
}
