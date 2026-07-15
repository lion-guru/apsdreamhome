import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';

class FaqPage extends StatefulWidget {
  const FaqPage({super.key});

  @override
  State<FaqPage> createState() => _FaqPageState();
}

class _FaqPageState extends State<FaqPage> {
  final _searchController = TextEditingController();
  String _selectedCategory = 'General';
  List<Map<String, String>> _filteredFaqs = [];
  String _searchQuery = '';

  static const Map<String, List<Map<String, String>>> _faqData = {
    'General': [
      {
        'question': 'What is APS Dream Home?',
        'answer':
            'APS Dream Home is a full-service real estate platform for buying, selling, and investing in residential and commercial properties across India. We offer transparent pricing, legal assistance, and end-to-end support.',
      },
      {
        'question': 'Which cities do you operate in?',
        'answer':
            'We currently operate in Uttar Pradesh (Gorakhpur, Lucknow, Varanasi, Kushinagar), Bihar, and Bengal. We are expanding to more cities soon.',
      },
      {
        'question': 'How can I contact customer support?',
        'answer':
            'You can reach us at +91 92771 21112 or email support@apsdreamhome.com. Our support team is available Monday to Saturday, 9 AM to 7 PM.',
      },
      {
        'question': 'Do you offer site visits?',
        'answer':
            'Yes, we arrange free guided site visits for all our listed properties. You can schedule a visit through the app or by calling our sales team.',
      },
    ],
    'Booking & Payment': [
      {
        'question': 'How do I book a plot?',
        'answer':
            'Select a plot, pay a token amount (starting from ₹25,000), and our team will guide you through the agreement, payment plan, and registration process.',
      },
      {
        'question': 'What payment methods are accepted?',
        'answer':
            'We accept UPI, net banking, bank transfer (NEFT/RTGS), cheque, and EMI options through our banking partners. Cash payments above ₹2,00,000 are not accepted as per RBI guidelines.',
      },
      {
        'question': 'Can I pay in installments (EMI)?',
        'answer':
            'Yes, we offer flexible EMI plans ranging from 6 to 60 months. Interest rates start from 8.5% p.a. through our partner banks. You can also choose our in-house payment plans.',
      },
      {
        'question': 'Is there a refund policy?',
        'answer':
            'Token amounts are refundable within 7 days of payment. After agreement signing, refunds are processed as per the cancellation policy mentioned in your agreement, minus applicable charges.',
      },
    ],
    'Legal & Documentation': [
      {
        'question': 'What documents are needed to buy a property?',
        'answer':
            'You need a valid photo ID (Aadhaar/PAN), address proof, passport-size photographs, PAN card, and income proof (for EMI). NRI buyers need additional documents like PIO/OCI card.',
      },
      {
        'question': 'Is the property RERA registered?',
        'answer':
            'All our properties are RERA registered. You can verify the registration number on the UP-RERA or respective state RERA website. Our sales team will provide the RERA certificate during booking.',
      },
      {
        'question': 'How is the stamp duty calculated?',
        'answer':
            'Stamp duty varies by state and property type. In UP, it is 7% for males and 5% for females (first property). Registration fee is 1% of the property value. Use our Stamp Duty Calculator in the app for exact amounts.',
      },
      {
        'question': 'What is the registry process?',
        'answer':
            'After full payment, we schedule a sub-registrar appointment. Both buyer and seller must attend with original documents and two witnesses. The process typically takes 1-2 hours and the sale deed is registered the same day.',
      },
    ],
    'Property': [
      {
        'question': 'How do I verify the property details?',
        'answer':
            'All property details including title, area, and approvals are verified by our legal team. You can view the title report, encumbrance certificate, and approved maps in the property documents section.',
      },
      {
        'question': 'Can I visit the property before buying?',
        'answer':
            'Absolutely. We encourage all buyers to visit the property before making a decision. Schedule a free site visit through the app or contact our sales team at +91 92771 21112.',
      },
      {
        'question': 'What is the typical possession timeline?',
        'answer':
            'Ready-to-move plots are available for immediate possession. For under-construction projects, possession is typically within 12-36 months from the date of booking as per the agreement.',
      },
    ],
    'Account & App': [
      {
        'question': 'How do I create an account?',
        'answer':
            'Download the APS Dream Home app, tap Register, and enter your name, phone number, email, and create a password. Verify your phone number via OTP and your account is ready.',
      },
      {
        'question': 'I forgot my password. How do I reset it?',
        'answer':
            'On the login screen, tap Forgot Password, enter your registered email address, and you will receive a password reset link. You can also use the OTP option to reset via your phone number.',
      },
      {
        'question': 'How do I enable two-factor authentication?',
        'answer':
            'Go to Profile > Security > Two-Factor Authentication and tap Enable. You will need an authenticator app like Google Authenticator. Scan the QR code and enter the verification code to complete setup.',
      },
      {
        'question': 'How do I update my profile information?',
        'answer':
            'Go to Profile > Edit Profile. You can update your name, phone number, email address, and profile picture. Changes to email and phone require OTP verification.',
      },
    ],
  };

  @override
  void initState() {
    super.initState();
    _loadFaqs();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _loadFaqs() {
    final allFaqs = _faqData[_selectedCategory] ?? [];
    if (_searchQuery.isEmpty) {
      _filteredFaqs = List.from(allFaqs);
    } else {
      _filteredFaqs = allFaqs.where((faq) {
        final q = faq['question']!.toLowerCase();
        final a = faq['answer']!.toLowerCase();
        final s = _searchQuery.toLowerCase();
        return q.contains(s) || a.contains(s);
      }).toList();
    }
  }

  void _searchAllCategories(String query) {
    setState(() {
      _searchQuery = query;
      if (query.isEmpty) {
        _loadFaqs();
        return;
      }
      _filteredFaqs = [];
      for (final entry in _faqData.entries) {
        for (final faq in entry.value) {
          final q = faq['question']!.toLowerCase();
          final a = faq['answer']!.toLowerCase();
          final s = query.toLowerCase();
          if (q.contains(s) || a.contains(s)) {
            _filteredFaqs.add(faq);
          }
        }
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Help Center')),
      body: Column(
        children: [
          _buildSearchBar(),
          _buildCategoryTabs(),
          Expanded(
            child: _filteredFaqs.isEmpty ? _buildEmptyState() : _buildFaqList(),
          ),
        ],
      ),
      bottomNavigationBar: _buildContactBar(),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
      child: TextField(
        controller: _searchController,
        onChanged: _searchAllCategories,
        style: const TextStyle(fontSize: 14),
        decoration: InputDecoration(
          hintText: 'Search questions...',
          prefixIcon: const Icon(
            Icons.search,
            size: 20,
            color: AppTheme.primaryColor,
          ),
          suffixIcon: _searchQuery.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear, size: 18),
                  onPressed: () {
                    _searchController.clear();
                    _searchAllCategories('');
                  },
                )
              : null,
          filled: true,
          fillColor: AppTheme.surfaceColor,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide.none,
          ),
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 12,
          ),
        ),
      ),
    );
  }

  Widget _buildCategoryTabs() {
    final categories = _faqData.keys.toList();
    return SizedBox(
      height: 50,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        scrollDirection: Axis.horizontal,
        itemCount: categories.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final cat = categories[index];
          final isSelected = _selectedCategory == cat && _searchQuery.isEmpty;
          return GestureDetector(
            onTap: () {
              setState(() {
                _selectedCategory = cat;
                _searchQuery = '';
                _searchController.clear();
                _loadFaqs();
              });
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: isSelected
                    ? AppTheme.primaryColor
                    : AppTheme.primaryColor.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: isSelected
                      ? AppTheme.primaryColor
                      : AppTheme.primaryColor.withValues(alpha: 0.3),
                ),
              ),
              child: Text(
                cat,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: isSelected ? Colors.white : AppTheme.primaryColor,
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildFaqList() {
    return ListView.separated(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      itemCount: _filteredFaqs.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, index) {
        final faq = _filteredFaqs[index];
        return _FaqExpansionTile(
          question: faq['question']!,
          answer: faq['answer']!,
          searchQuery: _searchQuery,
        );
      },
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.help_outline, size: 64, color: Colors.grey.shade300),
            const SizedBox(height: 16),
            const Text(
              'No questions found',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: AppTheme.textPrimaryLight,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Try a different search term or category',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContactBar() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.surfaceColor,
        border: Border(top: BorderSide(color: Colors.grey.shade200)),
      ),
      child: SafeArea(
        top: false,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              "Couldn't find your answer?",
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: AppTheme.textPrimaryLight,
              ),
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () async {
                      final phoneUri = Uri.parse(
                        'tel:${AppConstants.supportPhone}',
                      );
                      if (await canLaunchUrl(phoneUri)) {
                        await launchUrl(
                          phoneUri,
                          mode: LaunchMode.externalApplication,
                        );
                      }
                    },
                    icon: const Icon(Icons.phone_outlined, size: 18),
                    label: const Text('Call Us'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.primaryColor,
                      side: const BorderSide(color: AppTheme.primaryColor),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      final emailUri = Uri.parse(
                        'mailto:support@apsdreamhome.com',
                      );
                      if (await canLaunchUrl(emailUri)) {
                        await launchUrl(
                          emailUri,
                          mode: LaunchMode.externalApplication,
                        );
                      }
                    },
                    icon: const Icon(Icons.email_outlined, size: 18),
                    label: const Text('Email Support'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      elevation: 0,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _FaqExpansionTile extends StatefulWidget {
  final String question;
  final String answer;
  final String searchQuery;

  const _FaqExpansionTile({
    required this.question,
    required this.answer,
    required this.searchQuery,
  });

  @override
  State<_FaqExpansionTile> createState() => _FaqExpansionTileState();
}

class _FaqExpansionTileState extends State<_FaqExpansionTile> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      decoration: BoxDecoration(
        color: _expanded
            ? AppTheme.primaryColor.withValues(alpha: 0.04)
            : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: _expanded
              ? AppTheme.primaryColor.withValues(alpha: 0.3)
              : Colors.grey.shade200,
        ),
        boxShadow: _expanded
            ? [
                BoxShadow(
                  color: AppTheme.primaryColor.withValues(alpha: 0.05),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ]
            : [],
      ),
      child: Column(
        children: [
          GestureDetector(
            onTap: () => setState(() => _expanded = !_expanded),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(
                    _expanded
                        ? Icons.keyboard_arrow_up
                        : Icons.keyboard_arrow_down,
                    color: AppTheme.primaryColor,
                    size: 22,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      widget.question,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.textPrimaryLight,
                        height: 1.4,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          if (_expanded)
            Padding(
              padding: const EdgeInsets.only(left: 50, right: 16, bottom: 16),
              child: Text(
                widget.answer,
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.grey.shade700,
                  height: 1.6,
                ),
              ),
            ),
        ],
      ),
    );
  }
}
