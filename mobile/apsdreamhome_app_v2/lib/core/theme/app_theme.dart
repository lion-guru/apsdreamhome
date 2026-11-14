import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  // ============================================================================
  // Core Color Palette - Royal Navy & Luxury Champagne Gold
  // ============================================================================
  static const Color primaryColor = Color(0xFF0B194C); // Deep Royal Navy Blue
  static const Color secondaryColor = Color(0xFF1E3A8A); // Rich Imperial Blue
  static const Color accentColor = Color(0xFFF58220); // Sunset Gold / Vibrant Amber
  static const Color goldColor = Color(0xFFE5A93C); // Luxury Metallic Gold
  static const Color goldAccent = Color(0xFFE5A93C); // Luxury Metallic Gold
  static const Color backgroundColor = Color(0xFFFAF7F0); // Warm Light Golden Champagne
  static const Color surfaceColor = Color(0xFFFAF7F0); // Warm Light Golden Champagne
  static const Color cardColor = Colors.white;
  static const Color goldBorder = Color(0xFFE8DFD0); // Delicate Luxury Gold hairline border

  // Glassmorphism colors
  static const Color glassSurface = Color(0x1AFFFFFF);
  static const Color glassBorder = Color(0x33FFFFFF);

  // Semantic colors
  static const Color errorColor = Color(0xFFEF4444); // Modern Red
  static const Color warningColor = Color(0xFFF59E0B); // Amber Orange
  static const Color infoColor = Color(0xFF3B82F6); // Clean Blue
  static const Color successColor = Color(0xFF10B981); // Emerald Green

  // Extended role tokens - never raw hex in screens
  static const Color whatsappGreen = Color(0xFF25D366); // WhatsApp brand green
  static const Color lightBlue = Color(0xFF1E88E5); // Material light blue 600
  static const Color purple = Color(0xFF9C27B0); // Material purple 500
  static const Color teal = Color(0xFF00897B); // Material teal 600
  static const Color emeraldDeep = Color(0xFF43A047); // Material green 600
  static const Color slateLight = Color(0xFFCBD5E1); // Slate 300 - light borders
  static const Color amberDeep = Color(0xFFF57C00); // Deep orange 700
  static const Color amberSoft = Color(0xFFFFB300); // Amber accent
  static const Color indigoIndigo = Color(0xFF1565C0); // Blue 800
  static const Color emeraldTint = Color(0xFFD4AF37); // Metallic gold accent (legacy role tag)
  static const Color aiGradientStart = Color(0xFF7C3AED); // Violet 600 - AI primary
  static const Color aiGradientEnd = Color(0xFF9333EA); // Violet 700 - AI secondary
  static const Color googleBlue = Color(0xFF4285F4); // Google brand blue
  static const Color whatsappGreenDark = Color(0xFF128C7E); // WhatsApp dark teal
  static const Color whatsappGreenDeep = Color(0xFF075E54); // WhatsApp deep teal
  static const Color navyLight = Color(0xFF1E3A8A); // Light navy accent

  // Light surfaces for chip/badges
  static const Color chipGreenTint = Color(0xFFE3F6EC); // success surface tint
  static const Color chipBlueTint = Color(0xFFE8F0FE); // info surface tint
  static const Color chipPurpleTint = Color(0xFFF3E5F5); // purple surface tint
  static const Color chipTealTint = Color(0xFFE0F2F1); // teal surface tint
  static const Color chipAmberTint = Color(0xFFFFF7E0); // warning surface tint
  static const Color whatsappSurface = Color(0xFFE7FFEA); // WhatsApp light surface
  static const Color greenSurface = Color(0xFFF1F8E9); // green light surface
  static const Color orangeSurface = Color(0xFFFFF3E0); // orange light surface

  // Status tints - exact matches for badges/status chips
  static const Color successTint = Color(0xFFE3F6EC); // success chip background
  static const Color warningTint = Color(0xFFFFE8CC); // warning chip background
  static const Color holdTint = Color(0xFFFFF4D6); // hold chip background
  static const Color bookedTint = Color(0xFFFFE4E4); // booked chip background
  static const Color soldTint = Color(0xFFECEEF0); // sold chip background
  // Material tint surfaces used in chips/banners
  static const Color materialGreen = Color(0xFF4CAF50);
  static const Color materialGreenDeep = Color(0xFF388E3C);
  static const Color materialGreenLight = Color(0xFF66BB6A);
  static const Color orangeDeep = Color(0xFFE65100);
  static const Color orangeDark = Color(0xFFFB8C00);
  static const Color orangeLight = Color(0xFFFFB74D);
  static const Color orangeMaterial = Color(0xFFFF9800);
  static const Color orangeDarker = Color(0xFFEA580C);
  static const Color purpleLight = Color(0xFFBA68C8);
  static const Color purpleDeep = Color(0xFF6A1B9A);
  static const Color purpleDark = Color(0xFF8E24AA);
  static const Color amberDark = Color(0xFFFFA000);
  static const Color amberLightTint = Color(0xFFFDE68A);
  static const Color goldClassic = Color(0xFFFFD700);
  static const Color goldMetallic = Color(0xFFD4AF37);
  static const Color pink = Color(0xFFE91E63);
  static const Color pinkDeep = Color(0xFFD81B60);
  static const Color pinkLight = Color(0xFFF06292);
  static const Color rose = Color(0xFFE11D48);
  static const Color indigoDeep = Color(0xFF0D47A1);
  static const Color indigoMaterial = Color(0xFF2196F3);
  static const Color indigoLight = Color(0xFF42A5F5);
  static const Color indigo = Color(0xFF4F46E5);
  static const Color blueSurface = Color(0xFFE3F2FD);
  static const Color tealDeep = Color(0xFF0D9488);
  static const Color greySurface = Color(0xFFF5F5F5);
  static const Color greyDeep = Color(0xFF616161);
  static const Color slateMid = Color(0xFF94A3B8);
  static const Color surfaceWarm = Color(0xFFF5EFE3);
  static const Color aiVioletDark = Color(0xFF6D28D9);

  // Plot status colors
  static const Color plotAvailable = Color(0xFF10B981); // Emerald Green
  static const Color plotHold = Color(0xFFF59E0B); // Amber
  static const Color plotBooked = Color(0xFFEF4444); // Crimson Red
  static const Color plotSold = Color(0xFF64748B); // Slate Grey

  // Text colors
  static const Color textPrimaryLight = Color(0xFF212121); // Dark grey for light theme
  static const Color textSecondaryLight = Color(0xFF757575); // Medium grey for light theme
  static const Color textLight = Color(0xFFFFFFFF); // White text for dark backgrounds
  static const Color textHint = Color(0xFF9E9E9E); // Muted hint text

  // Shadow colors
  static const Color shadowLight = Color(0x0E000000);
  static const Color shadowMedium = Color(0x1A000000);
  static const Color shadowDark = Color(0x2A000000);
  static final Color shadowGold = const Color(0xFFD4AF37).withValues(alpha: 0.12);

  // Gradients
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  static const LinearGradient goldGradient = LinearGradient(
    colors: [Color(0xFFF58220), Color(0xFFE5A93C)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  static const LinearGradient champagneGradient = LinearGradient(
    colors: [Color(0xFFFAF7F0), Color(0xFFF1E9D9)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  static const LinearGradient deepGradient = LinearGradient(
    colors: [Color(0xFF0B194C), Color(0xFF1E3A8A), Color(0xFF2E5A88)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  static const LinearGradient glassGradient = LinearGradient(
    colors: [Color(0x2AFFFFFF), Color(0x0AFFFFFF)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  // Typography Scale
  static const String _fontFamily = 'Inter';
  static final TextTheme _textTheme = GoogleFonts.interTextTheme(
    const TextTheme(
      displayLarge: displayLarge,
      displayMedium: displayMedium,
      headlineLarge: headlineLarge,
      headlineMedium: headlineMedium,
      titleLarge: titleLarge,
      titleMedium: titleMedium,
      titleSmall: titleSmall,
      bodyLarge: bodyLarge,
      bodyMedium: bodyMedium,
      bodySmall: bodySmall,
      labelLarge: labelLarge,
      labelMedium: labelMedium,
      labelSmall: labelSmall,
    ),
  );

  static const TextStyle displayLarge = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 32,
    fontWeight: FontWeight.bold,
    letterSpacing: -0.5,
    height: 1.2,
  );

  static const TextStyle displayMedium = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 28,
    fontWeight: FontWeight.bold,
    letterSpacing: -0.3,
    height: 1.2,
  );

  static const TextStyle headlineLarge = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 24,
    fontWeight: FontWeight.w600,
    letterSpacing: -0.2,
    height: 1.3,
  );

  static const TextStyle headlineMedium = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 20,
    fontWeight: FontWeight.w600,
    letterSpacing: 0,
    height: 1.3,
  );

  static const TextStyle titleLarge = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 18,
    fontWeight: FontWeight.w600,
    letterSpacing: 0,
    height: 1.3,
  );

  static const TextStyle titleMedium = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 16,
    fontWeight: FontWeight.w600,
    letterSpacing: 0.1,
    height: 1.4,
  );

  static const TextStyle titleSmall = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 14,
    fontWeight: FontWeight.w600,
    letterSpacing: 0.1,
    height: 1.4,
  );

  static const TextStyle bodyLarge = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 16,
    fontWeight: FontWeight.w400,
    letterSpacing: 0.1,
    height: 1.5,
  );

  static const TextStyle bodyMedium = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 14,
    fontWeight: FontWeight.w400,
    letterSpacing: 0.15,
    height: 1.5,
  );

  static const TextStyle bodySmall = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 12,
    fontWeight: FontWeight.w400,
    letterSpacing: 0.2,
    height: 1.5,
  );

  static const TextStyle labelLarge = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 14,
    fontWeight: FontWeight.w600,
    letterSpacing: 0.5,
    height: 1.4,
  );

  static const TextStyle labelMedium = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 12,
    fontWeight: FontWeight.w500,
    letterSpacing: 0.5,
    height: 1.3,
  );

  static const TextStyle labelSmall = TextStyle(
    fontFamily: _fontFamily,
    fontSize: 10,
    fontWeight: FontWeight.w500,
    letterSpacing: 0.5,
    height: 1.3,
  );

  // ============================================================================
  // Theme Data
  // ============================================================================
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      fontFamily: _fontFamily,
      scaffoldBackgroundColor: backgroundColor,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primaryColor,
        brightness: Brightness.light,
        primary: primaryColor,
        secondary: accentColor,
        surface: surfaceColor,
      ),
      textTheme: _textTheme,
      cardTheme: CardThemeData(
        color: cardColor,
        elevation: 2,
        shadowColor: shadowLight,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(
            color: goldBorder,
            width: 1,
          ),
        ),
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryColor,
          foregroundColor: Colors.white,
          elevation: 2,
          shadowColor: primaryColor.withValues(alpha: 0.3),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          textStyle: const TextStyle(
            fontFamily: _fontFamily,
            fontSize: 15,
            fontWeight: FontWeight.w600,
            letterSpacing: 0.3,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: primaryColor,
          side: const BorderSide(color: primaryColor, width: 1.5),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          textStyle: const TextStyle(
            fontFamily: _fontFamily,
            fontSize: 15,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: primaryColor, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: errorColor, width: 2),
        ),
        filled: true,
        fillColor: Colors.grey.shade50,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        labelStyle: TextStyle(
          color: Colors.grey.shade600,
          fontSize: 14,
          fontWeight: FontWeight.w500,
        ),
        hintStyle: const TextStyle(
          color: textHint,
          fontSize: 14,
        ),
      ),
      floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: accentColor,
        foregroundColor: Colors.white,
        elevation: 4,
        shape: CircleBorder(),
      ),
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        modalBackgroundColor: Colors.white,
      ),
      dialogTheme: DialogThemeData(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
        ),
        backgroundColor: Colors.white,
        titleTextStyle: const TextStyle(
          fontFamily: _fontFamily,
          fontSize: 20,
          fontWeight: FontWeight.bold,
          color: primaryColor,
        ),
        contentTextStyle: const TextStyle(
          fontFamily: _fontFamily,
          fontSize: 16,
          color: textPrimaryLight,
        ),
      ),
      dividerTheme: DividerThemeData(
        color: Colors.grey.shade200,
        thickness: 1,
      ),
    );
  }

  static ThemeData get darkTheme {
    // Dark navy + champagne gold — the same brand language as lightTheme,
    // not a recoloured prototype. Primary intention: readable, elegant, not neon.
    const Color darkScaffold = Color(0xFF0A1233);
    const Color darkSurface = Color(0xFF111A3E);
    const Color darkCard = Color(0xFF161F45);
    const Color darkBorder = Color(0xFF2A3766);
    const Color darkTextPrimary = Color(0xFFEDEFF7);
    const Color darkTextSecondary = Color(0xFFB7BCCF);

    return ThemeData(
      useMaterial3: true,
      fontFamily: _fontFamily,
      scaffoldBackgroundColor: darkScaffold,
      colorScheme: const ColorScheme.dark(
        primary: Color(0xFF8FA7E8),
        onPrimary: Color(0xFF0B194C),
        secondary: accentColor,
        onSecondary: Color(0xFF0B194C),
        surface: darkSurface,
        onSurface: darkTextPrimary,
        error: errorColor,
        onError: Colors.white,
      ),
      textTheme: _textTheme.apply(
        bodyColor: darkTextPrimary,
        displayColor: darkTextPrimary,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: darkSurface,
        foregroundColor: darkTextPrimary,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: TextStyle(
          fontFamily: _fontFamily,
          color: darkTextPrimary,
          fontSize: 20,
          fontWeight: FontWeight.w700,
        ),
      ),
      cardTheme: CardThemeData(
        color: darkCard,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: darkBorder, width: 1),
        ),
        shadowColor: Colors.black.withValues(alpha: 0.4),
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: accentColor,
          foregroundColor: const Color(0xFF0B194C),
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          textStyle: const TextStyle(
            fontFamily: _fontFamily,
            fontSize: 15,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.3,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: const Color(0xFF8FA7E8),
          side: const BorderSide(color: darkBorder, width: 1.5),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: darkBorder),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: darkBorder),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF8FA7E8), width: 2),
        ),
        filled: true,
        fillColor: const Color(0xFF1A2348),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        labelStyle: const TextStyle(color: darkTextSecondary),
        hintStyle: const TextStyle(color: Color(0xFF6E7798)),
      ),
      dividerTheme: const DividerThemeData(
        color: darkBorder,
        thickness: 1,
      ),
      floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: accentColor,
        foregroundColor: Color(0xFF0B194C),
        elevation: 4,
      ),
    );
  }

  // ============================================================================
  // Decorative BoxDecorations
  // ============================================================================

  /// Glassmorphism decoration
  static BoxDecoration glassDecoration({
    Color? surfaceColor,
    Color? borderColor,
    double borderRadius = 12,
    double blur = 10,
    double opacity = 0.1,
  }) {
    return BoxDecoration(
      color: surfaceColor ?? glassSurface,
      borderRadius: BorderRadius.circular(borderRadius),
      border: Border.all(
        color: borderColor ?? glassBorder,
        width: 1,
      ),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withValues(alpha: opacity),
          blurRadius: blur,
          spreadRadius: 2,
        ),
      ],
    );
  }

  /// Card decoration with subtle shadow and gold border
  static BoxDecoration cardDecoration({
    Color? color,
    double radius = 16,
    Color? borderColor,
  }) {
    return BoxDecoration(
      color: color ?? cardColor,
      borderRadius: BorderRadius.circular(radius),
      border: Border.all(
        color: borderColor ?? goldBorder,
        width: 1,
      ),
      boxShadow: [
        const BoxShadow(
          color: shadowLight,
          blurRadius: 10,
          spreadRadius: 0,
          offset: Offset(0, 4),
        ),
      ],
    );
  }

  /// Gradient card decoration
  static BoxDecoration gradientCardDecoration({
    Gradient? gradient,
    double radius = 16,
  }) {
    return BoxDecoration(
      gradient: gradient ?? primaryGradient,
      borderRadius: BorderRadius.circular(radius),
      boxShadow: [
        const BoxShadow(
          color: shadowDark,
          blurRadius: 20,
          spreadRadius: 0,
          offset: Offset(0, 8),
        ),
      ],
    );
  }

  /// Soft shadow decoration for cards
  static BoxDecoration cardShadow({
    Color? color,
    double radius = 16,
  }) {
    return BoxDecoration(
      color: color ?? Colors.white,
      borderRadius: BorderRadius.circular(radius),
      boxShadow: [
        const BoxShadow(
          color: shadowLight,
          blurRadius: 16,
          spreadRadius: 0,
          offset: Offset(0, 8),
        ),
      ],
    );
  }
}
