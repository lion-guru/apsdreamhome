import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class InteriorDesignPage extends StatefulWidget {
  const InteriorDesignPage({super.key});

  @override
  State<InteriorDesignPage> createState() => _InteriorDesignPageState();
}

class _InteriorDesignPageState extends State<InteriorDesignPage> {
  List<Map<String, dynamic>> _services = [];
  List<Map<String, dynamic>> _portfolio = [];
  List<Map<String, dynamic>> _testimonials = [];
  bool _loading = true;
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _areaController = TextEditingController();
  final _messageController = TextEditingController();
  String _selectedPropertyType = 'apartment';
  String _selectedBudget = '100000';
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
        http.get(Uri.parse('$baseUrl/api/v2/mobile/interior/services')).timeout(const Duration(seconds: 10)),
        http.get(Uri.parse('$baseUrl/api/v2/mobile/interior/portfolio')).timeout(const Duration(seconds: 10)),
        http.get(Uri.parse('$baseUrl/api/v2/mobile/interior/testimonials')).timeout(const Duration(seconds: 10)),
      ]);

      List<Map<String, dynamic>> services = [];
      List<Map<String, dynamic>> portfolio = [];
      List<Map<String, dynamic>> testimonials = [];

      if (results[0].statusCode == 200) {
        final data = jsonDecode(results[0].body);
        if (data['success'] == true && data['data'] is List) {
          services = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      if (results[1].statusCode == 200) {
        final data = jsonDecode(results[1].body);
        if (data['success'] == true && data['data'] is List) {
          portfolio = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      if (results[2].statusCode == 200) {
        final data = jsonDecode(results[2].body);
        if (data['success'] == true && data['data'] is List) {
          testimonials = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      if (mounted) {
        setState(() {
          _services = services;
          _portfolio = portfolio;
          _testimonials = testimonials;
          _loading = false;
        });
      }
      return;
    } catch (_) {}

    if (mounted) {
      setState(() {
        _services = _mockServices;
        _portfolio = _mockPortfolio;
        _testimonials = _mockTestimonials;
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
            Uri.parse('${AppConstants.baseUrl}/service-interest'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({
              'name': _nameController.text.trim(),
              'phone': _phoneController.text.trim(),
              'email': _emailController.text.trim(),
              'property_type': _selectedPropertyType,
              'area': _areaController.text.trim(),
              'budget': _selectedBudget,
              'message': _messageController.text.trim(),
              'service_type': 'interior',
            }),
          )
          .timeout(const Duration(seconds: 15));

      if (mounted) {
        setState(() => _submitting = false);
        final data = jsonDecode(response.body);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text((data['message'] ?? 'Consultation request submitted!').toString()),
            backgroundColor: AppTheme.successColor,
          ),
        );
        _formKey.currentState!.reset();
        _nameController.clear();
        _phoneController.clear();
        _emailController.clear();
        _areaController.clear();
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
    _areaController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        colors: const [Color(0xFF1A237E), Color(0xFF4A148C), Color(0xFF6A1B9A)],
        child: SafeArea(
          child: _loading
              ? const Center(child: CircularProgressIndicator(color: Colors.white))
              : SingleChildScrollView(
                  child: Column(
                    children: [
                      _buildHero(),
                      _buildServicesSection(),
                      _buildToolsSection(),
                      _buildPortfolioSection(),
                      _buildTestimonialsSection(),
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
              gradient: const LinearGradient(colors: [Color(0xFF6A1B9A), Color(0xFF9C27B0)]),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF6A1B9A).withValues(alpha: 0.3),
                  blurRadius: 24,
                  offset: const Offset(0, 10),
                ),
              ],
            ),
            child: const Icon(Icons.palette_rounded, size: 44, color: Colors.white),
          ),
          const SizedBox(height: 20),
          ShaderMask(
            shaderCallback: (bounds) => const LinearGradient(
              colors: [Colors.white, Color(0xFFE1BEE7)],
            ).createShader(bounds),
            child: Text(
              'Interior Design Services',
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
            'Transform your space with award-winning designers — 200+ projects completed',
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.white70),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _statChip('200+', 'Projects'),
              const SizedBox(width: 12),
              _statChip('10+', 'Designers'),
              const SizedBox(width: 12),
              _statChip('98%', 'Satisfaction'),
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
          Text('Design Services', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text('Comprehensive interior design solutions for every space', style: TextStyle(color: Colors.white70, fontSize: 14)),
          const SizedBox(height: 20),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 0.9,
              crossAxisSpacing: 14,
              mainAxisSpacing: 14,
            ),
            itemCount: _services.length,
            itemBuilder: (context, index) {
              final svc = _services[index];
              final colors = [
                const Color(0xFF6A1B9A),
                const Color(0xFFE91E63),
                const Color(0xFF00C853),
                const Color(0xFFFF6F00),
                const Color(0xFF2979FF),
                const Color(0xFF00E676),
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
                      svc['title']?.toString() ?? 'Design Service',
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
                    const SizedBox(height: 10),
                    Text(
                      'Enquire Now →',
                      style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w600),
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

  Widget _buildToolsSection() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Free Design Tools', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text('Plan your space with our interactive calculators', style: TextStyle(color: Colors.white70, fontSize: 14)),
          const SizedBox(height: 20),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 1.1,
              crossAxisSpacing: 14,
              mainAxisSpacing: 14,
            ),
            itemCount: _designTools.length,
            itemBuilder: (context, index) {
              final tool = _designTools[index];
              return InkWell(
                  onTap: tool['onTap'] as void Function()?,
                  borderRadius: BorderRadius.circular(16),
                  child: GlassCard(
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
                            color: (tool['color'] as Color).withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Icon(tool['icon'] as IconData, color: tool['color'] as Color, size: 28),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          tool['title'] as String,
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          tool['description'] as String,
                          style: TextStyle(color: Colors.white.withValues(alpha: 0.5), fontSize: 10),
                          textAlign: TextAlign.center,
                        ),
],
                ),
              ),
            );
          },
        ),
      ],
    ),
  );
}

  Widget _buildPortfolioSection() {
    if (_portfolio.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Our Portfolio', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text('Recent interior design projects', style: TextStyle(color: Colors.white70, fontSize: 14)),
          const SizedBox(height: 20),
          SizedBox(
            height: 240,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _portfolio.length,
              separatorBuilder: (_, _) => const SizedBox(width: 14),
              itemBuilder: (context, index) {
                final item = _portfolio[index];
                final imgUrl = item['image']?.toString() ?? '';
                final isUrl = imgUrl.startsWith('http');

                return GestureDetector(
                  onTap: () => _showPortfolioDetail(item),
                  child: GlassCard(
                    width: 280,
                    padding: EdgeInsets.zero,
                    opacity: 0.1,
                    blur: 8,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        ClipRRect(
                          borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                          child: Stack(
                            children: [
                              isUrl
                                  ? Image.network(imgUrl, height: 160, width: double.infinity, fit: BoxFit.cover,
                                      errorBuilder: (_, _, _) => _portfolioPlaceholder())
                                  : _portfolioPlaceholder(),
                              Positioned(
                                bottom: 0,
                                left: 0,
                                right: 0,
                                child: Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    gradient: LinearGradient(
                                      begin: Alignment.bottomCenter,
                                      end: Alignment.topCenter,
                                      colors: [Colors.black.withValues(alpha: 0.8), Colors.transparent],
                                    ),
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        item['title']?.toString() ?? 'Design Project',
                                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                                      ),
                                      Text(
                                        item['category']?.toString() ?? '',
                                        style: TextStyle(color: Colors.white70, fontSize: 10),
                                      ),
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
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _portfolioPlaceholder() {
    return Container(
      height: 160,
      width: double.infinity,
      color: Colors.grey.shade800,
      child: const Center(child: Icon(Icons.image_rounded, size: 40, color: Colors.white30)),
    );
  }

  Widget _buildTestimonialsSection() {
    if (_testimonials.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Client Testimonials', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text('What our clients say about us', style: TextStyle(color: Colors.white70, fontSize: 14)),
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
            itemCount: _testimonials.length,
            itemBuilder: (context, index) {
              final t = _testimonials[index];
              return GlassCard(
                padding: const EdgeInsets.all(16),
                opacity: 0.1,
                blur: 8,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.format_quote_rounded, size: 28, color: Colors.white.withValues(alpha: 0.2)),
                    const SizedBox(height: 10),
                    Text(
                      t['content']?.toString() ?? t['message']?.toString() ?? '',
                      style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 13, height: 1.4),
                      maxLines: 4,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const Spacer(),
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 16,
                          backgroundColor: AppTheme.accentColor.withValues(alpha: 0.2),
                          child: Text(
                            (t['name']?.toString() ?? 'C')[0].toUpperCase(),
                            style: TextStyle(color: AppTheme.accentColor, fontWeight: FontWeight.w600, fontSize: 12),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                t['name']?.toString() ?? t['client_name']?.toString() ?? 'Client',
                                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 11),
                              ),
                              Text(
                                t['location']?.toString() ?? '',
                                style: TextStyle(color: Colors.white54, fontSize: 9),
                              ),
                            ],
                          ),
                        ),
                      ],
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
            Text('Free Design Consultation', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            Text('Book a free consultation with our expert designers', style: TextStyle(color: Colors.white70, fontSize: 13)),
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
                        child: _buildDropdown(
                          value: _selectedPropertyType,
                          label: 'Property Type *',
                          icon: Icons.home_outlined,
                          items: [
                            {'value': 'apartment', 'label': 'Apartment'},
                            {'value': 'house', 'label': 'House'},
                            {'value': 'villa', 'label': 'Villa'},
                            {'value': 'office', 'label': 'Office'},
                          ],
                          onChanged: (v) => setState(() => _selectedPropertyType = v!),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Expanded(
                        child: _buildField(
                          controller: _areaController,
                          label: 'Area (sq ft)',
                          icon: Icons.square_foot_rounded,
                          keyboardType: TextInputType.number,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildDropdown(
                          value: _selectedBudget,
                          label: 'Budget Range *',
                          icon: Icons.currency_rupee_rounded,
                          items: [
                            {'value': '50000', 'label': 'Under ₹50,000'},
                            {'value': '100000', 'label': '₹50,000 - ₹1,00,000'},
                            {'value': '200000', 'label': '₹1,00,000 - ₹2,00,000'},
                            {'value': '500000', 'label': '₹2,00,000 - ₹5,00,000'},
                            {'value': '1000000', 'label': '₹5,00,000+'},
                          ],
                          onChanged: (v) => setState(() => _selectedBudget = v!),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  _buildField(
                    controller: _messageController,
                    label: 'Requirements',
                    icon: Icons.description_outlined,
                    maxLines: 3,
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
                                Text('Book Consultation', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
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

  Widget _buildDropdown({
    required String value,
    required String label,
    required IconData icon,
    required List<Map<String, String>> items,
    required void Function(String?) onChanged,
  }) {
    return DropdownButtonFormField<String>(
      value: value,
      style: const TextStyle(color: Colors.white),
      dropdownColor: const Color(0xFF1A1A2E),
      icon: Icon(Icons.arrow_drop_down_rounded, color: Colors.white.withValues(alpha: 0.5)),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.white.withValues(alpha: 0.6)),
        prefixIcon: Icon(icon, color: Colors.white.withValues(alpha: 0.5), size: 20),
        filled: true,
        fillColor: Colors.white.withValues(alpha: 0.05),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.1))),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.1))),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppTheme.accentColor, width: 1.5)),
      ),
      items: items.map((item) {
        return DropdownMenuItem(
          value: item['value'],
          child: Text(item['label']!, style: const TextStyle(color: Colors.white)),
        );
      }).toList(),
      onChanged: onChanged,
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
                  child: _contactItem(Icons.chat_rounded, 'WhatsApp', '7007444842', () => _launchWhatsApp()),
                ),
                Container(width: 1, height: 40, color: Colors.white.withValues(alpha: 0.1)),
                Expanded(
                  child: _contactItem(Icons.location_on_rounded, 'Visit Us', 'Gorakhpur, UP', null),
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

  Future<void> _launchWhatsApp() async {
    await launchUrl(Uri.parse('https://wa.me/917007444842'));
  }

  void _showPortfolioDetail(Map<String, dynamic> item) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.7,
        decoration: const BoxDecoration(
          color: Color(0xFF1A1A2E),
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          children: [
            Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(top: 12),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(item['title']?.toString() ?? 'Project Details', style: AppTheme.titleLarge.copyWith(color: Colors.white)),
                    const SizedBox(height: 8),
                    Text(item['category']?.toString() ?? '', style: TextStyle(color: AppTheme.accentColor, fontSize: 13)),
                    const SizedBox(height: 16),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: (item['image']?.toString() ?? '').startsWith('http')
                          ? Image.network(item['image']?.toString() ?? '', height: 200, width: double.infinity, fit: BoxFit.cover)
                          : _portfolioPlaceholder(),
                    ),
                    const SizedBox(height: 16),
                    Text('Project Details', style: AppTheme.titleMedium.copyWith(color: Colors.white, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 12),
                    Text(item['description']?.toString() ?? 'No description available', style: TextStyle(color: Colors.white70, fontSize: 14, height: 1.5)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  IconData _getIcon(String name) {
    switch (name.toLowerCase()) {
      case 'fas fa-palette': return Icons.palette_rounded;
      case 'fas fa-paint-brush': return Icons.format_paint_rounded;
      case 'fas fa-couch': return Icons.chair_rounded;
      case 'fas fa-lightbulb': return Icons.lightbulb_rounded;
      case 'fas fa-ruler': return Icons.straighten_rounded;
      case 'fas fa-home': return Icons.home_rounded;
      default: return Icons.design_services_rounded;
    }
  }

  static const _mockServices = [
    {'title': 'Residential Interior', 'icon': 'fas fa-home', 'description': 'Complete home interior design & execution', 'features': ['Living Room', 'Bedroom', 'Kitchen', 'Bathroom']},
    {'title': 'Commercial Interior', 'icon': 'fas fa-building', 'description': 'Office, retail & hospitality spaces', 'features': ['Office Design', 'Retail Stores', 'Restaurants', 'Hotels']},
    {'title': 'Modular Kitchen', 'icon': 'fas fa-utensils', 'description': 'Custom modular kitchens with smart storage', 'features': ['Smart Storage', 'Premium Appliances', 'Ergonomic Design', 'Easy Maintenance']},
    {'title': 'Lighting Design', 'icon': 'fas fa-lightbulb', 'description': 'Ambient, task & accent lighting planning', 'features': ['Mood Lighting', 'Energy Efficient', 'Smart Controls', 'Architectural']},
    {'title': 'Furniture & Decor', 'icon': 'fas fa-couch', 'description': 'Custom furniture selection & procurement', 'features': ['Custom Furniture', 'Soft Furnishings', 'Art & Accessories', 'Space Planning']},
    {'title': 'Vastu-Compliant Design', 'icon': 'fas fa-compass', 'description': 'Vastu-aligned layouts for harmony & prosperity', 'features': ['Direction Analysis', 'Element Balance', 'Remedies', 'Energy Flow']},
  ];

  static const _mockPortfolio = [
    {'title': 'Modern 3BHK Apartment', 'category': 'Residential', 'image': 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=600&h=400&q=80', 'description': 'Contemporary minimalist design with neutral palette and smart storage solutions.'},
    {'title': 'Luxury Villa Interior', 'category': 'Residential', 'image': 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&h=400&q=80', 'description': 'Opulent villa with marble finishes, custom furniture, and automated lighting.'},
    {'title': 'Corporate Office Space', 'category': 'Commercial', 'image': 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=600&h=400&q=80', 'description': 'Open-plan office with collaborative zones, ergonomic furniture, and biophilic design.'},
    {'title': 'Boutique Hotel Lobby', 'category': 'Hospitality', 'image': 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&h=400&q=80', 'description': 'Grand lobby with statement lighting, local art, and welcoming reception area.'},
  ];

  static const _mockTestimonials = [
    {'name': 'Priya Sharma', 'location': 'Gorakhpur', 'message': 'The team transformed our 3BHK into a dream home. Every detail was perfect!'},
    {'name': 'Rajesh Kumar', 'location': 'Lucknow', 'message': 'Professional, creative, and on budget. Highly recommend for commercial spaces.'},
    {'name': 'Anita Singh', 'location': 'Varanasi', 'message': 'Vastu-compliant design that feels both modern and harmonious. Amazing work!'},
    {'name': 'Vikash Gupta', 'location': 'Gorakhpur', 'message': 'Modular kitchen exceeded expectations. Smart storage and beautiful finish.'},
  ];

  static final _designTools = [
    {'title': 'Cost Estimator', 'icon': Icons.calculate_rounded, 'color': Color(0xFF6A1B9A), 'description': 'Estimate interior design costs instantly', 'onTap': null},
    {'title': 'Room Planner', 'icon': Icons.straighten_rounded, 'color': Color(0xFF4CAF50), 'description': 'Plan room layouts with dimensions', 'onTap': null},
    {'title': 'Budget Planner', 'icon': Icons.pie_chart_rounded, 'color': Color(0xFFFF6F00), 'description': 'Allocate budget across design categories', 'onTap': null},
    {'title': 'Color Palette', 'icon': Icons.palette_rounded, 'color': Color(0xFF2979FF), 'description': 'Generate color schemes by room & style', 'onTap': null},
    {'title': 'Furniture Layout', 'icon': Icons.chair_rounded, 'color': Color(0xFF4CAF50), 'description': 'Plan furniture placement for any room', 'onTap': null},
    {'title': 'Material Selector', 'icon': Icons.format_paint_rounded, 'color': Color(0xFFE91E63), 'description': 'Compare materials & finishes', 'onTap': null},
  ];
}