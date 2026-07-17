import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class NACHMandatePage extends StatefulWidget {
  const NACHMandatePage({super.key});

  @override
  State<NACHMandatePage> createState() => _NACHMandatePageState();
}

class _NACHMandatePageState extends State<NACHMandatePage> {
  final int _currentStep = 0;

  static const _steps = [
    _NachStepData(
      Icons.account_balance_rounded,
      'Select Bank',
      'Choose your bank from our verified partner list or enter manually.',
    ),
    _NachStepData(
      Icons.edit_note_rounded,
      'Fill Details',
      'Provide your account number, IFSC code, and authorize the mandate.',
    ),
    _NachStepData(
      Icons.verified_rounded,
      'Verify & Submit',
      'Confirm your details and submit for e-signature or Aadhaar OTP.',
    ),
    _NachStepData(
      Icons.check_circle_rounded,
      'Active',
      'Your NACH mandate will be active within 2-3 working days.',
    ),
  ];

  static const _bankPartners = [
    _BankData(
      'SBI',
      'State Bank of India',
      Icons.account_balance,
      Color(0xFF1A237E),
    ),
    _BankData('HDFC', 'HDFC Bank', Icons.account_balance, Color(0xFFFF6F00)),
    _BankData('ICICI', 'ICICI Bank', Icons.account_balance, Color(0xFFE91E63)),
    _BankData('AXIS', 'Axis Bank', Icons.account_balance, Color(0xFF4CAF50)),
    _BankData(
      'PNB',
      'Punjab National Bank',
      Icons.account_balance,
      Color(0xFF0288D1),
    ),
    _BankData(
      'BOB',
      'Bank of Baroda',
      Icons.account_balance,
      Color(0xFF6A1B9A),
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(context),
                const SizedBox(height: 24),
                _buildSectionTitle('Setup Process'),
                const SizedBox(height: 16),
                ...List.generate(
                  _steps.length,
                  (i) => Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: _buildStepCard(_steps[i], i),
                  ),
                ),
                const SizedBox(height: 24),
                _buildSectionTitle('Partner Banks'),
                const SizedBox(height: 12),
                _buildBankGrid(),
                const SizedBox(height: 24),
                _buildSectionTitle('Your Mandates'),
                const SizedBox(height: 12),
                _buildActiveMandates(),
                const SizedBox(height: 24),
                _buildCTASection(context),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Column(
      children: [
        GestureDetector(
          onTap: () => context.pop(),
          child: Align(
            alignment: Alignment.centerLeft,
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(
                Icons.arrow_back,
                color: Colors.white,
                size: 22,
              ),
            ),
          ),
        ),
        const SizedBox(height: 20),
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF00897B), Color(0xFF26A69A)],
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF00897B).withValues(alpha: 0.3),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: const Icon(
            Icons.receipt_long_rounded,
            size: 40,
            color: Colors.white,
          ),
        ),
        const SizedBox(height: 16),
        ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            colors: [AppTheme.primaryColor, Color(0xFF00897B)],
          ).createShader(bounds),
          child: Text(
            'NACH / e-Mandate',
            style: Theme.of(context).textTheme.headlineLarge?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Set up automatic EMI payments directly from your bank account',
          style: Theme.of(
            context,
          ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: AppTheme.titleLarge.copyWith(
        color: Colors.white,
        fontWeight: FontWeight.w700,
      ),
    );
  }

  Widget _buildStepCard(_NachStepData step, int index) {
    final isActive = index <= _currentStep;
    final isCurrent = index == _currentStep;
    return GlassCard(
      padding: const EdgeInsets.all(14),
      opacity: 0.1,
      blur: 8,
      child: Row(
        children: [
          AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              gradient: isActive
                  ? const LinearGradient(
                      colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                    )
                  : null,
              color: isActive ? null : Colors.white.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: isCurrent
                    ? AppTheme.accentColor
                    : Colors.white.withValues(alpha: 0.2),
                width: isCurrent ? 2 : 1,
              ),
            ),
            child: Icon(
              isActive ? Icons.check : step.icon,
              color: isActive ? Colors.white : Colors.white54,
              size: 20,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  step.title,
                  style: TextStyle(
                    color: isActive ? Colors.white : Colors.white54,
                    fontWeight: isCurrent ? FontWeight.w700 : FontWeight.w500,
                    fontSize: 14,
                  ),
                ),
                Text(
                  step.description,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.5),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBankGrid() {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        childAspectRatio: 1.0,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
      ),
      itemCount: _bankPartners.length,
      itemBuilder: (context, index) {
        final bank = _bankPartners[index];
        return GlassCard(
          padding: const EdgeInsets.all(10),
          opacity: 0.08,
          blur: 6,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(bank.icon, color: bank.color, size: 28),
              const SizedBox(height: 6),
              Text(
                bank.shortName,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                  fontSize: 12,
                ),
              ),
              Text(
                bank.fullName,
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.5),
                  fontSize: 8,
                ),
                textAlign: TextAlign.center,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildActiveMandates() {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        children: [
          Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: AppTheme.warningColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.pending_rounded,
                  color: AppTheme.warningColor,
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'No Active Mandates',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: 15,
                      ),
                    ),
                    Text(
                      'Set up your first NACH mandate for automatic EMI payments',
                      style: TextStyle(color: Colors.white54, fontSize: 12),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            height: 42,
            child: DecoratedBox(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                gradient: const LinearGradient(
                  colors: [Color(0xFF00897B), Color(0xFF26A69A)],
                ),
              ),
              child: ElevatedButton(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text(
                        'NACH mandate creation coming soon. Visit our office to set up auto-pay.',
                      ),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: const Text(
                  'Create New Mandate',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCTASection(BuildContext context) {
    return GlassCard(
      padding: const EdgeInsets.all(20),
      opacity: 0.15,
      blur: 10,
      child: Row(
        children: [
          const Icon(
            Icons.security_rounded,
            color: AppTheme.accentColor,
            size: 32,
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  '100% Secure',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                    fontSize: 14,
                  ),
                ),
                Text(
                  'NACH mandates are RBI-regulated and bank-grade secure',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.6),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _NachStepData {
  final IconData icon;
  final String title;
  final String description;
  const _NachStepData(this.icon, this.title, this.description);
}

class _BankData {
  final String shortName;
  final String fullName;
  final IconData icon;
  final Color color;
  const _BankData(this.shortName, this.fullName, this.icon, this.color);
}
