import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class ConstructionServicesPage extends StatefulWidget {
  const ConstructionServicesPage({super.key});

  @override
  State<ConstructionServicesPage> createState() => _ConstructionServicesPageState();
}

class _ConstructionServicesPageState extends State<ConstructionServicesPage> {
  List<Map<String, dynamic>> _services = [];
  List<Map<String, dynamic>> _projects = [];
  bool _loading = true;
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _locationController = TextEditingController();
  final _budgetController = TextEditingController();
  final _messageController = TextEditingController();
  String _selectedType = 'residential';
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      AppConstants.initBaseUrl();
      final baseUrl = AppConstants.baseUrl;

      final results = await Future.wait([
        http.get(Uri.parse('$baseUrl/api/v2/mobile/construction/services')).timeout(const Duration(seconds: 10)),
        http.get(Uri.parse('$baseUrl/api/v2/mobile/construction/projects')).timeout(const Duration(seconds: 10)),
      ]);

      List<Map<String, dynamic>> services = [];
      List<Map<String, dynamic>> projects = [];

      if (results[0].statusCode == 200) {
        final data = jsonDecode(results[0].body);
        if (data['success'] == true && data['data'] is List) {
          services = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      if (results[1].statusCode == 200) {
        final data = jsonDecode(results[1].body);
        if (data['success'] == true && data['data'] is List) {
          projects = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      if (mounted) {
        setState(() {
          _services = services;
          _projects = projects;
          _loading = false;
        });
      }
      return;
    } catch (_) {}

    // Fallback to mock data
    if (mounted) {
      setState(() {
        _services = _mockServices;
        _projects = _mockProjects;
        _loading = false;
      });
    }
  }

  Future<void> _submitInquiry() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _submitting = true);

    try {
      AppConstants.initBaseUrl();
      final response = await http
          .post(
            Uri.parse('${AppConstants.baseUrl}/construction-services/inquiry'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({
              'name': _nameController.text.trim(),
              'phone': _phoneController.text.trim(),
              'email': _emailController.text.trim(),
              'project_type': _selectedType,
              'budget': _budgetController.text.trim(),
              'location': _locationController.text.trim(),
              'message': _messageController.text.trim(),
            }),
          )
          .timeout(const Duration(seconds: 15));

      if (mounted) {
        setState(() => _submitting = false);
        final data = jsonDecode(response.body);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text((data['message'] ?? 'Inquiry submitted successfully!').toString()),
            backgroundColor: AppTheme.successColor,
          ),
        );
        _formKey.currentState!.reset();
        _nameController.clear();
        _phoneController.clear();
        _emailController.clear();
        _locationController.clear();
        _budgetController.clear();
        _messageController.clear();
      }
    } catch (_) {
      if (mounted) {
        setState(() => _submitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Failed to submit. Please call 7007444842 directly.'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _locationController.dispose();
    _budgetController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        colors: const [Color(0xFF0D9488), Color(0xFF14B8A6), Color(0xFF00695C)],
        child: SafeArea(
          child: _loading
              ? const Center(child: CircularProgressIndicator(color: Colors.white))
              : SingleChildScrollView(
                  child: Column(
                    children: [
                      _buildHero(),
                      _buildServicesSection(),
                      _buildProcessSection(),
                      _buildProjectsSection(),
                      _buildContactForm(),
                      _buildFooterCTA(),
                      const SizedBox(height: 40),
                    ],
                  ),
                ),
        ),
      ),
    );
  }

  Widget _buildHero() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 40, 20, 30),
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
          const SizedBox(height: 20),
          Container(
            width: 90,
            height: 90,
            decoration: BoxDecoration(
              gradient: const LinearGradient(colors: [Color(0xFF0D9488), Color(0xFF14B8A6)]),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0D9488).withValues(alpha: 0.3),
                  blurRadius: 24,
                  offset: const Offset(0, 10),
                ),
              ],
            ),
            child: const Icon(Icons.construction_rounded, size: 44, color: Colors.white),
          ),
          const SizedBox(height: 20),
          ShaderMask(
            shaderCallback: (bounds) => const LinearGradient(
              colors: [Colors.white, Color(0xFF5EEAD4)],
            ).createShader(bounds),
            child: Text(
              'Construction Services',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
                height: 1.1,
              ),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 10),
          Text(
            'ISO-certified construction — residential, commercial & turnkey solutions',
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.white70),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _statChip('50+', 'Projects'),
              const SizedBox(width: 12),
              _statChip('15+', 'Years Exp'),
              const SizedBox(width: 12),
              _statChip('1000+', 'Clients'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _statChip(String value, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 18)),
          Text(label, style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11)),
        ],
      ),
    );
  }

  Widget _buildServicesSection() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Our Services', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text('Expert construction services tailored to your needs', style: TextStyle(color: Colors.white70, fontSize: 14)),
          const SizedBox(height: 20),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 0.95,
              crossAxisSpacing: 14,
              mainAxisSpacing: 14,
            ),
            itemCount: _services.length,
            itemBuilder: (context, index) {
              final svc = _services[index];
              final colors = [
                const Color(0xFF0D9488),
                const Color(0xFFFF6F00),
                const Color(0xFF00C853),
                const Color(0xFFD32F2F),
                const Color(0xFF2979FF),
                const Color(0xFF6A1B9A),
              ];
              final color = colors[index % colors.length];
              final icon = _getIcon(svc['icon']?.toString() ?? '');

              return GlassCard(
                padding: const EdgeInsets.all(16),
                opacity: 0.1,
                blur: 8,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        color: color.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Icon(icon, color: color, size: 26),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      svc['title']?.toString() ?? 'Service',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 14),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      svc['description']?.toString() ?? '',
                      style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 10),
                    if ((svc['features'] as List?)?.isNotEmpty == true)
                      Wrap(
                        spacing: 6,
                        runSpacing: 4,
                        alignment: WrapAlignment.center,
                        children: (svc['features'] as List).take(3).map((f) {
                          return Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: color.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              f.toString(),
                              style: TextStyle(color: color, fontSize: 9, fontWeight: FontWeight.w500),
                            ),
                          );
                        }).toList(),
                      ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildProcessSection() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Our Process', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text('Streamlined construction from concept to completion', style: TextStyle(color: Colors.white70, fontSize: 14)),
          const SizedBox(height: 20),
          Row(
            children: [
              _processStep('1', 'Consultation', 'Understand requirements & site analysis'),
              _processStep('2', 'Planning', 'Design, approvals & budget finalization'),
              _processStep('3', 'Execution', 'Quality construction with supervision'),
              _processStep('4', 'Handover', 'Final inspection & documentation'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _processStep(String num, String title, String desc) {
    return Expanded(
      child: Column(
        children: [
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              gradient: const LinearGradient(colors: [Color(0xFF0D9488), Color(0xFF14B8A6)]),
              borderRadius: BorderRadius.circular(25),
            ),
            child: Center(child: Text(num, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 18))),
          ),
          const SizedBox(height: 10),
          Text(title, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13)),
          const SizedBox(height: 4),
          Text(desc, style: TextStyle(color: Colors.white54, fontSize: 10), textAlign: TextAlign.center, maxLines: 2),
        ],
      ),
    );
  }

  Widget _buildProjectsSection() {
    if (_projects.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Recent Projects', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text('Showcasing our completed construction projects', style: TextStyle(color: Colors.white70, fontSize: 14)),
          const SizedBox(height: 20),
          SizedBox(
            height: 220,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _projects.length,
              separatorBuilder: (_, _) => const SizedBox(width: 14),
              itemBuilder: (context, index) {
                final p = _projects[index];
                final imgUrl = p['image']?.toString() ?? '';
                final isUrl = imgUrl.startsWith('http');

                return GlassCard(
                  width: 280,
                  padding: EdgeInsets.zero,
                  opacity: 0.1,
                  blur: 8,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      ClipRRect(
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                        child: isUrl
                            ? Image.network(imgUrl, height: 140, width: double.infinity, fit: BoxFit.cover,
                                errorBuilder: (_, _, _) => _projectPlaceholder())
                            : _projectPlaceholder(),
                      ),
                      Padding(
                        padding: const EdgeInsets.all(14),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: (p['status'] == 'completed' ? AppTheme.successColor : AppTheme.warningColor).withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                (p['status']?.toString() ?? 'In Progress').toUpperCase(),
                                style: TextStyle(
                                  color: p['status'] == 'completed' ? AppTheme.successColor : AppTheme.warningColor,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              p['site_name']?.toString() ?? 'Construction Project',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 14),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              p['location']?.toString() ?? '',
                              style: TextStyle(color: Colors.white54, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _projectPlaceholder() {
    return Container(
      height: 140,
      width: double.infinity,
      color: Colors.grey.shade800,
      child: const Center(child: Icon(Icons.construction_rounded, size: 40, color: Colors.white30)),
    );
  }

  Widget _buildContactForm() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
      child: GlassCard(
        padding: const EdgeInsets.all(20),
        opacity: 0.12,
        blur: 12,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Get a Free Quote', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            Text('Fill the form and our team will contact you within 24 hours', style: TextStyle(color: Colors.white70, fontSize: 13)),
            const SizedBox(height: 20),
            Form(
              key: _formKey,
              child: Column(
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: _buildField(
                          controller: _nameController,
                          label: 'Full Name *',
                          icon: Icons.person_outline_rounded,
                          validator: (v) => v?.trim().isEmpty == true ? 'Required' : null,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildField(
                          controller: _phoneController,
                          label: 'Phone *',
                          icon: Icons.phone_outlined,
                          keyboardType: TextInputType.phone,
                          validator: (v) => v?.trim().isEmpty == true ? 'Required' : null,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Expanded(
                        child: _buildField(
                          controller: _emailController,
                          label: 'Email',
                          icon: Icons.email_outlined,
                          keyboardType: TextInputType.emailAddress,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildField(
                          controller: _locationController,
                          label: 'Location *',
                          icon: Icons.location_on_outlined,
                          validator: (v) => v?.trim().isEmpty == true ? 'Required' : null,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Expanded(
                        child: _buildDropdown(),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildField(
                          controller: _budgetController,
                          label: 'Budget (₹)',
                          icon: Icons.currency_rupee_rounded,
                          keyboardType: TextInputType.number,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  _buildField(
                    controller: _messageController,
                    label: 'Project Details',
                    icon: Icons.description_outlined,
                    maxLines: 4,
                    validator: (v) => v?.trim().isEmpty == true ? 'Required' : null,
                  ),
                  const SizedBox(height: 20),
                  SizedBox(
                    width: double.infinity,
                    height: 52,
                    child: ElevatedButton(
                      onPressed: _submitting ? null : _submitInquiry,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.accentColor,
                        foregroundColor: AppTheme.primaryColor,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                      child: _submitting
                          ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primaryColor))
                          : const Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.send_rounded, size: 20),
                                SizedBox(width: 8),
                                Text('Submit Inquiry', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
                              ],
                            ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    int maxLines = 1,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      maxLines: maxLines,
      validator: validator,
      style: const TextStyle(color: Colors.white),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.white.withValues(alpha: 0.6)),
        prefixIcon: Icon(icon, color: Colors.white.withValues(alpha: 0.5), size: 20),
        filled: true,
        fillColor: Colors.white.withValues(alpha: 0.05),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.1))),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.1))),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppTheme.accentColor, width: 1.5)),
        errorBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppTheme.errorColor)),
      ),
    );
  }

  Widget _buildDropdown() {
    return DropdownButtonFormField<String>(
      value: _selectedType,
      style: const TextStyle(color: Colors.white),
      dropdownColor: const Color(0xFF1A1A2E),
      icon: Icon(Icons.arrow_drop_down_rounded, color: Colors.white.withValues(alpha: 0.5)),
      decoration: InputDecoration(
        labelText: 'Project Type *',
        labelStyle: TextStyle(color: Colors.white.withValues(alpha: 0.6)),
        prefixIcon: Icon(Icons.category_outlined, color: Colors.white.withValues(alpha: 0.5), size: 20),
        filled: true,
        fillColor: Colors.white.withValues(alpha: 0.05),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.1))),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.1))),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppTheme.accentColor, width: 1.5)),
      ),
      items: [
        'residential',
        'commercial',
        'renovation',
        'infrastructure',
        'turnkey',
      ].map((type) {
        return DropdownMenuItem(
          value: type,
          child: Text(type.toUpperCase(), style: const TextStyle(color: Colors.white)),
        );
      }).toList(),
      onChanged: (v) => setState(() => _selectedType = v!),
      validator: (v) => v == null ? 'Required' : null,
    );
  }

  Widget _buildFooterCTA() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: GlassCard(
        padding: const EdgeInsets.all(20),
        opacity: 0.15,
        blur: 12,
        child: Column(
          children: [
            Row(
              children: [
                Expanded(
                  child: _contactItem(Icons.phone_rounded, 'Call Us', '7007444842', () => _launchPhone()),
                ),
                Container(width: 1, height: 40, color: Colors.white.withValues(alpha: 0.1)),
                Expanded(
                  child: _contactItem(Icons.email_rounded, 'Email', 'info@apsdreamhome.com', () => _launchEmail()),
                ),
                Container(width: 1, height: 40, color: Colors.white.withValues(alpha: 0.1)),
                Expanded(
                  child: _contactItem(Icons.location_on_rounded, 'Office', 'Gorakhpur, UP', null),
                ),
              ],
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: () => _launchPhone(),
                icon: const Icon(Icons.phone_rounded, size: 20),
                label: const Text('Call Now', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.warningColor,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _contactItem(IconData icon, String title, String subtitle, VoidCallback? onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Column(
          children: [
            Icon(icon, color: AppTheme.accentColor, size: 24),
            const SizedBox(height: 6),
            Text(title, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 12)),
            Text(subtitle, style: TextStyle(color: Colors.white54, fontSize: 10)),
          ],
        ),
      ),
    );
  }

  Future<void> _launchPhone() async {
    await launchUrl(Uri.parse('tel:7007444842'));
  }

  Future<void> _launchEmail() async {
    await launchUrl(Uri.parse('mailto:info@apsdreamhome.com'));
  }

  IconData _getIcon(String name) {
    switch (name.toLowerCase()) {
      case 'fas fa-home': return Icons.home_rounded;
      case 'fas fa-building': return Icons.apartment_rounded;
      case 'fas fa-drafting-compass': return Icons.architecture_rounded;
      case 'fas fa-road': return Icons.alt_route_rounded;
      case 'fas fa-tools': return Icons.handyman_rounded;
      case 'fas fa-handshake': return Icons.handshake_rounded;
      default: return Icons.construction_rounded;
    }
  }

  static const _mockServices = [
    {'title': 'Residential Construction', 'icon': 'fas fa-home', 'description': 'Custom homes, villas, apartments & renovations', 'features': ['Custom Design', 'Villa Construction', 'Apartment Complexes', 'Home Renovation']},
    {'title': 'Commercial Construction', 'icon': 'fas fa-building', 'description': 'Office buildings, retail spaces & industrial units', 'features': ['Office Buildings', 'Retail Spaces', 'Shopping Complexes', 'Industrial Units']},
    {'title': 'Architectural & Design', 'icon': 'fas fa-drafting-compass', 'description': 'Architectural plans, 3D visualization & structural design', 'features': ['Architectural Plans', '3D Visualization', 'Structural Design', 'Vastu Consultation']},
    {'title': 'Infrastructure Development', 'icon': 'fas fa-road', 'description': 'Roads, drainage, water supply & community facilities', 'features': ['Road Construction', 'Drainage Systems', 'Water Supply', 'Community Centers']},
    {'title': 'Renovation & Remodeling', 'icon': 'fas fa-tools', 'description': 'Home, office & structural renovation services', 'features': ['Home Renovation', 'Office Renovation', 'Structural Repair', 'Waterproofing']},
    {'title': 'Turnkey Solutions', 'icon': 'fas fa-handshake', 'description': 'End-to-end project management from design to handover', 'features': ['Project Management', 'Material Procurement', 'Labour Management', 'Quality Assurance']},
  ];

  static const _mockProjects = [
    {'site_name': 'Suryoday Residential Complex', 'location': 'Gorakhpur, UP', 'status': 'completed', 'image': 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=600&h=400&q=80'},
    {'site_name': 'Braj Radha Commercial Plaza', 'location': 'Gorakhpur, UP', 'status': 'completed', 'image': 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&h=400&q=80'},
    {'site_name': 'Raghunath Nagri Township', 'location': 'Gorakhpur, UP', 'status': 'in_progress', 'image': 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=600&h=400&q=80'},
    {'site_name': 'Budh Bihar Affordable Housing', 'location': 'Gorakhpur, UP', 'status': 'in_progress', 'image': 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=600&h=400&q=80'},
  ];
}