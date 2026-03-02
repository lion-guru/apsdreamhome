<?php

namespace App\Services\Legacy;
/**
 * APS Dream Home - Media Library Integration for Dynamic Templates
 * Enhance dynamic templates with media library support
 */

require_once 'includes/config.php';

class MediaLibraryIntegration {
    private $db;
    private $mediaManager;

    public function __construct() {
        $this->db = \App\Core\App::database();
        require_once 'includes/media_library_manager.php';
        $this->mediaManager = new MediaLibraryManager();
    }

    /**
     * Get media files for dynamic templates
     */
    public function getMediaForTemplates($category = null, $limit = 10) {
        return $this->mediaManager->getMediaFiles($category, null, $limit);
    }

    /**
     * Get featured images for headers
     */
    public function getHeaderImages() {
        $sql = "SELECT * FROM media_library
                WHERE category IN ('general', 'property', 'project')
                AND mime_type LIKE 'image/%'
                ORDER BY upload_date DESC
                LIMIT 10";

        $results = $this->db->fetchAll($sql);
        $images = [];

        foreach ($results as $row) {
            $images[] = [
                'id' => $row['id'],
                'filename' => $row['filename'],
                'title' => $row['title'] ?: $row['original_name'],
                'url' => BASE_URL . 'uploads/media/' . $row['filename'],
                'description' => $row['description']
            ];
        }

        return $images;
    }

    /**
     * Get team member photos
     */
    public function getTeamPhotos() {
        $sql = "SELECT * FROM media_library
                WHERE category = 'team'
                AND mime_type LIKE 'image/%'
                ORDER BY upload_date ASC";

        $results = $this->db->fetchAll($sql);
        $photos = [];

        foreach ($results as $row) {
            $photos[] = [
                'id' => $row['id'],
                'filename' => $row['filename'],
                'title' => $row['title'] ?: $row['original_name'],
                'url' => BASE_URL . 'uploads/media/' . $row['filename'],
                'description' => $row['description'],
                'tags' => $row['tags']
            ];
        }

        return $photos;
    }

    /**
     * Get property images
     */
    public function getPropertyImages() {
        $sql = "SELECT * FROM media_library
                WHERE category = 'property'
                AND mime_type LIKE 'image/%'
                ORDER BY upload_date DESC";

        $results = $this->db->fetchAll($sql);
        $images = [];

        foreach ($results as $row) {
            $images[] = [
                'id' => $row['id'],
                'filename' => $row['filename'],
                'title' => $row['title'] ?: $row['original_name'],
                'url' => BASE_URL . 'uploads/media/' . $row['filename'],
                'description' => $row['description'],
                'tags' => $row['tags']
            ];
        }

        return $images;
    }

    /**
     * Get project images
     */
    public function getProjectImages() {
        $sql = "SELECT * FROM media_library
                WHERE category = 'project'
                AND mime_type LIKE 'image/%'
                ORDER BY upload_date DESC";

        $results = $this->db->fetchAll($sql);
        $images = [];

        foreach ($results as $row) {
            $images[] = [
                'id' => $row['id'],
                'filename' => $row['filename'],
                'title' => $row['title'] ?: $row['original_name'],
                'url' => BASE_URL . 'uploads/media/' . $row['filename'],
                'description' => $row['description'],
                'tags' => $row['tags']
            ];
        }

        return $images;
    }

    /**
     * Get documents for download
     */
    public function getDocuments() {
        $sql = "SELECT * FROM media_library
                WHERE category = 'document'
                ORDER BY upload_date DESC";

        $results = $this->db->fetchAll($sql);
        $documents = [];

        foreach ($results as $row) {
            $documents[] = [
                'id' => $row['id'],
                'filename' => $row['filename'],
                'original_name' => $row['original_name'],
                'title' => $row['title'] ?: $row['original_name'],
                'url' => BASE_URL . 'uploads/media/' . $row['filename'],
                'description' => $row['description'],
                'file_size' => $row['file_size'],
                'upload_date' => $row['upload_date']
            ];
        }

        return $documents;
    }

    /**
     * Get gallery images
     */
    public function getGalleryImages($limit = 20) {
        $sql = "SELECT * FROM media_library
                WHERE mime_type LIKE 'image/%'
                ORDER BY RAND()
                LIMIT ?";

        $results = $this->db->fetchAll($sql, [$limit]);
        $images = [];

        foreach ($results as $row) {
            $images[] = [
                'id' => $row['id'],
                'filename' => $row['filename'],
                'title' => $row['title'] ?: $row['original_name'],
                'url' => BASE_URL . 'uploads/media/' . $row['filename'],
                'description' => $row['description'],
                'category' => $row['category'],
                'tags' => $row['tags']
            ];
        }

        return $images;
    }

    /**
     * Get carousel images for homepage
     */
    public function getCarouselImages() {
        $sql = "SELECT * FROM media_library
                WHERE (category = 'general' OR tags LIKE '%carousel%' OR tags LIKE '%homepage%')
                AND mime_type LIKE 'image/%'
                ORDER BY upload_date DESC
                LIMIT 5";

        $results = $this->db->fetchAll($sql);
        $images = [];

        foreach ($results as $row) {
            $images[] = [
                'id' => $row['id'],
                'filename' => $row['filename'],
                'title' => $row['title'] ?: $row['original_name'],
                'url' => BASE_URL . 'uploads/media/' . $row['filename'],
                'description' => $row['description']
            ];
        }

        return $images;
    }

    /**
     * Render media gallery
     */
    public function renderMediaGallery($category = null, $columns = 4, $limit = 12) {
        $images = $this->getMediaForTemplates($category, $limit);

        if (empty($images)) {
            echo "<div class='alert alert-info'>No media files found.</div>";
            return;
        }

        echo "<div class='row'>\n";
        foreach ($images as $image) {
            if ($this->mediaManager->isImage($image['mime_type'])) {
                echo "<div class='col-md-$columns col-sm-6 mb-4'>\n";
                echo "<div class='card h-100'>\n";
                echo "<img src='" . BASE_URL . "uploads/media/" . $image['filename'] . "' class='card-img-top' alt='" . h($image['title']) . "'>\n";
                echo "<div class='card-body'>\n";
                echo "<h6 class='card-title'>" . h($image['title']) . "</h6>\n";
                if ($image['description']) {
                    echo "<p class='card-text small text-muted'>" . h($image['description']) . "</p>\n";
                }
                echo "</div>\n";
                echo "</div>\n";
                echo "</div>\n";
            }
        }
        echo "</div>\n";
    }

    /**
     * Render team section
     */
    public function renderTeamSection() {
        $teamMembers = $this->getTeamPhotos();

        if (empty($teamMembers)) {
            return;
        }

        echo "<section class='team-section py-5'>\n";
        echo "<div class='container'>\n";
        echo "<h2 class='text-center mb-5'>Our Team</h2>\n";
        echo "<div class='row'>\n";

        foreach ($teamMembers as $member) {
            echo "<div class='col-lg-3 col-md-6 mb-4'>\n";
            echo "<div class='text-center'>\n";
            echo "<img src='" . BASE_URL . "uploads/media/" . $member['filename'] . "' class='rounded-circle mb-3' alt='" . h($member['title']) . "' style='width: 150px; height: 150px; object-fit: cover;'>\n";
            echo "<h5>" . h($member['title']) . "</h5>\n";
            if ($member['description']) {
                echo "<p class='text-muted'>" . h($member['description']) . "</p>\n";
            }
            echo "</div>\n";
            echo "</div>\n";
        }

        echo "</div>\n";
        echo "</div>\n";
        echo "</section>\n";
    }

    /**
     * Render property showcase
     */
    public function renderPropertyShowcase() {
        $properties = $this->getPropertyImages();

        if (empty($properties)) {
            return;
        }

        echo "<section class='property-showcase py-5'>\n";
        echo "<div class='container'>\n";
        echo "<h2 class='text-center mb-5'>Featured Properties</h2>\n";
        echo "<div class='row'>\n";

        foreach ($properties as $property) {
            echo "<div class='col-lg-4 col-md-6 mb-4'>\n";
            echo "<div class='card h-100'>\n";
            echo "<img src='" . BASE_URL . "uploads/media/" . $property['filename'] . "' class='card-img-top' alt='" . h($property['title']) . "' style='height: 200px; object-fit: cover;'>\n";
            echo "<div class='card-body'>\n";
            echo "<h5 class='card-title'>" . h($property['title']) . "</h5>\n";
            if ($property['description']) {
                echo "<p class='card-text'>" . h(substr($property['description'], 0, 100)) . "...</p>\n";
            }
            echo "<a href='#' class='btn btn-primary'>View Details</a>\n";
            echo "</div>\n";
            echo "</div>\n";
            echo "</div>\n";
        }

        echo "</div>\n";
        echo "</div>\n";
        echo "</section>\n";
    }

    /**
     * Get media URL by ID
     */
    public function getMediaUrl($id) {
        $file = $this->mediaManager->getMediaFile($id);
        if ($file) {
            return BASE_URL . 'uploads/media/' . $file['filename'];
        }
        return null;
    }

    /**
     * Get media info by ID
     */
    public function getMediaInfo($id) {
        return $this->mediaManager->getMediaFile($id);
    }
}

// Global helper functions
function getMediaGallery($category = null, $columns = 4, $limit = 12) {
    $integration = new MediaLibraryIntegration($GLOBALS['conn'] ?? $GLOBALS['con'] ?? null);
    $integration->renderMediaGallery($category, $columns, $limit);
}

function getTeamSection() {
    $integration = new MediaLibraryIntegration($GLOBALS['conn'] ?? $GLOBALS['con'] ?? null);
    $integration->renderTeamSection();
}

function getPropertyShowcase() {
    $integration = new MediaLibraryIntegration($GLOBALS['conn'] ?? $GLOBALS['con'] ?? null);
    $integration->renderPropertyShowcase();
}

function getMediaUrl($id) {
    $integration = new MediaLibraryIntegration($GLOBALS['conn'] ?? $GLOBALS['con'] ?? null);
    return $integration->getMediaUrl($id);
}
?>
