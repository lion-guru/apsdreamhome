import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';
import '../../widgets/glass_card.dart';

class UserAgreementsPage extends ConsumerStatefulWidget {
  const UserAgreementsPage({super.key});

  @override
  ConsumerState<UserAgreementsPage> createState() => _UserAgreementsPageState();
}

class _UserAgreementsPageState extends ConsumerState<UserAgreementsPage> {
  final _api = ApiService();
  List<Map<String, dynamic>> _agreements = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadAgreements();
  }

  Future<void> _loadAgreements() async {
    try {
      final response = await _api.get('/user/agreements');
      if (response['success'] == true && response['data'] != null) {
        final data = response['data'];
        final agreements = (data is List ? data : data['agreements'] ?? []) as List;
        if (mounted) {
          setState(() {
            _agreements = agreements.cast<Map<String, dynamic>>();
            _isLoading = false;
            _error = null;
          });
        }
      } else {
        if (mounted) {
          setState(() {
            _isLoading = false;
            _error = (response['message'] as String?) ?? 'Failed to load agreements';
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _error = 'Error loading agreements: ${e.toString()}';
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: CustomScrollView(
            slivers: [
              SliverToBoxAdapter(child: _buildHeader(context)),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 40),
                sliver: _isLoading
                    ? SliverFillRemaining(
                        child: Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              CircularProgressIndicator(color: AppTheme.accentColor),
                              const SizedBox(height: 16),
                              Text(
                                'Loading your agreements...',
                                style: TextStyle(color: Colors.white70),
                              ),
                            ],
                          ),
                        ),
                      )
                    : _error != null
                        ? SliverFillRemaining(
                            child: Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.error_outline_rounded, size: 48, color: Colors.white54),
                                  const SizedBox(height: 16),
                                  Text(
                                    _error!,
                                    style: const TextStyle(color: Colors.white70),
                                    textAlign: TextAlign.center,
                                  ),
                                  const SizedBox(height: 16),
                                  ElevatedButton.icon(
                                    onPressed: _loadAgreements,
                                    icon: const Icon(Icons.refresh_rounded),
                                    label: const Text('Retry'),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: AppTheme.primaryColor,
                                      foregroundColor: Colors.white,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          )
                        : _agreements.isEmpty
                            ? SliverFillRemaining(
                                child: Center(
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Container(
                                        width: 80,
                                        height: 80,
                                        decoration: BoxDecoration(
                                          gradient: LinearGradient(
                                            colors: [
                                              AppTheme.primaryColor.withValues(alpha: 0.3),
                                              AppTheme.secondaryColor.withValues(alpha: 0.3),
                                            ],
                                          ),
                                          borderRadius: BorderRadius.circular(20),
                                        ),
                                        child: const Icon(
                                          Icons.description_rounded,
                                          size: 40,
                                          color: Colors.white54,
                                        ),
                                      ),
                                      const SizedBox(height: 16),
                                      Text(
                                        'No Agreements Yet',
                                        style: AppTheme.headlineMedium.copyWith(
                                          color: Colors.white,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      Text(
                                        'Your property agreements will appear here once you book a property.',
                                        style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 14),
                                        textAlign: TextAlign.center,
                                      ),
                                      const SizedBox(height: 24),
                                      ElevatedButton.icon(
                                        onPressed: () => context.go('/properties'),
                                        icon: const Icon(Icons.home_rounded),
                                        label: const Text('Browse Properties'),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: AppTheme.primaryColor,
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              )
                            : SliverList.separated(
                                itemCount: _agreements.length,
                                separatorBuilder: (_, _) => const SizedBox(height: 12),
                                itemBuilder: (context, index) =>
                                    _buildAgreementCard(context, _agreements[index]),
                              ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
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
                colors: [AppTheme.primaryColor, Color(0xFF1565C0)],
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: AppTheme.primaryColor.withValues(alpha: 0.3),
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
              'My Agreements',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'View and manage your property agreements',
            style: Theme.of(
              context,
            ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildAgreementCard(BuildContext context, Map<String, dynamic> agreement) {
    final status = agreement['status']?.toString() ?? 'pending';
    final isSigned = status == 'signed' || status == 'active' || status == 'published';
    final statusLabel = isSigned ? 'Signed' : 'Pending Signature';
    final statusColor = isSigned ? AppTheme.successColor : AppTheme.warningColor;
    final statusIcon = isSigned ? Icons.check_circle_rounded : Icons.pending_rounded;

    final plotNumber = agreement['plot_number']?.toString() ?? '';
    final colonyName = agreement['colony_name']?.toString() ?? '';
    final bookingNumber = agreement['booking_number']?.toString() ?? '';
    final totalValue = agreement['total_plot_value']?.toString() ?? '';
    final createdAt = agreement['created_at']?.toString() ?? '';
    final formattedDate = createdAt.isNotEmpty
        ? DateTime.tryParse(createdAt)?.toLocal().toString().split(' ')[0] ?? ''
        : '';

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
                  color: AppTheme.primaryColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.description_rounded,
                  color: AppTheme.primaryColor,
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Agreement #${agreement['id'] ?? ''}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: 14,
                      ),
                    ),
                    if (bookingNumber.isNotEmpty)
                      Text(
                        'Booking: $bookingNumber',
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.5),
                          fontSize: 12,
                        ),
                      ),
                    if (plotNumber.isNotEmpty && colonyName.isNotEmpty)
                      Text(
                        'Plot $plotNumber, $colonyName',
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.5),
                          fontSize: 12,
                        ),
                        maxLines: 1,
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
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(statusIcon, color: statusColor, size: 12),
                    const SizedBox(width: 4),
                    Text(
                      statusLabel,
                      style: TextStyle(
                        color: statusColor,
                        fontWeight: FontWeight.w600,
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          if (totalValue.isNotEmpty) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                Icon(Icons.currency_rupee_rounded, color: AppTheme.accentColor, size: 16),
                const SizedBox(width: 4),
                Text(
                  'Value: ₹${_formatCurrency(totalValue)}',
                  style: TextStyle(
                    color: AppTheme.accentColor,
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ],
          if (formattedDate.isNotEmpty) ...[
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(Icons.calendar_today_rounded, color: Colors.white54, size: 14),
                const SizedBox(width: 6),
                Text(
                  'Created: $formattedDate',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.5),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ],
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: () => _viewAgreement(context, agreement),
                    icon: const Icon(Icons.visibility_rounded, size: 16),
                    label: const Text('View'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.white,
                      side: BorderSide(color: Colors.white.withValues(alpha: 0.3)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              if (!isSigned)
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(10),
                        gradient: const LinearGradient(
                          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                        ),
                      ),
                      child: ElevatedButton.icon(
                        onPressed: () => _signAgreement(context, agreement),
                        icon: const Icon(Icons.draw_rounded, size: 16),
                        label: const Text('Sign'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          shadowColor: Colors.transparent,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ),
                  ),
              )
              else
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: OutlinedButton.icon(
                      onPressed: () => _downloadAgreement(context, agreement),
                      icon: const Icon(Icons.download_rounded, size: 16),
                      label: const Text('Download'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.successColor,
                        side: BorderSide(color: AppTheme.successColor.withValues(alpha: 0.5)),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
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

  void _viewAgreement(BuildContext context, Map<String, dynamic> agreement) {
    final agreementId = agreement['id']?.toString() ?? '';
    context.push('/user/agreements/$agreementId');
  }

  void _signAgreement(BuildContext context, Map<String, dynamic> agreement) {
    final agreementId = agreement['id']?.toString() ?? '';
    context.push('/document-esign/$agreementId');
  }

  void _downloadAgreement(BuildContext context, Map<String, dynamic> agreement) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Download functionality coming soon'),
        backgroundColor: AppTheme.infoColor,
      ),
    );
  }

  }

String _formatCurrency(String value) {
  final num = double.tryParse(value) ?? 0;
  if (num >= 10000000) {
    return '${(num / 10000000).toStringAsFixed(2)} Cr';
  } else if (num >= 100000) {
    return '${(num / 100000).toStringAsFixed(2)} L';
  }
  return num.toStringAsFixed(0);
}

String _getStatusLabel(Map<String, dynamic> agreement) {
  final status = agreement['status']?.toString() ?? 'pending';
  return status == 'signed' || status == 'active' || status == 'published' ? 'Signed' : 'Pending Signature';
}

bool _isAgreementSigned(Map<String, dynamic> agreement) {
  final status = agreement['status']?.toString() ?? '';
  return status == 'signed' || status == 'active' || status == 'published';
}

Color _getStatusColor(Map<String, dynamic> agreement) {
  final status = agreement['status']?.toString() ?? '';
  return (status == 'signed' || status == 'active' || status == 'published') ? AppTheme.successColor : AppTheme.warningColor;
}

IconData _getStatusIcon(Map<String, dynamic> agreement) {
  final status = agreement['status']?.toString() ?? '';
  return (status == 'signed' || status == 'active' || status == 'published') ? Icons.check_circle_rounded : Icons.pending_rounded;
}