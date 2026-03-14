import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../providers/mlm_provider.dart';
import '../../widgets/glass_card.dart';

final genealogyFutureProvider = FutureProvider<List<dynamic>>((ref) async {
  return await ref.read(mlmProvider.notifier).fetchGenealogy();
});

class GenealogyPage extends ConsumerWidget {
  const GenealogyPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final genealogyAsync = ref.watch(genealogyFutureProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Network Tree'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(genealogyFutureProvider),
          ),
        ],
      ),
      body: genealogyAsync.when(
        data: (data) => data.isEmpty 
            ? const Center(child: Text('No team members found yet.'))
            : ListView.builder(
                padding: const EdgeInsets.all(AppConstants.defaultPadding),
                itemCount: data.length,
                itemBuilder: (context, index) {
                  return _buildRecursiveNode(data[index], 0);
                },
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => Center(child: Text('Error: $error')),
      ),
    );
  }

  Widget _buildRecursiveNode(Map<String, dynamic> person, int depth) {
    final List<dynamic> children = person['children'] ?? [];
    
    return Column(
      children: [
        _TeamMemberTile(
          name: person['name'] ?? 'Unknown Agent',
          rank: person['rank'] ?? 'Associate',
          teamSize: person['team_size'] ?? 0,
          depth: depth,
          isMe: depth == 0 && person['parent_id'] == null,
        ),
        if (children.isNotEmpty)
          ...children.map((child) => _buildRecursiveNode(child, depth + 1)).toList(),
      ],
    );
  }
}

class _TeamMemberTile extends StatelessWidget {
  final String name;
  final String rank;
  final int teamSize;
  final int depth;
  final bool isMe;

  const _TeamMemberTile({
    required this.name,
    required this.rank,
    required this.teamSize,
    required this.depth,
    this.isMe = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.only(left: depth * 20.0, bottom: 8),
      child: GlassCard(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          child: Row(
            children: [
              if (depth > 0)
                Container(
                  width: 2,
                  height: 40,
                  color: Colors.grey.withOpacity(0.3),
                  margin: const EdgeInsets.only(right: 12),
                ),
              CircleAvatar(
                radius: 18,
                backgroundColor: isMe ? AppTheme.accentColor : AppTheme.primaryColor.withOpacity(0.1),
                child: Icon(
                  isMe ? Icons.stars : Icons.person,
                  size: 18,
                  color: isMe ? Colors.black : AppTheme.primaryColor,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: TextStyle(
                        fontWeight: isMe ? FontWeight.bold : FontWeight.w500,
                        color: Colors.white,
                      ),
                    ),
                    Text(
                      rank,
                      style: TextStyle(
                        fontSize: 12,
                        color: AppTheme.accentColor.withOpacity(0.8),
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white10,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'Team: $teamSize',
                  style: const TextStyle(fontSize: 11, color: Colors.white70),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
