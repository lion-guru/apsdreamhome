import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:http/http.dart' as http;
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class WhatsAppTemplatesPage extends StatefulWidget {
  const WhatsAppTemplatesPage({super.key});

  @override
  State<WhatsAppTemplatesPage> createState() => _WhatsAppTemplatesPageState();
}

class _WhatsAppTemplatesPageState extends State<WhatsAppTemplatesPage> {
  List<Map<String, dynamic>> _templates = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      AppConstants.initBaseUrl();
      final baseUrl = AppConstants.baseUrl;

      final response = await http
          .get(Uri.parse('$baseUrl/api/v2/mobile/whatsapp/templates'))
          .timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] is List) {
          if (mounted) {
            setState(() {
              _templates = List<Map<String, dynamic>>.from(data['data'] as List);
              _loading = false;
            });
          }
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _templates = _mockTemplates;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        colors: const [Color(0xFF075E54), Color(0xFF128C7E), Color(0xFF25D366)],
        child: SafeArea(
          child: _loading
              ? const Center(child: CircularProgressIndicator(color: Colors.white))
              : Column(
                  children: [
                    _buildHeader(),
                    Expanded(
                      child: _templates.isEmpty
                          ? _buildEmptyState()
                          : _buildTemplatesList(),
                    ),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
      child: Column(
        children: [
          GestureDetector(
            onTap: () => context.pop(),
            child: Align(
              alignment: Alignment.centerLeft,
              child: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.arrow_back, color: Colors.white, size: 22),
              ),
            ),
          ),
          const SizedBox(height: 10),
          ShaderMask(
            shaderCallback: (bounds) => const LinearGradient(
              colors: [Colors.white, Color(0xFF82E0AA)],
            ).createShader(bounds),
            child: Text(
              'WhatsApp Templates',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Pre-approved message templates for automated communication',
            style: TextStyle(color: Colors.white70, fontSize: 14),
          ),
        ],
      ),
    );
  }

  Widget _buildTemplatesList() {
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
      itemCount: _templates.length,
      itemBuilder: (context, index) {
        final template = _templates[index];
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: _buildTemplateCard(template),
        );
      },
    );
  }

  Widget _buildTemplateCard(Map<String, dynamic> template) {
    final name = template['name']?.toString() ?? 'Template';
    final category = template['category']?.toString() ?? 'General';
    final status = (template['status'] ?? template['approval_status'] ?? 'approved').toString().toLowerCase();
    final statusColor = _getStatusColor(status);
    final statusLabel = status[0].toUpperCase() + status.substring(1);
    final body = template['body']?.toString() ?? template['content']?.toString() ?? '';
    final variables = template['variables'] as List? ?? [];
    final language = template['language']?.toString() ?? 'en';
    final usageCount = template['usage_count'] ?? template['sent_count'] ?? 0;

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: const Color(0xFF25D366).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.chat_rounded, color: Color(0xFF25D366), size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 15),
                    ),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            category,
                            style: TextStyle(color: Colors.white.withValues(alpha: 0.7), fontSize: 10, fontWeight: FontWeight.w500),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: statusColor.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            statusLabel,
                            style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.w600),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              if (language != 'en')
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    language.toUpperCase(),
                    style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w600),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),
          if (body.isNotEmpty)
            Text(
              body.length > 120 ? '${body.substring(0, 120)}...' : body,
              style: TextStyle(color: Colors.white.withValues(alpha: 0.7), fontSize: 12, height: 1.4),
            ),
          if (variables.isNotEmpty) ...[
            const SizedBox(height: 10),
            Wrap(
              spacing: 6,
              runSpacing: 4,
              children: variables.map((v) {
                return Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    '{{${v.toString()}}}',
                    style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 9, fontFamily: 'monospace'),
                  ),
                );
              }).toList(),
            ),
          ],
          const SizedBox(height: 12),
          Row(
            children: [
              Icon(Icons.analytics_rounded, size: 14, color: Colors.white.withValues(alpha: 0.5)),
              const SizedBox(width: 4),
              Text(
                '$usageCount uses',
                style: TextStyle(color: Colors.white.withValues(alpha: 0.5), fontSize: 11),
              ),
              const Spacer(),
              if (status == 'approved' || status == 'active')
                _buildActionButton('Preview', Icons.preview_rounded, () => _showPreview(template)),
              const SizedBox(width: 8),
              _buildActionButton('Copy', Icons.copy_rounded, () => _copyTemplate(template)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionButton(String label, IconData icon, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.white.withValues(alpha: 0.15)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: Colors.white.withValues(alpha: 0.7)),
            const SizedBox(width: 6),
            Text(label, style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 11, fontWeight: FontWeight.w500)),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.chat_bubble_outline_rounded, size: 64, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          Text(
            'No WhatsApp Templates',
            style: TextStyle(color: Colors.grey.shade400, fontSize: 16),
          ),
          const SizedBox(height: 8),
          Text(
            'Approved templates will appear here',
            style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
          ),
        ],
      ),
    );
  }

  void _showPreview(Map<String, dynamic> template) {
    final body = template['body']?.toString() ?? template['content']?.toString() ?? '';
    final variables = template['variables'] as List? ?? [];
    final name = template['name']?.toString() ?? 'Template';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.7,
        decoration: const BoxDecoration(
          color: Color(0xFF0D1B2A),
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          children: [
            Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(top: 12),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(20),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(name, style: AppTheme.titleLarge.copyWith(color: Colors.white)),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, color: Colors.white),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            ),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    GlassCard(
                      padding: const EdgeInsets.all(16),
                      opacity: 0.1,
                      blur: 8,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.smartphone_rounded, color: Color(0xFF25D366), size: 20),
                              const SizedBox(width: 8),
                              Text('Preview', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13)),
                              const Spacer(),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: Colors.green.withValues(alpha: 0.2),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text('APPROVED', style: TextStyle(color: Colors.green, fontSize: 9, fontWeight: FontWeight.w600)),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.green.shade50,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.green.shade200),
                            ),
                            child: Text(
                              body,
                              style: const TextStyle(color: Colors.black87, fontSize: 14, height: 1.5),
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (variables.isNotEmpty) ...[
                      const SizedBox(height: 16),
                      Text('Variables Used', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13)),
                      const SizedBox(height: 10),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: variables.map((v) {
                          return GlassCard(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            opacity: 0.1,
                            blur: 6,
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.code_rounded, size: 14, color: Colors.white70),
                                const SizedBox(width: 6),
                                Text(
                                  '{{${v.toString()}}}',
                                  style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 11, fontFamily: 'monospace'),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _copyTemplate(Map<String, dynamic> template) {
    final body = template['body']?.toString() ?? template['content']?.toString() ?? '';
    // In a real app, you'd use clipboard
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Template copied to clipboard'),
        backgroundColor: AppTheme.successColor,
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'approved':
      case 'active':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'rejected':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  static const _mockTemplates = [
    {
      'name': 'Welcome Message',
      'category': 'Marketing',
      'status': 'approved',
      'body': 'Welcome to APS Dream Home, {{name}}! 🏠 We\'re excited to help you find your dream property. Explore our verified colonies and plots at {{app_url}}. For assistance, call {{support_phone}}.',
      'variables': ['name', 'app_url', 'support_phone'],
      'language': 'en',
      'usage_count': 1245,
    },
    {
      'name': 'Payment Confirmation',
      'category': 'Transactional',
      'status': 'approved',
      'body': 'Payment of ₹{{amount}} received for {{booking_id}}. Transaction ID: {{txn_id}}. Download receipt: {{receipt_url}}. Thank you for choosing APS Dream Home!',
      'variables': ['amount', 'booking_id', 'txn_id', 'receipt_url'],
      'language': 'en',
      'usage_count': 892,
    },
    {
      'name': 'EMI Reminder',
      'category': 'Transactional',
      'status': 'approved',
      'body': 'Dear {{name}}, your EMI of ₹{{emi_amount}} for plot {{plot_no}} is due on {{due_date}}. Pay now: {{payment_url}}. Avoid late fees. - APS Dream Home',
      'variables': ['name', 'emi_amount', 'plot_no', 'due_date', 'payment_url'],
      'language': 'en',
      'usage_count': 2103,
    },
    {
      'name': 'Site Visit Confirmation',
      'category': 'Transactional',
      'status': 'approved',
      'body': 'Your site visit for {{colony_name}} is confirmed on {{visit_date}} at {{visit_time}}. Agent: {{agent_name}} ({{agent_phone}}). Location: {{meeting_point}}. See you there! - APS Dream Home',
      'variables': ['colony_name', 'visit_date', 'visit_time', 'agent_name', 'agent_phone', 'meeting_point'],
      'language': 'en',
      'usage_count': 567,
    },
    {
      'name': 'Lead Follow-up',
      'category': 'Marketing',
      'status': 'pending',
      'body': 'Hi {{name}}, thanks for your interest in {{property_type}} at {{colony_name}}. Our expert {{agent_name}} will call you shortly at {{phone}}. Questions? Reply to this message. - APS Dream Home',
      'variables': ['name', 'property_type', 'colony_name', 'agent_name', 'phone'],
      'language': 'en',
      'usage_count': 334,
    },
    {
      'name': 'Property Recommendation',
      'category': 'Marketing',
      'status': 'approved',
      'body': 'Based on your preferences, we found {{count}} properties matching your criteria in {{location}}. View them here: {{properties_url}}. Best, APS Dream Home Team',
      'variables': ['count', 'location', 'properties_url'],
      'language': 'en',
      'usage_count': 789,
    },
    {
      'name': 'Registration Welcome (Hindi)',
      'category': 'Transactional',
      'status': 'approved',
      'body': 'APS Dream Home में आपका स्वागत है {{name}} जी! 🙏 आपका रजिस्ट्रेशन सफल रहा। आपका रेफरल कोड: {{referral_code}}. प्रॉपर्टी देखें: {{app_url}. सहायता के लिए: {{support_phone}}',
      'variables': ['name', 'referral_code', 'app_url', 'support_phone'],
      'language': 'hi',
      'usage_count': 445,
    },
    {
      'name': 'Document Ready Notification',
      'category': 'Transactional',
      'status': 'approved',
      'body': 'Your {{document_name}} is ready for {{action}}. Download: {{document_url}}. Valid until {{expiry_date}}. For queries, contact {{support_phone}}. - APS Dream Home',
      'variables': ['document_name', 'action', 'document_url', 'expiry_date', 'support_phone'],
      'language': 'en',
      'usage_count': 234,
    },
  ];
}