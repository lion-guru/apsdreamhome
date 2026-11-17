import 'dart:async';
import 'dart:math';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class CheckInPage extends ConsumerStatefulWidget {
  const CheckInPage({super.key});

  @override
  ConsumerState<CheckInPage> createState() => _CheckInPageState();
}

class _CheckInPageState extends ConsumerState<CheckInPage> {
  bool _isLoading = false;
  bool _zonesLoading = true;
  bool _isPunchedIn = false;
  Map<String, dynamic>? _todayRecord;
  List<Map<String, dynamic>> _zones = [];
  String? _error;
  String? _status;
  String? _distanceFromNearest;
  String? _nearestZoneName;

  @override
  void initState() {
    super.initState();
    _refresh();
  }

  Future<void> _refresh() async {
    setState(() {
      _isLoading = true;
      _zonesLoading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      // Load assigned zones (server-driven — admin can edit office/site locations)
      final z = await api.get('/geo-attendance/zones');
      if (z['success'] == true && z['data'] is List) {
        _zones = List<Map<String, dynamic>>.from(z['data'] as List);
      }

      // Today's attendance record
      try {
        final r = await api.get('/geo-attendance/me/today');
        if (r['success'] == true && r['data'] is Map<String, dynamic>) {
          final chk = (r['data'] as Map<String, dynamic>)['checkin'] as Map<String, dynamic>?;
          _isPunchedIn = chk != null && chk['check_out_at'] == null;
          _todayRecord = chk;
          _status = chk?['status']?.toString();
        }
      } catch (_) {
        // Today-query failure must not block the page
      }

      // Position for distance display
      try {
        final pos = await _getCurrentPosition(silent: true);
        if (pos != null) {
          _refreshNearestZoneInfo(pos);
        }
      } catch (_) {
        // GPS is optional for status display
      }
    } catch (e) {
      _error = 'Load failed: $e';
    } finally {
      setState(() {
        _isLoading = false;
        _zonesLoading = false;
      });
    }
  }

  double? _distanceToMeters({required double lat, required double lng}) {
    if (_zones.isEmpty) return null;
    double? best;
    for (final z in _zones) {
      final d = _haversineDistance(
        lat,
        lng,
        (z['latitude'] as num?)?.toDouble() ?? 0,
        (z['longitude'] as num?)?.toDouble() ?? 0,
      );
      if (best == null || d < best) best = d;
    }
    return best;
  }

  void _refreshNearestZoneInfo(Position pos) {
    if (_zones.isEmpty) return;
    Map<String, dynamic>? nearest;
    double? best;
    for (final z in _zones) {
      final d = _haversineDistance(
        pos.latitude,
        pos.longitude,
        (z['latitude'] as num?)?.toDouble() ?? 0,
        (z['longitude'] as num?)?.toDouble() ?? 0,
      );
      if (best == null || d < best) {
        best = d;
        nearest = z;
      }
    }
    _distanceFromNearest = best?.round().toString();
    _nearestZoneName = nearest?['name']?.toString();
  }

  Future<Position?> _getCurrentPosition({bool silent = false}) async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      if (!silent) setState(() => _error = 'Location services are disabled');
      return null;
    }

    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        if (!silent) setState(() => _error = 'Location permission denied');
        return null;
      }
    }
    if (permission == LocationPermission.deniedForever) {
      if (!silent) setState(() => _error = 'Location permission permanently denied');
      return null;
    }

    return Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(accuracy: LocationAccuracy.high));
  }

  double _haversineDistance(double lat1, double lng1, double lat2, double lng2) {
    const earthRadius = 6371000.0;
    final dLat = _deg2rad(lat2 - lat1);
    final dLng = _deg2rad(lng2 - lng1);
    final a = sin(dLat / 2) * sin(dLat / 2) +
        cos(_deg2rad(lat1)) *
            cos(_deg2rad(lat2)) *
            sin(dLng / 2) *
            sin(dLng / 2);
    final c = 2 * atan2(sqrt(a), sqrt(1 - a));
    return earthRadius * c;
  }

  double _deg2rad(double deg) => deg * (pi / 180.0);

  Future<void> _punchIn() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final position = await _getCurrentPosition();
      if (position == null) return;

      final distance = _distanceToMeters(lat: position.latitude, lng: position.longitude);
      setState(() {
        _distanceFromNearest = distance?.round().toString();
        // find nearest zone name
        if (_zones.isNotEmpty && distance != null) {
          Map<String, dynamic>? nearest;
          double best = double.infinity;
          for (final z in _zones) {
            final d = _haversineDistance(
              position.latitude,
              position.longitude,
              (z['latitude'] as num?)?.toDouble() ?? 0,
              (z['longitude'] as num?)?.toDouble() ?? 0,
            );
            if (d < best) {
              best = d;
              nearest = z;
            }
          }
          _nearestZoneName = nearest?['name']?.toString();
        }
      });

      final api = ref.read(apiServiceProvider);
      final response = await api.post('/geo-attendance/check-in', data: {
        'latitude': position.latitude,
        'longitude': position.longitude,
        'accuracy_m': position.accuracy,
        'speed_kmh': (position.speed * 3.6).abs(),
        'altitude_m': position.altitude,
        'is_mock_gps': position.isMocked,
      });

      if (response['success'] == true) {
        await _refresh();
        if (mounted) {
          final status = (response['data'] ?? const {})['status'] as String?;
          final msg = (response['data'] ?? const {})['message'] as String?;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(msg ?? 'Punch-in recorded!'),
              backgroundColor: status == 'valid'
                  ? AppTheme.successColor
                  : AppTheme.warningColor,
            ),
          );
        }
      } else {
        setState(() =>
            _error = (response['error'] ?? response['message'] ?? 'Failed to punch in').toString());
      }
    } catch (e) {
      setState(() => _error = 'Error: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _punchOut() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final position = await _getCurrentPosition(silent: true);
      final api = ref.read(apiServiceProvider);
      final response = await api.post('/geo-attendance/check-out', data: {
        'latitude': position?.latitude ?? 0.0,
        'longitude': position?.longitude ?? 0.0,
        'work_log': '',
      });

      if (response['success'] == true) {
        if (!mounted) return;
        await _refresh();
      } else {
        setState(() =>
            _error = (response['error'] ?? response['message'] ?? 'Failed to punch out').toString());
      }
    } catch (e) {
      setState(() => _error = 'Error: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final office = _zones.firstWhere(
      (z) => (z['type'] as String?) == 'office',
      orElse: () => const <String, dynamic>{},
    );
    final officeName = office['name']?.toString() ?? 'Office';
    final officeRadius = office['radius_m']?.toString() ?? '150';
    final officeSelfie = (office['selfie_required'] as bool?) ?? false;

    if (_zonesLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          // Status card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: _isPunchedIn
                    ? [AppTheme.successColor, AppTheme.successColor.withValues(alpha: 0.7)]
                    : [AppTheme.primaryColor, AppTheme.primaryColor.withValues(alpha: 0.7)],
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: (_isPunchedIn ? AppTheme.successColor : AppTheme.primaryColor)
                      .withValues(alpha: 0.3),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              children: [
                Icon(
                  _isPunchedIn ? Icons.check_circle : Icons.access_time,
                  size: 64,
                  color: Colors.white,
                ),
                const SizedBox(height: 12),
                Text(
                  _isPunchedIn ? 'Checked In' : 'Not Checked In',
                  style: const TextStyle(
                    fontFamily: 'Inter',
                    fontSize: 24,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                if (_todayRecord != null) ...[
                  const SizedBox(height: 8),
                  Text(
                    'In: ${_todayRecord!['check_in_at'] ?? 'N/A'}',
                    style: const TextStyle(fontFamily: 'Inter', color: Colors.white70),
                  ),
                  if (_status != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      'Status: $_status',
                      style: const TextStyle(fontFamily: 'Inter', color: Colors.white70, fontSize: 12),
                    ),
                  ],
                ],
              ],
            ),
          ),
          const SizedBox(height: 24),

          // Error message
          if (_error != null)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppTheme.errorColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppTheme.errorColor.withValues(alpha: 0.3)),
              ),
              child: Text(
                _error!,
                style: const TextStyle(fontFamily: 'Inter', color: AppTheme.errorColor, fontSize: 13),
              ),
            ),

          if (_distanceFromNearest != null) ...[
            const SizedBox(height: 8),
            Text(
              'Distance from ${_nearestZoneName ?? 'zone'}: ${_distanceFromNearest}m',
              style: TextStyle(fontFamily: 'Inter', color: Colors.grey.shade700),
            ),
          ],

          const SizedBox(height: 24),

          // Action button
          SizedBox(
            width: double.infinity,
            height: 56,
            child: ElevatedButton(
              onPressed: _isLoading ? null : (_isPunchedIn ? _punchOut : _punchIn),
              style: ElevatedButton.styleFrom(
                backgroundColor: _isPunchedIn ? AppTheme.errorColor : AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                elevation: 2,
              ),
              child: _isLoading
                  ? const CircularProgressIndicator(color: Colors.white)
                  : Text(
                      _isPunchedIn ? 'PUNCH OUT' : 'PUNCH IN',
                      style: const TextStyle(
                        fontFamily: 'Inter',
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 1.2,
                      ),
                    ),
            ),
          ),

          const SizedBox(height: 24),

          // Office info — data now comes from server
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.grey.shade100,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Office Location',
                  style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 4),
                Text(
                  officeName,
                  style: TextStyle(fontFamily: 'Inter', color: Colors.grey.shade700),
                ),
                Text(
                  'Radius: ${officeRadius}m${officeSelfie ? ' · Selfie required' : ''}',
                  style: TextStyle(
                    fontFamily: 'Inter',
                    color: Colors.grey.shade600,
                    fontSize: 12,
                  ),
                ),
                const SizedBox(height: 12),
                if (_zones.length > 1) ...[
                  const Divider(),
                  const SizedBox(height: 8),
                  const Text(
                    'Field sites',
                    style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w600, fontSize: 12),
                  ),
                  const SizedBox(height: 4),
                  for (final z in _zones.where((z) => (z['type'] as String?) != 'office'))
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 2),
                      child: Row(
                        children: [
                          const Icon(Icons.location_on, size: 14, color: AppTheme.successColor),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              '${z['name']} — ${z['radius_m']}m${((z['selfie_required'] as bool?) ?? false) ? ' · selfie' : ''}',
                              style: TextStyle(
                                fontFamily: 'Inter',
                                fontSize: 11,
                                color: Colors.grey.shade700,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
