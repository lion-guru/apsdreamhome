import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/api_service.dart';
import '../../data/models/document_model.dart';

final documentProvider = StateNotifierProvider<DocumentNotifier, AsyncValue<List<Document>>>((ref) {
  return DocumentNotifier(ref.watch(apiServiceProvider));
});

class DocumentNotifier extends StateNotifier<AsyncValue<List<Document>>> {
  final ApiService _apiService;

  DocumentNotifier(this._apiService) : super(const AsyncValue.loading()) {
    fetchDocuments();
  }

  Future<void> fetchDocuments() async {
    try {
      state = const AsyncValue.loading();
      final data = await _apiService.getDocuments();
      final documents = data.map((json) => Document.fromJson(json)).toList();
      state = AsyncValue.data(documents);
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  Future<void> refresh() async {
    await fetchDocuments();
  }
}
