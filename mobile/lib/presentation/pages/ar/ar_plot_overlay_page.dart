import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_constants.dart';
import '../../core/theme/app_theme.dart';
import '../../data/models/property_model.dart';
import '../providers/property_provider.dart';
import '../widgets/glass_card.dart';
import '../widgets/common_widgets.dart';

class ARPlotOverlayPage extends ConsumerWidget {
  const ARPlotOverlayPage({super.key});
  
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('AR Plot Overlay'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Colors.blue.shade50,
              Colors.green.shade50,
            ],
          ),
        ),
        child: Stack(
          children: [
            // AR Camera View (Simulated)
            _buildARCameraView(context),
            
            // AR Controls Overlay
            _buildARControls(context, ref),
            
            // Plot Information Overlay
            _buildPlotInfoOverlay(context),
            
            // Bottom Controls
            _buildBottomControls(context),
          ],
        ),
      ),
    );
  }
  
  Widget _buildARCameraView(BuildContext context) {
    return Container(
      width: double.infinity,
      height: double.infinity,
      child: Stack(
        children: [
          // Simulated camera view with AR overlay
          Container(
            decoration: BoxDecoration(
              gradient: RadialGradient(
                center: Alignment.center,
                radius: 1.0,
                colors: [
                  Colors.green.withOpacity(0.3),
                  Colors.blue.withOpacity(0.2),
                ],
              ),
            ),
          ),
          
          // AR Grid Lines
          CustomPaint(
            size: Size.infinite,
            painter: ARGridPainter(),
          ),
          
          // Virtual Plot Boundaries
          ...List.generate(5, (index) => _buildVirtualPlot(index)),
          
          // AR Instructions
          Positioned(
            top: 100,
            left: 20,
            right: 20,
            child: GlassCard(
              child: Row(
                children: [
                  Icon(
                    Icons.info_outline,
                    color: AppTheme.primaryColor,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Point camera at plot area to see AR overlay',
                      style: TextStyle(
                        color: AppTheme.primaryColor,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
  
  Widget _buildVirtualPlot(int index) {
    final positions = [
      const Offset(100, 200),
      const Offset(250, 180),
      const Offset(400, 220),
      const Offset(150, 350),
      const Offset(350, 380),
    ];
    
    final plotNames = ['Plot A-12', 'Plot B-05', 'Plot C-08', 'Plot D-03', 'Plot E-11'];
    final statuses = ['Available', 'Booked', 'Sold', 'Available', 'Hold'];
    final colors = [Colors.green, Colors.orange, Colors.red, Colors.green, Colors.grey];
    
    return Positioned(
      left: positions[index].dx,
      top: positions[index].dy,
      child: Column(
        children: [
          // Virtual Plot Boundary
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              border: Border.all(
                color: colors[index],
                width: 3,
              ),
              borderRadius: BorderRadius.circular(8),
              color: colors[index].withOpacity(0.1),
            ),
            child: Center(
              child: Icon(
                Icons.apartment,
                size: 32,
                color: colors[index],
              ),
            ),
          ),
          
          const SizedBox(height: 4),
          
          // Plot Label
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: colors[index].withOpacity(0.9),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  plotNames[index],
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 10,
                  ),
                ),
                Text(
                  statuses[index],
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 8,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
  
  Widget _buildARControls(BuildContext context, WidgetRef ref) {
    return Positioned(
      top: 50,
      right: 20,
      child: Column(
        children: [
          // AR Toggle
          GlassCard(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                Icon(
                  Icons.view_in_ar,
                  size: 24,
                  color: AppTheme.primaryColor,
                ),
                const SizedBox(height: 4),
                Text(
                  'AR ON',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryColor,
                  ),
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 12),
          
          // Grid Toggle
          GlassCard(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                Icon(
                  Icons.grid_on,
                  size: 24,
                  color: Colors.grey.shade600,
                ),
                const SizedBox(height: 4),
                Text(
                  'Grid',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 12),
          
          // Distance Measure
          GlassCard(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                Icon(
                  Icons.straighten,
                  size: 24,
                  color: Colors.blue,
                ),
                const SizedBox(height: 4),
                Text(
                  'Measure',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: Colors.blue,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
  
  Widget _buildPlotInfoOverlay(BuildContext context) {
    return Positioned(
      bottom: 160,
      left: 20,
      right: 20,
      child: GlassCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.location_on,
                  color: AppTheme.primaryColor,
                ),
                const SizedBox(width: 8),
                Text(
                  'Plot A-12',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryColor,
                  ),
                ),
                const Spacer(),
                StatusBadge(
                  status: 'Available',
                  color: Colors.green,
                ),
              ],
            ),
            
            const SizedBox(height: 12),
            
            Row(
              children: [
                _buildInfoItem('Size', '2,500 sq ft'),
                const SizedBox(width: 16),
                _buildInfoItem('Price', '₹45L'),
                const SizedBox(width: 16),
                _buildInfoItem('Type', 'Residential'),
              ],
            ),
            
            const SizedBox(height: 12),
            
            Row(
              children: [
                _buildInfoItem('Dimensions', '50x50 ft'),
                const SizedBox(width: 16),
                _buildInfoItem('Facing', 'North'),
                const SizedBox(width: 16),
                _buildInfoItem('Road', '30 ft'),
              ],
            ),
          ],
        ),
      ),
    );
  }
  
  Widget _buildInfoItem(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 10,
            color: Colors.grey.shade600,
          ),
        ),
        Text(
          value,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
  
  Widget _buildBottomControls(BuildContext context) {
    return Positioned(
      bottom: 20,
      left: 20,
      right: 20,
      child: Row(
        children: [
          Expanded(
            child: GlassCard(
              child: InkWell(
                onTap: () {
                  // Take screenshot
                },
                borderRadius: BorderRadius.circular(12),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Icon(
                        Icons.camera_alt,
                        size: 24,
                        color: AppTheme.primaryColor,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Capture',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.primaryColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
          
          const SizedBox(width: 12),
          
          Expanded(
            child: GlassCard(
              child: InkWell(
                onTap: () {
                  // Share AR view
                },
                borderRadius: BorderRadius.circular(12),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Icon(
                        Icons.share,
                        size: 24,
                        color: Colors.green,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Share',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Colors.green,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
          
          const SizedBox(width: 12),
          
          Expanded(
            child: GlassCard(
              child: InkWell(
                onTap: () {
                  // Get directions
                },
                borderRadius: BorderRadius.circular(12),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Icon(
                        Icons.directions,
                        size: 24,
                        color: Colors.blue,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Navigate',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Colors.blue,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class ARGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withOpacity(0.3)
      ..strokeWidth = 1;
    
    final spacing = 50.0;
    
    // Draw vertical lines
    for (double x = 0; x < size.width; x += spacing) {
      canvas.drawLine(
        Offset(x, 0),
        Offset(x, size.height),
        paint,
      );
    }
    
    // Draw horizontal lines
    for (double y = 0; y < size.height; y += spacing) {
      canvas.drawLine(
        Offset(0, y),
        Offset(size.width, y),
        paint,
      );
    }
    
    // Draw center crosshair
    final centerPaint = Paint()
      ..color = Colors.red.withOpacity(0.5)
      ..strokeWidth = 2;
    
    canvas.drawLine(
      Offset(size.width / 2 - 20, size.height / 2),
      Offset(size.width / 2 + 20, size.height / 2),
      centerPaint,
    );
    
    canvas.drawLine(
      Offset(size.width / 2, size.height / 2 - 20),
      Offset(size.width / 2, size.height / 2 + 20),
      centerPaint,
    );
  }
  
  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
