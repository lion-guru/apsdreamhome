import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';
import '../../widgets/app_widgets.dart';

/// Deal Pipeline - visual pipeline of deals across stages
class DealPipelinePage extends ConsumerStatefulWidget {
  const DealPipelinePage({super.key});

  @override
  ConsumerState<DealPipelinePage> createState() => _DealPipelinePageState();
}

class _DealPipelinePageState extends ConsumerState<DealPipelinePage> {
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _deals = [];
  String _selectedStage = 'all';

  static const _stages = [
    {'key': 'all', 'label': 'All Deals', 'icon': Icons.dashboard, 'color': AppTheme.primaryColor},
    {'key': 'lead', 'label': 'Lead', 'icon': Icons.person_search, 'color': AppTheme.infoColor},
    {'key': 'qualification', 'label': 'Qualified', 'icon': Icons.verified, 'color': Colors.purple},
    {'key': 'proposal', 'label': 'Proposal', 'icon': Icons.description, 'color': AppTheme.warningColor},
    {'key': 'negotiation', 'label': 'Negotiation', 'icon': Icons.handshake, 'color': AppTheme.accentColor},
    {'key': 'closed_won', 'label': 'Won', 'icon': Icons.check_circle, 'color': AppTheme.successColor},
    {'key': 'closed_lost', 'label': 'Lost', 'icon': Icons.cancel, 'color': AppTheme.errorColor},
  ];

  @override
  void initState() {
    super.initState();
    _loadDeals();
  }

  Future<void> _loadDeals() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final api = ApiService();
      final response = await api.get('/deals');
      final data = response['data'];
      if (data is List) {
        _deals = data.cast<Map<String, dynamic>>();
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  List<Map<String, dynamic>> get _filteredDeals {
    if (_selectedStage == 'all') return _deals;
    return _deals.where((d) => d['stage'] == _selectedStage).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Deal Pipeline'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(56),
          child: SizedBox(
            height: 56,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
              itemCount: _stages.length,
              itemBuilder: (context, index) {
                final stage = _stages[index];
                final isSelected = _selectedStage == stage['key'];
                final count = stage['key'] == 'all'
                    ? _deals.length
                    : _deals.where((d) => d['stage'] == stage['key']).length;

                return Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 4),
                  child: FilterChip(
                    selected: isSelected,
                    label: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          stage['icon'] as IconData,
                          size: 14,
                          color: isSelected ? Colors.white : (stage['color'] as Color),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '${stage['label']} ($count)',
                          style: TextStyle(
                            fontSize: 11,
                            color: isSelected ? Colors.white : Colors.grey.shade700,
                          ),
                        ),
                      ],
                    ),
                    selectedColor: stage['color'] as Color,
                    backgroundColor: Colors.white,
                    checkmarkColor: Colors.white,
                    onSelected: (selected) {
                      setState(() => _selectedStage = stage['key'] as String);
                    },
                  ),
                );
              },
            ),
          ),
        ),
      ),
      body: _isLoading
          ? Center(
              child: AppWidgets.shimmerLoading(
                child: Column(
                  children: List.generate(5, (i) => Container(
                    height: 90,
                    margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                  )),
                ),
              ),
            )
          : _error != null
              ? AppWidgets.errorWidget(
                  message: _error!,
                  onRetry: _loadDeals,
                )
              : _filteredDeals.isEmpty
                  ? AppWidgets.emptyState(
                      title: 'No deals found',
                      subtitle: 'Deals will appear here as you create them',
                      icon: Icons.local_shipping_outlined,
                    )
                  : RefreshIndicator(
                      onRefresh: _loadDeals,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(12),
                        itemCount: _filteredDeals.length,
                        itemBuilder: (context, index) {
                          final deal = _filteredDeals[index];
                          return _buildDealCard(deal);
                        },
                      ),
                    ),
    );
  }

  Widget _buildDealCard(Map<String, dynamic> deal) {
    final stage = (deal['stage'] ?? 'lead').toString();
    final stageInfo = _stages.firstWhere(
      (s) => s['key'] == stage,
      orElse: () => _stages[0],
    );
    final stageColor = stageInfo['color'] as Color;
    final double dealValue = ((deal['deal_value'] ?? deal['amount'] ?? 0) as num).toDouble();
    final double probability = ((deal['probability'] ?? 0) as num).toDouble();
    final customerName = (deal['customer_name'] ?? deal['name'] ?? 'Unknown').toString();
    final propertyName = (deal['property_name'] ?? deal['plot_number'] ?? '').toString();
    final createdAt = deal['created_at'];

    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: () => _showDealDetail(deal),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header: customer + stage badge
              Row(
                children: [
                  CircleAvatar(
                    radius: 18,
                    backgroundColor: stageColor.withValues(alpha: 0.1),
                    child: Icon(
                      stageInfo['icon'] as IconData,
                      color: stageColor,
                      size: 18,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          customerName,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                          ),
                        ),
                        if (propertyName.isNotEmpty)
                          Text(
                            propertyName,
                            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                          ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: stageColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      (stageInfo['label'] as String).toUpperCase(),
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: stageColor,
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 12),

              // Value + Probability + Date
              Row(
                children: [
                  // Deal value
                  if (dealValue > 0) ...[
                    Icon(Icons.currency_rupee, size: 16, color: AppTheme.successColor),
                    const SizedBox(width: 4),
                    Text(
                      _formatCurrency(dealValue),
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        color: AppTheme.successColor,
                      ),
                    ),
                    const SizedBox(width: 16),
                  ],

                  // Probability
                  if (probability > 0) ...[
                    Icon(Icons.percent, size: 14, color: AppTheme.warningColor),
                    const SizedBox(width: 4),
                    Text(
                      '${probability.toStringAsFixed(0)}%',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                        color: AppTheme.warningColor,
                      ),
                    ),
                    const Spacer(),
                  ] else
                    const Spacer(),

                  // Date
                  if (createdAt != null)
                    Text(
                      _formatDate(createdAt),
                      style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                    ),
                ],
              ),

              // Progress bar for probability
              if (probability > 0) ...[
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: probability / 100,
                    backgroundColor: Colors.grey.shade200,
                    valueColor: AlwaysStoppedAnimation<Color>(stageColor),
                    minHeight: 4,
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  void _showDealDetail(Map<String, dynamic> deal) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.6,
        minChildSize: 0.3,
        maxChildSize: 0.9,
        expand: false,
        builder: (context, scrollController) {
          return ListView(
            controller: scrollController,
            padding: const EdgeInsets.all(20),
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Text(
                (deal['customer_name'] ?? deal['name'] ?? 'Deal').toString(),
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              _detailRow('Stage', (deal['stage'] ?? 'lead').toString()),
              _detailRow('Value', '₹${_formatCurrency(double.tryParse(deal['deal_value'].toString()) ?? 0.0)}'),
              _detailRow('Probability', '${deal['probability'] ?? 0}%'),
              if (deal['property_name'] != null)
                _detailRow('Property', deal['property_name'].toString()),
              if (deal['notes'] != null && deal['notes'].toString().isNotEmpty)
                _detailRow('Notes', deal['notes'].toString()),
              const SizedBox(height: 20),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.close),
                      label: const Text('Close'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.edit),
                      label: const Text('Edit'),
                    ),
                  ),
                ],
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
  }

  String _formatCurrency(double amount) {
    if (amount >= 10000000) {
      return '${(amount / 10000000).toStringAsFixed(2)} Cr';
    } else if (amount >= 100000) {
      return '${(amount / 100000).toStringAsFixed(2)} L';
    } else if (amount >= 1000) {
      return '${(amount / 1000).toStringAsFixed(1)} K';
    }
    return amount.toStringAsFixed(0);
  }

  String _formatDate(dynamic date) {
    if (date is String) {
      try {
        final d = DateTime.parse(date);
        return '${d.day}/${d.month}/${d.year}';
      } catch (_) {
        return date;
      }
    }
    return '';
  }
}
