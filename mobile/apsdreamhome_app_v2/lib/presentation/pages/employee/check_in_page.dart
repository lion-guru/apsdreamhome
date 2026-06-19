import 'dart:math';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
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
  bool _isPunchedIn = false;
  Map<String, dynamic>? _todayRecord;
  double? _distanceFromOffice;
  String? _error;

  static const double _officeLat = 26.8402;
  static const double _officeLng = 83.3012;
  static const int _radiusMeters = 100;

  @override
  void initState() {
    super.initState();
    _loadStatus();
  }

  Future<void> _loadStatus() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('/attendance/status');
      if (response['success'] == true) {
        setState(() {
          _todayRecord = (response['today'] as Map<String, dynamic>?) ?? _todayRecord;
          _isPunchedIn = (response['is_punched_in'] as bool?) ?? false;
        });
      }
    } catch (e) {
      // Silent fail on load
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<Position?> _getCurrentPosition() async {
    final bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      setState(() => _error = 'Location services are disabled');
      return null;
    }

    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        setState(() => _error = 'Location permission denied');
        return null;
      }
    }

    if (permission == LocationPermission.deniedForever) {
      setState(() => _error = 'Location permission permanently denied');
      return null;
    }

    return await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high);
  }

  double _haversineDistance(double lat1, double lng1, double lat2, double lng2) {
    const earthRadius = 6371000.0;
    final dLat = _deg2rad(lat2 - lat1);
    final dLng = _deg2rad(lng2 - lng1);
    final a = sin(dLat / 2) * sin(dLat / 2) +
        cos(_deg2rad(lat1)) * cos(_deg2rad(lat2)) *
            sin(dLng / 2) * sin(dLng / 2);
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

      final distance = _haversineDistance(
        position.latitude,
        position.longitude,
        _officeLat,
        _officeLng,
      );
      setState(() => _distanceFromOffice = distance);

      if (distance > _radiusMeters) {
        setState(() {
          _error =
              'You are ${distance.round()}m from office. Must be within ${_radiusMeters}m to check in.';
          _isLoading = false;
        });
        return;
      }

      final api = ref.read(apiServiceProvider);
      final response = await api.post('/attendance/punch-in', data: {
        'latitude': position.latitude,
        'longitude': position.longitude,
      });

      if (response['success'] == true) {
        await _loadStatus();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Punch-in recorded!'),
              backgroundColor: AppTheme.successColor,
            ),
          );
        }
      } else {
        setState(() => _error = (response['error'] as String?) ?? 'Failed to punch in');
      }
    } catch (e) {
      setState(() => _error = 'Error: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _punchOut() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.post('/attendance/punch-out', data: {});

      if (response['success'] == true) {
        await _loadStatus();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Punch-out recorded!'),
              backgroundColor: AppTheme.successColor,
            ),
          );
        }
      } else {
        setState(() => _error = (response['error'] as String?) ?? 'Failed to punch out');
      }
    } catch (e) {
      setState(() => _error = 'Error: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Attendance',
            style: GoogleFonts.outfit(fontWeight: FontWeight.w600)),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: _isLoading && _todayRecord == null
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
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
                            ? [
                                AppTheme.successColor,
                                AppTheme.successColor.withValues(alpha: 0.7)
                              ]
                            : [
                                AppTheme.primaryColor,
                                AppTheme.primaryColor.withValues(alpha: 0.7)
                              ],
                      ),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: (_isPunchedIn
                                  ? AppTheme.successColor
                                  : AppTheme.primaryColor)
                              .withValues(alpha: 0.3),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Column(
                      children: [
                        Icon(
                          _isPunchedIn
                              ? Icons.check_circle
                              : Icons.access_time,
                          size: 64,
                          color: Colors.white,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          _isPunchedIn ? 'Checked In' : 'Not Checked In',
                          style: GoogleFonts.outfit(
                              fontSize: 24,
                              fontWeight: FontWeight.w700,
                              color: Colors.white),
                        ),
                        if (_todayRecord != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            'In: ${_todayRecord!['punch_in_time'] ?? 'N/A'}',
                            style:
                                GoogleFonts.inter(color: Colors.white70),
                          ),
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
                        border: Border.all(
                            color: AppTheme.errorColor.withValues(alpha: 0.3)),
                      ),
                      child: Text(_error!,
                          style: GoogleFonts.inter(
                              color: AppTheme.errorColor, fontSize: 13)),
                    ),

                  if (_distanceFromOffice != null) ...[
                    const SizedBox(height: 8),
                    Text(
                      'Distance from office: ${_distanceFromOffice!.round()}m',
                      style:
                          GoogleFonts.inter(color: Colors.grey.shade600),
                    ),
                  ],

                  const SizedBox(height: 24),

                  // Action button
                  SizedBox(
                    width: double.infinity,
                    height: 56,
                    child: ElevatedButton(
                      onPressed:
                          _isLoading ? null : (_isPunchedIn ? _punchOut : _punchIn),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _isPunchedIn
                            ? AppTheme.errorColor
                            : AppTheme.primaryColor,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                        elevation: 2,
                      ),
                      child: _isLoading
                          ? const CircularProgressIndicator(
                              color: Colors.white)
                          : Text(
                              _isPunchedIn ? 'PUNCH OUT' : 'PUNCH IN',
                              style: GoogleFonts.outfit(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w700,
                                  letterSpacing: 1.2),
                            ),
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Office info
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Office Location',
                            style: GoogleFonts.outfit(
                                fontWeight: FontWeight.w600)),
                        const SizedBox(height: 4),
                        Text('Kunraghat Office, Gorakhpur',
                            style: GoogleFonts.inter(
                                color: Colors.grey.shade600)),
                        Text('Radius: ${_radiusMeters}m',
                            style: GoogleFonts.inter(
                                color: Colors.grey.shade600,
                                fontSize: 12)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
