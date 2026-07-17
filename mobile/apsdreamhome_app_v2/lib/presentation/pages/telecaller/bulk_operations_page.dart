import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/services/auto_dialer_service.dart';

class BulkOperationsPage extends ConsumerStatefulWidget {
  const BulkOperationsPage({super.key});

  @override
  ConsumerState<BulkOperationsPage> createState() => _BulkOperationsPageState();
}

class _BulkOperationsPageState extends ConsumerState<BulkOperationsPage> {
  final AutoDialerService _dialerService = AutoDialerService();
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  bool _isSending = false;
  List<Map<String, dynamic>> _leads = [];
  Set<int> _selectedLeadIds = {};
  String _searchQuery = '';
  String _filterStatus = 'all';
  String _actionType = 'sms'; // sms, whatsapp, call

  final TextEditingController _messageController = TextEditingController();
  Map<String, dynamic>? _selectedTemplate;
  List<Map<String, dynamic>> _smsTemplates = [];
  List<Map<String, dynamic>> _whatsAppTemplates = [];

  @override
  void initState() {
    super.initState();
    _loadLeads();
    _loadTemplates();
  }

  void _loadTemplates() {
    _smsTemplates = [
      {
        'name': 'Welcome New Lead',
        'message':
            'Hello {{name}}! Welcome to APS Dream Home. Call us at 7007444842 for exciting property deals.',
      },
      {
        'name': 'Site Visit Reminder',
        'message':
            'Hi {{name}}, reminder: your site visit to {{property}} is on {{date}} at {{time}}. See you there!',
      },
      {
        'name': 'EMI Reminder',
        'message':
            'Dear {{name}}, your EMI of Rs.{{amount}} is due on {{date}}. Please pay on time.',
      },
      {
        'name': 'Follow Up',
        'message':
            'Hi {{name}}, following up on {{property}}. Would you like to schedule a site visit?',
      },
    ];
    _whatsAppTemplates = [
      {
        'name': 'Welcome Message',
        'message':
            'Hello {{name}}! Welcome to APS Dream Home. How can we help you find your dream property?',
      },
      {
        'name': 'Property Details',
        'message':
            'Hi {{name}}, here are details for {{property}}:\nLocation: {{location}}\nPrice: Rs.{{price}}',
      },
      {
        'name': 'Visit Confirmation',
        'message':
            'Hi {{name}}, your site visit is confirmed!\nDate: {{date}}\nTime: {{time}}',
      },
    ];
  }

  List<Map<String, dynamic>> get _currentTemplates =>
      _actionType == 'whatsapp' ? _whatsAppTemplates : _smsTemplates;

  String _personalize(String template, Map<String, dynamic> lead) {
    return template
        .replaceAll('{{name}}', (lead['name'] ?? 'Customer').toString())
        .replaceAll(
          '{{property}}',
          (lead['property_interest'] ?? 'property').toString(),
        )
        .replaceAll('{{phone}}', (lead['phone'] ?? '').toString())
        .replaceAll('{{date}}', DateTime.now().toString().split(' ')[0])
        .replaceAll('{{time}}', '10:00 AM')
        .replaceAll(
          '{{amount}}',
          '${(num.tryParse((lead['budget'] ?? 0).toString()) ?? 0 / 100000)
                  .toStringAsFixed(1)}L',
        )
        .replaceAll('{{colony}}', (lead['city'] ?? 'our colony').toString())
        .replaceAll(
          '{{location}}',
          (lead['city'] ?? 'prime location').toString(),
        )
        .replaceAll(
          '{{price}}',
          (num.tryParse((lead['budget'] ?? 0).toString()) ?? 0).toString(),
        );
  }

  Future<void> _loadLeads() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.get('leads');
      if (response['success'] == true) {
        final data = response['data'];
        setState(() {
          _leads = (data is List)
              ? data.map((e) => Map<String, dynamic>.from(e as Map)).toList()
              : [];
          _isLoading = false;
        });
      } else {
        setState(() {
          _leads = _mockLeads();
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _leads = _mockLeads();
        _isLoading = false;
      });
    }
  }

  List<Map<String, dynamic>> _mockLeads() {
    return List.generate(15, (i) {
      final statuses = ['new', 'contacted', 'qualified', 'hot', 'cold'];
      return {
        'id': i + 1,
        'name': [
          'Rahul',
          'Priya',
          'Amit',
          'Sneha',
          'Vikram',
          'Neha',
          'Sanjay',
          'Kavita',
          'Rajesh',
          'Pooja',
          'Manoj',
          'Anita',
          'Deepak',
          'Meena',
          'Suresh',
        ][i],
        'phone': '9${(9000000000 + i * 111111111)}',
        'status': statuses[i % 5],
        'budget': 2500000 + i * 500000,
        'property_interest': [
          'Plot',
          'Apartment',
          'Villa',
          'Commercial',
        ][i % 4],
        'last_contacted': i < 5 ? '2026-07-${14 - i}' : null,
      };
    });
  }

  List<Map<String, dynamic>> get _filteredLeads {
    return _leads.where((lead) {
      final name = (lead['name'] ?? '').toString().toLowerCase();
      final phone = (lead['phone'] ?? '').toString();
      final matchesSearch =
          _searchQuery.isEmpty ||
          name.contains(_searchQuery.toLowerCase()) ||
          phone.contains(_searchQuery);
      final matchesStatus =
          _filterStatus == 'all' || (lead['status'] ?? '') == _filterStatus;
      return matchesSearch && matchesStatus;
    }).toList();
  }

  void _toggleSelectAll() {
    setState(() {
      if (_selectedLeadIds.length == _filteredLeads.length) {
        _selectedLeadIds.clear();
      } else {
        _selectedLeadIds = _filteredLeads.map((l) => l['id'] as int).toSet();
      }
    });
  }

  void _toggleLead(int id) {
    setState(() {
      if (_selectedLeadIds.contains(id)) {
        _selectedLeadIds.remove(id);
      } else {
        _selectedLeadIds.add(id);
      }
    });
  }

  Future<void> _executeBulkAction() async {
    if (_selectedLeadIds.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Select at least one lead')));
      return;
    }

    final message = _messageController.text.trim();
    if (_actionType != 'call' && message.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Enter a message')));
      return;
    }

    setState(() => _isSending = true);

    try {
      final selectedLeads = _leads
          .where((l) => _selectedLeadIds.contains(l['id']))
          .toList();

      if (_actionType == 'call') {
        // Open dialer for first lead, log the rest
        if (selectedLeads.isNotEmpty) {
          final phone = (selectedLeads[0]['phone'] ?? '').toString();
          final uri = Uri(scheme: 'tel', path: phone);
          if (await canLaunchUrl(uri)) {
            await launchUrl(uri);
          }
          for (final lead in selectedLeads) {
            await _logBulkAction(lead, 'call');
          }
        }
      } else if (_actionType == 'sms') {
        for (final lead in selectedLeads) {
          final phone = (lead['phone'] ?? '').toString();
          if (phone.isNotEmpty) {
            final msg = _selectedTemplate != null
                ? _personalize(
                    (_selectedTemplate!['message'] ?? message).toString(),
                    lead,
                  )
                : message;
            await _dialerService.sendSms(phone: phone, message: msg);
          }
        }
      } else if (_actionType == 'whatsapp') {
        for (final lead in selectedLeads) {
          final phone = (lead['phone'] ?? '').toString();
          if (phone.isNotEmpty) {
            final msg = _selectedTemplate != null
                ? _personalize(
                    (_selectedTemplate!['message'] ?? message).toString(),
                    lead,
                  )
                : message;
            await _dialerService.sendWhatsApp(phone: phone, message: msg);
          }
        }
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              '${_selectedLeadIds.length} leads ${_actionType == 'call'
                  ? 'dialing'
                  : _actionType == 'sms'
                  ? 'SMS sent'
                  : 'WhatsApp sent'}',
            ),
            backgroundColor: Colors.green,
          ),
        );
        setState(() {
          _selectedLeadIds.clear();
          _messageController.clear();
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isSending = false);
    }
  }

  Future<void> _logBulkAction(Map<String, dynamic> lead, String action) async {
    try {
      await _apiService.post(
        AppConstants.callLogEndpoint,
        data: {
          'lead_id': lead['id'],
          'phone': lead['phone'],
          'action': 'bulk_$action',
          'timestamp': DateTime.now().toIso8601String(),
        },
      );
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0D1B2A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1B2838),
        title: const Text('Bulk Operations'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
        actions: [
          if (_leads.isNotEmpty)
            TextButton(
              onPressed: _toggleSelectAll,
              child: Text(
                _selectedLeadIds.length == _filteredLeads.length
                    ? 'Deselect All'
                    : 'Select All',
                style: const TextStyle(color: Colors.amber),
              ),
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Colors.amber))
          : Column(
              children: [
                _buildSearchBar(),
                _buildActionSelector(),
                if (_actionType != 'call') _buildTemplatePicker(),
                if (_actionType != 'call') _buildMessageInput(),
                _buildSelectedCount(),
                Expanded(child: _buildLeadsList()),
              ],
            ),
      bottomNavigationBar: _buildBottomBar(),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      padding: const EdgeInsets.all(12),
      color: const Color(0xFF1B2838),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              onChanged: (v) => setState(() => _searchQuery = v),
              style: const TextStyle(color: Colors.white),
              decoration: InputDecoration(
                hintText: 'Search leads by name or phone...',
                hintStyle: const TextStyle(color: Colors.white38),
                prefixIcon: const Icon(Icons.search, color: Colors.white54),
                filled: true,
                fillColor: const Color(0xFF0D1B2A),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(vertical: 0),
              ),
            ),
          ),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            decoration: BoxDecoration(
              color: const Color(0xFF0D1B2A),
              borderRadius: BorderRadius.circular(10),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: _filterStatus,
                dropdownColor: const Color(0xFF0D1B2A),
                style: const TextStyle(color: Colors.white, fontSize: 13),
                isDense: true,
                items: const [
                  DropdownMenuItem(value: 'all', child: Text('All')),
                  DropdownMenuItem(value: 'new', child: Text('New')),
                  DropdownMenuItem(
                    value: 'contacted',
                    child: Text('Contacted'),
                  ),
                  DropdownMenuItem(value: 'hot', child: Text('Hot')),
                  DropdownMenuItem(value: 'cold', child: Text('Cold')),
                ],
                onChanged: (v) => setState(() => _filterStatus = v ?? 'all'),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionSelector() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      color: const Color(0xFF1B2838),
      child: Row(
        children: [
          _actionChip('SMS', 'sms', Icons.sms, Colors.blue),
          const SizedBox(width: 8),
          _actionChip('WhatsApp', 'whatsapp', Icons.chat, Colors.green),
          const SizedBox(width: 8),
          _actionChip('Call', 'call', Icons.phone, Colors.amber),
        ],
      ),
    );
  }

  Widget _buildTemplatePicker() {
    final templates = _currentTemplates;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      color: const Color(0xFF1B2838),
      child: Row(
        children: [
          const Icon(Icons.format_quote, color: Colors.white54, size: 18),
          const SizedBox(width: 8),
          Expanded(
            child: DropdownButtonHideUnderline(
              child: DropdownButton<Map<String, dynamic>>(
                value: _selectedTemplate,
                dropdownColor: const Color(0xFF0D1B2A),
                hint: const Text(
                  'Use saved template (optional)',
                  style: TextStyle(color: Colors.white54, fontSize: 13),
                ),
                style: const TextStyle(color: Colors.white, fontSize: 13),
                isExpanded: true,
                items: [
                  const DropdownMenuItem<Map<String, dynamic>>(
                    value: null,
                    child: Text(
                      'None (write custom)',
                      style: TextStyle(color: Colors.white54),
                    ),
                  ),
                  ...templates.map(
                    (t) => DropdownMenuItem<Map<String, dynamic>>(
                      value: t,
                      child: Text(
                        (t['name'] ?? '').toString(),
                        style: const TextStyle(fontSize: 13),
                      ),
                    ),
                  ),
                ],
                onChanged: (t) {
                  setState(() {
                    _selectedTemplate = t;
                    if (t != null) {
                      _messageController.text = (t['message'] ?? '').toString();
                    }
                  });
                },
              ),
            ),
          ),
          if (_selectedTemplate != null)
            IconButton(
              icon: const Icon(Icons.clear, color: Colors.white54, size: 18),
              onPressed: () => setState(() {
                _selectedTemplate = null;
                _messageController.clear();
              }),
            ),
        ],
      ),
    );
  }

  Widget _actionChip(String label, String value, IconData icon, Color color) {
    final isSelected = _actionType == value;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _actionType = value),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected
                ? color.withValues(alpha: 0.2)
                : const Color(0xFF0D1B2A),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: isSelected ? color : Colors.white24,
              width: isSelected ? 2 : 1,
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: isSelected ? color : Colors.white54, size: 18),
              const SizedBox(width: 6),
              Text(
                label,
                style: TextStyle(
                  color: isSelected ? color : Colors.white70,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  fontSize: 13,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMessageInput() {
    return Container(
      padding: const EdgeInsets.all(12),
      color: const Color(0xFF1B2838),
      child: TextField(
        controller: _messageController,
        style: const TextStyle(color: Colors.white),
        maxLines: 3,
        decoration: InputDecoration(
          hintText: _actionType == 'sms'
              ? 'Type SMS message... Use {{name}} for personalization'
              : 'Type WhatsApp message... Use {{name}} for personalization',
          hintStyle: const TextStyle(color: Colors.white38),
          filled: true,
          fillColor: const Color(0xFF0D1B2A),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide.none,
          ),
        ),
      ),
    );
  }

  Widget _buildSelectedCount() {
    if (_selectedLeadIds.isEmpty) return const SizedBox.shrink();
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      color: Colors.amber.withValues(alpha: 0.1),
      child: Row(
        children: [
          const Icon(Icons.check_circle, color: Colors.amber, size: 18),
          const SizedBox(width: 8),
          Text(
            '${_selectedLeadIds.length} leads selected',
            style: const TextStyle(
              color: Colors.amber,
              fontWeight: FontWeight.bold,
            ),
          ),
          const Spacer(),
          GestureDetector(
            onTap: () => setState(() => _selectedLeadIds.clear()),
            child: const Text(
              'Clear',
              style: TextStyle(color: Colors.amber, fontSize: 12),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLeadsList() {
    final filtered = _filteredLeads;
    if (filtered.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.people_outline, size: 48, color: Colors.white30),
            SizedBox(height: 16),
            Text(
              'No leads found',
              style: TextStyle(color: Colors.white54, fontSize: 16),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: filtered.length,
      itemBuilder: (context, index) {
        final lead = filtered[index];
        final id = lead['id'] as int;
        final isSelected = _selectedLeadIds.contains(id);
        final name = (lead['name'] ?? 'Unknown').toString();
        final phone = (lead['phone'] ?? '').toString();
        final status = (lead['status'] ?? '').toString();
        final budget = lead['budget'] ?? 0;

        return GestureDetector(
          onTap: () => _toggleLead(id),
          onLongPress: () {
            // Quick call on long press
            final uri = Uri(scheme: 'tel', path: phone);
            canLaunchUrl(uri).then((can) {
              if (can) launchUrl(uri);
            });
          },
          child: Card(
            color: isSelected
                ? Colors.amber.withValues(alpha: 0.1)
                : const Color(0xFF1B2838),
            margin: const EdgeInsets.only(bottom: 6),
            child: Padding(
              padding: const EdgeInsets.all(10),
              child: Row(
                children: [
                  Checkbox(
                    value: isSelected,
                    onChanged: (_) => _toggleLead(id),
                    activeColor: Colors.amber,
                    checkColor: Colors.black,
                  ),
                  CircleAvatar(
                    backgroundColor: _statusColor(
                      status,
                    ).withValues(alpha: 0.2),
                    child: Text(
                      name[0].toUpperCase(),
                      style: TextStyle(
                        color: _statusColor(status),
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Text(
                          phone,
                          style: const TextStyle(
                            color: Colors.white70,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: _statusColor(status).withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          status.toUpperCase(),
                          style: TextStyle(
                            color: _statusColor(status),
                            fontSize: 9,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '₹${(budget / 100000).toStringAsFixed(1)}L',
                        style: const TextStyle(
                          color: Colors.white54,
                          fontSize: 11,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildBottomBar() {
    return Container(
      color: const Color(0xFF1B2838),
      padding: const EdgeInsets.all(12),
      child: SizedBox(
        width: double.infinity,
        child: ElevatedButton.icon(
          onPressed: _selectedLeadIds.isEmpty || _isSending
              ? null
              : _executeBulkAction,
          icon: _isSending
              ? const SizedBox(
                  width: 16,
                  height: 16,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: Colors.white,
                  ),
                )
              : Icon(
                  _actionType == 'call'
                      ? Icons.phone
                      : _actionType == 'sms'
                      ? Icons.sms
                      : Icons.chat,
                ),
          label: Text(
            _isSending
                ? 'Sending...'
                : _selectedLeadIds.isEmpty
                ? 'Select leads first'
                : '${_actionType == 'call'
                      ? 'Call'
                      : _actionType == 'sms'
                      ? 'Send SMS'
                      : 'Send WhatsApp'} (${_selectedLeadIds.length})',
          ),
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.amber.shade700,
            foregroundColor: Colors.white,
            disabledBackgroundColor: Colors.white24,
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        ),
      ),
    );
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'hot':
        return Colors.red;
      case 'new':
        return Colors.blue;
      case 'contacted':
        return Colors.green;
      case 'qualified':
        return Colors.purple;
      case 'cold':
        return Colors.grey;
      default:
        return Colors.white70;
    }
  }
}
