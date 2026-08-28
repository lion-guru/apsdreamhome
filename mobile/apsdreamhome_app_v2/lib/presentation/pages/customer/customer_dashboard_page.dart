import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/glass_card.dart';
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
          ? const Center(child: CircularProgressIndicator())
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
            const Icon(Icons.error_outline, size: 48, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              'Something went wrong',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            Text(
              _error ?? 'Unknown error',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey[600]),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loadDashboard,
              child: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildWelcomeBanner(String name) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 50, 20, 24),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Welcome back,',
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.7),
              fontSize: 14,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            name,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'APS Dream Home',
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.8),
              fontSize: 13,
            ),
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
      padding: const EdgeInsets.all(16),
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
            '₹${_formatAmount(totalPaid)}',
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
        padding: const EdgeInsets.all(12),
        child: Column(
          children: [
            Icon(icon, color: color, size: 28),
            const SizedBox(height: 8),
            Text(
              value,
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(fontSize: 11, color: Colors.grey[600]),
              textAlign: TextAlign.center,
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
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: 4,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            children: [
              _buildActionTile(
                Icons.book_outlined,
                'My\nBookings',
                () => context.push('/customer-bookings'),
              ),
              _buildActionTile(
                Icons.payment,
                'Pay\nEMI',
                () => context.push('/emi-schedule'),
              ),
              _buildActionTile(
                Icons.apartment,
                'Browse\nColonies',
                () => context.push('/colonies'),
              ),
              _buildActionTile(
                Icons.calendar_month,
                'Schedule\nVisit',
                () => context.push('/site-visit'),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionTile(IconData icon, String label, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: AppTheme.primaryColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: AppTheme.primaryColor, size: 26),
          ),
          const SizedBox(height: 6),
          Text(
            label,
            style: const TextStyle(fontSize: 11),
            textAlign: TextAlign.center,
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
              const Text(
                'Your Bookings',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              TextButton(
                onPressed: () => context.push('/customer-bookings'),
                child: const Text('View All'),
              ),
            ],
          ),
          if (!isActive)
            GlassCard(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Center(
                  child: Column(
                    children: [
                      Icon(
                        Icons.home_outlined,
                        size: 48,
                        color: Colors.grey[400],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        'No active bookings yet',
                        style: TextStyle(color: Colors.grey[600], fontSize: 14),
                      ),
                      const SizedBox(height: 12),
                      ElevatedButton.icon(
                        onPressed: () => context.push('/colonies'),
                        icon: const Icon(Icons.search),
                        label: const Text('Browse Colonies'),
                      ),
                    ],
                  ),
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
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Need Help?',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              'Our team is here to assist you',
              style: TextStyle(fontSize: 13, color: Colors.grey[600]),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () =>
                        _launchUrl('tel:+91${AppConstants.supportPhone}'),
                    icon: const Icon(Icons.phone, size: 18),
                    label: const Text('Call Us'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _launchUrl(
                      'https://wa.me/91${AppConstants.supportPhone}?text=Hi, I need help',
                    ),
                    icon: const Icon(Icons.chat, size: 18, color: Colors.green),
                    label: const Text(
                      'WhatsApp',
                      style: TextStyle(color: Colors.green),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  String _formatAmount(double amount) {
    if (amount >= 10000000) {
      return '${(amount / 10000000).toStringAsFixed(2)}Cr';
    }
    if (amount >= 100000) {
      return '${(amount / 100000).toStringAsFixed(2)}L';
    }
    return amount.toStringAsFixed(0);
  }

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }
}
