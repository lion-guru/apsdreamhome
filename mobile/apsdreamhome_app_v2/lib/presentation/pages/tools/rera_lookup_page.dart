import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:share_plus/share_plus.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';

class ReraLookupPage extends StatefulWidget {
  const ReraLookupPage({super.key});

  @override
  State<ReraLookupPage> createState() => _ReraLookupPageState();
}

class _ReraLookupPageState extends State<ReraLookupPage> {
  final _reraController = TextEditingController();
  Map<String, dynamic>? _result;
  bool _searching = false;
  bool _searched = false;
  List<Map<String, dynamic>> _projects = [];
  bool _loadingProjects = true;

  @override
  void initState() {
    super.initState();
    _loadProjects();
  }

  Future<void> _loadProjects() async {
    try {
      AppConstants.initBaseUrl();
      final url = '${AppConstants.baseUrl}/api/v2/mobile/rera/projects';
      final resp = await http
          .get(Uri.parse(url))
          .timeout(const Duration(seconds: 10));
      if (resp.statusCode == 200) {
        final data = jsonDecode(resp.body);
        if (data['success'] == true && data['data'] is List) {
          setState(() {
            _projects = (data['data'] as List).cast<Map<String, dynamic>>();
            _loadingProjects = false;
          });
          return;
        }
      }
    } catch (_) {}
    setState(() {
      _projects = _mockProjects;
      _loadingProjects = false;
    });
  }

  @override
  void dispose() {
    _reraController.dispose();
    super.dispose();
  }

  Future<void> _search() async {
    final query = _reraController.text.trim();
    if (query.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Enter a RERA number'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() {
      _searching = true;
      _searched = true;
    });

    // Try API first
    try {
      AppConstants.initBaseUrl();
      final url =
          '${AppConstants.baseUrl}/api/v2/mobile/rera/verify/${Uri.encodeComponent(query)}';
      final resp = await http
          .get(Uri.parse(url))
          .timeout(const Duration(seconds: 10));
      if (resp.statusCode == 200) {
        final data = jsonDecode(resp.body);
        if (data['success'] == true && data['data'] is Map) {
          final project = data['data'] as Map<String, dynamic>;
          setState(() {
            _searching = false;
            _result = {
              'rera': project['rera_number'] ?? query,
              'project': project['project_name'] ?? 'Unknown',
              'builder': project['builder_name'] ?? '',
              'status': project['status'] ?? 'Unknown',
              'approved': project['registration_date'] ?? '',
              'validTill': project['valid_upto'] ?? '',
              'area': project['total_area'] ?? '',
              'units': project['total_units'] ?? 0,
              'address': '${project['address'] ?? ''} ${project['city'] ?? ''}'
                  .trim(),
            };
          });
          return;
        }
      }
    } catch (_) {}

    // Fallback to local search
    await Future.delayed(const Duration(milliseconds: 500));
    final match = _projects
        .where(
          (p) =>
              (p['rera'] as String).toLowerCase().contains(query.toLowerCase()),
        )
        .toList();

    setState(() {
      _searching = false;
      _result = match.isNotEmpty ? match.first : null;
    });
  }

  void _share() {
    if (_result == null) return;
    Share.share(
      'RERA Lookup Result\n'
      'Project: ${_result!['project']}\n'
      'RERA: ${_result!['rera']}\n'
      'Status: ${_result!['status']}\n'
      'Builder: ${_result!['builder']}',
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('RERA Lookup'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          _result != null
              ? IconButton(icon: const Icon(Icons.share), onPressed: _share)
              : const SizedBox(),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 24),
            _buildSearchBox(),
            const SizedBox(height: 24),
            if (_searching)
              const Center(
                child: CircularProgressIndicator(color: AppTheme.primaryColor),
              ),
            if (_searched && !_searching && _result == null) _buildNotFound(),
            if (_result != null && !_searching) _buildResult(),
            if (!_searched) ...[
              const SizedBox(height: 16),
              if (_loadingProjects)
                const Center(child: CircularProgressIndicator())
              else
                _buildQuickList(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1565C0), Color(0xFF42A5F5)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.verified_rounded, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'RERA Compliance Lookup',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Verify RERA registration status',
            style: TextStyle(
              fontSize: 13,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBox() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _reraController,
              decoration: InputDecoration(
                labelText: 'Enter RERA Number',
                hintText: 'e.g. UPRERAPRJ12345',
                prefixIcon: const Icon(Icons.search_rounded),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 14,
                ),
              ),
              textInputAction: TextInputAction.search,
              onSubmitted: (_) => _search(),
            ),
          ),
          const SizedBox(width: 12),
          GestureDetector(
            onTap: _search,
            child: Container(
              width: 50,
              height: 50,
              decoration: BoxDecoration(
                color: AppTheme.primaryColor,
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(Icons.search_rounded, color: Colors.white),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNotFound() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.red.shade200),
      ),
      child: Column(
        children: [
          Icon(
            Icons.error_outline_rounded,
            size: 48,
            color: Colors.red.shade300,
          ),
          const SizedBox(height: 12),
          const Text(
            'No Record Found',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'No project found for "${_reraController.text.trim()}". Verify the number and try again.',
            style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildResult() {
    final p = _result!;
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 15,
          ),
        ],
      ),
      child: Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFF1565C0), Color(0xFF1976D2)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.vertical(top: Radius.circular(12)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.greenAccent.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Icon(
                    Icons.check_circle_rounded,
                    color: Colors.greenAccent,
                    size: 28,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        p['project'].toString(),
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        p['rera'].toString(),
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.white.withValues(alpha: 0.8),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                _detailRow(
                  'Status',
                  p['status'].toString(),
                  Colors.greenAccent.shade700,
                ),
                _detailRow('Builder', p['builder'].toString(), Colors.black87),
                _detailRow(
                  'Approved',
                  p['approved'].toString(),
                  Colors.black87,
                ),
                _detailRow(
                  'Valid Till',
                  p['validTill'].toString(),
                  Colors.black87,
                ),
                _detailRow('Total Area', p['area'].toString(), Colors.black87),
                _detailRow('Total Units', '${p['units']}', Colors.black87),
                _detailRow(
                  'Address',
                  p['address'].toString(),
                  Colors.grey.shade700,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _detailRow(String label, String value, Color valueColor) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
            child: Text(
              label,
              style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                color: valueColor,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickList() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Our Registered Projects',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: Colors.grey.shade800,
          ),
        ),
        const SizedBox(height: 12),
        ..._projects.map(
          (p) => Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.04),
                  blurRadius: 8,
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  width: 6,
                  height: 50,
                  decoration: BoxDecoration(
                    color: Colors.greenAccent.shade700,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        p['project'].toString(),
                        style: const TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 14,
                        ),
                      ),
                      Text(
                        p['rera'].toString(),
                        style: TextStyle(
                          color: Colors.grey.shade600,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.green.shade50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.green.shade200),
                  ),
                  child: Text(
                    p['status'].toString(),
                    style: TextStyle(
                      color: Colors.green.shade700,
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  static final List<Map<String, dynamic>> _mockProjects = [
    {
      'rera': 'UPRERAPRJ12345',
      'project': 'APS Suryoday Colony',
      'builder': 'APS Dream Home Developers',
      'status': 'Registered',
    },
    {
      'rera': 'UPRERAPRJ67890',
      'project': 'APS Braj Radha Nagar',
      'builder': 'APS Dream Home Developers',
      'status': 'Registered',
    },
    {
      'rera': 'UPRERAPRJ11111',
      'project': 'APS Raghunath Nagri',
      'builder': 'APS Dream Home Developers',
      'status': 'Registered',
    },
    {
      'rera': 'UPRERAPRJ22222',
      'project': 'Green Valley Estate',
      'builder': 'Green Developers Ltd.',
      'status': 'Registered',
    },
  ];
}
