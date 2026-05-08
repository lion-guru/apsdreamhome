import 'dart:io';
import 'package:flutter/material.dart';
// import 'package:image_picker/image_picker.dart';

/// Bulk Marketing Page
/// Send SMS, Email, WhatsApp to multiple users
class BulkMarketingPage extends StatefulWidget {
  const BulkMarketingPage({super.key});

  @override
  State<BulkMarketingPage> createState() => _BulkMarketingPageState();
}

class _BulkMarketingPageState extends State<BulkMarketingPage> {
  final TextEditingController _messageController = TextEditingController();
  final TextEditingController _subjectController = TextEditingController();

  String _selectedChannel = 'sms'; // sms, email, whatsapp
  String _selectedTemplate = '';
  final List<String> _selectedUsers = [];
  File? _attachment;
  bool _isSending = false;
  double _sendProgress = 0;

  // Templates
  final List<Map<String, dynamic>> _templates = [
    {
      'name': 'Festival Offer',
      'type': 'promotional',
      'sms':
          '🎉 Diwali Dhamaka! Get 10% OFF on all plots. Limited time offer! Visit APS Dream Home today. Call: 9277121112',
      'email':
          '🎉 Diwali Special Offer!\n\nDear Customer,\n\nCelebrate this Diwali with your dream plot! Get FLAT 10% discount on all colonies.\n\nOffer valid till 15th November.\n\nVisit us today!',
      'whatsapp':
          '🎉 *Diwali Dhamaka Offer!*\n\nGet 10% OFF on all plots\n✅ Suryoday Heights\n✅ Raghunath City\n✅ Braj Radha Enclave\n\n*Limited time offer!*\n\nCall now: 9277121112',
    },
    {
      'name': 'New Colony Launch',
      'type': 'announcement',
      'sms':
          '🏠 NEW LAUNCH: Ganga Nagri Phase 2 in Varanasi! Starting ₹3,800/sqft. Book now!',
      'email':
          '🏠 New Colony Launch Alert!\n\nWe are excited to announce Ganga Nagri Phase 2 in Varanasi!\n\nFeatures:\n• River view plots\n• Premium location\n• Starting ₹3,800/sqft\n\nBook your site visit today!',
      'whatsapp':
          '🏠 *NEW LAUNCH: Ganga Nagri Phase 2*\n\n📍 Varanasi\n💰 Starting ₹3,800/sqft\n\n*Features:*\n✓ River view\n✓ Gated community\n✓ 24/7 security\n\nBook now!',
    },
    {
      'name': 'Commission Reminder',
      'type': 'internal',
      'sms':
          'Dear Associate, your commission of ₹{{amount}} is ready for payout. Complete KYC to receive.',
      'email':
          'Commission Payout Ready\n\nDear Associate,\n\nYour commission amount of ₹{{amount}} is ready for payout.\n\nPlease complete your KYC verification to receive the payment.\n\nLogin to your dashboard for details.',
      'whatsapp':
          '*Commission Payout Ready* 💰\n\nYour commission: ₹{{amount}}\n\nComplete KYC to receive payment\n\nLogin: https://apsdreamhome.com',
    },
    {
      'name': 'Site Visit Reminder',
      'type': 'reminder',
      'sms':
          'Reminder: Your site visit is scheduled for {{date}} at {{time}}. Agent: {{agent}} will pick you up.',
      'email':
          'Site Visit Reminder\n\nDear Customer,\n\nThis is a reminder for your scheduled site visit:\n\nDate: {{date}}\nTime: {{time}}\nLocation: {{colony}}\nAgent: {{agent}}\n\nSee you soon!',
      'whatsapp':
          '*Site Visit Reminder* 🚗\n\n📅 Date: {{date}}\n🕐 Time: {{time}}\n🏠 Colony: {{colony}}\n👤 Agent: {{agent}}\n\nReady for pickup!',
    },
  ];

  // User segments
  final List<Map<String, dynamic>> _segments = [
    {'name': 'All Customers', 'count': 1250, 'icon': Icons.people},
    {'name': 'All Associates', 'count': 456, 'icon': Icons.business},
    {'name': 'Active Leads', 'count': 328, 'icon': Icons.trending_up},
    {'name': 'New Registrations', 'count': 89, 'icon': Icons.person_add},
    {'name': 'Unpaid Commissions', 'count': 67, 'icon': Icons.payment},
    {'name': 'KYC Pending', 'count': 234, 'icon': Icons.warning},
  ];

  @override
  void dispose() {
    _messageController.dispose();
    _subjectController.dispose();
    super.dispose();
  }

  void _selectTemplate(Map<String, dynamic> template) {
    setState(() {
      _selectedTemplate = template['name'] as String? ?? '';
      if (_selectedChannel == 'sms') {
        _messageController.text = template['sms'] as String? ?? '';
      } else if (_selectedChannel == 'email') {
        _subjectController.text = template['name'] as String? ?? '';
        _messageController.text = template['email'] as String? ?? '';
      } else {
        _messageController.text = template['whatsapp'] as String? ?? '';
      }
    });
  }

  Future<void> _pickAttachment() async {
    _showError('Attachments are temporarily disabled in this build.');
    /*
    // final picker = ImagePicker();
    */
  }

  Future<void> _sendCampaign() async {
    if (_selectedUsers.isEmpty) {
      _showError('Please select at least one user segment');
      return;
    }
    if (_messageController.text.trim().isEmpty) {
      _showError('Please enter a message');
      return;
    }
    if (_selectedChannel == 'email' && _subjectController.text.trim().isEmpty) {
      _showError('Please enter an email subject');
      return;
    }

    setState(() {
      _isSending = true;
    });

    // Calculate total recipients
    int totalRecipients = 0;
    for (var segmentName in _selectedUsers) {
      final segment = _segments.firstWhere((s) => s['name'] == segmentName);
      totalRecipients += (segment['count'] as int);
    }

    // Simulate sending
    for (int i = 0; i <= 100; i += 5) {
      await Future.delayed(const Duration(milliseconds: 100));
      setState(() {
        _sendProgress = i / 100;
      });
    }

    setState(() {
      _isSending = false;
      _sendProgress = 0;
    });

    _showSuccess('Campaign sent to $totalRecipients recipients!');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bulk Marketing'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.history),
            onPressed: () => _showCampaignHistory(),
          ),
        ],
      ),
      body: _isSending ? _buildSendingProgress() : _buildForm(),
    );
  }

  Widget _buildSendingProgress() {
    int totalRecipients = 0;
    for (var segmentName in _selectedUsers) {
      final segment = _segments.firstWhere((s) => s['name'] == segmentName);
      totalRecipients += (segment['count'] as int);
    }

    final int sentCount = (totalRecipients * _sendProgress).round();

    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          SizedBox(
            width: 200,
            height: 200,
            child: CircularProgressIndicator(
              value: _sendProgress,
              strokeWidth: 12,
              backgroundColor: Colors.grey.shade200,
              valueColor: AlwaysStoppedAnimation<Color>(
                _selectedChannel == 'sms'
                    ? Colors.blue
                    : _selectedChannel == 'email'
                        ? Colors.orange
                        : Colors.green,
              ),
            ),
          ),
          const SizedBox(height: 32),
          Text(
            '${(_sendProgress * 100).toInt()}%',
            style: const TextStyle(
              fontSize: 48,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Sending to $sentCount of $totalRecipients recipients...',
            style: TextStyle(color: Colors.grey.shade600),
          ),
          const SizedBox(height: 8),
          Text(
            'via ${_selectedChannel.toUpperCase()}',
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: _selectedChannel == 'sms'
                  ? Colors.blue
                  : _selectedChannel == 'email'
                      ? Colors.orange
                      : Colors.green,
            ),
          ),
          const SizedBox(height: 32),
          ElevatedButton(
            onPressed: () {
              setState(() {
                _isSending = false;
              });
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            child: const Text('Cancel'),
          ),
        ],
      ),
    );
  }

  Widget _buildForm() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Channel Selection
          _buildChannelSelector(),
          const SizedBox(height: 24),

          // Templates
          _buildTemplatesSection(),
          const SizedBox(height: 24),

          // Recipients
          _buildRecipientsSection(),
          const SizedBox(height: 24),

          // Message Input
          _buildMessageInput(),
          const SizedBox(height: 24),

          // Attachment (for WhatsApp/Email)
          if (_selectedChannel != 'sms') _buildAttachmentSection(),
          if (_selectedChannel != 'sms') const SizedBox(height: 24),

          // Send Button
          _buildSendButton(),
          const SizedBox(height: 32),

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
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                _buildChannelCard('sms', 'SMS', Icons.sms, Colors.blue),
                const SizedBox(width: 12),
                _buildChannelCard('email', 'Email', Icons.email, Colors.orange),
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
    final isSelected = _selectedChannel == channel;

    return Expanded(
      child: InkWell(
        onTap: () {
          setState(() {
            _selectedChannel = channel;
            _messageController.clear();
            _selectedTemplate = '';
          });
        },
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color:
                isSelected ? color.withValues(alpha: 0.1) : Colors.grey.shade50,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected ? color : Colors.grey.shade300,
              width: isSelected ? 2 : 1,
            ),
          ),
          child: Column(
            children: [
              Icon(icon, color: isSelected ? color : Colors.grey, size: 32),
              const SizedBox(height: 8),
              Text(
                label,
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  color: isSelected ? color : Colors.grey.shade700,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTemplatesSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Quick Templates',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              height: 100,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: _templates.length,
                itemBuilder: (context, index) {
                  final template = _templates[index];
                  final isSelected = _selectedTemplate == template['name'];

                  Color typeColor;
                  switch (template['type']) {
                    case 'promotional':
                      typeColor = Colors.orange;
                      break;
                    case 'announcement':
                      typeColor = Colors.blue;
                      break;
                    case 'internal':
                      typeColor = Colors.purple;
                      break;
                    case 'reminder':
                      typeColor = Colors.green;
                      break;
                    default:
                      typeColor = Colors.grey;
                  }

                  return Container(
                    width: 160,
                    margin: const EdgeInsets.only(right: 12),
                    child: InkWell(
                      onTap: () => _selectTemplate(template),
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? typeColor.withValues(alpha: 0.1)
                              : Colors.grey.shade50,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color:
                                isSelected ? typeColor : Colors.grey.shade300,
                          ),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: typeColor.withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                template['type'].toString().toUpperCase(),
                                style: TextStyle(
                                  fontSize: 10,
                                  color: typeColor,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              template['name'] as String,
                              style:
                                  const TextStyle(fontWeight: FontWeight.bold),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecipientsSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Select Recipients',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  '${_selectedUsers.length} segments selected',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _segments.map((segment) {
                final isSelected = _selectedUsers.contains(segment['name']);

                return FilterChip(
                  label: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(segment['icon'] as IconData, size: 16),
                      const SizedBox(width: 4),
                      Text(segment['name'] as String),
                      const SizedBox(width: 4),
                      Text(
                        '(${segment['count']})',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                  selected: isSelected,
                  onSelected: (selected) {
                    setState(() {
                      if (selected) {
                        _selectedUsers.add(segment['name'] as String);
                      } else {
                        _selectedUsers.remove(segment['name']);
                      }
                    });
                  },
                  selectedColor: Colors.blue.shade100,
                  checkmarkColor: Colors.blue.shade700,
                );
              }).toList(),
            ),
            if (_selectedUsers.isNotEmpty) ...[
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Icon(Icons.people, color: Colors.blue.shade700),
                    const SizedBox(width: 8),
                    Text(
                      'Total Recipients: ${_selectedUsers.fold<int>(0, (sum, user) {
                        final seg =
                            _segments.firstWhere((s) => s['name'] == user);
                        return sum + (seg['count'] as int);
                      })}',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: Colors.blue.shade700,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildMessageInput() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Message',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),

            if (_selectedChannel == 'email')
              TextField(
                controller: _subjectController,
                decoration: InputDecoration(
                  labelText: 'Subject',
                  prefixIcon: const Icon(Icons.subject),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
            if (_selectedChannel == 'email') const SizedBox(height: 16),

            TextField(
              controller: _messageController,
              maxLines: 6,
              maxLength: _selectedChannel == 'sms' ? 160 : null,
              decoration: InputDecoration(
                hintText: _selectedChannel == 'sms'
                    ? 'Enter SMS message (160 characters max)'
                    : _selectedChannel == 'email'
                        ? 'Enter email body...'
                        : 'Enter WhatsApp message...',
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                counterText: _selectedChannel == 'sms' ? null : '',
              ),
            ),
            const SizedBox(height: 8),

            // Message variables helper
            const Text(
              'Available variables: {{name}}, {{amount}}, {{date}}, {{time}}, {{agent}}, {{colony}}',
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAttachmentSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Attachment',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            if (_attachment == null)
              InkWell(
                onTap: _pickAttachment,
                child: Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                        color: Colors.grey.shade300, style: BorderStyle.solid),
                  ),
                  child: Column(
                    children: [
                      Icon(Icons.add_photo_alternate,
                          size: 48, color: Colors.grey.shade600),
                      const SizedBox(height: 8),
                      Text(
                        'Tap to add image or document',
                        style: TextStyle(color: Colors.grey.shade600),
                      ),
                    ],
                  ),
                ),
              )
            else
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.green.shade50,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    Icon(Icons.check_circle, color: Colors.green.shade700),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Attachment added',
                        style: TextStyle(color: Colors.green.shade700),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () {
                        setState(() {
                          _attachment = null;
                        });
                      },
                    ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildSendButton() {
    Color buttonColor;
    switch (_selectedChannel) {
      case 'sms':
        buttonColor = Colors.blue;
        break;
      case 'email':
        buttonColor = Colors.orange;
        break;
      case 'whatsapp':
        buttonColor = Colors.green;
        break;
      default:
        buttonColor = Colors.blue;
    }

    return ElevatedButton.icon(
      onPressed: _sendCampaign,
      icon: const Icon(Icons.send),
      label: const Text('Send Campaign'),
      style: ElevatedButton.styleFrom(
        backgroundColor: buttonColor,
        foregroundColor: Colors.white,
        minimumSize: const Size(double.infinity, 54),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
                Text(
                  'Best Practices',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 12),
            _buildTip('• Keep SMS under 160 characters to avoid splitting'),
            _buildTip('• Use personalization variables for better engagement'),
            _buildTip('• Include clear call-to-action (CTA)'),
            _buildTip('• Send during business hours (9 AM - 6 PM)'),
            _buildTip('• Test with a small group before bulk sending'),
          ],
        ),
      ),
    );
  }

  Widget _buildTip(String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Text(
        text,
        style: TextStyle(fontSize: 13, color: Colors.grey.shade700),
      ),
    );
  }

  void _showCampaignHistory() {
    showModalBottomSheet(
      context: context,
      builder: (context) => Container(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Campaign History',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            Expanded(
              child: ListView(
                children: [
                  _buildHistoryItem(
                      'Diwali Offer', 'SMS', 1250, 'Sent 2 days ago'),
                  _buildHistoryItem(
                      'New Colony Launch', 'WhatsApp', 890, 'Sent 5 days ago'),
                  _buildHistoryItem(
                      'Commission Reminder', 'Email', 456, 'Sent 1 week ago'),
                  _buildHistoryItem(
                      'Site Visit Reminder', 'SMS', 328, 'Sent 1 week ago'),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHistoryItem(
      String name, String channel, int count, String time) {
    Color channelColor;
    switch (channel) {
      case 'SMS':
        channelColor = Colors.blue;
        break;
      case 'Email':
        channelColor = Colors.orange;
        break;
      case 'WhatsApp':
        channelColor = Colors.green;
        break;
      default:
        channelColor = Colors.grey;
    }

    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: channelColor.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(
          channel,
          style: TextStyle(
            color: channelColor,
            fontWeight: FontWeight.bold,
            fontSize: 12,
          ),
        ),
      ),
      title: Text(name),
      subtitle: Text('$count recipients • $time'),
      trailing: const Icon(Icons.chevron_right),
    );
  }

  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Colors.white),
            const SizedBox(width: 8),
            Text(message),
          ],
        ),
        backgroundColor: Colors.green,
      ),
    );
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.error, color: Colors.white),
            const SizedBox(width: 8),
            Text(message),
          ],
        ),
        backgroundColor: Colors.red,
      ),
    );
  }
}
