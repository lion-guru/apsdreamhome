import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class UserInquiriesPage extends ConsumerStatefulWidget {
  const UserInquiriesPage({super.key});

  @override
  ConsumerState<UserInquiriesPage> createState() => _UserInquiriesPageState();
}

class _UserInquiriesPageState extends ConsumerState<UserInquiriesPage> {
  List<Map<String, dynamic>> _inquiries = [];
  bool _isLoading = true;
  String _selectedFilter = 'all';
  Dio get _dio => Dio(BaseOptions(baseUrl: AppConstants.baseUrl));

  @override
  void initState() {
    super.initState();
    _fetchInquiries();
  }

  Future<void> _fetchInquiries() async {
    setState(() => _isLoading = true);
    try {
      final token = await ref.read(authProvider.notifier).getToken();
      final params = <String, dynamic>{};
      if (_selectedFilter != 'all') {
        params['status'] = _selectedFilter;
      }
      final response = await _dio.get(
        '/api/v2/mobile/user/inquiries',
        queryParameters: params,
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      final resData = response.data as Map<String, dynamic>;
      if (resData['success'] == true) {
        final data = resData['data'] as List;
        setState(() {
          _inquiries = data.cast<Map<String, dynamic>>();
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load inquiries: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: Column(
            children: [
              _buildHeader(),
              _buildFilterTabs(),
              Expanded(
                child: _isLoading
                    ? const Center(child: CircularProgressIndicator(color: Colors.white))
                    : _inquiries.isEmpty
                        ? _buildEmptyState()
                        : _buildInquiriesList(),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
      child: Row(
        children: [
          GestureDetector(
            onTap: () => Navigator.pop(context),
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.arrow_back, color: Colors.white, size: 22),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: ShaderMask(
              shaderCallback: (bounds) => const LinearGradient(
                colors: [Colors.white, Color(0xFFE1BEE7)],
              ).createShader(bounds),
              child: Text(
                'My Inquiries',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterTabs() {
    final filters = [
      {'key': 'all', 'label': 'All'},
      {'key': 'new', 'label': 'New'},
      {'key': 'contacted', 'label': 'Contacted'},
      {'key': 'pending', 'label': 'Pending'},
      {'key': 'completed', 'label': 'Completed'},
    ];

    return Container(
      height: 50,
      margin: const EdgeInsets.symmetric(horizontal: 20),
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: filters.length,
        separatorBuilder: (_, _) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final filter = filters[index];
          final isSelected = _selectedFilter == filter['key'];
          return FilterChip(
            label: Text(filter['label']!, style: TextStyle(
              color: isSelected ? AppTheme.primaryColor : Colors.white,
              fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
            )),
            selected: isSelected,
            onSelected: (_) => setState(() {
              _selectedFilter = filter['key']!;
              _fetchInquiries();
            }),
            backgroundColor: Colors.white.withValues(alpha: 0.1),
            selectedColor: AppTheme.accentColor.withValues(alpha: 0.3),
            checkmarkColor: Colors.white,
            side: BorderSide(
              color: isSelected ? AppTheme.accentColor : Colors.white.withValues(alpha: 0.2),
            ),
          );
        },
      ),
    );
  }

  Widget _buildInquiriesList() {
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(20, 10, 20, 20),
      itemCount: _inquiries.length,
      itemBuilder: (context, index) {
        final inq = _inquiries[index];
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: _buildInquiryCard(inq),
        );
      },
    );
  }

  Widget _buildInquiryCard(Map<String, dynamic> inq) {
    final type = inq['type']?.toString() ?? 'general';
    final message = inq['message']?.toString() ?? '';
    final status = inq['status']?.toString() ?? 'new';
    final priority = inq['priority']?.toString() ?? 'medium';
    final createdAt = inq['created_at']?.toString() ?? '';

    final typeColors = {
      'property_listing': Colors.green,
      'property': Colors.blue,
      'general': Colors.orange,
    };
    final typeColor = typeColors[type] ?? Colors.grey;

    final statusColors = {
      'new': Colors.blue,
      'contacted': Colors.cyan,
      'pending': Colors.orange,
      'in_progress': Colors.orange,
      'completed': Colors.green,
      'cancelled': Colors.red,
    };
    final statusColor = statusColors[status] ?? Colors.grey;

    final priorityColors = {
      'high': Colors.red,
      'medium': Colors.orange,
      'low': Colors.cyan,
    };
    final priorityColor = priorityColors[priority] ?? Colors.grey;

    String dateStr = '';
    String timeStr = '';
    if (createdAt.isNotEmpty) {
      try {
        final dt = DateTime.parse(createdAt);
        dateStr = '${dt.day} ${_monthName(dt.month)} ${dt.year}';
        timeStr = '${dt.hour % 12 == 0 ? 12 : dt.hour % 12}:${dt.minute.toString().padLeft(2, '0')} ${dt.hour >= 12 ? 'PM' : 'AM'}';
      } catch (_) {
        dateStr = createdAt;
      }
    }

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
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: typeColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  type.replaceAll('_', ' ').toUpperCase(),
                  style: TextStyle(
                    color: typeColor,
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
              const Spacer(),
              Text(
                dateStr,
                style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            message.length > 120 ? '${message.substring(0, 120)}...' : message,
            style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 14, height: 1.4),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  status.replaceAll('_', ' ').toUpperCase(),
                  style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.w600),
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: priorityColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  priority.toUpperCase(),
                  style: TextStyle(color: priorityColor, fontSize: 10, fontWeight: FontWeight.w600),
                ),
              ),
              const Spacer(),
              Text(
                timeStr,
                style: TextStyle(color: Colors.white.withValues(alpha: 0.5), fontSize: 11),
              ),
            ],
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
          Icon(Icons.inbox_outlined, size: 64, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          Text(
            'No Inquiries Yet',
            style: TextStyle(color: Colors.grey.shade400, fontSize: 18, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 8),
          Text(
            'Your property inquiries will appear here',
            style: TextStyle(color: Colors.grey.shade600, fontSize: 14),
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: () => Navigator.pop(context),
            icon: const Icon(Icons.search_rounded, size: 18),
            label: const Text('Browse Properties'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.accentColor,
              foregroundColor: AppTheme.primaryColor,
            ),
          ),
        ],
      ),
    );
  }

  String _monthName(int m) {
    const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return months[m];
  }
}