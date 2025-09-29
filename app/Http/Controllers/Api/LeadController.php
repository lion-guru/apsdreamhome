<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\LeadFile;
use App\Models\LeadTag;
use App\Models\LeadStatus;
use App\Models\LeadSource;
use App\Models\LeadCustomField;
use App\Models\LeadDeal;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    /**
     * The LeadService instance.
     *
     * @var LeadService
     */
    protected $leadService;

    /**
     * Create a new controller instance.
     *
     * @param LeadService $leadService
     * @return void
     */
    public function __construct(LeadService $leadService)
    {
        $this->middleware('auth:api');
        $this->leadService = $leadService;
    }

    /**
     * Display a listing of the leads.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Start building the query
        $query = Lead::with([
            'status',
            'source',
            'assignedTo',
            'createdBy',
            'tags',
        ]);

        // Apply filters
        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('source_id')) {
            $query->where('source_id', $request->source_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Apply sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate the results
        $perPage = $request->input('per_page', 15);
        $leads = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $leads,
        ]);
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'status_id' => 'required|exists:lead_statuses,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'assigned_to' => 'nullable|exists:users,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $lead = $this->leadService->createLead($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully',
                'data' => $lead,
            ], 201);
        } catch (\Exception $e) {
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
        $lead = Lead::with([
            'status',
            'source',
            'assignedTo',
            'createdBy',
            'tags',
            'notes' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'files',
            'activities' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'statusHistory' => function ($query) {
                $query->with(['status', 'changedBy'])
                      ->orderBy('created_at', 'desc');
            },
            'assignmentHistory' => function ($query) {
                $query->with(['assignee', 'assigner', 'previousAssignee'])
                      ->orderBy('assigned_at', 'desc');
            },
            'customFields.field',
            'deals' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $lead,
        ]);
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
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:leads,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'status_id' => 'sometimes|exists:lead_statuses,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'assigned_to' => 'nullable|exists:users,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable',
            'status_notes' => 'nullable|string',
            'assignment_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $lead = $this->leadService->updateLead($lead, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully',
                'data' => $lead,
            ]);
        } catch (\Exception $e) {
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
        $lead = Lead::findOrFail($id);

        // Check if user has permission to delete
        $user = auth()->user();
        if (!$user->can('delete', $lead)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this lead',
            ], 403);
        }

        try {
            // Delete related records
            $lead->activities()->delete();
            $lead->notes()->delete();
            $lead->tags()->detach();
            $lead->customFields()->delete();
            
            // Delete files
            foreach ($lead->files as $file) {
                Storage::delete($file->file_path);
                $file->delete();
            }
            
            // Delete the lead
            $lead->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update lead status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:lead_statuses,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $this->leadService->updateLeadStatus(
                $lead, 
                $request->status_id, 
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Lead status updated successfully',
                'data' => $lead->load('status'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead status',
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
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $this->leadService->assignLead(
                $lead, 
                $request->assigned_to, 
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Lead assigned successfully',
                'data' => $lead->load('assignedTo'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign lead',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead notes.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotes($id)
    {
        $lead = Lead::findOrFail($id);
        $notes = $lead->notes()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    /**
     * Add a note to a lead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNote(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'is_private' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $note = new LeadNote([
                'content' => $request->content,
                'is_private' => $request->is_private ?? false,
                'user_id' => auth()->id(),
            ]);

            $lead->notes()->save($note);

            // Log the activity
            $this->leadService->logActivity($lead, 'note_added', [
                'title' => 'Note Added',
                'description' => 'A new note was added to the lead',
                'note_id' => $note->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note added successfully',
                'data' => $note->load('user'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a lead note.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $leadId
     * @param  int  $noteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNote(Request $request, $leadId, $noteId)
    {
        $note = LeadNote::where('lead_id', $leadId)
            ->findOrFail($noteId);

        // Check if user has permission to update this note
        $user = auth()->user();
        if ($note->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this note',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'is_private' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $note->update([
                'content' => $request->content,
                'is_private' => $request->is_private ?? $note->is_private,
            ]);

            // Log the activity
            $this->leadService->logActivity($note->lead, 'note_updated', [
                'title' => 'Note Updated',
                'description' => 'A note was updated',
                'note_id' => $note->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully',
                'data' => $note->load('user'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a lead note.
     *
     * @param  int  $leadId
     * @param  int  $noteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNote($leadId, $noteId)
    {
        $note = LeadNote::where('lead_id', $leadId)
            ->findOrFail($noteId);

        // Check if user has permission to delete this note
        $user = auth()->user();
        if ($note->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this note',
            ], 403);
        }

        try {
            // Log the activity before deleting
            $this->leadService->logActivity($note->lead, 'note_deleted', [
                'title' => 'Note Deleted',
                'description' => 'A note was deleted',
                'note_id' => $note->id,
            ]);

            $note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead files.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFiles($id)
    {
        $lead = Lead::findOrFail($id);
        $files = $lead->files()
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $files,
        ]);
    }

    /**
     * Upload a file to a lead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFile(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // Max 10MB
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_private' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = Str::random(40) . '.' . $extension;
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();

            // Store the file
            $path = $file->storeAs('leads/' . $id, $fileName, 'public');

            // Create file record
            $leadFile = new LeadFile([
                'lead_id' => $lead->id,
                'uploaded_by' => auth()->id(),
                'file_name' => $originalName,
                'file_path' => $path,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'title' => $request->title ?? $originalName,
                'description' => $request->description,
                'is_private' => $request->is_private ?? false,
            ]);

            $lead->files()->save($leadFile);

            // Log the activity
            $this->leadService->logActivity($lead, 'file_uploaded', [
                'title' => 'File Uploaded',
                'description' => 'A new file was uploaded to the lead',
                'file_id' => $leadFile->id,
                'file_name' => $originalName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $leadFile->load('uploader'),
            ], 201);
        } catch (\Exception $e) {
            // Delete the file if it was uploaded but the database operation failed
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a lead file.
     *
     * @param  int  $leadId
     * @param  int  $fileId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFile($leadId, $fileId)
    {
        $file = LeadFile::where('lead_id', $leadId)
            ->findOrFail($fileId);

        // Check if user has permission to delete this file
        $user = auth()->user();
        if ($file->uploaded_by !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this file',
            ], 403);
        }

        try {
            $filePath = $file->file_path;
            
            // Log the activity before deleting
            $this->leadService->logActivity($file->lead, 'file_deleted', [
                'title' => 'File Deleted',
                'description' => 'A file was deleted from the lead',
                'file_id' => $file->id,
                'file_name' => $file->file_name,
            ]);

            // Delete the file record
            $file->delete();

            // Delete the actual file
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a lead file.
     *
     * @param  int  $leadId
     * @param  int  $fileId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadFile($leadId, $fileId)
    {
        $file = LeadFile::where('lead_id', $leadId)
            ->findOrFail($fileId);

        // Check if user has permission to download this file
        $user = auth()->user();
        if ($file->is_private && $file->uploaded_by !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to download this file',
            ], 403);
        }

        $filePath = storage_path('app/public/' . $file->file_path);

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        // Log the download activity
        $this->leadService->logActivity($file->lead, 'file_downloaded', [
            'title' => 'File Downloaded',
            'description' => 'A file was downloaded from the lead',
            'file_id' => $file->id,
            'file_name' => $file->file_name,
        ]);

        return response()->download($filePath, $file->file_name);
    }

    /**
     * Get lead activities.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivities($id)
    {
        $lead = Lead::findOrFail($id);
        $activities = $lead->activities()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get lead tags.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTags($id)
    {
        $lead = Lead::findOrFail($id);
        $tags = $lead->tags;

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Add a tag to a lead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addTag(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find or create the tag
            $tag = LeadTag::firstOrCreate(
                ['name' => $request->name],
                [
                    'color' => $this->generateRandomColor(),
                    'created_by' => auth()->id(),
                ]
            );

            // Attach the tag to the lead if not already attached
            if (!$lead->tags->contains($tag->id)) {
                $lead->tags()->attach($tag->id, ['created_by' => auth()->id()]);

                // Log the activity
                $this->leadService->logActivity($lead, 'tag_added', [
                    'title' => 'Tag Added',
                    'description' => 'A tag was added to the lead',
                    'tag_id' => $tag->id,
                    'tag_name' => $tag->name,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tag added successfully',
                'data' => $tag,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add tag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a tag from a lead.
     *
     * @param  int  $leadId
     * @param  int  $tagId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeTag($leadId, $tagId)
    {
        $lead = Lead::findOrFail($leadId);
        $tag = $lead->tags()->findOrFail($tagId);

        try {
            // Log the activity before detaching
            $this->leadService->logActivity($lead, 'tag_removed', [
                'title' => 'Tag Removed',
                'description' => 'A tag was removed from the lead',
                'tag_id' => $tag->id,
                'tag_name' => $tag->name,
            ]);

            // Detach the tag
            $lead->tags()->detach($tag->id);

            return response()->json([
                'success' => true,
                'message' => 'Tag removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove tag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead custom fields.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomFields($id)
    {
        $lead = Lead::findOrFail($id);
        $customFields = LeadCustomField::with(['values' => function ($query) use ($id) {
            $query->where('lead_id', $id);
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $customFields,
        ]);
    }

    /**
     * Update lead custom fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCustomFields(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $customFields = $request->all();

        try {
            $this->leadService->updateCustomFields($lead, $customFields);

            return response()->json([
                'success' => true,
                'message' => 'Custom fields updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update custom fields',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead deals.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeals($id)
    {
        $lead = Lead::findOrFail($id);
        $deals = $lead->deals()
            ->with(['createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $deals,
        ]);
    }

    /**
     * Create a new deal for a lead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDeal(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'deal_name' => 'required|string|max:255',
            'deal_value' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'expected_close_date' => 'required|date',
            'probability' => 'nullable|integer|min:0|max:100',
            'deal_stage' => 'required|string|in:prospect,qualification,needs_analysis,proposal,negotiation,won,lost',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:open,in_progress,on_hold,closed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $deal = $this->leadService->createDeal($lead, array_merge(
                $request->all(),
                ['created_by' => auth()->id()]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Deal created successfully',
                'data' => $deal->load(['createdBy', 'updatedBy']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create deal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a lead deal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $leadId
     * @param  int  $dealId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDeal(Request $request, $leadId, $dealId)
    {
        $deal = LeadDeal::where('lead_id', $leadId)
            ->findOrFail($dealId);

        $validator = Validator::make($request->all(), [
            'deal_name' => 'sometimes|required|string|max:255',
            'deal_value' => 'sometimes|required|numeric|min:0',
            'currency' => 'sometimes|required|string|size:3',
            'expected_close_date' => 'sometimes|required|date',
            'probability' => 'nullable|integer|min:0|max:100',
            'deal_stage' => 'sometimes|required|string|in:prospect,qualification,needs_analysis,proposal,negotiation,won,lost',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:open,in_progress,on_hold,closed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $originalData = $deal->getOriginal();
            $deal->fill($request->all());
            $deal->updated_by = auth()->id();
            $deal->save();

            // Log the activity if any changes were made
            if ($deal->wasChanged()) {
                $changes = $deal->getChanges();
                
                $this->leadService->logActivity($deal->lead, 'deal_updated', [
                    'title' => 'Deal Updated',
                    'description' => 'A deal was updated',
                    'deal_id' => $deal->id,
                    'changes' => $this->formatDealChanges($originalData, $changes),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Deal updated successfully',
                'data' => $deal->load(['createdBy', 'updatedBy']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update deal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a lead deal.
     *
     * @param  int  $leadId
     * @param  int  $dealId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDeal($leadId, $dealId)
    {
        $deal = LeadDeal::where('lead_id', $leadId)
            ->findOrFail($dealId);

        // Check if user has permission to delete this deal
        $user = auth()->user();
        if ($deal->created_by !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this deal',
            ], 403);
        }

        try {
            // Log the activity before deleting
            $this->leadService->logActivity($deal->lead, 'deal_deleted', [
                'title' => 'Deal Deleted',
                'description' => 'A deal was deleted',
                'deal_id' => $deal->id,
                'deal_name' => $deal->deal_name,
            ]);

            $deal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deal deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete deal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOverviewStats()
    {
        $user = auth()->user();
        
        $query = Lead::query();
        
        // If user is not admin, only show their assigned leads
        if (!$user->hasRole('admin')) {
            $query->where('assigned_to', $user->id);
        }
        
        $totalLeads = $query->count();
        $newLeads = $query->where('created_at', '>=', now()->subDays(7))->count();
        $convertedLeads = $query->whereHas('deals', function ($q) {
            $q->where('deal_stage', 'won');
        })->count();
        
        $conversionRate = $totalLeads > 0 
            ? round(($convertedLeads / $totalLeads) * 100, 2) 
            : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_leads' => $totalLeads,
                'new_leads' => $newLeads,
                'converted_leads' => $convertedLeads,
                'conversion_rate' => $conversionRate,
            ],
        ]);
    }

    /**
     * Get lead status statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatusStats()
    {
        $user = auth()->user();
        
        $query = LeadStatus::withCount(['leads' => function ($q) use ($user) {
            if (!$user->hasRole('admin')) {
                $q->where('assigned_to', $user->id);
            }
        }])->where('is_active', true);
        
        $statuses = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }

    /**
     * Get lead source statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSourceStats()
    {
        $user = auth()->user();
        
        $query = LeadSource::withCount(['leads' => function ($q) use ($user) {
            if (!$user->hasRole('admin')) {
                $q->where('assigned_to', $user->id);
            }
        }])->where('is_active', true);
        
        $sources = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $sources,
        ]);
    }

    /**
     * Get lead assigned to statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignedToStats()
    {
        $user = auth()->user();
        
        // Only admins can see stats for all users
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $users = User::withCount('assignedLeads')
            ->whereHas('assignedLeads')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get lead created by statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreatedByStats()
    {
        $user = auth()->user();
        
        // Only admins can see stats for all users
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $users = User::withCount('createdLeads')
            ->whereHas('createdLeads')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get lead timeline statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTimelineStats()
    {
        $user = auth()->user();
        
        $query = Lead::query();
        
        // If user is not admin, only show their assigned leads
        if (!$user->hasRole('admin')) {
            $query->where('assigned_to', $user->id);
        }
        
        // Get leads created in the last 12 months by month
        $leadsByMonth = $query->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();
        
        // Format the data for the chart
        $labels = [];
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $monthName = $date->format('M Y');
            
            $count = $leadsByMonth->first(function ($item) use ($year, $month) {
                return $item->year == $year && $item->month == $month;
            });
            
            $labels[] = $monthName;
            $data[] = $count ? $count->count : 0;
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Leads',
                        'data' => $data,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get all lead statuses for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatuses()
    {
        $statuses = LeadStatus::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'color', 'is_default']);
            
        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }

    /**
     * Get all lead sources for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSources()
    {
        $sources = LeadSource::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'icon']);
            
        return response()->json([
            'success' => true,
            'data' => $sources,
        ]);
    }

    /**
     * Get all lead tags for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTags()
    {
        $tags = LeadTag::orderBy('name')
            ->get(['id', 'name', 'color']);
            
        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Get all users for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers()
    {
        $users = User::orderBy('name')
            ->get(['id', 'name', 'email', 'avatar']);
            
        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get all custom field definitions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomFieldDefinitions()
    {
        $fields = LeadCustomField::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $fields,
        ]);
    }

    /**
     * Get all deal stages for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDealStages()
    {
        $stages = LeadDeal::getDealStages();
            
        return response()->json([
            'success' => true,
            'data' => $stages,
        ]);
    }

    /**
     * Format deal changes for activity log.
     *
     * @param  array  $original
     * @param  array  $changes
     * @return array
     */
    protected function formatDealChanges(array $original, array $changes): array
    {
        $formatted = [];
        $ignoredFields = ['updated_at', 'updated_by'];
        
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignoredFields)) {
                continue;
            }
            
            $oldValue = $original[$field] ?? null;
            
            // Format values based on field type
            switch ($field) {
                case 'expected_close_date':
                    $oldValue = $oldValue ? Carbon::parse($oldValue)->toDateString() : null;
                    $newValue = $newValue ? Carbon::parse($newValue)->toDateString() : null;
                    break;
                    
                case 'deal_value':
                    $oldValue = $oldValue ? number_format($oldValue, 2) : null;
                    $newValue = $newValue ? number_format($newValue, 2) : null;
                    break;
                    
                case 'probability':
                    $oldValue = $oldValue ? $oldValue . '%' : null;
                    $newValue = $newValue ? $newValue . '%' : null;
                    break;
            }
            
            $formatted[$field] = [
                'from' => $oldValue,
                'to' => $newValue,
            ];
        }
        
        return $formatted;
    }

    /**
     * Generate a random color for tags.
     *
     * @return string
     */
    protected function generateRandomColor(): string
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
}
