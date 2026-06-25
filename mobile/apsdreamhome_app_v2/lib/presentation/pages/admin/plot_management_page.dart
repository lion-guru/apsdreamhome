import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';
import '../../../data/services/colony_service.dart';

final _plotsProvider = FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  try {
    final api = ApiService();
    final response = await api.get('/admin/plots');
    if (response['success'] == true && response['data'] != null) {
      return (response['data'] as List).cast<Map<String, dynamic>>();
    }
    if (response['data'] is List) {
      return (response['data'] as List).cast<Map<String, dynamic>>();
    }
    return [];
  } catch (e) {
    AppLogger.error('Error fetching plots', e);
    return [];
  }
});

final _coloniesProvider = FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  try {
    final colonyService = ColonyService();
    final colonies = await colonyService.getColonies();
    return colonies.map((c) => {
      'id': c.id,
      'name': c.name,
      'total_plots': c.totalPlots,
    }).toList();
  } catch (e) {
    AppLogger.error('Error fetching colonies for plot filter', e);
    return [];
  }
});

class PlotManagementPage extends ConsumerStatefulWidget {
  const PlotManagementPage({super.key});

  @override
  ConsumerState<PlotManagementPage> createState() => _PlotManagementPageState();
}

class _PlotManagementPageState extends ConsumerState<PlotManagementPage> {
  String _filterStatus = 'all';
  String _filterColony = 'all';
  String _searchQuery = '';

  @override
  Widget build(BuildContext context) {
    final plotsAsync = ref.watch(_plotsProvider);
    final coloniesAsync = ref.watch(_coloniesProvider);

    return Column(
      children: [
        _buildHeader(),
        _buildStatsRow(plotsAsync),
        _buildFilters(coloniesAsync),
        Expanded(
          child: plotsAsync.when(
            data: (plots) {
              var filtered = plots;
              if (_filterStatus != 'all') {
                filtered = filtered.where((p) => p['status'] == _filterStatus).toList();
              }
              if (_filterColony != 'all') {
                filtered = filtered.where((p) => p['colony_id']?.toString() == _filterColony).toList();
              }
              if (_searchQuery.isNotEmpty) {
                final q = _searchQuery.toLowerCase();
                filtered = filtered.where((p) =>
                  (p['plot_number']?.toString().toLowerCase().contains(q) ?? false) ||
                  (p['colony_name']?.toString().toLowerCase().contains(q) ?? false) ||
                  (p['block_name']?.toString().toLowerCase().contains(q) ?? false)
                ).toList();
              }
              if (filtered.isEmpty) return _buildEmptyState();
              return _buildPlotsList(filtered);
            },
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (e, _) => Center(child: Text('Error: $e')),
          ),
        ),
      ],
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Plot Inventory',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Text('Manage plot inventory across all colonies',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey[600])),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.invalidate(_plotsProvider);
              ref.invalidate(_coloniesProvider);
            },
            tooltip: 'Refresh',
          ),
        ],
      ),
    );
  }

  Widget _buildStatsRow(AsyncValue<List<Map<String, dynamic>>> plotsAsync) {
    return plotsAsync.when(
      data: (plots) {
        final total = plots.length;
        final available = plots.where((p) => p['status'] == 'available').length;
        final booked = plots.where((p) => p['status'] == 'booked').length;
        final totalValue = plots.fold<double>(0, (sum, p) {
          final price = (p['total_price'] as num?)?.toDouble() ?? 0;
          return sum + price;
        });
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
          child: Row(
            children: [
              _buildStatCard('Total', '$total', Colors.blue),
              const SizedBox(width: 8),
              _buildStatCard('Available', '$available', Colors.green),
              const SizedBox(width: 8),
              _buildStatCard('Booked', '$booked', Colors.orange),
              const SizedBox(width: 8),
              _buildStatCard('Value', '₹${_formatAmount(totalValue)}', Colors.purple),
            ],
          ),
        );
      },
      loading: () => const SizedBox.shrink(),
      error: (_, __) => const SizedBox.shrink(),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Column(
          children: [
            Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
            const SizedBox(height: 2),
            Text(label, style: TextStyle(fontSize: 11, color: color.withValues(alpha: 0.7))),
          ],
        ),
      ),
    );
  }

  Widget _buildFilters(AsyncValue<List<Map<String, dynamic>>> coloniesAsync) {
    final statuses = ['all', 'available', 'booked', 'sold', 'hold', 'blocked'];
    return SizedBox(
      height: 48,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
        children: [
          for (final s in statuses)
            Padding(
              padding: const EdgeInsets.only(right: 8),
              child: FilterChip(
                label: Text(s[0].toUpperCase() + s.substring(1),
                  style: TextStyle(fontSize: 12, color: _filterStatus == s ? Colors.white : Colors.grey[700])),
                selected: _filterStatus == s,
                onSelected: (_) => setState(() => _filterStatus = s),
                selectedColor: Colors.blue,
                backgroundColor: Colors.grey[100],
                materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                visualDensity: VisualDensity.compact,
              ),
            ),
          const SizedBox(width: 8),
          coloniesAsync.when(
            data: (colonies) => Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey[300]!),
                borderRadius: BorderRadius.circular(20),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: _filterColony,
                  isDense: true,
                  items: [
                    const DropdownMenuItem(value: 'all', child: Text('All Colonies', style: TextStyle(fontSize: 12))),
                    ...colonies.map((c) => DropdownMenuItem(
                      value: c['id'].toString(),
                      child: Text(c['name']?.toString() ?? '', style: const TextStyle(fontSize: 12)),
                    )),
                  ],
                  onChanged: (v) => setState(() => _filterColony = v ?? 'all'),
                ),
              ),
            ),
            loading: () => const SizedBox.shrink(),
            error: (_, __) => const SizedBox.shrink(),
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
          Icon(Icons.landscape_outlined, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text('No plots found', style: TextStyle(fontSize: 16, color: Colors.grey[600])),
          const SizedBox(height: 8),
          Text('Try adjusting your filters', style: TextStyle(fontSize: 13, color: Colors.grey[500])),
        ],
      ),
    );
  }

  Widget _buildPlotsList(List<Map<String, dynamic>> plots) {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      itemCount: plots.length,
      itemBuilder: (ctx, i) => _buildPlotCard(plots[i]),
    );
  }

  Widget _buildPlotCard(Map<String, dynamic> plot) {
    final status = plot['status']?.toString() ?? 'unknown';
    final statusColor = _statusColor(status);
    final plotNumber = plot['plot_number']?.toString() ?? 'N/A';
    final colony = plot['colony_name']?.toString() ?? '';
    final block = plot['block_name']?.toString() ?? '';
    final area = (plot['area_sqft'] as num?)?.toDouble() ?? 0;
    final price = (plot['total_price'] as num?)?.toDouble() ?? 0;
    final width = plot['width_ft']?.toString() ?? '';
    final length = plot['length_ft']?.toString() ?? '';

    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 42, height: 42,
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Center(
                    child: Text(plotNumber.split('-').last.length > 3
                      ? plotNumber.substring(plotNumber.length - 3)
                      : plotNumber,
                      style: TextStyle(fontWeight: FontWeight.bold, color: statusColor, fontSize: 13)),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(plotNumber, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
                      const SizedBox(height: 2),
                      if (colony.isNotEmpty)
                        Text('$colony${block.isNotEmpty ? ' • $block' : ''}',
                          style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: statusColor.withValues(alpha: 0.3)),
                  ),
                  child: Text(status.toUpperCase(),
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: statusColor)),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                _buildDetailChip(Icons.straighten, '${_formatNumber(area)} sqft'),
                if (width.isNotEmpty && length.isNotEmpty) ...[
                  const SizedBox(width: 12),
                  _buildDetailChip(Icons.crop, '$width×$length ft'),
                ],
                const SizedBox(width: 12),
                _buildDetailChip(Icons.currency_rupee, '₹${_formatAmount(price)}'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailChip(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: Colors.grey[600]),
        const SizedBox(width: 4),
        Text(text, style: TextStyle(fontSize: 12, color: Colors.grey[700])),
      ],
    );
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'available': return Colors.green;
      case 'booked': return Colors.orange;
      case 'sold': return Colors.blue;
      case 'hold': return Colors.amber;
      case 'blocked': return Colors.red;
      default: return Colors.grey;
    }
  }

  String _formatAmount(double amount) {
    if (amount >= 10000000) return '${(amount / 10000000).toStringAsFixed(2)} Cr';
    if (amount >= 100000) return '${(amount / 100000).toStringAsFixed(2)} L';
    if (amount >= 1000) return '${(amount / 1000).toStringAsFixed(1)}K';
    return amount.toStringAsFixed(0);
  }

  String _formatNumber(double n) {
    if (n == n.roundToDouble()) return n.toInt().toString();
    return n.toStringAsFixed(1);
  }
}
