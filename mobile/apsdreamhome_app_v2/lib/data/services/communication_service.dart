import 'dart:convert';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:http/http.dart' as http;
// import 'package:url_launcher/url_launcher.dart';

import '../../core/utils/logger.dart';

/// Communication Service - WhatsApp, Email, SMS Integration
/// For APS Dream Home - Multi-channel communication
class CommunicationService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  // WhatsApp Business API Configuration
  static const String _whatsappApiUrl = 'https://graph.facebook.com/v18.0';
  static const String _whatsappPhoneNumberId = 'YOUR_PHONE_NUMBER_ID';
  static const String _whatsappAccessToken = 'YOUR_ACCESS_TOKEN';

  // Email Configuration (SendGrid/AWS SES)
  static const String _sendGridApiKey = 'YOUR_SENDGRID_API_KEY';
  static const String _fromEmail = 'noreply@apsdreamhome.com';
  static const String _fromName = 'APS Dream Home';

  // SMS Gateway (Twilio/Msg91)
  static const String _smsApiKey = 'YOUR_SMS_API_KEY';
  static const String _smsSenderId = 'APSDREAM';

  // ==================== WHATSAPP ====================

  /// Send WhatsApp Message via Business API
  Future<bool> sendWhatsAppMessage({
    required String phone,
    required String message,
    String? templateName,
    Map<String, dynamic>? templateParams,
  }) async {
    try {
      // Format phone number
      final formattedPhone = _formatPhoneForWhatsApp(phone);

      // If template is provided, use template message
      if (templateName != null) {
        return await _sendTemplateMessage(
          phone: formattedPhone,
          templateName: templateName,
          params: templateParams ?? {},
        );
      }

      // Otherwise send text message
      final response = await http.post(
        Uri.parse('$_whatsappApiUrl/$_whatsappPhoneNumberId/messages'),
        headers: {
          'Authorization': 'Bearer $_whatsappAccessToken',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'messaging_product': 'whatsapp',
          'recipient_type': 'individual',
          'to': formattedPhone,
          'type': 'text',
          'text': {'body': message},
        }),
      );

      if (response.statusCode == 200) {
        await _logCommunication(
          type: 'whatsapp',
          recipient: phone,
          content: message,
          status: 'sent',
        );
        return true;
      } else {
        AppLogger.error('WhatsApp API error: ${response.body}');
        return false;
      }
    } catch (e) {
      AppLogger.error('Error sending WhatsApp message', e);
      return false;
    }
  }

  /// Send WhatsApp Template Message
  Future<bool> _sendTemplateMessage({
    required String phone,
    required String templateName,
    required Map<String, dynamic> params,
  }) async {
    try {
      final components = <Map<String, dynamic>>[];

      // Build template parameters
      if (params.isNotEmpty) {
        final parameters = params.entries.map((e) {
          return {
            'type': 'text',
            'text': e.value.toString(),
          };
        }).toList();

        components.add({
          'type': 'body',
          'parameters': parameters,
        });
      }

      final response = await http.post(
        Uri.parse('$_whatsappApiUrl/$_whatsappPhoneNumberId/messages'),
        headers: {
          'Authorization': 'Bearer $_whatsappAccessToken',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'messaging_product': 'whatsapp',
          'recipient_type': 'individual',
          'to': phone,
          'type': 'template',
          'template': {
            'name': templateName,
            'language': {'code': 'hi'},
            'components': components,
          },
        }),
      );

      return response.statusCode == 200;
    } catch (e) {
      AppLogger.error('Error sending template message', e);
      return false;
    }
  }

  /// Open WhatsApp Chat (Direct from app)
  Future<bool> openWhatsAppChat(String phone, {String? message}) async {
    try {
      final formattedPhone = _formatPhoneForWhatsApp(phone);
      final url = message != null
          ? 'https://wa.me/$formattedPhone?text=${Uri.encodeComponent(message)}'
          : 'https://wa.me/$formattedPhone';

      // if (await canLaunchUrl(Uri.parse(url))) {
      //   await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
      //   return true;
      // }
      return false;
    } catch (e) {
      AppLogger.error('Error opening WhatsApp', e);
      return false;
    }
  }

  /// Send WhatsApp to Multiple Recipients
  Future<Map<String, bool>> sendBulkWhatsApp({
    required List<String> phones,
    required String message,
    String? templateName,
  }) async {
    final results = <String, bool>{};

    for (final phone in phones) {
      final success = await sendWhatsAppMessage(
        phone: phone,
        message: message,
        templateName: templateName,
      );
      results[phone] = success;

      // Delay to avoid rate limiting
      await Future.delayed(const Duration(milliseconds: 500));
    }

    return results;
  }

  // ==================== EMAIL ====================

  /// Send Email via SendGrid
  Future<bool> sendEmail({
    required String to,
    required String subject,
    required String body,
    String? htmlBody,
    List<String>? cc,
    List<String>? bcc,
    List<Map<String, dynamic>>? attachments,
  }) async {
    try {
      final emailData = {
        'personalizations': [
          {
            'to': [
              {'email': to}
            ],
            if (cc != null) 'cc': cc.map((e) => {'email': e}).toList(),
            if (bcc != null) 'bcc': bcc.map((e) => {'email': e}).toList(),
          }
        ],
        'from': {'email': _fromEmail, 'name': _fromName},
        'subject': subject,
        'content': [
          {
            'type': 'text/plain',
            'value': body,
          },
          if (htmlBody != null)
            {
              'type': 'text/html',
              'value': htmlBody,
            },
        ],
        if (attachments != null) 'attachments': attachments,
      };

      final response = await http.post(
        Uri.parse('https://api.sendgrid.com/v3/mail/send'),
        headers: {
          'Authorization': 'Bearer $_sendGridApiKey',
          'Content-Type': 'application/json',
        },
        body: jsonEncode(emailData),
      );

      if (response.statusCode == 202) {
        await _logCommunication(
          type: 'email',
          recipient: to,
          content: subject,
          status: 'sent',
        );
        return true;
      } else {
        AppLogger.error('SendGrid error: ${response.body}');
        return false;
      }
    } catch (e) {
      AppLogger.error('Error sending email', e);
      return false;
    }
  }

  /// Send Booking Confirmation Email
  Future<bool> sendBookingConfirmation({
    required String to,
    required String customerName,
    required String bookingId,
    required String plotNumber,
    required String colonyName,
    required double amount,
    required DateTime bookingDate,
  }) async {
    final subject = 'Booking Confirmation - $plotNumber | APS Dream Home';
    final body = '''
Dear $customerName,

Your booking has been confirmed!

Booking Details:
- Booking ID: $bookingId
- Plot Number: $plotNumber
- Colony: $colonyName
- Amount Paid: ₹${amount.toStringAsFixed(2)}
- Booking Date: ${_formatDate(bookingDate)}

Thank you for choosing APS Dream Home!

Best regards,
APS Dream Home Team
    ''';

    final htmlBody = _buildBookingEmailTemplate(
      customerName: customerName,
      bookingId: bookingId,
      plotNumber: plotNumber,
      colonyName: colonyName,
      amount: amount,
      bookingDate: bookingDate,
    );

    return sendEmail(
      to: to,
      subject: subject,
      body: body,
      htmlBody: htmlBody,
    );
  }

  /// Send Payment Reminder Email
  Future<bool> sendPaymentReminder({
    required String to,
    required String customerName,
    required double amount,
    required DateTime dueDate,
    required String bookingId,
  }) async {
    final subject = 'Payment Reminder - EMI Due on ${_formatDate(dueDate)}';
    final body = '''
Dear $customerName,

This is a friendly reminder that your EMI payment is due soon.

Payment Details:
- Amount Due: ₹${amount.toStringAsFixed(2)}
- Due Date: ${_formatDate(dueDate)}
- Booking ID: $bookingId

Please make the payment to avoid late fees.

Payment Options:
- Online: https://apsdreamhome.com/pay
- UPI: apsdream@upi
- Bank Transfer: Details in attachment

Thank you!
APS Dream Home
    ''';

    return sendEmail(
      to: to,
      subject: subject,
      body: body,
    );
  }

  // ==================== SMS ====================

  /// Send SMS via SMS Gateway (Msg91/Twilio)
  Future<bool> sendSMS({
    required String phone,
    required String message,
    String? templateId, // For DLT registered templates in India
  }) async {
    try {
      // Using Msg91 for Indian numbers
      final response = await http.post(
        Uri.parse('https://api.msg91.com/api/v5/flow/'),
        headers: {
          'authkey': _smsApiKey,
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'template_id': templateId ?? 'YOUR_TEMPLATE_ID',
          'sender': _smsSenderId,
          'short_url': '1', // Enable short URL
          'recipients': [
            {
              'mobiles': _formatPhoneForSMS(phone),
              'VAR1': message,
            }
          ],
        }),
      );

      if (response.statusCode == 200) {
        await _logCommunication(
          type: 'sms',
          recipient: phone,
          content: message,
          status: 'sent',
        );
        return true;
      } else {
        AppLogger.error('SMS API error: ${response.body}');
        return false;
      }
    } catch (e) {
      AppLogger.error('Error sending SMS', e);
      return false;
    }
  }

  /// Send OTP SMS
  Future<bool> sendOTP({
    required String phone,
    required String otp,
    int expiryMinutes = 10,
  }) async {
    final message =
        'Your APS Dream Home verification code is $otp. Valid for $expiryMinutes minutes. Do not share this code with anyone.';

    return sendSMS(
      phone: phone,
      message: message,
      templateId: 'OTP_TEMPLATE_ID',
    );
  }

  /// Send Payment Receipt SMS
  Future<bool> sendPaymentReceiptSMS({
    required String phone,
    required String customerName,
    required double amount,
    required String receiptNumber,
    required DateTime date,
  }) async {
    final message =
        'Hi $customerName, payment of ₹${amount.toStringAsFixed(0)} received. Receipt: $receiptNumber. Date: ${_formatDate(date)}. Thanks! APS Dream Home';

    return sendSMS(
      phone: phone,
      message: message,
      templateId: 'PAYMENT_RECEIPT_TEMPLATE_ID',
    );
  }

  // ==================== PUSH NOTIFICATIONS ====================

  /// Send Push Notification via FCM
  Future<bool> sendPushNotification({
    required String userId,
    required String title,
    required String body,
    Map<String, dynamic>? data,
  }) async {
    try {
      // Get user's FCM token
      final userDoc = await _firestore.collection('users').doc(userId).get();
      final fcmToken = userDoc.data()?['fcmToken'];

      if (fcmToken == null) {
        AppLogger.warning('No FCM token for user $userId');
        return false;
      }

      // Send via Firebase Functions (server-side)
      await _firestore.collection('notifications').add({
        'userId': userId,
        'title': title,
        'body': body,
        'data': data,
        'type': 'push',
        'status': 'pending',
        'createdAt': FieldValue.serverTimestamp(),
      });

      return true;
    } catch (e) {
      AppLogger.error('Error sending push notification', e);
      return false;
    }
  }

  // ==================== UTILITY METHODS ====================

  String _formatPhoneForWhatsApp(String phone) {
    // Remove all non-numeric characters
    var cleaned = phone.replaceAll(RegExp(r'[^0-9]'), '');

    // Remove leading 0
    if (cleaned.startsWith('0')) {
      cleaned = cleaned.substring(1);
    }

    // Add country code if not present
    if (!cleaned.startsWith('91')) {
      cleaned = '91$cleaned';
    }

    return cleaned;
  }

  String _formatPhoneForSMS(String phone) {
    // Similar formatting for SMS
    var cleaned = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (cleaned.startsWith('0')) {
      cleaned = cleaned.substring(1);
    }
    if (!cleaned.startsWith('91')) {
      cleaned = '91$cleaned';
    }
    return cleaned;
  }

  String _formatDate(DateTime date) {
    return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
  }

  Future<void> _logCommunication({
    required String type,
    required String recipient,
    required String content,
    required String status,
  }) async {
    await _firestore.collection('communication_logs').add({
      'type': type,
      'recipient': recipient,
      'content': content,
      'status': status,
      'sentAt': FieldValue.serverTimestamp(),
    });
  }

  String _buildBookingEmailTemplate({
    required String customerName,
    required String bookingId,
    required String plotNumber,
    required String colonyName,
    required double amount,
    required DateTime bookingDate,
  }) {
    return '''
<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; }
    .container { max-width: 600px; margin: 0 auto; }
    .header { background: #1e3a5f; color: white; padding: 20px; text-align: center; }
    .content { padding: 20px; }
    .details { background: #f5f5f5; padding: 15px; margin: 20px 0; }
    .footer { text-align: center; padding: 20px; color: #666; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>APS Dream Home</h1>
      <p>Booking Confirmation</p>
    </div>
    <div class="content">
      <h2>Dear $customerName,</h2>
      <p>Congratulations! Your booking has been confirmed.</p>
      
      <div class="details">
        <h3>Booking Details:</h3>
        <p><strong>Booking ID:</strong> $bookingId</p>
        <p><strong>Plot Number:</strong> $plotNumber</p>
        <p><strong>Colony:</strong> $colonyName</p>
        <p><strong>Amount Paid:</strong> ₹${amount.toStringAsFixed(2)}</p>
        <p><strong>Booking Date:</strong> ${_formatDate(bookingDate)}</p>
      </div>
      
      <p>Thank you for choosing APS Dream Home!</p>
    </div>
    <div class="footer">
      <p>© 2026 APS Dream Home. All rights reserved.</p>
    </div>
  </div>
</body>
</html>
    ''';
  }
}

// Communication Templates
class CommunicationTemplates {
  // WhatsApp Templates
  static const String welcomeTemplate = 'welcome_message';
  static const String bookingConfirmationTemplate = 'booking_confirmation';
  static const String paymentReminderTemplate = 'payment_reminder';
  static const String siteVisitTemplate = 'site_visit_reminder';
  static const String offerTemplate = 'special_offer';

  // SMS Templates (DLT Registered)
  static const String otpTemplate =
      '1707161793823985278'; // Example template ID
  static const String paymentReceiptTemplate = '1707161793823985279';
  static const String emiReminderTemplate = '1707161793823985280';
  static const String bookingConfirmTemplate = '1707161793823985281';
}
