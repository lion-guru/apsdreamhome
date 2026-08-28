import 'package:flutter/material.dart';

/// Custom Button Widget - Reusable button with loading state
class CustomButton extends StatelessWidget {
  final String text;
  final VoidCallback onPressed;
  final bool isLoading;
  final bool disabled;
  final Color? backgroundColor;
  final Color? foregroundColor;
  final EdgeInsets? padding;
  final double? width;
  final double? height;
  final Widget? icon;
  final OutlinedBorder? shape;
  final double? elevation;

  const CustomButton({
    super.key,
    required this.text,
    required this.onPressed,
    this.isLoading = false,
    this.disabled = false,
    this.backgroundColor,
    this.foregroundColor,
    this.padding,
    this.width,
    this.height,
    this.icon,
    this.shape,
    this.elevation,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      height: height ?? 48,
      child: ElevatedButton(
        onPressed: (disabled || isLoading) ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: backgroundColor ?? const Color(0xFF2E7D32),
          foregroundColor: foregroundColor ?? Colors.white,
          padding: padding ?? const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          shape: shape ?? RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          elevation: elevation ?? 2,
        ),
        child: isLoading
            ? const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              )
            : icon != null
                ? Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      icon!,
                      const SizedBox(width: 8),
                      Text(text),
                    ],
                  )
                : Text(text),
      ),
    );
  }
}
