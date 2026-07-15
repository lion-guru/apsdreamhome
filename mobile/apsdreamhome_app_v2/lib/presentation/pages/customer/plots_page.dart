import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/colony_model.dart';
import '../../../data/models/plot_model.dart';
import '../../widgets/app_widgets.dart';

class PlotsPage extends ConsumerStatefulWidget {
  final String? colonyId;

  const PlotsPage({super.key, this.colonyId});

  @override
  ConsumerState<PlotsPage> createState() => _PlotsPageState();
}

class _PlotsPageState extends ConsumerState<PlotsPage> {
  String _selectedFilter = 'all';
  String? _selectedFacing;
  bool _cornerOnly = false;
  bool _parkFacingOnly = false;

  final List<String> _filters = [
    'all',
    'available',
    'hold',
    'booked',
    'sold',
    'premium',
  ];

  final List<String> _facings = ['North', 'South', 'East', 'West'];

  Widget _buildBody() {
    final colonyAsync = widget.colonyId != null
        ? ref.watch(colonyProvider(widget.colonyId!))
        : const AsyncValue.data(null);

    return colonyAsync.when(
      data: (colony) {
        if (colony == null && widget.colonyId != null) {
          return AppWidgets.errorWidget(
            message: 'Colony not found',
            onRetry: () => ref.refresh(colonyProvider(widget.colonyId!)),
          );
        }

        return RefreshIndicator(
          onRefresh: () async {
            if (widget.colonyId != null) {
              ref.invalidate(colonyProvider(widget.colonyId!));
              ref.invalidate(plotsProvider(widget.colonyId!));
            }
          },
          child: Column(
            children: [
              _buildLegend(),
              if (colony != null) _buildStatsBar(colony),
              _buildFilterChips(),
              Expanded(
                child: widget.colonyId != null
                    ? _buildPlotGrid()
                    : _buildAllPlotsList(),
              ),
            ],
          ),
        );
      },
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, stack) => AppWidgets.errorWidget(
        message: error.toString(),
        onRetry: () => ref.refresh(colonyProvider(widget.colonyId!)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Standalone (colony detail → plots): needs own Scaffold with AppBar
    if (widget.colonyId != null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Select Plot'),
          actions: [
            IconButton(
              onPressed: _showFilterBottomSheet,
              icon: const Icon(Icons.filter_list),
            ),
          ],
        ),
        body: _buildBody(),
      );
    }
    // Shell tab: body only (shell provides Scaffold/AppBar)
    return _buildBody();
  }

  Widget _buildLegend() {
    final items = [
      {'color': AppTheme.plotAvailable, 'label': 'Available'},
      {'color': AppTheme.plotHold, 'label': 'Hold'},
      {'color': AppTheme.plotBooked, 'label': 'Booked'},
      {'color': AppTheme.plotSold, 'label': 'Sold'},
    ];

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: items.map((item) {
          return Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 12,
                height: 12,
                decoration: BoxDecoration(
                  color: item['color'] as Color,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 4),
              Text(
                item['label'] as String,
                style: const TextStyle(fontSize: 11),
              ),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildStatsBar(ColonyModel colony) {
    return Container(
      padding: const EdgeInsets.all(16),
      color: Colors.grey.shade50,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _buildStatItem('Total', colony.totalPlots.toString()),
          _buildStatItem(
            'Available',
            colony.availablePlots.toString(),
            AppTheme.plotAvailable,
          ),
          _buildStatItem(
            'Hold',
            colony.holdPlots.toString(),
            AppTheme.plotHold,
          ),
          _buildStatItem(
            'Booked',
            colony.bookedPlots.toString(),
            AppTheme.plotBooked,
          ),
          _buildStatItem(
            'Sold',
            colony.soldPlots.toString(),
            AppTheme.plotSold,
          ),
        ],
      ),
    );
  }

  Widget _buildStatItem(String label, String value, [Color? color]) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: color ?? Colors.black87,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
        ),
      ],
    );
  }

  Widget _buildFilterChips() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            ..._filters.map((filter) {
              final isSelected = _selectedFilter == filter;
              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: ChoiceChip(
                  label: Text(filter.toUpperCase()),
                  selected: isSelected,
                  onSelected: (selected) {
                    setState(() => _selectedFilter = filter);
                  },
                ),
              );
            }),
          ],
        ),
      ),
    );
  }

  Widget _buildPlotGrid() {
    final plotsAsync = ref.watch(plotsProvider(widget.colonyId!));

    return plotsAsync.when(
      data: (plots) {
        // Apply filters
        var filteredPlots = plots;

        if (_selectedFilter != 'all' && _selectedFilter != 'premium') {
          filteredPlots = filteredPlots
              .where((p) => p.status == _selectedFilter)
              .toList();
        }

        if (_selectedFilter == 'premium') {
          filteredPlots = filteredPlots
              .where((p) => p.hasPremiumLocation)
              .toList();
        }

        if (_selectedFacing != null) {
          filteredPlots = filteredPlots
              .where((p) => p.facing == _selectedFacing)
              .toList();
        }

        if (_cornerOnly) {
          filteredPlots = filteredPlots
              .where((p) => p.isCorner == true)
              .toList();
        }

        if (_parkFacingOnly) {
          filteredPlots = filteredPlots
              .where((p) => p.isParkFacing == true)
              .toList();
        }

        if (filteredPlots.isEmpty) {
          return AppWidgets.emptyState(
            title: 'No Plots Found',
            subtitle: 'Try adjusting your filters',
            onAction: () {
              setState(() {
                _selectedFilter = 'all';
                _selectedFacing = null;
                _cornerOnly = false;
                _parkFacingOnly = false;
              });
            },
            actionLabel: 'Clear Filters',
          );
        }

        return GridView.builder(
          padding: const EdgeInsets.all(16),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 0.85,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
          ),
          itemCount: filteredPlots.length,
          itemBuilder: (context, index) {
            final plot = filteredPlots[index];
            return _buildPlotCard(plot);
          },
        );
      },
      loading: () => GridView.builder(
        padding: const EdgeInsets.all(16),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          childAspectRatio: 0.85,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
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
      ),
      error: (error, stack) => AppWidgets.errorWidget(
        message: error.toString(),
        onRetry: () => ref.refresh(plotsProvider(widget.colonyId!)),
      ),
    );
  }

  Widget _buildAllPlotsList() {
    final coloniesAsync = ref.watch(coloniesProvider);
    return coloniesAsync.when(
      data: (colonies) {
        if (colonies.isEmpty) {
          return AppWidgets.emptyState(
            title: 'No Colonies Found',
            subtitle: 'No colonies available at the moment',
          );
        }
        return RefreshIndicator(
          onRefresh: () async => ref.invalidate(coloniesProvider),
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: colonies.length,
            itemBuilder: (context, index) {
              final colony = colonies[index];
              return GestureDetector(
                onTap: () => context.push('/plots?colonyId=${colony.id}'),
                child: Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.06),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 56,
                        height: 56,
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              AppTheme.primaryColor,
                              AppTheme.secondaryColor,
                            ],
                          ),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(
                          Icons.apartment,
                          color: Colors.white,
                          size: 28,
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              colony.name,
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${colony.totalPlots} plots · ${colony.availablePlots} available',
                              style: TextStyle(
                                color: Colors.grey.shade600,
                                fontSize: 13,
                              ),
                            ),
                            if (colony.district.isNotEmpty)
                              Text(
                                colony.district,
                                style: TextStyle(
                                  color: Colors.grey.shade500,
                                  fontSize: 12,
                                ),
                              ),
                          ],
                        ),
                      ),
                      Icon(
                        Icons.arrow_forward_ios,
                        size: 16,
                        color: Colors.grey.shade400,
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        );
      },
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => AppWidgets.emptyState(
        title: 'Error loading colonies',
        subtitle: '$error',
      ),
    );
  }

  Widget _buildPlotCard(PlotModel plot) {
    Color statusColor;
    switch (plot.status) {
      case 'available':
        statusColor = AppTheme.plotAvailable;
        break;
      case 'hold':
        statusColor = AppTheme.plotHold;
        break;
      case 'booked':
        statusColor = AppTheme.plotBooked;
        break;
      case 'sold':
        statusColor = AppTheme.plotSold;
        break;
      default:
        statusColor = Colors.grey;
    }

    final canSelect = plot.isAvailable;

    return GestureDetector(
      onTap: canSelect
          ? () => _showPlotDetails(plot)
          : () {
              AppWidgets.showInfoSnackBar(
                context,
                'This plot is ${plot.status}',
              );
            },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: canSelect ? statusColor : Colors.grey.shade300,
            width: canSelect ? 2 : 1,
          ),
          boxShadow: [
            if (canSelect)
              BoxShadow(
                color: statusColor.withValues(alpha: 0.2),
                blurRadius: 8,
              ),
          ],
        ),
        child: Stack(
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header with status
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: const BorderRadius.vertical(
                      top: Radius.circular(11),
                    ),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Plot ${plot.plotNumber}',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      Container(
                        width: 12,
                        height: 12,
                        decoration: BoxDecoration(
                          color: statusColor,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ],
                  ),
                ),

                // Details
                Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Area
                      Row(
                        children: [
                          Icon(
                            Icons.square_foot,
                            size: 16,
                            color: Colors.grey.shade600,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            '${plot.areaSqft.toStringAsFixed(0)} sqft',
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey.shade700,
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 4),

                      // Facing
                      Row(
                        children: [
                          Icon(
                            Icons.compass_calibration_outlined,
                            size: 16,
                            color: Colors.grey.shade600,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            '${plot.facing} Facing',
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey.shade700,
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 8),

                      // Premium badges
                      if (plot.hasPremiumLocation)
                        Wrap(
                          spacing: 4,
                          runSpacing: 4,
                          children: [
                            if (plot.isCorner == true)
                              _buildPremiumBadge('Corner', Colors.orange),
                            if (plot.isParkFacing == true)
                              _buildPremiumBadge('Park', Colors.green),
                            if (plot.isMainRoadFacing == true)
                              _buildPremiumBadge('Main Road', Colors.blue),
                          ],
                        ),

                      const Spacer(),

                      // Price
                      AppWidgets.priceTag(
                        amount: plot.totalPrice,
                        prefix: '₹',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primaryColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),

            // Selection indicator
            if (canSelect)
              Positioned(
                top: 8,
                right: 8,
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: statusColor,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.check, color: Colors.white, size: 14),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildPremiumBadge(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 9,
          color: color,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  void _showPlotDetails(PlotModel plot) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Plot ${plot.plotNumber}',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  AppWidgets.statusBadge(status: plot.status),
                ],
              ),

              const SizedBox(height: 16),

              // Details Grid
              GridView.count(
                shrinkWrap: true,
                crossAxisCount: 2,
                childAspectRatio: 2.5,
                crossAxisSpacing: 16,
                mainAxisSpacing: 8,
                children: [
                  _buildDetailItem(
                    'Area',
                    '${plot.areaSqft.toStringAsFixed(0)} sqft',
                  ),
                  _buildDetailItem('Facing', plot.facing),
                  _buildDetailItem(
                    'Base Price',
                    '₹${plot.basePrice.toStringAsFixed(0)}',
                  ),
                  _buildDetailItem(
                    'Price/sqft',
                    '₹${plot.pricePerSqft.toStringAsFixed(0)}',
                  ),
                ],
              ),

              if (plot.hasPremiumLocation) ...[
                const SizedBox(height: 16),

                Text(
                  'Premium Location Benefits',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),

                const SizedBox(height: 8),

                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    if (plot.isCorner == true)
                      _buildBenefitChip('Corner Plot (+10%)', Icons.turn_right),
                    if (plot.isParkFacing == true)
                      _buildBenefitChip('Park Facing (+5%)', Icons.park),
                    if (plot.isMainRoadFacing == true)
                      _buildBenefitChip('Main Road (+8%)', Icons.add_road),
                  ],
                ),
              ],

              const SizedBox(height: 24),

              // Total Price
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.primaryColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Total Price',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                    AppWidgets.priceTag(
                      amount: plot.totalPrice,
                      prefix: '₹',
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryColor,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Action Buttons
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () {
                        Navigator.pop(context);
                        // Show on map
                      },
                      icon: const Icon(Icons.map_outlined),
                      label: const Text('View on Map'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: plot.isAvailable
                          ? () {
                              Navigator.pop(context);
                              context.push('/booking/${plot.id}');
                            }
                          : null,
                      icon: const Icon(Icons.bookmark_add_outlined),
                      label: const Text('Book Now'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDetailItem(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
        ),
      ],
    );
  }

  Widget _buildBenefitChip(String label, IconData icon) {
    return Chip(
      avatar: Icon(icon, size: 16),
      label: Text(label),
      backgroundColor: Colors.amber.withValues(alpha: 0.1),
    );
  }

  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Filters',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Facing Filter
                  Text(
                    'Facing',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),

                  const SizedBox(height: 12),

                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _facings.map((facing) {
                      final isSelected = _selectedFacing == facing;
                      return ChoiceChip(
                        label: Text(facing),
                        selected: isSelected,
                        onSelected: (selected) {
                          setModalState(() {
                            _selectedFacing = selected ? facing : null;
                          });
                        },
                      );
                    }).toList(),
                  ),

                  const SizedBox(height: 24),

                  // Premium Filters
                  Text(
                    'Premium Location',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),

                  const SizedBox(height: 12),

                  CheckboxListTile(
                    title: const Text('Corner Plot Only'),
                    value: _cornerOnly,
                    onChanged: (value) {
                      setModalState(() => _cornerOnly = value ?? false);
                    },
                  ),

                  CheckboxListTile(
                    title: const Text('Park Facing Only'),
                    value: _parkFacingOnly,
                    onChanged: (value) {
                      setModalState(() => _parkFacingOnly = value ?? false);
                    },
                  ),

                  const SizedBox(height: 24),

                  // Apply Button
                  SizedBox(
                    width: double.infinity,
                    height: 56,
                    child: ElevatedButton(
                      onPressed: () {
                        setState(() {});
                        Navigator.pop(context);
                      },
                      child: const Text(
                        'Apply Filters',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }
}
