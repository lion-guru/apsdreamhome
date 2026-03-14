import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/sync_provider.dart';

class SyncIndicator extends ConsumerWidget {
  const SyncIndicator({super.key});
  
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final syncState = ref.watch(syncStateProvider);
    final connectivity = ref.watch(connectivityProvider);
    
    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.9),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (connectivity.value ?? false) ...[
            if (syncState.isSyncing) ...[
              const SizedBox(
                width: 16,
                height: 16,
                child: CircularProgressIndicator(strokeWidth: 2),
              ),
              const SizedBox(width: 8),
              const Text(
                'Syncing...',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ] else if (syncState.isPending) ...[
              Icon(
                Icons.sync_problem,
                size: 16,
                color: Colors.orange,
              ),
              const SizedBox(width: 4),
              Text(
                '${syncState.pendingCount}',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Colors.orange,
                ),
              ),
            ] else ...[
              Icon(
                Icons.cloud_done,
                size: 16,
                color: Colors.green,
              ),
              const SizedBox(width: 4),
              const Text(
                'Synced',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Colors.green,
                ),
              ),
            ],
          ] else ...[
            Icon(
              Icons.cloud_off,
              size: 16,
              color: Colors.grey,
            ),
            const SizedBox(width: 4),
            const Text(
              'Offline',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: Colors.grey,
              ),
            ),
          ],
        ],
      ),
    );
  }
}
