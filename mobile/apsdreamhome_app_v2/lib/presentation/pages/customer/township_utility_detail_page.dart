import 'package:flutter/material.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

/// Curated display fields per utility desk. Keeps the detail view readable
/// instead of dumping every raw column.
const Map<String, List<String>> _deskFields = {
  'stp': [
    'log_date', 'stp_plant_code', 'raw_sewage_inflow_kl', 'treated_water_output_kl',
    'inlet_bod_mg_l', 'outlet_bod_mg_l', 'inlet_cod_mg_l', 'outlet_cod_mg_l',
    'outlet_tss_mg_l', 'outlet_ph', 'chlorine_dosing_ppm', 'aeration_blower_hours',
    'water_reuse_purpose', 'sludge_cake_disposed_kg', 'uppcb_compliance_status',
    'operator_name', 'operator_phone', 'remarks',
  ],
  'water': [
    'log_date', 'shift', 'pump_number', 'run_hours', 'power_units_kwh',
    'water_pumped_kl', 'oht_level_pct', 'tds_ppm', 'ph_level',
    'chlorine_dose_gm', 'line_pressure_bar', 'pump_health', 'operator_name',
    'operator_phone', 'remarks',
  ],
  'genset': [
    'log_date', 'genset_number', 'run_hours', 'diesel_consumed_litres',
    'diesel_added_litres', 'diesel_closing_stock_litres', 'power_generated_kwh',
    'fuel_efficiency_kwh_per_litre', 'diesel_rate_per_litre', 'diesel_expense_inr',
    'outage_reason', 'mechanical_status', 'operator_name', 'operator_phone', 'remarks',
  ],
  'security': [
    'roster_date', 'shift', 'gate_name', 'guard_name', 'guard_phone', 'agency_name',
    'supervisor_name', 'supervisor_contact', 'status', 'checkin_time',
    'checkout_time', 'boom_barrier_status', 'visitor_vehicles_logged',
  ],
  'security_incidents': [
    'incident_date', 'incident_time', 'category', 'severity', 'location_detail',
    'description', 'action_taken', 'police_informed', 'status', 'resolved_by',
    'resolution_notes',
  ],
  'cctv': [
    'audit_date', 'nvr_identifier', 'total_cameras_installed', 'cameras_online_count',
    'cameras_offline_count', 'uptime_percentage', 'recording_retention_days',
    'hdd_storage_status', 'night_vision_ir_status', 'footage_exported_count',
    'cctv_technician_name', 'technician_phone', 'offline_camera_locations', 'remarks',
  ],
  'waste': [
    'collection_date', 'shift', 'plots_serviced_count', 'wet_waste_kg',
    'dry_waste_kg', 'total_waste_kg', 'compost_pit_diverted_kg',
    'disposal_destination', 'tractor_trips_count', 'sanitation_headcount',
    'sanitation_supervisor', 'supervisor_contact', 'drain_cleaning_status', 'remarks',
  ],
  'horticulture': [
    'census_date', 'park_name', 'green_area_sqft', 'trees_planted_count',
    'tree_species_summary', 'survival_rate_pct', 'watering_method',
    'fertilizer_used_kg', 'gardener_headcount', 'maintenance_tasks',
    'horticulture_supervisor', 'supervisor_contact', 'remarks',
  ],
  'lighting': [
    'audit_date', 'feeder_pillar_code', 'total_poles_connected', 'poles_functional',
    'poles_faulty', 'uptime_percent', 'timer_mode', 'on_time', 'off_time',
    'phase_balance_status', 'cable_health', 'faulty_pole_numbers',
    'electrician_name', 'electrician_phone', 'remarks',
  ],
  'rwh': [
    'audit_date', 'pit_identifier', 'recharge_type', 'catchment_area_sqft',
    'filtration_media_status', 'desilting_chambers_cleared', 'recharge_capacity_kl',
    'water_table_depth_ft', 'monsoon_readiness_score', 'up_groundwater_noc_status',
    'environmental_officer_name', 'officer_contact', 'maintenance_actions_done',
    'remarks',
  ],
  'fire': [
    'inspection_date', 'fire_zone_identifier', 'static_line_pressure_bar',
    'jockey_pump_status', 'main_electric_pump_status', 'diesel_engine_backup_status',
    'fire_water_storage_kl', 'hydrant_valves_tested', 'hydrant_valves_healthy',
    'hose_boxes_equipped', 'extinguishers_checked', 'fire_drill_conducted',
    'up_fire_noc_status', 'noc_certificate_number', 'noc_expiry_date',
    'fire_officer_name', 'fire_officer_contact', 'defects_observed', 'remarks',
  ],
};

class TownshipUtilityDetailPage extends StatefulWidget {
  final String type;

  const TownshipUtilityDetailPage({super.key, required this.type});

  @override
  State<TownshipUtilityDetailPage> createState() =>
      _TownshipUtilityDetailPageState();
}

class _TownshipUtilityDetailPageState extends State<TownshipUtilityDetailPage> {
  bool _loading = true;
  String? _error;
  String _label = '';
  Map<String, dynamic> _summary = {};
  List<Map<String, dynamic>> _records = [];

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
      AppConstants.initBaseUrl();
      final res = await ApiService()
          .get('/api/v2/mobile/township/operations/${widget.type}');
      if (res['success'] == true) {
        final records = (res['records'] ?? []) as List;
        setState(() {
          _label = (res['label'] ?? widget.type).toString();
          _summary = Map<String, dynamic>.from(
              (res['summary'] ?? {}) as Map);
          _records = records
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
          _loading = false;
        });
      } else {
        setState(() {
          _error = (res['message'] ?? 'Could not load this desk.').toString();
          _loading = false;
        });
      }
    } catch (e) {
      debugPrint('utility detail load failed: $e');
      setState(() {
        _error = 'Network error. Please pull down to retry.';
        _loading = false;
      });
    }
  }

  String _labelFor(String key) {
    final cleaned = key.replaceAll('_', ' ');
    return cleaned
        .split(' ')
        .map((w) => w.isEmpty ? w : '${w[0].toUpperCase()}${w.substring(1)}')
        .join(' ');
  }

  String _formatValue(dynamic value) {
    if (value == null) return '—';
    if (value is String) {
      final v = value.trim();
      if (v.isEmpty) return '—';
      // Present enum-style values (e.g. uppcb_compliance_status) in title case.
      if (v.contains('_')) {
        return v
            .split('_')
            .map((w) => w.isEmpty
                ? w
                : '${w[0].toUpperCase()}${w.substring(1)}')
            .join(' ');
      }
      return v;
    }
    if (value is bool) return value ? 'Yes' : 'No';
    if (value is num) {
      if (value == value.roundToDouble()) return value.toInt().toString();
      return value.toStringAsFixed(2);
    }
    return value.toString();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        title: Text(
          _label.isEmpty ? 'Utility Desk' : _label,
          style: const TextStyle(
            fontWeight: FontWeight.w700,
            color: AppTheme.primaryColor,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: AppTheme.primaryColor),
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ListView(
                    children: [
                      const SizedBox(height: 80),
                      const Icon(Icons.cloud_off_outlined,
                          size: 56, color: Colors.black26),
                      const SizedBox(height: 16),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 32),
                        child: Text(
                          _error!,
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Colors.black54),
                        ),
                      ),
                    ],
                  )
                : _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    final count = ((_summary['record_count'] ?? 0) as num).toInt();
    final colonies = ((_summary['colonies_covered'] ?? 0) as num).toInt();
    final latest = (_summary['latest_date'] ?? '').toString();

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        GlassCard(
          child: Column(
            children: [
              _summaryTile('Records', '$count'),
              const Divider(height: 18, color: AppTheme.goldBorder),
              _summaryTile('Colonies covered', '$colonies'),
              const Divider(height: 18, color: AppTheme.goldBorder),
              _summaryTile('Latest log', latest.isEmpty ? '—' : latest),
            ],
          ),
        ),
        const SizedBox(height: 16),
        if (_records.isEmpty)
          const GlassCard(
            child: Column(
              children: [
                Icon(Icons.inbox_outlined,
                    size: 44, color: Colors.black26),
                SizedBox(height: 12),
                Text(
                  'No readings logged yet',
                  style: TextStyle(
                      fontWeight: FontWeight.w700,
                      color: AppTheme.primaryColor),
                ),
                SizedBox(height: 6),
                Text(
                  'Logs are entered from the web admin desk. '
                  'They will appear here automatically.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.black54, fontSize: 12.5),
                ),
              ],
            ),
          )
        else
          ..._records.map(_recordCard),
      ],
    );
  }

  Widget _summaryTile(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label,
            style: const TextStyle(color: Colors.black54, fontSize: 13.5)),
        Text(
          value,
          style: const TextStyle(
            fontWeight: FontWeight.w700,
            color: AppTheme.primaryColor,
            fontSize: 13.5,
          ),
        ),
      ],
    );
  }

  Widget _recordCard(Map<String, dynamic> record) {
    final fields = _deskFields[widget.type] ?? const <String>[];
    final colony = (record['colony_name'] ?? '—').toString();

    final entries = fields
        .where((f) => record.containsKey(f))
        .map((f) => MapEntry(f, _formatValue(record[f])))
        .where((e) => e.value != '—')
        .toList();

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: GlassCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.location_city_outlined,
                    size: 18, color: AppTheme.accentColor),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    colony,
                    style: const TextStyle(
                      fontWeight: FontWeight.w800,
                      color: AppTheme.primaryColor,
                      fontSize: 14.5,
                    ),
                  ),
                ),
              ],
            ),
            if (entries.isNotEmpty) ...[
              const SizedBox(height: 12),
              const Divider(color: AppTheme.goldBorder),
              const SizedBox(height: 8),
              ...entries.map((e) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        SizedBox(
                          width: 140,
                          child: Text(
                            _labelFor(e.key),
                            style: const TextStyle(
                                color: Colors.black54, fontSize: 12.5),
                          ),
                        ),
                        Expanded(
                          child: Text(
                            e.value,
                            style: const TextStyle(
                              fontWeight: FontWeight.w600,
                              color: AppTheme.primaryColor,
                              fontSize: 12.5,
                            ),
                          ),
                        ),
                      ],
                    ),
                  )),
            ],
          ],
        ),
      ),
    );
  }
}
