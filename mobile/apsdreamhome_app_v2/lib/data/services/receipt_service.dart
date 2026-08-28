import 'dart:io';

import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:path_provider/path_provider.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:share_plus/share_plus.dart';

import '../../core/utils/logger.dart';

/// Receipt Generation Service
/// Creates professional PDF receipts for EMI payments, bookings, etc.
class ReceiptService {
  // Company Details
  static const String _companyName = 'APS Dream Home';
  static const String _companyAddress =
      '123, ABC Tower, Gorakhpur, UP - 273001';
  static const String _companyPhone = '+91 92771 21112';
  static const String _companyEmail = 'info@apsdreamhome.com';
  static const String _companyGST = '09AABCU9603R1ZX';

  // ==================== EMI RECEIPT ====================

  /// Generate EMI Payment Receipt
  Future<File?> generateEMIReceipt({
    required String receiptNumber,
    required String customerName,
    required String customerAddress,
    required String customerPhone,
    required String bookingId,
    required String plotNumber,
    required String colonyName,
    required int emiNumber,
    required int totalEMIs,
    required double emiAmount,
    required double lateFee,
    required double totalPaid,
    required DateTime paymentDate,
    required String paymentMode,
    required String? transactionId,
    required String? chequeNumber,
    required String collectedBy,
    String? agentName,
  }) async {
    try {
      final pdf = pw.Document();

      // Load logo (if available)
      pw.ImageProvider? logo;
      try {
        final logoData = await rootBundle.load('assets/images/logo.png');
        logo = pw.MemoryImage(logoData.buffer.asUint8List());
      } catch (e) {
        AppLogger.warning('Logo not found, using text header');
      }

      pdf.addPage(
        pw.Page(
          pageFormat: PdfPageFormat.a4,
          build: (pw.Context context) {
            return pw.Column(
              crossAxisAlignment: pw.CrossAxisAlignment.start,
              children: [
                // Header
                _buildHeader(logo),
                pw.SizedBox(height: 20),

                // Receipt Title
                pw.Center(
                  child: pw.Container(
                    padding: const pw.EdgeInsets.symmetric(
                      horizontal: 30,
                      vertical: 10,
                    ),
                    decoration: pw.BoxDecoration(
                      border: pw.Border.all(width: 2),
                      borderRadius: pw.BorderRadius.circular(5),
                    ),
                    child: pw.Text(
                      'EMI PAYMENT RECEIPT',
                      style: const pw.TextStyle(
                        fontSize: 20,
                        fontWeight: pw.FontWeight.bold,
                      ),
                    ),
                  ),
                ),
                pw.SizedBox(height: 20),

                // Receipt Details
                pw.Row(
                  mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                  children: [
                    _buildDetailColumn('Receipt No:', receiptNumber),
                    _buildDetailColumn(
                        'Date:', DateFormat('dd/MM/yyyy').format(paymentDate)),
                  ],
                ),
                pw.SizedBox(height: 20),

                // Customer Details
                _buildSectionTitle('Customer Details'),
                pw.SizedBox(height: 10),
                _buildDetailRow('Name:', customerName),
                _buildDetailRow('Address:', customerAddress),
                _buildDetailRow('Phone:', customerPhone),
                pw.SizedBox(height: 20),

                // Property Details
                _buildSectionTitle('Property Details'),
                pw.SizedBox(height: 10),
                _buildDetailRow('Booking ID:', bookingId),
                _buildDetailRow('Plot Number:', plotNumber),
                _buildDetailRow('Colony/Project:', colonyName),
                pw.SizedBox(height: 20),

                // Payment Details
                _buildSectionTitle('Payment Details'),
                pw.SizedBox(height: 10),
                _buildDetailRow('EMI Number:', '$emiNumber of $totalEMIs'),
                pw.SizedBox(height: 10),

                // Amount Table
                _buildAmountTable(
                  emiAmount: emiAmount,
                  lateFee: lateFee,
                  totalPaid: totalPaid,
                ),
                pw.SizedBox(height: 20),

                // Payment Mode
                _buildDetailRow('Payment Mode:', paymentMode),
                if (transactionId != null)
                  _buildDetailRow('Transaction ID:', transactionId),
                if (chequeNumber != null)
                  _buildDetailRow('Cheque Number:', chequeNumber),
                pw.SizedBox(height: 20),

                // Collection Details
                _buildDetailRow('Collected By:', collectedBy),
                if (agentName != null) _buildDetailRow('Agent:', agentName),
                pw.SizedBox(height: 30),

                // Signatures
                pw.Row(
                  mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                  children: [
                    _buildSignatureBlock('Customer Signature'),
                    _buildSignatureBlock('Authorized Signatory'),
                  ],
                ),
                pw.SizedBox(height: 30),

                // Footer
                pw.Divider(),
                pw.Center(
                  child: pw.Text(
                    'This is a computer-generated receipt and does not require a physical signature.',
                    style: const pw.TextStyle(
                      fontSize: 10,
                      fontStyle: pw.FontStyle.italic,
                      color: PdfColors.grey700,
                    ),
                  ),
                ),
                pw.SizedBox(height: 10),
                pw.Center(
                  child: pw.Text(
                    'Thank you for your payment!',
                    style: const pw.TextStyle(
                      fontSize: 12,
                      fontWeight: pw.FontWeight.bold,
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      );

      // Save PDF
      final output = await getTemporaryDirectory();
      final file = File('${output.path}/EMI_Receipt_$receiptNumber.pdf');
      await file.writeAsBytes(await pdf.save());

      AppLogger.info('EMI Receipt generated: ${file.path}');
      return file;
    } catch (e) {
      AppLogger.error('Error generating EMI receipt', e);
      return null;
    }
  }

  // ==================== BOOKING RECEIPT ====================

  /// Generate Booking Confirmation Receipt
  Future<File?> generateBookingReceipt({
    required String bookingId,
    required String customerName,
    required String customerAddress,
    required String customerPhone,
    required String customerEmail,
    required String plotNumber,
    required String colonyName,
    required String plotSize,
    required double plotPrice,
    required double bookingAmount,
    required double totalPrice,
    required double emiAmount,
    required int totalEMIs,
    required DateTime bookingDate,
    required String paymentMode,
    required String? transactionId,
    required String salesPerson,
    required String? salesPersonPhone,
  }) async {
    try {
      final pdf = pw.Document();

      pdf.addPage(
        pw.Page(
          pageFormat: PdfPageFormat.a4,
          build: (pw.Context context) {
            return pw.Column(
              crossAxisAlignment: pw.CrossAxisAlignment.start,
              children: [
                // Header
                _buildHeader(null),
                pw.SizedBox(height: 20),

                // Title
                pw.Center(
                  child: pw.Container(
                    padding: const pw.EdgeInsets.symmetric(
                      horizontal: 30,
                      vertical: 10,
                    ),
                    decoration: pw.BoxDecoration(
                      color: PdfColors.blue100,
                      borderRadius: pw.BorderRadius.circular(5),
                    ),
                    child: pw.Text(
                      'BOOKING CONFIRMATION',
                      style: const pw.TextStyle(
                        fontSize: 22,
                        fontWeight: pw.FontWeight.bold,
                        color: PdfColors.blue900,
                      ),
                    ),
                  ),
                ),
                pw.SizedBox(height: 20),

                // Booking ID (Prominent)
                pw.Center(
                  child: pw.Container(
                    padding: const pw.EdgeInsets.all(15),
                    decoration: pw.BoxDecoration(
                      border: pw.Border.all(width: 2, color: PdfColors.blue),
                      borderRadius: pw.BorderRadius.circular(8),
                    ),
                    child: pw.Column(
                      children: [
                        pw.Text(
                          'BOOKING ID',
                          style: const pw.TextStyle(
                            fontSize: 14,
                            color: PdfColors.grey700,
                          ),
                        ),
                        pw.Text(
                          bookingId,
                          style: const pw.TextStyle(
                            fontSize: 28,
                            fontWeight: pw.FontWeight.bold,
                            color: PdfColors.blue,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                pw.SizedBox(height: 20),

                // Customer & Date
                pw.Row(
                  mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                  children: [
                    _buildDetailColumn('Booking Date:',
                        DateFormat('dd/MM/yyyy').format(bookingDate)),
                    _buildDetailColumn('Status:', 'CONFIRMED'),
                  ],
                ),
                pw.SizedBox(height: 20),

                // Customer Details
                _buildSectionTitle('Customer Information'),
                pw.SizedBox(height: 10),
                _buildDetailRow('Name:', customerName),
                _buildDetailRow('Address:', customerAddress),
                _buildDetailRow('Phone:', customerPhone),
                _buildDetailRow('Email:', customerEmail),
                pw.SizedBox(height: 20),

                // Property Details
                _buildSectionTitle('Property Details'),
                pw.SizedBox(height: 10),
                pw.Container(
                  padding: const pw.EdgeInsets.all(15),
                  decoration: pw.BoxDecoration(
                    border: pw.Border.all(width: 1),
                    borderRadius: pw.BorderRadius.circular(5),
                  ),
                  child: pw.Column(
                    children: [
                      _buildDetailRow('Plot Number:', plotNumber),
                      _buildDetailRow('Colony/Project:', colonyName),
                      _buildDetailRow('Plot Size:', plotSize),
                      _buildDetailRow(
                          'Plot Price:', '₹${_formatPrice(plotPrice)}'),
                    ],
                  ),
                ),
                pw.SizedBox(height: 20),

                // Payment Summary
                _buildSectionTitle('Payment Summary'),
                pw.SizedBox(height: 10),
                _buildBookingAmountTable(
                  plotPrice: plotPrice,
                  bookingAmount: bookingAmount,
                  totalPrice: totalPrice,
                  emiAmount: emiAmount,
                  totalEMIs: totalEMIs,
                ),
                pw.SizedBox(height: 20),

                // Payment Details
                _buildDetailRow('Payment Mode:', paymentMode),
                if (transactionId != null)
                  _buildDetailRow('Transaction ID:', transactionId),
                pw.SizedBox(height: 20),

                // Sales Person
                _buildDetailRow('Sales Executive:', salesPerson),
                if (salesPersonPhone != null)
                  _buildDetailRow('Contact:', salesPersonPhone),
                pw.SizedBox(height: 30),

                // Terms & Conditions
                _buildSectionTitle('Terms & Conditions'),
                pw.SizedBox(height: 10),
                pw.Text(
                  '1. Booking amount is non-refundable.\n'
                  '2. EMI payment should be made on or before the due date.\n'
                  '3. Late payment will attract penalty as per company policy.\n'
                  '4. All disputes are subject to Gorakhpur jurisdiction.\n'
                  '5. Possession will be given after full payment.',
                  style: const pw.TextStyle(fontSize: 10),
                ),
                pw.SizedBox(height: 30),

                // Signatures
                pw.Row(
                  mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                  children: [
                    _buildSignatureBlock('Customer Signature'),
                    _buildSignatureBlock('For APS Dream Home'),
                  ],
                ),
                pw.SizedBox(height: 20),

                // Footer
                pw.Divider(),
                pw.Center(
                  child: pw.Text(
                    'Congratulations on your booking! Thank you for choosing APS Dream Home.',
                    style: const pw.TextStyle(
                      fontSize: 12,
                      fontWeight: pw.FontWeight.bold,
                      color: PdfColors.blue900,
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      );

      // Save PDF
      final output = await getTemporaryDirectory();
      final file = File('${output.path}/Booking_Receipt_$bookingId.pdf');
      await file.writeAsBytes(await pdf.save());

      AppLogger.info('Booking Receipt generated: ${file.path}');
      return file;
    } catch (e) {
      AppLogger.error('Error generating booking receipt', e);
      return null;
    }
  }

  // ==================== COMMISSION STATEMENT ====================

  /// Generate Monthly Commission Statement for Associates/Agents
  Future<File?> generateCommissionStatement({
    required String personName,
    required String personId,
    required String personType, // Associate, Agent, Telecaller
    required int month,
    required int year,
    required double baseSalary,
    required List<Map<String, dynamic>> commissionDetails,
    required double totalCommission,
    required double totalEarnings,
    required double tdsDeduction,
    required double netPayable,
  }) async {
    try {
      final pdf = pw.Document();
      final monthName = DateFormat('MMMM yyyy').format(DateTime(year, month));

      pdf.addPage(
        pw.Page(
          pageFormat: PdfPageFormat.a4,
          build: (pw.Context context) {
            return pw.Column(
              crossAxisAlignment: pw.CrossAxisAlignment.start,
              children: [
                _buildHeader(null),
                pw.SizedBox(height: 20),

                pw.Center(
                  child: pw.Text(
                    'COMMISSION STATEMENT',
                    style: const pw.TextStyle(
                      fontSize: 20,
                      fontWeight: pw.FontWeight.bold,
                    ),
                  ),
                ),
                pw.SizedBox(height: 10),
                pw.Center(
                  child: pw.Text(
                    'For the month of $monthName',
                    style: const pw.TextStyle(fontSize: 14),
                  ),
                ),
                pw.SizedBox(height: 20),

                // Person Details
                _buildSectionTitle('Employee Details'),
                pw.SizedBox(height: 10),
                _buildDetailRow('Name:', personName),
                _buildDetailRow('ID:', personId),
                _buildDetailRow('Type:', personType),
                pw.SizedBox(height: 20),

                // Commission Breakdown Table
                _buildSectionTitle('Commission Breakdown'),
                pw.SizedBox(height: 10),
                _buildCommissionTable(commissionDetails),
                pw.SizedBox(height: 20),

                // Summary
                _buildSectionTitle('Payment Summary'),
                pw.SizedBox(height: 10),
                _buildAmountSummaryRow('Base Salary:', baseSalary),
                _buildAmountSummaryRow('Total Commission:', totalCommission),
                _buildAmountSummaryRow('Total Earnings:', totalEarnings),
                pw.Divider(),
                _buildAmountSummaryRow('TDS Deduction (10%):', tdsDeduction,
                    isNegative: true),
                pw.Divider(),
                _buildAmountSummaryRow('Net Payable:', netPayable,
                    isBold: true, isTotal: true),
                pw.SizedBox(height: 30),

                pw.Row(
                  mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                  children: [
                    _buildSignatureBlock('Employee Signature'),
                    _buildSignatureBlock('Authorized Signatory'),
                  ],
                ),
              ],
            );
          },
        ),
      );

      final output = await getTemporaryDirectory();
      final file = File('${output.path}/Commission_${personId}_$monthName.pdf');
      await file.writeAsBytes(await pdf.save());

      return file;
    } catch (e) {
      AppLogger.error('Error generating commission statement', e);
      return null;
    }
  }

  // ==================== PRINT & SHARE ====================

  /// Print PDF using system dialog
  Future<void> printReceipt(File pdfFile) async {
    try {
      await Printing.layoutPdf(
        onLayout: (PdfPageFormat format) async => pdfFile.readAsBytes(),
      );
      AppLogger.info('Receipt printed successfully');
    } catch (e) {
      AppLogger.error('Error printing receipt', e);
    }
  }

  /// Share PDF via WhatsApp, Email, etc.
  Future<void> shareReceipt(File pdfFile, {String? text}) async {
    try {
      await Share.shareXFiles(
        [XFile(pdfFile.path)],
        text: text ?? 'Please find your receipt attached.',
      );
      AppLogger.info('Receipt shared successfully');
    } catch (e) {
      AppLogger.error('Error sharing receipt', e);
    }
  }

  /// Print via Bluetooth Thermal Printer
  Future<void> printViaBluetooth({
    required String receiptNumber,
    required String customerName,
    required double amount,
    required DateTime date,
    required String paymentMode,
  }) async {
    try {
      // This would integrate with a Bluetooth printer package
      // Example: flutter_blue_plus or esc_pos_utils
      AppLogger.info(
          'Bluetooth printing initiated for receipt: $receiptNumber');
    } catch (e) {
      AppLogger.error('Error printing via Bluetooth', e);
    }
  }

  /// Save receipt to Google Drive
  Future<String?> saveToDrive(File pdfFile, {String? folderId}) async {
    // This would integrate with Google Drive API
    // Upload file and return shareable URL
    return null;
  }

  // ==================== PDF BUILDER HELPERS ====================

  pw.Widget _buildHeader(pw.ImageProvider? logo) {
    return pw.Container(
      padding: const pw.EdgeInsets.only(bottom: 10),
      decoration: const pw.BoxDecoration(
        border: pw.Border(bottom: pw.BorderSide(width: 2)),
      ),
      child: pw.Row(
        mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
        children: [
          if (logo != null)
            pw.Image(logo, width: 80)
          else
            pw.Column(
              crossAxisAlignment: pw.CrossAxisAlignment.start,
              children: [
                pw.Text(
                  _companyName,
                  style: const pw.TextStyle(
                    fontSize: 24,
                    fontWeight: pw.FontWeight.bold,
                  ),
                ),
              ],
            ),
          pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.end,
            children: [
              pw.Text(_companyAddress, style: const pw.TextStyle(fontSize: 10)),
              pw.Text('Phone: $_companyPhone',
                  style: const pw.TextStyle(fontSize: 10)),
              pw.Text('Email: $_companyEmail',
                  style: const pw.TextStyle(fontSize: 10)),
              pw.Text('GSTIN: $_companyGST',
                  style: const pw.TextStyle(fontSize: 10)),
            ],
          ),
        ],
      ),
    );
  }

  pw.Widget _buildSectionTitle(String title) {
    return pw.Container(
      padding: const pw.EdgeInsets.symmetric(vertical: 5),
      decoration: const pw.BoxDecoration(
        border: pw.Border(bottom: pw.BorderSide(width: 1)),
      ),
      child: pw.Text(
        title,
        style: const pw.TextStyle(
          fontSize: 14,
          fontWeight: pw.FontWeight.bold,
        ),
      ),
    );
  }

  pw.Widget _buildDetailRow(String label, String value) {
    return pw.Padding(
      padding: const pw.EdgeInsets.symmetric(vertical: 3),
      child: pw.Row(
        crossAxisAlignment: pw.CrossAxisAlignment.start,
        children: [
          pw.Expanded(
            flex: 3,
            child: pw.Text(
              label,
              style: const pw.TextStyle(fontWeight: pw.FontWeight.bold),
            ),
          ),
          pw.Expanded(
            flex: 5,
            child: pw.Text(value),
          ),
        ],
      ),
    );
  }

  pw.Widget _buildDetailColumn(String label, String value) {
    return pw.Column(
      crossAxisAlignment: pw.CrossAxisAlignment.start,
      children: [
        pw.Text(
          label,
          style: const pw.TextStyle(
            fontSize: 10,
            color: PdfColors.grey700,
          ),
        ),
        pw.Text(
          value,
          style: const pw.TextStyle(fontWeight: pw.FontWeight.bold),
        ),
      ],
    );
  }

  pw.Widget _buildAmountTable({
    required double emiAmount,
    required double lateFee,
    required double totalPaid,
  }) {
    return pw.Table(
      border: pw.TableBorder.all(width: 1),
      children: [
        pw.TableRow(
          decoration: const pw.BoxDecoration(color: PdfColors.grey200),
          children: [
            _buildTableCell('Description', isHeader: true),
            _buildTableCell('Amount (₹)', isHeader: true, alignRight: true),
          ],
        ),
        pw.TableRow(
          children: [
            _buildTableCell('EMI Amount'),
            _buildTableCell(_formatPrice(emiAmount), alignRight: true),
          ],
        ),
        if (lateFee > 0)
          pw.TableRow(
            children: [
              _buildTableCell('Late Fee'),
              _buildTableCell(_formatPrice(lateFee), alignRight: true),
            ],
          ),
        pw.TableRow(
          decoration: const pw.BoxDecoration(color: PdfColors.grey200),
          children: [
            _buildTableCell('TOTAL PAID', isHeader: true),
            _buildTableCell(
              _formatPrice(totalPaid),
              isHeader: true,
              alignRight: true,
            ),
          ],
        ),
      ],
    );
  }

  pw.Widget _buildBookingAmountTable({
    required double plotPrice,
    required double bookingAmount,
    required double totalPrice,
    required double emiAmount,
    required int totalEMIs,
  }) {
    return pw.Table(
      border: pw.TableBorder.all(width: 1),
      children: [
        pw.TableRow(
          decoration: const pw.BoxDecoration(color: PdfColors.grey200),
          children: [
            _buildTableCell('Description', isHeader: true),
            _buildTableCell('Amount (₹)', isHeader: true, alignRight: true),
          ],
        ),
        pw.TableRow(
          children: [
            _buildTableCell('Plot Price'),
            _buildTableCell(_formatPrice(plotPrice), alignRight: true),
          ],
        ),
        pw.TableRow(
          children: [
            _buildTableCell('Booking Amount (Paid Now)'),
            _buildTableCell(_formatPrice(bookingAmount),
                alignRight: true, isHighlight: true),
          ],
        ),
        pw.TableRow(
          children: [
            _buildTableCell('Balance Amount'),
            _buildTableCell(
              _formatPrice(totalPrice - bookingAmount),
              alignRight: true,
            ),
          ],
        ),
        pw.TableRow(
          decoration: const pw.BoxDecoration(color: PdfColors.blue50),
          children: [
            _buildTableCell(
                'EMI Details: $totalEMIs EMIs of ₹${_formatPrice(emiAmount)} each'),
            _buildTableCell('', alignRight: true),
          ],
        ),
        pw.TableRow(
          decoration: const pw.BoxDecoration(color: PdfColors.grey200),
          children: [
            _buildTableCell('TOTAL PRICE', isHeader: true),
            _buildTableCell(
              _formatPrice(totalPrice),
              isHeader: true,
              alignRight: true,
            ),
          ],
        ),
      ],
    );
  }

  pw.Widget _buildCommissionTable(List<Map<String, dynamic>> details) {
    return pw.Table(
      border: pw.TableBorder.all(width: 1),
      children: [
        pw.TableRow(
          decoration: const pw.BoxDecoration(color: PdfColors.grey200),
          children: [
            _buildTableCell('Date', isHeader: true),
            _buildTableCell('Description', isHeader: true),
            _buildTableCell('Amount (₹)', isHeader: true, alignRight: true),
          ],
        ),
        ...details.map((detail) => pw.TableRow(
              children: [
                _buildTableCell((detail['date'] as String?) ?? ''),
                _buildTableCell((detail['description'] as String?) ?? ''),
                _buildTableCell(
                  _formatPrice(((detail['amount'] ?? 0) as num).toDouble()),
                  alignRight: true,
                ),
              ],
            )),
      ],
    );
  }

  pw.Widget _buildAmountSummaryRow(
    String label,
    double amount, {
    bool isNegative = false,
    bool isBold = false,
    bool isTotal = false,
  }) {
    final color = isNegative
        ? PdfColors.red
        : (isTotal ? PdfColors.blue : PdfColors.black);
    return pw.Padding(
      padding: const pw.EdgeInsets.symmetric(vertical: 5),
      child: pw.Row(
        mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
        children: [
          pw.Text(
            label,
            style: pw.TextStyle(
              fontWeight:
                  isBold || isTotal ? pw.FontWeight.bold : pw.FontWeight.normal,
              fontSize: isTotal ? 14 : 12,
            ),
          ),
          pw.Text(
            '${isNegative ? '-' : ''}₹${_formatPrice(amount)}',
            style: pw.TextStyle(
              fontWeight:
                  isBold || isTotal ? pw.FontWeight.bold : pw.FontWeight.normal,
              fontSize: isTotal ? 14 : 12,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  pw.Widget _buildTableCell(
    String text, {
    bool isHeader = false,
    bool alignRight = false,
    bool isHighlight = false,
  }) {
    return pw.Container(
      padding: const pw.EdgeInsets.all(8),
      alignment:
          alignRight ? pw.Alignment.centerRight : pw.Alignment.centerLeft,
      decoration: isHighlight
          ? const pw.BoxDecoration(color: PdfColors.yellow100)
          : null,
      child: pw.Text(
        text,
        style: pw.TextStyle(
          fontWeight: isHeader ? pw.FontWeight.bold : pw.FontWeight.normal,
        ),
      ),
    );
  }

  pw.Widget _buildSignatureBlock(String label) {
    return pw.Column(
      children: [
        pw.Container(
          width: 150,
          height: 50,
          decoration: pw.BoxDecoration(
            border: pw.Border.all(width: 1),
          ),
        ),
        pw.SizedBox(height: 5),
        pw.Text(label, style: const pw.TextStyle(fontSize: 10)),
      ],
    );
  }

  String _formatPrice(double price) {
    final formatter = NumberFormat('#,##0.00', 'en_IN');
    return formatter.format(price);
  }
}

// Receipt Types
enum ReceiptType {
  emi,
  booking,
  commission,
  general,
}
