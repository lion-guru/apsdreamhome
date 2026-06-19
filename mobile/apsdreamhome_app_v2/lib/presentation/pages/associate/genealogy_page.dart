import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../data/repositories/mlm_repository.dart';
import '../../widgets/app_widgets.dart';

/// Genealogy Page - Connected to MlmRepository
class GenealogyPage extends ConsumerWidget {
  const GenealogyPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final genealogyAsync = ref.watch(genealogyTreeProvider(null));

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Team'),
        actions: [
          IconButton(
            onPressed: () => ref.refresh(genealogyTreeProvider(null)),
            icon: const Icon(Icons.refresh),
          ),
          PopupMenuButton<int>(
            onSelected: (value) {},
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 3,
                child: Text('Show 3 Levels'),
              ),
              const PopupMenuItem(
                value: 5,
                child: Text('Show 5 Levels'),
              ),
              const PopupMenuItem(
                value: 7,
                child: Text('Show 7 Levels'),
              ),
            ],
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async { ref.refresh(genealogyTreeProvider(null)); },
        child: genealogyAsync.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, stack) => AppWidgets.errorWidget(
            message: error.toString(),
            onRetry: () => ref.refresh(genealogyTreeProvider(null)),
          ),
          data: (genealogy) {
            final nodes = genealogy.nodes;
            if (nodes.isEmpty) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.people_outline, size: 80, color: Colors.grey.shade300),
                    const SizedBox(height: 16),
                    Text(
                      'No team members yet',
                      style: TextStyle(
                        fontSize: 18,
                        color: Colors.grey.shade600,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Start building your network',
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey.shade500,
                      ),
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: () => context.push('/team/invite'),
                      child: const Text('Invite Members'),
                    ),
                  ],
                ),
              );
            }

            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: nodes.length,
              itemBuilder: (context, index) {
                final member = nodes[index];
                return _buildMemberCard(member);
              },
            );
          },
        ),
      ),
    );
  }

  Widget _buildMemberCard(GenealogyNode member) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Member Info
            Row(
              children: [
                // Avatar
                CircleAvatar(
                  radius: 30,
                  backgroundColor: Colors.blue.shade100,
                  child: Text(
                    member.name.isNotEmpty
                        ? member.name[0].toUpperCase()
                        : '?',
                    style: const TextStyle(
                      color: Colors.blue,
                      fontWeight: FontWeight.bold,
                      fontSize: 20,
                    ),
                  ),
                ),
                
                const SizedBox(width: 16),
                
                // Name and Rank
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        member.name,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        member.rank,
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: 12),
            
            // Stats
            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    'Direct Members',
                    member.directCount.toString(),
                    Icons.people,
                    Colors.blue,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _buildStatCard(
                    'Total Team',
                    member.totalCount.toString(),
                    Icons.groups,
                    Colors.green,
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: 12),
            
            // Commission Info
            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    'Total Commission',
                    '₹${member.totalCommission.toStringAsFixed(2)}',
                    Icons.trending_up,
                    Colors.purple,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _buildStatCard(
                    'This Month',
                    '₹${member.monthlyCommission.toStringAsFixed(2)}',
                    Icons.show_chart,
                    Colors.orange,
                  ),
                ),
              ],
            ),
            
            if (member.joinedAt != null) ...[
              const SizedBox(height: 12),
              Text(
                'Joined ${_getFormattedDate(member.joinedAt)}',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade500,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 16, color: color),
              const SizedBox(width: 8),
              Text(
                title,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade700,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  String _getFormattedDate(DateTime? date) {
    if (date == null) return 'recently';
    
    final now = DateTime.now();
    final difference = now.difference(date);
    
    if (difference.inDays == 0) {
      return 'today';
    } else if (difference.inDays == 1) {
      return 'yesterday';
    } else if (difference.inDays < 7) {
      return '${difference.inDays} days ago';
    } else if (difference.inDays < 30) {
      return '${(difference.inDays / 7).floor()} weeks ago';
    } else {
      return '${(difference.inDays / 30).floor()} months ago';
    }
  }
}