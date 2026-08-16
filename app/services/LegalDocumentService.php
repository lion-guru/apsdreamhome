<?php

namespace App\Services;

use App\Models\LegalDocument;
use App\Models\LegalDocumentAcceptance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LegalDocumentService
{
    /**
     * Get all mandatory documents for a user role.
     */
    public function getMandatoryDocumentsForRole(string $role): \Illuminate\Database\Eloquent\Collection
    {
        return LegalDocument::published()
            ->mandatory()
            ->forRole($role)
            ->orderBy('category')
            ->orderBy('document_type')
            ->get();
    }

    /**
     * Get all applicable documents for a user role.
     */
    public function getApplicableDocumentsForRole(string $role): \Illuminate\Database\Eloquent\Collection
    {
        return LegalDocument::published()
            ->forRole($role)
            ->orderBy('category')
            ->orderBy('document_type')
            ->get();
    }

    /**
     * Get unaccepted mandatory documents for a user.
     */
    public function getUnacceptedMandatoryDocuments($user): \Illuminate\Database\Eloquent\Collection
    {
        $role = $this->getUserRole($user);
        $mandatoryDocs = $this->getMandatoryDocumentsForRole($role);

        return $mandatoryDocs->filter(function ($doc) use ($user) {
            return !$doc->isAcceptedBy($user);
        })->values();
    }

    /**
     * Check if user has accepted all mandatory documents.
     */
    public function hasAcceptedAllMandatory($user): bool
    {
        return $this->getUnacceptedMandatoryDocuments($user)->isEmpty();
    }

    /**
     * Accept a legal document on behalf of a user.
     */
    public function acceptDocument($user, LegalDocument $document, string $version = null, array $metadata = []): LegalDocumentAcceptance
    {
        $version = $version ?? $document->version;

        return LegalDocumentAcceptance::updateOrCreate(
            [
                'legal_document_id' => $document->id,
                'user_id' => $user->getKey(),
                'user_type' => get_class($user),
            ],
            [
                'version' => $version,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );
    }

    /**
     * Accept multiple documents at once.
     */
    public function acceptDocuments($user, array $documentIds, array $versions = []): int
    {
        $count = 0;
        foreach ($documentIds as $id) {
            $document = LegalDocument::find($id);
            if ($document && $document->is_mandatory) {
                $version = $versions[$id] ?? null;
                $this->acceptDocument($user, $document, $versions[$id] ?? null);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Check if user needs to accept documents before proceeding.
     */
    public function requiresAcceptance($user): bool
    {
        return !$this->hasAcceptedAllMandatory($user);
    }

    /**
     * Get the next unaccepted mandatory document for user.
     */
    public function getNextUnacceptedDocument($user): ?\App\Models\LegalDocument
    {
        return $this->getUnacceptedMandatoryDocuments($user)->first();
    }

    /**
     * Get user role from user model.
     */
    protected function getUserRole($user): string
    {
        if ($user instanceof \App\Models\Admin || $user->hasRole('admin')) {
            return 'admin';
        }
        if ($user instanceof \App\Models\Associate || $user->hasRole('associate')) {
            return 'associate';
        }
        if ($user instanceof \App\Models\Agent || $user->hasRole('agent')) {
            return 'agent';
        }
        if ($user instanceof \App\Models\Employee || $user->hasRole('employee')) {
            return 'employee';
        }
        return 'customer';
    }

    /**
     * Get documents by category for display.
     */
    public function getDocumentsByCategory(string $category = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = LegalDocument::published()
            ->orderBy('category')
            ->orderBy('document_type')
            ->orderBy('title');

        if ($category) {
            $query->category($category);
        }

        return $query->get()->groupBy('category');
    }

    /**
     * Get the latest version of a document for display.
     */
    public function getDocumentForDisplay(string $slug): ?\App\Models\LegalDocument
    {
        return LegalDocument::published()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get document by slug and category.
     */
    public function getDocumentBySlugAndCategory(string $slug, string $category): ?\App\Models\LegalDocument
    {
        return LegalDocument::published()
            ->where('slug', $slug)
            ->where('category', $category)
            ->first();
    }

    /**
     * Clear cache for legal documents.
     */
    public function clearCache(): void
    {
        Cache::forget('legal_documents_all');
        Cache::forget('legal_documents_mandatory');
    }

    /**
     * Get acceptance rate for a document.
     */
    public function getAcceptanceRate(LegalDocument $document): float
    {
        $totalUsers = $this->getTotalApplicableUsers($document);
        if ($totalUsers === 0) {
            return 0;
        }

        $accepted = $document->acceptances()->count();

        return round(($accepted / $totalUsers) * 100, 2);
    }

    /**
     * Get total applicable users for a document.
     */
    protected function getTotalApplicableUsers(LegalDocument $document): int
    {
        // This would need to be implemented based on your user models
        // For now, return a placeholder
        return 0;
    }

    /**
     * Get documents that need user acceptance (for middleware).
     */
    public function getDocumentsForMiddleware($user): array
    {
        $unaccepted = $this->getUnacceptedMandatoryDocuments($user);

        return $unaccepted->map(function ($doc) {
            return [
                'id' => $doc->id,
                'slug' => $doc->slug,
                'title' => $doc->title,
                'category' => $doc->category,
                'document_type' => $doc->document_type,
                'version' => $doc->version,
                'url' => $doc->url,
            ];
        })->toArray();
    }
}