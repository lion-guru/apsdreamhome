import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
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

  static final List<Map<String, dynamic>> _mockProjects = [
    {
      'rera': 'UP/RERA/2026/00001',
      'project': 'APS Suryoday Colony',
      'builder': 'APS Dream Home Pvt. Ltd.',
      'status': 'Registered',
      'approved': '15 Jan 2026',
      'validTill': '14 Jan 2029',
      'area': '12.5 Acres',
      'units': 204,
      'address': 'Sahjanwa, Gorakhpur, UP',
    },
    {
      'rera': 'UP/RERA/2026/00002',
      'project': 'APS Braj Radha Nagar',
      'builder': 'APS Dream Home Pvt. Ltd.',
      'status': 'Registered',
      'approved': '20 Feb 2026',
      'validTill': '19 Feb 2029',
      'area': '8.2 Acres',
      'units': 156,
      'address': 'Braj, Gorakhpur, UP',
    },
    {
      'rera': 'UP/RERA/2026/00003',
      'project': 'APS Raghunath Nagri',
      'builder': 'APS Dream Home Pvt. Ltd.',
      'status': 'Registered',
      'approved': '10 Mar 2026',
      'validTill': '09 Mar 2029',
      'area': '6.8 Acres',
      'units': 128,
      'address': 'Raghunathpur, Gorakhpur, UP',
    },
    {
      'rera': 'UP/RERA/2025/00100',
      'project': 'Green Valley Estate',
      'builder': 'Green Developers Ltd.',
      'status': 'Registered',
      'approved': '05 Jun 2025',
      'validTill': '04 Jun 2028',
      'area': '15.0 Acres',
      'units': 280,
      'address': 'Lucknow, UP',
    },
  ];

  @override
  void dispose() {
    _reraController.dispose();
    super.dispose();
  }

  void _search() {
    final query = _reraController.text.trim();
    if (query.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Enter a RERA number'),
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

    Future.delayed(const Duration(milliseconds: 800), () {
      final match = _mockProjects
          .where(
            (p) => (p['rera'] as String).toLowerCase().contains(
              query.toLowerCase(),
            ),
          )
          .toList();

      setState(() {
        _searching = false;
        if (match.isNotEmpty) {
          _result = match.first;
        } else {
          _result = null;
        }
      });
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
            if (!_searched) ...[const SizedBox(height: 16), _buildQuickList()],
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
                hintText: 'e.g. UP/RERA/2026/00001',
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
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1565C0), Color(0xFF1976D2)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(12),
              ),
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
        ..._mockProjects.map(
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
}
