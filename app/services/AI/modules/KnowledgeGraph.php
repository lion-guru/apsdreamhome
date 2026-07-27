<?php

namespace App\Services\AI\Modules;

use PDO;

/**
 * KnowledgeGraph - Manages AI's internal knowledge base and entity relationships.
 * Stores entities, relations, and supports traversal queries for context retrieval.
 *
 * DB tables: ai_knowledge_entities, ai_knowledge_relations
 */
class KnowledgeGraph
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: \App\Core\Database\Database::getInstance();
    }

    /**
     * Query entities by name, type, or relation
     */
    public function query($entity, $relation = null): array
    {
        try {
            if ($relation) {
                $sql = "SELECT e.*, kr.relation_type, kr.target_entity_id, te.entity_name AS target_name
                        FROM ai_knowledge_entities e
                        JOIN ai_knowledge_relations kr ON kr.source_entity_id = e.id
                        JOIN ai_knowledge_entities te ON te.id = kr.target_entity_id
                        WHERE e.entity_name LIKE ? AND kr.relation_type = ?
                        ORDER BY kr.confidence DESC LIMIT 20";
                $rows = $this->db->fetchAll($sql, ["%{$entity}%", $relation]);
            } else {
                $sql = "SELECT * FROM ai_knowledge_entities WHERE entity_name LIKE ? ORDER BY confidence DESC LIMIT 20";
                $rows = $this->db->fetchAll($sql, ["%{$entity}%"]);
            }
            return $rows ?: [];
        } catch (\Throwable $e) {
            error_log("KnowledgeGraph::query error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find related entities via traversal
     */
    public function getRelated(int $entityId, int $depth = 2): array
    {
        try {
            $visited = [$entityId];
            $results = [];
            $queue = [$entityId];

            for ($d = 0; $d < $depth && !empty($queue); $d++) {
                $nextQueue = [];
                foreach ($queue as $currentId) {
                    $placeholders = implode(',', array_fill(0, count($visited), '?'));
                    $sql = "SELECT kr.*, e.entity_name, e.entity_type
                            FROM ai_knowledge_relations kr
                            JOIN ai_knowledge_entities e ON e.id = kr.target_entity_id
                            WHERE kr.source_entity_id = ? AND kr.target_entity_id NOT IN ($placeholders)
                            UNION
                            SELECT kr.*, e.entity_name, e.entity_type
                            FROM ai_knowledge_relations kr
                            JOIN ai_knowledge_entities e ON e.id = kr.source_entity_id
                            WHERE kr.target_entity_id = ? AND kr.source_entity_id NOT IN ($placeholders)";
                    $params = array_merge([$currentId], $visited, [$currentId], $visited);
                    $rows = $this->db->fetchAll($sql, $params) ?: [];

                    foreach ($rows as $row) {
                        $targetId = $row['target_entity_id'] ?? $row['source_entity_id'];
                        if (!in_array($targetId, $visited)) {
                            $visited[] = $targetId;
                            $nextQueue[] = $targetId;
                            $results[] = $row;
                        }
                    }
                }
                $queue = $nextQueue;
            }
            return $results;
        } catch (\Throwable $e) {
            error_log("KnowledgeGraph::getRelated error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add a new entity
     */
    public function addEntity(string $name, string $type = 'concept', array $metadata = []): ?int
    {
        try {
            $existing = $this->db->fetch("SELECT id FROM ai_knowledge_entities WHERE entity_name = ? AND entity_type = ?", [$name, $type]);
            if ($existing) {
                $this->db->execute("UPDATE ai_knowledge_entities SET confidence = LEAST(confidence + 0.05, 1.0), updated_at = NOW() WHERE id = ?", [$existing['id']]);
                return (int)$existing['id'];
            }
            $this->db->execute(
                "INSERT INTO ai_knowledge_entities (entity_name, entity_type, metadata, confidence, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())",
                [$name, $type, !empty($metadata) ? json_encode($metadata) : null, 0.5]
            );
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log("KnowledgeGraph::addEntity error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a relation between two entities
     */
    public function addRelation($entity1, $relation, $entity2, float $confidence = 0.8): bool
    {
        try {
            $id1 = is_int($entity1) ? $entity1 : $this->findOrCreateEntity($entity1);
            $id2 = is_int($entity2) ? $entity2 : $this->findOrCreateEntity($entity2);
            if (!$id1 || !$id2 || $id1 === $id2) return false;

            $existing = $this->db->fetch(
                "SELECT id FROM ai_knowledge_relations WHERE source_entity_id = ? AND target_entity_id = ? AND relation_type = ?",
                [$id1, $id2, $relation]
            );
            if ($existing) {
                $this->db->execute("UPDATE ai_knowledge_relations SET confidence = GREATEST(confidence, ?), updated_at = NOW() WHERE id = ?", [$confidence, $existing['id']]);
                return true;
            }

            $this->db->execute(
                "INSERT INTO ai_knowledge_relations (source_entity_id, target_entity_id, relation_type, confidence, metadata, created_at, updated_at) VALUES (?, ?, ?, ?, NULL, NOW(), NOW())",
                [$id1, $id2, $relation, $confidence]
            );
            return true;
        } catch (\Throwable $e) {
            error_log("KnowledgeGraph::addRelation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search entities by type
     */
    public function findByType(string $type, int $limit = 50): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM ai_knowledge_entities WHERE entity_type = ? ORDER BY confidence DESC LIMIT ?",
                [$type, $limit]
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get stats about the knowledge graph
     */
    public function getStats(): array
    {
        try {
            return [
                'total_entities' => (int)$this->db->fetch("SELECT COUNT(*) AS c FROM ai_knowledge_entities")['c'] ?? 0,
                'total_relations' => (int)$this->db->fetch("SELECT COUNT(*) AS c FROM ai_knowledge_relations")['c'] ?? 0,
                'entity_types' => $this->db->fetchAll("SELECT entity_type, COUNT(*) AS count FROM ai_knowledge_entities GROUP BY entity_type ORDER BY count DESC") ?: [],
                'avg_confidence' => (float)($this->db->fetch("SELECT AVG(confidence) AS avg FROM ai_knowledge_entities")['avg'] ?? 0),
            ];
        } catch (\Throwable $e) {
            return ['total_entities' => 0, 'total_relations' => 0];
        }
    }

    /**
     * Prune low-confidence entities
     */
    public function prune(float $minConfidence = 0.2): int
    {
        try {
            $this->db->execute("DELETE FROM ai_knowledge_relations WHERE source_entity_id IN (SELECT id FROM ai_knowledge_entities WHERE confidence < ?)", [$minConfidence]);
            $this->db->execute("DELETE FROM ai_knowledge_relations WHERE target_entity_id IN (SELECT id FROM ai_knowledge_entities WHERE confidence < ?)", [$minConfidence]);
            $this->db->execute("DELETE FROM ai_knowledge_entities WHERE confidence < ?", [$minConfidence]);
            return $this->db->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function findOrCreateEntity(string $name): ?int
    {
        $existing = $this->db->fetch("SELECT id FROM ai_knowledge_entities WHERE entity_name = ?", [$name]);
        if ($existing) return (int)$existing['id'];
        return $this->addEntity($name, 'concept');
    }
}
