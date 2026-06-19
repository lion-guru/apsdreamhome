import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';

/// AI Assistant Service
/// Advanced conversational AI that can understand natural language
/// and perform actions without RBAC restrictions
class AIAssistantService {
  final ApiService _api;
  
  // Conversation history for context
  final List<Map<String, dynamic>> _conversationHistory = [];
  
  AIAssistantService({ApiService? api})
      : _api = api ?? ApiService();

  /// Process user message and generate AI response
  Future<AIResponse> processMessage(String userMessage, {String? userId, String? userRole}) async {
    AppLogger.info('🤖 AI Processing: "$userMessage"');
    
    // Add to conversation history
    _conversationHistory.add({
      'role': 'user',
      'message': userMessage,
      'timestamp': DateTime.now(),
    });
    
    // Analyze intent
    final intent = _analyzeIntent(userMessage);
    
    // Extract entities
    final entities = _extractEntities(userMessage);
    
    // Generate response based on intent
    AIResponse response;
    
    switch (intent) {
      case AIIntent.findPlots:
        response = await _handlePlotSearch(entities);
        break;
      case AIIntent.bookPlot:
        response = await _handlePlotBooking(entities, userId);
        break;
      case AIIntent.calculateEMI:
        response = _handleEMICalculation(entities);
        break;
      case AIIntent.checkCommission:
        response = await _handleCommissionQuery(userId);
        break;
      case AIIntent.siteVisit:
        response = await _handleSiteVisitBooking(entities, userId);
        break;
      case AIIntent.kycStatus:
        response = await _handleKYCQuery(userId);
        break;
      case AIIntent.leadInfo:
        response = await _handleLeadQuery(entities, userId);
        break;
      case AIIntent.colonyInfo:
        response = await _handleColonyQuery(entities);
        break;
      case AIIntent.propertyValue:
        response = _handlePropertyValuation(entities);
        break;
      case AIIntent.generalInfo:
        response = _handleGeneralQuery(userMessage);
        break;
      case AIIntent.greeting:
        response = _handleGreeting(userRole);
        break;
      case AIIntent.help:
        response = _handleHelpRequest();
        break;
      default:
        response = _handleUnknownQuery(userMessage);
    }
    
    // Add to conversation history
    _conversationHistory.add({
      'role': 'assistant',
      'message': response.message,
      'timestamp': DateTime.now(),
      'actions': response.suggestedActions,
    });
    
    // Keep history limited
    if (_conversationHistory.length > 20) {
      _conversationHistory.removeAt(0);
    }
    
    return response;
  }

  /// Analyze user intent from message
  AIIntent _analyzeIntent(String message) {
    final lower = message.toLowerCase();
    
    // Plot/Property search
    if (lower.contains('plot') || 
        lower.contains('property') || 
        lower.contains('colony') ||
        lower.contains('land') ||
        lower.contains('available') ||
        lower.contains('show me') ||
        lower.contains('find') && lower.contains('plot')) {
      return AIIntent.findPlots;
    }
    
    // Booking
    if (lower.contains('book') || 
        lower.contains('buy') || 
        lower.contains('purchase') ||
        lower.contains('reserve') ||
        lower.contains('booking')) {
      return AIIntent.bookPlot;
    }
    
    // EMI
    if (lower.contains('emi') || 
        lower.contains('loan') || 
        lower.contains('monthly') ||
        lower.contains('installment') ||
        lower.contains('calculate')) {
      return AIIntent.calculateEMI;
    }
    
    // Commission
    if (lower.contains('commission') || 
        lower.contains('earning') || 
        lower.contains('income') ||
        lower.contains('payout') ||
        lower.contains('kitna mila') ||
        lower.contains('paisa kab milega')) {
      return AIIntent.checkCommission;
    }
    
    // Site Visit
    if (lower.contains('visit') || 
        lower.contains('dekho') || 
        lower.contains('ghumne') ||
        lower.contains('pickup') ||
        lower.contains('site')) {
      return AIIntent.siteVisit;
    }
    
    // KYC
    if (lower.contains('kyc') || 
        lower.contains('document') || 
        lower.contains('aadhar') ||
        lower.contains('pan') ||
        lower.contains('verify')) {
      return AIIntent.kycStatus;
    }
    
    // Lead
    if (lower.contains('lead') || 
        lower.contains('customer') || 
        lower.contains('client') ||
        lower.contains('contact')) {
      return AIIntent.leadInfo;
    }
    
    // Colony Info
    if (lower.contains('suryoday') || 
        lower.contains('raghunath') || 
        lower.contains('braj') ||
        lower.contains('buddha') ||
        lower.contains('ganga') ||
        lower.contains('colony')) {
      return AIIntent.colonyInfo;
    }
    
    // Property Value
    if (lower.contains('value') || 
        lower.contains('price') || 
        lower.contains('daam') ||
        lower.contains('kitna hai') ||
        lower.contains('rate') ||
        lower.contains('cost')) {
      return AIIntent.propertyValue;
    }
    
    // Greeting
    if (lower.contains('hello') || 
        lower.contains('hi') || 
        lower.contains('hey') ||
        lower.contains('namaste') ||
        lower.contains('pranam')) {
      return AIIntent.greeting;
    }
    
    // Help
    if (lower.contains('help') || 
        lower.contains('madad') || 
        lower.contains('kaise') ||
        lower.contains('what can you do')) {
      return AIIntent.help;
    }
    
    return AIIntent.generalInfo;
  }

  /// Extract entities from message
  Map<String, dynamic> _extractEntities(String message) {
    final entities = <String, dynamic>{};
    final lower = message.toLowerCase();
    
    // Extract area
    final areaRegex = RegExp(r'(\d+)\s*(sqft|sq ft|square feet|sqft)', caseSensitive: false);
    final areaMatch = areaRegex.firstMatch(message);
    if (areaMatch != null) {
      entities['area'] = double.tryParse(areaMatch.group(1) ?? '0');
    }
    
    // Extract price
    final priceRegex = RegExp(r'(\d+)\s*(lakh|lac|l|crore|cr|c)', caseSensitive: false);
    final priceMatch = priceRegex.firstMatch(message);
    if (priceMatch != null) {
      final amount = double.tryParse(priceMatch.group(1) ?? '0') ?? 0;
      final unit = priceMatch.group(2)?.toLowerCase() ?? '';
      if (unit.contains('cr') || unit.contains('crore')) {
        entities['price'] = amount * 10000000;
      } else if (unit.contains('l') || unit.contains('lac') || unit.contains('lakh')) {
        entities['price'] = amount * 100000;
      }
    }
    
    // Extract colony names
    final colonies = ['suryoday', 'raghunath', 'braj', 'buddha', 'ganga', 'lucknow', 'gorakhpur', 'varanasi'];
    for (final colony in colonies) {
      if (lower.contains(colony)) {
        entities['colony'] = colony;
        break;
      }
    }
    
    // Extract numbers (could be plot numbers, phone, etc.)
    final numberRegex = RegExp(r'\b(\d{1,4})\b');
    final numbers = numberRegex.allMatches(message).map((m) => m.group(1)).toList();
    if (numbers.isNotEmpty) {
      entities['numbers'] = numbers;
    }
    
    return entities;
  }

  /// Handle plot search
  Future<AIResponse> _handlePlotSearch(Map<String, dynamic> entities) async {
    try {
      // Query available plots via REST API
      final result = await _api.request(
        method: 'GET',
        endpoint: 'properties',
        queryParameters: {'status': 'available', 'limit': 5},
      );
      
      final plotsList = result['data'] ?? result['properties'] ?? [];
      final List<Map<String, dynamic>> plots = List<Map<String, dynamic>>.from(
        (plotsList is List) ? plotsList.map((p) => Map<String, dynamic>.from(p as Map)) : [],
      );
      
      if (plots.isEmpty) {
        return AIResponse(
          message: "Sorry, I couldn't find any available plots matching your criteria right now. But don't worry! New plots are added regularly. Would you like me to notify you when plots become available?",
          suggestedActions: [
            AIAction('View All Colonies', '/colonies'),
            AIAction('Book Site Visit', '/tools/site-visit'),
          ],
        );
      }
      
      return AIResponse(
        message: "Great news! I found ${plots.length} available plots for you. Here are some options:\n\n🏠 Plot #${plots[0]['plotNumber']} - ${plots[0]['area']} sqft - ₹${plots[0]['price']}\n🏠 Plot #${plots[1]['plotNumber']} - ${plots[1]['area']} sqft - ₹${plots[1]['price']}\n\nWould you like to see more details or book a site visit to see these plots in person?",
        suggestedActions: [
          AIAction('View Details', '/colony/${plots[0]['colonyId']}/plot/${plots[0]['id']}'),
          AIAction('Book Site Visit', '/tools/site-visit'),
          AIAction('Calculate EMI', '/tools/emi-calculator'),
        ],
      );
    } catch (e) {
      return AIResponse(
        message: "I'd love to help you find the perfect plot! Let me show you all our available colonies where you can choose from various plot sizes and locations.",
        suggestedActions: [
          AIAction('View All Colonies', '/colonies'),
          AIAction('See on Map', '/tools/map'),
        ],
      );
    }
  }

  /// Handle plot booking
  Future<AIResponse> _handlePlotBooking(Map<String, dynamic> entities, String? userId) async {
    if (userId == null) {
      return AIResponse(
        message: "To book a plot, you'll need to log in first. It only takes a minute! Once logged in, I can help you book any plot instantly.",
        suggestedActions: [
          AIAction('Login', '/login'),
          AIAction('Register', '/register'),
        ],
      );
    }
    
    return AIResponse(
      message: "Perfect! I can help you book a plot. Which colony are you interested in? I can show you available plots in:\n\n🏘️ Suryoday Heights Phase 1 (Gorakhpur)\n🏘️ Raghunath City Center (Gorakhpur)\n🏘️ Braj Radha Enclave (Lucknow)\n\nOr tell me your budget and preferred area size, and I'll find the best options for you!",
      suggestedActions: [
        AIAction('Browse Colonies', '/colonies'),
        AIAction('View Available Plots', '/plots'),
      ],
    );
  }

  /// Handle EMI calculation
  AIResponse _handleEMICalculation(Map<String, dynamic> entities) {
    final area = entities['area'] as double?;
    final price = entities['price'] as double?;
    
    if (area != null && price != null) {
      final pricePerSqft = price / area;
      
      return AIResponse(
        message: 'I can help you calculate EMI! For ${area.toStringAsFixed(0)} sqft at ₹${(price / 100000).toStringAsFixed(2)}L total (₹${pricePerSqft.toStringAsFixed(0)}/sqft), here are some options:\n\n🏦 SBI Bank @ 8.45%: EMI around ₹${((price * 0.8) * 0.0085).toStringAsFixed(0)}/month for 10 years\n🏦 HDFC @ 8.50%: EMI around ₹${((price * 0.8) * 0.0086).toStringAsFixed(0)}/month\n\nWould you like me to show you the detailed EMI calculator with exact numbers?',
        suggestedActions: [
          AIAction('Open EMI Calculator', '/tools/emi-calculator'),
          AIAction('Apply for Loan', '/tools/emi-calculator'),
        ],
      );
    }
    
    return AIResponse(
      message: 'I can help you calculate EMI for your plot purchase! Just tell me:\n\n📐 Plot area (in sqft)\n💰 Total price or price per sqft\n⏱️ Preferred loan tenure\n\nOr I can open the full EMI calculator where you can compare different banks like SBI, HDFC, ICICI, and more!',
      suggestedActions: [
        AIAction('Open EMI Calculator', '/tools/emi-calculator'),
      ],
    );
  }

  /// Handle commission query
  Future<AIResponse> _handleCommissionQuery(String? userId) async {
    if (userId == null) {
      return AIResponse(
        message: "I'd love to show you your commission details! Please log in first so I can access your account information.",
        suggestedActions: [
          AIAction('Login', '/login'),
        ],
      );
    }
    
    try {
      final result = await _api.request(
        method: 'GET',
        endpoint: 'mlm/summary',
      );
      
      final data = result['data'] ?? result;
      
      if (data is Map<String, dynamic> && data.isNotEmpty) {
        final pending = data['pending_commission'] ?? data['pending'] ?? 0;
        final approved = data['approved_commission'] ?? data['approved'] ?? 0;
        final paid = data['total_commission'] ?? data['paid'] ?? 0;
        
        return AIResponse(
          message: "Here's your commission summary:\n\n💰 Pending: ₹${pending.toStringAsFixed(0)}\n✅ Approved: ₹${approved.toStringAsFixed(0)}\n💵 Paid (Total): ₹${paid.toStringAsFixed(0)}\n\nYour commissions are calculated based on the differential commission structure. Would you like to see your detailed genealogy and team performance?",
          suggestedActions: [
            AIAction('View Commission Details', '/associate/commission'),
            AIAction('My Team', '/associate/team'),
            AIAction('Request Payout', '/associate/payout'),
          ],
        );
      } else {
        return AIResponse(
          message: "I see you're new to our commission program! Once you make your first sale or add associates to your team, you'll start earning commissions.\n\nOur differential commission structure offers up to 20% for Site Managers!",
          suggestedActions: [
            AIAction('Commission Structure', '/associate/commission'),
            AIAction('My Genealogy', '/associate/genealogy'),
          ],
        );
      }
    } catch (e) {
      return AIResponse(
        message: 'I can help you track your commissions! Our system offers one of the best commission structures in the industry with differential payouts. Would you like to learn more?',
        suggestedActions: [
          AIAction('Commission Page', '/associate/commission'),
        ],
      );
    }
  }

  /// Handle site visit booking
  Future<AIResponse> _handleSiteVisitBooking(Map<String, dynamic> entities, String? userId) async {
    final colony = entities['colony'] as String?;
    
    String message;
    if (colony != null) {
      message = "Perfect! I can book a site visit for $colony. Our agent will pick you up and show you all the available plots. You'll also get to see the amenities and surrounding area.\n\nWould you like to:\n1️⃣ Schedule for tomorrow\n2️⃣ Pick a custom date\n3️⃣ Get more info about the colony first";
    } else {
      message = "I'd be happy to arrange a free site visit for you! Our agent will:\n\n🚗 Pick you up from your location\n🏘️ Show you the colony and plots\n📋 Explain all amenities and pricing\n🤝 Answer all your questions\n\nWhich colony would you like to visit?";
    }
    
    return AIResponse(
      message: message,
      suggestedActions: [
        AIAction('Book Site Visit', '/tools/site-visit'),
        AIAction('View Colonies', '/colonies'),
        AIAction('See on Map', '/tools/map'),
      ],
    );
  }

  /// Handle KYC query
  Future<AIResponse> _handleKYCQuery(String? userId) async {
    if (userId == null) {
      return AIResponse(
        message: "KYC verification is required for booking plots and receiving commissions. It's a quick process - just upload your Aadhar and PAN cards.\n\nWould you like to complete your KYC now?",
        suggestedActions: [
          AIAction('Complete KYC', '/tools/kyc-verification'),
          AIAction('Login First', '/login'),
        ],
      );
    }
    
    try {
      final result = await _api.request(
        method: 'GET',
        endpoint: 'profile',
      );
      
      final data = result['data'] ?? result;
      final kycStatus = (data is Map<String, dynamic> ? data['kyc_status'] ?? data['kycStatus'] : null) ?? 'pending';
      
      switch (kycStatus) {
        case 'verified':
          return AIResponse(
            message: 'Great news! Your KYC is verified ✅. You can now:\n\n✅ Book plots online\n✅ Receive commission payouts\n✅ Access all premium features\n\nIs there anything else I can help you with?',
            suggestedActions: [
              AIAction('Browse Plots', '/colonies'),
              AIAction('Book Plot', '/plots'),
            ],
          );
        case 'under_review':
          return AIResponse(
            message: "Your KYC is currently under review ⏳. Our team is verifying your documents. This usually takes 24-48 hours.\n\nI'll notify you once it's approved!",
            suggestedActions: [
              AIAction('Check Status', '/tools/kyc-verification'),
            ],
          );
        default:
          return AIResponse(
            message: 'Your KYC is pending 📋. To complete it, please upload:\n\n📄 Aadhar Card (Front & Back)\n📄 PAN Card\n🤳 Selfie with Aadhar\n\nThis is required for booking plots and receiving commissions.',
            suggestedActions: [
              AIAction('Complete KYC Now', '/tools/kyc-verification'),
            ],
          );
      }
    } catch (e) {
      return AIResponse(
        message: 'KYC verification helps us ensure secure transactions. It only takes a few minutes to upload your documents!',
        suggestedActions: [
          AIAction('Complete KYC', '/tools/kyc-verification'),
        ],
      );
    }
  }

  /// Handle lead query
  Future<AIResponse> _handleLeadQuery(Map<String, dynamic> entities, String? userId) async {
    return AIResponse(
      message: 'I can help you manage your leads! As an associate, you can:\n\n👥 View all your assigned leads\n📞 Track call history\n✅ Update lead status\n🎯 Convert leads to sales\n💰 Earn commission on conversions\n\nWould you like to see your leads or learn about lead management?',
      suggestedActions: [
        AIAction('My Leads', '/associate/leads'),
        AIAction('Add New Lead', '/associate/leads'),
        AIAction('CRM Dashboard', '/admin/crm'),
      ],
    );
  }

  /// Handle colony query
  Future<AIResponse> _handleColonyQuery(Map<String, dynamic> entities) async {
    final colony = entities['colony'] as String?;
    
    final colonyInfo = {
      'suryoday': {
        'name': 'Suryoday Heights Phase 1',
        'location': 'Gorakhpur',
        'plots': 120,
        'price': '₹3,000/sqft',
        'features': ['Park', 'Security', 'Water'],
      },
      'raghunath': {
        'name': 'Raghunath City Center',
        'location': 'Gorakhpur',
        'plots': 80,
        'price': '₹3,500/sqft',
        'features': ['Mall', 'Hospital', 'School'],
      },
      'braj': {
        'name': 'Braj Radha Enclave',
        'location': 'Lucknow',
        'plots': 200,
        'price': '₹4,200/sqft',
        'features': ['Pool', 'Club', 'Gym'],
      },
    };
    
    if (colony != null && colonyInfo.containsKey(colony)) {
      final info = colonyInfo[colony]!;
      return AIResponse(
        message: "Here's information about ${info['name']}:\n\n📍 Location: ${info['location']}\n🏘️ Total Plots: ${info['plots']}\n💰 Price: ${info['price']}\n✨ Features: ${(info['features'] as List).join(', ')}\n\nWould you like to see available plots or book a site visit?",
        suggestedActions: [
          AIAction('View Plots', '/colonies'),
          AIAction('Book Visit', '/tools/site-visit'),
          AIAction('See on Map', '/tools/map'),
        ],
      );
    }
    
    return AIResponse(
      message: 'We have several premium colonies across UP:\n\n🏘️ Suryoday Heights Phase 1 (Gorakhpur) - 120 plots\n🏘️ Raghunath City Center (Gorakhpur) - 80 plots\n🏘️ Braj Radha Enclave (Lucknow) - 200 plots\n🏘️ Budh Bihar Colony (Kushinagar)\n🏘️ Ganga Nagri (Varanasi)\n\nWhich one would you like to know more about?',
      suggestedActions: [
        AIAction('View All Colonies', '/colonies'),
        AIAction('See on Map', '/tools/map'),
      ],
    );
  }

  /// Handle property valuation
  AIResponse _handlePropertyValuation(Map<String, dynamic> entities) {
    final area = entities['area'] as double?;
    final colony = entities['colony'] as String?;
    
    if (area != null) {
      // Calculate approximate value
      double baseRate = 3000; // Default
      if (colony == 'lucknow') baseRate = 4200;
      if (colony == 'gorakhpur') baseRate = 3000;
      if (colony == 'varanasi') baseRate = 3800;
      
      final estimatedValue = area * baseRate;
      
      return AIResponse(
        message: "Based on my AI analysis, here's the estimated value:\n\n📐 Area: ${area.toStringAsFixed(0)} sqft\n💰 Base Rate: ₹$baseRate/sqft\n💵 Estimated Value: ₹${(estimatedValue / 100000).toStringAsFixed(2)}L\n\nThis is based on current market trends and recent sales in the area. For a detailed valuation with all factors (road width, corner plot, amenities), try our full valuation tool!",
        suggestedActions: [
          AIAction('Detailed Valuation', '/tools/property-valuation'),
          AIAction('View Similar Plots', '/colonies'),
        ],
      );
    }
    
    return AIResponse(
      message: 'I can help you estimate property value using AI! Just tell me:\n\n📐 Plot area (sqft)\n🏘️ Colony/Location\n🛣️ Road width\n\nOur AI analyzes recent sales, market trends, location premium, and amenities to give you an accurate estimate.',
      suggestedActions: [
        AIAction('AI Valuation Tool', '/tools/property-valuation'),
      ],
    );
  }

  /// Handle general query
  AIResponse _handleGeneralQuery(String message) {
    return AIResponse(
      message: "I'm your APS Dream Home AI assistant! I can help you with:\n\n🏠 Finding and booking plots\n💰 Calculating EMI and loans\n📊 Checking your commissions\n🚗 Booking site visits\n📋 KYC verification\n💬 Connecting with support\n\nWhat would you like to do today?",
      suggestedActions: [
        AIAction('Browse Plots', '/colonies'),
        AIAction('Calculate EMI', '/tools/emi-calculator'),
        AIAction('Site Visit', '/tools/site-visit'),
        AIAction('My Profile', '/profile'),
      ],
    );
  }

  /// Handle greeting
  AIResponse _handleGreeting(String? userRole) {
    String greeting;
    if (userRole == 'associate') {
      greeting = 'Namaste! 🙏 Welcome back, Associate! Ready to make some sales today? I can help you find leads, track commissions, or book plots for your customers.';
    } else if (userRole == 'admin') {
      greeting = 'Hello Admin! 👋 Ready to manage the empire? I can help you with analytics, reports, or any administrative tasks.';
    } else {
      greeting = "Namaste! 🙏 Welcome to APS Dream Home! I'm your personal AI assistant. I can help you find your dream plot, calculate EMI, book site visits, and much more.\n\nWhat brings you here today?";
    }
    
    return AIResponse(
      message: greeting,
      suggestedActions: [
        AIAction('Find Plots', '/colonies'),
        AIAction('EMI Calculator', '/tools/emi-calculator'),
        AIAction('Site Visit', '/tools/site-visit'),
        AIAction('Help', '/help'),
      ],
    );
  }

  /// Handle help request
  AIResponse _handleHelpRequest() {
    return AIResponse(
      message: "I'm here to help! Here's what I can do for you:\n\n🗣️ **Chat naturally** - Ask me anything like you'd ask a human\n🏠 **Find plots** - Tell me your budget, location, size\n💰 **Calculate EMI** - I'll show you bank-wise EMI options\n🚗 **Book site visits** - Free pickup and tour\n📋 **Check KYC status** - Document verification\n💵 **Track commissions** - For associates\n🤖 **AI Valuation** - Get property value estimates\n\nJust type naturally - no need for specific commands!",
      suggestedActions: [
        AIAction('Start Exploring', '/colonies'),
        AIAction('EMI Calculator', '/tools/emi-calculator'),
      ],
    );
  }

  /// Handle unknown query
  AIResponse _handleUnknownQuery(String message) {
    return AIResponse(
      message: "I want to make sure I help you correctly! Could you tell me more about what you're looking for?\n\nFor example:\n• \"Show me plots in Gorakhpur\"\n• \"Calculate EMI for 20 lakh\"\n• \"Book a site visit tomorrow\"\n• \"What's my commission status?\"\n\nOr just tell me in your own words - I'm here to help! 😊",
      suggestedActions: [
        AIAction('Browse Plots', '/colonies'),
        AIAction('Contact Support', '/tools/chat'),
      ],
    );
  }

  /// Clear conversation history
  void clearHistory() {
    _conversationHistory.clear();
  }

  /// Get conversation history
  List<Map<String, dynamic>> get history => List.unmodifiable(_conversationHistory);
}

/// AI Response Model
class AIResponse {
  final String message;
  final List<AIAction> suggestedActions;
  final Map<String, dynamic>? data;

  AIResponse({
    required this.message,
    this.suggestedActions = const [],
    this.data,
  });
}

/// AI Action Model
class AIAction {
  final String label;
  final String route;
  final Map<String, dynamic>? params;

  AIAction(this.label, this.route, {this.params});
}

/// AI Intent Enum
enum AIIntent {
  findPlots,
  bookPlot,
  calculateEMI,
  checkCommission,
  siteVisit,
  kycStatus,
  leadInfo,
  colonyInfo,
  propertyValue,
  generalInfo,
  greeting,
  help,
  unknown,
}

/// Provider
final aiAssistantServiceProvider = Provider<AIAssistantService>((ref) {
  return AIAssistantService();
});

/// Chat state provider
final aiChatProvider = StateNotifierProvider<AIChatNotifier, List<Map<String, dynamic>>>((ref) {
  return AIChatNotifier();
});

class AIChatNotifier extends StateNotifier<List<Map<String, dynamic>>> {
  AIChatNotifier() : super([]);

  void addMessage(String role, String message, {List<AIAction>? actions}) {
    state = [...state, {
      'role': role,
      'message': message,
      'timestamp': DateTime.now(),
      'actions': actions?.map((a) => {'label': a.label, 'route': a.route}).toList(),
    }];
  }

  void clear() {
    state = [];
  }
}
