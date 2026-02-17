<?php

namespace App\Models;

/**
 * MLM Training System
 */
class MLMTrainingSystem
{

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    public static function getTrainingModules()
    {
        return [
            [
                'id' => 'basics',
                'title' => 'MLM Fundamentals',
                'description' => 'Learn the basics of multi-level marketing',
                'duration' => '2 hours',
                'difficulty' => 'Beginner',
                'modules' => [
                    'What is MLM?',
                    'Understanding Compensation Plans',
                    'Building Your Network',
                    'Legal and Ethical Considerations'
                ]
            ],
            [
                'id' => 'advanced',
                'title' => 'Advanced MLM Strategies',
                'description' => 'Master advanced MLM techniques',
                'duration' => '4 hours',
                'difficulty' => 'Advanced',
                'modules' => [
                    'Leadership Development',
                    'Team Building Strategies',
                    'Commission Maximization',
                    'Business Scaling'
                ]
            ],
            [
                'id' => 'digital',
                'title' => 'Digital MLM Marketing',
                'description' => 'Leverage digital tools for MLM success',
                'duration' => '3 hours',
                'difficulty' => 'Intermediate',
                'modules' => [
                    'Social Media Marketing',
                    'Content Creation',
                    'Online Lead Generation',
                    'Digital Tools and Automation'
                ]
            ]
        ];
    }

    public function trackProgress($associateId, $moduleId, $progress)
    {
        // Track training progress
        $sql = "INSERT INTO mlm_training_progress (associate_id, module_id, progress_percentage, completed_at)
                VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE progress_percentage = ?, completed_at = ?";

        $stmt = $this->db->prepare($sql);
        $completedAt = $progress >= 100 ? date('Y-m-d H:i:s') : null;
        $stmt->execute([$associateId, $moduleId, $progress, $completedAt, $progress, $completedAt]);

        return ['success' => true, 'progress' => $progress];
    }
}
