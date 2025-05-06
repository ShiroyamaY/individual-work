<?php

namespace App\Swagger\Schemas\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Document",
 *     type="object",
 *     required={"id", "path", "original_filename", "mime_type", "size", "hash"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="path", type="string", example="/documents/file.pdf"),
 *     @OA\Property(property="original_filename", type="string", example="contract.pdf"),
 *     @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *     @OA\Property(property="size", type="integer", example=102400),
 *     @OA\Property(property="hash", type="string", example="d41d8cd98f00b204e9800998ecf8427e")
 * )
 */
class Document
{
}
