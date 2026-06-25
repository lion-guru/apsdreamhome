import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class ProfilePage extends ConsumerStatefulWidget {
  const ProfilePage({super.key});

  @override
  ConsumerState<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends ConsumerState<ProfilePage> {
  bool _isLoadingProfile = true;
  bool _isEditingPersonal = false;

  String _apiName = '';
  String _apiEmail = '';
  String _apiPhone = '';
  String _apiRank = '';
  String _apiAvatar = '';
  String _apiReferralCode = '';

  int _totalProperties = 0;
  int _myBookings = 0;
  int _savedCount = 0;

  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();

  bool _hasBankDetails = false;
  String _bankName = '';
  String _bankAccountLast4 = '';
  String _bankIfsc = '';
  String _bankUpi = '';

  bool _hasAddress = false;
  String _addressLine1 = '';
  String _addressCity = '';
  String _addressState = '';
  String _addressPincode = '';

  String _kycStatus = 'not_started';

  @override
  void initState() {
    super.initState();
    _loadProfileData();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _loadProfileData() async {
    setState(() => _isLoadingProfile = true);

    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('user/profile');
      final data = response['data'] ?? response;

      if (mounted) {
        setState(() {
          _apiName = data['name']?.toString() ?? '';
          _apiEmail = data['email']?.toString() ?? '';
          _apiPhone = data['phone']?.toString() ?? '';
          _apiRank = data['rank']?.toString() ?? data['role']?.toString() ?? '';
          _apiAvatar = data['avatar']?.toString() ??
              data['profile_image']?.toString() ??
              '';
          _apiReferralCode = data['referral_code']?.toString() ?? '';
          _totalProperties = _parseInt(
            data['total_properties'] ?? data['properties_count'] ?? 0,
          );
          _myBookings = _parseInt(
            data['my_bookings'] ?? data['bookings_count'] ?? 0,
          );
          _savedCount = _parseInt(
            data['saved_count'] ?? data['favorites_count'] ?? 0,
          );
        });
      }
    } catch (_) {
      final user = ref.read(authProvider);
      if (user != null && mounted) {
        setState(() {
          _apiName = user.name;
          _apiEmail = user.email;
          _apiPhone = user.phone ?? '';
          _apiRank = user.rank;
          _apiAvatar = user.avatar ?? '';
          _apiReferralCode = user.referralCode ?? '';
        });
      }
    }

    // Fire-and-forget sub-loads
    _loadBankDetails();
    _loadAddress();
    _loadKycStatus();

    if (mounted) setState(() => _isLoadingProfile = false);
  }

  Future<void> _loadBankDetails() async {
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('user/bank-details');
      final data = response['data'] ?? response;
      if (mounted && data is Map<String, dynamic>) {
        setState(() {
          _hasBankDetails = true;
          _bankName = data['bank_name']?.toString() ?? '';
          final acct = data['account_number']?.toString() ?? '';
          _bankAccountLast4 =
              acct.length > 4 ? acct.substring(acct.length - 4) : acct;
          _bankIfsc =
              data['ifsc_code']?.toString() ?? data['ifsc']?.toString() ?? '';
          _bankUpi = data['upi_id']?.toString() ?? '';
        });
      }
    } catch (_) {}
  }

  Future<void> _loadAddress() async {
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('user/addresses');
      final data = response['data'] ?? response;
      if (mounted && data is Map<String, dynamic>) {
        setState(() {
          _hasAddress = true;
          _addressLine1 =
              data['address_line1']?.toString() ?? data['address']?.toString() ?? '';
          _addressCity = data['city']?.toString() ?? '';
          _addressState = data['state']?.toString() ?? '';
          _addressPincode =
              data['pincode']?.toString() ?? data['zip_code']?.toString() ?? '';
        });
      } else if (mounted && data is List && data.isNotEmpty) {
        final first = data.first as Map<String, dynamic>;
        setState(() {
          _hasAddress = true;
          _addressLine1 =
              first['address_line1']?.toString() ?? first['address']?.toString() ?? '';
          _addressCity = first['city']?.toString() ?? '';
          _addressState = first['state']?.toString() ?? '';
          _addressPincode =
              first['pincode']?.toString() ?? first['zip_code']?.toString() ?? '';
        });
      }
    } catch (_) {}
  }

  Future<void> _loadKycStatus() async {
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('user/kyc/status');
      final data = response['data'] ?? response;
      if (mounted && data is Map<String, dynamic>) {
        setState(() => _kycStatus = data['status']?.toString() ?? 'not_started');
      }
    } catch (_) {}
  }

  Future<void> _updateProfile() async {
    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final phone = _phoneController.text.trim();

    if (name.isEmpty || email.isEmpty) {
      _showSnackBar('Name and email are required', isError: true);
      return;
    }

    try {
      final api = ref.read(apiServiceProvider);
      await api.put('user/profile', data: {
        'name': name,
        'email': email,
        'phone': phone,
      });
      if (mounted) {
        setState(() {
          _apiName = name;
          _apiEmail = email;
          _apiPhone = phone;
          _isEditingPersonal = false;
        });
      }
      _showSnackBar('Profile updated successfully');
    } catch (e) {
      _showSnackBar('Failed to update profile', isError: true);
    }
  }

  int _parseInt(dynamic value) {
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  void _showSnackBar(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppTheme.errorColor : AppTheme.successColor,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  Color _getRankColor(String rank) {
    switch (rank.toLowerCase()) {
      case 'president':
        return const Color(0xFFFFD700);
      case 'vice president':
        return const Color(0xFFFF8C00);
      case 'site manager':
        return const Color(0xFF9C27B0);
      case 'sr. bdm':
      case 'sr bdm':
        return const Color(0xFF2196F3);
      case 'bdm':
        return const Color(0xFF00BCD4);
      case 'sr. associate':
      case 'sr associate':
        return AppTheme.successColor;
      case 'associate':
        return AppTheme.secondaryColor;
      case 'admin':
        return AppTheme.errorColor;
      case 'agent':
        return const Color(0xFF673AB7);
      case 'employee':
        return const Color(0xFF795548);
      default:
        return AppTheme.primaryColor;
    }
  }

  IconData _getRankIcon(String rank) {
    switch (rank.toLowerCase()) {
      case 'president':
        return Icons.emoji_events;
      case 'vice president':
        return Icons.workspace_premium;
      case 'site manager':
        return Icons.engineering;
      case 'sr. bdm':
      case 'sr bdm':
        return Icons.star;
      case 'bdm':
        return Icons.trending_up;
      case 'sr. associate':
      case 'sr associate':
        return Icons.person_add;
      case 'associate':
        return Icons.group;
      case 'admin':
        return Icons.admin_panel_settings;
      case 'agent':
        return Icons.business_center;
      case 'employee':
        return Icons.work;
      default:
        return Icons.person;
    }
  }

  // ─── Build ───────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authProvider);
    final displayName = _apiName.isNotEmpty ? _apiName : (user?.name ?? 'User');
    final displayEmail = _apiEmail.isNotEmpty ? _apiEmail : (user?.email ?? '');
    final displayPhone = _apiPhone.isNotEmpty ? _apiPhone : (user?.phone ?? '');
    final displayRank = _apiRank.isNotEmpty ? _apiRank : (user?.rank ?? '');
    final displayAvatar =
        _apiAvatar.isNotEmpty ? _apiAvatar : (user?.avatar);
    final displayReferral = _apiReferralCode.isNotEmpty
        ? _apiReferralCode
        : (user?.referralCode ?? '');

    return RefreshIndicator(
      onRefresh: _loadProfileData,
      color: AppTheme.primaryColor,
      child: _isLoadingProfile
          ? const Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _ProfileHeader(
                    name: displayName,
                    email: displayEmail,
                    rank: displayRank,
                    avatar: displayAvatar,
                    rankColor: _getRankColor(displayRank),
                    rankIcon: _getRankIcon(displayRank),
                    onAvatarEdit: _onAvatarEdit,
                  ),
                  const SizedBox(height: 20),
                  _QuickStatsRow(
                    properties: _totalProperties,
                    bookings: _myBookings,
                    saved: _savedCount,
                  ),
                  const SizedBox(height: 20),
                  _PersonalInfoSection(
                    name: displayName,
                    email: displayEmail,
                    phone: displayPhone,
                    isEditing: _isEditingPersonal,
                    nameController: _nameController,
                    emailController: _emailController,
                    phoneController: _phoneController,
                    onToggleEdit: () {
                      setState(() {
                        _isEditingPersonal = !_isEditingPersonal;
                        if (_isEditingPersonal) {
                          _nameController.text = displayName;
                          _emailController.text = displayEmail;
                          _phoneController.text = displayPhone;
                        }
                      });
                    },
                    onSave: _updateProfile,
                  ),
                  const SizedBox(height: 20),
                  _BankDetailsSection(
                    hasDetails: _hasBankDetails,
                    bankName: _bankName,
                    accountLast4: _bankAccountLast4,
                    ifsc: _bankIfsc,
                    upi: _bankUpi,
                    onEdit: () => _showBankDetailsSheet(),
                  ),
                  const SizedBox(height: 20),
                  _AddressSection(
                    hasAddress: _hasAddress,
                    line1: _addressLine1,
                    city: _addressCity,
                    state: _addressState,
                    pincode: _addressPincode,
                    onEdit: () => _showAddressSheet(),
                  ),
                  const SizedBox(height: 20),
                  _KycStatusSection(
                    status: _kycStatus,
                    onStartKyc: () => context.push('/kyc-verification'),
                  ),
                  const SizedBox(height: 20),
                  _MoreFeaturesSection(
                    context: context,
                  ),
                  const SizedBox(height: 20),
                  _QuickActionsSection(
                    referralCode: displayReferral,
                    onShareReferral: _shareReferralCode,
                    onHelpSupport: _openHelpSupport,
                    onRateApp: _rateApp,
                  ),
                  const SizedBox(height: 24),
                  _LogoutButton(onTap: _showLogoutDialog),
                  const SizedBox(height: 16),
                ],
              ),
            ),
    );
  }

  // ─── Avatar edit ─────────────────────────────

  Future<void> _onAvatarEdit() async {
    try {
      final picker = ImagePicker();
      final picked = await picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 512,
        maxHeight: 512,
        imageQuality: 85,
      );
      if (picked == null) return;

      final api = ref.read(apiServiceProvider);
      final fileName = picked.path.split('/').last;
      final formData = FormData.fromMap({
        'avatar': await MultipartFile.fromFile(picked.path, filename: fileName),
      });

      await api.post('user/profile/avatar', data: formData);
      _showSnackBar('Avatar updated');
      _loadProfileData();
    } catch (_) {
      _showSnackBar('Photo upload coming soon');
    }
  }

  // ─── Share referral ──────────────────────────

  void _shareReferralCode(String code) async {
    if (code.isEmpty) {
      _showSnackBar('Referral code not available yet', isError: true);
      return;
    }
    await Clipboard.setData(ClipboardData(text: code));
    try {
      await Share.share(
        'Join APS Dream Home with my referral code: $code\n'
        'Download the app and use code $code during registration.',
        subject: 'APS Dream Home Referral',
      );
    } catch (_) {
      _showSnackBar('Referral code "$code" copied to clipboard');
    }
  }

  void _openHelpSupport() {
    GoRouter.of(context).push('/support-tickets');
  }

  void _rateApp() async {
    const playStoreId = 'com.apsdreamhomes.mobileapp';
    final marketUri = Uri.parse('market://details?id=$playStoreId');
    final webUri = Uri.parse(
      'https://play.google.com/store/apps/details?id=$playStoreId',
    );
    if (await canLaunchUrl(marketUri)) {
      await launchUrl(marketUri, mode: LaunchMode.externalApplication);
    } else if (await canLaunchUrl(webUri)) {
      await launchUrl(webUri, mode: LaunchMode.externalApplication);
    }
  }

  // ─── Bank details bottom sheet ───────────────

  void _showBankDetailsSheet() {
    final bankCtrl = TextEditingController(text: _bankName);
    final acctCtrl = TextEditingController();
    final ifscCtrl = TextEditingController(text: _bankIfsc);
    final upiCtrl = TextEditingController(text: _bankUpi);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(
          24,
          24,
          24,
          MediaQuery.of(ctx).viewInsets.bottom + 24,
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
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
              const SizedBox(height: 16),
              const Text(
                'Bank Details',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.textPrimaryLight,
                ),
              ),
              const SizedBox(height: 20),
              _BottomSheetField(controller: bankCtrl, label: 'Bank Name', icon: Icons.account_balance),
              const SizedBox(height: 12),
              _BottomSheetField(
                controller: acctCtrl,
                label: _hasBankDetails ? 'New Account (blank to keep)' : 'Account Number',
                icon: Icons.credit_card,
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 12),
              _BottomSheetField(controller: ifscCtrl, label: 'IFSC Code', icon: Icons.code),
              const SizedBox(height: 12),
              _BottomSheetField(controller: upiCtrl, label: 'UPI ID (optional)', icon: Icons.qr_code),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(ctx);
                    _saveBankDetails(
                      bankName: bankCtrl.text.trim(),
                      accountNumber: acctCtrl.text.trim(),
                      ifsc: ifscCtrl.text.trim(),
                      upi: upiCtrl.text.trim(),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: const Text('Save Bank Details', style: TextStyle(fontWeight: FontWeight.w600)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _saveBankDetails({
    required String bankName,
    required String accountNumber,
    required String ifsc,
    required String upi,
  }) async {
    if (bankName.isEmpty) {
      _showSnackBar('Bank name is required', isError: true);
      return;
    }
    try {
      final api = ref.read(apiServiceProvider);
      final payload = <String, dynamic>{
        'bank_name': bankName,
        'ifsc_code': ifsc,
      };
      if (accountNumber.isNotEmpty) payload['account_number'] = accountNumber;
      if (upi.isNotEmpty) payload['upi_id'] = upi;
      await api.post('user/bank-details', data: payload);

      if (mounted) {
        setState(() {
          _hasBankDetails = true;
          _bankName = bankName;
          if (accountNumber.length > 4) {
            _bankAccountLast4 = accountNumber.substring(accountNumber.length - 4);
          } else if (accountNumber.isNotEmpty) {
            _bankAccountLast4 = accountNumber;
          }
          _bankIfsc = ifsc;
          _bankUpi = upi;
        });
      }
      _showSnackBar('Bank details saved');
    } catch (_) {
      _showSnackBar('Failed to save bank details', isError: true);
    }
  }

  // ─── Address bottom sheet ────────────────────

  void _showAddressSheet() {
    final line1Ctrl = TextEditingController(text: _addressLine1);
    final cityCtrl = TextEditingController(text: _addressCity);
    final stateCtrl = TextEditingController(text: _addressState);
    final pinCtrl = TextEditingController(text: _addressPincode);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(
          24,
          24,
          24,
          MediaQuery.of(ctx).viewInsets.bottom + 24,
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
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
              const SizedBox(height: 16),
              const Text(
                'Address',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.textPrimaryLight,
                ),
              ),
              const SizedBox(height: 20),
              _BottomSheetField(controller: line1Ctrl, label: 'Address Line', icon: Icons.home_outlined),
              const SizedBox(height: 12),
              _BottomSheetField(controller: cityCtrl, label: 'City', icon: Icons.location_city),
              const SizedBox(height: 12),
              _BottomSheetField(controller: stateCtrl, label: 'State', icon: Icons.map_outlined),
              const SizedBox(height: 12),
              _BottomSheetField(
                controller: pinCtrl,
                label: 'Pincode',
                icon: Icons.pin_drop_outlined,
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(ctx);
                    _saveAddress(
                      line1: line1Ctrl.text.trim(),
                      city: cityCtrl.text.trim(),
                      state: stateCtrl.text.trim(),
                      pincode: pinCtrl.text.trim(),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: const Text('Save Address', style: TextStyle(fontWeight: FontWeight.w600)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _saveAddress({
    required String line1,
    required String city,
    required String state,
    required String pincode,
  }) async {
    if (line1.isEmpty) {
      _showSnackBar('Address line is required', isError: true);
      return;
    }
    try {
      final api = ref.read(apiServiceProvider);
      await api.post('user/addresses', data: {
        'address_line1': line1,
        'city': city,
        'state': state,
        'pincode': pincode,
      });
      if (mounted) {
        setState(() {
          _hasAddress = true;
          _addressLine1 = line1;
          _addressCity = city;
          _addressState = state;
          _addressPincode = pincode;
        });
      }
      _showSnackBar('Address saved');
    } catch (_) {
      _showSnackBar('Failed to save address', isError: true);
    }
  }

  // ─── Logout ──────────────────────────────────

  void _showLogoutDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.logout, color: AppTheme.errorColor),
            SizedBox(width: 10),
            Text('Logout'),
          ],
        ),
        content: const Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text('Cancel', style: TextStyle(color: Colors.grey.shade600)),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              await ref.read(authProvider.notifier).logout();
              if (mounted) context.go('/login');
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.errorColor,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
  }
}

// ═══════════════════════════════════════════════════
// Sub-widgets
// ═══════════════════════════════════════════════════

class _ProfileHeader extends StatelessWidget {
  final String name;
  final String email;
  final String rank;
  final String? avatar;
  final Color rankColor;
  final IconData rankIcon;
  final VoidCallback onAvatarEdit;

  const _ProfileHeader({
    required this.name,
    required this.email,
    required this.rank,
    required this.avatar,
    required this.rankColor,
    required this.rankIcon,
    required this.onAvatarEdit,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            Stack(
              alignment: Alignment.bottomRight,
              children: [
                Container(
                  width: 96,
                  height: 96,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: AppTheme.primaryColor.withValues(alpha: 0.1),
                    border: Border.all(
                      color: AppTheme.primaryColor.withValues(alpha: 0.3),
                      width: 3,
                    ),
                  ),
                  child: avatar != null && avatar!.isNotEmpty
                      ? ClipOval(
                          child: CachedNetworkImage(
                            imageUrl: avatar!,
                            fit: BoxFit.cover,
                            width: 96,
                            height: 96,
                            placeholder: (_, __) => const Center(
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: AppTheme.primaryColor,
                              ),
                            ),
                            errorWidget: (_, __, ___) => const Icon(
                              Icons.person,
                              size: 48,
                              color: AppTheme.primaryColor,
                            ),
                          ),
                        )
                      : const Icon(Icons.person, size: 48, color: AppTheme.primaryColor),
                ),
                GestureDetector(
                  onTap: onAvatarEdit,
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: const BoxDecoration(
                      color: AppTheme.primaryColor,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.camera_alt, size: 16, color: Colors.white),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Text(
              name,
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: AppTheme.textPrimaryLight,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 4),
            Text(
              email,
              style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              decoration: BoxDecoration(
                color: rankColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: rankColor.withValues(alpha: 0.3)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(rankIcon, size: 16, color: rankColor),
                  const SizedBox(width: 6),
                  Text(
                    rank.isNotEmpty ? rank : 'Member',
                    style: TextStyle(
                      color: rankColor,
                      fontWeight: FontWeight.w600,
                      fontSize: 13,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _QuickStatsRow extends StatelessWidget {
  final int properties;
  final int bookings;
  final int saved;

  const _QuickStatsRow({
    required this.properties,
    required this.bookings,
    required this.saved,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _StatCard(
            icon: Icons.home_work_outlined,
            label: 'Properties',
            value: '$properties',
            color: AppTheme.primaryColor,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _StatCard(
            icon: Icons.receipt_long_outlined,
            label: 'Bookings',
            value: '$bookings',
            color: AppTheme.successColor,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _StatCard(
            icon: Icons.bookmark_outline,
            label: 'Saved',
            value: '$saved',
            color: AppTheme.warningColor,
          ),
        ),
      ],
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color color;

  const _StatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 22, color: color),
            ),
            const SizedBox(height: 10),
            Text(
              value,
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey.shade600,
                fontWeight: FontWeight.w500,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}

class _PersonalInfoSection extends StatelessWidget {
  final String name;
  final String email;
  final String phone;
  final bool isEditing;
  final TextEditingController nameController;
  final TextEditingController emailController;
  final TextEditingController phoneController;
  final VoidCallback onToggleEdit;
  final VoidCallback onSave;

  const _PersonalInfoSection({
    required this.name,
    required this.email,
    required this.phone,
    required this.isEditing,
    required this.nameController,
    required this.emailController,
    required this.phoneController,
    required this.onToggleEdit,
    required this.onSave,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.person_outline, size: 20, color: AppTheme.primaryColor),
                    SizedBox(width: 8),
                    Text(
                      'Personal Information',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.textPrimaryLight,
                      ),
                    ),
                  ],
                ),
                IconButton(
                  icon: Icon(
                    isEditing ? Icons.close : Icons.edit_outlined,
                    size: 20,
                    color: AppTheme.primaryColor,
                  ),
                  onPressed: onToggleEdit,
                ),
              ],
            ),
            const Divider(height: 20),
            if (isEditing) ...[
              _EditableField(controller: nameController, label: 'Full Name', icon: Icons.person_outline),
              const SizedBox(height: 12),
              _EditableField(
                controller: emailController,
                label: 'Email',
                icon: Icons.email_outlined,
                keyboardType: TextInputType.emailAddress,
              ),
              const SizedBox(height: 12),
              _EditableField(
                controller: phoneController,
                label: 'Phone',
                icon: Icons.phone_outlined,
                keyboardType: TextInputType.phone,
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: onSave,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: const Text('Save Changes', style: TextStyle(fontWeight: FontWeight.w600)),
                ),
              ),
            ] else ...[
              _InfoTile(icon: Icons.person_outline, label: 'Name', value: name.isNotEmpty ? name : 'Not set'),
              _InfoTile(icon: Icons.email_outlined, label: 'Email', value: email.isNotEmpty ? email : 'Not set'),
              _InfoTile(
                icon: Icons.phone_outlined,
                label: 'Phone',
                value: phone.isNotEmpty ? phone : 'Not provided',
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _EditableField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final IconData icon;
  final TextInputType keyboardType;

  const _EditableField({
    required this.controller,
    required this.label,
    required this.icon,
    this.keyboardType = TextInputType.text,
  });

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, size: 20, color: AppTheme.primaryColor),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppTheme.primaryColor, width: 2),
        ),
        filled: true,
        fillColor: Colors.grey.shade50,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
    );
  }
}

class _InfoTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _InfoTile({required this.icon, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey.shade500),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade500, fontWeight: FontWeight.w500),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimaryLight,
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

class _BankDetailsSection extends StatelessWidget {
  final bool hasDetails;
  final String bankName;
  final String accountLast4;
  final String ifsc;
  final String upi;
  final VoidCallback onEdit;

  const _BankDetailsSection({
    required this.hasDetails,
    required this.bankName,
    required this.accountLast4,
    required this.ifsc,
    required this.upi,
    required this.onEdit,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.account_balance_outlined, size: 20, color: AppTheme.primaryColor),
                    SizedBox(width: 8),
                    Text(
                      'Bank Details',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.textPrimaryLight,
                      ),
                    ),
                  ],
                ),
                TextButton.icon(
                  onPressed: onEdit,
                  icon: Icon(
                    hasDetails ? Icons.edit_outlined : Icons.add,
                    size: 18,
                    color: AppTheme.primaryColor,
                  ),
                  label: Text(
                    hasDetails ? 'Edit' : 'Add',
                    style: const TextStyle(color: AppTheme.primaryColor, fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            if (hasDetails) ...[
              _InfoTile(icon: Icons.account_balance, label: 'Bank Name', value: bankName),
              _InfoTile(
                icon: Icons.credit_card,
                label: 'Account (Last 4)',
                value: accountLast4.isNotEmpty ? '**** $accountLast4' : 'Not set',
              ),
              _InfoTile(icon: Icons.code, label: 'IFSC', value: ifsc.isNotEmpty ? ifsc : 'Not set'),
              if (upi.isNotEmpty) _InfoTile(icon: Icons.qr_code, label: 'UPI ID', value: upi),
            ] else ...[
              Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 20),
                  child: Column(
                    children: [
                      Icon(Icons.account_balance_outlined, size: 40, color: Colors.grey.shade300),
                      const SizedBox(height: 12),
                      Text('No bank details added', style: TextStyle(fontSize: 14, color: Colors.grey.shade500)),
                      const SizedBox(height: 4),
                      Text(
                        'Add bank details for easier payouts',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade400),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _AddressSection extends StatelessWidget {
  final bool hasAddress;
  final String line1;
  final String city;
  final String state;
  final String pincode;
  final VoidCallback onEdit;

  const _AddressSection({
    required this.hasAddress,
    required this.line1,
    required this.city,
    required this.state,
    required this.pincode,
    required this.onEdit,
  });

  @override
  Widget build(BuildContext context) {
    final cityState = [
      city,
      if (state.isNotEmpty) state,
    ].join(', ');

    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.location_on_outlined, size: 20, color: AppTheme.primaryColor),
                    SizedBox(width: 8),
                    Text(
                      'Address',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.textPrimaryLight,
                      ),
                    ),
                  ],
                ),
                TextButton.icon(
                  onPressed: onEdit,
                  icon: Icon(
                    hasAddress ? Icons.edit_outlined : Icons.add,
                    size: 18,
                    color: AppTheme.primaryColor,
                  ),
                  label: Text(
                    hasAddress ? 'Edit' : 'Add',
                    style: const TextStyle(color: AppTheme.primaryColor, fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            if (hasAddress) ...[
              _InfoTile(
                icon: Icons.home_outlined,
                label: 'Address',
                value: line1.isNotEmpty ? line1 : 'Not set',
              ),
              if (cityState.isNotEmpty) _InfoTile(icon: Icons.location_city, label: 'City / State', value: cityState),
              if (pincode.isNotEmpty) _InfoTile(icon: Icons.pin_drop_outlined, label: 'Pincode', value: pincode),
            ] else ...[
              Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 20),
                  child: Column(
                    children: [
                      Icon(Icons.location_on_outlined, size: 40, color: Colors.grey.shade300),
                      const SizedBox(height: 12),
                      Text('No address added', style: TextStyle(fontSize: 14, color: Colors.grey.shade500)),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _KycStatusSection extends StatelessWidget {
  final String status;
  final VoidCallback onStartKyc;

  const _KycStatusSection({required this.status, required this.onStartKyc});

  @override
  Widget build(BuildContext context) {
    final isVerified = status == 'verified' || status == 'approved';
    final isPending = status == 'pending' || status == 'submitted' || status == 'under_review';

    final statusColor = isVerified
        ? AppTheme.successColor
        : isPending
            ? AppTheme.warningColor
            : Colors.grey.shade400;

    final statusIcon = isVerified
        ? Icons.verified
        : isPending
            ? Icons.hourglass_top
            : Icons.info_outline;

    final statusTitle = isVerified
        ? 'KYC Verified'
        : isPending
            ? 'KYC Under Review'
            : 'KYC Not Started';

    final statusSubtitle = isVerified
        ? 'Your identity has been verified'
        : isPending
            ? 'Documents are being reviewed'
            : 'Complete KYC to unlock all features';

    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.verified_user_outlined, size: 20, color: AppTheme.primaryColor),
                SizedBox(width: 8),
                Text(
                  'KYC Verification',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(statusIcon, size: 24, color: statusColor),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        statusTitle,
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: statusColor,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        statusSubtitle,
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            if (!isVerified) ...[
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: onStartKyc,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppTheme.primaryColor,
                    side: const BorderSide(color: AppTheme.primaryColor),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: Text(
                    isPending ? 'View Status' : 'Start KYC Verification',
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _MoreFeaturesSection extends StatelessWidget {
  final BuildContext context;
  const _MoreFeaturesSection({required this.context});

  @override
  Widget build(BuildContext context) {
    final items = [
      _FeatureItem(Icons.search, 'Saved Searches', '/saved-searches', AppTheme.primaryColor),
      _FeatureItem(Icons.notifications_active, 'Property Alerts', '/property-alerts', AppTheme.warningColor),
      _FeatureItem(Icons.compare_arrows, 'Compare Properties', '/compare', AppTheme.infoColor),
      _FeatureItem(Icons.star_border, 'Testimonials', '/testimonials', AppTheme.accentColor),
      _FeatureItem(Icons.language, 'Language', '/language', AppTheme.secondaryColor),
      _FeatureItem(Icons.calculate, 'Stamp Duty Calculator', '/stamp-duty-calculator', const Color(0xFF43A047)),
      _FeatureItem(Icons.straighten, 'Plot Converter', '/plot-converter', const Color(0xFF00897B)),
      _FeatureItem(Icons.question_answer, 'FAQs', '/faq', AppTheme.infoColor),
    ];

    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.grid_view_rounded, size: 20, color: AppTheme.primaryColor),
                SizedBox(width: 8),
                Text(
                  'More Features',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            ...items.asMap().entries.map((entry) {
              final idx = entry.key;
              final item = entry.value;
              return Column(
                children: [
                  _ActionTile(
                    icon: item.icon,
                    label: item.label,
                    subtitle: '',
                    color: item.color,
                    showDivider: idx < items.length - 1,
                    onTap: () => GoRouter.of(context).push(item.route),
                  ),
                ],
              );
            }),
          ],
        ),
      ),
    );
  }
}

class _FeatureItem {
  final IconData icon;
  final String label;
  final String route;
  final Color color;
  _FeatureItem(this.icon, this.label, this.route, this.color);
}

class _QuickActionsSection extends StatelessWidget {
  final String referralCode;
  final Function(String) onShareReferral;
  final VoidCallback onHelpSupport;
  final VoidCallback onRateApp;

  const _QuickActionsSection({
    required this.referralCode,
    required this.onShareReferral,
    required this.onHelpSupport,
    required this.onRateApp,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.flash_on_outlined, size: 20, color: AppTheme.primaryColor),
                SizedBox(width: 8),
                Text(
                  'Quick Actions',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            _ActionTile(
              icon: Icons.share_outlined,
              label: 'Share Referral Code',
              subtitle: referralCode.isNotEmpty ? referralCode : 'Your unique code',
              color: AppTheme.primaryColor,
              onTap: () => onShareReferral(referralCode),
            ),
            _ActionTile(
              icon: Icons.help_outline,
              label: 'Help & Support',
              subtitle: 'Get assistance with your account',
              color: AppTheme.infoColor,
              onTap: onHelpSupport,
            ),
            _ActionTile(
              icon: Icons.star_outline,
              label: 'Rate This App',
              subtitle: 'Share your feedback on the store',
              color: AppTheme.accentColor,
              onTap: onRateApp,
              showDivider: false,
            ),
          ],
        ),
      ),
    );
  }
}

class _ActionTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String subtitle;
  final Color color;
  final VoidCallback onTap;
  final bool showDivider;

  const _ActionTile({
    required this.icon,
    required this.label,
    required this.subtitle,
    required this.color,
    required this.onTap,
    this.showDivider = true,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 10),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(icon, size: 20, color: color),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        label,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.textPrimaryLight,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(subtitle, style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
                    ],
                  ),
                ),
                Icon(Icons.chevron_right, size: 20, color: Colors.grey.shade400),
              ],
            ),
          ),
        ),
        if (showDivider) Divider(height: 1, color: Colors.grey.shade100),
      ],
    );
  }
}

class _LogoutButton extends StatelessWidget {
  final VoidCallback onTap;

  const _LogoutButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton.icon(
        onPressed: onTap,
        icon: const Icon(Icons.logout, color: AppTheme.errorColor),
        label: const Text(
          'Logout',
          style: TextStyle(
            color: AppTheme.errorColor,
            fontWeight: FontWeight.w600,
            fontSize: 16,
          ),
        ),
        style: OutlinedButton.styleFrom(
          side: const BorderSide(color: AppTheme.errorColor, width: 1.5),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          padding: const EdgeInsets.symmetric(vertical: 14),
        ),
      ),
    );
  }
}

class _BottomSheetField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final IconData icon;
  final TextInputType keyboardType;

  const _BottomSheetField({
    required this.controller,
    required this.label,
    required this.icon,
    this.keyboardType = TextInputType.text,
  });

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, size: 20),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
        filled: true,
        fillColor: Colors.grey.shade50,
      ),
    );
  }
}
