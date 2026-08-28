import 'dart:convert';
import 'dart:io';
import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';

import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';

/// Export Service - Data Export to Excel/PDF/CSV
class ExportService {
  final ApiService _api;

  ExportService({ApiService? api}) : _api = api ?? ApiService();

  /// Export Bookings to CSV
  Future<String> exportBookingsToCSV({
    DateTime? startDate,
    DateTime? endDate,
    String? status,
    String? colonyId,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (startDate != null) queryParams['start_date'] = startDate.toIso8601String();
      if (endDate != null) queryParams['end_date'] = endDate.toIso8601String();
      if (status != null) queryParams['status'] = status;
      if (colonyId != null) queryParams['colony_id'] = colonyId;

      final result = await _api.request(
        method: 'GET',
        endpoint: 'properties',
        queryParameters: queryParams.isNotEmpty ? queryParams : null,
      );

      final items = List<Map<String, dynamic>>.from(
        ((result['data'] ?? result['properties'] ?? []) as List)
            .map((e) => Map<String, dynamic>.from(e as Map)),
      );

      final csv = StringBuffer();
      csv.writeln(
          'Booking ID,Customer Name,Phone,Colony,Plot,Total Price,Status,Created Date');

      for (final item in items) {
        csv.writeln('"${item['booking_id'] ?? item['id'] ?? ''}",'
            '"${item['customer_name'] ?? item['customerName'] ?? ''}",'
            '"${item['customer_phone'] ?? item['customerPhone'] ?? ''}",'
            '"${item['colony_name'] ?? item['colonyName'] ?? ''}",'
            '"${item['plot_number'] ?? item['plotNumber'] ?? ''}",'
            '${item['total_price'] ?? item['totalPrice'] ?? 0},'
            '${item['status'] ?? 'pending'},'
            '${item['created_at'] ?? item['createdAt'] ?? ''}');
      }

      final directory = await getApplicationDocumentsDirectory();
      final fileName = 'bookings_${DateTime.now().millisecondsSinceEpoch}.csv';
      final file = File('${directory.path}/$fileName');
      await file.writeAsString(csv.toString());

      AppLogger.info('Bookings exported: ${file.path}');
      return file.path;
    } catch (e) {
      AppLogger.error('Error exporting bookings', e);
      rethrow;
    }
  }

  /// Export Commissions to CSV
  Future<String> exportCommissionsToCSV({
    String? associateId,
    String? status,
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (associateId != null) queryParams['associate_id'] = associateId;
      if (status != null) queryParams['status'] = status;
      if (startDate != null) queryParams['start_date'] = startDate.toIso8601String();
      if (endDate != null) queryParams['end_date'] = endDate.toIso8601String();

      final result = await _api.request(
        method: 'GET',
        endpoint: 'mlm/payouts',
        queryParameters: queryParams.isNotEmpty ? queryParams : null,
      );

      final items = List<Map<String, dynamic>>.from(
        ((result['data'] ?? result['payouts'] ?? []) as List)
            .map((e) => Map<String, dynamic>.from(e as Map)),
      );

      final csv = StringBuffer();
      csv.writeln(
          'Commission ID,Associate,Rank,Amount,Percentage,Status,Booking,Date');

      for (final item in items) {
        csv.writeln('"${item['id'] ?? ''}",'
            '"${item['beneficiary_name'] ?? item['beneficiaryName'] ?? ''}",'
            '${item['beneficiary_rank'] ?? item['beneficiaryRank'] ?? ''},'
            '${item['amount'] ?? 0},'
            '${item['percentage'] ?? 0}%,'
            '${item['status'] ?? 'pending'},'
            '${item['booking_id'] ?? item['bookingId'] ?? ''},'
            '${item['created_at'] ?? item['createdAt'] ?? ''}');
      }

      final directory = await getApplicationDocumentsDirectory();
      final fileName =
          'commissions_${DateTime.now().millisecondsSinceEpoch}.csv';
      final file = File('${directory.path}/$fileName');
      await file.writeAsString(csv.toString());

      return file.path;
    } catch (e) {
      AppLogger.error('Error exporting commissions', e);
      rethrow;
    }
  }

  /// Export EMI Schedule to CSV
  Future<String> exportEMIScheduleToCSV() async {
    try {
      final result = await _api.request(
        method: 'GET',
        endpoint: 'mlm/summary',
      );

      final emiData = result['data'] ?? result;
      final items = emiData is Map<String, dynamic>
          ? (emiData['pending_payouts'] ?? emiData['installments'] ?? []) as List
          : [];

      final csv = StringBuffer();
      csv.writeln(
          'Customer,Phone,Plot,Monthly EMI,Total Months,Paid Months,Next Due,Status');

      for (final item in items) {
        final data = item is Map<String, dynamic> ? item : <String, dynamic>{};
        final emiDetails = data['emi_details'] ?? data['emiDetails'] ?? data;

        if (emiDetails is Map<String, dynamic>) {
          final nextDue = emiDetails['next_due_date'] ?? emiDetails['nextDueDate'] ?? '';
          final nextDueStr = nextDue.toString();
          final emiStatus = _calculateEMIStatus(nextDueStr);

          csv.writeln('"${data['customer_name'] ?? data['customerName'] ?? ''}",'
              '"${data['customer_phone'] ?? data['customerPhone'] ?? ''}",'
              '"${data['plot_number'] ?? data['plotNumber'] ?? ''}",'
              '${emiDetails['monthly_emi'] ?? emiDetails['monthlyEMI'] ?? 0},'
              '${emiDetails['total_installments'] ?? emiDetails['totalInstallments'] ?? 0},'
              '${emiDetails['installments_paid'] ?? emiDetails['installmentsPaid'] ?? 0},'
              '$nextDueStr,'
              '$emiStatus');
        }
      }

      final directory = await getApplicationDocumentsDirectory();
      final fileName =
          'emi_schedule_${DateTime.now().millisecondsSinceEpoch}.csv';
      final file = File('${directory.path}/$fileName');
      await file.writeAsString(csv.toString());

      return file.path;
    } catch (e) {
      AppLogger.error('Error exporting EMI schedule', e);
      rethrow;
    }
  }

  /// Export Leads to CSV
  Future<String> exportLeadsToCSV({
    String? assignedTo,
    String? status,
    DateTime? startDate,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (assignedTo != null) queryParams['assigned_to'] = assignedTo;
      if (status != null) queryParams['status'] = status;
      if (startDate != null) queryParams['start_date'] = startDate.toIso8601String();

      final result = await _api.request(
        method: 'GET',
        endpoint: 'leads',
        queryParameters: queryParams.isNotEmpty ? queryParams : null,
      );

      final items = List<Map<String, dynamic>>.from(
        ((result['data'] ?? result['leads'] ?? []) as List)
            .map((e) => Map<String, dynamic>.from(e as Map)),
      );

      final csv = StringBuffer();
      csv.writeln(
          'Lead ID,Name,Phone,Email,Source,Status,Assigned To,Created Date');

      for (final item in items) {
        csv.writeln('"${item['id'] ?? ''}",'
            '"${item['name'] ?? ''}",'
            '"${item['phone'] ?? ''}",'
            '"${item['email'] ?? ''}",'
            '${item['source'] ?? ''},'
            '${item['status'] ?? 'new'},'
            '"${item['assigned_to_name'] ?? item['assignedToName'] ?? ''}",'
            '${item['created_at'] ?? item['createdAt'] ?? ''}');
      }

      final directory = await getApplicationDocumentsDirectory();
      final fileName = 'leads_${DateTime.now().millisecondsSinceEpoch}.csv';
      final file = File('${directory.path}/$fileName');
      await file.writeAsString(csv.toString());

      return file.path;
    } catch (e) {
      AppLogger.error('Error exporting leads', e);
      rethrow;
    }
  }

  /// Export complete data backup (JSON)
  Future<String> createFullBackup() async {
    try {
      final backup = <String, dynamic>{};

      // Export all collections via REST API
      final endpoints = {
        'users': 'profile',
        'colonies': 'properties',
        'plots': 'properties',
        'bookings': 'properties',
        'commissions': 'mlm/summary',
        'leads': 'leads',
        'payments': 'mlm/payouts',
      };

      for (final entry in endpoints.entries) {
        try {
          final result = await _api.request(
            method: 'GET',
            endpoint: entry.value,
          );
          backup[entry.key] = result['data'] ?? result;
        } catch (e) {
          AppLogger.error('Error backing up ${entry.key}', e);
          backup[entry.key] = [];
        }
      }

      // Save as JSON
      final directory = await getApplicationDocumentsDirectory();
      final fileName = 'backup_${DateTime.now().millisecondsSinceEpoch}.json';
      final file = File('${directory.path}/$fileName');

      await file.writeAsString(jsonEncode(backup));

      AppLogger.info('Full backup created: ${file.path}');
      return file.path;
    } catch (e) {
      AppLogger.error('Error creating backup', e);
      rethrow;
    }
  }

  /// Share exported file
  Future<void> shareExportedFile(String filePath, {String? subject}) async {
    try {
      await Share.shareXFiles(
        [XFile(filePath)],
        subject: subject ?? 'APS Dream Home Export',
      );
    } catch (e) {
      AppLogger.error('Error sharing file', e);
      rethrow;
    }
  }

  /// Get export file size
  Future<String> getFileSize(String filePath) async {
    try {
      final file = File(filePath);
      final bytes = await file.length();

      if (bytes < 1024) {
        return '$bytes B';
      } else if (bytes < 1024 * 1024) {
        return '${(bytes / 1024).toStringAsFixed(1)} KB';
      } else {
        return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
      }
    } catch (e) {
      return 'Unknown';
    }
  }

  String _calculateEMIStatus(String nextDueDateStr) {
    if (nextDueDateStr.isEmpty) return 'Unknown';

    final nextDueDate = DateTime.tryParse(nextDueDateStr);
    if (nextDueDate == null) return 'Unknown';

    final now = DateTime.now();
    if (nextDueDate.isBefore(now)) {
      return 'Overdue';
    } else if (nextDueDate.difference(now).inDays <= 7) {
      return 'Due Soon';
    } else {
      return 'Active';
    }
  }
}
