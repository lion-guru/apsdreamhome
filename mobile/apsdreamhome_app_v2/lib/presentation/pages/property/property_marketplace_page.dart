import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';

import '../../../core/theme/app_theme.dart';
import '../../widgets/app_widgets.dart';
import '../../../data/services/property_listing_service.dart';
import '../../../data/services/comparison_service.dart';

class PropertyMarketplacePage extends ConsumerStatefulWidget {
  const PropertyMarketplacePage({super.key});

  @override
  ConsumerState<PropertyMarketplacePage> createState() =>
      _PropertyMarketplacePageState();
}

class _PropertyMarketplacePageState
    extends ConsumerState<PropertyMarketplacePage>
    with SingleTickerProviderStateMixin {
  // --- Search ---
  bool _showSearchBar = false;
  final TextEditingController _searchController = TextEditingController();
  final FocusNode _searchFocusNode = FocusNode();
  Timer? _debounceTimer;

  // --- Filters ---
  String _selectedType = 'all';
  String _selectedPurpose = 'all';
  double _filterMinPrice = 0;
  double _filterMaxPrice = 100000000;
  String _sortBy = 'newest';
  String _selectedColony = 'All';
  int? _selectedColonyId;

  // --- View ---
  bool _isGridView = true;
  final ScrollController _scrollController = ScrollController();

  // --- Data ---
  List<PropertyListing> _properties = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  bool _hasMore = true;
  int _currentPage = 1;
  static const int _pageSize = 20;
  // --- Type Colors ---
  static const Map<String, Color> _typeColors = {
    'plot': AppTheme.successColor,
    'house': AppTheme.infoColor,
    'flat': Color(0xFF7B1FA2),
    'shop': AppTheme.warningColor,
    'farmhouse': Color(0xFF00796B),
  };

  static const Map<String, IconData> _typeIcons = {
    'plot': Icons.landscape,
    'house': Icons.home,
    'flat': Icons.apartment,
    'shop': Icons.store,
    'farmhouse': Icons.agriculture,
  };

  @override
  void initState() {
    super.initState();
    _fetchProperties();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _searchFocusNode.dispose();
    _debounceTimer?.cancel();
    _scrollController.dispose();
    super.dispose();
  }

  // ──────────────────────────── Data Fetching ────────────────────────────

  Future<void> _fetchProperties({bool reset = false}) async {
    if (reset) {
      _currentPage = 1;
      _hasMore = true;
      _properties = [];
    }

    setState(() {
      _isLoading = _properties.isEmpty;
    });

    final service = ref.read(propertyListingServiceProvider);
    final results = await service.getProperties(
      type: _selectedType == 'all' ? null : _selectedType,
      purpose: _selectedPurpose == 'all' ? null : _selectedPurpose,
      minPrice: _filterMinPrice > 0 ? _filterMinPrice : null,
      maxPrice: _filterMaxPrice < 100000000 ? _filterMaxPrice : null,
      sortBy: _sortBy,
      colonyId: _selectedColonyId,
      page: _currentPage,
      limit: _pageSize,
    );

    final sorted = _applySorting(results);

    setState(() {
      if (reset || _currentPage == 1) {
        _properties = sorted;
      } else {
        _properties = [..._properties, ...sorted];
      }
      _hasMore = sorted.length >= _pageSize;
      _isLoading = false;
      _isLoadingMore = false;
    });
  }

  Future<void> _searchProperties(String query) async {
    if (query.trim().length < 2) return;
    final service = ref.read(propertyListingServiceProvider);
    final results = await service.searchProperties(query.trim());
    final sorted = _applySorting(results);
    setState(() {
      _properties = sorted;
      _hasMore = false;
      _isLoading = false;
    });
  }

  List<PropertyListing> _applySorting(List<PropertyListing> list) {
    final sorted = List<PropertyListing>.from(list);
    switch (_sortBy) {
      case 'price_low':
        sorted.sort((a, b) => a.price.compareTo(b.price));
        break;
      case 'price_high':
        sorted.sort((a, b) => b.price.compareTo(a.price));
        break;
      case 'newest':
      default:
        sorted.sort((a, b) => b.createdAt.compareTo(a.createdAt));
        break;
    }
    return sorted;
  }

  // ──────────────────────────── Pagination ───────────────────────────────

  void _onScroll() {
    if (_scrollController.position.pixels >=
            _scrollController.position.maxScrollExtent - 200 &&
        !_isLoadingMore &&
        _hasMore &&
        !_isLoading) {
      _loadMore();
    }
  }

  Future<void> _loadMore() async {
    setState(() {
      _isLoadingMore = true;
      _currentPage++;
    });

    final service = ref.read(propertyListingServiceProvider);
    final results = await service.getProperties(
      type: _selectedType == 'all' ? null : _selectedType,
      purpose: _selectedPurpose == 'all' ? null : _selectedPurpose,
      minPrice: _filterMinPrice > 0 ? _filterMinPrice : null,
      maxPrice: _filterMaxPrice < 100000000 ? _filterMaxPrice : null,
      sortBy: _sortBy,
      colonyId: _selectedColonyId,
      page: _currentPage,
      limit: _pageSize,
    );

    final sorted = _applySorting(results);
    setState(() {
      _properties = [..._properties, ...sorted];
      _hasMore = sorted.length >= _pageSize;
      _isLoadingMore = false;
    });
  }

  Future<void> _onRefresh() async {
    _currentPage = 1;
    _hasMore = true;
    if (_searchController.text.trim().length >= 2) {
      await _searchProperties(_searchController.text);
    } else {
      await _fetchProperties(reset: true);
    }
  }

  // ──────────────────────────── Search ───────────────────────────────────

  void _onSearchChanged(String value) {
    _debounceTimer?.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 400), () {
      if (value.trim().length >= 2) {
        _searchProperties(value);
      } else if (value.trim().isEmpty) {
        _fetchProperties(reset: true);
      }
    });
  }

  void _toggleSearch() {
    setState(() {
      _showSearchBar = !_showSearchBar;
      if (!_showSearchBar) {
        _searchController.clear();
        _searchFocusNode.unfocus();
        _fetchProperties(reset: true);
      } else {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          _searchFocusNode.requestFocus();
        });
      }
    });
  }

  // ──────────────────────────── Filter Sheet ─────────────────────────────

  void _showFilterSheet() {
    String tempType = _selectedType;
    String tempPurpose = _selectedPurpose;
    double tempMin = _filterMinPrice;
    double tempMax = _filterMaxPrice;
    String tempSort = _sortBy;
    String tempColony = _selectedColony;
    int? tempColonyId = _selectedColonyId;

    const colonies = [
      ('All', 'All', null),
      ('Suryoday Colony', 'Suryoday Colony', 2),
      ('Braj Radha Nagri', 'Braj Radha Nagri', 3),
      ('Budh Bihar Colony', 'Budh Bihar Colony', 5),
      ('Raghunath Nagri', 'Raghunath Nagri', 6),
      ('APS Motiram Township', 'APS Motiram Township', 7),
    ];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setSheetState) {
            return DraggableScrollableSheet(
              initialChildSize: 0.85,
              maxChildSize: 0.95,
              minChildSize: 0.5,
              expand: false,
              builder: (ctx, scrollCtrl) {
                return Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Column(
                    children: [
                      const SizedBox(height: 10),
                      Container(
                        width: 40,
                        height: 4,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade300,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Filters',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          TextButton(
                            onPressed: () {
                              setSheetState(() {
                                tempType = 'all';
                                tempPurpose = 'all';
                                tempMin = 0;
                                tempMax = 100000000;
                                tempSort = 'newest';
                                tempColony = 'All';
                                tempColonyId = null;
                              });
                            },
                            child: const Text('Reset All'),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Expanded(
                        child: ListView(
                          controller: scrollCtrl,
                          children: [
                            _filterSectionTitle('Property Type'),
                            const SizedBox(height: 8),
                            _buildFilterChips(
                              options: const [
                                ('All', 'all'),
                                ('Plot', 'plot'),
                                ('House', 'house'),
                                ('Flat', 'flat'),
                                ('Apartment', 'apartment'),
                                ('Villa', 'villa'),
                                ('Shop', 'shop'),
                                ('Farmhouse', 'farmhouse'),
                              ],
                              selected: tempType,
                              onSelected: (val) =>
                                  setSheetState(() => tempType = val),
                            ),
                            const SizedBox(height: 20),
                            _filterSectionTitle('Purpose'),
                            const SizedBox(height: 8),
                            _buildFilterChips(
                              options: const [
                                ('All', 'all'),
                                ('Buy', 'buy'),
                                ('Sell', 'sell'),
                                ('Rent', 'rent'),
                              ],
                              selected: tempPurpose,
                              onSelected: (val) =>
                                  setSheetState(() => tempPurpose = val),
                            ),
                            const SizedBox(height: 20),
                            _filterSectionTitle('Colony / Location'),
                            const SizedBox(height: 8),
                            _buildFilterChips(
                              options: colonies.map((c) => (c.$1, c.$2)).toList(),
                              selected: tempColony,
                              onSelected: (val) {
                                final match = colonies.firstWhere(
                                  (c) => c.$2 == val,
                                  orElse: () => ('All', 'All', null),
                                );
                                setSheetState(() {
                                  tempColony = val;
                                  tempColonyId = match.$3;
                                });
                              },
                            ),
                            const SizedBox(height: 20),
                            _filterSectionTitle('Price Range'),
                            const SizedBox(height: 4),
                            Padding(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 4,
                              ),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    _formatPriceShort(tempMin),
                                    style: TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.grey.shade700,
                                    ),
                                  ),
                                  Text(
                                    _formatPriceShort(tempMax),
                                    style: TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.grey.shade700,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            RangeSlider(
                              values: RangeValues(tempMin, tempMax),
                              min: 0,
                              max: 100000000,
                              divisions: 50,
                              activeColor: AppTheme.primaryColor,
                              inactiveColor: Colors.grey.shade200,
                              onChanged: (values) {
                                setSheetState(() {
                                  tempMin = values.start;
                                  tempMax = values.end;
                                });
                              },
                            ),
                            const SizedBox(height: 12),
                            _filterSectionTitle('Sort By'),
                            const SizedBox(height: 8),
                            _buildFilterChips(
                              options: const [
                                ('Newest', 'newest'),
                                ('Price: Low', 'price_low'),
                                ('Price: High', 'price_high'),
                                ('Popular', 'popular'),
                              ],
                              selected: tempSort,
                              onSelected: (val) =>
                                  setSheetState(() => tempSort = val),
                            ),
                            const SizedBox(height: 24),
                          ],
                        ),
                      ),
                      SafeArea(
                        child: Padding(
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          child: SizedBox(
                            width: double.infinity,
                            height: 48,
                            child: ElevatedButton(
                              onPressed: () {
                                setState(() {
                                  _selectedType = tempType;
                                  _selectedPurpose = tempPurpose;
                                  _filterMinPrice = tempMin;
                                  _filterMaxPrice = tempMax;
                                  _sortBy = tempSort;
                                  _selectedColony = tempColony;
                                  _selectedColonyId = tempColonyId;
                                });
                                Navigator.pop(ctx);
                                _fetchProperties(reset: true);
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppTheme.primaryColor,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                              child: const Text(
                                'Apply Filters',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
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
      },
    );
  }

  // ──────────────────────────── Build ────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: ComparisonService(),
      builder: (context, _) {
        final compareCount = ComparisonService().count;
        return Container(
          color: Colors.grey.shade50,
          child: Column(
            children: [
              _buildSearchToolbar(),
              _buildResultBar(),
              Expanded(
                child: _isLoading
                    ? _buildShimmer()
                    : _properties.isEmpty
                    ? _buildEmptyState()
                    : RefreshIndicator(
                        color: AppTheme.primaryColor,
                        onRefresh: _onRefresh,
                        child: _isGridView ? _buildGridView() : _buildListView(),
                      ),
              ),
              if (compareCount > 0) _buildCompareBar(compareCount),
            ],
          ),
        );
      },
    );
  }

  Widget _buildCompareBar(int count) {
    return GestureDetector(
      onTap: () => context.push('/compare'),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: const BoxDecoration(
          color: Color(0xFF1e293b),
          border: Border(top: BorderSide(color: Color(0xFF334155), width: 1)),
        ),
        child: SafeArea(
          top: false,
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: const Color(0xFFf59e0b).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(
                  Icons.compare_arrows,
                  color: Color(0xFFf59e0b),
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      '$count ${count == 1 ? 'property' : 'properties'} selected',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const Text(
                      'Tap to compare side by side',
                      style: TextStyle(
                        color: Color(0xFF94a3b8),
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFFf59e0b),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text(
                  'Compare',
                  style: TextStyle(
                    color: Color(0xFF0f172a),
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSearchToolbar() {
    return Container(
      color: AppTheme.primaryColor,
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      child: Row(
        children: [
          if (_showSearchBar)
            IconButton(
              icon: const Icon(Icons.arrow_back, color: Colors.white),
              onPressed: _toggleSearch,
            ),
          if (_showSearchBar)
            Expanded(
              child: TextField(
                controller: _searchController,
                focusNode: _searchFocusNode,
                onChanged: _onSearchChanged,
                style: const TextStyle(color: Colors.white, fontSize: 16),
                decoration: InputDecoration(
                  hintText: 'Search properties...',
                  hintStyle: TextStyle(
                    color: Colors.white.withValues(alpha: 0.7),
                  ),
                  prefixIcon: Icon(
                    Icons.search,
                    color: Colors.white.withValues(alpha: 0.7),
                  ),
                  suffixIcon: _searchController.text.isNotEmpty
                      ? IconButton(
                          icon: Icon(
                            Icons.clear,
                            color: Colors.white.withValues(alpha: 0.7),
                          ),
                          onPressed: () {
                            _searchController.clear();
                            _onSearchChanged('');
                          },
                        )
                      : null,
                  border: InputBorder.none,
                  filled: true,
                  fillColor: Colors.white.withValues(alpha: 0.15),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 0,
                  ),
                ),
              ),
            ),
          if (!_showSearchBar)
            IconButton(
              icon: const Icon(Icons.search, color: Colors.white),
              onPressed: _toggleSearch,
              tooltip: 'Search',
            ),
          IconButton(
            icon: const Icon(Icons.tune, color: Colors.white),
            onPressed: _showFilterSheet,
            tooltip: 'Filters',
          ),
          if (!_showSearchBar)
            IconButton(
              icon: Icon(
                _isGridView ? Icons.view_list : Icons.grid_view,
                color: Colors.white,
              ),
              onPressed: () => setState(() => _isGridView = !_isGridView),
              tooltip: _isGridView ? 'List view' : 'Grid view',
            ),
        ],
      ),
    );
  }

  // ──────────────────────────── Result Count Bar ─────────────────────────

  int get _activeFilterCount {
    int count = 0;
    if (_selectedType != 'all') count++;
    if (_selectedPurpose != 'all') count++;
    if (_selectedColony != 'All') count++;
    if (_filterMinPrice > 0) count++;
    if (_filterMaxPrice < 100000000) count++;
    return count;
  }

  Widget _buildResultBar() {
    if (_isLoading) return const SizedBox.shrink();
    final activeFilters = <String>[];
    if (_selectedType != 'all') activeFilters.add(_selectedType.toUpperCase());
    if (_selectedPurpose != 'all') {
      activeFilters.add(_selectedPurpose.toUpperCase());
    }
    if (_selectedColony != 'All') activeFilters.add(_selectedColony);
    if (_filterMinPrice > 0 || _filterMaxPrice < 100000000) {
      activeFilters.add(
        '${_formatPriceShort(_filterMinPrice)} - ${_formatPriceShort(_filterMaxPrice)}',
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      color: Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  'Showing ${_properties.length} properties',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: Colors.grey.shade600,
                  ),
                ),
              ),
              if (_activeFilterCount > 0)
                GestureDetector(
                  onTap: () {
                    setState(() {
                      _selectedType = 'all';
                      _selectedPurpose = 'all';
                      _filterMinPrice = 0;
                      _filterMaxPrice = 100000000;
                      _sortBy = 'newest';
                      _selectedColony = 'All';
                      _selectedColonyId = null;
                    });
                    _fetchProperties(reset: true);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 3,
                    ),
                    decoration: BoxDecoration(
                      color: AppTheme.errorColor.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.close,
                          size: 12,
                          color: AppTheme.errorColor,
                        ),
                        const SizedBox(width: 3),
                        Text(
                          'Clear ($_activeFilterCount)',
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: AppTheme.errorColor,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
            ],
          ),
          if (activeFilters.isNotEmpty) ...[
            const SizedBox(height: 6),
            SizedBox(
              height: 24,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: activeFilters.length,
                separatorBuilder: (_, _) => const SizedBox(width: 6),
                itemBuilder: (_, i) {
                  return Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      activeFilters[i],
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.primaryColor,
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ──────────────────────────── Shimmer ──────────────────────────────────

  Widget _buildShimmer() {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade300,
      highlightColor: Colors.grey.shade100,
      child: _isGridView ? _buildShimmerGrid() : _buildShimmerList(),
    );
  }

  Widget _buildShimmerGrid() {
    return GridView.builder(
      padding: const EdgeInsets.all(12),
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 12,
        crossAxisSpacing: 12,
        childAspectRatio: 0.68,
      ),
      itemCount: 6,
      itemBuilder: (_, _) => _shimmerCard(isGrid: true),
    );
  }

  Widget _buildShimmerList() {
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      physics: const NeverScrollableScrollPhysics(),
      itemCount: 4,
      itemBuilder: (_, _) => _shimmerCard(isGrid: false),
    );
  }

  Widget _shimmerCard({required bool isGrid}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            height: isGrid ? 110 : 140,
            width: double.infinity,
            decoration: BoxDecoration(
              color: Colors.grey.shade300,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(12),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  height: 12,
                  width: isGrid ? 80 : 160,
                  color: Colors.grey.shade300,
                ),
                const SizedBox(height: 8),
                Container(
                  height: 10,
                  width: isGrid ? 60 : 100,
                  color: Colors.grey.shade300,
                ),
                const SizedBox(height: 8),
                Container(
                  height: 10,
                  width: isGrid ? 50 : 120,
                  color: Colors.grey.shade300,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ──────────────────────────── Empty State ──────────────────────────────

  Widget _buildEmptyState() {
    return AppWidgets.emptyState(
      icon: Icons.home_work_outlined,
      title: 'No Properties Found',
      subtitle: _searchController.text.isNotEmpty
          ? 'No results for "${_searchController.text}". Try different keywords.'
          : 'No properties match your filters. Try adjusting your search.',
      onAction: () {
        setState(() {
          _selectedType = 'all';
          _selectedPurpose = 'all';
          _filterMinPrice = 0;
          _filterMaxPrice = 100000000;
          _sortBy = 'newest';
          _selectedColony = 'All';
          _selectedColonyId = null;
          _searchController.clear();
        });
        _fetchProperties(reset: true);
      },
      actionLabel: 'Clear Filters',
    );
  }

  // ──────────────────────────── Grid View ────────────────────────────────

  Widget _buildGridView() {
    return NotificationListener<ScrollNotification>(
      onNotification: (_) {
        _onScroll();
        return false;
      },
      child: GridView.builder(
        controller: _scrollController,
        padding: const EdgeInsets.all(12),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childAspectRatio: 0.68,
        ),
        itemCount: _properties.length + (_isLoadingMore ? 2 : 0),
        itemBuilder: (context, index) {
          if (index >= _properties.length) {
            return _shimmerCard(isGrid: true);
          }
          return _buildGridCard(_properties[index]);
        },
      ),
    );
  }

  Widget _buildGridCard(PropertyListing property) {
    final typeColor = _typeColors[property.type] ?? Colors.grey;
    final typeName =
        property.type[0].toUpperCase() + property.type.substring(1);
    final locationText = property.city != null
        ? '${property.city}, ${property.state ?? ''}'
        : property.location;

    return GestureDetector(
      onTap: () => context.push('/property-detail/${property.id}'),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 8,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image area
            Stack(
              children: [
                _buildImagePlaceholder(
                  property,
                  typeColor,
                  height: 110,
                  isGrid: true,
                ),
                if (property.isVerified)
                  Positioned(
                    top: 6,
                    left: 6,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 6,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: AppTheme.successColor,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.verified, color: Colors.white, size: 11),
                          SizedBox(width: 3),
                          Text(
                            'Verified',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 9,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                Positioned(
                  top: 6,
                  right: 6,
                  child: Row(
                    children: [
                      GestureDetector(
                        onTap: () {
                          final svc = ComparisonService();
                          if (svc.contains(property.id)) {
                            svc.remove(property.id);
                          } else {
                            final added = svc.add(property);
                            if (!added && !svc.contains(property.id)) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text('Max 3 properties for comparison'),
                                  duration: Duration(seconds: 2),
                                ),
                              );
                            }
                          }
                        },
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: ComparisonService().contains(property.id)
                                ? const Color(0xFFf59e0b)
                                : Colors.white.withValues(alpha: 0.92),
                            shape: BoxShape.circle,
                          ),
                          child: Icon(
                            ComparisonService().contains(property.id)
                                ? Icons.check
                                : Icons.compare_arrows,
                            size: 14,
                            color: ComparisonService().contains(property.id)
                                ? Colors.white
                                : Colors.grey.shade700,
                          ),
                        ),
                      ),
                      const SizedBox(width: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: typeColor,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          typeName,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 9,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                Positioned(
                  bottom: 6,
                  left: 6,
                  child: Row(
                    children: [
                      if (property.isHighlighted) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFFFFD700), Color(0xFFFFA000)],
                            ),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                property.isPremium
                                    ? Icons.star
                                    : property.isFeatured
                                    ? Icons.trending_up
                                    : Icons.priority_high,
                                color: Colors.white,
                                size: 11,
                              ),
                              const SizedBox(width: 2),
                              Text(
                                property.isPremium
                                    ? 'Premium'
                                    : property.isFeatured
                                    ? 'Featured'
                                    : 'Urgent',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 9,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 4),
                      ],
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: property.purpose.toLowerCase() == 'rent'
                              ? AppTheme.infoColor
                              : AppTheme.warningColor,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          property.purposeLabel,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 9,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            // Details
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      property.title,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        height: 1.3,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          Icons.location_on,
                          size: 11,
                          color: Colors.grey.shade500,
                        ),
                        const SizedBox(width: 2),
                        Expanded(
                          child: Text(
                            locationText,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: 10,
                              color: Colors.grey.shade500,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const Spacer(),
                    Row(
                      children: [
                        Text(
                          '₹${property.formattedPrice}',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      property.formattedArea,
                      style: TextStyle(
                        fontSize: 10,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ──────────────────────────── List View ────────────────────────────────

  Widget _buildListView() {
    return NotificationListener<ScrollNotification>(
      onNotification: (_) {
        _onScroll();
        return false;
      },
      child: ListView.builder(
        controller: _scrollController,
        padding: const EdgeInsets.all(12),
        itemCount: _properties.length + (_isLoadingMore ? 2 : 0),
        itemBuilder: (context, index) {
          if (index >= _properties.length) {
            return _shimmerCard(isGrid: false);
          }
          return _buildListCard(_properties[index]);
        },
      ),
    );
  }

  Widget _buildListCard(PropertyListing property) {
    final typeColor = _typeColors[property.type] ?? Colors.grey;
    final typeName =
        property.type[0].toUpperCase() + property.type.substring(1);
    final locationText = property.city != null
        ? '${property.city}, ${property.state ?? ''}'
        : property.location;

    return GestureDetector(
      onTap: () => context.push('/property-detail/${property.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 8,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Row(
          children: [
            // Image
            Stack(
              children: [
                _buildImagePlaceholder(
                  property,
                  typeColor,
                  height: 140,
                  isGrid: false,
                ),
                if (property.isVerified)
                  Positioned(
                    top: 6,
                    left: 6,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 6,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: AppTheme.successColor,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.verified, color: Colors.white, size: 11),
                          SizedBox(width: 3),
                          Text(
                            'Verified',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 9,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                Positioned(
                  top: 6,
                  right: 6,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 6,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: typeColor,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      typeName,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 9,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
              ],
            ),
            // Details
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            property.title,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              height: 1.3,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          Icons.location_on,
                          size: 13,
                          color: Colors.grey.shade500,
                        ),
                        const SizedBox(width: 3),
                        Expanded(
                          child: Text(
                            locationText,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey.shade500,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        _infoChip(Icons.square_foot, property.formattedArea),
                        const SizedBox(width: 8),
                        _purposeBadge(property),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          '₹${property.formattedPrice}',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                        Text(
                          'By ${property.ownerName}',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey.shade500,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ──────────────────────────── Shared Widgets ───────────────────────────

  Widget _buildImagePlaceholder(
    PropertyListing property,
    Color typeColor, {
    required double height,
    required bool isGrid,
  }) {
    final width = isGrid ? double.infinity : 130.0;

    if (property.imageUrl != null && property.imageUrl!.isNotEmpty) {
      return ClipRRect(
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(isGrid ? 12 : 12),
          topRight: Radius.circular(isGrid ? 12 : 0),
          bottomLeft: const Radius.circular(0),
          bottomRight: const Radius.circular(0),
        ),
        child: Image.network(
          property.imageUrl!,
          height: height,
          width: width,
          fit: BoxFit.cover,
          errorBuilder: (_, _, _) => _fallbackPlaceholder(
            property,
            typeColor: typeColor,
            height: height,
            width: width,
          ),
        ),
      );
    }
    return _fallbackPlaceholder(
      property,
      typeColor: typeColor,
      height: height,
      width: width,
    );
  }

  Widget _fallbackPlaceholder(
    PropertyListing property, {
    required Color typeColor,
    required double height,
    required double width,
  }) {
    final icon = _typeIcons[property.type] ?? Icons.home;
    return Container(
      height: height,
      width: width,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            typeColor.withValues(alpha: 0.3),
            typeColor.withValues(alpha: 0.15),
          ],
        ),
        borderRadius: BorderRadius.circular(0),
      ),
      child: Center(
        child: Icon(
          icon,
          size: height * 0.35,
          color: typeColor.withValues(alpha: 0.6),
        ),
      ),
    );
  }

  Widget _infoChip(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: Colors.grey.shade600),
          const SizedBox(width: 3),
          Text(
            text,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w500,
              color: Colors.grey.shade700,
            ),
          ),
        ],
      ),
    );
  }

  Widget _purposeBadge(PropertyListing property) {
    final isRent = property.purpose.toLowerCase() == 'rent';
    final color = isRent ? AppTheme.infoColor : AppTheme.warningColor;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        property.purposeLabel,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w600,
          color: color,
        ),
      ),
    );
  }

  Widget _filterSectionTitle(String title) {
    return Text(
      title,
      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
    );
  }

  Widget _buildFilterChips({
    required List<(String, String)> options,
    required String selected,
    required ValueChanged<String> onSelected,
  }) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: options.map((opt) {
        final isActive = selected == opt.$2;
        return ChoiceChip(
          label: Text(opt.$1),
          selected: isActive,
          selectedColor: AppTheme.primaryColor.withValues(alpha: 0.1),
          backgroundColor: Colors.white,
          labelStyle: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w500,
            color: isActive ? AppTheme.primaryColor : Colors.grey.shade700,
          ),
          side: BorderSide(
            color: isActive ? AppTheme.primaryColor : Colors.grey.shade300,
          ),
          onSelected: (_) => onSelected(opt.$2),
        );
      }).toList(),
    );
  }

  // ──────────────────────────── Helpers ──────────────────────────────────

  String _formatPriceShort(double price) {
    if (price >= 10000000) {
      return '₹${(price / 10000000).toStringAsFixed(1)} Cr';
    }
    if (price >= 100000) return '₹${(price / 100000).toStringAsFixed(1)} L';
    if (price >= 1000) return '₹${(price / 1000).toStringAsFixed(0)} K';
    if (price == 0) return '₹0';
    return '₹${price.toStringAsFixed(0)}';
  }
}
