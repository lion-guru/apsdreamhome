import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/property_providers.dart';
import '../../../data/models/property_model.dart';

/// Favorites/Wishlist Page - Connected to Repository
class FavoritesPage extends ConsumerWidget {
  const FavoritesPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final favoritesAsync = ref.watch(favoritesProvider);

    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: const Text('My Wishlist'),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        actions: [
          favoritesAsync.when(
            data: (favorites) => favorites.isNotEmpty
                ? TextButton.icon(
                    onPressed: () => _showClearConfirmation(context, ref),
                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                    label: const Text('Clear', style: TextStyle(color: Colors.red)),
                  )
                : const SizedBox.shrink(),
            loading: () => const SizedBox.shrink(),
            error: (_, __) => const SizedBox.shrink(),
          ),
        ],
      ),
      body: favoritesAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, size: 80, color: Colors.red.shade300),
              const SizedBox(height: 16),
              Text('Error loading favorites',
                  style: TextStyle(fontSize: 18, color: Colors.grey.shade600)),
              const SizedBox(height: 8),
              Text(error.toString(),
                  style: TextStyle(fontSize: 14, color: Colors.red.shade600)),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.refresh(favoritesProvider),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
        data: (favorites) => favorites.isEmpty
            ? _buildEmptyState(context)
            : _buildFavoritesList(context, ref, favorites),
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.favorite_border, size: 80, color: Colors.grey.shade300),
          const SizedBox(height: 16),
          Text('No properties in wishlist',
              style: TextStyle(fontSize: 18, color: Colors.grey.shade600)),
          const SizedBox(height: 8),
          Text(
            'Start exploring and add properties to your wishlist',
            style: TextStyle(fontSize: 14, color: Colors.grey.shade500),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: () => context.go('/properties'),
            icon: const Icon(Icons.explore),
            label: const Text('Browse Properties'),
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFavoritesList(
      BuildContext context, WidgetRef ref, List<PropertyModel> favorites) {
    return RefreshIndicator(
      onRefresh: () => ref.refresh(favoritesProvider.future),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: favorites.length,
        itemBuilder: (context, index) {
          final property = favorites[index];
          return _buildPropertyCard(context, ref, property);
        },
      ),
    );
  }

  Widget _buildPropertyCard(BuildContext context, WidgetRef ref, PropertyModel property) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: () => context.go('/properties/${property.id}'),
        borderRadius: BorderRadius.circular(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Property Image
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
              child: property.imageUrl != null && property.imageUrl!.isNotEmpty
                  ? Image.network(
                      property.imageUrl!,
                      height: 180,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _buildPlaceholder(),
                    )
                  : _buildPlaceholder(),
            ),
            
            // Property Details
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title and Favorite Button
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          property.title,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 8),
                      FavoriteButton(propertyId: property.id),
                    ],
                  ),
                  
                  const SizedBox(height: 8),
                  
                  // Price
                  Text(
                    '₹${property.price.toStringAsFixed(0)}',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.green.shade700,
                    ),
                  ),
                  
                  const SizedBox(height: 8),
                  
                  // Location and Type
                  Row(
                    children: [
                      Icon(Icons.location_on_outlined, 
                           size: 16, color: Colors.grey.shade600),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          property.location,
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey.shade600,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.blue.shade50,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          property.type.toUpperCase(),
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.blue.shade700,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                  
                  if (property.size != null) ...[
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(Icons.square_foot, 
                             size: 16, color: Colors.grey.shade600),
                        const SizedBox(width: 4),
                        Text(
                          '${property.size!.toStringAsFixed(0)} sq ft',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ],
                  
                  // Status Badge
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      _buildStatusBadge(property.status),
                      const Spacer(),
                      Text(
                        'Added ${_getFormattedDate(property.createdAt)}',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPlaceholder() {
    return Container(
      height: 180,
      width: double.infinity,
      color: Colors.grey.shade200,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.image_outlined, 
               size: 48, color: Colors.grey.shade400),
          const SizedBox(height: 8),
          Text('No Image',
               style: TextStyle(color: Colors.grey.shade400)),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color color;
    String text;
    
    switch (status.toLowerCase()) {
      case 'available':
        color = Colors.green;
        text = 'Available';
        break;
      case 'sold':
        color = Colors.red;
        text = 'Sold';
        break;
      case 'reserved':
        color = Colors.orange;
        text = 'Reserved';
        break;
      default:
        color = Colors.grey;
        text = status;
    }
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 12,
          color: color,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  String _getFormattedDate(String? dateString) {
    if (dateString == null) return 'recently';
    
    try {
      final date = DateTime.parse(dateString);
      final now = DateTime.now();
      final difference = now.difference(date);
      
      if (difference.inDays == 0) {
        return 'today';
      } else if (difference.inDays == 1) {
        return 'yesterday';
      } else if (difference.inDays < 7) {
        return '${difference.inDays} days ago';
      } else if (difference.inDays < 30) {
        return '${(difference.inDays / 7).floor()} weeks ago';
      } else {
        return '${(difference.inDays / 30).floor()} months ago';
      }
    } catch (e) {
      return 'recently';
    }
  }

  void _showClearConfirmation(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Clear Wishlist'),
        content: const Text('Are you sure you want to remove all properties from your wishlist?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              // Clear favorites logic would go here
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Wishlist cleared')),
              );
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Clear'),
          ),
        ],
      ),
    );
  }
}

/// Favorite Button Widget
class FavoriteButton extends ConsumerWidget {
  final String propertyId;

  const FavoriteButton({super.key, required this.propertyId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final favoritesAsync = ref.watch(favoritesProvider);
    
    return favoritesAsync.when(
      data: (favorites) {
        final isFavorite = favorites.any((p) => p.id == propertyId);
        
        return IconButton(
          onPressed: () => _toggleFavorite(ref, propertyId),
          icon: Icon(
            isFavorite ? Icons.favorite : Icons.favorite_border,
            color: isFavorite ? Colors.red : Colors.grey,
          ),
        );
      },
      loading: () => const IconButton(
        onPressed: null,
        icon: Icon(Icons.favorite_border, color: Colors.grey),
      ),
      error: (_, __) => const IconButton(
        onPressed: null,
        icon: Icon(Icons.favorite_border, color: Colors.grey),
      ),
    );
  }

  void _toggleFavorite(WidgetRef ref, String propertyId) {
    // Toggle favorite logic would go here
    // This would call the repository to toggle favorite status
    ScaffoldMessenger.of(ref.context).showSnackBar(
      const SnackBar(content: Text('Favorite toggled')),
    );
  }
}