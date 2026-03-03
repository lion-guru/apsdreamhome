<?php
namespace App\Services;

class DocumentVersioning {
    private $db;
    
    public function __construct() {
        $this->db = new \App\Core\Database();
    }
    
    public function saveVersion($documentId, $content, $userId, $comment = "") {
        $query = "INSERT INTO document_versions (document_id, content, user_id, comment, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        
        return $this->db->query($query, [$documentId, $content, $userId, $comment]);
    }
    
    public function getVersions($documentId, $limit = 10) {
        $query = "SELECT * FROM document_versions 
                  WHERE document_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        
        return $this->db->query($query, [$documentId, $limit])->fetchAll();
    }
    
    public function getVersion($documentId, $versionId) {
        $query = "SELECT * FROM document_versions 
                  WHERE document_id = ? AND id = ?";
        
        return $this->db->query($query, [$documentId, $versionId])->fetch();
    }
    
    public function restoreVersion($documentId, $versionId, $userId) {
        $version = $this->getVersion($documentId, $versionId);
        
        if ($version) {
            return $this->saveVersion($documentId, $version["content"], $userId, 
                "Restored from version " . $versionId);
        }
        
        return false;
    }
    
    public function compareVersions($documentId, $version1Id, $version2Id) {
        $v1 = $this->getVersion($documentId, $version1Id);
        $v2 = $this->getVersion($documentId, $version2Id);
        
        if ($v1 && $v2) {
            return $this->calculateDiff($v1["content"], $v2["content"]);
        }
        
        return null;
    }
    
    private function calculateDiff($content1, $content2) {
        // Simple diff calculation
        $lines1 = explode("\n", $content1);
        $lines2 = explode("\n", $content2);
        
        $diff = [];
        $maxLines = max(count($lines1), count($lines2));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $line1 = $lines1[$i] ?? "";
            $line2 = $lines2[$i] ?? "";
            
            if ($line1 !== $line2) {
                $diff[] = [
                    "line" => $i + 1,
                    "old" => $line1,
                    "new" => $line2,
                    "type" => $this->getChangeType($line1, $line2)
                ];
            }
        }
        
        return $diff;
    }
    
    private function getChangeType($old, $new) {
        if ($old === "") {
            return "added";
        } elseif ($new === "") {
            return "removed";
        } else {
            return "modified";
        }
    }
}
