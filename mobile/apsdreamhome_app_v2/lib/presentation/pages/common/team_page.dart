import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class TeamPage extends StatefulWidget {
  const TeamPage({super.key});

  @override
  State<TeamPage> createState() => _TeamPageState();
}

class _TeamPageState extends State<TeamPage>
    with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeOut),
    );
    _slideAnimation =
        Tween<Offset>(begin: const Offset(0, 0.2), end: Offset.zero).animate(
          CurvedAnimation(
            parent: _animationController,
            curve: Curves.easeOutCubic,
          ),
        );
    _animationController.forward();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  static const _teamMembers = [
    _TeamMember(
      'Abhaay Singh',
      'Founder & Director',
      'Founder and main director of APS Dream Home. Leading strategic operations, land acquisition, and technology-driven growth across Eastern UP. 15+ years in real estate development.',
      Icons.business_rounded,
      Color(0xFF1A237E),
      linkedIn: 'https://www.linkedin.com/in/abhaay-singh-867944210/',
      twitter: 'https://twitter.com/abhaaysingh',
      email: 'abhaay@apsdreamhome.com',
      phone: '+91-9918061919',
      initials: 'AS',
      expertise: [
        'Land Acquisition',
        'Strategic Planning',
        'Business Development',
        'AI in Real Estate',
      ],
    ),
    _TeamMember(
      'Praveen Prabhat',
      'Senior Property Advisor',
      'Expert in land registry, property verification, and acquisition. Core advisor on all land-related matters and legal documentation. 20+ years experience in property law.',
      Icons.balance_rounded,
      Color(0xFF283593),
      linkedIn: 'https://linkedin.com/in/praveenprabhat',
      email: 'praveen@apsdreamhome.com',
      initials: 'PP',
      expertise: [
        'Land Registry',
        'Property Verification',
        'Legal Documentation',
        'Title Search',
      ],
    ),
    _TeamMember(
      'Pramod Sharma',
      'Head of Marketing & Sales',
      'Driving brand growth through innovative marketing strategies. Expert in real estate marketing, lead generation, and digital campaigns. Built 5000+ customer base.',
      Icons.campaign_rounded,
      Color(0xFF3949AB),
      linkedIn: 'https://linkedin.com/in/pramodsharma',
      twitter: 'https://twitter.com/pramodsharma_re',
      email: 'pramod@apsdreamhome.com',
      initials: 'PS',
      expertise: [
        'Digital Marketing',
        'Lead Generation',
        'Brand Strategy',
        'Sales Operations',
      ],
    ),
    _TeamMember(
      'Anuj Srivastava',
      'Head of Finance & Accounts',
      'Managing financial operations, EMI tracking, TDS/GST compliance, and ensuring transparent accounting for all stakeholders. CA with 12+ years experience.',
      Icons.account_balance_rounded,
      Color(0xFF5C6BC0),
      linkedIn: 'https://linkedin.com/in/anuj-srivastava-ca',
      email: 'anuj@apsdreamhome.com',
      initials: 'AS',
      expertise: [
        'Financial Planning',
        'Tax Compliance',
        'EMI Management',
        'Audit & Assurance',
      ],
    ),
    _TeamMember(
      'Shushant Srivastava',
      'Head of Legal & Compliance',
      'Expert in property law, RERA compliance, title verification, and documentation. Every deal is legally sound. Supreme Court practicing advocate.',
      Icons.gavel_rounded,
      Color(0xFF7986CB),
      linkedIn: 'https://linkedin.com/in/shushantsrivastava-law',
      email: 'shushant@apsdreamhome.com',
      initials: 'SS',
      expertise: [
        'Property Law',
        'RERA Compliance',
        'Title Verification',
        'Litigation',
      ],
    ),
    _TeamMember(
      'Vijay Verma',
      'CTO & Head of IT and AI',
      'Building next-gen real estate platform with AI-powered tools, automation, and data-driven insights. Ex-Microsoft, 18+ years in tech.',
      Icons.computer_rounded,
      Color(0xFF9FA8DA),
      linkedIn: 'https://linkedin.com/in/vijayverma-tech',
      twitter: 'https://twitter.com/vijayverma_ai',
      email: 'vijay@apsdreamhome.com',
      initials: 'VV',
      expertise: [
        'AI/ML',
        'Platform Architecture',
        'Mobile Development',
        'Data Analytics',
      ],
    ),
    _TeamMember(
      'Rachna Gupta',
      'Head of Customer Relations',
      'Empowering customers with world-class support. Leading the Nari Shakti women empowerment initiative. 10+ years in customer success.',
      Icons.star_rounded,
      Color(0xFFCE93D8),
      linkedIn: 'https://linkedin.com/in/rachna-gupta-cs',
      email: 'rachna@apsdreamhome.com',
      initials: 'RG',
      expertise: [
        'Customer Success',
        'Nari Shakti Program',
        'Grievance Redressal',
        'Retention',
      ],
    ),
    _TeamMember(
      'Praveen Singh',
      'Senior Advisor',
      'Seasoned advisor with deep expertise in real estate markets, business strategy, and market expansion. Former Director at major developer.',
      Icons.lightbulb_rounded,
      Color(0xFFB39DDB),
      linkedIn: 'https://linkedin.com/in/praveensingh-advisor',
      email: 'praveen.singh@apsdreamhome.com',
      initials: 'PS',
      expertise: [
        'Market Strategy',
        'Business Expansion',
        'Investment Advisory',
        'Partnerships',
      ],
    ),
  ];

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: CustomScrollView(
            slivers: [
              SliverToBoxAdapter(child: _buildHeader(context)),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 40),
                sliver: SliverGrid(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    childAspectRatio: 0.78,
                    crossAxisSpacing: 16,
                    mainAxisSpacing: 16,
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (context, index) => FadeTransition(
                      opacity: _fadeAnimation,
                      child: SlideTransition(
                        position:
                            Tween<Offset>(
                              begin: const Offset(0, 0.3),
                              end: Offset.zero,
                            ).animate(
                              CurvedAnimation(
                                parent: _animationController,
                                curve: Interval(
                                  index * 0.1,
                                  0.6 + index * 0.05,
                                  curve: Curves.easeOutCubic,
                                ),
                              ),
                            ),
                        child: _buildTeamCard(_teamMembers[index]),
                      ),
                    ),
                    childCount: _teamMembers.length,
                  ),
                ),
              ),
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 20,
                  ),
                  child: _buildCTASection(context),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
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
          const SizedBox(height: 16),
          TweenAnimationBuilder<double>(
            tween: Tween(begin: 0.8, end: 1.0),
            duration: const Duration(milliseconds: 600),
            curve: Curves.elasticOut,
            builder: (context, scale, child) => Transform.scale(
              scale: scale,
              child: Container(
                width: 88,
                height: 88,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                  ),
                  borderRadius: BorderRadius.circular(22),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primaryColor.withValues(alpha: 0.4),
                      blurRadius: 24,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: const Icon(
                  Icons.groups_rounded,
                  size: 44,
                  color: Colors.white,
                ),
              ),
            ),
          ),
          const SizedBox(height: 18),
          ShaderMask(
            shaderCallback: (bounds) => const LinearGradient(
              colors: [AppTheme.primaryColor, AppTheme.accentColor],
            ).createShader(bounds),
            child: Text(
              'Our Leadership',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
                letterSpacing: -0.5,
              ),
            ),
          ),
          const SizedBox(height: 10),
          Text(
            'Meet the passionate professionals driving APS Dream Home forward',
            style: Theme.of(
              context,
            ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 18),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            decoration: BoxDecoration(
              color: AppTheme.accentColor.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(30),
              border: Border.all(
                color: AppTheme.accentColor.withValues(alpha: 0.3),
              ),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  Icons.verified_rounded,
                  color: AppTheme.accentColor,
                  size: 16,
                ),
                const SizedBox(width: 8),
                Text(
                  '8 Core Members • 75+ Years Combined Experience',
                  style: TextStyle(
                    color: AppTheme.accentColor,
                    fontWeight: FontWeight.w600,
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

  Widget _buildTeamCard(_TeamMember member) {
    return GestureDetector(
      onTap: () => _showMemberBottomSheet(context, member),
      child: GlassCard(
        opacity: 0.1,
        blur: 10,
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Hero(
              tag: 'avatar_${member.name}',
              child: Container(
                width: 88,
                height: 88,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [member.color, member.color.withValues(alpha: 0.7)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(22),
                  boxShadow: [
                    BoxShadow(
                      color: member.color.withValues(alpha: 0.35),
                      blurRadius: 18,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: Center(
                  child: Text(
                    member.initials,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w800,
                      fontSize: 28,
                      letterSpacing: 1,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 14),
            Text(
              member.name,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w700,
                fontSize: 15,
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 4),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
              decoration: BoxDecoration(
                color: member.color.withValues(alpha: 0.18),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: member.color.withValues(alpha: 0.3)),
              ),
              child: Text(
                member.role,
                style: TextStyle(
                  color: member.color,
                  fontWeight: FontWeight.w600,
                  fontSize: 10.5,
                ),
                textAlign: TextAlign.center,
              ),
            ),
            const SizedBox(height: 10),
            if (member.linkedIn != null)
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _SocialIcon(
                    icon: Icons.work_rounded,
                    color: const Color(0xFF0A66C2),
                    onTap: () => _launchUrl(member.linkedIn!),
                    tooltip: 'LinkedIn',
                  ),
                  if (member.twitter != null) ...[
                    const SizedBox(width: 8),
                    _SocialIcon(
                      icon: Icons.alternate_email_rounded,
                      color: const Color(0xFF1DA1F2),
                      onTap: () => _launchUrl(member.twitter!),
                      tooltip: 'Twitter/X',
                    ),
                  ],
                  const SizedBox(width: 8),
                  _SocialIcon(
                    icon: Icons.email_rounded,
                    color: const Color(0xFFEA4335),
                    onTap: () => _launchUrl('mailto:${member.email}'),
                    tooltip: 'Email',
                  ),
                ],
              ),
            const SizedBox(height: 8),
            Flexible(
              child: Text(
                member.bio,
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.65),
                  fontSize: 10.5,
                  height: 1.35,
                ),
                textAlign: TextAlign.center,
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Tap to view details →',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.4),
                fontSize: 10,
                fontStyle: FontStyle.italic,
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showMemberBottomSheet(BuildContext context, _TeamMember member) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) =>
          _MemberDetailSheet(member: member, onLaunchUrl: _launchUrl),
    );
  }

  Widget _buildCTASection(BuildContext context) {
    return GlassCard(
      opacity: 0.15,
      blur: 12,
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  AppTheme.accentColor.withValues(alpha: 0.2),
                  AppTheme.accentColor.withValues(alpha: 0.05),
                ],
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(
              Icons.handshake_rounded,
              color: AppTheme.accentColor,
              size: 36,
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'Want to Join Our Team?',
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w700,
              fontSize: 18,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'We are always looking for passionate professionals to join our mission.',
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.6),
              fontSize: 13,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 18),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: DecoratedBox(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                gradient: const LinearGradient(
                  colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                ),
                boxShadow: [
                  BoxShadow(
                    color: AppTheme.primaryColor.withValues(alpha: 0.4),
                    blurRadius: 14,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: ElevatedButton(
                onPressed: () => context.push('/careers'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                child: const Text(
                  'View Open Positions',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
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

class _SocialIcon extends StatelessWidget {
  final IconData icon;
  final Color color;
  final VoidCallback onTap;
  final String tooltip;

  const _SocialIcon({
    required this.icon,
    required this.color,
    required this.onTap,
    required this.tooltip,
  });

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(8),
          child: Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: color.withValues(alpha: 0.3)),
            ),
            child: Icon(icon, color: color, size: 16),
          ),
        ),
      ),
    );
  }
}

class _MemberDetailSheet extends StatelessWidget {
  final _TeamMember member;
  final Function(String) onLaunchUrl;

  const _MemberDetailSheet({required this.member, required this.onLaunchUrl});

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.75,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      builder: (context, scrollController) => Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0xFF1A1A2E), Color(0xFF16213E), Color(0xFF0F0F23)],
          ),
          borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: SingleChildScrollView(
          controller: scrollController,
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              Container(
                width: 50,
                height: 5,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
              const SizedBox(height: 24),
              Hero(
                tag: 'avatar_${member.name}',
                child: Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        member.color,
                        member.color.withValues(alpha: 0.7),
                      ],
                    ),
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: member.color.withValues(alpha: 0.4),
                        blurRadius: 24,
                        offset: const Offset(0, 10),
                      ),
                    ],
                  ),
                  child: Center(
                    child: Text(
                      member.initials,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w800,
                        fontSize: 36,
                        letterSpacing: 1.5,
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Text(
                member.name,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 24,
                  letterSpacing: -0.3,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: member.color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: member.color.withValues(alpha: 0.4),
                  ),
                ),
                child: Text(
                  member.role,
                  style: TextStyle(
                    color: member.color,
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                  ),
                ),
              ),
              const SizedBox(height: 24),
              if (member.linkedIn != null ||
                  member.twitter != null ||
                  member.email != null ||
                  member.phone != null)
                _buildSocialRow(),
              const SizedBox(height: 24),
              _buildSection('About', member.bio),
              const SizedBox(height: 20),
              if (member.expertise.isNotEmpty) _buildExpertiseChips(),
              const SizedBox(height: 24),
              if (member.email != null)
                _buildActionButton(
                  'Send Email',
                  Icons.email_rounded,
                  const Color(0xFFEA4335),
                  () => onLaunchUrl('mailto:${member.email}'),
                ),
              if (member.phone != null) ...[
                const SizedBox(height: 12),
                _buildActionButton(
                  'Call',
                  Icons.phone_rounded,
                  const Color(0xFF4CAF50),
                  () => onLaunchUrl('tel:${member.phone}'),
                ),
              ],
              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSocialRow() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (member.linkedIn != null)
          _SocialActionButton(
            icon: Icons.work_rounded,
            label: 'LinkedIn',
            color: const Color(0xFF0A66C2),
            onTap: () => onLaunchUrl(member.linkedIn!),
          ),
        if (member.twitter != null) ...[
          const SizedBox(width: 12),
          _SocialActionButton(
            icon: Icons.alternate_email_rounded,
            label: 'Twitter/X',
            color: const Color(0xFF1DA1F2),
            onTap: () => onLaunchUrl(member.twitter!),
          ),
        ],
        if (member.email != null) ...[
          const SizedBox(width: 12),
          _SocialActionButton(
            icon: Icons.email_rounded,
            label: 'Email',
            color: const Color(0xFFEA4335),
            onTap: () => onLaunchUrl('mailto:${member.email}'),
          ),
        ],
      ],
    );
  }

  Widget _buildExpertiseChips() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Areas of Expertise',
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w700,
            fontSize: 16,
          ),
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 10,
          runSpacing: 10,
          children: member.expertise
              .map(
                (skill) => Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.07),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: Colors.white.withValues(alpha: 0.12),
                    ),
                  ),
                  child: Text(
                    skill,
                    style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.9),
                      fontWeight: FontWeight.w500,
                      fontSize: 12,
                    ),
                  ),
                ),
              )
              .toList(),
        ),
      ],
    );
  }

  Widget _buildSection(String title, String content) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w700,
            fontSize: 16,
          ),
        ),
        const SizedBox(height: 10),
        Text(
          content,
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.75),
            fontSize: 14,
            height: 1.5,
          ),
        ),
      ],
    );
  }

  Widget _buildActionButton(
    String label,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return SizedBox(
      width: double.infinity,
      height: 50,
      child: DecoratedBox(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(14),
          gradient: LinearGradient(
            colors: [color, color.withValues(alpha: 0.8)],
          ),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.35),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: ElevatedButton(
          onPressed: onTap,
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.transparent,
            shadowColor: Colors.transparent,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(14),
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: Colors.white, size: 20),
              const SizedBox(width: 10),
              Text(
                label,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 15,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SocialActionButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _SocialActionButton({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.15),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: color.withValues(alpha: 0.4)),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: color, size: 18),
              const SizedBox(width: 8),
              Text(
                label,
                style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _TeamMember {
  final String name;
  final String role;
  final String bio;
  final IconData icon;
  final Color color;
  final String? linkedIn;
  final String? twitter;
  final String? email;
  final String? phone;
  final String initials;
  final List<String> expertise;

  const _TeamMember(
    this.name,
    this.role,
    this.bio,
    this.icon,
    this.color, {
    this.linkedIn,
    this.twitter,
    this.email,
    this.phone,
    required this.initials,
    required this.expertise,
  });
}
