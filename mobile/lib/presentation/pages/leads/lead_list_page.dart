import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_constants.dart';
import '../../core/theme/app_theme.dart';
import '../../data/models/lead_model.dart';
import '../providers/lead_provider.dart';
import '../widgets/glass_card.dart';
import '../widgets/common_widgets.dart';
import '../widgets/voice_lead_dialog.dart';

class LeadListPage extends ConsumerStatefulWidget {
  const LeadListPage({super.key});
  
  @override
  ConsumerState<LeadListPage> createState() => _LeadListPageState();
}

class _LeadListPageState extends ConsumerState<LeadListPage> {
  final TextEditingController _searchController = TextEditingController();
  String _selectedStatus = 'All';
  
  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }
  
  @override
  Widget build(BuildContext context) {
    final leadsAsync = ref.watch(leadsProvider);
    final connectivity = ref.watch(connectivityProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Leads'),
        actions: [
          IconButton(
            onPressed: () => _showVoiceAssistant(),
            icon: const Icon(Icons.mic, color: Colors.blue),
            tooltip: 'Voice-to-Lead',
          ),
          IconButton(
            onPressed: connectivity.value ?? false ? _addLead : null,
            icon: const Icon(Icons.add),
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            padding: const EdgeInsets.all(AppConstants.defaultPadding),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search leads...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        onPressed: () {
                          _searchController.clear();
                          setState(() {});
                        },
                        icon: const Icon(Icons.clear),
                      )
                    : null,
              ),
              onChanged: (value) {
                setState(() {});
              },
            ),
          ),
          
          // Status Filter
          Container(
            padding: const EdgeInsets.symmetric(horizontal: AppConstants.defaultPadding),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterChip('All', _selectedStatus == 'All', (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                  }),
                  const SizedBox(width: 8),
                  _buildFilterChip('New', _selectedStatus == 'New', (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                  }),
                  const SizedBox(width: 8),
                  _buildFilterChip('Contacted', _selectedStatus == 'Contacted', (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                  }),
                  const SizedBox(width: 8),
                  _buildFilterChip('Interested', _selectedStatus == 'Interested', (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                  }),
                  const SizedBox(width: 8),
                  _buildFilterChip('Converted', _selectedStatus == 'Converted', (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                  }),
                ],
              ),
            ),
          ),
          
          const SizedBox(height: 8),
          
          // Leads List
          Expanded(
            child: leadsAsync.when(
              data: (leads) {
                final filteredLeads = _filterLeads(leads);
                
                if (filteredLeads.isEmpty) {
                  return const EmptyStateWidget(
                    title: 'No Leads Found',
                    subtitle: 'Try adjusting your filters or search terms',
                    action: Text('Add your first lead to get started'),
                  );
                }
                
                return RefreshIndicator(
                  onRefresh: () => ref.read(leadProvider.notifier).refreshLeads(),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppConstants.defaultPadding),
                    itemCount: filteredLeads.length,
                    itemBuilder: (context, index) {
                      final lead = filteredLeads[index];
                      return LeadCard(
                        lead: lead,
                        onTap: () => _showLeadDetails(lead),
                        onStatusUpdate: connectivity.value ?? false
                            ? (newStatus) => _updateLeadStatus(lead, newStatus)
                            : null,
                      );
                    },
                  ),
                );
              },
              loading: () => const Padding(
                padding: EdgeInsets.all(AppConstants.defaultPadding),
                child: Column(
                  children: [
                    ShimmerCard(height: 120),
                    SizedBox(height: 16),
                    ShimmerCard(height: 120),
                    SizedBox(height: 16),
                    ShimmerCard(height: 120),
                  ],
                ),
              ),
              error: (error, stack) => EmptyStateWidget(
                title: 'Error Loading Leads',
                subtitle: error.toString(),
                action: ElevatedButton(
                  onPressed: () => ref.read(leadProvider.notifier).refreshLeads(),
                  child: const Text('Retry'),
                ),
              ),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: connectivity.value ?? false ? _addLead : null,
        child: const Icon(Icons.add),
      ),
    );
  }
  
  Widget _buildFilterChip(String label, bool isSelected, Function(String) onSelected) {
    return FilterChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        if (selected) {
          onSelected(label);
        }
      },
      backgroundColor: Colors.grey.shade200,
      selectedColor: AppTheme.primaryColor.withOpacity(0.2),
      labelStyle: TextStyle(
        color: isSelected ? AppTheme.primaryColor : Colors.black87,
        fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
      ),
    );
  }
  
  List<Lead> _filterLeads(List<Lead> leads) {
    return leads.where((lead) {
      // Search filter
      final searchQuery = _searchController.text.toLowerCase();
      final matchesSearch = searchQuery.isEmpty ||
          lead.name.toLowerCase().contains(searchQuery) ||
          lead.phone.contains(searchQuery) ||
          (lead.email?.toLowerCase().contains(searchQuery) ?? false);
      
      // Status filter
      final matchesStatus = _selectedStatus == 'All' || lead.status == _selectedStatus;
      
      return matchesSearch && matchesStatus;
    }).toList();
  }
  
  void _showLeadDetails(Lead lead) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => LeadDetailsSheet(lead: lead),
    );
  }
  
  void _updateLeadStatus(Lead lead, String newStatus) async {
    try {
      await ref.read(leadProvider.notifier).updateLeadStatus(lead, newStatus);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Lead status updated')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}')),
        );
      }
    }
  }
  
  void _addLead() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => AddLeadSheet(
        onLeadAdded: (lead) {
          ref.read(leadProvider.notifier).addLead(lead);
        },
      ),
    );
  }

  void _showVoiceAssistant() async {
    final result = await showDialog<String>(
      context: context,
      builder: (context) => const VoiceLeadDialog(),
    );

    if (result != null && result.isNotEmpty) {
      _processVoiceResult(result);
    }
  }

  void _processVoiceResult(String text) async {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Processing lead data...')),
    );

    try {
      final apiService = ref.read(apiServiceProvider);
      // In a real flow, we would call parse-lead and then fill the add lead form
      // Here we simulate the extraction for the demo
      
      _addLead(); // Open add lead sheet
      // Typically we'd pass the parsed data to the sheet
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: ${e.toString()}')),
      );
    }
  }
}

class LeadCard extends StatelessWidget {
  const LeadCard({
    super.key,
    required this.lead,
    required this.onTap,
    this.onStatusUpdate,
  });
  
  final Lead lead;
  final VoidCallback onTap;
  final Function(String)? onStatusUpdate;
  
  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Name and Status
              Row(
                children: [
                  Expanded(
                    child: Text(
                      lead.name,
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  StatusBadge(
                    status: lead.status,
                    color: _getStatusColor(lead.status),
                  ),
                ],
              ),
              
              const SizedBox(height: 8),
              
              // Contact Info
              Row(
                children: [
                  Icon(
                    Icons.phone_outlined,
                    size: 16,
                    color: Colors.grey.shade600,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    lead.phone,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Colors.grey.shade600,
                    ),
                  ),
                  if (lead.email != null) ...[
                        const SizedBox(width: 16),
                        Icon(
                          Icons.email_outlined,
                          size: 16,
                          color: Colors.grey.shade600,
                        ),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            lead.email!,
                            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                              color: Colors.grey.shade600,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                ],
              ),
              
              if (lead.propertyInterest != null) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(
                      Icons.apartment_outlined,
                      size: 16,
                      color: Colors.grey.shade600,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Interested in: ${lead.propertyInterest}',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ],
              
              if (lead.budget != null) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(
                      Icons.monetization_on_outlined,
                      size: 16,
                      color: Colors.grey.shade600,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Budget: ${lead.formattedBudget}',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ],
              
              const SizedBox(height: 12),
              
              // Actions
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  IconButton(
                    onPressed: () => _callLead(lead.phone),
                    icon: Icon(Icons.call, color: Colors.green.shade600),
                  ),
                  IconButton(
                    onPressed: () => _whatsappLead(lead.phone),
                    icon: Icon(Icons.message, color: Colors.green.shade600),
                  ),
                  if (onStatusUpdate != null)
                    PopupMenuButton<String>(
                      icon: Icon(Icons.more_vert, color: Colors.grey.shade600),
                      onSelected: (newStatus) {
                        onStatusUpdate!(newStatus);
                      },
                      itemBuilder: (context) => [
                        PopupMenuItem(
                          value: 'New',
                          child: Text('Mark as New'),
                        ),
                        PopupMenuItem(
                          value: 'Contacted',
                          child: Text('Mark as Contacted'),
                        ),
                        PopupMenuItem(
                          value: 'Interested',
                          child: Text('Mark as Interested'),
                        ),
                        PopupMenuItem(
                          value: 'Not Interested',
                          child: Text('Mark as Not Interested'),
                        ),
                        PopupMenuItem(
                          value: 'Converted',
                          child: Text('Mark as Converted'),
                        ),
                      ],
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
  
  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'new':
        return Colors.blue;
      case 'contacted':
        return Colors.orange;
      case 'interested':
        return Colors.green;
      case 'not interested':
        return Colors.red;
      case 'converted':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }
  
  void _callLead(String phone) async {
    final Uri phoneUri = Uri(
      scheme: 'tel',
      path: phone,
    );
    
    if (await canLaunchUrl(phoneUri)) {
      await launchUrl(phoneUri);
    }
  }
  
  void _whatsappLead(String phone) async {
    final Uri whatsappUri = Uri(
      scheme: 'https',
      host: 'wa.me',
      path: phone,
    );
    
    if (await canLaunchUrl(whatsappUri)) {
      await launchUrl(whatsappUri);
    }
  }
}
