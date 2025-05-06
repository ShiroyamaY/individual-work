<?php

namespace App\Swagger;

/**
 * @OA\Tag(
 *      name="Authentication",
 *      description="Operations related to authentication"
 * )
 *
 * @OA\Get(
 *     path="/user",
 *     summary="Get authenticated user",
 *     description="Returns the currently authenticated user.",
 *     operationId="getUser",
 *     tags={"Authentication"},
 *     security={{ "bearerAuth": {} }},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */
class AuthController extends Controller
{
}
