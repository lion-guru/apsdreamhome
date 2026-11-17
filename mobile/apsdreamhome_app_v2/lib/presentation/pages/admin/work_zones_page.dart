import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive_helper.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

/// Admin GPS Work Zones — create/edit geofences for office and field sites.
/// Latitude/longitude captured from the device standing there (never guessed).
class AdminWorkZonesPage extends ConsumerStatefulWidget {
  const AdminWorkZonesPage({super.key});

  @override
  ConsumerState<AdminWorkZonesPage> createState() =>
      _AdminWorkZonesPageState();
}

class _AdminWorkZonesPageState extends ConsumerState<AdminWorkZonesPage> {
  List<Map<String, dynamic>> _zones = [];
  bool _loading = true;
  bool _busy = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final res = await api.get('/admin/work-zones');
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _zones = (res['data'] as List)
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        });
      } else {
        setState(() {
          _zones = [];
          _error = res['error'] as String? ?? 'Failed to load zones';
        });
      }
    } catch (e) {
      setState(() => _error = 'Failed to load zones: $e');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _toggleZone(Map<String, dynamic> zone) async {
    final nextActive = (zone['is_active'] as bool?) != true;
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final res = await api.post('/admin/work-zones/${zone['id']}/toggle',
          data: {'active': nextActive});
      if (res['success'] == true) {
        await _load();
      } else {
        setState(() => _error = res['error'] as String? ?? 'Toggle failed');
      }
    } catch (e) {
      setState(() => _error = 'Toggle failed: $e');
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _editZone([Map<String, dynamic>? zone]) async {
    final saved = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => _ZoneFormDialog(zone: zone),
    );
    if (saved == true && mounted) await _load();
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    return Scaffold(
      backgroundColor: Colors.transparent,
      body: RefreshIndicator(
        onRefresh: _load,
        child: _error == null
            ? ListView(
                padding: const EdgeInsets.fromLTRB(24, 20, 24, 80),
                children: [
                  // Header
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      boxShadow: [
                        BoxShadow(color: Color(0x0D000000), blurRadius: 15),
                      ],
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.my_location,
                            size: 32, color: AppTheme.primaryColor),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('GPS Work Zones',
                                  style: TextStyle(
                                      fontSize: ResponsiveHelper.fontSize(context, 20),
                                      fontWeight: FontWeight.bold,
                                      fontFamily: 'Inter')),
                              const Text(
                                  'Office geofence & field sites. Stand at a site and tap Use Current Location inside the form.',
                                  style: TextStyle(color: Colors.grey, fontSize: 12, fontFamily: 'Inter')),
                            ],
                          ),
                        ),
                        FilledButton.icon(
                          onPressed: () => _editZone(),
                          icon: const Icon(Icons.add),
                          label: const Text('Add Zone'),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text('${_zones.length} zones',
                      style: const TextStyle(
                          fontFamily: 'Inter', color: Colors.grey, fontSize: 12)),
                  const SizedBox(height: 8),
                  if (_zones.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(32),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Center(
                        child: Text('No zones configured.\nAdd the office zone to enable geofenced attendance.',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontFamily: 'Inter', color: Colors.grey)),
                      ),
                    ),
                  for (final zone in _zones)
                    Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: (zone['type']?.toString() == 'office')
                              ? AppTheme.primaryColor.withValues(alpha: 0.4)
                              : const Color(0xFFE2E8F0),
                        ),
                        boxShadow: const [
                          BoxShadow(color: Color(0x08000000), blurRadius: 10),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.location_on, size: 18, color: AppTheme.successColor),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  zone['name']?.toString() ?? 'Zone ${zone['id']}',
                                  style: const TextStyle(
                                      fontFamily: 'Inter',
                                      fontWeight: FontWeight.w700,
                                      fontSize: 15),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: _typeColor(zone['type']?.toString() ?? 'site'),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(_typeLabel(zone['type']?.toString() ?? 'site'),
                                    style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 11,
                                        fontWeight: FontWeight.w600,
                                        fontFamily: 'Inter')),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Wrap(
                            spacing: 12,
                            runSpacing: 4,
                            children: [
                              _chip('Lat', '${zone['latitude']}'),
                              _chip('Lng', '${zone['longitude']}'),
                              _chip('Radius', '${zone['radius_m']} m'),
                              if ((zone['selfie_required'] as bool?) == true)
                                _chip('Selfie', 'Required'),
                              _chip('Status', (zone['is_active'] as bool?) == true ? 'Live' : 'Off',
                                  active: (zone['is_active'] as bool?) == true),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.end,
                            children: [
                              OutlinedButton.icon(
                                onPressed: (_busy || zone['id'] == null) ? null : () => _toggleZone(zone),
                                icon: Icon(
                                  (zone['is_active'] as bool?) == true
                                      ? Icons.power_off
                                      : Icons.power,
                                  size: 16,
                                ),
                                label: Text((zone['is_active'] as bool?) == true ? 'Disable' : 'Enable'),
                              ),
                              const SizedBox(width: 8),
                              FilledButton.icon(
                                onPressed: _busy ? null : () => _editZone(zone),
                                icon: const Icon(Icons.edit, size: 16),
                                label: const Text('Edit'),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                ],
              )
            : _errorView(),
      ),
    );
  }

  Widget _errorView() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.error_outline, size: 48, color: AppTheme.errorColor),
          const SizedBox(height: 12),
          Text(_error ?? 'Something went wrong',
              textAlign: TextAlign.center,
              style: const TextStyle(fontFamily: 'Inter', color: AppTheme.errorColor)),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: _load,
            icon: const Icon(Icons.refresh, size: 16),
            label: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _chip(String label, String value, {bool active = false}) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.baseline,
      textBaseline: TextBaseline.alphabetic,
      children: [
        Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey, fontFamily: 'Inter')),
        const SizedBox(width: 4),
        Text(value,
            style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: active ? AppTheme.successColor : Colors.black87,
                fontFamily: 'Inter')),
      ],
    );
  }

  Color _typeColor(String type) {
    switch (type) {
      case 'office':
        return AppTheme.primaryColor;
      case 'client_site':
        return AppTheme.infoColor;
      default:
        return AppTheme.warningColor;
    }
  }

  String _typeLabel(String type) {
    switch (type) {
      case 'office':
        return 'Office';
      case 'client_site':
        return 'Client Site';
      default:
        return 'Field Site';
    }
  }
}

class _ZoneFormDialog extends ConsumerStatefulWidget {
  final Map<String, dynamic>? zone;

  const _ZoneFormDialog({this.zone});

  @override
  ConsumerState<_ZoneFormDialog> createState() => _ZoneFormDialogState();
}

class _ZoneFormDialogState extends ConsumerState<_ZoneFormDialog> {
  late final TextEditingController _name;
  late final TextEditingController _lat;
  late final TextEditingController _lng;
  late final TextEditingController _radius;
  late String _type;
  late bool _selfie;
  bool _saving = false;
  bool _gpsBusy = false;
  String? _formError;

  @override
  void initState() {
    super.initState();
    final z = widget.zone;
    _name = TextEditingController(text: z?['name']?.toString() ?? '');
    _lat = TextEditingController(text: (z?['latitude'])?.toString() ?? '');
    _lng = TextEditingController(text: (z?['longitude'])?.toString() ?? '');
    _radius = TextEditingController(text: (z?['radius_m'])?.toString() ?? '150');
    _type = z?['type']?.toString() ?? 'site';
    _selfie = (z?['selfie_required'] as bool?) ?? false;
  }

  @override
  void dispose() {
    _name.dispose();
    _lat.dispose();
    _lng.dispose();
    _radius.dispose();
    super.dispose();
  }

  Future<void> _useCurrentLocation() async {
    setState(() {
      _gpsBusy = true;
      _formError = null;
    });
    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        setState(() => _formError = 'Location services disabled');
        return;
      }
      LocationPermission perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.denied || perm == LocationPermission.deniedForever) {
        setState(() => _formError = 'Location permission denied');
        return;
      }
      final pos = await Geolocator.getCurrentPosition(
          locationSettings: const LocationSettings(accuracy: LocationAccuracy.high));
      setState(() {
        _lat.text = pos.latitude.toStringAsFixed(7);
        _lng.text = pos.longitude.toStringAsFixed(7);
      });
    } catch (e) {
      setState(() => _formError = 'GPS error: $e');
    } finally {
      if (mounted) setState(() => _gpsBusy = false);
    }
  }

  Future<void> _save() async {
    final lat = double.tryParse(_lat.text.trim());
    final lng = double.tryParse(_lng.text.trim());
    final radius = int.tryParse(_radius.text.trim());
    if (_name.text.trim().isEmpty) {
      setState(() => _formError = 'Name is required');
      return;
    }
    if (lat == null || lat < 6.0 || lat > 37.5) {
      setState(() => _formError = 'Latitude must be 6–37.5 (India). Swapped or wrong?');
      return;
    }
    if (lng == null || lng < 68.0 || lng > 98.0) {
      setState(() => _formError = 'Longitude must be 68–98 (India). Swapped or wrong?');
      return;
    }
    if (radius == null || radius < 10 || radius > 10000) {
      setState(() => _formError = 'Radius must be 10–10000 metres');
      return;
    }

    setState(() {
      _saving = true;
      _formError = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final body = {
        'name': _name.text.trim(),
        'type': _type,
        'latitude': lat,
        'longitude': lng,
        'radius_m': radius,
        'selfie_required': _selfie,
      };
      final z = widget.zone;
      final res = z == null
          ? await api.post('/admin/work-zones/store', data: body)
          : await api.post('/admin/work-zones/${z['id']}/update', data: body);
      if (res['success'] == true) {
        if (mounted) Navigator.of(context).pop(true);
      } else {
        setState(() => _formError = res['error'] as String? ?? 'Save failed');
      }
    } catch (e) {
      setState(() => _formError = 'Save error: $e');
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text(
        widget.zone == null ? 'Add Work Zone' : 'Edit Work Zone',
        style: const TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w700),
      ),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (_formError != null)
              Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppTheme.errorColor.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(_formError!,
                    style: const TextStyle(color: AppTheme.errorColor, fontSize: 13, fontFamily: 'Inter')),
              ),
            TextField(
              controller: _name,
              decoration: const InputDecoration(
                labelText: 'Name',
                hintText: 'e.g. Kunraghat Office',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _type,
              decoration: const InputDecoration(
                labelText: 'Zone Type',
                border: OutlineInputBorder(),
              ),
              items: const [
                DropdownMenuItem(value: 'office', child: Text('Office')),
                DropdownMenuItem(value: 'site', child: Text('Field Site')),
                DropdownMenuItem(value: 'client_site', child: Text('Client Site')),
              ],
              onChanged: (v) => setState(() => _type = v ?? 'site'),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _lat,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Latitude',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextField(
                    controller: _lng,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Longitude',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: _gpsBusy ? null : _useCurrentLocation,
              icon: _gpsBusy
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Icon(Icons.my_location),
              label: Text(_gpsBusy ? 'Reading GPS…' : 'Use Current Location'),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _radius,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Radius (metres)',
                      hintText: '150',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Selfie required', style: TextStyle(fontSize: 13, fontFamily: 'Inter')),
                    value: _selfie,
                    onChanged: (v) => setState(() => _selfie = v),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
      actions: [
        TextButton(
          onPressed: _saving ? null : () => Navigator.of(context).pop(false),
          child: const Text('Cancel'),
        ),
        FilledButton(
          onPressed: _saving ? null : _save,
          child: Text(_saving ? 'Saving…' : 'Save'),
        ),
      ],
    );
  }
}
