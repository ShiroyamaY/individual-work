<?php

namespace App\Swagger;

/**
 * @OA\Tag(
 *     name="Public Key",
 *     description="Operations for Public Key Management"
 * )
 *
 * @OA\Get(
 *     path="/public-key",
 *     summary="Get the public key",
 *     tags={"Public Key"},
 *     @OA\Response(
 *         response=200,
 *         description="Successfully retrieved public key",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="key", type="string", example="-----BEGIN PUBLIC KEY-----...-----END PUBLIC KEY-----")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Public keys are temporarily unavailable",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Public keys are temporarily unavailable.")
 *         )
 *     )
 * )
 */
class PublicKeyController extends Controller
{
}
