<?php

namespace App\Swagger\Schemas\Resources;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="SignatureRequestResource",
 *     type="object",
 *     required={"request_id", "status"},
 *     @OA\Property(property="request_id", type="string", example="abc123"),
 *     @OA\Property(property="status", type="string", enum={"pending", "completed"}, example="pending")
 * )
 */
class SignatureRequestResource
{
}
