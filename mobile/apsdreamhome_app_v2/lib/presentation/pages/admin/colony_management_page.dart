import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/colony_model.dart';
import '../../widgets/app_widgets.dart';

class ColonyManagementPage extends ConsumerStatefulWidget {
  const ColonyManagementPage({super.key});

  @override
  ConsumerState<ColonyManagementPage> createState() =>
      _ColonyManagementPageState();
}

class _ColonyManagementPageState extends ConsumerState<ColonyManagementPage> {
  String _searchQuery = '';
  String? _selectedState;
  String? _selectedStatus;

  @override
  Widget build(BuildContext context) {
    final coloniesAsync = ref.watch(coloniesProvider);

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(coloniesProvider),
      child: Stack(
        fit: StackFit.expand,
        children: [
          Column(
            children: [
              // Header Section
              _buildHeader(),

              // Stats Cards
              coloniesAsync.maybeWhen(
                data: (colonies) => _buildStatsRow(colonies),
                orElse: () => _buildStatsRow(const []),
              ),

              // Filters
              _buildFilters(),

              // Colonies List
              Expanded(
                child: coloniesAsync.when(
                  data: (colonies) {
                    var filtered = colonies;

                    if (_searchQuery.isNotEmpty) {
                      filtered = filtered
                          .where(
                            (c) =>
                                c.name.toLowerCase().contains(
                                  _searchQuery.toLowerCase(),
                                ) ||
                                c.location.toLowerCase().contains(
                                  _searchQuery.toLowerCase(),
                                ),
                          )
                          .toList();
                    }

                    if (_selectedState != null) {
                      filtered = filtered
                          .where((c) => c.state == _selectedState)
                          .toList();
                    }

                    if (_selectedStatus != null) {
                      filtered = filtered
                          .where((c) => c.status == _selectedStatus)
                          .toList();
                    }

                    return _buildColoniesGrid(filtered);
                  },
                  loading: () => _buildLoadingGrid(),
                  error: (error, stack) => AppWidgets.errorWidget(
                    message: error.toString(),
                    onRetry: () => ref.refresh(coloniesProvider),
                  ),
                ),
              ),
            ],
          ),
          Positioned(
            right: 16,
            bottom: 16,
            child: FloatingActionButton.extended(
              onPressed: () => _showAddColonyDialog(),
              icon: const Icon(Icons.add),
              label: const Text('Add Colony'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(
                Icons.location_city,
                size: 28,
                color: AppTheme.primaryColor,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Colony Management',
                      style: TextStyle(
                        fontSize: 19,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Manage colonies, plots and master plans',
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => _showImportDialog(),
                  icon: const Icon(Icons.upload_file),
                  label: const Text('Import'),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => _showExportDialog(),
                  icon: const Icon(Icons.download),
                  label: const Text('Export'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatsRow(List<ColonyModel> colonies) {
    final active = colonies.where((c) => c.status == 'active').length;
    final upcoming = colonies
        .where((c) => c.status == 'upcoming' || c.status == 'launching')
        .length;
    final soldOut = colonies.where((c) => c.status == 'sold_out').length;

    final stats = [
      {
        'label': 'Total',
        'value': '${colonies.length}',
        'icon': Icons.location_city,
        'color': AppTheme.primaryColor,
      },
      {
        'label': 'Active',
        'value': '$active',
        'icon': Icons.check_circle,
        'color': AppTheme.successColor,
      },
      {
        'label': 'Launching',
        'value': '$upcoming',
        'icon': Icons.rocket_launch,
        'color': AppTheme.infoColor,
      },
      {
        'label': 'Sold Out',
        'value': '$soldOut',
        'icon': Icons.sell,
        'color': AppTheme.warningColor,
      },
    ];

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
      child: GridView.count(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        crossAxisCount: 2,
        mainAxisSpacing: 12,
        crossAxisSpacing: 12,
        childAspectRatio: 2.4,
        children: stats.map((stat) {
          return Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 10,
                ),
              ],
            ),
            child: Row(
              children: [
                Icon(
                  stat['icon'] as IconData,
                  color: stat['color'] as Color,
                  size: 26,
                ),
                const SizedBox(width: 12),
                Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      stat['value'] as String,
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: stat['color'] as Color,
                      ),
                    ),
                    Text(
                      stat['label'] as String,
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildFilters() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        children: [
          // Search
          TextField(
            onChanged: (value) => setState(() => _searchQuery = value),
            decoration: InputDecoration(
              hintText: 'Search colonies...',
              prefixIcon: const Icon(Icons.search),
              filled: true,
              fillColor: Colors.grey.shade100,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: BorderSide.none,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              // State Filter
              Expanded(
                child: DropdownButtonFormField<String>(
                  initialValue: _selectedState,
                  isExpanded: true,
                  hint: const Text(
                    'All States',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  decoration: InputDecoration(
                    isDense: true,
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 14,
                    ),
                    filled: true,
                    fillColor: Colors.grey.shade100,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  items: const [
                    DropdownMenuItem(value: null, child: Text('All States')),
                    DropdownMenuItem(
                      value: 'Uttar Pradesh',
                      child: Text('Uttar Pradesh'),
                    ),
                    DropdownMenuItem(value: 'Bihar', child: Text('Bihar')),
                    DropdownMenuItem(
                      value: 'Madhya Pradesh',
                      child: Text('Madhya Pradesh'),
                    ),
                  ],
                  onChanged: (value) => setState(() => _selectedState = value),
                ),
              ),
              const SizedBox(width: 8),
              // Status Filter
              Expanded(
                child: DropdownButtonFormField<String>(
                  initialValue: _selectedStatus,
                  isExpanded: true,
                  hint: const Text(
                    'All Status',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  decoration: InputDecoration(
                    isDense: true,
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 14,
                    ),
                    filled: true,
                    fillColor: Colors.grey.shade100,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  items: const [
                    DropdownMenuItem(value: null, child: Text('All Status')),
                    DropdownMenuItem(value: 'active', child: Text('Active')),
                    DropdownMenuItem(
                      value: 'launching',
                      child: Text('Launching'),
                    ),
                    DropdownMenuItem(value: 'upcoming', child: Text('Upcoming')),
                    DropdownMenuItem(
                      value: 'sold_out',
                      child: Text('Sold Out'),
                    ),
                  ],
                  onChanged: (value) =>
                      setState(() => _selectedStatus = value),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildColoniesGrid(List<ColonyModel> colonies) {
    if (colonies.isEmpty) {
      return AppWidgets.emptyState(
        title: 'No Colonies Found',
        subtitle: 'Add your first colony to get started',
        onAction: () => _showAddColonyDialog(),
        actionLabel: 'Add Colony',
      );
    }

    return LayoutBuilder(
      builder: (context, constraints) {
        final width = constraints.maxWidth;
        final crossAxisCount = width >= 1100 ? 3 : (width >= 650 ? 2 : 1);
        final childAspectRatio = width >= 1100 ? 1.2 : (width >= 650 ? 0.98 : 1.0);
        return GridView.builder(
          padding: const EdgeInsets.all(16),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: crossAxisCount,
            childAspectRatio: childAspectRatio,
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
          ),
          itemCount: colonies.length,
          itemBuilder: (context, index) {
            final colony = colonies[index];
            return _buildColonyCard(colony);
          },
        );
      },
    );
  }

  Widget _buildColonyCard(ColonyModel colony) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image Header
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(12),
                ),
                child: Container(
                  height: 120,
                  width: double.infinity,
                  color: Colors.grey.shade200,
                  child: colony.images != null && colony.images!.isNotEmpty
                      ? Image.network(colony.images!.first, fit: BoxFit.cover)
                      : const Icon(
                          Icons.home_work,
                          size: 50,
                          color: Colors.grey,
                        ),
                ),
              ),
              Positioned(
                top: 8,
                right: 8,
                child: AppWidgets.statusBadge(status: colony.status),
              ),
            ],
          ),

          // Content
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  colony.name,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                if (colony.location.isNotEmpty ||
                    colony.state.isNotEmpty ||
                    colony.district.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    [
                      colony.location,
                      colony.state,
                      if (colony.location.isEmpty && colony.state.isEmpty)
                        colony.district,
                    ]
                        .where((s) => s.isNotEmpty)
                        .join(', '),
                    style: TextStyle(
                      color: Colors.grey.shade600,
                      fontSize: 13,
                    ),
                  ),
                ],
                const SizedBox(height: 12),

                // Stats Row
                Row(
                  children: [
                    _buildStatChip(
                      'Total: ${colony.totalPlots}',
                      Icons.grid_view,
                    ),
                    const SizedBox(width: 8),
                    _buildStatChip(
                      'Avail: ${colony.availablePlots}',
                      Icons.check_circle,
                      color: AppTheme.successColor,
                    ),
                  ],
                ),

                const SizedBox(height: 12),

                // Price & Progress
                Row(
                  children: [
                    AppWidgets.priceTag(
                      amount: colony.pricePerSqft,
                      prefix: '₹',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryColor,
                      ),
                    ),
                    const Spacer(),
                    SizedBox(
                      width: 100,
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: LinearProgressIndicator(
                          value: colony.progressPercentage / 100,
                          backgroundColor: Colors.grey.shade200,
                          valueColor: AlwaysStoppedAnimation<Color>(
                            colony.isSoldOut
                                ? AppTheme.errorColor
                                : AppTheme.successColor,
                          ),
                          minHeight: 6,
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 12),

                // Actions
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _showEditColonyDialog(colony),
                        icon: const Icon(Icons.edit, size: 16),
                        label: const Text('Edit'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _showManagePlots(colony),
                        icon: const Icon(Icons.map, size: 16),
                        label: const Text('Plots'),
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

  Widget _buildStatChip(String label, IconData icon, {Color? color}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: (color ?? Colors.grey).withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: color ?? Colors.grey),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 11,
              color: color ?? Colors.grey.shade700,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoadingGrid() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final width = constraints.maxWidth;
        final crossAxisCount = width >= 1100 ? 3 : (width >= 650 ? 2 : 1);
        final childAspectRatio = width >= 1100 ? 1.2 : (width >= 650 ? 0.98 : 1.0);
        return GridView.builder(
          padding: const EdgeInsets.all(16),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: crossAxisCount,
            childAspectRatio: childAspectRatio,
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
          ),
          itemCount: 6,
          itemBuilder: (context, index) {
            return AppWidgets.shimmerLoading(
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showAddColonyDialog() {
    // Add colony dialog
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add New Colony'),
        content: const Text('Colony add form will open here'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }

  void _showEditColonyDialog(ColonyModel colony) {
    final url = Uri.parse(
      '${AppConstants.baseUrl}/admin/locations/colonies/${colony.id}/edit',
    );
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Edit "${colony.name}" on web admin panel'),
        action: SnackBarAction(
          label: 'Open Web',
          onPressed: () async {
            if (await canLaunchUrl(url)) {
              await launchUrl(url, mode: LaunchMode.externalApplication);
            }
          },
        ),
      ),
    );
  }

  void _showManagePlots(ColonyModel colony) {
    context.push(
      '/colony-plots/${colony.id}',
      extra: {'colonyName': colony.name},
    );
  }

  void _showImportDialog() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text(
          'Use the web admin panel to import colony data (CSV format)',
        ),
      ),
    );
  }

  void _showExportDialog() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Use the web admin panel to export colony data'),
      ),
    );
  }
}
