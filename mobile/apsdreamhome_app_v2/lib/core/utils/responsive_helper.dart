import 'package:flutter/material.dart';

class ResponsiveHelper {
  static bool isSmallScreen(BuildContext context) {
    return MediaQuery.of(context).size.width < 360;
  }

  static bool isMediumScreen(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    return width >= 360 && width < 600;
  }

  static bool isLargeScreen(BuildContext context) {
    return MediaQuery.of(context).size.width >= 600;
  }

  static double fontSize(BuildContext context, double base) {
    if (isSmallScreen(context)) return base * 0.75;
    if (isMediumScreen(context)) return base * 0.85;
    return base;
  }

  static EdgeInsets padding(BuildContext context, {double all = 16}) {
    final factor = isSmallScreen(context) ? 0.75 : 1.0;
    return EdgeInsets.all(all * factor);
  }

  static double chartHeight(BuildContext context, {double ratio = 0.25}) {
    return MediaQuery.of(context).size.height * ratio;
  }

  static double width(BuildContext context) {
    return MediaQuery.of(context).size.width;
  }
}
