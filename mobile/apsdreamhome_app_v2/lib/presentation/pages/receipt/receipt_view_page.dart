import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:printing/printing.dart';

import '../../../data/services/receipt_service.dart';

/// Receipt View Page - Preview, Print, Share receipts
class ReceiptViewPage extends ConsumerStatefulWidget {
  final Map<String, dynamic>? receiptData;
  final String? receiptType;

  const ReceiptViewPage({
    super.key,
    this.receiptData,
    this.receiptType,
  });

  @override
  ConsumerState<ReceiptViewPage> createState() => _ReceiptViewPageState();
}

class _ReceiptViewPageState extends ConsumerState<ReceiptViewPage> {
  final ReceiptService _receiptService = ReceiptService();
  File? _pdfFile;
  bool _isGenerating = false;
  bool _isPrinting = false;

  @override
  void initState() {
    super.initState();
    _generateReceipt();
  }

  Future<void> _generateReceipt() async {
    setState(() => _isGenerating = true);

    try {
      final type = widget.receiptType ?? 'emi';
      final data = widget.receiptData ?? _getMockData(type);

      switch (type) {
        case 'emi':
          _pdfFile = await _receiptService.generateEMIReceipt(
            receiptNumber: (data['receiptNumber'] as String?) ?? 'EMI-${DateTime.now().millisecondsSinceEpoch}',
            customerName: (data['customerName'] as String?) ?? 'Customer Name',
            customerAddress: (data['customerAddress'] as String?) ?? 'Address',
            customerPhone: (data['customerPhone'] as String?) ?? 'Phone',
            bookingId: (data['bookingId'] as String?) ?? 'B001',
            plotNumber: (data['plotNumber'] as String?) ?? 'P-001',
            colonyName: (data['colonyName'] as String?) ?? 'Colony Name',
            emiNumber: (data['emiNumber'] as int?) ?? 1,
            totalEMIs: (data['totalEMIs'] as int?) ?? 36,
            emiAmount: (data['emiAmount'] as num?)?.toDouble() ?? 5000.0,
            lateFee: (data['lateFee'] as num?)?.toDouble() ?? 0.0,
            totalPaid: (data['totalPaid'] as num?)?.toDouble() ?? 5000.0,
            paymentDate: (data['paymentDate'] as DateTime?) ?? DateTime.now(),
            paymentMode: (data['paymentMode'] as String?) ?? 'Cash',
            transactionId: data['transactionId'] as String?,
            chequeNumber: data['chequeNumber'] as String?,
            collectedBy: (data['collectedBy'] as String?) ?? 'Agent Name',
            agentName: data['agentName'] as String?,
          );
          break;
        case 'booking':
          _pdfFile = await _receiptService.generateBookingReceipt(
            bookingId: (data['bookingId'] as String?) ?? 'B001',
            customerName: (data['customerName'] as String?) ?? 'Customer Name',
            customerAddress: (data['customerAddress'] as String?) ?? 'Address',
            customerPhone: (data['customerPhone'] as String?) ?? 'Phone',
            customerEmail: (data['customerEmail'] as String?) ?? 'email@example.com',
            plotNumber: (data['plotNumber'] as String?) ?? 'P-001',
            colonyName: (data['colonyName'] as String?) ?? 'Colony Name',
            plotSize: (data['plotSize'] as String?) ?? '100 sq yd',
            plotPrice: (data['plotPrice'] as num?)?.toDouble() ?? 2500000.0,
            bookingAmount: (data['bookingAmount'] as num?)?.toDouble() ?? 250000.0,
            totalPrice: (data['totalPrice'] as num?)?.toDouble() ?? 2500000.0,
            emiAmount: (data['emiAmount'] as num?)?.toDouble() ?? 7500.0,
            totalEMIs: (data['totalEMIs'] as int?) ?? 36,
            bookingDate: (data['bookingDate'] as DateTime?) ?? DateTime.now(),
            paymentMode: (data['paymentMode'] as String?) ?? 'Online',
            transactionId: (data['transactionId'] as String?) ?? 'TXN123456',
            salesPerson: (data['salesPerson'] as String?) ?? 'Sales Executive',
            salesPersonPhone: (data['salesPersonPhone'] as String?) ?? '9876543210',
          );
          break;
      }

      if (_pdfFile != null) {
        AppLogger.info('Receipt generated: ${_pdfFile!.path}');
      }
    } catch (e) {
      AppLogger.error('Error generating receipt', e);
    } finally {
      setState(() => _isGenerating = false);
    }
  }

  Map<String, dynamic> _getMockData(String type) {
    if (type == 'emi') {
      return {
        'receiptNumber': 'EMI-2024-001',
        'customerName': 'Ramesh Kumar',
        'customerAddress': '123, Gandhi Nagar, Gorakhpur',
        'customerPhone': '+91 98765 43210',
        'bookingId': 'B001',
        'plotNumber': 'P-45',
        'colonyName': 'Suryoday Heights',
        'emiNumber': 5,
        'totalEMIs': 36,
        'emiAmount': 5000,
        'lateFee': 100,
        'totalPaid': 5100,
        'paymentDate': DateTime.now(),
        'paymentMode': 'UPI',
        'transactionId': 'UPI123456789',
        'collectedBy': 'Field Agent - Rajesh',
      };
    } else {
      return {
        'bookingId': 'B001',
        'customerName': 'Amit Sharma',
        'customerAddress': '456, Rajendra Nagar, Gorakhpur',
        'customerPhone': '+91 98765 43211',
        'customerEmail': 'amit@example.com',
        'plotNumber': 'P-67',
        'colonyName': 'Raghunath City Center',
        'plotSize': '150 sq yd',
        'plotPrice': 3750000,
        'bookingAmount': 375000,
        'totalPrice': 3750000,
        'emiAmount': 10000,
        'totalEMIs': 36,
        'bookingDate': DateTime.now(),
        'paymentMode': 'Online Transfer',
        'transactionId': 'NEFT987654321',
        'salesPerson': 'Senior Associate - Priya',
        'salesPersonPhone': '+91 98765 43222',
      };
    }
  }

  Future<void> _printReceipt() async {
    if (_pdfFile == null) return;

    setState(() => _isPrinting = true);
    
    try {
      await _receiptService.printReceipt(_pdfFile!);
    } catch (e) {
      AppLogger.error('Error printing receipt', e);
    } finally {
      setState(() => _isPrinting = false);
    }
  }

  Future<void> _shareReceipt() async {
    if (_pdfFile == null) return;

    try {
      await _receiptService.shareReceipt(
        _pdfFile!,
        text: 'Your payment receipt from APS Dream Home',
      );
    } catch (e) {
      AppLogger.error('Error sharing receipt', e);
    }
  }

  Future<void> _printViaBluetooth() async {
    if (_pdfFile == null) return;

    // Show Bluetooth printer selection dialog
    showModalBottomSheet(
      context: context,
      builder: (context) => const BluetoothPrinterDialog(),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Receipt'),
        actions: [
          IconButton(
            icon: const Icon(Icons.share),
            onPressed: _shareReceipt,
          ),
        ],
      ),
      body: _isGenerating
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('Generating receipt...'),
                ],
              ),
            )
          : _pdfFile == null
              ? const Center(
                  child: Text('Failed to generate receipt'),
                )
              : PdfPreview(
                  build: (format) => _pdfFile!.readAsBytes(),
                  allowPrinting: true,
                  allowSharing: true,
                  canChangePageFormat: false,
                  canChangeOrientation: false,
                  canDebug: false,
                ),
      bottomNavigationBar: _pdfFile == null
          ? null
          : SafeArea(
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.1),
                      blurRadius: 10,
                      offset: const Offset(0, -5),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: _isPrinting ? null : _printReceipt,
                            icon: _isPrinting
                                ? const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                    ),
                                  )
                                : const Icon(Icons.print),
                            label: Text(_isPrinting ? 'Printing...' : 'Print'),
                            style: ElevatedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _shareReceipt,
                            icon: const Icon(Icons.share),
                            label: const Text('Share PDF'),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _printViaBluetooth,
                            icon: const Icon(Icons.bluetooth),
                            label: const Text('Bluetooth'),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () {
                              // Upload to Google Drive
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text('Uploading to Google Drive...'),
                                ),
                              );
                            },
                            icon: const Icon(Icons.cloud_upload),
                            label: const Text('Save to Drive'),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () {
                              // Send via WhatsApp
                              _receiptService.shareReceipt(
                                _pdfFile!,
                                text: 'Your APS Dream Home receipt',
                              );
                            },
                            icon: const Icon(Icons.message),
                            label: const Text('WhatsApp'),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              foregroundColor: Colors.green,
                            ),
                          ),
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

/// Bluetooth Printer Selection Dialog
class BluetoothPrinterDialog extends StatelessWidget {
  const BluetoothPrinterDialog({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text(
            'Select Bluetooth Printer',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          ListTile(
            leading: const Icon(Icons.print, color: Colors.blue),
            title: const Text('EPSON TM-T82'),
            subtitle: const Text('Connected'),
            trailing: const Icon(Icons.check_circle, color: Colors.green),
            onTap: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Printing to EPSON TM-T82...')),
              );
            },
          ),
          ListTile(
            leading: const Icon(Icons.print, color: Colors.grey),
            title: const Text('Xprinter XP-58'),
            subtitle: const Text('Available'),
            onTap: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Connecting to Xprinter...')),
              );
            },
          ),
          const SizedBox(height: 16),
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
        ],
      ),
    );
  }
}

// Simple logger class for the import
class AppLogger {
  static void info(String message) {
    debugPrint('INFO: $message');
  }

  static void error(String message, [dynamic error]) {
    debugPrint('ERROR: $message ${error ?? ''}');
  }

  static void warning(String message) {
    debugPrint('WARNING: $message');
  }
}
