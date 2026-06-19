import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/plot_model.dart';

class ColonyPlotGridPage extends ConsumerStatefulWidget {
  final int colonyId;
  final String colonyName;

  const ColonyPlotGridPage({
    super.key,
    required this.colonyId,
    required this.colonyName,
  });

  @override
  ConsumerState<ColonyPlotGridPage> createState() => _ColonyPlotGridPageState();
}

class _ColonyPlotGridPageState extends ConsumerState<ColonyPlotGridPage> {
  final TransformationController _transformationController =
      TransformationController();
  String _selectedStatus = 'all';

  @override
  void dispose() {
    _transformationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final plotsAsync = ref.watch(plotsProvider(widget.colonyId.toString()));

    return Scaffold(
      appBar: AppBar(
        title: Text(
          widget.colonyName,
          style: GoogleFonts.outfit(fontWeight: FontWeight.w600),
        ),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.filter_list),
            onSelected: (value) => setState(() => _selectedStatus = value),
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'all', child: Text('All Plots')),
              const PopupMenuItem(value: 'available', child: Text('Available')),
              const PopupMenuItem(value: 'hold', child: Text('On Hold')),
              const PopupMenuItem(value: 'booked', child: Text('Booked')),
              const PopupMenuItem(value: 'sold', child: Text('Sold')),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          // Legend
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            color: Colors.grey.shade100,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _legendItem('Available', AppTheme.plotAvailable),
                _legendItem('Hold', AppTheme.plotHold),
                _legendItem('Booked', AppTheme.plotBooked),
                _legendItem('Sold', AppTheme.plotSold),
              ],
            ),
          ),
          // Plot grid with pinch-to-zoom
          Expanded(
            child: plotsAsync.when(
              data: (plots) {
                final filtered = _selectedStatus == 'all'
                    ? plots
                    : plots
                        .where((p) => p.status == _selectedStatus)
                        .toList();
                return InteractiveViewer(
                  transformationController: _transformationController,
                  minScale: 0.5,
                  maxScale: 3.0,
                  boundaryMargin: const EdgeInsets.all(50),
                  child: _buildPlotGrid(filtered),
                );
              },
              loading: () =>
                  const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _transformationController.value = Matrix4.identity(),
        tooltip: 'Reset Zoom',
        child: const Icon(Icons.zoom_out_map),
      ),
    );
  }

  Widget _legendItem(String label, Color color) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 14,
          height: 14,
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(3),
          ),
        ),
        const SizedBox(width: 4),
        Text(label,
            style: GoogleFonts.inter(
                fontSize: 11, fontWeight: FontWeight.w500)),
      ],
    );
  }

  Widget _buildPlotGrid(List<PlotModel> plots) {
    if (plots.isEmpty) {
      return Center(
        child: Text(
          'No plots found',
          style: GoogleFonts.inter(fontSize: 16, color: Colors.grey),
        ),
      );
    }

    // Parse plot numbers to extract block and row info
    // Plot numbers are like "MT-A-001", "MT-B-015", etc.
    int maxCol = 0;
    int maxRow = 0;
    final parsedPlots = <ParsedPlot>[];

    for (final plot in plots) {
      final parsed = _parsePlotNumber(plot);
      parsedPlots.add(parsed);
      if (parsed.col > maxCol) maxCol = parsed.col;
      if (parsed.row > maxRow) maxRow = parsed.row;
    }

    final cols = maxCol.clamp(1, 20);
    final rows = maxRow.clamp(1, 20);
    const cellSize = 56.0;
    const gap = 4.0;

    final gridWidth = (cols * (cellSize + gap)) + 80;
    final gridHeight = (rows * (cellSize + gap)) + 80;

    return Container(
      padding: const EdgeInsets.all(16),
      child: GestureDetector(
        onTapUp: (details) {
          final dx = details.localPosition.dx;
          final dy = details.localPosition.dy;
          for (final pp in parsedPlots) {
            final x = 40.0 + (pp.col - 1) * (cellSize + gap);
            final y = 40.0 + (pp.row - 1) * (cellSize + gap);
            final rect = Rect.fromLTWH(x, y, cellSize, cellSize);
            if (rect.contains(Offset(dx, dy))) {
              _showPlotDetail(pp.plot);
              break;
            }
          }
        },
        child: CustomPaint(
          size: Size(gridWidth, gridHeight),
          painter: PlotGridPainter(
            parsedPlots: parsedPlots,
            cellSize: cellSize,
            gap: gap,
          ),
        ),
      ),
    );
  }

  ParsedPlot _parsePlotNumber(PlotModel plot) {
    // Parse plotNumber like "MT-A-001" -> col=A(1), row=001
    final parts = plot.plotNumber.split('-');
    int col = 0;
    int row = 0;

    if (parts.length >= 3) {
      // Block letter part (e.g., "A", "B", "C")
      final blockPart = parts[1];
      if (blockPart.length == 1 && blockPart.codeUnitAt(0) >= 65) {
        col = blockPart.codeUnitAt(0) - 64; // A=1, B=2, C=3
      } else {
        col = int.tryParse(blockPart.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0;
      }
      // Row number part (e.g., "001", "015")
      row = int.tryParse(parts[2].replaceAll(RegExp(r'[^0-9]'), '')) ?? 0;
    } else if (parts.length == 2) {
      final blockPart = parts[0];
      if (blockPart.length == 1 && blockPart.codeUnitAt(0) >= 65) {
        col = blockPart.codeUnitAt(0) - 64;
      } else {
        col = int.tryParse(blockPart.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0;
      }
      row = int.tryParse(parts[1].replaceAll(RegExp(r'[^0-9]'), '')) ?? 0;
    } else {
      row = int.tryParse(plot.plotNumber.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0;
    }

    return ParsedPlot(plot: plot, col: col, row: row);
  }

  void _showPlotDetail(PlotModel plot) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => PlotDetailBottomSheet(plot: plot),
    );
  }
}

/// Internal model for parsed plot grid position
class ParsedPlot {
  final PlotModel plot;
  final int col;
  final int row;

  const ParsedPlot({
    required this.plot,
    required this.col,
    required this.row,
  });
}

class PlotDetailBottomSheet extends StatelessWidget {
  final PlotModel plot;

  const PlotDetailBottomSheet({super.key, required this.plot});

  Color get _statusColor {
    switch (plot.status) {
      case 'available':
        return AppTheme.plotAvailable;
      case 'hold':
        return AppTheme.plotHold;
      case 'booked':
        return AppTheme.plotBooked;
      case 'sold':
        return AppTheme.plotSold;
      default:
        return Colors.grey;
    }
  }

  String get _statusLabel {
    switch (plot.status) {
      case 'available':
        return 'Available';
      case 'hold':
        return 'On Hold';
      case 'booked':
        return 'Booked';
      case 'sold':
        return 'Sold';
      default:
        return plot.status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.6,
      minChildSize: 0.3,
      maxChildSize: 0.9,
      expand: false,
      builder: (context, scrollController) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: ListView(
            controller: scrollController,
            padding: const EdgeInsets.all(20),
            children: [
              // Handle bar
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Plot number + status badge
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Plot ${plot.plotNumber}',
                    style: GoogleFonts.outfit(
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.primaryColor,
                    ),
                  ),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: _statusColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: _statusColor, width: 1),
                    ),
                    child: Text(
                      _statusLabel,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: _statusColor,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                plot.colonyName,
                style: GoogleFonts.inter(
                  fontSize: 14,
                  color: Colors.grey.shade600,
                ),
              ),
              const SizedBox(height: 20),

              // Details grid
              _detailRow(Icons.straighten, 'Area',
                  '${plot.areaSqft.toStringAsFixed(0)} sq.ft'),
              _detailRow(Icons.explore, 'Facing', plot.facing),
              if (plot.frontWidth != null && plot.depth != null)
                _detailRow(Icons.aspect_ratio, 'Dimensions',
                    '${plot.frontWidth!.toStringAsFixed(0)} x ${plot.depth!.toStringAsFixed(0)} ft'),
              if (plot.shape != null)
                _detailRow(Icons.category, 'Shape', plot.shape!),
              const Divider(height: 24),

              // Price
              _detailRow(
                Icons.currency_rupee,
                'Base Price',
                '₹${plot.basePrice.toStringAsFixed(0)}',
              ),
              if (plot.totalPrice != plot.basePrice)
                _detailRow(
                  Icons.price_check,
                  'Final Price',
                  '₹${plot.totalPrice.toStringAsFixed(0)}',
                  valueColor: AppTheme.primaryColor,
                  valueWeight: FontWeight.w700,
                ),
              const Divider(height: 24),

              // Premium flags
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  if (plot.isCorner == true)
                    _premiumBadge('Corner Plot', Icons.star),
                  if (plot.isParkFacing == true)
                    _premiumBadge('Park Facing', Icons.park),
                  if (plot.isMainRoadFacing == true)
                    _premiumBadge('Main Road', Icons.route),
                ],
              ),

              // Booked info
              if (plot.status == 'booked' && plot.bookedByName != null) ...[
                const Divider(height: 24),
                _detailRow(Icons.person, 'Booked By', plot.bookedByName!),
                if (plot.bookedAt != null)
                  _detailRow(
                      Icons.calendar_today,
                      'Booked On',
                      '${plot.bookedAt!.day}/${plot.bookedAt!.month}/${plot.bookedAt!.year}'),
              ],

              // Hold info
              if (plot.status == 'hold' && plot.holdUntil != null) ...[
                const Divider(height: 24),
                _detailRow(Icons.access_time, 'Hold Until',
                    '${plot.holdUntil!.day}/${plot.holdUntil!.month}/${plot.holdUntil!.year} ${plot.holdUntil!.hour}:${plot.holdUntil!.minute.toString().padLeft(2, '0')}'),
              ],

              const SizedBox(height: 20),

              // Action button
              if (plot.status == 'available')
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(context);
                      // TODO: Navigate to booking flow
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: Text(
                      'Book This Plot',
                      style: GoogleFonts.outfit(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  Widget _detailRow(IconData icon, String label, String value,
      {Color? valueColor, FontWeight? valueWeight}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Icon(icon, size: 18, color: Colors.grey.shade500),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              label,
              style: GoogleFonts.inter(
                fontSize: 14,
                color: Colors.grey.shade600,
              ),
            ),
          ),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: valueWeight ?? FontWeight.w600,
              color: valueColor ?? Colors.black87,
            ),
          ),
        ],
      ),
    );
  }

  Widget _premiumBadge(String label, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: AppTheme.accentColor.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
            color: AppTheme.accentColor.withValues(alpha: 0.4), width: 1),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: AppTheme.accentColor),
          const SizedBox(width: 4),
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: Colors.orange.shade800,
            ),
          ),
        ],
      ),
    );
  }
}

class PlotGridPainter extends CustomPainter {
  final List<ParsedPlot> parsedPlots;
  final double cellSize;
  final double gap;

  PlotGridPainter({
    required this.parsedPlots,
    required this.cellSize,
    required this.gap,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final textPainter = TextPainter(textDirection: TextDirection.ltr);

    for (final pp in parsedPlots) {
      final col = pp.col;
      final row = pp.row;
      if (col == 0 || row == 0) continue;

      final x = 40.0 + (col - 1) * (cellSize + gap);
      final y = 40.0 + (row - 1) * (cellSize + gap);

      Color statusColor;
      switch (pp.plot.status) {
        case 'available':
          statusColor = AppTheme.plotAvailable;
          break;
        case 'hold':
          statusColor = AppTheme.plotHold;
          break;
        case 'booked':
          statusColor = AppTheme.plotBooked;
          break;
        case 'sold':
          statusColor = AppTheme.plotSold;
          break;
        default:
          statusColor = Colors.grey.shade300;
      }

      // Draw cell
      final paint = Paint()
        ..color = statusColor
        ..style = PaintingStyle.fill;
      final rect = RRect.fromRectAndRadius(
        Rect.fromLTWH(x, y, cellSize, cellSize),
        const Radius.circular(6),
      );
      canvas.drawRRect(rect, paint);

      // Draw border
      final borderPaint = Paint()
        ..color = statusColor.withValues(alpha: 0.6)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 1;
      canvas.drawRRect(rect, borderPaint);

      // Draw plot number
      final displayNumber = pp.plot.plotNumber.length > 5
          ? pp.plot.plotNumber.substring(pp.plot.plotNumber.length - 3)
          : pp.plot.plotNumber;
      textPainter.text = TextSpan(
        text: displayNumber,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.w600,
        ),
      );
      textPainter.layout();
      textPainter.paint(
        canvas,
        Offset(
          x + (cellSize - textPainter.width) / 2,
          y + (cellSize - textPainter.height) / 2,
        ),
      );
    }
  }

  @override
  bool shouldRepaint(covariant PlotGridPainter oldDelegate) =>
      parsedPlots != oldDelegate.parsedPlots;
}
