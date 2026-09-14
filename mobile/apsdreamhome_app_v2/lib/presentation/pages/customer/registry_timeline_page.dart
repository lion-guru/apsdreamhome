import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final _registryProvider = FutureProvider.family<Map<String, dynamic>, String>((ref, bookingId) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  return api.getRegistryTimeline(bookingId);
});

class RegistryTimelinePage extends ConsumerWidget {
  final String bookingId;
  const RegistryTimelinePage({super.key, required this.bookingId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(_registryProvider(bookingId));
    return Scaffold(
      appBar: AppBar(
        title: const Text('Registry Journey'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: async.when(
        data: (data) => _buildBody(context, ref, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => _buildError(context, ref, e.toString()),
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref, Map<String, dynamic> data) {
    final booking = (data['booking'] as Map<String, dynamic>?) ?? {};
    final stages = (data['stages'] as List<dynamic>?) ?? [];
    final canDownload = data['can_download_certificate'] == true || data['can_download'] == true;
    final progress = (data['progress_pct'] as num?)?.toDouble() ?? 0;
    final completed = (data['completed_count'] as num?)?.toInt() ?? 0;
    final plotNo = booking['plot_number']?.toString() ?? '-';
    final colony = booking['colony_name']?.toString() ?? '-';
    final bookingNo = booking['booking_number']?.toString() ?? '#$bookingId';

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(_registryProvider(bookingId)),
      color: AppTheme.primaryColor,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildHero(plotNo, colony, bookingNo, progress, completed, stages.length),
          const SizedBox(height: 16),
          if ((booking['appointment_date'] ?? '') != '' && booking['appointment_date'] != null)
            _buildAppointmentCard(booking),
          _buildStepper(stages),
          const SizedBox(height: 16),
          _buildCertificateCard(context, canDownload),
        ],
      ),
    );
  }

  Widget _buildHero(String plotNo, String colony, String bookingNo, double pct, int done, int total) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [Color(0xFF0a192f), Color(0xFF1e3a5f)]),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(12)),
            child: const Icon(Icons.verified_outlined, color: Colors.white),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Plot $plotNo • $colony', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
              Text('Booking $bookingNo', style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 12)),
            ]),
          ),
        ]),
        const SizedBox(height: 14),
        ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: LinearProgressIndicator(value: (pct.clamp(0, 100)) / 100, minHeight: 8, backgroundColor: Colors.white24, valueColor: const AlwaysStoppedAnimation(Color(0xFF22c55e))),
        ),
        const SizedBox(height: 6),
        Text('$done of $total stages completed (${pct.toStringAsFixed(1)}%)', style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 12)),
      ]),
    );
  }

  Widget _buildAppointmentCard(Map<String, dynamic> booking) {
    final venue = booking['sub_registrar_office']?.toString() ?? 'Sub-Registrar Office';
    final dt = booking['appointment_date']?.toString() ?? '';
    return GlassCard(
      opacity: 0.08,
      blur: 8,
      padding: const EdgeInsets.all(16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: const [Icon(Icons.event_available, color: AppTheme.successColor), SizedBox(width: 8), Text('Sub-Registrar Appointment', style: TextStyle(fontWeight: FontWeight.w700))]),
        const SizedBox(height: 8),
        Text('When: $dt', style: const TextStyle(fontSize: 13)),
        Text('Venue: $venue', style: const TextStyle(fontSize: 13)),
        const SizedBox(height: 10),
        const Text('Documents to carry:', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 12)),
        const SizedBox(height: 4),
        for (final d in const [
          'Original allotment letter + receipts',
          'Agreement for Sale (signed)',
          'Aadhaar + PAN (buyer + co-buyer)',
          'Passport photos (4 each)',
          'NOC copy (if applicable)',
          'Stamp duty challan / receipt',
        ])
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 2),
            child: Row(children: [
              Container(width: 8, height: 8, decoration: const BoxDecoration(color: AppTheme.successColor, shape: BoxShape.circle)),
              const SizedBox(width: 8),
              Expanded(child: Text(d, style: const TextStyle(fontSize: 12))),
            ]),
          ),
      ]),
    );
  }

  Widget _buildStepper(List<dynamic> stages) {
    return GlassCard(
      opacity: 0.08,
      blur: 8,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: const [Icon(Icons.route, color: AppTheme.primaryColor), SizedBox(width: 8), Text('Live Progress', style: TextStyle(fontWeight: FontWeight.w700))]),
          const SizedBox(height: 12),
          for (int i = 0; i < stages.length; i++) _buildStep(i, stages[i] as Map<String, dynamic>, i == stages.length - 1),
        ],
      ),
    );
  }

  Widget _buildStep(int index, Map<String, dynamic> s, bool last) {
    final status = s['status']?.toString() ?? 'pending';
    final label = s['label']?.toString() ?? s['key']?.toString() ?? 'Stage ${index + 1}';
    final isDone = status == 'completed';
    final isCurrent = status == 'in_progress';
    final Color dotColor = isDone ? const Color(0xFF22c55e) : isCurrent ? AppTheme.primaryColor : Colors.grey.shade300;
    final IconData dotIcon = isDone ? Icons.check : isCurrent ? Icons.sync : Icons.schedule;
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(color: dotColor, shape: BoxShape.circle),
            child: Icon(dotIcon, color: Colors.white, size: 18),
          ),
          if (!last) Container(width: 2, height: 28, color: isDone ? const Color(0xFF22c55e) : Colors.grey.shade200),
        ]),
        const SizedBox(width: 12),
        Expanded(
          child: Container(
            margin: EdgeInsets.only(bottom: last ? 0 : 14),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: isDone ? const Color(0xFFbbf7d0) : isCurrent ? const Color(0xFF5eead4) : Colors.grey.shade200),
            ),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                Text('${index + 1}.', style: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.w600)),
                const SizedBox(width: 6),
                Expanded(child: Text(label, style: const TextStyle(fontWeight: FontWeight.w700))),
                _badge(status),
              ]),
              if ((s['appointment_date'] ?? '') != '' || (s['venue'] ?? '') != '')
                Padding(
                  padding: const EdgeInsets.only(top: 4),
                  child: Text(
                    [if ((s['appointment_date'] ?? '') != '') 'On: ${s['appointment_date']}', if ((s['venue'] ?? '') != '') 'Venue: ${s['venue']}'].join(' • '),
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                  ),
                ),
            ]),
          ),
        ),
      ],
    );
  }

  Widget _badge(String status) {
    final label = status == 'completed' ? 'Done' : status == 'in_progress' ? 'In Progress' : 'Pending';
    final bg = status == 'completed' ? const Color(0xFFdcfce7) : status == 'in_progress' ? const Color(0xFFccfbf1) : Colors.grey.shade200;
    final fg = status == 'completed' ? const Color(0xFF166534) : status == 'in_progress' ? const Color(0xFF0f766e) : Colors.grey.shade600;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(label, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: fg)),
    );
  }

  Widget _buildCertificateCard(BuildContext context, bool canDownload) {
    return GlassCard(
      opacity: 0.08,
      blur: 8,
      padding: const EdgeInsets.all(16),
      child: Column(children: [
        Row(mainAxisAlignment: MainAxisAlignment.center, children: const [Icon(Icons.workspace_premium, color: Color(0xFFd4af37)), SizedBox(width: 8), Text('Possession Certificate', style: TextStyle(fontWeight: FontWeight.w700))]),
        const SizedBox(height: 8),
        Text(
          canDownload ? 'Your handover is complete. Download the official certificate.' : 'Unlocks after Stage 7 (Mutation & Possession) is completed.',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
        ),
        const SizedBox(height: 12),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: canDownload ? () => _downloadCertificate(context) : null,
            icon: Icon(canDownload ? Icons.download : Icons.lock),
            label: Text(canDownload ? 'Download Possession Certificate' : 'Locked until handover'),
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white, disabledBackgroundColor: Colors.grey.shade300),
          ),
        ),
      ]),
    );
  }

  Future<void> _downloadCertificate(BuildContext context) async {
    final url = ApiService().possessionCertificateUrl(bookingId);
    final uri = Uri.parse(url);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Open: $url')));
      }
    } catch (e) {
      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Download failed: $e')));
    }
  }

  Widget _buildError(BuildContext context, WidgetRef ref, String error) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Icon(Icons.error_outline, size: 56, color: Colors.grey.shade400),
          const SizedBox(height: 12),
          Text(error, textAlign: TextAlign.center),
          const SizedBox(height: 12),
          ElevatedButton(onPressed: () => ref.invalidate(_registryProvider(bookingId)), child: const Text('Retry')),
        ]),
      ),
    );
  }
}
