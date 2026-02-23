<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Services\CRM\LeadScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * CustomerController - Customer Portal Controller
 *
 * Provides customer-facing functionality for property browsing,
 * inquiries, profile management, and service requests.
 */
class CustomerController extends Controller
{
    protected $leadScoringService;

    public function __construct(LeadScoringService $leadScoringService)
    {
        $this->leadScoringService = $leadScoringService;
    }

    /**
     * Customer Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $customer = $user->customer;

        // Customer statistics
        $stats = [
            'total_inquiries' => DB::table('leads')->where('customer_id', $user->id)->count(),
            'active_inquiries' => DB::table('leads')->where('customer_id', $user->id)->where('status', 'active')->count(),
            'viewed_properties' => DB::table('property_views')->where('user_id', $user->id)->count(),
            'saved_properties' => DB::table('saved_properties')->where('user_id', $user->id)->count(),
        ];

        // Recent inquiries
        $recentInquiries = DB::table('leads')
            ->where('customer_id', $user->id)
            ->with(['property', 'assigned_agent'])
            ->latest()
            ->take(5)
            ->get();

        // Recommended properties based on interests
        $recommendedProperties = $this->getRecommendedProperties($user);

        // Upcoming appointments
        $upcomingAppointments = DB::table('appointments')
            ->where('customer_id', $user->id)
            ->where('appointment_date', '>=', now())
            ->orderBy('appointment_date')
            ->take(3)
            ->get();

        return view('customers.dashboard', compact('stats', 'recentInquiries', 'recommendedProperties', 'upcomingAppointments'));
    }

    /**
     * Browse Properties
     */
    public function properties(Request $request)
    {
        $query = DB::table('properties')->where('status', 'active');

        // Filter by property type
        if ($request->has('type') && $request->type !== '') {
            $query->where('property_type', $request->type);
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price !== '') {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price !== '') {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by location
        if ($request->has('location') && $request->location !== '') {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Filter by bedrooms
        if ($request->has('bedrooms') && $request->bedrooms !== '') {
            $query->where('bedrooms', $request->bedrooms);
        }

        // Search by title or description
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'title') {
            $query->orderBy('title', $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $properties = $query->paginate(12);

        // Get property types for filter dropdown
        $propertyTypes = DB::table('properties')
            ->select('property_type')
            ->distinct()
            ->pluck('property_type');

        return view('customers.properties.index', compact('properties', 'propertyTypes'));
    }

    /**
     * View Property Details
     */
    public function showProperty($id)
    {
        $property = DB::table('properties')->find($id);

        if (!$property) {
            return redirect()->route('customer.properties')->with('error', 'Property not found');
        }

        $user = Auth::user();

        // Record property view
        DB::table('property_views')->insert([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'viewed_at' => now(),
        ]);

        // Check if property is saved
        $isSaved = DB::table('saved_properties')
            ->where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->exists();

        // Get similar properties
        $similarProperties = DB::table('properties')
            ->where('property_type', $property->property_type)
            ->where('id', '!=', $property->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        // Get agent information
        $agent = null;
        if ($property->agent_id) {
            $agent = DB::table('users')
                ->join('agents', 'users.id', '=', 'agents.user_id')
                ->where('users.id', $property->agent_id)
                ->select('users.*', 'agents.license_number', 'agents.experience_years')
                ->first();
        }

        return view('customers.properties.show', compact('property', 'isSaved', 'similarProperties', 'agent'));
    }

    /**
     * Save/Unsave Property
     */
    public function toggleSaveProperty($id)
    {
        $user = Auth::user();

        $exists = DB::table('saved_properties')
            ->where('user_id', $user->id)
            ->where('property_id', $id)
            ->exists();

        if ($exists) {
            // Remove from saved
            DB::table('saved_properties')
                ->where('user_id', $user->id)
                ->where('property_id', $id)
                ->delete();

            $message = 'Property removed from saved list';
        } else {
            // Add to saved
            DB::table('saved_properties')->insert([
                'user_id' => $user->id,
                'property_id' => $id,
                'saved_at' => now(),
            ]);

            $message = 'Property saved successfully';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Saved Properties
     */
    public function savedProperties()
    {
        $user = Auth::user();

        $properties = DB::table('saved_properties')
            ->join('properties', 'saved_properties.property_id', '=', 'properties.id')
            ->where('saved_properties.user_id', $user->id)
            ->where('properties.status', 'active')
            ->select('properties.*', 'saved_properties.saved_at')
            ->paginate(12);

        return view('customers.properties.saved', compact('properties'));
    }

    /**
     * Submit Property Inquiry
     */
    public function submitInquiry(Request $request, $propertyId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'contact_method' => 'required|in:phone,email,both',
            'preferred_time' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // Create lead/inquiry
        $leadId = DB::table('leads')->insertGetId([
            'customer_id' => $user->id,
            'property_id' => $propertyId,
            'status' => 'new',
            'lead_source' => 'website_inquiry',
            'message' => $request->message,
            'contact_method' => $request->contact_method,
            'preferred_contact_time' => $request->preferred_time,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Calculate lead score
        $leadData = [
            'customer_id' => $user->id,
            'property_id' => $propertyId,
            'message_length' => strlen($request->message),
            'contact_method' => $request->contact_method,
            'has_preferred_time' => !empty($request->preferred_time),
        ];

        $leadScore = $this->leadScoringService->calculateScore($leadData);

        // Update lead with score
        DB::table('leads')->where('id', $leadId)->update([
            'lead_score' => $leadScore,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Your inquiry has been submitted successfully! An agent will contact you soon.');
    }

    /**
     * My Inquiries
     */
    public function inquiries()
    {
        $user = Auth::user();

        $inquiries = DB::table('leads')
            ->leftJoin('properties', 'leads.property_id', '=', 'properties.id')
            ->leftJoin('users as agents', 'leads.assigned_agent_id', '=', 'agents.id')
            ->where('leads.customer_id', $user->id)
            ->select(
                'leads.*',
                'properties.title as property_title',
                'properties.price as property_price',
                'properties.location as property_location',
                'agents.name as agent_name',
                'agents.email as agent_email',
                'agents.phone as agent_phone'
            )
            ->orderBy('leads.created_at', 'desc')
            ->paginate(10);

        return view('customers.inquiries.index', compact('inquiries'));
    }

    /**
     * View Inquiry Details
     */
    public function showInquiry($id)
    {
        $user = Auth::user();

        $inquiry = DB::table('leads')
            ->leftJoin('properties', 'leads.property_id', '=', 'properties.id')
            ->leftJoin('users as agents', 'leads.assigned_agent_id', '=', 'agents.id')
            ->where('leads.id', $id)
            ->where('leads.customer_id', $user->id)
            ->select(
                'leads.*',
                'properties.title as property_title',
                'properties.price as property_price',
                'properties.location as property_location',
                'properties.images as property_images',
                'agents.name as agent_name',
                'agents.email as agent_email',
                'agents.phone as agent_phone'
            )
            ->first();

        if (!$inquiry) {
            return redirect()->route('customer.inquiries')->with('error', 'Inquiry not found');
        }

        // Get inquiry timeline/updates
        $updates = DB::table('lead_updates')
            ->where('lead_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customers.inquiries.show', compact('inquiry', 'updates'));
    }

    /**
     * Customer Profile
     */
    public function profile()
    {
        $user = Auth::user();
        $customer = $user->customer;

        return view('customers.profile', compact('user', 'customer'));
    }

    /**
     * Update Customer Profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'occupation' => 'nullable|string|max:100',
            'monthly_income' => 'nullable|numeric|min:0',
            'preferred_locations' => 'nullable|string',
            'property_preferences' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update user
        $user->update($request->only(['name', 'email', 'phone']));

        // Update customer profile
        if ($user->customer) {
            $user->customer->update($request->only([
                'date_of_birth',
                'occupation',
                'monthly_income',
                'preferred_locations',
                'property_preferences'
            ]));
        }

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    /**
     * Schedule Appointment
     */
    public function scheduleAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        DB::table('appointments')->insert([
            'customer_id' => $user->id,
            'property_id' => $request->property_id,
            'appointment_date' => $request->appointment_date . ' ' . $request->appointment_time,
            'notes' => $request->notes,
            'status' => 'scheduled',
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Appointment scheduled successfully');
    }

    /**
     * Get recommended properties based on user behavior
     */
    private function getRecommendedProperties($user)
    {
        // Get user's inquiry history and saved properties
        $inquiredPropertyTypes = DB::table('leads')
            ->join('properties', 'leads.property_id', '=', 'properties.id')
            ->where('leads.customer_id', $user->id)
            ->pluck('properties.property_type')
            ->unique()
            ->toArray();

        $savedPropertyTypes = DB::table('saved_properties')
            ->join('properties', 'saved_properties.property_id', '=', 'properties.id')
            ->where('saved_properties.user_id', $user->id)
            ->pluck('properties.property_type')
            ->unique()
            ->toArray();

        $preferredTypes = array_unique(array_merge($inquiredPropertyTypes, $savedPropertyTypes));

        if (empty($preferredTypes)) {
            // No preferences, return featured properties
            return DB::table('properties')
                ->where('status', 'active')
                ->where('featured', true)
                ->take(6)
                ->get();
        }

        // Return properties of preferred types
        return DB::table('properties')
            ->where('status', 'active')
            ->whereIn('property_type', $preferredTypes)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();
    }
}
