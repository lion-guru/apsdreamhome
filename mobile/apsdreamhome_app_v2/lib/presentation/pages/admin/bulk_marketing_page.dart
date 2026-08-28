import 'package:flutter/material.dart';

/// Bulk Marketing Page
/// No backend API exists for bulk marketing — shows empty/configured state
class BulkMarketingPage extends StatefulWidget {
  const BulkMarketingPage({super.key});

  @override
  State<BulkMarketingPage> createState() => _BulkMarketingPageState();
}

class _BulkMarketingPageState extends State<BulkMarketingPage> {
  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Channel Selection
          _buildChannelSelector(),
          const SizedBox(height: 24),

          // Not Configured State
          _buildNotConfiguredState(),
          const SizedBox(height: 24),

          // Tips
          _buildTipsSection(),
        ],
      ),
    );
  }

  Widget _buildChannelSelector() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Select Channel',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                _buildChannelCard('sms', 'SMS', Icons.sms, Colors.blue),
                const SizedBox(width: 12),
                _buildChannelCard(
                    'email', 'Email', Icons.email, Colors.orange),
                const SizedBox(width: 12),
                _buildChannelCard(
                    'whatsapp', 'WhatsApp', Icons.chat, Colors.green),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChannelCard(
      String channel, String label, IconData icon, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.grey.shade50,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade300),
        ),
        child: Column(
          children: [
            Icon(icon, color: Colors.grey, size: 32),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                  fontWeight: FontWeight.bold,
                  color: Colors.grey.shade700),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNotConfiguredState() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.campaign_outlined,
                size: 80,
                color: Colors.grey.shade300,
              ),
              const SizedBox(height: 24),
              Text(
                'Bulk Marketing Not Configured',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey.shade800,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                'The bulk marketing API is not yet available.\nContact your admin to enable SMS, Email,\nand WhatsApp campaign functionality.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey.shade600,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 24),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.blue.shade200),
                ),
                child: Column(
                  children: [
                    Icon(Icons.info_outline, color: Colors.blue.shade700),
                    const SizedBox(height: 8),
                    Text(
                      'When configured, you will be able to:',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: Colors.blue.shade700,
                      ),
                    ),
                    const SizedBox(height: 8),
                    _buildFeatureItem(Icons.sms, 'Send bulk SMS campaigns'),
                    _buildFeatureItem(
                        Icons.email, 'Send email campaigns'),
                    _buildFeatureItem(
                        Icons.chat, 'Send WhatsApp broadcasts'),
                    _buildFeatureItem(
                        Icons.analytics, 'Track delivery and engagement'),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFeatureItem(IconData icon, String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 16, color: Colors.blue.shade600),
          const SizedBox(width: 8),
          Text(
            text,
            style: TextStyle(color: Colors.blue.shade700, fontSize: 13),
          ),
        ],
      ),
    );
  }

  Widget _buildTipsSection() {
    return Card(
      color: Colors.blue.shade50,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.lightbulb, color: Colors.blue),
                SizedBox(width: 8),
                Text('Best Practices',
                    style: TextStyle(fontWeight: FontWeight.bold)),
              ],
            ),
            const SizedBox(height: 12),
            _buildTip('Keep SMS under 160 characters to avoid splitting'),
            _buildTip('Use personalization variables for better engagement'),
            _buildTip('Include clear call-to-action (CTA)'),
            _buildTip('Send during business hours (9 AM - 6 PM)'),
            _buildTip('Test with a small group before bulk sending'),
          ],
        ),
      ),
    );
  }

  Widget _buildTip(String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Text(
        '• $text',
        style: TextStyle(fontSize: 13, color: Colors.grey.shade700),
      ),
    );
  }
}
