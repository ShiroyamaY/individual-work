<?php

namespace App\Swagger\Schemas\Requests;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateSignatureRequest",
 *     type="object",
 *     required={"document"},
 *     @OA\Property(
 *         property="document",
 *         type="string",
 *         format="binary",
 *         description="PDF file to be signed"
 *     )
 * )
 */
class CreateSignatureRequest
{
}
