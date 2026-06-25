import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class PropertyAlertsPage extends ConsumerStatefulWidget {
  const PropertyAlertsPage({super.key});

  @override
  ConsumerState<PropertyAlertsPage> createState() => _PropertyAlertsPageState();
}

class _PropertyAlertsPageState extends ConsumerState<PropertyAlertsPage> {
  bool _isLoading = true;
  String? _error;
  bool _isSaving = false;

  bool _newListings = true;
  bool _priceDrops = true;
  bool _statusChanges = false;
  bool _weeklyDigest = true;

  bool _pushEnabled = true;
  bool _emailEnabled = true;
  bool _smsEnabled = false;

  String _frequency = 'instant';
  final List<String> _monitoredLocations = [];
  final TextEditingController _locationController = TextEditingController();

  double _priceMin = 0;
  double _priceMax = 50000000;
  final RangeValues _priceRange = const RangeValues(0, 50000000);

  final Set<String> _selectedTypes = {'plot', 'house', 'flat'};
  final List<Map<String, dynamic>> _alerts = [];

  @override
  void initState() {
    super.initState();
    _loadAlerts();
  }

  @override
  void dispose() {
    _locationController.dispose();
    super.dispose();
  }

  Future<void> _loadAlerts() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('user/alerts');
      if (!mounted) return;
      final data = response['data'];
      if (data is Map) {
        _newListings = data['new_listings'] == true || data['new_listings'] == 1;
        _priceDrops = data['price_drops'] == true || data['price_drops'] == 1;
        _statusChanges = data['status_changes'] == true || data['status_changes'] == 1;
        _weeklyDigest = data['weekly_digest'] == true || data['weekly_digest'] == 1;
        _pushEnabled = data['push_enabled'] == true || data['push_enabled'] == 1;
        _emailEnabled = data['email_enabled'] == true || data['email_enabled'] == 1;
        _smsEnabled = data['sms_enabled'] == true || data['sms_enabled'] == 1;
        _frequency = data['frequency']?.toString() ?? 'instant';
        final locations = data['locations'];
        if (locations is List) {
          _monitoredLocations.clear();
          _monitoredLocations.addAll(locations.cast<String>());
        }
        final types = data['property_types'];
        if (types is List && types.isNotEmpty) {
          _selectedTypes.clear();
          _selectedTypes.addAll(types.cast<String>());
        }
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

  Future<void> _saveAlerts() async {
    if (!mounted) return;
    setState(() => _isSaving = true);
    try {
      final api = ref.read(apiServiceProvider);
      final payload = {
        'new_listings': _newListings ? 1 : 0,
        'price_drops': _priceDrops ? 1 : 0,
        'status_changes': _statusChanges ? 1 : 0,
        'weekly_digest': _weeklyDigest ? 1 : 0,
        'push_enabled': _pushEnabled ? 1 : 0,
        'email_enabled': _emailEnabled ? 1 : 0,
        'sms_enabled': _smsEnabled ? 1 : 0,
        'frequency': _frequency,
        'locations': _monitoredLocations,
        'price_min': _priceMin.round(),
        'price_max': _priceMax.round(),
        'property_types': _selectedTypes.toList(),
      };
      if (_alerts.isNotEmpty) {
        final id = _alerts.first['id'];
        await api.put('user/alerts/$id', data: payload);
      } else {
        await api.post('user/alerts', data: payload);
      }
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Alert preferences saved'),
            duration: Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to save: $e'),
            backgroundColor: AppTheme.errorColor,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSaving = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surfaceColor,
      appBar: AppBar(
        title: const Text('Property Alerts'),
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
          ? Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : _error != null
              ? _buildError()
              : _buildContent(),
      bottomNavigationBar: _isLoading || _error != null
          ? null
          : SafeArea(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: ElevatedButton(
                  onPressed: _isSaving ? null : _saveAlerts,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: Colors.grey.shade400,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 2,
                  ),
                  child: _isSaving
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          'Save Preferences',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
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
              onPressed: _loadAlerts,
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

  Widget _buildContent() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildHeaderCard(),
          const SizedBox(height: 16),
          _buildAlertTypesCard(),
          const SizedBox(height: 16),
          _buildChannelsCard(),
          const SizedBox(height: 16),
          _buildFrequencyCard(),
          const SizedBox(height: 16),
          _buildLocationsCard(),
          const SizedBox(height: 16),
          _buildPriceRangeCard(),
          const SizedBox(height: 16),
          _buildPropertyTypesCard(),
          const SizedBox(height: 80),
        ],
      ),
    );
  }

  Widget _buildHeaderCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.primaryColor,
            AppTheme.secondaryColor,
          ],
        ),
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primaryColor.withValues(alpha: 0.3),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.notifications_active,
              size: 32,
              color: Colors.white,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Property Alerts',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Get notified about properties that match your preferences',
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.white.withValues(alpha: 0.8),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAlertTypesCard() {
    return _buildCard(
      title: 'Alert Types',
      icon: Icons.tune,
      child: Column(
        children: [
          _buildToggleRow(
            'New listings',
            'When new properties match your criteria',
            Icons.home_work_outlined,
            _newListings,
            (v) => setState(() => _newListings = v),
          ),
          Divider(height: 1, color: Colors.grey.shade200),
          _buildToggleRow(
            'Price drops',
            'When a property price is reduced',
            Icons.trending_down,
            _priceDrops,
            (v) => setState(() => _priceDrops = v),
          ),
          Divider(height: 1, color: Colors.grey.shade200),
          _buildToggleRow(
            'Status changes',
            'When availability status changes',
            Icons.swap_horiz,
            _statusChanges,
            (v) => setState(() => _statusChanges = v),
          ),
          Divider(height: 1, color: Colors.grey.shade200),
          _buildToggleRow(
            'Weekly digest',
            'Summary of matching properties',
            Icons.summarize,
            _weeklyDigest,
            (v) => setState(() => _weeklyDigest = v),
          ),
        ],
      ),
    );
  }

  Widget _buildChannelsCard() {
    return _buildCard(
      title: 'Notification Channels',
      icon: Icons.send,
      child: Column(
        children: [
          _buildToggleRow(
            'Push Notifications',
            'Instant alerts on your device',
            Icons.phone_android,
            _pushEnabled,
            (v) => setState(() => _pushEnabled = v),
          ),
          Divider(height: 1, color: Colors.grey.shade200),
          _buildToggleRow(
            'Email',
            'Send alerts to your email',
            Icons.email_outlined,
            _emailEnabled,
            (v) => setState(() => _emailEnabled = v),
          ),
          Divider(height: 1, color: Colors.grey.shade200),
          _buildToggleRow(
            'SMS',
            'Text message alerts',
            Icons.sms_outlined,
            _smsEnabled,
            (v) => setState(() => _smsEnabled = v),
          ),
        ],
      ),
    );
  }

  Widget _buildFrequencyCard() {
    final frequencies = [
      {'value': 'instant', 'label': 'Instant', 'icon': Icons.flash_on},
      {'value': 'daily', 'label': 'Daily', 'icon': Icons.today},
      {'value': 'weekly', 'label': 'Weekly', 'icon': Icons.date_range},
    ];

    return _buildCard(
      title: 'Alert Frequency',
      icon: Icons.schedule,
      child: Row(
        children: frequencies.map((f) {
          final isSelected = _frequency == f['value'];
          return Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _frequency = f['value'] as String),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                margin: EdgeInsets.only(
                  right: f != frequencies.last ? 8 : 0,
                ),
                padding: const EdgeInsets.symmetric(vertical: 16),
                decoration: BoxDecoration(
                  color: isSelected
                      ? AppTheme.primaryColor.withValues(alpha: 0.1)
                      : Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isSelected ? AppTheme.primaryColor : Colors.grey.shade200,
                    width: isSelected ? 2 : 1,
                  ),
                ),
                child: Column(
                  children: [
                    Icon(
                      f['icon'] as IconData,
                      size: 24,
                      color: isSelected ? AppTheme.primaryColor : Colors.grey.shade500,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      f['label'] as String,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: isSelected ? AppTheme.primaryColor : Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildLocationsCard() {
    return _buildCard(
      title: 'Location Preferences',
      icon: Icons.location_on_outlined,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _locationController,
                  decoration: InputDecoration(
                    hintText: 'Add a location',
                    hintStyle: TextStyle(color: Colors.grey.shade400),
                    prefixIcon: Icon(Icons.location_searching, color: Colors.grey.shade400),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide(color: Colors.grey.shade300),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: AppTheme.primaryColor, width: 2),
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    filled: true,
                    fillColor: Colors.grey.shade50,
                  ),
                  onSubmitted: (value) {
                    final trimmed = value.trim();
                    if (trimmed.isNotEmpty && !_monitoredLocations.contains(trimmed)) {
                      setState(() => _monitoredLocations.add(trimmed));
                      _locationController.clear();
                    }
                  },
                ),
              ),
              const SizedBox(width: 8),
              IconButton(
                onPressed: () {
                  final trimmed = _locationController.text.trim();
                  if (trimmed.isNotEmpty && !_monitoredLocations.contains(trimmed)) {
                    setState(() => _monitoredLocations.add(trimmed));
                    _locationController.clear();
                  }
                },
                icon: const Icon(Icons.add_circle, color: AppTheme.primaryColor, size: 32),
              ),
            ],
          ),
          if (_monitoredLocations.isNotEmpty) ...[
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _monitoredLocations.map((loc) {
                return Chip(
                  label: Text(
                    loc,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  deleteIcon: const Icon(Icons.close, size: 18),
                  onDeleted: () {
                    setState(() => _monitoredLocations.remove(loc));
                  },
                  backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
                  side: BorderSide(
                    color: AppTheme.primaryColor.withValues(alpha: 0.3),
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(20),
                  ),
                );
              }).toList(),
            ),
          ] else ...[
            const SizedBox(height: 12),
            Text(
              'No locations added. Add locations to monitor specific areas.',
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade500,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPriceRangeCard() {
    return _buildCard(
      title: 'Price Range',
      icon: Icons.currency_rupee,
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '₹${(_priceRange.start / 100000).toStringAsFixed(0)}L',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.primaryColor,
                ),
              ),
              Text(
                '₹${(_priceRange.end / 100000).toStringAsFixed(0)}L',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.primaryColor,
                ),
              ),
            ],
          ),
          RangeSlider(
            values: _priceRange,
            min: 0,
            max: 50000000,
            divisions: 50,
            activeColor: AppTheme.primaryColor,
            inactiveColor: Colors.grey.shade200,
            onChanged: (values) {
              setState(() {
                _priceMin = values.start;
                _priceMax = values.end;
              });
            },
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Any',
                style: TextStyle(
                  fontSize: 11,
                  color: Colors.grey.shade500,
                ),
              ),
              Text(
                '50 Cr',
                style: TextStyle(
                  fontSize: 11,
                  color: Colors.grey.shade500,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPropertyTypesCard() {
    final types = [
      {'value': 'plot', 'label': 'Plot', 'icon': Icons.landscape},
      {'value': 'house', 'label': 'House', 'icon': Icons.home},
      {'value': 'flat', 'label': 'Flat', 'icon': Icons.apartment},
      {'value': 'shop', 'label': 'Shop', 'icon': Icons.store},
    ];

    return _buildCard(
      title: 'Property Types',
      icon: Icons.category_outlined,
      child: Row(
        children: types.map((t) {
          final isSelected = _selectedTypes.contains(t['value']);
          return Expanded(
            child: GestureDetector(
              onTap: () {
                setState(() {
                  if (isSelected) {
                    _selectedTypes.remove(t['value']);
                  } else {
                    _selectedTypes.add(t['value'] as String);
                  }
                });
              },
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                margin: EdgeInsets.only(
                  right: t != types.last ? 8 : 0,
                ),
                padding: const EdgeInsets.symmetric(vertical: 16),
                decoration: BoxDecoration(
                  color: isSelected
                      ? AppTheme.primaryColor.withValues(alpha: 0.1)
                      : Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isSelected ? AppTheme.primaryColor : Colors.grey.shade200,
                    width: isSelected ? 2 : 1,
                  ),
                ),
                child: Column(
                  children: [
                    Icon(
                      t['icon'] as IconData,
                      size: 24,
                      color: isSelected ? AppTheme.primaryColor : Colors.grey.shade500,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      t['label'] as String,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: isSelected ? AppTheme.primaryColor : Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildCard({
    required String title,
    required IconData icon,
    required Widget child,
  }) {
    return Card(
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
                Icon(icon, size: 20, color: AppTheme.primaryColor),
                const SizedBox(width: 8),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            child,
          ],
        ),
      ),
    );
  }

  Widget _buildToggleRow(
    String title,
    String subtitle,
    IconData icon,
    bool value,
    ValueChanged<bool> onChanged,
  ) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey.shade600),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey.shade500,
                  ),
                ),
              ],
            ),
          ),
          Switch(
            value: value,
            onChanged: onChanged,
            activeColor: AppTheme.primaryColor,
          ),
        ],
      ),
    );
  }
}
