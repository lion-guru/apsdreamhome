<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller for Event Bus operations
 */
class EventController extends BaseController
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Subscribe to an event
     */
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_name' => 'required|string|max:255',
                'handler' => 'required|string', // In real app, this would be handled differently
                'priority' => 'nullable|integer|min:1|max:4'
            ]);

            // For demo purposes, we'll use a simple handler
            $handler = function ($payload, $metadata) {
                Log::info('Event handled', [
                    'payload' => $payload,
                    'metadata' => $metadata
                ]);
            };

            $subscriptionId = $this->eventService->subscribe(
                $validated['event_name'],
                $handler,
                $validated['priority'] ?? EventService::PRIORITY_NORMAL
            );

            return response()->json([
                'success' => true,
                'message' => 'Event subscription created successfully',
                'data' => [
                    'subscription_id' => $subscriptionId,
                    'event_name' => $validated['event_name'],
                    'priority' => $validated['priority'] ?? EventService::PRIORITY_NORMAL
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe to event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subscribe to wildcard events
     */
    public function subscribeWildcard(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'pattern' => 'required|string|max:255',
                'handler' => 'required|string' // In real app, this would be handled differently
            ]);

            // For demo purposes, we'll use a simple handler
            $handler = function ($payload, $metadata) {
                Log::info('Wildcard event handled', [
                    'payload' => $payload,
                    'metadata' => $metadata
                ]);
            };

            $subscriptionId = $this->eventService->subscribeWildcard(
                $validated['pattern'],
                $handler
            );

            return response()->json([
                'success' => true,
                'message' => 'Wildcard subscription created successfully',
                'data' => [
                    'subscription_id' => $subscriptionId,
                    'pattern' => $validated['pattern']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe to wildcard events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe from an event
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_name' => 'required|string|max:255',
                'subscription_id' => 'required|string|max:255'
            ]);

            $result = $this->eventService->unsubscribe(
                $validated['event_name'],
                $validated['subscription_id']
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Unsubscribed successfully' : 'Subscription not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe from event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe from wildcard events
     */
    public function unsubscribeWildcard(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'subscription_id' => 'required|string|max:255'
            ]);

            $result = $this->eventService->unsubscribeWildcard($validated['subscription_id']);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Wildcard unsubscribed successfully' : 'Subscription not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe from wildcard events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish an event
     */
    public function publish(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_name' => 'required|string|max:255',
                'payload' => 'nullable',
                'metadata' => 'nullable|array',
                'metadata.type' => 'nullable|in:system,user,domain,infrastructure',
                'metadata.priority' => 'nullable|integer|min:1|max:4'
            ]);

            $eventId = $this->eventService->publish(
                $validated['event_name'],
                $validated['payload'],
                $validated['metadata'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Event published successfully',
                'data' => [
                    'event_id' => $eventId,
                    'event_name' => $validated['event_name'],
                    'published_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add event transformer
     */
    public function addTransformer(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transformer' => 'required|string' // In real app, this would be handled differently
            ]);

            // For demo purposes, we'll use a simple transformer
            $transformer = function ($event) {
                $event['metadata']['transformed_at'] = now()->toISOString();
                $event['metadata']['transformer_applied'] = true;
                return $event;
            };

            $transformerId = $this->eventService->addEventTransformer($transformer);

            return response()->json([
                'success' => true,
                'message' => 'Event transformer added successfully',
                'data' => [
                    'transformer_id' => $transformerId
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add event transformer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add event middleware
     */
    public function addMiddleware(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'middleware' => 'required|string' // In real app, this would be handled differently
            ]);

            // For demo purposes, we'll use a simple middleware
            $middleware = function ($event) {
                $event['metadata']['middleware_applied'] = true;
                $event['metadata']['middleware_timestamp'] = now()->toISOString();
                return $event; // Return true to continue processing
            };

            $middlewareId = $this->eventService->addEventMiddleware($middleware);

            return response()->json([
                'success' => true,
                'message' => 'Event middleware added successfully',
                'data' => [
                    'middleware_id' => $middlewareId
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add event middleware',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get event history
     */
    public function getHistory(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_name' => 'nullable|string|max:255',
                'event_type' => 'nullable|in:system,user,domain,infrastructure',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'limit' => 'nullable|integer|min:1|max:1000'
            ]);

            $filters = array_filter([
                'event_name' => $validated['event_name'] ?? null,
                'event_type' => $validated['event_type'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null
            ]);

            $limit = $validated['limit'] ?? 100;
            $history = $this->eventService->getEventHistory($filters, $limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'history' => $history,
                    'total_count' => count($history),
                    'filters' => $filters,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get event history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subscriptions
     */
    public function getSubscriptions(): JsonResponse
    {
        try {
            $subscriptions = $this->eventService->getSubscriptions();
            $wildcardSubscriptions = $this->eventService->getWildcardSubscriptions();

            return response()->json([
                'success' => true,
                'data' => [
                    'direct_subscriptions' => $subscriptions,
                    'wildcard_subscriptions' => $wildcardSubscriptions,
                    'total_direct' => count($subscriptions),
                    'total_wildcard' => count($wildcardSubscriptions),
                    'total_subscriptions' => count($subscriptions) + count($wildcardSubscriptions)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get subscriptions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear event history
     */
    public function clearHistory(): JsonResponse
    {
        try {
            $result = $this->eventService->clearEventHistory();

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Event history cleared successfully' : 'Failed to clear event history'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear event history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate event bus report
     */
    public function generateReport(): JsonResponse
    {
        try {
            $report = $this->eventService->generateReport();

            return response()->json([
                'success' => true,
                'message' => 'Event bus report generated successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate event bus report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get event bus dashboard
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $report = $this->eventService->generateReport();
            $subscriptions = $this->eventService->getSubscriptions();
            $wildcardSubscriptions = $this->eventService->getWildcardSubscriptions();
            $recentHistory = $this->eventService->getEventHistory([], 10);

            $dashboard = [
                'overview' => [
                    'processing_mode' => $report['processing_mode'],
                    'total_subscriptions' => $report['subscriptions']['total_direct_subscriptions'] + $report['subscriptions']['total_wildcard_subscriptions'],
                    'total_events' => $report['event_history']['total_events'],
                    'system_health' => $report['performance_metrics']['system_health']
                ],
                'subscriptions' => [
                    'direct' => $subscriptions,
                    'wildcard' => $wildcardSubscriptions,
                    'summary' => $report['subscriptions']
                ],
                'activity' => [
                    'recent_events' => $recentHistory,
                    'frequency' => $report['performance_metrics']['event_frequency'],
                    'average_priority' => $report['performance_metrics']['average_priority']
                ],
                'performance' => $report['performance_metrics'],
                'configuration' => [
                    'retention_days' => $report['event_history']['retention_days'],
                    'max_queue_size' => $report['event_history']['max_queue_size'],
                    'middleware_count' => $report['middleware']['total_middleware'],
                    'transformers_count' => $report['middleware']['total_transformers']
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $dashboard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get event bus dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Demonstrate event bus functionality
     */
    public function demonstrate(): JsonResponse
    {
        try {
            // Subscribe to specific event
            $subscriptionId1 = $this->eventService->subscribe('user.registered', function ($payload, $metadata) {
                Log::info('User registered event handled', [
                    'user_email' => $payload['email'] ?? 'unknown',
                    'event_id' => $metadata['event_id'] ?? 'unknown'
                ]);
            });

            // Subscribe to wildcard events
            $subscriptionId2 = $this->eventService->subscribeWildcard('user.*', function ($payload, $metadata) {
                Log::info('User wildcard event handled', [
                    'event_name' => $metadata['event_name'] ?? 'unknown',
                    'event_type' => $metadata['type'] ?? 'unknown'
                ]);
            });

            // Add transformer
            $transformerId = $this->eventService->addEventTransformer(function ($event) {
                $event['metadata']['demonstration_transformer'] = true;
                return $event;
            });

            // Add middleware
            $middlewareId = $this->eventService->addEventMiddleware(function ($event) {
                $event['metadata']['demonstration_middleware'] = true;
                return $event;
            });

            // Publish test events
            $eventId1 = $this->eventService->publish('user.registered', [
                'email' => 'demo@example.com',
                'name' => 'Demo User'
            ], [
                'type' => 'user',
                'priority' => 'high'
            ]);

            $eventId2 = $this->eventService->publish('user.login', [
                'user_id' => 123,
                'login_time' => now()->toISOString()
            ], [
                'type' => 'user',
                'priority' => 'normal'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event bus demonstration completed successfully',
                'data' => [
                    'subscriptions' => [
                        'user.registered' => $subscriptionId1,
                        'user.* wildcard' => $subscriptionId2
                    ],
                    'transformer_id' => $transformerId,
                    'middleware_id' => $middlewareId,
                    'published_events' => [
                        'user.registered' => $eventId1,
                        'user.login' => $eventId2
                    ],
                    'demonstration_completed_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event bus demonstration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get event statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $report = $this->eventService->generateReport();
            $history = $this->eventService->getEventHistory();

            // Calculate additional statistics
            $eventTypes = [];
            $priorities = [];
            $eventsPerHour = [];

            foreach ($history as $event) {
                $type = $event['metadata']['type'] ?? 'unknown';
                $priority = $event['metadata']['priority'] ?? 'normal';
                $hour = date('Y-m-d H:00', $event['metadata']['timestamp']);

                $eventTypes[$type] = ($eventTypes[$type] ?? 0) + 1;
                $priorities[$priority] = ($priorities[$priority] ?? 0) + 1;
                $eventsPerHour[$hour] = ($eventsPerHour[$hour] ?? 0) + 1;
            }

            $statistics = [
                'overview' => $report,
                'event_types' => $eventTypes,
                'priorities' => $priorities,
                'hourly_distribution' => array_slice($eventsPerHour, -24, 24, true),
                'most_active_hour' => !empty($eventsPerHour) ? array_keys($eventsPerHour, max($eventsPerHour))[0] : null,
                'total_events_processed' => count($history)
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get event statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
