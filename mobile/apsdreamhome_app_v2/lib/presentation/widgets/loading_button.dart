import 'package:flutter/material.dart';

class LoadingButton extends StatefulWidget {
  final String text;
  final VoidCallback onPressed;
  final bool isLoading;
  final Color? backgroundColor;
  final Color? textColor;
  final double? width;
  final double? height;
  final bool small;

  const LoadingButton({
    super.key,
    required this.text,
    required this.onPressed,
    this.isLoading = false,
    this.backgroundColor,
    this.textColor,
    this.width,
    this.height,
    this.small = false,
  });

  @override
  State<LoadingButton> createState() => _LoadingButtonState();
}

class _LoadingButtonState extends State<LoadingButton> {
  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: widget.width,
      height: widget.height ?? (widget.small ? 36.0 : 48.0),
      child: ElevatedButton(
        onPressed: widget.isLoading ? null : widget.onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: widget.backgroundColor ?? Theme.of(context).primaryColor,
          foregroundColor: widget.textColor ?? Colors.white,
          disabledBackgroundColor: widget.backgroundColor?.withValues(alpha: 0.5) ?? 
              Theme.of(context).primaryColor.withValues(alpha: 0.5),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
        ),
        child: widget.isLoading
            ? SizedBox(
                width: 16,
                height: 16,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(
                    widget.textColor ?? Colors.white,
                  ),
                ),
              )
            : Text(
                widget.text,
                style: TextStyle(
                  fontSize: widget.small ? 14 : 16,
                  fontWeight: FontWeight.w600,
                ),
              ),
      ),
    );
  }
}
