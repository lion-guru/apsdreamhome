import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class DocumentGalleryPage extends StatefulWidget {
  const DocumentGalleryPage({super.key});

  @override
  State<DocumentGalleryPage> createState() => _DocumentGalleryPageState();
}

class _DocumentGalleryPageState extends State<DocumentGalleryPage> {
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _documents = [];
  bool _loading = true;
  String _selectedCategory = '';
  final _searchController = TextEditingController();

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
        http.get(Uri.parse('$baseUrl/api/v2/mobile/documents/categories')).timeout(const Duration(seconds: 10)),
        http.get(Uri.parse('$baseUrl/api/v2/mobile/documents')).timeout(const Duration(seconds: 10)),
      ]);

      List<Map<String, dynamic>> categories = [];
      List<Map<String, dynamic>> documents = [];

      if (results[0].statusCode == 200) {
        final data = jsonDecode(results[0].body);
        if (data['success'] == true && data['data'] is List) {
          categories = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      if (results[1].statusCode == 200) {
        final data = jsonDecode(results[1].body);
        if (data['success'] == true && data['data'] is List) {
          documents = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      if (mounted) {
        setState(() {
          _categories = categories;
          _documents = documents;
          _loading = false;
        });
      }
      return;
    } catch (_) {}

    if (mounted) {
      setState(() {
        _categories = _mockCategories;
        _documents = _mockDocuments;
        _loading = false;
      });
    }
  }

  List<Map<String, dynamic>> get _filteredDocuments {
    var docs = _documents;
    if (_selectedCategory.isNotEmpty) {
      docs = docs.where((d) => (d['document_type'] ?? d['category'] ?? '').toString().toLowerCase() == _selectedCategory.toLowerCase()).toList();
    }
    if (_searchController.text.trim().isNotEmpty) {
      final query = _searchController.text.trim().toLowerCase();
      docs = docs.where((d) =>
        (d['document_number'] ?? d['document_type'] ?? '').toString().toLowerCase().contains(query) ||
        (d['issued_by'] ?? '').toString().toLowerCase().contains(query) ||
        (d['document_type'] ?? '').toString().toLowerCase().contains(query)
      ).toList();
    }
    return docs;
  }

  @override
  void dispose() {
    _searchController.dispose();
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
              : Column(
                  children: [
                    _buildHeader(),
                    _buildFilters(),
                    Expanded(
                      child: _filteredDocuments.isEmpty
                          ? _buildEmptyState()
                          : _buildDocumentsGrid(),
                    ),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
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
          const SizedBox(height: 10),
          ShaderMask(
            shaderCallback: (bounds) => const LinearGradient(
              colors: [Colors.white, Color(0xFF5EEAD4)],
            ).createShader(bounds),
            child: Text(
              'Document Gallery',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Browse and download verified documents',
            style: TextStyle(color: Colors.white70, fontSize: 14),
          ),
        ],
      ),
    );
  }

  Widget _buildFilters() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 10),
      child: Column(
        children: [
          GlassCard(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            opacity: 0.1,
            blur: 8,
            child: Row(
              children: [
                Icon(Icons.search_rounded, color: Colors.white.withValues(alpha: 0.7), size: 22),
                const SizedBox(width: 12),
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    style: const TextStyle(color: Colors.white),
                    decoration: InputDecoration(
                      hintText: 'Search documents...',
                      hintStyle: TextStyle(color: Colors.white.withValues(alpha: 0.5)),
                      border: InputBorder.none,
                    ),
                    onChanged: (_) => setState(() {}),
                  ),
                ),
                if (_searchController.text.isNotEmpty)
                  IconButton(
                    icon: Icon(Icons.clear_rounded, color: Colors.white.withValues(alpha: 0.5), size: 20),
                    onPressed: () {
                      _searchController.clear();
                      setState(() {});
                    },
                  ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          if (_categories.isNotEmpty)
            SizedBox(
              height: 40,
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 20),
                children: [
                  _buildCategoryChip('All', ''),
                  ..._categories.map((cat) => _buildCategoryChip(
                    (cat['category'] ?? cat['name'] ?? '').toString(),
                    (cat['category'] ?? cat['name'] ?? '').toString(),
                  )),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildCategoryChip(String label, String value) {
    final isSelected = _selectedCategory == value;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label, style: TextStyle(
          color: isSelected ? AppTheme.primaryColor : Colors.white,
          fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
          fontSize: 12,
        )),
        selected: isSelected,
        onSelected: (_) => setState(() => _selectedCategory = value),
        backgroundColor: Colors.white.withValues(alpha: 0.08),
        selectedColor: AppTheme.accentColor.withValues(alpha: 0.3),
        checkmarkColor: Colors.white,
        side: BorderSide(
          color: isSelected ? AppTheme.accentColor : Colors.white.withValues(alpha: 0.2),
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      ),
    );
  }

  Widget _buildDocumentsGrid() {
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
      itemCount: _filteredDocuments.length,
      itemBuilder: (context, index) {
        final doc = _filteredDocuments[index];
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: _buildDocumentCard(doc),
        );
      },
    );
  }

  Widget _buildDocumentCard(Map<String, dynamic> doc) {
    final fileType = (doc['document_type'] ?? doc['type'] ?? 'pdf').toString().toLowerCase();
    final iconData = _getFileIcon(fileType);
    final iconColor = _getFileColor(fileType);
    final status = (doc['verification_status'] ?? doc['status'] ?? 'pending').toString().toLowerCase();
    final statusColor = _getStatusColor(status);
    final statusLabel = status[0].toUpperCase() + status.substring(1);

    final docNumber = doc['document_number'] ?? doc['document_type'] ?? 'Document';
    final issuedBy = doc['issued_by'] ?? '';
    final issueDate = doc['issue_date'] ?? '';
    final expiryDate = doc['expiry_date'] ?? '';
    final docUrl = doc['url'] ?? '';

    final List<Widget> leftColumnChildren = [
      Text(
        docNumber.toString(),
        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 14),
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
      ),
      const SizedBox(height: 4),
      Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
            decoration: BoxDecoration(
              color: iconColor.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Text(
              fileType.toUpperCase(),
              style: TextStyle(color: iconColor, fontSize: 9, fontWeight: FontWeight.w600),
            ),
          ),
          if (issuedBy.toString().isNotEmpty) ...[
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                issuedBy.toString(),
                style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ],
      ),
    ];

    if (issueDate.toString().isNotEmpty) {
      leftColumnChildren.add(const SizedBox(height: 4));
      leftColumnChildren.add(
        Row(
          children: [
            Icon(Icons.calendar_today_rounded, size: 11, color: Colors.white.withValues(alpha: 0.5)),
            const SizedBox(width: 4),
            Text(
              'Issued: $issueDate',
              style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
            ),
            if (expiryDate.toString().isNotEmpty) ...[
              const SizedBox(width: 12),
              Text(
                'Expires: $expiryDate',
                style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
              ),
            ],
          ],
        ),
      );
    }

    final List<Widget> rightColumnChildren = [
      Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: statusColor.withValues(alpha: 0.2),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(
          statusLabel,
          style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.w600),
        ),
      ),
    ];

    if (docUrl.toString().isNotEmpty) {
      rightColumnChildren.add(const SizedBox(height: 8));
      rightColumnChildren.add(
        IconButton(
          onPressed: () => _launchUrl(docUrl.toString()),
          icon: Icon(Icons.open_in_new_rounded, color: AppTheme.accentColor, size: 20),
          tooltip: 'View Document',
        ),
      );
    }

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Row(
        children: [
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: iconColor.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(iconData, color: iconColor, size: 24),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: leftColumnChildren,
            ),
          ),
          const SizedBox(width: 12),
          Column(children: rightColumnChildren),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.folder_open_rounded, size: 64, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          Text(
            _searchController.text.isNotEmpty || _selectedCategory.isNotEmpty
                ? 'No documents found'
                : 'No documents available',
            style: TextStyle(color: Colors.grey.shade400, fontSize: 16),
          ),
          const SizedBox(height: 8),
          Text(
            _searchController.text.isNotEmpty || _selectedCategory.isNotEmpty
                ? 'Try adjusting your search or filters'
                : 'Documents will appear here when available',
            style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
          ),
          if (_searchController.text.isNotEmpty || _selectedCategory.isNotEmpty) ...[
            const SizedBox(height: 16),
            TextButton(
              onPressed: () {
                _searchController.clear();
                setState(() => _selectedCategory = '');
              },
              child: const Text('Clear Filters', style: TextStyle(color: AppTheme.accentColor)),
            ),
          ],
        ],
      ),
    );
  }

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url.startsWith('http') ? url : '${AppConstants.baseUrl}$url');
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  IconData _getFileIcon(String type) {
    switch (type) {
      case 'pdf': return Icons.picture_as_pdf_rounded;
      case 'doc':
      case 'docx': return Icons.description_rounded;
      case 'xls':
      case 'xlsx': return Icons.table_chart_rounded;
      case 'jpg':
      case 'jpeg':
      case 'png': return Icons.image_rounded;
      default: return Icons.insert_drive_file_rounded;
    }
  }

  Color _getFileColor(String type) {
    switch (type) {
      case 'pdf': return const Color(0xFFEF5350);
      case 'doc':
      case 'docx': return const Color(0xFF4285F4);
      case 'xls':
      case 'xlsx': return const Color(0xFF34A853);
      case 'jpg':
      case 'jpeg':
      case 'png': return const Color(0xFF00BCD4);
      default: return Colors.grey;
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'verified': return AppTheme.successColor;
      case 'pending': return AppTheme.warningColor;
      case 'rejected': return AppTheme.errorColor;
      case 'expired': return Colors.grey;
      default: return Colors.grey;
    }
  }

  static const _mockCategories = [
    {'category': 'legal'},
    {'category': 'registration'},
    {'category': 'approval'},
    {'category': 'compliance'},
    {'category': 'financial'},
  ];

  static const _mockDocuments = [
    {
      'document_number': 'REG-2026-001',
      'document_type': 'pdf',
      'category': 'registration',
      'issued_by': 'UP RERA',
      'issue_date': '2026-01-15',
      'expiry_date': '2031-01-14',
      'verification_status': 'verified',
      'url': '/documents/REG-2026-001.pdf',
    },
    {
      'document_number': 'APP-2026-045',
      'document_type': 'pdf',
      'category': 'approval',
      'issued_by': 'Gorakhpur Development Authority',
      'issue_date': '2026-03-20',
      'expiry_date': '2029-03-19',
      'verification_status': 'verified',
      'url': '/documents/APP-2026-045.pdf',
    },
    {
      'document_number': 'LGL-2026-012',
      'document_type': 'docx',
      'category': 'legal',
      'issued_by': 'Legal Department',
      'issue_date': '2026-02-10',
      'expiry_date': '',
      'verification_status': 'pending',
      'url': '/documents/LGL-2026-012.docx',
    },
    {
      'document_number': 'CMP-2026-008',
      'document_type': 'pdf',
      'category': 'compliance',
      'issued_by': 'Environmental Board',
      'issue_date': '2026-04-05',
      'expiry_date': '2027-04-04',
      'verification_status': 'verified',
      'url': '/documents/CMP-2026-008.pdf',
    },
    {
      'document_number': 'FIN-2026-023',
      'document_type': 'xlsx',
      'category': 'financial',
      'issued_by': 'Finance Department',
      'issue_date': '2026-05-01',
      'expiry_date': '2027-03-31',
      'verification_status': 'verified',
      'url': '/documents/FIN-2026-023.xlsx',
    },
    {
      'document_number': 'APP-2025-089',
      'document_type': 'pdf',
      'category': 'approval',
      'issued_by': 'Municipal Corporation',
      'issue_date': '2025-11-12',
      'expiry_date': '2026-11-11',
      'verification_status': 'expired',
      'url': '/documents/APP-2025-089.pdf',
    },
  ];
}