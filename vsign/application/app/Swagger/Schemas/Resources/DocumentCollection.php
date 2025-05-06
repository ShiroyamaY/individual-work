<?php

namespace App\Swagger\Schemas\Resources;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DocumentCollection",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/DocumentResource")
 * )
 */
class DocumentCollection
{
}
