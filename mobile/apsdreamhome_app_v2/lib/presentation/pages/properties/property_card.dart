import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/models/property_model.dart';
import '../../widgets/status_badge.dart';

class PropertyCard extends StatelessWidget {
  const PropertyCard({
    super.key,
    required this.property,
    required this.onTap,
    this.onStatusChange,
  });

  final Property property;
  final VoidCallback onTap;
  final Function(String)? onStatusChange;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image Section
            if (property.imageUrl != null)
              ClipRRect(
                borderRadius:
                    const BorderRadius.vertical(top: Radius.circular(12)),
                child: CachedNetworkImage(
                  imageUrl: property.imageUrl!,
                  height: 180,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => Container(
                    height: 180,
                    color: Colors.grey.shade300,
                    child: const Center(
                      child: CircularProgressIndicator(),
                    ),
                  ),
                  errorWidget: (context, url, error) => Container(
                    height: 180,
                    color: Colors.grey.shade300,
                    child: const Icon(Icons.image_not_supported, size: 48),
                  ),
                ),
              ),

            // Content Section
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title and Status
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          property.title,
                          style:
                              Theme.of(context).textTheme.titleLarge?.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                        ),
                      ),
                      StatusBadge(
                        status: property.status,
                        color: _getStatusColor(property.status),
                      ),
                    ],
                  ),

                  const SizedBox(height: 8),

                  // Location
                  Row(
                    children: [
                      Icon(
                        Icons.location_on_outlined,
                        size: 16,
                        color: Colors.grey.shade600,
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          property.location,
                          style:
                              Theme.of(context).textTheme.bodyMedium?.copyWith(
                                    color: Colors.grey.shade600,
                                  ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 8),

                  // Type and Size
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppTheme.primaryColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          property.type,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppTheme.primaryColor,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      if (property.size != null) ...[
                        Icon(
                          Icons.square_foot_outlined,
                          size: 16,
                          color: Colors.grey.shade600,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          property.formattedSize,
                          style:
                              Theme.of(context).textTheme.bodySmall?.copyWith(
                                    color: Colors.grey.shade600,
                                  ),
                        ),
                      ],
                    ],
                  ),

                  const SizedBox(height: 12),

                  // Price and Actions
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            property.formattedPrice,
                            style: Theme.of(context)
                                .textTheme
                                .titleLarge
                                ?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.primaryColor,
                                ),
                          ),
                          if (property.description.isNotEmpty) ...[
                            const SizedBox(height: 4),
                            Text(
                              property.description,
                              style: Theme.of(context)
                                  .textTheme
                                  .bodySmall
                                  ?.copyWith(
                                    color: Colors.grey.shade600,
                                  ),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ],
                      ),
                      if (onStatusChange != null)
                        PopupMenuButton<String>(
                          icon: Icon(Icons.more_vert,
                              color: Colors.grey.shade600),
                          onSelected: (newStatus) {
                            onStatusChange!(newStatus);
                          },
                          itemBuilder: (context) => [
                            const PopupMenuItem(
                              value: 'Available',
                              child: Text('Mark as Available'),
                            ),
                            const PopupMenuItem(
                              value: 'Booked',
                              child: Text('Mark as Booked'),
                            ),
                            const PopupMenuItem(
                              value: 'Sold',
                              child: Text('Mark as Sold'),
                            ),
                            const PopupMenuItem(
                              value: 'Hold',
                              child: Text('Put on Hold'),
                            ),
                          ],
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

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'available':
        return Colors.green;
      case 'booked':
        return Colors.orange;
      case 'sold':
        return Colors.red;
      case 'hold':
        return Colors.grey;
      default:
        return Colors.blue;
    }
  }
}



