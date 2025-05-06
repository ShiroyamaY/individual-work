<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Document Signature",
 *     description="Operations related to document signing"
 * )
 *
 * @OA\Post(
 *     path="/sign-request",
 *     summary="Initialize a signature request",
 *     description="Creates a signature request for a document.",
 *     tags={"Document Signature"},
 *     security={{ "bearerAuth": {} }},
 *     operationId="initializeSignature",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/CreateSignatureRequest"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Signature request initialized",
 *         @OA\JsonContent(ref="#/components/schemas/SignatureRequestResource")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to initialize signature request"
 *     )
 * )
 *
 * @OA\Get(
 *     path="/documents/to-sign",
 *     summary="Get documents that need to be signed",
 *     tags={"Document Signature"},
 *     security={{ "bearerAuth": {} }},
 *     operationId="getDocumentsToSign",
 *     @OA\Response(
 *         response=200,
 *         description="List of documents to be signed",
 *         @OA\JsonContent(ref="#/components/schemas/DocumentCollection")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/documents/{id}/sign",
 *     summary="Sign a document",
 *     tags={"Document Signature"},
 *     security={{ "bearerAuth": {} }},
 *     operationId="signDocument",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the document to sign",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=202,
 *         description="Document signing process started"
 *     )
 * )
 */
class DocumentSignatureController
{
}
