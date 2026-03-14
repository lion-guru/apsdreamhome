import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:uuid/uuid.dart';
import '../../core/constants/app_constants.dart';
import '../../core/theme/app_theme.dart';
import '../../data/models/lead_model.dart';

class AddLeadSheet extends ConsumerStatefulWidget {
  const AddLeadSheet({
    super.key,
    required this.onLeadAdded,
  });
  
  final Function(Lead) onLeadAdded;
  
  @override
  ConsumerState<AddLeadSheet> createState() => _AddLeadSheetState();
}

class _AddLeadSheetState extends ConsumerState<AddLeadSheet> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _propertyController = TextEditingController();
  final _budgetController = TextEditingController();
  final _notesController = TextEditingController();
  
  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _propertyController.dispose();
    _budgetController.dispose();
    _notesController.dispose();
    super.dispose();
  }
  
  void _submitLead() {
    if (_formKey.currentState!.validate()) {
      final lead = Lead(
        leadId: const Uuid().v4(),
        name: _nameController.text.trim(),
        phone: _phoneController.text.trim(),
        email: _emailController.text.trim().isNotEmpty ? _emailController.text.trim() : null,
        propertyInterest: _propertyController.text.trim().isNotEmpty ? _propertyController.text.trim() : null,
        budget: _budgetController.text.trim().isNotEmpty ? double.tryParse(_budgetController.text.trim()) : null,
        status: 'New',
        notes: _notesController.text.trim().isNotEmpty ? _notesController.text.trim() : null,
        createdAt: DateTime.now().toIso8601String(),
        updatedAt: DateTime.now().toIso8601String(),
      );
      
      widget.onLeadAdded(lead);
      Navigator.of(context).pop();
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.9,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      builder: (context, scrollController) {
        return Container(
          decoration: BoxDecoration(
            color: Theme.of(context).scaffoldBackgroundColor,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              // Header
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  border: Border(bottom: BorderSide(color: Colors.grey.shade300)),
                ),
                child: Row(
                  children: [
                    IconButton(
                      onPressed: () => Navigator.of(context).pop(),
                      icon: const Icon(Icons.close),
                    ),
                    const Expanded(
                      child: Text(
                        'Add New Lead',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                    const SizedBox(width: 48), // Balance the close button
                  ],
                ),
              ),
              
              // Form
              Expanded(
                child: SingleChildScrollView(
                  controller: scrollController,
                  padding: const EdgeInsets.all(20),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Name Field
                        TextFormField(
                          controller: _nameController,
                          decoration: const InputDecoration(
                            labelText: 'Name *',
                            hintText: 'Enter lead name',
                            prefixIcon: Icon(Icons.person),
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Please enter lead name';
                            }
                            return null;
                          },
                        ),
                        
                        const SizedBox(height: 16),
                        
                        // Phone Field
                        TextFormField(
                          controller: _phoneController,
                          keyboardType: TextInputType.phone,
                          decoration: const InputDecoration(
                            labelText: 'Phone *',
                            hintText: 'Enter phone number',
                            prefixIcon: Icon(Icons.phone),
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Please enter phone number';
                            }
                            if (!RegExp(r'^[0-9]{10}$').hasMatch(value.trim())) {
                              return 'Please enter a valid 10-digit phone number';
                            }
                            return null;
                          },
                        ),
                        
                        const SizedBox(height: 16),
                        
                        // Email Field
                        TextFormField(
                          controller: _emailController,
                          keyboardType: TextInputType.emailAddress,
                          decoration: const InputDecoration(
                            labelText: 'Email',
                            hintText: 'Enter email address',
                            prefixIcon: Icon(Icons.email),
                          ),
                          validator: (value) {
                            if (value != null && value.trim().isNotEmpty) {
                              if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value.trim())) {
                                return 'Please enter a valid email';
                              }
                            }
                            return null;
                          },
                        ),
                        
                        const SizedBox(height: 16),
                        
                        // Property Interest Field
                        TextFormField(
                          controller: _propertyController,
                          decoration: const InputDecoration(
                            labelText: 'Property Interest',
                            hintText: 'e.g., 3 BHK Apartment, Residential Plot',
                            prefixIcon: Icon(Icons.apartment),
                          ),
                        ),
                        
                        const SizedBox(height: 16),
                        
                        // Budget Field
                        TextFormField(
                          controller: _budgetController,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(
                            labelText: 'Budget',
                            hintText: 'Enter budget amount',
                            prefixIcon: Icon(Icons.currency_rupee),
                          ),
                          validator: (value) {
                            if (value != null && value.trim().isNotEmpty) {
                              if (double.tryParse(value.trim()) == null) {
                                return 'Please enter a valid budget amount';
                              }
                            }
                            return null;
                          },
                        ),
                        
                        const SizedBox(height: 16),
                        
                        // Notes Field
                        TextFormField(
                          controller: _notesController,
                          maxLines: 3,
                          decoration: const InputDecoration(
                            labelText: 'Notes',
                            hintText: 'Additional notes about the lead',
                            prefixIcon: Icon(Icons.note),
                          ),
                        ),
                        
                        const SizedBox(height: 32),
                        
                        // Submit Button
                        SizedBox(
                          width: double.infinity,
                          height: 48,
                          child: ElevatedButton(
                            onPressed: _submitLead,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppTheme.primaryColor,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                            child: const Text(
                              'Add Lead',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ),
                        
                        const SizedBox(height: 32),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class LeadDetailsSheet extends StatelessWidget {
  const LeadDetailsSheet({
    super.key,
    required this.lead,
  });
  
  final Lead lead;
  
  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.7,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      builder: (context, scrollController) {
        return Container(
          decoration: BoxDecoration(
            color: Theme.of(context).scaffoldBackgroundColor,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: CustomScrollView(
            controller: scrollController,
            slivers: [
              SliverAppBar(
                pinned: true,
                title: Text(lead.name),
                actions: [
                  IconButton(
                    onPressed: () => Navigator.of(context).pop(),
                    icon: const Icon(Icons.close),
                  ),
                ],
              ),
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Status Badge
                      StatusBadge(
                        status: lead.status,
                        color: _getStatusColor(lead.status),
                      ),
                      
                      const SizedBox(height: 20),
                      
                      // Contact Information
                      _buildSectionTitle('Contact Information'),
                      const SizedBox(height: 12),
                      _buildDetailRow(
                        context,
                        'Phone',
                        lead.phone,
                        Icons.phone,
                        onTap: () => _callLead(lead.phone),
                      ),
                      if (lead.email != null)
                        _buildDetailRow(
                          context,
                          'Email',
                          lead.email!,
                          Icons.email,
                        ),
                      
                      const SizedBox(height: 20),
                      
                      // Property Information
                      if (lead.propertyInterest != null || lead.budget != null) ...[
                        _buildSectionTitle('Property Information'),
                        const SizedBox(height: 12),
                        if (lead.propertyInterest != null)
                          _buildDetailRow(
                            context,
                            'Interested In',
                            lead.propertyInterest!,
                            Icons.apartment,
                          ),
                        if (lead.budget != null)
                          _buildDetailRow(
                            context,
                            'Budget',
                            lead.formattedBudget,
                            Icons.monetization_on,
                          ),
                        const SizedBox(height: 20),
                      ],
                      
                      // Notes
                      if (lead.notes != null && lead.notes!.isNotEmpty) ...[
                        _buildSectionTitle('Notes'),
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade50,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.grey.shade200),
                          ),
                          child: Text(
                            lead.notes!,
                            style: Theme.of(context).textTheme.bodyMedium,
                          ),
                        ),
                        const SizedBox(height: 20),
                      ],
                      
                      // Timestamps
                      _buildSectionTitle('Timeline'),
                      const SizedBox(height: 12),
                      _buildDetailRow(
                        context,
                        'Created',
                        _formatDate(lead.createdAt),
                        Icons.calendar_today,
                      ),
                      _buildDetailRow(
                        context,
                        'Last Updated',
                        _formatDate(lead.updatedAt),
                        Icons.update,
                      ),
                      
                      const SizedBox(height: 20),
                      
                      // Actions
                      Row(
                        children: [
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: () => _callLead(lead.phone),
                              icon: const Icon(Icons.call),
                              label: const Text('Call'),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: () => _whatsappLead(lead.phone),
                              icon: const Icon(Icons.message),
                              label: const Text('WhatsApp'),
                            ),
                          ),
                        ],
                      ),
                      
                      const SizedBox(height: 20),
                    ],
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
  
  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: const TextStyle(
        fontSize: 18,
        fontWeight: FontWeight.bold,
      ),
    );
  }
  
  Widget _buildDetailRow(
    BuildContext context,
    String label,
    String value,
    IconData icon, {
    VoidCallback? onTap,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(8),
        child: Row(
          children: [
            Icon(icon, size: 20, color: Colors.grey.shade600),
            const SizedBox(width: 12),
            Text(
              '$label:',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Colors.grey.shade600,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                value,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            if (onTap != null) ...[
              Icon(
                Icons.arrow_forward_ios,
                size: 16,
                color: Colors.grey.shade400,
              ),
            ],
          ],
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
  
  String _formatDate(String dateString) {
    final date = DateTime.parse(dateString);
    return '${date.day}/${date.month}/${date.year} ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
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
