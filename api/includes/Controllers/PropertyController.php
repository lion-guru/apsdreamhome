<?php

namespace App\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Property API",
 *     version="1.0.0",
 *     description="API for managing properties"
 * )
 */
class PropertyController
{
    /**
     * @OA\Get(
     *     path="/api/properties",
     *     summary="Get list of properties",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Property")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedError")
     * )
     */
    public function index()
    {
        // Implementation
    }

    /**
     * @OA\Post(
     *     path="/api/properties",
     *     summary="Create a new property",
     *     tags={"Properties"},
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PropertyInput")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Property created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Property")
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedError")
     * )
     */
    public function store()
    {
        // Implementation
    }
}

/**
 * @OA\Schema(
 *     schema="Property",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Beautiful House"),
 *     @OA\Property(property="description", type="string", example="A beautiful house with a garden"),
 *     @OA\Property(property="price", type="number", format="float", example=250000.50),
 *     @OA\Property(property="bedrooms", type="integer", example=3),
 *     @OA\Property(property="bathrooms", type="number", format="float", example=2.5),
 *     @OA\Property(property="area", type="integer", example=1800, description="Area in square feet"),
 *     @OA\Property(property="address", type="string", example="123 Main St"),
 *     @OA\Property(property="city", type="string", example="New York"),
 *     @OA\Property(property="state", type="string", example="NY"),
 *     @OA\Property(property="zip_code", type="string", example="10001"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="PropertyInput",
 *     required={"title", "price"},
 *     @OA\Property(property="title", type="string", example="Beautiful House"),
 *     @OA\Property(property="description", type="string", example="A beautiful house with a garden"),
 *     @OA\Property(property="price", type="number", format="float", example=250000.50),
 *     @OA\Property(property="bedrooms", type="integer", example=3),
 *     @OA\Property(property="bathrooms", type="number", format="float", example=2.5),
 *     @OA\Property(property="area", type="integer", example=1800, description="Area in square feet"),
 *     @OA\Property(property="address", type="string", example="123 Main St"),
 *     @OA\Property(property="city", type="string", example="New York"),
 *     @OA\Property(property="state", type="string", example="NY"),
 *     @OA\Property(property="zip_code", type="string", example="10001")
 * )
 */
