<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::query();
        
        // Basic filtering
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }
        
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        // Basic pagination
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);
        
        $properties = $query->limit($limit)->offset($offset)->get();
        
        return response()->json([
            'data' => $properties,
            'meta' => [
                'total' => $query->count(),
                'limit' => $limit,
                'offset' => $offset,
                'version' => '1.0'
            ]
        ]);
    }
    
    /**
     * Store a newly created property.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'property_type' => 'required|string|in:apartment,house,condo,commercial',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'area' => 'required|numeric|min:0',
            'features' => 'nullable|string',
            'images' => 'nullable|array',
            'status' => 'required|string|in:active,inactive,sold'
        ]);
        
        $property = Property::create($validated);
        
        return response()->json([
            'data' => $property,
            'message' => 'Property created successfully',
            'version' => '1.0'
        ], 201);
    }
    
    /**
     * Display the specified property.
     */
    public function show(Property $property): JsonResponse
    {
        return response()->json([
            'data' => $property,
            'version' => '1.0'
        ]);
    }
    
    /**
     * Update the specified property.
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string|max:255',
            'property_type' => 'sometimes|string|in:apartment,house,condo,commercial',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'area' => 'sometimes|numeric|min:0',
            'features' => 'sometimes|string',
            'images' => 'sometimes|array',
            'status' => 'sometimes|string|in:active,inactive,sold'
        ]);
        
        $property->update($validated);
        
        return response()->json([
            'data' => $property,
            'message' => 'Property updated successfully',
            'version' => '1.0'
        ]);
    }
    
    /**
     * Remove the specified property.
     */
    public function destroy(Property $property): JsonResponse
    {
        $property->delete();
        
        return response()->json([
            'message' => 'Property deleted successfully',
            'version' => '1.0'
        ]);
    }
    
    /**
     * Basic search functionality
     */
    public function search(Request $request): JsonResponse
    {
        $query = Property::query();
        
        // Search in title and description
        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Apply filters
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }
        
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        $properties = $query->limit(50)->get();
        
        return response()->json([
            'data' => $properties,
            'meta' => [
                'total' => $query->count(),
                'query' => $request->all(),
                'version' => '1.0'
            ]
        ]);
    }
    
    /**
     * Enhanced search functionality (v1.1)
     */
    public function enhancedSearch(Request $request): JsonResponse
    {
        $query = Property::query();
        
        // Advanced search with multiple fields
        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%')
                  ->orWhere('features', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Advanced filtering
        if ($request->has('property_types')) {
            $query->whereIn('property_type', explode(',', $request->property_types));
        }
        
        if ($request->has('price_range')) {
            $priceRange = explode(',', $request->price_range);
            $query->whereBetween('price', [$priceRange[0], $priceRange[1]]);
        }
        
        if ($request->has('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }
        
        if ($request->has('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }
        
        if ($request->has('area_min')) {
            $query->where('area', '>=', $request->area_min);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        $limit = min($request->get('limit', 20), 100);
        $offset = $request->get('offset', 0);
        
        $properties = $query->limit($limit)->offset($offset)->get();
        
        return response()->json([
            'data' => $properties,
            'meta' => [
                'total' => $query->count(),
                'limit' => $limit,
                'offset' => $offset,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'filters' => $request->all(),
                'version' => '1.1'
            ]
        ]);
    }
}
