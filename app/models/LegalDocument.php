<?php

namespace App\Models;

use App\Core\UnifiedModel;
use Illuminate\Support\Str;

/**
 * LegalDocument Model
 * Manages legal documents (Terms, Privacy, Booking Terms, etc.)
 */
class LegalDocument extends \App\Core\UnifiedModel
{
    public static $table = 'legal_documents';
    public static $primaryKey = 'id';
    protected static $tenantScoped = false; // Legal documents are global

    protected $fillable = [
        'slug',
        'title',
        'category',
        'document_type',
        'content',
        'summary',
        'version',
        'status',
        'is_mandatory',
        'applies_to_roles',
        'metadata',
        'created_by',
        'updated_by',
        'published_at',
        'effective_from',
        'expires_at',
    ];

    protected $casts = [
        'applies_to_roles' => 'array',
        'metadata' => 'array',
        'is_mandatory' => 'boolean',
        'published_at' => 'datetime',
        'effective_from' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Scope: Published documents only
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', date('Y-m-d H:i:s'));
            })
            ->where(function ($q) {
                $q->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', date('Y-m-d H:i:s'));
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', date('Y-m-d H:i:s'));
            });
    }

    /**
     * Scope: Mandatory documents
     */
    public function scopeMandatory($query)
    {
        return $query->where('status', 'published')
            ->where('is_mandatory', true);
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: By document type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope: For role
     */
    public function scopeForRole($query, string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('applies_to_roles')
                ->orWhereRaw('JSON_CONTAINS(applies_to_roles, ?)', [json_encode($role)]);
        });
    }

    /**
     * Scope: By slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get the URL for this document.
     */
    public function getUrlAttribute(): string
    {
        return BASE_URL . '/legal/' . $this->slug;
    }

    /**
     * Scope: Published documents only
     */
    public static function published(): array
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $now = date('Y-m-d H:i:s');
        $sql = "
            SELECT * FROM legal_documents 
            WHERE status = 'published'
            AND (published_at IS NULL OR published_at <= ?)
            AND (effective_from IS NULL OR effective_from <= ?)
            AND (expires_at IS NULL OR expires_at > ?)
            AND deleted_at IS NULL
            ORDER BY created_at DESC
        ";
        $stmt = \App\Core\Database\Database::getInstance()->prepare($sql);
        $stmt->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $doc = new static($row);
            $doc->exists = true;
            return $doc;
        }, $results);
    }

    /**
     * Scope: Mandatory documents
     */
    public static function mandatory(): array
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $now = date('Y-m-d H:i:s');
        $sql = "
            SELECT * FROM legal_documents 
            WHERE status = 'published' 
            AND is_mandatory = 1
            AND (published_at IS NULL OR published_at <= ?)
            AND (effective_from IS NULL OR effective_from <= ?)
            AND (expires_at IS NULL OR expires_at > ?)
            AND deleted_at IS NULL
            ORDER BY created_at DESC
        ";
        $stmt = \App\Core\Database\Database::getInstance()->prepare($sql);
        $stmt->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $doc = new static($row);
            $doc->exists = true;
            return $doc;
        }, $results);
    }

    /**
     * Scope: By category
     */
    public static function category(string $category): array
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $sql = "SELECT * FROM legal_documents WHERE category = ? AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = \App\Core\Database\Database::getInstance()->prepare($sql);
        $stmt->execute([$category]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $doc = new static($row);
            $doc->exists = true;
            return $doc;
        }, $results);
    }

    /**
     * Scope: By document type
     */
    public static function type(string $type): array
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $sql = "SELECT * FROM legal_documents WHERE document_type = ? AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = \App\Core\Database\Database::getInstance()->prepare($sql);
        $stmt->execute([$type]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $doc = new static($row);
            $doc->exists = true;
            return $doc;
        }, $results);
    }

    /**
     * Scope: For role
     */
    public static function forRole(string $role): array
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $sql = "
            SELECT * FROM legal_documents 
            WHERE (applies_to_roles IS NULL OR JSON_CONTAINS(applies_to_roles, ?))
            AND deleted_at IS NULL
            ORDER BY created_at DESC
        ";
        $stmt = \App\Core\Database\Database::getInstance()->prepare($sql);
        $stmt->execute([json_encode($role)]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $doc = new static($row);
            $doc->exists = true;
            return $doc;
        }, $results);
    }

    /**
     * Scope: By slug
     */
    public static function bySlug(string $slug): ?static
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $row = $db->query("SELECT * FROM legal_documents WHERE slug = ? AND deleted_at IS NULL", [$slug])->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        $doc = new static($row);
        $doc->exists = true;
        return $doc;
    }

    /**
     * Check if user has accepted this document.
     */
    public function isAcceptedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        // Check if user has accepted this document
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $userId = is_object($user) ? ($user->id ?? $user->getKey()) : $user;
        $userType = is_object($user) ? get_class($user) : 'user';

        $stmt = $db->prepare("
            SELECT COUNT(*) as cnt FROM legal_document_acceptances 
            WHERE legal_document_id = ? AND user_id = ? AND user_type = ?
        ");
        $stmt->execute([$this->id, $userId, $userType]);
        $count = $stmt->fetchColumn();

        return $count > 0;
    }

    /**
     * Get the versions for this document.
     */
    public function getVersions(): array
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM legal_document_versions WHERE legal_document_id = ? ORDER BY version DESC");
        $stmt->execute([$this->id]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $version = new LegalDocumentVersion($row);
            $version->exists = true;
            return $version;
        }, $rows);
    }

    /**
     * Get the acceptances for this document.
     */
    public function getAcceptances(): array
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM legal_document_acceptances WHERE legal_document_id = ? ORDER BY accepted_at DESC");
        $stmt->execute([$this->id]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $acceptance = new LegalDocumentAcceptance($row);
            $acceptance->exists = true;
            return $acceptance;
        }, $rows);
    }

    /**
     * Get the creator of this document.
     */
    public function getCreator()
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$this->created_by]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        $user = new \App\Models\User($row);
        $user->exists = true;
        return $user;
    }

    /**
     * Get the last updater of this document.
     */
    public function getUpdater()
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$this->updated_by]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        $user = new \App\Models\User($row);
        $user->exists = true;
        return $user;
    }

    /**
     * Check if user has accepted this document.
     */
    public function isAcceptedByUser($user): bool
    {
        if (!$user) {
            return false;
        }
        $userId = $user->id ?? $user->getKey();
        $userType = get_class($user);

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $count = $db->query("
            SELECT COUNT(*) as cnt FROM legal_document_acceptances 
            WHERE legal_document_id = ? AND user_id = ? AND user_type = ?
        ", [$this->id, $userId, $userType])->fetchColumn();

        return $count > 0;
    }

    /**
     * Create a new version of this document.
     */
    public function createVersion(string $changeSummary = null, $user = null): LegalDocumentVersion
    {
        $version = new LegalDocumentVersion();
        $version->legal_document_id = $this->id;
        $version->version = $this->version;
        $version->content = $this->content;
        $version->change_summary = $changeSummary;
        $version->created_by = $user?->id ?? $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        $version->save();

        return $version;
    }

    /**
     * Save the document with auto-slug and version handling.
     */
    public function save(array $options = []): bool
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->title);
        }
        if (empty($this->version)) {
            $this->version = '1.0';
        }
        if ($this->status === 'published' && empty($this->published_at)) {
            $this->published_at = date('Y-m-d H:i:s');
        }

        // JSON encode array fields
        $this->applies_to_roles = is_array($this->applies_to_roles) ? json_encode($this->applies_to_roles) : $this->applies_to_roles;
        $this->metadata = is_array($this->metadata) ? json_encode($this->metadata) : $this->metadata;

        $result = parent::save($options);

        // Create initial version if new
        if (empty($this->wasRecentlyCreated)) {
            $this->createVersion('Initial version');
        }

        return $result;
    }

    /**
     * Update the document with version tracking.
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        $oldContent = $this->content;
        $oldVersion = $this->version;

        // JSON encode array fields
        if (isset($attributes['applies_to_roles']) && is_array($attributes['applies_to_roles'])) {
            $attributes['applies_to_roles'] = json_encode($attributes['applies_to_roles']);
        }
        if (isset($attributes['metadata']) && is_array($attributes['metadata'])) {
            $attributes['metadata'] = json_encode($attributes['metadata']);
        }

        $result = parent::update($attributes, $options);

        // Create version if content changed significantly
        if ($oldContent !== $this->content) {
            $this->createVersion('Content updated');
        }

        return $result;
    }
}