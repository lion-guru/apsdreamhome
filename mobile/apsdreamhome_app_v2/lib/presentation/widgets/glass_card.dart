import 'dart:ui';
import 'package:flutter/material.dart';

class GlassCard extends StatelessWidget {
  const GlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(20),
    this.margin = EdgeInsets.zero,
    this.width,
    this.height,
    this.borderRadius = 16.0,
    this.blur = 10.0,
    this.opacity = 0.15,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry margin;
  final double? width;
  final double? height;
  final double borderRadius;
  final double blur;
  final double opacity;

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(borderRadius),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: blur, sigmaY: blur),
        child: Container(
          width: width,
          height: height,
          margin: margin,
          padding: padding,
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.92),
            borderRadius: BorderRadius.circular(borderRadius),
            border: Border.all(
              color: const Color(0xFFE8DFD0),
              width: 1.2,
            ),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF0B194C).withValues(alpha: 0.06),
                blurRadius: 16,
                spreadRadius: 0,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: child,
        ),
      ),
    );
  }
}

class GradientBackground extends StatelessWidget {
  const GradientBackground({
    super.key,
    required this.child,
    this.colors,
  });

  final Widget child;
  final List<Color>? colors;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: colors ?? [
            const Color(0xFFFDFBF7),
            const Color(0xFFF9F5EC),
            const Color(0xFFF5EFE3),
            const Color(0xFFEFE7D8),
          ],
          stops: const [0.0, 0.3, 0.7, 1.0],
        ),
      ),
      child: child,
    );
  }
}

/// Animated mesh gradient background with slow-moving orbs.
/// Uses [SingleTickerProviderStateMixin] via a private StatefulWidget.
class MeshGradientBackground extends StatefulWidget {
  const MeshGradientBackground({
    super.key,
    required this.child,
    this.colors,
  });

  final Widget child;
  final List<Color>? colors;

  @override
  State<MeshGradientBackground> createState() => _MeshGradientBackgroundState();
}

class _MeshGradientBackgroundState extends State<MeshGradientBackground>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 20),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final t = _controller.value;
    final shift1 = t * 40 - 20;
    final shift2 = (1 - t) * 30 - 15;
    final shift3 = t * 25 - 12;
    final shift4 = (1 - t) * 35 - 17;

    final gradientColors = widget.colors ??
        const [
          Color(0xFFFDFBF7), // Warm Pearl Ivory
          Color(0xFFF9F5EC), // Soft Champagne Cream
          Color(0xFFF5EFE3), // Sunlit Golden Mist
          Color(0xFFEFE7D8), // Elegant Warm Gold
          Color(0xFFFDFBF7), // Warm Pearl Ivory
        ];

    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: gradientColors,
          stops: const [0.0, 0.25, 0.5, 0.75, 1.0],
        ),
      ),
      child: AnimatedBuilder(
        animation: _controller,
        builder: (context, child) {
          return Stack(
            children: [
              Positioned(
                top: -80 + shift1,
                right: -60 + shift2,
                child: Container(
                  width: 240,
                  height: 240,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: const Color(0xFFD4AF37).withValues(alpha: 0.12 + t * 0.04),
                  ),
                ),
              ),
              Positioned(
                bottom: 100 + shift3,
                left: -40 + shift4,
                child: Container(
                  width: 200,
                  height: 200,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: const Color(0xFFF58220).withValues(alpha: 0.08 + t * 0.03),
                  ),
                ),
              ),
              Positioned(
                top: 180 - shift2,
                right: 60 + shift1,
                child: Container(
                  width: 150,
                  height: 150,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: const Color(0xFFE5A93C).withValues(alpha: 0.07 + t * 0.02),
                  ),
                ),
              ),
              Positioned(
                bottom: -40 - shift1,
                right: -20 + shift3,
                child: Container(
                  width: 180,
                  height: 180,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: const Color(0xFFD4AF37).withValues(alpha: 0.10 + t * 0.03),
                  ),
                ),
              ),
              child!,
            ],
          );
        },
        child: widget.child,
      ),
    );
  }
}
