<?php
/**
 * AI Recommendations API
 * Provides personalized recommendations based on user learning
 */

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $ai_learner = new AILearningSystem();
    $recommendations = $ai_learner->getPersonalizedRecommendations();

    echo json_encode([
        'success' => true,
        'recommendations' => $recommendations,
        'generated_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Unable to generate recommendations: ' . $e->getMessage()]);
}
