<?php
/**
 * OpenAPI Specification Base Info
 *
 * Provides the base OpenAPI info annotation for the APS Dream Home API.
 */

namespace App\OpenApi;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="APS Dream Home API",
 *         version="1.0.0",
 *         description="REST API for APS Dream Home - Real Estate ERP/CRM Platform"
 *     ),
 *     @OA\Server(
 *         url="https://unforced-willena-seclusively.ngrok-free.dev/apsdreamhome",
 *         description="Development server"
 *     ),
 *     @OA\Server(
 *         url="https://www.apsdreamhomes.co.in",
 *         description="Production server"
 *     )
 * )
 */
class OpenApiInfo
{
}
