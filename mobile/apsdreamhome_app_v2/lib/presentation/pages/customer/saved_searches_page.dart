import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class SavedSearchesPage extends ConsumerStatefulWidget {
  const SavedSearchesPage({super.key});

  @override
  ConsumerState<SavedSearchesPage> createState() => _SavedSearchesPageState();
}

class _SavedSearchesPageState extends ConsumerState<SavedSearchesPage> {
  List<Map<String, dynamic>> _searches = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadSearches();
  }

  Future<void> _loadSearches() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('user/saved-searches');
      if (!mounted) return;
      final data = response['data'];
      if (data is List) {
        _searches = data.cast<Map<String, dynamic>>();
      } else if (data is Map && data.containsKey('searches')) {
        final list = data['searches'];
        if (list is List) {
          _searches = list.cast<Map<String, dynamic>>();
        } else {
          _searches = [];
        }
      } else {
        _searches = [];
      }
    } catch (e) {
      if (mounted) {
        setState(() => _error = e.toString());
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _toggleAlerts(int id, bool value) async {
    try {
      final api = ref.read(apiServiceProvider);
      await api.put('user/saved-searches/$id', data: {'email_alerts': value ? 1 : 0});
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to update alerts: $e'),
            backgroundColor: AppTheme.errorColor,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    }
  }

  Future<void> _deleteSearch(int id) async {
    try {
      final api = ref.read(apiServiceProvider);
      await api.delete('user/saved-searches/$id');
      if (mounted) {
        setState(() {
          _searches.removeWhere((s) => _getInt(s, 'id') == id);
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Search deleted'),
            duration: Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to delete: $e'),
            backgroundColor: AppTheme.errorColor,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    }
  }

  int _getInt(Map<String, dynamic> map, String key) {
    final val = map[key];
    if (val is int) return val;
    if (val is String) return int.tryParse(val) ?? 0;
    return 0;
  }

  String _getString(Map<String, dynamic> map, String key, [String fallback = '']) {
    final val = map[key];
    return val?.toString() ?? fallback;
  }

  String _formatPrice(double price) {
    if (price >= 10000000) {
      return '₹${(price / 10000000).toStringAsFixed(2)} Cr';
    } else if (price >= 100000) {
      return '₹${(price / 100000).toStringAsFixed(2)} L';
    }
    return '₹${price.toStringAsFixed(0)}';
  }

  String _buildFilterSummary(Map<String, dynamic> search) {
    final parts = <String>[];
    final location = _getString(search, 'location');
    if (location.isNotEmpty) parts.add(location);
    final type = _getString(search, 'property_type');
    if (type.isNotEmpty) parts.add(type[0].toUpperCase() + type.substring(1));
    final minPrice = _getDouble(search, 'min_price');
    final maxPrice = _getDouble(search, 'max_price');
    if (minPrice > 0 || maxPrice > 0) {
      if (minPrice > 0 && maxPrice > 0) {
        parts.add('${_formatPrice(minPrice)} - ${_formatPrice(maxPrice)}');
      } else if (minPrice > 0) {
        parts.add('Above ${_formatPrice(minPrice)}');
      } else {
        parts.add('Below ${_formatPrice(maxPrice)}');
      }
    }
    final bedrooms = _getInt(search, 'bedrooms');
    if (bedrooms > 0) parts.add('$bedrooms BHK');
    return parts.isEmpty ? 'All properties' : parts.join(' | ');
  }

  double _getDouble(Map<String, dynamic> map, String key, [double fallback = 0.0]) {
    final val = map[key];
    if (val is double) return val;
    if (val is int) return val.toDouble();
    if (val is String) return double.tryParse(val) ?? fallback;
    return fallback;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surfaceColor,
      appBar: AppBar(
        title: const Text('Saved Searches'),
        centerTitle: true,
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/');
            }
          },
        ),
      ),
      body: _isLoading
          ? _buildLoading()
          : _error != null
              ? _buildError()
              : _searches.isEmpty
                  ? _buildEmpty()
                  : _buildContent(),
      floatingActionButton: FloatingActionButton(
        onPressed: () => context.push('/properties'),
        backgroundColor: AppTheme.accentColor,
        foregroundColor: AppTheme.primaryColor,
        elevation: 4,
        child: const Icon(Icons.search),
      ),
    );
  }

  Widget _buildLoading() {
    return const Center(
      child: CircularProgressIndicator(color: AppTheme.primaryColor),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.errorColor.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.error_outline,
                size: 56,
                color: AppTheme.errorColor,
              ),
            ),
            const SizedBox(height: 20),
          Text(
              'Something went wrong',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: Colors.grey.shade800,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _loadSearches,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 160,
              height: 160,
              padding: const EdgeInsets.all(32),
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withValues(alpha: 0.06),
                shape: BoxShape.circle,
              ),
              child: Stack(
                alignment: Alignment.center,
                children: [
                  Icon(
                    Icons.search,
                    size: 80,
                    color: AppTheme.primaryColor.withValues(alpha: 0.2),
                  ),
                  Positioned(
                    right: 10,
                    top: 10,
                    child: Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: AppTheme.primaryColor.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        Icons.add,
                        size: 24,
                        color: AppTheme.primaryColor.withValues(alpha: 0.5),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),
            Text(
              'No saved searches yet',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: Colors.grey.shade800,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Save a search from the properties page',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade500,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 32),
            ElevatedButton.icon(
              onPressed: () => context.push('/properties'),
              icon: const Icon(Icons.search),
              label: const Text('Browse Properties'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 36, vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                elevation: 2,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    return RefreshIndicator(
      onRefresh: _loadSearches,
      color: AppTheme.primaryColor,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _searches.length,
        itemBuilder: (context, index) {
          final search = _searches[index];
          return _buildSearchCard(search);
        },
      ),
    );
  }

  Widget _buildSearchCard(Map<String, dynamic> search) {
    final id = _getInt(search, 'id');
    final name = _getString(search, 'name', 'Untitled Search');
    final lastRunAt = _getString(search, 'last_run_at', '');
    final resultCount = _getInt(search, 'result_count');
    final emailAlerts = search['email_alerts'] == true || search['email_alerts'] == 1 || search['email_alerts'] == '1';
    final filterSummary = _buildFilterSummary(search);

    return Dismissible(
      key: ValueKey(id),
      direction: DismissDirection.endToStart,
      background: Container(
        alignment: Alignment.centerRight,
        margin: const EdgeInsets.only(bottom: 16),
        padding: const EdgeInsets.only(right: 24),
        decoration: BoxDecoration(
          color: AppTheme.errorColor,
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Icon(
          Icons.delete_outline,
          color: Colors.white,
          size: 28,
        ),
      ),
      confirmDismiss: (direction) async {
        return await showDialog<bool>(
          context: context,
          builder: (ctx) => AlertDialog(
            title: const Text('Delete Search'),
            content: Text('Delete "$name"?'),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx, false),
                child: const Text('Cancel'),
              ),
              TextButton(
                onPressed: () => Navigator.pop(ctx, true),
                style: TextButton.styleFrom(
                  foregroundColor: AppTheme.errorColor,
                ),
                child: const Text('Delete'),
              ),
            ],
          ),
        );
      },
      onDismissed: (direction) => _deleteSearch(id),
      child: Card(
        margin: const EdgeInsets.only(bottom: 16),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        elevation: 2,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.search,
                      size: 20,
                      color: AppTheme.primaryColor,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          name,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            color: AppTheme.textPrimaryLight,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          filterSummary,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Divider(height: 1, color: Colors.grey.shade200),
              const SizedBox(height: 12),
              Row(
                children: [
                  Icon(Icons.calendar_today, size: 14, color: Colors.grey.shade500),
                  const SizedBox(width: 6),
                  Text(
                    lastRunAt.isNotEmpty ? 'Last run: $lastRunAt' : 'Never run',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade600,
                    ),
                  ),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: resultCount > 0
                          ? AppTheme.successColor.withValues(alpha: 0.1)
                          : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '$resultCount results',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: resultCount > 0
                            ? AppTheme.successColor
                            : Colors.grey.shade600,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Icon(
                    emailAlerts ? Icons.notifications_active : Icons.notifications_off,
                    size: 18,
                    color: emailAlerts ? AppTheme.warningColor : Colors.grey.shade400,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Email alerts',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: Colors.grey.shade700,
                    ),
                  ),
                  const Spacer(),
                  Switch(
                    value: emailAlerts,
                    onChanged: (value) {
                      setState(() {
                        search['email_alerts'] = value ? 1 : 0;
                      });
                      _toggleAlerts(id, value);
                    },
                    activeColor: AppTheme.primaryColor,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
