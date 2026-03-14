import 'dart:async';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';

/// GPS Site Visit Tracking Page
/// This page activates when an agent starts a site visit.
/// It logs the agent's GPS location periodically to the backend.
class SiteVisitPage extends StatefulWidget {
  final int? visitId;
  final String? propertyName;
  final double? destLat;
  final double? destLng;

  const SiteVisitPage({
    Key? key,
    this.visitId,
    this.propertyName,
    this.destLat,
    this.destLng,
  }) : super(key: key);

  @override
  _SiteVisitPageState createState() => _SiteVisitPageState();
}

class _SiteVisitPageState extends State<SiteVisitPage> {
  Position? _currentPosition;
  bool _trackingActive = false;
  bool _isLoading = false;
  int? _visitId;
  Timer? _locationTimer;
  String _statusMessage = 'Ready to start site visit';
  int _locationUpdates = 0;
  double? _distanceToSite;

  @override
  void initState() {
    super.initState();
    _visitId = widget.visitId;
    if (_visitId != null) {
      _startTracking();
    }
  }

  @override
  void dispose() {
    _locationTimer?.cancel();
    super.dispose();
  }

  Future<void> _startTracking() async {
    setState(() {
      _isLoading = true;
      _statusMessage = 'Requesting GPS permission...';
    });

    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      setState(() {
        _statusMessage = '⚠️ Location services are disabled. Please enable them.';
        _isLoading = false;
      });
      return;
    }

    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        setState(() {
          _statusMessage = '⚠️ Location permission denied.';
          _isLoading = false;
        });
        return;
      }
    }

    if (permission == LocationPermission.deniedForever) {
      setState(() {
        _statusMessage = '⚠️ Location permanently denied. Grant in phone settings.';
        _isLoading = false;
      });
      return;
    }

    // Get initial position
    final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high);

    setState(() {
      _currentPosition = position;
      _trackingActive = true;
      _isLoading = false;
      _statusMessage = '✅ Live tracking active';
    });

    // Poll location every 30 seconds
    _locationTimer = Timer.periodic(const Duration(seconds: 30), (_) async {
      await _updateLocation();
    });
  }

  Future<void> _updateLocation() async {
    final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high);

    setState(() {
      _currentPosition = position;
      _locationUpdates++;
    });

    if (widget.destLat != null && widget.destLng != null) {
      final dist = Geolocator.distanceBetween(position.latitude, position.longitude,
          widget.destLat!, widget.destLng!);
      setState(() {
        _distanceToSite = dist;
      });
    }

    // TODO: Call apiService.updateSiteVisitLocation(_visitId!, lat, lng)
  }

  Future<void> _stopTracking() async {
    _locationTimer?.cancel();
    setState(() {
      _trackingActive = false;
      _statusMessage = '🏁 Visit completed';
    });
    // TODO: Call apiService to mark visit complete
    if (mounted) {
      Navigator.pop(context, true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0A1628),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0A1628),
        title: const Text('GPS Site Tracking', style: TextStyle(color: Colors.white)),
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          if (_trackingActive)
            Container(
              margin: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
              padding: const EdgeInsets.symmetric(horizontal: 10),
              decoration: BoxDecoration(
                color: Colors.green.withOpacity(0.2),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.green),
              ),
              child: Row(
                children: [
                  const Icon(Icons.circle, color: Colors.green, size: 8),
                  const SizedBox(width: 4),
                  Text('LIVE', style: TextStyle(color: Colors.green, fontSize: 12, fontWeight: FontWeight.bold)),
                ],
              ),
            ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Status Card
            _buildStatusCard(),
            const SizedBox(height: 16),

            // Location Info Card
            if (_currentPosition != null) _buildLocationCard(),
            const SizedBox(height: 16),

            // Distance Card (if destination set)
            if (_distanceToSite != null) _buildDistanceCard(),
            const SizedBox(height: 16),

            // Property Info
            if (widget.propertyName != null)
              _buildInfoCard('🏡 Property', widget.propertyName!),
            const SizedBox(height: 32),

            // Action Buttons
            if (!_trackingActive && !_isLoading)
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton.icon(
                  onPressed: _startTracking,
                  icon: const Icon(Icons.play_arrow),
                  label: const Text('Start Tracking'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
            if (_isLoading)
              const CircularProgressIndicator(color: Colors.green),
            if (_trackingActive)
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton.icon(
                  onPressed: _stopTracking,
                  icon: const Icon(Icons.stop),
                  label: const Text('Complete Visit'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.red,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: _trackingActive
              ? [Colors.green.shade900, Colors.green.shade700]
              : [const Color(0xFF1A237E), const Color(0xFF283593)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: [
          Icon(
            _trackingActive ? Icons.location_on : Icons.location_off,
            color: Colors.white,
            size: 48,
          ),
          const SizedBox(height: 12),
          Text(
            _statusMessage,
            style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w600),
            textAlign: TextAlign.center,
          ),
          if (_locationUpdates > 0) ...[
            const SizedBox(height: 8),
            Text(
              '$_locationUpdates location updates sent',
              style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 13),
            ),
          ]
        ],
      ),
    );
  }

  Widget _buildLocationCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1C2840),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.blue.withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('📍 Current Location', style: TextStyle(color: Colors.blueAccent, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          Text('Lat: ${_currentPosition!.latitude.toStringAsFixed(6)}',
              style: const TextStyle(color: Colors.white70, fontFamily: 'monospace')),
          Text('Lng: ${_currentPosition!.longitude.toStringAsFixed(6)}',
              style: const TextStyle(color: Colors.white70, fontFamily: 'monospace')),
          Text('Accuracy: ${_currentPosition!.accuracy.toStringAsFixed(1)}m',
              style: const TextStyle(color: Colors.white54, fontSize: 12)),
        ],
      ),
    );
  }

  Widget _buildDistanceCard() {
    final km = _distanceToSite! / 1000;
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1C2840),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.orange.withOpacity(0.4)),
      ),
      child: Row(
        children: [
          const Icon(Icons.directions, color: Colors.orange, size: 32),
          const SizedBox(width: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Distance to Site', style: TextStyle(color: Colors.orange, fontSize: 13)),
              Text(
                km < 1 ? '${_distanceToSite!.toStringAsFixed(0)} m' : '${km.toStringAsFixed(2)} km',
                style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard(String label, String value) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1C2840),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(color: Colors.white54, fontSize: 13)),
          const SizedBox(height: 4),
          Text(value, style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
