<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadNote;
use App\Models\LeadFile;
use App\Models\LeadTag;
use App\Models\LeadStatus;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeadController extends Controller
{
    /**
     * Display a listing of the leads.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 25);
            $page = $request->input('page', 1);
            
            $query = Lead::with(['assignedTo', 'createdBy', 'tags', 'status']);
            
            // Apply filters
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%");
                });
            }
            
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }
            
            if ($request->has('source')) {
                $query->where('source', $request->input('source'));
            }
            
            if ($request->has('assigned_to')) {
                if ($request->input('assigned_to') === 'me') {
                    $query->where('assigned_to', $user->id);
                } elseif ($request->input('assigned_to') === 'unassigned') {
                    $query->whereNull('assigned_to');
                } else {
                    $query->where('assigned_to', $request->input('assigned_to'));
                }
            }
            
            if ($request->has('tag')) {
                $query->whereHas('tags', function($q) use ($request) {
                    $q->where('name', $request->input('tag'));
                });
            }
            
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }
            
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }
            
            // Apply sorting
            $sortField = $request->input('sort_field', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // Get paginated results
            $leads = $query->paginate($perPage, ['*'], 'page', $page);
            
            return response()->json([
                'success' => true,
                'data' => $leads->items(),
                'pagination' => [
                    'total' => $leads->total(),
                    'per_page' => $leads->perPage(),
                    'current_page' => $leads->currentPage(),
                    'last_page' => $leads->lastPage(),
                    'from' => $leads->firstItem(),
                    'to' => $leads->lastItem(),
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leads',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created lead in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            
            $lead = new Lead();
            $lead->first_name = $request->input('first_name');
            $lead->last_name = $request->input('last_name');
            $lead->email = $request->input('email');
            $lead->phone = $request->input('phone');
            $lead->company = $request->input('company');
            $lead->source = $request->input('source', 'website');
            $lead->status = $request->input('status', 'new');
            $lead->assigned_to = $request->input('assigned_to');
            $lead->created_by = $user->id;
            
            // Handle custom fields
            if ($request->has('custom_fields')) {
                $lead->custom_fields = $request->input('custom_fields');
            }
            
            $lead->save();
            
            // Log activity
            $this->logActivity($lead->id, 'lead_created', 'Lead created', [
                'assigned_to' => $lead->assigned_to,
                'status' => $lead->status,
            ]);
            
            // Handle tags if provided
            if ($request->has('tags')) {
                $this->syncTags($lead, $request->input('tags'));
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully',
                'data' => $lead->load(['assignedTo', 'createdBy', 'tags']),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified lead.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $lead = Lead::with([
                'assignedTo', 
                'createdBy', 
                'tags', 
                'status', 
                'source',
                'activities' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'activities.user',
                'notes' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'notes.user',
                'files' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'files.uploadedBy',
            ])->findOrFail($id);
            
            // Check permissions
            $this->authorize('view', $lead);
            
            return response()->json([
                'success' => true,
                'data' => $lead,
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified lead in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $lead = Lead::findOrFail($id);
            
            // Check permissions
            $this->authorize('update', $lead);
            
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'company' => 'nullable|string|max:255',
                'source' => 'nullable|string|max:50',
                'status' => 'nullable|string|max:50',
                'assigned_to' => 'nullable|exists:users,id',
                'custom_fields' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            DB::beginTransaction();
            
            $originalData = $lead->getOriginal();
            $user = Auth::user();
            
            // Update lead fields
            $lead->fill($request->only([
                'first_name', 'last_name', 'email', 'phone', 'company',
                'source', 'status', 'assigned_to', 'custom_fields'
            ]));
            
            // Track changes for activity log
            $changes = [];
            foreach ($lead->getDirty() as $key => $value) {
                if ($key === 'assigned_to' && $originalData['assigned_to'] != $value) {
                    $changes['assigned_to'] = [
                        'from' => $originalData['assigned_to'],
                        'to' => $value,
                    ];
                } elseif ($key === 'status' && $originalData['status'] != $value) {
                    $changes['status'] = [
                        'from' => $originalData['status'],
                        'to' => $value,
                    ];
                } elseif ($key === 'custom_fields') {
                    $changes['custom_fields'] = true;
                } else {
                    $changes[$key] = true;
                }
            }
            
            $lead->updated_by = $user->id;
            $lead->save();
            
            // Log activity if there are changes
            if (!empty($changes)) {
                $this->logActivity($lead->id, 'lead_updated', 'Lead updated', [
                    'changes' => $changes,
                ]);
            }
            
            // Handle tags if provided
            if ($request->has('tags')) {
                $this->syncTags($lead, $request->input('tags'));
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully',
                'data' => $lead->load(['assignedTo', 'createdBy', 'tags']),
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified lead from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $lead = Lead::findOrFail($id);
            
            // Check permissions
            $this->authorize('delete', $lead);
            
            DB::beginTransaction();
            
            // Log activity before deleting
            $this->logActivity($lead->id, 'lead_deleted', 'Lead deleted');
            
            // Delete related records (handled by database cascade)
            $lead->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully',
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Add a note to the lead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNote(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'is_private' => 'sometimes|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $lead = Lead::findOrFail($id);
            
            // Check permissions
            $this->authorize('update', $lead);
            
            $user = Auth::user();
            
            $note = new LeadNote([
                'content' => $request->input('content'),
                'is_private' => $request->input('is_private', false),
                'created_by' => $user->id,
            ]);
            
            $lead->notes()->save($note);
            
            // Log activity
            $this->logActivity($lead->id, 'note_added', 'Note added', [
                'note_id' => $note->id,
                'is_private' => $note->is_private,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Note added successfully',
                'data' => $note->load('user'),
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Upload a file for the lead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // Max 10MB
            'description' => 'nullable|string|max:1000',
            'is_private' => 'sometimes|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $lead = Lead::findOrFail($id);
            
            // Check permissions
            $this->authorize('update', $lead);
            
            $user = Auth::user();
            $file = $request->file('file');
            
            // Generate a unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = 'lead_' . $lead->id . '_' . time() . '_' . Str::random(10) . '.' . $extension;
            
            // Store the file
            $path = $file->storeAs('leads/' . $lead->id, $filename, 'private');
            
            // Create file record
            $leadFile = new LeadFile([
                'original_name' => $originalName,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'description' => $request->input('description'),
                'is_private' => $request->input('is_private', false),
                'uploaded_by' => $user->id,
            ]);
            
            $lead->files()->save($leadFile);
            
            // Log activity
            $this->logActivity($lead->id, 'file_uploaded', 'File uploaded', [
                'file_id' => $leadFile->id,
                'file_name' => $originalName,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $leadFile->load('uploadedBy'),
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        } catch (\Exception $e) {
            // Delete the file if it was uploaded but database operation failed
            if (isset($path) && Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Change lead status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $lead = Lead::findOrFail($id);
            
            // Check permissions
            $this->authorize('update', $lead);
            
            $oldStatus = $lead->status;
            $newStatus = $request->input('status');
            
            // Don't do anything if status hasn't changed
            if ($oldStatus === $newStatus) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status unchanged',
                    'data' => $lead->load('status'),
                ]);
            }
            
            $lead->status = $newStatus;
            $lead->save();
            
            // Add a note if provided
            if ($request->filled('notes')) {
                $user = Auth::user();
                
                $note = new LeadNote([
                    'content' => "Status changed from {$oldStatus} to {$newStatus}. " . $request->input('notes'),
                    'is_private' => false,
                    'created_by' => $user->id,
                ]);
                
                $lead->notes()->save($note);
            }
            
            // Log activity
            $this->logActivity($lead->id, 'status_changed', 'Status changed', [
                'from' => $oldStatus,
                'to' => $newStatus,
                'notes' => $request->input('notes'),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $lead->load('status'),
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Assign lead to a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $lead = Lead::findOrFail($id);
            
            // Check permissions
            $this->authorize('assign', $lead);
            
            $oldUserId = $lead->assigned_to;
            $newUserId = $request->input('user_id');
            
            // Don't do anything if assignment hasn't changed
            if ($oldUserId == $newUserId) {
                return response()->json([
                    'success' => true,
                    'message' => 'Assignment unchanged',
                    'data' => $lead->load('assignedTo'),
                ]);
            }
            
            $lead->assigned_to = $newUserId;
            $lead->save();
            
            // Add a note if provided
            if ($request->filled('notes')) {
                $user = Auth::user();
                
                $note = new LeadNote([
                    'content' => 'Assignment notes: ' . $request->input('notes'),
                    'is_private' => false,
                    'created_by' => $user->id,
                ]);
                
                $lead->notes()->save($note);
            }
            
            // Log activity
            $this->logActivity($lead->id, 'assigned', 'Lead assigned', [
                'from' => $oldUserId,
                'to' => $newUserId,
                'notes' => $request->input('notes'),
            ]);
            
            // TODO: Send notification to the new assignee
            
            return response()->json([
                'success' => true,
                'message' => 'Lead assigned successfully',
                'data' => $lead->load('assignedTo'),
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get lead statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        try {
            $user = Auth::user();
            
            // Base query
            $query = Lead::query();
            
            // If user is not an admin, only show their assigned leads
            if (!$user->hasRole('admin')) {
                $query->where('assigned_to', $user->id);
            }
            
            // Total leads
            $totalLeads = (clone $query)->count();
            
            // Leads by status
            $leadsByStatus = (clone $query)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
            
            // Leads by source
            $leadsBySource = (clone $query)
                ->select('source', DB::raw('count(*) as count'))
                ->groupBy('source')
                ->pluck('count', 'source')
                ->toArray();
            
            // Leads created this month
            $leadsThisMonth = (clone $query)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            
            // Leads created last month
            $leadsLastMonth = (clone $query)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();
            
            // Calculate percentage change
            $monthlyChange = 0;
            if ($leadsLastMonth > 0) {
                $monthlyChange = (($leadsThisMonth - $leadsLastMonth) / $leadsLastMonth) * 100;
            } elseif ($leadsThisMonth > 0) {
                $monthlyChange = 100; // Infinite growth (from 0 to something)
            }
            
            // Recent activities
            $recentActivities = LeadActivity::with(['lead', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_leads' => $totalLeads,
                    'leads_by_status' => $leadsByStatus,
                    'leads_by_source' => $leadsBySource,
                    'leads_this_month' => $leadsThisMonth,
                    'leads_last_month' => $leadsLastMonth,
                    'monthly_change' => round($monthlyChange, 2),
                    'recent_activities' => $recentActivities,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lead statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Log an activity for the lead.
     *
     * @param  int  $leadId
     * @param  string  $activityType
     * @param  string  $description
     * @param  array  $metadata
     * @return void
     */
    protected function logActivity($leadId, $activityType, $description, $metadata = [])
    {
        try {
            $user = Auth::user();
            
            $activity = new LeadActivity([
                'lead_id' => $leadId,
                'activity_type' => $activityType,
                'description' => $description,
                'metadata' => $metadata,
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
            ]);
            
            $activity->save();
            
        } catch (\Exception $e) {
            // Log the error but don't fail the main operation
            \Log::error('Failed to log lead activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Sync tags for the lead.
     *
     * @param  \App\Models\Lead  $lead
     * @param  array  $tagNames
     * @return void
     */
    protected function syncTags($lead, $tagNames)
    {
        if (!is_array($tagNames)) {
            $tagNames = [$tagNames];
        }
        
        $tagIds = [];
        $user = Auth::user();
        
        foreach ($tagNames as $tagName) {
            if (empty(trim($tagName))) continue;
            
            // Find or create the tag
            $tag = LeadTag::firstOrCreate(
                ['name' => $tagName],
                [
                    'color' => '#' . substr(md5($tagName), 0, 6),
                    'created_by' => $user->id,
                ]
            );
            
            $tagIds[] = $tag->id;
        }
        
        // Sync the tags
        $lead->tags()->sync($tagIds);
    }
}
