import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';

class DocumentEsignPage extends StatefulWidget {
  const DocumentEsignPage({super.key});

  @override
  State<DocumentEsignPage> createState() => _DocumentEsignPageState();
}

class _DocumentEsignPageState extends State<DocumentEsignPage> {
  final ApiService _apiService = ApiService();
  final _scrollController = ScrollController();
  final _searchController = TextEditingController();
  bool _isLoading = false;
  List<dynamic> _documents = [];
  String _searchQuery = '';
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadDocuments();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  List<dynamic> _getMockDocuments() {
    return [
      {'id': '1', 'title': 'Property Sale Agreement', 'document_type': 'sale', 'status': 'pending', 'created_at': '2025-01-15'},
      {'id': '2', 'title': 'Colony Allotment Letter', 'document_type': 'allotment', 'status': 'pending', 'created_at': '2025-01-10'},
      {'id': '3', 'title': 'Booking Confirmation', 'document_type': 'booking', 'status': 'signed', 'created_at': '2025-01-05'},
    ];
  }

  Future<void> _loadDocuments() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.request(
        method: 'GET',
        endpoint: 'document-esign',
      );

       final dynamic data = response['data'];
      final List<dynamic> docs = data is List
          ? List<dynamic>.from(data)
          : (response['documents'] is List
              ? List<dynamic>.from(response['documents'] as List)
              : <dynamic>[]);
      setState(() {
        _documents = docs;
        _isLoading = false;
        _errorMessage = null;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'No documents loaded. You can sign documents from other pages.';
        _isLoading = false;
        _documents = _getMockDocuments();
      });
    }
  }

  Widget _buildDocumentList() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

      final filteredDocs = _searchQuery.isNotEmpty
        ? _documents.where((dynamic doc) {
            final title = (doc as Map<String, dynamic>)['title']?.toString() ?? '';
            final docType = doc['document_type']?.toString() ?? '';
            return title.toLowerCase().contains(_searchQuery.toLowerCase()) ||
                docType.toLowerCase().contains(_searchQuery.toLowerCase());
          }).toList()
        : _documents;

    if (filteredDocs.isEmpty) {
      return _buildEmptyState();
    }

    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.all(16),
      itemCount: filteredDocs.length,
      itemBuilder: (context, index) {
        final doc = filteredDocs[index];
        final dynamic status = doc['status'] ?? 'pending';
        final bool isSigned = status is String && status == 'signed';

        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isSigned ? Colors.green.shade100 : Colors.orange.shade100,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    isSigned ? 'Signed' : 'Pending',
                    style: TextStyle(
                      color: isSigned ? Colors.green : Colors.orange,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        doc['title']?.toString() ?? 'Untitled',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Type: ${doc['document_type']?.toString() ?? 'N/A'}',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Created: ${doc['created_at']?.toString() ?? 'N/A'}',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                      ),
                    ],
                  ),
                ),
                ElevatedButton(
                  onPressed: isSigned
                      ? null
                      : () => _navigateToDetail(doc as Map<String, dynamic>),
                  child: isSigned ? const Text('Signed') : const Text('E-Sign Now'),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _navigateToDetail(Map<String, dynamic> doc) {
    final dynamic docId = doc['id'];
    final String idStr = docId is String ? docId : docId?.toString() ?? '';
    final int docIdInt = int.tryParse(idStr) ?? 0;

    GoRouter.of(context).go('/document-esign/$docIdInt', extra: {
      'title': doc['title']?.toString() ?? 'Document',
    });
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.description_outlined, size: 64, color: Colors.grey.shade300),
          const SizedBox(height: 16),
          const Text('No documents found', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: AppTheme.textPrimaryLight)),
          const SizedBox(height: 8),
          Text('Documents will appear here after creation', style: TextStyle(fontSize: 13, color: Colors.grey.shade500)),
          if (_errorMessage != null) ...[
            const SizedBox(height: 12),
            Text(_errorMessage!, style: const TextStyle(color: Colors.red, fontSize: 12)),
          ],
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
      child: TextField(
        controller: _searchController,
        onChanged: (value) => setState(() => _searchQuery = value),
        style: const TextStyle(fontSize: 14),
        decoration: InputDecoration(
          hintText: 'Search documents...',
          prefixIcon: const Icon(Icons.search, size: 20, color: AppTheme.primaryColor),
          suffixIcon: _searchQuery.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear, size: 18),
                  onPressed: () {
                    _searchController.clear();
                    setState(() => _searchQuery = '');
                  },
                )
              : null,
          filled: true,
          fillColor: AppTheme.surfaceColor,
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Documents')),
      body: Column(children: [_buildSearchBar(), Expanded(child: _buildDocumentList())]),
    );
  }
}