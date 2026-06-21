import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../data/repositories/lead_repository.dart';
import '../../../data/repositories/auth_repository.dart';
import '../../../data/models/lead_model.dart';
import '../../widgets/app_widgets.dart';

/// Leads Page - Connected to Repository
class LeadsPage extends ConsumerStatefulWidget {
  const LeadsPage({super.key});

  @override
  ConsumerState<LeadsPage> createState() => _LeadsPageState();
}

class _LeadsPageState extends ConsumerState<LeadsPage> with TickerProviderStateMixin {
  late TabController _tabController;
  final _searchController = TextEditingController();
  bool _isSearching = false;
  String _selectedStatus = 'all';
  String _selectedSource = 'all';
  String _selectedPriority = 'all';
  
  final List<String> _statuses = [
    'all', 'new', 'contacted', 'interested', 'visited', 'closed', 'lost'
  ];
  
  final List<String> _sources = [
    'all', 'website', 'phone', 'email', 'referral', 'social', 'campaign'
  ];
  
  final List<String> _priorities = [
    'all', 'high', 'medium', 'low'
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 6, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    ref.watch(currentUserProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: _isSearching
            ? TextField(
                controller: _searchController,
                decoration: const InputDecoration(
                  hintText: 'Search leads...',
                  border: InputBorder.none,
                ),
                onChanged: (value) => setState(() {}),
              )
            : const Text('My Leads'),
        actions: [
          IconButton(
            onPressed: () {
              setState(() => _isSearching = !_isSearching);
              if (!_isSearching) {
                _searchController.clear();
              }
            },
            icon: Icon(_isSearching ? Icons.close : Icons.search),
          ),
          IconButton(
            onPressed: _showFilterOptions,
            icon: const Icon(Icons.filter_list),
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          tabs: const [
            Tab(text: 'All'),
            Tab(text: 'New'),
            Tab(text: 'Contacted'),
            Tab(text: 'Interested'),
            Tab(text: 'Visited'),
            Tab(text: 'Closed'),
            Tab(text: 'Lost'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Filter Chips
          _buildFilterChips(),
          
          // Leads List
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildLeadsList('all'),
                _buildLeadsList('new'),
                _buildLeadsList('contacted'),
                _buildLeadsList('interested'),
                _buildLeadsList('visited'),
                _buildLeadsList('closed'),
                _buildLeadsList('lost'),
              ],
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => context.push('/leads/add'),
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildFilterChips() {
    return Container(
      height: 60,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: 3,
        itemBuilder: (context, index) {
          List<String> options;
          
          switch (index) {
            case 0:
              options = _statuses;
              break;
            case 1:
              options = _sources;
              break;
            case 2:
              options = _priorities;
              break;
            default:
              options = ['all'];
          }
          
          return Container(
            margin: const EdgeInsets.only(right: 8),
            child: Wrap(
              spacing: 4,
              children: options.map((option) {
                final isSelected = _getSelectedFilter(index, option);
                
                return FilterChip(
                  label: Text(option.toUpperCase()),
                  selected: isSelected,
                  onSelected: (selected) {
                    setState(() {
                      _setSelectedFilter(index, option);
                    });
                  },
                  backgroundColor: Colors.grey.shade200,
                  selectedColor: Colors.blue.shade100,
                  labelStyle: TextStyle(
                    color: isSelected ? Colors.blue.shade700 : Colors.grey.shade700,
                    fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  ),
                );
              }).toList(),
            ),
          );
        },
      ),
    );
  }

  bool _getSelectedFilter(int filterIndex, String option) {
    switch (filterIndex) {
      case 0:
        return _selectedStatus == option;
      case 1:
        return _selectedSource == option;
      case 2:
        return _selectedPriority == option;
      default:
        return false;
    }
  }

  void _setSelectedFilter(int filterIndex, String option) {
    switch (filterIndex) {
      case 0:
        _selectedStatus = option;
        break;
      case 1:
        _selectedSource = option;
        break;
      case 2:
        _selectedPriority = option;
        break;
    }
  }

  Widget _buildLeadsList(String status) {
    final filters = {
      'status': status == 'all' ? null : status,
      'source': _selectedSource == 'all' ? null : _selectedSource,
      'priority': _selectedPriority == 'all' ? null : _selectedPriority,
      'search': _searchController.text.isNotEmpty ? _searchController.text : null,
    };

    return RefreshIndicator(
      onRefresh: () async { ref.refresh(myLeadsProvider(filters)); }, // ignore: unused_result
      child: Consumer(
        builder: (context, ref, child) {
          final leadsAsync = ref.watch(myLeadsProvider(filters));
          
          return leadsAsync.when(
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (error, stack) => AppWidgets.errorWidget(
              message: error.toString(),
              onRetry: () => ref.refresh(myLeadsProvider(filters)),
            ),
            data: (leads) {
              if (leads.isEmpty) {
                return Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.people_outline, size: 80, color: Colors.grey.shade300),
                      const SizedBox(height: 16),
                      Text(
                        'No $status leads',
                        style: TextStyle(
                          fontSize: 18,
                          color: Colors.grey.shade600,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Try adjusting your filters',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                );
              }

              return ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: leads.length,
                itemBuilder: (context, index) {
                  final lead = leads[index];
                  return _buildLeadCard(lead);
                },
              );
            },
          );
        },
      ),
    );
  }

  Widget _buildLeadCard(LeadModel lead) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: () => context.push('/leads/${lead.id}'),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Lead Name and Status
              Row(
                children: [
                  Expanded(
                    child: Text(
                      lead.name,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 8),
                  _buildStatusBadge(lead.status ?? 'new'),
                ],
              ),
              
              const SizedBox(height: 8),
              
              // Contact Info
              Row(
                children: [
                  Icon(Icons.phone, size: 16, color: Colors.grey.shade600),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      lead.phone,
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ),
                  if ((lead.email ?? '').isNotEmpty) ...[
                    const SizedBox(width: 8),
                    Icon(Icons.email, size: 16, color: Colors.grey.shade600),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        lead.email ?? '',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
              
              if ((lead.interestedIn ?? '').isNotEmpty) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.home, size: 16, color: Colors.grey.shade600),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        'Interested in: ${lead.interestedIn}',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
              
              // Budget and Priority
              const SizedBox(height: 8),
              Row(
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Budget',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade500,
                        ),
                      ),
                      Text(
                        lead.displayBudget ?? 'Not specified',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.green.shade700,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(width: 16),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Priority',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade500,
                        ),
                      ),
                      const SizedBox(height: 4),
                      _buildPriorityBadge(lead.priority ?? 'low'),
                    ],
                  ),
                ],
              ),
              
              // Created Date and Actions
              const SizedBox(height: 12),
              Row(
                children: [
                  Text(
                    'Created ${_getFormattedDate(lead.createdAt)}',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade500,
                    ),
                  ),
                  const Spacer(),
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        onPressed: () => _callLead(lead.phone),
                        icon: const Icon(Icons.call, size: 20),
                        color: Colors.green,
                      ),
                      IconButton(
                        onPressed: () => _emailLead(lead.email ?? ''),
                        icon: const Icon(Icons.email, size: 20),
                        color: Colors.blue,
                      ),
                      IconButton(
                        onPressed: () => context.push('/leads/${lead.id}/edit'),
                        icon: const Icon(Icons.edit, size: 20),
                        color: Colors.orange,
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

  Widget _buildStatusBadge(String status) {
    Color color;
    String text;
    
    switch (status.toLowerCase()) {
      case 'new':
        color = Colors.blue;
        text = 'New';
        break;
      case 'contacted':
        color = Colors.orange;
        text = 'Contacted';
        break;
      case 'interested':
        color = Colors.purple;
        text = 'Interested';
        break;
      case 'visited':
        color = Colors.indigo;
        text = 'Visited';
        break;
      case 'closed':
        color = Colors.green;
        text = 'Closed';
        break;
      case 'lost':
        color = Colors.red;
        text = 'Lost';
        break;
      default:
        color = Colors.grey;
        text = status;
    }
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 10,
          color: color,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  Widget _buildPriorityBadge(String priority) {
    Color color;
    
    switch (priority.toLowerCase()) {
      case 'high':
        color = Colors.red;
        break;
      case 'medium':
        color = Colors.orange;
        break;
      case 'low':
        color = Colors.yellow;
        break;
      default:
        color = Colors.grey;
    }
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        priority.toUpperCase(),
        style: TextStyle(
          fontSize: 10,
          color: color,
          fontWeight: FontWeight.w600,
        ),
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

  void _showFilterOptions() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => DraggableScrollableSheet(
          initialChildSize: 0.7,
          maxChildSize: 0.9,
          minChildSize: 0.5,
          builder: (context, scrollController) => Container(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Handle
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                
                const SizedBox(height: 20),
                
                // Title
                const Text(
                  'Filter Leads',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                
                const SizedBox(height: 24),
                
                Expanded(
                  child: ListView(
                    controller: scrollController,
                    children: [
                      // Status Filter
                      const Text(
                        'Status',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Wrap(
                        spacing: 8,
                        children: _statuses.map((status) => FilterChip(
                          label: Text(status.toUpperCase()),
                          selected: _selectedStatus == status,
                          onSelected: (selected) {
                            setModalState(() {
                              _selectedStatus = selected ? status : 'all';
                            });
                            setState(() {
                              _selectedStatus = selected ? status : 'all';
                            });
                          },
                        )).toList(),
                      ),
                      
                      const SizedBox(height: 24),
                      
                      // Source Filter
                      const Text(
                        'Source',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Wrap(
                        spacing: 8,
                        children: _sources.map((source) => FilterChip(
                          label: Text(source.toUpperCase()),
                          selected: _selectedSource == source,
                          onSelected: (selected) {
                            setModalState(() {
                              _selectedSource = selected ? source : 'all';
                            });
                            setState(() {
                              _selectedSource = selected ? source : 'all';
                            });
                          },
                        )).toList(),
                      ),
                      
                      const SizedBox(height: 24),
                      
                      // Priority Filter
                      const Text(
                        'Priority',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Wrap(
                        spacing: 8,
                        children: _priorities.map((priority) => FilterChip(
                          label: Text(priority.toUpperCase()),
                          selected: _selectedPriority == priority,
                          onSelected: (selected) {
                            setModalState(() {
                              _selectedPriority = selected ? priority : 'all';
                            });
                            setState(() {
                              _selectedPriority = selected ? priority : 'all';
                            });
                          },
                        )).toList(),
                      ),
                      
                      const SizedBox(height: 24),
                      
                      // Apply Button
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () => Navigator.of(context).pop(),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            backgroundColor: Colors.blue,
                            foregroundColor: Colors.white,
                          ),
                          child: const Text('Apply Filters'),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _callLead(String phone) {
    // Launch phone dialer
    AppWidgets.showSuccessSnackBar(context, 'Calling $phone...');
    // In a real app, you would use url_launcher or similar
  }

  void _emailLead(String email) {
    // Launch email app
    AppWidgets.showSuccessSnackBar(context, 'Opening email app...');
    // In a real app, you would use url_launcher or similar
  }
}