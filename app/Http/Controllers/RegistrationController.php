<?php
/**
 * Unified Registration Controller with MLM Referral System
 * Modern implementation of the legacy registration system
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MLMProfile;
use App\Models\MLMReferral;
use App\Models\MLMNetworkTree;
use App\Services\MLMReferralService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    protected $mlmReferralService;
    protected $emailService;
    private $recaptchaSecret;

    public function __construct()
    {
        $this->mlmReferralService = new MLMReferralService();
        $this->emailService = new EmailService();
        $this->recaptchaSecret = getenv('RECAPTCHA_SECRET_KEY') ?: 'recaptcha_secret_placeholder';
    }

    /**
     * Show the unified registration form
     */
    public function showRegistrationForm(Request $request)
    {
        $referralCode = $request->get('ref');
        $referrerInfo = null;

        // Validate and get referrer information
        if ($referralCode) {
            $referrerInfo = $this->mlmReferralService->validateReferralCode($referralCode);
        }

        // Get form data for Indian states
        $indianStates = [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
            'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka',
            'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya',
            'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim',
            'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand',
            'West Bengal', 'Delhi', 'Jammu and Kashmir', 'Ladakh'
        ];

        // User types configuration
        $userTypes = [
            'customer' => [
                'label' => 'Customer (Property Buyer)',
                'description' => 'Buy properties and earn referral rewards',
                'icon' => 'user'
            ],
            'agent' => [
                'label' => 'Real Estate Agent',
                'description' => 'Sell properties and earn commissions',
                'icon' => 'user-tie'
            ],
            'associate' => [
                'label' => 'MLM Associate',
                'description' => 'Build network and earn multi-level commissions',
                'icon' => 'users'
            ],
            'builder' => [
                'label' => 'Property Builder',
                'description' => 'List properties and manage sales',
                'icon' => 'building'
            ],
            'investor' => [
                'label' => 'Property Investor',
                'description' => 'Invest in properties and earn returns',
                'icon' => 'chart-line'
            ]
        ];

        return view('auth.register', compact('referralCode', 'referrerInfo', 'indianStates', 'userTypes'));
    }

    /**
     * Process the unified registration
     */
    public function register(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|size:10|regex:/^[0-9]+$/|unique:users,mobile',
            'password' => 'required|string|min:6|confirmed',
            'user_type' => 'required|in:customer,agent,associate,builder,investor',
            'referrer_code' => 'nullable|string',
            'terms' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Check if email/mobile already exists
            $existingUser = User::where('email', $request->email)
                               ->orWhere('mobile', $request->mobile)
                               ->first();

            if ($existingUser) {
                return back()->withErrors([
                    'email' => 'Email or mobile number already registered.'
                ])->withInput();
            }

            // Validate referrer code if provided
            $sponsorId = null;
            $sponsorCode = null;

            if ($request->referrer_code) {
                $referrerInfo = $this->mlmReferralService->validateReferralCode($request->referrer_code);
                if (!$referrerInfo) {
                    return back()->withErrors([
                        'referrer_code' => 'Invalid referrer code.'
                    ])->withInput();
                }
                $sponsorId = $referrerInfo['user_id'];
                $sponsorCode = $request->referrer_code;
            }

            // Generate unique referral code
            $referralCode = $this->generateReferralCode($request->full_name, $request->email);

            // Create user
            $user = User::create([
                'name' => $request->full_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'password' => Hash::make($request->password),
                'type' => $request->user_type,
                'status' => 'active',
                'email_verified_at' => now()
            ]);

            // Create MLM profile
            $mlmProfile = MLMProfile::create([
                'user_id' => $user->id,
                'referral_code' => $referralCode,
                'sponsor_user_id' => $sponsorId,
                'sponsor_code' => $sponsorCode,
                'user_type' => $request->user_type,
                'verification_status' => 'verified',
                'status' => 'active'
            ]);

            // Handle referral and network building
            if ($sponsorId) {
                $this->processReferral($user->id, $sponsorId, $request->user_type);
            }

            // Create role-specific profile
            $this->createRoleSpecificProfile($user, $request);

            DB::commit();

            // Send welcome email
            $this->sendWelcomeEmail($user, $referralCode);

            // Auto-login user
            auth()->login($user);

            // Redirect based on user type
            return $this->redirectBasedOnRole($user);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors([
                'registration' => 'Registration failed. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Generate unique referral code
     */
    protected function generateReferralCode($name, $email)
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $suffix = strtoupper(substr(md5($email . time()), 0, 4));
        $code = $prefix . $suffix;

        // Ensure uniqueness
        while (MLMProfile::where('referral_code', $code)->exists()) {
            $suffix = strtoupper(substr(md5($email . time() . rand()), 0, 4));
            $code = $prefix . $suffix;
        }

        return $code;
    }

    /**
     * Process referral and build network tree
     */
    protected function processReferral($userId, $sponsorId, $userType)
    {
        // Create referral record
        MLMReferral::create([
            'referrer_user_id' => $sponsorId,
            'referred_user_id' => $userId,
            'referral_type' => $userType,
            'status' => 'active'
        ]);

        // Update sponsor's direct referrals count
        $sponsorProfile = MLMProfile::where('user_id', $sponsorId)->first();
        if ($sponsorProfile) {
            $sponsorProfile->increment('direct_referrals');
        }

        // Build network tree
        $this->buildNetworkTree($userId, $sponsorId);
    }

    /**
     * Build the MLM network tree
     */
    protected function buildNetworkTree($userId, $sponsorId, $level = 1, $maxLevel = 10)
    {
        if ($level > $maxLevel) {
            return;
        }

        // Add current sponsor to network tree
        MLMNetworkTree::create([
            'ancestor_user_id' => $sponsorId,
            'descendant_user_id' => $userId,
            'level' => $level
        ]);

        // Get sponsor's sponsor (go up the tree)
        $sponsorProfile = MLMProfile::where('user_id', $sponsorId)->first();
        if ($sponsorProfile && $sponsorProfile->sponsor_user_id) {
            $this->buildNetworkTree($userId, $sponsorProfile->sponsor_user_id, $level + 1, $maxLevel);
        }
    }

    /**
     * Create role-specific profile
     */
    protected function createRoleSpecificProfile($user, $request)
    {
        switch ($request->user_type) {
            case 'agent':
                // Create agent profile
                DB::table('agents')->insert([
                    'user_id' => $user->id,
                    'license_number' => $request->license_number,
                    'experience_years' => $request->experience,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                break;

            case 'associate':
                // Create associate profile
                DB::table('associates')->insert([
                    'user_id' => $user->id,
                    'pan_number' => strtoupper($request->pan_number),
                    'aadhar_number' => $request->aadhar_number,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                break;

            case 'builder':
                // Create builder profile
                DB::table('builders')->insert([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'rera_registration' => $request->rera_registration,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                break;

            case 'investor':
                // Create investor profile
                DB::table('investors')->insert([
                    'user_id' => $user->id,
                    'investment_range' => $request->investment_range,
                    'investment_type' => $request->investment_type,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                break;

            case 'customer':
            default:
                // Create customer profile
                DB::table('customers')->insert([
                    'user_id' => $user->id,
                    'budget_range' => $request->budget_range,
                    'property_type' => $request->property_type,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                break;
        }
    }

    /**
     * Send welcome email
     */
    protected function sendWelcomeEmail($user, $referralCode)
    {
        try {
            $this->emailService->sendWelcomeEmail($user, $referralCode);
        } catch (\Exception $e) {
            // Log error but don't fail registration
            error_log('Welcome email failed: ' . $e->getMessage());
        }
    }

    /**
     * Redirect based on user role
     */
    protected function redirectBasedOnRole($user)
    {
        $redirectUrl = '/dashboard';

        switch ($user->type) {
            case 'admin':
                $redirectUrl = '/admin/dashboard';
                break;
            case 'agent':
                $redirectUrl = '/agent/dashboard';
                break;
            case 'associate':
                $redirectUrl = '/associate/dashboard';
                break;
            case 'builder':
                $redirectUrl = '/builder/dashboard';
                break;
            case 'investor':
                $redirectUrl = '/investor/dashboard';
                break;
            case 'customer':
            default:
                $redirectUrl = '/dashboard';
                break;
        }

        return redirect($redirectUrl)->with('success', 'Registration successful! Welcome to APS Dream Home.');
    }

    /**
     * Validate referral code via AJAX
     */
    public function validateReferralCode(Request $request)
    {
        $code = $request->get('code');
        $referrerInfo = $this->mlmReferralService->validateReferralCode($code);

        return response()->json([
            'valid' => $referrerInfo !== null,
            'referrer' => $referrerInfo ? [
                'name' => $referrerInfo['name'],
                'referral_code' => $referrerInfo['referral_code']
            ] : null
        ]);
    }

    /**
     * Check email availability via AJAX
     */
    public function checkEmail(Request $request)
    {
        $email = $request->get('email');
        $exists = User::where('email', $email)->exists();

        return response()->json(['available' => !$exists]);
    }

    /**
     * Check mobile availability via AJAX
     */
    public function checkMobile(Request $request)
    {
        $mobile = $request->get('mobile');
        $exists = User::where('mobile', $mobile)->exists();

        return response()->json(['available' => !$exists]);
    }
}
