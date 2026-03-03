<?php
namespace App\Services;

class ConflictResolution {
    private $operations = [];
    
    public function addOperation($userId, $operation, $content, $position, $timestamp) {
        $this->operations[] = [
            "user_id" => $userId,
            "operation" => $operation,
            "content" => $content,
            "position" => $position,
            "timestamp" => $timestamp
        ];
        
        // Sort by timestamp
        usort($this->operations, function($a, $b) {
            return $a["timestamp"] - $b["timestamp"];
        });
        
        return $this->resolveConflicts();
    }
    
    public function resolveConflicts() {
        $resolved = [];
        $currentContent = "";
        
        foreach ($this->operations as $operation) {
            $conflict = $this->detectConflict($operation, $resolved);
            
            if ($conflict) {
                $resolved[] = $this->resolveConflict($operation, $conflict);
            } else {
                $resolved[] = $operation;
            }
        }
        
        return $resolved;
    }
    
    private function detectConflict($operation, $previousOperations) {
        foreach ($previousOperations as $prev) {
            if ($this->operationsOverlap($operation, $prev)) {
                return $prev;
            }
        }
        
        return null;
    }
    
    private function operationsOverlap($op1, $op2) {
        $pos1 = $op1["position"];
        $pos2 = $op2["position"];
        $len1 = strlen($op1["content"]);
        $len2 = strlen($op2["content"]);
        
        // Check if operations affect overlapping regions
        return !($pos1 + $len1 <= $pos2 || $pos2 + $len2 <= $pos1);
    }
    
    private function resolveConflict($operation, $conflict) {
        // Simple conflict resolution: prioritize the operation with earlier timestamp
        if ($operation["timestamp"] < $conflict["timestamp"]) {
            return $operation;
        } else {
            // Adjust position of the conflicting operation
            $adjustment = strlen($operation["content"]);
            $conflict["position"] += $adjustment;
            return $conflict;
        }
    }
    
    public function applyOperations($content, $operations) {
        foreach ($operations as $operation) {
            $content = $this->applyOperation($content, $operation);
        }
        
        return $content;
    }
    
    private function applyOperation($content, $operation) {
        $position = $operation["position"];
        $opContent = $operation["content"];
        
        switch ($operation["operation"]) {
            case "insert":
                return substr($content, 0, $position) . $opContent . substr($content, $position);
                
            case "delete":
                return substr($content, 0, $position) . substr($content, $position + strlen($opContent));
                
            case "replace":
                return substr($content, 0, $position) . $opContent . substr($content, $position + strlen($opContent));
                
            default:
                return $content;
        }
    }
}
