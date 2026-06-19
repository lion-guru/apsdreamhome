import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/utils/logger.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

/// AI Data Extractor
/// Allows users to query any data using natural language
class AIDataExtractor extends ConsumerStatefulWidget {
  const AIDataExtractor({super.key});

  @override
  ConsumerState<AIDataExtractor> createState() => _AIDataExtractorState();
}

class _AIDataExtractorState extends ConsumerState<AIDataExtractor> {
  final TextEditingController _queryController = TextEditingController();
  bool _isLoading = false;
  Map<String, dynamic>? _result;
  String? _explanation;

  @override
  void dispose() {
    _queryController.dispose();
    super.dispose();
  }

  Future<void> _executeQuery() async {
    final query = _queryController.text.trim();
    if (query.isEmpty) return;

    setState(() {
      _isLoading = true;
      _result = null;
      _explanation = null;
    });

    try {
      final parsedQuery = _parseQuery(query);
      final data = await _fetchData(parsedQuery);
      final explanation = _generateExplanation(query, data, parsedQuery);

      setState(() {
        _result = data;
        _explanation = explanation;
        _isLoading = false;
      });
    } catch (e) {
      AppLogger.error('AI Data Extractor error', e);
      setState(() {
        _isLoading = false;
        _explanation =
            "Sorry, I couldn't process that query. Please try rephrasing it.";
      });
    }
  }

  /// Parse natural language query
  Map<String, dynamic> _parseQuery(String query) {
    final lower = query.toLowerCase();
    final result = <String, dynamic>{
      'collection': null,
      'operation': 'get',
      'filters': <String, dynamic>{},
      'sort': null,
      'limit': 10,
    };

    if (lower.contains('plot') || lower.contains('property')) {
      result['collection'] = 'plots';
    } else if (lower.contains('colony') || lower.contains('project')) {
      result['collection'] = 'colonies';
    } else if (lower.contains('user') ||
        lower.contains('associate') ||
        lower.contains('customer')) {
      result['collection'] = 'users';
    } else if (lower.contains('booking') || lower.contains('sale')) {
      result['collection'] = 'bookings';
    } else if (lower.contains('commission')) {
      result['collection'] = 'commissions';
    } else if (lower.contains('lead')) {
      result['collection'] = 'leads';
    }

    if (lower.contains('available') || lower.contains('unsold')) {
      result['filters']['status'] = 'available';
    }
    if (lower.contains('booked') || lower.contains('sold')) {
      result['filters']['status'] = 'booked';
    }
    if (lower.contains('pending')) {
      result['filters']['status'] = 'pending';
    }

    final locations = [
      'gorakhpur',
      'lucknow',
      'varanasi',
      'kanpur',
      'kushinagar'
    ];
    for (final loc in locations) {
      if (lower.contains(loc)) {
        result['filters']['location'] = loc;
        break;
      }
    }

    if (lower.contains('under') && lower.contains('lakh')) {
      final match = RegExp(r'under\s*(\d+)\s*lakh').firstMatch(lower);
      if (match != null) {
        final amount = int.parse(match.group(1)!) * 100000;
        result['filters']['maxPrice'] = amount;
      }
    }

    if (lower.contains('cheapest') || lower.contains('lowest price')) {
      result['sort'] = {'field': 'price', 'direction': 'asc'};
    } else if (lower.contains('expensive') || lower.contains('highest price')) {
      result['sort'] = {'field': 'price', 'direction': 'desc'};
    } else if (lower.contains('newest') || lower.contains('latest')) {
      result['sort'] = {'field': 'createdAt', 'direction': 'desc'};
    }

    if (lower.contains('count') ||
        lower.contains('how many') ||
        lower.contains('total')) {
      result['operation'] = 'count';
    }
    if (lower.contains('sum') ||
        lower.contains('total amount') ||
        lower.contains('total revenue')) {
      result['operation'] = 'sum';
      result['field'] = 'price';
    }

    if (lower.contains('top') || lower.contains('best')) {
      final match = RegExp(r'(top|best)\s*(\d+)').firstMatch(lower);
      if (match != null) {
        result['limit'] = int.parse(match.group(2)!);
      } else {
        result['limit'] = 5;
      }
    }

    return result;
  }

  /// Map parsed collection to REST API endpoint + query params
  String _getEndpoint(String collection, Map<String, dynamic> filters) {
    switch (collection) {
      case 'plots':
      case 'colonies':
        return 'properties';
      case 'bookings':
        return 'bookings';
      case 'commissions':
        return 'mlm/payouts';
      case 'leads':
        return 'leads';
      case 'users':
      default:
        return 'user/profile';
    }
  }

  Map<String, dynamic> _getQueryParams(Map<String, dynamic> filters) {
    final params = <String, dynamic>{};
    if (filters.containsKey('status')) {
      params['status'] = filters['status'];
    }
    if (filters.containsKey('location')) {
      params['location'] = filters['location'];
    }
    if (filters.containsKey('maxPrice')) {
      params['max_price'] = filters['maxPrice'];
    }
    return params;
  }

  /// Fetch data via REST API (replaces Firestore Query)
  Future<Map<String, dynamic>> _fetchData(Map<String, dynamic> query) async {
    final collection = query['collection'] as String?;
    if (collection == null) {
      return {'error': 'Could not determine data type from query'};
    }

    final api = ref.read(apiServiceProvider);
    final endpoint = _getEndpoint(collection, query['filters'] as Map<String, dynamic>);
    final queryParams = _getQueryParams(query['filters'] as Map<String, dynamic>);

    final response = await api.request(
      method: 'GET',
      endpoint: endpoint,
      queryParameters: queryParams,
    );

    // Handle different response shapes from backend
    List<Map<String, dynamic>> documents;
    if (response is Map<String, dynamic>) {
      final data = response['data'];
      if (data is List) {
        documents = data.map((e) => e as Map<String, dynamic>).toList();
      } else if (data is Map<String, dynamic> && data.containsKey('properties')) {
        final props = data['properties'];
        if (props is List) {
          documents = props.map((e) => e as Map<String, dynamic>).toList();
        } else {
          documents = [];
        }
      } else {
        documents = [];
      }
    } else {
      documents = [];
    }

    final limit = query['limit'] as int;
    documents = documents.take(limit).toList();

    // Client-side sort (backend doesn't support dynamic sort params)
    final sort = query['sort'] as Map<String, dynamic>?;
    if (sort != null) {
      final field = sort['field'] as String;
      final desc = sort['direction'] == 'desc';
      documents.sort((a, b) {
        final aVal = a[field] ?? 0;
        final bVal = b[field] ?? 0;
        if (desc) {
          return (bVal is num && aVal is num)
              ? bVal.compareTo(aVal)
              : bVal.toString().compareTo(aVal.toString());
        }
        return (aVal is num && bVal is num)
            ? aVal.compareTo(bVal)
            : aVal.toString().compareTo(bVal.toString());
      });
    }

    return {
      'collection': collection,
      'count': documents.length,
      'documents': documents,
      'operation': query['operation'] as String,
    };
  }

  /// Generate natural language explanation
  String _generateExplanation(String query, Map<String, dynamic> data,
      Map<String, dynamic> parsedQuery) {
    final count = data['count'] as int;
    final collection = data['collection'] as String;

    if (count == 0) {
      return "I searched through all $collection but couldn't find any matching your criteria. Try broadening your search?";
    }

    String explanation = "Found $count ${count == 1 ? 'record' : 'records'}";

    final filters = parsedQuery['filters'] as Map<String, dynamic>;
    if (filters.isNotEmpty) {
      final filterDescriptions = <String>[];
      filters.forEach((key, value) {
        if (key == 'status') {
          filterDescriptions.add('with status "$value"');
        } else if (key == 'location') {
          filterDescriptions.add('in ${value.toString().toUpperCase()}');
        } else if (key == 'maxPrice') {
          filterDescriptions
              .add('priced under ₹${(value / 100000).toStringAsFixed(1)}L');
        }
      });
      if (filterDescriptions.isNotEmpty) {
        explanation += " ${filterDescriptions.join(', ')}";
      }
    }

    explanation += '.';

    final docs = data['documents'] as List<dynamic>;
    if (docs.isNotEmpty && collection == 'plots') {
      final prices = docs.map((d) {
        final docMap = d as Map<String, dynamic>;
        return (docMap['price'] ?? 0) as num;
      }).toList();
      final avgPrice = prices.fold<num>(0, (a, b) => a + b) / prices.length;
      explanation +=
          ' Average price is ₹${(avgPrice / 100000).toStringAsFixed(1)}L.';
    }

    return explanation;
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.all(16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF4285F4), Color(0xFF34A853)],
                    ),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.psychology, color: Colors.white),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'AI Data Extractor',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      Text(
                        'Ask anything, get data instantly',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _queryController,
              decoration: InputDecoration(
                hintText:
                    'e.g., "Show me available plots in Gorakhpur under 30 lakh"',
                hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                prefixIcon: const Icon(Icons.search),
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                suffixIcon: _isLoading
                    ? const Padding(
                        padding: EdgeInsets.all(12),
                        child: SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        ),
                      )
                    : IconButton(
                        icon: const Icon(Icons.send),
                        onPressed: _executeQuery,
                      ),
              ),
              onSubmitted: (_) => _executeQuery(),
            ),
            const SizedBox(height: 12),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildExampleChip('Available plots in Lucknow'),
                  _buildExampleChip('Top 5 associates by sales'),
                  _buildExampleChip('Total revenue this month'),
                  _buildExampleChip('Pending KYC users'),
                  _buildExampleChip('Cheapest 1000 sqft plots'),
                ],
              ),
            ),
            const SizedBox(height: 16),
            if (_explanation != null)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Icon(Icons.info_outline,
                        color: Colors.blue.shade700, size: 20),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        _explanation!,
                        style: TextStyle(color: Colors.blue.shade700),
                      ),
                    ),
                  ],
                ),
              ),
            if (_result != null && _result!['documents'] != null)
              Expanded(
                child:
                    _buildResultsList(_result!['documents'] as List<dynamic>),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildExampleChip(String text) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ActionChip(
        label: Text(text, style: const TextStyle(fontSize: 11)),
        onPressed: () {
          _queryController.text = text;
          _executeQuery();
        },
        backgroundColor: Colors.grey.shade100,
      ),
    );
  }

  Widget _buildResultsList(List<dynamic> documents) {
    return ListView.builder(
      shrinkWrap: true,
      itemCount: documents.length.clamp(0, 5),
      itemBuilder: (context, index) {
        final doc = documents[index] as Map<String, dynamic>;
        return ListTile(
          dense: true,
          leading: CircleAvatar(
            backgroundColor: Colors.blue.shade100,
            child: Text('${index + 1}'),
          ),
          title: Text(
            (doc['name'] ??
                doc['plotNumber'] ??
                doc['title'] ??
                'Item ${index + 1}') as String,
            style: const TextStyle(fontWeight: FontWeight.w500),
          ),
          subtitle: Text(
            _formatDocumentSubtitle(doc),
            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
          ),
          trailing: doc['price'] != null
              ? Text(
                  '₹${((doc['price'] as num) / 100000).toStringAsFixed(1)}L',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: Colors.green.shade700,
                  ),
                )
              : null,
        );
      },
    );
  }

  String _formatDocumentSubtitle(Map<String, dynamic> doc) {
    final parts = <String>[];

    if (doc['location'] != null) parts.add(doc['location'] as String);
    if (doc['area'] != null) parts.add('${doc['area']} sqft');
    if (doc['status'] != null) parts.add(doc['status'] as String);
    if (doc['phone'] != null) parts.add(doc['phone'] as String);

    return parts.join(' • ');
  }
}

/// AI Query Suggestions Provider
final aiQuerySuggestionsProvider = Provider<List<String>>((ref) {
  return [
    'Show me all available plots',
    'List top 10 associates by sales',
    'Total revenue this month',
    'Pending KYC verifications',
    'Bookings in Suryoday Heights',
    'Commission payouts pending',
    'Available plots under 30 lakh',
    'Site visits scheduled today',
    'Leads converted this week',
    'Colony-wise sales comparison',
  ];
});
