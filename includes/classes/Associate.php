<?php
class Associate {
    private $db;
    private $table = 'associates';

    public function __construct(Database $database) {
        $this->db = $database;
    }

    public function getById(string $uid): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE uid = ?",
            [$uid]
        );
    }

    public function getByEmail(string $email): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    public function create(array $associateData): array {
        try {
            $this->db->beginTransaction();

            // Generate new associate ID
            $newUid = $this->generateAssociateId();
            
            $data = [
                'uid' => $newUid,
                'name' => $associateData['name'],
                'email' => $associateData['email'],
                'phone' => $associateData['phone'],
                'password' => password_hash($associateData['password'], PASSWORD_DEFAULT),
                'sponser_id' => $associateData['sponsor_id'] ?? null,
                'join_date' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ];

            $this->db->insert($this->table, $data);
            $associateId = $this->db->lastInsertId();

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Associate created successfully',
                'associate_id' => $associateId,
                'uid' => $newUid
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Failed to create associate: ' . $e->getMessage()
            ];
        }
    }

    public function update(string $uid, array $data): array {
        try {
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $this->db->update(
                $this->table,
                $data,
                'uid = ?',
                [$uid]
            );

            return [
                'success' => true,
                'message' => 'Associate updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update associate: ' . $e->getMessage()
            ];
        }
    }

    public function getTeamMembers(string $uid, int $level = 1): array {
        return $this->db->fetchAll(
            "SELECT a.* FROM associates a 
            INNER JOIN team_hierarchy th ON a.associate_id = th.associate_id 
            WHERE th.upline_id = (SELECT associate_id FROM associates WHERE uid = ?) 
            AND th.level = ?",
            [$uid, $level]
        );
    }

    public function calculateCommission(string $uid): array {
        $associate = $this->getById($uid);
        if (!$associate) {
            return ['success' => false, 'message' => 'Associate not found'];
        }

        try {
            // Get current level details
            $levelDetails = $this->db->fetchOne(
                "SELECT * FROM associate_levels WHERE level_id = ?",
                [$associate['current_level_id']]
            );

            // Calculate commission based on business volume and level percentage
            $commission = ($associate['current_month_business'] * $levelDetails['commission_percentage']) / 100;

            return [
                'success' => true,
                'commission' => $commission,
                'level_name' => $levelDetails['level_name'],
                'commission_percentage' => $levelDetails['commission_percentage']
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to calculate commission'];
        }
    }

    public function updateLevel(string $uid): array {
        $associate = $this->getById($uid);
        if (!$associate) {
            return ['success' => false, 'message' => 'Associate not found'];
        }

        try {
            // Find appropriate level based on total business
            $newLevel = $this->db->fetchOne(
                "SELECT * FROM associate_levels 
                WHERE min_business <= ? AND max_business >= ?
                ORDER BY level_id DESC LIMIT 1",
                [$associate['total_business'], $associate['total_business']]
            );

            if ($newLevel && $newLevel['level_id'] != $associate['current_level_id']) {
                $this->update($uid, ['current_level_id' => $newLevel['level_id']]);
                return [
                    'success' => true,
                    'message' => 'Level updated successfully',
                    'new_level' => $newLevel['level_name']
                ];
            }

            return ['success' => true, 'message' => 'No level update needed'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update level'];
        }
    }

    private function generateAssociateId(): string {
        $result = $this->db->fetchOne(
            "SELECT uid FROM {$this->table} ORDER BY associate_id DESC LIMIT 1"
        );

        if ($result) {
            $numericPart = intval(substr($result['uid'], 3)) + 1;
        } else {
            $numericPart = 1;
        }

        return sprintf('APS%06d', $numericPart);
    }

    public function validateSponsorId(string $sponsorId): bool {
        $result = $this->db->fetchOne(
            "SELECT uid FROM {$this->table} WHERE uid = ?",
            [$sponsorId]
        );
        return $result !== null;
    }
}