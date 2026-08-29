import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/auto_dialer_service.dart';

class TemplatesPage extends ConsumerStatefulWidget {
  const TemplatesPage({super.key});

  @override
  ConsumerState<TemplatesPage> createState() => _TemplatesPageState();
}

class _TemplatesPageState extends ConsumerState<TemplatesPage>
    with SingleTickerProviderStateMixin {
  final AutoDialerService _dialerService = AutoDialerService();
  late TabController _tabController;
  bool _isLoading = true;

  List<Map<String, dynamic>> _smsTemplates = [];
  List<Map<String, dynamic>> _whatsappTemplates = [];
  int _selectedTab = 0;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _tabController.addListener(() {
      setState(() => _selectedTab = _tabController.index);
    });
    _loadTemplates();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadTemplates() async {
    setState(() => _isLoading = true);
    try {
      // Load from local storage or use defaults
      final smsStored = await _loadFromStorage('sms_templates');
      final waStored = await _loadFromStorage('whatsapp_templates');
      setState(() {
        _smsTemplates = smsStored.isNotEmpty
            ? smsStored
            : _defaultSmsTemplates();
        _whatsappTemplates = waStored.isNotEmpty
            ? waStored
            : _defaultWhatsAppTemplates();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _smsTemplates = _defaultSmsTemplates();
        _whatsappTemplates = _defaultWhatsAppTemplates();
        _isLoading = false;
      });
    }
  }

  Future<List<Map<String, dynamic>>> _loadFromStorage(String key) async {
    // Simple in-memory for now; can be replaced with SharedPreferences
    return [];
  }

  List<Map<String, dynamic>> _defaultSmsTemplates() {
    return [
      {
        'id': 1,
        'name': 'Welcome New Lead',
        'category': 'welcome',
        'message':
            'Hello {{name}}! Welcome to APS Dream Home. We have exciting properties for you. Call us at 7007444842 for details.',
        'variables': ['name'],
      },
      {
        'id': 2,
        'name': 'Site Visit Reminder',
        'category': 'reminder',
        'message':
            'Hi {{name}}, this is a reminder about your site visit to {{property}} on {{date}} at {{time}}. See you there!',
        'variables': ['name', 'property', 'date', 'time'],
      },
      {
        'id': 3,
        'name': 'EMI Reminder',
        'category': 'payment',
        'message':
            'Dear {{name}}, your EMI of Rs.{{amount}} is due on {{date}}. Please ensure timely payment to avoid penalties.',
        'variables': ['name', 'amount', 'date'],
      },
      {
        'id': 4,
        'name': 'Follow Up',
        'category': 'follow_up',
        'message':
            'Hi {{name}}, just following up on our conversation about {{property}}. Would you like to schedule a visit?',
        'variables': ['name', 'property'],
      },
      {
        'id': 5,
        'name': 'Property Update',
        'category': 'promotion',
        'message':
            'Great news {{name}}! New plots available in {{colony}} starting at Rs.{{price}}. Limited stock - book now!',
        'variables': ['name', 'colony', 'price'],
      },
    ];
  }

  List<Map<String, dynamic>> _defaultWhatsAppTemplates() {
    return [
      {
        'id': 1,
        'name': 'Welcome Message',
        'category': 'welcome',
        'message':
            'Hello {{name}}! Welcome to APS Dream Home. We are here to help you find your dream property. How can we assist you?',
        'variables': ['name'],
      },
      {
        'id': 2,
        'name': 'Property Details',
        'category': 'property',
        'message':
            'Hi {{name}}, here are the details for {{property}}:\n\nLocation: {{location}}\nPrice: Rs.{{price}}\nSize: {{size}}\n\nWould you like to visit?',
        'variables': ['name', 'property', 'location', 'price', 'size'],
      },
      {
        'id': 3,
        'name': 'Visit Confirmation',
        'category': 'reminder',
        'message':
            'Hi {{name}}, your site visit is confirmed!\n\nDate: {{date}}\nTime: {{time}}\nAddress: {{address}}\n\nSee you soon!',
        'variables': ['name', 'date', 'time', 'address'],
      },
      {
        'id': 4,
        'name': 'Payment Reminder',
        'category': 'payment',
        'message':
            'Dear {{name}}, reminder: Your payment of Rs.{{amount}} for {{property}} is due on {{date}}.',
        'variables': ['name', 'amount', 'property', 'date'],
      },
    ];
  }

  void _showCreateTemplate({
    Map<String, dynamic>? existing,
    bool isSms = true,
  }) {
    final nameController = TextEditingController(
      text: (existing?['name'] ?? '').toString(),
    );
    final messageController = TextEditingController(
      text: (existing?['message'] ?? '').toString(),
    );
    String category = (existing?['category'] ?? 'general').toString();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: const Color(0xFF1B2838),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Padding(
          padding: EdgeInsets.fromLTRB(
            20,
            20,
            20,
            MediaQuery.of(context).viewInsets.bottom + 20,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      isSms ? Icons.sms : Icons.chat,
                      color: Colors.amber,
                      size: 24,
                    ),
                    const SizedBox(width: 10),
                    Text(
                      existing != null ? 'Edit Template' : 'New Template',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                TextField(
                  controller: nameController,
                  style: const TextStyle(color: Colors.white),
                  decoration: InputDecoration(
                    labelText: 'Template Name',
                    labelStyle: const TextStyle(color: Colors.white70),
                    filled: true,
                    fillColor: const Color(0xFF0D1B2A),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: category,
                  dropdownColor: const Color(0xFF0D1B2A),
                  style: const TextStyle(color: Colors.white),
                  decoration: InputDecoration(
                    labelText: 'Category',
                    labelStyle: const TextStyle(color: Colors.white70),
                    filled: true,
                    fillColor: const Color(0xFF0D1B2A),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  items: const [
                    DropdownMenuItem(value: 'general', child: Text('General')),
                    DropdownMenuItem(value: 'welcome', child: Text('Welcome')),
                    DropdownMenuItem(
                      value: 'reminder',
                      child: Text('Reminder'),
                    ),
                    DropdownMenuItem(value: 'payment', child: Text('Payment')),
                    DropdownMenuItem(
                      value: 'follow_up',
                      child: Text('Follow Up'),
                    ),
                    DropdownMenuItem(
                      value: 'promotion',
                      child: Text('Promotion'),
                    ),
                    DropdownMenuItem(
                      value: 'property',
                      child: Text('Property'),
                    ),
                  ],
                  onChanged: (v) =>
                      setModalState(() => category = v ?? 'general'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: messageController,
                  style: const TextStyle(color: Colors.white),
                  maxLines: 5,
                  decoration: InputDecoration(
                    labelText: 'Message Template',
                    labelStyle: const TextStyle(color: Colors.white70),
                    hintText: 'Use {{name}}, {{property}}, etc. for variables',
                    hintStyle: const TextStyle(color: Colors.white38),
                    filled: true,
                    fillColor: const Color(0xFF0D1B2A),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 6,
                  children: [
                    _variableChip('{{name}}', messageController),
                    _variableChip('{{property}}', messageController),
                    _variableChip('{{phone}}', messageController),
                    _variableChip('{{date}}', messageController),
                    _variableChip('{{time}}', messageController),
                    _variableChip('{{amount}}', messageController),
                    _variableChip('{{colony}}', messageController),
                  ],
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      final name = nameController.text.trim();
                      final message = messageController.text.trim();
                      if (name.isEmpty || message.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Name and message required'),
                          ),
                        );
                        return;
                      }
                      final template = {
                        'id':
                            existing?['id'] ??
                            DateTime.now().millisecondsSinceEpoch,
                        'name': name,
                        'category': category,
                        'message': message,
                        'variables': _extractVariables(message),
                      };
                      setModalState(() {
                        if (isSms) {
                          if (existing != null) {
                            _smsTemplates.removeWhere(
                              (t) => t['id'] == existing['id'],
                            );
                          }
                          _smsTemplates.add(template);
                        } else {
                          if (existing != null) {
                            _whatsappTemplates.removeWhere(
                              (t) => t['id'] == existing['id'],
                            );
                          }
                          _whatsappTemplates.add(template);
                        }
                      });
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('Template "${template['name']}" saved'),
                        ),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.amber.shade700,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: Text(existing != null ? 'Update' : 'Create'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _variableChip(String variable, TextEditingController controller) {
    return ActionChip(
      label: Text(
        variable,
        style: const TextStyle(fontSize: 11, color: Colors.white70),
      ),
      backgroundColor: const Color(0xFF0D1B2A),
      side: const BorderSide(color: Colors.white24),
      onPressed: () {
        final text = controller.text;
        controller.text = '$text $variable';
        controller.selection = TextSelection.fromPosition(
          TextPosition(offset: controller.text.length),
        );
      },
    );
  }

  List<String> _extractVariables(String message) {
    final regex = RegExp(r'\{\{(\w+)\}\}');
    return regex.allMatches(message).map((m) => m.group(0)!).toList();
  }

  void _deleteTemplate(Map<String, dynamic> template, bool isSms) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: const Color(0xFF1B2838),
        title: const Text(
          'Delete Template?',
          style: TextStyle(color: Colors.white),
        ),
        content: Text(
          'Delete "${template['name']}"?',
          style: const TextStyle(color: Colors.white70),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              setState(() {
                if (isSms) {
                  _smsTemplates.removeWhere((t) => t['id'] == template['id']);
                } else {
                  _whatsappTemplates.removeWhere(
                    (t) => t['id'] == template['id'],
                  );
                }
              });
              Navigator.pop(context);
              ScaffoldMessenger.of(
                context,
              ).showSnackBar(const SnackBar(content: Text('Template deleted')));
            },
            child: const Text('Delete', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0D1B2A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1B2838),
        title: const Text('Message Templates'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Colors.amber))
          : Column(
              children: [
                TabBar(
                  controller: _tabController,
                  indicatorColor: Colors.amber,
                  labelColor: Colors.amber,
                  unselectedLabelColor: Colors.white54,
                  tabs: const [
                    Tab(text: 'SMS Templates', icon: Icon(Icons.sms)),
                    Tab(text: 'WhatsApp Templates', icon: Icon(Icons.chat)),
                  ],
                ),
                Expanded(
                  child: _selectedTab == 0
                      ? _buildTemplateList(_smsTemplates, isSms: true)
                      : _buildTemplateList(_whatsappTemplates, isSms: false),
                ),
              ],
            ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: Colors.amber.shade700,
        onPressed: () => _showCreateTemplate(isSms: _selectedTab == 0),
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildTemplateList(
    List<Map<String, dynamic>> templates, {
    required bool isSms,
  }) {
    if (templates.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              isSms ? Icons.sms : Icons.chat,
              size: 48,
              color: Colors.white30,
            ),
            const SizedBox(height: 16),
            const Text(
              'No templates yet',
              style: TextStyle(color: Colors.white54, fontSize: 16),
            ),
            const SizedBox(height: 8),
            const Text(
              'Tap + to create your first template',
              style: TextStyle(color: Colors.white38, fontSize: 13),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: templates.length,
      itemBuilder: (context, index) {
        final template = templates[index];
        return Card(
          color: const Color(0xFF1B2838),
          margin: const EdgeInsets.only(bottom: 8),
          child: ListTile(
            contentPadding: const EdgeInsets.all(12),
            leading: CircleAvatar(
              backgroundColor: _categoryColor(
                (template['category'] ?? '').toString(),
              ).withValues(alpha: 0.2),
              child: Icon(
                isSms ? Icons.sms : Icons.chat,
                color: _categoryColor((template['category'] ?? '').toString()),
                size: 20,
              ),
            ),
            title: Text(
              (template['name'] ?? '').toString(),
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.bold,
              ),
            ),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 4),
                Text(
                  (template['message'] ?? '').toString().length > 80
                      ? '${(template['message'] ?? '').toString().substring(0, 80)}...'
                      : (template['message'] ?? '').toString(),
                  style: const TextStyle(color: Colors.white70, fontSize: 12),
                ),
                const SizedBox(height: 4),
                Wrap(
                  spacing: 4,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 6,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: _categoryColor(
                          (template['category'] ?? '').toString(),
                        ).withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        (template['category'] ?? '').toString().toUpperCase(),
                        style: TextStyle(
                          color: _categoryColor(
                            (template['category'] ?? '').toString(),
                          ),
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    if ((template['variables'] as List?)?.isNotEmpty == true)
                      Text(
                        '${(template['variables'] as List).length} vars',
                        style: const TextStyle(
                          color: Colors.white38,
                          fontSize: 10,
                        ),
                      ),
                  ],
                ),
              ],
            ),
            trailing: PopupMenuButton(
              color: const Color(0xFF0D1B2A),
              itemBuilder: (context) => [
                const PopupMenuItem(value: 'edit', child: Text('Edit')),
                const PopupMenuItem(
                  value: 'delete',
                  child: Text('Delete', style: TextStyle(color: Colors.red)),
                ),
              ],
              onSelected: (value) {
                if (value == 'edit') {
                  _showCreateTemplate(existing: template, isSms: isSms);
                } else if (value == 'delete') {
                  _deleteTemplate(template, isSms);
                }
              },
            ),
          ),
        );
      },
    );
  }

  Color _categoryColor(String? category) {
    switch (category) {
      case 'welcome':
        return Colors.green;
      case 'reminder':
        return Colors.blue;
      case 'payment':
        return Colors.orange;
      case 'follow_up':
        return Colors.purple;
      case 'promotion':
        return Colors.red;
      case 'property':
        return Colors.teal;
      default:
        return Colors.white70;
    }
  }
}
