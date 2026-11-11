import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/api_service.dart';

final notificationPrefsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  final res = await api.get('/api/v2/mobile/notification-preferences');
  return res;
});

class NotificationPreferencesPage extends ConsumerStatefulWidget {
  const NotificationPreferencesPage({super.key});

  @override
  ConsumerState<NotificationPreferencesPage> createState() => _NotificationPreferencesPageState();
}

class _NotificationPreferencesPageState extends ConsumerState<NotificationPreferencesPage> {
  final Map<String, Map<String, bool>> _localPrefs = {};
  bool _saving = false;

  void _initLocalPrefs(List<dynamic> list) {
    if (_localPrefs.isEmpty) {
      for (final item in list) {
        final m = item as Map<String, dynamic>;
        final type = m['type']?.toString() ?? '';
        if (type.isNotEmpty) {
          _localPrefs[type] = {
            'whatsapp': m['whatsapp'] == true,
            'sms': m['sms'] == true,
            'push': m['push'] == true,
            'email': m['email'] == true,
          };
        }
      }
    }
  }

  Future<void> _saveAllPrefs() async {
    setState(() => _saving = true);
    try {
      final api = ApiService();
      final payload = _localPrefs.entries.map((e) {
        return {
          'type': e.key,
          'whatsapp': e.value['whatsapp'] ?? true,
          'sms': e.value['sms'] ?? true,
          'push': e.value['push'] ?? true,
          'email': e.value['email'] ?? true,
        };
      }).toList();

      final res = await api.post('/api/v2/mobile/notification-preferences/save', data: {
        'preferences': payload,
      });

      if (mounted) {
        final msg = ((res as Map<String, dynamic>?)?['message'] ?? 'Preferences updated!').toString();
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final prefsAsync = ref.watch(notificationPrefsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Notification Preferences', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              _localPrefs.clear();
              ref.invalidate(notificationPrefsProvider);
            },
          ),
        ],
      ),
      body: prefsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: Color(0xFFD4AF37))),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (data) {
          final list = (data['preferences'] as List<dynamic>?) ?? [];
          _initLocalPrefs(list);

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFD4AF37).withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0B194C).withValues(alpha: 0.08),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.tune, color: Color(0xFF0B194C), size: 24),
                    ),
                    const SizedBox(width: 14),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Multi-Channel Alert Center', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: Color(0xFF0B194C))),
                          SizedBox(height: 4),
                          Text('Choose which channel delivers your plot bookings, receipts and registry updates.', style: TextStyle(fontSize: 12, color: Colors.grey, height: 1.3)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              ...list.map((item) {
                final m = item as Map<String, dynamic>;
                final type = m['type']?.toString() ?? '';
                final title = m['name']?.toString() ?? 'Alert';
                final desc = m['desc']?.toString() ?? '';
                final current = _localPrefs[type] ?? {'whatsapp': true, 'sms': true, 'push': true, 'email': true};

                return Card(
                  margin: const EdgeInsets.only(bottom: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(title, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
                        const SizedBox(height: 2),
                        Text(desc, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                        const Divider(height: 20),
                        Wrap(
                          spacing: 12,
                          runSpacing: 8,
                          children: [
                            FilterChip(
                              avatar: const Icon(Icons.chat, size: 14, color: Colors.green),
                              label: const Text('WhatsApp', style: TextStyle(fontSize: 11)),
                              selected: current['whatsapp'] ?? true,
                              onSelected: (val) {
                                setState(() {
                                  _localPrefs[type]?['whatsapp'] = val;
                                });
                              },
                            ),
                            FilterChip(
                              avatar: const Icon(Icons.sms, size: 14, color: Colors.blue),
                              label: const Text('SMS', style: TextStyle(fontSize: 11)),
                              selected: current['sms'] ?? true,
                              onSelected: (val) {
                                setState(() {
                                  _localPrefs[type]?['sms'] = val;
                                });
                              },
                            ),
                            FilterChip(
                              avatar: const Icon(Icons.notifications_active, size: 14, color: Color(0xFFD4AF37)),
                              label: const Text('Push', style: TextStyle(fontSize: 11)),
                              selected: current['push'] ?? true,
                              onSelected: (val) {
                                setState(() {
                                  _localPrefs[type]?['push'] = val;
                                });
                              },
                            ),
                            FilterChip(
                              avatar: const Icon(Icons.email, size: 14, color: Colors.purple),
                              label: const Text('Email', style: TextStyle(fontSize: 11)),
                              selected: current['email'] ?? true,
                              onSelected: (val) {
                                setState(() {
                                  _localPrefs[type]?['email'] = val;
                                });
                              },
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              }),
              const SizedBox(height: 16),

              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0B194C),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: _saving
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.save, size: 18),
                  label: Text(_saving ? 'Saving...' : 'Save All Preferences', style: const TextStyle(fontWeight: FontWeight.w700, color: Colors.white)),
                  onPressed: _saving ? null : _saveAllPrefs,
                ),
              ),
              const SizedBox(height: 24),
            ],
          );
        },
      ),
    );
  }
}
