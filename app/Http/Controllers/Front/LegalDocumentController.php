<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use App\Services\LegalDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalDocumentController extends Controller
{
    protected $legalService;

    public function __construct(LegalDocumentService $legalService)
    {
        $this->legalService = $legalService;
    }

    /**
     * Display a legal document by slug.
     */
    public function show($slug)
    {
        $document = LegalDocument::published()
            ->where('slug', $slug)
            ->firstOrFail();

        // Check if user needs to accept this document
        $user = Auth::user();
        $accepted = false;
        if ($user) {
            $accepted = $document->isAcceptedBy($user);
        }

        // Get related documents in same category
        $related = LegalDocument::published()
            ->where('category', $document->category)
            ->where('id', '!=', $document->id)
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('legal.show', compact('document', 'accepted', 'related'));
    }

    /**
     * List all legal documents by category.
     */
    public function index($category = null)
    {
        $documents = LegalDocument::published()
            ->when($category, function ($q, $cat) {
                $q->category($cat);
            })
            ->orderBy('category')
            ->orderBy('document_type')
            ->orderBy('title')
            ->get()
            ->groupBy('category');

        $categories = [
            'company' => 'Company Policies',
            'associate' => 'Associate/MLM',
            'agent' => 'Agent/Sales',
            'booking' => 'Booking & Reservations',
            'general' => 'General',
        ];

        return view('legal.index', compact('documents', 'categories', 'category'));
    }

    /**
     * Accept a legal document (AJAX).
     */
    public function accept(Request $request, $slug)
    {
        $request->validate([
            'document_id' => 'required|exists:legal_documents,id',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $document = LegalDocument::findOrFail($request->document_id);
        
        if (!$document->is_mandatory) {
            return response()->json(['success' => false, 'message' => 'This document is not mandatory.'], 400);
        }

        $legalService = app(\App\Services\LegalDocumentService::class);
        $legalService->acceptDocument(auth()->user(), LegalDocument::find($request->document_id));

        return response()->json([
            'success' => true,
            'message' => 'Document accepted successfully.',
            'redirect' => $request->input('redirect') ?? url()->previous(),
        ]);
    }

    /**
     * Get unaccepted mandatory documents for current user (for middleware).
     */
    public function getUnaccepted()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([]);
        }

        $legalService = app(\App\Services\LegalDocumentService::class);
        $unaccepted = $legalService->getUnacceptedMandatoryDocuments(auth()->user());

        return response()->json($unaccepted->map(function ($doc) {
            return [
                'id' => $doc->id,
                'slug' => $doc->slug,
                'title' => $doc->title,
                'category' => $doc->category,
                'document_type' => $doc->document_type,
                'version' => $doc->version,
                'url' => $doc->url,
            ];
        }));
    }
}