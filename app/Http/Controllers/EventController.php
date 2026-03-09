<?php

namespace App\Http\Controllers;

use App\Services\Events\EventService;
use App\Http\Controllers\Controller;

/**
 * Event Controller
 * Handles event management operations
 */
class EventController extends Controller
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
        $this->middleware('auth');
    }

    /**
     * Display event dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->eventService->getEventStats();
            $recentEvents = $this->eventService->getRecentEvents(20);
            
            return view('events.dashboard', compact('stats', 'recentEvents'));

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load event dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Publish an event
     */
    public function publish()
    {
        try {
            $eventName = request('event_name');
            $eventData = request('event_data', []);
            $eventType = request('event_type', EventService::TYPE_USER);
            $priority = request('priority', EventService::PRIORITY_NORMAL);

            $this->eventService->publish($eventName, $eventData, $eventType, $priority);

            return response()->json([
                'success' => true, 
                'message' => 'Event published successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get event statistics
     */
    public function getStats()
    {
        try {
            $stats = $this->eventService->getEventStats();
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get recent events
     */
    public function getRecentEvents()
    {
        try {
            $limit = request('limit', 20);
            $events = $this->eventService->getRecentEvents($limit);
            return response()->json(['success' => true, 'data' => $events]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Process event queue
     */
    public function processQueue()
    {
        try {
            $limit = request('limit', 50);
            $processed = $this->eventService->processQueue($limit);
            
            return response()->json([
                'success' => true, 
                'message' => "Processed {$processed} events",
                'processed' => $processed
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Clear old event logs
     */
    public function clearOldLogs()
    {
        try {
            $days = request('days', 30);
            $deleted = $this->eventService->clearOldLogs($days);
            
            return response()->json([
                'success' => true, 
                'message' => "Deleted {$deleted} old events",
                'deleted' => $deleted
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Subscribe to events
     */
    public function subscribe()
    {
        try {
            $eventName = request('event_name');
            $handler = request('handler'); // This would need to be a callable reference
            $priority = request('priority', EventService::PRIORITY_NORMAL);

            // Note: This is a simplified version. In practice, you'd need
            // a way to register actual PHP callables from HTTP requests
            $this->eventService->subscribe($eventName, function($event) {
                // Default handler - would be customizable
                error_log("Event received: " . $event['name']);
            }, $priority);

            return response()->json([
                'success' => true, 
                'message' => 'Subscribed to event successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get event subscribers
     */
    public function getSubscribers($eventName)
    {
        try {
            $subscribers = $this->eventService->getSubscribers($eventName);
            return response()->json(['success' => true, 'data' => $subscribers]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create event tables
     */
    public function createTables()
    {
        try {
            $this->eventService->createEventTables();
            return response()->json([
                'success' => true, 
                'message' => 'Event tables created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
