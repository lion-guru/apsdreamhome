import 'package:flutter/foundation.dart';
import 'property_listing_service.dart';

class ComparisonService extends ChangeNotifier {
  static final ComparisonService _instance = ComparisonService._internal();
  factory ComparisonService() => _instance;
  ComparisonService._internal();

  final List<PropertyListing> _items = [];
  static const int maxItems = 3;

  List<PropertyListing> get items => List.unmodifiable(_items);
  int get count => _items.length;
  bool get isFull => _items.length >= maxItems;

  bool add(PropertyListing property) {
    if (_items.length >= maxItems) return false;
    if (_items.any((p) => p.id == property.id)) return false;
    _items.add(property);
    notifyListeners();
    return true;
  }

  void remove(int propertyId) {
    _items.removeWhere((p) => p.id == propertyId);
    notifyListeners();
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }

  bool contains(int propertyId) {
    return _items.any((p) => p.id == propertyId);
  }
}
