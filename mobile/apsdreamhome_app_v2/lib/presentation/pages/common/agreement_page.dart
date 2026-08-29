import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';
import '../../widgets/glass_card.dart';

class AgreementPage extends ConsumerStatefulWidget {
  const AgreementPage({super.key});

  @override
  ConsumerState<AgreementPage> createState() => _AgreementPageState();
}

class _AgreementPageState extends ConsumerState<AgreementPage> {
  final _api = ApiService();
  List<Map<String, dynamic>> _documents = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDocuments();
  }

  Future<void> _loadDocuments() async {
    try {
      final response = await _api.get('/legal/documents');
      if (response['success'] == true && response['data'] != null) {
        final data = response['data'];
        final docs = (data is List ? data : data['documents'] ?? []) as List;
        if (mounted) {
          setState(() {
            _documents = docs.cast<Map<String, dynamic>>();
            _isLoading = false;
          });
        }
      } else {
        if (mounted) setState(() => _isLoading = false);
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  int get _signedCount => _documents
      .where((d) => (d['status']?.toString() ?? '') == 'signed')
      .length;
  int get _pendingCount => _documents
      .where((d) => (d['status']?.toString() ?? '') != 'signed')
      .length;

  void _showSignaturePad(BuildContext context, String title) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _SignaturePadSheet(
        documentTitle: title,
        onSigned: () {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Document signed successfully!'),
              backgroundColor: AppTheme.successColor,
            ),
          );
        },
      ),
    );
  }

  void _viewDocument(BuildContext context, String title) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: const Color(0xFF1A237E),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(title, style: const TextStyle(color: Colors.white)),
        content: const Text(
          'View the full document in the Legal Documents section.',
          style: TextStyle(color: Colors.white70),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text(
              'Cancel',
              style: TextStyle(color: Colors.white54),
            ),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              context.push('/legal-documents');
            },
            child: const Text(
              'Open Legal Documents',
              style: TextStyle(color: Color(0xFFFFD700)),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final total = _documents.isNotEmpty
        ? _documents.length
        : _agreements.length;
    final signed = _documents.isNotEmpty ? _signedCount : 2;
    final pending = total - signed;

    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(context),
                const SizedBox(height: 24),
                if (_isLoading)
                  const Center(
                    child: Padding(
                      padding: EdgeInsets.all(20),
                      child: CircularProgressIndicator(color: Colors.white38),
                    ),
                  )
                else ...[
                  _buildStatsRow(total, signed, pending),
                  const SizedBox(height: 24),
                  _buildSectionTitle('Your Agreements'),
                  const SizedBox(height: 16),
                  ..._agreements.asMap().entries.map(
                    (e) => Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _buildAgreementCard(e.value, _getDocStatus(e.key)),
                    ),
                  ),
                ],
                const SizedBox(height: 24),
                _buildSectionTitle('How E-Sign Works'),
                const SizedBox(height: 12),
                ..._esignSteps.map(
                  (s) => Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: _buildESignStep(s),
                  ),
                ),
                const SizedBox(height: 24),
                _buildFAQSection(),
                const SizedBox(height: 24),
                _buildESignDocumentsLink(),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }

  String _getDocStatus(int index) {
    if (_documents.isEmpty) return _defaultStatuses[index];
    if (index < _documents.length) {
      final status = _documents[index]['status']?.toString() ?? '';
      if (status == 'active' || status == 'published') return 'signed';
    }
    return 'pending_signature';
  }

  static const _defaultStatuses = [
    'pending_signature',
    'draft',
    'signed',
    'pending_signature',
    'signed',
  ];

  Widget _buildHeader(BuildContext context) {
    return Column(
      children: [
        GestureDetector(
          onTap: () => context.pop(),
          child: Align(
            alignment: Alignment.centerLeft,
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(
                Icons.arrow_back,
                color: Colors.white,
                size: 22,
              ),
            ),
          ),
        ),
        const SizedBox(height: 20),
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF1565C0), Color(0xFF42A5F5)],
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF1565C0).withValues(alpha: 0.3),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: const Icon(
            Icons.auto_stories_rounded,
            size: 40,
            color: Colors.white,
          ),
        ),
        const SizedBox(height: 16),
        ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            colors: [AppTheme.primaryColor, Color(0xFF1565C0)],
          ).createShader(bounds),
          child: Text(
            'Agreements & E-Sign',
            style: Theme.of(context).textTheme.headlineLarge?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'View, sign, and manage all your property agreements digitally',
          style: Theme.of(
            context,
          ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildStatsRow(int total, int signed, int pending) {
    return Row(
      children: [
        Expanded(
          child: _buildStatCard(
            'Total',
            '$total',
            Icons.description_rounded,
            AppTheme.primaryColor,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _buildStatCard(
            'Signed',
            '$signed',
            Icons.check_circle_rounded,
            AppTheme.successColor,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _buildStatCard(
            'Pending',
            '$pending',
            Icons.pending_rounded,
            AppTheme.warningColor,
          ),
        ),
      ],
    );
  }

  Widget _buildStatCard(
    String label,
    String count,
    IconData icon,
    Color color,
  ) {
    return GlassCard(
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
      opacity: 0.1,
      blur: 6,
      child: Column(
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 8),
          Text(
            count,
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.w800,
              fontSize: 22,
            ),
          ),
          Text(
            label,
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.6),
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: AppTheme.titleLarge.copyWith(
        color: Colors.white,
        fontWeight: FontWeight.w700,
      ),
    );
  }

  Widget _buildAgreementCard(_AgreementData agreement, String status) {
    final statusLabel = switch (status) {
      'signed' => 'Signed',
      'draft' => 'Draft',
      'pending_signature' => 'Pending',
      _ => 'Unknown',
    };
    final statusColor = switch (status) {
      'signed' => AppTheme.successColor,
      'draft' => AppTheme.infoColor,
      'pending_signature' => AppTheme.warningColor,
      _ => Colors.grey,
    };

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: agreement.color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(agreement.icon, color: agreement.color, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      agreement.title,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: 14,
                      ),
                    ),
                    Text(
                      agreement.description,
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.5),
                        fontSize: 12,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  statusLabel,
                  style: TextStyle(
                    color: statusColor,
                    fontWeight: FontWeight.w600,
                    fontSize: 11,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              if (status == 'pending_signature')
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(10),
                        gradient: const LinearGradient(
                          colors: [
                            AppTheme.primaryColor,
                            AppTheme.secondaryColor,
                          ],
                        ),
                      ),
                      child: ElevatedButton(
                        onPressed: () =>
                            _showSignaturePad(context, agreement.title),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          shadowColor: Colors.transparent,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                        child: const Text(
                          'E-Sign Now',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w600,
                            fontSize: 12,
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              if (status == 'pending_signature') const SizedBox(width: 8),
              Expanded(
                child: SizedBox(
                  height: 36,
                  child: OutlinedButton(
                    onPressed: () => _viewDocument(context, agreement.title),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.white,
                      side: BorderSide(
                        color: Colors.white.withValues(alpha: 0.3),
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: Text(
                      status == 'signed' ? 'Download' : 'View',
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildESignStep(_ESignStepData step) {
    return GlassCard(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      opacity: 0.06,
      blur: 6,
      child: Row(
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
              ),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Center(
              child: Text(
                '${step.number}',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                  fontSize: 14,
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              step.text,
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.8),
                fontSize: 13,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFAQSection() {
    return GlassCard(
      padding: const EdgeInsets.all(20),
      opacity: 0.15,
      blur: 10,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Frequently Asked Questions',
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w700,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 12),
          ..._faqItems.map(
            (faq) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    faq.question,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                      fontSize: 13,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    faq.answer,
                    style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.6),
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Data Classes ───

class _AgreementData {
  final String title;
  final String description;
  final IconData icon;
  final Color color;
  const _AgreementData(this.title, this.description, this.icon, this.color);
}

final _agreements = [
  const _AgreementData(
    'Plot Sale Agreement',
    'Standard sale agreement for residential plots with all terms and conditions.',
    Icons.description_rounded,
    Color(0xFFFF9800),
  ),
  const _AgreementData(
    'Construction Contract',
    'Agreement for construction services with material and timeline specifications.',
    Icons.construction_rounded,
    Color(0xFF2196F3),
  ),
  const _AgreementData(
    'Allotment Letter',
    'Official allotment letter confirming your property allocation.',
    Icons.check_circle_rounded,
    Color(0xFF4CAF50),
  ),
  const _AgreementData(
    'Maintenance Agreement',
    'Terms for common area maintenance and society charges.',
    Icons.handyman_rounded,
    Color(0xFF9C27B0),
  ),
  const _AgreementData(
    'Rental Agreement',
    'Leave and license agreement for rental properties.',
    Icons.real_estate_agent_rounded,
    Color(0xFF43A047),
  ),
];

class _ESignStepData {
  final int number;
  final String text;
  const _ESignStepData(this.number, this.text);
}

final _esignSteps = [
  const _ESignStepData(1, 'Review the agreement document carefully'),
  const _ESignStepData(2, 'Authenticate via Aadhaar OTP or mobile OTP'),
  const _ESignStepData(3, 'Apply your digital signature'),
  const _ESignStepData(4, 'Receive signed copy via email & document locker'),
];

class _FAQItem {
  final String question;
  final String answer;
  const _FAQItem(this.question, this.answer);
}

final _faqItems = [
  const _FAQItem(
    'What is E-Sign?',
    'E-Sign is a legally valid digital signature under the IT Act, 2000. It uses Aadhaar-based authentication to sign documents electronically.',
  ),
  const _FAQItem(
    'Is E-Sign legally binding?',
    'Yes, E-Sign documents are legally valid and enforceable in Indian courts under the Information Technology Act.',
  ),
  const _FAQItem(
    'How long does it take?',
    'The entire E-Sign process takes less than 5 minutes once you review the document.',
  ),
  const _FAQItem(
    'Can I download signed agreements?',
    'Yes, all signed agreements are available for download from the document locker section.',
  ),
];

// ─── Sign Documents Link ───

Widget _buildESignDocumentsLink() {
  return Builder(
    builder: (context) => Center(
      child: GestureDetector(
        onTap: () => GoRouter.of(context).go('/document-esign'),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [AppTheme.primaryColor, Color(0xFF1565C0)],
            ),
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: AppTheme.primaryColor.withValues(alpha: 0.4),
                blurRadius: 20,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.draw_rounded,
                color: Colors.white,
                size: 20,
              ),
              SizedBox(width: 8),
              Text(
                'Sign Documents Online →',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                ),
              ),
            ],
          ),
        ),
      ),
    ),
  );
}

// ─── Signature Pad Bottom Sheet ───

class _SignaturePadSheet extends StatefulWidget {
  final String documentTitle;
  final VoidCallback onSigned;

  const _SignaturePadSheet({
    required this.documentTitle,
    required this.onSigned,
  });

  @override
  State<_SignaturePadSheet> createState() => _SignaturePadSheetState();
}

class _SignaturePadSheetState extends State<_SignaturePadSheet> {
  final List<Offset?> _points = [];
  final List<Offset> _currentStroke = [];

  void _onPanStart(DragStartDetails details) {
    _currentStroke.clear();
    setState(() {
      _points.add(details.localPosition);
      _currentStroke.add(details.localPosition);
    });
  }

  void _onPanUpdate(DragUpdateDetails details) {
    setState(() {
      _points.add(details.localPosition);
      _currentStroke.add(details.localPosition);
    });
  }

  void _onPanEnd(DragEndDetails details) {
    setState(() => _points.add(null));
  }

  void _clearSignature() {
    setState(() => _points.clear());
  }

  @override
  Widget build(BuildContext context) {
    final isSigned = _points.length > 2;

    return Container(
      height: 440,
      decoration: const BoxDecoration(
        color: Color(0xFF0D1B3E),
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          // Handle
          Container(
            margin: const EdgeInsets.only(top: 8),
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.white24,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          // Title
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    widget.documentTitle,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                TextButton(
                  onPressed: _clearSignature,
                  child: const Text(
                    'Clear',
                    style: TextStyle(color: Colors.white54, fontSize: 13),
                  ),
                ),
              ],
            ),
          ),
          const Divider(color: Colors.white12, height: 1),
          // Signature pad
          Container(
            margin: const EdgeInsets.all(20),
            height: 140,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.white24),
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: GestureDetector(
                onPanStart: _onPanStart,
                onPanUpdate: _onPanUpdate,
                onPanEnd: _onPanEnd,
                child: CustomPaint(
                  painter: _SignaturePainter(_points),
                  size: const Size(double.infinity, 140),
                ),
              ),
            ),
          ),
          // Hint text
          const Text(
            'Sign above using your finger or stylus',
            style: TextStyle(color: Colors.white38, fontSize: 12),
          ),
          const SizedBox(height: 16),
          // Action buttons
          Row(
            children: [
              const SizedBox(width: 20),
              Expanded(
                child: SizedBox(
                  height: 48,
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.white,
                      side: const BorderSide(color: Colors.white24),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text('Cancel'),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: SizedBox(
                  height: 48,
                  child: ElevatedButton(
                    onPressed: isSigned
                        ? () {
                            widget.onSigned();
                            Navigator.pop(context);
                          }
                        : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1A237E),
                      disabledBackgroundColor: Colors.white12,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text(
                      'Sign Document',
                      style: TextStyle(fontWeight: FontWeight.w600),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 20),
            ],
          ),
          const SizedBox(height: 16),
        ],
      ),
    );
  }
}

class _SignaturePainter extends CustomPainter {
  final List<Offset?> points;
  _SignaturePainter(this.points);

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF1A237E)
      ..strokeWidth = 3
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    for (int i = 0; i < points.length - 1; i++) {
      if (points[i] != null && points[i + 1] != null) {
        canvas.drawLine(points[i]!, points[i + 1]!, paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant _SignaturePainter oldDelegate) => true;
}
