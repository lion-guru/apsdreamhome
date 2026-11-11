import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/app_widgets.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class CustomerDashboardPage extends ConsumerStatefulWidget {
  const CustomerDashboardPage({super.key});

  @override
  ConsumerState<CustomerDashboardPage> createState() =>
      _CustomerDashboardPageState();
}

class _CustomerDashboardPageState extends ConsumerState<CustomerDashboardPage> {
  Map<String, dynamic>? _dashboardData;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    try {
      setState(() {
        _loading = true;
        _error = null;
      });
      final api = ref.read(apiServiceProvider);
      final res = await api.get('/dashboard');
      if (mounted) {
        setState(() {
          _dashboardData = (res['data'] as Map<String, dynamic>?) ?? res;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _loading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authProvider);
    final name = user?.name ?? 'Customer';

    return Scaffold(
      body: _loading
          ? Center(
              child: AppWidgets.shimmerLoading(
                child: Container(
                  height: 400,
                  margin: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              ),
            )
          : _error != null
          ? _buildError()
          : RefreshIndicator(
              onRefresh: _loadDashboard,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildWelcomeBanner(name),
                    _buildQuickStats(),
                    _buildQuickActions(),
                    _buildRecentBookings(),
                    _buildContactSupport(),
                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 48, color: AppTheme.errorColor),
            const SizedBox(height: 16),
            Text(
              'Something went wrong',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              _error ?? 'Unknown error',
              textAlign: TextAlign.center,
              style: AppTheme.bodyMedium.copyWith(color: AppTheme.textSecondaryLight),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loadDashboard,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Retry', style: TextStyle(fontWeight: FontWeight.w600)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildWelcomeBanner(String name) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.fromLTRB(20, 40, 20, 28),
      decoration: BoxDecoration(
        gradient: AppTheme.primaryGradient,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(32)),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primaryColor.withValues(alpha: 0.25),
            blurRadius: 24,
            spreadRadius: 0,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: Colors.white.withValues(alpha: 0.3),
                    width: 1,
                  ),
                ),
                child: const Icon(Icons.person_outline, size: 30, color: Colors.white),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Welcome back,',
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.75),
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        letterSpacing: 0.4,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 28,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppTheme.accentColor.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: AppTheme.accentColor.withValues(alpha: 0.3),
                          width: 1,
                        ),
                      ),
                      child: const Text(
                        'APS Dream Home',
                        style: TextStyle(
                          color: AppTheme.accentColor,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildQuickStats() {
    final data = _dashboardData ?? {};
    final bookings = (data['bookings'] as Map<String, dynamic>?) ?? {};
    final activeBookings = (bookings['active'] as int?) ?? 0;
    final totalPaid = ((bookings['total_value'] as num?) ?? 0).toDouble();
    final pendingEmis = (bookings['pending_emis'] as int?) ?? 0;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          _buildStatCard(
            'Active\nBookings',
            '$activeBookings',
            Icons.calendar_today,
            AppTheme.primaryColor,
          ),
          const SizedBox(width: 12),
          _buildStatCard(
            'Pending\nEMIs',
            '$pendingEmis',
            Icons.schedule,
            AppTheme.warningColor,
          ),
          const SizedBox(width: 12),
          _buildStatCard(
            'Total\nPaid',
            '₹${AppWidgets.formatCurrency(totalPaid)}',
            Icons.payment,
            AppTheme.successColor,
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(
    String label,
    String value,
    IconData icon,
    Color color,
  ) {
    return Expanded(
      child: GlassCard(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: color.withValues(alpha: 0.3),
                      width: 1,
                    ),
                  ),
                  child: Icon(icon, color: color, size: 20),
                ),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Icon(Icons.trending_up_rounded, color: color.withValues(alpha: 0.6), size: 14),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              value,
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: color,
                letterSpacing: -0.3,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: AppTheme.textSecondaryLight,
                height: 1.2,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickActions() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Quick Actions',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppTheme.textPrimaryLight),
          ),
          const SizedBox(height: 16),
          GridView.count(
            crossAxisCount: 4,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: 0.85,
            children: [
              _buildActionTile(Icons.book_outlined, 'My\nBookings', AppTheme.primaryColor, () => context.push('/customer-bookings')),
              _buildActionTile(Icons.payment, 'Pay\nEMI', AppTheme.warningColor, () => context.push('/emi-schedule')),
              _buildActionTile(Icons.receipt_long_rounded, 'Maintenance\nDues', AppTheme.secondaryColor, () => context.push('/maintenance-dues')),
              _buildActionTile(Icons.support_agent_rounded, 'Resident\nHelpdesk', Colors.red, () => context.push('/township-grievances')),
              _buildActionTile(Icons.directions_car_rounded, 'Free Car\nSite Visit', AppTheme.accentColor, () => context.push('/book-site-visit')),
              _buildActionTile(Icons.apartment, 'Browse\nTownships', AppTheme.infoColor, () => context.push('/colonies')),
              _buildActionTile(Icons.grid_view_rounded, 'Verified\nPlots', AppTheme.successColor, () => context.push('/plots')),
              _buildActionTile(Icons.domain_outlined, 'Township\nOps', Colors.purple, () => context.push('/township-operations')),
              _buildActionTile(Icons.wb_sunny_outlined, 'Solar\nSubsidy', Colors.orange, () => context.push('/solar-subsidy')),
              _buildActionTile(Icons.menu_book_rounded, 'Digital\nPassbook', AppTheme.goldColor, () => context.push('/customer-passbook')),
              _buildActionTile(Icons.swap_horiz_rounded, 'Plot\nTransfer', Colors.teal, () => context.push('/plot-transfers')),
              _buildActionTile(Icons.savings_rounded, 'Loan\nClose 5%', AppTheme.primaryColor, () => context.push('/loan-foreclosure')),
              _buildActionTile(Icons.public_rounded, 'NRI Global\nDesk', Colors.indigo, () => context.push('/nri-desk')),
              _buildActionTile(Icons.timeline_rounded, 'Milestone\nJourney', AppTheme.accentColor, () => context.push('/customer-journey/1')),
              _buildActionTile(Icons.event_available_rounded, 'Registry\nAppointment', AppTheme.secondaryColor, () => context.push('/registry-appointments')),
              _buildActionTile(Icons.how_to_reg, 'SRO Registry\nDesk', Colors.teal, () => context.push('/registry-desk')),
              _buildActionTile(Icons.videocam_rounded, 'Drone Site\nTimelapse', Colors.deepPurple, () => context.push('/colony-timelapse')),
              _buildActionTile(Icons.verified_outlined, 'Digital\nAllotment', AppTheme.successColor, () => context.push('/allotment-acceptance')),
              _buildActionTile(Icons.tune_rounded, 'Alert\nPreferences', Colors.pink, () => context.push('/notification-preferences')),
              _buildActionTile(Icons.gavel_rounded, 'Live Plot\nAuctions', Colors.orange, () => context.push('/property-auctions')),
              _buildActionTile(Icons.agriculture_rounded, 'Farmer\nLand Desk', AppTheme.goldColor, () => context.push('/farmer-desk')),
              _buildActionTile(Icons.handshake_rounded, 'VIP Executive\nMeeting', AppTheme.primaryColor, () => context.push('/meeting-scheduler')),
              _buildActionTile(Icons.psychology_outlined, 'AI Assistant', AppTheme.accentColor, () => context.push('/ai-chatbot')),
              _buildActionTile(Icons.recommend_outlined, 'For You', AppTheme.goldColor, () => context.push('/user/ai-suggestions')),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionTile(IconData icon, String label, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [color.withValues(alpha: 0.15), color.withValues(alpha: 0.05)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: color.withValues(alpha: 0.3), width: 1),
              boxShadow: [
                BoxShadow(color: color.withValues(alpha: 0.12), blurRadius: 8, spreadRadius: 0, offset: const Offset(0, 3)),
              ],
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(height: 6),
          Text(
            label,
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: FontWeight.w600,
              color: AppTheme.textPrimaryLight,
              height: 1.2,
            ),
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildRecentBookings() {
    final data = _dashboardData ?? {};
    final bookings = (data['bookings'] as Map<String, dynamic>?) ?? {};
    final isActive = ((bookings['active'] as int?) ?? 0) > 0;

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Your Bookings',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppTheme.textPrimaryLight),
              ),
              TextButton(
                onPressed: () => context.push('/customer-bookings'),
                style: TextButton.styleFrom(
                  foregroundColor: AppTheme.primaryColor,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                child: const Text('View All', style: TextStyle(fontWeight: FontWeight.w600)),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (!isActive)
            GlassCard(
              padding: const EdgeInsets.all(28),
              child: Center(
                child: Column(
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: AppTheme.goldColor.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Icon(Icons.home_outlined, size: 32, color: AppTheme.goldColor),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      'No active bookings yet',
                      style: TextStyle(color: AppTheme.textSecondaryLight, fontSize: 15, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Explore available plots and book your dream home',
                      style: TextStyle(color: Colors.grey[500], fontSize: 13),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton.icon(
                      onPressed: () => context.push('/colonies'),
                      icon: const Icon(Icons.search, size: 18),
                      label: const Text('Browse Colonies', style: TextStyle(fontWeight: FontWeight.w600)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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

  Widget _buildContactSupport() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: GlassCard(
        padding: const EdgeInsets.all(20),
        child: Row(
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                gradient: AppTheme.goldGradient,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(color: AppTheme.accentColor.withValues(alpha: 0.3), blurRadius: 12, spreadRadius: 0, offset: const Offset(0, 4)),
                ],
              ),
              child: const Icon(Icons.support_agent, color: Colors.white, size: 26),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Need Help?',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppTheme.textPrimaryLight),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Our team is here to assist you',
                    style: TextStyle(fontSize: 13, color: AppTheme.textSecondaryLight),
                  ),
                ],
              ),
            ),
            Row(
              children: [
                _supportButton(Icons.phone, 'Call Us', () => _launchUrl('tel:+91${AppConstants.supportPhone}')),
                const SizedBox(width: 10),
                _supportButton(Icons.chat, 'WhatsApp', () => _launchUrl('https://wa.me/91${AppConstants.supportPhone}?text=Hi, I need help')),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _supportButton(IconData icon, String label, VoidCallback onTap) {
    return OutlinedButton.icon(
      onPressed: onTap,
      icon: Icon(icon, size: 16, color: AppTheme.primaryColor),
      label: Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.primaryColor)),
      style: OutlinedButton.styleFrom(
        foregroundColor: AppTheme.primaryColor,
        side: BorderSide(color: AppTheme.primaryColor.withValues(alpha: 0.4), width: 1),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.03),
      ),
    );
  }

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }
}