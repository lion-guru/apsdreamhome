import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:intl/intl.dart';

import '../../../core/services/database_helper.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/colony_model.dart';
import '../../../data/models/plot_model.dart';
import '../../widgets/glass_card.dart';

class OfflineBookingPage extends ConsumerStatefulWidget {
  const OfflineBookingPage({super.key});

  @override
  ConsumerState<OfflineBookingPage> createState() => _OfflineBookingPageState();
}

class _OfflineBookingPageState extends ConsumerState<OfflineBookingPage> {
  final _formKey = GlobalKey<FormState>();
  final _clientNameController = TextEditingController();
  final _clientPhoneController = TextEditingController();
  final _clientEmailController = TextEditingController();
  final _tokenAmountController = TextEditingController();
  final _notesController = TextEditingController();

  String? _selectedColonyId;
  String? _selectedPlotId;
  bool _isOnline = true;
  bool _isSubmitting = false;

  List<ColonyModel> _colonies = [];
  List<PlotModel> _plots = [];
  List<Map<String, dynamic>> _pendingBookings = [];

  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;

  @override
  void initState() {
    super.initState();
    _initConnectivity();
    _loadColonies();
    _loadPendingBookings();
  }

  @override
  void dispose() {
    _connectivitySubscription?.cancel();
    _clientNameController.dispose();
    _clientPhoneController.dispose();
    _clientEmailController.dispose();
    _tokenAmountController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  void _initConnectivity() {
    _connectivitySubscription = Connectivity().onConnectivityChanged.listen((results) {
      final online = results.isNotEmpty && !results.contains(ConnectivityResult.none);
      if (mounted) setState(() => _isOnline = online);
      if (online) _syncPendingBookings();
    });
    Connectivity().checkConnectivity().then((results) {
      final online = results.isNotEmpty && !results.contains(ConnectivityResult.none);
      if (mounted) setState(() => _isOnline = online);
    });
  }

  Future<void> _loadColonies() async {
    try {
      final colonyService = ref.read(colonyServiceProvider);
      final colonies = await colonyService.getColonies();
      if (mounted) setState(() => _colonies = colonies);
    } catch (e) {
      // silent
    }
  }

  Future<void> _loadPlots(String colonyId) async {
    try {
      final colonyService = ref.read(colonyServiceProvider);
      final plots = await colonyService.getPlotsByColony(colonyId);
      if (mounted) {
        setState(() {
          _plots = plots;
          _selectedPlotId = null;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _plots = []);
    }
  }

  Future<void> _loadPendingBookings() async {
    try {
      final db = await DatabaseHelper().database;
      final results = await db.query('bookings', orderBy: 'created_at DESC');
      if (mounted) setState(() => _pendingBookings = results);
    } catch (e) {
      // silent
    }
  }

  Future<void> _submitBooking() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedColonyId == null || _selectedPlotId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select colony and plot'), backgroundColor: Colors.orange),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final authState = ref.read(authProvider);
      final userId = authState?.id.toString() ?? '0';

      final bookingData = {
        'colony_id': _selectedColonyId,
        'plot_id': _selectedPlotId,
        'user_id': userId,
        'client_name': _clientNameController.text.trim(),
        'client_phone': _clientPhoneController.text.trim(),
        'client_email': _clientEmailController.text.trim(),
        'token_amount': double.tryParse(_tokenAmountController.text) ?? 0.0,
        'notes': _notesController.text.trim(),
        'booking_date': DateTime.now().toIso8601String(),
        'status': _isOnline ? 'pending_sync' : 'offline',
        'is_synced': 0,
        'created_at': DateTime.now().toIso8601String(),
      };

      final db = await DatabaseHelper().database;
      await db.insert('bookings', bookingData);

      if (_isOnline) {
        await _syncBookingNow(bookingData);
      }

      _formKey.currentState!.reset();
      _clientNameController.clear();
      _clientPhoneController.clear();
      _clientEmailController.clear();
      _tokenAmountController.clear();
      _notesController.clear();
      setState(() {
        _selectedPlotId = null;
        _isSubmitting = false;
      });
      await _loadPendingBookings();

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(_isOnline
                ? 'Booking submitted & syncing!'
                : 'Booking saved offline. Will sync when online.'),
            backgroundColor: _isOnline ? Colors.green : Colors.blue,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    } catch (e) {
      setState(() => _isSubmitting = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  Future<void> _syncBookingNow(Map<String, dynamic> booking) async {
    try {
      final api = ApiService();
      await api.initialize();
      await api.post('/sync', data: {'type': 'booking', 'data': booking});
      final db = await DatabaseHelper().database;
      await db.rawUpdate(
        'UPDATE bookings SET is_synced = 1, status = ? WHERE created_at = ?',
        ['synced', booking['created_at']],
      );
    } catch (e) {
      // Will retry later via periodic sync
    }
  }

  Future<void> _syncPendingBookings() async {
    try {
      final db = await DatabaseHelper().database;
      final unsynced = await db.query('bookings', where: 'is_synced = ?', whereArgs: [0]);
      for (final booking in unsynced) {
        await _syncBookingNow(Map<String, dynamic>.from(booking));
      }
      await _loadPendingBookings();
    } catch (e) {
      // Will retry
    }
  }

  @override
  Widget build(BuildContext context) {
    PlotModel? selectedPlot;
    for (final p in _plots) {
      if (p.id.toString() == _selectedPlotId) {
        selectedPlot = p;
        break;
      }
    }

    return GradientBackground(
      child: Scaffold(
        backgroundColor: Colors.transparent,
        appBar: AppBar(
          title: const Text('Offline Booking'),
          backgroundColor: Colors.transparent,
          elevation: 0,
          actions: [
            Container(
              margin: const EdgeInsets.only(right: 12),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: (_isOnline ? Colors.green : Colors.orange).withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: _isOnline ? Colors.green : Colors.orange,
                ),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    _isOnline ? Icons.wifi : Icons.wifi_off,
                    size: 16,
                    color: _isOnline ? Colors.green : Colors.orange,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    _isOnline ? 'Online' : 'Offline',
                    style: TextStyle(
                      color: _isOnline ? Colors.green : Colors.orange,
                      fontWeight: FontWeight.w600,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Pending sync count
                if (_pendingBookings.any((b) => b['is_synced'] == 0))
                  GlassCard(
                    opacity: 0.1,
                    child: Row(
                      children: [
                        const Icon(Icons.sync_problem, color: Colors.orange, size: 24),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${_pendingBookings.where((b) => b['is_synced'] == 0).length} booking(s) pending sync',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              if (_isOnline)
                                const Text(
                                  'Auto-syncing now...',
                                  style: TextStyle(color: Colors.green, fontSize: 12),
                                ),
                            ],
                          ),
                        ),
                        if (_isOnline)
                          TextButton(
                            onPressed: _syncPendingBookings,
                            child: const Text('Sync Now', style: TextStyle(color: Colors.cyan)),
                          ),
                      ],
                    ),
                  ),

                const SizedBox(height: 16),

                // Colony & Plot Selection
                GlassCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Select Colony & Plot',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        initialValue: _selectedColonyId,
                        decoration: const InputDecoration(
                          labelText: 'Colony',
                          labelStyle: TextStyle(color: Colors.white70),
                          filled: true,
                          fillColor: Colors.white10,
                          border: OutlineInputBorder(),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Colors.white30),
                          ),
                        ),
                        dropdownColor: const Color(0xFF1A237E),
                        style: const TextStyle(color: Colors.white),
                        items: _colonies.map((colony) {
                          return DropdownMenuItem(
                            value: colony.id.toString(),
                            child: Text(colony.name),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() => _selectedColonyId = value);
                          if (value != null) _loadPlots(value);
                        },
                        validator: (v) => v == null ? 'Select colony' : null,
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        initialValue: _selectedPlotId,
                        decoration: const InputDecoration(
                          labelText: 'Plot',
                          labelStyle: TextStyle(color: Colors.white70),
                          filled: true,
                          fillColor: Colors.white10,
                          border: OutlineInputBorder(),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Colors.white30),
                          ),
                        ),
                        dropdownColor: const Color(0xFF1A237E),
                        style: const TextStyle(color: Colors.white),
                        items: _plots.map((plot) {
                          return DropdownMenuItem(
                            value: plot.id.toString(),
                            child: Text(
                              '${plot.plotNumber} - ${plot.areaSqft.toStringAsFixed(0)} sqft - Rs.${NumberFormat('#,##,###').format(plot.totalPrice)}',
                              style: const TextStyle(fontSize: 13),
                            ),
                          );
                        }).toList(),
                        onChanged: (value) => setState(() => _selectedPlotId = value),
                        validator: (v) => v == null ? 'Select plot' : null,
                      ),
                      if (selectedPlot != null) ...[
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.cyan.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.cyan.withValues(alpha: 0.3)),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 12,
                                height: 12,
                                decoration: BoxDecoration(
                                  color: _plotColor(selectedPlot.status),
                                  shape: BoxShape.circle,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Plot ${selectedPlot.plotNumber}',
                                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                                    ),
                                    Text(
                                      '${selectedPlot.areaSqft.toStringAsFixed(0)} sqft - ${selectedPlot.status}',
                                      style: const TextStyle(color: Colors.white70, fontSize: 12),
                                    ),
                                  ],
                                ),
                              ),
                              Text(
                                'Rs.${NumberFormat('#,##,###').format(selectedPlot.totalPrice)}',
                                style: const TextStyle(
                                  color: Colors.cyan,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 16,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                ),

                const SizedBox(height: 16),

                // Client Details
                GlassCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Client Details',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildTextField(_clientNameController, 'Client Name', Icons.person),
                      const SizedBox(height: 12),
                      _buildTextField(_clientPhoneController, 'Phone', Icons.phone, keyboardType: TextInputType.phone),
                      const SizedBox(height: 12),
                      _buildTextField(_clientEmailController, 'Email (optional)', Icons.email, keyboardType: TextInputType.emailAddress),
                      const SizedBox(height: 12),
                      _buildTextField(_tokenAmountController, 'Token Amount', Icons.currency_rupee, keyboardType: TextInputType.number),
                      const SizedBox(height: 12),
                      _buildTextField(_notesController, 'Notes (optional)', Icons.notes, maxLines: 2),
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // Submit Button
                SizedBox(
                  width: double.infinity,
                  height: 56,
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : _submitBooking,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFFFD700),
                      foregroundColor: const Color(0xFF1A237E),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 4,
                    ),
                    child: _isSubmitting
                        ? const SizedBox(
                            width: 24,
                            height: 24,
                            child: CircularProgressIndicator(
                              strokeWidth: 2.5,
                              color: Color(0xFF1A237E),
                            ),
                          )
                        : Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(_isOnline ? Icons.cloud_upload : Icons.save_alt,
                                  color: const Color(0xFF1A237E)),
                              const SizedBox(width: 8),
                              Text(
                                _isOnline ? 'Submit Booking' : 'Save Offline',
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF1A237E),
                                ),
                              ),
                            ],
                          ),
                  ),
                ),

                const SizedBox(height: 24),

                // Pending bookings list
                if (_pendingBookings.isNotEmpty) ...[
                  Text(
                    'Recent Bookings (${_pendingBookings.length})',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  ..._pendingBookings.take(10).map((booking) => _buildBookingTile(booking)),
                ],
                const SizedBox(height: 32),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildTextField(
    TextEditingController controller,
    String label,
    IconData icon, {
    TextInputType keyboardType = TextInputType.text,
    int maxLines = 1,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      maxLines: maxLines,
      style: const TextStyle(color: Colors.white),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(color: Colors.white70),
        prefixIcon: Icon(icon, color: Colors.white54),
        filled: true,
        fillColor: Colors.white10,
        border: const OutlineInputBorder(),
        enabledBorder: const OutlineInputBorder(
          borderSide: BorderSide(color: Colors.white30),
        ),
        focusedBorder: const OutlineInputBorder(
          borderSide: BorderSide(color: Color(0xFFFFD700), width: 2),
        ),
      ),
      validator: (v) {
        if (maxLines == 1 && (v == null || v.trim().isEmpty)) return 'Required';
        if (label == 'Token Amount' && (v == null || v.isEmpty)) return 'Required';
        return null;
      },
    );
  }

  Widget _buildBookingTile(Map<String, dynamic> booking) {
    final isSynced = booking['is_synced'] == 1;
    final status = booking['status'] ?? 'offline';
    final date = (booking['created_at'] as String?) ?? '';

    return GlassCard(
      opacity: 0.08,
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          Container(
            width: 10,
            height: 10,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isSynced ? Colors.green : Colors.orange,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  (booking['client_name'] as String?) ?? 'Unknown',
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                ),
                Text(
                  'Plot: ${booking['plot_id'] ?? "N/A"} - Rs.${booking['token_amount'] ?? 0}',
                  style: const TextStyle(color: Colors.white70, fontSize: 12),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: (isSynced ? Colors.green : Colors.orange).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  isSynced ? 'Synced' : status.toString().toUpperCase(),
                  style: TextStyle(
                    color: isSynced ? Colors.green : Colors.orange,
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
              if (date.isNotEmpty)
                Text(
                  _formatDate(date),
                  style: const TextStyle(color: Colors.white54, fontSize: 10),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Color _plotColor(String status) {
    switch (status.toLowerCase()) {
      case 'available':
        return AppTheme.plotAvailable;
      case 'hold':
        return AppTheme.plotHold;
      case 'booked':
        return AppTheme.plotBooked;
      case 'sold':
        return AppTheme.plotSold;
      default:
        return Colors.grey;
    }
  }

  String _formatDate(String dateStr) {
    try {
      final dt = DateTime.parse(dateStr);
      return DateFormat('dd MMM, hh:mm a').format(dt);
    } catch (_) {
      return dateStr;
    }
  }
}
