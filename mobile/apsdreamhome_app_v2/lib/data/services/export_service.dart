import 'dart:convert';
import 'dart:io';
import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';
import 'package:cloud_firestore/cloud_firestore.dart';

import '../../core/utils/logger.dart';

/// Export Service - Data Export to Excel/PDF/CSV
class ExportService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  /// Export Bookings to CSV
  Future<String> exportBookingsToCSV({
    DateTime? startDate,
    DateTime? endDate,
    String? status,
    String? colonyId,
  }) async {
    try {
      Query query = _firestore
          .collection('bookings')
          .orderBy('createdAt', descending: true);

      if (startDate != null) {
        query = query.where('createdAt', isGreaterThanOrEqualTo: startDate);
      }
      if (endDate != null) {
        query = query.where('createdAt', isLessThanOrEqualTo: endDate);
      }
      if (status != null) {
        query = query.where('status', isEqualTo: status);
      }
      if (colonyId != null) {
        query = query.where('colonyId', isEqualTo: colonyId);
      }

      final snapshot = await query.get();

      // CSV Header
      final csv = StringBuffer();
      csv.writeln(
          'Booking ID,Customer Name,Phone,Colony,Plot,Total Price,Status,Created Date');

      // CSV Data
      for (final doc in snapshot.docs) {
        final data = doc.data() as Map<String, dynamic>;
        csv.writeln('${doc.id},'
            '"${data['customerName'] ?? ''}",'
            '"${data['customerPhone'] ?? ''}",'
            '"${data['colonyName'] ?? ''}",'
            '"${data['plotNumber'] ?? ''}",'
            '${data['totalPrice'] ?? 0},'
            '${data['status'] ?? 'pending'},'
            '${data['createdAt']?.toDate()?.toString() ?? ''}');
      }

      // Save to file
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
      Query query = _firestore
          .collection('commissions')
          .orderBy('createdAt', descending: true);

      if (associateId != null) {
        query = query.where('beneficiaryId', isEqualTo: associateId);
      }
      if (status != null) {
        query = query.where('status', isEqualTo: status);
      }
      if (startDate != null) {
        query = query.where('createdAt', isGreaterThanOrEqualTo: startDate);
      }
      if (endDate != null) {
        query = query.where('createdAt', isLessThanOrEqualTo: endDate);
      }

      final snapshot = await query.get();

      final csv = StringBuffer();
      csv.writeln(
          'Commission ID,Associate,Rank,Amount,Percentage,Status,Booking,Date');

      for (final doc in snapshot.docs) {
        final data = doc.data() as Map<String, dynamic>;
        csv.writeln('${doc.id},'
            '"${data['beneficiaryName'] ?? ''}",'
            '${data['beneficiaryRank'] ?? ''},'
            '${data['amount'] ?? 0},'
            '${data['percentage'] ?? 0}%,'
            '${data['status'] ?? 'pending'},'
            '${data['bookingId'] ?? ''},'
            '${data['createdAt']?.toDate()?.toString() ?? ''}');
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
      final snapshot = await _firestore
          .collection('bookings')
          .where('paymentPlan', isEqualTo: 'emi')
          .where('status', whereIn: ['confirmed', 'partial']).get();

      final csv = StringBuffer();
      csv.writeln(
          'Customer,Phone,Plot,Monthly EMI,Total Months,Paid Months,Next Due,Status');

      for (final doc in snapshot.docs) {
        final data = doc.data();
        final emiDetails = data['emiDetails'] as Map<String, dynamic>?;

        if (emiDetails != null) {
          csv.writeln('"${data['customerName'] ?? ''}",'
              '"${data['customerPhone'] ?? ''}",'
              '"${data['plotNumber'] ?? ''}",'
              '${emiDetails['monthlyEMI'] ?? 0},'
              '${emiDetails['totalInstallments'] ?? 0},'
              '${emiDetails['installmentsPaid'] ?? 0},'
              '${emiDetails['nextDueDate'] ?? ''},'
              '${_calculateEMIStatus(emiDetails)}');
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
      Query query =
          _firestore.collection('leads').orderBy('createdAt', descending: true);

      if (assignedTo != null) {
        query = query.where('assignedTo', isEqualTo: assignedTo);
      }
      if (status != null) {
        query = query.where('status', isEqualTo: status);
      }
      if (startDate != null) {
        query = query.where('createdAt', isGreaterThanOrEqualTo: startDate);
      }

      final snapshot = await query.get();

      final csv = StringBuffer();
      csv.writeln(
          'Lead ID,Name,Phone,Email,Source,Status,Assigned To,Created Date');

      for (final doc in snapshot.docs) {
        final data = doc.data() as Map<String, dynamic>;
        csv.writeln('${doc.id},'
            '"${data['name'] ?? ''}",'
            '"${data['phone'] ?? ''}",'
            '"${data['email'] ?? ''}",'
            '${data['source'] ?? ''},'
            '${data['status'] ?? 'new'},'
            '"${data['assignedToName'] ?? ''}",'
            '${data['createdAt']?.toDate()?.toString() ?? ''}');
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

      // Export all collections
      final collections = [
        'users',
        'colonies',
        'plots',
        'bookings',
        'commissions',
        'leads',
        'payments',
      ];

      for (final collection in collections) {
        final snapshot = await _firestore.collection(collection).get();
        backup[collection] = snapshot.docs
            .map((doc) => {
                  'id': doc.id,
                  'data': doc.data(),
                })
            .toList();
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

  String _calculateEMIStatus(Map<String, dynamic> emiDetails) {
    final nextDueDate = (emiDetails['nextDueDate'] as Timestamp?)?.toDate();
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
