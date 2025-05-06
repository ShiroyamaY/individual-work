<?php

namespace App\Swagger\Schemas\Resources;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DocumentResource",
 *     type="object",
 *     required={"id", "title", "size", "path", "createdAt"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="contract.pdf"),
 *     @OA\Property(property="size", type="integer", example=102400),
 *     @OA\Property(property="path", type="string", example="/documents/file.pdf"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2024-03-21T12:00:00Z")
 * )
 */
class DocumentResource
{
}
