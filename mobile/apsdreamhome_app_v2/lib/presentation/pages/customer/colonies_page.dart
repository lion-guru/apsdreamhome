import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/colony_model.dart';
import '../../widgets/app_widgets.dart';

class ColoniesPage extends ConsumerStatefulWidget {
  const ColoniesPage({super.key});

  @override
  ConsumerState<ColoniesPage> createState() => _ColoniesPageState();
}

class _ColoniesPageState extends ConsumerState<ColoniesPage> {
  String _searchQuery = '';
  String? _selectedStatus;
  String? _selectedState;

  @override
  Widget build(BuildContext context) {
    final coloniesAsync = ref.watch(coloniesProvider);

    return Column(
      children: [
        // Search Bar
        Padding(
          padding: const EdgeInsets.all(16),
          child: TextField(
            onChanged: (value) {
              setState(() => _searchQuery = value);
            },
            decoration: InputDecoration(
              hintText: 'Search colonies...',
              prefixIcon: const Icon(Icons.search),
              suffixIcon: _searchQuery.isNotEmpty
                  ? IconButton(
                      onPressed: () {
                        setState(() => _searchQuery = '');
                      },
                      icon: const Icon(Icons.clear),
                    )
                  : null,
            ),
          ),
        ),

        // Active Filters
        if (_selectedStatus != null || _selectedState != null)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                if (_selectedStatus != null && _selectedStatus != 'All')
                  Chip(
                    label: Text(_selectedStatus!),
                    deleteIcon: const Icon(Icons.clear, size: 18),
                    onDeleted: () {
                      setState(() => _selectedStatus = null);
                    },
                  ),
                const SizedBox(width: 8),
                if (_selectedState != null && _selectedState != 'All')
                  Chip(
                    label: Text(_selectedState!),
                    deleteIcon: const Icon(Icons.clear, size: 18),
                    onDeleted: () {
                      setState(() => _selectedState = null);
                    },
                  ),
              ],
            ),
          ),

        // Colonies List
        Expanded(
          child: RefreshIndicator(
            onRefresh: () async => ref.refresh(coloniesProvider),
            child: coloniesAsync.when(
              data: (colonies) {
                var filteredColonies = colonies;

                if (_searchQuery.isNotEmpty) {
                  filteredColonies = filteredColonies.where((c) {
                    return c.name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
                        c.location.toLowerCase().contains(_searchQuery.toLowerCase()) ||
                        c.district.toLowerCase().contains(_searchQuery.toLowerCase());
                  }).toList();
                }

                if (_selectedStatus != null && _selectedStatus != 'All') {
                  filteredColonies = filteredColonies
                      .where((c) => c.status.toLowerCase() == _selectedStatus!.toLowerCase())
                      .toList();
                }

                if (_selectedState != null && _selectedState != 'All') {
                  filteredColonies = filteredColonies
                      .where((c) => c.state == _selectedState)
                      .toList();
                }

                if (filteredColonies.isEmpty) {
                  return ListView(
                    children: [
                      const SizedBox(height: 100),
                      AppWidgets.emptyState(
                        title: 'No Colonies Found',
                        subtitle: 'Try adjusting your filters or search query',
                        onAction: () {
                          setState(() {
                            _searchQuery = '';
                            _selectedStatus = null;
                            _selectedState = null;
                          });
                        },
                        actionLabel: 'Clear Filters',
                      ),
                    ],
                  );
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: filteredColonies.length,
                  itemBuilder: (context, index) {
                    final colony = filteredColonies[index];
                    return _buildColonyCard(context, colony);
                  },
                );
              },
              loading: () => ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: 5,
                itemBuilder: (context, index) {
                  return AppWidgets.shimmerLoading(
                    child: Container(
                      height: 150,
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  );
                },
              ),
              error: (error, stack) => ListView(
                children: [
                  const SizedBox(height: 100),
                  AppWidgets.errorWidget(
                    message: error.toString(),
                    onRetry: () => ref.refresh(coloniesProvider),
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildColonyCard(BuildContext context, ColonyModel colony) {
    return AppWidgets.customCard(
      onTap: () => context.push('/colony-plots/${colony.id}', extra: {
        'colonyName': colony.name,
      }),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Stack(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  width: double.infinity,
                  height: MediaQuery.of(context).size.width > 600 ? 180 : 140,
                  color: Colors.grey.shade200,
                  child: colony.displayImages.isNotEmpty
                      ? Image.network(
                          colony.displayImages.first,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) {
                            return const Center(
                              child: Icon(Icons.image_not_supported_outlined, color: Colors.grey, size: 50),
                            );
                          },
                        )
                      : const Center(
                          child: Icon(Icons.home_work_outlined, color: Colors.grey, size: 50),
                        ),
                ),
              ),
              Positioned(
                top: 12,
                left: 12,
                child: AppWidgets.statusBadge(status: colony.status),
              ),
              Positioned(
                top: 12,
                right: 12,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 10),
                    ],
                  ),
                  child: Text(
                    '${colony.availablePlots} Available',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.successColor),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      colony.name,
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(Icons.location_on_outlined, size: 16, color: Colors.grey.shade500),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            [
                              if (colony.district.isNotEmpty) colony.district,
                              if (colony.state.isNotEmpty) colony.state,
                            ].join(', '),
                            style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    if (colony.amenities != null && colony.amenities!.isNotEmpty)
                      Wrap(
                        spacing: 8,
                        runSpacing: 4,
                        children: colony.amenities!.take(3).map((amenity) {
                          return Chip(
                            label: Text(amenity, style: const TextStyle(fontSize: 10)),
                            padding: EdgeInsets.zero,
                            materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          );
                        }).toList(),
                      ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text('Starting from', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                  const SizedBox(height: 4),
                  AppWidgets.priceTag(
                    amount: colony.pricePerSqft,
                    prefix: '₹',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
                  ),
                  const SizedBox(height: 4),
                  Text('per sqft', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                ],
              ),
            ],
          ),
          const SizedBox(height: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Sold: ${colony.soldPlots}/${colony.totalPlots}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                  Text(
                    '${colony.progressPercentage.toStringAsFixed(1)}%',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: LinearProgressIndicator(
                  value: colony.progressPercentage / 100,
                  backgroundColor: Colors.grey.shade200,
                  valueColor: AlwaysStoppedAnimation<Color>(
                    colony.isSoldOut ? AppTheme.errorColor : AppTheme.successColor,
                  ),
                  minHeight: 6,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => context.push('/colony-detail/${colony.id}'),
              child: const Text('View Details'),
            ),
          ),
        ],
      ),
    );
  }
}
