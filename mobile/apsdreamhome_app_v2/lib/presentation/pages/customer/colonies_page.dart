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
  
  final List<String> _statuses = ['All', 'Active', 'Launching', 'Upcoming'];
  final List<String> _states = ['All', 'Uttar Pradesh', 'Bihar', 'Madhya Pradesh'];

  @override
  Widget build(BuildContext context) {
    final coloniesAsync = ref.watch(coloniesProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Colonies'),
        actions: [
          IconButton(
            onPressed: () {
              _showFilterBottomSheet(context);
            },
            icon: const Icon(Icons.filter_list),
          ),
        ],
      ),
      body: Column(
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
            child: coloniesAsync.when(
              data: (colonies) {
                // Filter colonies
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
                  return AppWidgets.emptyState(
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
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () => ref.refresh(coloniesProvider),
              ),
            ),
          ),
        ],
      ),
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
          // Image
          Stack(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  width: double.infinity,
                  height: 180,
                  color: Colors.grey.shade200,
                  child: colony.images != null && colony.images!.isNotEmpty
                      ? Image.network(
                          colony.images!.first,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) {
                            return const Center(
                              child: Icon(
                                Icons.image_not_supported_outlined,
                                color: Colors.grey,
                                size: 50,
                              ),
                            );
                          },
                        )
                      : const Center(
                          child: Icon(
                            Icons.home_work_outlined,
                            color: Colors.grey,
                            size: 50,
                          ),
                        ),
                ),
              ),
              
              // Status Badge
              Positioned(
                top: 12,
                left: 12,
                child: AppWidgets.statusBadge(status: colony.status),
              ),
              
              // Available Plots Badge
              Positioned(
                top: 12,
                right: 12,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.1),
                        blurRadius: 10,
                      ),
                    ],
                  ),
                  child: Text(
                    '${colony.availablePlots} Available',
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.successColor,
                    ),
                  ),
                ),
              ),
            ],
          ),
          
          const SizedBox(height: 16),
          
          // Info
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      colony.name,
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    
                    const SizedBox(height: 4),
                    
                    Row(
                      children: [
                        Icon(
                          Icons.location_on_outlined,
                          size: 16,
                          color: Colors.grey.shade500,
                        ),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            '${colony.location}, ${colony.district}, ${colony.state}',
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey.shade600,
                            ),
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
                            label: Text(
                              amenity,
                              style: const TextStyle(fontSize: 10),
                            ),
                            padding: EdgeInsets.zero,
                            materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          );
                        }).toList(),
                      ),
                  ],
                ),
              ),
              
              // Price
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    'Starting from',
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey.shade500,
                    ),
                  ),
                  const SizedBox(height: 4),
                  AppWidgets.priceTag(
                    amount: colony.pricePerSqft,
                    prefix: '₹',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primaryColor,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'per sqft',
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey.shade500,
                    ),
                  ),
                ],
              ),
            ],
          ),
          
          const SizedBox(height: 16),
          
          // Progress Bar
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Sold: ${colony.soldPlots}/${colony.totalPlots}',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade600,
                    ),
                  ),
                  Text(
                    '${colony.progressPercentage.toStringAsFixed(1)}%',
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primaryColor,
                    ),
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
                    colony.isSoldOut
                        ? AppTheme.errorColor
                        : AppTheme.successColor,
                  ),
                  minHeight: 6,
                ),
              ),
            ],
          ),
          
          const SizedBox(height: 16),
          
          // Action Button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => context.push('/colony/${colony.id}'),
              child: const Text('View Details'),
            ),
          ),
        ],
      ),
    );
  }

  void _showFilterBottomSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
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
                        'Filters',
                        style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          setState(() {
                            _selectedStatus = null;
                            _selectedState = null;
                          });
                        },
                        child: const Text('Clear All'),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: 24),
                  
                  // Status Filter
                  Text(
                    'Status',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  
                  const SizedBox(height: 12),
                  
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _statuses.map((status) {
                      final isSelected = _selectedStatus == status ||
                          (_selectedStatus == null && status == 'All');
                      
                      return ChoiceChip(
                        label: Text(status),
                        selected: isSelected,
                        onSelected: (selected) {
                          setState(() {
                            _selectedStatus = status == 'All' ? null : status;
                          });
                        },
                      );
                    }).toList(),
                  ),
                  
                  const SizedBox(height: 24),
                  
                  // State Filter
                  Text(
                    'State',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  
                  const SizedBox(height: 12),
                  
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _states.map((state) {
                      final isSelected = _selectedState == state ||
                          (_selectedState == null && state == 'All');
                      
                      return ChoiceChip(
                        label: Text(state),
                        selected: isSelected,
                        onSelected: (selected) {
                          setState(() {
                            _selectedState = state == 'All' ? null : state;
                          });
                        },
                      );
                    }).toList(),
                  ),
                  
                  const SizedBox(height: 32),
                  
                  // Apply Button
                  SizedBox(
                    width: double.infinity,
                    height: 56,
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(context);
                        this.setState(() {});
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
